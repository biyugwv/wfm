<?php
namespace Api\admin\log;

class main {
    public $data;


    public function __construct()
    {

    }


    public  function main(){
        $param = $this->data;
        $uid = getUid($param['ssid']);

        // 获取该用户的下载记录
        if($uid){

            $down = db()->count("log_down",['uid'=>$uid]);
            $click = db()->count("log_click",['uid'=>$uid]);
            $ret['down'] = $down;
            $ret['click'] = $click;
        }else{
            $err = '请登录';
        }
        if($err){
            $ret['err'] = $err;
        }


        return $ret;
    }



}
