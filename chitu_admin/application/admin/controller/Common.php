<?php
/**
 * description: 公共方法
 * auther : liyuan
 */
namespace app\admin\controller;
use think\Controller; 	// 使用控制器
use think\Db;			// 使用数据库操作
use think\Request;		// 使用请求

class Common 
{	
    /**
     * 根据输入字段模糊查询平台所有可用的项目公司
     * @param {string} term 关于公司的模糊字段
     * @return {array} select 满足该字段的所有平台可用公司
     */
    public function getmanager() {
        $search = input('term'); // 公司字段
        // 公司
        if(!empty($search)){
           $where['name'] = ['like','%'.$search.'%'];
        }
        // 查询条件 1干线2提货3项目
        $where['type'] = 3;
        // 查询条件 status 1开通2删除
        $where['status'] = 1;
        // 查询
        $select = DB::table('ct_company')->where($where)->select();
        // 输出可用自动补全插件所用的字段格式
        $com = array();
        foreach ($select as $value) {
            $com[] = array(
                'id'=>$value['cid'],
                'label'=>$value['name']
            );
        }
        echo  json_encode($com);
    }

    /**
     * 根据输入字段模糊查询平台所有可用的承运公司
     * @param {string} term 关于公司的模糊字段
     * @return {array} select 满足该字段的所有平台可用公司
     */
    public function getcarriers(){
        $serch = input('term');
        $arr = array();
        $where['type'] = ['neq',3];
        $where['status'] = 1;
        $reslut = DB::table('ct_company')
                    ->where('name','like',"%".$serch."%")
                    ->where($where)
                    ->order('status','asc')
                    ->select();
        foreach ($reslut as $value) {
            $arr[] = array(
                    'id'=>$value['cid'],
                    'label'=>$value['name']
                );
        }
        echo json_encode($arr);
    }

    /**
     * 查询承运公司下面的员工
     * @param Int $cid  [承运公司id]
     * @param Int $type [类型 1司机 2调度 3管理 不传则返回所有]
     * @return {array} select 满足该字段的所有数据
     */
    public function getCarrierStaff()
    {
        // 公司id
        $cid = input('cid');
        // type 1司机 2调度 3管理
        $type = input('type');
        // 判断参数
        if(empty($cid)){
            return json(['code'=>false,'message'=>'参数错误']);;
        }
        // 筛选条件 公司id
        $where['companyid'] = $cid;
        // 筛选条件 未删除
        $where['delstate'] = 1;
        // 如果有则筛选
        if(!empty($type)){
            $where['type'] = $type;
        }
        // 查询数据
        $result = DB::table('ct_driver')->where($where)->select();
        // 定义储存员工的数据
        $staffArr = array();
        // 遍历数据 筛选出需要的数据
        foreach ($result as $key => $value) {
            // 用户id
            $staffArr[$key]['id'] = $value['drivid'];
            // 用户名称
            $staffArr[$key]['name'] = $value['realname'] ? $value['realname'] : $value['username'];
        }
        // 判断是否有人
        if(empty($staffArr)){
            switch ($type) {
                case '1':
                    $type = '司机';
                    break;
                case '2':
                    $type = '调度';
                    break;
                case '3':
                    $type = '管理';
                    break;
                default:
                    $type = '员工';
                    break;
            }
            return json(['code'=>false,'message'=>'该公司下还没有'.$type.'，请先添加']);
        }
        // 返回数据
        return json(['code'=>true,'message'=>'查询成功','data'=>$staffArr]);
    }   

    /**
     * 根据输入的模糊字段查询平台可用的个体司机
     * @return [type] [description]
     */
    public function getonedriver(){
        $serch = input('term');
        $arr = array();
        $reslut = DB::table('ct_driver')
                    ->where('realname','like',"%".$serch."%")
                    ->where(array('delstate' => 1,'type' => 1, 'driver_grade' => 1))
                    ->order('drivid','asc')
                    ->select();
        foreach ($reslut as $value) {
            $arr[] = array(
                    'id'=>$value['drivid'],
                    'label'=>$value['realname']
                );
        }
        echo json_encode($arr);
    }

    /**
     * 根据输入的模糊字段查询平台可用的个体司机
     * 根据手机号查找
     * @auther: 李渊
     * @date: 2018.9.26
     * @param  [type] [name] [<description>]
     * @return [type] [description]
     */
    public function getonePhone()
    {
        $serch = input('term');
        $arr = array();
        $reslut = DB::table('ct_driver')
                    ->where('mobile','like',"%".$serch."%")
                    ->where(array('delstate' => 1,'type' => 1, 'driver_grade' => 1))
                    ->order('drivid','asc')
                    ->select();
        foreach ($reslut as $value) {
            $arr[] = array(
                    'id'=>$value['drivid'],
                    'label'=>$value['mobile']
                );
        }
        echo json_encode($arr);
    }

