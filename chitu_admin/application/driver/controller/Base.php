<?php
/**
 * 司机app接口基类文件
 * author : 依然范儿特西
 */
namespace app\driver\controller;
use think\Controller;
use think\Request; 
use  think\Db;  //使用数据库操作

class Base extends Controller
{
	function __construct(){
		parent::__construct();
		
	}

	// 空操作
	public function _empty(){
		return json(['code'=>'110','message'=>'赤途(上海)供应链管理有限公司']);
	}

    /**
     * 对象 转 数组
     * @param object $obj 对象
     * @return array
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
	//并发执行，备注：使用控制器才可以使用此方法
	public  function _initialize(){
		//echo "并发操作</br>";
	}
    //删除验证码记录
    public function delete_yzm($phone){
       Db::table("ct_validate_record")->where('phone',$phone)->delete();
    }


    //监听sql
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
     */
    public function app_activate(){
        $token = input('token');  //验证令牌
        if (empty($token)) {
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
        $start = mktime(0,0,0,date("m",time()),date("d",time()),date("Y",time()));
        $end = mktime(23,59,59,date("m",time()),date("d",time()),date("Y",time()));
        $where['starttime'] = array(array('gt',$start),array('lt', $end));
        $where['userid'] = $driver_id;
        $result = Db::table('ct_app_activate')->where($where)->find();
        if ($result) {
            Db::table('ct_app_activate')->where(array('id'=>$result['id'],'userid'=>$driver_id))->update(array('data_times'=>$result['data_times']+1));
            return json(['code'=>'1001','message'=>'记录成功']);
        }else{
            $data['starttime'] = time();
            $data['usertype'] = 1;
            $data['data_times'] = 1;
            $data['userid'] = $driver_id;
            Db::table('ct_app_activate')->insert($data);
            return json(['code'=>'1001','message'=>'记录成功']);
        }
    }
   


    /**
     * 单文件上传
     * name：表单上传文件的名字
     * ext： 文件允许的后缀，字符串形式
     * path：文件保存目录
     */
    public function file_upload($name,$ext,$path){
    	$dir_path=ROOT_PATH.'./public/uploads/'.$path;
    	if (!is_dir($dir_path))mkdir($dir_path, 0777);// 使用最大权限0777创建文件
	    $file = request()->file($name);
	    $info = $file->move($dir_path,true,false);
	    if($info){
	        // 成功上传后 获取上传信息
	        //$file_path = $info->getSaveName();
            $file_path =str_replace('\\','/',$info->getSaveName());
	        $data['file_path'] = '/uploads/'.$path.'/'.$file_path;
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
	public function product_token($driver_id){
    	$token_key = time().mt_rand('000000','999999')."codephp";
    	//判断数据是否已存在
        $condition['driver_id'] = $driver_id;
        $res = Db::table("ct_driver_token")->where($condition)->find();
        if($res){
            //已存在更新
            $upda = array(
        		'token'=>$token_key,
        		'last_time'=>time()
            );
            Db::table("ct_driver_token")->where('driver_id',$driver_id)->update($upda);
        }else{
            //不存在新增
            $indata = array(
                'driver_id'=>$driver_id,
                'last_time'=>time(),
                'token'=>$token_key
                );
            Db::table("ct_driver_token")->insert($indata);
        }
    	$token = $this->encode($token_key);
    	return $token;
    }
    //生成code 2019.5.29
    public function  product_code($driver_id){

        $token_key = time().mt_rand('000000','999999')."codephp";
        //判断数据是否已存在
        $condition['driver_id'] = $driver_id;
        $res = Db::table("ct_driver_code")->where($condition)->find();
        if($res){
            //已存在更新
            $endtime  =strtotime(" +1years",$res['duetime']);
            $upda = array(
                'code'=>$token_key,
                'now_time'=>time(),
                'duetime'=>$endtime
            );
            Db::table("ct_driver_code")->where('driver_id',$driver_id)->update($upda);
        }else{
            $endtime =strtotime(" +1years",time());
            //不存在新增
            $indata = array(
                'driver_id'=>$driver_id,
                'now_time'=>time(),
                'code'=>$token_key,
                'addtime'=>time(),
                'duetime'=>$endtime
            );
            Db::table("ct_driver_code")->insert($indata);
        }
        $token = $this->encode($token_key);
        return $token;
    }

    /*
     * 生code时效6个月
     * */
    public function  product_acode($driver_id){

        $token_key = time().mt_rand('000000','999999')."codephp";
        //判断数据是否已存在
        $condition['driver_id'] = $driver_id;
        $res = Db::table("ct_driver_code")->where($condition)->find();
        if($res){
            //已存在更新
            $endtime  =strtotime(" +6 month",$res['duetime']);
            $upda = array(
                'code'=>$token_key,
                'now_time'=>time(),
                'duetime'=>$endtime
            );
            Db::table("ct_driver_code")->where('driver_id',$driver_id)->update($upda);
        }else{
            $endtime =strtotime(" +6 month",time());
            //不存在新增
            $indata = array(
                'driver_id'=>$driver_id,
                'now_time'=>time(),
                'code'=>$token_key,
                'addtime'=>time(),
                'duetime'=>$endtime
            );
            Db::table("ct_driver_code")->insert($indata);
        }
        $token = $this->encode($token_key);
        return $token;
    }
    // 验证code  时效一年 2019.5.28
    public function check_code($code){
        // 解密字符串
        $token_decode = $this->decode($code);

        // 查询
        $where['code'] = $token_decode;
        // 查询token
        $res = Db::table("ct_driver_code")->where('code',$token_decode)->find();
        // 验证结果是否存在
        if(empty($res)){
            $data['status'] = '1';  //非法请求
        }else{
            // 验证是否超时：目前设置token有效时间为1年
//            $oldtime = date('Y-m-d H:i:s',$res['now_time']);
//            $check_time = strtotime(date("Y-m-d H:i:s",strtotime("$oldtime   +1  year")));
            $check_time = $res['duetime'];
            // 验证是否超时
            if($check_time  <  time()){
                $data['status'] = '2'; //token已过期
            }else{
                $data['status'] = '3'; //通过
                $data['driver_id'] = $res['driver_id'];
                // 验证用户是否存在
                $isdriver = DB::table('ct_driver')->where(array('drivid' => $res['driver_id'], 'delstate'=>1 ))->find();
                // 判断用户是否存在
                if(empty($isdriver)){ // 如果没有定义非法请求
                    $data['status'] = '1';  // 非法请求
                }
            }

        }
        return $data;
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
        $res = Db::table("ct_driver_token")->where('token',$token_decode)->find();
        // 验证结果是否存在
        if(empty($res)){
        	$data['status'] = '1';  //非法请求
        }else{
        	// 验证是否超时：目前设置token有效时间为1年
	        $oldtime = date('Y-m-d H:i:s',$res['last_time']);
	        $check_time = strtotime(date("Y-m-d H:i:s",strtotime("$oldtime   +1  year")));
            // 验证是否超时
	        if($check_time  <  time()){
	        	$data['status'] = '2'; //token已过期	
	        }else{
	        	$data['status'] = '3'; //通过
	        	$data['driver_id'] = $res['driver_id'];
                // 验证用户是否存在 
                $isdriver = DB::table('ct_driver')->where(array('drivid' => $res['driver_id'], 'delstate'=>1 ))->find();
                // 判断用户是否存在
                if(empty($isdriver)){ // 如果没有定义非法请求 
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
        $skey="ct8888php";
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
        $skey="ct8888php";

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

    function arr2str ($arr){
        foreach ($arr as $v){
            $v = join(",",$v); //可以用implode将一维数组转换为用逗号连接的字符串
            $temp[] = $v;
        }
        $t="";
        foreach($temp as $v){
            $t.=$v.",";
        }
        $t=substr($t,0,-1);
        return $t;
    }
   
    /**
     * 查找详细地址信息 上海-嘉定区-江桥镇
     * @param  [type] $proid  [省ID]
     * @param  [type] $cityid [市ID]
     * @param  [type] $areaid [区ID]
     * @return [type]         [description]
     */
    public function detailadd($proid,$cityid,$areaid){
        // 省
        $result1 =  DB::table('ct_district')->where(array('id'=>$proid))->find();
        // 市
        $result2 =  DB::table('ct_district')->where(array('id'=>$cityid))->find();
        // 区
        $result3 =  DB::table('ct_district')->where(array('id'=>$areaid))->find();
        // 地址
        return $result1['name'] . $result2['name'] .  $result3['name'];
    }

    /*
    *返回物品信息
    *$oid 订单ID
    
    *type 类型，1提货地址2配送地址
    *author:chenwei
    */
    public function senditem($oid,$type){
        //查询物品
        $goods_list = Db::table("ct_inrentory")
                ->where('orderid',$oid)
                ->select(); 
        $arr_pick = array();
       
        $arr_pei = array();
        $arr = array();
        $arr_list = array();
        foreach ($goods_list as $key => $value) {
            $arr_pick[$value['taddressid']][]=$value;
            $arr_pei[$value['paddress']][] = $value;
        }
        if ($type =='1') {
             foreach ($arr_pick as $key => $value) {
                //发货地址
                $fa = Db::table("ct_addressuser")
                    ->field('pro_id,city_id,area_id,address')
                    ->where('address_id',$key)
                    ->find();
                $arr[$key]['fa_address'] = detailadd($fa['pro_id'],$fa['city_id'],$fa['area_id']).$fa['address'];
                foreach ($value as $key2 => $value2) {
                    $arr[$key]['good'][] = $value2;
                }
            }
            foreach ($arr as $k => $v) {
                $arr_list[] = $v;
            }
            
            
        }elseif($type =='2'){
             foreach ($arr_pei as $k => $v) {
                //配送地址
                $send = Db::table("ct_addressuser")
                    ->field('pro_id,city_id,area_id,address')
                    ->where('address_id',$k)
                    ->find();   
                $arr[$k]['send_address'] = detailadd($send['pro_id'],$send['city_id'],$send['area_id']).$send['address'];
                foreach ($v as $key => $value) {
                     $arr[$k]['good'][] = $value;
                }
            }
            foreach ($arr as $k => $v) {
                $arr_list[] = $v;
            }
        
        }
       
       return $arr_list;

    }
     /*
    *发车队列窗口
    */
    public function getTimeFromWeek($dayNum){
        $curDayNum=date("w");
       
        if($dayNum>$curDayNum) $timeFlag="next ";
        elseif($dayNum==$curDayNum) $timeFlag="";
        else $timeFlag="next ";
        $arryWeekDay = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
        $timeStamp=strtotime("$timeFlag"."$arryWeekDay[$dayNum]");
        return $timeStamp;
    }

    /**
     * 早上八点自动生成上海市内栾陈的车源信息
     * @date: 2018.10.29
     * @auther： 李渊
     * @description：每天早上8点自动生成车源信息
     * @notes: 暂时不需要自动生成先关闭该功能，车源信息增加了费用功能，下次开启时请生成费用
     * @param [string] $value [description]
     * @return [array] [<description>]   
     */
    public function setCarinfo()
    {
        // $driver = array(); 
        // $driver[0]['name'] = '栾师傅';
        // $driver[0]['id'] = 23;
        // $driver[0]['phone'] = '15800901483';
        // $driver[1]['name'] = '陈师傅';
        // $driver[1]['id'] = 165;
        // $driver[1]['phone'] = '13248361505';
        // // 生成上海市内栾陈的车源
        // $dataArr = array();
        // // 循环添加数据
        // for ($key=0; $key < 3; $key++) { 
        //     // 定义订单编号
        //     $dataArr[$key]['ordernumber'] = 'P'.date('ymdhis').mt_rand('000','999'); //订单编号
        //     // 始发城市 上海市
        //     $dataArr[$key]['start_city'] = 45054;
        //     // 终点城市 上海市、苏州市、嘉兴市
        //     switch ($key) {
        //         case 0: // 上海市
        //             $end_city = 45054;
        //             break;
        //         case 1: // 苏州市
        //             $end_city = 166;
        //             break;
        //         case 2: // 嘉兴市
        //             $end_city = 178;
        //             break;
        //         default:
        //             # code...
        //             break;
        //     }
        //     $dataArr[$key]['end_city'] = $end_city;
        //     // 车型id 4.2米
        //     $dataArr[$key]['carid'] = 1;
        //     // 重量
        //     $dataArr[$key]['weight'] = 1500;
        //     // 体积
        //     $dataArr[$key]['volume'] = 12;
        //     // 发车日期
        //     $dataArr[$key]['loaddate'] = date("Y-m-d").' '.'12:00';
        //     // 定义此信息为车源信息
        //     $dataArr[$key]['ordertype'] = 2;
        //     // 定义支付状态为已支付
        //     $dataArr[$key]['paystate'] = 2;
        //     // 发布人id
        //     $driverIndex = array_rand($driver,1);
        //     // 默认发布人
        //     $dataArr[$key]['userid'] = $driver[$driverIndex]['id']; 
        //     // 发布日期
        //     $dataArr[$key]['addtime'] = mktime(7, 0, 0, date('m'),date('d'),date('y'))+mt_rand('0','3600');
        //     // 默认联系人
        //     $dataArr[$key]['issue_realname']  = $driver[$driverIndex]['name']; 
        //     // 默认车源联系人电话
        //     $dataArr[$key]['issue_phone']  = $driver[$driverIndex]['phone'];
        // }

        // // 返回数组
        // return json_encode($dataArr);
    }

    /**
     * 下去一点自动生成上海市内栾陈的车源信息
     * @date: 2018.10.29
     * @auther： 李渊
     * @description：每天早上8点自动生成车源信息
     * @notes: 暂时不需要自动生成先关闭该功能，车源信息增加了费用功能，下次开启时请生成费用
     * @param [string] $value [description]
     * @return [array] [<description>]   
     */
    public function setCarinfopm()
    {
        // $driver = array(); 
        // $driver[0]['name'] = '栾师傅';
        // $driver[0]['id'] = 23;
        // $driver[0]['phone'] = '15800901483';
        // $driver[1]['name'] = '陈师傅';
        // $driver[1]['id'] = 165;
        // $driver[1]['phone'] = '13248361505';
        // // 生成上海市内栾陈的车源
        // $dataArr = array();
        // // 循环添加数据
        // for ($key=0; $key < 3; $key++) { 
        //     // 定义订单编号
        //     $dataArr[$key]['ordernumber'] = 'P'.date('ymdhis').mt_rand('000','999'); //订单编号
        //     // 始发城市 上海市
        //     $dataArr[$key]['start_city'] = 45054;
        //     // 终点城市 上海市、苏州市、嘉兴市
        //     switch ($key) {
        //         case 0: // 上海市
        //             $end_city = 45054;
        //             break;
        //         case 1: // 苏州市
        //             $end_city = 166;
        //             break;
        //         case 2: // 嘉兴市
        //             $end_city = 178;
        //             break;
        //         default:
        //             # code...
        //             break;
        //     }
        //     $dataArr[$key]['end_city'] = $end_city;
        //     // 车型id 4.2米
        //     $dataArr[$key]['carid'] = 1;
        //     // 重量
        //     $dataArr[$key]['weight'] = 1500;
        //     // 体积
        //     $dataArr[$key]['volume'] = 12;
        //     // 发车日期
        //     $dataArr[$key]['loaddate'] = date("Y-m-d").' '.'18:00';
        //     // 定义此信息为车源信息
        //     $dataArr[$key]['ordertype'] = 2;
        //     // 定义支付状态为已支付
        //     $dataArr[$key]['paystate'] = 2;
        //     // 发布人id
        //     $driverIndex = array_rand($driver,1);
        //     // 默认发布人
        //     $dataArr[$key]['userid'] = $driver[$driverIndex]['id']; 
        //     // 默认联系人
        //     $dataArr[$key]['issue_realname']  = $driver[$driverIndex]['name']; 
        //     // 默认车源联系人电话
        //     $dataArr[$key]['issue_phone']  = $driver[$driverIndex]['phone'];
        //     // 发布日期
        //     $dataArr[$key]['addtime'] = mktime(13, 0, 0, date('m'),date('d'),date('y'))+mt_rand('0','3600');
        // }
        // // 追加数据
        // DB::table('ct_issue_item')->insertAll($dataArr);
    }

    /**
     * 定时任务
     * @auther: 李渊
     * @date: 2018.11.23
     * @description：每天早上8点自动生成车源信息
     * @notes: 暂时不需要自动生成先关闭该功能，车源信息增加了费用功能，下次开启时请生成费用
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function timeOutCarinfo()
    {
        // // 查询整车固定的优惠线路
        // $result = Db::table('ct_activitycity')->where('state', 1)->select();
        // // 设置添加多条数据的数组
        // $dataArr1 = array();
        // // 遍历循环添加数据
        // foreach ($result as $key => $value) {
        //     // 查询固定车源的承运人
        //     $driverinfo = Db::table('ct_driver')->where('drivid', $value['appoint_driver'])->find(); 
        //     // 定义订单编号
        //     $dataArr1[$key]['ordernumber'] = 'P'.date('ymdhis').mt_rand('000','999'); //订单编号
        //     // 始发城市
        //     $dataArr1[$key]['start_city'] = $value['startCity'];
        //     // 终点城市
        //     $dataArr1[$key]['end_city'] = $value['endCity'];
        //     // 车型id
        //     $dataArr1[$key]['carid'] = 6;
        //     // 载重
        //     $dataArr1[$key]['weight'] = 20000;
        //     // 体积
        //     $dataArr1[$key]['volume'] = 60;
        //     // 发车日期
        //     $dataArr1[$key]['loaddate'] = date("Y-m-d").' '.'20:00';
        //     // 定义此信息为车源信息
        //     $dataArr1[$key]['ordertype'] = 2;
        //     // 定义支付状态为未支付
        //     $dataArr1[$key]['paystate'] = 2;
        //     // 发布人id
        //     $dataArr1[$key]['userid'] = $value['appoint_driver']; 
        //     // 发布日期
        //     $dataArr1[$key]['addtime'] = mktime(7, 0, 0, date('m'),date('d'),date('y'))+mt_rand('0','3600');
        //     // 默认车源联系人姓名
        //     $realname = $driverinfo['realname'];
        //     if ($realname) {
        //         $realname =  mb_substr($realname,0,1,'utf-8');
        //         $realname = $realname.'师傅';
        //     }
        //     $dataArr1[$key]['issue_realname']  = $realname ? $realname : $driverinfo['username']; 
        //     // 默认车源联系人电话
        //     $dataArr1[$key]['issue_phone']  = $driverinfo['mobile'];
        // }
        // // 设置添加多条数据的数组
        // $dataArr2 = $dataArr1;
        // // 遍历循环数据2
        // foreach ($dataArr2 as $key2 => $value2) {
        //     // 重新定义订单编号
        //     $dataArr2[$key2]['ordernumber'] = 'P'.date('ymdhis').mt_rand('000','999');
        //     // 重新生成时间
        //     $dataArr2[$key2]['addtime'] = mktime(7, 0, 0, date('m'),date('d'),date('y'))+mt_rand('0','3600');
        //     // 重新定义车型id
        //     $dataArr2[$key2]['carid'] = 7;
        //     // 载重
        //     $dataArr1[$key]['weight'] = 30000;
        //     // 体积
        //     $dataArr1[$key]['volume'] = 68;
        // }

        // // 生成栾陈的市内车源
        // $dataArr3 = $this->setCarinfo();
        // $dataArr3 = json_decode($dataArr3,true);
        // // 合并数组
        // $dataAll = array_merge($dataArr1,$dataArr2,$dataArr3);

        // // 追加数据
        // DB::table('ct_issue_item')->insertAll($dataAll);
    }
}

?>
