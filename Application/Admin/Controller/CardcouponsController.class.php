<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Model;
use Think\SnowFlake;

//$$$$卡券管理-cardcoupons$$$$
class CardcouponsController extends BaseController {
    //####卡券列表-index####
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
            $where = 'where `my_cardcoupons`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_cardcoupons`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_cardcoupons`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['servicevoucherid'] <> ''){$where .= " `my_cardcoupons`.`servicevoucherid` = ".$_POST['servicevoucherid']." and ";}$where .= " `my_cardcoupons`.`name` like '%".$_POST['name']."%' and ";
        $where = trim($where,' and');
    	$sql = "select `my_cardcoupons`.`id`,`my_cardcoupons`.`name`,`my_cardcoupons`.`credit`,`my_cardcoupons`.`number`,`my_cardcoupons`.`createtime`,`my_cardcoupons`.`updatetime` from `my_cardcoupons`  left join `my_servicevoucher` on `my_servicevoucher`.`id` = `my_cardcoupons`.`servicevoucherid` ".$where." order by `my_cardcoupons`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        foreach($result['rows'] as $key=>$val){
            $cardnoRange = M("my_cards")->field("max(cardno) maxcardno,min(cardno) mincardno")->where(['cardcouponsid'=>$val['id']])->find();
            $result['rows'][$key]["cardnoRange"] = $cardnoRange['mincardno'].'-'.$cardnoRange['maxcardno'];
        }
        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('cardcoupons',array('ID','卡券名称','对应面值','对应数量','创建时间','更新时间','卡号范围'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }
    //@@@@卡券列表添加-add@@@@
    public function add(){
        $this->display();
    }

    /**
     * 注意这里填写的数据
     * 1.卡券列表信息
     * 2.卡片自动生成数据
     * 3.关联列表信息
     * 用于事务来做处理
     * servicevoucherids 对应服务券
     * num1 num2   num+服务券ID 数量
     * number 卡片的数量
     */
    public function saveadd(){
        //上传
        $model = new Model();
        $model->startTrans();//开启事务
        $flag = [];
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
                    $cardcouponsData[$value['key']] = '/upload/'.$value['savepath'].$value['savename'].',';
                }
            }
        }
        $cardcouponsData['createtime'] = date('Y-m-d H:i:s',time());
        $cardcouponsData['updatetime'] = date('Y-m-d H:i:s',time());
        $cardcouponsData['adminid'] = $_SESSION['id'];
        $servicevoucheridsArr = explode(',',$_POST["servicevoucherids"]);
        writelog("servicevoucheridsArr:------",$servicevoucheridsArr);
        $servicevoucherids = '';
        $servicevoucheridsNums = [];
        for($i=0;$i<count($servicevoucheridsArr);$i++){
            $servicevoucherids .= $servicevoucheridsArr[$i].'#num'.$_POST["num".$servicevoucheridsArr[$i]].',';
            $servicevoucheridsNum = [
                'id' => $servicevoucheridsArr[$i],
                'num' => $_POST["num".$servicevoucheridsArr[$i]]
            ];
            $servicevoucheridsNums[] = $servicevoucheridsNum;
        }
        writelog("servicevoucheridsNums:------",$servicevoucheridsNums);
        $servicevoucherids = trim($servicevoucherids,',');
        $cardcouponsData['servicevoucherid'] = $servicevoucherids;
        $cardcouponsData['name'] = $_POST["name"];
        $cardcouponsData['credit'] = $_POST["credit"];
        $cardcouponsData['number'] = $_POST["number"];
        writelog("cardcouponsData:",$cardcouponsData);
        $cardcouponsid = $model->table('my_cardcoupons')->add($cardcouponsData);
        writelog("cardcouponsid:",$cardcouponsid);
        if( $cardcouponsid){//如果没有传入房型的信息则,直接提交数据
			$flag[] = true;
            //生成number数量的卡片，没生成一个 对应num+服务券ID 多个的关联
            //生成卡片
            $cardsData = self::CardDataStitch($cardcouponsid,$cardcouponsData['number']);
            writelog("cardsData:------",$cardsData);
            $cardsid = $model->table('my_cards')->addAll($cardsData);//批量插入输入 返回的是插入的第一条数据的主键
            writelog("sql:-",$model->getLastSql());
            writelog("error:-",$model->getError());
            $cardsEndPos = $cardsid + count($cardsData);//插入数据的最后一条
            writelog("cardsid:----cardsEndPos----",$cardsid."---".$cardsEndPos);
            if( $cardsid ){
				$flag[] = true;
                //$model->commit();
                //插入关联表的信息 cardservicevoucher
                $cardservicevoucherData = self::cardservicevoucherInfo($cardsid, $cardsEndPos, $servicevoucheridsNums);
                writelog("cardservicevoucherData:------",$cardservicevoucherData);
                $cardservicevoucherid = $model->table('my_cardservicevoucher')->addAll($cardservicevoucherData);//批量插入输入 返回的是插入的第一条数据的主键
                if($cardservicevoucherid){
                    //$model->commit();
                    $flag[] = true;
                }else{
					$flag[] = false;
				}
            }else{
                $flag[] = false;
            }
        }else{
			$flag[] = false;
		}
        if(empty($flag) || in_array(false,$flag)){
            $model->rollback();
			exit('数据有误');
        }else{
            $model->commit();
			exit('success');
        }
    }
    //@@@@卡券列表修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $cardcoupons = M('my_cardcoupons');
        $where['id'] = $id;
        $result = $cardcoupons->where($where)->find();
        

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
    	$cardcoupons = M('my_cardcoupons');
        $where['id'] = $id;
        $cardcoupons->where($where)->save($_POST);
        echo 'success';
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $cardcoupons = M('my_cardcoupons');
        $where['id'] = $id;
        $result = $cardcoupons->where($where)->find();
        
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@卡券列表删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
    	$cardcoupons = M('my_cardcoupons');
        $cardcoupons->delete($id);
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$cardcoupons = M('my_cardcoupons');
        $where['id'] = array('in',$idlist);
        $cardcoupons->where($where)->delete();
        echo 'success';
    }
    /**
     * @param $cid
     * @param $number
     * @return array
     * 卡片数据拼接
     */
    protected function CardDataStitch($cid ,$number){
        $arr = [];
        for($i=1;$i<=$number;$i++){
            $data['createtime'] = date('Y-m-d H:i:s',time());
            $data['adminid'] = $_SESSION['id'];
            $data['cardcouponsid'] = $cid;//卡片与卡券关联ID
            $data['cardno'] = D("cards")->getCardno();//卡号 首先查询一下卡片中最大的卡号，在此基础上加1 最小8位数
            $datacenterId = rand(1,9);
            $sf = new SnowFlake($datacenterId,10-$datacenterId);
            $data['cardpwd'] = trim($sf->generateID(),'-');//卡密
            $data['status'] = 29;//卡号状态
            $data['usestatus'] = 33;//使用状态
            $arr[] = $data;
        }
        return $arr;
    }

    /**
     * @param $cardsStartPos
     * @param $cardsEndPos
     * @param $servicevoucherData
     * @return array
     *  批量生成卡片和服务券关联的信息
     * 数组中 包含服务券的ID和数量
     */
    protected function cardservicevoucherInfo($cardsStartPos,$cardsEndPos,$servicevoucherData){
        $data = [];
        for($cardsStartPos;$cardsStartPos<$cardsEndPos;$cardsStartPos++){
            foreach($servicevoucherData as $key=>$val){
                for($i=0;$i<$val["num"];$i++){
                    $arr = [
                        'createtime' => date('Y-m-d H:i:s',time()),
                        'updatetime' => date('Y-m-d H:i:s',time()),
                        'adminid' => $_SESSION['id'],
                        'cardsid' => $cardsStartPos,
                        'servicevoucherid' => $val['id'],
                        'usestatus' => 33,
                        'status' => 20
                    ];
                    $data[] = $arr;
                }
            }
        }
        return $data;
    }

    /**
     * @param $id
     * 导出卡片信息
     */
    public function download($id){
        $id = $_GET['id'];
        $cards = M('my_cards');
        $where['cardcouponsid'] = $id;
        $result = $cards->where($where)->select();
        $this->assign('result',$result);
        $this->assign('id',$id);
        $this->display();
    }
}
