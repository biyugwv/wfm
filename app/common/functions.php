<?php

//    global functions
function getConfig($name){
    $path = APP_PATH . "/config/".$name.".config.php";

    if(file_exists($path)){
        return include($path);
    }else{
        return [
            "err" => 1
        ];
    }
}

function _post($key){
    if(isset($_POST[$key])){
        return $_POST[$key];
    }else{
        return null;
    }
}

function _get($key){
    if(isset($_GET[$key])){
        return $_GET[$key];
    }else{
        return null;
    }
}

function _request($key){
    return _get($key) ? _get($key) : _post($key);
}

function _data(){
    return $_GET ? $_GET : $_POST;
}

function getClass($route)
{
    // 检查父级
    if(is_file(APP_PATH.'/'.'module/'.$route['module'].'/'.$route['module'].'.php')){
        $parantClass = 'Api\\'.$route['module'].'\\'.$route['module'];
        $parant = new $parantClass;
        if(method_exists($parant,"init")){
            $ret = $parant->init($route['module'].'/'.$route['dodir'].'/'.$route['dofile']);
            if(is_array($ret)){
                return $ret;
            }
        }
    }
    if(is_file(APP_PATH.'/'.'module/'.$route['module'].'/'.$route['dodir'].'/'.$route['dofile'].".php")){
        $class = 'Api\\'.$route['module'].'\\'.$route['dodir'].'\\'.$route['dofile'];
        $api = new $class;
    }else{
        $api = null;
    }
    return $api;
}

function getApi($route,$data=['act'=>'noact'])
{
    $ret = [];
    $routes = [];
    $routearr = explode("/",$route);
    if(sizeof($routearr) == 3){
        $routes['module'] = $routearr[0];
        $routes['dodir'] = $routearr[1];
        $routes['dofile'] = $routearr[2];
        $api = App\Api\Api::getInstance();
        $ret = $api->getApi($routes,$data);
    }
    return $ret;
}
/***  Redis操作  ***/

function redisInstance(){
    $redisConfig = getConfig("redis");
    $redis = App\Redis\Redis::getInstance($redisConfig);
    return $redis;
}
function setRedis($key,$value='',$expire)
{
    $redis = redisInstance();
    if($key){
        return $redis->setex($key,$expire,$value);
    }else{
        return false;
    }
}

function getRedis($key){
    $redis = redisInstance();
    if($key){
        return $redis->get($key);
    }else{
        return false;
    }
}

function lpushRedis($key,$value){
    $redis = redisInstance();
    return $redis->lPush($key,$value);
}
function rpushRedis($key,$value){
    $redis = redisInstance();
    return $redis->rPush($key,$value);
}
function llenRedis($key){
    $redis = redisInstance();
    return $redis->lLen($key);
}
function lpopRedis($key){
    $redis = redisInstance();
    return $redis->lPop($key);
}
function rpopRedis($key){
    $redis = redisInstance();
    return $redis->rPop($key);
}

/***  Redis操作  ***/

function db(){
    return new \App\Db\Db();
}

function error($msg,$level){

}

/**
 * @desc加密
 * @param string $str 待加密字符串
 * @param string $key 密钥
 * @return string
 */
function encrypt($str, $key){
    //$mixStr = md5(date('Y-m-d H:i:s').rand(1000));
    $mixStr = 'ifyouwantleavejustgo';
    $tmp = '';
    $strLen = strlen($str);
    for($i=0, $j=0; $i<$strLen; $i++, $j++){
        $j = $j == 20 ? 0 : $j;
        $tmp .= $mixStr[$j].($str[$i] ^ $mixStr[$j]);
    }
    return base64_encode(bind_key($tmp, $key));
}

/**
 * @desc解密
 * @param string $str 待解密字符串
 * @param string $key 密钥
 * @return string
 */
function decrypt($str, $key){
    $str = bind_key(base64_decode($str), $key);
    $strLen = strlen($str);
    $tmp = '';
    for($i=0; $i<$strLen; $i++){
        $tmp .= $str[$i] ^ $str[++$i];
    }
    return $tmp;
}

/**
 * @desc辅助方法 用密钥对随机化操作后的字符串进行处理
 * @param $str
 * @param $key
 * @return string
 */
function bind_key($str, $key){
    $encrypt_key = md5($key);

    $tmp = '';
    $strLen = strlen($str);
    for($i=0, $j=0; $i<$strLen; $i++, $j++){
        $j = $j == 32 ? 0 : $j;
        $tmp .= $str[$i] ^ $encrypt_key[$j];
    }
    return $tmp;
}


function downInfo($uid,$path){
    $str = encrypt($uid.'|'.$path,'dumpinfo');
    return '------'.$str.'------';
}

function getUid($ssid){
    return getRedis($ssid);
}


function requestStart(){
    $info = [
        'url'=>$_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        'useragent' =>$_SERVER['HTTP_USER_AGENT'],
        'referer' =>$_SERVER['HTTP_REFERER'],
        'via' =>$_SERVER['HTTP_VIA'],
        'client_ip' =>$_SERVER['HTTP_CLIENT_IP'],
        'addr'=>$_SERVER['REMOTE_ADDR'],
        'act'=>_request("act"),
        'ssid'=>_request("ssid"),
        'uid'=>getUid(_request('ssid')),
        'data'=>_data(),
        'addtime'=>time()
    ];
    $redis = redisInstance();
    $concurrent = $redis->get('concurrent');
    if(!$concurrent) $concurrent = 0;
    $concurrent++;
    if($concurrent>1){
        $info['requeststat'] = -1;
        rpushRedis('requestlog',json_encode($info));
        die('当前服务拥挤，请重试！');
    }else{
        $info['requeststat'] = 1;
        rpushRedis('requestlog',json_encode($info));
        $redis->set('concurrent',$concurrent);
    }
}

function requestEnd(){
    $redis = redisInstance();
    $concurrent = $redis->get('concurrent');
    if($concurrent>=1) {
        $concurrent--;
    }
    $redis->set('concurrent',$concurrent);
}

