<?php
/**
 * 后台基类文件
 */
namespace app\company\controller;
use think\Controller;
use think\Session;
use  think\Db;  //使用数据库操作

class Base extends Controller
{
	function __construct(){
		parent::__construct();
		
	}

	//空操作
	public function _empty(){
		echo '空操作';
	}


	//并发执行，备注：使用控制器才可以使用此方法
	public  function _initialize(){
		//echo "并发操作</br>";
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
	/*
	*查找单个起点城市 北京
	*$proid 省ID
	*$cityid 市ID
	*author:chenwei
	*/
	
	public function start_city($searchid){
		
		$result =  DB::table('ct_district ')->where(array('id'=>$searchid))->find();
		return $result['name'];
	}
	/*
	*查找一对起点城市 上海-北京
	*$proid 省ID
	*$cityid 市ID
	*author:chenwei
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
	/*
	*查找详细地址信息 上海-嘉定区-江桥镇
	*$proid 省ID
	*$cityid 市ID
	*areaid 区ID
	*author:chenwei
	*/
	public function detailadd($proid,$cityid,$areaid){
		//if($proid !=''){
			$result1 =  DB::table('ct_district')->where(array('id'=>$proid))->find();
			$result2 =  DB::table('ct_district')->where(array('id'=>$cityid))->find();
			$result3 =  DB::table('ct_district')->where(array('id'=>$areaid))->find();
			return $result1['name'] . $result2['name'] .  $result3['name'];
		//}
		//return '';
		
	}

	/*
	*过滤物品重复提货点
	*allitems:订单所有物品
	*author:chenwei
	*/
	public function filteritme($items){
		$array = array();
		foreach($items as $key=>$val){
	      if(!in_array($items[$key]['taddressid'],$array)){
	      	$items[$key]['taddressid'];
	      }else{
	        unset($items[$key]);
	      }
	    } 
	    return $items;
	}

	/*
	*项目客户公司下所有用户ID
	*companyid:公司ID
	*author:chenwei
	*/
	public function getuseridstr($companyid){
		$user_com = DB::table('ct_user')->where('lineclient',$companyid)->SELECT();
       foreach ($user_com as $key => $value) {
           $arr_uid[] = $value['uid'];
       }
       $userid_str = implode(',',$arr_uid);
       return $userid_str;
	}

	/*
	*项目客户公司下所有下单的用户用户ID
	*companyid:公司ID
	*cityid:开通城市ID
	*author:chenwei
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
     *author:chenwei
     */
	public function filterdistribution($allitems){
       	$abr = array();
		foreach($allitems as $key=>$val){
			if(!in_array($allitems[$key]['paddress'],$abr)){
				$abr[] = $allitems[$key]['paddress'];
			}else{
				unset($allitems[$key]);
			}
		} 
        return $allitems;
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
	





/**********************************************************************/


}

 ?>
