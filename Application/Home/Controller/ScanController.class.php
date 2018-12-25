<?php
namespace Home\Controller;
use Think\Controller;
use Think\Model;

/**
 *【客户扫码控制器】
 */
class ScanController extends BaseController {

	//汽车增值服务兑换页
    public function index()
    {

    	$this->display();  
    }
    /**
     * 根据卡密 兑换卡号和使用卡密
     * 并且将关联表信息修改-关联表(服务券、卡片、卡券、用户)
     */
    public function add()
    {
        $this->selPhone();
        //数组分割
        $data = explode(',',I('post.id'));
        $vcode = $data[count($data)-1];
        $verify = new \Think\Verify();
        if (!$verify->check($vcode) ) {
            $this->ajaxReturn(['code'=>101,'string'=>'验证码填写错误']);
        } 
        //去除验证码信息
        array_pop($data);
        $cards          = M('my_cards');
        $cardservicevoucher  = M('my_cardservicevoucher');
        $cardcoupons  = M('my_cardcoupons');
        $merchant       = M('my_merchant');
        $really = [];    //存在
        $fake   = [];   //不存在
        foreach ($data as $val ) {
            //根据卡密去查询是否有数据
            $code = $cards
                    ->field('id,cardcouponsid,merchantid,status,usestatus')
                    ->where(['cardpwd'=>trim($val)])
                    ->find();                    
            if ( $code ) {
                //判断卡密是否是激活并且没有使用状态
                if ($code['status'] == 30 && $code['usestatus'] == 33 ) {
                    //查询卡卷的面值
                    $credit = $cardcoupons->where(['id'=>$code['cardcouponsid']])->getField('credit');
                    //商户余额
                    $info = $merchant
                            ->field('balance,operation')
                            ->where(['id'=>$code['merchantid']])
                            ->find();
                    //判断商户余额是否够用或者是可预支
                    if ($info['balance'] > $credit || $info['operation'] == 14 ) {
                        //余额减去面值钱
                        $price = $info['balance']-$credit;
                        //修改商户余额
                        $map = ['balance'=>$price];
                        $chant = $merchant->where(['id'=>$code['merchantid']])->save($map);
                        //修改卡片信息
                        $data = array(
                            'updatetime'    => date('Y-m-d H:i:s',time()),//修改时间
                            'usestatus'     =>  32,
                            'wechatuserid'  => session('userinfo')['id']
                        );
                        $result = $cards->where(['id'=>$code['id']])->save($data);
                        $cardservicevoucherdata = [
                            'wechatuserid' => session('userinfo')['id'],
                            'starttime' => date('Y-m-d H:i:s',time()),
                            'endtime' => date("Y-m-d H:i:s",strtotime('+1 year'))
                        ];
                        //修改关联表信息 将所属用户修改
                        $csvresult = $cardservicevoucher->where(['cardsid'=>$code['id']])->save($cardservicevoucherdata);
                        //是否正确
                        if ($chant && $result && $csvresult) {
                            //卡卷码正确
                            $really[] = $val;
                        }else{
                            //卡卷码错误
                            $fake[] = $val;
                        }
                    }else{
                        //卡卷码错误
                        $fake[] = $val;
                    }
                }else{
                    //卡卷码错误
                    $fake[] = $val;
                }                                            
            }else{
                //卡卷码错误
                $fake[] = $val;
            }
        }
        $this->ajaxReturn([
            'code'  =>  200,
            'really'=>$really,
            'fake'=>$fake,
            'string'=>count($really).'个充值码成功,'.count($fake).'个充值码失败'
        ]);
        
    }
     /**
     * @offcet 修改卡卷码的状态
     * @param string $table 卡卷表
     * @param string $merchant 商户表
     * @param int $merchantid 商户id
     * @param string $code  卡卷码
     * @return bool
     */
    public function updStatus($table,$code)
    {   
        //修改信息
        $data = array(
            'updatetime'    => date('Y-m-d H:i:s',time()),//修改时间
            'usestatus'     =>  32,
            'wechatuserid'  => session('userinfo')['id']
        );
        return $table->where(['code'=>$code])->save($data);
    }
    /**
     * 列表查询数据
     * 服务券信息
     */
    public function lst()
    {
        if ( IS_POST ) {
            //使用事务 进行数据的插入
            $model = new Model();
            $model->startTrans();//开启事务
            $flag = [];
            $id = I('post.id');
            $card = M('my_cardservicevoucher')
                ->field('servicevoucherid,cardsid')
                ->where(['id'=>$id,'usestatus'=>33,'status'=>20])//未使用并激活
                ->find();
            //需要对card数据进行验证
            if(!$card){
                $this->ajaxReturn(['code'=>101,'string'=>'信息有误，请联系管理员']);
            }
            $servicevoucher = M('my_servicevoucher')
                ->field('id,name,integral')
                ->where(['id'=>$card['servicevoucherid']])
                ->find();
            if(!$servicevoucher){
                $this->ajaxReturn(['code'=>101,'string'=>'信息有误，请联系管理员']);
            }
            //查询对应兑换码的数据
            $redeemcode = M('my_redeemcode')
                ->field('id')
                ->where("servicevoucherid = %d and status = %d and usestatus = %d", array($card['servicevoucherid'], 24, 27))
                ->select();
            if(!$redeemcode){
                $this->ajaxReturn(['code'=>101,'string'=>'信息有误，请联系管理员']);
            }
            $redeemcodeid = $redeemcode[array_rand($redeemcode,1)]['id'];//取出一个兑换码
            $csvData = array(
                'usestatus' =>  32,    
                'usetime'   =>  date('Y-m-d H:i:s',time()),//使用时间
            );
            $poData = array(
                'createtime'  => date('Y-m-d H:i:s',time()),
                'wechatuserid' => session('userinfo')['id'],
                'username'      =>  session('userinfo')['nickname'],
                'telephone'     =>  session('userinfo')['phone'],
                'credit'        =>  $servicevoucher['integral'],  //积分
                //'money'         =>  round($servicevoucher['integral']/10,2), //金额(元)
				'money'         =>  0, //金额(元)
                'paidflag'      =>  36,  //订单状态
                'ordernum'      =>  date('YmdHis',time()).rand(100,999), //订单号
                'batch'         =>  date('Y-m-d H:i:s',time()), //交易号
                'payingtype'    =>  42,
                'ipaddr'        =>  get_client_ip(),
                'ordertype'     =>  39

            );
            //修改状态和使用时间
            $flag[] = $model->table('my_cardservicevoucher')->where(['id'=>$id])->save($csvData);
            $payingordersid = $model->table('my_payingorders')->add($poData);
            
            if($payingordersid){
                $flag[] = true;
                $buyingmodofcustomerData = [
                    'createtime'  => date('Y-m-d H:i:s',time()),
                    'updatetime'  => date('Y-m-d H:i:s',time()),
                    'payingordersid'  => $payingordersid,
                    'servicevoucherid'  => $card['servicevoucherid'],
                    'usestatus'  => 32,
                    'credit'  => $servicevoucher['integral'],
                    'redeemcodeid'=>$redeemcodeid//随机出对应的兑换码
                ];
                $buyingmodofcustomerid = $model->table('my_buyingmodofcustomer')->add($buyingmodofcustomerData);
                if($buyingmodofcustomerid){
                    $flag[] = true;
                    //修改兑换码的使用情况
                    $redeemcodeData = [
                        'updatetime' => date('Y-m-d H:i:s',time()),
                        'wechatuserid' =>session('userinfo')['id'],
                        'usestatus' => 26,
						'cardservicevoucherid'	=> $id
                    ];
                    $redeemcoderes = $model->table('my_redeemcode')->where(['id'=>$redeemcodeid])->save($redeemcodeData);
                    //用户表数据增加积分
                    //$user =  $model->table("my_wechatuser")->where(['id'=>session('userinfo')['id']])->setInc('credits',$servicevoucher['integral']); 
                    if( $redeemcoderes){
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
                $this->ajaxReturn(['code'=>101,'string'=>'使用失败']);
            }else{
                $model->commit();
				$servicevoucherid = M('my_cardservicevoucher')->where(['id'=>$id])->getField('servicevoucherid');
				$url = M('my_servicevoucher')->where(['id'=>$servicevoucherid])->getField('exchangeurl');
				$code = M('my_redeemcode')->where(['cardservicevoucherid' => $id])->getField('code');
                $this->ajaxReturn(['code'=>200,'string'=>'使用成功','url'=>$url,'codes'=>$code]);
            }
            return;
        }
        //全部的
        $table = M("")
                ->table('my_cardservicevoucher s,my_servicevoucher r')
                ->field('s.id,s.wechatuserid,s.servicevoucherid,s.usestatus,s.status,s.starttime,s.endtime,r.name,r.integral,r.instructions,r.icon,r.exchangeurl')
                ->where("s.servicevoucherid = r.id and s.wechatuserid = ".session('userinfo')['id']." and s.starttime is not null and s.endtime is not null")
                ->select();
		
        foreach($table as $key=>$val){
            $table[$key]["starttime"] = strtotime($val["starttime"]);
            $table[$key]["endtime"] = strtotime($val["endtime"]);
        }
        
        $time = time();
        $useCode = []; //已使用
        $usedCode = []; //未使用
        $overCode = [];  //已过期
        foreach ($table as $k => $val ) {
            if ($val['usestatus'] == 32 && $val['endtime'] > $time) {//已使用
                $table[$k]['flag'] = 0;
                $useCode[] = $val;
            }else if ($val['usestatus'] == 33 && $val['status'] == 20 && $val['endtime'] > $time) {//未使用
                $usedCode[] = $val;
                $table[$k]['flag'] = 1;
            }else if ($val['endtime'] < $time){
                //获取已经过期的
                $overCode[] = $val;
                $table[$k]['flag'] = -1;
            }
        }
		//dump($usedCode);die;
        $this->list = [
            'all'       =>  $table,
            'useCode'   =>  $useCode,
            'usedCode'  =>  $usedCode,
            'overCode'  =>  $overCode
        ];
        $this->display();
    }
	//点击查看内容	
	public function getFind() {
		$id = I('post.id');
        //查看一次 修改订单详情的状态
		$servicevoucherid = M('my_cardservicevoucher')->where(['id'=>$id])->getField('servicevoucherid');
		$url = M('my_servicevoucher')->where(['id'=>$servicevoucherid])->getField('exchangeurl');
		$code = M('my_redeemcode')->where(['cardservicevoucherid' => $id])->getField('code');
        $bocData = M('my_buyingmodofcustomer')->where(['cardservicevoucherid' => $id])->field('usestatus')->find();
        if($bocData['usestatus'] == 33){
            $buyingmodofcustomerRes = M('my_buyingmodofcustomer')->where(['cardservicevoucherid' => $id])->save(['usestatus'=>32]);
        }
		$this->ajaxReturn(['url'=>$url,'code'=>$code]);
	}
	
    //验证码
    public function Verify()
    {
    	//清除缓存
	    ob_end_clean();
	    $verify = new \Think\Verify();
	    //$verify->fontSize = 30;
    	$verify->length   = 4;
    	$verify->useNoise = false;
    	$verify->useCurve = false;
    	$verify->codeSet = '0123456789'; 
	    $verify->entry();
    }

    /**
     * 转让列表数据 --- 只显示未使用的券
     */
    public function TransferLst(){
        if ( IS_POST ) {
            $this->selPhone();
            //使用事务 进行数据的插入
            $model = new Model();
            $model->startTrans();//开启事务
            $flag = [];
            $id = I('post.id');     
            $price = 0;
            $buyingmodofcustomerData = [];
            foreach ($id as $key => $val) 
            {
				if (!empty($val)) {
					$card = M('my_cardservicevoucher')
						->field('servicevoucherid,cardsid')
						->where(['id'=>$val,'usestatus'=>33,'status'=>20])//未使用并激活
						->find();
					//需要对card数据进行验证
					if( !$card ){
						$this->ajaxReturn(['code'=>101,'string'=>'信息有误，请联系管理员']);
					}
					$servicevoucher = M('my_servicevoucher')
						->field('id,name,integral')
						->where(['id'=>$card['servicevoucherid']])
						->find();      
					if(!$servicevoucher){
						$this->ajaxReturn(['code'=>101,'string'=>'信息有误，请联系管理员']);
					}
					//查询对应兑换码的数据---转让的订单 无须获取兑换码
//					$where =array(
//						'servicevoucherid'=>(int)$card['servicevoucherid'],'status'=>24,'usestatus' =>27
//					);
//					$redeemcode = M('my_redeemcode')->field('id')->where($where)->select();
//					if(!$redeemcode){
//						$this->ajaxReturn(['code'=>101,'string'=>'信息有误，请联系管理员']);
//					}
//					$redeemcodeid = $redeemcode[array_rand($redeemcode,1)]['id'];//取出一个兑换码
					$csvData = array(
						'usestatus' =>  50,
						'usetime'   =>  date('Y-m-d H:i:s',time()),//使用时间
					);
					//修改状态和使用时间
					$flag[] = $model->table('my_cardservicevoucher')->where(['id'=>$val])->save($csvData);
					$price += (int)$servicevoucher['integral'];
					//订单详情
					$buyingmodofcustomerData[] = [
						'createtime'  => date('Y-m-d H:i:s',time()),
						'updatetime'  => date('Y-m-d H:i:s',time()),
						'servicevoucherid'  => $card['servicevoucherid'],
						'usestatus'  => 50,
						'credit'  => $servicevoucher['integral'],
						'cardservicevoucherid'=>$val,
						'redeemcodeid'=>''//转让订单 不给 对应的兑换码
					];
				}
                
            }
            //生成订单
            $poData = array(
                'createtime'    => date('Y-m-d H:i:s',time()),
                'wechatuserid'  => session('userinfo')['id'],
                'username'      =>  session('userinfo')['nickname'],
                'telephone'     =>  session('userinfo')['phone'],
                'credit'        =>  $price,  //积分
                'money'         =>  round($price/100,2), //金额(元)
                'paidflag'      =>  36,  //订单状态
                'ordernum'      =>  date('YmdHis',time()).rand(100,999), //订单号
                'batch'         =>  date('Y-m-d H:i:s',time()), //交易号
                'payingtype'    =>  42,
                'ipaddr'        =>  get_client_ip(),
                'ordertype'     =>  40,
            );
                       
            $payingordersid = $model->table('my_payingorders')->add($poData);

            if($payingordersid){
                $flag[] = true;
                foreach ($buyingmodofcustomerData as $k=>$va ) {
                    $buyingmodofcustomerData[$k]['payingordersid']= $payingordersid;
                     //修改兑换码的使用情况
                    /*$redeemcodeData = [
                        'updatetime' => date('Y-m-d H:i:s',time()),
                        'wechatuserid' =>session('userinfo')['id'],
                        'usestatus' => 26
                    ];
                    $redeemcoderes = $model->table('my_redeemcode')->where(['id'=>$va['redeemcodeid']])->save($redeemcodeData);*/
                }

                $buyingmodofcustomerid = $model->table('my_buyingmodofcustomer')->addAll($buyingmodofcustomerData);
//                if($buyingmodofcustomerid && $redeemcoderes){
                if($buyingmodofcustomerid){
                    //用户表数据增加积分
                    $user =  $model->table("my_wechatuser")->where(['id'=>session('userinfo')['id']])->setInc('credits',$price); 
                    if ($user) {
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
                $this->ajaxReturn(['code'=>101,'string'=>'使用失败']);
            }else{
                $model->commit();
                $this->ajaxReturn(['code'=>200,'string'=>'使用成功']);
            }
            return;
        }
        //查询数据
       $transData = M("")
            ->table('my_cardservicevoucher c,my_servicevoucher s')
            ->field('c.id,c.wechatuserid,c.servicevoucherid,c.usestatus,c.status,c.starttime,c.endtime,s.name,s.integral,s.transfer')
            ->where("c.servicevoucherid = s.id and c.wechatuserid = ".session('userinfo')['id']." and c.starttime is not null and c.endtime is not null and usestatus = 33 and status = 20")
            ->select();
             //查询数据
        $time = time();
        foreach($transData as $key=>$val){
            if(strtotime($val['endtime']) > $time){
                $transData[$key]["starttime"] = strtotime($val["starttime"]);
                $transData[$key]["endtime"] = strtotime($val["endtime"]);
            }
        }
        $this->transData = $transData;
        $this->display();
    }
}
