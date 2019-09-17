<?php
namespace app\driver\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型
class Pay extends Base{

	
	//整车余额支付: 信息费-免费期间接口
	public  function  balance_vehicle_free(){
		$token   = input("token");  //令牌
		$uoid = input('uoid'); //订单ID
	    if(empty($token) || empty($uoid)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		//修改状态: orderstate 下单状态 1 待确认 2已完成 3已取消： 司机抢单支付完成才算抢单成功
		$res = Db::table('ct_userorder')->where('uoid',$uoid)->update(array('orderstate'=>'2'));
		if($res){
			return json(['code'=>'1001','message'=>'支付成功']);
		}else{
			return json(['code'=>'1002','message'=>'支付失败']);
		}
	}

	/**
	 * 个人中心-支付宝余额充值接口
	 * @Auther: 李渊
	 * @Date: 2018.7.11
	 * @param  [type] $token 	[令牌]
	 * @param  [type] $price 	[充值金额]
	 * @return [type] [description]
	 */
    public function alipay(){
        // 令牌
        $token   = input("token");
        // 充值金额
        $price = input('price');
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        // 获取司机信息
        $driver_data = Db::table('ct_driver')->where('drivid',$driver_id)->find();
        // 引用支付宝支付sdk
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
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $bizcontent = json_encode([
            'body'=>'支付宝充值',
            'subject'=>'余额充值：'.$driver_data['mobile'],
            'out_trade_no'=>'C'.date('Ymdhis').mt_rand('000','999'),//此订单号为商户唯一订单号
            'total_amount'=>$price,//保留两位小数
            'product_code'=>'QUICK_MSECURITY_PAY',
            'passback_params'=>$driver_id
        ]);
        $request->setNotifyUrl("https://app.56cold.com/driver/pay/notifyurl");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        echo $response;
    }

    /**
     * 个人中心-支付宝回调异步通知
     * @Auther: 李渊
     * @Date: 2018.7.11
     * @param  [type] $token 	[令牌]
     * @param  [type] $price 	[充值金额]
     * @return [type] [description]
     */
    public function notifyurl(){
        require_once EXTEND_PATH.'Alipay/aop/AopClient.php';
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        $data = array();
        if($_POST['trade_status'] == 'TRADE_SUCCESS') {
            $data['orderid'] = $_POST['out_trade_no'];
            $data['paytype'] = '1';
            $data['platformorderid'] = $_POST['trade_no'];
            $data['paynum'] = $_POST['total_amount'];
            $data['paytime'] = time();
            $data['userid'] = $_POST['passback_params'];
            $data['payname'] = $_POST['buyer_logon_id'];
            $data['type'] = '2';
            $data['state'] = '2';
            $driver_data = Db::table('ct_driver')->where('drivid',$_POST['passback_params'])->find();
            $mooney['balance'] = $driver_data['balance']+$_POST['total_amount'];
            Db::table('ct_driver')->where('drivid',$_POST['passback_params'])->update($mooney);
            Db::table("ct_paymessage")->insert($data);
            $payid = Db::table("ct_paymessage")->getLastInsID();
            $balance_data=array(
                'pay_money'=>$_POST['total_amount'],
                'order_content'=>"支付宝充值余额",
                'orderid'=> $payid,
                'ordertype'=> '5',
                'action_type'=>'1', //操作类型： 1 增加 2 扣除
                'driver_id'=>$_POST['passback_params'],
                'addtime'=>time()
            );
            Db::table('ct_balance_driver')->insert($balance_data);
            echo 'success';
        }else{
            echo "fail";
        }
    }
	/**
	 * 个人中心-支付宝支付完成
	 * 获取司机所有信息
	 * @Auther: 李渊
	 * @Date: 2018.7.11
	 * @param  [type] $token 	[令牌]
	 * @param  [type] $price 	[充值金额]
	 * @return [type] [description]
	 */
	public function alipaysuccessfulreturn(){
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
			$driver_id = $check_result['driver_id'];
		}
		$result = Db::field('money')->table('ct_driver')->where('drivid',$driver_id)->find();
		return json_encode($result);
	}

