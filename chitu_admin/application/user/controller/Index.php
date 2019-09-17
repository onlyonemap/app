<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Index  extends Base{

    public function index(){
        return json(['code'=>110,'message'=>'赤途(上海)供应链管理有限公司']);
	}
    

    /**
     * app版本更新状态
     * @Auther: 李渊
     * @Date: 2018.8.10
     * @return [type] [description]
     */
    public function upgrade(){
        // 苹果更新状态
        $ios_state  =  Db::table("ct_config")->field("auth_price")->where('id',5)->find();
        // 安卓更新状态
        $android_state  =  Db::table("ct_config")->field("auth_price")->where('id',6)->find();
        // 苹果版本号
        $ios_version  = Db::table("ct_config")->field("auth_price")->where('id',7)->find();
        // 安卓版本号
        $android_version  = Db::table("ct_config")->field("auth_price")->where('id',16)->find();
        // 返回状态
        return json([
            'ios'=>$ios_state['auth_price'],
            'android'=>$android_state['auth_price'],
            'ios_version'=>$ios_version['auth_price'],
            'android_version'=>$android_version['auth_price']
        ]);
    }

    /**
     * 获取轮播图
     * @Auther: 李渊
     * @Date: 2018.8.2
     * @param  [type] $type     [手机号]
     * @param  [type] $apptype  [验证码]
     * @param  [type] $delstate [密码]
     * @return [type]           [description]
     */
    public  function adv_list(){
        $conditon['type'] = '1';
        $conditon['apptype'] = '1';
        $conditon['delstate'] = '1';
        $result  =  Db::table("ct_banner")->field("picture,lineurl")->where($conditon)->select();
        if($result){
            return json(['code'=>'1001','message'=>'查询成功','date'=>$result]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }

    /**
     * 用户注册
     * 注册赠送50元优惠卷
     * @Auther: 李渊
     * @Date: 2018.8.2
     * @param  [type] $phone    [手机号]
     * @param  [type] $yzm_code [验证码]
     * @param  [type] $password [密码]
     * @return [type]        [description]
     */
    public function register(){
        // 获取手机号
    	$phone = input('phone');
        // 获取验证码
        $yzm_code = input('yzm_code');
        // 获取密码
        $password = input('password');
        // 判断手机号密码
        if(empty($phone)  ||  empty($yzm_code)  || empty($password)){
        	 return json(['code'=>'1000','message'=>'参数错误']);
        }
        // 移除手机号两边的空白字符
        $data['phone'] = trim($phone);
        // 是否注销 1否 2是
        $data['delstate'] = '1';
        // 查询数据
        $if_exf = Db::table("ct_user")->where($data)->find();  
        // 判断是否已经注册
        if($if_exf){
            return json(['code'=>'1007','message'=>'用户已存在']);
        }  
        // 获取发送的验证码
        $record = Db::table("ct_validate_record")->where("phone = $phone")->find();
        // 检查发送短信验证码的手机号码和提交的手机号码是否匹配
        if(iconv_strlen($yzm_code) > 4){ // 验证码不能超过四位数字
        	return json(['code'=>'1001','message'=>'验证码不能超过四位数字']);
        }else if($record['yzm'] != $yzm_code){ // 验证码是否正确
        	return json(['code'=>'1002','message'=>'验证码不正确！']);
        }elseif($record['expired_time'] < time()){ // 检查过期时间
        	return json(['code'=>'1003','message'=>'验证码已过期！']);
        }
        /*** 插入数据库 ***/
        $insert_data = array(
            'phone'=>$phone,
            'userstate'=>'1',
            'user_grade'=>'3',
            'password'=>MD5($password."ct888"),
            'username'=>'Chitu'.mt_rand('1000','9999'),
            'addtime'=>time()
        );
        // 插入数据并返回id
        $userId = Db::table("ct_user")->insertGetId($insert_data);
        // 判断是否插入成功
        if($userId){
            // 删除验证码记录
            $this->delete_yzm($phone); 
            // 查询注册赠送的50元优惠卷
            $coun = DB::table('ct_coupon')->where(array('cou_number'=>'50','state'=>1,'coutype_id'=>1))->find();
            // 设置优惠卷id
            $coupon_data['coup_id'] = $coun['cou_id'];
            // 设置用户id
            $coupon_data['userid'] = $userId;
            // 设置优惠卷状态 1未使用 2已使用 3已过期
            $coupon_data['failure'] = '1';
            // 设置优惠卷开始时间
            $coupon_data['time_start'] = time();
            // 设置优惠卷结束时间
            $coupon_data['time_end'] = time()+86400*$coun['time_day'];
            // 插入优惠卷
            Db::table('ct_coupon_user')->insert($coupon_data);
            // 返回状态码
            return json(['code'=>'1005','message'=>'注册成功']);
        }else{
        	return json(['code'=>'1006','message'=>'注册失败']);
        }
    }

    /**
     * 用户登录
     * @Auther: 李渊
     * @Date: 2018.8.2
     * @param  [type] $phone    [手机号]
     * @param  [type] $password [密码]
     * @return [type]        [description]
     */
    public function user_login(){
        // 获取手机号
    	$phone     =  input("phone");
        // 获取密码
		$password  =  input("password");
        // 判断参数是否正确
		if(empty($phone) || empty($password)){
			 return json(['code'=>'1000','message'=>'参数错误']);
		}
        // 查询数据
		$result  = 	Db::table("ct_user")->where("phone = $phone and delstate = 1")->find();
        // 判断是否有此账号
		if ($result) {
            // 加密密码
            $new_password = MD5($password."ct888");
            // 判断密码是否正确
			if($new_password == $result['password']){
                // 销毁密码
                unset($result['password']);
				// 获取身份令牌
				$result['token'] = $this->product_token($result['uid']);
                // 判断图像
                if ($result['image']!='') {
                    $result['image'] = get_url().$result['image'];
                }else{
                    $result['image'] = get_url().'/static/defaultUserImg.png';
                }
                // 获取常用地址
                $result['address'] = $this->get_default_address($result['uid']);
                // 获取常用联系人
                $result['contact'] = $this->get_default_contact($result['uid']);
                // 返回个人数据
				return json(['code'=>'1001','message'=>'登录成功','data'=>$result]);
			}else{
				return json(['code'=>'1002','message'=>'密码错误']);
			}

		} else { // 用户不存在

            /*** 插入数据库 ***/
            $insert_data = array(
                'phone'=>$phone,
                'userstate'=>'1',
                'user_grade'=>'3',
                'password'=>MD5($password."ct888"),
                'username'=>'Chitu'.mt_rand('1000','9999'),
                'addtime'=>time()
            );
            // 插入数据并返回id
            $userId = Db::table("ct_user")->insertGetId($insert_data);
            // 判断是否插入成功
            if($userId){
                // 查询数据
                $result  =  Db::table("ct_user")->where("uid",$userId)->find();
                // 获取身份令牌
                $result['token'] = $this->product_token($result['uid']);
                // 用户图像
                $result['image'] = get_url().'/static/defaultUserImg.png';
                // 查询注册赠送的50元优惠卷
                $coun = DB::table('ct_coupon')->where(array('cou_number'=>'50','state'=>1,'coutype_id'=>1))->find();
                // 设置优惠卷id
                $coupon_data['coup_id'] = $coun['cou_id'];
                // 设置用户id
                $coupon_data['userid'] = $userId;
                // 设置优惠卷状态 1未使用 2已使用 3已过期
                $coupon_data['failure'] = '1';
                // 设置优惠卷开始时间
                $coupon_data['time_start'] = time();
                // 设置优惠卷结束时间
                $coupon_data['time_end'] = time()+86400*$coun['time_day'];
                // 插入优惠卷
                Db::table('ct_coupon_user')->insert($coupon_data);
                // 返回状态码
                return json(['code'=>'1001','message'=>'登录成功','data'=>$result]);
            }else{
                return json(['code'=>'1002','message'=>'登录失败']);
            }
		}
    }

    /**
     * 用户登陆
     * 没有账号的则注册
     * 注册赠送50元优惠卷
     * @Auther: 李渊
     * @Date: 2018.9.26
     * @param  [type] $phone    [手机号]
     * @param  [type] $yzm_code [验证码]
     * @return [type]        [description]
     */
    public function user_code_login(){
        // 获取手机号
        $phone = input('phone');
        // 获取验证码
        $yzm_code = input('yzm_code');
        // 判断手机号密码
        if(empty($phone)  ||  empty($yzm_code)){
             return json(['code'=>'1000','message'=>'参数错误']);
        }
        // 移除手机号两边的空白字符
        $data['phone'] = trim($phone);
        // 是否注销 1否 2是
        $data['delstate'] = '1';
        // 获取发送的验证码
        $record = Db::table("ct_validate_record")->where("phone = $phone")->find();
        // 检查发送短信验证码的手机号码和提交的手机号码是否匹配
        if(iconv_strlen($yzm_code) > 4){ // 验证码不能超过四位数字
            return json(['code'=>'1002','message'=>'验证码不能超过四位数字']);
        }else if($record['yzm'] != $yzm_code){ // 验证码是否正确
            return json(['code'=>'1002','message'=>'验证码不正确！']);
        }elseif($record['expired_time'] < time()){ // 检查过期时间
            return json(['code'=>'1003','message'=>'验证码已过期！']);
        }
        // 查询数据
        $if_exf = Db::table("ct_user")->where($data)->find();  
        // 判断是否已经注册
        if ($if_exf) {
            // 获取身份令牌
            $if_exf['token'] = $this->product_token($if_exf['uid']);
            // 判断图像
            if ($if_exf['image']!='') {
                $if_exf['image'] = get_url().$if_exf['image'];
            }else{
                $if_exf['image'] = get_url().'/static/defaultUserImg.png';
            }
            // 获取常用地址
            $if_exf['address'] = $this->get_default_address($if_exf['uid']);
            // 获取常用联系人
            $if_exf['contact'] = $this->get_default_contact($if_exf['uid']);
            // 返回个人数据
            return json(['code'=>'1001','message'=>'登录成功','data'=>$if_exf]);
        }  

        /*** 如果没有注册过，则自动注册一下并返回注册成功后数据 ***/
        
        // 插入数据
        $insert_data = array(
            'phone'=>$phone,
            'userstate'=>'1',
            'user_grade'=>'3',
            'password'=>MD5("666666ct888"),
            'username'=>'Chitu'.mt_rand('1000','9999'),
            'addtime'=>time()
        );
        // 插入数据并返回id
        $userId = Db::table("ct_user")->insertGetId($insert_data);
        // 判断是否插入成功
        if($userId){
            // 查询数据
            $result  =  Db::table("ct_user")->where("uid",$userId)->find();
            // 获取身份令牌
            $result['token'] = $this->product_token($result['uid']);
            // 用户图像
            $result['image'] = get_url().'/static/defaultUserImg.png';
            
            // 删除验证码记录
            $this->delete_yzm($phone); 
            
            // 查询注册赠送的50元优惠卷
            $coun = DB::table('ct_coupon')->where(array('cou_number'=>'50','state'=>1,'coutype_id'=>1))->find();
            // 设置优惠卷id
            $coupon_data['coup_id'] = $coun['cou_id'];
            // 设置用户id
            $coupon_data['userid'] = $userId;
            // 设置优惠卷状态 1未使用 2已使用 3已过期
            $coupon_data['failure'] = '1';
            // 设置优惠卷开始时间
            $coupon_data['time_start'] = time();
            // 设置优惠卷结束时间
            $coupon_data['time_end'] = time()+86400*$coun['time_day'];
            // 插入优惠卷
            Db::table('ct_coupon_user')->insert($coupon_data);
            // 返回状态码
            return json(['code'=>'1001','message'=>'登录成功','data'=>$result]);
        }else{
            return json(['code'=>'1002','message'=>'登录失败']);
        }
    }

    /**
     * 发送验证码
     * @Auther: 李渊
     * @Date: 2018.8.2
     * @param  [type] $phone    [手机号]
     * @return [type]        [description]
     */
    public  function yzm_send(){
        // 获取手机号
    	$phone= input("phone");
        // 判断是否传值
    	if(empty($phone)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
        // 判断手机号格式
		if(preg_match("/^1[345678]{1}\d{9}$/",$phone)){  
            // 请求短信接口
		    $result = send_sms('1',$phone);    
            // 判断是否发送成功
		    if($result['status'] == '1'){
                // 判断是否存在手机号
                $phone_mes = Db::table("ct_validate_record")->where('phone',$phone)->find();
                // 没有则插入有则更改验证吗
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

    /**
     * 城配开通城市
     * 更改返回的字段值保持与零担开通城市的字段值一致
     * @Auther: 李渊
     * @Data: 2018.7.18
     * @return [type] [description]
     */
    public function open_city_with(){
        $result = DB::table('ct_city_cost')->field('c_city')->where('delstate',1)->select();
        $arr = array();
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $search = Db::table('ct_district')->where('id',$value['c_city'])->find();
                $parent = Db::table('ct_district')->where('id',$search['parent_id'])->find();
                $arr[$key]['text'] = $search['name'];
                $arr[$key]['value'] = $value['c_city'];
                $arr[$key]['proText'] = $parent['name'];
                $arr[$key]['proValue'] = $parent['id'];
            }
        }
        if(empty($arr)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1002','message'=>'查询成功','data'=>$arr]);
        }   
    }

    /**
     * 最新内容
     */
    public function index_roll_mess(){
        $arr = array();
        $where['s.delstate'] =1;
        $where['s.whethertoopen'] =1;
        $result_com = DB::table('ct_shift')
                    ->alias('s')
                    ->join('ct_already_city a','a.city_id=s.linecityid')
                    ->field('a.end_id,a.start_id,s.addtime,s.dewin,s.weekday,s.shiftstate')
                    ->where($where)
                    ->order('addtime desc')
                    ->limit(10)
                    ->select();
        foreach ($result_com as $key => $value) {
            $city_start = detailadd($value['start_id'],'','');
            $city_end = detailadd($value['end_id'],'','');
            if ($value['shiftstate'] =='2') {
                $str = $value['weekday'];
            }else{
                $str = '每' .$value['dewin'].'准点发班';
            }
            $arr[$key]['message'] = $city_start.'—'.$city_end.' '.$str;
            $arr[$key]['addtime'] = $value['addtime'];
        }
        $array = array();
        $balance = Db::table('ct_balance')
                ->alias('b')
                ->join('ct_user u','u.uid=b.userid')
                ->field('b.ordertype,b.orderid,u.username,b.addtime')
                ->order('addtime desc')
                ->limit(10)
                ->select();
        foreach ($balance as $key => $value) {
            if ($value['ordertype'] =='1') {
               $order = Db::table('ct_order')
                    ->alias('a')
                    ->join('ct_shift s','s.sid=a.shiftid')
                    ->join('ct_already_city c','c.city_id = s.linecityid')
                    ->field('c.start_id,c.end_id')
                    ->where('oid',$value['orderid'])
                    ->find();
                $city_start = detailadd($order['start_id'],'','');
                $city_end = detailadd($order['end_id'],'','');
                $message = $value['username'] . ' 发布了 '.$city_start.'—'.$city_end.'零担订单';
            }elseif ($value['ordertype'] =='2') {
                $shift = Db::table('ct_shift_order')
                    ->alias('o')
                    ->join('ct_fixation_line f','o.shiftid=f.id')
                    ->join('ct_already_city c','c.city_id=f.lienid')
                    ->field('c.start_id,c.end_id')
                    ->where('s_oid',$value['orderid'])
                    ->find();
                $city_start = detailadd($shift['start_id'],'','');
                $city_end = detailadd($shift['end_id'],'','');
                $message = $value['username'] . ' 发布了 '.$city_start.'—'.$city_end.'定制订单';
            }elseif ($value['ordertype'] =='3') {
                $city = Db::table('ct_city_order')
                    ->field('city_id')
                    ->where('id',$value['orderid'])
                    ->find();
                $city_start = detailadd($city['city_id'],'','');
                $message = $value['username'] . ' 发布了 '.$city_start.'市内订单';

            }elseif ($value['ordertype'] =='4') {
                $carload = Db::table('ct_userorder')
                    ->field('startcity,endcity')
                    ->where('uoid',$value['orderid'])
                    ->find();
                $city_start = detailadd($carload['startcity'],'','');
                $city_end = detailadd($carload['endcity'],'','');
                $message = $value['username'] . ' 发布了 '.$city_start.'—'.$city_end.'整车订单';
            }
            $array[$key]['message'] = $message;
            $array[$key]['addtime'] = $value['addtime'];
        }
        $list = array_merge($arr,$array);
        if (!empty($list)) {
            $list = $this->my_sort($list,'addtime',SORT_DESC);
        }
        return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
    }
}
