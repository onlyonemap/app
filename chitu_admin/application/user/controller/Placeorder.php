<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request;
use  think\Loader; //加载模型

class Placeorder extends Base{

    /**
     * 整车发布-计费费用、公里数、行车时长
     * 根据两个城市计算里程、行车时长
     * @auther 李渊
     * @date 2018.6.15
     * @param [string] startcity_str 起点城市
     * @param [string] endcity_str 终点城市
     * @param [int] carid 车型的ID
     * @return [type] [description]
     */
    public function arrtime_city($startcity_str, $endcity_str, $carid)
    {
        // 查询系数类型 2 整车
        $type = 2;
        // 总运费
        $allmoney = 0;
        // 起步价
        $startPrice = 0;
        // 运费
        $freight = 0;

        // 每公里单价
        $onePrice = 0;
        // 行车时长
        $gethour = 0;
        // 里程数
        $km = 0;
        // 起步价系数
        $scale_startprice = 1;
        // 里程偏离系数
        $scale_km = 1;
        // 单公里价格系数
        $scale_price_km = 1;
        // 查找选定的车型
        $car_type = Db::table('ct_cartype')->where('car_id',$carid)->find();
        // 查找系数比例
        $scale = Db::table('ct_price_setting')->where('type',$type)->find();
        // 如果有系数值则写入
        if($scale['type']){
            $scale_startprice = $scale['scale_startprice'];
            $scale_km = $scale['scale_km'];
            $scale_price_km = $scale['scale_price_km'];
        }
        // 起点城市经纬度
        $start_action = bd_local($type='1',$startcity_str,$area='');//经纬度
        // 终点城市经纬度
        $end_action = bd_local($type='1',$endcity_str,$area='');//经纬度
        // 获取百度返回的结果
        $list = direction($start_action['lat'], $start_action['lng'], $end_action['lat'], $end_action['lng']);
        // 解析结果得到公里数和行车时长
        $finally = $list['distance']/1000;
        // $gethour = $list['duration']/60/60;
        $gethour = round($finally/65);
        $gethour = $gethour < 1 ? 1 : $gethour;
        // 乘以公里系数后的公里数
        $km = mileage_interval(2,(int)$finally);
        // 计算起步价
        $startPrice = $car_type['lowprice']*$scale_startprice;
        // 运费 公里数*单价
        $freight = $km*$car_type['costkm']*$scale_price_km;
        // 总运费
        $allmoney = $startPrice+$freight;

        // 行车小时
        $data['hour'] = round($gethour);
        // 预计费用
        $data['countprice'] = round($allmoney);
        // 预计费用
        $data['carname'] = $car_type['carparame'];

        return $data;
    }

    /**
     * 城配计算价格
     * @auther 李渊
     * @date 2018.12.13
     * @param [string] startcity_str 起点城市
     * @param [string] endcity_str 终点城市
     * @param [int] carid 车型的ID
     * @return [type] [description]
     */
    public function city_price($city_id, $end_city)
    {
        // 查询城配是否开通此城市
        $result = DB::table('ct_city_cost')->where(array('c_city' => $city_id, 'delstate' => 1))->find();

        $price = array();
        // 如果开通
        if (!empty($result) && ($city_id == $end_city)) {
            // 查找选定的车型
            $car_type = Db::table('ct_cartype')->where('car_id',1)->find();
            // 查找系数比例
            $scale = Db::table('ct_price_setting')->where('type',1)->find();
            // 如果有系数值则写入
            $scale_startprice = $scale['scale_startprice'];
            $scale_km = $scale['scale_km'];
            $scale_price_km = $scale['scale_price_km'];
            $scale_pickgood = $scale['scale_pickgood'];
            $scale_sendgood = $scale['scale_sendgood'];
            $scale_multistore = $scale['scale_multistore'];
            $scale_discount = $scale['scale_discount'];


            // 计算起步价
            $startPrice = $car_type['lowprice']*$scale_startprice;
            // 装货费
            $pickPrice = $car_type['pickup']*$scale_pickgood;
            // 卸货费
            $sendPrice = $car_type['unload']*$scale_sendgood;
            // 装卸费
            $psPrice = $pickPrice+$sendPrice;
            // 多点提配费用
            $multistorePrice = 0;
            // 里程费 公里数*单价
            $freight = 0;

            // 总运费 = 起步价 + 里程费 + 装卸费 + 多点提配费
            $allmoney = $startPrice+$freight+$psPrice+$multistorePrice;
            // 折扣价
            $discount = $allmoney*$scale_discount;

            $city = addresidToName($city_id); // 优惠价
            $price[0]['start_city'] = $city; // 优惠价
            $price[0]['end_city'] = $city; // 优惠价
            $price[0]['hour'] = 2; // 优惠价
            $price[0]['countprice'] = round($discount); // 优惠价
            $price[0]['carname'] = '4.2米冷藏厢车'; // 优惠价

            return $price;
        }

        return $price;
    }

