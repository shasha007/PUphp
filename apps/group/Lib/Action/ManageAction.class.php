<?php

class ManageAction extends BaseAction {

    var $group;

    protected function _initialize() {
        parent::_initialize();
        if (!$this->isadmin)
            $this->error('您没有权限');
        $this->group = D('Group');
        $data=M(group)->where('id='.$this->gid)->field('disband,vStatus')->find();
        $this->assign('data', $data);
        
    }

    //基本设置  修改
    public function index() {
        if (isset($_POST['editsubmit'])) {
            $group['name'] = h(t($_POST['name']));
            $group['intro'] = h(t($_POST['intro']));
            $group['cid0'] = intval($_POST['cid0']);
            $group['need_invite'] = ($_POST['need_invite'] == 1) ? intval($_POST['need_invite']) : 0;
            $group['need_invite'] = 1;

            intval($_POST['cid1']) > 0 && $group['cid1'] = intval($_POST['cid1']);

            if (!$group['name']){
                $this->error('标题不能为空');
            }else if (get_str_length($group['name']) > 20){
                $this->error('标题不能超过20个字节');
            }
            if (D('Category')->getField('id', 'name=' . $group['name'])) {
                $this->error('请选择群分类');
            }
            $introLen = get_str_length($group['intro']);
            if ($introLen > 60) {
                $this->error('社团简介请不要超过60个字');
            }elseif ($introLen==0) {
                $this->error('社团简介不能为空');
            }
            //if (!preg_replace("/[,\s]*/i", '', $_POST['tags']) || count(array_filter(explode(',', $_POST['tags']))) > 5) {
            //	$this->error('标签不能为空或者不要超过5个');
            //}

            if ($_FILES['logo']['size'] > 0) {
                // 社团LOGO
                $options['userId'] = $this->mid;
                $options['max_size'] = 2 * 1024 * 1024;  //2MB
                $info = X('Xattach')->upload('group_logo', $options);
                if ($info['status']) {
                    $group['logo'] = $info['info'][0]['savepath'] . $info['info'][0]['savename'];
                }
            }

            $res = $this->group->where('id=' . $this->gid)->save($group);

            if ($res !== false) {
                D('Log')->writeLog($this->gid, $this->mid, '修改社团基本信息', 'setting');

                // 更新社团标签
                D('GroupTag')->setGroupTag($_POST['tags'], $this->gid);
                $this->assign('jumUrl', U('group/Manage/index', array('gid' => $this->gid)));
                $this->success('保存成功');
            }
            $this->error('保存失败');
        }

        // 社团标签
        foreach ($this->groupinfo['tags'] as $v) {
            $_group_tags[] = $v['tag_name'];
        }

        $this->assign('group_tags', implode(',', $_group_tags));
        $this->assign('reTags', D('GroupTag')->getHotTags('recommend'));
        $this->assign('current', 'basic');
        $this->display();
    }

