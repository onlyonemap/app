<?php
namespace app\user\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Contact  extends Base{

   
	//常用联系人列表：
	public function index(){
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
			$user_id = $check_result['user_id'];
		}
		$result = Db::table("ct_contacts")
			->where('userid',$user_id)
			->select();
		if(empty($result)){
			return json(['code'=>'1001','message'=>'暂无数据']);
		}else{
			return json(['code'=>'1002','message'=>'查询成功','data'=>$result]);
		}
		

	}


	//设为默认联系人
	public  function default_contact(){
		$conid = input('conid');
		$token   = input("token");  //令牌
	    if(empty($token) || empty($conid)){
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
		$condition['conid'] = $conid;
		$condition['yesno']  = '2';  //是否默认1否2是
		$if_exf = Db::table("ct_contacts")->where($condition)->find();
		if($if_exf){
			return json(['code'=>'1001','message'=>'已是默认地址']);
		}else{
			$where['userid'] = $user_id;
			$where['yesno']  = '2';  //是否默认1否2是
			$if_default = Db::table("ct_contacts")->where($where)->find();
			if($if_default){
				 Db::table("ct_contacts")->where('conid',$if_default['conid'])->update(array('yesno'=>1));
			}
			$res = Db::table("ct_contacts")->where('conid',$conid)->update(array('yesno'=>2));	
			if($res){
				return json(['code'=>'1002','message'=>'设置成功']);
			}else{
				return json(['code'=>'1003','message'=>'设置失败']);
			}

		}

	}


	//添加常用联系人
	public function add(){
		$username = input('name');
		$telephone = input('phone');
		$token   = input("token");  //令牌
	    if(empty($username) || empty($telephone)  || empty($token)){
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
            'username'=>$username,
            'telephone'=>$telephone,
            'add_time'=>time(),
            'userid'=>$user_id
        );
        $result = Db::table("ct_contacts")->insert($insert_data);

        if($result){
            return json(['code'=>'1001','message'=>'添加成功']);
        }else{
        	return json(['code'=>'1002','message'=>'添加失败']);
        }


	}


	//联系人修改
	public function edite(){
		$username = input('name');
		$telephone = input('phone');
		$token   = input("token");  //令牌
		$conid = input('conid');
	    if(empty($username) || empty($telephone) || empty($token)){
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
            'username'=>$username,
            'telephone'=>$telephone
        );
        $condition['conid'] = $conid;
        $condition['userid'] = $user_id;
        $result = Db::table("ct_contacts")->where($condition)->update($upda_data);

        if($result){
            return json(['code'=>'1001','message'=>'修改成功']);
        }else{
        	return json(['code'=>'1002','message'=>'修改失败']);
        }

	}



	//联系人多选删除
	public function del_arr(){
		$ids = input('ids');
		$token   = input("token");  //令牌
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
		$condition['userid'] = $user_id;
		$condition['conid'] = array('in',$ids);
		$result = Db::table("ct_contacts")->where($condition)->delete();
        if($result){
            return json(['code'=>'1001','message'=>'删除成功']);
        }else{
        	return json(['code'=>'1002','message'=>'删除失败']);
        }

	}














}
