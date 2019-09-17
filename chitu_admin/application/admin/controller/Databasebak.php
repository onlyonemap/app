<?php
namespace app\admin\controller;
use  think\Controller;   //使用控制器
use OT\Database;
use  think\Db;  //使用数据库操作
use think\Request; 
use think\Session;
class Databasebak  extends Base
{
	function __construct(){
        parent::__construct();
         $config['path']="databak/";
        $this->if_login();
    }
    /*
    *数据备份
    */
	public function index(){
        //$Db = Db::getInstance();
        $array = array();
        $list  = Db::query('SHOW TABLE STATUS');
        $list  = array_map('array_change_key_case', $list);
        foreach ($list as $key => $value) {
            $array[] = $value;
            $array[$key]['data_length'] = $this->format_bytes($value['data_length']);
        }

        $this->assign('list', $array);
        return view('databasebak/export');
    }

    /**
     * 优化表
     *
     * 
     */
    public function optimize(){
        $get_data = Request::instance()->get();
        if ($get_data['state'] == 2) {
            $tables = Request::instance()->get('table/a');
            if (is_array($tables)) {
                 $tables = implode('`,`', $tables);
                $list = DB::query("OPTIMIZE TABLE `{$tables}`");
                 if($list){
                    //$this->success("数据表优化完成！");
                    print_r("ok");
                } else {
                    //$this->success("优化出错请重试！");
                    print_r("fail");
                } 
            }
        }else{
            $tables = $get_data['table'];
            $list = DB::query("OPTIMIZE TABLE `{$tables}`");
             if($list){
                $this->success("数据表'{$tables}'优化完成！");
            } else {
                $this->error("数据表'{$tables}'优化出错请重试！");
            } 
        }
       
    }
    /*
    *修复表
    */
    public function repair(){
         $get_data = Request::instance()->get();
         if ($get_data['state'] == 1) {
            $tables = $get_data['table'];
             $list = Db::query("REPAIR TABLE `{$tables}`");
            if($list){
                $this->success("数据表'{$tables}'修复完成！");
            } else {
                $this->error("数据表'{$tables}'修复出错请重试！");
            }
         }else{
            $tables = Request::instance()->get('table/a');
            if (is_array($tables)) {
                 $tables = implode('`,`', $tables);
                $list = DB::query("REPAIR TABLE `{$tables}`");
                 if($list){
                    //$this->success("数据表优化完成！");
                    print_r("ok");
                } else {
                    //$this->success("优化出错请重试！");
                    print_r("fail");
                } 
            }
         }
    }
    /*
    *数据库备份
    */
    public function export(){
            $type=input("tp");
               $name=input("name");
               //echo $type;
               //exit();
               //$sql=new \org\Baksql(\think\Config::get("database"));
                import("Databasebc",EXTEND_PATH.'Databasebc');
                $databak = new \Databasebc(\think\Config::get("database"));
               switch ($type)
                {
                case "backup": //备份
                  return $databak->backup();
                  break;  
                case "dowonload": //下载
                  $databak->downloadFile($name);
                  break;  
                case "restore": //还原
                  return $databak->restore($name);
                  break; 
                case "del": //删除
                  return $databak->delfilename($name);
                  break;                            
                }
    }

    public function bak(){

        $FilePath2="databak/";
        $FilePath = opendir($FilePath2);
        $FileAndFolderAyy=array();
        $i=1;
        while (false !== ($filename = readdir($FilePath))) {
            if ($filename!="." && $filename!=".."){
            $i++;
            $FileAndFolderAyy[$i]['name'] = $filename;
            //$FileAndFolderAyy[$i]['time'] = $this->getfiletime($filename);
            $FileAndFolderAyy[$i]['time'] = filectime("$FilePath2/$filename");
            $FileAndFolderAyy[$i]['size'] = $this->getFilesize($FilePath2.$filename);
            //$FileAndFolderAyy[$i]['time'] = filectime("$FilePath2/$filename");
            }
        }
        //var_dump($FileAndFolderAyy);
        $this->assign('list',$FileAndFolderAyy);
        return view('databasebak/bak');
    }

    public function test(){
      
     /* $file = '20170610143038.sql';
      $sql=new \org\Baksql(\think\Config::get("database"));
      $sql->restore($file);*/
      import("Databasebc",EXTEND_PATH.'Databasebc');
      $databak = new \Databasebc(\think\Config::get("database"));
       //echo "<pre/>";
       //print_r($databak->backup('ct_addressinfo')) ;
        //print_r($databak->restore('20170613160624.sql')) ;
      print_r($databak->delfilename('20170613160624.sql'));

    }

