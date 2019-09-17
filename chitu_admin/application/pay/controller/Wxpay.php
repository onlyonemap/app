<?php
namespace app\pay\controller;
use  think\Controller;   //使用控制器
use  think\Db;  //使用数据库操作
use  think\Request; 
use  think\Loader; //加载模型

class Wxpay
{   
    /**
     * @Auther: 李渊
     * @Date： 2018.6.20
     * 赤途承运端-整车-扫码支付-获取二维码 模式二 （ 可正常使用 ）
     * 流程：
     * 1、调用统一下单，取得code_url，生成二维码
     * 2、用户扫描二维码，进行支付
     * 3、支付完成之后，微信服务器会通知支付成功
     * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
     * @param  [type] $body         [商品描述]
     * @param  [type] $attach       [商品附加数据]
     * @param  [type] $out_trade_no [商品订单号]
     * @param  [type] $Total_fee    [订单总金额，单位为分]
     * @return [type] $url          [二维码地址]
     */
    public function setcard($body,$attach,$out_trade_no,$Total_fee) {
        Loader::import('wxpay.NativePay', EXTEND_PATH);
        $notify = new \NativePay();
        $input = new \WxPayUnifiedOrder();

        // 设置商品描述
        $input->SetBody($body);
        // 设置商品附加数据
        $input->SetAttach($attach);
        // 设置商品订单号
        $input->SetOut_trade_no($out_trade_no);
        // 设置订单总金额，单位为分
        $input->SetTotal_fee($Total_fee);
        // 设置交易起始时间
        $input->SetTime_start(date("YmdHis"));
        // 设置交易结束时间
        $input->SetTime_expire(date("YmdHis", time() + 600));
        // 订单优惠标记
        $input->SetGoods_tag('test');
        // 支付成功回调地址
        $input->SetNotify_url("https://app.56cold.com/pay/wxpay/notify_url");
        // 交易类型 JSAPI 公众号支付 NATIVE 扫码支付 APP APP支付
        $input->SetTrade_type("NATIVE");
        // 设置商品id
        $input->SetProduct_id($attach);
        // 生成二维码
        $result = $notify->GetPayUrl($input);
        // 判断是否生成成功
        if(!empty($result['result_code']) && $result['result_code'] == 'SUCCESS'){
            // 生成二维码地址
            $url = get_url().'/admin/qrcode/setcard?data='.$result["code_url"];
        }else{
            $url = $result;
        }
        // 输出二维码地址
        return $url;
    }

    // 公众号支付
    public function jspay()
    {
        $params = [
            'body' => '支付测试',
            'out_trade_no' => mt_rand().time(),
            'total_fee' => 1,
        ];
        $result = \wxpay\JsapiPay::getPayParams($params);
        halt($result);
    }

    // 小程序支付
    public function smallapp()
    {
        $params = [
            'body'         => '支付测试',
            'out_trade_no' => mt_rand().time(),
            'total_fee'    => 1,
        ];
        $code = '08123gA41K4EQO1RH1B41uP2A4123gAW';
        $result = \wxpay\JsapiPay::getPayParams($params, $code);
        $openId = 'oCtoK0SjxW-N5qjEDgaMyummJyig';
        $result = \wxpay\JsapiPay::getParams($params, $openId);
    }

    // 刷卡支付
    public function micropay()
    {
        $params = [
            'body' => '支付测试',
            'out_trade_no' => mt_rand().time(),
            'total_fee' => 1,
        ];
        $auth_code = '134628839776154108';
        $result = \wxpay\MicroPay::pay($params, $auth_code);
        halt($result);
    }

    // H5支付
    public function wappay()
    {
        $params = [
            'body' => '支付测试',
            'out_trade_no' => mt_rand().time(),
            'total_fee' => 1,
        ];
        $result = \wxpay\WapPay::getPayUrl($params);
        halt($result);
    }
    
