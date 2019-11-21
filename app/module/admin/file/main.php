<?php
namespace Api\admin\file;

class main {
    public $data;
    public $uid;
    public function __construct()
    {
        $this->uid = getUid(_request('ssid'));
    }
    
    public  function fileList(){
        $param= $this->data;
        $uid = $this->uid;
        if(!$this->canSee()){
            return ['err'=>'无查看权限'];
        }
        $root = "/biyugwv";
        $path = decrypt($param['path'],'filepath');
        $patharr = explode("/",$path);
        if(strpos(end($patharr),'.')){
            $path = implode('/',array_slice($patharr,0,count($patharr)-1));
        }
        $list = [];
        $list_folders = [];
        $list_files = [];
        $right = [];
        $rights = db()->select("rights","*",["uid"=>$uid]);
        $ret['rights'] = $rights[0];
        $ret['uid'] = $uid;
        if (false != ($handle = opendir ( $root.$path ))) {
            while ( false !== ($file = readdir( $handle )) ) {
                //去掉"“.”、“..”以及带“.xxx”后缀的文件
                if ($file != "." && $file != ".." ) {
                    $type = 'folder';
                    if(is_file( $root.$path.'/'.$file  )) {
                        $list_files[]=['type'=>'file','name'=>$file,'path'=>encrypt($path.'/'.$file,'filepath')];
                    }else{
                        $list_folders[]=['type'=>'folder','name'=>$file,'path'=>encrypt($path.'/'.$file,'filepath')];
                    }

                }
            }
            //关闭句柄
            closedir ( $handle );
        }
        $list = array_merge($list_folders,$list_files);
        $patharr = explode("/",$path);
        $path_ret = [];
        $pathstr = '';
        foreach ($patharr as $k=>$v){

            if(!$k){
                array_push($path_ret,['name'=>'biyugwv','path'=>encrypt($pathstr,"filepath")]);
            }else{
                $pathstr .= '/'.$v;
                array_push($path_ret,['name'=>$v,'path'=>encrypt($pathstr,"filepath")]);
            }
        }
        $ret['path'] = $path_ret;
        $ret['list'] = $list;
        $ret['dir'] = '/';

        return $ret;
    }

    public  function fileDown(){
        $param= $this->data;
        $uid = $this->uid;
        if(!$this->canSee()){
            return ['err'=>'无查看权限'];
        }
        if(!$this->canDown()) return ['err'=>'没有下载权限'];
        $root  = "/biyugwv";
        $file  = $root . decrypt($param['path'],'filepath');
        $arr = explode("/",$file);
        $filename = $arr[count($arr)-1];
        $path = str_replace('/'.$filename,"",$file);
        $dumpinfo = downInfo($uid,$path);
        if(is_file($file)){

            header("Content-type:application/octet-stream");
            header("Accept-Ranges:bytes");
            header("Accept-Length:".filesize($file));
            header("Content-Disposition: attachment; filename=".$filename);
            $f = fopen($file, 'r');//打开文件
            $content = fread($f,filesize($file));
            db()->insert("log_down",['uid'=>$uid,'path'=>$path,'filename'=>$filename,'content'=>$content]);
            echo $content;
            echo "\r\n";
            echo "\r\n";
            echo $dumpinfo;
            fclose($f);
            die();

        }else{
            echo("文件不存在：$file--".$param['path']);
            die();
        }


    }

    public  function  fileHistory(){
        // path  name
        $param= $this->data;
        $uid = $this->uid;
        if(!$this->canSee()){
            return ['err'=>'无查看权限'];
        }
        
        $path = decrypt($param['path'],'filepath');
        $patharr = explode('/',$path);
        $path = implode('/',array_slice($patharr,0,count($patharr)-1));
        $name = $param['name'];
        $root = "/biyugwv";
        $file  = $root . $path.'/'.$name;
        $ret['path'] = 'root' . $path;
        $ret['name'] = $name;
        $ret['ext'] = end(explode('.',$name));
        // 线上文件
        if(!is_file($file)){
            return ['err'=>'文件不存在'];
        }
        if(!$param['page'] || intval($param['page'])<=0) {
            $param['page'] = 1;
        }
        $s = (intval($param['page'])-1) * 10;
        $cond =['LIMIT'=>[$s,10]];
        $cond['ORDER'] = ['id'=>'DESC'];
        $cond['name'] = $param['name'];
        $cond['path'] = $param['path'];
        if($uid != 'admin'){
            $cond['uid'] = $uid;
        }
        $list = db()->select("files_edit",['id','addtime','isonline','isapply','uid','remarks'],$cond);
        foreach($list as  $k=>&$v){
            $v['addtime']  = date('Y-m-d H:i:d',$v['addtime']);
        }
        $ret['list'] = $list;
        return $ret;
    }

