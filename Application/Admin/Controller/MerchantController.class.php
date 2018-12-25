<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$商户管理-merchant$$$$
class MerchantController extends BaseController {
    //####商户列表-index####
    public function index(){
        $c2 = $this->getclass(2,'type');
        $this->assign('c2',$c2);$c7 = $this->getclass(7,'status');
        $this->assign('c7',$c7);$c13 = $this->getclass(13,'operation');
        $this->assign('c13',$c13);
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
            $where = 'where `my_merchant`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_merchant`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_merchant`.`createtime`)<='".$endtime."' and";
        }
        $where .= " `my_merchant`.`name` like '%".$_POST['name']."%' and ";if($_POST['type'] <> ''){$where .= " `my_merchant`.`type` = ".$_POST['type']." and ";}if($_POST['operation'] <> ''){$where .= " `my_merchant`.`operation` = ".$_POST['operation']." and ";}
        $where = trim($where,' and');
    	$sql = "select `my_merchant`.`id`,`my_merchant`.`merchantnum`,`my_merchant`.`name`,`my_merchant`.`fullname`,`my_merchant`.`address`,`my_merchant`.`phone`,`my_merchant`.`landline`,`my_merchant`.`coordinate`,`my_merchant`.`type`,`my_merchant`.`scale`,`my_merchant`.`introduction`,`my_merchant`.`status`,`my_merchant`.`balance`,`my_merchant`.`total`,`my_merchant`.`operation`,`my_merchant`.`logo`,`my_merchant`.`shjyqtotal`,`my_merchant`.`shczktotal`,`my_merchant`.`fwktotal`,`my_merchant`.`jfktotal`,`my_merchant`.`createtime`,`my_merchant`.`updatetime` from `my_merchant`  ".$where." order by `my_merchant`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);

        

        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['type'] = $this->getclassname($value['type']);$result['rows'][$key]['status'] = $this->getclassname($value['status']);$result['rows'][$key]['operation'] = $this->getclassname($value['operation']);}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('merchant',array('ID','商户号','商户名称','商户全称','商户地址','电话','座机','坐标','商家类别','商家规模','商家简介','商户状态','余额','总额','运营状态','商户logo','石化加油券','石化充值卡','服务卡','积分卡','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    

    

    //@@@@商户列表添加-add@@@@
    public function add(){
        $c2 = $this->getclass(2,'type');
        $this->assign('c2',$c2);$c7 = $this->getclass(7,'status');
        $this->assign('c7',$c7);$c13 = $this->getclass(13,'operation');
        $this->assign('c13',$c13);
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
        if($info['logo']){$_POST['logo'] = '/upload/'.$info['logo']['savepath'].$info['logo']['savename'];}

        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        $_POST['adminid'] = $_SESSION['id'];
        $merchant = D('Merchant');
		if(!$merchant->create($_POST)){
			exit($merchant->getError());
		}else{
			$merchant->add();
			echo 'success';
		}
    }
    //@@@@商户列表修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $merchant = M('my_merchant');
        $where['id'] = $id;
        $result = $merchant->where($where)->find();
        $c2 = $this->getclass(2,'type',$result['type']);
        $this->assign('c2',$c2);$c7 = $this->getclass(7,'status',$result['status']);
        $this->assign('c7',$c7);$c13 = $this->getclass(13,'operation',$result['operation']);
        $this->assign('c13',$c13);

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
        if($info['logo']){$_POST['logo'] = '/upload/'.$info['logo']['savepath'].$info['logo']['savename'];}
    	$merchant = M('my_merchant');
        $where['id'] = $id;
        $merchant->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $merchant = M('my_merchant');
        $where['id'] = $id;
        $result = $merchant->where($where)->find();
        $c2 = $this->getclass(2,'type',$result['type']);
        $this->assign('c2',$c2);$c7 = $this->getclass(7,'status',$result['status']);
        $this->assign('c7',$c7);$c13 = $this->getclass(13,'operation',$result['operation']);
        $this->assign('c13',$c13);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@商户列表删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$merchant = M('my_merchant');
        $merchant->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$merchant = M('my_merchant');
        $where['id'] = array('in',$idlist);
        $merchant->where($where)->delete();
        echo 'success';
    }

}