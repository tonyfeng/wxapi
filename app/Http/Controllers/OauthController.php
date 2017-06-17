<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\WeixinApiModel;
use App\Libs\Common;
use Symfony\Component\HttpFoundation\Response;

class OauthController extends Controller
{

    /**
     * 微信授权
     * scope = snsapi_base|snsapi_userinfo
     */
    public function wxOauth(Request $request){

        $getParamArray = $request->query->all();
        $textJson = file_get_contents('php://input');
        $postParams = json_decode($textJson, true);
        if(!$postParams){
            Common::outputJson(0,20009);
        }
        $paramArray = array_merge($getParamArray,$postParams);

        //验证参数  action = login/pay
        $srcParams = array("appid","version","time","sign","mid","scope","redirect_url");
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
        $scope = $paramArray['scope'];
        $redirectUrl = url("v1/wxcall");
        $mid = $paramArray['mid'];
        $redirect_url = $paramArray['redirect_url'];

        $redirectParamArray = array("mid"=>$mid,"redirect_url"=>$redirect_url);
        $redirectParamArray['sign'] = md5(json_encode(Common::paramSort($redirectParamArray)).config('app.grcode_key_encrypt'));
        $encode = base64_encode(json_encode($redirectParamArray));
        $redirectUrl .= "?encode=".$encode;
        $response = WeixinApiModel::getModelOauth()->scopes(array($scope))
            ->setRedirectUrl($redirectUrl)
            ->redirect();
        $url = $response->getTargetUrl();

        $outHtml = "<!DOCTYPE html>
        <html>
            <head>
                <meta charset=\"UTF-8\" />
                <meta http-equiv=\"refresh\" content=\"1;url=".$url."\" />
                <title>微信页面跳转中...</title>
            </head>
            <body>
            </body>
        </html>";
        echo $outHtml;
        exit();

    }

    public function wxcall(Request $request){

        $paramArray = $request->query->all();
        $encode = $paramArray['encode'];
        if(!$encode){
            Common::outputJson(0,0);
        }

        //解码
        $base64Array = json_decode(base64_decode($encode),true);
        $paramSign = $base64Array['sign'];
        unset($base64Array['sign']);
        $sign = md5(json_encode(Common::paramSort($base64Array)).config("app.grcode_key_encrypt"));
        if($paramSign != $sign){
            die("签名错误，不合法的请求！");
        }
        $mid = $base64Array['mid'];
        $redirectUrl = $base64Array['redirect_url'];

        WeixinApiModel::initialize($mid);
        $user = WeixinApiModel::getModelOauth()->user()->toArray();
        $status = 1;
        if(!$user){
            $status = 0;
        }

        //地址拼接
        if(strpos($redirectUrl,"?") > 0){
            $s = "&";
        }else{
            $s = "?";
        }
        $redirectUrl .= $s . "status=".$status;

        $outHtml = "<!DOCTYPE html>
        <html>
            <head>
                <meta charset=\"UTF-8\" />
                <title>微信页面跳转中...</title>
            </head>
            <body onload='form.submit();'>
            <form name='form' method='POST' action='".$redirectUrl."'> 
                <input type='hidden' name='mid' value='".$mid."' />
                <input type='hidden' name='userInfo' value='".json_encode($user)."' />
            </form> 
            </body>
        </html>";
        echo $outHtml;
        exit();
    }











}

