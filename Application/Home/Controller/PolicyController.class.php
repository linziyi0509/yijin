<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/2/12
 * Time: 16:27
 */

namespace Home\Controller;
use Common\Model\PolicyModel;
use Common\Model\PolicyservicevoucherModel;
use Think\Controller;
use Think\Model;

class PolicyController extends BaseController
{
    /**
     * 前台-一个用户，三个月内，不允许再次进入问卷调查，直接跳转到保单服务券
     * post 接收值的时候 需要注意 是营销员还是普通用户
     * 图形验证码 只在发送短信验证码时候验证 一次 失效
     */
    public function index(){
        $psvm = new PolicyservicevoucherModel();
        if(!$psvm->checkWechatuserunique(session('userinfo')['id'])){
            exit($this->redirect('Policy/servicepage'));
        }
        if(IS_POST){
            $policynumber = I('post.policynumber','','trim');
            $telephone = I('post.telephone','','trim');
            $phonecode = I('post.phonecode','','trim');
            if ( empty($telephone) || strlen($telephone) != 11 || empty($policynumber)) {
                return $this->ajaxReturn(['code'=>102,'string'=>'请输入正确的手机号码']);
            }
            if(intval(S($policynumber.'sms'. $telephone)) != intval($phonecode)){
                $this->ajaxReturn(['code'=>101,'string'=>'短信验证码填写错误']);
            }
            //验证通过清除缓存信息
            S($policynumber.'sms'. $telephone,'null');
            //获取保单号信息和关联服务券信息
            $model = new PolicyModel();
            $policyData = $model->policynumberPhone($policynumber,$telephone);
            if($policyData){
                $relationModel = new PolicyservicevoucherModel();
                if(!session('userinfo')['id']){
                    $this->ajaxReturn(['code'=>101,'string'=>'请授权一金公众号']);
                }
                $flag = $relationModel->policytoserviceInfo($policyData);
                if($flag === true){
                    $this->ajaxReturn(['code'=>200,'string'=>php_en((string) $policyData['id'])]);
                }else if($flag == 2){
                    $this->ajaxReturn(['code'=>101,'string'=>'营销员信息未经用户授权！']);
                }else{
                    $this->ajaxReturn(['code'=>101,'string'=>'信息有误,或信息已使用']);
                }
            }else{
                $this->ajaxReturn(['code'=>101,'string'=>'此信息不存在']);
            }
        }else{
            $this->display();
        }
    }
    public function stepone($policyid){
        $this->policyid = $policyid;
        $this->display();
    }
    public function steptwo($policyid){
        $this->policyid = $policyid;
        $this->display();
    }
    public function stepthree($policyid){
        $this->policyid = $policyid;
        $this->display();
    }
    public function stepfour($policyid){
        $policyid = php_de($policyid);
        writelog('Policy---stepfour---policyid：',$policyid);
        //验证 --- 此id下的信息是否授权营销员
        $model = new PolicyModel();
        $result = $model->checkSalesById($policyid);
        $data = [
            'policyid'=>$policyid,
            'isshow'=>$result
        ];
        $this->assign('data',$data);
        $this->display();
    }
    /**
     * 列表查询数据
     * 服务券信息
     */
    public function servicepage()
    {
        if ( IS_POST ) {
            $id = I('post.id','','trim');
            $psvModel = new PolicyservicevoucherModel();
            $result = $psvModel->usingPolicyLogic($id);
            if(is_array($result)){
                $this->ajaxReturn($result);
            }else{
                $this->ajaxReturn(['code'=>101,'string'=>$result]);
            }

            exit();
        }
        //全部的
        $table = M("")
            ->table('my_policyservicevoucher s,my_servicevoucher r,my_policy p')
            ->field('s.id,s.wechatuserid,s.servicevoucherid,s.usestatus,s.starttime,s.endtime,r.name,r.integral,r.instructions,r.icon,r.exchangeurl,p.isaudit')
            ->where("s.servicevoucherid = r.id and p.id = s.policyid and s.wechatuserid = ".session('userinfo')['id']." and s.starttime is not null and s.endtime is not null")
            ->select();
        foreach($table as $key=>$val){
            $table[$key]["starttime"] = strtotime($val["starttime"]);
            $table[$key]["endtime"] = strtotime($val["endtime"]);
        }
        $time = time();
        $useCode = []; //已使用
        $usedCode = []; //未使用
        $overCode = [];  //已过期
        foreach ($table as $k => $val ) {
            if ($val['usestatus'] == 32 && $val['isaudit'] == 76 && $val['endtime'] > $time) {//已使用
                $table[$k]['flag'] = 0;
                $useCode[] = $val;
            }else if ($val['usestatus'] == 33 && $val['endtime'] > $time) {//未使用-未审核点击使用 提示
                $usedCode[] = $val;
                $table[$k]['flag'] = 1;
            }else if ($val['endtime'] < $time){
                //获取已经过期的
                $overCode[] = $val;
                $table[$k]['flag'] = -1;
            }
        }
        $this->list = [
            'all'       =>  $table,
            'useCode'   =>  $useCode,
            'usedCode'  =>  $usedCode,
            'overCode'  =>  $overCode
        ];
        $this->display();
    }

