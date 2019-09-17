<?php
namespace app\common\model;


use think\Model;

class AuthRule extends Model
{
    public function getSon($sid){
        return $this->field('id,name,title,pid')->where('pid',$sid)->select();
    }
    public function getf($fid){
        return $this->where('pid',$fid)->field('id,title')->find()->title;
    }
}
