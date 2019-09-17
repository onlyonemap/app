<?php
namespace app\admin\controller;

use think\Controller;
use app\common\model;
use think\Db;

Class System extends Controller {
    public function index(){
        if(isset($_GET['sid'])){
            $system = model('system')->field('sort,title,sid,fid,connect')->where('fid',$_GET['sid'])->paginate(10,false,[
                'query' => request()->param(),
            ]);$count = $system->total();
        }else{
            $system = model('system')->field('sort,title,sid,fid,connect')->where('fid',0)->paginate(10,false,[
                'query' => request()->param(),
            ]);
            $count = $system->total();
        }
        echo '<pre/>';
        print_r($system);
        $this->assign(['list'=>$system,'count'=>$count]);
        return $this->fetch();
    }
    public function add(){
        $fid = $this->getList();//model('system')->field('title,sid')->select();
        $this->assign('fid',$fid);
        return $this->fetch();
    }
    public function getList($fid = 0, $target = []){
        $one = model('system')->field('sid,title')->where('fid', $fid)->select();
        static $n = 0; // 初始分类级别是1
        foreach ($one as $c) {
            // 第一次遍历
            // $c->id == 1;
            // $c->title == '体育';
            $c->level = $n; // 对象属性赋值
            $target[$c->sid] = $c->toArray();
            $n++;
            $target = $this->getList($c->sid, $target);
            $n--;
        }

        return $target;
    }
}
