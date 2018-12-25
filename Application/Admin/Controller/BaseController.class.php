<?php
namespace Admin\Controller;
use Think\Controller;
class BaseController extends Controller {
    var $ceng = 0;
    public function __construct(){
        parent::__construct();
        //判断权限
        check_login();
        //验证具体权限
        $url = ltrim(__SELF__,'/index.php');
        $url_arr = explode('/', $url);
        $c = $url_arr[1];
        $m = ACTION_NAME;
        $data = M('my_rbac')->field('`id`')->where(array('c'=>$c,'m'=>$m))->find();
        $levelstr_arr = explode(',', $_SESSION['levelstr']);
        if(!in_array($data['id'], $levelstr_arr) && ($c <> 'index' && $m <> 'index') && $_SESSION['levelstr'] <> 'all' && in_array($m, C('ACTION'))){
            if(in_array($m, array('add','update','xiangqing'))){
                echo '<div style="margin-top:200px;float: none;margin-right: 0px;margin-left: 0px;text-align:center;">您没有权限! </div>';exit;
            }
            exit('您没有权限');
            $result = array('result'=>false,'data'=>'','message'=>'您没有权限！');
            echo json_encode($result);exit;
        }
        if(intval(session('adminid')) != 1 || intval(session('groupid')) != 1){
            $adminData = M('my_admin')->where('id='.intval(session('adminid')))->find();
            if(!empty($adminData['merchantid'])){
                session('adminmerchantid',$adminData['merchantid']);
            }else{
                echo '<div style="margin-top:200px;float: none;margin-right: 0px;margin-left: 0px;text-align:center;">联系管理员给您授权商户! <a href="/admin/Login/sendemail">点击发送邮件</a></div>';exit;
            }
        }
		self::addoperationlog($data['id']);
		//echo json_encode($arr);
    }

	public function addoperationlog($id){
		$arr = M('my_rbac')->where(array('id'=>$id))->find();
		if($arr['name']){
			$operationlog = M('my_operationlog');
			$oper['createtime'] = date('Y-m-d H:i:s',time());
			$oper['updatetime'] = date('Y-m-d H:i:s',time());
			$oper['adminid'] = session('id');
			$oper['name'] = $arr['name'];
			$oper['url'] = $_SERVER["REQUEST_SCHEME"].'://'.$_SERVER["SERVER_NAME"].U($arr['c'].''.$arr['m']);
			$oper['operator'] = session('user_name');
			$operationlog->add($oper);
		}
	}
    public function list_($sql){
        $db = M();
        $result['rows'] = $db->query($sql);
        foreach ($result['rows'] as $key => $value) {
            if($value['parentid'] <> 0){
                $result['rows'][$key]['_parentId'] = $value['parentid'];
            }
        }
        return json_encode($result);
    }


    public function pagelist_($sql){
        $page = $_POST['page'];
        $rows = $_POST['rows'];
        $result['rows'] = M()->query($sql.' limit '.($page-1)*$rows.','.$rows);
        //统计总数
        $rs = M()->query($sql);
        $result['total'] = count($rs);
        return $result;
    }


