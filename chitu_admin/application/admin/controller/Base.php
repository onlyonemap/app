<?php
/**
 * 后台基类文件
 */
namespace app\admin\controller;
use think\Controller;
use think\Request; 
use think\Session;
use think\Db;  //使用数据库操作

class Base extends Controller
{
	function __construct(){
		parent::__construct();
		
	}

	//空操作
	public function _empty(){
		return view("error/index");

	}

	//并发执行，备注：使用控制器才可以使用此方法
	public  function _initialize(){
		$config = [

		    'auth_config' =>  ['auth_user'=>'admin'],
		];
		$auth=new \Auth\Auth(\think\Config::set($config));
//		 Config::set('auth_user',1,'auth_config');
		$request = Request::instance(); 
		$rule_name = $request->module() . '/' . $request->controller() . '/' . $request->action();
		$userid = Session::get('admin_id','admin_mes');
		$result=$auth->check($rule_name,$userid,1);
		
//		if(!$result){
//			$this->error('您没有权限访问');
//		}
//		echo "并发操作</br>";
		$this->quanxian();
	}
	protected function quanxian(){
		$uid = Session::get('admin_id','admin_mes');
		$cstr = strtolower(request()->controller().'/'.request()->action());
		$result = model('auth_rule')->field('name')->where('name','admin/'.$cstr)->find();
		$admin = model('admin')->field('qxz,admin')->where('aid',$uid)->find();
		if($result && $admin->admin!=5){
			$quanxian = model('authstr')->field('quanxianstr')->where('qid',$admin->qxz)->find();
			$qstr = strtolower($quanxian?$quanxian->quanxianstr:'');
			$res = strpos($qstr,$cstr);
			if(is_numeric($res)){
				return true;
			}else{
				if(request()->isAjax()){
					echo json_encode($this->returnArr(false,'','您没有权限操作',null));
					exit;
				}else{
					echo '<h1>您没有权限操作</h1>';
					exit;
				}

			}
		}else{
			return true;
		}

	}
	public function getTimeFromWeek($dayNum){
	    $curDayNum=date("w");
	   
	    if($dayNum>$curDayNum) $timeFlag="next ";
	    elseif($dayNum==$curDayNum) $timeFlag="";
	    else $timeFlag="next ";
	    $arryWeekDay = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	    $timeStamp=strtotime("$timeFlag"."$arryWeekDay[$dayNum]");
	    return $timeStamp;
	        
	}

	public function if_login(){
		$admin_id = Session::get('admin_id','admin_mes');
		if(empty($admin_id)){
			$this->redirect('Index/login');
		}
	}
	
	/**
	 * 通过id查找城市名称 如45054 返回上海市
	 * @param  [type] $searchid [城市 id]
	 * @return [type]           [城市 名称]
	 */
	public function start_city($searchid){
		$result =  DB::table('ct_district ')->where(array('id'=>$searchid))->find();
		return $result['name'];
	}
	
	/**
	 * 查找一对城市 如 45054 45052 返回 上海--北京
	 * @param  [type] $proid  [城市id]
	 * @param  [type] $cityid [城市id]
	 * @return [type]         [城市名称]
	 */
	public function start_end_city($proid,$cityid){
		$result1 =  DB::table('ct_district')->where(array('id'=>$proid))->find();
		$result2 =  DB::table('ct_district')->where(array('id'=>$cityid))->find();
		return $result1['name'] ."--". $result2['name'];
	}
	/*
	*二维数组去重
	*$proid 省ID
	*$cityid 市ID
	*author:chenwei
	*/
	public function more_array_unique($arr=array()){  
	    foreach($arr[0] as $k => $v){  
	        $arr_inner_key[]= $k;   //先把二维数组中的内层数组的键值记录在在一维数组中  
	    }  
	    foreach ($arr as $k => $v){  
	        $v =join(",",$v);    //降维 用implode()也行  
	        $temp[$k] =$v;      //保留原来的键值 $temp[]即为不保留原来键值  
	    }  
	   	 
	    $temp =array_unique($temp);    //去重：去掉重复的字符串  
	    foreach ($temp as $k => $v){  
	        $a = explode(",",$v);     
	        $arr_after[$k]= array_combine($arr_inner_key,$a);  //将原来的键与值重新合并  
	    }  
	     
	    return $arr_after;  
	}  
	

