<?php
/**
 * Created by wenjiping.
 * User: Administrator
 * Date: 2016/1/13
 * Time: 15:17
 */


class IndexAction extends Action{

    /**
     * 签到页面
     */
    public function index(){
        $type_id = intval($_REQUEST['type_id']);

        if ($type_id < 1){
            $this->error('ID错误');
        }

        $time = D('CheckInType')->getCurrentTime($type_id);
        if (!($time)){
            $this->error('没有找到对应的签到时间段');
        }
        $start_time = $time['start_time'];
        $end_time   = $time['end_time'];

        //取得某人的签到记录
        $check_in_list = D('CheckIn')->getUserCheckList($this->mid,$start_time,$end_time);

        //取得签到排行
        $ranking = D('CheckInTotal')->getRankList($type_id);

        $this->assign('checkInList',$check_in_list);
        $this->assign('ranking',$ranking);
        $this->display();
    }

    public function ajaxIndex(){
        $type_id = intval($_REQUEST['type_id']);

        if ($type_id < 1){
            $this->error('ID错误');
        }

        $time = D('CheckInType')->getCurrentTime($type_id);
        if (!($time)){
            $this->error('没有找到对应的签到时间段');
        }
        $start_time = $time['start_time'];
        $end_time   = $time['end_time'];

        //取得某人的签到记录
        $check_in_list = D('CheckIn')->getUserCheckList($this->mid,$start_time,$end_time);
        foreach($check_in_list as &$item){
            $item['check_in_date'] = date('Y-m-d',$item['check_in_date']);
        }
        $this->ajaxReturn($check_in_list,'获取签到日期成功',1);
    }

    /**
     * 签到
     *      传入参数：POST $type
     */
    public function checkIn(){
        $user_id = $this->mid;
        $type_id = intval($_REQUEST['type_id']);
        if ($type_id < 1){
            $this->error('ID错误');
        }
        $is_check_in = D('CheckIn')->isCheckIn($user_id);
        if ($is_check_in){
            $this->ajaxReturn('','您已经签到过了',0);
        }

        M()->startTrans();
        $res = D('CheckIn')->checkIn($user_id);
        $sum_res = D('CheckInTotal')->addToCheckInTotal($user_id,$type_id);

        if ($res && $sum_res){
            M()->commit();
            $this->ajaxReturn($sum_res,'签到成功',1);
        }else{
            M()->rollback();
            $this->ajaxReturn('','系统错误',0);
        }
    }
}