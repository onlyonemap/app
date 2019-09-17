<?php
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request;
use think\Session;
class Setting  extends Base
{
	function __construct(){
        parent::__construct();
        $this->uid = Session::get('admin_id','admin_mes');
        $this->if_login();
    }

    /**
     * 前往平台基础设置页面
     * @Auther: 李渊
     * @Date: 2018.6.22
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function index() {
        // 查询用户版：IOS（1整包升级，2差量升级，3不升级）
        $update_userios_config = DB::table('ct_config')->where('id',5)->find();
        $setting['update_userios_config'] = $update_userios_config['auth_price'];
        // 查询用户版：Android（1整包升级，2差量升级，3不升级）
        $update_userand_config = DB::table('ct_config')->where('id',6)->find();
        $setting['update_userand_config'] = $update_userand_config['auth_price'];
        // 查询用户版苹果版本号
        $update_userios_version = DB::table('ct_config')->where('id',7)->find();
        $setting['update_userios_version'] = $update_userios_version['auth_price'];
         // 查询用户版安卓版本号
        $update_userand_version = DB::table('ct_config')->where('id',16)->find();
        $setting['update_userand_version'] = $update_userand_version['auth_price'];


        // 查询承运端：IOS（1整包升级，2差量升级，3不升级）
        $update_driverios_config = DB::table('ct_config')->where('id',9)->find();
        $setting['update_driverios_config'] = $update_driverios_config['auth_price'];
        // 查询承运端：Android（1整包升级，2差量升级，3不升级）
        $update_driverand_config = DB::table('ct_config')->where('id',10)->find();
        $setting['update_driverand_config'] = $update_driverand_config['auth_price'];
        // 查询承运端苹果版本号
        $update_driverios_version = DB::table('ct_config')->where('id',11)->find();
        $setting['update_driverios_version'] = $update_driverios_version['auth_price'];
        // 查询承运端苹果版本号
        $update_driverand_version = DB::table('ct_config')->where('id',17)->find();
        $setting['update_driverand_version'] = $update_driverand_version['auth_price'];

        // 查询司机接单金额限制
        $driver_robbing = DB::table('ct_config')->where('id',12)->find();
        $setting['driver_robbing'] = $driver_robbing['auth_price'];
        // 查询信息发布收取费用
        $Infor_delivery = DB::table('ct_config')->where('id',1)->find();
        $setting['Infor_delivery'] = $Infor_delivery['auth_price'];
        // 查询积分换算设置
        $integral_setting = DB::table('ct_config')->where('id',13)->find();
        $setting['integral_setting'] = $integral_setting['auth_price'];
        // 查询承运端：运费提现费率
        $driver_freight_rate = DB::table('ct_config')->where('id',14)->find();
        $setting['driver_freight_rate'] = $driver_freight_rate['auth_price'];
        // 查询承运端：余额提现费率
        $driver_balance_rate = DB::table('ct_config')->where('id',15)->find();
        $setting['driver_balance_rate'] = $driver_balance_rate['auth_price'];

        $this->assign('setting', $setting);
        return view('setting/index');
    }

    /**
     * page
     * 前往低价线路配置页面
     * @auther: 李渊
     * @Date: 2018.1.3
     * @return [type] [description]
     */
    public function lowprice()
    {
        // 低价整车 : 同城发布参考价
        $city_price_low = DB::table('ct_config')->where('id',18)->find();
        $setting['city_price_low'] = $city_price_low['auth_price'];
        // 低价整车 : 同城发布发布价低于参考价时增加费用
        $city_price_reduce = DB::table('ct_config')->where('id',19)->find();
        $setting['city_price_reduce'] = $city_price_reduce['auth_price'];
        // 低价整车 : 同城发布发布价低于参考价时增加百分比
        $city_price_add = DB::table('ct_config')->where('id',20)->find();
        $setting['city_price_add'] = $city_price_add['auth_price'];
        // 低价整车 : 城际发布参考价
        $vehicle_price_low = DB::table('ct_config')->where('id',21)->find();
        $setting['vehicle_price_low'] = $vehicle_price_low['auth_price'];
        // 低价整车 : 城际发布发布价低于参考价时增加费用
        $vehicle_price_reduce = DB::table('ct_config')->where('id',22)->find();
        $setting['vehicle_price_reduce'] = $vehicle_price_reduce['auth_price'];
        // 低价整车 : 城际发布发布价低于参考价时增加百分比
        $vehicle_price_add = DB::table('ct_config')->where('id',23)->find();
        $setting['vehicle_price_add'] = $vehicle_price_add['auth_price'];

        $this->assign('setting', $setting);
        return view('setting/low_price');
    }

    /**
     * 修改平台基础设置
     * @Auther: 李渊
     * @Date: 2018.6.22
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function update() {
        // 获取修改类型
        $id = input('id');
        // 获取修改后的值
        $data['auth_price'] = input('updateVal');
        // 修改数据
        $update = DB::table('ct_config')->where('id',$id)->update($data);
        // 判断是否修改成功
        if ($update) {
            return true;
        }else{
            return false;
        }
    }

    /**
     * to 城配、整车计费系数页面
     * @Auther: 李渊
     * @Date: 2018.6.14
     * @return [type]        [description]
     */
    public function toscalePrice() {
        // 查询城配费用系数标准
        $resultCity = Db::table('ct_price_setting')->where('type','1')->find();
        // 查询整车费用系数标准
        $resultVehicle = Db::table('ct_price_setting')->where('type','2')->find();
        $this->assign('priceCity', $resultCity);
        $this->assign('priceVehicle', $resultVehicle);
        return view('setting/scalePrice');
    }

