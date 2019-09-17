<?php
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Cityorder  extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//    }

    /**
     * 城配订单列表
     * @Auther: 李渊
     * @Date: 2018.7.9
     * @param  [type] $search    [搜索字段 订单号、姓名、电话号码]
     * @param  [type] $starttime [开始时间]
     * @param  [type] $endtime   [结束时间]
     * @return [type]            [满足条件的所有订单数据]
     */
	public function index(){
        // 搜索字段 订单号、姓名、电话号码
        $search = input('search');
        // 开始时间
        $stime = input('starttime');
        // 结束时间
        $etime = input('endtime');
        // 数据筛选条件 除了未支付外的所有订单
        $order_where['paystate'] =['NEQ','1'];
        // 页码
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
            $order_where['a.orderid|u.realname|u.phone'] = ['like','%'.$search.'%'];
        }
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $order_where['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] =$stime;
            $pageParam['query']['endtime'] = $etime;
        }
        // 查询数据
        $result = Db::table('ct_city_order')
                ->alias('a')
                ->join('ct_user u','u.uid=userid')
                ->join('ct_rout_order r','r.rid = a.rout_id')
                ->join('ct_company com','com.cid=u.lineclient',"left")
                ->field('a.*,u.phone,u.realname,u.username,com.name,u.lineclient,r.driverid,u.lineclient')
                ->where($order_where)
                ->order('a.id','desc')
                ->paginate(100,false, $pageParam);
