<?php
/**
 * Created by wenjiping.
 * User: Administrator
 * Date: 2016/1/13
 * Time: 15:21
 */

class CheckInModel extends Model{


    /**
     * 获取用户某个时间段的签到详情
     * @param $user_id          用户ID
     * @param $start_time       开始签到时间
     * @param $end_time         结束签到时间
     * @return array|bool
     */
    public function getUserCheckList($user_id,$start_time,$end_time){
        $map['user_id']         = $user_id;
        $map['check_in_date']   = array(array('egt',$start_time),array('elt',$end_time));
        $list = $this->field('check_in_date')->where($map)->select();
        return $list;
    }

    /**
     * 签到
     * @param $user_id
     * @return bool | int
     */
    public function checkIn($user_id){
        $data['user_id']        = $user_id;
        $data['check_in_date']  = strtotime(date('Y-m-d'));
        $data['create_time']    = time();
        $data['user_name']      = getUserName($user_id);
        $data['school_id']      = getUserField($user_id,'sid');
        $data['school_name']    = getUserSchoolName($user_id);
        return $this->add($data);
    }

    /**
     * 判断是否为连续签到
     * @param $user_id
     */
    public function isContinueCheckIn($user_id){
        $map['user_id']         = $user_id;
        $map['check_in_date']   = strtotime('-1 day',strtotime(date('Y-m-d')));
        $res = $this->where($map)->find();
        return $res;
    }

    /**
     * 判断用户是否已经签到过
     * @param $user_id
     * @return mixed
     */
    public function isCheckIn($user_id){
        $map['user_id']         = $user_id;
        $map['check_in_date']   = strtotime(date('Y-m-d'));
        $res = $this->where($map)->find();
        return $res;
    }

}