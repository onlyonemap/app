<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
//use phpexcel\PHPExcel;
header("Content-type:text/html;charset=utf-8");
class Execl  extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->if_login();
//
//    }

   public function index(){
   
    /*$xlsCell  = array(
        array('ID','ID'),
        array('number','批次'),
        array('OrderNumber','订单编号'),
        array('CompanyName','公司名称')
      
    );
    $data = array(
      array('ID'=>'1','number'=>'小明','OrderNumber'=>'A','CompanyName'=>'D'),
      array('ID'=>'2','number'=>'小红','OrderNumber'=>'A','CompanyName'=>'D'),
      array('ID'=>'3','number'=>'小王','OrderNumber'=>'A','CompanyName'=>'D'),
      array('ID'=>'4','number'=>'小张','OrderNumber'=>'A','CompanyName'=>'d'),
      array('ID'=>'5','number'=>'小李','OrderNumber'=>'A','CompanyName'=>'a'),
      array('ID'=>'6','number'=>'老五','OrderNumber'=>'A','CompanyName'=>'b'),
      array('ID'=>'7','number'=>'小七','OrderNumber'=>'A','CompanyName'=>'b'),
      array('ID'=>'8','number'=>'小八','OrderNumber'=>'A','CompanyName'=>'b')
      );
    $this->writertwo('test',$xlsCell,$data);//导出 此导出表头最长为A-Z,如果需要更长，请自行更改*/
    if (request()->file('file') !='') {
        $path = $this->file_upload('file','xls,xlsx','execl');
        $path = $path['file_path'];
         $arr = array();
        $list = $this->reader('../public'.$path);//导入
        foreach ($list as $key => $value) {
            $data['name'] = $value['B'];
            $data['number'] = $value['C'];
            $data['cname'] = $value['D'];
            $data_test1['cscros'] = $value['E'];
            $data_test1['sscros'] = $value['F'];
            $data_test1['ascros'] = $value['G'];
            $data_test1['bscros'] = $value['H'];
            $ins_id = DB::table('ct_test')->insertGetId($data);
            $data_test1['testid'] = $ins_id;
            $ins_id2 = DB::table('ct_test2')->insertGetId($data_test1);
        }
        //echo "<pre/>";
        //print_r($list);
    }
   
   
  }

  public function execl(){
    return view('execl/execl');
  }
/*
*仓库
*
*/
  public function impwarehouse(){
        if (request()->file('file') !='') {
            $path = $this->file_upload('file','xls,xlsx','execl');
            $path = $path['file_path'];
             $arr = array();
            $list = $this->reader('../public'.$path);//导入
            $i=0;
           foreach ($list as $key => $value) {
               /* $city_condition['name'] = ['like',trim($value['B']).'%']; 
                $city_condition['level'] = 2; 
                $city = DB::table('ct_district')->field('id,name')->where($city_condition)->find();
                $area_condition['name'] = ['like','%'.trim($value['C']).'%']; 
               $area_condition['parent_id'] = $city['id']; 
                $area = Db::table('ct_district')->field('id,name')->where($area_condition)->find();*/
                
           
            
                $data['cityid'] = $value['B'];
                $data['areaid'] = $value['C'];
                $data['housename'] = $value['D'];
                $data['address'] = $value['E'];
                $data['areanumber'] = $value['F'];
                $data['price'] = $value['G'];
                $data['com_name'] = $value['H'];
                $data['cantact'] = $value['I'];
                 $data['telephone'] = $value['J'];
                
           
    
                $ins_id2 = DB::table('ct_warehouse')->insertGetId($data);
            }
            //echo "<pre/>";
            //print_r($list);
        }
  }

  public function impdriver(){
    return view('execl/impdriver');
  }
