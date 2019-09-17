<?php
namespace app\driver\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Index extends Base{

    public function index(){
        // 指定json数据输出
        return json(['code'=>110,'message'=>'赤途(上海)供应链管理有限公司']);
	}

    // 广告图
    public  function adv_driver_list(){
        $conditon['type'] = '1';
        $conditon['apptype'] = '2';
        $conditon['delstate'] = '1';
        $result  =  Db::table("ct_banner")->field("picture,lineurl")->where($conditon)->select();
        if($result){
            return json(['code'=>'1001','message'=>'查询成功','date'=>$result]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }
    
    /**
     * app版本更新状态
     * @Auther: 李渊
     * @Date: 2018.8.10
     * @return [type] [description]
     */
    public function upgrade(){
        // 苹果更新状态
        $ios_state  =  Db::table("ct_config")->field("auth_price")->where('id',9)->find();
        // 安卓更新状态
        $android_state  =  Db::table("ct_config")->field("auth_price")->where('id',10)->find();
        // 苹果版本号
        $ios_version  = Db::table("ct_config")->field("auth_price")->where('id',11)->find();
        // 安卓版本号
        $android_version  = Db::table("ct_config")->field("auth_price")->where('id',17)->find();
        // 返回状态
        return json([
            'ios'=>$ios_state['auth_price'],
            'android'=>$android_state['auth_price'],
            'ios_version'=>$ios_version['auth_price'],
            'android_version'=>$android_version['auth_price']
        ]);
    }

    /**
     * 获取用户数据
     */
    public  function user_number(){
        $file = "../public/uploads/userorder.json";
        $message = file_get_contents($file);
        $result = json_decode($message,'TRUE');
        return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
    }
    
    /** 
     * 生成某个范围内的随机时间 
     * @param <type> $begintime  起始时间 格式为 Y-m-d H:i:s 
     * @param <type> $endtime    结束时间 格式为 Y-m-d H:i:s   
     */  
    public function randomDate($begintime, $endtime="") {  
        $begin = strtotime($begintime);  
        $end = $endtime == "" ? mktime() : strtotime($endtime);  
        $timestamp = rand($begin, $end);  
        return  $timestamp;  
    } 

	//司机注册
    public function register(){
    	$mobile = input('mobile');
        $yzm_code = input('yzm_code');
        $password = input('password');
        $re_password = input('re_password');
        if(empty($mobile)  ||  empty($yzm_code)  || empty($password)  || empty($re_password)){
        	 return json(['code'=>'1000','message'=>'参数错误']);
        }
        //验证手机号是否已注册
        $data['mobile'] = trim($mobile);
        $data['delstate'] = '1';
        $if_exf = Db::table("ct_driver")->where($data)->find();  
        if($if_exf){
            return json(['code'=>'1007','message'=>'用户已存在']);
            exit();
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

        /**** 插入数据库*/
        $insert_data = array(
            'mobile'=>$mobile,
            'password'=>MD5($password."ct888"),
            'addtime'=>time(),
            'username'=>'Chitu'.mt_rand('1000','9999'),
            'driver_grade'=>'1',
            'type'=>'1'  //用户类型：1司机 2调度 3管理
        );
        $result = Db::table("ct_driver")->insert($insert_data);

        if($result){
             $this->delete_yzm($mobile); //删除验证码记录
            return json(['code'=>'1005','message'=>'注册成功']);
        }else{
        	return json(['code'=>'1006','message'=>'注册失败']);
        }
    } 

    /**
     * 用户登陆
     * @auther:李渊
     * @date: 2018.9.26
     * 手机号密码登陆
     * 如果没有账户则注册
     * @param  [Number] [mobile]   [手机号]
     * @param  [String] [password] [密码]
     * @return [type] [description]
     */
    public function driver_login(){
        // 手机号
    	$mobile = input("mobile");
        // 密码
		$password = input("password");
        // 设备id
        $clientid = input("clientid");
        // 判断参数是否正确
		if(empty($mobile) || empty($password)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
        // 查询用户数据
		$result = Db::table("ct_driver")->where("mobile = $mobile and delstate = 1")->find();

        // 判断用户是否存在
		if ($result) {
            // 如果是个体用户
            if ($result['type']=='1'  && $result['companyid']=='') {
                // 查询车辆数据
                $cardata = Db::field('status')->table('ct_carcategory')->where('driverid',$result['drivid'])->select();
                // 判断驾驶证审核状态
                $result['driverstatus'] = $result['drivingimage'] ? $result['carstatus'] : 1;
                // 默认车辆审核未审核
                $result['carstatus'] = 1;
                // 判断车辆认证状态
                if (!empty($cardata)) {
                    foreach ($cardata as $key => $value) {
                        if ($value['status'] == '2') {
                            $result['carstatus'] = 2;
                        }
                    }
                }
            } else {
                $result['driverstatus'] = 2; //司机驾驶证认证
                $result['carstatus'] = 2; //车辆认证
            }

            $new_password = MD5($password."ct888");
            if ($result['gclientid'] != $clientid) {
                $data['gclientid'] = $clientid;
                Db::table("ct_driver")->where('drivid',$result['drivid'])->update($data);
            }
			if($new_password == $result['password']){
				// 销毁密码
				unset($result['password']);
				// 获取身份令牌
				$result['token'] = $this->product_token($result['drivid']);
                $result['image'] = $result['image'] ? get_url().$result['image'] : $result['image'];
				return json(['code'=>'1001','message'=>'登录成功','data'=>$result]);
			}else{
				return json(['code'=>'1002','message'=>'密码错误']);
			}
		} 

        /*** 没有账户则注册 ***/

        // 注册数据
        $insert_data = array(
            'mobile'=>$mobile,
            'password'=>MD5($password."ct888"),
            'addtime'=>time(),
            'username'=>'Chitu'.mt_rand('1000','9999'),
            'driver_grade'=>'1',
            'type'=>'1'  //用户类型：1司机 2调度 3管理
        );
        // 插入数据
        $insert = Db::table("ct_driver")->insertGetId($insert_data);
        // 判断是否插入成功
        if ($insert) {
            // 获取数据
            $driverInfo = Db::table("ct_driver")->where('drivid',$insert)->find();
            // 设置驾驶证认证状态为1 未认证
            $driverInfo['driverstatus'] = 1;
            // 设置车辆认证状态
            $driverInfo['carstatus'] = 1;
            // 设置用户token
            $driverInfo['token'] = $this->product_token($driverInfo['drivid']);
            // 返回状态和数据
            return json(['code'=>'1001','message'=>'登录成功','data'=>$driverInfo]);
        } else {
            return json(['code'=>'1003','message'=>'登录失败']);
        }
    }

    /**
     * 用户登陆
     * @auther:李渊
     * @date: 2018.9.26
     * 手机号验证码登陆
     * 如果没有注册则直接注册
     * @param  [Number] [mobile]   [手机号]
     * @param  [Number] [yzm_code] [验证吗]
     * @param  [Number] [clientid] [设备id]
     * @return [type] [description]
     */
    public function driver_code_login(){
        // 手机号
        $mobile   = input("mobile");
        // 验证码
        $yzm_code = input('yzm_code');
        // 设备id
        $clientid = input("clientid");
        // 验证参数
        if(empty($mobile) || empty($yzm_code)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        // 查询数据
        $result  =  Db::table("ct_driver")->where("mobile = $mobile and delstate = 1")->find();
        //获取发送的验证码
        $record = Db::table("ct_validate_record")->where("phone = $mobile")->find();
        // 检查发送短信验证码的手机号码和提交的手机号码是否匹配
        if(iconv_strlen($yzm_code) > 4){
            return json(['code'=>'1001','message'=>'验证码不能超过四位数字']);
        }else if($record['yzm'] != $yzm_code){
            return json(['code'=>'1002','message'=>'验证码不正确！']);
            // 检查过期时间
        }elseif($record['expired_time'] < time()){
            return json(['code'=>'1003','message'=>'验证码已过期！']);
        }
        // 删除验证码记录
        $this->delete_yzm($mobile); 
        // 判断是否存在用户
        if ($result) {
            if ($result['type']=='1'  && $result['companyid']=='') {
                $cardata = Db::field('status')->table('ct_carcategory')->where('driverid',$result['drivid'])->select();
                // 判断驾驶证审核状态
                $result['driverstatus'] = $result['drivingimage'] ? $result['carstatus'] : 1;
                // 默认车辆审核未审核
                $result['carstatus'] = 1;
                if (!empty($cardata)) {
                    foreach ($cardata as $key => $value) {
                        if ($value['status'] == '2') {
                            $result['carstatus'] = 2;
                        }
                    }
                }
            }else{
                $result['driverstatus'] = 2; //司机驾驶证认证
                $result['carstatus'] = 2; //车辆认证
            }
            
            if ($result['gclientid'] != $clientid) {
                $data['gclientid'] = $clientid;
                Db::table("ct_driver")->where('drivid',$result['drivid'])->update($data);
            }
            // 获取身份令牌
            $result['token'] = $this->product_token($result['drivid']);
            $result['image'] = $result['image'] ? get_url().$result['image'] : $result['image'];
            return json(['code'=>'1001','message'=>'登录成功','data'=>$result]);
        }

        /*** 没有账户则注册 ***/

        // 注册数据
        $insert_data = array(
            'mobile'=>$mobile,
            'password'=>MD5("666666ct888"),
            'addtime'=>time(),
            'username'=>'Chitu'.mt_rand('1000','9999'),
            'driver_grade'=>'1',
            'type'=>'1'  //用户类型：1司机 2调度 3管理
        );
        // 插入数据
        $insert = Db::table("ct_driver")->insertGetId($insert_data);
        // 判断是否插入成功
        if ($insert) {
            // 获取数据
            $driverInfo = Db::table("ct_driver")->where('drivid',$insert)->find();
            // 设置驾驶证认证状态为1 未认证
            $driverInfo['driverstatus'] = 1;
            // 设置车辆认证状态
            $driverInfo['carstatus'] = 1;
            // 设置用户token
            $driverInfo['token'] = $this->product_token($driverInfo['drivid']);
            // 返回状态和数据
            return json(['code'=>'1001','message'=>'登录成功','data'=>$driverInfo]);
        } else {
            return json(['code'=>'1003','message'=>'登录失败']);
        }

    }

    //发送验证码
    public  function yzm_send(){
    	$phone= input("phone");
    	if(empty($phone)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		if(preg_match("/^1[345678]{1}\d{9}$/",$phone)){  
		    $result = send_sms('1',$phone);    //请求短信接口
		    if($result['status'] == '1'){
                $phone_mes = Db::table("ct_validate_record")->where('phone',$phone)->find();
                if(!empty($phone_mes)){
                    $updata = array(
                        'yzm'=>$result['verify'],
                        'expired_time'=>strtotime('now')+10*60   //过期时间:10分钟
                    );
                    Db::table("ct_validate_record")->where('phone',$phone)->update($updata);  
                }else{
                    $indate = array(
                        'phone'=>$phone,
                        'yzm'=>$result['verify'],
                        'expired_time'=>strtotime('now')+10*60   //过期时间:10分钟
                     );
                    Db::table("ct_validate_record")->insert($indate);    
                }
		    	$data['yzm_code'] = $result['verify'];
		    	return json(['code'=>'1001','message'=>'发送成功','data'=>$data]);
		    }else{
		    	return json(['code'=>'1002','message'=>'发送失败']);
		    }
		}else{  
		    return json(['code'=>'1003','message'=>'手机号格式错误']);
		}  

    }

  

}
