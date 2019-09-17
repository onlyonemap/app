<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Charts  extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->if_login();
//
//    }

    public function index(){
        $count = array();
        //统计客户总数
        $countclient = DB::table('ct_user')->where('delstate',1)->count();
        //统计今年客户注册数量
        $countclinet_year = DB::query("SELECT count(uid) as count FROM `ct_user` where delstate=1  AND DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')=DATE_FORMAT(FROM_UNIXTIME(".time()."),'%Y')");
        //统计承运商总数
        $carrcount = DB::table('ct_driver')->where(array('delstate'=>1,'type'=>3))->count();
        //统计今年承运商总数
        $carrount_year = DB::query("SELECT count(drivid) as count FROM `ct_driver` where delstate=1 AND type =3 AND DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') = DATE_FORMAT(FROM_UNIXTIME(".time()."),'%Y')");
        //统计运营干线
        $shiftcount = DB::table('ct_shift')->where(array('delstate'=>1))->count();
        //统计本年度的运营干线
        $shiftcountyear = DB::query("SELECT count(sid) count FROM `ct_shift` where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')=DATE_FORMAT(FROM_UNIXTIME(".time()."),'%Y') AND delstate=1");
        $count =array('countclient'=>$countclient,'countclinetyear'=>$countclinet_year[0]['count'],'carrcount'=>$carrcount,'carrcountyear'=>$carrount_year[0]['count'],'shiftcount'=>$shiftcount,'shiftcountyear'=>$shiftcountyear[0]['count']);
       
        $this->assign('count',$count);
    	return view('charts/index');
    }
    // 1 获取注册客户、线下客户、撮合客户、总注册客户月数据
    public function ClientMonthNumber(){
        $year = isset($_POST["year"]) ? $_POST["year"] : '';
        $query=DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(addtime),'%m') months,DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,count(uid) count,
            (SELECT count(uid) FROM ct_user where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')=".$year." AND DATE_FORMAT(FROM_UNIXTIME(addtime),'%m')=months AND userstate=1) count0,
            (SELECT count(uid) FROM ct_user where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')=".$year." AND DATE_FORMAT(FROM_UNIXTIME(addtime),'%m')=months AND userstate=2) count1,
            (SELECT count(uid) FROM ct_user where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')=".$year." AND DATE_FORMAT(FROM_UNIXTIME(addtime),'%m')=months AND userstate=3) count2 
            FROM ct_user where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')=".$year." GROUP BY months");
        echo json_encode($query);
    }
    // 1 获取注册客户、线下客户、撮合客户、总注册客户月数据
    public function ClientYearNumber(){
        
        $query=DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,count(uid) count,
            (SELECT count(uid) FROM ct_user where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')=years AND userState=1 GROUP BY DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')) count0,
            (SELECT count(uid) FROM ct_user where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')=years AND userState=2 GROUP BY DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')) count1,
            (SELECT count(uid) FROM ct_user where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')=years AND userState=3 GROUP BY DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')) count2 
            FROM ct_user GROUP BY years");
        echo json_encode($query);
    }
    //2获取承运商数量月数据
    public function carrierMonthNumber(){
        //获得传值年份
        $year = isset($_POST["year"]) ? $_POST["year"] : '';//获取年份
        //查询年份对应月份,月份承运商注册数据
        $query=DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(addtime),'%m') months,count(drivid) count FROM ct_driver where type =3 AND DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')='$year' GROUP BY months");
        echo json_encode($query);
    }
    //2获取承运商数量年数据
    public function carrierYearNumber(){
        //获取已有年份
        $query=DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,count(drivid) count FROM ct_driver where type =3 GROUP BY years");
        echo json_encode($query);
    }
    //3客户年,默认显示当年货物重量最多的那位客户
    public function PlatformGoodsYearWeight(){
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
        $line = isset($_POST["lineID"]) ? $_POST["lineID"] : ''; //获取开通城市的ID

        $array = array();
        $arr_uid = array();
        if ($CustomerID !='') {
            //未选择线路时候
            $user_id = DB::table('ct_user')->where('uid',$CustomerID)->find();
           if($line =='') {
               
               //项目客户找到该公司下所有用户下的订单
               if ($user_id['userstate'] == 2) {
                   $userid_str = $this->getuseridstr($user_id['lineclient']);
                  
                   $query = DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,sum(totalweight) count FROM ct_order  where userid IN(".$userid_str.") AND orderstate=7 GROUP BY years ");
                    
               }else{
                    $query = DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,sum(totalweight) count FROM ct_order  where userid = ".$CustomerID." AND orderstate=7 GROUP BY years ");
                   
               }
           }else{
                //当选择线路时候，项目客户筛选为当下线路下所有的订单情况
                if($user_id['userstate'] == 2) {
                       $userid_str = $this->getlineuseridstr($user_id['lineclient'],$line);
                       $query = DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,sum(totalweight) count FROM ct_order  where userid IN(".$userid_str.") AND orderstate=7 GROUP BY years ");
                    }else{
                        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y') years,sum(totalweight) count FROM ct_order a INNER JOIN ct_shift d ON d.sid =a.shiftid where d.linecityid=".$line." AND userid =".$CustomerID." AND orderstate=7 GROUP BY years ");
                    }
                }
        }else{
            $query = DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,sum(totalweight) count FROM ct_order  where orderstate=7 GROUP BY years ");
        }
       if (!empty($query)) {
            foreach ($query as $key => $value) {
                $array  = array(array('years'=>$value['years'],'customerName'=>'','weight'=>$value['count']));
            }
       }else{
            $array =array(array('years'=>'','customerName'=>'','weight'=>0));
       }
        
        echo json_encode($array);
        //echo json_encode($query);
    }
    //3客户月数据（带对应检索条件）
    public function PlatformGoodsMonthWeight(){
        $year = isset($_POST["year"]) ? $_POST["year"] : '';//获取年份
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
         $array =array(array('months'=>'','count'=>0));
        //echo  $CustomerID;
        $line = isset($_POST["lineID"]) ? $_POST["lineID"] : ''; //获取线路
        if ($CustomerID !='') {
            $user_id = DB::table('ct_user')->where('uid',$CustomerID)->find();
            if ($line=='') {
                 if ($user_id['userstate'] == 2) {
                   $userid_str = $this->getuseridstr($user_id['lineclient']);
                   $query=DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(addtime),'%m') months,sum(totalweight) count FROM ct_order where userid IN(".$userid_str.") AND  DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')='$year' AND orderstate=7 GROUP BY months");
                }else{
                    $query=DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(addtime),'%m') months,sum(totalweight) count FROM ct_order where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')='$year'  AND userid=".$CustomerID." AND orderstate=7 GROUP BY months");
                }
                
            }else{

                 if($user_id['userstate'] == 2) {
                       $userid_str = $this->getlineuseridstr($user_id['lineclient'],$line);
                        $query = DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(addtime),'%m') months,sum(totalweight) count FROM ct_order  where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')='$year' AND userid IN (".$userid_str.") AND orderstate=7 GROUP BY months"); 
                }else{
                   $query = DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%m') months,sum(totalweight) count FROM ct_order a INNER JOIN ct_shift d ON d.sid=a.shiftid where DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y')='$year' AND d.linecityid=".$line." AND a.userid=".$CustomerID." AND orderstate=7 GROUP BY months"); 
                }
                
            }
        }else{
            $query=DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(addtime),'%m') months,sum(totalweight) count FROM ct_order where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')='$year'  AND orderstate=7 GROUP BY months");
        }
        foreach ($query as $key => $value) {
            $array =array(array('months'=>$value['months'],'count'=>$value['count']));
        }

        echo json_encode($array);
    } 
    //3干线承运商年数据（具体承运商）
    public function CarrierGoodsYearWeight(){
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
        $line = isset($_POST["lineID"]) ? $_POST["lineID"] : ''; //获取开通城市的ID
        if ($line !='') {
            $line_str = " AND c.sid = ".$line;
        }else{
            $line_str = '';
        }
        
        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y') years,d.name as carrierName,sum(totalweight) weight FROM ct_order a  INNER JOIN ct_shift c ON c.sid=a.shiftid INNER JOIN ct_company d ON c.companyid=d.cid where d.cid=".$CustomerID." AND a.orderstate=7 ".$line_str." GROUP BY years");
        echo json_encode($query);
        //echo $line;

    }
    //3干线承运商月数据（具体承运商）
    public function CarrierGoodsMonthWeight(){
        $year = isset($_POST["year"]) ? $_POST["year"] : '';//获取年份
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
        $line = isset($_POST["lineID"]) ? $_POST["lineID"] : ''; //获取线路
        if ($line !='') {
            $line_str = " AND c.sid = ".$line;
        }else{
            $line_str = '';
        }
        
        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%m') months,sum(totalweight) count FROM ct_order a INNER JOIN ct_shift c ON c.sid=a.shiftid INNER JOIN ct_company d ON c.companyid=d.cid where DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y')=".$year." AND d.cid=".$CustomerID." AND a.orderstate=7 ".$line_str." GROUP BY months");

        
        echo json_encode($query);

    } 
    //3提货承运商年数据（具体承运商）
    public function PickGoodsYearWeight(){
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y') years,d.name as carrierName,sum(totalweight) weight FROM ct_order a INNER JOIN ct_pickorder b ON b.orderid = a.oid INNER JOIN ct_driver dr ON dr.drivid =b.driverid  INNER JOIN ct_company d ON dr.companyid=d.cid where b.status=2 AND d.cid=".$CustomerID." AND a.orderstate=7  GROUP BY years");
        echo json_encode($query);
    }
    //3提货承运商月数据（具体承运商）
    public function PickGoodsMonthWeight(){
        $year = isset($_POST["year"]) ? $_POST["year"] : '';//获取年份
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%m') months,sum(totalweight) count FROM ct_order a INNER JOIN ct_pickorder b ON b.orderid = a.oid INNER JOIN ct_driver dr ON dr.drivid =b.driverid  INNER JOIN ct_company d ON dr.companyid=d.cid where DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y')=".$year." AND b.status=2 AND d.cid=".$CustomerID." AND a.orderstate=7 GROUP BY months");
        echo json_encode($query);
    }
    //检索客户,当存在公司ID时候查找ct_company公司名称模糊查询，否则查找ct_user中公司名称，否则查找realname
    public function CustomersSearch(){
        $search = strtolower($_GET["term"]);
        $where_data['a.delstate'] = 1;

        $where_data['a.realname|b.name'] = ['like','%'.$search.'%'];
        $array=array();
        $arr = array();
        $result = DB::field('a.uid,a.realname,b.name')
                    ->table('ct_user')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.lineclient','LEFT')
                    ->where($where_data)
                    ->SELECT();
       
        foreach ($result as $key => $value) {
            if($value['name'] !='') {
                $realName = $value['name'];
            }else{
                $realName = $value['realname'];
            }
        $array[] = array(
                'id'=>$value['uid'],
                'label'=>$realName
            );
        }
       
        $out = array();
        foreach ($array as $key=>$value) {
            if (!in_array($value['label'], $out))
            {
                $out[$value['id']] = $value['label'];
            }
        }
        foreach ($out as $key2 => $val) {
            $arr[] = array(
                    'id'=>$key2,
                    'label'=>$val
                );
        }
        echo json_encode($arr);
    }
    //根据客户ID查找客户下单所有的路线,如果是项目客户则根据公司ID查选
    public function CustomersShiftSearch(){
        $userID = strtolower($_POST["id"]);
        $lineid = array();
        $arr = array();
        $shift_arr = array();
        $user_arr = DB::table('ct_user')->where('uid',$userID)->find();

        //项目客户
        if($user_arr['userstate'] == 2) {
                $user_com = DB::field('uid')
                            ->table('ct_user')
                            ->where('lineclient',$user_arr['lineclient'])
                            ->SELECT();
                foreach ($user_com as $key => $value) {
                   $arr_uid[] = $value['uid'];
               }
               $userid_str = implode(',',$arr_uid);
              
                $shift_arr =  DB::field('d.start_id,d.end_id,d.city_id')
                                ->table('ct_order')
                                ->alias('a')
                                ->join('ct_shift c','c.sid = a.shiftid')
                                ->join('ct_already_city d','d.city_id = c.linecityid')
                                ->where(array('a.userid'=>['IN',$userid_str],'a.orderstate'=>7))
                                ->SELECT();
                 
                if (!empty($shift_arr)) {
                    foreach ($shift_arr as  $val) {
                        $lineid[] = array('start'=>$val['start_id'],'endid'=>$val['end_id'],'city_id'=>$val['city_id']);
                    }
                }
          
            
        }else{
            $shift_arr =  DB::field('d.start_id,d.end_id,d.city_id')
                            ->table('ct_order')
                            ->alias('a')
                            ->join('ct_shift c','c.sid = a.shiftid')
                            ->join('ct_already_city d','d.city_id = c.linecityid')
                            ->where(array('a.userid'=>$userID,'a.orderstate'=>7))
                            ->SELECT();
            if (!empty($shift_arr)) {
                foreach ($shift_arr as  $val) {
                    $lineid[] = array('start'=>$val['start_id'],'endid'=>$val['end_id'],'city_id'=>$val['city_id']);
                }
            }
        }
        //去掉重复的数组
        if (!empty($lineid)) {
            $unique = $this->more_array_unique($lineid);
            foreach ($unique as $key => $value) {
                 $arr[] = array(
                    'shiftID' => $value['city_id'], //开通城市ID
                     'shiftName' => $this->start_end_city($value['start'],$value['endid'])
                );
            }
        }
        //print_r($arr);
         echo json_encode($arr);
        
    }

    // 检索干线承运商
    public function carrierSearch(){
        $search = strtolower($_GET["term"]);
        $where_data['status'] = 1;
        $where_data['type'] = 1;
        $where_data['name'] = ['like','%'.$search.'%'];
        $com = DB::table('ct_company')->where($where_data)->SELECT();
        $result = array();
        foreach ($com as $key => $value) {
                $result[] = array(
                    'id' => $value['cid'],
                    'label' => $value['name']
            );
        }
        echo json_encode($result);
    }
    // 检索提货承运商
    public function pickSearch(){
        $search = strtolower($_GET["term"]);
        $where_data['status'] = 1;
        $where_data['type'] = 2;
        $where_data['name'] = ['like','%'.$search.'%'];
        $com = DB::table('ct_company')->where($where_data)->SELECT();
        $result = array();
        foreach ($com as $key => $value) {
                $result[] = array(
                    'id' => $value['cid'],
                    'label' => $value['name']
            );
        }
        echo json_encode($result);
    }
    // 检索承运商班次
    public function carrierShiftSearch(){
        $search = strtolower($_POST["id"]);
        $array = array();
        $result = DB::field('a.sid,b.start_id,b.end_id')
                    ->table('ct_shift')
                    ->alias('a')
                    ->join('ct_already_city b','b.city_id = a.linecityid')
                    ->where(array('a.companyid'=>$search,'whethertoopen'=>1,'delstate'=>1))
                    ->SELECT();
        if(!empty($result)) {
            foreach ($result as $key => $value) {
                 $array[] = array(
                    'shiftID' => $value['sid'], //班次ID
                    'shiftName' => $this->start_end_city($value['start_id'],$value['end_id'])
                );
            }
        }
        echo json_encode($array);

    }
    // 4客户年,默认显示当年货物总订单数
   public function ClientYearOrders(){
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
        $line = isset($_POST["lineID"]) ? $_POST["lineID"] : ''; //获取线路
        $array = array();
        if ($CustomerID == '') {
            $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,count(oid) count FROM ct_order where orderstate=7 GROUP BY years");
          
        }else{
            $user_id = DB::table('ct_user')->where('uid',$CustomerID)->find();
           if($line =='') {
               //项目客户找到该公司下所有用户下的订单
               if ($user_id['userstate'] == 2) {
                   

                   $userid_str = $this->getuseridstr($user_id['lineclient']);
                   $query = DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,count(oid) count FROM ct_order  where userid IN(".$userid_str.") AND orderstate=7 GROUP BY years ");
                    
               }else{
                    $query = DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,sum(oid) count FROM ct_order  where userid = ".$CustomerID." AND orderstate=7 GROUP BY years ");
                    //var_dump($query);
               }
            }else{
                 if($user_id['userstate'] == 2) {
                         $userid_str = $this->getuseridstr($user_id['lineclient'],$line);
                       $query = DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,count(oid) count FROM ct_order  where userid IN(".$userid_str.") AND orderstate=7 GROUP BY years ");
                    }else{
                        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y') years,count(oid) count FROM ct_order a INNER JOIN ct_shift d ON d.sid =a.shiftid where d.linecityid=".$line." AND userid =".$CustomerID." AND orderstate=7 GROUP BY years ");
                    }
            }
        }
        foreach ($query as $key => $value) {
               $array = array(array('years'=>$value['years'],'customerName'=>'','Number'=>$value['count']));
        }
        echo json_encode($array);
   }
   // 4客户年,默认显示月货物总订单数
   public function ClientMonthOrders(){
        $year = isset($_POST["year"]) ? $_POST["year"] : '';//获取年份
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
        $line = isset($_POST["lineID"]) ? $_POST["lineID"] : ''; //获取线路
        $array = array();

        if ($CustomerID =='') {
            $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(addtime),'%m') months,count(oid) Number FROM ct_order where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')='$year' AND orderstate=7 GROUP BY months");
        }else{
            $user_id = DB::table('ct_user')->where('uid',$CustomerID)->find();
            if ($line == '') {
                if ($user_id['userstate'] == 2) {
                   $userid_str = $this->getuseridstr($user_id['lineclient']);
                   $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(addtime),'%m') months,count(oid) Number FROM ct_order where userid IN(".$userid_str.") AND DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')='$year' AND orderstate=7 GROUP BY months");
               }else{
                    $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(addtime),'%m') months,count(oid) Number FROM ct_order where userid =".$CustomerID." AND DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')='$year' AND orderstate=7 GROUP BY months");
               }
            }else{
                if ($user_id['userstate'] == 2) {
                    $userid_str = $this->getuseridstr($user_id['lineclient'],$line);
                    $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(addtime),'%m') months,count(oid) Number FROM ct_order where userid IN(".$userid_str.") AND DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')='$year' AND orderstate=7 GROUP BY months");
                }else{
                    $query = DB::query("SELECT  DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%m') months,count(oid) Number FROM ct_order a INNER JOIN ct_shift d ON d.sid=a.shiftid where DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y')='$year' AND d.linecityid=".$line." AND a.userid=".$CustomerID." AND orderstate=7 GROUP BY months"); 
                }
            }
        }
        echo json_encode($query);
   }


   // 4干线承运商订单年数据（具体承运商）
   public function CarrierYearOrders(){
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
        $line = isset($_POST["lineID"]) ? $_POST["lineID"] : ''; //获取开通城市的ID
        if ($line !='') {
            $line_str = " AND c.sid = ".$line;
        }else{
            $line_str = '';
        }
        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y') years,d.name as carrierName,count(oid) Number FROM ct_order a INNER JOIN ct_shift c ON c.sid=a.shiftid INNER JOIN ct_company d ON c.companyid=d.cid where d.cid=".$CustomerID." AND a.orderstate=7 ".$line_str." GROUP BY years"); 
        echo json_encode($query);
   }
   // 4干线承运商订单月数据（具体承运商）
    public function CarrierMonthOrders(){
        $year = isset($_POST["year"]) ? $_POST["year"] : '';//获取年份
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
        $line = isset($_POST["lineID"]) ? $_POST["lineID"] : ''; //获取线路
        if ($line !='') {
            $line_str = " AND c.sid = ".$line;
        }else{
            $line_str = '';
        }
        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%m') months,count(oid) Number FROM ct_order a INNER JOIN ct_shift c ON c.sid=a.shiftid INNER JOIN ct_company d ON c.companyid=d.cid where DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y')=".$year." AND d.cid=".$CustomerID." AND a.orderstate=7 ".$line_str." GROUP BY months");
        echo json_encode($query);
    }
     // 4提货承运商订单年数据（具体承运商）
    public function PickCarrierYearOrders(){
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y') years,d.name as carrierName,count(a.oid) Number FROM ct_order a INNER JOIN ct_pickorder b ON b.orderid = a.oid INNER JOIN ct_driver dr ON dr.drivid =b.driverid  INNER JOIN ct_company d ON dr.companyid=d.cid where b.status=2 AND d.cid=".$CustomerID." AND a.orderstate=7  GROUP BY years");
        echo json_encode($query);
    }

    // 4提货承运商订单月数据（具体承运商）
    public function PickCarrierMonthOrders(){
        $year = isset($_POST["year"]) ? $_POST["year"] : '';//获取年份
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';//获取客户
        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%m') months,count(a.oid) Number FROM ct_order a INNER JOIN ct_pickorder b ON b.orderid = a.oid INNER JOIN ct_driver dr ON dr.drivid =b.driverid INNER JOIN ct_company d ON dr.companyid=d.cid where DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%Y')=".$year." AND b.status=2 AND d.cid=".$CustomerID." AND a.orderstate=7 GROUP BY months");
        echo json_encode($query);
    }

    /// 5 获取营运干线数量年数据
    public function shiftYearNumber(){
        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y') years,count(sid) count FROM ct_shift  where delstate=1 GROUP BY years");
        echo json_encode($query);
    }
    /// 5 获取营运干线数量月数据
    public function shiftMonthNumber(){
        $year = isset($_POST["year"]) ? $_POST["year"] : '';//获取年份
        $query = DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(addtime),'%m') months,count(sid) count FROM ct_shift where DATE_FORMAT(FROM_UNIXTIME(addtime),'%Y')='$year' AND delstate =1 GROUP BY months");
        echo json_encode($query);
    }

