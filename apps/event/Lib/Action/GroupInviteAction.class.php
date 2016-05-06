<?php

class GroupInviteAction extends GroupBaseAction {

    protected function _initialize() {
        parent::_initialize();
        if (!$this->ismember || ($this->groupinfo['need_invite'] == 2 && !$this->isadmin)) {
            $this->error('你没有权限进行邀请');
        }
        $this->setTitle("邀请 - " . $this->groupinfo['name']);
    }

    // 部落创建的邀请
    public function create() {
        $from = isset($_GET['from']) ? t($_GET['from']) : '';
        if ($_POST['sendsubmit']) {
            $toUserIds = explode(",", $_POST["fri_ids"]);
            if (empty($toUserIds)) {
                $this->error('请选择您的好友！');
                exit();
            }
            foreach ($toUserIds as $k => $v) {
                if (!$v)
                    continue;
                if (D('Member')->where("gid={$this->gid} AND uid={$v}")->count() > 0) {
                    unset($toUserIds[$k]);
                    continue;
                }
                $inviter = D('User', 'home')->getUserByIdentifier($v);
                if (!$inviter || $this->user['sid'] != $inviter['sid']) {
                    unset($toUserIds[$k]);
                    continue;
                }
                if ($this->isadmin) {
                    $invite_verify_data['gid'] = $this->gid;
                    $invite_verify_data['uid'] = $v;
                    M('group_invite_verify')->add($invite_verify_data);
                }
            }
            if (empty($toUserIds)) {
                $this->error('部落只有本校人员才可加入，请邀请本校好友！');
                exit;
            }
            $message_data['title'] = "邀您加入部落 {$this->groupinfo['name']}";
            $url = '/index.php?app=event&mod=GroupAnnounce&act=index&gid='.$this->gid;
            $message_data['content'] = "你好，诚邀您加入“{$this->groupinfo['name']}” 部落，" . '<a href="' . $url . '"> 【去看看】 </a>';
            foreach ($toUserIds as $t_u_k => $t_u_v) {
                $message_data['to'] = $t_u_v;
                $res = model('Message')->postMessage($message_data, $this->mid,false);
                if (!$res) {
                    unset($toUserIds[$t_u_k]);
                }
            }

            if (count($toUserIds) > 0) {
                $this->success('发送邀请成功');
            } else {
                $this->error('邀请失败,您的好友可能已经加入了该部落！');
            }
            exit;
        }

        $this->assign('from', $from);
        $this->display();
    }

}