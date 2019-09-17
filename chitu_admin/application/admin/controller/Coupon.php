<?php
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Coupon  extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//    }

    /**
     * 优惠卷列表
     * @auther 李渊
     * @date 2018.6.13
     * @return [type] [description]
     */
    public function couindex(){
        // 查询数据
        $result = DB::table('ct_coupon')->where('delstate',1)->order('cou_id','desc')->paginate(10);
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list', $result);
        return view('coupon/couindex');
    }
  
    /**
     * to 分发优惠卷页面
     * 返回可使用的优惠卷类型
     * @Auther: 李渊
     * @Date: 2018.7.11
     * @return [type] [description]
     */
	public function index(){
        // 查询所有可有的优惠卷类型
        $type = Db::table('ct_coupon')->where(array('state'=>'1','delstate'=>'1'))->select();
        $this->assign('list',$type);
        return view('coupon/index');
    }

    /**
     * 赠送优惠卷操作
     * 可赠送多个人
     * @Auther: 李渊
     * @Date: 2018.7.11
     * @return [type] [description]
     */
    public function coupuser(){
        $postdata = Request::instance()->post();
        // 获取赠送人的手机号
        $userphone = explode(',',$postdata['content']);
        // 循环手机号赠送优惠卷
        foreach ($userphone as $key => $value) {
            // 获取用户信息
            $user = Db::table('ct_user')->where(array('phone' => $value, 'delstate' => 1))->find();
            // 获取赠送的优惠卷信息
            $coun = Db::table('ct_coupon')->field('time_day,content')->where('cou_id',$postdata['couid'])->find();
            // 赠送优惠卷id
            $coupon_data['coup_id'] = $postdata['couid'];
            // 赠送人
            $coupon_data['userid'] = $user['uid'];
            // 类型 1 未使用
            $coupon_data['failure'] = '1';
            // 优惠卷开始时间
            $coupon_data['time_start'] = time();
            // 优惠卷结束时间
            $coupon_data['time_end'] = time()+86400*$coun['time_day'];
            // 赠送
            $result = Db::table('ct_coupon_user')->insert($coupon_data);
            if ($result) {
                $content = "给用户".$value."赠送了 ".$coun['content']." 优惠卷";
                $this->hanldlog($this->uid,$content);
            }
        }
        if ($result) {
            $this->success('赠送成功!','coupon/index');
        }else{
            $this->error('赠送失败!');
        }
    }
  
    /**
     * to 添加优惠卷页面
     * @Auther: 李渊
     * @Date: 2018.7.11
     * @return [type] [description]
     */
    public function addcou(){
        return view('coupon/addcou');
    }

    /**
     * 添加优惠卷操作
     * @Auther: 李渊
     * @Date: 2018.7.11
     * @return [type] [description]
     */
    public function postcou(){
        $post_data = Request::instance()->post();
        // 优惠卷名称
        $data['cou_name'] = $post_data['cou_name'];
        // 优惠卷金额
        $data['cou_number'] = $post_data['cou_number'];
        // 优惠卷描述
        $data['description'] = $post_data['description'];
        // 优惠券类型（1注册赠送 2消费满额赠送 3分享获取）
        $data['coutype_id'] = $post_data['ctype'];
        // 有效天数
        $data['time_day'] = $post_data['time_day'];
        // 详细说明
        $data['content'] = $post_data['content'];
        // 添加时间
        $data['addtime'] = time();
        // 优惠券状态1可用2无效
        $data['state'] = $post_data['state'];
        // 插入数据
        $insertID = DB::table('ct_coupon')->insertGetId($data);
        // 判断是否插入成功
        if ($insertID) {
            $content = "添加了".$post_data['cou_name']."优惠券信息";
            $this->hanldlog($this->uid,$content);
            $this->success('添加成功！！','coupon/couindex');
        }else{
            $this->error('添加失败！！');
        }
    }

    /**
     * to 编辑优惠卷页面
     * @Auther: 李渊
     * @Date: 2018.7.11
     * @return [type] [description]
     */
    public function editcou(){
        // 优惠卷id
        $id = input('id');
        // 查询数据
        $result = DB::table('ct_coupon')->where('cou_id',$id)->find();
        // 输出结果
        $this->assign('list',$result);
        // 模板渲染
        return view('coupon/editcou');
    }

    /**
     * 编辑优惠卷操作
     * @Auther: 李渊
     * @Date: 2018.7.11
     * @return [type] [description]
     */
    public function eidtpost(){
        $post_data = Request::instance()->post();
        // 优惠卷名称
        $data['cou_name'] = $post_data['cou_name'];
        // 优惠卷金额
        $data['cou_number'] = $post_data['cou_number'];
        // 优惠卷满减限制金额
        $data['description'] = $post_data['description'];
        // 优惠券类型（1注册赠送 2消费满额赠送 3分享获取）
        $data['coutype_id'] = $post_data['ctype'];
        // 有效天数
        $data['time_day'] = $post_data['time_day'];
        // 详细说明
        $data['content'] = $post_data['content'];
        // 添加时间
        $data['addtime'] = time();
        // 优惠券状态1可用2无效
        $data['state'] = $post_data['state'];
        // 更新数据
        $updateID = DB::table('ct_coupon')->where('cou_id',$post_data['id'])->update($data);
        // 判断是否修改成功
        if ($updateID) {
            $content = "修改了".$post_data['cou_name']."优惠券信息";
            $this->hanldlog($this->uid,$content);
            $this->success('修改成功！！','coupon/couindex');
        }else{
            $this->error('修改失败！！');
        }
    }     

    /**
     * 删除、开启、关闭优惠卷操作
     * @Auther: 李渊
     * @Date: 2018.7.11
     * @param  [type] $del  [操作类型 1 开启 2 关闭 3 删除]
     * @param  [type] $id   [优惠卷id]
     * @return [type] [description]
     */
    public function delcoup($pad){
        $get_data = Request::instance()->get();
        // 判断操作类型
        switch ($get_data['del']) {
            case '1': // 开启
                $data['state']=1;
                $update = DB::table('ct_coupon')->where('cou_id',$get_data['id'])->update($data);
                $content = "开启了".$get_data['id']."优惠券信息";
                $this->hanldlog($this->uid,$content);
                break;
            case '2': // 关闭
                $data['state']=2;
                $update = DB::table('ct_coupon')->where('cou_id',$get_data['id'])->update($data);
                $content = "关闭了".$get_data['id']."优惠券信息";
                $this->hanldlog($this->uid,$content);
                break;
            case '3': // 删除
                $data['delstate']=2;
                $data['deltime'] = time();
                $update = DB::table('ct_coupon')->where('cou_id',$get_data['id'])->update($data);
                $content = "删除了".$get_data['id']."优惠券信息";
                $this->hanldlog($this->uid,$content);
                break;
            
            default:
                # code...
                break;
        }
        // 判断是否操作成功
        if ($update) {
            $this->success("操作成功！！",'coupon/couindex');
        }else{
            $this->error("操作失败！！");
        }
    }
}
