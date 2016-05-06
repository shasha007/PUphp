<?php

class GrowPraiseModel extends Model {

    public function apiPraise($data){
        $result=$this->add($data);
        return $result;
    }

    public function getPraise($grow_id,$uid){
        $map['uid']=$uid;
        $map['grow_id']=$grow_id;
        $res = $this->where($map)->find();
        return $res;
    }

    //统计点赞数
    public function countPraise($grow_id){
        $res = $this->where('grow_id='.$grow_id)->count();
        return $res;
    }
}

?>
