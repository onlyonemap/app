<?php 

/**
 *  空控制器执行文件
 */
namespace app\backstage\controller;
use think\Request;

class Error
{

	public function in(Request $request){
		//获取请求的控制器
		$result = $request->controller();
		return $this->data($result);
	}

	public function data($result){
		return "空控制器：".$result;
	}

  	//空操作
	public function _empty(){
		echo 'none';
	}


}




 ?>