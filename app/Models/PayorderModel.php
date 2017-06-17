<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PayorderModel extends Model
{
    
    protected $table = "yf_payorder";

    protected $fillable = ["id","uid","mid","wx_mch_id","wx_appid","device_info","body","detail","attach","out_trade_no","transaction_id","total_fee","callback_total_fee","fee_type","poundage_total_fee","settlement_total_fee","cash_fee","cash_fee_type","spbill_create_ip","time_start","time_expire","openid","bank_type","return_count","return_code","result_code","trade_type","status","is_subscribe","sub_is_subscribe","coupon_fee","coupon_count","coupon_id_var","coupon_fee_var","time_end"];
    
    //偏移量提示数据
    public static function orderLists($starttime,$pagesize,$pkid = 0){

        if(empty($endtime)){
            $endtime = $starttime." 23:59:59";
        }else{
            $endtime = $endtime." 23:59:59";
        }
        $starttime = $starttime." 00:00:00";
        $result = (new static)->where("status","=",1)->where("id",">",$pkid)->whereBetween("time_start",array($starttime,$endtime))
            ->orderBy("id","asc")->take($pagesize)->get();
        return $result;
    }
	
	public static function getTrateOrder($uid,$mid,$querytime){

		$starttime = $querytime." 00:00:00";
		$endtime = $querytime." 23:59:59";
        $uidArray = UserModel::getSubUid($uid);
        $query = (new static)->whereIn("uid",$uidArray);
        if($mid){
            $query->where("mid","=",$mid);
        }
        $result = $query->where("status","=",1)
                ->whereBetween("time_start",array($starttime,$endtime))
                ->orderBy("time_start","desc")
				->get();
		return $result;
	}
	
	
    public static function queryByOrderNo($out_trade_no){
    
        $result = (new static)
            ->where("out_trade_no","=",$out_trade_no)
            ->get();
        return $result;
    }
    
    public static function queryByTransactionId($transaction_id){
        
        $result = (new static)->where("transaction_id","=",$transaction_id)->get();
        
        return $result;
    }
    
}