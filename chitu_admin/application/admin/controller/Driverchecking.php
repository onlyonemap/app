<?php
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;

class Driverchecking extends Base{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//    }
    /*
    *承运商首次对账页面
    */
    public function index(){
    	//统计公司个数
        $comTotal = array();
        //储存查询结果集
        $arr = array();
    	//用户未对过账状态
    	$where_data['drivercheck'] = '1';
        $search = Request::instance()->get();
        $pageParam    = ['query' =>[]];
        if (!empty($search['company'])) {
            $where_data['c.name|a.ordernumber|ucom.name'] = ['like','%'.$search['company'].'%'];
            $pageParam['query']['company'] = $search['company'];
        }
        if (!empty($search['starttime']) && !empty($search['endtime'])) {
            $endtime = strtotime(trim($search['endtime']).'23:59:59');
            $starttime = strtotime(trim($search['starttime']).'00:00:00');
            $where_data['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] = $search['starttime'];
            $pageParam['query']['endtime'] = $search['endtime'];
        }
        //统计订单个数

        $result = Db::table('ct_account_order')
                    ->alias('a')
                    ->join('ct_company c','c.cid = a.driver_companyid')
                    ->join('ct_user u','u.uid = a.userid')
                    ->join('ct_company ucom','ucom.cid = u.lineclient','LEFT')
                    ->field('a.addtime,a.driver_companyid,c.name as dcompany,ucom.name,a.ordernumber,a.userid,a.orderid,a.otype')
                    ->where($where_data)
                    ->order('addtime desc')
                    ->paginate(80,false,$pageParam);
        $count_result = Db::table('ct_account_order')
					->alias('a')
					->join('ct_company c','c.cid = a.driver_companyid')
					->join('ct_user u','u.uid = a.userid')
            		->join('ct_company ucom','ucom.cid = u.lineclient','LEFT')
					->where($where_data)
					->count('id');
        $result_data = $result->toArray();
        foreach ($result_data['data'] as $key => $value) {
            $comTotal[] = $value['driver_companyid'];  //统计公司个数
            $arr[$key]['ordernumber'] = $value['ordernumber']; //订单编号
            $arr[$key]['orderid'] = $value['orderid']; //订单ID
            $arr[$key]['ostate'] = $value['otype']; //订单类型1零担2定制3城配4整车
            $arr[$key]['name'] = $value['dcompany']; //公司名称
            $arr[$key]['addtime'] = $value['addtime']; //下单时间
            $arr[$key]['companyid'] = $value['driver_companyid']; //公司ID
        	$arr[$key]['clinemess'] = $this->cline_mess($value['userid']); //下单人信息
            //获取订单线路，门店数，总运费，提货费，干线费，配送费
            $return_mess = $this->account_mess($value['otype'],$value['orderid'],'2');
            $arr[$key]['line'] = $return_mess['line'];  //线路
            $arr[$key]['doornum'] = $return_mess['doornum']; //门店数
            $arr[$key]['tprice'] = $return_mess['pick_price']; //提货费
            $arr[$key]['linepice'] = $return_mess['line_price']; //干线费
            $arr[$key]['delivecost'] = $return_mess['send_price']; //配送费
            $arr[$key]['totalweight'] = $return_mess['total_weight']; //重量
            $arr[$key]['totalvolume'] = $return_mess['total_volume']; //体积
            $arr[$key]['countcoat'] = $return_mess['total_price']; //总运费
        }
        $page = $result->render();
        //去除重复公司
        $comNum = array_unique($comTotal);
        $this->assign('page',$page);
        $this->assign('list',$arr);
        $this->assign('comnum',count($comNum));  //统计公司数
        $this->assign('ordercount',$count_result); //统计订单数
        return view('driverchecking/index');
    }
     /*
    *添加销账记录
    */
    public function addinvo(){
    	//1零担，2定制 ，3城配 4整车
        $post_data=Request::instance()->post();
        $batch ='';
        $price_shift = 0;  //定制
        $price_order = 0;  //零担
        $price_city = 0;  //城配
        $price_car = 0;  //整车
        $order_arr = $post_data['order'];
        $ostate = $post_data['ostate'];
        $shift_str = array(); //定制
        $order_str = array(); //零担
        $city_str = array(); //市配
        $car_str = array(); //整车
        $i=0;
        foreach ($order_arr as $key => $value) {
                $arr[$i]['b'] = $order_arr[$key];  //订单ID
                $arr[$i]['a'] = $ostate[$key];  //订单状态 1零担，2定制 ，3城配 4整车
                $i++;
        }// end foreach
        foreach ($arr as $key => $info) {
            if ($info['a'] == '2') {  //定制
              $shift_str[]= $info['b'];
            }
            if ($info['a'] == '1') {  //零担
              $order_str[] = $info['b'];
            }
            if ($info['a'] == '3') {  //市配
              $city_str[] = $info['b'];
            }
            if ($info['a'] == '4') {  //整车
              $car_str[] = $info['b'];
            }
        }// end foreach
        if (!empty($order_str)) { //零担
            foreach ($order_str as $key => $value) {
                $return_order_mess = $this->account_mess('1',$value,'2');
                $price_order += $return_order_mess['total_price'];
            }
        } // end if 
        if (!empty($shift_str)) { //定制
            foreach ($shift_str as $key => $value) {
                $return_shift_mess = $this->account_mess('2',$value,'2');
                $price_shift += $return_shift_mess['total_price'];
            }
        }// end if 
        if (!empty($city_str)) { //城配
            foreach ($city_str as $key => $value) {
                $return_city_mess = $this->account_mess('3',$value,'2');
                $price_city += $return_city_mess['total_price'];
            }
        }// end if 
        if (!empty($car_str)) { //整车
            foreach ($car_str as $key => $value) {
                $return_car_mess = $this->account_mess('4',$value,'2');
                $price_car += $return_car_mess['total_price'];
            }
        }// end if 
        $total = $price_order+$price_shift+$price_city+$price_car;
        $company = Db::field('type')->table('ct_company')->where('cid',$post_data['companyid'][0])->find();
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
       	$account_data['drivercheck'] = 2;
        $account_data['in_driver'] = $insert_data;
        if (!empty($order_str)) { //零担
        	$line_data['checkyesno'] = 2;
	        $line_data['line_checkid']  = $insert_data;
	        $pick_data['checkyesno'] = 2;
	        $pick_data['pic_checkid']  = $insert_data;
        	$order_id = implode(',', $order_str);
        	$update_line = DB::table('ct_lineorder')->where('orderid','IN',$order_id)->update($line_data);
            $update_pick = DB::table('ct_pickorder')->where('orderid','IN',$order_id)->update($pick_data);
        	DB::table('ct_account_order')->where(array('otype'=>1,'orderid'=>['IN',$order_id]))->update($account_data);
        }// end if
        $data['checkyesno'] = 2;
        $data['carr_checkid'] = $insert_data;
        if (!empty($shift_str)) {
        	$shift_id = implode(',', $shift_str);
        	Db::table('ct_shift_order')->where('s_oid','IN',$shift_id)->update($data);
        	DB::table('ct_account_order')->where(array('otype'=>2,'orderid'=>['IN',$shift_id]))->update($account_data);
        }//end if
        if(!empty($city_str)){ //市配
            $city_id = implode(',', $city_str);
            Db::table('ct_city_order')->where('id','IN',$city_id)->update($data);
            DB::table('ct_account_order')->where(array('otype'=>3,'orderid'=>['IN',$city_id]))->update($account_data);
        }//end if
        if(!empty($car_str)){ //整车
            $car_id = implode(',', $car_str);
            Db::table('ct_userorder')->where('uoid','IN',$car_id)->update($data);
            DB::table('ct_account_order')->where(array('otype'=>4,'orderid'=>['IN',$car_id]))->update($account_data);
        }
        if ($insert_data) {
            $result['code'] = true;
            $result['message'] = '添加成功';
        }else{
            $result['code'] = false;
            $result['message'] = '添加失败';
        }
        echo json_encode($result);
    }

    /*
    *账单页面
    */
    public function carrcheck(){
    	//统计公司个数
        $comTotal = array();
        //储存查询结果集
        $arr = array();
        //储存对账ID
        $arrInvoID = array();
        //储存不同对账ID下的信息
        $messInvo = array();
        //获取搜索内容
        $search = Request::instance()->get();
        //定义分页搜索参数
        $pageParam    = ['query' =>[]];
        //按公司，订单编号搜索
        if (!empty($search['company'])) {
            $where_data['c.name|a.ordernumber|ucom.name'] = ['like','%'.$search['company'].'%'];
            $pageParam['query']['company'] = $search['company'];
        }
        //按对账月份搜索
        if (!empty($search['sermonth'])) {
            $sermonth = strtotime(trim($search['sermonth']));
            $where_data['inv.sermonth'] = ['EQ',$sermonth];
            $pageParam['query']['sermonth'] =$search['sermonth'];
        }
        //发票号
        $where_data['inv.Invoiceno'] = '';
        $where_data['a.drivercheck'] = 2;
        //统计订单个数
        $count_result = Db::table('ct_account_order')
                            ->alias('a')
                            ->join('ct_company c','c.cid = a.driver_companyid')
                            ->join('ct_invoice inv','inv.iid = a.in_driver')
                            ->join('ct_user u','u.uid = a.userid')
                    		->join('ct_company ucom','ucom.cid = u.lineclient','LEFT')
                            ->where($where_data)
                            ->count('id');
        $result = Db::table('ct_account_order')
                    ->alias('a')
                    ->join('ct_company c','c.cid = a.driver_companyid')
                    ->join('ct_invoice inv','inv.iid = a.in_driver')
                    ->join('ct_user u','u.uid = a.userid')
                    ->join('ct_company ucom','ucom.cid = u.lineclient','LEFT')
                    ->field('a.addtime,a.driver_companyid,a.userid,a.drivercheck,a.in_driver,c.name dcompany,ucom.name,a.ordernumber,a.orderid,a.otype,inv.usertype,inv.confirm,inv.self_total,inv.self_remark,inv.sermonth')
                    ->where($where_data)
                    ->order('addtime desc')
                    ->paginate(80,false,$pageParam);
        $result_data = $result->toArray();
        foreach ($result_data['data'] as $key => $value) {
            $comTotal[] = $value['driver_companyid'];  //统计公司个数
            $arrInvoID[] = $value['in_driver'];  //存储对账ID
            $messInvo[$value['in_driver']]['confirm'] = $value['confirm'];  //发起对账 客户是否确认对账
            $messInvo[$value['in_driver']]['self_total'] = $value['self_total']; //对账订单金额
            $messInvo[$value['in_driver']]['self_remark'] = $value['self_remark']; //对账备注信息
            $messInvo[$value['in_driver']]['sermonth'] = $value['sermonth'];  //对账月份
            $arr[$key]['invoID'] = $value['in_driver']; //对账单ID
            $arr[$key]['ordernumber'] = $value['ordernumber']; //订单编号
            $arr[$key]['orderid'] = $value['orderid']; //订单ID
            $arr[$key]['ostate'] = $value['otype']; //订单类型1零担2定制3城配4整车
            $arr[$key]['name'] = $value['dcompany']; //公司名称
            $arr[$key]['addtime'] = $value['addtime']; //下单时间
            $arr[$key]['companyid'] = $value['driver_companyid']; //公司ID
        	$arr[$key]['clinemess'] = $this->cline_mess($value['userid']); //下单人信息
            //获取订单线路，门店数，总运费，提货费，干线费，配送费
            $return_mess = $this->account_mess($value['otype'],$value['orderid'],'2');
            $arr[$key]['line'] = $return_mess['line'];  //线路
            $arr[$key]['doornum'] = $return_mess['doornum']; //门店数
            $arr[$key]['tprice'] = $return_mess['pick_price']; //提货费
            $arr[$key]['linepice'] = $return_mess['line_price']; //干线费
            $arr[$key]['delivecost'] = $return_mess['send_price']; //配送费
            $arr[$key]['totalweight'] = $return_mess['total_weight']; //重量
            $arr[$key]['totalvolume'] = $return_mess['total_volume']; //体积
            $arr[$key]['countcoat'] = $return_mess['total_price']; //总运费
        }
        //去除重复对账ID
        $count_InvoID = array_unique($arrInvoID);
        //降维并保留键名处理
        $oneArrInvo = array();
        if (count($messInvo) =='1') {
            foreach ($messInvo as $b) {
                $oneArrInvo = $b;
            }
        }
        if (count($count_InvoID) > 1) {  //当有多个ID时页面显示提示信息
            $this->assign('countarr','listtwo');
        }else{  //一个对账是页面显示对账详细信息
            $this->assign('countarr','listone');
            $this->assign('listcount',$oneArrInvo);
        }
        //去除重复公司
        $comNum = array_unique($comTotal);
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$arr);
        $this->assign('comnum',count($comNum));  //统计公司数
        $this->assign('ordercount',$count_result); //统计订单数
        return view('driverchecking/carrcheck');
    }

