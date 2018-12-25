<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$订单管理-buyingmodofcustomer$$$$
class BuyingmodofcustomerController extends BaseController {
    //####订单详情-index####
    public function index(){
        $c31 = $this->getclass(31,'usestatus');
        $this->assign('c31',$c31);
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
            $where = 'where `my_buyingmodofcustomer`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_buyingmodofcustomer`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_buyingmodofcustomer`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['payingordersid'] <> ''){$where .= " `my_buyingmodofcustomer`.`payingordersid` = ".$_POST['payingordersid']." and ";}if($_POST['servicevoucherid'] <> ''){$where .= " `my_buyingmodofcustomer`.`servicevoucherid` = ".$_POST['servicevoucherid']." and ";}if($_POST['redeemcodeid'] <> ''){$where .= " `my_buyingmodofcustomer`.`redeemcodeid` = ".$_POST['redeemcodeid']." and ";}if($_POST['usestatus'] <> ''){$where .= " `my_buyingmodofcustomer`.`usestatus` = ".$_POST['usestatus']." and ";}
        $where = trim($where,' and');
    	$sql = "select `my_buyingmodofcustomer`.`id`,`my_buyingmodofcustomer`.`remark`,`my_buyingmodofcustomer`.`usestatus`,`my_buyingmodofcustomer`.`credit`,`my_buyingmodofcustomer`.`createtime`,`my_buyingmodofcustomer`.`updatetime`,`my_buyingmodofcustomer`.`payingordersid`,`my_payingorders`.`ordernum`,`my_servicevoucher`.`name`,`my_redeemcode`.`code` from `my_buyingmodofcustomer`  left join `my_payingorders` on `my_payingorders`.`id` = `my_buyingmodofcustomer`.`payingordersid` left join `my_servicevoucher` on `my_servicevoucher`.`id` = `my_buyingmodofcustomer`.`servicevoucherid` left join `my_redeemcode` on `my_redeemcode`.`id` = `my_buyingmodofcustomer`.`redeemcodeid` ".$where." order by `my_buyingmodofcustomer`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['usestatus'] = $this->getclassname($value['usestatus']);}
        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('buyingmodofcustomer',array('ID','简介','使用状态','对应积分','创建时间','更新时间','订单ID','订单号','服务券名称','兑换码'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }
    //
    public function add(){
        $c31 = $this->getclass(31,'usestatus');
        $this->assign('c31',$c31);
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
        $buyingmodofcustomer = M('my_buyingmodofcustomer');
        $buyingmodofcustomer->add($_POST);
        echo 'success';
    }
    //@@@@订单详情修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $buyingmodofcustomer = M('my_buyingmodofcustomer');
        $where['id'] = $id;
        $result = $buyingmodofcustomer->where($where)->find();
        $c31 = $this->getclass(31,'usestatus',$result['usestatus']);
        $this->assign('c31',$c31);

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
        
    	$buyingmodofcustomer = M('my_buyingmodofcustomer');
        $where['id'] = $id;
        $buyingmodofcustomer->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $buyingmodofcustomer = M('my_buyingmodofcustomer');
        $where['id'] = $id;
        $result = $buyingmodofcustomer->where($where)->find();
        $c31 = $this->getclass(31,'usestatus',$result['usestatus']);
        $this->assign('c31',$c31);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@订单详情删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$buyingmodofcustomer = M('my_buyingmodofcustomer');
        $buyingmodofcustomer->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$buyingmodofcustomer = M('my_buyingmodofcustomer');
        $where['id'] = array('in',$idlist);
        $buyingmodofcustomer->where($where)->delete();
        echo 'success';
    }

}