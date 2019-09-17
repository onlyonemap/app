<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Manager  extends Base
{
	function __construct(){
        parent::__construct();
        $this->uid = Session::get('admin_id','admin_mes');
        $this->if_login();
    }

    /**
     * 项目公司列表
     * @Auther: 李渊
     * @Date: 2018.7.9
     * @param   [String] search [搜索类型 公司名称]
     * @return  [type]          [description]
     */
    public function index(){
        $search = input('search');
        $pageParam    = ['query' =>[]];
        if(!empty($search)){
            $where['name'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search;
        }
        // 1开通2删除
        $where['status'] = 1; 
        // 1干线2提货3项目
        $where['type'] = 3; 
        // 查询所有开通的项目客户公司
        $uid= $this->uid;
        $grade = Db::table('ct_admin')->field('grade')->where('aid',$uid)->find();
        if($grade['grade'] == 1){
            $select = DB::table('ct_company')->where($where)->order('cid desc')->paginate(10,false, $pageParam);
        }else{
            $select = DB::table('ct_company')->where($where)->where('aid',$uid)->order('cid desc')->paginate(10,false, $pageParam);
        }
        // 查询每个公司下的业务员和公司的员工
        $result = $select->toArray();            
        $newArr  = array();
        foreach ($result['data'] as $value) {
            // 查询该项目公司的赤途业务员
            $busid =  DB::table('ct_business')->where('cid',$value['cid'])->max('sort');
            $busine = DB::table('ct_business')->where(array('cid'=>$value['cid'],'sort'=>$busid))->find();
            $amin = DB::table('ct_admin')->where('aid',$busine['aid'])->find();
            $value['aminname'] = $amin['realname'];
            // 查询该项目公司的员工
            $usewhere['lineclient'] = $value['cid'];
            $usewhere['delstate'] = 1; // 未注销
            $value['alluser'] = DB::table('ct_user')->where($usewhere)->select();
            $newArr[]=$value;
        }
        // 页码 页面输出
        $page =  $select->render();
        $this->assign('page',$page);
    	$this->assign('list',$newArr);
    	return view('manager/index');
    }

    /**
     * 项目公司详情
     * @Auther: 李渊
     * @Date: 2018.7.9
     * @param   [Int]   cid [公司id]
     * @return  [type]      [description]
     */
    public function detail() {
        $cid = input('cid'); // 公司id
        // 查询公司信息
        $select = DB::table('ct_company')->where('cid',$cid)->find();
        // 输出公司地址
        $select['address'] = detailadd($select['provinceid'],$select['cityid'],$select['areaid']).$select['address'];
        // 查询所有公司员工
        $user = DB::table('ct_user')
                ->where(array('lineclient'=>$cid,'delstate'=>1))
                 ->order('user_grade','desc')
                ->select();
        
        $select['user'] =  $user;
        $this->assign('list',$select);
        return view('manager/detail');
    }  

    /**
     * 前往添加项目公司信息页面
     * @Auther: 李渊
     * @Date: 2018.7.9
     * @return [type] [description]
     */
    public function addmanager(){
        $where['pstate'] = 1; // 平台员工在线
        $result = DB::table('ct_admin')->where($where)->select();
        $this->assign('list',$result);
        return view('manager/addmanager');
    }

    /**
     * 添加项目客户信息
     * @Auther: 李渊
     * @Date: 2018.7.9
     * @return [type] [description]
     */
    public function addmessage(){
        $postdate = Request::instance()->post();
        // 公司名称
        $add_data['name'] = $postdate['name'];
        // 公司额度
        $add_data['credit'] = $postdate['credit'] < 0 ? 0 : $postdate['credit'];
        // 1干线2提货3项目
        $add_data['type'] = 3;
        // 地址
        if ($postdate['province'] !='0') {
            $add_data['provinceid'] = $postdate['province'];
            $add_data['cityid'] = $postdate['city'];
            $add_data['areaid'] = $postdate['area'];
            $add_data['address'] = $postdate['addinfo'];
        }
        
        // 插入公司余额
        $add_data['money'] = $postdate['credit'];
        // 插入公司添加时间
        $add_data['addtime'] = time();
        // 插入公司信息
        $add = DB::table("ct_company")->insertGetId($add_data);
        // 业务员业务添加
        $business['aid'] = $postdate['adminid'];
        $business['sort'] = 1;
        $business['starttime'] = time();
        $business['cid'] = $add;
        $isadd = DB::table("ct_business")->insert($business);

        if($add){
            $content = "添加了 ".$postdate['name'];
            $this->hanldlog($this->uid,$content);
            $this->success("添加成功",'manager/index');
        }else{
            $this->error("添加失败");
        }
    }

    /**
     * 前往更新项目公司信息页面
     * @Auther: 李渊
     * @Date: 2018.7.9
     * @return [type] [description]
     */
    public function updatemanager(){
    	$id = input('cid');
        // 查询公司信息
    	$select  = DB::table('ct_company')->where('cid',$id)->find();
        // 输出地址
    	$select['line'] = detailadd($select['provinceid'],$select['cityid'],$select['areaid']).' '.$select['address'];
        // 输出业务员信息
        $busid =  DB::table('ct_business')->where('cid',$select['cid'])->max('sort');
        $busine = DB::table('ct_business')->where(array('cid'=>$select['cid'],'sort'=>$busid))->find();
        $amin = DB::table('ct_admin')->where('aid',$busine['aid'])->find();
        $select['aminname'] = $amin['realname'];
        // 输出所有平台员工
        $where['pstate'] = 1; // 平台员工在线
        $result = DB::table('ct_admin')->where($where)->select();
        $this->assign('adminlist',$result);

    	$this->assign('list',$select);
    	return view('manager/updatemanager');
    }
    
    /**
     * 更新项目客户信息
     * @Auther: 李渊
     * @Date: 2018.7.9
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function updateManagerInfo() {
        $postdate = Request::instance()->post();
        // 公司名称
        $update['name'] = $postdate['name'];
        // 获取公司id
        $companyId = $postdate['cid'];
        // 公司额度
        $creditLine = $update['credit'] = $postdate['credit'];
        // 地址
        if ($postdate['province'] !='0') {
            $update['provinceid'] = $postdate['province'];
            $update['cityid'] = $postdate['city'];
            $update['areaid'] = $postdate['area'];
            $update['address'] = $postdate['addinfo'];
        }

        // 查询公司信息控制信用额度
        $company = DB::table('ct_company')->where('cid',$companyId)->find();
        // 公司原有的信用额度
        $oldCredit = $company['credit'];
        // 公司剩余的信用额度
        $overCredit = $company['money'];
        // 公司使用的信用额度
        $useCredit = $oldCredit - $overCredit;
        // 如果新的信用额度小于已经使用的额度则不能修改成功
        if($creditLine < $useCredit){
            $this->error("修改失败,信用额度小于已经使用的额度");
            exit();
        }
        // 如果新的信用额度大于已经使用的额度则执行修改动作
        $update['credit'] = $creditLine;
        $update['money'] = $overCredit+($creditLine-$oldCredit);

        // 执行修改动作
        $isUpdate = DB::table('ct_company')->where('cid',$companyId)->update($update);
        if ($isUpdate) {
            $this->success('修改成功','manager/index');
        }else{
            $this->error("修改失败");
        }
    }

    /**
     * 更新项目业务员
     * @Auther: 李渊
     * @Date: 2018.8.10
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function updateSalesman()
    {
        $postdate = Request::instance()->post();
        // 获取公司id
        $companyId = $postdate['cid'];
        // 业务员业务修改
        if($postdate['adminid'] != ''){
            $max =  DB::table('ct_business')->where('cid',$postdate['cid'])->max('sort');
            $business['aid'] = $postdate['adminid'];
            $business['sort'] = $max + 1;
            $business['starttime'] = time();
            $business['cid'] = $postdate['cid'];
            $isadd = DB::table("ct_business")->insert($business);
            // 判断是否修改成功
            if ($isadd) {
                $this->success('修改成功','manager/index');
            }else{
                $this->error("修改失败");
            }
        }
    }

    /**
     * 项目公司 删除
     * @Auther: 李渊
     * @Date: 2018.7.9
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function delcom(){
        $get = Request::instance()->get();
        // 项目公司所有员工 2 删除
        $deluse = DB::table('ct_user')->where('lineclient',$get['cid'])->update(array('delstate'=>2));
        // 项目公司 2 删除
        $delcom = DB::table('ct_company')->where('cid',$get['cid'])->update(array('status'=>2));

        if($delcom || $deluse){
            $this->success('删除成功', 'manager/index');
        }else{
            $this->error('删除失败');
        }
    }

    /**
     * 项目客户升级为定制用户
     * @Auther: 李渊
     * @Date: 2018.7.9
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function upgrade(){
        $get = Request::instance()->get();
        $result = Db::table('ct_company')->where('cid',$get['cid'])->update(array('customer'=>2));
        $result2 = Db::table('ct_user')->where('lineclient',$get['cid'])->update(array('custom'=>2));
        if($result && $result2){
            $this->success('升级成功', 'manager/index');
        }else{
            $this->error('升级失败');
        }
    }
    
    /**
     * 公司改变信用额度
     * @Auther: 李渊
     * @Date: 2018.7.5
     * @param  [Int] $number [信用额度]
     * @param  [Int] $id     [公司id]
     * @return [type]         [description]
     */
    public function recharge(){
        // 获取新的信用额度
        $creditLine = input('number');
        // 获取公司id
        $companyId = input('id');
        // 查询公司信息
        $company = DB::table('ct_company')->where('cid',$companyId)->find();
        // 公司原有的信用额度
        $oldCredit = $company['credit'];
        // 公司剩余的信用额度
        $overCredit = $company['money'];
        // 公司使用的信用额度
        $useCredit = $oldCredit - $overCredit;
        // 如果新的信用额度小于已经使用的额度则不能修改成功
        if($creditLine < $useCredit){
            return json(['code'=>false,'message'=>'修改失败,信用额度小于已经使用的额度']);
            exit();
        }
        // 如果新的信用额度大于已经使用的额度则执行修改动作
        $updateDate['credit'] = $creditLine;
        $updateDate['money'] = $overCredit+($creditLine-$oldCredit);
        // 执行修改动作
        $update = DB::table('ct_company')->where('cid',$companyId)->update($updateDate);
        if ($update) {
            return json(['code'=>true,'message'=>'修改成功']);
        }else{
            return json(['code'=>false,'message'=>'修改失败']);
        }
    }
}