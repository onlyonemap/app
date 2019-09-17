<?php
/**
 * 用户app接口基类文件
 * author : 依然范儿特西
 */
namespace app\user\controller;
use think\Controller;
use think\Request; 
use  think\Db;  //使用数据库操作

class Base extends Controller
{
	function __construct(){
		parent::__construct();
		$config_mess = Db::table("ct_config")->where('id','13')->find();
        $this->config = $config_mess['auth_price'];
	}

	// 空操作
	public function _empty(){
		return json(['code'=>'110','message'=>'赤途(上海)供应链管理有限公司']);
	}

    
	// 并发执行，备注：使用控制器才可以使用此方法
	public  function _initialize(){
		//echo "并发操作</br>";
	}


    // 消息推送
    public function push_msg(){
        import('getui.GeTui');
        $gt = new \getui\GeTui();
        $gt->pushToAndroidApp();
    }
    // 消息推送
    public function pushMessage($data,$city){
        import('getui.GeTui');
        $gt = new \getui\GeTui();
        $a =  $gt->pushMessageToApp($data, $city);
    }

    
    // 监听sql
    public function listion_sql(){
        Db::listen(function($sql,$time,$explain){
            // 记录SQL
            echo $sql. ' ['.$time.'s]';
            // 查看性能分析结果
            dump($explain);
        });
    }

    /**
     * 查询用户启动APP次数
     * @auther 李渊
     * @date 2018.6.14 
     * @param [string] token 用户令牌
     * @return [type] [description]
     */
    public function app_activate(){
        $token = input('token');  //验证令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        // 开始时间戳
        $start = mktime(0,0,0,date("m",time()),date("d",time()),date("Y",time()));
        // 结束时间戳
        $end = mktime(23,59,59,date("m",time()),date("d",time()),date("Y",time()));
        // 查询条件
        $where['starttime'] = array(array('gt',$start),array('lt', $end));
        $where['userid'] = $user_id;
        $result = Db::table('ct_app_activate')->where($where)->find();
        if ($result) {
            Db::table('ct_app_activate')->where(array('id'=>$result['id'],'userid'=>$user_id))->update(array('data_times'=>$result['data_times']+1));
            return json(['code'=>'1001','message'=>'记录成功']);
        }else{
            $data['starttime'] = time();
            $data['usertype'] = 2;
            $data['data_times'] = 1;
            $data['userid'] = $user_id;
            Db::table('ct_app_activate')->insert($data);
            return json(['code'=>'1001','message'=>'记录成功']);
        }
    }

    // 删除验证码记录
    public function delete_yzm($phone){
       Db::table("ct_validate_record")->where('phone',$phone)->delete();
    }

    /**
     * 单文件上传
     * name：表单上传文件的名字
     * ext： 文件允许的后缀，字符串形式
     * path：文件保存目录
     */
    public function file_upload($name,$ext,$path){
    	$dir_path=ROOT_PATH.'/public/uploads/'.$path;
    	if (!is_dir($dir_path))mkdir($dir_path, 0777);// 使用最大权限0777创建文件
	    $file = request()->file($name);
	    $info = $file->move($dir_path,true,false);
	    if($info){
	        // 成功上传后 获取上传信息
	        $file_path = $info->getSaveName();
	        $data['file_path'] = '/uploads/'.$path.'/'.$info->getSaveName();
	    }else{
	        // 上传失败获取错误信息
	        $data['file_path'] =$file->getError();
	    }

	    return $data;
    }

	/**
     * @param 生成token
     * @param   
     * @return 加密后的字符串
     */
	public function product_token($user_id){
    	$token_key = time().mt_rand('000000','999999')."codephp";
    	//判断数据是否已存在
        $condition['user_id'] = $user_id;
        $res = Db::table("ct_user_token")->where($condition)->find();
        if($res){
            //已存在更新
            $upda = array(
        		'token'=>$token_key,
        		'last_time'=>time()
            );
            Db::table("ct_user_token")->where('user_id',$user_id)->update($upda);
        }else{
            //不存在新增
            $indata = array(
                'user_id'=>$user_id,
                'last_time'=>time(),
                'token'=>$token_key
                );
            Db::table("ct_user_token")->insert($indata);
           
        }
    	$token = $this->encode($token_key);
    	return $token;
    }

    /**
     * 验证token
     * @param  [type] $token [description]
     * @return [type]        [description]
     */
	public function check_token($token){
        // 解密字符串
        $token_decode = $this->decode($token);
        // 查询
        $where['token'] = $token_decode;
        // 查询token
        $res = Db::table("ct_user_token")->where($where)->find();
        // 验证token是否存在
        if(empty($res)){ // 不存在
        	$data['status'] = '1';  //非法请求
        }else{ // 存在
        	// 验证是否超时：目前设置token有效时间为1年
	        $oldtime = date('Y-m-d H:i:s',$res['last_time']);
	        $check_time = strtotime(date("Y-m-d H:i:s",strtotime("$oldtime   +1  year")));
            // 验证是否超时
	        if($check_time  <  time()){
	        	$data['status'] = '2'; // token已过期	
	        }else{
	        	$data['status'] = '3'; // 通过
                // 获取用户id
	        	$data['user_id'] = $res['user_id'];
                // 验证用户是否存在 
                $isUser = DB::table('ct_user')->where(array('uid' => $res['user_id'], 'delstate'=>1 ))->find();
                // 判断用户是否存在
                if(empty($isUser)){ // 如果没有定义非法请求 
                    $data['status'] = '1';  // 非法请求
                }
	        }
        }
        return $data;
	}


	/**
     * @param 字符串加密
     * @param
     * @return 加密后的字符串
     */
    public  function encode($string = '') {
        $skey="ctphp696969";
        $strArr = str_split(base64_encode($string));
        $strCount = count($strArr);
        foreach (str_split($skey) as $key => $value)
            $key < $strCount && $strArr[$key].=$value;
        return str_replace(array('=', '+', '/'), array('O0O0O', 'o000o', 'oo00o'), join('', $strArr));
    }
    
    /**
     * @param 字符串解密
     * @param
     * @return 解密后的字符串
     */
    public  function decode($string = '') {
        $skey="ctphp696969";
        $strArr = str_split(str_replace(array('O0O0O', 'o000o', 'oo00o'), array('=', '+', '/'), $string), 2);
        $strCount = count($strArr);
        foreach (str_split($skey) as $key => $value)
            $key <= $strCount  && isset($strArr[$key]) && $strArr[$key][1] === $value && $strArr[$key] = $strArr[$key][0];
        return base64_decode(join('', $strArr));
    }
    

    /**
     * @param 二维数组去重
     * @param
     * @return 去重后的数组
     */
    public function assoc_unique($arr, $key) { 
        $rAr=array(); 
        for($i=0;$i<count($arr);$i++) { 
            if(!isset($rAr[$arr[$i][$key]])) { 
                $rAr[$arr[$i][$key]]=$arr[$i]; 
            } 
        } 
        $arr=array_values($rAr); 
        return $arr;
    } 