    /**
     * 整车计算价格
     * @auther 李渊
     * @date 2018.12.13
     * @param [string] startcity_str 起点城市
     * @param [string] endcity_str 终点城市
     * @param [int] carid 车型的ID
     * @return [type] [description]
     */
    public function vehicle_price($start_id,$end_id)
    {
        // 起点城市
        $start_city = addresidToName($start_id);
        // 终点城市
        $end_city = addresidToName($end_id);
        // 整车
        $vehicle = array();
        // 车型id
        // $carID = array(1,2,3,4,5,6,7,9);
        // 循环
        // foreach ($carID as $k => $val) {
        $res = $this->arrtime_city($start_city, $end_city, 1);
        $vehicle[0]['start_city'] = $start_city;
        $vehicle[0]['end_city'] = $end_city;
        $vehicle[0]['hour'] = $res['hour'];
        $vehicle[0]['countprice'] = $res['countprice'];
        $vehicle[0]['carname'] = $res['carname'];
        // }

        return $vehicle;
    }

    /**
     * 零担计算价格
     * @auther 李渊
     * @date 2018.12.13
     * @param [string] startcity_str 起点城市
     * @param [string] endcity_str 终点城市
     * @param [int] carid 车型的ID
     * @return [type] [description]
     */
    public function line_price($start_id, $end_id)
    {
        // 零担
        $order_type = 1;
        // 排序规则：1 时效 2 价格
        switch ($order_type) {
            case '1':
                $order = "s.trunkaging asc, g.deptime asc";
                break;
            case '2':
                $order = "s.price asc, g.deptime asc";
                break;
            default:
                $order = "g.deptime asc";
                break;
        }

        // 开始时间
        $begintoday = mktime(0,0,0,date('m'),date('d')+1,date('Y'));

        // 结束时间
        $endtoday = mktime(0,0,0,date('m'),date('d')+7,date('Y'))-1;
        if ($end_id){
            $result = Db::table("ct_already_city")->field('city_id')->where(array('start_id' => $start_id,'end_id'=>$end_id))->find();
        }else{
            $result1 = Db::table("ct_already_city")->field('city_id')->where(array('start_id' => $start_id))->select();
            $result =$this->multiToSingle($result1);
        }

//        var_dump($result);
//        exit();
        // 查询开通的干线城市
//        var_dump($result);
//        exit();
        // 查询条件 计划发车时间 在七天以内的
        $condition1['g.deptime'] = array(array('gt',$begintoday),array('lt', $endtoday));
        // 查询条件 状态（1进行中 2已完成 3一直在）
        $condition1['g.status'] = ['IN','1,3'];

//		exit();
        // 查询条件 已开通城市 线路ID
//		$condition['s.linecityid'] = $result['city_id'];
        // 起点城市
        $start_city = addresidToName($start_id);
        // 终点城市
        $end_city = addresidToName($end_id);
        $list = array();
        // 如果有此线路则查询对应的班次信息

        if(!empty($result)){
            $line = [];
            if(empty($end_id)){
                foreach ($result as $key =>$value){
                    $line1 = Db::table("ct_shift")
                        ->alias('s')
                        ->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
                        ->field('s.sid,s.picktype,s.beginareaid,s.endareaid,s.sendtype,s.picksite,s.stime,s.sphone,s.sendsite,s.dtime,s.tphone,s.companyid,s.shiftnumber,s.trunkaging,s.price,s.timestrat,s.timeend,s.lowprice,s.shiftstate,s.pmoney,s.smoney,s.weekday,s.discount,g.slid,g.deptime')
                        ->where($condition1)
                        ->where('linecityid',$value)
                        ->order($order)
                        ->select();
                    $line = array_merge($line,$line1);
//                var_dump($line);
                }
            }
            // 遍历数据
            foreach ($line as $key => $value) {
                // 查询干线起点城市对应的提货区域
                $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['companyid'],'type'=>1,'province'=>$start_id))->find();
                // 查询干线终点城市对应的配送区域
                $pei_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['companyid'],'type'=>2,'province'=>$end_id))->find();
                // 返回起点城市名称
                $list[$key]['start_city'] = $start_city;
                // 返回终点城市名称
                $list[$key]['end_city'] = $end_city;
                // 返回起点城市id
                $list[$key]['start_id'] = $start_id;
                // 返回终点城市id
                $list[$key]['end_id'] = $end_id;
                // 返回干线班次
                $list[$key]['shiftnumber'] = $value['shiftnumber'];
                // 返回时效
                $list[$key]['trunkaging'] = $value['trunkaging'];
                // 返回干线价格每公斤多少元
                $list[$key]['price'] = $value['price'];
                // 返回折扣
                $list[$key]['discount'] = $value['discount'];
                // 返回干线折扣后价格每公斤多少元
                $list[$key]['discount_price'] = round($value['price']*$value['discount'],2);
                // 返回干线班次队列id
                $list[$key]['slid'] = $value['slid'];
                // 返回干线班次id
                $list[$key]['sid'] = $value['sid'];
                // 返回发车时间
                $list[$key]['deptime'] = $value['deptime'];
                // 返回干线最低收费
                $list[$key]['lowprice'] = sprintf("%.0f",$value['lowprice']);
                // 1 平台添加 2 自主添加
                $list[$key]['shiftstate'] = $value['shiftstate'];
                // 自主添加提货费
                $list[$key]['pmoney'] = $value['shiftstate'] == 1 ? $ti_arr['price'] : $value['pmoney'];
                $list[$key]['pmoney'] = round($list[$key]['pmoney']);
                // 自主添加配送费
                $list[$key]['smoney'] = $value['shiftstate'] == 1 ? $pei_arr['price'] : $value['smoney'];
                $list[$key]['smoney'] = round($list[$key]['smoney']);
                // 自主添加班期
                $list[$key]['weekday'] = $value['weekday'];

                //收货地址
                $list[$key]['picksite'] = $value['picksite'];
                //收货时间段
                $list[$key]['stime'] = $value['stime'];
                //收货联系人
                $list[$key]['sphone'] = $value['sphone'];
                //卸货地址
                $list[$key]['sendsite'] = $value['sendsite'];
                //提货时间段
                $list[$key]['dtime'] = $value['dtime'];
                //提货联系人
                $list[$key]['tphone'] = $value['tphone'];

                $list[$key]['picktype'] = $value['picktype'];

                $list[$key]['sendtype'] =$value['sendtype'];

                // 获取当天下午五点的时间戳
                $new_wu = strtotime(date('Y-m-d 17:00:00'));
                // 获取此刻时间
                $new = time();
                // 班线可用状态 1 不可用 2 正常显示
                $timestrat = '2';
                $list[$key]['time_status'] = $timestrat;
                if ($value['shiftstate'] =='1') {
                    $time0 = 0;
                    if($new >= $new_wu && strtotime(date('Y-m-d',strtotime('+1 day'))) == $value['deptime']){
                        $time0 = false; // 已超过五点
                    }else{
                        $time0 = true; // 正常使用
                    }
                    // 发出时段在 08:00 - 12:00 并且 查询时间大于发车时间42小时才可以看到班次 最晚提货时间提前 18 小时
                    $time1 = true;
                    if($value['timestrat'] >= '08:00' && $value['timestrat'] <= '12:00' && $new > ($value['deptime']-42*60*60) ){
                        $time1 = false;

                    }
                    // 发出时段在 13:00 - 15:00 并且 查询时间大于发车时间24小时才可以看到班次 最晚提货时间提前 7 小时
                    $time2 = true;
                    if($value['timestrat'] >= '13:00' && $value['timestrat'] <= '17:00' && $new > ($value['deptime']-24*60*60) ){
                        $time2 = false;
                    }
                    // 发出时段在 18:00 - 22:00 并且 查询时间大于发车时间27小时才可以看到班次 最晚提货时间提前 7 小时
                    $time3 = true;
                    if($value['timestrat'] >= '18:00' && $value['timestrat'] <= '22:00' && $new > ($value['deptime']-27*60*60) ){
                        $time3 = false;
                    }

                    // 所有状态均可用才显示班次
                    if($time0 && $time1 && $time2 && $time3 ){
                        $list[$key]['time_status'] = '2';
                    }else{
                        $list[$key]['time_status'] = '1';
                    }
                }
            }
        }
        return $list;
    }
    public function multiToSingle($arr, $delimiter = '->',$key = ' ') {
        $resultAry = array();
        if (!(is_array($arr) && count($arr)>0)) {
            return false;
        }
        foreach ($arr AS $k=>$val) {
            $newKey = trim($key . $k . $delimiter);
            if (is_array($val) && count($val)>0) {
                $resultAry = array_merge($resultAry, $this->multiToSingle($val, $delimiter, $newKey));
            } else {
                $resultAry[] =  $val;
            }
        }
        return $resultAry;
    }

    public function bulk_line($start_id,$end_id,$start_area,$end_area){
        // 零担
        $order_type = 1;
        // 排序规则：1 时效 2 价格
        switch ($order_type) {
            case '1':
                $order = "s.trunkaging asc, g.deptime asc";
                break;
            case '2':
                $order = "s.price asc, g.deptime asc";
                break;
            default:
                $order = "g.deptime asc";
                break;
        }
        $condit = [];
        // 开始时间
//        $begintoday = mktime(0,0,0,date('m'),date('d')+1,date('Y'));
        $begintoday = time();
        // 结束时间
        $endtoday = mktime(0,0,0,date('m'),date('d')+7,date('Y'))-1;
        if ($start_id && $end_id){
            $result = Db::table("ct_already_city")->field('city_id')->where(array('start_id' => $start_id,'end_id'=>$end_id))->find();
//                 var_dump($result);
                  // 查询条件 计划发车时间 在七天以内的
                  $condition['g.deptime'] = array(array('gt',$begintoday),array('lt', $endtoday));
                  // 查询条件 状态（1进行中 2已完成 3一直在）
                  $condition['g.status'] = ['IN','1,3'];
                  // 查询条件 已开通城市 线路ID
                  $condition['s.linecityid'] = $result['city_id'];
                  if (!empty($start_area)){
                      $condit['s.beginareaid'] = $start_area;
                  }
                  if ( !empty($end_area)){
                      $condit['s.endareaid'] = $end_area;
                  }
                  // 起点城市
                  $start_city = addresidToName($start_id);
                  // 终点城市
                  $end_city = addresidToName($end_id);
                  $list = array();

                  // 如果有此线路则查询对应的班次信息
                  if(!empty($result)){

                      $line = Db::table("ct_shift")
                          ->alias('s')
                          ->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
                          ->field('s.sid,s.begincityid,s.beginareaid,s.endareaid,s.endcityid,s.transit,s.istransit,s.picktype,s.sendtype,s.picksite,s.stime,s.sphone,s.sendsite,s.dtime,s.tphone,s.companyid,s.shiftnumber,s.trunkaging,s.price,s.timestrat,s.timeend,s.lowprice,s.shiftstate,s.pmoney,s.smoney,s.weekday,s.discount,g.slid,g.deptime')
                          ->where($condition)
                          ->where($condit)
                          ->order($order)
                          ->select();
                      if ($line){
                          $line = $line;
                      }else{
                          $line0 = Db::table("ct_shift")
                              ->alias('s')
                              ->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
                              ->field('s.sid,s.begincityid,s.beginareaid,s.endareaid,s.endcityid,s.transit,s.istransit,s.picktype,s.sendtype,s.picksite,s.stime,s.sphone,s.sendsite,s.dtime,s.tphone,s.companyid,s.shiftnumber,s.trunkaging,s.price,s.timestrat,s.timeend,s.lowprice,s.shiftstate,s.pmoney,s.smoney,s.weekday,s.discount,g.slid,g.deptime')
                              ->where($condition)
                              ->order($order)
                              ->select();
                          $line = $line0;
                      }

                          $list = $this->common($line,$start_id,$end_id,$start_city,$end_city);
                  }
            if (!empty($list)){
                return $list;
            }else{
                    $start_city = addresidToName($start_id);
                    // 终点城市
                    $end_city = addresidToName($end_id);
                    $result1 = Db::table("ct_already_city")->field('city_id')->where('start_id',$start_id)->select();
                    $result =$this->multiToSingle($result1);
                    // 查询条件 计划发车时间 在七天以内的
                    $condition1['g.deptime'] = array(array('gt',$begintoday),array('lt', $endtoday));
                    // 查询条件 状态（1进行中 2已完成 3一直在）
                    $condition1['g.status'] = ['IN','1,3'];
                    $list = array();
                    if(!empty($result)){
                        $line1 = [];
                        foreach ($result as $key =>$value){
                            $line2 = Db::table("ct_shift")
                                ->alias('s')
                                ->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
                                ->field('s.sid')
                                ->where($condition1)
                                ->where('linecityid',$value)
                                ->order($order)
                                ->select();
                            $line1 = array_merge($line1,$line2);
                        }
//                    $startline= $this->common($line1,$start_id,$end_id,$start_city,$end_city);
                    }
                    $startline = $this->multiToSingle($line1);
                    $result2 = Db::table("ct_already_city")->field('city_id')->where('end_id', $end_id)->select();
                    $result3 =$this->multiToSingle($result2);
                    $list = array();
                    if(!empty($result3)){
                        $line3 = [];
                        foreach ($result3 as $key =>$value){
                            $line4 = Db::table("ct_shift")
                                ->alias('s')
                                ->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
                                ->field('s.sid')
                                ->where($condition1)
                                ->where('linecityid',$value)
                                ->order($order)
                                ->select();
                            $line3 = array_merge($line4,$line3);
                        }
                        $endline = $this->multiToSingle($line3);
                    }
                    $start_line = [];
                    $end_line = [];
                    if (empty($startline) ||empty($endline)){
                        return $list = [];
                    }else{

                        foreach($startline as $key => $value){
                            $start_line1 =  Db::table('ct_shift')->where('sid',$value)->where('shiftstate',1)->where('istransit',1)->select();
                            $start_line = array_merge($start_line1,$start_line);
                        }
                        foreach($endline as $key => $value){
                            $end_line1 =  Db::table('ct_shift')->where('sid',$value)->where('shiftstate',1)->where('istransit',1)->select();
                            $end_line = array_merge($end_line1,$end_line);
                        }
                        $lineinfo = [];
                        for ($i = 0;$i<count($start_line);$i++){
                            for ($j = 0;$j<count($end_line);$j++){
                                $deptime = Db::table('ct_shift_log')->where(array('shiftid'=>$start_line[$i]['sid'],'status'=>1))->find();
                                $deptim = Db::table('ct_shift_log')->where(array('shiftid'=>$end_line[$j]['sid'],'status'=>1))->find();
                                if ($start_line[$i]['endcityid'] === $end_line[$j]['begincityid'] && $start_line[$i]['begincityid']!=$start_line[$i]['endcityid'] && $deptime['deptime'] > $deptim['deptime'] && $deptime['deptime'] <= $deptime['deptime']+$start_line[$i]['trunkaging']*24*60*60){
                                    $companyname = '赤途（上海）供应链管理有限公司';
                                    $ShiftNumber = 'CTMT'.rand(100,1000);

                                    $nline = $this->saveconnect($start_line[$i]['sid'],$end_line[$j]['sid'],$end_line[$j]['begincityid'],$companyname,$ShiftNumber,$condition1,$order);
                                    $lineinfo = array_merge($nline,$lineinfo);
                                }
                            }
                        }
                    }
                    $list = $this->common($lineinfo,$start_id,$end_id,$start_city,$end_city);
                    return $list;


                }
        }elseif(empty($end_id)){
            $result1 = Db::table("ct_already_city")->field('city_id')->where(array('start_id' => $start_id))->select();
            $result =$this->multiToSingle($result1);

            // 查询条件 计划发车时间 在七天以内的
            $condition1['g.deptime'] = array(array('gt',$begintoday),array('lt', $endtoday));
            // 查询条件 状态（1进行中 2已完成 3一直在）
            $condition1['g.status'] = ['IN','1,3'];
            // 起点城市
            $start_city = addresidToName($start_id);
            // 终点城市
            $end_city = addresidToName($end_id);
            $list = array();
            if(!empty($result)){
                $line = [];
                if(empty($end_id)){
                    foreach ($result as $key =>$value){
                        $line1 = Db::table("ct_shift")
                            ->alias('s')
                            ->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
                            ->field('s.sid,s.begincityid,s.endcityid,s.beginareaid,s.endareaid,s.transit,s.istransit,s.picktype,s.sendtype,s.picksite,s.stime,s.sphone,s.sendsite,s.dtime,s.tphone,s.companyid,s.shiftnumber,s.trunkaging,s.price,s.timestrat,s.timeend,s.lowprice,s.shiftstate,s.pmoney,s.smoney,s.weekday,s.discount,g.slid,g.deptime')
                            ->where($condition1)
                            ->where('linecityid',$value)
                            ->order($order)
                            ->select();
                        $line = array_merge($line,$line1);
                    }
                }
                $list= $this->common($line,$start_id,$end_id,$start_city,$end_city);
            }
            return $list;
        }

    }

    /*
     *
     * */
        public function common($line,$start_id,$end_id,$start_city,$end_city){
            // 遍历数据
            if ($line){
                foreach ($line as $key => $value) {
                    // 查询干线起点城市对应的提货区域
                    $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['companyid'],'type'=>1,'province'=>$start_id))->find();
//                   var_dump($ti_arr);
                    // 查询干线终点城市对应的配送区域
                    $pei_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['companyid'],'type'=>2,'province'=>$end_id))->find();
