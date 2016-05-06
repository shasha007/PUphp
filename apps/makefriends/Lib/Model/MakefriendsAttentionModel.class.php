<?php

class MakefriendsAttentionModel extends Model {

    //是否已关注
    public function hasAttention_old($uid, $toid) {
        if ($uid == $toid) {
            return false;
        }
        $map['uid'] = $uid;
        $map['tid'] = $toid;
        if ($this->where($map)->count()) {
            return true;
        }
        return false;
    }

    public function hasAttention($uid, $fid){
       if ($uid == $fid) {
            return false;
        }
        $map['uid'] = $uid;
        $map['fid'] = $fid;
        if (M('WeiboFollow')->where($map)->count()) {
            return true;
        }
        return false;
    }

    //关注好友 uid关注人 toid被关注人
    public function doAttention($uid, $toid) {
        if ($uid <= 0 || $toid <= 0) {
            $this->error = '用户id错误';
            return false;
        }
        if ($uid == $toid) {
            $this->error = '不能关注自己';
            return false;
        }
        if ($this->hasAttention($uid, $toid)) {
            $this->error = '已经关注对方';
            return false;
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        $checkUser = $daoUser->checkAndAddUser($uid);
        if(!$checkUser){
            $this->error = '本人账号错误';
            return false;
        }
        if (!$daoUser->getUserInfo($toid)) {
            $this->error = '被关注的人不存在';
            return false;
        }
        $map['uid'] = $uid;
        $map['tid'] = $toid;
        $map['cTime'] = time();
        $result = $this->add($map);
        if (!$result) {
            $this->error = '已经关注对方';
            return false;
        }
        //贡献值操作
        $daoUser->incGx($uid, 'attention', $toid);
        //人气值操作
        $daoUser->incRq($toid, 'attention');
        return true;
    }

    //取消关注
    public function cancelAttention($uid, $toid) {
        if ($uid == $toid) {
            $this->error = '不能关注自己';
            return false;
        }
        $map['uid'] = $uid;
        $map['tid'] = $toid;
        $res = $this->where($map)->delete();
        if (!$res) {
            $this->error = '没有关注对方';
            return false;
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        //贡献值操作
        $daoUser->decGx($uid, 'attention', $toid);
        //人气值操作
        $daoUser->decRq($toid, 'attention');
        return true;
    }

    //关注列表
    public function attentionList($map, $limit = 10, $page = 1, $order = 'cTime DESC') {
        $offset = ($page - 1) * $limit;
        $field = 'uid';
        if (isset($map['uid'])) {
            $field = 'tid as uid,newPhoto';
        }
        $list = $this->where($map)->field($field)->order($order)->limit("$offset,$limit")->select();
        if (!$list) {
            return array();
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        foreach ($list as &$value) {
            $value['nickname'] = D('MakefriendsUser','makefriends')->getNickname($value['uid']);
            $value['headPhoto_middle'] = $daoUser->getHeadPhoto($value['uid'],'big');
        }
        return $list;
    }

    //TAshow 关注列表
    public function newAttentionList($map, $limit = 10, $page = 1, $order = 'ctime DESC') {
        $offset = ($page - 1) * $limit;
        $field = 'uid,type,froms';
        if (isset($map['uid'])) {
            $field = 'fid as uid,newPhoto,type,froms';
        }
        $list = M('WeiboFollow')->where($map)->field($field)->order($order)->limit("$offset,$limit")->select();;
        if (!$list) {
            return array();
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        foreach ($list as &$value) {
            $value['nickname'] = D('MakefriendsUser','makefriends')->getNickname($value['uid']);
            $value['headPhoto_middle'] = $daoUser->getHeadPhoto($value['uid'],'big');
            if(isset($map['fid'])){
             $hasAttention =$this->hasAttention($map['fid'],$value['uid']);
             $value['hasAttention'] = $hasAttention?1:0;
            }
            $users = D('User', 'home')->getUserByIdentifier($value['uid'], 'uid');
            $value['school'] = tsGetSchoolName($users['sid']);
        }
        return $list;
    }

    //新照片 （我的关注）
    public function newPhoto_old($tid){
        $map['tid'] = $tid;
        $map['newPhoto'] = 0;
        $tids = $this->where($map)->field('uid')->findAll();
        if(!$tids){
            return;
        }
        $data['newPhoto'] = 1;
        $res = $this->where($map)->save($data);
        if(!$res){
            return;
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        foreach($tids as $v){
            $uid = $v['uid'];
            $daoUser->upCache($uid, 'newPhoto', 1);
        }
    }

        public function newPhoto($tid){
        $map['fid'] = $tid;
        $map['newPhoto'] = 0;
        $tids = M('WeiboFollow')->where($map)->field('uid')->findAll();
        if(!$tids){
            return;
        }
        $data['newPhoto'] = 1;
        $res = M('WeiboFollow')->where($map)->save($data);
        if(!$res){
            return;
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        foreach($tids as $v){
            $uid = $v['uid'];
            $daoUser->upCache($uid, 'newPhoto', 1);
        }
    }

    public function readPhoto_old($uid,$tid){
        $map['uid'] = $uid;
        $map['tid'] = $tid;
        $map['newPhoto'] = 1;
        $tids = $this->where($map)->field('uid')->find();
        if(!$tids){
            return;
        }
        $data['newPhoto'] = 0;
        $res = $this->where($map)->save($data);
        if(!$res){
            return;
        }
        D('MakefriendsUser', 'makefriends')->upCache($uid, 'newPhoto', 0, 'unset');
    }

        public function readPhoto($uid,$tid){
        $map['uid'] = $uid;
        $map['fid'] = $tid;
        $map['newPhoto'] = 1;
        $tids = M('WeiboFollow')->where($map)->field('uid')->find();
        if(!$tids){
            return;
        }
        $data['newPhoto'] = 0;
        $res =  M('WeiboFollow')->where($map)->save($data);
        if(!$res){
            return;
        }
        D('MakefriendsUser', 'makefriends')->upCache($uid, 'newPhoto', 0, 'unset');
    }
}

?>