<?php
return array(
	//'配置项'=>'配置值'
    'URL_MODEL' =>  1,
    'TOKEN'=>'yijinkj',
    //'APPID'=>'wxfa3e6bd92e24fb6e',//测试
    'APPID'=>'wx753dc34eca78fba2',//一金
    //'APPSECRET'=>'fb73828f963468ab9156c0e6a58fd5b3',//测试
    'APPSECRET'=>'e791f8eefac52d71ce577c6efdc40b5d',//一金
    //'TOKEN'=>'ld666',
    'LJSTOKEN'=>'ljs2018',
    //'APPID'=>'wx6965488870dc2b07',
    'LJSAPPID'=>'wx2ad912500e0ad57d',
    //'APPSECRET'=>'b3b1ddfc009602a8730ad58e7bdb4c19',
    'LJSAPPSECRET'=>'ec8604009b83cb300039cb789de7f014',
    'MCHID' => '1493908692',
    'MYKEY' => 'ge5XZxSklEN8QHHnC2tmVP5Kp2aO6CiA',
    //=======【证书路径设置】=====================================
	/**
     *
     * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要）
     * @var path
     */

    'SSLCERT_PATH' => dirname(dirname(__FILE__)).'/cert/apiclient_cert.pem',
    'SSLKEY_PATH' => dirname(dirname(__FILE__)).'/cert/apiclient_key.pem',
    //支付宝信息
    'ALIPAYAPPID' => '2018012902106848',
    'RECHARGE_RATIO' => array(
        array('money'=>50,'credit'=>500),
        array('money'=>100,'credit'=>1000),
        array('money'=>200,'credit'=>2000),
        array('money'=>500,'credit'=>5000),
    ),
    //中石化加解密算法的key
    "SECRETKEY"=>"Anhuiyijinkeji66",
    "SINOPEC_BASEURL"=>"http://ahtest.star3d.cn/api/",
    //邮件配置
    'MAIL_HOST' =>'smtp.163.com',//smtp服务器的名称
    'MAIL_SMTPAUTH' =>TRUE, //启用smtp认证
    'MAIL_USERNAME' =>'ejinkj88@163.com',//你的邮箱名
    'MAIL_FROM' =>'ejinkj88@163.com',//发件人地址
    'MAIL_FROMNAME'=>'安徽一金科技公司客服',//发件人姓名
    'MAIL_PASSWORD' =>'',//邮箱密码
    'MAIL_CHARSET' =>'utf-8',//设置邮件编码
    'MAIL_ISHTML' =>TRUE, // 是否HTML格式邮件
);
