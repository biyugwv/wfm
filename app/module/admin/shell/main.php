<?php
namespace Api\admin\shell;

class main {
    public $data;
    public $uid;
    public $shwll;
    
    public function __construct()
    {
        $this->uid = getUid(_request('ssid'));
    }
    
    public  function execute(){
        //
        //if($this->uid != 'admin'){
        //    return ['err'=>'暂无权限'];
        //}
        $param = $this->data;
        $this->shell = $param['shell'];
        // 过滤
        $this->fillter();
        $output = [];
        $retstat =  exec($this->shell,$output,$stat);
        $ret['content'] = $output;
        $ret['stat'] = $stat;
        $ret['retstat'] = $retstat;
        return $ret;
    }



    public function fillter(){
        $shell = $this->shell;
        
    }

    
}

