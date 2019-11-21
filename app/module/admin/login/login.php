<?php
namespace Api\admin\login;

class login {
    public $data;


    public function __construct()
    {

    }


    public  function api(){
        return ['msg'=>['name'=>'api','value'=>200]];
    }

    public  function  login(){
        $param = $this->data;
        if(!$param['username']){
            $err = '请填写用户名';
        }
        if(!$err && !$param['password']){
            $err = '请填写密码';
        }
        if(!$err){
            $data = db()->select("admin",['id','username','password'],['username'=>$param['username']]);
            if(!count($data)){
                $err = "用户不存在";
            }else{
                if(md5(md5($param['password'])) !== $data[0]['password']){
                    $err = "密码错误";
                }else{
                    $ssid = md5(md5($data[0]['username'].rand(10000,99999)));
                    $ok = setRedis($ssid,$data[0]['username'],3600);
                    if($ok){
                        $ret['data'] = $data;
                        $ret['ok'] = 1;
                        $ret['ssid'] = $ssid;
                        $ret['expire'] = 3600*1000;
                    }else{
                        $err = "登录失败";
                    }

                }
            }
        }

        if($err) $ret['err'] = $err;
        return $ret;
    }

    public  function  logout()
    {
        $param = $this->data;
        $ssid = $param['ssid'];
        $uid = getUid($ssid);

    }
}
