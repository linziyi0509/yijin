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
use Common\Model\SalesmaninfoModel;
use Think\Controller;
use Think\Model;

class SalesmaninfoController extends Controller
{
    /**
     * 图形验证码 只在发送短信验证码时候验证 一次 失效
     */
    public function index(){
        if(IS_POST){
            $idcard = I('post.idcard','','trim');
            $telephone = I('post.telephone','','trim');
            $phonecode = I('post.phonecode','','trim');
            if ( empty($telephone) || strlen($telephone) != 11 || empty($idcard)) {
                $this->ajaxReturn(['code'=>102,'string'=>'请输入正确的手机号码']);
            }
            if(intval(S($idcard.'sms'. $telephone)) != intval($phonecode)){
                $this->ajaxReturn(['code'=>101,'string'=>'短信验证码填写错误']);
            }
            //验证通过清除缓存信息
            S($idcard.'sms'. $telephone,'null');
            //获取保单号信息和关联服务券信息
            $model = new SalesmaninfoModel();
            $salesmaninfoData = $model->idcardPhone($idcard, $telephone);
            if($salesmaninfoData){
                if(!session('userinfo')['id']){
                    $this->ajaxReturn(['code'=>101,'string'=>'请授权一金公众号']);
                }
                if($salesmaninfoData['wechantuserid'] > 0){
                    //直接跳转到输入保单号页面
                    $this->ajaxReturn(['code'=>201,'string'=>'已绑定微信']);
                }
                $flag = $model->updateSales($salesmaninfoData);
                if($flag === true){
                    $this->ajaxReturn(['code'=>200,'string'=>'绑定成功']);
                }else{
                    $this->ajaxReturn(['code'=>101,'string'=>'绑定失败，重新绑定']);
                }
            }else{
                $this->ajaxReturn(['code'=>101,'string'=>'此信息不存在']);
            }
        }else{
            $this->display('Policy/salesmanindex');
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
        $idcard = I('post.idcard','','trim');
        $telephone = I('post.telephone','','trim');
        $code = I('post.code','','trim');
        if ( empty($telephone) || strlen($telephone) != 11 || empty($idcard)) {
            return $this->ajaxReturn(['code'=>102,'string'=>'请输入正确的手机号码']);
        }
        $verify = new \Think\Verify();
        if (!$verify->check($code) ) {
            $this->ajaxReturn(['code'=>101,'string'=>'验证码填写错误']);
        }
        //判断保单号和手机号在后台是否存在此记录 不存在 不允许发送验证码
        $model = new SalesmaninfoModel();
        $salesmaninfoData = $model->idcardPhone($idcard,$telephone);
        if($salesmaninfoData){
            $code = rand(1000,9999);
            S($idcard.'sms'.$telephone, $code, '300');
            writelog('Salesmaninfo---phonesms---sms:',$idcard.'sms'.$telephone."_--------------".S($idcard.'sms'.$telephone));
            vendor('api_sdk.SmsDemo');
            $sms = new \SmsDemo();
            $data = [
                'phone'=>$telephone,
                'code'=>$code,
            ];
            $response = $sms ->sendSms($data);
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
     * 保单号兑换中心
     */
    public function exchange(){
        if(IS_POST){
            //数组分割
            $really = [];
            $fake = [];
            $data = explode(',',I('post.id'));
            $vcode = $data[count($data)-1];
            $verify = new \Think\Verify();
            if (!$verify->check($vcode) ) {
                $this->ajaxReturn(['code'=>101,'string'=>'验证码填写错误']);
            }
            //去除验证码信息
            array_pop($data);
            //根据保单号查询到保单id和是否授权营销员的信息，并且是否审核过，审核可使用---营销员已经绑定微信，查询当前输入的保单号对应的营销员手机号是否与营销员绑定的微信一致性
            $policy = M('my_policy');
            foreach ($data as $val){
                $policyData = $policy
                    ->field('id,isaudit,merchantid,isauthorizesales,salesmanphone')
                    ->where(['policynumber'=>$val])
                    ->find();
                if($policyData) {
                    //首先做营销员手机号的判断
                    $salesmaninfoData = M('my_salesmaninfo')->where(['wechatuserid='.session('userinfo')['id']])->field('phone')->select();
                    $data = [];
                    foreach($salesmaninfoData as $key=>$val){
                        $data[] = $val['phone'];
                    }
                    if(in_array($policyData['salesmanphone'],$data)){
                        //判断保单号是否冻结、是否审核 是否授权营销员
                        if (in_array($policyData['isaudit'],[75,76]) && $policyData['isauthorizesales'] == 69 ) {
                            //根据policy的主键去查询关联信息中是否存在授权营销员的服务券信息  同步授权 且用户授权营销员
                            $policysvData = M('my_policyservicevoucher')->where(['id='=>$policyData['id'],'issynchrogrant'=>72,'isusergrantsales'=>79])->select();
                            if ($policysvData) {
                                //将查找到的信息 进行当前用户的分配
                                $psvModel = M('my_policyservicevoucher');
                                $result = [];
                                foreach($policysvData as $key=>$val){
                                    $data = array(
                                        'updatetime'    => date('Y-m-d H:i:s',time()),//修改时间
                                        'wechatuserid'  => session('userinfo')['id']
                                    );
                                    $result[] =$psvModel->where('id='.$val['id'])->save($data);
                                }
                                //是否正确
                                if (!in_array(false,$result)) {
                                    //信息正确
                                    $really[] = $val;
                                }else{
                                    //修改营销员信息错误
                                    $fake[] = $val;
                                }
                            }else{
                                //关联信息中 未授权或者用户未同意授权保单
                                $fake[] = $val;
                            }
                        }else{
                            //未审核或未授权营销员的保单号
                            $fake[] = $val;
                        }
                    }else{
                        //营销员的手机号不对
                        $fake[] = $val;
                    }
                }else{
                    //保单号错误
                    $fake[] = $val;
                }
            }
            $this->ajaxReturn([
                'code'  =>  200,
                'really'=>$really,
                'fake'=>$fake,
                'string'=>count($really).'个保单号成功,'.count($fake).'个保单号失败'
            ]);
        }else{
            $this->display();
        }
    }
}