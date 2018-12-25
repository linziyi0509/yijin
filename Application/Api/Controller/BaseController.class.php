<?php
namespace Api\Controller;
use Think\Controller;
class BaseController extends Controller {

    public function __construct(){
        parent::__construct();
        foreach ($_REQUEST as $key => $value) {
            $canshu .= $key.'='.$value.'&';
        }
        $canshu = trim($canshu,'&');
        addlog(array('url'=>'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'].'?'.$canshu),'api-'.ACTION_NAME,'---接口地址----：');
        addlog($_REQUEST,'api-'.ACTION_NAME,'接收参数：');
        addlog($_FILES,'api-'.ACTION_NAME,'接收文件');
    }

     public function Rsuccess($mess='',$data=array()){
        if (!$data) {
            $data = array();
        }
        $data = array('code'=>'1000','message'=>$mess,'data'=>$data);
        $data = json_encode($data);
        $data = str_replace('null','""',$data);
        echo $data;
        exit;
    }
    public function Rerror($mess=''){
        $data = array('code'=>'1001','message'=>$mess,'data'=>array());
        echo json_encode($data);
        exit;
    }


    public function CheckI($key, $default='', $func='htmlspecialchars'){
        $return     = I('param.'.$key,$default,$func);
        if(!$return){
            $this->Rerror('缺少参数'.$key);
        }
        return $return;
    }

    public function TokenToUinfoTrue(){
        $userid = $this->CheckI('userid',0,'intval');
        $token  = $this->CheckI('token');
        $info   = M('user')->where(array('id'=>$userid,'token'=>$token))->find();
        if(!$info){
            $this->Rerror('登录失效请重新登录');
        }
        return $info;
    }

    public function TokenToUinfoFalse(){
        $userid = I('param.userid',0,'intval');
        $token  = I('param.token');
        if(!$userid || !$token){
            return false;
        }
        $info   = M('user')->where(array('id'=>$userid,'token'=>$token))->find();
        if(!$info){
            return false;
        }
        return $info;
    }

    public function Concat(&$list,$param){
        foreach ($list as &$lv){
            if($lv[$param]){
                $lv[$param] = PICBASEURL.$lv[$param];
            }
        }
    }
   	/**
     *上传图片操作
     *@param string $picname
     *@return String $imgPath
     */
    protected function uploadPic(){
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;
        $upload->rootPath = './upload/';
        $upload->savePath = '';
        $upload->saveName = array('uniqid','');//uniqid函数生成一个唯一的字符串序列。
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');
        $upload->autoSub = true;
        $upload->subName = array('date','Ymd');
        $arr = $upload->upload();
        $retrun = array();
        if(is_array($arr)){
            foreach ($arr as $k => $v){
                $imgPath[] = "/upload/".$v['savepath'].$v['savename'];
            }
            $retrun = array('imgPath'=>$imgPath);
        }
        if($upload->getError()=='上传文件后缀不允许'){
            $this->Rerror('上传图片格式不支持');
        }
        return $retrun;
    }
}


    // public function __construct(){
    //     parent::__construct();
    //     //登陆验证
    //     if(!is_dir('log/'.date('Ymd'))){
    //         mkdir('log/'.date('Ymd'));
    //     }
    //     $fileurl = 'log/'.date('Ymd').'/'.strtolower(CONTROLLER_NAME).'.txt';
    //     $str     = "\r\n----------------------------------".date('H:i:s')."--------------------------------------\r\n";
    //     $str    .= "--time: ".date('Y/m/d H:i:s')."\r\n";
    //     $str    .= "--url: ".__SELF__."\r\n";
    //     $str    .= "--get: ";
    //     foreach ($_GET as $gk=>$gv){
    //         $str .= $gk.'='.$gv.'   ';
    //     }
    //     $str    .= "\r\n";
    //     $str    .= "--post: ";
    //     foreach ($_POST as $pk=>$pv){
    //     	if(is_array($pv)){
    //     		$str .= $pk.'='.json_encode($pv).'   ';
    //     	}else {
    //     		$str .= $pk.'='.$pv.'   ';
    //     	}
            
    //     }
    //     $str    .= "\r\n";
    //     file_put_contents($fileurl, $str,FILE_APPEND);
    // }



