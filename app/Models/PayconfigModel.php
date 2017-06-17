<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PayconfigModel extends Model
{

    protected $table = "yf_payconfig";

    protected $fillable = ['mid','wx_mch_id','wx_mcd_key','wx_appid','wx_appid_key','wx_token','wx_aes_key','wx_cert_path','wx_key_path','openid','type'];
    
    public static function getMchPayConf($mid){

         $result = (new static)->where("mid","=",$mid)
                ->get();
         return $result;
    }
}