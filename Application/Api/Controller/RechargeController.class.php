<?php
namespace Api\Controller;
use Think\Controller;
class RechargeController extends BaseController{
	//用户充值
	public function user_recharge(){
		$user_id = I("user_id") ? intval(I("user_id"))  : '';
		$type = I("type") ? intval(I("type"))  : '';
		$money = I("money") ? trim(I("money"))  : '';
		$nickname = I("nickname") ? trim (I("nickname")) : '';
		if(!$user_id || !$type || !$money || !$nickname){
			$this->Rerror("缺少参数");
		}
		$data["userid"] 		= $user_id; //用户id
		$data["type"]   		= $type;	//充值类型
		$data["money"]  		= $money;	//充值金额
		$data["nickname"]		= $nickname; //昵称
		$data["createtime"] 	= date("Y-m-d H:i:s"); //时间
		$data["orderNumber"] 	= "xmj".date("YmdHis").rand(100,999); //订单号
		$data["status"] 		= 104;
		$info = M("chongzhi")->add($data);
		if($data["type"]==100){
			$redata['orderid']     = intval($info);
			$redata['out_trade_no'] = strval($data['orderNumber']);//商户订单号
			$redata['subject']      = "雇主的订单".$redata['out_trade_no'];//订单标题
			$redata['total_money']  = strval($data['money']);//支付金额
			$redata['body']         = '买家(UID'.$data['userid'].')支付订单(￥'.$data['money'].')';
			exit(json_encode(array('code'=>'1000','message'=>'充值成功','data'=>$redata)));
		}else if($data["type"]==102){
			require_once VENDOR_PATH.'Wxpay/WxPay.Api.php';
			require_once VENDOR_PATH.'Wxpay/WxPay.Config.php';
			require_once VENDOR_PATH.'Wxpay/WxPay.Data.php';
			$totalprice = intval($data["money"]*100);
			$input = new \WxPayUnifiedOrder();
			$input->SetBody('微信支付的订单'.$data['orderNumber']);
			$input->SetAttach('买家(UID'.$data['userid'].')支付订单(￥'.$data['money'].')');
			$input->SetOut_trade_no($data["orderNumber"]);
			$input->SetTotal_fee($totalprice);
			$input->SetTime_start(date("YmdHis")); 
			$input->SetNotify_url('http://zhaiyi.bjqttd.com/api/recharge/wechat_alipay');
			$input->SetTrade_type("APP");
			$order =  \WxPayApi::unifiedOrder($input);
			if(!$order['prepay_id']){
				$this->Rerror('微信prepayid生成失败');
			}
			$obj = new \WxPayResults();
			if(!$obj->CheckSign()){
				$this->Rerror('微信签名错误');
			}
			$re['prepay_id']    = $order['prepay_id'];
			$re['nonce_str']    = $order['nonce_str'];
			$return	 		 = $obj->returenAppFromArray($re);
			unset($return['partnerid']);
			unset($return['appid']);
			unset($return['package']);
			M("chongzhi")->where(array('id'=>$info))->data(array('prepay_id'=>$re['prepay_id']))->save();
			$this->Rsuccess('订单生成成功',$return);
		}
	}
	//支付宝支付异步通知
	public function alipay(){

	//  file_put_contents('pay_post.txt', time().":".json_encode($_POST)."\r\n",FILE_APPEND);
       //file_put_contents('pay_get.txt', time().":".json_encode($_GET)."\r\n",FILE_APPEND);
    	//支付宝参数
		$alipay_config['partner']		= '2088121763064257';
		$alipay_config['private_key_path']	= VENDOR_PATH.'Alipay/key/rsa_private_key.pem';
		$alipay_config['ali_public_key_path']= VENDOR_PATH.'Alipay/key/alipay_public_key.pem';
		$alipay_config['sign_type']    = strtoupper('RSA');
		$alipay_config['input_charset']= strtolower('utf-8');
		$alipay_config['cacert']    = VENDOR_PATH.'Alipay\\cacert.pem';
		$alipay_config['transport']    = 'http';
		//引入文件
    	vendor('Alipay.lib.alipay_core#function');
    	vendor('Alipay.lib.alipay_rsa#function');
    	vendor('Alipay.lib.alipay_notify#class');
		//计算得出通知验证结果
		$alipayNotify = new \AlipayNotify($alipay_config);
		$verify_result = $alipayNotify->verifyNotify();
		if($verify_result) {
			//商户订单号
			$out_trade_no = $_POST['out_trade_no'];
			//支付宝交易号
			$trade_no = $_POST['trade_no'];
			//总金额
			$total_fee = $_POST['total_fee'];
			//交易状态
			$trade_status = $_POST['trade_status'];
		    if($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
		    	//↓↓↓↓↓↓↓↓↓↓根据实际情况处理商户逻辑↓↓↓↓↓↓↓↓↓↓
		    	$order = M('chongzhi')->where(array('orderNumber'=>$out_trade_no))->find();
				if($order &&  $order['status']=='104' && $order['type'] == '100'){
					//更新订单
					$data["status"]=105;
					$data['updatetime'] = date('Y-m-d H:i:s');
					if(!M('chongzhi')->where(array('orderNumber'=>$out_trade_no))->data($data)->save()){
						 echo "fail";
					}
					//更新用户的余额
					$info = M("user")->field("recharge_money")->where("id='".$order['userid']."'")->find();
					$new['recharge_money'] = $info["recharge_money"]+$total_fee;
					M("user")->where("id='".$order['userid']."'")->save($new);
				}
				//↑↑↑↑↑↑↑↑↑↑根据实际情况处理商户逻辑↑↑↑↑↑↑↑↑↑↑
		    }
			echo "success";
		}
		else {
		     echo "fail";
		}
	}
	//微信支付的异步通知
	public function wechat_alipay(){
		require_once VENDOR_PATH.'Wxpay/WxPay.Api.php'; //引入微信Api
		require_once VENDOR_PATH.'Wxpay/WxPay.Config.php'; //引入微信配置文件
		require_once VENDOR_PATH.'Wxpay/WxPay.Data.php';
        require_once VENDOR_PATH.'Wxpay/WxPay.Notify.php';
        $payResult =  file_get_contents('php://input'); 
        $notify = new \WxPayNotify(); //返回值类
 		$obj    = new \WxPayResults();
		$array  = $obj->FromXml($payResult);
		if($obj->checkSign() == TRUE){
			//商户订单号
			$out_trade_no = $array['out_trade_no'];
			
		    if($array['result_code'] == 'SUCCESS') {
		    	//↓↓↓↓↓↓↓↓↓↓根据实际情况处理商户逻辑↓↓↓↓↓↓↓↓↓↓
		    	$order = M('chongzhi')->where(array('orderNumber'=>$out_trade_no))->find();
				if($order && $order['status']=='104'){
					//更新订单
					$data['status'] = 105;
					$data['updatetime'] = date('Y-m-d H:i:s');
					if(!M('chongzhi')->where(array('orderNumber'=>$out_trade_no))->data($data)->save()){
						echo $obj->ToResultWeixin(array('return_code'=>'FAIL','return_msg'=>'更新订单失败'));
						exit;
					}
					$info = M("user")->field("recharge_money")->where("id='".$order['userid']."'")->find();
					$new['recharge_money'] = $info["recharge_money"]+$order["money"];
					M("user")->where("id='".$order['userid']."'")->save($new);
				}
				//↑↑↑↑↑↑↑↑↑↑根据实际情况处理商户逻辑↑↑↑↑↑↑↑↑↑↑
		    }
			echo $obj->ToResultWeixin(array('return_code'=>'SUCCESS','return_msg'=>'OK'));
			exit;
		}else {
			echo $obj->ToResultWeixin(array('return_code'=>'FAIL','return_msg'=>'签名校验失败'));
			exit;
		}
	}

}
