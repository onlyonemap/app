<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Ucenter extends Base{

    /**
     * 根据手机号获取司机的地理位置
     * 如果没有手机号则显示所有司机的位置信息
     * @param [Number] phone 需要获取该司机位置的手机号
     * @return [Array] data 经纬度信息
     */
    public function user_location_mess(){
    	// 获取该手机号
        $phone = input('phone');  
        // 如果有手机号则获取该手机号的位置信息 否则获取所有的信息
        if ($phone!='') {
        	$result = Db::table('ct_user_location')
        			->alias('a')
        			->join('ct_driver d','d.drivid = a.userid')
        			->field('a.*')
        			->where(array('d.mobile'=>$phone))
        			->find();
        }else{
        	$result = Db::table('ct_user_location')
        			->alias('a')
        			->join('ct_driver d','d.drivid = a.userid')
        			->field('a.*')
        			->select();
        }
        
        if($result){
            return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
        }else{
        	return json(['code'=>'1001','message'=>'暂无数据']);
        }
    }

   	/**
   	 * 订单用户反馈
   	 * @return [type] [description]
   	 */
    public function user_contact(){
    	$token  = input('token'); //令牌
    	$data['message'] = $message = input('message');//信息
    	$data['orderid'] = $orderid = input('orderid');  //订单ID
    	$data['otype'] = $otype = input('otype');  // 订单类型1零担，2定制、3城配 4整车
    	if(empty($token) || empty($orderid) || empty($otype)){
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
		$data['userid'] = $user_id;
		$data['addtime'] = time();
		$data['utype'] = 1;
		$result = Db::table('ct_order_contact')->insert($data);
		if ($result) {
			 return json(['code'=>'1002','message'=>'提交成功']);
        }else{
        	return json(['code'=>'1001','message'=>'提交失败']);
        }
    }

	//密码修改
	public function pswd_edite(){
		$token   = input("token");  //令牌
		$old_password   = input("old_password");  //原始密码
		$new_password   = input("new_password");  //新密码
		$confirm_password   = input("confirm_password");  //确认新密码
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
		$user_where['uid'] = $user_id;
		$user_where['password'] = MD5($old_password."ct888");
		$user_mes = Db::table("ct_user")->where($user_where)->find();
		if(!empty($user_mes)){
			if($new_password  == $confirm_password){
					$upda['password'] = MD5($confirm_password."ct888");
					$upda_res=Db::table("ct_user")->where('uid',$user_id)->update($upda);
					if($upda_res){
						return json(['code'=>'1001','message'=>'修改成功']);
					}else{
						return json(['code'=>'1002','message'=>'修改失败']);
						
					}
			}else{
				return json(['code'=>'1003','message'=>'两个新密码不一致']);
			}
		}else{
			return json(['code'=>'1004','message'=>'原始密码不正确']);
		}
	}

	//忘记密码
	public  function pswd_froget(){
	    $phone = input('phone');
        $yzm_code = input('yzm_code');
        $password = input('password');
        $re_password = input('re_password');
        if(empty($phone)  ||  empty($yzm_code)  || empty($password)  || empty($re_password)){
        	 return json(['code'=>'1000','message'=>'参数错误']);
        }
        //验证手机号是否存在
        $if_exf = Db::table("ct_user")->where("phone = $phone")->find();  
        if(empty($if_exf)){
            return json(['code'=>'1007','message'=>'用户不存在']);
        }  
        //获取发送的验证码
        $record = Db::table("ct_validate_record")->where("phone = $phone")->find();
        // 检查发送短信验证码的手机号码和提交的手机号码是否匹配
        if(iconv_strlen($yzm_code) > 4){
        	return json(['code'=>'1001','message'=>'验证码不能超过四位数字']);
        }else if($record['yzm'] != $yzm_code){
        	return json(['code'=>'1002','message'=>'验证码不正确！']);
            // 检查过期时间
        }else if($record['expired_time'] < time()){
        	return json(['code'=>'1003','message'=>'验证码已过期！']);
            //检查两次输入的密码是否相同
        }elseif($password != $re_password){
        	return json(['code'=>'1004','message'=>'两次输入密码不一致']);
        }

        $upda['password'] = MD5($re_password."ct888");
        
		$upda_res=Db::table("ct_user")->where('phone',$phone)->update($upda);

		if($upda_res){
			$this->delete_yzm($phone); //删除验证码记录
			return json(['code'=>'1005','message'=>'找回成功']);
		}else{
			return json(['code'=>'1006','message'=>'找回失败']);
		}
	}

	//手机号更换
	public  function phone_edite(){
	    $new_phone = input('new_phone');
        $yzm_code = input('yzm_code');
        $token = input('token'); 
        if(empty($new_phone)  ||  empty($yzm_code)  || empty($token)){
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
        //验证手机号是否存在
        $if_exf = Db::table("ct_user")->where("phone = $new_phone")->find();  
        if($if_exf){
            return json(['code'=>'1004','message'=>'手机号已存在']);
        }  
        //获取发送的验证码
        $record = Db::table("ct_validate_record")->where("phone = $new_phone")->find();
        // 检查发送短信验证码的手机号码和提交的手机号码是否匹配
        if(iconv_strlen($yzm_code) > 4){
        	return json(['code'=>'1001','message'=>'验证码不能超过四位数字']);
        }else if($record['yzm'] != $yzm_code){
        	return json(['code'=>'1002','message'=>'验证码不正确！']);
            // 检查过期时间
        }else if($record['expired_time'] < time()){
        	return json(['code'=>'1003','message'=>'验证码已过期！']);
        }

        $upda['phone'] = $new_phone;
		$upda_res=Db::table("ct_user")->where('uid',$user_id)->update($upda);
		if($upda_res){
			  $this->delete_yzm($new_phone); //删除验证码记录
			return json(['code'=>'1005','message'=>'更换成功']);
		}else{
			return json(['code'=>'1006','message'=>'更换失败']);
		}

	}

	//意见反馈
	public function feedback(){
		$token = input('token'); 
		$content = input('content'); 
        if(empty($token)  ||  empty($content)){
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

		$insert_data = array(
			'type'=>'1', //类型： 1用户端2司机端
			'action_id'=>$user_id,
			'content'=>$content,
			'status'=>'1', //状态1未解决2解决
			'add_time'=>time()
		);
		$result = Db::table("ct_feedback")->insert($insert_data);

        if($result){
            return json(['code'=>'1001','message'=>'提交成功']);
        }else{
        	return json(['code'=>'1002','message'=>'提交失败']);
        }

	}

	//我的钱包
	public function my_money(){
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

		$user_mes = Db::table("ct_user")->where('uid',$user_id)->find();
		$result['money'] = $user_mes['money']; 
		return json(['code'=>'1001','message'=>'查询成功','data'=>$result]);	
	}

	/*
	*公司钱包
	*/
	public function company_money(){
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

		$user_mes = Db::table("ct_user")->where('uid',$user_id)->find();

		if($user_mes['userstate'] == 1){ // 注册客户
			$result['money'] = 0;
			$result['credit'] = 0; 
			$result['company'] = '个体用户';  
		}else{ // 项目客户
			$com = Db::table("ct_company")->where('cid',$user_mes['lineclient'])->find();
			$result['money'] = $com['money'];
			$result['credit'] = $com['credit']; 
			$result['company'] = $com['name']; 
		}

		return json(['code'=>'1001','message'=>'查询成功','data'=>$result]);	
	}

	/**
	 * 交易明细
	 * @auther: 李渊
	 * @date: 2018.8.30
	 * @param [String] [token] [用户令牌]
	 * @return [type] [description]
	 */
	public function balance_list()
	{
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

		// 查找交易记录
		$result = Db::table("ct_balance")->where('userid',$user_id)->order('blid','desc')->paginate(10);
		// 转数组
		$list_mes = $result->toArray();
		// 获取数据
		$list = $list_mes['data'];
		// 判断是否为空
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}
	}

	/**
	 * 交易明细详情
	 * @auther: 李渊
	 * @date: 2018.8.30
	 * @param [String] 	[token] [用户令牌]
	 * @param [Int] 	[id] 	[交易的id]
	 * @return [type] [description]
	 */
	public function  balance_list_detail()
	{
		$token = input("token"); // 令牌
		$id = input("id");  // 交易id

		if(empty($token) || empty($id)){
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

		// 查询数据
		$result = Db::table("ct_balance")->where('blid',$id)->find();
		// 获取交易的订单id
		$orderid = $result['orderid'];
		// 根据交易类型进行判断获取交易明细
		switch ($result['ordertype']) {
			case 1: // 零担
				$order = Db::table('ct_order')->where('oid',$orderid)->find();
				$pay_detail = $this->balance_mess($order['paystate'],$order['pay_type']);
				$pay_mess = $pay_detail['pay_mess'];
				$paytype = $pay_detail['paytype'];
				$order_mess = "冷链零担";
				$order_number = $order['ordernumber'];
				break;
			case 2: // 定制
				$order = Db::table('ct_shift_order')->where('s_oid',$orderid)->find();
				$pay_detail = $this->balance_mess($order['affirm'],$order['pay_type']);
				$pay_mess = $pay_detail['pay_mess'];
				$paytype = $pay_detail['paytype'];
				$order_mess = "冷链定制";
				$order_number = $order['ordernumber'];
				break;
			case 3: // 城配
				$order = Db::table('ct_city_order')->where('id',$orderid)->find();
				$pay_detail = $this->balance_mess($order['paystate'],$order['pay_type']);
				$pay_mess = $pay_detail['pay_mess'];
				$paytype = $pay_detail['paytype'];
				$order_mess = "冷链城配";
				$order_number = $order['orderid'];
				break;
			case 4: // 整车
				$order = Db::table('ct_userorder')->where('uoid',$orderid)->find();
				$pay_detail = $this->balance_mess($order['paystate'],$order['pay_type']);
				$pay_mess = $pay_detail['pay_mess'];
				$paytype = $pay_detail['paytype'];
				$order_mess = "冷链整车";
				$order_number = $order['ordernumber'];
				break;
			case 5: // 支付宝充值
				$order = Db::table('ct_paymessage')->where('pid',$orderid)->find();
				$pay_mess = '充值成功';
				$paytype = '支付宝';
				$order_mess = "余额充值";
				$order_number = $order['orderid'];
				break;
			case 6: // 微信充值
				$order = Db::table('ct_paymessage')->where('pid',$orderid)->find();
				$pay_mess = '充值成功';
				$paytype = '微信';
				$order_mess = "余额充值";
				$order_number = $order['orderid'];
				break;
			case 7: // 余额提现
				$order = Db::table('ct_application')->where('id',$orderid)->find();
				$pay_mess = $this->withdraw_status($order['states']);
				$paytype = '提现';
				$order_mess = "余额提现";
				$order_number = $order['alipaynumber'];
				break;
			default:
				# code...
				break;
		}
		
		$result['order_mess'] = $order_mess; // 商品说明	
		$result['order_number'] = $order_number; // 订单编号	
		$result['pay_mess'] = $pay_mess; // 当前支付状态
		$result['pay_type'] = $paytype; // 当前支付方式
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
	}

	/**
	 * 修改真实姓名
	 * @Auther: 李渊
	 * @Date: 2018.6.25
	 * @param  [type] $token 	[用户令牌]
	 * @param  [type] $realname [真实姓名]
	 * @return [type]        [description]
	 */
	public function  update_realname() 
	{
		// 用户令牌
		$token = input("token");
		// 真实姓名
		$realname = input("realname");
		if (empty($token) || empty($realname)) {
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
		// 更新用户信息
		$result = Db::table('ct_user')->where('uid',$user_id)->update(["realname"=>$realname]);
		if($result){
            return json(['code'=>'1001','message'=>'修改成功']);
        }else{
        	return json(['code'=>'1002','message'=>'修改失败']);
        }
	}

	/**
	 * 修改性别
	 * @Auther: 李渊
	 * @Date: 2018.8.2
	 * @param  [type] $token 	[用户令牌]
	 * @param  [type] $sex 		[性别 1 男 2 女 ]
	 * @return [type]        [description]
	 */
	public function update_sex() 
	{
		// 用户令牌
		$token = input("token");
		// 真实姓名
		$sex = input("sex");
		if (empty($token) || empty($sex)) {
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
		// 更新用户信息
		$result = Db::table('ct_user')->where('uid',$user_id)->update(["sex"=>$sex]);
		if($result){
            return json(['code'=>'1001','message'=>'提交成功']);
        }else{
        	return json(['code'=>'1002','message'=>'提交失败']);
        }
	}

	/**
	 * 更新用户名
	 * @Auther: 李渊
	 * @Date: 2018.8.9
	 * 暂时还没用
	 * @param  [type] $token 	[用户令牌]
	 * @param  [type] $username [用户名]
	 * @return [type] [description]
	 */
	public function update_username()
	{
		// 用户令牌
		$token = input("token");
		// 用户名
		$username = input('username');
		// 判断参数
		if (empty($token) || empty($username)) {
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 验证令牌
		$check_result = $this->check_token($token);
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		// 更新数据
		$update['username'] = $username;
		// 更新用户名
        $result = Db::table('ct_user')->where('uid',$user_id)->update($update);
        // 判断更新结果
        if($result){
            return json(['code'=>'1001','message'=>'你的用户名已经修改成功啦']);
        }else{
        	return json(['code'=>'1002','message'=>'你的用户名已经修改失败了！']);
        }
	}
	/**
	 * 修改头像
	 * @return [type] [description]
	 */
	public function  my_avatar(){
		$token = input("token");
		if (empty($token)) {
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
		$find_user = Db::table('ct_user')->field('image')->where('uid',$user_id)->find();
		$delpath ='../public'.$find_user['image'];
		
		if(!empty($_FILES['picture']['tmp_name'])){
        	//回单2
			$re_2 = $this->file_upload('picture','jpg,gif,png,jpeg',"avatar");
	        $data['image'] = $re_2['file_path']; //源文件地址
	        if(file_exists($delpath)){
				@unlink($delpath);
			}
        }

        $result = Db::table('ct_user')->where('uid',$user_id)->update($data);
        $find = Db::table('ct_user')->field('image')->where('uid',$user_id)->find();
        if ($find['image'] !='') {
        	$url = get_url().$find['image'];
        }
        $list['image'] = $url;
        if($result){
            return json(['code'=>'1001','message'=>'提交成功','data'=>$list]);
        }else{
        	return json(['code'=>'1002','message'=>'提交失败']);
        }

	}
	//资金明细
	public function funds_list(){
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
		$result = Db::table('ct_paymessage')->where('userid',$user_id)->order('pid','desc')->paginate(10);
		$list_mes = $result->toArray();
		$list = $list_mes['data'];
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}
	}
    /*
    * 个人中心整车进行中列表
    * */
    public function vehicle_proceed(){
        $token   = input("token");  //令牌
        // 验证令牌
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
        // 查询条件 除未支付的订单
        $condition['paystate'] = ['NEQ','1'];
        // 查询条件 用户的id
        $condition['userid'] = $user_id;
        // 查询整车订单
        $result = Db::table("ct_userorder")
            ->alias('o')
            ->join('__CARTYPE__ c','o.carid = c.car_id','left')
            ->field('o.*,c.carparame')
            ->order('addtim e desc')
            ->where($condition)
            ->paginate(10);
        $list_mes = $result->toArray();
        $list = $list_mes['data'];
        foreach ($list as $key => $value) {

            // 订单运费 没有折扣价显示订单运费
            $price = $value['user_discount'] =='' ? $value['actual_payment'] : $value['user_discount'];
            // 订单运费 没有支付显示订单运费否则显示支付运费
            $price = $value['referprice'] =='' ? $price : $value['referprice'];
            // 订单运费 有修改过运费则显示修改过用费
            $price = $value['upprice'] =='' ? $price : $value['upprice'];

            $list[$key]['startcity'] =  $value['startcity']; // 起点城市
            $list[$key]['endcity'] = $value['endcity']; // 终点城市
            $list[$key]['type'] = $value['type'];  // 下单类型 1现金 2面议
            $list[$key]['price'] = round($price);
            $list[$key]['referprice'] = round($value['referprice']);
            $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';  //起点城市
            $list[$key]['addtime'] = $value['addtime']; //下单时间
            $list[$key]['temperture'] = $value['temperture']; //温度
            $list[$key]['arrivetime'] = $value['arrivetime']; //司机确认送达时间
            $list[$key]['carnum'] = $value['carnum'];//车辆
            $list[$key]['pickaddress'] = json_decode($value['pickaddress']);
            $list[$key]['sendaddress'] = json_decode($value['sendaddress']);
            if ($value['orderstate']== 1 && $value['loaddate']/1000 <time()) {
                $list[$key]['orderstate'] = 7;
            }
        }
        if(empty($list)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
        }
    }
    /*
     * 个人中心整车已完成列表
     * */
    public function vehicle_done(){
        $token   = input("token");  //令牌
        // 验证令牌
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
        // 查询条件 除未支付的订单
        $condition['paystate'] = ['NEQ','1'];
        // 查询条件 用户的id
        $condition['userid'] = $user_id;
        $condition['orderstate']  ='3';
        // 查询整车订单
        $result = Db::table("ct_userorder")
            ->alias('o')
            ->join('__CARTYPE__ c','o.carid = c.car_id','left')
            ->field('o.*,c.carparame')
            ->order('addtime desc')
            ->where($condition)
            ->paginate(10);
        $list_mes = $result->toArray();
        $list = $list_mes['data'];
        foreach ($list as $key => $value) {

            // 订单运费 没有折扣价显示订单运费
            $price = $value['user_discount'] =='' ? $value['actual_payment'] : $value['user_discount'];
            // 订单运费 没有支付显示订单运费否则显示支付运费
            $price = $value['referprice'] =='' ? $price : $value['referprice'];
            // 订单运费 有修改过运费则显示修改过用费
            $price = $value['upprice'] =='' ? $price : $value['upprice'];

            $list[$key]['startcity'] =  $value['startcity']; // 起点城市
            $list[$key]['endcity'] = $value['endcity']; // 终点城市
            $list[$key]['type'] = $value['type'];  // 下单类型 1现金 2面议
            $list[$key]['price'] = round($price);
            $list[$key]['referprice'] = round($value['referprice']);
            $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';  //起点城市
            $list[$key]['addtime'] = $value['addtime']; //下单时间
            $list[$key]['temperture'] = $value['temperture']; //温度
            $list[$key]['arrivetime'] = $value['arrivetime']; //司机确认送达时间
            $list[$key]['carnum'] = $value['carnum'];//车辆
            $list[$key]['pickaddress'] = json_decode($value['pickaddress']);
            $list[$key]['sendaddress'] = json_decode($value['sendaddress']);
            if ($value['orderstate']== 1 && $value['loaddate']/1000 <time()) {
                $list[$key]['orderstate'] = 7;
            }
        }
        if(empty($list)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
        }
    }
    /*
     * 个人中心整车已取消列表
     * */
    public function vehicle_cancle(){
        $token   = input("token");  //令牌
        // 验证令牌
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
        // 查询条件 除未支付的订单
        $condition['paystate'] = ['NEQ','1'];
        // 查询条件 用户的id
        $condition['userid'] = $user_id;
        $condition['orderstate']  ='4';
        // 查询整车订单
        $result = Db::table("ct_userorder")
            ->alias('o')
            ->join('__CARTYPE__ c','o.carid = c.car_id','left')
            ->field('o.*,c.carparame')
            ->order('addtime desc')
            ->where($condition)
            ->paginate(10);
        $list_mes = $result->toArray();
        $list = $list_mes['data'];
        foreach ($list as $key => $value) {

            // 订单运费 没有折扣价显示订单运费
            $price = $value['user_discount'] =='' ? $value['actual_payment'] : $value['user_discount'];
            // 订单运费 没有支付显示订单运费否则显示支付运费
            $price = $value['referprice'] =='' ? $price : $value['referprice'];
            // 订单运费 有修改过运费则显示修改过用费
            $price = $value['upprice'] =='' ? $price : $value['upprice'];

            $list[$key]['startcity'] =  $value['startcity']; // 起点城市
            $list[$key]['endcity'] = $value['endcity']; // 终点城市
            $list[$key]['type'] = $value['type'];  // 下单类型 1现金 2面议
            $list[$key]['price'] = round($price);
            $list[$key]['referprice'] = round($value['referprice']);
            $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';  //起点城市
            $list[$key]['addtime'] = $value['addtime']; //下单时间
            $list[$key]['temperture'] = $value['temperture']; //温度
            $list[$key]['arrivetime'] = $value['arrivetime']; //司机确认送达时间
            $list[$key]['carnum'] = $value['carnum'];//车辆
            if ($value['orderstate']== 1 && $value['loaddate']/1000 <time()) {
                $list[$key]['orderstate'] = 7;
            }
        }
        if(empty($list)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
        }
    }
	/**
	 * 个人中心-订单列表-冷链整车
	 * @Auther 李渊
	 * @date 2018-6-8
	 * @return [type] [description]
	 */
	public function my_vehicle(){
		$token   = input("token");  //令牌
		// 验证令牌
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
		// 查询条件 除未支付的订单
		$condition['paystate'] = ['NEQ','1'];
		// 查询条件 用户的id
		$condition['userid'] = $user_id;
		// 查询整车订单
		$result = Db::table("ct_userorder")
				->alias('o')
				->join('__CARTYPE__ c','o.carid = c.car_id','left')
				->field('o.*,c.carparame')
				->order('addtime desc')
                ->where('orderstate','neq','3')
                ->where('orderstate','neq','4')
				->where($condition)
				->paginate(10);
		$list_mes = $result->toArray();
		$list = $list_mes['data'];
		foreach ($list as $key => $value) {
			
			// 订单运费 没有折扣价显示订单运费 
			$price = $value['user_discount'] =='' ? $value['actual_payment'] : $value['user_discount'];
			// 订单运费 没有支付显示订单运费否则显示支付运费
			$price = $value['referprice'] =='' ? $price : $value['referprice'];
			// 订单运费 有修改过运费则显示修改过用费
			$price = $value['upprice'] =='' ? $price : $value['upprice'];

			$list[$key]['startcity'] =  $value['startcity']; // 起点城市
			$list[$key]['endcity'] = $value['endcity']; // 终点城市
			$list[$key]['type'] = $value['type'];  // 下单类型 1现金 2面议
			$list[$key]['price'] = round($price);
			$list[$key]['referprice'] = round($value['referprice']);
            $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';  //起点城市
			$list[$key]['addtime'] = $value['addtime']; //下单时间
			$list[$key]['temperture'] = $value['temperture']; //温度
			$list[$key]['arrivetime'] = $value['arrivetime']; //司机确认送达时间
            $list[$key]['carnum'] = $value['carnum'];//车辆
            $list[$key]['pickaddress'] = json_decode($value['pickaddress']);
            $list[$key]['sendaddress'] = json_decode($value['sendaddress']);
			if ($value['orderstate']== 1 && $value['loaddate']/1000 <time()) {
				$list[$key]['orderstate'] = 7;
			}
		}
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}	
	}
	//订单列表2019.5.23
	public function vehicle_zclist(){
        $token   = input("token");  //令牌
        // 验证令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else {
            $user_id = $check_result['user_id'];
        }

            // 查询条件 除未支付的订单
            $condition['orderstatus'] = ['NEQ','1'];

            // 查询条件 用户的id
            $condition['userid'] = $user_id;

            // 查询整车订单
            $result = Db::table("ct_useorder")
                ->alias('o')
                ->join('__CARTYPE__ c','o.carid = c.car_id','left')
                ->field('o.*,c.carparame')
                ->order('addtime desc')
                ->where($condition)
                ->where('orderstatus','neq','4')
                ->paginate(10);
            $list_mes = $result->toArray();
            $list = $list_mes['data'];

            foreach ($list as $key => $value) {

                //运费
                $list[$key]['startcity'] = $value['startcity'];  // 起点城市

                $list[$key]['endcity'] = $value['endcity']; // 终点城市

                $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';  //车型

                $list[$key]['addtime'] = $value['addtime']; //下单时间

                $list[$key]['temperture'] = $value['temperture']; //温度

                if ($value['orderstatus']== 2 && $value['picktime']/1000 <time()) {
                $list[$key]['orderstatus'] = 3;
                }
            }
            if (empty($list)) {
                    return json(['code' => '1001', 'message' => '暂无数据']);
                } else {
                    return json(['code' => '1002', 'message' => '查询成功', 'data' => $list]);
                }
    }
    /*
     * 整车取消订单列表
     * */
    public function cancel_list(){
        $token   = input("token");  //令牌
        // 验证令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else {
            $user_id = $check_result['user_id'];
        }

        // 查询条件 除未支付的订单
        $condition['orderstatus'] = ['EQ','4'];

        // 查询条件 用户的id
        $condition['userid'] = $user_id;

        // 查询整车订单
        $result = Db::table("ct_useorder")
            ->alias('o')
            ->join('__CARTYPE__ c','o.carid = c.car_id','left')
            ->field('o.*,c.carparame')
            ->order('addtime desc')
            ->where($condition)
            ->paginate(10);
        $list_mes = $result->toArray();
        $list = $list_mes['data'];

        foreach ($list as $key => $value) {

            //运费
            $list[$key]['startcity'] = $value['startcity'];  // 起点城市

            $list[$key]['endcity'] = $value['endcity']; // 终点城市

            $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';  //车型

            $list[$key]['addtime'] = $value['addtime']; //下单时间

            $list[$key]['temperture'] = $value['temperture']; //温度

            if ($value['orderstatus']== 2 && $value['picktime']/1000 <time()) {
                $list[$key]['orderstatus'] = 3;
            }
        }
        if (empty($list)) {
            return json(['code' => '1001', 'message' => '暂无数据']);
        } else {
            return json(['code' => '1002', 'message' => '查询成功', 'data' => $list]);
        }
    }
    //订单列表--订单详情2019.5.23
    public function vehicle_view(){
        $token   = input("token");  //令牌
        $uoid   = input("uoid");  //订单ID
        if(empty($token) || empty($uoid)){
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
        // 查询数据
        $detail = Db::table("ct_useorder")
            ->alias('o')
            ->join('ct_cartype c','o.carid = c.car_id','left')
            ->field('o.*,c.carparame')
            ->where('o.uoid',$uoid)
            ->find();
//        var_dump($detail['startcity']);

        // 查询返回起点城市、终点城市

        $detail['startcity'] = $detail['startcity'];
//        exit();
        $detail['endcity'] = $detail['endcity'];
        // 车型
        $detail['carparame'] = $detail['carparame'] ? $detail['carparame'] : '12.5米-15米冷藏箱车';
        // 转义并返回提货地址
        $detail['pickaddress'] = json_decode($detail['pickaddress']);
        // 转义并返回配送地址
        $detail['sendaddress'] = json_decode($detail['sendaddress'],TRUE);
        // 订单运费
        $detail['price'] = round($detail['price']);


        if(empty($detail)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
        }
    }
    //个人中心城配订单列表 2019.6.1
    public function deliveryList(){
        $token   = input("token");  //令牌
        // 验证令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else {
            $user_id = $check_result['user_id'];
        }

        // 查询条件 除未支付的订单
        $condition['orderstatus'] = ['NEQ','1'];

        // 查询条件 用户的id
        $condition['userid'] = $user_id;

        // 查询整车订单
        $result = Db::table("ct_delivery")
            ->alias('o')
            ->join('__CARTYPE__ c','o.carid = c.car_id','left')
            ->field('o.*,c.carparame')
            ->order('addtime desc')
            ->where($condition)
            ->where('o.orderstatus','neq',4)
            ->paginate(10);
        $list_mes = $result->toArray();
        $list = $list_mes['data'];

        foreach ($list as $key => $value) {

            $list[$key]['startcity'] = $value['startcity'];  // 起点城市

            $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';  //车型

            $list[$key]['addtime'] = $value['addtime']; //下单时间

            $list[$key]['temperture'] = $value['temperture']; //温度

            if ($value['orderstatus']== 2 && $value['picktime']/1000 <time()) {
                $list[$key]['orderstatus'] = 3;
            }
        }
        if (empty($list)) {
            return json(['code' => '1001', 'message' => '暂无数据']);
        } else {
            return json(['code' => '1002', 'message' => '查询成功', 'data' => $list]);
        }
    }
    // 个人中心城配订单详情 2019.6.1
    public function deliveryView(){
        $token   = input("token");  //令牌
        $uoid   = input("uoid");  //订单ID
        if(empty($token) || empty($uoid)){
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
        // 查询数据
        $detail = Db::table("ct_delivery")
            ->alias('o')
            ->join('ct_cartype c','o.carid = c.car_id','left')
            ->field('o.*,c.carparame')
            ->where('o.uoid',$uoid)
            ->find();

        // 查询返回起点城市、终点城市

        $detail['startcity'] = $detail['startcity'];
        // 车型
        $detail['carparame'] = $detail['carparame'] ? $detail['carparame'] : '12.5米-15米冷藏箱车';
        // 转义并返回提货地址
        $detail['taddress'] = json_decode($detail['taddress']);
        // 转义并返回配送地址
        $detail['paddress'] = json_decode($detail['paddress'],TRUE);
        // 订单运费
        $detail['price'] = round($detail['price']);
        if(empty($detail)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
        }
    }
    /*
     * 取消订单列表
     * */
    public function cancel_delivery(){
        $token   = input("token");  //令牌
        // 验证令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else {
            $user_id = $check_result['user_id'];
        }

        // 查询条件 取消的订单
        $condition['orderstatus'] = ['EQ','4'];

        // 查询条件 用户的id
        $condition['userid'] = $user_id;

        // 查询整车订单
        $result = Db::table("ct_delivery")
            ->alias('o')
            ->join('__CARTYPE__ c','o.carid = c.car_id','left')
            ->field('o.*,c.carparame')
            ->order('addtime desc')
            ->where($condition)
            ->paginate(10);
        $list_mes = $result->toArray();
        $list = $list_mes['data'];

        foreach ($list as $key => $value) {

            $list[$key]['startcity'] = $value['startcity'];  // 起点城市

            $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';  //车型

            $list[$key]['addtime'] = $value['addtime']; //下单时间

            $list[$key]['temperture'] = $value['temperture']; //温度

            if ($value['orderstatus']== 2 && $value['picktime']/1000 <time()) {
                $list[$key]['orderstatus'] = 3;
            }
        }
        if (empty($list)) {
            return json(['code' => '1001', 'message' => '暂无数据']);
        } else {
            return json(['code' => '1002', 'message' => '查询成功', 'data' => $list]);
        }
    }
	/**
	 * 个人中心-订单列表-冷链整车详情
	 * @Auther 李渊
	 * @date 2018-6-8
	 * @param [String] token 用户令牌
	 * @param [int] uoid 订单id
	 * @return [type] [description]
	 */
	public function vehicle_detail(){
		$token   = input("token");  //令牌
		$uoid   = input("uoid");  //订单ID
		if(empty($token) || empty($uoid)){
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
		// 查询数据
		$detail = Db::table("ct_userorder")
				->alias('o')
				->join('ct_cartype c','o.carid = c.car_id','left')
				->field('o.*,c.carparame')
				->where('o.uoid',$uoid)
				->find();
		// 查询返回起点城市、终点城市

        $detail['startcity'] = $detail['startcity'];
        $detail['endcity'] = $detail['endcity'];
        // 车型
        $detail['carparame'] = $detail['carparame'] ? $detail['carparame'] : '12.5米-15米冷藏箱车';
		// 转义并返回提货地址
		$detail['pickaddress'] = json_decode($detail['pickaddress']);
		// 转义并返回配送地址
		$detail['sendaddress'] = json_decode($detail['sendaddress'],TRUE);
		// 订单运费 没有折扣价显示订单运费 
		$price = $detail['user_discount'] =='' ? $detail['actual_payment'] : $detail['user_discount'];
		// 订单运费 没有支付显示订单运费否则显示支付运费
		$price = $detail['referprice'] =='' ? $price : $detail['referprice'];
		// 订单运费 有修改过运费则显示修改过用费
		$price = $detail['upprice'] =='' ? $price : $detail['upprice'];
		$detail['price'] = round($price);
		$detail['referprice'] = round($detail['referprice']);
		// 订单回单
		$detail['receipts'] = json_decode($detail['receipts'],TRUE);

		// 查询接单人信息
		$carriers = DB::table('ct_driver')->where('drivid',$detail['carriersid'])->find();
		// 返回接单人姓名
		$detail['carriersName'] = $carriers['realname'] == '' ? $carriers['username'] : $carriers['realname'];
		// 返回接单人电话
		$detail['carriersPhone'] = $carriers['mobile'];
		// 返回接单人图像
		if ($carriers['image']!='') {
            $detail['carriersImg'] = get_url().$carriers['image'];
        }else{
            $detail['carriersImg'] = get_url().'/static/user_header.png';
        }

		if(empty($detail)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
		}	
	}

	/**
	* 个人中心-整车物流信息
	* @auther 李渊
	* @date 2018.6.12
	* @param string token 验证令牌
	* @param string uoid 订单ID
	*/
	public function vehical_wuliu_info(){
		$token   = input("token");  //令牌
		$uoid   = input("oid");  //订单ID
		if(empty($token) || empty($uoid)){
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
		// 查询该订单信息
		$result = DB::table('ct_userorder')->where('uoid',$uoid)->find();
		// 起点城市
		$start_city = $result['startcity'];
		// 终点城市
		$end_city = $result['endcity'];
		// 定义物流状态数组 
		// 物流状态为 正在为你匹配车辆 - 订单已承接 - 已提货 - 已配送 - 已送达 - 已完成
		$arr = array();
		// 定义起始物流状态 正在为你匹配车辆
		$arr[] = array('message'=>'下单成功','date'=>date('Y-m-d H:i:s',$result['addtime']));
		// 如果订单不是未接单则定义以下物流状态否则返回
		if ($result['orderstate'] !='1') {
			// 定义第二物流状态 已承接
			$taketime = $result['taketime']!='' ? date('Y-m-d H:i:s',$result['taketime']):'';
			$arr[] = array('message'=>'订单已承接','date'=>$taketime);
			// 定义第三物流状态 已提货
			if ($result['pickTime']) {
				$pickTime = date('Y-m-d H:i:s',$result['pickTime']);
				$arr[] = array('message'=>'已提货','date'=>$pickTime);
				$arr[] = array('message'=>'运输中 从【'.$start_city.'】发往【'.$end_city.'】','date'=>$pickTime);
			}
			// 定义第四物流状态 已配送
			if ($result['sendTime']) {
				$sendTime = date('Y-m-d H:i:s',$result['sendTime']);;
				$arr[] = array('message'=>'货物已到达【'.$end_city.'】，等待卸货','date'=>$sendTime);
			}
			// 定义第五物流状态 司机确认订单完成
			if ($result['arrivetime']) {
				$arrivetime = date('Y-m-d H:i:s',$result['arrivetime']);;
				$arr[] = array('message'=>'配送完成，等待用户确认','date'=>$arrivetime);
			}
			// 定义第六物流状态 用户确认订单完成
			if ($result['orderstate'] =='3') {
				$arr[] = array('message'=>'订单已完成,感谢您的使用','date'=>'');
			}
		}
		return json(['code'=>'1002','message'=>'查询成功','data'=>$arr]);
	}

	/**
	 * 整车确认完成
	 * @param string token 验证令牌
	 * @param string uoid 订单ID
	 */
	public function vehical_affirm(){
		$token   = input("token");  //令牌
		$uoid   = input("orderid");  //订单ID
		if(empty($token) || empty($uoid)){
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
		// 查询订单信息 
		$result = Db::table("ct_userorder")
						->alias('a')
						->join('ct_user u','u.uid = a.userid')
						->join('ct_driver d','d.drivid = a.carriersid')
						->join('ct_company c','c.cid=d.companyid','LEFT')
						->field('a.*,u.phone,d.type driver_type,d.money driver_money,d.companyid,c.money com_money,u.lineclient')
						->where('uoid',$uoid)
						->find();
		// 如果订单为3则已完成
		if ($result['orderstate'] == '3') {
			return json(['code'=>'1002','message'=>'操作完成']);
		}
		// 如果没有司机确认送达时间则用户不能点击完成
		if (!$result['arrivetime']) {
			return json(['code'=>'1009','message'=>'订单未配送完成']);
		}
		// 更新物流状态
		$data['orderstate'] = 3;
		$re = Db::table('ct_userorder')->where('uoid',$uoid)->update($data);
		// 更新是否成功判断
		if ($re) {
			// 如果不是面议的订单类型给承运商账户插入运费
			if ($result['type'] !='2') {

				// 获取承运商运费 如果运费有修改则插入修改后的运费
				$price = $result['carr_upprice']=='' ? $result['fprice'] : $result['carr_upprice'];
				// 司机接单则金额进入司机账户否则进入公司账户(司机包括个体司机和公司下面的司机)
				if ($result['driver_type']=='1') {  
					Db::table('ct_driver')->where('drivid',$result['carriersid'])->update(array('money'=>$price+$result['driver_money']));
				}else{	//调度或管理员时候接单金额进入公司余额
					DB::table('ct_company')->where('cid',$result['companyid'])->update(array('money'=>$price+$result['com_money']));
					//插入对账需要信息
					$array = array(
							'ordernumber' => $result['ordernumber'], //订单编号
							'orderid' => $result['uoid'],  //订单ID
							'addtime' => $result['addtime'], //下单时间
							'userid' => $result['userid'],	//下单人
							'otype' => 4,	//订单类型1零担2定制3城配4整车
							'user_companyid' => $result['pay_type']=='1' ? $result['lineclient'] : '',  //当为项目客户信用支付时插入公司ID
							'driver_companyid' => $result['companyid']	//承运商公司ID
						);
					$this->insert_invomess($array);
				}
				//写入司机收入记录
				Db::table('ct_balance_driver')->insert(array('pay_money'=>$price,'order_content'=>'整车订单收入费用','orderid'=>$uoid,'ordertype'=>'4','action_type'=>'1','driver_id'=>$result['carriersid'],'addtime'=>time()));
			}
			
			return json(['code'=>'1002','message'=>'操作完成']);
		}
	}

	/**
	 * 个人中心-城配订单列表
	 * @Author: 李渊
	 * @Date: 2018.6.15
	 * @return [type] [description]
	 */
	public function cityWith_list(){
		$token = input('token'); // 用户令牌
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
		// 查询条件 用户id
		$where['userid'] = $user_id;
		// 除了未支付的所有订单
		$where['paystate'] = array('NEQ','1');
		// 查询数据
		$result = Db::table('ct_city_order')->alias('o')->join('ct_rout_order r','r.rid=o.rout_id')->field('o.*,r.driverphone,r.arrivetime')->where($where)->order('id','desc')->paginate(10);
		// 转义
		$list_mes = $result->toArray();
		$list = $list_mes['data'];
		$arr = array();
		foreach ($list as $key => $value) {
			$carmess = Db::table('ct_cartype')->where('car_id',$value['carid'])->find();
			$city = $value['city_id'];
			// 返回城配城市
			$arr[$key]['cityname'] = $city;
			// 返回订单id
			$arr[$key]['id'] = $value['id'];
			// 返回车型
			$arr[$key]['carname'] = $carmess['carparame'];
			// 订单运费 没有折扣价显示订单运费 
			$price = $value['user_discount'] =='' ? $value['actual_payment'] : $value['user_discount'];
			// 订单运费 没有支付显示订单运费否则显示支付运费
			$price = $value['actualprice'] =='' ? $price : $value['actualprice'];
			// 订单运费 有修改过运费则显示修改过用费
			$price = $value['upprice'] =='' ? $price : $value['upprice'];
			// 支付价格
			$arr[$key]['paymoney'] = round($price); 
			// 运输人联系方式用以获取经纬度
			$arr[$key]['driverphone'] = $value['driverphone']; 
			// 用车时间
			$arr[$key]['data_type'] = $value['data_type'];
			//客户出价
            $arr[$key]['fprice'] = $value['fprice'];
            //
            $arr[$key]['handingmode'] = $value['handingmode'];
			// 下单时间
			$arr[$key]['addtime'] = $value['addtime']; 
			// 冷冻类型1冷冻2冷藏3恒温
			$arr[$key]['cold_type'] = $value['cold_type']; 
			// 用车类型：1用车车2包车
			$arr[$key]['ordertype'] = $value['ordertype'];
			// 支付类型：1未支付2已支付3提货付 
			$arr[$key]['paystate'] = $value['paystate']; 
			// 订单类型1未接单2已接单3已完成4已取消5已提货 6超过提货时间未有人接单
			$arr[$key]['state'] = $value['state'];
			if ($value['state']==1 && strtotime($value['data_type']) < time()) {
				$arr[$key]['state'] =6;
			}
			// 选择支付类型： 1 标准价格  2 面议 3 提货支付
			$arr[$key]['pytype'] = $value['pytype'];
			// 司机送达时间
			$arr[$key]['arrivetime'] = $value['arrivetime'];
		}

		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$arr]);
		}	
	}

	/**
	 * 个人中心-城配订单详情
	 * @Auther: 李渊
	 * @Date: 2018.6.15
	 * @return [type] [description]
	 */
	public function cityWith_detail(){
		$token   = input("token");  //令牌
		$id   = input("id");  //订单ID
		if(empty($token) || empty($id)){
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
		// 查找数据
		$result = Db::table('ct_city_order')
				->alias('o')
				->join('ct_rout_order r','r.rid=o.rout_id')
				->field('o.*,r.driverid,r.drivername,r.driverphone,r.carlicense')
				->where('id',$id)
				->find();
		// 查找车型
		$carmess = Db::table('ct_cartype')->where('car_id',$result['carid'])->find();
		// 查找城市
		$city = detailadd($result['city_id'],'','');
		// 返回城市名称
		$result['cityname'] = $city;
		// 返回车型名称
		$result['carname'] = $carmess['carparame'];
		// 返回提货地址
		$result['saddress'] = json_decode($result['saddress'],TRUE);
		// 返回配送地址
		$result['eaddress'] = json_decode($result['eaddress'],TRUE);
		// 返回回单
		$result['receipts'] = json_decode($result['picture'],TRUE);
		// 订单运费 没有折扣价显示订单运费 
		$price = $result['user_discount'] =='' ? $result['actual_payment'] : $result['user_discount'];
		// 订单运费 没有支付显示订单运费否则显示支付运费
		$price = $result['actualprice'] =='' ? $price : $result['actualprice'];
		// 订单运费 有修改过运费则显示修改过用费
		$price = $result['upprice'] =='' ? $price : $result['upprice'];
		// 支付价格
		$result['paymoney'] = round($price); 

		// 查询接单人信息
		$carriers = DB::table('ct_driver')->where('drivid',$result['driverid'])->find();
		// 返回接单人姓名
		$result['carriersName'] = $carriers['realname'] == '' ? $carriers['username'] : $carriers['realname'];
		// 返回接单人电话
		$result['carriersPhone'] = $carriers['mobile'];
		// 返回接单人图像
		if ($carriers['image']!='') {
            $result['carriersImg'] = get_url().$carriers['image'];
        }else{
            $result['carriersImg'] = get_url().'/static/user_header.png';
        }
        // 返回查询结果
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
	}

	/**
	 * 城配物流信息
	 * @Auther: 李渊
	 * @Date: 2018.6.15
	 * @param string token 验证令牌
	 * @param string id 订单ID
	*/
	public function citywith_wuliu_info(){
		$token   = input("token");  //令牌
		$id   = input("oid");  //订单ID
		if(empty($token) || empty($id)){
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
		// 查询该订单信息
		$result = DB::table('ct_userorder')->where('uoid',$id)->find();
		// 查询提货地址
		$pick_address = json_decode($result['pickaddress'],true);
		// 查询配送地址
		$send_address = json_decode($result['sendaddress'],true);
		// 查询城配下单城市

		$start_city = $result['startcity'];
		// 定义物流状态数组
		// 物流状态为 正在为你匹配车辆 - 订单已承接 - 已提货 - 已送达 - 已完成
		$arr = array();
		// 定义起始物流状态 正在为你匹配车辆
		$arr[] = array('message'=>'下单成功','date'=>date('Y-m-d H:i:s',$result['addtime']));
		// 如果订单不是未接单则定义以下物流状态否则返回
		if ($result['orderstate'] !='1' && $result['orderstate'] !='4') {
			// 定义第二物流状态 已承接
			$taketime = $result['taketime']!='' ? date('Y-m-d H:i:s',$result['taketime']):'';
			$arr[] = array('message'=>'订单已承接','date'=>$taketime);
			// 定义第三物流状态 已提货
			if ($result['pickTime'] || $result['orderstate'] == '5') {
				if($result['pickTime']){
					$pickTime = date('Y-m-d H:i:s',$result['pickTime']);
				}else{
					$pickTime = $result['data_type'];
				}
				$arr[] = array('message'=>'已提货','date'=>$pickTime);
				$arr[] = array('message'=>'【'.$start_city.'】货物离开 '.$pick_address[0]['address']." 发往 ".$send_address[0]['address'],'date'=>$pickTime);
			}
			// 定义第四物流状态 司机确认订单完成
			if ($result['arrivetime']) {
				$arrivetime = $result['arrivetime']!='' ? date('Y-m-d H:i:s',$result['arrivetime']):'';
				$arr[] = array('message'=>'配送完成，等待用户确认','date'=>$arrivetime);
			}
			// 定义第五物流状态 用户确认订单完成
			if ($result['orderstate'] =='3') {
				$finshtime = $result['arrivetime']!='' ? date('Y-m-d H:i:s',$result['arrivetime']):'';
				$arr[] = array('message'=>'订单已完成,感谢您的使用','date'=>'');
			}
		}else if($result['orderstate'] == '4'){
            $arr[] = array('message'=>'订单已取消','date'=>'');
        }
		return json(['code'=>'1002','message'=>'查询成功','data'=>$arr]);
	}

	/**
	 * 城配订单确认送达
	 * @Auther： 李渊
	 * @date: 2018.6.15
	 * @return [type] [description]
	 */
	public function cityWith_service(){
		$token   = input("token");  //令牌
		$id = input('orderid'); //订单ID
		if(empty($token) || empty($id)){
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
		// 查询订单数据
		$result = DB::table('ct_city_order')
					->alias('o')
					->join('ct_rout_order r','r.rid = o.rout_id')
					->join('ct_user u','u.uid = o.userid')
					->join('ct_driver d','d.drivid=r.driverid')
					->join('ct_company c','c.cid=d.companyid','LEFT')
					->field('o.paymoney,o.id,o.carr_upprice,o.state,o.userid,o.addtime,o.paystate,o.pay_type,o.orderid,
						o.pytype,r.driverid,o.ordertype,o.eaddress,o.picture,r.rid,r.driverid,r.arrivetime,d.companyid,
						d.type,d.money,c.money com_money,u.lineclient')
					->where('id',$id)
					->find();
		// 如果已经是送达状态则返回
		if ($result['state']=='3') {
			return json(['code'=>'1002','message'=>'操作成功']);
			exit();
		}
		// 如果司机没有确认或者订单未支付则不等送达
		if(!$result['arrivetime'] || $result['paystate'] != '2'){
			return json(['code'=>'1009','message'=>'司机未送达，请耐心等待!']);
			exit();
		}
		// 查询条件订单id
		$where['id'] = $id;
		// 订单状态
		$up_data['state'] = '3';//接单状态1未接2已接3已完成
		// 更新订单完成时间
		$rout_data['finshtime'] = time();
		// 更新派送信息对接信息
		$rout_data['apitype'] = 1;
		// 更新数据
		$re = Db::table("ct_city_order")->where($where)->update($up_data);
		Db::table('ct_rout_order')->where('rid',$result['rid'])->update($rout_data);

		if($re){
			// 如果不是面议的订单类型给承运商账户插入运费
			if ($result['pytype'] !='2') {
				// 查询订单的承运商运费
				$city_price = $result['carr_upprice']=='' ? $result['paymoney'] : $result['carr_upprice'];
				// 司机接单则金额进入司机账户否则进入公司账户(司机包括个体司机和公司下面的司机)
				if ($result['type'] =='1') {
					Db::table('ct_driver')->where('drivid',$result['driverid'])->update(array('money'=>$city_price+$result['money']));
				}else{
					Db::table('ct_company')->where('cid',$result['companyid'])->update(array('money'=>$city_price+$result['com_money']));
					//插入对账需要信息
					$array = array(
							'ordernumber' => $result['orderid'], //订单编号
							'orderid' => $result['id'],  //订单ID
							'addtime' => $result['addtime'], //下单时间
							'userid' => $result['userid'],	//下单人
							'otype' => 3,	//订单类型1零担2定制3城配4整车
							'user_companyid' => $result['pay_type']=='1' ? $result['lineclient'] : '',  //当为项目客户信用支付时插入公司ID
							'driver_companyid' => $result['companyid']	//承运商公司ID
						);
					$this->insert_invomess($array);
				}
				Db::table('ct_balance_driver')->insert(array('pay_money'=>$city_price,'order_content'=>'市内配送订单收入费用','orderid'=>$id,'ordertype'=>'3','action_type'=>'1','driver_id'=>$result['driverid'],'addtime'=>time()));
			}
			return json(['code'=>'1002','message'=>'操作成功']);
		}else{
			return json(['code'=>'1001','message'=>'操作失败']);
		}
	}
	
	/**
	 * 个人中心-零担订单列表
	 * @Auther: 李渊
	 * @Date: 2018.7.5
	 * @param  [type] $token [用户令牌]
	 * @param  [type] $token [用户令牌]
	 * @return [type] [description]
	 */
	public function my_shift(){
		$token   = input("token");  //令牌
		$act_type = input("act_type");   //订单状态：1 进行中  2 已完成  
		if(empty($token) || empty($act_type)){
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
		// 根据类型查找对应的订单
		switch ($act_type) {
			case '1':
				$condition['o.orderstate'] =array('in','2,3,4,5,6');
				break;
			case '2':
				$condition['o.orderstate'] ='7';
				break;
			default:
				$condition['o.orderstate'] =array('in','2,3,4,5,6');
				break;
		}
		// 筛选条件 用户id
		$condition['o.userid'] = $user_id;
		// 查找数据
		$result = Db::table("ct_order")
				->alias('o')
				->join('__SHIFT__ s','o.shiftid = s.sid')
				->join('__LINEORDER__ l','l.orderid = o.oid')
				->join('__PICKORDER__ p','p.orderid = o.oid')
				->join('__DELORDER__ d','d.orderid = o.oid')
				->field('o.oid,o.picktype,o.sendtype,o.picksite,o.stime,o.sphone,o.sendsite,o.dtime,o.tphone,o.ordernumber,o.orderstate,o.addtime,o.totalcost,o.pickcost,o.linepice,o.delivecost,o.all_price,o.arrivetime,l.driverid,
					s.shiftnumber,s.sid,o.paystate,l.luseprice,p.usepprice,d.puseprice,s.shiftstate,s.weekday')
				->order('o.addtime desc')
				->where($condition)
				->paginate(10);
		$list_mes = $result->toArray();
		$list = $list_mes['data'];
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			foreach ($list as $key => $value) {
				$grade = Db::table('ct_linegrade')->where('orderid',$value['oid'])->find();
				if (empty($grade)) { 
					//未评论
					$list[$key]['gradestatus'] = '1';
				}else{
					//以评论
					$list[$key]['gradestatus'] = '2';
				}
				//查找运输人号码
				$driverphone = '';
				if ($value['driverid'] !='') {
					$search_phone = Db::table('ct_driver')->where('drivid',$value['driverid'])->find();
					$driverphone = $search_phone['mobile'];
				}
				$list[$key]['driverphone'] = $driverphone;
				// 提货费
				$tiprice = $value['usepprice'] == '' ? $value['pickcost'] : $value['usepprice'];
				// 干线费
				$lineprice = $value['luseprice']== '' ? $value['linepice'] : $value['luseprice'];
				// 配送费
				$peiprice = $value['puseprice']== '' ? $value['delivecost'] : $value['puseprice'];
				if ($value['picktype'] ==1 && $value['sendtype'] == 1){
                    $order_price = $tiprice +$lineprice+$peiprice;
                }elseif($value['picktype'] ==1 && $value['sendtype'] == 2){
                    $order_price = $tiprice +$lineprice;
                }elseif($value['picktype'] ==2 && $value['sendtype'] == 1){
                    $order_price = $lineprice+$peiprice;
                }else{
                    $order_price = $lineprice;
                }
//				$order_price = $tiprice +$lineprice+$peiprice;
				$list[$key]['allprice'] = round($order_price);
				$line = Db::field('b.*')->table('ct_shift')->alias('a')->join('ct_already_city b','b.city_id=a.linecityid')->where('a.sid',$value['sid'])->find();
				//查询起点城市
				$start_city_name = Db::table("ct_district")->field("name")->where("id",$line['start_id'])->find();
				$list[$key]['start_city_name'] = $start_city_name['name'];
				//查询终点城市
				$end_city_name = Db::table("ct_district")->field("name")->where("id",$line['end_id'])->find();
				$list[$key]['end_city_name'] = $end_city_name['name'];
				//查询提货司机信息
				$driver_car = Db::table('ct_pickorder')->where('orderid',$value['oid'])->find();
				
					$list[$key]['drivername'] = $driver_car['drivername'];
					$list[$key]['driverphone'] = $driver_car['driverphone'];
					$list[$key]['carlicense'] = $driver_car['carlicense'];
					$list[$key]['arrivetime'] = $value['arrivetime'];
					$list[$key]['shiftnumber'] = $value['shiftstate'] =='1' ? $value['shiftnumber'] : $value['weekday'];
				
			}
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}			
	}

	/**
	 * 个人中心-零担订单详情
	 * @Auther: 李渊
	 * @Date: 2018.7.5
	 * @param  [string] $token [用户令牌]
	 * @param  [string] $oid   [订单id]
	 * @return [type] 	[description]
	 */
	public function shift_detail(){
		$token   = input("token");  //令牌
		$oid   = input("oid");  //订单ID

		if(empty($token) || empty($oid)){
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
		// 查找数据
		$detail = Db::table("ct_order")
				->alias('o')
				->join('__LINEORDER__ l','l.orderid = o.oid')
				->join('__PICKORDER__ p','p.orderid = o.oid')
				->join('__DELORDER__ d','d.orderid = o.oid')
				->join('__SHIFT__ s','o.shiftid=s.sid')
				->join('__ALREADY_CITY__ al','al.city_id = s.linecityid')
				->join('__USER__ u','u.uid=o.userid')
				->field('o.oid,o.picktype,o.sendtype,o.picksite,o.stime,o.sphone,o.sendsite,o.dtime,o.tphone,o.ordernumber,o.orderstate,o.coldtype,o.totalnumber,o.all_price,o.totalweight,o.totalvolume,o.itemtype,o.remark,o.pickcost,o.linepice,o.delivecost,
					o.userid,o.picktime,o.addtime,o.totalcost,o.receipt,o.pickaddress,o.sendaddress,o.arrtime,o.starttime,s.shiftnumber,u.realname,u.phone,
					al.start_id,al.end_id,l.luseprice,p.usepprice,d.puseprice,s.shiftstate,s.weekday,s.driver_id')
				->where('oid',$oid)
				->find();

		$result['drivername'] = '赤途(上海)供应链管理有限公司';
		$result['driverphone'] = '4009-206-101';			
		if ($detail['shiftstate'] =='2') {
			$driver = Db::table('ct_driver')->where('drivid',$detail['driver_id'])->find();
			$result['shift_person'] = $driver['username'];
			$result['shift_phone'] = $driver['mobile'];
		}
		$start_city = detailadd($detail['start_id'],'','');
		$end_city = detailadd($detail['end_id'],'',''); 
		$result['ordernumber']	= $detail['ordernumber']; //订单号
		$result['receipts']	= json_decode($detail['receipt'],TRUE); //回单
		$result['shiftnumber']	= $detail['shiftnumber'] = $detail['shiftstate'] =='1' ? $detail['shiftnumber'] : $detail['weekday'];
		$result['start_city_name'] = $start_city;	//起点城市
		$result['end_city_name'] = $end_city;	 //终点城市
		$result['add_time'] = $detail['addtime'];	     //下单时间
		$result['coldtype'] = $detail['coldtype'];	     //冷藏类型
		$result['itemtype'] = $detail['itemtype'];	     //物品类型
		$result['picktime'] = $detail['picktime'];	     //预计提货时间
		$result['totalnumber'] = $detail['totalnumber'];	     //总件数
		$result['totalweight'] = $detail['totalweight'];	     //总重量
		$result['totalvolume'] = $detail['totalvolume'];	     //总体积
		$result['remark'] = $detail['remark'];	     //预计提货时间
		$result['deptime'] = $detail['starttime'];	     //发车时间
		$result['endtime'] = $detail['arrtime'];	     //倒车时间
		$result['realname'] = $detail['realname'];	     //下单人
		$result['phone'] = $detail['phone'];	     //下单人联系方式
		$result['orderstate'] = $detail['orderstate'];	     //订单状态

        $result['picktype'] = $detail['picktype'];
        $result['sendtype'] = $detail['sendtype'];

        $result['picksite'] = $detail['picksite'];
        $result['stime'] = $detail['stime'];
        $result['sphone'] = $detail['sphone'];
        $result['sendsite'] = $detail['sendsite'];
        $result['dtime'] = $detail['dtime'];
        $result['tphone'] = $detail['tphone'];

		//查询提货司机信息
		$driver_car = Db::table('ct_pickorder')->where('orderid',$oid)->find();
		
		//$result['drivername'] = $driver_car['drivername'];
		//$result['driverphone'] = $driver_car['driverphone'];
		$result['carlicense'] = $driver_car['carlicense'];

		if($detail['pickaddress']){
            $pickaddress = json_decode($detail['pickaddress'],TRUE);
            $pick_arr = array();
            if (!empty($pickaddress)) {
                foreach ($pickaddress as $key => $value) {
                    $pick_arr[] = $start_city.$value['taddressstr'];
                }
            }
        }else{
            $pick_arr = $detail['pickaddress'] = '';
        }
		if($detail['sendaddress']){
            $sendaddress = json_decode($detail['sendaddress'],TRUE);
            $send_arr = array();
            if (!empty($sendaddress)) {
                foreach ($sendaddress as $key => $val) {
                    $send_arr[$key]['name'] = $val['name'];
                    $send_arr[$key]['phone'] = $val['phone'];
                    $send_arr[$key]['paddress'] = $end_city.$val['paddressstr'];
                }
            }
        }else{
            $send_arr = $detail['sendaddress'] = '';
        }

		// 提货费
		$tiprice = $detail['usepprice'] == '' ? $detail['pickcost'] : $detail['usepprice'];
		// 干线费
		$lineprice = $detail['luseprice']== '' ? $detail['linepice'] : $detail['luseprice'];
		// 配送费
		$peiprice = $detail['puseprice']== '' ? $detail['delivecost'] : $detail['puseprice'];
		$price = $tiprice +$lineprice+$peiprice;		
		$result['money'] = round($price); //实际支付金额;
		$result['pick_address'] = $pick_arr;
		$result['send_address'] = $send_arr;
		
		$result['image'] =  get_url().'/static/service_header.png';
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}	
	}

	/**
	 * 零担物流信息
	 * @param  [string] $token [用户令牌]
	 * @param  [Int] 	$oid   [订单id]
	 * @return [Array] 	[description]
	 */
	public function bulk_wuliu_info(){
		$token   = input("token");  //令牌
		$oid  = input("oid");  //订单ID
		if(empty($token) || empty($oid)){
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
		// 查询数据
		$result = Db::table("ct_order")
				->alias('o')
				->join('__PICKORDER__ p','p.orderid = o.oid')
				->join('__SHIFT__ s','o.shiftid=s.sid')
				->join('__ALREADY_CITY__ al','al.city_id = s.linecityid')
				->field('o.oid,o.ordernumber,o.orderstate,o.sendsite,o.picktime,o.addtime,o.pickaddress,o.sendaddress,o.arrtime,o.starttime,o.arrivetime,p.status,
					al.start_id,al.end_id,p.receivetime,s.shiftstate')
				->where('oid',$oid)
				->find();
		$start_city = detailadd($result['start_id'],'','');
		$end_city = detailadd($result['end_id'],'','');
        if ($result['sendaddress']){
            $sendaddress = json_decode($result['sendaddress'],TRUE);
            $address = array();
            foreach ($sendaddress as $value) {
                $address = $value;
            }
        }else{
            $address = $result['sendsite'];
        }
		$arr = array();
		$arr[] = array('message'=>'下单成功','date'=>date('Y-m-d H:i:s',$result['addtime']));
		if ($result['orderstate'] !='1') {
			if ($result['status']=='2') {
				$taketime = $result['receivetime']!='' ? date('Y-m-d H:i:s',$result['receivetime']):'';
				$arr[] = array('message'=>'订单已承接','date'=>$taketime);
				$loaddate = strtotime($result['picktime']);
				if (time()>$loaddate) {
					$arr[] = array('message'=>'货物已出库','date'=>date('Y-m-d H:i:s',strtotime($result['picktime'] . ' +30 minute')));
					if ($result['shiftstate']=='1') {
						if (time()>$result['starttime']) {
								$arr[] = array('message'=>'运输中 从【'.$start_city.'】发往【'.$end_city.'】','date'=>date('Y-m-d H:i:s',$result['starttime']));
						}
						if (time()>$result['arrtime']) {
								$arr[] = array('message'=>'货物已到达【'.$end_city.'】','date'=>date('Y-m-d H:i:s',$result['arrtime']));
						}
					}
					
				}
			}
			
			if ($result['arrivetime'] !='') {
				$arrivetime = $result['arrivetime']!='' ? date('Y-m-d H:i:s',$result['arrivetime']):'';
				if ($result['sendaddress']){
                    $arr[] = array('message'=>'货物已到达【'.$end_city.'】'.$address['paddressstr'].'，卸货完成','date'=>$arrivetime);
                }else{
                    $arr[] = array('message'=>'货物已到达【'.$end_city.'】'.$address.'，卸货完成','date'=>$arrivetime);
                }

			}

			if ($result['orderstate'] =='7') {
				$arr[] = array('message'=>'已完成','date'=>'');
			}
		}
		return json(['code'=>'1002','message'=>'查询成功','data'=>$arr]);
	}
	
	/**
	 * 零担确认送达
	 * @Auther: 李渊
	 * @Date: 2018.7.5
	 * @param  [string] $token 		[用户令牌]
	 * @param  [Int] 	$orderid   	[订单id]
	 * @return [Object] 			[description]
	 */
	public function bulk_end(){
		// 用户令牌
		$token = input('token');
		// 订单id
		$oid   = input("orderid"); 
		if(empty($token) || empty($oid)){
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
		// 查找是否有已经有回单上传
	    $find = Db::table("ct_order")
	    		->alias('o')
	    		->join('ct_user u','u.uid=o.userid')
				->join("ct_pickorder ti",'ti.orderid = o.oid') // 提货单
				->join("ct_lineorder gan",'gan.orderid = o.oid') // 干线单
				->join("ct_delorder pi",'pi.orderid = o.oid') // 配送单
				->join("ct_shift s",'s.sid = o.shiftid')
	    		->field('o.receipt,u.phone,o.all_price,o.orderstate,o.pickcost,o.linepice,o.delivecost,o.ordernumber,o.oid,o.addtime,o.userid,
	    			o.pay_type,u.lineclient,ti.tcarr_upprice,gan.lcarr_price,pi.pcarr_upprice,s.companyid')
	    		->where('oid',$oid)
	    		->find();
	    
	   	// 如果订单已完成
	    if ($find['orderstate'] =='7') {
        	return json(['code'=>'1002','message'=>'操作成功']);
        	exit();
        }
        // 如果没有回单
        if($find['receipt'] == ''){
        	return json(['code'=>'1003','message'=>'未上传回单']);
        	exit();
        }
        // 更新零担单
		$orderdata['orderstate'] = '7';  //接单状态1未接2已接3已完成
		$re = Db::table("ct_order")->where('oid',$oid)->update($orderdata);
		// 提货费
		$tiprice = $find['tcarr_upprice'] == '' ? $find['pickcost'] : $find['tcarr_upprice'];
		// 干线费
		$lineprice = $find['lcarr_price']== '' ? $find['linepice'] : $find['lcarr_price'];
		// 配送费
		$peiprice = $find['pcarr_upprice']== '' ? $find['delivecost'] : $find['pcarr_upprice'];
		// 总费用
		$totalprice = $tiprice + $lineprice + $peiprice;
		// 查找司机信息
	    $driver_mess = Db::table('ct_company')
	    				->alias('c')
	    				->join('ct_driver d','d.companyid=c.cid')
	    				->field('c.cid,c.money,d.drivid')
	    				->where(array('cid'=>$find['companyid'],'d.type'=>'3'))
	    				->find();
        if($re){
			//更新公司余额
			DB::table('ct_company')->where('cid',$driver_mess['cid'])->update(array('money'=>$driver_mess['money']+round($totalprice)));
			//记录收入记录
			Db::table('ct_balance_driver')->insert(array('pay_money'=>round($totalprice),'order_content'=>'零担订单收入费用','orderid'=>$oid,'ordertype'=>'1','action_type'=>'1','driver_id'=>$driver_mess['drivid'],'addtime'=>time()));
			
		 	// 更新提货单
		 	$pickdata['status'] = '3';  //接单状态1未接2已接3已完成
			Db::table("ct_pickorder")->where('orderid',$oid)->update($pickdata);
			// 更新干线单
		 	$linedata['status'] = '3';  //接单状态1未接2已接3已完成
		 	Db::table("ct_lineorder")->where('orderid',$oid)->update($linedata);
		 	// 更新配送单
		 	$deldata['status'] = '3';  //接单状态1未接2已接3已完成
			Db::table("ct_delorder")->where('orderid',$oid)->update($deldata);
			//插入对账需要信息
			$array = array(
					'ordernumber' => $find['ordernumber'], //订单编号
					'orderid' => $find['oid'],  //订单ID
					'addtime' => $find['addtime'], //下单时间
					'userid' => $find['userid'],	//下单人
					'otype' => 1,	//订单类型1零担2定制3城配4整车
					'user_companyid' => $find['pay_type']=='1' ? $find['lineclient'] : '',  //当为项目客户信用支付时插入公司ID
					'driver_companyid' => $find['companyid']	//承运商公司ID
				);
			$this->insert_invomess($array);
			return json(['code'=>'1002','message'=>'操作成功']);
		}else{
			return json(['code'=>'1001','message'=>'操作失败']);
		}
	}
	
	/**
	 * 零担定制列表
	 * @Auther: 李渊
	 * @Date: 2018.7.10
	 * @param  [string] $token 		[用户令牌]
	 * @return [Object] 			[description]
	 */
	public function special_line(){
		$token = input('token');
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
		// 查询数据
		$result = Db::table('ct_shift_order')
					->alias('o')
					->join('__FIXATION_LINE__ s','s.id = o.shiftid')
					->field('o.s_oid,o.addtime,o.totalprice,o.orderstate,o.upprice,o.picktime,o.price,s.carprice,s.lienid')
					->where(array('userid'=>$user_id,'affirm'=>'2'))
					->order('s_oid','desc')
					->paginate(10);
		$list_mes = $result->toArray();
		$list = $list_mes['data'];
		$arr = array();
		$str_start = '';
		$str_end='';
		foreach ($list as $key => $value) {
			$city_search = Db::table('ct_already_city')->where('city_id',$value['lienid'])->find();
			// 订单ID
			$arr[$key]['s_oid'] = $value['s_oid']; 
			// 下单时间
			$arr[$key]['addtime']  = $value['addtime']; 
			// 提货时间
			$arr[$key]['picktime'] = $value['picktime']; 
			// 起始城市
			$sarr =  Db::table('ct_district')->where('id',$city_search['start_id'])->find();
            $start_city ='';
            if ($sarr['level'] =='3') {
              	$scity = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
              	$start_city = $scity['name'];
            }
            $arr[$key]['startcity'] = $start_city.$sarr['name'];
          	// 终点城市
            $earr = Db::table('ct_district')->where('id',$city_search['end_id'])->find();
            $end_city='';
            if ($earr['level'] =='3') {
              	$ecity = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
              	$end_city = $ecity['name'];
            }
            $arr[$key]['endcity'] = $end_city.$earr['name'];
            // 价格总额
			$arr[$key]['price'] = $value['totalprice']; 
			if (!empty($value['upprice'])) {
				$arr[$key]['price'] = $value['upprice'];
			}
			
			$arr[$key]['orderstate'] = $value['orderstate']; //1未接单，2已接单3已完成
		}
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$arr]);
		}	

	}
	
	/*
	 *
	 * 专车定制线路详情
	 */
	public function special_line_detail(){
		$token = input('token');
		$orderid = input('orderid');
		if(empty($token) || empty($orderid)){
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
		$result = Db::table('ct_shift_order')
					->alias('o')
					->join('__FIXATION_LINE__ s','s.id = o.shiftid')
					->join('__ALREADY_CITY__ a','a.city_id = s.lienid')
					->field('o.s_oid,o.userid,o.addtime,o.totalprice,o.upprice,o.ordernumber,o.remark,o.orderstate,o.doornum,o.totalcar,o.picktime,o.price,s.carrierid,s.carprice,a.end_id,a.start_id')
					->where(array('s_oid'=>$orderid,'affirm'=>'2'))
					->find();
		$array = array();
		$str_start ='';
		$str_end='';
		//起始城市
		$start_area = Db::table('ct_district')->where('id',$result['start_id'])->find();
		if ($start_area['level'] =='3') {
			$str_city = Db::table('ct_district')->where('id',$start_area['parent_id'])->find();
			$str_start = $str_city['name'];
		}
		$result['startcity'] = $str_start.$start_area['name'];
		//终点城市
		$end_area = Db::table('ct_district')->where('id',$result['end_id'])->find();
		if ($end_area['level'] =='3') {
			$str_city = Db::table('ct_district')->where('id',$end_area['parent_id'])->find();
			$str_end = $str_city['name'];
		}
		$result['endcity'] = $str_end.$end_area['name']; //终点城市名称
		//查找下单人联系放肆
		$user = Db::table('ct_user')->where('uid',$result['userid'])->find();
		$result['user_phone'] = $user['phone'];
		//查找承运商联系方式
		$carries = Db::table('ct_driver')->where('drivid',$result['carrierid'])->find();
		$result['carr_phone'] = $carries['mobile'];
		$result['price'] = $result['totalprice']; //价格总额
		if (!empty($result['upprice'])) {
			$result['price'] = $result['upprice'];
		}
		$result['drivername'] = '赤途(上海)供应链管理有限公司';
		$result['driverphone'] = '4009-206-101';
		$result['image'] =  get_url().'/static/service_header.png';
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}	
	}
	/*
	*定制线路物流信息
	*@param string token 验证令牌
	*@param string id 订单ID
	*/
	public function special_wuliu_info(){
		$token   = input("token");  //令牌
		$orderid   = input("oid");  //订单ID
		if(empty($token) || empty($orderid)){
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
		$result = Db::table('ct_shift_order')
					->alias('o')
					->join('__FIXATION_LINE__ s','s.id = o.shiftid')
					->join('__ALREADY_CITY__ a','a.city_id = s.lienid')
					->field('o.s_oid,o.addtime,o.ordernumber,o.taketime,o.arrivetime,o.remark,o.orderstate,o.picktime,a.end_id,a.start_id')
					->where(array('s_oid'=>$orderid))
					->find();
		$str_start ='';
		$str_end='';
		//起始城市
		$start_area = Db::table('ct_district')->where('id',$result['start_id'])->find();
		if ($start_area['level'] =='3') {
			$str_city = Db::table('ct_district')->where('id',$start_area['parent_id'])->find();
			$str_start = $str_city['name'];
		}
		$result['startcity'] = $str_start.$start_area['name'];
		//终点城市
		$end_area = Db::table('ct_district')->where('id',$result['end_id'])->find();
		if ($end_area['level'] =='3') {
			$str_city = Db::table('ct_district')->where('id',$end_area['parent_id'])->find();
			$str_end = $str_city['name'];
		}
		
		$arr = array();
		$arr[] = array('message'=>'下单成功','date'=>date('Y-m-d H:i:s',$result['addtime']));
		if ($result['orderstate'] !='1') {
			$taketime = $result['taketime']!='' ? date('Y-m-d H:i:s',$result['taketime']):'';
			$arr[] = array('message'=>'订单已承接','date'=>$taketime);
			$loaddate = strtotime($result['picktime']);
			if (time()>$loaddate) {
				$arr[] = array('message'=>'订单已出库','date'=>$result['picktime']);
					//提货时间加一个小时
					$send_totime = strtotime($result['picktime'] . ' +1 hour');
					if (time()>$send_totime) {
						$send_time = date('Y-m-d H:i:s',$send_totime);
						$arr[] = array('message'=>'货物从'.$str_start.' 离开发往 '.$str_end,'date'=>$send_time);
					}
			}
			if ($result['arrivetime'] !='') {
				$arrivetime = $result['arrivetime']!='' ? date('Y-m-d H:i:s',$result['arrivetime']):'';
				$arr[] = array('message'=>'货物已到达'.$str_end.'，卸货完成','date'=>$arrivetime);
			}
			if ($result['orderstate'] =='3') {
				$arr[] = array('message'=>'已完成','date'=>'');
			}
			
		}
		return json(['code'=>'1002','message'=>'查询成功','data'=>$arr]);
		
	}
	/*
	*订单完成
	*@param token  令牌
	*@param orderid   订单ID
	*/
	public function special_line_confim(){
		$token = input('token');
		$orderid = input('orderid');
		if(empty($token) || empty($orderid)){
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
		
		//查找定制订单金额
		$orderid_mess = Db::table('ct_shift_order')
							->alias('o')
							->join('ct_fixation_line f','f.id = o.shiftid')
							->field('o.*,f.companyid')
							->where('s_oid',$orderid)
							->find();
		$driver_mess = Db::table('ct_driver')
						->alias('a')
						->join('ct_company c','c.cid = a.companyid')
						->where('drivid',$orderid_mess['driverid'])
						->find();
		if ($orderid_mess['orderstate']=='3') {
			return json(['code'=>'1002','message'=>'操作成功']);
			exit();
		}
		$data['orderstate']=3;
		$result = Db::table('ct_shift_order')->where('s_oid',$orderid)->update($data);
		//完成订单将订单加到公司账户下
		Db::table('ct_company')->where('cid',$driver_mess['companyid'])->update(array('money'=>$driver_mess['money']+$orderid_mess['price']));
		Db::table('ct_balance_driver')->insert(array('pay_money'=>$orderid_mess['price'],'order_content'=>'定制线路订单收入费用','orderid'=>$orderid,'ordertype'=>'2','action_type'=>'1','driver_id'=>$orderid_mess['driverid'],'addtime'=>time()));
		//插入对账需要信息
		$array = array(
				'ordernumber' => $orderid_mess['ordernumber'], //订单编号
				'orderid' => $orderid_mess['s_oid'],  //订单ID
				'addtime' => $orderid_mess['addtime'], //下单时间
				'userid' => $orderid_mess['userid'],	//下单人
				'otype' => 2,	//订单类型1零担2定制3城配4整车
				'user_companyid' => $orderid_mess['pay_type']=='1' ? $orderid_mess['companyid'] : '',  //当为项目客户信用支付时插入公司ID
				'driver_companyid' => $driver_mess['companyid']	//承运商公司ID
			);
		$this->insert_invomess($array);
		if($result){
			return json(['code'=>'1002','message'=>'操作成功']);
		}else{
			return json(['code'=>'1001','message'=>'操作失败']);
		}
	}

	/**
	 * 个人中心-发布货源订单列表
	 * @Auther: 李渊
	 * @Date: 2018.7.10
	 * @param  [string] $token 		[用户令牌]
	 * @return [type] [description]
	 */
	public function item_list(){
		// 令牌
		$token = input("token");  
		// 类型
		$type = input("type");
		// 验证类型 
		if (empty($token) || empty($type)) {
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
		// 查询条件 货源信息
		$condition['o.ordertype'] = 1;
		// 查询条件 已支付
		$condition['o.paystate'] = 2;
		// 查询条件 用户id
		$condition['o.userid'] = $user_id;
		// 查询条件 订单状态
		if ($type == 1) {
			$condition['o.orderstate'] = 1;
		} else {
			$condition['o.orderstate'] = ['NEQ',1];
		}
		
		// 查询数据
		$result = Db::table("ct_issue_item")
				->alias('o')
				->join('ct_cartype car','car.car_id = o.carid','LEFT')
				->field('o.*,car.carparame')
				->order('addtime desc')
				->where($condition)
				->paginate(10);
		// 转义 
		$list_mes = $result->toArray();
		// 获取查询数据
		$list = $list_mes['data'];
		// 循环输出数据
		foreach ($list as $key => $value) {
			$list[$key] = $value;
            // 起点地址
            $list[$key]['start_address'] = idToAddress('',$value['start_city'],$value['start_area']); 
            // 终点地址
            $list[$key]['end_address'] = idToAddress('',$value['end_city'],$value['end_area']); 
			// 重量
            $list[$key]['weight'] = $value['weight'] ? ($value['weight']/1000).'吨' : ''; 
            // 立方
            $list[$key]['volume'] = $value['volume'] ? $value['volume'].'方' : ''; 
            // 包车类型
            $list[$key]['carriage'] = $value['carriage'] == 1 ? '拼车' : '包车';
		}
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}	
	}

	/**
	 * 个人中心-发布货源订单详情
	 * @Auther: 李渊
	 * @Date: 2018.7.10
	 * @param  [string] $token 		[用户令牌]
	 * @param  [string] $orderid 	[订单ID]
	 * @return [type] [description]
	 */
	public function item_list_detail(){
		// 令牌
		$token   = input("token");  
		// 订单ID
		$orderid   = input("orderid");  
		if(empty($token) || empty($orderid)){
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
		// 查询数据
		$detail = Db::table("ct_issue_item")
                ->alias('o')
                ->join('ct_cartype car','car.car_id = o.carid','LEFT')
                ->field('o.*,car.carparame')
                ->where('o.id',$orderid)
                ->find();
        // 起点地址
        $detail['startAddress'] = idToAddress($detail['start_pro'],$detail['start_city'],$detail['start_area']); 
        // 终点地址
        $detail['endAddress'] = idToAddress($detail['end_pro'],$detail['end_city'],$detail['end_area']);       
		// 重量
        $detail['weight'] = $detail['weight'] ? ($detail['weight']/1000).'吨' : '';
        // 立方
        $detail['volume'] = $detail['volume'] ? $detail['volume'].'方' : '';
        // 运输类型
        $detail['carriage'] = $detail['carriage'] == 1 ? '拼车' : '包车';
		// 判断是查询成功
		if(empty($detail)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
		}	
	}

	/**
	 * 查看我查看过车源
	 * @auther: 李渊
	 * @date: 2018.9.17
	 * @return [type] [description]
	 */
	public function pretend_item_list(){
		$token = input("token"); // 令牌
		// 判断参数表
		if(empty($token)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 验证令牌
		$check_result = $this->check_token($token);
		// 验证登陆状态
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}	
		// 筛选已经支付过得
		$condition['o.paystate'] = 2;
		// 筛选车源
		$condition['o.ordertype'] = 2;
		// 查询数据
		$result = Db::table("ct_issue_item")
				->alias('o')
				->join('__CARTYPE__ c','o.carid = c.car_id')
				->field('o.*,c.carparame')
				->order('addtime desc')
				->where($condition)
				->select();
		// 定义新数组
		$array = array();
		// 定义索引
		$i = 0;
		// 遍历数据
		foreach ($result as $key => $value) {
			// 字符串转数组获取查看的人
			$get_driver = json_decode($value['driverid'],TRUE);
			// 判断该用户是否支付过
			if (!empty($get_driver) && in_array($user_id, $get_driver)) {
				// 订单ID
				$array[$i]['orderid'] = $value['id'];  
				// 订单状态 1 进行中 2 已完成 3 手动取消 4 自动取消
			 	$array[$i]['orderstate'] = $value['orderstate'];  
			 	// 定义起点城市
			 	$array[$i]['start_city'] = addresidToName($value['start_city']);  
			 	// 终点城市
			 	$array[$i]['end_city'] = addresidToName($value['end_city']); 
			 	// 车型名
			 	$array[$i]['carparame'] = $value['carparame']; 
			 	// 发车时间
			 	$array[$i]['loaddate'] = $value['loaddate']; 
			 	// 索引自增
			 	$i++;
			}
		}
		// 判断是否有数据
		if(empty($array)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$array]);
		}	
	}
	
	/**
	 * 个人中心： 已查看的车源信息
	 * @auther： 李渊
	 * @date: 2018.10.25
	 * @param  [String] [token]   [<用户令牌>]
	 * @param  [Int] 	[orderid] [<订单id>]
	 * @return [type] [description]
	 */
	public function pretend_list_detail(){
		$token 		= input("token");    // 令牌
		$orderid    = input("orderid");  // 订单ID
		// 验证参数
		if(empty($token) || empty($orderid)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 验证令牌
		$check_result = $this->check_token($token);
		// 验证登陆状态
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}	
		// 查询订单详情
		$detail = Db::table("ct_issue_item")
				->alias('o')
				->join('__CARTYPE__ c','o.carid = c.car_id')
				->field('o.*,c.carparame,c.allweight,c.allvolume')
				->where('o.id',$orderid)
				->find();
		// 查询发布司机的信息
		$driver = DB::table('ct_driver')->where('drivid',$detail['userid'])->find();
		// 返回发布人的图像
		if ($driver['image']!='') {
            $detail['image'] = get_url().$driver['image'];
        }else{
            $detail['image'] = get_url().'/static/user_header.png';
        }
		// 返回起点城市
		$detail['start_city'] = addresidToName($detail['start_city']);
		// 返回终点城市
		$detail['end_city'] = addresidToName($detail['end_city']);
		// 返回重量
		$detail['weight'] = $detail['weight'] ? $detail['weight'] : $detail['allweight'];
		// 返回体积
		$detail['volume'] = $detail['volume'] ? $detail['volume'] : $detail['allvolume'];
		// 判断是否有数据
		if(empty($detail)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
		}	
	}
	
	/**
	 * 返回用户订单反馈内容
	 * @param token string  验证令牌
	 * @param orderid int  订单ID
	 * @param act_type int 订单类型 1零担 2定制 3城配 4整车
	 */
	public function order_contact_mess(){
		$token   = input("token");  //令牌
		$orderid   = input("orderid");  //订单ID
		$act_type   = input("act_type");  //订单类型 1零担 2定制 3城配 4整车
		if(empty($token) || empty($orderid)){
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
		//用户反馈内容
		$contact_arr = array();
		$contacts_mess = Db::table('ct_order_contact')->where(array('orderid'=>$orderid,'otype'=>$act_type))->select();
		if (!empty($contacts_mess)) {
			foreach ($contacts_mess as $key => $value) {
				if ($value['utype']=='1') {
					$user = Db::table('ct_user')->where('uid',$value['userid'])->find();
					$contact_arr[$key]['username'] = $user['realname'];
					$contact_arr[$key]['phone'] = $user['phone'];
					if ($user['image'] == '') {
						$avatar = get_url().'/static/user_header.png';
					}else{
						$avatar = get_url().$user['image'];
					}
				}else{

					$contact_arr[$key]['username'] = '赤途(上海)供应链管理有限公司';
					$contact_arr[$key]['phone'] = '4009-206-101';	
					$avatar = get_url().'/static/service_header.png';
					
				}
				$contact_arr[$key]['image'] = $avatar;
				$contact_arr[$key]['message'] = $value['message'];
				$contact_arr[$key]['utype'] = $value['utype']; //1用户，2平台
				$contact_arr[$key]['addtime'] = $value['addtime'];
			}
		}
		$result['contact_mess'] = $contact_arr;
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}		
		
	}

	//优惠券列表
	public function coupon_list(){
		$token = input('token');
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
			$user_id = $check_result['user_id'];
		}
		$where['userid'] = $user_id;
		$where['failure'] = 1;
		$where['time_end'] = ['GT',time()];
		if ($price !='') {
			$where['description'] = ['ELT',$price];
		}
		$result = Db::table('ct_coupon_user')
					->alias('a')
					->join('ct_coupon b','b.cou_id=a.coup_id')
					->where($where)
					->select();
		$list = Db::table('ct_coupon_user')->where('userid',$user_id)->select();
		if (!empty($list)) {
			foreach ($list as $key => $value) {
				if ($value['failure'] == 1 && $value['time_end'] < time()) {
					$udata['failure'] = 3;
					Db::table('ct_coupon_user')->where(array('userid'=>$user_id))->update($udata);
				}
			}
		}
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
	}

	public function information(){
		$token = input('token');
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
		$result = Db::table('ct_user')->where('uid',$user_id)->find();
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
	}

}


