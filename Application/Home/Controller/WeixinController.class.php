<?php
namespace Home\Controller;
use Think\Controller;
/**
 *@FILENAME:Home\Controller;
 *@AUTHOR:dudongjiang;
 *@DATE:2016年9月20日;
 *@EFFORT:type:一张类别表my_classes中
 *名称是自动回复语类型：值包含三个：自动回复(3)、关键词回复(4)、被关注回复(5)
 *因此做判断 的时候需要使用int整型去做判断;
 *获取微信的一些信息
 **/
class WeixinController extends Controller
{
    /**
     *@FUNCNAME:index;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月19日;
     *@EFFORT:设置授权的地址并且验证token;
     **/
	public function index()
	{
		$weObj = wechat_connect();
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
		    //接收消息的类型
			$type = $weObj->getRev()->getRevType();			
			$data['content'] = $weObj->getRev()->getRevContent();
			if(strstr($data['content'],"客服") || strstr($data['content'],"人工服务"))
			{
				$weObj->transmitService()->reply();
				exit();
			}
			switch($type){
				case $weObj::MSGTYPE_TEXT:
					// 文本
					$data['content'] = $weObj->getRev()->getRevContent();
					// 获取发送内容
					$data['fromusername'] = $weObj->getRev()->getRevFrom();
					// 获取发送者
					$tousername = $weObj->getRev()->getRevTo();
					//获取接受者
					$data['createtime'] = $weObj->getRev()->getRevCtime();
					// 获取发送时间
					$data['msgtype'] = $type;
					// 获取发送时间
					//判断关键词是否为数字或者数字字符串
					if(strstr($data['content'],"客服") ||strstr($data['content'],"人工服务"))
					{
						$weObj->transmitService()->reply();
						exit();
						
					}
					//关键词回复
					$text = $this->normalReply($data, $type);
					$text = trim($text);
					if(is_numeric(trim($data['content']))){
						$ad_list = $this->renews($text, $data['fromusername']);
						file_put_contents("/logs/".date("Ymd").".log.php", var_export($ad_list,true),FILE_APPEND);
						$weObj->news($ad_list)->reply();
					}else{
						$weObj->text($text)->reply();
					}
					exit();
					break;
				case $weObj::MSGTYPE_EVENT:
					//事件
					$event = $weObj->getRev()->getRevEvent();
                    writelog('event信息:',$event);
					if(strstr($event['key'],"客服") || strstr($event['key'],"人工服务"))
					{
						$weObj->transmitService()->reply();
						exit();
					}
					$event['openid'] = $weObj->getRev()->getRevFrom();
					if($event['event'] == 'subscribe'){
                        			//添加或者编辑用户信息
						$wxuser_mod = M('my_wechatuser');
						$wxuser_infor = $wxuser_mod
									->where("openid = '{$event['openid']}'")
									->find();
						$userinfo = $weObj->getUserInfo($event['openid']);
						//关注信息添加到数据库
						$this->saveInfo($event['openid'],$userinfo);
                        			$Message = M('my_wechatkeywords');
                        			$where = array(
                            				'type' => 55
                        			);
                        			$content = $Message->where($where)->find();
                        			$info = $content['response'];
                        			$info = str_replace("<br/>", "\n", $info);
                        			$weObj->text($info)->reply();
						exit();
						//关注消息结束
					}elseif($event['event'] == 'unsubscribe'){
						$wxuser_mod = M('my_wechatuser');
						$member['subscribe'] = 0;
						$member['unsubscribe_time'] = time();
						$wxuser_mod->where("openid = '{$event['openid']}'")->save($member);
					}elseif($event['event'] == 'CLICK'){// 点击菜单拉取消息
						if($event['key'] == 3){
								$weObj->image('vEV52sDd1-hE_HlPHXrk-xjaUkKNgodvqc30hIKswAU')->reply();
						}else{
							$res_data = M('my_wechatkeywords')->where('keyword like "%'.$event['key'].'%"')->find();
							if($res_data){
								$info = str_replace("<br/>", "\n", $res_data['response']);
								$weObj->text($info)->reply();
							}
						}
						exit();
					}elseif ($event['event'] == 'VIEW'){// 点击菜单跳转页面
						history($event['openid'], 2, $event['key']);
						exit();
					}elseif($event['event'] == 'SCAN'){
						//用户已关注时的事件推送 扫描时间  处理扫描带参数二维码事件
                        if($event['key']){
                            $userinfo = $weObj->getUserInfo($event['openid']);
                            writelog('userinfo信息:',$userinfo);
                            writelog('event信息:',$event);
                            //查询 是否已经绑定
                            $wechatuser = M('my_wechatuser');
                            $wechatuserInfo = $wechatuser->where('openid = "'.$userinfo['openid'].'"')->find();
                            if($wechatuserInfo){
                                writelog('wechatuserInfo信息:',$wechatuserInfo);
                                //查询是否绑定用户
                                $user = M('my_user')->where('wechatuserid='.$wechatuserInfo['id'])->find();
                                writelog('user信息:',$user);
                                if($user){
                                    $user['openid'] = $userinfo['openid'];
                                    S('user', $user);
                                }else{
                                    S('user', null);
                                }
                            }else{
                                $data = array(
                                    'openid' => $userinfo['openid'],
                                    'nickname' => trim($userinfo['nickname']),
                                    'sex' => $userinfo['sex'],
                                    'language' => $userinfo['language'],
                                    'city' => $userinfo['city'],
                                    'province' => $userinfo['province'],
                                    'country' => $userinfo['country'],
                                    'headimgurl' => $userinfo['headimgurl'],
                                    'createtime' => date('Y-m-d H:i:S',time())
                                );
                                $wechatuser->add($data);
                            }
					    }
					}
					break;
				case $weObj::MSGTYPE_IMAGE:
					break;
				case $weObj::MSGTYPE_LOCATION:
					break;
				case $weObj::MSGTYPE_VOICE:
					break;
				default:
					$weObj->text("有什么疑问请输入客服！我们的工作人员会联系您！")->reply();
			}
		}else{
            file_put_contents("/logs/".date("Ymd").".log.php", var_export($_SERVER,true),FILE_APPEND);
			$weObj->valid();
		}
	}
	public function indexljs()
	{
		$weObj = wechat_connect('LJS');
		if($_SERVER['REQUEST_METHOD'] == 'POST'){
		    //接收消息的类型
			$type = $weObj->getRev()->getRevType();			
			switch($type){
				case $weObj::MSGTYPE_TEXT:
					// 文本
					$data['content'] = $weObj->getRev()->getRevContent();
					// 获取发送内容
					$data['fromusername'] = $weObj->getRev()->getRevFrom();
					// 获取发送者
					$tousername = $weObj->getRev()->getRevTo();
					//获取接受者
					$data['createtime'] = $weObj->getRev()->getRevCtime();
					// 获取发送时间
					$data['msgtype'] = $type;
					// 获取发送时间
					//关键词回复
					$text = $this->normalReply($data, $type);
					$text = trim($text);
					//判断关键词是否为数字或者数字字符串
					/*if(is_numeric(trim($data['content']))){
						$ad_list = $this->renews($text, $data['fromusername']);
						file_put_contents("/tmp/wechat0923.txt", var_export($ad_list,true),FILE_APPEND);
						$weObj->news($ad_list)->reply();
					}else{*/
						$weObj->text($text)->reply();
//					}
					exit();
					break;
				case $weObj::MSGTYPE_EVENT:
					//事件
					$event = $weObj->getRev()->getRevEvent();
					$event['openid'] = $weObj->getRev()->getRevFrom();
					if($event['event'] == 'subscribe'){
                        //添加或者编辑用户信息
						$wxuser_mod = M('my_wechatuser');
						$wxuser_infor = $wxuser_mod
									->where("openid = '{$event['openid']}'")
									->find();
						$userinfo = $weObj->getUserInfo($event['openid']);
						//关注信息添加到数据库
						$this->saveInfo($event['openid'],$userinfo);
						
						$weObj->text('你好！')->reply();
						exit();
						//关注消息结束
					}elseif($event['event'] == 'unsubscribe'){
						$wxuser_mod = M('my_wechatuser');
						$member['subscribe'] = 0;
						$member['unsubscribe_time'] = time();
						$wxuser_mod->where("openid = '{$event['openid']}'")->save($member);
					}elseif($event['event'] == 'CLICK'){// 点击菜单拉取消息
						if($event['key'] == 3){
							$weObj->image('vEV52sDd1-hE_HlPHXrk-xjaUkKNgodvqc30hIKswAU')->reply();
						}else{
							$res_data = M('my_wechatkeywords')->where('keyword = "'.$event['key'].'"')->find();
							if($res_data){
								$info = str_replace("<br/>", "\n", $res_data['response']);
								$weObj->text($info)->reply();
							}
						}
						exit();
					}elseif ($event['event'] == 'VIEW'){// 点击菜单跳转页面
						history($event['openid'], 2, $event['key']);
						exit();
					}elseif($event['event'] == 'SCAN'){
						//用户已关注时的事件推送 扫描时间
					}
					break;
				case $weObj::MSGTYPE_IMAGE:
					break;
				case $weObj::MSGTYPE_LOCATION:
					break;
				case $weObj::MSGTYPE_VOICE:
					break;
				default:
					$weObj->text("help info")->reply();
			}
		}else{
			$weObj->valid();
		}
	}
	
	/*
	* 关键词回复
	*/
	public function normalReply($data, $type)
	{
	    //记录用户回复的信息
		$this->savelast($data);
		// 查询回复
        $Message = M('my_wechatkeywords');
        $info = '';
        $where['keyword'] = array(
            'like',
            '%' . $data['content'] . '%'
        );
        $info = $Message->where($where)->select();
        writelog("关键字回复：",$info);
        if(empty($info)){
            $where2 = array(
                'type' => 53
            );
            $info = $Message->where($where2)->select();
            writelog("自动回复：",$info);
        }
		$i = 0;
        $infor = '';
		foreach($info as $val){
			$i++;
			$infor.= $val['response'] . "\n";
		}
		return $infor;
	}
	
	public function savelast($data, $type)
	{
		// 数据储存
		$usermsg = M('my_wechatrecord');
		$res = $usermsg->add(array(
			'fromusername' => $data['fromusername'],
			'content' => $data['content'],
			'createtime' => date("Y-m-d H:i:s",$data['createtime']),
			'msgtype' => $data['msgtype'],
			'replycontent' => '',
		));
	}

	//
	/**
     *【存储用户关注后通关网页获取的用户信息】
     *@param string $openid 用户唯一id
     *@param array $data 数组
     **/
	public function saveInfo($opneid,$data){
		$wxuser_mod = M('my_wechatuser');
		unset($data['remark']);
		unset($data['groupid']);
		unset($data['tagid_list']);
		//获取用户是否关注
		$info = $wxuser_mod->where(["openid" => $opneid])->getField('openid');
		$data['subscribe_time'] = date('Y-m-d H:i:s',$data['subscribe_time']);
		if (empty($info)) {
			//未关注过添加
			$data['createtime'] = date('Y-m-d H:i:s',time());
			$wxuser_mod->add($data);
		}else{
			//关注过修改
			$data['updatetime'] = date('Y-m-d H:i:s',time());
			$wxuser_mod->where(["openid" => $opneid])->save($data);
		}
		writelog('数据添加成功---',$wxuser_mod->getLastSql());
	}
	
    /**
     *@FUNCNAME:renews;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月23日;
     *@EFFORT:处理返回信息 这里keyword填写的是 数字或者数字字符串就会查找这里的数据;
     **/
	public function renews($id, $openid)
	{
		$my_wechatsingleimgreply = M('my_wechatsingleimgreply');
		$one = $my_wechatsingleimgreply->where("keyword=$id")->select();
		foreach($one as $key => $value){
			$ad_list[$key]['Title'] = $value['title'];
			$ad_list[$key]['Description'] = $value['digest'];
			$ad_list[$key]['PicUrl'] = "http://" . I('server.HTTP_HOST') . __ROOT__ . $value['img_url'];
			if(strstr($value['content_source_url'], "&")){
				$ad_list[$key]['Url'] = $value['content_source_url'] . "&openid=" . $openid;
			}else{
				$ad_list[$key]['Url'] = $value['content_source_url'] . "/openid/" . $openid;
			}
		}
		return $ad_list;
	}
}
