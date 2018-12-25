<?php
namespace Admin\Controller;
use Admin\Model\OperablerelationModel;
use Think\Controller;
//$$$$营销员管理-salesmaninfo$$$$
class SalesmaninfoController extends BaseController {
    //####列表-index####
    public function index(){
        $c62 = $this->getclass(62,'personnelattribute');
        $this->assign('c62',$c62);$c65 = $this->getclass(65,'personnelstatus');
        $this->assign('c65',$c65);
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
            $model = new OperablerelationModel();
            $result = $model->userhavemerchat();
            $where = 'where `my_salesmaninfo`.`merchantid` in('.$result.') and ';
        }
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_salesmaninfo`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_salesmaninfo`.`createtime`)<='".$endtime."' and";
        }
        $where .= " `my_salesmaninfo`.`name` like '%".$_POST['name']."%' and ";
        $where .= " `my_salesmaninfo`.`phone` like '%".$_POST['phone']."%' and ";
        $where .= " `my_salesmaninfo`.`idcard` like '%".$_POST['idcard']."%' and ";
        if($_POST['personnelattribute'] <> ''){
            $where .= " `my_salesmaninfo`.`personnelattribute` = ".$_POST['personnelattribute']." and ";
        }
            if($_POST['personnelstatus'] <> ''){
                $where .= " `my_salesmaninfo`.`personnelstatus` = ".$_POST['personnelstatus']." and ";
            }
        $where = trim($where,' and');
    	$sql = "select `my_salesmaninfo`.`id`,`my_salesmaninfo`.`name`,`my_salesmaninfo`.`phone`,`my_salesmaninfo`.`idcard`,`my_salesmaninfo`.`personnelattribute`,`my_salesmaninfo`.`personnelstatus`,`my_salesmaninfo`.`idcardimg`,`my_salesmaninfo`.`remark`,`my_merchant`.`name` as `mname`,`my_salesmaninfo`.`createtime`,`my_salesmaninfo`.`updatetime` from `my_salesmaninfo`  left join `my_merchant` on `my_merchant`.`id` = `my_salesmaninfo`.`merchantid` left join `my_wechatuser` on `my_wechatuser`.`id` = `my_salesmaninfo`.`wechatuserid` ".$where." order by `my_salesmaninfo`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);

        

        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['personnelattribute'] = $this->getclassname($value['personnelattribute']);$result['rows'][$key]['personnelstatus'] = $this->getclassname($value['personnelstatus']);}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('salesmaninfo',array('ID','姓名','手机号','身份证号','人员属性','人员状态','身份证正面照','备注','所属商户','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    

    

    //@@@@列表添加-add@@@@
    public function add(){
        $c62 = $this->getclass(62,'personnelattribute');
        $this->assign('c62',$c62);$c65 = $this->getclass(65,'personnelstatus');
        $this->assign('c65',$c65);
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
        if($info['idcardimg']){$_POST['idcardimg'] = '/upload/'.$info['idcardimg']['savepath'].$info['idcardimg']['savename'];}

        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        $_POST['adminid'] = $_SESSION['id'];
        $_POST['merchantid'] = session('adminmerchantid');
        $salesmaninfo = M('my_salesmaninfo');
        if($salesmaninfo->add($_POST)){
            exit('success');
        }else{
            exit($salesmaninfo->getErrors());
        }
    }
    //@@@@列表修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $salesmaninfo = M('my_salesmaninfo');
        $where['id'] = $id;
        $result = $salesmaninfo->where($where)->find();
        $c62 = $this->getclass(62,'personnelattribute',$result['personnelattribute']);
        $this->assign('c62',$c62);$c65 = $this->getclass(65,'personnelstatus',$result['personnelstatus']);
        $this->assign('c65',$c65);

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
    	$salesmaninfo = M('my_salesmaninfo');
        $where['id'] = $id;
        $_POST['updatetime'] =date('Y-m-d H:i:s',time());
        $data = $salesmaninfo->where($where)->find();
        if(is_array($info)){
                if($info['idcardimg']){$_POST['idcardimg'] = '/upload/'.$info['idcardimg']['savepath'].$info['idcardimg']['savename'];}
        }else{
            $_POST['idcardimg'] = $data['idcardimg'];
        }
        $_POST['merchantid'] = session('adminmerchantid');
        if($salesmaninfo->where($where)->save($_POST)){
            exit('success');
        }else{
            exit($salesmaninfo->getErrors());
        }
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $salesmaninfo = M('my_salesmaninfo');
        $where['id'] = $id;
        $result = $salesmaninfo->where($where)->find();
        $c62 = $this->getclass(62,'personnelattribute',$result['personnelattribute']);
        $this->assign('c62',$c62);$c65 = $this->getclass(65,'personnelstatus',$result['personnelstatus']);
        $this->assign('c65',$c65);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@列表删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$salesmaninfo = M('my_salesmaninfo');
        $salesmaninfo->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$salesmaninfo = M('my_salesmaninfo');
        $where['id'] = array('in',$idlist);
        $salesmaninfo->where($where)->delete();
        echo 'success';
    }

}
