<?php
namespace Admin\Controller;
use Admin\Model\OperablerelationModel;
use Admin\Model\PolicyModel;
use Think\Controller;
use Think\Model;

//$$$$发券管理-policy$$$$
class PolicyController extends BaseController {
    //####发券列表-index####
    public function index(){
        $c68 = $this->getclass(68,'isauthorizesales');
        $this->assign('c68',$c68);$c74 = $this->getclass(74,'isaudit');
        $this->assign('c74',$c74);
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
            $model = new OperablerelationModel();
            $result = $model->userhavemerchat();
            $where = 'where `my_policy`.`merchantid` in('.$result.') and ';
//            $where = 'where `my_policy`.`adminid`='.$_SESSION['adminid'].' and ';
        }
        
        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_policy`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_policy`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['merchantid'] <> ''){
            $where .= " `my_policy`.`merchantid` = ".$_POST['merchantid']." and ";
        }
        $where .= " `my_policy`.`policynumber` like '%".$_POST['policynumber']."%' and ";
        $where .= " `my_policy`.`telephone` like '%".$_POST['telephone']."%' and ";
        if($_POST['isauthorizesales'] <> ''){
            $where .= " `my_policy`.`isauthorizesales` = ".$_POST['isauthorizesales']." and ";
        }
        if($_POST['salesmanphone'] <> ''){
            $where .= " `my_policy`.`salesmanphone` like '%".$_POST['salesmanphone']."%' and ";
        }
        if($_POST['isaudit'] <> ''){
            $where .= " `my_policy`.`isaudit` = ".$_POST['isaudit']." and ";
        }
        $where = trim($where,' and');
    	$sql = "select `my_policy`.`id`,`my_policy`.`policynumber`,`my_policy`.`telephone`,`my_admin`.`name` as `aname`,`my_merchant`.`name` as `mname`,`my_policy`.`isauthorizesales`,`my_policy`.`salesmanphone`,`my_policy`.`shjyqtotal`,`my_policy`.`shczktotal`,`my_policy`.`fwktotal`,`my_policy`.`jfktotal`,`my_policy`.`audittime`,`my_policy`.`remark`,`my_policy`.`isaudit`,`my_policy`.`createtime`,`my_policy`.`updatetime` from `my_policy`  left join `my_merchant` on `my_merchant`.`id` = `my_policy`.`merchantid` left join `my_admin` on `my_admin`.`id` = `my_policy`.`adminid` ".$where." order by `my_policy`.`".$sort."` ".$order;
        $result = $this->pagelist_($sql,$_POST);

        

        foreach ($result['rows'] as $key => $value) {$result['rows'][$key]['isauthorizesales'] = $this->getclassname($value['isauthorizesales']);$result['rows'][$key]['isaudit'] = $this->getclassname($value['isaudit']);}

        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('policy',array('ID','保单号','手机号','发券人','商户名','是否授权营销员','营销员手机号','石化加油券','石化充值卡','服务卡','积分卡','审核时间','备注','是否审核','创建时间','更新时间'),$result['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
    	echo json_encode($result);
    }


    

    

    //@@@@发券列表添加-add@@@@
    public function add(){
        $c68 = $this->getclass(68,'isauthorizesales');
        $this->assign('c68',$c68);$c74 = $this->getclass(74,'isaudit');
        $this->assign('c74',$c74);
        $this->display();
    }

    public function saveadd(){
        /*发券管理信息---------dudj-----开始---------------------*/
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
                    $policyData[$value['key']] = '/upload/'.$value['savepath'].$value['savename'].',';
                }
            }
        }
        $policyData['createtime'] = date('Y-m-d H:i:s',time());
        $policyData['updatetime'] = date('Y-m-d H:i:s',time());
        $policyData['policynumber'] = $_POST['policynumber'];
        $policyData['adminid'] = $_SESSION['adminid'];
        $policyData['telephone'] = $_POST['telephone'];
        $policyData['isauthorizesales'] = $_POST['isauthorizesales'];
        //如果授权 保存授权员的手机号
        if($policyData['isauthorizesales'] == 69){
            $policyData['salesmanphone'] = $_POST['salesmanphone'];
        }else{
            $policyData['isauthorizesales'] = 70;
        }
