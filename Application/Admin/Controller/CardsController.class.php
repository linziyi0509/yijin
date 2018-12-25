<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$卡券管理-cards$$$$
class CardsController extends BaseController {
    //####卡片详情-index####
    public function index(){
        $c28 = $this->getclass(28,'status');
        $this->assign('c28',$c28);$c31 = $this->getclass(31,'usestatus');
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
            $where = 'where `my_cards`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_cards`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_cards`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['wechatuserid'] <> '')
        {
            $where .= " `my_cards`.`wechatuserid` = ".$_POST['wechatuserid']." and ";
        }
        if($_POST['cardcouponsid'] <> ''){
            $where .= " `my_cards`.`cardcouponsid` = ".$_POST['cardcouponsid']." and ";
        }
        if($_POST['merchantid'] <> ''){
            $where .= " `my_cards`.`merchantid` = ".$_POST['merchantid']." and ";
        }
        $where .= " `my_cards`.`cardno` like '%".$_POST['cardno']."%' and ";
        if($_POST['status'] <> ''){
            $where .= " `my_cards`.`status` = ".$_POST['status']." and ";
        }
        if($_POST['usestatus'] <> ''){
            $where .= " `my_cards`.`usestatus` = ".$_POST['usestatus']." and ";
        }
        if($_POST['username'] <> ''){
            $where .= " `my_wechatuser`.`nickname` like '".$_POST['username']."' and ";
        }
        if($_POST['phone'] <> ''){
            $where .= " `my_wechatuser`.`phone` like '".$_POST['phone']."' and ";
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
        $where = trim($where,' and');
    	$sql = "select `my_cards`.`id`,`my_cards`.`cardno`,`my_cards`.`cardpwd`,`my_cards`.`status`,`my_cards`.`usestatus`,`my_cards`.`createtime`,`my_cards`.`updatetime`,`my_wechatuser`.`nickname`,`my_wechatuser`.`phone`,`my_cardcoupons`.`name`,`my_merchant`.`name` as `mname` from `my_cards`  left join `my_wechatuser` on `my_wechatuser`.`id` = `my_cards`.`wechatuserid` left join `my_cardcoupons` on `my_cardcoupons`.`id` = `my_cards`.`cardcouponsid` left join `my_merchant` on `my_merchant`.`id` = `my_cards`.`merchantid` ".$where." order by `my_cards`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['status'] = $this->getclassname($value['status']);$result['rows'][$key]['usestatus'] = $this->getclassname($value['usestatus']);}
        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('cards',array('ID','卡号','卡密','卡号状态','使用状态','创建时间','更新时间','用户名','手机号','对应卡券','对应商户'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }
    //@@@@卡片详情添加-add@@@@
    public function add(){
        $c28 = $this->getclass(28,'status');
        $this->assign('c28',$c28);$c31 = $this->getclass(31,'usestatus');
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
        $cards = M('my_cards');
        $cards->add($_POST);
        echo 'success';
    }
    //@@@@卡片详情修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $cards = M('my_cards');
        $where['id'] = $id;
        $result = $cards->where($where)->find();
        $c28 = $this->getclass(28,'status',$result['status']);
        $this->assign('c28',$c28);$c31 = $this->getclass(31,'usestatus',$result['usestatus']);
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
        
    	$cards = M('my_cards');
        $where['id'] = $id;
        $cards->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $cards = M('my_cards');
        $where['id'] = $id;
        $result = $cards->where($where)->find();
        $c28 = $this->getclass(28,'status',$result['status']);
        $this->assign('c28',$c28);$c31 = $this->getclass(31,'usestatus',$result['usestatus']);
        $this->assign('c31',$c31);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@卡片详情删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$cards = M('my_cards');
        $cards->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$cards = M('my_cards');
        $where['id'] = array('in',$idlist);
        $cards->where($where)->delete();
        echo 'success';
    }

    /**
     * 激活-冻结
     */
    public function activefrozen(){
        if(IS_POST){
            $afFlag = $_POST["afFlag"];
            if($afFlag == 'active'){
                $idlist = $_POST["idlist"];
                if($idlist){
                    //判断是否包含逗号 也就是判断是否为多个数据
                    if(strrpos($idlist,',')){
                        $idlistArr = explode(',',$idlist);
                    }else{
                        $idlistArr = [$idlist];
                    }
                    $data = [];$count = count($idlistArr);
                    for($i=0;$i<$count;$i++){
                        $arr['id'] = $idlistArr[$i];
                        $arr['merchantid'] = $_POST['merchantid'];
                        $arr['status'] = 30;
                        $arr['updatetime'] = date('Y-m-d H:i:s',time());
                        $data[] = $arr;
                    }
                    //批量修改激活状态和激活给具体的商户
                    $res = batch_update('my_cards',$data,'id');
                    if($res){
                        exit('success');
                    }else{
                        exit('激活失败，请查看数据');
                    }
                }else{
                    exit('激活失败，请查看数据');
                }
            }else if($afFlag == 'frozen'){//冻结
                $idlist = $_POST["idlist"];
                if($idlist){
                    //判断是否包含逗号 也就是判断是否为多个数据
                    if(strrpos($idlist,',')){
                        $idlistArr = explode(',',$idlist);
                    }else{
                        $idlistArr = [$idlist];
                    }
                    $data = [];
                    $count = count($idlistArr);
                    for($i=0;$i<$count;$i++){
                        $arr['id'] = $idlistArr[$i];
//                        $arr['merchantid'] = $_POST['merchantid'];
                        $arr['status'] = 29;
                        $arr['updatetime'] = date('Y-m-d H:i:s',time());
                        $data[] = $arr;
                    }
                    //批量修改激活状态和激活给具体的商户
                    $res = batch_update('my_cards',$data,'id');
                    if($res){
                        exit('success');
                    }else{
                        exit('冻结失败，请查看数据');
                    }
                }else{
                    exit('冻结失败，请查看数据');
                }
            }
        }else{
            $cards = M('my_cards');
            $afFlag = $_GET["afFlag"];
            if($afFlag == 'active'){
                $where['_string'] = 'status = 29 and usestatus = 33';
                $condition['status'] = 29;
                $condition['usestatus'] = 33;
            }else if($afFlag == 'frozen'){
                $where['_string'] = 'status = 30 and usestatus = 33';
                $condition['status'] = 30;
                $condition['usestatus'] = 33;
            }
            $data['idlist'] = $_GET['idlist'];
            $where['id']  = array('in',$data['idlist']);
            $dataCard = $cards->where($where)->select();
            if(empty($dataCard)){
                $data['flag'] = 1;
            }else{
                $data['flag'] = 0;
            }
            //商家信息
            $data['dataMerchat'] = M('my_merchant')->field("id,name")->select();
            $condition['id'] = ['in',$data['idlist']];
            $data['dataCard'] = $cards->field("id,cardno")->where($condition)->select();
            $data['afFlag'] = $afFlag;
            $this->assign('data',$data);
            writelog('data:',$data);
            $this->display();
        }
    }

}