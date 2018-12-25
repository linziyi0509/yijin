<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$发券管理-policyservicevoucher$$$$
class PolicyservicevoucherController extends BaseController {
    //####保单服务券关联-index####
    public function index(){
        $c31 = $this->getclass(31,'usestatus');
        $this->assign('c31',$c31);$c19 = $this->getclass(19,'status');
        $this->assign('c19',$c19);$c71 = $this->getclass(71,'issynchrogrant');
        $this->assign('c71',$c71);$c78 = $this->getclass(78,'isusergrantsales');
        $this->assign('c78',$c78);
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
            $where = 'where `my_policyservicevoucher`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_policyservicevoucher`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_policyservicevoucher`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['wechatuserid'] <> ''){
            $where .= " `my_policyservicevoucher`.`wechatuserid` = ".$_POST['wechatuserid']." and ";
        }
        if($_POST['policyid'] <> ''){
            $where .= " `my_policyservicevoucher`.`policyid` = ".$_POST['policyid']." and ";
        }
        if($_POST['servicevoucherid'] <> ''){
            $where .= " `my_policyservicevoucher`.`servicevoucherid` = ".$_POST['servicevoucherid']." and ";
        }
        if($_POST['petroinfoid'] <> ''){
            $where .= " `my_policyservicevoucher`.`petroinfoid` = ".$_POST['petroinfoid']." and ";
        }
        if($_POST['usestatus'] <> ''){
            $where .= " `my_policyservicevoucher`.`usestatus` = ".$_POST['usestatus']." and ";
        }
        if($_POST['status'] <> ''){
            $where .= " `my_policyservicevoucher`.`status` = ".$_POST['status']." and ";
        }
        if($_POST['issynchrogrant'] <> ''){
            $where .= " `my_policyservicevoucher`.`issynchrogrant` = ".$_POST['issynchrogrant']." and ";
        }
        if($_POST['isusergrantsales'] <> ''){
            $where .= " `my_policyservicevoucher`.`isusergrantsales` = ".$_POST['isusergrantsales']." and ";
        }
        if($_POST['oilcode'] <> ''){
            $where .= " `my_policyservicevoucher`.`oilcode` like '%".$_POST['oilcode']."%' and ";
        }
        //保单号
        if($_POST['policynumber'] <> ''){
            $where .= " `my_policy`.`policynumber` like '%".$_POST['policynumber']."%' and ";
        }
        //用户昵称
        if($_POST['nickname'] <> ''){
            $where .= " `my_wechatuser`.`nickname` like '%".$_POST['nickname']."%' and ";
        }
        //商户
        if($_POST['mname'] <> ''){
            $where .= " `my_merchant`.`name` like '%".$_POST['mname']."%' and ";
        }
        $where = trim($where,' and');
    	$sql = "select `my_policyservicevoucher`.`id`,`my_policyservicevoucher`.`usetime`,`my_policyservicevoucher`.`usestatus`,`my_policyservicevoucher`.`status`,`my_policyservicevoucher`.`issynchrogrant`,`my_policyservicevoucher`.`isusergrantsales`,`my_policyservicevoucher`.`starttime`,`my_policyservicevoucher`.`endtime`,`my_policyservicevoucher`.`oilcode`,`my_policyservicevoucher`.`createtime`,`my_policyservicevoucher`.`updatetime`,`my_servicevoucher`.`name`,`my_servicevoucher`.`money`,`my_servicevoucher`.`integral`,`my_policy`.`policynumber`,`my_wechatuser`.`nickname`,`my_merchant`.`name` as `mname` from `my_policyservicevoucher`  left join `my_wechatuser` on `my_wechatuser`.`id` = `my_policyservicevoucher`.`wechatuserid` left join `my_policy` on `my_policy`.`id` = `my_policyservicevoucher`.`policyid` left join `my_servicevoucher` on `my_servicevoucher`.`id` = `my_policyservicevoucher`.`servicevoucherid` left join `my_petroinfo` on `my_petroinfo`.`id` = `my_policyservicevoucher`.`petroinfoid` left join `my_merchant` on `my_merchant`.`id` = `my_policyservicevoucher`.`merchantid` ".$where." order by `my_policyservicevoucher`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['usestatus'] = $this->getclassname($value['usestatus']);$result['rows'][$key]['status'] = $this->getclassname($value['status']);$result['rows'][$key]['issynchrogrant'] = $this->getclassname($value['issynchrogrant']);$result['rows'][$key]['isusergrantsales'] = $this->getclassname($value['isusergrantsales']);}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('policyservicevoucher',array('ID','使用时间','使用状态','是否激活','是否同步授权','用户授权营销员','开始时间','结束时间','石油码','创建时间','更新时间','服务券名称','金额','对应积分','保单号','用户昵称','商户'),$result['rows']);
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
        $this->assign('c19',$c19);$c71 = $this->getclass(71,'issynchrogrant');
        $this->assign('c71',$c71);$c78 = $this->getclass(78,'isusergrantsales');
        $this->assign('c78',$c78);
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
        $policyservicevoucher = M('my_policyservicevoucher');
        $policyservicevoucher->add($_POST);
        echo 'success';
    }
    //@@@@保单服务券关联修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $policyservicevoucher = M('my_policyservicevoucher');
        $where['id'] = $id;
        $result = $policyservicevoucher->where($where)->find();
        $c31 = $this->getclass(31,'usestatus',$result['usestatus']);
        $this->assign('c31',$c31);$c19 = $this->getclass(19,'status',$result['status']);
        $this->assign('c19',$c19);$c71 = $this->getclass(71,'issynchrogrant',$result['issynchrogrant']);
        $this->assign('c71',$c71);$c78 = $this->getclass(78,'isusergrantsales',$result['isusergrantsales']);
        $this->assign('c78',$c78);

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
        
    	$policyservicevoucher = M('my_policyservicevoucher');
        $where['id'] = $id;
        $policyservicevoucher->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $policyservicevoucher = M('my_policyservicevoucher');
        $where['id'] = $id;
        $result = $policyservicevoucher->where($where)->find();
        $c31 = $this->getclass(31,'usestatus',$result['usestatus']);
        $this->assign('c31',$c31);$c19 = $this->getclass(19,'status',$result['status']);
        $this->assign('c19',$c19);$c71 = $this->getclass(71,'issynchrogrant',$result['issynchrogrant']);
        $this->assign('c71',$c71);$c78 = $this->getclass(78,'isusergrantsales',$result['isusergrantsales']);
        $this->assign('c78',$c78);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@保单服务券关联删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$policyservicevoucher = M('my_policyservicevoucher');
        $policyservicevoucher->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$policyservicevoucher = M('my_policyservicevoucher');
        $where['id'] = array('in',$idlist);
        $policyservicevoucher->where($where)->delete();
        echo 'success';
    }

}