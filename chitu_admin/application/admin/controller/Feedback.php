<?php
/*
*author:崔玉龙
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Feedback extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->if_login();
//    }
    //未审核列表
    public function index(){
    	$where='';
        
        $pageParam    = ['query' =>[]];
        $stime = input('starttime');
        $etime = input('endtime');
       if(!empty($stime) && !empty($etime)) {
          $endtime = strtotime(trim($etime).'23:59:59');
          $starttime = strtotime(trim($stime).'00:00:00');
          $where['add_time'] = array(array('EGT',$starttime),array('ELT', $endtime));
          $pageParam['query']['starttime'] =$stime;
          $pageParam['query']['endtime'] = $etime;
      }
      $where['status']=1;
      $array = array();
      $result = Db::table('ct_feedback')->where($where)->order('id','desc')->paginate(10,false, $pageParam);
       $result_data = $result->toArray();
      foreach ($result_data['data'] as  $key=> $value) {
            $array[] = $value;
            if ($value['type'] ==1 ) {
                $user = DB::table('ct_user')->where('uid',$value['action_id'])->find();
                $array[$key]['username'] = $user['realname'];
                $array[$key]['phone'] = $user['phone'];
            }elseif ($value['type'] ==2 ){
                $driver = DB::table('ct_driver')->where('drivid',$value['action_id'])->find();
                $array[$key]['username'] = $driver['realname'];
                $array[$key]['phone'] = $driver['mobile'];
            }
        }
        $page = $result->render();
        $this->assign('list',$array);
        $this->assign('page',$page);
      return view('feedback/index');
    }
    public function pass(){
        $data['status'] = 2;
        $data['replay_mess'] = input('mess');
        $update= DB::table('ct_feedback')->where('id',input('id'))->update($data);
        if ($update) {
            $this->success('问题已解决','feedback/index');
        }else{
            $this->error('问题未解决');
        }
           
    }

    public function indexpass(){
        $where='';
        
        $pageParam    = ['query' =>[]];
        $stime = input('starttime');
        $etime = input('endtime');
       if(!empty($stime) && !empty($etime)) {
          $endtime = strtotime(trim($etime).'23:59:59');
          $starttime = strtotime(trim($stime).'00:00:00');
          $where['add_time'] = array(array('EGT',$starttime),array('ELT', $endtime));
          $pageParam['query']['starttime'] =$stime;
          $pageParam['query']['endtime'] = $etime;
      }
      $where['status']=2;
      $array = array();
      $result = Db::table('ct_feedback')->where($where)->order('id','desc')->paginate(10,false, $pageParam);
       $result_data = $result->toArray();
      foreach ($result_data['data'] as  $key=> $value) {
            $array[] = $value;
            if ($value['type'] ==1 ) {
                $user = DB::table('ct_user')->where('uid',$value['action_id'])->find();
                $array[$key]['username'] = $user['realname'];
                $array[$key]['phone'] = $user['phone'];
            }elseif ($value['type'] ==2 ){
                $driver = DB::table('ct_driver')->where('drivid',$value['action_id'])->find();
                $array[$key]['username'] = $driver['realname'];
                $array[$key]['phone'] = $driver['mobile'];
            }
        }
        $page = $result->render();
        $this->assign('list',$array);
        $this->assign('page',$page);
      return view('feedback/indexpass');
    }

    //后台操作日志删除操作
    public function del(){
        
            $get_data = Request::instance()->get();
            if ($get_data['del'] ==1) {
                $del= DB::table('ct_feedback')->where('id',$get_data['id'])->delete();
                if ($del) {
                    $this->success('删除成功','feedback/indexpass');
                }else{
                    $this->error('删除失败');
                }
            }
            
            if ($get_data['del'] ==2) {
                $del= DB::table('ct_feedback')->where(array('id'=>array('in',$get_data['id'])))->delete();
                if ($del) {
                    print_r('ok');
                }else{
                     print_r('fail');
                }
            }
    }

    
}