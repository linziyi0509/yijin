<?php
namespace Admin\Model;
use Think\Model;
/**
 *@FILENAME:Admin\Model;
 *@AUTHOR:dudongjiang;
 *@DATE:2016年9月21日;
 *@EFFORT:验证微信分组的唯一性;
 **/
class WechatgroupModel extends Model
{
    protected $tableName = 'my_wechatgroup';
    /* 自动验证 */
    protected $_validate= array(
        /**
            self::MODEL_INSERT或者1新增数据时候验证
            self::MODEL_UPDATE或者2编辑数据时候验证
            self::MODEL_BOTH或者3全部情况下验证（默认）
         */
        array('groupname', 'require', '10000'),
        array('groupname', '', '10001', self::EXISTS_VALIDATE, 'unique', 1),
        array('groupname', '', '10002', self::EXISTS_VALIDATE, 'unique', 2)
    );
}

?>