<?php
namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function index(){
        $this->display();
    }

    public function check_login(){
    	$arr = $_POST;
    	if($arr['username'] == '' || $arr['password'] == '' || $arr['verify'] == ''){
    		$data = array('result'=>false,'data'=>'','message'=>'请填写完整信息!');
    		echo json_encode($data);exit;
    	}
    	if(!check_verify($arr["verify"])){
    	    $data = array('result'=>false,'data'=>'','message'=>'验证码填写有误!');
    	    exit(json_encode($data));
    	}
        $data = M('my_admin')->alias('a')->field('a.*,g.`levelstr`,g.`all`,g.`id` as groupid,g.`gname` as gname')->join('my_group as g on g.id=a.groupid')->where(array('a.username'=>$arr['username'],'a.password'=>md5_($arr['password'])))->find();
    	if($data){
    		$_SESSION['user_name'] = $data['username'];//用户名
            $_SESSION['levelstr'] = $data['levelstr'];//用户权限id
            $_SESSION['all'] = $data['all'];//是否查看全部
            $_SESSION['id'] = $data['id'];//用户id
            $_SESSION['adminid'] = $data['id'];//用户id
            $_SESSION['groupid'] = $data['groupid'];//分组id
			$_SESSION['gname'] = $data['gname'];//分组名
    		$data = array('result'=>true,'data'=>$data,'message'=>'登录成功');
    		echo json_encode($data);
    	}else{
    		$data = array('result'=>false,'data'=>'','message'=>'用户名或密码错误!');
    		echo json_encode($data);
    	}
    }
    public function logout(){
		session_destroy();
	}
	public function updatepassword(){
        $id = $_SESSION['id'];
        $arr = array('password'=>md5_($_POST['newpass']));
        M('my_admin')->where(array('id'=>$id))->save($arr);
        echo '修改成功';
	}
    
    /**
     *@FUNCNAME:verify;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月22日;
     *@EFFORT:验证码;
     **/
	public function verify(){
	    //清除缓存
	    ob_end_clean();
        $config = array(
            'length' => 4,     // 验证码位数
            'useNoise' => false, // 关闭验证码杂点
            'useZh' => false,
            'codeSet' => '0123456789'
        );
	    $verify = new \Think\Verify($config);
	    $verify->entry();
	}
	public function sendemail(){
	    $email = 'yijin@ejinkj.com';
	    $title = '后台用户授权商户';
        $content = "编号id为：".session('adminid')."---账户：".session('user_name').'---分组：'.session('gname').'---请超级管理员尽快给此账户分配商户，以免耽误用户的操作';
        $flag = sendMail2($email,$title,$content);
        if($flag){
            session_destroy();
            $this->success('请等待管理员操作！','index');
        }
    }
}