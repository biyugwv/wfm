<?php
namespace App\Api;
use App\Response\Response as Response;
class Api{
    static  $cache = [];
    static private $response;
    static private $instance;
    static public function getInstance()
    {
        if (!self::$instance instanceof self) {
            self::$instance = new self();
            self::$response = Response::getInstance();
         }
        return self::$instance;
    }

    public  function get($info)
    {
        switch ($info[0]){
            case 0;
                $this->notFound();
                break;
            case 1:
                // page  file data
                $this->dispatcher($info);
                break;
            case 2:
                $this->methodErr();
                break;
            default:
                $this->notFound();
                break;
        }
    }

    //
    private  function  dispatcher($info)
    {
        switch ($info[1]){
            case "Admin":
                // 输出page
                break;
            case "Api":
                $this->getApi($info[2]);
                break;

        }
    }

    public  function getApi($apiUrl,$apidata = [])
    {

        $apiCacheName = $apiUrl['module']."_".$apiUrl['dodir']."_".$apiUrl['dofile'];
        // 公共函数获取Api
        $cache = self::$cache;
        if($cache[$apiCacheName]){
            $api = $cache[$apiCacheName];
        }else{
            // 引入
            $api = getClass($apiUrl);
            if(is_array($api)){
                $ret = $api;
            }else{
                self::$cache[$apiCacheName] = $api;
            }

        }
        if(!$ret){
            if( !count($apidata)){
            $data = _data();
        }else{
                $data = $apidata;
            }

            $act = $data['act'];
            $ret = [];
            if($act && method_exists($api,$act)){
                $api->data = $data;
                $ret = $api->$act();
            }else{
                $ret = ['err'=>'act不存在'];
            }
        }
        if( !count($apidata) )  {
            self::$response->dump(200,$ret);
        }else{
            return $ret;
        }
    }

    // method err
    private  function  methodErr()
    {
        self::$response->dump(10);
    }

    // 404
    private  function  notFound()
    {
        self::$response->dump(404);

    }
}




