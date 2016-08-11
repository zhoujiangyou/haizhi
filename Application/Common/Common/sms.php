<?php
//curl get请求获取数据
// param $url 请求数据地址

/**
 * @param $url
 *
 * @return mixed
 */
function curl_get($url){
    //初始化
    $ch = curl_init();
    //设置选项，包括URL
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    //执行并获取HTML文档内容
    $output = curl_exec($ch);
    //释放curl句柄
    curl_close($ch);
    //打印获得的数据
   return $output;
}

/**
 *方法进行curlpost操作，将发送短信信息推送
 */
function curl_post($url,$post_data=array()){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    // post数据
    curl_setopt($ch, CURLOPT_POST, 1);
    // post的变量
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    $output = curl_exec($ch);
    curl_close($ch);
    //打印获得的数据
    return $output;
}

/**
 * @param $mobile 	多个之间用英文逗号隔开
 * @param $customerName 多个之间用英文逗号隔开，此参数需要URLEncode
 * @param $msg 此参数需要URLEncode（msg最多180个字。每条短信最多60个字，当msg超过60个字时会被自动拆分）
 *
 * return url+param
 */
/** @var TYPE_NAME $hotLine */
function con_url($mobile, $customerName, $msg){
    $mainHttp = C('SMS_MAIN_HTTP');
    $hotLine = C('SMS_HOTLINE');
    $enterpriseId = C('SMS_ENTERPRISEID');
    $userName = C('SMS_USERNAME');
    $seed =C('SMS_SEED');
    $pwd =md5(md5(C('SMS_PWD')).$seed);
    $type = C('SMS_TYPE');
    $customerName = urlencode($customerName);
    $msg = urlencode($msg);
//
//    $url = "$mainHttp?hotline=$hotLine&enterpriseId=&userName=$userName&pwd=$pwd&seed=$seed&type=$type&mobile=$mobile&customerName=$customerName&msg=$msg";

    $url="$mainHttp?hotline=$hotLine&enterpriseId=&userName=$userName&pwd=$pwd&seed=$seed&type=8&mobile=$mobile&customerName=$customerName&msg=$msg";
    return $url;
}

/**
 * 发送短信
 * @param $mobile
 * @param $customerName
 * @param $msg
 *
 * @return mixed
 */
function send_sms($mobile, $customerName, $msg){
    $url = con_url($mobile,$customerName,$msg);
    var_dump($url);exit();
    $result =  curl_post($url);
    return json_decode($result);
}