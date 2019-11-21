<?php
namespace Api\admin;

class admin{
    static $whiteList = [
        "admin/login/login",
        "admin/shell/main",
    ];
    public function init($route='')
    {
        if(!in_array($route,self::$whiteList )){
            if(!_request("ssid")){
                return ['err'=>'need ssid'];
            }else{
                // 验证
                $ssid = _request("ssid");
                $uid = getUid($ssid);
                if(!$uid){
                    return ['err'=>'ssid err','ssid'=>$ssid,'uid'=>$uid];
                }
            }
        }
    }
}