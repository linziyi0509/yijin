<?php
// +----------------------------------------------------------------------
//极光推送
// +----------------------------------------------------------------------
namespace Org\Wzf;

class jpush{
 
    private $_appkeys = '';
    private $_masterSecret = '';

    function __construct($masterSecret = '',$appkeys = '') {
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
    //通知
    function send($message,$registration_id,$options=0)
    {
        $url = 'https://api.jpush.cn/v3/push';
        $base64=base64_encode("$this->_appkeys:$this->_masterSecret"); 
        $header=array("Authorization:Basic $base64","Content-Type:application/json");
        // print_r($header);
        $param='{"platform":"all","audience":{"registration_id":['.$registration_id.']},"notification" : { "android" : {
            "alert" : "'.$message.'",
            "title":"Send to Android",
            "builder_id":1,
            "extras" : { "newsid" : 321}

}, 
      "ios" : {
            "alert" : "'.$message.'",
            "sound":"default",
            "badge":"+1",
            "extras" : { "newsid" : 321}
      }},"options":{"apns_production":'.$options.'}}';
        $res = $this->request_post($url,$param,$header);
        $res_arr = json_decode($res, true);
        error_log ('推送：'.var_export($res_arr,true).'
            ',3,"./log/".date('Y-m-d')."-tuisongxiaoxitz.php");
        error_log ('推送标题：'.$message.$registration_id,3,"./log/".date('Y-m-d')."-tuisongxiaoxitz.php");
        return $res_arr;
    }
    //消息
    function send2($title,$content,$registration_id,$options=0)
    {
        $url = 'https://api.jpush.cn/v3/push';
        $base64=base64_encode("$this->_appkeys:$this->_masterSecret"); 
        $header=array("Authorization:Basic $base64","Content-Type:application/json");
        // print_r($header);
        if ($registration_id == 'all') {
            $list = '"all"';
        } else {
            $list = '{"registration_id":['.$registration_id.']}';
        }
        
        $param='{"platform":"all","audience":'.$list.',"message":{"title":"'.$title.'","msg_content":"'.$content.'"},"options":{"apns_production":'.$options.'}}';
        $res = $this->request_post($url,$param,$header);
        $res_arr = json_decode($res, true);
        
        error_log ('推送：'.var_export($res_arr,true).'
            ',3,"./log/".date('Y-m-d')."-tuisongxiaoxi.php");
        error_log ('推送标题：'.$title.$content.$registration_id,3,"./log/".date('Y-m-d')."-tuisongxiaoxi.php");
        return $res_arr;
    }
}