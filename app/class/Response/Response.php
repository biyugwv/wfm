<?php
namespace App\Response;

class Response{
    static public  $instance = null;
    static private $code;
    static private $allow = [];
    static private $codeList = [
            10 => "method error",
            200 => "ok",
            404 => "not found",
            500 => "server error",
            600 => "undeclared",
    ];
    static private $httpVersion = 2.0;
    static public  function  getInstance($config=[])
    {

        if(!self::$instance)
        {

            self::$instance = new self($config);
        }
        return self::$instance;
    }
    private  function  __construct($config=[])
    {

        if($config['allow'] && is_array($config['allow']))
        {
            self::$allow = $config['allow'];
        }
        if($config['httpVersion']) self::$httpVersion = $config['httpVersion'];
    }
    /*
     *  output file
     */
    public function output()
    {

    }

    public  function  dump($code,$data=[])
    {
        if(!is_array($data)) $data = [];
        // allow origin
        $allow = self::$allow;
        $original = $_SERVER['HTTP_ORIGIN'];
    
        if( count($allow) && in_array($original,$allow))
        {
            header('Access-Control-Allow-Origin:'.$original);
            header('Access-Control-Allow-Credentials:true');
        }
        $codeList = self::$codeList;
        if($codeList[$code]){
            $codeDetail = $codeList[$code];
        }else{
            $code = 600;
            $codeDetail = $codeList[600];
        }
        $header = 'HTTP/'.self::$httpVersion.' '.$code.' '.$codeDetail;
        header("Content-type: application/json; charset=utf-8");
        header($header);
        $data['code'] = $code;
        $data['codeDetail'] = $codeDetail;
        $microtime = microtime(true);
        $usetime = round(($microtime - STARTTIME)*1000);
        $data['usetime'] = $usetime; 
        echo(json_encode($data,JSON_UNESCAPED_UNICODE));
        die();
    }

}

