<?php
/*
*订单文件导入
*/
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Orderimport extends Base{
	function __construct(){
        parent::__construct();

    }
	public function imcarload(){
		return view('orderimport/imcarload');
	}
	/*
	*整车订单导入
	*/
	public function carload(){
		if (request()->file('file') !='') {
			$path = $this->file_upload('file','xls,xlsx','execl');
	        $path = $path['file_path'];
	       
	        $list = $this->reader('../public'.$path);//导入
            foreach ($list as $key => $value) {
                $user = DB::table('ct_user')->where('phone',$value['F'])->find();
                $car_arr = array('1'=>'4.2','2'=>'5.2','3'=>'7.6','4'=>'9.6前四后四','5'=>'9.6前四后八','6'=>'13.5','7'=>'15');
                $carid = array_keys($car_arr,$value['D']);
                $paddress = $value['L'];//收货地址
                $pickaddress = $value['J'];
                if ($value['K'] == "否") {
                    $pickyes = 1;
                }else{
                    $pickyes = 2;
                }
                $pick_arr = array(
                        array(
                            'areaName'=>$pickaddress,
                            'areaID'=>1,
                            'temperature'=>$value['G'],
                            'goodType'=>$value['C'],
                            'pickyes'=>$pickyes
                        )
                    );
                $pei_arr = array(
                        array(
                            'areaName'=>$paddress,
                            'areaID'=>1,
                            'username'=>$value['M'],
                            'telephone'=>$value['N']
                        )
                    );
                $start_city = Db::table('ct_district')->field('id')->where('name',$value['H'])->find();
                $end_city = Db::table('ct_district')->field('id')->where('name',$value['I'])->find();
                $data['ordernumber'] = 'K'.date('Ymdhis').mt_rand('000000','999999');
                $data['remark'] = $value['O'];
                $data['paystate'] = 2;
                $data['loaddate'] = strtotime($value['B']).'000';
                $data['addtime'] = time();
                $data['arrtime'] = strtotime($value['P']);
                $data['startcity'] = $start_city['id'];
                $data['endcity'] = $end_city['id'];
                $data['sendaddress'] = json_encode($pei_arr);
                $data['pickaddress'] = json_encode($pick_arr);
                $data['carid'] = $carid[0];
                $arr_list[] = $data;
            }
            //
	        echo "<pre/>";
	        print_r($arr_list);

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
