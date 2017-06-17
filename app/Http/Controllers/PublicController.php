<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\UserModel;
use App\Libs\Common;

class PublicController extends Controller
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
    
    public function login(Request $request){

        $getParamArray = $request->query->all();
        $textJson = file_get_contents('php://input');
        $postParams = json_decode($textJson, true);
        if(!$postParams){
            Common::outputJson(0,20009);
        }
        $paramArray = array_merge($getParamArray,$postParams);
       
        //验证参数
        $srcParams = array("appid","version","time","sign","loginname","password","ip");
        if(!Common::validationParams($paramArray, $srcParams)){
            Common::outputJson(0,20010);
        }
 
        //验证签名
        $appkey = $this->getAppKey($getParamArray["appid"]);
        if(!Common::validationSign($appkey,$paramArray, $getParamArray["sign"])){
            Common::outputJson(0,20006);
        }

        //验证用户和密码
        $userInfo = UserModel::findByInfo($paramArray["loginname"], $paramArray["password"]);
        $userInfo = $userInfo->toArray();
        if(count($userInfo) == 0){
             Common::outputJson(0,20008);
        }
        $userInfo = $userInfo[0];

        //用户是否禁用
        if($userInfo['status'] == 0){
            Common::outputJson(0,20021);
        }

        //更新登陆状态
        $updateData = array('lastlogin_time'=>date("Y-m-d H:i:s"),"logincount"=>$userInfo['logincount'] + 1);
        UserModel::where('id', '=', $userInfo['id'])->update($updateData);

        //生成token,将TOKEN写存储登陆标识
        $timestamp = time();
        $privateKeyString = $this->getPrivateKey($getParamArray["appid"]);
        $originalData = $this->createTokenString($paramArray["loginname"],$userInfo["id"],$timestamp);
        $token = Common::RsaEncrypt($originalData, $privateKeyString);
        $this->tokenWriteCache($getParamArray["appid"],$paramArray["loginname"],$userInfo["id"],$timestamp);
        $data = array("uid"=>$userInfo["id"],"mid"=>$userInfo["mid"],"loginname"=>$userInfo["loginname"],"isadmin"=>$userInfo["isadmin"]);
        Common::outputJson(1,0,$data,"token^|^".$token);
    }
    
	public function logout(Request $request){
       
		$getParamArray = $request->query->all();
        $textJson = file_get_contents('php://input');
        $postParams = json_decode($textJson, true);
        if(!$postParams){
            Common::outputJson(0,20009);
        }
        $paramArray = array_merge($getParamArray,$postParams);
       
        //验证参数
        $srcParams = array("appid","version","time","sign","token","uid");
        if(!Common::validationParams($paramArray, $srcParams)){
            Common::outputJson(0,20010);
        }

        //验证签名
        $appkey = $this->getAppKey($getParamArray["appid"]);
        if(!Common::validationSign($appkey,$paramArray, $getParamArray["sign"])){
            Common::outputJson(0,20006);
        }
		
		//是否登陆
        if(!$this->VerificationToken($paramArray)){
             Common::outputJson(0,20011);
        }
        
		if(!$this->deleteTokenCache($paramArray)){
			Common::outputJson(0,0);
		}
		Common::outputJson(1,0);
	}

    public function checkoauth(Request $request){

        $getParamArray = $request->query->all();
        $textJson = file_get_contents('php://input');
        $postParams = json_decode($textJson, true);
        if (!$postParams) {
            Common::outputJson(0, 20009);
        }
        $paramArray = array_merge($getParamArray, $postParams);

        //验证参数
        $srcParams = array("appid", "version", "time", "sign", "token", "uid");
        if (!Common::validationParams($paramArray, $srcParams)) {
            Common::outputJson(0, 20010);
        }

        //验证签名
        $appkey = $this->getAppKey($getParamArray["appid"]);
        if (!Common::validationSign($appkey, $paramArray, $getParamArray["sign"])) {
            Common::outputJson(0, 20006);
        }

        //是否登陆
        if (!$this->VerificationToken($paramArray)) {
            Common::outputJson(0, 0);
        }
        Common::outputJson(1, 0);
    }

}