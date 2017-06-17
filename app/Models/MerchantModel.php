<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class MerchantModel extends Model
{
    
    protected $table = "yf_merchant";

    protected $fillable = ['type',"uid","categoryid","name","shopimg","numberno","picid","cardno","cardid1","cardid2","provinceid","cityid","address","bank_name","bank_user","bank_no","username","mobile","email","mid","body","other_picid"];

    public static function getInfo($uid){
        
         $result = (new static)->where("uid","=",$uid)
                 ->get();
         return $result;
    }

    public static function getMchInfo($mid){

        $result = (new static)->where("mid","=",$mid)
            ->get();
        return $result;
    }
    
    public static function getLists($uid){
        
         $result = (new static)->where("uid","=",$uid)
             ->where("isdel","=",0)
             ->get();
         return $result;
    }

    public static function pageSearchOrder($uid,$page,$pagesize){

        $offset = ($page - 1) * $pagesize;
        $query = (new static)->where("uid","=",$uid);
        $result = $query->where("isdel","=",0);
        $total = $result->count();
        $result = $result->skip($offset)->take($pagesize)->get();
        return array("result"=>$result,"total"=>$total);
    }
}
