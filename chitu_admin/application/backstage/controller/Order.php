<?php
namespace app\backstage\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Session;
/**
  * Author: 
  */
class Order extends Base
{
	//获取用户ID
	public function ccarid(){
		return Session::get('carrier_id','carrier_mes');
	}
	//提货订单
	public function torder(){
		//update 2017-7-20 laochen
		$carrid['drivid'] = $this->ccarid();

		$company_id = Db::table('ct_driver')->where($carrid)->find();
		$search = input('search');
	    $stime = input('starttime');
	    $etime = input('endtime');
	    $pageParam    = ['query' =>[]];
	    if(!empty($search)){
	       $condition['o.ordernumber'] = ['like','%'.$search.'%'];
	       $pageParam['query']['search'] = $search;
	    }
	    if(!empty($stime) && !empty($etime)) {
	          $endtime = strtotime(trim($etime).'23:59:59');
	          $starttime = strtotime(trim($stime).'00:00:00');
	          $condition['o.addtime'] = array(array('EGT',$starttime),array('ELT', $endtime));
	          $pageParam['query']['starttime'] =$stime;
	          $pageParam['query']['endtime'] = $etime;
	      }
	      $condition['o.paystate'] = 2; 
	      if ($company_id['type'] == 3 ) {
				$userid = Db::table('ct_driver')->where('companyid',$company_id['companyid'])->select();
		 		$uid = '';
		 		foreach ($userid as $key => $value) {
		 			$uid .= $value['drivid'].',';
		 		}
		 		$uid =  substr($uid,0,strlen($uid)-1);
		 		$comid = $company_id['companyid'];
				$result = Db::table("ct_order")
					->alias('o')
					->join("__PICKORDER__ p",'o.oid = p.orderid')
					->join('ct_shift_log g','g.slid=o.slogid')
					->join('ct_shift s','s.sid=g.shiftid')
					->join('ct_driver dr','dr.drivid=p.driverid','LEFT')
					->field('o.oid,o.addtime,o.ordernumber,o.pickcost,o.totalnumber,o.picktime,
						o.totalweight,o.totalvolume,p.type,p.status,p.picid,o.slogid,p.systemorders,
						p.drivername,p.driverphone,p.carlicense,p.tprice,p.orderid,p.receivetime')
				    ->order('o.addtime desc')
				    ->where($condition)
				    ->where(function($query) use($uid){
				        $query->where(array('p.driverid'=>array('in',$uid)));
				       // $query->whereOr(array('s.companyid'=>$comid));
				       // $query->whereOr(array('p.systemorders'=>1));
				    })
				    ->paginate(10);
				   //echo  Db::table("ct_order")->getLastSql();exit();
				
			}else{
				$condition['p.driverid'] =  $this->ccarid();
				$condition2['p.sallotid'] =  $this->ccarid();
				$result = Db::table("ct_order")
					->alias('o')
					->join("__PICKORDER__ p",'o.oid = p.orderid')
					->join('ct_shift_log g','g.slid=o.slogid')
					->join('ct_shift s','s.sid=g.shiftid')
					->field('o.oid,o.addtime,o.ordernumber,o.pickcost,o.totalnumber,o.picktime,
						o.totalweight,o.totalvolume,p.type,p.status,p.picid,o.slogid,p.systemorders,
						p.drivername,p.driverphone,p.carlicense,p.tprice,p.orderid,p.receivetime')					
					->order('o.addtime desc')
					->where($condition)
					->whereOr($condition2)
					->paginate(10);
					
			}
			$list_mes = $result->toArray();
			$array = $list_mes['data'];

		
		$page = $result->render();
		$this->assign('list',$array);
		$this->assign('page',$page);
		return view('order/torder');
	}
	//提货单详情
	public function torderdetails(){
		$orderid_where['a.oid'] = input('id');
		$type_where['d.type'] = 2;
		$array = array();
		$result = Db::field('a.ordernumber,a.addtime,a.userid,a.picktime,a.pickaddress,a.totalnumber,al.start_id,a.totalweight,a.totalvolume,d.realname,d.mobile,d.type,c.beginprovinceid,c.begincityid,c.beginareaid,c.beginaddress')
			->table('ct_order')
			->alias('a')
			->join('ct_shift_log b','b.slid=a.slogid')
			->join('ct_shift c','c.sid=b.shiftid')
			->join('ct_driver d','d.companyid=c.companyid')
			->join('ct_already_city al','al.city_id = c.linecityid')
			->where($type_where)
			->where($orderid_where)
			->find();
		//获取托运人电话信息
		$checked = Db::field('realname,phone')
			->table('ct_user')
			->where('uid',$result['userid'])
			->find();
		$city = Db::table('ct_district')->field('name')->where('id',$result['start_id'])->find();
		$price_data = Db::field('b.*')
			->table('ct_order')
			->alias('a')
			->join('ct_pickorder b','b.orderid=a.oid')
			->where($orderid_where)
			->find();
		$array['ordernumber'] = $result['ordernumber'];
		$array['drivername'] = $price_data['drivername'];
		$array['driverphone'] = $price_data['driverphone'];
		$array['carlicense'] = $price_data['carlicense'];
		$array['id'] = $price_data['picid'];
		$array['username'] = $checked['realname'];
		$array['telephone'] = $checked['phone'];
		 //提货地址
		   $taddress = json_decode($result['pickaddress'],TRUE);
		   foreach ($taddress as $key => $value) {
		      $array['taddress'][$key] =  $city['name'].$value['taddressstr'];
		    } 
		$array['addtime'] = $result['addtime'];
		$array['picktime'] = $result['picktime'];
		$array['weight'] = $result['totalweight'];
		$array['volume'] = $result['totalvolume'];
		$array['number'] = $result['totalnumber'];
		$array['name'] = $result['realname'];
		$array['phone'] = $result['mobile'];
		$address_p = $this->completeAddress($result['beginprovinceid'],$result['begincityid'],$result['beginareaid']);
		$array['psaddress'] = $address_p.$result['beginaddress'];
		$array['receivetime'] = $price_data['receivetime'];
		$array['tprice'] = $price_data['tprice'];
		$this->assign('list',$array);
		return view('order/torderdetails');
	}
	//干线订单以及配送订单
	public function gorder(){
		$company_where['b.drivid'] = $this->ccarid();
		$company_data = Db::field('a.cid')
			->table('ct_company')
			->alias('a')
			->join('ct_driver b','b.companyid=a.cid')
			->where($company_where)
			->find();
		$result_where['d.cid'] = $company_data['cid'];
		$result_where['a.paystate'] = '2';
		//$search = input('search');
		//$result_where['a.ordernumber'] = ['like','%'.$search.'%'];
		$search = input('search');
	    $stime = input('starttime');
	    $etime = input('endtime');
	    $pageParam    = ['query' =>[]];
	    if(!empty($search)){
	       $result_where['a.ordernumber'] = ['like','%'.$search.'%'];
	       $pageParam['query']['search'] = $search;
	    }
	    if(!empty($stime) && !empty($etime)) {
	          $endtime = strtotime(trim($etime).'23:59:59');
	          $starttime = strtotime(trim($stime).'00:00:00');
	          $result_where['a.addtime'] = array(array('EGT',$starttime),array('ELT', $endtime));
	          $pageParam['query']['starttime'] =$stime;
	          $pageParam['query']['endtime'] = $etime;
	      }
		$result = Db::field('a.*,b.deptime,b.endtime,c.shiftnumber,c.linecityid,c.timestrat,c.timeend,c.arrivetimestart,c.arrivetimeend,d.cid,e.*')
			->table('ct_order')
			->alias('a')
			->join('ct_shift_log b','b.slid=a.slogid')
			->join('ct_shift c','c.sid=b.shiftid')
			->join('ct_company d','d.cid=c.companyid')
			->join('ct_already_city e','e.city_id=c.linecityid')
			->where($result_where)
			->order('a.oid','desc')
			->paginate(10,false, $pageParam);
		foreach ($result as $key => $value) {
			$name = $this->completeAddress($value['start_id'],'','');
			$name1 = $this->completeAddress($value['end_id'],'','');
			$driver_where['oid'] = $value['oid']; 
			$driver = Db::field('c.realname,b.affirm')
				->table('ct_order')
				->alias('a')
				->join('ct_lineorder b','b.orderid=a.oid')
				->join('ct_driver c','c.drivid=b.driverid')
				->where($driver_where)
				->find();
			$array = $result[$key];
			$tprice_where['orderid'] = $value['oid'];
			$tprice = Db::table('ct_pickorder')->where($tprice_where)->find();
			//判断提货单是不是自己公司承接
			if ($tprice['type'] != 2) {
				$array['pickcost'] = '0';
			}
			$array['startcity'] = $name;
			if ($driver['affirm']==3) {
				$array['driver'] = '系统派单';
			}else{
				$array['driver'] = '手动确认';
			}
			
			$array['endcity'] = $name1;
			$result[$key] = $array;
		}
		$this->assign('list',$result);
		return view('order/gorder');
	}
	//干线订单详情
	public function gorderdetails(){
		$id = input('id');
		$torder_where['orderid'] = $id;
		$result = Db::field('a.*,b.deptime,b.endtime,c.shiftnumber,c.linecityid,c.timestrat,c.timeend,c.arrivetimestart,c.arrivetimeend,c.companyid,d.*')
			->table('ct_order')
			->alias('a')
			->join('ct_shift_log b','b.slid=a.slogid')
			->join('ct_shift c','c.sid=b.shiftid')
			->join('ct_already_city d','d.city_id=c.linecityid')
			->where('a.oid',$id)
			->find();
		$torder = Db::table('ct_pickorder')->where($torder_where)->find();
		$array = $result;
		$startcity = $this->completeAddress($result['start_id'],'','');
		$array['startcity'] = $startcity;
		$array['endcity'] = $this->completeAddress($result['end_id'],'','');
		//判断提货单是不是自己公司承接
		
		if ($torder['type'] == '2') {
			$array['torder'] = '1';
			//获取托运人电话信息
			$checked = Db::field('realname,phone')
				->table('ct_user')
				->where('uid',$result['userid'])
				->find();	
			$array['username'] = $checked['realname'];
			$array['telephone'] = $checked['phone'];
			  //提货地址
		   $taddress = json_decode($result['pickaddress'],TRUE);
		   foreach ($taddress as $key => $value) {
		      $array['taddress'][$key] =  $startcity.$value['taddressstr'];
		    } 
		}else{
			$array['torder'] = '2';
			$driver_where['drivid'] = $torder['driverid'];
			$driver = Db::table('ct_driver')->where($driver_where)->find();
			$array['username'] = $driver['realname'];
			$array['telephone'] = $driver['mobile'];
			$array['pickcost'] = '0';
		}
		//获取收货人姓名，电话
		$receive = Db::field('b.mobile,b.realname')
					->table('ct_company')
					->alias('a')
					->join('ct_driver b','b.companyid=a.cid')
					->where(array('a.cid'=>$result['companyid'],'b.type'=>2))
					->find();
		$array['receivename'] = $receive['realname'];
		$array['receivephone'] = $receive['mobile'];
		//配送地址
	    $paddress = json_decode($result['sendaddress'],TRUE);
	    if (!empty($paddress)) {
	      foreach ($paddress as $key => $value) {
	        
	        $array['paddress'][$key]['address'] =  $value['paddressstr'];
	        $array['paddress'][$key]['contact'] =  $value['name']."/".$value['phone'];
	      } 
	    }
	    $array['picktime'] = $result['picktime'];
		$array['addtime'] = $result['addtime'];
		$array['weight'] = $result['totalweight'];
		$array['volume'] = $result['totalvolume'];
		$array['number'] = $result['totalnumber'];
		$this->assign('list',$array);
		// echo '<pre>';
		// print_r($distribution);
		return view('order/gorderdetails');
	}

	//接单整车订单列表
	public function cityvehicle(){
		$search = input('search');
	    $stime = input('starttime');
	    $etime = input('endtime');
	    $pageParam    = ['query' =>[]];
	   $arr=array();
	  
	    if(!empty($search)){
	       $condition['u.ordernumber'] = ['like','%'.$search.'%'];
	       $pageParam['query']['search'] = $search;
	    }
	    if(!empty($stime) && !empty($etime)) {
	          $endtime = strtotime(trim($etime).'23:59:59');
	          $starttime = strtotime(trim($stime).'00:00:00');
	          $condition['u.addtime'] = array(array('EGT',$starttime),array('ELT', $endtime));
	          $pageParam['query']['starttime'] =$stime;
	          $pageParam['query']['endtime'] = $etime;
	      }
	      $drivid = Db::table('ct_driver')->where('drivid',$this->ccarid())->find();
	      $condition['paystate'] = 2;
	      $driver_id = $this->ccarid();
			if ($drivid['type'] == '3' ) {
				$userid = Db::table('ct_driver')->where('companyid',$drivid['companyid'])->select();
		 		$uid = '';
		 		foreach ($userid as $key => $value) {
		 			$uid .= $value['drivid'].',';
		 		}
		 		$uid =  substr($uid,0,strlen($uid)-1);
		 		$condition['u.carriersid'] = array('in',$uid);
		 		$driver_id=['NEQ',''];

			}

			$result = Db::table("ct_userorder")
					->alias('u')
					->join('__CARTYPE__ c',"u.carid = c.car_id")
					->join('__DRIVER__ d',"d.drivid = u.carriersid")
					->field('u.uoid,u.ordernumber,u.addtime,u.price,u.paystate,u.type,u.arrtime,u.loaddate,u.orderstate,u.startcity,u.endcity,c.carparame,u.drivername,u.carlicense,d.realname')
					->order('u.addtime desc')
					->where($condition)
					->where(function($query) use($driver_id){
				        $query->where(array('u.driverid'=>$driver_id));
				        $query->whereOr(array('u.carriersid'=>$driver_id));
				    })
					->paginate(10,false, $pageParam);
	      
 		
		$res = $result->toArray();
		 $page = $result->render();
		foreach ($res['data'] as $key => $value) {
			$arr[] = $value;
			$scity = $this->completeAddress('',$value['startcity'],'');
			$ecity = $this->completeAddress('',$value['endcity'],'');
			$arr[$key]['loaddate'] = floor($value['loaddate']/1000);
			$arr[$key]['start_city'] = $scity;
			$arr[$key]['end_city'] = $ecity;
		}
		$this->assign('list',$arr);
		$this->assign('page',$page);
		return view('order/cityvehicle');
	}

	
	//货主整车订单列表
	public function usercityvehicle(){
		$search = input('search');
	    $stime = input('starttime');
	    $etime = input('endtime');
	    $pageParam    = ['query' =>[]];
	   $arr=array();
	  
	    if(!empty($search)){
	       $result_where['a.ordernumber'] = ['like','%'.$search.'%'];
	       $pageParam['query']['search'] = $search;
	    }
	    if(!empty($stime) && !empty($etime)) {
	          $endtime = strtotime(trim($etime).'23:59:59');
	          $starttime = strtotime(trim($stime).'00:00:00');
	          $result_where['a.addtime'] = array(array('EGT',$starttime),array('ELT', $endtime));
	          $pageParam['query']['starttime'] =$stime;
	          $pageParam['query']['endtime'] = $etime;
	      }
 		
 		$find = Db::table('ct_user')->field('lineclient')->where('uid',$this->ccarid())->find();
 		$userid = Db::field('b.uid')
 			->table('ct_user')
 			->where('lineclient',$find['lineclient'])
 			->select();
 		$uid = '';
 		foreach ($userid as $key => $value) {
 			$uid .= $value['uid'].',';
 		}
 		$uid =  substr($uid,0,strlen($uid)-1);
 		$result_where['userid'] = ['in',$uid];
		$result = Db::field('a.*,b.realname')
		->table('ct_userorder')
		->alias('a')
		->join('ct_user b','b.uid=a.userid')
		->where($result_where)
		->order('a.addtime','desc')
		->paginate(10,false, $pageParam);

		$res = $result->toArray();
		 $page = $result->render();
		foreach ($res['data'] as $key => $value) {
			$arr[] = $value;
			$scity = $this->completeAddress('',$value['startcity'],'');
			$ecity = $this->completeAddress('',$value['endcity'],'');
			$arr[$key]['start_city'] = $scity;
			$arr[$key]['end_city'] = $ecity;
			$arr[$key]['loaddate'] = $value['loaddate']/1000;
		}
		$this->assign('list',$arr);
		$this->assign('page',$page);
		
		return view('order/usercityvehicle');
	}
	
	//货主整车订单详情
	public function cityvehicledetails(){
		$usertype = Session::get('carrier_usertype','carrier_mes');
		$comid = Db::table('ct_driver')->where('drivid',$this->ccarid())->find();
		 $id = input('id');
      $array = Db::field('a.*,car.allweight,car.allvolume,car.dimensions,d.realname as carrname,d.mobile as carrphone,user.phone,user.realname as username')
              ->table('ct_userorder')
              ->alias('a')
              
              ->join('ct_driver d','d.drivid=a.carriersid','LEFT')
              ->join('ct_cartype car','car.car_id = a.carid')
              ->join('ct_user user','user.uid = a.userid')
              ->where('a.uoid',$id)
              ->find();

     $array['pickaddress'] = json_decode($array['pickaddress'],TRUE);
    $sendaddress = json_decode($array['sendaddress'],TRUE);
    
      $array['receipts'] = json_decode($array['receipts'],TRUE);
     
     
    if ($usertype == 'user') {
     	$array['prices'] = $array['price'];
     }else{
     	$array['prices'] = sprintf('%.2f',driver_carload($array['price']));
     }
     $array['sendaddress'] = $sendaddress;
      $array['arrtime'] = round($array['arrtime']/1000);     
      $array['loaddate'] = round($array['loaddate']/1000);
      $array['startcity'] = $this->completeAddress('',$array['startcity'],'');
      $array['endcity'] = $this->completeAddress('',$array['endcity'],'');
      $this->assign('list',$array);
		return view('order/cityvehicledetails');
	}
	
	
	//司机：市内配送订单列表
	public function citydistribution(){
		$search = input('search');
	    $stime = input('starttime');
	    $etime = input('endtime');
	    $pageParam    = ['query' =>[]];
	   $arr=array();
	  	$where['paystate'] = 2;
	    if(!empty($search)){
	       $where['a.orderid'] = ['like','%'.$search.'%'];
	       $pageParam['query']['search'] = $search;
	    }
	    if(!empty($stime) && !empty($etime)) {
	          $endtime = strtotime(trim($etime).'23:59:59');
	          $starttime = strtotime(trim($stime).'00:00:00');
	          $where['a.addtime'] = array(array('EGT',$starttime),array('ELT', $endtime));
	          $pageParam['query']['starttime'] =$stime;
	          $pageParam['query']['endtime'] = $etime;
	      }
		$carrid['drivid'] = $this->ccarid();
		$driver_id = $this->ccarid();
		$company_id = Db::table('ct_driver')->where($carrid)->find();
		if ($company_id['type'] == '3' ) {
			$userid = Db::table('ct_driver')->field('drivid')->where('companyid',$company_id['companyid'])->select();
	 		$uid = '';
	 		foreach ($userid as $key => $value) {
	 			$uid .= $value['drivid'].',';
	 		}
	 		$uid =  substr($uid,0,strlen($uid)-1);
	 		$where['driverid'] = array('in',$uid);
	 		$driver_id =['NEQ',''];
		}
		$result = Db::table('ct_city_order')
			->alias('o')
			->join('ct_rout_order r','r.rid = o.rout_id')
			->join('ct_cartype c','c.car_id = o.carid')
			->join('ct_driver b','b.drivid = r.driverid')
			->field('o.id,o.orderid,o.addtime,o.data_type,o.paymoney,o.pytype,o.state,o.cold_type,o.ordertype,r.driverid,o.pytype,r.drivername,r.take_time,r.carlicense,c.carparame,b.realname,b.mobile')
			->where($where)
			->where(function($query) use($driver_id){
		        $query->where(array('r.driverid'=>$driver_id));
		        $query->whereOr(array('r.allotid'=>$driver_id));
		    })
			->paginate(10,false,$pageParam);
		$res = $result->toArray();
		$array = $res['data'];
		$page = $result->render();
		$this->assign('page',$page);
		$this->assign('list',$array);
		
		return view('order/citydistribution');


	}


	//司机：市内配送订单详情
	public function citydistributiondetails(){
		$id = input('id');
		$array = Db::field('a.*,b.realname,b.mobile,r.take_time,r.drivername,r.driverphone,r.carlicense,r.rid')
					->table('ct_city_order')
					->alias('a')
					->join('ct_rout_order r','r.rid = a.rout_id')
					->join('ct_driver b','b.drivid=r.driverid')
					->where('a.id',$id)
					->find();
		
		foreach ($array as $key => $value) {
			
			if (!empty($value['saddress'] )) {
				$array[$key]['saddress'] = json_decode($value['saddress'],TRUE);
			}
			
			if (!empty($value['eaddress'] )) {
				$array[$key]['eaddress'] = json_decode($value['eaddress'],TRUE);
			}
			
			
		}
		$this->assign('array',$array);
		return view('order/citydistributiondetails');



	}
	//项目客户：市内配送订单列表
	public function usercity(){
		$search = input('search');
	    $stime = input('starttime');
	    $etime = input('endtime');
	    $pageParam    = ['query' =>[]];
	   $arr=array();
	  
	    if(!empty($search)){
	       $result_where['a.ordernumber'] = ['like','%'.$search.'%'];
	       $pageParam['query']['search'] = $search;
	    }
	    if(!empty($stime) && !empty($etime)) {
	          $endtime = strtotime(trim($etime).'23:59:59');
	          $starttime = strtotime(trim($stime).'00:00:00');
	          $result_where['a.addtime'] = array(array('EGT',$starttime),array('ELT', $endtime));
	          $pageParam['query']['starttime'] =$stime;
	          $pageParam['query']['endtime'] = $etime;
	      }
		$carrid['uid'] = $this->ccarid();
		$company_id = Db::table('ct_user')->where($carrid)->find();
		$driverid = Db::field('uid')->table('ct_user')->where('lineclient',$company_id['companyid'])->select();
		$userid = '';
		foreach ($driverid as $key => $value) {
			$userid .= $value['uid'].',';
		}
		$userid =  substr($userid,0,strlen($userid)-1);
		$result_where['a.userid'] = ['IN',$userid];
		$array = Db::field('a.*,b.realname,b.phone,r.take_time,r.start_time,r.runtime,r.status')
			->table('ct_city_order')
			->alias('a')
			->join('ct_user b','b.uid=a.userid')
			->join('ct_rout_order r','r.rid=a.rout_id','LEFT')
			->where($result_where)
			->order('a.addtime','desc')
			->paginate(10,false,$pageParam);

		$this->assign('list',$array);
		
		return view('order/usercity');


	}

	/*
    *市内配送订单列表
    */
    public function details(){
        $id = input('id');
        $result = Db::table('ct_city_order')
                ->field('a.*,c.realname,c.phone,car.realname drivername,car.mobile')
                ->alias('a')
                ->join('ct_user c','c.uid = a.userid')
                ->join('ct_rout_order rout','rout.rid = a.rout_id','LEFT')
                ->join('ct_driver car','car.drivid = rout.driverid','LEFT')
                ->where('id',$id)
                ->find();
       $reicpt = json_decode($result['picture'],TRUE);
        $pic = array();
        if (!empty($reicpt)) {
           foreach ($reicpt as $key => $value) {
            $pic[] = $value;
           }
        }
         //发货人
        $shipper = city_contact($result['shipperid']);
        if ($shipper['code'] == '1002') {
             $result['shipperid'] = $shipper['name'];
            $result['pickphone'] = $shipper['phone'];
        }
        //收货人
        $contact = city_contact($result['contactid']);
        if ($contact['code'] == '1002') {
            $result['contactid'] = $contact['name'];
            $result['sendphone'] = $contact['phone'];
        }
        //提货地址
		$result['pickaddress'] = cityorder_address($result['saddress'],'1');
		//配送地址
		$result['getaddress'] = cityorder_address($result['eaddress'],'2');
        $this->assign('list',$result);
       return view('order/details');
    }
	//零担提货单添加司机、电话、车牌
	public function adddriver_ti(){
		$datea = Request::instance()->get();
		if (empty($datea['drivername']) || empty($datea['driverphone']) || empty($datea['carlicense'])) {
			//没值
			$this->success('操作失败', 'order/torder');
		}else{
			//有值
			$invoice_where['picid'] = $datea['id'];
			$invoice_data['drivername'] = $datea['drivername'];
			$invoice_data['driverphone'] = $datea['driverphone'];
			$invoice_data['carlicense'] = $datea['carlicense'];
			Db::table('ct_pickorder')->where($invoice_where)->update($invoice_data);
			$this->success('操作成功', 'order/torder');
		}
	}

	//整车订单添加司机、电话、车牌
	public function adddriver_city(){
		$datea = Request::instance()->get();
		if (empty($datea['drivername']) || empty($datea['driverphone']) || empty($datea['carlicense'])) {
			//没值
			$this->success('操作失败', 'order/cityvehicle');
		}else{
			//有值
			$carload_where['uoid'] = $datea['id'];
			$carload_data['drivername'] = $datea['drivername'];
			$carload_data['driverphone'] = $datea['driverphone'];
			$carload_data['carlicense'] = $datea['carlicense'];
			Db::table('ct_userorder')->where($carload_where)->update($carload_data);
			$this->success('操作成功', 'order/cityvehicle');
		}
	}
	//市内配送订单添加司机、电话、车牌
	public function city_driver(){
		$datea = Request::instance()->get();
		if (empty($datea['drivername']) || empty($datea['driverphone']) || empty($datea['carlicense'])) {
			//没值
			$this->success('操作失败', 'order/citydistribution');
		}else{
			//有值
			$invoice_where['rid'] = $datea['id'];
			$invoice_data['drivername'] = $datea['drivername'];
			$invoice_data['driverphone'] = $datea['driverphone'];
			$invoice_data['carlicense'] = $datea['carlicense'];
			Db::table('ct_rout_order')->where($invoice_where)->update($invoice_data);
			$this->success('操作成功', 'order/citydistribution');
		}
	}
	//项目用户订单列表
	public function uorder(){
		$search = input('search');
	    $stime = input('starttime');
	    $etime = input('endtime');
	    $pageParam    = ['query' =>[]];
	   $arr=array();
	  
	    if(!empty($search)){
	       $result_where['a.ordernumber'] = ['like','%'.$search.'%'];
	       $pageParam['query']['search'] = $search;
	    }
	    if(!empty($stime) && !empty($etime)) {
	          $endtime = strtotime(trim($etime).'23:59:59');
	          $starttime = strtotime(trim($stime).'00:00:00');
	          $result_where['a.addtime'] = array(array('EGT',$starttime),array('ELT', $endtime));
	          $pageParam['query']['starttime'] =$stime;
	          $pageParam['query']['endtime'] = $etime;
	      }

		$carrid['uid'] = $this->ccarid();
		$company_id = Db::table('ct_user')->where($carrid)->find();
		$userid = Db::field('uid')->table('ct_user')->where('lineclient',$company_id['lineclient'])->select();
		$userid_id = '';
		foreach ($userid as $key => $value) {
			$userid_id .= $value['uid'].',';
		}
		$userid_id =  substr($userid_id,0,strlen($userid_id)-1);
		 $result_where['a.userid'] = ['in',$userid_id];
		// $result = Db::table('ct_order')->where('a.userid','in',$userid_id)->order('oid','desc')->paginate(10);
		$result = Db::field('a.*,b.deptime,b.endtime,c.shiftnumber,c.linecityid,c.timestrat,c.timeend,c.arrivetimestart,c.arrivetimeend,d.cid,e.*')
			->table('ct_order')
			->alias('a')
			->join('ct_shift_log b','b.slid=a.slogid')
			->join('ct_shift c','c.sid=b.shiftid')
			->join('ct_company d','d.cid=c.companyid')
			->join('ct_already_city e','e.city_id=c.linecityid')
			->where($result_where)
			->order('a.oid','desc')
			->paginate(10,false,$pageParam);
		foreach ($result as $key => $value) {
			$name = $this->completeAddress($value['start_id'],'','');
			$name1 = $this->completeAddress($value['end_id'],'','');
			$array = $result[$key];
			$array['startcity'] = $name;
			$array['endcity'] = $name1;
			$result[$key] = $array;
		}
		// echo '<pre>';
		// print_r($result);
		$this->assign('list',$result);
		return view('order/uorder');
	}
	//项目用户订单详情
	public function uorderdetails(){
		$id = input('id');
		$result = Db::field('a.*,b.deptime,b.endtime,c.shiftnumber,c.linecityid,c.timestrat,c.timeend,c.arrivetimestart,c.arrivetimeend,d.cid,e.*')
			->table('ct_order')
			->alias('a')
			->join('ct_shift_log b','b.slid=a.slogid')
			->join('ct_shift c','c.sid=b.shiftid')
			->join('ct_company d','d.cid=c.companyid')
			->join('ct_already_city e','e.city_id=c.linecityid')
			->where('a.oid',$id)
			->find();
		$name = $this->completeAddress($result['start_id'],'','');
		$name1 = $this->completeAddress($result['end_id'],'','');
		$result['startcity'] = $name;
		$result['endcity'] = $name1;
		$tname = Db::field('realname,phone')->table('ct_user')->where('uid',$result['userid'])->find();
		
		$result['username'] = $tname['realname'];
		
		$result['telephone'] = $tname['phone'];
		
		$taddress=array();
		$paddress=array();
		    //提货地址

		   $taddress = json_decode($result['pickaddress'],TRUE);
		   if (!empty($taddress)) {
		     foreach ($taddress as $key => $value) {
		        $result['taddress'][$key] =  $value['taddressstr'];
		      } 
		  }
		    //配送地址
		    $paddress = json_decode($result['sendaddress'],TRUE);
		    if (!empty($paddress)) {
		      foreach ($paddress as $key => $value) {
		        
		        $result['paddress'][$key]['address'] =  $value['paddressstr'];
		        $result['paddress'][$key]['contact'] =  $value['name']."/".$value['phone'];
		      } 
		    }
		if ($result['receipt']) {
			$receipt = json_decode($result['receipt'],true);
			$ii = '0';
			$result['receipt'] = array();
			foreach ($receipt as $ke1y => $valu1e) {
				$result['receipt'][$ii]['name'] = $valu1e;
				$ii++;
			}
			
			
		}

		$this->assign('list',$result);
		return view('order/uorderdetails');
	}
}