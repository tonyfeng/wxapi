<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Payment\Order;
use App\Models\PayconfigModel;
use App\Models\UserModel;

class WeixinApiModel extends Model
{

    private static $mConfig;

    private static $payment;

    private static $oauth;

    private static $qrcode;

    private static $notice;

    private static $js;

    /*
     *$options = [
    // 前面的appid什么的也得保留哦
    'app_id' => 'xxxx',
    // ...
    // payment
    'payment' => [
        'merchant_id'        => 'your-mch-id',
        'key'                => 'key-for-signature',
        'cert_path'          => 'path/to/your/cert.pem', // XXX: 绝对路径！！！！
        'key_path'           => 'path/to/your/key',      // XXX: 绝对路径！！！！
        'notify_url'         => '默认的订单回调地址',       // 你也可以在下单时单独设置来想覆盖它
        // 'device_info'     => '013467007045764',
        // 'sub_app_id'      => '',
        // 'sub_merchant_id' => '',
        // ...
    ],
];

    */
    /**
     * 初始化配置
     * @param type $options
     */
    public static function initialize($mid){

        //获取子商户信息
        $result = PayconfigModel::getMchPayConf($mid);
        if(!$result) {
            return false;
        }
        $result = $result[0];

        //商户配置
        if($result['type'] == 1){
            $mchConfig = [
                'merchant_id' => $result["wx_mch_id"],  //微信支付普通商户号
                'key' => $result["wx_mcd_key"],         ///普通商户KEY
                'cert_path' => $result["wx_cert_path"], // XXX: 绝对路径
                'key_path' => $result["wx_key_path"]   // XXX: 绝对路径
            ];

            //普通商户公众号配置信息
            $app_id = $result["wx_appid"];
            $secret = $result["wx_appid_key"];
            $token = $result["wx_token"];
            $aes_key = $result["wx_aes_key"];
        }else {
            $mchConfig = [
                'merchant_id' => Config::get("weixin.merchant_id"),  //微信支付服务商商户号
                'key' => Config::get("weixin.merchant_key"), //微信支付服务商商户KEY
                'cert_path' => $result["wx_cert_path"], // XXX: 绝对路径
                'key_path' => $result["wx_key_path"],   // XXX: 绝对路径
                'sub_app_id' => $result["wx_appid"],    //子商户公众号
                'sub_merchant_id' => $result["wx_mch_id"],    //子商户号
            ];

            //服务商公众号配置信息
            $app_id = Config::get("weixin.weixin_appid");
            $secret = Config::get("weixin.weixin_appsecret");
            $token = Config::get("weixin.weixin_token");
            $aes_key = Config::get("weixin.weixin_aes_key");
        }

        //配置
        self::$mConfig = [
            'app_id'    => $app_id,
            'secret'    => $secret,
            'token'     => $token,
            'aes_key'   => $aes_key,
            'payment'   => $mchConfig
        ];



        $app = new Application(self::$mConfig);
        self::$payment = $app->payment;
        self::$qrcode = $app->qrcode;
        self::$oauth = $app->oauth;
        self::$notice = $app->notice;
        self::$js = $app->js;
        return $result;
    }
    
    /**
     * 微信应用支付实例对象
     * @return type
     */
    public static function getModelPayment(){

        return self::$payment;
    }

    /**
 * 微信应用OAUTH实例对象
 * @return type
 */
    public static function getModelOauth(){

        return self::$oauth;
    }

    /**
     * 微信应用二维码实例对象
     * @return type
     */
    public static function getModelQrcode(){

        return self::$qrcode;
    }

    /**
     * 微信应用消息模板实例对象
     * @return type
     */
    public static function getModelNotice(){

        return self::$notice;
    }


    /**
     * 微信应用OAUTH实例对象
     * @return type
     */
    public static function getModelJs(){

        return self::$js;
    }

    public static function sign($params){
        return \EasyWeChat\Payment\generate_sign($params, self::$mConfig['payment']['key'], 'md5');
    }



        /**
     * 生成统一订单
     * @param array $data
     * @return array
     */
    public static function createOrder($data){
                
        $attributes = [
            'out_trade_no'     => $data['out_trade_no'],
            'total_fee'        => $data['total_fee'],
            'trade_type'       => $data['trade_type'],
            'body'             => $data['body'],
            'notify_url'       => $data["notify_url"], // 支付结果通知网址，如果不设置则会使用配置里的默认地址
            'spbill_create_ip' => !empty($data['ip']) ? $data['ip'] : '',
            'attach'           => !empty($data['attach']) ? $data['attach'] : '',
            'openid'        => !empty($data['openid']) ? $data['openid'] : ''
        ];

        if(!empty($data["product_id"])){
            $data["product_id"] = $data["product_id"];
        }
        //去除传空值的字段
        foreach ($attributes as $key=>$value){
            if(empty($value)){
                unset($attributes[$key]);
            }
        }
        $order = new Order($attributes);
        $result = self::$payment->prepare($order);
        return $result;
    }
    
    /**
     * 查询订单位
     * @param string $out_order_no
     * @return string 
     */
    public static function orderQuery($out_trade_no){
        
        $result = self::$payment->query($out_trade_no);
        
        return $result;
    }

}