<?php
namespace app\driver\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request;
use  think\Loader; //加载模型

class Ucenter extends Base{


	/**
	 * 驾驶证认证
	 * @param string token 验证令牌
	 * @param string drivingimage 驾驶证
	 */
	public function auth_driving(){
		$token = input("token");
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
		if(!empty($_FILES['drivingimage']['tmp_name'])){
			$re_travelimg = $this->file_upload('drivingimage','jpg,gif,png,jpeg',"driver");
	        $driv_img = $re_travelimg['file_path']; //源文件地址
	        $up_data['drivingimage'] = $driv_img;
	        $up_data['carstatus'] = 4; // 修改为4认证中
	        Db::table("ct_driver")->where('drivid',$driver_id)->update($up_data);
	        return json(['code'=>'1001','message'=>'提交成功']);
        }else{
        	return json(['code'=>'1002','message'=>'没有数据提交']);
        }

	}

	/**
	 * 司机密码修改
	 * @param string token 验证令牌
	 * @param string old_password 原始密码
	 * @param string new_password 新密码
	 * @param string confirm_password 确认新密码
	 */
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
			$driver_id = $check_result['driver_id'];
		}
		$driver_where['drivid'] = $driver_id;
		$driver_where['password'] = MD5($old_password."ct888");
		$driver_mes = Db::table("ct_driver")->where($driver_where)->find();
		if(!empty($driver_mes)){
			if($new_password  == $confirm_password){
					$upda['password'] = MD5($confirm_password."ct888");
					$upda_res=Db::table("ct_driver")->where('drivid',$driver_id)->update($upda);
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

	/**
	 * 忘记密码
	 * @param string token 验证令牌
	 * @param string mobile 手机号码
	 * @param string yzm_code 验证码
	 * @param string password 新密码
	 * @param string re_password 确认新密码
	 */
	public  function pswd_froget(){
	    $mobile = input('mobile');
        $yzm_code = input('yzm_code');
        $password = input('password');
        $re_password = input('re_password');
        if(empty($mobile)  ||  empty($yzm_code)  || empty($password)  || empty($re_password)){
        	 return json(['code'=>'1000','message'=>'参数错误']);
        }
        //验证手机号是否存在
        $if_exf = Db::table("ct_driver")->where(array("mobile" => $mobile,"delstate"=>'1'))->find();
        if(empty($if_exf)){
            return json(['code'=>'1007','message'=>'用户不存在']);
        }
        //获取发送的验证码
        $record = Db::table("ct_validate_record")->where("phone = $mobile")->find();
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
		$upda_res=Db::table("ct_driver")->where('mobile',$mobile)->update($upda);
		if($upda_res){
			$this->delete_yzm($mobile); //删除验证码记录
			return json(['code'=>'1005','message'=>'找回成功']);
		}else{
			return json(['code'=>'1006','message'=>'找回失败']);
		}
	}

	/**
	 * 更换手机号
	 * @param string token 验证令牌
	 * @param string new_phone 新手机号码
	 * @param string yzm_code 验证码
	 */
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
			$driver_id = $check_result['driver_id'];
		}
        //验证手机号是否存在
        $if_exf = Db::table("ct_driver")->where(array("mobile" => $new_phone,"delstate"=>'1'))->find();
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
        $upda['mobile'] = $new_phone;
		$upda_res=Db::table("ct_driver")->where('drivid',$driver_id)->update($upda);
		if($upda_res){
			  $this->delete_yzm($new_phone); //删除验证码记录
			return json(['code'=>'1005','message'=>'更换成功']);
		}else{
			return json(['code'=>'1006','message'=>'更换失败']);
		}
	}

	/**
	 * 意见反馈
	 * @param string token 验证令牌
	 * @param string content 反馈内容
	 */
	public function feedback(){
		$token = input('token');
		$content = input('content');
		$image =input('image');
        if(empty($token)  ||  empty($content)){
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
		$insert_data = array(
			'type'=>'2', //类型： 1用户端2司机端
			'action_id'=>$driver_id,
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

	/**
     * 记录用户位置
     * @param string token 验证令牌
	 * @param string long 经度
	 * @param string lat 纬度
	 * @param string address 详细地址
     */
    public function user_location(){
        $token = input('token');  //验证令牌
        $long = input('long');  //经度
        $lat = input('lat');  //纬度
        $address = input('address');  //详细地址
        if (empty($token) || empty($long) || empty($lat)) {
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);  //验证令牌
        if ($check_result['status'] == '1') {
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] == '2'){
            return json(['code'=>'1008','message'=>'token已过去，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
       	$data['addtime'] = time();
        $data['longitude'] = $long;
        $data['latitude'] = $lat;
        $data['address'] = $address;
        $data['userid'] = $driver_id;
        $result = Db::table('ct_user_location')->where(array('userid'=>$driver_id))->find();
        if ($result) {
            Db::table('ct_user_location')->where(array('userid'=>$driver_id))->update($data);
            return json(['code'=>'1001','message'=>'记录成功']);
        }else{
            Db::table('ct_user_location')->insert($data);
            return json(['code'=>'1001','message'=>'记录成功']);
        }
    }

    /**
     * 客户上传头像
     * @param string token 验证令牌
	 * @param string picture 头像图片
     */
	public function my_avatar()
	{
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
			$driver_id = $check_result['driver_id'];
		}
		$find_user = Db::table('ct_driver')->field('image')->where('drivid',$driver_id)->find();
		$delpath ='../public'.$find_user['image'];
		$url='';
		if(!empty($_FILES['picture']['tmp_name'])){
        	//回单2
			$re_2 = $this->file_upload('picture','jpg,gif,png,jpeg',"avatar");
	        $data['image'] = $re_2['file_path']; //源文件地址
	        $url = get_url().$re_2['file_path'];
	        if(file_exists($delpath)){
				@unlink($delpath);
			}
        }

        $result = Db::table('ct_driver')->where('drivid',$driver_id)->update($data);
        $find = Db::table('ct_driver')->field('image')->where('drivid',$driver_id)->find();
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

	/**
	 * 修改昵称
	 * @Auther: 李渊
	 * @Date: 2018.8.28
	 * @param  [type] $token 	[用户令牌]
	 * @param  [type] $username [用户昵称]
	 * @return [type]        [description]
	 */
	public function update_username()
	{
		// 用户令牌
		$token = input("token");
		// 真实姓名
		$username = input("username");
		if (empty($token) || empty($username)) {
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
		// 更新用户信息
		$result = Db::table('ct_driver')->where('drivid',$driver_id)->update(["username"=>$username]);
		if($result){
            return json(['code'=>'1001','message'=>'修改成功']);
        }else{
        	return json(['code'=>'1002','message'=>'修改失败']);
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
			$driver_id = $check_result['driver_id'];
		}
		// 更新用户信息
		$result = Db::table('ct_driver')->where('drivid',$driver_id)->update(["realname"=>$realname]);
		if($result){
            return json(['code'=>'1001','message'=>'提交成功']);
        }else{
        	return json(['code'=>'1002','message'=>'提交失败']);
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
	public function update_sex() {
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
			$driver_id = $check_result['driver_id'];
		}
		// 更新用户信息
		$result = Db::table('ct_driver')->where('drivid',$driver_id)->update(["sex"=>$sex]);
		if($result){
            return json(['code'=>'1001','message'=>'修改成功']);
        }else{
        	return json(['code'=>'1002','message'=>'修改失败']);
        }
	}

	/**
	 * 公司车辆信息
	 * @param string token 验证令牌
	 */
	public function company_car(){
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
		$result = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		$car_mess = Db::table('ct_carcategory')
						->alias('c')
						->join('ct_cartype car','car.car_id=c.carid')
						->field('c.carnumber,car.carparame')
						->where(array('c.com_id'=>$result['companyid'],'c.status'=>'2'))
						->select();
		 if($car_mess){
            return json(['code'=>'1001','message'=>'查询成功','data'=>$car_mess]);
        }else{
        	return json(['code'=>'1002','message'=>'暂无数据']);
        }
	}

	/**
	 * 公司司机信息
	 * @param string token 验证令牌
	 */
	public function company_driver(){
		$token   = input("token");  //令牌
		if(empty($token)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
//        var_dump($check_result);

		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		$result = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		$driver_mess = Db::table('ct_driver')
						->field('mobile,realname,drivid')
						->where(array('companyid'=>$result['companyid'],'delstate'=>1,'type'=>1))
						->select();
		 if($driver_mess){
            return json(['code'=>'1002','message'=>'查询成功','data'=>$driver_mess]);
        }else{
        	return json(['code'=>'1001','message'=>'暂无数据']);
        }
	}

	/**
	 * 个人中心-我的钱包-个人钱包余额和运费
	 * 显示司机的余额和运费，余额和运费提现需要扣除费率
	 * 所以返回余额和运费的同时 提现余额和运费的费率也要返回
	 * @Auther: 李渊
	 * @Date: 2018.7.12
	 * @param 	[string] token 验证令牌
	 * @return 	[type] 	[description]
	 */
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
			$driver_id = $check_result['driver_id'];
		}
		// 查询司机信息
		$driver_mes = Db::table("ct_driver")->where('drivid',$driver_id)->find();
		// 查询运费提现费率信息
		$freight_rotate_mes = Db::table("ct_config")->where('id',14)->find();
		// 查询余额提现费率信息
		$balance_rotate_mes = Db::table("ct_config")->where('id',15)->find();
		// 个人余额
		$driver_money['balance'] = number_format($driver_mes['balance'],2);
		// 个人运费
		$driver_money['freight'] = number_format($driver_mes['money'],2);
		// 运费提现费率
		$driver_money['freight_rotate'] = $freight_rotate_mes['auth_price'];
		// 余额提现费率
		$driver_money['balance_rotate'] = $balance_rotate_mes['auth_price'];
		// 返回数据
		return json(['code'=>'1001','message'=>'查询成功','data'=>$driver_money]);
	}

	/**
	 * 余额明细记录
	 * @param string token 验证令牌
	 */
	public function  balance_list(){
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

		$result = Db::table("ct_balance_driver")->where('driver_id',$driver_id)->order('addtime  desc')->paginate(10);
		$list_mes = $result->toArray();
		$list = $list_mes['data'];
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}
	}

	/**
	 * 余额明细记录详情
	 * @param string token 验证令牌
	 */
	public function  balance_detail(){
		$token   = input("token");  //令牌
		$id = input("id"); //明细ID
		if(empty($token) || empty($id)){
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

		$result = Db::table("ct_balance_driver")->where('blid',$id)->find();
		$orderid = $result['orderid'];
		if ($result['ordertype'] == '1') {
			$order = Db::table('ct_order')->where('oid',$orderid)->find();
			$order_mess = "冷链零担";
			$order_number = $order['ordernumber'];
		}
		if ($result['ordertype'] == '2') {
			$order = Db::table('ct_shift_order')->where('s_oid',$orderid)->find();
			$order_mess = "定制线路";
			$order_number = $order['ordernumber'];
		}
		if ($result['ordertype'] == '3') {
			$order = Db::table('ct_city_ordb    er')->where('id',$orderid)->find();
			$order_mess = "冷链城配";
			$order_number = $order['orderid'];
		}
		if ($result['ordertype'] == '4') {
			$order = Db::table('ct_userorder')->where('uoid',$orderid)->find();
			$order_mess = "冷链整车";
			$order_number = $order['ordernumber'];
		}
		if ($result['ordertype'] == '5') {
			$order = Db::table('ct_paymessage')->where('pid',$orderid)->find();
			$order_mess = "支付宝充值";
			$order_number = $order['platformorderid'];
		}
		if ($result['ordertype'] == '6') {
			$order = Db::table('ct_paymessage')->where('pid',$orderid)->find();
			$order_mess = "微信充值";
			$order_number = $order['platformorderid'];
		}
		if ($result['ordertype'] == '7') {
			$order = Db::table('ct_application')->where('id',$orderid)->find();
			$order_mess = "支付宝提现";
			$order_number = $order['alipaynumber'];
		}
		$result['order_mess'] = $order_mess; //商品说明
		$result['order_number'] = $order_number; //订单编号
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}

	}
	public function vipdriver(){
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
            $driver_id = $check_result['driver_id'];
        }
//         $data=Db::table('ct_setting_price')->field('vipprice,vipcount')->where('type',1)->find();
//         $count = Db::table('ct_driver')->field('infocount')->where('drivid',$driver_id)->find();
//         $arr['infocount'] = $data['vipcount'] + $count['infocount'];
//         $re = Db::table('ct_driver')->where('drivid',$driver_id)->update($arr);
//         Db::table('driverorder')->where('driverid',$driver_id)->where('orderstatus',1)->delete();
        //生成code标识
        $code = $this->product_acode($driver_id);
        $arr['code'] = $code;
        $re =  Db::table('ct_driver')->where('drivid',$driver_id)->update($arr);
         if($re){
             return json(array('code'=>'1001','message'=>'修改成功'));
         }else{
             return json(array('code'=>'1002','message'=>'修改失败'));
         }
	}
	public function goldvip(){
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
            $driver_id = $check_result['driver_id'];
        }
        //生成code标识
        $code = $this->product_code($driver_id);
        $arr['code'] = $code;
        $re =  Db::table('ct_driver')->where('drivid',$driver_id)->update($arr);
//        Db::table('driverorder')->where('driverid',$driver_id)->where('orderstatus',1)->delete();
        if($re){
            return json(array('code'=>'1001','message'=>'修改成功'));
        }else{
            return json(array('code'=>'1002','message'=>'修改失败'));
        }

    }
	// 接单 判断城配订单状态是否存在
//    public function isdelivery(){
//        $token = input('token'); // 令牌
//        $id  = input('uoid');//订单编号
//        $level = input('level');//会员等级
//        if(empty($token) || empty($id)){
//            return json(['code'=>'1000','message'=>'参数错误']);
//        }
//        $check_result = $this->check_token($token);//验证令牌
//
//        if($check_result['status'] =='1'){
//            return json(['code'=>'1007','message'=>'非法请求']);
//        }elseif($check_result['status'] =='2'){
//            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
//        }else{
//            $driver_id = $check_result['driver_id'];
//        }
//        $res = Db::table('ct_delivery_order')->where('driverid',$driver_id)->where('uoid',$id)->where('orderstatus',2)->find();
//        if($level == 1){
//
//            if ($res){
//                $userid = Db::table('ct_delivery')->field('userid')->where('uoid',$id)->find();
//                $userinfo = Db::table('ct_user')->field('username,phone')->where('uid',$userid['userid'])->find();
//                return json(['code'=>'1001','message'=>'查询成功','data'=>$userinfo]);
//            }else{
//                return json(['code'=>'1002','message'=>'暂无数据']);
//            }
//        }elseif($level == 2){
//
//            if ($res){
//                $userid = Db::table('ct_delivery')->field('userid')->where('uoid',$id)->find();
//                $userinfo = Db::table('ct_user')->field('username,phone')->where('uid',$userid['userid'])->find();
//                return json(['code'=>'1001','message'=>'查询成功','data'=>$userinfo]);
//            }else{
//                return json(['code'=>'1002','message'=>'暂无数据']);
//            }
//        }elseif($level == 3){
//            if($res){
//                $userid = Db::table('ct_delivery')->field('userid')->where('uoid',$id)->find();
//                $userinfo = Db::table('ct_user')->field('username,phone')->where('uid',$userid['userid'])->find();
//                return json(['code'=>'1001','message'=>'查询成功','data'=>$userinfo]);
//            }else{
//                return json(['code'=>'1002','message'=>'暂无数据']);
//            }
//        }
//    }
    public function isdelivery(){
        $token = input('token'); // 令牌
        $id  = input('uoid');//订单编号
        $level = input('level');//会员等级
        if(empty($token) || empty($id)){
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
        $res = Db::table('ct_delivery_order')->where('driverid',$driver_id)->where('uoid',$id)->where('orderstatus',2)->find();
        if ($res){
                $userid = Db::table('ct_delivery')->field('userid')->where('uoid',$id)->find();
                $userinfo = Db::table('ct_user')->field('username,phone')->where('uid',$userid['userid'])->find();
                return json(['code'=>'1001','message'=>'查询成功','data'=>$userinfo]);
            }else{
                return json(['code'=>'1002','message'=>'暂无数据']);
            }

    }
	//接单 判断整车订单状态是否存在
    public function isorder(){
	    $token = input('token'); // 令牌
        $id  = input('uoid');//订单编号
        $level = input('level');//会员等级
        if(empty($token) || empty($id)){
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
        $res = Db::table('ct_driverorder')->where('driverid',$driver_id)->where('uoid',$id)->where('orderstatus',2)->find();
        if($level == 1){

            if ($res){
                $userid = Db::table('ct_useorder')->field('userid')->where('uoid',$id)->find();
                $userinfo = Db::table('ct_user')->field('username,phone')->where('uid',$userid['userid'])->find();
                return json(['code'=>'1001','message'=>'查询成功','data'=>$userinfo]);
            }else{
                return json(['code'=>'1002','message'=>'暂无数据']);
            }
        }elseif($level == 2){

            if ($res){
                $userid = Db::table('ct_useorder')->field('userid')->where('uoid',$id)->find();
                $userinfo = Db::table('ct_user')->field('username,phone')->where('uid',$userid['userid'])->find();
                return json(['code'=>'1001','message'=>'查询成功','data'=>$userinfo]);
            }else{
                return json(['code'=>'1002','message'=>'暂无数据']);
            }
        }elseif($level == 3){
            if($res){
                $userid = Db::table('ct_useorder')->field('userid')->where('uoid',$id)->find();
                $userinfo = Db::table('ct_user')->field('username,phone')->where('uid',$userid['userid'])->find();
                return json(['code'=>'1001','message'=>'查询成功','data'=>$userinfo]);
            }else{
                return json(['code'=>'1002','message'=>'暂无数据']);
            }
        }
	}
	/*
	 * 零担判断订单是否存在
	 * */
	public function isbulk(){
        $token = input('token'); // 令牌
        $id  = input('id');//订单编号
        $level = input('level');//会员等级
        if(empty($token) || empty($id)){
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
        $res = Db::table('ct_bulk_order')->where('driverid',$driver_id)->where('oid',$id)->where('orderstate',2)->find();

            if($res){
                return json(['code'=>'1001','message'=>'查询成功']);
            }else{
                return json(['code'=>'1002','message'=>'暂无数据']);
            }

    }
	//会员是否过期
    public function overdue(){
        $token = input('token'); // 令牌
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
        $arr = Db::table('ct_driver')->field('level')->where('drivid',$driver_id)->find();
        if ($arr['level'] == 2){
            $driver = Db::table('ct_driver_code')->field('duetime')->where('driver_id',$driver_id)->find();
            $endtime =date('Y-m-d H:i:s',$driver['duetime']);
            $userinfo['level'] = 2;
            $userinfo['endtime'] = $endtime;
            return json(['code'=>'1001','message'=>'查询成功','data'=>$userinfo]);
        }elseif($arr['level'] == 3){
            $driver = Db::table('ct_driver_code')->field('duetime')->where('driver_id',$driver_id)->find();
            $endtime =date('Y-m-d H:i:s',$driver['duetime']);
//            $endtime = $driver['duetime'];
            $userinfo['level'] = 3;
            $userinfo['endtime'] = $endtime;
            return json(['code'=>'1001','message'=>'查询成功','data'=>$userinfo]);
        }
        return json(['code'=>'1001','message'=>'查询成功','data'=>$arr]);
    }
	//查看用户信息等级 2019.5.27
     public function  driverLevel(){
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
         $re = Db::table('ct_driver')->field('level')->where('drivid',$driver_id)->find();
//            if ($re['level'] == 2){
//                $arr = Db::table('ct_driver')->field('infocount')->where('drivid',$driver_id)->find();
//                if ($arr['infocount'] <= 0){
//                    $view['level'] = 1;
//                    $view['infocount'] = 0;
//                     Db::table('ct_driver')->where('drivid',$driver_id)->update($view);
//                    $level = Db::table('ct_driver')->field('level')->where('drivid',$driver_id)->find();
//                    return json(['code'=>'1001','message'=>'查询成功','data'=>$level]);
//                }else{
//                    return json(['code'=>'1001','message'=>'查询成功','data'=>$re]);
//                }
//            }
            if($re['level'] == 3 || $re['level'] == 2){
                $arr = Db::table('ct_driver')->field('code')->where('drivid',$driver_id)->find();
                $check = $this->check_code($arr['code']);
                if ($check['status']=='2'){
                    Db::table('ct_driver')->field('code')->where('drivid',$driver_id)->delete();
                    Db::table('ct_driver_code')->field('*')->where('drivid',$driver_id)->delete();
                    $view['level'] = 1;
                     Db::table('ct_driver')->where('drivid',$driver_id)->update($view);
                    $level = Db::table('ct_driver')->field('level')->where('drivid',$driver_id)->find();
                    return json(['code'=>'1001','message'=>'查询成功','data'=>$level]);
                }else{
                    return json(['code'=>'1001','message'=>'查询成功','data'=>$re]);
                }
            }elseif($re['level'] == 1){
                return json(['code'=>'1001','message'=>'查询成功','data'=>$re]);
            }

     }
     /*
      * 根据会员等级添加零担订单
      * */
     public function blevel(){
         $level = input('level');
         $token = input('token');
         $id = input('oid');
         if(empty($token)||empty($id)){
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
         if($level == 1){
             return json(['code'=>'1001','message'=>'普通会员，请充值','data'=>'1']);
         } elseif($level == 2){
             $count = Db::table('ct_driver')->field('infocount')->where('drivid',$driver_id)->where('level',$level)->find();
             if($count['infocount']<= 0 ){
                 return json(['code'=>'1009','message'=>'vip会员已过期']);
             }
             $surplus['infocount'] = $count['infocount'] - 1;

             Db::table('ct_driver')->field('infocount')->where('drivid',$driver_id)->where('level',$level)->update($surplus);
             //查找订单
             $res = Db::table('ct_order')->field('*')->where('oid',$id)->find();
             //添加到司机订单表

             $re = Db::table('ct_bulk_order')->where('driverid',$driver_id)->where('oid',$id)->where('orderstate',2)->find();
             if ($re){
                 return json(['code'=>'1001','message'=>'查询成功']);
             }else{
                 $list['driverid'] = $driver_id;
                 $list['ordernumber']= $res['ordernumber'];
                 $list['oid'] = $res['oid'];
                 $list['addtime'] = time();
                 $list['slogid'] = $res['slogid'];
                 $list['userid']= $res['userid'];
                 $list['coldtype'] = $res['coldtype'];
                 $list['totalnumber'] = $res['totalnumber'];
                 $list['totalweight'] = $res['totalweight'];
                 $list['itemtype'] = $res['itemtype'];
                 $list['picktime'] = $res['picktime'];
                 $list['orderstate'] = 2;
                 $list['remark'] = $res['remark'];
                 $list['totalvolume'] = $res['totalvolume'];
                 $list['lineprice'] = $res['linepice'];
                 $list['pickcost'] = $res['pickcost'];
                 $list['delivecost'] = $res['delivecost'];
                 $list['usercheck'] = $res['usercheck'];
                 $list['serviceid'] = $res['serviceid'];
                 $list['paystate'] = $res['paystate'];
                 $list['pickaddress'] = $res['pickaddress'];
                 $list['sendaddress'] = $res['sendaddress'];
                 $list['user_checkid'] = $res['user_checkid'];
                 $list['receipt'] = $res['receipt'];
                 $list['all_price'] = $res['all_price'];
                 $list['shiftid'] = $res['shiftid'];
                 $list['arrtime'] = $res['arrtime'];
                 $list['starttime'] = $res['starttime'];
                 $list['carr_upprice'] = $res['carr_upprice'];
                 $list['pay_type'] = $res['pay_type'];
                 $list['arrivetime'] = $res['arrivetime'];
                 $list['withdraw'] = $res['withdraw'];
                 $list['picksite'] = $res['picksite'];
                 $list['stime'] = $res['stime'];
                 $list['sphone'] = $res['sphone'];
                 $list['sendsite'] = $res['dtime'];
                 $list['tphone'] = $res['tphone'];
                 $arr = Db::table('ct_bulk_order')->insert($list);
                 if($arr){
                     return json(['code'=>'1001','message'=>'查询成功']);
                 }
             }
         }elseif($level == 3){
             $code = Db::table('ct_driver')->field('code')->where('drivid',$driver_id)->where('level',$level)->find();
             if(empty($code['code'])){
                 return json(['code'=>'1000','message'=>'参数错误']);
             }
             $check = $this->check_code($code['code']);//验证code
             if ($check['status'] =='1'){
                 return json(['code'=>'1007','message'=>'非法请求']);
             }elseif($check['status']=='2'){
                 return json(['code'=>'1009','message'=>'黄金会员已过期']);
             }
             //查找订单
             $res = Db::table('ct_order')->field('*')->where('oid',$id)->find();
             $re = Db::table('ct_bulk_order')->where('driverid',$driver_id)->where('oid',$id)->where('orderstate',2)->find();
             if($re){
                 return json(['code'=>'1001','message'=>'查询成功']);
             }else{
                 //添加到司机订单表
                 $list['driverid'] = $driver_id;
                 $list['ordernumber']= $res['ordernumber'];
                 $list['oid'] = $res['oid'];
                 $list['addtime'] = time();
                 $list['slogid'] = $res['slogid'];
                 $list['userid']= $res['userid'];
                 $list['coldtype'] = $res['coldtype'];
                 $list['totalnumber'] = $res['totalnumber'];
                 $list['totalweight'] = $res['totalweight'];
                 $list['itemtype'] = $res['itemtype'];
                 $list['picktime'] = $res['picktime'];
                 $list['orderstate'] = 2;
                 $list['remark'] = $res['remark'];
                 $list['totalvolume'] = $res['totalvolume'];
                 $list['lineprice'] = $res['linepice'];
                 $list['pickcost'] = $res['pickcost'];
                 $list['delivecost'] = $res['delivecost'];
                 $list['usercheck'] = $res['usercheck'];
                 $list['serviceid'] = $res['serviceid'];
                 $list['paystate'] = $res['paystate'];
                 $list['pickaddress'] = $res['pickaddress'];
                 $list['sendaddress'] = $res['sendaddress'];
                 $list['user_checkid'] = $res['user_checkid'];
                 $list['receipt'] = $res['receipt'];
                 $list['all_price'] = $res['all_price'];
                 $list['shiftid'] = $res['shiftid'];
                 $list['arrtime'] = $res['arrtime'];
                 $list['starttime'] = $res['starttime'];
                 $list['carr_upprice'] = $res['carr_upprice'];
                 $list['pay_type'] = $res['pay_type'];
                 $list['arrivetime'] = $res['arrivetime'];
                 $list['withdraw'] = $res['withdraw'];
                 $list['picksite'] = $res['picksite'];
                 $list['stime'] = $res['stime'];
                 $list['sphone'] = $res['sphone'];
                 $list['sendsite'] = $res['dtime'];
                 $list['tphone'] = $res['tphone'];
                 $arr = Db::table('ct_bulk_order')->insert($list);
                 if($arr){
                     return json(['code'=>'1001','message'=>'查询成功']);
                 }
             }
         }
     }
     //城配根据会员等级执行相应操作 2019.6.3
    public function dLevel(){
        $level = input('level');
        $token = input('token');
        $id = input('uoid');
        if(empty($token)||empty($id)){
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
        if($level == 1){
            return json(['code'=>'1001','message'=>'普通会员，请充值','data'=>'1']);
        } elseif($level == 2){
            $count = Db::table('ct_driver')->field('infocount')->where('drivid',$driver_id)->where('level',$level)->find();
            if($count['infocount']<= 0 ){
                return json(['code'=>'1009','message'=>'vip会员已过期']);
            }
            $surplus['infocount'] = $count['infocount'] - 1;

            Db::table('ct_driver')->field('infocount')->where('drivid',$driver_id)->where('level',$level)->update($surplus);
            //查找订单
            $res = Db::table('ct_delivery')->field('*')->where('uoid',$id)->find();
            //添加到司机订单表

            $re = Db::table('ct_delivery_order')->where('driverid',$driver_id)->where('uoid',$id)->find();
            if ($re){
                return json(['code'=>'1001','message'=>'查询成功']);
            }else{
                $list['ordernumber']= $res['ordernumber'];
                $list['uoid'] = $res['uoid'];
                $list['createtime'] = time();
                $list['pickyesno'] = $res['pickyesno'];
                $list['sendyesno']= $res['sendyesno'];
                $list['temperture'] = $res['temperture'];
                $list['carid'] = $res['carid'];
                $list['handingmode'] = $res['handingmode'];
                $list['goodsname'] = $res['goodsname'];
                $list['picktime'] = $res['picktime'];
                $list['orderstatus'] = 2;
                $list['remark'] = $res['remark'];
                $list['fprice'] = $res['fprice'];
                $list['startcity'] = $res['startcity'];
                $list['weight'] = $res['weight'];
                $list['volume'] = $res['volume'];
                $list['price'] = $res['price'];
                $list['driverid'] = $driver_id;
                $list['paytype'] = $res['paytype'];
                $list['taddress'] = $res['taddress'];
                $list['paddress'] = $res['paddress'];
                $list['orderstatus'] = 2;
                $arr = Db::table('ct_delivery_order')->insert($list);
                if($arr){
                    return json(['code'=>'1001','message'=>'查询成功']);
                }
            }
        }elseif($level == 3){
            $code = Db::table('ct_driver')->field('code')->where('drivid',$driver_id)->where('level',$level)->find();
            if(empty($code['code'])){
                return json(['code'=>'1000','message'=>'参数错误']);
            }
            $check = $this->check_code($code['code']);//验证code
            if ($check['status'] =='1'){
                return json(['code'=>'1007','message'=>'非法请求']);
            }elseif($check['status']=='2'){
                return json(['code'=>'1009','message'=>'黄金会员已过期']);
            }
            //查找订单
            $res = Db::table('ct_delivery')->field('*')->where('uoid',$id)->find();
            $re = Db::table('ct_delivery_order')->where('driverid',$driver_id)->where('uoid',$id)->find();
            if($re){
                return json(['code'=>'1001','message'=>'查询成功']);
            }else{
                //添加到司机订单表
                $list['ordernumber']= $res['ordernumber'];
                $list['uoid'] = $res['uoid'];
                $list['createtime'] = time();
                $list['pickyesno'] = $res['pickyesno'];
                $list['sendyesno']= $res['sendyesno'];
                $list['temperture'] = $res['temperture'];
                $list['carid'] = $res['carid'];
                $list['handingmode'] = $res['handingmode'];
                $list['goodsname'] = $res['goodsname'];
                $list['picktime'] = $res['picktime'];
                $list['orderstatus'] = 2;
                $list['remark'] = $res['remark'];
                $list['fprice'] = $res['fprice'];
                $list['startcity'] = $res['startcity'];
                $list['weight'] = $res['weight'];
                $list['volume'] = $res['volume'];
                $list['price'] = $res['price'];
                $list['driverid'] = $driver_id;
                $list['paytype'] = $res['paytype'];
                $list['taddress'] = $res['taddress'];
                $list['paddress'] = $res['paddress'];
                $list['orderstatus'] = 2;
                $arr = Db::table('ct_delivery_order')->insert($list);
                if($arr){
                    return json(['code'=>'1001','message'=>'查询成功']);
                }
            }

        }
    }

     //整车根据会员等级执行相应操作 2019.5.28
    public function level(){
	    $level = input('level');
	    $token = input('token');
	    $id = input('uoid');
        if(empty($token)||empty($id)){
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
        if($level == 1){
           $arr =  Db::table('ct_driver')->field('deposit')->where('drivid',$driver_id)->find();
           $data['deposit'] = $arr['deposit'];
            return json(['code'=>'1001','message'=>'充值押金','data'=>$data]);
        } elseif($level == 2){
            $code = Db::table('ct_driver')->field('code')->where('drivid',$driver_id)->where('level',$level)->find();
            if(empty($code['code'])){
                return json(['code'=>'1000','message'=>'参数错误']);
            }
            $check = $this->check_code($code['code']);//验证code
            if ($check['status'] =='1'){
                return json(['code'=>'1007','message'=>'非法请求']);
            }elseif($check['status']=='2'){
                return json(['code'=>'1009','message'=>'年卡已过期']);
            }
             $ispay['ispay'] = 2;
             $res= Db::table('ct_userorder')->field('ispay')->where('uoid',$id)->update($ispay);

            if ($res){
                return json(['code'=>'1001','message'=>'下单成功','data'=>'2']);
            }else{
                return json(['code'=>'1002','message'=>'请勿重复提交']);
            }
        }elseif($level == 3){
            $code = Db::table('ct_driver')->field('code')->where('drivid',$driver_id)->where('level',$level)->find();
            if(empty($code['code'])){
                return json(['code'=>'1000','message'=>'参数错误']);
            }
            $check = $this->check_code($code['code']);//验证code
            if ($check['status'] =='1'){
                return json(['code'=>'1007','message'=>'非法请求']);
            }elseif($check['status']=='2'){
                return json(['code'=>'1009','message'=>'年卡已过期']);
            }
            $ispay['ispay'] = 2;
            //查找订单
            $res = Db::table('ct_userorder')->field('ispay')->where('uoid',$id)->update($ispay);
             if ($res){
                return json(['code'=>'1001','message'=>'下单成功','data'=>'3']);
            }else{
                return json(['code'=>'1002','message'=>'请勿重复提交']);
            }
        }
    }

     //查询整车会员权益 2019.5.27
    public function equity(){
    $token = input('token');
    $type = input('type');
    if(empty($token) || empty($type)){
        return json(['code'=>'1000','message'=>'参数错误']);
    }
    $check_result = $this->check_token($token);//验证令牌
    if($check_result['status'] == '1'){
        return json(['code'=>'1007','message'=>'非法请求']);
    }elseif($check_result['status']=='2'){
        return json(['code'=>'1008','message'=>'token已过期，请重新登陆']);
    }else{
        $driver_id = $check_result['driver_id'];
    }
    $res = Db::table('ct_setting_price')->field('pledge,viewprice,vipprice,vipcount,yearly')->where('type',$type)->select();
    if ($res){
        return json(['code'=>'1001','message'=>'请求成功','data'=>$res]);
    }else{
        return json(['code'=>'1002','message'=>'暂无数据']);
    }
}
    public function cityequity(){
        $token = input('token');
        $type = input('type');
        if(empty($token) || empty($type)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] == '1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status']=='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登陆']);
        }else{
            $driver_id = $check_result['driver_id'];
        }

        $res = Db::table('ct_setting_price')->field('pledge,viewprice,vipprice,vipcount,yearly')->where('type',$type)->select();

        if ($res){
            return json(['code'=>'1001','message'=>'请求成功','data'=>$res]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }

    //个人中心整车订单列表
    public function allorder(){
        $token   = input("token");  //令牌
        // 验证用户令牌
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

        // 查询条件 除了未接单以外的所有订单
        $result = Db::table("ct_driverorder")
            ->alias('u')
            ->join('__CARTYPE__ c',"u.carid = c.car_id",'left')
            ->field('u.uoid,u.ordernumber,u.price,u.pay_type,u.picktime,u.handingmode,u.temperture,u.orderstatus,
					u.startcity,u.endcity,c.carparame')
            ->order('u.ordertime desc')
            ->where('driverid',$driver_id)
            ->where('orderstatus',2)
            ->paginate(10);
        $list_mes = $result->toArray();
        $list = $list_mes['data'];

        // 遍历数据

        if(empty($list)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            foreach ($list as $key => $value) {
                // 起点城市
                $list[$key]['startcity'] = $value['startcity'];
                // 终点城市
                $list[$key]['endcity'] = $value['endcity'];
                // 车型
                $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';
                // 承运价
                $var_price = $value['price'];
                // 返回价格并取整
                $list[$key]['price'] = round($var_price);
                // 返回支付类型
                $list[$key]['pay_type'] = $value['pay_type'];

            }
            return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
        }
    }


    //个人中心整车订单详情
    public function orderview(){
        // 订单ID
        $uoid = input("uoid");
        $token   = input("token");  //令牌
        // 验证用户令牌
        if(empty($token)||empty($uoid)){
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
        // 查询数据
        $detail = Db::table("ct_driverorder")
            ->alias('o')
            ->join('__CARTYPE__ c','o.carid = c.car_id','left')
            ->field('o.*,c.carparame')
            ->where('o.uoid',$uoid)
            ->where('o.driverid',$driver_id)
            ->find();
        //下单人电话
        $id = Db::table('ct_useorder')->field('userid')->where('uoid',$uoid)->find();
        $userinfo = Db::table('ct_user')->field('username,phone')->where('uid',$id['userid'])->find();
        // 判断是否有数据
        if(empty($detail)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            // 整车运费
            $price = $detail['price'];
            // 返回运费并取整
            $detail['price'] = round($price);
            // 起点城市
            $detail['startcity'] = $detail['startcity'];
            // 终点城市
            $detail['endcity'] = $detail['endcity'];
            // 车型
            $detail['carparame'] = $detail['carparame'] ? $detail['carparame'] : '12.5米-15米冷藏箱车';
            // 发货地
            $detail['pickaddress'] = json_decode($detail['pickaddress']);
            // 卸货地
            $detail['sendaddress'] = json_decode($detail['sendaddress']);
            $detail['username']= $userinfo['username'];
            $detail['phone'] = $userinfo['phone'];
            return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
        }
    }
    //个人中心城配订单列表
    public function delivery_order(){
        $token   = input("token");  //令牌
        // 验证用户令牌
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


        // 查询条件 除了未接单以外的所有订单
        $result = Db::table("ct_delivery_order")
            ->alias('u')
            ->join('__CARTYPE__ c',"u.carid = c.car_id",'left')
            ->field('u.uoid,u.ordernumber,u.price,u.paytype,u.picktime,u.handingmode,u.temperture,u.orderstatus,
					u.startcity,c.carparame')
            ->order('u.createtime desc')
            ->where('driverid',$driver_id)
            ->where('orderstatus',2)
            ->paginate(10);
        $list_mes = $result->toArray();
        $list = $list_mes['data'];

        // 遍历数据

        if(empty($list)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            foreach ($list as $key => $value) {
                // 起点城市
                $list[$key]['startcity'] = $value['startcity'];

                // 车型
                $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';
                // 承运价
                $var_price = $value['price'];
                // 返回价格并取整
                $list[$key]['price'] = round($var_price);
                // 返回支付类型
                $list[$key]['paytype'] = $value['paytype'];

            }
            return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
        }
    }
    //个人中心城配订单详情
    public function delivery_view(){
       // 订单ID
        $uoid = input("uoid");
        $token   = input("token");  //令牌
        // 验证用户令牌
        if(empty($token)||empty($uoid)){
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
        // 查询数据
        $detail = Db::table("ct_delivery_order")
            ->alias('o')
            ->join('__CARTYPE__ c','o.carid = c.car_id','left')
            ->field('o.*,c.carparame')
            ->where('o.uoid',$uoid)
            ->where('o.driverid',$driver_id)
            ->find();
        //下单人电话
        $id = Db::table('ct_delivery')->field('userid')->where('uoid',$uoid)->find();
        $userinfo = Db::table('ct_user')->field('username,phone')->where('uid',$id['userid'])->find();
        // 判断是否有数据
        if(empty($detail)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            // 整车运费
            $price = $detail['price'];
            // 返回运费并取整
            $detail['price'] = round($price);
            // 起点城市
            $detail['startcity'] = $detail['startcity'];

            // 车型
            $detail['carparame'] = $detail['carparame'] ? $detail['carparame'] : '12.5米-15米冷藏箱车';
            // 发货地
            $detail['taddress'] = json_decode($detail['taddress']);
            // 卸货地
            $detail['paddress'] = json_decode($detail['paddress']);
            $detail['username']= $userinfo['username'];
            $detail['phone'] = $userinfo['phone'];
            return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
        }
    }

	/**
	 * 个人中心-订单列表 订单分派司机和车牌号
	 * @Auther: 李渊
	 * @Date: 2018.8.1
	 * @param  [type] $token 		[令牌]
	 * @param  [type] $act_type 	[订单类型 1:提货订单 2:干线订单  3:配送订单  4:整车订单  5:市内配送订单]
	 * @param  [type] $orderid 		[订单ID]
	 * @param  [type] $driverid 	[司机ID]
	 * @param  [type] $realname 	[司机名称]
	 * @param  [type] $mobile 		[联系方式]
	 * @param  [type] $carnumber 	[车牌号]
	 * @return [type]        [description]
	 */
	public function allocation(){
		// 令牌
		$token   = input("token");
		// 订单类型 1:提货订单 2:干线订单  3:配送订单  4:整车订单  5:市内配送订单
		$act_type = input('act_type');
		// 订单ID
		$orderid = input('orderid');
		// 司机ID
		$driverid = input('driverid');
		// 司机名称
		$realname = input('realname');
		// 联系方式
		$mobile = input('mobile');
		// 车牌号
		$carnumber = input('carnumber');
		// 判断参数是否正确
		if(empty($token) || empty($act_type) || empty($orderid) || empty($realname) || empty($mobile) || empty($carnumber)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 验证令牌
		$check_result = $this->check_token($token);
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		// 判断订单类型
		switch ($act_type) {
			case '1': // 提货订单
				$search = DB::table('ct_pickorder')
							->alias('a')
							->join('ct_order o','a.orderid = o.oid')
							->join('ct_shift s','o.shiftid = s.sid')
							->join('ct_already_city c','c.city_id = s.linecityid')
							->field('o.ordernumber,c.start_id,c.end_id,o.starttime')
							->find();
				$startcity = detailadd($search['start_id'],'','');
				$endcity = detailadd($search['end_id'],'','');
				$data_pick = array(
					'drivername'=>$realname,
					'driverphone'=>$mobile,
					'carlicense'=>$carnumber,
					'sallotid'=>$driverid
					);
				$content = "尊敬的客户：调度已向您指派零担订单,编号为:".$search['ordernumber'].'发货时间为'.date('Y-m-d',$search['starttime'])."从".$startcity."发往".$endcity;
				//send_sms_class($mobile,$content);
				$order = Db::table('ct_pickorder')->where('picid',$orderid)->update($data_pick);
				break;
			case '2': // 干线订单
				$data_line = array(
					'lname'=>$realname,
					'lmobile'=>$mobile,
					'lcarnumber'=>$carnumber,
					'lallotid'=>$driverid
					);
				$order = Db::table('ct_lineorder')->where('lid',$orderid)->update($data_line);
				break;
			case '3': // 配送订单
				$data_send = array(
					'pname'=>$realname,
					'pmobile'=>$mobile,
					'pcarnumber'=>$carnumber,
					'pallotid'=>$driverid
					);
				$order = Db::table('ct_delorder')->where('deid',$orderid)->update($data_send);
				break;
			case '4': // 整车订单
				$search = Db::table('ct_userorder')->field('startcity,endcity,loaddate,ordernumber')->where('uoid',$orderid)->find();
				// 查询起点城市
				$startcity = addresidToName($search['startcity']);
				// 查询终点城市
				$endcity = addresidToName($search['endcity']);
				// 发货时间
				$sendtime = floor($search['loaddate']/1000);
				// 短信内容
				$content = "尊敬的客户：调度已向您指派整车订单,编号为:".$search['ordernumber'].'发货时间为'.date('Y-m-d',$sendtime)."从".$startcity."发往".$endcity;
				// 更新数据
				$data_carload = array(
					'drivername'=>$realname,
					'driverphone'=>$mobile,
					'carlicense'=>$carnumber,
					'driverid'=>$driverid
					);
				// 更新运输司机和车牌
				$order = Db::table('ct_userorder')->where('uoid',$orderid)->update($data_carload);
				break;
			case '5': // 市内配送订单
				$search = Db::table('ct_rout_order')->alias('r')->join('ct_city_order o','o.rout_id = r.rid')->field('o.data_type,o.orderid,o.city_id')->where('id',$orderid)->find();
				// 城配城市
				$startcity = addresidToName($search['city_id']);
				// 短信内容
				$content = "尊敬的客户：调度已向您指派".$startcity."内城配订单,编号为:".$search['orderid']."配送时间为:".$search['data_type']."请你登录赤途承运端APP查看配送信息";
				// 更新数据
				$data_city = array(
					'drivername'=>$realname,
					'driverphone'=>$mobile,
					'carlicense'=>$carnumber,
					'allotid'=>$driverid
					);
				// 更新司机联系方式和车牌
				$order = Db::table('ct_rout_order')->alias('r')->join('ct_city_order o','o.rout_id = r.rid')->where('id',$orderid)->update($data_city);
				break;
			default:
				# code...
				break;
		}
		// 判断是否分派成功
		if($order){
			// 发送短信
			send_sms_class($mobile,$content);
			// 返回状态
			return json(['code'=>'1002','message'=>'提交成功']);
		}else{
			return json(['code'=>'1001','message'=>'提交失败']);
		}
	}

	/**
	 * 个人中心-订单列表-整车订单列表
	 * @param string token 验证令牌
	 */
	public function  vehical_list(){
		$token  = input("token");  //令牌
		// 验证用户令牌
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
		// 查询登陆承运app的承运信息
		$driver_mes = Db::table("ct_driver")->where('drivid',$driver_id)->find();
		// 判断登陆人的属性
		if ($driver_mes['type'] == '3') { // 登陆用户是管理员
			$userid = Db::table('ct_driver')->where(array('companyid'=>$driver_mes['companyid'],'type'=>['neq','1']))->select();
	 		$uid = '';
	 		foreach ($userid as $key => $value) {
	 			$uid .= $value['drivid'].',';
	 		}
	 		$uid =  substr($uid,0,strlen($uid)-1);
	 		$condition['u.carriersid'] = array('in',$uid);
	 		$driver_id=['NEQ',''];
		}
		// 查询条件 除了未接单以外的所有订单
		$condition['u.orderstate'] =  ['NEQ','1'];
		$result = Db::table("ct_userorder")
				->alias('u')
				->join('__CARTYPE__ c',"u.carid = c.car_id",'left')
				->field('u.uoid,u.carnum,u.order_type,u.fprice,u.down_price,u.ordernumber,u.price,u.paystate,u.carr_upprice,u.type,u.arrtime,u.loaddate,u.temperture,u.arrivetime,u.orderstate,
					u.startcity,u.startarea,u.endarea,u.endcity,c.carparame,u.drivername,u.carlicense,u.pickaddress,u.sendaddress')
				->order('u.addtime desc')
				->where($condition)
				->where(function($query) use($driver_id){
			        $query->where(array('u.driverid'=>$driver_id));
			        $query->whereOr(array('u.carriersid'=>$driver_id));
			    })
				->paginate(10);
		$list_mes = $result->toArray();
		$list = $list_mes['data'];
        // 遍历数据
		foreach ($list as $key => $value) {
			// 起点城市
			$list[$key]['startcity'] = $value['startcity'];
            // 终点城市
			$list[$key]['endcity'] = $value['endcity'];
            // 车型
            $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';
			// 承运价
			$var_price = $value['carr_upprice']=='' ? $value['fprice'] : $value['carr_upprice'];
			// 返回价格并取整
			$list[$key]['price'] = round($var_price);
            // 返回支付类型
			$list[$key]['type'] = $value['type'];
            // 判断是否派单
			$list[$key]['driverstate'] = 1;
			$list[$key]['order_type'] = $value['order_type'];
			$list[$key]['down_price'] = $value['down_price'];
			$list[$key]['tprice'] = round($value['price']);
			$list[$key]['carnum'] = $value['carnum'];
			if ($value['drivername'] !='' ||  $value['carlicense'] !='') {
				$list[$key]['driverstate'] = 2;
			}
		}
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}
	}
/*
 * 取消订单
 * */
        public function vehical_cancle(){
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
                $driver_id = $check_result['driver_id'];
            }
            $arr = Db::table('ct_userorder')->field('orderstate')->where('uoid',$uoid)->find();
            if ($arr['orderstate']=='2' ){
                $status = 1;
                $ispay = 1;
                $data['orderstate'] = $status;
                $data['ispay'] = $ispay;
                $data['carlicense'] = '';
                $data['drivername'] = '';
                $data['driverphone'] = '';
                $res =  Db::table('ct_userorder')->field('orderstate,ispay,carlicense,drivername,driverphone')->where('uoid',$uoid)->update($data);
            }

            if($res){
                return json(['code'=>'1001','message'=>'取消成功']);
            }else{
                return json(['code'=>'1002','message'=>'取消失败']);
            }

       }
	/**
	* 我的承接：整车订单详情
	* @param string token 验证令牌
	* @param string uoid 订单ID
	*/
	public function  vehical_detail(){
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
			$driver_id = $check_result['driver_id'];
		}
		$detail = Db::table("ct_userorder")
				->alias('o')
				->join('__CARTYPE__ c','o.carid = c.car_id','left')
				->field('o.*,c.carparame')
				->where('o.uoid',$uoid)
				->find();
		// 承运价格
		$var_price = $detail['carr_upprice'] =='' ? $detail['price'] : $detail['carr_upprice'];
		// 返回承运价并取整
		$detail['price'] = round($var_price);
		// 查询下单人信息
		$user_search = Db::table("ct_user")->where('uid',$detail['userid'])->find();
		// 下单人姓名
		$detail['realname'] = $user_search['realname'] ? $user_search['realname'] :$user_search['username'];
		// 下单人电话
		$detail['phone'] = $user_search['phone'];
		// 下单人图像
		if ($user_search['image'] =='') {
			$detail['image'] = get_url().'/static/user_header.png';
		}else{
			$detail['image'] = get_url().$user_search['image'];
		}
		// 起点城市
		$detail['startcity'] = $detail['startcity'];
		// 终点城市
		$detail['endcity'] = $detail['endcity'];
		// 发货地址
		$detail['saddress'] = json_decode($detail['pickaddress']);
		// 配送地址
		$detail['paddress'] = json_decode($detail['sendaddress'],TRUE);
        // 车型
        $detail['carparame'] = $detail['carparame'] ? $detail['carparame'] : '12.5米-15米冷藏箱车';;
		// 回单
		$receipts = json_decode($detail['receipts'],TRUE);
		$array = array();
		if (!empty($receipts)) {
			foreach ($receipts as $key => $value) {
				$array[$key]['key'] = $key;
				$array[$key]['value'] = $value;
			}
		}
		$detail['receipts'] = $array;

		//用户反馈内容
		$contact_arr = array();
		$contacts_mess = Db::table('ct_order_contact')->where(array('orderid'=>$detail['uoid'],'utype'=>'1','otype'=>'4'))->select();
		if (!empty($contacts_mess)) {
			foreach ($contacts_mess as $key => $value) {
				$user = Db::table('ct_user')->where(array('uid'=>$value['userid']))->find();
				$contact_arr[$key]['realname'] = $user['realname']=='' ? $user['username'] : $user['realname'];
				$contact_arr[$key]['phone'] = $user['phone'];
				$contact_arr[$key]['message'] = $value['message'];
				$contact_arr[$key]['addtime'] = $value['addtime'];
			}
		}
		$detail['contact_mess'] = $contact_arr;
		if(empty($detail)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
		}
	}

	/**
	 * 个人中心-订单列表-整车确认提货完成
	 * @auther 李渊
	 * @date 2018.6.12
	 * @param string token 验证令牌
	 * @param string id 订单ID
	 */
	public function vehicle_collectGoods() {
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
			$driver_id = $check_result['driver_id'];
		}
		// 查询条件 订单id
		$where['uoid'] = $id;
		// 查询结果
		$result = Db::table('ct_userorder')->where($where)->find();
		// 不能重复点击提货
		if($result['orderstate'] == 5){
			return json(['code'=>'1003','message'=>'已提货']);
			exit;
		}
		// 更新状态 下单状态 1 未接单 2已接单 3已完成  4已取消 5 已提货 6 已配送
		$update['orderstate'] = 5;
		// 更新确认提货时间
		$update['pickTime'] = time();
		// 更新数据
		$re = DB::table('ct_userorder')->where('uoid',$id)->update($update);
		// 判断是否操作成功
		if($re){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 个人中心-订单列表-整车订单确认送货完成
	 * @auther 李渊
	 * @date 2018.6.12
	 * @param string token 验证令牌
	 * @param string id 订单ID
	 */
	public function vehicle_Delivery() {
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
			$driver_id = $check_result['driver_id'];
		}
		// 查询条件 订单id
		$where['uoid'] = $id;
		// 查询结果
		$result = Db::table('ct_userorder')->where($where)->find();
		// 不能重复点击提货
		if($result['orderstate'] == 6){
			return json(['code'=>'1003','message'=>'已配送']);
			exit;
		}
		// 更新状态 下单状态 1 未接单 2已接单 3已完成  4已取消 5 已提货 6 已配送
		$update['orderstate'] = 6;
		// 更新确认送货时间
		$update['sendTime'] = time();
		// 更新数据
		$re = DB::table('ct_userorder')->where('uoid',$id)->update($update);
		// 判断是否操作成功
		if($re){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 删除回单操作
	 * @param string token 验证令牌
	 * @param string uoid 订单ID
	 * @param string otyeid 回单标签ID
	 */
	public function delete_receipts(){
		$token   = input("token");  //令牌
		$oid   = input("oid");  //订单ID
		$otyeid   = input("otyeid");  //回单标签ID
		$act_type   = input("act_type");  //订单类型 1零担 2城配 3整车
		if(empty($token) || empty($oid) || empty($act_type)){
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
		if ($act_type=='3') {
			$result = Db::table('ct_userorder')->where('uoid',$oid)->find();
			$receipts = json_decode($result['receipts'],TRUE);
			unset($receipts[$otyeid]);
			$data['receipts'] = json_encode(array_values($receipts));
			$res = Db::table('ct_userorder')->where('uoid',$oid)->update($data);
		}elseif ($act_type=='2') {
			$result = Db::table('ct_city_order')->where('id',$oid)->find();
			$receipts = json_decode($result['picture'],TRUE);
			unset($receipts[$otyeid]);
			$data['picture'] = json_encode(array_values($receipts));
			$res = Db::table('ct_city_order')->where('id',$oid)->update($data);
		}elseif ($act_type=='1') {
			$result = Db::table('ct_order')->where('oid',$oid)->find();
			$receipts = json_decode($result['receipt'],TRUE);
			unset($receipts[$otyeid]);
			$data['receipt'] = json_encode(array_values($receipts));
			$res = Db::table('ct_order')->where('oid',$oid)->update($data);
		}
		if ($res) {
			return json(['code'=>'1001','message'=>'操作完成']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 我的承接：整车确认完成
	 * @param string token 验证令牌
	 * @param string uoid 订单ID
	 */
	public function vehical_affirm(){
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
			$driver_id = $check_result['driver_id'];
		}
		$result = Db::table("ct_userorder")->field('receipts')->where('uoid',$uoid)->find();
		if ($result['receipts']=='') {
			return json(['code'=>'1016','message'=>'操作未完成']);
		}else{
			$data['arrivetime'] = time();
			Db::table('ct_userorder')->where('uoid',$uoid)->update($data);

			return json(['code'=>'1001','message'=>'操作完成']);
		}
	}

	/**
	 * 我的承接：整车回单上传
	 * @param string token 验证令牌
	 * @param string uoid 订单ID
	 */
	public function vehical_back(){
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
			$driver_id = $check_result['driver_id'];
		}
		$arr_list = array();
		//查找是否有已经有回单上传
	    $result = Db::table("ct_userorder")
	    			->alias('a')
	    			->join('ct_user u','u.uid = a.userid')
	    			->field('a.ordernumber,a.receipts,a.paystate,u.phone')
	    			->where('uoid',$uoid)
	    			->find();
	    if(!empty($_FILES['back_img_1']['tmp_name'])){
		    $re_1 = $this->file_upload('back_img_1','jpg,gif,png,jpeg',"bulk_back");
	        $url = $re_1['file_path']; //源文件地址
	        if (!empty($result['receipts'])) {
	        	$arr_list = json_decode($result['receipts'],TRUE);
	        	array_push($arr_list,$url);
	        }else{
	        	$arr_list[] = $url;
	        }
        }
	    $upda_data['receipts'] = json_encode($arr_list);
        $re = Db::table("ct_userorder")->where('uoid',$uoid)->update($upda_data);
		if($re){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

    /**
	 * 我的承接：零担提货单订单列表
	 * 暂时未用
	 * @param string token 验证令牌
	 * @param string act_type 订单类型 1进行中2已完成
	 */
	public  function bulk_list_ti(){
		$token   = input("token");  //令牌
		$act_type   = input("act_type");  //1进行中2已完成
		if(empty($token) || empty($act_type)){
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
		$driver_mes = Db::table("ct_driver")->where('drivid',$driver_id)->find();
		if ($act_type =='1') {
			$condition['p.status'] = 2;
			$wheror = 2;
		}elseif($act_type =='2'){
			$condition['p.status'] = 3;
			$wheror = 3;
		}
		$condition['o.paystate'] = 2;
		if ($driver_mes['type'] == 3 ) {
			$userid = Db::table('ct_driver')->where('companyid',$driver_mes['companyid'])->select();
	 		$uid = '';
	 		foreach ($userid as $key => $value) {
	 			$uid .= $value['drivid'].',';
	 		}
	 		$uid =  substr($uid,0,strlen($uid)-1);
	 		$comid = $driver_mes['companyid'];
				$result = Db::table("ct_order")
				->alias('o')
				->join("__PICKORDER__ p",'o.oid = p.orderid')
				->join('ct_shift s','s.sid=o.shiftid')
				->join('ct_driver dr','dr.drivid=p.driverid','LEFT')
				->field('o.oid,o.addtime,o.pickcost,o.totalnumber,o.picktime,o.totalweight,o.totalvolume,p.type,p.status,p.picid,o.shiftid,p.systemorders,p.drivername,p.carlicense')
			    ->order('o.addtime desc')
			    ->where($condition)
			    ->where(function($query) use($uid){
			        $query->where(array('p.driverid'=>array('in',$uid)));
			    })
			    ->paginate(10);
		}else{
			$condition['p.driverid'] =  $driver_id;
			$condition2['p.sallotid'] =  $driver_id;
			$result = Db::table("ct_order")
				->alias('o')
				->join("__PICKORDER__ p",'o.oid = p.orderid')
				->join('ct_shift s','s.sid=o.shiftid')
				->field('o.oid,o.addtime,o.pickcost,o.totalnumber,o.picktime,o.totalweight,o.totalvolume,p.status,p.picid,o.shiftid,p.systemorders,p.drivername,p.carlicense')
				->order('o.addtime desc')
				->where($condition)
				->whereOr($condition2)
				->paginate(10);
		}
		$list_mes = $result->toArray();
		$list = $list_mes['data'];
		if(!empty($list)){
			foreach ($list as $key => $value) {
				$mes_where['s.sid'] = $value['shiftid'];
				$line = Db::table("ct_shift")
					->alias('s')
					->join('__ALREADY_CITY__ c','c.city_id = s.linecityid')
					->field('c.start_id,s.beginareaid,s.beginprovinceid,s.begincityid,s.beginaddress')
					->where($mes_where)
					->find();
				//查询起点城市
				$city_name = Db::table("ct_district")->field("name")->where("id",$line['start_id'])->find();
				$list[$key]['city_name'] = $city_name['name'];
				$area_name = Db::table("ct_district")->field("name")->where("id",$line['beginareaid'])->find();
				$list[$key]['beginaddress'] =$city_name['name'].$area_name['name'].$line['beginaddress'];
				$list[$key]['pickcost'] = round($value['pickcost']);
				$list[$key]['driverstate'] = 1;
				if ($value['drivername'] !='' ||  $value['carlicense'] !='') {
					$list[$key]['driverstate'] = 2;
				}
			}
		}
        if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}
	}

	/**
	 * 我的承接：零担干线单订单列表
	 * 未用，暂时保留
	 * @param  token   令牌
	 * @param  act_type   类型：1 未接单 2 手动接单和系统接单
	 */
	public  function bulk_list_gan(){
			$token   = input("token");  //令牌
			$act_type = input("act_type"); //类型：1 未接单 2 手动接单和系统接单
			if(empty($token) || empty($act_type)){
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
			$driver_mes = Db::table("ct_driver")->where('drivid',$driver_id)->find();
			$condition['b.companyid']  = $driver_mes['companyid'];
			if ($act_type == '1') {
				$condition['l.affirm'] = '1';
			}else{
				$condition['l.affirm'] = ['NEQ','1'];
			}
			$condition['o.paystate'] =  '2';
			$result = Db::table("ct_order")
				->alias('o')
				->join("__LINEORDER__ l",'o.oid = l.orderid')
				->join('ct_shift b','b.sid=o.shiftid')
				->field('o.oid,o.ordernumber,o.addtime,o.linepice,o.totalnumber,o.totalweight,o.totalvolume,
					o.orderstate,o.shiftid,l.lid,l.affirm')
				->order('o.addtime desc')
				->where($condition)
				->paginate(10);
			$list_mes = $result->toArray();
			$list = $list_mes['data'];
			if(!empty($list)){
				foreach ($list as $key => $value) {
					$mes_where['s.sid'] = $value['shiftid'];
					$line = Db::table("ct_shift")
						->alias('s')
						->join('__ALREADY_CITY__ c','c.city_id = s.linecityid')
						->field('c.start_id,c.end_id,s.shiftnumber')
						->where($mes_where)
						->find();
					$list[$key]['shiftnumber'] = $line['shiftnumber'];
					//查询起点城市
					$start_city_name = Db::table("ct_district")->field("name")->where("id",$line['start_id'])->find();
					$list[$key]['start_city_name'] = $start_city_name['name'];
					//查询终点城市
					$end_city_name = Db::table("ct_district")->field("name")->where("id",$line['end_id'])->find();
					$list[$key]['end_city_name'] = $end_city_name['name'];
					$list[$key]['linepice'] = $value['linepice'];
				}
			}
	        if(empty($list)){
				return json(['code'=>'1001','message'=>'暂无数据']);
			}else{
				return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
			}
	}

	/**
	 * 我的承接：零担配送单订单列表
	 * 未用，暂时保留
	 * @param  token   令牌
	 * @param  act_type   类型：1 未接单 2 手动接单和系统接单
	 */
	public  function bulk_list_pei(){
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
		$driver_mes = Db::table("ct_driver")->where('drivid',$driver_id)->find();
		$condition['d.driverid'] =  $driver_mes['companyid'];
		$condition['o.paystate'] = '2';
			$result = Db::table("ct_order")
				->alias('o')
				->join("__DELORDER__ d",'o.oid = d.orderid')
				->field('o.oid,o.ordernumber,o.addtime,o.delivecost,o.totalnumber,o.totalweight,o.totalvolume,o.orderstate,o.shiftid')
				->order('o.addtime desc')
				->where($condition)
				->paginate(10);
		$list_mes = $result->toArray();
		$list = $list_mes['data'];
		if(!empty($list)){
			foreach ($list as $key => $value) {
				$mes_where['s.sid'] = $value['shiftid'];
				$line = Db::table("ct_shift")
					->alias('s')
					->join('__ALREADY_CITY__ c','c.city_id = s.linecityid')
					->field('c.end_id,s.endprovinceid,s.endcityid,s.endareaid,s.endaddress')
					->where($mes_where)
					->find();
				//查询终点城市
				$end_city_name = Db::table("ct_district")->field("name")->where("id",$line['end_id'])->find();
				$list[$key]['end_city_name'] = $end_city_name['name'];
				$area_name = Db::table("ct_district")->field("name")->where("id",$line['endareaid'])->find();
				$list[$key]['endaddress'] =$end_city_name['name'].$area_name['name'].$line['endaddress'];
				$list[$key]['delivecost'] = $value['delivecost'];
			}
		}
        if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}
	}

	/**
	 * 我的承接：零担订单详情
	 * 未用，暂时保留
	 * @param  token   令牌
	 * @param  oid   oid
	 * @param  act_type   //订单类型：1 提货单  2 干线单  3 配送单
	 */
	public function  bulk_detail(){
		$token   = input("token");  //令牌
		$oid   = input("oid");  //oid
		$act_type   = input("act_type");  //订单类型：1 提货单  2 干线单  3 配送单
		if(empty($token) || empty($act_type) || empty($oid)){
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
		$detail = Db::table("ct_order")
				->field('oid,ordernumber,pickaddress,sendaddress,itemtype,coldtype,userid,
					remark,shiftid,addtime,totalnumber,picktime,totalweight,totalvolume,orderstate,linepice,pickcost,delivecost,receipt')
				->where('oid',$oid)
				->find();
		$mes_where['g.sid'] = $detail['shiftid'];
		$line = Db::table("ct_shift")
			->alias('s')
			->join('__ALREADY_CITY__ c','c.city_id = s.linecityid')
			->field('s.beginareaid,s.beginaddress,s.endareaid,s.endaddress,s.linecityid,s.shiftnumber,c.start_id,c.end_id')
			->where($mes_where)
			->find();
		$user_mess = Db::table('ct_user')->field('realname,phone')->where('uid',$detail['userid'])->find();
		//查询起点城市
		$start_city_name = Db::table("ct_district")->field("name")->where("id",$line['start_id'])->find();
		$result['start_city_name'] = $start_city_name['name'];
		//查询起点城市
		$end_city_name = Db::table("ct_district")->field("name")->where("id",$line['end_id'])->find();
		$result['end_city_name'] = $end_city_name['name'];
		//收货仓库
		$area_name = Db::table("ct_district")->field("name")->where("id",$line['beginareaid'])->find();
		$result['beginaddress'] =$start_city_name['name'].$area_name['name'].$line['beginaddress'];
		$end_area_name = Db::table("ct_district")->field("name")->where("id",$line['endareaid'])->find();
		$result['endaddress'] =$end_city_name['name'].$end_area_name['name'].$line['endaddress'];
		$result['ordernumber']	= $detail['ordernumber']; //订单号
		$result['totalnumber']	= $detail['totalnumber']; //数量
		$result['totalweight']	= $detail['totalweight']; //重量
		$result['totalvolume']	= $detail['totalvolume']; //体积
		$result['itemtype']	= $detail['itemtype']; //物品类型
		$result['coldtype']	= $detail['coldtype']; //冷冻类型
		$result['picktime']	= $detail['picktime']; //提货时间
		$result['remark']	= $detail['remark']; //备注
		$result['realname']	= $user_mess['realname']; //下单人姓名
		$result['phone']	= $user_mess['phone']; //下单人电话
		if ($act_type == '1') {
			$pickstatus = Db::table('ct_pickorder')->where('orderid',$oid)->find();
			$result['status'] = $pickstatus['status'];
		}else{
			$result['orderstate']	= $detail['orderstate']; //订单状态
		}
		$result['shiftnumber']	= $line['shiftnumber']; //班次号
		$result['add_time'] =    $detail['addtime'];	     //下单时间
		//提货地址
		//$result['goods_list_pick'] = $this->senditem($oid,1);
		$pickaddress = json_decode($detail['pickaddress'],TRUE);
		$sendaddress = json_decode($detail['sendaddress'],TRUE);
		foreach ($pickaddress as $key => $value) {
				$pick_arr[] = $start_city_name['name'].$value['taddressstr'];
		}
		$result['goods_list_pick'] = $pick_arr;
		foreach ($sendaddress as $key => $val) {
				$send_arr[$key]['name'] = $val['name'];
				$send_arr[$key]['phone'] = $val['phone'];
				$send_arr[$key]['tabid'] = $val['tabid'];
				$send_arr[$key]['paddress'] = $end_city_name['name'].$val['paddressstr'];
		}
		//配送地址
		$result['goods_list_pei'] = $send_arr;
		$result['receipts'] = json_decode($detail['receipt'],TRUE);
		//订单类型：1 提货单  2 干线单  3 配送单
		switch ($act_type) {
			case '1':
				$result['money'] = round($detail['pickcost']);
				$peple_send = Db::field('d.realname,d.mobile,p.drivername,p.driverphone,p.carlicense')
					->table('ct_order')
					->alias('a')
					->join('ct_shift c','c.sid=a.shiftid')
					->join('ct_pickorder p','p.orderid = a.oid')
					->join('ct_driver d','d.companyid=c.companyid','LEFT')
					->where(array('a.oid'=>$oid,'d.type'=>2))
					->find();
				$result['peple_send_name'] = $peple_send['realname'];
				$result['peple_send_phone'] = $peple_send['mobile'];
				$result['drivername'] = $peple_send['drivername'];
				$result['driverphone'] = $peple_send['driverphone'];
				$result['carlicense'] = $peple_send['carlicense'];
				break;
			case '2':
				$result['money'] = round($detail['linepice']);
				$peple_send = Db::field('d.realname,d.mobile')
					->table('ct_order')
					->alias('a')
					->join('ct_shift c','c.sid=a.shiftid')
					->join('ct_driver d','d.companyid=c.companyid')
					->where(array('a.oid'=>$oid,'d.type'=>2))
					->find();
				$result['peple_send_name'] = $peple_send['realname'];
				$result['peple_send_phone'] = $peple_send['mobile'];
				break;
			case '3':
				$result['money'] = round($detail['delivecost']);
				break;
		}
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
	}

	/**
	 * 干线订单主动确认
	 * 未用，暂时保留
	 * @param  token   令牌
	 * @param  lid   干线订单ID
	 */
	public function lineorder_confirm(){
		$token   = input("token");  //令牌
		$lid   = input("lid");  //干线订单ID
		if(empty($token) ||  empty($lid)){
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
		$up_data = array(
			'affirm'=>'2',  //是否确认接单1未确认2已确认 3 系统确认
			'drivid'=>$driver_id,
			'receivetime'=>time()
			);
		$send_data = array(
			'status'=>'2',  //是否确认接单1未确认2已确认 3 系统确认
			'drivid'=>$driver_id,
			'recceivetime'=>time()
			);
		$re = Db::table("ct_lineorder")->where('lid',$lid)->update($up_data);
		$driver_mes = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		$result = Db::field('b.linepice,b.delivecost,b.oid')
					->table("ct_lineorder")
					->alias('a')
					->join('ct_order b','a.orderid=b.oid')
					->where('a.lid',$lid)
					->find();
		$re = Db::table("ct_delorder")->where('deid',$result['oid'])->update($send_data);
		if($re){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 我的承接：提货订单确认完成 2017-7-26
	 * 未用，暂时保留
	 * @param  token   令牌
	 * @param  lid   干线订单ID
	 */
	public function pick_confirm(){
		$token = input('token');
		$picid   = input("picid");  //提货ID
		if (empty($token) || empty($token)) {
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


		$search_driver = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		$search_pick = DB::table('ct_pickorder')
						->alias('p')
						->join('ct_lineorder li','li.orderid=p.orderid')
						->field('p.tprice,p.picktime,li.arrtime,li.finishtime,p.driverid')
						->where('picid',$picid)
						->find();
		if ($search_pick['finishtime']=='') {
			return json(['code'=>'1016','message'=>'操作未完成']);
			exit();
		}
		// 更新提货单
		$pickdata['status'] = '3';  //接单状态1未接2已接3已完成
		$up = Db::table("ct_pickorder")->where('picid',$picid)->update($pickdata);
		if ($up) {
			if ($search_pick['driverid'] !='') {
				Db::table('ct_driver')->where('drivid',$search_pick['driverid'])->update(array('money'=>$search_pick['tprice']+$search_driver['money']));
				Db::table('ct_balance_driver')->insert(array('pay_money'=>$search_pick['tprice'],'order_content'=>'接提货单收入费用，订单ID：'.$picid,'action_type'=>'1','driver_id'=>$search_pick['driverid'],'addtime'=>time()));
			}
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 我的承接:提货单物流
	 * 未用，暂时保留
	 * @param  token   令牌
	 * @param  picid   提货ID
	 */
	public function bulk_pick_wuliu(){
		$token = input("token"); //令牌
		$picid = input("picid"); //提货ID
		if(empty($token) ||  empty($picid)){
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
		$result = Db::table('ct_pickorder')
					->alias('a')
					->join('ct_order o','o.oid = a.orderid')
					->join('ct_lineorder l','l.orderid=o.oid')
					->join('ct_shift_log log','log.slid = o.slogid')
					->join('ct_shift s','s.sid=log.shiftid')
					->join('ct_already_city c','c.city_id = s.linecityid ')
					->field('a.picid,a.orderid,a.picktime,o.userid,o.pickaddress,c.start_id,l.arrtime,l.finishtime')
					->where('picid',$picid)
					->find();
		$startcity = Db::table('ct_district')->field('name')->where('id',$result['start_id'])->find();
		$city_name = $startcity['name'];
		if ($result['arrtime'] == '') {
			$result['arrstate'] = 1;
		}else{
			$result['arrstate'] = 2;
		}
		if ($result['finishtime'] == '') {
			$result['finishstate'] = 1;
		}else{
			$result['finishstate'] = 2;
		}
        $pick_json = json_decode($result['pickaddress'],TRUE);
        $arr_list = array();
        //查找发货地址  
        $i = 0;
        foreach ($pick_json as $key2 => $value2) {
        	$arr_list[$i]['addid'] = $value2['tabid'];   // 地址编号
        	$arr_list[$i]['userid'] = $result['userid'];
        	$code = Db::table('ct_wuliu_code')->where(array('address_id'=>$value2['tabid'],'pick_id'=>$picid))->order('send_time','desc')->find();
        	if (empty($code)) {
        		$arr_list[$i]['sendcode'] = 1;
        	}else{
        		if($code['codestate'] == 1){
	        		$arr_list[$i]['sendcode'] = 1;
	        	}else{
	        		$arr_list[$i]['sendcode'] = 2;
	        	}
        	}
            $arr_list[$i]['fa_address'] = $city_name.$value2['taddressstr'];
        	$i++;
        }
        $result['countcode'] = count($arr_list);
        $result['addresslist'] = $arr_list;
       if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
	}

	/**
	 * 我的承接：物流发送验证码，输入验证码，点击到仓，卸货按钮
	 * 未用，暂时保留
	 * @param  token   令牌
	 * @param  picid   提货ID
	 * @param  oid   订单ID
	 * @param  addid   地址ID 验证码
	 * @param  userid   地址ID 验证码
	 * @param  pcode   发货人ID 验证码
	 * @param  countcode   统计要发送验证码个数验证码
	 * @param  act_type   发送状态 1发送验证码 2 提交验证码 3 到仓 4卸货
	 */
	public function bulk_wuliu_confirm(){
		$token   = input("token");  //令牌
		$picid   = input("picid");  //提货订单ID
		$oid   = input("oid");  //订单ID
		$addid   = input("addid");  //地址ID 验证码
		$shipper   = input("userid");  //发货人ID 验证码
		$pcode   = input("pcode");  //发货人ID 验证码
		$countcode   = input("countcode");  //统计要发送验证码个数验证码
		$act_type   = input("act_type");  //发送状态 1发送验证码 2 提交验证码 3 到仓 4卸货
		if (empty($token) || empty($picid) || empty($oid)) {
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
		$search_pick = DB::table('ct_pickorder')
						->alias('p')
						->join('ct_lineorder li','li.orderid=p.orderid')
						->join('ct_order o','o.oid = p.orderid')
						->field('p.tprice,p.picktime,o.pickaddress,li.arrtime,li.arrtime,li.finishtime')
						->where('picid',$picid)
						->find();
		//发送验证码
		if ($act_type == '1') {
			if(empty($addid) || empty($shipper)) {
				return json(['code'=>'1000','message'=>'参数错误']);
			}
			$search_code = Db::table('ct_wuliu_code')->field('code_id')->where(array('pick_id'=>$picid,'address_id'=>$addid))->find();
			if (!empty($search_code)) {
				Db::table('ct_wuliu_code')->delete($search_code['code_id']);
			}
			//查询地址信息
			$address='';
			$pick_arr = json_decode($search_pick['pickaddress'],TRUE);
			foreach ($pick_arr as $key => $value) {
				if ($value['tabid'] == $addid) {
					$address = $value['taddressstr'];
					break;
				}
			}
             //查找发货人
             $senduser = Db::table('ct_user')->field('phone')->where('uid',$shipper)->find();
             //发送信息
             $code = send_sms('2',$senduser['phone'],$address);
             if ($code['status'] == '1') {
             	 $data = array(
             		'pick_id'=>$picid,
             		'codenumber'=>$code['verify'],
             		'address_id'=>$addid,
             		'send_time'=>time(),
             		'codestate'=>1
             	);
            	Db::table('ct_wuliu_code')->insert($data);
	            return json(['code'=>'1001','message'=>'操作成功']);
			}else{
				return json(['code'=>'1002','message'=>'操作失败']);
			}
		}elseif($act_type == '2'){ //输入验证码
			if (empty($pcode) || empty($addid) || empty($countcode)) {
				return json(['code'=>'1000','message'=>'参数错误']);
			}
			$search_code = DB::table('ct_wuliu_code')->field('code_id,codenumber')->where(array('address_id'=>$addid,'pick_id'=>$picid))->find();
			if ($search_code['codenumber'] == $pcode) {
				$data['codestate'] = 2;
				DB::table('ct_wuliu_code')->where(array('address_id'=>$addid,'pick_id'=>$picid,'code_id'=>$search_code['code_id']))->update($data);
				//统计验证码个数
				$count_code = DB::table('ct_wuliu_code')->where(array('pick_id'=>$picid,'codestate'=>2))->count('code_id');
				if ($count_code == $countcode) {
					$pick_data=array(
						'picktime'=>time()
					);
					Db::table('ct_pickorder')->where('picid',$picid)->update($pick_data);
				}
				return json(['code'=>'1001','message'=>'操作成功']);
			}else{
				return json(['code'=>'1015','message'=>'验证码不对']);
			}
		}elseif($act_type == '3'){ //到仓时间
			if ($search_pick['picktime']=='') {
				return json(['code'=>'1016','message'=>'操作未完成']);
			}
			$line_data['arrtime'] = time();
			$up = Db::table('ct_lineorder')->where('orderid',$oid)->update($line_data);
			if ($up) {
				return json(['code'=>'1001','message'=>'操作成功']);
			}else{
				return json(['code'=>'1002','message'=>'操作失败']);
			}
		}else{ //卸货完成
			if ($search_pick['picktime']=='' || $search_pick['arrtime']=='') {
				return json(['code'=>'1016','message'=>'操作未完成']);
			}
			$line_data['finishtime'] = time();
			$up = Db::table('ct_lineorder')->where('orderid',$oid)->update($line_data);
			if ($up) {
				return json(['code'=>'1001','message'=>'操作成功']);
			}else{
				return json(['code'=>'1002','message'=>'操作失败']);
			}
		}
	}

	/**
	 * 零担回单上传
	 * 未用，暂时保留
	 * @param  token   令牌
	 * @param  oid   订单ID
	 */
	public  function bulk_back(){
		$token   = input("token");  //令牌
		$oid   = input("oid");  //订单ID
		if(empty($token) ||  empty($oid)){
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
	    //查找是否有已经有回单上传
	     $find = Db::table("ct_order")->alias('o')->join('ct_user u','u.uid=o.userid')->field('o.receipt,u.phone')->where('oid',$oid)->find();
	     $arr_list = array();
	     if(!empty($_FILES['back_img_1']['tmp_name'])){
			$re_1 = $this->file_upload('back_img_1','jpg,gif,png,jpeg',"bulk_back");
	        $file_url= $re_1['file_path']; //源文件地址
	        if (!empty($find['receipt'])) {
	        	$arr_list = json_decode($find['receipt'],TRUE);
	        	array_push($arr_list, $file_url);
	        }else{
	        	$arr_list[] =$file_url;
	        }
	    }
        $upda_data['receipt'] = json_encode($arr_list);
        $re = Db::table("ct_order")->where('oid',$oid)->update($upda_data);
		if($re){
				// 更新干线单
				 $orderdata['orderstate'] = '7';  //接单状态1未接2已接3已完成
				 Db::table("ct_order")->where('oid',$oid)->update($orderdata);
					// 更新干线单
				 $linedata['status'] = '3';  //接单状态1未接2已接3已完成
				 Db::table("ct_lineorder")->where('orderid',$oid)->update($linedata);
				 // 更新配送单
				 $deldata['status'] = '3';  //接单状态1未接2已接3已完成
				 //接单人ID
				Db::table("ct_delorder")->where('orderid',$oid)->update($deldata);
				$content = "尊敬的客户：您订单编号为 ".$result['orderid']."已经安全到达目的地!!";

			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 2017-12-1
	 * author:dachenwei
	 * update
	 * 市内配送订单
	 * @param  string token 令牌
	 * @param  string type 订单类型 1已接单 2已完成订单 3 客户取消的订单
	 */
	public function city_With(){
		$token   = input("token");  //令牌
		$type = input('type');// 1 已接单 2 已完成订单 3 客户取消的订单
	    if(empty($token) || empty($type)){
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
		$driver_mes = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		$where2 = array();
		if ($driver_mes['type'] == '3' ) {
			$userid = Db::table('ct_driver')->where(array('companyid'=>$driver_mes['companyid'],'type'=>['neq','1']))->select();
	 		$uid = '';
	 		foreach ($userid as $key => $value) {
	 			$uid .= $value['drivid'].',';
	 		}
	 		$uid =  substr($uid,0,strlen($uid)-1);
	 		$where['driverid'] = array('in',$uid);
	 		$driver_id =['NEQ',''];
		}
		switch ($type) {
			case '1':
				$where['o.state'] = array('IN','2,5');
				break;
			case '2':
				$where['o.state'] = '3';
				break;
			case '3':
				$where['o.state'] = '4';
				break;
		}
		$result = Db::table('ct_city_order')
				->alias('o')
				->join('ct_rout_order r','r.rid = o.rout_id')
				->join('ct_cartype c','c.car_id = o.carid')
				->field('o.id,o.fprice,o.handingmode,o.data_type,o.paymoney,o.state,o.carr_upprice,o.addtime,o.city_id,o.cold_type,o.ordertype,r.driverid,o.pytype,o.paystate,o.actual_payment,o.actualprice,r.drivername,r.carlicense,c.carparame,r.arrivetime')
				->where($where)
				->where(function($query) use($driver_id){
			        $query->where(array('r.driverid'=>$driver_id));
			        $query->whereOr(array('r.allotid'=>$driver_id));
			    })
				->order("o.addtime","desc")
				->paginate(10);
		$list_mes = $result->toArray();
		$list = array();
		foreach ($list_mes['data'] as $key => $value) {
			$list[$key] = $value;
			if ($value['pytype'] =='1' ) {
				$paymoney = $value['carr_upprice']=='' ? $value['paymoney'] : $value['carr_upprice'];
			}else{
				$paymoney=0;
			}
			$city = $value['city_id'];
			$list[$key]['cityname'] = $city;
			$list[$key]['pytype'] = $value['pytype'];
			$list[$key]['paymoney'] = round($paymoney);
			if($value['pytype'] == 3){
				$list[$key]['paymoney'] = $value['actual_payment'];
			}
			$list[$key]['driverstate'] = 1;
			if ($value['drivername'] !='' ||  $value['carlicense'] !='') {
				$list[$key]['driverstate'] = 2;
			}
		}
		if(empty($list_mes['data'])){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}
	}

	/**
	 * 城配订单详情
	 * @Author:李渊
	 * @Date: 2018.8.13
	 * @param  string token 令牌
	 * @param  string id 订单ID
	 */
	public function city_With_detail(){
		$token = input("token");  //令牌
		$id = input("id");
		if(empty($token) || empty($id)){
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
		$result = DB::table('ct_city_order')
					->alias('o')
					->join('ct_cartype c','c.car_id = o.carid')
					->join('ct_user u','u.uid=o.userid')
					->join('ct_rout_order r','r.rid=o.rout_id')
					->field('o.*,c.carparame,u.phone,u.image,u.realname,r.drivername,r.driverphone,r.carlicense')
					->where('id',$id)
					->find();
		// 下单人图像
		if ($result['image'] =='') {
			$result['image'] = get_url().'/static/user_header.png';
		}else{
			$result['image'] = get_url().$result['image'];
		}
		// 支付状态
		switch ($result['paystate']) {
			case '1': // 未支付
				$result['paystate'] = '未支付';
				break;
			case '2': // 未支付
				$result['paystate'] = '已支付';
				break;
			case '3': // 未支付
				$result['paystate'] = '支付失败';
				break;
			case '4': // 未支付
				$result['paystate'] = '未支付';
				break;
			default:
				# code...
				break;
		}

		if ($result['pytype'] =='1' ) {
			$paymoney = $result['carr_upprice']=='' ? $result['paymoney'] : $result['carr_upprice'];
		}else{
			$paymoney=0;
		}
		$city = $result['city_id'];
		$result['cityname'] = $city;
		$result['paymoney'] = round($paymoney);
		if($result['pytype'] == 3){
			$result['paymoney'] = $result['actual_payment'];
		}
		$result['saddress'] = json_decode($result['saddress'],TRUE);
		$result['eaddress'] = json_decode($result['eaddress'],TRUE);
		$receipts = json_decode($result['picture'],TRUE);
		$array = array();
		if (!empty($receipts)) {
			foreach ($receipts as $key => $value) {
				$array[$key]['key'] = $key;
				$array[$key]['value'] = $value;
			}
		}
		$result['receipts'] = $array;
		// 用户反馈内容
		$contact_arr = array();
		$contacts_mess = Db::table('ct_order_contact')->where(array('orderid'=>$result['id'],'utype'=>'1','otype'=>'3'))->select();
		if (!empty($contacts_mess)) {
			foreach ($contacts_mess as $key => $value) {
				$user = Db::table('ct_user')->where(array('uid'=>$value['userid']))->find();
				$contact_arr[$key]['realname'] = $user['realname']=='' ? $user['username'] : $user['realname'];
				$contact_arr[$key]['phone'] = $user['phone'];
				$contact_arr[$key]['message'] = $value['message'];
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

	/**
	 * 市内配送司机确认提货
	 * @Author:李渊
	 * @Date: 2018.8.13
	 * @param  string token 令牌
	 * @param  string id 订单ID
	 */
	public function city_With_ti(){
		// 用户令牌
		$token = input("token");
		// 订单id
		$id = input('id');
		// 验证参数
	    if(empty($token) || empty($id)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 验证令牌
		$check_result = $this->check_token($token);
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		// 更新状态
		$confirmdata['state'] = 5;
		// 插入提货时间
		$insert['pickTime'] = time();
		// 更新数据
		$re = DB::table('ct_city_order')->where('id',$id)->update($confirmdata);
		// 查找订单数据
		$order = DB::table('ct_city_order')->where('id',$id)->find();
		// 更新数据
		$rout = DB::table('ct_rout_order')->where('rid',$order['rout_id'])->update($insert);

		if($re){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 城配订单确认配送完成
	 * @Author: 李渊
	 * @Date: 2018.8.13
	 * @param  string token 令牌
	 * @param  string id 订单ID
	 */
	public function city_With_end(){
		// 用户令牌
		$token   = input("token");
		// 订单id
		$id = input('id');
		// 判断参数是否有误
	    if(empty($token) || empty($id)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 验证令牌
		$check_result = $this->check_token($token);
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		// 查询数据
		$result = DB::table('ct_city_order')
					->alias('o')
					->join('ct_rout_order r','r.rid = o.rout_id')
					->field('r.rid,o.picture')
					->where('id',$id)
					->find();
		// 查询回单
		$picture = json_decode($result['picture'],TRUE);
		// 判断是否有回单
		if (empty($picture)) {
			return json(['code'=>'1016','message'=>'请先上传回单']);
			exit();
		}
		// 更新配送状态7配送完成
		$update['state'] = 7;
		// 司机确认送达时间
		$rout_data['arrivetime'] = time();
		// 货运网已经不用保留
		$rout_data['apitype'] = 1;
		// 更新数据
		$update = Db::table('ct_city_order')->where('id',$id)->update($update);
		// 更新数据
		$re = Db::table('ct_rout_order')->where('rid',$result['rid'])->update($rout_data);
		// 判断更新状态
		if($re && $update){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 2017-12-1
	 * author:dachenwei
	 * update
	 * 市内配送回单上传
	 * @param  string token 令牌
	 * @param  string id 订单ID
	 */
	public function city_with_back(){
		$token   = input("token");  //令牌
		$id = input('oid');
		if(empty($token) || empty($id) ){
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
		$result = DB::table('ct_city_order')
    			->alias('o')
    			->join('ct_user u','u.uid=o.userid')
    			->field('o.*,u.phone')
    			->where('id',$id)->find();
		 $arr_list = array();
	     if(!empty($_FILES['back_img_1']['tmp_name'])){
			$re_1 = $this->file_upload('back_img_1','jpg,gif,png,jpeg',"bulk_back");
	        $file_url= $re_1['file_path']; //源文件地址
	        if (!empty($result['picture'])) {
	        	$arr_list = json_decode($result['picture'],TRUE);
	        	array_push($arr_list, $file_url);
	        }else{
	        	$arr_list[] =$file_url;
	        }
	    }
	    $upda_data['picture'] = json_encode($arr_list);
      	$re = Db::table("ct_city_order")->where('id',$id)->update($upda_data);
      	$rout_data['apipicture'] = 1;
      	DB::table("ct_rout_order")->where('rid',$result['rout_id'])->update($rout_data);
      	if($re){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}


	/**
	 * 专车接单
	 * @param token  令牌
	 * @param orderid   订单ID
	 */
	public function shift_order_ask(){
		$token = input('token');
		$orderid = input('orderid');
		if(empty($token) ){
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
		//判断订单是否被承接
		$judge_order = DB::table('ct_shift_order')->where('s_oid',$orderid)->find();
		if ($judge_order['orderstate'] == 2) {
			return json(['code'=>'1012','ostate'=>1]);
			exit;
		}
		$data['taketime'] = time();
		$data['orderstate'] = 2;
		$data['driverid'] = $driver_id;
		$result = Db::table('ct_shift_order')->where('s_oid',$orderid)->update($data);
		if($result){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 固定班次订单列表
	 * @param token  令牌
	 * @param type   订单类型 1未接 2已接
	 */
	public function shift_order_list(){
		$token = input('token');
		$type = input('type');  //1未接 2已接
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
		$driver_mes = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		$where2 = array();
		if ($type == '1' ) {
			$where['orderstate'] = 1;
	 		$where['carrierid'] = $driver_mes['companyid'];
		}else{
			$where['driverid'] = $driver_id;
			$where['orderstate'] = ['IN','2,3'];
		}
		$where['affirm'] =2;
		$result = Db::table('ct_shift_order')
					->alias('o')
					->join('__FIXATION_LINE__ s','s.id = o.shiftid')
					->join('__ALREADY_CITY__ a','a.city_id = s.lienid')
					->join('__COMPANY__ c','c.cid=s.companyid')
					->field('o.s_oid,o.remark,o.ordernumber,o.carr_upprice,o.addtime,o.doornum,o.orderstate,o.picktime,o.arrivetime,o.orderstate,
						o.price,s.carprice,s.carr_price,a.end_id,a.start_id,c.name')
					->where($where)
					->order('s_oid','desc')
					->paginate(10);
		$list_mes = $result->toArray();
		$list = $list_mes['data'];
		$arr = array();
		$str_start = '';
		$str_end='';
		foreach ($list as $key => $value) {
			$arr[$key]['arrivetime'] = $value['arrivetime']; //司机确认到达时间
			$arr[$key]['s_oid'] = $value['s_oid']; //订单ID
			$arr[$key]['addtime']  = $value['addtime']; //下单时间
			$arr[$key]['picktime'] = $value['picktime']; //提货时间
			$arr[$key]['company'] = $value['name']; //提货时间
			$arr[$key]['orderstate'] = $value['orderstate']; //1未接单，2已接单3已完成
			$carr_price = $value['carr_upprice']=='' ? $value['price'] : $value['carr_upprice']; //给承运商价格
			$arr[$key]['carr_price'] = round($carr_price);
			// 起始城市
			$sarr = Db::table('ct_district')->where('id',$value['start_id'])->find();
			switch ($sarr['level']) {
                case 1: // 省
                    $arr[$key]['startcity'] = $sarr['name'];
                    break;
                case 2: // 市
                    $spro = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                    $arr[$key]['startcity'] = $sarr['name'];
                    break;
                default: // 区
                    $scity = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                    $arr[$key]['startcity'] =$scity['name'] . $sarr['name'];
                    break;
            }
            // 终点城市
            $earr = Db::table('ct_district')->where('id',$value['end_id'])->find();
            switch ($earr['level']) {
                case 1: // 省
                    $arr[$key]['endcity'] = $earr['name'];
                    break;
                case 2: // 市
                    $epro = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                    $arr[$key]['endcity'] = $earr['name'];
                    break;
                default: // 区
                    $ecity = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                    $arr[$key]['endcity'] = $ecity['name'] . $earr['name'];
                    break;
            }
			$arr[$key]['orderstate'] = $value['orderstate']; //1未接单，2已接单3已完成
		}
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$arr]);
		}
	}

	/**
	 * 固定班次订单详情
	 * @param token  令牌
	 * @param id   订单ID
	 */
	public function shift_order_detail(){
		$token = input('token');
		$id = input('id');
		if(empty($token) || empty($id)){
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
		$result = Db::table('ct_shift_order')
					->alias('o')
					->join('__FIXATION_LINE__ s','s.id = o.shiftid')
					->join('__ALREADY_CITY__ a','a.city_id = s.lienid')
					->field('o.s_oid,o.userid,o.addtime,o.remark,o.ordernumber,o.orderstate,o.doornum,o.totalcar,o.picktime,
						o.price,o.carr_upprice,s.carrierid,s.carr_price,s.carprice,a.end_id,a.start_id,s.paddress')
					->where(array('s_oid'=>$id,'affirm'=>'2'))
					->find();
		$str_start ='';
		$str_end='';
		// 起始城市
		$sarr = Db::table('ct_district')->where('id',$result['start_id'])->find();
		switch ($sarr['level']) {
            case 1: // 省
                $result['startcity'] = $sarr['name'];
                break;
            case 2: // 市
                $spro = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                $result['startcity'] = $sarr['name'];
                break;
            default: // 区
                $scity = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                $result['startcity'] = $scity['name'] . $sarr['name'];
                break;
        }
        // 终点城市
        $earr = Db::table('ct_district')->where('id',$result['end_id'])->find();
        switch ($earr['level']) {
            case 1: // 省
                $result['endcity'] = $earr['name'];
                break;
            case 2: // 市
                $epro = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                $result['endcity'] = $earr['name'];
                break;
            default: // 区
                $ecity = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                $result['endcity'] = $ecity['name'] . $earr['name'];
                break;
        }
		//查找下单人联系放肆
		$user = Db::table('ct_user')->where('uid',$result['userid'])->find();
		$result['user_phone'] = $user['phone'];
		//查找承运商联系方式
		$carries = Db::table('ct_driver')->where('drivid',$result['carrierid'])->find();
		$result['carr_phone'] = $carries['mobile'];
		$carr_price = $result['carr_upprice']=='' ? $result['price'] : $result['carr_upprice'];
		$result['carr_price'] = round($carr_price);
		$result['paddress'] = json_decode($result['paddress'],TRUE);
		//用户反馈内容
		$contact_arr = array();
		$contacts_mess = Db::table('ct_order_contact')->where(array('orderid'=>$result['s_oid'],'utype'=>'1','otype'=>'2'))->select();
		if (!empty($contacts_mess)) {
			foreach ($contacts_mess as $key => $value) {
				$user = Db::table('ct_user')->where(array('uid'=>$value['userid']))->find();
				$contact_arr[$key]['realname'] = $user['realname']=='' ? $user['username'] : $user['realname'];
				$contact_arr[$key]['phone'] = $user['phone'];
				$contact_arr[$key]['message'] = $value['message'];
				$contact_arr[$key]['addtime'] = $value['addtime'];
			}
		}
		$result['contact_mess'] = $contact_arr;
		$result['realname'] = '赤途(上海)供应链管理有限公司';
		$result['phone'] = '4009-206-101';
		$result['image'] =  get_url().'/static/service_header.png';
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
	}

	/**
	 * 订单完成
	 * @param token  令牌
	 * @param orderid   订单ID
	 */
	public function special_line_confim(){
		$token = input('token');
		$orderid = input('orderid');
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
		$data['arrivetime']=time();
		$result = Db::table('ct_shift_order')->where('s_oid',$orderid)->update($data);
		if($result){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 2017-12-1
	 * author:dachenwei
	 * update
	 * 顺风车回单上传
	 * @param token  令牌
	 * @param id   订单ID
	 */
	public function shift_order_back(){
		$token   = input("token");  //令牌
		$id = input('oid');
		if(empty($token) || empty($id) ){
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
		 $result = DB::table('ct_shift_order')
	    			->alias('o')
	    			->join('ct_user u','u.uid=o.userid')
	    			->field('o.*,u.phone')
	    			->where('s_oid',$id)->find();
		 $arr_list = array();
	     if(!empty($_FILES['back_img_1']['tmp_name'])){
			$re_1 = $this->file_upload('back_img_1','jpg,gif,png,jpeg',"bulk_back");
	        $file_url= $re_1['file_path']; //源文件地址
	        if (!empty($result['receipts'])) {
	        	$arr_list = json_decode($result['receipts'],TRUE);
	        	array_push($arr_list, $file_url);
	        }else{
	        	$arr_list[] =$file_url;
	        }
	    }
	    $upda_data['receipts'] = json_encode($arr_list);
	    $re = Db::table("ct_shift_order")->where('s_oid',$id)->update($upda_data);
	    if($re){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 2018-03-06
	 * author:dachenwei
	 * update
	 * 顺风车回单上传
	 * @param token  令牌
	 * @param id   订单ID
	 */
	public function shift_order_end(){
		$token   = input("token");  //令牌
		$id = input('id');
		if(empty($token) || empty($id) ){
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
		$result = DB::table('ct_shift_order')->where('s_oid',$id)->find();
		if (empty($result['receipts'])) {
			return json(['code'=>'1016','message'=>'操作未完成']);
			exit();
		}
		$where['s_oid'] = $id;
		$up_data['orderstate'] = '3';//接单状态1未接2已接3已完成
		$up_data['finshtime'] = time();
		$re = Db::table("ct_shift_order")->where($where)->update($up_data);
		if($re){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 用户信息获取
	 * @param token  令牌
	 */
	public function information(){
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
		$driverstatus = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		$cardata = Db::field('status,fail_reason')->table('ct_carcategory')->where('driverid',$driver_id)->order('addtime desc')->find();
		$data=$driverstatus;
		if ($driverstatus['companyid'] != '') {
			$data['driverstatus'] = 2; //司机驾驶证认证
			$data['carstatus'] = 2; //车辆认证
		}else{
			if ($driverstatus['drivingimage'] =='') {
				$data['driverstatus'] = 4;
			}else{
				$data['driverstatus'] = $driverstatus['carstatus'];
			}

			if (!empty($cardata)) {
				/*foreach ($cardata as $key => $value) {
					if ($value['status'] == '2') {
						$data['carstatus'] = 2;
					}
				}*/
				$data['carstatus'] = $cardata['status'];
				$data['cars_mess']= $cardata['fail_reason'];
			}else{
				$data['carstatus'] = 4;
				$data['cars_mess']='';
			}
		}
		return json(['code'=>'1001','message'=>'查询成功','data'=>$data]);
	}

	/**
	 * 冷链零担订单
	 * @auther liyuan
	 * @date 2018-04-12
	 * @return [type] 对应承运公司的零担订单(包含提干配信息)
	 */
	public function bulkorder() {
		// 获取用户令牌
		$token = input('token');
		// 承运首页是type // 1 未接单 2 手动接单和系统接单
		$type = input('type');
		// 个人中心传的是act_type
		$act_type = input('act_type');
		// 验证token
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
		// 查询承运人信息
		$driver_mes = Db::table("ct_driver")->where('drivid',$driver_id)->find();
		//$condition['b.companyid']  = $driver_mes['companyid'];
		//$conditionOr['b.driver_id']  = $driver_mes['drivid'];
		$companyid = $driver_mes['companyid'];
		/*if ($type == 1) {
			$condition['gan.affirm'] = '1';
		}else{
			$condition['gan.affirm'] = ['NEQ','1'];
		}*/
		// 说明个人中心请求则需要只查询公司和接单人订单
		$condition = '';
		$conditionOr= '';
		if($act_type){
			$condition['b.companyid']  = $driver_mes['companyid'];
			$conditionOr['b.driver_id']  = $driver_mes['drivid'];
		}
		$paystate = 2;
		//$condition['o.paystate'] =  '2';
		// 查询承运人对应的冷链零担订单
		$result = Db::table("ct_order")
				->alias('o')
				->join("ct_pickorder ti",'ti.orderid = o.oid') // 提货单
				->join("ct_lineorder gan",'gan.orderid = o.oid') // 干线单
				->join("ct_delorder pi",'pi.orderid = o.oid') // 配送单
				->join('ct_shift b','b.sid = o.shiftid') // 班次
				->join('ct_already_city c','c.city_id = b.linecityid') // 线路
				->join('ct_district scity','scity.id = c.start_id') // 起点城市
				->join('ct_district ecity','ecity.id = c.end_id') // 终点城市
				->field('o.oid,o.picktype,o.sendtype,o.stime,o.ordernumber,o.picktime,o.addtime,o.arrivetime,o.totalnumber,o.totalweight,o.totalvolume,o.orderstate,o.receipt,
					o.slogid,o.pickcost,o.linepice,o.delivecost,o.userid,ti.tcarr_upprice,gan.lcarr_price,pi.pcarr_upprice,b.companyid,
					b.shiftnumber,b.shiftstate,b.driver_id,b.weekday,scity.name as start_city_name,ecity.name as end_city_name,gan.affirm')
				->order('o.addtime desc')
				->where(function($query) use($paystate){
					 $query->where(array('o.paystate'=>$paystate));
				})
                ->where('orderstate','neq','1')
				->where($condition)
				->whereOr($conditionOr)
				->paginate(10);
			$list_mes = $result->toArray();
			$list = $list_mes['data'];
			$arrlist = array();
			if(!empty($list)){
				foreach ($list as $key => $value) {
					$arrlist[$key]['realname'] = '赤途(上海)供应链管理有限公司';
					$arrlist[$key]['phone'] = '4009-206-101';

					//判断是否是改干线公司下的成员显示接单按钮 iscompany 1 不显示 2显示
					$iscompany = '1';
					//判断是否有回单 ispicturn 1 未上传 2已上传
					$ispicturn = '1';
					if ($value['shiftstate'] =='2') { //当改班次为廉价班次时
						// 订单对应的下单人信息
						$user_mess = Db::table('ct_user')->field('username,realname,phone')->where('uid',$value['userid'])->find();
						// 下单人姓名
						$arrlist[$key]['realname'] = $user_mess['realname'] == '' ? $user_mess['username'] :  $user_mess['realname'];
						// 下单人电话
						$arrlist[$key]['phone']	= $user_mess['phone'];
						if ($driver_id == $value['driver_id'] ) {
						 	$iscompany = '2';
						 }
					}else{ //选择平台添加班次时
						if ($companyid!='' && $companyid ==$value['companyid']) {
							$iscompany = '2';
						}
					}
					// 回单
					$receipts = json_decode($value['receipt'],TRUE);
					if (!empty($receipts)) {
						$ispicturn = '2';
					}
					//是否上传回单 1 未上传 2已上传
					$arrlist[$key]['ispicturn'] = $ispicturn;

					// 订单id
					$arrlist[$key]['oid'] = $value['oid'];
					// 订单编号
					$arrlist[$key]['ordernumber'] = $value['ordernumber'];
					//订单状态1平台2用户
					$arrlist[$key]['shiftstate'] = $value['shiftstate'];
					// 班次号
					$arrlist[$key]['shiftnumber'] = $value['shiftstate']=='1' ? $value['shiftnumber']:$value['weekday'];
					// 下单时间
					$arrlist[$key]['addtime'] = $value['addtime'];
					// 提货时间
					$arrlist[$key]['picktime'] = $value['picktime'];
					// 接单状态 1 未接单 2 手动接单 3 系统接单
					$arrlist[$key]['affirm'] = $value['affirm'];
					// 是否显示接单按钮
					if($value['affirm'] == '1' && $iscompany =='2'){ // 显示
						$arrlist[$key]['iscompany'] = '2';
					}else{
						$arrlist[$key]['iscompany'] = '1';
					}

					// 订单状态 1已下单2已支付3订单承接4入始发5订单发出6入终点7已完成8订单取消
					$arrlist[$key]['orderstate'] = $value['orderstate'];
					// 总件数
					$arrlist[$key]['totalnumber'] = $value['totalnumber'];
					// 总重量
					$arrlist[$key]['totalweight'] = $value['totalweight'];
					// 总体积
					$arrlist[$key]['totalvolume'] = $value['totalvolume'];
					// 起点城市
					$arrlist[$key]['start_city_name'] = $value['start_city_name'];
					// 终点城市
					$arrlist[$key]['end_city_name'] = $value['end_city_name'];
					// 提货费
					$arrlist[$key]['tiprice'] = $value['tcarr_upprice'] == '' ? $value['pickcost'] : $value['tcarr_upprice'];
					// 干线费
					$arrlist[$key]['lineprice'] = $value['lcarr_price']== '' ? $value['linepice'] : $value['lcarr_price'];
					// 配送费
					$arrlist[$key]['peiprice'] = $value['pcarr_upprice']== '' ? $value['delivecost'] : $value['pcarr_upprice'];
					if ($value['picktype'] ==1 && $value['sendtype'] == 1){
                        $totalprice = $arrlist[$key]['tiprice'] + $arrlist[$key]['lineprice'] + $arrlist[$key]['peiprice'];
                    }elseif($value['picktype'] == 1 && $value['sendtype'] == 2){
                        $totalprice = $arrlist[$key]['tiprice'] + $arrlist[$key]['lineprice'];
                    }elseif($value['picktype'] == 2 && $value['sendtype'] == 1){
                        $totalprice =  $arrlist[$key]['lineprice'] + $arrlist[$key]['peiprice'];
                    }else{
                        $totalprice = $arrlist[$key]['lineprice'];
                    }
//					$totalprice = $arrlist[$key]['tiprice'] + $arrlist[$key]['lineprice'] + $arrlist[$key]['peiprice'];
					// 总费用
					$arrlist[$key]['totalprice'] = round($totalprice);
					// 司机确认到达时间
					$arrlist[$key]['arrivetime'] =$value['arrivetime'];
                    //仓库时间段
					$arrlist[$key]['stime'] = $value['stime'];
				}
			}
	        if(empty($arrlist)){
				return json(['code'=>'1001','message'=>'暂无数据']);
			}else{
				return json(['code'=>'1002','message'=>'查询成功','data'=>$arrlist]);
			}
	}

	/**
	 * 冷链零担订单详情
	 * @auther liyuan
	 * @date 2018-04-12
	 * @return [type] 对应承运公司的零担订单详情(包含提干配信息)
	 */
	public function bulkdetail() {
		// 令牌
		$token   = input("token");
		// 订单ID
		$oid   = input("oid");
		if(empty($token) || empty($oid)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 验证令牌
		$check_result = $this->check_token($token);
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		// 查询冷链零担订单详情
		$detail = Db::table("ct_order")->where('oid',$oid)->find();
		// 订单对应的班次信息
		$line = DB::table('ct_shift')
				->alias('b')
				->join('ct_already_city c','c.city_id = b.linecityid') // 线路
				->join('ct_district scity','scity.id = c.start_id') // 起点城市
				->join('ct_district ecity','ecity.id = c.end_id') // 终点城市
				->field('b.*,scity.name as startname,c.start_id,c.end_id,ecity.name as endname,b.shiftstate,b.companyid,b.driver_id')
				->where('sid',$detail['shiftid'])
				->find();

		//查询查看详情司机时候未该干线下员工 （1是该干线员工 2不是该干线员工）
		$driver_mess = Db::table('ct_driver')->field('companyid')->where('drivid',$driver_id)->find();
		$isCheckdriver = 2;
		if ($line['companyid'] == $driver_mess['companyid'] || $driver_id = $line['driver_id']) {
			$isCheckdriver = 1;
		}
		// 订单对应的下单人信息
		$user_mess = Db::table('ct_user')->field('username,realname,phone,image')->where('uid',$detail['userid'])->find();
		// 订单号
		$result['ordernumber']	= $detail['ordernumber'];
		// 班次状态1平台2用户
		$result['shiftstate']	= $line['shiftstate'];
		// 班次号
		$result['shiftnumber']	= $line['shiftstate']=='1' ? $line['shiftnumber'] : $line['weekday'];
		// 下单时间
		$result['add_time'] =    $detail['addtime'];
		// 起点城市
		$result['start_city_name'] = $line['startname'];
		// 终点城市
		$result['end_city_name'] = $line['endname'];
		// 件数
		$result['totalnumber']	= $detail['totalnumber'];
		// 重量
		$result['totalweight']	= $detail['totalweight'];
		// 体积
		$result['totalvolume']	= $detail['totalvolume'];
		// 物品类型
		$result['itemtype']	= $detail['itemtype'];
		// 冷冻类型
		$result['coldtype']	= $detail['coldtype'];
		// 提货时间
		$result['picktime']	= $detail['picktime'];
		// 备注
		$result['remark']	= $detail['remark'];
		//新加参数上门提货等
        $result['picktype'] = $detail['picktype'];
        $result['sendtype'] = $detail['sendtype'];
        $result['picksite'] = $detail['picksite'];
        $result['stime'] = $detail['stime'];
        $result['sphone'] = $detail['sphone'];
        $result['sendsite'] = $detail['sendsite'];
        $result['dtime'] =$detail['dtime'];
        $result['tphone'] = $detail['tphone'];
		if ($line['shiftstate'] =='2') {
			// 下单人姓名
			$result['realname'] = $user_mess['realname'] == '' ? $user_mess['username'] :  $user_mess['realname'];
			// 下单人电话
			$result['phone']	= $user_mess['phone'];
			if ($user_mess['image']=='') {
				$result['image'] =  get_url().'/static/defaultUserImg.png';
			}else{
				$result['image'] = get_url().$user_mess['image'];
			}

		}else{
			$result['realname'] = '赤途(上海)供应链管理有限公司';
			$result['phone'] = '4009-206-101';
			$result['image'] =  get_url().'/static/service_header.png';
		}
		// 订单状态
		$result['orderstate']	= $detail['orderstate']; //订单状态
        if($detail['pickaddress']){
            if ($isCheckdriver=='1') {
                // 提货地址
                $pickaddress = json_decode($detail['pickaddress'],TRUE);
                foreach ($pickaddress as $key => $value) {
                    $pick_arr[] = $line['startname'].$value['taddressstr'];
                }
            }else{
                $pick_arr[] = '***';
            }
            // 提货地址
            $result['goods_list_pick'] = $pick_arr;
        }else{
            $result['goods_list_pick'] = '';
        }
        if($detail['sendaddress']){

            // 送货地址
            $sendaddress = json_decode($detail['sendaddress'],TRUE);
            foreach ($sendaddress as $key => $val) {
                $send_arr[$key]['name'] = $isCheckdriver=='1' ? $val['name'] : '***';
                $send_arr[$key]['phone'] = $isCheckdriver=='1' ? $val['phone'] : '***';
                $send_arr[$key]['tabid'] = $val['tabid'];
                $send_arr[$key]['paddress'] = $line['endname'].$val['paddressstr'];
            }
            // 送货地址
            $result['goods_list_pei'] = $send_arr;
        }else{
            $result['goods_list_pei'] = '';
        }

		// 回单
		$receipts = json_decode($detail['receipt'],TRUE);
		$array = array();
		if (!empty($receipts)) {
			foreach ($receipts as $key => $value) {
				$array[$key]['key'] = $key;
				$array[$key]['value'] = $value;
			}
		}
		$result['receipts'] = $array;
		//用户反馈内容
		$contact_arr = array();
		$contacts_mess = Db::table('ct_order_contact')->where(array('orderid'=>$detail['oid'],'utype'=>'1','otype'=>'1'))->select();
		if (!empty($contacts_mess)) {
			foreach ($contacts_mess as $key => $value) {
				$user = Db::table('ct_user')->where(array('uid'=>$value['userid']))->find();
				$contact_arr[$key]['realname'] = $user['realname']=='' ? $user['username'] : $user['realname'];
				$contact_arr[$key]['phone'] = $user['phone'];
				$contact_arr[$key]['message'] = $value['message'];
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

	/**
	 * 冷链零担订单手动接单
	 * @auther liyuan
	 * @date 2018-04-12
	 * @return [type] [description]
	 */
	public function bulk_ask(){
		// 令牌
		$token   = input("token");
		// 订单id
		$oid   = input("oid");
		// 验证
		if(empty($token) ||  empty($oid)){
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
		$judge_order = DB::table('ct_order')->where('oid',$oid)->find();
		if ($judge_order['orderstate'] == 3) {
			return json(['code'=>'1012','ostate'=>1]);
			exit;
		}
		$upt_data = array(
			'driverid'=>$driver_id,
			'type'=>2,
			'status'=>'2', //接单状态1未接2已接3已完成
			'receivetime'=>time()
			);
		$upg_data = array(
			'affirm'=>'2',  //是否确认接单1未确认2已确认 3 系统确认
			'driverid'=>$driver_id,
			'receivetime'=>time()
			);
		$send_data = array(
			'status'=>'2',  //是否确认接单1未确认2已确认 3 系统确认
			'driverid'=>$driver_id,
			'recceivetime'=>time()
			);
		// 零担订单
		$bulkorder = Db::table("ct_order")->where('oid',$oid)->update(array('orderstate'=>3));
		// 提货单手动接单
		$ret = Db::table("ct_pickorder")->where('orderid',$oid)->update($upt_data);
		// 干线单手动接单
		$reg = Db::table("ct_lineorder")->where('orderid',$oid)->update($upg_data);
		// 配送单手动接单
		$rep = Db::table("ct_delorder")->where('orderid',$oid)->update($send_data);
		if($bulkorder && $ret && $reg && $rep){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 冷链零担订单回单上传
	 * @auther liyuan
	 * @date 2018-04-12
	 * @return [type] [description]
	 */
	public  function bulkback(){
		// 令牌
		$token   = input("token");
		// 订单ID
		$oid   = input("oid");
		if(empty($token) ||  empty($oid)){
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
	    // 查找是否有已经有回单上传
	    $find = Db::table("ct_order")->field('receipt')->where('oid',$oid)->find();
	    $arr_list = array();
	     if(!empty($_FILES['back_img_1']['tmp_name'])){
			$re_1 = $this->file_upload('back_img_1','jpg,gif,png,jpeg',"bulk_back");
	        $file_url= $re_1['file_path']; //源文件地址
	        if (!empty($find['receipt'])) {
	        	$arr_list = json_decode($find['receipt'],TRUE);
	        	array_push($arr_list, $file_url);
	        }else{
	        	$arr_list[] =$file_url;
	        }
	    }

        $upda_data['receipt'] = json_encode($arr_list);
       	$upda_data['arrivetime'] = time();
        $re = Db::table("ct_order")->where('oid',$oid)->update($upda_data);

		if($re){

			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**
	 * 个人中心-查看货源列表
	 * @Auther: 李渊
	 * @Date: 2018.7.10
	 * @param 	[string]	token 	用户令牌
	 * @return 	[type] 		[description]
	 */
	public function issue_item_list(){
		// 用户令牌
		$token = input("token");
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
		// 筛选条件已支付
		$condition['o.paystate'] = 2;
		// 货源信息
		$condition['o.ordertype'] = 1;
		// 查询数据
		$result = Db::table("ct_issue_item")
				->alias('o')
				->join('ct_cartype car','car.car_id = o.carid','LEFT')
				->field('o.*,car.carparame')
				->order('addtime desc')
				->where($condition)
				->select();
		//
		$array = array();
		$i=0;
		foreach ($result as $key => $value) {
			$get_driver = json_decode($value['driverid'],TRUE);
			if (!empty($get_driver)) {
				if (in_array($driver_id, $get_driver)) {  //查找该司机是否支付过
					$array[$i] = $value;
		            // 起点地址
		            $array[$i]['start_address'] = idToAddress('',$value['start_city'],$value['start_area']);
		            // 终点地址
		            $array[$i]['end_address'] = idToAddress('',$value['end_city'],$value['end_area']);
		            // 重量
		            $array[$i]['weight'] = $value['weight'] ? ($value['weight']/1000).'吨' : '';
		            // 立方
		            $array[$i]['volume'] = $value['volume'] ? $value['volume'].'方' : '';
		            // 包车类型
		            $array[$i]['carriage'] = $value['carriage'] == 1 ? '拼车' : '包车';

					$i++;
				}
			}
		}
		if(empty($array)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$array]);
		}
	}

	/**
	 * 个人中心-查看货源详情
	 * @Auther: 李渊
	 * @Date: 2018.7.10
	 * @param 	[string]	token 	用户令牌
	 * @param 	[Int]		orderid 订单ID
	 * @return 	[type] 		[description]
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
			$search_driver = $check_result['driver_id'];
		}
		// 查询详情数据
		$detail = Db::table("ct_issue_item")
                ->alias('o')
                ->join('ct_user u','u.uid = o.userid')
                ->join('ct_cartype car','car.car_id = o.carid','LEFT')
                ->field('o.*,u.image,car.carparame')
                ->where('o.id',$orderid)
                ->find();
		// 下单人图像
		if ($detail['image']=='') {
			$detail['image'] =  get_url().'/static/defaultUserImg.png';
		}else{
			$detail['image'] = get_url().$detail['image'];
		}

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

		if(empty($detail)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
		}
	}
	/*
	 * 取消押金退款
	 * */
	public function refund(){
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
        // 查找承运端登陆人信息
        $user_money = Db::table('ct_driver')->where('drivid',$driver_id)->find();

        // 插入记录 状态 ( 1审核中 2审核成功，3审核失败，4打款完成 5取消退款)
        $insert_data['states'] = '5';
        // 插入记录 支付宝账号
//        $insert_data['account'] = $account;


        $money = Db::table('ct_application')->field('id,money')->where(array('action_type' => 1,'states' => 1,'action_id' => $driver_id))->find();
        // 更新司机余额
        $dmoney = $user_money['deposit'];
        // 更新司机余额
        $re = Db::table('ct_driver')->where('drivid',$driver_id)->update(array('deposit'=>$dmoney,'destate'=>'2'));
        // 判断是否更新成功
        if ($re) {
            // 插入提现记录并返回id
            Db::table("ct_application")->where('id',$money['id'])->update($insert_data);
            // 司机资产变动明细动态
            $balance_data=array(
                'pay_money'=>$money['money'],
                'order_content'=>"取消押金退款",
                'action_type'=>'1', //操作类型： 1 增加 2 扣除
                'orderid' => $money['id'],
                'ordertype' => '9',
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
     * 获取用户押金余额
     * */
    public function getDeposit(){
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
       $data=  Db::table('ct_driver')->where('drivid',$driver_id)->field('deposit,destate')->find();
        if ($data){
            return json(['code'=>'1001','message'=>'操作成功','data'=>$data]);
        }else{
            return json(['code'=>'1002','message'=>'操作失败']);
        }
    }
    /*
     * 查看押金退款状态
     * */
    public function getState(){
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
       $result =  Db::table('ct_application')
            ->where(array('action_type' => 1,'action_id' => $driver_id))
            ->field('states')
            ->order('start_time DESC')
            ->limit(1)
            ->find();
        $res = Db::table('ct_driver')->field('destate')->where('drivid',$driver_id)->find();
        if ($result['states']==2 && $res['destate']==3){
            Db::table('ct_driver')->where('drivid',$driver_id)->update(array('destate'=>1,'deposit'=>0));
            return json(['code'=>'1001','message'=>'操作成功']);
        }else{
            return json(['code'=>'1002','message'=>'无任何操作']);
        }
    }
}


