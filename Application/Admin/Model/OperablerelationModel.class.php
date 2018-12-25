<?php
namespace Admin\Model;
use Think\Model;
/**
 *@FILENAME:Admin\Model;
 *@AUTHOR:dudongjiang;
 *@DATE:2017年12月06日;
 *@EFFORT:商户模型
 **/
class OperablerelationModel extends Model{
	protected $tableName = 'my_operablerelation';

    /**
     * @return string
     * 根据登录用户查询用户所属商户和可操作的商户的集合
     */
    public function userhavemerchat(){
        $adminInfo = M('my_admin')->where('id='.session('adminid'))->field('merchantid')->select();
        $relationInfo = M(self::getTableName())->where('adminid='.session('adminid'))->field('merchantid')->select();
        if(is_array($relationInfo)){
            $data = array_merge($relationInfo, $adminInfo);
        }else{
            $data = $adminInfo;
        }
        $arr = [];
        foreach($data as $key=>$val){
            if(!in_array($val['merchantid'],$arr)){
                $arr[] = $val['merchantid'];
            }
        }
        $idSrt = implode(',',$arr);
        return $idSrt;
    }
}
?>