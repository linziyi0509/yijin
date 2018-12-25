<?php
namespace Admin\Controller;
use Think\Controller;
use Think\Model;

//$$$$后台管理-admin-15$$$$
class AdminController extends BaseController {
    //####管理员列表-index####
    public function index(){
        $this->display();
    }


    public function list_page(){
    	$username = $_POST['username'];
    	$gname = $_POST['gname'];
    	$sql = "select a.*,g.`gname`,c.`cityname`,m.`name` as `mname` from `my_admin` as a left join `my_group` as g on g.`id`=a.`groupid` left join `my_allcity` as c on c.`cityid`=a.`cityid` left join `my_merchant` as m on m.`id`=a.`merchantid`
    			where a.`username` like '%".$username."%' and g.`gname` like '%".$gname."%' and a.`id`<>1 order by a.`id` desc";
    	$result = $this->pagelist_($sql,$_POST);
    	echo json_encode($result);
    }
    //@@@@用户添加-add@@@@
    public function add(){
        $catelist = M('my_class')->where(array('parentid'=>45))->select();
        $this->assign('catelist',$catelist);
        $grouplist = M('my_group')->where('id>1')->select();
        $merchantlist = M('my_merchant')->field('id,name')->select();
        $this->assign('grouplist',$grouplist);
        $this->assign('merchantlist',$merchantlist);
        $this->display();
    }

    public function saveadd(){
    	unset($_POST['password2']);
        $model = new Model();
        $model->startTrans();
    	$_POST['password'] = md5_($_POST['password']);
    	$operatemerchantidArr = explode(',',$_POST['operatemerchantid']);
        unset($_POST['operatemerchantid']);
        //$admin = M('my_admin');
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
        $_POST['typeid'] = implode(',', $_POST['typeid']);
        $_POST['cateid'] = implode(',', $_POST['cateid']);
        $arr = $_POST;
    	$result = $model->table('my_admin')->add($arr);
        writelog('Admin---saveadd---result:::',$result);
        if(!empty($operatemerchantidArr)){
            $operatemerchantidDatas = [];
            foreach($operatemerchantidArr as $key=>$val){
                $operatemerchantidData['merchantid'] = $val;
                $operatemerchantidData['adminid'] = $result;
                $operatemerchantidDatas[] = $operatemerchantidData;
            }
            writelog('Admin---saveadd---operatemerchantidDatas:::',$operatemerchantidDatas);
            $flag = $model->table('my_operablerelation')->addAll($operatemerchantidDatas);
            writelog('Admin---saveadd---flag:::',$flag);
            if($flag && $result){
                $model->commit();
                echo 'success';
            }else{
                $model->rollback();
                exit($model->getError().'数据有误，请重新添加');
            }
        }else{
            if($result){
                $model->commit();
                echo 'success';
            }else{
                $model->rollback();
                exit($model->getError().'数据有误，请重新添加');
            }
        }
    }
    //@@@@用户修改-update@@@@
    public function update(){
    	$id = $_GET['id']; 
        $admin = M('my_admin');
    	$result = $admin->where(array('id'=>$id))->find();
        $catelist = M('my_class')->where(array('parentid'=>45))->select();
        $this->assign('catelist',$catelist);
    	$this->assign('result',$result);
		$grouplist = M('my_group')->where('id>1')->select();
        $this->assign('grouplist',$grouplist);
        $merchantlist = M('my_merchant')->field('id,name')->select();
        $this->assign('merchantlist',$merchantlist);
        $this->display();
    }

    public function saveupdate(){
    	$id = $_POST['id'];
        $model = new Model();
        $model->startTrans();
        $operatemerchantidArr = explode(',',$_POST['operatemerchantid']);
        unset($_POST['operatemerchantid']);
    	unset($_POST['id']);
    	unset($_POST['password2']);
    	if(empty($_POST['password'])){
    		unset($_POST['password']);
    	}else{
    		$_POST['password'] = md5_($_POST['password']);
    	}
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

        $_POST['typeid'] = implode(',', $_POST['typeid']);
        $_POST['cateid'] = implode(',', $_POST['cateid']);
        
    	$arr = $_POST;
        $result = $model->table('my_admin')->where(array('id'=>$id))->save($arr);
        writelog('Admin---saveupdate---result:::',$result);
        if(!empty($operatemerchantidArr)){
            $operatemerchantidDatas = [];
            foreach($operatemerchantidArr as $key=>$val){
                $operatemerchantidData['merchantid'] = $val;
                $operatemerchantidData['adminid'] = $id;
                $operatemerchantidDatas[] = $operatemerchantidData;
            }
            $delflag = $model->table('my_operablerelation')->where('adminid='.$id)->delete();
            writelog('Admin---saveupdate---delflag:::',$delflag);
            writelog('Admin---saveupdate---operatemerchantidDatas:::',$operatemerchantidDatas);
            $flag = $model->table('my_operablerelation')->addAll($operatemerchantidDatas);
            writelog('Admin---saveupdate---flag:::',$flag);
            if($delflag && $flag && $result){
                $model->commit();
                echo 'success';
            }else{
                $model->rollback();
                exit($model->getError().'数据有误，请重新添加');
            }
        }else{
            $delflag = $model->table('my_operablerelation')->where('adminid='.$id)->delete();
            if($result && $delflag){
                $model->commit();
                exit('success');
            }else{
                $model->rollback();
                exit($model->getError().'数据有误，请重新添加');
            }
        }
    }
    //@@@@用户删除-del@@@@
    public function del(){
    	$id = $_GET['id'];
        if($id==1){
            exit;
        }
        $admin = M('my_admin');
    	$admin->delete($id);
        echo 'success';
    }
    //@@@@用户批量删除-delall@@@@
    public function delall(){
    	$idlist = $_POST['idlist'];
        $admin = M('my_admin');
        $where['id'] = array('in',$idlist);
        $admin->where($where)->delete();
        echo 'success';
    }

    //开发者-developer
    public function developer(){
        $this->display(); 
    }

    //我要开发
    public function develop(){
        $this->display(); 
    }

    //修改开发
    public function updatedevelop(){
        $id = $_GET['id']; 
        $develop = M('my_develop');
        $result = $develop->find($id);
        $result['content'] = unserialize($result['content']);
        $this->assign('result',$result);
        $this->display();
    }


    public function develop_list_page(){
        $name = $_POST['name'];
        $sql = "select * from `my_develop` where `erji` like '%".$name."%' order by `id` desc";
        $result = $this->pagelist_($sql);
        echo json_encode($result);
    }

    public function deldevelop(){
        //删除记录
        $id = $_GET['id'];
        $table = ucwords($_GET['table']);
        $basedir = dirname(__FILE__);
        if(unlink($basedir.'/'.$table.'Controller.class.php') && deldir($basedir.'/../View/'.$table)){
            //删除数据表
            M()->query("drop table `my_".$_GET['table']."`");
            //删除记录
            M('my_develop')->delete($id);
            //删除菜单
            M('my_rbac')->where(array('c'=>$_GET['table']))->delete();
        }
    }       