    //访问权限
    public function privacy() {
        if (!iscreater($this->mid, $this->gid)) {
            $this->error('创建者才有的权限');  //创建者才可以修改配置
        }

        if (isset($_POST['editsubmit'])) {
            //$groupinfo = $this->group->create();dump($groupinfo);exit;
            /* if(!$_POST['isInvite']) {
              $groupinfo['need_invite'] = 0;
              } */
            $groupinfo['brower_level'] = ($_POST['brower_level'] == 1) ? intval($_POST['brower_level']) : -1;
            $groupinfo['type'] = ($groupinfo['brower_level'] == 1) ? 'close' : 'open';
            $groupinfo['need_invite'] = ($_POST['need_invite'] == 1 || $_POST['need_invite'] == 2) ? intval($_POST['need_invite']) : 0;
            $groupinfo['openWeibo'] = ($_POST['openWeibo'] == 0) ? intval($_POST['openWeibo']) : 1;
            $groupinfo['openBlog'] = ($_POST['openBlog'] == 0) ? intval($_POST['openBlog']) : 1;
            $groupinfo['openUploadFile'] = ($_POST['openUploadFile'] == 0) ? intval($_POST['openUploadFile']) : 1;
            $groupinfo['whoUploadFile'] = ($_POST['whoUploadFile'] == 2) ? intval($_POST['whoUploadFile']) : 3;
            $groupinfo['whoDownloadFile'] = ($_POST['whoDownloadFile'] == 0 || $_POST['whoDownloadFile'] == 2) ? intval($_POST['whoDownloadFile']) : 3;
            $res = $this->group->where('id=' . $this->gid)->save($groupinfo);

            if ($res !== false) {
                D('Log')->writeLog($this->gid, $this->mid, '修改社团访问权限', 'setting');

                $this->assign('jumUrl', U('group/Manage/privacy', array('gid' => $this->gid)));
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
        $this->assign('current', 'privacy');
        $this->display();
    }

    //成员管理
    public function membermanage() {
        $type = (isset($_GET['type']) && in_array($_GET['type'], array('manage', 'apply'))) ? $_GET['type'] : '';
        if ('' == $type || 'apply' == $type) {
            $memberlist = D('Member')->where("gid={$this->gid} AND level=0")->order('level ASC')->findPage();
            $type = 'apply';
        }
        if ('manage' == $type || (!$memberlist['data'] && 'apply' != $_GET['type'])) {
            $memberlist = D('Member')->where("gid={$this->gid} AND level>0")->order('level ASC')->findPage();
            $type = 'manage';
        }

        foreach ($memberlist['data'] as &$member) {

            $user = D('User', 'home')->getUserByIdentifier($member['uid'], 'uid');
            //$member['school1'] = "苏州大学";
            //$member['school2'] = "信息学院";
            $member['school1'] = tsGetSchoolName($user['sid']);
            $member['school2'] = tsGetSchoolName($user['sid1']);
            //将来 年级，专业
            //$res['year'] = $user['year'];
            //$res['major'] = $user['major'];
            $member['mobile'] = $user['mobile'];
        }
        /*
         * 缓存当前页用户信息
         */
        $ids = getSubBeKeyArray($memberlist['data'], 'uid');
        D('User', 'home')->setUserObjectCache($ids['uid']);

        $this->assign('memberlist', $memberlist);
        $this->assign('iscreater', iscreater($this->mid, $this->gid));
        $this->assign('current', 'membermanage');
        $this->assign('type', $type);

        if ('apply' == $type) {
            $this->display('memberapply');
            exit;
        } else {
            $this->display();
        }
    }

    //操作：设置成管理员，降级成为普通会员，剔除会员，允许成为会员
    public function memberaction() {
        if (!isset($_POST['op']) || !in_array($_POST['op'], array('admin', 'normal', 'out', 'allow')))
            exit();

        switch ($_POST['op']) {
            case 'admin':  // 设置成管理员
                if (!iscreater($this->mid, $this->gid)) {
                    $this->error('创建者才有的权限');  // 创建者才可以进行此操作
                }
                $content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '提升为管理员 ';
                $res = D('Member')->where('gid=' . $this->gid . ' AND uid=' . $this->uid . ' AND level<>1')->setField('level', 2);   //3 普通用户
                break;
            case 'normal':   // 降级成为普通会员
                if (!iscreater($this->mid, $this->gid)) {
                    $this->error('创建者才有的权限');  // 创建者才可以进行此操作
                }
                $content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '降为普通会员 ';
                $res = D('Member')->where('gid=' . $this->gid . ' AND uid=' . $this->uid . ' AND level=2')->setField('level', 3);   //3 普通用户
                break;
            case 'out':     // 剔除会员
                $content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '剔除社团 ';
                if (iscreater($this->mid, $this->gid)) {
                    $level = ' AND level<>1';
                } else {
                    $level = ' AND level<>1 AND level<>2';
                }
                $current_level = D('Member')->getField('level', 'gid=' . $this->gid . ' AND uid=' . $this->uid . $level);
                $res = D('Member')->where('gid=' . $this->gid . ' AND uid=' . $this->uid . $level)->delete();   //剔除用户
                if ($res) {
                    D('Group')->setDec('membercount', 'id=' . $this->gid);     //用户数量减少1
                    // 被拒绝加入不扣积分
                    if (intval($current_level) > 0) {
                        X('Credit')->setUserCredit($this->uid, 'quit_group');
                    }
                }
                break;
            case 'allow':   // 批准成为会员
                $content = '将用户 ' . getUserSpace($this->uid, 'fn', '_blank', '@' . getUserName($this->uid)) . '批准成为会员 ';
                $res = D('Member')->where('gid=' . $this->gid . ' AND uid=' . $this->uid . ' AND level=0')->setField('level', 3);   //level级别由0 变成 3
                if ($res) {
                    D('Group')->setInc('membercount', 'id=' . $this->gid); //增加一个成员
                    X('Credit')->setUserCredit($this->uid, 'join_group');
                }
                break;
        }

        if ($res) {
            D('Log')->writeLog($this->gid, $this->mid, $content, 'member');
        }

        header('Location:' . $_SERVER['HTTP_REFERER']);
        //$this->redirect('/Manage/membermanage',array('gid'=>$this->gid));
    }

    //群公告
    public function announce() {
        if (isset($_POST['editsubmit'])) {
            $groupinfo['announce'] = t(getShort($_POST['announce'], 60));
            $res = $this->group->where('id=' . $this->gid)->save($groupinfo);

            if ($res !== false) {
                $log = empty($groupinfo['announce']) ? '清除公告' : "发布公告: {$groupinfo['announce']}";
                D('Log')->writeLog($this->gid, $this->mid, $log, 'setting');
                $this->assign('jumUrl', U('group/Manage/announce', array('gid' => $this->gid)));
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        }
        $this->assign('current', 'announce');
        $this->display();
    }

    //jun  校方认证
    public function validate() {
        $school = M('school');
        $etime = $school->field('etime')->find($this->user['sid']);
        $daoValidate = M('group_validate');
        $rejects = $daoValidate->where("gid=$this->gid AND status=0")->order("id DESC")->field('reject,vtime')->findAll();
        $hasValid = $daoValidate->where("gid=$this->gid AND status=1")->field('id')->find();
        $this->assign('hasValid', $hasValid);
        $this->assign('etime', $etime);
        $this->assign('rejects', $rejects);
        $this->display();
    }

    //申请校方认证
    public function addValidate() {
        if ($this->groupinfo['uid'] != $this->mid) {
            $this->error("您没有权限");
        }
        if ($this->groupinfo['vStatus'] == 1) {
            $this->error("已通过审核，请不要重复提交");
        } else {
            $sqCnt = M('group_validate')->where("gid=$this->gid AND status=1")->count();
            if ($sqCnt) {
                $this->error("申请审核中，请不要重复提交");
            }
        }
        $reason = t($_POST['reason']);
        if (mb_strlen($reason, 'utf8') <= 0 || mb_strlen($reason, 'utf8') > 255)
            $this->error("申请原因格式不对");
        if (empty($_FILES['cover']['name']))
            $this->error("申请资料不能为空");
        $images = X('Xattach')->upload('group');
        if (!$images['status']) {
            $this->error("上传文件大小或格式不对");
        }
        $map['gid'] = $this->gid;
        $map['uid'] = '';
        $map['reason'] = $reason;
        $map['cover_id'] = $images['info'][0]['id'];
        $map['reject'] = '';
        $map['atime'] = time();
        $map['vtime'] = '';
        $map['status'] = 1;
        $map['sid'] = $this->groupinfo['school'];
        $table = M('group_validate');
        $result = $table->add($map);
        if ($result) {
            $this->success("申请成功，请等待审核");
        }
    }

}