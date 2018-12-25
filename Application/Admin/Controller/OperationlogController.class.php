<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$系统管理-operationlog$$$$
class OperationlogController extends BaseController {
    //####操作日志-index####
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
            $where = 'where `my_operationlog`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_operationlog`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_operationlog`.`createtime`)<='".$endtime."' and";
        }
        $where .= " `my_operationlog`.`name` like '%".$_POST['name']."%' and ";$where .= " `my_operationlog`.`operator` like '%".$_POST['operator']."%' and ";
        $where = trim($where,' and');
    	$sql = "select `my_operationlog`.`id`,`my_operationlog`.`name`,`my_operationlog`.`operator`,`my_operationlog`.`url`,`my_operationlog`.`createtime`,`my_operationlog`.`updatetime` from `my_operationlog`  ".$where." order by `my_operationlog`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);

        

        

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('operationlog',array('ID','操作名称','操作人','操作连接','创建时间','更新时间'),$result['rows']);
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
        if(!empty($info)){
            foreach ($info as $key => $value) {
                if($value['key']){
                    $_POST[$value['key']] = '/upload/'.$value['savepath'].$value['savename'].',';
                }
            }
        }
        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        $_POST['adminid'] = $_SESSION['id'];
        $operationlog = M('my_operationlog');
        $operationlog->add($_POST);
        echo 'success';
    }
    //
    public function update(){
        $id = $_GET['id']; 
        $operationlog = M('my_operationlog');
        $where['id'] = $id;
        $result = $operationlog->where($where)->find();
        

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
        
    	$operationlog = M('my_operationlog');
        $where['id'] = $id;
        $operationlog->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $operationlog = M('my_operationlog');
        $where['id'] = $id;
        $result = $operationlog->where($where)->find();
        
        $this->assign('result',$result);
        $this->display();
    }
    //
    public function del(){
    	$id = $_GET['id'];
    	$operationlog = M('my_operationlog');
        $operationlog->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$operationlog = M('my_operationlog');
        $where['id'] = array('in',$idlist);
        $operationlog->where($where)->delete();
        echo 'success';
    }

}