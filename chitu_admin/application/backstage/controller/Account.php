<?php
namespace app\backstage\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
/**
  * Author: baobaolong
  */
class Account extends Base
{
	//首页 所有用户展示（用户和运营人员）
   /* public function index(){
        $this->if_login();
        $carrier_id = Session::get('carrier_id','carrier_mes');
        $where['carrid'] = $carrier_id;
        $carriers_result  = Db::table('ct_carriers')->where($where)->find();
        $search = input('search');
        if (!empty($search)) {
            $user_date['phone'] = $search;
            $user_date['lineclient'] = $carriers_result['companyid'];
            $udata = Db::table('ct_user')->where($user_date)->find();
            if (!empty($udata)) {
                $userarray['0']['type'] = '1';
                $userarray['0']['id'] = $udata['uid'];
                $userarray['0']['name'] = $udata['realname'];
                $userarray['0']['phone'] = $udata['phone'];
                $userarray['0']['time'] = $udata['addtime'];
                $this->assign('array',$userarray);
                return view('account/index'); 
                exit;
            }
            $driver_date['mobile'] = $search;
            $driver_date['companyid'] = $carriers_result['companyid'];
            $ddata = Db::table('ct_driver')->where($driver_date)->find();
            if(!empty($ddata)){
                $dirverarray['0']['type'] = '2';
                $dirverarray['0']['id'] = $ddata['drivid'];
                $dirverarray['0']['name'] = $ddata['realname'];
                $dirverarray['0']['phone'] = $ddata['mobile'];
                $dirverarray['0']['time'] = $ddata['addtime'];
                $this->assign('array',$dirverarray);
                return view('account/index'); 
                exit;
            }
        }
        $user_where['lineclient'] = $carriers_result['companyid'];
        $user_where['delstate'] = '1';
        $user_relust = Db::table('ct_user')->where($user_where)->select();
        $driver_where['companyid'] = $carriers_result['companyid'];
        $driver_where['delstate'] = '1';
        $dirver_relust = Db::table('ct_driver')->where($driver_where)->order('type desc')->select();
        $userarray = array();
        $dirverarray = array();
        if (!empty($user_relust)) {
            foreach ($user_relust as $key => $value) {
                $userarray[$key]['type'] = '1';
                $userarray[$key]['id'] = $value['uid'];
                $userarray[$key]['name'] = $value['realname'];
                $userarray[$key]['phone'] = $value['phone'];
                $userarray[$key]['time'] = $value['addtime'];
                $dirverarray[$key]['usertype'] = '';
            }
        }
        if (!empty($dirver_relust)) {
            foreach ($dirver_relust as $key => $value) {
                $dirverarray[$key]['type'] = '2';
                $dirverarray[$key]['id'] = $value['drivid'];
                $dirverarray[$key]['name'] = $value['realname'];
                $dirverarray[$key]['phone'] = $value['mobile'];
                $dirverarray[$key]['time'] = $value['addtime'];
                $dirverarray[$key]['usertype'] = $value['type'];
            }
        }
        $array = $this->mymArrsort(array_merge($dirverarray,$userarray),'time');
        $this->assign('array',$array);
    	return view('account/index'); 
    }*/

    public function index(){
        $this->if_login();
        $carrier_id = Session::get('carrier_id','carrier_mes');
        $where['drivid'] = $carrier_id;
        $carriers_result  = Db::table('ct_driver')->where($where)->find();
        $search = input('search');
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
           $driver_where['realname|phone'] = ['like','%'.$search.'%'];
           $pageParam['query']['search'] = $search;
        }
        $driver_where['companyid'] = $carriers_result['companyid'];
        $driver_where['delstate'] = '1';
        $dirver_relust = Db::table('ct_driver')
                            ->where($driver_where)
                            ->order('type desc')
                            ->paginate(10,false, $pageParam);
       