    public function jinzhi(){
        $id = $_POST['id'];
        //$this->saveupdate_('my_develop',array('suoding'=>1),$id);
        M('my_develop')->where(array('id'=>$id))->save(array('suoding'=>1));
    }

    public function quxiaojinzhi(){
        $id = $_POST['id'];
        M('my_develop')->where(array('id'=>$id))->save(array('suoding'=>0));
    }

    public function savedevelop(){
        $arr_post = $_POST;
        $table = $_POST['table'];
        $type = $_POST['type'];
        $module = $_POST['module'];
        $md = $_POST['md'];
        writelog("post:",$_POST);
        //验证
        //if(empty($type)){
            //$data = array('result'=>false,'data'=>'','message'=>'请选择类型!');
            //echo json_encode($data);exit;
        //}
        if(empty($md)){
            $data = array('result'=>false,'data'=>'','message'=>'请填写一级模块!');
            echo json_encode($data);exit;
        }
        if(empty($module)){
            $data = array('result'=>false,'data'=>'','message'=>'请填写二级模块!');
            echo json_encode($data);exit;
        }
        if(empty($table)){
            $data = array('result'=>false,'data'=>'','message'=>'请填写表名!');
            echo json_encode($data);exit;
        }

        //保存信息
        $d_arr = array('content'=>serialize($_POST),'name'=>$_POST['table'],'yiji'=>$_POST['md'],'erji'=>$_POST['module'],'description'=>$_POST['descriptions']);
        if($_POST['action'] == 'add'){
            //$this->saveadd2_('my_develop',$d_arr);
            M('my_develop')->add($d_arr);
        }elseif($_POST['action'] == 'update'){
            // $this->saveupdate2_('my_develop',$d_arr,$_POST['id']);
            M('my_develop')->where(array('id'=>$_POST['id']))->save($d_arr);
        }
        
        //leftjoin连表 创建关联字段
        $leftjoin_file = '';
        for ($i=0; $i <= $_POST['lian']; $i++) { 
            if($_POST['leftjoin'][$i] && $_POST['leftjoin'][$i]<>''){
                //多对多关系
                if($_POST['duoduiduo'][$i] && $_POST['duoduiduo'][$i] == 1){
                    //创建多对多中间表
                    //如果中间表不存在
                    $iftable = M()->query("select `TABLE_NAME` from `INFORMATION_SCHEMA`.`TABLES` where `TABLE_NAME`='".$table."_has_".$_POST['leftjoin'][$i]."'");
                    if(empty($iftable)){
                        //先删除多对多中间表
                        M()->query("drop table `".$_POST['leftjoin'][$i]."_has_".$table."`");
                        M()->query("create table `".$_POST['leftjoin'][$i]."_has_".$table."` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT,`".$_POST['leftjoin'][$i]."id` int(10) UNSIGNED NOT NULL,`".$table."id` int(10) UNSIGNED NOT NULL,PRIMARY KEY (`id`),UNIQUE KEY `".$_POST['leftjoin'][$i]."id_".$table."id` (`".$_POST['leftjoin'][$i]."id`,`".$table."id`) USING BTREE,KEY `".$_POST['leftjoin'][$i]."id` (`".$_POST['leftjoin'][$i]."id`) USING BTREE,KEY `".$table."id` (`".$table."id`) USING BTREE)");
                    }
                    

                }else{//一对多
                    $leftjoin_file .= ' ,`'.$_POST['leftjoin'][$i].'id` int(10) DEFAULT NULL';
                    $leftjoin_key .= 'KEY `'.$_POST['leftjoin'][$i].'id` (`'.$_POST['leftjoin'][$i].'id`),';
                }
                
            }
        }

        $sql = 'CREATE TABLE `my_'.$table.'` (`id`  int(10) UNSIGNED NULL AUTO_INCREMENT, `createtime` datetime NULL ,`updatetime`  datetime NULL,`adminid`  int(10) DEFAULT 1 '.$leftjoin_file.' ';
        for ($i=-1; $i < $_POST['hang']; $i++) {
            //验证
            if(empty($_POST['name'][($i)]) || strpos($_POST['name'][($i)],'.')){
                continue;
            }
            if(empty($_POST['type'][($i)])){
                $data = array('result'=>false,'data'=>'','message'=>'请选择类型!');
                echo json_encode($data);exit;
            }
            //转义
            if($_POST['type'][($i)] == 'pic'){
                $_POST['type'][($i)] = 'varchar';
                $_POST['long'][($i)] = 45;
            }
            if($_POST['type'][($i)] == 'piclist'){
                $_POST['type'][($i)] = 'varchar';
                $_POST['long'][($i)] = 2000;
            }

            if($_POST['type'][($i)] == 'class'){
                $_POST['type'][($i)] = 'int';
                $_POST['long'][($i)] = 10;
            }

            if(!empty($_POST['default'][($i)]) || ($_POST['default'][($i)] == 0 && $_POST['default'][($i)]<>'')){
                if($_POST['default'][($i)] == '\'\''){
                    $default = 'DEFAULT \'\'';
                }else{
                    $default = 'DEFAULT \''.$_POST['default'][($i)].'\'';
                }
                
            }else{
                $default = '';
            }
            if(!empty($_POST['description'][($i)])){
                $description = 'COMMENT \''.$_POST['description'][($i)].'\'';
            }else{
                $description = '';
            }

            if(!empty($_POST['long'][($i)])){
                $long = '('.$_POST['long'][($i)].')';
            }else{
                $long = '';
            }
            //如果是货币类型

            if ($_POST['type'][($i)] == 'huobi') {
                $sql .= ',`'.$_POST['name'][($i)].'`  decimal(10,2) NULL'.$default.' '.$description.' ';
            } else {
                $sql .= ',`'.$_POST['name'][($i)].'`  '.$_POST['type'][($i)].$long.' NULL '.$default.' '.$description.' ';
            }

            if($_POST['searchtype'][($i)] && $_POST['searchtype'][($i)]<>''){
                $search_key .= 'KEY `'.$_POST['name'][($i)].'` (`'.$_POST['name'][($i)].'`),';
            }
        }

        $sql .= ','.$search_key.'KEY `createtime` (`createtime`),KEY `updatetime` (`updatetime`),'.$leftjoin_key.'PRIMARY KEY (`id`)) COMMENT=\''.$module.'\'';

        //生成控制器以及模板
        $s = $this->generate($arr_post);
        if ($_POST['ifupdate'] && $_POST['ifupdate']==1) {//如果更新数据表
            //删除数据表
            M()->query("drop table `my_".$table."`");
            $result = M()->query($sql);
            if($result === false){
                $data = array('result'=>false,'data'=>$sql,'message'=>'失败！可检查'.$sql);
            }else{
                if(in_array('setting', $_POST['gongneng'])){//如果是勾选了配置
                    M()->query("insert into `my_".$table."` (`id`) VALUES ('1')");
                }
                //生成菜单
                $this->addmenu($arr_post);
                $data = array('result'=>true,'data'=>$sql,'message'=>'成功!');
            }
        } else {
            //如果不更新数据表
            $data = array('result'=>true,'data'=>$sql,'message'=>'成功!');
        }
        
        writelog("sql:",$sql);
        echo json_encode($data);
    }

