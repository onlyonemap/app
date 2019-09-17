<?php
namespace app\driver\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Uccar extends Base{

    //平台：车型列表
	public function car_model(){
		$token   = input("token");  //令牌
		if(empty($token)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}

		$list = Db::table("ct_cartype")->order('car_id  desc')->select();
		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}

	}
	//车辆添加
	public function car_add(){
		$token   = input("token");  //令牌
		if(empty($token)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		$driver = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		$data['carid'] = input('carid'); //车型ID
		$data['carnumber'] = input('carnumber'); //车牌号
		
		$data['status'] = '1'; //车辆状态（车辆状态（1未审核 2审核通过 3审核失败））
		$data['temperature'] = input("temperature"); //温控
		$data['car_age'] = input("car_age"); //车龄
		if ($driver['type'] =='1' && $driver['companyid'] =='') {
			$data['driverid'] = $driver_id; //司机ID
		}else{
			$data['com_id'] = $driver["companyid"]; //车龄
		}
		
		 //行驶证
		if(!empty($_FILES['travelimg']['tmp_name'])){
			$re_travelimg = $this->file_upload('travelimg','jpg,gif,png,jpeg',"driver");
	        $data['travelimg'] = $re_travelimg['file_path']; //源文件地址
        }else{
			$data['travelimg'] = '';
		}
		//运营证
		if(!empty($_FILES['operateimg']['tmp_name'])){
			$re_operateimg = $this->file_upload('operateimg','jpg,gif,png,jpeg',"driver");
	        $data['operateimg'] = $re_operateimg['file_path']; //源文件地址
	    }
		//车辆照片：右前方45°侧面照
		if(!empty($_FILES['carimage']['tmp_name'])){
			$re_carimage = $this->file_upload('carimage','jpg,gif,png,jpeg',"driver");
	        $data['carimage'] = $re_carimage['file_path']; //源文件地址
    	}


		$data['addtime'] = time(); //添加时间
		
		$res = Db::table("ct_carcategory")->insert($data);
		if($res){
			return json(['code'=>'1001','message'=>'添加成功']);
		}else{
			return json(['code'=>'1002','message'=>'添加失败']);
		}

	}
	
	//车辆列表
	public function car_list(){
		$token   = input("token");  //令牌
		if(empty($token)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		$driver = Db::table('ct_driver')->where('drivid',$driver_id)->find();
		if ($driver['type']=='1' && $driver['companyid'] =='') {
			$list = Db::table("ct_carcategory")
				->alias('c')
				->join('__CARTYPE__ t','c.carid = t.car_id')	
				->field('c.ccid,c.carnumber,c.status,c.car_age,c.temperature,t.carparame')
				->order('c.addtime desc')
				->where('c.driverid',$driver_id)
				->select();
		}else{
			$driver_inid = Db::table('ct_driver')->where('companyid',$driver['companyid'])->select();
			$cid='';
			foreach ($driver_inid as $key => $value) {
				$cid .= $value['drivid'].',';
			}
			$cid =  substr($cid,0,strlen($cid)-1);
			
			$where['a.driverid'] = array('in',$cid);
			$where2['a.com_id'] = $driver['companyid'];
			$list = Db::field('a.ccid,a.carnumber,a.status,a.car_age,a.temperature,b.carparame')
			->table('ct_carcategory')
			->alias('a')
			->join('ct_cartype b','b.car_id=a.carid')
			->where($where)
			->whereOr($where2)
			->order('a.addtime desc')
			->select();
		}
		

		if(empty($list)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$list]);
		}		


	}


	//单个车辆信息
	public  function car_mes(){
		$token   = input("token");  //令牌
		$ccid   = input("ccid");  //车辆ID
		if(empty($token) || empty($ccid)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}
		$car_mes = Db::table("ct_carcategory")
				->alias('c')
				->join("__CARTYPE__ t",'c.carid = t.car_id')
				->where('c.ccid',$ccid)
				->find();

		if(empty($car_mes)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$car_mes]);
		}				
	}

	//数据修改提交
	public function car_edite(){
		$token   = input("token");  //令牌
		$ccid    = input("ccid");  //车辆ID
		if(empty($token)  || empty($ccid)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}

		$data['carid'] = input('carid'); //车型ID
		$data['carnumber'] = input('carnumber'); //车牌号
		$data['fail_reason'] = ''; //清空理由
		//$data['driverid'] = $driver_id; //司机ID
		$data['status'] = '1'; //车辆状态（车辆状态（1未审核 2审核通过 3审核失败））
		$data['temperature'] = input("temperature"); //温控
		$data['car_age'] = input("car_age"); //车龄
		$data['addtime'] = time(); //车龄
		$find_mess = Db::table('ct_carcategory')->where('ccid',$ccid)->find();
		 //行驶证
		$travelimg ='';
		if(!empty($_FILES['travelimg']['tmp_name'])){
			$re_travelimg = $this->file_upload('travelimg','jpg,gif,png,jpeg',"driver");
	        $data['travelimg'] = $re_travelimg['file_path']; //源文件地址
	        $travelimg = $find_mess['travelimg'];
        }
		//运营证
		$operateimg = '';
		if(!empty($_FILES['operateimg']['tmp_name'])){
			$re_operateimg = $this->file_upload('operateimg','jpg,gif,png,jpeg',"driver");
	        $data['operateimg'] = $re_operateimg['file_path']; //源文件地址
	        $operateimg = $find_mess['operateimg'];
	    }
		//车辆照片：右前方45°侧面照
		$carimage = '';
		if(!empty($_FILES['carimage']['tmp_name'])){
			$re_carimage = $this->file_upload('carimage','jpg,gif,png,jpeg',"driver");
	        $data['carimage'] = $re_carimage['file_path']; //源文件地址
	        $carimage = $find_mess['carimage'];
    	}
    	$arr_mess['travelimg'] = $travelimg;
    	$arr_mess['operateimg'] = $operateimg;
    	$arr_mess['carimage'] = $carimage;
    	$arr_mess['reason'] = $find_mess['fail_reason'];
    	$arr_mess['addtime'] = $find_mess['addtime'];
    	$arr[] = $arr_mess;
    	$array=array();
		if ($find_mess['history_mess']!='') {
			$array = json_decode($find_mess['history_mess'],TRUE);
		}
		$reson_arr = array_merge($arr,$array);
		$data['history_mess']=json_encode($reson_arr); 
		$res = Db::table("ct_carcategory")->where('ccid',$ccid)->update($data);
		if($res){
			return json(['code'=>'1001','message'=>'修改成功']);
		}else{
			return json(['code'=>'1002','message'=>'修改失败']);
		}
		
	}

	/*
	*  车辆删除
	*/
	public  function  car_delete(){
		$token   = input("token");  //令牌
		$ids = input('ids');  //车辆ID 数组
		if(empty($token)  || empty($ids)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$driver_id = $check_result['driver_id'];
		}

		//$condition['driverid'] = $driver_id;
		$condition['ccid'] = array('in',$ids);
		$result = Db::table("ct_carcategory")->where($condition)->delete();
        if($result){
            return json(['code'=>'1001','message'=>'删除成功']);
        }else{
        	return json(['code'=>'1002','message'=>'删除失败']);
        }

	}

	









}


