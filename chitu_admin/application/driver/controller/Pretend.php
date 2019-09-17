<?php
namespace app\driver\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Pretend  extends Base{

	/**
     * 根据车源的起点城市、终点城市、价格计算定金
     * @auther: 李渊
     * @date: 2018.1.3
     * @param [Int] 	[$start_id] [起点城市id]
     * @param [Int] 	[$end_id]   [终点城市id]
     * @param [String]  [$price] 	[价格]
     * @return [Object] [$down_price]	[定金]
     */
    public function getLowprice($start_id,$end_id,$price)
    {
    	// 定义原价
    	$old_price = $price;
    	// 定义显示价
    	$show_price = $price;
    	// 定义定金
    	$down_price = 50;
    	// 判断是否是同城配送
    	if ($start_id == $end_id) { // 同城配送
    		// 查询最低价
    		$setting = DB::table('ct_config')->where('id',18)->find();
    		// 查询最低价
    		$lowprice = $setting['auth_price'];
    		// 判断发布价是否大于参考价
    		if ($price >= $lowprice) {
    			// 查询比例
    			$setting = DB::table('ct_config')->where('id',20)->find();
    			// 计算价格
    			$price = $price * (1 + $setting['auth_price']);
    		} else {
    			// 查询比例
    			$setting = DB::table('ct_config')->where('id',19)->find();
    			// 计算价格
    			$price = $price + $setting['auth_price'];
    		}
    	} else { // 城际配送
    		// 查询最低价
    		$setting = DB::table('ct_config')->where('id',21)->find();
    		// 查询最低价
    		$lowprice = $setting['auth_price'];
    		// 判断发布价是否大于参考价
    		if ($price >= $lowprice) {
    			// 查询比例
    			$setting = DB::table('ct_config')->where('id',22)->find();
    			// 计算价格
    			$price = $price + $setting['auth_price'];
    		} else {
    			// 查询比例
    			$setting = DB::table('ct_config')->where('id',23)->find();
    			// 计算价格
    			$price = $price * (1 + $setting['auth_price']);
    		}
    	}
    	// 显示价取十位数为整数
        $show_price = ceil($price/10)*10;
        // 定金
        $down_price = $show_price - $old_price;
        // 返回数据
        return $down_price;
    }

