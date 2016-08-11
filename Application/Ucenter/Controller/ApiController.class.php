<?php
/**
 * 放置用户登陆注册
 */
namespace Ucenter\Controller;

use Think\Controller;
use Ucenter\Api;

require_once APP_PATH . 'User/Conf/config.php';

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class ApiController extends Controller {
    public function login() {
        $callback = I('callback');
        $api = A('User', 'Api');
        $res = $api->login();
        $data = json_encode($res);
        echo $callback . '(' . json_encode($data) . ')';
    }
}
