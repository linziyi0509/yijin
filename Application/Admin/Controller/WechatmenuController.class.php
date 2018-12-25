<?php
namespace Admin\Controller;
/**
 *@FILENAME:Admin\Controller;
 *@AUTHOR:dudongjiang;
 *@DATE:2016年9月19日;
 *@EFFORT:微信菜单的增加、删除、修改、查看;
 **/
class WechatmenuController extends BaseController {
    /**
     *@FUNCNAME:index;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月19日;
     *@EFFORT:展示数据，也可以修改;
     **/
     
    public function index(){
        $weObj = wechat_connect();
        $menuList = $weObj->getMenu();
        $result = $menuList["menu"]["button"];
        $this->assign("list",$result);
        $this->display();
    }
    /**
     *@FUNCNAME:create;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月19日;
     *@EFFORT:生成菜单;
     **/
    public function create(){
        $weObj = wechat_connect();
        $data = $_POST;
        /**
         * 按钮判断
         * 1.view和click的区别
         * click 点击推事件用户点击click类型按钮后，微信服务器会通过消息接口推送消息类型为event的结构给开发者（参考消息接口指南），并且带上按钮中开发者填写的key值，开发者可以通过自定义的key值与用户进行交互；
         * view 跳转URL用户点击view类型按钮后，微信客户端将会打开开发者在按钮中填写的网页URL，可与网页授权获取用户基本信息接口结合，获得用户基本信息
         * 
         */
        $countparent = count($data);
        for($i=0;$i<$countparent;$i++){
            //主菜单循环处理数据
            //处理 判断主菜单的名称是否有值存在
            if($data["parents"][$i]["name"]!=""){
                //存在 判断主菜单是什么类型的
                if(check_url($data["url"][$i]["url"])){
                    $menu[$i]["url"] = $data["url"][$i]["url"];
                    $menu[$i]["type"] = "view";
                }else if($data["url"][$i]["url"] == ""){
                    
                }else{
                    $menu[$i]["key"] = $data["url"][$i]["url"];
                    $menu[$i]["type"] = "click";
                }
                $menu[$i]["name"] = $data["parents"][$i]["name"];
                //处理子菜单
                $countsun = count($data["sub_button"][$i]["name"]);
                if($countsun>0){
                    for($j=0; $j<$countsun; $j++){
                        if($data["sub_button"][$i]["name"][$j]!=""){
                             if(check_url($data["sub_button"][$i]["url"][$j])){
                                 $menu[$i]["sub_button"][$j]["url"] = $data["sub_button"][$i]["url"][$j];
                                 $menu[$i]["sub_button"][$j]["type"] = "view";
                             }else{
                                 $menu[$i]["sub_button"][$j]["key"] = $data["sub_button"][$i]["url"][$j];
                                 $menu[$i]["sub_button"][$j]["type"] = "click";
                             }
                             $menu[$i]["sub_button"][$j]["name"]= $data["sub_button"][$i]["name"][$j];
                        }
                    }
                }
            }
        }
        $button['button']=@$menu;
        writelog('button按钮的值：',$button);
        if($weObj->createMenu($button)){
            $this->success("菜单创建成功");
        }else{
            $this->error("菜单创建失败");
        }
    }
    /**
     *@FUNCNAME:delete;
     *@AUTHOR:dudongjiang;
     *@DATE:2016年9月19日;
     *@EFFORT:删除菜单，请不要随意操作;
     **/
    public function delete(){
         $weObj = wechat_connect();
         $deleteres = $weObj->deleteMenu();
         if($deleteres){
             $this->success("菜单删除成功");
         }else{
             $this->error("菜单创建失败");
         }
    }

}