<?php

/*
 * 承办活动申请模型
 *
 */

class FundApplyeventModel extends Model {

    public function applyEventList($map, $limit, $order = 'id DESC') {
        $res = $this->where($map)->field('id,eventId,gid,sid,uid,cTime,attachId,amount,state')->order($order)->findPage($limit);

        if ($res) {
            foreach ($res['data'] as &$val) {
                $val['eventName'] = M('FundEvent')->getField('eventName', 'eventId=' . $val['eventId']);
                $val['sid'] = tsGetSchoolName($val['sid']);
                $val['gid'] = M('Group')->getField('name', 'id=' . $val['gid']);
                $user = D('User', 'home')->getUserByIdentifier($val['uid'], 'uid');
                $val['uname'] = $user['uname'];
                $attach = getAttach($val['attachId']);
                $file = $attach['savepath'] . $attach['savename'];
                if ($val['attachId']) {
                    $val['attachId'] = './data/uploads/' . $file;
                }
                $val['count'] = $this->groupCount($val['eventId']);
            }
            return $res;
        } else {
            return false;
        }
    }
    //承办列表
    public function activityApply($map=array(), $order = 'id DESC') {
        $res = $this->table("ts_fund_applyevent as a")
                ->join("ts_fund_event as b on a.eventId=b.eventId")
                ->where($map)
                ->field('a.eventId,a.gid,a.sid,b.eventName,b.company,count(1) as groups,b.byTime,max(state) as state')
                ->group('a.eventId,a.sid')->order($order)
                ->findPage(10);
        foreach ($res['data'] as &$v) {
            $eid = $v['eventId'];
            $sid = $v['sid'];
            $audited = $this->where("eventId=$eid and sid=$sid and state=1")->field('gid,eid')->find();
            if($audited){
                $v['group'] = M('Group')->getField('name', 'id=' . $audited['gid']);
                $v['eid'] = $audited['eid'];
            }
        }
        return $res;
    }
    //完结活动
    public function doFinish($applyId,$amount3){
        $apply = $this->where('id='.$applyId)->field('eid,finished')->find();
        if(!$apply){
            $this->error = '活动申请不存在';
            return false;
        }
        if($apply['finished']!=0){
            $this->error = '活动已完结';
            return false;
        }
        $amount3 = $amount3*100/100;
        $data['amount3'] = $amount3;
        $data['finished'] = 1;
        $this->where('id='.$applyId)->save($data);
        unset($data);
        $data['school_audit'] = 5;
        $data['fTime'] = time();
        $data['isTicket'] = 0;
        M('Event')->where('id='.$apply['eid'])->save($data);
        //诚信度
        M('event_cron')->add(array('event_id'=>$apply['eid']));
        return true;
    }
    /* 通过审核
     * $id 申请id
     */
    public function through($id,$amount2,$mid) {
        $amount2 = $amount2*100/100;
        if(!$amount2){
            $this->error = '核准金额为空';
            return false;
        }
        $apply = $this->where("id=$id")->field('eventId,sid,uid,state')->find();
        if(!$apply){
            $this->error = '申请不存在';
            return false;
        }
        if($apply['state']!=0){
            $this->error = '申请已被他人审核完毕';
            return false;
        }
        $eid = $apply['eventId'];
        $sid = $apply['sid'];
        $hasSelfSid = $this->where("eventId=$eid and sid=$sid and state=1")->count();
        if($hasSelfSid>0){
            $reason = '该活动已被本校其它部落承办成功，申请自动被驳回';
            $this->error = $reason;
            $this->reject($id, $reason);
            return false;
        }
        $data['state'] = 1;
        $data['amount2'] = $amount2;
        $res = $this->where("id=$id")->save($data);
        if (!$res) {
            $this->error = '操作失败，请重试';
            return false;
        }
        $merge = $this->merge_event($id, $mid);
        if(!$merge){
            $data['state'] = 0;
            $this->where("id=$id")->save($data);
            $this->error = '操作失败，请重试';
            return false;
        }
        $reason = '该活动已被本校其它部落承办成功，申请自动被驳回';
        $selfSid = $this->where("eventId=$eid and sid=$sid and state=0")->field('id')->findAll();
        $rejectIds = getSubByKey($selfSid, 'id');
        $this->reject($rejectIds, $reason);
        return true;
    }

    /**
     * 驳回申请
     * $id 申请id
     * $reason 驳回理由
     * * */
    public function reject($id, $reason) {
        if(!is_array($id)){
            $id = explode(',', $id);
        }
        $map['id'] = array('in',$id);
        $data['state'] = -1;
        $data['rejectReason'] = $reason;
        $res = $this->where($map)->save($data);
        if ($res) {
            /* 通知发放操作 */
            $info = $this->where($map)->field('eventId,uid')->findAll();
            $uids = getSubByKey($info, 'uid');
            $eventName = M('FundEvent')->getField('eventName', 'eventId=' . $info[0]['eventId']);
            $notify_dao = service('Notify');
            $notify_data['title'] = $eventName;
            $notify_data['reason'] = $reason;
            $notify_dao->sendIn($uids, 'fund_rejectApplyEvent', $notify_data);
            return true;
        } else {
            $this->error = '操作失败，请重试';
            return false;
        }
    }

