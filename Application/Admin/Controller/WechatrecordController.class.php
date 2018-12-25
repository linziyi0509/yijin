<?php
namespace Admin\Controller;
use Think\Controller;
//$$$$微信-wechatrecord$$$$
class WechatrecordController extends BaseController {
    //####消息列表-index####
    public function index(){
        $c13 = $this->getclass(13,'isreply');
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
            $where = 'where `my_wechatrecord`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_wechatrecord`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_wechatrecord`.`createtime`)<='".$endtime."' and";
        }
        
        $where = trim($where,' and');
    	$sql = "SELECT
        	`my_wechatrecord`.`id`,
        	`my_wechatrecord`.`fromusername`,
        	`my_wechatrecord`.`msgtype`,
        	`my_wechatrecord`.`content`,
        	`my_wechatrecord`.`msgid`,
        	`my_wechatrecord`.`picurl`,
        	`my_wechatrecord`.`mediaid`,
        	`my_wechatrecord`.`title`,
        	`my_wechatrecord`.`description`,
        	`my_wechatrecord`.`url`,
        	`my_wechatrecord`.`isreply`,
        	`my_wechatrecord`.`replyid`,
        	`my_wechatrecord`.`replycontent`,
        	`my_wechatrecord`.`replytime`,
        	`my_wechatrecord`.`createtime`,
        	`my_wechatrecord`.`updatetime`,
        	`my_wechatusers`.`headimgurl`,
        	`my_wechatusers`.`nickname`
        FROM
        	`my_wechatrecord` LEFT JOIN `my_wechatusers` ON `my_wechatrecord`.`fromusername` = `my_wechatusers`.`openid` ".$where."
        ORDER BY
        	`my_wechatrecord`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['isreply'] = $this->getclassname($value['isreply']);}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('wechatrecord',array('ID','用户openid','消息类型','发送内容','消息id','图片链接','资源链接','标题','描述','跳转地址','微信是否回复消息','回复人','回复内容','回复时间','创建时间','更新时间','用户头像','用户昵称'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }
}