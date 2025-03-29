<?php

use App\Common\Library\Helper\Random;
use App\Common\Model\ProjectModel;
use App\Common\Override\ReflectionManager;
use App\Common\Library\Request\Http;
use App\Common\Logic\AuthLogic;
use App\Common\Model\SysConfigModel;
use Overtrue\Pinyin\Pinyin;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

const DS = DIRECTORY_SEPARATOR;

if (!function_exists('match_class')) {
    /**
     * 检查一个类或对象是否是 $target_class 类的子类或与其相同
     * @param string|object $needle
     * @param string $target_class
     * @return bool
     */
    function match_class(string|object $needle, string $target_class): bool
    {
        if (is_object($needle))
            return $needle instanceof $target_class;
        if (!class_exists($needle) || !class_exists($target_class))
            return false;
        return $needle == $target_class || is_subclass_of($needle, $target_class);
    }
}

if (!function_exists('container')) {
    /**
     * 返回容器实例,或 $class 的实例；
     * @param string $class
     * @return mixed
     */
    function container(string $class = '')
    {
        if ($class)
            return container()->get($class);
        return \Hyperf\Utils\ApplicationContext::getContainer();
    }
}

if (!function_exists('request')) {
    /**
     * request 实例
     * @return ServerRequestInterface
     */
    function request(): ServerRequestInterface
    {
        return container()->get(ServerRequestInterface::class);
    }
}

if (!function_exists('response')) {
    /**
     * response 实例
     * @return mixed
     */
    function response()
    {
        return container()->get(ResponseInterface::class);
    }
}

if (!function_exists('get_snowflake_id')) {
    /**
     * 生成一个雪花算法id
     * @return string
     */
    function get_snowflake_id(): string
    {
        $generator = container()->get(\Hyperf\Snowflake\IdGeneratorInterface::class);
        return $generator->generate();
    }
}

if (!function_exists('uuid')) {
    /**
     * 生成一个uuid
     * @return string
     */
    function uuid(): string
    {
        return \Ramsey\Uuid\Uuid::uuid1()->toString();
    }
}

if (!function_exists('get_request_id')) {
    /**
     * 获取请求id
     * @return string
     */
    function get_request_id(): string
    {
        return @request()->getHeaderLine('x-request-id');
    }
}

if (!function_exists('get_client_ip')) {
    /**
     * 获取客户端IP地址
     * @return mixed
     */
    function get_client_ip(): mixed
    {
        $res = @request()->getServerParams();
        if (isset($res['http_client_ip'])) {
            return $res['http_client_ip'];
        } elseif (isset($res['http_x_real_ip'])) {
            return $res['http_x_real_ip'];
        } elseif (isset($res['http_x_forwarded_for'])) {
            // 部分CDN会获取多层代理IP，所以转成数组取第一个值
            $arr = explode(',', $res['http_x_forwarded_for']);
            return $arr[0];
        } else {
            return $res['remote_addr'];
        }
    }
}

if (!function_exists('get_namespace')) {
    /**
     * 获取类的命名空间(末尾不带反斜杠)
     * @param string|object $class
     * @return string
     */
    function get_namespace(string|object $class): string
    {
        if (is_object($class)) {
            $class = $class::class;
        }
        return ReflectionManager::getNamespace($class);
    }
}

if (!function_exists('get_class_basename')) {
    /**
     * 获取不含命名空间的类名
     * @param string|object $class
     * @return string
     */
    function get_class_basename(string|object $class): string
    {
        if (is_object($class)) {
            $class = $class::class;
        }
        $arr = explode('\\', $class);
        return $arr ? end($arr) : '';
    }
}

if (!function_exists('get_redis')) {
    /**
     * 获取redis对象
     */
    function get_redis(): \Hyperf\Redis\Redis
    {
        return container()->get(Redis::class);
    }
}

if (!function_exists('get_redis_lock')) {
    /**
     * 获取redis锁
     * @param $key
     * @param int $seconds
     * @return boolean
     */
    function get_redis_lock($key, $seconds = 1): bool
    {
        $redis = get_redis();

        if ($redis->setnx($key, 'lock') == 1) {
            $redis->expire($key, $seconds);
            return true;
        }

        return false;
    }
}

if (!function_exists('del_redis_lock')) {
    /**
     * 清除redis锁
     * @param $key
     */
    function del_redis_lock($key)
    {
        $redis = get_redis();
        $redis->del($key);
    }
}

if (!function_exists('page_helper')) {
    /**
     * 分页公共处理方法
     * @param $page
     * @param $pageSize
     * @param $total_count
     * @return array
     */
    function page_helper($page, $pageSize, $total_count = 0)
    {
        // page < 1 查询第一页
        if (intval($page) < 1) {
            $page = 1;
        }

        if (intval($pageSize) < 1) {
            $pageSize = 10;
        }

        if ($total_count) {
            // 计算总页数
            $pages = ceil(intval($total_count) / intval($pageSize));

            // page > 最大页数 查询最后一页
            if (intval($page) > $pages) {
                $page = $pages;
            }
        }

        // 计算查询偏移量
        $offset = ($page - 1) * intval($pageSize);

        return [
            'page' => $page,
            'pageSize' => intval($pageSize),
            'offset' => $offset,
            'pages' => $pages,
        ];
    }
}

