<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$用户管理-use$$$$
class UseController extends BaseController {
    //####用户统计-index####
    public function index(){
        
        $this->display();
    }


    public function list_page(){
        //导出逻辑
        if($_POST['daochu']){
            $_POST['page'] = 1;
            $_POST['rows'] = 99999999;
        }
        $sort = isset($_POST['sort']) ? $_POST['sort'] : 'id';
        $order = isset($_POST['order']) ? $_POST['order'] : 'desc';
        //导出逻辑结束
        $starttime = $_POST['starttime'];
        $endtime = $_POST['endtime'];
        if($_SESSION['all'] == 1){
            $where = 'where 1=1 and ';
        }else{
            $where = 'where `my_use`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_use`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_use`.`createtime`)<='".$endtime."' and";
        }
        
        $where = trim($where,' and');
    	$sql = "select `my_use`.`id`,`my_use`.`createtime`,`my_use`.`updatetime` from `my_use`  ".$where." order by `my_use`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);

        

        

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('use',array('ID','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    

    

    //
    public function add(){
        
        $this->display();
    }

    public function saveadd(){
        //上传
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;
        $upload->rootPath = './upload/';
        $upload->savePath = '';
        $upload->saveName = array('uniqid','');//uniqid函数生成一个唯一的字符串序列。
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->autoSub = true;
        $upload->subName = array('date','Ymd');
        $info = $upload->upload();

        

        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        $_POST['adminid'] = $_SESSION['id'];
        $use = M('my_use');
        $use->add($_POST);
        echo 'success';
    }
    //
    public function update(){
        $id = $_GET['id']; 
        $use = M('my_use');
        $where['id'] = $id;
        $result = $use->where($where)->find();
        

    	$this->assign('result',$result);
        $this->display();
    }

    public function saveupdate(){
    	$id = $_POST['id'];
    	unset($_POST['id']);
        //上传
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;
        $upload->rootPath = './upload/';
        $upload->savePath = '';
        $upload->saveName = array('uniqid','');//uniqid函数生成一个唯一的字符串序列。
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->autoSub = true;
        $upload->subName = array('date','Ymd');
        $info = $upload->upload();
        $_POST['updatetime'] =date('Y-m-d H:i:s',time());
        
    	$use = M('my_use');
        $where['id'] = $id;
        $use->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $use = M('my_use');
        $where['id'] = $id;
        $result = $use->where($where)->find();
        
        $this->assign('result',$result);
        $this->display();
    }
    //
    public function del(){
    	$id = $_GET['id'];
    	$use = M('my_use');
        $use->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$use = M('my_use');
        $where['id'] = array('in',$idlist);
        $use->where($where)->delete();
        echo 'success';
    }

}