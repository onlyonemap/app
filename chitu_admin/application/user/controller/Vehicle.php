<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request;
use  think\Loader; //加载模型

class Vehicle extends Base{
    public function chentest(){
        echo "xxx";
    }

    /**
     * 优惠线路 取的是车源数据
     * 车源列表
     * @auther 李渊
     * @date 2019.1.4
     * @param [int] startid 	起点城市id
     * @param [int] endcityid 终点城市id
     * @return [type] [description]
     */
    public function discount_line(){
        $startid 	= input('startcityid'); // 起点城市id
        $endcityid 	= input('endcityid'); 	// 终点城市id
        // 筛选起点城市
        if ($startid !='') {
            $condition['o.start_city'] = $startid;
        }
        // 筛选终点城市
        if ($endcityid !='') {
            $condition['o.end_city'] = $endcityid;
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
            ->paginate(10);
        // 转数组
        $list_mes = $result->toArray();
        // 获取数据数组
        $list = $list_mes['data'];
        // 遍历数据
        foreach ($list as $key => $value) {
            // 返回起点城市
            $list[$key]['startCity'] = addresidToName($value['start_city']);
            // 返回终点城市
            $list[$key]['endCity'] = addresidToName($value['end_city']);
            // 返回起点城市
            $list[$key]['startArea'] = addresidToName($value['start_area']);
            // 返回终点城市
            $list[$key]['endArea'] = addresidToName($value['end_area']);
            // 重量
            $list[$key]['weight'] = $value['weight'] ? $value['weight']/1000 : $value['allweight'];
            // 体积
            $list[$key]['volume'] = $value['volume'] ? $value['volume'] : $value['allvolume'];
            // 总价
            $list[$key]['price'] = $value['referprice'] + $value['down_price'];
            // 判断是否同城 1 同城 2 城际
            if ($value['start_city'] == $value['end_city']) {
                $list[$key]['is_same_city'] = 1;
            } else {
                $list[$key]['is_same_city'] = 2;
            }
        }
        // 判断是否有数据
        if(empty($list)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
        }
    }

    /**
     * 优惠线路 整车发布：数据提交
     * @auther: 李渊
     * @date: 2019.1.4
     * @return [type] [description]
     */
    public function discount_post()
    {
        $token = input("token");  //令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        // 用户id
        $data['userid'] = $user_id;
        // 平台计算的原始订单费用
        $order_price = input("price");
        // 用户折扣后的费用
        $discount_price = input("user_discount");
        // 平台计算的订单运费
        $data['actual_payment']  = round($order_price);
        // 用户折扣后的费用
        $data['user_discount']  = round($discount_price);
        // 定金
        $data['down_price'] = input("down_price");
        // 承运商运费 平台费用-定金
        $data['price']  = $data['actual_payment']-$data['down_price'];

        // 订单编号
        $data['ordernumber'] = 'K'.date('ymdHis').mt_rand('000','999');
        // 订单类型 1 正常整车 2 特价整车
        $data['order_type'] = 2;
        // 车型ID
        $data['carid']       = input("carid");
        // 起点城市ID
        $data['startcity']   = input("startcity");
        // 终点城市ID
        $data['endcity']     = input("endcity");
        // 起点区ID
        $data['startarea']   = input("startarea");
        // 终点区ID
        $data['endarea']     = input("endarea");
        // 发货日期
        $data['loaddate']    = input("loaddate");
        // 备注
        $data['remark']      = input("remark");
        // 下单日期
        $data['addtime']     = time();
        // 是否装
        $data['pickyesno']  = input("pickyesno");
        // 是否卸
        $data['sendyesno']  = input("sendyesno");
        // 温度
        $data['temperture']  = input("temperture");
        // 物品名称
        $data['goodsname']  = input("goodsname");
        // 选择支付类型： 1 立即支付  2 线下支付 3 提货支付 4 配送支付 5 支付定金
        $data['type']  = input("type");
        // 支付状态
        $data['paystate'] = 1; // 1未支付2已支付3支付失败4提货支付5配送支付
        // 提货地址
        $taddress = json_decode(input("taddress"));
        foreach ($taddress as $key => $value) {
            $pickaddress[] = $this->object_to_array($value);
        }
        $data['pickaddress'] = json_encode($pickaddress);
        // 配货地址
        $paddress = json_decode(input("paddress"));
        foreach ($paddress as $key => $value) {
            $peiaddress[] = $this->object_to_array($value);
        }
        $data['sendaddress'] = json_encode($peiaddress);
        $inser = Db::table('ct_userorder')->insertGetId($data);
        if($inser){
            // 推送
            return json(['code'=>'1001','message'=>'提交成功','data'=>$inser]);
        }else{
            return json(['code'=>'1002','message'=>'提交失败']);
        }
    }

    /**
     * 获取整车特价线路列表
     * @Auther: 李渊
     * @Date: 2018.10.9
     * @return [type] $data     列表数据
     */
    public function vehical_special()
    {
        $where['state'] = 1;
        // 查询数据
        $result = DB::table('ct_activitycity')->where($where)->order('id','asc')->select();

        // 遍历数据
        $arr = array();
        //
        foreach ($result as $key => $value) {
            // 起点城市id
            $arr[$key]['start_city'] = $value['startCity'];
            // 终点城市id
            $arr[$key]['end_city'] = $value['endCity'];
            // 起点城市
            $arr[$key]['startCity'] = addresidToName($value['startCity']);
            // 终点城市
            $arr[$key]['endCity'] = addresidToName($value['endCity']);
            // 起点城市周边城市
            $arr[$key]['startCityAround'] = json_decode($value['startCityAround'],true);
            // 终点城市周边城市
            $arr[$key]['endCityAround'] = json_decode($value['endCityAround'],true);
            // 发车日期
            $arr[$key]['loaddate'] = date("Y-m-d").' '.'20:00';
            // 价格
            $arr[$key]['referprice'] = $value['price'];
            // 定金
            $arr[$key]['down_price'] = 500;
            // 价格
            $arr[$key]['price'] = $value['price']+500;
            // 备注
            $arr[$key]['remark'] = $value['remark'];
        }
        // 判断是否为空
        if(empty($arr)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1002','message'=>'查询成功','data'=>$arr]);
        }
    }

    /**
     * 获取整车特价线路列表详情
     * @Auther: 李渊
     * @Date: 2018.9.6
     * @return [type] $id     列表数据索引id
     */
    public function vehical_special_detail()
    {
        $id = input('id');
        // 查询数据
        $result = DB::table('ct_activitycity')->where('id',$id)->find();
        // 起点城市
        $result['startCityName'] = addresidToName($result['startCity']);
        // 终点城市
        $result['endCityName'] = addresidToName($result['endCity']);
        // 起点城市周边城市
        $result['startCityAround'] = json_decode($result['startCityAround'],true);
        // 终点城市周边城市
        $result['endCityAround'] = json_decode($result['endCityAround'],true);

        $startArr = array();
        if (!empty($result['startCityAround'])) {
            # code...
            foreach ($result['startCityAround'] as $key => $value) {
                $startArr[$key]['value'] = $value['id'];
                $startArr[$key]['text'] = $value['name'];

                $childrenArr = array();
                $children = Db::table('ct_district')->where('parent_id',$value['id'])->select();

                foreach ($children as $k => $v) {
                    $childrenArr[$k]['value'] = $v['id'];
                    $childrenArr[$k]['text'] = $v['name'];
                }
                $startArr[$key]['children'] = $childrenArr;
            }
        }

        $endArr = array();
        if (!empty($result['endCityAround'])) {
            # code...
            foreach ($result['endCityAround'] as $key1 => $value1) {
                $endArr[$key1]['value'] = $value1['id'];
                $endArr[$key1]['text'] = $value1['name'];

                $childrenArr = array();
                $children = Db::table('ct_district')->where('parent_id',$value1['id'])->select();

                foreach ($children as $k1 => $v1) {
                    $childrenArr[$k1]['value'] = $v1['id'];
                    $childrenArr[$k1]['text'] = $v1['name'];
                }
                $endArr[$key1]['children'] = $childrenArr;
            }
        }


        // 返回始发周边
        $result['startCityAround'] = $startArr;

        // 返回始发周边
        $result['endCityAround'] = $endArr;
        // 判断是否为空
        if(empty($result)){
            return json(['code'=>'1001','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
        }
    }

    /**
     * 计算整车公里数
     * 从第一个提货地址到第一个配送地址的距离
     * @Auther: 李渊
     * @Date: 2018.6.15
     * @param  [type] $startcity 起点城市
     * @param  [type] $endcity   终点城市
     * @param  [type] $pickarr   提货地址数组
     * @param  [type] $sendarr   配送地址数组
     * @return [type] $km        公里数
     */
    public function vehical_km($startcity,$endcity,$pickarr,$sendarr)
    {
        // 选取第一个提货地址
        $pick = $pickarr[0]['areaName'];
        // 选取第一个配送地址
        $send = $sendarr[0]['areaName'];
        // 获取起始地址经纬度
        $start_action = bd_local($type='2',$startcity,$pick);//经纬度
        // 获取终点地址经纬度
        $end_action = bd_local($type='2',$endcity,$send);//经纬度
        // 公里数
        $finally =0;
        // 根据经纬度获取行车距离
        $list = direction($start_action['lat'], $start_action['lng'], $end_action['lat'], $end_action['lng']);
        // 获取公里数
        $finally = $list['distance']/1000;
        // 乘以公里系数后的公里数
        $km = mileage_interval(2,(int)$finally);
        // 返回公里数
        if ($km<=10){
            $km = 10;
        }
        return $km;
    }

    /**
     * 计算城配公里数
     * 以第一个提货点为起始点与剩下所有提货点做比较，距离第一个提货点最近的做第二个点计算距离
     * 拿最后一个提货点与所有配送点做比较，距离最后一个提货点最近的配送点计算距离
     * @Auther: 李渊
     * @Date: 2018.6.15
     * @param  [type] $city    城配城市
     * @param  [type] $pickarr 提货地址数组
     * @param  [type] $sendarr 配送地址数组
     * @return [type] $km      公里数
     */
    public function city_km($city,$pickarr,$sendarr)
    {
        $pick_arr = array();  //提货地址
        foreach ($pickarr as $key => $value) {
            $pick_arr[$key] = $value['address'];
        }
        $send_arr = array();  //配送地址
        foreach ($sendarr as $key => $value) {
            $send_arr[$key] = $value['address'];
        }
        // 公里数
        $finally =0;
        if (count($pick_arr) =='1' && count($send_arr) =='1') {   //配送和提货都是一个
            $start_action = bd_local($type='2',$city,$pick_arr['0']);//获取起始地址经纬度
            if (empty($start_action)){
                return $km = '';
            }
            $start_str['start'] = $start_action['lat'].','.$start_action['lng'];  //
            $end_action = bd_local($type='2',$city,$send_arr['0']);//获取终点经纬度
            if (empty($end_action)){
                return $km = '';
            }
            $start_str['end'] = $end_action['lat'].','.$end_action['lng'];
            $start_1 = $this->address_kilo($start_str['start'],$start_str['end']);   //获取公里数
            $finally = array_sum($start_1)/1000;   //计算公里数
        }elseif (count($pick_arr) >'1' && count($send_arr) =='1') {  //当提货多点，配送一个点时
            foreach ($pick_arr as $key => $value) {
                $start_action = bd_local($type='2',$city,$value);//经纬度
                if (empty($start_action)){
                    return $km = '';
                }
            }
            $pick_arr_more = $this->get_arr_list($city,0,$pick_arr);  //获取多个提货地址公里数
            $count_kilo = $this->array_search_key('count_finly',$pick_arr_more);  //查找最短距离的公里数
            $count_end_address = $this->array_search_key('pick_adds',$pick_arr_more);  //查找多个地址比较的最后一个地址
            $end_address = end($count_end_address);  //获取最后一个地址
            //获取最后一个提货地址
            $start_action = bd_local($type='2',$city,$end_address['0']);//获取起始地址经纬度
            $start_str['start'] = $start_action['lat'].','.$start_action['lng'];  //
            $end_action = bd_local($type='2',$city,$send_arr['0']);//获取终点经纬度
            $start_str['end'] = $end_action['lat'].','.$end_action['lng'];
            $start_1 = $this->address_kilo($start_str['start'],$start_str['end']);   //获取公里数
            $finally = array_sum($count_kilo)/1000 +array_sum($start_1)/1000;   //计算公里数
        }elseif (count($pick_arr) =='1' && count($send_arr) >'1') {
            $start_action = bd_local($type='2',$city,$pick_arr['0']); //获取起始地址经纬度
            $start_str['start'] = $start_action['lat'].','.$start_action['lng'];  //
            foreach ($send_arr as $key => $value) {
                $start_action = bd_local($type='2',$city,$value);//经纬度
                if (empty($start_action)){
                return $km = '';
               }
                $list[$key] = $start_action['lat'].','.$start_action['lng'];
                $start_str['end'][] = $start_action['lat'].','.$start_action['lng'];
            }
            $start_str['end'] = implode('|',$start_str['end']);  //将重点地址转换成字符
            $start_1 = $this->address_kilo($start_str['start'],$start_str['end']);
            $frist_kilo_value =min($start_1); //获取提货地址到配送地址最小公里数
            $frist_kilo_key = array_flip($start_1);  //获取提货地址到配送地址最小公里数数组键值和值交换
            $min_key = $frist_kilo_key[$frist_kilo_value];  //获取最小键值
            $start_2 = $this->get_arr_list($city,$min_key,$send_arr);
            $count_num = $this->array_search_key('count_finly',$start_2);
            $finally = array_sum($count_num)/1000+$frist_kilo_value/1000;
        }else{
            foreach ($pick_arr as $key => $value) {
                $start_action = bd_local($type='2',$city,$value);//经纬度
                if (empty($start_action)){
                    return $km = '';
                }
            }
            foreach ($send_arr as $key => $value) {
                $start_action = bd_local($type='2',$city,$value);//经纬度
                if (empty($start_action)){
                    return $km = '';
                }
            }
            $pick_arr_more = $this->get_arr_list($city,0,$pick_arr);  //获取多个提货地址公里数
            $count_kilo = $this->array_search_key('count_finly',$pick_arr_more);  //查找最短距离的公里数
            $count_end_address = $this->array_search_key('pick_adds',$pick_arr_more);  //查找多个地址比较的最后一个地址
            $end_address = end($count_end_address);  //获取最后一个地址
            //获取最后一个提货地址
            $start_action = bd_local($type='2',$city,$end_address['0']);//获取起始地址经纬度
            $start_str['start'] = $start_action['lat'].','.$start_action['lng'];  //
            foreach ($send_arr as $key => $value) {
                $start_action = bd_local($type='2',$city,$value);//经纬度
                $list[$key] = $start_action['lat'].','.$start_action['lng'];
                $start_str['end'][] = $start_action['lat'].','.$start_action['lng'];
            }
            $start_str['end'] = implode('|',$start_str['end']);  //将重点地址转换成字符
            $start_1 = $this->address_kilo($start_str['start'],$start_str['end']);
            $frist_kilo_value =min($start_1); //获取提货地址到配送地址最小公里数
            $frist_kilo_key = array_flip($start_1);  //获取提货地址到配送地址最小公里数数组键值和值交换
            $min_key = $frist_kilo_key[$frist_kilo_value];  //获取最小键值
            $start_2 = $this->get_arr_list($city,$min_key,$send_arr);
            $count_num = $this->array_search_key('count_finly',$start_2);
            $finally = array_sum($count_kilo)/1000+$frist_kilo_value/1000+array_sum($count_num)/1000;
        }
        // 乘以公里系数后的公里数
        $km = mileage_interval(1,(int)$finally);
        return $km;
    }
    /*
     * 城配计算公里数
     * */
    public function delivery_count(){
        $token = input("token");  //令牌
        $carid = input("carid");  //车型的ID
        $type = input("type");  //订单类型 1 城配 2 整车
        $startcity_str = input("startcity_str");  //起点城市
        $endcity_str = input("endcity_str");  //终点城市
        $taddress = json_decode(input("taddress"),TRUE);  //提货地址
        $paddress = json_decode(input("paddress"),TRUE);  //配货地址
        $pickyn = input("pickyesno"); //是否装卸1否2是
        $unloadyn = input("sendyesno"); //是否装卸1否2是
        $datetype = input('data_type'); // 发货时间
        $starttime = input('start_time');//开始时间段
        $endtime = input('end_time');//结束时间段
        $startcity_id = input('cityid');//城市ID
        if (input('carnum')){
            $carnum = input('carnum');
        }else{
            $carnum  = 1;
        }

        // 验证信息
        if(empty($token) || empty($carid) || empty($startcity_str) || empty($taddress) || empty($paddress)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }

        // 总运费
        $allmoney = 0;
        // 起步价
        $startPrice = 0;
        // 里程费
        $freight = 0;
        // 装货费
        $pickPrice = 0;
        // 卸货费
        $sendPrice = 0;
        // 装卸费
        $psPrice = $pickPrice+$sendPrice;
        // 多点提配费
        $multistorePrice = 0;
        // 每公里单价
        $onePrice = 0;
        // 里程数
        $km = 0;
        // 起步价系数
        $scale_startprice = 1;
        // 里程偏离系数
        $scale_km = 1;
        // 单公里价格系数
        $scale_price_km =1;
        // 装货费用系数
        $scale_pickgood = 1;
        // 卸货费用系数
        $scale_sendgood = 1;
        // 多点提配系数
        $scale_multistore = 1;
        // 促销优惠折扣系数 折扣为 总价格乘以折扣
        $scale_discount = 1;
        // 查找选定的车型
        $car_type = Db::table('ct_cartype')->where('car_id',$carid)->find();
        // 查找系数比例
        $scale = Db::table('ct_price_setting')->where('type',$type)->find();
        // 如果有系数值则写入
        if($scale['type']){
            $scale_startprice = $scale['scale_startprice'];
            $scale_km = $scale['scale_km'];
            $scale_price_km = $scale['scale_price_km'];
            $scale_pickgood = $scale['scale_pickgood'];
            $scale_sendgood = $scale['scale_sendgood'];
            $scale_multistore = $scale['scale_multistore'];
            $scale_discount = $scale['scale_discount'];
        }
        $parameter = Db::table('ct_city_cost')->where('c_city',$startcity_id)->find();

        // 计算公里数、里程费、装卸费
            // 获取城配运费
            $km = $this->city_km($startcity_str,$taddress,$paddress);
            if (empty($km)){
                return json(['code'=>'1003','message'=>'请输入正确的地址']);
            }
            // 里程费 公里数*单价
            $freight = ($km-5)*$car_type['costkm']*$parameter['scale_price'];
        if ($endtime){
               $time =$endtime - $starttime;
        }else{
            $time = $km/35;
        }
            $timeprice = $time/3600*$parameter['scale_hour'];
            if($freight >= $timeprice){
                $freight = $freight;
            }else{

                $freight = $timeprice;
            }
        // 装货费
        if ($pickyn == '2') {
            $pickPrice = $car_type['pickup']*$scale_pickgood;
        }
        // 卸货费
        if ($unloadyn == '2') {
            $sendPrice = $car_type['unload']*$scale_sendgood;
        }
            // 装卸费
            $psPrice = $pickPrice+$sendPrice;
        // 计算起步价
        $startPrice = $car_type['lowprice']*$parameter['start_fare'];
        // 多点提配费用
        $multistorePrice = (count($taddress)+count($paddress)-2)*$car_type['morepickup']*$scale_multistore;
        // 起步价费用取整
        $startPrice = round($startPrice);
        // 里程费费用取整
        $freight = round($freight);
        // 装卸费费用取整
        $psPrice = round($psPrice);
        // 多点提配费费用取整
        $multistorePrice = round($multistorePrice);
        //根据时间判断计费价格
        //获取当天时间戳
        $nowday = mktime(23, 59, 59, date('m'), date('d'), date('Y'))*1000;
        //获取第二天时间戳
        $seconday = $nowday+24*60*60*1000;

        // 总运费 = 起步价 + 里程费 + 装卸费 + 多点提配费
        if ($km <= 5){
            $allmoney = $startPrice*$carnum;
            $singleprice = $startPrice;
        }else{
            $allmoney = ($startPrice+$freight+$psPrice+$multistorePrice)*$carnum;
            $singleprice = $startPrice+$freight+$psPrice+$multistorePrice;
        }
        // 折扣价

        $discount = $allmoney*$scale_discount;
        $price['singleprice']= $singleprice;
        $price['allmoney'] = round($allmoney); // 总费用
        $price['discount'] = round($discount); // 优惠价
        $price['kilometre'] = round($km); // 公里数
        $price['freight'] = $freight+$startPrice; // 里程费
        $price['psPrice'] = $psPrice; // 装卸费
        $price['multistorePrice'] = $multistorePrice; // 多点提配费

        return json(['code'=>'1001','message'=>'查询成功','data'=>$price]);
    }
    /*
     * 整车计算公里数
     * */
    public function vehical_klio(){
        $token  = input("token");  //令牌
        $startcity_str  = json_decode(input("startcity_str"),TRUE);  //起点城市
        $endcity_str    = json_decode(input("endcity_str"),TRUE);  //终点城市
        $carid = input("carid");  //车型的ID
        $goback = input('goback');
        if( empty($token) || empty($startcity_str) || empty($endcity_str) || empty($carid)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] == '1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
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
        // 查找选定的车型0
        $car_type = Db::table('ct_cartype')->where('car_id',$carid)->find();
        // 查找系数比例
        $scale = Db::table('ct_price_setting')->where('type',$type)->find();
        // 如果有系数值则写入

        if($scale['type']){
            $scale_startprice = $scale['scale_startprice'];
            $scale_km = $scale['scale_km'];
            $scale_price_km = $scale['scale_price_km'];
        }
        if (count($startcity_str) == 1 && count($endcity_str) == 1){
            // 起点城市经纬度
            $start_action = bd_local($type='1',$startcity_str[0]['city'],$area='');//经纬度
            // 终点城市经纬度
            $end_action = bd_local($type='1',$endcity_str[0]['city'],$area='');//经纬度
            $list = direction($start_action['lat'], $start_action['lng'], $end_action['lat'], $end_action['lng']);
            $finally = $list['distance']/1000;
            $kilo = mileage_interval(2,(int)$finally);
        }elseif(count($startcity_str) >1 && count($endcity_str) ==1){
            $km ='';
            for ($i=1;$i<count($startcity_str);$i++){
                $start_action = bd_local($type='1',$startcity_str[$i-1]['city'],$area='');//经纬度
                $start_action1= bd_local($type='1',$startcity_str[$i]['city'],$area='');
                // 获取百度返回的结果
                $list = direction($start_action['lat'], $start_action['lng'], $start_action1['lat'], $start_action1['lng']);
                $finally = $list['distance']/1000;
                $km1 = mileage_interval(2,(int)$finally);
                $km += $km1;
            }
            $start_action2 = bd_local($type='1',end($startcity_str)['city'],$area='');
            $end_action = bd_local($type='1',$endcity_str[0]['city'],$area='');//经纬度
            $list2 = direction($start_action2['lat'], $start_action2['lng'], $end_action['lat'], $end_action['lng']);
            $finally1 = $list2['distance']/1000;
            $kilo1 = mileage_interval(2,(int)$finally1);
            $kilo = $kilo1 + $km;
        }elseif(count($startcity_str) ==1 && count($endcity_str)>1){
            $km = '';
            for ($i=1;$i<count($endcity_str);$i++){
                $end_action = bd_local($type='1',$endcity_str[$i-1]['city'],$area='');
                $end_action1 = bd_local($type='1',$endcity_str[$i]['city'],$area='');
                $list = direction($end_action['lat'], $end_action['lng'], $end_action1['lat'], $end_action1['lng']);
                $finally = $list['distance']/1000;
                $km1 = mileage_interval(2,(int)$finally);
                $km += $km1;
            }
            $end_action2 = bd_local($type='1',$endcity_str[0]['city'],$area='');
            $start_action = bd_local($type='1',$startcity_str[0]['city'],$area='');//经纬度
            $list2 = direction($start_action['lat'], $start_action['lng'], $end_action2['lat'], $end_action2['lng']);
            $finally1 = $list2['distance']/1000;
            $kilo1 = mileage_interval(2,(int)$finally1);
            $kilo = $kilo1 + $km;
        }else{
            $km ='';
            for ($i=1;$i<count($startcity_str);$i++){
                $start_action = bd_local($type='1',$startcity_str[$i-1]['city'],$area='');//经纬度
                $start_action1= bd_local($type='1',$startcity_str[$i]['city'],$area='');
                // 获取百度返回的结果
                $list = direction($start_action['lat'], $start_action['lng'], $start_action1['lat'], $start_action1['lng']);
                $finally = $list['distance']/1000;
                $km1 = mileage_interval(2,(int)$finally);
                $km += $km1;
            }
            $km2 = '';
            for ($j=1;$j<count($endcity_str);$j++){
                $end_action = bd_local($type='1',$endcity_str[$j-1]['city'],$area='');
                $end_action1 = bd_local($type='1',$endcity_str[$j]['city'],$area='');
                $list1 = direction($end_action['lat'],$end_action['lng'],$end_action1['lat'],$end_action1['lng']);
                $finally1 = $list1['distance']/1000;
                $km3 = mileage_interval(2,(int)$finally1);
                $km2 += $km3;
            }
            $end_action3 = bd_local($type='1',$endcity_str[0]['city'],$area='');
            $start_action3 = bd_local($type='1',end($startcity_str)['city'],$area='');//经纬度
            $list2 = direction($start_action3['lat'], $start_action3['lng'], $end_action3['lat'], $end_action3['lng']);
            $finally2 = $list2['distance']/1000;
            $kilo1 = mileage_interval(2,(int)$finally2);

            $kilo = $km + $km2 + $kilo1;

        }
        // 计算起步价
        $startPrice = $car_type['lowprice']*$scale_startprice;
        // 运费 公里数*单价
        $freight = $kilo*$car_type['costkm']*$scale_price_km;
       if ($goback == 1){
           $allmoney = $startPrice+$freight;
           $kilo1 = round($kilo);
       }else{
           $allmoney = ($startPrice+$freight)*1.7;
           $kilo1   = round($kilo)*2;
       }
        // 总运费


        if ($kilo) {
            $re_data['km'] = $kilo1;//预计里程数
            $re_data['countprice'] = round($allmoney);  //预计费用
            return json(['code'=>'1001','message'=>'查询成功','data'=>$re_data]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }

    /**
     * 整车、城配发布计算价格
     * @auther 李渊
     * @date 2018.6.15
     * @description: 计费公式 = 起步价+装卸费+多点提货费
     * @param [string] token 用户令牌
     * @param [int] carid 车型的ID
     * @param [int] type 订单类型 1 城配 2 整车
     * @param [string] startcity_str 起点城市
     * @param [string] endcity_str 终点城市
     * @param [array] taddress 提货地址
     * @param [array] paddress 配货地址
     * @param [int] pickyesno 是否装货1否2是
     * @param [int] sendyesno 是否卸货1否2是
     * @return [type]        [description]
     */
    public function valuation()
    {
        $token = input("token");  //令牌
        $carid = input("carid");  //车型的ID
        $type = input("type");  //订单类型 1 城配 2 整车
        $startcity_str = input("startcity_str");  //起点城市
        $endcity_str = input("endcity_str");  //终点城市
        $taddress = json_decode(input("taddress"),TRUE);  //提货地址
        $paddress = json_decode(input("paddress"),TRUE);  //配货地址
        $pickyn = input("pickyesno"); //是否装卸1否2是
        $unloadyn = input("sendyesno"); //是否装卸1否2是
        $datetype = input('data_type'); // 发货时间

        if ($type == 1) {
            $cold_type  = input('cold_type');   // 城配计算价格 冷冻类型
            $number  = input('number');         // 城配计算价格 件数
        }

        // 验证信息
        if(empty($token) || empty($carid) || empty($startcity_str) || empty($taddress) || empty($paddress)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        // 总运费
        $allmoney = 0;
        // 起步价
        $startPrice = 0;
        // 里程费
        $freight = 0;
        // 装货费
        $pickPrice = 0;
        // 卸货费
        $sendPrice = 0;
        // 装卸费
        $psPrice = $pickPrice+$sendPrice;
        // 多点提配费
        $multistorePrice = 0;
        // 每公里单价
        $onePrice = 0;
        // 里程数
        $km = 0;
        // 起步价系数
        $scale_startprice = 1;
        // 里程偏离系数
        $scale_km = 1;
        // 单公里价格系数
        $scale_price_km =1;
        // 装货费用系数
        $scale_pickgood = 1;
        // 卸货费用系数
        $scale_sendgood = 1;
        // 多点提配系数
        $scale_multistore = 1;
        // 促销优惠折扣系数 折扣为 总价格乘以折扣
        $scale_discount = 1;
        // 查找选定的车型
        $car_type = Db::table('ct_cartype')->where('car_id',$carid)->find();
        // 查找系数比例
        $scale = Db::table('ct_price_setting')->where('type',$type)->find();
        // 如果有系数值则写入
        if($scale['type']){
            $scale_startprice = $scale['scale_startprice'];
            $scale_km = $scale['scale_km'];
            $scale_price_km = $scale['scale_price_km'];
            $scale_pickgood = $scale['scale_pickgood'];
            $scale_sendgood = $scale['scale_sendgood'];
            $scale_multistore = $scale['scale_multistore'];
            $scale_discount = $scale['scale_discount'];
            $scale_sameday = $scale['scale_sameday'];
            $scale_seconday = $scale['scale_seconday'];
            $scale_moreday = $scale['scale_moreday'];
        }

        // 计算公里数、里程费、装卸费
        if($type == '2'){ // 整车
            // 获取整车公里数
            $km = $this->vehical_km($startcity_str,$endcity_str,$taddress,$paddress);
            // 里程费 公里数*单价
            $freight = $km*$car_type['costkm']*$scale_price_km;

            // 装货费
            if ($pickyn == '2') {
                $pickPrice = $car_type['pickup']*$scale_pickgood;
            }
            // 卸货费
            if ($unloadyn == '2') {
                $sendPrice = $car_type['unload']*$scale_sendgood;
            }
            // 装卸费
            $psPrice = $pickPrice+$sendPrice;
        }else{ // 城配
            // 获取城配运费
            $km = $this->city_km($startcity_str,$taddress,$paddress);
            // 里程费 公里数*单价
            $freight = $km*$car_type['costkm']*$scale_price_km;
            // 装货费
            $pickPrice = $car_type['pickup']*$scale_pickgood;
            // 卸货费
            $sendPrice = $car_type['unload']*$scale_sendgood;
            // 装卸费
            $psPrice = $pickPrice+$sendPrice;
        }
        // 计算起步价
        $startPrice = $car_type['lowprice']*$scale_startprice;
        // 多点提配费用
        $multistorePrice = (count($taddress)+count($paddress)-2)*$car_type['morepickup']*$scale_multistore;
        // 起步价费用取整
        $startPrice = round($startPrice);
        // 里程费费用取整
        $freight = round($freight);
        // 装卸费费用取整
        $psPrice = round($psPrice);
        // 多点提配费费用取整
        $multistorePrice = round($multistorePrice);
        //根据时间判断计费价格
        //获取当天时间戳
        $nowday = mktime(23, 59, 59, date('m'), date('d'), date('Y'))*1000;
        //获取第二天时间戳
        $seconday = $nowday+24*60*60*1000;


        // 如果是城配则根据用车时间计算对应的比例
        if($type == 1 && !empty($datetype)){
            // 时间转化为时间戳
            $date = strtotime(trim($datetype).':00');
            // 根据用车时间计算费用
            $allmoney = money_datescale(1,$date,$allmoney,$cold_type,$number);
        }elseif($type == 2 && !empty($datetype)){
            if ($datetype <= $nowday){
                $timeprice =($startPrice+$freight)*$scale_sameday;
                $allmoney = $timeprice+$psPrice;

            }elseif($nowday < $datetype && $datetype <= $seconday){
                $timeprice =($startPrice+$freight)*$scale_seconday;
                $allmoney = $timeprice+$psPrice;

            }else{
                $timeprice =($startPrice+$freight)*$scale_moreday;
                $allmoney = $timeprice+$psPrice;

            }
        }

        // 总运费 = 起步价 + 里程费 + 装卸费 + 多点提配费
        $allmoney = $startPrice+$freight+$psPrice+$multistorePrice;
        // 折扣价
        $discount = $allmoney*$scale_discount;
        $price['allmoney'] = round($allmoney); // 总费用
        $price['discount'] = round($discount); // 优惠价
        $price['kilometre'] = round($km); // 公里数
        $price['freight'] = $freight+$startPrice; // 里程费
        $price['psPrice'] = $psPrice; // 装卸费
        $price['multistorePrice'] = $multistorePrice; // 多点提配费

        return json(['code'=>'1001','message'=>'查询成功','data'=>$price]);
    }

    /*
     * 整车计算价格
     * */
    public function vehical_count(){
        $token = input("token");  //令牌
        $carid = input("carid");  //车型的ID
        $type = input("type");  //订单类型 1 城配 2 整车
        $startcity_str = json_decode(input("startcity_str"),TRUE);  //起点城市
        $endcity_str = json_decode(input("endcity_str"),TRUE);  //终点城市
        $taddress = json_decode(input("taddress"),TRUE);  //提货地址
        $paddress = json_decode(input("paddress"),TRUE);  //配货地址
        $pickyn = input("pickyesno"); //是否装卸1否2是
        $unloadyn = input("sendyesno"); //是否装卸1否2是
        $datetype = input('data_type'); // 发货时间
        $carnum = input('carnum');//用车辆数
        $goback = input('goback');//1,单程 2,返程
        // 验证信息
        if(empty($token) || empty($carid) || empty($startcity_str) || empty($taddress) || empty($paddress)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        // 总运费
        $allmoney = 0;
        // 起步价
        $startPrice = 0;
        // 里程费
        $freight = 0;
        // 装货费
        $pickPrice = 0;
        // 卸货费
        $sendPrice = 0;
        // 装卸费
        $psPrice = $pickPrice+$sendPrice;
        // 多点提配费
        $multistorePrice = 0;
        // 每公里单价
        $onePrice = 0;
        // 里程数
        $km = 0;
        // 起步价系数
        $scale_startprice = 1;
        // 里程偏离系数
        $scale_km = 1;
        // 单公里价格系数
        $scale_price_km =1;
        // 装货费用系数
        $scale_pickgood = 1;
        // 卸货费用系数
        $scale_sendgood = 1;
        // 多点提配系数
        $scale_multistore = 1;
        // 促销优惠折扣系数 折扣为 总价格乘以折扣
        $scale_discount = 1;
        // 查找选定的车型
        $car_type = Db::table('ct_cartype')->where('car_id',$carid)->find();
        // 查找系数比例
        $scale = Db::table('ct_price_setting')->where('type',$type)->find();
        // 如果有系数值则写入
        if($scale['type']){
            $scale_startprice = $scale['scale_startprice'];
            $scale_km = $scale['scale_km'];
            $scale_price_km = $scale['scale_price_km'];
            $scale_pickgood = $scale['scale_pickgood'];
            $scale_sendgood = $scale['scale_sendgood'];
            $scale_multistore = $scale['scale_multistore'];
            $scale_discount = $scale['scale_discount'];
            $scale_sameday = $scale['scale_sameday'];
            $scale_seconday = $scale['scale_seconday'];
            $scale_moreday = $scale['scale_moreday'];
        }
        // 获取整车公里数
        $km = $this->count_kilo($startcity_str,$endcity_str,$taddress,$paddress);
        // 里程费 公里数*单价
        $freight = $km*$car_type['costkm']*$scale_price_km;

        // 装货费
        if ($pickyn == '2') {
            $pickPrice = $car_type['pickup']*$scale_pickgood;
        }
        // 卸货费
        if ($unloadyn == '2') {
            $sendPrice = $car_type['unload']*$scale_sendgood;
        }
        // 装卸费
        $psPrice = $pickPrice+$sendPrice;

        // 计算起步价
        $startPrice = $car_type['lowprice']*$scale_startprice;

        // 多点提配费用
        $multistorePrice = (count($taddress)+count($paddress)-2)*$car_type['morepickup']*$scale_multistore;
        // 起步价费用取整
        $startPrice = round($startPrice);
        // 里程费费用取整
        $freight = round($freight);
        // 装卸费费用取整
        $psPrice = round($psPrice);
        // 多点提配费费用取整
        $multistorePrice = round($multistorePrice);
        //根据时间判断计费价格
        //获取当天时间戳
        $nowday = mktime(23, 59, 59, date('m'), date('d'), date('Y'))*1000;
        //获取第二天时间戳
        $seconday = $nowday+24*60*60*1000;
        // 总运费 = 起步价 + 里程费 + 装卸费 + 多点提配费
        if ($goback == 1){
            $allmoney = $startPrice+$freight+$psPrice+$multistorePrice;
            $freight1 = floor($freight+$startPrice);
        }else{
            $allmoney = ($startPrice+$freight)*1.7+$psPrice+$multistorePrice;
            $freight1 = floor(($freight+$startPrice)*1.7);
        }


        // 根据用车时间计算价格
        if ($type == 2 && !empty($datetype)){
            if ($datetype <= $nowday){
                $timeprice =($startPrice+$freight+$multistorePrice)*$scale_sameday;
                $allmoney = $timeprice+$psPrice;

            }elseif($nowday < $datetype && $datetype <= $seconday){
                $timeprice =($startPrice+$freight+$multistorePrice)*$scale_seconday;
                $allmoney = $timeprice+$psPrice;

            }else{
                $timeprice =($startPrice+$freight+$multistorePrice)*$scale_moreday;
                $allmoney = $timeprice+$psPrice;

            }
        }

        // 折扣价
        $discount = $allmoney*$scale_discount;
        $price['singleprice'] = round($allmoney);
        $price['allmoney'] = round($allmoney)*$carnum; // 总费用
        $price['discount'] = round($discount); // 优惠价
        $price['kilometre'] = round($km); // 公里数
        $price['freight'] = $freight1; // 里程费
        $price['psPrice'] = $psPrice; // 装卸费
        $price['multistorePrice'] = $multistorePrice; // 多点提配费
        return json(['code'=>'1001','message'=>'查询成功','data'=>$price]);

    }
    /*
     * 整车计算公里数
     *
     * */
    public function count_kilo($startcity,$endcity,$pickarr,$sendarr){
        if (count($startcity) == 1 && count($endcity) == 1){
            // 起点城市经纬度
            $start_action = bd_local($type='2',$startcity[0]['city'],$pickarr[0]['areaName']);//经纬度
            // 终点城市经纬度
            $end_action = bd_local($type='2',$endcity[0]['city'],$sendarr[0]['areaName']);//经纬度
            $list = direction($start_action['lat'], $start_action['lng'], $end_action['lat'], $end_action['lng']);
            $finally = $list['distance']/1000;
            $kilo = mileage_interval(2,(int)$finally);
        }elseif(count($startcity) >1 && count($endcity) ==1){
            $km ='';
            for ($i=1;$i<count($startcity);$i++){
                $start_action = bd_local($type='2',$startcity[$i-1]['city'],$pickarr[$i-1]['areaName']);//经纬度
                $start_action1= bd_local($type='2',$startcity[$i]['city'],$pickarr[$i]['areaName']);
                // 获取百度返回的结果
                $list = direction($start_action['lat'], $start_action['lng'], $start_action1['lat'], $start_action1['lng']);
                $finally = $list['distance']/1000;
                $km1 = mileage_interval(2,(int)$finally);
                $km += $km1;
            }
            $start_action2 = bd_local($type = '2',end($startcity)['city'],end($pickarr)['areaName']);
            $end_action = bd_local($type = '2',$endcity[0]['city'],$sendarr[0]['areaName']);//经纬度
            $list2 = direction($start_action2['lat'], $start_action2['lng'], $end_action['lat'], $end_action['lng']);
            $finally1 = $list2['distance']/1000;
            $kilo1 = mileage_interval(2,(int)$finally1);
            $kilo = $kilo1 + $km;
        }elseif(count($startcity) ==1 && count($endcity)>1){
            $km = '';
            for ($i=1;$i<count($endcity);$i++){
                $end_action = bd_local($type='2',$endcity[$i-1]['city'],$sendarr[$i-1]['areaName']);
                $end_action1 = bd_local($type='2',$endcity[$i]['city'],$sendarr[$i]['areaName']);
                $list = direction($end_action['lat'], $end_action['lng'], $end_action1['lat'], $end_action1['lng']);
                $finally = $list['distance']/1000;
                $km1 = mileage_interval(2,(int)$finally);
                $km += $km1;
            }
            $end_action2 = bd_local($type='2',$endcity[0]['city'],$sendarr[0]['areaName']);
            $start_action = bd_local($type='2',$startcity[0]['city'],$pickarr[0]['areaName']);//经纬度
            $list2 = direction($start_action['lat'], $start_action['lng'], $end_action2['lat'], $end_action2['lng']);
            $finally1 = $list2['distance']/1000;
            $kilo1 = mileage_interval(2,(int)$finally1);
            $kilo = $kilo1 + $km;
        }else{
            $km ='';
            for ($i=1;$i<count($startcity);$i++){
                $start_action = bd_local($type='2',$startcity[$i-1]['city'],$pickarr[$i-1]['areaName']);//经纬度
                $start_action1= bd_local($type='2',$startcity[$i]['city'],$pickarr[$i]['areaName']);
                // 获取百度返回的结果
                $list = direction($start_action['lat'], $start_action['lng'], $start_action1['lat'], $start_action1['lng']);
                $finally = $list['distance']/1000;
                $km1 = mileage_interval(2,(int)$finally);
                $km += $km1;
            }
            $km2 = '';
            for ($j=1;$j<count($endcity);$j++){
                $end_action = bd_local($type='2',$endcity[$j-1]['city'],$sendarr[$j-1]['areaName']);
                $end_action1 = bd_local($type='2',$endcity[$j]['city'],$sendarr[$j]['areaName']);
                $list1 = direction($end_action['lat'],$end_action['lng'],$end_action1['lat'],$end_action1['lng']);
                $finally1 = $list1['distance']/1000;
                $km3 = mileage_interval(2,(int)$finally1);
                $km2 += $km3;
            }
            $end_action3 = bd_local($type='2',$endcity[0]['city'],$sendarr[0]['areaName']);
            $start_action3 = bd_local($type='2',end($startcity)['city'],end($pickarr)['areaName']);//经纬度
            $list2 = direction($start_action3['lat'], $start_action3['lng'], $end_action3['lat'], $end_action3['lng']);
            $finally2 = $list2['distance']/1000;
            $kilo1 = mileage_interval(2,(int)$finally2);

            $kilo = $km + $km2 + $kilo1;

        }
        return $kilo;
    }
    //城配计算价格
    public function city_count(){
        $token = input("token");  //令牌
        $carid = input("carid");  //车型的ID
        $startcity_str = input("startcity_str");  //起点城市
        $taddress = json_decode(input("taddress"),TRUE);  //提货地址
        $paddress = json_decode(input("paddress"),TRUE);  //配货地址
        // 验证信息
        if(empty($token) || empty($carid) || empty($startcity_str) || empty($taddress) || empty($paddress)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }

        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        //类型
        $type = 1;
        // 总运费
        $allmoney = 0;
        // 起步价
        $startPrice = 0;
        // 里程费
        $freight = 0;
        // 装货费
        $pickPrice = 0;
        // 卸货费
        $sendPrice = 0;
        // 装卸费
        $psPrice = $pickPrice+$sendPrice;
        // 多点提配费
        $multistorePrice = 0;

        // 每公里单价
        $onePrice = 0;
        // 里程数
        $km = 0;
        // 起步价系数
        $scale_startprice = 1;
        // 里程偏离系数
        $scale_km = 1;
        // 单公里价格系数
        $scale_price_km =1;
        // 装货费用系数
        $scale_pickgood = 1;
        // 卸货费用系数
        $scale_sendgood = 1;
        // 多点提配系数
        $scale_multistore = 1;
        // 促销优惠折扣系数 折扣为 总价格乘以折扣
        $scale_discount = 1;
        // 查找选定的车型
        $car_type = Db::table('ct_cartype')->where('car_id',$carid)->find();
        // 查找系数比例
        $scale = Db::table('ct_price_setting')->where('type',$type)->find();

        // 如果有系数值则写入
        if($scale['type']){
            $scale_startprice = $scale['scale_startprice'];
            $scale_km = $scale['scale_km'];
            $scale_price_km = $scale['scale_price_km'];
            $scale_pickgood = $scale['scale_pickgood'];
            $scale_sendgood = $scale['scale_sendgood'];
            $scale_multistore = $scale['scale_multistore'];

        }

        $km = $this->city_km($startcity_str,$taddress,$paddress);
        // 里程费 公里数*单价
        $freight = $km*$car_type['costkm']*$scale_price_km;

        // 计算起步价
        $startPrice = $car_type['lowprice']*$scale_startprice;

        // 多点提配费用
        $multistorePrice = (count($taddress)+count($paddress)-2)*$car_type['morepickup']*$scale_multistore;
        // 起步价费用取整
        $startPrice = round($startPrice);
        // 里程费费用取整
        $freight = round($freight);
        // 装卸费费用取整
        $psPrice = round($psPrice);
        // 多点提配费费用取整
        $multistorePrice = round($multistorePrice);

        // 总运费 = 起步价 + 里程费 + 装卸费 + 多点提配费

        $allmoney = $startPrice+$freight+$psPrice+$multistorePrice;

        $res['licheng'] = round($freight);
        $res['startPrice'] = round($startPrice);
        $res['duodian'] = round($multistorePrice);
        $res['km'] = round($km);
        $res['fprice'] = round($allmoney);
        return json(['code'=>1001,'message'=>'查询成功','data'=>$res]);

    }
    //城配确认订单 2019.5.31
    public function delivery(){
        $token = input("token");  //令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        $data['ordernumber']  = 'SP'.date('ymdHis').mt_rand('000','999');
        $data['userid'] = $user_id;
        $data['carid'] = input('carid');
        $data['startcity'] = input('startcity_str');
        $data['taddress'] = input('taddress');
        $data['paddress'] = input('paddress');
        $data['temperture'] = input('temperture');
        $data['goodsname'] = input('goodsname');
        $data['weight'] = input('weight');
        $data['volume'] = input('volume');
        $data['pickyesno'] = input('pickyesno');
        $data['sendyesno'] = input('sendyesno');
        $data['handingmode'] = input('handingmode');
        $data['paytype'] = input('paytype');
        $data['picktime'] = input('picktime');
        $data['remark'] = input('remark');
        $data['price'] = input('price');
        $data['fprice'] = input('fprice');
        $data['addtime'] = time();
        $type_pay = input('ztype');
            $data['orderstatus'] = 2;
        $inser = Db::table('ct_delivery') ->insertGetId($data);

        if($inser){
            $vehicalData = Db::table("ct_delivery")->field('startcity')->where('uoid',$inser)->find();
            // 推送
            $result =  DB::table('ct_district')->where(array('name'=>$vehicalData['startcity'],'level'=>'2'))->find();
            $this->send_note($type_type='3',$result['id'],'');
            return json(['code'=>'1001','message'=>'提交成功','data'=>$inser]);
        }else{
            return json(['code'=>'1002','message'=>'提交失败']);
        }
    }
    /**
     *取消信息发布：城配
     */
    public function delivery_cancel(){
        $token = input("token");  //令牌
        $id = input('uoid'); //订单号
        //获取装车时间
        $picktime = input('picktime');
        if(empty($token) || empty($id) ||empty($picktime)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        $type = 2;
        $status  = 4;
        $data['orderstatus'] = $status;
        //获取下单时间
        $addtime = time()*1000;

        //判断取消装车时间是否大于5小时 大于直接取消 小于扣取信息费
        if( $picktime - $addtime  >=3*60*60*1000){
            $res = Db::table('ct_delivery')->where('userid',$user_id)->where('uoid',$id)->update($data);
        }else{
            $db = Db::table('ct_user')->field('money')->where('uid',$user_id)->find();
            $money = Db::table('ct_setting_price')->field('cancleprice')->where('type',$type)->find();
            $arr['money'] =  $db['money'] - $money['cancleprice'];
            $re = Db::table('ct_user')->field('money')->where('uid',$user_id)->update($arr);
            $content = "整车取消订单违约扣款";
            //插入余额使用记录和更新余额
//            $this->record($user_data['integral'],$user_data['uid'],'',$pay_count,'',$content,'4',$array_data['attach'],$user_data['oid'],'1');
                $res = Db::table('ct_delivery')->where('userid',$user_id)->where('uoid',$id)->update($data);
        }
        if($res){
            return json(['code'=>'1001','message'=>'取消成功']);
        }else{
            return json(['code'=>'1002','message'=>'取消失败']);
        }

    }


    /**
     * 整车发布-计费费用、公里数、行车时长
     * 根据两个城市计算里程、行车时长
     * @auther 李渊
     * @date 2018.6.15
     * @param [string] token 用户令牌
     * @param [string] startcity_str 起点城市
     * @param [string] endcity_str 终点城市
     * @param [int] carid 车型的ID
     * @return [type] [description]
     */
    public function arrtime_city()
    {
        $token   = input("token");  //令牌
        $startcity_str  = input("startcity_str");  //起点城市
        $endcity_str     = input("endcity_str");  //终点城市
        $carid = input("carid");  //车型的ID
        if( empty($token) || empty($startcity_str) || empty($endcity_str) || empty($carid)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
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
        // 查找选定的车型0
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
        if ($km <10){
            $km = 10;
        }
        // 计算起步价
        $startPrice = $car_type['lowprice']*$scale_startprice;
        // 运费 公里数*单价
        $freight = $km*$car_type['costkm']*$scale_price_km;
        // 总运费
        $allmoney = $startPrice+$freight;

        if ($gethour) {
            $re_data['km'] = round($km);//预计里程数
//	   		$re_data['hour'] = round($gethour); //行车小时
            $re_data['countprice'] = round($allmoney);  //预计费用
            return json(['code'=>'1001','message'=>'查询成功','data'=>$re_data]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }

    /**
     * 整车发布：数据提交
     * @auther: 李渊
     * @date: 2018.6.12
     * @return [type] [description]
     */
    public function vehical_post()
    {
        $token = input("token");  //令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        // 用户id
        $data['userid'] = $user_id;
        // 用户出价
        $order_price = input("price");
        // 平台计算的订单运费
        $data['fprice']  = input("fprice");
        // 承运商运费 先为平台计算的运费
        $data['price']  = round($order_price);
        // 订单编号
        $data['ordernumber'] = 'K'.date('ymdHis').mt_rand('000','999');
        // 车型ID
        $data['carid']       = input("carid");
        // 起点城市ID
        $data['startcity']   = input("startcity");
        // 终点城市ID
        $data['endcity']     = input("endcity");
        // 发货日期
        $data['picktime']    = input("picktime");
        // 备注
        $data['remark']      = input("remark");

        // 下单日期
        $data['addtime']     = time();
        // 公里数
        $data['fkilo']  = input("fkilo");
        // 是否装
        $data['pickyesno']  = input("pickyesno");
        // 是否卸
        $data['sendyesno']  = input("sendyesno");
        // 温度
        $data['temperture']  = input("temperture");
        // 物品名称
        $data['goodsname']  = input("goodsname");
        // 选择支付类型： 1 提货支付 2 配送支付
        $data['pay_type']  = input("pay_type");
        //运输门点
        $data['handingmode'] = input('transportType');
        //支付方式 1,微信支付 2支付宝支付
//        $data['paytype'] = input('paytype');
        //是否支付
        $type_pay = input('ztype');
        if ($type_pay == 1){
            $data['orderstatus'] = 1;
        }
        if($type_pay == 2){
            $data['orderstatus'] = 2;
        }
        // 提货地址
        $taddress = json_decode(input("taddress"));
        foreach ($taddress as $key => $value) {
            $pickaddress[] = $this->object_to_array($value);
        }
        $data['pickaddress'] = json_encode($pickaddress);
        // 配货地址
        $paddress = json_decode(input("paddress"));
        foreach ($paddress as $key => $value) {
            $peiaddress[] = $this->object_to_array($value);
        }
        $data['sendaddress'] = json_encode($peiaddress);

        $inser = Db::table('ct_useorder') ->insertGetId($data);
        if($inser){
            $vehicalData = Db::table("ct_useorder")->field('startcity,endcity')->where('uoid',$inser)->find();
            // 推送
            $result =  DB::table('ct_district')->where(array('name'=>$vehicalData['startcity'],'level'=>'2'))->find();
            $result1 =  DB::table('ct_district')->where(array('name'=>$vehicalData['endcity'],'level'=>'2'))->find();
            $this->send_note($type_type='2',$result['id'],$result1['id'],'');
            return json(['code'=>'1001','message'=>'提交成功','data'=>$inser]);
        }else{
            return json(['code'=>'1002','message'=>'提交失败']);
        }
    }
    /*
     * 市内用车提交订单
     * */
    public function delivery_post(){
        $token = input("token");  //令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        // 用户id
        $data['userid'] = $user_id;
        // 平台计算的原始订单费用
        $order_price = input("price");
        // 用户折扣后的费用
        $discount_price = input("user_discount");
        // 平台计算的订单运费
        $data['actual_payment']  = round($order_price);
        // 用户折扣后的费用
        $data['user_discount']  = round($discount_price);
        // 承运商运费 先为平台计算的运费
        $data['price']  = round($order_price);

        // 订单编号
        $data['ordernumber'] = 'K'.date('ymdHis').mt_rand('000','999');
        // 车型ID
        $data['carid']       = input("carid");
        // 起点城市
        $data['startcity'] = input('startcity');
        // 终点城市
        $data['endcity'] = input('endcity');
        // 发货日期
        $data['loaddate']    = input("loaddate");
        // 备注
        $data['remark']      = input("remark");
        // 预计到达时间
        $data['arrtime']      = input("arrtime");
        // 下单日期
        $data['addtime']     = time();
        //车辆数
        $data['carnum'] = input('carnum');
        // 公里数
        $data['mileage']  = input("mileage");
        // 是否装
        $data['pickyesno']  = input("pickyesno");
        // 是否卸
        $data['sendyesno']  = input("sendyesno");
        // 温度
        $data['temperture']  = input("temperture");
        // 物品名称
        $data['goodsname']  = input("goodsname");
        //运输门点
        $data['handingmode'] = input('handingmode');
        //客户出价
        $data['fprice'] = input('fprice');
        // 选择支付类型： 1 立即支付 4 货到付款
        $data['type'] = input("type");
        //类型
        $data['order_type'] = 3;
        $data['time_slot'] = input('time_slot');//市内用车时间段

//         $data['paystate'] = 1; // 1未支付2已支付3支付失败4提货支付5配送支付
        if ($data['type']==1){
            $data['paystate']=1;
        }else{
            $data['paystate'] = 2; // 1未支付2已支付3支付失败4货到付款
        }
        // 提货地址
        $taddress = json_decode(input("taddress"));
        foreach ($taddress as $key => $value) {
            $pickaddress[] = $this->object_to_array($value);
        }
        $data['pickaddress'] = json_encode($pickaddress);
        // 配货地址
        $paddress = json_decode(input("paddress"));
        foreach ($paddress as $key => $value) {
            $peiaddress[] = $this->object_to_array($value);
        }
        $data['sendaddress'] = json_encode($peiaddress);

        $inser = Db::table('ct_userorder')->insertGetId($data);
        if($inser){
            $vehicalData = Db::table("ct_userorder")->field('startcity,endcity,paystate')->where('uoid',$inser)->find();
//              推送
            $result =  DB::table('ct_district')->where(array('name'=>$vehicalData['startcity'],'level'=>'2'))->find();
            $result1 =  DB::table('ct_district')->where(array('name'=>$vehicalData['endcity'],'level'=>'2'))->find();
            $this->send_note($type_type='2',$result['id'],$result1['id'],'');
            return json(['code'=>'1001','message'=>'提交成功','data'=>$inser]);
        }else{
            return json(['code'=>'1002','message'=>'提交失败']);
        }

    }
    /*
     * 整车数据提交
     * */
     public function vehicle_post(){
         $token = input("token");  //令牌
         if(empty($token)){
             return json(['code'=>'1000','message'=>'参数错误']);
         }
         $check_result = $this->check_token($token);//验证令牌

         if($check_result['status'] =='1'){
             return json(['code'=>'1007','message'=>'非法请求']);
         }elseif($check_result['status'] =='2'){
             return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
         }else{
             $user_id = $check_result['user_id'];
         }
         // 用户id
         $data['userid'] = $user_id;
         // 平台计算的原始订单费用
         $order_price = input("price");
         // 用户折扣后的费用
         $discount_price = input("user_discount");
         // 平台计算的订单运费
         $data['actual_payment']  = round($order_price);
         // 用户折扣后的费用
         $data['user_discount']  = round($discount_price);
         // 承运商运费 先为平台计算的运费
         $data['price']  = round($order_price);

         // 订单编号
         $data['ordernumber'] = 'K'.date('ymdHis').mt_rand('000','999');
         // 车型ID
         $data['carid']       = input("carid");
         // 起点城市ID
         $startcity = json_decode(input('startcity'),TRUE);
         $data['startcity']   = $startcity[0]['city'];
         // 终点城市ID
         $endcity = json_decode(input('endcity'),TRUE);
         $data['endcity']     = end($endcity)['city'];
         // 发货日期
         $data['loaddate']    = input("loaddate");
         // 备注
         $data['remark']      = input("remark");
         // 预计到达时间
         $data['arrtime']      = input("arrtime");
         // 下单日期
         $data['addtime']     = time();
         //车辆数
         $data['carnum'] = input('carnum');
         // 公里数
         $data['mileage']  = input("mileage");
         // 是否装
         $data['pickyesno']  = input("pickyesno");
         // 是否卸
         $data['sendyesno']  = input("sendyesno");
         // 温度
         $data['temperture']  = input("temperture");
         // 物品名称
         $data['goodsname']  = input("goodsname");
         //运输门点
         $data['handingmode'] = input('handingmode');
         //客户出价
         $data['fprice'] = input('fprice');
         // 选择支付类型： 1 标准价格  2 面议 3 提货支付 4 配送支付
         $data['type'] = input("type");
//         $data['paystate'] = 1; // 1未支付2已支付3支付失败4提货支付5配送支付
         if ($data['type']==1){
             $data['paystate']=1;
         }else{
             $data['paystate'] = 2; // 1未支付2已支付3支付失败4货到付款
         }
         // 提货地址
         $taddress = json_decode(input("taddress"));
         foreach ($taddress as $key => $value) {
             $pickaddress[] = $this->object_to_array($value);
         }
         $data['pickaddress'] = json_encode($pickaddress);
         // 配货地址
         $paddress = json_decode(input("paddress"));
         foreach ($paddress as $key => $value) {
             $peiaddress[] = $this->object_to_array($value);
         }
         $data['sendaddress'] = json_encode($peiaddress);
         $inser = Db::table('ct_userorder')->insertGetId($data);
         if($inser){
             $vehicalData = Db::table("ct_userorder")->field('startcity,endcity,paystate')->where('uoid',$inser)->find();
//              推送
             $result =  DB::table('ct_district')->where(array('name'=>$vehicalData['startcity'],'level'=>'2'))->find();
             $result1 =  DB::table('ct_district')->where(array('name'=>$vehicalData['endcity'],'level'=>'2'))->find();
             $this->send_note($type_type='2',$result['id'],$result1['id'],'');
             return json(['code'=>'1001','message'=>'提交成功','data'=>$inser]);
         }else{
             return json(['code'=>'1002','message'=>'提交失败']);
         }
     }

    /**
     *整车取消订单
     */
    public function vehical_cancel(){
        $token = input("token");  //令牌
        $id = input('uoid'); //订单号
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }

        $type = 1;
        $status  = 4;
        $data['orderstate'] = $status;
        //获取当前时间
        $addtime = time()*1000;

        //获取装车时间
        $picktime = input('picktime');

        //判断取消装车时间是否大于5小时 大于直接取消 小于扣取信息费
        if( ($picktime - $addtime)  >= 5*60*60*1000){
            $res = Db::table('ct_userorder')->where('userid',$user_id)->where('uoid',$id)->update($data);

        }else{
            $db = Db::table('ct_user')->field('money')->where('uid',$user_id)->find();
            $money = Db::table('ct_setting_price')->field('cancleprice')->where('type',$type)->find();
            $arr['money'] =  ($db['money'] - $money['cancleprice']);
            $re = Db::table('ct_user')->where('uid',$user_id)->update($arr);
            $content = "整车取消订单违约扣款";
            //插入余额使用记录和更新余额
//            $this->record($user_data['integral'],$user_data['uid'],'',$pay_count,'',$content,'4',$array_data['attach'],$user_data['oid'],'1');
                $res = Db::table('ct_userorder')->where('userid',$user_id)->where('uoid',$id)->update($data);
        }
        if($res){
            return json(['code'=>'1001','message'=>'取消成功']);
        }else{
            return json(['code'=>'1002','message'=>'取消失败']);
        }

    }
    /**
     *整车，城配获取保证金门槛费用及单条信息发布费用
     */
    public function vehical_price(){
        $token = input('token');
        $type = input('type');
        if(empty($token) || empty($type)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] == '1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status']=='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登陆']);
        }else{
            $user_id = $check_result['user_id'];
        }
        $res = Db::table('ct_setting_price')->field('charge,deposit')->where('type',$type)->find();
        if ($res){
            return json(['code'=>'1001','message'=>'请求成功','data'=>$res]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }



    /**
     * 整车发布：优惠线路下单提交
     * @auther: 李渊
     * @date: 2018.9.6
     * @return [type] [description]
     */
    public function vehical_special_post()
    {
        $token = input("token");  //令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }

        // 获取优惠线路索引id
        $id = input('id');
        // 查询数据
        $result = Db::table('ct_activitycity')->where('id',$id)->find();

        // 用户id
        $data['userid'] = $user_id;

        // 平台计算的订单运费
        $data['actual_payment']  = $result['price'];
        // 用户折扣后的费用
        $data['user_discount']  = $result['price'];
        // 承运商运费 先为平台计算的运费
        $data['price']  = $result['appoint_price'];
        // 承运商折扣后的费用
        $data['driver_discount'] = $result['appoint_price'];

        // 订单编号
        $data['ordernumber'] = 'K'.date('ymdHis').mt_rand('000','999');
        // 起点城市ID
        $data['startcity']   = input("startcity");
        // 终点城市ID
        $data['endcity']     = input("endcity");
        // 发货日期
        $data['loaddate']    = input("loaddate");
        // 备注
        $data['remark']      = input("remark");
        // 下单日期
        $data['addtime']     = time();
        // 温度
        $data['temperture']  = input("temperture");

        // 选择支付类型： 1 标准价格  2 面议 3 提货支付 4 配送支付
        // $data['type']  = input("type");
        $data['type']  = 3;
        if($data['type'] == 3){
            $data['paystate'] = 4; // 1未支付2已支付3支付失败4提货支付5配送支付
        }
        if($data['type'] == 4){
            $data['paystate'] = 5; // 1未支付2已支付3支付失败4提货支付5配送支付
        }

        // 提货地址
        $taddress = json_decode(input("taddress"));
        foreach ($taddress as $key => $value) {
            $pickaddress[] = $this->object_to_array($value);
        }
        $data['pickaddress'] = json_encode($pickaddress);
        // 配货地址
        $paddress = json_decode(input("paddress"));
        foreach ($paddress as $key => $value) {
            $peiaddress[] = $this->object_to_array($value);
        }
        $data['sendaddress'] = json_encode($peiaddress);

        // 定义接单状态已接单
        $data['orderstate'] = 2;
        // 定义接单人id
        $data['carriersid'] = $result['appoint_driver'];
        // 定义接单时间
        $data['taketime'] = time();

        // 插入数据
        $inser = Db::table('ct_userorder')->insertGetId($data);
        // 判断是否插入成功
        if($inser){
            // 获取推送人的信息
            $sendData = Db::table("ct_driver")->where('drivid',$result['appoint_driver'])->find();
            // 起点城市
            $startCity = addresidToName($data['startcity']);
            // 终点城市
            $endCity = addresidToName($data['endcity']);
            // 推送
            $center_list = '你有从【'.$startCity.'】发往【'.$endCity.'】整车订单,请前往【赤途承运端】app，分派车辆，及时完成运输服务';
            // 发送短信
            send_sms_class($sendData['mobile'],$center_list);
            return json(['code'=>'1001','message'=>'提交成功']);
        }else{
            return json(['code'=>'1002','message'=>'提交失败']);
        }
    }

    /**
     * 市内配送数据提交
     * @Author: 李渊
     * @Date: 2018-6-15
     * @return [type] [description]
     */
    public function city_With()
    {
        $token = input("token");  //令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        // 城配开通城市id
        $data['city_id']  = input('cityid'); //城市ID
        // 车型id
        $data['carid'] = input('carid'); //车型ID
        // 下单用户ID
        $data['userid'] = $user_id; //下单用户ID
        // 订单编号
        $data['orderid'] = 'SP'.date('ymdHis').mt_rand('000','999');
        // 冷冻类型
        $data['cold_type']  = input('cold_type');
        // 用车类型 1用车 2包车
        $data['ordertype']  = input('ordertype');
        // 选择支付类型： 1 标准价格  2 面议 3 提货支付
        $data['pytype'] = input('pytype');
        // 用车时间
        $data['data_type']  = input('data_type');
        // 下单时间
        $data['addtime']  = time();
        // 备注
        $data['remark']  = input("remark");
        // 提货地址
        $data['saddress'] = input('taddress');
        // 配送地址
        $data['eaddress'] = input('paddress');
        // 订单费用
        $data['actual_payment']  = input('price');
        // 用户折扣价
        $data['user_discount'] = input('user_discount');
        //物品名称
        $data['goodsname'] = input('goodsname');
        //重量
        $data['weight'] = input('weight');
        //体积
        $data['volume'] = input('volume');
        //司机装货
        $data['pickyesno'] = input('pickyesno');
        //司机卸货
        $data['sendyesno'] = input('sendyesno');
        //运输门点
        $data['handingmode'] = input('handingmode');
        //付款方式
        $data['paytype'] = input('paytype');
        //客户出价
        $data['fprice'] = input('fprice');
        // 促销价格
        $promotion = money_promotion(1,$data['actual_payment'],$data['user_discount'],$data['addtime'],$data['orderid'],$user_id);
        // 用户折扣价
        $data['user_discount'] = $promotion;
        // 司机承运价
        $data['paymoney'] = input('price');
        // 如果提货支付则支付状态为4
        if($data['pytype'] == 3){
            $data['paystate'] = 4;
        }
        Db::startTrans();
        try{
            $rout_data['start_time'] = time();
            $rout = Db::table('ct_rout_order')->insertGetId($rout_data);
            $data['rout_id'] = $rout;
            $insert = DB::table('ct_city_order')->insertGetId($data);
            // 提交事务
            Db::commit();
            if($insert) {
                $user_data = DB::table('ct_city_order')->where('id',$insert)->find();
                // 订单价
                $resultData['money'] = $data['actual_payment'];
                // 促销价
                $resultData['promotion'] = $promotion;
                $resultData['uoid'] = $insert;
                // 折扣
                $resultData['scale'] = round($resultData['promotion']/$resultData['money'],2)*10;
                // 推送
//                $this->send_note($typestate='3',$user_data['city_id'],'','');
                // 返回操作状态
                return json(['code'=>'1001','message'=>'操作成功','data'=>$resultData]);
            }
        } catch (\Exception $e){
            // 回滚事务
            Db::rollback();
            return json(['code'=>'1002','message'=>'操作失败']);
        }
    }

    /*
     * 城配订单提交数据
     * */
    public function city_delivery(){
        $token = input("token");  //令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        // 城配开通城市id
        $data['city_id']  = input('cityid'); //城市ID
        // 车型id
        $data['carid'] = input('carid'); //车型ID
        // 下单用户ID
        $data['userid'] = $user_id; //下单用户ID
        // 订单编号
        $data['orderid'] = 'SP'.date('ymdHis').mt_rand('000','999');
        // 冷冻类型
        $data['cold_type']  = input('cold_type');
        // 用车类型 1用车 2包车
        $data['ordertype']  = input('ordertype');
        // 选择支付类型： 1 标准价格  2 面议 3 提货支付
        $data['pytype'] = input('pytype');
        // 用车时间
        $data['data_type']  = input('data_type');
        // 件数
        $data['number']  = input('number');
        // 下单时间
        $data['addtime']  = time();
        // 备注
        $data['remark']  = input("remark");
        // 提货地址
        $data['saddress'] = input('taddress');
        // 配送地址
        $data['eaddress'] = input('paddress');

        // 订单费用
        $data['actual_payment']  = input('price');
        // 用户折扣价
        $data['user_discount'] = input('user_discount');
        // 促销价格
        $promotion = money_promotion(1,$data['actual_payment'],$data['user_discount'],$data['addtime'],$data['orderid'],$user_id);
        // 用户折扣价
        $data['user_discount'] = $promotion;
        // 司机承运价
        $data['paymoney'] = input('price');
        // 如果提货支付则支付状态为4
        if($data['pytype'] == 3){
            $data['paystate'] = 4;
        }

        Db::startTrans();
        try{
            $rout_data['start_time'] = time();
            $rout = Db::table('ct_rout_order')->insertGetId($rout_data);
            $data['rout_id'] = $rout;
            $insert = DB::table('ct_city_order')->insertGetId($data);

            // 提交事务
            Db::commit();
            if($insert) {
                $user_data = DB::table('ct_city_order')->where('id',$insert)->find();
                // 订单价
                $resultData['money'] = $data['actual_payment'];
                // 促销价
                $resultData['promotion'] = $promotion;

                // 折扣
                $resultData['scale'] = round($resultData['promotion']/$resultData['money'],2)*10;
                // 推送
                $this->send_note($typestate='3',$user_data['city_id'],'','');
                // 返回操作状态
                return json(['code'=>'1001','message'=>'操作成功','data'=>$resultData]);
            }
        } catch (\Exception $e) {
            // 回滚事务
            Db::rollback();
            return json(['code'=>'1002','message'=>'操作失败']);
        }
    }
    /**
     * 发布货物信息——提交货物信息
     * @Auther: 李渊
     * @Date: 2018.7.3
     * @return [type] [description]
     */
    public function issue_item()
    {
        // 令牌
        $token = input("token");
        // 起点城市ID
        $startcity = input("start_city");
        // 终点城市ID
        $endcity = input("end_city");
        // 温度
        $temperture = input("temperture");
        // 运输方式
        $carriage = input("carriage");

        if(empty($token) || empty($startcity) || empty($endcity) || empty($carriage) || empty($temperture)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }

        // 查找数据
        $user_mess = Db::table('ct_user')->where('uid',$user_id)->find();
        // 用户id
        $data['userid'] = $user_id;
        // 订单编号
        $data['ordernumber'] = 'P'.date('ymdHis').mt_rand('000','999');
        // 起点省id
        $data['start_pro'] = input("start_pro");
        // 起点城市ID
        $data['start_city'] = $startcity;
        // 起点城市ID
        $data['start_area'] = input("start_area");
        // 终点城市ID
        $data['end_pro']   = input("end_pro");
        // 起点城市ID
        $data['end_city'] = $endcity;
        // 起点城市ID
        $data['end_area'] = input("end_area");

        // 温度区间
        $data['temperture']  = $temperture;
        // 车型id
        $data['carid'] = input("carid");
        // 运输方式
        $data['carriage'] = $carriage;
        // 物品类别
        $data['good_type'] = input("good_type");
        // 重量提交是吨要乘以1000变为公斤
        $data['weight']  = input("weight")*1000;
        // 体积
        $data['volume'] = input("volume");
        // 运费单价
        $data['freight_unit_price'] = input("freight_unit_price");
        // 运费支付方式
        $data['freight_type'] = input("freight_type");
        // 发货日期
        $data['loaddate'] = input("loaddate");
        // 装卸方式
        $data['pick_remark'] = input("pick_remark");
        // 订单备注
        $data['remark'] = input("remark");
        // 下单日期
        $data['addtime'] = time();
        // 发布联系人
        $realname = $user_mess['realname'];
        if ($realname) {
            $realname =  mb_substr($realname,0,1,'utf-8');
            $realname = $realname.'经理';
        }

        $data['issue_realname']  = $realname ? $realname : $user_mess['username'];
        // 发布人号码
        $data['issue_phone']  = $user_mess['phone'];
        // 订单类型 1 用户发布货源  2司机发布车源
        $data['ordertype']  = 1;
        // 插入数据
        $inserId = Db::table('ct_issue_item')->insertGetId($data);
        if($inserId){
            $re_data['insert_id'] = $inserId;
            return json(['code'=>'1001','message'=>'提交成功','data'=>$re_data]);
        }else{
            return json(['code'=>'1002','message'=>'提交失败']);
        }
    }

    /**
     * 发布货物信息——确认发布货物信息
     * @Auther: 李渊
     * @Date: 2018.7.10
     * @return [type] [description]
     */
    public function affirm_issue_item()
    {
        // 令牌
        $token = input("token");
        // 订单ID
        $orderid = input("orderid");
        if(empty($token) || empty($orderid) ){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        // 查询订单
        $find = Db::table('ct_issue_item')->where('id',$orderid)->find();
        // 修改订单状态确认发布
        $data['paystate'] = 2;
        $result = Db::table('ct_issue_item')->where('id',$orderid)->update($data);
        // 发布成功
        if ($result) {
            $select = DB::table('ct_driver')->field('mobile')->where(array('delstate'=>'1'))->select();

            $startCity = addresidToName($find['start_city']);
            $endCity = addresidToName($find['end_city']);
            $center_list = '有【'.$startCity.'】到【'.$endCity.'】的货源信息发布了，请在手机应用市场下载【赤途承运端】直接登录查看吧！';
            $phone = $this->arr_to_str($select);
            // 群发短信
            send_imst($phone,$center_list);
            // 推送消息
            $this->send_note($typestate='4',$find['start_city'],$find['end_city'],'');
            return json(['code'=>'1001','message'=>'订单确认成功']);
        }else{
            return json(['code'=>'1002','message'=>'订单确认失败']);
        }
    }

    /**
     * 发布货物信息——取消发布货物信息
     * @Auther: 李渊
     * @Date: 2018.7.3
     * @return [type] [description]
     */
    public function cancel_issue_item()
    {
        $token = input("token");  //令牌
        $orderid = input("orderid");//订单ID
        $act_type = input("act_type");//操作类型  2已完成 3 手动取消
        if(empty($token) || empty($orderid) ){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        //手动取消
        $data['orderstate'] = $act_type;
        $result = Db::table('ct_issue_item')->where('id',$orderid)->update($data);
        if ($result) {
            return json(['code'=>'1001','message'=>'操作成功']);
        }else{
            return json(['code'=>'1002','message'=>'操作失败']);
        }
    }

    /**
     * 返回常用地址列表
     * @Auther: 李渊
     * @Date: 2018.7.3
     * @param string token 令牌
     * @param int cityid 城市ID
     * @return [type] [description]
     */
    public function user_address()
    {
        $token   = input("token");  //令牌
        $cityid   = input("cityid");  //城市ID
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        $array = array();
        $result = Db::table("ct_addressuser")
            ->alias('a')
            ->join('__DISTRICT__ d','a.pro_id = d.id')
            ->field("a.*,d.name as pro_name")
            ->where(array('a.user_id'=>$user_id,'city_id'=>$cityid))
            ->select();
        if (!empty($result)) {
            foreach ($result as $key => $value) {
                $array[$key]['address'] =detailadd($value['pro_id'],$value['city_id'],$value['area_id']).$value['address'];

            }
        }

        if(!empty($array)){
            return json(['code'=>'1001','message'=>'查询成功','data'=>$array]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }

    /*
     * 查询最低出价
     * */
    public function lowprice(){
        $token   = input("token");  //令牌
        $type   = input("type");  //城市ID
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        $data =  Db::table('ct_setting_price')->field('earnest,minnum')->where('type',$type)->find();

        if ($data){
            return json(['code'=>'1001','message'=>'查询成功','data'=>$data]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }

    /*
     * 市内配送
     * */
    public function incity(){
        $token   = input("token");  //令牌
        if(empty($token)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);//验证令牌

        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }

    }
}
