<?php
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Logmessage extends Base
{
	function __construct(){
        parent::__construct();
        $this->if_login();
    }
    // 后台操作日志表
    public function index(){
        $where='';
    	$search = input('search');
        $pageParam    = ['query' =>[]];
        $stime = input('starttime');
        $etime = input('endtime');
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $where['a.addtime'] = array(array('EGT',$starttime),array('ELT', $endtime));
            $pageParam['query']['starttime'] =$stime;
            $pageParam['query']['endtime'] = $etime;
        }

    	$result = Db::field('a.*,b.username')
                    ->table('ct_log')
                    ->alias('a')
                    ->join('ct_admin b','b.aid=a.admin_id','LEFT')
                    ->where($where)
                    ->order('id','desc')
                    ->paginate(10,false, $pageParam);
        $page = $result->render();
    	$this->assign('list',$result);
        $this->assign('page',$page);
    	return view('logmessage/index');
    }
    // 后台操作日志删除操作
    public function delcom(){
        
        $get_data = Request::instance()->get();

        if ($get_data['del'] ==1) {
            $del= DB::table('ct_log')->where('id',$get_data['id'])->delete();
            if ($del) {
                $this->success('删除成功','logmessage/index');
            }else{
                $this->error('删除失败');
            }
        }
            
        if ($get_data['del'] ==2) {
            $del= DB::table('ct_log')->where(array('id'=>array('in',$get_data['id'])))->delete();
            if ($del) {
                print_r('ok');
            }else{
                 print_r('fail');
            }
        }
    }
    // 用户充值列表
    public function indexpay(){
        $where='';
        $search = input('search');
        $pageParam    = ['query' =>[]];
        $stime = input('starttime');
        $etime = input('endtime');
       if(!empty($stime) && !empty($etime)) {
          $endtime = strtotime(trim($etime).'23:59:59');
          $starttime = strtotime(trim($stime).'00:00:00');
          $where['paytime'] = array(array('EGT',$starttime),array('ELT', $endtime));
          $pageParam['query']['starttime'] =$stime;
          $pageParam['query']['endtime'] = $etime;
      }
      $array = array();
        $result = Db::table('ct_paymessage')
                    ->where($where)
                    ->order('pid','desc')
                    ->paginate(10,false, $pageParam);
        $result_data = $result->toArray();
        foreach ($result_data['data'] as  $key=> $value) {
            $array[] = $value;
            if ($value['type'] ==1 ) {
                $user = DB::table('ct_user')->where('uid',$value['userid'])->find();
                
                $array[$key]['username'] = $user['realname'];
                $array[$key]['phone'] = $user['phone'];
            }elseif ($value['type'] ==2 ){
                $driver = DB::table('ct_driver')->where('drivid',$value['userid'])->find();
                $array[$key]['username'] = $driver['realname'];
                $array[$key]['phone'] = $driver['mobile'];
            }
        }
        $page = $result->render();
        $this->assign('list',$array);
        $this->assign('page',$page);
        return view('logmessage/indexpay');
    }
    //删除用户充值操作
    public function delpay(){
         $get_data = Request::instance()->get();
            if ($get_data['del'] ==1) {
                $del= DB::table('ct_paymessage')->where('pid',$get_data['id'])->delete();
                if ($del) {
                    $this->success('删除成功','logmessage/indexpay');
                }else{
                    $this->error('删除失败');
                }
            }
            
            if ($get_data['del'] ==2) {
                $del= DB::table('ct_paymessage')->where(array('pid'=>array('in',$get_data['id'])))->delete();
                if ($del) {
                    print_r('ok');
                }else{
                     print_r('fail');
                }
            }
    }
    /*
    *用户余额明细
    */
    public function indexuser(){
        $where='';
        $search = input('search');
        $pageParam    = ['query' =>[]];
        $stime = input('starttime');
        $etime = input( 'endtime');
       if(!empty($stime) && !empty($etime)) {
          $endtime = strtotime(trim($etime).'23:59:59');
          $starttime = strtotime(trim($stime).'00:00:00');
          $where['paytime'] = array(array('EGT',$starttime),array('ELT', $endtime));
          $pageParam['query']['starttime'] =$stime;
          $pageParam['query']['endtime'] = $etime;
      }
      $result = Db::field('a.*,b.realname')
                    ->table('ct_balance')
                    ->alias('a')
                    ->join('ct_user b','b.uid=a.userid')
                    ->where($where)
                    ->order('blid','desc')
                    ->paginate(10,false, $pageParam);
        $page = $result->render();
        $this->assign('list',$result);
        $this->assign('page',$page);
        return view('logmessage/indexuser');
    }
    /*
    *删除用户余额明细操作
    */
    public function deluser(){
        $get_data = Request::instance()->get();
            if ($get_data['del'] ==1) {
                $del= DB::table('ct_balance')->where('blid',$get_data['id'])->delete();
                if ($del) {
                    $this->success('删除成功','logmessage/indexuser');
                }else{
                    $this->error('删除失败');
                }
            }
            
            if ($get_data['del'] ==2) {
                $del= DB::table('ct_balance')->where(array('blid'=>array('in',$get_data['id'])))->delete();
                if ($del) {
                    print_r('ok');
                }else{
                     print_r('fail');
                }
            }
    }

    /*
    *司机余额明细
    */
    public function indexdriver(){
        $where='';
        $search = input('search');
        $pageParam    = ['query' =>[]];
        $stime = input('starttime');
        $etime = input('endtime');
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $where['paytime'] = array(array('EGT',$starttime),array('ELT', $endtime));
            $pageParam['query']['starttime'] = $stime;
            $pageParam['query']['endtime'] = $etime;
        }
        $result = Db::field('a.*,b.realname')
                    ->table('ct_balance_driver')
                    ->alias('a')
                    ->join('ct_driver b','b.drivid=a.driver_id')
                    ->where($where)
                    ->order('blid','desc')
                    ->paginate(10,false, $pageParam);
        $page = $result->render();
        $this->assign('list',$result);
        $this->assign('page',$page);
        return view('logmessage/indexdriver');
    }
    /*
    *删除用户余额明细操作
    */
    public function deldriver(){
        $get_data = Request::instance()->get();
            if ($get_data['del'] ==1) {
                $del= DB::table('ct_balance_driver')->where('blid',$get_data['id'])->delete();
                if ($del) {
                    $this->success('删除成功','logmessage/indexdriver');
                }else{
                    $this->error('删除失败');
                }
            }
            
            if ($get_data['del'] ==2) {
                $del= DB::table('ct_balance_driver')->where(array('blid'=>array('in',$get_data['id'])))->delete();
                if ($del) {
                    print_r('ok');
                }else{
                     print_r('fail');
                }
            }
    }

    /*
    *
    *司机取消订单日志
    */
    public function cancelorder(){
        $where='';
        $search = input('search');
        $pageParam    = ['query' =>[]];
        $stime = input('starttime');
        $etime = input('endtime');
       if(!empty($stime) && !empty($etime)) {
          $endtime = strtotime(trim($etime));
          $starttime = strtotime(trim($stime));
          $where['cancel_time'] = array(array('EGT',$starttime),array('ELT', $endtime));
          $pageParam['query']['starttime'] =$stime;
          $pageParam['query']['endtime'] = $etime;
      }
        $result = DB::table('ct_cancel_order')
                    ->alias('a')
                    ->join('ct_pickorder p','p.picid = a.pick_id ')
                    ->join('ct_order o','o.oid=p.orderid')
                    ->join('ct_driver dr','dr.drivid = a.driver_id')
                    ->field('a.can_id,a.deduct,a.order_state,a.cancel_time,o.ordernumber,dr.mobile,dr.realname')
                    ->where($where)
                    ->paginate(10,false, $pageParam);
        $page = $result->render();
        $this->assign('list',$result);
        $this->assign('page',$page);
        return view('logmessage/cancelorder');
    }

    public function delcancel(){
         $get_data = Request::instance()->get();
            if ($get_data['del'] ==1) {
                $del= DB::table('ct_cancel_order')->where('can_id',$get_data['id'])->delete();
                if ($del) {
                    $this->success('删除成功','logmessage/cancelorder');
                }else{
                    $this->error('删除失败');
                }
            }
            
            if ($get_data['del'] ==2) {
                $del= DB::table('ct_cancel_order')->where(array('can_id'=>array('in',$get_data['id'])))->delete();
                if ($del) {
                    print_r('ok');
                }else{
                     print_r('fail');
                }
            }
    }
}