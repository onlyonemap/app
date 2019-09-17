<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Exception;
use think\Request;
use think\Session;
class Staff extends Base
{
	function __construct(){
        parent::__construct();
        $this->uid = Session::get('admin_id','admin_mes');
        $this->if_login();

    }

    /**
     * 平台员工列表
     * @auther 李渊
     * @date 2018.6.13
     * @return [type] [description]
     */
    public function index(){
        // 搜索内容
        $search = input('search');
        // 页码
        $pageParam    = ['query' =>[]];
        // 如果搜索结果不为空则模糊查询真是姓名
        if(!empty($search)){
            $user_where['realname'] = ['like','%'.$search.'%'];
            $pageParam['query']['search'] = $search;
        }
        // 查询条件 查询在线的员工
        $uid = $this->uid;
        $user_where['pstate'] = 1;
        $grade =Db::table('ct_admin')->field('grade')->where('aid',$uid)->find();
        if ($grade['grade'] == 1){
            // 查询数据
            $result = DB::table("ct_admin")->where($user_where)->order('aid','asc')->paginate(10,false, $pageParam);
        }else{
            // 查询数据
            $result = DB::table("ct_admin")->where($user_where)->where('aid',$uid)->order('aid','asc')->paginate(10,false, $pageParam);
        }

        // 分页
        $page =  $result->render();
        // 渲染数据
        $this->assign('grade',$grade);
        $this->assign('page',$page);
        $this->assign('list',$result);
    	return view('staff/list'); 
    }
    // 平台职员详情
    public function todetail() {
        $id = input('id');
        $result = DB::table('ct_admin')->where('aid',$id)->find();
        // 性别
        if($result['sex'] == 2){
            $result['sex'] = '女';
        }else{
            $result['sex'] = '男';
        }
        // 职位
        $rolenumber = $result['rolenumber'];
        switch ($rolenumber) {
            case 1: // 管理
                $result['role'] = '管理'.'-'.$result['role']; 
                break;
            case 2: // 运营
                $result['role'] = '运营'.'-'.$result['role']; 
                break;
            case 3: // 市场
                $result['role'] = '市场'.'-'.$result['role']; 
                break;
            case 5: // 客服
                $result['role'] = '客服'.'-'.$result['role']; 
                break;
            case 1: // 技术
                $result['role'] = '技术'.'-'.$result['role']; 
                break;
            default:
                break;
        }
        $this->assign("list",$result);
        return view('staff/detail');
    }
    // to 添加平台职员页面
    public function toadd(){
        return view('staff/add');
    }
    // to 更新平台职员页面
    public function toupdate(){
        $id = input('id');
        $result = DB::table('ct_admin')->where('aid',$id)->find();
        $this->assign("list",$result);
        return view('staff/update');
    }
    // 添加平台职员操作
    public function addmessage(){
        $post = Request::instance()->post();
        $user_date['username'] = $post['username'];
        $user_date['realname'] = $post['realname'];
        $user_date['tel'] = $post['phone'];
        $user_date['sex'] = $post['sex'];
        $user_date['numbers'] = $post['number'];
        $user_date['email'] = $post['email'];
        $user_date['weixin'] = $post['weixin'];
        $user_date['addtime'] = time();
        switch ($post['identity']) {
            case 'a':
                $idname = "管理员";
                $roleNumber = 1;
                break;
            case 'b':
                $idname = "总经理";
                $roleNumber = 1;
                break;
            case 'c':
                $idname = "总监";
                $roleNumber = 2;
                break;
            case 'd':
                $idname = "经理";
                $roleNumber = 2;
                break;
            case 'e':
                $idname = "总监";
                $roleNumber = 3;
                break;
            case 'f':
                $idname = "经理";
                $roleNumber = 3;
                break;
            case 'g':
                $idname = "总监";
                $roleNumber = 4;
                break;
            case 'h':
                $idname = "主管";
                $roleNumber = 4;
                break;
            case 'i':
                $idname = "专员";
                $roleNumber = 4;
                break;
            case 'j':
                $idname = "总监";
                $roleNumber = 5;
                break;
            case 'k':
                $idname = "前端";
                $roleNumber = 5;
                break;
            case 'o':
                $idname = "后端";
                $roleNumber = 5;
                break;
            case 'p':
                $idname = "UI";
                $roleNumber = 5;
                break;
            case 'q':
                $idname = "产品经理";
                $roleNumber = 5;
                break;
            default:
                # code...
                break;
        }
        $user_date['role'] = $idname;
        $user_date['rolenumber'] = $roleNumber;
        
        $user_date['password'] = md5('666666ct888');
        $user = DB::table('ct_admin')->insertGetId($user_date);
        if ($user) {
            $content = "添加了 ".$post['realname']." 新部门成员";
            $this->hanldlog($this->uid,$content);
            $this->success('添加成功','staff/index');
        }else{
            $this->error("添加失败");
        }
    }
    // 更新平台职员操作
    public function updatemessage(){
        $post = Request::instance()->post();
        $user_date['username'] = $post['username'];
        $user_date['realname'] = $post['realname'];
        $user_date['tel'] = $post['phone'];
        $user_date['sex'] = $post['sex'];
        $user_date['numbers'] = $post['number'];
        $user_date['email'] = $post['email'];
        $user_date['weixin'] = $post['weixin'];
       
        $user_date['addtime'] = time();

        
        if ($post['password'] !='') {
            $user_date['password'] = md5($post['password'].'ct888');
        }
        $user = DB::table('ct_admin')->where('aid',$post['aid'])->update($user_date);
        if (isset($user)) {
            $content = "修改了".$post['realname']."部门成员信息";
            $this->hanldlog($this->uid,$content);
            $this->success('修改成功','staff/index');
        }else{
            $this->error("修改失败");
        }
    }
    // 删除和恢复职员操作
    public function delete(){
        $get = Request::instance()->get();
        
        $delcom = DB::table('ct_admin')->where('aid',$get['id'])->update(array('pstate'=>2));
        if($delcom){
            $content = "删除了ID为".$get['id']."部门成员信息";
            $this->hanldlog($this->uid,$content);
            $this->success('删除成功', 'staff/index');
        }else{
            $this->error('删除失败');
        }
    }

    public function addauth($aid){
        $quanxian = model('quanxian')->field('title,qid')->where('status',0)->select();
        $admin = model('admin')->field('qxz,aid')->where('aid',$aid)->find();
        $this->assign(['quanxian'=>$quanxian,'admin'=>$admin]);
      return  $this->fetch();
    }
    public function saveauth(){
        try {
            $data = request()->post();


            $admin = model('admin')->where('aid', $data['aid'])->find();
            $admin->allowField(true)->save($data);
            echo json_encode($this->returnArr(true,'权限修改成功','权限修改失败',null));
        }catch (Exception $e){
            echo json_encode($this->returnArr(true,'权限修改成功','权限修改失败',null));
        }
    }
    protected function returnArr($res, $success, $error, $data)
    {
        $returnArr = [
            'msg' => $res ? $success : $error,
            'result' => $res ? 'success' : 'error',
            'data' => $data
        ];
        return $returnArr;
    }
}
