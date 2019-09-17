<?php
namespace app\admin\controller;


use think\Controller;

Class PublicController extends Controller {
    protected function _initialize()
    {
        $uid = Session::get('admin_id', 'admin_mes');
        if ($uid == '') {
            $this->redirect("index/login");

            $this->quanxian();
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

        public function del($result = true)
    {
        try {
            $request = Request::instance();
            $data = $request->param();
            $aid = substr($data['id'], 0, -1);
            foreach ($data['model'] as $k => $v) {
                $res = model($v)::destroy($aid);
                if (!$res) {
                    $result = false;
                }
            }
            echo json_encode($this->returnArr($result, '删除成功', '删除失败', null));
        } catch (Exception $e) {
            echo json_encode($this->returnArr(!$e, '删除成功', '删除失败', null));
        }
    }

        public function xgstatus()
    {
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



        public function sousuo($where = '',$created='created')
    {
        $request = Request::instance();
        $data = $request->param();
        if (empty($data['stime'])) {
            $data['stime'] = 1525586502;
        } else {
            $data['stime'] = strtotime($data['stime']);
        }
        if (empty($data['etime'])) {
            $data['etime'] = strtotime(date("Y-m-d", strtotime("+1 day")));
        } else {
            $data['etime'] = strtotime($data['etime']) + 86400;
        }

        if ($data['status'] != 'no') {
            $where = ['status' => ['=', $data['status']]];
        }
        $res = model($data['model'])
            ->field($data['wfield'])
            ->where($data['field'], 'like', '%' . $data['gjz'] . '%')
            ->where($created, ['>=', $data['stime']], ['<', $data['etime']], 'and')
            ->where($where)
            ->paginate(10, false, [
                'query' => $request->param(),
            ]);
        $count = $res->total();
        $this->assign([$data['model'] . 'Obj' => $res, 'count' => $count]);
        return $this->fetch($data['url']);
    }

        protected function sjyz($model, $field, $wfield, $msg)
    {
        $res = model($model)->field($field)->where($field, $wfield)->find();
        if ($res) {
            echo json_encode($this->returnArr(!$res, '', $msg, null));
            exit();
        } else {
            return true;
        }
    }

        protected function quanxian(){
        $cstr = strtolower(request()->controller().'/'.request()->action());
        $result = model('system')->field('connect')->where('connect',$cstr)->find();
        $admin = model('admin')->field('qxz,admin')->where('aid',session('admin.aid'))->find();
        if($result && $admin->admin!=5){
            $quanxian = model('quanxianstr')->field('quanxianstr')->where('qid',$admin->qxz)->find();
            $qstr = strtolower($quanxian?$quanxian->quanxianstr:'');
            $res = strpos($qstr,$cstr);
            if(is_numeric($res)){
                return true;
            }else{
                if(request()->isAjax()){
                    echo json_encode($this->returnArr(false,'','您没有权限操作',null));
                    exit;
                }else{
                    echo '<h1>您没有权限操作</h1>';
                    exit;
                }

            }
        }else{
            return true;
        }

    }
}
