<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PrintModel extends Model
{
    
    protected $table = "yf_print";

    protected $fillable = ["id","mid","typeid","printname","machine_code","msign","mobilephone","status","updated_at","created_at"];
    
    
    public static function pageSearchLists($where,$page,$pagesize){

        $offset = ($page - 1) * $pagesize;
        $result = (new static)->where("isdel","=","0")->where($where)
            ->orderBy("id","desc");
        $total = $result->count();
        $result = $result->skip($offset)->take($pagesize)->get();
        $lists = array("result"=>$result,"total"=>$total);
        return $lists;
    }

    public static function getInfo($id){
        
         $result = (new static)->where("id","=",$id)
                 ->get();
         return $result;
    }
}
