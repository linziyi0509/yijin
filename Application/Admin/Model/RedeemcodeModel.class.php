<?php
namespace Admin\Model;
use Think\Model;
/**
 *@FILENAME:Admin\Model;
 *@AUTHOR:dudongjiang;
 *@DATE:2017年11月02日;
 *@EFFORT:验证兑换码的数据
 **/
class RedeemcodeModel extends Model
{
    protected $tableName = 'my_redeemcode';
    /* 自动验证 */
    protected $_validate= array(
        /**
            self::MODEL_INSERT或者1新增数据时候验证
            self::MODEL_UPDATE或者2编辑数据时候验证
            self::MODEL_BOTH或者3全部情况下验证（默认）
         */
        array('code', 'require', '10000'),
        array('code', '', '10001', self::EXISTS_VALIDATE, 'unique', 1),
        array('code', '', '10002', self::EXISTS_VALIDATE, 'unique', 2)
    );
}
?>