    /**
     * update 城配、整车计费系数
     * @Auther: 李渊
     * @Date: 2018.6.14
     * @param  [Int] $type [修改类型 1 城配 2 整车]
     * @param  [Int] $scaleType [修改的系数类型 1起步价系数 2里程偏离系数 3单公里价格系数 4装货费用系数 5卸货费用系数 6多点提配系数]
     * @param  [Int] $updateVal [修改的系数类型的值]
     * @return [type]        [description]
     */
    public function updateScalePrice() {
        // 获取修改类型
        $type = input('type');
        // 获取修改的系数类型 1起步价系数 2里程偏离系数 3单公里价格系数 4装货费用系数 5卸货费用系数 6多点提配系数 7 优惠折扣
        $scaleType = input('scaleType');
        // 获取修改的系数类型的值
        $updateVal = input('updateVal');
        // 判断修改类型
        if($type == "1"){ // 城配
            $where['type'] = 1;
        }else{ // 整车
            $where['type'] = 2;
        }
        // 判断修改参数
        switch ($scaleType) {
            case '1': // 起步价系数
                $update['scale_startprice'] = $updateVal;
                break;
            case '2': // 里程偏离系数0-100
                $update['scale_km'] = $updateVal;
                break;
            case '3': // 里程偏离系数100-300
                $update['scale_km_two'] = $updateVal;
                break;
            case '4': // 里程偏离系数300-1000
                $update['scale_km_three'] = $updateVal;
                break;
            case '5': // 里程偏离系数1000以上
                $update['scale_km_four'] = $updateVal;
                break;
            case '6': // 单公里价格系数
                $update['scale_price_km'] = $updateVal;
                break;
            case '7': // 装货费用系数
                $update['scale_pickgood'] = $updateVal;
                break;
            case '8': // 卸货费用系数
                $update['scale_sendgood'] = $updateVal;
                break;
            case '9': // 多点提配系数
                $update['scale_multistore'] = $updateVal;
                break;
            case '10': // 当日配送费用系数
                $update['scale_sameday'] = $updateVal;
                break;
            case '11': // 次日配送费用系数
                $update['scale_seconday'] = $updateVal;
                break;
            case '12': // 两天后提配费用系数
                $update['scale_moreday'] = $updateVal;
                break;
            case '13': // 优惠折扣
                $update['scale_discount'] = $updateVal;
                break;
            case '14': // 优惠折扣
                $update['goback'] = $updateVal;
                break;
            default:
                # code...
                break;
        }
        // 查询城配费用系数标准
        $result = Db::table('ct_price_setting')->where($where)->find();
        // 判断是否有数据 没有则重新插入 有则更新
        if(!$result['type']){
            $update['type'] = $type;
            $res = Db::table('ct_price_setting')->insertGetId($update);
        }else{
            $res = Db::table('ct_price_setting')->where($where)->update($update);
        }
        // 判断是否修改成功
        if($res){
            return true;
        }else{
            return false;
        }
    }

