<?php
namespace app\backstage\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
/**
  * 登录公司的所有车辆信息
  * Author: baobaolong
  */
class Vehiclereview  extends Base
{
	//登录公司所有车辆列表
	public function index(){
        $carrid = Session::get('carrier_id','carrier_mes');
        $search = input('search');
        
        $comid = Db::table('ct_driver')->where('drivid',$carrid)->find();
        if (!empty($search)) {
            $result_where['a.carnumber'] = ['like','%'.$search.'%'];
        }
        $result_where['a.com_id'] = $comid['companyid']; 
        $result = Db::field('a.*,b.realname,c.carparame,c.lowprice')
                ->table('ct_carcategory')
                ->alias('a')
                ->join('ct_driver b','b.drivid=a.driverid','LEFT')
                ->join('ct_cartype c','c.car_id=a.carid')
                ->where($result_where)
                ->paginate(10);
        $this->assign('list',$result);
        $page = $result->render();
        $this->assign('page',$page);
        return view('vehiclereview/index');
        /*$driver_data = Db::field('a.drivid')
            ->table('ct_driver')
            ->alias('a')
            ->join('ct_carriers b','b.companyid=a.companyid')
            ->where('b.carrid',$carrid)
            ->select();
        $driver = '';
        foreach ($driver_data as $key => $value) {
            $driver .= $value['drivid'].',';
        }
        $driver =  substr($driver,0,strlen($driver)-1); 

        if (empty($search)) {
           $result = Db::field('a.*,b.realname,c.carparame,c.lowprice')
                ->table('ct_carcategory')
                ->alias('a')
                ->join('ct_driver b','b.drivid=a.driverid')
                ->join('ct_cartype c','c.car_id=a.carid')
                ->where('a.driverid','in',$driver)
                ->paginate(10);
            $this->assign('list',$result);
            return view('vehiclereview/index');
        }else{
            $result_where['a.carnumber'] = ['like','%'.$search.'%'];
            $result = Db::field('a.*,b.realname')
                ->table('ct_carcategory')
                ->alias('a')
                ->join('ct_driver b','b.drivid=a.driverid')
                ->where('a.driverid','in',$driver)
                ->where($result_where)
                ->paginate(10);
            $this->assign('list',$result);
            return view('vehiclereview/index');
        }*/
    }
    //车辆详情
    public function details(){
    	$id = input('id');
    	$result = Db::field('a.*,b.realname,b.mobile,c.*')
    	->table('ct_carcategory')
    	->alias('a')
    	->join('ct_driver b','b.drivid=a.driverid','LEFT')
        ->join('ct_cartype c','c.car_id=a.carid')
    	->where('a.ccid',$id)
    	->find();
    	$this->assign('list',$result);
    	return view('vehiclereview/details');
    }
}
