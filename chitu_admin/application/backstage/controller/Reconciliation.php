<?php
namespace app\backstage\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
/**
  * 对账
  * Author: baobaolong
  */
class Reconciliation extends Base
{
	function __construct(){
        parent::__construct();
        $this->if_login();
        $this->carrid = Session::get('carrier_id','carrier_mes');
        $this->usertype = Session::get('carrier_usertype','carrier_mes');
    }
	//对账单列表
	public function index(){
		$carrid = Session::get('carrier_id','carrier_mes');
		if ($this->usertype == "driver") {
			$carrier_type = Db::table('ct_driver')
			->where('drivid',$this->carrid)
			->find();
			$companyid = $carrier_type['companyid'];
		}else{
			$carrier_type = Db::table('ct_user')->where('uid',$this->carrid)->find();
			$companyid = $carrier_type['lineclient'];
		}
		
		$result = Db::table('ct_invoice')
			->where('companyid',$companyid)
			->order('sermonth','desc')
			->paginate(10);
		$this->assign('list',$result);
		return view('reconciliation/index');
	}
	//对账单详情->订单列表
	public function details(){
		$id = input('id');
		if ($this->usertype == "driver") {
			$company_type = Db::table('ct_driver')->alias('a')->join('ct_company c','c.cid=a.companyid')->where('drivid',$this->carrid)->find();
		}else{
			$company_type = Db::table('ct_user')->alias('a')->join('ct_company c','c.cid=a.lineclient')->where('uid',$this->carrid)->find();
		}
		
		$invoice_data = Db::table('ct_invoice')->where('iid',$id)->find();
		switch ($company_type['type']) {
			//干线公司对账列表
			case '1':
					$result = Db::field('b.*,e.start_id,e.end_id,d.shiftnumber,c.deptime')
						->table('ct_lineorder')
						->alias('a')
						->join('ct_order b','b.oid=a.orderid')
						->join('ct_shift_log c','c.slid=b.slogid')
						->join('ct_shift d','d.sid=c.shiftid')
						->join('ct_already_city e','e.city_id=d.linecityid')
						->where('a.line_checkid',$id)
						->paginate(10, false, ['query' => Request::instance()->param(),]);
					foreach ($result as $key => $value) {
						$array = $result[$key];
						$tprice = Db::table('ct_pickorder')->where('orderid',$value['oid'])->find();
						if ($tprice['type'] != 2) {
							$array['pickcost'] = '0';
						}
						$array['start_id'] = $this->completeAddress($value['start_id'],'','');
						$array['end_id'] = $this->completeAddress($value['end_id'],'','');
						$result[$key] = $array;
					}

					$uurl = 'reconciliation/gdetails';
				break;
			//项目用户对账
			case '3':
					$result = Db::field('a.*,c.shiftnumber,d.start_id,d.end_id,b.deptime')
						->table('ct_order')
						->alias('a')
						->join('ct_shift_log b','b.slid=a.slogid')
						->join('ct_shift c','c.sid=b.shiftid')
						->join('ct_already_city d','d.city_id=c.linecityid')
						->where('a.user_checkid',$id)
						->paginate(10, false, ['query' => Request::instance()->param(),]);
					foreach ($result as $key => $value) {
						$start_id = $this->completeAddress($value['start_id'],'','');
						$end_id = $this->completeAddress($value['end_id'],'','');
						$array = $result[$key];
						$array['start_id'] = $start_id;
						$array['end_id'] = $end_id;
						$result[$key] = $array;
					}
					$uurl = 'reconciliation/details';
				break;
			//提货公司对账
			case '2':
					$result = Db::field('b.ordernumber,a.*,c.realname')
						->table('ct_pickorder')
						->alias('a')
						->join('ct_order b','b.oid=a.orderid')
						->join('ct_driver c','c.drivid=a.driverid')
						->where('a.pic_checkid',$id)
						->paginate(10, false, ['query' => Request::instance()->param(),]);
					
					$uurl = 'reconciliation/pdetails';
				break;
		}
		$this->assign('array',$invoice_data);
		$this->assign('list',$result);
		return view($uurl);
	}
	//对账确定
	public function determine(){
		$invoice_where['iid'] = input('id');
		$invoice_data['confirm'] = '1';
		Db::table('ct_invoice')->where($invoice_where)->update($invoice_data);
		$this->success('操作成功', 'reconciliation/index');

	}
	//订单详情
	public function orderdateils(){
		$result = Db::field('a.*,c.shiftnumber,b.deptime,b.endtime,d.start_id,d.end_id,c.timestrat,c.timeend,c.arrivetimestart,c.arrivetimeend')
			->table('ct_order')
			->alias('a')
			->join('ct_shift_log b','b.slid=a.slogid')
			->join('ct_shift c','c.sid=b.shiftid')
			->join('ct_already_city d','d.city_id=c.linecityid')
			->where('a.oid',input('id'))
			->find();

		
			//提货地址
		   $taddress = json_decode($result['pickaddress'],TRUE);
		   if (!empty( $taddress)) {
		   foreach ($taddress as $key => $value) {
		    
		      $array['taddress'][$key] =  $value['taddressstr'];
		    } 
		  }
		
		//配送地址
	    $paddress = json_decode($result['sendaddress'],TRUE);
	    if (!empty( $paddress)) {
	       foreach ($paddress as $key => $value) {
	        
	        $array['paddress'][$key]['address'] =  $value['paddressstr'];
	        $array['paddress'][$key]['contact'] =  $value['name']."/".$value['phone'];
	      } 
	    }
  
		$result['startcity'] = detailadd($result['start_id'],'','');
		$result['endcity'] = detailadd($result['end_id'],'','');
		//获取下单人信息
		$user_data = Db::field('a.realname,a.phone')
			->table('ct_user')
			->where('uid',$result['userid'])
			->find();
		$result['username'] = $user_data['realname'];
		$result['userphone'] = $user_data['phone'];
		

		//获取提货人信息
		$mention = Db::field('b.drivername,b.driverphone,b.carlicense')
			->table('ct_order')
			->alias('a')
			->join('ct_pickorder b','b.orderid=a.oid')
			->where('a.oid',input('id'))
			->find();
		$result['realname'] = $mention['drivername'];
		$result['mobile'] = $mention['driverphone'];
		$result['carlicense'] = $mention['carlicense'];
		$this->assign('list',$result);
		return view('reconciliation/orderdateils');
	}	
	//项目用户确定对账单
	public function determineq(){

		$datea = Request::instance()->get();
		if (empty($datea['price']) || empty($datea['remark'])) {
			//没值
			$this->success('操作失败', 'reconciliation/index');
		}else{
			//有值
			$invoice_where['iid'] = $datea['id'];
			$invoice_data['carr_total'] = $datea['price'];
			$invoice_data['carr_remark'] = $datea['remark'];
			Db::table('ct_invoice')->where($invoice_where)->update($invoice_data);
			$this->success('操作成功', 'reconciliation/index');
		}
	}
	//干线公司确定对账单
	public function gdetermine(){
		$url_data = Request::instance()->get();
		if (empty($url_data['Invoiceno']) || empty($url_data['Invoiceamount'])) {
			$this->success('操作失败', 'reconciliation/index');
		}else{
			$update_where['iid'] = $url_data['id']; 
			$update_data['Invoiceno'] = $url_data['Invoiceno']; 
			$update_data['Invoiceamount'] = $url_data['Invoiceamount']; 
			$update_data['paytime'] = time(); 
			Db::table('ct_invoice')->where($update_where)->update($update_data);
			$this->success('操作成功', 'reconciliation/index');
		}

	}

}