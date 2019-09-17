<?php
/*
*author:崔玉龙
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Toreview extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//    }
    //未审核列表
    public function index(){
        $phone = input('search');
        $search['states'] = 1;
        $pageParam    = ['query' =>[]];
        if (!empty($phone)) {
            $search['account'] = ['like','%'.$phone.'%'];
            $pageParam['query']['search'] = $search;
        }
        $array = array();
        $result = DB::table('ct_application')
                    ->where($search)
                    ->order('start_time','desc')
                    ->paginate(10,false, $pageParam);
        $results = $result->toArray();
        foreach ($results['data'] as $key => $value){
            $array[$key] = $value;
            if ($value['action_type'] ==1){
                //司机
                $driver = DB::table('ct_driver')->where('drivid',$value['action_id'])->find();
                $array[$key]['realname'] = $driver['realname'];
                $array[$key]['mobile'] = $driver['mobile'];
                $array[$key]['menu_type'] = $value['menu_type']=='1' ? '个人' : '公司';
                $array[$key]['company'] = '';
                if ($value['menu_type'] =='2') {
                    $str_mess = Db::table('ct_company')->where('cid',$driver['companyid'])->find();
                    $array[$key]['company'] = $str_mess['name'];
                }
            }else{
                $username = DB::table('ct_user')->where('uid',$value['action_id'])->find();
                $array[$key]['realname'] = $username['realname'];
                $array[$key]['mobile'] = $username['phone'];
                $array[$key]['menu_type'] ='个人';
                $array[$key]['company'] ='';
            }

        }
        
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$array);
        $this->assign('phone',$phone);
    	return view('toreview/index');
    }
    //审核失败列表
    public function failure(){
        $phone = input('search');
        $search['states'] = 3;
        $pageParam    = ['query' =>[]];
        if (!empty($phone)) {
            $search['account'] = ['like','%'.$phone.'%'];
            $pageParam['query']['search'] = $search;
        }
        $array = array();
        $result = DB::table('ct_application')
                    ->where($search)
                    ->order('start_time','desc')
                    ->paginate(10,false, $pageParam);        
        $results = $result->toArray();
        foreach ($results['data'] as $key => $value){
            $array[$key] = $value;
            if ($value['action_type'] ==1){
                //司机
                $driver = DB::table('ct_driver')->where('drivid',$value['action_id'])->find();
                $array[$key]['realname'] = $driver['realname'];
                $array[$key]['mobile'] = $driver['mobile'];
                $array[$key]['menu_type'] = $value['menu_type']=='1' ? '个人' : '公司';
                $array[$key]['company'] = '';
                if ($value['menu_type'] =='2') {
                    $str_mess = Db::table('ct_company')->where('cid',$driver['companyid'])->find();
                    $array[$key]['company'] = $str_mess['name'];
                }
            }else{
                $username = DB::table('ct_user')->where('uid',$value['action_id'])->find();
                $array[$key]['realname'] = $username['realname'];
                $array[$key]['mobile'] = $username['phone'];
                $array[$key]['menu_type'] ='个人';
                $array[$key]['company'] ='';
            }

        }
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$array);
        $this->assign('phone',$phone);
        return view('toreview/failure');
    }
    //审核成功列表
    public function carryout(){
         $phone = input('search');
        $search['states'] = 2;
        $pageParam    = ['query' =>[]];
        if (!empty($phone)) {
            $search['account'] = ['like','%'.$phone.'%'];
            $pageParam['query']['search'] = $search;
        }
        $array = array();
        $result = DB::table('ct_application')
                    ->where($search)
                    ->order('start_time','desc')
                    ->paginate(10,false, $pageParam);
        $results = $result->toArray();
        foreach ($results['data'] as $key => $value){
            $array[$key] = $value;
            if ($value['action_type'] ==1){
                //司机
                $driver = DB::table('ct_driver')->where('drivid',$value['action_id'])->find();
                $array[$key]['realname'] = $driver['realname'];
                $array[$key]['mobile'] = $driver['mobile'];
                $array[$key]['menu_type'] = $value['menu_type']=='1' ? '个人' : '公司';
                $array[$key]['company'] = '';
                if ($value['menu_type'] =='2') {
                    $str_mess = Db::table('ct_company')->where('cid',$driver['companyid'])->find();
                    $array[$key]['company'] = $str_mess['name'];
                }
            }else{
                $username = DB::table('ct_user')->where('uid',$value['action_id'])->find();
                $array[$key]['realname'] = $username['realname'];
                $array[$key]['mobile'] = $username['phone'];
                $array[$key]['menu_type'] ='个人';
                $array[$key]['company'] ='';
            }

        }
        
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$array);
        $this->assign('phone',$phone);
        return view('toreview/carryout');
    }
    //审核订单操作
    public function modifytype(){
        $type = input('type');
        $id = input('id');
        $admin_id = Session::get('admin_id','admin_mes');

        $getmess = Db::table('ct_application')->where('id',$id)->update(['states'=>$type,'admin_id'=>$admin_id,'end_time'=>time()]);
        $find_mess = Db::table('ct_application')->where('id',$id)->find();
        if ($type == '3') {// 审核失败
            if ($find_mess['action_type'] == '1') { // 司机
              
                if ($find_mess['menu_type'] =='1') {  //司机个人提现
                    //修改费用明细状态并返还提现余额
                    Db::table('ct_balance_driver')->where('orderid',$id)->update(array('action_type'=>'1','order_content'=>'提现失败退款'));
                    $driver = DB::table('ct_driver')->where('drivid',$find_mess['action_id'])->find();
                    if ($find_mess['withdraw_type'] == 1) { //运费提现
                         $count = $driver['money']+$find_mess['actual_money'];
                        $drivid_data['money']=$count;
                    }else{ //个人充值余额提现
                        $count = $driver['balance']+$find_mess['actual_money'];
                        $drivid_data['balance']=$count;
                    }
                    DB::table('ct_driver')->where('drivid',$find_mess['action_id'])->update($drivid_data);
                }else{
                    $driver = DB::table('ct_driver')->alias('a')->join('ct_company c','a.companyid=c.cid')->field('c.money,c.cid')->where('drivid',$find_mess['action_id'])->find();
                    $count = $driver['money']+$find_mess['actual_money'];
                    $drivid_data['money']=$count;
                    DB::table('ct_company')->where('cid',$driver['cid'])->update($drivid_data);
                }
            }else{  //用户提现审核失败
                //修改费用明细状态并返还提现余额
                Db::table('ct_balance')->where('orderid',$id)->update(array('action_type'=>'1','order_content'=>'提现失败退款'));
                $username = DB::table('ct_user')->where('uid',$find_mess['action_id'])->find();
                $count = $username['money']+$find_mess['actual_money'];
                $user_data['money']=$count;
                DB::table('ct_user')->where('uid',$find_mess['action_id'])->update($user_data);
            }
        }else{ // 审核成功
            if($find_mess['withdraw_type'] == 1){ // 运费提现
                // 查询整车订单
                $vehicalOrder = Db::table('ct_userorder')
                            ->where(array('carriersid' => $find_mess['action_id'], 'withdraw' =>3, 'orderstate' => 3))
                            ->select();
                // 查询市配订单
                $cityOrder = Db::table('ct_city_order')
                            ->alias('o')
                            ->join('ct_rout_order r','o.rout_id = r.rid')
                            ->field('o.id,r.finshtime,r.driverid')
                            ->where(array('driverid' => $find_mess['action_id'], 'withdraw' =>3, 'state' => 3))
                            ->select();
                
                // 修改订单的提现状态
                foreach ($cityOrder as $key => $value) {
                    $cityOrderUp = Db::table('ct_city_order')->where('id',$value['id'])->update(array('withdraw'=>1));
                }

                foreach ($vehicalOrder as $key => $value) {
                    $vehicalOrderUp = Db::table('ct_userorder')->where('uoid',$value['uoid'])->update(array('withdraw'=>1));    
                }
            }
        }
        $content = "审核了ID为 ".$id."提现申请";
        $this->hanldlog($this->uid,$content);
        $this->success('操作成功', 'toreview/index');
        
    }
    //打款完成操作
    public function playmoney(){
        $id = input('id');
        $alipaynumber = input('alipaynumber');
        $admin_id = Session::get('admin_id','admin_mes');
         Db::table('ct_application')
         ->where('id',$id)
         ->update([
            'states'=>'4',
            'finance_id'=>$admin_id,
            'alipay_time'=>time(),
            'alipaynumber'=>$alipaynumber
        ]);
         $content = "已向ID为 ".$id."打款";
        $this->hanldlog($this->uid,$content);
         $this->success('操作成功', 'toreview/fighttocomplete');
    }
    //打款完成列表
    public function fighttocomplete(){
        $phone = input('search');
        $search['states'] = 4;
        $pageParam    = ['query' =>[]];
        if ($phone!='') {
            $search['account'] = ['like','%'.$phone.'%'];
            $pageParam['query']['search'] = $search;
        }
        $array = array();
        $result = DB::table('ct_application')
                    ->where($search)
                    ->order('start_time','desc')
                    ->paginate(10,false, $pageParam);
        $results = $result->toArray();
        foreach ($results['data'] as $key => $value){
            $array[$key] = $value;
            if ($value['action_type'] ==1){
                //司机
                $driver = DB::table('ct_driver')->where('drivid',$value['action_id'])->find();
                $array[$key]['realname'] = $driver['realname'];
                $array[$key]['mobile'] = $driver['mobile'];
            }else{
                $username = DB::table('ct_user')->where('uid',$value['action_id'])->find();
                $array[$key]['realname'] = $username['realname'];
                $array[$key]['mobile'] = $username['phone'];
            }

        }
        
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$array);
        $this->assign('phone',$phone);
        return view('toreview/fighttocomplete');
    }
    //提现单详情
    public function details(){
        $result = Db::table('ct_application')->where('id',input('id'))->find();
        if ($result['action_type'] ==1){
                //司机
            $driver = DB::table('ct_driver')->where('drivid',$result['action_id'])->find();
            $result['realname'] = $driver['realname'];
            $result['mobile'] = $driver['mobile'];
        }else{
            $username = DB::table('ct_user')->where('uid',$result['action_id'])->find();
            $result['realname'] = $username['realname'];
            $result['mobile'] = $username['phone'];
        }
        $admin_name = Db::field('realname')->table('ct_admin')->where('aid',$result['admin_id'])->find();
        $admin_name1 = Db::field('realname')->table('ct_admin')->where('aid',$result['finance_id'])->find();
        $result['admin_id'] = $admin_name['realname'];
        $result['finance_id'] = $admin_name1['realname'];
        $this->assign('list',$result);
        
        return view('toreview/details');
    }
}