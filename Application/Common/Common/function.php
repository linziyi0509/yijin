<?php  
use Vendor\Wechat\Wechat;
function md5_($str) {
	return md5('*&'.$str.'^%$');
}

function check_login(){
	if (empty($_SESSION['user_name'])) {
		header('location:/Admin/login');
	}
	/*if (empty($_SESSION['user_name'])) {
		header('location:/admin/login');
	}*/
}

//判断权限
function checklevel($id){
	if (in_array($id, explode(',', $_SESSION['levelstr'])) || $_SESSION['levelstr'] == 'all') {
		return true;
	} else {
		return false;
	}
	
}


//将数字转换成大写字母
function numtostr($num){
	$arr = array(0=>'A',1=>'B',2=>'C',3=>'D',4=>'E',5=>'F',6=>'G',7=>'H',8=>'I',9=>'J',10=>'K',11=>'L',12=>'M',13=>'N',14=>'O',15=>'P',16=>'Q',17=>'R',18=>'S',19=>'T',20=>'U',21=>'V',22=>'W',23=>'X',24=>'Y',25=>'Z');
	return $arr[$num];
}

function deldir($directory){
	if(is_dir($directory)) {
		if($dir_handle=@opendir($directory)) {
			while(false!==($filename=readdir($dir_handle))) {
				$file=$directory."/".$filename;
				if($filename!="." && $filename!="..") {
					if(is_dir($file)) {
						deldir($file);
					}
					else {
						unlink($file);
					}
				}
			}
			closedir($dir_handle);

		}
		return rmdir($directory);
	}
}


//搜索关键字标红
function returnred($key,$content){
	return str_replace($key,'<font color="red">'.$key.'</font>',$content);
}



function SendMail($server,$sendmail,$password,$address,$title,$message){
	$mail=new \Org\Wzf\PHPMailer();
	// 设置PHPMailer使用SMTP服务器发送Email
	$mail->IsSMTP();
	// 设置邮件的字符编码，若不指定，则为'UTF-8'
	$mail->CharSet='UTF-8';
	// 添加收件人地址，可以多次使用来添加多个收件人
	$mail->AddAddress($address);
	// 设置邮件正文
	$mail->Body=$message;
	// 设置邮件头的From字段。
	$mail->From=$sendmail;
	// 设置发件人名字
	$mail->FromName='积分系统';
	// 设置邮件标题
	$mail->Subject=$title;
	// 设置SMTP服务器。
	$mail->Host=$server;
	// 设置为“需要验证”
	$mail->SMTPAuth=true;
	// 设置用户名和密码。
	$mail->Username=$sendmail;
	$mail->Password=$password;
	// 发送邮件。
	//return($mail->Send());
	$mail->Send();
}

