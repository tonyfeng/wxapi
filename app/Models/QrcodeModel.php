<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class QrcodeModel extends Model
{

    protected $table = "yf_qrcode";
    protected $fillable = ["mid","name","total_fee"];

    public static function getInfoByid($id){

        $result = (new static)->where("id","=",$id)
            ->where("isdel","=",0)
            ->get();
        return $result;
    }
    
}
