<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Carsource  extends Base{

	/**
	 * 首页-用户查看车源列表
	 * @auther 李渊
	 * @date 2018.6.14
	 * @param [string] token 用户令牌
	 * @param [int] startcityid 起点城市id
	 * @param [int] endcityid 终点城市id
	 * @param [int] carid 车型id
	 * @return [type] [description]
	 */
	public function driver_pretend_list(){
		$token 		= input("token"); 		// 用户令牌
		$startid 	= input('startcityid'); // 起点城市id
		$endcityid 	= input('endcityid'); 	// 终点城市id
		$carid 		= input('carid');		// 车型id
		// 筛选起点城市
		if ($startid !='') {
			$condition['o.start_city'] = $startid;
		}
		// 筛选终点城市
		if ($endcityid !='') {
			$condition['o.end_city'] = $endcityid;
		}
		// 筛选车型
		if ($carid !='') {
			$condition['o.carid'] = $carid;
		}
		// 司机发布 车源
		$condition['o.ordertype'] = 2;
		// 查询条件 已支付的货源订单
		$condition['o.paystate'] = 2;
		// 查询条件 已支付的货源订单
		$condition['o.orderstate'] = 1;
		// 查询数据
		$result = Db::table("ct_issue_item")
				->alias('o')
				->join('ct_cartype car','car.car_id = o.carid','LEFT')
				->field('o.id,o.userid,o.start_city,o.end_city,o.weight,o.volume,o.loaddate,o.issue_realname,o.issue_phone,o.addtime,car.carparame,car.allweight,car.allvolume')
				->order('addtime desc')
				->where($condition)
				->paginate(10);
		// 转数组 
		$list_mes = $result->toArray();
		// 获取数据数组
		$list = $list_mes['data'];
		// 获取当前时间戳
		$time = time();
		// 遍历数据
		foreach ($list as $key => $value) {
			$driverInfo = Db::table('ct_driver')->where('drivid',$value['userid'])->find();
			// 发布数量
			$res1 = Db::table('ct_issue_item')->where(array('userid' => $value['userid'], 'ordertype' => 2, 'paystate' => 2))->count();
			// 取消数量
			$res2 = Db::table('ct_issue_item')->where(array('userid' => $value['userid'], 'ordertype' => 2, 'orderstate' => 3))->count();
			// 发布数量
			// $list[$key]['issued'] = $res1;
			$list[$key]['issued'] = '***';
			// 取消数量
			$list[$key]['cancel'] = $res2;
			// 返回起点城市
			$list[$key]['start_city'] = addresidToName($value['start_city']);  
			// 返回终点城市
			$list[$key]['end_city'] = addresidToName($value['end_city']); 
			// 判断装车时间是今天还是明天
			$newTime = mktime(23, 59, 59, date('m'),date('d'),date('y'));

			if ( $newTime > strtotime($value['loaddate'])) {
				$list[$key]['loaddate'] = 1;
			} else {
				$list[$key]['loaddate'] = 2;
			}
			// 用户图像
			$list[$key]['image'] = $driverInfo['image'];
			if ($driverInfo['image'] !='') {
				$list[$key]['image'] = get_url().$driverInfo['image'];
			}
			// 重量
			$list[$key]['weight'] = $value['weight'] ? $value['weight']/1000 : $value['allweight'];
			// 体积
			$list[$key]['volume'] = $value['volume'] ? $value['volume'] : $value['allvolume'];
		}
		// 判断是否有数据
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}	
	}

	/**
	 * 首页-用户查看车源详情
	 * @auther: 李渊
	 * @date: 2018.9.20
	 * @param [string] token   用户令牌
	 * @param [number] orderid 订单id
	 */
	public function driver_pretend_detail(){
		$token 		= input("token");  	// 令牌
		$orderid	= input("orderid"); // 订单ID
		// 验证参数
		if(empty($token) || empty($orderid)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 验证令牌
		$check_result = $this->check_token($token);
		// 判断登陆状态
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
				->join('__CARTYPE__ c','o.carid = c.car_id')
				->field('o.id,o.userid,o.ordernumber,o.start_city,o.end_city,o.weight,o.volume,o.loaddate,o.issue_realname,o.issue_phone,
					o.remark,o.driverid,o.addtime,c.carparame,c.allvolume,c.allweight')
				->where('o.id',$orderid)
				->find();
		// 获取查看过此信息的人的id
		$get_driver = json_decode($detail['driverid'],TRUE);
		// 默认用户未支付即没有查看过该条信息
		$user_state = '1';
		// 判断该用户是否支付过此条信息
		if (!empty($get_driver) && in_array($user_id, $get_driver) ) {
			// 支付过状态改变
			$user_state = '2';
		}
		// 获取发布人的信息
		$driver = DB::table('ct_driver')->where('drivid',$detail['userid'])->find();
		// 获取发布人的图像
		if ($driver['image']!='') {
            $detail['image'] = get_url().$driver['image'];
        }else{
            $detail['image'] = get_url().'/static/user_header.png';
        }
        // 返回起点城市
		$detail['start_city'] = addresidToName($detail['start_city']);
		// 返回终点城市
		$detail['end_city'] = addresidToName($detail['end_city']);
		// 返回载重
		$detail['weight'] = $detail['weight'] ? $detail['weight']/1000 : $detail['allweight'];
		// 返回体积
		$detail['volume'] = $detail['volume'] ? $detail['volume'] : $detail['allvolume'];
		// 返回该条信息支付状态
		$detail['user_state'] = $user_state;  //用户支付状态 1 未支付 2已支付	
		// 返回数据
		if(empty($detail)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
		}	
	}

	/**
	 * 首页-车源信息-获取支付状态
	 * @Auther: 李渊
	 * @Date: 2018.10.24
	 * @param string token   [用户令牌]
	 * @param int 	 orderid [订单id]
	 * @return [type]        [description]
	 */
	public function carPayState()
	{
		$token   	= input("token"); 	 // 令牌
		$orderid   	= input("orderid");  // 订单ID
		// 验证参数
		if(empty($token) || empty($orderid)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 验证token
		$check_result = $this->check_token($token);
		// 验证登陆状态
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		// 查询订单
		$order = Db::table("ct_issue_item")->field('driverid')->where(array('id'=>$orderid,'ordertype'=>2))->find();
		// 获取查看人id
		$get_driver = json_decode($order['driverid'],TRUE);
		// 默认支付状态
		$data['isPay'] = false;
		// 判断是否支付过
		if (!empty($get_driver)) {
			// 是否支付过
			$data['isPay'] = in_array($user_id, $get_driver);
		}
		// 返回数据
		return json(['code'=>'1001','message'=>'操作成功','data'=>$data]);
	}

	/**
	 * 首页-用户拨打电话支付车源信息费用
	 * 判断是否支付过支付过则不支付
	 * @auther: 李渊
	 * @date: 2018.10.24
	 * @param [type] $[name] [<description>]
	 * @return [type] [<description>]
	 */
	public function driver_pretend_ask(){
		$token 		= input("token");    // 令牌
		$orderid	= input("orderid");  // 订单ID
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

		$arr = array();
		// 查询该条信息所有支付查看过的用户id
		$order = Db::table("ct_issue_item")->field('driverid')->where(array('id'=>$orderid,'ordertype'=>2))->find();
		// 转数组
		$get_driver = json_decode($order['driverid'],TRUE);
		// 
		if (!empty($get_driver)) {
			if (!in_array($user_id, $get_driver)){  //查找该司机是否支付过
				$arr2[] = $user_id;
				$arr = array_merge($arr2,$get_driver);
			}else{
				$arr = $get_driver;
			}
		}else{
			$arr[] = $user_id;
		}
		// 数组转换
		$data['driverid'] = json_encode($arr);
		// 更新查看人
		$result = Db::table("ct_issue_item")->where('id',$orderid)->update($data);
		// 返回数据
		if($result){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}
}
