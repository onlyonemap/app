<?php
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Index  extends Controller
{
	function __construct(){
        parent::__construct();

    }

    /**
     * 首页
     * @Auther: 李渊
     * @Date: 2018.8.1
     * @return [type] [description]
     */
    public function index() {

        $uid = Session::get('admin_id','admin_mes');
        if ($uid =='') {
           $this->redirect("index/login");
        }
        $auth = model('admin')->field('qxz,admin')->where('aid',$uid)->find();
        $authstr =  model('authstr')->where('qid',$auth->qxz)->find();
        $system = model('auth_rule')->field('id,pid,name,title')->where('pid',0)->select();
        $this->assign([
            'auth'=>$auth,
            'authstr'=>$authstr?$authstr->quanxianstr:'',
            'system'=>$system
        ]);
        return view('index/index'); 
    }
    
    /**
     * 获取未处理的信息
     * 包括以下信息 未接单的订单 注册未认证的信息
     * @Auther: 李渊
     * @Date: 2018.8.1
     * @return [type] [description]
     */
    public function getcheckmsg() {
        // 市内配送订单 客户三天内下未接单订单
        $starttime = strtotime('-3day');
        $endtime = time();
        $where['addtime'] = array(array('gt',$starttime),array('lt', $endtime));
        $where['paystate'] = ['IN','2,4'];
        $where['state'] = 1;
        $cityordernum = DB::table('ct_city_order')->where($where)->count();
        $result['cityordernum'] = $cityordernum;
        // 整车订单
        $starttime = strtotime('-3day');
        $endtime = time();
        $where1['addtime'] = array(array('gt',$starttime),array('lt', $endtime));
        $where1['paystate'] = ['IN','2,4,5'];
        $where1['orderstate'] = 1;
        $allordernum = DB::table('ct_userorder')->where($where1)->count();
        $result['allordernum'] = $allordernum;
        // 定制订单
        $starttime = strtotime('-3day');
        $endtime = time();
        $where2['addtime'] = array(array('gt',$starttime),array('lt', $endtime));
        $where2['paystate'] = 2;
        $where2['orderstate'] = 1;
        $lineordernum = DB::table('ct_shift_order')->where($where2)->count();
        $result['lineordernum'] = $lineordernum;
        // 司机车辆未认证
        $starttime = strtotime('-3day');
        $endtime = time();
        $where3['addtime'] = array(array('gt',$starttime),array('lt', $endtime));
        $where3['status'] = 1;
        $car = DB::table('ct_carcategory')->where($where3)->count();
        $result['car'] = $car;
        // 司机驾驶证未认证
        $starttime = strtotime('-3day');
        $endtime = time();
        $where4['addtime'] = array(array('gt',$starttime),array('lt', $endtime));
        $where4['delstate'] = 1;
        $where4['carstatus'] = 1;
        $drivernum = DB::table('ct_driver')->where($where4)->count();
        $result['drivernum'] = $drivernum;
        // 零担订单
        $starttime = strtotime('-3day');
        $endtime = time();
        $where5['addtime'] = array(array('gt',$starttime),array('lt', $endtime));
        $where5['orderstate'] = 2;
        $where5['paystate'] = 2;
        $useorder = DB::table('ct_order')->where($where5)->count();
        $result['useorder'] = $useorder;

        echo json_encode($result);
    }

    /**
     * 主页统计信息
     * @Auther: 李渊
     * @Date: 2018.8.1
     * @return [type] [description]
     */
    public  function main(){
        return $this->fetch();
    }
   
    public function login(){
        //没有继承controller用view
        return view('index/login');
    }

    public function login_send(){
        $admin_name = Request::instance()->post('admin_name/s');
        $admin_password = input("admin_password/s");
//        var_dump($admin_password);

        $where['username'] = $admin_name;
        $where['password'] = md5($admin_password.'ct888');
//        var_dump($where);
//        exit();
        $where['pstate'] = 1;
        $result  = Db::table('ct_admin')->where($where)->find();
        if($result){
            Session::set('admin_id',$result['aid'],'admin_mes');
           $this->redirect('admin/index/index');
        }else{
             $this->error('用户名或密码错误，请重新输入...');
        }
    }

    //退出
    public function logout(){
        session::clear('admin_mes');
        $this->redirect("index/login");
        
    }

    public function sendordernotice(){
       /* $count1 = DB::table('ct_order')
            ->alias('a')
            ->join('ct_lineorder b','b.orderid=a.oid')
            ->where(array('paystate'=>'2','affirm'=>'1'))
            ->count('oid');
        $count2 = Db::table('ct_userorder')
                    ->where(array('orderstate'=>'1','paystate'=>'2'))
                    ->count('uoid');
        $count3 = Db::table('ct_rout_order')
                    ->where(array('status'=>'1'))
                    ->count('rid');*/
        //零担订单
        $list1 = DB::table('ct_order')
            ->alias('a')
            ->join('ct_lineorder b','b.orderid=a.oid')
            ->where(array('paystate'=>'2','affirm'=>'1'))
            ->field('a.ordernumber,a.oid')
            
            ->select();
        foreach ($list1 as $key => $value) {
            $list1[$key]['type'] = 1;
            $list1[$key]['id'] = $value['oid'];
        }
        //整车订单
        $list2 = Db::table('ct_userorder')
                    ->where(array('orderstate'=>'1','paystate'=>'2'))
                    ->field('ordernumber,uoid')
                   
                    ->select();
        foreach ($list2 as $key2 => $v) {
            $list2[$key2]['type'] = 2;
             $list2[$key2]['id'] = $v['uoid'];
        }
        //市配订单
        $list3 = Db::table('ct_rout_order')
            ->where(array('status'=>'1'))
            ->field('rid')
            
            ->select();
         foreach ($list3 as $key3 => $val) {
            $list3[$key3]['type'] = 3;
            $list3[$key3]['id'] = $val['rid'];
        }
         $list = array_merge($list1,$list2,$list3);
        //echo "<pre/>";
        // print_r($list);exit();
        //$count = $count1+$count2+$count3;
       // $data = array('num'=>$count);
        if (!empty($list)) {
            return json(['datanum'=>true,'data'=>$list]);
        }else{
            return json(['datanum'=>false,'data'=>'']);
        }
        
    }

    public function test(){
        $auth = new \Auth\Auth(); 
        $tree = new \Datatree\Datatree(); 
        $request = Request::instance();  
        if (!$auth->check($request->module() . '-' . $request->controller() . '-' . $request->action(), 1)) {// 第一个参数是规则名称,第二个参数是用户UID  
            echo "<xx>";
             return array('status'=>'error','msg'=>'有权限！');
            $this->error('你没有权限');
        }  
    }
    public function register(){
       return $this->fetch();
    }

    public function doregister(Request $request){
        $data = $request->post();
        $res['addtime'] = time();
        $res['rolenumber'] = '3';
        $res['username'] = $data['admin_name'];
        $res['password'] = md5($data['user_password'].'ct888');
        $res['realname'] = $data['realname'];
        $res['tel'] = $data['phone'];
        $res['email'] = $data['email'];
        $res['numbers'] = 'ct_'.rand(00000,99999);
        $res['weixin'] = $data['phone'];
        $res['role'] = '市场部经理';
        $res['grade'] = '3';
        var_dump($data['user_password']);
        var_dump($res);

        $user = DB::table('ct_admin')->insertGetId($res);
        if ($user) {
            echo json_encode(['code'=>'1001','message'=>'注册成功']);
        }else{
            echo json_encode(['code'=>'1002','message'=>'注册失败']);
        }
    }



}
