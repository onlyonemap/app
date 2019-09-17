<?php
/*
*author:chenwei
*/
namespace app\backstage\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
//use phpexcel\PHPExcel;
class Execl  extends Base
{
	function __construct(){
        parent::__construct();
        $this->if_login();

    }

   public function index(){
   
    $xlsCell  = array(
        array('ID','ID'),
        array('number','批次'),
        array('OrderNumber','订单编号'),
        array('CompanyName','公司名称')
      
    );
    $data = array(
      array('ID'=>'1','number'=>'小明','OrderNumber'=>'A','CompanyName'=>'D'),
      array('ID'=>'2','number'=>'小红','OrderNumber'=>'A','CompanyName'=>'D'),
      array('ID'=>'3','number'=>'小王','OrderNumber'=>'A','CompanyName'=>'D'),
      array('ID'=>'4','number'=>'小张','OrderNumber'=>'A','CompanyName'=>'d'),
      array('ID'=>'5','number'=>'小李','OrderNumber'=>'A','CompanyName'=>'a'),
      array('ID'=>'6','number'=>'老五','OrderNumber'=>'A','CompanyName'=>'b'),
      array('ID'=>'7','number'=>'小七','OrderNumber'=>'A','CompanyName'=>'b'),
      array('ID'=>'8','number'=>'小八','OrderNumber'=>'A','CompanyName'=>'b')
      );
    $this->writertwo('test',$xlsCell,$data);//导出 此导出表头最长为A-Z,如果需要更长，请自行更改
    // $list = $this->reader('./UploadFiles/excel/ceshi.xls');//导入
   
  }

  public function carrexecl(){
    $get_data = Request::instance()->get();
    $orderarr = explode(',', $get_data['orderID']);
    $statearr = explode(',', $get_data['state']);
    $arr = array();
    $allline = array();
    $pick_line = array();
    $line_line = array(); 
    $carriarr = array();
    $all_line_price=0;
    $pick_line_price=0;
    $line_line_price=0;
    foreach ($orderarr as $key => $value) {
        $arr[$value] = $statearr[$key];
    }
    foreach ($arr as $key2 => $value2) {
        if ($value2 ==1) {
           $select_allline = Db::field('b.*,a.tprice,c.Invoiceno,c.Invoiceamount,c.paytime')
                              ->table('ct_pickorder')
                              ->alias('a')
                              ->join('ct_order b','b.oid = a.orderid')
                              ->join('ct_invoice c','c.iid = a.pic_checkid')
                              ->where('a.orderid',$key2)
                              ->find();
           $all_line_price =  ($select_allline['tprice']+$select_allline['linepice'] + $select_allline['delivecost']);
           $select_allline['countcoat'] = $all_line_price;
            $allline[] = $select_allline;
         }
         if ($value2 ==2) {
              $select_pick =  Db::field('b.*,a.tprice,c.Invoiceno,c.Invoiceamount,c.paytime')
                              ->table('ct_pickorder')
                              ->alias('a')
                              ->join('ct_order b','b.oid = a.orderid')
                              ->join('ct_invoice c','c.iid = a.pic_checkid')
                              ->where('a.orderid',$key2)
                              ->find();
             $pick_line_price =$select_pick['tprice'];
             $select_pick['linepice']= 0;
             $select_pick['delivecost']= 0;
             $pick_line['countcoat']= $pick_line_price;
             $pick_line[] = $select_pick;
         }
         if ($value2 ==3) {
             $select_line = Db::field('b.*,c.Invoiceno,c.Invoiceamount,c.paytime')
                            ->table('ct_lineorder')
                            ->alias('a')
                            ->join('ct_order b','b.oid = a.orderid')
                            ->join('ct_invoice c','c.iid = a.line_checkid')
                            ->where('a.orderid',$key2)
                            ->find();
             $line_line_price = ($select_line['linepice'] + $select_line['delivecost']);
             $select_line['tprice']= 0;
             $select_line['countcoat']= $line_line_price;
             $line_line[] = $select_line;
         }
    }
    $mergearr = array_merge($allline,$pick_line,$line_line);
    foreach ($mergearr as  $val) {
        $shift = DB::field('c.start_id,c.end_id,d.name')
                    ->table('ct_shift_log')
                    ->alias('a')
                    ->join('ct_shift b','b.sid = a.shiftid')
                    ->join('ct_already_city c','c.city_id = b.linecityid')
                    ->join('ct_company d','d.cid = b.companyid')
                    ->find();
            $carriarr[] = array(
                    'OrderNumber' => $val['ordernumber'],
                    'CompanyName' =>$shift['name'],
                    'AddTime' => date('Y-m-d H:i',$val['addtime']),
                    'Line' => $this->start_end_city($shift['start_id'],$shift['end_id']),
                    'ti_pay'=>$val['tprice'],
                    'TrunkCost' => $val["linepice"],
                    'pei_pay' => $val["delivecost"],
                    'total' => $val["countcoat"],
                    'invoNo' => $val["Invoiceno"],
                    'pay' => $val["Invoiceamount"],
                    'payTime' => $val["paytime"]=='' ? '' : date('Y-m-d',$val["paytime"])
                );
          
    }
    $xlsCell  = array(
        array('OrderNumber','订单编号'),
        array('CompanyName','公司名称'),
        array('AddTime','下单时间'),
        array('Line','线路'),
        array('ti_pay','提货费(元)'),
        array('TrunkCost','干线费(元)'),
        array('pei_pay','配送费(元)'),
        array('total','金额(元)'),
        array('invoNo','发票号'),
        array('pay','开票金额'),
        array('payTime','开票日期')
    );
    $this->writertwo('物流公司',$xlsCell,$carriarr);
  }

