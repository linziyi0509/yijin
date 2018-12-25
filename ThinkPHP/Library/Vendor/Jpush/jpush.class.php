<?php
// +----------------------------------------------------------------------
//极光推送
// +----------------------------------------------------------------------

class jpush{
 
    private $_appkeys = '';
    private $_masterSecret = '';
    private $_options = 'false';//false=开发环境 true=生产环境

    function __construct($appkeys='b1165d04345d554b79ba9016',$masterSecret='dd80c06241be2226b810f24d') {
        $this->_masterSecret = $masterSecret;
        $this->_appkeys = $appkeys;
    }

    function request_post($url="",$param="",$header="") {
        if (empty($url) || empty($param)) {
        return false;
        }
        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT,1);
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
        // 增加 HTTP Header（头）里的字段
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        // 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        $data = curl_exec($ch);//运行curl
     
        curl_close($ch);
        return $data;
    }
    
    /**
     * 推送通知
     * @param string $message 消息内容
     * @param string|array $registration_id 设备码(支持数组)
     */
    function sendnotice($message,$registration_id)
    {
        $url 	= 'https://api.jpush.cn/v3/push';
        $base64	= base64_encode("$this->_appkeys:$this->_masterSecret"); 
        $header	= array("Authorization:Basic $base64","Content-Type:application/json");
        if(!is_array($registration_id)){
        	$registration_id = array($registration_id);
        }
        $paramarr['platform'] 		= 'all';
        $paramarr['audience'] 		= array('registration_id'=>$registration_id);
        $paramarr['notification'] 	= array('alert'=>$message);
        $paramarr['options'] 		= array('apns_production'=>$this->_options);
        $res 		= $this->request_post($url,json_encode($paramarr,JSON_UNESCAPED_UNICODE),$header);
        $res_arr 	= json_decode($res, true);
        return $res_arr;
    }
    
    /**
     * 推送穿透消息
     * @param string $title 消息标题
     * @param string $content 消息内容
     * @param string|array $registration_id 设备码(支持数组)
     * @param array $extras 穿透参数(选填)
     */
    function sendmessage($title,$content,$registration_id,$extras=false)
    {
        $url = 'https://api.jpush.cn/v3/push';
        $base64=base64_encode("$this->_appkeys:$this->_masterSecret"); 
        $header=array("Authorization:Basic $base64","Content-Type:application/json");
    	if(!is_array($registration_id)){
        	$registration_id = array($registration_id);
        }
        $paramarr['platform'] 	= 'all';
        $paramarr['audience'] 	= array('registration_id'=>$registration_id);
        $paramarr['message'] 	= array('title'=>$title,'msg_content'=>$content);
        $paramarr['options'] 	= array('apns_production'=>$this->_options);
        if(is_array($extras)){
        	$paramarr['message']['extras'] = $extras;
        }
        $res = $this->request_post($url,json_encode($paramarr,JSON_UNESCAPED_UNICODE),$header);
        $res_arr = json_decode($res, true);
        return $res_arr;
    }
}