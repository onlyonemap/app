<?php
namespace app\driver\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Shift extends Base{
    /*
    *我的公司信息
    */
    
    public function mycompany(){
        $token = input('token');  //验证令牌
        if (empty($token)) {
            return json(['code'=>'1000','message'=>'参数错误']);
        } 
        $check_result = $this->check_token($token);  //验证令牌
        if ($check_result['status'] == '1') {
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif($check_result['status'] == '2'){
            return json(['code'=>'1008','message'=>'token已过去，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }

        $driver = Db::table('ct_driver')->where('drivid',$driver_id)->find();
        if (empty($driver['companyid'])) {
            return json(['code'=>'1002','message'=>'暂无数据']);
        }else{
            $com_message = Db::table('ct_company')->field('cid,avatar,name,type,provinceid,money,areaid,cityid,address')->where('cid',$driver['companyid'])->find();
            $com_message['companyid'] = $com_message['cid'];
            $com_message['pro_str'] = detailadd($com_message['provinceid'],'','');
            $com_message['city_str'] = detailadd($com_message['cityid'],'','');
            $com_message['area_str'] = detailadd($com_message['areaid'],'','');
            $com_message['comaddress'] = detailadd($com_message['provinceid'],$com_message['cityid'],$com_message['areaid']).$com_message['address'];
            return json(['code'=>'1001','message'=>'查询成功','data'=>$com_message]);
        }
    }

    /*
    *班次列表
    */
    public function shiftlist(){
        $token = input('token');  //token令牌
        if (empty($token)) {
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);
        if ($check_result['status'] == '1') {
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif ($check_result['status'] == '2') {
            return json(['code'=>'1008','message'=>'token已过去，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        $result = Db::table('ct_shift')
                    ->field('weekday,price,pmoney,smoney,addtime,sid,deltime,whethertoopen,linecityid,trunkaging')
                    ->where(array('driver_id'=>$driver_id,'shiftstate'=>'2','delstate'=>'1'))
                    ->order('addtime desc')
                    ->paginate(10);
        $res = $result->toArray();
        $arr = array();
        foreach ($res['data'] as $key => $value) {
            $arr[$key]['weekday'] = $value['weekday'];  //班期
            $arr[$key]['trunkaging'] = $value['trunkaging'];  //天数
            $arr[$key]['price'] = $value['price'];  //每公斤价格
            $arr[$key]['sid'] = $value['sid']; //班次ID
            $arr[$key]['deltime'] = $value['deltime'];  // 删除时间
            $arr[$key]['addtime'] = $value['addtime'];  //添加时间
            $arr[$key]['smoney'] = $value['smoney'];  //配送费
            $arr[$key]['pmoney'] = $value['pmoney']; //提货费
            $arr[$key]['whethertoopen'] = $value['whethertoopen']; //1开启 2 关闭
            $line = Db::table('ct_already_city')->where('city_id',$value['linecityid'])->find();
            $arr[$key]['startcity'] = detailadd($line['start_id'],'','');
            $arr[$key]['endcity'] = detailadd($line['end_id'],'','');
        }
        if (empty($arr)) {
            return json(['code'=>'1002','message'=>'暂无数据']);
        }else{
            return json(['code'=>'1001','message'=>'查询成功','data'=>$arr]);
        } 
    }
    /*
    *添加班次
    */
    public function addshift(){
        $token = input('token'); //token令牌
        if (empty($token)) {
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);
        if ($check_result['status'] == '1') {
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif ($check_result['status'] == '2') {
            return json(['code'=>'1008','message'=>'token已过去，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        $startcity = input('startcity'); //起始城市
        $endcity = input('endcity'); //终点城市
        $shiftdata['price'] = $price = input('price');  //干线每公斤价格
        $shiftdata['lowprice'] =  $lowprice = input('lowprice'); //干线最低收费
        $shiftdata['pmoney'] =  $pmoney = input('pmoney'); //提货费
        $shiftdata['smoney'] =  $smoney = input('smoney'); //配送费
        $shiftdata['weekday'] =  $weekday = input('weekday'); //班期
        $shiftdata['driver_id'] =  $driver_id; //添加人ID
        $shiftdata['trunkaging'] =  $trunkaging = input('trunkaging'); //时效 天数
        $shiftdata['shiftstate'] =  '2'; //1平台2用户
        $shiftdata['addtime'] =  time(); //添加时间


        //排除城市是否存在
        $find_city = Db::table('ct_already_city')->where(array('start_id'=>$startcity,'end_id'=>$endcity))->find();
        if ($find_city=='') {
            $city_data['start_id'] =  $startcity;
            $city_data['end_id'] = $endcity;
            $city_data['add_time'] = time();
            //插入开通城市
            $city_id = Db::table('ct_already_city')->insertGetId($city_data);
        }else{
            $city_id = $find_city['city_id'];
        }
        $shiftdata['linecityid'] = $city_id;  //开通城市ID
        //插入班次列表
        $shift_ins = Db::table('ct_shift')->insertGetId($shiftdata);
        //发车队列
        //第二天时间
        $endToday=mktime(12,0,0,date('m'),date('d')+1,date('Y'));
        $date_log['deptime'] = $endToday;
        $date_log['endtime'] = '';
        $date_log['tonnage'] = '';
        $date_log['volume'] = '';
        $date_log['status'] = '3';
        $date_log['shiftid'] = $shift_ins;
        Db::table('ct_shift_log')->insert($date_log);
        if ($shift_ins) {
            return json(['code'=>'1001','message'=>'操作成功']);
        }else{
            return json(['code'=>'1002','message'=>'操作失败']);
        }
    }
    /*
     * 添加线路 2019.6.14
     * */
    public function addline(){
        $token = input('token'); //token令牌
        if (empty($token)) {
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);
        if ($check_result['status'] == '1') {
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif ($check_result['status'] == '2') {
            return json(['code'=>'1008','message'=>'token已过去，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        $startcity =$shiftdata['begincityid'] = input('startcity'); //起始城市
        $endcity = $shiftdata['endcityid'] = input('endcity'); //终点城市
        $shiftdata['price'] = $price = input('price');  //干线每公斤价格
        $shiftdata['lowprice'] =  $lowprice = input('lowprice'); //干线最低收费
        $shiftdata['pmoney'] =  $pmoney = input('pmoney'); //提货费
        $shiftdata['smoney'] =  $smoney = input('smoney'); //配送费
        $shiftdata['weekday'] =  $weekday = input('weekday'); //班期
        $shiftdata['driver_id'] =  $driver_id; //添加人ID
        $shiftdata['trunkaging'] =  $trunkaging = input('trunkaging'); //时效 天数
        $shiftdata['shiftstate'] =  '2'; //1平台2用户
        $shiftdata['addtime'] =  time(); //添加时间

        $shiftdata['picksite'] = input('picksite'); //收货地址
        $shiftdata['sendsite'] = input('sendsite'); //提货地址
        $shiftdata['sphone'] = input('sphone');//收货联系电话
        $shiftdata['tphone'] = input('tphone');//提货联系电话
        $shiftdata['stime'] = input('stime');//收货时间段
        $shiftdata['dtime'] = input('dtime');//提货时间段
        $shiftdata['picktype'] = input('picktype');
        $shiftdata['sendtype'] = input('sendtype');
        //排除城市是否存在
        $find_city = Db::table('ct_already_city')->where(array('start_id'=>$startcity,'end_id'=>$endcity))->find();
        if ($find_city=='') {
            $city_data['start_id'] =  $startcity;
            $city_data['end_id'] = $endcity;
            $city_data['add_time'] = time();
            //插入开通城市
            $city_id = Db::table('ct_already_city')->insertGetId($city_data);
        }else{
            $city_id = $find_city['city_id'];
        }
        $shiftdata['linecityid'] = $city_id;  //开通城市ID
        //插入班次列表
        $shift_ins = Db::table('ct_shift')->insertGetId($shiftdata);
        //发车队列
        //第二天时间
        $endToday=mktime(12,0,0,date('m'),date('d')+1,date('Y'));
        $date_log['deptime'] = $endToday;
        $date_log['endtime'] = '';
        $date_log['tonnage'] = '';
        $date_log['volume'] = '';
        $date_log['status'] = '3';
        $date_log['shiftid'] = $shift_ins;
        Db::table('ct_shift_log')->insert($date_log);
        if ($shift_ins) {
            return json(['code'=>'1001','message'=>'操作成功']);
        }else{
            return json(['code'=>'1002','message'=>'操作失败']);
        }
    }
    /*
     * 线路详情
     * */
    public function shiftview(){
        $token = input('token'); //验证令牌
        $id = input('sid') ;
        if (empty($token) || empty($id)) {
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);
        if ($check_result['status'] == '1') {
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif ($check_result['status'] == '2') {
            return json(['code'=>'1008','message'=>'token已过去，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        $data = Db::table('ct_shift')
            ->field('sid,linecityid,addtime,weekday,price,lowprice,trunkaging,deltime,delstate,picksite,sendsite,sphone,tphone,stime,dtime')
            ->where('driver_id',$driver_id)
            ->where('sid',$id)
            ->find();
        $arr = Db::table('ct_already_city')->where('city_id',$data['linecityid'])->find();
        $data['startcity'] = detailadd($arr['start_id'],'','');
        $data['endcity'] = detailadd($arr['end_id'],'','');
        if($data){
            return json(['code'=>'1001','message'=>'查询成功','data'=>$data]);
        }else{
            return json(['code'=>'1002','message'=>'暂无数据']);
        }
    }
 
   
    /*
    *关闭班次和删除
    */
    public function delshift(){
        $token = input('token'); //验证令牌
        $id = input('id');  //ID
        if (empty($token) || empty($id)) {
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        $check_result = $this->check_token($token);
        if ($check_result['status'] == '1') {
            return json(['code'=>'1007','message'=>'非法请求']);
        }elseif ($check_result['status'] == '2') {
            return json(['code'=>'1008','message'=>'token已过去，请重新登录']);
        }else{
            $driver_id = $check_result['driver_id'];
        }
        $data['whethertoopen'] = 2;
        $data['deltime'] = time();
        $result = Db::table('ct_shift')->where('sid',$id)->update($data);

        if ($result) {
            Db::table('ct_shift_log')->where('shiftid',$id)->update(array('status'=>'2'));
            return json(['code'=>'1001','message'=>'操作成功']);
        }else{
            return json(['code'=>'1002','message'=>'操作失败']);
        }
    }
    /*
     * 滚动条内容获取
     * */
   public function getContent(){
       $type = input('type');//1零担 2整车 3城配
             $list = Db::table('ct_dynamic_content')
                 ->field('content')
                 ->where('state',1)
                 ->where('type',$type)
                 ->order('createtime DESC')
                 ->limit(3)
                 ->select();
       if ($list){
           return json(['code'=>'1001','message'=>'查询成功','data'=>$list]);
       }else{
           return json(['code'=>'1002','message'=>'暂无数据']);
       }
   }
}
