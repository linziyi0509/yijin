<?php
namespace Home\Controller;
use Think\Controller;
//class UserController extends BaseController {
class UserController extends Controller {
	//手机绑定页面
	public function index()
    {      
		if ( empty(session('openid')) ) {
			$this->redirect('Scan/index');
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
        //获取用户的个人信息
        $this->user_data = $user_data;
        $this->display();
    }
    /*public function index()
    {      
		
        //获取用户的个人信息
        $this->user_data =M('my_wechatuser')->where('openid = "'.session('openid').'"')->find();       
        $this->display();
    }*/

    //确认绑定程序
    public function savePhone()
    {
    	$data = I('post.');
    	$code = session('code');
    	//判断验证码是否正确,目前验证码为写死数
    	$s = S('sms' . $data['phone']);
        if ($s == false) {
            $this->ajaxReturn(['msg'=>102,'string'=>'验证码已经超时，请重新发送验证码']);
        }
    	if ($data['code'] != $s ) {
    		$this->ajaxReturn(['msg'=>102,'string'=>'验证码错误']);
    	}  
    	$wechatuser = M('my_wechatuser');
    	//查询用户是否绑定手机号
    	$info 	= $wechatuser
    			->where(['phone'=>$data['phone']])
    			->getField('phone');
    	if (!empty($info)) {
    		$this->ajaxReturn(['msg'=>103,'string'=>'手机号已经绑定,请选择其他手机号']);
    	}
        $array =[
            'updatetime'=>  date('Y-m-d H:i:s',time()),
            'phone'     =>  $data['phone'],
            'actived'   =>  20,
        ];
    	//修改数据
    	$result = $wechatuser
    			->where(['openid'=>session('openid')])
    			->save($array);
    	if ($result) {
            
            // 删除缓存
            S('sms' . $data['phone'],'null');
    		$this->ajaxReturn(['msg'=>200,'string'=>'手机号绑定成功']);
    	}else{
    		$this->ajaxReturn(['msg'=>101,'string'=>'手机号绑定失败']);
    	}
    }

    //短信验证码发送实例
    public function sms()
    {
        $phone = I('post.phone','','trim');
        if ( empty($phone) || strlen($phone) != 11 ) {
            return $this->ajaxReturn(['code'=>102,'string'=>'请输入正确的手机号码']);
        }
        //查询用户是否绑定手机号
        $info   = M('my_wechatuser')
                ->where(['phone'=>$phone])
                ->getField('phone');
        if (!empty($info)) {
            $this->ajaxReturn(['msg'=>103,'string'=>'手机号已经绑定,请选择其他手机号']);
        }
        $code = rand(1000,9999);
        S('sms'. $phone, $code, '300');
        vendor('api_sdk.SmsDemo');
        $sms = new \SmsDemo();       
        $data = [
            'phone'=>$phone,
            'code'=>$code,
        ];
        $response = $sms ->sendSms($data);
        if ($response->Code == 'OK' ) {
            return $this->ajaxReturn(['code'=>200,'string'=>'验证码发送成功,请继续绑定操作']);
        }else{
            return $this->ajaxReturn(['code'=>101,'string'=>'验证码发送失败，请重新发送']);
        }
        
        
       
    }


}