if (!function_exists('m_config')) {
    /**
     * 获取模块配置信息
     *  eg:
     *      获取指定模块配置：m_config('ModuleName:config_key.sub_key')
     *      获取当前模块配置：m_config('config_key.sub_key')
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    function m_config($key, $default = null)
    {
        $arr = explode(':', $key);
        if ($arr[1] ?? 0) {
            $module = $arr[0];
            $m_key = $arr[1];
        } else {
            $m_key = $key;
            $module = module();
        }
        $real_key = 'modules.' . $module . '.' . $m_key;
        return config($real_key, $default);
    }
}


if (!function_exists('sys_config')) {
    /**
     * 获取模块配置信息
     *  eg:
     *      获取指定模块配置：m_config('ModuleName:config_key.sub_key')
     *      获取当前模块配置：m_config('config_key.sub_key')
     * @param mixed $key
     * @param mixed $default
     * @return mixed
     */
    function sys_config($key, $default = null)
    {
        return SysConfigModel::getValue($key, $default);
    }
}

if (!function_exists('module')) {
    /**
     * 获取当前模块（调用类所在模块）
     * @return string
     */
    function module(): string
    {
        $class = get_trace_class();
        if (!$class) return '';
        preg_match_all('/^\\\{0,1}App\\\([^\\\]*)\\\.*$/', $class, $match, PREG_PATTERN_ORDER);
        return $match[1][0] ?? '';
    }
}

if (!function_exists('get_trace_class')) {
    /**
     * 获取调用class
     * @return string
     */
    function get_trace_class(): string
    {
        $trace = debug_backtrace();
        foreach ($trace as $t) {
            if ($class = $t['class'] ?? 0) {
                return $class;
            }
        }
        return '';
    }
}

if (!function_exists('list_to_tree')) {
    /**
     * 把返回的数据集转换成Tree
     * @param $list
     * @param string $pk
     * @param string $pid
     * @param string $child
     * @param int $root
     * @return array
     */
    function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = 'children', $root = 0)
    {
        $tree = [];
        foreach ($list as $key => $value) {
            if ($value[$pid] == $root) {
                $children = list_to_tree(list: $list, root: $value[$pk]);
                if ($children) {
                    $value[$child] = $children;
                }
                $tree[] = $value;
                unset($list[$key]);
            }
        }
        return $tree;
    }
}

if (!function_exists('get_random_captcha')) {
    /**
     * 生成唯一数字验证码
     * @param int $len
     * @param null $chars
     * @param bool $status
     * @return string
     */
    function get_random_captcha($len = 4, $chars = null, $status = true)
    {
        // 判断是否需要真实发送，测试时验证码统一为0
        if (!$status) {
            $str = str_pad('', $len, '0');
        } else {
            if (is_null($chars)) {
                $chars = "0123456789";
            }

            mt_srand(bcmul(10000000, (double)microtime()));

            for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
                $str .= $chars[mt_rand(0, $lc)];
            }
        }

        return $str;
    }
}

if (!function_exists('check_id_card')) {
    /**
     * 验证身份证
     * @param $id_card
     * @return bool
     */
    function check_id_card($id_card)
    {
        if (empty($id_card)) {
            return false;
        }
        $city = array(11 => "北京", 12 => "天津", 13 => "河北", 14 => "山西", 15 => "内蒙古", 21 => "辽宁", 22 => "吉林", 23 => "黑龙江", 31 => "上海", 32 => "江苏", 33 => "浙江", 34 => "安徽", 35 => "福建", 36 => "江西", 37 => "山东", 41 => "河南", 42 => "湖北", 43 => "湖南", 44 => "广东", 45 => "广西", 46 => "海南", 50 => "重庆", 51 => "四川", 52 => "贵州", 53 => "云南", 54 => "西藏", 61 => "陕西", 62 => "甘肃", 63 => "青海", 64 => "宁夏", 65 => "新疆", 71 => "台湾", 81 => "香港", 82 => "澳门", 91 => "国外");
        $iSum = 0;
        $id_card_length = strlen($id_card);
        //长度验证
        if (!preg_match('/^\d{17}(\d|x)$/i', $id_card) and !preg_match('/^\d{15}$/i', $id_card)) {
            return false;
        }
        //地区验证
        if (!array_key_exists(intval(substr($id_card, 0, 2)), $city)) {
            return false;
        }
        // 15位身份证验证生日，转换为18位
        if ($id_card_length == 15) {
            $s_birthday = '19' . substr($id_card, 6, 2) . '-' . substr($id_card, 8, 2) . '-' . substr($id_card, 10, 2);
            if ($s_birthday != $s_birthday) {
                return false;
            }
            $id_card = substr($id_card, 0, 6) . "19" . substr($id_card, 6, 9);//15to18
            $Bit18 = get_verify_bit($id_card);//算出第18位校验码
            $id_card = $id_card . $Bit18;
        }
        // 判断是否大于2078年，小于1900年
        $year = substr($id_card, 6, 4);
        if ($year < 1900 || $year > 2078) {
            return false;
        }
        //18位身份证处理
        $s_birthday = substr($id_card, 6, 4) . '-' . substr($id_card, 10, 2) . '-' . substr($id_card, 12, 2);
        if ($s_birthday != $s_birthday) {
            return false;
        }
        //身份证编码规范验证
        $idcard_base = substr($id_card, 0, 17);
        if (strtoupper(substr($id_card, 17, 1)) != get_verify_bit($idcard_base)) {
            return false;
        }
        return true;
    }
}

