<?php
namespace Home\Controller;
use Think\Controller;

class IndexController extends Controller {
    public function index(){
        $weObj = wechat_connect();
        $share = $weObj->getOauthAccessToken();
        $this->share = $share;
        $this->display();
    }
    public function wechatlogin(){
        $weObj = wechat_connect();
        //判断code
//        $token = $weObj->getOauthAccessToken();
        $qrcode = $weObj->getQRCode(rand(1,100000));
        if($qrcode){
            $url = $weObj->getQRUrl($qrcode['ticket']);
            if($url){
                redirect($url);
            }
        }
        /*if($_GET['code']){
            $qrcode = $weObj->getQRCode(rand(1,100000));
            if($qrcode){
                $url = $weObj->getQRUrl($qrcode['ticket']);
                if($url){
                    redirect($url);
                }
            }
            //获取详细信息
//            $userinfo = $weObj->getOauthUserinfo($token['access_token'], $token['openid']);
        }else{
            //去获取code
                redirect($weObj->getOauthAccessToken(get_url(), 'state','snsapi_base'));
        }*/
    }
    public function bindInfo(){
        if(S('user')){
            var_dump(S('user'));
        }else{
            echo '绑定账号';
        }
    }
    public function apilist(){
    	$result = $this->getall("select * from `my_api2` order by `id` desc");
    	$this->assign('result',$result);
    	$this->display();
    }

    public function createMenu()
    {
        // 删除缓存
      //S('com.ld.wechat.web.WeChat_token_cache',null);die;
        $data = '{"button":[             
            {
               "name":"服务商城",
               "sub_button":[
                {
                   "type":"view",
                   "name":"服务转让",
                   "url":"http://www.baidu.com/"
                },
                {
                   "type":"click",
                   "name":"油卡充值",
                   "key":"您所这在城市暂无开通"
                },
                {
                   "type":"click",
                   "name":"话费充值",
                   "key":"您所这在城市暂无开通"
                }]
            },{
               "name":"个人中心",
               "sub_button":[
               {
                   "type":"view",
                   "name":"手机绑定",
                   "url":"http://jeep3.yikoo.com/index.php/Home/User/getOpenid"
                },
                {
                   "type":"click",
                   "name":"我的服务",
                   "key":"暂无开通此功能"
                },
                {
                   "type":"view",
                   "name":"我的转让",
                   "url":"http://www.baidu.com/"
                }]
           }
        ]}';
        $weObj = wechat_connect();
        $result = $weObj->createMenu($data);
        dump($result);
    }
	public function strtomd5_pass($str)
	{
		$get_string = "ASDFWEHUIOJHJHPF";//加密参与字符
		 global $get_string;
		 return md5(md5($str.$get_string).$get_string);
	}
	public function test(){
		
		@exec("ipconfig /all",$array);
		$macstr = '';
		for($Tmpa=0;$Tmpa<count($array);$Tmpa++){
		    if(eregi("Physical",$array[$Tmpa]))
		 	{
		        	$mac=explode(":",$array[$Tmpa]);
		        	$macstr = trim($mac[1]);
    			}
		}
		$macmd5str = substr(self::strtomd5_pass($macstr),0,24);
		$macmd5str = strtoupper($macmd5str);
		$robotstr = substr($macmd5str,0,6)."-".substr($macmd5str,4,6)."-".substr($macmd5str,8,6)."-".substr($macmd5str,12,6);

		$macmd5towstr = substr(self::strtomd5_pass($robotstr),0,24);
		$macmd5towstr = strtoupper($macmd5towstr);
		$robotwostr = substr($macmd5towstr,0,6)."-".substr($macmd5towstr,4,6)."-".substr($macmd5towstr,8,6)."-".substr($macmd5towstr,12,6);

		echo "机器码：".$robotstr;
		echo "<br>";
		echo "系列号：".$robotwostr;
	}
}