	/**
	 * 过滤物品重复提货点
	 * allitems:订单所有物品
	 * author:chenwei
	 */
	public function filteritme($items){
		$array = array();
		foreach ($items as $key => $val) {
	      	if (!in_array($items[$key]['taddressid'],$array)) {
	      		$items[$key]['taddressid'];
	      	} else {
	        	unset($items[$key]);
	      	}
	    } 
	    return $items;
	}

	/**
	 * 将二维数据降到一维
	 * $arr 二维数组
	 */
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

	/**
	 * 项目客户公司下所有用户ID
	 * companyid:公司ID
	 * author:chenwei
	 */
	public function getuseridstr($companyid){
		$user_com = DB::table('ct_user')->where('lineclient',$companyid)->SELECT();
       	foreach ($user_com as $key => $value) {
           $arr_uid[] = $value['uid'];
       	}
       	$userid_str = implode(',',$arr_uid);
       	return $userid_str;
	}

	/**
	 * 项目客户公司下所有下单的用户用户ID
	 * companyid:公司ID
	 * cityid:开通城市ID
	 * author:chenwei
	 */
	public function getlineuseridstr($companyid,$cityid){
		$user_com = DB::table('ct_user')
                    ->alias('a')
                    ->join('ct_order b','b.userid = a.uid')
                    ->join('ct_shift_log c','c.slid = b.slogid')
                    ->join('ct_shift d','d.sid = c.shiftid')
                    ->where(array('a.lineclient'=>$companyid,'d.linecityid'=>$cityid))
                    ->SELECT();
        foreach ($user_com as $key => $value) {
           $arr_uid[] = $value['uid'];
        }
        $userid_str = implode(',',$arr_uid);
        return $userid_str;
	}

 	/**
     * 过滤物品重复配送点
     * allitems: 订单所有物品
     * author:chenwei
     */
	public function filterdistribution($allitems){
       	$abr = array();
		foreach ($allitems as $key => $val) {
			if(!in_array($allitems[$key]['paddress'],$abr)){
				$abr[] = $allitems[$key]['paddress'];
			}else{
				unset($allitems[$key]);
			}
		} 
        return $allitems;
    }

    /**
     * 插入操作日记
     * allitems: 订单所有物品
     * author:chenwei
     */
	public function hanldlog($uid='',$content){
		if ($uid !='') {
			$data['admin_id'] = $uid;
		}
       	
       	$data['content'] = $content;
       	$data['addtime'] = time();
       	DB::table('ct_log')->insert($data);
    }

    /**
	 * 获取排序后的分类
	 * @param  [type]  $data  [description]
	 * @param  integer $pid   [description]
	 * @param  string  $html  [description]
	 * @param  integer $level [description]
	 * @return [type]         [description]
	 */
	public function getSortedCategory($data,$pid=0,$html="|---",$level=0)
	{
		$temp = array();
		foreach ($data as $k => $v) {
			if($v['cate_parent'] == $pid){
		
				$str = str_repeat($html, $level);
				$v['html'] = $str;
				$temp[] = $v;

				$temp = array_merge($temp,$this->getSortedCategory($data,$v['id'],'|---',$level+1));
			}
			
		}
		return $temp;
	}

	/**
	 * 格式化字节大小
	 * @param  number $size      字节数
	 * @param  string $delimiter 数字和单位分隔符
	 * @return string            格式化后的带单位的大小
	 * @author 
	 */
	function format_bytes($size, $delimiter = '') {
	    $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
	    for ($i = 0; $size >= 1024 && $i < 5; $i++) $size /= 1024;
	    return round($size, 2) . $delimiter . $units[$i];
	}

