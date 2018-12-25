<?php
namespace Admin\Model;
use Think\Model;
/**
 *@FILENAME:Admin\Model;
 *@AUTHOR:dudongjiang;
 *@DATE:2017年12月06日;
 *@EFFORT:商户模型
 **/
class MerchantModel extends Model{
	protected $tableName = 'my_merchant';
    /* 自动验证 */
    protected $_validate= array(
        /**
            self::MODEL_INSERT或者1新增数据时候验证
            self::MODEL_UPDATE或者2编辑数据时候验证
            self::MODEL_BOTH或者3全部情况下验证（默认）
         */
		array('name,merchantnum','require','不能为空！'),
        array('name', '', '商户名称必须唯一！', self::EXISTS_VALIDATE, 'unique', self::MODEL_BOTH),
        array('merchantnum', '' , '商户号必须唯一！', self::EXISTS_VALIDATE, 'unique', self::MODEL_BOTH),
		array('phone','11','电话号码格式不对！', self::EXISTS_VALIDATE, 'length', self::MODEL_INSERT),
    );
}
?>