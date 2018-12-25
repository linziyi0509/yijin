<?php
namespace Home\Controller;
use Think\Controller;

/**
 *【客户扫码控制器】
 */
class ScanController extends BaseController {

    public function getOpenid()
    {
        $weObj = wechat_connect();
        $url = 'http://'.$_SERVER['HTTP_HOST'].U('index');
        $share = $weObj->getOauthRedirect($url);
        header('Location:'.$share);
    }

	//汽车增值服务兑换页
    public function index()
    {
        /*$weObj = wechat_connect();
        $info = $weObj->getOauthAccessToken();
        
        if ($info == false) {
           $this->redirect('getOpenid');
        }
        if (empty(session('userinfo'))) {
            // 缓存初始化
            $this->saveInfo($info['openid']);
        }else{
            $this->saveInfo(session('userinfo')['openid']);
        }*/
    	$this->display();  
    }

    //卡卷验证
    public function add()
    {

        //数组分割
        $data = explode(',',I('post.id'));  
        $code = $data[count($data)-1];       
        $verify = new \Think\Verify();
        if (!$verify->check($code) ) {
            $this->ajaxReturn(['code'=>101,'string'=>'验证码填写错误']);
        } 
        //去除验证码信息
        array_pop($data);
        $redeemcode = M('my_redeemcode');
        $really = [];    //存在
        $fake   = [];   //不存在
        foreach ($data as $val ) {
            //根据卡卷码去查询是否由此id
            $code = $redeemcode->where(['code'=>$val])->find();
            if ($code) {
                $result = $this->updStatus($redeemcode,$val);
                if ($result) {
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
     * @param string $table 表
     * @param string $code  卡卷码
     * @return bool
     */
    public function updStatus($table,$code)
    {
        $data = array(
            'updatetime'=> date('Y-m-d H:i:s',time()),//修改时间
            'wechatuserid'=> session('userinfo')['id']
        );
        return $table->where(['code'=>$code,'status'=>24,'usestatus'=>27])->save($data);
    }

    public function lst()
    {
        
        //全部的
        /*$all = M('my_redeemcode as r')
            ->join('my_servicevoucher as s on s.id=r.servicevoucherid')
            ->field('r.servername,r.code,s.name,s.integral,s.starttime,s.endtime')
            ->where(['wechatuserid'=>session('userinfo')['id'],'servicevoucherid'=>['neq','']])
            ->select();*/
        $all = M('my_redeemcode as r')
            ->join('my_servicevoucher as s on s.id=r.servicevoucherid')
            ->field('r.servername,r.code,r.status,r.usestatus,s.name,s.integral,s.starttime,s.endtime')
            ->where(['wechatuserid'=>5,'servicevoucherid'=>['neq','']])
            ->select();
        $time = time();
        $useCode = []; //已使用
        $usedCode = []; //未使用
        $overCode = [];  //已过期
        foreach ($all as $k => $val ) {
            //获取已经使用的总换码
            if ($val['usestatus'] == 26) {
                $useCode[] = $val;
            }else if ($val['usestatus'] == 27 && strtotime($val['endtime']) > $time) {
                //获取到未使用并且没有过期的
                $usedCode[] = $val;
            }else if ( strtotime($val['endtime']) < $time){
                //获取已经过期的
                $overCode[] = $val;
            }
        }
        $this->list = [
            'all'       =>  $all,
            'useCode'   =>  $useCode,
            'usedCode'  =>  $usedCode,
            'overCode'  =>  $overCode
        ];
        //dump($this->list);die;
        $this->display();
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





}