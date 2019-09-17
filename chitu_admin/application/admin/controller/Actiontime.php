<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Actiontime  extends Controller
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//
//  }
	public function timedtask(){
		$start_time = mktime(0,0,0,date("m",time()),date("d",time()),date("Y",time()));  //当天开始时间
		$end_time = mktime(23,59,59,date("m",time()),date("d",time()),date("Y",time())); //当天结束时间
		$where_data['deptime'] = array(array('EGT',$start_time),array('ELT', $end_time));
		//第二天12点时间错
		$endToday=mktime(12,0,0,date('m'),date('d')+2,date('Y'));
		Db::table('ct_shift_log')->where(array('status'=>'3'))->update(array('deptime'=>$endToday));
		$where_data['a.status'] = 1;
		$where_data['b.delstate'] = 1;
		$where_data['b.whethertoopen'] = 1;
		$where_data['b.shiftstate'] = 1;
		$shift_arr = DB::field('a.*,b.sid,b.residualweight,b.whethertoopen,b.shiftstate,b.residualbearing,b.dewin,b.arrivewin,b.trunkaging')
					->table('ct_shift_log')
					->alias('a')
					->join('ct_shift b','b.sid = a.shiftid')
					->where($where_data)
					->select();
		if(!empty($shift_arr)){
			foreach ($shift_arr as $key => $value) {
				$up_data['status'] =2;
				$up = DB::table('ct_shift_log')->where('slid',$value['slid'])->update($up_data);
				
				$arr_week = array("0" => "周日","1" => "周一","2" => "周二","3" => "周三","4" => "周四","5" => "周五","6" => "周六");
		        //获取到发车的周
		        $get_start_week = array_search($value['dewin'],$arr_week);
		        //获取到到车的周
		        $get_end_week = array_search($value['arrivewin'],$arr_week);
		        //发车队列具体发车时间
		        $deptime = $this->getTimeFromWeeks($get_start_week);
		        $deptime = strtotime("+1week",$deptime);
		        $aging = str_replace('天', '', $value['trunkaging']);
		        //发车队列具体到车时间
		        $endtime = strtotime("+$aging day",$deptime);
				$date_log['deptime'] = $deptime;
		        $date_log['endtime'] = $endtime;
		        $date_log['tonnage'] = $value['residualweight'];
		        $date_log['volume'] = $value['residualbearing'];
		        $date_log['shiftid'] = $value['sid'];
		        $getid = Db::table('ct_shift_log')->insertGetId($date_log);
		        $content = "定时任务添加了 ".$getid."的队列信息";
      			$this->hanldlogs('',$content);
			}
		}
		
	}


	public function index(){
		$data['phone'] = '11111111111';
		$data['realname'] = '定时任务';
		DB::table('ct_user')->insert($data);
	}


	public function test(){

		$where_data['whethertoopen'] = 1;
		$where_data['delstate'] = 1;
		$where_data['shiftstate'] = 1;
		$shift_arr = DB::table('ct_shift')
					->where($where_data)
					->select();
		if(!empty($shift_arr)){
			foreach ($shift_arr as $key => $value) {
				
				$arr_week = array("0" => "周日","1" => "周一","2" => "周二","3" => "周三","4" => "周四","5" => "周五","6" => "周六");
		        //获取到发车的周
		        $get_start_week = array_search($value['dewin'],$arr_week);
		        //获取到到车的周
		        $get_end_week = array_search($value['arrivewin'],$arr_week);
		        //发车队列具体发车时间
		        $deptime = $this->getTimeFromWeeks($get_start_week);
		        //$deptime = strtotime("+1week",$deptime);
		        $aging = str_replace('天', '', $value['trunkaging']);
		        //发车队列具体到车时间
		        $endtime = strtotime("+$aging day",$deptime);
				$date_log['deptime'] = $deptime;
		        $date_log['endtime'] = $endtime;
		        $date_log['tonnage'] = $value['residualweight'];
		        $date_log['volume'] = $value['residualbearing'];
		        $date_log['shiftid'] = $value['sid'];
		        $id = Db::table('ct_shift_log')->insertGetId($date_log);
		        
	        	 $content = "手动添加了 ".$id."的队列信息";
      			 $this->hanldlogs($this->uid,$content);
      			 
		        
			}
		}

		if ($id) {
			echo "success";
		}else{
			echo "fail";
		}
		
	}

	public function unorder(){
		$get_order = DB::table('ct_order')
						->alias('a')
						->field('a.*,b.tonnage,b.volume')
						->join('ct_shift_log b','b.slid=a.slogid')
						->where(array('paystate'=>['neq',2]))
						->select();
		foreach ($get_order as $key => $value) {
			$get_addtime = $value['addtime']+1800;
			$order_data['orderstate']=8;
			if ($value['addtime'] <= time()) {
				$up_order = DB::table('ct_order')->where('oid',$value['oid'])->update($order_data);
				$shift_data['tonnage'] = $value['totalweight']+$value['tonnage'];
				$shift_data['volume'] = $value['totalvolume']+$value['volume'];
				DB::table('ct_shift_log')->where('slid',$value['slogid'])->update($shift_data);
			}
		}
		//echo "<pre/>";
		//print_r($get_order);
	}

	public function getTimeFromWeeks($dayNum){
	    $curDayNum=date("w");
	   
	    if($dayNum>$curDayNum) $timeFlag="next ";
	    elseif($dayNum==$curDayNum) $timeFlag="";
	    else $timeFlag="next ";
	    $arryWeekDay = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	    $timeStamp=strtotime("$timeFlag"."$arryWeekDay[$dayNum]");
	    return $timeStamp;
	        
	}

	/**
     * 插入操作日记
     * allitems: 订单所有物品
     *author:chenwei
     */
	public function hanldlogs($uid='',$content){
		if ($uid !='') {
			$data['admin_id'] = $uid;
		}
       	
       	$data['content'] = $content;
       	$data['addtime'] = time();
       	DB::table('ct_log')->insert($data);
    }

    public function postjson(){
    	$arr = array(
    			'title'=>'highlight_string',
    			'list'=>'listji'
    		);
    	return json_encode($arr);
    }

    public function getmess(){
    	$input = input('list');
    	print_r(json_decode($input,true));
    }

    public function bakdatebase(){
    	import("Databasebc",EXTEND_PATH.'Databasebc');
        $databak = new \Databasebc(\think\Config::get("database"));
        $databak->backup();
        
    }
    public function getlocal(){
    	$start_str = '';
    	$startcity_str = "上海市";
    	$pick_add = "金沙江西路";
    	$start_action = bd_local($type='2',$startcity_str,$pick_add);//经纬度
    	
    	$start_str = $start_action['lat'].','.$start_action['lng'];
    	return $start_str;
    	//echo $start_str;
		//$end_action = bd_local($type='2',$endcity_str,$pei_add);//经纬
    }
    public function getendlocal(){
    	$end_str = '';
    	$endcity_str = "上海市";
    	$pick_add = array(array('address'=>"西藏北路"),array('address'=>"南京西路"));
    	foreach ($pick_add as $key => $value) {
    		$start_action = bd_local($type='2',$endcity_str,$value['address']);//经纬度
    		$end_str .= $start_action['lat'].','.$start_action['lng'].'|';
    	}
    	return rtrim($end_str,'|');
    }

    public function address($start,$end){
    	//$start = $this->getlocal();
    	//$end = $this->getendlocal();
    	$ak ="SdRptW2rs3xsjHhVhQOy17QzP6Gexbp6";
		//$url = "http://api.map.baidu.com/routematrix/v2/driving?output=json&origins=121.339267,31.244001&destinations=121.46678,31.236135|121.488551,31.277542&ak=".$ak;
		$url = "http://api.map.baidu.com/routematrix/v2/driving?output=json&origins=".$start."&destinations=".$end."&ak=".$ak;
		$renderOption =    file_get_contents($url);
		$result = json_decode($renderOption,true);
		//print_r($result);//exit();
		if ($result['status'] == '0') {
			$res = $result['result'];
		}else{
			$res=array();
		}
		$arr = array();
		if (!empty($res)) {
			for ($i=0; $i < count($res); $i++) { 
				$arr[$i] = $res[$i]['distance']['value'];
			}
		}
		return $arr;
    }
    public function getminline(){

    	$total = 0;
    	//起始点
    	$start_str = '';
    	$startcity_str = "上海市";
    	$pick_add = "金沙江西路地铁站";
    	$start_action = bd_local($type='2',$startcity_str,$pick_add);//经纬度
    	$start_str['start'] = $start_action['lat'].','.$start_action['lng'];
    	//配送点
    	$end_str = '';
    	$endcity_str = "上海市";
    	$pick_add = array(array('address'=>"西藏北路地铁站"),array('address'=>"南京西路地铁站"),array('address'=>"上海西站地铁站"),array('address'=>"上海南站地铁站"));
    	foreach ($pick_add as $key => $value) {
    		$start_action = bd_local($type='2',$endcity_str,$value['address']);//经纬度
    		$list[$key] = $start_action['lat'].','.$start_action['lng'];
 			$end_str .= $start_action['lat'].','.$start_action['lng'].'|';
    	}

    	$end_str = rtrim($end_str,'|');
    	$start_str['end']= $end_str;
    	$start_1 = $this->address($start_str['start'],$start_str['end']);
    	$t=min($start_1);
    	$brr=array_flip($start_1);
    	$min_key = $brr[$t];
    	$total = $t;
    	if (count($pick_add)>1) {
    		$num = $this->getlist($startcity_str,$min_key,$pick_add);
	    	$count_num = $this->array_search_key('count_finly',$num);
	    	
	    	$total = array_sum($count_num)+$t;
    	}
    	
    	$num2 = $this->getlist($startcity_str,0,$pick_add);
    	echo $total;
       $count_arr = $this->array_search_key('pick_adds',$num);
       $end = end($count_arr);
    	print_r($count_arr);
    	//echo $this->array_depth($num);
    }

    public function getlist($endcity_str,$min_key,$pick_add=array()){
    	//$endcity_str = "上海市";
    	$i=0;
    	$j=0;
    	$count_finly = 0;
    	$arr = array();
    	foreach ($pick_add as $key => $value) {
    		$start_action = bd_local($type='2',$endcity_str,$value['address']);//经纬度
    		if ($min_key == $key) {
    			unset($pick_add[$key]);
    			foreach ($pick_add as $val) {
    				$pick_adds[$i] = $val;
    				$i++;
    			}
    			
    			$str['start'] = $start_action['lat'].','.$start_action['lng'];
    		}else{
    			$str['end'][] =$start_action['lat'].','.$start_action['lng'];
    		}
    	}
    	$str['end']= implode('|',$str['end']);
    	$start_1 = $this->address($str['start'],$str['end']);
    	$t=min($start_1);
    	$brr=array_flip($start_1);
    	$min_keys = $brr[$t];
    	$list = array();
    	
    	if (count($pick_adds)>1) {
    		$list = $this->getlist($endcity_str,$min_keys,$pick_adds);
    	}
    	$arr['address'] = $pick_adds;
    	$count_finly =$t;
    	return array('arr'=>$arr,'pick_adds'=>$pick_adds,'list'=>$list,'count_finly'=>$count_finly);

    }
    public function array_search_key($needle, $haystack){ 
		global $nodes_found; 
		foreach ($haystack as $key1=>$value1) { 
		 if ($key1=== $needle){ 
		  $nodes_found[] = $value1;     
		   } 
		    if (is_array($value1)){    
		      $this->array_search_key($needle, $value1); 
		    } 
		} 
		return $nodes_found; 
	} 



    public function lists(){
    	$a = array(
				array(3),
				array(7),
				array(15),
		);
		$b = $a;
		$c = array();
		$arr = array();
		$co = count($b);
		foreach ($b as $key => $value) {
			$c[$key]['start'] = $value;
			foreach ($b as $k=> $val) {
				if ($val==$value) {
					continue;
				}else{
					$arr[$key] = $value;
				}
			}
			
			$c[$key]['end'] = $arr;
		}
		print_r($c);
		/*for($i=0; $i<count($b); $i++){
			sort($b[$i]);
			echo '第'.$i.'列 最小数='.$b[$i][0].' 最大数='.$b[$i][count($b[$i])-1].'<br>';
		}*/
		//$c = array();
		
    }
}