<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    
    protected $table = "yf_user";

    protected $fillable = ["id","puid","mid","loginname","password","ip","status","isadmin","isdel","lastlogin_time","updated_at","created_at"];

    public static function findByInfo($loginname, $password){

        $password = md5($password);
        $result = (new static)->where("loginname","=",$loginname)
                ->where("password","=",$password)
                ->where("isdel","=",0)
                ->get();
        return $result;
    }

    public static function findByUsername($loginname){

        $result = (new static)->where("loginname","=",$loginname)
            ->where("isdel","=",0)
            ->get();
        return $result;
    }
    
	public static function getInfoByUid($uid){
		$result = (new static)->where("id","=",$uid)->where("isdel","=",0)
                ->get();
        return $result;
	}

}