/*
*个体司机导入
*
*/
  public function driver(){
     if (request()->file('file') !='') {
        $path = $this->file_upload('file','xls,xlsx','execl');
        $path = $path['file_path'];
         $arr = array();
        $list = $this->reader('../public'.$path);//导入
       foreach ($list as $key => $value) {
            $data['mobile'] = $value['C'];
            $data['realname'] = $value['B'];
            $data['addtime'] = time();
            $data['type'] = 3;
            $data['password'] = md5('666666ct888');
            $data['identity'] = $value['I'];
            $data['carstatus'] = 2;
            $data['carid'] = $value['D'];
            $inser = DB::table('ct_driver')->insertGetId($data);
            $car['carnumber'] = $value['E'];
            $car['driverid'] = $inser;
            $car['carid'] = $value['D'];
            $car['car_age'] = $value['G'];
            $car['addtime'] = time();
            $car['status'] = 2;
             $inser = DB::table('ct_carcategory')->insertGetId($car);
        }
        // echo "<pre/>";
           // print_r($list);
    }
  }

  public function expdriver(){
      return view('execl/expdriver');
  }
  /*
  *
  *公司司机导入
  */
  public function comdriver(){
    if (request()->file('file') !='') {
        $path = $this->file_upload('file','xls,xlsx','execl');
        $path = $path['file_path'];
         $arr = array();
        $list = $this->reader('../public'.$path);//导入
       foreach ($list as $key => $value) {
           
            $car['carnumber'] = $value['B'];
            
            $car['carid'] = $value['C'];
            $getyear = strtotime($value['D']);
            $car['car_age'] = date('Y',time())-date('Y',$getyear);
            $car['com_id'] = 15;
            $car['addtime'] = time();
            $car['status'] = 2;
           
            //print_r($car);
             $inser = DB::table('ct_carcategory')->insertGetId($car);
        }
        if ($inser) {
          $this->success('导入成功', 'execl/expdriver');
        }
        // echo "<pre/>";
           // print_r($list);
    }
  }
  /*
  *
  *对账承运商导出列表
  */
    public function carrexecl(){
        $get_data = Request::instance()->get();

       // print_r($get_data);exit();
        $orderarr = explode(',', $get_data['orderID']);
        $ostate = explode(',', $get_data['ostate']);
        $arr2 = array('order'=>'');
        $shift_str = ''; //定制
        $order_str = '';  // 零担
        $city_str = '';  // 城配
        $car_str = '';  // 整车
        $arr = array();
        $allline = array();
        $pick_line = array();
        $line_line = array(); 
        $carriarr = array(); //零担
        $userarr_shift = array();  //定制
        $userarr_city = array();  //市配
        $userarr_car = array();  //整车
        $all_line_price=0;
        $pick_line_price=0;
        $line_line_price=0;
        $i=0;
        foreach ($orderarr as $key => $value) {
            $arr[$i]['b'] = $orderarr[$key];
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
   /* foreach ($orderarr as $key => $value) {
        $arr[$value] = $statearr[$key];
    }*/
    if ($order_str !='') {     
       $select_allline = Db::field('b.*,a.tprice,tcarr_price,l.lcarr_price,d.pcarr_price')
                        ->table('ct_pickorder')
                        ->alias('a')
                        ->join('ct_order b','b.oid = a.orderid')
                        ->join('ct_lineorder l','b.oid = l.orderid')
                        ->join('ct_delorder d','b.oid = d.orderid')
                        ->where('a.orderid',$key2)
                        ->where('oid','IN',rtrim($order_str,','))
                        ->select();
        foreach ($select_allline as  $val) {
        $tprice = $val['tcarr_price']=='' ? $val['tprice'] : $val['tcarr_price'];
        $linepice = $val['lcarr_price']=='' ? $val['linepice'] : $val['lcarr_price'];
        $delivecost = $val['pcarr_price']=='' ? $val['delivecost'] : $val['pcarr_price'];
        $all_line_price =  ($tprice+$linepice+$delivecost);
        $shift = DB::field('c.start_id,c.end_id,d.name')
                    ->table('ct_shift_log')
                    ->alias('a')
                    ->join('ct_shift b','b.sid = a.shiftid')
                    ->join('ct_already_city c','c.city_id = b.linecityid')
                    ->join('ct_company d','d.cid = b.companyid')
                    ->find();
            $carriarr[] = array(
                    'OrderNumber' => $val['ordernumber'],
                    'CompanyName' =>$shift['name'],
                    'AddTime' => date('Y-m-d H:i',$val['addtime']),
                    'Line' => $this->start_end_city($shift['start_id'],$shift['end_id']),
                    'ti_pay'=>$val['tprice'],
                    'Doornum'=>'0',
                    'Ostate'=>'零担',
                    'TrunkCost' => $val["linepice"],
                    'pei_pay' => $val["delivecost"],
                    'total' => $all_line_price
                    
                );
          
        } //end ct_order foreach
    }

    if ($shift_str !='') {
        $result_shift = Db::table('ct_shift_order')
                        ->alias('b')
                        ->join('ct_fixation_line f','b.shiftid=f.id')
                        ->join('ct_company com','com.cid=f.carrierid')
                        ->field('b.*,com.name,com.cid,f.carr_price,f.lienid')
                        ->where('s_oid','IN',rtrim($shift_str,','))
                        ->select();
        foreach ($result_shift as $key => $value) {
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
            $totalprice = $value['price'];
            $countprice_shift = $value['carr_upprice'] =='' ? $totalprice : $value['carr_upprice'];
            $userarr_shift[] = array(
                    'OrderNumber' => $value['ordernumber'], //订单编号
                    'CompanyName' =>$value['name'], //公司名称
                    'AddTime' => date('Y-m-d H:i',$value['addtime']), //下单时间
                    'Line' => $startcity .'--'.$endcity, //线路
                    'Doornum'=>$value['doornum'],
                    'Ostate'=>'定制',
                    'ti_pay'=>'0',
                    'TrunkCost' => '0',
                    'pei_pay' => '0',
                    'total' => $countprice_shift
                );
        }
    } // end if 定制
    if ($city_str !='') {

        $result_city = Db::table('ct_city_order')
                        ->alias('b')
                        ->join('ct_rout_order r','r.rid=b.rout_id')
                        ->join('ct_driver d','d.drivid=r.driverid')
                        ->join('ct_company com','com.cid=d.companyid')
                        ->field('b.*,com.name,com.cid')
                        ->where('b.id','IN',rtrim($city_str,','))
                        ->select();
        foreach ($result_city as $key => $value) {
            $countprice_city = $value['carr_upprice'] =='' ? $value['paymoney'] : $value['carr_upprice'];
            $userarr_city[] = array(
                    'OrderNumber' => $value['orderid'], //订单编号
                    'CompanyName' =>$value['name'], //公司名称
                    'AddTime' => date('Y-m-d H:i',$value['addtime']), //下单时间
                    'Line' => '上海市', //线路
                    'Doornum'=>'0',
                    'Ostate'=>'城配',
                    'ti_pay'=>'0',
                    'TrunkCost' => '0',
                    'pei_pay' => '0',
                    'total' => $countprice_city
                );
        }//市配
    }
    if ($car_str !='') {
        $result_car = Db::table('ct_userorder')
                    ->alias('b')
                    ->join('ct_driver u','u.drivid=b.carriersid')
                    ->join('ct_company com','com.cid=u.companyid')
                    ->field('b.*,com.name,com.cid')
                    ->where('uoid','IN',rtrim($car_str,','))
                    ->select();
        foreach ($result_car as $key => $value) {
            
            $countprice_car = $value['carr_upprice'] =='' ? $value['price'] : $value['carr_upprice'];
            
            $userarr_car[] = array(
                    'OrderNumber' => $value['ordernumber'], //订单编号
                    'CompanyName' =>$value['name'], //公司名称
                    'AddTime' => date('Y-m-d H:i',$value['addtime']), //下单时间
                    'Line' => $this->start_end_city($value['startcity'],$value['endcity']), //线路
                    'Doornum'=>'0',
                    'Ostate'=>'整车',
                    'ti_pay'=>'0',
                    'TrunkCost' => '0',
                    'pei_pay' => '0',
                    'total' => $countprice_car
                );
        }//整车
    }

    
    $list = array_merge($carriarr,$userarr_shift,$userarr_city,$userarr_car);
    $xlsCell  = array(
        array('OrderNumber','订单编号'),
        array('CompanyName','公司名称'),
        array('AddTime','下单时间'),
        array('Line','线路'),
        array('Doornum','门店数'),
        array('Ostate','订单类型'),
        array('ti_pay','提货费(元)'),
        array('TrunkCost','干线费(元)'),
        array('pei_pay','配送费(元)'),
        array('total','金额(元)')
    );
    $this->writertwo('物流公司',$xlsCell,$list);
  }

  /*
  *
  *对账用户导出列表
  */
  public function userexecl(){
    // 1零担，2定制 
        $get_data = Request::instance()->get();
        $orderid_arr = explode(',', $get_data['orderID']);
        $state_arr = explode(',', $get_data['ostate']);
        $i=0;
       foreach ($orderid_arr as $key => $value) {
            $array[$i]['b']= $orderid_arr[$key];
            $array[$i]['a']= $state_arr[$key];
           $i++;
        }

        $order_str = '';
        $shift_str = '';
        $city_str = '';
        $car_str = '';
        foreach ($array as $key => $info) {
          if ($info['a'] == '2') {
            $shift_str .= $info['b'].',';
          }
          if ($info['a'] == '1') {
            $order_str .= $info['b'].',';
          }
        if ($info['a'] == '3') {
            $city_str .= $info['b'].',';
          }
          if ($info['a'] == '4') {
            $car_str .= $info['b'].',';
          }
            //$result[$info['a']][] = $info['b'];
        }
        $i =1;
        // 下单总额
        $countcoat = 0;
        $userarr_shift = array();  //定制
        $userarr_order = array();  //零担
        $userarr_city = array();  //市配
        $userarr_car = array();  //整车
        if ($shift_str !='') {
            $result = Db::table('ct_shift_order')
                  ->alias('s')
                  ->join('ct_fixation_line f','f.id = s.shiftid')
                  ->join('ct_user u','u.uid=s.userid')
                  ->join('ct_company c','c.cid=u.lineclient')
                  ->field('s.*,f.lienid,u.lineclient,c.name,u.realname')
                  ->where(array('s_oid'=>['IN', rtrim($shift_str,',')]))
                  ->select();
          foreach ($result as $key => $value) {
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
            $count_all = $value['upprice']=='' ? $value['totalprice'] : $value['upprice'];
              $userarr_shift[] = array(
                  'ID' => $i,
                  'OrderNumber' =>$value['ordernumber'],
                  'CompanyName' =>$value['name'],
                  'AddTime' => date('Y-m-d',$value['addtime']),
                  'Line' => $startcity .'--'.$endcity,
                  'doornum' => $value['doornum'],
                  'ostate'=>'定制',
                  'get_weight' => 0,
                  'get_volume' => 0,
                  'ti_pay' => 0,
                  'TrunkCost' => 0,
                  'pei_pay' => 0,
                  'count_all'=>$count_all
              ); 
            $i++;
          } //end ct_shift_order froeach
        }//end  ct_shift_order if
        
          $a = $i;
          if ($order_str !='') {
              //零担
              $result = DB::field('a.*,pic.tprice,user.realname,user.lineclient,user.userstate,d.start_id,d.end_id')
                  ->table('ct_order')
                  ->alias('a')
                  ->join('ct_user user','user.uid = a.userid')
                  ->join('ct_pickorder pic','pic.orderid = a.oid')
                  ->join('ct_shift c','c.sid=a.shiftid')
                  ->join('ct_already_city d','d.city_id = c.linecityid')
                  ->where(array('a.oid'=>['IN',rtrim($order_str,',')]))
                  ->select();
              foreach ($result as $key => $value) {
                  $comname = DB::field('name')->table('ct_company')->where('cid',$value['lineclient'])->find();
                  $CompanyName = $comname['name'];
                  $countcoat = ($value['tprice']+$value['linepice']+$value['delivecost']);
                  $count_all = $value['totalcost']=='' ? $countcoat : $value['totalcost'];
                  $userarr_order[] = array(
                          'ID' => $a,
                          'OrderNumber' =>$value['ordernumber'],
                          'CompanyName' =>$CompanyName,
                          'AddTime' => date('Y-m-d',$value['addtime']),
                          'Line' => $this->start_end_city($value['start_id'],$value['end_id']),
                          'doornum' => 0,
                          'get_weight' => $value['totalweight'],
                          'get_volume' => $value['totalvolume'],
                          'ti_pay' => $value['tprice'],
                          'ostate'=>'零担',
                          'TrunkCost' => $value['linepice'],
                          'pei_pay' => $value['delivecost'],
                          'count_all'=>$count_all
                      ); 
                  $a++;
              }// end ct_order foreach
          }// end ct_order if

          $b=$a;
          if ($city_str !='') {
              $result_city = Db::table('ct_city_order')
                  ->alias('a')
                  ->join('ct_user u','u.uid=a.userid')
                  ->join('ct_company c','c.cid=u.lineclient')
                  ->field('a.*,u.lineclient,c.name,u.realname,c.cid')
                  ->where(array('a.id'=>['IN',rtrim($city_str,',')]))
                  ->select();
                foreach ($result_city as $key => $value) {
                    $countprice_city = $value['upprice'] =='' ? $value['actualprice'] : $value['upprice'];
                    $userarr_city[] = array(
                          'ID' => $b,
                          'OrderNumber' => $value['orderid'], //订单ID
                          'CompanyName' =>$value['name'], //公司ID
                          'AddTime' => date('Y-m-d',$value['addtime']), //下单时间
                          'Line' => '上海市', //线路
                          'doornum' => 0,
                          'get_weight' => 0,
                          'get_volume' => 0,
                          'ti_pay' => 0,
                          'ostate'=>'城配',
                          'TrunkCost' => 0,
                          'pei_pay' => 0,
                          'count_all'=>$countprice_city
                      ); 
                    $b++;
                }//市配
          }//end if ct_city_order

          $c=$b;
          if ($car_str !='') {
              $result_car = Db::table('ct_userorder')
                    ->alias('a')
                    ->join('ct_user u','u.uid=a.userid')
                    ->join('ct_company c','c.cid=u.lineclient')
                    ->field('a.*,u.realname,u.lineclient,c.name,c.cid')
                    ->where(array('a.uoid'=>['IN',rtrim($car_str,',')]))
                    ->select(); 
                foreach ($result_car as $key => $value) {
                    $countprice_car = $value['upprice'] =='' ? $value['referprice'] : $value['upprice'];
                    $userarr_car[] = array(
                          'ID' => $c,
                          'OrderNumber' => $value['ordernumber'], //订单ID
                          'CompanyName' =>$value['name'], //公司名称
                          'AddTime' => date('Y-m-d',$value['addtime']), //下单时间
                          'Line' => $this->start_end_city($value['startcity'],$value['endcity']),  //线路
                          'doornum' => 0,
                          'get_weight' => 0,
                          'get_volume' => 0,
                          'ti_pay' => 0,
                          'ostate'=>'整车',
                          'TrunkCost' => 0,
                          'pei_pay' => 0,
                          'count_all'=>$countprice_car
                      ); 
                    $c++;
                }//整车
          }//end if ct_city_order
          
          $list = array_merge($userarr_shift,$userarr_order,$userarr_city,$userarr_car);
          $xlsCell  = array(
              array('ID','ID'),
              array('OrderNumber','订单编号'),
              array('CompanyName','公司名称'),
              array('AddTime','下单时间'),
              array('Line','线路'),
              array('ostate','订单类型'),
              array('doornum','门店数'),
              array('get_weight','重量(kg)'),
              array('get_volume','立方(m³)'),
              array('ti_pay','提货费(元)'),
              array('TrunkCost','干线费(元)'),
              array('pei_pay','配送费(元)'),
              array('count_all','运费(元)')
          );
        
        $this->writertwo('客户',$xlsCell,$list);
        //echo $get_data;
        //$orderarr = explode(',', $get_data['orderID']);

  }
  /*
  *
  *注册用户导出
  */

  public function registerexecl(){
    $user_data = Request::instance()->get();
    if ($user_data['userid'] == '') {
        $where = '';
    }else{
        $where = array('uid'=>['IN',$user_data['userid']]);
    }
    $stime = $user_data['starttime'];
    $etime = $user_data['endtime'];
    if (!empty($stime) && !empty($etime)) {
        $endtime = strtotime(trim($etime).'23:59:59');
        $starttime = strtotime(trim($stime).'00:00:00');
        $where['addtime'] = array(array('gt',$starttime),array('lt', $endtime));
       
    }
    $result = Db::table('ct_user')->where($where)->select();
    foreach ($result as $key => $value) {
        if($value['userstate'] == 1){
            $client_state = '小微客户';
        }elseif($value['userstate'] == 2){
            $client_state = '项目客户';
        }elseif($value['userstate'] ==3){
            $client_state = '线下客户';
        }
        if ($value['userstate'] == 1) {
            if ($value['auth_status'] == 1) {
                $client_status = '未认证';
            }elseif($value['auth_status'] == 2){
                $client_status = '认证成功';
            }elseif($value['auth_status'] ==3){
                $client_status = '认证失败';
            }
        }else{
            $client_status = '';
        }
        
        //统计零担下单总数
       $bulk_count = Db::table('ct_order')->where('userid',$value['uid'])->count('oid');
       //统计市配下单总数
       $city_count = Db::table('ct_city_order')->where('userid',$value['uid'])->count('id');
       //统计整车下单总数
       $carload_count = Db::table('ct_userorder')->where('userid',$value['uid'])->count('uoid');
       $user_arr[] = array(
            'ID' => $value['uid'],
            'Phone' =>$value['phone'],
            'Realname' =>$value['realname'],
            'AddTime' => date('Y-m-d H:i',$value['addtime']),
            'Userstate' => $client_state,
            'Auth_status' => $client_status,
            'Bulk_count' => $bulk_count,
            'City_count' => $city_count,
            'Carload_count' => $carload_count
            
        ); 
    }
     $xlsCell  = array(
            array('ID','ID'),
            array('Phone','联系方式'),
            array('Realname','真实姓名'),
            array('AddTime','下单时间'),
            array('Userstate','客户属性'),
            array('Auth_status','是否认证'),
            array('Bulk_count','零担下单总数'),
            array('City_count','市配下单总数'),
            array('Carload_count','整车下单总数')
        );
        $this->writertwo('客户',$xlsCell,$user_arr);

  }
    /**
     * 市内配送订单导出
     */
    // public function getclient(){
    //     $dataid = Request::instance()->get();
    //     if (empty($dataid)) {
    //       $where = '';
    //     }else{
    //       $where = array('id'=>['IN',$dataid['id']]);
    //     }
    //     $where['paystate'] = ['IN','2,3'];
    //     $client_arr = array();
    //     $result = DB::table('ct_city_order')
    //                 ->alias('a')
    //                 ->join('ct_user u','u.uid = a.userid')
    //                 ->field('a.*,u.phone,u.realname,u.username,lineclient')
    //                 ->where($where)
    //                 ->select();
    //     foreach ($result as $key => $value) {
    //        //提货地址地址
    //         $saddress_str = json_decode($value['saddress'],TRUE);
    //         $saddress = '';
    //         if ($value['ordertype'] =='1') {
    //            foreach ($saddress_str as $key => $val) {
    //             $saddress .=$val['address'].'/';
    //           }
    //         }
    //         $CompanyName = '';
    //         if ($value['lineclient'] !='') {
    //           $CompanyName = $this->getCompanyname($value['lineclient']);
    //         }
    //         //配送地址
    //         $eaddress_str = json_decode($value['eaddress'],true);
    //         $eaddress = '';
    //         if ($value['ordertype'] =='1') {
    //           foreach ($eaddress_str as $key => $vals) {
    //             $eaddress .= $vals['address'].'/';
    //           }
    //         }
    //       $addtime = date('Y-m-d',$value['addtime']);
    //       $client_arr[] = array(
    //               'Ordernumber' => $value['orderid'],
    //               'Ordertime' => date("Y-m-d",$value['addtime']),
    //               'CompanyName' => $CompanyName,
    //               'Shipperid' => $value['realname']=='' ? $value['username']:$value['realname'],
    //               'Pickphone' => $value['phone'],
    //               'Paymoney' => $value['paymoney'],
    //               'Ordertype'=> $value['ordertype'] ='1'?'用车':'包车',
    //               'ColdType' => $value['cold_type'],
    //               'AddTime' =>  $addtime,
    //               'SddTime' => $value['data_type'],
                  
    //               'Saddress' => trim($saddress,'/'),
                  
    //               'Eaddress' => trim($eaddress,'/'),
    //         );
    //     }
    //     $xlsCell  = array(
    //         array('Ordernumber','订单编号'),
    //         array('Ordertime','下单时间'),
    //         array('CompanyName','客户公司'),
    //         array('Shipperid','下单客户'),
    //         array('Pickphone','联系方式'),
    //         array('Ordertype','订单类型'),
    //         array('Paymoney','付款金额'),
    //         array('ColdType','冷藏类型'),
    //         array('AddTime','下单时间'),
    //         array('SddTime','发货日期'),
          
    //         array('Saddress','发货地址'),
    //         //array('Contactid','收货人'),
    //         //array('Sendphone','收货人号码'),
    //         array('Eaddress','收货地址')
    //     );
    //     $this->writertwo('用户',$xlsCell,$client_arr);
    // }
    /**
     * 市内配送订单导出
     */
    public function getclient(){
        $dataid = Request::instance()->get();
        if (empty($dataid)) {
          $where = '';
        }else{
          $where = array('id'=>['IN',$dataid['id']]);
        }
        $where['paystate'] = ['IN','2,3'];
        $client_arr = array();
        $result = DB::table('ct_city_order')
                    ->alias('a')
                    ->join('ct_user u','u.uid = a.userid')
                    ->field('a.*,u.phone,u.realname,u.username,lineclient')
                    ->where($where)
                    ->select();
        foreach ($result as $key => $value) {
            //提货地址地址
            $saddress_str = json_decode($value['saddress'],TRUE);
            $saddress = '';
            if ($value['ordertype'] =='1') {
               foreach ($saddress_str as $key => $val) {
                $saddress .=$val['address'].'/';
              }
            }
            $CompanyName = '';
            if ($value['lineclient'] !='') {
              $CompanyName = $this->getCompanyname($value['lineclient']);
            }
            //配送地址
            $eaddress_str = json_decode($value['eaddress'],true);
            $eaddress = '';
            if ($value['ordertype'] =='1') {
              foreach ($eaddress_str as $key => $vals) {
                $eaddress .= $vals['address'].'/';
              }
            }
            $addtime = date('Y-m-d',$value['addtime']);
            // 支付类型
            
            $paystatr = '';
            $pay = 0;
            if ($value['paystate'] =='2') {
                if ($value['lineclient'] == '' || $value['pay_type'] !='1' ) {
                    $paystatr = '已支付';
                    $pay = $value['upprice']=='' ? $value['actualprice'] : $value['upprice'];
                }else{
                    $paystatr = '信用支付';
                    
                    if ($value['usercheck'] =='2') {
                        $pay_state = Db::table('ct_invoice')->where('iid',$value['user_checkid'])->find();
                        if ($pay_state['instate'] == '2') {
                            $paystatr = '已支付';
                            $pay = $value['upprice']=='' ? $value['actualprice'] : $value['upprice'];
                        }
                    }
                }
            }else{
                $paystatr ='未支付';
            }

            $client_arr[] = array(
                  'Ordernumber' => $value['orderid'],
                  'Ordertime' => date("Y-m-d",$value['addtime']),
                  'CompanyName' => $CompanyName,
                  'Shipperid' => $value['realname']=='' ? $value['username']:$value['realname'],
                  'Pickphone' => $value['phone'],
                  'Paymoney' => $value['upprice'] == '' ? $value['actualprice'] : $value['upprice'],
                  'actualprice' => $pay,
                  'Ordertype'=> $value['ordertype'] ='1'?'用车':'包车',
                  'ColdType' => $value['cold_type'],
                  'AddTime' =>  $addtime,
                  'SddTime' => $value['data_type'],
                  
                  'Saddress' => trim($saddress,'/'),
                  
                  'Eaddress' => trim($eaddress,'/'),
                  'Paystatr' => $paystatr,
            );
        }
        $xlsCell  = array(
            array('Ordernumber','订单编号'),
            array('Ordertime','下单时间'),
            array('CompanyName','客户公司'),
            array('Shipperid','下单客户'),
            array('Pickphone','联系方式'),
            array('Ordertype','订单类型'),
            array('Paymoney','应收金额'),
            array('actualprice','实收金额'),
            array('ColdType','冷藏类型'),
            array('AddTime','下单时间'),
            array('SddTime','发货日期'),
          
            array('Saddress','发货地址'),
            //array('Contactid','收货人'),
            //array('Sendphone','收货人号码'),
            array('Eaddress','收货地址'),
            array('Paystatr','支付类型')
        );
        $this->writertwo('用户',$xlsCell,$client_arr);
    }

  /*
  *用户未支付列表
  * ordertype:1 市内配送 2,整车订单 3、零担
  */
  public function unpay(){
    $data = Request::instance()->get();
    $type = $data['ordertype'];
    $where['u.userstate'] = 1;  //注册用户
    if ( $type == '1') {
        $title = '市内配送未支付用户';
        $where['paystate'] = 1;
        
        $result = Db::table('ct_city_order')
                    ->alias('o')
                    ->join('ct_user u','u.uid = o.userid')
                    ->field('o.userid,o.orderid,o.ordertype,o.saddress,o.eaddress,u.realname,u.phone,u.addtime')
                    ->where($where)
                    ->group('userid')
                    ->select();
        $userarr = array();
        $i=1;
        foreach ($result as $key => $value) {
          //提货地址地址
          $saddress_str = json_decode($value['saddress'],TRUE);
          $saddress = '';
          if ($value['ordertype'] =='1') {
             foreach ($saddress_str as $key => $val) {
              $saddress .=$val['address'].'/';
            } //end foreach
          } //end if
          
          //配送地址
          $eaddress_str = json_decode($value['eaddress'],true);
          $eaddress = '';
          if ($value['ordertype'] =='1') {
            foreach ($eaddress_str as $key => $vals) {
              $eaddress .= $vals['address'].'/';
            }//end foreach
          }//end if
          $userarr[] = array(
              'Number'=>$i,
              'Realname' => $value['realname'],
              'Phone' => $value['phone'],
              'AddTime' => date('Y-m-d',$value['addtime']),
              'Pickaddress' => trim($saddress,'/'),
              'Sendaddress' => trim($eaddress,'/')
            );
          $i++;
        }//end foreach
        
    }elseif($type == '2'){
       $title = '整车未支付用户';
        $where['paystate'] = 1;
        $result = Db::table('ct_userorder')
                    ->alias('o')
                    ->join('ct_user u','o.userid=u.uid')
                    ->field('o.userid,o.pickaddress,o.sendaddress,u.realname,u.phone,u.addtime')
                    ->where($where)
                    ->group('userid')
                    ->select();
          $userarr = array();
          $i=1;
          foreach ($result as $key => $value) {
            $pick = json_decode($value['pickaddress'],TRUE);
            $paddress = '';
            foreach ($pick as $k => $v) {
                $paddress .= $v['areaName'] . '/';
            }
            $send = json_decode($value['sendaddress'],TRUE);
            $saddress = '';
            foreach ($send as $ke => $val) {
                  $saddress .= $val['areaName'] . '/';
            }
            $userarr[] = array(
                'Number'=>$i,
                'Realname' => $value['realname'],
                'Phone' => $value['phone'],
                'AddTime' => date('Y-m-d',$value['addtime']),
                'Pickaddress' => trim($paddress,'/'),
                'Sendaddress' => trim($saddress,'/')
              );
            $i++;
          }//end foreach
    }else{
       $title = '零担未支付用户';
        $where['paystate'] = 1;
        $result = Db::table('ct_order')
                    ->alias('o')
                    ->join('ct_user u','o.userid=u.uid')
                    ->field('o.userid,o.pickaddress,o.sendaddress,u.realname,u.phone,u.addtime')
                    ->where($where)
                    ->group('userid')
                    ->select();
        $userarr = array();
        $i=1;
        foreach ($result as $key => $value) {
          $pick = json_decode($value['pickaddress'],TRUE);
          $paddress = '';
          foreach ($pick as $k => $v) {
              $paddress .= $v['taddressstr'] . '/';
          }
          $send = json_decode($value['sendaddress'],TRUE);
          $saddress = '';
          foreach ($send as $ke => $val) {
                $saddress .= $val['paddressstr'] . '/';   
          }
          $userarr[] = array(
              'Number'=>$i,
              'Realname' => $value['realname'],
              'Phone' => $value['phone'],
              'AddTime' => date('Y-m-d',$value['addtime']),
              'Pickaddress' => trim($paddress,'/'),
              'Sendaddress' => trim($saddress,'/')
            );
          $i++;
        }//end foreach
    }

    $xlsCell  = array(
          array('Number','编号'),
          array('Realname','姓名'),
          array('Phone','联系方式'),
          array('AddTime','注册时间'),
          array('Pickaddress','发货地址'),
          array('Sendaddress','收货地址')
    );
    $this->writertwo($title,$xlsCell,$userarr);
  }
 
 /*
 *后台导出零担用户列表
 */
 public function getbulkclient(){
     $get_data = Request::instance()->get();
        // 下单总额
        $countcoat = 0;
        $userarr = array();
        $result = DB::field('a.*,pic.tprice,user.realname,user.phone,user.lineclient,user.userstate,user.username,d.start_id,d.end_id')
            ->table('ct_order')
            ->alias('a')
            ->join('ct_user user','user.uid = a.userid')
            ->join('ct_pickorder pic','pic.orderid = a.oid')
            ->join('ct_shift c','c.sid=a.shiftid')
            ->join('ct_already_city d','d.city_id = c.linecityid')
            ->where(array('a.oid'=>['IN', $get_data['id']]))
            ->select();
        foreach ($result as $key => $value) {
            $CompanyName ='';
            if($value['lineclient'] != ''){
              $CompanyName = $this->getCompanyname($value['lineclient']);
             
            }
            $pick_add = json_decode($value['pickaddress'],TRUE);
            $pick_str ='';
            foreach ($pick_add as $v) {
              $pick_str .= $v['taddressstr'] .'/';
            }
            $send_add = json_decode($value['sendaddress'],TRUE);
            $send_str ='';
            foreach ($send_add as $val) {
              $send_str .= $val['paddressstr'] .'/';
            }
            if($value['userstate'] == 1){
                $client_state = '小微客户';
            }elseif($value['userstate'] == 2){
                $client_state = '项目客户';
            }elseif($value['userstate'] ==3){
                $client_state = '线下客户';
            }
            $countcoat = ($value['tprice']+$value['linepice']+$value['delivecost']);
            $count_all = $value['totalcost']=='' ? $countcoat : $value['totalcost'];
            $userarr[] = array(
                    'ID' => $value['oid'],
                    'OrderNumber' =>$value['ordernumber'],
                    'CompanyName' =>$CompanyName,
                    'Realname' =>$value['realname']=='' ? $value['username'] : $value['realname'],
                    'Phone' =>$value['phone'],
                    'AddTime' => date('Y-m-d H:i',$value['addtime']),
                    'Line' => $this->start_end_city($value['start_id'],$value['end_id']),
                    'get_weight' => $value['totalweight'],
                    'get_volume' => $value['totalvolume'],
                    'pickaddress' => rtrim($pick_str,'/'),
                    'sendaddress' => rtrim($send_str,'/'),
                    'ti_pay' => $value['tprice'],
                    'TrunkCost' => $value['linepice'],
                    'pei_pay' => $value['delivecost'],
                    'total' => $countcoat,
                    'states' =>$client_state,
                    'count_all'=>$count_all
                    
                ); 
        }
        $xlsCell  = array(
            array('ID','ID'),
            array('OrderNumber','订单编号'),
            array('CompanyName','客户公司'),
            array('Realname','下单客户'),
            array('Phone','下单客户'),
            array('states','客户属性'),
            array('AddTime','下单时间'),
            array('Line','线路'),
            array('pickaddress','提货地址'),
            array('sendaddress','配送地址'),
            array('get_weight','重量(kg)'),
            array('get_volume','立方(m³)'),
            array('ti_pay','提货费(元)'),
            array('TrunkCost','干线费(元)'),
            array('pei_pay','配送费(元)'),
            array('total','金额(元)'),
            array('count_all','交易额(元)')
            
        );
        $this->writertwo('客户',$xlsCell,$userarr);
 }

public function getCompanyname($id){
  $comname = DB::field('name')->table('ct_company')->where('cid',$id)->find();
  return $comname['name'];
}

     /*
     *
     *整车订单
     */
    // public function getcarclient(){
    // $getid = Request::instance()->get();
    // $result = Db::table('ct_userorder')
    //     ->alias('a')
    //     ->join('ct_cartype c','c.car_id = carid')
    //     ->join('ct_user u','u.uid=a.userid')
    //     ->field('a.*,c.carparame,u.username,u.realname,u.phone,u.lineclient')
    //           ->where(array('uoid'=>['IN',$getid['id']]))
    //           ->select();
    //     $good = '';
        
    //     $CompanyName = '';
    //     $i=1;
    //     foreach ($result as $key => $value) {
    //         $paddress = '';
    //         $saddress = '';
    //         $pick = json_decode($value['pickaddress'],TRUE);
    //         foreach ($pick as $k => $v) {
    //             $paddress .= $v['areaName'] . '/';
    //         }
    //         $send = json_decode($value['sendaddress'],TRUE);
    //         foreach ($send as $ke => $val) {
    //             $saddress .= $val['areaName'] . '/';
    //         }
          
    //         if ($value['lineclient'] !='') {
    //             $CompanyName = $this->getCompanyname($value['lineclient']);
    //         }
    //         $arr[] = array(
    //                 'ID' => $i,
    //                 'Picktime' => date("Y-m-d",$value['loaddate']/1000), //提货日期
    //                 'StartCity' => $this->start_city($value['startcity']),
    //                 'EndCity' => $this->start_city($value['endcity']),
    //                 'Cartype' => $value['carparame'],
    //                 'GoodType' =>trim($good,'/'),
    //                 'Paddress'=> trim($paddress,'/'),
    //                 'Arrtime' => date("Y-m-d",$value['arrtime']/1000), //到达日期
    //                 'Saddress' => trim($saddress,'/'),
    //                 'count_all' =>  $value['referprice'],
    //                 'SendName' => $value['realname']==''?$value['username']:$value['realname'],
    //                 'Telephone' => $value['phone'],
    //                 'CompanyName' => $CompanyName,
    //                 'Remark' => $value['remark']
    //         );
    //         $i++;
    //     }
    //     $list = array(
    //         array('ID','编号'),
            
    //         array('Picktime','提货日期'),
    //         array('CompanyName','客户公司'),
    //         array('SendName','下单客户'),
    //         array('Telephone','联系号码'),
    //         array('StartCity','装货城市'),
    //         array('EndCity','目的地城市'),
    //         array('GoodType','物物品名'),
    //         array('Cartype','车型'),
    //         array('count_all','价格'),
    //         array('Paddress','提货详细地址'),
    //         array('Arrtime','到达日期'),
    //         array('Saddress','发货详细地址'),
            
    //         array('Remark','备注')
    //       );
    //    $this->writertwo('下单列表',$list,$arr);
    // }
    /*
    *定制订单
    *
    */
    public function getshiftoder(){
        $dataid = Request::instance()->get();
        if (empty($dataid)) {
          $where = '';
        }else{
          $where = array('a.s_oid'=>['IN',$dataid['id']]);
        }
        $where['a.affirm'] = 2;
        $result = Db::table('ct_shift_order')
                    ->alias('a')
                    ->join('ct_fixation_line l','l.id=a.shiftid')
                    ->join('ct_user u','u.uid=a.userid')
                    ->join('ct_company c','c.cid=u.lineclient')
                    ->join('ct_already_city al','al.city_id = l.lienid')
                    ->field('a.*,l.oneline,l.lienid,l.doornum,l.goodname,l.temperature,l.paddress,c.name')
                    ->where($where)
                    ->select();
        $userarr_shift = array();
        $i=1;
        foreach ($result as $key => $value) {
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
            $count_all = $value['upprice']=='' ? $value['totalprice'] : $value['upprice'];
            //提货地址地址
            $saddress_str = json_decode($value['paddress'],TRUE);
            $saddress = '';
            
            foreach ($saddress_str as $key => $val) {
                $saddress .=$val.'/';
            }
              $userarr_shift[] = array(
                  'ID' => $i,
                  'OrderNumber' =>$value['ordernumber'],
                  'CompanyName' =>$value['name'],
                  'AddTime' => date('Y-m-d',$value['addtime']),
                  'Line' => $startcity .'--'.$endcity,
                  'SendType'=>$value['goodname'],
                  'ColdType'=>$value['temperature'],
                  'Doornum' => $value['doornum'],
                  'Oneline' =>$value['oneline'],
                  'Count_all'=>$count_all,
                  'Saddress' => rtrim($saddress,'/')
              ); 
            $i++;
        } //end ct_shift_order froeach
        $xlsCell  = array(
            array('ID','编号'),
            array('OrderNumber','订单编号'),
            array('CompanyName','公司名称'),
            array('AddTime','下单时间'),
            array('Line','线路'),
            array('SendType','物品类型'),
            array('ColdType','冷藏类型'),
            array('Doornum','门店数'),
            array('Oneline','单个门店价(元/门店)'),
            array('Count_all','运费'),
            array('Saddress','提货地址')
            
        );
        $this->writertwo('用户定制订单列表',$xlsCell,$userarr_shift);
    }

    /**
     * 整车订单导出
     */
    public function getcarclient(){
        $getid = Request::instance()->get();

        $result = Db::field('a.*,d.carparame,u.realname,u.username,u.phone,u.lineclient,u.userstate,in.instate')
                  ->table('ct_userorder')
                  ->alias('a')              
                  ->join('ct_user u','a.userid = u.uid')
                  ->join('ct_cartype d','d.car_id = a.carid')
                  ->join('ct_invoice in','in.iid=a.user_checkid','LEFT')
                  ->where(array('uoid'=>['IN',$getid['id']]))
                  ->order('a.uoid','desc')
                  ->select();
     
        $good = '';
        
        $CompanyName = '';
        $i=1;
        foreach ($result as $key => $value) {
           
            // 发货收货地址
            $paddress = '';
            $saddress = '';
            $pick = json_decode($value['pickaddress'],TRUE);
            foreach ($pick as $k => $v) {
                $paddress .= $v['areaName'] . '/';
            }
            $send = json_decode($value['sendaddress'],TRUE);
            foreach ($send as $ke => $val) {
                $saddress .= $val['areaName'] . '/';
            }
            // 公司名称
            if ($value['lineclient'] !='') {
                $CompanyName = $this->getCompanyname($value['lineclient']);
            }
            // 支付状态
            $paystate_str = '';
            $pay = 0;
            if ($value['paystate']=='2') {
                if ($value['lineclient'] !='' && $value['pay_type'] =='1') {
                    if ($value['instate']=='2' ) {
                        $paystate_str='已支付';
                        $pay = $value['upprice']==''?$value['referprice']:$value['upprice'];
                    }else{
                        $paystate_str='信用支付';
                    }
                }else{
                    $paystate_str='已支付';
                    $pay = $value['upprice']==''?$value['referprice']:$value['upprice'];
                }
            }else{
                $paystate_str='未支付';
            }

            $arr[] = array(
                    'ID' => $i,
                    'Picktime' => date("Y-m-d",$value['loaddate']/1000), //提货日期
                    'StartCity' => $this->start_city($value['startcity']),
                    'EndCity' => $this->start_city($value['endcity']),
                    'Cartype' => $value['carparame'],
                    'GoodType' =>trim($good,'/'),
                    'Paddress'=> trim($paddress,'/'),
                    'Arrtime' => date("Y-m-d",$value['arrtime']/1000), //到达日期
                    'Saddress' => trim($saddress,'/'),
                    'count_all' =>  $value['upprice']==''?$value['referprice']:$value['upprice'] ,
                    'Getprice' =>  $pay ,
                    'SendName' => $value['realname']==''?$value['username']:$value['realname'],
                    'Telephone' => $value['phone'],
                    'CompanyName' => $CompanyName,
                    'Remark' => $value['remark'],
                    'Paystate' => $paystate_str,
            );
            $i++;
        }
        $list = array(
            array('ID','编号'),
            array('Picktime','提货日期'),
            array('CompanyName','客户公司'),
            array('SendName','下单客户'),
            array('Telephone','联系号码'),
            array('StartCity','装货城市'),
            array('EndCity','目的地城市'),
            array('GoodType','物物品名'),
            array('Cartype','车型'),
            array('Paddress','提货详细地址'),
            array('Arrtime','到达日期'),
            array('Saddress','发货详细地址'),
            array('Remark','备注'),
            array('Paystate','支付状态'),
            array('count_all','应收价格'),
            array('Getprice','实收收价格')
        );
        $this->writertwo('下单列表',$list,$arr);
    }


static function writertwo($expTitle,$expCellName,$expTableData,$type = 0){
        $result = import("PHPExcel",EXTEND_PATH.'PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $xlsTitle = iconv('utf-8', 'gb2312//IGNORE', $expTitle);//文件名称
        $fileTitle =  $xlsTitle.date('_Y-m-d His');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $countArr  = array('get_weight','get_volume','ti_pay','TrunkCost','pei_pay','total','count_all','pay');
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  列表:'.date('Y-m-d H:i:s'));  
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]); 
        } 
          // Miscellaneous glyphs, UTF-8
        $end = $dataNum+2;
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            if(in_array($expCellName[$j][0], $countArr)){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+4), '=SUM('.$cellName[$j].'3:'.$cellName[$j].$end.')');
            }
          } 
                   
        }  
        $fileName = iconv("utf-8", "gb2312//IGNORE", './Data/excel/'.date('Y-m-d_', time()).time().'.xls');
        $saveName = iconv("utf-8", "gb2312//IGNORE", $fileTitle.'.xls');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        if ($type == 0) {
             header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
            header("Content-Disposition: attachment;filename=\"$saveName\"");
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
        } else {
            $objWriter->save($fileName);
            return $fileName;
        }
         
    }


    static function reader($file) {
        if (self::_getExt($file) == 'xls') {
            $result = import("Excel5",EXTEND_PATH.'PHPExcel/PHPExcel/Reader');
            $PHPReader = new \PHPExcel_Reader_Excel5();
        } elseif (self::_getExt($file) == 'xlsx') {
            $result = import("Excel2007",EXTEND_PATH.'PHPExcel/PHPExcel/Reader');
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        } else {
            return '路径出错';
        }

        $PHPExcel     = $PHPReader->load($file);
        $currentSheet = $PHPExcel->getSheet(0);
        $allColumn    = $currentSheet->getHighestColumn();
        $allRow       = $currentSheet->getHighestRow();
        for($currentRow = 2; $currentRow <= $allRow; $currentRow++){
            for($currentColumn='B'; $currentColumn <= $allColumn; $currentColumn++){
                $address = $currentColumn.$currentRow;
                $arr[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
            }
        }
        return $arr;
    }

    private static function _getExt($file) {
        return pathinfo($file, PATHINFO_EXTENSION);
    }


    

    /*public function in(){
        $content = file_get_contents('./UploadFiles/excel/ceshi.xls');
        dump($content);exit;

    }*/

    
}
