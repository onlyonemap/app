<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Order  extends Base
{
	function __construct(){
        parent::__construct();
        $this->uid = Session::get('admin_id','admin_mes');
        $this->if_login();

    }
    // 客户订单列表
    public function clientorder(){
        //$lineorder_where = '';
        $uid = $this->uid;
        $grade = Db::table('ct_admin')->field('grade')->where('aid',$uid)->find();
        $shift = Db::table('ct_shift')->field('sid')->where('aid',$uid)->select();
        $shiftid =$this->multiToSingle($shift);

        $search = input('search');
        $stime = input('starttime');
        $etime = input('endtime');
        $pageParam    = ['query' =>[]];
        $arr = array();
        if(!empty($search)){ 
            $lineorder_where['a.ordernumber|e.realname|e.phone'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search;
        }
        if (!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $lineorder_where['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] = $stime;
            $pageParam['query']['endtime'] = $etime;
        }
        $str = array();
        $check_str = '1';//是否完成对账
        $lineorder_where['paystate'] = 2;
        $result1= [];
        if ($grade['grade'] == 1){
            $model = new Order();
            $result1 = DB::field('a.*,line.affirm,c.shiftnumber,c.timestrat,c.timeend,c.arrivetimestart,line.lcarr_price,line.line_checkid,
                            c.arrivetimeend,e.userstate,e.username,e.realname,e.phone,e.lineclient,del.pcarr_upprice,
                            line.luseprice,del.puseprice,city.start_id,city.end_id,c.begincityid,c.endcityid,c.pmoney,c.smoney,c.companyid,c.driver_id,c.shiftstate,c.weekday')
                ->table('ct_order')
                ->alias('a')
                ->join('ct_shift c','c.sid=a.shiftid')
                ->join('ct_already_city city','city.city_id=c.linecityid')
                //->join('ct_company d','d.cid=c.companyid',"left")
                // ->join('ct_company dcom','dcom.cid = c.companyid')
                ->join('ct_user e','e.uid=a.userid')
                ->join('ct_lineorder line','line.orderid=a.oid')
                ->join('ct_delorder del','del.orderid=a.oid')
                ->where($lineorder_where)
                ->order('a.oid','desc')
//                ->select();
                ->paginate(50,false, $pageParam);
        }else{
            if (empty($shiftid) ){
                $result1 = [];
            }else{
                foreach($shiftid as $key =>$value){
                    $result = Db::field('a.*,line.affirm,c.shiftnumber,c.timestrat,c.timeend,c.arrivetimestart,line.lcarr_price,line.line_checkid,
                            c.arrivetimeend,e.userstate,e.username,e.realname,e.phone,e.lineclient,del.pcarr_upprice,
                            line.luseprice,del.puseprice,city.start_id,city.end_id,c.begincityid,c.endcityid,c.pmoney,c.smoney,c.companyid,c.driver_id,c.shiftstate,c.weekday')
                        ->table('ct_order')
                        ->alias('a')
                        ->join('ct_shift c','c.sid=a.shiftid')
                        ->join('ct_already_city city','city.city_id=c.linecityid')
                        //->join('ct_company d','d.cid=c.companyid',"left")
                        // ->join('ct_company dcom','dcom.cid = c.companyid')
                        ->join('ct_user e','e.uid=a.userid')
                        ->join('ct_lineorder line','line.orderid=a.oid')
                        ->join('ct_delorder del','del.orderid=a.oid')
                        ->where($lineorder_where)
                        ->where('shiftid',$value)
                        ->order('a.oid','desc')
                        ->select();
//                ->paginate(50,false, $pageParam);
                    $result1 = array_merge($result,$result1);
            }
            }
        }

        $result_data = $result1;
        foreach ($result_data as $key => $value) {
            $pick_cost = DB::field('tprice,driverid,usepprice,tcarr_upprice,pic_checkid')
                        ->table('ct_pickorder')
                        ->where('orderid',$value['oid'])
                        ->find();
            //查找班次负责人
            $shift_driver = Db::table('ct_driver')->where('drivid',$value['driver_id'])->find();
            $value['drivername']=$shift_driver['realname'].'(TEL:'.$shift_driver['mobile'].')';

            $value['shiftnumber'] = $value['shiftstate']=='1' ? $value['shiftnumber'] : $value['weekday'];
          
            //查找下单人
            if ($value['lineclient'] == '') {
                $username = $value['realname']=='' ? $value['username'] : $value['realname'];
                $value['clienter'] = $username .' / '. $value['phone'];
                // 查找业务员
                $value['salesman'] = $this->get_sharename($value['userid']);
            }else{
                $client_com = Db::table('ct_company')->where('cid',$value['lineclient'])->find();
                $value['clienter'] = $client_com['name'];
                // 查找业务员
                $value['salesman'] = $this->get_order_salesman($value['lineclient'],$value['addtime']);
            }
            //查看是否上传完回单   订单状态为配送完成
            $picturn = json_decode($value['receipt'],TRUE);
            if (!empty($picturn)) {
                $value['affirm'] = 4;
            }
            //用户是否支付判断
            if ($value['shiftstate']=='1') {  //当选择平台班次时
                     //查找承运商公司
                    $shiftcompany = Db::table('ct_company')->where('cid',$value['companyid'])->find();
                    $value['drivername']=$shiftcompany['name'];
                }
                // 返回对账信息
                $check_return = $this->checkMessage('1',$value['oid']);
                //用户支付页面信息
                $value['user_pay_mess'] = $check_return['use_pay_state'];
                //应收用户运费
                $value['user_total_money'] =  $check_return['use_ar_money'];
                //实收用户运费
                $value['user_total_upmoney'] =  $check_return['use_ra_money'];
                //应付承运商运费
                $value['driver_total_money'] = $check_return['driver_ap_money'];
                //实付付承运商运费
                $value['driver_total_upmoney'] =  $check_return['driver_pa_money'];
                //承运商应付状态
                $value['check_driver_str'] = $check_return['driver_pay_state'];

                // 查询干线起点城市对应的提货区域
                $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['companyid'],'type'=>1,'province'=>$value['begincityid']))->find();

            // 查询干线终点城市对应的配送区域
                $pei_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['companyid'],'type'=>2,'province'=>$value['endcityid']))->find();

            $value['pmoney'] = $value['shiftstate'] == 1 ? $ti_arr['price'] : $value['pmoney'];
            $value['pmoney'] = round($value['pmoney']);
            // 自主添加配送费
            $value['smoney'] = $value['shiftstate'] == 1 ? $pei_arr['price'] : $value['smoney'];
            $value['smoney'] = round($value['smoney']);

            // 提货时间
            if(strpos($value['picktime'],"月") > 0 || strpos($value['picktime'],"-") > 0) {
                $value['picktime'] = $value['picktime'];
            }else{
                $value['picktime'] = date('Y-m-d H:i:s',$value['picktime']/1000);
            }
            // 起始城市
            $sarr = Db::table('ct_district')->where('id',$value['start_id'])->find();
            switch ($sarr['level']) {
                case 1: // 省
                    $value['startcity'] = $sarr['name'];
                    break;
                case 2: // 市
                    $value['startcity'] = $sarr['name'];
                    break;
                default: // 区
                    $scity = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                    $value['startcity'] = $scity['name'];
                    break;
            }
            // 终点城市
            $earr = Db::table('ct_district')->where('id',$value['end_id'])->find();
            switch ($earr['level']) {
                case 1: // 省
                    $value['endcity'] = $earr['name'];
                    break;
                case 2: // 市
                    $value['endcity'] = $earr['name'];
                    break;
                default: // 区
                    $ecity = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                    $value['endcity'] = $ecity['name'];
                    break;
            }
          
            $arr[] = $value;
        }
//        $page = $result1->render();
        $this->assign('result',$result1);
        $this->assign('list',$arr);
//        $this->assign('page',$page);
        return view('order/clientorder');
    }
    // 客户订单详情
    public function orderdetails(){
        $id = input('id');
        $torder_where['orderid'] = $id;
        $result_where['oid'] = $id;
        $array = Db::field('a.*,c.shiftnumber,c.companyid,c.linecityid,c.timestrat,c.timeend,c.arrivetimestart,c.arrivetimeend,c.weekday,c.driver_id,
                    user.username,user.realname,user.phone,user.userstate,user.lineclient,line.luseprice,del.puseprice,c.shiftstate')
                    ->table('ct_order')
                    ->alias('a')
                    ->join('ct_shift c','c.sid=a.shiftid')
                    ->join('ct_user user','user.uid = a.userid')
                    ->join('ct_lineorder line','line.orderid=a.oid')
                    ->join('ct_delorder del','del.orderid=a.oid')
                    ->where($result_where)
                    ->find();
        $pickorder = Db::field('a.tprice,a.driverid,b.companyid,usepprice')
                    ->table('ct_pickorder')
                    ->alias('a')
                    ->join('ct_driver b','b.drivid = a.driverid','left')
                    ->where($torder_where)
                    ->find();
         $array['tprice'] =$pickorder['tprice'];
        //班次公司名称
        if ($array['shiftstate']=='1') {
            $shift_com = Db::table('ct_company')->field('name')->where('cid',$array['companyid'])->find();
            $array['drivercom'] =$shift_com['name'];
        }else{
            $shift_com = Db::table('ct_driver')->field('realname,mobile')->where('drivid',$array['driver_id'])->find();
            $array['drivercom'] =$shift_com['realname'].'(TEL:'.$shift_com['mobile'].')';
            $array['shiftnumber'] =$array['weekday'];
            //$array['tprice'] = 0;
           // $array['delivecost'] = 0;
        }
        //是否对账完成
        $array['instate'] = '';
        $invo = Db::table('ct_invoice')->where('iid',$array['user_checkid'])->find();
        if (!empty($invo)) {
            $array['instate'] = $invo['instate'];
        }
        // 操作人
        $array['servicename'] = '';
        $array['tel'] = '';
        if($array['serviceid'] !='') {
            $admin = Db::field('username,tel')->table('ct_admin')->where('aid',$array['serviceid'])->find();
            $array['servicename'] = $admin['username'];
            $array['tel'] = $admin['tel'];
        }
        // 下单公司
        if ($array['userstate'] == '1') {
            $array['company'] = '-------';
        }else{
            $search_com = DB::table("ct_company")->where('cid',$array['lineclient'])->find();
            $array['company'] = $search_com['name'];
        }
        // 下单客户
        if($array['realname'] == ''){
            $array['realname'] = $array['username']; 
        }
        // id
        $array['oid'] = $id;
        // 起点终点城市查询
        $city_data = DB::table('ct_already_city')->where('city_id',$array['linecityid'])->find();
        // 起点城市
        $array['startcity'] = detailadd($city_data['start_id'],'','');
        // 终点城市
        $array['endcity'] = detailadd($city_data['end_id'],'','');
        // 提货时间
        if(strpos($array['picktime'],"月") > 0 || strpos($array['picktime'],"-") > 0) {
            $array['picktime'] = $array['picktime'];
        }else{
            $array['picktime'] = date('Y-m-d H:i:s',$array['picktime']/1000);
        }
        //提货地址
        $taddress = array();
        $taddress = json_decode($array['pickaddress'],TRUE);
        if (!empty( $taddress)) {
            foreach ($taddress as $key => $value) {
                $array['taddress'][$key] =  $value['taddressstr'];
            } 
        }
        //配送地址
        $paddress = array();
        $paddress = json_decode($array['sendaddress'],TRUE);
        if (!empty( $paddress)) {
            foreach ($paddress as $key => $value) {
                $array['paddress_arr'][$key]['address'] =  $value['paddressstr'];
                $array['paddress_arr'][$key]['contact'] =  $value['name']."/".$value['phone'];
            } 
        }
        // 回单
        $get_arr = json_decode($array['receipt']);
        $array_get = array();
        if (!empty($get_arr)) {
            foreach ($get_arr as $key => $value) {
                $array_get[] = $value;
            }
        }
        $array['picture'] = $array_get;
        //查找用户反馈内容
        $con_arr = array();
        $contact = Db::table('ct_order_contact')
                    ->where(array('orderid'=>$array['oid'],'otype'=>'1'))
                    ->order('id desc')
                    ->select();
        if (!empty($contact)) {
            foreach ($contact as $key => $value) {
                if ($value['utype']=='1') {
                    $user_mess = Db::table('ct_user')->where(array('uid'=>$value['userid']))->find();
                    $con_arr[$key]['realname'] = $user_mess['realname'];
                    $con_arr[$key]['phone'] = $user_mess['phone'];
                    $con_arr[$key]['image'] = $user_mess['image'];
                }else{
                    $user_mess = Db::table('ct_admin')->where(array('aid'=>$value['userid']))->find();
                    $con_arr[$key]['realname'] = $user_mess['realname'];
                    $con_arr[$key]['phone'] = $user_mess['tel'];
                    $con_arr[$key]['image'] =  get_url().'/static/service_header.png';
                }
                $con_arr[$key]['utype'] = $value['utype'];
                $con_arr[$key]['addtime'] = $value['addtime'];
                $con_arr[$key]['message'] = $value['message'];
            }
        }
        $array['contact'] = $con_arr;
        //到车时间
        $array['deptime'] = $array['starttime'];
        // 总重量 
        $array['endtime'] = $array['arrtime'];
        // 总件数
        $array['totalnumber'] = $array['totalnumber'];
        // 总重量 
        $array['totalweight'] = $array['totalweight'];
        // 总体积
        $array['totalvolume'] = $array['totalvolume'];
        $this->assign('list',$array);
        return view('order/orderdetails');
    }
    // 提货订单列表
    public function pickorder(){
        $pickorder_where['a.paystate'] = 2;
        $pickorder_where['c.shiftstate'] = 1;
        $search = input('search');
        $stime = input('starttime');
        $etime = input('endtime');
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
           $pickorder_where['a.ordernumber'] = ['like','%'.$search.'%'];
           $pageParam['query']['search'] = $search;
        }
        $arr = array();
        $array = Db::table('ct_order')
                  ->field('a.*,b.tprice,b.status,b.orderid,b.pic_checkid,b.usepprice,b.systemorders,user.realname,
                    user.username,user.phone,com.name,user.lineclient,c.companyid')
                  ->alias('a')
                   ->join('ct_shift c','c.sid=a.shiftid')
                  ->join('ct_pickorder b','b.orderid=a.oid') 
                  ->join('ct_user user','user.uid = a.userid')
                  ->join('ct_company com','com.cid = user.lineclient','left')
                  ->where($pickorder_where)
                  ->order('a.oid','desc')
                  ->paginate(80,false, $pageParam);
        $result = $array->toArray();
        foreach ($result['data'] as $key=> $value) {
            $arr[$key] = $value;
            $tprice = number_format($value['tprice'],2);
            // 承运人
            $shift = DB::table('ct_company')
                           ->field('name')
                           ->where('cid',$value['companyid'])
                           ->find();
            $arr[$key]['drivercom'] =  $shift['name'];
            // 提货时间
            $arr[$key]['picktime'] = $value['picktime'];
            //查看是否上传完回单   订单状态为配送完成
            if ($value['status'] !='3') {
                $picturn = json_decode($value['receipt'],TRUE);
                if (!empty($picturn)) {
                    $value['status'] = 3;
                }
            }
            
            //用户返回对账信息
            $check_return = $this->checkMessage('1',$value['oid']);
            //用户支付状态
            $arr[$key]['user_paymess'] = $check_return['use_pay_state'];
            //用户应收提货费
            $arr[$key]['tprice'] = $tprice;
            // 用户实收费用
            $arr[$key]['user_tprice']=  $check_return['user_line_money']['tprice'];
            //承运商支付状态
            $arr[$key]['driver_paymess'] = $check_return['driver_pay_state'];
            //承运商应付提货费
            $arr[$key]['tprice'] = $tprice;
            // 承运商实付实付费用
            $arr[$key]['driver_tprice']= $check_return['driver_line_money']['tprice_driver'];
            
            
        }
        $page = $array->render();
        $this->assign('page',$page);
        $this->assign('list',$arr);
        return view('order/pickorder');
    }
    // 提货订单详情
    public function pickdetails(){
        $orderid_where['a.oid'] = input('id');
        $array = array();
        $result = Db::field('a.oid,a.ordernumber,a.paystate,a.itemtype,a.picktime,a.addtime,a.coldtype,a.orderstate,
                        a.serviceid,a.pickaddress,a.totalnumber,a.totalweight,a.totalvolume,c.beginprovinceid,
                        c.begincityid,c.beginareaid,c.beginaddress,c.companyid,in.instate,p.tprice,p.tcarr_upprice,
                        p.usepprice,p.picid,p.status,p.systemorders,p.driverid,inv.instate carr_instate')
                    ->table('ct_order')
                    ->alias('a')
                    ->join('ct_pickorder p','p.orderid=a.oid') // 提货单
                    ->join('ct_shift c','c.sid=a.shiftid')
                    ->join('ct_invoice in','in.iid = a.user_checkid','LEFT')    //项目客户对账
                    ->join('ct_invoice inv','inv.iid = p.pic_checkid','LEFT')   //承运商对账
                    ->where($orderid_where)
                    ->find();

        // 获取下单客户信息
        $checked = Db::field('b.realname,b.username,com.name,b.phone')
                    ->table('ct_order')
                    ->alias('a')
                    ->join('ct_user b','b.uid=a.userid')
                    ->join('ct_company com','com.cid=b.lineclient','left')
                    ->where($orderid_where)
                    ->find();
        // 下单公司
        if($checked['name'] == ''){
            $array['companyname'] = "------";
        }else{
            $array['companyname'] = $checked['name'];
        }
        // 下单客户
        if($checked['realname'] == ''){
            $array['realname'] = $checked['username'];
        }else{
            $array['realname'] = $checked['realname'];
        }
        $array['telephone'] = $checked['phone'];
        
        // 提货地址
        $taddress = json_decode($result['pickaddress'],TRUE);
        foreach ($taddress as $key => $value) {
            $array['taddress'][$key] =  $value['taddressstr'];
        }
        // 干线公司及管理员信息
        $shiftdriver = Db::field('d.realname,d.username,com.name,d.mobile')
            ->table('ct_company')
            ->alias('com')
            ->join('ct_driver d','d.companyid = com.cid')
            ->where('cid',$result['companyid'])
            ->where(array('cid' => $result['companyid'],'d.type' => 3, 'd.delstate' => 1))
            ->find();
        // 接单人信息
        if($result['systemorders'] == '0'){ // 手动接单为接单人
            $driver = Db::field('d.realname,d.username,com.name,d.mobile')
                    ->table('ct_driver')
                    ->alias('d')
                    ->join('ct_company com','com.cid=d.companyid','left')
                    ->where('drivid',$result['driverid'])
                    ->find();
            $array['drivercom'] = $driver['name'] == '' ? '------' : $driver['name'];
            $array['drivername'] = $driver['realname'] == '' ? $driver['username']:$driver['realname'];
            $array['mobile'] = $driver['mobile'];
        }else{ // 系统自动分配给干线承运商则接单人干线管理员
            $array['drivercom'] = $shiftdriver['name'] == '' ? '------' : $shiftdriver['name'];
            $array['drivername'] = $shiftdriver['realname'] == '' ? $shiftdriver['username'] : $shiftdriver['realname'];
            $array['mobile'] = $shiftdriver['mobile'];
        } 
        

        // 收货人为干线公司管理员
        $array['getcompany'] = $shiftdriver['name'];
        if($shiftdriver['realname'] == ''){
            $array['getname'] = $shiftdriver['username'];
        }else{
            $array['getname'] = $shiftdriver['realname'];
        }
        $array['getmobile'] = $shiftdriver['mobile'];

        // 用户是否对账 1或空  未对账  2已对账
        $array['instate'] = $result['instate']; 
        // 承运商是否对账 1或空  未对账  2已对账 
        $array['carr_instate'] = $result['carr_instate']; 
        // 提货订单ID
        $array['picid'] = $result['picid'];
        // 接单状态 1未接 2已接 3已完成
        $array['pickstatus'] = $result['status'];
        // 提货费用
        $array['tprice'] =  $result['tprice'];
        // 修改后用户提货费
        $array['usepprice'] = $result['usepprice'];
        // 修改后承运商承运费
        $array['tcarr_upprice'] = $result['tcarr_upprice'];
        // 订单号  
        $array['ordernumber'] = $result['ordernumber'];     
        // 支付状态 1未支付 2支付成功  3支付失败
        $array['paystate'] = $result['paystate'];
        // 订单状态1已下单2已支付3订单承接4入始发5订单发出6入终点7已完成8订单取消
        $array['orderstate'] = $result['orderstate'];
        // 物品类别
        $array['itemtype'] = $result['itemtype'];
        // 温度要求
        $array['coldtype'] = $result['coldtype'];
        // 提货时间
        $array['picktime'] = $result['picktime'];
        // 下单时间
        $array['addtime'] = $result['addtime'];
        // 总件数
        $array['totalnumber'] = $result['totalnumber'];
        // 总重量
        $array['totalweight'] = $result['totalweight'];
        // 总体积
        $array['totalvolume'] = $result['totalvolume'];
        // 配送地址即干线始发仓地址
        $address_p = detailadd($result['beginprovinceid'],$result['begincityid'],$result['beginareaid']);
        $array['psaddress'] = $address_p.$result['beginaddress'];

        $this->assign('list',$array);
        return view('order/pickdetails');
    }
    // 干线订单列表
    public function lineorder(){
        $search = input('search');
        $stime = input('starttime');
        $etime = input('endtime');
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
           $line_where['a.ordernumber'] = ['like','%'.$search.'%'];
           $pageParam['query']['search'] = $search;
        }
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $line_where['a.addtime'] = array(array('EGT',$starttime),array('ELT', $endtime));
            $pageParam['query']['starttime'] =$stime;
            $pageParam['query']['endtime'] = $etime;
        }
        // 只拉取支付成功的订单
        $line_where['a.paystate'] = 2;
                
        $result = Db::field('a.*,c.shiftnumber,line.affirm,line.status,c.linecityid,c.timestrat,
                            c.timeend,c.arrivetimestart,c.arrivetimeend,line.luseprice,u.lineclient,
                            c.shiftstate,c.driver_id,com.name shiftcompany')
                    ->table('ct_order')
                    ->alias('a')
                    ->join('ct_user u','u.uid=a.userid')
                    ->join('ct_lineorder line','line.orderid=a.oid')
                    ->join('ct_shift c','c.sid=a.shiftid')
                    ->join('ct_company com','com.cid=c.companyid')
                    ->where($line_where)
                    ->order('a.oid','desc')
                    ->paginate(80,false, $pageParam);      
        $result_data = $result->toArray();
        foreach ($result_data['data'] as $key => $value) {
            $city_data = Db::table('ct_already_city')->where('city_id',$value['linecityid'])->find();
            $name = $this->start_city($city_data['start_id']);
            $name1 = $this->start_city($city_data['end_id']);
            $array = $value;
            // 起点城市
            $array['startcity'] = $name;
            // 终点城市
            $array['endcity'] = $name1;
            //回单是否上传，判断状态
            $picturn = json_decode($value['receipt'],TRUE);
            if (!empty($picturn)) {
                $array['affirm'] = 4;
            }
            $check_return = $this->checkMessage('1',$value['oid']);
            //实际干线运费金额
            $line_pay_ment =  number_format($value['linepice'],2);
            //应收用户支付信息
            $array['user_paymess'] = $check_return['use_pay_state'];
            //应收用户金额
            $array['linepice'] =  $line_pay_ment;
            //实收用户金额
            $array['user_linepice'] =  $check_return['user_line_money']['linepice'];
            //应付承运商支付信息
            $array['driver_paymess'] = $check_return['driver_pay_state'];
            //应付承运商金额
            $array['linepice'] =  $line_pay_ment;
            //实付承运商金额
            $array['driver_linepice'] =  $check_return['driver_line_money']['linepice_driver'];
            $result[$key] = $array;
        }
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$result);
        return view('order/lineorder');
    }
    // 干线订单详情
    public function linedetails(){
        $id = input('id');
        $torder_where['orderid'] = $id;
        $result_where['a.oid'] = $id;
        $result = Db::field('a.ordernumber,a.serviceid,a.linepice,a.orderstate,a.coldtype,a.itemtype,in.instate,
                            a.paystate,a.delivecost,a.pickaddress,a.sendaddress,a.totalnumber,a.totalweight,a.totalvolume,a.arrtime endtime,a.starttime deptime,p.systemorders,p.driverid as tdriverid,
                            c.shiftnumber,c.companyid,c.linecityid,c.timestrat,c.timeend,c.arrivetimestart,c.arrivetimeend,c.beginprovinceid,c.begincityid,c.beginareaid,c.beginaddress,c.endprovinceid,c.endcityid,c.endareaid,c.endaddress,
                            l.affirm,l.driverid,l.luseprice,l.lcarr_price,
                            l.lid,al.start_id,al.end_id,inv.instate carr_instate')
                    ->table('ct_order')
                    ->alias('a')
                    ->join('ct_lineorder l','l.orderid = a.oid')
                    ->join('ct_pickorder p','p.orderid = a.oid')
                    ->join('ct_shift c','c.sid=a.shiftid')
                    ->join('ct_already_city al','al.city_id=c.linecityid')
                    ->join('ct_invoice in','in.iid=a.user_checkid','LEFT')      //项目客户对账
                    ->join('ct_invoice inv','inv.iid = l.line_checkid','LEFT')   //承运商对账
                    ->where($result_where)
                    ->find();
        
       
        $array = $result;
        // 干线订单
        $array['lid'] = $result['lid']; 
        // 起点城市
        $array['startcity'] = $this->start_city($result['start_id']);
        // 终点城市
        $array['endcity'] = $this->start_city($result['end_id']);
        // 是否确认接单1未确认2已确认 3 系统确认
        $array['linestatus'] = $result['affirm'];
        // 用户修改干线价格
        $array['luseprice'] = $result['luseprice']; 
        // 承运商修改干线价格
        $array['lcarr_price'] = $result['lcarr_price']; 
        
        
        
        // 获取发货人电话信息 为提货单接单人或为干线公司及管理员信息
        $shiftdriver = Db::field('d.realname,d.username,com.name,d.mobile')
            ->table('ct_company')
            ->alias('com')
            ->join('ct_driver d','d.companyid = com.cid')
            ->where('cid',$result['companyid'])
            ->where(array('cid' => $result['companyid'],'d.type' => 3, 'd.delstate' => 1))
            ->find();
        
        if($result['systemorders'] == '0'){ // 如果提货单为手动接单则发货人为提货公司
            $driver = Db::field('d.realname,d.username,com.name,d.mobile')
                    ->table('ct_driver')
                    ->alias('d')
                    ->join('ct_company com','com.cid=d.companyid','left')
                    ->where('drivid',$result['tdriverid'])
                    ->find();
            if($driver['name'] == ''){
                $array['company'] = '------';
            }else{
                $array['company'] = $driver['name'];
            }
            if($driver['realname'] == ''){
                $array['username'] = $driver['username'];
            }else{
                $array['username'] = $driver['realname'];
            }
            $array['telephone'] = $driver['mobile'];
        }else{ // 系统自动分配给干线承运商则接单人干线管理员
            if($shiftdriver['name'] == ''){
                $array['company'] = '------';
            }else{
                $array['company'] = $shiftdriver['name'];
            }
            if($shiftdriver['realname'] == ''){
                $array['username'] = $shiftdriver['username'];
            }else{
                $array['username'] = $shiftdriver['realname'];
            }
            $array['telephone'] = $shiftdriver['mobile'];
        }

        // 接单信息
        $shiftdriver = Db::field('d.realname,d.username,com.name,d.mobile')
            ->table('ct_company')
            ->alias('com')
            ->join('ct_driver d','d.companyid = com.cid')
            ->where('cid',$result['companyid'])
            ->where(array('cid' => $result['companyid'],'d.type' => 3, 'd.delstate' => 1))
            ->find();
        // 接单人信息
        if($result['affirm'] == '2'){ // 手动接单为接单人
            $driver = Db::field('d.realname,d.username,com.name,d.mobile')
                    ->table('ct_driver')
                    ->alias('d')
                    ->join('ct_company com','com.cid=d.companyid','left')
                    ->where('drivid',$result['driverid'])
                    ->find();
            if($driver['name'] == ''){
                $array['drivercom'] = '------';
            }else{
                $array['drivercom'] = $driver['name'];
            }
            if($driver['realname'] == ''){
                $array['drivername'] = $driver['username'];
            }else{
                $array['drivername'] = $driver['realname'];
            }
            $array['mobile'] = $driver['mobile'];
        }else{ // 系统自动分配给干线承运商则接单人干线管理员
            if($shiftdriver['name'] == ''){
                $array['drivercom'] = '------';
            }else{
                $array['drivercom'] = $shiftdriver['name'];
            }
            if($shiftdriver['realname'] == ''){
                $array['drivername'] = $shiftdriver['username'];
            }else{
                $array['drivername'] = $shiftdriver['realname'];
            }
            $array['mobile'] = $shiftdriver['mobile'];
        }
        // 收货人为干线公司管理员
        $array['getcompany'] = $shiftdriver['name'];
        if($shiftdriver['realname'] == ''){
            $array['getname'] = $shiftdriver['username'];
        }else{
            $array['getname'] = $shiftdriver['realname'];
        }
        $array['getmobile'] = $shiftdriver['mobile'];






        $array['servicename'] = '';
        $array['tel'] = '';
        if($result['serviceid'] !='') {
            $admin = Db::field('username,tel')->table('ct_admin')->where('aid',$result['serviceid'])->find();
            $array['servicename'] = $admin['username'];
            $array['tel'] = $admin['tel'];
        }
        $taddress=array();
        $paddress=array();
        
        // 提货地址为干线始点仓库
        if($result['beginareaid'] == ''){ // 没有添加干线终点仓
            $array['tsaddress'] = '没有干线终点仓';
        }else{
            $address_t = detailadd($result['beginprovinceid'],$result['begincityid'],$result['beginareaid']);
            $array['tsaddress'] = $address_t.$result['beginaddress'];
        }
        // 配送地址为干线终点点仓库
        if($result['endareaid'] == ''){ // 没有添加干线终点仓
            $array['psaddress'] = '没有干线终点仓';
        }else{
            $address_p = detailadd($result['endprovinceid'],$result['endcityid'],$result['endareaid']);
            $array['psaddress'] = $address_t.$result['endaddress'];
        }

        $array['coldtype'] = $result['coldtype']; //冷藏类型
        $array['itemtype'] = $result['itemtype']; //物品类型
        $array['weight'] = $result['totalweight']; //总件数
        $array['volume'] = $result['totalvolume']; //总重量
        $array['number'] = $result['totalnumber']; //总体积
        $this->assign('list',$array);
        return view('order/linedetails');
    }
    // 修改总价格
    public function upprice(){
        $post = Request::instance()->post();
        $carrier_id = Session::get('admin_id','admin_mes');
        if ($post['ajax'] == 1) {
            $oid = $post['oid'];
            $update_data['totalcost'] = $post['price'];
            $update_data['serviceid'] = $carrier_id;
            $o_result = Db::table('ct_order')->alias('a')->join('ct_pickorder p','p.orderid=a.oid')->where('oid',$oid)->find();
            if ($o_result['user_checkid'] !='') {
                $invo = Db::table('ct_invoice')->where('iid',$o_result['user_checkid'])->find();
                $price = $o_result['tprice'] + $o_result['linepice'] + $o_result['delivecost'];
                $get_price = intval($price)-intval($post['price']);
                $invo_price = $invo['totalprice'] - $get_price;
                $invo_data['totalprice']= $invo_price;
                if ($invo['self_total'] !='') {
                  $self_price = $invo['self_total'] - $get_price;
                  $invo_data['self_total']= $self_price;
                }
                Db::table('ct_invoice')->where('iid',$o_result['user_checkid'])->update($invo_data);
             }
            $up = DB::table('ct_order')->where('oid',$oid)->update($update_data);
        }
    }
    // 配送订单列表
    public function delorder(){
        $line_where['a.paystate'] = 2;
        $search = input('search');
        $stime = input('starttime');
        $etime = input('endtime');
        $array = array();
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
            $line_where['a.ordernumber'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search;
        }
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $line_where['a.addtime'] = array(array('EGT',$starttime),array('ELT', $endtime));
            $pageParam['query']['starttime'] =$stime;
            $pageParam['query']['endtime'] = $etime;
        }  

        $result = Db::field('a.*,u.lineclient,u.realname,u.username,u.phone,com.name,li.affirm,c.shiftnumber,pei.status,pei.puseprice')
                ->table('ct_order')
                ->alias('a')
                ->join('ct_user u','u.uid = a.userid')
                ->join('ct_delorder pei','pei.orderid = a.oid')
                ->join('ct_shift c','c.sid = a.shiftid')
                ->join('ct_company com','com.cid = c.companyid')
                ->join('ct_lineorder li','li.orderid = a.oid')
                ->where($line_where)
                ->order('a.oid','desc')
                ->paginate(80,false, $pageParam);
         $result_data = $result->toArray();
        foreach ($result_data['data'] as $key => $value) {            
            $array = $value;
            //返回对账信息
            if ($value['lineclient'] =='' || $value['lineclient'] ==0) {
                $username = $value['realname']=='' ? $value['username'] : $value['realname'];
                $array['clienter'] = $username .' / '. $value['phone'];
            }else{
                $client_com = Db::table('ct_company')->where('cid',$value['lineclient'])->find();
                $array['clienter'] = $client_com['name'];
            }
            if ($value['status'] != 3) {
                $picturn = json_decode($value['receipt'],TRUE);
                if (!empty($picturn)) {
                    $value['status']=4;
                }
            }
            $check_return = $this->checkMessage('1',$value['oid']);
            //应收用户支付信息
            $array['user_paymess'] = $check_return['use_pay_state'];
            //应收用户金额
            $array['delivecost'] =  number_format($value['delivecost'],2);
            //实收用户金额
            $array['user_delivecost'] =  $check_return['user_line_money']['delivecost'];
            //应付承运商支付信息
            $array['driver_paymess'] =  $check_return['driver_pay_state'];
            //应付承运商金额
            $array['delivecost'] =  number_format($value['delivecost'],2);
            //实付承运商金额
            $array['driver_delivecost'] =  $check_return['driver_line_money']['delivecost_driver'];
            // 承运人
            $pei = Db::table('ct_delorder')->where('orderid',$value['oid'])->find();
        
            $array['drivercom'] = $value['name'];
           
            
            // 配送费
            $result[$key] = $array;
        }
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$result);
        return view('order/delorder');
    }
    // 配送订单详情
    public function deldetails(){
        $id = input('id');
        $torder_where['orderid'] = $id;
        $result_where['a.oid'] = $id;
        $result = Db::field('a.*,li.affirm,
                            c.companyid,a.starttime deptime,a.arrtime endtime,
                            c.linecityid,c.timestrat,c.timeend,c.arrivetimestart,c.arrivetimeend,c.endprovinceid,c.endcityid,c.endareaid,c.endaddress,
                            in.instate,d.status,d.puseprice,d.driverid as peidriverid,
                            d.deid,d.pcarr_upprice,inv.instate carr_instate')
                    ->table('ct_order')
                    ->alias('a')
                    ->join('ct_delorder d','d.orderid=a.oid')
                    ->join('ct_lineorder li','li.orderid = a.oid')
                    ->join('ct_shift c','c.sid=a.shiftid')
                    ->join('ct_invoice in','in.iid = a.user_checkid','LEFT')
                    ->join('ct_invoice inv','inv.iid = li.line_checkid','LEFT')
                    ->where($result_where)
                    ->find();
        $array = $result;

        // 下单人为干线公司 联系人为管理员
        $user = Db::field('com.name,d.realname,d.username,d.mobile')
              ->table('ct_company')
              ->alias('com')
              ->join('ct_driver d','d.companyid = com.cid')
              ->where(array('d.type' => 3, 'd.delstate' => 1,'com.cid' => $result['companyid']))
              ->find();
        $array['company'] = $user['name'];
        if($user['realname'] == ''){
            $array['username'] = $user['username'];    
        }else{
            $array['username'] = $user['realname'];    
        }
        $array['phone'] = $user['mobile'];

        // 接单人手动接单为接单人自动接单为干线公司
        if($result['peidriverid'] == ''){ // 自动接单
            $array['drivercom'] = $array['company'];
            $array['drivername'] = $array['username'];
            $array['mobile'] = $user['mobile'];
        }else{ // 手动接单
            $driver = Db::field('com.name,d.realname,d.username,d.mobile')
              ->table('ct_company')
              ->alias('com')
              ->join('ct_driver d','d.companyid = com.cid')
              ->where(array('d.type' => 3, 'd.delstate' => 1,'d.drivid' => $result['peidriverid']))
              ->find();
            if($driver['name'] == ''){
                $array['drivercom'] = '------';
            }else{
                $array['drivercom'] =  $driver['name'];
            }
            if($driver['realname'] == ''){
                $array['drivername'] = $driver['username'];    
            }else{
                $array['drivername'] = $driver['realname'];    
            }
            $array['mobile'] = $driver['mobile'];
        }        

        // 配送订单ID
        $array['deid'] = $result['deid'];  
        // 配送订单状态
        $array['linestatus'] = $result['status']; 
        // 配送修改后运费
        $array['puseprice'] = $result['puseprice']; 
        // 承运商配送修改后运费
        $array['pcarr_upprice'] = $result['pcarr_upprice']; 
        // 回单
        $get_arr = json_decode($result['receipt']);
        $array_get = array();
        if (!empty($get_arr)) {
            foreach ($get_arr as $key => $value) {
                $array_get[] = $value;
            }
        }
        $array['picture'] = $array_get;
        // 提货地址为干线终点仓库
        if($result['endareaid'] == ''){ // 没有添加干线终点仓
            $array['tsaddress'] = '没有干线终点仓';
        }else{
            $address_t = detailadd($result['endprovinceid'],$result['endcityid'],$result['endareaid']);
            $array['tsaddress'] = $address_t.$result['endaddress'];
        }
        
        // 配送地址
        $array['paddress']=array();
        $paddress = json_decode($result['sendaddress'],TRUE);
        if (!empty($paddress)) {
            foreach ($paddress as $key => $value) {
                $array['paddress'][$key]['address'] =  $value['paddressstr'];
                $array['paddress'][$key]['contact'] =  $value['name']."/".$value['phone'];
            } 
        }
        // 项目客户是否发生对账
        $array['instate'] = $result['instate']; 
        // 承运商是否发生对账
        $array['carr_instate'] = $result['carr_instate']; 
        // 冷藏类型
        $array['coldtype'] = $result['coldtype']; 
        // 物品类型
        $array['itemtype'] = $result['itemtype']; 
        // 总重量
        $array['weight'] = $result['totalweight']; 
        // 总体积
        $array['volume'] = $result['totalvolume']; 
        // 总件数
        $array['number'] = $result['totalnumber']; 
        $this->assign('list',$array);
        return view('order/deldetails');
    }

    /*
    *用户修改干线运费
    */
    public function pick_user_price(){
        $postdata = Request::instance()->post();
        $price = $postdata['price'];
        $id = $postdata['oid'];
        $ordertype = $postdata['ordertype'];  //1、提货订单2、干线订单 3、配送订单
        if ($postdata['ajax'] =='1') {
            if ($ordertype=='1') { //配送
                $where['picid'] = $id;
            }elseif ($ordertype=='2'){ //干线
                $where['lid'] = $id;
            }elseif ($ordertype=='3'){//配送
                $where['deid'] = $id;
            }
            $order_mess = Db::table('ct_order')
                        ->alias('o')
                        ->join('ct_pickorder a','a.orderid=o.oid')
                        ->join('ct_lineorder l','l.orderid=o.oid')
                        ->join('ct_delorder d','d.orderid=o.oid')
                        ->field('a.picid,o.oid,a.tprice,o.delivecost,o.linepice,o.totalcost,o.user_checkid,l.lid,d.deid')
                        ->where($where)
                        ->find();
            if ($ordertype=='1') { //提货
                $subtract = $order_mess['tprice']-$price;
            }elseif ($ordertype=='2'){ //干线
                $subtract = $order_mess['linepice']-$price;
            }elseif ($ordertype=='3'){//配送
                $subtract = $order_mess['delivecost']-$price;
            }
            if ($order_mess['totalcost'] !='' ) {  //查找是否有修改后的总价  有则减
                $order_price = $order_mess['totalcost']-$subtract;
                $data_order['totalcost'] = $order_price;
                Db::table('ct_order')->where('oid',$order_mess['oid'])->update($data_order);
            }
            if ($order_mess['user_checkid'] !='') {  //查看是否已存在对账单
                $invo = Db::table('ct_invoice')->where('iid',$f_result['user_checkid'])->find();
                $invo_price = $invo['totalprice'] - $subtract;  //有则减掉或加越
                $invo_data['totalprice']= $invo_price;
                if ($invo['self_total'] !='') {  //查找平台是否修改对账总额
                    $self_price = $invo['self_total'] - $subtract;    //有则减掉或加越
                    $invo_data['self_total']= $self_price;
                }
                Db::table('ct_invoice')->where('iid',$f_result['user_checkid'])->update($invo_data);
            }
            if ($ordertype=='1') { //配送
                $data_pick['usepprice'] = $price;
                $res = Db::table('ct_pickorder')->where('picid',$id)->update($data_pick);
            }elseif ($ordertype=='2') { //干线
                $data_pick['luseprice'] = $price;
                $res = Db::table('ct_lineorder')->where('lid',$id)->update($data_pick);
            }else{
                $data_pick['puseprice'] = $price;
                $res = Db::table('ct_delorder')->where('deid',$id)->update($data_pick);
            }
            
        }
    }

    /*
    *承运商修改干线运费
    */
    public function pick_carr_price(){
        $postdata = Request::instance()->post();
        $price = $postdata['price'];
        $id = $postdata['oid'];
        $ordertype = $postdata['ordertype'];  //1、提货订单2、干线订单 3、配送订单
        if ($postdata['ajax'] =='1') {
            if ($ordertype=='1') { //配送
                $where['picid'] = $id;
            }elseif ($ordertype=='2'){ //干线
                $where['lid'] = $id;
            }elseif ($ordertype=='3'){//配送
                $where['deid'] = $id;
            }
            $order_mess = Db::table('ct_order')
                        ->alias('o')
                        ->join('ct_pickorder a','a.orderid=o.oid')
                        ->join('ct_lineorder l','l.orderid=o.oid')
                        ->join('ct_delorder d','d.orderid=o.oid')
                        ->field('a.picid,o.oid,a.tprice,a.pic_checkid,o.delivecost,o.linepice,o.totalcost,o.user_checkid,l.lid,l.line_checkid,d.deid')
                        ->where($where)
                        ->find();
            if ($ordertype=='1') { //提货
                $subtract = $order_mess['tprice']-$price;
                $checkid = $order_mess['pic_checkid'];
            }elseif ($ordertype=='2'){ //干线
                $subtract = $order_mess['linepice']-$price;
                $checkid = $order_mess['line_checkid'];
            }elseif ($ordertype=='3'){//配送
                $subtract = $order_mess['delivecost']-$price;
                $checkid = $order_mess['line_checkid'];
            }
            
            if ($checkid !='') {  //查看是否已存在对账单
                $invo = Db::table('ct_invoice')->where('iid',$checkid)->find();
                $invo_price = $invo['totalprice'] - $subtract;  //有则减掉或加越
                $invo_data['totalprice']= $invo_price;
                if ($invo['carr_total'] !='') {  //查找平台是否修改对账总额
                    $self_price = $invo['carr_total'] - $subtract;    //有则减掉或加越
                    $invo_data['carr_total']= $self_price;
                }
                Db::table('ct_invoice')->where('iid',$checkid)->update($invo_data);
            }
            if ($ordertype=='1') { //配送
                $data_pick['tcarr_upprice'] = $price;
                $res = Db::table('ct_pickorder')->where('picid',$id)->update($data_pick);
            }elseif ($ordertype=='2') { //干线
                $data_pick['lcarr_price'] = $price;
                $res = Db::table('ct_lineorder')->where('lid',$id)->update($data_pick);
            }else{
                $data_pick['pcarr_upprice'] = $price;
                $res = Db::table('ct_delorder')->where('deid',$id)->update($data_pick);
            }
            
        }
    }

    /**
     * 定制线路订单列表
     */
    public function customlineorder() {
        $line_where['affirm'] = 2;
        //$line_where['paystate'] = 2;
        $search = input('search');
        $stime = input('starttime');
        $etime = input('endtime');
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
            $line_where['a.ordernumber|c.name|carrr.name'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search;
        }
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $line_where['a.addtime'] = array(array('EGT',$starttime),array('ELT', $endtime));
            $pageParam['query']['starttime'] =$stime;
            $pageParam['query']['endtime'] = $etime;
        }
        $result = Db::field('a.*,u.phone,u.username,u.realname,u.lineclient,c.name,carrr.name as carriername,city.start_id,city.end_id')
                    ->table('ct_shift_order')
                    ->alias('a')
                    ->join('ct_user u','u.uid = a.userid')
                    ->join('ct_fixation_line f','f.id=a.shiftid')
                    ->join('ct_already_city city','city.city_id = f.lienid')
                    ->join('ct_company c','c.cid = f.companyid')
                    ->join('ct_company carrr','carrr.cid = f.carrierid')
                    ->where($line_where)
                    ->order('a.s_oid','desc')
                    ->paginate(80,false, $pageParam);
        $arr = array();
        $str_start ='';
        $str_end ='';
        $check_str = '1'; //识别订单是否完成对账
        $str_city_end = '';
        $result_data = $result->toArray();
        foreach ($result_data['data'] as $key => $value) {
           $arr[$key] = $value;


            
            //起始城市
            $start_area = Db::table('ct_district')->where('id',$value['start_id'])->find();
            $arr[$key]['startcity'] = $start_area['name'];
            if ($start_area['level'] =='3') {
                $str_city = Db::table('ct_district')->where('id',$start_area['parent_id'])->find();
                $str_start = $str_city['name'];
                $arr[$key]['startcity'] = $str_city['name'].$start_area['name'];
            }
            //终点城市
            $end_area = Db::table('ct_district')->where('id',$value['end_id'])->find();
            $arr[$key]['endcity'] =$end_area['name'];
            if ($end_area['level'] =='3') {
                $str_city_end = Db::table('ct_district')->where('id',$end_area['parent_id'])->find();
                $str_end = $str_city_end['name'];
                $arr[$key]['endcity'] = $str_city_end['name'].$end_area['name']; //终点城市名称
            }
            // 查找业务员
            $arr[$key]['salesman'] = $this->get_order_salesman($value['lineclient'],$value['addtime']);
            //返回对账信息
            $check_return = $this->checkMessage('4',$value['s_oid']);
            //应收客户信息
            $arr[$key]['paystatr'] = $check_return['use_pay_state'];
            //应收客户运费
            $arr[$key]['actualprice'] = $check_return['use_ar_money'];
            //实收客户运费
            $arr[$key]['payprice'] = $check_return['use_ra_money'];

            //应付承运商信息
            $arr[$key]['driver_mess'] = $check_return['driver_pay_state'];
             //实付承运商运费
            $arr[$key]['driver_pay'] = $check_return['driver_pa_money'];
            //应付承运商运费
            $arr[$key]['driver_payment'] = $check_return['driver_ap_money']; 
        }  
        $page = $result->render();        
        $this->assign('list',$arr);
        $this->assign('page',$page);
        return view('order/customlineorder');
    }
    
    /**
     * 定制线路订单详情
     * @Auther: 李渊
     * @Date: 2018.8.1
     * @return [type] [description]
     */
    public function customlinedetail() {
        // 获取订单id
        $id = input('id');
        // 查询订单数据
        $result = Db::field('a.*,u.phone,u.username,u.realname,f.companyid,f.carr_price,f.carrierid,f.trans_mess,
                            c.start_id,c.end_id,in.instate,d.username as dusername,d.realname as drealname,d.mobile as dmobile,
                            inv.instate carr_instate')
                    ->table('ct_shift_order')
                    ->alias('a')
                    ->join('ct_user u','u.uid = a.userid')
                    ->join('ct_fixation_line f','f.id=a.shiftid')
                    ->join('ct_driver d','d.drivid=a.driverid','LEFT')
                    ->join('ct_already_city c','c.city_id=f.lienid')
                    ->join('ct_invoice in','in.iid=a.user_checkid','LEFT')
                    ->join('ct_invoice inv','inv.iid=a.carr_checkid','LEFT')
                    ->where('s_oid',$id)
                    ->find();
            $str_start ='';
            $str_end='';
            $check_str = 1;
            $start_area = Db::table('ct_district')->where('id',$result['start_id'])->find();
            if ($start_area['level'] =='3') {
                $str_city = Db::table('ct_district')->where('id',$start_area['parent_id'])->find();
                $str_start = $str_city['name'];
            }
            $result['startcity'] = $str_start.$start_area['name'];
            //终点城市
            $end_area = Db::table('ct_district')->where('id',$result['end_id'])->find();
            if ($end_area['level'] =='3') {
                $str_city_end = Db::table('ct_district')->where('id',$end_area['parent_id'])->find();
                $str_end = $str_city_end['name'];
            }
            $result['endcity'] = $str_end.$end_area['name']; //终点城市名称
            $user_com = Db::table('ct_company')->where('cid',$result['companyid'])->find();
            $carr_com = Db::table('ct_company')->where('cid',$result['carrierid'])->find();
            if ($result['usercheck']=='2') {
                $invo_data = Db::table('ct_invoice')->where('iid',$result['user_checkid'])->find();
                $check_str = $invo_data['instate'];
            }
            $json = json_decode($result['trans_mess'],TRUE);
            $driver_str = '';
            $carnumber = '';
            foreach ($json as $key => $value) {
                $s_driver = DB::table('ct_driver')->where('drivid',$value['driverid'])->find();
                $driver_str .= $s_driver['realname']=='' ? $s_driver['username'] :$s_driver['realname']."(TEL".$s_driver['mobile'].") ".' / ';
                $s_carnumber = Db::table('ct_carcategory')->where('ccid',$value['carid'])->find();
                $carnumber .= $s_carnumber['carnumber'] .' / ';
            }
        
             //查找用户反馈内容
            $con_arr = array();
            $contact = Db::table('ct_order_contact')
                        ->where(array('orderid'=>$result['s_oid'],'otype'=>'2'))
                        ->order('id desc')
                        ->select();
            if (!empty($contact)) {
                foreach ($contact as $key => $value) {
                    if ($value['utype']=='1') {
                        $user_mess = Db::table('ct_user')->where(array('uid'=>$value['userid']))->find();
                        $con_arr[$key]['realname'] = $user_mess['realname'];
                        $con_arr[$key]['phone'] = $user_mess['phone'];
                        $con_arr[$key]['image'] = $user_mess['image'];
                    }else{
                        $user_mess = Db::table('ct_admin')->where(array('aid'=>$value['userid']))->find();
                        $con_arr[$key]['realname'] = $user_mess['realname'];
                        $con_arr[$key]['phone'] = $user_mess['tel'];
                        $con_arr[$key]['image'] =  get_url().'/static/service_header.png';
                    }
                    $con_arr[$key]['utype'] = $value['utype'];
                    $con_arr[$key]['addtime'] = $value['addtime'];
                    $con_arr[$key]['message'] = $value['message'];
                }
            }
            $result['contact'] = $con_arr;
            $result['driver_number'] = rtrim($carnumber,' / ');
            $result['driver_mess'] = rtrim($driver_str,' / ');
            $result['check_str'] = $check_str;
            $result['user_com'] = $user_com['name'];
            $result['carr_com'] = $carr_com['name'];
            $this->assign('list',$result);
            return view('order/customlinedetail');
    }

    /**
     * 定制线路修改价格
     * @Auther: 李渊
     * @Date: 2018.8.1
     * @return [type] [description]
     */
    public function line_order(){
        $postdata = Request::instance()->post();
        // 获取修改的类型 1 客户价格 2 承运价格
        $otype = $postdata['otype'];
        // 获取修改的价格
        $post_price = $postdata['price'];
        // 获取修改的订单id
        $id = $postdata['oid'];
        // 查询订单数据
        $result = Db::table('ct_shift_order')
                        ->alias('o')
                        ->join('ct_fixation_line f','f.id=o.shiftid')
                        ->where('s_oid',$id)
                        ->find();
        // 判断修改的类型 1 客户价格 2 承运价格
        if ($otype=='1') {
            $data['upprice'] = $post_price;
            $price = $result['totalprice'];
            $user_checkid = $result['user_checkid'];
        }else{ // 承运商
            $data['carr_upprice'] = $post_price;
            $price = $result['price'];
            $user_checkid = $result['carr_checkid'];
        }

        $get_price = intval($price)-intval($post_price);
        // 判断是否进行对账
        if ($user_checkid !='') {
            $invo = Db::table('ct_invoice')->where('iid',$user_checkid)->find();
            $invo_price = $invo['totalprice'] - $get_price;
            $invo_data['totalprice']= $invo_price;
            if ($invo['self_total'] !='') {
                $self_price = $invo['self_total'] - $get_price;
                $invo_data['self_total']= $self_price;
            }
            Db::table('ct_invoice')->where('iid',$result['user_checkid'])->update($invo_data);
        }
        // 修改订单价格
        $res = Db::table('ct_shift_order')->where('s_oid',$id)->update($data);
        // 判断是否修改成功
        if($res){
            return json(['code'=>true,'message'=>'修改成功']);
        }else{
            return json(['code'=>false,'message'=>'修改失败']);
        }
    }

    /**
     * 定制线路取消订单
     * @Auther: 李渊
     * @Date: 2018.8.1
     * @return [type] [description]
     */
    public function ordercancel(){
        // 获取订单id
        $orderid = input("id");
        // 查询订单信息
        $sorder_mess = Db::table('ct_shift_order')
                        ->alias('o')
                        ->join('__USER__ u','u.uid = o.userid')
                        ->join('__FIXATION_LINE__ f','f.id=o.shiftid')
                        ->join('__COMPANY__ c','c.cid=f.companyid')
                        ->join('__ALREADY_CITY__ a','a.city_id=f.lienid')
                        ->field('o.s_oid,o.orderstate,o.ordernumber,o.pay_type,o.picktime,o.totalprice,f.carrierid,f.trans_mess,f.paddress,
                            f.ptime,a.start_id,a.end_id,c.cid,c.money,u.uid,u.money usermoney')
                        ->where('s_oid',$orderid)
                        ->find();
        // 判断订单状态 接单、提货、完成、取消状态不能在进行取消操作
        switch ($sorder_mess['orderstate']) {
            case '1': // 未接单
                # code...
                break;
            case '2': // 已接单
                # code...
                break;
            case '3': // 已完成
                return json(['code'=>false,'message'=>'订单已经完成，不可取消']);
                break;
            case '4': // 已取消
                return json(['code'=>false,'message'=>'订单已经取消']);
                break;
            default:
                # code...
                break;
        }
        // 更新订单状态
        $update = Db::table('ct_shift_order')->where('s_oid',$orderid)->update(['orderstate'=>4]);
        // 判断订单是否取消成功
        if($update){
            // 如果是信用支付 费用归还
            if ($sorder_mess['pay_type']=='1') {
                // 计算公司信用额度
                $data['money'] = $sorder_mess['money']+$sorder_mess['totalprice'];
                // 更新公司信用额度
                $res = Db::table('ct_company')->where('cid',$sorder_mess['cid'])->update($data);
            }else{ // 如果是余额、支付宝、微信 费用都返还余额
                $data['money'] = $sorder_mess['usermoney']+$sorder_mess['totalprice'];
                // 更新个人余额
                $res = Db::table('ct_user')->where('uid',$sorder_mess['uid'])->update($data);
               
            }
            // 如果余额恢复则插入操作日志
            if ($res) {
                $content = "取消定制线路订单".$sorder_mess['ordernumber']."，个人余额恢复:".$sorder_mess['totalprice'];
                $this->hanldlog($this->uid,$content);
            }
            
            $list_phone = '';
            $phone_driver ='';
            $phone_leader ='';
            // 判断是否分配有司机
            if (!empty($sorder_mess['trans_mess'])) {
                $arr_mess = json_decode($sorder_mess['trans_mess'],true);
                if (!empty($arr_mess)) {
                    foreach ($arr_mess as $key => $value) {
                        $driver = Db::table('ct_driver')->where('drivid',$value['driverid'])->find();
                        $list_phone .= $driver['mobile'].',';
                    }
                }
                $phone_driver = rtrim ($list_phone,',');
            }
            if(!empty($sorder_mess['carrierid'])){
                $driver_leader = Db::table('ct_driver')->field('mobile')->where(array('companyid'=>$sorder_mess['carrierid'],'type'=>3))->find();
                $phone_leader = $driver_leader['mobile'];
            }
            $str ='';
            if ($phone_driver!='') {
                $str =',';
            }
            $phone_all = $phone_driver.$str.$phone_leader;
            //echo $phone_all;exit();
            $saddress = '';
            if($sorder_mess['paddress'] !='') {
                $address = json_decode($sorder_mess['paddress'],true);
               foreach ($address as $key => $val) {
                $saddress .=$val.'/';
              }
            }
            $city_start = '';
            $city_end = '';
            $startarea = Db::table('ct_district')->where('id',$sorder_mess['start_id'])->find();
            $endarea = Db::table('ct_district')->where('id',$sorder_mess['end_id'])->find();
            if ($startarea['level'] =='3') {
                $startcity = DB::table('ct_district')->where('id',$startarea['parent_id'])->find();
                $city_start = $startcity['name'];
            }
            if ($endarea['level'] =='3') {
                $startcity = DB::table('ct_district')->where('id',$endarea['parent_id'])->find();
                $city_end = $startcity['name'];
            }
            // 起点城市
            $startcity = $city_start.$startarea['name']; 
            // 终点城市  
            $endcity = $city_end.$endarea['name'];           
            // 短信内容
            $content = "尊敬的用户：从:".$startcity." 发往 ".$endcity ."的货物！提货地址为".$saddress."。用户已取消订单";
            // 发送短信
            send_sms_class($phone_all,$content);
            // 插入记录
            $content = "取消定制线路订单".$sorder_mess['ordernumber'];
            $this->hanldlog($this->uid,$content);
            // 返回订单取消状态
            return json(['code'=>true,'message'=>'订单已取消']);
        }else{
            // 返回订单取消状态
            return json(['code'=>true,'message'=>'取消失败']);
        }
    }
  
    /**
     * 订单反馈
     * 平台回复用户反馈
     * @Auther: 李渊
     * @Date: 2018.7.24
     * @param  [type] $otype    [请求类型] 1 客户价格 2 承运价格
     * @param  [type] $oid      [订单id]
     * @param  [type] $price    [修改价格]
     * @return [type] [description]
     */
    public function replay_contact(){
        $postdata = Request::instance()->post();
        // 获取操作人
        $data['userid'] = $this->uid;
        // 获取订单id
        $data['orderid'] = $postdata['oid'];
        // 获取反馈消息
        $data['message'] = $postdata['message'];
        // 设置反馈人类型 1 用户 2 平台
        $data['utype'] = '2';
        // 获取反馈的订单类型 1零担 2定制 3城配 4整车
        $data['otype'] = $postdata['otype'];
        // 设置反馈时间
        $data['addtime'] = time();
        // 添加反馈
        $insert = Db::table('ct_order_contact')->insert($data);
        // 判断是否添加成功
        if($insert){
            return json(['code'=>true,'message'=>'反馈成功']);
        }else{
            return json(['code'=>false,'message'=>'反馈失败']);;
        }
    }

}
