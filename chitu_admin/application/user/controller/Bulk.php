<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Bulk  extends Base{

	/**
	 * 零担已开通起点城市
	 * @auther: 李渊
	 * @date: 2018.10.10
	 * @param  [String] [token] [用户令牌]
	 * @return [type] [description]
	 */
	public function start_city(){
		// 获取用户令牌
		$token = input("token");
		// 查询零担已开通起点城市数据
		$result = Db::table("ct_already_city")
			->alias('c')
			->join('ct_shift s','s.linecityid = c.city_id')
			->join('__DISTRICT__ d','d.id = c.start_id')
			->field('d.id  as value,d.name as text')
			->where('level','2')
			->order('d.id asc')
			->select();
		// 判断数据是否为空
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			$result =$this->assoc_unique($result,'text'); //数组，键值
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
	}

	/**
	 * 零担已开通起点城市对应开通终点城市
	 * @auther: 李渊
	 * @date: 2018.10.10
	 * @param  [Int]  [start_id] [起点城市id]
	 * @return [type] [description]
	 */
	public function end_city(){
		// 获取起点城市id
		$start_id   = input("start_id"); 
		// 判断是否传参
	    if(empty($start_id)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		// 查询起点城市对应开通的终点城市
		$result = Db::table("ct_already_city")
			->alias('c')
			->join('ct_shift s','s.linecityid = c.city_id')
			->join('__DISTRICT__ d','d.id = c.end_id')
			->field('d.id  as value,d.name as text')
			->where(array('c.start_id'=>$start_id,'level'=>2))
			->select();
		// 判断数据是否为空
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			$result =$this->assoc_unique($result,'text'); //数组，键值
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
	}

	/**
	 * 零担班次列表
	 * 规则： 当天检索第二天的班次。。如果当天时间超过下午五点，就检索第三天的班次
	 * @auther: 李渊
	 * @date: 2018.10.10
	 * @param  [String] [token] 		[用户令牌]
	 * @param  [Int]  	[start_id] 		[起点城市id]
	 * @param  [Int]  	[end_id] 	 	[终点城市id]
	 * @return [type] 	[description]
	 */
	public function shift_list(){
		$token 		 = input("token");		 // 获取用户令牌
		$start_id    = input("start_id");    // 起点城市ID
		$end_id      = input("end_id");  	 // 终点城市ID
		$order_type  = input("order_type");  // 排序规则：1 时效 2 价格
		// 验证参数
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
		// 排序规则：1 时效 2 价格
		switch ($order_type) { 
			case '1':
				$order = "s.trunkaging asc, g.deptime asc";
				break;
			case '2':
				$order = "s.price asc, g.deptime asc";
				break;
			default:
				$order = "g.deptime asc";
				break;
		}
		// 开始时间
		$begintoday = mktime(0,0,0,date('m'),date('d')+1,date('Y'));
		// 结束时间
		$endtoday = mktime(0,0,0,date('m'),date('d')+7,date('Y'))-1;
		// 查询条件 计划发车时间 在七天以内的
		$condition['g.deptime'] = array(array('gt',$begintoday),array('lt', $endtoday));
		// 如果没有始发城市和终点城市返回下单最多的城市
		if ($start_id =='' && $end_id=='') {
			// 状态（1进行中2已完成）
			$condition['g.status'] = '1'; 
			// 统计下单最多的班次 默认列表显示
			$count_shift = Db::table('ct_shift')
					->alias('a')
					->join('ct_order o','o.shiftid = a.sid')
					->field("count(a.sid) counts,a.sid")
					->group('sid')
					->order('counts','desc')
					->limit(10)
					->select();
			// 如果存在数据
			if (!empty($count_shift)) {
				$shift_id = $this->arrtostr($count_shift,'sid');
				$condition['s.sid'] = array('in',$shift_id);
				$line = Db::table("ct_shift")
					->alias('s')
					->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
					->field('s.sid,s.companyid,s.shiftnumber,s.trunkaging,s.price,s.timestrat,s.timeend,s.lowprice,s.shiftstate,s.pmoney,s.smoney,s.weekday,s.discount,g.slid,g.deptime')
					->where($condition)
					->order($order)
					->paginate(10);
			}else{
				//当下单总数为空时默认为上海班次
				$where['start_id'] = '45054';
				$result = Db::table("ct_already_city")->where($where)->select();
				$shift_id = $this->arrtostr($result,'city_id');
				$condition['s.linecityid'] = array('in',$shift_id);
				$line = Db::table("ct_shift")
					->alias('s')
					->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
					->field('s.sid,s.companyid,s.shiftnumber,s.trunkaging,s.price,s.timestrat,s.timeend,s.lowprice,s.shiftstate,s.pmoney,s.smoney,s.weekday,s.discount,g.slid,g.deptime')
					->where($condition)
					->order($order)
					->paginate(10);
			}
		}else{
			$condition['g.status'] = ['IN','1,3']; //状态（1进行中2已完成）
			$where['start_id'] = $start_id;
			$where['end_id'] = $end_id;
			$result = Db::table("ct_already_city")->where($where)->find();
			$condition['s.linecityid'] = $result['city_id'];
			if(!empty($result)){
				$line = Db::table("ct_shift")
					->alias('s')
					->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
					->field('s.sid,s.companyid,s.shiftnumber,s.trunkaging,s.price,s.timestrat,s.timeend,s.lowprice,s.shiftstate,s.pmoney,s.smoney,s.weekday,s.discount,g.slid,g.deptime')
					->where($condition)
					->order($order)
					->paginate(10);
			}else{
				return json(['code'=>'1003','message'=>'城市信息不存在']);
			}
		}
		$list_mes = $line->toArray();
		// 遍历数据
		foreach ($list_mes['data'] as $key => $value) {
			// 干线评论个数 暂时不用
			// $count_grade = Db::table('ct_linegrade')->where('shift_id',$value['sid'])->count('shift_id');
			// $sum_grade = Db::table('ct_linegrade')->where('shift_id',$value['sid'])->sum('line_grade');
			// if ($sum_grade=='') {
			// 		$list[$key]['grade'] = 0;
			// }else{
			// 		$list[$key]['grade'] = round($sum_grade/$count_grade);
			// }
			
			$shift = DB::table('ct_shift')->where('sid',$value['sid'])->find();
			// 查找干线城市对应的始发终点城市id
			$line_city = DB::table('ct_already_city')->where('city_id',$shift['linecityid'])->find();
			// 返回起点城市名称
			$start_city = detailadd($line_city['start_id'],'','');
			// 返回终点城市名称
			$end_city = detailadd($line_city['end_id'],'','');
			// 查询干线起点城市对应的提货区域
            $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['companyid'],'type'=>1,'province'=>$line_city['start_id']))->find();
            // 查询干线终点城市对应的配送区域
            $pei_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['companyid'],'type'=>2,'province'=>$line_city['end_id']))->find();
			// 返回起点城市
			$list[$key]['start_city'] = $start_city;
			// 返回终点城市
			$list[$key]['end_city'] = $end_city;
			// 返回起点城市id
			$list[$key]['start_id'] = $line_city['start_id'];
			// 返回终点城市id
			$list[$key]['end_id'] = $line_city['end_id'];
			// 返回干线班次
			$list[$key]['shiftnumber'] = $value['shiftnumber'];
			// 返回时效
			$list[$key]['trunkaging'] = $value['trunkaging'];
			// 返回干线价格每公斤多少元
			$list[$key]['price'] = $value['price'];
			// 返回折扣
			$list[$key]['discount'] = $value['discount'];
			// 返回干线折扣后价格每公斤多少元
			$list[$key]['discount_price'] = round($value['price']*$value['discount'],2);
			// 返回干线班次队列id
			$list[$key]['slid'] = $value['slid'];
			// 返回干线班次id
			$list[$key]['sid'] = $value['sid'];
			// 返回发车时间
			$list[$key]['deptime'] = $value['deptime'];
			// 返回干线最低收费
			$list[$key]['lowprice'] = sprintf("%.0f",$value['lowprice']);
			// 1 平台添加 2 自主添加
			$list[$key]['shiftstate'] = $value['shiftstate'];  
			// 自主添加提货费
			$list[$key]['pmoney'] = $value['shiftstate'] == 1 ? $ti_arr['price'] : $value['pmoney']; 
			$list[$key]['pmoney'] = round($list[$key]['pmoney']); 
			// 自主添加配送费
			$list[$key]['smoney'] = $value['shiftstate'] == 1 ? $pei_arr['price'] : $value['smoney'];
			$list[$key]['smoney'] = round($list[$key]['smoney']); 	
			// 自主添加班期
			$list[$key]['weekday'] = $value['weekday'];	
			// 获取当天下午五点的时间戳
			$new_wu = strtotime(date('Y-m-d 17:00:00')); 
			// 获取此刻时间
			$new = time(); 
			// 班线可用状态 1 不可用 2 正常显示
			$timestrat = '2';
			$list[$key]['time_status'] = $timestrat;
			if ($value['shiftstate'] =='1') {
				$time0 = 0;
				if($new >= $new_wu && strtotime(date('Y-m-d',strtotime('+1 day'))) == $value['deptime']){
					$time0 = false; // 已超过五点
				}else{
					$time0 = true; // 正常使用
				}
				// 发出时段在 08:00 - 12:00 并且 查询时间大于发车时间42小时才可以看到班次 最晚提货时间提前 18 小时
				$time1 = true;
				if($value['timestrat'] >= '08:00' && $value['timestrat'] <= '12:00' && $new > ($value['deptime']-42*60*60) ){
					$time1 = false;
					
				}
				// 发出时段在 13:00 - 15:00 并且 查询时间大于发车时间24小时才可以看到班次 最晚提货时间提前 7 小时
				$time2 = true;
				if($value['timestrat'] >= '13:00' && $value['timestrat'] <= '17:00' && $new > ($value['deptime']-24*60*60) ){
					$time2 = false;
				}
				// 发出时段在 18:00 - 22:00 并且 查询时间大于发车时间27小时才可以看到班次 最晚提货时间提前 7 小时
				$time3 = true;
				if($value['timestrat'] >= '18:00' && $value['timestrat'] <= '22:00' && $new > ($value['deptime']-27*60*60) ){
					$time3 = false;
				}
				// 所有状态均可用才显示班次
				if($time0 && $time1 && $time2 && $time3){
					$list[$key]['time_status'] = '2';
				}else{
					$list[$key]['time_status'] = '1';
				}
			} // endif
			
		}
		if(!empty($list)){
			return json(['code'=>'1001','message'=>'查询成功','data'=>$list]);
		}else{
			return json(['code'=>'1002','message'=>'请选择7天内发车的班次!']);
		}	
	}

	/**
	 * 干线班次详情
	 * @auther: 李渊
	 * @date: 2018.10.10
	 * @param  [String] [token] 	[用户令牌]
	 * @param  [Int] 	[slid] 		[班次队列id]
	 * @param  [Int] 	[start_id] 	[起点城市id]
	 * @param  [Int] 	[end_id] 	[终点城市id]
	 * @return [type] [description]
	 */
	public function shift_detail(){
		$token      = input("token");  		// 令牌
		$slid  		= input("slid");      	// 班次队列ID
		$start_id   = input("start_id");  	// 起点城市ID
		$end_id     = input("end_id"); 		// 终点城市ID
		// 验证参数是否完整
	    if(empty($token) || empty($slid)  || empty($start_id)  || empty($end_id)){
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
		$condition['g.slid'] = $slid;
		//$condition['g.status'] = "1";  //状态（1进行中2已完成）
		$result = Db::table("ct_shift")
		->alias('s')
		->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
		->join('__ALREADY_CITY__ al','al.city_id = s.linecityid')
		->field('s.companyid,s.companyid,s.shiftstate,s.pmoney,s.smoney,s.weekday,s.trunkaging,s.shiftnumber,
			s.price,s.selfdeliverydeadline,s.timestrat,s.discount,al.start_id,g.*')
		->where($condition)
		->find();
		//print_r($result);exit();
		$array = array();
		$result['send_endtime'] = mktime(0,0,0,date('m'),date('d')+5,date('Y'));;
		if ($result['shiftstate'] =='1') {
			$company = Db::table('ct_company')->where('cid',$result['companyid'])->find();
			//周边提货区域
			$pickround = json_decode($company['pickround'],TRUE);
			 if (!empty($pickround)) {
	            foreach ($pickround as $value) {
	                foreach ($value as $k => $val) {
	                    if ($k == $start_id) {
	                        foreach ($val as $ke => $v) {
	                            $array[$ke] = detailadd($v['cityid'],'','').detailadd($v['areaid'],'','');
	                        }
	                    }// end if
	                } //end foreach 
	            }//end foreach 
	        }// end if
			//提货费用 

			$tiwhere["companyid"] = $result['companyid'];
			$tiwhere["province"] =$start_id;
			$tiwhere["type"] = '1';  //1提2配
			$ti = Db::table("ct_tpprice")->where($tiwhere)->find();
			$send_car_time = date('Y-m-d',$result['deptime']).' '.$result['timestrat'].':00';
			
			$result['send_endtime'] = strtotime($send_car_time);
			// 发出时段在 08:00 - 12:00 并且 查询时间大于发车时间42小时才可以看到班次 最晚提货时间提前 18 小时
			if($result['timestrat'] >= '08:00' && $result['timestrat'] <= '12:00'){
				$result['send_endtime'] = $result['send_endtime']-18*60*60;
			}
			// 发出时段在 08:00 - 12:00 并且 查询时间大于发车时间42小时才可以看到班次 最晚提货时间提前 7 小时
			if($result['timestrat'] >= '13:00' && $result['timestrat'] <= '17:00'){
				$result['send_endtime'] = $result['send_endtime']-7*60*60;
			}
			// 发出时段在 08:00 - 12:00 并且 查询时间大于发车时间42小时才可以看到班次 最晚提货时间提前 7 小时
			if($result['timestrat'] >= '18:00' && $result['timestrat'] <= '22:00'){
				$result['send_endtime'] = $result['send_endtime']-7*60*60;
			} 
		}//endif shiftstate
		
		// 返回干线折扣后价格每公斤多少元
		$result['discount_price'] = round($result['price']*$result['discount'],2);
		// 起点城市
		$search_start = Db::table('ct_district')->where('id',$start_id)->find();
		$result['start_city'] = $search_start['name'];
		// 终点城市
		$search_end = Db::table('ct_district')->where('id',$end_id )->find();
		$result['end_city'] = $search_end['name'];
		// 周边城市
		$result['round_city'] = $array;
		return json(['code'=>'1001','message'=>'查询成功','data'=>$result]);
	}


	//零担计费： 提、干、配
	public function calculation(){

		$token = input("token");  //令牌
		$sid   = input("sid");  //班次ID
		$total_weight  = input("total_weight");  //总重量
		$total_volume  = input("total_volume");  //总体积
		$ti_num   = input("ti_num");  //提货门点个数
		$pei_num   = input("pei_num");  //配送门点个数
		$taddress = json_decode(input("taddress"));  //提货地址 [["tareaid":"111","taddressstr":"dfds"]]		
	    if(empty($token) || empty($sid) || empty($total_weight)  || empty($total_volume)){
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

		//班次信息
		$shift_mes = Db::table("ct_shift")
			->alias("s")
			->join("__ALREADY_CITY__ a","s.linecityid = a.city_id")
			->field("s.companyid,s.eprice,s.price,s.lowprice,a.start_id,a.end_id,s.shiftstate,s.pmoney,s.smoney,s.discount")
			->where('s.sid',$sid)
			->find();
		$ti_fel = $shift_mes['pmoney'];
		$gan_fel = '0';
		$pei_fel = $shift_mes['smoney'];
		if ($shift_mes['shiftstate'] == '1') {
			//提货基准价信息
			$tiwhere["companyid"] = $shift_mes['companyid'];
			$tiwhere["province"] =$shift_mes['start_id'];
			$tiwhere["type"] = '1';  //1提2配
			$ti = Db::table("ct_tpprice")->where($tiwhere)->find();
			if(!empty($ti)){
				$ti_jizhun_price = $ti['price'];
				$ti_feilv_price = $ti['rate'];
			}else{
				$ti_jizhun_price = '400';
				$ti_feilv_price = '150';
			}
			if($total_weight >= 5000){
				$ti_fel = '0';
			}else{
				$ti_fel =  $ti_jizhun_price + ($ti_num -1) * $ti_feilv_price;
			}

			//干线费
			$shift_frr = Db::table("ct_shiftfree")->where('shiftid',$sid)->select();

			/*
			*  //重抛比 ： 2.5
			*  立方除以吨位
			*/
			$weight_dun = ($total_volume*1000)/$total_weight;
			$throw_ratio =sprintf("%.1f",$weight_dun);
			if($throw_ratio < 2.5){ //重货
				$scale = 5;
				foreach ($shift_frr as $key => $value) {
					if($value['freeprice'] < $scale){
						$scale = $value['freeprice'];
					}
				 	if($total_weight>= $value['starweight'] && $total_weight<= $value['endweight']){
				 		$gan_fel = $value['freeprice'] * $total_weight;
				 	}
				}
				if(!isset($gan_fel)){
				 	$gan_fel = $scale == 5 ? $shift_mes['lowprice']: $scale* $total_weight;
				}
			}else{  //抛货
				$gan_fel = $total_volume * $shift_mes['eprice'];
			}
			if(!empty($shift_mes['lowprice'])){
				$lowprice = $shift_mes['lowprice'];
			}else{
				$lowprice = "500";
			}
			//干线费最低500元
			$gan_fel = ($gan_fel>$lowprice)? $gan_fel:$lowprice;
			$gan_fel = $shift_mes['discount']*$gan_fel;

			//配送费用
			$peiwhere["companyid"] = $shift_mes['companyid'];
			$peiwhere["province"] =$shift_mes['end_id'];
			$peiwhere["type"] = '2';  //1提2配
			$peisong = Db::table("ct_tpprice")->where($peiwhere)->find();

			if(!empty($peisong)){
				$pei_jizhun_price = $peisong['price'];
				$pei_feilv_price = $peisong['rate'];
			}else{
				$pei_jizhun_price = '400';
				$pei_feilv_price = '150';
			}
			$pei_fel = $pei_jizhun_price + ($pei_num -1) * $pei_feilv_price;
		}
		if($shift_mes['shiftstate'] == '2'){
            $gan_fel = $shift_mes['price']*$total_weight;
            if(!empty($shift_mes['lowprice'])){
                $lowprice = $shift_mes['lowprice'];
            }
            $gan_fel = ($gan_fel>$lowprice)? $gan_fel:$lowprice;
            $gan_fel = $shift_mes['discount']*$gan_fel;
        }
		//返回数据
		$result = array(
			'ti_fel'=>round($ti_fel),
			'gan_fel'=>round($gan_fel),
			'pei_fel'=>round($pei_fel)
			);
		return json(['code'=>'1001','message'=>'查询成功','data'=>$result]);	
	}
    //零担收货地址列表
	public  function shift_address(){
		$token   = input("token");  //令牌
		$city_id   = input("city_id");  //城市ID
	    if(empty($token) || empty($city_id)){
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

		//处理一级直辖市问题： 北京 天津 上海 重庆 
		if(in_array($city_id,array('1','2','9','22'))){
			$where_on = 'a.pro_id = d.id';
		}else{
			$where_on = 'a.city_id = d.id';
		}
		
		$condition['a.user_id'] = $user_id;
		$condition['d.id'] = $city_id;
		$result = Db::table("ct_addressuser")
			->alias('a')
			->join('__DISTRICT__ d',$where_on)
			->field("a.*,d.name as pro_name")
			->where($condition)
			->select();
		foreach ($result as $key => $value) {
			$city_result = Db::table("ct_district")->field('name')->where('id',$value['city_id'])->find();
			$result[$key]['city_name'] =$city_result['name'];
			$area_result = Db::table("ct_district")->field('name')->where('id',$value['area_id'])->find();
			$result[$key]['area_name'] =$area_result['name'];
		}	
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}


	}

  
	/***
	* 零担数据提交
	* 任何一门点超过5吨，直接分配给干线公司操作
	**/
	public  function bulk_add(){
		$token      = input("token");  //令牌
		$data['slogid'] = $slid  = input("slid");  //班次队列ID
		$data['totalweight'] = $total_weight  = input("total_weight");  //总重量
		$data['totalvolume'] = $total_volume  = input("total_volume");  //总体积
		$data['totalnumber'] = $total_number  = input("total_number");  //总件数

		$data['pickcost'] = $pickcost   = round(input("pickcost"));     //提货费用
		$data['linepice'] = $linepice   = round(input("linepice"));     //干线费用
		$data['delivecost'] = $delivecost   = round(input("delivecost")); //配送费用

        $addresst = input('taddress');
        $addressp = input('paddress');



		$data['picktime'] = $picktime   = input("picktime"); //预计提货时间
		$data['itemtype'] = $itemtype   = input("itemtype"); //物品类型
		$data['coldtype'] = $coldtype   = input("coldtype"); //冷藏类型类型
		$data['remark'] = input("remark");     //备注

        if (input('picktype')){
            $data['picktype'] = input('picktype');
        }else{
            $data['picktype'] = 1;
        }
        if (input('sendtype')){
            $data['sendtype'] = input('sendtype');
        }else{
            $data['sendtype'] = 1;
        }

        $data['picksite'] = input('picksite');
        $data['stime'] = input('stime');
        $data['sphone'] = input('sphone');
        $data['sendsite'] = input('sendsite');
        $data['dtime'] = input('dtime');
        $data['tphone'] = input('tphone');
        if ($addresst){
            $taddress = json_decode($addresst);  //提货地址 [["tareaid":"111","taddressstr":"dfds"]]

            foreach ($taddress as $key => $value) {
                $pickaddress[] = $this->object_to_array($value);
            }
            $data['pickaddress'] = json_encode($pickaddress);  //提货地址
        }else{
            $data['pickaddress'] ='';
        }
        if ($addressp){
            $paddress = json_decode($addressp);  //配货地址 [["pareaid":"111","paddressstr":"dfds","phone":"12345655","name":"2dfd"],["pareaid":"111","paddressstr":"dfds","phone":"12345655","name":"2dfd"]]

            foreach ($paddress as $key => $value) {
                $peiaddress[] = $this->object_to_array($value);
            }
            $data['sendaddress'] = json_encode($peiaddress);  //配送地址
        }else{
            $data['sendaddress'] ='';
        }

	    if(empty($token) || empty($slid) || empty($total_weight)  || empty($total_volume)  || empty($total_number)   || empty($itemtype) || empty($coldtype)){
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

		//查询当前班次的公司ID
		$shift_mes = Db::table("ct_shift")
					->alias("s")
					->join("__SHIFT_LOG__ g",'s.sid = g.shiftid')
					->field("s.companyid,g.shiftid,g.deptime,g.endtime,s.shiftstate")
					->where("g.slid",$slid)
					->find();

		$data['shiftid'] = $shift_mes['shiftid'];
		$data['starttime'] = $shift_mes['deptime'];
		$data['arrtime'] = $shift_mes['endtime'];
        $type = input('ztype');
        if($type == 1){
            $data['orderstate'] = 1;
            $data['paystate'] = 1;
        }else{
            $data['orderstate'] = 2;
            $data['paystate'] = 2;
        }
//		$data['paystate'] = $shift_mes['shiftstate']=='1' ? '1':'2';
//		$data['orderstate'] = $shift_mes['shiftstate']=='1' ? '1':'2';
		$data['userid'] = $user_id;
		$data['addtime'] = time();
		$data['ordernumber'] = $ordernumber= 'Z'.date('ymdHis').mt_rand('000','999'); //订单编号
		$result = Db::table("ct_order")->insert($data);
		$insert_id = Db::table("ct_order")->getLastInsID();
		$ti_where['type'] = '1';
		$ti_where['status'] = '1';
		$line_where['orderid'] = $insert_id;
		$line_where['status'] = '1';
		$pei_where['orderid'] = $insert_id;
		$pei_where['status'] = '1';
		if ($shift_mes['shiftstate'] == '1') {
			if($total_weight >= '5000'){
		    	//$ti_where['driverid'] = $shift_mes['companyid'];
		    	$ti_where['type'] = '2';  //接单人属性1散户(个体，提)2干线
		    	$ti_where['status'] = '2'; //接单状态1未接2已接3已完成
		    }
		    $line_where['status'] = '2';
		    $pei_where['status'] = '2';
		}
		if($result){
			    $ti_where['orderid'] = $insert_id;
			    $ti_where['tprice'] = $pickcost;
				//生成提、干、配订单
			  	Db::table("ct_pickorder")->insert($ti_where);
			  	Db::table("ct_lineorder")->insert($line_where);
			  	Db::table("ct_delorder")->insert($pei_where);

			  	$back_data['orderid'] = $insert_id;
			  	$back_data['ordernumber'] = $ordernumber;
			  	//用户提交表表单时候返回用户余额和公司信用额度
			  	$balance = $this->replay_user_money($user_id);
			  	//账户余额
			  	$back_data['money'] = $balance['money'];
			  	//公司
			  	$back_data['com_money'] = $balance['com_money'];
			return json(['code'=>'1001','message'=>'添加成功','data'=>$back_data]);
		}else{
			return json(['code'=>'1002','message'=>'添加失败']);

		}

	}

	/*
	*
	*评论功能
	*/
	public function addgrade(){
		$token = input('token');//令牌
		$data['shift_id'] = $shiftid = input('shiftid');  //班次ID
		$data['islike'] = $islike = input('islike');  //此次服务是否满意1、满意 2、一般 3、较差
		$data['content'] = input('content'); //评价内容
		$data['line_grade'] = input('lgrade');  //干线评分
		$data['server_grade'] = input('sgrade');  //此次服务评分
		$data['orderid'] = input('orderid');  //订单ID
		if (empty($token) || empty($shiftid)) {
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
		$data['user_id'] = $user_id;
		$data['addtime'] = time();
		$result = Db::table('ct_linegrade')->insert($data);
		if ($result) {
			return json(['code'=>'1001','message'=>'添加成功']);
		}else{
			return json(['code'=>'1002','message'=>'添加失败']);

		}
	}

	/*
	*评论干线列表
	*/

	public function gradelist(){
		$shiftid = input('shiftid');  //班次ID
		if (empty($shiftid)) {
			return json(['code'=>'1000','message'=>'参数错误']);
		}

		$result = Db::table('ct_linegrade')
					->alias('l')
					->join('ct_user u','u.uid=l.user_id')
					->field('l.*,u.phone')
					->where('shift_id',$shiftid)
					->paginate(10);
		$res_data = $result->toArray();
		if ($result) {
			return json(['code'=>'1001','message'=>'查询成功','data'=>$res_data['data']]);
		}else{
			return json(['code'=>'1002','message'=>'暂无数据']);

		}
	}

	
	
    
	
}