    /**
     * @Auther: 李渊
     * @Date: 2018.6.21
     * 微信支付订单查询
     * 根据订单号向微信服务器查询订单支付状态
     * @param  [type] $ordernumber   [商户订单号]
     * @return [type] $code    [状态码]
     * @return [type] $message [描述信息]
     */
    public function query() 
    {
        require_once EXTEND_PATH."wxpay/lib/WxPay.Api.php";
        // 获取商户订单号
        $out_trade_no = input("ordernumber");
        $input = new \WxPayOrderQuery();
        $input->SetOut_trade_no($out_trade_no);
        $WxPayApi = new \WxPayApi();
        $result = $WxPayApi->orderQuery($input);
        // 判断交易状态
        if (!isset($result['trade_state'])) {
            return json(['code'=>'1000','message'=>'订单不存在']);
        }
        switch ($result['trade_state']) {
            case 'SUCCESS': // 支付成功
                return json(['code'=>'1001','message'=>'支付成功']);
                break;
            case 'REFUND': // 转入退款
                return json(['code'=>'1002','message'=>'转入退款']);
                break;
            case 'NOTPAY': // 未支付
                return json(['code'=>'1003','message'=>'未支付']);
                break;
            case 'CLOSED': // 已关闭
                return json(['code'=>'1004','message'=>'已关闭']);
                break;
            case 'REVOKED': // 已撤销
                return json(['code'=>'1005','message'=>'已撤销']);
                break;
            case 'USERPAYING': // 用户支付中
                return json(['code'=>'1006','message'=>'用户支付中']);
                break;
            case 'PAYERROR': // 支付失败
                return json(['code'=>'1007','message'=>'支付失败']);
                break;
            default:
                # code...
                break;
        }
    }
   
    /**
     * @Auther: 李渊
     * @Date: 2018.7.23
     * 微信退款
     * 根据订单为完成交易发生退款 
     * 根据商户订单号发生退款
     * @param  [String] $out_trade_no   [商户订单号]
     * @param  [String] $total_fee      [订单金额]
     * @param  [String] $refund_fee     [退款金额]
     * @return [type] [description]
     */
    public function refund() 
    {
        ini_set('date.timezone','Asia/Shanghai');
        require_once EXTEND_PATH."wxpay/lib/WxPay.Api.php";
        
        // 判断是否有商户订单号如果有则用商户订单号发起退款申请
        if(isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != ""){
            $out_trade_no = input("out_trade_no");
            $total_fee = input("total_fee");
            $refund_fee = input("refund_fee");
            $input = new \WxPayRefund();
            $WxPayApi = new \WxPayApi();
            $WxPayConfig = new \WxPayConfig();
            $input->SetOut_trade_no($out_trade_no);
            $input->SetTotal_fee($total_fee);
            $input->SetRefund_fee($refund_fee);
            $input->SetOut_refund_no($WxPayConfig::MCHID.date("YmdHis"));
            $input->SetOp_user_id($WxPayConfig::MCHID);
            $input->SetRefund_account('REFUND_SOURCE_RECHARGE_FUNDS');
            $data = $WxPayApi->refund($input);
            print('<pre/>');
            print_r($data);
            exit();
        }
    }

    /**
     * @Auther: 李渊
     * @Date: 2018.7.23
     * 微信查询
     * 微信订单退款查询
     * @param  [String] $out_trade_no   [商户订单号]
     * @return [type] [description]
     */
    public function refundquery($out_trade_no) 
    {
        ini_set('date.timezone','Asia/Shanghai');
        require_once EXTEND_PATH."wxpay/lib/WxPay.Api.php";

        if(isset($_REQUEST["out_trade_no"]) && $_REQUEST["out_trade_no"] != ""){
            $out_trade_no = input("out_trade_no");
            $input = new \WxPayRefundQuery();
            $WxPayApi = new \WxPayApi();
            $input->SetOut_trade_no($out_trade_no);
            $data = $WxPayApi::refundQuery($input);
            print('<pre/>');
            print_r($data);

            exit();
        }
    }

    // 下载对账单
    public function download()
    {
        $result = \wxpay\DownloadBill::exec('20170923');
        echo($result);
    }

