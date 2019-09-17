<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Address  extends Base{

	public function city(){
		$where['name'] = array("like","%市%");
		$where['level'] = '2';
		$city = Db::table("ct_district")->field('id,name')->where($where)->select();
		
		return json_encode($city);
	}
    
    /**
     * 获取所有的省市区
     * @auther: 李渊
     * @date: 2018.10.10
     * @param  [type] [name] [<description>]
     * @return [type] [description]
     */
    public function all(){
    	$parent = Db::table("ct_district")->where('parent_id=0')->select();
		foreach ($parent as $key => $value) {
			$city= Db::table("ct_district")->where('parent_id',$value['id'])->select();
			foreach ($city as $k => $v) {
				$city[$k]['area'] = Db::table("ct_district")->where('parent_id',$v['id'])->select();
			}
			$parent[$key]['city'] = $city; 
		}
		return json_encode($parent);
    }

	//一级省市区
	public function parent_address(){
		$token   = input("token");  //令牌
		$result = Db::table("ct_district")->where("parent_id",'0')->select();
		if($result){
			return json(['code'=>'1001','message'=>'查询成功','data'=>$result]);
		}else{
			return json(['code'=>'1002','message'=>'暂无数据']);
		}

	}

	//获取子类地区
	public function child_address(){
		$token   = input("token");  //令牌
		$id   = input("id");  //地区ID
	    if(empty($id) ){
			return json(['code'=>'1000','message'=>'参数错误']);
		}

		$result = Db::table("ct_district")->where("parent_id",$id)->select();
		if($result){
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}else{
			return json(['code'=>'1001','message'=>'暂无数据']);
		}

	}	

	/**
	 * 常用地址列表
	 * @auther: 李渊
	 * @date: 2018.8.22
	 * @param  [String] token  [用户令牌]
	 * @param  [String] cityid [城市id 可为空 为空返回全部地址]
	 * @return [type] [description]
	 */
	public function index(){
		// 用户令牌
	    $token   = input("token");
	    // 城市id
	    $cityid = input("city_id");
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
		// 判断条件用户id
		$where['a.user_id'] = $user_id;
		// 判断是否有城市id
		if($cityid){
			$where['a.city_id'] = $cityid;
		}
		// 查询数据
		$result = Db::table("ct_addressuser")
			->alias('a')
			->join('__DISTRICT__ d','a.pro_id = d.id')
			->field("a.*,d.name as pro_name")
			->where($where)
			->select();
		// 遍历数据
		foreach ($result as $key => $value) {
			$city_result = Db::table("ct_district")->field('name')->where('id',$value['city_id'])->find();
			$result[$key]['city_name'] =$city_result['name'];
			$area_result = Db::table("ct_district")->field('name')->where('id',$value['area_id'])->find();
			$result[$key]['area_name'] =$area_result['name'];
		}	
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
		
	}


	//设为默认地址
	public  function default_address(){
		$address_id = input('address_id');
		$token   = input("token");  //令牌
	    if(empty($token) || empty($address_id)){
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
		$condition['address_id'] = $address_id;
		$condition['default']  = '2';  //是否默认: 1 否 2 是
		$if_exf = Db::table("ct_addressuser")->where($condition)->find();
		if($if_exf){
			return json(['code'=>'1001','message'=>'已是默认地址']);
		}else{
			$where['user_id'] = $user_id;
			$where['default']  = '2';  //是否默认: 1 否 2 是
			$if_default = Db::table("ct_addressuser")->where($where)->find();
			if($if_default){
				 Db::table("ct_addressuser")->where('address_id',$if_default['address_id'])->update(array('default'=>1));
			}
			$res = Db::table("ct_addressuser")->where('address_id',$address_id)->update(array('default'=>2));	
			if($res){
				return json(['code'=>'1002','message'=>'设置成功']);
			}else{
				return json(['code'=>'1003','message'=>'设置失败']);
			}

		}
		
	}


	//添加常用地址
	public function add(){
		$pro_id = input('pro_id');
		$city_id = input('city_id');
		$area_id = input('area_id');
		$address = input('address');
		$token   = input("token");  //令牌
		$name = input("name");
		$phone = input("phone");
	    if(empty($pro_id) || empty($city_id) || empty($area_id)  || empty($address) || empty($token)){
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

		 /**** 插入数据库*/
        $insert_data = array(
            'pro_id'=>$pro_id,
            'city_id'=>$city_id,
            'area_id'=>$area_id,
            'address'=>$address,
            'name'=>$name,
            'phone'=>$phone,
            'add_time'=>time(),
            'user_id'=>$user_id
        );
        $result = Db::table("ct_addressuser")->insert($insert_data);

        if($result){
            return json(['code'=>'1001','message'=>'添加成功']);
        }else{
        	return json(['code'=>'1002','message'=>'添加失败']);
        }


	}


	//地址修改
	public function edite(){
		$pro_id = input('pro_id');
		$city_id = input('city_id');
		$area_id = input('area_id');
		$address = input('address');
		$name = input("name");
		$phone = input("phone");
		$address_id = input('address_id');
		$token   = input("token");  //令牌
	    if(empty($pro_id) || empty($city_id) || empty($area_id)  || empty($address) || empty($token) || empty($address_id)){
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

		 /**** 更新数据库*/
        $upda_data = array(
            'pro_id'=>$pro_id,
            'city_id'=>$city_id,
            'area_id'=>$area_id,
            'address'=>$address,
            'name'=>$name,
            'phone'=>$phone,
        );
        $condition['address_id'] = $address_id;
        $condition['user_id'] = $user_id;
        $result = Db::table("ct_addressuser")->where($condition)->update($upda_data);

        if($result){
            return json(['code'=>'1001','message'=>'修改成功']);
        }else{
        	return json(['code'=>'1002','message'=>'修改失败']);
        }

	}

	/**
	 * 地址多选删除
	 * @auther: 李渊
	 * @date: 2018.10.10
	 * @param  [array] 	[ids] [需要删除的地址id]
	 * @return [type] 	[description]
	 */	
	public function del_arr(){
		// 获取ids
		$ids = input('ids');
		// 获取token
		$token   = input("token");
		// 验证参数
	    if(empty($token) || empty($ids)){
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
		$condition['user_id'] = $user_id;
		$condition['address_id'] = array('in',$ids);
		$result = Db::table("ct_addressuser")->where($condition)->delete();
        if($result){
            return json(['code'=>'1001','message'=>'删除成功']);
        }else{
        	return json(['code'=>'1002','message'=>'删除失败']);
        }

	}








}