//        $policyData['audittime'] = $_POST['audittime'];
        $policyData['remark'] = $_POST['remark'];
//        $policyData['isaudit'] = $_POST['isaudit'];
        $servicevoucheridsArr = explode(',',$_POST["servicevoucherids"]);
        writelog("servicevoucheridsArr:------",$_POST["servicevoucherids"]);
        $servicevoucheridsNums = [];
        for($i=0;$i<count($servicevoucheridsArr);$i++){
            $svdata = M("my_servicevoucher")->where(['id'=>$servicevoucheridsArr[$i]])->find();
            $servicevoucheridsNum = [
                'id' => $servicevoucheridsArr[$i],
                'num' => $_POST["num".$servicevoucheridsArr[$i]],
                'money' => $svdata["money"],
                'type' => $svdata['type'],
                'issynchrogrant'=> $_POST["issynchrogrant".$servicevoucheridsArr[$i]],
            ];
            $servicevoucheridsNums[] = $servicevoucheridsNum;
        }
        foreach($servicevoucheridsNums as $key=>$val){
            if($val["issynchrogrant"] == 72){
                if($policyData['isauthorizesales'] != 69){
                    exit('请查看授权状态是否一致，授权状态改变需重新查询!');
                }
            }
        }
        writelog("servicevoucheridsNums:------",$servicevoucheridsNums);
        //判断是否授权和详情中是否授权的数据一致----开始

        //判断是否授权和详情中是否授权的数据一致----结束
        //判断金额是否正确
        $compare = [];
        foreach($servicevoucheridsNums as $val){
            $compare[serviceType($val["type"])] += $val["num"]*$val["money"];
        }
        foreach($compare as $key=>$val){
            switch($key){
                case 'shjyqtotal':
                    if($val == $_POST['shjyqtotal']){
                        $policyData['shjyqtotal'] = $_POST['shjyqtotal'];
                    }else{
                        exit('请勾选您添加过数量的服务券!');
                    }
                    break;
                case 'shczktotal':
                    if($val == $_POST['shczktotal']){
                        $policyData['shczktotal'] = $_POST['shczktotal'];
                    }else{
                        exit('请勾选您添加过数量的服务券!');
                    }
                    break;
                case 'fwktotal':
                    if($val == $_POST['fwktotal']){
                        $policyData['fwktotal'] = $_POST['fwktotal'];
                    }else{
                        exit('请勾选您添加过数量的服务券!');
                    }
                    break;
                case 'jfktotal':
                    if($val == $_POST['jfktotal']){
                        $policyData['jfktotal'] = $_POST['jfktotal'];
                    }else{
                        exit('请勾选您添加过数量的服务券!');
                    }
                    break;
            }
        }
        //判断金额结束
        writelog("$policyData:",$policyData);
        /**
         * 手动验证数据 开始
         */
        $result = self::checkData($policyData);
        $policyData['policynumber'] = strtoupper($_POST['policynumber']);
        $policyData['merchantid'] = session('adminmerchantid');
        $policyid = '';
        if($result === true){
            //查询商户余额，然后进行比较
            $merchantBalance = M('my_merchant')->where('id='.session('adminmerchantid'))->field('balance,shjyqbalance,shczkbalance,fwkbalance,jfkbalance,name')->find();
            writelog('Policy---saveadd---merchantBalance:',$merchantBalance);

            $totalBalance = floatval($policyData['shjyqtotal']) + floatval($policyData['shczktotal']) + floatval($policyData['fwktotal']) + floatval($policyData['jfktotal']);
            writelog('Policy---saveadd---totalBalance:',$totalBalance);
            $compare = floatval($merchantBalance['balance']) - $totalBalance;
            if($merchantBalance['shjyqbalance'] < $policyData['shjyqtotal']){
                exit('商户-'.$merchantBalance['name'].':石化加油券余额不足');
            }
            if($merchantBalance['shczkbalance'] < $policyData['shczktotal']){
                exit('商户-'.$merchantBalance['name'].':石化充值卡余额不足');
            }
            if($merchantBalance['fwkbalance'] < $policyData['fwktotal']){
                exit('商户-'.$merchantBalance['name'].':服务卡余额不足');
            }
            if($merchantBalance['jfkbalance'] < $policyData['jfktotal']){
                exit('商户-'.$merchantBalance['name'].':积分卡余额不足');
            }
            if($compare<0){
                exit('商户-'.$merchantBalance['name'].':余额不足');
            }
            //扣除商户的钱---对应每一个余额
            $merchantInfoData['shjyqbalance'] = floatval($merchantBalance['shjyqbalance'] - $policyData['shjyqtotal']);
            $merchantInfoData['shczkbalance'] = floatval($merchantBalance['shczkbalance'] - $policyData['shczktotal']);
            $merchantInfoData['fwkbalance'] = floatval($merchantBalance['fwkbalance'] - $policyData['fwktotal']);
            $merchantInfoData['jfkbalance'] = floatval($merchantBalance['jfkbalance'] - $policyData['jfktotal']);
            $merchantInfoData['balance'] = floatval($merchantBalance['balance'] - $totalBalance);
            $merchantflag = M('my_merchant')->where('id='.session('adminmerchantid'))->save($merchantInfoData);
            if(!$merchantflag){
                 exit('商户扣除金额失败.');
            }
            $policyid = $model->table('my_policy')->add($policyData);
        }else{
            exit($result);
        }
        writelog("policyid:-----------",$policyid);
        /*发券管理信息---------dudj-----结束---------------------*/
        if($policyid){//将保单号和服务券关联
            $flag[] = true;
            $resultPolicyServicevoucherData = self::resultPolicyServicevoucher($policyid,$servicevoucheridsNums);
            writelog("resultPolicyServicevoucherData:-----------",$resultPolicyServicevoucherData);
            $policyServicevoucherid = $model->table('my_policyservicevoucher')->addAll($resultPolicyServicevoucherData);//批量插入输入 返回的是插入的第一条数据的主键
            writelog("sql-------------------",$model->getLastSql());
            if( $policyServicevoucherid){
                $flag[] = true;
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
    //@@@@发券列表修改-update@@@@
    public function update(){
        $id = $_GET['id']; 
        $policy = M('my_policy');
        $where['id'] = $id;
        $result = $policy->where($where)->find();
        $c68 = $this->getclass(68,'isauthorizesales',$result['isauthorizesales']);
        $this->assign('c68',$c68);$c74 = $this->getclass(74,'isaudit',$result['isaudit']);
        $this->assign('c74',$c74);

    	$this->assign('result',$result);
        $this->display();
    }

    /**
     * 验证唯一性
     */
    public function saveupdate(){
    	$id = $_POST['id'];
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
    	$policy = M('my_policy');
        $where['id'] = $id;
        $result = self::checkDataByUpdate($_POST);
        unset($_POST['id']);
        if($result === true){
            $_POST['merchantid'] = session('adminmerchantid');
            $policy->where($where)->save($_POST);
            echo 'success';
        }else{
            exit($result);
        }

    }
    
    public function xiangqing(){
        $id = $_GET['id']; 
        $policy = M('my_policy');
        $where['id'] = $id;
        $result = $policy->where($where)->find();
        $c68 = $this->getclass(68,'isauthorizesales',$result['isauthorizesales']);
        $this->assign('c68',$c68);$c74 = $this->getclass(74,'isaudit',$result['isaudit']);
        $this->assign('c74',$c74);
        //查询服务券名称及数量
        $psvData = M('my_policyservicevoucher')->join('LEFT JOIN my_servicevoucher on my_policyservicevoucher.servicevoucherid=my_servicevoucher.id')->field('my_servicevoucher.name,count(my_policyservicevoucher.policyid) as num')->where(['my_policyservicevoucher.policyid'=>$id])->group('my_policyservicevoucher.servicevoucherid')->select();
        $result['serviceinfo'] = $psvData;
        $this->assign('result',$result);
        $this->display();
    }
    //@@@@发券列表删除-del@@@@
    public function del(){
        $flag = [];
    	$id = $_GET['id'];
        $model = new Model();
        $model->startTrans();//开启事务
        $data = $model->table('my_policy')->where('id='.$id)->find();
        if(in_array($data['isaudit'],[76])){
            exit('数据不允许删除');
        }
        //根据policyid，查询生成卡券对应的总额，每一个券对应的服务券个数，每一个服务券的类型以及服务券的额度，计算出每一个类型的总额，并将总额累加到相对应的余额和总余额上---前提是未审核的数据和未使用的数据
        $operateMerchantRes = $this->operateMerchant($model,$id);
        $flag = array_merge($flag,$operateMerchantRes);
        writelog("flag----总额的------",$flag);
        $flag[] = $model->table('my_policy')->delete($id);
        $flag[] = $model->table('my_policyservicevoucher')->where("policyid=".$id)->delete();
        if(empty($flag) || in_array(false,$flag)){
            $model->rollback();
            exit('数据有误');
        }else{
            $model->commit();
            exit('success');
        }
    }
    //
    public function delall(){
        $flag = [];
        $model = new Model();
        $model->startTrans();//开启事务
    	$idlist = $_POST['idlist'];
        $policy = M('my_policy');
        $where['id'] = array('in',$idlist);
        $data = $policy->where($where)->select();
        foreach($data as $key=>$val){
            if(in_array($val['isaudit'],[76])){
                exit('数据不允许删除');
            }
        }
        //根据policyid，查询生成卡券对应的总额，每一个券对应的服务券个数，每一个服务券的类型以及服务券的额度，计算出每一个类型的总额，并将总额累加到相对应的余额和总余额上---前提是未审核的数据和未使用的数据
        $operateMerchantRes = $this->operateMerchant($model,$idlist);
        $flag = array_merge($flag,$operateMerchantRes);
        writelog("flag----总额的------",$flag);
        $flag[] = $model->table('my_policy')->where($where)->delete();
        $where1['policyid'] = array('in',$idlist);
        $flag[] = $model->table('my_policyservicevoucher')->where($where1)->delete();
        if(empty($flag) || in_array(false,$flag)){
            $model->rollback();
            exit('数据有误');
        }else{
            $model->commit();
            exit('success');
        }
    }
    /**
     * @param $policyData
     * @return bool|string
     * 1.手机号必须是正确，且300天内不允许重复
     * 2.营销员手机号必须存在于营销员列表
     * 3.保单号必须保持0~8位 字母默认转大写 不能有汉字
     * 4.保单号唯一
     */
    protected function checkData($policyData){
        if(!preg_match("/^1[345789]\d{9}$/", $policyData['telephone'])){
            return "手机号码格式不正确！";
        }
        if(!preg_match("/^[0-9a-zA-Z]{8}$/", $policyData['policynumber'])){
            return "保单号格式不正确！";
        }
        $policynumber = M("my_policy")->where(["policynumber"=>$policyData['policynumber']])->find();
        if($policynumber){
            return "保单号已存在！";
        }
        $policy = M("my_policy")->where(["telephone"=>$policyData['telephone']])->order("createtime desc")->limit(1)->find();
        if($policy){
            $time = time()-300*24*60*60-strtotime($policy['createtime']);
            if($time<0){
                return "此手机号在最近无法使用！";
            }
        }
        if(intval($policyData['isauthorizesales']) == 69){
            if($policyData['salesmanphone'] == ''){
                return "此营销员手机号不能为空！";
            }
            if(!preg_match("/^1[345789]\d{9}$/", $policyData['salesmanphone'])){
                return "营销员手机号码格式不正确！";
            }
            $salesmaninfo = M("my_salesmaninfo")->where(["phone"=>$policyData['salesmanphone']])->find();
            if(!$salesmaninfo){
                return "此营销员手机号无法使用！";
            }
        }
        return true;
    }
    /**
     * @param $policyData
     * @return bool|string
     * 修改的时候  需要确保数据唯一
     * 1.手机号必须是正确，且300天内不允许重复
     * 2.营销员手机号必须存在于营销员列表
     * 3.保单号必须保持0~8位 字母默认转大写 不能有汉字
     * 4.保单号唯一
     */
    protected function checkDataByUpdate($policyData){
        if(!preg_match("/^1[345789]\d{9}$/", $policyData['telephone'])){
            return "手机号码格式不正确！";
        }
        if(!preg_match("/^[0-9a-zA-Z]{8}$/", $policyData['policynumber'])){
            return "保单号格式不正确！";
        }
        $where['id'] = ['neq',$policyData['id']];
        $policunumberWhere['policynumber'] = $policyData['policynumber'];
        $policynumber = M("my_policy")->where(array_merge($where,$policunumberWhere))->find();
        if($policynumber){
            return "保单号已存在！";
        }
        $telephoneWhere['telephone'] = $policyData['telephone'];
        $policy = M("my_policy")->where(array_merge($where,$telephoneWhere))->order("createtime desc")->limit(1)->find();
        if($policy){
            $time = time()-300*24*60*60-strtotime($policy['createtime']);
            if($time<0){
                return "最近无法修改成此手机号！";
            }
        }
        return true;
    }

    /**
     * @param $policyid
     * @param $data
     * @return array
     * 处理保单和服务券关联信息
     */
    protected function resultPolicyServicevoucher($policyid,$data){
        $arrs = [];
        foreach($data as $key=>$val){
            for($i=0;$i<$val['num'];$i++){
                $arr = [];
                if(in_array($val['type'],[92,93])){//石化加油券
                    //随机取出---券的面额，券的类型
                    $arr['petroinfoid'] = '1';
                    $arr['oilcode'] = '测试中';
                }
                //没有值 默认不授权 73  默认激活 判断是否审核即可 使用
                $arr = [
                    'status'=>20,
                    'usestatus'=>33,
                    'issynchrogrant'=>$val["issynchrogrant"]?$val["issynchrogrant"]:73,
                    'servicevoucherid'=>$val['id'],
                    'policyid'=>$policyid,
                    'adminid'=>$_SESSION['adminid'],
                    'createtime'=>date('Y-m-d H:i:s'),
                    'updatetime'=>date('Y-m-d H:i:s'),
                    'merchantid'=>session('adminmerchantid'),
                ];
                $arrs[] = $arr;
            }
        }
        return $arrs;
    }

    /**
     *  保单批量审核
     */
    public function auditall(){
        $idlist = $_POST["idlist"];
        if($idlist){
            //判断是否包含逗号 也就是判断是否为多个数据
            if(strrpos($idlist,',')){
                $idlistArr = explode(',',$idlist);
            }else{
                $idlistArr = [$idlist];
            }
            $data = [];
            $count = count($idlistArr);
            for($i=0;$i<$count;$i++){
                $arr['id'] = $idlistArr[$i];
                $arr['isaudit'] = 76;
                $arr['audittime'] = date('Y-m-d H:i:s');
                $data[] = $arr;
            }
            //批量修改审核状态
            $res = batch_update('my_policy',$data,'id');
            if($res){
                exit('success');
            }else{
                exit('审核失败，请查看数据');
            }
        }else{
            exit('审核失败，请查看数据');
        }
    }

    /**
     * 批量导入积分卡
     * 切记 保单号必须是八位 且不能与数据库重复
     * 手机号必须正确
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
                    $data[$j]['policynumber'] = (string)$PHPExcel->getActiveSheet()->getCell("A".$i)->getValue();
                    $data[$j]['telephone'] = (string)$PHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
                    $data[$j]['jfktotal'] = (string)$PHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
                    $data[$j]['createtime'] = date("Y-m-d H:i:s",time());//使用状态-未使用
                    $data[$j]['adminid'] = $_SESSION['id'];//使用状态-未使用
                    $j++;
                }
                $m = D("my_policy"); // 打开表
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
    public function morecondition(){
        $this->display();
    }

    /**
     * 查询商户的信息
     */
    public function list_page_more(){
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
            $model = new OperablerelationModel();
            $result = $model->userhavemerchat();
            $where = 'where `my_merchant`.`id` in('.$result.') and ';
        }

        if(!empty($starttime)){
            $starttime = strtotime($starttime);
            $where .=" UNIX_TIMESTAMP(`my_merchant`.`createtime`)>='".$starttime."' and";
        }
        if(!empty($endtime)){
            $endtime = strtotime($endtime);
            $where .=" UNIX_TIMESTAMP(`my_merchant`.`createtime`)<='".$endtime."' and";
        }
        if($_POST['name'] <> ''){
            $where .= " `my_merchant`.`name` like '%".$_POST['name']."%' and ";
        }
        $where = trim($where,' and');
        $sql = "SELECT
        `my_policyservicevoucher`.`id`,
        `my_policyservicevoucher`.`merchantid`,
        `my_merchant`.`name`,
        sum(
            `my_servicevoucher`.`money`
        ) AS `money`,
        `my_servicevoucher`.type,
        `my_merchant`.`createtime`
    FROM
        `my_policyservicevoucher`
    LEFT JOIN `my_merchant` ON `my_policyservicevoucher`.`merchantid` = `my_merchant`.`id`
    LEFT JOIN `my_policy` ON `my_policyservicevoucher`.`policyid` = `my_policy`.`id`
    LEFT JOIN `my_servicevoucher` ON `my_policyservicevoucher`.`servicevoucherid` = `my_servicevoucher`.`id`
 ".$where." GROUP BY
	`my_servicevoucher`.`type`,
	`my_policyservicevoucher`.`merchantid` order by `my_policyservicevoucher`.`".$sort."` ".$order;
        $result = $this->pagelist_($sql,$_POST);
        $data = [];
        foreach($result['rows'] as $key=>$val){
            if(is_array($data[$val['merchantid']])){
                $appendData = [str_replace('total','totalsum',serviceType($val['type']))=>$val['money']];
                $data[$val['merchantid']] = array_merge($data[$val['merchantid']],$appendData);
            }else{
                $data[$val['merchantid']] = [
                    'id'=>$val['merchantid'],
                    'name'=>$val['name'],
                    str_replace('total','totalsum',serviceType($val['type']))=>$val['money'],
                    'createtime'=>$val['createtime'],
                ];
            }
        }
        $resultDatas = [];
        foreach ($data as $key=>$val){
            $resultDatas[] = $val;
        }
        $arr['rows'] = $resultDatas;
        $arr['total'] = count($resultDatas);
        //导出逻辑
        if($_POST['daochu']){
            $this->export_xls('policy',array('ID','商户名','石化加油券','石化充值卡','服务卡','积分卡','创建时间'),$arr['rows']);
            $res = array('result'=>true,'data'=>'','message'=>'');
            echo json_encode($res);exit;
        }
        //导出逻辑结束
        echo json_encode($arr);
    }
    /**
     * @param $model
     * @param $id
     * @return array
     * 删除发券信息  将商户对应的余额和对应商户类型的余额回退账户
     */
    private function operateMerchant($model,$id)
    {
        $data = [];
        if ($id) {
            $where = 'where `ps`.`policyid` in(' . $id . ') and ';
        } else {
            exit('数据不允许操作');
        }
        $where = trim($where, ' and');
        $sql = 'SELECT
            ps.policyid,p.policynumber,p.fwktotal as pfwktotal,p.jfktotal as pjfktotal,p.shczktotal as pshczktotal,p.shjyqtotal as pshjyqtotal,s.`name` as sname,ps.merchantid,m.`name` as mname,s.money,s.`type`,sum(s.money) AS summoney,count(1) as nums
        FROM
            my_policyservicevoucher ps
        LEFT JOIN my_servicevoucher s ON ps.servicevoucherid = s.id
        LEFT JOIN my_policy p ON ps.policyid = p.id
        LEFT JOIN my_merchant m ON ps.merchantid = m.id ' . $where . ' and ps.usestatus = 33
        GROUP BY
            ps.policyid,
            s.type';
        writelog("sql----SQL------",$sql);
        $_POST['page'] = 1;
        $_POST['rows'] = 15;
        $result = $this->pagelist_($sql, $_POST);
        writelog("result----result------",$result);
        foreach($result['rows'] as $key=>$val){
            writelog("result----val------",$val);
            $merchantData = [];
            $operatefield = serviceType(intval($val['type']));
            $memchantInfo = $model->table("my_merchant")->where("id=".$val["merchantid"])->find();
            writelog("---商户信息---",$memchantInfo);
            if($val["p".$operatefield] == $val["summoney"]){
                $merchantData[str_replace("total","balance",$operatefield)] = floatval($memchantInfo[str_replace("total","balance",$operatefield)]) + floatval($val["summoney"]);
                $merchantData["balance"] = floatval($memchantInfo["balance"]) + floatval($val["summoney"]);
                writelog("merchantData----merchantData------",$merchantData);
                $data[] = $model->table('my_merchant')->where("id=".$val['merchantid'])->save($merchantData);
                writelog("data----data------",$data);
            }else{
                $data[] = false;
            }
        }
        return $data;
    }
}