<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$分类管理-class-3$$$$
class ClassController extends BaseController {
    //####分类管理列表-index####
    public function index(){
        $this->display();
    }


    public function getlist(){
    	$sql = "select * from `my_class` order by `order` asc";
    	$result = $this->list_($sql);
    	echo $result;
    }


    public function saveadd(){
        M('my_class')->add($_POST);
    }


    public function saveupdate(){
    	$id = $_POST['id'];
    	unset($_POST['id']);
    	$arr = $_POST;
        M('my_class')->where(array('id'=>$id))->save($arr);
    }

    //@@@@分类管理删除-del@@@@
    public function del(){
    	$id = $_POST['id'];
        M('my_class')->delete($id);
        M('my_class')->where(array('parentid'=>$id))->delete();
    }

    

}