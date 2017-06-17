<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SettlementModel extends Model
{
    
    protected $table = "yf_settlement";

    protected $fillable = ["id","mid","wx_mch_id","amount","total_fee","settlement_total_fee","poundage_total_fee","settlement_date"];

    public static function settlementQuery($mid,$settlementDate){

        $result = (new static)->where("mid","=",$mid)->where("settlement_date","=",$settlementDate)
            ->get();
        return $result;
    }

    
}