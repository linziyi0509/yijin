<?php
namespace Admin\Controller;
use Admin\Model\RedeemcodeModel;
use Think\Controller;
use PHPExcel_IOFactory;
use PHPExcel;
//$$$$服务券管理-redeemcode$$$$
class RedeemcodeController extends BaseController {
    //####兑换码列表-index####
    public function index(){
        $c22 = $this->getclass(22,'status');
        $this->assign('c22',$c22);$c25 = $this->getclass(25,'usestatus');
        $this->assign('c25',$c25);
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
            $where = 'where `my_redeemcode`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_redeemcode`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_redeemcode`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['servicevoucherid'] <> ''){$where .= " `my_redeemcode`.`servicevoucherid` = ".$_POST['servicevoucherid']." and ";}$where .= " `my_redeemcode`.`servername` like '%".$_POST['servername']."%' and ";$where .= " `my_redeemcode`.`code` like '%".$_POST['code']."%' and ";if($_POST['status'] <> ''){$where .= " `my_redeemcode`.`status` = ".$_POST['status']." and ";}
        $where = trim($where,' and');
    	$sql = "select `my_redeemcode`.`id`,`my_redeemcode`.`servername`,`my_redeemcode`.`code`,`my_redeemcode`.`status`,`my_redeemcode`.`usestatus`,`my_redeemcode`.`createtime`,`my_redeemcode`.`updatetime`,`my_servicevoucher`.`name` from `my_redeemcode`  left join `my_servicevoucher` on `my_servicevoucher`.`id` = `my_redeemcode`.`servicevoucherid` ".$where." order by `my_redeemcode`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['status'] = $this->getclassname($value['status']);$result['rows'][$key]['usestatus'] = $this->getclassname($value['usestatus']);}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('redeemcode',array('ID','服务名称','兑换码','兑换码状态','兑换码使用状态','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }
    //@@@@兑换码列表添加-add@@@@
    public function add(){
        $c22 = $this->getclass(22,'status');
        $this->assign('c22',$c22);$c25 = $this->getclass(25,'usestatus');
        $this->assign('c25',$c25);
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
        $redeemcode = M('my_redeemcode');
        $redeemcode->add($_POST);
        echo 'success';
    }
    //@@@@兑换码列表修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $redeemcode = M('my_redeemcode');
        $where['id'] = $id;
        $result = $redeemcode->where($where)->find();
        $c22 = $this->getclass(22,'status',$result['status']);
        $this->assign('c22',$c22);$c25 = $this->getclass(25,'usestatus',$result['usestatus']);
        $this->assign('c25',$c25);

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
        
    	$redeemcode = M('my_redeemcode');
        $where['id'] = $id;
        $redeemcode->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $redeemcode = M('my_redeemcode');
        $where['id'] = $id;
        $result = $redeemcode->where($where)->find();
        $c22 = $this->getclass(22,'status',$result['status']);
        $this->assign('c22',$c22);$c25 = $this->getclass(25,'usestatus',$result['usestatus']);
        $this->assign('c25',$c25);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@兑换码列表删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$redeemcode = M('my_redeemcode');
        $redeemcode->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$redeemcode = M('my_redeemcode');
        $where['id'] = array('in',$idlist);
        $redeemcode->where($where)->delete();
        echo 'success';
    }

    /**
     * 从excel中导入兑换码
     * 1.上传有规则的excel
     * 2.从excel中读取所有的数据
     * 3.验证数据并将数据写入到数据库
     */
    public function import(){
        Vendor('PHPExcel.PHPExcel');
        Vendor('PHPExcel.PHPExcel.IOFactory');
        Vendor('PHPExcel.PHPExcel.Reader.Excel5');
        Vendor('PHPExcel.PHPExcel.Reader.Excel2007');
        if(IS_POST){
            $upload = new \Think\Upload();// 实例化上传类
            $upload->maxSize = 3145728;
            $upload->rootPath = './upload/';
            $upload->savePath = '';
            $upload->saveName = array('uniqid','');//uniqid函数生成一个唯一的字符串序列。
            $upload->exts = array('xlsx', 'xls');
            $upload->autoSub = true;
            $upload->subName = array('date','Ymd');
            $info = $upload->upload();
            if(!empty($info)){
                $codefile = '';
                foreach ($info as $key => $value) {
                    if($value['key']){
                        $codefile = '/upload/'.$value['savepath'].$value['savename'];
                    }
                }
                $PHPReader = new \PHPExcel_Reader_Excel2007();
                $filePath = BASEPATH.$codefile;
                if( ! $PHPReader->canRead($filePath))
                {
                    $PHPReader = new \PHPExcel_Reader_Excel5();
                    if( ! $PHPReader->canRead($filePath)){
                        echo 'no Excel';
                        return '';
                    }
                }
                $PHPExcel = $PHPReader->load($filePath); //读取文件
                $sheet = $PHPExcel->getSheet(0);//取得sheet(0)表
                $highestRow = $sheet->getHighestRow(); // 取得总行数
                $highestColumn = $sheet->getHighestColumn(); // 取得总列数
                $j = 0;
                for($i=3;$i<=$highestRow;$i++)
                {
                    $data[$j]['servername'] = (string)$PHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
                    $data[$j]['code'] = (string)$PHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
                    $data[$j]['status'] = 23;//类别-冻结
                    $data[$j]['usestatus'] = 27;//使用状态-未使用
                    $data[$j]['createtime'] = date("Y-m-d H:i:s",time());//使用状态-未使用
                    $data[$j]['adminid'] = $_SESSION['id'];//使用状态-未使用
                    $j++;
                }
                $m = D("my_redeemcode"); // 打开表
                $res = $m->addAll($data); // 批量插入
                //删除上传的文件
                unlink($filePath);
                if($res){
                    echo 'success';
                }else{
                    echo '导入失败,查看数据';
                }
            }else{
                echo '导入失败,查看文件后缀名及数据';
            }
        }else{
            $this->display();
        }
    }
    public function active(){
        if(IS_POST){
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
                    $arr['servicevoucherid'] = $_POST['servicevoucherid'];
                    $arr['status'] = 24;
                    $arr['updatetime'] = date('Y-m-d H:i:s',time());
                    $data[] = $arr;
                }
                $res = batch_update('my_redeemcode',$data,'id');
                if($res){
                    exit('success');
                }else{
                    exit('激活失败，请查看数据');
                }
            }else{
                exit('激活失败，请查看数据');
            }
        }else{
            $redeemcode = M('my_redeemcode');
            $data['idlist'] = $_GET['idlist'];
            $where['id']  = array('in',$data['idlist']);
            $where['_string'] = 'status = 24 or usestatus = 26';
            $dataRed = $redeemcode->where($where)->select();
            if(!empty($dataRed)){
                $data['flag'] = 1;
            }else{
                $data['flag'] = 0;
            }
            //服务券信息
            $data['dataServicevoucher'] = M('my_servicevoucher')->field("id,name")->select();
            $condition['status'] = 23;
            $condition['usestatus'] = 27;
            $condition['id'] = ['in',$data['idlist']];
            $data['dataRedeemcode'] = $redeemcode->field("id,servername,code")->where($condition)->select();
            $this->assign('data',$data);
            $this->display();
        }
    }
}
