<?php
namespace Api\admin\index;

class main {
    public $data;


    public function __construct()
    {

    }


    public  function sidebar(){
        $param = $this->data;
        $uid = getUid($param['ssid']);
        $sidebars = getConfig("sidebar");
        $ret['sbs'] = $sidebars;
        // 获取该用户的左侧栏
        if($uid){
            $sidebarList = [];
            $sidebar = db()->select("sidebar",['sidebar'],['uid'=>$uid]);
            $sidebar = $sidebar[0]['sidebar'] ;
            $sidebararr = explode(",",$sidebar);

            foreach ($sidebararr as $k=>$v){
                if($sidebars[$v]) $sidebarList[$k] = $sidebars[$v];
            }
        }else{
            $err = '请登录';
        }
        if($err){
            $ret['err'] = $err;
        }
        if(count($sidebarList)){
            $ret['ok'] = 1;
            $ret['sidebarList'] = $sidebarList;
        }else{
            $ret['err'] = "请求失败";
        }

        return $ret;
    }

    
    public  function  clickLog(){
        $param= $this->data;
        $uid = getRedis($param['ssid']);
        $px = $param['px'];
        $py = $param['py'];
        $x = $param['x'];
        $y = $param['y'];
        lpushRedis("log_click",$px.",".$py.",".$x.",".$y.",".$uid.",".(time()));
        $ret['ok'] = 1;
        return $ret;

    }

}