    //生成控制器和模板
    public function generate($arr){

        //生成控制器
        $basedir = dirname(__FILE__);
        $content = file_get_contents($basedir.'/../Mather/MatherController.class.php');
        $table = ucfirst($arr['table']);
        $file = '';

        //leftjoin连表
        $leftjoin = '';
        $duoduiduo_control = '';
        for ($i=0; $i <= $arr['lian']; $i++) { 
            if($arr['leftjoin'][$i] && $arr['leftjoin'][$i]<>''){
                if($_POST['duoduiduo'][$i] && $_POST['duoduiduo'][$i] == 1){
                    $leftjoin .= '';
                    $where .= '';
                    //查看中间表是否存在 如果存在则用已存在的中间表
                    $iftable = M()->query("select `TABLE_NAME` from `INFORMATION_SCHEMA`.`TABLES` where `TABLE_NAME`='".$table."_has_".$_POST['leftjoin'][$i]."'");
                    if(!empty($iftable)){
                        $has = $arr['table'].'_has_'.$arr['leftjoin'][$i];
                    }else{
                        $has = $arr['leftjoin'][$i].'_has_'.$arr['table'];
                    }


                    //获取对对多外表的配置数据
                    //查询my_develop表的外表设置
                    // $leftsetting = $this->getone_("select `content` from `my_develop` where `name`='".$arr['leftjoin'][$i]."'");
                    $leftsetting = M('my_develop')->field('`content`')->where(array('name'=>$arr['leftjoin'][$i]))->find();
                    $leftcontent = unserialize($leftsetting['content']);
                    for ($a=0;$a<=$leftcontent['hang'];$a++) {
                        if(empty($leftcontent['name'][$a])){
                            continue;
                        }
                        
                        if($leftcontent['searchtype'][$a] == 'like' && $leftcontent['type'][$a]=='varchar'){
                            $searchname .= '`my_'.$arr['leftjoin'][$i].'`.`'.$leftcontent['name'][$a].'` like \'%".$_POST[\''.$leftcontent['name'][$a].'\']."%\' and ';
                        }
                    }
                    $searchname = trim($searchname,'and ');
                    
                    
                    //生成多对多控制器
                    $duoduiduo_control .= 'public function '.$arr['leftjoin'][$i].'and'.$arr['table'].'list(){
        $id = $_POST[\'id\'];
        if($_SESSION[\'levelstr\'] == \'all\'){
            $where = \'where 1=1 and \';
        }else{
            $where = \'where `my_'.$arr['table'].'`.`adminid`=\'.$_SESSION[\'adminid\'].\' and \';
        }
        $where .= " '.$searchname.' and ";
        $sql = "select `my_'.$arr['leftjoin'][$i].'`.*,`'.$has.'`.`id` from `my_'.$arr['leftjoin'][$i].'` left join `'.$has.'` on `'.$has.'`.`'.$arr['leftjoin'][$i].'id`=`my_'.$arr['leftjoin'][$i].'`.`id`  ".$where." `'.$has.'`.`'.$arr['table'].'id`=\'".$id."\' order by `my_'.$arr['leftjoin'][$i].'`.`id` desc";
        $result = $this->pagelist_($sql,$_POST);
        echo json_encode($result);
    }
    public function delall_'.$arr['leftjoin'][$i].'and'.$arr['table'].'list(){
        $idlist = $_POST[\'idlist\'];
        $this->delall_(\''.$has.'\',$idlist);
    }

    public function del_'.$arr['leftjoin'][$i].'and'.$arr['table'].'list(){
        $id = $_POST[\'id\'];
        $this->del_(\''.$has.'\',$id);
    }
    ';
                    //生成多对多模板start
                    $duoduiduo_view = file_get_contents($basedir.'/../Mather/duoduiduo.html');
                    //查询my_develop表的外表设置
                    // $leftsetting = $this->getone_("select `content` from `my_develop` where `name`='".$arr['leftjoin'][$i]."'");
                    $leftsetting = M('my_develop')->field('`content`')->where(array('name'=>$arr['leftjoin'][$i]))->find();
                    $leftcontent = unserialize($leftsetting['content']);
                    $leftfield = '';
                    $searchlist = '';
                    $leftparam = '';
                    for ($a=0;$a<=$leftcontent['hang'];$a++) {
                        if(empty($leftcontent['name'][$a])){
                            continue;
                        }
                        
                        if($leftcontent['ifshow'][$a]==1 && $leftcontent['type'][$a]=='varchar'){
                            $leftfield .= '{ field: \''.$leftcontent['name'][$a].'\', title: \''.$leftcontent['description'][$a].'\', align: \'center\', width: 100 },';
                        }
                        if($leftcontent['searchtype'][$a] == 'like' && $leftcontent['type'][$a]=='varchar'){
                            $searchlist .= '<span>'.$leftcontent['description'][$a].'：</span><input id="'.$leftcontent['name'][$a].'" class="easyui-validatebox" size="10"> &nbsp; ';
                            $leftparam .= ''.$leftcontent['name'][$a].':$(\'#'.$leftcontent['name'][$a].'\').val(),';
                        }
                    }

                    $leftcontrol = $arr['table'];
                    $leftmethod = $arr['leftjoin'][$i].'and'.$arr['table'].'list';

                    //导入链接设置
                    $duoduiduodaoru = '/admin/'.$leftcontrol.'/daoru'.$arr['leftjoin'][$i].'?id=\'+id+\'';

                    $duoduiduo_view = str_replace("***field***",$leftfield,$duoduiduo_view);
                    $duoduiduo_view = str_replace("***searchlist***",$searchlist,$duoduiduo_view);
                    $duoduiduo_view = str_replace("***leftparam***",$leftparam,$duoduiduo_view);
                    $duoduiduo_view = str_replace("***leftcontrol***",$leftcontrol,$duoduiduo_view);
                    $duoduiduo_view = str_replace("***leftmethod***",$leftmethod,$duoduiduo_view);
                    $duoduiduo_view = str_replace("***duoduiduodaoru***",$duoduiduodaoru,$duoduiduo_view);
                    file_put_contents($basedir.'/../View/'.$table.'/'.$arr['leftjoin'][$i].'and'.$arr['table'].'.html',$duoduiduo_view);
                    //生成多对多模板end



                    //获取对对多外表的配置数据
                    //查询my_develop表的外表设置
                    // $leftsetting = $this->getone_("select `content` from `my_develop` where `name`='".$arr['leftjoin'][$i]."'");
                    $leftsetting = M('my_develop')->field('`content`')->where(array('name'=>$arr['leftjoin'][$i]))->find();
                    $leftcontent = unserialize($leftsetting['content']);
                    for ($a=0;$a<=$leftcontent['hang'];$a++) {
                        if(empty($leftcontent['name'][$a])){
                            continue;
                        }
                        
                        if($leftcontent['searchtype'][$a] == 'like' && $leftcontent['type'][$a]=='varchar'){
                            $searchwhere .= '`'.$leftcontent['name'][$a].'` like \'%".$_POST[\''.$leftcontent['name'][$a].'\']."%\' and ';
                        }
                    }
                    $searchwhere = trim($searchwhere,'and ');

                    //生成多对多导入控制器
                    $duoduiduodaoru_control .= 'public function daoru'.$arr['leftjoin'][$i].'list(){
        if($_SESSION[\'levelstr\'] == \'all\'){
            $where = \'where 1=1 and \';
        }else{
            $where = \'where `adminid`=\'.$_SESSION[\'adminid\'].\' and \';
        }
        $where .= " '.$searchwhere.' ";
        $sql = "select * from `my_'.$arr['leftjoin'][$i].'` ".$where."  order by `id` desc";
        $result = $this->pagelist_($sql,$_POST);
        echo json_encode($result);
    }
    public function import_daoru'.$arr['leftjoin'][$i].'list(){
        $idlist = $_POST[\'idlist\'];
        $'.$arr['table'].'id = $_POST[\'id\'];
        $id_arr = explode(\',\', $idlist);
        foreach ($id_arr as $key => $value) {
            $this->saveadd_(\''.$has.'\',array(\''.$arr['table'].'id\'=>$'.$arr['table'].'id,\''.$arr['leftjoin'][$i].'id\'=>$value));
        }
    }
    ';
                    //生成多对多导入start
                    $duoduiduo_view = file_get_contents($basedir.'/../Mather/duoduiduodaoru.html');
                    //查询my_develop表的外表设置
                    // $leftsetting = $this->getone_("select `content` from `my_develop` where `name`='".$arr['leftjoin'][$i]."'");
                    $leftsetting = M('my_develop')->field('`content`')->where(array('name'=>$arr['leftjoin'][$i]))->find();
                    $leftcontent = unserialize($leftsetting['content']);
                    $leftfield = '';
                    $searchlist = '';
                    $leftparam = '';
                    for ($a=0;$a<=$leftcontent['hang'];$a++) {
                        if(empty($leftcontent['name'][$a])){
                            continue;
                        }
                        
                        if($leftcontent['ifshow'][$a]==1 && $leftcontent['type'][$a]=='varchar'){
                            $leftfield .= '{ field: \''.$leftcontent['name'][$a].'\', title: \''.$leftcontent['description'][$a].'\', align: \'center\', width: 100 },';
                        }
                        if($leftcontent['searchtype'][$a] == 'like' && $leftcontent['type'][$a]=='varchar'){
                            $searchlist .= '<span>'.$leftcontent['description'][$a].'：</span><input id="'.$leftcontent['name'][$a].'" class="easyui-validatebox" size="10"> &nbsp; ';
                            $leftparam .= ''.$leftcontent['name'][$a].':$(\'#'.$leftcontent['name'][$a].'\').val(),';
                        }
                    }

                    $leftcontrol = $arr['table'];
                    $leftmethod = 'daoru'.$arr['leftjoin'][$i].'list';
                    $leftfield = trim($leftfield,',');

                    $duoduiduo_view = str_replace("***field***",$leftfield,$duoduiduo_view);
                    $duoduiduo_view = str_replace("***searchlist***",$searchlist,$duoduiduo_view);
                    $duoduiduo_view = str_replace("***leftparam***",$leftparam,$duoduiduo_view);
                    $duoduiduo_view = str_replace("***leftcontrol***",$leftcontrol,$duoduiduo_view);
                    $duoduiduo_view = str_replace("***leftmethod***",$leftmethod,$duoduiduo_view);
                    file_put_contents($basedir.'/../View/'.$table.'/daoru'.$arr['leftjoin'][$i].'.html',$duoduiduo_view);
                    //生成多对多导入end

                }else{
                    $leftjoin .= ' left join `my_'.$arr['leftjoin'][$i].'` on `my_'.$arr['leftjoin'][$i].'`.`id` = `my_'.$arr['table'].'`.`'.$arr['leftjoin'][$i].'id`';
                    $where .= 'if($_POST[\''.$arr['leftjoin'][$i].'id\'] <> \'\'){$where .= " `my_'.$arr['table'].'`.`'.$arr['leftjoin'][$i].'id` = ".$_POST[\''.$arr['leftjoin'][$i].'id\']." and ";}';
                }
                
            }
        }

        $otherfiled = '';//连表的外表字段
        $showfiled = '';//显示的字段
        for ($i=0; $i < $arr['hang']; $i++) { 
            if(empty($_POST['name'][($i)])){
                continue;
            }
            if(strpos($arr['name'][($i)],'.')){
                $name_arr = explode('.', $arr['name'][($i)]);
                $otherfiled .= ','.$arr['name'][($i)];
                //$arr['name'][($i)] = $name_arr[1];
            }else{
                $otherfiled .= ',`my_'.$arr['table'].'`.`'.$arr['name'][($i)].'`';
            }
            $showfiled .= '\''.$arr['description'][($i)].'\''.',';
            //查询条件
            if($arr['searchtype'][($i)] != ''){
                if($arr['searchtype'][($i)] == 'like'){
                    if(strpos($arr['name'][($i)],'.')){
                        $where .= '$where .= " '.$arr['name'][($i)].' '.$arr['searchtype'][($i)].' \'%".$_POST[\''.$name_arr[1].'\']."%\' and ";';
                    }else{
                        $where .= '$where .= " `my_'.$arr['table'].'`.`'.$arr['name'][($i)].'` '.$arr['searchtype'][($i)].' \'%".$_POST[\''.$arr['name'][($i)].'\']."%\' and ";';
                    }
                    
                }elseif($arr['searchtype'][($i)] == '='){
                    if(strpos($arr['name'][($i)],'.')){
                        $where .= 'if($_POST[\''.$arr['name'][($i)].'\'] <> \'\'){$where .= " '.$arr['name'][($i)].' '.$arr['searchtype'][($i)].' ".$_POST[\''.$name_arr[1].'\']." and ";}';
                    }else{
                        $where .= 'if($_POST[\''.$arr['name'][($i)].'\'] <> \'\'){$where .= " `my_'.$arr['table'].'`.`'.$arr['name'][($i)].'` '.$arr['searchtype'][($i)].' ".$_POST[\''.$arr['name'][($i)].'\']." and ";}';
                    }
                    
                }else{
                    if(strpos($arr['name'][($i)],'.')){
                        $where .= '$where .= " '.$arr['name'][($i)].' '.$arr['searchtype'][($i)].' ".$_POST[\''.$name_arr[1].'\']." and ";';
                    }else{
                        $where .= '$where .= " `my_'.$arr['table'].'`.`'.$arr['name'][($i)].'` '.$arr['searchtype'][($i)].' ".$_POST[\''.$arr['name'][($i)].'\']." and ";';
                    }
                    
                }
                
            }

            //生成上传图片
            if($arr['type'][($i)] == 'pic'){
                $file .= 'if($info[\''.$arr['name'][($i)].'\']){$_POST[\''.$arr['name'][($i)].'\'] = \'/upload/\'.$info[\''.$arr['name'][($i)].'\'][\'savepath\'].$info[\''.$arr['name'][($i)].'\'][\'savename\'];}';
            }

            if($arr['type'][($i)] == 'piclist'){
                $file .= 'if(!empty($info)){foreach ($info as $key => $value) {if($value[\'key\']){$_POST[\''.$arr['name'][($i)].'\'] .= \'/upload/\'.$value[\'savepath\'].$value[\'savename\'].\',\';}}$_POST[\''.$arr['name'][($i)].'\'] = trim($_POST[\''.$arr['name'][($i)].'\'],\',\');}';
            }

            if($arr['type'][($i)] == 'class'){
                // $parent_rs = $this->getone_("select `id` from `my_class` where `name`='".trim($arr['description'][($i)])."'");
                $parent_rs = M('my_class')->field('`id`')->where(array('name'=>trim($arr['description'][($i)])))->find();
                $parentid[$i] = $parent_rs['id'];

                //分类id转名称
                if(!isset($have)){
                    $getclassname = 'foreach ($result[\'rows\'] as $key => $value) {';
                }
                $getclassname .= '$result[\'rows\'][$key][\''.$arr['name'][($i)].'\'] = $this->getclassname($value[\''.$arr['name'][($i)].'\']);';
                $have = 1;//已经有过分类的转换
            }
        }

        //如果已经有过分类的转换
        if(isset($have)){
            $getclassname .= '}';
        }

        

        //替换内容

        //替换分类下拉
        foreach ($parentid as $key => $value) {
            $classstr .= '$c'.$value.' = $this->getclass('.$value.',\''.$arr['name'][($key)].'\');
        $this->assign(\'c'.$value.'\',$c'.$value.');';
            $classstr_update .= '$c'.$value.' = $this->getclass('.$value.',\''.$arr['name'][($key)].'\',$result[\''.$arr['name'][($key)].'\']);
        $this->assign(\'c'.$value.'\',$c'.$value.');';
        }
        if(isset($classstr)){
            if(in_array('setting', $arr['gongneng'])){//如果是勾选了配置
                $classstr = 'header(\'Location: /admin/'.$arr['table'].'/update?id=1\');
                '.$classstr;
            }
            $content = str_replace("***class***",$classstr,$content);
        }else{
            if(in_array('setting', $arr['gongneng'])){//如果是勾选了配置
                $settingstr = 'header(\'Location: /admin/'.$arr['table'].'/update?id=1\');
                ';
            }
            $content = str_replace("***class***",$settingstr,$content);
        }

        if(isset($classstr_update)){
            $content = str_replace("***classupdate***",$classstr_update,$content);
        }else{
            $content = str_replace("***classupdate***",'',$content);
        }
        
        $content = str_replace("***md***",$arr['md'],$content);
        $content = str_replace("***module***",$arr['module'],$content);
        $content = str_replace("***table***",$arr['table'],$content);
        $content = str_replace("***Table***",$table,$content);
        $content = str_replace("***where***",$where,$content);
        $content = str_replace("***file***",$file,$content);
        $content = str_replace("***getclassname***",$getclassname,$content);
        $content = str_replace("***leftjoin***",$leftjoin,$content);
        $content = str_replace("***otherfiled***",$otherfiled,$content);
        $content = str_replace("***showfiled***",$showfiled,$content);
        $content = str_replace("//***duoduiduo_control***",$duoduiduo_control,$content);
        $content = str_replace("//***duoduiduodaoru_control***",$duoduiduodaoru_control,$content);
        $content = str_replace("***duoduiduo_view***",$duoduiduo_view,$content);

        if(in_array('add', $arr['gongneng'])){
            $content = str_replace("***tianjia***",'@'.'@@@'.$arr['module'].'添加-add@'.'@@@',$content);
        }else{
            $content = str_replace("***tianjia***",'',$content);
        }

        if(in_array('del', $arr['gongneng'])){
            $content = str_replace("***shanchu***",'@'.'@@@'.$arr['module'].'删除-del@'.'@@@',$content);
        }else{
            $content = str_replace("***shanchu***",'',$content);
        }



        if(in_array('delall', $arr['gongneng'])){
            $content = str_replace("***piliangshanchu***",'@'.'@@@'.$arr['module'].'批量删除-delall@'.'@@@',$content);
        }else{
            $content = str_replace("***piliangshanchu***",'',$content);
        }

        if(in_array('update', $arr['gongneng'])){
            $content = str_replace("***xiugai***",'@'.'@@@'.$arr['module'].'修改-update@'.'@@@',$content);
        }else{
            $content = str_replace("***xiugai***",'',$content);
        }
        
        file_put_contents($basedir.'/'.$table.'Controller.class.php',$content);















        //生成列表模板
        $content2 = file_get_contents($basedir.'/../Mather/index.html');

        //是否显示创建时间搜索
        if ($arr['chuangjian'] == 1) {
            $createtimesearch = '<span>创建时间大于</span><input name="starttime" type="text"  onclick="SelectDate(this,\'yyyy-MM-dd hh:mm:ss\')" id="starttime" size="20" value="" class="easyui-validatebox" > 小于 <input name="endtime" type="text"  onclick="SelectDate(this,\'yyyy-MM-dd hh:mm:ss\')" id="endtime" value=""  size="20" class="easyui-validatebox"> ';
        }

        //是否显示创建时间
        if ($arr['chuangjian_ifshow'] == 1) {
            $chuangjian_ifshow = '{ field: \'createtime\', title: \'创建时间\', align: \'center\', width: 125,sortable:true },';
        }

        //是否显示更新时间
        if ($arr['gengxin_ifshow'] == 1) {
            $gengxin_ifshow = '{ field: \'updatetime\', title: \'更新时间\', align: \'center\', width: 125,sortable:true },';
        }

        //生成外表搜索参数
        for ($i=0; $i <= $arr['lian']; $i++) { 
            if($arr['leftjoin'][$i] && $arr['leftjoin'][$i]<>''){
                $otherid .= $arr['leftjoin'][$i].'id:$fly.request(\''.$arr['leftjoin'][$i].'id\'),';
            }
        }

        //替换内容
        for ($i=0; $i < $arr['hang']; $i++) { 
            if(empty($_POST['name'][($i)])){
                continue;
            }
            //搜索
            if($arr['searchtype'][($i)] != ''){
                if($arr['type'][($i)] == 'class'){
                    $search .= '<span>'.$arr['description'][($i)].'：</span><?php echo $c'.$parentid[$i].';?><input type="hidden"> &nbsp;';
                }else{
                    if(strpos($arr['name'][($i)],'.')){
                        $name_arr = explode('.', $arr['name'][($i)]);
                        $search .= '<span>'.$arr['description'][($i)].'：</span><input id="'.$name_arr[1].'" class="easyui-validatebox" size="10"> &nbsp;';
                    }else{
                        $search .= '<span>'.$arr['description'][($i)].'：</span><input id="'.$arr['name'][($i)].'" class="easyui-validatebox" size="10"> &nbsp;';
                    }
                }
                
                if(strpos($arr['name'][($i)],'.')){
                    $name_arr = explode('.', $arr['name'][($i)]);
                    $params .= $name_arr[1].':$(\'#'.$name_arr[1].'\').val(),';
                }else{
                    $params .= $arr['name'][($i)].':$(\'#'.$arr['name'][($i)].'\').val(),';
                }
                
            }
            //字段
            if($arr['ifshow'][($i)] == 1){

                if($arr['type'][($i)] == 'pic'){
                    if(strpos($arr['name'][($i)],'.')){
                        $name_arr = explode('.', $arr['name'][($i)]);
                        $format = 'formatter: '.$name_arr[1].'_,';
                    }else{
                        $format = 'formatter: '.$arr['name'][($i)].'_,';
                    }
                    
                }

                if(strpos($arr['name'][($i)],'.')){
                    $name_arr = explode('.', $arr['name'][($i)]);
                    $field .= '{ field: \''.$name_arr[1].'\', title: \''.$arr['description'][($i)].'\', '.$format.'align: \'center\', width: 100 },';
                }else{
                    $field .= '{ field: \''.$arr['name'][($i)].'\', title: \''.$arr['description'][($i)].'\', '.$format.'align: \'center\', width: 100,sortable:true },';
                }
                
                $format = '';

                if($arr['type'][($i)] == 'pic'){
                    if(strpos($arr['name'][($i)],'.')){
                        $name_arr = explode('.', $arr['name'][($i)]);
                        $function .= 'function '.$name_arr[1].'_(val,row){return \'<img src="\'+row.'.$$name_arr[1].'+\'" width=100>\';}';
                    }else{
                        $function .= 'function '.$arr['name'][($i)].'_(val,row){return \'<img src="\'+row.'.$arr['name'][($i)].'+\'" width=100>\';}';
                    }
                    
                }
            }
            

            
            
            
        }

        

        $toolbar = '';
        $shanchu = '';

        //添加删除修改 定义
        if(in_array('add', $arr['gongneng'])){
            $toolbar = '{text: \'添 加\',iconCls: \'icon-add\',handler: function () {$fly.gethtml(\'/admin/'.$arr['table'].'/add\',\'添加\');}}';
        }

        if(in_array('del', $arr['gongneng'])){
            $toolbar .= ', \'-\',{text: \'删 除\',iconCls: \'icon-cancel\',handler: function () {var idlist = $fly.getchecked();if(idlist==\'\'){$fly.msg(\'您没有勾选任何数据！\');return false;}$.messager.confirm(\'友情提示\',\'确定要删除选中吗？\',function(r){if (r){var idlist = $fly.getchecked();$.post(\'/admin/'.$arr['table'].'/delall\',{idlist:idlist},function(){$(\'#mytable\').datagrid(\'reload\');});}});}}';
            $shanchu = '{ field: \'shanchu\', title: \'删除\', align: \'center\',formatter: shanchu, width: 50 },';
        }

        if(in_array('out', $arr['gongneng'])){
            $toolbar .= ', \'-\',{text: \'导 出\',iconCls: \'icon-redo\',handler: function () {
                var win = $.messager.progress({
                    title:\'请稍等\',
                    msg:\'正在导出...\'
                });
                $.post(\'/admin/'.$arr['table'].'/list_page\',{daochu:1,'.$params.$otherid.'starttime:$("input[name=\'starttime\']").val(),endtime:$("input[name=\'endtime\']").val()},function(data){
                    data = eval(\'[\'+data+\']\');
                    data = data[0];
                    $.messager.progress(\'close\');
                    if(data.result){
                        window.location.href=\'/'.$arr['table'].'.xls\';
                    }
                    
                });
            }}, \'-\',';
        }
        
        if(in_array('update', $arr['gongneng'])){
            $update = '{ field: \'xiugai\', title: \'修改\', align: \'center\',formatter: xiugai, width: 50 },';
        }

        if(in_array('xiangqing', $arr['gongneng'])){
            $xiangqing = '{ field: \'xiangqing\', title: \'详情\', align: \'center\',formatter: xiangqing, width: 50 }';
        }


        //添加外表数据
        // if(isset($arr['addother']) && $arr['addother'] == 1){
        //     $addother = 'function addother(val,row){return \'<a href="javascript:get_html(\\\\\'/admin/'.$arr['othertable'].'/add?'.$arr['table'].'id=\'+row.id+\'\\\\\',\\\\\'添加'.$arr['otherdescription'].'\\\\\');"><font color="red">添加'.$arr['otherdescription'].'</font></a>\';}';
             
        //     $addotherfile = '{ field: \'addother\', title: \'添加'.$arr['otherdescription'].'\', align: \'center\',formatter: addother, width: 120 },'; 
        // }else{
        //     $addother = '';
        //     $addotherfile = '';
        // }
        foreach ($arr['othertable'] as $k => $value) {
            if(isset($arr['chakanother'][$k]) && $arr['chakanother'][$k] == 1){
                if(isset($arr['addother'][$k]) && $arr['addother'][$k] == 1){
                    $add = '<a href="javascript:get_html(\\\\\'/admin/'.$arr['othertable'][$k].'/add?'.$arr['table'].'id=\'+row.id+\'\\\\\',\\\\\'添加'.$arr['otherdescription'][$k].'\\\\\');"><font color="red">添加</font></a> | ';
                }else{
                    $add = '';
                }
                $addother .= 'function otherlist'.$k.'(val,row){return \''.$add.'<a href="javascript:alertotherlist'.$k.'(\'+row.id+\');"><font color="red">查看</font></a>\';}function alertotherlist'.$k.'(id){
                $fly.gethtml(\'/admin/'.$arr['othertable'][$k].'/index?'.$arr['table'].'id=\'+id,\'查看'.$arr['otherdescription'][$k].'\');}';
            }else{
                $addother .= '';
            }   

            if(isset($arr['chakanother'][$k]) && $arr['chakanother'][$k] == 1){
                $addotherfile .= '{ field: \'otherlist'.$k.'\', title: \'查看'.$arr['otherdescription'][$k].'\', align: \'center\',formatter: otherlist'.$k.', width: 80 },';
            }else{
                $addotherfile .= '';
            }
        }


        $toolbar = trim($toolbar,', \'-\',');



        //多对多关系
        //leftjoin连表
        $duoduiduo = '';
        $function_duoduiduo = '';
        for ($i=0; $i <= $arr['lian']; $i++) { 
            if($arr['leftjoin'][$i] && $arr['leftjoin'][$i]<>''){
                if($_POST['duoduiduo'][$i] && $_POST['duoduiduo'][$i] == 1){
                    //如果选中了多对多
                    //生成查外表数据的链接
                    $duoduiduo .= '{ field: \''.$arr['leftjoin'][$i].'_c\', title: \'查看'.$arr['ltablename'][$i].'\', align: \'center\',formatter: '.$arr['leftjoin'][$i].'_c, width: 150 },';
                    $function_duoduiduo .= 'function '.$arr['leftjoin'][$i].'_c(val,row){
        return \'<a href="javascript:get_html(\\\'/admin/'.$arr['table'].'/'.$arr['leftjoin'][$i].'and'.$arr['table'].'?id=\'+row.id+\'\\\',\\\'查看'.$arr['ltablename'][$i].'\\\');"><font color="red">查看'.$arr['ltablename'][$i].'</font></a>\';
    }';
                    

                }else{
                    $duoduiduo .= '';
                    $function_duoduiduo .= '';
                }
                
            }
        }
        
        $content2 = str_replace("***table***",$arr['table'],$content2);
        $content2 = str_replace("***search***",$search,$content2);
        $content2 = str_replace("***createtimesearch***",$createtimesearch,$content2);
        $content2 = str_replace("***chuangjianshijian***",$chuangjian_ifshow,$content2);
        $content2 = str_replace("***gengxinshijian***",$gengxin_ifshow,$content2);
        $content2 = str_replace("***field***",$field,$content2);
        $content2 = str_replace("***params***",$params,$content2);
        $content2 = str_replace("***toolbar***",$toolbar,$content2);
        $content2 = str_replace("***shanchu***",$shanchu,$content2);
        $content2 = str_replace("***xiangqing***",$xiangqing,$content2);
        $content2 = str_replace("***update***",$update,$content2);
        $content2 = str_replace("***function***",$function,$content2);
        $content2 = str_replace("***addother***",$addother,$content2);
        $content2 = str_replace("***addotherfile***",$addotherfile,$content2);
        $content2 = str_replace("***otherid***",$otherid,$content2);
        $content2 = str_replace("***duoduiduo***",$duoduiduo,$content2);
        $content2 = str_replace("***function_duoduiduo***",$function_duoduiduo,$content2);
        
 
        
        
        if (!is_dir($basedir.'/../View/'.$table.'/')) {
            mkdir($basedir.'/../View/'.$table.'/');
        }
        file_put_contents($basedir.'/../View/'.$table.'/index.html',$content2);





        //生成添加模板
        $content3 = file_get_contents($basedir.'/../Mather/add.html');

        //外表关联字段
        for ($i=0; $i <= $arr['lian']; $i++) { 
            if($arr['leftjoin'][$i] && $arr['leftjoin'][$i]<>''){
                if($_POST['duoduiduo'][$i] && $_POST['duoduiduo'][$i] == 1){
                    $addtr .= '';
                }else{
                    $addtr .= '<input type="hidden" id="'.$arr['leftjoin'][$i].'id"  name="'.$arr['leftjoin'][$i].'id" value="<?php echo $_GET[\''.$arr['leftjoin'][$i].'id\']?>">';
                }
                
            }
        }

        for ($i=0; $i < $arr['hang']; $i++) { 
            if(empty($arr['name'][($i)]) || strpos($arr['name'][($i)],'.')){
                continue;
            }
            //字段
            $input = '';
            $yanzheng = '';
            if ($arr['yanzheng'][($i)] == 1) {
                $yanzheng = 'class="easyui-validatebox" required="true" ';
                 if($arr['long'][($i)] != ''){
                    $yanzheng .= 'validType="length[0,'.$arr['long'][($i)].']"';
                 }
            }else {
                $yanzheng = '';
            }
            if($arr['long'][($i)] > 80){
                $arr['long'][($i)] = 80;
            }
            if($arr['type'][($i)] == 'text'){
                $input .= '<textarea name="'.$arr['name'][($i)].'" cols="80" rows="8" '.$yanzheng.' id="'.$arr['name'][($i)].'"></textarea>';
            }elseif($arr['type'][($i)] == 'longtext'){
                $textarea .= $arr['name'][($i)].' = K.create(\'textarea[name="'.$arr['name'][($i)].'"]\', {allowFileManager : true});';
                $input .= '<textarea name="'.$arr['name'][($i)].'" cols="90" rows="15" id="'.$arr['name'][($i)].'"></textarea>';
            }elseif($arr['type'][($i)] == 'pic'){
                $input .= '<input name="'.$arr['name'][($i)].'" id="'.$arr['name'][($i)].'" type="file">';
            }elseif($arr['type'][($i)] == 'piclist'){
                $input .= '<input name="'.$arr['name'][($i)].'[]" id="'.$arr['name'][($i)].'[]" type="file" multiple="true">';
            }elseif($arr['type'][($i)] == 'class'){
                $input .= '<?php echo $c'.$parentid[$i].';?>';
            }elseif($arr['type'][($i)] == 'datetime'){
                $input .= '<input name="'.$arr['name'][($i)].'" type="text" id="'.$arr['name'][($i)].'" size="20" onclick="SelectDate(this,\'yyyy-MM-dd hh:mm:ss\')" '.$yanzheng.' />';
            }else{
                $input .= '<input name="'.$arr['name'][($i)].'" type="text" id="'.$arr['name'][($i)].'" size="'.$arr['long'][($i)].'" '.$yanzheng.'  />';
            }
            $addtr .= '<tr><td width="11%" align="right" bgcolor="#FFFFFF">'.$arr['description'][($i)].'：</td><td width="89%" bgcolor="#FFFFFF">'.$input.'</td></tr>';
            
        }

        //替换内容
        $content3 = str_replace("***addtr***",$addtr,$content3);
        $content3 = str_replace("***textarea***",$textarea,$content3);
        $content3 = str_replace("***module***",$arr['table'],$content3);
        
        
        if (!is_dir($basedir.'/../View/'.$table.'/')) {
            mkdir($basedir.'/../View/'.$table.'/');
        }
        file_put_contents($basedir.'/../View/'.$table.'/add.html',$content3);




        //生成修改模板
        $content4 = file_get_contents($basedir.'/../Mather/update.html');
        $textarea = '';
        $addtr = '';

        for ($i=0; $i < $arr['hang']; $i++) { 
            if(empty($arr['name'][($i)]) || strpos($arr['name'][($i)],'.')){
                continue;
            }
            //字段
            $input = '';
            if($arr['long'][($i)] > 80){
                $arr['long'][($i)] = 80;
            }
            if($arr['type'][($i)] == 'text'){
                $input .= '<textarea name="'.$arr['name'][($i)].'" cols="80" rows="8" id="'.$arr['name'][($i)].'" '.$yanzheng.'><?php echo $result[\''.$arr['name'][($i)].'\'];?></textarea>';
            }elseif($arr['type'][($i)] == 'longtext'){
                $textarea .= $arr['name'][($i)].' = K.create(\'textarea[name="'.$arr['name'][($i)].'"]\', {allowFileManager : true});';
                $input .= '<textarea name="'.$arr['name'][($i)].'" cols="90" rows="15" class="easyui-validatebox" id="'.$arr['name'][($i)].'"><?php echo $result[\''.$arr['name'][($i)].'\'];?></textarea>';
            }elseif($arr['type'][($i)] == 'pic'){
                $input .= '<input name="'.$arr['name'][($i)].'" id="'.$arr['name'][($i)].'" type="file">';
            }elseif($arr['type'][($i)] == 'piclist'){
                $input .= '<input name="'.$arr['name'][($i)].'[]" id="'.$arr['name'][($i)].'[]" type="file" multiple="true">';
            }elseif($arr['type'][($i)] == 'class'){
                $input .= '<?php echo $c'.$parentid[$i].';?>';
            }elseif($arr['type'][($i)] == 'datetime'){
                $input .= '<input name="'.$arr['name'][($i)].'" type="text" id="'.$arr['name'][($i)].'" size="20" value="<?php echo $result[\''.$arr['name'][($i)].'\'];?>" onclick="SelectDate(this,\'yyyy-MM-dd hh:mm:ss\')" '.$yanzheng.' />';
            }else{
                $input .= '<input name="'.$arr['name'][($i)].'" type="text" id="'.$arr['name'][($i)].'" size="'.$arr['long'][($i)].'" value="<?php echo $result[\''.$arr['name'][($i)].'\'];?>" '.$yanzheng.' />';
            }
            $addtr .= '<tr><td width="11%" align="right" bgcolor="#FFFFFF">'.$arr['description'][($i)].'：</td><td width="89%" bgcolor="#FFFFFF">'.$input.'</td></tr>';
            
        }

        //如果是设置
        if(in_array('setting', $arr['gongneng'])){//如果是勾选了配置
            $guanbi = '';
        }else{
            $guanbi = '<input type="button" name="Submit" value="关闭" onClick="parent.layer.closeAll();" class="submit" />';
        }

        //替换内容
        $content4 = str_replace("***addtr***",$addtr,$content4);
        $content4 = str_replace("***textarea***",$textarea,$content4);
        $content4 = str_replace("***module***",$arr['table'],$content4);
        $content4 = str_replace("***guanbi***",$guanbi,$content4);
        
        
        if (!is_dir($basedir.'/../View/'.$table.'/')) {
            mkdir($basedir.'/../View/'.$table.'/');
        }
        file_put_contents($basedir.'/../View/'.$table.'/update.html',$content4);











        //生成详情模板
        $content5 = file_get_contents($basedir.'/../Mather/xiangqing.html');;
        $addtr = '';
        for ($i=0; $i < $arr['hang']; $i++) { 
            if(empty($_POST['name'][($i)]) || strpos($arr['name'][($i)],'.')){
                continue;
            }

            if($arr['type'][($i)] == 'class'){
                $addtr .= '<tr><td width="11%" align="right" bgcolor="#FFFFFF">'.$arr['description'][($i)].'：</td><td width="89%" bgcolor="#FFFFFF"><?php echo $c'.$parentid[$i].';?></td></tr>';
            }elseif($arr['type'][($i)] == 'pic'){
                $addtr .= '<tr><td width="11%" align="right" bgcolor="#FFFFFF">'.$arr['description'][($i)].'：</td><td width="89%" bgcolor="#FFFFFF"><img src="<?php echo $result[\''.$arr['name'][($i)].'\'];?>" width="100"></td></tr>';
            }elseif($arr['type'][($i)] == 'piclist'){
                $addtr .= '<tr><td width="11%" align="right" bgcolor="#FFFFFF">'.$arr['description'][($i)].'：</td><td width="89%" bgcolor="#FFFFFF"><?php $picarr = explode(\',\', $result[\''.$arr['name'][($i)].'\']);foreach ($picarr as $key => $value) {?><img src="<?php echo $value;?>" width="100"><?php }?></td></tr>';
            }else{
                $addtr .= '<tr><td width="11%" align="right" bgcolor="#FFFFFF">'.$arr['description'][($i)].'：</td><td width="89%" bgcolor="#FFFFFF"><?php echo $result[\''.$arr['name'][($i)].'\'];?></td></tr>';
            }
            
            
        }
        $addtr .= '<tr><td width="11%" align="right" bgcolor="#FFFFFF">创建时间：</td><td width="89%" bgcolor="#FFFFFF"><?php echo $result[\'createtime\'];?></td></tr><tr><td width="11%" align="right" bgcolor="#FFFFFF">修改时间：</td><td width="89%" bgcolor="#FFFFFF"><?php echo $result[\'updatetime\'];?></td></tr>';

        //替换内容
        $content5 = str_replace("***addtr***",$addtr,$content5);
        $content5 = str_replace("***module***",$arr['table'],$content5);
        
        
        if (!is_dir($basedir.'/../View/'.$table.'/')) {
            mkdir($basedir.'/../View/'.$table.'/');
        }
        file_put_contents($basedir.'/../View/'.$table.'/xiangqing.html',$content5);
    }



    public function addmenu($arr){
        // $rs = $this->getone_("select `id` from `my_rbac` where `name` = '".$arr['md']."' and `parentid`=0");
        $rs = M('my_rbac')->field('`id`')->where(array('name'=>$arr['md'],'parentid'=>0))->find();
        if($rs['id']){
            $parentid = $rs['id'];
        }else{
            //添加一级菜单
            // $parentid = $this->saveadd3_('my_rbac',array('name'=>$arr['md'],'c'=>$arr['table'],'parentid'=>0));
            $parentid = M('my_rbac')->add(array('name'=>$arr['md'],'c'=>$arr['table'],'parentid'=>0));
        }
        //添加二级菜单
        // $this->saveadd2_('my_rbac',array('name'=>$arr['module'],'c'=>$arr['table'],'m'=>'index','parentid'=>$parentid));
        M('my_rbac')->add(array('name'=>$arr['module'],'c'=>$arr['table'],'m'=>'index','parentid'=>$parentid));
        //添加功能
        if(in_array('add', $arr['gongneng'])){
            // $this->saveadd2_('my_rbac',array('name'=>$arr['module'].'添加','c'=>$arr['table'],'m'=>'add','parentid'=>$parentid,'type'=>0));
            M('my_rbac')->add(array('name'=>$arr['module'].'添加','c'=>$arr['table'],'m'=>'add','parentid'=>$parentid,'type'=>0));
        }
        if(in_array('del', $arr['gongneng'])){
            // $this->saveadd2_('my_rbac',array('name'=>$arr['module'].'删除','c'=>$arr['table'],'m'=>'del','parentid'=>$parentid,'type'=>0));
            M('my_rbac')->add(array('name'=>$arr['module'].'删除','c'=>$arr['table'],'m'=>'del','parentid'=>$parentid,'type'=>0));
        }
        if(in_array('update', $arr['gongneng'])){
            // $this->saveadd2_('my_rbac',array('name'=>$arr['module'].'修改','c'=>$arr['table'],'m'=>'update','parentid'=>$parentid,'type'=>0));
            M('my_rbac')->add(array('name'=>$arr['module'].'修改','c'=>$arr['table'],'m'=>'update','parentid'=>$parentid,'type'=>0));
        }
        if(in_array('xiangqing', $arr['gongneng'])){
            // $this->saveadd2_('my_rbac',array('name'=>$arr['module'].'详情','c'=>$arr['table'],'m'=>'xiangqing','parentid'=>$parentid,'type'=>0));
            M('my_rbac')->add(array('name'=>$arr['module'].'详情','c'=>$arr['table'],'m'=>'xiangqing','parentid'=>$parentid,'type'=>0));
        }
        
    }
    /**
     * 获取商户所有信息
     */
    public function getMerchantInfo(){
        $data = M('my_merchant')->field('id,name')->select();
        echo json_encode($data);
    }
    /**
     * 根据修改的用户的id 获取商户所有关联信息
     */
    public function getMerchantRelationInfo(){
        $adminid = I('POST.adminid','','trim');
        $data = M('my_operablerelation')->join('my_merchant on my_merchant.id=my_operablerelation.merchantid')->where('my_operablerelation.adminid='.$adminid)->field('my_operablerelation.merchantid,my_merchant.name')->select();
        echo json_encode($data);
    }
}
