<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Advertisement  extends Base
{
//	function __construct(){
//        parent::__construct();
//        $this->uid = Session::get('admin_id','admin_mes');
//        $this->if_login();
//    }

    /**
     * 广告类别列表
     * @auther: 李渊
     * @date: 2018.8.20
     * @param string $value [description]
     */
    public function adventClass()
    {
        // 搜索内容
        $search = input('search');
        // 如果搜索结果不为空则模糊查询分类名称
        $where = '';
        // 
        if(!empty($search)){
            $where['cate_name'] = ['like','%'.$search.'%'];
        }
        // 查找数据
        $result = DB::table('ct_article_cate')->where($where)->select();
        // 循环遍历数据
        for ($i=0; $i < count($result); $i++) { 
            // 判断是否有父级分类
            if ($result[$i]['cate_parent'] != '0') {
                // 查找父级分类
                $cate_parent = DB::table('ct_article_cate')->where('id',$result[$i]['cate_parent'])->find();
                // 返回父级分类名称
                $result[$i]['cate_parent'] = $cate_parent['cate_name'];
            } else {
                // 返回父级分类名称
                $result[$i]['cate_parent'] = '没有父级';
            }
            // 广告类型
            $result[$i]['cate_type'] = $result[$i]['cate_type'] == 1 ? '广告类型' : '文章类型';
        }
        // 返回数据 
        $this->assign('list',$result);
        return view('advertisement/adventClass');
    }

    /**
     * to 添加广告类别
     * @auther: 李渊
     * @date: 2018.8.20
     * @return [type] [description]
     */
    public function adventClassAdd()
    {
        // 查找所有的分类
        $list = DB::table('ct_article_cate')->select();
        // 返回数据
        $this->assign('list',$list);
        return view('advertisement/adventClassAdd');
    }

    /**
     * 添加广告类别动作
     * @auther: 李渊
     * @date: 2018.8.20
     * @param  string $value [description]
     * @return [type]        [description]
     */
    public function addadventClass()
    {
        // 分类名称
        $data['cate_name'] = input('cate_name');
        // 父级分类
        $data['cate_parent'] = input('cate_parent');
        // 广告类型
        $data['cate_type'] = input('cate_type');
        // 
        $insert = DB::table('ct_article_cate')->insert($data);
        // 如果有父级则要更新父级状态
        if (!empty($data['cate_parent']) || $data['cate_parent'] !='0') {
            // 查找父级
            $parent = DB::table('ct_article_cate')->where('id',$data['cate_parent'])->find();
            // 判断父级
            if ($parent['cate_haschild'] == 1) {
                DB::table('ct_article_cate')->where('id',$data['cate_parent'])->update(['cate_haschild' => '2']);
            }
        }
        // 
        if ($insert) {
            $content = "添加了广告分类:".$data['cate_name'];
            $this->hanldlog($this->uid,$content);
            $this->success('添加成功','advertisement/adventClass');
        } else {
            $this->error("添加失败");
        }
    }

    /**
     * to 更新广告类别
     * @auther: 李渊
     * @date: 2018.8.20
     * @return [type] [description]
     */
    public function adventClassUpdate()
    {
        // 获取索引id
        $id = input('id');
        // 查找数据
        $result = DB::table('ct_article_cate')->where('id',$id)->find();
        // 查找所有的分类
        $list = DB::table('ct_article_cate')->select();
        // 遍历移除自身分类
        for ($i=0; $i < count($list); $i++) { 
            if ($list[$i]['id'] == $id) {
                array_splice($list, $i, 1);
            }
        }
        // 返回数据
        $this->assign('result',$result);
        $this->assign('list',$list);
        return view('advertisement/adventClassUpdate');
    }

    /**
     * 轮播图列表
     * @Auther: 李渊
     * @Date: 2018.7.2
     * @return [type] [description]
     */
    public function index(){
    	$result = DB::field('a.*,b.cate_name')
                    ->table('ct_banner')
                    ->alias('a')
                    ->join('ct_article_cate b','b.id=a.type')
                    ->where('delstate',1)
                    ->order('id desc')
                    ->paginate(10);
    	$page = $result->render();
    	$this->assign('list',$result);
    	$this->assign('page',$page);
    	return view('advertisement/index');
    }
    
    /**
     * to 添加轮播图
     * @Auther: 李渊
     * @Date: 2018.7.2
     * @return [type] [description]
     */
    public function add(){
        $model = DB::table('ct_article_cate')->where('cate_type',1)->select();
        $cate = $this->getSortedCategory($model);
        $this->assign('cate',$cate);
    	return view('advertisement/add');
    }

    /**
     * 轮播图添加
     * @Auther: 李渊
     * @Date: 2018.7.2
     * @return [type] [description]
     */
    public function addpost(){
    	$post_data = Request::instance()->post();
    	$data['type'] = $post_data['type'];
    	//$data['lineurl'] = urlencode($post_data['lineurl']);
        $data['lineurl'] = $post_data['lineurl'];
        $data['apptype'] = $post_data['apptype'];
    	if (request()->file('image') !='') {
    		$path = $this->file_upload('image','jpg,png,gif','ad');
            $data['picture'] = $path['file_path'];
    	}else{
            $this->error('请上传图片!!!');
        }
        if ($post_data['action'] == 'add') {
            $inserID = Db::table('ct_banner')->insertGetId($data);
            if ($inserID) {
                $content = "添加了ID为： ".$inserID." 广告图";
                $this->hanldlog($this->uid,$content);
                $this->success('添加成功！！','advertisement/index');
                exit();
            }else{
                $this->success('添加失败！！！');
                 exit();
            }
        }
    }

    /**
     * 轮播图修改
     * @Auther: 李渊
     * @Date: 2018.7.2
     * @return [type] [description]
     */
    public function update(){
    	$id = input('id');
    	$result =DB::table('ct_banner')->where('id',$id)->find();
        $model = DB::table('ct_article_cate')->where('cate_type',1)->select();
        $cate = $this->getSortedCategory($model);
        $this->assign('cate',$cate);
    	$this->assign('list',$result);
    	return view('advertisement/update');
    }
    
    /**
     * 轮播图删除
     * @Auther: 李渊
     * @Date: 2018.7.2
     * @return [type] [description]
     */
    public function del(){
    	$id = input('id');
    	$data['delstate']=2;
    	$result =DB::table('ct_banner')->where('id',$id)->update($data);
    	if ($result) {
            $content = "删除了ID为".$id."广告图片信息";
            $this->hanldlog($this->uid,$content);
            $this->success('删除成功！！','advertisement/index');
            exit();
        }else{
            $this->success('删除失败！！！');
             exit();
        }
    	
    }

    /**
     * 提交轮播广告动作
     * @Auther: 李渊
     * @Date: 2018.7.2
     * @return [type] [description]
     */
    public function uppost(){
    	$post_data = Request::instance()->post();
    	$data['type'] = $post_data['type'];
        $find = Db::table('ct_banner')->where('id',$post_data['id'])->find();
        $find_str = substr($find['picture'],1);
    	//$data['lineurl'] = urlencode($post_data['lineurl']);
        $data['lineurl'] = $post_data['lineurl'];
        $data['apptype'] = $post_data['apptype'];
    	if (request()->file('image') !='') {
    		$path = $this->file_upload('image','jpg,png,gif','ad');
            $data['picture'] = $path['file_path'];
            @unlink($find_str);
    	}
        if ($post_data['action'] == 'update') {
            $inserID = Db::table('ct_banner')->where('id',$post_data['id'])->update($data);
            if ($inserID) {
                $content = "修改了ID为： ".$post_data['id']." 广告图";
                $this->hanldlog($this->uid,$content);
                $this->success('修改成功！！','advertisement/index');
                exit();
            }else{
                $this->success('修改失败！！！');
                 exit();
            }
        }
    }
    /*
     *零担动态内容列表
     * */
    public function bulkIndex(){

        $arr = Db::table('ct_dynamic_content')
            ->field('*')
            ->order('createtime DESC')
            ->select();
        $this->assign('list',$arr);
       return  $this->fetch();
    }
    /*
     * 添加内容
     * */
    public function bulkadd(){
        return $this->fetch();
    }
    public function savecontent(){
        $data['content'] = $_POST['content'];
        $data['state'] = $_POST['state'];
        $data['type'] = $_POST['type'];
        $data['createtime'] = time();
        $res = Db::table('ct_dynamic_content')->insert($data);
        if ($res){
            return json(['code'=>'1001','message'=>'添加成功']);
        }else{
            return json(['code'=>'1002','message'=>'添加失败']);
        }
    }
    /*
     *修改内容
     * */
    public function edit($id){
        $data = Db::table('ct_dynamic_content')->field('*')->where('id',$id)->find();
//        var_dump($data);
        $this->assign('list',$data);
        return $this->fetch();
    }

    public function contentsave(){
        $data['content'] = $_POST['content'];
        $data['state'] = $_POST['state'];
        $data['type'] = $_POST['type'];
        $data['id'] = $_POST['id'];
        $res = Db::table('ct_dynamic_content')->where('id',$data['id'])->update($data);
        if ($res){
            return json(['code'=>'1001','message'=>'修改成功']);
        }else{
            return json(['code'=>'1002','message'=>'修改失败']);
        }
    }
    /*
     * 删除内容
     * */
    public function contentdel($id)
    {
        $data = Db::table('ct_dynamic_content')->field('*')->where('id', $id)->delete();

        if ($data) {
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}