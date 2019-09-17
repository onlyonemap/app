<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
use  think\Db;  //使用数据库操作

/**
 * 对象 转 数组
 * @param object $obj 对象
 * @return array
 */
function object_to_array($obj) {
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
 * 获取当前的url 地址
 * @return [type] [description]
 */
function get_url() {
    $sys_protocal = isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443' ? 'https://' : 'http://';
    return $sys_protocal.(isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '');
}

/**
 * 查找详细地址信息 上海-嘉定区-江桥镇
 * @param  [type] $proid  [省ID]
 * @param  [type] $cityid [市ID]
 * @param  [type] $areaid [区ID]
 * @return [type]         [description]
 */
function detailadd($proid,$cityid,$areaid){
	$result1 =  DB::table('ct_district')->where(array('id'=>$proid))->find();
	$result2 =  DB::table('ct_district')->where(array('id'=>$cityid))->find();
	$result3 =  DB::table('ct_district')->where(array('id'=>$areaid))->find();
	return $result1['name'] . $result2['name'] .  $result3['name'];
}
function findcity($city){
//    $result1 =  DB::table('ct_district')->where('name','like',"%".$proid."%")->find();
    $result2 =  DB::table('ct_district')->where('name','like',$city."%")->find();
//    $result3 =  DB::table('ct_district')->where('name','like',"%".$area."%")->find();
    return  $result2['name'];
}

/**
 * 根据地址id返回对应的地址名称
 * 如：省  1		 	返回 北京市
 * 如：市  45052 	返回 北京市
 * 如：区  37    	返回 东城区
 * @Auther: 李渊
 * @Date: 2018.7.9
 * @param 	string 	$id 	[地址id]
 * @return 	[type]  $name   [地址名称]
 */
function addresidToName($id){
	$result =  DB::table('ct_district')->where(array('id'=>$id))->find();
	return $result['name'];
}

/**
 * 根据地址id返回对应的地址名称并过滤省市
 * 如：省  1		 	返回 北京市
 * 如：市  45052 	返回 北京市
 * 如：区  37    	返回 东城区
 * @Auther: 李渊
 * @Date: 2018.7.9
 * @param 	string 	$id 	[地址id]
 * @return 	[type]  $name   [地址名称]
 */
function idToAddress($proid,$cityid,$areaid){
	$result1 =  DB::table('ct_district')->where(array('id'=>$proid))->find();
	$result2 =  DB::table('ct_district')->where(array('id'=>$cityid))->find();
	$result3 =  DB::table('ct_district')->where(array('id'=>$areaid))->find();
	// 获取省
	$pro = $result1['name'] ? $result1['name'].'·' : '';
	// 获取市
	if ($result3['name']) {
		$city = $result2['name'] ? $result2['name'].'·' : '';
	} else {
		$city = $result2['name'] ? $result2['name'] : '';
	}
	// 获取区
	$area = $result3['name'] ? $result3['name'] : '';
    // 如果是直辖市则过滤掉
    if ($pro == $city) {
    	$pro = '';
    }
    // 过滤省
    $pro = str_replace("省","",$pro);
    // 过滤市
    $city = str_replace("市","",$city);
    // 返回地址
	return $pro.$city.$area;    
}

/**
 * 根据id返回对应的省市区名称
 * 如：省  1		 	返回 北京市
 * 如：市  45052 	返回 北京市
 * 如：市  xxx      	返回 xx省xx市
 * 如：区  37    	返回 北京市东城区
 * @Auther: 李渊
 * @Date: 2018.7.9
 * @param 	string 	$id 	[地址id]
 * @return 	[type]  $name   [地址名称]
 */
function cityidToProcity($id)
{
	// 查询数据
	$resultOne =  DB::table('ct_district')->where(array('id'=>$id))->find();
	// 判断是否是省
	switch ($resultOne['level']) {
		case '1':
			return $resultOne['name'];
			break;
		case '2':
			$resultTwo =  DB::table('ct_district')->where(array('id'=>$resultOne['parent_id']))->find();
			if ($resultOne['name'] == $resultTwo['name']) {
				return $resultOne['name'];
			} else {
				return $resultTwo['name'].$resultOne['name'];
			}
			break;
		case '3':
			$resultTwo =  DB::table('ct_district')->where(array('id'=>$resultOne['parent_id']))->find();
			$resultThree =  DB::table('ct_district')->where(array('id'=>$resultTwo['parent_id']))->find();
			if ($resultOne['name'] == $resultTwo['name']) {
				return $resultThree['name'].$resultTwo['name'];
			} else {
				return $resultThree['name'].$resultTwo['name'].$resultOne['name'];
			}
			break;
		default:
			# code...
			break;
	}
}
/*
 * 根据区查询所在市
 * 返回ID
 * */
function areatocity($id){
    $resultOne = Db::table('ct_district')->where('id',$id)->field('parent_id')->find();
    $result = $resultOne['parent_id'];
    return $result;

}
/**
 * 发送短信
 * @param  [type] $type   [description]
 * @param  [type] $mobile [短信类型] 1 验证码2、提货验证码
 * @param  string $str    [description]
 * @return [type]         [description]
 */
function send_sms($type,$mobile,$str=''){
	header("Content-Type: text/html; charset=UTF-8");
	$flag = 0; 
	$params='';//要post的数据 
	$verify = mt_rand(1000,9999);//获取4位随机验证码	
	if($type == '1'){
		$content ='您的验证码为:'.$verify.',请及时完成验证操作';
	}elseif($type == '2'){
		$content = "您好,".$str." 的验证码为：".$verify.",请提供给司机";
	}	
	//以下信息自己填
	$argv = array( 
		'name'=>'chitushgyl',     //必填参数。用户账号
		'pwd'=>'31EADA99CDDCF8B08C230CF995EA',     //必填参数。（web平台：基本资料中的接口密码）
		'content'=>$content,   //必填参数。发送内容（1-500 个汉字）UTF-8编码
		'mobile'=>$mobile,   //必填参数。手机号码。多个以英文逗号隔开
		'stime'=>'',   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
		'sign'=>'赤途冷链',    //必填参数。用户签名。
		'type'=>'pt',  //必填参数。固定值 pt
		'extno'=>''    //可选参数，扩展码，用户定义扩展码，只能为数字
	); 
	
	//构造要post的字符串 
	foreach ($argv as $key=>$value) { 
		if ($flag!=0) { 
			$params .= "&"; 
			$flag = 1; 
		} 
		$params.= $key."="; $params.= urlencode($value);// urlencode($value); 
		$flag = 1; 
	} 
	
	$url = "http://web.duanxinwang.cc/asmx/smsservice.aspx?".$params; //提交的url地址
	$con= substr( file_get_contents($url), 0, 1 );  //获取信息发送后的状态

	if($con == '0'){
		$result['status'] = '1'; //发送成功
	}else{
		$result['status'] = '2';//发送失败
	}
     $result['verify'] =$verify;
	return $result;
}  

/**
 * 短信发送
 * 群发
 * @param $mobile 手机号码，多个用英文逗号隔开
 * @param $content 发送内容
 */
function send_sms_class($mobile,$content){
	header("Content-Type: text/html; charset=UTF-8");


	$flag = 0; 
	$params='';//要post的数据 
	//以下信息自己填
	$argv = array( 
		'name'=>'chitushgyl',     //必填参数。用户账号
		'pwd'=>'31EADA99CDDCF8B08C230CF995EA',     //必填参数。（web平台：基本资料中的接口密码）
		'content'=>$content,   //必填参数。发送内容（1-500 个汉字）UTF-8编码
		'mobile'=>$mobile,   //必填参数。手机号码。多个以英文逗号隔开
		'stime'=>'',   //可选参数。发送时间，填写时已填写的时间发送，不填时为当前时间发送
		'sign'=>'赤途冷链',    //必填参数。用户签名。
		'type'=>'pt',  //必填参数。固定值 pt
		'extno'=>''    //可选参数，扩展码，用户定义扩展码，只能为数字
	);
    $url = "http://web.duanxinwang.cc/asmx/smsservice.aspx";
	//构造要post的字符串 
	foreach ($argv as $key=>$value) { 
		if ($flag!=0) { 
			$params .= "&"; 
			$flag = 1; 
		} 
		$params.= $key."="; $params.= urlencode($value);// urlencode($value); 
		$flag = 1; 
	}
	  $curl = curl_file_post_contents($url,$argv);
      $con= substr( $curl, 0, 1 );
	if($con == '0'){
		$result = '成功'; //发送成功
	}else{
		$result = '失败';//发送失败
	}
	return $result;
}

    /*
     *   php访问url路径，post请求
     *
     *   durl   路径url
     *   post_data   array()   post参数数据
     */
    function curl_file_post_contents($durl, $post_data){
    // header传送格式
    $headers = array();
    //初始化
    $curl = curl_init();
    //设置抓取的url
    curl_setopt($curl, CURLOPT_URL, $durl);
    //设置头文件的信息作为数据流输出
    curl_setopt($curl, CURLOPT_HEADER, false);
    //设置获取的信息以文件流的形式返回，而不是直接输出。
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //设置post方式提交
    curl_setopt($curl, CURLOPT_POST, true);
    // 设置post请求参数
    curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
    // 添加头信息
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    // CURLINFO_HEADER_OUT选项可以拿到请求头信息
    curl_setopt($curl, CURLINFO_HEADER_OUT, true);
    // 不验证SSL
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    //执行命令
    $data = curl_exec($curl);
    // 打印请求头信息
//        echo curl_getinfo($curl, CURLINFO_HEADER_OUT);
    //关闭URL请求
    curl_close($curl);
    //显示获得的数据
    return $data;
}


/**
 * 用户的验证码短信
 * @param string $verify 验证码
 * @param int $number 类型  1、注册验证码 2、忘记密码验证码 3、修改手机号验证码
 */
function user_verify($verify, $number){

	if($number != '' && $verify != '') {
		switch ($number) {
			case 1:
				$get_str = 'TMP_SIGNIN_SUCCESS_MESS';
				break;
			case 2:
				$get_str = 'TMP_LOGIN_FORGET_CODE_MSG';
				break;
			case 3:
				$get_str = 'TMP_UPDATE_PHONE_CODE_MSG';
				break;
		}
		$data['mess_str'] = $get_str;
		$get = Db::table('ct_message_temp')->where($data)->find();
		$result_str = str_replace('$vity',$verify,$get['message']);
		return $result_str;
	}else{
		return '';
	}
}

/**
 * 用户实名认证短信 
 * @param int $number 类型  1、实名认证成功 2、实名认证失败 
 */
function user_auth($number){
	if ($number !='') {
		switch ($number) {
			case 1:
				$get_str = 'TMP_AUTH_SUCCESS_MESS';
				break;
			case 2:
				$get_str = 'TMP_AUTH_FAIL_MESS';
				break;
		}
		$data['mess_str'] = $get_str;
		$get = Db::table('ct_message_temp')->where($data)->find();
		return $get['message'];
	}else{
		return '';
	}
}

/**
 * 零担干线或室内配送短信
 * number 类型 定义以下规则
 * 1、提交订单未支付 
 * 2、提交订单已承接 
 * 3、提货时($driver,$phone,$cartype',$carnumber,$arrtime) 
 * 4、进行中 
 * 5、到达目的地 
 * 6、配送完成
 * @param  [int]  $number    [类型] 
 * @param  string $driver    [司机名称]
 * @param  string $phone     [司机联系电话]
 * @param  string $cartype   [车型]
 * @param  string $carnumber [车牌号]
 * @param  string $arrtime   [提货时间]
 * @return [type]            [description]
 */
function user_orderstatu($number,$driver='',$phone='',$cartype='',$carnumber='',$arrtime=''){
	if ($number !='') {
		switch ($number) {
			case 1:
				$get_str = 'TMP_LINE_UNPAY_MSG';
				break;
			case 2:
				$get_str = 'TMP_LINE_SUBORDER_MSG';
				break;
			case 3:
				$get_str = 'TMP_LINE_PICKUP_MSG';
				break;
			case 4:
				$get_str = 'TMP_LINE_PROCEED_MSG';
				break;
			case 5:
				$get_str = 'TMP_LINE_SEND_MSG';
				break;
			case 6:
				$get_str = 'TMP_LINE_FINISH_MSG';
				break;
		}
		$data['mess_str'] = $get_str;
		$get = Db::table('ct_message_temp')->where($data)->find();
		if ($driver!='' && $phone!='' && $cartype!='' && $carnumber!='' && $arrtime!='') {
			$result_str = str_replace(array('$driver','$phone','$cartype','$carnumber','$arrtime'),array($driver,$phone,$cartype,$carnumber,$arrtime),$get['message']);
			return $result_str;
		}else{
			return $get['message'];
		}
	}else{
		return '';
	}
}

/**
 * 用户跨区整车短信
 * @param str $driver 司机名称
 * @param str $phone 司机联系电话
 * @param str $cartype 车型
 * @param str $carnumber 车牌号
 * @param str $arrtime  提货时间 2017年6月21日下午 
 * @param int $number 类型  1、提交订单 2、匹配中 3、匹配成功($driver,$phone,$cartype',$carnumber,$arrtime)
 */
function user_carload($number,$driver='',$phone='',$cartype='',$carnumber='',$arrtime=''){
	if ($number !='') {
		switch ($number) {
			case 1:
				$get_str = 'TMP_CARLOAD_SUB_MESS';
				break;
			case 2:
				$get_str = 'TMP_CARLOAD_MATCH_MESS';
				break;
			case 3:
				$get_str = 'TMP_CARLOAD_MATCHYES_MESS';
				break;
		}
		$data['mess_str'] = $get_str;
		$get = Db::table('ct_message_temp')->where($data)->find();
		if ($driver!='' && $phone!='' && $cartype!='' && $carnumber!='' && $arrtime!='') {
			$result_str = str_replace(array('$driver','$phone','$cartype','$carnumber','$arrtime'),array($driver,$phone,$cartype,$carnumber,$arrtime),$get['message']);
			return $result_str;
		}else{
			return $get['message'];
		}
	}else{
		return '';
	}
}

/**
 * 承运端实名认证短信
 * @param int $number 类型  1、实名认证成功 2、实名认证失败 
 */
function driver_auth($number){
	if ($number !='') {
		switch ($number) {
			case 1:
				$get_str = 'DRIVER_UNAUTH_MESS';
				break;
			case 2:
				$get_str = 'DRIVER_AUTH_SUCCESS_MESS';
				break;
		}
		$data['mess_str'] = $get_str;
		$get = Db::table('ct_message_temp')->where($data)->find();
		return $get['message'];
	}else{
		return '';
	}
}

/**
 * 承运端车辆认证短信
 * @param int $number 类型  1、车辆未认证 2、车辆认证成功 
 */
function driver_carauth($number){
	if ($number !='') {
		switch ($number) {
			case 1:
				$get_str = 'DRIVER_CAR_AUTH_MESS';
				break;
			case 2:
				$get_str = 'DRIVER_CAR_AUTH_SUCCESS_MESS';
				break;
		}
		$data['mess_str'] = $get_str;
		$get = Db::table('ct_message_temp')->where($data)->find();
		return $get['message'];
	}else{
		return '';
	}
}

/**
 * 承运端发布车源短信
 * @param  [int]  $number   [类型] 1、提交订单 2、匹配中 3、匹配成功($username,$phone) 
 * @param  string $username [货主名称]
 * @param  string $phone    [货主联系方式]
 * @return [type]           [description]
 */
function driver_carmessage($number,$username='',$phone=''){
	if ($number !='') {
		switch ($number) {
			case 1:
				$get_str = 'DRIVER_PICKUP_MESS';
				break;
			case 2:
				$get_str = 'DRIVER_MATCH_MESS';
				break;
			case 3:
				$get_str = 'DRIVER_MATCH_SUCCESS_MESS';
				break;
		}
		$data['mess_str'] = $get_str;

		$get = Db::table('ct_message_temp')->where($data)->find();
		if ($username!='' && $phone!=''){
			$result_str = str_replace(array('$username','$phone'),array($username,$phone),$get['message']);
			return $result_str;
		}else{
			return $get['message'];
		}
		
	}else{
		return '';
	}
}

/**
 * 阿里身份证实名认证： 
 * https://market.aliyun.com/products/57000002/cmapi016424.html?spm=5176.730005.0.0.3m50lU#sku=yuncode1042400000
 * 已过期，不能使用而且app也不在进行实名认证了
 * @param  [type] $realname [真实姓名]
 * @param  [type] $cardno   [身份证号]
 * @return [type]           [description]
 */
function idcard($realname,$cardno){
    $host = "http://aliyunverifyidcard.haoservice.com";
    $path = "/idcard/VerifyIdcardv2";
    $method = "GET";
    $appcode = "70df1b4e92ba47b88ed04194f98d53d3";
    $headers = array();
    array_push($headers, "Authorization:APPCODE " . $appcode);
    $querys = "cardNo=".$cardno."&realName=".$realname;
    $bodys = "";
    $url = $host . $path . "?" . $querys;
    //curl发起请求
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_FAILONERROR, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($curl, CURLOPT_HEADER, true);    //表示需要response header
    if (1 == strpos("$".$host, "https://"))
    {
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
    }
   
    $result = curl_exec($curl);
    return $result;
}

/**
 * 百度接口根据地理位置获取经纬度
 * @param  [type] $type [区域类型] 1 城市到城市位置 2 区域到区域位置
 * @param  [type] $city [城市名称]
 * @param  [type] $area [区域位置]
 * @return [type]       [经纬度]
 */
function bd_local($type,$city,$area){
	//$ak = '49tGEmabyb7q6NMwks789zvHfZ39dTsh';
	$ak ="SdRptW2rs3xsjHhVhQOy17QzP6Gexbp6";
	if($type == '1'){
       $address = $city."市委";
	}else{
       $address = $area;
	}
	$url ="http://api.map.baidu.com/geocoder/v2/?callback=renderOption&output=json&address=".$address."&city=".$city."&ak=".$ak;
	$renderOption = file_get_contents($url);
	preg_match("/.*\((.*)\)/",$renderOption,$result);
	$res = json_decode($result[1],true);
    if($res['status'] == '0'){
    	$finlly = $res['result']['location'];
    }else{
    	$finlly = '';
    }
    return $finlly;
}

/**
 * 百度接口根据地理位置获取行车距离
 * https://lbsyun.baidu.com/index.php?title=webapi/route-matrix-api-v2
 * 根据批量算路计算驾车距离
 * 城配项目因为计算多个点距离所以使用此方法
 * 整车项目使用direction方法不会造成误差
 * 返回两点之间行车距离最短的线路
 * @param  [type] $lat1 [起点纬度]
 * @param  [type] $lng1 [起点经度]
 * @param  [type] $lat2 [终点纬度]
 * @param  [type] $lng2 [终点经度]
 * @return [type]       [description]
 */
function getDriverline($lat1, $lng1, $lat2, $lng2){
	if(empty($lat1) || empty($lng1) || empty($lat2) || empty($lng2)){
 		return '';
 	} 
	$ak ="SdRptW2rs3xsjHhVhQOy17QzP6Gexbp6";
	$url = "http://api.map.baidu.com/routematrix/v2/driving?output=json&tactics=13&origins=".$lat1.",".$lng1."&destinations=".$lat2.",".$lng2."&ak=".$ak;
	$renderOption =    file_get_contents($url);
	$result = json_decode($renderOption,true);
	if ($result['status'] == '0') {
		$res = $result['result'][0];
	}else{
		$res='';
	}
	return $res;
}

/**
 * 百度接口根据地理位置获取行车距离
 * https://lbsyun.baidu.com/index.php?title=webapi/direction-api-v2
 * 根据路线规划获取驾车距离
 * 整车项目使用改方法不会造成偏差
 * 城配仍然使用getDriverline方法计算距离
 * 返回两点之间行车距离最短的线路
 * @param  [type] $lat1 [起点纬度]
 * @param  [type] $lng1 [起点经度]
 * @param  [type] $lat2 [终点纬度]
 * @param  [type] $lng2 [终点经度]
 * @return [type]       [description]
 */
function direction($lat1, $lng1, $lat2, $lng2){
	if(empty($lat1) || empty($lng1) || empty($lat2) || empty($lng2)){
 		return '';
 	} 
	$ak ="SdRptW2rs3xsjHhVhQOy17QzP6Gexbp6";
	$url = "http://api.map.baidu.com/direction/v2/driving?output=json&tactics=0&origin=".$lat1.",".$lng1."&destination=".$lat2.",".$lng2."&ak=".$ak;

	$renderOption =    file_get_contents($url);
	$result = json_decode($renderOption,true);

	if ($result['status'] == '0') {
		$res['distance'] = $result['result']['routes'][0]['distance'];
		$res['duration'] = $result['result']['routes'][0]['duration'];
	}else{
		$res='';
	}

	return $res;
}

/**
 * 司机端运费区间计算
 * 根据客户下单的金额来计算承运端看单的订单金额
 * 城配、整车改为提货支付、到货支付已经不在使用即承运端显示订单价格
 * @param  [type] $price [客户下单运费金额]
 * @param  [type] $otype [订单类型 1城配 2整车 3提货 4干线 5配送]
 * @return [type]        [description]
 */
function driver_money_rang($price,$otype){
	if ($otype=='') {
		return $price;
	}
	$city_cost = Db::table('ct_city_section')->where('otype',$otype)->select();
	$paymoney =0;
	if (!empty($city_cost)) {
		foreach ($city_cost as $key => $value) {
			if ($price > $value['weight_start'] && $price <= $value['weight_end']) {
				$paymoney = $price*$value['billing']/100;
			}
		}
		return $paymoney;
	}else{
		return $price;
	}	
}

/**
 * 里程区间计算
 * 根据公里数计算该公里数区间段内的系数弥补公里数上的偏差
 * @desction: 城配、整车已不在使用改方法
 * @Auther: 李渊
 * @Date: 2018.7.3
 * @param  [type] $km [公里数]
 * @return [type]     [description]
 */
function driver_km_rang($km){
	if ($km=='') {
		return '';
	}
	$intkm = $km;
	$city_cost = Db::table('ct_city_section')->where('otype','6')->select();
	$finally =0;
	if (!empty($city_cost)) {
		foreach ($city_cost as $key => $value) {
			if ($intkm > $value['weight_start'] && $intkm <= $value['weight_end']) {
				$finally =$intkm*$value['billing'];

			}
		}
		return $finally;
	}else{
		return '';
	}
}

/**
 * 车配、整车订单计算里程区间
 * @Auther： 李渊
 * @Date: 2018.6.15
 * @param  int     $type 项目类型 1 城配 2 整车
 * @param  float   $km   里程数
 * @return [float] $km   乘以里程系数后的里程数  
 */
function mileage_interval($type,$km){
	// 查询里程系数标准
    $result = Db::table('ct_price_setting')->where('type',$type)->find();
    // 默认计算后的里程数
    $finally = $km;
    // 获取0-100里程系数
    $scale_km = $result['scale_km'] == '' ? 1 : $result['scale_km'];
    // 获取100-300里程系数
    $scale_km_two = $result['scale_km_two'] == '' ? 1 : $result['scale_km_two'];
    // 获取300-1000里程系数
    $scale_km_three = $result['scale_km_three'] == '' ? 1 : $result['scale_km_three'];
    // 获取1000以上里程系数
    $scale_km_four = $result['scale_km_four'] == '' ? 1 : $result['scale_km_four'];
    // 判断0-100里程数所在范围并返回相应的里程数
    if($km >=0 && $km<= 100){
    	$finally = $km*$scale_km;
    	return $finally;
    }
    // 判断100-300里程数所在范围并返回相应的里程数
    if($km > 100 && $km<= 300){
    	$finally = $km*$scale_km_two;
    	return $finally;
    }
    // 判断300-1000里程数所在范围并返回相应的里程数
    if($km > 300 && $km<= 1000){
    	$finally = $km*$scale_km_three;
    	return $finally;
    }
    // 判断1000以上里程数所在范围并返回相应的里程数
    if($km > 1000){
    	$finally = $km*$scale_km_four;
    	return $finally;
    }
}

/**
 * 促销活动费用计算
 * @Auther: 李渊
 * @Date: 2018.6.25
 * @param  [type] $type     [项目类型] 1 城配 2 整车
 * @param  [type] $money    [订单金额]
 * @param  [type] $discount [折扣价]
 * @param  [type] $time 	[订单时间]
 * @param  [type] $orderid 	[订单号]
 * @param  [type] $userid 	[下单人id]
 * @return [type]        [description]
 */
function money_promotion($type,$money,$discount,$time,$orderid,$userid){
	// 此刻时间
	$newTime = $time;
	// 获取订单号长度
	$orderLeng = strlen($orderid);
	// 获取订单号最后一位数
	$lastNum = $orderid{$orderLeng-1};
	// 获取订单号最后第二位数
	$lasttwoNum = $orderid{$orderLeng-2};
	// 获取订单号最后第三位数
	$lastthreeNum = $orderid{$orderLeng-3};
	// 默认促销一优惠后的金额
	$tenOrderMoney = $money;
	// 默认促销二优惠后的金额
	$wholeOrderMoney = $money;
	// 默认促销三优惠后的金额
	$twoSameorderMoney = $money;
	// 默认促销四优惠后的金额
	$threeSameorderMoney = $money;
	// 默认促销五优惠后的金额
	$dateOrderMoney = $money;
	// 默认促销六优惠后的金额
	$firstOrderMoney = $money;



	// 查询促销日前十单优惠折扣配置
	$tenOrder = Db::table('ct_promotion')->where(array('type' => $type,'promotionType' => 1))->find();
	if(!empty($tenOrder) && $tenOrder['switch'] == 1 && $tenOrder['startTime'] < $newTime && $tenOrder['endTime'] > $newTime){
		// 查询条件 查询促销日期间下单的个数
		$where['addtime'] = array(array('gt',$tenOrder['startTime']),array('lt', $tenOrder['endTime']));
		// 默认下单为0
		$count = 0; 
		// 判断是城配还是整车
		if($type == 1){ // 城配
			$count = Db::table('ct_city_order')->where($where)->count();
		}else{ // 整车
			$count = Db::table('ct_userorder')->where($where)->count();
		}
		if($count < 10){
			$tenOrderMoney = $money*$tenOrder['scale'];
		}
	}
	// 查询整点第一单优惠折扣配置
	$wholeOrder = Db::table('ct_promotion')->where(array('type' => $type,'promotionType' => 2))->find();
	if(!empty($wholeOrder) && $wholeOrder['switch'] == 1 && $wholeOrder['startTime'] < $newTime && $wholeOrder['endTime'] > $newTime){
		// 上一个整点时间戳
		$nextTime = strtotime(date("Y-m-d H",$newTime).":00:00");
		// 下一个整点时间戳
		$prevTime = $nextTime+3600;
		// 查询条件 查询促销日期间整点下单的个数
		$where['addtime'] = array(array('gt',$nextTime),array('lt', $prevTime));
		// 默认下单为0
		$count = 0;
		// 判断是城配还是整车
		if($type == 1){ // 城配
			$count = Db::table('ct_city_order')->where($where)->count();
		}else{ // 整车
			$count = Db::table('ct_userorder')->where($where)->count();
		}
		if($count < 1){
			$wholeOrderMoney = $money*$wholeOrder['scale'];
		}
	}
	// 查询订单号后两位相同的订单优惠配置
	$twoSameorder = Db::table('ct_promotion')->where(array('type' => $type,'promotionType' => 3))->find();
	if(!empty($twoSameorder) && $twoSameorder['switch'] == 1 && $twoSameorder['startTime'] < $newTime && $twoSameorder['endTime'] > $newTime){
		// 判断订单后两位是否相等
		if($lastNum == $lasttwoNum){
			$twoSameorderMoney = $money*$twoSameorder['scale'];
		}
	}
	// 查询订单号后三位相同的订单优惠配置
	$threeSameorder = Db::table('ct_promotion')->where(array('type' => $type,'promotionType' => 4))->find();
	if(!empty($threeSameorder) && $threeSameorder['switch'] == 1 && $threeSameorder['startTime'] < $newTime && $threeSameorder['endTime'] > $newTime){
		// 判断订单后两位是否相等
		if($lastNum == $lasttwoNum && $lasttwoNum == $lastthreeNum){
			$threeSameorderMoney = $money*$threeSameorder['scale'];
		}
	}
	// 查询订单后两位等于当天日期优惠配置
	$dateOrder = Db::table('ct_promotion')->where(array('type' => $type,'promotionType' => 5))->find();
	if(!empty($dateOrder) && $dateOrder['switch'] == 1 && $dateOrder['startTime'] < $newTime && $dateOrder['endTime'] > $newTime){
		// 当天日期
		$date = date('d',time());
		// 订单后两位
		$str = $lastNum.$lasttwoNum;
		// 判断后两位是否等于当天日期
		if($date == $str){
			$dateOrderMoney = $money*$dateOrder['scale'];
		}
	}
	// 查询促销日第一单折扣配置即从未下过单情况
	$firstOrder = Db::table('ct_promotion')->where(array('type' => $type,'promotionType' => 6))->find();
	if(!empty($firstOrder) && $firstOrder['switch'] == 1 && $firstOrder['startTime'] < $newTime && $firstOrder['endTime'] > $newTime){
		// 筛选条件
		$where['userid'] = $userid;
		// 默认下单为0
		$count = 0; 
		// 判断是城配还是整车
		if($type == 1){ // 城配
			$count = Db::table('ct_city_order')->where($where)->count();
		}else{ // 整车
			$count = Db::table('ct_userorder')->where($where)->count();
		}
		if($count < 1){
			$firstOrderMoney = $money*$firstOrder['scale'];
		}
	}
	// 比较最小值
	$minMoney = min($tenOrderMoney,$wholeOrderMoney,$twoSameorderMoney,$threeSameorderMoney,$dateOrderMoney,$firstOrderMoney,$discount);
	// 输出最小值
	return $minMoney;
}

/**
 * 根据提配日期、件数计算费用
 * @Auther: 李渊
 * @Date: 2018.8.18
 * 根据不同的提配日期计费不同的费用
 * 当天的、第二天、超过两天
 * 如果超过两天冷冻大于50件或者冷藏大于40件才能享受折扣否则不享受
 * @param  [number] $type 	[订单类型 1 城配 2整车]
 * @param  [number] $date 	[用车时间戳]
 * @param  [number] $money  [金额]
 * @return [number]         [计算后的金额]
 */
function   money_datescale($type,$date,$money,$cold_type,$number)
{
	// 查找配置系数
	$scale = Db::table('ct_price_setting')->where('type',$type)->find();
	// 当日配送费用系数比例
	$scale_sameday = $scale['scale_sameday'];
	// 下单第二天配送费用百分比
	$scale_seconday = $scale['scale_seconday'];
	// 超出下单日两天后费用百分比
	$scale_moreday = $scale['scale_moreday'];
	// 当天时间戳
	$sameday = mktime(23, 59, 59, date('m'), date('d'), date('Y'));
	// 第二天时间戳
	$seconday = $sameday+24*60*60;
	// 费用
	$money = $money;
	// 判断用车时间
	if ($date <= $sameday) {
		$money = $money * $scale_sameday;
	} elseif($date > $sameday && $date <= $seconday) {
		$money = $money * $scale_seconday;
	} else {
		$money = $money * $scale_seconday;
		if (!empty($cold_type) || !empty($number)) {
			if ( ($cold_type == '冷冻' && $number <= 50) || ($cold_type == '冷藏' && $number <= 40) ) {
				$money = $money * $scale_moreday;
			}
		}
	}
	// 返回费用
	return $money;
}

function curlSMS($url,$post_fields=array()) {
    $ch=curl_init();
    curl_setopt($ch,CURLOPT_URL,$url);
    curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
    curl_setopt($ch,CURLOPT_TIMEOUT,30);
    curl_setopt($ch,CURLOPT_HEADER,1);
    curl_setopt($ch,CURLOPT_POST,1);
    curl_setopt($ch,CURLOPT_POSTFIELDS,$post_fields);
    $data = curl_exec($ch);
    curl_close($ch);
    $res = explode("\r\n\r\n",$data);
    return $res[2]; 
}

/**
 * 用户APP发布货源暂用短信接口
 * 群发
 * @param $mobile 手机号码，多个用英文逗号隔开
 * @param $content 发送内容
 */
function send_imst($mobile,$content) {
   
    $url = "http://web.duanxinwang.cc/asmx/smsservice.aspx?";
    $data=array
    (
        'name'=>'chitushgyl',     //必填参数。用户账号
        'pwd'=>'31EADA99CDDCF8B08C230CF995EA',     //必填参数。（web平台：基本资料中的接口密码）
        'mobile'=>$mobile,
        'content'=>$content,
        'encode'=>'UTF-8',
        'stime'=>'',   
		'sign'=>'赤途',    //必填参数。用户签名。  
		'type'=>'pt',  
		'extno'=>''

    );
    $result = curlSMS($url,$data);
    //print_r($data); 
    return $result;
}










