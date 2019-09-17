<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型
//固定零担班次
class Fixedshift extends Base{


	/*
	*
	*专车起始城市列表
	*/
	public function scity(){
		$token = input('token'); //验证令牌
		$type = input('type'); // 1、起始城市 2、终点城市
		$start_id = input('start_id');  // 起始城市ID  非必填
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
		$user_mess = Db::table('ct_user')->where('uid',$user_id)->find();
		$sql = 'd.id = a.start_id';
		if ($type == '2') {
			$where['start_id'] = $start_id;
			$sql = 'd.id = a.end_id';
		}
		$where['f.companyid'] = $user_mess['lineclient'];

		$shift_mess = Db::table('ct_fixation_line')
					->alias('f')
					->join('__ALREADY_CITY__ a','a.city_id = f.lienid')
					->join('__DISTRICT__ d',$sql)
					->field('d.id  as value,d.name as text,f.id as shiftid,d.level,d.parent_id,f.doornum,f.line_percent,f.remark')
					->where($where)
					->select();
		$arr = array();
		$str = '';
		foreach ($shift_mess as $key => $value) {
			if ($value['level'] == '3') {
				$city_str = Db::table('ct_district')->where('id',$value['parent_id'])->find();
				$str=$city_str['name'];
			}
			$arr[$key]['value'] = $value['value'];
			$arr[$key]['text'] = $str.$value['text'];
			$arr[$key]['shiftid'] = $value['shiftid'];
			$arr[$key]['doornum'] = $value['doornum'];
			$arr[$key]['remark'] = $value['remark'];
			$arr[$key]['line_percent'] = $value['line_percent'];
		}		
		if (empty($shift_mess)) {
			return json(['code'=>'1001','message'=>'非定制客户']);
		}else{
			$result =$this->assoc_unique($arr,'text'); //数组，键值
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
	}
	

	/*
	*
	*插入专线订单
	*/

	public function special_add(){
		$token = input('token'); //验证令牌
		$data['shiftid'] = $shiftid = input('shiftid');  //班次ID
		$data['picktime'] = $picktime = input('picktime');  //提货时间
		$data['remark'] = $remark = input('remark');  //备注
		$data['doornum'] = $doornum = input('doornum');  //门店数
		$data['ordernumber'] = $ordernumber= 'G'.date('ymdhis').mt_rand('000','999'); //订单编号
		if (empty($token) || empty($picktime) || empty($shiftid) || empty($doornum)) {
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
		$user_mess = Db::table('ct_user')->where('uid',$user_id)->find();
		$shift_mess = Db::table('ct_fixation_line')
						->alias('f')
						->join('__ALREADY_CITY__ a','a.city_id = f.lienid')
						->field('f.*,a.start_id,a.end_id')
						->where('id',$shiftid)
						->find();
		$city_start = '';
		$city_end = '';
		$startarea = Db::table('ct_district')->where('id',$shift_mess['start_id'])->find();
		$endarea = Db::table('ct_district')->where('id',$shift_mess['end_id'])->find();
		if ($startarea['level'] =='3') {
			$startcity = DB::table('ct_district')->where('id',$startarea['parent_id'])->find();
			$city_start = $startcity['name'];
		}
		if ($endarea['level'] =='3') {
			$startcity = DB::table('ct_district')->where('id',$endarea['parent_id'])->find();
			$city_end = $startcity['name'];
		}
		//温度
		$data['cold_number'] = $shift_mess['temperature'];
		//温度类型
		$data['good_type'] = $shift_mess['goodname'];
		$data['userid'] = $user_id;
		//承运商价格
		$price = 0;
		//客户价格
		$doorprice = 0;
		//车辆数
		$carnum = 0;
		//超门店个数
		$moredoor = 0;
		if ($doornum > $shift_mess['appoint_door']) {  //下单门店数大于合同约定门店数
			$carnum = (int)$doornum/(int)$shift_mess['appoint_door'];
			$moredoor = $doornum - intval($carnum)*$shift_mess['appoint_door'];
		}else{
			$carnum = 1;
			$moredoor = 0;
		}
		//客户运费 = 车辆数*基础运费+超配门店数*超配门店数 
		$doorprice = intval($carnum)*$shift_mess['carprice'] + $shift_mess['moredoor']*$moredoor;
		//承运商价格 = 车辆数*承运商基础运费+承运商超配门店数*超配门店数 
		$price = intval($carnum)*$shift_mess['carr_price'] + $shift_mess['carr_moredoor']*$moredoor;
		//echo $price;exit();
		
		$data['price'] = $price;
		$data['doorprice'] = $doorprice;
		$data['totalprice'] = $doorprice;
		$data['addtime'] = time();
		$result = Db::table('ct_shift_order')->insert($data);
		$insert_id = Db::table("ct_shift_order")->getLastInsID();
		if ($result){
			$back_data['shiftid'] = $shiftid;  //班次ID
			$back_data['orderid'] = $insert_id;  //订单ID
		  	$back_data['remark'] = $remark;  //备注
		  	$back_data['doornum'] = $doornum; //门店数
		  	$back_data['picktime'] = $picktime; //提货时间
		  	$back_data['price'] = $doorprice;  //车辆数价格
		  	//$back_data['oneline'] = $shift_mess['oneline'];  // 没超一个门店价格
		  	$back_data['startcity'] = $city_start.$startarea['name']; //起点城市
		  	$back_data['endcity'] = $city_end.$endarea['name'];		  	 //终点城市 
		  	//用户余额信息 
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
	*确定表单提交
	*/

	public function special_affirm(){
		$token = input('token');
		$orderid = input('orderid');
		if (empty($token)  || empty($orderid)) {
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);
		if ($check_result['status'] =='1') {
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif ($check_result['status'] =='2') {
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		$sorder_mess = Db::table('ct_shift_order')
						->alias('o')
						->join('__FIXATION_LINE__ f','f.id=o.shiftid')
						->join('__COMPANY__ c','c.cid=f.companyid')
						->join('__ALREADY_CITY__ a','a.city_id=f.lienid')
						->field('o.s_oid,o.picktime,o.totalprice,f.carrierid,f.trans_mess,f.paddress,f.ptime,a.start_id,a.end_id,c.cid,c.money')
						->where('s_oid',$orderid)
						->find();
		$list_phone = '';
		$phone_driver ='';
		$phone_leader ='';
		if (!empty($sorder_mess['trans_mess'])) {
			$arr_mess = json_decode($sorder_mess['trans_mess'],true);
			if (!empty($arr_mess)) {
				foreach ($arr_mess as $key => $value) {
					$driver = Db::table('ct_driver')->where('drivid',$value['driverid'])->find();
					$list_phone .= $driver['mobile'].',';
				}
			}
			
			$phone_driver = rtrim ($list_phone,',');
		}
		if(!empty($sorder_mess['carrierid'])){
			$driver_leader = Db::table('ct_driver')->field('mobile')->where(array('companyid'=>$sorder_mess['carrierid'],'type'=>3))->find();
			$phone_leader = $driver_leader['mobile'];
		}
		$str ='';
		if ($phone_driver!='') {
			$str =',';
		}
		$phone_all = $phone_driver.$str.$phone_leader;
		//echo $phone_all;exit();
		$saddress = '';
		if($sorder_mess['paddress'] !='') {
			$address = json_decode($sorder_mess['paddress'],true);
           foreach ($address as $key => $val) {
            $saddress .=$val.'/';
          }
        }
        $city_start = '';
		$city_end = '';
		$startarea = Db::table('ct_district')->where('id',$sorder_mess['start_id'])->find();
		$endarea = Db::table('ct_district')->where('id',$sorder_mess['end_id'])->find();
		if ($startarea['level'] =='3') {
			$startcity = DB::table('ct_district')->where('id',$startarea['parent_id'])->find();
			$city_start = $startcity['name'];
		}
		if ($endarea['level'] =='3') {
			$startcity = DB::table('ct_district')->where('id',$endarea['parent_id'])->find();
			$city_end = $startcity['name'];
		}
		$startcity = $city_start.$startarea['name']; //起点城市
		  	$endcity = $city_end.$endarea['name'];		  	 //终点城市  
		$ptime = $sorder_mess['ptime'];
		$ttime = strtotime($sorder_mess['picktime'] . "-24 hours");
		$content = "尊敬的用户：您有订单".$sorder_mess['picktime']."从:".$startcity." 发往 ".$endcity ."的货物！提货地址为".$saddress."。请您及时处理";
		if ($ttime < time()) {
			$data['send_mess'] = 2;
		}
		send_sms_class($phone_all,$content);
		$data['affirm'] = 2;
		$data['pay_type'] = 1;
		$shen_money = $sorder_mess['money']-$sorder_mess['totalprice'];
		$result = Db::table('ct_shift_order')->where('s_oid',$orderid)->update($data);
		//插入余额使用记录和更新余额
		$order_content  = "定制线路下单信用额度扣款";
		$this->record('',$user_id,$sorder_mess['cid'],$sorder_mess['totalprice'],$shen_money,$order_content,'2','',$orderid,'2');
		if ($result) {
			return json(['code'=>'1001','message'=>'订单确认成功']);
		}else{
			return json(['code'=>'1002','message'=>'订单确认失败']);
		}
	}
	
	
}