    public function datainfo(){
        $uid = Session::get('admin_id','admin_mes');
        if ($uid =='') {
            $this->redirect("index/login");
        }

        $timestamp = time();
        //$start = mktime(0,0,0,date("m",time()),date("d",time()),date("Y",time()));
        //$end = mktime(23,59,59,date("m",time()),date("d",time()),date("Y",time()));
        // 昨天开始时间
        $start = mktime(0,0,0,date('m'),date('d')-1,date('Y'));
        // 昨天结束时间
        $end =mktime(0,0,0,date('m'),date('d'),date('Y'))-1;
        // 当周开始时间
        $toweek_start = strtotime(date('Y-m-d', strtotime("this week Monday", $timestamp)));
        // 当周结束时间
        $toweek_end = strtotime(date('Y-m-d', strtotime("this week Sunday", $timestamp))) + 24 * 3600 - 1 ;
        // 当月开始时间
        $tomoth_start = mktime(0, 0, 0, date('m'), 1, date('Y'));
        // 当月结束时间
        $tomoth_end = mktime(23, 59, 59, date('m'), date('t'), date('Y'));
        // 当年开始时间
        $toyear_start = mktime(0, 0, 0, 1, 1, date('Y'));
        // 当年结束时间
        $toyear_end = mktime(23, 59, 59, 12, 31, date('Y'));
        //20180423  支付宝统计
        // 零担支付宝统计
        $where_pay_today['paytime'] = array(array('gt',$start),array('lt', $end));
        $where_pay_toweek['paytime'] = array(array('gt',$toweek_start),array('lt', $toweek_end));
        $where_pay_tomoth['paytime'] = array(array('gt',$tomoth_start),array('lt', $tomoth_end));
        $where_pay_toyear['paytime'] = array(array('gt',$toyear_start),array('lt', $toyear_end));
        $where_pay_state['state'] = 1;
        $where_alipay['paytype'] = 1;
        //支付宝今天收入
        $alipay_today_order = Db::table('ct_paymessage')->where($where_alipay)->where($where_pay_state)->where($where_pay_today)->sum('paynum');
        //支付宝当周收入
        $alipay_toweek_order = Db::table('ct_paymessage')->where($where_alipay)->where($where_pay_state)->where($where_pay_toweek)->sum('paynum');
        //$alipay_toweek_order =  DB::query("SELECT sum(paynum) as sumweek_order FROM `ct_paymessage` where paytype =1 AND state=1 AND DATE_FORMAT(FROM_UNIXTIME(paytime),'%w')=DATE_FORMAT(FROM_UNIXTIME(".time()."),'%w')");
        //支付宝当月收入
        $alipay_tomoth_order = Db::table('ct_paymessage')->where($where_alipay)->where($where_pay_state)->where($where_pay_tomoth)->sum('paynum');
        //$alipay_tomoth_order = DB::query("SELECT sum(paynum) as summoth_order FROM `ct_paymessage` where paytype =1 AND state=1 AND DATE_FORMAT(FROM_UNIXTIME(paytime),'%m')=DATE_FORMAT(FROM_UNIXTIME(".time()."),'%m')");
        //支付宝当年收入
        $alipay_toyear_order = Db::table('ct_paymessage')->where($where_alipay)->where($where_pay_state)->where($where_pay_toyear)->sum('paynum');
        //$alipay_toyear_order = DB::query("SELECT sum(paynum) as sumyear_order FROM `ct_paymessage` where paytype =1 AND state=1 AND DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y')=DATE_FORMAT(FROM_UNIXTIME(".time()."),'%Y')");
        //支付宝总收入
        $alipay_sumall_order = Db::table('ct_paymessage')->where($where_alipay)->where($where_pay_state)->sum('paynum');
        //$alipay_sum = array('sum_alipay_All'=>number_format($alipay_sumall_order,2),'sum_alipay_year'=>number_format($alipay_toyear_order[0]['sumyear_order'],2),'sum_alipay_tmoth'=>number_format($alipay_tomoth_order[0]['summoth_order'],2),'sum_alipay_week'=>number_format($alipay_toweek_order[0]['sumweek_order'],2),'sum_alipay_day'=>number_format($alipay_today_order,2));
        $alipay_sum = array('sum_alipay_All'=>$alipay_sumall_order,'sum_alipay_year'=>$alipay_toyear_order,'sum_alipay_tmoth'=>$alipay_tomoth_order,'sum_alipay_week'=>$alipay_toweek_order,'sum_alipay_day'=>$alipay_today_order);
        $this->assign('alipay',$alipay_sum);
        //20180423
        //20180423  微信统计
        //微信统计
        $where_wechat['paytype'] = 2;

        //微信今天收入
        $wechat_today_order = Db::table('ct_paymessage')->where($where_wechat)->where($where_pay_state)->where($where_pay_today)->sum('paynum');
        //微信当周收入
        $wechat_toweek_order = Db::table('ct_paymessage')->where($where_wechat)->where($where_pay_state)->where($where_pay_toweek)->sum('paynum');
        //$wechat_toweek_order =  DB::query("SELECT sum(paynum) as sumweek_order FROM `ct_paymessage` where paytype =2 AND state=1 AND DATE_FORMAT(FROM_UNIXTIME(paytime),'%w')=DATE_FORMAT(FROM_UNIXTIME(".time()."),'%w')");
        //微信当月收入
        $wechat_tomoth_order = Db::table('ct_paymessage')->where($where_wechat)->where($where_pay_state)->where($where_pay_tomoth)->sum('paynum');
        //$wechat_tomoth_order = DB::query("SELECT sum(paynum) as summoth_order FROM `ct_paymessage` where paytype =2 AND state=1 AND DATE_FORMAT(FROM_UNIXTIME(paytime),'%m')=DATE_FORMAT(FROM_UNIXTIME(".time()."),'%m')");
        //微信当年收入
        $wechat_toyear_order = Db::table('ct_paymessage')->where($where_wechat)->where($where_pay_state)->where($where_pay_toyear)->sum('paynum');
        //$wechat_toyear_order = DB::query("SELECT sum(paynum) as sumyear_order FROM `ct_paymessage` where paytype =2 AND state=1 AND DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y')=DATE_FORMAT(FROM_UNIXTIME(".time()."),'%Y')");
        //微信总收入
        $wechat_sumall_order = Db::table('ct_paymessage')->where($where_wechat)->where($where_pay_state)->sum('paynum');
        $wechat_sum = array('sum_wechat_All'=>$wechat_sumall_order,'sum_wechat_year'=>$wechat_toyear_order,'sum_wechat_tmoth'=>$wechat_tomoth_order,'sum_wechat_week'=>$wechat_toweek_order,'sum_wechat_day'=>$wechat_today_order);
        $this->assign('wechat',$wechat_sum);
        /**
         * 2018-5-20
         * 信用支付金额汇总
         * 李渊
         */
        $where_day['addtime'] = array(array('gt',$start),array('lt', $end));
        $where_week['addtime'] = array(array('gt',$toweek_start),array('lt', $toweek_end));
        $where_month['addtime'] = array(array('gt',$tomoth_start),array('lt', $tomoth_end));
        $where_year['addtime'] = array(array('gt',$toyear_start),array('lt', $toyear_end));
        // 市配订单金额
        $city_day_paymoney = DB::table('ct_city_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_day)->sum('paymoney');
        $city_week_paymoney = DB::table('ct_city_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_week)->sum('paymoney');
        $city_month_paymoney = DB::table('ct_city_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_month)->sum('paymoney');
        $city_year_paymoney = DB::table('ct_city_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_year)->sum('paymoney');
        $city_paymoney = DB::table('ct_city_order')->where(array('pay_type' => 1, 'paystate' => 2))->sum('paymoney');
        // 整车订单金额
        $vechicle_day_paymoney = DB::table('ct_userorder')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_day)->sum('referprice');
        $vechicle_week_paymoney = DB::table('ct_userorder')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_week)->sum('referprice');
        $vechicle_month_paymoney = DB::table('ct_userorder')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_month)->sum('referprice');
        $vechicle_year_paymoney = DB::table('ct_userorder')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_year)->sum('referprice');
        $vechicle_paymoney = DB::table('ct_userorder')->where(array('pay_type' => 1, 'paystate' => 2))->sum('referprice');
        // 定制订单金额
        $custom_day_paymoney = DB::table('ct_shift_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_day)->sum('totalprice');
        $custom_week_paymoney = DB::table('ct_shift_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_week)->sum('totalprice');
        $custom_month_paymoney = DB::table('ct_shift_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_month)->sum('totalprice');
        $custom_year_paymoney = DB::table('ct_shift_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_year)->sum('totalprice');
        $custom_paymoney = DB::table('ct_shift_order')->where(array('pay_type' => 1, 'paystate' => 2))->sum('totalprice');
        // 零担订单金额
        $shift_day_paymoney = DB::table('ct_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_day)->sum('all_price');
        $shift_week_paymoney = DB::table('ct_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_week)->sum('all_price');
        $shift_month_paymoney = DB::table('ct_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_month)->sum('all_price');
        $shift_year_paymoney = DB::table('ct_order')->where(array('pay_type' => 1, 'paystate' => 2))->where($where_year)->sum('all_price');
        $shift_paymoney = DB::table('ct_order')->where(array('pay_type' => 1, 'paystate' => 2))->sum('all_price');

        $credit['day_payment'] = $city_day_paymoney+$vechicle_day_paymoney+$custom_day_paymoney+$shift_day_paymoney;
        $credit['week_payment'] = $city_week_paymoney+$vechicle_week_paymoney+$custom_week_paymoney+$shift_week_paymoney;
        $credit['month_payment'] = $city_month_paymoney+$vechicle_month_paymoney+$custom_month_paymoney+$shift_month_paymoney;
        $credit['year_payment'] = $city_year_paymoney+$vechicle_year_paymoney+$custom_year_paymoney+$shift_year_paymoney;
        $credit['all_payment'] = $city_paymoney+$vechicle_paymoney+$custom_paymoney+$shift_paymoney;
        $this->assign('credit',$credit);
        /*
        *
        *余额支付
        */
        // 市配订单金额
        $city_day_balance = DB::table('ct_city_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_day)->sum('paymoney');
        $city_week_balance = DB::table('ct_city_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_week)->sum('paymoney');
        $city_month_balance = DB::table('ct_city_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_month)->sum('paymoney');
        $city_year_balance = DB::table('ct_city_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_year)->sum('paymoney');
        $city_balance = DB::table('ct_city_order')->where(array('pay_type' => 2, 'paystate' => 2))->sum('paymoney');
        // 整车订单金额
        $vechicle_day_balance = DB::table('ct_userorder')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_day)->sum('referprice');
        $vechicle_week_balance = DB::table('ct_userorder')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_week)->sum('referprice');
        $vechicle_month_balance = DB::table('ct_userorder')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_month)->sum('referprice');
        $vechicle_year_balance = DB::table('ct_userorder')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_year)->sum('referprice');
        $vechicle_balance = DB::table('ct_userorder')->where(array('pay_type' => 2, 'paystate' => 2))->sum('referprice');
        // 定制订单金额
        $custom_day_balance = DB::table('ct_shift_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_day)->sum('totalprice');
        $custom_week_balance = DB::table('ct_shift_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_week)->sum('totalprice');
        $custom_month_balance = DB::table('ct_shift_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_month)->sum('totalprice');
        $custom_year_balance = DB::table('ct_shift_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_year)->sum('totalprice');
        $custom_paymoney = DB::table('ct_shift_order')->where(array('pay_type' => 2, 'paystate' => 2))->sum('totalprice');
        // 零担订单金额
        $shift_day_balance = DB::table('ct_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_day)->sum('all_price');
        $shift_week_balance = DB::table('ct_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_week)->sum('all_price');
        $shift_month_balance = DB::table('ct_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_month)->sum('all_price');
        $shift_year_balance = DB::table('ct_order')->where(array('pay_type' => 2, 'paystate' => 2))->where($where_year)->sum('all_price');
        $shift_balance = DB::table('ct_order')->where(array('pay_type' => 2, 'paystate' => 2))->sum('all_price');

        $balance['day_payment'] = $city_day_balance+$vechicle_day_balance+$custom_day_balance+$shift_day_balance;
        $balance['week_payment'] = $city_week_balance+$vechicle_week_balance+$custom_week_balance+$shift_week_balance;
        $balance['month_payment'] = $city_month_balance+$vechicle_month_balance+$custom_month_balance+$shift_month_balance;
        $balance['year_payment'] = $city_year_balance+$vechicle_year_balance+$custom_year_balance+$shift_year_balance;
        $balance['all_payment'] = $city_balance+$vechicle_balance+$custom_paymoney+$shift_balance;
        $this->assign('balance',$balance);
        $tatol_pay['total_day'] = $alipay_today_order + $wechat_today_order +$credit['day_payment']+$balance['day_payment'];
        $tatol_pay['total_week'] = $alipay_toweek_order + $wechat_toweek_order +$credit['week_payment']+$balance['week_payment'];
        $tatol_pay['total_month'] = $alipay_tomoth_order + $wechat_tomoth_order +$credit['month_payment']+$balance['month_payment'];
        $tatol_pay['total_year'] = $alipay_toyear_order + $wechat_toyear_order +$credit['year_payment']+$balance['year_payment'];
        $tatol_pay['total_all'] = $alipay_sumall_order + $wechat_sumall_order +$credit['all_payment']+$balance['all_payment'];
        $this->assign('tatol_pay',$tatol_pay);
        // $this->assign('credit_month_payment',$credit_month_payment);
        // $this->assign('credit_year_payment',$credit_year_payment);
        // $this->assign('credit_payment',$credit_payment);



        //20180423
        //支付宝充值
        $pay_recharge = DB::table('ct_paymessage')->where(array('paytype'=>1,'state'=>'2'))->sum('paynum');
        $this->assign('pay_recharge',$pay_recharge);
        //微信充值
        $wei_recharge = DB::table('ct_paymessage')->where(array('paytype'=>2,'state'=>'2'))->sum('paynum');
        $this->assign('wei_recharge',$wei_recharge);
        //用户充值
        $client_recharge = DB::table('ct_paymessage')->where(array('type'=>1,'state'=>'2'))->sum('paynum');
        $this->assign('client_recharge',$client_recharge);
        //司机充值
        $driver_recharge = DB::table('ct_paymessage')->where(array('type'=>2,'state'=>'2'))->sum('paynum');
        $this->assign('driver_recharge',$driver_recharge);
        //充值总额
        $sum_recharge = DB::table('ct_paymessage')->sum('paynum');
        $this->assign('sum_recharge',$sum_recharge);

        //提现待审核
        $replay_count = DB::table('ct_application')->where('states',1)->sum('money');
        $this->assign('replay_count',$replay_count);
        //提现审核通过
        $replay_pass = DB::table('ct_application')->where('states',2)->sum('money');
        $this->assign('replay_pass',$replay_pass);
        //提现已打款
        $replay_pass_success = DB::table('ct_application')->where('states',4)->sum('money');
        $this->assign('replay_pass_success',$replay_pass_success);
        //提现总额
        $replay_sum = DB::table('ct_application')->where('states',4)->sum('money');
        $this->assign('replay_sum',$replay_sum);

        //当天注册用户量
        $where_register['delstate'] =1;
        //用户昨天注册总数
        $day_user = DB::table('ct_user')->where($where_day)->where($where_register)->count('uid');
        //用户当周注册总数
        $week_user = DB::table('ct_user')->where($where_week)->where($where_register)->count('uid');
        //用户当月注册总数
        $month_user = DB::table('ct_user')->where($where_month)->where($where_register)->count('uid');
        //用户当月注册总数
        $year_user = DB::table('ct_user')->where($where_year)->where($where_register)->count('uid');
        //用户总数
        $count_user =  DB::table('ct_user')->where($where_register)->count('uid');

        $tatal_user['day_user'] = $day_user;
        $tatal_user['week_user'] = $week_user;
        $tatal_user['month_user'] = $month_user;
        $tatal_user['year_user'] = $year_user;
        $tatal_user['all_user'] = $count_user;
        $this->assign('user',$tatal_user);


        //司机昨天注册总数
        $day_driver = DB::table('ct_driver')->where($where_day)->where($where_register)->count('drivid');
        //司机当周注册总数
        $week_driver = DB::table('ct_driver')->where($where_week)->where($where_register)->count('drivid');
        //司机当月注册总数
        $month_driver = DB::table('ct_driver')->where($where_month)->where($where_register)->count('drivid');
        //司机当月注册总数
        $year_driver = DB::table('ct_driver')->where($where_year)->where($where_register)->count('drivid');
        //司机总数
        $count_driver =  DB::table('ct_driver')->where($where_register)->count('drivid');
        $tatal_driver['day_driver'] = $day_driver;
        $tatal_driver['week_driver'] = $week_driver;
        $tatal_driver['month_driver'] = $month_driver;
        $tatal_driver['year_driver'] = $year_driver;
        $tatal_driver['all_driver'] = $count_driver;
        $this->assign('driver',$tatal_driver);

        //零担进行在线订单总数


        $proceed_order = DB::table('ct_order')->where(array('orderstate'=>array('not in','7,8'),'paystate'=>2))->count('oid');
        $this->assign('proceed_order',$proceed_order);
        //零担已完成订单总数
        $finish_order = DB::table('ct_order')->where(array('orderstate'=>7,'paystate'=>2))->count('oid');
        $this->assign('finish_order',$finish_order);
        //零担无效订单总数
        $Invalid_order = DB::table('ct_order')->where(array('paystate'=>1))->count('oid');
        $this->assign('Invalid_order',$Invalid_order);
        //零担总订单总数
        $count_order = DB::table('ct_order')->count('oid');
        $this->assign('count_order',$count_order);



        //货主跨区未接整车订单
        $uncity_user_order = DB::table('ct_userorder')->where(array('orderstate'=>1,'paystate'=>2))->count('uoid');
        $this->assign('uncity_user_order',$uncity_user_order);
        //货主跨区已完成整车订单
        $city_user_order = DB::table('ct_userorder')->where(array('orderstate'=>3))->count('uoid');
        $this->assign('city_user_order',$city_user_order);
        //货主跨区未支付整车订单
        $city_user_cancelorder = DB::table('ct_userorder')->where(array('paystate'=>1))->count('uoid');
        $this->assign('city_user_cancelorder',$city_user_cancelorder);
        //货主跨区整车总订单
        $city_user_countorder = DB::table('ct_userorder')->count('uoid');
        $this->assign('city_user_countorder',$city_user_countorder);

        //车主市内未接订单
        $uncity_driver_order = DB::table('ct_city_order')->where(array('paystate'=>2,'state'=>1))->count('id');
        $this->assign('uncity_driver_order',$uncity_driver_order);
        //车主市内已完成订单
        $city_driver_order = DB::table('ct_city_order')->where(array('paystate'=>2,'state'=>3))->count('id');
        $this->assign('city_driver_order',$city_driver_order);
        //车主市内未支付订单
        $city_driver_cancelorder = DB::table('ct_city_order')->where(array('paystate'=>1))->count('id');
        $this->assign('city_driver_cancelorder',$city_driver_cancelorder);
        //车主市内总订单
        $city_driver_countorder = DB::table('ct_city_order')->count('id');
        $this->assign('city_driver_countorder',$city_driver_countorder);


        //待解决反馈信息
        $unfeed_message = DB::table('ct_feedback')->where('type',1)->count('id');
        $this->assign('unfeed_message',$unfeed_message);
        //已解决反馈信息
        $feed_message = DB::table('ct_feedback')->where('type',2)->count('id');
        $this->assign('feed_message',$feed_message);
        //统计提现总数
        $sum_applay = DB::table('ct_application')->where(array('states'=>4))->sum('money');
        $this->assign('sum_applay',$sum_applay);
        //统计今年提现总数
        $sum_applay_year = DB::query("SELECT sum(money) as count FROM `ct_application` where states=4  AND DATE_FORMAT(FROM_UNIXTIME(start_time),'%Y') = DATE_FORMAT(FROM_UNIXTIME(".time()."),'%Y')");
        $this->assign('sum_applay_year',$sum_applay_year[0]['count']);
        //统计充值总数
        $sum_pay = DB::table('ct_paymessage')->sum('paynum');
        $this->assign('sum_pay',$sum_pay);
        //统计今年提现总数
        $sum_pay_year = DB::query("SELECT sum(paynum) as count FROM `ct_paymessage` where  DATE_FORMAT(FROM_UNIXTIME(paytime),'%Y') = DATE_FORMAT(FROM_UNIXTIME(".time()."),'%Y')");
        //var_dump($sum_applay_year);
        $this->assign('sum_pay_year',$sum_pay_year[0]['count']);
        $t = time();
        $start_time = mktime(0,0,0,date("m",$t),date("d",$t),date("Y",$t));  //当天开始时间
        $end_time = mktime(23,59,59,date("m",$t),date("d",$t),date("Y",$t)); //当天结束时间
        $count = DB::table('ct_device')->where('model','neq','')->count('id');

        return $this->fetch();
    }





}
