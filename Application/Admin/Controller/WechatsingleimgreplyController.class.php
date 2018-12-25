<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$wechat自定义回复-wechatsingleimgreply$$$$
class WechatsingleimgreplyController extends BaseController {
    //####单图文回复-index####
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
            $where = 'where `my_wechatsingleimgreply`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_wechatsingleimgreply`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_wechatsingleimgreply`.`createtime`)<='".$endtime."' and";
        }
        
        $where = trim($where,' and');
    	$sql = "select `my_wechatsingleimgreply`.`id`,`my_wechatsingleimgreply`.`keyword`,`my_wechatsingleimgreply`.`title`,`my_wechatsingleimgreply`.`digest`,`my_wechatsingleimgreply`.`show_cover_pic`,`my_wechatsingleimgreply`.`content`,`my_wechatsingleimgreply`.`content_source_url`,`my_wechatsingleimgreply`.`hits`,`my_wechatsingleimgreply`.`img_url`,`my_wechatsingleimgreply`.`media_id`,`my_wechatsingleimgreply`.`createtime`,`my_wechatsingleimgreply`.`updatetime` from `my_wechatsingleimgreply`  ".$where." order by `my_wechatsingleimgreply`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('wechatsingleimgreply',array('ID','关键词','标题','图文摘要','封面状态','图文消息内容','原文地址','点击量','图片地址','素材id','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    

    

    //@@@@单图文回复添加-add@@@@
    public function add(){
        
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
        if(!is_array($info)){
            exit($info);
        }
        if($info['img_url']){$_POST['img_url'] = '/upload/'.$info['img_url']['savepath'].$info['img_url']['savename'];}

        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        $_POST['adminid'] = $_SESSION['id'];
        $wechatsingleimgreply = D('Wechatsingleimgreply');
        if (!$wechatsingleimgreply->create($_POST)){ // 创建数据对象
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            exit($wechatsingleimgreply->getError());
        }else{
            $weObj = wechat_connect();
            $filepath = realpath("./").$_POST["img_url"];
            writelog("打印图片:",$filepath);
            $thumbres = $weObj->uploadthumb(array("media"=>"@".$filepath));
            if(!$thumbres['thumb_media_id']){
                exit("图文消息缩略图生成出错");
            }
            $data = array(
                    'articles'=>array(
                        'title' => I("post.title","","trim"),
                        'digest' => I("post.digest","","trim"),
                        'show_cover_pic' => 1,
                        'content' => I("post.content","","trim"),
                        'content_source_url' => I("post.content_source_url","","trim"),
                        'thumb_media_id' => $thumbres['thumb_media_id']
                    )
            );
            $singleimgdata = $weObj->add_news($data);
            writelog("微信添加图文消息返回的结果：",$singleimgdata);
            if($singleimgdata["media_id"]){
                $_POST["media_id"] = $singleimgdata["media_id"];
                $dbinfo = $wechatsingleimgreply->add();
                if (!isset($dbinfo)) {
                    exit('本地数据保存失败');
                }
            }else {
                exit('微信接口出错');
            }
        }
        echo 'success';
    }
    //@@@@单图文回复修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $wechatsingleimgreply = M('my_wechatsingleimgreply');
        $where['id'] = $id;
        $result = $wechatsingleimgreply->where($where)->find();
        

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
        if(!is_array($info)){
            exit($info);
        }
        $_POST['updatetime'] =date('Y-m-d H:i:s',time());
        if($info['img_url']){$_POST['img_url'] = '/upload/'.$info['img_url']['savepath'].$info['img_url']['savename'];}
    	$wechatsingleimgreply = M('my_wechatsingleimgreply');
        $where['id'] = $id;
        $wechatsingleimgreply->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $wechatsingleimgreply = M('my_wechatsingleimgreply');
        $where['id'] = $id;
        $result = $wechatsingleimgreply->where($where)->find();
        
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@单图文回复删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$wechatsingleimgreply = M('my_wechatsingleimgreply');
        $wechatsingleimgreply->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$wechatsingleimgreply = M('my_wechatsingleimgreply');
        $where['id'] = array('in',$idlist);
        $wechatsingleimgreply->where($where)->delete();
    }

}