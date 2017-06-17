<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Libs\Common;
use App\Models\PictureModel;

class MediaController extends Controller
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

    public function uploadImg(Request $request)
    {

        $paramArray = $request->all();
        $fileObj = isset($paramArray['media']) ? $paramArray['media'] : null;
        if (!$paramArray) {
            Common::outputJson(0, 20009);
        }

        if($fileObj->isValid()){
            $paramArray['media'] = 1;
        }

        //验证参数
        $srcParams = array("appid", "version", "time", "sign","mid","media");
        if (!Common::validationParams($paramArray, $srcParams)) {
            Common::outputJson(0, 20010);
        }

        //验证图片大小
        if($fileObj->getSize() > config('app.picture.size')){
            Common::outputJson(0, 20010);
        }

        //验证图片扩展名
        $extName = strtolower($fileObj->getClientOriginalExtension());
        if(!in_array($extName,config('app.picture.ext'))){
            Common::outputJson(0, 20010);
        }

        //验证图片类型

        //验证签名
        unset($paramArray['media']);
        $appkey = $this->getAppKey($paramArray["appid"]);
        if (!Common::validationSign($appkey, $paramArray, $paramArray["sign"])) {
            Common::outputJson(0, 20006);
        }

        //业务处理
        try {
            $savePath = storage_path() . '/uploads';
            $picno = Common::getPictureNo();
            $fileName = $picno."." . $extName;
            $fileObj->move($savePath, $fileName);
            $mid = $paramArray['mid'];
            $data = array("mid"=>$mid,"picno"=>$picno,'file'=>$fileName);
            PictureModel::create($data);
            Common::outputJson(1, 0,$picno);
        } catch (Exception $e) {
            Common::outputJson(0, 20001);
        }

    }
    
    public function showPic(Request $request,$encodestr)
    {

        //验证参数签名
        $base64Array = json_decode(base64_decode($encodestr),true);
        $paramSign = $base64Array['sign'];
        unset($base64Array['sign']);
        $sign = md5(json_encode(Common::paramSort($base64Array)).config("app.grcode_key_encrypt"));
        if($paramSign != $sign){
            die("签名错误，不合法的请求！");
        }
        $picno = $base64Array['picno'];

        //业务处理
         @header('Cache-Control: max-age=86400,must-revalidate');
         @header("Content-Type:image/png");

        try {
            $lists = PictureModel::getInfoBypicno($picno);
            if($lists->count() > 0) {
                $info = $lists->toArray()[0];
                $fileName = storage_path() . '/uploads/' . $info["file"];
                $contents = file_get_contents($fileName);
                echo $contents;
            }else{
                Common::outputJson(1, 20024);
            }
        } catch (Exception $e) {
            Common::outputJson(0, 20001);
        }
    }

}

