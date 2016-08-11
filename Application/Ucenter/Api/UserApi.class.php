<?php
/**
 * 放置用户登陆注册
 */
namespace Ucenter\Api;

use Think\Controller;

require_once APP_PATH . 'User/Conf/config.php';

/**
 * 用户控制器
 * 包括用户中心，用户登录及注册
 */
class UserApi extends Controller {
    public function login() {
        $aUsername = $username = I('userName');
        $aPassword = I('password');
        $aVerify = I('verify', '');
        $aRemember = I('remember', 0, 'int');


        /* 检测验证码 */
        if (check_verify_open('login')) {
            if (!check_verify($aVerify)) {
                $res['info']=L('_INFO_VERIFY_CODE_INPUT_ERROR_').L('_PERIOD_');
                return $res;
            }
        }

        /* 调用UC登录接口登录 */
        // 默认type=1，2是email，3是手机
        check_username($aUsername, $email, $mobile, $aUnType);

        if (!check_reg_type($aUnType)) {
            $res['info']=L('_INFO_TYPE_NOT_OPENED_').L('_PERIOD_');
        }

        $uid = UCenterMember()->login($username, $aPassword, $aUnType);
        if (0 < $uid) { //UC登录成功
            /* 登录用户 */
            $Member = D('Member');
            $args['uid'] = $uid;
            $args = array('uid'=>$uid,'nickname'=>$username);
            check_and_add($args);

            if ($Member->login($uid, $aRemember == 1)) { //登录用户
                //TODO:跳转到登录前页面

                $html_uc = '';
                if (UC_SYNC && $uid != 1) {
                    include_once './api/uc_client/client.php';
                    //同步登录到UC
                    $ref = M('ucenter_user_link')->where(array('uid' => $uid))->find();
                    $html_uc = uc_user_synlogin($ref['uc_uid']);
                }

                $oc_config =  include_once './OcApi/oc_config.php';
                if ($oc_config['SSO_SWITCH']) {
                    include_once  './OcApi/OCenter/OCenter.php';
                    $OCApi = new \OCApi();
                    $html_oc = $OCApi->ocSynLogin($uid);
                }

                $html =  empty($html_oc) ? $html_uc : $html_oc;
                $res['status']=1;
                $res['info']=$html;
                $res['data']['uid']=$uid;
                $res['data']['uname']=D('Admin/Member')->getNickName($uid);
                //$this->success($html, get_nav_url(C('AFTER_LOGIN_JUMP_URL')));
            } else {
                $res['info']=$Member->getError();
            }
        } else { //登录失败
            switch ($uid) {
                case -1:
                    $res['info']= L('_INFO_USER_FORBIDDEN_');
                    break; //系统级别禁用
                case -2:
                    $res['info']= L('_INFO_PW_ERROR_').L('_EXCLAMATION_');
                    break;
                default:
                    $res['info']= $uid;
                    break; // 0-接口参数错误（调试阶段使用）
            }
        }
        return $res;
    }
}


