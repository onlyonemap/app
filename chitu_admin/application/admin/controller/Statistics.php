<?php
/**
 * 赤途冷链
 * ============================================================================
 * 网站地址: https://app.56cold.com
 * ----------------------------------------------------------------------------
 * @Author: 李渊 
 * @Date: 2018-11-02
 * @Notes: 后台数据统计类
 * ============================================================================
 */ 
namespace app\admin\controller;
use think\Controller;  	// 使用控制器
use think\Db;			// 使用数据库操作
use think\Request;		// 使用请求
use think\Session; 		// 使用session
class Statistics extends Base
{
	
//	function __construct()
//	{
//		parent::__construct();
//		$this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//	}

	/**
	 * 货源信息：司机拨打电话查看货源统计
	 * @return [type] [description]
	 */
	public function driverCallUserCount()
	{
		// 定义统计数组
		$countArr = array();
		// 查询条件 货源信息
		$where['ordertype'] = 1;
		// 查询条件 已支付
		$where['paystate'] = 2;
		// 查询条件 有人打电话
		$where['driverid'] = ['NEQ',''];
		// 查询数据
		$result = Db::table('ct_issue_item')->where($where)->select();
		// 遍历循环数据
		foreach ($result as $key => $value) {
			$driverid = json_decode($value['driverid'],true);
			foreach ($driverid as $key1 => $value1) {
				if (array_key_exists($value1,$countArr)) {
					$countArr[$value1] = $countArr[$value1]+1;
				} else {
					$countArr[$value1] = 1;
				}
			}
		}

		// 定义统计信息
		$driverCallinfo = array();
		$i = 0;
		// 遍历数据
		foreach ($countArr as $key => $value) {
			$driver = Db::table('ct_driver')->where('drivid',$key)->find();
			$driverCallinfo[$i]['index'] = $i;
			$driverCallinfo[$i]['id'] = $driver['drivid'];
			$driverCallinfo[$i]['name'] = $driver['realname'] ? $driver['realname'] : $driver['username'];
			$driverCallinfo[$i]['mobile'] = $driver['mobile'];
			$driverCallinfo[$i]['count'] = $value;
			$driverCallinfo[$i]['driver_grade'] = $driver['driver_grade'];
			// 是否为公司 1 否 2 是
			if ($driver['companyid'] == '') { 
				$driverCallinfo[$i]['type'] = 1;
			} else {
				$driverCallinfo[$i]['type'] = 2;
			}
			$i++;
		}

		$this->assign('list', $driverCallinfo);
		return view('statistics/driverCallUserCount');
	}
}

?>