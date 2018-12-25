<?php
namespace Admin\Controller;
use Think\Controller;
use Vendor\Petro\EcardEncryptUtil;
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/2/11
 * Time: 19:32
 */
class TestController extends Controller
{
    /**
     * 测试中石化的接口
     */
    public function sinopec(){
        $model = new EcardEncryptUtil();
        $str = "12bvdde`中户人民共和国，1234@￥#%&*（）-=|+_}{[]/.,;:,.>》》。，《dkfjaskfaskdjfkdasj";
        $enc = $model->encrypt($str,"f8ee541137a2aa381abaac17886653ba"/*C("SECRETKEY")*/);
        $dnc = $model->decrypt($enc, "f8ee541137a2aa381abaac17886653ba"/*C("SECRETKEY")*/);
        $aa = $model->md532($str);
        //test
        var_dump($enc);
        var_dump($dnc);
    }
}