<?php
namespace Admin\Model;
use Think\Model;
/**
 *@FILENAME:Admin\Model;
 *@AUTHOR:dudongjiang;
 *@DATE:2017年12月06日;
 *@EFFORT:充值记录模型
 **/
class RechargerecordModel extends Model{
	protected $tableName = 'my_rechargerecord';
    /* 自动验证 */
    protected $_validate= array(
        /**
            self::MODEL_INSERT或者1新增数据时候验证
            self::MODEL_UPDATE或者2编辑数据时候验证
            self::MODEL_BOTH或者3全部情况下验证（默认）
         */
		array('batch','require','不能为空！'),
        array('batch', '', '交易流水号必须唯一！', self::EXISTS_VALIDATE, 'unique', self::MODEL_BOTH),
    );
}
?>