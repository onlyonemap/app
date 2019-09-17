<?php
/*
*订单文件导入
*/
namespace app\backstage\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Orderimport extends Base{
	function __construct(){
        parent::__construct();
        $this->if_login();

    }
	public function imcarload(){
        $carrier_id['uid'] = Session::get('carrier_id','carrier_mes');
        $company = Db::field('lineclient')->table('ct_user')->where($carrier_id)->find();
        $user = Db::table('ct_user')->where('lineclient',$company['lineclient'])->select();
        $this->assign('list',$user);
		return view('orderimport/imcarload');
	}
	/*
	*整车订单导入
	*/
	public function carload(){
        $postdata = Request::Instance()->post();
        $data['userid'] = $postdata['userid'];
        //print_r($postdata);exit();
		if (request()->file('file') !='') {

			$path = $this->file_upload('file','xls,xlsx','execl');
	        $path = $path['file_path'];
	        $delpath = '../public'.$path;
	        $list = $this->reader('../public'.$path);//导入
            foreach ($list as $key => $value) {
                
                $car_arr = array('1'=>'4.2','2'=>'5.2','3'=>'7.6','4'=>'9.6前四后四','5'=>'9.6前四后八','6'=>'12.5','7'=>'15','9'=>'面包车');
                $carid = array_keys($car_arr,$value['D']);
                $paddress = $value['J']; //收货地址
                $pickaddress = $value['H'];  //发货地址
                if ($value['I'] == "否") {
                    $pickyes = 1;
                }else{
                    $pickyes = 2;
                }
                $pick_arr = array(
                        array(
                            'areaName'=>$pickaddress,
                            'areaID'=>1,
                            'temperature'=>$value['E'],
                            'goodType'=>$value['C'],
                            'pickyes'=>$pickyes
                        )
                    );
                $pei_arr = array(
                        array(
                            'areaName'=>$paddress,
                            'areaID'=>1,
                            'username'=>'',
                            'telephone'=>''
                        )
                    );
                $array['name'] = ['like',$value['F'].'%'];
                $array['level'] = 2;
                $array2['name'] = ['like',$value['G'].'%'];
                $array2['level'] = 2;
                $start_city = Db::table('ct_district')->field('id')->where($array)->find();
                $end_city = Db::table('ct_district')->field('id')->where($array2)->find();
                $data['ordernumber'] = 'K'.date('Ymdhis').mt_rand('000000','999999');
                $data['remark'] = $value['K'];
                $data['paystate'] = 2;
                $data['loaddate'] = strtotime($value['B']).'000';
                $data['addtime'] = time();
                $data['arrtime'] = strtotime($value['L']).'000';
                $data['startcity'] = $start_city['id'];
                $data['endcity'] = $end_city['id'];
                $data['sendaddress'] = json_encode($pei_arr);
                $data['pickaddress'] = json_encode($pick_arr);
                $data['carid'] = $carid[0];
               // print_r($carid[0]);
                // exit();
                $car_type = Db::table('ct_cartype')->where('car_id',$carid[0])->find();

                $start_action = bd_local($type='2',$value['H'],$pickaddress);//经纬度
                $end_action = bd_local($type='2',$value['I'],$paddress);//经纬度
                $finally ='';
                $list = getDriverline($start_action['lat'], $start_action['lng'], $end_action['lat'], $end_action['lng']);
               
                foreach ($list as $key=> $v) {
                    if ($key=="distance") {
                        $finally = sprintf("%.2f",$v['value']/1000);
                    }
                }
                $ride_finally = 0;
                if ($finally <= 500) {
                    $ride_finally = $finally*1.05;
                }elseif($finally>500){
                    $ride_finally = $finally*1.03;
                }
                $data['mileage'] = sprintf('%.2f',$ride_finally);
                $price = $ride_finally* $car_type['costkm'];
                $data['price'] = max($car_type['lowprice'],$price);
                $data['referprice'] = max($car_type['lowprice'],$price);
                $insert = DB::table('ct_userorder')->insertGetId($data);
                if (!$insert) {
                    @unlink($delpath);
                    $this->error("数据有误!!");
                }
               // $arr_list[] = $data;
            }

		}

        if ($insert) {
            @unlink($delpath);
            $this->success('添加成功','orderimport/imcarload');
        }
	}
    public function imcity(){
          $carrier_id['uid'] = Session::get('carrier_id','carrier_mes');
        $company = Db::field('lineclient')->table('ct_user')->where($carrier_id)->find();
        $user = Db::table('ct_user')->where('lineclient',$company['lineclient'])->select();
        $this->assign('list',$user);
        return view('orderimport/imcity');
    }
    /*
    *市内配送
    */
   /* public function cityorder(){
        $postdata = Request::Instance()->post();
        
        if (request()->file('file') !='') {
            $path = $this->file_upload('file','xls,xlsx','execl');
            $path = $path['file_path'];
            $list = $this->reader('../public'.$path);//导入

            // echo "<pre/>";
            //print_r($list);
            foreach ($list as $key => $value) {
                $data['userid'] = $postdata['userid'];
                if ($value['F'] == "当日达") {
                    $arriver = 1; 
                }else{
                    $arriver = 2;
                }
                if ($value['J'] == "冷冻") {
                    $cold = 1; 
                }elseif ($value['J'] == "冷藏") {
                    $cold = 2;
                }else{
                    $cold = 3;
                }
                $data['orderid'] = 'SP'.date('Ymdhis').mt_rand('000000','999999');
                $data['all_number'] = $value['B'];
                $data['all_weight'] = $value['C'];
                $data['all_volume'] = $value['D'];
                $data['pickphone'] = $value['I'];
                $data['shipperid'] = $value['H'];
                $data['addtime'] = strtotime($value['E']);
                $data['data_type'] = $arriver;
                $data['cold_type'] = $cold;
                $data['sent_type'] = $value['G'];
                $data['contactid'] = $value['P'];
                $data['sendphone'] = $value['Q'];
                //发货地址
                //发货城市ID
                $citycondition['name'] = ['like',$value['K'].'%'];
                $citycondition['level'] = 2;
                $city_id = Db::table('ct_district')->where($citycondition)->find();
                $arr['tcity_id'] = $city_id['id'];
                //发货省ID
                $pro_id = Db::table('ct_district')->where(array('id'=>$city_id['parent_id']))->find();
                $arr['tpro_id'] = $pro_id['id'];
                //发货区ID
                $areacondition['name'] = ['like',$value['L'].'%'];
                $areacondition['level'] = 3;
                $area_id = Db::table('ct_district')->field('id,name')->where($areacondition)->find();
                $arr['tarea_id'] = $area_id['id'];
                $arr['taddress'] = $value['M'];
                $data['saddress'] = json_encode($arr);
                //到货地址
                //配送区ID
                $arrvarea['name'] = ['like',$value['N'].'%'];
                $arrvarea['level'] = 3;
                $arr_area = DB::table('ct_district')->where($arrvarea)->find();
                $arr_send['spro_id'] = $pro_id['id'];
                $arr_send['scity_id'] = $city_id['id'];
                $arr_send['sarea_id'] = $arr_area['id'];
                $arr_send['paddress'] = $value['O'];
                $data['eaddress'] = json_encode($arr_send);
                //城市费用区间
                $city_cost = Db::table('ct_city_section')->where('cityid','9')->select();
                if ($area_id['id'] == '161' || $arr_area['id'] == '161') {
                    $paymoney = 800;
                }elseif($area_id['id'] == '156' || $arr_area['id'] == '156'){
                    $paymoney = 700;
                }elseif($area_id['id'] == '160' || $arr_area['id'] == '160'){
                    $paymoney = 600;
                }else{
                    foreach ($city_cost as $keys => $values) {
                        if ($value['C'] > $values['weight_start'] && $value['C'] <= $values['weight_end']) {
                            $paymoney = $value['C']*$values['billing'];
                            if ($paymoney < $values['low_pirce']) {
                                $paymoney = $values['low_pirce'];
                            }
                        }
                    }// end foreach
                } //end if 
                $data['paymoney'] = $paymoney;
                $data['actualprice'] = $paymoney;
                $data['state'] = '2';
                $insert_id = DB::table('ct_city_order')->insertGetId($data);
                if ( $insert_id) {
                    $city = '9' ;//城市ID暂时等于9
                        $city_condition = Db::table('ct_city_cost')->where('c_city',$city)->find();
                        if ($paymoney > $city_condition['spellmoney'] || $value['C'] > $city_condition['spellweight']) {
                            $data_rout['count_money'] = $type_data['paymoney'];
                            $data_rout['start_time'] = time();
                            if ($arriver == '1') {
                                $data_rout['runtime'] = strtotime(date("Y-m-d"));
                            }else{
                                $data_rout['runtime'] = strtotime(date("Y-m-d",strtotime("+1 day")));
                            }
                            $result = Db::table("ct_rout_order")->insert($data_rout);
                            $rout_id = Db::table("ct_rout_order")->getLastInsID();
                            Db::table('ct_city_order')->where('id',$insert_id)->update(array('rout_id'=>$rout_id,'state'=>'2'));
                            //向承运商发送信息
                            
                        }else{
                            $this->city_stitching($city);
                        }
                    }
                
                
                //$arr_list[] = $data;
            }// end foreach
            if ($insert_id) {
                $this->success('添加成功','orderimport/imcity');
            }
            //echo "<pre/>";
            //print_r($arr_list);
        }//
    }*/

