<?php

class GroupManageAction extends GroupBaseAction {

    var $group;

    protected function _initialize() {
        parent::_initialize();
        if (!$this->smid)
            $this->error('您不是该校用户');
        if (!$this->isadmin)
            $this->error('您没有权限');
    }

    //基本设置
    public function index() {
        // 部落标签
//        foreach ($this->groupinfo['tags'] as $v) {
//            $_group_tags[] = $v['tag_name'];
//        }
//        $this->assign('group_tags', implode(',', $_group_tags));
//        $this->assign('reTags', D('GroupTag')->getHotTags('recommend'));
        $this->assign('current', 'basic');
        $this->display();
    }

    //基本修改
    public function doEdit() {
        $group['intro'] = h(t($_POST['intro']));
        $_POST['email'] && $group['email'] = h(t($_POST['email']));
        $_POST['telephone'] && $group['telephone'] = h(t($_POST['telephone']));
        $_POST['contact'] && $group['contact'] = h(t($_POST['contact']));
        $group['is_init'] = 1;
        $introLen = get_str_length($group['intro']);
        if ($introLen > 300) {
            $this->error('部落简介请不要超过100个字');
        } elseif ($introLen == 0) {
            $this->error('部落简介不能为空');
        }
        if($_POST['email']){
            $bool=$this->_isValidEmail($_POST['email']);
            if(!$bool){
                  $this->error('email格式不正确');
            }
        }
        if ($_FILES['logo']['size'] > 0) {
            // 部落LOGO
            $options['userId'] = $this->mid;
            $options['max_size'] = 2 * 1024 * 1024;  //2MB
            $options['save_to_db'] = false;
            $options['allow_exts'] = 'jpg,gif,png,jpeg,bmp';
            $info = X('Xattach')->upload('group_logo', $options);
            if ($info['status']) {
                $group['logo'] = $info['info'][0]['savepath'] . $info['info'][0]['savename'];
            }else{
                $this->error($info['info']);
            }
        }

        $res = $this->group->where('id=' . $this->gid)->save($group);

        if ($res !== false) {
            D('GroupLog')->writeLog($this->gid, $this->mid, '修改部落基本信息', 'setting');
            // 更新部落标签
            D('EventGroupTag')->setGroupTag($_POST['tags'], $this->gid);
            $this->assign('jumUrl', U('event/GroupManage/index', array('gid' => $this->gid)));
            $this->success('保存成功');
        }
        $this->error('保存失败');
    }

    //检查Email地址是否合法
    private function _isValidEmail($email) {

        return preg_match("/[_a-zA-Z\d\-\.]+@[_a-zA-Z\d\-]+(\.[_a-zA-Z\d\-]+)+$/i", $email) !== 0;
    }

    //成员管理
    public function memberManage() {
        $type = (isset($_GET['type']) && in_array($_GET['type'], array('manage', 'apply'))) ? $_GET['type'] : '';
        if ('' == $type || 'apply' == $type) {
            $memberlist = D('GroupMember')->where("gid={$this->gid} AND level=0")->order('level ASC')->findPage(20);
            $type = 'apply';
        } else if ('manage' == $type || (!$memberlist['data'] && 'apply' != $_GET['type'])) {
            $search_key = $this->_getSearchKey();
            $search_key = $search_key ? " AND name LIKE '%{$search_key}%' " : '';
            $memberlist = D('GroupMember')->where("gid={$this->gid} AND level>0" . $search_key)->order('level ASC')->findPage(20);
            $type = 'manage';
        }
        foreach ($memberlist['data'] as &$member) {

            $user = D('User', 'home')->getUserByIdentifier($member['uid'], 'uid');
            $member['school1'] = tsGetSchoolName($user['sid']);
            $member['school2'] = tsGetSchoolName($user['sid1']);
            $member['mobile'] = $user['mobile'];
        }
        /*
         * 缓存当前页用户信息
         */
        $ids = getSubBeKeyArray($memberlist['data'], 'uid');
        D('User', 'home')->setUserObjectCache($ids['uid']);
        $this->assign('memberlist', $memberlist);
        $this->assign('iscreater', $this->_iscreater($this->mid, $this->gid));
        $this->assign('current', 'memberManage');
        $this->assign('type', $type);

        if ('apply' == $type) {
            $this->display('memberapply');
            exit;
        } else {
            $this->display();
        }
    }

    //判读是不是创建者
    private function _iscreater($uid, $gid) {
        return D('GroupMember')->where("uid=$uid AND gid=$gid AND level=1")->count();
    }