    /**
     * @param 二维数组转字符串
     * @param
     * @return 字符串
     */    
    public function arr_to_str($arr){ 
        $temp= array();
       foreach ($arr as $v){ 
           $v = join(",",$v); //可以用implode将一维数组转换为用逗号连接的字符串，join是别名 
           $temp[] = $v; 
       } 
       $t = '';
       foreach($temp as $v){ 
          $t.=$v.","; 
       } 
       $t=substr($t,0,-1); //利用字符串截取函数消除最后一个逗号 
       return $t; 
    }
    //支付宝支付
    public function ali_pay($type,$data){
        require_once EXTEND_PATH.'Alipay/aop/AopClient.php';
        require_once EXTEND_PATH.'Alipay/aop/request/AlipayTradeAppPayRequest.php';
        $aop = new \AopClient();
        $request = new \AlipayTradeAppPayRequest();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = "2017052307318743";
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAuWqafyecwj1VxcHQjFHrPIqhKrfMPjQRVRTs7/PvGlCXOxV34KaAop4XWEBKgvWhdQX2JkMDLSwPkH790TBJVS84/zQ6sjanpHjgT82/AimuS+/Vk8pB/pAfnOnRN3dhe6y2i9kzJPU62Uj9qn5jJXbWJhyM16Zxdk7GBOChis3C3KvB2WN8qAQawqfUvgHRm/yUgNfVUutKRMdDdQxQypwxkEP50+U9qKeSQecZRyo6xmJ5CWbULQ7FpV5q6lmM7SbyBuyDVk7z4itLIgE8qpt6B3cp9Qm3U3f6DoVJA2LAjinP4v6kNVb/f5qu8VpmR0DD+dRJ1+ujDz1EC/f/lwIDAQABAoIBAHrS0DcM8X2GDcxrQA/DsDUxi+N1T1mhOh4HN5EYILpoylU8OmXZRfrzCHnQVMt9lQ+k/FKKL4970W+hf9dTyjAgkPwVCBDHvbNo0wZqP25aV/g7jlpRL/hGVnqmNI4uiafYWDA5l/SScgI/pLGM+XZ2yxMB9JZhzmVVdz0B5GDCHcjQUkY3//8Tpgw6ylngrq67KjWDbZPAZQHcpj/hdYPOu7Z1kXp30jtdEZi6S+7ZJe/AWMSuEtwWsM53ZOyxqPjSwbW8XfWHHbG3yKF6sngCmwRpwX5rp1EjSsVhA5rbpCM0jbYCKp977XwkGtG6xAOydZdz0WHyirDUTA3PMTECgYEA4lzvyfcg0SyaOWVszwxcWntVm6sQG7deaSlW92Urhy7qaDnv4Ad8TEe0M0QGVllnZUDJA3x8NzoD5DlFROUGZpI/uJk5a0dQlvMbyzS2rx2v4TP19Xm5D7iQk0RK5Zry0K/Fj1kZusIVm3qwsl1DlunAfGipZ1TV0C7QNUJcW0kCgYEA0bE/3ljnSPsKjpc+projOuaLqf7+0x3ITaYle60MbwZrjUnX3cSwbqN3Iu12Npa3mI+RwTyDifFgWB/8hFoqTecFGDnxRa1e7DLlJX9FkIMtoroVsDJUMD+HUx01t9V8fEqVPNyRmnbFyXfdHrRb7zYefwuPZcoE18reADc9o98CgYB1zDl5F+L7F8P2ZIK4SM1yxMYrKV1LnyRBg6LfQcXiJpcTwDrFkf+sTpBHMXo+y23UMl+pMcoOj2FhDjCvBqRLEoaYkRxhaI5Wz5LCL991x/Q0NO8lXL/in4CVMq/rRrRfx2j/DTYni0LlU3bKi2BWE7T4yRqHTI2sNgBiBvO7CQKBgQCDsHNR6jdmR/J7VlTMVH2nkf4IRtI2N7ABw+QqZaU3XKrS0ps09T9wXEyHrOXepoyqzQ9WcfCSAvrknUHyxMVoozs52bnCbnz8jYIHKITBmwBf/8l7HEBvBJayBdgkmXhSfmx3CnaOsSTJv/MoQ1CxTCWe1924qUSdWRROwmJ9tQKBgQCgWUnO0z1O46N1p66gcA0NrRMFsncotg42MipvUpCrMN6lJ80/H7Kj1tGOizJazLXPKN9NKl/lco0xJyAyZS4vFacZXbH2OO0jHyfovPblSY5O10g3d1PC4mbZ/wd4HU4QVO21+U5dIH/HPubhOGQWcpAO+3Fqxx7VFuaZPbsC7g==';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        switch ($type) {
            case '1'://零担支付
                $subject = '零担订单支付'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $couponid = $data['couponid'];
                $notifyurl = "https://app.56cold.com/user/pay/ldnotifyurl";
                break;
            case '2'://城配送支付
                $subject = '城配送订单支付'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $couponid = $data['couponid'];
                $notifyurl = "https://app.56cold.com/user/pay/spnotifyurl";
                break;
            case '3':  //整车配送支付2017-8-4
                $subject = '整车订单支付'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $couponid = $data['couponid'];
                $notifyurl = "https://app.56cold.com/user/pay/zcnotifyurl";
 		         break;
            case '4':  //定制线路支付2018-5-24
                $subject = '定制线路订单支付'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $couponid = $data['couponid'];
                $notifyurl = "https://app.56cold.com/user/pay/dznotifyurl";
                 break;
            case '5':
                $subject = '特价订单支付'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $couponid = $data['couponid'];
                $notifyurl = "https://app.56cold.com/user/pay/tcnotifyurl";
                break;
            default:
                # code...
                break;
        }
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $bizcontent = json_encode([
            'body'=>'支付宝支付',
            'subject'=>$subject,
            'out_trade_no'=>$out_trade_no,//此订单号为商户唯一订单号
            'total_amount'=>$price,//保留两位小数
            'product_code'=>'QUICK_MSECURITY_PAY',
            'passback_params'=>$couponid
        ]);
        $request->setNotifyUrl($notifyurl);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        return $response;
    }
    //支付宝支付：发布信息费
    public function aliypay($type,$data){
        require_once EXTEND_PATH.'Alipay/aop/AopClient.php';
        require_once EXTEND_PATH.'Alipay/aop/request/AlipayTradeAppPayRequest.php';
        $aop = new \AopClient();
        $request = new \AlipayTradeAppPayRequest();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = "2017052307318743";
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAuWqafyecwj1VxcHQjFHrPIqhKrfMPjQRVRTs7/PvGlCXOxV34KaAop4XWEBKgvWhdQX2JkMDLSwPkH790TBJVS84/zQ6sjanpHjgT82/AimuS+/Vk8pB/pAfnOnRN3dhe6y2i9kzJPU62Uj9qn5jJXbWJhyM16Zxdk7GBOChis3C3KvB2WN8qAQawqfUvgHRm/yUgNfVUutKRMdDdQxQypwxkEP50+U9qKeSQecZRyo6xmJ5CWbULQ7FpV5q6lmM7SbyBuyDVk7z4itLIgE8qpt6B3cp9Qm3U3f6DoVJA2LAjinP4v6kNVb/f5qu8VpmR0DD+dRJ1+ujDz1EC/f/lwIDAQABAoIBAHrS0DcM8X2GDcxrQA/DsDUxi+N1T1mhOh4HN5EYILpoylU8OmXZRfrzCHnQVMt9lQ+k/FKKL4970W+hf9dTyjAgkPwVCBDHvbNo0wZqP25aV/g7jlpRL/hGVnqmNI4uiafYWDA5l/SScgI/pLGM+XZ2yxMB9JZhzmVVdz0B5GDCHcjQUkY3//8Tpgw6ylngrq67KjWDbZPAZQHcpj/hdYPOu7Z1kXp30jtdEZi6S+7ZJe/AWMSuEtwWsM53ZOyxqPjSwbW8XfWHHbG3yKF6sngCmwRpwX5rp1EjSsVhA5rbpCM0jbYCKp977XwkGtG6xAOydZdz0WHyirDUTA3PMTECgYEA4lzvyfcg0SyaOWVszwxcWntVm6sQG7deaSlW92Urhy7qaDnv4Ad8TEe0M0QGVllnZUDJA3x8NzoD5DlFROUGZpI/uJk5a0dQlvMbyzS2rx2v4TP19Xm5D7iQk0RK5Zry0K/Fj1kZusIVm3qwsl1DlunAfGipZ1TV0C7QNUJcW0kCgYEA0bE/3ljnSPsKjpc+projOuaLqf7+0x3ITaYle60MbwZrjUnX3cSwbqN3Iu12Npa3mI+RwTyDifFgWB/8hFoqTecFGDnxRa1e7DLlJX9FkIMtoroVsDJUMD+HUx01t9V8fEqVPNyRmnbFyXfdHrRb7zYefwuPZcoE18reADc9o98CgYB1zDl5F+L7F8P2ZIK4SM1yxMYrKV1LnyRBg6LfQcXiJpcTwDrFkf+sTpBHMXo+y23UMl+pMcoOj2FhDjCvBqRLEoaYkRxhaI5Wz5LCL991x/Q0NO8lXL/in4CVMq/rRrRfx2j/DTYni0LlU3bKi2BWE7T4yRqHTI2sNgBiBvO7CQKBgQCDsHNR6jdmR/J7VlTMVH2nkf4IRtI2N7ABw+QqZaU3XKrS0ps09T9wXEyHrOXepoyqzQ9WcfCSAvrknUHyxMVoozs52bnCbnz8jYIHKITBmwBf/8l7HEBvBJayBdgkmXhSfmx3CnaOsSTJv/MoQ1CxTCWe1924qUSdWRROwmJ9tQKBgQCgWUnO0z1O46N1p66gcA0NrRMFsncotg42MipvUpCrMN6lJ80/H7Kj1tGOizJazLXPKN9NKl/lco0xJyAyZS4vFacZXbH2OO0jHyfovPblSY5O10g3d1PC4mbZ/wd4HU4QVO21+U5dIH/HPubhOGQWcpAO+3Fqxx7VFuaZPbsC7g==';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        switch ($type) {
            case '1':  //整车配送支付
                $subject = '整车订单支付'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $notifyurl = "https://app.56cold.com/user/pay/zcontify";
                break;
            case '2'://零担支付
                $subject = '零担订单支付'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $notifyurl = "https://app.56cold.com/user/pay/ldnotifyurl";
                break;
            case '3'://城配送支付
                $subject = '城配送订单支付'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $notifyurl = "https://app.56cold.com/user/pay/spnotifyurl";
                break;
            default:
                # code...
                break;
        }
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $bizcontent = json_encode([
            'body'=>'支付宝支付',
            'subject'=>$subject,
            'out_trade_no'=>$out_trade_no,//此订单号为商户唯一订单号
            'total_amount'=>$price,//保留两位小数
            'product_code'=>'QUICK_MSECURITY_PAY',
        ]);
        $request->setNotifyUrl($notifyurl);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        return $response;
    }
    
    //微信支付
    public function weixin_pay($type,$data){
        require_once EXTEND_PATH.'wxAppPay/weixin.php';
        switch ($type) {
            case '1':
                $body = '零担订单支付：'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $couponid = $data['couponid'];
                $noturl = 'https://app.56cold.com/user/pay/wxpaymenturl';
                break;
            case '2':
                $body = '城配送信息费支付：'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
//                $couponid = $data['couponid'];
                $noturl = 'https://app.56cold.com/user/pay/spwxpaymenturl';
                break;
            case '3':
                $body = '整车信息费支付：'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $couponid = $data['couponid'];
                $noturl = 'https://app.56cold.com/user/pay/cityweixinnotifyurl';
                break;
            case '4':
                $body = '定制线路订单支付：'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
//                $couponid = $data['couponid'];
                $noturl = 'https://app.56cold.com/user/pay/dzwxpaymenturl';
		        break;
            case '5':
                $body = '特价微信支付：'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $couponid = $data['couponid'];
                $noturl = 'https://app.56cold.com/user/pay/wxnotifyurl';
                break;
	    }
        $appid = 'wxe2d6b74ba8fa43e7';
        $mch_id = '1481595522';
        $notify_url = $noturl;
        $key = 'FdzK0xScm6GRS0zUW4LRYOak5rZA9k3o';
        $wechatAppPay = new \wxAppPay($appid,$mch_id,$notify_url,$key);
        $params['body'] = $body;                       //商品描述
        $params['out_trade_no'] = $out_trade_no;    //自定义的订单号
        $params['total_fee'] = $price*100;                       //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP 
        $params['attach'] = $couponid;                      //附加参数（用户ID）
        $result = $wechatAppPay->unifiedOrder($params);
        // print_r($result); // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
        //2.创建APP端预支付参数
        /** @var TYPE_NAME $result */
        $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
        return $data;
    }
    //整车微信支付 2019.5.24
    public function wx_pay($type,$data){
        require_once EXTEND_PATH.'wechatAppPay/weixin.php';
        $body = '整车订单支付：'.$data['ordernumber'];
        $out_trade_no = $data['ordernumber'];
        $price = $data['price'];
        $noturl = 'https://app.56cold.com/user/pay/zcwxnotify';
        $appid = 'wxe2d6b74ba8fa43e7';
        $mch_id = '1481595522';
        $notify_url = $noturl;
        $key = 'FdzK0xScm6GRS0zUW4LRYOak5rZA9k3o';
        $wechatAppPay = new \wechatAppPay($appid,$mch_id,$notify_url,$key);
        $params['body'] = $body;                       //商品描述
        $params['out_trade_no'] = $out_trade_no;    //自定义的订单号
        $params['total_fee'] = $price*100;                       //订单金额 只能为整数 单位为分
        $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP
        $result = $wechatAppPay->unifiedOrder($params);
        // print_r($result); // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
        //2.创建APP端预支付参数
        /** @var TYPE_NAME $result */
        $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
        return $data;
    }
   
    //分享获取优惠券
    public function shareIt(){
        $token   = input("token");  //令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        $coupon_id = Db::table('ct_coupon')->where(array('coutype_id'=>'3','state'=>'1'))->find();
        $coupon_type_where['coup_id'] = $coupon_id['cou_id'];
        $coupon_type_where['userid'] = $user_id;
        $coupon_type = Db::table('ct_coupon_user')->where($coupon_type_where)->select();
        if ($coupon_type) {
            return json(['code'=>'1002','message'=>'优惠券已领取']);
        }else{
            $coupon_data['coup_id'] = $coupon_id['cou_id'];
            $coupon_data['userid'] = $user_id;
            $coupon_data['failure'] = '1';
            $coupon_data['time_start'] = time();
            $coupon_data['time_end'] = time()+86400*$coupon_id['time_day'];
            Db::table('ct_coupon_user')->insert($coupon_data);
            return json(['code'=>'1001','message'=>'分享成功']);
        }
    }
    //下载次数统计
    public function downloadStatistics(){
        $data['logo']   = input("logo"); //设备标识
        $data['model'] = input("model"); //设备型号
        $data['type']        = input("type"); //1用户端下载2司机端下载
        $result = Db::table('ct_device')->where($data)->find();
        if ($result) {
            Db::table('ct_device')->where('id',$result['id'])->update(array('number'=>$result['number']+1,'dow_time'=>time()));
            return json(['code'=>'1001','message'=>'统计成功']);
        }else{
            $data['dow_time'] = time();
            Db::table('ct_device')->insert($data);
            return json(['code'=>'1001','message'=>'统计成功']);
        }
    }

    /**
     * 对象 转 数组
     * @param  [type]   $obj    [对象]
     * @return [array]          [description]
     */
    public function object_to_array($obj) {
        $obj = (array)$obj;
        foreach ($obj as $k => $v) {
            if (gettype($v) == 'resource') {
                return;
            }
            if (gettype($v) == 'object' || gettype($v) == 'array') {
                $obj[$k] = (array)$this->object_to_array($v);
            }
        }
        return $obj;
    }

    /**
     * [arrtostr description]
     * @param  [type] $arr [description]
     * @param  string $str [description]
     * @return [type]      [description]
     */
    public function arrtostr($arr,$str=''){
        $sum = 0;
        $count = count($arr);
        for($i = 0; $i < $count; $i++){
        $sum .= $arr[$i][$str].',';
        }
        $str_id = rtrim(substr($sum,1), ",");
        return  $str_id;
    }

    /**
     * 筛选数组中指定的值
     * @param string $needle 筛选键值名称
     * @param array $haystack 数组
     * @return 数组
     */
    public function array_search_key($needle, $haystack){ 
        global $nodes_found; 
        foreach ($haystack as $key1=>$value1) { 
         if ($key1=== $needle){ 
          $nodes_found[] = $value1;     
           } 
            if (is_array($value1)){    
              $this->array_search_key($needle, $value1); 
            } 
        } 
        return $nodes_found; 
    }

    /**
     * 二维数组排序
     * @param $arrays 数组
     * @param $sort_key 排序的键值
     * @param $sort_order SORT_ASC:升序 SORT_DESC：降序 排序的规则
     * @param $sort_type SORT_REGULAR :常规 SORT_NUMERIC:数字  SORT_STRING :字母 排序的类型
     */
    public function my_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){  
        if(is_array($arrays)){  
            foreach ($arrays as $array){  
                if(is_array($array)){  
                    $key_arrays[] = $array[$sort_key];  
                }else{  
                    return false;  
                }  
            }  
        }else{  
            return false;  
        } 
        array_multisort($key_arrays,$sort_order,$sort_type,$arrays);  
        return $arrays;  
    }
  

    /**
     * 客户下单向承运商推送消息
     * @param  [Int] $typestate [项目类型 1、零担 2、整车 3、城配送 4、发布货源]
     * @param  [Int] $startid   [起点城市id]
     * @param  [Int] $endid     [终点城市di]
     * @param  [Int] $companyid [公司id]
     * @return [type]           [description]
     */
    public function send_note($typestate='1',$startid,$endid='',$companyid=''){
        // 获取起点城市
        $start_city = addresidToName($startid);
        // 判断订单类型
        switch ($typestate) {
            case '1': // 零担
                // 获取终点城市
                 $end_city = addresidToName($endid);
                // 获取管理员信息
                 $phone = DB::table('ct_driver')->field('mobile')->where(array('companyid'=>$companyid,'type'=>'3'))->find();
                // 设置短信内容
                 $center_list = '您有新的零担订单从'. $start_city.'发往'.$end_city.'，请登录APP接单吧！';
                // 发送短信
                 send_sms_class($phone['mobile'],$center_list);
                // 推送消息
                $todata = array('title' => "赤途承运端",'content' => '零担有新订单发布了' , 'payload' => "订单信息");
                break;
            case '2': // 整车
                // 获取终点城市
                $end_city = addresidToName($endid);
                // 获取所有公司地址在订单始发城市的管理员的号码
//                 $select = DB::table('ct_company')
//                         ->alias('a')
//                         ->join('ct_driver b','b.companyid=a.cid')
//                         ->field('b.mobile')
//                         ->where(array('cityid'=>$startid,'a.status'=>'1','b.type'=>'3'))
//                         ->select();
                $select = Db::table('ct_driver')->field('mobile')->select();
                // 数组转字符串
                 $phone = $this->arr_to_str($select);
                // 设置短信、推送内容
                $center_list = '有从'. $start_city.'发往'.$end_city.'的整车订单';
                // 发送短信
                 $list = send_sms_class($phone,$center_list);
                // 推送消息
                $todata = array('title' => "赤途承运端",'content' => $center_list , 'payload' => "订单信息");
                break;
            case '3': // 城配
                // 获取所有公司地址在订单始发城市的管理员的号码
//                 $select = DB::table('ct_company')
//                     ->alias('a')
//                     ->join('ct_driver b','b.companyid=a.cid')
//                     ->field('b.mobile')
//                     ->where(array('cityid'=>$startid,'a.status'=>'1','b.type'=>'3'))
//                     ->select();
                 $select = Db::table('ct_driver')->field('mobile')->select();
                // 数组转字符串
                 $phone = $this->arr_to_str($select);
                // 设置短信、推送内容
                $center_list = $start_city.'有新的城配订单';
                // 发送短信
                 send_sms_class($phone,$center_list);
                // 推送消息
                $todata = array('title' => "赤途承运端",'content' => $center_list , 'payload' => "订单信息");
                break;
            case '4': // 发布货源
                // 获取终点城市
                $end_city = addresidToName($endid);
                // 设置推送内容
                $center_list = '有'. $start_city.'发往'.$end_city.'的货源';
                // 推送消息
                $todata = array('title' => "赤途承运端",'content' => $center_list , 'payload' => "订单信息");
                break;
            default:
                # code...
                break;
        }
        // 设置发送的固定联系电话
//         $leader = "15021899770,‭13248361505";
        // 发送短信
//         send_sms_class($leader,$center_list);
        // 去除空格
        $tocity[] = trim($start_city);
        // 推送消息
        $this->pushMessage($todata,$tocity);
    }

    /**
     * 定制线路发送短信息(用户下单支付时候调用）
     * @param int orderid
     */
    public function send_mess_shiftorder($orderid){
        $sorder_mess = Db::table('ct_shift_order')
                        ->alias('o')
                        ->join('__FIXATION_LINE__ f','f.id=o.shiftid')
                        ->join('__COMPANY__ c','c.cid=f.companyid')
                        ->join('__ALREADY_CITY__ a','a.city_id=f.lienid')
                        ->field('o.s_oid,o.picktime,o.totalprice,f.carrierid,f.trans_mess,f.paddress,f.ptime,a.start_id,a.end_id,c.cid,c.money')
                        ->where('s_oid',$orderid)
                        ->find();
        $list_phone = '';
        $phone_driver ='';
        $phone_leader ='';
        if (!empty($sorder_mess['trans_mess'])) {
            $arr_mess = json_decode($sorder_mess['trans_mess'],true);
            if (!empty($arr_mess)) {
                foreach ($arr_mess as $key => $value) {
                    $driver = Db::table('ct_driver')->where('drivid',$value['driverid'])->find();
                    $list_phone .= $driver['mobile'].',';
                }
            }
            
            $phone_driver = rtrim ($list_phone,',');
        }
        if(!empty($sorder_mess['carrierid'])){
            $driver_leader = Db::table('ct_driver')->field('mobile')->where(array('companyid'=>$sorder_mess['carrierid'],'type'=>3))->find();
            $phone_leader = $driver_leader['mobile'];
        }
        $str ='';
        if ($phone_driver!='') {
            $str =',';
        }
        $phone_all = $phone_driver.$str.$phone_leader;
        //echo $phone_all;exit();
        $saddress = '';
        if($sorder_mess['paddress'] !='') {
            $address = json_decode($sorder_mess['paddress'],true);
           foreach ($address as $key => $val) {
            $saddress .=$val.'/';
          }
        }
        $city_start = '';
        $city_end = '';
        $startarea = Db::table('ct_district')->where('id',$sorder_mess['start_id'])->find();
        $endarea = Db::table('ct_district')->where('id',$sorder_mess['end_id'])->find();
        if ($startarea['level'] =='3') {
            $startcity = DB::table('ct_district')->where('id',$startarea['parent_id'])->find();
            $city_start = $startcity['name'];
        }
        if ($endarea['level'] =='3') {
            $startcity = DB::table('ct_district')->where('id',$endarea['parent_id'])->find();
            $city_end = $startcity['name'];
        }
        $startcity = $city_start.$startarea['name']; //起点城市
        $endcity = $city_end.$endarea['name'];           //终点城市  
        $ptime = $sorder_mess['ptime'];
        $ttime = strtotime($sorder_mess['picktime'] . "-24 hours");
        $content = "尊敬的用户：您有订单".$sorder_mess['picktime']."从:".$startcity." 发往 ".$endcity ."的货物！提货地址为".$saddress."。请您及时处理";
        if ($ttime < time()) {
            $data['send_mess'] = 2;
        }
        $result = Db::table('ct_shift_order')->where('s_oid',$orderid)->update($data);
        if ($result) {
            send_sms_class($phone_all,$content);
        }
    }
    
    /**
     * 支付后计算积分
     * @param jifen  原有积分
     * @param userid  用户ID
     * @param money  支付金额
     * @param finally_money  余额支付扣除金额
     * @param content  记录内容
     * @param type  消费类型 1 增加  2 扣除3支付宝4微信
     * @param coupid  优惠卷ID
     * @param companyid  公司ID 无普通用户  有项目客户
     * @param paymessid  记录明细操作订单或充值的ID
     * @param paytype  1零担2定制3城配4整车5支付宝充值6微信充值
     */
    public function record($jifen='',$userid,$companyid='',$money='',$finally_money='',$content,$type,$coupid='',$paymessid,$paytype){
        
        if ($finally_money !='') {
            $data_user['money'] = $finally_money;
            //$data_user['integral'] = $jifen + ($finally_money/$this->config);
        }
        if ($companyid=='') { //个人
            if ($money !='') {
                $data_user['integral'] = $jifen + floor($money/$this->config);
            }
            //更新余额
            Db::table('ct_user')->where('uid',$userid)->update($data_user);
        }else{  //项目客户
            Db::table('ct_company')->where('cid',$companyid)->update($data_user);
        }
        
        $balance_data=array(
            'pay_money'=>$money,
            'order_content'=>$content,
            'action_type'=>$type, 
            'userid'=>$userid,
            'orderid'=>$paymessid, 
            'ordertype'=>$paytype,
            'addtime'=>time()
        );
        Db::table('ct_balance')->insert($balance_data);
        if ($coupid !='') {
            Db::table('ct_coupon_user')->where('cuid',$coupid)->update(array('failure'=>'2'));
        }
    }

    /**
     * 定制专线发送信息
     */
    public function special_sms_mess(){
        $where['affirm'] = 2;
        $where['send_mess'] = 1;
        $result =Db::table('ct_shift_order')
                    ->alias('s')
                    ->join('__FIXATION_LINE__ f','f.id=s.shiftid')
                    ->join('__ALREADY_CITY__ a','a.city_id=f.lienid')
                    ->field('s.s_oid,s.picktime,f.carrierid,f.trans_mess,f.paddress,f.ptime,a.start_id,a.end_id')
                    ->where($where)
                    ->select();
        foreach ($result as $k => $v) {
            $ptime = strtotime($v['picktime']. "-24 hours");
            $add_min = strtotime('+30 minute',$ptime);
            $now = time();
            if ($add_min > $now && $now > $ptime) {    
                $list_phone = '';
                if (!empty($v['trans_mess'])) {
                    $arr_mess = json_decode($v['trans_mess'],true);
                    foreach ($arr_mess as $key => $value) {
                        $driver = Db::table('ct_driver')->where('drivid',$value['driverid'])->find();
                        $list_phone .= $driver['mobile'].',';
                    }
                    $phone = rtrim ($list_phone,',');
                }else{
                    $driver = Db::table('ct_driver')->field('mobile')->where(array('companyid'=>$v['carrierid'],'type'=>3))->find();
                    $phone = $driver['mobile'];
                }
                //echo $phone;exit();
                $saddress = '';
                if($v['paddress'] !='') {
                    $address = json_decode($v['paddress'],true);
                   foreach ($address as $key => $val) {
                    $saddress .=$val.'/';
                  }
                }
                $city_start = '';
                $city_end = '';
                $startarea = Db::table('ct_district')->where('id',$v['start_id'])->find();
                $endarea = Db::table('ct_district')->where('id',$v['end_id'])->find();
                if ($startarea['level'] =='3') {
                    $startcity = DB::table('ct_district')->where('id',$startarea['parent_id'])->find();
                    $city_start = $startcity['name'];
                }
                if ($endarea['level'] =='3') {
                    $startcity = DB::table('ct_district')->where('id',$endarea['parent_id'])->find();
                    $city_end = $startcity['name'];
                }
                $startcity = $city_start.$startarea['name']; //起点城市
                $endcity = $city_end.$endarea['name'];           //终点城市 
                $content = "尊敬的用户：您有订单".date('Y-m-d H:i')."从:".$startcity." 发往 ".$endcity ."的货物！提货地址为".$saddress."。请您及时处理";

                if ($add_min > $now &&  $now > $ptime) {
                    send_sms_class($phone,$content);
                    $data['send_mess']=2;
                    Db::table('ct_shift_order')->where('s_oid',$v['s_oid'])->update($data);
                }
            }
        }
    }

    /**
     * 获取途经地址公里数
     * @param string  $start 起始地址的经纬度
     * @param string   $end   终点地址的经纬度  (多个以|隔开)
     * @return array 返回数组
     */
    public function address_kilo($start,$end){
        $ak ="SdRptW2rs3xsjHhVhQOy17QzP6Gexbp6";
        $url = "http://api.map.baidu.com/routematrix/v2/driving?output=json&origins=".$start."&destinations=".$end."&ak=".$ak;
        $renderOption =    file_get_contents($url);
        $result = json_decode($renderOption,true);
        if ($result['status'] == '0') {
            $res = $result['result'];
        }else{
            $res=array();
        }
        $arr = array();
        if (!empty($res)) {
            for ($i=0; $i < count($res); $i++) { 
                $arr[$i] = $res[$i]['distance']['value'];
            }
        }
        return $arr;
    }

    /**
     * 反复筛选比较多个地址中最短距离
     * @param string $endcity_str 城市
     * @param int $min_key 数组指针
     * @param array $pick_add 多地址数组
     * @return 返回多维数组
     */
    public function get_arr_list($endcity_str,$min_key,$pick_add=array()){
        //$endcity_str="上海市";
        $i=0;
        $count_finly = 0;
        $arr = array();
        foreach ($pick_add as $key => $value) {
            $start_action = bd_local($type='2',$endcity_str,$value);//经纬度
            if ($min_key == $key) {
                unset($pick_add[$key]);
                foreach ($pick_add as $val) {
                    $pick_adds[$i] = $val;
                    $i++;
                }
                
                $str['start'] = $start_action['lat'].','.$start_action['lng'];
            }else{
                $str['end'][] =$start_action['lat'].','.$start_action['lng'];
            }
        }
        $str['end']= implode('|',$str['end']);
        $start_1 = $this->address_kilo($str['start'],$str['end']);
        $t=min($start_1);
        $brr=array_flip($start_1);
        $min_keys = $brr[$t];
        $list = array();
        
        if (count($pick_adds)>1) {
            $list = $this->get_arr_list($endcity_str,$min_keys,$pick_adds);
        }
        $arr['address'] = $pick_adds;
        $count_finly =$t;
        return array('arr'=>$arr,'pick_adds'=>$pick_adds,'list'=>$list,'count_finly'=>$count_finly);
    } 


    /**
     * 获取用户默认的常用地址
     * @auther: 李渊
     * @date: 2018.10.22
     * @param  [type] $userid  [用户id]
     * @return [type]          []
     */
    public function get_default_address($userid) 
    {
        // 查询用户的默认地址
        $default = Db::table('ct_addressuser')->where(array('user_id' => $userid, 'default' => 2))->find();
        // 常用的地址
        $address['address'] = detailadd($default['pro_id'], $default['city_id'], $default['area_id']);
        // 常用地址对应的联系人
        $address['addressName'] = $default['name'];
        // 常用地址对应的联系电话
        $address['addressPhone'] = $default['phone'];
        // 返回数据
        return $address;
    }

    /**
     * 获取用户默认的常用联系人
     * @auther: 李渊
     * @date: 2018.10.22
     * @param  [type] $userid  [用户id]
     * @return [type]          []
     */
    public function get_default_contact($userid) 
    {
        // 查询用户的默认地址
        $default = Db::table('ct_contacts')->where(array('userid' => $userid, 'yesno' => 2))->find();
        // 常用联系人姓名
        $contact['contact_name'] = $default['username'];
        // 常用联系人电话
        $contact['contact_phone'] = $default['telephone'];
        // 返回数据
        return $contact;
    }

    /**
     * 获取支付状态和支付方式
     * @auther: 李渊
     * @date: 2018.8.30
     * @param  [type] $paystate [支付状态]
     * @param  [type] $paytype  [支付方式]
     * @return [type]           [支付信息]
     */
    public function balance_mess($paystate,$paytype)
    {
        // 判断支付状态
        $pay_mess = $paystate == '2' ? '支付成功' : '支付失败';
        // 支付方式
        switch ($paytype) {
            case '1':
                $pay_type = '信用支付';
                break;
            case '2':
                $pay_type = '余额';
                break;
            case '3':
                $pay_type = '支付宝';
                break;
            case '4':
                $pay_type = '微信';
                break;
            default:
                $pay_type = '信用支付';
                break;
        }
        // 返回支付状态
        $arr['pay_mess'] = $pay_mess;
        // 返回支付方式
        $arr['paytype'] = $pay_type;
        // 返回支付信息
        return $arr;
    }

    /**
     * 获取提现状态说明
     * @auther: 李渊
     * @date: 2018.8.30
     * @param  [type] $state [提现状态]
     * @return [type]        [提现状态说明]
     */
    public function withdraw_status($state)
    {
        switch ($state) {
            case '1':
                $state = "审核中";
                break;
            case '2':
                $state = "审核成功";
                break;
            case '3':
                $state = "审核失败";
                break;
            case '4':
                $state = "打款完成";
                break;  
        }

        return $state;
    }


    /**
     * 车型id转换为车型
     * @auther: 李渊
     * @date: 2018.9.1
     * @param  [type] $id [车型id]
     * @return [type]     [车型名称]
     */
    public function caridToName($id)
    {
        switch ($id) {
            case '1':
                $car['weight'] = 1.5;
                $car['volume'] = 12;
                $car['carName'] = '4.2米';
                break;
            case '2':
                $car['weight']  = 3;
                $car['volume'] = 14;
                $car['carName'] = '5.2米';
                break;
            case '3':
                $car['weight']  = 8;
                $car['volume'] = 35;
                $car['carName'] = '7.6米';
                break;
            case '4':
                $car['weight']  = 14;
                $car['volume'] = 46;
                $car['carName'] = '9.6米';
                break;
            case '5':
                $car['weight']  = 16;
                $car['volume'] = 46;
                $car['carName'] = '9.6米';
                break;
            case '6':
                $car['weight']  = 20;
                $car['volume'] = 60;
                $car['carName'] = '12.5米';
                break;
            case '7':
                $car['weight']  = 25;
                $car['volume'] = 68;
                $car['carName'] = '15米';
                break;
            case '9':
                $car['weight']  = 0.5;
                $car['volume'] = 4;
                $car['carName'] = '依维柯';
                break;
            default:
                $car['carName'] = '';
                $car['weight']  = '';
                $car['volume'] = '';
                break;
        }

        return $car;
    }

    /**
     * 用户提交表表单时候返回用户余额和公司信用额度
     * @param time 2018-05-25
     */
    public function replay_user_money($userid){
        $balance =Db::table('ct_user')->alias('a')->join('ct_company c','c.cid=a.lineclient','LEFT')->field('a.money,c.money com_money')->where('uid',$userid)->find();
        //账户余额
        $back_data['money'] = $balance['money'];
        //公司信用余额
        $back_data['com_money'] = $balance['com_money'];
        return $back_data;
    } 

    /**
     * 订单完成插入对账需要订单信息
     * @param ordernumber 订单编号
     * @param orderid 订单ID
     * @param addtime 下单时间
     * @param otype 订单类型1零担2定制3城配4整车
     * @param user_companyid 用户公司ID(承运商对账有可能存在空)
     * @param userid 用户ID
     * @param driver_companyid 承运公司ID
     */
    public function insert_invomess($array = array()){
        if (!empty($array)) {
            $user_companyid = $array['user_companyid'];
            $data['ordernumber'] = $array['ordernumber'];
            $data['orderid'] = $array['orderid'];
            $data['addtime'] = $array['addtime'];
            $data['otype'] = $array['otype'];
            if ($user_companyid !='') {
                $data['user_companyid'] = $user_companyid;
            }
            $data['userid'] = $array['userid'];
            $data['driver_companyid'] = $array['driver_companyid'];
            DB::table('ct_account_order')->insert($data);
        }
        
    }

    /**
     * 定时任务之用户未点击完成则3天后自动完成
     * @Auther: 李渊
     * @Date: 2018.6.22
     * @return [type] [description]
     */
    public function finish_order(){
        // 获取现在的日期
        $nowtime = time();
        // 零担订单
        $order_bulk = Db::table("ct_order")
                ->alias('o')
                ->join("ct_pickorder ti",'ti.orderid = o.oid') // 提货单
                ->join("ct_lineorder gan",'gan.orderid = o.oid') // 干线单
                ->join("ct_delorder pi",'pi.orderid = o.oid') // 配送单
                ->join("ct_shift s",'s.sid = o.shiftid')
                ->join('ct_user u','u.uid = o.userid')
                ->field('o.receipt,o.oid,o.all_price,o.arrivetime,o.ordernumber,o.userid,o.pay_type,u.lineclient,o.addtime,o.orderstate,o.pickcost,o.linepice,o.delivecost,ti.tcarr_upprice,gan.lcarr_price,pi.pcarr_upprice,s.companyid')
                ->where(array('paystate'=>'2','orderstate'=>['NEQ','7'],'arrivetime'=>['NEQ','']))
                ->select();
        if (!empty($order_bulk)) {
            foreach ($order_bulk as $key => $value) {
                // 提货费
                $tiprice = $value['tcarr_upprice'] == '' ? $value['pickcost'] : $value['tcarr_upprice'];
                // 干线费
                $lineprice = $value['lcarr_price']== '' ? $value['linepice'] : $value['lcarr_price'];
                // 配送费
                $peiprice = $value['pcarr_upprice']== '' ? $value['delivecost'] : $value['pcarr_upprice'];
                // 总费用
                $totalprice = $tiprice + $lineprice + $peiprice;
                 //查找司机信息
                $driver_mess = Db::table('ct_company')
                                ->alias('c')
                                ->join('ct_driver d','d.companyid=c.cid')
                                ->field('c.cid,c.money,d.drivid')
                                ->where(array('cid'=>$value['companyid'],'d.type'=>'3'))
                                ->find();
                
                $start_time = date('Y-m-d H:i:s',$value['arrivetime']);
                
                $contact = Db::table('ct_order_contact')->where(array('orderid'=>$value['oid'],'otype'=>1))->order('addtime','asc')->find();
                if (!empty($contact)) {
                    $start_time = date('Y-m-d H:i:s',$contact['addtime']);
                }
                $date = strtotime($start_time . ' +3 day');
                
                if ($nowtime > $date) {
                    // 更新零担单
                    $orderdata['orderstate'] = '7';  //接单状态1未接2已接3已完成
                    $re = Db::table("ct_order")->where('oid',$value['oid'])->update($orderdata);
                    //更新公司余额
                    DB::table('ct_company')->where('cid',$driver_mess['cid'])->update(array('money'=>$driver_mess['money']+round($totalprice)));
                    //记录收入记录
                    Db::table('ct_balance_driver')->insert(array('pay_money'=>round($totalprice),'order_content'=>'零担订单收入费用','orderid'=>$value['oid'],'ordertype'=>'1','action_type'=>'1','driver_id'=>$driver_mess['drivid'],'addtime'=>time()));
                    // 更新提货单
                    $pickdata['status'] = '3';  //接单状态1未接2已接3已完成
                    Db::table("ct_pickorder")->where('orderid',$value['oid'])->update($pickdata);
                    // 更新干线单
                    $linedata['status'] = '3';  //接单状态1未接2已接3已完成
                    Db::table("ct_lineorder")->where('orderid',$value['oid'])->update($linedata);
                    // 更新配送单
                    $deldata['status'] = '3';  //接单状态1未接2已接3已完成
                    Db::table("ct_delorder")->where('orderid',$value['oid'])->update($deldata);
                    //插入对账需要信息
                    $array = array(
                            'ordernumber' => $value['ordernumber'], //订单编号
                            'orderid' => $value['oid'],  //订单ID
                            'addtime' => $value['addtime'], //下单时间
                            'userid' => $value['userid'],    //下单人
                            'otype' => 1,   //订单类型1零担2定制3城配4整车
                            'user_companyid' => $value['pay_type']=='1' ? $value['lineclient'] : '',  //当为项目客户信用支付时插入公司ID
                            'driver_companyid' => $value['companyid']    //承运商公司ID
                        );
                    $this->insert_invomess($array);
                }
            } 
        } 

        // 查找定制订单
        $orderid_mess = Db::table('ct_shift_order')
                            ->alias('o')
                            ->join('ct_fixation_line f','f.id = o.shiftid')
                            ->field('o.*,f.companyid')
                            ->where(array('o.orderstate'=>['NEQ','3'],'o.affirm'=>'2','o.arrivetime'=>['NEQ','']))
                            ->select();
        if (!empty($orderid_mess)) {
            foreach ($orderid_mess as $key => $value) {
                $driver_mess = Db::table('ct_driver')
                        ->alias('a')
                        ->join('ct_company c','c.cid = a.companyid')
                        ->where('drivid',$value['driverid'])
                        ->find();
                $start_time = date('Y-m-d H:i:s',$value['arrivetime']);
                $contact = Db::table('ct_order_contact')->where(array('orderid'=>$value['s_oid'],'otype'=>1))->order('addtime','asc')->find();
                if (!empty($contact)) {
                    $start_time = date('Y-m-d H:i:s',$contact['addtime']);
                }
                $date = strtotime($start_time . ' +3 day');

                if ($nowtime > $date) {
                    $data['orderstate']=3;
                    $result = Db::table('ct_shift_order')->where('s_oid',$value['s_oid'])->update($data);
                    //完成订单将订单加到公司账户下
                    Db::table('ct_company')->where('cid',$driver_mess['companyid'])->update(array('money'=>$driver_mess['money']+$value['price']));
                    Db::table('ct_balance_driver')->insert(array('pay_money'=>$value['price'],'order_content'=>'定制线路订单收入费用','orderid'=>$value['s_oid'],'ordertype'=>'2','action_type'=>'1','driver_id'=>$value['driverid'],'addtime'=>time()));
                    //插入对账需要信息
                    $array = array(
                            'ordernumber' => $value['ordernumber'], //订单编号
                            'orderid' => $value['s_oid'],  //订单ID
                            'addtime' => $value['addtime'], //下单时间
                            'userid' => $value['userid'],    //下单人
                            'otype' => 2,   //订单类型1零担2定制3城配4整车
                            'user_companyid' => $value['pay_type']=='1' ? $value['companyid'] : '',  //当为项目客户信用支付时插入公司ID
                            'driver_companyid' => $driver_mess['companyid'] //承运商公司ID
                        );
                    $this->insert_invomess($array);
                }
            }
        }

        // 查询城配用户未点击完成订单
        $result_city = DB::table('ct_city_order')
                    ->alias('o')
                    ->join('ct_rout_order r','r.rid = o.rout_id')
                    ->join('ct_driver d','d.drivid=r.driverid')
                    ->join('ct_user u','u.uid = o.userid')
                    ->join('ct_company c','c.cid=d.companyid','LEFT')
                    ->field('o.paymoney,o.carr_upprice,o.state,o.pytype,r.driverid,o.ordertype,o.id,o.rout_id,o.orderid,o.addtime,o.userid,
                            o.pay_type,r.arrivetime,r.rid,r.driverid,d.companyid,d.type,d.money,c.money com_money,u.lineclient')
                    ->where(array('state'=>['NEQ','3'],'paystate'=>'2','arrivetime'=>['NEQ','']))
                    ->select();
        // 如果结果不为空
        if (!empty($result_city)) {
            foreach ($result_city as $key => $value) {
                $start_time = date('Y-m-d H:i:s',$value['arrivetime']);
                $contact = Db::table('ct_order_contact')->where(array('orderid'=>$value['id'],'otype'=>1))->order('addtime','asc')->find();
                if (!empty($contact)) {
                    $start_time = date('Y-m-d H:i:s',$contact['addtime']);
                }
                $date = strtotime($start_time . ' +3 day');
                if ($nowtime > $date) {
                    $upcityorder['state'] = 3;
                    $re = Db::table('ct_city_order')->where('id',$value['id'])->update($upcityorder);
                    $uproutdata['finshtime'] = $date;
                    $rout = Db::table('ct_rout_order')->where('rid',$value['rout_id'])->update($uproutdata);

                    
                    $city_price = $value['carr_upprice']=='' ? $value['paymoney'] : $value['carr_upprice'];
                    $paymoney = round($city_price);

                    if ($value['type'] =='1') {
                        Db::table('ct_driver')->where('drivid',$value['driverid'])->update(array('money'=>$paymoney+$value['money']));
                    }else{
                        Db::table('ct_company')->where('cid',$value['companyid'])->update(array('money'=>$paymoney+$value['com_money']));
                        //插入对账需要信息
                        $array = array(
                                'ordernumber' => $value['orderid'], //订单编号
                                'orderid' => $value['id'],  //订单ID
                                'addtime' => $value['addtime'], //下单时间
                                'userid' => $value['userid'],  //下单人
                                'otype' => 3,   //订单类型1零担2定制3城配4整车
                                'user_companyid' => $value['pay_type']=='1' ? $value['lineclient'] : '',  //当为项目客户信用支付时插入公司ID
                                'driver_companyid' => $value['companyid']  //承运商公司ID
                            );
                        $this->insert_invomess($array);
                    }
                    Db::table('ct_balance_driver')->insert(array('pay_money'=>$paymoney,'order_content'=>'市内配送订单收入费用','orderid'=>$value['id'],'ordertype'=>'3','action_type'=>'1','driver_id'=>$value['driverid'],'addtime'=>time()));
                
                }
            }
        }
        
        // 整车未完成订单
        $result_car = Db::table("ct_userorder")
                        ->alias('a')
                        ->join('ct_driver d','d.drivid = a.carriersid')
                        ->join('ct_user u','u.uid = a.userid')
                        ->join('ct_company c','c.cid=d.companyid','LEFT')
                        ->field('a.price,a.carr_upprice,a.uoid,a.arrivetime,a.carriersid,d.type driver_type,d.money driver_money,d.companyid,
                            a.userid,a.addtime,a.ordernumber,o.pay_type,u.lineclient,c.money com_money')
                        ->where(array('orderstate'=>['NEQ','3'],'paystate'=>'2','arrivetime'=>['NEQ','']))
                        ->select();
        if (!empty($result_car)) {
            foreach ($result_car as $key => $value) {
                $start_time = date('Y-m-d H:i:s',$value['arrivetime']);
                $contact = Db::table('ct_order_contact')->where(array('orderid'=>$value['uoid'],'otype'=>1))->order('addtime','asc')->find();
                if (!empty($contact)) {
                    $start_time = date('Y-m-d H:i:s',$contact['addtime']);
                }
                $date = strtotime($start_time . ' +3 day');
                if ($nowtime > $date) {
                    $data['orderstate'] = 3;
                    $re = Db::table('ct_userorder')->where('uoid',$value['uoid'])->update($data);
                    $price = $value['carr_upprice']=='' ? $value['price'] : $value['carr_upprice'];
                    if ($value['driver_type']=='1') {   //司机时候接单金额进入司机余额
                    //谁接单订单金额就在谁的账户里
                        Db::table('ct_driver')->where('drivid',$value['carriersid'])->update(array('money'=>$price+$value['driver_money']));
                    }else{      //调度或管理员时候接单金额进入公司余额

                        DB::table('ct_company')->where('cid',$value['companyid'])->update(array('money'=>$price+$value['com_money']));
                        //插入对账需要信息
                        $array = array(
                                'ordernumber' => $value['ordernumber'], //订单编号
                                'orderid' => $value['uoid'],  //订单ID
                                'addtime' => $value['addtime'], //下单时间
                                'userid' => $value['userid'],  //下单人
                                'otype' => 4,   //订单类型1零担2定制3城配4整车
                                'user_companyid' => $value['pay_type']=='1' ? $value['lineclient'] : '',  //当为项目客户信用支付时插入公司ID
                                'driver_companyid' => $value['companyid']  //承运商公司ID
                            );
                        $this->insert_invomess($array);
                    }
                    //写入司机收入记录
                    Db::table('ct_balance_driver')->insert(array('pay_money'=>$price,'order_content'=>'整车订单收入费用','orderid'=>$value['uoid'],'ordertype'=>'4','action_type'=>'1','driver_id'=>$value['carriersid'],'addtime'=>time()));
                }
            }
        }
    }

    /**
     * 定时任务
     * 货源信息到装货时间自动下架
     * 货源：发布时间后72小时
     * 车源：发布时间后24小时
     * 整车：超过提货时间
     * 城配：超过提货时间
     * orderstate变为4
     * @Auther: 李渊
     * @Date: 2018.7.4
     * @return [type] [description]
     */
    public function cancel_item(){
        // 查询已支付且货源信息在进行中的订单
        $result = Db::table('ct_issue_item')->where(array('paystate'=>2, 'orderstate'=>1, 'ordertype' => 1))->select();
        // 当前时间
        $newTime = time();
        // 循环判断日期是否过期过期则改变状态
        foreach ($result as $key => $value) {
            // 获取下单时间后的72小时
            $addtime = $value['addtime']+72*60*60;
            // 判断时间是否过72小时
            if ($newTime > $addtime) {
                Db::table('ct_issue_item')->where('id',$value['id'])->update(array('orderstate'=>4));
            }  
        }


        // 查询已支付且车源信息在进行中的订单
        $result_carInfo = Db::table('ct_issue_item')->where(array('paystate'=>2, 'orderstate'=>1, 'ordertype' => 2))->select();
        // 循环判断日期是否过期过期则改变状态
        foreach ($result_carInfo  as $key1 => $value1) {
            // 获取下单时间后的72小时
            $loaddate = strtotime($value1['loaddate']);
            // 判断时间是否过72小时
            if ($newTime > $loaddate) {
                Db::table('ct_issue_item')->where('id',$value1['id'])->update(array('orderstate'=>4));
            }  
        }


        // 整车超提货时间未接单（更改订单状态，如支付则退至到余额）
        $result_car = Db::table('ct_userorder')->where(array('orderstate'=>1,'paystate'=>['IN','2,4,5']))->select();
        foreach ($result_car as $key => $value) {
            if (time() > $value['loaddate']/1000) {
                $user_mess = DB::table('ct_user')
                                ->alias('u')
                                ->join('ct_company c','c.cid=u.lineclient','LEFT')
                                ->field('u.money,u.phone,c.cid,c.money com_money')
                                ->where('uid',$value['userid'])
                                ->find();
                //起始城市
                $startCity = addresidToName($value['startcity']);
                //终点城市
                $endCity = addresidToName($value['endcity']);
                //当支付类型为 提货支付、配送支付
                $sendContent = "尊敬的用户：您好！您在本平台下的".$startCity.'发往'.$endCity."整车订单,因超过提货时间未有人接单，平台已取消订单";
                if ($value['paystate']=='2') {
                    if ($value['pay_type'] =='1') {  //信用额度支付时，订单定额恢复到信用额度
                        $sumMoney = $value['referprice'] + $user_mess['com_money'];
                        $com_updata = Db::table('ct_company')->where('cid',$user_mess['cid'])->update(array('money'=>$sumMoney));
                        $sendContent = "尊敬的用户：您好！您在本平台下的".$startCity.'发往'.$endCity."整车订单,因超过提货时间未有人接单，支付订单金额退至您公司额度！！";
                    }else{
                        $sumMoney = $value['referprice'] + $user_mess['money'];
                        $user_update = Db::table('ct_user')->where('uid',$value['userid'])->update(array('money'=>$sumMoney));
                        $sendContent = "尊敬的用户：您好！您在本平台下的".$startCity.'发往'.$endCity."整车订单,因超过提货时间未有人接单，支付订单金额退至您账户余额！！";
                    }
                }
                DB::table('ct_userorder')->where('uoid',$value['uoid'])->update(array('orderstate'=>7));
                send_sms_class($user_mess['phone'],$sendContent);
            }
        }//整车

        //城配超提货时间未接单（更改订单状态，如支付则退至到余额）
        $result_city = Db::table('ct_city_order')->where(array('state'=>1,'paystate'=>['IN','2,4']))->select();
        foreach ($result_city as $key => $value) {
            if (time() > strtotime($value['data_type'])) {
                $user_mess = DB::table('ct_user')
                                ->alias('u')
                                ->join('ct_company c','c.cid=u.lineclient','LEFT')
                                ->field('u.money,u.phone,c.cid,c.money com_money')
                                ->where('uid',$value['userid'])
                                ->find();
                //起始城市
                $startCity = addresidToName($value['city_id']);
                //当支付类型为 提货支付
                $sendContent = "尊敬的用户：您好！您在本平台下的".$startCity."城配订单,因超过提货时间未有人接单，平台取消订单";
                if ($value['paystate']=='2') {
                    if ($value['pay_type'] =='1') {  //信用额度支付时，订单定额恢复到信用额度
                        $sumMoney = $value['actualprice'] + $user_mess['com_money'];
                        $com_updata = Db::table('ct_company')->where('cid',$user_mess['cid'])->update(array('money'=>$sumMoney));
                        $sendContent = "尊敬的用户：您好！您在本平台下的".$startCity."城配订单,因超过提货时间未有人接单，支付订单金额退至您公司额度！！";
                    }else{
                        $sumMoney = $value['actualprice'] + $user_mess['money'];
                        $user_update = Db::table('ct_user')->where('uid',$value['userid'])->update(array('money'=>$sumMoney));
                        $sendContent = "尊敬的用户：您好！您在本平台下的".$startCity."城配订单,因超过提货时间未有人接单，支付订单金额退至您账户余额！！";
                    }
                }
                DB::table('ct_city_order')->where('id',$value['id'])->update(array('state'=>6));
                send_sms_class($user_mess['phone'],$sendContent);
            }
        }
    }
}

?>
