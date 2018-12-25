<?php
namespace Api\Controller;
use Think\Controller;
class PersonalController extends BaseController{
	//编辑工人端信息
	public function save_user(){
		$user_id = I("user_id") ? intval(I("user_id"))  : '';
		$username = I("username") ? trim(I("username"))  : '';
		$sex = I("sex") ? intval(I("sex"))  : '';
		$type = I("type") ? intval(I("type"))  : '';
		$live_city = I("live_city") ? trim(I("live_city"))  : '';
		$address = I("address") ? trim(I("address"))  : '';
		$job_year = I("job_year") ? intval(I("job_year"))  : '';
		$education = I("education") ? trim(I("education"))  : '';
		$id_card = I("id_card") ? trim(I("id_card"))  : '';
		$user_desc = I("user_desc") ? trim(I("user_desc"))  : '';
		if(!$user_id || !$username){
			$this->Rerror("缺少参数");
		}
		$imgArr = $this->uploadPic();
        $imgStr = implode(',',$imgArr['imgPath']);
		$data["username"] = $username;
		$data["nickname"] = $username;
		$data["sex"] = $sex;
		$data["gztypeid"] = $type;
		$data["live_city"] = $live_city;
		$data["address"] = $address;
		$data["job_year"] = $job_year;
		$data["education"] = $education;
		$data["education_image"] = strval($imgStr);
		$data["id_card"] = $id_card;
		$data["user_desc"] = $user_desc;
		$info = M("user")->where("id='".$user_id."'")->save($data);
		if(!$info){
			$this->Rerror("修改失败");
		}
		$this->Rsuccess("修改成功");
	}
	//编辑雇主端信息
	public function  edit_employee(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$username = I("username") ? trim(I("username")) : '';
		$nickname["nickname"] = $username;
		$sex = I("sex") ? intval(I("sex")) : '';
		$tel = I("tel") ? trim(I("tel")) : '';
		$address = I("address") ? trim(I("address")) : '';
		if(!$user_id || !$username || !$sex || !$tel || !$address){
			$this->Rerror("参数为空");
		}
		$data["em_name"] = $username;
		$data["em_sex"]  = $sex;
		$data["tel"]  =$tel;
		$data["em_address"] = $address;
		$info = M("user")->where("id='".$user_id."'")->save($data);
		if(!$info){
			$this->Rerror("修改失败");
		}
		$this->Rsuccess("修改成功");

	}
	//修改工人端头像
	public function save_image(){
		$user_id = I("user_id") ? intval(I("user_id"))  : '';
		if(!$user_id){
			$this->Rerror("缺少参数");
		}
		$imgArr = $this->uploadPic();
		if(!$imgArr['imgPath']){
			$this->Rerror('上传失败');
		}
		foreach ($imgArr['imgPath'] as $iv){
			$data['picture'] =$iv;
		}
		$info = M("user")->where("id='".$user_id."'")->save($data);
		$new_info = M("user")->field("picture")->where("id='".$user_id."'")->find();
		if(!$info){
			$this->Rerror('修改失败');
		}
		$this->Rsuccess("修改成功",$new_info);
	}
	//修改雇主端头像
	public function save_em_image(){
		$user_id = I("user_id") ? intval(I("user_id"))  : '';
		if(!$user_id){
			$this->Rerror("缺少参数");
		}
		$imgArr = $this->uploadPic();
		if(!$imgArr['imgPath']){
			$this->Rerror('上传失败');
		}
		foreach ($imgArr['imgPath'] as $iv){
			$data['em_image'] =$iv;
		}
		$info = M("user")->where("id='".$user_id."'")->save($data);
		$new_info = M("user")->field("em_image")->where("id='".$user_id."'")->find();
		if(!$info){
			$this->Rerror('修改失败');
		}
		$this->Rsuccess("修改成功",$new_info);
	}
	//工人端|我的红包
	public function my_money(){
		$user_id = I("user_id") ? intval(I("user_id"))  : '';
		if(!$user_id ){
			$this->Rerror("缺少参数");
		}
		$info = M("user")->field("money")->where("id='".$user_id."'")->find();
		$money_info = M("redmoney")->where("type=78")->select();
		if(!$info){
			$this->Rerror("没有数据哦");
		}
		$arr = array("code"=>"1000","message"=>"查询成功","data"=>$info,"money"=>$money_info);
        exit(json_encode($arr));
	}
	//雇主端| 我的红包
	public function my_employee_money(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		if(!$user_id){
			$this->Rerror("缺少参数");
		}
		$info = M("user")->field("em_money")->where("id='".$user_id."'")->find();
		$money_info = M("redmoney")->where("type=79")->select();
		if(!$info){
			$this->Rerror("没有数据");
		}
		$arr = array("code"=>"1000","message"=>"查询成功","data"=>$info,"money"=>$money_info);
        exit(json_encode($arr));
	}
	//用户反馈
	public function user_feedback(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$content = I("content") ? trim (I("content")) : '';
		$nickname = I("nickname") ? trim (I("nickname")) : '';
		$type    = I("type") ? intval(I("type")) : '';
		$mobile  = I("mobile") ? trim(I("mobile")) : '';
		if(!$user_id || !$content || !$nickname || !$type){
			$this->Rerror("缺少参数");
		}
		$data["createtime"] = date("Y-m-d H:i:s");
		$data["updatetime"] = date("Y-m-d H:i:s");
		$data["nickname"]   = $nickname;
		$data["userid"]    = $user_id;
		$data["content"]   = $content;
		$data["phone"]     = $mobile;
		$data["type"]      = $type;
		$info = M("feedback")->add($data);
		if(!$info){
			$this->Rerror("反馈失败");
		}
		$this->Rsuccess("反馈成功");
	}
	//分享得积分
	public function share_with(){
		$user_id = I("user_id") ? intval(I("user_id"))  : '';
		$record = I("record") ? intval(I("record"))  : '';
		if(!$user_id || !$record){
			$this->Rerror("缺少参数");
		}
		$info = M("user")->field("record")->where("id='".$user_id."'")->find();
		$info1["record"] = $info["record"]+ $record;
		$data = M("user")->where("id='".$user_id."'")->save($info1);
		if(!$data){
			$this->Rerror("赠送失败");
		}
		$this->Rsuccess("赠送成功");
	}
	//我的订单
	public function my_order(){
		$user_id = I("user_id") ? intval(I("user_id"))  : '';
		$pageindex = I("pageindex") ? intval(I("pageindex"))  : '';
		$status = I("status") ? intval(I("status"))  : '';
		if(!$user_id || !$pageindex || !$status){
			$this->Rerror("缺少参数");
		}
		if($status==84){
			$pagesize = 10; // 每页显示条数
			$offset = ($pageindex-1)*$pagesize;  //偏移量
			$info = M("sendxq")->order("createtime desc")->where("userid='".$user_id."' and states=84")->limit($offset,$pagesize)->select();
			if(!$info){
				$this->Rsuccess("暂时没有数据");
			}
			$this->Rsuccess("查询成功",$info);
		}else if($status==85){
			$pagesize = 10; // 每页显示条数
			$offset = ($pageindex-1)*$pagesize;  //偏移量
			$info = M("sendxq")->order("createtime desc")->where("userid='".$user_id."' and states=85")->limit($offset,$pagesize)->select();
			if(!$info){
				$this->Rsuccess("暂时没有数据");		
			}
			$this->Rsuccess("查询成功",$info);
		}
		$pagesize = 10; // 每页显示条数
		$offset = ($pageindex-1)*$pagesize;  //偏移量
		$info = M("sendxq")->order("createtime desc")->where("userid='".$user_id."' and states=86")->limit($offset,$pagesize)->select();
		if(!$info){
			$this->Rsuccess("暂时没有数据");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//雇主订单状态数量
	public function my_order_count(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		if(!$user_id){
			$this->Rerror("缺少参数");
		}
		$send = M("sendxq")->order("createtime desc")->where("userid='".$user_id."' and states=84")->count();
		$work = M("sendxq")->order("createtime desc")->where("userid='".$user_id."' and states=85")->count();
		$finish =  M("sendxq")->order("createtime desc")->where("userid='".$user_id."' and states=86")->count();
		if(!$send && !$work && !$finish){
			$arr = array("code"=>"1000","send"=>0,"message"=>"查询成功","work"=>0,"finish"=>0);
			exit(json_encode($arr));
		}else if($send && !$work && !$finish){
			$arr = array("code"=>"1000","send"=>$send,"message"=>"查询成功","work"=>0,"finish"=>0);
			exit(json_encode($arr));
		}else if(!$send && $work && !$finsh){
			$arr = array("code"=>"1000","send"=>0,"message"=>"查询成功","work"=>$work,"finish"=>0);
			exit(json_encode($arr));
		}else if(!$send && !$work && $finish){
			$arr = array("code"=>"1000","send"=>0,"message"=>"查询成功","work"=>0,"finish"=>$finish);
			exit(json_encode($arr));
		}else if($send && $work && !$finish){
			$arr = array("code"=>"1000","send"=>$send,"message"=>"查询成功","work"=>$work,"finish"=>0);
			exit(json_encode($arr));
		}else if($send && !$work && $finish){
			$arr = array("code"=>"1000","send"=>$send,"message"=>"查询成功","work"=>0,"finish"=>$finish);
			exit(json_encode($arr));
		}else if(!$send && $work && $finish){
			$arr = array("code"=>"1000","send"=>0,"message"=>"查询成功","work"=>$work,"finish"=>$finish);
			exit(json_encode($arr));
		}
		$arr = array("code"=>"1000","send"=>$send,"message"=>"查询成功","work"=>$work,"finish"=>$finish);
		exit(json_encode($arr));
	}
	//工人端 | 显示工种分类
	public function work_sort(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		if(!$user_id){
			$this->Rerror("缺少参数");
		}
		$info = M("gztype")->field("id,typeName,money")->select();
		if(!$info){
			$this->Rsuccess("暂时没有工种分类");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//添加银行卡
	public function add_bank_card(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$username = I("username") ? trim(I("username")) : '';
		$bank_number = I("bank_number") ? trim(I("bank_number")) : '';
		$bank_name = I("bank_name") ? trim(I("bank_name")) : '';
		if(!$user_id || !$username || !$bank_number || !$bank_name){
			$this->Rerror("缺少参数");
		}
		$bank_info = M("yhk")->where("num='".$bank_number."' or name='".$bank_name."'")->find();
		if($bank_info){
			$this->Rerror("添加的卡号或者开卡银行已存在");
		}
		$data["userid"]   = $user_id;
		$data["username"] = $username;
		$data["num"]      = $bank_number;
		$data["name"]     = $bank_name;
		$data["createtime"] = date("Y-m-d H:i:s");
		$data["updatetime"] = date("Y-m-d H:i:s");
		$info = M("yhk")->add($data);
		if(!$info){
			$this->Rerror("添加银行卡失败");
		}
		$this->Rsuccess("添加银行卡成功");
	}
	//我的银行卡列表
	public function my_bank_card(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		if(!$user_id){
			$this->Rerror("缺少参数");
		}
		$info = M("yhk")->field("name,num")->where("userid='".$user_id."'")->select();
		if(!$info){
			$this->Rsuccess("您还没有添加银行卡");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//用户提现
	public  function user_cash_money(){
		$user_id = I("user_id") ? intval(I("user_id")) : '' ;
		$goods_type = I("goods_type") ? trim(I("goods_type")) : '' ;
		$username = I("username") ? trim(I("username")) : '' ;
		$bank_name = I("bank_name") ? trim(I("bank_name")) : '' ;
		$bank_number= I("bank_number") ? trim(I("bank_number")) : '';
		$carry_cash = I("carry_cash") ? trim(I("carry_cash")) : '' ;
		$type = I("type") ? intval(I("type")) :'';
		if(!$user_id ||!$goods_type || !$username || !$bank_name || !$bank_number || !$carry_cash || !$type){
			$this->Rerror("缺少参数");
		}
		if($type==78){
			$user_money = M("user")->field("money")->where("id='".$user_id."'")->find();
			if($carry_cash > $user_money["money"]){
				$this->Rerror("余额不足");
			}
			$new_data["money"] = $user_money["money"] - $carry_cash;
			M("user")->where("id='".$user_id."'")->save($new_data);
		}else if($type==79){
			$user_main_money = M("user")->field("recharge_money")->where("id='".$user_id."'")->find();
			if($carry_cash > $user_main_money["recharge_money"]){
				$this->Rerror("余额不足");
			}
			$new_data["recharge_money"] = $user_main_money["recharge_money"] - $carry_cash;
			M("user")->where("id='".$user_id."'")->save($new_data);
		}
		$data["userid"]      = $user_id;
		$data["goods_type"]  = $goods_type;
		$data["bank_name"]   = $bank_name;
		$data["bank_number"] = $bank_number;
		$data["carry_cash"]  = $carry_cash;
		$data["username"]    = $username;
		$data["status"]      = 107;
		$data["order_number"]='xmj'.date("YmdHis").rand(100,999);
		$data["createtime"]  = date("Y-m-d H:i:s");
		$data["updatetime"]  = date("Y-m-d H:i:s");
		$info = M("usertx")->add($data);
		if(!$info){
			$this->Rerror("提现失败");
		}
		$this->Rsuccess("提现成功");
	}
	//用户交易
	public function user_trade(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		if(!$user_id){
			$this->Rerror("参数为空");
		}
		$info = M("usertx")->where("userid='".$user_id."'")->select();
		if(!$info){
			$this->Rsuccess("暂时没有交易记录");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//交易详情
	public function user_trade_details(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$trade_id = I("trade_id") ? intval(I("trade_id")) : '';
		if(!$user_id || !$trade_id){
			$this->Rerror("缺少参数");
		}
		$info = M("usertx")->where("id='".$trade_id." and userid='".$user_id."'")->find();
		if(!$info){
			$this->Rerror("查询失败");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//工人端 | 我的消息
	public function my_work_news(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		if(!$user_id){
			$this->Rerror("缺少参数");
		}
		$info = M("news")->order("createtime desc")->where("userid='".$user_id."'")->select();
		if(!$info){
			$this->Rsuccess("无消息展示");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//雇主端 | 我的消息
	public function my_employee_news(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		if(!$user_id){
			$this->Rerror("缺少参数");
		}
		$info = M("news")->order("createtime desc")->where("userid='".$user_id."'")->select();
		if(!$info){
			$this->Rsuccess("暂时没有数据");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//工人端 | 消息详情
	public function my_work_news_details(){
		$news_id = I("news_id") ? intval(I("news_id")) : '';
		if(!$news_id){
			$this->Rerror("缺少参数");
		}
		$info = M("news")->where("id='".$news_id."'")->find();
		if(!$info){
			$this->Rerror("消息不存在");
		}
		$this->Rsuccess("查询成功",$info);
	}
	//我的礼包
	public  function my_gift(){
		$user_id = I("user_id") ? intval(I("user_id")) : '';
		$type = I("type") ? intval(I("type")) : '';
		if(!$user_id || !$type){
			$this->Rerror("缺少参数");
		}
		$info = M("redmoney")->where("type='".$type."'")->select();
		if(!$info){
			$this->Rsuccess("暂时没有礼包");
		}
		$this->Rsuccess("查询成功",$info);
 	}
 	//我的余额
 	public function my_balance(){
 		$user_id = I("user_id") ? intval(I("user_id")) : '';
 		$user_type = I("type") ? intval(I("type")) : '';
 		if(!$user_id || !$user_type){
 			$this->Rerror("缺少参数");
 		}
 		if($user_type==78){
 			$info = M("user")->field("money")->where("id='".$user_id."'")->find();
 			$this->Rsuccess("查询成功",$info);
 		}
		$info = M("user")->field("recharge_money")->where("id='".$user_id."'")->find();
		$this->Rsuccess("查询成功",$info);
 	}
	//雇主端 | 我的消息
	public  function em_my_news(){
		$phone = I("phone") ? trim(I("phone")) : '' ;
		$type  = I("type") ? intval(I("type")) : '' ;
		if(!$phone || !$type){
			$this->Rerror("缺少参数");
		}
		$info = M("news")->order("createtime desc")->where("news_phone='".$phone."' and user_type='".$type."'")->select();
		if(!$info){
		   $this->Rsuccess("暂时没有消息");
		}
		$this->Rsuccess("查询成功",$info);
	
	}





}