    //群公告
    public function announce() {
        if (isset($_POST['editsubmit'])) {
            $groupinfo['announce'] = t(getShort($_POST['announce'], 60));
            $res = $this->group->where('id=' . $this->gid)->save($groupinfo);

            if ($res !== false) {
                $log = empty($groupinfo['announce']) ? '清除公告' : "发布公告: {$groupinfo['announce']}";
                D('GroupLog')->writeLog($this->gid, $this->mid, $log, 'setting');
                $this->assign('jumUrl', U('event/GroupManage/announce', array('gid' => $this->gid)));
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
        $this->assign('current', 'announce');
        $this->display();
    }

    //操作：设置成管理员，降级成为普通会员，剔除会员，允许成为会员
    public function memberaction() {
        if (!isset($_POST['op']) || !in_array($_POST['op'], array('admin', 'normal', 'out', 'allow')))
            exit();

        switch ($_POST['op']) {
            case 'admin':  // 设置成管理员
                if (!$this->_iscreater($this->mid, $this->gid)) {
                    $this->error('创建者才有的权限');  // 创建者才可以进行此操作
                }
                $content = '将用户 ' . $this->uid . ' 提升为管理员';
                $res = D('GroupMember')->where('gid=' . $this->gid . ' AND uid=' . $this->uid . ' AND level<>1')->setField('level', 2);   //3 普通用户
                $s_data['gid'] = $this->gid;
                $s_data['uid'] = $this->uid;
                M('event_group')->add($s_data);
                M('user')->where('uid = '.$this->uid)->save(array('can_add_event'=>1));
                break;
            case 'normal':   // 降级成为普通会员
                if (!$this->_iscreater($this->mid, $this->gid)) {
                    $this->error('创建者才有的权限');  // 创建者才可以进行此操作
                }
                $content = '将用户 ' . $this->uid . ' 降为普通会员 ';
                $res = D('GroupMember')->where('gid=' . $this->gid . ' AND uid=' . $this->uid . ' AND level=2')->setField('level', 3);   //3 普通用户
                if($res){
                    M('event_group')->where('gid=' . $this->gid . ' AND uid=' . $this->uid)->delete();  //删除社团活动发起权限
                }
                break;
            case 'out':     // 剔除会员
                $content = '将用户 ' . $this->uid . ' 踢出部落';
                if ($this->_iscreater($this->mid, $this->gid)) {
                    $level = ' AND level<>1';
                } else {
                    $level = ' AND level<>1 AND level<>2';
                }
                $current_level = D('GroupMember')->getField('level', 'gid=' . $this->gid . ' AND uid=' . $this->uid . $level);
                $res = D('GroupMember')->where('gid=' . $this->gid . ' AND uid=' . $this->uid . $level)->delete();   //剔除用户
                if ($res) {
                    $notify_dao = service('Notify');
                    $notify_data = array('title' =>$this->groupinfo['name']);
                    if ($current_level) {
                    $notify_dao->sendIn($this->uid ,'event_group_member_out', $notify_data);
                        D('EventGroup')->setDec('membercount', 'id=' . $this->gid);     //用户数量减少1
                    }else{
                          $notify_dao->sendIn($this->uid ,'event_group_member_no', $notify_data);
                    }
                    M('event_group')->where('gid=' . $this->gid . ' AND uid=' . $this->uid)->delete();   //删除社团活动发起权限
                }
                break;

        }

        if ($res) {
            D('GroupLog')->writeLog($this->gid, $this->mid, $content, 'log_groupManage');
        }

        header('Location:' . $_SERVER['HTTP_REFERER']);
    }


    public function applyaction() {
        $uid = is_array($_POST ['uids']) ? implode(',', $_POST ['uids']) : $_POST ['uids']; // 判读是不是数组
        $map['gid'] = $this->gid;
        $map['uid'] = array('IN', $uid);
        $map['level'] = 0;
        if ($_POST ['op'] == 'allow') {
            $res = D('GroupMember')->where($map)->setField('level', 3);   //level级别由0 变成 3
            if ($res) {
                if (strpos($_POST ['uids'], ',')) {
                    echo 1;
                } else {
                    echo 2;
                }

                if (!is_array($uid)) {
                    $uidarr = explode(',', $uid);
                }
                foreach ($uidarr as $v) {
                    //加入到队列:加入部落群组
                    $rongyun_data['do_action']  = json_encode(array('Rongyun','joinTribeGroup'));
                    $rongyun_data['param']      = json_encode(array('userId'=>$uid,'groupId'=>$this->gid,'groupName'=>$this->groupinfo['name']));
                    $rongyun_data['create_time']= time();
                    $rongyun_data['next_time']  = time();
                    model('Scheduler')->addToRongyun($rongyun_data);
                    //系统消息
                    $notify_dao = service('Notify');
                    $notify_data = array('title' => $this->groupinfo['name'], 'group_id' => $this->gid);
                    $notify_dao->sendIn($v, 'event_group_member', $notify_data);

                    D('EventGroup')->setInc('membercount', 'id=' . $this->gid); //增加一个成员
                    $content = '将用户 ' . getUserSpace($v, 'fn', '_blank', '@' . getUserName($v)) . '批准成为会员 ';
                    D('GroupLog')->writeLog($this->gid, $this->mid, $content, 'member'); //写入日志
                }
            } else {
                echo 0;
            }
        } else if ($_POST ['op'] == 'out') {
            $res = D('GroupMember')->where($map)->delete();   //剔除用户
            if ($res) {
                if (strpos($_POST ['uids'], ',')) {
                    echo 1;
                } else {
                    echo 2;
                }
                if (!is_array($uid)) {
                    $uidarr = explode(',', $uid);
                }
                foreach ($uidarr as $v) {
                    //加入到队列:加入部落群组
                    $rongyun_data['do_action']  = json_encode(array('Rongyun','quitTribeGroup'));
                    $rongyun_data['param']      = json_encode(array('userId'=>$uid,'groupId'=>$this->gid,'groupName'=>$this->groupinfo['name']));
                    $rongyun_data['create_time']= time();
                    $rongyun_data['next_time']  = time();
                    model('Scheduler')->addToRongyun($rongyun_data);
                    $notify_dao = service('Notify');
                    $notify_data = array('title' => $this->groupinfo['name']);
                    $notify_dao->sendIn($v, 'event_group_member_no', $notify_data);
                }
            } else {
                echo 0;
            }
        }
    }

    //添加成员备注
    public function addRemark(){
        $remark =  t($_POST['val']);
        if(get_str_length($remark)>6){
             $this->error('字符长度不得超过6个字');
        }
       $map['gid'] =  $this->gid;
       $map['uid'] =  intval($_POST['uid']);

       $res = M('group_member')->where($map)->setField('remark',$remark);
       if($res){
            $this->success('备注成功');
       }else{
            $this->error('备注失败');
       }
    }

}

?>
