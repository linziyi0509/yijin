<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$服务券管理-servicevoucher$$$$
class ServicevoucherController extends BaseController {
    //####服务券列表-index####
    public function index(){
        $c91 = $this->getclass(91,'type');
        $this->assign('c91',$c91);
        $c87 = $this->getclass(87,'istransfer');
        $this->assign('c87',$c87);
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
            if($_POST['type'] != 'search'){
                $where = 'where `my_servicevoucher`.`adminid`='.$_SESSION['adminid'].' and ';
            }else{
                $where = 'where 1=1 and ';
            }
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_servicevoucher`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_servicevoucher`.`createtime`)<='".$endtime."' and";
        }
        $where .= " `my_servicevoucher`.`name` like '%".$_POST['name']."%' and ";
        $where = trim($where,' and');
    	$sql = "select `my_servicevoucher`.`id`,`my_servicevoucher`.`name`,`my_servicevoucher`.`integral`,`my_servicevoucher`.`instructions`,`my_servicevoucher`.`transfer`,`my_servicevoucher`.`type`,`my_servicevoucher`.`istransfer`,`my_servicevoucher`.`money`,`my_servicevoucher`.`starttime`,`my_servicevoucher`.`endtime`,`my_servicevoucher`.`icon`,`my_servicevoucher`.`exchangeurl`,`my_servicevoucher`.`createtime`,`my_servicevoucher`.`updatetime` from `my_servicevoucher`  ".$where." order by `my_servicevoucher`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        foreach ($result['rows'] as $key => $value) {
            $result['rows'][$key]['type'] = $this->getclassname($value['type']);
            $result['rows'][$key]['istransfer'] = $this->getclassname($value['istransfer']);
        }
        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('servicevoucher',array('ID','服务券名称','对应积分','使用说明','转让说明','服务券类型','是否可转让','金额','开始时间','结束时间','图标','兑换网址','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    

    

    //@@@@服务券列表添加-add@@@@
    public function add(){
        $c91 = $this->getclass(91,'type');
        $this->assign('c91',$c91);
        $c87 = $this->getclass(87,'istransfer');
        $this->assign('c87',$c87);
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
        if($info['icon']){$_POST['icon'] = '/upload/'.$info['icon']['savepath'].$info['icon']['savename'];}

        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        $_POST['adminid'] = $_SESSION['id'];
        $servicevoucher = M('my_servicevoucher');
        $servicevoucher->add($_POST);
        echo 'success';
    }
    //@@@@服务券列表修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $servicevoucher = M('my_servicevoucher');
        $where['id'] = $id;
        $result = $servicevoucher->where($where)->find();
        $c91 = $this->getclass(91,'type',$result['type']);
        $this->assign('c91',$c91);
        $c87 = $this->getclass(87,'istransfer',$result['istransfer']);
        $this->assign('c87',$c87);

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
        if($info['icon']){$_POST['icon'] = '/upload/'.$info['icon']['savepath'].$info['icon']['savename'];}
    	$servicevoucher = M('my_servicevoucher');
        $where['id'] = $id;
        $servicevoucher->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $servicevoucher = M('my_servicevoucher');
        $where['id'] = $id;
        $result = $servicevoucher->where($where)->find();
        $c91 = $this->getclass(91,'type',$result['type']);
        $this->assign('c91',$c91);
        $c87 = $this->getclass(87,'istransfer',$result['istransfer']);
        $this->assign('c87',$c87);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@服务券列表删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$servicevoucher = M('my_servicevoucher');
        $servicevoucher->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$servicevoucher = M('my_servicevoucher');
        $where['id'] = array('in',$idlist);
        $servicevoucher->where($where)->delete();
    }

}