	/**
	 * 承运端-发布车源
	 * @auther: 李渊
	 * @date: 2018.9.26
	 * @param  [String] [token] 	[用户令牌]
	 * @param  [Int]    [startcity] [起点城市id]
	 * @param  [Int] 	[endcity] 	[终点城市id]
	 * @param  [Int] 	[carid]	 	[车型id]
	 * @param  [double] [weight] 	[载重]
	 * @param  [double] [volume] 	[<体积>]
	 * @param  [varchar] [name] 	[<发车时间>]
	 * @return [Int] [订单id,支付订单使用]
	 */
	public function pretend_add()
	{
	  	$token 		= input("token");  		// 令牌
	  	$startcity 	= input("startcity");  	// 起点城市ID
	    $endcity 	= input("endcity");  	// 起点城市ID
	    $startarea 	= input("startarea");  	// 起点区ID
	    $endarea 	= input("endarea");  	// 起点区ID
	    $carid 		= input("carid");  		// 车型ID
	    $weight		= input("weight"); 		// 载重 (吨)
		$volume		= input("volume");		// 体积 (立方)
		$loaddate	= input("loaddate"); 	// 发车时间 (字符串)
		$price		= input("price"); 		// 出价 (float)
	    // 判断值是否完整
	    if(empty($token) || empty($startcity) || empty($endcity) || empty($carid) || empty($weight) || empty($volume) || empty($loaddate) || empty($price)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 验证令牌
		$check_result = $this->check_token($token);
		// 检测登陆状态
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}

		// 查询发布人信息
		$driverInfo = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		// 如果有真实姓名
		$realname = $driverInfo['realname'];
		if ($realname) {
	    	$realname =  mb_substr($realname,0,1,'utf-8');
	    	$realname = $realname.'师傅';
	    }
		// 默认车源联系人姓名
		$data['issue_realname']  = $realname ? $realname : $driverInfo['username']; 
		// 默认车源联系人电话
	    $data['issue_phone']  = $driverInfo['mobile'];
		// 始发城市
		$data['start_city'] = $startcity;
		// 终点城市
		$data['end_city'] = $endcity;
		// 始发区域
		$data['start_area'] = $startarea;
		// 终点区域
		$data['end_area'] = $endarea;
		// 车型id
		$data['carid'] = $carid;
		// 发车日期
		$data['loaddate'] = $loaddate;
		// 载重 吨 转化为公斤
		$data['weight'] = $weight*1000;
		// 立方
		$data['volume'] = $volume;
		// 出价
		$data['referprice'] = $price;
		// 定金
		$data['down_price'] = $this->getLowprice($startcity,$endcity,$price);
		// 定义此信息为车源信息
		$data['ordertype'] = 2; 
		// 定义支付状态为未支付
		$data['paystate'] = 1; 
		// 定义订单编号
	    $data['ordernumber'] = 'P'.date('ymdhis').mt_rand('000','999');
	    // 订单备注
		$data['remark'] = input("remark"); 
		// 发布人id
		$data['userid'] = $driver_id; 
		// 发布日期
		$data['addtime'] = time();
		// 插入数据
		$insID = Db::table("ct_issue_item")->insertGetId($data);
		// 判断是否插入成功
		if($insID){
			// 返回订单信息id供支付用
			$re_data['insert_id'] = $insID; 
			// 返回状态和数据
			return json(['code'=>'1001','message'=>'提交成功','data'=>$re_data]);
		}else{
			return json(['code'=>'1002','message'=>'提交失败']);
		}
	}
    /*
     * 修改车源
     * */
    public function edit(){
        $token 		= input("token");  	//令牌
        $orderid 	= input("orderid");	//订单ID
        // 判断值是否完整
        if(empty($token) || empty($orderid) ){
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

        $data['start_city'] = input('startcity');
        $data['end_city'] = input('endcity');
        $data['carid']  = input('carid');
        $data['loaddate'] = input('loaddate');
        $data['weight']  = input('weight')*1000;
        $data['volume'] = input('volume');
        $data['referprice'] = input('price');
        $data['remark'] = input('remark');
        $res = Db::table('ct_issue_item')->where('id',$orderid)->update($data);
        if ($res){
            return json(['code'=>'1001','message'=>'查询成功','data'=>$data]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }
	/**
     *
	 * 发布车源信息 :确认发布车源信息
	 * @auther: 李渊
	 * @date: 2018.9.26
	 * @return [type] [description]
	 */
	public function affirm_pretend()
	{
		$token 		= input("token");  	//令牌
		$orderid 	= input("orderid");	//订单ID
		// 判断值是否完整
		if(empty($token) || empty($orderid) ){
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
		// 更改支付状态
		$data['paystate'] = 2;
		// 更新支付状态
		$result = Db::table('ct_issue_item')->where('id',$orderid)->update($data);
		if ($result) {
			return json(['code'=>'1001','message'=>'订单确认成功']);
		}else{
			return json(['code'=>'1002','message'=>'订单确认失败']);
		}
	}

	/**
	 * 已发布车源信息
	 * @auther: 李渊
	 * @date: 2018.10.25
	 * @param  [String]  [token] [<用户令牌>]
	 * @return [type] [description]
	 */
	public function my_pretend()
	{
		$token   = input("token");  //令牌
		// 验证参数
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
			$driver_id = $check_result['driver_id'];
		}
		// 筛选车源
		$condition['ordertype'] = 2;
		// 筛选已经支付过的
		$condition['paystate'] = 2;
		// 筛选登陆用户的
		$condition['userid'] = $driver_id;
		// 查询数据
		$result = Db::table("ct_issue_item")
				->alias('o')
				->join('__CARTYPE__ c','o.carid = c.car_id')
				->field('o.id,o.paystate,o.loaddate,o.start_city,o.start_area,o.end_area,
					o.end_city,c.carparame,o.addtime,o.orderstate')
				->order('addtime desc')
				->where($condition)
				->paginate(10);
		// 转数组
		$list_mes = $result->toArray();
		// 获取数据
		$list = $list_mes['data'];
		// 遍历数据
		foreach ($list as $key => $value) {
			// 返回起点城市
			$list[$key]['start_city'] =  detailadd('',$value['start_city'],$value['start_area']);
			// 返回终点城市
			$list[$key]['end_city'] = detailadd('',$value['end_city'],$value['end_area']); 
		}
		// 判断数据是否为空
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}	
	}

	/**
	 * 发布车源： 已经发布车源详情
	 * @auther: 李渊
	 * @date: 2018.10.25
	 * @param  [String]  [token]   [<用户令牌>]
	 * @param  [Int]  	 [orderid] [<订单id>]
	 * @return [type] [description]
	 */
	public function my_pretend_detail()
	{
		$token     = input("token");    // 令牌
		$orderid   = input("orderid");  // 订单ID
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
			$driver_id = $check_result['driver_id'];
		}
		// 查询数据
		$detail = Db::table("ct_issue_item")
				->alias('o')
				->join('__CARTYPE__ c','o.carid = c.car_id')
				->field('o.ordernumber,o.referprice,o.orderstate,o.addtime,o.loaddate,o.weight,o.volume,
					o.remark,o.start_city,o.start_area,o.end_city,o.end_area,o.issue_realname,o.issue_phone,c.car_id,c.carparame,c.allweight,c.allvolume')
				->where('o.id',$orderid)
				->find();
        $detail['startcity'] = $detail['start_city'];
        $detail['endcity'] = $detail['end_city'];
		// 返回起点城市
		$detail['start_city'] = detailadd('',$detail['start_city'],$detail['start_area']);
		// 返回终点城市
		$detail['end_city'] = detailadd('',$detail['end_city'],$detail['end_area']);	
		// 返回承载重量
		$detail['weight'] = $detail['weight'] ? $detail['weight']/1000 : $detail['allweight'];
		// 返回承载立方
		$detail['volume'] = $detail['volume'] ? $detail['volume'] : $detail['allvolume'];

		// 判断数据是否为空
		if(empty($detail)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
		}	
	}

	/**
	 * 发布车源 : 取消发布车源信息
	 * @auther: 李渊
	 * @date: 2018.9.26
	 * @param  [type] [name] [<description>]
	 * @return [type] [description]
	 */
	public function cancel_my_pretend()
	{
		$token = input("token");  //令牌
		$orderid = input("orderid");//订单ID
		$act_type = input("act_type");//操作类型  2已完成 3 手动取消
		if(empty($token) || empty($orderid) ){
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
		//手动取消
		$data['orderstate'] = $act_type;
		$result = Db::table('ct_issue_item')->where('id',$orderid)->update($data);
		if ($result) {
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}
}