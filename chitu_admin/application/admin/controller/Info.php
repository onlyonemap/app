<?php
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Info  extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//    }
  
    /**
     * 已开通城配配城市列表
     * @Auther: 李渊
     * @Date: 2018.8.7
     * @param   [search]    [筛选字段 城市名称]
     * @return  [type]      [description]
     */
    public function costrange(){
        // 筛选字段 城市名称
        $search = input('search');
        // 筛选字段
        $where ='';
        // 页码
        $pageParam    = ['query' =>[]];
        // 判断是否筛选
        if (!empty($search)) {
            $where['name'] = ['like','%'. $search.'%'];
            $pageParam['query']['search'] = $search;
        }
        // 查询数据
        $result = DB::table('ct_city_cost')
            ->field('a.*,b.name')
            ->alias('a')
            ->join('ct_district b','b.id=a.c_city')
            ->order(array('a.delstate'=>'asc','a.cost_id'=>'desc'))
            ->where($where)
            ->paginate(10,false, $pageParam);
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list', $result);
        return view('info/rangeindex');
    }
  
    /**
     * to 添加城配运输城市
     * @Auther: 李渊
     * @Date: 2018.8.7
     * @return  [type]      [description]
     */
    public function addrange(){
        return view('info/addrange');
    }

    /**
     * 添加城配运输城市
     * @Auther: 李渊
     * @Date: 2018.8.7
     * @return  [type]      [description]
     */
    public function postrange(){
        $post_data = Request::instance()->post();
        // 获取添加的城市
        $cityid = $post_data['tcity'];
        // 查询改城市 判断城市是否存在不能重复添加
        $searchDate = DB::table('ct_city_cost')->where('c_city',$cityid)->find(); 
        if (!empty($searchDate)) {
            $this->error('该城市已存在!!!');
        }
        // 添加城市
        $data['c_city'] = $cityid;
        // 插入数据
        $insertID = DB::table("ct_city_cost")->insertGetId($data);
      
        if ($insertID) {
            $content = "添加了新城市市配区间编号为：".$insertID."信息";
            $this->hanldlog($this->uid,$content);
            $this->success("添加成功！！",'info/costrange');
        }else{
            $this->error("添加失败!!");
        }
    }

    /**
     * 修改城配运输城市
     * @Auther: 李渊
     * @Date: 2018.8.7
     * @return  [type]      [description]
     */
    public function edit(){
        // 索引id
        $id = input('id');
        // 查询城市
        $result = DB::table('ct_city_cost')->where('cost_id',$id)->find();
        // 数据
        $data['name'] = addresidToName($result['c_city']);
        // 数据
        $data['cost_id'] = $id;
        // 返回数据
        $this->assign('list',$data);
        // 渲染视图
        return view('info/edit');
    }

    /**
     * 删除开通城市
     */
    public function delcity(){
        $get_data = Request::instance()->get();
        // 定义状态
        $data['delstate'] = 2;
        // 修改状态
        $del = Db::table('ct_city_cost')->where('cost_id',$get_data['id'])->update($data); 
        // 判断是否删除成功
        if ($del) {
            $content = "删除了城市市配区间编号为：".$get_data['id']."信息";
            $this->hanldlog($this->uid,$content);
            $this->success('删除成功!!','info/costrange');
        }else{
            $this->error('删除失败!!');
        }
    }

    

    /**
     * 统计APP端用户启动次数
     * @auther 李渊
     * @date 2018.6.14
     * @return [type] [description]
     */
    public function userAppstart(){
        // 现在的时间
        $timestamp = time();
        // 今天开始时间
        $today_start = mktime(0,0,0,date("m",time()),date("d",time()),date("Y",time()));
        // 昨天开始时间
        $start = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        // 昨天结束时间
        $end =mktime(0,0,0,date('m'),date('d'),date('Y'))-1; 
        // 当周开始时间
        $toweek_start = strtotime(date('Y-m-d', strtotime("this week Monday", $timestamp)));  
        // 当周结束时间
        $toweek_end = strtotime(date('Y-m-d', strtotime("this week Sunday", $timestamp))) + 24 * 3600 - 1 ;
        // 当月开始时间
        $tomoth_start = mktime(0, 0, 0, date('m'), 1, date('Y'));  
        // 当月结束时间
        $tomoth_end = mktime(23, 59, 59, date('m'), date('t'), date('Y')); 
        // 当年开始时间
        $toyear_start =mktime(0, 0, 0, 1, 1, date('Y'));  
        // 当年结束时间
        $toyear_end = mktime(23, 59, 59, 12, 31, date('Y'));
        // 查询数据
        $result_com = DB::field('count(userid),data_times,b.uid,b.realname,b.username,b.phone')
                        ->table('ct_app_activate')
                        ->alias('a')
                        ->join('ct_user b','b.uid=a.userid')
                        ->where('usertype','2')
                        ->group('userid')
                        ->select();
        $where_now['starttime'] = array(array('gt',$today_start),array('lt', $timestamp));
        $where_today['starttime'] = array(array('gt',$start),array('lt', $end));
        $where_toweek['starttime'] = array(array('gt',$toweek_start),array('lt', $toweek_end));
        $where_tomoth['starttime'] = array(array('gt',$tomoth_start),array('lt', $tomoth_end));
        $where_toyear['starttime'] = array(array('gt',$toyear_start),array('lt', $toyear_end));
        $i=1;
        if (!empty($result_com)) {
            foreach ($result_com as $key => $value) {
                $data['userid'] = $value['uid'];
                $result_com[$key]['ID'] = $i;
                $result_com[$key]['realname'] = $value['realname'] == '' ? $value['username'] : $value['realname'];
                $result_com[$key]['tonow'] = DB::table('ct_app_activate')->where($where_now)->where($data)->sum('data_times');
                $result_com[$key]['today'] = DB::table('ct_app_activate')->where($where_today)->where($data)->sum('data_times');
                $result_com[$key]['toweek'] = DB::table('ct_app_activate')->where($where_toweek)->where($data)->sum('data_times');
                $result_com[$key]['tomoth'] = DB::table('ct_app_activate')->where($where_tomoth)->where($data)->sum('data_times');
                $result_com[$key]['toyear'] = DB::table('ct_app_activate')->where($where_toyear)->where($data)->sum('data_times');
                $result_com[$key]['total'] = DB::table('ct_app_activate')->where($data)->sum('data_times');
                $i++;
            }
        }
        $this->assign('list',$result_com);
        return view('info/userAppstart');
    }

    /**
     * 统计APP端司机启动次数
     * @auther 李渊
     * @date 2018.6.13
     * @return [type] [description]
     */
    public function total_activate(){
        // 现在的时间
        $timestamp = time();
        // 今天开始时间
        $today_start = mktime(0,0,0,date("m",time()),date("d",time()),date("Y",time()));
        // 昨天开始时间
        $start = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        // 昨天结束时间
        $end =mktime(0,0,0,date('m'),date('d'),date('Y'))-1; 
        // 当周开始时间
        $toweek_start = strtotime(date('Y-m-d', strtotime("this week Monday", $timestamp)));  
        // 当周结束时间
        $toweek_end = strtotime(date('Y-m-d', strtotime("this week Sunday", $timestamp))) + 24 * 3600 - 1 ;
        // 当月开始时间
        $tomoth_start = mktime(0, 0, 0, date('m'), 1, date('Y'));  
        // 当月结束时间
        $tomoth_end = mktime(23, 59, 59, date('m'), date('t'), date('Y')); 
        // 当年开始时间
        $toyear_start =mktime(0, 0, 0, 1, 1, date('Y'));  
        // 当年结束时间
        $toyear_end = mktime(23, 59, 59, 12, 31, date('Y'));
        // 查询数据
        $result_com = DB::field('count(userid),data_times,b.drivid,b.realname,b.username,b.mobile')
                        ->table('ct_app_activate')
                        ->alias('a')
                        ->join('ct_driver b','b.drivid=a.userid')
                        ->where('usertype','1')
                        ->group('userid')
                        ->select();
        $where_now['starttime'] = array(array('gt',$today_start),array('lt', $timestamp));
        $where_today['starttime'] = array(array('gt',$start),array('lt', $end));
        $where_toweek['starttime'] = array(array('gt',$toweek_start),array('lt', $toweek_end));
        $where_tomoth['starttime'] = array(array('gt',$tomoth_start),array('lt', $tomoth_end));
        $where_toyear['starttime'] = array(array('gt',$toyear_start),array('lt', $toyear_end));
        $i=1;
        if (!empty($result_com)) {
            foreach ($result_com as $key => $value) {
                $data['userid'] = $value['drivid'];
                $result_com[$key]['ID'] = $i;
                $result_com[$key]['realname'] = $value['realname'] == '' ? $value['username'] : $value['realname'];
                $result_com[$key]['tonow'] = DB::table('ct_app_activate')->where($where_now)->where($data)->sum('data_times');
                $result_com[$key]['today'] = DB::table('ct_app_activate')->where($where_today)->where($data)->sum('data_times');
                $result_com[$key]['toweek'] = DB::table('ct_app_activate')->where($where_toweek)->where($data)->sum('data_times');
                $result_com[$key]['tomoth'] = DB::table('ct_app_activate')->where($where_tomoth)->where($data)->sum('data_times');
                $result_com[$key]['toyear'] = DB::table('ct_app_activate')->where($where_toyear)->where($data)->sum('data_times');
                $result_com[$key]['total'] = DB::table('ct_app_activate')->where($data)->sum('data_times');
                $i++;
            }
        }
        $this->assign('list',$result_com);
        return view('info/total_activate');
    }
  
}
