<?php
namespace app\backstage\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
/**
  * Author: baobaolong
  */
class Billexport extends Base
{
	function __construct(){
		//导入phpExcel核心类
		include_once('PHPExcel.php');
    }
	public function index(){
        $this->display();
    }
	//导入excel代码实现
	public function orderImport(){
		error_reporting(E_ALL);
        $objPHPExcel = new PHPExcel();
        /*以下是一些设置 ，什么作者  标题啊之类的*/
        echo '1';
        exit;
        $objPHPExcel->getProperties()
		    ->setCreator("转弯的阳光")
		    ->setLastModifiedBy("转弯的阳光")
		    ->setTitle("数据EXCEL导出")
		    ->setSubject("数据EXCEL导出")
		    ->setDescription("备份数据")
		    ->setKeywords("excel")
		    ->setCategory("result file");
        /*以下就是对处理Excel里的数据， 横着取数据，主要是这一步，其他基本都不要改*/
        foreach($data as $k => $v){
             $num=$k+1;
             $objPHPExcel->setActiveSheetIndex(0)
                //Excel的第A列，uid是你查出数组的键值，下面以此类推
                ->setCellValue('A'.$num, $v['uid'])    
                ->setCellValue('B'.$num, $v['email'])
                ->setCellValue('C'.$num, $v['password']);
            }
            $objPHPExcel->getActiveSheet()->setTitle('User');
            $objPHPExcel->setActiveSheetIndex(0);
             header('Content-Type: application/vnd.ms-excel');
             header('Content-Disposition: attachment;filename="'.$name.'.xls"');
             header('Cache-Control: max-age=0');
             $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
             $objWriter->save('php://output');
             exit;





	}
	//导出excel代码实现
	public function orderexport(){

	}
}