    /**
     * @Date: 2018-05-02
     * @Description: 承运公司是否存在 存在则false
     * @Auther: 李渊
     * @return [bool] 存在false 不存在true
     */
    public function checkcarries(){
        $get_post = Request::instance()->post();
        $com = DB::table('ct_company')
                ->where(array('name'=>['eq',$get_post['name']],'type'=>['neq',3],'status'=>1))
                ->find();
        if (!empty($com)) {
            return false;
        }else{
            return true;
        }
    }

    /**
     * @Date: 2018-05-02
     * @Description: 检查用户手机号是否已经存在
     * @Auther: 李渊
     * @return [bool] 存在false 不存在true
     */
    public function checkphone(){
        $get_post = Request::instance()->post();
        $phone = DB::table('ct_user')->where(array('phone'=>$get_post['phone'],'delstate'=>1))->find();
        if (!empty($phone)) {
            return false;
        }else{
            return true;
        }
    }

    /**
     * @Date: 2018-05-02
     * @Description: 检查承运手机号是否已经存在
     * @Auther: 李渊
     * @return [bool] 存在false 不存在true
     */
    public function checkmobile(){
        $get_post = Request::instance()->post();
        $phone = DB::table('ct_driver')->where(array('mobile'=>$get_post['phone'],'delstate'=>1))->find();
        if (!empty($phone)) {
            return false;
        }else{
            return true;
        }
    }

    // 筛选承运商公司对应车型的车牌号
    public function getcarrierscarnum()
    {
        $companyid = input('companyid'); // 承运商id
        $carid = input('carid'); // 车型id

        $select  = DB::table('ct_carcategory')->where(array('com_id'=>$companyid,'carid'=>$carid))->select();
        
        echo  json_encode($select);
    }

    /**
     * 筛选承运商公司操作
     */
    public function search(){
        $serch = input('term');
        $arr = array();
        $where['type'] = ['neq',3];
        $where['status'] = 1;
        $reslut = DB::table('ct_company')
                    ->where('name','like',"%".$serch."%")
                    ->where($where)
                    ->order('status','asc')
                    ->select();
        foreach ($reslut as $value) {
            $arr[] = array(
                    'id'=>$value['cid'],
                    'label'=>$value['name']
                );
        }
        echo json_encode($arr);
    }

    /**
     * 筛选用户信息
     */
    public function search_user_phone(){
        $search = input('term');
        $array = array();
        $result = Db::table('ct_user')->where(array('phone'=>['like','%'.$search.'%'],'userstate'=>'1'))->order('userstate','asc')->select();
        if (!empty($result)) {
            foreach ($result as $value) {
                $array[] = array(
                        'id'=>$value['uid'],
                        'label'=>$value['phone'],
                        'name'=>$value['username'],
                        'value'=>$value['realname']
                    );
            }
        }
       
        echo json_encode($array);
    }

    /**
     * 筛选不是定制公司用户信息
     */
    public function search_user_company(){
        $search = input('term');
        $array = array();
        $result = Db::table('ct_user')
                    ->alias('u')
                    ->join('ct_company c','c.cid=u.lineclient','LEFT')
                    ->field('u.uid,u.phone,u.username,u.realname,c.cid,c.name')
                    ->where(array('phone'=>['like','%'.$search.'%'],'u.delstate'=>1))
                    ->order('userstate','asc')->select();
        if (!empty($result)) {
            foreach ($result as $value) {
                $array[] = array(
                        'id'=>$value['uid'],
                        'label'=>$value['phone'],
                        'name'=>$value['username'],
                        'value'=>$value['realname'],
                        'label1'=>$value['name'],
                        'label2'=>$value['cid']
                    );
            }
        }
       
        echo json_encode($array);
    }
    
    
    /**
     * @Auther: 李渊
     * @Date: 2018-05-02
     * @Description: 三级联动获取地址表数据
     * @return [type] [description]
     */
    public function getaddress(){
        // 对应的省市id
        $id = Request::instance()->post('id'); 
        // 查找对应id的数据
        $result =  DB::table('ct_district ')->where(array('parent_id'=>$id))->select();

        
        return $result;  
    }

    /**
     * 获取城配已开通城市
     * @Auther: 李渊
     * @Date: 2018.8.13
     * @return [type] [description]
     */
    public function openCity()
    {
        // 查询城配已开通城市
        $city = DB::table('ct_city_cost')->where('delstate',1)->select();
        // 
        $cityArr = array();
        // 遍历数据获取城市名称
        foreach ($city as $key => $value) {
            $cityArr[$key]['id'] = $value['c_city'];  
            $cityArr[$key]['name'] = addresidToName($value['c_city']);   
        }
        // 返回数据
        return json(['code'=>true,'message'=>'查询成功','data'=>$cityArr]);
    }
}