    /**
     * 用户授权营销员
     * 根据保单号id
     */
    public function grantSales(){
        $data['policyid'] = I('post.policyid','','trim');
        $data['grant'] = I('post.grant','','trim');
        $data['salesmanphone'] = I('post.salesmanphone','','trim');
        writelog('polict---grantSales---data---',$data);
        $psvm = new PolicyservicevoucherModel();
        $result = $psvm->grantSalesById($data);
        if($result === true){
            $this->ajaxReturn(['code'=>200,'string'=>'授权成功']);
        }else if($result == true){
            $this->ajaxReturn(['code'=>102,'string'=>$result]);
        }else{
            $this->ajaxReturn(['code'=>101,'string'=>'用户授权营销员失败！']);
        }
    }
    public function verify(){
        //清除缓存
        ob_end_clean();
        $verify = new \Think\Verify();
        //$verify->fontSize = 30;
        $verify->length   = 4;
        $verify->useNoise = false;
        $verify->useCurve = false;
        $verify->codeSet = '0123456789';
        $verify->entry();
    }
    //发送验证码
    public function phonesms()
    {
        $policynumber = I('post.policynumber','','trim');
        $telephone = I('post.telephone','','trim');
        $code = I('post.code','','trim');
        if ( empty($telephone) || strlen($telephone) != 11 || empty($policynumber)) {
            return $this->ajaxReturn(['code'=>102,'string'=>'请输入正确的手机号码']);
        }
        $verify = new \Think\Verify();
        if (!$verify->check($code) ) {
            $this->ajaxReturn(['code'=>101,'string'=>'验证码填写错误']);
        }
        //判断保单号和手机号在后台是否存在此记录 不存在 不允许发送验证码
        $model = new PolicyModel();
        $policyData = $model->policynumberPhone($policynumber,$telephone);
        if($policyData){
            $code = rand(1000,9999);
            S($policynumber.'sms'.$telephone, $code, '300');
            writelog('sms:',$policynumber.'sms'.$telephone."_--------------".S($policynumber.'sms'.$telephone));
            vendor('api_sdk.SmsDemo');
            $sms = new \SmsDemo();
            $data = [
                'phone'=>$telephone,
                'code'=>$code,
            ];
            $response = $sms ->sendSms($data);
            writelog('验证码返回信息：-----',$response);
            if ($response->Code == 'OK' ) {
                return $this->ajaxReturn(['code'=>200,'string'=>'验证码发送成功,请继续验证信息']);
            }else{
                return $this->ajaxReturn(['code'=>101,'string'=>'验证码发送失败，请重新发送']);
            }
        }else{
            $this->ajaxReturn(['code'=>101,'string'=>'此信息不存在']);
        }
    }
    /**
     * salesman index 营销员的入口
     * 一个微信只能绑定一个微信
     */
    public function salesmanindex(){
        $salesmanData = M('my_salesmaninfo')->where('wechatuserid='.session('userinfo')['id'])->find();
        if($salesmanData){
            $this->display('Salesmaninfo/exchange');
        }else{
            $this->display();
        }
    }
    //点击查看内容
    public function getFind() {
        $id = I('post.id');
        $policyservicevoucherInfo = M('my_policyservicevoucher')->where(['id'=>$id])->field('servicevoucherid')->find();
        //加入类型判断
        $servicevoucherData = M('my_servicevoucher')->where(['id'=>$policyservicevoucherInfo['servicevoucherid']])->field('exchangeurl,type')->find();
        //policyservicevoucherid 才是
        $code = M('my_redeemcode')->where(['policyservicevoucherid' => $id])->getField('code');
        $petrocode = M('my_petroinfo')->where(['policyservicevoucherid' => $id])->field('id,typecode,checkcode')->find();
        $bocData = M('my_buyingmodofcustomer')->where(['policyservicevoucherid' => $id])->field('usestatus')->find();
        if($bocData['usestatus'] == 33){
            $buyingmodofcustomerRes = M('my_buyingmodofcustomer')->where(['policyservicevoucherid' => $id])->save(['usestatus'=>32]);
        }
        $content = $petrocode['typecode'].".".$petrocode['checkcode'];
        $this->ajaxReturn(['url'=>$servicevoucherData['exchangeurl'],'code'=>$code,'type'=>$servicevoucherData['type'],'content'=>$content]);
    }
    /**
     * 转让列表数据 --- 只显示未使用的券
     */
    public function TransferLst(){
        if ( IS_POST ) {
            $this->selPhone();
            //使用事务 进行数据的插入
            $model = new Model();
            $model->startTrans();//开启事务
            $flag = [];
            $id = I('post.id');
            $price = 0;
            $buyingmodofcustomerData = [];
            foreach ($id as $key => $val)
            {
                if (!empty($val)) {
                    $policysv = M('my_policyservicevoucher')
                        ->field('servicevoucherid,policyid')
                        ->where(['id'=>$val,'usestatus'=>33])//未使用
                        ->find();
                    //需要对保单号关联数据进行验证
                    if( !$policysv ){
                        $this->ajaxReturn(['code'=>101,'string'=>'保单号关联信息有误，请联系管理员']);
                    }
                    $policyData = M('my_policy')->where(['id'=>$policysv['policyid'],'isaudit'=>76])->find();
                    if( !$policyData ){
                        $this->ajaxReturn(['code'=>101,'string'=>'本券需发券商审核后方可使用，请耐心等待审核。如持续未审核请联系发券商或在线客服。']);
                    }
                    $servicevoucher = M('my_servicevoucher')
                        ->field('id,name,integral')
                        ->where(['id'=>$policysv['servicevoucherid']])
                        ->find();
                    if(!$servicevoucher){
                        $this->ajaxReturn(['code'=>101,'string'=>'服务券信息有误，请联系管理员']);
                    }
                    //查询对应兑换码的数据
//                    $where =array(
//                        'servicevoucherid'=>(int)$policysv['servicevoucherid'],'status'=>24,'usestatus' =>27
//                    );
//                    $redeemcode = M('my_redeemcode')->field('id')->where($where)->select();
//                    if(!$redeemcode){
//                        $this->ajaxReturn(['code'=>101,'string'=>'信息有误，请联系管理员']);
//                    }
//                    $redeemcodeid = $redeemcode[array_rand($redeemcode,1)]['id'];//取出一个兑换码
                    $csvData = array(
                        'usestatus' =>  50,
                        'usetime'   =>  date('Y-m-d H:i:s',time()),//使用时间
                    );
                    //修改状态和使用时间
                    $flag[] = $model->table('my_policyservicevoucher')->where(['id'=>$val])->save($csvData);
                    $price += (int)$servicevoucher['integral'];
                    //订单详情
                    $buyingmodofcustomerData[] = [
                        'createtime'  => date('Y-m-d H:i:s',time()),
                        'updatetime'  => date('Y-m-d H:i:s',time()),
                        'servicevoucherid'  => $policysv['servicevoucherid'],
                        'usestatus'  => 50,
                        'credit'  => $servicevoucher['integral'],
                        'redeemcodeid'=>''//随机出对应的兑换码
                    ];
                }
            }
            //生成订单
            $poData = array(
                'createtime'    => date('Y-m-d H:i:s',time()),
                'wechatuserid'  => session('userinfo')['id'],
                'username'      =>  session('userinfo')['nickname'],
                'telephone'     =>  session('userinfo')['phone'],
                'credit'        =>  $price,  //积分
                'money'         =>  round($price/100,2), //金额(元)
                'paidflag'      =>  36,  //订单状态
                'ordernum'      =>  date('YmdHis',time()).rand(100,999), //订单号
                'batch'         =>  date('Y-m-d H:i:s',time()), //交易号
                'payingtype'    =>  42,
                'ipaddr'        =>  get_client_ip(),
                'ordertype'     =>  40,
            );
            $payingordersid = $model->table('my_payingorders')->add($poData);
            if($payingordersid){
                $flag[] = true;
                foreach ($buyingmodofcustomerData as $k=>$va ) {
                    $buyingmodofcustomerData[$k]['payingordersid']= $payingordersid;
                    //修改兑换码的使用情况
//                    $redeemcodeData = [
//                        'updatetime' => date('Y-m-d H:i:s',time()),
//                        'wechatuserid' =>session('userinfo')['id'],
//                        'usestatus' => 26
//                    ];
//                    $redeemcoderes = $model->table('my_redeemcode')->where(['id'=>$va['redeemcodeid']])->save($redeemcodeData);
                }
                $buyingmodofcustomerid = $model->table('my_buyingmodofcustomer')->addAll($buyingmodofcustomerData);
//                if($buyingmodofcustomerid && $redeemcoderes){
                if($buyingmodofcustomerid){
                    //用户表数据增加积分
                    $user =  $model->table("my_wechatuser")->where(['id'=>session('userinfo')['id']])->setInc('credits',$price);
                    if ($user) {
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
            if(empty($flag) || in_array(false,$flag)){
                $model->rollback();
                $this->ajaxReturn(['code'=>101,'string'=>'使用失败']);
            }else{
                $model->commit();
                $this->ajaxReturn(['code'=>200,'string'=>'使用成功']);
            }
            return;
        }
        //查询数据
        $transData = M("")
            ->table('my_policyservicevoucher p,my_servicevoucher s')
            ->field('p.id,p.wechatuserid,p.servicevoucherid,p.usestatus,p.status,p.starttime,p.endtime,s.name,s.integral,s.transfer')
            ->where("p.servicevoucherid = s.id and p.wechatuserid = ".session('userinfo')['id']." and p.starttime is not null and p.endtime is not null and p.usestatus = 33")
            ->select();
        //查询数据
        $time = time();
        foreach($transData as $key=>$val){
            if(strtotime($val['endtime']) > $time){
                $transData[$key]["starttime"] = strtotime($val["starttime"]);
                $transData[$key]["endtime"] = strtotime($val["endtime"]);
            }
        }
        $this->transData = $transData;
        $this->display();
    }
    /**
     * 实时刷新条形码
     */
    public function barcodeInfo(){
        $content = $_GET['content'];
        return barcode($content);
    }

    /**
     * 实时刷新二维码
     */
    public function qrcodeInfo(){
        $content = $_GET['content'];
        return qrcode($content);
    }
}