    /**
     * 将fund_applyevent中的活动添加到event中 口袋大学活动
     * $id fund_applyevent活动id
     * ** */
    public function merge_event($id, $uid) {
        //申请活动数据信息
        $info = $this->where("id=$id")->field('eventId,uid,gid,sid')->find();
        $event = M('FundEvent')->where('eventId=' . $info['eventId'])->field('eventName,logo,cTime,endTime')->find();
        //组装数据
        $data['uid'] = $info['uid'];
        $data['gid'] = $info['gid'];
        $data['audit_uid'] = $uid;
        $data['audit_uid2'] = $uid;
        $data['title'] .= $event['eventName'].'('.  tsGetSchoolName($info['sid']).')';
        $data['coverId'] = $event['logo'];
        $data['contact'] = '';
        $data['typeId'] = 11;
        $data['default_banner'] = 1;
        $data['sTime'] = $event['cTime'];
        $data['eTime'] = $event['endTime'];
        $data['startline'] = 0;
        $data['deadline'] = $event['cTime'];
        $data['cTime'] = time();
        $data['rTime'] = 0;
        $data['description'] = '';
        $data['joinCount'] = 1;
        $data['limitCount'] = 6000000;
        $data['is_school_event'] = 473;
        $data['status'] = 1;
        $data['school_audit'] = 2;
        $data['show_in_xyh'] = 2;
        $data['puRecomm'] = 0;
        $res = M('Event')->add($data);
        unset($data);
        if ($res) {
            $this->setField('eid',$res,"id=$id");
            $data['eventId'] = $res;
            $data['sid'] = $info['sid'];
            M('event_school2')->add($data);
            //将发起人默认参加 和签到
            $autor = M('user')->where('uid='.$info['uid'])->field('realname,mobile,sex,sid')->find();
            $euData['eventId'] = $res;
            $euData['uid'] = $info['uid'];
            $euData['realname'] = $autor['realname'];
            $euData['sex'] =  $autor['sex'];
            $euData['tel'] =  $autor['mobile'];
            $euData['usid'] =  $autor['sid'];
            $euData['cTime'] =  time();
            $euData['status']=2;
            M('event_user')->add($euData);
            //todo部落活跃度
            return true;
        } else {
            return false;
        }
    }

    /**
     * 某部落承办部落数统计
     */
    public function groupCount($eventId) {
        $map['eventId'] = $eventId;
        $map['state'] = array('neq', 2);
        $num = $this->where($map)->count();
        return $num;
    }

    public function doApply($mid, $input) {
        //参数合法性检查
        $required_field = array(
            'contact' => '联系人',
            'telephone' => '电话',
            'alipayAccount' => '支付宝账号',
            'amount' => '承办金额',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k])) {
                $this->error = $v . '不可为空';
                return false;
            }
        }
        $amount = t($input['amount']) * 100 / 100;
        if (!$amount) {
            $this->error = '请输入承办金额';
            return false;
        }
        $eventId = intval($input['eventId']);
        if ($eventId<=0) {
            $this->error = '活动选择出错请重新操作';
            return false;
        }
        $gid = intval($input['gid']);
        if ($gid<=0) {
            $this->error = '请选择承办部落';
            return false;
        }
        $map['eventId'] = $eventId;
        $map['gid'] = $gid;
        $map['state'] = array('egt',0);
        if (M('FundApplyevent')->where($map)->field('id')->find()) {
            $this->error = '该部落已经申请此活动';
            return false;
        }
        unset($map);
        $fundEvent = M('FundEvent')->where("eventId=$eventId")->field('byTime')->find();
        if (!$fundEvent) {
            $this->error = '该活动不存在';
            return false;
        }
        //判断活动是否过期
        if ($fundEvent['byTime'] < time()) {
            $this->error = '该活动承办已截止';
            return false;
        }

        //判断用户是否有发起活动权限 can_add_event
        $canGroupEvent = M('event_group')->where("uid=$mid and gid=$gid")->field('gid')->find();
        if (!$canGroupEvent) {
            $this->error = '您没有该部落活动权限';
            return false;
        }

        $data['contact'] = t($input['contact']);
        $data['telephone'] = t($input['telephone']);
        $data['qq'] = $input['qq'] ? t($input['qq']) : '';
        $data['alipayAccount'] = t($input['alipayAccount']);
        $data['gid'] = intval($input['gid']);
        $data['amount'] = $amount;
        $data['eventId'] = $eventId;
        $data['uid'] = $mid;
        $data['sid'] = getUserField($mid, 'sid');
        $data['cTime'] = time();
        if (!empty($_FILES['pic']['name'])) {
            $info = X('Xattach')->upload('fund');
            if ($info['status']) {
                //附件Id
                $attachId = $info['info'][0]['id'];
                $data['attachId'] = $attachId;
            }
        }
        $res = $this->add($data);
        if ($res) {
            return true;
        } else {
            $this->error = '操作失败';
            return false;
        }
    }
    public function myEvent($mid,$page,$count){
        $offset = ($page - 1) * $count;
        $map['uid'] = $mid;
        $res = $this->where($map)->field('eventId,gid,state,rejectReason')->order('id desc')->limit("$offset,$count")->findAll();
        foreach($res as &$v){
            $v['title'] = '活动已删除';
            $title = M('FundEvent')->getField('eventName', 'eventId='.$v['eventId']);
            if($title){
                $v['title'] = $title;
            }
            $v['groupName'] = '';
            $groupName = M('group')->getField('name', 'id='.$v['gid']);
            if($groupName){
                $v['groupName'] = $groupName;
            }
        }
        return $res;
    }

}

?>