function sendMail2($to, $title, $content) {
    Vendor('PHPMailer.phpmailer');
    Vendor('PHPMailer.smtp');
    $mail = new \Org\Wzf\PHPMailer();
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host=C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
    $mail->Username = C('MAIL_USERNAME'); //你的邮箱名
    $mail->Password = C('MAIL_PASSWORD') ; //邮箱密码
    $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
    $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
    $mail->AddAddress($to,"尊敬的管理员");
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
    $mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
    $mail->Subject =$title; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody = "这是一个纯文本的身体在非营利的HTML电子邮件客户端"; //邮件正文不支持HTML的备用显示
    return($mail->send());
}

	function array_to_json($data){
	foreach ( $data as $key => $value ) { 
        $data[$key] = urlencode ( $value ); 
    }     
		echo urldecode ( json_encode ( $data ) );
	}
	function check_phone($str){
		if(preg_match("/^13[0-9]{1}[0-9]{8}$|15[0189]{1}[0-9]{8}$|189[0-9]{8}$/",$str)){
			return 1;
		}else{
			return 0;
		}
	}

	
	function addlog($arr,$name,$description){
		error_log ($description.':'.date('Y-m-d H:i:s').'----'.var_export($arr,true).'
			',3,"./log/".date('Y-m-d')."-".$name.".php");
	}

	//获取两个经纬度距离
	function getdistance($lng1,$lat1,$lng2,$lat2){
		//将角度转为狐度
		$radLat1=deg2rad($lat1);//deg2rad()函数将角度转换为弧度
		$radLat2=deg2rad($lat2);
		$radLng1=deg2rad($lng1);
		$radLng2=deg2rad($lng2);
		$a=$radLat1-$radLat2;
		$b=$radLng1-$radLng2;
		$s=2*asin(sqrt(pow(sin($a/2),2)+cos($radLat1)*cos($radLat2)*pow(sin($b/2),2)))*6378.137*1000;
		return $s;
	}

	function j_encode($data){
		addlog($data,'api-'.ACTION_NAME,'返回参数：');
		$data = json_encode($data);
		echo $data;exit;
	}

	/**
	接口解密
	机密算法  token*159357-13579
	*/
	function md6($str){
		return (intval($str)+13579)/159357;
	}


	/**
	验证参数
	*/
	function check_param($arr){
		foreach ($arr as $key => $value) {
			if (empty($_REQUEST["$value"])) {
				$result = array('result'=>'0','message'=>'缺少参数'.$value,'data'=>'');
				echo json_encode($result);exit;
			}
		}
	}

	/**
	验证参数
	*/
	function check_param2($arr){
		foreach ($arr as $key => $value) {
			if (empty($_REQUEST["$value"])) {
				echo '<div style="padding-top:100px;text-align:center;font-size:55px;">访问的页面不存在 <a href="javascript:history.go(-1);">返回</a></div>';exit;
			}
		}
	}
	/**
	根据地址获取经纬度
	*/


	function getLatLong($add){
    	//转换 返回
    	 $url = 'http://api.map.baidu.com/geocoder?address='.$add.'&output=xml&coord_type=wgs84&src=sanshiliuji';
    	 $result = file_get_contents($url);
	    if($result){
			$arr = json_decode(json_encode(simplexml_load_string($result)),true);
        }
        if($arr['result']['location']['lat']){
            $data['lat'] = $arr['result']['location']['lat'];
            $data['lng'] = $arr['result']['location']['lng'];
        }else {
        	$data['lat'] = 0;
            $data['lng'] = 0;
        }
        return $data;
    }
    /**
     *@FUNCNAME:wechat_connect
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月19日;
     *@EFFORT:连接微信;
     **/
     function wechat_connect($prefix = ''){
         $options = array(
            'token' => C($prefix.'TOKEN'), // 填写你设定的key
    		'appid' => C($prefix.'APPID'),
    		'appsecret' => C($prefix.'APPSECRET'),
         );
         $connect = new Wechat($options);
         return $connect;
     }
     /**
      *@FUNCNAME:check_url;
      *@AUTHOR:dudongjiang;
      *@DATE:2016年9月19日;
      *@EFFORT:检查变量是否匹配一个url;
      **/
      
     function check_url($url) {
         if (!preg_match('/http:\/\/[\w.]+[\w\/]*[\w.]*\??[\w=&\+\%]*/is', $url)) {
             return false;
         }
         return true;
     }
     /**
      *@FUNCNAME:writelog;
      *@AUTHOR:dudongjiang;
      *@DATE:2016年9月21日;
      *@EFFORT:打印日志;
      **/
     function writelog($header,$content=""){
         $string = "";
         if(empty($content)){
             return FALSE;
         }else if(is_array($content)){
             foreach ($content as $key=>$val){
                 if(is_array($val))
                 {
                     foreach ($val as $k=>$v){
                         $string .= $k."=>".$v."<br/>";
                     }
                 }
                 else
                 {
                     $string .= $key."=>".$val."<br/>";
                 }
             }
         }
         if(is_array($content))
             $content = $string;
         $dir=getcwd().DIRECTORY_SEPARATOR.'logs'.DIRECTORY_SEPARATOR;
         if(!is_dir($dir)){
             if(!mkdir($dir)){
                 return false;
             }
         }
         $filename=$dir.DIRECTORY_SEPARATOR.date("Ymd",time()).'.log.php';
         $logs=include $filename;
         if($logs && !is_array($logs)){
             unlink($filename);
             return false;
         }
         $logs[]=array("time"=>date("Y-m-d H:i:s"),"content"=>$header.$content);
         $str="<?php \r\n return ".var_export($logs, true).";";
         if(!$fp=@fopen($filename,"wb")){
             return false;
         }
         if(!fwrite($fp, $str))return false;
         fclose($fp);
         return true;
     }
     /**
      *@FUNCNAME:check_verify;
      *@AUTHOR:dudongjiang;
      *@DATE:2016年9月22日;
      *@EFFORT:验证验证码是否正确;
      **/
     function check_verify($code, $id = ""){
         $verify = new \Think\Verify();
         return $verify->check($code, $id);
     }
    /**
     * 获取当前页面完整URL地址
     */
    function get_url() {
        $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
        $php_self = $_SERVER['PHP_SELF'] ? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : '';
        $relate_url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : $php_self . (isset($_SERVER['QUERY_STRING']) ? '?' . $_SERVER['QUERY_STRING'] : $path_info);
        return $sys_protocal . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '') . $relate_url;
    }

    /*
     * 获取访问者的IP
     [ip] => 124.65.120.206
     [beginip] => 124.65.111.0
     [endip] => 124.65.121.137
     [country] => 北京市
     [area] => 联通ADSL
     * */
    function getIp() {
        $ips = get_client_ip();
        if ($ips == '127.0.0.1') {
            return "";
        }
        $ip = new \Org\Net\IpLocation('UTFWry.dat');
        $area = $ip -> getlocation($ips);
        // 获取某个IP地址所在的位置
        return $area;
    }

    /*
     * 微信用户访问地理位置信息
     * */
    function mobileHis($uid, $wifimac, $mac) {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        $arrIP = getIp();
        $mobile_mod = M('mobile_history');
        if ($arrIP) {
            $ip = $mobile_mod -> cache(true, 300) -> field('ip,addtime') -> where("uid=" . $uid) -> find();
            $data = array();
            $data['uid'] = $uid;
            $data['ip'] = $arrIP['ip'];
            $data['beginip'] = $arrIP['beginip'];
            $data['endip'] = $arrIP['endip'];
            $data['country'] = $arrIP['country'];
            $data['area'] = $arrIP['area'];
            $data['wifimac'] = $wifimac;
            $data['mac'] = $mac;
            $data['useragent'] = $agent;
            $data['addtime'] = time();
            if (!$ip) {
                $mobile_mod -> add($data);
            } else {
                if ((time() - $ip['addtime']) > 3600) {
                    $mobile_mod -> where("uid=" . $uid) -> save($data);
                }
            }
        }
    }
    /**
     * @param string $table_name
     * @param array $data
     * @param string $field
     * @return bool
     * 批量更新数据
     */
    function batch_update($table_name='',$data=array(),$field=''){
        if(!$table_name||!$data||!$field){
            return false;
        }else{
            $sql='UPDATE '.$table_name;
        }
        $con=array();
        $con_sql=array();
        $fields=array();
        foreach ($data as $key => $value) {
            $x=0;
            foreach ($value as $k => $v) {
                if($k!=$field&&!$con[$x]&&$x==0){$con[$x]=" set {$k} = (CASE {$field} ";}
                elseif($k!=$field&&!$con[$x]&&$x>0){$con[$x]=" {$k} = (CASE {$field} ";}
                if($k!=$field){
                    $temp=$value[$field];
                    $con_sql[$x].= " WHEN '{$temp}' THEN '{$v}' ";
                    $x++;
                }
            }
            $temp=$value[$field];
            if(!in_array($temp,$fields)){$fields[]=$temp;}
        }
        $num=count($con)-1;
        foreach ($con as $key => $value) {
            foreach ($con_sql as $k => $v) {
                if($k==$key&&$key<$num){$sql.=$value.$v.' end),';}
                elseif($k==$key&&$key==$num){$sql.=$value.$v.' end)';}
            }
        }
        $str=implode(',',$fields);
        $sql.=" where {$field} in({$str})";
        $res = M()->execute($sql);
        return $res;
    }

    /**
     * @return AopClient
     * 支付宝支付公共参数
     */
    function alipayPay(){
        vendor('Alipay.aop.AopClient');
        vendor('Alipay.aop.SignData');
        $alipay = new \AopClient();
        $alipay->gatewayUrl = 'https://openapi.alipay.com/gateway.do';
        $alipay->appId = C('ALIPAYAPPID');
        $alipay->rsaPrivateKey = file_get_contents(VENDOR_PATH.'Alipay/key/rsa_private_key.pem');
        $alipay->alipayrsaPublicKey = file_get_contents(VENDOR_PATH.'Alipay/key/alipay_public_key.pem');
        $alipay->apiVersion = '1.0';
        $alipay->signType = 'RSA2';
        $alipay->postCharset = 'utf-8';
        $alipay->format = 'json';
        return $alipay;
    }

    /**
     * @param $code
     * @return bool|mixed
     * 返回支付宝的错误信息
     */
    function alipayErrorInfo($code)
    {
        $arr = [
            'INVALID_PARAMETER' => '参数有误。',
            'SYSTEM_ERROR' => '系统繁忙',
            'PERMIT_CHECK_PERM_LIMITED' => '根据监管部门的要求，请补全您的身份信息解除限制',
            'PAYCARD_UNABLE_PAYMENT' => '付款账户余额支付功能不可用',
            'PAYEE_NOT_EXIST' => '收款账号不存在',
            'PAYER_DATA_INCOMPLETE' => '根据监管部门的要求，需要付款用户补充身份信息才能继续操作',
            'PERM_AML_NOT_REALNAME_REV' => '根据监管部门的要求，需要收款用户补充身份信息才能继续操作',
            'PAYER_STATUS_ERROR' => '付款账号状态异常',
            'PAYEE_USER_INFO_ERROR' => '支付宝账号和姓名不匹配，请确认姓名是否正确',
            'PAYER_BALANCE_NOT_ENOUGH' => '付款方余额不足',
            'PAYMENT_INFO_INCONSISTENCY' => '两次请求商户单号一样，但是参数不一致',
            'CERT_MISS_TRANS_LIMIT' => '您的付款金额已达单笔1万元或月累计5万元，根据监管部门的要求，需要付款用户补充身份信息才能继续操作',
            'CERT_MISS_ACC_LIMIT' => '您连续10天余额账户的资金都超过5000元，根据监管部门的要求，需要付款用户补充身份信息才能继续操作	',
            'PAYEE_ACC_OCUPIED' => '该手机号对应多个支付宝账户，请传入收款方姓名确定正确的收款账号',
            'MEMO_REQUIRED_IN_TRANSFER_ERROR' => '根据监管部门的要求，单笔转账金额达到50000元时，需要填写付款理由',
            'PERMIT_NON_BANK_LIMIT_PAYEE' => '根据监管部门的要求，对方未完善身份信息或未开立余额账户，无法收款',
            'PERMIT_PAYER_LOWEST_FORBIDDEN' => '根据监管部门要求，付款方身份信息完整程度较低，余额支付额度受限',
            'PERMIT_PAYER_FORBIDDEN' => '根据监管部门要求，付款方余额支付额度受限',
            'PERMIT_CHECK_PERM_IDENTITY_THEFT' => '您的账户存在身份冒用风险，请进行身份核实解除限制',
            'REMARK_HAS_SENSITIVE_WORD' => '转账备注包含敏感词，请修改备注文案后重试',
            'ACCOUNT_NOT_EXIST' => '根据监管部门的要求，请补全你的身份信息，开立余额账户',
            'PAYER_CERT_EXPIRED' => '根据监管部门的要求，需要付款用户更新身份信息才能继续操作',
            'EXCEED_LIMIT_PERSONAL_SM_AMOUNT' => '转账给个人支付宝账户单笔最多5万元',
            'EXCEED_LIMIT_ENT_SM_AMOUNT' => '转账给企业支付宝账户单笔最多10万元',
            'EXCEED_LIMIT_SM_MIN_AMOUNT' => '单笔最低转账金额0.1元',
            'EXCEED_LIMIT_DM_MAX_AMOUNT' => '单日最多可转100万元',
            'EXCEED_LIMIT_UNRN_DM_AMOUNT' => '收款账户未实名，单日最多可收款1000元	',
            'isp.unknow-error' => '服务暂不可用（业务系统不可用）',
            'aop.unknow-error' => '服务暂不可用（网关自身的未知错误）',
            'aop.invalid-auth-token' => '无效的访问令牌',
            'aop.auth-token-time-out' => '访问令牌已过期',
            'aop.invalid-app-auth-token' => '无效的应用授权令牌',
            'aop.invalid-app-auth-token-no-api' => '商户未授权当前接口',
            'aop.app-auth-token-time-out' => '应用授权令牌已过期',
            'aop.no-product-reg-by-partner' => '商户未签约任何产品',
            'isv.missing-method' => '缺少方法名参数',
            'isv.missing-signature' => '缺少签名参数',
            'isv.missing-signature-type' => '缺少签名类型参数',
            'isv.missing-signature-key' => '缺少签名配置',
            'isv.missing-app-id' => '缺少appId参数',
            'isv.missing-timestamp' => '缺少时间戳参数',
            'isv.missing-version' => '缺少版本参数',
            'isv.decryption-error-missing-encrypt-type' => '解密出错, 未指定加密算法',
            'isv.invalid-parameter' => '参数无效',
            'isv.upload-fail' => '文件上传失败',
            'isv.invalid-file-extension' => '文件扩展名无效',
            'isv.invalid-file-size' => '文件大小无效',
            'isv.invalid-method' => '不存在的方法名',
            'isv.invalid-format' => '无效的数据格式',
            'isv.invalid-signature-type' => '无效的签名类型',
            'isv.invalid-signature' => '无效签名',
            'isv.invalid-encrypt-type' => '无效的加密类型',
            'isv.invalid-encrypt' => '解密异常',
            'isv.invalid-app-id' => '无效的appId参数',
            'isv.invalid-timestamp' => '非法的时间戳参数',
            'isv.invalid-charset' => '字符集错误',
            'isv.missing-signature-config' => '验签出错, 未配置对应签名算法的公钥或者证书',
            'isv.insufficient-isv-permissions' => 'ISV权限不足',
            'isv.insufficient-user-permissions' => '用户权限不足'
        ];
        if (isset($arr[$code]) && $arr[$code]) {
            return $arr[$code];
        } else {
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
    function http_post($url,$param,$useCert = false){
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
        curl_setopt($oCurl, CURLOPT_URL, C("SINOPEC_BASEURL").$url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt($oCurl, CURLOPT_POST,true);
        curl_setopt($oCurl, CURLOPT_POSTFIELDS,$strPOST);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);
        curl_close($oCurl);
        if(intval($aStatus["http_code"])==200){
            return $sContent;
        }else{
            return false;
        }
    }

    function getHex($num)
    {
        $msg = (string)dechex($num);//把十进制转换为十六进制
        if (mb_strlen($msg) == 1) {
            $msg = "0" . $msg;
        }
        return $msg;
    }

    /**
     * @param string $src
     * @return string
     * 加密算法
     */
    function php_en($src = '')
    {
    // $dest = '';
        $key = 'ADDBYHGFFOVER';//ADDBYHGFFOVER
        $keyPos = -1;
        $srcLen = mb_strlen($src);
        $keyLen = mb_strlen($key);
        if ($keyLen == 0) {
            $key = 'xj2006';
        }
        $range = 255;
        $num = rand(0, $range);
        $dest = getHex($num);
        //十进制=>十六进制
        if ($src == null) {
            $src = '';
        }
        for ($i = 0; $i < $srcLen; $i++) {
            //ascii码值+随机数再求余
            $SrcAsc = (ord($src[ $i ]) + $num) % 255;
            if ($keyPos < $keyLen - 1) {
                $keyPos = $keyPos + 1;
            } else {
                $keyPos = 0;
            }
            $ss = ord($key[ $keyPos ]);
            $SrcAsc = $SrcAsc ^ $ss;
            $dest .= getHex($SrcAsc);
            $num = $SrcAsc;
        }
        return trim($dest);
    }

    /**
     * @param string $src
     * @return null|string
     * 解密算法
     */
    function php_de($src = '')
    {
        $key = 'ADDBYHGFFOVER';
        $keyPos = -1;
        $result = "";
        $srcLen = mb_strlen($src);
        $keyLen = mb_strlen($key);
        $TmpSrcAsc = "";
        if($srcLen == 2){
            return null;
        }
        if($keyLen == 0 ){
            $key="xj2006";
        }
        try {
            $offset = intval(hexdec(mb_substr($src, 0, 2)));
        } catch (Exception $e) {
            $offset = 0;
        }
        $srcPos = 3;
        do{
            try {
                $s=mb_substr($src, $srcPos-1, 2);
                $srcAsc = intval(hexdec($s));
            } catch (Exception $e) {
                $srcAsc=0;
            }
            if($keyPos < $keyLen-1){
                $keyPos = $keyPos + 1;
            }else{
                $keyPos=0;
            }
            $ss = ord(mb_substr($key, $keyPos, 1));
            $TmpSrcAsc = $srcAsc^$ss; //求异或
            if($TmpSrcAsc <= $offset){
                $TmpSrcAsc = 255 + $TmpSrcAsc - $offset;
            }else{
                $TmpSrcAsc = $TmpSrcAsc - $offset;
            }
            $d = chr(intval($TmpSrcAsc));
            $result.=$d;
            $offset = $srcAsc;
            $srcPos = $srcPos + 2;
        }while(!($srcPos >= $srcLen));
        return $result;
    }
    /**
     * @param $param
     * @return mixed
     * 根据编号返回字段
     */
    function serviceType($param){
        $types = [
            92 => 'shjyqtotal',
            93 => 'shczktotal',
            94 => 'fwktotal',
            95 => 'jfktotal'
        ];
        if(empty($param)){
            return 'fwktotal';
        }
        return $types[$param];
    }
    /**
     * @param $url
     * @param int $size
     * 生成二维码
     */
    function qrcode($content,$size=4){
        Vendor('phpqrcode.phpqrcode');
        // 如果没有http 则添加
        QRcode::png($content,false,QR_ECLEVEL_L,$size,2,false,0xFFFFFF,0x000000);
    }
    /**
     * @throws Exception
     * 生成条形码
     */
    function barcode($content){
        Vendor('barcodegen.class.BCGFont');//字体类
        Vendor('barcodegen.class.BCGDrawing');
        Vendor('barcodegen.class.BCGColor');//字体颜色类
        Vendor('barcodegen.class.BCGCode128');
        $content = isset($content)?$content:'00000000000';
        $font = new \BCGFont('./font/Arial.ttf', 12);
        $color_black = new \BCGColor(0,0,0);
        $color_white = new \BCGColor(255,255,255);
        $drawException = null;
        try {
            $code = new \BCGCode128();
            $code->setScale(2);
            $code->setThickness(30);
            $code->setForegroundColor($color_black);
            $code->setBackgroundColor($color_white);
            $code->parse($content);
        } catch(Exception $exception) {
            $drawException = $exception;
        }
        $drawing = new \BCGDrawing('', $color_white);
        if($drawException) {
            throw new Exception($drawException);
        } else {
            $drawing->setBarcode($code);
            $drawing->draw();
        }
        header('Content-Type: image/png');
        header('Content-Disposition: inline; filename="barcode.png"');
        $drawing->finish(\BCGDrawing::IMG_FORMAT_PNG);
    }
