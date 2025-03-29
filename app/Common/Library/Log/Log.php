<?php

namespace App\Common\Library\Log;

use Exception;
use Hyperf\Context\Context;
use Hyperf\HttpServer\Router\Dispatched;

/**
 * 日志记录
 */

/**
 * Class Log
 * @method static access(string $msg) 记录access日志
 * @method static debug(string $msg) 记录debug级别run日志
 * @method static info(string $msg) 记录info级别run日志
 * @method static run(string $msg) 记录run级别run日志
 * @method static error(string $msg) 记录error日志
 */
class Log
{
    // 日志信息
    protected static array $log = array();

    // 日志文件大小限制，单位：M
    protected static int $logFileSize = 20;

    public static function __callStatic($name, $arguments)
    {
        $name = strtolower($name);

        // 获取访问请求信息或异常信息
        $msg = isset($arguments[0]) ? $arguments[0] : '';
        is_object($msg) && $msg = (string)$msg;

        if (is_string($msg)) {
            $msg = str_replace("\n", ',', $msg);
        }

        // 判断记录日志类型
        $level = strtolower($name);
        $logType = match ($level) {
            'access' => 'access',
            'info', 'debug', 'run' => 'run',
            default => 'error',
        };

        // 执行日志记录
        self::writeLog($msg, $level, $logType);
    }

    /**
     * 记录运行日志
     *
     * @param string $message 日志内容
     * @param array $param 自定义日志参数+内容
     */
    public static function writeRunLog(string $message, $param = array())
    {
        // 执行日志记录
        self::writeLog($message, 'info', 'run', $param);
    }

    /**
     * 日志写入接口
     * @access public
     * @param string $logMsg 日志信息
     * @param string $logLevel 日志级别
     * @param string $logType 日志类型
     * @param array $param 自定义日志参数+内容
     * @return void
     */
    static private function writeLog(string $logMsg, string $logLevel, string $logType, $param = array())
    {
        // 将日志保存在内存，请求结束后统一记录
        $requestId = get_request_id();
        self::$log[$requestId][] = self::createLogString($logMsg, $logLevel, $logType, $param);
    }

    /**
     * access log
     * 拼接日志内容
     * @param $log
     * @param $logLevel
     * @param $logType
     * @param array $param
     * @return array
     */
    static private function createLogString($log, $logLevel, $logType, $param = array()): array
    {
        $logMsg['nowTime'] = (new \DateTime())->format('Y-m-d H:i:s.u'); // 记录日志时间
        $logMsg['hostName'] = php_uname('n'); // 服务器名称
        $logMsg['traceId'] = get_request_id(); // 链条id，请求id（例：一次请求十条日志，十条日志的traceid相同）
        $logMsg['spanId'] = uuid(); // 日志id（例：一次请求十条日志，十条日志的spanId不同）
        $logMsg['clientIp'] = get_client_ip(); // 客户端ip
        $logMsg['logLevel'] = $logLevel; // 日志级别
        $logMsg['logType'] = $logType; // 日志类型
        if ($logType == 'access') {
            $logMsg = self::getRequestData($logMsg); // 请求数据
        }

        // 自定义参数
        $logMsg['logParams'] = $param;

        // 自定义日志内容
        $logMsg['logContent'] = $log;

        return $logMsg;
    }

    /**
     * 日志保存
     * @throws Exception
     */
    public static function save()
    {
        $requestId = get_request_id();
        if (!isset(self::$log[$requestId]) || empty(self::$log[$requestId])) {
            return;
        }

        // 定义初始父id
        $parent = 'ROOT';
        $startTime = Context::get('request_start_time');
        $endTime = (new \DateTime())->format('Y-m-d H:i:s.u');

        // 获取请求信息
        $requestHost = @request()->fullUrl();
        $requestMethod = @request()->getMethod();
        $requestRoute = @request()->getAttribute(Dispatched::class)->handler->callback;
        $contentType = @request()->getHeaderLine('content-type');
        $thisModule = explode('/', @request()->getAttribute(Dispatched::class)->handler->route);
        $thisModule = $thisModule[1] ?? "";

        foreach (self::$log[$requestId] as $key => $value) {
            $value['requestHost'] = $requestHost; // 记录请求地址
            $value['requestMethod'] = $requestMethod; // 记录请求类型
            $value['requestRoute'] = $requestRoute; // 记录请求路由
            $value['contentType'] = $contentType; // 记录请求头信息

            // 第一条为访问日志
            if ($key == 0 && $value['logType'] == 'access') {
                $value['serviceStart'] = $startTime;
                $value['serviceEnd'] = $endTime;

                // 计算总耗时，单位秒
                $startTimestamp = (new \DateTime($startTime))->format('U.u');
                $endTimestamp = (new \DateTime($endTime))->format('U.u');
                $value['elapsed'] = bcsub($endTimestamp, $startTimestamp, 4);
            }

            // 记录父级日志id
            $value['parentId'] = $parent;

            // 处理日志内容
            $thisLog = json_encode($value, JSON_UNESCAPED_UNICODE) . PHP_EOL;

            // 将日志记录在本地文件
            self::writeToFile($thisLog, $thisModule, $value['logType']);

            // 定义下条日志的父级id
            $parent = $value['spanId'];
        }

        // 保存后清空日志缓存
        self::$log[$requestId] = array();
    }

    /**
     * 将日志写入文件
     * 如果在零点的时候，临时文件都已经移动到日期目录了，这时还有前一天的日志文件没有写入文件，会写入到当天的文件里。
     * @param $log
     * @param $level
     */
    static private function writeToFile($log, $module, $level)
    {
        // 定义日志文件类型
        $typeName = match ($level) {
            'access' => 'access',
            'run' => 'run',
            default => 'error',
        };

        if (empty($module)) {
            $module = 'undefined';
        }

        // 定义日志目录与日志文件路径
        $logPath = BASE_PATH . '/runtime/logs/' . $module . '/' . date('Y-m-d') . '/';
        $destination = $logPath . $typeName . '.log';

        // 如果日志目录不存在，则创建日志目录
        $log_dir = dirname($destination);
        if (!is_dir($log_dir)) {
            try {
                mkdir($log_dir, 0755, true);
            } catch (Exception $e) {

            }
        }

        // 检测日志文件大小，超过配置大小则备份日志文件重新生成
//        if (is_file($destination) && floor(self::$logFileSize * 1048576) <= filesize($destination)) {
//            $filename = $log_dir . '/' . basename($destination) . '.' . time();
//            rename($destination, $filename);
//        }

        error_log($log, 3, $destination);
    }

    /**
     * 获取请求数据
     * @param $logMsg
     * @return mixed
     */
    static private function getRequestData($logMsg): mixed
    {
        $requestMethod = @request()->getMethod();
        $contentType = @request()->getHeaderLine('content-type');
        if ($requestMethod == 'GET') {
            $data = @request()->all();
        } else {
            if (str_contains($contentType, 'application/octet-stream')) {
                $data = 'Binary file stream';
            } else {
                $data = @request()->all();
            }
        }
        $logMsg['requestParams'] = $data;
        return $logMsg;
    }
}