    public function cityorder(){
        $postdata = Request::Instance()->post();
        if (request()->file('file') !='') {
            $path = $this->file_upload('file','xls,xlsx','execl');
            $path = $path['file_path'];
            $list = $this->reader('../public'.$path);//导入
            foreach ($list as $key => $value) {
                $car_arr = array('1'=>'4.2','2'=>'5.2','3'=>'7.6','4'=>'9.6前四后四','5'=>'9.6前四后八','6'=>'12.5','7'=>'15','9'=>'面包车');
                $carid = array_keys($car_arr,$value['C']);
                $ordertype= 1;
                $city=$value['E'];
                $pickaddress = explode("/", $value['F']);  //装货地址
                $sendaddress = explode("/", $value['G']);  //装货地址

                $pickarr = array();
                foreach ($pickaddress as $key1 => $value1) {
                    $pickarr[$key1]['address'] = $value1;
                }
                $sendarr = array();
                $i=1;
                foreach ($sendaddress as $key2 => $value2) {
                    $sendarr[$key2]['address'] = $value2;
                    $sendarr[$key2]['addtabid'] = $i;
                }

                if ($ordertype == 1) {
                    $carmess = DB::table('ct_cartype')->where('car_id',$carid[0])->find();
                    $start_action = bd_local($type='2',$city,$pickarr['0']['address']);//起始位置经纬度
                    $end_action = bd_local($type='2',$city,$sendarr['0']['address']);//重点位置经纬度
                    $finally ='';
                    
                    $list = getDriverline($start_action['lat'], $start_action['lng'], $end_action['lat'], $end_action['lng']);
                    foreach ($list as $key=> $v) {
                        if ($key=="distance") {
                            $finally = sprintf("%.2f",$v['value']/1000);
                        }
                    }
                    $price = round($carmess['lowprice']+$finally*$carmess['costkm']);
                }

                $data['userid'] = $postdata['userid']; 
                $data['orderid'] = 'SP'.date('Ymdhis').mt_rand('000000','999999');
                $data['cold_type'] = $value['D'];
                $data['data_type'] = $value['B'];
                $data['paymoney'] = $price;
                $data['actualprice'] = $price;
                $data['saddress'] = json_encode($pickarr);
                $data['eaddress'] = json_encode($sendarr);
                $data['remark'] = $value['H']; 
                $data['addtime'] = time();
                $data['paystate'] = 2;
                $data['state'] = 1;
                $data['ordertype'] = $ordertype;
                $data['carid'] = $carid[0];
                $data['pytype'] = 1;
                $data['remark'] = 1;
                Db::startTrans();
                try{
                    $rout_data['start_time'] = time();
                    $rout = Db::table('ct_rout_order')->insertGetId($rout_data);
                    $data['rout_id'] = $rout;
                    $insert = DB::table('ct_city_order')->insertGetId($data);
                    Db::commit();    
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    @unlink($delpath);
                    $this->error("数据有误!!");
                }
            }

            if ($insert) {
                @unlink($delpath);
                $this->success('添加成功','orderimport/imcity');
            }
        }
    }
	static function reader($file) {
        if (self::_getExt($file) == 'xls') {
            $result = import("Excel5",EXTEND_PATH.'PHPExcel/PHPExcel/Reader');
            $PHPReader = new \PHPExcel_Reader_Excel5();
        } elseif (self::_getExt($file) == 'xlsx') {
            $result = import("Excel2007",EXTEND_PATH.'PHPExcel/PHPExcel/Reader');
            $PHPReader = new \PHPExcel_Reader_Excel2007();
        } else {
            return '路径出错';
        }

        $PHPExcel     = $PHPReader->load($file);
        $currentSheet = $PHPExcel->getSheet(0);
        $allColumn    = $currentSheet->getHighestColumn();
        $allRow       = $currentSheet->getHighestRow();
        for($currentRow = 2; $currentRow <= $allRow; $currentRow++){
            for($currentColumn='B'; $currentColumn <= $allColumn; $currentColumn++){
                $address = $currentColumn.$currentRow;
                $arr[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
            }
        }
        return $arr;
    }

    private static function _getExt($file) {
        return pathinfo($file, PATHINFO_EXTENSION);
    }
}
