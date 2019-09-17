<?php
namespace app\admin\controller;

use think\Controller;
use think\Db;
use think\Exception;
use think\Request;


Class Auth extends Base{
    public function index(){
        $data = Db::table('ct_admin')->where('pstate',1)->paginate(10);
        $page =  $data->render();
        $this->assign('page',$page);
        $this->assign('list',$data);

     return $this->fetch();
    }
    public function setting(){
        $adminObj = Db::table('ct_quanxian')->paginate(20, false, ['query' => request()->param(),]);
        $count = $adminObj->total();
        $this->assign(['list' => $adminObj, 'count' => $count]);
        return $this->fetch();
    }
    public function addauth(){
        return $this->fetch();
    }

    public function saveauth(){
        try{
            $data = request()->post();
            $arr['title'] = $data['title'];
            $arr['describe'] = $data['describe'];
            $quanxian = Db::table('ct_quanxian')->insertGetId($arr);
            $qid = $quanxian;
            $array['quanxianstr'] = '';
            $array['qid'] = $qid;
            Db::table('ct_authstr')->insert($array);
            echo json_encode($this->returnArr(true,'权限组添加成功','权限组添加失败',$qid));
        }catch (Exception $e){
            echo json_encode($this->returnArr(true,'权限组添加成功','权限组添加失败',null));
        }
    }

    public function authxr($qid){
        $quanxianstr = Db::table('ct_authstr')->where('qid',$qid)->find();
        $authstr = $quanxianstr['quanxianstr'];
        $system = model('auth_rule')->where('pid',0)->select();
//        echo '<pre/>';
//        print_r($system);

        $this->assign(['system'=>$system,'quanxianstr'=>$authstr]);
        return $this->fetch();
    }

    public function tosaveauth(){
        try{
            $data = request()->post();
            $quan = array_filter($data['quanxian']);
            $str = implode(',',$quan);
            $data['quanxianstr'] = $str;
            model('authstr')->where('qid',$data['qid'])->find()->allowField(true)->save($data);
            $quanxian = model('quanxian')->field('title')->where('qid',$data['qid'])->find()->title;
            echo json_encode($this->returnArr(true,'权限添加成功','权限添加失败',null));
        }catch (Exception $e){
            echo json_encode($this->returnArr(false,'权限添加成功','权限添加失败',null));
        }

    }

    /*
     * 修改
     * */
    public function updatestatus(){
        $data = request()->post();
        try {
            $request = Request::instance();
            $data = $request->param();
            $aid = substr($data['id'], 0, -1);
            $quanxian = model($data['model'])->where($data['zhujian'],'in',$aid)->update(['status'=>$data['status']]);
            echo json_encode($this->returnArr(true, $data['msg'].'成功', $data['msg'].'失败', null));
        } catch (Exception $e) {
            echo json_encode($this->returnArr(!$e, $data['msg'].'成功', $data['msg'].'失败', null));
        }
    }

    public function authedit($qid){
        $quanxian = model('quanxian')->field('qid,title,describe')->where('qid',$qid)->find();
        $this->assign('quanxian',$quanxian);
       return  $this->fetch();
    }

    public function  dosaveauth(){
        try{
            $data = request()->post();
            $quanxian = model('quanxian')->where('qid',$data['qid'])->find();
            $quanxian->allowField(true)->save($data);

            echo json_encode($this->returnArr(true,'权限组修改成功','权限组修改失败',null));
        }catch (Exception $e){
            echo json_encode($this->returnArr(false,'权限组修改成功','权限组修改失败',null));
        }
    }

    public function delete(){
        try {
            $request = Request::instance();
            $data = $request->param();
            $aid = substr($data['id'], 0, -1);
            foreach ($data['model'] as $k => $v) {
                $result = model($v)::destroy($aid);
                if (!$result) {
                    $result = false;
                }
            }
            echo json_encode($this->returnArr($result, '删除成功', '删除失败', null));
        } catch (Exception $e) {
            echo json_encode($this->returnArr(!$e, '删除成功', '删除失败', null));
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