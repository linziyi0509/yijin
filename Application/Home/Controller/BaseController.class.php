<?php
namespace Home\Controller;
use Think\Controller;
class BaseController extends Controller {
    public function __construct(){        
        parent::__construct();
        //openid没有就去获取
        $wxuser =  M('my_wechatuser');
        //直接判断如果没有绑定手机号 首先绑定手机号
        if(session('userinfo')){
            $phone = M('my_wechatuser')->where('openid = "'.session('openid').'"')->getField('phone');
            if ( empty($phone)) {
                $this->redirect('User/index');
            }
        }
        if(strlen(session('openid')) != 28){
            //生成微信操作对象
            $wxobj = wechat_connect();

            //判断code
            if($_GET['code']){
                //返回的token
                $token = $wxobj->getOauthAccessToken();                  
                //获取详细信息
                $userinfo = $wxobj->getOauthUserinfo($token['access_token'], $token['openid']);
                $nickname = trim($userinfo['nickname']);
                
                //看数据库是否存在，存在修改，不存在添加
                $res = $wxuser->where('openid = "'.$token['openid'].'"')->find();
                $data = array(
                    'openid' => $userinfo['openid'],
                    //'nickname' => base64_encode($nickname),
                    'nickname' => $nickname,
                    'sex' => $userinfo['sex'],
                    'language' => $userinfo['language'],
                    'city' => $userinfo['city'],
                    'province' => $userinfo['province'],
                    'country' => $userinfo['country'],
                    'headimgurl' => $userinfo['headimgurl']
                );
                if( $res ){
                    $data['updatetime'] = date('Y-m-d H:i:S',time());
                    $wxuser->where('openid = "'.$token['openid'].'"')->save($data);
                }else{
					if($userinfo['openid']){
						$data['createtime'] = date('Y-m-d H:i:S',time());
						$data['updatetime'] = date('Y-m-d H:i:S',time());
						$wxuser->add($data);
					}
                }
                session('openid', $token['openid']);
            }else{
                //去获取code
                redirect($wxobj->getOauthRedirect(get_url(), 'state'));
            }
        }

        //查询用户数据
        if(!session('userinfo')){
            $user_data = $wxuser->where('openid = "'.session('openid').'"')->find();
            session('userinfo', $user_data);
        }
    }

    //查询手机号码是否绑定
    public function selPhone()
    {
        $phone = M('my_wechatuser')->where('openid = "'.session('openid').'"')->getField('phone');
        if ( empty($phone)) {
            $this->ajaxReturn([ 'code'  =>  310,'string'=>'你的手机号未绑定，点击确认跳转到绑定手机号页面']);
        }
    }
}
