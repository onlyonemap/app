<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Carloadorder  extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//    }

    /**
     * 整车开通的优惠城市列表
     * @auther: 李渊
     * @data: 2018.9.6
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function activityCity()
    {
        $search = input('search');
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
            $where['com.name'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search;
        }
        $where['state'] = 1;
        // 查询数据
        $result = DB::field('a.*,com.name,d.username,d.realname')
            ->table('ct_activitycity')
            ->alias('a')
            ->join('ct_company com','com.cid = a.appoint_cid','left')
            ->join('ct_driver d','d.drivid = a.appoint_driver','left')
            ->where($where)
            ->order('a.id','desc')
            ->paginate(10,false, $pageParam);
        $list =  $result->toArray();
        $arr = array();
        // 遍历获取城市
        foreach ($list['data'] as $key => $value) {
            $arr[$key] = $value;
            // 起点城市
            $arr[$key]['startCity'] = addresidToName($value['startCity']);
            // 终点城市
            $arr[$key]['endCity'] = addresidToName($value['endCity']);
        }
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$arr);
        return view('carloadorder/citylist');
    }

    /**
     * 前往添加整车开通的优惠城市页面
     * @auther: 李渊
     * @data: 2018.9.6
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function toaddCity()
    {
        return view('carloadorder/addcity');
    }

    /**
     * 添加整车开通的优惠城市
     * @auther: 李渊
     * @data: 2018.9.6
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function addcity()
    {
        // 获取起点城市
        $data['startCity'] = input('startCity');
        // 获取终点
        $data['endCity'] = input('endCity');
        // 获取线路价格
        $data['price'] = input('price');
        // 获取线路备注
        $data['remark'] = input('remark');
        // 起点城市周边城市
        $data['startCityAround'] = input("startCityAround");  
        // 终点城市周边城市
        $data['endCityAround'] = input("endCityAround"); 

        // 获取线路指派承运公司
        $data['appoint_cid'] = input('appoint_cid');
        // 获取线路承运人
        $data['appoint_driver'] = input('appoint_driver');
        // 获取线路承运费用
        $data['appoint_price'] = input('appoint_price');

        // 插入数据
        $insert = Db::table('ct_activitycity')->insert($data);
        // 判断
        if($insert){
            $content = "添加了整车优惠线路:";
            $this->hanldlog($this->uid,$content);
            return json(['code'=>true,'message'=>'添加成功']);
        }else{
            return json(['code'=>false,'message'=>'添加失败']);
        }
    }

    /**
     * 前往修改整车开通的优惠城市页面
     * @auther: 李渊
     * @data: 2018.9.6
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function toupdateCity()
    {
        // 获取索引id
        $id = input('id');
        // 查询数据
        $result = Db::table('ct_activitycity')->where('id',$id)->find();
        // 返回起点城市
        $result['startCityName'] = addresidToName($result['startCity']);
        // 返回终点城市
        $result['endCityName'] =  addresidToName($result['endCity']);
        // 返回起点城市周边城市
        $result['startCityAround'] =  json_decode($result['startCityAround'],true);
        // 返回终点城市周边城市
        $result['endCityAround'] =  json_decode($result['endCityAround'],true);
        // 获取承运公司信息
        $carrier = Db::table('ct_company')->where('cid',$result['appoint_cid'])->find();
        // 返回承运商名称
        $result['companyname'] = $carrier['name'];

        // 获取调度信息
        $driver = Db::table('ct_driver')->where('drivid',$result['appoint_driver'])->find();
        // 返回调度姓名
        $result['drivername'] = $driver['realname'] == '' ? $driver['username'] : $driver['realname'];        

        $this->assign('list',$result);
        return view('carloadorder/updatecity');
    }

    /**
     * 修改整车开通的优惠城市
     * @auther: 李渊
     * @data: 2018.9.6
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function updatecity()
    {
        $id = input('id');
        // 获取起点城市
        $startCity = input('startCity');
        // 判断是否修改
        if ($startCity !='0' &&  $startCity !='') {
            $data['startCity'] = $startCity;
        }
        // 获取终点城市
        $endCity = input('endCity');
        // 判断是否修改
        if ($endCity !='0' &&  $endCity !='') {
            $data['endCity'] = $endCity;
        }
        // 起点城市周边城市
        $data['startCityAround'] = input("startCityAround");  
        // 终点城市周边城市
        $data['endCityAround'] = input("endCityAround"); 
        // 获取线路价格
        $data['price'] = input('price');
        // 获取线路备注
        $data['remark'] = input('remark');

        // 获取线路指派承运公司
        $data['appoint_cid'] = input('appoint_cid');
        // 获取线路承运人
        $data['appoint_driver'] = input('appoint_driver');
        // 获取线路承运费用
        $data['appoint_price'] = input('appoint_price');

        // 插入数据
        $update = Db::table('ct_activitycity')->where('id',$id)->update($data);
        // 判断
        if($update){
            $content = "修改了整车优惠线路:";
            $this->hanldlog($this->uid,$content);
            return json(['code'=>true,'message'=>'修改成功']);
        }else{
            return json(['code'=>false,'message'=>'修改失败']);
        }
    }

    /**
     * 删除整车开通的优惠城市
     * @auther: 李渊
     * @data: 2018.9.6
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function deleteCity()
    {
        // 获取索引id
        $id = input('id');
        // 更新状态 
        $update = Db::table('ct_activitycity')->where('id',$id)->update(array('state' => 2));
        // 判断
        if ($update) {
            $content = "删除了ID为".$id."整车优惠城市";
            $this->hanldlog($this->uid,$content);
            $this->success('删除成功','carloadorder/activityCity');
        }else{
            $this->error("删除失败");
        }
    }
    /**
     * 整车订单列表
     * @auther 李渊
     * @date 2018.6.12
     * @return [type] [description]
     */
    public function prouserorder()
    {
        $search = input('search');
        $stime = input('starttime');
        $etime = input('endtime');
        // 查询条件 操作：1未支付2已支付3支付失败3提货支付4配送支付'
        $proorder_where['paystate'] = ['NEQ','1'];
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
            $proorder_where['a.ordernumber|u.realname|u.phone'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search;
        }
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $proorder_where['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] =$stime;
            $pageParam['query']['endtime'] = $etime;
        }
        $arr = array();
        $array = Db::field('a.*,u.realname,u.username,u.phone,u.lineclient,u.userstate,in.instate')
                ->table('ct_userorder')
                ->alias('a')              
                ->join('ct_user u','a.userid = u.uid')
                ->join('ct_invoice in','in.iid=a.user_checkid','LEFT')
                ->where($proorder_where)
                ->order('a.uoid','desc')
                ->paginate(50,false, $pageParam);
        $array_data =  $array->toArray();

        foreach ($array_data['data']  as $key => $value) {
            $arr[$key] = $value;
            // 起点城市
            $arr[$key]['startcity'] = $value['startcity'];
            // 终点城市
            $arr[$key]['endcity'] = $value['endcity'];
            // 发货人信息
            if($value['userstate'] == 1){ // 注册用户
                $arr[$key]['name'] = $value['realname'] == '' ? $value['username'] : $value['realname'];
                $arr[$key]['phone'] =  $value['phone'];
                // 查找业务员
                $arr[$key]['salesman'] = $this->get_sharename($value['userid']);
            }else{ // 项目客户
                $com = DB::table('ct_company')->where('cid',$value['lineclient'])->find();
                $arr[$key]['name'] = $com['name'];
                $arr[$key]['phone'] =  '';
                // 查找业务员
                $arr[$key]['salesman'] = $this->get_order_salesman($value['lineclient'],$value['addtime']);
            }
            // 接单人信息
            $driver = DB::table('ct_driver')->where('drivid',$value['carriersid'])->find();
            // 判断是否有人接单 
            if (!empty($driver)) { 
                if($driver['type'] == '1'){ // 司机  
                    // 接单人姓名
                    $arr[$key]['drivername'] = $driver['realname'] == '' ? $driver['username'] : $driver['realname'];
                    // 接单人电话
                    $arr[$key]['mobile'] =  $driver['mobile']; 
                }else{ // 公司
                    $com = DB::table('ct_company')->where('cid',$driver['companyid'])->find();
                    // 接单人姓名
                    $arr[$key]['drivername'] = $com['name'];
                    // 接单人电话
                    $arr[$key]['mobile'] =  '';
                }
            }else{
                // 接单人姓名
                $arr[$key]['drivername'] = '';
                // 接单人电话
                $arr[$key]['mobile'] =  '';
                // 应付承运商信息
                $driver_mess= "<span class='label label-primary addwidth-1'>待确定</span>";
               
            }
            // 提货时间
            if(strpos($value['loaddate'],"-") > 0) {
                $arr[$key]['loaddate'] = $value['loaddate'];
            }else{
                $arr[$key]['loaddate'] = date('Y',$value['addtime']).'-'.date('m-d H:i',$value['loaddate']/1000);
            }
            // 返回对账信息
            $check_return = $this->checkMessage('2',$value['uoid']);
            //应收客户信息
            $arr[$key]['paystate_str'] = $check_return['use_pay_state'];
            //应收客户运费
            $arr[$key]['paymoney'] = $check_return['use_ar_money'];
            //实收客户运费
            $arr[$key]['referprice'] = $check_return['use_ra_money'];
            //应付承运商信息
            $arr[$key]['driver_mess'] = $check_return['driver_pay_state'];
             //实付承运商运费
            $arr[$key]['driver_pay'] = $check_return['driver_pa_money'];
            //应付承运商运费
            $arr[$key]['driver_payment'] = $check_return['driver_ap_money'];
           
        }
        $page = $array->render();
        $this->assign('page',$page);
        $this->assign('list',$arr);
        return view('carloadorder/prouserorder');
    }
    /*
     * 整车信息发布列表
     * */
    public function vehicalList(){
        $search = input('search');
        $stime = input('starttime');
        $etime = input('endtime');
        // 查询条件 操作：1未支付2已支付3支付失败3提货支付4配送支付'
        $proorder_where['orderstatus'] = ['NEQ','1'];
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
            $proorder_where['a.ordernumber|u.username|u.phone'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search;
        }
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $proorder_where['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] =$stime;
            $pageParam['query']['endtime'] = $etime;
        }
        $arr = array();
    $array = Db::table('ct_useorder')
        ->alias('a')
        ->join('ct_user u','a.userid = u.uid')
        ->field('a.uoid,a.ordernumber,a.startcity,a.endcity,a.addtime,a.picktime,a.price,a.orderstatus,u.username,u.phone')
        ->where($proorder_where)
        ->order('a.uoid', 'desc')
        ->paginate(50,false,$pageParam);

        foreach($array as  $key =>$value){
            $arr[$key]['uoid'] = $value['uoid'];

            $arr[$key]['ordernumber'] = $value['ordernumber'];
            $arr[$key]['startcity'] = $value['startcity'];
            $arr[$key]['endcity'] = $value['endcity'];
            $arr[$key]['addtime'] = $value['addtime'];
            $arr[$key]['price'] = $value['price'];
            $arr[$key]['orderstatus'] = $value['orderstatus'];
            $arr[$key]['username'] = $value['username'];
            $arr[$key]['phone'] = $value['phone'];
            $arr[$key]['picktime'] = date('Y',$value['addtime']).'-'.date('m-d H:i',$value['picktime']/1000);

        }
        $page = $array->render();
        $this->assign('page',$page);
        $this->assign('data',$arr);
        return $this->fetch();
    }
    /*
     * 整车发布信息详情
     * */
    public function vehicalView(){
        $id = input('id');
        $data = Db::field('a.*,car.carparame,b.username,b.phone')
            ->table('ct_useorder')
            ->alias('a')
            ->join('ct_cartype car','car.car_id = a.carid','left')
            ->join('ct_user b','a.userid = b.uid')
            ->where('a.uoid',$id)
            ->find();
        $data['picktime'] = $data['picktime']/1000;
        $data['pickaddress'] = json_decode($data['pickaddress'],TRUE);
        $data['sendaddress'] = json_decode($data['sendaddress'],TRUE);

        $this->assign('list',$data);
        return $this->fetch();
    }

    /**
     * 整车订单详情
     * @auther 李渊
     * @date 2018.6.13
     * @param [Int] 订单id
     * @return [type] [description]
     */
    public function prodetails(){
        // 获取订单id
        $id = input('id');
        // 查找订单数据
        $detail = Db::field('a.*,car.carparame,in.instate,inv.instate carr_instate')
                ->table('ct_userorder')
                ->alias('a')
                ->join('ct_cartype car','car.car_id = a.carid','left')
                ->join('ct_invoice in','in.iid=a.user_checkid','LEFT')
                ->join('ct_invoice inv','inv.iid=a.carr_checkid','LEFT')
                ->where('a.uoid',$id)
                ->find();
        // 查找下单人信息
        $user = Db::table('ct_user')->where('uid',$detail['userid'])->find();
        // 查找下单人公司信息
        $userCompany =  Db::table('ct_company')->where('cid',$user['lineclient'])->find();
        // 查找接单人数据
        $driver = Db::table('ct_driver')->where('drivid',$detail['carriersid'])->find();
        // 查找接单人公司数据
        $driverCompany = Db::table('ct_company')->where('cid',$driver['companyid'])->find();
        // 查找运输人数据
        $runDriver = Db::table('ct_driver')->where('drivid',$detail['driverid'])->find();

        // 返回用户公司
        $detail['userCompany'] = $userCompany['name'] ? $userCompany['name'] : '----';
        // 返回下单人员
        $detail['username'] = $user['realname'] ? $user['realname'] : $user['username'];
        // 返回下单人员联系方式
        $detail['phone'] = $user['phone'];

        // 返回承运公司
        $detail['driverCompany'] = $driverCompany['name'] ? $userCompany['name'] : '----';
        // 返回接单人员
        $detail['drivername'] = $driver['realname'] ? $driver['realname'] : $driver['username'];
        // 返回接单人员联系方式
        $detail['mobile'] = $driver['mobile'];

        // 返回运输人员 先查看是否直接有运输人没有根据id查找
        $detail['runDriver'] = $detail['drivername'] ? $detail['drivername'] : '';
        $detail['runDriver'] = $detail['runDriver'] ? $detail['runDriver'] : $runDriver['realname'];
        $detail['runDriver'] = $detail['runDriver'] ? $detail['runDriver'] : $runDriver['username'];
        // 返回运输人员联系方式 先查看是否直接有运输人没有根据id查找
        $detail['runMobile'] = $detail['driverphone'] ? $detail['driverphone'] : $runDriver['mobile'];

        // 返回提货地址
        $detail['pickaddress'] = json_decode($detail['pickaddress'],TRUE);
        // 返回配送地址
        $detail['sendaddress'] = json_decode($detail['sendaddress'],TRUE);
        // 返回回单
        $detail['receipts'] = json_decode($detail['receipts'],TRUE);
        // 返回预计到达时间
        $detail['arrtime'] = round($detail['arrtime']/1000); 
        // 返回发货日期
        $detail['loaddate'] = round($detail['loaddate']/1000);
        // 返回始发城市
        $detail['startcity'] = $detail['startcity'];
        // 返回终点城市
        $detail['endcity'] = $detail['endcity'];

        // 返回支付类型 1 立即支付  2 线下支付 3 提货付款 4 到货付款 5 支付定金
        switch ($detail['type']) {
            case '1':
                $detail['type'] = '标准价格';
                break;
            case '2':
                $detail['type'] = '面议';
                $detail['price'] = '0.00';
                break;
            case '3':     
                $detail['type'] = '提货付款';
                break;
            case '4':
                $detail['type'] = '到货付款';
                break;
            case '4':
                $detail['type'] = '支付定金';
                break;
            default:
                # code...
                break;
        }

        // 返回支付状态 1未支付2已支付3支付失败4提货支付5配送支付
        switch ($detail['paystate']) {
            case '1':
                $detail['paystate'] = '未支付';
                break;
            case '2':
                $detail['paystate'] = '已支付';
                break;
            case '3':
                $detail['paystate'] = '支付失败';
                break;
            case '4':
                $detail['paystate'] = '未支付';
                break;
            case '5':
                $detail['paystate'] = '未支付';
                break;
            default:
                # code...
                break;
        }

        // 返回付款方式 1信用支付2余额支付 3支付宝 4微信
        switch ($detail['pay_type']) {
            case '1':
                $detail['pay_type'] = "信用支付";
                break;
            case '2':
                $detail['pay_type'] = "余额支付";
                break;
            case '3':
                $detail['pay_type'] = "支付宝支付";
                break;
            case '4':
                $detail['pay_type'] = "微信支付";
                break;
            default:
                # code...
                break;
        }


        // 物流状态为 正在为你匹配车辆 - 订单已承接 - 已提货 - 已配送 - 已送达 - 已完成
        $arr = array();
        // 定义起始物流状态 正在为你匹配车辆
        $arr[] = array('message'=>'下单成功','date'=>date('Y-m-d H:i:s',$detail['addtime']));
        // 如果订单不是未接单或者已取消则定义以下物流状态否则返回
        if ($detail['orderstate'] !='1' && $detail['orderstate'] !='4') {
            // 定义第二物流状态 已承接
            if($detail['taketime']){
                $taketime = date('Y-m-d H:i:s',$detail['taketime']);
                $arr[] = array('message'=>'订单已承接','date'=>$taketime);
            }
            // 定义第三物流状态 已提货
            if ($detail['pickTime']) {
                $pickTime = date('Y-m-d H:i:s',$detail['pickTime']);
                $arr[] = array('message'=>'已提货','date'=>$pickTime);
            }
            // 定义第四物流状态 已配送
            if ($detail['sendTime']) {
                $sendTime = date('Y-m-d H:i:s',$detail['sendTime']);;
            }
            // 定义第五物流状态 司机确认订单完成
            if ($detail['arrivetime']) {
                $arrivetime = date('Y-m-d H:i:s',$detail['arrivetime']);;
                $arr[] = array('message'=>'配送完成','date'=>$arrivetime);
            }
            // 定义第六物流状态 用户确认订单完成
            if ($detail['orderstate'] =='3') {
                $arr[] = array('message'=>'已完成','date'=>'');
            }
        }else if($detail['orderstate'] == '4'){
            $arr[] = array('message'=>'订单已取消','date'=>'');
        }
        // 返回物流信息
        $detail['logistics'] = $arr;

      
        // 查找用户反馈内容
        $con_arr = array();
        // 查找用户反馈内容
        $contact = Db::table('ct_order_contact')->where(array('orderid'=>$detail['uoid'],'otype'=>'4'))->order('id desc')->select();
        // 判断是否有反馈
        if (!empty($contact)) {
            foreach ($contact as $key => $value) {
                if ($value['utype']=='1') { // 用户反馈
                    $user_mess = Db::table('ct_user')->where(array('uid'=>$value['userid']))->find();
                    $con_arr[$key]['realname'] = $user_mess['realname'] ? $user_mess['realname'] : $user_mess['username'] ;
                    $con_arr[$key]['phone'] = $user_mess['phone'];
                    $con_arr[$key]['image'] = $user_mess['image'] ? $user_mess['image'] : get_url().'/static/user_header.png';
                }else{ // 平台反馈
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
        // 返回反馈内容
        $detail['contact'] = $con_arr;
        
        // 输出订单详情数据
        $this->assign('list',$detail);
        return view('carloadorder/prodetails');
    }

     /**
     * 取消整车订单
     * 取消订单要满足以下条件
     * 1、该订单未提货
     * 2、取消时订单是信用支付则恢复信用额度 支付宝、余额、微信支付则支付金额恢复到余额上面
     * @Auther: 李渊
     * @Date: 2018.7.25
     * @return [type] [description]
     */
    public function cancelOrder(){
        // 获取订单id
        $id = input("id");
        // 查询订单数据
        $result = Db::table('ct_userorder')
                ->alias('a')
                ->field('a.*,c.phone,c.username,c.realname,c.money,c.uid,c.lineclient,d.mobile as dmobile,d.username as dusername,
                    d.realname as drealname,e.mobile as emobile,e.username as eusername,e.realname as erealname')
                ->join('ct_user c','c.uid = a.userid')
                ->join('ct_driver d','d.drivid = a.driverid','left')
                ->join('ct_driver e','e.drivid = a.carriersid','left')
                ->where('uoid',$id)
                ->find();
        // 判断订单状态 接单、提货、完成、取消状态不能在进行取消操作
        switch ($result['orderstate']) {
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
            case '5': // 已提货
                return json(['code'=>false,'message'=>'订单已经提货，不能取消']);
                break;
            case '6': // 已配送
                return json(['code'=>false,'message'=>'订单已经配送，不能取消']);
                break;
            case '7': // 订单超时未接单
                return json(['code'=>false,'message'=>'订单已经超时，不能取消']);
                break;
            default:
                # code...
                break;
        }
        // 修改订单状态为4取消
        $iscancle = Db::table('ct_userorder')->where('uoid',$id)->update(['orderstate'=>4]);
        // 判断是否取消成功
        if($iscancle){ // 取消成功
            // 如果是信用支付 费用归还信用额度
            if ($result['pay_type']=='1') {
                // 查询公司信用额度
                $company = Db::table('ct_company')->field('cid,money')->where('cid',$result['lineclient'])->find();
                // 计算公司信用额度
                $data['money'] = $company['money']+$result['referprice'];
                // 更新公司信用额度
                $resl = Db::table('ct_company')->where('cid',$company['cid'])->update($data);
            }else{ // 如果是余额、支付宝、微信 费用归还余额
                $data['money'] = $result['money']+$result['referprice'];
                // 更新个人余额
                $resl = Db::table('ct_user')->where('uid',$result['uid'])->update($data);
            }
            // 如果余额恢复则插入操作日志
            if ($resl) {
                // 插入操作记录
                $content = "取消整车订单".$result['ordernumber']."，公司信用余额恢复:".$result['referprice'];
                $this->hanldlog($this->uid,$content);
            }
           
            // 用户联系人
            $username = $result['realname'] == '' ? $result['username'] : $result['realname'];
            // 用户手机号
            $userphone = $result['phone']; 

            // 调度联系人
            $dusername = $result['drealname'] == '' ? $result['dusername'] : $result['drealname'];
            // 调度手机号
            $dphone = $result['dmobile'];
            
            // 司机联系人
            if($result['drivername'] == ''){
                $drivername = $result['erealname'] == '' ? $result['eusername'] : $result['erealname'];
            }else{
                $drivername = $result['drivername']; 
            }
            // 司机电话
            $driverphone = $result['driverphone'] == '' ? $result['emobile'] : $result['driverphone'];

            // 用户发送短信
            // send_sms_class($userphone,'尊敬的赤途用户,你好! 你的订单号为'.$result['ordernumber'].'的订单已经取消,感谢你的使用!');                
            // 司机发送短信
            if($driverphone != ''){
                send_sms_class($driverphone,'尊敬的赤途司机,你好! 订单号为'.$result['ordernumber'].'的订单,用户已经取消,感谢你的使用!');
            }
            // 调度发送短信
            if($dphone != ''){
                send_sms_class($dphone,'尊敬的赤途司机,你好! 订单号为'.$result['ordernumber'].'的订单,用户已经取消,感谢你的使用!');
            }
            // 插入记录
            $content = "取消整车订单".$result['ordernumber'];
            $this->hanldlog($this->uid,$content);
            // 返回订单取消状态
            return json(['code'=>true,'message'=>'订单已取消']);
        }else{
            return json(['code'=>true,'message'=>'取消失败']);
        } 
    }

    /**
     * 修改整车订单价格
     * @Auther: 李渊
     * @Date: 2018.7.25
     * @param  [type] $otype    [请求类型] 1 客户价格 2 承运价格
     * @param  [type] $oid      [订单id]
     * @param  [type] $price    [修改价格]
     * @return [type] [description]
     */
    public function upprice(){
        $postdata = Request::instance()->post();
        // 获取修改的类型 1 客户价格 2 承运价格
        $otype = $postdata['otype'];
        // 获取修改的金额 
        $price = $postdata['price'];
        // 获取修改的订单id
        $id = $postdata['oid'];
        // 查询订单数据
        $result = Db::table('ct_userorder')->where('uoid',$id)->find();
        // 判断修改的类型 1 客户价格 2 承运价格
        if ($otype == '1') {  //用户
            $data['upprice'] = $price;
            $checkid = $result['user_checkid'];
            $get_price = intval($result['referprice'])-intval($price);
        }else{ //承运商
            $data['carr_upprice'] = $price;
            $checkid = $result['carr_checkid'];
            $get_price = intval($result['price'])-intval($price);
        }
        // 判断是否进行对账
        if ($checkid !='') {
            $invo = Db::table('ct_invoice')->where('iid',$checkid)->find();
            $invo_price = $invo['totalprice'] - $get_price;
            $invo_data['totalprice']= $invo_price;
            if ($invo['self_total'] !='') {
                $self_price = $invo['self_total'] - $get_price;
                $invo_data['self_total']= $self_price;
            }
            Db::table('ct_invoice')->where('iid',$checkid)->update($invo_data);
        }
        // 修改订单价格
        $isUpdate = Db::table('ct_userorder')->where('uoid',$id)->update($data);
        // 判断是否修改成功
        if ($isUpdate) {
            return json(['code'=>true,'message'=>'修改成功']);
        }else{
            return json(['code'=>false,'message'=>'修改失败']);
        }
    }

    /**
     * 未支付订单
     * @return [type] [description]
     */
    public function unorder(){
        $arr = array(); //零担
        $arr_city = array();  //市内配送
        $arr_car = array();  //整车
        $where_data ='';
        $search = Request::instance()->get();
        $pageParam    = ['query' =>[]];
        if (!empty($search['company'])) {
            $where_data['realname|username|phone'] = ['like','%'.$search['company'].'%'];
            $pageParam['query']['company'] = $search['company'];
        }
        if (!empty($search['starttime']) && !empty($search['endtime'])) {
            $endtime = strtotime(trim($search['endtime']).'23:59:59');
            $starttime = strtotime(trim($search['starttime']).'00:00:00');
            $where_data['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] = $search['starttime'];
            $pageParam['query']['endtime'] = $search['endtime'];
        }
      
        $where_data['paystate'] = 1;
        $where_data['userstate'] = 1;
        // 统计订单个数
        $count_order = DB::table('ct_order')->alias('a')->join('ct_user b','b.uid = a.userid')->where($where_data)->count('oid');
        $count_car =  DB::table('ct_city_order')->alias('a')->join('ct_user b','b.uid = a.userid')->where($where_data)->count('id');
        $count_city= DB::table('ct_userorder')->alias('a')->join('ct_user b','b.uid = a.userid')->where($where_data)->count('uoid');
        // 零担
        $result_order = DB::field('a.*,c.tprice,c.usepprice,b.realname,b.username,b.phone,b.userstate,b.lineclient,com.name,com.cid,l.luseprice,d.puseprice')
                ->table('ct_order')
                ->alias('a')
                ->join('ct_user b','b.uid = a.userid')
                ->join('ct_company com','com.cid=b.lineclient','LEFT')
                ->join('ct_pickorder c','c.picid=a.oid')
                ->join('ct_lineorder l','l.orderid=a.oid')
                ->join('ct_delorder d','d.orderid=a.oid')
                ->where($where_data)
                ->paginate(20,false, $pageParam); 
        $order_data = $result_order->toArray();   
        foreach ($order_data['data'] as $key => $value) {
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
            $realname = $value['realname']=='' ? $value['username'] : $value['realname'];
            $realname_str = $realname.' (TEL:'. $value['phone'] .')';
            $value['name'] = $value['name']=='' ? $realname_str : $value['name']; //公司名称
            $value['delivecost'] = intval($delivecost);
            $value['tprice'] = intval($tprice);
            $value['linepice'] = intval($lineprice);
            $value['doornum'] = 0;
            $value['totalprice'] = $value['totalcost'] =='' ? $countprice : $value['totalcost'];
            $value['ostate'] = 1;
            $arr[] = $value;
        } // end 零担

        // 市配
        $result_city = Db::table('ct_city_order')
                      ->alias('a')
                      ->join('ct_user u','u.uid=a.userid')
                      ->join('ct_company c','c.cid=u.lineclient','LEFT')
                      ->field('a.*,u.lineclient,c.name,u.realname,u.username,u.phone,c.cid')
                      ->where($where_data)
                      ->paginate(20,false, $pageParam); 
        $city_data = $result_city->toArray();  
        foreach ($city_data['data'] as $key => $value) {

            $arr_city[$key]['oid'] = $value['id']; //订单ID
            $arr_city[$key]['line'] = '上海市';  //线路
            $realname = $value['realname']=='' ? $value['username'] : $value['realname'];
            $realname_str = $realname.' (TEL:'. $value['phone'] .')';
            $arr_city[$key]['name'] = $value['name']=='' ? $realname_str : $value['name']; //公司名称
            $arr_city[$key]['doornum'] = 0; //门店数
            $arr_city[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_city[$key]['lineclient'] = $value['lineclient']; //公司ID
            $arr_city[$key]['ordernumber'] = $value['orderid']; //订单编号
            $arr_city[$key]['totalweight'] = 0; //重量
            $arr_city[$key]['totalvolume'] = 0; //体积
            $arr_city[$key]['tprice'] = 0; //提货费
            $arr_city[$key]['linepice'] = 0; //干线费
            $arr_city[$key]['delivecost'] = 0; //配送费
            $countprice_city = $value['upprice'] =='' ? $value['actualprice'] : $value['upprice'];
            $arr_city[$key]['totalprice'] = $countprice_city;
            $arr_city[$key]['ostate'] = 3;
        }//市配

        //整车
        $result_car = Db::table('ct_userorder')
                        ->alias('a')
                        ->join('ct_user u','u.uid=a.userid')
                        ->join('ct_company c','c.cid=u.lineclient','LEFT')
                        ->field('a.*,u.realname,u.lineclient,c.name,c.cid,u.username,u.phone')
                        ->where($where_data)
                        ->paginate(20,false, $pageParam); 
        $car_data = $result_car->toArray();
        foreach ($car_data['data'] as $key => $value) {
            $arr_car[$key]['oid'] = $value['uoid']; //订单ID
            $arr_car[$key]['line'] = $this->start_end_city($value['startcity'],$value['endcity']);  //线路
            $arr_car[$key]['doornum'] = 0; //门店数
            $arr_car[$key]['addtime'] = $value['addtime']; //下单时间
            $arr_car[$key]['lineclient'] = $value['lineclient']; //公司ID
            //$arr_car[$key]['name'] = $value['name']; //公司名称
            $realname = $value['realname']=='' ? $value['username'] : $value['realname'];
            $realname_str = $realname.' (TEL:'. $value['phone'] .')';
            $arr_car[$key]['name'] = $value['name']=='' ? $realname_str : $value['name']; //公司名称
            $arr_car[$key]['ordernumber'] = $value['ordernumber']; //订单编号
            $arr_car[$key]['totalweight'] = 0; //重量
            $arr_car[$key]['totalvolume'] = 0; //体积
            $arr_car[$key]['tprice'] = 0; //提货费
            $arr_car[$key]['linepice'] = 0; //干线费
            $arr_car[$key]['delivecost'] = 0; //配送费
            $countprice_car = $value['upprice'] =='' ? $value['referprice'] : $value['upprice'];
            $arr_car[$key]['totalprice'] = $countprice_car;
            $arr_car[$key]['ostate'] = 4;
        }//整车
    
        $list = array_merge($arr,$arr_city,$arr_car);
        if (!empty($list)) {
            $list = $this->my_sort($list,'addtime',SORT_DESC);
        }
        $arr_page = array('order'=>intval($count_order),'city'=>intval($count_city),'car'=>intval($count_car));
        $pos = array_search(max($arr_page), $arr_page);
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
            
        }
        $this->assign('page',$page);
        $this->assign('list',$list);
        return view('carloadorder/unorder');
    }

    /**
     * 删除未支付订单列表中订单
     * @return [type] [description]
     */
    public function delorder(){
        $post_data = Request::instance()->post();
        $orderid = $post_data['orderID'];
        $otype = $post_data['ostate'];
        $arr2 = array('shift'=>'','order'=>'','city'=>'','car'=>'');
        $shift_str = ''; //定制
        $order_str = '';  // 零担
        $city_str = '';  // 城配
        $car_str = '';  // 整车
        $i=0;
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
            $del_data = DB::table('ct_order')->where('oid','IN',rtrim($order_str,','))->delete();
            DB::table('ct_pickorder')->where('orderid','IN',rtrim($order_str,','))->delete(); 
            DB::table('ct_lineorder')->where('orderid','IN',rtrim($order_str,','))->delete();
            DB::table('ct_delorder')->where('orderid','IN',rtrim($order_str,','))->delete();
        }
        if($shift_str!=''){  //定制
            $del_data = Db::table('ct_shift_order')->where('s_oid','IN',rtrim($shift_str,','))->delete();     
        }
        if($city_str!=''){ //城配
            $routid = DB::table('ct_city_order')->field('rout_id')->where('id','IN',rtrim($city_str,','))->select();
            $rout_arr = $this->multiToSingle($routid);
            $rout_str = implode(',',$rout_arr); 
            DB::table('ct_rout_order')->where('rid','IN',$rout_str)->delete();
            $del_data = Db::table('ct_city_order')->where('id','IN',rtrim($city_str,','))->delete();
        }
        if($car_str!=''){ //整车
            $del_data = Db::table('ct_userorder')
                    ->where('uoid','IN',rtrim($car_str,','))
                    ->delete();  
        }
       
        if ($del_data ) {
            print_r('ok');
        }else{
            print_r('fail');
        }
    }
    /*
     * 承运端整车订单列表
     * */
     public function cyzList(){
         $search = input('search');
         $stime = input('starttime');
         $etime = input('endtime');
         // 查询条件 操作：1未支付2已支付3支付失败3提货支付4配送支付'
         $proorder_where['orderstatus'] = ['NEQ','1'];
         $pageParam    = ['query' =>[]];
         if(!empty($search)){
             $proorder_where['a.ordernumber|u.username|u.phone'] = ['like','%'.$search.'%'];
             $pageParam['query']['search'] = $search;
         }
         if(!empty($stime) && !empty($etime)) {
             $endtime = strtotime(trim($etime).'23:59:59');
             $starttime = strtotime(trim($stime).'00:00:00');
             $proorder_where['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
             $pageParam['query']['starttime'] =$stime;
             $pageParam['query']['endtime'] = $etime;
         }
         $arr = array();
         $array = Db::table('ct_driverorder')
             ->alias('a')
             ->join('ct_driver u','a.driverid = u.drivid')
             ->field('a.id,a.uoid,a.ordernumber,a.startcity,a.endcity,a.ordertime,a.picktime,a.price,a.orderstatus,u.username,u.mobile')
             ->where($proorder_where)
             ->order('a.id', 'desc')
             ->where('orderstatus','neq','4')
             ->paginate(50,false,$pageParam);

         foreach($array as  $key =>$value){
             $arr[$key]['id'] =$value['id'];
             $arr[$key]['uoid'] = $value['uoid'];
             $count = Db::table('ct_driverorder')->where('uoid',$value['uoid'])->where('orderstatus',2)->count();
             $arr[$key]['ordernumber'] = $value['ordernumber'];
             $arr[$key]['startcity'] = $value['startcity'];
             $arr[$key]['endcity'] = $value['endcity'];
             $arr[$key]['ordertime'] = $value['ordertime'];
             $arr[$key]['price'] = $value['price'];
             $arr[$key]['orderstatus'] = $value['orderstatus'];
             $arr[$key]['username'] = $value['username'];
             $arr[$key]['phone'] = $value['mobile'];
             $arr[$key]['picktime'] = date('Y',$value['ordertime']).'-'.date('m-d H:i',$value['picktime']/1000);
             $arr[$key]['count'] = $count;
         }

         $page = $array->render();
         $this->assign('page',$page);
         $this->assign('data',$arr);
         return $this->fetch();

    }
    /*
     * 承运端整车订单详情
     * */
    public function cyzView(){
        $id = input('id');
        $data = Db::field('a.*,car.carparame,b.username,b.mobile')
            ->table('ct_driverorder')
            ->alias('a')
            ->join('ct_cartype car','car.car_id = a.carid','left')
            ->join('ct_driver b','a.driverid = b.drivid')
            ->where('a.id',$id)
            ->find();
        $arr = Db::table('ct_driverorder')->where('uoid',$data['uoid'])->where('orderstatus',2)->count();
        $data['picktime'] = $data['picktime']/1000;
        $data['pickaddress'] = json_decode($data['pickaddress'],TRUE);
        $data['sendaddress'] = json_decode($data['sendaddress'],TRUE);
        $this->assign('count',$arr);
        $this->assign('list',$data);

       return  $this->fetch();
    }


    /*
     * 未支付订单
     * */
    public function unpaid(){
        $arr = array(); //零担
        $arr_city = array();  //市内配送
        $arr_car = array();  //整车
        $where_data ='';
        $search = Request::instance()->get();
        $pageParam    = ['query' =>[]];
        if (!empty($search['company'])) {
            $where_data['realname|username|phone'] = ['like','%'.$search['company'].'%'];
            $pageParam['query']['company'] = $search['company'];
        }
        if (!empty($search['starttime']) && !empty($search['endtime'])) {
            $endtime = strtotime(trim($search['endtime']).'23:59:59');
            $starttime = strtotime(trim($search['starttime']).'00:00:00');
            $where_data['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] = $search['starttime'];
            $pageParam['query']['endtime'] = $search['endtime'];
        }

        $where_data['paystate'] = 1;
        $where_data['userstate'] = 1;
        // 统计订单个数
        $count_order = DB::table('ct_order')->alias('a')->join('ct_user b','b.uid = a.userid')->where($where_data)->count('oid');
        $count_car =  DB::table('ct_city_order')->alias('a')->join('ct_user b','b.uid = a.userid')->where($where_data)->count('id');
        $count_city= DB::table('ct_userorder')->alias('a')->join('ct_user b','b.uid = a.userid')->where($where_data)->count('uoid');
        return $this->fetch();
    }
}
