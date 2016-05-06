<?php

class GroupMemberAction extends GroupBaseAction {

    var $member;

    public function _initialize() {
        parent::_initialize();
        $this->member = D('GroupMember');
        $this->assign('current', 'member');
        $this->setTitle("成员 - " . $this->groupinfo['name']);
    }

    //所有成员
    public function index() {
        //社团成员才能看到成员
        if(!$this->_showMembers())
            $this->error ('加入部落才可以查看成员！');
        if ($_GET['order'] == 'new') {
            $order = 'id DESC';
            $this->assign('order', $_GET['order']);
        } elseif ($_GET['order'] == 'visit') {
            $order = 'mtime DESC';
            $this->assign('order', $_GET['order']);
        } else {
            $order = 'level ASC';
            $this->assign('order', 'all');
        }
        $search_key = $this->_getSearchKey();
           $search_key = $search_key ? " AND name LIKE '%{$search_key}%' " : '';
        if ($search_key) {
             $memberInfo = $this->member->order($order)->where('gid=' . $this->gid . " AND status=1 AND level>0".$search_key)->findPage(10);
        } else {
            $memberInfo = $this->member->order($order)->where('gid=' . $this->gid . " AND status=1 AND level>0")->findPage(10);
        }

        foreach ($memberInfo['data'] as &$member) {
            $user = D('User', 'home')->getUserByIdentifier($member['uid'], 'uid');
            //$member['school1'] = tsGetSchoolName($user['sid']);
            $member['school2'] = tsGetSchoolName($user['sid1']);
//            $member['weibo'] = D('GroupWeibo')->field('weibo_id,gid,content')
//                    ->where("uid={$member['uid']} AND gid={$member['gid']} AND isdel=0")
//                    ->order('ctime DESC')
//                    ->find();
            $member['followState'] = getFollowState($this->mid, $member['uid']);
        }
        $this->assign('memberInfo', $memberInfo);
        $this->display();
    }

    private function _isGroupMember($uid, $gid){
        $user = M('group_member')->getField('id','uid='.$uid.' and gid='.$gid.' and status=1 AND level>0');
        if($user)
            return true;
        return false;
    }

    private function _showMembers(){
        if(!$this->mid)
            return false;
        if($this->user['can_admin'])
            return true;
        if($this->_isGroupMember($this->mid, $this->gid))
            return true;
        $groups = M('user_group_link')->where('uid='.$this->mid)->field('user_group_id')->findAll();
        $gids = getSubByKey($groups, 'user_group_id');
        return in_array(C('SADMIN'), $gids);
    }

}