//第6个根据用户名获取订单城市
   public function getcityiid(){
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';
        $result = Db::query("SELECT distinct d.start_id FROM ct_order a LEFT JOIN ct_shift c ON c.sid=a.shiftid LEFT JOIN ct_already_city d ON d.city_id=c.linecityid WHERE a.userid='$CustomerID'");
        foreach ($result as $key => $value) {
            $result[$key]['shifaID'] = $value['start_id'];
            $result[$key]['shifaName'] = detailadd($value['start_id'],'','');
        }
        return json_encode($result);
   }
   //第6个图第二步
   public function distributionofgoods(){
        $CustomerID = isset($_POST["CustomerID"]) ? $_POST["CustomerID"] : '';
        $shifaID = isset($_POST["shifaID"]) ? $_POST["shifaID"] : '';
        $totalweight = Db::query("SELECT sum(totalweight) totalweight FROM ct_order WHERE userid='$CustomerID'");
        $result = array();
        $weight = Db::query("SELECT sum(a.totalweight) count,d.end_id FROM ct_order a LEFT JOIN ct_shift c ON c.sid=a.shiftid LEFT JOIN ct_already_city d ON d.city_id=c.linecityid WHERE a.userid='$CustomerID' AND d.start_id='$shifaID'  GROUP BY d.end_id order BY count desc limit 0,5");
        foreach ($weight as $key => $value) {
            $result[$key]['weight'] = $value['count'];
            $result[$key]['CityName'] = detailadd($value['end_id'],'','');
            $result[$key]['CityName1'] = $totalweight['0']['totalweight'];
        }
        return json_encode($result);
   }
   //平台月度发货量前十统计图
   public function toptenstatistics(){
        $year = isset($_POST["year"]) ? $_POST["year"] : '';//获取年份
        $month = isset($_POST["month"]) ? $_POST["month"] : '';//获取月份
        $result = Db::query("SELECT DATE_FORMAT(FROM_UNIXTIME(a.addtime),'%m') months,sum(a.totalweight) weight,a.userid customerName,b.lineclient FROM ct_order a LEFT JOIN ct_user b ON b.uid=a.userid WHERE DATE_FORMAT(FROM_UNIXTIME(a.addTime),'%Y')='$year' AND DATE_FORMAT(FROM_UNIXTIME(a.addTime),'%m')='$month' AND a.orderstate!='' GROUP BY a.userid order by weight desc limit 0,10");
        foreach ($result as $key => $value) {
            if ($value['lineclient'] == '') {
                $uname = Db::table('ct_user')->where('uid',$value['customerName'])->find();
                $result[$key]['customerName'] = $uname['realname'];
            }else{
                $uname = Db::table('ct_company')->where('cid',$value['lineclient'])->find();
                $result[$key]['customerName'] = $uname['name'];
            }
        }
        return json_encode($result);
   }
   /*
   *申请提现总额，司机，用户年数据
   */
    public function countapplytotalyear(){

        $query=DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(start_time),'%Y') years,sum(money) count,
            (SELECT sum(money) FROM ct_application where DATE_FORMAT(FROM_UNIXTIME(start_time),'%Y')=years AND states=4 AND action_type=1 GROUP BY DATE_FORMAT(FROM_UNIXTIME(start_time),'%Y')) count0,
            (SELECT sum(money) FROM ct_application where DATE_FORMAT(FROM_UNIXTIME(start_time),'%Y')=years AND states=4 AND action_type=2 GROUP BY DATE_FORMAT(FROM_UNIXTIME(start_time),'%Y')) count1
            FROM ct_application where states=4 GROUP BY years");
        echo json_encode($query);
    }
   /*
   *申请提现总额，司机，用户月数据
   */
   public function countapplytotalmoth(){
        $year = isset($_POST["year"]) ? $_POST["year"] : '';
        $query=DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(start_time),'%m') months,DATE_FORMAT(FROM_UNIXTIME(start_time),'%Y') years,sum(money) count,
        (SELECT sum(money) FROM ct_application where DATE_FORMAT(FROM_UNIXTIME(start_time),'%Y')=".$year." AND DATE_FORMAT(FROM_UNIXTIME(start_time),'%m')=months AND states=4 AND action_type=1) count0,
        (SELECT sum(money) FROM ct_application where DATE_FORMAT(FROM_UNIXTIME(start_time),'%Y')=".$year." AND DATE_FORMAT(FROM_UNIXTIME(start_time),'%m')=months AND states=4 AND action_type=2) count1
        FROM ct_application where DATE_FORMAT(FROM_UNIXTIME(start_time),'%Y')=".$year." GROUP BY months");
        echo json_encode($query);
   }

   /*
   *申请提现总额，司机，用户年数据
   */
   public function countpaytotalyear(){

        $query=DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y') years,sum(paynum) count,
            (SELECT sum(paynum) FROM ct_paymessage where DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y')=years AND  type=2 GROUP BY DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y')) count0,
            (SELECT sum(paynum) FROM ct_paymessage where DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y')=years AND  type=1 GROUP BY DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y')) count1
            FROM ct_paymessage  GROUP BY years");
        echo json_encode($query);
   }
   /*
   *申请提现总额，司机，用户月数据
   */
   public function countpaytotalmoth(){
        $year = isset($_POST["year"]) ? $_POST["year"] : '';
        $query=DB::query("SELECT DATE_FORMAT(FROM_UNIXTIME(paytime),'%m') months,DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y') years,sum(paynum) count,
        (SELECT sum(paynum) FROM ct_paymessage where DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y')=".$year." AND DATE_FORMAT(FROM_UNIXTIME(paytime),'%m')=months AND type=2) count0,
        (SELECT sum(paynum) FROM ct_paymessage where DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y')=".$year." AND DATE_FORMAT(FROM_UNIXTIME(paytime),'%m')=months AND type=1) count1
        FROM ct_paymessage where DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y')=".$year." GROUP BY months");
    echo json_encode($query);
   }

   /*
   *下载量统计
   */
   public function countdown(){
       $t = time();
        $start_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));  //当天开始时间
        $end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t)); //当天结束时间
        $result = DB::table('ct_device')
                ->field('count(id) count,model')
                ->where('model','neq','')
                ->order('id','desc')
                ->group('model')
                ->paginate(10);
        $count = DB::table('ct_device')->where('model','neq','')->count('id');
        //统计今年客户下载数量
        $count_year = DB::query("SELECT count(id) as countyear FROM `ct_device` where model !=''  AND DATE_FORMAT(FROM_UNIXTIME(dow_time),'%Y')=DATE_FORMAT(FROM_UNIXTIME(".time()."),'%Y')");
        //统计今月客户下载数量
        $count_moth = DB::query("SELECT count(id) as countmoth FROM `ct_device` where model !=''  AND DATE_FORMAT(FROM_UNIXTIME(dow_time),'%m')=DATE_FORMAT(FROM_UNIXTIME(".time()."),'%m')");
        //统计当周客户下载数量
        $count_week = DB::query("SELECT count(id) as countweek FROM `ct_device` where model !=''  AND DATE_FORMAT(FROM_UNIXTIME(dow_time),'%w')=DATE_FORMAT(FROM_UNIXTIME(".time()."),'%w')");
        //统计当天客户下载数量
        $count_day = DB::query("SELECT count(id) as countday FROM `ct_device` where model !=''  AND $start_time < dow_time AND dow_time < $end_time");
        $count = array('countAll'=>$count,'countyear'=>$count_year[0]['countyear'],'countmoth'=>$count_moth[0]['countmoth'],'countweek'=>$count_week[0]['countweek'],'countday'=>$count_day[0]['countday']);
        //echo "<pre/>";
        //print_r($count);
        $page =  $result->render();
        $this->assign('count',$count);
        $this->assign('list',$result);
        $this->assign('page',$page);
        return view('charts/countdown');
    }

    /**
     * 业务员零担订单数量统计
     * @auther: 李渊
     * @date: 2018.8.20
     * 统计当年每月数量
     * @return [type] [description]
     */
    public function staticBulkOrder($business)
    {
        // 当年第一秒时间戳
        $yearFirstSeconds = strtotime(date("Y-01-01 00:00:00"));
        // 筛选条件 下单时间
        $where['addtime'] = ['EGT',$yearFirstSeconds];
        // 筛选条件 订单状态
        $where['orderstate'] = 7;
        // 获取零担已完成订单
        $bulkOrder = Db::table('ct_order')->where($where)->select();

        // 遍历订单数据
        foreach ($bulkOrder as $key => $value) {
            // 获取下单人
            $userid = $bulkOrder[$key]['userid'];
            // 查找下单人数据
            $useInfo = Db::table('ct_user')->where('uid',$userid)->find();
            // 判断下单人属性获取业务员  
            if ($useInfo['userstate'] == 1) {
                $businesName = $this->get_sharename($userid);
            } else {
                $businesName = $this->get_order_salesman($useInfo['lineclient'],$bulkOrder[$key]['addtime']);
            }
            
            // 遍历业务员
            foreach ($business as $key2 => $value2) {
                // 判断订单业务员与业务员是否一致
                if ($business[$key2]['name'] == $businesName) {
                    $m = date('n',$bulkOrder[$key]['addtime'])-1;
                    $business[$key2]['data'][$m] = $business[$key2]['data'][$m]+1;
                    $business[$key2]['money'][$m] = $business[$key2]['money'][$m]+$bulkOrder[$key]['linepice']+$bulkOrder[$key]['pickcost']+$bulkOrder[$key]['delivecost'];
                }
            }
        }

        return $business;
    }

    /**
     * 业务员整车订单数量统计
     * @auther: 李渊
     * @date: 2018.8.20
     * 统计当年每月数量
     * @return [type] [description]
     */
    public function staticUserorder($business)
    {
        // 当年第一秒时间戳
        $yearFirstSeconds = strtotime(date("Y-01-01 00:00:00"));
        // 筛选条件 下单时间
        $where['addtime'] = ['EGT',$yearFirstSeconds];
        // 筛选条件 订单状态
        $where['orderstate'] = 3;
        // 获取零担已完成订单
        $userOrder = Db::table('ct_userorder')->where($where)->select();
        // 遍历订单数据
        foreach ($userOrder as $key => $value) {
            // 获取下单人
            $userid = $userOrder[$key]['userid'];
            // 查找下单人数据
            $useInfo = Db::table('ct_user')->where('uid',$userid)->find();
            // 判断下单人属性获取业务员
            if ($useInfo['userstate'] == 1) {
                $businesName = $this->get_sharename($userid);
            } else {
                $businesName = $this->get_order_salesman($useInfo['lineclient'],$userOrder[$key]['addtime']);
            }
            // 遍历业务员
            foreach ($business as $key2 => $value2) {
                // 判断订单业务员与业务员是否一致
                if ($business[$key2]['name'] == $businesName) {
                    $m = date('n',$userOrder[$key]['addtime'])-1;
                    $business[$key2]['data'][$m] = $business[$key2]['data'][$m]+1;
                    $business[$key2]['money'][$m] = $business[$key2]['money'][$m]+$userOrder[$key]['actual_payment'];
                }
            }
        }

        return $business;
    }

    /**
     * 业务员城配订单数量统计
     * @auther: 李渊
     * @date: 2018.8.20
     * 统计当年每月数量
     * @return [type] [description]
     */
    public function staticCityorder($business)
    {
        // 当年第一秒时间戳
        $yearFirstSeconds = strtotime(date("Y-01-01 00:00:00"));
        // 筛选条件 下单时间
        $where1['addtime'] = ['EGT',$yearFirstSeconds];
        // 筛选条件 订单状态
        $where1['state'] = 3;
        // 获取零担已完成订单
        $cityOrder = Db::table('ct_city_order')->where($where1)->select();
        // 遍历订单数据
        foreach ($cityOrder as $key => $value) {

            // 获取下单人
            $userid = $cityOrder[$key]['userid'];
            // 查找下单人数据
            $useInfo = Db::table('ct_user')->where('uid',$userid)->find();
            // 判断下单人属性获取业务员
            if ($useInfo['userstate'] == 1) {
                $businesName = $this->get_sharename($userid);
            } else {
                $businesName = $this->get_order_salesman($useInfo['lineclient'],$cityOrder[$key]['addtime']);
            }
            // 遍历业务员
            foreach ($business as $key2 => $value2) {
                // 判断订单业务员与业务员是否一致
                if ($business[$key2]['name'] == $businesName) {
                    $m = date('n',$cityOrder[$key]['addtime'])-1;
                    $business[$key2]['data'][$m] = $business[$key2]['data'][$m]+1;
                    $business[$key2]['money'][$m] = $business[$key2]['money'][$m]+$cityOrder[$key]['actual_payment'];
                }
            }
        }
        return $business;
    }

    /**
     * 业务员定制订单数量统计
     * @auther: 李渊
     * @date: 2018.8.20
     * 统计当年每月数量
     * @return [type] [description]
     */
    public function staticShiftorder($business)
    {
        // 当年第一秒时间戳
        $yearFirstSeconds = strtotime(date("Y-01-01 00:00:00"));
        // 筛选条件 下单时间
        $where3['addtime'] = ['EGT',$yearFirstSeconds];
        // 筛选条件 订单状态
        $where3['orderstate'] = 3;
        // 获取零担已完成订单
        $shiftOrder = Db::table('ct_shift_order')->where($where3)->select();
        // 遍历订单数据
        foreach ($shiftOrder as $key => $value) {

            // 获取下单人
            $userid = $shiftOrder[$key]['userid'];
            // 查找下单人数据
            $useInfo = Db::table('ct_user')->where('uid',$userid)->find();
            // 判断下单人属性获取业务员
            if ($useInfo['userstate'] == 1) {
                $businesName = $this->get_sharename($userid);
            } else {
                $businesName = $this->get_order_salesman($useInfo['lineclient'],$shiftOrder[$key]['addtime']);
            }
            // 遍历业务员
            foreach ($business as $key2 => $value2) {
                // 判断订单业务员与业务员是否一致
                if ($business[$key2]['name'] == $businesName) {
                    $m = date('n',$shiftOrder[$key]['addtime'])-1;
                    $business[$key2]['data'][$m] = $business[$key2]['data'][$m]+1;
                    $business[$key2]['money'][$m] = $business[$key2]['money'][$m]+$shiftOrder[$key]['totalprice'];
                }
            }
        }

        return $business;
    }

    /**
     * 业务员订单数量统计
     * @auther: 李渊
     * @date: 2018.8.20
     * 柱状图
     * 统计当年每月每个业务员订单数量
     * @return [type] [description]
     */
    public function businessOrder()
    {
        // 获取平台业务员
        $busine = Db::table('ct_admin')->where('pstate',1)->select();

        $business = array();
        // 遍历业务员
        foreach ($busine as $key => $value) {
            $business[$key]['name'] = $value['realname'];
            $business[$key]['data'] = array('0','0','0','0','0','0','0','0','0','0','0','0');
            $business[$key]['money'] = array('0','0','0','0','0','0','0','0','0','0','0','0');
        }
        // 获取订单数量
        $business = $this->staticBulkOrder($business);
        $business = $this->staticUserorder($business);
        $business = $this->staticCityorder($business);
        $business = $this->staticShiftorder($business);
        // 定义结果集
        $retult = array();
        // 遍历数据获取订单总数量 
        foreach ($business as $k => $v) {
            // 默认订单数为0
            $allOrder = 0;
            // 获取单个业务员总的订单数
            foreach ($business[$k]['data'] as $i => $s) {
                $allOrder = $allOrder + $s;
            }
            // 判断总订单数
            if($allOrder != '0'){
                $r['data'] = $v['data'];
                $r['type'] = 'bar';
                $r['name'] =  $v['name'];
                array_push($retult,$r);
            }
        }
        echo json_encode($retult);
    }

    /**
     * 业务员订单数量统计
     * @auther: 李渊
     * @date: 2018.8.20
     * 饼图
     * 统计当年每月每个业务员订单数量
     * @return [type] [description]
     */
    public function businessOrderPie()
    {
        // 获取平台业务员
        $busine = Db::table('ct_admin')->where('pstate',1)->select();

        $business = array();
        // 遍历业务员
        foreach ($busine as $key => $value) {
            $business[$key]['name'] = $value['realname'];
            $business[$key]['data'] = array('0','0','0','0','0','0','0','0','0','0','0','0');
            $business[$key]['money'] = array('0','0','0','0','0','0','0','0','0','0','0','0');
        }
        // 获取订单数量
        $business = $this->staticBulkOrder($business);
        $business = $this->staticUserorder($business);
        $business = $this->staticCityorder($business);
        $business = $this->staticShiftorder($business);
        // 定义结果集
        $retult = array();
        // 遍历数据获取订单总数量 
        foreach ($business as $k => $v) {
            $result_val = 0;

            foreach ($business[$k]['data'] as $i => $s) {
                $result_val = $result_val + $s;
            }

            if($result_val != '0'){
                $r['value'] = $result_val;
                $r['name'] =  $v['name'];
                array_push($retult,$r);
            }
        }
        echo json_encode($retult);
    }

    /**
     * 业务员订单金额统计
     * @auther: 李渊
     * @date: 2018.8.20
     * 饼图
     * 统计当年每月每个业务员订单金额
     * @return [type] [description]
     */
    public function businessMoneyPie()
    {
        // 获取平台业务员
        $busine = Db::table('ct_admin')->where('pstate',1)->select();

        $business = array();
        // 遍历业务员
        foreach ($busine as $key => $value) {
            $business[$key]['name'] = $value['realname'];
            $business[$key]['data'] = array('0','0','0','0','0','0','0','0','0','0','0','0');
            $business[$key]['money'] = array('0','0','0','0','0','0','0','0','0','0','0','0');
        }
        // 获取订单数量
        $business = $this->staticBulkOrder($business);
        $business = $this->staticUserorder($business);
        $business = $this->staticCityorder($business);
        $business = $this->staticShiftorder($business);
        // 定义结果集
        $retult = array();
        // 遍历数据获取订单总数量 
        foreach ($business as $k => $v) {
            $result_val = 0;

            foreach ($business[$k]['money'] as $i => $s) {
                $result_val = $result_val + $s;
            }

            if($result_val != '0'){
                $r['value'] = $result_val;
                $r['name'] =  $v['name'];
                array_push($retult,$r);
            }
        }
        echo json_encode($retult);
    }
}