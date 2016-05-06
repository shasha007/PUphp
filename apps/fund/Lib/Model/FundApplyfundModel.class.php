<?php

/*
 * 基金申请模型
 *
 */

class FundApplyfundModel extends Model {

    public function fundList($map, $limit = 10, $order = 'af.id DESC') {
        $db_prefix = C('DB_PREFIX');
        $field = 'af.id,af.eventId,af.sid,e.title as eventName,e.sTime,e.eTime,e.school_audit,af.position,af.telephone,af.qq,af.alipayAccount'
                . ',af.responsibleInfo,af.range,af.eRegistration,af.eSign,af.fund,af.audltFund,af.uid,af.fund,af.audltFund,af.state,af.cTime,af.sendTime,af.loanState';
        if(isset($map['af.state']) && $map['af.state'] == -1){
            $field .= ',af.rejectReason';
        }
        $res = $this->table("{$db_prefix}fund_applyfund as af")
                ->join("{$db_prefix}event as e on e.id=af.eventId")
                ->where($map)
                ->field($field)
                ->order($order)
                ->findPage($limit);
        if ($res) {
            foreach ($res['data'] as &$v) {
                $v['schoolId'] = $v['sid'];
                $v['sid'] = tsGetSchoolName(getUserField($v['uid'], 'sid'));
                $v['uid'] = getUserField($v['uid'], 'realname');
                $v['range'] = getRange($v['range']);
            }
            return $res;
        } else {
            return false;
        }
    }

    //前台申请/审核动态
    public function frontList() {
        
    }

    /**
     * 某一条资金详情
     */
    public function oneDetail($id) {
        $map['af.id'] = $id;
        $res = $this->fundList($map);
        if ($res) {
            return $res['data'][0];
        } else {
            return false;
        }
    }

    /**
     * 财务确认发放
     * * */
    public function send($id,$uid) {
        $data['loanState'] = 1;
        $data['sendTime'] = time();
        $map['id'] = $id;
        $res = $this->where($map)->save($data);
        if ($res) {
            //流水操作
            $info = $this->oneDetail($id);
            $money = $info['audltFund'];
            D('FundFundwater')->addWater($id, $money);
            //申请基金日志
            D('FundFundlog')->addFundlog($uid,$id, 2);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 申请基金通过
     * $id id
     */
    public function through($id, $money, $uid) {
        $map2['id'] = $id;
        $apply = $this->where($map2)->field('eventId')->find();
        $event = M('event')->where('id='.$apply['eventId'])->field('status')->find();
        if(!$event['status']){
            $this->error = '该活动尚未通过学校审核！';
            return false;
        }
        $fund = D('FundSponsor','fund')->fundNow();
        $map['state'] = 1;
        $map['loanState'] = 0;
        $waiteForGive = $this->where($map)->sum('audltFund');
        $restMoney = $fund['last']-$waiteForGive;
        if($restMoney<$money){
            $this->error = '基金不够了！还剩：'.$fund['last'].' 待发放：'.$waiteForGive;
            return false;
        }
        
        $data['state'] = 1;
        $data['audltFund'] = $money;
        $res = $this->where($map2)->save($data);
        if ($res) {
            D('FundFundlog')->addFundlog($uid, $id, 1);
            return true;
        } else {
            $this->error = '操作失败';
            return false;
        }
    }

    /**
     * 申请基金驳回
     * $id id
     * $reason 原因
     */
    public function reject($id, $reason) {
        $data['state'] = -1;
        $data['rejectReason'] = $reason;
        $map['id'] = $id;
        $res = $this->where($map)->save($data);
        if ($res) {
            /* 通知发放操作 */
            $notify_dao = service('Notify');
            $info = $this->where($map)->field('eventId,uid')->find();
            $eventName = M('Event')->getField('title', 'id=' . $info['eventId']);
            $notify_data['title'] = $eventName;
            $notify_data['reason'] = $reason;
            $notify_dao->sendIn($info['uid'], 'fund_rejectApplyFund', $notify_data);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 状态
     */
    public function state($id) {
        $arr = array('-1' => '驳回', '0' => '审核中', '1' => '待发放', '3' => '已发放');
        if (isset($id)) {
            return $arr[$id];
        } else {
            return $arr;
        }
    }

    //可申请的活动列表
    public function eventList($mid) {
        $map['uid'] = $mid;
        $map['isDel'] = 0;
        $map['school_audit'] = array('neq',5);
        $events = M('Event')->where($map)->field('id,title')->order('id desc')->findAll();
        $map2['uid'] = $mid;
        $map2['state'] = array('egt',0);
        $hasApply = $this->where($map2)->field('eventId')->findAll();
        $hasIds = getSubByKey($hasApply, 'eventId');
        foreach($events as $k=>$v){
            if(in_array($v['id'], $hasIds)){
                unset($events[$k]);
            }
        }
        return $events;
    }

    //申请提交
    public function doApply($mid) {
        if (empty($_POST['position'])) {
            $this->error = '请填写职位';
            return false;
        }
        if (empty($_POST['telephone'])) {
            $this->error = '请填写电话';
            return false;
        }
        if (empty($_POST['alipayAccount'])) {
            $this->error = '请填写支付宝帐号';
            return false;
        }
        if (empty($_POST['fund'])) {
            $this->error = '请填写金额';
            return false;
        }

        //判断是否已经申请过(通过/审核中)
        $map['eventId'] = intval($_POST['eventId']);
        $map['state'] = array('egt', 0);
        if (M('FundApplyfund')->where($map)->find()) {
            $this->error = '该活动已申请,请勿重复申请';
            return false;
        }
        $data['position'] = t($_POST['position']);
        $data['telephone'] = t($_POST['telephone']);
        $data['alipayAccount'] = t($_POST['alipayAccount']);
        $data['responsibleInfo'] = t($_POST['responsibleInfo']);
        $data['fund'] = t($_POST['fund']);
        $data['qq'] = t($_POST['qq']);
        $data['range'] = t($_POST['range']);
        $data['eRegistration'] = t($_POST['eRegistration']) ? t($_POST['eRegistration']) : 0;
        $data['eSign'] = t($_POST['eSign']) ? t($_POST['eSign']) : 0;
        $data['cTime'] = time();
        $data['uid'] = $mid;
        $data['eventId'] = intval($_POST['eventId']);
        $userSid = getUserField($mid, 'sid');
        $data['sid'] = $userSid;
        $res = M('FundApplyfund')->add($data);
        if ($res) {
            return true;
        } else {
            $this->error = '操作失败';
            return false;
        }
    }
    public function myFund($mid,$page,$count){
        $offset = ($page - 1) * $count;
        $map['uid'] = $mid;
        $res = $this->where($map)->field('eventId,state,loanState,rejectReason')->order('id desc')->limit("$offset,$count")->findAll();
        foreach($res as &$v){
            $v['title'] = '活动已删除';
            $title = M('event')->getField('title', 'id='.$v['eventId']);
            if($title){
                $v['title'] = $title;
            }
        }
        return $res;
    }
}

?>
