<?php
namespace app\driver\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型
//订单承接
class Undertake extends Base{

	//零担:提货单抢单列表
	public  function bulk(){
		$token   = input("token");  //令牌
	   /* if(empty($token)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}*/ //2017-7-25

		/*
		*  城市搜索 
 		*/
 		$condition=array();
		$start_city = input('start_city'); //起点城市ID
		if(isset($start_city)  && !empty($start_city)){
			$condition['c.start_id'] = $start_city;
		}
		$end_city = input('end_city'); //终点城市ID
		if(isset($end_city)  && !empty($end_city)){
			$condition['c.end_id'] = $end_city;
		}

		$line = Db::table("ct_shift")
			->alias('s')
			->join('__ALREADY_CITY__ c','c.city_id = s.linecityid')
			->field('s.shiftid')
			->where($condition)
			->select();	
		if(!empty($line)){
			$res = $this->arr2str($line);
			$where["o.shiftid"] = array('in',$res); //提货单 班次队列ID
		}else{
			return json(['code'=>'1001','message'=>'暂无数据']);
		}
		$where["p.status"] = '1';//接单状态1未接2已接3已完成
		$where["o.paystate"] = '2';//支付状态，必须是已支付  1未支付   2已支付   3支付失败
		$result = Db::table("ct_order")
			->alias('o')
			->join("__PICKORDER__ p","o.oid = p.orderid")
			->field('p.picid,o.addtime,o.picktime,o.totalnumber,o.totalweight,o.totalvolume,o.pickcost,o.oid,o.slogid,o.shiftid')
			->order("o.addtime desc")
			->where($where)
			->paginate(10);

		$list_mes = $result->toArray();
		$list = $list_mes['data'];

		if(!empty($list)){
			foreach ($list as $key => $value) {
				// $mes_where['g.status'] = '1'; //状态（1进行中2已完成）
				$mes_where['g.sid'] = $value['slogid'];
				$line = Db::table("ct_shift")
					->alias('s')
					->join('__ALREADY_CITY__ c','c.city_id = s.linecityid')
					->field('g.slid,s.beginareaid,s.beginaddress,c.start_id')
					->where($mes_where)
					->find();	
				//查询起点城市
				$city_name = Db::table("ct_district")->field("name")->where("id",$line['start_id'])->find();
				$area_name = Db::table("ct_district")->field("name")->where("id",$line['beginareaid'])->find();
				$list[$key]['city_name'] = $city_name['name'];
				$list[$key]['beginaddress'] =$city_name['name'].$area_name['name'].$line['beginaddress'];
				$list[$key]['pickcost'] = round($value['pickcost']);
			}
		}
		/*$driverdata = Db::field('carstatus,companyid')->table('ct_driver')->where('drivid',$driver_id)->find();
		$cardata = Db::field('status')->table('ct_carcategory')->where('driverid',$driver_id)->select();
		$carstatus = '1';
		foreach ($cardata as $key => $value) {
			if ($value['status'] == '2') {
				$carstatus = 2;
			}
		}
		if ($driverdata['companyid'] != '') {
			$driverdata['carstatus'] = 2;
			$carstatus = 2;
		}*/  //2017-7-25
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			//return json(['code'=>'1002','message'=>'查询成功','data'=>$list,'driverstatus'=>$driverdata['carstatus'],'carstatus'=>$carstatus]); //2017-7-25
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list,'driverstatus'=>2,'carstatus'=>2]);
		}

	}

	//零担详情
	public function bulk_detail(){
		$token   = input("token");  //令牌
		$oid   = input("oid");  //订单ID
	    if(empty($oid)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		/*$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}*/


		$detail = Db::table("ct_order")
				->field('oid,ordernumber,itemtype,coldtype,remark,picktime,slogid,shiftid,addtime,totalnumber,totalweight,totalvolume,pickcost,pickaddress,sendaddress')
				->where('oid',$oid)
				->find();
		
		$mes_where['g.sid'] = $detail['shiftid'];
		$mes_where['g.status'] = '1'; //状态（1进行中2已完成）
		$line = Db::table("ct_shift")
			->alias('s')
			->join('__ALREADY_CITY__ c','c.city_id = s.linecityid')
			->field('s.beginareaid,s.beginaddress,s.linecityid,c.start_id')
			->where($mes_where)
			->find();
		
		//查询起点城市
		$city_name = Db::table("ct_district")->field("name")->where("id",$line['start_id'])->find();
		$area_name = Db::table("ct_district")->field("name")->where("id",$line['beginareaid'])->find();
		$result['city_name'] = $city_name['name'];
		$result['beginaddress'] =$city_name['name'].$area_name['name'].$line['beginaddress'];
		$pickaddress = json_decode($detail['pickaddress'],TRUE);

		foreach ($pickaddress as $key => $value) {
				$pick_arr[] = $city_name['name'].$value['taddressstr'];
		}
		$result['pick_address'] = $pick_arr;
			
		$res_city = Db::table("ct_already_city")
				->where('city_id',$line['linecityid'])
				->find();
		$result['ordernumber']	= $detail['ordernumber']; //订单号
		$result['add_time'] = $detail['addtime'];	     //下单时间
		$result['totalnumber'] = $detail['totalnumber']; //总数量
		$result['totalweight'] = $detail['totalweight']; //总重量
		$result['totalvolume'] = $detail['totalvolume']; //总体积
		$result['itemtype'] = $detail['itemtype']; //物品类型
		$result['coldtype'] = $detail['coldtype']; //冷藏类型
		$result['picktime'] = $detail['picktime']; //预计提货时间
		$result['remark'] = $detail['remark']; //备注


			
		//获取干线运营人员
		$peple_send = Db::field('d.realname,d.mobile')
			->table('ct_order')
			->alias('a')
			->join('ct_shift c','c.sid=a.shiftid')
			->join('ct_driver d','d.companyid=c.companyid')
			->where(array('a.oid'=>$oid,'d.type'=>2))
			->find();
		
		$result['peple_send_name'] = $peple_send['realname'];	
		$result['peple_send_phone'] = $peple_send['mobile'];			

		//查询物品
		
		$result['money'] = round($detail['pickcost']); //提货费
		//$result['goods_list'] = $this->senditem($oid,1);
		

		//add 2017-7-25
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			//return json(['code'=>'1002','message'=>'查询成功','data'=>$result]); //2017-7-25
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result,'driverstatus'=>2,'carstatus'=>2]);
		}	 

	}

	public function test(){
		echo round(driver_money_rang('1092','2'));
	}


	/**
	 * 零担订单承接
	 * @auther 李渊
	 * @date 2018.6.14
	 * @param [string] token 用户令牌
	 * @param [Int] picid 订单id
	 * @return [type] [description]
	 */
	public function bulk_ask(){
		$token   = input("token");  //令牌
		$picid   = input("picid");  //提货ID
	    if(empty($token) || empty($picid)){
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
		// 查询接单人信息
		$driver_mes = Db::table("ct_driver")->where('drivid',$driver_id)->find();
		// 判断订单是否被承接
		$judge_order = DB::table('ct_pickorder')->where('picid',$picid)->find();
		if ($judge_order['status'] == 2) {
			return json(['code'=>'1012','ostate'=>1]);
			exit;
		}
		// 判断是否为个体司机
		if ($driver_mes['companyid'] == '' && $driver_mes['type'] =='1') {
			$up_data = array(
			'driverid'=>$driver_id,
			'type'=>$type,
			'status'=>'2', //接单状态1未接2已接3已完成
			'receivetime'=>time(),
			'drivername'=>$driver_mes['realname'],
			'driverphone'=>$driver_mes['mobile'],
			'carlicense'=>''
			);
		}else{
			$up_data = array(
			'driverid'=>$driver_id,
			'type'=>$type,
			'status'=>'2', //接单状态1未接2已接3已完成
			'receivetime'=>time()
			);
		}
		$re = Db::table("ct_pickorder")->where('picid',$picid)->update($up_data);
		$result = Db::field('tprice')->table('ct_pickorder')->where('picid',$picid)->find();
		if($re){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}

	}	


    /**
     * 首页-整车抢单列表
     * @auther: 李渊
     * @date: 2018.9.7
     * 抢单列表所有人可看，所以不加token验证
     * @return [type] [description]
     */
    public function vehical(){
    	// 支付状态 除了未支付的所有订单
    	$condition['paystate'] = ['NEQ','1'];
    	// 查询整车订单
		$result = Db::table("ct_userorder")
			->alias('u')
			->join("__CARTYPE__ c","c.car_id = u.carid",'left')
			->field('u.uoid,u.order_type,u.carnum,u.ispay,u.fprice,u.handingmode,u.down_price,u.startcity,u.endcity,u.startarea,u.endarea,u.loaddate,u.temperture,u.orderstate,c.carparame,u.price,u.carr_upprice,u.type,u.pickaddress,u.sendaddress')
			->order("u.addtime desc")
			->where($condition)
            ->where('orderstate','neq',4)
			->paginate(10);
		// 转数组
		$list_mes = $result->toArray();
		// 获取data数据
		$list = $list_mes['data'];
		// 遍历数组
		foreach ($list as $key => $value) {
			// 返回起点城市
			$list[$key]['startcity'] = $value['startcity'];
			// 返回终点城市
			$list[$key]['endcity'] = $value['endcity'];
			// 返回车型
			$list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';
			// 提货地址
			$pick_address = json_decode($value['pickaddress'],TRUE);
			// 配送地址
			$send_address = json_decode($value['sendaddress'],TRUE);
			// 返回装卸个数
			$list[$key]['pickuload'] = count($pick_address).'装'.count($send_address).'卸';
			// 返回整车运费
			$var_price = $value['carr_upprice']=='' ? $value['price'] : $value['carr_upprice'];
			$list[$key]['price'] = round($var_price);
			$list[$key]['order_type'] = $value['order_type'];
			$list[$key]['down_price'] = $value['down_price'];
			$list[$key]['tprice'] = $value['price'];
			$list[$key]['carnum'] = $value['carnum'];
			// 返回支付类型： 1 标准价格  2 面议 3 提货付款 4 到货付款
			$list[$key]['type'] = $value['type'];
			// 如果订单未接单并且提货时间超过现在则订单状态为7超时
			if ($value['orderstate']== 1 && $value['loaddate']/1000 < time()) {
				$list[$key]['orderstate'] = 7;
			}
		}
		// 判断数组是否为空
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}
    }
    //整车支付确认订单提交数据
    public function vehical_post()
    {
        $token = input('token');
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        // 验证token
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        // 用户id

        $data['driverid'] = $driver_id;
        //uoid
        $data['uoid'] = input('uoid');
        // 用户出价
        $order_price = input("price");
        // 平台计算的订单运费
        $data['fprice']  = input("fprice");
        // 承运商运费 先为平台计算的运费
        $data['price']  = round($order_price);
        // 订单编号
        $data['ordernumber'] = 'K'.date('ymdHis').mt_rand('000','999');
        // 车型ID
        $data['carid']       = input("carid");
        // 起点城市ID
        $data['startcity']   = input("startcity");
        // 终点城市ID
        $data['endcity']     = input("endcity");
        // 发货日期
        $data['picktime']    = input("picktime");
        // 备注
        $data['remark']      = input("remark");
        // 预计到达时间
//	    $data['arrtime']      = input("arrtime");
        // 下单日期
        $data['ordertime']     = time();
        // 公里数
        $data['fkilo']  = input("fkilo");
        // 是否装
        $data['pickyesno']  = input("pickyesno");
        // 是否卸
        $data['sendyesno']  = input("sendyesno");
        // 温度
        $data['temperture']  = input("temperture");
        // 物品名称
        $data['goodsname']  = input("goodsname");
        // 选择支付类型： 1 提货支付 2 配送支付
        $data['pay_type']  = input("pay_type");
        //运输门点
        $data['handingmode'] = input('handingmode');
        //支付状态
        $data['orderstatus'] = 1;
        // 提货地址
        $data['pickaddress'] = input("pickaddress");
        // 配货地址
        $data['sendaddress'] = input("sendaddress");

        $inser = Db::table('ct_driverorder') ->insertGetId($data);

        if($inser){
            return json(['code'=>'1001','message'=>'提交成功','data'=>$inser]);
        }else{
            return json(['code'=>'1002','message'=>'提交失败']);
        }
    }
    //城配订单提交
    public function delivery_post(){
        $token = input('token');
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        // 验证token
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        // 用户id

        $data['ordernumber']  = 'SP'.date('ymdHis').mt_rand('000','999');
        $data['driverid'] = $driver_id;
        $data['uoid'] = input('uoid');
        $data['carid'] = input('carid');
        $data['startcity'] = input('startcity');
        $data['taddress'] = input('taddress');
        $data['paddress'] = input('paddress');
        $data['temperture'] = input('temperture');
        $data['goodsname'] = input('goodsname');
        $data['weight'] = input('weight');
        $data['volume'] = input('volume');
        $data['pickyesno'] = input('pickyesno');
        $data['sendyesno'] = input('sendyesno');
        $data['handingmode'] = input('handingmode');
        $data['paytype'] = input('paytype');
        $data['picktime'] = input('picktime');
        $data['remark'] = input('remark');
        $data['price'] = input('price');
        $data['fprice'] = input('fprice');
        $data['createtime'] = time();
        $data['orderstatus'] = 1;

        $inser = Db::table('ct_delivery_order') ->insertGetId($data);

        if($inser){
            return json(['code'=>'1001','message'=>'提交成功','data'=>$inser]);
        }else{
            return json(['code'=>'1002','message'=>'提交失败']);
        }
    }
    /*
     * 零担订单信息支付订单提交
     * */
    public function bulk_post(){
        $token = input('token');
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        // 验证token
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        $id = input('oid');

        $res = Db::table('ct_order')->field('*')->where('oid',$id)->find();
        $list['driverid'] = $driver_id;
        $list['ordernumber']= 'Z'.date('ymdHis').mt_rand('000','999'); //订单编号;
        $list['oid'] = $id;
        $list['addtime'] = time();
        $list['slogid'] = $res['slogid'];
        $list['userid']= $res['userid'];
        $list['coldtype'] = $res['coldtype'];
        $list['totalnumber'] = $res['totalnumber'];
        $list['totalweight'] = $res['totalweight'];
        $list['itemtype'] = $res['itemtype'];
        $list['picktime'] = $res['picktime'];
        $list['orderstate'] = 1;
        $list['remark'] = $res['remark'];
        $list['totalvolume'] = $res['totalvolume'];
        $list['lineprice'] = $res['linepice'];
        $list['pickcost'] = $res['pickcost'];
        $list['delivecost'] = $res['delivecost'];
        $list['usercheck'] = $res['usercheck'];
        $list['serviceid'] = $res['serviceid'];

        $list['pickaddress'] = $res['pickaddress'];
        $list['sendaddress'] = $res['sendaddress'];
        $list['user_checkid'] = $res['user_checkid'];
        $list['receipt'] = $res['receipt'];
        $list['all_price'] = $res['all_price'];
        $list['shiftid'] = $res['shiftid'];
        $list['arrtime'] = $res['arrtime'];
        $list['starttime'] = $res['starttime'];

        $list['pay_type'] = $res['pay_type'];
        $list['arrivetime'] = $res['arrivetime'];

        $list['picksite'] = $res['picksite'];
        $list['stime'] = $res['stime'];
        $list['sphone'] = $res['sphone'];
        $list['sendsite'] = $res['sendsite'];
        $list['dtime'] = $res['dtime'];
        $list['tphone'] = $res['tphone'];

        $data = Db::table('ct_bulk_order')->insertGetId($list);
        if($data){
            return json(['code'=>'1001','message'=>'提交成功','data'=>$data]);
        }else{
            return json(['code'=>'1002','message'=>'提交失败']);
        }
    }
    //首页-整车列表 2019.5.23
    public function vehicle(){
        // 支付状态 除了未支付的所有订单
        $condition['orderstatus'] = ['NEQ','1'];
        // 查询整车订单
        $result = Db::table("ct_useorder")
            ->alias('u')
            ->join("__CARTYPE__ c","c.car_id = u.carid",'left')
            ->join('ct_user b',"b.uid = u.userid",'left')
            ->field('u.*,c.carparame,b.username,b.phone')
            ->order("u.addtime desc")
            ->where($condition)
            ->where('orderstatus','neq',4)
            ->order('addtime DESC')
            ->paginate(10);

        // 转数组
        $list_mes = $result->toArray();
        // 获取data数据
        $list = $list_mes['data'];
        // 遍历数组
        foreach ($list as $key => $value) {
            // 返回起点城市
            $list[$key]['startcity'] = $value['startcity'];
            // 返回终点城市
            $list[$key]['endcity'] = $value['endcity'];
            // 返回车型
            $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';
            // 提货地址
//            $pick_address = json_decode($value['pickaddress'],TRUE);
//            // 配送地址
//            $send_address = json_decode($value['sendaddress'],TRUE);
            // 返回装卸个数
            $list[$key]['handingmode'] = $value['handingmode'];
            // 返回整车运费
            $list[$key]['price'] = round($value['price']);
            // 如果订单未接单并且提货时间超过现在则订单状态为7超时
//            if ($value['orderstatus']== 2 && $value['picktime']/1000 < time()) {
//                $list[$key]['orderstatus'] = 4;
//            }
        }
        // 判断数组是否为空
        if(empty($list)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
        }
    }

    //首页--整车详情 2019.5.23
     public function vehicle_view(){
         // 订单ID
         $uoid = input("uoid");
         // 判断是否有id
         if(empty($uoid)){
             return json(['code'=>'1000','message'=>'参数错误']);
         }
         // 查询数据
         $detail = Db::table("ct_useorder")
             ->alias('o')
             ->join('__CARTYPE__ c','o.carid = c.car_id','left')
             ->field('o.*,c.carparame')
             ->where('o.uoid',$uoid)
             ->find();

         //下单人电话
         $id = Db::table('ct_useorder')->field('userid')->where('uoid',$uoid)->find();
         $userinfo = Db::table('ct_user')->field('username,phone')->where('uid',$id['userid'])->find();
//         exit();
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
         $detail['username'] = $userinfo['username'];
         $detail['phone'] = $userinfo['phone'];

         // 判断是否有数据
         if(empty($detail)){
             return json(['code'=>'1001','message'=>'暂无数据']);
         }else{
             return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
         }
     }
    //首页城配订单列表  2019.6.1
     public function delivery_list(){
        $startcity = input('startcity');
         // 支付状态 除了未支付的所有订单
         $condition['orderstatus'] = ['NEQ', '1'];
         // 查询整车订单
         $result = Db::table("ct_delivery")
             ->alias('u')
             ->join("__CARTYPE__ c", "c.car_id = u.carid", 'left')
             ->join('ct_user b', "b.uid = u.userid", 'left')
             ->field('u.*,c.carparame,b.username,b.phone')
             ->order("u.addtime desc")
             ->where($condition)
             ->where('startcity',$startcity)
             ->where('orderstatus', 'neq', 4)
             ->order('addtime DESC')
             ->paginate(10);

         // 转数组
         $list_mes = $result->toArray();
         // 获取data数据
         $list = $list_mes['data'];
         // 遍历数组
         foreach ($list as $key => $value) {
             // 返回起点城市
             $list[$key]['startcity'] = $value['startcity'];
             // 返回终点城市
             $list[$key]['endcity'] = $value['endcity'];
             // 返回车型
             $list[$key]['carparame'] = $value['carparame'] ? $value['carparame'] : '12.5米-15米冷藏箱车';
             // 提货地址
//            $pick_address = json_decode($value['pickaddress'],TRUE);
//            // 配送地址
//            $send_address = json_decode($value['sendaddress'],TRUE);
             // 返回装卸个数
             $list[$key]['handingmode'] = $value['handingmode'];
             // 返回整车运费
             $list[$key]['price'] = round($value['price']);
             $list[$key]['fprice'] = round($value['fprice']);
             // 如果订单未接单并且提货时间超过现在则订单状态为7超时
//            if ($value['orderstatus']== 2 && $value['picktime']/1000 < time()) {
//                $list[$key]['orderstatus'] = 4;
//            }
         }
         // 判断数组是否为空
         if (empty($list)) {
             return json(['code' => '1001', 'message' => '暂无数据']);
         } else {
             return json(['code' => '1002', 'message' => '查询成功', 'data' => $list]);
         }
     }
     //首页城配订单详情
    public function delivery_view(){
        // 订单ID
        $uoid = input("uoid");
        // 判断是否有id
        if(empty($uoid)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        // 查询数据
        $detail = Db::table("ct_delivery")
            ->alias('o')
            ->join('__CARTYPE__ c','o.carid = c.car_id','left')
            ->field('o.*,c.carparame')
            ->where('o.uoid',$uoid)
            ->find();

        //下单人电话
        $id = Db::table('ct_delivery')->field('userid')->where('uoid',$uoid)->find();
        $userinfo = Db::table('ct_user')->field('username,phone')->where('uid',$id['userid'])->find();

        // 整车运费
        $price = $detail['fprice'];
        // 返回运费并取整
        $detail['fprice'] = round($price);
        // 起点城市
        $detail['startcity'] = $detail['startcity'];
        // 终点城市
        $detail['endcity'] = $detail['endcity'];
        // 车型
        $detail['carparame'] = $detail['carparame'] ? $detail['carparame'] : '12.5米-15米冷藏箱车';
        // 发货地
        $detail['pickaddress'] = json_decode($detail['taddress']);
        // 卸货地
        $detail['sendaddress'] = json_decode($detail['paddress']);
        $detail['username'] = $userinfo['username'];
        $detail['phone'] = $userinfo['phone'];

        // 判断是否有数据
        if(empty($detail)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
        }
    }


    /**
     * 首页-整车抢单详情
     * @auther: 李渊
     * @date: 2018.9.7
     * 抢单列表所有人可看，所以不加token验证
     * @param  [Int]  [uoid] [订单id]
     * @return [Json] [订单详情]
     */
	public function vehicle_detail(){
		// 订单ID  
		$uoid = input("uoid");  
		// 判断是否有id
		if(empty($uoid)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 查询数据
		$detail = Db::table("ct_userorder")
				->alias('o')
				->join('__CARTYPE__ c','o.carid = c.car_id','left')	
				->field('o.*,c.carparame')
				->where('o.uoid',$uoid)
				->find();
		// 整车运费
		$price = $detail['carr_upprice'] == '' ? $detail['price'] : $detail['carr_upprice'];
		// 返回运费并取整
		$detail['price'] = round($price);
		// 起点城市
		$detail['startcity'] = $detail['startcity'];
		// 终点城市
		$detail['endcity'] = $detail['endcity'];
		// 车型
		$detail['carparame'] = $detail['carparame'] ? $detail['carparame'] : '12.5米-15米冷藏箱车';
		// 发货地
		$detail['saddress'] = json_decode($detail['pickaddress']);
		// 卸货地
		$detail['paddress'] = json_decode($detail['sendaddress']);
		// 判断是否有数据
		if(empty($detail)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
		}	
	}
    
	/**
	 * 首页-整车抢单
	 * @auther： 李渊
	 * @date： 2018.6.13
	 * @param  [string]  [token] [用户令牌]
	 * @param  [Int]  	 [uoid]  [订单id]
	 * @return [type] [description]
	 */
	public function vehical_ask(){
		// 令牌
		$token = input("token");
		// 订单ID  
		$uoid = input("uoid");  
		// 判断是否传值
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

		// 查询订单数据
		$judge_order = DB::table('ct_userorder')->where('uoid',$uoid)->find();
		// 查询用户信息
		$driver_mes = Db::table("ct_driver")->where('drivid',$driver_id)->find();

		// 查找下单用户属性
		$find_user = Db::table('ct_userorder')->alias('o')->join('ct_user u','o.userid=u.uid')->field('u.lineclient,o.order_type')->where('uoid',$uoid)->find();
		


		// 初始化订单是否被承接状态 0 已经被承接 1 未承接
		$checkArr['isState'] = 1;
		// 初始化司机资金限制 0 资金不足不能接单 1 可承接(资金大于后台配置的接单最低金额 2、公司接单不受限制)
		$checkArr['isDriver'] = 1;
		// 初始化司机是否有车 0 没有可用车 1 有 (公司接单不受限制)
		$checkArr['isCar'] = 1;
		// 初始化司机是否能接单个体司机不能接公司下的订单 0 不能 1能
		$driver_affim = 1;

		// 判断订单是否被承接
		if ($judge_order['orderstate'] == 2) { // 订单已被承接
			$checkArr['isState'] = 0;
		}
		

		// 判断用户属性 1 个体用户 2 项目客户
		$checkUser = $find_user['lineclient'] == '' ? '1':'2';
		// 判断司机属性 1 司机 2 否
		$checkDriver = $driver_mes['type'] == '1' ? '1':'2';
		// 如果项目客户下单个体司机不能承接
		if ($checkUser =='2' && $checkDriver == '1') {
			$driver_affim = 0;
		}

		// 如果是特价整车所有人都可以接单
		if ($find_user['order_type'] == 2) {
			$driver_affim = 1;
		}

		// 如果项目客户下单个体司机不能承接
		if($driver_affim == 0){
			return json(['code'=>'1000','message'=>'该订单为公司订单，您不是公司用户，不能承接','data'=>$driver_affim]);
			exit;
		}
		
		// 获取司机姓名
		$driverName = $driver_mes['realname'] == '' ? $driver_mes['username'] : $driver_mes['realname'];


		// 判断是否有车 个体司机要判断是否有审核通过的车 公司司机不用判断
		if($driver_mes['companyid'] == ''){
			// 查找车辆
			$car = Db::table('ct_carcategory')->where(array('driverid'=>$driver_id,'status'=>2))->find();

			// 插入订单接单人信息
			$up_data = array(
				'carriersid'=>$driver_id,
				'orderstate'=>'2', 
				'drivername'=>$driverName,
				'driverphone'=>$driver_mes['mobile'],
				'taketime'=>time(),
				'carlicense'=>$car['carnumber']
			);
		}else{
			// 插入订单接单人信息
			$up_data = array(
				'carriersid'=>$driver_id,
				'orderstate'=>'2',
				'taketime'=>time()
			);
		}
		// 是否可抢单完成
		if($checkArr['isState'] == 0 || $checkArr['isDriver'] == 0 ||  $driver_affim == 0){
			return json(['code'=>'1003','message'=>'不能抢单','data'=>$checkArr]);
			exit;
		}
		// 插入接单数据
		$re = Db::table("ct_userorder")->where('uoid',$uoid)->update($up_data);
		if($re){
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}

	}
    /*
     * 判断公司或个体
     * */
    public function iscompany(){
        $token = input("token");
        // 订单ID
        $uoid = input("uoid");
        // 判断是否传值
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
        // 查询订单数据
        $judge_order = DB::table('ct_userorder')->where('uoid',$uoid)->find();
        // 查询用户信息
        $driver_mes = Db::table("ct_driver")->where('drivid',$driver_id)->find();
        // 查找下单用户属性
        $find_user = Db::table('ct_userorder')->alias('o')->join('ct_user u','o.userid=u.uid')->field('u.lineclient,o.order_type')->where('uoid',$uoid)->find();
        // 初始化订单是否被承接状态 0 已经被承接 1 未承接
        $checkArr['isState'] = 1;
        $driver_affim = 1;
        $driver_aff = 1;
        //公司司机不可以接单
        if($driver_mes['companyid'] != '' && $driver_mes['type'] == 1){
             $driver_aff = 0;
        }

        // 判断用户属性 1 个体用户 2 项目客户
        $checkUser = $find_user['lineclient'] == '' ? '1':'2';
        // 判断司机属性 1 司机 2 否
        $checkDriver = $driver_mes['type'] == '1' ? '1':'2';

        // 如果项目客户下单个体司机不能承接
        if ($checkUser =='2' && $checkDriver == '1') {
            $driver_affim = 0;
        }

        // 如果是特价整车所有人都可以接单
        if ($find_user['order_type'] == 2) {
            $driver_affim = 1;
        }
        //公司下的司机不可以接单
        if ($driver_aff == 0){
            return json(['code'=>'1004','message'=>'只有该公司下的管理或者调度才可以接单','data'=>$driver_aff]);
            exit;
        }
        // 如果项目客户下单个体司机不能承接
        if($driver_affim == 0){
            return json(['code'=>'1000','message'=>'该订单为公司订单，您不是公司用户，不能承接','data'=>$driver_affim]);
            exit;
        }
        // 判断订单是否被承接
        if ($judge_order['orderstate'] == 2) { // 订单已被承接
            $checkArr['isState'] = 0;
        }
        // 是否可抢单完成
        if($checkArr['isState'] == 0){
            return json(['code'=>'1003','message'=>'订单已承接','data'=>$checkArr]);
            exit;
        }
        $company = $driver_mes['companyid'];
        if($company){
            return json(['code'=>'1001','message'=>'公司','data'=>$checkArr]);
        }else{
            return json(['code'=>'1002','message'=>'个体司机','data'=>$checkArr]);
        }

    }
    /*
     * 个体司机是否认证
     * */
    public function isauth(){
        $token = input("token");
        // 订单ID
        $uoid = input("uoid");
        // 判断是否传值
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
        // 查询用户信息
        $driver_mes = Db::table("ct_driver")->where('drivid',$driver_id)->find();
        // 初始化司机驾驶证状态 0 驾驶证未认证不能接单 1 可承接(驾驶证认证 2、公司接单不受限制)
        $checkArr['isDriver'] = 1;
        // 初始化司机是否能接单个体司机不能接公司下的订单 0 不能 1能

        // 判断驾驶证是否认证 个体司机接单需要认证 公司司机不需要认证
		if ($driver_mes['driver_grade'] =='1' && $driver_mes['carstatus'] != '2') {
			$checkArr['isDriver'] = 0;
		}
		if ($checkArr['isDriver'] == 1){
		    return json(['code'=>'1001','message'=>'已认证','data'=>$checkArr]);
        }else{
		    return json(['code'=>'1002','message'=>'未认证','data'=>$checkArr]);
        }

    }
	/**
	 * 2017-12-1
	 * author:dachenwei
	 * update
	 * 市内配送列表
	*/
	public function city_With()
	{
		$token = input('token');
		// 验证token
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		// 查询司机信息  
		$find_driver = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		// 
		if ($find_driver['type'] =='1') {
			$owhere['pay_type'] = ['neq','1'];
		}
		$owhere['paystate'] = 2;
		
		$cityorder = DB::table('ct_city_order')->where($owhere)->whereOr('paystate',4)->order("addtime desc")->paginate(10);
		$arr = array();
		foreach ($cityorder as $key => $value) {
			$carmess = Db::table('ct_cartype')->where('car_id',$value['carid'])->find();
			$city = detailadd($value['city_id'],'','');
			$arr[$key]['cityname'] = $city;
			$arr[$key]['id'] = $value['id'];
			$arr[$key]['data_type'] = $value['data_type'];
			$arr[$key]['state'] = $value['state'];
			$arr[$key]['carname'] = $carmess['carparame'];
			$arr[$key]['ordertype'] = $value['ordertype'];
			$paymoney = $value['carr_upprice']=='' ? $value['paymoney'] : $value['carr_upprice'];
			$arr[$key]['paymoney'] = round($paymoney);
			if($value['pytype'] == 3){ // 提货支付
				$arr[$key]['paymoney'] = $value['actual_payment'];
			}
			$arr[$key]['cold_type'] = $value['cold_type'];
			$arr[$key]['pytype'] = $value['pytype'];
			$arr[$key]['handingmode'] = $value['handingmode'];
			$arr[$key]['fprice'] = $value['fprice'];
			if ($value['state']==1 && strtotime($value['data_type']) < time()) {
				$arr[$key]['state'] =6;
			}
		}

		if(empty($arr)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$arr]);
		}
	}
	
	/*
	*2017-12-1
	*author:dachenwei
	*update
	*市内配送订单详情
	*/

	public function city_With_detail(){
		$token   = input("token");  //令牌
		$id = input('id');//订单ID
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
		$result = Db::table('ct_city_order')
				->alias('o')
				->join('ct_cartype c','c.car_id=o.carid')
				->field('o.*,c.carparame')
				->where('id',$id)
				->find();
		$city = detailadd($result['city_id'],'','');
		$result['cityname'] = $city;
		$result['saddress'] = json_decode($result['saddress'],TRUE);
		$result['eaddress'] = json_decode($result['eaddress'],TRUE);
		$paymoney = $result['carr_upprice']=='' ? $result['paymoney'] : $result['carr_upprice'];
		//$result['actualprice'] = round($paymoney);
		$result['paymoney'] = round($paymoney);
		if($result['pytype'] == 3){
			$result['paymoney'] = $result['actual_payment'];
		}
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
	}

	/**
	 * 首页-城配抢单
	 * @auther 李渊
	 * @date 2018.6.13
	 * @param [string] token 用户令牌
	 * @param [int] id 订单id
	 * @return [type] [description]
	 */
	public function city_With_ask(){
		$token = input("token");  //令牌
		$id = input('id'); //订单ID
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
		
		// 判断订单是否被承接
		$judge_order = DB::table('ct_city_order')->where(array('id'=>$id))->find();
		if ($judge_order['state'] == 2) { // 订单已被承接
			$checkArr['isState'] = 0;
		}else{
			$checkArr['isState'] = 1;
		}
		// 判断是否为司机接单 如果为司机接单则余额需要有钱才可以接单
		$driver_mes = Db::table("ct_driver")->where('drivid',$driver_id)->find();
		$driverName = $driver_mes['realname'] == '' ? $driver_mes['username'] : $driver_mes['realname'];
		// 司机资产小于设置最低金额时候不能承接订单
		$config = Db::table('ct_config')->where('id',12)->find();
		if ($driver_mes['type'] =='1') { // 司机接单
			if (($driver_mes['money']+$driver_mes['balance']) < $config['auth_price']) {
				$checkArr['isMoney'] = 0;
			}else{
				$checkArr['isMoney'] = 1;
			}
		}else{
			$checkArr['isMoney'] = 1;
		}
		$checkArr['auth_price'] = $config['auth_price'];
		// 判断驾驶证是否认证 个体司机接单需要认证 公司司机不需要认证
		if ($driver_mes['driver_grade'] =='1') { 
			if ($driver_mes['carstatus'] != '2') { // 驾驶证未认证或者认证失败
				$checkArr['isDriver'] = 0;
			}else{
				$checkArr['isDriver'] = 1;
			}
		}else{
			$checkArr['isDriver'] = 1;
		}
		// 判断是否有车 个体司机要判断是否有审核通过的车 公司司机不用判断
		if($driver_mes['companyid'] == '' && $driver_mes['type'] == '1'){
			$car = Db::table('ct_carcategory')->where(array('driverid'=>$driver_id,'status'=>2))->find();
			if (empty($car)) { // 没有车则返回
				$checkArr['isCar'] = 0;
			}else{
				$checkArr['isCar'] = 1;
			}
			// 插入订单接单人信息
			$up_data = array(
			'driverid'=>$driver_id,
			'take_time'=>time(),
			'drivername'=>$driverName,
			'driverphone'=>$driver_mes['mobile'],
			'carlicense'=>$car['carnumber']
			);
		}else{
			$checkArr['isCar'] = 1;
			// 插入订单接单人信息
			$up_data = array(
			'driverid'=>$driver_id,
			'take_time'=>time()
			);
		}
		// 是否可抢单完成
		if($checkArr['isState'] == 0 || $checkArr['isMoney'] == 0 || $checkArr['isDriver'] == 0 || $checkArr['isCar'] == 0){
			return json(['code'=>'1003','message'=>'不能抢单','data'=>$checkArr]);
			exit;
		}
		// 插入接单数据
		$re = Db::table("ct_rout_order")->where('rid',$judge_order['rout_id'])->update($up_data);
		// 更新订单状态
		$city_data['state'] = 2;
		DB::table("ct_city_order")->where('id',$id)->update($city_data);
		if($re){
			//add 2017-7-26
			if ($driver_mes['type'] == '3' && $driver_mes['coldmoney'] !='0' ) {
				Db::table('ct_driver')->where('drivid',$driver_id)->update(array('coldmoney'=>$driver_mes['coldmoney']-100));
			}//2017-7-26 end
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}

	}

	/**
	 * 首页-货源列表
	 * @Auther: 李渊
	 * @Date: 2018.6.14
	 * @param [string] token 用户令牌
	 * @param [int] startcityid 起点城市id
	 * @param [int] endcityid 终点城市id
	 * @return [type] [description]
	 */
	public function issue_item_list(){
		// 起点城市id 
		$startid = input('startcityid');
		// 终点城市id
		$endcityid = input('endcityid');
		// 运输方式
		$carriage = input('carriage');
		// 查询条件 起点城市
		if ($startid !='') {
			$condition['o.start_city'] = $startid;
		}
		// 查询条件 终点城市
		if ($endcityid !='') {
			$condition['o.end_city'] = $endcityid;
		}
		// 查询条件 是否包车
		if ($carriage !='') {
			$condition['o.carriage'] = $carriage;
		}
		// 查询条件 用户发布货源
		$condition['o.ordertype'] = 1;
		// 查询条件 支付完成的
		$condition['o.paystate'] = 2;
		// 查询条件 支付完成的
		$condition['o.orderstate'] = 1;
		// 查询数据
		$result = Db::table("ct_issue_item")
				->alias('o')
				->join('ct_cartype car','car.car_id = o.carid','LEFT')
				->join('ct_user user','user.uid = o.userid')
				->field('o.*,car.carparame,user.image')
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
			// 发布数量
			$res1 = Db::table('ct_issue_item')->where(array('userid' => $value['userid'], 'ordertype' => 1, 'paystate' => 2))->count();
			// 取消数量
			$res2 = Db::table('ct_issue_item')->where(array('userid' => $value['userid'], 'ordertype' => 1, 'orderstate' => 3))->count();
			// 发布数量
			$list[$key]['issued'] = $res1;
			// 取消数量
			$list[$key]['cancel'] = $res2;
            // 起点地址
            $list[$key]['start_address'] = idToAddress('',$value['start_city'],$value['start_area']); 
            // 终点地址
            $list[$key]['end_address'] = idToAddress('',$value['end_city'],$value['end_area']); 

			// 用户图像
			if ($value['image']=='') {
				$list[$key]['image'] =  get_url().'/static/defaultUserImg.png';
			}else{
				$list[$key]['image'] = get_url().$value['image'];
			}
            // 重量
            $list[$key]['weight'] = $value['weight'] ? ($value['weight']/1000).'吨' : ''; 
            // 立方
            $list[$key]['volume'] = $value['volume'] ? $value['volume'].'方' : ''; 
            // 包车类型
            $list[$key]['carriage'] = $value['carriage'] == 1 ? '拼车' : '包车';

            $list[$key]['addtime'] = $value['addtime']; //下单时间
		}
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}	
	}

	/**
	 * 首页-货源详情
	 * @Auther: 李渊
	 * @Date: 2018.7.10
	 * @param [string] 	token 	用户令牌
	 * @param [int] 	orderid 订单id
	 * @return [type] [description]
	 */
	public function item_list_detail(){
		// 令牌
		$token = input("token");  
		// 订单ID
		$orderid = input("orderid");  
		// 判断数据是否为空
		if (empty($token) || empty($orderid)) {
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 验证令牌
		$check_result = $this->check_token($token);
		if ($check_result['status'] =='1') {
			return json(['code'=>'1007','message'=>'非法请求']);
		} elseif ($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		} else {
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

        // 起点地址
        $detail['startAddress'] = idToAddress($detail['start_pro'],$detail['start_city'],$detail['start_area']); 
        // 终点地址
        $detail['endAddress'] = idToAddress($detail['end_pro'],$detail['end_city'],$detail['end_area']); 

		// 下单人图像
		if ($detail['image']=='') {
			$detail['image'] =  get_url().'/static/defaultUserImg.png';
		}else{
			$detail['image'] = get_url().$detail['image'];
		} 
		// 重量
        $detail['weight'] = $detail['weight'] ? ($detail['weight']/1000).'吨' : '';
        // 立方
        $detail['volume'] = $detail['volume'] ? $detail['volume'].'方' : '';
        // 运输类型
        $detail['carriage'] = $detail['carriage'] == 1 ? '拼车' : '包车';
		// 查看人id
		$get_driver = json_decode($detail['driverid'],TRUE);
		// 默认该司机未查看此货源信息
		$driver_state = '1';
		if (!empty($get_driver)) {
			if (in_array($search_driver, $get_driver)) {  //查找该司机是否支付过
				$driver_state = '2';
			}
		}
		// 司机支付状态 1 未支付 2已支付	
		$detail['driver_state'] = $driver_state; 
		// 判断数据是否为空
		if(empty($detail)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$detail]);
		}	
	}

	/**
	 * 首页-货源详情-获取支付状态
	 * @Auther: 李渊
	 * @Date: 2018.9.17
	 * @param string token   [用户令牌]
	 * @param int 	 orderid [订单id]
	 * @return [type]        [description]
	 */
	public function goodPayState()
	{
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
		// 查询订单
		$order = Db::table("ct_issue_item")->field('driverid')->where(array('id'=>$orderid,'ordertype'=>1))->find();
		// 获取查看人id
		$get_driver = json_decode($order['driverid'],TRUE);

		// 默认支付状态
		$data['isPay'] = false;
		if (!empty($get_driver)) {
			// 是否支付过
			$data['isPay'] = in_array($search_driver, $get_driver);
		}
		// 返回数据
		return json(['code'=>'1001','message'=>'操作成功','data'=>$data]);
	}

	/**
	 * 发布货源： 司机支付查看完整发布货源信息
	 * @Auther：李渊
	 * @Date: 2018.9.17
	 */
	public function item_list_ask(){
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

		$arr = array();
		$order = Db::table("ct_issue_item")->field('driverid')->where(array('id'=>$orderid,'ordertype'=>1))->find();
		$get_driver = json_decode($order['driverid'],TRUE);

		$list = 0;
		if (!empty($get_driver)) {
			if (!in_array($search_driver, $get_driver)){  //查找该司机是否支付过
				$arr2[] = $search_driver;
				$arr = array_merge($arr2,$get_driver);
			}else{
				$arr = $get_driver;
			}
		}else{
			$arr[] = $search_driver;
		}
		
		$data['driverid'] = json_encode($arr);
		$result = Db::table("ct_issue_item")->where('id',$orderid)->update($data);
		if($result){
			return   json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/**************************************************************************/

}