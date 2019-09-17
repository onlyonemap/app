<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Carraccount  extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//
//    }

	 //承运商销账首页
public function index(){
    //orderstatus 1、接单为整条干线 2、提货公司接单 3、配送公司接单
    $line_arr = array();  //一条干线上的订单
    $pick_arr = array();  //只接提货的订单
    $all_arr = array();   //干线上的订单
    $arr_shift = array();     //定制线路订单
    $arr_city = array();  //城配线路订单
    $arr_car = array();  //城配线路订单
    $com_arr = array(); //统计公司个数
    // search condition start 
    $where='';
    $where_search='';
    $where_search2='';
    $search = Request::instance()->get();
    $pageParam    = ['query' =>[]];
    if (!empty($search['company'])) {
        //$where['com.name'] = ['like','%'.$search['company'].'%'];
        $where_search['com.name|ordernumber|ucom.name|user.phone'] = ['like','%'.$search['company'].'%'];
        $where_search2['com.name|orderid|ucom.name|user.phone'] = ['like','%'.$search['company'].'%'];
        $pageParam['query']['company'] = $search['company'];
    }
    if (!empty($search['starttime']) && !empty($search['endtime'])) {
        $endtime = strtotime(trim($search['endtime']).'23:59:59');
        $starttime = strtotime(trim($search['starttime']).'00:00:00');
        $where['b.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
        $pageParam['query']['starttime'] =$starttime;
        $pageParam['query']['endtime'] = $endtime;
    }
    // search condition end 
    //一条干线上的订单
    $where_order['a.checkyesno'] = 1;
    $where_order['b.orderstate'] = 7;
    $where_order['a.type'] = 2;
    $where_order['s.shiftstate'] = 1;
    $result_line = Db::field('a.driverid,s.companyid,a.orderid,a.tprice,b.totalweight,b.totalvolume,ct.start_id,b.userid,
                            ct.end_id,com.name dcompany,b.ordernumber,b.linepice,b.delivecost,b.addtime,b.totalnumber,b.totalweight,
                            b.totalvolume,b.totalcost,a.tcarr_upprice,l.lcarr_price,d.pcarr_upprice,ucom.name usercompany,user.phone')
            ->table('ct_pickorder')
            ->alias('a')
            ->join('ct_order b','b.oid = a.orderid')
            ->join('ct_user user','user.uid = b.userid')
            ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
            ->join('ct_lineorder l','b.oid=l.orderid')
            ->join('ct_delorder d','b.oid=d.orderid')
            ->join('ct_shift s','s.sid = b.shiftid')
            ->join('ct_already_city ct','ct.city_id = s.linecityid')
            ->join('ct_company com','com.cid = s.companyid')
            ->where($where)
            ->where($where_search)
            ->where($where_order)
            ->paginate(30,false, $pageParam);
    //统计订单个数
    $count_order_row = Db::table('ct_pickorder')
                    ->alias('a')
                    ->join('ct_order b','b.oid = a.orderid')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_shift s','s.sid = b.shiftid')
                    ->join('ct_company com','com.cid = s.companyid')
                    ->where($where)
                    ->where($where_search)
                    ->where($where_order)
                    ->count('b.oid');
    $line_data = $result_line->toArray();
    foreach ($line_data['data'] as $value2) {
        $com_arr[] = $value2['companyid'];
        $value2['line'] =  $this->start_end_city($value2['start_id'],$value2['end_id']);
        $tprice = $value2['tcarr_upprice']=='' ? $value2['tprice'] : $value2['tcarr_upprice'];  //提货费
        $value2['tprice']=$tprice;
        $linepice = $value2['lcarr_price']=='' ? $value2['linepice'] : $value2['lcarr_price'];  //干线费
        $value2['linepice']=$linepice;
        $delivecost = $value2['pcarr_upprice']=='' ? $value2['delivecost'] : $value2['pcarr_upprice'];  //干线费
        $value2['delivecost']=$delivecost;
        $total = $tprice + $linepice+$delivecost; //总运费
        $value2['countcoat'] =  $total;
        $value2['orderstatus'] = 1;
        $value2['ostate'] = 1;
        $value2['doornum'] = 0;
        $value2['name'] = $value2['dcompany'];
        //下单人信息
        $value2['clinemess'] = $this->cline_mess($value2['userid']);
        $all_arr[] = $value2;
    }
    //定制订单
    $where_shift['orderstate']=3;
    $where_shift['checkyesno']=1;
    $where_shift['affirm']=2;
    $result_shift = Db::table('ct_shift_order')
                    ->alias('b')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_fixation_line f','b.shiftid=f.id')
                    ->join('ct_company com','com.cid=f.carrierid')
                    ->field('b.*,com.name dcompany,com.cid,f.carr_price,f.lienid,ucom.name,user.phone')
                    ->where($where)
                    ->where($where_shift)
                    ->where($where_search)
                    ->paginate(30,false,$pageParam);
    $count_shift_row = Db::table('ct_shift_order')
                    ->alias('b')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_fixation_line f','b.shiftid=f.id')
                    ->join('ct_company com','com.cid=f.carrierid')
                    ->where($where)
                    ->where($where_shift)
                    ->where($where_search)
                    ->count('b.s_oid');
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
        $arr_shift[$key]['orderid'] = $value['s_oid']; //订单ID
        $arr_shift[$key]['line'] = $startcity .'--'.$endcity;  //线路
        $arr_shift[$key]['doornum'] = $value['doornum']; //门店数
        $arr_shift[$key]['addtime'] = $value['addtime']; //下单时间
        $arr_shift[$key]['companyid'] = $value['cid']; //公司ID
        $arr_shift[$key]['name'] = $value['dcompany']; //公司名称
        $arr_shift[$key]['ordernumber'] = $value['ordernumber']; //订单编号
        $arr_shift[$key]['totalweight'] = 0; //重量
        $arr_shift[$key]['totalvolume'] = 0; //体积
        $arr_shift[$key]['tprice'] = 0; //提货费
        $arr_shift[$key]['linepice'] = 0; //干线费
        $arr_shift[$key]['delivecost'] = 0; //配送费
        $arr_shift[$key]['orderstatus'] = 4;
        $arr_shift[$key]['ostate'] = 2;
         //下单人信息
        $arr_shift[$key]['clinemess'] = $this->cline_mess($value['userid']);
        $totalprice = $value['price'];
        $countprice_shift = $value['carr_upprice'] =='' ? $totalprice : $value['carr_upprice'];
        $arr_shift[$key]['countcoat'] = $countprice_shift;
        $arr_shift[$key]['ostate'] = 2;
    }

    //城配订单
    $where_city['state']=3;
    $where_city['checkyesno']=1;
    $where_city['d.type'] = ['IN','2,3'];
    $result_city = Db::table('ct_city_order')
                    ->alias('b')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_rout_order r','r.rid=b.rout_id')
                    ->join('ct_driver d','d.drivid=r.driverid')
                    ->join('ct_company com','com.cid=d.companyid')
                    ->field('b.*,com.name dcompany,com.cid,ucom.name,user.phone')
                    ->where($where)
                    ->where($where_city)
                    ->where($where_search2)
                    ->paginate(30,false,$pageParam);
    $count_city_row = Db::table('ct_city_order')
                    ->alias('b')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_rout_order r','r.rid=b.rout_id')
                    ->join('ct_driver d','d.drivid=r.driverid')
                    ->join('ct_company com','com.cid=d.companyid')
                    ->where($where)
                    ->where($where_city)
                    ->where($where_search2)
                    ->count('id');
    $city_data = $result_city->toArray();
    foreach ($city_data['data'] as $key => $value) {
        $com_arr[] = $value['cid'];
        $arr_city[$key]['orderid'] = $value['id']; //订单ID
        $arr_city[$key]['line'] = '上海市';  //线路
        $arr_city[$key]['doornum'] = 0; //门店数
        $arr_city[$key]['addtime'] = $value['addtime']; //下单时间
        $arr_city[$key]['companyid'] = $value['cid']; //公司ID
        $arr_city[$key]['name'] = $value['dcompany']; //公司名称
        $arr_city[$key]['ordernumber'] = $value['orderid']; //订单编号
        $arr_city[$key]['totalweight'] = 0; //重量
        $arr_city[$key]['totalvolume'] = 0; //体积
        $arr_city[$key]['tprice'] = 0; //提货费
        $arr_city[$key]['linepice'] = 0; //干线费
        $arr_city[$key]['delivecost'] = 0; //配送费
        $arr_city[$key]['orderstatus'] = 4;
        //下单人信息
        $arr_city[$key]['clinemess'] = $this->cline_mess($value['userid']);
        $countprice_city = $value['carr_upprice'] =='' ? $value['paymoney'] : $value['carr_upprice'];
        $arr_city[$key]['countcoat'] = $countprice_city;
        $arr_city[$key]['ostate'] = 3;
    }//市配

    //整车
    $where_car['orderstate'] = 3;
    $where_car['checkyesno'] = 1;
    $where_car['u.type'] = ['IN','2,3'];
    $result_car = Db::table('ct_userorder')
                    ->alias('b')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_driver u','u.drivid=b.carriersid')
                    ->join('ct_company com','com.cid=u.companyid')
                    ->field('b.*,com.name dcompany,com.cid,ucom.name,user.phone')
                    ->where($where)
                    ->where($where_car)
                    ->where($where_search)
                    ->paginate(30,false,$pageParam);
    $count_car_row = Db::table('ct_userorder')
                    ->alias('b')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_driver u','u.drivid=b.carriersid')
                    ->join('ct_company com','com.cid=u.companyid')
                    ->where($where)
                    ->where($where_car)
                    ->where($where_search)
                    ->count('uoid');
    $car_data = $result_car->toArray();
    foreach ($car_data['data'] as $key => $value) {
        $com_arr[] = $value['cid'];
        $arr_car[$key]['orderid'] = $value['uoid']; //订单ID
        $arr_car[$key]['line'] = $this->start_end_city($value['startcity'],$value['endcity']);  //线路
        $arr_car[$key]['doornum'] = 0; //门店数
        $arr_car[$key]['addtime'] = $value['addtime']; //下单时间
        $arr_car[$key]['companyid'] = $value['cid']; //公司ID
        $arr_car[$key]['name'] = $value['dcompany']; //公司名称
        $arr_car[$key]['ordernumber'] = $value['ordernumber']; //订单编号
        $arr_car[$key]['totalweight'] = 0; //重量
        $arr_car[$key]['totalvolume'] = 0; //体积
        $arr_car[$key]['tprice'] = 0; //提货费
        $arr_car[$key]['linepice'] = 0; //干线费
        $arr_car[$key]['delivecost'] = 0; //配送费
        $arr_car[$key]['orderstatus'] = 4;
        $countprice_car = $value['carr_upprice'] =='' ? $value['price'] : $value['carr_upprice'];
        $arr_car[$key]['countcoat'] = $countprice_car;
        $arr_car[$key]['ostate'] = 4;
        //下单人信息
        $arr_car[$key]['clinemess'] = $this->cline_mess($value['userid']);
    }//整车
    //订单个数
    $count_row = array('order'=>intval($count_order_row),'shift'=>intval($count_shift_row),'city'=>intval($count_city_row),'car'=>intval($count_car_row));
    $pos = array_search(max($count_row), $count_row);
    switch ($pos) {
        case 'order':
            $page = $result_line->render();
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
    //合并订单
    $allarr = array_merge($all_arr,$pick_arr,$line_arr,$arr_shift,$arr_city,$arr_car);
    if (!empty($allarr)) {
      $allarr = $this->my_sort($allarr,'addtime',SORT_DESC);
    }
    $comnum = 0;
    $ordercount=0;
    $comnum = array_unique($com_arr); //公司去重
    $ordercount = count($allarr);  //统计订单个数
    $this->assign('comnum',count($comnum));
    $this->assign('ordercount',$ordercount);
    $this->assign('page',$page);  //统计费用价格
    $this->assign('list',$allarr);
    return view('carraccount/index'); 
}
    /*
    *添加销账记录
    */
    public function addinvo(){
      //ostate 1零担，2定制 ，3城配 4整车
      //statusarr 1整条干线  2只接提货 3只接干线
      $price = 0;
      $totalprice = 0;
      $batch ='';
      $post_data = Request::instance()->post();
      $arr = array();
      $ostate = $post_data['ostate'];
      $order_arr = $post_data['order'];
      $statusarr = $post_data['statusarr'];
      $price_oneline = 0; //零担一条线上
      $price_pick = 0; //只接干线单
      $price_line = 0; //只接干线
      $price_shift = 0;  //定制
      $price_order = 0;  //零担
      $price_city = 0;  //城配
      $price_car = 0;  //整车
      $shift_str = ''; //定制
      $order_str = ''; //零担
      $city_str = ''; //市配
      $car_str = ''; //整车
      $arr2 = array('shift'=>'','order'=>'','city'=>'','car'=>'');
      $i=0;
      /*foreach ($post_data['order'] as $key => $value) {
        $arr[$value] = $post_data['statusarr'][$key]; 
      }*/
      foreach ($order_arr as $key => $value) {
            $arr[$i]['b'] = $order_arr[$key];
            $arr[$i]['a'] = $ostate[$key];
            $arr[$i]['c'] = $statusarr[$key];
            $i++;
      }
      foreach ($arr as $key => $info) {
        if ($info['a'] == '2') {  //定制
          $shift_str .= $info['b'].',';
          $arr2['shift'][] = $info['b'];
        }
        if ($info['a'] == '1') {  //零担
          $order_str .= $info['b'].',';
          $arr2['order'][$info['b']] = $info['c'];
        }
        if ($info['a'] == '3') {  //市配
          $city_str .= $info['b'].',';
          $arr2['city'][] = $info['b'];
        }
        if ($info['a'] == '4') {  //整车
          $car_str .= $info['b'].',';
          $arr2['car'][] = $info['b'];
        }

      }
     
      //获取添加对账时的总价歌 orderstatus 为1时整条订单为干线承接  为2时只接了提货单 为3时提货被接走
        if ($order_str !='') { //零担
            foreach ($arr2['order'] as $k => $val) {
                if($val == 1) {
                    $line = Db::field('a.linepice,l.lcarr_price,a.delivecost,d.pcarr_upprice,pic.tcarr_upprice,pic.tprice')
                          ->table('ct_order')
                          ->alias('a')
                          ->join('ct_pickorder pic','pic.orderid = a.oid')
                          ->join('ct_delorder d','d.orderid=a.oid')
                          ->join('ct_lineorder l','l.orderid=a.oid')
                          ->where('oid',$k)
                          ->find();
                    $lineprice = $line['lcarr_price']=='' ? $line['linepice'] : $line['lcarr_price'];
                    $tprice = $line['tcarr_upprice']=='' ? $line['tprice'] : $line['tcarr_upprice'];
                    $delivecost = $line['pcarr_upprice']=='' ? $line['delivecost'] : $line['pcarr_upprice'];
                    $price_oneline += $lineprice+$tprice+$delivecost;
                }
            }// end foreach
            $totalprice = $price_oneline;
        }// end零担

      if ($shift_str !='') { //定制
        $fix_arr = Db::table('ct_shift_order')
                    ->alias('a')
                    ->join('ct_fixation_line f','f.id=a.shiftid')
                    ->field('a.totalprice,a.carr_upprice,a.price,a.doornum')
                    ->where('s_oid','IN',rtrim($shift_str,','))
                    ->select();
        foreach ($fix_arr as $key => $value) {
          $price_shift += $value['price']==''? $value['totalprice'] : $value['price'];
        } 
      }
     
      if ($city_str !='') { //城配
        $city_arr = Db::table('ct_city_order')
                  ->field('paymoney,carr_upprice')
                  ->where('id','IN',rtrim($city_str,','))
                  ->select();
        foreach ($city_arr as $key => $value) {
          $price_city += $value['carr_upprice']==''? $value['paymoney'] : $value['carr_upprice'];
        } 
      }
      if ($car_str !='') { //整车
        $car_arr = Db::table('ct_userorder')
                  ->field('price,carr_upprice')
                  ->where('uoid','IN',rtrim($car_str,','))
                  ->select();
        foreach ($car_arr as $key => $value) {
          $price_car += $value['carr_upprice']==''? $value['price'] : $value['carr_upprice'];
        } 
      }
      $total = $totalprice+$price_shift+$price_city+$price_car;
      $company = Db::field('type')->table('ct_company')->where('cid',$post_data['companyid'][0])->find();
      //var_dump($company);
      $sermonth = strtotime($post_data['moth']);
      $invo_data['sermonth'] = $sermonth;
      $invo_data['companyid'] = $post_data['companyid'][0];
      $invo_data['usertype'] = $company['type'];
      $invo_data['totalprice'] = $total;
      $invo_data['firtime'] = time();
       $invo= DB::table('ct_invoice')
              ->where(array('companyid'=>$post_data['companyid'][0],'sermonth'=>$sermonth))
              ->count();
       if ($invo ==0) {
          $batch =1;
        }else{
          $batch = $invo +1;
        }
        $invo_data['batch'] = $batch;
        $insert_data = DB::table('ct_invoice')->insertGetId($invo_data);
        $line_data['checkyesno'] = 2;
        $line_data['line_checkid']  = $insert_data;
        $pick_data['checkyesno'] = 2;
        $pick_data['pic_checkid']  = $insert_data;
        if (!empty($arr2['order'])) {
           foreach ($arr2['order'] as $key2 => $value2) {
                if ($value2 ==1) {
                    $update_line = DB::table('ct_lineorder')->where('orderid',$key2)->update($line_data);
                    $update_pick = DB::table('ct_pickorder')->where('orderid',$key2)->update($pick_data);
                }
           }
        }
        $data['checkyesno'] = 2;
        $data['carr_checkid'] = $insert_data;
        if ($shift_str !='') {
            Db::table('ct_shift_order')->where('s_oid','IN',$shift_str)->update($data);
        }
        if ($city_str !='') {
            Db::table('ct_city_order')->where('id','IN',$city_str)->update($data);
        }
        if ($car_str !='') {
            Db::table('ct_userorder')->where('uoid','IN',$car_str)->update($data);
        }
       if($insert_data) {
            print_r('ok');
      }else{
            print_r('fail');
      }
     
    }
    /*
    *账单确认
    */
    public function carrcheck(){
        $arr_shift = array(); //定制
        $arr_city = array(); //城配
        $arr_car = array(); //城配
        $line_arr = array();
        $pick_arr = array();
        $all_arr = array();
        $all_checkid = array(); //所有对账ID
        $check_mess = array(); //对账信息
        $com_arr = array(); //公司ID
        // search condition start 
        $search = Request::instance()->get();
        $where_search = '';
        $where_search2 = '';
        $pageParam    = ['query' =>[]];
        if (!empty($search['company'])) {
            $where_search['com.name|ordernumber|ucom.name|user.phone'] = ['like','%'.$search['company'].'%'];
            $where_search2['com.name|orderid|ucom.name|user.phone'] = ['like','%'.$search['company'].'%'];
            $pageParam['query']['company'] =$search['company'];
        }
        if (!empty($search['sermonth'])) {
            $sermonth = strtotime(trim($search['sermonth']));
            $where_data['in.sermonth'] = ['EQ',$sermonth];
            $pageParam['query']['sermonth'] =$search['sermonth'];
        }
        $where_data['in.Invoiceno'] = '';
        $where_data['in.unpass'] = 1; 
        // search condition end 
        //一条干线上的订单
        $where_line['b.orderstate'] = 7;
        $where_line['a.type'] = 2;
        $where_line['a.checkyesno'] = 2;
        $result_line = Db::field('a.driverid,a.tcarr_upprice,s.companyid,a.orderid,a.pic_checkid,a.tprice,b.totalweight,b.totalvolume,
                        ct.start_id,ct.end_id,com.name dcompany,b.ordernumber,b.linepice,b.delivecost,b.addtime,b.totalnumber,l.lcarr_price,
                        b.totalcost,in.sermonth,in.confirm,in.self_total,in.self_remark,d.pcarr_upprice,b.userid,ucom.name,user.phone')
                ->table('ct_pickorder')
                ->alias('a')
                ->join('ct_order b','b.oid = a.orderid')
                ->join('ct_user user','user.uid = b.userid')
                ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                ->join('ct_lineorder l','l.orderid=b.oid')
                ->join('ct_delorder d','d.orderid = b.oid')
                ->join('ct_invoice in','in.iid = a.pic_checkid')
                ->join('ct_shift s','s.sid = b.shiftid')
                ->join('ct_already_city ct','ct.city_id = s.linecityid')
                ->join('ct_company com','com.cid = s.companyid')
                ->where($where_data)
                ->where($where_line)
                ->where($where_search)
                ->paginate(50,false,$pageParam);
        //统计订单个数
        $count_order_row = Db::table('ct_pickorder')
                    ->alias('a')
                    ->join('ct_order b','b.oid = a.orderid')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_invoice in','in.iid = a.pic_checkid')
                    ->join('ct_shift s','s.sid = b.shiftid')
                    ->join('ct_company com','com.cid = s.companyid')
                    ->where($where_data)
                    ->where($where_line)
                    ->where($where_search)
                    ->count('b.oid');
        $order_data = $result_line->toArray();
        foreach ($order_data['data'] as $value2) {
            $com_arr[] = $value2['companyid'];
            $all_checkid[] = $value2['pic_checkid'];
            $check_mess[$value2['pic_checkid']]['confirm'] = $value2['confirm'];
            $check_mess[$value2['pic_checkid']]['self_total'] = $value2['self_total'];
            $check_mess[$value2['pic_checkid']]['self_remark'] = $value2['self_remark'];
            $check_mess[$value2['pic_checkid']]['sermonth'] = $value2['sermonth'];
            $value2['invoID'] =  $value2['pic_checkid'];
            $value2['line'] =  $this->start_end_city($value2['start_id'],$value2['end_id']);
            $tprice = $value2['tcarr_upprice']=='' ? $value2['tprice'] : $value2['tcarr_upprice'];
            $linepice = $value2['lcarr_price']=='' ? $value2['linepice'] : $value2['lcarr_price'];
            $delivecost = $value2['pcarr_upprice']=='' ? $value2['delivecost'] : $value2['pcarr_upprice'];
            $total_oneline = $tprice + $linepice + $delivecost;
            $value2['countcoat'] =  $total_oneline;
            $value2['orderstatus'] = 1;
            $value2['ostate'] = 1;
            $value2['doornum'] = 0;
            $value2['name'] = $value2['dcompany'];
            //下单人信息
            $value2['clinemess'] = $this->cline_mess($value2['userid']);
            $all_arr[] = $value2;
        }
        //定制订单
        $where_shift['orderstate']=3;
        $where_shift['checkyesno'] = 2;
        $result_shift = Db::table('ct_shift_order')
                        ->alias('b')
                        ->join('ct_user user','user.uid = b.userid')
                        ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                        ->join('ct_fixation_line f','b.shiftid=f.id')
                        ->join('ct_company com','com.cid=f.carrierid')
                        ->join('ct_invoice in','in.iid=b.carr_checkid')
                        ->field('b.*,com.name dcompany,com.cid,f.lienid,in.sermonth,in.confirm,in.self_total,in.self_remark,user.phone,ucom.name')
                        ->where($where_data)
                        ->where($where_shift)
                        ->where($where_search)
                        ->paginate(50,false,$pageParam);
        $count_shift_row = Db::table('ct_shift_order')
                    ->alias('b')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_fixation_line f','b.shiftid=f.id')
                    ->join('ct_company com','com.cid=f.carrierid')
                    ->join('ct_invoice in','in.iid=b.carr_checkid')
                    ->where($where_data)
                    ->where($where_shift)
                    ->where($where_search)
                    ->count('b.s_oid');
        $shift_data = $result_shift->toArray();
        foreach ($shift_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $check_mess[$value['carr_checkid']]['confirm'] = $value['confirm'];
            $check_mess[$value['carr_checkid']]['self_total'] = $value['self_total'];
            $check_mess[$value['carr_checkid']]['self_remark'] = $value['self_remark'];
            $check_mess[$value['carr_checkid']]['sermonth'] = $value['sermonth'];
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
            $arr_shift[$key]['orderid'] = $value['s_oid']; //订单ID
            $arr_shift[$key]['line'] = $startcity .'--'.$endcity;  //线路
            $arr_shift[$key]['doornum'] = $value['doornum']; //门店数
            $arr_shift[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_shift[$key]['companyid'] = $value['cid']; //公司ID
            $arr_shift[$key]['name'] = $value['dcompany']; //公司名称
            $arr_shift[$key]['ordernumber'] = $value['ordernumber']; //订单编号
            $arr_shift[$key]['totalweight'] = 0; //重量
            $arr_shift[$key]['totalvolume'] = 0; //体积
            $arr_shift[$key]['tprice'] = 0; //提货费
            $arr_shift[$key]['linepice'] = 0; //干线费
            $arr_shift[$key]['delivecost'] = 0; //配送费
            $arr_shift[$key]['orderstatus'] = 4;
            $arr_shift[$key]['ostate'] = 2;
            $arr_shift[$key]['invoID'] = $value['carr_checkid'];
            $totalprice = $value['price'];
            $countprice_shift = $value['carr_upprice'] =='' ? $totalprice : $value['carr_upprice'];
            $arr_shift[$key]['countcoat'] = $countprice_shift;
            //下单人信息
            $arr_shift[$key]['clinemess'] = $this->cline_mess($value['userid']);
            $arr_shift[$key]['ostate'] = 2;
        }

        //城配订单
        $where_city['state']=3;
        $where_city['checkyesno'] = 2;
        $result_city = Db::table('ct_city_order')
                    ->alias('b')
                    ->join('ct_rout_order r','r.rid=b.rout_id')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_driver d','d.drivid=r.driverid')
                    ->join('ct_company com','com.cid=d.companyid')
                    ->join('ct_invoice in','in.iid=b.carr_checkid')
                    ->field('b.*,com.name dcompany,com.cid,in.sermonth,in.confirm,in.self_total,in.self_remark,ucom.name,user.phone')
                    ->where($where_data)
                    ->where($where_city)
                    ->where($where_search2)
                    ->paginate(50,false,$pageParam);
        $count_city_row = Db::table('ct_city_order')
                    ->alias('b')
                    ->join('ct_rout_order r','r.rid=b.rout_id')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_invoice in','in.iid=b.carr_checkid')
                    ->join('ct_driver d','d.drivid=r.driverid')
                    ->join('ct_company com','com.cid=d.companyid')
                    ->where($where_data)
                    ->where($where_city)
                    ->where($where_search2)
                    ->count('b.id');
        $city_data = $result_city->toArray();
        foreach ($city_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $check_mess[$value['carr_checkid']]['confirm'] = $value['confirm'];
            $check_mess[$value['carr_checkid']]['self_total'] = $value['self_total'];
            $check_mess[$value['carr_checkid']]['self_remark'] = $value['self_remark'];
            $check_mess[$value['carr_checkid']]['sermonth'] = $value['sermonth'];
            $arr_city[$key]['orderid'] = $value['id']; //订单ID
            $arr_city[$key]['line'] = '上海市';  //线路
            $arr_city[$key]['doornum'] = 0; //门店数
            $arr_city[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_city[$key]['companyid'] = $value['cid']; //公司ID
            $arr_city[$key]['name'] = $value['dcompany']; //公司名称
            $arr_city[$key]['ordernumber'] = $value['orderid']; //订单编号
            $arr_city[$key]['totalweight'] = 0; //重量
            $arr_city[$key]['totalvolume'] = 0; //体积
            $arr_city[$key]['tprice'] = 0; //提货费
            $arr_city[$key]['linepice'] = 0; //干线费
            $arr_city[$key]['delivecost'] = 0; //配送费
            $arr_city[$key]['orderstatus'] = 4;
            $arr_city[$key]['invoID'] = $value['carr_checkid'];
            $countprice_city = $value['carr_upprice'] =='' ? $value['paymoney'] : $value['carr_upprice'];
            $arr_city[$key]['countcoat'] = $countprice_city;
            $arr_city[$key]['ostate'] = 3;
            //下单人信息
            $arr_city[$key]['clinemess'] = $this->cline_mess($value['userid']);
        }//市配

    //整车
    $where_car['orderstate'] = 3;
    $where_car['checkyesno'] = 2;
    $result_car = Db::table('ct_userorder')
                    ->alias('b')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_driver u','u.drivid=b.carriersid')
                    ->join('ct_company com','com.cid=u.companyid')
                    ->join('ct_invoice in','in.iid=b.carr_checkid')
                    ->field('b.*,com.name dcompany,com.cid,in.sermonth,in.confirm,in.self_total,in.self_remark,user.phone,ucom.name')
                    ->where($where_data)
                    ->where($where_car)
                    ->where($where_search)
                    ->paginate(50,false,$pageParam);
    $count_car_row = Db::table('ct_userorder')
                    ->alias('b')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_invoice in','in.iid=b.carr_checkid')
                    ->join('ct_driver u','u.drivid=b.carriersid')
                    ->join('ct_company com','com.cid=u.companyid')
                    ->where($where_data)
                    ->where($where_car)
                    ->where($where_search)
                    ->count('uoid');
    $car_data = $result_car->toArray();
    foreach ($car_data['data'] as $key => $value) {
        $com_arr[] = $value['cid'];
        $check_mess[$value['carr_checkid']]['confirm'] = $value['confirm'];
        $check_mess[$value['carr_checkid']]['self_total'] = $value['self_total'];
        $check_mess[$value['carr_checkid']]['self_remark'] = $value['self_remark'];
        $check_mess[$value['carr_checkid']]['sermonth'] = $value['sermonth'];
        $arr_car[$key]['orderid'] = $value['uoid']; //订单ID
        $arr_car[$key]['line'] = $this->start_end_city($value['startcity'],$value['endcity']);  //线路
        $arr_car[$key]['doornum'] = 0; //门店数
        $arr_car[$key]['addtime'] = $value['addtime']; //下单时间
        $arr_car[$key]['companyid'] = $value['cid']; //公司ID
        $arr_car[$key]['name'] = $value['dcompany']; //公司名称
        $arr_car[$key]['ordernumber'] = $value['ordernumber']; //订单编号
        $arr_car[$key]['totalweight'] = 0; //重量
        $arr_car[$key]['totalvolume'] = 0; //体积
        $arr_car[$key]['tprice'] = 0; //提货费
        $arr_car[$key]['linepice'] = 0; //干线费
        $arr_car[$key]['delivecost'] = 0; //配送费
        $arr_car[$key]['orderstatus'] = 4;
        $arr_car[$key]['invoID'] = $value['carr_checkid'];
        $countprice_car = $value['carr_upprice'] =='' ? $value['price'] : $value['carr_upprice'];
        $arr_car[$key]['countcoat'] = $countprice_car;
        $arr_car[$key]['ostate'] = 4;
        //下单人信息
        $arr_car[$key]['clinemess'] = $this->cline_mess($value['userid']);
    }//整车
     //订单个数
    $count_row = array('order'=>intval($count_order_row),'shift'=>intval($count_shift_row),'city'=>intval($count_city_row),'car'=>intval($count_car_row));
    $pos = array_search(max($count_row), $count_row);
    switch ($pos) {
        case 'order':
            $page = $result_line->render();
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
      $countun = array_unique($all_checkid);
        if (!empty($countun)) {
            if (count($countun) > 1) {
              $this->assign('countarr','listtwo');
            }else{
              $countlist = DB::table('ct_invoice')->where('iid',$countun[0])->find();
              $this->assign('listcount',$countlist['sermonth']);
            }
        }
      //合并三种可能数组
        $allarr = array_merge($all_arr,$pick_arr,$line_arr,$arr_shift,$arr_city,$arr_car);

        if (!empty($allarr)) {
            $allarr = $this->my_sort($allarr,'addtime',SORT_DESC);
        }

        $dat = array();
        if (count($check_mess) =='1') {
            foreach ($check_mess as $b) {
                $dat = $b;
            }
        }
        if (count($check_mess) > 1) {
            $this->assign('countarr','listtwo');
        }else{
            $this->assign('countarr','listone');
            $this->assign('listcount',$dat);
        }
        $comnum = 0;
        $ordercount=0;
        $comnum = array_unique($com_arr); //公司去重
        $ordercount = count($allarr);  //统计订单个数
        $this->assign('comnum',count($comnum));
        $this->assign('ordercount',$ordercount);
        $this->assign('list',$allarr);
        $this->assign('page',$page);
        return view('carraccount/carrcheck');
    }
    /*
    *修改总价
    */
    public function update(){
        $getid = Request::instance()->get();
        $invo = Db::table('ct_invoice')->where('iid',$getid['id'])->find();
        $this->assign('list',$invo);
        return view('carraccount/update');
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
    /*
    *删除确认账单操作
    *
    */
    public function delcheck(){
        $arr = array();
        $all_line_price = 0;
        $pick_line_price = 0;
        $line_line_price = 0;
        $get_price_order = 0;//零担价格
        $get_price_shift = 0;//定制价格
        $get_price_city = 0;//城配价格
        $get_price_car = 0;//整车价格
        $post_data = Request::instance()->post();
        $ostate = $post_data['ostate'];
        $order_arr = $post_data['orderID'];
        $statusarr = $post_data['statearr'];
        $invo_id = $post_data['invoID'][0];
        $arr2 = array('shift'=>'','order'=>'','city'=>'','car'=>'');
        $shift_str = ''; //定制
        $order_str = '';  // 零担
        $city_str = '';  // 城配
        $car_str = '';  // 整车

        $i=0;
        foreach ($order_arr as $key => $value) {
            $arr[$i]['b'] = $order_arr[$key];
            $arr[$i]['a'] = $ostate[$key];
            $arr[$i]['c'] = $statusarr[$key];
            $i++;
        }
        foreach ($arr as $key => $info) {
            if ($info['a'] == '2') {  //定制
                $shift_str .= $info['b'].',';
                $arr2['shift'][] = $info['b'];
            }
            if ($info['a'] == '1') {  //零担
                $order_str .= $info['b'].',';
                $arr2['order'][$info['b']] = $info['c'];
            }
            if ($info['a'] == '3') {  //市配
                $city_str .= $info['b'].',';
                $arr2['city'][] = $info['b'];
            }
            if ($info['a'] == '4') {  //整车
                $car_str .= $info['b'].',';
                $arr2['car'][] = $info['b'];
            }

        }

      //print_r($arr2);exit();
     
    
      //获取删除对账时的价格并修改状态 orderstatus 为1时整条订单为干线承接  为2时只接了提货单 为3时提货被接走
        if ($order_str !='') { //零担
            foreach ($arr2['order'] as $key2 => $value2) {
                $line_data['checkyesno'] = 1;
                $line_data['line_checkid']  = '';
                $pick_data['checkyesno'] = 1;
                $pick_data['pic_checkid']  = '';
                if ($value2 ==1) {
                    $select_allline = Db::field('a.tprice,a.tcarr_upprice,b.linepice,b.delivecost,l.lcarr_price,d.pcarr_upprice')
                                      ->table('ct_pickorder')
                                      ->alias('a')
                                      ->join('ct_order b','b.oid = a.orderid')
                                      ->join('ct_lineorder l','l.orderid=b.oid')
                                      ->join('ct_delorder d','d.orderid=b.oid')
                                      ->where('a.orderid',$key2)
                                      ->find();
                    $tprice = $select_allline['tcarr_upprice']=='' ? $select_allline['tprice'] : $select_allline['tcarr_upprice'];
                    $linepice = $select_allline['lcarr_price']=='' ? $select_allline['linepice'] : $select_allline['lcarr_price'];
                    $delivecost = $select_allline['pcarr_upprice']=='' ? $select_allline['delivecost'] : $select_allline['pcarr_upprice'];
                   $all_line_price +=  ($tprice+$linepice +$delivecost); 
                   $update_line = DB::table('ct_lineorder')->where('orderid',$key2)->update($line_data);
                   $update_pick = DB::table('ct_pickorder')->where('orderid',$key2)->update($pick_data);
                }
            }
        }
        if($shift_str!=''){  //定制
            $shift_arr = Db::table('ct_shift_order')
                    ->field('carr_upprice,price')
                    ->where('s_oid','IN',rtrim($shift_str,','))
                    ->select();
            foreach ($shift_arr as $key => $value) {
                    $get_price_shift += $value['carr_upprice'] =='' ? $value['price'] : $value['carr_upprice'];
            }          
        }
        if($city_str!=''){ //城配
            $city_arr = Db::table('ct_city_order')
                    ->field('carr_upprice,paymoney')
                    ->where('id','IN',rtrim($city_str,','))
                    ->select();
            foreach ($city_arr as $key => $value) {
                    $get_price_city += $value['carr_upprice'] =='' ? $value['paymoney'] : $value['carr_upprice'];
            }          
        }
        if($car_str!=''){ //整车
            $car_arr = Db::table('ct_userorder')
                    ->field('carr_upprice,price')
                    ->where('uoid','IN',rtrim($car_str,','))
                    ->select();
            foreach ($car_arr as $key => $value) {
                    $get_price_car += $value['carr_upprice'] =='' ? $value['price'] : $value['carr_upprice'];
            }          
        }
       $all_price = $all_line_price + $pick_line_price +$line_line_price+$get_price_shift+$get_price_city+$get_price_car;
      $invo_arr = DB::table('ct_invoice')->where('iid',$invo_id)->find(); 
      $subcost = (int)$invo_arr['totalprice'] - (int)$all_price;
      $invo_data['totalprice'] = $subcost;
        if ($invo_arr['self_total'] !='') {
            $invo_data['self_total'] = (int)$invo_arr['self_total'] - (int)$all_price;
        }
        if($subcost <= 0) {
            $del_data = Db::table('ct_invoice')->delete($invo_id);
        }else{
            $del_data = Db::table('ct_invoice')->where('iid',$invo_id)->update($invo_data);
        }
        $order_data['checkyesno'] = 1;
        $order_data['carr_checkid'] = ' ';
       
        if(!empty($arr2['shift'])){ //定制
            $data = DB::table('ct_shift_order')->where('s_oid','IN',$shift_str)->update($order_data);
        }
        if(!empty($arr2['city'])){ //城配
            $data = DB::table('ct_city_order')->where('id','IN',$city_str)->update($order_data);
        }
        if(!empty($arr2['car'])){ //整车
            $data = DB::table('ct_userorder')->where('uoid','IN',$car_str)->update($order_data);
        }
        if ($del_data) {
            print_r('ok');
        }else{
            print_r('fail');
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
      return view('carraccount/upcheck');
    }

    /*
    *开票提交动作
    */
    public function confirmcheck(){
        $post_data = Request::instance()->post();
        $data_arr['Invoiceno'] = $post_data['number'];
        $data_arr['instate'] = 2;
        $data_arr['confirm'] = 1;
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

    //承运商销账页面
    public function writeoff(){
        $line_arr = array(); //一条干线上的订单
        $pick_arr = array(); //只接提货的订单
        $all_arr = array(); //直接干线
        $arr_shift = array(); //定制
        $arr_city = array(); //城配
        $arr_car = array(); //城配
        $all_checkid = array(); //所有对账ID
        $check_mess = array(); //对账信息
        $com_arr = array(); //公司ID
        
      // search condition start 
        $search = Request::instance()->get();
        $pageParam    = ['query' =>[]];
        $where_search ='';
        $where_search2 ='';
        if (!empty($search['company'])) {
            $where_search['com.name|ordernumber|ucom.name|user.phone'] = ['like','%'.$search['company'].'%'];
            $where_search2['com.name|orderid|ucom.name|user.phone'] = ['like','%'.$search['company'].'%'];
            $pageParam['query']['company'] = $search['company'];
        }
        if (!empty($search['sermonth'])) {
            $sermonth = strtotime(trim($search['sermonth']));
            $where_data['in.sermonth'] = ['EQ',$sermonth];
            $pageParam['query']['sermonth'] = $search['sermonth'];
        }
        if (!empty($search['invonumber'])){
            $where_data['in.Invoiceno'] = ['like',$search['invonumber'].'%'];
            $pageParam['query']['invonumber'] = $search['invonumber'];
        }
        $where_data['instate'] = 2;
        // search condition end 
        //一条干线上的订单
        $where_line['b.orderstate'] = 7;
        $where_line['a.type'] = 2;
        //$where_line['a.checkyesno'] = 2;
        $result_line = Db::field('a.driverid,s.companyid,a.orderid,a.pic_checkid,a.tprice,a.tcarr_upprice,ct.start_id,ct.end_id,com.name dcompany,ucom.name,
                                b.ordernumber,b.linepice,b.delivecost,b.addtime,b.totalnumber,b.totalweight,b.totalvolume,b.userid,user.phone,
                                b.totalcost,in.sermonth,in.Invoiceno,in.unpass,in.Invoiceamount,in.paytime,in.unpass,l.lcarr_price,d.pcarr_upprice')
                ->table('ct_pickorder')
                ->alias('a')
                ->join('ct_order b','b.oid = a.orderid')
                ->join('ct_user user','user.uid = b.userid')
                ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                ->join('ct_lineorder l','l.orderid=b.oid')
                ->join('ct_delorder d','d.orderid=b.oid')
                ->join('ct_invoice in','in.iid = a.pic_checkid')
                ->join('ct_shift s','s.sid = b.shiftid')
                ->join('ct_already_city ct','ct.city_id = s.linecityid')
                ->join('ct_company com','com.cid = s.companyid')
                ->where($where_data)
                ->where($where_line)
                ->where($where_search)
                ->paginate(50,false,$pageParam);
         //统计订单个数
        $count_order_row = Db::table('ct_pickorder')
                    ->alias('a')
                    ->join('ct_order b','b.oid = a.orderid')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_invoice in','in.iid = a.pic_checkid')
                    ->join('ct_shift s','s.sid = b.shiftid')
                    ->join('ct_company com','com.cid = s.companyid')
                    ->where($where_data)
                    ->where($where_line)
                    ->where($where_search)
                    ->count('b.oid');
        $order_data = $result_line->toArray();      
        foreach ($order_data['data'] as $value2) {
            $com_arr[] = $value2['companyid'];
            $check_mess[$value2['pic_checkid']]['Invoiceno'] = $value2['Invoiceno'];
            $check_mess[$value2['pic_checkid']]['Invoiceamount'] = $value2['Invoiceamount'];
            $check_mess[$value2['pic_checkid']]['paytime'] = $value2['paytime'];
            $check_mess[$value2['pic_checkid']]['unpass'] = $value2['unpass'];
            $check_mess[$value2['pic_checkid']]['sermonth'] = $value2['sermonth'];
            $value2['invoID'] =  $value2['pic_checkid'];
            $value2['line'] =  $this->start_end_city($value2['start_id'],$value2['end_id']);
             $tprice = $value2['tcarr_upprice']=='' ? $value2['tprice'] : $value2['tcarr_upprice'];  //提货费
            $value2['tprice']=$tprice;
            $linepice = $value2['lcarr_price']=='' ? $value2['linepice'] : $value2['lcarr_price'];  //干线费
            $value2['linepice']=$linepice;
            $delivecost = $value2['pcarr_upprice']=='' ? $value2['delivecost'] : $value2['pcarr_upprice'];  //干线费
            $value2['delivecost']=$delivecost;
            $total = $tprice + $linepice+$delivecost; //总运费
            $value2['countcoat'] =  $total;
            $value2['orderstatus'] = 1;
            $value2['unpass'] = $value2['unpass']; //是否销账
            $value2['ostate'] = 1;
            $value2['doornum'] = 0;
            $value2['name'] = $value2['dcompany'];
            //下单人信息
            $value2['clinemess'] = $this->cline_mess($value2['userid']);
            $all_arr[] = $value2;
        }
       //定制订单
        $where_shift['orderstate']=3;
       // $where_shift['checkyesno'] = 2;
        $result_shift = Db::table('ct_shift_order')
                        ->alias('b')
                        ->join('ct_fixation_line f','b.shiftid=f.id')
                        ->join('ct_user user','user.uid = b.userid')
                        ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                        ->join('ct_company com','com.cid=f.carrierid')
                        ->join('ct_invoice in','in.iid=b.carr_checkid')
                        ->field('b.*,com.name dcompany,com.cid,f.lienid,in.sermonth,in.Invoiceamount,in.unpass,in.paytime,in.Invoiceno,user.phone,ucom.name')
                        ->where($where_data)
                        ->where($where_shift)
                        ->where($where_search)
                        ->paginate(50,false,$pageParam);
         $count_shift_row = Db::table('ct_shift_order')
                    ->alias('b')
                    ->join('ct_fixation_line f','b.shiftid=f.id')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_company com','com.cid=f.carrierid')
                    ->join('ct_invoice in','in.iid=b.carr_checkid')
                    ->where($where_data)
                    ->where($where_shift)
                    ->where($where_search)
                    ->count('b.s_oid');
        $shift_data = $result_shift->toArray();
        foreach ($shift_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $check_mess[$value['carr_checkid']]['Invoiceno'] = $value['Invoiceno'];
            $check_mess[$value['carr_checkid']]['Invoiceamount'] = $value['Invoiceamount'];
            $check_mess[$value['carr_checkid']]['unpass'] = $value['unpass'];
            $check_mess[$value['carr_checkid']]['paytime'] = $value['paytime'];
            $check_mess[$value['carr_checkid']]['sermonth'] = $value['sermonth'];
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
       
            $arr_shift[$key]['orderid'] = $value['s_oid']; //订单ID
            $arr_shift[$key]['line'] = $startcity .'--'.$endcity;  //线路
            $arr_shift[$key]['doornum'] = $value['doornum']; //门店数
            $arr_shift[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_shift[$key]['companyid'] = $value['cid']; //公司ID
            $arr_shift[$key]['name'] = $value['dcompany']; //公司名称
            $arr_shift[$key]['ordernumber'] = $value['ordernumber']; //订单编号
            $arr_shift[$key]['totalweight'] = 0; //重量
            $arr_shift[$key]['totalvolume'] = 0; //体积
            $arr_shift[$key]['tprice'] = 0; //提货费
            $arr_shift[$key]['linepice'] = 0; //干线费
            $arr_shift[$key]['delivecost'] = 0; //配送费
            $arr_shift[$key]['orderstatus'] = 4;
            $arr_shift[$key]['ostate'] = 2;
            $arr_shift[$key]['unpass'] = $value['unpass']; //是否销账
            $arr_shift[$key]['invoID'] = $value['carr_checkid'];
            $totalprice = $value['price'];
            $countprice_shift = $value['carr_upprice'] =='' ? $totalprice : $value['carr_upprice'];
            $arr_shift[$key]['countcoat'] = $countprice_shift;
            $arr_shift[$key]['ostate'] = 2;
            //下单人信息
            $arr_shift[$key]['clinemess'] = $this->cline_mess($value['userid']);
        }

        //城配订单
        $where_city['state']=3;
        //$where_city['checkyesno'] = 2;
        $result_city = Db::table('ct_city_order')
                        ->alias('b')
                        ->join('ct_rout_order r','r.rid=b.rout_id')
                        ->join('ct_user user','user.uid = b.userid')
                        ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                        ->join('ct_driver d','d.drivid=r.driverid')
                        ->join('ct_company com','com.cid=d.companyid')
                        ->join('ct_invoice in','in.iid=b.carr_checkid')
                        ->field('b.*,com.name dcompany,com.cid,in.sermonth,in.Invoiceamount,in.unpass,in.paytime,in.Invoiceno,user.phone,ucom.name')
                        ->where($where_data)
                        ->where($where_city)
                        ->where($where_search2)
                        ->paginate(50,false,$pageParam);
        $count_city_row = Db::table('ct_city_order')
                    ->alias('b')
                    ->join('ct_rout_order r','r.rid=b.rout_id')
                    ->join('ct_user user','user.uid = b.userid')
                    ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                    ->join('ct_invoice in','in.iid=b.carr_checkid')
                    ->join('ct_driver d','d.drivid=r.driverid')
                    ->join('ct_company com','com.cid=d.companyid')
                    ->where($where_data)
                    ->where($where_city)
                    ->where($where_search2)
                    ->count('b.id');
        $city_data = $result_city->toArray();               
        foreach ($city_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $check_mess[$value['carr_checkid']]['Invoiceno'] = $value['Invoiceno'];
            $check_mess[$value['carr_checkid']]['Invoiceamount'] = $value['Invoiceamount'];
            $check_mess[$value['carr_checkid']]['unpass'] = $value['unpass'];
            $check_mess[$value['carr_checkid']]['paytime'] = $value['paytime'];
            $check_mess[$value['carr_checkid']]['sermonth'] = $value['sermonth'];
            $arr_city[$key]['orderid'] = $value['id']; //订单ID
            $arr_city[$key]['line'] = '上海市';  //线路
            $arr_city[$key]['doornum'] = 0; //门店数
            $arr_city[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_city[$key]['companyid'] = $value['cid']; //公司ID
            $arr_city[$key]['name'] = $value['dcompany']; //公司名称
            $arr_city[$key]['ordernumber'] = $value['orderid']; //订单编号
            $arr_city[$key]['totalweight'] = 0; //重量
            $arr_city[$key]['totalvolume'] = 0; //体积
            $arr_city[$key]['tprice'] = 0; //提货费
            $arr_city[$key]['linepice'] = 0; //干线费
            $arr_city[$key]['delivecost'] = 0; //配送费
            $arr_city[$key]['orderstatus'] = 4;
            $arr_city[$key]['unpass'] = $value['unpass']; //是否销账
            $arr_city[$key]['invoID'] = $value['carr_checkid'];
            $countprice_city = $value['carr_upprice'] =='' ? $value['paymoney'] : $value['carr_upprice'];
            $arr_city[$key]['countcoat'] = $countprice_city;
            $arr_city[$key]['ostate'] = 3;
            //下单人信息
            $arr_city[$key]['clinemess'] = $this->cline_mess($value['userid']);
        }//市配

        //整车
        $where_car['orderstate'] = 3;
        //$where_car['checkyesno'] = 2;
        $result_car = Db::table('ct_userorder')
                        ->alias('b')
                        ->join('ct_user user','user.uid = b.userid')
                        ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                        ->join('ct_driver u','u.drivid=b.carriersid')
                        ->join('ct_company com','com.cid=u.companyid')
                        ->join('ct_invoice in','in.iid=b.carr_checkid')
                        ->field('b.*,com.name dcompany,com.cid,in.sermonth,in.Invoiceamount,in.unpass,in.paytime,in.Invoiceno,user.phone,ucom.name')
                        ->where($where_data)
                        ->where($where_car)
                        ->where($where_search)
                        ->paginate(50,false,$pageParam);
        $count_car_row = Db::table('ct_userorder')
                        ->alias('b')
                        ->join('ct_user user','user.uid = b.userid')
                        ->join('ct_company ucom','ucom.cid=user.lineclient','LEFT')
                        ->join('ct_invoice in','in.iid=b.carr_checkid')
                        ->join('ct_driver u','u.drivid=b.carriersid')
                        ->join('ct_company com','com.cid=u.companyid')
                        ->where($where_data)
                        ->where($where_car)
                        ->where($where_search)
                        ->count('uoid');
        $car_data = $result_car->toArray();
        foreach ($car_data['data'] as $key => $value) {
            $com_arr[] = $value['cid'];
            $check_mess[$value['carr_checkid']]['Invoiceno'] = $value['Invoiceno'];
            $check_mess[$value['carr_checkid']]['Invoiceamount'] = $value['Invoiceamount'];
            $check_mess[$value['carr_checkid']]['unpass'] = $value['unpass'];
            $check_mess[$value['carr_checkid']]['paytime'] = $value['paytime'];
            $check_mess[$value['carr_checkid']]['sermonth'] = $value['sermonth'];
            $arr_car[$key]['orderid'] = $value['uoid']; //订单ID
            $arr_car[$key]['line'] = $this->start_end_city($value['startcity'],$value['endcity']);  //线路
            $arr_car[$key]['doornum'] = 0; //门店数
            $arr_car[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_car[$key]['companyid'] = $value['cid']; //公司ID
            $arr_car[$key]['name'] = $value['dcompany']; //公司名称
            $arr_car[$key]['ordernumber'] = $value['ordernumber']; //订单编号
            $arr_car[$key]['totalweight'] = 0; //重量
            $arr_car[$key]['totalvolume'] = 0; //体积
            $arr_car[$key]['tprice'] = 0; //提货费
            $arr_car[$key]['linepice'] = 0; //干线费
            $arr_car[$key]['delivecost'] = 0; //配送费
            $arr_car[$key]['orderstatus'] = 4;
            $arr_car[$key]['unpass'] = $value['unpass']; //是否销账
            $arr_car[$key]['invoID'] = $value['carr_checkid'];
            $countprice_car = $value['carr_upprice'] =='' ? $value['price'] : $value['carr_upprice'];
            $arr_car[$key]['countcoat'] = $countprice_car;
            $arr_car[$key]['ostate'] = 4;
            //下单人信息
            $arr_car[$key]['clinemess'] = $this->cline_mess($value['userid']);
        }//整车
        //订单个数
        $count_row = array('order'=>intval($count_order_row),'shift'=>intval($count_shift_row),'city'=>intval($count_city_row),'car'=>intval($count_car_row));
        $pos = array_search(max($count_row), $count_row);
        switch ($pos) {
            case 'order':
                $page = $result_line->render();
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
        $countun = array_unique($all_checkid);
        if (!empty($countun)) {
            if (count($countun) > 1) {
              $this->assign('countarr','listtwo');
            }else{
              $countlist = DB::table('ct_invoice')->where('iid',$countun[0])->find();
              $this->assign('listcount',$countlist['sermonth']);
            }
        }
      //合并三种可能数组
        $allarr = array_merge($all_arr,$pick_arr,$line_arr,$arr_shift,$arr_city,$arr_car);

        if (!empty($allarr)) {
            $allarr = $this->my_sort($allarr,'addtime',SORT_DESC);
        }

        $dat = array();
        $str_inv='';
        if (count($check_mess) =='1') {
            foreach ($check_mess as $b) {
                $dat = $b;
            }
        }else{
           foreach ($check_mess as $key => $value) {
              $str_inv .= $value['Invoiceno'] .' / ';
           }
        }

        if (count($check_mess) > 1) {
            $this->assign('countarr','listtwo');
            $this->assign('listcount',rtrim($str_inv,' / '));
            $this->assign('listnum',count($check_mess));
        }else{
            $this->assign('countarr','listone');
            $this->assign('listcount',$dat);
        }
        $comnum = 0;
        $ordercount=0;
        $comnum = array_unique($com_arr); //公司去重
        $ordercount = count($allarr);  //统计订单个数
        $this->assign('comnum',count($comnum));
        $this->assign('ordercount',$ordercount);
        $this->assign('list',$allarr);
        $this->assign('page',$page);
        return view('carraccount/writeoff');
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
        $count_order_line = Db::table('ct_order')->alias('o')->join('ct_lineorder l','l.orderid=o.oid')->where('line_checkid',$id)->sum('linepice');
        //零担提货总额
        $count_order_pick = Db::table('ct_order')->alias('o')->join('ct_pickorder p','p.orderid=o.oid')->where('pic_checkid',$id)->sum('pickcost');
        //零担配送总额
        $count_order_send = Db::table('ct_order')->alias('o')->join('ct_lineorder l','l.orderid=o.oid')->where('line_checkid',$id)->sum('delivecost');
        //定制总额
        $count_shift = Db::table('ct_shift_order')->where('carr_checkid',$id)->sum('price');
        //城配总额
        $count_city = Db::table('ct_city_order')->where('carr_checkid',$id)->sum('paymoney');
        //整车总额
        $count_car = Db::table('ct_userorder')->where('carr_checkid',$id)->select();
        $total_car = 0;
        if (!empty($count_car)) {
            foreach ($count_car as $key => $value) {
              $total_car+= $value['price'];
            }
        }
        $total = $count_order_line+$count_order_pick+$count_order_send+$count_shift+$count_city+$total_car;
        $company = Db::table('ct_invoice')
                    ->alias('a')
                    ->join('ct_company c','c.cid=a.companyid')
                    ->field('c.money,a.companyid,a.sermonth,c.name')
                    ->where('iid',$id)
                    ->find();
        
        if ($company['money'] !='0') {
            Db::table('ct_company')->where('cid',$company['companyid'])->update(array('money'=>$company['money']-$total));
        }
        $content = $company['name'].' '.date('Y-m',$company['sermonth'])."销账成功，承运商账号消除余额为:".$total;
        $this->hanldlog($this->uid,$content);
        $up = DB::table('ct_invoice')->where('iid',$id)->update($data);
        //标记账单销账完成
        $check_data['checkyesno'] = 3;
        Db::table('ct_pickorder')->where('pic_checkid',$id)->update($check_data);
        Db::table('ct_shift_order')->where('carr_checkid',$id)->update($check_data);
        Db::table('ct_city_order')->where('carr_checkid',$id)->update($check_data);
        Db::table('ct_userorder')->where('carr_checkid',$id)->update($check_data);
        if($up){
          print_r('ok');
        }else{
          print_r('fail');
        }
      }
    }

    
}