    /**
     * to 赤途冷链促销设置页面
     * @Auther: 李渊
     * @Date: 2018.6.14
     * @param string $value [description]
     */
    public function promotion() {
        // 查询前十单优惠
        $tenOrder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 1))->find();
        if(empty($tenOrder)){
            $insertData['type'] = 1;
            $insertData['promotionType'] = 1;
            Db::table('ct_promotion')->insert($insertData);
            $tenOrder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 1))->find();
        }
        // 查询整点订单优惠
        $wholeOrder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 2))->find();
        if(empty($wholeOrder)){
            $insertData['type'] = 1;
            $insertData['promotionType'] = 2;
            Db::table('ct_promotion')->insert($insertData);
            $wholeOrder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 2))->find();
        }
        // 查询订单号后两位相同的订单优惠
        $twoSameorder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 3))->find();
        if(empty($twoSameorder)){
            $insertData['type'] = 1;
            $insertData['promotionType'] = 3;
            Db::table('ct_promotion')->insert($insertData);
            $threeSameorder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 4))->find();
        }
        // 查询订单号后两位相同的订单优惠
        $threeSameorder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 4))->find();
        if(empty($threeSameorder)){
            $insertData['type'] = 1;
            $insertData['promotionType'] = 4;
            Db::table('ct_promotion')->insert($insertData);
            $threeSameorder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 4))->find();
        }
        // 订单后两位等于当天日期
        $dateOrder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 5))->find();
        if(empty($dateOrder)){
            $insertData['type'] = 1;
            $insertData['promotionType'] = 5;
            Db::table('ct_promotion')->insert($insertData);
            $dateOrder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 5))->find();
        }
        // 首个订单
        $firstOrder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 6))->find();
        if(empty($firstOrder)){
            $insertData['type'] = 1;
            $insertData['promotionType'] = 6;
            Db::table('ct_promotion')->insert($insertData);
            $firstOrder = Db::table('ct_promotion')->where(array('type' => 1,'promotionType' => 6))->find();
        }

        // 查询整车费用系数标准
        $result['tenOrder'] = $tenOrder;
        $result['wholeOrder'] = $wholeOrder;
        $result['twoSameorder'] = $twoSameorder;
        $result['threeSameorder'] = $threeSameorder;
        $result['dateOrder'] = $dateOrder;
        $result['firstOrder'] = $firstOrder;
        // 显示
        $this->assign('priceCity', $result);
        return view('setting/promotion');
    }

    /**
     * 更新促销折扣系数
     * @Auther: 李渊
     * @Date: 2018.6.27
     * 修改的系数类型 定义以下规则
     * 1、tenOrder 促销日前十单优惠折扣
     * 2、wholeOrder 促销日整点订单优惠折扣
     * 3、twoSameorder 促销日订单号后两位相同的订单优惠折扣
     * 4、threeSameorder 促销日订单号后三位相同的订单优惠折扣
     * 5、dateOrder 促销日订单后两位等于当天日期的优惠折扣
     * @param  [Int] $type [修改类型 1 城配 2 整车]
     * @param  [Int] $scaleType
     * @return [type]        [description]
     */
    public function updatePromotion() {
        // 优惠项目 1 城配 2 整车
        $type = input('type');
        // 开始时间
        $startTime = input('startTime');
        $startTime = strtotime($startTime);
        // 结束时间
        $endTime = input('endTime');
        $endTime = strtotime($endTime);
        // 优惠的规则类型
        $promotionType = input('index');
        // 折扣
        $scale = input('scale');
        // 开关
        $switch = input('switchtab');
        // 查询条件
        $where['type'] = $type;
        $where['promotionType'] = $promotionType;
        // 更新数据、
        $update['startTime'] = $startTime;
        $update['endTime'] = $endTime;
        $update['scale'] = $scale;
        $update['switch'] = $switch;
        // 更新
        $res = Db::table('ct_promotion')->where($where)->update($update);
        // 判断是否修改成功
        if($res){
            return true;
        }else{
            return false;
        }
    }





    public function clearcache()
    {
         /*$test = \think\Config::get();
        echo "<pre/>";
        print_r($test['cache']);
        $dh = */

        $FilePath2="../runtime/temp/";
        $FilePath = opendir($FilePath2);
        $FileAndFolderAyy=array();
        $i=1;
        while (false !== ($filename = readdir($FilePath))) {
            if ($filename!="." && $filename!=".."){
            $i++;
            @unlink($FilePath2.$filename);
            $FileAndFolderAyy[$i]['name'] = $filename;
            //$FileAndFolderAyy[$i]['time'] = $this->getfiletime($filename);
            //$FileAndFolderAyy[$i]['time'] = filectime("$FilePath2/$filename");
            //$FileAndFolderAyy[$i]['size'] = $this->getFilesize($FilePath2.$filename);
            //$FileAndFolderAyy[$i]['time'] = filectime("$FilePath2/$filename");
            }
        }
        closedir($FilePath);
        print_r("ok");

    }
    /*
    *短信模板页面
    */
    public function messtemp(){
        $result = DB::table('ct_message_temp')->select();
        $this->assign('list',$result);
        return view('setting/messtemp');
    }
     /*
    *添加短信模板页面
    */
    public function addtemp(){

        return view('setting/addtemp');
    }
    /*
    *添加短信模板提交动作
    */
    public function posttemp(){

        $post_data = Request::instance()->post();
        //var_dump($post_data);exit();
        $data['mess_type'] = $post_data['mess_type'];
        $data['mess_str'] = $post_data['mess_str'];
        $data['message'] = $post_data['message'];
        $data['description'] = $post_data['description'];
        $data['type'] = $post_data['type'];
        $data['mess_state'] = $post_data['mess_state'];
        $insData = DB::table('ct_message_temp')->insert($data);
        if ($insData) {
            $content = "增加了".$post_data['mess_type']."短信模板信息";
            $this->hanldlog($this->uid,$content);
            $this->success('添加成功','setting/addtemp');
        }else{
            $this->error('添加失败');
        }
    }
    /*
    *获取短信字段返回短信内容
    */
    public function updatetemp(){
        $getname = Request::instance()->get('name');
        if ($getname != '') {
            $selectSql = DB::table('ct_message_temp')->where('mess_str',$getname)->find();
            $arr = array('statu'=>1,'data'=>array('id'=>$selectSql['mid'],'content'=>$selectSql['message'],'description'=>$selectSql['description']));
            return json_encode($arr);
        }
        //echo $test;

    }
    /*
    *获取短信类型返回短信模板字段
    *
    */
    public function get_temp_name(){
        $data_type = Request::instance()->post('mess_state');
        $select = DB::table('ct_message_temp')->where('mess_state',$data_type)->select();
        echo json_encode($select);
    }
     /*
    *修改短信模板内容
    */
    public function uptemp(){
        $post_data = Request::instance()->post();
        if ($post_data =='') {
            $this->error("请选择你要修改的短信模板!!");
        }
        $id = $post_data['id'];
        $data['message'] = $post_data['message'];
        if ($post_data['select_state'] !='') {
            $data['mess_state'] = $post_data['select_state'];
        }
        //print_r($post_data);exit();
        $up= DB::table('ct_message_temp')->where('mid',$id)->update($data);

       if ($up) {
            $content = "修改了ID:".$id."短信模板信息";
            $this->hanldlog($this->uid,$content);
           $this->success('修改成功!!!','setting/messtemp');
       }else{
           $this->error('修改失败!!!');
       }
    }

    public function messindex(){
        $select = DB::table('ct_greet_message')
                    ->order('g_id','desc')
                    ->paginate(10);
        $this->assign('list',$select);
        return view('setting/messindex');
    }

    public function addmess(){
        return view('setting/addmess');
    }
    public function postmess(){
        $post = Request::instance()->post();
        if (isset($post['user_defined'])) {
            $data['user_defined'] = $post['user_defined'];
        }
        if (isset($post['send_user'])) {
            $data['send_user'] = $post['send_user'];
        }

        $data['send_message'] = $post['send_message'];
        $data['send_date'] = strtotime($post['send_date']);
        $data['mess_type'] = $post['mess_type'];
        $data['send_type'] = $post['send_type'];

        $inser = DB::table('ct_greet_message')->insertGetId($data);
        if ($post['send_type']=='1') {
            if ($post['send_user']=='1') {
                $select_driver = DB::table('ct_driver')->field('mobile')->where('delstate','1')->select();
                $select_user = DB::table('ct_user')->field('phone')->where('delstate','1')->select();
                //echo "<pre/>";
                $merge = array_merge($select_driver,$select_user);
                $unique = $this->more_array_unique($merge);
                $array = $this->multiToSingle($unique);
                $phone = implode(',', $array);

            }elseif($post['send_user']=='2'){
                $select_driver = DB::table('ct_driver')->field('mobile')->where('delstate','1')->select();
                $unique = $this->more_array_unique($select_driver);
                $array = $this->multiToSingle($unique);
                $phone = implode(',', $array);
                //print_r($phone);
            }elseif($post['send_user']=='3'){
                $select_user = DB::table('ct_user')->field('phone')->where('delstate','1')->select();
                $unique = $this->more_array_unique($select_user);
                $array = $this->multiToSingle($unique);
                $phone = implode(',', $array);
            }
        }else{
            $phone=trim($post['user_defined']);
        }

        $content=$post['send_message'];

        //var_dump($post);
        //exit();
        if ($inser) {
            $res = send_sms_class($phone,$content);
            if ($res['status'] == '1') {
                $get_str = "成功";
            }else{
                $get_str = "失败";
            }
            $phone_arr = explode(',', $phone);
            foreach ($phone_arr as $key => $value) {
                $phone_data['send_phone'] = $value;
                $phone_data['greet_id'] = $inser;
                $phone_data['send_state'] = $get_str;
                $phone_data['addtime'] = time();
                $phone_inser = DB::table('ct_senduser_record')->insert($phone_data);
            }
            $content = "发送了:".$content."短信信息";
            $this->hanldlog($this->uid,$content);
            if ($res['status'] == '1') {
               $this->success("发送成功",'setting/messindex');
            }else{
                $this->error('发送失败');
                exit();
            }

        }else{
             $this->error('添加失败');
        }
    }

    public function del(){
        $get_data = Request::instance()->get();

        if ($get_data['del'] =='1') {
             DB::table('ct_senduser_record')->where('greet_id',$get_data['id'])->delete();
            $del = DB::table('ct_greet_message')->delete($id);
            if ($del) {
                $content = "删除了ID:".$get_data['id']."短信信息";
                $this->hanldlog($this->uid,$content);
                $this->success('删除成功!!','setting/messindex');
            }else{
                $this->error('删除失败!!');
            }
        }elseif ($get_data['del'] =='2'){
            $del = DB::table('ct_senduser_record')->delete($get_data['id']);
            if ($del) {
                 $content = "删除了ID:".$get_data['id']."短信队列信息";
                $this->hanldlog($this->uid,$content);
                $this->success('删除成功!!','setting/messindex');
            }else{
                $this->error('删除失败!!');
            }
        }elseif ($get_data['del'] =='3'){
           $content = "删除了ID:".$get_data['id']."短信队列信息";
            $this->hanldlog($this->uid,$content);
            $del= DB::table('ct_senduser_record')->where(array('id'=>array('in',$get_data['id'])))->delete();
            if ($del) {
                print_r('ok');
            }else{
                 print_r('fail');
            }
        }


    }
    public function upmess(){
        $id = input('id');
        $find = DB::table('ct_greet_message')->where('g_id',$id)->find();
        $this->assign('list',$find);
        return view('setting/upmess');
    }

    public function uppostmess(){
        $post = Request::instance()->post();

        if (isset($post['user_defined']) && $post['send_type'] == 2) {
            $data['user_defined'] = $post['user_defined'];
            $data['send_user'] ='';
        }
        if (isset($post['send_user']) && $post['send_type'] == 1) {
            $data['send_user'] = $post['send_user'];
            $data['user_defined'] = '';
        }

        $data['send_message'] = $post['send_message'];
        $data['send_date'] = time();
        $data['mess_type'] = $post['mess_type'];
        $data['send_type'] = $post['send_type'];

        $up = DB::table('ct_greet_message')->where('g_id',$post['id'])->update($data);
        if ($post['send_type']=='1') {
            if ($post['send_user']=='1') {
                $select_driver = DB::table('ct_driver')->field('mobile')->where('delstate','1')->select();
                $select_user = DB::table('ct_user')->field('phone')->where('delstate','1')->select();
                //echo "<pre/>";
                $merge = array_merge($select_driver,$select_user);
                $unique = $this->more_array_unique($merge);
                $array = $this->multiToSingle($unique);
                $phone = implode(',', $array);

            }elseif($post['send_user']=='2'){
                $select_driver = DB::table('ct_driver')->field('mobile')->where('delstate','1')->select();
                $unique = $this->more_array_unique($select_driver);
                $array = $this->multiToSingle($unique);
                $phone = implode(',', $array);
                //print_r($phone);
            }elseif($post['send_user']=='3'){
                $select_user = DB::table('ct_user')->field('phone')->where('delstate','1')->select();
                $unique = $this->more_array_unique($select_user);
                $array = $this->multiToSingle($unique);
                $phone = implode(',', $array);
            }
        }else{
            $phone=trim($post['user_defined']);
        }

        $content=$post['send_message'];
        if ($up) {
            if (isset($post['send_yes']) == 'yes') {
                $res = send_sms_class($phone,$content);
                if ($res['status'] == '1') {
                    $get_str = "成功";
                }else{
                    $get_str = "失败";
                }
                $phone_arr = explode(',', $phone);
                foreach ($phone_arr as $key => $value) {
                    $phone_data['send_phone'] = $value;
                    $phone_data['greet_id'] = $post['id'];
                    $phone_data['send_state'] = $get_str;
                    $phone_data['addtime'] = time();
                    $phone_inser = DB::table('ct_senduser_record')->insert($phone_data);
                }
                 $content = "修改了ID:".$post['id']."短信信息";
                $this->hanldlog($this->uid,$content);
                if ($res['status'] == '1') {
                   $this->success("发送成功",'setting/messindex');
                }else{
                    $this->error('发送失败','setting/messindex');
                    exit();
                }
            }else{
                 $this->success("修改成功",'setting/messindex');
            }
        }else{
             $this->error('修改失败');
        }

    }


    /*
    *发送队列
    */
    public function sendlist(){
        $id = input('id');
        $find = DB::table("ct_senduser_record")
                    ->alias('a')
                    ->join('ct_greet_message b','b.g_id = a.greet_id')
                    ->where('greet_id',$id)
                    ->order('id','desc')
                    ->select();
        $this->assign('list',$find);
        return view('setting/sendlist');
    }

    public function send(){
        $id = input('id');
        $search = DB::table('ct_senduser_record')
                    ->alias('a')
                    ->join('ct_greet_message b','b.g_id=a.greet_id')
                    ->where('id',$id)
                    ->find();
        $data['send_phone'] = $search['send_phone'];
        $data['greet_id'] = $search['greet_id'];
        $data['addtime'] = time();
        $phone = $search['send_phone'];
        $content = $search['send_message'];
        $res = send_sms_class($phone,$content);
        if ($res['status'] == '1') {
            $get_str = "成功";

        }else{
            $get_str = "失败";
        }
         $data['send_state'] = $get_str;
            $phone_inser = DB::table('ct_senduser_record')->insert($data);
            $content = "发送了ID:".$content."短信信息";
            $this->hanldlog($this->uid,$content);
       if ($res['status'] == '1') {

           $this->success("发送成功",'setting/messindex');
        }else{
            $this->error('发送失败','setting/messindex');
            exit();
        }

    }
    /*
    *菜单管理
    */
    public function menuindex(){
         // 获取用户组数据
        $rule_data = $this->getTreeData('tree','id','title');
        $this->assign('rule_data',$rule_data);
        return view('setting/menuindex');
    }
    /*
    *添加菜单
    */
    public function addmenu(){
        $post_data = Request::instance()->post();
        $data['name'] = $post_data['name'];
        $data['ertype'] = $post_data['ertype'];
        $data['title'] = $post_data['title'];

        if ($post_data['action'] == 'add') {
            $data['pid'] = $post_data['pid'];
            $insert = Db::table('ct_auth_rule')->insert($data);
            if ($insert) {
                 $content = "新增了".$post_data['name']."菜单";
                 $this->hanldlog($this->uid,$content);
                $this->success('新增成功','setting/menuindex');
            }else{
                $this->error('新增失败!!');
            }
            exit();
        }

        if ($post_data['action'] == 'update') {
            $id = $post_data['id'];
            $update = Db::table('ct_auth_rule')->where('id',$id)->update($data);
            if ($update) {
                $content = "修改了".$post_data['name']."菜单";
                 $this->hanldlog($this->uid,$content);
                $this->success('修改成功','setting/menuindex');
            }else{
                $this->error('修改失败!!');
            }
            exit();
        }

    }

    /*
    *删除菜单
    */
    public function delmenu(){
        $id = input('id');
        $map = array('id'=>$id);
        $count= Db::table('ct_auth_rule')
            ->where(array('pid'=>$id))
            ->count();
        if($count!=0){
             $this->error('请先删除子权限');
        }
        $result=Db::table('ct_auth_rule')->where($map)->delete();
        if ($result) {
             $content = "删除了权限菜单";
            $this->hanldlog($this->uid,$content);
            $this->success('删除成功','setting/menuindex');
        }
    }


    /**
     * 用户组列表
     * @auther: 李渊
     * @date: 2018.8.21
     * 用户组代表享受统一的权限的一组成员
     * 例如 超级管理员组 只要平台用户加入这个组则享有这个组的所有权限
     * 权限只针对用户组设置
     * @return [type] [description]
     */
    public function groupindex(){
        // 查找后台管理用户组
        $result = Db::table('ct_auth_group')->where('utype','1')->select();
        // 返回数据
        var_dump($result);
        $this->assign('rule_data',$result);
        return view('setting/groupindex');
    }

    /**
     * 添加用户组
     * @auther: 李渊
     * @date: 2018.8.21
     * @param   [String]    title   [用户组名称]
     * @return  [type]  [description]
     */
    public function addGroup(){
        $post = Request::instance()->post();
        // 获取title
        $data['title'] = $post['title'];
        // 设置为后台
        $data['utype'] = 1;
        // 插入数据
        $result = Db::table('ct_auth_group')->insert($data);
        if ($result) {
            $content = "新增了".$post['title']."用户组";
            $this->hanldlog($this->uid,$content);
            return json(['code'=>true,'message'=>'添加成功']);
        }else{
            return json(['code'=>false,'message'=>'添加失败']);
        }
    }

    /**
     * 修改用户组
     * @auther：李渊
     * @date: 2018.8.21
     * @param   [Int]       id      [用户组索引id]
     * @param   [String]    title   [用户组名称]
     * @return [type] [description]
     */
    public function updateGroup()
    {
        $post = Request::instance()->post();
        // 获取title
        $data['title'] = $post['title'];
        // 获取索引id
        $id = $post['id'];
        // 更新数据
        $result = Db::table('ct_auth_group')->where('id',$id)->update($data);
        if ($result) {
            $content = "新增了".$post['title']."用户组";
            $this->hanldlog($this->uid,$content);
            return json(['code'=>true,'message'=>'修改成功']);
        }else{
            return json(['code'=>true,'message'=>'修改失败']);
        }
    }

    /**
     * 删除用户组
     * @auther：李渊
     * @date: 2018.8.21
     * @param   [Int]       id      [用户组索引id]
     * @return [type] [description]
     */
    public function delgroud(){
        // 获取索引id
        $id = input('id');
        // 删除数据
        $result = Db::table('ct_auth_group')->where('id',$id)->delete();
        if ($result) {
            Db::table('ct_auth_group_access')->where('group_id',$id)->delete();
            $content = "删除了ID为".$id."用户组";
            $this->hanldlog($this->uid,$content);
            return json(['code'=>true,'message'=>'删除成功']);
        }else{
           return json(['code'=>false,'message'=>'删除失败']);
        }
    }

    /*
    *用户组权限列表
    */
    public function rulegroup(){
        $gourpid = input('id');
        $group_data = Db::table('ct_auth_group')->where('id',$gourpid)->find();

        $group_data['rules'] = explode(',', $group_data['rules']);
        $rule_data  = $this->getTreeData('level','id','title');
        // echo "<pre/>";
        //print_r( $rule_data);
       // exit();
        $assign=array(
                'group_data'=>$group_data,
                'rule_data'=>$rule_data
                );
        echo '<pre/>';
        print_r($assign);
//        var_dump($assign);
        $this->assign($assign);
        return view('setting/rulegroup');

    }
    /*
    *添加用户权限
    */
    public function post_rule_group(){
        $post_data = Request::instance()->post();
        $id = $post_data['id'];
        $data['rules'] = implode(',', $post_data['rule_ids']);
        $result = Db::table('ct_auth_group')->where('id',$id)->update($data);
        if ($result) {
            $this->success('操作成功','setting/groupindex');
        }else{
            $this->error('操作失败');
        }
    }
    /*
    *
    *添加成员成为用户组列表
    */
    public function checkuser(){
        $gourpid = input("group_id");
        $name = input("username");
        $where ='';
        if ($name !='') {
            $where['username|realname'] =  $name;
        }
        $group_name = Db::table('ct_auth_group')->where('id',$gourpid)->find();
        $uids =Db::table('ct_auth_group_access')->field('uid')->where(array('group_id'=>$gourpid))->select();
        echo "<pre/>";
        print_r($uids);
        print_r($this->multiToSingle($uids));exit();
        $user_data = Db::table('ct_admin')->where($where)->select();
         $assign=array(
            'group_name'=>$group_name['title'],
            'uids'=>$this->multiToSingle($uids),
            'user_data'=>$user_data
            );
         echo '<pre/>';
         print_r($assign);
         exit();
        $this->assign($assign);
        return view('setting/checkuser');
    }
    /*
    *分配用户管理组
    */
    public function add_user_to_group(){
        $get_data = Request()->instance()->get();
        $map=array(
            'uid'=>$get_data['uid'],
            'group_id'=>$get_data['group_id'],
            'ustype'=>1
            );
        $count = Db::table('ct_auth_group_access')->where($map)->count();
        if($count==0){
            DB::table('ct_auth_group_access')->insert($map);
        }
         $this->success('操作成功',"setting/checkuser?group_id=".$get_data['group_id']);

    }

    /*
    *
    *城配和城配费用区间页面
    */
    public function setting_freight(){
        $result = Db::table('ct_city_section')
                    ->field('otype')
                    ->group('otype')
                    ->having('count(otype)>1')
                    ->select();
        $arr = array();
        foreach ($result as $key => $value) {
            if($value['otype'] != 6){
                $arr[$key]['otypenumber'] =$value['otype'];
                $arr[$key]['otype'] =$this->get_otype_str($value['otype']);

                $arr[$key]['rang'] = Db::table('ct_city_section')->where('otype',$value['otype'])->select();
            }
        }

        $this->assign('list',$arr);
        return view('setting/freightindex');
    }

    /*
    *
    *添加费用区间页面
    */
    public function freight(){
        return view('setting/freight');
    }
    /*
    *
    *修改费用区间页面
    */
    public function up_freight(){
        $otype = input('otype');
        $result = Db::table('ct_city_section')
                    ->where('otype',$otype)
                    ->find();
        $arr = array();
        $arr['otypenumber'] =$otype;
        $arr['setion'] = Db::table('ct_city_section')->where('otype',$otype)->select();

        $this->assign('list',$arr);
        return view('setting/edit_freight');
    }
    /*
    *
    *添加费用区间提交动作
    */
    public function  post_freight(){
        $post_data = Request::instance()->post();

      $array = array();
      $i=0;
      $arr_setion1 = $post_data['mytext1'];     //重量1
      $arr_setion2 = $post_data['mytext2'];     //重量1
      $arr_setion4 = $post_data['mytext4'];     //百分比
      foreach ($arr_setion1 as $key => $value) {
        $array[$i][]= $arr_setion1[$key];
        $array[$i][]= $arr_setion2[$key];
        $array[$i][]= $arr_setion4[$key];
        $i++;
      }// end foreach
      //echo "<pre/>";
      //print_r($array);
      foreach ($array as $key => $value) {
        # code...
        $setion_date['weight_start'] = $value['0'];
        $setion_date['weight_end'] = $value['1'];
        $setion_date['billing'] = $value['2'];
        $setion_date['otype'] = $post_data['otype'];
       $insertID =  DB::table('ct_city_section')->insert($setion_date);
      }

      if ($insertID) {
        $content = "添加了".$this->get_otype_str($post_data['otype'])."运费区间信息";
        $this->hanldlog($this->uid,$content);
        $this->success("添加成功！！","setting/setting_freight");
      }else{
        $this->error("添加失败!!");
      }
    }

    public function eidt_freight(){
        $postdate = Request::instance()->post();
        $otype = $postdate['otype'];
        $array = array();
        $arr_setionid = $postdate['cid'];
        $arr_setion1 = $postdate['mytext1'];
        $arr_setion2 = $postdate['mytext2'];
        $arr_setion4 = $postdate['mytext4'];
        $i=0;
        foreach ($arr_setion1 as $key => $value) {
            $array[$i][]= $arr_setionid[$key];
            $array[$i][]= $arr_setion1[$key];
            $array[$i][]= $arr_setion2[$key];

            $array[$i][]= $arr_setion4[$key];
            $i++;
        }
        foreach ($array as $key => $value) {
            $setion_date['weight_start'] = $value['1'];
            $setion_date['weight_end'] = $value['2'];

            $setion_date['billing'] = $value['3'];
            if(empty($value['0'])){
                $setion_date['otype'] = $otype;
                $setion_insert =  Db::table('ct_city_section')->insert($setion_date);
            }else{
                $setion_date['otype'] = $otype;
                $setion_update = Db::table('ct_city_section')->where('s_id',$value['0'])->update($setion_date);
                $setion_update = true;
            }
        }

        if ( $setion_insert || $setion_update) {

           $content = "修改了".$this->get_otype_str($otype)."运费区间信息";
            $this->hanldlog($this->uid,$content);
           $this->success("修改成功！！","setting/setting_freight");
        }else{
          $this->error("修改失败!!");
        }
    }

    /*
    *删除运费区间炒作
    */
    public function del_freight(){
        $otype = input('otype');
        $result = Db::table('ct_city_section')->where('otype',$otype)->delete();
        if ($result) {
            $content = "删除了".$this->get_otype_str($otype)."运费区间信息";
            $this->hanldlog($this->uid,$content);
            $this->success("删除成功！！","setting/setting_freight");
        }else{
            $this->error("删除失败!!");
        }

    }

    /*
    *返回订单类型字符串
    *@param  int $otype 1城配 2整车 3提货 4干线 5配送
    */
    public function get_otype_str($otype){
        switch ($otype) {
                case '1':
                  $str = '城配';
                  break;
                case '2':
                  $str = '整车';
                  break;
                case '3':
                  $str = '提货';
                  break;
                case '4':
                  $str = '干线';
                  break;
                case '5':
                  $str = '配送';
                  break;

                default:
                  $str = '城配';
                  break;
          }
        return $str;
    }


    public function  information(){
        $res = Db::table('ct_setting_price')
            ->field('deposit,charge,cancleprice,viewprice,vipprice,vipcount,yearly')
            ->where('type',1)
            ->find();
        $data = Db::table('ct_setting_price')->field('deposit,charge,cancleprice,viewprice,vipprice,vipcount,yearly')->where('type',2)->find();
        $this->assign('data',$data);
        $this->assign('res',$res);
        return $this->fetch();
    }
    public function updatePrice(){
        // 获取修改类型
        $type = input('type');
        // 获取修改的数据类型 1单条信息查看费用 2充值金额 3充值金额查看数量 4包年充值金额 5客户保证金门槛 6发布单条信息费用 7 违规取消订单扣费
        $scaleType = input('scaleType');
        // 获取修改的数据值
        $updateVal = input('updateVal');
        if($type == 1){
           $where['type'] =1; //整车
        }else{
            $where['type'] = 2; //城配
        }
        switch ($scaleType){
            case '1':
                $data['viewprice'] = $updateVal;
                break;
            case '2':
                $data['vipprice'] = $updateVal;
                break;
            case '3':
                $data['vipcount'] = $updateVal;
                break;
            case '4':
                $data['yearly'] = $updateVal;
                break;
            case '5':
                $data['deposit'] = $updateVal;
                break;
            case '6':
                $data['charge'] = $updateVal;
                break;
            case '7':
                $data['cancleprice'] = $updateVal;
                break;
            default:
                #code...
                break;

        }
        // 查询城配费用系数标准
        $result = Db::table('ct_setting_price')->where($where)->find();
        // 判断是否有数据 没有则重新插入 有则更新
        if(!$result['type']){
            $data['type'] = $type;
            $res = Db::table('ct_setting_price')->insertGetId($data);
        }else{
            $res = Db::table('ct_setting_price')->where($where)->update($data);
        }
        // 判断是否修改成功
        if($res){
            return true;
        }else{
            return false;
        }

    }

    /*
     * 市内开通城市设置
     * */
    public function city(){
        $list = Db::table('ct_city_cost')
            ->alias('a')
            ->join('ct_district b','a.c_city=b.id')
            ->field('a.*,b.name')
            ->where('delstate',1)
            ->paginate();
        $this->assign('list',$list);
        $page = $list->render();
        $this->assign('page',$page);
        return $this->fetch();
}
    /*
     * 添加城市
     * */
    public function addcity(){
        return $this->fetch();
    }

    public function toaddcity(){
        $postdate = Request::instance()->post();

        $data['c_city'] = $postdate['tcity'];
        $data['scale_price'] = $postdate['scale_price'];
        $data['scale_hour'] = $postdate['scale_hour'];
        $data['start_fare'] = $postdate['start_fare'];
        $data['scale_klio'] = $postdate['scale_klio'];
        $data['addtime'] = date('Y-m-d h:i:s',time());
        $cityid=  Db::table('ct_city_cost')->insertGetId($data);
        $arr = Db::table('ct_city_cost')
            ->alias('a')
            ->join('ct_district b','a.c_city = b.id')
            ->field('a.c_city,b.name')
            ->where('cost_id',$cityid)
            ->find();
        if($cityid){
            $content = "新添加了市内城市 ".$arr['name'];
            $this->hanldlog($this->uid,$content);
            $this->success('新增成功', 'setting/city');
        }else{
            $this->error('新增失败');
        }
    }

    /*
     * 修改开通城市
     * */
    public function updatecity($id){
        $res =  Db::table('ct_city_cost')
            ->alias('a')
            ->join('ct_district b','a.c_city=b.id')
            ->field('a.*,b.name')
            ->where('cost_id',$id)
            ->find();
        $this->assign('res',$res);
        return $this->fetch();
    }
    public function savecity(){
        // 获取修改类型
        $type = input('type');
        // 获取修改的数据类型 1
        $scaleType = input('scaleType');
        // 获取修改的数据值
        $updateVal = input('updateVal');
            $where['cost_id'] =$type; //整车

        switch ($scaleType){
            case '2':
                $data['scale_hour'] = $updateVal;
                break;
            case '3':
                $data['start_fare'] = $updateVal;
                break;
            case '4':
                $data['scale_price'] = $updateVal;
                break;
            case '5':
                $data['scale_klio'] = $updateVal;
                break;
            default:
                #code...
                break;

        }
        // 查询城配费用系数标准
        $result = Db::table('ct_city_cost')->where($where)->find();
        // 判断是否有数据 没有则重新插入 有则更新
        if(!$result['cost_id']){
            $data['cost_id'] = $type;
            $res = Db::table('ct_city_cost')->insertGetId($data);
        }else{
            $res = Db::table('ct_city_cost')->where($where)->update($data);
        }
        // 判断是否修改成功
        if($res){
            return true;
        }else{
            return false;
        }
    }
    /*
     * 开通城市详情
     * */
    public function todetail($id){
       $res =  Db::table('ct_city_cost')
            ->alias('a')
            ->join('ct_district b','a.c_city=b.id')
            ->field('a.*,b.name')
            ->where('cost_id',$id)
            ->find();
        $this->assign('list',$res);
        return $this->fetch();
    }

    /*
     * 删除已开通城市
     * */
    public function delcom(){
        $get = Request::instance()->get();
        $arr['delstate'] = $get['del'];
        $delcom = Db::table('ct_city_cost')
            ->where('cost_id',$get['id'])
            ->update($arr);

            if($delcom){
                $content = "删除了已开通的市内城市";
                $this->hanldlog($this->uid,$content);
                $this->success('删除成功', 'setting/city');
            }else{
                $this->error('删除失败');
            }
    }
    /*
     *城市配送分类
     * */
    public function delivery(){
       $list = Db::table('ct_delivery_type')->paginate();
       $page = $list->render();
       $this->assign('page',$page);
       $this->assign('list',$list);
        return $this->fetch();
    }
    /*
     * 添加城市配送分类
     * */
    public function addtype(){
       return $this->fetch();
    }
    public function savetype(){
        $get = Request::instance()->post();
        $data['name'] = $get['name'];
        $data['status'] = $get['status'];
        $data['addtime'] = time();
        $list = Db::table('ct_delivery_type')->insert($data);
        if ($list){
            $this->success('添加成功','setting/delivery');
        }else{
            $this->error('添加失败');
        }
    }
    /*
     * 修改市内配送分类
     * */
    public function updatedelivery(){
        $get = Request::instance()->get();
        $list = Db::table('ct_delivery_type')->where('cid',$get['id'])->field('name,cid,status')->find();
        $this->assign('list',$list);
        return $this->fetch();
    }

    public function savedelivery(){
        $post = Request::instance()->post();
        $data['name'] = $post['name'];
        $data['status'] = $post ['status'];
       $res = Db::table('ct_delivery_type')->where('cid',$post['cid'])->where($data);
        if ($res){
            $this->success('修改成功','setting/delivery');
        }else{
            $this->error('添加失败');
        }

    }

    /*
     * 删除分类
     * */
    public function deldelivery(){
        $get = Request::instance()->get();
        $id = $get['id'];
        $data['status'] = 2;
        $res =  Db::table('ct_delivery_type')->where('cid',$id)->update($data);
       if ($res){
            $this->success('删除成功','setting/delivery');
       }else{
           $this->error();
       }
    }
       /*
        *城市配送相关参数设置
        * */
    public function delivery_price(){
        $list = Db::table('ct_city_delivery')
            ->alias('a')
            ->join('ct_delivery_type b','a.cid = b.cid')
            ->field('a.*,b.name')
            ->paginate();
        $this->assign('list',$list);
        $page = $list->render();
        $this->assign('page',$page);
        return $this->fetch();
    }
    /*
     * 添加分类参数
     * */
     public function addprice(){
         $arr = Db::table('ct_delivery_type')->field('cid,name')->select();
         $this->assign('arr',$arr);
        return $this->fetch();
     }

     public function toaddprice(){
         $post = Request::instance()->post();
         $data['cid'] = $post['cid'];
         $data['delivery_time'] = $post['delivery_time'];
         $data['delivery_num']  =  $post['delivery_num'];
         $data['delivery_low']  =  $post['delivery_low'];
         $data['delivery_inner']  = $post['delivery_inner'];
         $data['delivery_outer'] = $post['delivery_outer'];

         $res = Db::table('ct_city_delivery')->insert($data);
         if ($res){
             $this->success('添加成功','setting/delivery_price');
         }else{
             $this->error('添加失败');
         }

     }

     /*
      * 修改市内配送参数
      * */
     public function saveprice(){
        $get = Request::instance()->get();
        $res = Db::table('ct_city_delivery')
            ->alias('a')
            ->join('ct_delivery_type b','a.cid=b.cid')
            ->where('id',$get['id'])
            ->field('a.*,b.name')
            ->find();
        $this->assign('res',$res);
       return $this->fetch();

     }

     public function tosaveprice(){
         // 获取修改类型
         $type = input('type');
         // 获取修改的数据类型 1单条信息查看费用 2充值金额 3充值金额查看数量 4包年充值金额 5客户保证金门槛 6发布单条信息费用 7 违规取消订单扣费
         $scaleType = input('scaleType');
         // 获取修改的数据值
         $updateVal = input('updateVal');
         $where['id'] =$type; //整车

         switch ($scaleType){
             case '2':
                 $data['delivery_time'] = $updateVal;
                 break;
             case '3':
                 $data['delivery_num'] = $updateVal;
                 break;
             case '4':
                 $data['delivery_low'] = $updateVal;
                 break;
             case '5':
                 $data['delivery_inner'] = $updateVal;
                 break;
             case '6':
                 $data['delivery_outer'] = $updateVal;
                 break;
             default:
                 #code...
                 break;

         }
         // 查询城配费用系数标准
         $result = Db::table('ct_city_delivery')->where($where)->find();
         // 判断是否有数据 没有则重新插入 有则更新
         if(!$result['id']){
             $data['id'] = $type;
             $res = Db::table('ct_city_delivery')->insertGetId($data);
         }else{
             $res = Db::table('ct_city_delivery')->where($where)->update($data);
         }
         // 判断是否修改成功
         if($res){
             return true;
         }else{
             return false;
         }
     }
     /*
      * 删除市内配送参数
      * */
     public function delprice(){
        $get = Request::instance()->get();
        $res = Db::table('ct_city_delivery')->where('id',$get['id'])->delete();
        if ($res){
            $this->success('删除成功','setting/delivery_price');
        }else{
            $this->error('删除失败');
        }
     }
}
