<?php
namespace app\company\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Index  extends Base
{
	
	//首页
    public function index(){
        $result = DB::table('ct_news')->where('delstate',1)->order('id desc')->paginate(10);
        $page = $result->render();
        $this->assign('list',$result);
        $this->assign('page',$page);
        $this->assign('lista','11');
    	return view('index/index'); 
    }
     public function details(){
        $id = input('id');
        $result = DB::table('ct_news')->where('id',$id)->find();
        $this->assign('result',$result);
        return view('index/details');
     }
     public function feedback(){
        return view('index/toastr');
     }

     public function map(){
        return view('index/map');
     }


     
   public function qrcode(){
        $PNG_TEMP_DIR = dirname(dirname(dirname(dirname(__FILE__)))).DIRECTORY_SEPARATOR.'public'.DIRECTORY_SEPARATOR.'temp'.DIRECTORY_SEPARATOR;
       // echo $PNG_TEMP_DIR;exit();
         if (!file_exists($PNG_TEMP_DIR))
        mkdir($PNG_TEMP_DIR);
    
    $PNG_WEB_DIR = '/temp/';
    $filename = $PNG_TEMP_DIR.'test.png';
        Vendor('phpqrcode.phpqrcode');

        //生成二维码图片
        $object = new \QRcode();
        $url='http://www.shouce.ren/';//网址或者是文本内容
        $level=3;
        $size=4;
        $errorCorrectionLevel =intval($level) ;//容错级别
        $matrixPointSize = intval($size);//生成图片大小
        $object->png($url, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
          echo '<img src="'.$PNG_WEB_DIR.basename($filename).'" /><hr/>';  

    }

    public function info(){
            //$filename = '/static/chitu.apk';
        $filename = '/static/user_header.png';
    //header('application/vnd.android.package-archive');//android包apk下载 的专属头文件
    header('Content-Type: application/octet-stream');
    header("Content-Length: " . $filename); //这个头文件是为了下载时显示文件大小的，如果没有此头部，(手机)下载时不会显示大小
    header("Content-Disposition: attachment; filename=".basename($filename));
    readfile($filename);
    }

     /**
     * 订单提醒
     */
    public function sendnotice(){
        //请求地址
        $uri = "http://goeasy.io/goeasy/publish";
        // 参数数组
        $data = [
            'appkey'  => "BS-665a449ba3624903a7c893614c2ef082",
            'channel' => "csdnNotification",
            'content' =>"您有新的订单"
        ];
        $ch = curl_init ();
        curl_setopt ( $ch, CURLOPT_URL, $uri );//地址
        curl_setopt ( $ch, CURLOPT_POST, 1 );//请求方式为post
        curl_setopt ( $ch, CURLOPT_HEADER, 0 );//不打印header信息
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );//返回结果转成字符串
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $data );//post传输的数据。
        $return = curl_exec ( $ch );
        curl_close ( $ch );
        print_r($return);
    }
    public function send(){
      $this->sendnotice();
      return view('index/send');
    }

  
}