  public function billexport(){
        // $get_data = Request::instance()->get();
        $id = input('id');
        $id = input('id');
        $carrid = Session::get('carrier_id','carrier_mes');
        $company_type = Db::table('ct_carriers')->where('carrid',$carrid)->find();
        $invoice_data = Db::table('ct_invoice')->where('iid',$id)->find();
        switch ($company_type['type']) {
            //干线公司对账列表
            case '1':
                    $result = Db::field('b.*,e.start_id,e.end_id,d.shiftnumber,c.deptime')
                        ->table('ct_lineorder')
                        ->alias('a')
                        ->join('ct_order b','b.oid=a.orderid')
                        ->join('ct_shift_log c','c.slid=b.slogid')
                        ->join('ct_shift d','d.sid=c.shiftid')
                        ->join('ct_already_city e','e.city_id=d.linecityid')
                        ->where('a.line_checkid',$id)
                        ->select();
                    foreach ($result as $key => $value) {
                        $tprice = Db::table('ct_pickorder')->where('orderid',$value['oid'])->find();
                        if ($tprice['type'] != 2) {
                            $result[$key]['pickcost'] = '0';
                        }
                        $result[$key]['start_id'] = $this->completeAddress($value['start_id'],'','');
                        $result[$key]['end_id'] = $this->completeAddress($value['end_id'],'','');
                        
                    }
                break;
            //项目用户对账
            case '3':
                    $result = Db::field('a.*,c.shiftnumber,d.start_id,d.end_id,b.deptime')
                        ->table('ct_order')
                        ->alias('a')
                        ->join('ct_shift_log b','b.slid=a.slogid')
                        ->join('ct_shift c','c.sid=b.shiftid')
                        ->join('ct_already_city d','d.city_id=c.linecityid')
                        ->where('a.user_checkid',$id)
                        ->select();
                    foreach ($result as $key => $value) {
                        $start_id = $this->completeAddress($value['start_id'],'','');
                        $end_id = $this->completeAddress($value['end_id'],'','');
                        
                        $result[$key]['start_id'] = $start_id;
                        $result[$key]['end_id'] = $end_id;
                    }
                break;
            //提货公司对账
            case '2':
                    $result = Db::field('b.ordernumber,a.*,c.realname')
                        ->table('ct_pickorder')
                        ->alias('a')
                        ->join('ct_order b','b.oid=a.orderid')
                        ->join('ct_driver c','c.drivid=a.driverid')
                        ->where('a.pic_checkid',$id)
                        ->select();
                break;
        }









        echo '<pre>';
        print_r($result);


        // 下单总额
        // $countcoat = 0;
        // $userarr = array();
        // $result = 
        // foreach ($result as $key => $value) {
        //     if($value['lineclient'] == ''){
        //         if ($value['company'] == '') {
        //             $CompanyName =$value['realname'];
        //         }else{
        //             $CompanyName =$value['company'];
        //         }
        //     }else{
        //         $comname = DB::field('name')->table('ct_company')->where('cid',$value['lineclient'])->find();
        //         $CompanyName = $comname['name'];
        //     }
        //     if($value['userState'] == 1){
        //         $client_state = '小微客户';
        //     }elseif($value['userState'] == 2){
        //         $client_state = '项目客户';
        //     }elseif($value['userState'] ==3){
        //         $client_state = '线下客户';
        //     }
        //     $countcoat = ($value['tprice']+$value['linepice']+$value['delivecost']);
        //     $count_all = $value['totalcost']=='' ? $countcoat : $value['totalcost'];
        //     $userarr[] = array(
        //             'ID' => $value['oid'],
        //             'OrderNumber' =>$value['ordernumber'],
        //             'CompanyName' =>$CompanyName,
        //             'AddTime' => date('Y-m-d H:i',$value['addtime']),
        //             'Line' => $this->start_end_city($value['start_id'],$value['end_id']),
        //             'get_weight' => $value['totalweight'],
        //             'get_volume' => $value['totalvolume'],
        //             'ti_pay' => $value['tprice'],
        //             'TrunkCost' => $value['linepice'],
        //             'pei_pay' => $value['delivecost'],
        //             'total' => $countcoat,
        //             'states' =>$client_state,
        //             'count_all'=>$count_all,
        //             'invoNo' => $value['Invoiceno'],
        //             'pay' => $value['Invoiceamount'],
        //             'payTime' => $value["paytime"]=='' ? '' : date('Y-m-d',$val["paytime"])
        //         ); 
        // }
        // $xlsCell  = array(
        //     array('ID','ID'),
        //     array('OrderNumber','订单编号'),
        //     array('CompanyName','公司名称'),
        //     array('states','客户属性'),
        //     array('AddTime','下单时间'),
        //     array('Line','线路'),
        //     array('get_weight','重量(kg)'),
        //     array('get_volume','立方(m³)'),
        //     array('ti_pay','提货费(元)'),
        //     array('TrunkCost','干线费(元)'),
        //     array('pei_pay','配送费(元)'),
        //     array('total','金额(元)'),
        //     array('count_all','交易额(元)'),
        //     array('invoNo','发票号'),
        //     array('pay','开票金额'),
        //     array('payTime','开票日期')
        // );
        // $this->writertwo('对账',$xlsCell,$userarr);
        //echo $get_data;
        //$orderarr = explode(',', $get_data['orderID']);

  }

static function writertwo($expTitle,$expCellName,$expTableData,$type = 0){
        $result = import("PHPExcel",EXTEND_PATH.'PHPExcel');
        $objPHPExcel = new \PHPExcel();
        $xlsTitle = iconv('utf-8', 'gb2312//IGNORE', $expTitle);//文件名称
        $fileTitle =  $xlsTitle.date('_Y-m-d His');//or $xlsTitle 文件名称可根据自己情况设定
        $cellNum = count($expCellName);
        $dataNum = count($expTableData);
        $countArr  = array('get_weight','get_volume','ti_pay','TrunkCost','pei_pay','total','count_all','pay');
        $cellName = array('A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z','AA','AB','AC','AD','AE','AF','AG','AH','AI','AJ','AK','AL','AM','AN','AO','AP','AQ','AR','AS','AT','AU','AV','AW','AX','AY','AZ');
        $objPHPExcel->getActiveSheet(0)->mergeCells('A1:'.$cellName[$cellNum-1].'1');//合并单元格
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle.'  对账列表:'.date('Y-m-d H:i:s'));  
        for($i=0;$i<$cellNum;$i++){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i].'2', $expCellName[$i][1]); 
        } 
          // Miscellaneous glyphs, UTF-8
        $end = $dataNum+2;
        for($i=0;$i<$dataNum;$i++){
          for($j=0;$j<$cellNum;$j++){
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+3), $expTableData[$i][$expCellName[$j][0]]);
            if(in_array($expCellName[$j][0], $countArr)){
                $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j].($i+4), '=SUM('.$cellName[$j].'3:'.$cellName[$j].$end.')');
            }
          } 
                   
        }  
        $fileName = iconv("utf-8", "gb2312//IGNORE", './Data/excel/'.date('Y-m-d_', time()).time().'.xls');
        $saveName = iconv("utf-8", "gb2312//IGNORE", $fileTitle.'.xls');
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        if ($type == 0) {
             header('Content-type:application/vnd.ms-excel;charset=utf-8;name="'.$xlsTitle.'.xls"');
            header("Content-Disposition: attachment;filename=\"$saveName\"");
            header('Cache-Control: max-age=0');
            $objWriter->save('php://output');
        } else {
            $objWriter->save($fileName);
            return $fileName;
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
        for($currentRow = 1; $currentRow <= $allRow; $currentRow++){
            for($currentColumn='A'; $currentColumn <= $allColumn; $currentColumn++){
                $address = $currentColumn.$currentRow;
                $arr[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
            }
        }
        return $arr;
    }

    private static function _getExt($file) {
        return pathinfo($file, PATHINFO_EXTENSION);
    }


    

    /*public function in(){
        $content = file_get_contents('./UploadFiles/excel/ceshi.xls');
        dump($content);exit;

    }*/

    
}
