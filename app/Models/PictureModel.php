<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class PictureModel extends Model
{
    
    protected $table = "yf_picture";

    protected $fillable = ["id","mid","title","picno","file","updated_at","created_at"];
    
    public static function getList($mid){

        $result = (new static)->where("mid","=",$mid)
            ->get();
        return $result;
    }

    public static function getInfoBypicno($picno){

        $result = (new static)->where("picno","=",$picno)
            ->get();
        return $result;
    }
}
