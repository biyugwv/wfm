<?php 
require(__DIR__ . '/class/mysql.php');
require(__DIR__ . '/class/Redis.php');
error_reporting(E_ALL & ~E_NOTICE);
//phpinfo();
//die();
$conf_redis  = [
	'host'     => '127.0.0.1',
    'port'     => 6379,
    'auth'     => 'wu2182606'
];

$conf_mysql = [
			'host'=>'localhost',
			'port'=>'3306',
			'user'=>'root',
			'password'=>'wu2182606',
			'dbname'=>'wfm'
		];
$redis = myRedis::getInstance($conf_redis);
$mysql = new Mysql($conf_mysql);

$i = 20;
while($i--){
	if($redis->lLen("log_click")){
		$value = $redis->lPop("log_click");
		$arr = explode(",",$value);
		if(count($arr) == 6){
			$mysql->doSql("insert into wfm_log_click(px,py,x,y,uid,addtime,pushtime) values ('".$arr[0]."','".$arr[1]."','".$arr[2]."','".$arr[3]."','".$arr[4]."','".$arr[5]."','".time()."')");
		}
	
	}
}

$i = 200;
while($i--){
    if($redis->lLen("requestlog")){
        $value = $redis->lPop("requestlog");
        $data = json_decode($value,true);
        $mysql->doSql("insert into wfm_log_request(useragent,referer,via,client_ip,act,ssid,url,data,uid,addtime,pushtime,stat) values ('".$data['useragent']."','".$data['referer']."','".$data['via']."','".$data['client_ip']."','".$data['act']."','".$data['ssid']."','".$data['url']."','".json_encode($data['data'])."','".$data['uid']."','".$data['addtime']."','".time()."','".$data['requeststat']."')");
    }
}


?>
