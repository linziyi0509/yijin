<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$用户管理-wechatuser$$$$
class WechatuserController extends BaseController {
    //####用户列表-index####
    public function index(){
        $c16 = $this->getclass(16,'disable');
        $this->assign('c16',$c16);$c19 = $this->getclass(19,'actived');
        $this->assign('c19',$c19);
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
            $where = 'where `my_wechatuser`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_wechatuser`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_wechatuser`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['merchantid'] <> ''){$where .= " `my_wechatuser`.`merchantid` = ".$_POST['merchantid']." and ";}$where .= " `my_wechatuser`.`nickname` like '%".$_POST['nickname']."%' and ";
        $where = trim($where,' and');
    	$sql = "select `my_wechatuser`.`id`,`my_wechatuser`.`openid`,`my_wechatuser`.`nickname`,`my_wechatuser`.`sex`,`my_wechatuser`.`city`,`my_wechatuser`.`province`,`my_wechatuser`.`country`,`my_wechatuser`.`language`,`my_wechatuser`.`headimgurl`,`my_wechatuser`.`subscribe_time`,`my_wechatuser`.`subscribe`,`my_wechatuser`.`phone`,`my_wechatuser`.`email`,`my_wechatuser`.`credits`,`my_wechatuser`.`disable`,`my_wechatuser`.`actived`,`my_wechatuser`.`createtime`,`my_wechatuser`.`updatetime` from `my_wechatuser`  left join `my_merchant` on `my_merchant`.`id` = `my_wechatuser`.`merchantid` ".$where." order by `my_wechatuser`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);

        

        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['disable'] = $this->getclassname($value['disable']);$result['rows'][$key]['actived'] = $this->getclassname($value['actived']);}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('wechatuser',array('ID','用户标识','用户昵称','性别','城市','省份','国家','语言','用户头像','关注时间','是否关注','手机号','邮箱','积分','是否禁用','是否激活','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    

    

    //
    public function add(){
        $c16 = $this->getclass(16,'disable');
        $this->assign('c16',$c16);$c19 = $this->getclass(19,'actived');
        $this->assign('c19',$c19);
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
        if($info['headimgurl']){$_POST['headimgurl'] = '/upload/'.$info['headimgurl']['savepath'].$info['headimgurl']['savename'];}

        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        $_POST['adminid'] = $_SESSION['id'];
        $wechatuser = M('my_wechatuser');
        $wechatuser->add($_POST);
        echo 'success';
    }
    //@@@@用户列表修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $wechatuser = M('my_wechatuser');
        $where['id'] = $id;
        $result = $wechatuser->where($where)->find();
        $c16 = $this->getclass(16,'disable',$result['disable']);
        $this->assign('c16',$c16);$c19 = $this->getclass(19,'actived',$result['actived']);
        $this->assign('c19',$c19);

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
        if($info['headimgurl']){$_POST['headimgurl'] = '/upload/'.$info['headimgurl']['savepath'].$info['headimgurl']['savename'];}
    	$wechatuser = M('my_wechatuser');
        $where['id'] = $id;
        $wechatuser->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $wechatuser = M('my_wechatuser');
        $where['id'] = $id;
        $result = $wechatuser->where($where)->find();
        $c16 = $this->getclass(16,'disable',$result['disable']);
        $this->assign('c16',$c16);$c19 = $this->getclass(19,'actived',$result['actived']);
        $this->assign('c19',$c19);
        $this->assign('result',$result);
        $this->display();
    }
    //
    public function del(){
    	$id = $_GET['id'];
    	$wechatuser = M('my_wechatuser');
        $wechatuser->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$wechatuser = M('my_wechatuser');
        $where['id'] = array('in',$idlist);
        $wechatuser->where($where)->delete();
        echo 'success';
    }

}