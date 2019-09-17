<?php
namespace app\backstage\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
/**
  * Author: baobaolong
  */
class Index  extends Base
{
	function __construct(){

        parent::__construct();
        //$this->if_login();
        $this->usertype = Session::get('carrier_usertype','carrier_mes');
    }
	//首页
    public function index(){
        $this->if_login();
        $carrier_id['b.drivid'] = Session::get('carrier_id','carrier_mes');
        $company = Db::field('a.name,b.type')->table('ct_company')->alias('a')->join('ct_driver b','b.companyid=a.cid')->where($carrier_id)->find();
        $this->assign('arr',$company);
        $this->assign('usertype',$this->usertype);
        $this->display('public/menu');
    	return view('index/index'); 
    }

    //首页右边
    public  function main(){
        $this->if_login();
        if ($this->usertype == "driver") {
           // echo "xxx";exit();
            return view('index/main');
        }else{
            return view('index/develop');
        }
       /* $company_id = Db::field('type')->table('ct_driver')->where('drivid',Session::get('carrier_id','carrier_mes'))->find();
        $this->assign('type',$company_id['type']);
        if ($company_id['type'] == '2') { //提货
            return view('index/main_pick');
        }else if($company_id['type'] == '1') { //干线
            return view('index/main');
        }else{ 
             return view('index/develop');
        }*/


    }
   
    public function login(){
        //没有继承controller用view
        return view('index/login'); 
    }

    public function login_send(){
        $carrier_name = Request::instance()->post('carrier_name/s'); 
        $carrier_password = input("carrier_password/s"); 
        $carrier_type = input("carrier_usertype/s");   
        if ($carrier_type == 'driver') {
             $where['mobile'] = $carrier_name;
            $where['password'] = md5($carrier_password.'ct888');
            $result  = Db::table('ct_driver')->where($where)->find();
            Session::set('carrier_id',$result['drivid'],'carrier_mes');
            Session::set('carrier_type',$result['type'],'carrier_mes');
            Session::set('carrier_usertype',$carrier_type,'carrier_mes');
        }else{
            $where['phone'] = $carrier_name;
            $where['password'] = md5($carrier_password.'ct888');
            $result  = Db::table('ct_user')->where($where)->find();
            Session::set('carrier_id',$result['uid'],'carrier_mes');
            Session::set('carrier_type',$result['userstate'],'carrier_mes');
            Session::set('carrier_usertype',$carrier_type,'carrier_mes'); 
        }
       
        if($result){
            
            $this->redirect("Index/index");
        }else{
             $this->error('用户名或密码错误，请重新输入...');
        }  
    }
    //退出
    public function logout(){
        session::clear('carrier_mes');
        $this->redirect("Index/login");
    }

   public function sendordernotice(){
    $this->if_login();
    
    $company_id = Db::field('type')->table('ct_carriers')->where('carrid',Session::get('carrier_id','carrier_mes'))->find();
       /* $count1 = DB::table('ct_order')
            ->alias('a')
            ->join('ct_lineorder b','b.orderid=a.oid')
            ->where(array('paystate'=>'2','affirm'=>'1'))
            ->count('oid');
        $count2 = Db::table('ct_userorder')
                    ->where(array('orderstate'=>'1','paystate'=>'2'))
                    ->count('uoid');
        $count3 = Db::table('ct_rout_order')
                    ->where(array('status'=>'1'))
                    ->count('rid');*/
        if ($company_id['type'] == '3') {
            $list = array();
            return json(['datanum'=>true,'datatype'=>$company_id['type'],'data'=>$list]);
        }else{
           //零担订单
           if($company_id['type'] == '2'){
                $list1 = DB::table('ct_order')
                    ->alias('a')
                    ->join('ct_pickorder b','b.orderid=a.oid')
                    ->where(array('b.status'=>'1'))
                    ->field('a.ordernumber,a.oid')
                    ->select();
                foreach ($list1 as $key => $value) {
                    $list1[$key]['type'] = 1;
                    $list1[$key]['id'] = $value['oid'];
                }
           }elseif($company_id['type'] == '1'){
                $list1 = DB::table('ct_order')
                    ->alias('a')
                    ->join('ct_lineorder b','b.orderid=a.oid')
                    ->where(array('paystate'=>'2','affirm'=>'1'))
                    ->field('a.ordernumber,a.oid')
                    
                    ->select();
                foreach ($list1 as $key => $value) {
                    $list1[$key]['type'] = 1;
                    $list1[$key]['id'] = $value['oid'];
                }
           }
           
            //整车订单
            $list2 = Db::table('ct_userorder')
                        ->where(array('orderstate'=>'1','paystate'=>'2'))
                        ->field('ordernumber,uoid')
                       
                        ->select();
            foreach ($list2 as $key2 => $v) {
                $list2[$key2]['type'] = 2;
                 $list2[$key2]['id'] = $v['uoid'];
            }
            //市配订单
            $list3 = Db::table('ct_rout_order')
                ->where(array('status'=>'1'))
                ->field('rid')
                
                ->select();
             foreach ($list3 as $key3 => $val) {
                $list3[$key3]['type'] = 3;
                $list3[$key3]['id'] = $val['rid'];
            }
             $list = array_merge($list1,$list2,$list3);
            //echo "<pre/>";
            // print_r($list);exit();
            //$count = $count1+$count2+$count3;
           // $data = array('num'=>$count);
            if (!empty($list)) {
                return json(['datanum'=>true,'datatype'=>$company_id['type'],'data'=>$list]);
            }else{
                return json(['datanum'=>false,'datatype'=>$company_id['type'],'data'=>'']);
            } 
        }
        
        
    }
}
