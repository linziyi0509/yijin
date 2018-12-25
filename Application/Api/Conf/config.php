<?php
return array(
        //数据库配置信息
    'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'base', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => '', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => 'my_', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集
    'LOAD_EXT_FILE'=>'functions',
	'PAGE_SIZE' => '10',
    'UPLOAD_CONFIG' => array(
            'maxSize'=>3145728,
            'rootPath'=>'./upload/',
            'savePath'=>'',
            'saveName'=>array('uniqid',''),
            'exts' => array('jpg', 'gif', 'png', 'jpeg'),
            'autoSub'=>true,
            'subName'=>array('date','Ymd'),
        ),
);
