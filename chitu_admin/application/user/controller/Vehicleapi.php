<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Vehicleapi  extends Base{

    public function index(){
        return json(['code'=>110,'message'=>'赤途(上海)供应链管理有限公司']);
	}

    public function chentest(){
        echo "xxx";
    }
    /*
    *验证用户
    *@param username  string 用户名
    *@param password  string 密码
    */
    public function checktoken(){
        $username = trim(input('phone'));
        $password = trim(input('password'));
        if (empty($username) || empty($password)) {
           return json(['code'=>'1000','message'=>'参数错误']);
           exit();
        }
        //查找是否存在用户 如若不存在则传建用户并返回token值
        $result = Db::table("ct_user")->where("phone = $username and delstate = 1")->find();
        if($result){
             $new_password = MD5($password."ct888");
            if($new_password == $result['password']){
                //销毁密码
                unset($result['password']);
                //获取身份令牌
                $token = $this->product_token($result['uid']);
                return json(['code'=>'1001','message'=>'验证token成功','data'=>$token]);
                exit();
            }else{
                return json(['code'=>'1002','message'=>'验证失败']);
                exit();
            }
        }else{
             /**** 插入数据库*/
            $insert_data = array(
                'phone'=>$username,
                'userstate'=>'1',
                'password'=>MD5($password."ct888"),
                'username'=>'Chitu'.mt_rand('0000','9999'),
                'addtime'=>time()
            );
            $results = Db::table("ct_user")->insert($insert_data);
            $userId = Db::table('ct_user')->getLastInsID();
            $token = $this->product_token($userId);
            return json(['code'=>'1001','message'=>'首次验证token成功','data'=>$token]);
            exit();
        }
    }
    /*
    *插入市内配送数据
    *
    */
    public function city_with_api(){
        $receiveFile = 'cityorder.txt';
       $streamData = isset($GLOBALS['HTTP_RAW_POST_DATA'])? $GLOBALS['HTTP_RAW_POST_DATA'] : '';  
        if(empty($streamData)){  
            $streamData = file_get_contents('php://input');  
        }  
        if($streamData!=''){  
           $ret_get = file_put_contents($receiveFile, $streamData, true); 
            //$json = iconv('GBK','utf-8', $streamData);
            //$ret = json_decode($json,TRUE);
           $ret = json_decode($streamData,TRUE);
            Db::startTrans();
            try{
                if (!empty($ret)) {
                    foreach ($ret as $ke => $val){
                        $token = $val['token'];
                        if ($token=='') {
                           return json(['code'=>'1000','message'=>'参数错误']);
                           exit();
                        }
                        $check_result = $this->check_token($token);//验证令牌
                        if($check_result['status'] =='1'){
                            return json(['code'=>'1007','message'=>'非法请求']);
                        }elseif($check_result['status'] =='2'){
                            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
                        }else{
                            $user_id = $check_result['user_id'];
                        }
                        $data['userid'] =  $user_id;
                        $data['orderid'] = 'SP'.date('ymdhis').mt_rand('000','999'); // 订单编号
                        $coldtype = $val['cold_type'];
                        if ($coldtype =='1') {
                            $cole_str = '冷冻-15℃~-8℃';
                        }elseif ($coldtype =='2'){
                            $cole_str = '冷藏2℃~8℃';
                        }else{
                           $cole_str = '恒温12℃~18℃'; 
                        }
                         $data['cold_type'] =  $cole_str; //冷冻类型1冷冻2冷藏3恒温
                        $data['ordertype'] = $orordertype ='1';  //用车类型1用车车2包车
                        $data['data_type'] = $data_type = $val['data_type'];   //用车时间
                        $data['addtime'] = $addtime = time();
                        
                        $pick_name = isset($val['pickname']) ? $val['pickname']:''; //提提货人
                        $pick_phone = isset($val['pickphone']) ? $val['pickphone']:''; //提货人联系方式
                        $weight = $val['weight']; //重量
                        $volume = $val['volume']; //体积
                        $numbers = $val['numbers']; //件数
                        $remark = $val['remark']; //备注
                        //构造车型数组
                        $select_car = Db::table('ct_cartype')->select();
                        foreach ($select_car as $key => $value) {
                            $allweight = $value['allweight']*1000;
                            $arr[$value['car_id']] = $allweight;
                        }
                        //车型ID
                        $get_val = array_filter($arr, function($arr) use($weight) { return $weight <= $arr; }); 
                        sort($get_val);
                        $get_carid = array_search($get_val[0],$arr); 
                        $data['carid'] = $get_carid; //车型ID
                        $data['remark']      = $remark." 重量： ". $weight ."(kg) 体积： ".$volume."(m³) 件数： ".$numbers;  //备注
                        $saddress ='';
                        $eaddress = '';
                        $receive_name ='';
                        $receive_phone ='';
                        $pick_arr = array();
                        $send_arr = array();
                        $saddress = $val['saddress'];  //提货地址 [{"address":"dsdsdfdf"}]
                        $eaddress =  $val['eaddress'];  //收货地址 [{"address":"dsdsdfdf","addtabid":'1'},{"address":"dsdsdfdf","addtabid":'2'}]
                        $receive_name = $val['receivename']; //提提货人
                        $receive_phone = $val['receivephone']; //提货人联系方式
                        $data['paystate'] = 2;
                        $pick_arr[] = array(
                                    'address'=> $saddress ." ".$pick_name." ".$pick_phone
                            );
                        $send_arr[] = array(
                                    'address'=> $eaddress ." ".$receive_name." ".$receive_phone,
                                    'addtabid'=>'1'

                            );
                        

                        $pickadd = json_encode($pick_arr);
                        $sendadd = json_encode($send_arr);
                        $data['saddress'] = $pickadd;
                        $data['eaddress'] = $sendadd;
                        $city = "上海市";
                        $carmess = DB::table('ct_cartype')->where('car_id',$get_carid)->find(); //获取车型数据
                        $start_action = bd_local($type='2',$city,$saddress);//起始位置经纬度
                        $end_action = bd_local($type='2',$city,$eaddress);//重点位置经纬度
                        $finally ='';
                        $list = getDriverline($start_action['lat'], $start_action['lng'], $end_action['lat'], $end_action['lng']);
                        foreach ($list as $key=> $v) {
                            if ($key=="distance") {
                                $finally = $v['value']/1000;
                            }
                        }
                        $driver = "15021899770";
                        $search = Db::table('ct_driver')->where('mobile',$driver)->find();
                        //查找调度栾陈信息
                        $leader = "15800901483";
                        $search2 = Db::table('ct_driver')->where('mobile',$leader)->find();
                        $data['state'] = '2';
                        $rout_data['driverid'] = $search2['drivid'];
                        $rout_data['drivername'] = '陈德锦';
                        $rout_data['driverphone'] = '15021899770';
                        $rout_data['allotid'] = $search['drivid'];
                        $rout_data['carlicense'] = '沪DF3123';
                        //计算价格
                        $km = driver_km_rang((int)$finally);
                        if ($km =='') {
                            $km = $finally*1.05;
                        }
                        $price = $carmess['lowprice']+$km*$carmess['costkm'];
                        $data['actual_payment']  = round($price); // 用户未使用优惠卷价格
                        //$price = $carmess['lowprice']+$finally*1.05*$carmess['costkm'];
                        $price_parent = driver_money_rang($price,'1');
                        $data['paymoney'] =round($price-$price_parent);
                        $data['city_id'] ='45054';
                        $data['actualprice'] =round($price);
                        $rout_data['start_time'] = $addtime;
                        $rout_data['planid'] = $val['planid']; //航空货运网标识ID 
                        $rout_data['carnumber'] = $val['carnumber']; //航空h货运网装车单号
                        $rout_data['take_time'] = time();
                        $rout = Db::table('ct_rout_order')->insertGetId($rout_data);
                        $data['rout_id'] = $rout;
                        $data['pay_type'] = 1;
                        $insert = DB::table('ct_city_order')->insertGetId($data);

                        //查找用户公司信息
                        $user_mess = Db::table('ct_user')
                                        ->alias('u')
                                        ->join('ct_company c','c.cid=u.lineclient')
                                        ->field('c.money,c.cid,u.uid')
                                        ->where('uid',$user_id)
                                        ->find();
                        $shen_money = $user_mess['money']-$price;
                        //更新余额
                        Db::table('ct_company')->where('cid',$user_mess['cid'])->update(array('money'=>$shen_money));
                        //插入余额使用记录和更新余额
                        $order_content = "城配下单信用额度扣款";
                        $this->record('',$user_mess['uid'],$user_mess['cid'],$price,$shen_money,$order_content,'2','',$insert,'3');
                        $center_list = "已向您指派城配订单编号为：".$data['orderid']." 请您前往APP查看订单详情!!";
                        $send_phone = '15021899770,15800901483,18601648456';
                       // send_sms_class($send_phone,$center_list);
                        if($insert && $rout) {
                                $my_phone = '18302198119';
                                send_sms_class($send_phone,$center_list);

                                Db::commit();
                                //echo '操作成功';
                               // return json(['code'=>'1001','message'=>'操作成功','data'=>'']);
                        }
                    } //end foreach
                    return json(['code'=>'1001','message'=>'操作成功','data'=>'']);
                }   //end if
            }catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return json(['code'=>'1002','message'=>'操作失败']);
            }//end try
            /*if ($rout && $insert) {
                Db::commit(); 
                return json(['code'=>'1001','message'=>'操作成功','data'=>'']);
            }else{
                Db::rollback();
                return json(['code'=>'1002','message'=>'操作失败']);
            }*/
        }else{

            //$ret = false;  
            return json(['code'=>'1000','message'=>'无数据传输']);
        }  
      
     
    }
   
    /*
    *返回市内配送订单状态信息
    *
    */
    public function city_with_orderstatus(){
        $token = input('token');
        if (empty($token)) {
           return json(['code'=>'1000','message'=>'参数错误']);
           exit();
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

        $result = Db::table('ct_city_order')
                    ->alias('o')
                    ->join('ct_rout_order r','r.rid=o.rout_id')
                    ->where(array('userid'=>$user_id,'planid'=>['NEQ',''],'apitype'=>'1','o.state'=>['NEQ',1]))
                    ->select();
        foreach ($result as $key => $value) {
            $array[$key]['orderstate'] = $value['state'];  //订单类型1未接单2已接单3已完成
            $array[$key]['taketime'] = $value['take_time'].'000';  //接单时间
            $array[$key]['driver_name'] = $value['drivername'];  //司机名称
            $array[$key]['driver_phone'] = $value['driverphone'];  //司机联系方式
            $array[$key]['carlicense'] = $value['carlicense'];  //车牌号
            $array[$key]['planid'] = $value['planid'];  //航空货运标识ID
            $array[$key]['carnumber'] = $value['carnumber'];  //航空货运运单单号
             $array[$key]['finshtime'] = '';
            if ($value['finshtime'] !='') {
                $array[$key]['finshtime'] = $value['finshtime'].'000';  //订单结束时间
            }
           
            $array[$key]['apitype'] = $value['apitype'];  //是否获取完成 1 否 2 是
        } 
        if ($result) {
             return json(['code'=>'1001','message'=>'查询成功','data'=>$array]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }

    /*
    *返回市内配送订单回单
    */
    public function city_with_receipt(){
        $token = input('token');
        if (empty($token)) {
           return json(['code'=>'1000','message'=>'参数错误']);
           exit();
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }

        $city = Db::table('ct_city_order')
                    ->alias('o')
                    ->join('ct_rout_order r','r.rid=o.rout_id')
                    ->where(array('userid'=>$user_id,'planid'=>['NEQ',''],'apipicture'=>'1','o.state'=>['NEQ',1]))
                    ->select();
        $array = array();
        //$arr = array();
        foreach ($city as $key => $value) {           
                $pic = json_decode($value['picture'],TRUE);
                if (!empty($pic)) {
                    foreach ($pic as $k => $val) {
                        $arr[$k] ="https://" .$_SERVER['SERVER_NAME'].$val;
                    }
                }else{
                   $arr = array(); 
                }
               
                $array[$key]['picture'] = $arr;
            $array[$key]['planid'] = $value['planid'];
            $array[$key]['carnumber'] = $value['carnumber'];  //航空货运运单单号
            $array[$key]['apipicture'] = $value['apipicture'];
        }
        if ($city) {
             return json(['code'=>'1001','message'=>'查询成功','data'=>$array]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }
    /*
    *获取信息完修改对接状态
    */
    public function city_with_updata(){
        $token = input('token');
        $planid = input('planid');
        if (empty($token) || empty($planid)) {
           return json(['code'=>'1000','message'=>'参数错误']);
           exit();
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        
        $data['apitype'] = 2;
     
        
        $result = Db::table('ct_rout_order')->where('planid',$planid)->update($data);
        if ($result) {
             return json(['code'=>'1001','message'=>'操作成功']);
        }else{
            return json(['code'=>'1002','message'=>'操作失败']);
        }

    }

    
    /*
    *获取订单完修改对接状态
    */
    public function city_with_pictype(){
        $token = input('token');
        $planid = input('planid');
        if (empty($token) || empty($planid)) {
           return json(['code'=>'1000','message'=>'参数错误']);
           exit();
        }
        $check_result = $this->check_token($token);//验证令牌
        if($check_result['status'] =='1'){
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] =='2'){
            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
        }else{
            $user_id = $check_result['user_id'];
        }
        $data['apipicture'] = 2;
        $result = Db::table('ct_rout_order')->where('planid',$planid)->update($data);
        if ($result) {
             return json(['code'=>'1001','message'=>'操作成功']);
        }else{
            return json(['code'=>'1002','message'=>'操作失败']);
        }

    }

    public function send(){
           $receiveFile = 'cityorder.txt';
       //$streamData = isset($GLOBALS['HTTP_RAW_POST_DATA'])? $GLOBALS['HTTP_RAW_POST_DATA'] : '';  
      //  if(empty($streamData)){  
            $streamData = file_get_contents($receiveFile);  
       // }  
            $streamData2 ='111';
        if($streamData2!=''){  
           //$ret_get = file_put_contents($receiveFile, $streamData, true); 
            $json = iconv('GBK','UTF-8//TRANSLIT//IGNORE', $streamData);
            $ret = json_decode($json,TRUE);
            //print_r($ret);exit();
            Db::startTrans();
            try{
                foreach ($ret as $ke => $val){
                    $token = $val['token'];
                        if ($token=='') {
                           return json(['code'=>'1000','message'=>'参数错误']);
                           exit();
                        }
                        $check_result = $this->check_token($token);//验证令牌
                        if($check_result['status'] =='1'){
                            return json(['code'=>'1007','message'=>'非法请求']);
                        }elseif($check_result['status'] =='2'){
                            return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
                        }else{
                            $user_id = $check_result['user_id'];
                        }
                        //$user_id = '7';
                        $data['userid'] =  $user_id;
                        $data['orderid'] = 'SP'.date('ymdhis').mt_rand('000','999'); // 订单编号
                        $coldtype = $val['cold_type'];
                        if ($coldtype =='1') {
                            $cole_str = '冷冻-15℃~-8℃';
                        }elseif ($coldtype =='2'){
                            $cole_str = '冷藏2℃~8℃';
                        }else{
                           $cole_str = '恒温12℃~18℃'; 
                        }
                         $data['cold_type'] =  $cole_str; //冷冻类型1冷冻2冷藏3恒温
                        $data['ordertype'] = $orordertype ='1';  //用车类型1用车车2包车
                        $data['data_type'] = $data_type = $val['data_type'];   //用车时间
                        $data['addtime'] = $addtime = time();
                        $pick_name = $val['pickname']; //提提货人
                        $pick_phone = $val['pickphone']; //提货人联系方式
                        $weight = $val['weight']; //重量
                        $volume = $val['volume']; //体积
                        $numbers = $val['numbers']; //件数
                        $remark = isset($val['remark']); //备注
                        //构造车型数组
                        $select_car = Db::table('ct_cartype')->select();
                        foreach ($select_car as $key => $value) {
                            $allweight = $value['allweight']*1000;
                            $arr[$value['car_id']] = $allweight;
                        }
                        //车型ID
                        $get_val = array_filter($arr, function($arr) use($weight) { return $weight <= $arr; }); 
                        sort($get_val);
                        $get_carid = array_search($get_val[0],$arr); 
                        $data['carid'] = $get_carid; //车型ID
                        $data['remark']      = $remark." 重量： ". $weight ."(kg) 体积： ".$volume."(m³) 件数： ".$numbers;  //备注
                        $saddress ='';
                        $eaddress = '';
                        $receive_name ='';
                        $receive_phone ='';
                        $pick_arr = array();
                        $send_arr = array();
                        $saddress = $val['saddress'];  //提货地址 [{"address":"dsdsdfdf"}]
                        $eaddress =  $val['eaddress'];  //收货地址 [{"address":"dsdsdfdf","addtabid":'1'},{"address":"dsdsdfdf","addtabid":'2'}]
                        $receive_name = $val['receivename']; //提提货人
                        $receive_phone = $val['receivephone']; //提货人联系方式
                        $data['paystate'] = 2;
                        $pick_arr[] = array(
                                    'address'=> $saddress ." ".$pick_name." ".$pick_phone
                            );
                        $send_arr[] = array(
                                    'address'=> $eaddress ." ".$receive_name." ".$receive_phone,
                                    'addtabid'=>'1'

                            );
                        

                        $pickadd = json_encode($pick_arr);
                        $sendadd = json_encode($send_arr);
                        $data['saddress'] = $pickadd;
                        $data['eaddress'] = $sendadd;
                        $city = "上海市";
                        $carmess = DB::table('ct_cartype')->where('car_id',$get_carid)->find(); //获取车型数据
                        $start_action = bd_local($type='2',$city,$saddress);//起始位置经纬度
                        $end_action = bd_local($type='2',$city,$eaddress);//重点位置经纬度
                        $finally ='';
                        $list = getDriverline($start_action['lat'], $start_action['lng'], $end_action['lat'], $end_action['lng']);
                        foreach ($list as $key=> $v) {
                            if ($key=="distance") {
                                $finally = sprintf("%.2f",$v['value']/1000);
                            }
                        }
                        $driver = "15021899770";
                        $search = Db::table('ct_driver')->where('mobile',$driver)->find();
                        //查找调度栾陈信息
                        $leader = "15800901483";
                        $search2 = Db::table('ct_driver')->where('mobile',$leader)->find();
                        $data['state'] = '2';
                        $rout_data['driverid'] = $search2['drivid'];
                        $rout_data['drivername'] = '陈德锦';
                        $rout_data['driverphone'] = '15021899770';
                        $rout_data['allotid'] = $search['drivid'];
                        $rout_data['carlicense'] = '沪DF3123';
                        //计算价格
                        $price = $carmess['lowprice']+$finally*1.05*$carmess['costkm'];
                        $price_parent = driver_money_rang($price,'1');
                        $data['paymoney'] =round($price-$price_parent);
                        $data['actualprice'] =round($price);
                        $rout_data['start_time'] = $addtime;
                        $rout_data['planid'] = $val['planid']; //航空货运网标识ID 
                        $rout_data['carnumber'] = $val['carnumber']; //航空h货运网装车单号
                        $rout_data['take_time'] = time();
                        $rout = Db::table('ct_rout_order')->insertGetId($rout_data);
                        $data['rout_id'] = $rout;
                        $data['pay_type'] = 1;
                        $insert = DB::table('ct_city_order')->insertGetId($data);
                        //查找用户公司信息
                        $user_mess = Db::table('ct_user')
                                        ->alias('u')
                                        ->join('ct_company c','c.cid=u.lineclient')
                                        ->field('c.money,c.cid')
                                        ->where('uid',$user_id)
                                        ->find();
                        $shen_money = $user_mess['money']-$price;
                        //更新余额
                        Db::table('ct_company')->where('cid',$user_mess['cid'])->update(array('money'=>$shen_money));
                        //插入余额使用记录和更新余额
                        $order_content = "城配线路下单扣款，订单号：".$data['orderid'];
                        $this->record('',$user_mess['cid'],$price,$shen_money,$order_content,'2','','company');
                        $center_list = "已向您指派城配订单编号为：".$data['orderid']." 请您前往APP查看订单详情!!";
                        //$send_phone = '15021899770,15800901483,18601648456';
                        //send_sms_class($send_phone,$center_list);

                    if($insert && $rout) {
                            $my_phone = '18302198119';
                            send_sms_class($my_phone,$center_list);

                            Db::commit();
                            echo '操作成功';
                           // return json(['code'=>'1001','message'=>'操作成功','data'=>'']);
                    }
                }
              

            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
                return json(['code'=>'1002','message'=>'操作失败']);
            }

             /*if ($rout && $insert) {
                    Db::commit(); 
                    return json(['code'=>'1001','message'=>'操作成功','data'=>'']);
                }else{
                    Db::rollback();
                    return json(['code'=>'1002','message'=>'操作失败']);
                }*/
        }else{

            //$ret = false;  
            return json(['code'=>'1000','message'=>'无数据传输']);
        }  
    }



   

}
