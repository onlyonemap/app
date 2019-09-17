<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class User extends Base
{
	function __construct(){
        parent::__construct();
        $this->uid = Session::get('admin_id','admin_mes');
        $this->if_login();

    }
	
    /**
     * 用户列表页面
     * @Auther: 李渊
     * @Date: 2018.6.21
     * @param  [type] $search       [搜索字段] 手机号码 姓名
     * @param  [type] $starttime    [开始时间] 
     * @param  [type] $endtime      [结束时间] 
     * @return [type] [de=scription]
     */
    public function userlist(){
        // 获取搜索字段
        $search = input('search');
        // 获取开始时间
        $stime = input('starttime');
        // 获取结束时间
        $etime = input('endtime');
        // 页码
        $pageParam = ['query' =>[]];
        // 判断是否有搜索条件
        if(!empty($search)){
           $user_where['realname|phone'] = ['like','%'.$search.'%'];
           $pageParam['query']['search'] = $search;
        }
        // 判断是否有开始时间和结束时间
        if (!empty($stime) && !empty($etime)) {
            $endtime = strtotime(trim($etime).'23:59:59');
            $starttime = strtotime(trim($stime).'00:00:00');
            $user_where['a.addtime'] = array(array('gt',$starttime),array('lt', $endtime));
            $pageParam['query']['starttime'] = $stime;
            $pageParam['query']['endtime'] = $etime;
        }
        // 过滤已注销的用户
        $user_where['delstate'] = '1';
        // 
        $array = array();
        // 查询结果
        $result = DB::table("ct_user")
                    ->alias('a')
                    ->field('a.*,b.name')
                    ->join('ct_company b','b.cid=a.lineclient','LEFT')
                    ->where($user_where)
                    ->order('uid','desc')
                    ->paginate(10,false, $pageParam);
        $result_data = $result->toArray();
        // 循环分享对象
        foreach ($result_data['data'] as $key => $value) {
            $array[$key] = $value;
            $share_name = '平台';
            if ($value['shareid'] !='') {
                $share_user =  Db::table('ct_user')->field('realname,username')->where('uid',$value['shareid'])->find();
                $share_name = $share_user['realname']=='' ? $share_user['username'] : $share_user['realname'];
            }
            //分享人姓名或昵称
            $array[$key]['share_name'] = $share_name;
        }
        $page =  $result->render();
        $this->assign('page',$page);
        $this->assign('list',$array);
        return  view('user/userlist');
    }
    
    /**
     * 前往添加用户信息页面
     * @Auther: 李渊
     * @Date: 2018.6.21
     * @return [type] [description]
     */
    public function toaddclient(){
        return view('user/addclient');
    }

    /**
     * 添加用户信息操作
     * @Auther: 李渊
     * @Date: 2018.6.21
     * @return [type] [description]
     */
    public function addclient() {
        $post_data =  Request::instance()->post();
        // 真实姓名
        $user_data['realname'] = $post_data['realname'];
        // 用户昵称
        $user_data['username'] = $post_data['username'];
        // 手机号
        $user_data['phone'] = $post_data['phone'];
        // 性别
        $user_data['sex'] = $post_data['sex'];
        // 身份证号
        $user_data['idcard'] = $post_data['idcard'];
        // 余额
        $user_data['money'] = $post_data['money'];
        // 公司id 个体用户为空
        $user_data['lineclient'] = $post_data['companyid'];
        // 用户类型 1管理2业务3个体
        $user_data['user_grade'] = $post_data['user_grade'];
        // 初始密码666666
        $user_data['password'] = md5('666666ct888');
        // 认证成功
        $user_data['auth_status'] = 2;
        // 添加时间
        $user_data['addtime'] = time();

        switch ($post_data['user_grade']) { // 1管理2业务0个体
            case '1': // 管理
                if($user_data['lineclient']){ // 如果添加了公司
                    $com = DB::table('ct_company')->where('cid',$user_data['lineclient'])->find();
                    $user_data['custom'] = $com['customer'];
                }else{
                    $this->error("请选择已经注册的公司");
                }
                // 查找改公司下是否已经有管理员有则不能添加
                $com = DB::table('ct_user')->where(array('lineclient' => $post_data['companyid'], 'delstate' =>1,'user_grade'=>1))->find();
                if($com){
                    $this->error("该公司已经有管理员了");
                }
                $user_data['userstate'] = 2; // 1 注册 2 项目 3 撮合
                break;
            case '2': // 业务
                if($user_data['lineclient']){ // 如果添加了公司
                    $com = DB::table('ct_company')->where('cid',$user_data['lineclient'])->find();
                    $user_data['custom'] = $com['customer'];
                }else{
                    $this->error("请选择已经注册的公司");
                }
                $user_data['userstate'] = 2; // 1 注册 2 项目 3 撮合
                break;
            case '3': // 个体 
                $user_data['userstate'] = 1;
                $user_data['lineclient'] = '';
                break;
            default:
                # code...
                break;
        }
        // 插入员工数据
        $date = DB::table('ct_user')->insert($user_data);
        if($date) {
            $content = "添加了".$user_data['realname']."的用户";
            $this->hanldlog($this->uid,$content);
            $this->success('添加成功','user/userlist');
        }else{
            $this->error("添加失败");
        }
    }

    /**
     * 前往 修改用户信息页面
     * @Auther: 李渊
     * @Date: 2018.6.21
     * @param  [type] $id           [用户id] 
     * @param  [type] $starttime    [开始时间] 
     * @param  [type] $endtime      [结束时间] 
     * @return [type] [description]
     */
    public function toupdateclient(){
        // 用户id
        $id = input('id');
        // 查询用户信息
        $result = DB::table("ct_user")->where('uid',$id)->find();
        // 查询公司信息
        $com = DB::table("ct_company")->where('cid',$result['lineclient'])->find();
        // 返回公司名称
        $result['companyname'] = $com['name'];
        // 返回公司id
        $result['companyid'] = $com['cid'];
        $this->assign('list',$result);
        return view("user/updateclient");
    }

    /**
     * 修改用户信息操作
     * @Auther: 李渊
     * @Date: 2018.6.21
     * @return [type] [description]
     */
    public function upclient(){
        $post_data =  Request::instance()->post();
        // 真实姓名
        $user_data['realname'] = $post_data['realname'];
        // 用户昵称
        $user_data['username'] = $post_data['username'];
        // 手机号
        $user_data['phone'] = $post_data['phone'];
        // 性别
        $user_data['sex'] = $post_data['sex'];
        // 身份证号
        $user_data['idcard'] = $post_data['idcard'];
        // 余额
        $user_data['money'] = $post_data['money'];
        // 公司id 个体用户为空
        $user_data['lineclient'] = $post_data['companyid'];
        // 用户类型 1管理2业务3个体
        $user_data['user_grade'] = $post_data['user_grade'];
        // 密码
        if ($post_data['password']!='') {
            $user_data['password'] = md5($post_data['password'].'ct888');
        }
        
        switch ($post_data['user_grade']) { // 1管理2业务3个体
            case '1': // 管理
                if($user_data['lineclient']){ // 如果添加了公司
                    $com = DB::table('ct_company')->where('cid',$user_data['lineclient'])->find();
                    $user_data['custom'] = $com['customer'];
                }else{
                    $this->error("请选择已经注册的公司");
                }
                // 查找改公司下是否已经有管理员有则不能添加
                $com = DB::table('ct_user')->where(array('lineclient' => $post_data['companyid'], 'delstate' =>1,'user_grade'=>1))->find();
                if($com && $post_data['uid'] != $com['uid']){
                    $this->error("该公司已经有管理员了");
                }
                $user_data['userstate'] = 2; // 1 注册 2 项目 3 撮合
                break;
            case '2': // 业务
                if($user_data['lineclient']){ // 如果添加了公司
                    $com = DB::table('ct_company')->where('cid',$user_data['lineclient'])->find();
                    $user_data['custom'] = $com['customer'];
                }else{
                    $this->error("请选择已经注册的公司");
                }
                $user_data['userstate'] = 2; // 1 注册 2 项目 3 撮合
                break;
            case '3': // 个体 
                $user_data['userstate'] = 1;
                $user_data['lineclient'] = '';  
                break;
            default:
                # code...
                break;
        }
        // 更新数据
        $date = DB::table('ct_user')->where('uid',$post_data['uid'])->update($user_data);

        if($date) {
            $content = "修改了".$user_data['realname']."的用户信息";
            $this->hanldlog($this->uid,$content);
            $this->success('修改成功','user/userlist');
        }else{
            $this->error("修改失败");
        }
    }

    /**
     * 删除用户信息操作
     * @Auther: 李渊
     * @Date: 2018.8.1
     * [delclient description]
     * @param  [type] $id   [用户id]
     * @return [type]       [description]
     */
    public function delete() {
        // 获取要删除的用户id
        $id = input('id');
        // 更改用户状态为删除状态
        $state = DB::table('ct_user')->where('uid',$id)->update(array('delstate'=>2));
        // 判断是否删除成功
        if($state){
            $content = "删除了ID为".$id."的用户信息";
            $this->hanldlog($this->uid,$content);
            $this->success('删除成功', 'user/userlist');
        }else{
            $this->error('删除失败');
        }
    }

    /**
     * 恢复用户信息操作
     * @Auther: 李渊
     * @Date: 2018.8.1
     * [delclient description]
     * @param  [type] $id   [用户id]
     * @return [type]       [description]
     */
    public function recovery() {
        // 获取要删除的用户id
        $id = input('id');
        // 更改用户状态为删除状态
        $state = DB::table('ct_user')->where('uid',$id)->update(array('delstate'=>1));
        // 判断是否删除成功
        if($state){
            $content = "恢复了ID为".$id."的用户信息";
            $this->hanldlog($this->uid,$content);
            $this->success('恢复成功', 'user/userlist');
        }else{
            $this->error('恢复失败');
        }
    }
  
}
