<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Warehouse  extends Base{
	/*
	*
	*仓库列表
	*
	*/
	public function houselist(){
		
		$city = input('cityid'); //城市id
		$areaid = input('areaid'); //区id
		$type = input('type'); //类型搜索1仓储型2中转型
		$beginprice = input('start');  //开始价格
		$endprice = input('end');  //结束价格
		$pricestr = input('pricestr');  //面议
		$where='';
		if (!empty($city)) {
			$where['cityid'] = $city;
		}
		if (!empty($areaid)) {
			$where['areaid'] = $areaid;
		}
		if (!empty($type)) {
			$where['wtype'] = $type;
		}
		if (!empty($beginprice) && !empty($endprice)) {
			$where['price'] = array('between',"".$beginprice.",".$endprice."");
		}
		if (!empty($pricestr)) {
			$where['price'] = $pricestr;
		}
		$result = Db::table("ct_warehouse")
					->where($where)
					->order('wid desc')
					->paginate(10);
		$result = $result->toArray();
		$res = array();
		foreach ($result['data'] as $key => $value) {
			$res[$key] = $value;
			$res[$key]['citystr'] = detailadd($value['cityid'],'','');
			$res[$key]['areastr'] = detailadd($value['areaid'],'','');
			$res[$key]['picture'] = json_decode($value['picture']);
		}
		if ($result) {
			return json(['code'=>'1001','message'=>'查询成功','data'=>$res]);
		}else{
			return json(['code'=>'1002','message'=>'暂无数据']);
		}
	}
	/*
	*仓库详情
	*/
	public function house_detail(){
		$wid = input('wid'); //仓库ID
		if (empty($wid)) {
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$result = Db::table('ct_warehouse')->where('wid',$wid)->find();
		$result['address_str'] = detailadd($result['cityid'],$result['areaid'],'');
		$result['picture'] = json_decode($result['picture']);
		if (empty($result)) {
			return json(['code'=>'1002','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1001','message'=>'查询成功','data'=>$result]);
		}

	}
	/*
	*我的发布：仓库列表
	*/
	public function myhouse(){
		$token = input('token'); //令牌
		if(empty($token)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		$where['userid'] = $user_id;
		$result = Db::table("ct_warehouse")
					->where($where)
					->order('wid desc')
					->select();
		
		$res = array();
		foreach ($result as $key => $value) {
			$res[$key] = $value;
			$res[$key]['citystr'] = detailadd($value['cityid'],'','');
			$res[$key]['areastr'] = detailadd($value['areaid'],'','');
			$res[$key]['picture'] = json_decode($value['picture']);
		}
		if ($result) {
			return json(['code'=>'1001','message'=>'查询成功','data'=>$res]);
		}else{
			return json(['code'=>'1002','message'=>'暂无数据']);
		}
	}
	/*
	*我的发布：删除仓库
	*/
	public function delhouse(){
		$id = input('wid'); //仓库id
		$token = input('token'); //令牌
		if (empty($id) && empty($token)) {
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		$del = Db::table('ct_warehouse')->delete($id);
		if ($del) {
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}
	/*
	*我的发布：添加仓库
	*/
	public function addhouse(){
		$token = input('token'); //令牌
		$housename = input('housename'); //仓库名称
		$cityid = input('cityid');  //城市id
		$areaid = input('areaid');	//区id
		$address = input('address');	//详细地址
		$com_name = input('com_name');	//公司名称
		$wtype = input('wtype');	//仓储类型 1仓储型2中转型
		$areanumber = input('areanumber');	//仓库面积
		$price = input('price');	//仓库价格
		$cantact = input('cantact');	//仓库联系人
		$telephone = input('telephone');	//联系人电话号码
		$remarks = input('remarks');	//备注
		if(empty($token) && empty($cityid) && empty($areaid)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		$i=1;
		$res_imgs = array();
		for($i=1;$i<10;$i++){
			if(!empty($_FILES['back_img_'.$i]['tmp_name'])){
				//回单2
				$re = $this->file_upload('back_img_'.$i,'jpg,gif,png,jpeg',"house");
		       $res_imgs[] = $re['file_path']; //源文件地址
			}
		}
		if(!empty($_FILES['businessImg']['tmp_name'])){
			$res = $this->file_upload('businessImg','jpg,gif,png,jpeg',"house");
		    $data['license'] = $res['file_path']; //源文件地址
		    $data['state'] = 2;
		}
		$picture = json_encode($res_imgs);
		 $city_str = detailadd($cityid,'','');
        $area_str = detailadd($areaid,'','' );
        $local_action = bd_local($type='2',$city_str,$city_str.$area_str.$address);
        $data['longitude'] = $local_action['lng'];
        $data['latitude'] = $local_action['lat'];	
		$data['picture'] = $picture;
		$data['cityid'] = $cityid;
		$data['areaid'] = $areaid;
		$data['address'] = $address;
		$data['com_name'] = $com_name;
		$data['housename'] = $housename;
		$data['wtype'] = $wtype;
		$data['areanumber'] = $areanumber;
		$data['price'] = $price;
		$data['cantact'] = $cantact;
		$data['telephone'] = $telephone;
		$data['remarks'] = $remarks;
		$data['picture'] = $picture;
		$data['userid'] = $user_id;
		$data['addtime'] = time();
		$inser = Db::table('ct_warehouse')->insert($data);
		if ($inser) {
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}

	/*
	*修改仓库
	*/
	public function uphouse(){
		$token = input('token'); //令牌
		$wid = input('wid'); //仓库iD
		$housename = input('housename'); //仓库名称
		$cityid = input('cityid');  //城市id
		$areaid = input('areaid');	//区id
		$address = input('address');	//详细地址
		$com_name = input('com_name');	//公司名称
		$wtype = input('wtype');	//仓储类型 1仓储型2中转型
		$areanumber = input('areanumber');	//仓库面积
		$price = input('price');	//仓库价格
		$cantact = input('cantact');	//仓库联系人
		$telephone = input('telephone');	//联系人电话号码
		$remarks = input('remarks');	//备注
		if(empty($token) && empty($wid)){
			return json(['code'=>'1000','message'=>'参数错误']);
		}
		$check_result = $this->check_token($token);//验证令牌
		
		if($check_result['status'] =='1'){
			return json(['code'=>'1007','message'=>'非法请求']);
		}elseif($check_result['status'] =='2'){
			return json(['code'=>'1008','message'=>'token已过期，请重新登录']);
		}else{
			$user_id = $check_result['user_id'];
		}
		$i=1;
		
		$res_imgs = array();
		for($i=1;$i<10;$i++){
			if(!empty($_FILES['back_img_'.$i]['tmp_name'])){
				//回单2
				$re = $this->file_upload('back_img_'.$i,'jpg,gif,png,jpeg',"house");
		       $res_imgs[] = $re['file_path']; //源文件地址
			}
		}
		if(!empty($_FILES['businessImg']['tmp_name'])){
			$res = $this->file_upload('businessImg','jpg,gif,png,jpeg',"house");
		    $data['license'] = $res['file_path']; //源文件地址
		    $data['state'] = 2;
		}
		if (!empty($res_imgs)) {
			$picture = json_encode($res_imgs);	
			$data['picture'] = $picture;
		}
		
		if (!empty($cityid) && !empty($areaid)) {
			$data['cityid'] = $cityid;
			$data['areaid'] = $areaid;
			$data['address'] = $address;
			 $city_str = detailadd($cityid,'','');
	        $area_str = detailadd($areaid,'','' );
	        $local_action = bd_local($type='2',$city_str,$city_str.$area_str.$address);
	        $data['longitude'] = $local_action['lng'];
       		 $data['latitude'] = $local_action['lat'];	
		}
		$data['housename'] = $housename;
		$data['com_name'] = $com_name;
		$data['wtype'] = $wtype;
		$data['areanumber'] = $areanumber;
		$data['price'] = $price;
		$data['cantact'] = $cantact;
		$data['telephone'] = $telephone;
		$data['remarks'] = $remarks;
		
		$inser = Db::table('ct_warehouse')->where('wid',$wid)->update($data);
		if ($inser) {
			return json(['code'=>'1001','message'=>'操作成功']);
		}else{
			return json(['code'=>'1002','message'=>'操作失败']);
		}
	}
}
