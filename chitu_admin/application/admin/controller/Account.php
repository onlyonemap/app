<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Account  extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//
//    }


	 //客户首次对账首页
    public function index(){
        $arr = array(); //零担
        $arr_shift= array();  //固定班次
        $arr_city = array();  //市内配送
        $arr_car = array();  //整车
        $counarr = array();
        $com_arr = array();
        $totalweight = 0; //总吨位
        $totalvolume= 0; //总立方
        $totalpick= 0;//提货总价
        $totalline= 0;//干线总价
        $totalpei= 0; //配送总价
        $totalprice = 0; //零担总价格
        $totalprice_shift = 0; //定制总价格
        $totalupprice=0;//修改后总价
        $totalprice_city = 0; //城配总价格
        $totalprice_car = 0; //整车总价格
        $search = Request::instance()->get();
        $pageParam    = ['query' =>[]];
        $where_search = '';
        $where_search2 = '';
        if (!empty($search['company'])) {
            //$where_data['realname|name'] = ['like','%'.$search['company'].'%'];
            $where_search['name|ordernumber'] = ['like','%'.$search['company'].'%'];
            $where_search2['name|orderid'] = ['like','%'.$search['company'].'%'];
        }
        if (!empty($search['starttime']) && !empty($search['endtime'])) {
            $endtime = strtotime(trim($search['endtime']).'23:59:59');
            $starttime = strtotime(trim($search['starttime']).'00:00:00');
            $where_data['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] = $search['starttime'];
            $pageParam['query']['endtime'] = $search['endtime'];
        }
        $where_data['pay_type'] = 1;
        $where_data['usercheck'] = 1;
        $where_data['userstate'] = 2;
        $where_shift['orderstate'] = 3;
        //定制订单
        $result_shift = Db::table('ct_shift_order')
                ->alias('a')
                ->join('ct_fixation_line f','f.id = a.shiftid')
                ->join('ct_user u','u.uid=a.userid')
                ->join('ct_company c','c.cid=u.lineclient')
                ->field('a.*,f.lienid,u.lineclient,c.name,c.cid,u.realname')
                ->where($where_search)
                ->where($where_data)
                ->where($where_shift)
                ->paginate(50,false,$pageParam);
        $count_shift_row = Db::table('ct_shift_order')
                    ->alias('a')
                    ->join('ct_fixation_line f','a.shiftid=f.id')
                    ->join('ct_user u','u.uid=a.userid')
                    ->join('ct_company c','c.cid=u.lineclient')
                    ->where($where_search)
                    ->where($where_data)
                    ->where($where_shift)
                    ->count('a.s_oid');
        $shift_data = $result_shift->toArray();
        foreach ($shift_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $lienid = Db::table('ct_already_city')->where('city_id',$value['lienid'])->find();
            //起点城市
            $scity = Db::table('ct_district')->where('id',$lienid['start_id'])->find();
            $city_str ='';
            if ($scity['level'] =='3') {
                $city_search = Db::table('ct_district')->where('id',$scity['parent_id'])->find();
                $city_str = $city_search['name'];
            }
            $startcity =  $city_str.$scity['name'];
            //终点城市
            $ecity = Db::table('ct_district')->where('id',$lienid['end_id'])->find();
            $city_str2 = '';
            if ($ecity['level'] =='3') {
                $endcity_search = Db::table('ct_district')->where('id',$ecity['parent_id'])->find();
                $city_str2 = $endcity_search['name'];
            }
            $endcity =  $city_str2.$ecity['name'];
       
           $arr_shift[$key]['oid'] = $value['s_oid']; //订单ID
           $arr_shift[$key]['line'] = $startcity .'--'.$endcity;  //线路
           $arr_shift[$key]['doornum'] = $value['doornum']; //门店数
           $arr_shift[$key]['addtime'] = $value['addtime']; //下单时间
           $arr_shift[$key]['lineclient'] = $value['lineclient']; //公司ID
           $arr_shift[$key]['name'] = $value['name']; //公司名称
           $arr_shift[$key]['ordernumber'] = $value['ordernumber']; //订单编号
           $arr_shift[$key]['totalweight'] = 0; //重量
           $arr_shift[$key]['totalvolume'] = 0; //体积
           $arr_shift[$key]['tprice'] = 0; //提货费
           $arr_shift[$key]['linepice'] = 0; //干线费
           $arr_shift[$key]['delivecost'] = 0; //配送费
           $countprice_shift = $value['upprice'] =='' ? $value['totalprice'] : $value['upprice'];
           $arr_shift[$key]['totalprice'] = $countprice_shift;
           $totalprice_shift +=$countprice_shift;//总价格
           $arr_shift[$key]['ostate'] = 2;
        }
        //零担
        $where_order['orderstate'] = 7;
        $where_order['s.shiftstate'] = 1;
        $result_order = DB::field('a.*,c.tprice,c.usepprice,b.realname,b.userstate,b.lineclient,com.name,com.cid,l.luseprice,d.puseprice,s.shiftstate')
                    ->table('ct_order')
                    ->alias('a')
                    ->join('ct_user b','b.uid = a.userid')
                    ->join('ct_company com','com.cid=b.lineclient')
                    ->join('ct_pickorder c','c.picid=a.oid')
                    ->join('ct_lineorder l','l.orderid=a.oid')
                    ->join('ct_delorder d','d.orderid=a.oid')
                    ->join('ct_shift s','s.sid=a.shiftid')
                    ->where($where_search)
                    ->where($where_data)
                    ->where($where_order)
                    ->paginate(50,false,$pageParam);
        $count_order_row = Db::table('ct_order')
                        ->alias('a')
                        ->join('ct_user b','b.uid = a.userid')
                        ->join('ct_company com','com.cid=b.lineclient')
                        ->join('ct_shift s','s.sid=a.shiftid')
                        ->where($where_search)
                        ->where($where_data)
                        ->where($where_order)
                        ->count('a.oid');
        $order_data = $result_order->toArray();
        foreach ($order_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $shift = Db::field('al.start_id,al.end_id')
                      ->table('ct_shift')
                      ->alias('b')
                      ->join('ct_already_city al','al.city_id = b.linecityid')
                      ->where('sid',$value['shiftid'])
                      ->find(); 
            $value['line'] = $this->start_end_city($shift['start_id'],$shift['end_id']);
            $lineprice = $value['puseprice']=='' ? $value['linepice'] : $value['puseprice']; //干线费用
            $tprice = $value['usepprice']=='' ? $value['tprice'] : $value['usepprice']; //提货费用
            $delivecost = $value['puseprice']=='' ? $value['delivecost'] : $value['puseprice']; //配送费用
            $countprice = $lineprice+$tprice+$delivecost;
            $value['delivecost'] = intval($delivecost);
            $value['tprice'] = intval($tprice);
            $value['linepice'] = intval($lineprice);
            $totalweight +=$value['totalweight']; //总重量
            $totalvolume +=$value['totalvolume']; //总体积
            $totalpick +=$tprice;//总提货费
            $totalline +=$lineprice;//总提货费
            $totalpei +=$delivecost;//总提货费
            $totalupprice += $value['totalcost'] =='' ? $countprice : $value['totalcost'];
           
            $value['doornum'] = 0;
            $value['totalprice'] = $value['totalcost'] =='' ? $countprice : $value['totalcost'];
            $value['ostate'] = 1;
            $arr[] = $value;
        } // end 零担
        //市配
        $where_city['state'] =3;
        $result_city = Db::table('ct_city_order')
                      ->alias('a')
                      ->join('ct_user u','u.uid=a.userid')
                      ->join('ct_company c','c.cid=u.lineclient')
                      ->field('a.*,u.lineclient,c.name,u.realname,c.cid')
                      ->where($where_search2)
                      ->where($where_data)
                      ->where($where_city)
                      ->paginate(50,false,$pageParam);
        $count_city_row = Db::table('ct_city_order')
                        ->alias('a')
                        ->join('ct_user u','u.uid=a.userid')
                        ->join('ct_company c','c.cid=u.lineclient')
                        ->where($where_search2)
                        ->where($where_data)
                        ->where($where_city)
                        ->count('a.id');
        $city_data = $result_city->toArray();
        foreach ($city_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $arr_city[$key]['oid'] = $value['id']; //订单ID
            $arr_city[$key]['line'] = '上海市';  //线路
            $arr_city[$key]['doornum'] = 0; //门店数
            $arr_city[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_city[$key]['lineclient'] = $value['lineclient']; //公司ID
            $arr_city[$key]['name'] = $value['name']; //公司名称
            $arr_city[$key]['ordernumber'] = $value['orderid']; //订单编号
            $arr_city[$key]['totalweight'] = 0; //重量
            $arr_city[$key]['totalvolume'] = 0; //体积
            $arr_city[$key]['tprice'] = 0; //提货费
            $arr_city[$key]['linepice'] = 0; //干线费
            $arr_city[$key]['delivecost'] = 0; //配送费
            $countprice_city = $value['upprice'] =='' ? $value['actualprice'] : $value['upprice'];
            $arr_city[$key]['totalprice'] = $countprice_city;
            $totalprice_city +=$countprice_city;//总价格
            $arr_city[$key]['ostate'] = 3;
        }//市配

        //整车
        $where_car['orderstate'] = 3;
        $result_car = Db::table('ct_userorder')
                        ->alias('a')
                        ->join('ct_user u','u.uid=a.userid')
                        ->join('ct_company c','c.cid=u.lineclient')
                        ->field('a.*,u.realname,u.lineclient,c.name,c.cid')
                        ->where($where_search)
                        ->where($where_data)
                        ->where($where_car)
                        ->paginate(50,false,$pageParam);
        $count_car_row = Db::table('ct_userorder')
                        ->alias('a')
                        ->join('ct_user u','u.uid=a.userid')
                        ->join('ct_company c','c.cid=u.lineclient')
                        ->where($where_search)
                        ->where($where_data)
                        ->where($where_car)
                        ->count('a.uoid');
        $car_data = $result_car->toArray();
        foreach ($car_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $arr_car[$key]['oid'] = $value['uoid']; //订单ID
            $arr_car[$key]['line'] = $this->start_end_city($value['startcity'],$value['endcity']);  //线路
            $arr_car[$key]['doornum'] = 0; //门店数
            $arr_car[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_car[$key]['lineclient'] = $value['lineclient']; //公司ID
            $arr_car[$key]['name'] = $value['name']; //公司名称
            $arr_car[$key]['ordernumber'] = $value['ordernumber']; //订单编号
            $arr_car[$key]['totalweight'] = 0; //重量
            $arr_car[$key]['totalvolume'] = 0; //体积
            $arr_car[$key]['tprice'] = 0; //提货费
            $arr_car[$key]['linepice'] = 0; //干线费
            $arr_car[$key]['delivecost'] = 0; //配送费
            $countprice_car = $value['upprice'] =='' ? $value['referprice'] : $value['upprice'];
            $arr_car[$key]['totalprice'] = $countprice_car;
            $totalprice_car +=$countprice_car;//总价格
            $arr_car[$key]['ostate'] = 4;
        }//整车
        //订单个数
        $count_row = array('order'=>intval($count_order_row),'shift'=>intval($count_shift_row),'city'=>intval($count_city_row),'car'=>intval($count_car_row));
        $pos = array_search(max($count_row), $count_row);
        switch ($pos) {
            case 'order':
                $page = $result_order->render();
                break;
            case 'city':
                $page = $result_city->render();
                break;
            case 'car':
                $page = $result_car->render();
                break;
            case 'shift':
                $page = $result_shift->render();
                break;
        }
        $list = array_merge($arr_shift,$arr,$arr_city,$arr_car);
        if (!empty($list)) {
            $list = $this->my_sort($list,'addtime',SORT_DESC);
        }
    
        $comnum = array_unique($com_arr);
        $ordercount = count($list);
        $this->assign('comnum',count($comnum));
        $this->assign('list',$list);
        $this->assign('ordercount',$ordercount);
        $this->assign('page',$page);
      	return view('account/index'); 
    }
    /*
    *添加对账信息
    */
    public function addcheck(){
      //1零担，2定制 ，3城配 4整车
      $post=Request::instance()->post();
      $batch ='';
      $price_shift = 0;  //定制
      $price_order = 0;  //零担
      $price_city = 0;  //城配
      $price_car = 0;  //整车
      $order_arr = $post['order'];
      $ostate = $post['ostate'];
      $shift_str = ''; //定制
      $order_str = ''; //零担
      $city_str = ''; //市配
      $car_str = ''; //整车
      $arr2 = array('shift'=>'','order'=>'','city'=>'','car'=>'');
      $i=0;
      foreach ($order_arr as $key => $value) {
            $arr[$i]['b'] = $order_arr[$key];
            $arr[$i]['a'] = $ostate[$key];
            $i++;
      }
      foreach ($arr as $key => $info) {
        if ($info['a'] == '2') {  //定制
          $shift_str .= $info['b'].',';
        }
        if ($info['a'] == '1') {  //零担
          $order_str .= $info['b'].',';
        }
        if ($info['a'] == '3') {  //市配
          $city_str .= $info['b'].',';
        }
        if ($info['a'] == '4') {  //整车
          $car_str .= $info['b'].',';
        }

      }
      if ($order_str !='') { //零担
        $order_sel = DB::field('a.linepice,a.delivecost,a.totalcost,b.tprice,b.usepprice,l.luseprice,d.puseprice')
                    ->table('ct_order')
                    ->alias('a')
                    ->join('ct_pickorder b','b.orderid = a.oid')
                    ->join('ct_lineorder l','l.orderid=a.oid')
                    ->join('ct_delorder d','d.orderid=a.oid')
                    ->where('oid','IN',rtrim($order_str,','))->select();
        foreach ($order_sel as $key => $value) {
            $lineprice = $value['puseprice']=='' ? $value['linepice'] : $value['puseprice']; //干线费用
            $tprice = $value['usepprice']=='' ? $value['tprice'] : $value['usepprice']; //提货费用
            $delivecost = $value['puseprice']=='' ? $value['delivecost'] : $value['puseprice']; //配送费用
            $total = $lineprice+$delivecost+$tprice;
            $price_order += $value['totalcost']==''?$total:$value['totalcost'];
        }
      }
      if ($shift_str !='') { //定制
        $fix_arr = Db::table('ct_shift_order')
                  ->field('totalprice,upprice')
                  ->where('s_oid','IN',rtrim($shift_str,','))
                  ->select();
        foreach ($fix_arr as $key => $value) {
          $price_shift += $value['upprice']==''? $value['totalprice'] : $value['upprice'];
        } 
      }
     
      if ($city_str !='') { //城配
        $city_arr = Db::table('ct_city_order')
                  ->field('actualprice,upprice')
                  ->where('id','IN',rtrim($city_str,','))
                  ->select();
        foreach ($city_arr as $key => $value) {
          $price_city += $value['upprice']==''? $value['actualprice'] : $value['upprice'];
        } 
      }
      if ($car_str !='') { //整车
        $car_arr = Db::table('ct_userorder')
                  ->field('referprice,upprice')
                  ->where('uoid','IN',rtrim($car_str,','))
                  ->select();
        foreach ($car_arr as $key => $value) {
          $price_car += $value['upprice']==''? $value['referprice'] : $value['upprice'];
        } 
      }
    
      $price = $price_order+$price_shift+$price_city+$price_car;
      $sermonth = strtotime($post['moth']);
      $invo_data['sermonth'] = $sermonth;
      $invo_data['companyid'] = $post['companyid'][0];
      $invo_data['usertype'] = 3;
      $invo_data['totalprice'] = $price;
      $invo_data['firtime'] = time();
      $invo= DB::table('ct_invoice')
              ->where(array('companyid'=>$post['companyid'][0],'sermonth'=>$sermonth))
              ->count();
       if ($invo ==0) {
          $batch =1;
        }else{
          $batch = $invo +1;
        }
       $invo_data['batch'] = $batch;
      $insert_data = DB::table('ct_invoice')->insertGetId($invo_data);
        //插入对账信息
        $order_data['user_checkid'] = $insert_data;
        $order_data['usercheck'] = 2;
        if ($order_str!='') { //零担
              DB::table('ct_order')->where('oid','IN',$order_str)->update($order_data);
        }
        if($shift_str!=''){ //定制
              DB::table('ct_shift_order')->where('s_oid','IN',$shift_str)->update($order_data);
        }

        if($city_str!=''){ //市配
              DB::table('ct_city_order')->where('id','IN',$city_str)->update($order_data);
        }
        if($car_str!=''){ //整车
              DB::table('ct_userorder')->where('uoid','IN',$car_str)->update($order_data);
        }
        if($insert_data) {
            print_r('ok');
        }else{
            print_r('fail');
        }
    }
    /*
    *
    *账单确认页面
    */

    public function affirm(){
        $arr = array(); //零担
        $arr_shift = array(); //定制
        $arr_city = array(); //城配
        $arr_car = array(); //城配
        $counarr = array();  //合计对账总价
        $ll=array(); //获取订单对账ID
        $ll2=array();  //获取订单对账信息
        $com_arr = array();  //获取订单对账公司ID
        $totalweight = 0; //总吨位
        $totalvolume=0; //总立方
        $totalpick=0;//提货总价
        $totalline=0;//干线总价
        $totalpei=0; //配送总价
        $totalprice =0; //总价格
        $totalupprice=0;//零担后总价
        $totalupprice_shift = 0;//定制后总价
        $totalprice_city = 0;//市配总价
        $totalprice_car = 0;//市配总价
        $search = Request::instance()->get();
        $pageParam    = ['query' =>[]];
        $where_search ='';
        $where_search2 ='';
        if (isset($search['action']) == 'search') {
            $pageParam['query']['action'] = 'search';
            if (!empty($search['company'])) {
              $where_search['name|ordernumber'] = ['like','%'.$search['company'].'%'];
              $where_search2['name|orderid'] = ['like','%'.$search['company'].'%'];
              $pageParam['query']['company'] = $search['company'];
            }
            if (!empty($search['moth'])) {
                $sermoth = strtotime($search['moth']);
                $where_data['sermonth'] = ['EQ',$sermoth];
                $pageParam['query']['moth'] = $search['moth'];
            }
            $this->assign('search',$search['action']);
        }
        $where_data['pay_type'] = '1';
        $where_data['Invoiceno'] = '';
        $where_data['usercheck'] = 2;
        $where_data['inv.usertype'] = 3; //用户类型判断条件
        $where_data2['orderstate'] = 3; //定制订单类型
        $result_shift = Db::table('ct_shift_order')
                ->alias('s')
                ->join('ct_fixation_line f','f.id = s.shiftid')
                ->join('ct_user u','u.uid=s.userid')
                ->join('ct_company c','c.cid=u.lineclient')
                ->field('s.*,f.lienid,u.lineclient,c.name,c.cid,u.realname,inv.usertype,inv.confirm,inv.self_total,inv.self_remark,inv.sermonth')
                ->join('ct_invoice inv','inv.iid = s.user_checkid')
                ->where($where_search)
                ->where($where_data)
                ->where($where_data2)
                ->paginate(50,false,$pageParam);
        $count_shift_row = Db::table('ct_shift_order')
                    ->alias('s')
                    ->join('ct_fixation_line f','f.id = s.shiftid')
                    ->join('ct_user u','u.uid=s.userid')
                    ->join('ct_company c','c.cid=u.lineclient')
                    ->join('ct_invoice inv','inv.iid = s.user_checkid')
                    ->where($where_search)
                    ->where($where_data)
                    ->where($where_data2)
                    ->count('s.s_oid');
        $shift_data = $result_shift->toArray();
        foreach ($shift_data['data'] as $key => $value) {
          $com_arr[] = $value['cid'];
          $ll[] = $value['user_checkid'];
          $ll2[$value['user_checkid']]['confirm'] = $value['confirm'];
          $ll2[$value['user_checkid']]['self_total'] = $value['self_total'];
          $ll2[$value['user_checkid']]['self_remark'] = $value['self_remark'];
          $ll2[$value['user_checkid']]['sermonth'] = $value['sermonth'];
          $lienid = Db::table('ct_already_city')->where('city_id',$value['lienid'])->find();
              //起点城市
              $scity = Db::table('ct_district')->where('id',$lienid['start_id'])->find();
              $city_str ='';
              if ($scity['level'] =='3') {
                $city_search = Db::table('ct_district')->where('id',$scity['parent_id'])->find();
                $city_str = $city_search['name'];
              }
              $startcity =  $city_str.$scity['name'];
              //终点城市
              $ecity = Db::table('ct_district')->where('id',$lienid['end_id'])->find();
              $city_str2 = '';
              if ($ecity['level'] =='3') {
                $endcity_search = Db::table('ct_district')->where('id',$ecity['parent_id'])->find();
                $city_str2 = $endcity_search['name'];
              }
              $endcity =  $city_str2.$ecity['name'];
           $arr_shift[$key]['oid'] = $value['s_oid']; //订单ID
           $arr_shift[$key]['user_checkid'] = $value['user_checkid']; //对账单ID
           $arr_shift[$key]['line'] = $startcity .'--'.$endcity;  //线路
           $arr_shift[$key]['doornum'] = $value['doornum']; //门店数
           $arr_shift[$key]['addtime'] = $value['addtime']; //下单时间
           $arr_shift[$key]['lineclient'] = $value['lineclient']; //公司ID
           $arr_shift[$key]['name'] = $value['name']; //公司名称
           $arr_shift[$key]['ordernumber'] = $value['ordernumber']; //订单编号
           $arr_shift[$key]['totalweight'] = 0; //重量
           $arr_shift[$key]['totalvolume'] = 0; //体积
           $arr_shift[$key]['tprice'] = 0; //提货费
           $arr_shift[$key]['linepice'] = 0; //干线费
           $arr_shift[$key]['delivecost'] = 0; //配送费
           $countprice = $value['upprice'] =='' ? $value['totalprice'] : $value['upprice'];
           $arr_shift[$key]['totalprice'] = $countprice;
           $totalupprice_shift +=$countprice;//总价格
           $arr_shift[$key]['ostate'] = 2;          
        }
        //零担

        $result_order = DB::field('a.*,c.tprice,c.usepprice,b.userstate,b.lineclient,com.name,com.cid,inv.usertype,
                                  inv.confirm,inv.self_total,inv.self_remark,inv.sermonth,l.luseprice,d.puseprice')
                    ->table('ct_order')
                    ->alias('a')
                    ->join('ct_user b','b.uid = a.userid')
                    ->join('ct_company com','com.cid=b.lineclient',"LEFT")
                    ->join('ct_pickorder c','c.picid=a.oid')
                    ->join('ct_lineorder l','l.orderid=a.oid')
                    ->join('ct_delorder d','d.orderid=a.oid')
                    ->join('ct_invoice inv','inv.iid = a.user_checkid')
                    ->where($where_search)
                    ->where($where_data)
                    ->paginate(50,false,$pageParam);
        //统计订单个数
        $count_order_row = Db::table('ct_order')
                    ->alias('a')
                    ->join('ct_invoice inv','inv.iid = a.user_checkid')
                    ->join('ct_user b','b.uid = a.userid')
                    ->join('ct_company com','com.cid=b.lineclient',"LEFT")
                    ->where($where_search)
                    ->where($where_data)
                    ->count('a.oid');
        $order_data = $result_order->toArray();
        foreach ($order_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];  //获取公司ID
            $ll[] = $value['user_checkid'];  //对账ID
            $ll2[$value['user_checkid']]['confirm'] = $value['confirm'];
            $ll2[$value['user_checkid']]['self_total'] = $value['self_total'];
            $ll2[$value['user_checkid']]['self_remark'] = $value['self_remark'];
            $ll2[$value['user_checkid']]['sermonth'] = $value['sermonth'];
            $shift = Db::field('al.start_id,al.end_id')
                      ->table('ct_shift')
                      ->alias('b')
                      ->join('ct_already_city al','al.city_id = b.linecityid')
                      ->where('sid',$value['shiftid'])
                      ->find();
            $value['line'] = $this->start_end_city($shift['start_id'],$shift['end_id']);
            $lineprice = $value['puseprice']=='' ? $value['linepice'] : $value['puseprice']; //干线费用
            $tprice = $value['usepprice']=='' ? $value['tprice'] : $value['usepprice']; //提货费用
            $delivecost = $value['puseprice']=='' ? $value['delivecost'] : $value['puseprice']; //配送费用

            $countprice = $lineprice+$tprice+$delivecost;
            $value['delivecost'] = intval($delivecost);
            $value['tprice'] = intval($tprice);
            $value['linepice'] = intval($lineprice);
            $totalweight +=$value['totalweight']; //总重量
            $totalvolume +=$value['totalvolume']; //总体积
            $totalpick +=$tprice;//总提货费
            $totalline +=$lineprice;//总提货费
            $totalpei +=$delivecost;//总提货费
            $totalupprice += $value['totalcost'] =='' ? $countprice : $value['totalcost'];
            $value['totalprice'] = $value['totalcost'] =='' ? $countprice : $value['totalcost'];
            $value['doornum'] = 0;
            $value['ostate'] = 1;
            $arr[] = $value;
        } 

        //市配
        $result_city = Db::table('ct_city_order')
                        ->alias('a')
                        ->join('ct_user u','u.uid=a.userid')
                        ->join('ct_company c','c.cid=u.lineclient')
                        ->join('ct_invoice inv','inv.iid=a.user_checkid')
                        ->field('a.*,c.name,c.cid,u.lineclient,inv.usertype,inv.confirm,inv.self_total,inv.self_remark,inv.sermonth')
                        ->where($where_search2)
                        ->where($where_data)
                        ->paginate(50,false,$pageParam);
         $count_city_row = Db::table('ct_city_order')
                    ->alias('a')
                    ->join('ct_user u','u.uid=a.userid')
                    ->join('ct_company c','c.cid=u.lineclient')
                    ->join('ct_invoice inv','inv.iid=a.user_checkid')
                    ->where($where_search2)
                    ->where($where_data)
                    ->count('a.id');
        $city_data = $result_city->toArray();
        foreach ($city_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $ll[] = $value['user_checkid'];
            $ll2[$value['user_checkid']]['confirm'] = $value['confirm'];
            $ll2[$value['user_checkid']]['self_total'] = $value['self_total'];
            $ll2[$value['user_checkid']]['self_remark'] = $value['self_remark'];
            $ll2[$value['user_checkid']]['sermonth'] = $value['sermonth'];
            $arr_city[$key]['oid'] = $value['id']; //订单ID
            $arr_city[$key]['user_checkid'] = $value['user_checkid']; //对账ID
            $arr_city[$key]['line'] = '上海市';  //线路
            $arr_city[$key]['doornum'] = 0; //门店数
            $arr_city[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_city[$key]['lineclient'] = $value['lineclient']; //公司ID
            $arr_city[$key]['name'] = $value['name']; //公司名称
            $arr_city[$key]['ordernumber'] = $value['orderid']; //订单编号
            $arr_city[$key]['totalweight'] = 0; //重量
            $arr_city[$key]['totalvolume'] = 0; //体积
            $arr_city[$key]['tprice'] = 0; //提货费
            $arr_city[$key]['linepice'] = 0; //干线费
            $arr_city[$key]['delivecost'] = 0; //配送费
            $countprice_city = $value['upprice'] =='' ? $value['actualprice'] : $value['upprice'];
            $arr_city[$key]['totalprice'] = $countprice_city;
            $totalprice_city +=$countprice_city;//总价格
            $arr_city[$key]['ostate'] = 3;

        }
        //整车
        $result_car = Db::table('ct_userorder')
                        ->alias('a')
                        ->join('ct_user u','u.uid=a.userid')
                        ->join('ct_company c','c.cid=u.lineclient')
                        ->join('ct_invoice inv','inv.iid=a.user_checkid')
                        ->field('a.*,u.realname,u.lineclient,c.name,c.cid,inv.usertype,inv.confirm,inv.self_total,inv.self_remark,inv.sermonth')
                        ->where($where_search)
                        ->where($where_data)
                        ->paginate(50,false,$pageParam);
        $count_car_row = Db::table('ct_userorder')
                    ->alias('a')
                    ->join('ct_user u','u.uid=a.userid')
                    ->join('ct_company c','c.cid=u.lineclient')
                    ->join('ct_invoice inv','inv.iid=a.user_checkid')
                    ->where($where_search)
                    ->where($where_data)
                    ->count('uoid');
        $car_data = $result_car->toArray();
        foreach ($car_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $ll[] = $value['user_checkid'];
            $ll2[$value['user_checkid']]['confirm'] = $value['confirm'];
            $ll2[$value['user_checkid']]['self_total'] = $value['self_total'];
            $ll2[$value['user_checkid']]['self_remark'] = $value['self_remark'];
            $ll2[$value['user_checkid']]['sermonth'] = $value['sermonth'];
            $arr_car[$key]['oid'] = $value['uoid']; //订单ID
            $arr_car[$key]['user_checkid'] = $value['user_checkid']; //订单ID
            $arr_car[$key]['line'] = $this->start_end_city($value['startcity'],$value['endcity']);  //线路
            $arr_car[$key]['doornum'] = 0; //门店数
            $arr_car[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_car[$key]['lineclient'] = $value['lineclient']; //公司ID
            $arr_car[$key]['name'] = $value['name']; //公司名称
            $arr_car[$key]['ordernumber'] = $value['ordernumber']; //订单编号
            $arr_car[$key]['totalweight'] = 0; //重量
            $arr_car[$key]['totalvolume'] = 0; //体积
            $arr_car[$key]['tprice'] = 0; //提货费
            $arr_car[$key]['linepice'] = 0; //干线费
            $arr_car[$key]['delivecost'] = 0; //配送费
            $countprice_car = $value['upprice'] =='' ? $value['referprice'] : $value['upprice'];
            $arr_car[$key]['totalprice'] = $countprice_car;
            $totalprice_car +=$countprice_car;//总价格
            $arr_car[$key]['ostate'] = 4;
        }//整车

         //订单个数
        $count_row = array('order'=>intval($count_order_row),'shift'=>intval($count_shift_row),'city'=>intval($count_city_row),'car'=>intval($count_car_row));
        $pos = array_search(max($count_row), $count_row);
        switch ($pos) {
            case 'order':
                $page = $result_order->render();
                break;
            case 'city':
                $page = $result_city->render();
                break;
            case 'car':
                $page = $result_car->render();
                break;
            case 'shift':
                $page = $result_shift->render();
                break;
        }
        $counarr['totalupprice'] =  number_format($totalupprice+$totalupprice_shift+$totalprice_city,2); 
        $list = array_merge($arr_shift,$arr,$arr_city,$arr_car);//合并数组
        if (!empty($list)) {
            $list = $this->my_sort($list,'addtime',SORT_DESC);
        }
        $ordercount = count($list); //统计订单个数
        $count = array_unique($ll);
        $comnum = array_unique($com_arr);
        $dat = array();
        if (count($ll2) =='1') {
            foreach ($ll2 as $b) {
                $dat = $b;
            }
        }
        if (count($count) > 1) {
            $this->assign('countarr','listtwo');
        }else{
            $this->assign('countarr','listone');
            $this->assign('listcount',$dat);
        }
        $this->assign('ordercount',$ordercount);
        $this->assign('list',$list);
        $this->assign('count',$counarr);
        $this->assign('comnum',count($comnum));
        $this->assign('page',$page);
        return view('account/affirm');
    }
    /*
    *开票提交动作
    */
    public function Confirmcheck(){
      $post_data = Request::instance()->post();
      $data_arr['Invoiceno'] = $post_data['number'];
      $data_arr['instate'] = 2;
      $data_arr['paytime'] = strtotime($post_data['payTime']);
      $data_arr['Invoiceamount'] = $post_data['money'];
      $invo_id = $post_data['invoID'];
      $update_data = Db::table('ct_invoice')->where('iid',$invo_id)->update($data_arr);
      
      if ($update_data) {
        print_r("ok");
      }else{
        print_r("fail");
      }
    }
    /*
    *删除对账订单
    */
    public function delcheck(){
       $post_data = Request::instance()->post();
       $orderid = $post_data['orderID'];
       $otype = $post_data['ostate'];
       $invo_id = $post_data['invoID'][0];
       $get_price = 0; //获取价格
       $get_price_order = 0;//零担价格
       $get_price_shift = 0;//定制价格
       $get_price_city = 0;//城配价格
       $get_price_car = 0;//整车价格
       $shift_str = ''; //定制
       $order_str = '';  // 零担
       $city_str = '';  // 城配
       $car_str = '';  // 整车
       $i=0;
       $arr2 = array('shift'=>'','order'=>'','city'=>'','car'=>'');
       foreach ($orderid as $key => $value) {
            $array[$i]['b']= $orderid[$key];
            $array[$i]['a']= $otype[$key];
            $i++;
       }
        foreach ($array as $key => $info) {
          if ($info['a'] == '2') {
            $shift_str .= $info['b'].',';
            $arr2['shift'][] = $info['b'];
          }
          if ($info['a'] == '1') {
            $order_str .= $info['b'].',';
            $arr2['order'][] = $info['b'];
          }
          if ($info['a'] == '3') {
            $city_str .= $info['b'].',';
            $arr2['city'][] = $info['b'];
          }
          if ($info['a'] == '4') {
            $car_str .= $info['b'].',';
            $arr2['car'][] = $info['b'];
          }
        }
        if ($order_str !='') { //零担
            $price_arr = DB::field('a.linepice,a.delivecost,a.totalcost,b.tprice,b.usepprice,l.luseprice,d.puseprice')
                    ->table('ct_order')
                    ->alias('a')
                    ->join('ct_pickorder b','b.orderid = a.oid')
                    ->join('ct_lineorder l','l.orderid=a.oid')
                    ->join('ct_delorder d','d.orderid=a.oid')
                    ->where('oid','IN',rtrim($order_str,','))->select();
            foreach ($price_arr as $key => $value) {
                     $lineprice = $value['puseprice']=='' ? $value['linepice'] : $value['puseprice']; //干线费用
                    $tprice = $value['usepprice']=='' ? $value['tprice'] : $value['usepprice']; //提货费用
                    $delivecost = $value['puseprice']=='' ? $value['delivecost'] : $value['puseprice']; //配送费用
                    $total = $lineprice+$delivecost+$tprice;
                    //$cost = $value['linepice']+$value['tprice']+$value['delivecost'];
                    $get_price_order += $value['totalcost'] =='' ? $total : $value['totalcost'];
            }
        }
        if($shift_str!=''){  //定制
            $shift_arr = Db::table('ct_shift_order')
                    ->field('totalprice,upprice')
                    ->where('s_oid','IN',rtrim($shift_str,','))
                    ->select();
            foreach ($shift_arr as $key => $value) {
                    $get_price_shift += $value['upprice'] =='' ? $value['totalprice'] : $value['upprice'];
            }          
        }
        if($city_str!=''){ //城配
            $city_arr = Db::table('ct_city_order')
                    ->field('actualprice,upprice')
                    ->where('id','IN',rtrim($city_str,','))
                    ->select();
            foreach ($city_arr as $key => $value) {
                    $get_price_city += $value['upprice'] =='' ? $value['actualprice'] : $value['upprice'];
            }          
        }
        if($car_str!=''){ //整车
            $car_arr = Db::table('ct_userorder')
                    ->field('referprice,upprice')
                    ->where('uoid','IN',rtrim($car_str,','))
                    ->select();
            foreach ($car_arr as $key => $value) {
                    $get_price_car += $value['upprice'] =='' ? $value['referprice'] : $value['upprice'];
            }          
        }
       $get_price = $get_price_order+$get_price_shift+$get_price_city+$get_price_car;
        $invo_arr = DB::table('ct_invoice')->where('iid',$invo_id)->find(); 
        $subcost = (int)$invo_arr['totalprice'] - (int)$get_price;
        $invo_data['totalprice'] = $subcost;
        if ($invo_arr['self_total'] !='') {
            $invo_data['self_total'] = (int)$invo_arr['self_total'] - (int)$get_price;
        }
        if($subcost ==0) {
            $del_data = Db::table('ct_invoice')->delete($invo_id);
        }else{
            $del_data = Db::table('ct_invoice')->where('iid',$invo_id)->update($invo_data);
        }
        $order_data['usercheck'] = 1;
        $order_data['user_checkid'] = ' ';
        if ($order_str !='') { //零担
            $data = DB::table('ct_order')->where('oid','IN',$order_str)->update($order_data);
        }
        if($shift_str!=''){ //定制
            $data = DB::table('ct_shift_order')->where('s_oid','IN',$shift_str)->update($order_data);
        }
        if($city_str!=''){ //城配
            $data = DB::table('ct_city_order')->where('id','IN',$city_str)->update($order_data);
        }
        if($car_str !=''){ //整车
            $data = DB::table('ct_userorder')->where('uoid','IN',$car_str)->update($order_data);
        }
        if ($del_data && $data) {
          print_r('ok');
        }else{
          print_r('fail');
        }
    }
    //修改价格
    public function update(){
      $getid = Request::instance()->get();
      $invo = Db::table('ct_invoice')->where('iid',$getid['id'])->find();
      $this->assign('list',$invo);
      return view('account/update');
    }
    //修改价格提交动作
    public function updateinvo(){
      $post_data = Request::instance()->post();
      if ($post_data['ajax'] == 1) {
        $data['self_total'] = $post_data['price'];
        $data['self_remark'] = $post_data['remark'];
        $id = $post_data['invoID'];
        $up = DB::table('ct_invoice')->where('iid',$id)->update($data);
        if ($up) {
          print_r('ok');
        }else{
          print_r('fail');
        }
      }
    }
    //开票页面
    public function upcheck(){
      $getid = Request::instance()->get();
      $invo = Db::table('ct_invoice')->where('iid',$getid['id'])->find();
      if ($invo['self_total'] =='') {
        $invo['checktic'] = $invo['totalprice'];
      }else{
        $invo['checktic'] = $invo['self_total'];
      }

      $this->assign('list',$invo);
      return view('account/upcheck');
    }
    /*
    *财务销账页面
    */
    public function cancel(){
        $arr = array(); //零担
        $arr_city = array(); //城配
        $arr_shift = array(); //定制
        $arr_car = array(); //定制
        $ll=array(); //判断发票号个数
        $ll2=array(); //存储发票号信息
        $com_arr = array(); //公司个数
        $counarr = array();
        $totalweight = 0; //总吨位
        $totalvolume=0;  //总立方
        $totalpick=0; //提货总价
        $totalline=0;  //干线总价
        $totalpei=0;  //配送总价
        $totalprice =0; //总价格
        $totalupprice=0;//零担修改后总价
        $totalupprice_shift=0;//定制修改后总价
        $totalprice_city = 0; //城配修改后总价
        $totalprice_car = 0; //城配修改后总价
        $search = Request::instance()->get();
        $pageParam    = ['query' =>[]];
        $where_search='';
        $where_search2='';
        if (isset($search['action']) == 'search') {
            if (!empty($search['company'])) {
              //$where_data['realname|name'] = ['like','%'.$search['company'].'%'];
                $where_search['ordernumber|name'] = ['like','%'.$search['company'].'%'];
                $where_search2['orderid|name'] = ['like','%'.$search['company'].'%'];
                $pageParam['query']['company'] = $search['company'];
            }
            if (!empty($search['moth'])) {
                $sermoth = strtotime($search['moth']);
                $where_data['inv.sermonth'] = ['EQ',$sermoth];
                $pageParam['query']['moth'] = $search['moth'];
            }
            if (!empty($search['invonumber'])){
                $where_data['inv.Invoiceno'] = ['like',$search['invonumber'].'%'];
                $pageParam['query']['invonumber'] = $search['invonumber'];
            }
            $this->assign('search',$search['action']);
        }
        //$where_data['usercheck'] = 2;
        $where_data['inv.usertype'] = 3; //用户类型判断条件
        $where_data['inv.instate'] = 2;
        $where_data2['orderstate'] = 3;
        $result_shift = Db::table('ct_shift_order')
                  ->alias('s')
                  ->join('ct_fixation_line f','f.id = s.shiftid')
                  ->join('ct_user u','u.uid=s.userid')
                  ->join('ct_company c','c.cid=u.lineclient')
                  ->join('ct_invoice inv','inv.iid = s.user_checkid')
                  ->field('s.*,f.lienid,u.lineclient,c.name,c.cid,u.realname,inv.usertype,
                    inv.confirm,inv.self_total,inv.self_remark,inv.carr_total,inv.carr_remark,
                    inv.sermonth,inv.Invoiceno,inv.Invoiceamount,inv.paytime,inv.unpass')
                  ->where($where_search)
                  ->where($where_data)
                  ->where($where_data2)
                  ->paginate(50,false,$pageParam);
        $count_shift_row = Db::table('ct_shift_order')
                    ->alias('s')
                    ->join('ct_fixation_line f','f.id = s.shiftid')
                    ->join('ct_user u','u.uid=s.userid')
                    ->join('ct_company c','c.cid=u.lineclient')
                    ->join('ct_invoice inv','inv.iid = s.user_checkid')
                    ->where($where_search)
                    ->where($where_data)
                    ->where($where_data2)
                    ->count('s.s_oid');
        $shift_data = $result_shift->toArray();
        foreach ($shift_data['data'] as $key => $value) {
          $com_arr[] = $value['cid'];
          $ll[] = $value['user_checkid'];
          $ll2[$value['user_checkid']]['Invoiceno'] = $value['Invoiceno'];
          $ll2[$value['user_checkid']]['Invoiceamount'] = $value['Invoiceamount'];
          $ll2[$value['user_checkid']]['paytime'] = $value['paytime'];
          $ll2[$value['user_checkid']]['unpass'] = $value['unpass'];
          $lienid = Db::table('ct_already_city')->where('city_id',$value['lienid'])->find();
              //起点城市
              $scity = Db::table('ct_district')->where('id',$lienid['start_id'])->find();
              $city_str ='';
              if ($scity['level'] =='3') {
                $city_search = Db::table('ct_district')->where('id',$scity['parent_id'])->find();
                $city_str = $city_search['name'];
              }
              $startcity =  $city_str.$scity['name'];
              //终点城市
              $ecity = Db::table('ct_district')->where('id',$lienid['end_id'])->find();
              $city_str2 = '';
              if ($ecity['level'] =='3') {
                $endcity_search = Db::table('ct_district')->where('id',$ecity['parent_id'])->find();
                $city_str2 = $endcity_search['name'];
              }
              $endcity =  $city_str2.$ecity['name'];
           
           $arr_shift[$key]['oid'] = $value['s_oid']; //订单ID
           $arr_shift[$key]['user_checkid'] = $value['user_checkid']; //对账单ID
           $arr_shift[$key]['line'] = $startcity .'--'.$endcity;  //线路
           $arr_shift[$key]['doornum'] = $value['doornum']; //门店数
           $arr_shift[$key]['addtime'] = $value['addtime']; //下单时间
           $arr_shift[$key]['lineclient'] = $value['lineclient']; //公司ID
           $arr_shift[$key]['name'] = $value['name']; //公司名称
           $arr_shift[$key]['ordernumber'] = $value['ordernumber']; //订单编号
            $arr_shift[$key]['unpass'] = $value['unpass']; //是否销账
           $arr_shift[$key]['totalweight'] = 0; //重量
           $arr_shift[$key]['totalvolume'] = 0; //体积
           $arr_shift[$key]['tprice'] = 0; //提货费
           $arr_shift[$key]['linepice'] = 0; //干线费
           $arr_shift[$key]['delivecost'] = 0; //配送费
           $countprice = $value['upprice'] =='' ? $value['totalprice'] : $value['upprice'];
           $arr_shift[$key]['totalprice'] = $countprice;
           $totalupprice_shift +=$countprice;//总价格
           $arr_shift[$key]['ostate'] = 2;          
        }
        $where_order['a.orderstate'] = 7;
        $result = DB::field('a.*,c.tprice,c.usepprice,b.realname,b.userstate,b.lineclient,com.name,com.cid,
                        inv.usertype,inv.Invoiceno,inv.Invoiceamount,inv.paytime,inv.sermonth,inv.unpass,l.luseprice,d.puseprice')
                    ->table('ct_order')
                    ->alias('a')
                    ->join('ct_user b','b.uid = a.userid')
                    ->join('ct_company com','com.cid=b.lineclient',"LEFT")
                    ->join('ct_pickorder c','c.picid=a.oid')
                    ->join('ct_lineorder l','l.orderid=a.oid')
                    ->join('ct_delorder d','d.orderid=a.oid')
                    ->join('ct_invoice inv','inv.iid = a.user_checkid')
                    ->where($where_search)
                    ->where($where_data)
                    ->where($where_order)
                    ->paginate(50,false,$pageParam);
        //统计订单个数
        $count_order_row = Db::table('ct_order')
                    ->alias('a')
                    ->join('ct_user b','b.uid = a.userid')
                    ->join('ct_company com','com.cid=b.lineclient',"LEFT")
                    ->join('ct_invoice inv','inv.iid = a.user_checkid')
                    ->where($where_search)
                    ->where($where_data)
                    ->where($where_order)
                    ->count('a.oid');
        $order_data = $result->toArray();
        foreach ($order_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $ll[] = $value['user_checkid'];
            $ll2[$value['user_checkid']]['Invoiceno'] = $value['Invoiceno'];
            $ll2[$value['user_checkid']]['Invoiceamount'] = $value['Invoiceamount'];
            $ll2[$value['user_checkid']]['paytime'] = $value['paytime'];
            $ll2[$value['user_checkid']]['unpass'] = $value['unpass'];
            $shift = Db::field('al.start_id,al.end_id')
                      ->table('ct_shift')
                      ->alias('b')
                      ->join('ct_already_city al','al.city_id = b.linecityid')
                      ->where('sid',$value['shiftid'])
                      ->find();
            $value['line'] = $this->start_end_city($shift['start_id'],$shift['end_id']);
            $value['unpass'] = $value['unpass']; //是否销账
            $lineprice = $value['puseprice']=='' ? $value['linepice'] : $value['puseprice']; //干线费用
            $tprice = $value['usepprice']=='' ? $value['tprice'] : $value['usepprice']; //提货费用
            $delivecost = $value['puseprice']=='' ? $value['delivecost'] : $value['puseprice']; //配送费用

            $countprice = $lineprice+$tprice+$delivecost;
            $value['delivecost'] = intval($delivecost);
            $value['tprice'] = intval($tprice);
            $value['linepice'] = intval($lineprice);
            $totalweight +=$value['totalweight']; //总重量
            $totalvolume +=$value['totalvolume']; //总体积
            $totalpick +=$tprice;//总提货费
            $totalline +=$lineprice;//总提货费
            $totalpei +=$delivecost;//总提货费
            $totalprice +=$countprice;//总价格
            $totalupprice += $value['totalcost'] =='' ? $countprice : $value['totalcost'];
            
            $value['totalprice'] = $value['totalcost'] =='' ? $countprice : $value['totalcost'];
            $value['doornum'] = 0;
            $value['ostate'] = 1;
            $arr[] = $value;
        }
        //市配
        $result_city = Db::table('ct_city_order')
                        ->alias('a')
                        ->join('ct_user u','u.uid=a.userid')
                        ->join('ct_company c','c.cid=u.lineclient')
                        ->join('ct_invoice inv','inv.iid=a.user_checkid')
                        ->field('a.*,c.name,u.realname,c.cid,u.lineclient,inv.Invoiceno,inv.Invoiceamount,inv.paytime,inv.unpass')
                        ->where($where_search2)
                        ->where($where_data)
                        ->paginate(50,false,$pageParam);
        $count_city_row = Db::table('ct_city_order')
                    ->alias('a')
                    ->join('ct_user u','u.uid=a.userid')
                    ->join('ct_company c','c.cid=u.lineclient')
                    ->join('ct_invoice inv','inv.iid=a.user_checkid')
                    ->where($where_search2)
                    ->where($where_data)
                    ->count('a.id');
        $city_data = $result_city->toArray();
        foreach ($city_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $ll[] = $value['user_checkid'];
            $ll2[$value['user_checkid']]['Invoiceno'] = $value['Invoiceno'];
            $ll2[$value['user_checkid']]['Invoiceamount'] = $value['Invoiceamount'];
            $ll2[$value['user_checkid']]['paytime'] = $value['paytime'];
            $ll2[$value['user_checkid']]['unpass'] = $value['unpass'];
            $arr_city[$key]['oid'] = $value['id']; //订单ID
            $arr_city[$key]['user_checkid'] = $value['user_checkid']; //对账ID
            $arr_city[$key]['line'] = '上海市';  //线路
            $arr_city[$key]['doornum'] = 0; //门店数
            $arr_city[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_city[$key]['lineclient'] = $value['lineclient']; //公司ID
            $arr_city[$key]['name'] = $value['name']; //公司名称
            $arr_city[$key]['ordernumber'] = $value['orderid']; //订单编号
            $arr_city[$key]['unpass'] = $value['unpass']; //是否销账
            $arr_city[$key]['totalweight'] = 0; //重量
            $arr_city[$key]['totalvolume'] = 0; //体积
            $arr_city[$key]['tprice'] = 0; //提货费
            $arr_city[$key]['linepice'] = 0; //干线费
            $arr_city[$key]['delivecost'] = 0; //配送费
            $countprice_city = $value['upprice'] =='' ? $value['actualprice'] : $value['upprice'];
            $arr_city[$key]['totalprice'] = $countprice_city;
            $totalprice_city +=$countprice_city;//总价格
            $arr_city[$key]['ostate'] = 3;
        }//城配       
        //整车
        $result_car = Db::table('ct_userorder')
                        ->alias('a')
                        ->join('ct_user u','u.uid=a.userid')
                        ->join('ct_company c','c.cid=u.lineclient')
                        ->join('ct_invoice inv','inv.iid=a.user_checkid')
                        ->field('a.*,u.realname,u.lineclient,c.name,c.cid,inv.usertype,inv.Invoiceno,inv.Invoiceamount,inv.paytime,inv.unpass')
                        ->where($where_search)
                        ->where($where_data)
                        ->paginate(50,false,$pageParam);
         $count_car_row = Db::table('ct_userorder')
                        ->alias('a')
                        ->join('ct_user u','u.uid=a.userid')
                        ->join('ct_company c','c.cid=u.lineclient')
                        ->join('ct_invoice inv','inv.iid=a.user_checkid')
                        ->where($where_search)
                        ->where($where_data)
                        ->count('uoid');
        $car_data = $result_car->toArray();
        foreach ($car_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $ll[] = $value['user_checkid'];
            $ll2[$value['user_checkid']]['Invoiceno'] = $value['Invoiceno'];
            $ll2[$value['user_checkid']]['Invoiceamount'] = $value['Invoiceamount'];
            $ll2[$value['user_checkid']]['paytime'] = $value['paytime'];
            $ll2[$value['user_checkid']]['unpass'] = $value['unpass'];
            $arr_car[$key]['oid'] = $value['uoid']; //订单ID
            $arr_car[$key]['user_checkid'] = $value['user_checkid']; //订单ID
            $arr_car[$key]['line'] = $this->start_end_city($value['startcity'],$value['endcity']);  //线路
            $arr_car[$key]['doornum'] = 0; //门店数
            $arr_car[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_car[$key]['lineclient'] = $value['lineclient']; //公司ID
            $arr_car[$key]['name'] = $value['name']; //公司名称
            $arr_car[$key]['ordernumber'] = $value['ordernumber']; //订单编号
            $arr_car[$key]['unpass'] = $value['unpass']; //是否销账
            $arr_car[$key]['totalweight'] = 0; //重量
            $arr_car[$key]['totalvolume'] = 0; //体积
            $arr_car[$key]['tprice'] = 0; //提货费
            $arr_car[$key]['linepice'] = 0; //干线费
            $arr_car[$key]['delivecost'] = 0; //配送费
            $countprice_car = $value['upprice'] =='' ? $value['referprice'] : $value['upprice'];
            $arr_car[$key]['totalprice'] = $countprice_car;
            $totalprice_car +=$countprice_car;//总价格
            $arr_car[$key]['ostate'] = 4;
        }//整车
        //订单个数
        $count_row = array('order'=>intval($count_order_row),'shift'=>intval($count_shift_row),'city'=>intval($count_city_row),'car'=>intval($count_car_row));
        $pos = array_search(max($count_row), $count_row);
        switch ($pos) {
            case 'order':
                $page = $result->render();
                break;
            case 'city':
                $page = $result_city->render();
                break;
            case 'car':
                $page = $result_car->render();
                break;
            case 'shift':
                $page = $result_shift->render();
                break;
        }
        $list = array_merge($arr_shift,$arr,$arr_city,$arr_car);
        if (!empty($list)) {
            $list = $this->my_sort($list,'addtime',SORT_DESC);
        }
        
        $ordercount = count($list);
        $count = array_unique($ll);
        $comnum = array_unique($com_arr); //公司个数
        $dat = array();
        $str_inv = '';
        if (count($ll2) =='1') {
            foreach ($ll2 as $b) {
                $dat = $b;
            }
        }else{
           foreach ($ll2 as $key => $value) {
              $str_inv .= $value['Invoiceno'] .' / ';
           }
        }
        if (count($count) > 1) {
          $this->assign('countarr','listtwo'); 
          $this->assign('listcount',rtrim($str_inv,' / '));
          $this->assign('listnum',count($ll2));
        }else{
          $this->assign('countarr','listone');
          $this->assign('listcount',$dat);
        }
        $this->assign('ordercount',$ordercount);
        $this->assign('list',$list);
        $this->assign('count',$counarr);
        $this->assign('comnum',count($comnum));
        $this->assign('page',$page);
        return view('account/cancel');
    }
    /*
    *财务销账
    */
  public function passcheck(){
    $post_data = Request::instance()->post();
    if ($post_data['ajax'] == 1) {
      $uid = Session::get('admin_id','admin_mes');

      $data['optime'] = strtotime($post_data['paytime']);
      $data['financeuid'] = $uid;
      $data['unpass'] = 2;
      $id = $post_data['invoID'][0];
      //零担干线总额
      $count_order_line = Db::table('ct_order')->where('user_checkid',$id)->sum('linepice');
      //零担提货总额
      $count_order_pick = Db::table('ct_order')->where('user_checkid',$id)->sum('pickcost');
      //零担配送总额
      $count_order_send = Db::table('ct_order')->where('user_checkid',$id)->sum('delivecost');
      //定制总额
      $count_shift = Db::table('ct_shift_order')->where('user_checkid',$id)->sum('totalprice');
      //城配总额
      $count_city = Db::table('ct_city_order')->where('user_checkid',$id)->sum('paymoney');
      //整车总额
      $count_car = Db::table('ct_userorder')->where('user_checkid',$id)->sum('price');
      $total = $count_order_line+$count_order_pick+$count_order_send+$count_shift+$count_city+$count_car;
      $company = Db::table('ct_invoice')
                    ->alias('a')
                    ->join('ct_company c','c.cid=a.companyid')
                    ->field('c.money,a.companyid,a.sermonth,c.name')
                    ->where('iid',$id)
                    ->find();
        Db::table('ct_company')->where('cid',$company['companyid'])->update(array('money'=>$company['money']+$total));
        $content = $company['name'].' '.date('Y-m',$company['sermonth'])."销账成功，项目客户账号恢复余额为:".$total;
        $this->hanldlog($this->uid,$content);
      $up = DB::table('ct_invoice')->where('iid',$id)->update($data);
      $check_data['usercheck'] = 3;
      Db::table('ct_order')->where('user_checkid',$id)->update($check_data);
      Db::table('ct_shift_order')->where('user_checkid',$id)->update($check_data);
      Db::table('ct_city_order')->where('user_checkid',$id)->update($check_data);
      Db::table('ct_userorder')->where('user_checkid',$id)->update($check_data);
      if ($up){
        print_r('ok');
      }else{
        print_r('fail');
      }
    }
  }

    //平台主动对账确定
  public function determine(){
      $post_data = Request::instance()->post();
      $invoice_where['iid'] = $post_data['invoID'][0];
      $invoice_data['confirm'] = '1';
      $result = Db::table('ct_invoice')->where($invoice_where)->update($invoice_data);
      if ($result){
        print_r('ok');
      }else{
        print_r('fail');
      }

  }

    //修改平台及对账单价格
    public function updateprice(){
        $post_data = Request::instance()->post();
        $post_price = $post_data['price'];
        $orderid = $post_data['orderid'];
        $ostate = $post_data['ostate'];
        $data['upprice'] = $post_price;
        if ($ostate == '1') {  //零担
            $data_o['totalcost'] = $post_price;
            $res = Db::table('ct_order')->where('oid',$orderid)->update($data_o);
        }elseif($ostate == '2'){  //定制
            $res = Db::table('ct_shift_order')->where('s_oid',$orderid)->update($data);
        }elseif($ostate == '3'){  //市配
            $res = Db::table('ct_city_order')->where('id',$orderid)->update($data);
        }else{
            $res = Db::table('ct_userorder')->where('uoid',$orderid)->update($data);
        }
        if ($res) {
            $result['code'] = true;
        }else{
            $result['code'] = false;
    }
    echo json_encode($result);
  }


    
}