	/**
	 * 个人中心-微信余额充值接口
	 * @Auther: 李渊
	 * @Date: 2018.7.11
	 * @param  [type] $token 	[令牌]
	 * @param  [type] $price 	[充值金额]
	 * @return [type] [description]
	 */
  	public function weixinpay(){
  		// 引用微信支付sdk
	    require_once EXTEND_PATH.'wxAppPay/weixin.php';
	    // 令牌
	    $token   = input("token");
	    // 充值金额
		$price = input('price');
		if(empty($token)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		// 查找司机信息
		$driver_data = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		// appid
	    $appid = 'wx1ed6b733675628df';
	    // 商户号
	    $mch_id = '1487413072';
	    // 回调地址
	    $notify_url = 'https://app.56cold.com/driver/pay/weixinnotifyurl';
	    // 商户支付密钥
	    $key = '011eaf63e5f7944c6a979de570d44aaa';
	    // 实例化微信app支付
	    $wechatAppPay = new \wxAppPay($appid,$mch_id,$notify_url,$key);
	    // 商品描述
	    $params['body'] = $driver_data['mobile'].'余额充值';  
		// 自定义订单号
	    $params['out_trade_no'] = 'C'.date('Ymdhis').mt_rand('000','999');
	    // 订单金额 只能为整数 单位为分
	    $params['total_fee'] = $price*100;                      
	    // 交易类型 JSAPI | NATIVE | APP | WAP 
	    $params['trade_type'] = 'APP';          
	    // 附加参数（用户ID）           
	    $params['attach'] = $driver_id;
	    // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id                      
	    $result = $wechatAppPay->unifiedOrder($params);
	    // 创建APP端预支付参数
	    /** @var TYPE_NAME $result */
	    $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
	    // 根据上行取得的支付参数请求支付即可
	    return json_encode($data);
  	}



	/**
	 * 个人中心-微信异步通知
	 * @Auther: 李渊
	 * @Date: 2018.7.11
	 * @param  [type] $token 	[令牌]
	 * @param  [type] $price 	[充值金额]
	 * @return [type] [description]
	 */
	public function weixinnotifyurl(){
	    ini_set('date.timezone','Asia/Shanghai');  
	    error_reporting(E_ERROR); 
	    // 获取微信支付回调数据
	    $result = file_get_contents('php://input', 'r');
	    file_put_contents('wxpay.txt', $result);
	    libxml_disable_entity_loader(true);
	    $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	    // 判断是否支付成功
	    if ($array_data['return_code'] == 'SUCCESS') {
		    $data['orderid'] = $array_data['out_trade_no'];
		    $data['paytype'] = '2';
		    $data['platformorderid'] = $array_data['transaction_id'];
		    $data['paynum'] = $array_data['total_fee']/100;
		    $data['paytime'] = time();
		    $data['userid'] = $array_data['attach'];
		    $data['payname'] = $array_data['openid'];
		    $data['type'] = '2';
		    $data['state'] = '2';
		    // 查找司机信息
		    $driver_data = Db::table('ct_driver')->where('drivid',$array_data['attach'])->find();
		    // 余额增加
			$mooney['balance'] = $driver_data['balance']+$array_data['total_fee']/100;
			// 更新司机余额
			Db::table('ct_driver')->where('drivid',$array_data['attach'])->update($mooney);
			// 插入支付记录
			$payid = Db::table("ct_paymessage")->insertGetId($data);
			// 插入司机账户变更动态
			$balance_data=array(
				'pay_money'=>$array_data['total_fee']/100,
				'order_content'=>"微信充值余额",
				'orderid' => $payid,
				'ordertype' => '6',
				'action_type'=>'1', //操作类型： 1 增加 2 扣除
				'driver_id'=>$array_data['attach'],
				'addtime'=>time()
			);
			Db::table('ct_balance_driver')->insert($balance_data);
            echo "success";
	    }else{
	        echo "fail";
	    }
	}

	/**
	 * 支付宝余额提现
	 * 提现仅限司机公司不提供提现功能
	 * @Auther: 李渊
	 * @Date: 2018.7.11
	 * @param  [type] $token 	[令牌]
	 * @param  [type] $money 	[提现金额]
	 * @param  [type] $account 	[提现账号]
	 * @param  [type] $paymoney [扣除费率后的提现金额]
	 * @return [type]        [description]
	 */
	public function withdraw(){
		// 令牌
		$token   = input("token");  
		// 提现金额
		$money = input('money'); 
		// 提现账号
		$account = input('account'); 
		// 扣除费率后的提现金额
		$paymoney = input('paymoney'); 
		if(empty($token) || empty($money) || empty($account) || empty($paymoney)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		// 查找承运端登陆人信息
		$user_money = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		// 判断提现金额是否小于余额
		if ($user_money['balance'] < $money) {
			return json(['code'=>'1009','message'=>'提现额度不可大于账户余额']);
			exit;
		}
		// 查询是否有提现中未处理的业务 有则不能进行下一次提现操作
		$iswithdraw = Db::table("ct_application")->where(array('action_type' => 1,'states' => 1,'action_id' => $driver_id))->find();
		// 判断是否有提现中未处理的业务 有则不能进行下一次提现操作
		if($iswithdraw){
			return json(['code'=>'1010','message'=>'你的上一次提现业务正在处理中，请等待上一次提现业务完成再进行提现申请']);
			exit;
		}
		// 插入记录id
		$insert_data['action_id'] = $driver_id;
		// 插入记录 提现类型 司机
		$insert_data['action_type'] = '1';
		// 插入记录 提现金额
		$insert_data['money'] = $paymoney;
		// 插入记录 申请时间
		$insert_data['start_time'] = time();
		// 插入记录 状态 ( 1审核中 2审核成功，3审核失败，4打款完成)
		$insert_data['states'] = '1';
		// 插入记录 支付宝账号
		$insert_data['account'] = $account;
		// 插入记录 1个人2公司
		$insert_data['menu_type'] = '1';
		// 插入记录 实际提现金额
		$insert_data['actual_money'] = $money;
		// 插入记录 提现类型 0 余额提现 1 运费提现
		$insert_data['withdraw_type'] = '0';

		// 更新司机余额
		$dmoney = $user_money['balance']-$money;
		// 更新司机余额
		$re = Db::table('ct_driver')->where('drivid',$driver_id)->update(['balance'=>$dmoney]);
		// 判断是否更新成功
		if ($re) {
			// 插入提现记录并返回id
			$payid = Db::table("ct_application")->insertGetId($insert_data);
			// 司机资产变动明细动态
			$balance_data=array(
				'pay_money'=>$money,
				'order_content'=>"余额提现",
				'action_type'=>'2', //操作类型： 1 增加 2 扣除
				'orderid' => $payid,
				'ordertype' => '7',
				'driver_id'=>$driver_id,
				'addtime'=>time()
			);
			// 插入司机资产变动明细动态
			Db::table('ct_balance_driver')->insert($balance_data);
			return json(['code'=>'1001','message'=>'操作成功','data'=>$dmoney]);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}
	/*
	 * 查询是否有正在处理押金退款的业务
	 * */
	public function deposite(){
        // 令牌
        $token   = input("token");
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        // 查询是否有提现中未处理的业务 有则不能进行下一次提现操作
        $iswithdraw = Db::table("ct_application")->where(array('action_type' => 1,'states' => 1,'action_id' => $driver_id))->find();
        // 判断是否有提现中未处理的业务 有则不能进行下一次提现操作
        if($iswithdraw){
            return json(['code'=>'1001','message'=>'押金退款，正在审核中']);
        }else{
            return json(['code'=>'1002','message'=>'无退款业务']);
        }
    }
	/*
	 * 押金提现
	 * */
    public function depositdraw(){
        // 令牌
        $token   = input("token");
        // 提现金额
        $money = input('price');
        if(empty($token) || empty($money)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        // 查找承运端登陆人信息
        $user_money = Db::table('ct_driver')->where('drivid',$driver_id)->find();
        // 判断提现金额是否小于余额
        if ($user_money['deposit'] < $money) {
            return json(['code'=>'1009','message'=>'退款额度不可大于押金余额']);
            exit;
        }
        $notwithdraw = 1;

        // 查询是否有提现中未处理的业务 有则不能进行下一次提现操作
        $iswithdraw = Db::table("ct_userorder")->field('orderstate')->where('carriersid', $driver_id)->select();
        if ($iswithdraw){
            $withdraw = $this->multiToSingle($iswithdraw);
            $res = [2,5];
            $result = array_intersect($res,$withdraw);
//            $state = empty($result) ?  false : true;
            if ($result){
                $notwithdraw = 0;
            }else{
                $notwithdraw = 1;
            }
        }else{
            $notwithdraw = 1;
        }

        // 判断是否有提现中未处理的业务 有则不能进行下一次提现操作
        if($notwithdraw == 0){
            return json(['code'=>'1010','message'=>'当前账户有订单进行中，请在订单完成后申请退押金']);
            exit;
        }
        // 插入记录id
        $insert_data['action_id'] = $driver_id;
        // 插入记录 提现类型 司机
        $insert_data['action_type'] = '1';
        // 插入记录 提现金额
        $insert_data['money'] = $money;
        // 插入记录 申请时间
        $insert_data['start_time'] = time();
        // 插入记录 状态 ( 1审核中 2审核成功，3审核失败，4打款完成)
        $insert_data['states'] = '1';
        // 插入记录 支付宝账号
//        $insert_data['account'] = $account;
        // 插入记录 1个人2公司
        $insert_data['menu_type'] = '1';
        // 插入记录 实际提现金额
        $insert_data['actual_money'] = $money;
        // 插入记录 提现类型 0 余额提现 1 运费提现
        $insert_data['withdraw_type'] = '0';

        // 更新司机余额
        $dmoney = $money;
        // 更新司机余额
        $re = Db::table('ct_driver')->where('drivid',$driver_id)->update(array('deposit'=>$dmoney,'destate'=>3));
        // 判断是否更新成功
        if ($re) {
            // 插入提现记录并返回id
            $payid = Db::table("ct_application")->insertGetId($insert_data);
            // 司机资产变动明细动态
            $balance_data=array(
                'pay_money'=>$money,
                'order_content'=>"押金退款",
                'action_type'=>'2', //操作类型： 1 增加 2 扣除
                'orderid' => $payid,
                'ordertype' => '8',
                'driver_id'=>$driver_id,
                'addtime'=>time()
            );
            // 插入司机资产变动明细动态
            Db::table('ct_balance_driver')->insert($balance_data);
            return json(['code'=>'1001','message'=>'操作成功','data'=>$money]);
        }else{
            return json(['code'=>'1002','message'=>'操作失败']);
        }
    }

	/**
	 * 支付宝运费提现
	 * 提现仅限司机公司不提供提现功能
	 * @Auther: 李渊
	 * @Date: 2018.7.11
	 * @param  [type] $token 	[令牌]
	 * @param  [type] $money 	[提现金额]
	 * @param  [type] $account 	[提现账号]
	 * @param  [type] $paymoney [扣除费率后的提现金额]
	 * @return [type]        [description]
	 */
	public function withdrawFreight(){
		// 令牌
		$token   = input("token");  
		// 提现金额
		$money = input('money'); 
		// 提现账号
		$account = input('account'); 
		// 扣除费率后的提现金额
		$paymoney = input('paymoney'); 
		if(empty($token) || empty($money) || empty($account) || empty($paymoney)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		// 查找承运端登陆人信息
		$user_money = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		// 判断提现金额是否小于余额
		if ($user_money['money'] < $money) {
			return json(['code'=>'1009','message'=>'提现额度不可大于运费']);
			exit;
		}
		// 查询是否有提现中未处理的业务 有则不能进行下一次提现操作
		$iswithdraw = Db::table("ct_application")->where(array('action_type' => 1,'states' => 1,'action_id' => $driver_id))->find();
		// 判断是否有提现中未处理的业务 有则不能进行下一次提现操作
		if($iswithdraw){
			return json(['code'=>'1010','message'=>'你的上一次提现业务正在处理中，请等待上一次提现业务完成再进行提现申请']);
			exit;
		}
		// 插入记录id
		$insert_data['action_id'] = $driver_id;
		// 插入记录 提现类型 司机
		$insert_data['action_type'] = '1';
		// 插入记录 提现金额
		$insert_data['money'] = $paymoney;
		// 插入记录 申请时间
		$insert_data['start_time'] = time();
		// 插入记录 状态 ( 1审核中 2审核成功，3审核失败，4打款完成)
		$insert_data['states'] = '1';
		// 插入记录 支付宝账号
		$insert_data['account'] = $account;
		// 插入记录 1个人2公司
		$insert_data['menu_type'] = '1';
		// 插入记录 实际提现金额
		$insert_data['actual_money'] = $money;
		// 插入记录 提现类型 0 余额提现 1 运费提现
		$insert_data['withdraw_type'] = '1';

		// 更新司机余额
		$dmoney = $user_money['money']-$money;
		// 更新司机余额
		$re = Db::table('ct_driver')->where('drivid',$driver_id)->update(array('money'=>$dmoney));
		// 判断是否更新成功
		if ($re) {
			// 插入提现记录并返回id
			$payid = Db::table("ct_application")->insertGetId($insert_data);
			// 司机资产变动明细动态
			$balance_data=array(
				'pay_money'=>$money,
				'order_content'=>"运费提现",
				'action_type'=>'2', //操作类型： 1 增加 2 扣除
				'orderid' => $payid,
				'ordertype' => '7',
				'driver_id'=>$driver_id,
				'addtime'=>time()
			);
			// 插入司机资产变动明细动态
			Db::table('ct_balance_driver')->insert($balance_data);
			return json(['code'=>'1001','message'=>'操作成功','dmoney'=>$dmoney]);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

    //支付宝充值 2019.5.27
    public function zalipay(){
        $token   = input("token");  //令牌
        $price = input('price'); //充值金额
        if(empty($token) || empty($price)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        // 获取司机信息
        $driver_data = Db::table('ct_driver')->where('drivid',$driver_id)->find();
        // 引用支付宝支付sdk
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
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $bizcontent = json_encode([
            'body'=>'支付宝充值',
            'subject'=>'月卡套餐充值：'.$driver_data['mobile'],
            'out_trade_no'=>'C'.date('Ymdhis').mt_rand('000','999'),//此订单号为商户唯一订单号
            'total_amount'=>$price,//保留两位小数
            'product_code'=>'QUICK_MSECURITY_PAY',
            'passback_params'=>$driver_id
        ]);
        $request->setNotifyUrl("https://t.56cold.com/driver/pay/zcnotifyurl");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        echo $response;

    }
    //支付宝充值回调

    public function zcnotifyurl(){
        require_once EXTEND_PATH.'Alipay/aop/AopClient.php';
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        $data = array();
        if($_POST['trade_status'] == 'TRADE_SUCCESS') {
            $data['orderid'] = $_POST['out_trade_no'];
            $data['paytype'] = '1';
            $data['platformorderid'] = $_POST['trade_no'];
            $data['paynum'] = $_POST['total_amount'];
            $data['paytime'] = time();
            $data['userid'] = $_POST['passback_params'];
            $data['payname'] = $_POST['buyer_logon_id'];
            $data['type'] = '2';
            $data['state'] = '2';
            Db::table('ct_driver')->where('drivid',$_POST['passback_params'])->update(array('level'=>'2'));
            Db::table("ct_paymessage")->insert($data);
            $payid = Db::table("ct_paymessage")->getLastInsID();
            $balance_data=array(
                'pay_money'=>$_POST['total_amount'],
                'order_content'=>"月卡套餐充值",
                'orderid'=> $payid,
                'ordertype'=> '5',
                'action_type'=>'1', //操作类型： 1 增加 2 扣除
                'driver_id'=>$_POST['passback_params'],
                'addtime'=>time()
            );
            Db::table('ct_balance_driver')->insert($balance_data);
            echo "success";
        }else{
            echo "fail";
        }
    }
    //充值黄金会员
    public function czpay(){
        $token   = input("token");  //令牌
        $price = input('price'); //充值金额
        if(empty($token) || empty($price)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        // 获取司机信息
        $driver_data = Db::table('ct_driver')->where('drivid',$driver_id)->find();
        // 引用支付宝支付sdk
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
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $bizcontent = json_encode([
            'body'=>'支付宝充值',
            'subject'=>'年卡套餐充值：'.$driver_data['mobile'],
            'out_trade_no'=>'C'.date('Ymdhis').mt_rand('000','999'),//此订单号为商户唯一订单号
            'total_amount'=>$price,//保留两位小数
            'product_code'=>'QUICK_MSECURITY_PAY',
            'passback_params'=>$driver_id
        ]);
        $request->setNotifyUrl("https://app.56cold.com/driver/pay/cznotifyurl");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        echo $response;
    }
    public function cznotifyurl(){
        require_once EXTEND_PATH.'Alipay/aop/AopClient.php';
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        $data = array();
        if($_POST['trade_status'] == 'TRADE_SUCCESS') {
            $data['orderid'] = $_POST['out_trade_no'];
            $data['paytype'] = '1';
            $data['platformorderid'] = $_POST['trade_no'];
            $data['paynum'] = $_POST['total_amount'];
            $data['paytime'] = time();
            $data['userid'] = $_POST['passback_params'];
            $data['payname'] = $_POST['buyer_logon_id'];
            $data['type'] = '2';
            $data['state'] = '2';
            Db::table('ct_driver')->where('drivid',$_POST['passback_params'])->update(array('level'=>'3'));
            Db::table("ct_paymessage")->insert($data);
            $payid = Db::table("ct_paymessage")->getLastInsID();
            $balance_data=array(
                'pay_money'=>$_POST['total_amount'],
                'order_content'=>"年卡套餐充值",
                'orderid'=> $payid,
                'ordertype'=> '5',
                'action_type'=>'1', //操作类型： 1 增加 2 扣除
                'driver_id'=>$_POST['passback_params'],
                'addtime'=>time()
            );
            Db::table('ct_balance_driver')->insert($balance_data);
            echo "success";
        }else{
            echo "fail";
        }
    }
    /*
     * 押金充值（支付宝）
     * */
    public function depositpay(){
        $token   = input("token");  //令牌
        $price = input('price'); //充值金额
        if(empty($token) || empty($price)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        // 获取司机信息
        $driver_data = Db::table('ct_driver')->where('drivid',$driver_id)->find();
        // 引用支付宝支付sdk
        require_once EXTEND_PATH.'Alipay/aop/AopClient.php';
        require_once EXTEND_PATH.'Alipay/aop/request/AlipayTradeAppPayRequest.php';
        $aop = new \AopClient();
        $request = new \AlipayTradeAppPayRequest();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do ";
        $aop->appId = "2017052307318743";
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAuWqafyecwj1VxcHQjFHrPIqhKrfMPjQRVRTs7/PvGlCXOxV34KaAop4XWEBKgvWhdQX2JkMDLSwPkH790TBJVS84/zQ6sjanpHjgT82/AimuS+/Vk8pB/pAfnOnRN3dhe6y2i9kzJPU62Uj9qn5jJXbWJhyM16Zxdk7GBOChis3C3KvB2WN8qAQawqfUvgHRm/yUgNfVUutKRMdDdQxQypwxkEP50+U9qKeSQecZRyo6xmJ5CWbULQ7FpV5q6lmM7SbyBuyDVk7z4itLIgE8qpt6B3cp9Qm3U3f6DoVJA2LAjinP4v6kNVb/f5qu8VpmR0DD+dRJ1+ujDz1EC/f/lwIDAQABAoIBAHrS0DcM8X2GDcxrQA/DsDUxi+N1T1mhOh4HN5EYILpoylU8OmXZRfrzCHnQVMt9lQ+k/FKKL4970W+hf9dTyjAgkPwVCBDHvbNo0wZqP25aV/g7jlpRL/hGVnqmNI4uiafYWDA5l/SScgI/pLGM+XZ2yxMB9JZhzmVVdz0B5GDCHcjQUkY3//8Tpgw6ylngrq67KjWDbZPAZQHcpj/hdYPOu7Z1kXp30jtdEZi6S+7ZJe/AWMSuEtwWsM53ZOyxqPjSwbW8XfWHHbG3yKF6sngCmwRpwX5rp1EjSsVhA5rbpCM0jbYCKp977XwkGtG6xAOydZdz0WHyirDUTA3PMTECgYEA4lzvyfcg0SyaOWVszwxcWntVm6sQG7deaSlW92Urhy7qaDnv4Ad8TEe0M0QGVllnZUDJA3x8NzoD5DlFROUGZpI/uJk5a0dQlvMbyzS2rx2v4TP19Xm5D7iQk0RK5Zry0K/Fj1kZusIVm3qwsl1DlunAfGipZ1TV0C7QNUJcW0kCgYEA0bE/3ljnSPsKjpc+projOuaLqf7+0x3ITaYle60MbwZrjUnX3cSwbqN3Iu12Npa3mI+RwTyDifFgWB/8hFoqTecFGDnxRa1e7DLlJX9FkIMtoroVsDJUMD+HUx01t9V8fEqVPNyRmnbFyXfdHrRb7zYefwuPZcoE18reADc9o98CgYB1zDl5F+L7F8P2ZIK4SM1yxMYrKV1LnyRBg6LfQcXiJpcTwDrFkf+sTpBHMXo+y23UMl+pMcoOj2FhDjCvBqRLEoaYkRxhaI5Wz5LCL991x/Q0NO8lXL/in4CVMq/rRrRfx2j/DTYni0LlU3bKi2BWE7T4yRqHTI2sNgBiBvO7CQKBgQCDsHNR6jdmR/J7VlTMVH2nkf4IRtI2N7ABw+QqZaU3XKrS0ps09T9wXEyHrOXepoyqzQ9WcfCSAvrknUHyxMVoozs52bnCbnz8jYIHKITBmwBf/8l7HEBvBJayBdgkmXhSfmx3CnaOsSTJv/MoQ1CxTCWe1924qUSdWRROwmJ9tQKBgQCgWUnO0z1O46N1p66gcA0NrRMFsncotg42MipvUpCrMN6lJ80/H7Kj1tGOizJazLXPKN9NKl/lco0xJyAyZS4vFacZXbH2OO0jHyfovPblSY5O10g3d1PC4mbZ/wd4HU4QVO21+U5dIH/HPubhOGQWcpAO+3Fqxx7VFuaZPbsC7g==';
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $bizcontent = json_encode([
            'body'=>'支付宝充值',
            'subject'=>'押金充值：'.$driver_data['mobile'],
            'out_trade_no'=>'C'.date('Ymdhis').mt_rand('000','999'),//此订单号为商户唯一订单号
            'total_amount'=>$price,//保留两位小数
            'product_code'=>'QUICK_MSECURITY_PAY',
            'passback_params'=>$driver_id
        ]);
        $request->setNotifyUrl("https://app.56cold.com/driver/pay/yjnotifyurl");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        echo $response;
    }
    public function yjnotifyurl(){
        require_once EXTEND_PATH.'Alipay/aop/AopClient.php';
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        $data = array();
        if($_POST['trade_status'] == 'TRADE_SUCCESS') {
            $data['orderid'] = $_POST['out_trade_no'];
            $data['paytype'] = '1';
            $data['platformorderid'] = $_POST['trade_no'];
            $data['paynum'] = $_POST['total_amount'];
            $data['paytime'] = time();
            $data['userid'] = $_POST['passback_params'];
            $data['payname'] = $_POST['buyer_logon_id'];
            $data['type'] = '2';
            $data['state'] = '2';
            Db::table('ct_driver')->where('drivid',$_POST['passback_params'])->update(array('deposit'=>'59','destate'=>'2'));
            Db::table("ct_paymessage")->insert($data);
            $payid = Db::table("ct_paymessage")->getLastInsID();
            $balance_data=array(
                'pay_money'=>$_POST['total_amount'],
                'order_content'=>"押金充值",
                'orderid'=> $payid,
                'ordertype'=> '5',
                'action_type'=>'1', //操作类型： 1 增加 2 扣除
                'driver_id'=>$_POST['passback_params'],
                'addtime'=>time()
            );
            Db::table('ct_balance_driver')->insert($balance_data);
            echo "success";
        }else{
            echo "fail";
        }
    }
    /*
     * 押金充值（微信）
     * */
    public function yjwechatpay(){
        // 引用微信支付sdk
        require_once EXTEND_PATH.'wxAppPay/weixin.php';
        // 令牌
        $token   = input("token");
        // 充值金额
        $price = input('price');
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        // 查找司机信息
        $driver_data = Db::table('ct_driver')->where('drivid',$driver_id)->find();
        // appid
        $appid = 'wx1ed6b733675628df';
        // 商户号
        $mch_id = '1487413072';
        // 回调地址
        $notify_url = 'https://app.56cold.com/driver/pay/yxnotifyurl';
        // 商户支付密钥
        $key = '011eaf63e5f7944c6a979de570d44aaa';
        // 实例化微信app支付
        $wechatAppPay = new \wxAppPay($appid,$mch_id,$notify_url,$key);
        // 商品描述
        $params['body'] = $driver_data['mobile'].'押金充值';
        // 自定义订单号
        $params['out_trade_no'] = 'C'.date('Ymdhis').mt_rand('000','999');
        // 订单金额 只能为整数 单位为分
        $params['total_fee'] = $price*100;
        // 交易类型 JSAPI | NATIVE | APP | WAP
        $params['trade_type'] = 'APP';
        // 附加参数（用户ID）
        $params['attach'] = $driver_id;
        // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
        $result = $wechatAppPay->unifiedOrder($params);
        // 创建APP端预支付参数
        /** @var TYPE_NAME $result */
        $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
        // 根据上行取得的支付参数请求支付即可
        return json_encode($data);
    }
    public function yxnotifyurl(){
        ini_set('date.timezone','Asia/Shanghai');
        error_reporting(E_ERROR);
        // 获取微信支付回调数据
        $result = file_get_contents('php://input', 'r');
        file_put_contents('wxpay.txt', $result);
        libxml_disable_entity_loader(true);
        $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        // 判断是否支付成功
        if ($array_data['return_code'] == 'SUCCESS') {
            $data['orderid'] = $array_data['out_trade_no'];
            $data['paytype'] = '2';
            $data['platformorderid'] = $array_data['transaction_id'];
            $data['paynum'] = $array_data['total_fee']/100;
            $data['paytime'] = time();
            $data['userid'] = $array_data['attach'];
            $data['payname'] = $array_data['openid'];
            $data['type'] = '2';
            $data['state'] = '2';
            // 查找司机信息
            Db::table('ct_driver')->where('drivid',$array_data['attach'])->update(array('deposit'=>'59','destate'=>2));

            // 插入支付记录
            $payid = Db::table("ct_paymessage")->insertGetId($data);
            // 插入司机账户变更动态
            $balance_data=array(
                'pay_money'=>$array_data['total_fee']/100,
                'order_content'=>"押金充值",
                'orderid' => $payid,
                'ordertype' => '6',
                'action_type'=>'1', //操作类型： 1 增加 2 扣除
                'driver_id'=>$array_data['attach'],
                'addtime'=>time()
            );
            Db::table('ct_balance_driver')->insert($balance_data);
            echo "success";
        }else{
            echo "fail";
        }
    }
    //微信黄金会员充值
    public function hwxpay(){
        // 引用微信支付sdk
        require_once EXTEND_PATH.'wxAppPay/weixin.php';
        // 令牌
        $token   = input("token");
        // 充值金额
        $price = input('price');
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        // 查找司机信息
        $driver_data = Db::table('ct_driver')->where('drivid',$driver_id)->find();
        // appid
        $appid = 'wx1ed6b733675628df';
        // 商户号
        $mch_id = '1487413072';
        // 回调地址
        $notify_url = 'https://app.56cold.com/driver/pay/hnotifyurl';
        // 商户支付密钥
        $key = '011eaf63e5f7944c6a979de570d44aaa';
        // 实例化微信app支付
        $wechatAppPay = new \wxAppPay($appid,$mch_id,$notify_url,$key);
        // 商品描述
        $params['body'] = $driver_data['mobile'].'余额充值';
        // 自定义订单号
        $params['out_trade_no'] = 'C'.date('Ymdhis').mt_rand('000','999');
        // 订单金额 只能为整数 单位为分
        $params['total_fee'] = $price*100;
        // 交易类型 JSAPI | NATIVE | APP | WAP
        $params['trade_type'] = 'APP';
        // 附加参数（用户ID）
        $params['attach'] = $driver_id;
        // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
        $result = $wechatAppPay->unifiedOrder($params);
        // 创建APP端预支付参数
        /** @var TYPE_NAME $result */
        $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
        // 根据上行取得的支付参数请求支付即可
        return json_encode($data);
    }
    public function hnotifyurl(){
        ini_set('date.timezone','Asia/Shanghai');
        error_reporting(E_ERROR);
        // 获取微信支付回调数据
        $result = file_get_contents('php://input', 'r');
        file_put_contents('wxpay.txt', $result);
        libxml_disable_entity_loader(true);
        $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        // 判断是否支付成功
        if ($array_data['return_code'] == 'SUCCESS') {
            $data['orderid'] = $array_data['out_trade_no'];
            $data['paytype'] = '2';
            $data['platformorderid'] = $array_data['transaction_id'];
            $data['paynum'] = $array_data['total_fee']/100;
            $data['paytime'] = time();
            $data['userid'] = $array_data['attach'];
            $data['payname'] = $array_data['openid'];
            $data['type'] = '2';
            $data['state'] = '2';
            // 查找司机信息
            Db::table('ct_driver')->where('drivid',$array_data['attach'])->update(array('level'=>'3'));

            // 插入支付记录
            $payid = Db::table("ct_paymessage")->insertGetId($data);
            // 插入司机账户变更动态
            $balance_data=array(
                'pay_money'=>$array_data['total_fee']/100,
                'order_content'=>"年卡套餐充值",
                'orderid' => $payid,
                'ordertype' => '6',
                'action_type'=>'1', //操作类型： 1 增加 2 扣除
                'driver_id'=>$array_data['attach'],
                'addtime'=>time()
            );
            Db::table('ct_balance_driver')->insert($balance_data);
            echo "success";
        }else{
            echo "fail";
        }
    }

    //微信会员充值 2019.5.30
    public function zcwxpay(){
        // 引用微信支付sdk
        require_once EXTEND_PATH.'wxAppPay/weixin.php';
        // 令牌
        $token   = input("token");
        // 充值金额
        $price = input('price');
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        // 查找司机信息
        $driver_data = Db::table('ct_driver')->where('drivid',$driver_id)->find();
        // appid
        $appid = 'wx1ed6b733675628df';
        // 商户号
        $mch_id = '1487413072';
        // 回调地址
        $notify_url = 'https://app.56cold.com/driver/pay/weixinpaynotify';
        // 商户支付密钥
        $key = '011eaf63e5f7944c6a979de570d44aaa';
        // 实例化微信app支付
        $wechatAppPay = new \wxAppPay($appid,$mch_id,$notify_url,$key);
        // 商品描述
        $params['body'] = $driver_data['mobile'].'余额充值';
        // 自定义订单号
        $params['out_trade_no'] = 'C'.date('Ymdhis').mt_rand('000','999');
        // 订单金额 只能为整数 单位为分
        $params['total_fee'] = $price*100;
        // 交易类型 JSAPI | NATIVE | APP | WAP
        $params['trade_type'] = 'APP';
        // 附加参数（用户ID）
        $params['attach'] = $driver_id;
        // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
        $result = $wechatAppPay->unifiedOrder($params);
        // 创建APP端预支付参数
        /** @var TYPE_NAME $result */
        $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
        // 根据上行取得的支付参数请求支付即可
        return json_encode($data);
    }
    //微信会员充值回调 2019.5.30
    public function weixinpaynotify(){

        ini_set('date.timezone','Asia/Shanghai');
        error_reporting(E_ERROR);
        // 获取微信支付回调数据
        $result = file_get_contents('php://input', 'r');
        file_put_contents('wxpay.txt', $result);
        libxml_disable_entity_loader(true);
        $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        // 判断是否支付成功
        if ($array_data['return_code'] == 'SUCCESS') {

            $data['orderid'] = $array_data['out_trade_no'];
            $data['paytype'] = '2';
            $data['platformorderid'] = $array_data['transaction_id'];
            $data['paynum'] = $array_data['total_fee']/100;
            $data['paytime'] = time();
            $data['userid'] = $array_data['attach'];
            $data['payname'] = $array_data['openid'];
            $data['type'] = '2';
            $data['state'] = '2';
            // 查找司机信息
            Db::table('ct_driver')->where('drivid',$array_data['attach'])->update(array('level'=>'2'));
            // 插入支付记录
            $payid = Db::table("ct_paymessage")->insertGetId($data);
            // 插入司机账户变更动态
            $balance_data=array(
                'pay_money'=>$array_data['total_fee']/100,
                'order_content'=>"月卡套餐充值",
                'orderid' => $payid,
                'ordertype' => '6',
                'action_type'=>'1', //操作类型： 1 增加 2 扣除
                'driver_id'=>$array_data['attach'],
                'addtime'=>time()
            );
            Db::table('ct_balance_driver')->insert($balance_data);
            echo "success";
        }else{
            echo "fail";
        }
        }

    //整车单条信息支付宝支付
    public function zcpay(){
        // 令牌
        $token = input("token");
        // 订单ID
        $id = input('id');
        // 产品支付类型   1整车支付 2 市内配送支付 3 零担支付
        $type = input('type');
        // 支付金额
        $zsprice = input('price');
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        if($type == 1){
            $data = array();
            $ordernumber = Db::field('ordernumber')->table('ct_driverorder')->where('id',$id)->find();
            $data['price'] = $zsprice;
            $data['ordernumber'] = $ordernumber['ordernumber'];
        }elseif($type == 2){
            $data = array();
            $ordernumber = Db::field('ordernumber')->table('ct_delivery_order')->where('id',$id)->find();
            $data['price'] = $zsprice;
            $data['ordernumber'] = $ordernumber['ordernumber'];
        }elseif($type == 3){
            $data = array();
            $ordernumber = Db::field('ordernumber')->table('ct_bulk_order')->where('id',$id)->find();
            $data['price'] = $zsprice;
            $data['ordernumber'] = $ordernumber['ordernumber'];
        }
        // 引用支付宝支付sdk
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
        switch ($type){
            case '1':
                $subject = '整车订单支付'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $notifyurl = "https://app.56cold.com/driver/pay/zcnotify";
                break;
            case '2':
                $subject = '城配订单支付'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $notifyurl = "https://app.56cold.com/driver/pay/cpnotify";
                break;
            case '3':
                $subject = '零担订单支付'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $notifyurl = "https://app.56cold.com/driver/pay/bulknotify";
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
        echo $response;
    }
    //信息支付回调
    public function zcnotify(){
        require_once EXTEND_PATH.'Alipay/aop/AopClient.php';
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        $data = array();
        if($_POST['trade_status'] == 'TRADE_SUCCESS') {
            $data['orderid'] = $_POST['out_trade_no'];
            $data['paytype'] = '1';
            $data['platformorderid'] = $_POST['trade_no'];
            $data['paynum'] = $_POST['total_amount'];
            $data['paytime'] = time();
            $data['payname'] = $_POST['buyer_logon_id'];
            $data['type'] = '2';
            $data['state'] = '2';
            $driver_data = Db::field('a.*,b.paystate,b.price,b.uoid,b.startcity')
                ->table('ct_driver')
                ->alias('a')
                ->join('ct_driverorder b','b.driverid = a.drivid')
                ->where('b.ordernumber',$_POST['out_trade_no'])
                ->find();
            $data['userid'] = $driver_data['drivid'];
            Db::table('ct_driverorder')->where('ordernumber',$_POST['out_trade_no'])->update(array('paystate'=>'1','orderstatus'=>'2'));
            $payid= Db::table("ct_paymessage")->insert($data);
            $balance_data=array(
                'pay_money'=>$_POST['total_amount'],
                'order_content'=>"整车支付宝支付",
                'orderid'=> $payid,
                'ordertype'=> '4',
                'action_type'=>'2', //操作类型： 1 增加 2 扣除
                'driver_id'=>$driver_data['drivid'],
                'addtime'=>time()
            );
            Db::table('ct_balance_driver')->insert($balance_data);
            echo "success";
        }else{
            echo "fail";
        }
    }
    //城配单条信息支付宝支付回调
    public function cpnotify(){
        require_once EXTEND_PATH.'Alipay/aop/AopClient.php';
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        $data = array();
        if($_POST['trade_status'] == 'TRADE_SUCCESS') {
            $data['orderid'] = $_POST['out_trade_no'];
            $data['paytype'] = '1';
            $data['platformorderid'] = $_POST['trade_no'];
            $data['paynum'] = $_POST['total_amount'];
            $data['paytime'] = time();
            $data['payname'] = $_POST['buyer_logon_id'];
            $data['type'] = '2';
            $data['state'] = '2';
            $driver_data = Db::field('a.*,b.paystate,b.price,b.uoid,b.startcity')
                ->table('ct_driver')
                ->alias('a')
                ->join('ct_delivery_order b','b.driverid = a.drivid')
                ->where('b.ordernumber',$_POST['out_trade_no'])
                ->find();
            $data['userid'] = $driver_data['drivid'];
            Db::table('ct_delivery_order')->where('ordernumber',$_POST['out_trade_no'])->update(array('paystate'=>'1','orderstatus'=>'2'));
            Db::table("ct_paymessage")->insert($data);
            $payid = Db::table("ct_paymessage")->getLastInsID();
            $balance_data=array(
                'pay_money'=>$_POST['total_amount'],
                'order_content'=>"城配信息支付宝支付",
                'orderid'=> $payid,
                'ordertype'=> '3',
                'action_type'=>'2', //操作类型： 1 增加 2 扣除
                'driver_id'=>$driver_data['drivid'],
                'addtime'=>time()
            );
            Db::table('ct_balance_driver')->insert($balance_data);
            echo "success";
        }else{
            echo "fail";
        }
    }
    /*
     * 零担信息支付回调
     * */
    public function bulknotify(){
        require_once EXTEND_PATH.'Alipay/aop/AopClient.php';
        $aop = new \AopClient();
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAuQzIBEB5B/JBGh4mqr2uJp6NplptuW7p7ZZ+uGeC8TZtGpjWi7WIuI+pTYKM4XUM4HuwdyfuAqvePjM2ch/dw4JW/XOC/3Ww4QY2OvisiTwqziArBFze+ehgCXjiWVyMUmUf12/qkGnf4fHlKC9NqVQewhLcfPa2kpQVXokx3l0tuclDo1t5+1qi1b33dgscyQ+Xg/4fI/G41kwvfIU+t9unMqP6mbXcBec7z5EDAJNmDU5zGgRaQgupSY35BBjW8YVYFxMXL4VnNX1r5wW90ALB288e+4/WDrjTz5nu5yeRUqBEAto3xDb5evhxXHliGJMqwd7zqXQv7Q+iVIPpXQIDAQAB';
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        $data = array();
        if($_POST['trade_status'] == 'TRADE_SUCCESS') {
            $data['orderid'] = $_POST['out_trade_no'];
            $data['paytype'] = '1';
            $data['platformorderid'] = $_POST['trade_no'];
            $data['paynum'] = $_POST['total_amount'];
            $data['paytime'] = time();
            $data['payname'] = $_POST['buyer_logon_id'];
            $data['type'] = '2';
            $data['state'] = '2';
            $driver_data = Db::field('a.*,b.driverid')
                ->table('ct_driver')
                ->alias('a')
                ->join('ct_bulk_order b','b.driverid = a.drivid')
                ->where('b.ordernumber',$_POST['out_trade_no'])
                ->find();
            $data['userid'] = $driver_data['drivid'];
            Db::table('ct_bulk_order')->where('ordernumber',$_POST['out_trade_no'])->update(array('pay_type'=>'3','paystate'=>'2','orderstate'=>'2'));
            Db::table("ct_paymessage")->insert($data);
            $payid = Db::table("ct_paymessage")->getLastInsID();
            $balance_data=array(
                'pay_money'=>$_POST['total_amount'],
                'order_content'=>"零担信息支付宝支付",
                'orderid'=> $payid,
                'ordertype'=> '1',
                'action_type'=>'2', //操作类型： 1 增加 2 扣除
                'driver_id'=>$driver_data['drivid'],
                'addtime'=>time()
            );
            Db::table('ct_balance_driver')->insert($balance_data);
            echo "success";
        }else{
            echo "fail";
        }
    }
    //微信支付回调
    public function weixinnotify(){
        ini_set('date.timezone','Asia/Shanghai');
        error_reporting(E_ERROR);
        $result = file_get_contents('php://input', 'r');
        file_put_contents('wxpay.txt', $result);

        $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($array_data['return_code'] == 'SUCCESS') {
            $data['orderid'] = $array_data['out_trade_no'];
            $data['paytype'] = '2';
            $data['platformorderid'] = $array_data['transaction_id'];
            $data['paynum'] = $array_data['total_fee']/100;
            $data['paytime'] = time();
//            $data['userid'] = $_POST['passback_params'];
            $data['payname'] = $array_data['openid'];
            $data['type'] = '1';
            $data['state'] = '1';
            $driver_data = Db::field('a.*,b.paystate,b.price,b.id')
                ->table('ct_driver')
                ->alias('a')
                ->join('ct_driverorder b','b.driverid = a.drivid')
                ->where('b.ordernumber',$array_data['out_trade_no'])
                ->find();
            $data['userid'] = $driver_data['drivid'];
            Db::table('ct_driverorder')->where('ordernumber',$array_data['out_trade_no'])->update(array('paystate'=>'1','orderstatus'=>'2'));
            $payid = Db::table("ct_paymessage")->insertGetId($data);
            // 插入司机账户变更动态
            $balance_data=array(
                'pay_money'=>$array_data['total_fee']/100,
                'order_content'=>"整车信息微信支付",
                'orderid' => $payid,
                'ordertype' => '4',
                'action_type'=>'2', //操作类型： 1 增加 2 扣除
                'driver_id'=>$driver_data['drivid'],
                'addtime'=>time()
            );
            Db::table('ct_balance_driver')->insert($balance_data);
            echo "success";
        }else{
            echo 'fail';
        }
    }
    //城配微信支付回调
      public function wxnotify(){
          ini_set('date.timezone','Asia/Shanghai');
          error_reporting(E_ERROR);
          $result = file_get_contents('php://input', 'r');
          file_put_contents('wxpay.txt', $result);
          $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);

          if ($array_data['return_code'] == 'SUCCESS') {

              $data['orderid'] = $array_data['out_trade_no'];
              $data['paytype'] = '2';
              $data['platformorderid'] = $array_data['transaction_id'];
              $data['paynum'] = $array_data['total_fee']/100;
              $data['paytime'] = time();
//            $data['userid'] = $_POST['passback_params'];
              $data['payname'] = $array_data['openid'];
              $data['type'] = '1';
              $data['state'] = '1';
              $driver_data = Db::field('a.*,b.paystate,b.price,b.uoid')
                  ->table('ct_driver')
                  ->alias('a')
                  ->join('ct_delivery_order b','b.driverid = a.drivid')
                  ->where('b.ordernumber',$array_data['out_trade_no'])
                  ->find();
              $data['userid'] = $driver_data['drivid'];
              Db::table('ct_delivery_order')->where('ordernumber',$array_data['out_trade_no'])->update(array('paystate'=>'2','orderstatus'=>'2'));
              $payid = Db::table("ct_paymessage")->insertGetId($data);
              // 插入司机账户变更动态
              $balance_data=array(
                  'pay_money'=>$array_data['total_fee']/100,
                  'order_content'=>"城配信息微信支付",
                  'orderid' => $payid,
                  'ordertype' => '3',
                  'action_type'=>'2', //操作类型： 1 增加 2 扣除
                  'driver_id'=>$driver_data['drivid'],
                  'addtime'=>time()
              );
              Db::table('ct_balance_driver')->insert($balance_data);
              echo "success";
          }else{
              echo 'fail';
          }
      }
      /*
       * 零担微信支付回调
       * */
      public function bulkwxnotify(){
          ini_set('date.timezone','Asia/Shanghai');
          error_reporting(E_ERROR);
          $result = file_get_contents('php://input', 'r');
          file_put_contents('wxpay.txt', $result);
          $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
          if ($array_data['return_code'] == 'SUCCESS') {
              $data['orderid'] = $array_data['out_trade_no'];
              $data['paytype'] = '2';
              $data['platformorderid'] = $array_data['transaction_id'];
              $data['paynum'] = $array_data['total_fee']/100;
              $data['paytime'] = time();
//            $data['userid'] = $_POST['passback_params'];
              $data['payname'] = $array_data['openid'];
              $data['type'] = '1';
              $data['state'] = '1';
              $driver_data = Db::field('a.*,b.driverid')
                  ->table('ct_driver')
                  ->alias('a')
                  ->join('ct_bulk_order b','b.driverid = a.drivid')
                  ->where('b.ordernumber',$array_data['out_trade_no'])
                  ->find();
              $data['userid'] = $driver_data['drivid'];
              Db::table('ct_bulk_order')->where('ordernumber',$array_data['out_trade_no'])->update(array('pay_type'=>'4','paystate'=>'2','orderstate'=>'2'));
              $payid = Db::table("ct_paymessage")->insertGetId($data);
              // 插入司机账户变更动态
              $balance_data=array(
                  'pay_money'=>$array_data['total_fee']/100,
                  'order_content'=>"零担信息微信支付",
                  'orderid' => $payid,
                  'ordertype' => '1',
                  'action_type'=>'2', //操作类型： 1 增加 2 扣除
                  'driver_id'=>$driver_data['drivid'],
                  'addtime'=>time()
              );
              Db::table('ct_balance_driver')->insert($balance_data);
              echo "success";
          }else{
              echo 'fail';
          }
      }
      //整车微信支付
    public function ztwxpay(){
        $token  = input("token");  //令牌
        $id = input('id');//订单ID
        $type = input('type');//产品支付类型 1 零担支付 2市内配送支付 3整车 4 定制线路
        $zsprice = input('price');//支付金额
        $data = array();
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        if($type == 1){
            $ordernumber = Db::field('ordernumber')->table('ct_driverorder')->where('id',$id)->find();
            $data['price'] = $zsprice;
            $data['ordernumber'] = $ordernumber['ordernumber'];
        }elseif($type == 2){
            $car_where['ordernumber'] = 'SP'.date('Ymdhis').mt_rand('000000','999999'); //订单编号
            Db::table('ct_delivery_order')->where('id',$id)->update($car_where);
            $ordernumber = Db::field('ordernumber')->table('ct_delivery_order')->where('id',$id)->find();
            $data['price'] = $zsprice;
            $data['ordernumber'] = $ordernumber['ordernumber'];
        }elseif ($type == 3){
            $ordernumber = Db::field('ordernumber')->table('ct_bulk_order')->where('id',$id)->find();
            $data['price'] = $zsprice;
            $data['ordernumber'] = $ordernumber['ordernumber'];
        }
        return json_encode($this->weixin_pay($type,$data));
    }

    public function weixin_pay($type,$data){
        require_once EXTEND_PATH.'wechatAppPay/weixin.php';
        switch ($type){
            case '1':
                $body = '整车信息费支付：'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $noturl = 'https://app.56cold.com/driver/pay/weixinnotify';
                break;
            case '2':
                $body = '城配信息费支付：'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $noturl = 'https://app.56cold.com/driver/pay/wxnotify';
                break;
            case '3':
                $body = '零担信息费支付：'.$data['ordernumber'];
                $out_trade_no = $data['ordernumber'];
                $price = $data['price'];
                $noturl = 'https://app.56cold.com/driver/pay/bulkwxnotify';
                break;
        }

        // appid
        $appid = 'wx1ed6b733675628df';
        // 商户号
        $mch_id = '1487413072';
        // 回调地址
        $notify_url = $noturl;
        // 商户支付密钥
        $key = '011eaf63e5f7944c6a979de570d44aaa';
        // 实例化微信app支付
        $wechatAppPay = new \wechatAppPay($appid,$mch_id,$notify_url,$key);
        // 商品描述
        $params['body'] = $body ;
        // 自定义订单号
        $params['out_trade_no'] = $out_trade_no;
        // 订单金额 只能为整数 单位为分
        $params['total_fee'] = $price*100;
        // 交易类型 JSAPI | NATIVE | APP | WAP
        $params['trade_type'] = 'APP';

        // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
        $result = $wechatAppPay->unifiedOrder($params);
        // 创建APP端预支付参数
        /** @var TYPE_NAME $result */
        $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
        // 根据上行取得的支付参数请求支付即可
        return $data;
    }

    public function multiToSingle($arr, $delimiter = '->',$key = ' ') {
        $resultAry = array();
        if (!(is_array($arr) && count($arr)>0)) {
            return false;
        }
        foreach ($arr AS $k=>$val) {
            $newKey = trim($key . $k . $delimiter);
            if (is_array($val) && count($val)>0) {
                $resultAry = array_merge($resultAry, $this->multiToSingle($val, $delimiter, $newKey));
            } else {
                $resultAry[] =  $val;
            }
        }
        return $resultAry;
    }
}