	function getFilesize($file,$digits = 2) {
       if (is_file($file)) {
               $filePath = $file;
               if (!realpath($filePath)) {
                       $filePath = $_SERVER["DOCUMENT_ROOT"].$filePath;
       }
           $fileSize = filesize($filePath);
               $sizes = array("TB","GB","MB","KB","B");
               $total = count($sizes);
               while ($total-- && $fileSize > 1024) {
                       $fileSize /= 1024;
                       }
               return round($fileSize, $digits)." ".$sizes[$total];
       }
       return false;
	}
    /**
     * 单文件上传
     * name：表单上传文件的名字
     * ext： 文件允许的后缀，字符串形式
     * path：文件保存目录
     */
    public function file_upload($name,$ext,$path){
    	$dir_path=ROOT_PATH.'/public/uploads/'.$path;
    	if (!is_dir($dir_path))mkdir($dir_path, 0777);// 使用最大权限0777创建文件
	    $file = request()->file($name);
	    $info = $file->move($dir_path,true,false);
	    if($info){
	        // 成功上传后 获取上传信息
	        $file_path = $info->getSaveName();
	        $data['file_path'] = '/uploads/'.$path.'/'.$info->getSaveName();
	    }else{
	        // 上传失败获取错误信息
	        $data['file_path'] =$file->getError();
	    }

	    return $data;
    }

	function time2Units ($time){
		$year = floor($time / 60 / 60 / 24 / 365);
		$time -= $year * 60 * 60 * 24 * 365;
		$month = floor($time / 60 / 60 / 24 / 30);
		$time -= $month * 60 * 60 * 24 * 30;
		$week = floor($time / 60 / 60 / 24 / 7);
		$time -= $week * 60 * 60 * 24 * 7;
		$day = floor($time / 60 / 60 / 24);
		$time -= $day * 60 * 60 * 24;
		$hour = floor($time / 60 / 60);
		$time -= $hour * 60 * 60;
		$minute = floor($time / 60);
		$time -= $minute * 60;
		$second = $time;
		$elapse = '';
		$unitArr = array('年' =>'year', '个月'=>'month', '周'=>'week', '天'=>'day',
		'小时'=>'hour', '分钟'=>'minute', '秒'=>'second'
		);
		foreach ( $unitArr as $cn => $u ){
			if ( $$u > 0 ){
				$elapse = $$u . $cn;
				break;
			}
		}
		return $elapse;
	}
	

	/**
     * 获取全部数据
     * @param  string $type  tree获取树形结构 level获取层级结构
     * @param  string $order 排序方式   
     * @return array         结构数据
     */
    public function getTreeData($type='tree',$order='',$name='name',$child='id',$parent='pid'){
    	$tree = new \Datatree\Datatree(); 
        // 判断是否需要排序
        if(empty($order)){
            $data=Db::table('ct_auth_rule')->select();
        }else{
            $data=Db::table('ct_auth_rule')->order($order)->select();
        }
        // 获取树形或者结构数据
        if($type=='tree'){
            $data=$tree->tree($data,$name,$child,$parent);
        }elseif($type="level"){
            $data=$tree->channelLevel($data,0,'&nbsp;',$child);
        }
        return $data;
    }

    /*
    *二维数组排序
    *@param $arrays 数组
    *@param $sort_key 排序的键值
    *@param $sort_order SORT_ASC:升序 SORT_DESC：降序 排序的规则
    *@param $sort_type SORT_REGULAR :常规 SORT_NUMERIC:数字  SORT_STRING :字母 排序的类型
    *
    */
    public function my_sort($arrays,$sort_key,$sort_order=SORT_ASC,$sort_type=SORT_NUMERIC ){  
        if(is_array($arrays)){  
            foreach ($arrays as $array){  
                if(is_array($array)){  
                    $key_arrays[] = $array[$sort_key];  
                }else{  
                    return false;  
                }  
            }  
        }else{  
            return false;  
        } 
        array_multisort($key_arrays,$sort_order,$sort_type,$arrays);  
        return $arrays;  
    } 


/**********************************************************************/
	
