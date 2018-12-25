<?php
namespace Api\Controller;
use Think\Controller;
class UserController extends BaseController {
    /**
    用户注册
    * 男 81 女 82   78 工人  79 雇主
    */
    public function register(){
    	$model 		= M('user');
    	$String     = new \Org\Util\String();
    	$data['salt'] 		= $String->randString(6,0);
        $data['mobile']     = I('param.mobile');
    	$data['password'] 	= I('param.password');
    	$data['type'] 		= I('param.type');
        $code               = I('param.code');
    	if(!$data['mobile'] || !$data['password'] || !$code){
    		$this->Rerror('请填写账号、密码和验证码');
    	}
    	$data['password'] 	= md5(md5($data['password']).$data['salt']);
    	if(!preg_match('/^1[\d]{10}$/', $data['mobile'])){
    		$this->Rerror('账号格式错误');
    	}
    	if($data['type'] > 79 || $data['type'] < 78){
    		$this->Rerror('用户分类错误');
    	}
    	$checktel = $model->where(array('mobile'=>$data['mobile']))->find();
    	if($checktel){
    		$this->Rerror('该账号已存在');
    	}
        $check_code = M("sendmess")->field("message")->order("ctime desc")->where("mobile='".$data['mobile']."'")->find();
        if($check_code["message"] != $code){
            $this->Rerror("验证码不对");
        }    	
    	$data['createtime'] = date('Y-m-d H:i:s');
    	$data['updatetime'] = $data['createtime'];
    	$data['nickname']   = "会员".rand(100,999)."号";
        $data['picture']   = '/upload/touxiang.jpg';
        $data['em_nickname'] ="会员".rand(100,999)."号";
        $data['em_image']   = '/upload/touxiang.jpg';
    	$newid = $model->data($data)->add();
        $user1 = M("userlog")->field("register_number")->where("day='".date("Y-m-d")."'")->find();
        if($user1){
            $user2["register_number"] = $user1["register_number"] + 1;
            M("userlog")->where("day='".date("Y-m-d")."'")->save($user2);
        }else{
            $new1["day"] = date("Y-m-d");
            $new1["register_number"] = 1;
            $new1["updatetime"] = date("Y-m-d H:i:s");
            $new1["createtime"] = date("Y-m-d H:i:s");
            M("userlog")->add($new1);
        }
    	if(!$newid){
    		$this->Rerror('注册失败请重试');
    	}
        $token  = md5($String->randString(6,0).$newid);
    	$model->where(array('id'=>$newid))->data(array("token"=>$token))->save();
        $this->Rsuccess("注册成功");
    }
    //发送验证码
    public function SendTemplateSMS(){
        $data['mobile']     = I('param.mobile');
        if (!preg_match('/^1[\d]{10}$/', $data['mobile'])){
            $this->Rerror('手机号码格式错误');
        }
        $model  = M('sendmess');
        $last   = $model->where(array('mobile'=>$data['mobile'],'stype'=>1,'ctime'=>array('gt',time()-30)))->order('id desc')->find();
        if ($last){
            echo json_encode(array('code'=>'1001','error'=>'短信发送的太频繁了'));
            exit;
        }
        $data['lip']        = get_client_ip();
        $lips   = $model->where(array('lip'=>$data['lip'],'stype'=>1,'ctime'=>array('gt',time()-3600)))->count();
        if($lips > 49){
            echo json_encode(array('code'=>'1001','error'=>'短信发送的太频繁了'));
            exit;
        }
        $String             = new \Org\Util\String();
        $data['message']    = $String->randString(6,1);
        $data['ctime']      = time();
        $data['stype']      = 1;
        //↑↑↑↑↑↑↑↑↑↑商户逻辑↑↑↑↑↑↑↑↑↑↑

        //发送短信
        vendor('Cloopen.CCPRestSmsSDK');
        vendor('Cloopen.SendTemplateSMS');
        $send = sendTemplateSMS($data['mobile'],array($data['message'],5),60007);
        //↓↓↓↓↓↓↓↓↓↓商户逻辑↓↓↓↓↓↓↓↓↓↓
        if($send['status']){
            if($model->data($data)->add()){
                echo json_encode(array('code'=>'1000','error'=>'短信发送成功'));
                exit;
            }else {
                echo json_encode(array('code'=>'1001','error'=>'短信发送失败'));
                exit;
            }
        }else {
            echo json_encode(array('code'=>'1001','error'=>$send['msg']));
            exit;
        }
        //↑↑↑↑↑↑↑↑↑↑商户逻辑↑↑↑↑↑↑↑↑↑
    }

