<?php
namespace Common\Model;
use Think\Model;
/**
 *@FILENAME:Common\Model;
 *@AUTHOR:dudongjiang;
 *@DATE:2018年03月12日;
 *@EFFORT:营销员信息
 **/
class SalesmaninfoModel extends Model{
	protected $tableName = 'my_salesmaninfo';

    /**
     * @param $idcard
     * @param $phone
     * @return mixed
     * 根据身份证号和手机号进行查询
     */
    public function idcardPhone($idcard, $phone){
        $query = M(self::getTableName());
        $where['phone'] = $phone;
        $where['idcard'] = $idcard;
        $arr = $query->where($where)->find();
        return $arr;
    }
    /**
     * @param $param
     * @return bool
     * 根据数据进行修改绑定微信账号
     */
    public function updateSales($param){
        $query = M(self::getTableName());
        $where['id'] = $param['id'];
        $data['wechatuserid'] = session('userinfo')['id'];
        $data['updatetime'] = date('Y-m-d H:i:s');
        $arr = $query->where($where)->save($data);
        if($arr){
            return true;
        }else{
            return $query->getError();
        }
    }
}
?>