	/**
	 * 根据订单id返回订单 应收、实收、应付、实付状态和运费
	 * @Auther: 李渊
	 * @Date: 2018.7.9
	 * @param  [type] $ordertype [订单类型] 1、零担  2、整车 3、城配 4、定制
	 * @param  [type] $orderid   [订单id]
	 * @return [type]            [description]
	 */
	public function checkMessage($ordertype,$orderid) {
		// 初始化应收金额
		$use_ar_money = 0;
		// 初始化实收金额
		$use_ra_money = 0;
		// 初始化收款款状态： 1 未支付 2 已支付 3 信用支付
		$use_pay_state = 1;
		// 初始化应付金额
		$driver_ap_money = 0;
		// 初始化实付金额
		$driver_pa_money = 0;
		// 初始化付款状态： 1 未支付 2 已支付 3 信用支付
		$driver_pay_state = 1;
		$tprice =0;  //用户实付提货
        $linepice =0; //用户实付干线
        $delivecost =0;	//用户实付配送
        $tprice_driver =0;	//司机实付提货
        $linepice_driver =0;	//司机实付干线
        $delivecost_driver =0;	//司机实付配送
        //司机的已付提货，干线，配送运费
        $driver_arr = array();
        //用户的已付提货，干线，配送运费
        $user_arr = array();
		// 判断订单类型
		switch ($ordertype) {
			case '1': // 零担
				// 查询订单数据
	    		$order = Db::table('ct_order')
	    				->alias('o')
	    				->join('ct_lineorder l','l.orderid = o.oid')
	    				->join('ct_pickorder p','p.orderid = o.oid')
	    				->join('ct_delorder d','d.orderid = o.oid')
	    				->join('ct_shift s','s.sid = o.shiftid')
	    				->field('o.linepice,delivecost,o.user_checkid,o.paystate,o.orderstate,p.tprice,p.usepprice,p.tcarr_upprice,p.pic_checkid,l.luseprice,
	    					l.lcarr_price,l.line_checkid,d.pcarr_upprice,d.puseprice,o.usercheck,p.checkyesno,s.shiftstate,o.pay_type')
	    				->where('o.oid',$orderid)
	    				->find();
	    		 //用户应付总额
	    		$user_total = $order['tprice'] +$order['linepice']+$order['delivecost'];
            	$use_ar_money = $order['shiftstate']=='1' ? $user_total : 0;
            	//用户修改提货价格
   			 	$tprice = $order['usepprice']=='' ? $order['tprice'] : $order['usepprice'];
        		//用户修改干线价格
        		$linepice = $order['luseprice']=='' ? $order['linepice'] : $order['luseprice'];
        		//用户修改配送价格
        		$delivecost = $order['puseprice']=='' ? $order['delivecost'] : $order['puseprice'];
        		$user_arr = array('tprice'=>number_format($tprice,2),'linepice'=>number_format($linepice,2),'delivecost'=>number_format($delivecost,2));
	    		if ($order['paystate']=='2') {
	    			if ($order['pay_type'] == '1') {
	                    $use_ar_money = $tprice+$linepice+$delivecost;
	                    $use_ra_money = $order['usercheck'] == 3 ?  $use_ar_money : 0;
						$use_pay_state = $order['usercheck'] == 3 ? 2 : 3;
	    			}else{
	    				//当选择班次为平台班次时 用户已付状态
	    				$use_ra_money = $order['shiftstate']=='1' ? $use_ar_money: 0 ;
						$use_pay_state = 2;
	    			}
	    		}else{ // 未支付
					$use_ra_money = 0;
					$use_pay_state = 1;
				}
				//司机应付总额
				$driver_total = $order['tprice'] +$order['linepice']+$order['delivecost'];
            	$driver_ap_money =  $order['shiftstate']=='1' ? $driver_total : 0;
            	//承运商修改提货价格
            	$tprice_driver = $order['tcarr_upprice']=='' ? $order['tprice'] : $order['tcarr_upprice'];
            	//承运商修改干线价格
            	$linepice_driver = $order['lcarr_price']=='' ? $order['linepice'] : $order['lcarr_price'];
            	//承运商修改配送价格
            	$delivecost_driver = $order['pcarr_upprice']=='' ? $order['delivecost'] : $order['pcarr_upprice']; 
            	$driver_arr = array('tprice_driver'=>number_format($tprice_driver,2),'linepice_driver'=>number_format($linepice_driver,2),'delivecost_driver'=>number_format($delivecost_driver,2));
            	//if ($order['orderstate']=='7') {
            		if($order['checkyesno'] == 3){ // 对账完成或者提现完成
	                    $driver_is_pay = $tprice_driver+$linepice_driver+$delivecost_driver;
						$driver_pa_money =  $order['checkyesno'] == 3 ? $driver_is_pay : 0;
						$driver_pay_state = $order['checkyesno'] == 3 ? 2 : 3;
					}else{
						$driver_pa_money =0;
						//当选择班次为平台班次时 司机已付状态
						$driver_pay_state = $order['shiftstate']=='1' ? 3 : 2;
					}
            	//}else{
            		//$driver_pa_money = 0;
					//$driver_pay_state = 1;
            	//}
				break; 
			case '2': // 整车
				$order = Db::table('ct_userorder')->where('uoid',$orderid)->find();
				// 应收金额（兼顾到旧订单）
				$use_ar_money = $order['referprice'] == '' ? $order['actual_payment'] : $order['referprice'];
				// 实收金额 判断支付状态
				if($order['paystate'] == 2){ // 已支付
					if($order['pay_type'] == 1){ // 信用支付需要判断是否对账未对账实收0
						$use_ra_money = $order['upprice']=='' ?  $use_ar_money : $order['upprice'];
						$use_ra_money = $order['usercheck'] == 3 ?  $use_ar_money : 0;
						$use_pay_state = $order['usercheck'] == 3 ? 2 : 3;
					}else{
						$use_ra_money = $use_ar_money;
						$use_pay_state = 2;
					}
				}else{ // 未支付
					$use_ra_money = 0;
					switch ($order['paystate']) {
						case '1':
							$use_pay_state = 1;
							break;
						case '4':
							$use_pay_state = 4;
							break;
						case '5':
							$use_pay_state = 5;
							break;
						default:
							# code...
							break;
					}
					
				}
				// 应付金额等于订单实际的金额
				$driver_ap_money = $order['carr_upprice']=='' ? $order['price'] : $order['carr_upprice'];

				// 实付金额 判断订单完成状态
				if($order['orderstate'] == 3){ // 订单已完成
					//当订单为面议时
					if ($order['type']=='2') { 
						$driver_ap_money= 0;
						$driver_pa_money = 0;
						$driver_pay_state = 2;
					}else{
						if($order['checkyesno'] == 3){ // 对账完成或者提现完成
							$driver_pa_money = $order['carr_upprice']=='' ? $order['price'] : $order['carr_upprice'];
							$driver_pay_state = 2;
						}else{
							$driver_pa_money =0;
							$driver_pay_state = 3;
						}
					}
					
				}else{ // 未完成
					$driver_pa_money = 0;
					$driver_pay_state = 1;
				}
				break;
			case '3': // 城配
				$order = Db::table('ct_city_order')->where('id',$orderid)->find();
				// 应收金额（兼顾到旧订单）
				$use_ar_money = $order['actual_payment']==''? $order['actualprice'] : $order['actual_payment'];
				// 实收金额 判断支付状态
				if($order['paystate'] == 2){ // 已支付
					if($order['pay_type'] == 1){ // 信用支付需要判断是否对账未对账实收0
						$use_ra_money = $order['upprice']=='' ?  $order['actualprice']: $order['upprice'];
						$use_ra_money = $order['usercheck'] == 3 ?  $order['actualprice']: 0;
						$use_pay_state = $order['usercheck'] == 3 ? 2 : 3;
					}else{
						$use_ra_money = $order['actualprice'];
						$use_pay_state = 2;
					}
				}else{ // 未支付
					$use_ra_money = 0;
					$use_pay_state = 1;
				}
				
				// 应付金额等于订单实际的金额（兼顾到旧订单）
				$driver_ap_money = $order['carr_upprice']==''? $order['paymoney'] : $order['carr_upprice'];
				// 实付金额 判断订单完成状态
				if($order['state'] == 3){ // 订单已完成
					//当订单为面议时
					if ($order['pytype']=='2') { 
						$driver_ap_money= 0;
						$driver_pa_money = 0;
						$driver_pay_state = 2;
					}else{
						if($order['checkyesno'] == 3){ // 对账完成或者提现完成
							$driver_pa_money = $order['carr_upprice']=='' ? $order['paymoney'] : $order['carr_upprice'];
							$driver_pay_state = 2;
						}else{
							//$driver_pa_money = $order['carr_upprice']=='' ? $order['paymoney'] : $order['carr_upprice'];
							$driver_pa_money =0;
							$driver_pay_state = 3;
						}
					}
					
				}else{ // 未完成
					$driver_pa_money = 0;
					$driver_pay_state = 1;
				}
				break;
			case '4': // 定制
				$order = Db::table('ct_shift_order')->where('s_oid',$orderid)->find();
				// 应收金额（兼顾到旧订单）
				$use_ar_money = $order['totalprice'];
				// 实收金额 判断支付状态
				if($order['affirm'] == 2){ // 已支付
					if($order['pay_type'] == 1){ // 信用支付需要判断是否对账未对账实收0
						$use_ra_money = $order['upprice']=='' ?  $order['totalprice']: $order['upprice'];
						$use_ra_money = $order['usercheck'] == 3 ?  $order['totalprice']: 0;
						$use_pay_state = $order['usercheck'] == 3 ? 2 : 3;
					}else{
						$use_ra_money = $order['totalprice'];
						$use_pay_state = 2;
					}
				}else{ // 未支付
					$use_ra_money = 0;
					$use_pay_state = 1;
				}
				// 应付金额等于订单实际的金额
				$driver_ap_money = $order['price'] ;
				// 实付金额 判断订单完成状态
				if($order['orderstate'] == 3){ // 订单已完成					
					if($order['checkyesno'] == 3){ // 对账完成或者提现完成
						$driver_pa_money = $order['carr_upprice']=='' ? $order['price'] : $order['carr_upprice'];
						$driver_pay_state = 2;
					}else{
						$driver_pa_money =0;
						$driver_pay_state = 3;
					}	
				}else{ // 未完成
					$driver_pa_money = 0;
					$driver_pay_state = 1;
				}
				break;
			default:
				# code...
				break;
		}

		// 返回信息
		//应收用户金额
		$check['use_ar_money'] = number_format($use_ar_money,2);
		//实收用户金额
		$check['use_ra_money'] = number_format($use_ra_money,2);
		//用户支付信息状态
		$check['use_pay_state'] = $use_pay_state;
		//应付司机金额
		$check['driver_ap_money'] = number_format($driver_ap_money,2);
		//实付司机金额
		$check['driver_pa_money'] = number_format($driver_pa_money,2);
		//司机支付信息状态
		$check['driver_pay_state'] = $driver_pay_state;
		//零担订单返回用户实付提，干，配运费
		$check['user_line_money'] = $user_arr;
		//零担订单返回应付司机提，干，配运费
		$check['driver_line_money'] = $driver_arr;
		return $check;
	}


	/**
	 * 根据用户id返回下单人信息
	 * @param  [type] $userid [下单人id]
	 * @return [type]         [description]
	 */
	public function cline_mess($userid){
		$usermess ='';
		$user = Db::table('ct_user')->where('uid',$userid)->find();
		if ($user['lineclient']=='' || $user['lineclient']==0) {
			$username = $user['realname']=='' ? $user['username'] : $user['realname'];
			$phone = $user['phone'];
			$usermess = $username.'(TEL:'.$phone.')';
		}else{
			$company = Db::table('ct_company')->where('cid',$user['lineclient'])->find();
			$usermess = $company['name'];
		}
		return $usermess;
	}

	/**
	 * 根据订单类型，订单ID返回对账信息金额，线路，门店，重量
	 * @param  [type] $ordertype [订单类型] 1、定制 2、零担 3、城配 4、整车
	 * @param  [type] $orderid   [订单id]
	 * @param  [type] $type   [对账类型] 1、用户对账 2,、承运商对账
	 * @return [type]
	 */
	public function account_mess($ordertype,$orderid,$type){
		$array = array();
		$totalWeight = 0; //重量
		$totalVolume = 0; //体积
		$pickPrice = 0; //提货费
		$linePrice = 0; //干线费用
		$sendPrice = 0; //配送费
		$doorNum = 0; //门店数
		$line = ''; //线路 如上海--北京
		$totalPrice = 0; //费用总金额
		switch ($ordertype) {
			case '2': //定制订单
				$result = Db::table('ct_shift_order')
							->alias('s')
							->join('ct_fixation_line l','l.id = s.shiftid')
							->join('ct_already_city a','l.lienid = a.city_id')
							->field('s.doornum,s.totalprice,s.upprice,s.carr_upprice,s.price,a.start_id,a.end_id')
							->where('s_oid',$orderid)
							->find();
				//起点城市
	            $scity = Db::table('ct_district')->where('id',$result['start_id'])->find();
	            $city_str ='';
	            if ($scity['level'] =='3') {
	                $city_search = Db::table('ct_district')->where('id',$scity['parent_id'])->find();
	                $city_str = $city_search['name'];
	            }
	            $startcity =  $city_str.$scity['name'];
	            //终点城市
	            $ecity = Db::table('ct_district')->where('id',$result['end_id'])->find();
	            $city_str2 = '';
	            if ($ecity['level'] =='3') {
	                $endcity_search = Db::table('ct_district')->where('id',$ecity['parent_id'])->find();
	                $city_str2 = $endcity_search['name'];
	            }
	            $endcity =  $city_str2.$ecity['name'];
				$line = $startcity.'--'.$endcity;
				$doorNum = $result['doornum'];
				//用户总订单金额
				$user_total_price =  $result['upprice'] =='' ? $result['totalprice'] : $result['upprice'];
				//承运商总订单金额
				$driver_total_price =  $result['carr_upprice'] =='' ? $result['price'] : $result['carr_upprice'];
				$totalPrice = $type=='1' ? $user_total_price : $driver_total_price;
				break;
			case '1':
				$result =  Db::table('ct_order')
							->alias('o')
		                    ->join('ct_pickorder p','p.picid=o.oid')
		                    ->join('ct_lineorder l','l.orderid=o.oid')
		                    ->join('ct_delorder d','d.orderid=o.oid')
		                    ->join('ct_shift s','s.sid=o.shiftid')
		                    ->join('ct_already_city al','al.city_id = s.linecityid')
		                    ->field('o.linepice,o.totalcost,o.delivecost,o.totalvolume,o.totalweight,p.tprice,p.tcarr_upprice,p.usepprice,l.luseprice,
		                    	l.lcarr_price,d.pcarr_upprice,d.puseprice,al.end_id,al.start_id')
		                    ->where('oid',$orderid)
		                    ->find();
		        $line = $this->start_end_city($result['start_id'],$result['end_id']);
		        $user_lineprice = $result['puseprice']=='' ? $result['linepice'] : $result['puseprice']; //用户干线费用
            	$user_tprice = $result['usepprice']=='' ? $result['tprice'] : $result['usepprice']; //用户提货费用
            	$user_delivecost = $result['puseprice']=='' ? $result['delivecost'] : $result['puseprice']; //用户配送费用
				$driver_tprice = $result['tcarr_upprice']=='' ? $result['tprice'] : $result['tcarr_upprice'];  //司机提货费
		        $driver_linepice = $result['lcarr_price']=='' ? $result['linepice'] : $result['lcarr_price'];  //司机干线费
		        $driver_delivecost = $result['pcarr_upprice']=='' ? $result['delivecost'] : $result['pcarr_upprice'];  //司机配送费
		        $pickPrice = $type=='1' ? $user_tprice : $driver_tprice;   //提货费
		        $linePrice = $type=='1' ? $user_lineprice : $driver_linepice; //干线费
		        $sendPrice = $type=='1' ? $user_delivecost : $driver_delivecost; //配送费
		        $sumPrice = $pickPrice + $linePrice + $sendPrice;  //运费总计
		        $userSumPrice = $result['totalcost'] =='' ? $sumPrice : $result['totalcost']; //当用户总运费由修改价格时
		        $totalPrice = $type=='1' ? $userSumPrice : $sumPrice;
		        $totalVolume = $result['totalvolume'];  //体积
		        $totalWeight = $result['totalweight'];  //重量
				break;
			case '3':
				$result = Db::table('ct_city_order')->field('city_id,actual_payment,user_discount,upprice,paymoney,carr_upprice,actualprice')->where('id',$orderid)->find();
				$line = $this->start_city($result['city_id']);
				// 订单运费 没有折扣价显示订单运费 
	            $userPrice = $result['user_discount'] =='' ? $result['actual_payment'] : $result['user_discount'];
	            // 订单运费 没有支付显示订单运费否则显示支付运费
	            $actualPrice = $result['actualprice'] =='' ? $userPrice : $result['actualprice'];
	            //用户运费总计
	            $userCountPrice = $result['upprice'] =='' ? $actualPrice : $result['upprice'];
	            //司机运费总计
	            $driverCountPrice = $result['carr_upprice'] =='' ? $result['paymoney'] : $result['carr_upprice'];
	            $totalPrice = $type=='1' ? $userCountPrice : $driverCountPrice;
				break;
			case '4':
				$result = Db::table('ct_userorder')->field('startcity,endcity,actual_payment,user_discount,referprice,upprice,price,carr_upprice')->where('uoid',$orderid)->find();
				$line = $this->start_end_city($result['startcity'],$result['endcity']);
				// 订单运费 没有折扣价显示订单运费 
	            $userPrice = $result['user_discount'] =='' ? $result['actual_payment'] : $result['user_discount'];
	            // 订单运费 没有支付显示订单运费否则显示支付运费
	            $actualPrice = $result['referprice'] =='' ? $userPrice : $result['referprice'];
	            //用户运费总计
	            $userCountPrice = $result['upprice'] =='' ? $actualPrice : $result['upprice'];
	            //司机运费总计
	            $driverCountPrice = $result['carr_upprice'] =='' ? $result['price'] : $result['carr_upprice'];
	            $totalPrice = $type=='1' ? $userCountPrice : $driverCountPrice;
				break;
			default:
				break;
		}
		//
		$array['total_weight'] = $totalWeight; //重量
		$array['total_volume'] = $totalVolume; //体积
		$array['pick_price'] = $pickPrice; //提货费
		$array['line_price'] = $linePrice; //干线费用
		$array['send_price'] = $sendPrice; //配送费
		$array['doornum'] = $doorNum; //门店数
		$array['line'] = $line; //线路 如上海--北京
		$array['total_price'] = $totalPrice; //费用总金额
		return $array;
	}

	/**
	 * 根据订单获取公司的业务员
	 * @Auther: 李渊
	 * @Date: 2018.8.9
	 * 由于不同时间的业务员不一样
	 * 所以在根据订单查找公司的业务员的时候要根据下单时间进行匹配
	 * 返回业务员的姓名没有则默认平台
	 * @param  Int 		$cid 		[公司id]
	 * @param  Int 		$ordertime 	[下单时间]
	 * @return [String]      [业务员姓名]
	 */
	public function get_order_salesman($cid,$ordertime)
	{
		// 查询条件 业务员的开始时间要小于订单的下单时间
		$where['starttime']  = ['<',$ordertime];
		// 查询条件 公司id
		$where['cid']  = $cid;
		// 查询该下单时间内项目公司的赤途业务员的id
        $busid =  DB::table('ct_business')->where($where)->max('sort');
        // 查询该业务员信息
        $busine = DB::table('ct_business')->where(array('cid'=>$cid,'sort'=>$busid))->find();
        // 查询该业务员信息
        $amin = DB::table('ct_admin')->where('aid',$busine['aid'])->find();
        // 获取该业务员名字
        $name = $amin['realname'] ? $amin['realname'] : '平台';
        // 返回业务员名字
        return $name;
	}

	/**
	 * 获取分享人
	 * @Auther: 李渊
	 * @Date: 2018.8.9
	 * 根据用户的id获取分享给该用户的人，并返回分享人的姓名，
	 * 如果没有则默认分享人平台
	 * @param  Int 		$userid 	[下单人id]
	 * @return [String]      		[分享人姓名]
	 */
	public function get_sharename($userid)
	{
		// 查询下单人信息
		$user = Db::table('ct_user')->where('uid',$userid)->find();
		// 定义分享人姓名
		$sharename = '';
		// 判断是否有分享人
		if($user['shareid']){ 
			// 查询下单人的分享着
			$share = Db::table('ct_user')->where('uid',$user['shareid'])->find();
			// 分享人姓名
			$sharename = $share['realname'] ? $share['realname'] : $share['username'];
		}else{
			$sharename = '平台';
		}
		// 返回分享人
		return $sharename;
	}
}




?>
