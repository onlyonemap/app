<?php
/*
*author:chenwei
*/
namespace app\backstage\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Carrcompany extends Base
{
	function __construct(){
        parent::__construct();
       $this->uid = Session::get('carrier_id','carrier_mes');
        $company_id = Db::field('a.companyid,c.name,a.username')->table('ct_driver')->alias('a')->join('ct_company c','c.cid=a.companyid')->where('drivid',$this->uid)->find();
        $this->comid = $company_id['companyid'];
        $this->comname = $company_id['name'];
        $this->uname = $company_id['username'];
        $this->if_login();

    }

	//首页
    public function index(){
        $arr = array();
        $search = trim(input('search'));
       // echo $this->comid;exit();
        $where['cid'] = $this->comid;
        $result = DB::table('ct_company')
                    ->where($where)
                    ->order('status','asc')
                    ->paginate(10);
        $data_result = $result->toArray();
        foreach ($data_result['data'] as  $value) {
            $pro = DB::table('ct_district')->where('id',$value['provinceid'])->find();
            $city = DB::table('ct_district')->where('id',$value['cityid'])->find();
            $area = DB::table('ct_district')->where('id',$value['areaid'])->find();
            $value['provinceid'] = $pro['name'];
            $value['cityid'] = $city['name'];
            $value['areaid'] = $area['name'];
            $ti_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['cid'],'type'=>1))->select();
            $pei_arr = DB::table('ct_tpprice')->where(array('companyid'=>$value['cid'],'type'=>2))->select();
            $shift_arr = DB::field('a.shiftnumber,b.start_id,b.end_id')
                            ->table('ct_shift')
                            ->alias('a')
                            ->join('ct_already_city b','b.city_id=a.linecityid')
                            ->where(array('companyid'=>$value['cid']))
                            ->select();
            foreach ($ti_arr as $tval) {
                
                $tval['province'] = detailadd($tval['province'],'','');
                $value['ti'][] = $tval;
            }
            foreach ($pei_arr as $pval) {
                $add_arr = DB::table('ct_addressinfo')->where(array('inid'=>$pval['addid']))->select();
                if (!empty($add_arr)) {
                    foreach ($add_arr as $can) {
                        $can['can_address'] = detailadd($can['provinceid'],$can['cityid'],$can['areaid']).$can['address'];
                        $pval['can'][] = $can;
                    }
                }
                
               
                $pro_pei = DB::table('ct_district')->where('id',$pval['province'])->find();
                $pval['province'] = $pro_pei['name'];
                $value['pei'][] = $pval;

            }
            foreach ($shift_arr as $key => $valsf) {
                $startline = detailadd($valsf['start_id'],'','');
                $endline = detailadd($valsf['end_id'],'','');
                $valsf['shifstartline'] = $startline;
                $valsf['shifendline'] = $endline;
                $value['shift'][] = $valsf;
            }
            $arr[]=$value;
            
        }
        $page = $result->render();
        $this->assign('page',$page);
        $this->assign('list',$arr);
    	return view('carrcompany/index'); 
    }
    //添加干线承运商
    public function addcom(){

        return view('carrcompany/addcom');

    }
   
    /*
    *
    *地址表联动下拉数据
    */
    public function getaddress(){
        $getid = Request::instance()->get('id'); 
        $result =  DB::table('ct_district ')->where(array('parent_id'=>$getid))->select();
        //var_dump($result);
        
            switch ($getid)
            {
            case '1':
             return $opstr="[{id:'45052',name:'北京市'}]";
              break;  
            case '2':
              return $opstr="[{id:'45053',name:'天津市'}]";
              break;
            case '9':
              return $opstr="[{id:'45054',name:'上海市'}]";
              break;
            case '22':
              return $opstr="[{id:'45055',name:'重庆市'}]";
              break;
            default:
                $opstr="[";  
                foreach ($result as $key => $value) {
                   $opstr.="{id:'".$value['id']."',name:'".$value['name']."'},";  
                }
                $len=strlen($opstr)-1;  
                $opstr=substr($opstr,0,$len);  
                $opstr.="]"; 
                return $opstr;  
            }
    }
     //干线公司信息处理
    public function addmessage(){
        $postdata = Request::instance()->post();
        //echo "<pre/>";
        //print_r($postdata);
        //exit();
        if($postdata['action'] == "add"){
            $compay_data['name'] = $postdata['name'];
            $compay_data['provinceid'] = $postdata['province'];
            $compay_data['areaid'] = $postdata['area'];
            $compay_data['cityid'] = $postdata['city'];
            $compay_data['address'] = $postdata['addinfo'];
            $compay_data['type'] = 1;
            $compay_data['addtime'] =time();
            $compayid = Db::table('ct_company')->insertGetId($compay_data);
            if (isset($postdata['pickinfo'] )) {
                 foreach ($postdata['pickinfo'] as $key => $value) {
                    $ti_date['province']=$value['province'];
                    $ti_date['price']=$value['price'];
                    
                    $ti_date['rate']=$value['rate'];
                    $ti_date['type']=1;
                    $ti_date['companyid']=$compayid;
                   Db::table('ct_tpprice')->insert($ti_date);
                }
            }
            if (isset($postdata['infos'])) {
                foreach ($postdata['infos'] as  $val) {
                    if ($postdata['province'] !=0 ) {
                        $can_date['provinceid']= $val['provinceid'];
                        $can_date['cityid']= $val['cityid'];
                        $can_date['areaid']= $val['areaid'];
                        $can_date['address']= $val['address'];
                        $can_date['type']= 2;
                        $can_date['companyid']= $compayid;
                        $addid = Db::table('ct_addressinfo')->insertGetId($can_date);
                        $pei_date['addid']=$addid;
                    }
                    $pei_date['province']=$val['province'];
                    $pei_date['price']=$val['price'];
                   
                    $pei_date['rate']=$val['rate'];
                    $pei_date['type']=2;
                    $pei_date['companyid']=$compayid;
                    
                   Db::table('ct_tpprice')->insert($pei_date);
                }
            }
           
          if($compayid){
                $content = "添加了新干线公司".$postdata['name'];
                $this->hanldlog($this->uid,$content);
                $this->success('新增成功', 'carrcompany/index');
            }else {
                $this->error('新增失败');
            }
            exit();
        }
        
       if ($postdata['action'] == "update") {
             $compay_data['name'] = $postdata['name'];
             if($postdata['province'] !=0 && $postdata['province'] !=''){
                 $compay_data['provinceid'] = $postdata['province'];
                $compay_data['areaid'] = $postdata['area'];
                $compay_data['cityid'] = $postdata['city'];
                $compay_data['address'] = $postdata['addinfo'];
            }

            $com = Db::table('ct_company')->where('cid',$postdata['cid'])->update($compay_data);
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
           
          if(isset($com) || isset($ti)|| isset($addid) || isset($pei)){
                $content = "修改了干线公司".$postdata['name'];
                $this->hanldlog($this->uid,$content);
                $this->success('修改成功', 'carrcompany/index');
            }else {
                $this->error('修改失败');
            }
            exit();

       }
    }
   
    //删除和恢复操作
    public function delcom(){
        //$id = input('id');
        $get = Request::instance()->get();
        if($get['del'] == 1){
            $delcom = DB::table('ct_company')->where('cid',$get['id'])->update(array('status'=>2));
           if($delcom){
                $content = "删除了ID为".$get['id']."公司信息";
                $this->hanldlog($this->uid,$content);
                $this->success('删除成功', 'carrcompany/index');
           }else{
                $this->error('删除失败');
           }
        }else{
             $delcom = DB::table('ct_company')->where('cid',$get['id'])->update(array('status'=>1));
           if($delcom){
                $content = "恢复了ID为".$get['id']."公司信息";
                $this->hanldlog($this->uid,$content);
                $this->success('恢复成功', 'carrcompany/index');
           }else{
                $this->error('恢复失败');
           }
        }
    
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
        return view('carrcompany/updategan');
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

    

   
}
