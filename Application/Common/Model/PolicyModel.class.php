<?php
namespace Common\Model;
use Think\Model;
/**
 *@FILENAME:Common\Model;
 *@AUTHOR:dudongjiang;
 *@DATE:2018年03月05日;
 *@EFFORT:保单号信息
 **/
class PolicyModel extends Model{
	protected $tableName = 'my_policy';

    /**
     * @param $policynumber
     * @param $phone
     * @param int $flag
     * @return array
     * 根据保单号和手机号 查询保单信息
     * 默认查询的是手机号，可以查询营销员的信息
     * 74	是否审核
        75	待审核
        76	已审核
        77	驳回
     */
    public function policynumberPhone($policynumber, $phone, $flag=1){
        $query = M(self::getTableName());
        $where['policynumber'] = $policynumber;
        if($flag == 1){
            $where['telephone'] = $phone;
        }else{
            $where['salesmanphone'] = $phone;
        }
        //未审核的 也可以查询 冻结的不可以
        $where['isaudit'] = ['in',[76,75]];
        $arr = $query->where($where)->find();
        return $arr;
    }

    /**
     * @param $id
     * @return bool
     *  根据id 进行查询是否授权营销员
     */
    public function checkSalesById($id){
        $query = M(self::getTableName());
        $where['id'] = $id;
        $where['isauthorizesales'] = 69;
        //未审核的 也可以查询 冻结的不可以
        $where['isaudit'] = ['in',[76,75]];
        $arr = $query->where($where)->find();
        if($arr){
            //还要判断授权营销员之后 是否同步授权
            $condition['policyid'] = $id;
            $condition['issynchrogrant'] = 72;
            $psvData = M('my_policyservicevoucher')->where($condition)->select();
            if($psvData){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }
}
?>