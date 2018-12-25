<?php
namespace Vendor\Wechat;
use Think\Exception;

/**
 *@FILENAME:Vendor\Wechat;
 *@AUTHOR:dudongjiang;
 *@DATE:2016年9月21日;
 *@EFFORT:微信接口;
 **/
class Wechat
{
    const MSGTYPE_TEXT = 'text';
    const MSGTYPE_IMAGE = 'image';
    const MSGTYPE_LOCATION = 'location';
    const MSGTYPE_LINK = 'link';
    const MSGTYPE_EVENT = 'event';
    const MSGTYPE_MUSIC = 'music';
    const MSGTYPE_NEWS = 'news';
    const MSGTYPE_CUSTOMER = 'transfer_customer_service';
    const MSGTYPE_VOICE = 'voice';
    const MSGTYPE_VIDEO = 'video';
    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
    const API_URL_FILE = 'http://file.api.weixin.qq.com/cgi-bin/media/upload?';
    const AUTH_URL = '/token?grant_type=client_credential&';
    const MENU_CREATE_URL = '/menu/create?';
    const MENU_GET_URL = '/menu/get?';
    const MENU_DELETE_URL = '/menu/delete?';
    const MASSAGE_MASS_DELETE_URL = '/message/mass/delete?';
    const MEDIA_GET_URL = '/media/get?';
    const QRCODE_CREATE_URL='/qrcode/create?';
    const QR_SCENE = 0;
    const QR_LIMIT_SCENE = 1;
    const QRCODE_IMG_URL='https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=';
    const USER_GET_URL='/user/get?';
    const USER_INFO_URL='/user/info?';
    const GROUP_GET_URL='/groups/get?';
    const GROUP_CREATE_URL='/groups/create?';
    const GROUP_UPDATE_URL='/groups/update?';
    const GROUP_DELETE_URL='/groups/delete?';
    const GROUP_MEMBER_UPDATE_URL='/groups/members/update?';
    const CUSTOM_SEND_URL='/message/custom/send?';
    const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';
    const OAUTH_AUTHORIZE_URL = '/authorize?';
    const OAUTH_TOKEN_PREFIX = 'https://api.weixin.qq.com/sns/oauth2';
    const OAUTH_TOKEN_URL = '/access_token?';
    const OAUTH_REFRESH_URL = '/refresh_token?';
    const OAUTH_USERINFO_URL = 'https://api.weixin.qq.com/sns/userinfo?';
    const SEND_MSG_URL='/message/mass/send?';
    const UPLOAD_NEWS_URL='/media/uploadnews?';
    const REMARK_UPDATE_URL='/user/info/updateremark?';//备注修改
    const GET_CARDCOLOR_URL='https://api.weixin.qq.com/card/getcolors?';//获取卡券颜色接口
    const GET_STORE_URL='https://api.weixin.qq.com/card/location/batchget?';//拉取门店列表
    const ADD_STORE_URL='https://api.weixin.qq.com/card/location/batchadd?';//批量导入门店
    const CREATE_CARD_URL='https://api.weixin.qq.com/card/create?';//创建卡券
    const GET_CARD_URL='https://api.weixin.qq.com/card/get?';//获取卡券
    const GET_ALLCARD_URL='https://api.weixin.qq.com/card/batchget?';//获取所有的卡券列表
    const DEL_CARD_URL='https://api.weixin.qq.com/card/delete?';//删除卡券
    const UPDATE_CARD_URL='https://api.weixin.qq.com/card/update?';//更新卡券功能
    const QRCODE_CARD_URL='https://api.weixin.qq.com/card/qrcode/create?';//生成卡券二维码
    const CONSUME_CARD_URL='https://api.weixin.qq.com/card/code/consume?';//消耗卡券
    const DECRYPT_CARD_URL='https://api.weixin.qq.com/card/code/decrypt?';//code 解码
    const GET_CODE_URL='https://api.weixin.qq.com/card/code/get?';//查询code
    const UPDATA_CODE_URL='https://api.weixin.qq.com/card/code/update?';//更改code
    const UNABLE_CODE_URL='https://api.weixin.qq.com/card/code/unavailable?';//设置卡券失效
    const TEST_CODE_URL='https://api.weixin.qq.com/card/testwhitelist/set?';//设置白名单
    const NOTICE_URL='https://api.weixin.qq.com/cgi-bin/message/template/send?';//发送消息模板
    const MEDIA_FOREVER_UPLOAD_URL = '/material/add_material?';//上传永久素材
    const SHARE_JSAPI_TICKET = '/ticket/getticket?'; //jsapi获取的方法
    const MATERIAL_ADD_NEWS = '/material/add_news?';
    //支付---开始
    const UNIFIED_ORDER = 'https://api.mch.weixin.qq.com/pay/unifiedorder';//统一下单
    const MMPAYMKTTRANSFERS_TRANSFERS = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';//企业付款到零钱
    const MMPAYMKTTRANSFERS_GETTRANSFERINFO = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/gettransferinfo';//查询企业付款
    const OAUTH_LOGIN = 'https://open.weixin.qq.com/connect/qrconnect?';
    //支付---结束
    private $token;
    private $appid;
    private $appsecret;
    private $user_token;
    private $_msg;
    private $_funcflag = false;
    private $_receive;
    public $debug =  true;
    public $errCode = 40001;
    public $errMsg = "no access";
    private $_logcallback;
    private $access_token;

    public function __construct($options)
    {
        $this->token = isset($options['token'])?$options['token']:'';
        $this->appid = isset($options['appid'])?$options['appid']:'';
        $this->appsecret = isset($options['appsecret'])?$options['appsecret']:'';
        $this->debug = isset($options['debug'])?$options['debug']:false;
        $this->_logcallback = isset($options['logcallback'])?$options['logcallback']:false;
    }

