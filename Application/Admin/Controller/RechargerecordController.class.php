<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Exception;

//$$$$商户管理-rechargerecord$$$$
class RechargerecordController extends BaseController {
    //####充值记录-index####
    public function index(){
        $c10 = $this->getclass(10,'status');
        $this->assign('c10',$c10);
        $c91 = $this->getclass(91,'type');
        $this->assign('c91',$c91);
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
            $where = 'where `my_rechargerecord`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_rechargerecord`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_rechargerecord`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['merchantid'] <> ''){$where .= " `my_rechargerecord`.`merchantid` = ".$_POST['merchantid']." and ";}
        if($_POST['status'] <> ''){$where .= " `my_rechargerecord`.`status` = ".$_POST['status']." and ";}
        if($_POST['merchantname'] <> ''){$where .= " `my_merchant`.`name` like '".$_POST['merchantname']."' and ";}
        $where = trim($where,' and');
    	$sql = "select `my_rechargerecord`.`id`,`my_rechargerecord`.`amount`,`my_rechargerecord`.`batch`,`my_merchant`.`name`,`my_rechargerecord`.`status`,`my_rechargerecord`.`remark`,`my_rechargerecord`.`type`,`my_rechargerecord`.`createtime`,`my_rechargerecord`.`updatetime` from `my_rechargerecord`  left join `my_merchant` on `my_merchant`.`id` = `my_rechargerecord`.`merchantid` ".$where." order by `my_rechargerecord`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        foreach ($result['rows'] as $key => $value) {
            $result['rows'][$key]['status'] = $this->getclassname($value['status']);
            $result['rows'][$key]['type'] = $this->getclassname($value['type']);
        }
        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('rechargerecord',array('ID','交易金额(元)','交易流水号','对应商户','充值状态','备注','充值类型','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }
    //@@@@充值记录添加-add@@@@
    public function add(){
        $dataMerchant = M('my_merchant')->field("id,name")->where("status",8)->select();
        $c10 = $this->getclass(10,'status');
        $this->assign('c10',$c10);
        $c91 = $this->getclass(91,'type');
        $this->assign('c91',$c91);
        $this->assign('dataMerchant',$dataMerchant);
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
		try{
			$rechargerecord = D('Rechargerecord');
            if(!$rechargerecord->create($_POST)){
                exit($rechargerecord->getError());
            }else{
                $rechargerecord->add();
            }
            $merchantid = $_POST['merchantid'];
			if($_POST['status'] == 11){//充值状态为成功
                if(empty($_POST['type'])){
                    $_POST['type'] = 94;
                }
                $type = serviceType($_POST['type']);
				//------开始--------这里需要做总结和余额的计算
				$condition['merchantid'] = $merchantid;
				$condition['status'] = 11;
                $total = $rechargerecord->where($condition)
                    ->field("sum(`amount`) as `amounts`")->find();
                $merchant = M("my_merchant");
                $merchantRes = $merchant->where("id=".$merchantid)->find();
                $merchantData["total"] = $total["amounts"];
                $merchantData["balance"] = $merchantRes["balance"]+$total["amounts"]-$merchantRes["total"];
                //------------结束-------------
                //---开始 单个类型的总额---
                $where['merchantid'] = $merchantid;
                $where['status'] = 11;
                $where['type'] = $_POST['type'];
                $typetotal = $rechargerecord->where($where)
                    ->field("sum(`amount`) as `total`")->find();
				$merchantData[$type] = $typetotal["total"];
				$merchantData[str_replace('total','balance',$type)] = $merchantRes[str_replace('total','balance',$type)] + $typetotal["total"] - $merchantRes[$type];
                //---结束---
				$merchant->where("id=".$merchantid)->save($merchantData);
                $merchant->getlastsql();
			}
			echo 'success';
		}catch(Exception $e){
			exit($e->getMessage());
		}
    }
    //@@@@充值记录修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $rechargerecord = M('my_rechargerecord');
        $where['id'] = $id;
        $result = $rechargerecord->where($where)->find();
        $c10 = $this->getclass(10,'status',$result['status']);
        $this->assign('c10',$c10);
        $c91 = $this->getclass(91,'type',$result['type']);
        $this->assign('c91',$c91);
        $dataMerchant = M('my_merchant')->field("id,name")->where("status",8)->select();
        $this->assign('dataMerchant',$dataMerchant);
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

    	$rechargerecord = M('my_rechargerecord');
        $where['id'] = $id;
        $rechargerecord->where($where)->save($_POST);
        $merchantid = $_POST['merchantid'];
        //总金额和余额的修改
        if($_POST['status'] == 11){//充值状态为成功
            if(empty($_POST['type'])){
                $_POST['type'] = 94;
            }
            $type = serviceType($_POST['type']);
            //这里需要做总结和余额的计算
            $condition['merchantid'] = $merchantid;
            $condition['status'] = 11;
            $total = $rechargerecord->where($condition)
            ->field("sum(`amount`) as `amounts`")->find();
            $merchant = M("my_merchant");
            $merchantRes = $merchant->where("id=".$merchantid)->find();
            $merchantData["total"] = $total["amounts"];
            $merchantData["balance"] = $merchantRes["balance"]+$total["amounts"]-$merchantRes["total"];
            //---开始 单个类型的总额---
            $where['merchantid'] = $merchantid;
            $where['status'] = 11;
            $where['type'] = $_POST['type'];
            $typetotal = $rechargerecord->where($where)
                ->field("sum(`amount`) as `total`")->find();
            $merchantData[$type] = $typetotal["total"];
            //---结束---
            $merchant->where("id=".$merchantid)->save($merchantData);
            writelog("sql",$merchant->getlastsql());
            writelog("POST:",$_POST);
        }
        echo 'success';
    }
    public function xiangqing(){
        $id = $_GET['id']; 
        $rechargerecord = M('my_rechargerecord');
        $where['id'] = $id;
        $result = $rechargerecord->where($where)->find();
        $c10 = $this->getclass(10,'status',$result['status']);
        $this->assign('c10',$c10);
        $c91 = $this->getclass(91,'type',$result['type']);
        $this->assign('c91',$c91);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@充值记录删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$rechargerecord = M('my_rechargerecord');
        $rechargerecord->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$rechargerecord = M('my_rechargerecord');
        $where['id'] = array('in',$idlist);
        $rechargerecord->where($where)->delete();
        echo 'success';
    }
}