<?php
namespace Admin\Controller;
use Think\Controller;
class RbacController extends BaseController {
    public function index(){
        $this->display();
    }


    public function getlist(){
        $sql = "select * from `my_rbac` where `type`=1 order by `order` asc";
        $result = $this->list_($sql);
        echo $result;
    }


    public function saveadd(){
        $_POST['createtime'] = date('Y-m-d H:i:s',time());
        $_POST['updatetime'] = date('Y-m-d H:i:s',time());
        M('my_rbac')->add($_POST);
        echo 'success';
    }


    public function saveupdate(){
        $id = $_POST['id'];
        unset($_POST['id']);
        $arr = $_POST;
        M('my_rbac')->where(array('id'=>$id))->save($arr);
        echo 'success';
    }

    public function del(){
        $id = $_POST['id'];
        M('my_rbac')->where(array('id'=>$id,'parentid'=>$id))->delete();
        echo 'success';
    }

// //该导入功能已经弃用
//     public function daoru(){
//         exit;
//         $dirname = dirname(__FILE__);
//         $dir = dir($dirname);
//         //清除表内容
//         $this->query_("delete from `my_rbac`");
//         $this->query_("TRUNCATE TABLE `my_rbac`");
//         while (($f = $dir->read()) !== false)
//         {
//             if($f != '.' && $f != '..'){
//                 $file = file_get_contents($dirname."/".$f);
//                 if(strpos($file, '$$$$') && strpos($file, '####') && !strpos($file,'不导入该功能')){
//                     preg_match_all('/[$$$$](.*)[$$$$]/',$file,$arr_one_name);//获取一级模块名称
//                     preg_match_all('/class(.*)extends/',$file,$arr_controller);//获取控制器名
//                     preg_match_all('/[####](.*)[####]/',$file,$arr_two_name);//获得二级模块名称
//                     preg_match_all('/@@@@(.*)@@@@/',$file,$arr_right_name);//获得非左侧控制器名

//                     //一级
//                     $one_name = trim($arr_one_name[0][0],'$$$$');
//                     $one_name_arr = explode('-', $one_name);
//                     //if($one_name_arr[2]){
//                         //$order = $one_name_arr[2];
//                     //}else{
//                         //$order = 1;
//                     //}
//                     //$arr_one = array('name'=>$one_name_arr[0],'c'=>$one_name_arr[1],'parentid'=>0,'order'=>$order);         
//                     $arr_one = array('name'=>$one_name_arr[0],'c'=>$one_name_arr[1],'parentid'=>0);//去掉了排序         
                    

//                     //插入一级
//                     $rs = $this->getone_("select `id` from `my_rbac` where `name`='".$one_name_arr[0]."'");
//                     $id = $rs['id'];
//                     if($id){
//                         $this->saveupdate2_('my_rbac',$arr_one,$id);
//                         $parentid = $id;
//                     }else{
//                         $parentid = $this->saveadd_id('my_rbac',$arr_one);
//                     }

//                     //插入左侧二级
//                     //$two_controller = trim(trim($arr_controller[0][0],'class '),'extends');
//                     $two_controller = $one_name_arr[1];
//                     $two_controller = strtolower(trim($two_controller));
//                     foreach ($arr_two_name[0] as $key => $value) {
//                         if(strpos($value,'-')){

//                             //二级
//                             $two_name_arr = explode('-',trim($value,'####'));
//                             $two_name = $two_name_arr[0];
//                             $two_method = $two_name_arr[1];

//                             $arr_two = array('name'=>$two_name,'c'=>$two_controller,'m'=>$two_method,'parentid'=>$parentid);
//                             $rs2 = $this->getone_("select `id` from `my_rbac` where `c` = '".$two_controller."' and `m`= '".$two_method."' and `parentid`=".$parentid);
//                             $id2 = $rs2['id'];
//                             if($id2){
//                                 $this->saveupdate2_('my_rbac',$arr_two,$id2);
//                             }else{
//                                 $this->saveadd_id('my_rbac',$arr_two);
//                             }
//                         }
//                     }

//                     //插入非左侧
//                     foreach ($arr_right_name[0] as $k => $v) {
//                         if(strpos($v,'-')){

//                             //非左侧
//                             $right_name_arr = explode('-',trim($v,'@@@@'));
//                             $right_name = $right_name_arr[0];
//                             $right_method = $right_name_arr[1];

//                             $arr_right = array('name'=>$right_name,'c'=>$two_controller,'m'=>$right_method,'parentid'=>$parentid,'type'=>0);
//                             $rs3 = $this->getone_("select `id` from `my_rbac` where `c` = '".$two_controller."' and `m`= '".$right_method."' and `parentid`=".$parentid);
//                             $id3 = $rs3['id'];
//                             if($id3){
//                                 $this->saveupdate2_('my_rbac',$arr_right,$id3);
//                             }else{
//                                 $this->saveadd_id('my_rbac',$arr_right);
//                             }
//                         }
//                     }
//                 }
                
//             }
//         }
//         echo '执行完毕！';
//     }
}