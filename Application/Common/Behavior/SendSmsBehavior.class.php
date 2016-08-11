<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Common\Behavior;
use Think\Behavior;
use Think\Hook;

defined('THINK_PATH') or exit();

// 初始化钩子信息
class SendSmsBehavior extends Behavior {
    //发送短信，并将短信记录保存
    public function run(&$param){
        //发送短信
      if(!empty($param['username'])&&!empty($param['userphone']&&!empty($param['sms_template']))){
         $result= send_sms($param['userphone'],$param['username'],$param['sms_template']);
         $data = array(
             'sms_name'=>$param['username'],
             'sms_phone'=>$param['userphone'],
             'time'=>time(),
             'sms_error'=>$result->msg,
         );
          M('sms')->add($data);
      }
    }
}