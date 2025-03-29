<?php

namespace App\Common\Library\Request;

use App\Common\Library\Log\Log;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Class Http
 * @package dsp\http
 * @method static post($name, $path, array $data = [])
 * @method static get($name, $path, array $data = [])
 * @method static delete($name, $path, array $data = [])
 * @method static put($name, $path, array $data = [])
 */
class Http
{
    private static $instance;

    // 配置名字
    private $name;

    // 请求path
    private $path;

    // 请求数据
    private $data;

    // 配置
    private $conf;

    // 默认参数
    private array $default = [
        'timeout' => 5,
        'retry' => 1,
        'headers' => [],
        'format' => 'json'
    ];

    private function __construct()
    {
    }

    private function __clone()
    {
    }

    /**
     * 初始化
     * @param $name
     * @param $path
     * @param $data
     */
    public static function init($name, $path, $data)
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        $app = self::$instance;
        $app->name = $name;
        $app->path = $path;
        $app->data = $data;
        $app->requestData();
    }

    /**
     * 发起http请求
     * @param $name
     * @param $data
     * @return mixed
     */
    public static function __callStatic($name, $data)
    {
        if (in_array($name, ['get', 'post', 'put', 'delete'])) {
            self::init($data[0] ?? '', $data[1] ?? '', $data[2] ?? []);
            $data = self::$instance->request($name);
            self::init('', '', []);
            return $data;
        }
    }

    /**
     * 组织http请求参数
     */
    private function requestData()
    {
        $this->conf['host'] = env($this->name);
        $this->setter('timeout');
        $this->setter('retry');
        $this->setter('format');
        $this->setHeaders();
    }

    /**
     * 属性设置
     * @param $key
     */
    private function setter($key)
    {
        // 说明: 参数读取顺序, data > $conf > default
        if (!isset($this->data[$key])) {
            if (!isset($this->conf[$key])) {
                $this->data[$key] = $this->default[$key];
            } else {
                $this->data[$key] = $this->conf[$key];
            }
        }
    }

    /**
     * 设置RequestId
     */
    private function setRequestId()
    {
        $this->data['headers']['x-request-id'] = get_request_id();
    }

    /**
     * 设置header
     */
    private function setHeaders()
    {
        $this->data['headers'] = array_merge($this->default['headers'], $this->conf['headers'] ?? [], $this->data['headers'] ?? []);
        $this->setRequestId();
    }

    /**
     * 获取请求uri
     */
    private function getUri()
    {
        return trim($this->conf['host'], '/') . '/' . ltrim($this->path, '/');
    }

    /**
     * xml格式化解析
     * @param $xml
     * @return mixed
     */
    private function formatXml($xml)
    {
        return json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA), JSON_UNESCAPED_UNICODE), true);
    }

    /**
     * json格式化解析
     * @param $stringBody
     * @return mixed
     */
    private function formatJson($stringBody)
    {
        $jsonBody = json_decode($stringBody);
        return $jsonBody ? $jsonBody : $stringBody;
    }

    /**
     * 发起请求
     * @param $name string 请求类型
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function request($name)
    {
        if (empty($this->conf['host'])) {
            return ['code' => 500, 'error' => 'The corresponding domain name configuration was not found.', 'data' => []];
        }
        $uri = $this->getUri();

        // 重试次数
        $retry = $this->data['retry'];
        unset($this->data['retry']);

        // http
        $http = new Client(['timeout' => $this->data['timeout']]);
        unset($this->data['timeout']);

        // 返回格式
        $format = $this->data['format'];
        unset($this->data['format']);

        for ($times = 1; $times <= $retry; $times++) {
            // 记录请求日志
            $logArr = [];
            $logArr['requestStart'] = (new \DateTime())->format('Y-m-d H:i:s.u');
            $logArr['retry'] = $times . '/' . $retry;
            $logArr['requestUri'] = $uri;
            $logArr['requestMethod'] = $name;
            $logArr['requestData'] = $this->data;

            try {
                $response = $http->request($name, $uri, $this->data);
                $body = $response->getBody();
                $stringBody = (string)$body;
                $retData = match ($format) {
                    'xml' => $this->formatXml($stringBody),
                    'raw' => $stringBody,
                    default => $this->formatJson($stringBody),
                };

                $logArr['requestEnd'] = (new \DateTime())->format('Y-m-d H:i:s.u');

                // 计算总耗时，单位秒
                $startTimestamp = (new \DateTime($logArr['requestStart']))->format('U.u');
                $endTimestamp = (new \DateTime($logArr['requestEnd']))->format('U.u');
                $logArr['elapsed'] = bcsub($endTimestamp, $startTimestamp, 4);

                $logArr['status'] = $response->getStatusCode();
                $logArr['responseData'] = $retData;
                $logArr['responseHeaders'] = $response->getHeaders();
                Log::writeRunLog('api calls logging', $logArr);

                // 请求重试
                if ($response->getStatusCode() == 0 || $response->getStatusCode() > 300) {
                    if ($times < $retry) {
                        continue;
                    }
                }

                return ['code' => $response->getStatusCode(), 'data' => $retData, 'error' => '', 'headers' => $response->getHeaders()];
            } catch (RequestException $e) {
                $logArr['requestEnd'] = (new \DateTime())->format('Y-m-d H:i:s.u');

                // 计算总耗时，单位秒
                $startTimestamp = (new \DateTime($logArr['requestStart']))->format('U.u');
                $endTimestamp = (new \DateTime($logArr['requestEnd']))->format('U.u');
                $logArr['elapsed'] = bcsub($endTimestamp, $startTimestamp, 4);

                if ($e->hasResponse()) {
                    $body = $e->getResponse()->getBody();
                    $stringBody = (string)$body;
                    $retData = match ($format) {
                        'xml' => $this->formatXml($stringBody),
                        'raw' => $stringBody,
                        default => $this->formatJson($stringBody),
                    };

                    $code = $e->getResponse()->getStatusCode();
                    $logArr['status'] = $code;
                    $logArr['responseData'] = $retData;
                    $logArr['responseHeaders'] = $e->getResponse()->getHeaders();
                    Log::writeRunLog('api calls logging', $logArr);

                    // 请求重试
                    if ($times < $retry) {
                        continue;
                    }

                    return ['code' => $code, 'error' => $retData, 'data' => [], 'headers' => []];
                }

                $logArr['status'] = 500;
                $logArr['responseData'] = $e->getMessage();
                $logArr['responseHeaders'] = [];
                Log::writeRunLog('api calls logging', $logArr);

                if ($times < $retry) {
                    continue;
                }
                return ['code' => 500, 'error' => $e->getMessage(), 'data' => [], 'headers' => []];
            }
        }

        return ['code' => 500, 'error' => 'request error', 'data' => [], 'headers' => []];
    }
}