//                    var_dump($pei_arr);
                    // 返回起点城市名称
                    $list[$key]['start_city'] = addresidToName($value['begincityid']);
                    // 返回终点城市名称
                    $list[$key]['end_city'] = addresidToName($value['endcityid']);
                    // 返回起点城市区名称
                    $list[$key]['start_area'] = addresidToName($value['beginareaid']);
                    // 返回终点城市区名称
                    $list[$key]['end_area'] = addresidToName($value['endareaid']);
                    // 返回起点城市id
                    $list[$key]['start_id'] = $value['begincityid'];
                    // 返回终点城市id
                    $list[$key]['end_id'] = $value['endcityid'];
                    // 返回干线班次
                    $list[$key]['shiftnumber'] = $value['shiftnumber'];
                    // 返回时效
                    $list[$key]['trunkaging'] = $value['trunkaging'];
                    // 返回干线价格每公斤多少元
                    $list[$key]['price'] = $value['price'];
                    // 返回折扣
                    $list[$key]['discount'] = $value['discount'];
                    // 返回干线折扣后价格每公斤多少元
                    $list[$key]['discount_price'] = round($value['price']*$value['discount'],2);
                    // 返回干线班次队列id
                    $list[$key]['slid'] = $value['slid'];
                    // 返回干线班次id
                    $list[$key]['sid'] = $value['sid'];
                    // 返回发车时间
                    $list[$key]['deptime'] = $value['deptime'];
                    // 返回干线最低收费
                    $list[$key]['lowprice'] = sprintf("%.0f",$value['lowprice']);
                    // 1 平台添加 2 自主添加
                    $list[$key]['shiftstate'] = $value['shiftstate'];
                    // 自主添加提货费
                    $list[$key]['pmoney'] = $value['shiftstate'] == 1 ? $ti_arr['price'] : $value['pmoney'];
                    $list[$key]['pmoney'] = round($list[$key]['pmoney']);
                    // 自主添加配送费
                    $list[$key]['smoney'] = $value['shiftstate'] == 1 ? $pei_arr['price'] : $value['smoney'];
                    $list[$key]['smoney'] = round($list[$key]['smoney']);
                    // 自主添加班期
                    $list[$key]['weekday'] = $value['weekday'];

                    //收货地址
                    $list[$key]['picksite'] = $value['picksite'];
                    //收货时间段
                    $list[$key]['stime'] = $value['stime'];
                    //收货联系人
                    $list[$key]['sphone'] = $value['sphone'];
                    //卸货地址
                    $list[$key]['sendsite'] = $value['sendsite'];
                    //提货时间段
                    $list[$key]['dtime'] = $value['dtime'];
                    //提货联系人
                    $list[$key]['tphone'] = $value['tphone'];

                    $list[$key]['picktype'] = $value['picktype'];

                    $list[$key]['sendtype'] =$value['sendtype'];

                    $list[$key]['transit'] = addresidToName($value['transit']);

                    $list[$key]['istransit'] = $value['istransit'];
                    // 获取当天下午五点的时间戳
                    $new_wu = strtotime(date('Y-m-d 17:00:00'));
                    // 获取此刻时间
                    $new = time();
                    // 班线可用状态 1 不可用 2 正常显示
                    $timestrat = '2';
                    $list[$key]['time_status'] = $timestrat;

                    if ($value['shiftstate'] =='1') {
                        $time0 = 0;
                        if($new >= $new_wu && strtotime(date('Y-m-d',strtotime('+1 day'))) == $value['deptime']){
                            $time0 = false; // 已超过五点
                        }else{
                            $time0 = true; // 正常使用
                        }
                        // 发出时段在 08:00 - 12:00 并且 查询时间大于发车时间42小时才可以看到班次 最晚提货时间提前 18 小时
                        $time1 = true;
                        if($value['timestrat'] >= '08:00' && $value['timestrat'] <= '12:00' && $new > ($value['deptime']-2*60*60) ){
                            $time1 = false;

                        }
                        // 发出时段在 13:00 - 15:00 并且 查询时间大于发车时间24小时才可以看到班次 最晚提货时间提前 7 小时
                        $time2 = true;
                        if($value['timestrat'] >= '13:00' && $value['timestrat'] <= '17:00' && $new > ($value['deptime']-2*60*60) ){
                            $time2 = false;
                        }
                        // 发出时段在 18:00 - 22:00 并且 查询时间大于发车时间27小时才可以看到班次 最晚提货时间提前 7 小时
                        $time3 = true;
                        if($value['timestrat'] >= '18:00' && $value['timestrat'] <= '22:00' && $new > ($value['deptime']-2*60*60) ){
                            $time3 = false;
                        }

                        // 所有状态均可用才显示班次
                        if($time0 && $time1 && $time2 && $time3 ){
                            $list[$key]['time_status'] = '2';
                        }else{
                            $list[$key]['time_status'] = '1';
                        }

                    }
                }
                return $list;
            }else{
                return $list = [];
            }
        }

    /**
     * 特价整车
     * @auther 李渊
     * @date 2018.12.13
     * @param [string] startcity_str 起点城市
     * @param [string] endcity_str 终点城市
     * @param [int] carid 车型的ID
     * @return [type] [description]
     */
    public function special_vehicle($start_id, $end_id)
    {
        // 筛选起点城市
        if ($start_id !='') {
            $condition['o.start_city'] = $start_id;
        }
        // 筛选终点城市
        if ($end_id !='') {
            $condition['o.end_city'] = $end_id;
        }
        // 司机发布 车源
        $condition['o.ordertype'] = 2;
        // 查询条件 已支付的车源订单
        $condition['o.paystate'] = 2;
        // 查询条件 已支付的车源订单
        $condition['o.orderstate'] = 1;
        // 查询数据
        $result = Db::table("ct_issue_item")
            ->alias('o')
            ->join('ct_cartype car','car.car_id = o.carid','LEFT')
            ->field('o.start_city,o.start_area,o.end_city,o.end_area,o.carid,o.weight,o.volume,o.loaddate,o.referprice,o.down_price,o.remark,car.carparame,car.allweight,car.allvolume')
            ->order('addtime desc')
            ->where($condition)
            ->select();

        // 遍历数据
        foreach ($result as $key => $value) {
            // 返回起点城市
            $result[$key]['startCity'] = addresidToName($value['start_city']);
            // 返回终点城市
            $result[$key]['endCity'] = addresidToName($value['end_city']);
            // 返回起点城市
            $result[$key]['startArea'] = addresidToName($value['start_area']);
            // 返回终点城市
            $result[$key]['endArea'] = addresidToName($value['end_area']);
            // 重量
            $result[$key]['weight'] = $value['weight'] ? $value['weight']/1000 : $value['allweight'];
            // 体积
            $result[$key]['volume'] = $value['volume'] ? $value['volume'] : $value['allvolume'];
            // 总价
            $result[$key]['price'] = $value['referprice'] + $value['down_price'];
            // 判断是否同城 1 同城 2 城际
            if ($value['start_city'] == $value['end_city']) {
                $result[$key]['is_same_city'] = 1;
            } else {
                $result[$key]['is_same_city'] = 2;
            }
        }

        return $result;
    }

    /**
     * 符合起点城市、终点城市的零担、整车、城配数据列表
     * @auther: 李渊
     * @date: 2018.12.13
     * @param  [String] [token] 	 [用户令牌]
     * @param  [String] [start_city] [起点城市]
     * @param  [String] [end_city] 	 [终点城市]
     * @return [type] [description]
     */
    public function datalist(){
        // 获取用户令牌
        $token = input("token");
        // 获取起点城市
        $start_city = input("start_city");
        // 获取终点城市
        $end_city = input("end_city");
        //获取起点城市区
        $start_area = input('start_area');
        //获取终点城市区
        $end_area = input('end_area');
        // 验证参数
//        if(empty($token)){
//            return json(['code'=>'1000','message'=>'参数错误']);
//        }
//        $check_result = $this->check_token($token);//验证令牌
//
//        if($check_result['status'] =='1'){
//            return json(['code'=>'1007','message'=>'非法请求']);
//        }elseif($check_result['status'] =='2'){
//            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
//        }else{
//            $user_id = $check_result['user_id'];
//        }

        // 零担
        $data['line'] = $this->bulk_line($start_city, $end_city,$start_area,$end_area);
        // 整车
//		$data['vehicle'] = $this->vehicle_price($start_city, $end_city);
        // 城配
//		$data['city'] = $this->city_price($start_city, $end_city);
        // 特价整车
        $data['special_vehicle'] = $this->special_vehicle($start_city, $end_city);
        return json(['code'=>'1002','message'=>'查询成功','data'=>$data]);
    }

    /*
     * 零担线路动态内容
     * */
    public function dynamic_content(){
        $type = input('type');//1零担 2整车 3城配
        switch ($type){
            case '1':
                $list = Db::table('ct_dynamic_content')
                    ->field('content')
                    ->where('state',1)
                    ->where('type',$type)
                    ->order('createtime DESC')
                    ->limit(3)
                    ->select();
                break;
            case '2':
                $list = Db::table('ct_dynamic_content')
                    ->field('content')
                    ->where('state',1)
                    ->where('type',$type)
                    ->order('createtime DESC')
                    ->limit(3)
                    ->select();
                break;
            case '3':
                $list = Db::table('ct_dynamic_content')
                    ->field('content')
                    ->where('state',1)
                    ->where('type',$type)
                    ->order('createtime DESC')
                    ->limit(3)
                    ->select();
                break;
            default:
                break;
        }
        if ($list){
            return json(['code'=>'1001','message'=>'查询成功','data'=>$list]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }

    }

    public function saveconnect($startline,$endline,$transit,$companyname,$ShiftNumber,$condition1,$order){
        $deptime = Db::table('ct_shift_log')->where(array('shiftid'=>$startline,'status'=>1))->find();
        $deptim = Db::table('ct_shift_log')->where(array('shiftid'=>$endline,'status'=>1))->find();
        $startcity = Db::table('ct_shift')
            ->alias('s')
            ->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
            ->field('s.*,g.slid,g.deptime')
            ->where('sid',$startline)
            ->where('istransit',1)
            ->where('shiftstate',1)
            ->where($condition1)
            ->find();
        $endcity = Db::table('ct_shift')
            ->alias('s')
            ->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
            ->field('s.*,g.slid,g.deptime')
            ->where('sid',$endline)
            ->where('istransit',1)
            ->where('shiftstate',1)
            ->where($condition1)
            ->find();
        $newline = [];
        //组合线路
        //每公斤价格
        $newline['price'] = ($startcity['price'] + $endcity['price']) * 0.9;

        //班次号
        $newline['shiftnumber'] = $ShiftNumber;
        //开始城市
        $newline['begincityid'] = $startcity['begincityid'];
        //结束城市
        $newline['endcityid'] = $endcity['endcityid'];
        //折扣
        $newline['discount'] = 1;
        //最低收费
        $newline['lowprice'] = $startcity['lowprice'] > $endcity['lowprice'] ? $startcity['lowprice'] : $endcity['lowprice'];
        //抛货标准价
        $newline['eprice'] = round(($startcity['price'] + $endcity['price']) * 0.9*1000/2.5);
        //公司id
        $companyid = Db::table('ct_company')->field('cid')->where('name',$companyname)->find();

        $newline['companyid'] = $companyid['cid'];
        //
        $newline['timestrat'] = $startcity['timestrat'];

        $newline['timeend'] = $startcity['timeend'];

        $newline['shiftstate'] = 1;

        $newline['picksite'] = $startcity['picksite'];

        $newline['stime'] = $startcity['stime'];

        $newline['sphone'] = $startcity['sphone'];

        $newline['sendsite'] = $endcity['sendsite'];

        $newline['dtime'] = $endcity['dtime'];

        $newline['tphone'] = $endcity['tphone'];

        $newline['picktype'] = 1;

        $newline['sendtype'] = 1;

        $newline['aid'] = 1;

        $newline['addtime'] = time();

        $newline['whethertoopen'] = 1;

        $newline['freetonnage'] = $startcity['freetonnage'];

        $newline['arrivetimestart'] =$endcity['arrivetimestart'];

        $newline['arrivetimeend'] =$endcity['arrivetimeend'];

        $find_city = Db::table('ct_already_city')->where(array('start_id'=>$startcity['begincityid'],'end_id'=>$endcity['endcityid']))->find();
        // 如果不存在该始发-终点的线路则添加
        if ($find_city=='') {
            // 起点城市
            $city_data['start_id'] = $startcity['begincityid'];
            // 终点城市
            $city_data['end_id'] = $endcity['endcityid'];
            // 添加时间
            $city_data['add_time'] = time();
            // 添加到城市库里
            $city_id = Db::table('ct_already_city')->insertGetId($city_data);
        }else{
            $city_id = $find_city['city_id'];
        }

        $newline['linecityid'] = $city_id;

        $newline['dewin'] = $startcity['dewin'];

        $newline['selfdeliverydeadline'] = $startcity['selfdeliverydeadline'];

        $newline['morningtime'] = $endcity['morningtime'];

        $newline['endprovinceid'] = $endcity['endprovinceid'];

        $newline['endareaid'] = $endcity['endareaid'];

        $newline['endaddress'] = $endcity['endaddress'];

        $newline['beginprovinceid'] = $startcity['beginprovinceid'];

        $newline['beginareaid'] = $startcity['beginareaid'];

        $newline['beginaddress'] = $startcity['beginaddress'];
        //中转城市id
        $newline['transit'] = $transit;
        //是否中转
        $newline['istransit'] = 2;
        if ($deptime['endtime']< $deptim['deptime']){
            $endtime = $deptim['deptime']-$deptime['endtime']+($startcity['trunkaging'] + $endcity['trunkaging'])*24*60*60;
         //时效
        $newline['trunkaging'] = (($deptim['deptime']-$deptime['endtime'])/24/60/60+$startcity['trunkaging'] + $endcity['trunkaging']).'天';
        }elseif($deptime['endtime'] = $deptim['deptime'] ){
            $endtime = $deptime['deptime'] + ($startcity['trunkaging'] + $endcity['trunkaging'])*24*60*60;
            $newline['trunkaging'] = ($startcity['trunkaging'] + $endcity['trunkaging']).'天';
        }
        $startime = $deptime['deptime'];
//        $endtime = $deptime['deptime'] + ($startcity['trunkaging'] + $endcity['trunkaging'])*24*60*60;
        $a = date('w',strtotime(date('Y-m-d',$endtime)));
        $weekarray=["周日","周一","周二","周三","周四","周五","周六"];

        $newline['arrivewin'] =  $weekarray[$a];
        // 查询干线起点城市对应的提货区域
        $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$startcity['companyid'],'type'=>1,'province'=>$startcity['begincityid']))->find();
