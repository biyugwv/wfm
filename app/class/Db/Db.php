<?php

namespace  App\Db;

use Medoo\Medoo as medoo;


class Db{

	static  public $conn = null;

    public  function  __construct($opt=[])
    {
        if(self::$conn instanceof  medoo){
            return self::$conn;
        }else{
           return  self::$conn = new medoo($opt);
        }

    }

    public function  __call($name, $arguments)
    {
        $db = self::$conn;
        switch ($name){
            case "select":
                    if(is_array($arguments[2])){
                        $arguments[2]['status'] = 0;
                    }else{
                        $arguments[2]['status'] = 0;
                    }
                    break;
            case "update":
                if(!$arguments[1]['updatetime'])$arguments[1]['updatetime'] = time();
                break;
            case "insert":
                if(!$arguments[1]['addtime'])$arguments[1]['addtime'] = time();
                break;
            case "delete":
                return $db->update($arguments[0],['status'=>-1],$arguments[1]);
                break;
            default:
                break;
        }
        return $db->$name(...$arguments);

    }

}