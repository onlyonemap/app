<?php
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Cityconfig extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//    }

    /**
     * to 城配签约项目客户列表
     * @Auther: 李渊
     * @Date: 2018.8.13
     * @param [string] search [搜索字段 公司名称]
     * @return [type] [description]
     */
    public function contractList()
    {
    	// 搜索内容
        $search = input('search');
        // 页码
        $pageParam = ['query' =>[]];
        // 筛选字段
        $where['a.status'] = 1;
        // 如果搜索结果不为空则模糊查询真是姓名
        if(!empty($search)){
            $where['c.name'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search;
        }
        // 查询数据
        $result = DB::table("ct_city_company")
        		->alias('a')
        		->join('ct_company c','c.cid=a.cid')
        		->join('ct_district dis','dis.id=a.city_id')
        		->join('ct_company d','d.cid=a.appoint_cid')
        		->join('ct_driver driver','driver.drivid=a.appoint_driver')
        		->field('a.*,c.name,dis.name cityName,d.name driverName,driver.realname,driver.username')
        		->where($where)
        		->order('id','asc')
        		->paginate(10,false, $pageParam);
        // 分页
        $page =  $result->render();
        // 渲染数据
        $this->assign('page',$page);
        $this->assign('list',$result);

    	return view('cityconfig/contractList');
    }

    /**
     * to 城配签约项目客户添加页面
     * @Auther: 李渊
     * @Date: 2018.8.13
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function toaddContract()
    {
    	return view('cityconfig/addContract');
    }

    /**
     * 城配签约项目客户添加
     * @Auther: 李渊
     * @Date: 2018.8.13
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function addContract()
    {
    	// 获取签约公司
    	$companyname = input('companyname');
    	// 获取签约公司id
    	$data['cid'] = input('cid');
    	// 获取城配签约公司开通的城市
    	$data['city_id'] = input('city_id');
    	// 最低收费
    	$data['low_price'] = input('low_price');
    	// 包含门店
    	$data['low_door'] = input('low_door');
    	// 最高门店
    	$data['high_door'] = input('high_door');
    	// 多门店费
    	$data['moredoor_price'] = input('moredoor_price');
    	// 承运公司
    	$data['appoint_cid'] = input('appoint_cid');
    	// 承运公司
    	$data['appoint_driver'] = input('appoint_driver');
        // 承运最低收费
        $data['appoint_lowprice'] = input('appoint_lowprice');
        // 承运包含门店
        $data['appoint_lowdoor'] = input('appoint_lowdoor');
        // 承运最高门店
        $data['appoint_highdoor'] = input('appoint_highdoor');
        // 承运多门店费
        $data['appoint_moreprice'] = input('appoint_moreprice');

        // 判断是否重复插入城配城市
        $isData = Db::table('ct_city_company')->where(array('cid'=>$data['cid'],'status'=>1,'city_id'=>$data['city_id']))->find();
        // 如果有该城市
        if($isData){
            $this->error("该公司该城配城市已经添加过了，请选择其他城市");
        }
    	// 插入数据
    	$insert = Db::table('ct_city_company')->insert($data);
    	// 判断
    	if($insert){
            $content = "添加了城配签约公司:".$companyname;
            $this->hanldlog($this->uid,$content);
            $this->success('添加成功','cityconfig/contractList');
        }else{
            $this->error("添加失败");
        }
    }

    /**
     * to 城配签约项目客户修改
     * @Auther: 李渊
     * @Date: 2018.8.13
     * @param  string $id 	[索引id]
     * @return [type]       [description]
     */
    public function toupdateContract()
    {
    	// 获取id
    	$id = input('id');
    	// 查询数据
    	$result = Db::table('ct_city_company')->where('id',$id)->find();
    	// 查询公司数据
    	$company = Db::table('ct_company')->where('cid',$result['cid'])->find();
    	// 查询承运公司
    	$transport = Db::table('ct_company')->where('cid',$result['appoint_cid'])->find();
    	// 设置项目客户名称
    	$result['companyname'] = $company['name'];
    	// 承运公司名称
    	$result['transportName'] = $transport['name'];
    	// 渲染数据
        $this->assign('result',$result);

    	return view('cityconfig/updateContract');
    }

    /**
     * 城配签约项目客户修改
     * @Auther: 李渊
     * @Date: 2018.8.13
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function updateContract()
    {
    	// 获取索引id
    	$id = input('id');
    	// 获取签约公司id
    	$data['cid'] = input('cid');
    	// 获取城配签约公司开通的城市
    	$data['city_id'] = input('city_id');
    	// 最低收费
    	$data['low_price'] = input('low_price');
    	// 包含门店
    	$data['low_door'] = input('low_door');
    	// 最高门店
    	$data['high_door'] = input('high_door');
    	// 多门店费
    	$data['moredoor_price'] = input('moredoor_price');
    	// 承运公司
    	$data['appoint_cid'] = input('appoint_cid');
    	// 承运公司
    	$data['appoint_driver'] = input('appoint_driver');
        // 承运最低收费
        $data['appoint_lowprice'] = input('appoint_lowprice');
        // 承运包含门店
        $data['appoint_lowdoor'] = input('appoint_lowdoor');
        // 承运最高门店
        $data['appoint_highdoor'] = input('appoint_highdoor');
        // 承运多门店费
        $data['appoint_moreprice'] = input('appoint_moreprice');
    	// 修改数据
    	$update = Db::table('ct_city_company')->where('id',$id)->update($data);
    	// 判断
    	if($update){
    	    $content = "修改了索引为".$id."城配签约项目客户";
            $this->hanldlog($this->uid,$content);
            $this->success('修改成功','cityconfig/contractList');
    	}else{
    		$this->error("修改失败");
    	}
    }

    /**
     * 城配签约项目客户删除
     * @Auther: 李渊
     * @Date: 2018.8.13
     * @param  string $id [description]
     * @return [type]        [description]
     */
    public function deleteContract()
    {
    	// 获取id
    	$id = input('id');
    	// 修改状态
    	$update = Db::table('ct_city_company')->where('id',$id)->update(array('status' => 2));
    	// 判断状态
    	if ($update) {
    		$content = "删除了ID为".$id."城配签约项目客户";
            $this->hanldlog($this->uid,$content);
            $this->success('删除成功','cityconfig/contractList');
        }else{
            $this->error("删除失败");
        }
    }
}
