<?php
namespace Api\Controller;
use Think\Controller;
class GrabController extends BaseController{
	//工人抢单
	public function  grab_order(){
		$user_id = I("user_id") ? intval(I("user_id"))  : '';
		$order_id = I("order_id") ? intval(I("order_id"))  : '';
		if(!$user_id || !$order_id){
			$this->Rerror("缺少参数");
		}
		$order_info = M("order")->where("userid='".$user_id."' and status=97 and sendxqid='".$order_id."'")->find();
		if($order_info){
			$this->Rerror("您已经抢过单了");
		}
		$send_info = M("sendxq")->field("num,in_num")->where("id='".$order_id."'")->find();
		if($send_info["num"] - $send_info["in_num"] <=0){
			$data["states"] = 85;
                        M("sendxq")->where("id='".$order_id."'")->save($data);
			$this->Rerror("需求人数已达上限");
		}
		$new_number["in_num"] = $send_info["in_num"] + 1;
		M("sendxq")->where("id='".$order_id."'")->save($new_number);
		$result_info = M("user")->field("nickname")->where("id='".$user_id."'")->find();
		$data["userid"]   = $user_id;
		$data["sendxqid"] = $order_id; 
		$data["nickname"] = $result_info["nickname"];
		$data["ddh"] = 'xmj'.date("YmdHis").rand(100,999);
		$data["createtime"] = date("Y-m-d H:i:s");
		$data["updatetime"] = date("Y-m-d H:i:s");
		$info = M("order")->add($data);
		$data_user = M("sendxq")->field("userid")->where("id='".$data["sendxqid"]."'")->find();
		$user_info = M("user")->field("jpushcode")->where("id='".$data_user["userid"]."'")->find();
		$data1 = M("user")->field("mobile,id")->where("id='".$data["userid"]."'")->find();
        $title='小木匠提示您';
        $content="工人".$result_info["nickname"]."抢您的单了";
        $jpushcode=$user_info["jpushcode"];
        $type=1;
        vendor('Jpush/jpush#class');
        $jpush  = new \jpush();
        $result = $jpush->sendmessage($title,$content,$jpushcode,array('type'=>$type,'orderid'=>$order_id));
        $user_data["userid"]       = $data1['id'];
        $user_data["sendxqid"]     = $data["sendxqid"];
        $user_data["news_phone"]   = $data1['mobile'];
        $user_data["news_content"] = $content;
        $user_data["news_type"]    = 110;
        $user_data["user_type"]    = 79;
        $user_data["createtime"]   = date("Y-m-d H:i:s");
        $user_data["updatetime"]   = date("Y-m-d H:i:s");
        M("news")->add($user_data);
		if(!$info){
			$this->Rerror("抢单失败");
		}
		$this->Rsuccess("抢单成功");
	}
	//确认招用
	public function confirm_recruit(){
		$user_id      = I("user_id") ? intval(I("user_id"))  : ''; //登录的用户id
		$order_id     = I("order_id") ? intval(I("order_id"))  : ''; //发布需求的id
		if(!$user_id || !$order_id){
			$this->Rerror("缺少参数");
		}
		$data["status"] = 90;
		M("order")->where("sendxqid='".$order_id."'")->save($data);
		$send_info = M("sendxq")->field("num,in_num")->where("id='".$order_id."'")->find();
		if($send_info["num"] == $send_info["in_num"]){
			$data1['states']   = 85;
			M("sendxq")->where("id='".$order_id."'")->save($data1);
		}
		$data_info = M("order")->field("userid")->where("sendxqid='".$order_id."'")->select();
		foreach ($data_info as $key => $value) {
			$new[] = $value["userid"];
		}
		$new_array = implode(",",$new);
		$user_info = M("user")->field("jpushcode")->where("id in($new_array)")->select();
		$data2 = M("user")->field("id,mobile")->where("id in($new_array)")->select();
		$result1 = M("user")->field("nickname")->where("id='".$user_id."'")->find();
		foreach ($user_info as $k=>$v){
			$title='小木匠提示您';
			$content="雇主".$result1["nickname"]."招用您了";
			$jpushcode=$v["jpushcode"];
			$type=2;
			vendor('Jpush/jpush#class');
			$jpush  = new \jpush();
			$result = $jpush->sendmessage($title,$content,$jpushcode,array('type'=>$type,'orderid'=>$order_id));
		}
        $beansAll = array();
        for($i=0;$i<count($data2);$i++){
        	$beansAll[$i]["userid"]       = $data2[$i]['id'];
			$beansAll[$i]["news_phone"]   = $data2[$i]['mobile'];
			$beansAll[$i]["news_content"] = $content;
			$beansAll[$i]["sendxqid"]     = $order_id;
			$beansAll[$i]["news_type"]    = 111;
			$beansAll[$i]["user_type"]    = 78;
			$beansAll[$i]["createtime"]   = date("Y-m-d H:i:s");
			$beansAll[$i]["updatetime"]   = date("Y-m-d H:i:s");;
        }
    	M("news")->addAll($beansAll);
		$this->Rsuccess("招用成功");
	}
	//工人端抢单列表 | 施工中
	public function grab_list_work(){
		$user_id = I("user_id") ? intval(I("user_id"))  : '';
		$pageindex = I("pageindex") ? intval(I("pageindex"))  : '';
		if(!$user_id || !$pageindex){
			$this->Rerror("缺少参数");
		}
		$pagesize = 10;
        $offset = ($pageindex-1)*$pagesize;  //偏移量
		$info = M("order")->alias("a")->join("my_sendxq s on a.sendxqid = s.id")->limit($offset,$pagesize)->where("a.userid='".$user_id."' and status=90")->select();
		if(!$info){
			$this->Rsuccess("暂时没有数据");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//工人端抢单列表 | 已完成
	public function grab_list_finish(){
		$user_id = I("user_id") ? intval(I("user_id"))  : '';
		$pageindex = I("pageindex") ? intval(I("pageindex"))  : '';
		if(!$user_id || !$pageindex){
			$this->Rerror("缺少参数");
		}
		$pagesize = 10;
        $offset = ($pageindex-1)*$pagesize;  //偏移量
		$info = M("order")->alias("a")->join("my_sendxq s on a.sendxqid = s.id")->limit($offset,$pagesize)->where("a.userid='".$user_id."' and status=98")->select();
		if(!$info){
			$this->Rsuccess("暂时没有数据");
		}
		$this->Rsuccess("查询成功",$info);
		
	}
	//工人端的抢单列表状态的数量
	public function work_count(){
		$user_id = I("user_id") ? intval(I("user_id"))  : '';
		if(!$user_id){
			$this->Rerror("缺少参数");
		}
		$work = M("order")->alias("a")->join("my_sendxq s on a.sendxqid = s.id")->where("a.userid='".$user_id."' and status=90")->count();
		$finish =  M("order")->alias("a")->join("my_sendxq s on a.sendxqid = s.id")->where("a.userid='".$user_id."' and status=98")->count();
		if(!$work && !$finish){
			$arr = array("code"=>"1000","work"=>0,"message"=>"查询成功","finish"=>0);
			exit(json_encode($arr));
		}else if($work && !$finish){
			$arr = array("code"=>"1000","work"=>$work,"message"=>"查询成功","finish"=>0);
			exit(json_encode($arr));
		}else if(!$work && $finish){
			$arr = array("code"=>"1000","work"=>0,"message"=>"查询成功","finish"=>$finish);
			exit(json_encode($arr));
		}
		$arr = array("code"=>"1000","work"=>$work,"message"=>"查询成功","finish"=>$finish);
		exit(json_encode($arr));
	}
	//工人端抢单列表
	public function  grab_list(){
		$user_id = I('user_id') ? intval(I('user_id')) : '';
		$pageindex = I('pageindex') ? intval(I('pageindex')) : '';
		$order_id = I('order_id') ? intval(I('order_id')) : '';
		if(!$user_id || !$pageindex || !$order_id){
			$this->Rerror("缺少参数");
		}
		$pagesize = 10;
        $offset = ($pageindex-1)*$pagesize;  //偏移量
		$info = M("order")->alias("a")->join("my_sendxq s on a.sendxqid = s.id")->limit($offset,$pagesize)->where("s.userid='".$user_id."' and a.id='".$order_id."'")->find();
		$user_info = M("order")->alias("a")->field("u.*")->join("my_user u on a.userid=u.id")->where("a.sendxqid='".$order_id."'")->select();
		if(!$info && !$user_info){
			$arr = array("code"=>"1000","data"=>array(),"user_info"=>array());
        	exit(json_encode($arr));
		}else{
			$arr = array("code"=>"1000","data"=>$info,"user_info"=>$user_info);
        	exit(json_encode($arr));
		}

	}
	//发布需求
	public function send_demand(){
		$user_id = I('user_id') ? intval(I('user_id')) : ''; //用户id
		$type = I('type') ? intval(I('type')) : '';			 //工种
		$address = I('address') ? trim(I('address')) : '';	 //地址
		$content = I('content') ? trim(I('content')) : '';	 //工作内容
		$number = I('number') ? intval(I('number')) : '';	 //需求人数
		$startdate = I('startdate') ? trim(I('startdate')) : '';	 //开始时间
		$enddate = I('enddate') ? trim(I('enddate')) : '';	 //结束时间
		$money = I('money') ? trim(I('money')) : '';	 //价格区间
		$is_safe = I('is_safe') ? intval(I('is_safe')) : '';	 //是否需要保险
		$safe_money = I('safe_money') ? trim(I('safe_money')) : '';	 //保险额度
		$name = I('name') ? trim(I('name')) : '';	 //用户名
		$phone = I('phone') ? trim(I('phone')) : '';	 //手机号
		$descript = I('descript') ? trim(I('descript')) : '';	 //描述
		$lng = I('lng') ? trim(I('lng')) : '';	 //精度
		$lat = I('lat') ? trim(I('lat')) : '';	 //维度
		$total_money = I('total_money') ? trim(I('total_money')) : '';	 //总金额
		if(!$user_id || !$type || !$address || !$content || !$number || !$startdate || !$enddate || !$money || !$is_safe || !$name || !$phone || !$total_money){
			$this->Rerror("缺少参数");
		}
		$user_info = M("user")->field("mistake_number")->where("id='".$user_id."'")->find();
		if($user_info["mistake_number"]==3){
			$this->Rerror("您恶意下单次数超过三次，不能再下单了");
		}
		$data["createtime"] = date("Y-m-d H:i:s");
		$data["updatetime"] = date("Y-m-d H:i:s");
		$data["userid"]     = $user_id;
		$data["gztypeid"]   = $type;
		$data["address"]    = $address;
		$data["txt"]        = $content;
		$data["num"]        = $number;
		$data["startDate"]  = $startdate;
		$data["endDate"]    = $enddate;
		$data["money"]      = $money;
		$data["isbx"]       = $is_safe;
		$data["bf"]         = $safe_money;
		$data["name"]       = $name;
		$data["tel"]        = $phone;
		$data["txt1"]       = $descript;
		$data["ddh"]	    = "xmj".date("YmdHis").rand(100,999);
		$data["lng"]	    = $lng;
		$data["lat"]	    = $lat;
		$info = M("sendxq")->add($data);
		$user_info = M("user")->field("recharge_money")->where("id='".$data["userid"]."'")->find();
		if($total_money > $user_info["recharge_money"]){
			$this->Rerror("账户余额不足");
		}
		$new["recharge_money"] = $user_info["recharge_money"] - $total_money;
		$uinfo = M("user")->where("id='".$data["userid"]."'")->save($new);
		if(!$info && !$uinfo){
			$this->Rerror("发布需求失败");
		}
		$this->Rsuccess("发布需求成功");
	}
	//雇主下单的列表
	public function work_list(){
		$pageindex = I("pageindex") ? intval(I("pageindex")) : '';
		if(!$pageindex){
			$this->Rerror("参数不可为空");
		}
		$pagesize = 10;
		$offset = ($pageindex-1)*$pagesize;  //偏移量
		$info = M("sendxq")->alias("s")->field("s.*,picture")->join("my_user u on s.userid=u.id")->order("s.createtime desc")->limit($offset,$pagesize)->where("s.states=84")->select();
		if(!$info){
			$this->Rsuccess("暂时没有数据");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//雇主端 | 确认验收的验证
	public  function check(){
		$send_id = I("send_id") ? I("send_id") : '';
		if(!$send_id){
			$this->Rerror("缺少参数");
		}
		$info = M("order")->where("sendxqid='".$send_id."'")->select();
		foreach ($info as $key => $value) {
			if($value["status"]==98){
				$this->Rsuccess("雇主可以验收");
			}else{
				$this->Rerror("雇主不可以验收");
			}
		}
		if(!$info){
			$this->Rerror("雇主不可以验收");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//雇主端 | 确认验收
	public function confirm_check(){
		$user_id =  I("user_id") ? intval(I("user_id")) : '';
		$order_id = I("order_id") ? intval(I("order_id")) : '';
		if(!$user_id || !$order_id){
			$this->Rerror("缺少参数");
		}
		$data["states"] = 86;
		$info = M("sendxq")->where("id='".$order_id."'")->save($data);
		$user = M("user")->field("nickname")->where("id='".$user_id."'")->find();
		$order_info = M("order")->field("a.userid")->alias("a")->join("my_sendxq m on a.sendxqid=m.id")->where("a.sendxqid='".$order_id."'")->select();
		foreach($order_info as $key=>$val){
			$new_user[] = $val["userid"];
		}
		$new_array = implode($new_user,',');
		$user_info = M("user")->field("jpushcode")->where("id in($new_array)")->select();
		$data1 = M("user")->field("id,mobile")->where("id in($new_array)")->select();
		foreach ($user_info as $key => $value) {
			$title='小木匠提示您';
			$content="雇主".$user["nickname"]."确认验收了";
			$jpushcode=$value["jpushcode"];
			$type=3;
			vendor('Jpush/jpush#class');
			$jpush  = new \jpush();
			$result = $jpush->sendmessage($title,$content,$jpushcode,array('type'=>$type,'orderid'=>$order_id));
		}
        $beansAll = array();
        for($i=0;$i<count($data1);$i++){
        	$beansAll[$i]["userid"]       = $data1[$i]['id'];
			$beansAll[$i]["news_phone"]   = $data1[$i]['mobile'];
			$beansAll[$i]["news_content"] = $content;
			$beansAll[$i]["sendxqid"]     = $order_id;
			$beansAll[$i]["news_type"]    = 112;
			$beansAll[$i]["user_type"]    = 78;
			$beansAll[$i]["createtime"]   = date("Y-m-d H:i:s");
			$beansAll[$i]["updatetime"]   = date("Y-m-d H:i:s");;
        }
        M("news")->addAll($beansAll);
		if(!$info){
			$this->Rerror("确认验收失败");
		}
		$this->Rsuccess("确认验收成功");
	}
	//工人端　｜　工人竣工
	public function worker_finish(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$order_id = I("order_id") ? intval(I("order_id")) : '';
		if(!$user_id || !$order_id){
			$this->Rerror("缺少参数");
		}
		$data["status"] = 98;
		$info = M("order")->where("sendxqid='".$order_id."'")->save($data);
		$send_info = M("order")->field("m.userid")->alias("a")->join("my_sendxq on m a.sendxqid=m.id")->where("a.sendxqid='".$order_id."'")->find();
		$user_info = M("user")->field("jpushcode")->where("id='".$send_info["userid"]."'")->find();
		$data1 = M("user")->field("id,nickname,mobile")->where("id='".$user_id."'")->find();
		$title='小木匠提示您';
        $content="工人中".$data1["nickname"]."完工了";
        $jpushcode=$user_info["jpushcode"];
        $type=5;
        vendor('Jpush/jpush#class');
        $jpush  = new \jpush();
        $result = $jpush->sendmessage($title,$content,$jpushcode,array('type'=>$type,'orderid'=>$order_id));
        $user_data["userid"]     = $data1['id'];
        $user_data["news_phone"] = $data1["mobile"];
        $user_data["sendxqid"]   = $order_id;
        $user_data["news_type"]  = 113;
        $user_data["user_type"]  = 79;
        $user_data["news_content"] = $content;
        $user_data["createtime"] = date("Y-m-d H:i:s");
        $user_data["updatetime"] = date("Y-m-d H:i:s");
        M("news")->add($user_data);
		if(!$info){
			$this->Rerror("工人竣工失败");
		}
		$this->Rsuccess("工人竣工成功");
	}
	//工人端 | 取消订单
	public function cancel_order(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$order_id = I("order_id") ? intval(I("order_id")) : '';
		if(!$user_id || !$order_id){
			$this->Rsuccess("缺少参数");
		}
		$send_number = M("sendxq")->field("in_num")->where("id='".$order_id."'")->find();
		$data["in_num"] = $send_number["in_num"] - 1;
		$send_number_info = M("sendxq")->where("id='".$order_id."'")->save($data);
		$info = M("order")->where("userid='".$user_id."' and sendxqid='".$order_id."'")->delete();
		if(!$info){
			$this->Rerror("取消订单失败");
		}
		$this->Rsuccess("取消订单成功");
	}
	//工人端 | 订单的评价
	public function order_appraise(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$nickname = I("nickname") ? trim(I("nickname")) : '';
		$degree = I("degree") ? trim(I("degree")) : '';
		$order_number = I("order_number") ? trim(I("order_number")) : '';
		$send_id = I("send_id") ? intval(I("send_id")) : '';
		$content = I("content") ? trim(I("content")) : '';
		$type = I("type") ? intval(I("type")) : '';
		if(!$user_id || !$send_id || !$nickname || !$order_number || !$content || !$type){
			$this->Rerror("缺少参数");
		}
		$data["userid"] = $user_id;
		$data["nickname"] = $nickname;
		$data["degree"]   = $degree;
		$data["order_number"] = $order_number;
		$data["content"] = $content;
		$data["status"]   = $type;
		$data["sendxqid"]  = $send_id;
		$data["createtime"] = date("Y-m-d H:i:s");
		$data["updatetime"] = date("Y-m-d H:i:s");
		$info = M("pingjia")->add($data);
		if(!$info){
			$this->Rerror("评价失败");
		}
		$this->Rsuccess("评价成功");
	}
	//雇主端 | 辞退工人
	public function cancel_work(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$order_id = I("order_id") ? intval(I("order_id")) : '';
		$content1 = I("content") ? trim(I("content")) : '';
		if(!$user_id || !$order_id || !$content1){
			$this->Rerror("缺少参数");
		}
		$data1['status'] = 89;
		$data1["content"] = $content1;
		$user_data = M("order")->where("userid='".$user_id."'")->save($data1);
		$info = M("sendxq")->field("in_num,userid")->where("id='".$order_id."'")->find();
		$data['in_num'] = $info['in_num'] -1;
		$new_info = M("sendxq")->where("id='".$order_id."'")->save($data);
		$user = M("user")->field("nickname")->where("id='".$info["userid"]."'")->find();
		$user_info = M("user")->field("jpushcode")->where("id='".$user_id."'")->find();
		$data2 = M("user")->field("mobile")->where("id='".$user_id."'")->find();
		$title='小木匠提示您';
        $content="雇主".$user["nickname"]."辞退您了";
        $jpushcode=$user_info["jpushcode"];
        $type=4;
        vendor('Jpush/jpush#class');
        $jpush  = new \jpush();
        $result = $jpush->sendmessage($title,$content,$jpushcode,array('type'=>$type,'orderid'=>$order_id));
        $user_data["createtime"]   = date("Y-m-d H:i:s");
        $user_data["updatetime"]   = date("Y-m-d H:i:s");
        $user_data["userid"]       = $user_id;
        $user_data["sendxqid"]     = $order_id;
        $user_data["news_type"]    = 114;
        $user_data["user_type"]    = 78;
        $user_data["news_phone"]   = $data2["mobile"];
        $user_data["news_content"] = $content;
        M("news")->add($user_data);
		if(!$user_data && !$new_info){
			$this->Rerror("辞退失败");
		}
		$this->Rsuccess("辞退成功");
	}
	//雇主端 | 删除订单
	public function  delete_employee_order(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$send_id = I("send_id") ? intval(I("send_id")) : '';
		if(!$user_id || !$send_id){
			$this->Rerror("缺少参数");
		}
		$info = M("sendxq")->where("id='".$send_id."' and userid='".$user_id."' and states=86")->find();
		if(!$info){
			$this->Rerror("您不是发布的订单的人或者订单没完成");
		}
		$send_info = M("sendxq")->where("id='".$send_id."'")->delete();
		if(!$send_info){
			$this->Rerror("删除订单失败");
		}
		$this->Rsuccess("删除订单成功");
	}
	//工人端 | 删除订单
	public function delete_work_order(){
		$user_id  	= I("user_id") ? intval(I("user_id")) : '';
		$indent_id  = I("indent_id") ? intval(I("indent_id")) : '';
		if(!$user_id || !$indent_id){
			$this->Rerror("缺少参数");
		}
		$info = M("order")->where("userid='".$user_id."' and id='".$indent_id."' and status=98")->find();
		if(!$info){
			$this->Rerror("您没有抢过订单或者订单没完成");
		}
		$order_info = M("order")->where("id='".$indent_id."'")->delete();
		if(!$order_info){
			$this->Rerror("删除订单失败");
		}
		$this->Rsuccess("删除订单成功");
	}
	//雇主端 | 一键评价
	public function user_first_commont(){
		$user_id = I("user_id") ? intval(I("user_id")) : ''; //登录的用户id
		$send_id = I("send_id") ? intval(I("send_id")) : ''; //发布订单的id
		$degree = I("degree") ? intval(I("degree")) : '';		//等级
		$order_number = I("order_number") ? trim(I("order_number")) : ''; //发布的订单号
		$content = I("content") ? trim(I("content")) : '';		//评价的内容
		if(!$user_id || !$send_id || !$degree || !$order_number || !$content){
			$this->Rerror("缺少参数");
		}
		$order_info = M("order")->alias("a")->field("a.userid")->join("my_sendxq s on a.sendxqid=s.id")->where("sendxqid='".$send_id."' and s.states=86")->select();
		if(!$order_info){
			$this->Rsuccess("暂时没有订单");
		}
		foreach($order_info as $key=>$val){
			$new[] = $val["userid"];
		}
		$new_array = implode($new,',');
		$new_info = M("user")->field("id,nickname")->where("id in($new_array)")->select();
		$all = array();
        for($i=0;$i<count($new_info);$i++){
        	$all[$i]["userid"]       = $new_info[$i]['id'];
			$all[$i]["nickname"]     = $new_info[$i]['nickname'];
			$all[$i]["degree"]       = $degree;
			$all[$i]["content"]      = $content;
			$all[$i]["order_number"] = $order_number;
			$all[$i]["sendxqid"]     = $send_id;
			$all[$i]["status"]       = 79;
			$all[$i]["createtime"]   = date("Y-m-d H:i:s");
			$all[$i]["updatetime"]   = date("Y-m-d H:i:s");
        }
        $info = M("pingjia")->addAll($all);
        if(!$info){
        	$this->Rerror("评价失败");
        }
        $this->Rsuccess("评价成功");
	}
	//雇主端 | 单独评价工人
	public function comment_worker(){
		$user_id = I("user_id") ? intval(I("user_id")) : ''; //抢单的用户id
		$send_id = I("send_id") ? intval(I("send_id")) : ''; //发布订单的id
		$degree = I("degree") ? intval(I("degree")) : '';    //等级
		$order_number = I("order_number") ? trim(I("order_number")) : ''; //发布订单的订单号
		$content = I("content") ? trim(I("content")) : '';			//评假的内容
		if (!$user_id || !$send_id || !$degree || !$order_number || !$content) {
			$this->Rerror("缺少参数");
		}
		$info = M("user")->field("nickname")->where("id='".$user_id."'")->find();
		$data["userid"]   		= $user_id;
		$data["nickname"] 		= $info["nickname"];
		$data["degree"]			= $degree;
		$data["sendxqid"]		= $send_id;
		$data["order_number"] 	= $order_number;
		$data["content"]    	= $content;
		$data["status"]			= 79;
		$data["createtime"] 	= date("Y-m-d H:i:s");
		$data["updatetime"] 	= date("Y-m-d H:i:s");
		$user_info = M("pingjia")->add($data);
		if(!$user_info){
			$this->Rerror("评价失败");
		}
		$this->Rsuccess("评价成功",$user_info);
	}
	//雇主端 | 已竣工的工人列表
	public function work_finsh_list(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$send_id = I("send_id") ? intval(I("send_id")) : '';
		if(!$user_id || !$send_id){
			$this->Rerror("缺少参数");
		}
		$info = M("order")->alias("a")
		->field("u.username,u.nickname,u.picture,u.gztypeid,a.userid,m.id as pinjia_id")
		->join("my_sendxq s on a.sendxqid=s.id")
		->join("my_user u on a.userid=u.id")
		->join('my_pingjia m on m.sendxqid = s.id','left')
		->where("a.sendxqid='".$send_id."' and s.states=86")->select();
		if(!$info){
			$this->Rsuccess("暂时没有完成的订单");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//雇主端 | 查看工人端评价列表
	public function check_worker_comment(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		if(!$user_id){
			$this->Rerror("缺少参数");
		}
		$info = M("pingjia")->order("createtime desc")->where("status=79")->select();
		if(!$info){
			$this->Rsuccess("暂时没有数据");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//删除评价
	public function delete_assess(){
		$assess_id = I("assess_id") ? intval(I("assess_id")) : ''; //评价id
		if(!$assess_id){
			$this->Rerror("缺少参数");
		}
		$news_info = M("pingjia")->where("id='".$assess_id."' and status=79")->find();
		if(!$news_info){
			$this->Rerror("非雇主评价");
		}
		$info = M("pingjia")->where("id='".$assess_id."'")->delete();
		if(!$info){
			$this->Rerror("删除失败");
		}
		$this->Rsuccess("删除成功");
	}
	//消息的订单详情
	public  function indent_details(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$send_id = I("send_id") ? intval(I("send_id")) : '';
		if(!$user_id || !$send_id){
			$this->Rerror("缺少参数");
		}
		$info = M("sendxq")->where("id='".$send_id."' and userid='".$user_id."'")->find();
		if(!$info){
			$this->Rsuccess("暂时没有订单");
		}
		$this->Rsuccess("查询成功",$info);
	}
}
