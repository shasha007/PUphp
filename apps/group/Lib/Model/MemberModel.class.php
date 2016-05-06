<?php

class MemberModel extends Model {

    var $tableName = 'group_member';

    function getNewMemberList($gid, $limit = 3) {
        $gid = intval($gid);
        $new_member_list = $this->field('id,uid,level,ctime')->where("gid={$gid} AND level>1")->order('ctime DESC')->limit($limit)->findAll();
        return $new_member_list;
    }

    function memberCount($gid) {
        return $this->where("gid=" . $gid)->count();
    }

    public function allowOver3Day($uid) {
        $time = strtotime('-3 day');
        $allow = $this->where('uid='.$uid.' AND  level=0  AND ctime<=' . $time)->field('id, gid')->findAll();
        $daoGroup = M('group');
        $daoCredit = X('Credit');
        $daoLog = D('Log', 'group');
        $daoMember = D('Member','group');
        foreach ($allow as $v) {
            $daoMember->where('id=' . $v['id'])->setField('level', 3);
            $daoGroup->setInc('membercount', 'id=' . $v['gid'], 1);
            $daoCredit->setUserCredit($uid, 'join_group');
            $content = '申请超过3天，系统自动将用户 ' . getUserSpace($uid, 'fn', '_blank', '@' . getUserName($uid)) . '批准成为会员 ';
            $daoLog->writeLog($v['gid'], $uid, $content, 'member');
        }
    }

}