        $dirverarray = array();
        if (!empty($dirver_relust)) {
            foreach ($dirver_relust as $key => $value) {
                $dirverarray[$key]['id'] = $value['drivid'];
                $dirverarray[$key]['name'] = $value['realname'];
                $dirverarray[$key]['phone'] = $value['mobile'];
                $dirverarray[$key]['time'] = $value['addtime'];
                $dirverarray[$key]['type'] = $value['type'];
            }
        }
        $page = $dirver_relust->render();
        $this->assign('array',$dirverarray);
        $this->assign('page',$page);
        return view('account/index'); 

    }
    public function login(){
        //没有继承controller用view
        return view('index/login'); 
    }
    //根据登录承运商的状态进入添加用户页面
    public function addaccount(){
        $this->if_login();
       
        return view('account/addaccount');
    }
    //添加用户或者运营人员
    public function add(){
        $postarray = Request::instance()->post();
        //var_dump($postarray);exit();
        $carrier_id = Session::get('carrier_id','carrier_mes');
        $where['drivid'] = $carrier_id;
        $result  = Db::table('ct_driver')->where($where)->find();
        
            switch ($postarray['types']) {
                case '1':
                    $dirver_data['driver_grade'] = '2';
                    break;
                case '2':
                    $dirver_data['driver_grade'] = '3';
                    break;
                case '3':
                    $dirver_data['driver_grade'] = '4';
                    break;
                default:
                    $dirver_data['driver_grade'] = '2';
                    break;
            }
            $dirver_data['type'] = $postarray['types'];
             $dirver_data['carstatus'] = 2;
            $dirver_data['companyid'] = $result['companyid'];
            $dirver_data['realname'] = $postarray['realname'];
            if ($postarray['username'] == '') {
                 $dirver_data['username'] = $postarray['username'];
            }else{
                $dirver_data['username'] = 'Chitu'.mt_rand('0000','9999');
            }
           

            $dirver_data['mobile'] = $postarray['phone'];
             $search_driver = DB::table('ct_driver')->where('mobile',$postarray['phone'])->find();
            if (!empty($search_driver)) {
                $this->error("号码已存在，请重新写入");
            }
            if ($postarray['password']) {
                $dirver_data['password'] = md5($postarray['password'].'ct888');
            }
            $dirver_data['sex'] = $postarray['sex'];
            $dirver_data['addtime'] = time();
            Db::table('ct_driver')->insert($dirver_data);
        
        $this->success('新增成功', 'account/index');
    }
    //进入修改页面、判断是用户还是运营人员
    public function edit(){
        $getarray = Request::instance()->get();
       
        $where['drivid'] = $getarray['id'];
        $relust = Db::table('ct_driver')->where($where)->find();

        $relust['phone'] = $relust['mobile'];
        $relust['id'] = $relust['drivid'];
     // print_r($relust);
        $this->assign('array',$relust);
        $this->assign('carrier_type',$getarray['type']);
        return view('account/edit');
    }
    //删除用户（用户与运营人员）
    public function delaccount(){
        $getarray = Request::instance()->get();
        $where['drivid'] = $getarray['id'];
        Db::table('ct_driver')->where($where)->update(['delstate'=>'2','deltine'=>time()]);
        $this->success('删除成功', 'account/index');
    }
    //执行修改功能（用户或者运营人员）
    public function editaccount(){
        $postarray = Request::instance()->post();
            switch ($postarray['types']) {
                case '1':
                    $driver_data['driver_grade'] = '2';
                    break;
                case '2':
                    $driver_data['driver_grade'] = '3';
                    break;
                case '2':
                    $driver_data['driver_grade'] = '4';
                    break;
                default:
                    $driver_data['driver_grade'] = '2';
                    break;
            }
            $driver_data['type'] = $postarray['types'];
            $where['drivid'] = $postarray['id'];
            $driver_data['mobile'] = $postarray['phone'];
            if ($postarray['password'] !='') {
                  $driver_data['password'] = md5($postarray['password'].'ct888');
             } 
           
            $driver_data['realname'] = $postarray['realname'];
            $driver_data['username'] = $postarray['username'];
            $driver_data['sex'] = $postarray['sex'];
            $driver_data['delstate'] = '1';
            Db::table('ct_driver')->where($where)->update($driver_data);
           // echo Db::table('ct_driver')->getLastSql();
       
        $this->success('修改成功', 'account/index');
    }
}