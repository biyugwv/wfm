<?php
    error_reporting(E_ERROR);
    define('STARTTIME',microtime(true));
    if($_GET["xhprof"] == date("iH")){
        //开启性能分析
        xhprof_enable(XHPROF_FLAGS_MEMORY | XHPROF_FLAGS_CPU);
    }
    //php中止时运行的函数
    register_shutdown_function(function () {
        if($_GET["xhprof"] == date("iH")){
            //停止性能分析
            $xhprof_data = xhprof_disable();
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }
            //引入xhprof库文件，路径请自行修改
            $XHPROF_ROOT = 'biyugwv/php/xhprof';
            include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_lib.php";
            include_once $XHPROF_ROOT . "/xhprof_lib/utils/xhprof_runs.php";
            $xhprof_runs = new XHProfRuns_Default();
            //导出性能分析数据，默认xhprof.output_dir指定的目录
            $run_id = $xhprof_runs->save_run($xhprof_data, 'xhprof');
        }
        // 计数器减一
        requestEnd();
    });
    define("VENDOR_PATH","../vendor");
    define("APP_PATH",str_replace("\\","/",__DIR__));
    require_once(VENDOR_PATH."/autoload.php");

    // load common functions
    require_once(APP_PATH."/common/functions.php");
    requestStart();
    if(_get("show") == "phpinfo"){
        phpinfo();
        die();
    }
    if(_get('show')== 'server'){
         var_dump($_SERVER);
         die();
    }
    // app config
    $appConfig =  getConfig("app");



    // app init :  global db   + global api + global redis
    $db = new App\Db\Db($appConfig['db']);


    $responseInit = App\Response\Response::getInstance(['allow'=>$appConfig['allow']]);
    $api   = App\Api\Api::getInstance();

    // handle request
    $dispatcher = FastRoute\simpleDispatcher(function(FastRoute\RouteCollector $r) {
         $r->addRoute(['GET','POST'], '/', 'Admin');
         $r->addRoute(['GET','POST'], '/admin/{pagename}', 'Admin');
         $r->addRoute(['GET','POST'], '/api/{module}/{dodir}/{dofile}', 'Api');
    });

    $httpMethod = $_SERVER['REQUEST_METHOD'];
    $uri = $_SERVER['REQUEST_URI'];

    // 去除查询字符串( ? 后面的内容) 和 解码 URI
    if (false !== $pos = strpos($uri, '?')) {
        $uri = substr($uri, 0, $pos);
    }
    $uri = rawurldecode($uri);

    $routeInfo = $dispatcher->dispatch($httpMethod, $uri);

    // 获取资源
    $api->get($routeInfo);
