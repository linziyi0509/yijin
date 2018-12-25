<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$***md***-***table***$$$$
class ***Table***Controller extends BaseController {
    //####***module***-index####
    public function index(){
        ***class***
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
            $where = 'where `my_***table***`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_***table***`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_***table***`.`createtime`)<='".$endtime."' and";
        }
        ***where***
        $where = trim($where,' and');
    	$sql = "select `my_***table***`.`id`***otherfiled***,`my_***table***`.`createtime`,`my_***table***`.`updatetime` from `my_***table***` ***leftjoin*** ".$where." order by `my_***table***`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);

        

        ***getclassname***

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('***table***',array('ID',***showfiled***'创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    //***duoduiduo_control***

    //***duoduiduodaoru_control***

    //***tianjia***
    public function add(){
        ***class***
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
        if(!empty($info)){
            foreach ($info as $key => $value) {
                if($value['key']){
                    $_POST[$value['key']] = '/upload/'.$value['savepath'].$value['savename'].',';
                }
            }
        }
        ***file***

        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        $_POST['adminid'] = $_SESSION['id'];
        $***table*** = M('my_***table***');
        $***table***->add($_POST);
        echo 'success';
    }
    //***xiugai***
    public function update(){
        $id = $_GET['id']; 
        $***table*** = M('my_***table***');
        $where['id'] = $id;
        $result = $***table***->where($where)->find();
        ***classupdate***

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
        if(!empty($info)){
            foreach ($info as $key => $value) {
                if($value['key']){
                    $_POST[$value['key']] = '/upload/'.$value['savepath'].$value['savename'].',';
                }
            }
        }
        $_POST['updatetime'] =date('Y-m-d H:i:s',time());
        ***file***
    	$***table*** = M('my_***table***');
        $where['id'] = $id;
        $***table***->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $***table*** = M('my_***table***');
        $where['id'] = $id;
        $result = $***table***->where($where)->find();
        ***classupdate***
        $this->assign('result',$result);
        $this->display();
    }
    //***shanchu***
    public function del(){
    	$id = $_GET['id'];
    	$***table*** = M('my_***table***');
        $***table***->delete($id);
        echo 'success';
    }
    //***piliangshanchu***
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$***table*** = M('my_***table***');
        $where['id'] = array('in',$idlist);
        $***table***->where($where)->delete();
        echo 'success';
    }

}