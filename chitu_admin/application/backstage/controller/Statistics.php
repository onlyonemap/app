<?php
namespace app\backstage\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
/**
  * 统计图形展示
  * Author: baobaolong
  */
class Statistics  extends Base
{
	//获取公司ID
	public function ccarid(){
		$company_id = Db::table('ct_driver')->where('drivid',Session::get('carrier_id','carrier_mes'))->find();
		return  $company_id['companyid'];
	}
	//提货公司，总提货单年数量数据
	public function mentionorder(){
		//获取公司ID
		$companyid = $this->ccarid();
		$divid_data = Db::table('ct_driver')->where('companyid',$companyid)->select();
		$divid = '';
		foreach ($divid_data as $key => $value) {
			$divid .= $value['drivid'].',';
		}
		$divid =  substr($divid,0,strlen($divid)-1);
		$result = Db::query("SELECT DATE_FORMAT(FROM_UNIXTIME(receivetime),'%Y') years,count(picid) count,(SELECT count(picid) FROM ct_pickorder WHERE status=3 AND DATE_FORMAT(FROM_UNIXTIME(receivetime),'%Y')<=years) count1  FROM ct_pickorder WHERE driverid in (".$divid.") AND status=3 GROUP BY years");
		return json_encode($result);
	}
	//提货公司，总提货单每个月数量数据
	public function mentionorderdel(){
		//获取公司ID
		$postdata = Request::instance()->post();
		$year = isset($postdata['year']) ? $postdata['year'] : '2017';
		$companyid = $this->ccarid();  
		$divid_data = Db::table('ct_driver')->where('companyid',$companyid)->select();
		$divid = '';
		foreach ($divid_data as $key => $value) {
			$divid .= $value['drivid'].',';
		}
		$divid =  substr($divid,0,strlen($divid)-1);
		$result = Db::query("SELECT DATE_FORMAT(FROM_UNIXTIME(receivetime),'%Y') years,DATE_FORMAT(FROM_UNIXTIME(receivetime),'%m') months,count(picid) count,(SELECT count(picid) FROM ct_pickorder WHERE driverid in (".$divid.") AND status=3 AND DATE_FORMAT(FROM_UNIXTIME(receivetime),'%Y')<='$year' AND DATE_FORMAT(FROM_UNIXTIME(receivetime),'%m')<=months GROUP BY DATE_FORMAT(FROM_UNIXTIME(receivetime),'%Y')) count0  FROM ct_pickorder WHERE driverid in (".$divid.") AND status=3 GROUP BY months");
		return json_encode($result);
	}
	//提货公司，总提货单年重量数据
	public function mentionweight(){
		$companyid = $this->ccarid();
		$divid_data = Db::table('ct_driver')->where('companyid',$companyid)->select();
		$divid = '';
		foreach ($divid_data as $key => $value) {
			$divid .= $value['drivid'].',';
		}
		$divid =  substr($divid,0,strlen($divid)-1);
		$result = Db::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.receivetime),'%Y') years,sum(b.totalweight) count,(SELECT sum(b.totalweight) FROM ct_pickorder a LEFT JOIN ct_order b ON b.oid=a.orderid WHERE a.driverid in (".$divid.") AND DATE_FORMAT(FROM_UNIXTIME(a.receivetime),'%Y')<=years) count1 FROM ct_pickorder a LEFT JOIN ct_order b ON b.oid=a.orderid WHERE a.driverid in (".$divid.")");
		return json_encode($result);
	}
	//提货公司，总提货单年重量数据
	public function mentionweightdel(){
		$postdata = Request::instance()->post();
		$year = isset($postdata['year']) ? $postdata['year'] : '';
		$companyid = $this->ccarid();
		$divid_data = Db::table('ct_driver')->where('companyid',$companyid)->select();
		$divid = '';
		foreach ($divid_data as $key => $value) {
			$divid .= $value['drivid'].',';
		}
		$divid =  substr($divid,0,strlen($divid)-1);
		$result = Db::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.receivetime),'%Y') years,DATE_FORMAT(FROM_UNIXTIME(a.receivetime),'%m') months,sum(b.totalweight) count,(SELECT sum(b.totalweight) FROM ct_pickorder a LEFT JOIN ct_order b ON b.oid=a.orderid WHERE  a.driverid in (".$divid.") AND DATE_FORMAT(FROM_UNIXTIME(a.receivetime),'%Y')='$year' AND DATE_FORMAT(FROM_UNIXTIME(a.receivetime),'%m')<=months GROUP BY DATE_FORMAT(FROM_UNIXTIME(a.receivetime),'%Y')) count0 FROM ct_pickorder a LEFT JOIN ct_order b ON b.oid=a.orderid WHERE  a.driverid in (".$divid.") AND DATE_FORMAT(FROM_UNIXTIME(a.receivetime),'%Y')='$year' GROUP BY months");
		return json_encode($result);
	}
	//干线公司，总运输重量
	public function yeartotaltransport(){
		$postdata = Request::instance()->post();
		$year = isset($postdata["year"]) ? $postdata["year"] : '';//获取年份
		$month = isset($postdata["month"]) ? $postdata["month"] : '';//获取月份
		if (!empty($year) and !empty($month)) {
			$result = Db::query("SELECT DATE_FORMAT(FROM_UNIXTIME(b.deptime),'%m') years,sum(a.totalweight) weight,(SELECT sum(a.totalweight) FROM ct_order a  LEFT JOIN ct_shift_log b ON b.slid=a.slogid LEFT JOIN ct_shift c ON c.sid=b.shiftid WHERE DATE_FORMAT(FROM_UNIXTIME(b.deptime),'%m')='$month' AND c.companyid=".$this->ccarid().") sumweight,d.start_id,d.end_id FROM ct_order a LEFT JOIN ct_shift_log b ON b.slid=a.slogid LEFT JOIN ct_shift c ON c.sid=b.shiftid LEFT JOIN ct_already_city d ON d.city_id=c.linecityid WHERE DATE_FORMAT(FROM_UNIXTIME(b.deptime),'%Y')='$year' AND DATE_FORMAT(FROM_UNIXTIME(b.deptime),'%m')='$month' AND c.companyid=".$this->ccarid()." GROUP BY years");
		}else{
			$result = Db::query("SELECT DATE_FORMAT(FROM_UNIXTIME(b.deptime),'%Y') years,sum(a.totalweight) weight,(SELECT sum(a.totalweight) FROM ct_order a  LEFT JOIN ct_shift_log b ON b.slid=a.slogid LEFT JOIN ct_shift c ON c.sid=b.shiftid WHERE DATE_FORMAT(FROM_UNIXTIME(b.deptime),'%Y')<=years AND c.companyid=".$this->ccarid().") sumweight,d.start_id,d.end_id FROM ct_order a LEFT JOIN ct_shift_log b ON b.slid=a.slogid LEFT JOIN ct_shift c ON c.sid=b.shiftid LEFT JOIN ct_already_city d ON d.city_id=c.linecityid WHERE c.companyid=".$this->ccarid()." GROUP BY years");
		}
		// $result = Db::query("SELECT DATE_FORMAT(FROM_UNIXTIME(b.deptime),'%Y') years,sum(a.totalweight) weight,(SELECT sum(a.totalweight) FROM ct_order a  LEFT JOIN ct_shift_log b ON b.slid=a.slogid LEFT JOIN ct_shift c ON c.sid=b.shiftid WHERE DATE_FORMAT(FROM_UNIXTIME(b.deptime),'%Y')<=years AND c.companyid=".$this->ccarid().") sumweight,d.start_id,d.end_id FROM ct_order a LEFT JOIN ct_shift_log b ON b.slid=a.slogid LEFT JOIN ct_shift c ON c.sid=b.shiftid LEFT JOIN ct_already_city d ON d.city_id=c.linecityid WHERE c.companyid=".$this->ccarid()." GROUP BY years");
		
		foreach ($result as $key => $value) {
			$result[$key]['shiftName'] = $this->completeAddress($value['start_id'],'','').' - - - '.$this->completeAddress($value['end_id'],'','');
		}
		return json_encode($result);
	}
}