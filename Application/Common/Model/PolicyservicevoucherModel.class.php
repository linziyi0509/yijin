<?php
namespace Common\Model;
use Think\Model;
/**
 *@FILENAME:Common\Model;
 *@AUTHOR:dudongjiang;
 *@DATE:2018年03月05日;
 *@EFFORT:保单号服务券关联信息
 **/
class PolicyservicevoucherModel extends Model{
	protected $tableName = 'my_policyservicevoucher';

    /**
     * @param $policyDaya
     * @param int $flag
     * @return bool
     * 通过保单记录 进行查询对应的服务券信息 并授权给当前用户
     * 68	是否授权营销员 69	是  70	否
     * 注意：后台需要激活 前台才可以使用
     * 71	是否同步授权 72	是  73	否
     */
    public function policytoserviceInfo($policyData, $flag=1){
        $query = M(self::getTableName());
        $servicePolicyData = $query->where("policyid=".$policyData['id'])->select();
        //判断是否授权营销员
        $model = new Model();
        $model->startTrans();
        $flag = [];
        foreach($servicePolicyData as $key=>$val){
            if($val['wechatuserid'] && $val['starttime']){
                return false;
            }
            if($flag == 2){
                if($val['issynchrogrant'] == 72 && $val['isusergrantsales'] == 79){//当同步授权的时候 需要确认 用户是否授权给营销员了
                    $arr['wechatuserid'] = session('userinfo')['id'];
                    $arr['updatetime'] = date('Y-m-d H:i:s');
                    $arr['starttime'] = date('Y-m-d H:i:s');
                    $arr['endtime'] = date('Y-m-d H:i:s',strtotime('+ 365 days'));
                    $flag[] = $model->table(self::getTableName())->where('id='.$val['id'])->save($arr);
                }else{
                    return 2;
                }
            }else{
                if($val['issynchrogrant'] == 73){
                    $arr['wechatuserid'] = session('userinfo')['id'];
                    $arr['updatetime'] = date('Y-m-d H:i:s');
                    $arr['starttime'] = date('Y-m-d H:i:s');
                    $arr['endtime'] = date('Y-m-d H:i:s',strtotime('+ 365 days'));
                    $flag[] = $model->table(self::getTableName())->where('id='.$val['id'])->save($arr);
                }
            }
        }
        if(in_array(false,$flag)){
            $model->rollback();
            return false;
        }
        $model->commit();
        return true;
    }

    /**
     * @param $param
     * @return bool
     * 前台用户通过页面授权-后台填写的授权营销员
     */
    public function grantSalesById($param){
        $query = M(self::getTableName());
        $servicePolicyData = $query->where("policyid=".$param['policyid'])->select();
        $where['id'] = $param['policyid'];
        $where['salesmanphone'] = $param['salesmanphone'];
        $policyData = M('my_policy')->where($where)->find();
        if(!$policyData){
            return '营销员手机号不对';
        }
        //判断是否授权营销员
        $model = new Model();
        $model->startTrans();
        $flag = [];
        $arr = ['noagree'=>80,'agree'=>79];
        foreach($servicePolicyData as $key=>$val){
            if($val['wechatuserid'] && $val['starttime']){

            }else{
                $data['updatetime'] = date('Y-m-d H:i:s');
                $data['isusergrantsales'] = $arr[$param['grant']];
                $flag[] = $model->table(self::getTableName())->where('id='.$val['id'])->save($data);
            }
        }
        if(in_array(false,$flag)){
            $model->rollback();
            return false;
        }
        $model->commit();
        return true;
    }

