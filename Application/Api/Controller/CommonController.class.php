<?php
namespace Api\Controller;
use Think\Controller;
class CommonController extends BaseController {

	/**
	提交意见反馈
	*/
	public function tellusadd(){
		$uinfo 	= $this->TokenToUinfoFalse();
		if($uinfo){
			$data['userid'] = $uinfo['id'];
		}
		$data['content'] 		= $this->CheckI('content');
		$data['usertel'] 		= I('param.usertel');
		$data['createtime'] 	= date('Y-m-d H:i:s');
		$data['updatetime'] 	= $data['createtime'];

		M('feedback')->data($data)->add();
		$this->Rsuccess();
	}

	/**
	*城市列表
	*/
	/*public function citylist(){
		$list = M('allcity')->where(array('status'=>1))->field('cityid,cityname,enword')->select();
		$this->Rsuccess($list);
	}*/

	/**
	发送短信
	*/
	public function sendmessage(){
		$data['mobile'] 	= I('param.mobile');
		//验证
		if (!preg_match('/^1[\d]{10}$/', $data['mobile'])){
    		$this->Rerror('手机号码格式错误');
    	}
    	$model 	= M('sendmess');
    	//短信频繁度验证避免浪费短信包同一号码30秒只能发1次
    	$last 	= $model->where(array('mobile'=>$data['mobile'],'stype'=>1,'ctime'=>array('gt',time()-30)))->order('id desc')->find();
    	if ($last){
    		$this->Rerror('短信发送的太频繁了');
    	}
    	$data['lip'] 		= get_client_ip();
    	//IP频繁度验证避免浪费短信包同一IP1小时只能发50次
    	$lips 	= $model->where(array('lip'=>$data['lip'],'stype'=>1,'ctime'=>array('gt',time()-3600)))->count();
    	if($lips > 49){
    		$this->Rerror('短信发送的太频繁了');
    	}
    	$String 			= new \Org\Util\String();
    	$data['message'] 	= $String->randString(4,1);
    	$data['ctime'] 		= time();
    	$data['stype'] 		= 1;

    	$data['message'] 	= '1234';

    	//vendor('Cloopen.CCPRestSmsSDK');
    	//vendor('Cloopen.SendTemplateSMS');

    	//$send = sendTemplateSMS($data['mobile'],array($data['message'],5),32488);
    	$send['status'] = 1;
    	//↓↓↓↓↓↓↓↓↓↓商户逻辑↓↓↓↓↓↓↓↓↓↓
    	if($send['status']){
    		if($model->data($data)->add()){
    			$this->Rsuccess();
    		}else {
    			$this->Rerror('短信发送失败');
    		}
    	}else {
    		$this->Rerror($send['msg']);
    	}
	}

	/**
	H5视图
	*/
	public function webview(){
		$actkey = I('param.actkey');
		$actval = I('param.actval',0,'intval');

		switch ($actkey) {
			case 'timebuy':
				$content = M('timeproduct')->where(array('id'=>$actval))->getField('content');
				break;
			
			default:
				# code...
				break;
		}

		$this->assign('content',$content);
		$this->display();
	}