if (!function_exists('get_verify_bit')) {
    /**
     * 计算身份证校验码，根据国家标准GB 11643-1999
     * @param $idcard_base
     * @return false|string
     */
    function get_verify_bit($idcard_base)
    {
        if (strlen($idcard_base) != 17) {
            return false;
        }
        //加权因子
        $factor = array(7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2);
        //校验码对应值
        $verify_number_list = array('1', '0', 'X', '9', '8', '7', '6', '5', '4', '3', '2');
        $checksum = 0;
        for ($i = 0; $i < strlen($idcard_base); $i++) {
            $checksum += substr($idcard_base, $i, 1) * $factor[$i];
        }
        $mod = $checksum % 11;
        $verify_number = $verify_number_list[$mod];
        return $verify_number;
    }
}

if (!function_exists('create_project_code')) {
    /**
     * 生成项目码
     * @param $projectName
     * @param $companyName
     * @return string
     */
    function create_project_code($projectName, $companyName)
    {
        $pinYin = new Pinyin();
        $project_str = $pinYin->abbr($projectName);
        $company_str = $pinYin->abbr($companyName);
        do {
            $str = $company_str . $project_str . Random::alnum();
            for ($a = MD5(hash('sha256', $str), true),
                 $s = '0123456789ABCDEFGHIJKLMNOPQRSTUV',
                 $d = '',
                 $f = 0;
                 $f < 8;
                 $g = ord($a[$f]),
                 $d .= $s[($g ^ ord($a[$f + 8])) - $g & 0x1F],
                 $f++
            ) ;
        } while (ProjectModel::where(['code' => $d])->exists());

        return $d;
    }
}

if (!function_exists('get_tree')) {
    /**
     * 递归
     * @param array $data
     * @param int $pId
     * @param string $key
     * @return array
     */
    function get_tree(array $data = [], int $pId = 0, string $key = 'pid'): array
    {
        if (empty($data)) {
            return [];
        }
        $tree = [];
        foreach ($data as $v) {
            if ($v[$key] == $pId) {
                if (!empty(get_tree($data, $v['id'], $key))) {
                    $v['children'] = get_tree($data, $v['id'], $key);
                }
                $tree[] = $v;
            }
        }
        return $tree;
    }
}

if (!function_exists('get_random_password')) {
    /**
     * 生成字母数字密码
     * @param int $len
     * @param null $chars
     * @param bool $status
     * @return string
     */
    function get_random_password($len = 4, $chars = null)
    {
        if (is_null($chars)) {
            $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        }

        mt_srand(bcmul(10000000, (double)microtime()));

        for ($i = 0, $str = '', $lc = strlen($chars) - 1; $i < $len; $i++) {
            $str .= $chars[mt_rand(0, $lc)];
        }

        return $str;
    }
}

if (!function_exists('auth')) {
    /**
     * 获取当前请求的auth对象
     *
     * @return AuthLogic|null
     */
    function auth()
    {
        return request()->getAttribute('auth');
    }
}

if (!function_exists('create_order_no')) {
    /**
     * 根据当前时间创建订单编号，使用加法运算提高唯一性
     * @return string
     */
    function create_order_no(): string
    {
        $order_id_main = date('YmdHis') . rand(100000, 999999);
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for ($i = 0; $i < $order_id_len; $i++) {
            $order_id_sum += (int)(substr($order_id_main, $i, 1)); // 这里对生成的随机序列进行加法运算使重复率降低
        }
        return $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
    }
}

if (!function_exists('get_distance')) {
    /**
     * 计算两点地理坐标之间的距离
     * @param $longitude1
     * @param $latitude1
     * @param $longitude2
     * @param $latitude2
     * @param int $unit 单位 1:米 2:公里
     * @param int $decimal 精度 保留小数位数
     * @return float
     */
    function get_distance($longitude1, $latitude1, $longitude2, $latitude2, int $unit = 1, int $decimal = 2): float
    {
        $EARTH_RADIUS = 6370.996; // 地球半径系数
        $PI = 3.1415926;

        $radLat1 = $latitude1 * $PI / 180.0;
        $radLat2 = $latitude2 * $PI / 180.0;

        $radLng1 = $longitude1 * $PI / 180.0;
        $radLng2 = $longitude2 * $PI / 180.0;

        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;

        $distance = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2)));
        $distance = $distance * $EARTH_RADIUS * 1000;

        if ($unit == 2) {
            $distance = $distance / 1000;
        }

        return round($distance, $decimal);
    }
}

