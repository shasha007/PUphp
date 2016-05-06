<?php

class GroupMemberModel extends Model {

    function getNewMemberList($gid, $limit = 3) {
        $gid = intval($gid);
        $new_member_list = $this->field('id,uid,level,ctime')->where("gid={$gid} AND level>1")->order('id DESC')->limit($limit)->findAll();
        return $new_member_list;
    }

    function memberCount($gid) {
        return $this->where("gid=" . $gid)->count();
    }

    public function allowOver3Day($uid) {
        $time = strtotime('-3 day');
        $allow = $this->where('uid=' . $uid . ' AND  level=0  AND ctime<=' . $time)->field('id, gid')->findAll();
        $daoGroup = M('group');
        $daoCredit = X('Credit');
        $daoLog = D('GroupLog');
        foreach ($allow as $v) {
            $this->where('id=' . $v['id'])->setField('level', 3);
            $daoGroup->setInc('membercount', 'id=' . $v['gid'], 1);
            $daoCredit->setUserCredit($uid, 'join_group');
            $content = '申请超过3天，系统自动将用户 ' . getUserSpace($uid, 'fn', '_blank', '@' . getUserName($uid)) . '批准成为会员 ';
            $daoLog->writeLog($v['gid'], $uid, $content, 'member');
        }
    }

    public function isMember($mid,$gid) {
        $res = $this->where("uid=$mid AND gid=$gid")->getField('level');
        return $res ? $res : -1;
    }

       //创建者变成普通成员
    public function toNormalMember($uid, $gid) {
        $this->where('level=1 AND gid=' . $gid)->setField('level', 3);
        //删除原先部落主席活动发起权限
        $daoEventGroup = M('event_group');
        $daoEventGroup->where('uid=' . $uid . ' AND gid =' . $gid)->delete();
        $addevent = $daoEventGroup->where('uid=' . $uid)->find();
        if (!$addevent) {
            M('user')->where('uid =' . $uid)->setField('can_add_event', 0);
        }
    }

}
