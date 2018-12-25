<?php
namespace Admin\Controller;
use Think\Controller;
class IndexController extends BaseController {
    public function index(){
        batch_update();
    	$data = $this->getrbac();
//     	echo '<pre>';
//     	var_dump($data);die;
    	$this->assign('list',$data);
        $this->display();
    }
    public function setsession(){
    	//后台定时刷新防止session过期
    }
}