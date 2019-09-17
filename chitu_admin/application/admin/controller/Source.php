<?php
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Source  extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//    }

    /**
     * 货源信息列表
     * @Auther: 李渊
     * @Date: 2018.6.22
     * @param  [type] $search    [搜索字段 订单号、姓名、电话号码]
     * @param  [type] $starttime [开始时间]
     * @param  [type] $endtime   [结束时间]
     * @return [type]            [满足条件的所有订单数据]
     */
    public function sourcegood(){
        // 搜索字段 订单号、姓名、电话号码
        $search = input('search');
        // 开始时间
        $stime = input('starttime');
        // 结束时间
        $etime = input('endtime');
        // 页码
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
            $condition['o.ordernumber|o.issue_realname|o.issue_phone'] = ['like','%'.$search.'%'];
        }
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $condition['addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] =$stime;
            $pageParam['query']['endtime'] = $etime;
        }
        // 查询条件 货源信息
        $condition['o.ordertype'] = 1;
        // 查询条件 支付成功
        $condition['o.paystate'] = 2;
        // 查询数据
        $result = Db::table("ct_issue_item")
                ->alias('o')
                ->join('ct_cartype car','car.car_id = o.carid','LEFT')
                ->field('o.*,car.carparame')
                ->order('addtime desc')
                ->where($condition)
                ->paginate(18,false,$pageParam);
        // 转数组 
        $list_mes = $result->toArray();
        // 获取数据
        $list = $list_mes['data'];
        // 遍历数据
        foreach ($list as $key => $value) {
            $list[$key] = $value;
            // 起点省
            $start_pro = $value['start_pro'] ? addresidToName($value['start_pro']) : '';
            // 起点市
            $start_city = $value['start_city'] ? addresidToName($value['start_city']) : '';
            // 起点区
            $start_area = $value['start_area'] ? addresidToName($value['start_area']) : '';
            // 起点省
            $end_pro = $value['end_pro'] ? addresidToName($value['end_pro']) : '';
            // 起点市
            $end_city = $value['end_city'] ? addresidToName($value['end_city']) : '';
            // 起点区
            $end_area = $value['end_area'] ? addresidToName($value['end_area']) : '';

            // 起点地址
            $list[$key]['start_address'] = $start_pro.$start_city.$start_area; 
            // 终点地址
            $list[$key]['end_address'] = $end_pro.$end_city.$end_area; 
            // 重量
            $list[$key]['weight'] = $value['weight'] ? ($value['weight']/1000).'吨' : ''; 
            // 立方
            $list[$key]['volume'] = $value['volume'] ? $value['volume'].'方' : ''; 
            // 包车类型
            $list[$key]['carriage'] = $value['carriage'] == 1 ? '包车' : '拼车';

            $list[$key]['addtime'] = $value['addtime']; //下单时间
            // 转数组
            $driver = json_decode($value['driverid'],TRUE);
            // 获取数组个数即查看人数
            $list[$key]['countlook'] = count($driver);
        }
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$list);
        return view('source/goodslist');
    }
    

    /**
     * 发布货源： 发布货源详情
     * @Auther: 李渊
     * @Date: 2018.6.22
     * @param  [type] $id    [订单id]
     * @return [type]        [信息详情数据]
     */
    public function gooddetail(){
        // 订单ID
        $orderid   = input("id");  
        // 查询详情数据
        $detail = Db::table("ct_issue_item")
                ->alias('o')
                ->join('ct_user u','u.uid = o.userid')
                ->join('ct_cartype car','car.car_id = o.carid','LEFT')
                ->field('o.*,u.username,u.realname,u.phone,car.carparame')
                ->where('o.id',$orderid)
                ->find();
        // 起点省
        $start_pro = $detail['start_pro'] ? addresidToName($detail['start_pro']) : '';
        // 起点市
        $start_city = $detail['start_city'] ? addresidToName($detail['start_city']) : '';
        // 起点区
        $start_area = $detail['start_area'] ? addresidToName($detail['start_area']) : '';
        // 起点省
        $end_pro = $detail['end_pro'] ? addresidToName($detail['end_pro']) : '';
        // 起点市
        $end_city = $detail['end_city'] ? addresidToName($detail['end_city']) : '';
        // 起点区
        $end_area = $detail['end_area'] ? addresidToName($detail['end_area']) : '';

        // 起点地址
        $detail['start_address'] = $start_pro.$start_city.$start_area; 
        // 终点地址
        $detail['end_address'] = $end_pro.$end_city.$end_area; 

        // 下单人员
        $detail['realname'] = $detail['realname'] ? $detail['username'] : $detail['realname'];
        // 重量
        $detail['weight'] = $detail['weight'] ? ($detail['weight']/1000).'吨' : '';
        // 立方
        $detail['volume'] = $detail['volume'] ? $detail['volume'].'方' : '';
        // 运输类型
        $detail['carriage'] = $detail['carriage'] == 1 ? '拼车' : '包车';
        // 转数组
        $driver = json_decode($detail['driverid'],TRUE);
        // 获取数组个数即查看人数
        $detail['countlook'] = count($driver);
        // 返回数据
        $this->assign('list',$detail);
        return view('source/goodsdetail');
    }

    /**
     * 发布车源源信息列表
     * @Auther: 李渊
     * @Date: 2018.6.22
     * @param  [type] $search    [搜索字段 订单号、姓名、电话号码]
     * @param  [type] $starttime [开始时间]
     * @param  [type] $endtime   [结束时间]
     * @return [type]            [满足条件的所有订单数据]
     */
    public function sourcecar(){
        // 搜索字段 订单号、姓名、电话号码
        $search = input('search');
        // 开始时间
        $stime = input('starttime');
        // 结束时间
        $etime = input('endtime');
        // 页码
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
            $condition['o.ordernumber|u.realname|u.mobile'] = ['like','%'.$search.'%'];
        }
        if(!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $condition['o.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] =$stime;
            $pageParam['query']['endtime'] = $etime;
        }
        // 查询条件 车源信息
        $condition['ordertype'] = 2;
        // 查询条件 支付成功
        $condition['paystate'] = 2;
        // 查询数据
        $result = Db::table("ct_issue_item")
                ->alias('o')
                ->join('__CARTYPE__ c','o.carid = c.car_id')
                ->join('__DRIVER__ u','u.drivid=o.userid')
                ->field('o.id,o.ordernumber,o.paystate,o.loaddate,o.start_city,o.start_area,o.driverid,
                    o.end_city,o.end_area,c.carparame,o.addtime,o.orderstate,o.referprice,u.realname,u.username,u.mobile')
                ->order('addtime desc')
                ->where($condition)
                ->paginate(50,false,$pageParam);
        $list_mes = $result->toArray();
        $list = $list_mes['data'];
        foreach ($list as $key => $value) {
            $driver = json_decode($value['driverid'],TRUE);
            $list[$key]['realname'] = $value['realname']==''?$value['username']:$value['realname'];  // 姓名
            $list[$key]['startcity'] = detailadd('',$value['start_city'],$value['start_area']);  //起点城市
            $list[$key]['endcity'] = detailadd('',$value['end_city'],$value['end_area']); // 终点城市
            $list[$key]['addtime'] = $value['addtime']; //下单时间
            $list[$key]['countlook'] =count($driver);
        }
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$list);
        return view('source/carlist');
    }

    /**
     * 发布货源： 发布车源详情
     * @Auther: 李渊
     * @Date: 2018.6.22
     * @param  [type] $id    [订单id]
     * @return [type]        [信息详情数据]
     */
    public function cardetail(){
        // 订单ID
        $orderid   = input("id");  
        // 查询数据
        $detail = Db::table("ct_issue_item")
                ->alias('o')
                ->join('__CARTYPE__ c','o.carid = c.car_id')
                ->join('__DRIVER__ u','u.drivid = o.userid')
                ->field('o.*,u.username,u.realname,u.mobile,c.carparame')
                ->where('o.id',$orderid)
                ->find();
        // 下单人
        $detail['realname'] = $detail['realname']==''?$detail['username']:$detail['realname'];
        // 起点城市
        $detail['startcity'] = addresidToName($detail['start_city']);
        // 终点城市
        $detail['endcity'] = addresidToName($detail['end_city']);  
        // 输出数据
        $this->assign('list',$detail);
        return view('source/cardetail');
    }
}
