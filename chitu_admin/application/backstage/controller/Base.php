<?php
/**
 * 后台基类文件
 */
namespace app\backstage\controller;
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
		return view("error/index");
	}


	//并发执行，备注：使用控制器才可以使用此方法
	public  function _initialize(){
		//echo "并发操作</br>";
	}


	public function if_login(){
		$carrier_id = Session::get('carrier_id','carrier_mes');
		if(empty($carrier_id)){
			$this->redirect('Index/login');
		}
	}
	/**
     * 二维数组根据某个字段排序
     * arr： 需要排序的数组
     * var： 排序所需要的字段
     * Author: baobaolong
     */
	public function mymArrsort($arr,$var){
        $tmp=array();
        $rst=array();
        foreach($arr as $key=>$trim){
            $tmp[$key]=$trim[$var];
        }
        arsort($tmp);
        $i=0;
        foreach($tmp as $key1=>$trim1){
            $rst[$i]=$arr[$key1];
            $i=$i+1;
        }
        return $rst;
    }
    /**
     * 省市区地址查询
     * provinceid: 省ID
     * cityid:     市ID
     * areaid:	   区ID
     * Author: baobaolong
     */
	public function completeAddress($provinceid,$cityid,$areaid){
       	$provinceid_where['id'] = $provinceid;
		$provinceid = Db::table('ct_district')->where($provinceid_where)->find();
		$cityid_where['id'] = $cityid;
		$cityid = Db::table('ct_district')->where($cityid_where)->find();
		$areaid_where['id'] = $areaid;
		$areaid	= Db::table('ct_district')->where($areaid_where)->find();
        return $provinceid['name'].$cityid['name'].$areaid['name'];
    }
    /**
     * 过滤物品重复提货点
     * allitems: 订单所有物品
     * Author: baobaolong
     */
	public function filterrepeat($allitems){
       	$abr = array();
		foreach($allitems as $key=>$val){
			if(!in_array($allitems[$key]['taddressid'],$abr)){
				$abr[] = $allitems[$key]['taddressid'];
			}else{
				unset($allitems[$key]);
			}
		} 
        return $allitems;
    }
    /**
     * 过滤物品重复配送点
     * allitems: 订单所有物品
     * Author: baobaolong
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
	    $info = $file->validate(['size'=>15678,'ext'=>$ext,'autoSub'=>false])->move($dir_path,true,false);
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
     /**
     * 插入操作日记
     * allitems: 订单所有物品
     *author:chenwei
     */
	public function hanldlog($uid='',$content){
		if ($uid !='') {
			$data['admin_id'] = $uid;
		}
       	
       	$data['content'] = $content;
       	$data['addtime'] = time();
       	DB::table('ct_log')->insert($data);
    }
    /*
    *发车队列窗口
    */
    public function getTimeFromWeek($dayNum){
	    $curDayNum=date("w");
	   
	    if($dayNum>$curDayNum) $timeFlag="next ";
	    elseif($dayNum==$curDayNum) $timeFlag="";
	    else $timeFlag="next ";
	    $arryWeekDay = array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday');
	    $timeStamp=strtotime("$timeFlag"."$arryWeekDay[$dayNum]");
	    return $timeStamp;
	        
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
			if ( $u > 0 ){
				$elapse = $u . $cn;
				break;
			}
		}
		return $elapse;
	}
	



 //市内配送订单拼单
    public function city_stitching($city){
        $result = Db::table('ct_city_order')->where('state','2')->where('rout_id','0')->select();
        $total_fee = '';
        $total_weight = '';
        $all_id = '';
        $total_fee2 = '';
        $total_weight2 = '';
        $all_id2 = '';
        $city_condition = Db::table('ct_city_cost')->where('c_city',$city)->find();

       
        //今日、次日未做判断 未做冷冻  冷藏区分
        foreach ($result as $key => $value) {
            if ($value['data_type'] == '1') {
                $total_fee += $value['actualprice'];
                $total_weight += $value['all_weight'];
                $all_id .= $value['id'].',';
            }else{
                $total_fee2 += $value['actualprice'];
                $total_weight2 += $value['all_weight'];
                $all_id2 .= $value['id'].',';
            }
        }
        $all_id =  substr($all_id,0,strlen($all_id)-1);
        if ($total_fee > $city_condition['spellmoney'] || substr_count($all_id,',') == ($city_condition['spellnum']-1) || $total_weight > $city_condition['spellweight']) {
            $data['count_money'] = $total_fee;
            $data['start_time'] = time();
            $data['runtime'] = strtotime(date("Y-m-d"));
            $result = Db::table("ct_rout_order")->insert($data);
            if ($result) {
                $rout_id = Db::table("ct_rout_order")->getLastInsID();
                $city_where['id'] = array('in',$all_id);
                Db::table('ct_city_order')->where($city_where)->update(array('rout_id'=>$rout_id,'state'=>'2')); 
              // $this->send_note($typestate='3',$city,'','');
            }
        }
        $all_id2 =  substr($all_id2,0,strlen($all_id2)-1);
        if ($total_fee2 > $city_condition['spellmoney'] || substr_count($all_id2,',') == ($city_condition['spellnum']-1) || $total_weight2 > $city_condition['spellweight']) {
            $data2['count_money'] = $total_fee2;
            $data2['start_time'] = time();
            $data['runtime'] = strtotime(date("Y-m-d",strtotime("+1 day")));
            $result2 = Db::table("ct_rout_order")->insert($data2);
            if ($result2) {
                $rout_id2 = Db::table("ct_rout_order")->getLastInsID();
                $city_where2['id'] = array('in',$all_id2);
                Db::table('ct_city_order')->where($city_where2)->update(array('rout_id'=>$rout_id2,'state'=>'2')); 
               // $this->send_note($typestate='3',$city,'','');
            }
        }
    }


    public function deldir($dir) {
      //先删除目录下的文件：
      $dh=opendir($dir);
      while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
          $fullpath=$dir."/".$file;
          if(!is_dir($fullpath)) {
              unlink($fullpath);
          } else {
              $this->deldir($fullpath);
          }
        }
      }
     
      closedir($dh);
      //删除当前文件夹：
      if(rmdir($dir)) {
        return true;
      } else {
        return false;
      }
    }
/**********************************************************************/


}

 ?>
