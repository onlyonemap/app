<?php
/*
*author:chenwei
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Company extends Base
{
	function __construct(){
        parent::__construct();
        $this->uid = Session::get('admin_id','admin_mes');
        $this->if_login();
    }

	 //修改干线公司信息
    public function updategan(){
        $id = input('id');
        $reslut = DB::table('ct_company')->where(array('cid'=>$id))->select();
        foreach ($reslut as  $value) {
            $pro = DB::table('ct_district')->where('id',$value['provinceid'])->find();
            $city = DB::table('ct_district')->where('id',$value['cityid'])->find();
            $area = DB::table('ct_district')->where('id',$value['areaid'])->find();
            $value['provinceidname'] = $pro['name'];
            $value['cityidname'] = $city['name'];
            $value['areaidname'] = $area['name'];
            $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['cid'],'type'=>1))->select();
            $pei_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['cid'],'type'=>2))->select();

            foreach ($ti_arr as $tval) {
                $pro_ti = DB::table('ct_district')->where('id',$tval['province'])->find();
                $tval['provincename'] = $pro_ti['name'];
                $value['ti'][] = $tval;
            }
            foreach ($pei_arr as $pval) {
                $add_arr = DB::table('ct_addressinfo')->where(array('inid'=>$pval['addid']))->select();
                foreach ($add_arr as $can) {
                    if (!empty($add_arr)) {
                        foreach ($add_arr as $can) {
                            $can['can_address'] = detailadd($can['provinceid'],$can['cityid'],$can['areaid']).$can['address'];
                            $pval['can'][] = $can;
                        }
                    }
                    
                }
               
                $pro_pei = DB::table('ct_district')->where('id',$pval['province'])->find();
                $pval['province'] = $pro_pei['name'];
                $value['pei'][] = $pval;

            }
           
            
        }
       // echo "<pre>";
        //print_r($value);
        $this->assign('list', $value);
        return view('company/updategan');
    }
    
    //干线公司信息处理
    public function addmessage(){
        $postdata = Request::instance()->post();
        if ($postdata['action'] == "update") {
            if (isset($postdata['pickinfo'])) {
                foreach ($postdata['pickinfo'] as $key => $value) {
                    $ti_date['province']=$value['province'];
                    $ti_date['price']=$value['price'];
                    $ti_date['rate']=$value['rate'];
                    $ti_date['type']=1;
                    $ti_date['companyid']=$postdata['cid'];
                   $ti = Db::table('ct_tpprice')->insert($ti_date);
                }
            }

            if (isset($postdata['infos'])) {
                foreach ($postdata['infos'] as  $val) {
                    if ($val['provinceid'] !='0' && $val['provinceid'] !='') {
                        $can_date['provinceid']= $val['provinceid'];
                        $can_date['cityid']= $val['cityid'];
                        $can_date['areaid']= $val['areaid'];
                        $can_date['address']= $val['address'];
                        $can_date['type']= 2;
                        $can_date['companyid']= $postdata['cid'];
                        $addid = Db::table('ct_addressinfo')->insertGetId($can_date);
                        $pei_date['addid']=$addid;
                    }
                    
                    $pei_date['province']=$val['province'];
                    $pei_date['price']=$val['price'];
                    
                    $pei_date['rate']=$val['rate'];
                    $pei_date['type']=2;
                    $pei_date['companyid']=$postdata['cid'];
                    
                   $pei = Db::table('ct_tpprice')->insert($pei_date);
                }
            }

          if( isset($ti)|| isset($addid) || isset($pei)){
                $content = "添加或修改了提配区域:".$postdata['cid'];
                $this->hanldlog($this->uid,$content);
               // $this->success('修改成功', 'company/index');
                $result['message'] = '提交成功';
                $result['code'] = true;
                echo json_encode($result);
            }else {
                //$this->error('修改失败');
                $result['message'] = '提交失败';
                $result['code'] = false;
                echo json_encode($result);
            }
            exit();

       }
    }
  
    //删除地址
    public function deladd(){
        $id = input('addid');
        if(input('ajax') == 1 ){ 
            Db::table('ct_tpprice')->delete($id);
        }
        if(input('ajax') == 2) {
           $add = DB::table('ct_tpprice')->where('tpid',$id)->find();
           if ($add['addid']!='') {
               Db::table('ct_addressinfo')->delete($add['addid']);
           }
           
           Db::table('ct_tpprice')->delete($id);
        }
       
    }

    //物品列表页
    public function itemindex(){
        $result = DB::table('ct_itemcategory')->paginate(10);
        $page=$result->render();
        $this->assign('list',$result);
        $this->assign('page',$page);
        return view('company/itemindex');
    }

    //添加物品
    public function additem(){
        return view('company/additem');
    }
    //修改物品
    public function updateitem(){
        $id = input('id');
        $result = DB::table('ct_itemcategory')->where('item_id',$id)->find();
        $this->assign('list',$result);
        return view('company/updateitem');
    }
    public function itempost(){
        $post_data = Request::instance()->post();
        $data['goodsname'] = $post_data['goodsname'];
        if($post_data['action'] == 'add'){
            $result = DB::table('ct_itemcategory')->insert($data);
            if ($result) {
                $content = "添加了 ".$post_data['goodsname']."物品";
                $this->hanldlog($this->uid,$content);
                $this->success('添加成功!!','company/itemindex');
            }else{
                $this->error('添加失败!!');
            }
        }
        if($post_data['action'] == 'update'){

            $result = DB::table('ct_itemcategory')->where('item_id',$post_data['id'])->update($data);
            if (isset($result)) {
                $content = "修改了 ".$post_data['goodsname']."物品";
                $this->hanldlog($this->uid,$content);
                $this->success('修改成功!!','company/itemindex');
            }else{
                $this->error('修改失败!!');
            }
        }

    }
    //删除物品
    public function delitem(){
        $id = input('id');
       $res =  Db::table('ct_itemcategory')->delete($id);
       if($res){
            $content = "删除了ID为".$id."物品信息";
            $this->hanldlog($this->uid,$content);
            $this->success('删除成功!!','company/itemindex');
       }else{
            $this->error('删除失败！！');
       }
    }
  
}
