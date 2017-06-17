<?php

namespace App\Libs;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class Common
{
    
	/**
	 * 验证日期是否合法
	 *
	 */
	public static function isDate($dateString) {
		return strtotime( date('Y-m-d', strtotime($dateString)) ) === strtotime( $dateString );
	}	
	
    /**
     * 输出JSON字符串
     * @param int $status
     * @param string $error_code
     * @param void $data
     * @return string
     */
    public static function outputJson($status,$error_code,$data=null,$extenArray=array()){
        
        $lists = array("status"=>$status,"error_code"=>$error_code);
        if(is_array($extenArray)){
            $lists = array_merge($lists,$extenArray);
        }else{
            $tempArray = explode("^|^",$extenArray);
            $lists[$tempArray[0]] = $tempArray[1];
        }
        $lists["data"] = $data;
        $result = json_encode($lists);
        echo $result;
        exit();
    }
    
    /**
     * 验证参数是否为空
     * @param array $postParamArray POST参数
     * @param array $srcParamArray  需要验证的参数
     */
    public static function validationParams($postParamArray ,$srcParamArray){
                
        //验证key/value的是否非空值
        foreach ($srcParamArray as $value){
            if(!isset($postParamArray[$value])){
                return false;
            }
            if(empty($postParamArray[$value])){
                return false;
            }
        }
        return true;
    }
    
    /**
     * 验证签名是否正确
     * @param type $paramArray
     * @param type $sign
     * @return boolean
     */
    public static function  validationSign($appkey,$paramArray, $sign){
        $paramSign = self::md5Sign($appkey,$paramArray);
		
        if($paramSign != $sign){
            return false;
        }
        return true;
    }

    /**
     * md5签名
     * @param array $paramArray
     * @return string
     */
    public static function md5Sign($appkey,$paramArray){
        
        //去除sign参数
        if(isset($paramArray["sign"])){
            unset($paramArray["sign"]);
        }
        $paramString = Common::paramSort($paramArray);
        $sign = md5($paramString . $appkey);      
		//fwrite(fopen("/data1/www/api-service_yftechnet_com/storage/logs/sign.txt","w"),$paramString . $appkey."---".$sign);
        return $sign;
    }
    
    /**
     * 将key/value参数 a 到 z 的顺序排序
     * @param array $params
     * @return string
     */
    public static function paramSort($paramArray){
                
        $tempParamArray = array();
        $arrayKey = array_keys($paramArray);
        sort($arrayKey);
        foreach ($arrayKey as $value){
            if(empty($paramArray[$value])){
                continue;
            }
            $tempParamArray[] = $value."=".$paramArray[$value];
        }
        $paramString = implode("&", $tempParamArray);
        return $paramString;
    }

    /**
     * 生成商户消费订单号36位
     * @param type $uid
     * @return string
     */
    public static function getOrderNo(){

        $milliSecond =  floor(microtime() * 1000);
        $strNo = date("ymdHis") . $milliSecond.rand(0,9);
        $len = strlen($strNo);
        if($len < 16){
            $strUid = "";
            for($i = 0;$i < 16 - $len;$i++){
                $strUid .= rand(0,9);
            }
            $strNo = $strNo.$strUid;
        }
        return $strNo;
    }

    /**
     * 获取平台商户号
     * @param type $uid
     * @return string
     */
    public static function getMierchantNo(){

        $milliSecond =  floor(microtime() * 1000);
        $strNo = date("ymdHis") . $milliSecond.rand(100,999);
        return $strNo;
    }

    /**
     * 获取图片编号
     * yf+  md5(商户注册UID  + 时间(20161208201920) + 时间截 + 随机数)
     * @param type $uid
     * @return string
     */
    public static function getPictureNo(){

        $milliSecond =  floor(microtime()*1000);
        $strNo = "pic".date("YmdHis").$milliSecond.rand(100,999);
        $strNo = md5($strNo);
        $mid = substr($strNo,2);
        return $mid;
    }
    
    /**
     * 字符串RSA加密
     * @param type $originalData   用于加密的字符串
     * @param type $privateKeyString    密钥文件内容
     * @return void
     */
    public static function RsaEncrypt($originalData, $privateKeyString){

        $privateKeyString = openssl_pkey_get_private($privateKeyString);
        if (openssl_private_encrypt($originalData, $encryptData, $privateKeyString)){
            $encryptData = base64_encode($encryptData);
            return $encryptData;
        }else{
            return false;
        }
    }
    
    /**
     * 字符串RSA解密
     * @param type $encryptData
     * @param type $publicKeyString
     * @return void
     */
    public static function RsaDecrypt($encryptData, $publicKeyString){
        
        $publicKeyString = openssl_pkey_get_public($publicKeyString);
        $encryptData = base64_decode($encryptData);
        if (openssl_public_decrypt($encryptData, $originalData, $publicKeyString)) {  
            return $originalData;
        } else {  
            return false;
        }
    }

    /**
     * 写日志文件 Common::writeLogs("error",0,json_encode($_REQUEST));
     * @param $type  日志类型
     * @param  $code 状态码
     * @param $msg   信息
     * @return bool
     */
    public static function writeLogs($type,$code,$msg){

        //日志类型
        $logType = array(
            "info"=>Logger::INFO,
            "error"=>Logger::ERROR,
            "notice"=>Logger::NOTICE,
            "warning"=>Logger::WARNING,
            "critical"=>Logger::CRITICAL,
            "alert"=>Logger::ALERT,
            "emergency"=>Logger::EMERGENCY,
            "debug"=>Logger::DEBUG);
        if(!in_array($type,array_keys($logType))){
            return false;
        }

        $msgContents =   "||" . $code . "||" . $_SERVER["REQUEST_URI"] . "||" . $msg. "||" ;
        $logFile = storage_path() . "/logs/custom-" . date("YmdH", time()) . '.log';
        //写日志
        $log = new Logger('yftechnet');
        $log->pushHandler(new StreamHandler($logFile, $logType[$type]));
        $log->$type($msgContents);
        return true;
    }

    /**
     * POST请求
     * @param $remote_server
     * @param $post_string
     * @return array
     */
    public static function requestByCurl($remote_server, $post_string){

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt ($ch, CURLOPT_CUSTOMREQUEST, "POST" );
        curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, FALSE );
        curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, FALSE );
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible;)");
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,3);
        curl_setopt($ch,CURLOPT_TIMEOUT,5);
        $data = curl_exec($ch);
        $code = curl_getinfo($ch,CURLINFO_HTTP_CODE);
        echo $code;
        curl_close($ch);
        if($code != 200){
            return false;
        }
        return $data;
    }


}
