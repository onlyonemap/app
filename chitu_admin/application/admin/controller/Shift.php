<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Shift extends Base
{
	function __construct(){
        parent::__construct();
        $this->uid = Session::get('admin_id','admin_mes');
        $this->if_login();
    }

    /**
     * 干线班次列表
     * @Auther: 李渊
     * @Date: 2018.8.1
     * @param  [type] $search       [公司名称]
     * @param  [type] $startcity    [起点城市]
     * @param  [type] $endcity      [终点城市]
     * @return [type]               [description]
     */
    public function index(){
        // 筛选字段 公司名称
        $search = input('search');
        // 筛选字段 起点城市
        $startcity = input('startcity');
        // 筛选字段 终点城市
        $endcity = input('endcity');
        // 查询条件 在线
        $where['a.delstate'] =1;
        // 页码
        $pageParam    = ['query' =>[]];
        // 查询条件 公司名称
        if (!empty($search)) {
            $where['b.name'] = ['like','%'. $search.'%'];
            $pageParam['query']['search'] = $search;
        }
        // 查询条件 起点城市
        if (!empty($startcity)) {
            $where['c.start_id'] = $startcity;
            $pageParam['query']['startcity'] = $startcity;
        }
        // 查询条件 终点城市
        if (!empty($endcity)) {
            $where['c.end_id'] = $endcity;
            $pageParam['query']['endcity'] = $endcity;
        }
        $aid = $this->uid;
        $grade = Db::table('ct_admin')->field('grade')->where('aid',$aid)->find();
        if ($grade['grade'] == 1){
            $result = DB::field('a.*,b.name,d.username,d.realname,d.mobile')
                ->table('ct_shift')
                ->alias('a')
                ->join('ct_company b','b.cid=a.companyid','left')
                ->join('ct_driver d','d.drivid=a.driver_id','left')
                ->join('ct_already_city c','c.city_id=a.linecityid','left')
                ->where($where)
                ->order('a.whethertoopen,a.sid','desc')
                ->paginate(10,false, $pageParam);
        }else{
            // 查询数据
            $result = DB::field('a.*,b.name,d.username,d.realname,d.mobile')
                ->table('ct_shift')
                ->alias('a')
                ->join('ct_company b','b.cid=a.companyid','left')
                ->join('ct_driver d','d.drivid=a.driver_id','left')
                ->join('ct_already_city c','c.city_id=a.linecityid','left')
                ->where($where)
                ->where('a.aid',$aid)
                ->order('a.whethertoopen,a.sid','desc')
                ->paginate(10,false, $pageParam);
        }

        // 转义数据
        $result_data = $result->toArray();
        // 定义数组
    	$array = array();
        // 循环数据
    	foreach ($result_data['data']  as $value) {
            // 查询班次起点城市、终点城市
            $arr = DB::table('ct_already_city')->where('city_id',$value['linecityid'])->find();
            // 起点城市
    		$value['start'] = addresidToName($arr['start_id']);
            // 终点城市
    		$value['end'] = addresidToName($arr['end_id']);
            // 判断改班次是平台添加还是用户自己添加
            if ($value['shiftstate'] =='2') {
                // 班期
                $value['shiftnumber'] = $value['weekday'];
                // 用户名
                $name = $value['realname'] ? $value['realname'] : $value['username'];
                
                $value['name'] = $name .'(TEL'.$value['mobile'].')';
            }
			$array[] = $value;
    	}
        // 查询所有的城市数据
        $address = Db::table('ct_district')->where('level',2)->select();
        $page = $result->render();
        $this->assign('aid',$aid);
        $this->assign('page',$page);
    	$this->assign('list',$array);
        $this->assign('address',$address);
    	return view('shift/index');
    }

    /**
     * page
     * 添加班次页面
     * @auther: 李渊
     * @date: 2018.12.6
     * @return [type] [description]
     */
    public function addshift(){
        // 定义星期数组
    	$arr = array("周一" => "周一", "周二" => "周二", "周三" => "周三", "周四" => "周四", "周五" => "周五", "周六" => "周六", "周日" => "周日");
        // 返回数据
    	$this->assign('arr',$arr);
        // 渲染视图
    	return view('shift/addshift');
    }

    /**
     * post
     * 添加班次
     * @auther: 李渊
     * @date: 2018.12.6
     * @note: 添加班次的时候添加班次的折扣
     * @return [type] [description]
     */
    public function addmessage(){
        $postdate = Request::instance()->post();
//        echo '<pre/>';
//        print_r($postdate);

        // 查询要添加的公司干线班次数据
        $com = DB::table('ct_company')->where('name',$postdate['name'])->where(array('type'=>'1','status'=>1))->find();
        // 如果没有该班次则返回错误提示
//        print_r($com['cid']);
        if (empty($com['cid'])) {
            $this->error('数据有误!请重新添加');
        }
//        die();
        // 判断是否有始发城市和终点城市
        if ($postdate['tpro'] =='0' &&  $postdate['ppro'] =='0') {
            $this->error('请选择始发和终点城市!!');
        }
        // 获取班次号
        $shif_data['shiftnumber'] = $postdate['ShiftNumber'];
        // 获取公司id
        $shif_data['companyid'] = $com['cid'];

        // 干线的时效
        $shif_data['trunkaging'] = $postdate['TrunkAging'];
        // 干线最低价
        $shif_data['lowprice'] = $postdate['lowprice'];
        // 免提货费最高吨位数
        $shif_data['freetonnage'] = $postdate['FreeTonnage'];
        // 干线开启状态
        $shif_data['whethertoopen'] = $postdate['whethertoopen'];
        // 折扣
        $shif_data['discount'] = $postdate['discount'];

        $shif_data['stime'] = $postdate['stime'];
        $shif_data['dtime'] = $postdate['dtime'];
        $shif_data['sphone'] = $postdate['sphone'];
        $shif_data['tphone'] = $postdate['tphone'];
        $shif_data['aid'] = $this->uid;
        // 发车时间 (如: 周一)
//        $shif_data['dewin'] = $postdate['DeWin'];
        // 发车时段开始时间 (如: 8:00)
        $shif_data['timestrat'] = $postdate['TimeStrat'];
        // 发车时段结束时间 (如: 10:00)
        $shif_data['timeend'] = $postdate['TimeEnd'];
        // 自行送货截止发车时间提前小时数 (如：1h)
        $shif_data['selfdeliverydeadline'] = $postdate['SelfDeliveryDeadline'];

        // 到车时间 (如: 周三)
//        $shif_data['arrivewin'] = $postdate['ArriveWin'];
        // 到车时段开始时间 (如: 18:00)
//        $shif_data['arrivetimestart'] = $postdate['ArriveTimeStart'];
        // 到车时段结束时间 (如: 21:00)
//        $shif_data['arrivetimeend'] = $postdate['ArriveTimeEnd'];
        // 到车后最早去提货时间提前小时数 (如：1h)
        $shif_data['morningtime'] = $postdate['MorningTime'];

        // 干线起点仓地址
        if($postdate['sheng'] !='0' ) {
            $shif_data['beginprovinceid'] = $postdate['sheng'];
            $shif_data['begincityid'] = $postdate['shi'];
            $shif_data['beginareaid'] = $postdate['xian'];
            $shif_data['beginaddress'] = $postdate['beginAddress'];
            $shif_data['picksite'] = detailadd($postdate['sheng'],$postdate['shi'],$postdate['xian']) . $postdate['beginAddress'];
        }
        // 干线终点仓地址
        if ($postdate['sheng1'] !='0'  ) {
            $shif_data['endprovinceid'] = $postdate['sheng1'];
            $shif_data['endcityid'] = $postdate['shi1'];
            $shif_data['endareaid'] = $postdate['xian1'];
            $shif_data['endaddress'] = $postdate['endAddress'];
            $shif_data['sendsite'] = detailadd($postdate['sheng1'],$postdate['shi1'],$postdate['xian1']) . $postdate['endAddress'];
        }

        // 添加时间
        $shif_data['addtime'] = time();
        
        $arr_week = array("0" => "周日","1" => "周一","2" => "周二","3" => "周三","4" => "周四","5" => "周五","6" => "周六");
        // 获取到发车的周
//        $get_start_week = array_search($postdate['DeWin'],$arr_week);
        // 获取到到车的周
//        $get_end_week = array_search($postdate['ArriveWin'],$arr_week);
        // 发车队列具体发车时间
//        $deptime = $this->getTimeFromWeek($get_start_week);
        $aging = str_replace('天', '', $postdate['TrunkAging']);
        // 发车队列具体到车时间
//        $endtime = strtotime("+$aging day",$deptime);

        // 干线始发城市
        $shifa = $postdate['tcity'];
        // 干线终点城市
        $zhogndian = $postdate['pcity'];

        // 排除城市是否存在
        $find_city = Db::table('ct_already_city')->where(array('start_id'=>$shifa,'end_id'=>$zhogndian))->find();
        // 如果不存在该始发-终点的线路则添加
        if ($find_city=='') {
            // 起点城市
            $city_data['start_id'] = $shifa;
            // 终点城市
            $city_data['end_id'] = $zhogndian;
            // 添加时间
            $city_data['add_time'] = time();
            // 添加到城市库里
            $city_id = Db::table('ct_already_city')->insertGetId($city_data);
        }else{
            $city_id = $find_city['city_id'];
        }
        // 线路ID
        $shif_data['linecityid'] = $city_id;
        // 查询干线起点城市对应的提货区域
        $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$com['cid'],'type'=>1,'province'=>$postdate['shi']))->find();
        // 查询干线终点城市对应的配送区域
        $pei_arr = DB::table('ct_tpprice')->where(array('companyid'=>$com['cid'],'type'=>2,'province'=>$postdate['shi1']))->find();
        $shif_data['pmoney'] = $ti_arr['price'] ;
        $shif_data['smoney'] = $pei_arr['price'];
        $array = array();
        $arr_price1 = $postdate['mytext1'];
        $arr_price2 = $postdate['mytext2'];
        $arr_price3 = $postdate['mytext3'];
        // 获取抛货价格(元/m³)
        $shif_data['eprice'] = $postdate['mytext3'][0]*1000/2.5;
        //干线运价
        $pos = array_search(min($arr_price3), $arr_price3);
        $shif_data['price'] = $arr_price3[$pos];
        $shif_data['shiftstate'] = 1;

        $shif_data['datatime'] = implode($postdate['datatime'],'、');
        //插入班次记录
