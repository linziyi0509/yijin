<?php
namespace Api\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function __construct(){
        parent::__construct();
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
    //版本升级
    public function  edition_upgrade(){
        $user_id = I("user_id") ? intval(I("user_id")) : '';
        if(!$user_id){
            $this->Rerror("缺少参数");
        }
        $info = M("update")->order("createtime desc")->find();
        if(!$info){
            $this->Rerror("暂时没有新的版本");
        }
        $this->Rsuccess("查询成功",$info);
    }
    //关于我们
    public function about_our(){
    	$info = M("aboutus")->field("content")->find();
    	if(!$info){
    		$this->Rsuccess("暂时没有介绍");
    	}
    	$this->Rsuccess("查询成功",$info);
    }
}
