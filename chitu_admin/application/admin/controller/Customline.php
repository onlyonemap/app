<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Customline extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//
//    }
    // 定制客户公司列表
    public function companylist() {
        $search = input('search');
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
           $carriers_where['a.realname|b.name'] = ['like','%'.$search.'%'];
           $pageParam['query']['search'] = $search;
        }
        // 用户状态为 2 项目客户 
        $carriers_where['a.userstate'] = 2;
        // 用户状态为 1 管理员
        $carriers_where['a.user_grade'] =1;
        // 用户状态为 1 开通
        $carriers_where['a.delstate'] = 1;
        // 公司状态为 1 开通
        $carriers_where['b.status'] = 1;
        $carriers_where['b.customer'] = 2;
        // 通过公司管理员查找公司
        $select  = DB::field('a.*,b.name,b.cid')
                    ->table('ct_user')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.lineclient')
                    ->where($carriers_where)
                    ->order('a.uid','desc')
                    ->paginate(10,false, $pageParam);
        $company =  $select->toArray(); 
        $arr = array();         
        foreach ($company['data'] as $key => $value) {
            $arr[$key] = $value;
            $cid = $value['cid'];
            $linarr = array(); 
            $linarr = DB::field('a.*,d.name as carr_company,c.carparame')
                    ->table('ct_fixation_line')
                    ->alias('a')
                    ->join('ct_company d','d.cid=a.carrierid','left')
                    ->join('ct_cartype c','c.car_id = a.carid','left')  
                    ->where(array('companyid'=>$cid,'a.shiftstate'=>'1'))
                    ->select();

            // 始发地址和终点地址
            foreach ($linarr as $k => $v) {
                $startStr = '';
                $endStr = '';
                $linecity = Db::table('ct_already_city')->where('city_id',$v['lienid'])->find();
                // 
                $startid = $linecity['start_id'];
                $endid = $linecity['end_id'];

                $sarr = Db::table('ct_district')->where('id',$startid)->find();

                switch ($sarr['level']) {
                    case 1: // 省
                        $startStr = $sarr['neme'];
                        break;
                    case 2: // 市

                        $spro = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                        if ($sarr['id'] =='45054' || $sarr['id'] =='45055'|| $sarr['id'] =='45052'|| $sarr['id'] =='45053') {
                              $startStr = $sarr['name'];
                        }else{
                             $startStr = $spro['name'].$sarr['name'];
                        }
                      
                        break;
                    default: // 区
                        $scity = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                        $spro = Db::table('ct_district')->where('id',$scity['parent_id'])->find();
                        $startStr = $spro['name'] . $scity['name'] . $sarr['name'];
                        break;
                }
                $earr = Db::table('ct_district')->where('id',$endid)->find();
                switch ($earr['level']) {
                    case 1: // 省
                        $endStr = $earr['name'];
                        break;
                    case 2: // 市
                        $epro = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                        if ($earr['id'] =='45054' || $earr['id'] =='45055'|| $earr['id'] =='45052'|| $earr['id'] =='45053') {
                              $endStr = $earr['name'];
                        }else{
                             $endStr = $epro['name'].$earr['name'];
                        }
                        
                        break;
                    default: // 区
                        $ecity = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                        $epro = Db::table('ct_district')->where('id',$ecity['parent_id'])->find();

                        $endStr = $epro['name'] . $ecity['name'] . $earr['name'];
                        break;
                }
                $linarr[$k]['startname'] = $startStr;
                $linarr[$k]['endname'] = $endStr;
                $linarr[$k]['lineid'] = $v['lienid'];
            }
            $arr[$key]['line'] = $linarr;
        }  
       
        $page =  $select->render();
        $this->assign('page',$page);
        $this->assign('list',$arr);
        return view('customline/companylist');
    }
    // 跳转定制客户添加页面
    public function companyadd(){
        $where['pstate'] = 1; // 平台员工在线
        $result = DB::table('ct_admin')->where($where)->select();
        $this->assign('list',$result);
        return view('customline/companyadd');
    }
    // 添加定制客户
    public function companyaddmssage(){
        $postdate = Request::instance()->post();
        // company 公司名称
        $com_data['name'] = $postdate['name'];
        // company 公司地址
        if ($postdate['province'] !='0') {
            $com_data['provinceid'] = $postdate['province'];
            $com_data['cityid'] = $postdate['city'];
            $com_data['areaid'] = $postdate['area'];
            $com_data['address'] = $postdate['addinfo'];
        }
        // company 公司类型 1干线2提货3项目
        $com_data['type'] = 3;
        // company 公司类型 1非定制2定制
        $com_data['customer'] = 2;
        if ($postdate['company_id'] !='') {
            $search = Db::table('ct_user')->where('uid',$postdate['userid'])->find();
            if ($search['custom']==2) {
                $result['data']['message'] = 'yes';
                $result['code'] = false;
                echo json_encode($result);
                exit();
            }
            $com_id = $postdate['company_id'];
            Db::table('ct_company')->where('cid',$com_id)->update($com_data);
        }else{
            // company 添加时间
            $com_data['addtime'] = time();
            $com_id = DB::table('ct_company')->insertGetId($com_data);
        }
        // 公司id
        $add_data['lineclient'] = $com_id;
        // 用户名
        $add_data['username'] = $postdate['username'];
        // user 真实姓名
        $add_data['realname'] = $postdate['realname'];
        // user 手机号
        $add_data['phone'] = $postdate['phone'];
        
        // user 1管理2业务
        $add_data['user_grade'] = 1;
       
        // user 用户类型 userstate 1注册2项目3撮合
        $add_data['userstate'] = 2;
        // user 定制类型 custom 1非定制2定制
        $add_data['custom'] = 2;
        if ($postdate['userid'] !='') {
            $issuccess = Db::table('ct_user')->where('uid',$postdate['userid'])->update($add_data);
            DB::table('ct_user')->where('lineclient',$com_id)->update(array('custom'=>'2'));
        }else{
            // user 性别
            $add_data['sex'] = $postdate['sex'];
             // user 原始密码
            $add_data['password'] = md5('666666ct888');
            // user 添加时间
            $add_data['addtime'] = time();
            $issuccess = DB::table('ct_user')->insert($add_data);
        }
        

        // 业务员业务添加
        $business['aid'] = $postdate['adminid'];
        $business['sort'] = 1;
        $business['starttime'] = time();
        $business['cid'] = $add_data['lineclient'];
        $isadd = DB::table("ct_business")->insert($business);

        if($issuccess){ 
            $result['code'] = true;
            $result['data']['cid'] = $add_data['lineclient'];
            echo json_encode($result);
        }else{
            $result['data']['message'] = 'no';
            $result['code'] = false;
            echo json_encode($result);
        }
    }
    // 跳转添加定制页面
    public function addline(){ 
        $get = Request::instance()->get();
        $this->assign('list',$get);    
        return view('customline/addline');
    }

    // 专线列表
    public function index(){
        $search = input('search');
    	
        $where['b.status'] = 1;
        $where['a.shiftstate'] = 1;
        $pageParam    = ['query' =>[]];
        if (!empty($search)) {
            $where['b.name'] = ['like','%'. $search.'%'];
            $pageParam['query']['search'] = $search;
        }

        $result = DB::field('a.*,b.name,b.type,d.name as carr_company')
                    ->table('ct_fixation_line')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.companyid')
                    ->join('ct_company d','d.cid=a.carrierid','left')
                    ->where($where)
                    ->order('id desc')
                    ->paginate(10,false, $pageParam);
        $page = $result->render();

        $resultdata = $result->toArray(); 
        $resultarr = array();
        // 始发地址和终点地址
        foreach ($resultdata['data'] as $k => $v) {
            $resultarr[$k] = $v;
            
            $startStr = '';
            $endStr = '';
            $linecity = Db::table('ct_already_city')->where('city_id',$v['lienid'])->find();
            // 
            $startid = $linecity['start_id'];
            $endid = $linecity['end_id'];

            $sarr = Db::table('ct_district')->where('id',$startid)->find();

            switch ($sarr['level']) {
                case 1: // 省
                    $startStr = $sarr['neme'];
                    break;
                case 2: // 市

                    $spro = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                    if ($sarr['id'] =='45054' || $sarr['id'] =='45055'|| $sarr['id'] =='45052'|| $sarr['id'] =='45053') {
                          $startStr = $sarr['name'];
                    }else{
                         $startStr = $spro['name'].$sarr['name'];
                    }
                  
                    break;
                default: // 区
                    $scity = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                    $spro = Db::table('ct_district')->where('id',$scity['parent_id'])->find();
                    $startStr = $spro['name'] . $scity['name'] . $sarr['name'];
                    break;
            }
            $earr = Db::table('ct_district')->where('id',$endid)->find();
            switch ($earr['level']) {
                case 1: // 省
                    $endStr = $earr['name'];
                    break;
                case 2: // 市
                    $epro = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                    if ($earr['id'] =='45054' || $earr['id'] =='45055'|| $earr['id'] =='45052'|| $earr['id'] =='45053') {
                          $endStr = $earr['name'];
                    }else{
                         $endStr = $epro['name'].$earr['name'];
                    }
                    
                    break;
                default: // 区
                    $ecity = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                    $epro = Db::table('ct_district')->where('id',$ecity['parent_id'])->find();

                    $endStr = $epro['name'] . $ecity['name'] . $earr['name'];
                    break;
            }
            $resultarr[$k]['startname'] = $startStr;
            $resultarr[$k]['endname'] = $endStr;
        }
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$resultarr);
    	return view('customline/index');
    }
    // 添加专线 ajax
    public function addmessage(){
        $postdate = Request::instance()->post();
        // 指派承运商
        if ($postdate['carrierid'] !='') {        
            $carrier = DB::table('ct_company')->where('cid','=',$postdate['carrierid'])->where(array('type'=>array('in','1,2'),'status'=>1))->find();
            if(!$carrier) { // 判断公司是否存在
                $result['message'] = '该公司不存在请重新添加';
                $result['code'] = true;
                echo json_encode($result);
            }
            // 固定线路指派承运商ID
            $line_data['carrierid'] = $carrier['cid'];
            //添加司机和车辆信息
            $arr = $postdate['carmess'];
            foreach ($postdate['carmess'] as $key => $value) {
                if('0' == $value['driverid']) unset($arr[$key]);
            }
            $line_data['trans_mess'] = json_encode($arr);
            // 推荐车型
            $line_data['carid'] = $postdate['carid'];
        }
        //  
        $startAddress = '';
        $endAddress = '';
        // 始发城市和终点城市判定
        if ($postdate['tpro'] =='0' &&  $postdate['ppro'] =='0') {
            $result['message'] = '请选择始发和终点城市';
        }
        // 始发城市
        if ($postdate['tarea'] == '0') {
            $startAddress = $postdate['tcity'];
        }else{
            $startAddress = $postdate['tarea'];
        }
        // 终点城市
        if ($postdate['parea'] =='0') { 
            $endAddress = $postdate['pcity'];
        }else{
            $endAddress = $postdate['parea'];
        }
        $find_city = Db::table('ct_already_city')->where(array('start_id'=>$startAddress,'end_id'=>$endAddress))->find();
        if ($find_city=='') {
            $city_data['start_id'] = $startAddress;
            $city_data['end_id'] = $endAddress;
            $city_data['add_time'] = time();
            $city_id = Db::table('ct_already_city')->insertGetId($city_data);
        }else{
            $city_id = $find_city['city_id'];
        }

        // 提货地址
        $a = $postdate['pickinfo'];
        $pickaddress = array();
        $address ='';
        if (!empty($a)) {
             foreach ($a as  $val) {
                $str = '';
                if ($val['province'] != 0 ) {
                    $province = Db::table('ct_district')->where('id',$val['province'])->find();  
                    $province_str = $province['name'];
                    $city = Db::table('ct_district')->where('id',$val['city'])->find();
                    $city_str = $city['name'];
                    $area = Db::table('ct_district')->where('id',$val['area'])->find();
                    $area_str = $area['name'];
                    $address = $val['address'];
                    $address = $province_str.$city_str.$area_str.$address;
                    array_push($pickaddress,$address);
                }
                
            }
        }
        //echo json_encode($postdate['carmess']);exit();
        $line_data['paddress'] = json_encode($pickaddress);
        
        // 基础运费 (元/车)
        $line_data['carprice'] = $postdate['carprice'];
        // 超配门店运价 (元/门店)
        $line_data['moredoor'] = $postdate['moredoor'];
        // 合同门店数(个)
        $line_data['appoint_door'] = $postdate['appoint_door'];
        // 物品类别
        $line_data['goodname'] = $postdate['goodname'];
         //承运商基础运费（元/车）
        $line_data['carr_price'] = $postdate['carr_price'];
        //承运商超配门店运价（元/门店）
        $line_data['carr_moredoor'] = $postdate['carr_moredoor'];
        // 温度要求
        $line_data['temperature'] = $postdate['temperature'];
        // APP备注
        $line_data['remark'] = $postdate['remark'];
        //PC端备注
        $line_data['pcremark'] = $postdate['pcremark'];
        // 公司id
        $line_data['companyid'] = $postdate['cid'];
        // 线路ID
        $line_data['lienid'] = $city_id;
        // 线路状态
        $line_data['shiftstate'] = 1;
        // 添加时间
        $line_data['addtime'] = time();
       

        $lineid = Db::table('ct_fixation_line')->insertGetId($line_data);
        
        if($lineid){
            $result['message'] = '提交成功';
            $result['code'] = true;
            echo json_encode($result);
            
        }else{
            $result['message'] = '提交失败';
            $result['code'] = false;
            echo json_encode($result);
        }
    }
    // 删除专线 ajax
    public function deleteline()
    {
        $get = Request::instance()->post();
        $id = $get['id'];
        $lineid = $get['lineid'];
        $search_order = Db::table('ct_shift_order')->where('shiftid',$id)->select();
        if (empty($search_order)) {
            $isdel =  DB::table('ct_fixation_line')->where('id',$id)->delete();
            $line = DB::table('ct_fixation_line')->where('lienid',$lineid)->find();
            $shift = Db::table('ct_shift')->where('linecityid',$lineid)->find();
            if (empty($line) && empty($shift)) {
                DB::table('ct_already_city')->where('city_id',$lineid)->delete();
            }
        }else{
             $isdel =  DB::table('ct_fixation_line')->where('id',$id)->update(array('shiftstate'=>'2'));
        }
        //$data['shiftstate'] ='2';
        //$isdel = Db::table('ct_fixation_line')->where('id',$id)->update();
        if($isdel){
            $result['code'] = true;
            $result['message'] = '定制线路已经删除';
        }else{
            $result['code'] = false;
            $result['message'] = '定制线路删除失败';
        }
        echo json_encode($result);
    }
   
    
    // 详情
    public function detail() {
        $get = Request::instance()->get();
        $id = $get['id'];
        $result = DB::field('a.*,b.name,d.name as carr_company')
                    ->table('ct_fixation_line')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.companyid')
                    ->join('ct_company d','d.cid=a.carrierid','LEFT')
                    ->where('id',$id)
                    ->find();
        // 始发地址和终点地址
        $startStr = '';
        $endStr = '';
        $linecity = Db::table('ct_already_city')->where('city_id',$result['lienid'])->find();
        // 
        $startid = $linecity['start_id'];
        $endid = $linecity['end_id'];
        // 始发地
        $sarr = Db::table('ct_district')->where('id',$startid)->find();
        switch ($sarr['level']) {
            case 1: // 省
                $startStr = $sarr['neme'];
                break;
            case 2: // 市

                $spro = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                if ($sarr['id'] =='45054' || $sarr['id'] =='45055'|| $sarr['id'] =='45052'|| $sarr['id'] =='45053') {
                      $startStr = $sarr['name'];
                }else{
                     $startStr = $spro['name'].$sarr['name'];
                }
              
                break;
            default: // 区
                $scity = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                $spro = Db::table('ct_district')->where('id',$scity['parent_id'])->find();
                $startStr = $spro['name'] . $scity['name'] . $sarr['name'];
                break;
        }
        // 终点地
        $earr = Db::table('ct_district')->where('id',$endid)->find();
        switch ($earr['level']) {
            case 1: // 省
                $endStr = $earr['name'];
                break;
            case 2: // 市
                $epro = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                if ($earr['id'] =='45054' || $earr['id'] =='45055'|| $earr['id'] =='45052'|| $earr['id'] =='45053') {
                      $endStr = $earr['name'];
                }else{
                     $endStr = $epro['name'].$earr['name'];
                }
                
                break;
            default: // 区
                $ecity = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                $epro = Db::table('ct_district')->where('id',$ecity['parent_id'])->find();

                $endStr = $epro['name'] . $ecity['name'] . $earr['name'];
                break;
        }
        $result['startname'] = $startStr;
        $result['endname'] = $endStr;
        // 提货点地址
        $result['paddress'] = json_decode($result['paddress'],true);
        if($result['shiftstate'] == 1){
            $result['shiftstate'] = '开启';
        }else{
            $result['shiftstate'] = '关闭';
        }
        // 分配司机信息
        $mess_d = json_decode($result['trans_mess'],true);
      
        $arr = array();
        if (!empty($mess_d)) {
            foreach ($mess_d as $key => $value) {
                $driver = Db::table('ct_driver')->where('drivid',$value['driverid'])->find();
                if(!empty($value['carid'])) {
                    $car = Db::table('ct_carcategory')->where('ccid',$value['carid'])->find();
                    $arr[$key]['car_id'] = $value['carid'];
                    $arr[$key]['car_number'] = $car['carnumber'];
                }else{
                    $arr[$key]['car_number'] = '';
                }
                $arr[$key]['driver_id'] = $value['driverid'];
                $arr[$key]['driver_name'] = $driver['realname'];
                $arr[$key]['driver_phone'] = $driver['mobile'];
            }
        }
        
       
        $result['trans_mess'] = $arr;
        //echo "<pre/>";
       // print_r($result);
        $this->assign("list",$result);
        return view('customline/detail');
    }
    // 更新
    public function update($value='')
    {
        $get = Request::instance()->get();
        $id = $get['id'];
        $result = DB::field('a.*,b.name,d.name as carr_company,b.cid')
                    ->table('ct_fixation_line')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.companyid')
                    ->join('ct_company d','d.cid=a.carrierid','left')
                    ->where('id',$id)
                    ->find();
        // 始发地址和终点地址
        $startStr = '';
        $endStr = '';
        $linecity = Db::table('ct_already_city')->where('city_id',$result['lienid'])->find();
        // 
        $startid = $linecity['start_id'];
        $endid = $linecity['end_id'];

        $sarr = Db::table('ct_district')->where('id',$startid)->find();
        switch ($sarr['level']) {
            case 1: // 省
                $startStr = $sarr['neme'];
                break;
            case 2: // 市
                $spro = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                if ($sarr['id'] =='45054' || $sarr['id'] =='45055'|| $sarr['id'] =='45052'|| $sarr['id'] =='45053') {
                      $startStr = $sarr['name'];
                }else{
                     $startStr = $spro['name'].$sarr['name'];
                }
                break;
            default: // 区
                $scity = Db::table('ct_district')->where('id',$sarr['parent_id'])->find();
                $spro = Db::table('ct_district')->where('id',$scity['parent_id'])->find();
                $startStr = $spro['name'] . $scity['name'] . $sarr['name'];
                break;
        }
        $earr = Db::table('ct_district')->where('id',$endid)->find();
        switch ($earr['level']) {
            case 1: // 省
                $endStr = $earr['name'];
                break;
            case 2: // 市
                $epro = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                if ($earr['id'] =='45054' || $earr['id'] =='45055'|| $earr['id'] =='45052'|| $earr['id'] =='45053') {
                      $endStr = $earr['name'];
                }else{
                     $endStr = $epro['name'].$earr['name'];
                }
                break;
            default: // 区
                $ecity = Db::table('ct_district')->where('id',$earr['parent_id'])->find();
                $epro = Db::table('ct_district')->where('id',$ecity['parent_id'])->find();
                $endStr = $epro['name'] . $ecity['name'] . $earr['name'];
                break;
        }
        $result['startname'] = $startStr;
        $result['endname'] = $endStr;

        $result['paddress'] = json_decode($result['paddress'],true);
        $mess_d = json_decode($result['trans_mess'],true);
      // print_r($mess_d['0']['driverid']);exit();
        $arr = array();
        if (!empty($mess_d)) {
            foreach ($mess_d as $key => $value) {
                $driver = Db::table('ct_driver')->where('drivid',$value['driverid'])->find();
                $car = Db::table('ct_carcategory')->where('ccid',$value['carid'])->find();
                $arr[$key]['driver_id'] = $value['driverid'];
                $arr[$key]['driver_name'] = $driver['realname'];
                $arr[$key]['driver_phone'] = $driver['mobile'];
                $arr[$key]['car_id'] = $value['carid'];
                $arr[$key]['car_number'] = $car['carnumber'];
            }
        }
        
        //$result['cid'] = $get['id'];
        $result['trans_mess'] = $arr;

        $this->assign("list",$result);
        return view('customline/update');
    }
   
     /*
    *
    * 修改专线
    */
    public function upmessage(){
        $postdate = Request::instance()->post();
        //指派司机
        //print_r($postdate['pickinfos']);exit();
        $select_mess = array();
        $arr = array();
        if (isset($postdate['driverinfos'])) {
            $select_mess = $postdate['driverinfos'];
        }
        // 指派承运商
        if ($postdate['carrierName'] !='') {
            $carrier = DB::table('ct_company')->where('name','=',$postdate['carrierName'])->where(array('type'=>array('in','1,2'),'status'=>1))->find();
            if(!$carrier) { // 判断公司是否存在
                $this->error('该公司不存在请重新添加!');
            }
            // 固定线路指派承运商ID
            $line_data['carrierid'] = $carrier['cid'];
             //承运商基础运费（元/车）
            $line_data['carr_price'] = $postdate['carr_price'];
             //承运商超配门店运价（元/门店）
            $line_data['carr_moredoor'] = $postdate['carr_moredoor'];
            $line_data['carid'] = $postdate['carid'];
            $arr = $postdate['carmess'];
            foreach ($postdate['carmess'] as $key => $value) {
                if('0' == $value['driverid']) unset($arr[$key]);
            }
        }
        $merge_arr = array_merge($select_mess,$arr);
        $line_data['trans_mess'] = json_encode($merge_arr);

        //  
        $startAddress = '';
        $endAddress = '';
        // 始发城市和终点城市判定
        if ($postdate['tpro'] !='0' &&  $postdate['ppro'] !='0') {
            //$this->error('请选择始发和终点城市!!');
            // 始发城市
            if ($postdate['tarea'] == '0') {
                $startAddress = $postdate['tcity'];
            }else{
                $startAddress = $postdate['tarea'];
            }
            // 终点城市
            if ($postdate['parea'] =='0') { 
                $endAddress = $postdate['pcity'];
            }else{
                $endAddress = $postdate['parea'];
            }
            $find_city = Db::table('ct_already_city')->where(array('start_id'=>$startAddress,'end_id'=>$endAddress))->find();
            if ($find_city=='') {
                $city_data['start_id'] = $startAddress;
                $city_data['end_id'] = $endAddress;
                $city_data['add_time'] = time();
                $city_id = Db::table('ct_already_city')->insertGetId($city_data);
            }else{
                $city_id = $find_city['city_id'];
            }
            // 线路ID
            $line_data['lienid'] = $city_id;
        }

        
        //print_r($arr);
        //exit();
        // 提货地址
        $pickaddress = array();
        $oldpick = array();
        if (isset($postdate['pickinfos'])) {
            $pickaddress = $postdate['pickinfos'];
        }
        $a = $postdate['pickinfo'];
        $address ='';
        if (!empty($a)) {
             foreach ($a as  $val) {
                $str = '';
                if ($val['province'] != 0 ) {
                    $province = Db::table('ct_district')->where('id',$val['province'])->find();  
                    $province_str = $province['name'];
                    $city = Db::table('ct_district')->where('id',$val['city'])->find();
                    $city_str = $city['name'];
                    $area = Db::table('ct_district')->where('id',$val['area'])->find();
                    $area_str = $area['name'];
                    $address = $val['address'];
                    $address = $province_str.$city_str.$area_str.$address;
                    array_push($pickaddress,$address);
                }
                
            }
        }
       
        
        // 物品类别
        $line_data['goodname'] = $postdate['goodname'];
         // 温度要求
        $line_data['temperature'] = $postdate['temperature'];
        // APP备注
        $line_data['remark'] = $postdate['remark'];
        // PC备注
        $line_data['pcremark'] = $postdate['pcremark'];
        
        // 基础运费 (元/车)
        $line_data['carprice'] = $postdate['carprice'];
        // 超配门店运价 (元/门店)
        $line_data['moredoor'] = $postdate['moredoor'];
        // 合同门店数(个)
        $line_data['appoint_door'] = $postdate['appoint_door'];
        // 提货地址json
        $line_data['paddress'] = json_encode($pickaddress);
        // 公司id
        $line_data['companyid'] = $postdate['cid'];
        // 线路状态
        $line_data['shiftstate'] = 1;
        // 添加时间
        $line_data['addtime'] = time();
        

        $lineid = Db::table('ct_fixation_line')->where('id',$postdate['sid'])->update($line_data);
        
        if($lineid){
            
            $this->success('修改成功', 'customline/companylist');
            exit();
        }else{
            $this->error('修改失败');
            exit();
        }
    }
    // 删除
    public function delate()
    {
        $get = Request::instance()->get();
        $id = $get['id'];
        $lineid = $get['lineid'];
        $search_order = Db::table('ct_shift_order')->where('shiftid',$id)->select();
        if (empty($search_order)) {
            $res =  DB::table('ct_fixation_line')->where('id',$id)->delete();
            $line = DB::table('ct_fixation_line')->where('lienid',$lineid)->find();
            $shift = Db::table('ct_shift')->where('linecityid',$lineid)->find();
            if (empty($line) && empty($shift)) {
                DB::table('ct_already_city')->where('city_id',$lineid)->delete();
            }
        }else{
             $res =  DB::table('ct_fixation_line')->where('id',$id)->update(array('shiftstate'=>'2'));
        }
        
        
       if ($res) {
            
            $this->success('删除成功', 'customline/index');
       }else{
            $this->error('删除失败');
       }
    }
    /*
    *分配司机信息
    */
    public function search_driver(){
        // 指派承运商
        $companyid = input('companyid');
       $result = Db::table('ct_driver')->where(array('companyid'=> $companyid,'delstate'=>1,'type'=>['IN','1,2']))->select();
       $array = array();
       foreach ($result as $key => $value) {
           $array[$key]['id']=$value['drivid'];
           $array[$key]['name']=$value['realname'];
       }
       $res = Db::table('ct_carcategory')->where(array('com_id'=> $companyid,'status'=>2))->select();
       $arr = array();
       foreach ($res as $key => $value) {
           $arr[$key]['carid']=$value['ccid'];
           $arr[$key]['carnumber']=$value['carnumber'];
       }
       $two_arr['driverlist'] = $array;
       $two_arr['carlist'] = $arr;
       echo json_encode($two_arr);
    }
    // 验证公司名称是否存在
    public function iscompanyname(){
        $get_post = Request::instance()->post();
        $name = DB::table('ct_company')->where('name',$get_post['name'])->find();
        if (!empty($name)) {
            return false;
        }else{
            return true;
        }
    }

    /*
    *查询所有承运商的值
    */
    public function getcarriers(){
        $arr=array();
        $driver_arr=array();
        $car_arr=array();
       $reslut = DB::table('ct_company')->where(array('type'=>array('in','1,2'),'status'=>1))->order('status','asc')->select();
        foreach ($reslut as $value) {
            $driver = Db::table('ct_driver')->where(array('companyid'=>$value['cid'],'delstate'=>1))->select();
            foreach ($driver as $k => $v) {
                $driver_arr[$k] = array(
                    'driverid'=>$v['drivid'],
                    'drivername'=>$v['realname']
                );
            }
            $car = Db::table('ct_carcategory')->where(array('com_id'=> $value['cid'],'status'=>2))->select();
             foreach ($car as $ke => $val) {
                $car_arr[$ke] = array(
                    'driverid'=>$val['ccid'],
                    'carnumber'=>$val['carnumber']
                );
            }
        $arr[$value['cid']]['comname'] =  $value['name'];
        $arr[$value['cid']]['driverlist'] =  $driver_arr;
        $arr[$value['cid']]['carlist'] =  $car_arr;
        }
        echo json_encode($arr);
    }
    /*
    *添加承运商
    *
    */
    public function addcarr(){
        $get = Request::instance()->get();
        $id = $get['id'];
        $result = DB::field('a.*,b.name,d.name as carr_company')
                    ->table('ct_fixation_line')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.companyid')
                    ->join('ct_company d','d.cid=a.carrierid','left')
                    ->where('id',$id)
                    ->find();
        $mess_d = json_decode($result['trans_mess'],true);
        $arr = array();
        if (!empty($mess_d)) {
            foreach ($mess_d as $key => $value) {
                $driver = Db::table('ct_driver')->where('drivid',$value['driverid'])->find();
                $car = Db::table('ct_carcategory')->where('ccid',$value['carid'])->find();
                $arr[$key]['driver_id'] = $value['driverid'];
                $arr[$key]['driver_name'] = $driver['realname'];
                $arr[$key]['driver_phone'] = $driver['mobile'];
                $arr[$key]['car_id'] = $value['carid'];
                $arr[$key]['car_number'] = $car['carnumber'];
            }
        }
        
        $result['cid'] = $get['comid'];
        $result['trans_mess'] = $arr;
        $this->assign('list',$result);
        return view('customline/addcarr');
    }

    /*
    *
    *修改承运商
    */
    public function update_carr(){
        $postdate = Request::instance()->post();
        $carrid = $postdate['carrierid'];
        $select_mess = array();
        $arr = array();
        $merge_arr = '';
        if (isset($postdate['driverinfos'])) {
            $select_mess = $postdate['driverinfos'];
        }
        $search_carr = Db::table('ct_fixation_line')->field('carrierid')->where('id',$postdate['id'])->find();
        // 指派承运商
        $carrier = DB::table('ct_company')->where('cid','=',$postdate['carrierid'])->where(array('status'=>1))->find();
        if(empty($carrier)) { // 判断公司是否存在
            $result['message'] = '该公司不存在请重新添加';
            $result['code'] = false;
            echo json_encode($result);
            exit();
        }
       
        if (isset($postdate['carmess'])) {
            $arr = $postdate['carmess'];
             foreach ($postdate['carmess'] as $key => $value) {
                if(' ' == $value['driverid']) unset($arr[$key]);
            }
        }

       if ($search_carr['carrierid'] == $carrier['cid']) { //当修改还是同一承运商时
            $merge_arr = array_merge($select_mess,$arr);
       }else{
            $merge_arr = $arr;
       }
       //去掉数组中空值
        foreach ($merge_arr as $key => $value) {
            if('' == $value['driverid']) unset($merge_arr[$key]);
        }
        // 固定线路指派承运商ID
        $line_data['carrierid'] = $carrier['cid'];
        $line_data['carid'] = $postdate['carid'];
        $line_data['trans_mess'] = json_encode($merge_arr);
        $line_data['carid'] = $postdate['carid'];
        //承运商基础运费（元/车）
        $line_data['carr_price'] = $postdate['carr_price'];
        //承运商超配门店运价（元/门店）
        $line_data['carr_moredoor'] = $postdate['carr_moredoor'];
        //

        $result = Db::table('ct_fixation_line')->where('id',$postdate['id'])->update($line_data);
        if ($result){
            $res['code'] = true;
            $res['message'] = '指派成功';
        }else{
            $res['code'] = false;
            $res['message'] = '指派失败';
        }
        echo json_encode($res);
    }
}