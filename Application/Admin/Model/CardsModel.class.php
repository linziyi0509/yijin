<?php
namespace Admin\Model;
use Think\Model;
/**
 *@FILENAME:Admin\Model;
 *@AUTHOR:dudongjiang;
 *@DATE:2017年11月02日;
 *@EFFORT:卡片模型
 **/
class CardsModel extends Model{
	protected $tableName = 'my_cards';
    /* 自动验证 */
    protected $_validate= array(
        /**
            self::MODEL_INSERT或者1新增数据时候验证
            self::MODEL_UPDATE或者2编辑数据时候验证
            self::MODEL_BOTH或者3全部情况下验证（默认）
         */
        array('cardno,cardpwd', 'require', '10000'),
        array('cardno,cardpwd', '', '10001', self::EXISTS_VALIDATE, 'unique', 1),
        array('cardno,cardpwd', '', '10002', self::EXISTS_VALIDATE, 'unique', 2)
    );

    /**
     * @return string
     * 通过序列号进行获取 唯一的卡号  实现序列的脚本
     */
	public function getCardno(){
		$model = new Model();
		$cardData = $model->query("select nextval('cardsseq') as cardsseq");
        $data = $cardData[0];
		$prefix = '00000000';
		if(strlen($data["cardsseq"])<8){
			$strlen = 8-strlen($data["cardsseq"]);
			$cardno = substr($prefix,0,$strlen).$data["cardsseq"];
		}else{
			$cardno = $data["cardsseq"];
		}
		return $cardno;
	}
}
?>