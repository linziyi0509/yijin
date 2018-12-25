<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$微信-wechatgroup$$$$
class WechatgroupController extends BaseController {
    //####用户分组管理-index####
    public function index(){
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
            $where = 'where `my_wechatgroup`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_wechatgroup`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_wechatgroup`.`createtime`)<='".$endtime."' and";
        }
        
        $where = trim($where,' and');
    	$sql = "select `my_wechatgroup`.`id`,`my_wechatgroup`.`groupname`,`my_wechatgroup`.`groupid`,`my_wechatgroup`.`createtime`,`my_wechatgroup`.`updatetime` from `my_wechatgroup`  ".$where." order by `my_wechatgroup`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('wechatgroup',array('ID','分组名称','分组id','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }
    //@@@@用户分组管理添加-add@@@@
    public function add(){
        $this->display();
    }
    /**
     *@FUNCNAME:saveadd;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月19日;
     *@EFFORT:保存上传的数据，通过微信去获取用户的分组id;
     **/
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
        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        $_POST['adminid'] = $_SESSION['id'];
        /**
         * 连接微信
         * 创建分组
         */
        $wechatgroup = D('Wechatgroup');
        if (!$wechatgroup->create($_POST)){ // 创建数据对象
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            exit($wechatgroup->getError());
        }else{
            $weObj = wechat_connect();
            $groupdata = $weObj->createGroup(I("post.groupname"));
            writelog("微信创建分组返回的结果：",$groupdata);
            if($groupdata["group"]){
                $_POST["groupid"] = $groupdata["group"]["id"];
                $dbinfo = $wechatgroup->add();
                if (!isset($dbinfo)) {
                    exit('本地数据保存失败');
                }
            }else {
                exit('微信接口出错');
            }
        }
        echo 'success';
    }
    //@@@@用户分组管理修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $wechatgroup = M('my_wechatgroup');
        $where['id'] = $id;
        $result = $wechatgroup->where($where)->find();
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
        $_POST['updatetime'] =date('Y-m-d H:i:s',time());
        
    	$wechatgroup = D('Wechatgroup');
        $where['id'] = $id;
        $data = $wechatgroup->where($where)->find();
        if(!$wechatgroup->create($_POST)){
            exit($wechatgroup->getError());
        }else{
            $weObj = wechat_connect();
            $result = $weObj->updateGroup($data["groupid"], $_POST["groupname"]);
            if($result){
                $_POST["groupid"] = $result["group"]["id"];
                $dbinfo = $wechatgroup->where($where)->save();
                if (!isset($dbinfo)) {
                    exit('本地数据更新失败');
                }
            }else {
                exit('微信接口出错');
            }
        }
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $wechatgroup = M('my_wechatgroup');
        $where['id'] = $id;
        $result = $wechatgroup->where($where)->find();
        
        $this->assign('result',$result);
        $this->display();
    }
    /**
     *@FUNCNAME:refreshgroup;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月19日;
     *@EFFORT:刷新用户分组信息;
     **/
    public function refreshgroup(){
        $weObj = wechat_connect();
        $list = $weObj -> getGroup();
        writelog("用户分组更新的所有数据：",$list['groups']);
        $Group = M('my_wechatgroup');
        //清空所有的数据 重新获取
        $Group->query('TRUNCATE table `my_wechatgroup`');
        foreach ($list['groups'] as $key => $value) {
            $data[$key] = array('groupid' => $value['id'], 'groupname' => $value['name']);
        }
        if ($Group -> addAll($data)) {
            exit("success");
        }
        exit('更新失败');
    }
    /**
     *@FUNCNAME:del;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月21日;
     *@EFFORT:删除微信端的用户分组，并且将本地数据清除;
     **/
    public function del(){
    	$id = I('get.id','','intval');
    	$wechatgroup = M('my_wechatgroup');
    	$data = $wechatgroup->where("id=%d",$id)->find();
    	//连接微信 删除分组
    	$weObj = wechat_connect();
    	$wxres = $weObj->deleteGroup($data);
    	if(!$wxres){
    	    exit("微信删除分组有误!");
    	}
        $dbres = $wechatgroup->delete($id);
        if(!$dbres){
            exit("本地删除分组有误!");
        }
        echo 'success';
    }
}