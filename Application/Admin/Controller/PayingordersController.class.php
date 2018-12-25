<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Model;

//$$$$订单管理-payingorders$$$$
class PayingordersController extends BaseController {
    //####订单列表-index####
    public function index(){
        $c34 = $this->getclass(34,'paidflag');
        $this->assign('c34',$c34);
		$c41 = $this->getclass(41,'payingtype');
        $this->assign('c41',$c41);
		$c38 = $this->getclass(38,'ordertype');
        $this->assign('c38',$c38);
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
		if($_SESSION['gname'] == "客服组"){
			$where = 'where 1=1 and ';
		}else{			
			if($_SESSION['all'] == 1){
				$where = 'where 1=1 and ';
			}else{
				$where = 'where `my_payingorders`.`adminid`='.$_SESSION['adminid'].' and ';
			}
        }
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_payingorders`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_payingorders`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['wechatuserid'] <> ''){
            $where .= " `my_payingorders`.`wechatuserid` = ".$_POST['wechatuserid']." and ";
        }
        if($_POST['username'] <> ''){
	    $where .= " `my_payingorders`.`username` like '%".$_POST['username']."%' and ";
        }
        if($_POST['telephone'] <> ''){
            $where .= " `my_payingorders`.`telephone` like '%".$_POST['telephone']."%' and ";
        }
        if($_POST['paidflag'] <> ''){
            $where .= " `my_payingorders`.`paidflag` = ".$_POST['paidflag']." and ";
        }
		if($_POST['payingtype'] <> ''){
            $where .= " `my_payingorders`.`payingtype` = ".$_POST['payingtype']." and ";
        }
		if($_POST['ordertype'] <> ''){
            $where .= " `my_payingorders`.`ordertype` = ".$_POST['ordertype']." and ";
        }
		if($_POST['ordernum'] <> ''){
            $where .= " `my_payingorders`.`ordernum` like '%".$_POST['ordernum']."%' and ";
        }
        $where = trim($where,' and');
    	$sql = "select `my_payingorders`.`id`,`my_payingorders`.`username`,`my_payingorders`.`telephone`,`my_payingorders`.`credit`,`my_payingorders`.`money`,`my_payingorders`.`paidflag`,`my_payingorders`.`ordertype`,`my_payingorders`.`ordernum`,`my_payingorders`.`batch`,`my_payingorders`.`ornormal`,`my_payingorders`.`excepthandle`,`my_payingorders`.`payingtype`,`my_payingorders`.`ipaddr`,`my_payingorders`.`createtime`,`my_payingorders`.`updatetime`,`my_payingorders`.`payeeaccount`,`my_payingorders`.`payeerealname`,`my_admin`.`username` as `resultuser` from `my_payingorders`  left join `my_wechatuser` on `my_wechatuser`.`id` = `my_payingorders`.`wechatuserid` left join `my_admin` on `my_admin`.`id` = `my_payingorders`.`adminid` ".$where." order by `my_payingorders`.`".$sort."` ".$order;
    	$result = $this->pagelist_($sql,$_POST);
        foreach ($result['rows'] as $key => $value) {
			$result['rows'][$key]['paidflag'] = $this->getclassname($value['paidflag']);
			$result['rows'][$key]['ornormal'] = $this->getclassname($value['ornormal']);
			$result['rows'][$key]['payingtype'] = $this->getclassname($value['payingtype']);
			$result['rows'][$key]['ordertype'] = $this->getclassname($value['ordertype']);
		}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('payingorders',array('ID','用户名','手机号','积分','金额(元)','订单状态','订单类型','订单号','交易号','是否异常','异常处理情况','支付方式','用户付款时的ip','创建时间','更新时间','支付宝账号','支付宝名称','管理员账号'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }
    //
    public function add(){
        $c34 = $this->getclass(34,'paidflag');
        $this->assign('c34',$c34);$c46 = $this->getclass(46,'ornormal');
        $this->assign('c46',$c46);$c41 = $this->getclass(41,'payingtype');
        $this->assign('c41',$c41);$c38 = $this->getclass(38,'ordertype');
        $this->assign('c38',$c38);
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
        $payingorders = M('my_payingorders');
        $payingorders->add($_POST);
        echo 'success';
    }
    //@@@@订单列表修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $payingorders = M('my_payingorders');
        $where['id'] = $id;
        $result = $payingorders->where($where)->find();
        $c34 = $this->getclass(34,'paidflag',$result['paidflag']);
        $this->assign('c34',$c34);$c46 = $this->getclass(46,'ornormal',$result['ornormal']);
        $this->assign('c46',$c46);$c41 = $this->getclass(41,'payingtype',$result['payingtype']);
        $this->assign('c41',$c41);$c38 = $this->getclass(38,'ordertype',$result['ordertype']);
        $this->assign('c38',$c38);
    	$this->assign('result',$result);
        $this->display();
    }

