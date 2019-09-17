<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Carriers  extends Base
{
	function __construct(){
        parent::__construct();
        $this->uid = Session::get('admin_id','admin_mes');
        $this->if_login();
    }
    // 承运商公司列表 
    public function index(){
        $search = input('search');
        $pageParam = ['query' =>[]];
        if(!empty($search)){
           $where['name'] = ['like','%'.$search.'%'];
           $pageParam['query']['search'] = $search;
        }
        // 公司状态 1开通 2删除
        $where['status'] = 1;
        // 公司类型 1干线 2提货 3项目
        $where['type'] = ['in','1,2'];

        $uid= $this->uid;
        $grade = Db::table('ct_admin')->field('grade')->where('aid',$uid)->find();
        if($grade['grade'] == 1){
            $select = DB::table('ct_company')->where($where)->order('cid','desc')->paginate(10,false, $pageParam);
        }else{
            $select = DB::table('ct_company')->where($where)->where('aid',$uid)->order('cid','desc')->paginate(10,false, $pageParam);
        }
        // 查询所有的承运公司

        $data_result = $select->toArray();

        $arr = array();
        foreach ($data_result['data'] as  $value) {

            // 司机类型 1司机 2调度 3管理
            $bosswhere['type'] = 3; 
            // 司机状态 1正常 2 删除
            $bosswhere['delstate'] = 1;
            // 公司id 
            $bosswhere['companyid'] = $value['cid'];
            // 管理员
            $boss = DB::table('ct_driver')->where($bosswhere)->find();
            $value['realname'] = $boss['realname'];
            $value['username'] = $boss['username'];
            $value['sex'] = $boss['sex'];
            $value['mobile'] = $boss['mobile'];
            // 提货区域
            $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['cid'],'type'=>1))->select();
            // 配送区域
            $pei_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['cid'],'type'=>2))->select();
            // 干线区域
            $shift_arr = DB::field('a.shiftnumber,b.start_id,b.end_id')
                            ->table('ct_shift')
                            ->alias('a')
                            ->join('ct_already_city b','b.city_id=a.linecityid')
                            ->where(array('companyid'=>$value['cid'],'delstate'=>1))
                            ->select();
            foreach ($ti_arr as $tval) {
                $tval['province'] = detailadd($tval['province'],'','');
                $value['ti'][] = $tval;
            }
            foreach ($pei_arr as $pval) {
                $add_arr = DB::table('ct_addressinfo')->where(array('inid'=>$pval['addid']))->select();
                if (!empty($add_arr)) {
                    foreach ($add_arr as $can) {
                        $can['can_address'] = detailadd($can['provinceid'],$can['cityid'],$can['areaid']).$can['address'];
                        $pval['can'][] = $can;
                    }
                }
                $pro_pei = DB::table('ct_district')->where('id',$pval['province'])->find();
                $pval['province'] = $pro_pei['name'];
                $value['pei'][] = $pval;

            }
            foreach ($shift_arr as $key => $valsf) {
                $startline = $this->start_city($valsf['start_id']);
                $endline = $this->start_city($valsf['end_id']);
                $valsf['shifstartline'] = $startline;
                $valsf['shifendline'] = $endline;
                $value['shift'][] = $valsf;
            }
            $arr[]=$value;
        }       
    	$page =  $select->render();
        $this->assign('page',$page);
    	$this->assign('list',$arr);
    	return view('carriers/index');
    }
    // 承运商公司详情
    public function detail() {
        $cid = input('cid'); // 公司id
        // 查找公司
        $select  = DB::table('ct_company')->where('cid',$cid)->find();
        // 查找公司员工
        $driver = DB::table('ct_driver')
            ->where(array('companyid'=>$cid,'delstate'=>1))
            ->order('type','desc')
            ->select();
        // 公司地址
        $select['address'] = detailadd($select['provinceid'],$select['cityid'],$select['areaid']).$select['address'];
        // 查找公司车辆
        $car = DB::table('ct_carcategory')->where('com_id',$cid)->select();
        // 提货区域
        $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$cid,'type'=>1))->select();
        foreach ($ti_arr as $key => $tval) {
            $ti_arr[$key]['city'] = detailadd($tval['province'],'','');
        }
        // 配送区域
        $pei_arr = DB::table('ct_tpprice')->where(array('companyid'=>$cid,'type'=>2))->select();
        foreach ($pei_arr as  $key => $pval) {
            $add_arr = DB::table('ct_addressinfo')->where(array('inid'=>$pval['addid']))->select();
            if (!empty($add_arr)) {
                foreach ($add_arr as $can) {
                    $can['can_address'] = detailadd($can['provinceid'],$can['cityid'],$can['areaid']).$can['address'];
                    $pval['can'][] = $can;
                }
            }
            $pro_pei = DB::table('ct_district')->where('id',$pval['province'])->find();
            $pei_arr[$key]['city'] = $pro_pei['name'];
        }
        // 干线区域
        $shift_arr = DB::field('a.*,b.start_id,b.end_id')
                        ->table('ct_shift')
                        ->alias('a')
                        ->join('ct_already_city b','b.city_id=a.linecityid')
                        ->where(array('companyid'=>$cid))
                        ->select();  
        foreach ($shift_arr as $key => $valsf) {
            $startline = $this->start_city($valsf['start_id']);
            $endline = $this->start_city($valsf['end_id']);
            $shift_arr[$key]['shifstartline'] = $startline;
            $shift_arr[$key]['shifendline'] = $endline;
        }

        $select['driver'] =  $driver;
        $select['car'] =  $car;
        $select['tiArr'] =  $ti_arr;
        $select['peiArr'] =  $pei_arr;
        $select['shiftArr'] =  $shift_arr;

        $this->assign('list',$select);
        return view('carriers/detail');
    }
    
    /**
     * to 添加承运商页面
     * @Auther: 李渊
     * @Date: 2018.8.7
     * @return [type] [description]
     */
    public function addcarriers()
    {
    	return view('carriers/addcarriers');
    }
    
    /**
     * to 修改承运商页面
     * @Auther: 李渊
     * @Date: 2018.8.7
     * @return [type] [description]
     */
    public function updatecarriers(){
    	$cid = input('cid');
        // 查找公司
        $select  = DB::table('ct_company')->where('cid',$cid)->find();
        // 设置地址
        $select['address'] = detailadd($select['provinceid'],$select['cityid'],$select['areaid']).$select['address'];
        // 渲染视图
    	$this->assign('list',$select);
    	return view('carriers/updatecarriers');
    }
    
    /**
     * 添加承运公司
     * @Auther: 李渊
     * @Date: 2018.8.7
     * @return [type] [description]
     */
    public function addmessage()
    {
    	$postdate = Request::instance()->post();
        // 公司名称
    	$compay_data['name'] = $postdate['name'];
        // 公司地址
        if ($postdate['province'] !='0' &&  $postdate['province'] !='') {
            $compay_data['provinceid'] = $postdate['province'];
            $compay_data['areaid'] = $postdate['area'];
            $compay_data['cityid'] = $postdate['city'];
            $compay_data['address'] = $postdate['addinfo'];
        }
        // 公司类型：1干线 2提货 3项目
    	$compay_data['type'] = $postdate['type'];
        // 公司添加时间
        $compay_data['addtime'] =time();
        $compay_data['aid'] = $this->uid;
        // 添加公司
        $compayid = Db::table('ct_company')->insertGetId($compay_data);
        // 判断是否添加成功
    	if($compayid){
            $content = "添加了 ".$postdate['name'];
            $this->hanldlog($this->uid,$content);
    		$this->success("添加成功",'carriers/index');
    	}else{
    		$this->error("添加失败!!");
    	}
    }


    /**
     * 修改承运公司
     * @Auther: 李渊
     * @Date: 2018.8.7
     * @return [type] [description]
     */
    public function updateMessage()
    {
        $postdate = Request::instance()->post();
        // 公司名称
        $compay_data['name'] = $postdate['name'];
        // 公司地址
        if ($postdate['province'] !='0' &&  $postdate['province'] !='') {
            $compay_data['provinceid'] = $postdate['province'];
            $compay_data['areaid'] = $postdate['area'];
            $compay_data['cityid'] = $postdate['city'];
            $compay_data['address'] = $postdate['addinfo'];
        }
        // 公司类型：1干线 2提货 3项目
        $compay_data['type'] = $postdate['type'];
        // 添加公司
        $compayid = Db::table('ct_company')->where('cid',$postdate['companyid'])->update($compay_data);
        // 判断是否添加成功
        if($compayid){
            $content = "修改 ".$postdate['name'];
            $this->hanldlog($this->uid,$content);
            $this->success("修改成功",'carriers/index');
        }else{
            $this->error("修改失败!!");
        }
    }

    // 承运商公司删除
    public function delcom(){
        $get = Request::instance()->get();
        // 承运公司 2 删除
        $delcom = DB::table('ct_company')->where('cid',$get['cid'])->update(array('status'=>2));
        //删除公司车辆信息
        $del = Db::table('ct_carcategory')->where('com_id',$get['cid'])->find();
        if (!empty($del)) {
            $delcar = Db::table('ct_carcategory')->where('com_id',$get['cid'])->delete();
        }
        
        // 承运公司所有员工 2 删除
        $deldriver = DB::table('ct_driver')->where('companyid',$get['cid'])->update(array('delstate'=>2));
        // 所有提货配送干线班次删除
        Db::table('ct_shift')->alias('a')->join('ct_shift_log l','l.shiftid=a.sid')->where('companyid',$get['cid'])->update(array('a.delstate'=>2,'a.whethertoopen'=>2,'l.status'=>2));
        // 
        if($deldriver || $delcom){
            $this->success('删除成功', 'carriers/index');
        }else{
            $this->error('删除失败');
        }
    }
    /*
    *
    *添加干线
    */
    public function carraddshift(){
        $cid = input('cid');
        $arr = array("周一" => "周一","周二" => "周二","周三" => "周三","周四" => "周四","周五" => "周五","周六" => "周六","周日" => "周日");
        $this->assign('arr',$arr);
        $this->assign('cid',$cid);
        return view('carriers/carraddshift');
    }

    /*
    *
    *添加班次
    */
    public function addshiftmess(){
        $postdate = Request::instance()->post();
        $shif_data['shiftnumber'] = $postdate['ShiftNumber'];
        $shif_data['companyid'] = $postdate['cid'];
       
        $shif_data['eprice'] = $postdate['Eprice'];
        
        $shif_data['addtime'] = time();
        $shif_data['timestrat'] = $postdate['TimeStrat'];
        $shif_data['timeend'] = $postdate['TimeEnd'];
        $shif_data['selfdeliverydeadline'] = $postdate['SelfDeliveryDeadline'];
       if($postdate['sheng'] !='0' ) {
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
        if ($postdate['sheng1'] !='0'  ) {
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
        
            if ($postdate['tpro'] =='0' &&  $postdate['ppro'] =='0') {
                $this->error('请选择始发和终点城市!!');
            }
            //if ($postdate['tarea'] =='0') {
                $shifa = $postdate['tcity'];
           // }else{
             //   $shifa = $postdate['tarea'];
            //}
            // if ($postdate['parea'] =='0') {
                $zhogndian = $postdate['pcity'];
            //}else{
            //    $zhogndian = $postdate['parea'];
            //}
            //echo $shifa;exit();
            /*if ($postdate['tpro']=='1' || $postdate['tpro']=='2'||$postdate['tpro']=='9'||$postdate['tpro']=='22') {
                $shifa = $postdate['tpro'];
            }else{
                $shifa = $postdate['tcity'];
            }
            if ($postdate['ppro']=='1' ||$postdate['ppro']=='2'||$postdate['ppro']=='9'||$postdate['ppro']=='22') {
                $zhogndian = $postdate['ppro'];
            }else{
                $zhogndian = $postdate['pcity'];
            }*/

            
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
       
        
        $shif_data['whethertoopen'] = $postdate['whethertoopen'];
        $shif_data['freetonnage'] = $postdate['FreeTonnage'];
        $shif_data['linecityid'] = $city_id;
        
        $array = array();
        $arr_price1 = $postdate['mytext1'];
        $arr_price2 = $postdate['mytext2'];
        $arr_price3 = $postdate['mytext3'];
        //干线运价
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
            $content = "添加了编号为 ".$postdate['ShiftNumber']." 班次";
            $this->hanldlog($this->uid,$content);
             $this->success('新增成功', 'carriers/index');
             exit();
        }else{
            $this->error('新增失败');
            exit();
        }
    }

    /*
    *
    *添加周边提配区域
    */
    public function addround(){
        $this->assign('cid',input('id'));
        return view('carriers/addround');
    }
    /*
    *
    *添加周边提货城市提交动作
    */
    public function updateround(){
        $postdate = Request::instance()->post();
        if ($postdate['tpro'] =='0') {
            $res['code']=false;
            $res['message']='请选择提货城市!';
        }
        $pickcity = $postdate['roundcity'];
        $com = $postdate['comid'];
        $arr_city = array();
        $arr = array();
        $sel_city = array();
        if (isset($postdate['selcity'])) {
            foreach ($postdate['selcity'] as $key => $value) {
                $sel_city[$key]['cityid'] = $value['cityid'];
                $sel_city[$key]['areaid'] = $value['areaid'];
            }
        }
        foreach ($pickcity as $key => $value) {
            if ($value['ppro'] !='0') {
                $arr[$key]['cityid'] = $value['pcity'];
                $arr[$key]['areaid'] = $value['parea'];
            }
            
        }
        $list = array_merge($sel_city,$arr);
        $arr_city[][$postdate['tcity']] = $list;
        //$list =  json_encode($arr_city); 
        
        $real = json_encode($arr_city); 
        $result = Db::table('ct_company')->where('cid',$com)->update(array('pickround'=>$real));
        if ($result) {
            $res['code']=true;
            $res['message']='添加成功';
        }else{
            $res['code']=false;
            $res['message']='添加失败！重新添加!';
        }
        echo json_encode($res);
    }
    /*
    *返回选择提货城市下的周边城市
    */
    public function roundcity(){
        $postdate = Request::instance()->post();
        
        $array = array();
        $com = $postdate['companyid'];
        $pickid = $postdate['pickid'];

        $result = Db::table('ct_company')->where(array('cid'=>$com))->find();
        $pickround = json_decode($result['pickround'],TRUE);
        $i=0;
        if (!empty($pickround)) {
            foreach ($pickround as $value) {
                foreach ($value as $k => $val) {
                    if ($k == $pickid) {
                        foreach ($val as $ke => $v) {
                            $array[$ke]['cityid'] = $v['cityid'];
                            $array[$ke]['cityname'] = detailadd($v['cityid'],'','');
                            $array[$ke]['areaid'] = $v['areaid'];
                            $array[$ke]['areaname'] = detailadd($v['areaid'],'','');
                        }
                    }
                    
                }
                
            }
        }
    //print_r($array);
        echo json_encode($array);
    }

}