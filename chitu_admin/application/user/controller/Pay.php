<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型
//支付
class Pay  extends Base{
	
	/**
	 * @Auther: 李渊
	 * @Date: 2018.6.21
	 * app余额支付
	 * @param  [String] $token 		[令牌]
	 * @param  [String] $id    		[订单id]
	 * @param  [String] $type  		[订单类型] 1零担 2城配送 3 整车 4 定制
	 * @param  [String] $couponid 	[优惠券ID]
	 * @param  [String] $price 		[支付金额]
	 * @param  [String] $pay_type 	[付款类型] 1、原价支付2、面议，(暂时针对整车和市内)
	 * @return [type] [description]
	 */
	public  function  balance_shift(){
		// 令牌
		$token   = input("token");  
		// 订单ID
		$orderid = input('id');
		// 订单类型 1零担 2城配送 3整车 4定制 
		$type = input('type'); 
		// 优惠券ID
		$couponid = input('couponid');
		// 支付金额
		$order_money   = input('price'); 
		// 付款类型,1、原价支付2、面议，(暂时针对整车和市内)
		$pay_type = input('pay_type');
		// 验证令牌和参数
	    if(empty($token) || empty($orderid) || empty($type) || empty($order_money)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		// 查询下单用户信息
		$user_mes = Db::table("ct_user")->where('uid',$user_id)->find();
		// 获取用户余额
		$user_money = $user_mes['money'];  
		// 定义支付类型 余额支付
		$order_pay_type = '2';
		// 判断订单金额是否大于用户余额
		if($order_money > $user_money){
			return json(['code'=>'1001','message'=>'余额不足']);
			exit();
		}else{
			// 计算下单后的金额
			$finally_money = $user_money - $order_money;
			// 根据订单类型进行处理
			switch ($type) {
				case '1': // 零担
					$order_content  = "零担下单余额扣款";
					$blan_type = '1';
					// 查询零担订单信息
					$type_data = Db::table('ct_order')
								->alias('a')
								->join('ct_shift s','s.sid = a.shiftid')
								->join('ct_already_city l','l.city_id = s.linecityid')
								->field('a.paystate,a.ordernumber,l.start_id,l.end_id,s.companyid')
								->where('oid',$orderid)
								->find();
					// 判断订单状态如果是未支付订单则推送消息并且更新订单数据
					if ($type_data['paystate'] != '2') {
						//向承运商发送信息
						$this->send_note($typestate='1',$type_data['start_id'],$type_data['end_id'],$type_data['companyid']);
						Db::table('ct_order')->where('oid',$orderid)->update(array('orderstate'=>'2','paystate'=>'2','all_price'=>$order_money,'pay_type'=>$order_pay_type));				
					}else{
						return json(['code'=>'1018','message'=>'已支付']);
						exit;
					}
					break;
				case '2': // 城配
					$order_content  = "城配下单余额扣款";
					$blan_type = '3';
					// 查询城配订单信息
					$type_data = Db::table('ct_city_order')->where('id',$orderid)->find();
					// 判断订单状态如果是未支付订单则更新订单数据但是不推送消息
					if ($type_data['paystate'] != '2') {
						if ($pay_type == '2') {  // 信息费面议
							$where_up['paymoney'] = $order_money;
						}
						$where_up['pytype'] = $pay_type;
						$where_up['actualprice'] = $order_money;
						$where_up['paystate'] = 2;
						$where_up['pay_type'] = $order_pay_type;
						Db::table('ct_city_order')->where('id',$orderid)->update($where_up);
					}else{
						return json(['code'=>'1018','message'=>'已支付']);
						exit;
					}
					break;
				case '3': //整车
					$blan_type = '4';
					$order_content  = "整车下单余额扣款";
					$cityorder = Db::table("ct_userorder")->field('startcity,endcity,paystate')->where('uoid',$orderid)->find();
					if ($cityorder['paystate'] == '2') {
						return json(['code'=>'1018','message'=>'已支付']);
						exit;
					}
					if ($pay_type == '2') { //信息费面议
						$car_where['price'] = $order_money;
						$car_where['type'] = '2';
					}
					$car_where['paystate'] = '2';
					$car_where['referprice'] = $order_money;
					$car_where['pay_type'] = $order_pay_type;
					Db::table('ct_userorder')->where('uoid',$orderid)->update($car_where);
					break;
				case '4': // 定制
					$blan_type = '2';
					$order_content  = "定制线路下单余额扣款";
					$data['affirm'] = 2;
					$data['pay_type'] = $order_pay_type;
					$data['paystate'] = 2;
					$result = Db::table('ct_shift_order')->where('s_oid',$orderid)->update($data);
					break;
				default:
					# code...
					break;
			}
			//插入余额使用记录和更新余额
			$this->record($user_mes['integral'],$user_id,'',$order_money,$finally_money,$order_content,'2',$couponid,$orderid,$blan_type);
			return json(['code'=>'1002','message'=>'支付成功']);
		}
	}
	

	/**
	 * @Auther: 李渊
	 * @Date: 2018.6.21
	 * app信用额度支付
	 * 信用支付只有项目客户可以
	 * @param  [String] $token 		[令牌]
	 * @param  [String] $id    		[订单id]
	 * @param  [String] $type  		[订单类型] 1零担 2城配送 3 整车 4 定制
	 * @param  [String] $couponid 	[优惠券ID]
	 * @param  [String] $price 		[支付金额]
	 * @param  [String] $pay_type 	[付款类型] 1、原价支付2、面议，(暂时针对整车和市内)
	 * @return [type] [description]
	 */
	public function balance_credit(){
		// 令牌
		$token   = input("token");  
		// 订单ID
		$orderid = input('id'); 
		// 1零担 2市内配送  3整车支付 4定制线路
		$type = input('type'); 
		// 扣款金额
		$order_money   = input('price'); 
		// 优惠券ID
		$couponid = input('couponid');
		// 付款类型,1、原价支付2、面议，(暂时针对整车和市内)
		$pay_type = input('pay_type');
		
	    if(empty($token) || empty($orderid) || empty($order_money)){
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
		// 查询下单用户数据
		$user_mes = Db::table("ct_user")->where('uid',$user_id)->find();
		// 判断是否为项目客户
		if ($user_mes['lineclient'] =='') {
			return json(['code'=>'1022','message'=>'非信用额度支付客户']);
		}
		// 查询下单客户所在的公司
		$com = Db::table("ct_company")->where('cid',$user_mes['lineclient'])->find();
		// 查询公司信用值
		$user_money = $com['money'];
		// 查询公司id
		$companyid = $com['cid'];
		// 定义支付类型 信用支付
		$order_pay_type = '1';
		// 如果订单金额大于剩余信用额度
		if($order_money   >  $user_money){
			return json(['code'=>'1001','message'=>'信用余额不足']);
		}else{
			$finally_money = $user_money - $order_money;
			// 修改状态
			switch ($type) {
				case '1': //零担
					$blan_type='1';
					$order_content  = "零担下单信用额度扣款";
					$type_data = Db::table('ct_order')
									->alias('a')
									->join('ct_shift s','s.sid = a.shiftid')
									->join('ct_already_city l','l.city_id = s.linecityid')
									->field('a.paystate,a.ordernumber,l.start_id,l.end_id,s.companyid')
									->where('oid',$orderid)
									->find();
					if ($type_data['paystate'] != '2') {
						//向承运商发送信息
						$this->send_note($typestate='1',$type_data['start_id'],$type_data['end_id'],$type_data['companyid']);
						Db::table('ct_order')->where('oid',$orderid)->update(array('orderstate'=>'2','paystate'=>'2','all_price'=>$order_money,'pay_type'=>$order_pay_type));				
					}else{
						return json(['code'=>'1018','message'=>'已支付']);
						exit;
					}
					break;
				case '2': //城配
					$blan_type='3';
					$order_content  = "城配送下单信用额度扣款";
					$type_data = Db::table('ct_city_order')->where('id',$orderid)->find();
					if ($type_data['paystate'] != '2') {
						if ($pay_type == '2') {  //信息费面议
							//$where_up['pytype'] = 2;
							$where_up['paymoney'] = $order_money;
						}
						$where_up['pytype'] = $pay_type;
						$where_up['actualprice'] = $order_money;
						$where_up['paystate'] = 2;
						$where_up['pay_type'] = $order_pay_type;
						Db::table('ct_city_order')->where('id',$orderid)->update($where_up);
					}else{
						return json(['code'=>'1018','message'=>'已支付']);
						exit;
					}
					break;
				case '3': //整车
					$blan_type='4';
					$order_content  = "整车下单信用额度扣款";
					$cityorder = Db::table("ct_userorder")->field('startcity,endcity,paystate')->where('uoid',$orderid)->find();
					if ($cityorder['paystate'] == '2') {
						return json(['code'=>'1018','message'=>'已支付']);
						exit;
					}
					if ($pay_type == '2') { //信息费面议
						$car_where['price'] = $order_money;
						$car_where['type'] = '2';
					}
					$car_where['paystate'] = '2';
					$car_where['referprice'] = $order_money;
					$car_where['pay_type'] = $order_pay_type;
					Db::table('ct_userorder')->where('uoid',$orderid)->update($car_where);
					break;
				case '4': // 定制
					$blan_type = '2';
					$order_content  = "定制线路下单信用额度扣款";
					$data['affirm'] = 2;
					$data['pay_type'] = $order_pay_type;
					$data['paystate'] = 2;

					$result = Db::table('ct_shift_order')->where('s_oid',$orderid)->update($data);
					break;
				default:
					# code...
					break;
			}
			//信用额度使用记录和更新余额
			$this->record('',$user_id,$companyid,$order_money,$finally_money,$order_content,'2',$couponid,$orderid,$blan_type);
			return json(['code'=>'1002','message'=>'支付成功']);
		}
	}

	/**
	 * @Auther: 李渊
	 * @Date: 2018.6.21
	 * 支付宝订单支付
	 * @param  [String] $token 		[令牌]
	 * @param  [String] $id    		[订单id]
	 * @param  [String] $type  		[订单类型] 1零担 2城配送 3 整车 4 定制
	 * @param  [String] $couponid 	[优惠券ID]
	 * @param  [String] $price 		[支付金额]
	 * @param  [String] $pay_type 	[付款类型] 1、原价支付2、面议，(暂时针对整车和市内)
	 * @return [type] [description]
	 */
	public function city_With_pay(){
		// 令牌
		$token = input("token");  
		// 订单ID
		$id = input('id');
		// 产品支付类型 1 零担支付 2市内配送支付 3整车支付 4 定制线路
		$type = input('type');
		// 支付金额
		$zsprice = input('price');
		// 优惠券ID
		$couponid = input('couponid');

		$check_result = $this->check_token($token);//验证令牌
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		$data = array();
		// 判断订单类型
		switch ($type) {
			case '1':
				$ordernumber = Db::field('ordernumber')->table('ct_order')->where('oid',$id)->find();
				$data['price'] = $zsprice;
				$data['couponid'] = $couponid;
				$data['ordernumber'] = $ordernumber['ordernumber'];
				echo $this->ali_pay($type,$data);
				break;
			case '2':
				$ordernumber = Db::field('orderid')->table('ct_city_order')->where('id',$id)->find();
				$data['price'] = $zsprice;
				$data['couponid'] = $couponid;
				$data['ordernumber'] = $ordernumber['orderid'];
				echo $this->ali_pay($type,$data);
				break;
			case '3':
				$ordernumber = Db::field('ordernumber')->table('ct_userorder')->where('uoid',$id)->find();
				$data['price'] = $zsprice;
				$data['couponid'] = $couponid;
				$data['ordernumber'] = $ordernumber['ordernumber'];
				echo $this->ali_pay($type,$data);
				break;
			case '4':
				$ordernumber = Db::field('ordernumber')->table('ct_shift_order')->where('s_oid',$id)->find();
				$data['price'] = $zsprice;
				$data['couponid'] = $couponid;
				$data['ordernumber'] = $ordernumber['ordernumber'];
				echo $this->ali_pay($type,$data);
				break;
            case '5':
                $ordernumber = Db::field('ordernumber')->table('ct_userorder')->where('uoid',$id)->find();
                $data['price'] = $zsprice;
                $data['couponid'] = $couponid;
                $data['ordernumber'] = $ordernumber['ordernumber'];
                echo $this->ali_pay($type,$data);
                break;
			default:
				# code...
				break;
		}
	}
	/*
	 * 特价支付支付宝回调
	 * */
	public function tcnotifyurl(){
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
            $data['type'] = '1';
            $data['state'] = '1';
            $user_data = Db::field('a.*,b.startcity,b.endcity,b.uoid')->table('ct_user')->alias('a')->join('ct_userorder b','b.userid=a.uid')->where('b.ordernumber',$_POST['out_trade_no'])->find();
            $data['userid'] = $user_data['uid'];
            Db::table("ct_paymessage")->insert($data);
            Db::table('ct_userorder')->where('ordernumber',$_POST['out_trade_no'])->update(array('paystate'=>'2','referprice'=>$_POST['total_amount'],'pay_type'=>'3'));
            $content = '特价整车支付宝支付';
            //插入余额使用记录和更新余额
            $this->record($user_data['integral'],$user_data['uid'],'',$_POST['total_amount'],'',$content,'3',$_POST['passback_params'],$user_data['uoid'],'4');
            //执行推送
            // $this->send_note($typestate='2',$user_data['startcity'],$user_data['endcity'],'');
            echo 'success';
        }else{
            echo "fail";
        }
    }
	//信息费 支付宝支付 2019.5.22
	public function infopay(){
        // 令牌
        $token = input("token");
        // 订单ID
        $id = input('id');
        // 产品支付类型   1整车支付 2零担支付 3市内配送支付
        $type = input('type');
        // 支付金额
        $zsprice = input('price');
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        $data = array();

        switch ($type){
            case '1':
                $ordernumber = Db::field('ordernumber')->table('ct_useorder')->where('uoid',$id)->find();
                $data['price'] = $zsprice;
                $data['ordernumber'] = $ordernumber['ordernumber'];
                echo $this->aliypay($type,$data);
                break;
            case '2':
                $ordernumber = Db::field('ordernumber')->table('ct_order')->where('oid',$id)->find();
                $data['price'] = $zsprice;
                $data['ordernumber'] = $ordernumber['ordernumber'];
                echo $this->aliypay($type,$data);
                break;
            case '3':
                $ordernumber = Db::field('orderid')->table('ct_city_order')->where('id',$id)->find();
                $data['price'] = $zsprice;
                $data['ordernumber'] = $ordernumber['orderid'];
                echo $this->aliypay($type,$data);
                break;
            default:
                # code...
                break;
        }
    }

	//支付宝充值(崔玉龙添加)
	public function alipay(){
		$token   = input("token");  //令牌
		$price = input('price'); //充值金额
		$check_result = $this->check_token($token);//验证令牌
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		$user_data = Db::table('ct_user')->where('uid',$user_id)->find();
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
            'subject'=>'余额充值：'.$user_data['phone'],
            'out_trade_no'=>'C'.date('Ymdhis').mt_rand('000000','999999'),//此订单号为商户唯一订单号
            'total_amount'=>$price,//保留两位小数
            'product_code'=>'QUICK_MSECURITY_PAY',
            'passback_params'=>$user_id
        ]);
        $request->setNotifyUrl("https://app.56cold.com/user/pay/notifyurl");
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        echo $response;

	}
	//支付宝充值回调处理(崔玉龙添加)
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
			$data['type'] = '1';
			$data['state'] = '2';
			$user_data = Db::table('ct_user')->where('uid',$_POST['passback_params'])->find();
			//插入充值信息
			Db::table("ct_paymessage")->insert($data);
			//计算余额
			$mooney = $user_data['money']+$_POST['total_amount'];
			$content = "支付宝充值";
			$balan_id = Db::table("ct_paymessage")->getLastInsID();
			$blan_type = '5';
			//插入余额使用记录和更新余额
			$this->record($user_data['integral'],$user_data['uid'],'',$_POST['total_amount'],$mooney,$content,'1','',$balan_id,$blan_type);
			return 'success';
		}else{
			return "fail";
		}
	}
	//零担支付宝支付，回调接口
	public function ldnotifyurl(){
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
			$data['type'] = '1';
 			$data['state'] = '1';
			$user_data = Db::field('a.*,b.linepice,b.pickcost,b.delivecost,b.oid,l.start_id,l.end_id,s.companyid')
							->table('ct_user')
							->alias('a')
							->join('ct_order b','b.userid=a.uid')
							->join('ct_shift s','s.sid = b.shiftid')
							->join('ct_already_city l','l.city_id = s.linecityid')
							->where('b.ordernumber',$_POST['out_trade_no'])
							->find();
			$data['userid'] = $user_data['uid'];
			Db::table("ct_paymessage")->insert($data);
			$content = '零担支付宝支付';
			//插入余额使用记录和更新余额
			Db::table('ct_order')->where('ordernumber',$_POST['out_trade_no'])->update(array('orderstate'=>'2','paystate'=>'2','all_price'=>$_POST['total_amount'],'pay_type'=>'3'));
			$this->record($user_data['integral'],$user_data['uid'],'',$_POST['total_amount'],'',$content,'3',$_POST['passback_params'],$user_data['oid'],'1');
			//发送短信和推送消息
			$this->send_note($typestate='1',$user_data['start_id'],$user_data['end_id'],$user_data['companyid']);
			echo 'success';
		}else{
			echo "fail";
		}
	}
	//城配送支付完成 回调接口
	public function spnotifyurl(){
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
            $data['type'] = '1';
            $data['state'] = '1';
            $user_data = Db::field('a.*,b.pytype,b.actualprice,b.id,b.city_id')
                ->table('ct_user')
                ->alias('a')
                ->join('ct_city_order b','b.userid=a.uid')
                ->where('b.orderid',$_POST['out_trade_no'])
                ->find();
            $data['userid'] = $user_data['uid'];
            Db::table("ct_paymessage")->insert($data);
            $city_data['paystate'] = 2;
            $city_data['actualprice'] = $_POST['total_amount'];
            $city_data['pay_type'] = 3;
            Db::table('ct_city_order')->where('orderid',$_POST['out_trade_no'])->update($city_data);
//			Db::table('ct_city_order')->where('orderid',$_POST['out_trade_no'])->update(array('orderstatus'=>'2','paytype'=>'1'));
//			$content = '城配送支付宝支付';

//			$this->record($user_data['integral'],$user_data['uid'],'',$_POST['total_amount'],'',$content,'3',$_POST['passback_params'],$user_data['id'],'3');
			//推送消息
			// $this->send_note($typestate='3',$user_data['city_id'],'','');
			echo 'success';
		}else{
			echo "fail";
		}
	}
	//整车信息支付完成 回调接口
    public function zcontify(){
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
            $data['type'] = '1';
            $data['state'] = '1';
            $user_data = Db::field('a.*,b.startcity,b.endcity,b.uoid')->table('ct_user')->alias('a')->join('ct_useorder b','b.userid=a.uid')->where('b.ordernumber',$_POST['out_trade_no'])->find();
            $data['userid'] = $user_data['uid'];
            Db::table("ct_paymessage")->insert($data);
            Db::table('ct_useorder')->where('ordernumber',$_POST['out_trade_no'])->update(array('orderstatus'=>'2','paytype'=>'1'));
            $content = '整车信息支付宝支付';
            //插入余额使用记录和更新余额
             $this->record($user_data['integral'],$user_data['uid'],'',$_POST['total_amount'],'',$content,'3',$_POST['passback_params'],$user_data['uoid'],'4');
            //执行推送
            // $this->send_note($typestate='2',$user_data['startcity'],$user_data['endcity'],'');
            echo 'success';
        }else{
            echo "fail";
        }
    }

	//整车支付完成 回调接口 2017-8-4
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
            $data['payname'] = $_POST['buyer_logon_id'];
            $data['type'] = '1';
            $data['state'] = '1';
            $user_data = Db::field('a.*,b.startcity,b.endcity,b.uoid')->table('ct_user')->alias('a')->join('ct_userorder b','b.userid=a.uid')->where('b.ordernumber',$_POST['out_trade_no'])->find();
            $data['userid'] = $user_data['uid'];
            Db::table("ct_paymessage")->insert($data);
            Db::table('ct_userorder')->where('ordernumber',$_POST['out_trade_no'])->update(array('paystate'=>'2','referprice'=>$_POST['total_amount'],'pay_type'=>'3'));
            Db::table('ct_balance')->insert(array('pay_money'=>$_POST['total_amount'],'order_content'=>'整车订单支付宝支付','userid'=>$user_data['uid'],'ordertype'=>'4','action_type'=>'2','orderid'=>$_POST['out_trade_no'],'addtime'=>time()));
            $result =  DB::table('ct_district')->where(array('name'=>$user_data['startcity'],'level'=>'2'))->find();
            $result1 =  DB::table('ct_district')->where(array('name'=>$user_data['endcity'],'level'=>'2'))->find();
            $this->send_note($type_type='2',$result['id'],$result1['id'],'');
			echo 'success';
		}else{
			echo "fail";
		}
	}
	//定制支付完成 回调接口 2018-5-24
	public function dznotifyurl(){
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
			$data['type'] = '1';
 			$data['state'] = '1';
 			$user_data = Db::field('a.*,b.s_oid')->table('ct_user')->alias('a')->join('ct_shift_order b','b.userid=a.uid')->where('b.ordernumber',$_POST['out_trade_no'])->find();
			$data['userid'] = $user_data['uid'];
			Db::table("ct_paymessage")->insert($data);
			Db::table('ct_shift_order')->where('ordernumber',$_POST['out_trade_no'])->update(array('affirm'=>'2','paystate'=>'2','pay_type'=>'3'));
			$content = '定制线路支付宝支付';
			//插入余额使用记录和更新余额
			$this->record($user_data['integral'],$user_data['uid'],'',$_POST['total_amount'],'',$content,'3',$_POST['passback_params'],$user_data['s_oid'],'2');
			//发送信息
			$this->send_mess_shiftorder($user_data['s_oid']);
			echo 'success';
		}else{
			echo "fail";
		}
	}
	

	//支付宝提现
	public function withdraw(){
		$token   = input("token");  //令牌
		$money = input('money'); //提现金额
		$account = input('account'); //提现账号
		if(empty($token) || empty($money) || empty($account)){
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
		$result = Db::table('ct_user')->where('uid',$user_id)->find();
		if ($result['money'] < $money) {
			return json(['code'=>'1009','message'=>'提现额度不可大于账户余额']);
			exit;
		}
		$insert_data['action_id'] = $user_id;
		$insert_data['action_type'] = '2';
		$insert_data['money'] = $money;
		$insert_data['start_time'] = time();
		$insert_data['states'] = '1';
		$insert_data['account'] = $account;
		$insert_data['menu_type'] = 1;
		$insert_data['actual_money'] = $money;
		$umoney = $result['money']-$money;
		$re = Db::table('ct_user')->where('uid',$user_id)->update(['money'=>$umoney]);
		if ($re) {
			Db::table("ct_application")->insert($insert_data);
			$payid = Db::table("ct_application")->getLastInsID();
			$balance_data=array(
						'pay_money'=>$money,
						'order_content'=>"支付宝提现",
						'action_type'=>'2', //操作类型： 1 增加 2 扣除
						'orderid' => $payid,
						'ordertype' => '7',
						'userid'=>$user_id,
						'addtime'=>time()
			);
			Db::table('ct_balance')->insert($balance_data);
			return json(['code'=>'1002','message'=>'提现成功','money'=>$umoney]);
		}else{
			return json(['code'=>'1001','message'=>'操作失败']);
		}
	}
	//微信充值接口
  	public function weixinpay(){
	    require_once EXTEND_PATH.'wxAppPay/weixin.php';
	    $token   = input("token");  //令牌
		$price = input('price'); //充值金额
		$check_result = $this->check_token($token);//验证令牌
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		$user_data = Db::table('ct_user')->where('uid',$user_id)->find();
	    $appid = 'wxe2d6b74ba8fa43e7';
	    $mch_id = '1481595522';
	    $notify_url = 'https://app.56cold.com/user/pay/weixinnotifyurl';
	    $key = 'FdzK0xScm6GRS0zUW4LRYOak5rZA9k3o';
	    $wechatAppPay = new \wxAppPay($appid,$mch_id,$notify_url,$key);
	    $params['body'] = $user_data['phone'].'余额充值';                       //商品描述
	    $params['out_trade_no'] = 'C'.date('Ymdhis').mt_rand('000000','999999');    //自定义的订单号
	    $params['total_fee'] = $price*100;                       //订单金额 只能为整数 单位为分
	    $params['trade_type'] = 'APP';                      //交易类型 JSAPI | NATIVE | APP | WAP 
	    $params['attach'] = $user_id;                      //附加参数（用户ID） 
	    $result = $wechatAppPay->unifiedOrder($params);
	    // print_r($result); // result中就是返回的各种信息信息，成功的情况下也包含很重要的prepay_id
	    //2.创建APP端预支付参数
	    /** @var TYPE_NAME $result */
	    $data = @$wechatAppPay->getAppPayParams($result['prepay_id']);
	    // 根据上行取得的支付参数请求支付即可
	    return json_encode($data);
  	}

  	
  	//微信支付接口
  	public function weixinpayment(){
  		$token  = input("token");  //令牌
		$id = input('id');//订单ID
		$type = input('type');//产品支付类型 1 零担支付 2市内配送支付 3整车 4 定制线路
		$zsprice = input('price');//支付金额
//		$couponid = input('couponid');//优惠券ID
		$pay_type = input('pay_type');   //付款类型,1、原价支付2、面议，(暂时针对整车)
		$data = array();
		$check_result = $this->check_token($token);//验证令牌
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		if ($type == '1') {
			$ordernumber = Db::field('ordernumber')->table('ct_order')->where('oid',$id)->find();
			$data['price'] = $zsprice;
            $data['couponid'] = $user_id;
			$data['ordernumber'] = $ordernumber['ordernumber'];
		}elseif($type == '2'){
			$up_data['ordernumber'] = 'SP'.date('Ymdhis').mt_rand('000000','999999'); // 订单编号
            DB::table('ct_city_order')->where('uoid',$id)->update($up_data);
			$ordernumber = Db::field('ordernumber')->table('ct_city_order')->where('uoid',$id)->find();
			$data['price'] = $zsprice;
//			$data['couponid'] = $couponid;
			$data['ordernumber'] = $ordernumber['ordernumber'];
		}elseif($type == '3'){
            $car_where['ordernumber'] = 'K'.date('Ymdhis').mt_rand('000000','999999'); //订单编号
            $data['couponid'] = $user_id;
            Db::table('ct_userorder')->where('uoid',$id)->update($car_where);
            $ordernumber = Db::field('ordernumber')->table('ct_userorder')->where('uoid',$id)->find();
            $data['couponid'] = $user_id;
            $data['price'] = $zsprice;
            $data['ordernumber'] = $ordernumber['ordernumber'];
		}elseif ($type == '4') {
			$ordernumber = Db::field('ordernumber')->table('ct_shift_order')->where('s_oid',$id)->find();
			$data['price'] = $zsprice;
//			$data['couponid'] = $couponid;
			$data['ordernumber'] = $ordernumber['ordernumber'];
		}elseif($type == '5'){
            $ordernumber = Db::field('ordernumber')->table('ct_userorder')->where('uoid',$id)->find();
            $data['price'] = $zsprice;
            $data['couponid'] = $user_id;
            $data['ordernumber'] = $ordernumber['ordernumber'];
        }

		return json_encode($this->weixin_pay($type,$data));
  	}
  	//整车信息微信支付接口 2019.5.24
    public function zcwxpay(){
        $token   = input("token");  //令牌
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
            $user_id = $check_result['user_id'];
        }
            $car_where['ordernumber'] = 'K'.date('Ymdhis').mt_rand('000000','999999'); //订单编号

            Db::table('ct_useorder')->where('uoid',$id)->update($car_where);
            $ordernumber = Db::field('ordernumber')->table('ct_useorder')->where('uoid',$id)->find();
            $data['price'] = $zsprice;
            $data['ordernumber'] = $ordernumber['ordernumber'];

        return json_encode($this->wx_pay($type,$data));
    }

  	//零担微信支付异步通知
  	public function wxpaymenturl(){
  		ini_set('date.timezone','Asia/Shanghai');  
	    error_reporting(E_ERROR); 
	    $result = file_get_contents('php://input', 'r');
	    file_put_contents('wxpay.txt', $result);
	    $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	    if ($array_data['return_code'] == 'SUCCESS') {
		    $data['orderid'] = $array_data['out_trade_no'];
		    $data['paytype'] = '2';
		    $data['platformorderid'] = $array_data['transaction_id'];
		    $pay_count = $array_data['total_fee']/100;
		    $data['paynum'] = $pay_count;
		    $data['paytime'] = time();
		    $data['payname'] = $array_data['openid'];
		    $data['type'] = '1';
		    $data['state'] = '1';
		    $user_data = Db::field('a.*,l.start_id,l.end_id,s.companyid,b.oid')
		    			->table('ct_user')
		    			->alias('a')
		    			->join('ct_order b','b.userid=a.uid')
						->join('ct_shift s','s.sid = b.shiftid')
						->join('ct_already_city l','l.city_id = s.linecityid')
		    			->where('b.ordernumber',$array_data['out_trade_no'])
		    			->find();
		    $data['userid'] = $user_data['uid'];
			Db::table("ct_paymessage")->insert($data);
			Db::table('ct_order')->where('ordernumber',$array_data['out_trade_no'])->update(array('orderstate'=>'2','paystate'=>'2','all_price'=>$pay_count,'pay_type'=>'4'));
			$content = "零担微信支付";
			//插入余额使用记录和更新余额
			$this->record($user_data['integral'],$user_data['uid'],'',$pay_count,'',$content,'4',$array_data['attach'],$user_data['oid'],'1');
	    	//推送消息
			$this->send_note($typestate='1',$user_data['start_id'],$user_data['end_id'],$user_data['companyid']);
	    	echo 'success';
	    }else{
	        echo "fail";
	    }
  	}
  	//市内配送微信支付异步通知
  	public function spwxpaymenturl(){
  		ini_set('date.timezone','Asia/Shanghai');  
	    error_reporting(E_ERROR); 
	    $result = file_get_contents('php://input', 'r');
	    file_put_contents('wxpay.txt', $result);
	    $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	    if ($array_data['return_code'] == 'SUCCESS') {
		    $data['orderid'] = $array_data['out_trade_no'];
		    $data['paytype'] = '2';
		    $data['platformorderid'] = $array_data['transaction_id'];
		    $city_pay = $array_data['total_fee']/100;
		    $data['paynum'] = $city_pay;
		    $data['paytime'] = time();
		    $data['payname'] = $array_data['openid'];
		    $data['type'] = '1';
		    $data['state'] = '1';
		    $user_data = Db::field('a.*,b.paytype,b.price,b.uoid,b.startcity')
		    				->table('ct_user')
		    				->alias('a')
		    				->join('ct_city_order b','b.userid=a.uid')
		    				->where('b.ordernumber',$array_data['out_trade_no'])->find();
		    $data['userid'] = $user_data['uid'];
			Db::table("ct_paymessage")->insert($data);
			
			//推送消息
//			$this->send_note($typestate='3',$user_data['startcity'],'','');
			$city_data['orderstatus'] = 2;
			$city_data['paytype'] = 2;
			$city_data['paystate'] = 1;
			Db::table('ct_city_order')->where('ordernumber',$array_data['out_trade_no'])->update(array('orderstatus'=>'2','paytype'=>'2','paystate'=>'1'));
//			$content = "城配微信支付";
			//插入余额使用记录和更新余额
//			$this->record($user_data['integral'],$user_data['uid'],'',$city_pay,'',$content,'4',$array_data['attach'],$user_data['id'],'3');
	    	echo 'success';
	    }else{
	        echo "fail";
	    }
  	}
	//微信充值异步通知
	public function weixinnotifyurl(){
	    ini_set('date.timezone','Asia/Shanghai');  
	    error_reporting(E_ERROR); 
	    $result = file_get_contents('php://input', 'r');
	    file_put_contents('wxpay.txt', $result);
	    $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	    if ($array_data['return_code'] == 'SUCCESS') {
		    $data['orderid'] = $array_data['out_trade_no'];
		    $data['paytype'] = '2';
		    $data['platformorderid'] = $array_data['transaction_id'];
		    $pay = $array_data['total_fee']/100;
		    $data['paynum'] = $pay;
		    $data['paytime'] = time();
		    $data['userid'] = $array_data['attach'];
		    $data['payname'] = $array_data['openid'];
		    $data['type'] = '1';
		    $data['state'] = '2';
		    $user_data = Db::table('ct_user')->where('uid',$array_data['attach'])->find();
		    //c插入充值记录
			Db::table("ct_paymessage")->insert($data);
			$mooney = $user_data['money']+$pay;
			$content = "微信充值";
			$balan_id = Db::table("ct_paymessage")->getLastInsID();

			//插入余额使用记录和更新余额
			$this->record($user_data['integral'],$user_data['uid'],'',$pay,$mooney,$content,'1','',$balan_id,'6');
	    	echo 'success';
	    }else{
	        echo "fail";
	    }
	}
	/*
	*整车微信异步通知
	*/
	public function cityweixinnotifyurl(){
	    ini_set('date.timezone','Asia/Shanghai');  
	    error_reporting(E_ERROR); 
	    $result = file_get_contents('php://input', 'r');
	    file_put_contents('wxpay.txt', $result);
	    $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	    if ($array_data['return_code'] == 'SUCCESS') {
            $data['orderid'] = $array_data['out_trade_no'];
            $data['paytype'] = '2';  //微信
            $data['platformorderid'] = $array_data['transaction_id'];
            $allcar = $array_data['total_fee']/100;
            $data['paynum'] = $allcar;
            $data['paytime'] = time();
            $data['payname'] = $array_data['openid'];
            $data['type'] = '1';  //货主
            $data['state'] = '1'; //支付
            $user_data = Db::field('a.*,b.startcity,b.endcity,b.uoid')
                ->table('ct_user')
                ->alias('a')
                ->join('ct_userorder b','b.userid=a.uid')
                ->where('b.ordernumber',$array_data['out_trade_no'])
                ->find();
            $data['userid'] = $user_data['uid'];
            Db::table("ct_paymessage")->insert($data);
            Db::table('ct_userorder')->where('ordernumber',$array_data['out_trade_no'])->update(array('paystate'=>'2','referprice'=>$allcar,'pay_type'=>'4'));
            $result =  DB::table('ct_district')->where(array('name'=>$user_data['startcity'],'level'=>'2'))->find();
            $result1 =  DB::table('ct_district')->where(array('name'=>$user_data['endcity'],'level'=>'2'))->find();
            $this->send_note($type_type='2',$result['id'],$result1['id'],'');
            Db::table('ct_balance')->insert(array('pay_money'=>$allcar,'order_content'=>'整车订单微信支付','orderid'=>$array_data['out_trade_no'],'ordertype'=>'4','action_type'=>'2','userid'=>$user_data['uid'],'addtime'=>time()));
	    	echo 'success';
	    }else{
	        echo 'fail';
	    }
	}
	/*
	 * 特价线路微信支付回调
	 * */
	public function wxnotifyurl(){
        ini_set('date.timezone','Asia/Shanghai');
        error_reporting(E_ERROR);
        $result = file_get_contents('php://input', 'r');
        file_put_contents('wxpay.txt', $result);
        $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($array_data['return_code'] == 'SUCCESS') {
            $data['orderid'] = $array_data['out_trade_no'];
            $data['paytype'] = '2';  //微信
            $data['platformorderid'] = $array_data['transaction_id'];
            $allcar = $array_data['total_fee']/100;
            $data['paynum'] = $allcar;
            $data['paytime'] = time();
            $data['userid'] = $array_data['attach'];
            $data['payname'] = $array_data['openid'];
            $data['type'] = '1';  //货主
            $data['state'] = '1'; //支付
            $user_data = Db::field('a.*,b.startcity,b.endcity,b.uoid')
                ->table('ct_user')
                ->alias('a')
                ->join('ct_userorder b','b.userid=a.uid')
                ->where('b.ordernumber',$array_data['out_trade_no'])
                ->find();
            Db::table("ct_paymessage")->insert($data);
            Db::table('ct_userorder')->where('ordernumber',$array_data['out_trade_no'])->update(array('paystate'=>'2','referprice'=>$allcar,'pay_type'=>'4'));
            $content = "特价线路微信支付";
            //插入余额使用记录和更新余额
            $this->record($user_data['integral'],$user_data['uid'],'',$allcar,'',$content,'4',$array_data['attach'],$user_data['uoid'],'4');
            //推送信息
            // $this->send_note($typestate='2',$user_data['startcity'],$user_data['endcity'],'');
            echo 'success';
        }else{
            echo 'fail';
        }
    }
    //整车微信支付异步回调 2019.5.24 （暂不用）
    public function zcwxnotify(){
        ini_set('date.timezone','Asia/Shanghai');
        error_reporting(E_ERROR);
        $result = file_get_contents('php://input', 'r');
        file_put_contents('wxpay.txt', $result);
        $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        if ($array_data['return_code'] == 'SUCCESS') {
            $data['orderid'] = $array_data['out_trade_no'];
            $data['paytype'] = '2';  //微信
            $data['platformorderid'] = $array_data['transaction_id'];
            $allcar = $array_data['total_fee']/100;
            $data['paynum'] = $allcar;
            $data['paytime'] = time();
            $data['payname'] = $array_data['openid'];
            $data['type'] = '1';  //货主
            $data['state'] = '1'; //支付
            $user_data = Db::field('a.*,b.startcity,b.endcity,b.uoid')
                ->table('ct_user')
                ->alias('a')
                ->join('ct_useorder b','b.userid=a.uid')
                ->where('b.ordernumber',$array_data['out_trade_no'])
                ->find();
            $data['userid'] = $user_data['uid'];
            Db::table("ct_paymessage")->insert($data);
            Db::table('ct_useorder')->where('ordernumber',$array_data['out_trade_no'])->update(array('paystate'=>'2','pay_type'=>'2'));
//            $content = "整车微信支付";
            //插入余额使用记录和更新余额
//            $this->record($user_data['integral'],$user_data['uid'],'',$allcar,'',$content,'4',$array_data['attach'],$user_data['uoid'],'4');
            //推送信息
            // $this->send_note($typestate='2',$user_data['startcity'],$user_data['endcity'],'');
            echo 'success';
        }else{
            echo 'fail';
        }
    }
	/*
	*定制线路异步通知
	*/
	public function dzwxpaymenturl(){
	    ini_set('date.timezone','Asia/Shanghai');  
	    error_reporting(E_ERROR); 
	    $result = file_get_contents('php://input', 'r');
	    file_put_contents('wxpay.txt', $result);
	    $array_data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
	    if ($array_data['return_code'] == 'SUCCESS') {
		    $data['orderid'] = $array_data['out_trade_no'];
		    $data['paytype'] = '2';  //微信
		    $data['platformorderid'] = $array_data['transaction_id'];
		    $allcar = $array_data['total_fee']/100;
		    $data['paynum'] = $allcar;
		    $data['paytime'] = time();
		    $data['payname'] = $array_data['openid'];
		    $data['type'] = '1';  //货主
		    $data['state'] = '1'; //支付
		    $user_data = Db::field('a.*,b.s_oid')
		    				->table('ct_user')
		    				->alias('a')
		    				->join('ct_shift_order b','b.userid=a.uid')
		    				->where('b.ordernumber',$array_data['out_trade_no'])
		    				->find();
		    $data['userid'] = $user_data['uid'];
			Db::table("ct_paymessage")->insert($data);
			Db::table('ct_shift_order')->where('ordernumber',$array_data['out_trade_no'])->update(array('paystate'=>'2','affirm'=>'2','pay_type'=>'4'));
			$content = "定制线路微信支付";
			//插入余额使用记录和更新余额
			$this->record($user_data['integral'],$user_data['uid'],'',$allcar,'',$content,'4',$array_data['attach'],$user_data['s_oid'],'2');
			//发送信息
			$this->send_mess_shiftorder($user_data['s_oid']);
	    	echo 'success';
	    }else{
	        echo 'fail';
	    }
	}


	
}