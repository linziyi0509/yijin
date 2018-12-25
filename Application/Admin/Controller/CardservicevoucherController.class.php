<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$卡券管理-cardservicevoucher$$$$
class CardservicevoucherController extends BaseController {
    //####卡券服务券关联-index####
    public function index(){
        $c31 = $this->getclass(31,'usestatus');
        $this->assign('c31',$c31);$c19 = $this->getclass(19,'status');
        $this->assign('c19',$c19);
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
            $where = 'where `my_cardservicevoucher`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        $cards = M('my_cards');
        if($_POST['cardnostart'] <> ''){
            $dataStart = $cards->where("cardno = ".$_POST['cardnostart'])->find();
            if($dataStart){
                $where .= " `my_cards`.`id` >= ".$dataStart['id']." and ";
            }
        }
        if($_POST['cardnostart'] <> ''){
            $dataEnd = $cards->where("cardno = ".$_POST['cardnoend'])->find();
            if($dataEnd){
                $where .= " `my_cards`.`id` <= ".$dataEnd['id']." and ";
            }
        }
        if($_POST['username'] <> ''){
            $where .= " `my_wechatuser`.`nickname` like '".$_POST['username']."' and ";
        }
        if($_POST['phone'] <> ''){
            $where .= " `my_wechatuser`.`phone` like '".$_POST['phone']."' and ";
        }
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_cardservicevoucher`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_cardservicevoucher`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['cardsid'] <> ''){$where .= " `my_cardservicevoucher`.`cardsid` = ".$_POST['cardsid']." and ";}if($_POST['wechatuserid'] <> ''){$where .= " `my_cardservicevoucher`.`wechatuserid` = ".$_POST['wechatuserid']." and ";}if($_POST['servicevoucherid'] <> ''){$where .= " `my_cardservicevoucher`.`servicevoucherid` = ".$_POST['servicevoucherid']." and ";}if($_POST['usestatus'] <> ''){$where .= " `my_cardservicevoucher`.`usestatus` = ".$_POST['usestatus']." and ";}if($_POST['status'] <> ''){$where .= " `my_cardservicevoucher`.`status` = ".$_POST['status']." and ";}
        $where = trim($where,' and');
        $sql = "select `my_cardservicevoucher`.`id`,`my_cardservicevoucher`.`usetime`,`my_cardservicevoucher`.`usestatus`,`my_cardservicevoucher`.`status`,`my_cardservicevoucher`.`starttime`,`my_cardservicevoucher`.`endtime`,`my_cardservicevoucher`.`createtime`,`my_cardservicevoucher`.`updatetime`,`my_cards`.`cardno`,`my_wechatuser`.`nickname`,`my_wechatuser`.`phone`,`my_servicevoucher`.`name`,`my_servicevoucher`.`integral` from `my_cardservicevoucher`  left join `my_cards` on `my_cards`.`id` = `my_cardservicevoucher`.`cardsid` left join `my_wechatuser` on `my_wechatuser`.`id` = `my_cardservicevoucher`.`wechatuserid` left join `my_servicevoucher` on `my_servicevoucher`.`id` = `my_cardservicevoucher`.`servicevoucherid` ".$where." order by `my_cardservicevoucher`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['usestatus'] = $this->getclassname($value['usestatus']);$result['rows'][$key]['status'] = $this->getclassname($value['status']);}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('cardservicevoucher',array('ID','使用时间','使用状态','是否激活','开始时间','结束时间','创建时间','更新时间','卡号','用户名','手机号','服务券名称','服务券对应积分'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    

    

    //
    public function add(){
        $c31 = $this->getclass(31,'usestatus');
        $this->assign('c31',$c31);$c19 = $this->getclass(19,'status');
        $this->assign('c19',$c19);
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
        $cardservicevoucher = M('my_cardservicevoucher');
        $cardservicevoucher->add($_POST);
        echo 'success';
    }
    //@@@@卡券服务券关联修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $cardservicevoucher = M('my_cardservicevoucher');
        $where['id'] = $id;
        $result = $cardservicevoucher->where($where)->find();
        $c31 = $this->getclass(31,'usestatus',$result['usestatus']);
        $this->assign('c31',$c31);$c19 = $this->getclass(19,'status',$result['status']);
        $this->assign('c19',$c19);

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
        
    	$cardservicevoucher = M('my_cardservicevoucher');
        $where['id'] = $id;
        $cardservicevoucher->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $cardservicevoucher = M('my_cardservicevoucher');
        $where['id'] = $id;
        $result = $cardservicevoucher->where($where)->find();
        $c31 = $this->getclass(31,'usestatus',$result['usestatus']);
        $this->assign('c31',$c31);$c19 = $this->getclass(19,'status',$result['status']);
        $this->assign('c19',$c19);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@卡券服务券关联删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$cardservicevoucher = M('my_cardservicevoucher');
        $cardservicevoucher->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$cardservicevoucher = M('my_cardservicevoucher');
        $where['id'] = array('in',$idlist);
        $cardservicevoucher->where($where)->delete();
        echo 'success';
    }

}