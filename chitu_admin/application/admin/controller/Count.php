<?php
namespace app\admin\Count;
use think\Controller;
use think\Db;
use think\Session;

Class Count extends Controller{
    function __construct(){
        parent::__construct();
        $this->uid = Session::get('admin_id','admin_mes');
        $this->if_login();

    }

    public function index(){
        $uid = $this->uid;
        $grade = Db::table('ct_admin')->field('grade')->where('aid',$uid)->find();
        if ($grade['grade'] == 1){

        }else{
            $sid = Db::table('ct_shift')->field('sid')->where('aid',$uid)->find();

            $shift_log = Db::table('ct_shift_log')->field('slid')->where('shiftid',$sid['sid'])->select();

            $count1 = 0;
            $count2 = 0;
            $count3 = 0;
            foreach($shift_log as $key =>$value){
                $proceed_order = DB::table('ct_order')->where(array('orderstate'=>array('not in','7,8'),'paystate'=>2,'slogid'=>$value['slid']))->count('oid');
                $count1 += $proceed_order;
                $finish_order = DB::table('ct_order')->where(array('orderstate'=>7,'paystate'=>2,'slogid'=>$value['slid']))->count('oid');
                $count2 += $finish_order;
                $Invalid_order = DB::table('ct_order')->where(array('paystate'=>1,'slogid'=>$value['slid']))->count('oid');
                $count3 += $Invalid_order;

            }
            var_dump($count1);
            var_dump($count2);
            var_dump($count3);
            exit();
        }
    }
}
