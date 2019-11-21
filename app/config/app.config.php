<?php

    return [
        "name" => "wfm",
        "db" => [
            'database_type' => 'mysql',
            'database_name' => 'wfm',
            'server' => 'localhost',
            'username' => 'root',
            'password' => 'wu2182606',
            'charset' => 'utf8',
            // 可选参数
            'port' => 3306,
            // 可选，定义表的前缀
            'prefix' => 'wfm_',
            'allow' =>[

            ],

        ],
        "allow" =>[
            'http://localhost:8080',
            'http://49.51.160.73'
        ],
        "redis"=>[
            'host'     => 'localhost',
            'port'     => 6379,
            'database' => 2,
            'auth' => 'wu2182606'
        ]
    ];
