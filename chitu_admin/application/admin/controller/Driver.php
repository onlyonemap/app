<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Driver  extends Base
{
	function __construct(){
        parent::__construct();
        $this->uid = Session::get('admin_id','admin_mes');
        $this->if_login();

    }

    /**
     * 承运列表
     * @Auther: 李渊
     * @Date: 2018.7.2
     * @param  [type] $search       [搜索字段] 手机号码 姓名
     * @return [type] [description]
     */
    public function index(){
        $search = input("search");
        $pageParam    = ['query' =>[]];
        if (!empty($search)) {
            $where_data['a.realname|b.name|a.mobile'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search; 
        }
        // 承运人状态 1 正常  2 删除
        $where_data['delstate'] = 1;
        $array = array();
        // 查询承运列表
    	$select  = DB::field('a.*,b.name')
                    ->table('ct_driver')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.companyid','LEFT')
                    ->where($where_data)
                    ->order('a.drivid','desc')
                    ->paginate(10,false, $pageParam);
        $result = $select->toArray();
        foreach ($result['data'] as $key => $value) {
            $share_name = '平台';
            $array[$key] = $value;
            if ($value['shareid'] !='') {
                $share_mess = Db::table('ct_driver')->field('realname,username')->where('drivid',$value['shareid'])->find();
                $share_name = $share_mess['realname']=='' ? $share_mess['username'] : $share_mess['realname'];
            }
            $array[$key]['share_name'] = $share_name;
        }
        $page = $select->render();
    	$this->assign('list',$array);
        $this->assign('page',$page);
    	return view('driver/index');
    }

    /**
     * 承运详情
     * @Auther: 李渊
     * @Date: 2018.7.2
     * @param  [type] $id  [索引id]
     * @return [type] [description]
     */
    public function details(){
        $id = input('id');
        // 承运信息
        $result =  DB::field('a.*,b.name')
                    ->table('ct_driver')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.companyid','LEFT')
                    ->where('drivid',$id)
                    ->find();
        // 承运车辆信息
        $cararr = DB::field('a.*,b.dimensions,b.allvolume,b.allweight,b.avatar')
                    ->table('ct_carcategory')
                    ->alias('a')
                    ->join('ct_cartype b','b.car_id = a.carid')
                    ->where('driverid',$result['drivid'])
                    ->select();
        if (!empty($cararr)) {
            $result['car'] = $cararr;
        }
        $this->assign('vo',$result);
        return view('driver/details');
    }

    /**
     * to 添加承运信息页面
     * @Auther: 李渊
     * @Date: 2018.7.2
     * @return [type] [description]
     */
    public function adddriver(){
        return view('driver/adddriver');
    }
    
    /**
     * 添加承运信息操作
     * @Auther: 李渊
     * @Date: 2018.7.2
     * @return [type] [description]
     */
    public function postdriver(){
        $post = Request::instance()->post();
        // 真实姓名
        $data['realname'] = $post['realname'];
        // 用户昵称
        $data['username'] = $post['username'];
        // 手机号
        $data['mobile'] = $post['mobile'];
        // 性别
        $data['sex'] = $post['sex'];
        // 身份证
        $data['identity'] = $post['identity'];
        // 余额
        $data['balance'] = $post['balance'];
        
        // 驾驶证认证通过
        $data['carstatus'] = $post['carstatus'];
        // 初始密码666666
        $data['password'] = md5('666666ct888');
        // 添加时间
        $data['addtime'] = time();

        switch ($post['state']) { // 0个体1司机2调度3管理
            case '0': // 个体 
                $data['type'] = 1; // 用户类型：1司机 2调度 3管理
                $data['driver_grade'] = 1; // 1个体2公司司机3调度4管理
                //$data['companyid'] = '';
                break;
            case '1': // 司机
                if($post['companyid']){ // 如果添加了公司
                    $com = DB::table('ct_company')->where('cid',$post['companyid'])->find();
                }else{
                    $this->error("请选择已经注册的公司");
                }
                // 公司id 个体用户为空
                $data['companyid'] = $post['companyid'];
                $data['type'] = 1; // 用户类型：1司机 2调度 3管理
                $data['driver_grade'] = 2; // 1个体2公司司机3调度4管理
                break;
            case '2': // 调度
                if($post['companyid']){ // 如果添加了公司
                    $com = DB::table('ct_company')->where('cid',$post['companyid'])->find();
                }else{
                    $this->error("请选择已经注册的公司");
                }
                // 公司id 个体用户为空
                $data['companyid'] = $post['companyid'];
                $data['type'] = 2; // 用户类型：1司机 2调度 3管理
                $data['driver_grade'] = 3; // 1个体2公司司机3调度4管理
                break;
            case '3': // 管理
                if($post['companyid']){ // 如果添加了公司
                    $com = DB::table('ct_company')->where('cid',$post['companyid'])->find();
                }else{
                    $this->error("请选择已经注册的公司");
                }
                $com = DB::table('ct_driver')->where(array('companyid' => $post['companyid'], 'delstate' =>1,'type' =>3,'driver_grade'=>4))->find();
                if($com){
                    $this->error("该公司已经有管理员了");
                }
                // 公司id 个体用户为空
                $data['companyid'] = $post['companyid'];
                $data['type'] = 3; // 用户类型：1司机 2调度 3管理
                $data['driver_grade'] = 4; // 1个体2公司司机3调度4管理
                break;
            default:
                # code...
                break;
        }
        // 驾驶证
        if (request()->file('drivingimage') != '') {
            $driving_path= $this->file_upload('drivingimage','jpg,gif,png,jpeg','driver');
            $data['drivingimage'] = $driving_path['file_path'];
        }
        // 插入
        $driver = DB::table('ct_driver')->insertGetId($data);

        if ($driver) {
            $content = "添加了 ".$post['realname']."的司机信息";
            $this->hanldlog($this->uid,$content);
            $this->success('添加成功','driver/index');
        }else{
            $this->error('添加失败！！！');
        }
    }
    // to 修改承运信息页面
    public function updatedriver(){
        $get_data =input('id'); 
        $result =  DB::field('a.*,b.name,b.cid')
                    ->table('ct_driver')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.companyid','LEFT')
                    ->where('drivid',$get_data)
                    ->find();
        $this->assign('list',$result);
        return view('driver/updatedriver');
    }
    // 修改承运信息操作
    public function updatemessage(){
        $post = Request::instance()->post();
        // 真实姓名
        $data['realname'] = $post['realname'];
        // 用户昵称
        $data['username'] = $post['username'];
        // 手机号
        $data['mobile'] = $post['mobile'];
        // 性别
        $data['sex'] = $post['sex'];
        // 身份证
        $data['identity'] = $post['identity'];
        // 余额
        $data['balance'] = $post['balance'];
        // 公司id
       
        // 驾驶证认证通过
        $data['carstatus'] = $post['carstatus'];
        // 密码
        if ($post['password'] !='') {
            $data['password'] = md5($post['password'].'ct888');
        }

        switch ($post['state']) { // 0个体1司机2调度3管理
            case '0': // 个体 
                $data['type'] = 1; // 用户类型：1司机 2调度 3管理
                $data['driver_grade'] = 1; // 1个体2公司司机3调度4管理
                break;
            case '1': // 司机
                if($post['companyid']){ // 如果添加了公司
                    $com = DB::table('ct_company')->where('cid',$post['companyid'])->find();
                }else{
                    $this->error("请选择已经注册的公司");
                }
                 // 公司id 个体用户为空
                $data['companyid'] = $post['companyid'];
                $data['type'] = 1; // 用户类型：1司机 2调度 3管理
                $data['driver_grade'] = 2; // 1个体2公司司机3调度4管理
                break;
            case '2': // 调度
                if($post['companyid']){ // 如果添加了公司
                    $com = DB::table('ct_company')->where('cid',$post['companyid'])->find();
                }else{
                    $this->error("请选择已经注册的公司");
                }
                 // 公司id 个体用户为空
                $data['companyid'] = $post['companyid'];
                $data['type'] = 2; // 用户类型：1司机 2调度 3管理
                $data['driver_grade'] = 3; // 1个体2公司司机3调度4管理
                break;
            case '3': // 管理
                if($post['companyid']){ // 如果添加了公司
                    $com = DB::table('ct_company')->where('cid',$post['companyid'])->find();
                }else{
                    $this->error("请选择已经注册的公司");
                }

                $driverboss =  DB::table('ct_driver')->where(array('companyid' => $post['companyid'], 'delstate' => 1,'type'=>3,'driver_grade'=>4))->find();
                if($driverboss && $post['drivid'] != $driverboss['drivid']){
                    $this->error("该公司已经有管理员了");
                }
                 // 公司id 个体用户为空
                $data['companyid'] = $post['companyid'];
                $data['type'] = 3; // 用户类型：1司机 2调度 3管理
                $data['driver_grade'] = 4; // 1个体2公司司机3调度4管理
                break;
            default:
                # code...
                break;
        }  
        // 驾驶证
        if (request()->file('drivingimage') != '') {
            $driving_path= $this->file_upload('drivingimage','jpg,gif,png,jpeg','driver');
            $data['drivingimage'] = $driving_path['file_path'];
        }
        // 更新
        $update = DB::table('ct_driver')->where('drivid', $post['drivid'])->update($data);

        if (isset($update)) {
            $this->success('修改成功', 'driver/index');
        }else{
            $this->error('修改失败');
        }
        
    }

    /**
     * 删除承运人操作
     * @Auther: 李渊
     * @Date: 2018.8.4
     * @param  string $id   [司机id]
     * @return [type]       [description]
     */
    public function delete() {
        // 获取承运id
        $id = input('id');
        // 删除司机下面的车辆
        $car = Db::table('ct_carcategory')->where('driverid',$id)->delete();
        // 更改司机状态为删除
        $driver = DB::table('ct_driver')->where('drivid',$id)->update(array('delstate'=>2));
        // 删除司机对应的车辆
        $deleteDriver = Db::table('ct_carcategory')->where('driverid',$id)->delete();
        // 判断成功还是失败
        if($driver){
            $content = "删除了ID为".$id."司机信息";
            $this->hanldlog($this->uid,$content);
            $this->success('删除成功', 'driver/index');
        }else{
            $this->error('删除失败');
        }
    }


    // 删除、恢复、验证司机信息操作 
    public function delcom(){
        $get = Request::instance()->get();
        if($get['del'] == 1){ // 删除 删除司机同时要删除下面的车辆
            $find = Db::table('ct_carcategory')->where('driverid',$get['id'])->find();
            if (!empty($find)) {
                Db::table('ct_carcategory')->delete($find['ccid']);
            }
            $delcom = DB::table('ct_driver')->where('drivid',$get['id'])->update(array('delstate'=>2));
           
            if($delcom){
                $content = "删除了ID为".$get['id']."司机信息";
                $this->hanldlog($this->uid,$content);
                $this->success('删除成功', 'driver/index');
            }else{
                $this->error('删除失败');
            }
        }elseif($get['del'] == 2){ // 恢复
            $delcom = DB::table('ct_driver')->where('drivid',$get['id'])->update(array('delstate'=>1));
            if($delcom){
                $content = "恢复了ID为".$get['id']."司机信息";
                $this->hanldlog($this->uid,$content);
                $this->success('恢复成功', 'driver/index');
            }else{
                $this->error('恢复失败');
            }
        }elseif($get['del'] == 3){ // 认证驾驶证
            $delcom = DB::table('ct_driver')->where('drivid',$get['id'])->update(array('carstatus'=>2));
            if($delcom){
                $content = "通过了ID为".$get['id']."司机驾驶证信息";
                $this->hanldlog($this->uid,$content);
                $this->success('验证成功', 'driver/index');
            }else{
                $this->error('验证失败');
            }
        }elseif($get['del'] == 4){ // 认证失败
            $delcom = DB::table('ct_driver')->where('drivid',$get['id'])->update(array('carstatus'=>3));
            if($delcom){
                $driver_content ="尊敬的用户：您的信息未通过平台认证，请您重新上传信息，感谢您的配合!!";
                send_sms_class($mobile,$driver_content);
                $content = "ID为".$get['id']."司机驾驶证信息,未通过认证";
                $this->hanldlog($this->uid,$content);
                $this->success('操作成功', 'driver/index');
            }else{
                $this->error('操作失败');
            }
        }
    }
   
    /**
     * 车辆列表
     * @Auther: 李渊
     * @Date: 2018.8.4
     * @param  [type] $search   [搜索字段、车牌号、个体司机姓名、电话、用户名、公司名称]
     * @return [type]           [description]
     */
    public function carindex() {
        $where_data = '';
        // 搜索字段 车牌号
        $search = input("search");
        // 页码
        $pageParam    = ['query' =>[]];
        // 判断搜索字段
        if (!empty($search)) {
            $where_data['a.carnumber|d.mobile|com.name|d.realname|d.username'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search;
        }
        // 查询结果
        $result = DB::field('a.*,car.carparame,d.realname,d.username,d.mobile,com.name')
            ->table('ct_carcategory')
            ->alias('a')
            ->join('ct_cartype car','a.carid = car.car_id') // 关联车型
            ->join('ct_driver d','a.driverid = d.drivid','left') // 关联车辆联系人
            ->join('ct_company com','a.com_id = com.cid','left') // 关联公司
            ->where($where_data) 
            ->order('a.ccid','desc')
            ->paginate(10,false, $pageParam);
        // 转义 查询每个车辆对应的个体司机或者公司
        $resultarray = $result->toArray();
        // 定义输出数组
        $newArr  = array(); 
        // 遍历车辆拥有着信息
        foreach ($resultarray['data'] as $key => $value) {
            // 判断每个车辆对应的个体司机或者公司
            if($value['com_id'] == ''){ // 车辆归属与个体司机
                // 查询司机名称
                $value['name'] = $value['realname'] ? $value['realname'] : $value['username'];
                // 查询电话
                $value['phone'] = $value['mobile'];
                // 定义车辆归属人 1 个体 2 公司
                $value['car_grade'] = 1;
            }else{ // 车辆归属于公司
                // 查询该公司管理人信息
                $driver = DB::table('ct_driver')->where(array('companyid' => $value['com_id'],'type' => 3, 'delstate' => 1))->find();
                // 返回公司名称
                $value['name'] = $value['name'];
                // 返回公司管理电话
                $value['phone'] = $driver['mobile'];
                // 定义车辆归属人 1 个体 2 公司
                $value['car_grade'] = 2;
            }
            $newArr[$key] = $value;
        }

        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$newArr);
        return view('driver/carindex');
    }

    /**
     * 车辆信息详情
     * @Auther: 李渊
     * @Date: 2018.8.4
     * @param  [type] $id   [车辆id]
     * @return [type]       [description]
     */
    public function cardetails(){
        // 获取车辆id
        $id = input('id');
        // 查询条件
        $where['ccid'] = $id;
        // 查询车辆数据
        $result = DB::field('a.*,b.driver_grade,b.realname,b.username,b.mobile,b.type,d.name comname,c.name,car.*,e.mobile as companyphone')
                    ->table('ct_carcategory')
                    ->alias('a')
                    ->join('ct_driver b','b.drivid = a.driverid','LEFT')
                    ->join('ct_company c','c.cid = b.companyid','LEFT')
                    ->join('ct_company d','d.cid = a.com_id','LEFT')
                    ->join('ct_cartype car','car.car_id = a.carid')
                    ->join('ct_driver e','e.companyid = a.com_id','LEFT')
                    ->where($where)
                    ->find();
        // 查询车型信息
        $carType = DB::table('ct_cartype')->where('car_id',$result['carid'])->find();
        // 判断拥有着并查询拥有着信息
        if($result['com_id']){ // 属于公司
            // 查询公司信息
            $company = DB::table('ct_company')->where('cid',$result['com_id'])->find();
            // 查询公司管理着信息
            $driver = DB::table('ct_driver')->where(array('companyid' => $result['com_id'],'type' => 3, 'delstate' => 1))->find();
            // 返回拥有着名称
            $result['name'] = $company['name'];
            // 返回拥有着联系方式
            $result['phone'] = $driver['mobile'];
            // 返回车辆归属
            $result['driver_grade'] = '个体';
        }else{ // 属于个体
            // 查询拥有着信息
            $driver = DB::table('ct_driver')->where('drivid',$result['driverid'])->find();
            // 返回拥有着名称
            $result['name'] = $driver['realname'] ? $driver['realname'] : $driver['username'];
            // 返回拥有着联系方式
            $result['phone'] = $driver['mobile'];
            // 返回车辆归属
            $result['driver_grade'] = '公司';
        }
        // 上传行驶证历史记录json
        $history_mess = json_decode($result['history_mess'],true);
        $result['history_mess']  = $this->my_sort($history_mess,'addtime',SORT_DESC);
        $this->assign('list',$result);
        return view('driver/cardetails');
    }
    // 删除车辆
    public function delcar_mess(){
        $id = input('id');
        $result = DB::table('ct_carcategory')->delete($id);
        if ($result) {
            $content = "删除了ID为".$id."车辆信息";
            $this->hanldlog($this->uid,$content);
            $this->success('删除成功', 'driver/carindex');
        }else{
            $this->error('删除成功');
        }
    }
    // to 添加车辆页面
    public function addcarcategory(){
        $cartype = DB::table('ct_cartype')->select();
        $this->assign('list',$cartype);
        return view('driver/carcategory');
    }
    // 添加车辆信息操作
    public function addCar(){
        $post_data = Request::instance()->post();

        // 判断是否有司机或者公司id
        if (empty($post_data['driverid']) && $post_data['com_id']) {
            $this->success("请选择车辆归属！");
        }

        // 判断是向公司还是个体添加车辆 1 司机 2 公司
        if($post_data['type'] == 1){ 
            $data['driverid'] = $post_data['driverid'];
        }else{ // 添加公司车辆
            $data['com_id'] = $post_data['com_id'];
        }
        
        // 车龄
        $data['car_age'] = $post_data['car_age'];
        // 添加时间
        $data['addtime'] = time();
        // 状态
        $data['status'] = $post_data['status'];
        // 车型
        $data['carid'] = $post_data['cartype'];
        // 车温
        $data['temperature'] = $post_data['temperature'];
        // 车牌号
        $data['carnumber'] = $post_data['carnumber'];
        // 车照片
        if (request()->file('travelimg') !='') {
            $travelimg_path = $this->file_upload('travelimg','jpg,gif,png,jpeg','driver');
            $data['travelimg'] = $travelimg_path['file_path'];
        }
        if (request()->file('operateimg') !='') {
            $operateimg_path = $this->file_upload('operateimg','jpg,gif,png,jpeg','driver');
            $data['operateimg'] = $operateimg_path['file_path'];
        }
        if (request()->file('carimage') !='') {
            $carimage_path = $this->file_upload('carimage','jpg,gif,png,jpeg','driver');
            $data['carimage'] = $carimage_path['file_path'];
        }
        // 插入数据
        $add_driver = DB::table('ct_carcategory')->insertGetId($data);
        // 判断是否插入成功
        if ($add_driver){
            $this->success("添加成功!!!",'driver/carindex');
        }else{
            $this->success("添加失败!!!");
        }
    }
    // to 修改车辆页面
    public function updatecarcategory(){
        $id = input('id');
        $driver = DB::field('a.*,b.realname,c.name')
                    ->table('ct_carcategory')
                    ->alias('a')
                    ->join('ct_driver b','b.drivid = a.driverid','LEFT')
                    ->join('ct_company c','c.cid = a.com_id','LEFT')
                    ->where('ccid',$id)
                    ->find();
        $cartype = DB::table('ct_cartype')->select();
        $this->assign('result',$driver);
        $this->assign('list',$cartype);
        return view('driver/updatecarcategory');
    }
    // 修改车辆信息操作
    public function posttype(){
        $post_data = Request::instance()->post();
        // 判断是否有司机或者公司id
        if (empty($post_data['driverid']) && $post_data['com_id']) {
            $this->success("请选择车辆归属！");
        }

        // 判断是向公司还是个体添加车辆 1 司机 2 公司
        if ($post_data['type'] == 1) { 
            $data['driverid'] = $post_data['driverid'];
        } else { 
            $data['com_id'] = $post_data['com_id'];
        }  
        
        $data['car_age'] = $post_data['car_age'];
        $data['addtime'] = time();
        $data['status'] = $post_data['status'];
        $data['carid'] = $post_data['cartype'];
        $data['temperature'] = $post_data['temperature'];
        $data['carnumber'] = $post_data['carnumber'];
        if (request()->file('travelimg') !='') {
            $travelimg_path = $this->file_upload('travelimg','jpg,gif,png,jpeg','driver');
            $data['travelimg'] = $travelimg_path['file_path'];
        }
        if (request()->file('operateimg') !='') {
            $operateimg_path = $this->file_upload('operateimg','jpg,gif,png,jpeg','driver');
            $data['operateimg'] = $operateimg_path['file_path'];
        }
        if (request()->file('carimage') !='') {
            $carimage_path = $this->file_upload('carimage','jpg,gif,png,jpeg','driver');
            $data['carimage'] = $carimage_path['file_path'];
        }
       
     
        $id = $post_data['id'];
        $up_driver = DB::table('ct_carcategory')->where('ccid',$id)->update($data);
        if ($up_driver){
            $content = "修改了 ".$post_data['name']."的车辆信息";
            $this->hanldlog($this->uid,$content);
            $this->success("修改成功!!!",'driver/carindex');
        }else{
            $this->success("修改失败!!!");
        }
    }
    // 车辆信息验证操作
    public function carpass(){
        $post = Request::instance()->post();
        if ($post['ajax'] ==1) {
            if ($post['status'] == 3) {
                $data['fail_reason'] = $post['reason'];
            }
            $data['status'] = $post['status'];
            $data = DB::table('ct_carcategory')->where('ccid',$post['id'])->update($data);
        }
    }

    /**
     * 平台车型列表页面
     * @auther: 李渊
     * @date: 2018.9.17
     * @return [type] [description]
     */
    public function carlist(){
        // 查找数据
        $cartype = Db::table('ct_cartype')->order('car_id','asc')->select();
        // 模板赋值
        $this->assign('list',$cartype);
        // 模板渲染
        return view('driver/carlist');
    }
    /*
    *添加平台车型页面
    */
    public function cartype(){
        return view('driver/cartype');
    }
    /*
    *添加平台车型提交动作
    */
    public function addcartype(){
        $post = Request::instance()->post();
        //echo request()->file('image');
        if ($post['action'] == 'add') {
            if (request()->file('image') !='') {
                $path = $this->file_upload('image','jpg,png,gif','car');
                if (!$path) {
                   $this->error('图片上传有误!!!');
                }
                $car_data['avatar'] = $path['file_path'];
            }else{
                $this->error('请上传图片!!!');
            }
        }
        if ($post['action'] == 'update') {
            $find = DB::table('ct_cartype')->where('car_id',$post['id'])->find();
            $find_str = substr($find['avatar'],1);
            //echo $find_str;exit();
            if (request()->file('image') !='') {
                $path = $this->file_upload('image','jpg,png,gif','car');
                if (!$path) {
                   $this->error('图片上传有误!!!');
                }
                $car_data['avatar'] = $path['file_path'];
                 @unlink($find_str);
            }
        }
       
        
        //echo "<pre/>";
        //print_r($post);
        $car_data['pickup'] = $post['pickup'];
        $car_data['unload'] = $post['unload'];
        $car_data['morepickup'] = $post['morepickup'];
        $car_data['costkm'] = $post['costkm'];
        $car_data['lowprice'] = $post['lowprice'];
        $car_data['carparame'] = $post['carparame'];
        $car_data['allvolume'] = $post['allvolume'];
        $car_data['allweight'] = $post['allweight'];
        $car_data['dimensions'] = $post['dimensions'];
        $car_data['chartered'] = $post['chartered'];
        $car_data['klio'] = $post['klio'];
        if ($post['action'] == 'add') {
            $inserID = Db::table('ct_cartype')->insertGetId($car_data);
            if ($inserID) {
                $content = "添加了ID为".$post['carparame']."车型信息";
                 $this->hanldlog($this->uid,$content);
                $this->success('添加成功！！','driver/carlist');
                exit();
            }else{
                $this->success('添加失败！！！');
                 exit();
            }
        }
        if ($post['action'] == 'update') {
            $updateID = Db::table('ct_cartype')->where('car_id',$post['id'])->update($car_data);
             if (isset($updateID)) {
                $content = "修改了ID为".$post['carparame']."车型信息";
                $this->hanldlog($this->uid,$content);
                $this->success('修改成功！！','driver/carlist');
                exit();
            }else{
                $this->success('修改失败！！！');
                 exit();
            }
        }
    }
    /*
    *修改车型信息
    */
    public function updatecartype(){
        $getid = input('id');
        $cartype = Db::table('ct_cartype')->where('car_id',$getid)->find();
        $this->assign('list',$cartype);
        return view('driver/updatecartype');
    }
    /*
    *删除车型信息
    */
    public function delcar(){
        $getid = input('id');
        $data['status'] = 1;
        $del = Db::table('ct_cartype')->delete($getid);
        if ($del) {
                $content = "删除了ID为".$getid."车型信息";
                $this->hanldlog($this->uid,$content);
                $this->success('删除成功！！','driver/carlist');
                exit();
            }else{
                $this->success('删除失败！！！');
                 exit();
            }
    }
    /*
    *验证号码
    */
    public function checkmobile(){
        $get_post = Request::instance()->post();
        $phone = DB::table('ct_driver')->where(array('mobile'=>$get_post['mobile'],'delstate'=>'1'))->find();
       
        if (!empty($phone)) {
           return false;

        }else{
            return true;
        }
    }
    
    /*
    
    /*
    *筛选司机
    */
    public function checkdriver(){
        $serch = input('term');
        $arr = array();
        $reslut = DB::table('ct_driver')
                    ->where('realname','like',"%".$serch."%")
                    ->order('drivid','asc')
                    ->select();
        foreach ($reslut as $value) {
            $arr[] = array(
                    'id'=>$value['drivid'],
                    'label'=>$value['realname']
                );
        }
        echo json_encode($arr);
    }
    /*
    *检查司机
    */
    public function check(){
        $get_post = Request::instance()->post();
       
       $com = DB::table('ct_driver')->where(array('realname'=>['eq',$get_post['name']]))
                ->find();
        if (!empty($com)) {
           return true;

        }else{
            return false;
        }
    }

    /*
    *检查司机
    */
    public function playmoney(){
        $get_data = Request::instance()->get();
        $driverID = DB::table('ct_driver')->where('drivid',$get_data['id'])->find();
       if (!empty($driverID)) {
           $money = $driverID['money'] + $get_data['number'];

       }
       $data['money'] = $money;
       $updatamoney = DB::table('ct_driver')->where('drivid',$get_data['id'])->update($data);
       if ($updatamoney) {
            $content = "给".$driverID['mobile']."司机用户充值了".$get_data['number'];
            $this->hanldlog($this->uid,$content);
           $this->success('充值成功','driver/index');
       }else{
            $this->error('充值失败！！！');
       }
    }

    /**
     * to 司机位置页面
     * @Auther: 李渊
     * @Date: 2018.7.3
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function location(){
        return view('driver/location');
    }

    /**
     * 获取所有登陆司机的位置信息
     * @Auther: 李渊
     * @Date: 2018.7.3
     * @param  string $search [搜索]
     * @return [type]         [description]
     */
    public function getlocation(){
        // 获取搜索的手机号或者姓名
        $search = input("search");
        // 搜索姓名或者电话
        if (!empty($search)) {
            $where_data['d.realname|d.username|d.mobile'] = ['like','%'.$search.'%'];
        }
        // 承运人状态 1 正常  2 删除
        $where_data['delstate'] = 1;
        // 司机位置信息
        $result = DB::table('ct_user_location')
                ->alias('t')
                ->join('ct_driver d','d.drivid=t.userid')
                ->field('t.*,d.username,d.realname,d.mobile')
                ->select();
        // 返回数据
        return $result;
    }
}