<?php

/**
 * 活动默认学分
 */
class EventCreditModel extends Model {

    //增加、编辑
    public function addEventCredit($levelId,$credits,$sid){
        $map['levelId'] = $levelId;
        $this->where($map)->delete();
        $types = D('EventType','event')->eventTypeDb($sid);
        $credits = explode('|', $credits);
        foreach ($types as $k => $v) {
            $credit = $credits[$k]*100/100;
            if($credit){
                $map['typeId'] = $v['id'];
                $map['credit'] = $credits[$k];
                $this->add($map);
            }
        }
        return true;
    }
    public function creditsByLevelId($levelId) {
        $res = $this->where('levelId='.$levelId)->field('typeId,credit')->findAll();
        if(!$res){
            return false;
        }
        return $res;
    }
    public function delCredit($levelId){
        $this->where('levelId='.$levelId)->delete();
    }
    public function creditByLevelType($levelId,$typeId){
        $map['levelId'] = $levelId;
        $map['typeId'] = $typeId;
        $credit = $this->where($map)->field('credit')->find();
        if($credit){
            return $credit['credit'];
        }
        return 0;
    }
}
