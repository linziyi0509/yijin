<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$发券管理-petroreqinfo$$$$
class PetroreqinfoController extends BaseController {
    //####石化请求信息-index####
    public function index(){
        $c84 = $this->getclass(84,'moneytype');
        $this->assign('c84',$c84);$c81 = $this->getclass(81,'facevalue');
        $this->assign('c81',$c81);
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
            $where = 'where `my_petroreqinfo`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_petroreqinfo`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_petroreqinfo`.`createtime`)<='".$endtime."' and";
        }
        
        $where = trim($where,' and');
    	$sql = "select `my_petroreqinfo`.`id`,`my_petroreqinfo`.`number`,`my_petroreqinfo`.`moneytype`,`my_petroreqinfo`.`facevalue`,`my_petroreqinfo`.`createtime`,`my_petroreqinfo`.`updatetime` from `my_petroreqinfo`  ".$where." order by `my_petroreqinfo`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);

        

        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['moneytype'] = $this->getclassname($value['moneytype']);$result['rows'][$key]['facevalue'] = $this->getclassname($value['facevalue']);}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('petroreqinfo',array('ID','生成数量','券面额类型','加油券面值','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    

    

    //@@@@石化请求信息添加-add@@@@
    public function add(){
        $c84 = $this->getclass(84,'moneytype');
        $this->assign('c84',$c84);$c81 = $this->getclass(81,'facevalue');
        $this->assign('c81',$c81);
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
        $petroreqinfo = M('my_petroreqinfo');
        $petroreqinfo->add($_POST);
        echo 'success';
    }
    //@@@@石化请求信息修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $petroreqinfo = M('my_petroreqinfo');
        $where['id'] = $id;
        $result = $petroreqinfo->where($where)->find();
        $c84 = $this->getclass(84,'moneytype',$result['moneytype']);
        $this->assign('c84',$c84);$c81 = $this->getclass(81,'facevalue',$result['facevalue']);
        $this->assign('c81',$c81);

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
        
    	$petroreqinfo = M('my_petroreqinfo');
        $where['id'] = $id;
        $petroreqinfo->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $petroreqinfo = M('my_petroreqinfo');
        $where['id'] = $id;
        $result = $petroreqinfo->where($where)->find();
        $c84 = $this->getclass(84,'moneytype',$result['moneytype']);
        $this->assign('c84',$c84);$c81 = $this->getclass(81,'facevalue',$result['facevalue']);
        $this->assign('c81',$c81);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@石化请求信息删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$petroreqinfo = M('my_petroreqinfo');
        $petroreqinfo->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$petroreqinfo = M('my_petroreqinfo');
        $where['id'] = array('in',$idlist);
        $petroreqinfo->where($where)->delete();
        echo 'success';
    }

}