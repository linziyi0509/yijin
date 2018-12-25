<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Model;

//$$$$发券管理-petroinfo$$$$
class PetroinfoController extends BaseController {
    //####石化保单-index####
    public function index(){
        $c84 = $this->getclass(84,'moneytype');
        $this->assign('c84',$c84);$c81 = $this->getclass(81,'facevalue');
        $this->assign('c81',$c81);$c31 = $this->getclass(31,'usestatus');
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
        $disablestarttime = $_POST['disablestarttime'];
        $disableendtime = $_POST['disableendtime'];
        if($_SESSION['all'] == 1){
            $where = 'where 1=1 and ';
        }else{
            $where = 'where `my_petroinfo`.`adminid`='.$_SESSION['adminid'].' and ';
        }

        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_petroinfo`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_petroinfo`.`createtime`)<='".$endtime."' and";
        }
        if(!empty($disablestarttime)){
            $disablestarttime = strtotime($disablestarttime);
            $where .=" UNIX_TIMESTAMP(`my_petroinfo`.`disabletime`)>='".$disablestarttime."' and";
        }
        if(!empty($disableendtime)){
            $disableendtime = strtotime($disableendtime);
            $where .=" UNIX_TIMESTAMP(`my_petroinfo`.`disabletime`)<='".$disableendtime."' and";
        }
        if($_POST['petroreqinfoid'] <> ''){
            $where .= " `my_petroinfo`.`petroreqinfoid` = ".$_POST['petroreqinfoid']." and ";
        }
        $where .= " `my_petroinfo`.`typecode` like '%".$_POST['typecode']."%' and ";
        $where .= " `my_petroinfo`.`checkcode` like '%".$_POST['checkcode']."%' and ";
        $where .= " `my_petroinfo`.`typetitle` like '%".$_POST['typetitle']."%' and ";
        if($_POST['moneytype'] <> ''){
            $where .= " `my_petroinfo`.`moneytype` = ".$_POST['moneytype']." and ";
        }
        if($_POST['facevalue'] <> ''){
            $where .= " `my_petroinfo`.`facevalue` = ".$_POST['facevalue']." and ";
        }
        if($_POST['usestatus'] <> ''){
            $where .= " `my_petroinfo`.`usestatus` = ".$_POST['usestatus']." and ";
        }
        $where = trim($where,' and');
    	$sql = "select `my_petroinfo`.`id`,`my_petroinfo`.`typecode`,`my_petroinfo`.`checkcode`,`my_petroinfo`.`typetitle`,`my_petroinfo`.`moneytype`,`my_petroinfo`.`facevalue`,`my_petroinfo`.`enabletime`,`my_petroinfo`.`disabletime`,`my_petroinfo`.`imageurl`,`my_petroinfo`.`usedeclare`,`my_petroinfo`.`usestatus`,`my_petroinfo`.`vouchertype`,`my_petroinfo`.`createtime`,`my_petroinfo`.`updatetime` from `my_petroinfo`  left join `my_petroreqinfo` on `my_petroreqinfo`.`id` = `my_petroinfo`.`petroreqinfoid` ".$where." order by `my_petroinfo`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);

        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['moneytype'] = $this->getclassname($value['moneytype']);$result['rows'][$key]['facevalue'] = $this->getclassname($value['facevalue']);$result['rows'][$key]['usestatus'] = $this->getclassname($value['usestatus']);}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('petroinfo',array('ID','券编号','校验码','券类名称','券面额类型','加油券面值','生效时间','失效时间','券图路径','券使用说明','使用状态','券类型','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }

    //@@@@石化保单添加-add@@@@
    public function add(){
        $c84 = $this->getclass(84,'moneytype');
        $this->assign('c84',$c84);$c81 = $this->getclass(81,'facevalue');
        $this->assign('c81',$c81);$c31 = $this->getclass(31,'usestatus');
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
        $petroinfo = M('my_petroinfo');
        $petroinfo->add($_POST);
        echo 'success';
    }
    //@@@@石化保单修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $petroinfo = M('my_petroinfo');
        $where['id'] = $id;
        $result = $petroinfo->where($where)->find();
        $c84 = $this->getclass(84,'moneytype',$result['moneytype']);
        $this->assign('c84',$c84);$c81 = $this->getclass(81,'facevalue',$result['facevalue']);
        $this->assign('c81',$c81);$c31 = $this->getclass(31,'usestatus',$result['usestatus']);
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
        
    	$petroinfo = M('my_petroinfo');
        $where['id'] = $id;
        $petroinfo->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $petroinfo = M('my_petroinfo');
        $where['id'] = $id;
        $result = $petroinfo->where($where)->find();
        $c84 = $this->getclass(84,'moneytype',$result['moneytype']);
        $this->assign('c84',$c84);$c81 = $this->getclass(81,'facevalue',$result['facevalue']);
        $this->assign('c81',$c81);$c31 = $this->getclass(31,'usestatus',$result['usestatus']);
        $this->assign('c31',$c31);
        $this->assign('result',$result);
        $this->display();
    }
    //
    public function del(){
    	$id = $_GET['id'];
    	$petroinfo = M('my_petroinfo');
        $petroinfo->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$petroinfo = M('my_petroinfo');
        $where['id'] = array('in',$idlist);
        $petroinfo->where($where)->delete();
        echo 'success';
    }

    public function import(){
        Vendor('PHPExcel.PHPExcel');
        Vendor('PHPExcel.PHPExcel.IOFactory');
        Vendor('PHPExcel.PHPExcel.Reader.Excel5');
        Vendor('PHPExcel.PHPExcel.Reader.Excel2007');
        if(IS_POST){
            $model = new Model();
            $model->startTrans();//开启事务
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
                $data = [];
                for($i=2;$i<=$highestRow;$i++)
                {
                    $data[$j]['typetitle'] = (string)$PHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
                    $data[$j]['vouchertype'] = (string)$PHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
                    $data[$j]['moneytype'] = 85;
                    $data[$j]['typecode'] = (string)$PHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
                    $data[$j]['checkcode'] = (string)$PHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
                    $data[$j]['enabletime'] = (string)$PHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
                    $data[$j]['disabletime'] = (string)$PHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
                    $data[$j]['facevalue'] = self::getMoneyType((string)$PHPExcel->getActiveSheet()->getCell("C".$i)->getValue());
                    $data[$j]['createtime'] = date("Y-m-d H:i:s",time());
                    $data[$j]['updatetime'] = date("Y-m-d H:i:s",time());
                    $data[$j]['usestatus'] = 33;//使用状态-未使用
                    $data[$j]['adminid'] = $_SESSION['id'];
                    $j++;
                }
                $petroinfoid = $model->table('my_petroinfo')->addAll($data);//批量插入输入 返回的是插入的第一条数据的主键
                //删除上传的文件
                unlink($filePath);
                if($petroinfoid){
                    $model->commit();
                    exit('success');
                }else{
                    $model->rollback();
                    exit('导入失败,查看数据');
                }
            }else{
                exit('导入失败,查看文件后缀名及数据');
            }
        }else{
            $this->display();
        }
    }

    /**
     * @param $param
     * @return mixed
     * 返回券对应的类型
     */
    public function getMoneyType($param){
        $arr = [
            50 => 82,
            100 => 83
        ];
        $moneytype = intval($param);
        return $arr[$moneytype];
    }
}