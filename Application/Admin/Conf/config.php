<?php
return array(
        //数据库配置信息
    'DB_TYPE' => 'mysql', // 数据库类型
    /*'DB_HOST' => '47.96.2.165', // 服务器地址
    'DB_NAME' => 'base', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => 'vjEj31NP8x', // 密码*/
    'DB_HOST' => '127.0.0.1', // 服务器地址
    'DB_NAME' => 'base', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => 'root', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => '', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集
	/*
	'DB_TYPE' => 'mysql', // 数据库类型
    'DB_HOST' => '47.94.158.8', // 服务器地址
    'DB_NAME' => 'base', // 数据库名
    'DB_USER' => 'root', // 用户名
    'DB_PWD' => 'vjEj31NP8x', // 密码
    'DB_PORT' => 3306, // 端口
    'DB_PREFIX' => '', // 数据库表前缀
    'DB_CHARSET'=> 'utf8', // 字符集*/
    'LOAD_EXT_FILE'=>'functions',
	'TMPL_ACTION_ERROR' => THINK_PATH . 'Tpl/dispatch_jump.tpl',
	//默认成功跳转对应的模板文件
	'TMPL_ACTION_SUCCESS' => THINK_PATH . 'Tpl/dispatch_jump.tpl',
    'ACTION'=>array('index','add','update','del','delall','xiangqing','auditall','import','active','morecondition','orderreport')//active=---兑换码激活   activefrozen---卡片激活  morecondition ---发放报表   orderreport---订单报表
);