	/**
	全部分类
	*/
	public function catelist(){
		$tmp = array();
		$tmp['title'] 	= '服装';
		$tmp['actkey']  = 'dress';
		$tmp['child'] 	= M('class')->where(array('parentid'=>39))->field('id cateid,name title')->select();
		$tmp['child'] 	= $tmp['child'] ? $tmp['child'] : array();
		$data[] 		= $tmp;

		$tmp = array();
		$tmp['title'] 	= '美食';
		$tmp['actkey']  = 'food';
		$tmp['child'] 	= M('class')->where(array('parentid'=>45))->field('id cateid,name title')->select();
		$tmp['child'] 	= $tmp['child'] ? $tmp['child'] : array();
		$data[] 		= $tmp;

		$tmp = array();
		$tmp['title'] 	= '酒店';
		$tmp['actkey']  = 'hotel';
		$tmp['child'] 	= M('class')->where(array('parentid'=>51))->field('id cateid,name title')->select();
		$tmp['child'] 	= $tmp['child'] ? $tmp['child'] : array();
		$data[] 		= $tmp;

		$tmp = array();
		$tmp['title'] 	= '旅行';
		$tmp['actkey']  = 'travel';
		$tmp['child'] 	= M('class')->where(array('parentid'=>64))->field('id cateid,name title')->select();
		$tmp['child'] 	= $tmp['child'] ? $tmp['child'] : array();
		$data[] 		= $tmp;

		$tmp = array();
		$tmp['title'] 	= '便民';
		$tmp['actkey']  = 'life';
		$tmp['child'] 	= M('class')->where(array('parentid'=>69))->field('id cateid,name title')->select();
		$tmp['child'] 	= $tmp['child'] ? $tmp['child'] : array();
		$data[] 		= $tmp;

		$tmp = array();
		$tmp['title'] 	= '淘婚';
		$tmp['actkey']  = 'marry';
		$tmp['child'] 	= M('marryclass')->field('id cateid,title')->select();
		$tmp['child'] 	= $tmp['child'] ? $tmp['child'] : array();
		$data[] 		= $tmp;

		$tmp = array();
		$tmp['title'] 	= '超市';
		$tmp['actkey']  = 'market';
		$tmp['child'] 	= M('class')->where(array('parentid'=>24))->field('id cateid,name title')->select();
		$tmp['child'] 	= $tmp['child'] ? $tmp['child'] : array();
		$data[] 		= $tmp;

		$tmp = array();
		$tmp['title'] 	= '扫货';
		$tmp['actkey']  = 'timebuy';
		$tmp['child'] 	= M('class')->where(array('parentid'=>33))->field('id cateid,name title')->order('`order` asc')->select();
		$tmp['child'] 	= $tmp['child'] ? $tmp['child'] : array();
		$data[] 		= $tmp;

		$tmp = array();
		$tmp['title'] 	= '口碑';
		$tmp['actkey'] 	= 'rank';
		$tmp['child'] 	= null;
		$tmp['child'] 	= $tmp['child'] ? $tmp['child'] : array();
		$data[] 		= $tmp;

		$tmp = array();
		$tmp['title'] 	= '农业';
		$tmp['actkey'] 	= 'agrproduct';
		$tmp['child'] 	= M('agrclass')->field('id cateid,title')->select();
		$tmp['child'] 	= $tmp['child'] ? $tmp['child'] : array();
		$data[] 		= $tmp;

		$this->Rsuccess($data);

	}

	public function sign(){
		$uinfo = $this->TokenToUinfoTrue();

		$ctime = strtotime('today');
		$etime = $ctime+86400-1;
		$check = M('integrallog')->where(array('userid'=>$uinfo['id'],'UNIX_TIMESTAMP(createtime)'=>array('BETWEEN',array($ctime,$etime))))->find();
		if($check){
			$this->Rerror('您今天已经签到了');
		}
		$data['createtime'] = date('Y-m-d H:i:s');
		$data['updatetime'] = $data['createtime'];
		$data['userid'] 	= $uinfo['id'];
		$data['actval'] 	= 10;
		M('integrallog')->data($data)->add();
		$this->Rsuccess();
	}

	/**
	广告列表
	*/
	public function advlist(){
		$cateid = $this->CheckI('cateid',0,'intval');

		$list 	= M('advert')->where(array('cateid'=>$cateid))->field('picture')->limit(5)->select();
		foreach ($list as &$lv){
			if($lv['picture']){
				$lv['picture'] = PICBASEURL.$lv['picture'];
			}
		}
		$this->Rsuccess($list);
	}

	/**
	接口列表
	*/
	public function face(){
		$list = M('face')->select();
		$this->assign('list',$list);
		$this->display();
	}

}