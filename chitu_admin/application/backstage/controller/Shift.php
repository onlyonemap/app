<?php
/*
*author:chenwei
*/
namespace app\backstage\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Shift extends Base
{
	function __construct(){
        parent::__construct();
        $this->uid = Session::get('carrier_id','carrier_mes');
        $company_id = Db::field('a.companyid,c.name,a.username')->table('ct_driver')->alias('a')->join('ct_company c','c.cid=a.companyid')->where('drivid',$this->uid)->find();
        $this->comid = $company_id['companyid'];
        $this->comname = $company_id['name'];
        $this->uname = $company_id['username'];
        $this->if_login();

    }
    public function index(){
        $search = input('search');
        $where['a.companyid'] = $this->comid;
        $where['a.delstate'] =1;
        $where['b.status'] =1;
        $pageParam    = ['query' =>[]];
        if (!empty($search)) {
            $where['shiftnumber'] = ['like','%'. $search.'%'];
            $pageParam['query']['search'] = $search;
        }
        
    	$result = DB::field('a.*,b.name')
                    ->table('ct_shift')
                    ->alias('a')
                    ->join('ct_company b','b.cid=a.companyid')
                    ->where($where)
                    ->order('a.sid','desc')
                    ->paginate(10,false, $pageParam);
    	$array = array();
    	foreach ($result  as $value) {
            $arr = DB::table('ct_already_city')->where('city_id',$value['linecityid'])->find();
    		$value['start'] = detailadd($arr['start_id'],'','');
    		$value['end'] = detailadd($arr['end_id'],'','');
			$array[] = $value;
    	}
    	
        $page = $result->render();
       
        $this->assign('page',$page);
    	$this->assign('list',$array);
    	return view('shift/index');
    }
    public function addshift(){
    	

    	$arr = array("周一" => "周一","周二" => "周二","周三" => "周三","周四" => "周四","周五" => "周五","周六" => "周六","周日" => "周日");
    	$this->assign('arr',$arr);
    	return view('shift/addshift');
    }
    /*
    *
    *添加班次
    */
    public function addmessage(){
        $postdate = Request::instance()->post();
        $shif_data['shiftnumber'] = $postdate['ShiftNumber'];
        $shif_data['companyid'] =  $this->comid;
        $shif_data['price'] = $postdate['Price'];
        $shif_data['eprice'] = $postdate['Eprice'];
        $shif_data['residualweight'] = $postdate['ResidualWeight'];
        $shif_data['residualbearing'] = $postdate['ResidualBearing'];
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
        
        
        $shif_data['transfer'] = $postdate['transfer'];
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
            if ($postdate['tpro']=='1' || $postdate['tpro']=='2'||$postdate['tpro']=='9'||$postdate['tpro']=='22') {
                $shifa = $postdate['tpro'];
            }else{
                $shifa = $postdate['tcity'];
            }
            if ($postdate['ppro']=='1' ||$postdate['ppro']=='2'||$postdate['ppro']=='9'||$postdate['ppro']=='22') {
                $zhogndian = $postdate['ppro'];
            }else{
                $zhogndian = $postdate['pcity'];
            }
            
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
        $shiftid = Db::table('ct_shift')->insertGetId($shif_data);
        $array = array();
        $arr_price1 = $postdate['mytext1'];
        $arr_price2 = $postdate['mytext2'];
        $arr_price3 = $postdate['mytext3'];
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
        $date_log['tonnage'] = $postdate['ResidualWeight'];
        $date_log['volume'] = $postdate['ResidualBearing'];
        $date_log['shiftid'] = $shiftid;
        Db::table('ct_shift_log')->insert($date_log);
        if($shiftid){
            $content = $this->comname."的 ".$this->uname." 添加了编号为 ".$postdate['ShiftNumber']." 班次";
            $this->hanldlog($this->uid,$content);
             $this->success('新增成功', 'shift/index');
             exit();
        }else{
            $this->error('新增失败');
            exit();
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
    	
    	$shif_data['shiftnumber'] = $postdate['ShiftNumber'];
    	$shif_data['companyid'] = $this->comid;
    	$shif_data['price'] = $postdate['Price'];
        $shif_data['eprice'] = $postdate['Eprice'];
    	$shif_data['residualweight'] = $postdate['ResidualWeight'];
    	$shif_data['residualbearing'] = $postdate['ResidualBearing'];
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
        
        
        $shif_data['transfer'] = $postdate['transfer'];
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
            if ($postdate['tpro']=='1' || $postdate['tpro']=='2'||$postdate['tpro']=='9'||$postdate['tpro']=='22') {
                $shifa = $postdate['tpro'];
            }else{
                $shifa = $postdate['tcity'];
            }
            if ($postdate['ppro']=='1' ||$postdate['ppro']=='2'||$postdate['ppro']=='9'||$postdate['ppro']=='22') {
                $zhogndian = $postdate['ppro'];
            }else{
                $zhogndian = $postdate['pcity'];
            }
            
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
    	$shiftid = Db::table('ct_shift')->insertGetId($shif_data);
    	$array = array();
    	$arr_price1 = $postdate['mytext1'];
    	$arr_price2 = $postdate['mytext2'];
    	$arr_price3 = $postdate['mytext3'];
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
        $date_log['tonnage'] = $postdate['ResidualWeight'];
        $date_log['volume'] = $postdate['ResidualBearing'];
        $date_log['shiftid'] = $shiftid;
        Db::table('ct_shift_log')->insert($date_log);
    	if($shiftid){
            $content = $this->comname."的工作人员 ".$this->uname." 添加了编号为 ".$postdate['ShiftNumber']." 班次";
            $this->hanldlog('',$content);
    		 $this->success('新增成功', 'shift/index');
    		 exit();
    	}else{
    		$this->error('新增失败');
    		exit();
    	}
    }
   
    //删除动作
    public function delcom(){
    	$get = Request::instance()->get();
    	if($get['del'] == 1){
           $delcom = DB::table('ct_shift')->where('sid',$get['id'])->update(array('whethertoopen'=>2));
           if($delcom){
                DB::table('ct_shift_log')->where('shiftid',$get['id'])->update(array('status'=>2));
                $content = $this->comname."的工作人员 ".$this->uname." 关闭了ID为".$get['id']."班次信息";
                $this->hanldlog('',$content);
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
                $date_log['tonnage'] = $select['residualweight'];
                $date_log['volume'] = $select['residualbearing'];
                $date_log['shiftid'] = $select['sid'];
                Db::table('ct_shift_log')->insert($date_log);
                $content = $this->comname."的工作人员 ".$this->uname." 开启了ID为".$select['sid']."班次信息";
                $this->hanldlog('',$content);
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
           if($delcom){
                $content = $this->comname."的工作人员".$this->uname."删除了ID为".$get['id']."班次信息";
                $this->hanldlog('',$content);
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
    	$linestart = detailadd($result['start_id'],'','');
    	$lineend = detailadd($result['end_id'],'','');
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
       
       
        //检索该修改班次是否修改了发车或者到车时间
        $search_shift = DB::field('shiftnumber,arrivewin,arrivetimestart,arrivetimeend,dewin,timestrat,timeend')
                        ->table('ct_shift')
                        ->where('sid',$postdate['sid'])
                        ->find();
        //修改班次号时候判断是否已重复存在
        if ($postdate['ShiftNumber'] != $search_shift['shiftnumber']) {
               $check_number = DB::table('ct_shift')->where(array('shiftnumber'=>$postdate['ShiftNumber']))->select();
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
            $date_log['tonnage'] = $postdate['ResidualWeight'];
            $date_log['volume'] = $postdate['ResidualBearing'];
            $date_log['shiftid'] = $postdate['sid'];
            Db::table('ct_shift_log')->insert($date_log);
        }
        $shif_data['shiftnumber'] = $postdate['ShiftNumber'];
        $shif_data['companyid'] = $this->comid;
        $shif_data['price'] = $postdate['Price'];
        $shif_data['eprice'] = $postdate['Eprice'];
        $shif_data['residualweight'] = $postdate['ResidualWeight'];
        $shif_data['residualbearing'] = $postdate['ResidualBearing'];
        $shif_data['edittime'] = time();
       
        $shif_data['selfdeliverydeadline'] = $postdate['SelfDeliveryDeadline'];
        if($postdate['sheng'] !='0' && $postdate['sheng'] !=''){
            $shif_data['beginprovinceid'] = $postdate['sheng'];
            $shif_data['begincityid'] = $postdate['shi'];
            $shif_data['beginareaid'] = $postdate['xian'];
            $shif_data['beginaddress'] = $postdate['beginAddress'];
        }
        $shif_data['transfer'] = $postdate['transfer'];
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
        }

        //直辖市中对应的区改为市
        $shifa = '';
        $zhogndian = '';
        if ($postdate['tpro'] !='0' && $postdate['tpro'] !='') {
             if ($postdate['tpro']=='1' || $postdate['tpro']=='2'||$postdate['tpro']=='9'||$postdate['tpro']=='22') {
                $shifa = $postdate['tpro'];
            }else{
                $shifa = $postdate['tcity'];
            }
            
            //$city_data['start_id'] = $shifa;
        }
       
        if ($postdate['ppro'] !='0' && $postdate['ppro'] !='') {
            if ($postdate['ppro']=='1' ||$postdate['ppro']=='2'||$postdate['ppro']=='9'||$postdate['ppro']=='22') {
                $zhogndian = $postdate['ppro'];
            }else{
                $zhogndian = $postdate['pcity'];
            } 
            
            //$city_data['end_id'] = $zhogndian;
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
       

        
        
        $shiftdate = Db::table('ct_shift')->where('sid',$postdate['sid'])->update($shif_data);
        $array = array();
        $arr_sfid = $postdate['sfid'];
        $arr_price1 = $postdate['mytext1'];
        $arr_price2 = $postdate['mytext2'];
        $arr_price3 = $postdate['mytext3'];
        $i=0;
        foreach ($arr_price1 as $key => $value) {
            $array[$i][]= $arr_sfid[$key];
            $array[$i][]= $arr_price1[$key];
            $array[$i][]= $arr_price2[$key];
            $array[$i][]= $arr_price3[$key];
            $i++;
        }
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
        if(isset($shiftdate)|| isset($shifdata)|| isset($shif) || isset($city_id)){
               
                $content = $this->comname."的工作人员 ".$this->uname." 修改了编号为 ".$postdate['ShiftNumber']." 班次";
                $this->hanldlog('',$content);
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
        $where['delstate'] =1;
        $where['b.status'] =1;
        $where['a.companyid'] = $this->comid;
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
                    $v['start'] = detailadd($arr['start_id'],'','');
                    $v['end'] = detailadd($arr['end_id'],'','');
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
        $linestart = detailadd($result['start_id'],'','');
        $lineend = detailadd($result['end_id'],'','');
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
        $where['b.companyid'] =$this->comid;
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
        $result = DB::field('b.shiftnumber,b.delstate,b.linecityid,a.slid,a.deptime,a.endtime,b.companyid,a.status,c.name')
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
            $value['start'] = detailadd($arr['start_id'],'','');
            $value['end'] = detailadd($arr['end_id'],'','');
            $array[] = $value;
        }
        $page = $result->render();
       
        $this->assign('page',$page);
        $this->assign('list',$array);
        return view('shift/shiftlog');
    }
     /*
    *
    *检查班次好是否重复
    */
    public function checknumber(){
        $get_post = Request::instance()->post();
       $shift = DB::table('ct_shift')
                ->where(array('shiftnumber'=>$get_post['name']))
                ->find();
        if (empty($shift)) {
           return true;

        }else{
            return false;
        }
    }

    /*
    *
    *地址表联动下拉数据
    */
    public function getaddress(){
        $getid = Request::instance()->get('id'); 
        $result =  DB::table('ct_district ')->where(array('parent_id'=>$getid))->select();
        //var_dump($result);
        
            switch ($getid)
            {
            case '1':
             return $opstr="[{id:'45052',name:'北京市'}]";
              break;  
            case '2':
              return $opstr="[{id:'45053',name:'天津市'}]";
              break;
            case '9':
              return $opstr="[{id:'45054',name:'上海市'}]";
              break;
            case '22':
              return $opstr="[{id:'45055',name:'重庆市'}]";
              break;
            default:
                $opstr="[";  
                foreach ($result as $key => $value) {
                   $opstr.="{id:'".$value['id']."',name:'".$value['name']."'},";  
                }
                $len=strlen($opstr)-1;  
                $opstr=substr($opstr,0,$len);  
                $opstr.="]"; 
                return $opstr;  
            }
    }



}