    /**
     * For weixin server validation
     */
    private function checkSignature()
    {
        $signature = isset($_GET["signature"])?$_GET["signature"]:'';
        $timestamp = isset($_GET["timestamp"])?$_GET["timestamp"]:'';
        $nonce = isset($_GET["nonce"])?$_GET["nonce"]:'';

        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);

        sort($tmpArr, SORT_STRING);

        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }

    }

    /**
     * For weixin server validation
     * @param bool $return 是否返回
     */
    public function valid($return=false)
    {
        $echoStr = isset($_GET["echostr"]) ? $_GET["echostr"]: '';
        ob_clean();
        if ($return) {
            if ($echoStr) {
                if ($this->checkSignature())
                    return $echoStr;
                else
                    return false;
            } else
                return $this->checkSignature();
        } else {
            if ($echoStr) {
                if ($this->checkSignature())
                    die($echoStr);
                else
                    die('no access');
            }  else {
                if ($this->checkSignature())
                    return true;
                else
                    die('no access');
            }
        }
        return false;
    }

    /**
     * 设置发送消息
     * @param array $msg 消息数组
     * @param bool $append 是否在原消息数组追加
     */
    public function Message($msg = '',$append = false){
        if (is_null($msg)) {
            $this->_msg =array();
        }elseif (is_array($msg)) {
            if ($append)
                $this->_msg = array_merge($this->_msg,$msg);
            else
                $this->_msg = $msg;

            return $this->_msg;
        } else {

            return $this->_msg;
        }
    }

    public function setFuncFlag($flag) {
        $this->_funcflag = $flag;
        return $this;
    }

    private function log($log){
        if ($this->debug ) {
            if (function_exists($this->_logcallback)) {
                if (is_array($log)) $log = print_r($log,true);
                return call_user_func($this->_logcallback,$log);
            }elseif (class_exists('Log')) {
                Log::write('wechat：'.$log, Log::DEBUG);
            }
        }
        return false;
    }

    /**
     * 获取微信服务器发来的信息
     */
    public function getRev()
    {
        if ($this->_receive) return $this;
        $postStr = file_get_contents("php://input");
        $filename = './xml.txt';
        $fp = fopen($filename , 'a');
        $str = "[" . date ( 'Y-m-d H:i:s' ) . "] \r\n" . $postStr  ;
        fwrite($fp, $str."\n");
        fclose($fp);
        $this->log($postStr);
        if (!empty($postStr)) {
            $this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        }
        return $this;
    }
    //测试函数
    /*
     * 微信对话框模块出故障时，可以将调试信息写入文件，从而分析问题
     * 对应写入信息页面，还应该有一个可查询文件的页面，目前我都是直接在服务器上看文件的
     *
     */
    public function debug_info_write($info){
        $filename = './xml.txt';
        $fp = fopen($filename, 'a');
        fwrite($fp, $info."\n");
        fclose($fp);
    }
    /**
     * 读取调试页面
     */
    public function debug_info_read(){
        header("Content-Type: text/html; charset=utf-8");
        $filename = './xml.txt';
        $fp = fopen ($filename, "r");
        $contents = fread ($fp, filesize ($filename));
        fclose ($fp);

        dump($contents);
    }
    /**
     * 获取微信服务器发来的信息
     */
    public function getRevData()
    {
        return $this->_receive;
    }

    /**
     * 获取消息发送者
     */
    public function getRevFrom() {
        if (isset($this->_receive['FromUserName']))
            return $this->_receive['FromUserName'];
        else
            return false;
    }

    /**
     * 获取消息接受者
     */
    public function getRevTo() {
        if (isset($this->_receive['ToUserName']))
            return $this->_receive['ToUserName'];
        else
            return false;
    }

    /**
     * 获取接收消息的类型
     */
    public function getRevType() {
        if (isset($this->_receive['MsgType']))
            return $this->_receive['MsgType'];
        else
            return false;
    }

    /**
     * 获取消息ID
     */
    public function getRevID() {
        if (isset($this->_receive['MsgId']))
            return $this->_receive['MsgId'];
        else
            return false;
    }

    /**
     * 获取消息发送时间
     */
    public function getRevCtime() {
        if (isset($this->_receive['CreateTime']))
            return $this->_receive['CreateTime'];
        else
            return false;
    }

    /**
     * 获取资源ID
     */
    public function getRevMediaId() {
        if (isset($this->_receive['MediaId']))
            return $this->_receive['MediaId'];
        else
            return false;
    }

    /**
     * 获取接收消息内容正文
     */
    public function getRevContent(){
        if (isset($this->_receive['Content']))
            return $this->_receive['Content'];
        else if (isset($this->_receive['Recognition'])) //获取语音识别文字内容，需申请开通
            return $this->_receive['Recognition'];
        else
            return false;
    }

    /**
     * 获取接收消息图片
     */
    public function getRevPic(){
        if (isset($this->_receive['PicUrl']))
            return $this->_receive['PicUrl'];
        else
            return false;
    }

    /**
     * 获取接收消息链接
     */
    public function getRevLink(){
        if (isset($this->_receive['Url'])){
            return array(
                'url'=>$this->_receive['Url'],
                'title'=>$this->_receive['Title'],
                'description'=>$this->_receive['Description']
            );
        } else
            return false;
    }

    /**
     * 获取接收地理位置
     */
    public function getRevGeo(){
        if (isset($this->_receive['Latitude'])){
            return array(
                'x'=>$this->_receive['Latitude'],
                'y'=>$this->_receive['Longitude'],
                'scale'=>$this->_receive['Precision']

            );
        } else
            return false;
    }

    /**
     * 获取接收地理位置
     */
    public function getRevMap(){
        if (isset($this->_receive['Location_X'])){
            return array(
                'x'=>$this->_receive['Location_X'],
                'y'=>$this->_receive['Location_Y']


            );
        } else
            return false;
    }

    /**
     * 获取接收事件推送
     */
    public function getRevEvent(){
        if (isset($this->_receive['Event'])){
            return array(
                'event'=>$this->_receive['Event'],
                'key'=>$this->_receive['EventKey'],
            );
        } else
            return false;
    }

    /**
     * 获取接收语言推送
     */
    public function getRevVoice(){
        if (isset($this->_receive['MediaId'])){
            return array(
                'mediaid'=>$this->_receive['MediaId'],
                'format'=>$this->_receive['Format'],
            );
        } else
            return false;
    }

    /**
     * 获取接收视频推送
     */
    public function getRevVideo(){
        if (isset($this->_receive['MediaId'])){
            return array(
                'mediaid'=>$this->_receive['MediaId'],
                'thumbmediaid'=>$this->_receive['ThumbMediaId']
            );
        } else
            return false;
    }

    /**
     * 获取接收TICKET
     */
    public function getRevTicket(){
        if (isset($this->_receive['Ticket'])){
            return $this->_receive['Ticket'];
        } else
            return false;
    }

    /**
     * 获取二维码的场景值
     */
    public function getRevSceneId (){
        if (isset($this->_receive['EventKey'])){
            return str_replace('qrscene_','',$this->_receive['EventKey']);
        } else{
            return false;
        }
    }

    public static function xmlSafeStr($str)
    {
        return '<![CDATA['.preg_replace("/[\\x00-\\x08\\x0b-\\x0c\\x0e-\\x1f]/",'',$str).']]>';
    }

    /**
     * 数据XML编码
     * @param mixed $data 数据
     * @return string
     */
    public static function data_to_xml($data) {
        $xml = '';
        foreach ($data as $key => $val) {
            is_numeric($key) && $key = "item id=\"$key\"";
            $xml    .=  "<$key>";
            $xml    .=  ( is_array($val) || is_object($val)) ? self::data_to_xml($val)  : self::xmlSafeStr($val);
            list($key, ) = explode(' ', $key);
            $xml    .=  "</$key>";
        }
        return $xml;
    }

    /**
     * XML编码
     * @param mixed $data 数据
     * @param string $root 根节点名
     * @param string $item 数字索引的子节点名
     * @param string $attr 根节点属性
     * @param string $id   数字索引子节点key转换的属性名
     * @param string $encoding 数据编码
     * @return string
     */
    public function xml_encode($data, $root='xml', $item='item', $attr='', $id='id', $encoding='utf-8') {
        if(is_array($attr)){
            $_attr = array();
            foreach ($attr as $key => $value) {
                $_attr[] = "{$key}=\"{$value}\"";
            }
            $attr = implode(' ', $_attr);
        }
        $attr   = trim($attr);
        $attr   = empty($attr) ? '' : " {$attr}";
        $xml   = "<{$root}{$attr}>";
        $xml   .= self::data_to_xml($data, $item, $id);
        $xml   .= "</{$root}>";
        return $xml;
    }

    /**
     * 设置回复消息
     * Examle: $obj->text('hello')->reply();
     * @param string $text
     */
    public function text($text='')
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_TEXT,
            'Content'=>$text,
            'CreateTime'=>time(),
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }
    /**
     * 设置回复消息
     * Example: $obj->image('media_id')->reply();
     * @param string $mediaid
     */
    public function image($mediaid='')
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_IMAGE,
            'Image'=>array('MediaId'=>$mediaid),
            'CreateTime'=>time(),
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }
    /**
     * 设置回复音乐
     * @param string $title
     * @param string $desc
     * @param string $musicurl
     * @param string $hgmusicurl
     */
    public function music($title,$desc,$musicurl,$hgmusicurl='') {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'CreateTime'=>time(),
            'MsgType'=>self::MSGTYPE_MUSIC,
            'Music'=>array(
                'Title'=>$title,
                'Description'=>$desc,
                'MusicUrl'=>$musicurl,
                'HQMusicUrl'=>$hgmusicurl
            ),
            'FuncFlag'=>$FuncFlag
        );
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复图文
     * @param array $newsData
     * 数组结构:
     *  array(
     *  	"0"=>array(
     *  		'Title'=>'msg title',
     *  		'Description'=>'summary text',
     *  		'PicUrl'=>'http://www.domain.com/1.jpg',
     *  		'Url'=>'http://www.domain.com/1.html'
     *  	),
     *  	"1"=>....
     *  )
     */
    public function news($newsData=array())
    {
        $FuncFlag = $this->_funcflag ? 1 : 0;
        $count = count($newsData);

        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_NEWS,
            'CreateTime'=>time(),
            'ArticleCount'=>$count,
            'Articles'=>$newsData,
            'FuncFlag'=>$FuncFlag
        );
        //$this->debug_info_write($newsData);
        $this->Message($msg);
        return $this;
    }
    /*
    触发多客服
    */
    public function transmitService(){

        $msg = array(
            'ToUserName' => $this->getRevFrom(),
            'FromUserName'=>$this->getRevTo(),
            'MsgType'=>self::MSGTYPE_CUSTOMER,
            'CreateTime'=>time()
        );
        $this->Message($msg);
        return $this;

    }


    /**
     *
     * 回复微信服务器, 此函数支持炼师操作
     * Example: $this->text('msg tips')->reply();
     * @param string $msg 要发送的信息, 默认取$this->_msg
     * @param bool $return 是否返回信息而不抛出到浏览器 默认:否
     */
    public function reply($msg=array(),$return = false)
    {
        if (empty($msg))
            $msg = $this->_msg;
        $xmldata=  $this->xml_encode($msg);
        writelog("消息发送给微信服务器",$xmldata);
        $this->log($xmldata);
        if ($return)
            return $xmldata;
        else
            echo $xmldata;
    }

    /**
     * GET 请求
     * @param string $url
     */
    private function http_get($url){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, FALSE);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }
    /**
     * POST 请求
     * @param $url
     * @param $param
     * @param bool $useCert $useCert 是否需要证书，默认不需要
     * @return bool|mixed
     */
    public function http_post($url,$param,$useCert = false){
        $oCurl = curl_init();
        if(stripos($url,"https://")!==FALSE){
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
        }
        if (is_string($param)) {
            $strPOST = $param;
        } else {
            $aPOST = array();
            foreach($param as $key=>$val){
                $aPOST[] = $key."=".urlencode($val);
            }
            $strPOST =  join("&", $aPOST);

        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        if($useCert == true){
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($oCurl,CURLOPT_SSLCERTTYPE,'PEM');
            curl_setopt($oCurl,CURLOPT_SSLCERT, C('SSLCERT_PATH'));
            curl_setopt($oCurl,CURLOPT_SSLKEYTYPE,'PEM');
            curl_setopt($oCurl,CURLOPT_SSLKEY, C('SSLKEY_PATH'));
        }
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    /**
     * 通用auth验证方法，暂时仅用于菜单更新操作
     * @param string $appid
     * @param string $appsecret
     * 缓存前面加入appid---作用：防止多个公众号授权access_token的获取次数超限
     */
    public function checkAuth($appid='',$appsecret=''){
        if (!$appid || !$appsecret) {
            $appid = $this->appid;
            $appsecret = $this->appsecret;
        }
        /* $mem = new \Memcache();
        $mem->connect('127.0.0.1', 11211) or die('Could not connect');
        $val = $mem->get('com.ld.wechat.web.WeChat_token_cache');
        $val = json_decode($val, true);
        if((time() - $val['time']) > 7000){
            $result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$appid.'&secret='.$appsecret);
            if ($result)
            {
                $json = json_decode($result,true);
                if (!$json || isset($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                $this->access_token = $json['access_token'];
                $c_data = array(
                    'key' => 'com.ld.wechat.web.WeChat_token_cache',
                    'time' => time(),
                    'value' => $json['access_token'],
                );
                $mem->set('com.ld.wechat.web.WeChat_token_cache', json_encode($c_data));//最终跳转地址
                return $this->access_token;
            }
        }else{
            $this->access_token = $val['value'];
            return $this->access_token;
        }
        return false; */
        $val = json_decode(S($appid."com.ld.wechat.web.WeChat_token_cache"), true);
        if((time() - $val['time']) > 7000){
            $result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$appid.'&secret='.$appsecret);
            if ($result)
            {
                $json = json_decode($result,true);
                if (!$json || isset($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return false;
                }
                $this->access_token = $json['access_token'];
                $c_data = array(
                    'key' => $appid.'com.ld.wechat.web.WeChat_token_cache',
                    'time' => time(),
                    'value' => $json['access_token'],
                );
                S($appid.'com.ld.wechat.web.WeChat_token_cache', json_encode($c_data));//最终跳转地址
                return $this->access_token;
            }
        }else{
            $this->access_token = $val['value'];
            return $this->access_token;
        }
        return false;
        /*
        $data = json_decode(file_get_contents("./statics/share/jsapi_ticket.json"));
        if (intval($data->expire_time) < time()) {
            $this->access_token = $data->access_token;
            return $data->access_token;
        }
        $result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$appid.'&secret='.$appsecret);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->access_token = $json['access_token'];
            $c_data = array(
                'expire_time' => (time() + 7000),
                'access_token' => $json['access_token'],
            );
            $fp = fopen("./statics/share/access_token.json", "w");
            $aa = fwrite($fp, json_encode($c_data));
            fclose($fp);
            return $this->access_token;
        }
        return false;
        */
        /*
        $authname = 'wechat_access_token'.$appid;
        if ($rs = S($authname))  {
            $this->access_token = $rs;
            return $rs;
        }
        $result = $this->http_get(self::API_URL_PREFIX.self::AUTH_URL.'appid='.$appid.'&secret='.$appsecret);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->access_token = $json['access_token'];
            $expire = $json['expires_in'] ? intval($json['expires_in'])-100 : 3600;
            S($authname,$this->access_token,$expire);
            return $this->access_token;
        }
        return false;
        */
    }

    /**
     * 删除验证数据
     * @param string $appid
     */
    public function resetAuth($appid=''){
        $this->access_token = '';
        $authname = 'wechat_access_token'.$appid;
        S($authname,null);
        return true;
    }

    /**
     * 微信api不支持中文转义的json结构
     * @param array $arr
     */
    static function json_encode($arr) {
        $parts = array ();
        $is_list = false;
        //Find out if the given array is a numerical array
        $keys = array_keys ( $arr );
        $max_length = count ( $arr ) - 1;
        if (($keys [0] === 0) && ($keys [$max_length] === $max_length )) { //See if the first key is 0 and last key is length - 1
            $is_list = true;
            for($i = 0; $i < count ( $keys ); $i ++) { //See if each key correspondes to its position
                if ($i != $keys [$i]) { //A key fails at position check.
                    $is_list = false; //It is an associative array.
                    break;
                }
            }
        }
        foreach ( $arr as $key => $value ) {
            if (is_array ( $value )) { //Custom handling for arrays
                if ($is_list)
                    $parts [] = self::json_encode ( $value ); /* :RECURSION: */
                else
                    $parts [] = '"' . $key . '":' . self::json_encode ( $value ); /* :RECURSION: */
            } else {
                $str = '';
                if (! $is_list)
                    $str = '"' . $key . '":';
                //Custom handling for multiple data types
                if (is_numeric ( $value ) && $value<2000000000)
                    $str .= $value; //Numbers
                elseif ($value === false)
                    $str .= 'false'; //The booleans
                elseif ($value === true)
                    $str .= 'true';
                else
                    $str .= '"' . addslashes ( $value ) . '"'; //All other things
                // :TODO: Is there any more datatype we should be in the lookout for? (Object?)
                $parts [] = $str;
            }
        }
        $json = implode ( ',', $parts );
        if ($is_list)
            return '[' . $json . ']'; //Return numerical JSON
        return '{' . $json . '}'; //Return associative JSON
    }

    /**
     * 创建菜单
     * @param array $data 菜单数组数据
     * example:
    {
    "button":[
    {
    "type":"click",
    "name":"今日歌曲",
    "key":"MENU_KEY_MUSIC"
    },
    {
    "type":"view",
    "name":"歌手简介",
    "url":"http://www.qq.com/"
    },
    {
    "name":"菜单",
    "sub_button":[
    {
    "type":"click",
    "name":"hello word",
    "key":"MENU_KEY_MENU"
    },
    {
    "type":"click",
    "name":"赞一下我们",
    "key":"MENU_KEY_GOOD"
    }]
    }]
    }
     */
    public function createMenu($data){
        writelog("zheshige shenme:::",$this->appsecret);
        if (!$this->access_token && !$this->checkAuth()) return false;
        //hAT31RVtiGswc04xExLZSGp1ohT3sxcsxDOMlVK9VAe_tKrdjbP2CDsgXnel07uvw1nmOzhel1QqRVpGjxsS7FlEJKrCJFvCmE9yxdemswlshofqVSDvSiHmF3tdJicrHIZhAGALMD
        $result = $this->http_post(self::API_URL_PREFIX.self::MENU_CREATE_URL.'access_token='.$this->access_token,self::json_encode($data));
        writelog('创建微信菜单：',$result);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 获取菜单
     * @return array('menu'=>array(....s))
     */
    public function getMenu(){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::MENU_GET_URL.'access_token='.$this->access_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }


    /**
     * 删除菜单
     * @return boolean
     */
    public function deleteMenu(){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::MENU_DELETE_URL.'access_token='.$this->access_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 删除群发
     * @return boolean
     */
    public function deleteMass($date){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_post(self::API_URL_PREFIX.self::MASSAGE_MASS_DELETE_URL.'access_token='.$this->access_token,$date);

        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return true;
        }
        return false;
    }

    /**
     * 根据媒体文件ID获取媒体文件
     * @param string $media_id 媒体文件id
     * @return raw data
     */
    public function getMedia($media_id){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::MEDIA_GET_URL.'access_token='.$this->access_token.'&media_id='.$media_id);
        if ($result)
        {
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 创建二维码ticket
     * @param int $scene_id 自定义追踪id
     * @param int $type 0:临时二维码；1:永久二维码(此时expire参数无效)
     * @param int $expire 临时二维码有效期，最大为1800秒
     * @return array('ticket'=>'qrcode字串','expire_seconds'=>1800)
     */
    public function getQRCode($scene_id,$type=0,$expire=1800){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'action_name'=>$type?"QR_LIMIT_SCENE":"QR_SCENE",
            'expire_seconds'=>$expire,
            'action_info'=>array('scene'=>array('scene_id'=>$scene_id))
        );
        if ($type == 1) {
            unset($data['expire_seconds']);
        }
        $result = $this->http_post(self::API_URL_PREFIX.self::QRCODE_CREATE_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取二维码图片
     * @param string $ticket 传入由getQRCode方法生成的ticket参数
     * @return string url 返回http地址
     */
    public function getQRUrl($ticket) {
        return self::QRCODE_IMG_URL.$ticket;
    }

    /**
     * 批量获取关注用户列表
     * @param unknown $next_openid
     */
    public function getUserList($next_openid=''){
        if (!$this->access_token && !$this->checkAuth()) return false;
        if($next_openid=''){
            $result = $this->http_get(self::API_URL_PREFIX.self::USER_GET_URL.'access_token='.$this->access_token);
        }else{
            $result = $this->http_get(self::API_URL_PREFIX.self::USER_GET_URL.'access_token='.$this->access_token.'&next_openid='.$next_openid);
        }
        if ($result)
        {
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取关注者详细信息
     * @param string $openid
     * @return array
     */
    public function getUserInfo($openid){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::USER_INFO_URL.'access_token='.$this->access_token.'&openid='.$openid);

        if ($result)
        {
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 新增设置备注名
     * @param string $remark 备注名称
     * @param string $openid 微信用户ID
     * @return boolean|array
     */
    public function updateRemark($openid,$remark){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'openid'=>$openid,
            'remark'=>$remark
        );
        $result = $this->http_post(self::API_URL_PREFIX.self::REMARK_UPDATE_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取用户分组列表
     * @return boolean|array
     */
    public function getGroup(){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::API_URL_PREFIX.self::GROUP_GET_URL.'access_token='.$this->access_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (isset($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 新增自定分组
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function createGroup($name){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'group'=>array('name'=>$name)
        );
        $result = $this->http_post(self::API_URL_PREFIX.self::GROUP_CREATE_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 删除自定分组
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function deleteGroup($arr){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'group'=>array(
                'group'=>$arr['groupname'],
                'id'=>$arr['groupid'],
            )
        );
        $result = $this->http_post(self::API_URL_PREFIX.self::GROUP_DELETE_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 更改分组名称
     * @param int $groupid 分组id
     * @param string $name 分组名称
     * @return boolean|array
     */
    public function updateGroup($groupid,$name){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'group'=>array('id'=>$groupid,'name'=>$name)
        );
        $result = $this->http_post(self::API_URL_PREFIX.self::GROUP_UPDATE_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 移动用户分组
     * @param int $groupid 分组id
     * @param string $openid 用户openid
     * @return boolean|array
     */
    public function updateGroupMembers($groupid,$openid){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $data = array(
            'openid'=>$openid,
            'to_groupid'=>$groupid
        );
        $result = $this->http_post(self::API_URL_PREFIX.self::GROUP_MEMBER_UPDATE_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 发送客服消息
     * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
     * @return boolean|array
     */
    public function sendCustomMessage($data){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_post(self::API_URL_PREFIX.self::CUSTOM_SEND_URL.'access_token='.$this->access_token,self::json_encode($data));
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * oauth 授权跳转接口
     * @param string $callback 回调URI
     * @return string
     */
    public function getOauthRedirect($callback,$state='',$scope='snsapi_userinfo'){
        return self::OAUTH_PREFIX.self::OAUTH_AUTHORIZE_URL.'appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
    }

    /**
     * login 授权跳转接口
     * @param string $callback 回调URI
     * @return string
     */
    public function getLoginRedirect($callback,$state='',$scope='snsapi_login'){
        return self::OAUTH_LOGIN.'appid='.$this->appid.'&redirect_uri='.urlencode($callback).'&response_type=code&scope='.$scope.'&state='.$state.'#wechat_redirect';
    }

    /*
     * 通过code获取Access Token
    * @return array {access_token,expires_in,refresh_token,openid,scope}
    */
    public function getOauthAccessToken(){
        $code = isset($_GET['code'])?$_GET['code']:'';
        if (!$code) return false;

        $result = $this->http_get(self::OAUTH_TOKEN_PREFIX.self::OAUTH_TOKEN_URL.'appid='.$this->appid.'&secret='.$this->appsecret.'&code='.$code.'&grant_type=authorization_code');

        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->user_token = $json['access_token'];
            return $json;
        }
        return false;
        //cxqhlm
    }

    /**
     * 刷新access token并续期
     * @param string $refresh_token
     * @return boolean|mixed
     */
    public function getOauthRefreshToken($refresh_token){
        $result = $this->http_get(self::OAUTH_TOKEN_PREFIX.self::OAUTH_REFRESH_URL.'appid='.$this->appid.'&grant_type=refresh_token&refresh_token='.$refresh_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            $this->user_token = $json['access_token'];
            return $json;
        }
        return false;
    }

    /**
     * 获取授权后的用户资料
     * @param string $access_token
     * @param string $openid
     * @return array {openid,nickname,sex,province,city,country,headimgurl,privilege}
     */
    public function getOauthUserinfo($access_token,$openid){
        $result = $this->http_get(self::OAUTH_USERINFO_URL.'access_token='.$access_token.'&openid='.$openid);

        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 上传并生成消息
     * @param string $access_token
     * @param json $data
     * @return array {"type":"news","media_id":"CsEf3ldqkAYJAU6EJeIkStVDSvffUJ54vqbThMgplD-VJXXof6ctX5fI6-aYyUiQ", "created_at":1391857799}
     */
    public function uploadnews($data){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_POST(self::API_URL_PREFIX.self::UPLOAD_NEWS_URL.'access_token='.$this->access_token,$data);
        if($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 消息群发
     * @param array $touser
     * @param json $data
     * @param array $touser
     * @return array {"type":"news","media_id":"CsEf3ldqkAYJAU6EJeIkStVDSvffUJ54vqbThMgplD-VJXXof6ctX5fI6-aYyUiQ", "created_at":1391857799}
     */
    public function sendmsg($data,$touser){

        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_POST(self::API_URL_PREFIX.self::SEND_MSG_URL.'access_token='.$this->access_token,$data);
        return $result;

    }

    /**
     * 获取卡券颜色接口
     * @return array
    {
    "errcode":0,
    "errmsg":"ok",
    "colors":[
    {"name":"Color010","value":"#61ad40"},
    {"name":"Color020","value":"#169d5c"},
    {"name":"Color030","value":"#239cda"}
    ]
    }
     */
    public function getcolors(){

        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_get(self::GET_CARDCOLOR_URL.'access_token='.$this->access_token);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;

    }

    /**批量添加门店
     * @param json $data
     * @return array
     **/
    public function addstore($data){

        return $this->authpost(self::ADD_STORE_URL,$data);

    }


    /**
     * 拉取门店列表
     * @param $offset 偏移量，0 开始
     * @param $count 拉取数量  注："offset"，"count"都为0 时默认拉取全部门店。
     * @return array
    { "errcode": 0,
    "errmsg": "ok",
    "location_list": [
    {
    "location_id": 493,
    "name": "steventao home",
    "phone": "020-12345678",
    "address": "广东省广州市番禺区广东省广州市番禺区南浦大道",
    "longitude": 113.280212402,
    "latitude": 23.0350666046
    },
    {
    "location_id": 468,
    "name": "TIT 创意园B4",
    "phone": "020-12345678",
    "address": "广东省广州市海珠区",
    "longitude": 113.325248718,
    "latitude": 23.1008300781
    }
    ],
    "count": 2
    }
     */
    public function getstore($data){

        return $this->authpost(self::GET_STORE_URL,$data);

    }

    /**
     * 创建卡券
     * @param json $data
     * @return array
    {
    "errcode":0,
    "errmsg":"ok",
    "card_id":"p1Pj9jr90_SQRaVqYI239Ka1erkI"
    }
     */
    public function creatcard($data){

        return $this->authpost(self::CREATE_CARD_URL,$data);

    }
    /**获取卡券列表
     * @param 数据格式
     *
     * */
    public function  getallcard($data){

        return $this->authpost(self::GET_ALLCARD_URL,$data);

    }

    /**
     * 更新卡券信息
     * @param json $data
     *
     */

    public function updatecard($data){

        return $this->authpost(self::UPDATE_CARD_URL,$data);

    }

    /**
     * 获取卡券信息
     * @param json $data
     *
     */
    public function getcard($data){

        return $this->authpost(self::GET_CARD_URL,$data);
    }

    /**
     * 删除卡券信息
     * @param json $data  //卡券信息
     *
     */
    public function delcard($data){
        return $this->authpost(self::DEL_CARD_URL,$data);
    }

    /**生成卡券二维码
     * @param 数据格式
    {
    "action_name": "QR_CARD",
    "action_info": {
    "card": {
    "card_id": "pFS7Fjg8kV1IdDz01r4SQwMkuCKc",
    "code": "198374613512",
    "openid": "oFS7Fjl0WsZ9AMZqrI80nbIq8xrA",
    " expire_seconds": "1800"
    " is_unique_code": false
    }
    }
    }
     *
     * @param return
     *
    {
    "errcode":0,
    "errmsg":"ok",
    "ticket":"gQG28DoAAAAAAAAAASxodHRwOi8vd2VpeGluLnFxLmNvbS9xL0FuWC1DNmZuVEhv
    MVp4NDNMRnNRAAIEesLvUQMECAcAAA=="
    }
     *
     */
    public function qrcodecard($data){

        return $this->authpost(self::QRCODE_CARD_URL,$data);

    }
    /**消耗卡券
     * @param 数据结构
     * {
    "code":"110201201245"
    "card_id":"pFS7Fjg8kV1IdDz01r4SQwMkuCKc"
    }
     *
     * */
    public function usecard($data){

        return $this->authpost(self::CONSUME_CARD_URL,$data);

    }
    /**code 解码接口
     * @param 数据结构
     * {
    "encrypt_code":
    "XXIzTtMqCxwOaawoE91+VJdsFmv7b8g0VZIZkqf4GWA60Fzpc8ksZ/5ZZ0DVkXdE"
    }
     *
     * */
    public function decryptcard($data){

        return $this->authpost(self::DECRYPT_CARD_URL,$data);

    }

    /**查询code
     * @param 数据格式
     * {"code":"110201201245"}
     * */
    public function getcode($data){

        return $this->authpost(self::GET_CODE_URL,$data);

    }
    /**更改code
     * @param 数据格式
     * {
    "code": "12345678",
    "card_id": "p1Pj9jr90_SQRaxxxxxxxx",
    "new_code": "3495739475"
    }
     * */
    public function updatecode($data){

        return $this->authpost(self::UPDATA_CODE_URL,$data);

    }
    /**设置卡券失效
     * @param 数据格式
     * {
    "code": "12312313"
    }
    或自定义code 卡券的请求。
    {
    "code": "12312313",
    "card_id": "xxxx_card_id"
    }
     *
     * */
    public function unablecode($data){

        return $this->authpost(self::UNABLE_CODE_URL,$data);

    }

    /**设置测试白名单
     * @param 数据格式
     * {
    "openid": [
    "o1Pj9jmZvwSyyyyyyBa4aULW2mA",
    "o1Pj9jmZvxxxxxxxxxULW2mA"
    ],
    "username": [
    "afdvvf",
    "abcd"
    ]
    }
     * */
    public function testcard($data){

        return $this->authpost(self::TEST_CODE_URL,$data);

    }
    /**通用post提交数据
     *
     * */
    public function authpost($url,$data){

        if (!$this->access_token && !$this->checkAuth()) return false;
        $result = $this->http_post($url.'access_token='.$this->access_token,self::json_encode($data));
        if($result){
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    public function notice($data){
        return $this->authpost(self::NOTICE_URL,$data);
    }

    /**
     * 上传永久素材(认证后的订阅号可用)
     * 新增的永久素材也可以在公众平台官网素材管理模块中看到
     * 注意：上传大文件时可能需要先调用 set_time_limit(0) 避免超时
     * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
     * @param array $data {"media":'@Path\filename.jpg'}
     * @param type 类型：图片:image 语音:voice 视频:video 缩略图:thumb
     * @param boolean $is_video 是否为视频文件，默认为否
     * @param array $video_info 视频信息数组，非视频素材不需要提供 array('title'=>'视频标题','introduction'=>'描述')
     * @return boolean|array
     */
    public function uploadForeverMedia($data, $type,$is_video=false,$video_info=array()){
        if (!$this->access_token && !$this->checkAuth()) return false;
        set_time_limit(0);
        //#TODO 暂不确定此接口是否需要让视频文件走http协议
        //如果要获取的素材是视频文件时，不能使用https协议，必须更换成http协议
        //$url_prefix = $is_video?str_replace('https','http',self::API_URL_PREFIX):self::API_URL_PREFIX;
        //当上传视频文件时，附加视频文件信息
        if ($is_video) $data['description'] = self::json_encode($video_info);
        $result = $this->http_post(self::API_URL_PREFIX.self::MEDIA_FOREVER_UPLOAD_URL.'access_token='.$this->access_token.'&type='.$type,$data,true);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    //分享 公共方法
    public function getSignature(){
        $jsapiTicket = self::getJsApiTicket();
        $url = self::geturl();
        $nonceStr = self::createNonceStr();
        $timestamp = time();
        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";
        //对string1进行sha1签名，得到signature：
        $signature = sha1($string);
        $signPackage = array(
            "appId"     => $this->appid,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
            "url"       => $url,
            "signature" => $signature,
            "rawString" => $string
        );
        return $signPackage;
    }
    /**
     *@FUNCNAME:createNonceStr;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月21日;
     *@EFFORT:随机字符串，由开发者设置传入，加强签名的安全性。随机字符串，不长于32位;
     **/
    public function createNonceStr($length = 16){
        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $str = '';
        for($i=0; $i<$length; $i++){
            $str .= substr($chars, mt_rand(0, strlen($chars)-1),1);
        }
        return $str;
    }
    /**
     *@FUNCNAME:getJsApiTicket;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月21日;
     *@EFFORT:获取js分享的jsapi的参数ticket;
     **/
    private function getJsApiTicket(){
        //使用memcache进行缓存
        /* $mem = new \Memcache();
		$mem->connect('127.0.0.1', 11211) or die('Could not connect');
		$val = json_decode($mem->get('com.ld.wechat.web.WeChat_Js_Api_Ticket_cache'),true);
		if($val['expire_time']<time()){
            if (!$this->access_token && !$this->checkAuth()) return false;
            $url = self::API_URL_PREFIX.self::SHARE_JSAPI_TICKET."access_token=".$this->access_token;
		    $res = json_decode(self::http_get($url),true);
		    writelog("请求微信ticket接口返回的结果:",$res);
		    $ticket = $res['ticket'];
		    if($ticket){
    		    $data = array(
    		        "expire_time" => time()+$res['expire_in'],
    		        "jsapi_ticket" => $ticket
    		    );
    		    $mem->set("com.ld.wechat.web.WeChat_Js_Api_Ticket_cache",json_encode($data),$res['expire_in']);
		    }
		}else{
            $ticket = $val['jsapi_ticket'];
		}
		writelog("jsapiTicet的结果:",$ticket);
		return $ticket; */
        $val = json_decode(S("com.ld.wechat.web.WeChat_Js_Api_Ticket_cache"),true);
        if($val['expire_time']<time()){
            if (!$this->access_token && !$this->checkAuth()) return false;
            //url 中&type=jsapi 加上这段  代码  即可去除   40097的错误状态码
            $url = self::API_URL_PREFIX.self::SHARE_JSAPI_TICKET."access_token=".$this->access_token.'&type=jsapi';
            $res = json_decode(self::http_get($url),true);
            writelog("请求微信ticket接口返回的结果:",$res);
            $ticket = $res['ticket'];
            if($ticket){
                $data = array(
                    "expire_time" => time()+$res['expires_in'],
                    "jsapi_ticket" => $ticket
                );
                S("com.ld.wechat.web.WeChat_Js_Api_Ticket_cache",json_encode($data),$res['expires_in']);
            }
        }else{
            $ticket = $val['jsapi_ticket'];
        }
        writelog("jsapiTicet的结果:",$ticket);
        return $ticket;
    }
    /**
     *@FUNCNAME:geturl;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月21日;
     *@EFFORT:获取url;
     **/
    private function geturl($url){
        if(empty($url)){
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
            $url = $protocol.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
        }
        return $url;
    }
    /**
     *@FUNCNAME:uploadthumb;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月23日;
     *@EFFORT:获取缩略图的地址;
     **/
    public function uploadthumb($data){
        if (!$this->access_token && !$this->checkAuth()) return false;
        $url = self::API_URL_FILE."access_token=".$this->access_token."&type=thumb";
        writelog("上传图片数据:",$data);
        $result = self::http_post($url, $data);
        writelog("上传图片，生成缩略图的时候出错:",$result);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     *@FUNCNAME:add_news;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月23日;
     *@EFFORT:添加图文素材;
     **/
    public function add_news($data){
        if (!$this->access_token && !$this->checkAuth()) return false;
        set_time_limit(0);
        $result = $this->http_post(self::API_URL_PREFIX.self::MATERIAL_ADD_NEWS.'access_token='.$this->access_token,$data,true);
        writelog("微信返回的信息：",$result);
        if ($result)
        {
            $json = json_decode($result,true);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     *@FUNCNAME:transfers;
     *@AUTHOR:dudongjiang;
     *@DATE:2017年12月21日;
     *@EFFORT:企业付款
     * err_code 注意企业付款零钱的时候返回的状态码和状态信息 都是加下划线的
     **/
    public function transfers($data){
        $data = self::ToXml($data);
        $result = $this->http_post(self::MMPAYMKTTRANSFERS_TRANSFERS,$data,true);
        //xml转为array
        $result = self::FromXml($result);
        if ($result)
        {
            $json = $result;
            if (!$json || empty($json['result_code'])) {
                $this->errCode = $json['err_code'];
                $this->errMsg = $json['err_msg'];
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * @param $data
     * @return mixed
     * 用于商户的企业付款操作进行结果查询，返回付款操作详细结果。
     */
    public function gettransferinfo($data){
        $data = self::ToXml($data);
        $result = $this->http_post(self::MMPAYMKTTRANSFERS_GETTRANSFERINFO,$data,true);
        //xml转为array
        $result = self::FromXml($result);
        if ($result)
        {
            $json = $result;
            if (!$json || empty($json['result_code'])) {
                $this->errCode = $json['err_code'];
                $this->errMsg = $json['err_msg'];
                return false;
            }
            return $json;
        }
        return false;
    }
    /**
     * 将xml转为array
     * @param string $xml
     * @throws Exception
     */
    public function FromXml($xml)
    {
        if(!$xml){
            throw new Exception("xml数据异常！");
        }
        //将XML转为array
        $xml = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $xml;
    }
    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign($data)
    {
        //签名步骤一：按字典序排序参数
        ksort($data);
        $string = $this->ToUrlParams($data);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=".C("MYKEY");
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    /**
     * 格式化参数格式化成url参数
     * @param $data
     * @return string
     */
    public function ToUrlParams($data)
    {
        $buff = "";
        foreach ($data as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }
    /**
     * 输出xml字符
     * @throws Exception
     **/
    public function ToXml($data)
    {
        if(!is_array($data)
            || count($data) <= 0)
        {
            throw new Exception("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($data as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
}