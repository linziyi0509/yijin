<?php
/**
 * 发送模板短信
 * @param $mobile 手机号 支持数组
 * @param $data 短信内容
 * @param $target 短信接口
 */
function SendMessage($mobile,$data, $target="http://sms.chanzor.com:8001/sms.aspx") {
	if(is_array($mobile)){
		$mobile = implode(',', $mobile);
	}
	$post_data = "action=send&userid=&account=ldtxwangluo&password=152336&mobile=".$mobile."&sendTime=&content=".rawurlencode($data);
    $url_info = parse_url($target);
    $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
    $httpheader .= "Host:" . $url_info['host'] . "\r\n";
    $httpheader .= "Content-Type:application/x-www-form-urlencoded\r\n";
    $httpheader .= "Content-Length:" . strlen($post_data) . "\r\n";
    $httpheader .= "Connection:close\r\n\r\n";
    //$httpheader .= "Connection:Keep-Alive\r\n\r\n";
    $httpheader .= $post_data;

    $fd = fsockopen($url_info['host'], 80);
    fwrite($fd, $httpheader);
    $gets = "";
    while(!feof($fd)) {
        $gets .= fread($fd, 128);
    }
    fclose($fd);
    return $gets;
}

/**
* 根据经纬度计算实际距离
* @param float $lat1 纬度1
* @param float $lng1 经度1
* @param float $lat2 纬度2
* @param float $lng2 经度2
* @return int 米
*/
function getMeter1($lat1,$lng1,$lat2,$lng2){
    if(!$lat1 || !$lng1 || !$lat2 || !$lng2){
    	return 0;
    }
    $PI 		= 3.14159265/180;
    $earthR		= 6378137;
    $radlat1	= $lat1*$PI;
    $radlng1 	= $lng1*$PI;
    $radlat2 	= $lat2*$PI;
    $radlng2 	= $lng2*$PI;
    $a 			= $radlat1-$radlat2;
    $b			= $radlng1-$radlng2;
    $s 			= 2*asin(sqrt(pow(sin($a/2),2) + cos($radlat1)*cos($radlat2)*pow(sin($b/2),2)));
    $s 			= round($s*$earthR);
	return $s;
}

/**
 * 距离单位自动转换
 * @param int $num
 */
function numtometer1($num=0){
	if($num<=0){
		return '1m';
	}
	return $num > 30000 ? '>30km' : ($num > 1000 ? round($num/1000,2).'km' : $num.'m');
}

/**
 * 个性化日期
 * @param int $ustime
 */
function transTime($ustime){
	$rtime = date('n月j日 H:i',$ustime);
	$htime = date('H:i',$ustime);
	$time  = time()-$ustime;
	$todaytime = strtotime('today');
	$time1 = time()-$todaytime;
	if($time < 60){
		$str = '刚刚';
	}elseif ($time < 60*60){
		$min = floor($time/60);
		$str = $min.'分钟前';
	}elseif ($time < $time1){
		$str = '今天'.$htime;
	}else {
		$str = $rtime;
	}
	return $str;
}
?>	