    /**
     * @Auther: 李渊
     * @Date: 2018.6.21
     * 支付回调
     * 用户扫描二维码付款后微信服务器向商户服务器通知支付结果
     * @return [type] [description]
     */
    public function notify_url()
    {
        // 获取微信支付回调数据
        $result = file_get_contents('php://input', 'r');
        libxml_disable_entity_loader(true);
        $data = json_decode(json_encode(simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        // 判断支付状态是否成功
        if ($data['return_code'] == 'SUCCESS') { // 支付成功
            $this->callbackWxpay($data);
            echo 'success';
        }else{
            echo 'fail';
        }
    }

    /**
     * @Auther: 李渊
     * @Date: 2018.6.21
     * 前台请求改地址生成二维码
     * @param  [type] $id   [商户订单号]
     * @param  [type] $type [订单类型] 1 城配 2 整车
     * @return [type] [description]
     */
    public function getcard()
    {
        // 获取订单id
        $id = input("id");
        // 获取订单类型 1 城配 2 整车
        $type = input("type");
        // 验证信息
        if(empty($id) || empty($type)){
            return json(['code'=>'1000','message'=>'参数错误']);
        }
        // 订单编号
        $out_trade_no = 0;  
        // 订单金额
        $Total_fee = 0;
        // 商品描述
        $body = '赤途冷链订单运费';
        // 商品附加数据
        $attach = '赤途冷链订单运费';
        // 根据不同的类型查找数据
        switch ($type) {
            case '1': // 城配
                break;
            case '2': // 整车
                $result = Db::table('ct_userorder')->where('uoid',$id)->find();
                if($result['paystate'] == '2'){
                     return json(['code'=>'1003','message'=>'已支付','url'=>'']);
                }
                $out_trade_no = $result['ordernumber'];
                // 订单运费 没有折扣价显示订单运费 
                $price = $result['user_discount'] =='' ? $result['actual_payment'] : $result['user_discount'];
                // 订单运费 没有支付显示订单运费否则显示支付运费
                $price = $result['referprice'] =='' ? $price : $result['referprice'];
                // 订单运费 有修改过运费则显示修改过用费
                $price = $result['upprice'] =='' ? $price : $result['upprice'];
                // 微信扫码支付单位为分要乘以100
                $Total_fee = $price*100;
                $Total_fee = round($Total_fee);
                // 商品描述
                $body = '赤途冷链整车订单运费';
                // 商品附加数据
                $attach = $type;
                break;
            default:
                break;
        }
        // 获取二维码链接
        $url = $this->setcard($body,$attach,$out_trade_no,$Total_fee);
        // 判断是否生成成功
        if($url){
            // 输出
            return json(['code'=>'1001','message'=>'生成成功','url'=>$url]);
        }else{
            // 输出
            return json(['code'=>'1002','message'=>'生成失败','url'=>$url]);
        }
    }

    /**
     * @Auther: 李渊
     * @Date: 2018.6.21
     * 微信扫码支付成功回调
     * @param  string $data  [支付成功微信服务器返回的参数]
     * @return [type]        [description]
     */
    public function callbackWxpay($data)
    {
        // 判断订单类型 1 城配 2整车
        switch ($data['attach']) {
            case '1': // 城配扫码支付
                # code...
                break;
            case '2': // 整车扫码支付
                $update['ordernumber'] = $data['out_trade_no'];
                $update['orderid'] = $data['out_trade_no']; // 商户订单号
                $update['paytype'] = 2;  // 支付类型微信
                $update['platformorderid'] = $data['transaction_id']; // 微信支付订单号
                $update['paynum'] = $data['total_fee']/100; //支付费用
                $update['paytime'] = time(); // 支付时间
                $update['payname'] = $data['openid']; // 用户标识
                $update['type'] = 3;  // 扫码支付
                $update['state'] = 1; // 支付
                // 查询数据
                $user_data = Db::field('a.*,b.startcity,b.endcity,b.uoid')
                            ->table('ct_user')
                            ->alias('a')
                            ->join('ct_userorder b','b.userid=a.uid')
                            ->where('b.ordernumber',$data['out_trade_no'])
                            ->find();
                // 用户id
                $update['userid'] = $user_data['uid'];
                // 更新订单支付状态
                Db::table('ct_userorder')->where('ordernumber',$data['out_trade_no'])->update(array('paystate'=>'2','referprice'=>$update['paynum'],'pay_type'=>'4'));
                // 插入支付记录
                Db::table("ct_paymessage")->insert($update);
                break;
            default:
                # code...
                break;
        }
    }
}
