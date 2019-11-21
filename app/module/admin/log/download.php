<?php
namespace Api\admin\log;

class download {
    public $data;


    public function __construct()
    {

    }


    public  function getlist(){
        $param = $this->data;
        $uid = getUid($param['ssid']);

        // 获取该用户的下载记录
        if($uid){
            if(!$param['page'] || intval($param['page'])<=0) {
                $param['page'] = 1;
            }
            $s = (intval($param['page'])-1) * 10;
            $downlist = [];
            $cond = ['uid'=>$uid,'LIMIT'=>[$s,10]];
            $downlist = db()->select("log_down",['id','path','filename','content','addtime'],$cond);
            $list = [];
            foreach ($downlist as $v){
                $list[] = [
                    "id"=>$v['id'],
                    "date"=>date("Y-m-d H:i:d",$v['addtime']),
                    "name"=>$v['filename'],
                    "path"=>$v['path'],
                ];
            }
            unset($cond['LIMIT']);
            $total = db()->count("log_down",$cond);
            $ret['list'] = $list;
            $ret['pageall'] = ceil($total/10);
            $ret['pagenow'] = $param['page'];
            $ret['total'] = $total;
        }else{
            $err = '请登录';
        }
        if($err){
            $ret['err'] = $err;
        }


        return $ret;
    }



}
