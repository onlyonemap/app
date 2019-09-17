<?php
namespace app\admin\controller;

use think\Controller;   //使用控制器
use think\Db;  //使用数据库操作
use think\Request; 
use think\Session;

class Warehouse extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//    }

    /**
     * 仓库列表
     * @auther: 李渊
     * @date: 2018.8.17
     * 输出时间段内的所有仓库或者某个公司没有传值则输出所有
     * @param  [String]  $search    [搜索字段 公司名称]
     * @param  [String]  $starttime [起点时间]
     * @param  [String]  $endtime   [结束时间]
     * @return [Array]  [满足条件的所有仓库数据]
     */
    public function index()
    {
        // 获取搜索的模糊字段
        $search = input('search');
        // 获取开始时间
        $stime = input('starttime');
        // 获取结束时间
        $etime = input('endtime');
        // 分页
        $pageParam    = ['query' =>[]];
        // 查询条件
        $where = '';
        // 判断搜索条件
        if(!empty($search)){
            $where['com_name'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search;
        }
        // 判断搜索开始时间和结束时间
        if (!empty($stime) && !empty($etime)) {
            // 获取开始时间戳 
            $starttime = strtotime(trim($stime).'00:00:00');
            // 获取结束时间戳
            $endtime = strtotime(trim($etime).'23:59:59');
            // 查询条件注册时间大于开始时间小于结束时间
            $where['addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            // 
            $pageParam['query']['starttime'] = $stime;
            // 
            $pageParam['query']['endtime'] = $etime;
        }
        // 查询仓库数据
        $result = Db::table('ct_warehouse')->where($where)->order('wid desc')->paginate(10);
        // 转化为数组
        $data = $result->toArray();
        // 定义输出数组
        $arr = array();
        // 遍历数据
        foreach ($data['data'] as $key => $value) {
            // 索引
            $arr[$key]['wid']  = $value['wid'];
            // com_name
            $arr[$key]['com_name']  = $value['com_name'];
            // 仓库名称
            $arr[$key]['housename']  =  $value['housename'];
            // 面积
            $arr[$key]['areanumber']  = $value['areanumber'];
            // 类型
            $arr[$key]['wtype']  = $value['wtype'] == '1' ? '仓储型' : '中转型';
            // 联系人
            $arr[$key]['cantact']  = $value['cantact'];
            // 联系方式
            $arr[$key]['telephone']  = $value['telephone'];
            // 仓库价格
            $arr[$key]['price']  = $value['price'];
            // 获取省市区
            $city = detailadd('',$value['cityid'],$value['areaid']);
            // 返回详细地址
            $arr[$key]['address']  = $city.$value['address'];
            // 添加时间
            $arr[$key]['addtime'] = $value['addtime'];
        }
        $page = $result->render();
        $this->assign('list',$arr);
        $this->assign('page',$page);
    	return view('warehouse/index'); 
    }

    /**
     * to 添加仓库
     * @auther: 李渊
     * @date: 2018.8.17
     * @return [type] [description]
     */
    public function addhouse(){
        return view('warehouse/addhouse');
    }
   
    /**
     * 添加仓库数据
     * @auther: 李渊
     * @date: 2018.8.17
     * @return [type] [description]
     */
    public function addmess(){
        $postdata = Request::instance()->post();        
        // 公司公司
        $data['com_name'] = $postdata['name'];
        // 城市id
        $data['cityid'] = $postdata['city'];
        // 区域id
        $data['areaid'] = $postdata['area'];
        // 详细地址
        $data['address'] = $postdata['addinfo'];
        // 联系人
        $data['cantact'] = $postdata['cantact'];
        // 联系方式
        $data['telephone'] = $postdata['telephone'];
        // 仓库名称
        $data['housename'] = $postdata['housename'];
        // 价格
        $data['price'] = $postdata['price'];
        // 面积(㎡)
        $data['areanumber'] = $postdata['areanumber'];
        // 备注
        $data['remarks'] = $postdata['remarks'];
        // 获取城市名称
        $city_str = $this->start_city($postdata['city']);
        // 获取区域
        $area_str = $this->start_city($postdata['area']);
        // 获取经纬度
        $local_action = bd_local($type='2',$city_str,$city_str.$area_str.$postdata['addinfo']);
        // 纬度
        $data['longitude'] = $local_action['lng'];
        // 经度
        $data['latitude'] = $local_action['lat'];
        // 添加时间
        $data['addtime'] =time();
        // 插入数据
        $houseid = Db::table('ct_warehouse')->insertGetId($data);
        // 
        if($houseid){
            $content = "添加了新仓库".$postdata['name'];
            $this->hanldlog($this->uid,$content);
            $this->success('新增成功', 'warehouse/index');
        }else {
            $this->error('新增失败');
        }
    }
    
    /**
     * to 添加仓库页面
     * @auther: 李渊
     * @date: 2018.8.17
     * @return [type] [description]
     */
    public function updatehouse(){
        // 获取索引id
        $id = input('id');
        // 查询数据
        $detail = Db::table('ct_warehouse')->where('wid',$id)->find();
        // 地址
        $detail['add_str'] = detailadd('',$detail['cityid'],$detail['areaid']).$detail['address'];
        $this->assign('list',$detail);
        return view('warehouse/updatehouse');
    }

    /**
     * 更新仓库数据
     * @auther: 李渊
     * @date: 2018.8.17
     * @return [type] [description]
     */
    public function updateMess()
    {
        $postdata = Request::instance()->post();     
        // 公司名称
        $data['com_name'] = $postdata['name'];
        // 地址和经纬度
        if($postdata['province'] !=0 && $postdata['province'] !=''){
            $data['areaid'] = $postdata['area'];
            $data['cityid'] = $postdata['city'];
            $data['address'] = $postdata['addinfo'];
            $city_str = $this->start_city($postdata['city']);
            $area_str = $this->start_city($postdata['area']);
            $local_action = bd_local($type='2',$city_str,$city_str.$area_str.$postdata['addinfo']);
            $data['longitude'] = $local_action['lng'];
            $data['latitude'] = $local_action['lat'];
        }
        // 联系人
        $data['cantact'] = $postdata['cantact'];
        // 联系电话
        $data['telephone'] = $postdata['telephone'];
        // 仓库名称
        $data['housename'] = $postdata['housename'];
        // 价格
        $data['price'] = $postdata['price'];
        // 面积
        $data['areanumber'] = $postdata['areanumber'];
        // 备注
        $data['remarks'] = $postdata['remarks'];
        // 更新数据
        $com = Db::table('ct_warehouse')->where('wid',$postdata['wid'])->update($data);
      
        if(isset($com)){
            $content = "修改了仓库".$postdata['name'];
            $this->hanldlog($this->uid,$content);
            $this->success('修改成功', 'warehouse/index');
        }else {
            $this->error('修改失败');
        }
    }

    /**
     * 删除仓库数据
     * @auther: 李渊
     * @date: 2018.8.17
     * @return [type] [description]
     */
    public function delhouse()
    {
        // 获取索引id
        $id = input('id');
        // 删除数据
        $del = Db::table('ct_warehouse')->delete($id);

        if($del){
            $content = "删除了仓库ID为：".$id;
            $this->hanldlog($this->uid,$content);
            $this->success('删除成功', 'warehouse/index');
        }else {
            $this->error('删除失败');
        }
    }
}