    /**
     * 订单状态：待处理，已处理，已关闭
     * 订单类型：转让，正常使用，提现
     * 订单是否异常
     * 已处理的订单只允许修改为已关闭
     * 待处理的订单->已处理(提现)
     */
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
        $_POST['resulttime'] =date('Y-m-d H:i:s',time());
        $model = new Model();
        $model->startTrans();//开启事务
    	$payingorders = M('my_payingorders');
    	$wechatuser = M('my_wechatuser');
        $where['id'] = $id;
        $data = $payingorders->where($where)->find();
        $flag = [];
		$_POST['adminid'] = $_SESSION['id'];
		$wechatuserData = $wechatuser->where("id=".$data['wechatuserid'])->find();
		//var_dump($wechatuser->getLastSql());
		//die;
		if($wechatuserData["disabled"]==18){
			exit('请激活用户');
		}
		if($_POST["payingtype"] !== $data["payingtype"]){
            exit('不允许修改支付方式');
        }
        if($data["paidflag"] == 35  && $_POST["paidflag"] == 36 && $_POST["ordertype"] == 51 && $_POST["payingtype"] == 44){//待处理提现订单 扣除用户的积分，发送红包给用户 
            //需要计算并扣除当前用户的积分
			/*if($wechatuserData["credits"]>$data["credit"]){
				exit('当前用户积分不足');
			}
			if($data["credit"] != $_POST["credit"] || $data["money"] != $_POST["money"]){
				exit('金额与积分有误，请勿修改');
			}
            $flag[] = $model->table('my_wechatuser')->where("id=".$data['wechatuserid'])->save(["credits"=>$wechatuserData["credits"]-$data["credit"]]);
            */
            //需要调用接口进行发红包
            $weObj = wechat_connect("LJS");
            $transfers = [
                'mch_appid' => C('LJSAPPID'),
                'mchid' => C('MCHID'),
                'device_info' => $wechatuserData['device_info']?$wechatuserData['device_info']:'iphoneorandroid',
                'nonce_str' => $weObj->createNonceStr(),
                'partner_trade_no' => $data['ordernum'],//'ld201712261123',//ld201712211500
                'openid' => $wechatuserData['ljsopenid'],
                'check_name' => 'NO_CHECK',
                'amount' => $data['money']*100,//转为分
                'desc' => '积分服务',
                'spbill_create_ip' => $data['ipaddr']?$data['ipaddr']:'192.168.1.88'
            ];
            $transfers['sign'] = $weObj->MakeSign($transfers);
            $transfersresult = $weObj->transfers($transfers);
            writelog("微信支付信息：",$transfersresult);
            if($transfersresult["result_code"] == "SUCCESS" && $transfersresult["return_code"] == "SUCCESS"){
                $transferinfo = [
                    'appid' => C('LJSAPPID'),
                    'mch_id' => C('MCHID'),
                    'nonce_str' => $weObj->createNonceStr(),
                    'partner_trade_no' => $data['ordernum'],
                ];
                $transferinfo['sign'] = $weObj->MakeSign($transferinfo);
                $transfersinforesult = $weObj->gettransferinfo($transferinfo);
                writelog("微信支付订单返回信息：",$transfersinforesult);
                if($transfersinforesult["return_code"] == "SUCCESS" && $transfersinforesult["result_code"] == "SUCCESS"){
                    $_POST["batch"] = $transfersinforesult["payment_no"];
                    $flag[] = true;
                }else{
                    exit("付款成功，查询订单失败");
                }
                $flag[] = $model->table('my_payingorders')->where($where)->save($_POST);
            }else{
                exit($transfersresult["err_code_des"]);
                //AlipayFundTransToaccountTransferRequest
            }
        }else if($data["paidflag"] == 35  && $_POST["paidflag"] == 36 && $_POST["ordertype"] == 51 && $_POST["payingtype"] == 43){
            //处理支付宝的订单
            $alipay = alipayPay();
            vendor('Alipay.aop.request.AlipayFundTransToaccountTransferRequest');
            vendor('Alipay.aop.request.AlipayFundTransOrderQueryRequest');
            $request = new \AlipayFundTransToaccountTransferRequest();
            $request->setBizContent("{" .
                "\"out_biz_no\":\"".$data["ordernum"]."\"," .
                "\"payee_type\":\"ALIPAY_LOGONID\"," .
                "\"payee_account\":\"".$data["payeeaccount"]."\"," .
                "\"amount\":\"".$data["money"]."\"," .
                "\"payer_show_name\":\"乐寄售（一金生活）\"," .
                "\"payee_real_name\":\"".$data["payeerealname"]."\"," .
                "\"remark\":\"积分服务\"" .
                "}");
            $result = $alipay->execute($request);
            writelog("支付宝支付信息：",$result);
            $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
            writelog("支付宝支付信息---responseNode---：",$responseNode);
            $resultCode = $result->$responseNode->code;
            if(!empty($resultCode)&&$resultCode == 10000){
                $queryAlipay = alipayPay();
                $queryReq = new \AlipayFundTransOrderQueryRequest();
                //查询时 选择填写
                $queryReq->setBizContent("{" .
                    "\"out_biz_no\":\"".$data["ordernum"]."\"" .
                    "  }");
                $queryResult = $queryAlipay->execute ( $queryReq);
                $queryResponseNode = str_replace(".", "_", $queryReq->getApiMethodName()) . "_response";
                writelog("支付宝查询信息---queryResponseNode---：",$queryResponseNode);
                $queryResultCode = $queryResult->$queryResponseNode->code;
                if(!empty($queryResultCode)&&$queryResultCode == 10000){
                    $_POST["batch"] = $queryResult->$queryResponseNode->order_id;
                    $flag[] = true;
                } else {
                    exit("付款成功，查询订单失败");
                }
                $flag[] = $model->table('my_payingorders')->where($where)->save($_POST);
            } else {
                writelog("sub_code的值：",$result->$responseNode->sub_code);
                $errorInfo = alipayErrorInfo($result->$responseNode->sub_code);
                if($errorInfo){
                    exit("失败：".$errorInfo);
                }else{
                    exit("失败：".$result->$responseNode->sub_msg);
                }
            }
        }else if($data["paidflag"] == 36 && $_POST["paidflag"] != 37){
            exit('订单只允许被关闭');
        }elseif($data["paidflag"] == 36 && $_POST["paidflag"] == 37){
            $flag[] = $model->table('my_payingorders')->where($where)->save(["paidflag"=>37]);
        }else if($data["paidflag"] == 37){
            exit('订单不允许操作');
        }else if($data["paidflag"] == 35 && $_POST["paidflag"] == 37){
            exit('请处理订单后在关闭');
        }else if($data["paidflag"] == 35  && $_POST["paidflag"] == 36 && $_POST["ordertype"] == 40){
			//如果是转让的订单 将积分加入到用户积分中
			$flag[] = $model->table('my_wechatuser')->where("id=".$data['wechatuserid'])->save(["credits"=>$wechatuserData["credits"]+$data["credit"]]);
            $flag[] = $model->table('my_payingorders')->where($where)->save($_POST);
		}else{
            $flag[] = $model->table('my_payingorders')->where($where)->save($_POST);
        }
        if(empty($flag) || in_array(false,$flag)){
            $model->rollback();
            exit('数据有误');
        }else{
            $model->commit();
            exit('success');
        }
    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $payingorders = M('my_payingorders');
        $where['id'] = $id;
        $result = $payingorders->where($where)->find();
        $c34 = $this->getclass(34,'paidflag',$result['paidflag']);
        $this->assign('c34',$c34);
        $c46 = $this->getclass(46,'ornormal',$result['ornormal']);
        $this->assign('c46',$c46);
        $c41 = $this->getclass(41,'payingtype',$result['payingtype']);
        $this->assign('c41',$c41);
        $c38 = $this->getclass(38,'ordertype',$result['ordertype']);
        $this->assign('c38',$c38);
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@订单列表删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
        $payingorders = M('my_payingorders');
        $payingorders->delete($id);
        $buyingmodofcustomer = M('my_buyingmodofcustomer');
        $buyingmodofcustomerCon['payingordersid'] = array('in',$id);
        $buyingmodofcustomer->where($buyingmodofcustomerCon)->delete();
        echo 'success';
    }
    //
    public function delall(){
    	$idlist = $_POST['idlist'];
    	$payingorders = M('my_payingorders');
        $where['id'] = array('in',$idlist);
        $payingorders->where($where)->delete();
        $buyingmodofcustomer = M('my_buyingmodofcustomer');
        $buyingmodofcustomerCon['payingordersid'] = array('in',$idlist);
        $buyingmodofcustomer->where($buyingmodofcustomerCon)->delete();
        echo 'success';
    }
    //####订单报表列表-orderreport####
    public function orderreport(){
        $c34 = $this->getclass(34,'paidflag');
        $this->assign('c34',$c34);
        $c41 = $this->getclass(41,'payingtype');
        $this->assign('c41',$c41);
        $c38 = $this->getclass(38,'ordertype');
        $this->assign('c38',$c38);
        $this->display();
    }
	public function gettotalmoney(){
		$payingorders = M('my_payingorders');
        $sort = isset($_POST['sort']) ? $_POST['sort'] : 'id';
        $order = isset($_POST['order']) ? $_POST['order'] : 'desc';
        $starttime = $_POST['starttime'];
        $endtime = $_POST['endtime'];
        $where = ' where 1=1 and ';
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_payingorders`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_payingorders`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['paidflag'] <> ''){
            $where .= " `my_payingorders`.`paidflag` = ".$_POST['paidflag']." and ";
        }
        if($_POST['payingtype'] <> ''){
            $where .= " `my_payingorders`.`payingtype` = ".$_POST['payingtype']." and ";
        }
        if($_POST['ordertype'] <> ''){
            $where .= " `my_payingorders`.`ordertype` = ".$_POST['ordertype']." and ";
        }
        $where = trim($where,' and');
        $sql = "select sum(`my_payingorders`.`money`) as `totalmoney`,`my_payingorders`.`paidflag`,`my_payingorders`.`ordertype`,`my_payingorders`.`payingtype` from `my_payingorders` ".$where." group by `my_payingorders`.`paidflag`,`my_payingorders`.`payingtype`,`my_payingorders`.`ordertype` order by `my_payingorders`.`".$sort."` ".$order;
        $result = $this->pagelist_($sql,$_POST);
        foreach ($result['rows'] as $key => $value) {
            $result['rows'][$key]['paidflag'] = $this->getclassname($value['paidflag']);
            $result['rows'][$key]['payingtype'] = $this->getclassname($value['payingtype']);
            $result['rows'][$key]['ordertype'] = $this->getclassname($value['ordertype']);
        }
        //导出逻辑结束
    	echo json_encode($result);
	}
}
