<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/



$app->get('/', function () use ($app) {
    return 'Not Allowed';
});

    
/*** v1版本接口 ***/
$app->post('v1/loginoauth', 'PublicController@login');
$app->post('v1/checkoauth', 'PublicController@checkoauth');
$app->post('v1/signout', 'PublicController@logout');

//扫码支付请求&& 订单
$app->post('v1/wxnative', 'WeixinController@wxnative');
$app->post('v1/jsapi', 'WeixinController@jsapi');
$app->post("v1/wxpagepay", 'WeixinController@wxpagepay');
$app->get('v1/wxpaycall/{paramstr}', 'WeixinController@wxpaycall');

//异步通知notify
$app->post('v1/wxnotify', 'WeixinController@notify');
$app->post('v1/requestpay', 'WeixinController@requestpay');
$app->post('v1/wxoauth', 'OauthController@wxOauth');
$app->get('v1/wxcall', 'OauthController@wxcall');
$app->get('v1/paymsg', 'WeixinController@paymsg');

//媒体资源
$app->post('v1/image/uploadimg', 'MediaController@uploadImg');
$app->get('image/{encodestr}', 'MediaController@showPic');  //输出图片

//二维码
$app->get('qrcode/{encodestr}', 'QrcodeController@image');  //输出二维码图片
$app->post('v1/qrcode/wxurl', 'QrcodeController@wxurl');  

//打印设备接口
$app->post('v1/print', 'PrintController@rprint');
$app->post('v1/addprint', 'PrintController@add');
$app->post("v1/delprint", 'PrintController@delete');



















