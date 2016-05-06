<?php

/**
 * 活动默认学分是否激活
 */
class EventAutoLevelModel extends Model {

    public function statusNow($sid){
        $map['sid'] = $sid;
        $list = $this->where($map)->field('status')->find();
        if(!$list){
            return 0;
        }
        return $list['status'];
    }
    // 更改状态
    public function changeStatus($sid,$uid) {
        $map['sid'] = $sid;
        $list = $this->where($map)->field('status')->find();
        if(!$list){
            $map['uid'] = $uid;
            $map['ctime'] = time();
            $map['status'] = 1;
            $this->add($map);
            return 1;
        }
        $data['uid'] = $uid;
        $data['ctime'] = time();
        $data['status'] = 0;
        if(!$list['status']){
            $data['status'] = 1;
        }
        $this->where($map)->save($data);
        return $data['status'];
    }
}
