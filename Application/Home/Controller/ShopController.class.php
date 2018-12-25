<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;
//商城控制器
class ShopController extends BaseController {
    
    //商城首页
    public function index(){

        $this->assign('userinfo',session('userinfo'));
        $this->display();
    }
    //兑换历史
    public function record()
    {
		$this->selPhone();
        $id =M('my_wechatuser')
            ->where('openid = "'.session('openid').'"')
            ->getField('id');
        $data = M('my_payingorders')
            ->field('createtime,credit,money,paidflag,ordernum,payingtype')
            ->where(['wechatuserid' => $id,'ordertype'=>51])
            ->order('createtime desc')
            ->select();
        $this->assign('data',$data);
        $this->display();
    }

    //在线充值
    public function info()
    {
        if (IS_POST) {			
            $data = I('post.','','trim');  
            $phone = M('my_wechatuser')->where(['openid'=>session('openid')])->getField('phone');
            if (empty($phone)) {
                $this->ajaxReturn(['code'=>103,'string'=>'您的手机号未绑定，请去绑定手机号在来提现']);
            }
            if ($data['id'] == 1 ) {
                $begintime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));
                $endtime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);
                $map['createtime'] =array(array('EGT',$begintime),array('ELT',$endtime));
                $last =  M('my_payingorders')->where($map)->where(array('wechatuserid'=>session('userinfo')['id'],'payingtype'=>44))->sum('money');            
                if ((int)$last+$data['price'] > 20000) {
                    $this->ajaxReturn(['code'=>102,'string'=>'今日微信提现已达到上限，请选择其他提现方式']);
                }
            }else{
                $begintime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d'),date('Y')));
                $endtime=date("Y-m-d H:i:s",mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1);
                $map['createtime'] =array(array('EGT',$begintime),array('ELT',$endtime));
                $last =  M('my_payingorders')->where($map)->where(array('wechatuserid'=>session('userinfo')['id'],'payingtype'=>43))->sum('money'); 
        
                if ((int)$last+$data['price'] > 50000) {
                    $this->ajaxReturn(['code'=>102,'string'=>'今日支付宝提现已达到上限，请选择其他提现方式']);
                }
            }
                 
                       
            //使用事务 进行数据的插入
            $model = new Model();
            $model->startTrans();//开启事务
            $user_data =M('my_wechatuser')
                        ->where('openid = "'.session('openid').'"')
                        ->find();
            if ($user_data['credits'] < $data['credits'] ) {
                $this->ajaxReturn(['code'=>101,'string'=>'你的账号积分不足']);
            }
            //修改用户的积分
            $credits = $data['credits'];
            $userCredits = $user_data['credits']-$credits;
            $user_data =M('my_wechatuser')
                        ->where('openid = "'.session('openid').'"')
                        ->save(['credits'=>$userCredits]);
			$flag = [];
            if ($user_data) {
                $flag[] = true;
                //插入订单
                if ($data['id'] == 1 ) {
                    $map = [
                        'createtime'    =>  date('Y-m-d H:i:s',time()),
                        'wechatuserid'  =>  session('userinfo')['id'],
                        'username'      =>  session('userinfo')['nickname'],
                        'telephone'     =>  $phone,
                        'credit'        =>  $credits,  //积分
                        'money'         =>  $data['price'],//round($data['price']/10,2), //金额(元)
                        'paidflag'      =>  35,  //订单状态
                        'ordernum'      =>  date('YmdHis',time()).rand(100,999), //订单号
                        'batch'         =>  date('Y-m-d H:i:s',time()), //交易号
                        'payingtype'    =>  44,
                        'ipaddr'        =>  get_client_ip(),
                        'ordertype'     =>  51,
                    ];
                }else{
                    $map = [
                        'createtime'    =>  date('Y-m-d H:i:s',time()),
                        'wechatuserid'  =>  session('userinfo')['id'],
                        'username'      =>  session('userinfo')['nickname'],
                        'telephone'     =>  $phone,
                        'credit'        =>  $credits,  //积分
                        'money'         =>  $data['price'],//round($data['price']/10,2), //金额(元)
                        'paidflag'      =>  35,  //订单状态
                        'ordernum'      =>  date('YmdHis',time()).rand(100,999), //订单号
                        'batch'         =>  date('Y-m-d H:i:s',time()), //交易号
                        'payingtype'    =>  43,
                        'ipaddr'        =>  get_client_ip(),//getIp(),
                        'ordertype'     =>  51,
						'payeerealname'     =>  $data['username'],
                        'payeeaccount'     =>  $data['phone'],
                    ];
                }
                writelog("提现订单信息：",$map);
                $result =  M('my_payingorders')->add($map);    
                if ($result) {
                    $flag[] = true;
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
            die;
        }
		
		//获取用户的个人信息
        $user_data =M('my_wechatuser')->where('openid = "'.session('openid').'"')->find();
		$wxobj1 = wechat_connect("LJS");
		//返回的token
			//判断code
		if($_GET['code']){
			//返回的token
			$token = $wxobj1->getOauthAccessToken();
			//获取详细信息
			if ( empty($user_data['ljsopenid']) ) {
				if ( !empty($token['openid']) ) {
					M('my_wechatuser')->where('openid = "'.session('userinfo')['openid'].'"')->save(['ljsopenid'=>$token['openid']]);
				}
			}
		}else{
			//去获取code
			redirect($wxobj1->getOauthRedirect(get_url(), 'state'));
		}
		if ( empty($user_data['phone']) ) {
           $this->redirect('User/index');
        }
        $this->assign('userinfo',$user_data);
    	$this->display();
    }
}
