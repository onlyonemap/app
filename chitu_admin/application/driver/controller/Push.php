<?php
namespace app\driver\controller;
use  think\Controller;   //使用控制器
//推送
class Push extends Base{

    public function index(){
       	import('getui.GeTui');
        $gt = new \getui\GeTui();
        $a=$gt->pushToAndroidApp();
        print_r($a);
    }
    public function pushtest(){
    	import('getui.GeTui');
        $gt = new \getui\GeTui();
        $a=$gt->pushMessageToSingle();
        print_r($a);
    }
    public function pushlist(){
    	import('getui.GeTui');
        $gt = new \getui\GeTui();
       $arr = array('09db99811c61ad69e3e813efa0f105bd','b77f989e851f9c99a9fef1780b49a8ed','6f5201bdcfa958d784ed2b524731a717','8809102469b72547f94476f73ad4093e','50004714a4a4444d56fd80296b26aad5');
        //$arr = array('b77f989e851f9c99a9fef1780b49a8ed','6f5201bdcfa958d784ed2b524731a717');
        $a=$gt->pushMessageToList($arr);
        print_r($a);
    }

    public function toapp(){
        import('getui.GeTui');
        $gt = new \getui\GeTui();
        $data = array('title' => "消息推送",'content' => '你收到了一条消息推送' , 'payload' => "自行处理");
        $city = array("上海","浙江");
        $a=$gt->pushMessageToApp($data, $city);
        print_r($a);
    }


}
