//        var_dump($result);
        // 转义获取订单数据
        $result_data = $result->toArray();
        $pay =0;

        // 定义列表数据数组
        $arr = array();
        foreach ($result_data['data'] as $key => $value) {
            $arr[$key] = $value;
            // 判断是否为项目客户下单并返回下单人信息
            if($value['lineclient']){ // 项目客户
                $arr[$key]['name'] = $value['name'];
                $arr[$key]['phone'] = '';
                // 查找业务员
                $arr[$key]['salesman'] = $this->get_order_salesman($value['lineclient'],$value['addtime']);
            }else{ // 个体
                $arr[$key]['name'] = $value['realname']==''?$value['username']:$value['realname'];
                $arr[$key]['phone'] = $value['phone'];
                // 查找业务员
                $arr[$key]['salesman'] = $this->get_sharename($value['userid']);
            }

            // 承运人信息
            $driver = DB::table('ct_driver')->where('drivid',$value['driverid'])->find();
            // 判断承运人是承运商还是个体司机并返回接单信息
            if($driver['type'] == '1'){ // 司机接单
                // 接单人信息
                $arr[$key]['drivername'] = $driver['realname'] == ''?$driver['username']:$driver['realname'];
                // 接单人电话
                $arr[$key]['mobile'] =  $driver['mobile'];
            }else{ // 公司
                $com = DB::table('ct_company')->where('cid',$driver['companyid'])->find();
                $arr[$key]['drivername'] = $com['name'];
                $arr[$key]['mobile'] =  '';
            }
            
            // 返回城配城市
            $arr[$key]['city'] = addresidToName($value['city_id']);

            // 返回对账信息
            $check_return = $this->checkMessage('3',$value['id']);
            
            // 应收客户运费
            $arr[$key]['actualprice'] = $check_return['use_ar_money'];
            // 实收客户运费
            $arr[$key]['payprice'] = $check_return['use_ra_money'];
            // 应收客户信息  1 未支付 2 已支付 3 信用支付
            $arr[$key]['paystatr'] = $check_return['use_pay_state'];

            // 应付承运商运费
            $arr[$key]['driver_payment'] = $check_return['driver_ap_money'];
            // 实付承运商运费
            $arr[$key]['driver_pay'] = $check_return['driver_pa_money'];
            // 应付承运商信息 1 未支付 2 已支付 3 信用支付
            $arr[$key]['driver_mess'] = $check_return['driver_pay_state'];
            
        }
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$arr);
        return view('cityorder/index');
    }
    
    /**
     * 城配订单详情
     * @Auther: 李渊
     * @Date: 2018.7.9
     * @return [type] [description]
     */
    public function details(){
        // 获取订单id
        $id = input('id');
        // 查询订单详情
        $result = Db::table('ct_city_order')
                ->field('a.*,rout.*,cartype.carparame,in.instate,inv.instate as carr_instate')
                ->alias('a')
                ->join('ct_rout_order rout','rout.rid = a.rout_id')
                ->join('ct_invoice in','in.iid = a.user_checkid','LEFT')
                ->join('ct_invoice inv','inv.iid = a.carr_checkid','LEFT')
                ->join('ct_cartype cartype','cartype.car_id = a.carid')
                ->where('id',$id)
                ->find();
        // 查询下单人数据
        $user = Db::table('ct_user')->where('uid',$result['userid'])->find();
        // 查找用户公司数据
        $userCompany = Db::table('ct_company')->where('cid',$user['lineclient'])->find();
        // 查找接单人数据
        $driver = Db::table('ct_driver')->where('drivid',$result['driverid'])->find();
        // 查找接单人公司数据
        $driverCompany = Db::table('ct_company')->where('cid',$driver['companyid'])->find();
        // 查找运输人数据
        $runDriver = Db::table('ct_driver')->where('drivid',$result['allotid'])->find();

        // 返回用户公司
        $result['userCompany'] = $userCompany['name'] ? $userCompany['name'] : '----';
        // 返回下单人员
        $result['username'] = $user['realname'] ? $user['realname'] : $user['username'];
        // 返回下单人员联系方式
        $result['phone'] = $user['phone'];

        // 返回承运公司
        $result['driverCompany'] = $driverCompany['name'] ? $userCompany['name'] : '----';
        // 返回接单人员
        $result['drivername'] = $driver['realname'] ? $driver['realname'] : $driver['username'];
        // 返回接单人员联系方式
        $result['mobile'] = $driver['mobile'];

        // 返回运输人员 先查看是否直接有运输人没有根据id查找
        $result['runDriver'] = $result['drivername'] ? $result['drivername'] : '';
        $result['runDriver'] = $result['runDriver'] ? $result['runDriver'] : $runDriver['realname'];
        $result['runDriver'] = $result['runDriver'] ? $result['runDriver'] : $runDriver['username'];
        // 返回运输人员联系方式 先查看是否直接有运输人没有根据id查找
        $result['runMobile'] = $result['driverphone'] ? $result['driverphone'] : $runDriver['mobile'];
        
        // 返回下单城市
        $result['city'] = addresidToName($result['city_id']);
        // 返回提货地址
        $result['pickaddress'] = json_decode($result['saddress'],TRUE);
        // 返回配送地址
        $result['getaddress'] = json_decode($result['eaddress'],TRUE);
        // 返回回单
        $reicpt = json_decode($result['picture'],TRUE);
        $pic = array();
        if (!empty($reicpt)) {
            foreach ($reicpt as $key => $value) {
                $pic[] = $value;
            }
        }
        $result['pic'] =  $pic;
        // 返回支付状态 1未支付2已支付3支付失败4提货支付
        switch ($result['paystate']) {
            case '1':
                $result['paystate'] = '未支付';
                break;
            case '2':
                $result['paystate'] = '已支付';
                break;
            case '3':
                $result['paystate'] = '支付失败';
                break;
            case '4':
                $result['paystate'] = '未支付';
                break;
            default:
                # code...
                break;
        }
        // 返回用车类型  1用车 2包车
        switch ($result['ordertype']) {
            case '1':
                $result['ordertype'] = '正常城配';
                break;
            case '2':
                $result['ordertype'] = '城配包车';
                break;
            default:
                # code...
                break;
        }
        // 返回支付类型 1 标准价格  2 面议 3 提货付款
        switch ($result['pytype']) {
            case '1':
                $result['pytype'] = '下单支付';
                break;
            case '2':
                $result['pytype'] = '面议';
                $result['paymoney'] = '0.00';
                break;
            case '3':
                $result['pytype'] = '提货付款';
                break;
            default:
                # code...
                break;
        }
        // 返回付款方式 1信用支付2余额支付 3支付宝 4微信
        switch ($result['pay_type']) {
            case '1':
                $result['pay_type'] = "信用支付";
                break;
            case '2':
                $result['pay_type'] = "余额支付";
                break;
            case '3':
                $result['pay_type'] = "支付宝支付";
                break;
            case '4':
                $result['pay_type'] = "微信支付";
                break;
            default:
                # code...
                break;
        }


        // 物流状态为 正在为你匹配车辆 - 订单已承接 - 已提货 - 已送达 - 已完成
        $arr = array();
        // 定义起始物流状态 正在为你匹配车辆
        $arr[] = array('message'=>'下单成功','date'=>date('Y-m-d H:i:s',$result['addtime']));
        // 如果订单不是未接单则定义以下物流状态否则返回
        if ($result['state'] !='1' && $result['state'] !='4' && $result['state'] !='6') {
            // 定义第二物流状态 已承接
            $taketime = $result['take_time']!='' ? date('Y-m-d H:i:s',$result['take_time']):'';
            $arr[] = array('message'=>'订单已承接','date'=>$taketime);
            // 定义第三物流状态 已提货
            if ($result['pickTime'] || $result['state'] == '5') {
                if($result['pickTime']){
                    $pickTime = date('Y-m-d H:i:s',$result['pickTime']);
                }else{
                    $pickTime = $result['data_type'];
                }
                $arr[] = array('message'=>'已提货','date'=>$pickTime);
            }
            // 定义第四物流状态 司机确认订单完成
            if ($result['arrivetime']) {
                $arrivetime = $result['arrivetime']!='' ? date('Y-m-d H:i:s',$result['arrivetime']):'';
                $arr[] = array('message'=>'配送完成','date'=>$arrivetime);
            }
            // 定义第五物流状态 用户确认订单完成
            if ($result['state'] =='3') {
                $finshtime = $result['finshtime']!='' ? date('Y-m-d H:i:s',$result['finshtime']):'';
                $arr[] = array('message'=>'订单已完成','date'=>$finshtime);
            }
        }else if($result['state'] == '4'){
            $arr[] = array('message'=>'订单已取消','date'=>'');
        }else if($result['state'] == '6'){
            $arr[] = array('message'=>'订单已超时','date'=>'');
        }
        // 返回物流信息
        $result['logistics'] = $arr;


        // 定义反馈内容数组
        $con_arr = array();
        // 查找反馈数据
        $contact = Db::table('ct_order_contact')->where(array('orderid'=>$result['id'],'otype'=>'3'))->order('id desc')->select();
        // 判断是否为空
        if (!empty($contact)) {
            foreach ($contact as $key => $value) {
                if ($value['utype']=='1') { // 用户反馈
                    $user_mess = Db::table('ct_user')->where(array('uid'=>$value['userid']))->find();
                    $con_arr[$key]['realname'] = $user_mess['realname']?$user_mess['realname']:$user_mess['username'];
                    $con_arr[$key]['phone'] = $user_mess['phone'];
                    $con_arr[$key]['image'] = $user_mess['image'];
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
        $result['contact'] = $con_arr;
        // 返回数据
        $this->assign('list',$result);
        return view('cityorder/details');
    }
       /*
        * 城市配送信息发布列表
        * */
    public function deliveryList(){
        // 搜索字段 订单号、姓名、电话号码
        $search = input('search');
        // 开始时间
        $stime = input('starttime');
        // 结束时间
        $etime = input('endtime');
        // 数据筛选条件 除了未支付外的所有订单
        $order_where['orderstatus'] =['NEQ','1'];
        // 页码
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
            $order_where['a.ordernumber|u.username|u.phone'] = ['like','%'.$search.'%'];
        }
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $order_where['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] =$stime;
            $pageParam['query']['endtime'] = $etime;
        }
        $result = Db::table('ct_delivery')
            ->alias('a')
            ->join('ct_user u','u.uid=a.userid')
            ->field('a.uoid,a.ordernumber,a.startcity,a.addtime,a.picktime,a.orderstatus,a.price,u.phone,u.username')
            ->where($order_where)
            ->order('a.userid','desc')
            ->paginate(50,false, $pageParam);

//        var_dump($result);
        $data = [];
        foreach($result as $key =>$value){
            $data[$key]['uoid'] = $value['uoid'];
            $data[$key]['ordernumber'] = $value['ordernumber'];
            $data[$key]['startcity'] = $value['startcity'];
            $data[$key]['addtime'] = $value['addtime'];
            $data[$key]['picktime'] = date('Y',$value['addtime']).'-'.date('m-d H:i',$value['picktime']/1000);
            $data[$key]['orderstatus'] = $value['orderstatus'];
            $data[$key]['price'] =$value['price'];
            $data[$key]['username'] = $value['username'];
            $data[$key]['phone'] =$value['phone'];

        }
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$data);
        return $this->fetch();

    }
    /*
     * 城市配送信息发布详情
    * */
    public function deliveryView(){
        $id = input('id');
        $data = Db::table('ct_delivery')
            ->alias('a')
            ->join('ct_user u','a.userid = u.uid')
            ->join('ct_cartype car','car.car_id = a.carid','left')
            ->field('a.*,u.username,u.phone,car.carparame')
            ->where('uoid',$id)
            ->find();
        $data['picktime'] = $data['picktime']/1000;
        $data['taddress'] = json_decode($data['taddress'],TRUE);
        $data['paddress'] = json_decode($data['paddress'],TRUE);
        $this->assign('list',$data);

       return  $this->fetch();
    }
    /*
     * 承运端城配订单列表
     * */
    public function cycList(){
        // 搜索字段 订单号、姓名、电话号码
        $search = input('search');
        // 开始时间
        $stime = input('starttime');
        // 结束时间
        $etime = input('endtime');
        // 数据筛选条件 除了未支付外的所有订单
        $order_where['orderstatus'] =['NEQ','1'];
        // 页码
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
            $order_where['a.ordernumber|u.username|u.phone'] = ['like','%'.$search.'%'];
        }
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $order_where['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] =$stime;
            $pageParam['query']['endtime'] = $etime;
        }
        $result = Db::table('ct_delivery_order')
            ->alias('a')
            ->join('ct_driver u','u.drivid=a.driverid')
            ->field('a.id,a.uoid,a.ordernumber,a.startcity,a.createtime,a.picktime,a.orderstatus,a.price,u.mobile,u.username')
            ->where($order_where)
            ->order('a.driverid','desc')
            ->paginate(50,false, $pageParam);

        $data = [];
        foreach($result as $key =>$value){
            $data[$key]['id'] = $value['id'];
            $data[$key]['uoid'] = $value['uoid'];
            $count=  Db::table('ct_delivery_order')->where('uoid',$value['uoid'])->where('orderstatus',2)->count();
            $data[$key]['ordernumber'] = $value['ordernumber'];
            $data[$key]['startcity'] = $value['startcity'];
            $data[$key]['addtime'] = $value['createtime'];
            $data[$key]['picktime'] = date('Y',$value['createtime']).'-'.date('m-d H:i',$value['picktime']/1000);
            $data[$key]['orderstatus'] = $value['orderstatus'];
            $data[$key]['price'] =$value['price'];
            $data[$key]['username'] = $value['username'];
            $data[$key]['phone'] =$value['mobile'];
            $data[$key]['count'] = $count;
        }
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$data);
        return $this->fetch();

    }
    /*
     * 承运端城配订单详情
     * */
    public function cycView(){
        $id = input('id');
        $data = Db::table('ct_delivery_order')
            ->alias('a')
            ->join('ct_driver u','a.driverid = u.drivid')
            ->join('ct_cartype car','car.car_id = a.carid','left')
            ->field('a.*,u.username,u.mobile,car.carparame')
            ->where('id',$id)
            ->find();
        $arr = Db::table('ct_delivery_order')->where('uoid',$data['uoid'])->where('orderstatus',2)->count();
        $data['picktime'] = $data['picktime']/1000;
        $data['taddress'] = json_decode($data['taddress'],TRUE);
        $data['paddress'] = json_decode($data['paddress'],TRUE);
        $this->assign('list',$data);
        $this->assign('count',$arr);
       return $this->fetch();
    }
    /**
     * 取消市配订单
     * 取消订单要满足以下条件
     * 1、该订单未提货
     * 2、取消时订单是信用支付则恢复信用额度 支付宝、余额、微信支付则支付金额恢复到余额上面
     * @Auther: 李渊
     * @Date: 2018.7.24
     * @return [type] [description]
     */
    public function ordercancel() {
        // 获取订单id
        $id = input("id");
        // 查询订单数据
        $result = Db::table('ct_city_order')
                ->alias('a')
                ->field('a.orderid,a.state,a.pay_type,a.actualprice,a.userid,b.driverid,b.allotid,c.phone,c.username,c.realname,c.money,c.uid,d.mobile as dmobile,d.username as dusername,
                    c.lineclient,d.realname as drealname,b.drivername,b.driverphone,e.mobile as emobile,e.username as eusername,e.realname as erealname')
                ->join('ct_rout_order b','b.rid = a.rout_id')
                ->join('ct_user c','c.uid = a.userid')
                ->join('ct_driver d','d.drivid = b.driverid','left')
                ->join('ct_driver e','e.drivid = b.allotid','left')
                ->where('id',$id)
                ->find();
        // 判断订单状态 接单、提货、完成、取消状态不能在进行取消操作
        switch ($result['state']) {
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
            default:
                # code...
                break;
        }
        // 修改订单状态为4取消
        $iscancle = Db::table('ct_city_order')->where('id',$id)->update(['state'=>4]);
        // 判断订单是否取消成功
        if($iscancle){
            // 如果是信用支付 费用归还信用额度
            if ($result['pay_type']=='1') {
                // 查询公司信用额度
                $company = Db::table('ct_company')->field('cid,money')->where('cid',$result['lineclient'])->find();
                // 计算公司信用额度
                $data['money'] = $company['money']+$result['actualprice'];
                // 更新公司信用额度
                $resl = Db::table('ct_company')->where('cid',$company['cid'])->update($data);
            }else{ // 如果是余额、支付宝、微信 费用归还余额
                $data['money'] = $result['money']+$result['actualprice'];
                // 更新个人余额
                $resl = Db::table('ct_user')->where('uid',$result['uid'])->update($data);
            }
            // 如果余额恢复则插入操作日志
            if ($resl) {
                // 插入操作记录
                $content = "取消城配订单".$result['orderid']."，个人余额恢复:".$result['actualprice'];
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
            send_sms_class($userphone,'尊敬的赤途用户,你好! 你的订单号为'.$result['orderid'].'的订单已经取消,感谢你的使用!');                
            // 司机发送短信
            if($driverphone != ''){
                send_sms_class($driverphone,'尊敬的赤途司机,你好! 订单号为'.$result['orderid'].'的订单,用户已经取消,感谢你的使用!');
            }
            // 调度发送短信
            if($dphone != ''){
                send_sms_class($dphone,'尊敬的赤途司机,你好! 订单号为'.$result['orderid'].'的订单,用户已经取消,感谢你的使用!');
            }

            // 插入记录
            $content = "取消城配订单".$result['orderid'];
            $this->hanldlog($this->uid,$content);
            // 返回订单取消状态
            return json(['code'=>true,'message'=>'订单已取消']);
        }else{
            // 返回订单取消状态
            return json(['code'=>true,'message'=>'取消失败']);
        }
    }
    
    /**
     * 修改城配订单价格
     * @Auther: 李渊
     * @Date: 2018.7.24
     * @param  [type] $otype    [请求类型] 1 客户价格 2 承运价格
     * @param  [type] $oid      [订单id]
     * @param  [type] $price    [修改价格]
     * @return [type] [description]
     */
    public function update_price(){
        $postdata = Request::instance()->post();
        // 获取修改的类型 1 客户价格 2 承运价格
        $otype = $postdata['otype'];
        // 获取修改的金额 
        $price = $postdata['price'];
        // 获取修改的订单id
        $id = $postdata['oid'];
        // 查询订单数据
        $result = Db::table('ct_city_order')->where('id',$id)->find();
        // 判断修改的类型 1 客户价格 2 承运价格
        if ($otype=='1') {  //客户
            $update['upprice'] = $price;
            $checkid = $result['user_checkid'];
            $get_price = intval($result['actualprice'])-intval($price);
        }else{ // 承运商
            $update['carr_upprice'] = $price;
            $checkid = $result['carr_checkid'];
            $get_price = intval($result['paymoney'])-intval($price);
        }
        // 判断是否进行对账
        if ( $checkid !='') {
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
        $res = Db::table('ct_city_order')->where('id',$id)->update($update);
        // 判断是否修改成功
        if($res){
            return json(['code'=>true,'message'=>'修改成功']);
        }else{
            return json(['code'=>false,'message'=>'修改失败']);
        }
    }
}
