<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$wechat自定义回复-wechatreplymusic$$$$
class WechatreplymusicController extends BaseController {
    //####音乐回复-index####
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
            $where = 'where `my_wechatreplymusic`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_wechatreplymusic`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_wechatreplymusic`.`createtime`)<='".$endtime."' and";
        }
        $where .= " `my_wechatreplymusic`.`title` like '%".$_POST['title']."%' and ";
        $where = trim($where,' and');
    	$sql = "select `my_wechatreplymusic`.`id`,`my_wechatreplymusic`.`keyword`,`my_wechatreplymusic`.`title`,`my_wechatreplymusic`.`description`,`my_wechatreplymusic`.`musicurl`,`my_wechatreplymusic`.`hdmusicurl`,`my_wechatreplymusic`.`thumbmediaid`,`my_wechatreplymusic`.`keywordtype`,`my_wechatreplymusic`.`media_id`,`my_wechatreplymusic`.`createtime`,`my_wechatreplymusic`.`updatetime` from `my_wechatreplymusic`  ".$where." order by `my_wechatreplymusic`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);

        

        

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('wechatreplymusic',array('ID','关键词','音乐标题','音乐描述','音乐url','高清音乐url','缩略图','关键词类型','缩略图media_id','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    

    

    //@@@@音乐回复添加-add@@@@
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
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg' ,'mp3');
        $upload->autoSub = true;
        $upload->subName = array('date','Ymd');
        $info = $upload->upload();

        

        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        $_POST['adminid'] = $_SESSION['id'];
        $wechatreplymusic = M('my_wechatreplymusic');
        $wechatreplymusic->add($_POST);
        echo 'success';
    }
    //@@@@音乐回复修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $wechatreplymusic = M('my_wechatreplymusic');
        $where['id'] = $id;
        $result = $wechatreplymusic->where($where)->find();
        

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
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg','mp3');
        $upload->autoSub = true;
        $upload->subName = array('date','Ymd');
        $info = $upload->upload();
        $_POST['updatetime'] =date('Y-m-d H:i:s',time());
        
    	$wechatreplymusic = M('my_wechatreplymusic');
        $where['id'] = $id;
        $wechatreplymusic->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $wechatreplymusic = M('my_wechatreplymusic');
        $where['id'] = $id;
        $result = $wechatreplymusic->where($where)->find();
        
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@音乐回复删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$wechatreplymusic = M('my_wechatreplymusic');
        $wechatreplymusic->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$wechatreplymusic = M('my_wechatreplymusic');
        $where['id'] = array('in',$idlist);
        $wechatreplymusic->where($where)->delete();
    }

}