    /*
     * path 
     * name 
     * id
     */
    public  function  fileDetail(){
        $param = $this->data;
        $uid = $this->uid;
        if(!$this->canSee()){
            return ['err'=>'无查看权限'];
        }
        $path = $param['path'];
        $name = $param['name'];
        $id = $param['id'];
        
        if($id){
            $cond['id'] = $id;
            if($uid != 'admin') $cond['uid'] = $uid;
            
            $datas = db()->select("files_edit",['content','isonline','name'],$cond);
            if($datas[0]['content']){
                $name = $datas[0]['name'];
                $ret['name'] = $name;
                $ret['content'] = $datas[0]['content'];
                $ret['isonline'] = $datas[0]['isonline'];
            }else{
                return ['err'=>'文件不存在','v'=>1];
            }
        }else{
            
            $root = "/biyugwv";
            $file  = $root . decrypt($param['path'],'filepath');
            if(!is_file($file)){
                return ['err'=>'文件不存在','v'=>2];
            }
            $ret['path'] = 'root' . decrypt($param['path'],'filepath');
            $ret['name'] = $name;
            $f = fopen($file,'r');
            $content = fread($f,filesize($file));
            $ret['content'] = $content;
            $ret['isonline'] = 1;

            fclose($f);
        }
        $ret['ext'] = end(explode('.',$name));
        return $ret;
    }

	public function fileUpadate(){
		$param = $this->data;
        $uid = $this->uid;
        if(!$this->canSee()){
            return ['err'=>'无查看权限'];
        }
        if(!$this->canUpdate()){
            return ['err'=>'无修改权限'];
        }
       
        $remarks = $param['remarks'];
        $content = $param['content'];
        // 判断文件
        $path = decrypt($param['path'],'filepath');
        $patharr = explode('/',$path);
        $path = implode('/',array_slice($patharr,0,count($patharr)-1));
        $name = $param['name'];
        $root = "/biyugwv";
        $file  = $root . $path.'/'.$name;
        if(!is_file($file)){
            return ['err'=>'文件不存在'];
        }
        // 防止重复提交
        $datas = db()->select("files_edit",['id','addtime'],['uid'=>$uid,'ORDER'=>['id'=>'DESC'],'LIMIT'=>1]);
        $ret['datas'] = $datas;
        if($datas[0]['addtime'] && time()-$datas[0]['addtime']<100){
            return ['err'=>'提交太急了，请'.(100-time()+$datas[0]['addtime']).'秒后稍后重试'];
        }
        // 插入新的代码
        db()->insert("files_edit",['uid'=>$uid,'content'=>$content,'remarks'=>$remarks,'name'=>$param['name'],'path'=>$param['path']]);
        $ret['id'] = db()->id();
        $ret['ok'] = 1;
        return $ret;
        
	}

    private function  canSee(){
        $uid = $this->uid;
        $datas = db()->select("rights",'fileSee',['uid'=>$uid]);
        if($datas[0]['fileSee']) return true;
        return false;
    }

    private function  canUpdate(){
        $uid = $this->uid;
        $datas = db()->select("rights",'fileUpdate',['uid'=>$uid]);
        if($datas[0]['fileUpdate']) return true;
        return false;
    }

    
    private function  canDown(){
        $uid = $this->uid;
        $datas = db()->select("rights",'fileDown',['uid'=>$uid]);
        if($datas[0]['fileDown']) return true;
        return false;
    }
}