//        echo '<pre/>';
//        print_r($shif_data);
//        exit();
        $shiftid = Db::table('ct_shift')->insertGetId($shif_data);
        $i=0;
        //超吨位减免费用区间
        foreach ($arr_price1 as $key => $value) {
            $array[$i][]= $arr_price1[$key];
            $array[$i][]= $arr_price2[$key];
            $array[$i][]= $arr_price3[$key];
            $i++;
        }
        foreach ($array as $value) {
            $price_date['starweight'] = $value['0'];
            $price_date['endweight'] = $value['1'];
            $price_date['freeprice'] = $value['2'];
            $price_date['shiftid'] = $shiftid;
            Db::table('ct_shiftfree')->insert($price_date);
        }
        //发车队列
//        $date_log['deptime'] = $deptime;
//        $date_log['endtime'] = $endtime;
//        $date_log['tonnage'] = '';
//        $date_log['volume'] = '';
//        $date_log['shiftid'] = $shiftid;
//        Db::table('ct_shift_log')->insert($date_log);
        if($shiftid){
            $content = "添加了 ".$postdate['name']."编号为 ".$postdate['ShiftNumber']." 班次";
            $this->hanldlog($this->uid,$content);
             $this->success('新增成功', 'shift/index');
        }else{
            $this->error('新增失败');
        }
    }

    public function addmessag(){
        $postdate = Request::instance()->post();
        foreach($postdate['datatime'] as $key =>$value){


        // 查询要添加的公司干线班次数据
        $com = DB::table('ct_company')->where('name',$postdate['name'])->where(array('type'=>'1','status'=>1))->find();
        // 如果没有该班次则返回错误提示
//        print_r($com['cid']);
        if (empty($com['cid'])) {
            $this->error('数据有误!请重新添加');
        }
//        die();
        // 判断是否有始发城市和终点城市
        if ($postdate['tpro'] =='0' &&  $postdate['ppro'] =='0') {
            $this->error('请选择始发和终点城市!!');
        }

        // 获取公司id
        $shif_data['companyid'] = $com['cid'];
        // 获取抛货价格(元/m³)
            $shif_data['eprice'] = $postdate['mytext3'][0]*1000/2.5;
        // 干线的时效
        $shif_data['trunkaging'] = $postdate['TrunkAging'];
        // 干线最低价
        $shif_data['lowprice'] = $postdate['lowprice'];
        // 免提货费最高吨位数
        $shif_data['freetonnage'] = $postdate['FreeTonnage'];
        // 干线开启状态
        $shif_data['whethertoopen'] = $postdate['whethertoopen'];
        // 折扣
        $shif_data['discount'] = $postdate['discount'];

        $shif_data['stime'] = $postdate['stime'];
        $shif_data['dtime'] = $postdate['dtime'];
        $shif_data['sphone'] = $postdate['sphone'];
        $shif_data['tphone'] = $postdate['tphone'];
        $shif_data['aid'] = $this->uid;
        // 发车时间 (如: 周一)

        $shif_data['dewin'] = $value;
        // 发车时段开始时间 (如: 8:00)
        $shif_data['timestrat'] = $postdate['TimeStrat'];
        // 发车时段结束时间 (如: 10:00)
        $shif_data['timeend'] = $postdate['TimeEnd'];
        // 自行送货截止发车时间提前小时数 (如：1h)
        $shif_data['selfdeliverydeadline'] = $postdate['SelfDeliveryDeadline'];



        // 干线起点仓地址
        if($postdate['sheng'] !='0' ) {
            $shif_data['beginprovinceid'] = $postdate['sheng'];
            $shif_data['begincityid'] = $postdate['shi'];
            $shif_data['beginareaid'] = $postdate['xian'];
            $shif_data['beginaddress'] = $postdate['beginAddress'];
            $shif_data['picksite'] = detailadd($postdate['sheng'],$postdate['shi'],$postdate['xian']) . $postdate['beginAddress'];
        }
        // 干线终点仓地址
        if ($postdate['sheng1'] !='0'  ) {
            $shif_data['endprovinceid'] = $postdate['sheng1'];
            $shif_data['endcityid'] = $postdate['shi1'];
            $shif_data['endareaid'] = $postdate['xian1'];
            $shif_data['endaddress'] = $postdate['endAddress'];
            $shif_data['sendsite'] = detailadd($postdate['sheng1'],$postdate['shi1'],$postdate['xian1']) . $postdate['endAddress'];
        }

        // 添加时间
        $shif_data['addtime'] = time();

        $arr_week = array("0" => "周日","1" => "周一","2" => "周二","3" => "周三","4" => "周四","5" => "周五","6" => "周六");
        // 获取到发车的周
        $get_start_week = array_search($value,$arr_week);
        // 获取到到车的周
        // $get_end_week = array_search($postdate['ArriveWin'],$arr_week);
        // 发车队列具体发车时间
        $deptime = $this->getTimeFromWeek($get_start_week);
        $aging = str_replace('天', '', $postdate['TrunkAging']);
        // 发车队列具体到车时间
        $endtime = strtotime("+$aging day",$deptime);

        $number_wk=date("w",$endtime);

            // 获取班次号
            $shif_data['shiftnumber'] = $postdate['ShiftNumber'].array_search($value,$arr_week).($key+1);



        // 干线始发城市
        $shifa = $postdate['tcity'];
        // 干线终点城市
        $zhogndian = $postdate['pcity'];

            // 到车时间 (如: 周三)
          $shif_data['arrivewin'] =  $arr_week[$number_wk];
            // 到车时段开始时间 (如: 18:00)
            $shif_data['arrivetimestart'] = $postdate['ArriveTimeStart'];
            // 到车时段结束时间 (如: 21:00)
            $shif_data['arrivetimeend'] = $postdate['ArriveTimeEnd'];
            // 到车后最早去提货时间提前小时数 (如：1h)
            $shif_data['morningtime'] = $postdate['MorningTime'];

        // 排除城市是否存在
        $find_city = Db::table('ct_already_city')->where(array('start_id'=>$shifa,'end_id'=>$zhogndian))->find();
        // 如果不存在该始发-终点的线路则添加
        if ($find_city=='') {
            // 起点城市
            $city_data['start_id'] = $shifa;
            // 终点城市
            $city_data['end_id'] = $zhogndian;
            // 添加时间
            $city_data['add_time'] = time();
            // 添加到城市库里
            $city_id = Db::table('ct_already_city')->insertGetId($city_data);
        }else{
            $city_id = $find_city['city_id'];
        }
        // 线路ID
        $shif_data['linecityid'] = $city_id;
        // 查询干线起点城市对应的提货区域
        $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$com['cid'],'type'=>1,'province'=>$postdate['shi']))->find();
        // 查询干线终点城市对应的配送区域
        $pei_arr = DB::table('ct_tpprice')->where(array('companyid'=>$com['cid'],'type'=>2,'province'=>$postdate['shi1']))->find();
        $shif_data['pmoney'] = $ti_arr['price'] ;
        $shif_data['smoney'] = $pei_arr['price'];
        $array = array();
        $arr_price1 = $postdate['mytext1'];
        $arr_price2 = $postdate['mytext2'];
        $arr_price3 = $postdate['mytext3'];
        //干线运价
        $pos = array_search(min($arr_price3), $arr_price3);
        $shif_data['price'] = $arr_price3[$pos];
        $shif_data['shiftstate'] = 1;

        //插入班次记录
        $shiftid = Db::table('ct_shift')->insertGetId($shif_data);
        $i=0;
        //超吨位减免费用区间
        foreach ($arr_price1 as $key => $value) {
            $array[$i][]= $arr_price1[$key];
            $array[$i][]= $arr_price2[$key];
            $array[$i][]= $arr_price3[$key];
            $i++;
        }
        foreach ($array as $value) {
            $price_date['starweight'] = $value['0'];
            $price_date['endweight'] = $value['1'];
            $price_date['freeprice'] = $value['2'];
            $price_date['shiftid'] = $shiftid;
            Db::table('ct_shiftfree')->insert($price_date);
        }
        //发车队列
        $date_log['deptime'] = $deptime;
        $date_log['endtime'] = $endtime;
        $date_log['tonnage'] = '';
        $date_log['volume'] = '';
        $date_log['shiftid'] = $shiftid;
        Db::table('ct_shift_log')->insert($date_log);
        }
        if($shiftid){
            $content = "添加了 ".$postdate['name']."编号为 ".$postdate['ShiftNumber']." 班次";
            $this->hanldlog($this->uid,$content);
            $this->success('新增成功', 'shift/index');
        }else{
            $this->error('新增失败');
        }
    }
   /*
   *班次模板添加
   */
    public function addtemp(){
    	$postdate = Request::instance()->post();
       
        if ($postdate['action'] == 'temp') {
            $upshift = DB::table('ct_shift')->where('sid',$postdate['sid'])->find();
        }
    	$com = DB::table('ct_company')->where('name','eq',$postdate['name'])->where(array('type'=>'1','status'=>1))->find();
    	if (empty($com['cid'])) {
    		$this->error('数据有误!请重新添加');
    	}
    	$shif_data['shiftnumber'] = $postdate['ShiftNumber'];
    	$shif_data['companyid'] = $com['cid'];
    	//$shif_data['price'] = $postdate['Price'];
        $shif_data['eprice'] = $postdate['Eprice'];
    	$shif_data['addtime'] = time();
    	$shif_data['timestrat'] = $postdate['TimeStrat'];
    	$shif_data['timeend'] = $postdate['TimeEnd'];
    	$shif_data['selfdeliverydeadline'] = $postdate['SelfDeliveryDeadline'];
       if(isset($postdate['action']) =='temp' && $postdate['sheng'] =='0') {
            //当模板提交并且起始仓库地址不改变时
            $shif_data['beginprovinceid'] = $upshift['beginprovinceid'];
            $shif_data['begincityid'] = $upshift['begincityid'];
            $shif_data['beginareaid'] = $upshift['beginareaid'];
            $shif_data['beginaddress'] = $upshift['beginaddress'];
        }elseif($postdate['sheng'] !='0' ) {
            $shif_data['beginprovinceid'] = $postdate['sheng'];
            $shif_data['begincityid'] = $postdate['shi'];
            $shif_data['beginareaid'] = $postdate['xian'];
            $shif_data['beginaddress'] = $postdate['beginAddress'];
        } 
        
        
        //$shif_data['transfer'] = $postdate['transfer'];
    	 $shif_data['lowprice'] = $postdate['lowprice'];
    	$shif_data['trunkaging'] = $postdate['TrunkAging'];
    	$shif_data['arrivewin'] = $postdate['ArriveWin'];
    	$shif_data['arrivetimestart'] = $postdate['ArriveTimeStart'];
    	$shif_data['arrivetimeend'] = $postdate['ArriveTimeEnd'];
    	$shif_data['morningtime'] = $postdate['MorningTime'];
        if(isset($postdate['action']) =='temp' && $postdate['sheng1'] =='0') {
            //当模板提交并且终点仓库地址不改变时
            $shif_data['endprovinceid'] = $upshift['endprovinceid'];
            $shif_data['endcityid'] = $upshift['endcityid'];
            $shif_data['endareaid'] = $upshift['endareaid'];
            $shif_data['endaddress'] = $upshift['endaddress'];
        }elseif ($postdate['sheng1'] !='0'  ) {
        	$shif_data['endprovinceid'] = $postdate['sheng1'];
        	$shif_data['endcityid'] = $postdate['shi1'];
        	$shif_data['endareaid'] = $postdate['xian1'];
            $shif_data['endaddress'] = $postdate['endAddress'];
        }
        $shif_data['dewin'] = $postdate['DeWin'];
        $arr_week = array("0" => "周日","1" => "周一","2" => "周二","3" => "周三","4" => "周四","5" => "周五","6" => "周六");
        //获取到发车的周
        $get_start_week = array_search($postdate['DeWin'],$arr_week);
        //获取到到车的周
        $get_end_week = array_search($postdate['ArriveWin'],$arr_week);
        //发车队列具体发车时间
        $deptime = $this->getTimeFromWeek($get_start_week);
        $aging = str_replace('天', '', $postdate['TrunkAging']);
        //发车队列具体到车时间
        $endtime = strtotime("+$aging day",$deptime);

    	//直辖市中对应的区改为市
    	$shifa = '';
    	$zhogndian = '';
        if (isset($postdate['action']) =='temp' &&  $postdate['tpro'] =='0' &&  $postdate['ppro'] =='0') {
            //当模板提交并且始发和终点城市不改变时
            $city_id = $upshift['linecityid'];
        }else{
        if ($postdate['tpro'] =='0' &&  $postdate['ppro'] =='0') {
            $this->error('请选择始发和终点城市!!');
        }
        $shifa = $postdate['tcity'];
   
        $zhogndian = $postdate['pcity'];           
        //排除城市是否存在
        $find_city = Db::table('ct_already_city')->where(array('start_id'=>$shifa,'end_id'=>$zhogndian))->find();
        if ($find_city=='') {
            $city_data['start_id'] = $shifa;
            $city_data['end_id'] = $zhogndian;
             $city_data['add_time'] = time();
            $city_id = Db::table('ct_already_city')->insertGetId($city_data);
        }else{
            $city_id = $find_city['city_id'];
        }
        }
	    
        $shif_data['whethertoopen'] = $postdate['whethertoopen'];
    	$shif_data['freetonnage'] = $postdate['FreeTonnage'];
        $shif_data['linecityid'] = $city_id;
    	
    	$array = array();
    	$arr_price1 = $postdate['mytext1'];
    	$arr_price2 = $postdate['mytext2'];
    	$arr_price3 = $postdate['mytext3'];
        $pos = array_search(min($arr_price3), $arr_price3);
        $shif_data['price'] = $arr_price3[$pos];
        $shif_data['shiftstate'] = 1;
        $shiftid = Db::table('ct_shift')->insertGetId($shif_data);
    	$i=0;
        //超吨位减免费用区间
    	foreach ($arr_price1 as $key => $value) {
    		$array[$i][]= $arr_price1[$key];
    		$array[$i][]= $arr_price2[$key];
    		$array[$i][]= $arr_price3[$key];
    		$i++;
    	}
  		foreach ($array as $value) {
  			$price_date['starweight'] = $value['0'];
  			$price_date['endweight'] = $value['1'];
  			$price_date['freeprice'] = $value['2'];
  			$price_date['shiftid'] = $shiftid;
  			Db::table('ct_shiftfree')->insert($price_date);
  		}
        //发车队列
        $date_log['deptime'] = $deptime;
        $date_log['endtime'] = $endtime;
        $date_log['tonnage'] = '';
        $date_log['volume'] = '';
        $date_log['shiftid'] = $shiftid;
        Db::table('ct_shift_log')->insert($date_log);
    	if($shiftid){
            $content = "添加了 ".$postdate['name']."编号为 ".$postdate['ShiftNumber']." 班次";
            $this->hanldlog($this->uid,$content);
    		 $this->success('新增成功', 'shift/index');
    	}else{
    		$this->error('新增失败');
    	}
    }
    //删除动作
    public function delcom(){
    	$get = Request::instance()->get();
    	if($get['del'] == 1){
           $delcom = DB::table('ct_shift')->where('sid',$get['id'])->update(array('whethertoopen'=>2));
           if($delcom){
                DB::table('ct_shift_log')->where('shiftid',$get['id'])->update(array('status'=>2));
                $content = "关闭了ID为".$get['id']."班次信息";
                $this->hanldlog($this->uid,$content);
                $this->success('关闭成功', 'shift/index');
           }else{
                $this->error('关闭失败');
           }
        }else if($get['del'] == 2){
            $select = DB::table('ct_shift')->where('sid',$get['id'])->find();
            $delcom = DB::table('ct_shift')->where('sid',$get['id'])->update(array('whethertoopen'=>1));
            $arr_week = array("0" => "周日","1" => "周一","2" => "周二","3" => "周三","4" => "周四","5" => "周五","6" => "周六");
            //获取到发车的周
            $get_start_week = array_search($select['dewin'],$arr_week);
            
            //发车队列具体发车时间
            $deptime = $this->getTimeFromWeek($get_start_week);
            $aging = str_replace('天', '', $select['trunkaging']);
            //发车队列具体到车时间
            $endtime = strtotime("+$aging day",$deptime);
           if($delcom){
                //发车队列
                $date_log['deptime'] = $deptime;
                $date_log['endtime'] = $endtime;
                $date_log['tonnage'] = '';
                $date_log['volume'] = '';
                $date_log['shiftid'] = $select['sid'];
                Db::table('ct_shift_log')->insert($date_log);
                $content = "开启了ID为".$select['sid']."班次信息";
                $this->hanldlog($this->uid,$content);
                $this->success('开启成功', 'shift/index');
           }else{
                $this->error('开启失败');
           }
        }else if($get['del'] == 3){
             $findorder = DB::table('ct_order')
                        ->alias('a')
                        ->join('ct_shift_log b','b.slid = a.slogid')
                        ->where(array('b.shiftid'=>$get['id'],'b.status'=>1))
                        ->select();
            if (!empty($findorder)) {
                $this->error('班次已生成订单，班次禁止删除,可以选择关闭!!!');
            }
            $delcom = DB::table('ct_shift')->where('sid',$get['id'])->delete();
            DB::table('ct_shiftfree')->where('shiftid',$get['id'])->delete();
            DB::table('ct_shift_log')->where('shiftid',$get['id'])->delete();
            $find_city = DB::table('ct_shift')->field('linecityid')->where('sid',$get['id'])->find();
            $line = DB::table('ct_fixation_line')->where('lienid',$find_city['linecityid'])->find();
            $shift = Db::table('ct_shift')->where('linecityid',$find_city['linecityid'])->find();
            if (empty($line) && empty($shift)) {
                DB::table('ct_already_city')->where('city_id',$find_city['linecityid'])->delete();
            } 
           if($delcom){
                $content = "删除了ID为".$get['id']."班次信息";
                $this->hanldlog($this->uid,$content);
                $this->success('删除成功', 'shift/index');
           }else{
                $this->error('删除失败');
           }
        }
    }

    public function updateshift(){
    	$arr = array("周一" => "周一","周二" => "周二","周三" => "周三","周四" => "周四","周五" => "周五","周六" => "周六","周日" => "周日");
    	$tim = array( "00:00" => "00:00","01:00" => "01:00","02:00" => "02:00","03:00" => "03:00","04:00" => "04:00","05:00" => "05:00","06:00" => "06:00","07:00" => "07:00","08:00" => "08:00","09:00" => "09:00","10:00" => "10:00","11:00" => "11:00","12:00" => "12:00","13:00" => "13:00","14:00" => "14:00","15:00" => "15:00","16:00" => "16:00","17:00" => "17:00","18:00" => "18:00","19:00" => "19:00","20:00" => "20:00","21:00" => "21:00","22:00" => "22:00","23:00" => "23:00");
    	$hou = array("1h" => "1h","2h" => "2h","3h" => "3h","4h" => "4h","5h" => "5h","6h" => "6h","7h" => "7h","8h" => "8h","9h" => "9h","10h" => "10h","11h" => "11h","12h" => "12h","13h" => "13h","14h" => "14h","15h" => "15h","16h" => "16h","17h" => "17h","18h" => "18h","19h" => "19h","20h" => "20h","21h" => "21h","22h" => "22h","23h" => "23h");
        $day = array("1天" => "1天","2天" => "2天","3天" => "3天","4天" => "4天","5天" => "5天","6天" => "6天","7天" => "7天","8天" => "8天","9天" => "9天","10天" => "10天","11天" => "11天","12天" => "12天","13天" => "13天","14天" => "14天","15天" => "15天");
        $getid = Request::instance()->get('id'); 
    	$result =DB::field('a.*,b.name,c.start_id,c.end_id')
                    ->table('ct_shift')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.companyid')
                    ->join('ct_already_city c','c.city_id=a.linecityid')
                    ->where('sid',$getid)
                    ->find();
    	$linestart = $this->start_city($result['start_id']);
    	$lineend = $this->start_city($result['end_id']);
    	$result['linestart'] = $linestart;
    	$result['lineend'] = $lineend;
    	$result['free'] = DB::table('ct_shiftfree')
                            ->where('shiftid',$result['sid'])
                            ->select();
        $result['shifa'] = detailadd($result['beginprovinceid'],$result['begincityid'],$result['beginareaid']) . $result['beginaddress'];
        $result['zhongdiancan'] = detailadd($result['endprovinceid'],$result['endcityid'],$result['endareaid']) . $result['endaddress'];
    	
    	$this->assign('arr',$arr);
        $this->assign('tim',$tim);
        $this->assign('hou',$hou);
        $this->assign('day',$day);
    	$this->assign('list',$result);
    	return view('shift/updateshift');
    }
    public function updatemessage(){
        $postdate = Request::instance()->post();
       
        $com = DB::table('ct_company')->where('name','eq',$postdate['name'])->where(array('type'=>'1','status'=>1))->find();
        if (empty($com['cid'])) {
            $this->error('数据有误!请重新添加');
        }
        //检索该修改班次是否修改了发车或者到车时间
        $search_shift = DB::field('shiftnumber,arrivewin,arrivetimestart,arrivetimeend,dewin,timestrat,timeend')
                        ->table('ct_shift')
                        ->where('sid',$postdate['sid'])
                        ->find();
        //修改班次号时候判断是否已重复存在
        if ($postdate['ShiftNumber'] != $search_shift['shiftnumber']) {
               $check_number = DB::table('ct_shift')->where(array('shiftnumber'=>$postdate['ShiftNumber'],'shiftstate'=>'1'))->select();
               if (!empty($check_number)) {
                   $this->error('该班次号已存在,请重新输入!!');
               }
        }  
        //修改发车或者到车时间 是否存在该订单中
        $select_order = DB::table('ct_order')
                        ->alias('a')
                        ->join('ct_shift_log b','b.slid = a.slogid')
                        ->where(array('b.shiftid'=>$postdate['sid'],'b.status'=>1))
                        ->select();
        if ($search_shift['arrivewin'] != $postdate['ArriveWin'] || $search_shift['arrivetimestart'] != $postdate['ArriveTimeStart'] || $search_shift['arrivetimeend'] != $postdate['ArriveTimeEnd']) {
            if (!empty($select_order)) {
                $this->error('该班次已生成订单，到车时间禁止修改!!!');
            }
        }
        if ($search_shift['dewin'] != $postdate['DeWin'] || $search_shift['timestrat'] != $postdate['TimeStrat'] || $search_shift['timeend'] != $postdate['TimeEnd']) {
            if (!empty($select_order)) {
                $this->error('该班次已生成订单，发车时间禁止修改!!!');
            }
        }
        //当修改发车时间和到车时间时
         if ($search_shift['dewin'] != $postdate['DeWin'] || $search_shift['arrivewin'] != $postdate['ArriveWin']) {
            Db::table('ct_shift_log')->where('shiftid',$postdate['sid'])->delete();
            $arr_week = array("0" => "周日","1" => "周一","2" => "周二","3" => "周三","4" => "周四","5" => "周五","6" => "周六");
            //获取到发车的周
            $get_start_week = array_search($postdate['DeWin'],$arr_week);
            //获取到到车的周
            $get_end_week = array_search($postdate['ArriveWin'],$arr_week);
            //发车队列具体发车时间
            $deptime = $this->getTimeFromWeek($get_start_week);
            $aging = str_replace('天', '', $postdate['TrunkAging']);
            //发车队列具体到车时间
            $endtime = strtotime("+$aging day",$deptime);

            //发车队列
            $date_log['deptime'] = $deptime;
            $date_log['endtime'] = $endtime;
            $date_log['tonnage'] = '';
            $date_log['volume'] = '';
            $date_log['shiftid'] = $postdate['sid'];
            Db::table('ct_shift_log')->insert($date_log);
        }
        $shif_data['shiftnumber'] = $postdate['ShiftNumber'];
        $shif_data['companyid'] = $com['cid'];
       //$shif_data['price'] = $postdate['Price'];
        $shif_data['eprice'] = $postdate['Eprice'];
        // 折扣
        $shif_data['discount'] = $postdate['discount'];
        
        $shif_data['edittime'] = time();
       
        $shif_data['selfdeliverydeadline'] = $postdate['SelfDeliveryDeadline'];
        if($postdate['sheng'] !='0' && $postdate['sheng'] !=''){
            $shif_data['beginprovinceid'] = $postdate['sheng'];
            $shif_data['begincityid'] = $postdate['shi'];
            $shif_data['beginareaid'] = $postdate['xian'];
            $shif_data['beginaddress'] = $postdate['beginAddress'];
            $shif_data['picksite'] = detailadd($postdate['sheng'],$postdate['shi'],$postdate['xian']) . $postdate['beginAddress'];
        }
        //$shif_data['transfer'] = $postdate['transfer'];
        $shif_data['lowprice'] = $postdate['lowprice'];
        $shif_data['whethertoopen'] = $postdate['whethertoopen'];
        $shif_data['trunkaging'] = $postdate['TrunkAging'];
        $shif_data['morningtime'] = $postdate['MorningTime'];

        //到车时间
        $shif_data['arrivewin'] = $postdate['ArriveWin'];
        $shif_data['arrivetimestart'] = $postdate['ArriveTimeStart'];
        $shif_data['arrivetimeend'] = $postdate['ArriveTimeEnd'];
        //发车时间
        $shif_data['dewin'] = $postdate['DeWin'];
        $shif_data['timestrat'] = $postdate['TimeStrat'];
        $shif_data['timeend'] = $postdate['TimeEnd'];


        if($postdate['sheng1'] !='0' && $postdate['sheng1'] !=''){
            $shif_data['endprovinceid'] = $postdate['sheng1'];
            $shif_data['endcityid'] = $postdate['shi1'];
            $shif_data['endareaid'] = $postdate['xian1'];
            $shif_data['endaddress'] = $postdate['endAddress'];
            $shif_data['sendsite'] = detailadd($postdate['sheng1'],$postdate['shi1'],$postdate['xian1']) . $postdate['endAddress'];
        }

        //直辖市中对应的区改为市
        $shifa = '';
        $zhogndian = '';
        if ($postdate['tpro'] !='0' && $postdate['tpro'] !='') {
            $shifa = $postdate['tcity'];
        }
        if ($postdate['ppro'] !='0' && $postdate['ppro'] !='') {
            $zhogndian = $postdate['pcity'];  
        }
        if ($shifa !='' && $zhogndian !='') {
            //$cityup = Db::table('ct_already_city')->where('city_id',$postdate['alrcityid'])->update($city_data);
             $find_city = Db::table('ct_already_city')->where(array('start_id'=>$shifa,'end_id'=>$zhogndian))->find();
            if ($find_city=='') {
                $city_data['start_id'] = $shifa;
                $city_data['end_id'] = $zhogndian;
                 $city_data['add_time'] = time();
                $city_id = Db::table('ct_already_city')->insertGetId($city_data);
            }else{
                $city_id = $find_city['city_id'];
            }
            $shif_data['linecityid'] = $city_id;
        }

        $shif_data['freetonnage'] = $postdate['FreeTonnage'];
        $array = array();
        $arr_sfid = array();
        $arr_price1 =array();
        $arr_price2 =array();
        $arr_price3 =array();
        if (isset($postdate['sfid'])) {
            $arr_sfid = $postdate['sfid'];
        }
        if (isset($postdate['mytext1'])) {
            $arr_price1 = $postdate['mytext1'];
            $arr_price2 = $postdate['mytext2'];
            $arr_price3 = $postdate['mytext3'];
        
            //去区间价格最小值
            $pos = array_search(min($arr_price3), $arr_price3);
            $shif_data['price'] = $arr_price3[$pos];
            //更新记录
            
            $i=0;
            foreach ($arr_price1 as $key => $value) {
                $array[$i][]= $arr_sfid[$key];
                $array[$i][]= $arr_price1[$key];
                $array[$i][]= $arr_price2[$key];
                $array[$i][]= $arr_price3[$key];
                $i++;
            }
        }
       
        $shiftdate = Db::table('ct_shift')->where('sid',$postdate['sid'])->update($shif_data);
        //更新重量区间记录
        if (!empty($array)) {
            foreach ($array as $key => $value) {
                 $price_date['starweight'] = $value['1'];
                    $price_date['endweight'] = $value['2'];
                    $price_date['freeprice'] = $value['3'];
                if(empty($value['0'])){
                  
                    $price_date['shiftid'] = $postdate['sid'];
                    $shifdata =  Db::table('ct_shiftfree')->insert($price_date);
                }else{
                    $shif = Db::table('ct_shiftfree')->where(array('sfid'=>$value['0'],'shiftid'=>$postdate['sid']))->update($price_date);
                }
                
            }
        }
        
        
        if(isset($shiftdate)|| isset($shifdata)|| isset($shif) || isset($city_id)){
                $content = "修改了 ".$postdate['name']."编号为 ".$postdate['ShiftNumber']." 班次";
                $this->hanldlog($this->uid,$content);
                $this->success('修改成功', 'shift/index');
           }else{
                $this->error('修改失败');
           }
      
    }
    public function del(){
        $id = input('sfid');
        if (input('ajax') == 1) {
             Db::table('ct_shiftfree')->delete($id); 
        }
    }
    /*
    *模板列表
    */
    public function template(){
        $search = input('search');
        $where['a.delstate'] =1;
        $where['b.status'] =1;
        $where['a.shiftstate'] =1;
        $pageParam    = ['query' =>[]];
        if (!empty($search)) {
            $where['a.shiftnumber|b.name'] = ['like','%'. $search.'%'];
            $pageParam['query']['search'] = $search;
        }

        $result_count = array();
        $result_com = DB::field('count(sid),companyid,b.name,a.delstate,b.status')
                        ->table('ct_shift')
                        ->alias('a')
                        ->join('ct_company b','b.cid=a.companyid')
                        ->where($where)
                        ->group('companyid')
                        ->paginate(10,false, $pageParam);
        foreach ($result_com as $key => $val) {
             $result_count[$key]['companyname'] = $val['name'];

             $result_shift = DB::field('sid,shiftnumber,count(sid),companyid,linecityid,b.name')
                ->table('ct_shift')
                ->alias('a')
                ->join('ct_company b','b.cid=a.companyid')
                ->where('companyid',$val['companyid'])
                ->group('companyid,linecityid')
                ->order('sid desc')
                ->select();
                foreach (  $result_shift as  $v) {
                    $arr = DB::table('ct_already_city')->where('city_id',$v['linecityid'])->find();
                    $v['start'] = $this->start_city($arr['start_id']);
                    $v['end'] = $this->start_city($arr['end_id']);
                    $result_count[$key]['shift'][] = $v;
                }
        }
       
        
        $page = $result_com->render();
        $this->assign('page',$page);
        $this->assign('list',$result_count);
        return view('shift/template');
    }
    /*
    *编辑模板页面
    */
    public function uptemplate(){
        $arr = array("周一" => "周一","周二" => "周二","周三" => "周三","周四" => "周四","周五" => "周五","周六" => "周六","周日" => "周日");
        $tim = array( "00:00" => "00:00","01:00" => "01:00","02:00" => "02:00","03:00" => "03:00","04:00" => "04:00","05:00" => "05:00","06:00" => "06:00","07:00" => "07:00","08:00" => "08:00","09:00" => "09:00","10:00" => "10:00","11:00" => "11:00","12:00" => "12:00","13:00" => "13:00","14:00" => "14:00","15:00" => "15:00","16:00" => "16:00","17:00" => "17:00","18:00" => "18:00","19:00" => "19:00","20:00" => "20:00","21:00" => "21:00","22:00" => "22:00","23:00" => "23:00");
        $hou = array("1h" => "1h","2h" => "2h","3h" => "3h","4h" => "4h","5h" => "5h","6h" => "6h","7h" => "7h","8h" => "8h","9h" => "9h","10h" => "10h","11h" => "11h","12h" => "12h","13h" => "13h","14h" => "14h","15h" => "15h","16h" => "16h","17h" => "17h","18h" => "18h","19h" => "19h","20h" => "20h","21h" => "21h","22h" => "22h","23h" => "23h");
        $day = array("1天" => "1天","2天" => "2天","3天" => "3天","4天" => "4天","5天" => "5天","6天" => "6天","7天" => "7天","8天" => "8天","9天" => "9天","10天" => "10天","11天" => "11天","12天" => "12天","13天" => "13天","14天" => "14天","15天" => "15天");
        $getid = Request::instance()->get('id'); 
        $result =DB::field('a.*,b.name,c.start_id,c.end_id')
                    ->table('ct_shift')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.companyid')
                    ->join('ct_already_city c','c.city_id=a.linecityid')
                    ->where('sid',$getid)
                    ->find();
        $linestart = $this->start_city($result['start_id']);
        $lineend = $this->start_city($result['end_id']);
        $result['linestart'] = $linestart;
        $result['lineend'] = $lineend;
        $result['free'] = DB::table('ct_shiftfree')
                            ->where('shiftid',$result['sid'])
                            ->select();
        $result['shifa'] = detailadd($result['beginprovinceid'],$result['begincityid'],$result['beginareaid']) . $result['beginaddress'];
        $result['zhongdiancan'] = detailadd($result['endprovinceid'],$result['endcityid'],$result['endareaid']) . $result['endaddress'];
        
        $this->assign('arr',$arr);
        $this->assign('tim',$tim);
        $this->assign('hou',$hou);
        $this->assign('day',$day);
        $this->assign('list',$result);
        return view('shift/uptemplate');
    }

    public function shiftlog(){
        $search = input('search');
        $where['b.delstate'] =1;
        $where['c.status'] =1;
        $where['a.status'] =['IN','1,2'];
        $stime = input('starttime');
        $etime = input('endtime');
        $pageParam    = ['query' =>[]];
        if (!empty($search)) {
            $where['b.shiftnumber|c.name'] = ['like','%'. $search.'%'];
            $pageParam['query']['search'] = $search;
        }
        if (!empty($stime) && !empty($etime)) {
          $endtime = strtotime(trim($etime).'23:59:59');
          $starttime = strtotime(trim($stime).'00:00:00');
        
          $where['a.deptime'] = array(array('EGT',$starttime),array('ELT', $endtime));
          $pageParam['query']['starttime'] = $stime;
          $pageParam['query']['endtime'] = $etime;
          
      }
        $result = DB::field('b.shiftnumber,b.delstate,b.linecityid,a.slid,a.deptime,a.endtime,a.status,c.name')
                    ->table('ct_shift_log')
                    ->alias('a')
                    ->join('ct_shift b','b.sid = a.shiftid')
                    ->join('ct_company c','c.cid=b.companyid')
                    ->where($where)
                    ->order(array('a.deptime'=>'desc','a.slid'=>'desc'))
                    ->paginate(10,false, $pageParam);
        $array = array();
        foreach ($result  as $value) {
            $arr = DB::table('ct_already_city')->where('city_id',$value['linecityid'])->find();
            $value['start'] = $this->start_city($arr['start_id']);
            $value['end'] = $this->start_city($arr['end_id']);
            $array[] = $value;
        }
        $page = $result->render();
       
        $this->assign('page',$page);
        $this->assign('list',$array);
        return view('shift/shiftlog');
    }

    public function checknumber(){
        $get_post = Request::instance()->post();
       $shift = DB::table('ct_shift')
                ->where(array('shiftnumber'=>$get_post['name'],'whethertoopen'=>1,'delstate'=>1,'shiftstate'=>'1'))
                ->find();
        if (empty($shift)) {
           return true;

        }else{
            return false;
        }
    }

    /*
     * 中转线路列表
     * */
    public function connecting(){

    }

    /*
     * 添加中转线路
     * */
    public function addconnect(){
        return $this->fetch();

    }

    public function saveconnect(){
        $dataconnect = Request::instance()->post();
        $startline = $dataconnect['startline'];
        $endline = $dataconnect['endline'];
        print_r($dataconnect);

        if (empty($startline) || empty($endline)){
            $this->error('班次线路不能为空');
        }
//        $startcityid = substr($startline,0,strlen($startline)-(strrpos($startline,'-')+1));
//        $endcityid = substr($endline,-stripos($endline,'-'));
//
//        var_dump($startcityid);
//        var_dump($endcityid);
//        $transit = $dataconnect['transit'];

//        $startcity = $this->line($startcityid,$transit);
//        $endcity = $this->line($startcityid,$endcityid);
         $startcity = Db::table('ct_shift')->where('sid',$startline)->find();
         $endcity = Db::table('ct_shift')->where('sid',$endline)->find();
//        echo  '<pre/>';
//        print_r($startcity);
//        echo '<pre/>';
//        print_r($endcity);

        $newline = [];
        //组合线路
        //每公斤价格
        $newline['price'] = ($startcity['price'] + $endcity['price']) * 0.9;
        //时效
        $newline['trunkaging'] = $startcity['trunkaging'] + $endcity['trunkaging'];
        //班次号
        $newline['shiftnumber'] = $dataconnect['ShiftNumber'];
        //开始城市
        $newline['begincityid'] = $startcity['begincityid'];
        //结束城市
        $newline['endcityid'] = $endcity['endcityid'];
        //折扣
        $newline['discount'] = 1;
        //最低收费
        $newline['lowprice'] = $startcity['lowprice'] > $endcity['lowprice'] ? $startcity['lowprice'] : $endcity['lowprice'];
        //抛货标准价
        $newline['eprice'] = ($startcity['price'] + $endcity['price']) * 0.9*1000/2.5;
        //公司id
        $companyid = Db::table('ct_company')->field('cid')->where('name',$dataconnect['name'])->find();

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

        $newline['aid'] = $this->uid;

        $newline['addtime'] = time();

        $newline['whethertoopen'] = $dataconnect['whethertoopen'];

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
        $newline['transit'] = $dataconnect['transit'];
        //是否中转
        $newline['istransit'] = 2;
        $deptime = Db::table('ct_shift_log')->where(array('shiftid'=>$startline,'status'=>1))->find();
        $startime = $deptime['deptime'] ;
        $endtime = $deptime['deptime'] + ($startcity['trunkaging'] + $endcity['trunkaging'])*24*60*60;
        $a = date('w',strtotime(date('Y-m-d',$endtime)));
        $weekarray=["周日","周一","周二","周三","周四","周五","周六"];

        $newline['arrivewin'] =  $weekarray[$a];
        // 查询干线起点城市对应的提货区域
        $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$startcity['companyid'],'type'=>1,'province'=>$startcity['begincityid']))->find();

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


        if ($insert){
            $this->success('添加成功','shift/index');
        }

    }

    /*
     * 查看线路详情
     * */
    public function connectview(){

    }

    /*
     * 干线填写模板
     * */
    public function informention(){
        return $this->fetch();
    }
    /*
     * 查看发出城市的所有线路
     * */
    public function startline(){
        $start_city =  Request::instance()->post()['start_city'];
        $end_city = '';
        // 零担
        $data['line'] = $this->line_price($start_city, $end_city);

//        return json(['code'=>'1002','message'=>'查询成功','data'=>$data]);
        return $data;
    }

    /*
     * 查看到达城市的所有线路
     * */
    public function endline(){
        $end_city =  Request::instance()->post()['end_city'];
        $start_city = '';
        // 零担
        $data['line'] = $this->line_line($start_city, $end_city);

//        return json(['code'=>'1002','message'=>'查询成功','data'=>$data]);
        return $data;
    }

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
                        ->field('s.*,g.slid,g.deptime')
                        ->where($condition1)
                        ->where('linecityid',$value)
                        ->where('shiftstate',1)
                        ->where('istransit',1)
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
                    $list[$key]['end_city'] = addresidToName($value['endcityid']);


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
    public function line_line($start_id, $end_id){
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

            if ($start_id){
                $result = Db::table("ct_already_city")->field('city_id')->where(array('start_id' => $start_id,'end_id'=>$end_id))->find();
            }else{
                $result1 = Db::table("ct_already_city")->field('city_id')->where(array('end_id' => $end_id))->select();
                $result =$this->multiToSingle($result1);
            }
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
                if(empty($start_id)){
                    foreach ($result as $key =>$value){
                        $line1 = Db::table("ct_shift")
                            ->alias('s')
                            ->join('__SHIFT_LOG__ g','g.shiftid = s.sid')
                            ->field('s.sid,s.begincityid,s.endcityid,s.picktype,s.sendtype,s.picksite,s.stime,s.sphone,s.sendsite,s.dtime,s.tphone,s.companyid,s.shiftnumber,s.trunkaging,s.price,s.timestrat,s.timeend,s.lowprice,s.shiftstate,s.pmoney,s.smoney,s.weekday,s.discount,g.slid,g.deptime')
                            ->where($condition1)
                            ->where('linecityid',$value)
                            ->where('shiftstate',1)
                            ->where('istransit',1)
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
                        $list[$key]['start_city'] = addresidToName($value['begincityid']);
                        // 返回终点城市名称
                        $list[$key]['end_city'] = $end_city;

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

    public function line($start_id,$end_id){


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

            // 查询开通的干线城市
            $result = Db::table("ct_already_city")->field('city_id')->where(array('start_id' => $start_id,'end_id'=>$end_id))->find();
            // 查询条件 计划发车时间 在七天以内的
            $condition['g.deptime'] = array(array('gt',$begintoday),array('lt', $endtoday));
            // 查询条件 状态（1进行中 2已完成 3一直在）
            $condition['g.status'] = ['IN','1,3'];

            // 查询条件 已开通城市 线路ID
	     	$condition['s.linecityid'] = $result['city_id'];
            // 起点城市
            $start_city = addresidToName($start_id);
            // 终点城市
            $end_city = addresidToName($end_id);
            $list = array();
            // 如果有此线路则查询对应的班次信息
            if(!empty($result)) {
                foreach ($result as $key => $value) {
                    $line = Db::table("ct_shift")
                        ->alias('s')
                        ->join('__SHIFT_LOG__ g', 'g.shiftid = s.sid')
                        ->field('s.sid,s.picktype,s.sendtype,s.picksite,s.stime,s.sphone,s.sendsite,s.dtime,s.tphone,s.companyid,s.shiftnumber,s.trunkaging,s.price,s.timestrat,s.timeend,s.lowprice,s.shiftstate,s.pmoney,s.smoney,s.weekday,s.discount,g.slid,g.deptime')
                        ->where($condition)
                        ->where('shiftstate',1)
                        ->order($order)
                        ->select();
                }
                return $line;
            }


    }

}