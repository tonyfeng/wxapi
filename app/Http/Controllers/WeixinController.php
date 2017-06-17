<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use EasyWeChat\Support\XML;
use App\Models\WeixinApiModel;
use App\Models\PayorderModel;
use App\Models\QrcodeModel;
use App\Models\WxUserModel;
use App\Libs\Common;
use Symfony\Component\HttpFoundation\Response;

class WeixinController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {


    }

    //页面调用支付（自定义金额）
    public function wxpagepay(Request $request){

        $getParamArray = $request->query->all();
        $textJson = file_get_contents('php://input');
        $postParams = json_decode($textJson, true);
        if(!$postParams){
            Common::outputJson(0,20009);
        }
        $paramArray = array_merge($getParamArray,$postParams);

        //验证参数
        $srcParams = array("appid","version","time","sign","mid","total_fee","body","ip");
        if(!Common::validationParams($paramArray, $srcParams)){
            Common::outputJson(0,20010);
        }

        //验证签名
        $appkey = $this->getAppKey($getParamArray["appid"]);
        if(!Common::validationSign($appkey,$paramArray, $getParamArray["sign"])){
            Common::outputJson(0,20006);
        }

        //初始化微信调用模块
        $ret = WeixinApiModel::initialize($paramArray['mid']);
        if(!$ret){
            Common::writeLogs('error',20016,"WeixinApiModel::initialize(".$paramArray['mid'].")");
            Common::outputJson(0,20016);
        }

        //业务处理
        unset($paramArray['sign']);
        $paramArray['sign'] = md5(json_encode(Common::paramSort($paramArray)).config('app.grcode_key_encrypt'));
        $encode = base64_encode(json_encode($paramArray));
        $redirectUrl = url("v1/wxpaycall")."/".$encode;
        $response = WeixinApiModel::getModelOauth()->scopes(array("snsapi_base"))
            ->setRedirectUrl($redirectUrl)
            ->redirect();
        $url = $response->getTargetUrl();
        $outHtml = "<!DOCTYPE html>
        <html>
            <head>
                <meta charset=\"UTF-8\" />
                <meta http-equiv=\"refresh\" content=\"1;url=".$url."\" />
                <title>微信支付跳转...</title>
            </head>
            <body>
            </body>
        </html>";
        //Common::outputJson(1,0, htmlspecialchars($outHtml));
        echo $outHtml;
        exit();
    }

    public function wxpaycall(Request $request,$encodestr){


        //验证参数签名
        $paramArray = json_decode(base64_decode($encodestr),true);

        $paramSign = $paramArray['sign'];
        unset($paramArray['sign']);
        $sign = md5(json_encode(Common::paramSort($paramArray)).config("app.grcode_key_encrypt"));
        if($paramSign != $sign){
            die("签名错误，不合法的请求！");
        }
        $ret = WeixinApiModel::initialize($paramArray['mid']);
        if(!$ret){
            Common::writeLogs('error',20016,"WeixinApiModel::initialize(".$paramArray['mid'].")");
            Common::outputJson(0,20016);
        }
        $userInfo = WeixinApiModel::getModelOauth()->user()->toArray();

        //业务处理
        if(empty($paramArray["out_trade_no"])){
            $out_trade_no = Common::getOrderNo();
        }else{
            $out_trade_no = $paramArray["out_trade_no"];  //C传送
        }
        $postParams["out_trade_no"] = $out_trade_no;
        $postParams["body"] = $paramArray["body"];
        $postParams["total_fee"] = (float)$paramArray["total_fee"] * 100;
        $postParams["trade_type"] = "JSAPI";
        $postParams["ip"] = $paramArray["ip"];;
        $postParams["openid"] = $userInfo["id"];
        $postParams["attach"] = json_encode(array("mid"=>$paramArray['mid']));
        $postParams["notify_url"] = url("v1/wxnotify");//config("app.wxnotify_url");
        $result = WeixinApiModel::createOrder($postParams);
        if($result["return_code"] == "FAIL" || $result["result_code"] == "FAIL"){
            Common::writeLogs('error',20012,\GuzzleHttp\json_encode($result));
            Common::outputJson(0,20012);
        }

        //写入数所库
        $time = date('Y-m-d H:i:s');
        $attributes = array(
            "mid"=>$paramArray["mid"],
            "wx_mch_id"=>$ret["wx_mch_id"],
            "wx_appid"=>$ret["wx_appid"],
            "body"=>$paramArray["body"],
            "out_trade_no"=>$out_trade_no,
            "total_fee"=>$postParams["total_fee"],
            "spbill_create_ip"=>$paramArray["ip"],
            "time_start"=>$time,
            "trade_type"=>$postParams["trade_type"],
        );
        $orderInfo = PayorderModel::queryByOrderNo($out_trade_no);
        if (isset($orderInfo[0])){
            $affectedRows = PayorderModel::where('out_trade_no', '=', $out_trade_no)->update($attributes);
            if(!$affectedRows) {
                Common::writeLogs('error',20016,"update table payorder".\GuzzleHttp\json_encode($attributes));
                return false;
            }
        }else{
            $res = PayorderModel::Create($attributes);
            if(!$res){
                Common::writeLogs('error',20016,"insert table payorder".\GuzzleHttp\json_encode($attributes));
                Common::outputJson(0,20016);
            }
        }

        //支付完成同步跳转页面
        if(empty($paramArray["jump_url"])){
            $jump_url = url("v1/paymsg");
        }else{
            $jump_url = $paramArray["jump_url"];  //C传送
        }

        //生成支付 JS配置
        $payConfig = WeixinApiModel::getModelPayment()->configForJSSDKPayment($result['prepay_id']);
        $config = WeixinApiModel::getModelJs()->signature();
        $outHtml = "<!DOCTYPE html>
        <html>
            <head>
                <meta charset=\"UTF-8\" />
                <script type=\"text/javascript\" charset=\"UTF-8\" src=\"http://res.wx.qq.com/open/js/jweixin-1.0.0.js\"></script>
                <title>微信支付跳转...</title>
                <script language=\"javascript\" type=\"text/javascript\">
                
                window.onload = function() {
                    document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
                        // 通过下面这个API隐藏右上角按钮
                        WeixinJSBridge.call('hideOptionMenu');
                    });
                }
                
    
                //初始化配置
                wx.config({
                    debug: false, 
                    appId: '".$config['appId']."', 
                    timestamp: ".$config['timestamp'].", 
                    nonceStr: '".$config['nonceStr']."', 
                    signature: '".$config['signature']."',
                    jsApiList: ['chooseWXPay'] 
                });
                wx.ready(function(){
                    //调用微信支付
                    wx.chooseWXPay({
                        timestamp: ".$payConfig['timestamp'].", 
                        nonceStr: '".$payConfig['nonceStr']."', 
                        package: '".$payConfig['package']."', 
                        signType: '".$payConfig['signType']."', 
                        paySign: '".$payConfig['paySign']."', 
                        success: function (res) {
                            //alert((res.err_msg);
                            if (res.err_msg == 'ok') {
                                var status = 1;
                            }else{
                                var status = 0;
                            }
                            location.href = '".$jump_url."?orderno=".$out_trade_no."&status=' + status;
                        }
                    });
                });
                </script>
            </head>
            <body>
            </body>
        </html>";

        echo $outHtml;
        exit();
    }

    /**
     * jsapi支付发起请求
     */
    public function jsapi(Request $request){

        $getParamArray = $request->query->all();
        $textJson = file_get_contents('php://input');
        $postParams = json_decode($textJson, true);
        if(!$postParams){
            Common::outputJson(0,20009);
        }
        $paramArray = array_merge($getParamArray,$postParams);

        //验证参数

        $srcParams = array("appid","version","time","sign","mid","total_fee","body","ip");
        if(!Common::validationParams($paramArray, $srcParams)){
            Common::outputJson(0,20010);
        }

        //验证签名
        $appkey = $this->getAppKey($getParamArray["appid"]);
        if(!Common::validationSign($appkey,$paramArray, $getParamArray["sign"])){
            Common::outputJson(0,20006);
        }

        //初始化微信调用模块
        $ret = WeixinApiModel::initialize($paramArray['mid']);
        if(!$ret){
            Common::writeLogs('error',20016,"WeixinApiModel::initialize(".$paramArray['mid'].")");
            Common::outputJson(0,20016);
        }

        //业务处理
        $out_trade_no = Common::getOrderNo();
        $postParams["out_trade_no"] = $out_trade_no;
        $postParams["body"] = $paramArray["body"];
        $postParams["trade_type"] = "JSAPI";
        $postParams["total_fee"] = (float)$paramArray["total_fee"] * 100;
        $postParams["openid"] = $paramArray["openid"];
        $postParams["attach"] = json_encode(array("mid"=>$paramArray['mid']));
        $postParams["notify_url"] = url("v1/wxnotify");//config("app.wxnotify_url");
        $result = WeixinApiModel::createOrder($postParams);
        if($result["return_code"] == "FAIL" || $result["result_code"] == "FAIL"){
            Common::writeLogs('error',20012,\GuzzleHttp\json_encode($result));
            Common::outputJson(0,20012);
        }

        //写入数所库
        $time = date('Y-m-d H:i:s');
        $attributes = array(
            "mid"=>$paramArray["mid"],
            "wx_mch_id"=>$ret["wx_mch_id"],
            "wx_appid"=>$ret["wx_appid"],
            "body"=>$paramArray["body"],
            "out_trade_no"=>$out_trade_no,
            "total_fee"=>$postParams["total_fee"],
            "spbill_create_ip"=>$paramArray["ip"],
            "time_start"=>$time,
            "trade_type"=>$postParams["trade_type"],
        );
        $res = PayorderModel::Create($attributes);
        if(!$res){
            Common::writeLogs('error',20016,"insert table payorder".\GuzzleHttp\json_encode($attributes));
            Common::outputJson(0,20016);
        }

        //生成支付 JS配置
        $pay_json = WeixinApiModel::getModelPayment()->configForPayment($result['prepay_id']);
        $data = array("out_order_no"=>$out_trade_no,'total_fee'=>$postParams["total_fee"],'pay_json'=>$pay_json);
        Common::writeLogs('info',0,\GuzzleHttp\json_encode($paramArray));
        Common::outputJson(1, 0, $data);
    }

    /**
     * native支付发起请求wxnative
     */
    public function wxnative(Request $request){

        $getParamArray = $request->query->all();
        $textJson = file_get_contents('php://input');
        $postParams = json_decode($textJson, true);
        if(!$postParams){
            Common::outputJson(0,20009);
        }
        $paramArray = array_merge($getParamArray,$postParams);
        
        //验证参数
        $srcParams = array("appid","version","time","sign","token","mid","total_fee","body","ip");
        if(!Common::validationParams($paramArray, $srcParams)){ 
            Common::outputJson(0,20010);
        }

        //验证签名
        $appkey = $this->getAppKey($getParamArray["appid"]);
        if(!Common::validationSign($appkey,$paramArray, $getParamArray["sign"])){
            Common::outputJson(0,20006);
        }

        //是否登陆
        // if(!$this->VerificationToken($paramArray)){
        //     Common::outputJson(0,20011);
        // }

        //初始化微信调用模块
        $ret = WeixinApiModel::initialize($paramArray['mid']);
        if(!$ret){
            Common::writeLogs('error',20016,"WeixinApiModel::initialize(".$paramArray['mid'].")");
            Common::outputJson(0,20016);
        }

        //业务处理
        $out_trade_no = Common::getOrderNo();
        $postParams["out_trade_no"] = $out_trade_no;
        $postParams["body"] = $paramArray["body"];
        $postParams["total_fee"] = (float)$paramArray["total_fee"] * 100;
        $postParams["trade_type"] = "NATIVE";
        $postParams["attach"] = json_encode(array("mid"=>$paramArray['mid']));
        $postParams["notify_url"] = url("v1/wxnotify");//config("app.wxnotify_url");
        if(!empty($paramArray["product_id"])){
            $postParams["product_id"] = $paramArray["product_id"];
        }
        $result = WeixinApiModel::createOrder($postParams);
        if($result["return_code"] == "FAIL" || $result["result_code"] == "FAIL"){
            Common::writeLogs('error',20012,\GuzzleHttp\json_encode($result));
            Common::outputJson(0,20012);
        }

        //写入数所库
        $time = date('Y-m-d H:i:s');
        $attributes = array(
            "mid"=>$paramArray["mid"],
            "wx_mch_id"=>$ret["wx_mch_id"],
            "wx_appid"=>$ret["wx_appid"],
            "body"=>$paramArray["body"],
            "out_trade_no"=>$out_trade_no,
            "total_fee"=>$postParams["total_fee"],
            "spbill_create_ip"=>$paramArray["ip"],
            "time_start"=>$time,
            "trade_type"=>$postParams["trade_type"],
        );
        $res = PayorderModel::Create($attributes);
        if(!$res){
            Common::writeLogs('error',20016,"insert table payorder".\GuzzleHttp\json_encode($attributes));
            Common::outputJson(0,20016);
        }

        //动态金额二维码
        $qrocdeParam = array("url"=>base64_encode($result['code_url']),"size" => 300);
        $qrocdeParam['sign'] = md5(json_encode(Common::paramSort($qrocdeParam)).config('app.grcode_key_encrypt'));
        $qrcode_url = config("app.image_url") ."/". base64_encode(json_encode($qrocdeParam));

        $data = array("out_order_no"=>$out_trade_no,'total_fee'=>$postParams["total_fee"],'qrcode_url'=>$qrcode_url);
        Common::writeLogs('info',0,\GuzzleHttp\json_encode($paramArray));
        Common::outputJson(1, 0, $data);
    }
    
    public function notify(Request $request){

        //接收回调信息
        $post = \GuzzleHttp\json_encode($_POST);
        $body = file_get_contents('php://input');
        Common::writeLogs('info',0,"post:".$post."||body:".$body);

        //初始化微信调用模块
        $returnData = XML::parse($body);
        $attachArray = \GuzzleHttp\json_decode($returnData['attach'],true);
        $ret = WeixinApiModel::initialize($attachArray['mid']);
        if(!$ret){
            Common::writeLogs('error',20016,"WeixinApiModel::initialize(" . $attachArray['mid'] .")");
            return false;
        }

        $response = WeixinApiModel::getModelPayment()->handleNotify(function($notify, $successful) use($attachArray){

            $result = PayorderModel::queryByOrderNo($notify->out_trade_no);

            if (!isset($result[0])) { // 如果订单不存在
                Common::writeLogs('error',20017,"PayorderModel::queryByTransactionId(".$notify->transaction_id.")");
                return false;
            }
            $orderInfo = $result[0]["attributes"];
            $return_count = $orderInfo["return_count"] + 1;
            $updateData = array('result_code'=>$notify->result_code,'return_code'=>$notify->return_code,'return_count'=>$return_count);

            // 如果订单存在,检查订单是否已经更新过支付状态
            if ($orderInfo["result_code"] == "SUCCESS") {
                Common::writeLogs('info',20019,"$orderInfo[result_code] = " .$orderInfo["result_code"]);
                return true; // 已经支付成功了就不再更新了
            }

            //通信标识，非交易标识
            if($notify->return_code == "FAIL"){
                unset($updateData['result_code']);
                $affectedRows = PayorderModel::where('out_trade_no', '=', $notify->out_trade_no)->update($updateData);
                Common::writeLogs('error',20018,"$notify->return_code = " .$notify->return_code);
                return false;
            }

            // 用户是支付不成功
            if (!$successful) {
                $affectedRows = PayorderModel::where('out_trade_no', '=', $notify->out_trade_no)->update($updateData);
                Common::writeLogs('info',20020,"$successful=".$successful);
                return false;
            }

            //将成功支付订单写入表
            $end_time = preg_replace('/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/', '$1-$2-$3 $4:$5:$6', $notify->time_end);
            $total_fee = (float)$notify->total_fee;
            $data = array(
                "status"=>1,
                "transaction_id" => $notify->transaction_id,
                "callback_total_fee" => $notify->total_fee,
                "poundage_total_fee" => $total_fee * 0.006,
                "settlement_total_fee" => $total_fee - $total_fee * 0.006,
                "trade_type"=>$notify->trade_type,
                "bank_type" => $notify->bank_type,
                "return_count" => $return_count,
                "result_code" => $notify->result_code,
                "return_code" => $notify->return_code,
                "fee_type" => $notify->fee_type,
                "is_subscribe" => $notify->is_subscribe,
                "openid" => $notify->openid,
                "time_end" => $end_time, //20140903131540
                "attach" => $notify->attach

            );

            //更新支付订单信息
            $affectedRows = PayorderModel::where('out_trade_no', '=', $notify->out_trade_no)->update($data);
            if(!$affectedRows) {
                Common::writeLogs('error',20016,"update table payorder".\GuzzleHttp\json_encode($data));
                return false;
            }

            //收款通知
            $templateId = "DW5L5YOUqSldACzd22MSyY6fnrcF_9Ir-qPEs56WBB4";
            $url = "http://m.yftechnet.com/order/show/".$notify->out_trade_no;
            $data = array(
                "first"    => array("收款成功通知！", '#555555'),
                "keyword1" => array($orderInfo['body'], "#336699"),
                "keyword2" => array(round($total_fee / 100,2) . '元', "#FF0000"),
                "keyword3" => array($end_time, "#888888"),
                "remark"   => array("点击查看明细", "#5599FF"),
            );
            $userInfo = WxUserModel::getWxUserInfo($attachArray['mid']);
            $userInfo = $userInfo->toArray();
            if(count($userInfo) > 0) {
                $notify = WeixinApiModel::getModelNotice();
                foreach ($userInfo as $val) {
                    $userId = $val['openid'];
                    $result = $notify->uses($templateId)->withUrl($url)->andData($data)->andReceiver($userId)->send();
                    if($result['errmsg'] != 'ok'){
                        Common::writeLogs('error',20016,"send info".\GuzzleHttp\json_encode($data));
                    }
                }
            }

        });
        return $response;
    }

    /**
     * 固定金额产品支付请求
     *
     * @return bool | xml
     */
    public function requestpay(Request $request){

        //接收回调信息
        $post = \GuzzleHttp\json_encode($_POST);
        $body = file_get_contents('php://input');
        Common::writeLogs('info',0,"post:".$post."||body:".$body);

        //初始化微信调用模块
        $returnData = XML::parse($body);
        $product_id = \GuzzleHttp\json_decode($returnData['product_id'],true);
        $result = QrcodeModel::getInfoByid($product_id);
        $data = $result->toArray()[0];
        $ret = WeixinApiModel::initialize($data["mid"]);
        if(!$ret){
            Common::writeLogs('error',20016,"WeixinApiModel::initialize(" . $data['mid'] .")");
            return false;
        }

        $response = WeixinApiModel::getModelPayment()->handleNotify(function($notify, $successful)use($data,$ret){

            $out_trade_no = Common::getOrderNo();
            $postParams["out_trade_no"] = $out_trade_no;
            $postParams["body"] = $data['name'];
            $postParams["total_fee"] = $data['total_fee'];
            $postParams["trade_type"] = "NATIVE";
            $postParams["attach"] = json_encode(array("mid"=>$data['mid']));
            $postParams["notify_url"] = url("v1/wxnotify");//config("app.wxnotify_url");
            $result = WeixinApiModel::createOrder($postParams);
            $result = $result->toArray();
            if($result["return_code"] == "FAIL" || $result["result_code"] == "FAIL"){
                Common::writeLogs('error',20012,\GuzzleHttp\json_encode($result));
                Common::outputJson(0,20012);
            }

            //写入数所库
            $time = date('Y-m-d H:i:s');
            $attributes = array(
                "mid"=>$data["mid"],
                "wx_mch_id"=>$ret["wx_mch_id"],
                "wx_appid"=>$ret["wx_appid"],
                "body"=>$data["name"],
                "out_trade_no"=>$out_trade_no,
                "total_fee"=>(float)$postParams["total_fee"],
                "time_start"=>$time,
                "trade_type"=>$postParams["trade_type"],
            );
            $res = PayorderModel::Create($attributes);
            if(!$res){
                Common::writeLogs('error',20016,"insert table payorder".\GuzzleHttp\json_encode($attributes));
                Common::outputJson(0,20016);
            }

            echo new Response(XML::build($result));
            exit();
        });
        return $response;
    }

    public function paymsg(Request $request){

        $input = $request->all();
        $status = isset($input['status']) ? $input['status'] : 0;
        if($status){
            $msg = "支付成功！";
        }else{
            $msg = "支付失败！";
        }

        $outHtml = "<!DOCTYPE html>
        <html>
            <head>
                <meta charset=\"UTF-8\" />
                 <meta name=\"viewport\" id=\"viewport\" content=\"width=device-width, initial-scale=1, maximum-scale=1\">
                <script type=\"text/javascript\" charset=\"UTF-8\" src=\"http://res.wx.qq.com/open/js/jweixin-1.0.0.js\"></script>
                <title>微信支付</title>
                <script>
                    window.onload = function() {
                        document.addEventListener('WeixinJSBridgeReady', function onBridgeReady() {
                            // 通过下面这个API隐藏右上角按钮
                            WeixinJSBridge.call('hideOptionMenu');
                        });
                    }
                </script>
            </head>
            <body><div style='margin-top: 100px;height: 60px;line-height: 60px;vertical-align: middle;text-align: center;font-size: 22px;color: #4ABB44;width: 100%;'>".$msg."</div></body>
        </html>";
        echo $outHtml;
        exit();
    }


}