    /**
    用户登录
    */
	public function login(){
		$model 		= M('user');
        $mobile  	= I('param.mobile');
        $pswd  		= I('param.password'); 
        $type       = I('param.type');
        $jpushcode  = I('param.jpushcode');   	
       
        if(!$mobile || !$pswd){
            $this->Rerror('请填写账号和密码');
        }
        
        if(!preg_match('/^1[\d]{10}$/', $mobile)){
            $this->Rerror('账号格式错误');
        }
        $data['logintime'] = date('Y-m-d H:i:s');
        $data['jpushcode'] = $jpushcode;
        $data['type']      = $type;
        $data_info = M("user")->field("salt")->where("mobile='".$mobile."'")->find();
        if($data_info){
              $user_info = M("user")->where("mobile='".$mobile."'")->save($data);
        }
        $uinfo 	= $model->where("mobile='".$mobile."' and password='".md5(md5($pswd).$data_info['salt'])."'")->find();
        if(!$uinfo){
            $this->Rerror("账号或者密码错误");
        }
        $uinfo["userid"] = $uinfo["id"];
        $this->Rsuccess("登录成功",$uinfo);
    }

    /**
    通用接口
    */
    public function index(){
        $this->check_param(array('sql'));
        $sql = urldecode($_REQUEST['sql']);
        // echo $sql;exit;
        $result = M()->query($sql);
        if ($result === false) {
            $this->no('sql语句报错');
        }
        $this->ok('执行成功',$result);
    }
    //更新用户位置
    public function save_user_place(){
	$user_id = I("user_id") ? intval(I("user_id")) : ''; //用户id
        $data["a_lng"] = I("a_lng")   ? trim (I("a_lng")) : '';   //经度
        $data["a_lat"] = I("a_lat")   ? trim(I("a_lat")) : '';    //纬度
	if(!$user_id){
		$this->Rerror("缺少参数");
	}
        $info = M("user")->where("id='".$user_id."'and type=78")->save($data);
        if(!$info){
            $this->Rerror("更新失败");
        }
        $this->Rsuccess("更新成功");
    }
    //附近的工人
    public function near_worker(){
        $user_id = I("user_id") ? intval(I("user_id")) : ''; //用户id
        $lng   = I("a_lng")   ? trim (I("a_lng")) : '';   //经度
        $lat   = I("a_lat")   ? trim(I("a_lat")) : '';    //纬度
        if(!$user_id){
            $this->Rerror("缺少参数");
        }
        $alist = M("user")->field("nickname,picture,typeName,a_lat,a_lng")->join("my_gztype on my_user.gztypeid = my_gztype.id")->where("type=78")->select();
        foreach ($alist as $ak=>&$av){
            $lbsmeter       = getMeter1($lat,$lng,$av['a_lat'],$av['a_lng']);
            $av['distance'] = numtometer1($lbsmeter);
            $meters[$ak]    = $lbsmeter;
        }
        if(!$alist){
            $this->Rsuccess("暂时没有工人");
        }
        $this->Rsuccess("查询成功",$alist);
    }
    //附近的订单
    public  function near_order(){
        $lng = I("a_lng") ? trim(I("a_lng")) : '';
        $lat = I("a_lat") ? trim(I("a_lat")) : '';
        $alist = M("sendxq")->where("states!=86")->select();
        foreach ($alist as $ak=>&$av){
            $lbsmeter       = getMeter1($lat,$lng,$av['lng'],$av['lat']);
            $av['distance'] = numtometer1($lbsmeter);
            $meters[$ak]    = $lbsmeter;
        }
        if(!$alist){
            $this->Rsuccess("暂时没有订单");
        }
        $this->Rsuccess("查询成功",$alist);
    }
    //找回密码
     public function back_password(){
        $mobile = I("mobile") ? trim(I("mobile")) : ''; //手机号
        $new_password = I("new_password") ? trim(I("new_password")) : ''; //新密码
        $code = I("code") ? trim(I("code")) : '';   //验证码
        if(!$mobile || !$new_password || !$code){
           $this->Rerror("缺少参数");
        }
        $data_info=M("sendmess")->field("message")->order("ctime desc")->where("mobile='".$mobile."'")->find();
        if($code!=$data_info["message"]){
               $this->Rerror("验证码不对");
        }
        $check_mobile = M("user")->field("salt")->where("mobile='".$mobile."'")->find();
        if(!$check_mobile){
                $this->Rerror('该账号不存在');
        }
        $data["password"] = md5(md5($new_password).$check_mobile['salt']);
        $info = M("user")->where("mobile='".$mobile."'")->save($data);
        if(!$info){
            $this->Rerror("修改失败");
        }
        $this->Rsuccess("修改成功");
    }
}
