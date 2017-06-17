<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class WxUserModel extends Model
{
    
    protected $table = "yf_wxuser";

    protected $fillable = ["mid","openid","name","avatar","isdel","updated_at","created_at"];

    public static function getWxUserInfo($mid){

        $result = (new static)->where("mid","=",$mid)
            ->where("isdel","=",0)
            ->get();
        return $result;
    }

    public static function getByOpenid($openid){

        $result = (new static)->where("openid","=",$openid)
            ->where("isdel","=",0)
            ->get();
        return $result;
    }

}
