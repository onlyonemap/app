<?php
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Qrcode  extends Base
{
    
    /**
     * 扫描二维码跳转app下载页面
     * @param  string type   [app类型 1 用户端 2 承运端]
     * @return [type]        [description]
     */
    public function qrcode(){
        $type = input('type');
        $this->assign('type',$type);
        return view('qrcode/qrcode');
    }
    
    /**
     * 整车微信扫码支付二维码生成
     * @Auther: 李渊
     * @Date: 2018.7.17
     * @return [type] [description]
     */
    public function setcard()
    {
        require_once EXTEND_PATH.'wxpay/example/phpqrcode/phpqrcode.php';
        $url = urldecode(input("data"));
        $QRcode = new \QRcode();
        $img = $QRcode->png($url);
        exit();
    }
}
