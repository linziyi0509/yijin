<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$后台管理-group$$$$
class GroupController extends BaseController {
    //####管理员分组列表-index####
    public function index(){
        $this->display();
    }


    public function list_page(){
        //分类

        $starttime = $_POST['starttime'];
        $endtime = $_POST['endtime'];
        $where = 'where 1=1 and';
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`createtime`)<='".$endtime."' and";
        }
        
    	$sql = "select * from `my_group` ".$where." `gname` like '%".$_POST['gname']."%' and `id`<>1 order by `id` desc";
    	$result = $this->pagelist_($sql,$_POST);
    	echo json_encode($result);
    }
    //@@@@管理员分组添加-add@@@@
    public function add(){
        $rbac = $this->getrbac2();
        $this->assign('rbac',$rbac);
        $this->display();
    }

    public function saveadd(){
        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        if(!empty($_POST['levelstr'])){
            $_POST['levelstr'] = implode(',',$_POST['levelstr']);
        }else{
            echo '请至少勾选一个权限！';
            exit;
        }
        M('my_group')->add($_POST);
        echo 'success';
    }
    //@@@@管理员分组修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
    	$result = M('my_group')->where(array('id'=>$id))->find();

        $rbac = $this->getrbac2();
        $this->assign('rbac',$rbac);

    	$this->assign('result',$result);
        $this->display();
    }

    public function saveupdate(){
    	$id = $_POST['id'];
        unset($_POST['id']);
        $arr = $_POST;
        if(!empty($arr['levelstr']) && $id != 1){//顶级账户不能修改权限
            $arr['levelstr'] = implode(',',$arr['levelstr']);
        }else{
            echo '请至少勾选一个权限！';
            exit;
        }
        M('my_group')->where(array('id'=>$id))->save($arr);
        echo 'success';
    }
    

    //@@@@管理员分组删除-del@@@@
    public function del(){
    	$id = $_POST['id'];
        if($id<2){
            exit;
        }
        M('my_group')->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
        $admin = M('my_group');
        $where['id'] = array('in',$idlist);
        $admin->where($where)->delete();
        echo 'success';
    }
}