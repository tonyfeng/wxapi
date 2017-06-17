<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use App\Models\PayconfigModel;
use App\Libs\Common;

class Controller extends BaseController
{

    /**
     * 获取appkey
     * @param string $appid
     * @return string
     */
    protected function getAppKey($appid){

        $result = $this->getAppPlatformConfig($appid);
        if(!$result){
            return false;
        }
        $appKey = $result["appkey"];
        return $appKey;
    }
	
	protected function getInfoByAppid($appid){
        $result = $this->getAppPlatformConfig($appid);
        if(!$result){
            return false;
        }
		return $result;
	}
	
    protected function getPublicKey($appid){

        $result = $this->getAppPlatformConfig($appid);
        if(!$result){
            return false;
        }
        $publicKey = file_get_contents(storage_path() . '/rsa/'.$result["publickey"]);
        return $publicKey;
    }
	
	protected function getPrivateKey($appid){

        $result = $this->getAppPlatformConfig($appid);
        if(!$result){
            return false;
        }
        $privateKey =  file_get_contents(storage_path() . '/rsa/'.$result["privatekey"]);
        return $privateKey;
    }

    protected function getAppPlatformConfig($appid){
        
        $configArray = Config::get("app.app_platform");
        $pconfig = array();
        foreach ($configArray as $key=>$val){
            if($val['appid'] == $appid){
                $pconfig = $val;
                break;
            }
        }
        return $pconfig;

    }
	
	/**
     * 拼装字符串用于加密
     * @param type $username
     * @param type $uid
     * @return string
     */
    protected function createTokenString($username,$uid,$timestamp){
        
        $originalData = $username.":".$uid.":".$timestamp;
        return $originalData;
    }
    
    /**
     * 验证TOKEN
     * @param type $paramArray
     * @return int
     */
    protected function VerificationToken($paramArray){
        
        $cacheKey = "token:".$paramArray["appid"].":".$paramArray["uid"];
        $cacheKey = md5($cacheKey);
        if(!Cache::has($cacheKey)){
            return 0; //不存在
        }
        
        //RSA解密
        $encryptData = $paramArray["token"];
        $publicKeyString = $this->getpublicKey($paramArray["appid"]);
        $token = Common::RsaDecrypt($encryptData, $publicKeyString);
        if(empty($token)){
            return 0;
        }
        $tokenData = explode(":", $token);
        $serverData = explode(":",Cache::get($cacheKey));
        if(empty($serverData)){
            return 0;
        }

        //是否同一用户
        if(($serverData[1] != $tokenData[1]) && ($serverData[2] != $tokenData[2])){
            return 0;
        }
        
        //判断时间是否一致TOKEN是否过期限
        if($serverData[2] != $tokenData[2]){
            return 0;
        }
        $nowTime = time();
        $hour = floor((strtotime($nowTime) - strtotime($serverData[2])) % 86400 / 3600);
        if($hour >= 24){  //大于小时间就过期限
            return 0;
        }        
        return 1;
    }
	
	protected function deleteTokenCache($paramArray){
		
		$cacheKey = "token:".$paramArray["appid"].":".$paramArray["uid"];
        $cacheKey = md5($cacheKey);
        if(!Cache::has($cacheKey)){
            return 0; //不存在
        }
		
		Cache::forget($cacheKey);
		return 1;
	}
    
    /**
     * 保存认证token
     * @param type $appid
     * @param type $username
     * @param type $uid
     */
    protected function tokenWriteCache($appid,$username,$uid,$timestamp){
        $cacheKey = "token:".$appid.":".$uid;
        $cacheValue = $username.":".$uid.":".$timestamp;  //username + uid + timestamp
        $cacheKey = md5($cacheKey);
        Cache::put($cacheKey, $cacheValue, 1440);
    }
}
