<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\WeixinApiModel;
use App\Models\QrcodeModel;
use Endroid\QrCode\QrCode;
use App\Libs\Common;

class QrcodeController extends Controller
{
        
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //session()->regenerate();
    }

    public function wxurl(Request $request){

        $getParamArray = $request->query->all();
        $textJson = file_get_contents('php://input');
        $postParams = json_decode($textJson, true);
        if (!$postParams) {
            Common::outputJson(0, 20009);
        }
        $paramArray = array_merge($getParamArray, $postParams);

        //验证参数
        $srcParams = array("appid", "version", "time", "sign", "token", "id");
        if (!Common::validationParams($paramArray, $srcParams)) {
            Common::outputJson(0, 20010);
        }

        //验证签名
        $appkey = $this->getAppKey($getParamArray["appid"]);
        if (!Common::validationSign($appkey, $paramArray, $getParamArray["sign"])) {
            Common::outputJson(0, 20006);
        }

        //业务处理
        try {
            $result = QrcodeModel::getInfoByid($postParams['id']);
            $data = $result->toArray();
            $info = $data[0];
            $ret = WeixinApiModel::initialize($info['mid']);
            if(!$ret){
                Common::writeLogs('error',20016,"WeixinApiModel::initialize(" . $info['mid'] .")");
                return false;
            }
            $wxUrl = WeixinApiModel::getModelPayment()->scheme($postParams['id']);
            Common::outputJson(1, 0,array('wxurl'=>$wxUrl));
        } catch (Exception $e) {
            Common::outputJson(0, 20001);
        }
    }

    /**
     * 输出二维码图片
     * $encodestr = base64_encode(json_encode(array("url"=>"","size"=>300,"sign"=>"")));
     * @param type $encodestr
     * @return void
     */
    public function image($encodestr){

        //验证参数签名
        $base64Array = json_decode(base64_decode($encodestr),true);
        $paramSign = $base64Array['sign'];
        unset($base64Array['sign']);
        $sign = md5(json_encode(Common::paramSort($base64Array)).config("app.grcode_key_encrypt"));
        if($paramSign != $sign){
            die("签名错误，不合法的请求！");
        }
        $url = base64_decode($base64Array['url']);
        $size = $base64Array['size'];

        //输出二维码图片
        header('Content-type: image/png');
        $qrCode = new QrCode();
        $qrCode
            ->setText($url)
            ->setSize($size)
            ->setPadding(10)
            ->setErrorCorrection('high')
            ->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0))
            ->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0))
            //->setLabel('My label')
            ->setLabelFontSize(16)
            ->setImageType("png")
            ->render();
    }


}