    /*
    *
    *开票页面
    */
    public function upcheck(){
      $getid = Request::instance()->get();
      $invo = Db::table('ct_invoice')->where('iid',$getid['id'])->find();
      if ($invo['self_total'] =='') {
        $invo['checktic'] = $invo['totalprice'];
      }else{
        $invo['checktic'] = $invo['self_total'];
      }

      $this->assign('list',$invo);
      return view('driverchecking/upcheck');
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

    /*
    *修改总价
    */
    public function update(){
        $getid = Request::instance()->get();
        $invo = Db::table('ct_invoice')->where('iid',$getid['id'])->find();
        $this->assign('list',$invo);
        return view('driverchecking/update');
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
    * @param  [int] $orderID  订单ID
    * @param  [int] $invoID   对账ID
    * @param  [int] $ostate   账单类型 1、零担 2、定制 3、城配 4、整车
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
        $shift_str = array(); //定制
        $order_str = array();  // 零担
        $city_str = array();  // 城配
        $car_str = array();  // 整车
        $array = array();
        $i=0;
        foreach ($orderid as $key => $value) {
            $array[$i]['b']= $orderid[$key];
            $array[$i]['a']= $otype[$key];
            $i++;
        }
        foreach ($array as $key => $info) {
            if ($info['a'] == '2') {  //定制
              $shift_str[]= $info['b'];
            }
            if ($info['a'] == '1') {  //零担
              $order_str[] = $info['b'];
            }
            if ($info['a'] == '3') {  //市配
              $city_str[] = $info['b'];
            }
            if ($info['a'] == '4') {  //整车
              $car_str[] = $info['b'];
            }
        }
        if (!empty($order_str)) { //零担
            foreach ($order_str as $key => $value) {
                $return_order_mess = $this->account_mess('1',$value,'2');
                $get_price_order += $return_order_mess['total_price'];
            }
        }
        if (!empty($shift_str)) { //定制
            foreach ($shift_str as $key => $value) {
                $return_shift_mess = $this->account_mess('2',$value,'2');
                $get_price_shift += $return_shift_mess['total_price'];
            }
        }
        if (!empty($city_str)) { //城配
            foreach ($city_str as $key => $value) {
                $return_city_mess = $this->account_mess('3',$value,'2');
                $get_price_city += $return_city_mess['total_price'];
            }
        }
        if (!empty($car_str)) { //整车
             foreach ($car_str as $key => $value) {
                $return_car_mess = $this->account_mess('4',$value,'2');
                $get_price_car += $return_car_mess['total_price'];
            }
        }
        //删除确认订单的总额
        $get_price = $get_price_order+$get_price_shift+$get_price_city+$get_price_car;
        //查账改对账下的信息
        $invo_arr = DB::table('ct_invoice')->where('iid',$invo_id)->find(); 
        $subcost = (int)$invo_arr['totalprice'] - (int)$get_price;
        $invo_data['totalprice'] = $subcost;
        //是否有平台修改后的总额
        if ($invo_arr['self_total'] !='') {
            $invo_data['self_total'] = (int)$invo_arr['self_total'] - (int)$get_price;
        }
        if($subcost <= 0) {
            $del_data = Db::table('ct_invoice')->delete($invo_id);
        }else{
            $del_data = Db::table('ct_invoice')->where('iid',$invo_id)->update($invo_data);
        }
        $order_data['checkyesno'] = 1;
        $order_data['carr_checkid'] = ' ';
        $account_data['drivercheck'] = 1;
        $account_data['in_driver'] = '';
        if (!empty($order_str)) { //零担
        	$line_data['checkyesno'] = 1;
            $line_data['line_checkid']  = '';
            $pick_data['checkyesno'] = 1;
            $pick_data['pic_checkid']  = '';
            $order_id = implode(',', $order_str);
            //$data = DB::table('ct_order')->where('oid','IN',$order_id)->update($order_data);
            DB::table('ct_lineorder')->where('orderid','IN',$order_id)->update($line_data);
            DB::table('ct_pickorder')->where('orderid','IN',$order_id)->update($pick_data);
            DB::table('ct_account_order')->where(array('otype'=>1,'orderid'=>['IN',$order_id]))->update($account_data);
        }
        if(!empty($shift_str)){ //定制
            $shift_id = implode(',', $shift_str);
            $data = DB::table('ct_shift_order')->where('s_oid','IN',$shift_id)->update($order_data);
             DB::table('ct_account_order')->where(array('otype'=>2,'orderid'=>['IN',$shift_id]))->update($account_data);
        }
        if(!empty($city_str)){ //城配
            $city_id = implode(',', $city_str);
            $data = DB::table('ct_city_order')->where('id','IN',$city_id)->update($order_data);
            DB::table('ct_account_order')->where(array('otype'=>3,'orderid'=>['IN',$city_id]))->update($account_data);
        }
        if(!empty($car_str)){ //整车
            $car_id = implode(',', $car_str);
            $data = DB::table('ct_userorder')->where('uoid','IN',$car_id)->update($order_data);
            DB::table('ct_account_order')->where(array('otype'=>4,'orderid'=>['IN',$car_id]))->update($account_data);
        }
        if ($del_data) {
            $result['code'] = true;
            $result['message'] = '删除成功';
        }else{
            $result['code'] = false;
            $result['message'] = '删除失败';
        }
        echo json_encode($result);
    }

    /*
	*
	*承运商销账页面s
    */
    public function writeoff(){
    	//统计公司个数
        $comTotal = array();
        //储存查询结果集
        $arr = array();
        //储存对账ID
        $arrInvoID = array();
        //储存不同对账ID下的信息
        $messInvo = array();
        //获取搜索内容
        $search = Request::instance()->get();
        //定义分页搜索参数
        $pageParam    = ['query' =>[]];
        //按公司，订单编号搜索
        if (!empty($search['company'])) {
            $where_data['c.name|a.ordernumber|ucom.name'] = ['like','%'.$search['company'].'%'];
            $pageParam['query']['company'] = $search['company'];
        }

        //按对账月份搜索
        if (!empty($search['sermonth'])) {
            $sermonth = strtotime(trim($search['sermonth']));
            $where_data['inv.sermonth'] = ['EQ',$sermonth];
            $pageParam['query']['sermonth'] =$search['sermonth'];
        }
        //发票号搜索
        if (!empty($search['invonumber'])){
            $where_data['inv.Invoiceno'] = ['like',$search['invonumber'].'%'];
            $pageParam['query']['invonumber'] = $search['invonumber'];
        }
        
        $where_data['inv.instate'] = 2;
        $where_data['inv.usertype'] = 1;
        //统计订单个数
        $count_result = Db::table('ct_account_order')
                            ->alias('a')
                            ->join('ct_company c','c.cid = a.driver_companyid')
                            ->join('ct_invoice inv','inv.iid = a.in_driver')
                            ->join('ct_user u','u.uid = a.userid')
                    		->join('ct_company ucom','ucom.cid = u.lineclient','LEFT')
                            ->where($where_data)
                            ->count('id');
        $result = Db::table('ct_account_order')
                    ->alias('a')
                    ->join('ct_company c','c.cid = a.driver_companyid')
                    ->join('ct_invoice inv','inv.iid = a.in_driver')
                    ->join('ct_user u','u.uid = a.userid')
                    ->join('ct_company ucom','ucom.cid = u.lineclient','LEFT')
                    ->field('a.addtime,a.driver_companyid,a.userid,a.drivercheck,a.in_driver,c.name dcompany,ucom.name,a.ordernumber,a.orderid,a.otype,inv.paytime,inv.Invoiceamount,inv.Invoiceno,inv.unpass,inv.sermonth')
                    ->where($where_data)
                    ->order('addtime desc')
                    ->paginate(80,false,$pageParam);
        $result_data = $result->toArray();
        foreach ($result_data['data'] as $key => $value) {
            $comTotal[] = $value['driver_companyid'];  //统计公司个数
            $arrInvoID[] = $value['in_driver'];  //存储对账ID
            $messInvo[$value['in_driver']]['sermonth'] = $value['sermonth'];  //对账月份
            $messInvo[$value['in_driver']]['Invoiceno'] = $value['Invoiceno'];  //发票号
            $messInvo[$value['in_driver']]['paytime'] = $value['paytime'];
            $messInvo[$value['in_driver']]['unpass'] = $value['unpass'];
            $messInvo[$value['in_driver']]['Invoiceamount'] = $value['Invoiceamount'];  //发票金额
            $arr[$key]['invoID'] = $value['in_driver']; //对账单ID
            $arr[$key]['ordernumber'] = $value['ordernumber']; //订单编号
            $arr[$key]['orderid'] = $value['orderid']; //订单ID
            $arr[$key]['ostate'] = $value['otype']; //订单类型1零担2定制3城配4整车
            $arr[$key]['name'] = $value['dcompany']; //公司名称
            $arr[$key]['addtime'] = $value['addtime']; //下单时间
            $arr[$key]['companyid'] = $value['driver_companyid']; //公司ID
        	$arr[$key]['clinemess'] = $this->cline_mess($value['userid']); //下单人信息
        	$arr[$key]['unpass'] = $value['unpass']; //是否销账 1未销账 2已销账
            //获取订单线路，门店数，总运费，提货费，干线费，配送费
            $return_mess = $this->account_mess($value['otype'],$value['orderid'],'2');
            $arr[$key]['line'] = $return_mess['line'];  //线路
            $arr[$key]['doornum'] = $return_mess['doornum']; //门店数
            $arr[$key]['tprice'] = $return_mess['pick_price']; //提货费
            $arr[$key]['linepice'] = $return_mess['line_price']; //干线费
            $arr[$key]['delivecost'] = $return_mess['send_price']; //配送费
            $arr[$key]['totalweight'] = $return_mess['total_weight']; //重量
            $arr[$key]['totalvolume'] = $return_mess['total_volume']; //体积
            $arr[$key]['countcoat'] = $return_mess['total_price']; //总运费
        }
       //去除重复对账ID
        $count_InvoID = array_unique($arrInvoID);
        //降维并保留键名处理
        $oneArrInvo = array();
        //发票号
        $strInvo = '';
        if (count($messInvo) =='1') {
            foreach ($messInvo as $b) {
                $oneArrInvo = $b;
            }
        }else{
           foreach ($messInvo as $key => $value) {
              $strInvo .= $value['Invoiceno'] .' / ';
           }
        }
        if (count($count_InvoID) > 1) {  //当有多个ID时页面显示提示信息
            $this->assign('countarr','listtwo');
            $this->assign('listcount',rtrim($strInvo,' / '));
            $this->assign('listnum',count($oneArrInvo));
        }else{  //一个对账是页面显示对账详细信息
            $this->assign('countarr','listone');
            $this->assign('listcount',$oneArrInvo);
        }
        //去除重复公司
        $comNum = array_unique($comTotal);
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$arr);
        $this->assign('comnum',count($comNum));  //统计公司数
        $this->assign('ordercount',$count_result); //统计订单数
        return view('driverchecking/writeoff');
    }

    /*
    *财务销账
    * @param  [int] $orderID  订单ID
    * @param  [int] $invoID   对账ID
    * @param  [int] $ostate   账单类型 1、零担 2、定制 3、城配 4、整车
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
	        $count_car = Db::table('ct_userorder')->where('carr_checkid',$id)->sum('price');
	        $total = $count_order_line+$count_order_pick+$count_order_send+$count_shift+$count_city+$count_car;
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
	        Db::table('ct_account_order')->where('in_driver',$id)->update(array('drivercheck'=>3));
	    }
	    if ($up){
            $result['code'] = true;
            $result['message'] = '销账成功';
        }else{
            $result['code'] = false;
            $result['message'] = '销账失败';
        }
	}



}