    //获取分类
    public function getclass($id=0,$idname='',$selectid='',$limit=''){
        $class = M('my_class');
        if($selectid!==''){//修改页面里
            //$sql = "select * from `my_class` where `parentid`=".$id." order by `order` asc ".$limit;
            $where['parentid'] = $id;
            
            $result = $class->where($where)->order('`order`')->select();

            if($idname !== ''){
                $str = '<select name="'.$idname.'" id="'.$idname.'">';
                $this->ceng = 0;
            }else{
                $str = '';
            }
            $this->ceng ++;
            $s = '';
            for ($i = 1; $i < $this->ceng; $i++) {
                $s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            
            foreach ($result as $key => $value) {
                if($value['id'] == $selectid){
                    $selected = 'selected="selected"';
                }else{
                    $selected = '';
                }
                $str .= '<option value="'.$value['id'].'" '.$selected.'>'.$s.'|'.$value['name'].'</option>';
                $str .= $this->getclass($value['id'],'',$selectid);
                $this->ceng --;
            }

            if($idname !== ''){
                $str .= '</select">';
            }
            return $str;
        }else{
            //$sql = "select * from `my_class` where `parentid`=".$id." order by `order` asc";
            $where['parentid'] = $id;
            $result = $class->where($where)->order('`order`')->select();
            if($idname !== ''){
                $str = '<select name="'.$idname.'" id="'.$idname.'"><option value="">请选择</option>';
                $this->ceng = 0;
            }else{
                $str = '';
            }
            $this->ceng ++;
            $s = '';
            for ($i = 1; $i < $this->ceng; $i++) {
                $s .= '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
            }
            foreach ($result as $key => $value) {
                $str .= '<option value="'.$value['id'].'">'.$s.'|'.$value['name'].'</option>';
                $str .= $this->getclass($value['id'],'');
                $this->ceng --;
            }
            if($idname !== ''){
                $str .= '</select">';
            }
            return $str;
        }
        
    }



    //用到左侧菜单
    public function getrbac(){
        if($_SESSION['levelstr'] == 'all'){
            $sql = "select * from `my_rbac` where `parentid` = 0 order by `order` asc";
        }else{
            $sql = "select * from `my_rbac` where `parentid` = 0 and `id` in(select `parentid` from `my_rbac` where `id` in(".$_SESSION['levelstr'].")) order by `order` asc";
        }
        $result = M()->query($sql);
        foreach ($result as $key => $value) {
            if($_SESSION['levelstr'] <> 'all'){
                $where = "and `id` in(".$_SESSION['levelstr'].")";
            }else{
                $where = "";
            }
            $sql2 = "select * from `my_rbac` where `parentid` = ".$value['id']." and `type`=1 ".$where." order by `order` asc";
            $result2 = M()->query($sql2);
            $result[$key]['list'] = $result2;
        }
        return $result;
    }

    //用到用户编辑权限
    public function getrbac2(){
        if($_SESSION['levelstr'] == 'all'){
            $sql = "select * from `my_rbac` where `parentid` = 0 order by `order` asc";
        }else{
            $sql = "select * from `my_rbac` where `parentid` = 0 and `id` in(select `parentid` from `my_rbac` where `id` in(".$_SESSION['levelstr'].")) order by `order` asc";
        }
        $result = M()->query($sql);
        foreach ($result as $key => $value) {
            if($_SESSION['levelstr'] <> 'all'){
                $where = " and `id` in(".$_SESSION['levelstr'].")";
            }else{
                $where = "";
            }
            $sql2 = "select * from `my_rbac` where `parentid` = ".$value['id'].$where." order by `order` asc";
            $result2 = M()->query($sql2);
            $result[$key]['list'] = $result2;
        }
        return $result;
    }


    public function getclassname($id){
        $data = M('my_class')->field('`name`')->where(array('id'=>$id))->find();
        return $data['name'];
    }

    //导出excel
    public function export_xls($filename='',$fields=null,$OrdersData=null){
        Vendor('PHPExcel.PHPExcel');
        Vendor('PHPExcel.PHPExcel.IOFactory');
        Vendor('PHPExcel.PHPExcel.Reader.Excel5');
        // Create new PHPExcel object  
        $objPHPExcel = new \PHPExcel();  
        //样式
        $Alignment = new \PHPExcel_Style_Alignment();
        //边框
        $Border = new \PHPExcel_Style_Border();
        // Set properties  
        $objPHPExcel->getProperties()->setCreator("ctos")  
            ->setLastModifiedBy("ctos")  
            ->setTitle("Office 2007 XLSX Test Document")  
            ->setSubject("Office 2007 XLSX Test Document")  
            ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")  
            ->setKeywords("office 2007 openxml php")  
            ->setCategory("Test result file");  
    
        //设置字体大小
        $objPHPExcel->getActiveSheet()->getDefaultStyle()->getFont()->setSize(10);  

        //设置第1，2行高度  
        $objPHPExcel->getActiveSheet()->getRowDimension('1')->setRowHeight(22);  
        $objPHPExcel->getActiveSheet()->getRowDimension('2')->setRowHeight(20);  
        
        
        //设置第一行左对齐
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal($Alignment::HORIZONTAL_LEFT);  
        //设置第一行垂直居中
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setVertical($Alignment::VERTICAL_CENTER);  
 
        //  第一行合并单元格
        $objPHPExcel->getActiveSheet()->mergeCells('A1:N1');  
  
        // 设置首行内容
        $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue('A1', $filename.' 共'.count($OrdersData).'条记录  导出时间:'.date('Y-m-d H:i:s'))  ;
        //设置第二行标题内容
        foreach ($fields as $key => $value) {
            //导出标题
            $objPHPExcel->setActiveSheetIndex(0)  
            ->setCellValue(numtostr(($key)).'2', $value); 
            //设置列宽
            $objPHPExcel->getActiveSheet()->getColumnDimension(numtostr(($key)))->setWidth(20); 
            //设置垂直居中
            $objPHPExcel->getActiveSheet()->getStyle(numtostr(($key)).'2')->getAlignment()->setVertical($Alignment::VERTICAL_CENTER); 
            //设置水平居中 
            $objPHPExcel->getActiveSheet()->getStyle(numtostr(($key)).'2')->getAlignment()->setHorizontal($Alignment::HORIZONTAL_CENTER);  

            //设置边框
            $objPHPExcel->getActiveSheet()->getStyle(numtostr(($key)).'2')->getBorders()->getAllBorders()->setBorderStyle($Border::BORDER_THIN);  
            //设置字体加粗
            $objPHPExcel->getActiveSheet()->getStyle(numtostr(($key)).'2')->getFont()->setBold(true); 
        }   

        
      
        // 设置更多的导出行
        for($i=0;$i<=count($OrdersData)-1;$i++){  
            
            $a=0;
            foreach ($OrdersData[$i] as $k => $v) {
                //赋值
                $objPHPExcel->getActiveSheet(0)->setCellValue(numtostr(($a)).($i+3), ' '.$v);  
                //设置垂直居中
                $objPHPExcel->getActiveSheet()->getStyle(numtostr(($a)).($i+3))->getAlignment()->setVertical($Alignment::VERTICAL_CENTER);  
                //设置水平居中
                $objPHPExcel->getActiveSheet()->getStyle(numtostr(($a)).($i+3))->getAlignment()->setHorizontal($Alignment::HORIZONTAL_CENTER);  
                 //设置边框
                $objPHPExcel->getActiveSheet()->getStyle(numtostr(($a)).($i+3))->getBorders()->getAllBorders()->setBorderStyle($Border::BORDER_THIN); 
                 $a++;
            }   


            $objPHPExcel->getActiveSheet()->getRowDimension($i+3)->setRowHeight(20);  
            
        }   
        // Rename sheet  
        $objPHPExcel->getActiveSheet()->setTitle($filename.'记录');  
  
  
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet  
        $objPHPExcel->setActiveSheetIndex(0);  
  
        ob_end_clean();//清除缓冲区,避免乱码
        // Redirect output to a client’s web browser (Excel5)  
        header('Content-Type: application/vnd.ms-excel');  
        $filenames=$filename.'('.date('Ymd-His').').xls';
        header("Content-Disposition: attachment;filename={$filenames}");  
        header('Cache-Control: max-age=0');  
  
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');  
        $objWriter->save(BASEPATH.'/'.$filename.'.xls'); 
    }


    /**
    消息推送
    $code 举例'"123456"'
    */
    public function jpush($title,$content,$code){
        vendor('Jpush.jpush#class');
        $jpush = new \jpush(false);
        $jpush->send($title,$code);//通知
        $jpush->send2($title,$content,$code);//消息
    }
}