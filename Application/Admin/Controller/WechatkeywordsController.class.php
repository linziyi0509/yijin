<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$微信-wechatkeywords$$$$
class WechatkeywordsController extends BaseController {
    //####回复语-index####
    public function index(){
        $c52 = $this->getclass(52,'type');
        $this->assign('c52',$c52);$c56 = $this->getclass(56,'status');
        $this->assign('c56',$c56);
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
            $where = 'where `my_wechatkeywords`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_wechatkeywords`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_wechatkeywords`.`createtime`)<='".$endtime."' and";
        }
        $where .= " `my_wechatkeywords`.`type` like '%".$_POST['type']."%' and ";
        $where = trim($where,' and');
    	$sql = "select `my_wechatkeywords`.`id`,`my_wechatkeywords`.`keyword`,`my_wechatkeywords`.`response`,`my_wechatkeywords`.`type`,`my_wechatkeywords`.`status`,`my_wechatkeywords`.`createtime`,`my_wechatkeywords`.`updatetime` from `my_wechatkeywords`  ".$where." order by `my_wechatkeywords`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);

        

        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['type'] = $this->getclassname($value['type']);$result['rows'][$key]['status'] = $this->getclassname($value['status']);}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('wechatkeywords',array('ID','关键字','回复内容','微信回复语类型','微信回复语状态','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    

    

    //@@@@回复语添加-add@@@@
    public function add(){
        $c52 = $this->getclass(52,'type');
        $this->assign('c52',$c52);$c56 = $this->getclass(56,'status');
        $this->assign('c56',$c56);
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
        $wechatkeywords = M('my_wechatkeywords');
        $wechatkeywords->add($_POST);
        echo 'success';
    }
    //@@@@回复语修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $wechatkeywords = M('my_wechatkeywords');
        $where['id'] = $id;
        $result = $wechatkeywords->where($where)->find();
        $c52 = $this->getclass(52,'type',$result['type']);
        $this->assign('c52',$c52);$c56 = $this->getclass(56,'status',$result['status']);
        $this->assign('c56',$c56);

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
        
    	$wechatkeywords = M('my_wechatkeywords');
        $where['id'] = $id;
        $wechatkeywords->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $wechatkeywords = M('my_wechatkeywords');
        $where['id'] = $id;
        $result = $wechatkeywords->where($where)->find();
        $c52 = $this->getclass(52,'type',$result['type']);
        $this->assign('c52',$c52);$c56 = $this->getclass(56,'status',$result['status']);
        $this->assign('c56',$c56);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@回复语删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$wechatkeywords = M('my_wechatkeywords');
        $wechatkeywords->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$wechatkeywords = M('my_wechatkeywords');
        $where['id'] = array('in',$idlist);
        $wechatkeywords->where($where)->delete();
        echo 'success';
    }

}