//         var_dump($startcity['companyid']);
//         var_dump($startcity['begincityid']);
//        var_dump($ti_arr);
//         exit();
        // 查询干线终点城市对应的配送区域
        $pei_arr = DB::table('ct_tpprice')->where(array('companyid'=>$endcity['companyid'],'type'=>2,'province'=>$endcity['endcityid']))->find();
        $pprice= [];
        $pprice['price'] = $ti_arr['price'];
        $pprice['type'] = 1;
        $pprice['province'] = $startcity['begincityid'];
        $pprice['rate'] = $ti_arr['rate'];
        $pprice['companyid'] = $companyid['cid'];

        Db::table('ct_tpprice')->insert($pprice);

        $tprice= [];
        $tprice['price'] = $pei_arr['price'];
        $tprice['type'] = 2;
        $tprice['province'] = $endcity['endcityid'];
        $tprice['rate'] = $pei_arr['rate'];
        $tprice['companyid'] = $companyid['cid'];
        Db::table('ct_tpprice')->insert($tprice);

        $insert = Db::table('ct_shift')->insertGetId($newline);


        $date_log['deptime'] = $startime;
        $date_log['endtime'] = $endtime;
        $date_log['tonnage'] = '';
        $date_log['volume'] = '';
        $date_log['shiftid'] = $insert;
        Db::table('ct_shift_log')->insert($date_log);

        if(!empty($insert)){

            $line = Db::table("ct_shift")
                ->alias('s')
                ->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
                ->field('s.sid,s.begincityid,s.beginareaid,s.endareaid,s.endcityid,s.transit,s.istransit,s.picktype,s.sendtype,s.picksite,s.stime,s.sphone,s.sendsite,s.dtime,s.tphone,s.companyid,s.shiftnumber,s.trunkaging,s.price,s.timestrat,s.timeend,s.lowprice,s.shiftstate,s.pmoney,s.smoney,s.weekday,s.discount,g.slid,g.deptime')
                ->where('sid',$insert)
                ->where($condition1)
                ->order($order)
                ->select();

        }
        return $line;

    }
}