    /**
     * @param $wechatuserid
     * @return bool
     * 根据用户id查询是否多余存在
     */
    public function checkWechatuserunique($wechatuserid){
        $wechatuserData = M(self::getTableName())->where(["wechatuserid"=>$wechatuserid])->order("createtime desc")->limit(1)->find();
        $time = time()-300*24*60*60 - strtotime($wechatuserData['createtime']);
        if($time<0){
            return false;
        }else{
            return true;
        }
    }
    /**
     * @param $policyid
     * @return array|string
     *  根据保单号进行操作
     * 使用-生成订单-订单详情-返回数据详情等
     */
    public function usingPolicyLogic($policyservicevoucherid){
        $flag = [];
        $model = new Model();
        $model->startTrans();
        //首先查看当前保单是否激活
        $policyInfo = M(self::getTableName())
            ->join('my_policy ON my_policyservicevoucher.policyid = my_policy.id')
            ->field('isaudit,my_policyservicevoucher.id,my_policyservicevoucher.policyid,my_policyservicevoucher.servicevoucherid,my_policyservicevoucher.merchantid,my_policyservicevoucher.petroinfoid')
            ->where(['my_policyservicevoucher.id'=>$policyservicevoucherid,'usestatus'=>33])
            ->find();
        if(empty($policyInfo)){
            return '保单号信息有误，请联系管理员';
        }
        if(intval($policyInfo['isaudit']) != 76){
            return '本券需发券商审核后方可使用，请耐心等待审核。如持续未审核请联系发券商或在线客服。';
        }
        //服务券信息验证
        $servicevoucher = M('my_servicevoucher')
            ->field('id,name,integral,type,money')
            ->where(['id'=>$policyInfo['servicevoucherid']])
            ->find();
        if(!$servicevoucher){
            return '服务券信息有误，请联系管理员';
        }
        //查询对应的石油码---对应的类型   类型不一样 处理的逻辑不一样
        switch($servicevoucher['type']){
            case 92:
                    return self::shjyq($policyservicevoucherid, $flag, $model, $policyInfo, $servicevoucher);//石化加油券
                break;
            case 93:
            case 94:
                    return self::reedcodeResult($policyservicevoucherid, $flag, $model, $policyInfo, $servicevoucher);//服务券和充值卡
                break;
                case 95:
                    return self::jfkResult($model,$policyservicevoucherid,$servicevoucher,$policyInfo);//积分卡
                break;
        }
    }
    protected function shjyq($policyservicevoucherid, $flag, $model, $policyInfo, $servicevoucher){
        //查询对应兑换码的数据
        $arr = [
            100 => 83,
            50 => 82
        ];
        $facevalue = $arr[intval($servicevoucher['money'])];
        $petroinfocode = M('my_petroinfo')
            ->field('id')
            ->where("facevalue = %d and usestatus = %d and disabletime >= '%s'", array($facevalue, 33, date('Y-m-d 00:00:00')))
            ->select();
        if(!$petroinfocode){
            return '石化保单码信息有误，请联系管理员';
        }
        $petroinfocodeid = $petroinfocode[array_rand($petroinfocode,1)]['id'];//取出一个石化保单码
        //修改关联信息的状态
        $csvData = array(
            'usestatus' =>  32,
            'usetime'   =>  date('Y-m-d H:i:s',time()),//使用时间
        );
        //修改状态和使用时间
        $flag[] = $model->table('my_policyservicevoucher')->where(['id'=>$policyservicevoucherid])->save($csvData);
        //订单---创建---开始
        $poData = array(
            'createtime'  => date('Y-m-d H:i:s',time()),
            'wechatuserid' => session('userinfo')['id'],
            'username'      =>  session('userinfo')['nickname'],
            'telephone'     =>  session('userinfo')['phone'],
            'credit'        =>  $servicevoucher['integral'],  //积分
            //'money'         =>  round($servicevoucher['integral']/10,2), //金额(元)
            'money'         =>  0, //金额(元)
            'paidflag'      =>  36,  //订单状态
            'ordernum'      =>  date('YmdHis',time()).rand(100,999), //订单号
            'batch'         =>  date('Y-m-d H:i:s',time()), //交易号
            'payingtype'    =>  42,
            'ipaddr'        =>  get_client_ip(),
            'ordertype'     =>  39
        );
        $payingordersid = $model->table('my_payingorders')->add($poData);
        //订单---创建---结束
        if($payingordersid){
            $flag[] = true;
            $buyingmodofcustomerData = [
                'createtime'  => date('Y-m-d H:i:s',time()),
                'updatetime'  => date('Y-m-d H:i:s',time()),
                'payingordersid'  => $payingordersid,
                'servicevoucherid'  => $policyInfo['servicevoucherid'],
                'usestatus'  => 32,
                'credit'  => $servicevoucher['integral'],
                'policyservicevoucherid'=>$policyInfo['id'],
                'petroinfoid'=>$petroinfocodeid//随机出对应的兑换码
            ];
            $buyingmodofcustomerid = $model->table('my_buyingmodofcustomer')->add($buyingmodofcustomerData);
            if($buyingmodofcustomerid){
                $flag[] = true;
                //修改石油码的使用情况-开始 - 根据类型
                $petroinfocodeData = [
                    'updatetime' => date('Y-m-d H:i:s',time()),
                    'wechatuserid' =>session('userinfo')['id'],
                    'usestatus' => 32,
                    'policyservicevoucherid'	=> $policyservicevoucherid
                ];
                $petroinfocoderes = $model->table('my_petroinfo')->where(['id'=>$petroinfocodeid])->save($petroinfocodeData);
                if($petroinfocoderes){
                    $flag[] = true;
                }else{
                    $flag[] = false;
                }
                //修改石油码的使用情况-结束
            }else{
                $flag[] = false;
            }
        }else{
            $flag[] = false;
        }
        if(empty($flag) || in_array(false,$flag)){
            $model->rollback();
            return ['code'=>101,'string'=>'使用失败'];
        }else{
            $model->commit();
            //查询出石油码 和 石油码生成的图片
            $servicevoucherid = M('my_policyservicevoucher')->where(['id'=>$policyservicevoucherid])->getField('servicevoucherid');
            $servicevoucherData = M('my_servicevoucher')->where(['id'=>$servicevoucherid])->field('exchangeurl,type')->find();
            $petrocode = M('my_petroinfo')->where(['id' => $petroinfocodeid])->field('typecode,checkcode')->find();
            $content = $petrocode['typecode'].".".$petrocode['checkcode'];
            return ['code'=>200,'string'=>'使用成功','content'=>$content,'type'=>$servicevoucherData['type']];
        }
    }
    /**
     * @param $policyservicevoucherid
     * @param $flag
     * @param $model
     * @param $policyInfo
     * @param $servicevoucher
     * @return array|string
     * 处理类型为石化充值卡、服务券的服务券
     */
    protected function reedcodeResult($policyservicevoucherid, $flag, $model, $policyInfo, $servicevoucher){
        //查询对应兑换码的数据
        $redeemcode = M('my_redeemcode')
            ->field('id')
            ->where("servicevoucherid = %d and status = %d and usestatus = %d", array($policyInfo['servicevoucherid'], 24, 27))
            ->select();
        if(!$redeemcode){
            return '兑换码信息有误，请联系管理员';
        }
        $redeemcodeid = $redeemcode[array_rand($redeemcode,1)]['id'];//取出一个兑换码
        //修改关联信息的状态
        $csvData = array(
            'usestatus' =>  32,
            'usetime'   =>  date('Y-m-d H:i:s',time()),//使用时间
        );
        //修改状态和使用时间
        $flag[] = $model->table('my_policyservicevoucher')->where(['id'=>$policyservicevoucherid])->save($csvData);
        //订单---创建---开始
        $poData = array(
            'createtime'  => date('Y-m-d H:i:s',time()),
            'wechatuserid' => session('userinfo')['id'],
            'username'      =>  session('userinfo')['nickname'],
            'telephone'     =>  session('userinfo')['phone'],
            'credit'        =>  $servicevoucher['integral'],  //积分
            //'money'         =>  round($servicevoucher['integral']/10,2), //金额(元)
            'money'         =>  0, //金额(元)
            'paidflag'      =>  36,  //订单状态
            'ordernum'      =>  date('YmdHis',time()).rand(100,999), //订单号
            'batch'         =>  date('Y-m-d H:i:s',time()), //交易号
            'payingtype'    =>  42,
            'ipaddr'        =>  get_client_ip(),
            'ordertype'     =>  39
        );
        $payingordersid = $model->table('my_payingorders')->add($poData);
        //订单---创建---结束
        if($payingordersid){
            $flag[] = true;
            $buyingmodofcustomerData = [
                'createtime'  => date('Y-m-d H:i:s',time()),
                'updatetime'  => date('Y-m-d H:i:s',time()),
                'payingordersid'  => $payingordersid,
                'servicevoucherid'  => $policyInfo['servicevoucherid'],
                'usestatus'  => 32,
                'credit'  => $servicevoucher['integral'],
                'policyservicevoucherid'=>$policyInfo['id'],
                'redeemcodeid'=>$redeemcodeid//随机出对应的兑换码
            ];
            $buyingmodofcustomerid = $model->table('my_buyingmodofcustomer')->add($buyingmodofcustomerData);
            if($buyingmodofcustomerid){
                $flag[] = true;
                //修改石油码的使用情况-开始 - 根据类型
                //修改兑换码的使用情况
                $redeemcodeData = [
                    'updatetime' => date('Y-m-d H:i:s',time()),
                    'wechatuserid' =>session('userinfo')['id'],
                    'usestatus' => 26,
                    'policyservicevoucherid'	=> $policyservicevoucherid
                ];
                $redeemcoderes = $model->table('my_redeemcode')->where(['id'=>$redeemcodeid])->save($redeemcodeData);
                if( $redeemcoderes){
                    $flag[] = true;
                }else{
                    $flag[] = false;
                }
                //修改石油码的使用情况-结束
            }else{
                $flag[] = false;
            }
        }else{
            $flag[] = false;
        }
        if(empty($flag) || in_array(false,$flag)){
            $model->rollback();
            return ['code'=>101,'string'=>'使用失败'];
        }else{
            $model->commit();
            //查询出石油码 和 石油码生成的图片
            $servicevoucherid = M('my_policyservicevoucher')->where(['id'=>$policyservicevoucherid])->getField('servicevoucherid');
            $servicevoucherData = M('my_servicevoucher')->where(['id'=>$servicevoucherid])->field('exchangeurl,type')->find();
            $code = M('my_redeemcode')->where(['policyservicevoucherid' => $policyservicevoucherid])->getField('code');
            return ['code'=>200,'string'=>'使用成功','url'=>$servicevoucherData['exchangeurl'],'codes'=>$code,'type'=>$servicevoucherData['type']];
        }
    }
    protected function jfkResult($model,$policyservicevoucherid,$servicevoucher,$policyInfo){
        //修改关联信息的状态
        $csvData = array(
            'usestatus' =>  32,
            'usetime'   =>  date('Y-m-d H:i:s',time()),//使用时间
        );
        //修改状态和使用时间
        $flag[] = $model->table('my_policyservicevoucher')->where(['id'=>$policyservicevoucherid])->save($csvData);
        //订单---创建---开始
        $poData = array(
            'createtime'  => date('Y-m-d H:i:s',time()),
            'wechatuserid' => session('userinfo')['id'],
            'username'      =>  session('userinfo')['nickname'],
            'telephone'     =>  session('userinfo')['phone'],
            'credit'        =>  $servicevoucher['integral'],  //积分
            'money'         =>  0, //金额(元)
            'paidflag'      =>  36,  //订单状态
            'ordernum'      =>  date('YmdHis',time()).rand(100,999), //订单号
            'batch'         =>  date('Y-m-d H:i:s',time()), //交易号
            'payingtype'    =>  42,
            'ipaddr'        =>  get_client_ip(),
            'ordertype'     =>  39
        );
        $payingordersid = $model->table('my_payingorders')->add($poData);
        //订单---创建---结束
        if($payingordersid){
            $flag[] = true;
            //需要将积分卡中的积分加入到用户中
            $wechatuserInfo = M('my_wechatuser')->where(['id'=>session('userinfo')['id']])->find();
            if($wechatuserInfo){
                $flag[] = true;
                $wechatuserInfoUp['credits'] = intval($wechatuserInfo['credits']) + intval($servicevoucher['integral']);
                $wechatuserInfoUp['updatetime'] = date('Y-m-d H:i:s',time());
                $wechatuserinfoUpRes = M('my_wechatuser')->where(['id'=>session('userinfo')['id']])->save($wechatuserInfoUp);
                if($wechatuserinfoUpRes){
                    $flag[] = true;
                    $buyingmodofcustomerData = [
                        'createtime'  => date('Y-m-d H:i:s',time()),
                        'updatetime'  => date('Y-m-d H:i:s',time()),
                        'payingordersid'  => $payingordersid,
                        'servicevoucherid'  => $policyInfo['servicevoucherid'],
                        'usestatus'  => 32,
                        'credit'  => $servicevoucher['integral'],
                        'policyservicevoucherid'=>$policyInfo['id'],
                    ];
                    $buyingmodofcustomerid = $model->table('my_buyingmodofcustomer')->add($buyingmodofcustomerData);
                    if($buyingmodofcustomerid){
                        $flag[] = true;
                    }else{
                        $flag[] = false;
                    }
                }else{
                    $flag[] = false;
                }
            }else{
                $flag[] = false;
            }
        }else{
            $flag[] = false;
        }
        if(empty($flag) || in_array(false,$flag)){
            $model->rollback();
            return ['code'=>101,'string'=>'使用失败'];
        }else{
            $model->commit();
            return ['code'=>200,'string'=>'使用成功','type'=>$servicevoucher['type']];
        }
    }
}
?>