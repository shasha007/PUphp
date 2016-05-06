<?php

class GroupTopicAction extends GroupBaseAction {

    var $topic;
    var $post;
    var $member;

    public function _initialize() {
        parent::_initialize();
if($this->sid==402 && ACTION_NAME!='joinGroup'&& ACTION_NAME!='quitGroup'&& ACTION_NAME!='quitGroupDialog' ){
    redirect(U('event/GroupDir/index',array('gid'=>$this->gid)));
}
        // 判断功能是否开启
        if (!$this->groupinfo['openBlog']) {
            $this->error('帖子功能已关闭');
        }

        $this->topic = D('GroupTopic');
        $this->post = D('GroupPost');
        $this->member = D('GroupMember');

        // 权限判读
        if (in_array(ACTION_NAME, array('top', 'untop','del'))) {
            // 置顶，删除，帖
            if (!$this->isadmin) {
                $this->error('你没有权限');
            }
        } else if (in_array(ACTION_NAME, array('add', 'doAdd', 'edit', 'post', 'editPost'))) {
                if (!$this->smid) {
                $this->error('请先登录');
            }
            // 发布，回复，编辑
            if (!$this->ismember) {
                $this->error('抱歉，您不是部落成员');
            }
        }
        $this->assign('current', 'topic');
    }

    function index() {
        $search_key = $this->_getSearchKey();
        $search_key = $search_key ? " AND title LIKE '%{$search_key}%' " : '';
        $topiclist = $this->topic
                ->order('top DESC,replytime DESC')
                ->where('is_del=0  AND isEvent=0 AND isRule=0 AND gid=' . $this->gid . $search_key)
                ->findPage(10);
        $this->assign($topiclist);
        $this->setTitle("帖子 - " . $this->groupinfo['name']);
        $this->display();
    }

    // 发表话题 编辑话题
    public function add() {

//        $this->assign('category_list', $this->topic->categoryList($this->gid));
        $this->setTitle("发新帖子 - " . $this->groupinfo['name']);
        $this->display();
    }

    // 添加内容
    public function doAdd() {
        $title = getShort($_POST['title'], 30);
        if (empty($title))
            $this->error('标题不能为空');

        $this->__checkContent($_POST['content'], 10, 5000);

        $topic['attach'] = $this->_setTopicAttach(); // 附件信息
        $topic['gid'] = $this->gid;
        $topic['uid'] = $this->mid;
        $topic['name'] = getUserName($this->mid);
        $topic['title'] = h(t($title));
        $topic['cid'] = intval($_POST['cid']);
        $topic['addtime'] = time();
        $topic['replytime'] = time();

        if ($tid = D('GroupTopic')->add($topic)) {
            $post['gid'] = $this->gid;
            $post['uid'] = $this->mid;
            $post['tid'] = $tid;
            $post['content'] = t(h($_POST['content']));
            $post['istopic'] = 1;
            $post['ctime'] = time();
            $post['ip'] = get_client_ip();
            $post_id = $this->post->add($post);
            // 微博
//            $weibo_tpl_data = array('author' => getUserName($topic['uid']), 'title' => $topic['title'], 'url' => U('group/Topic/topic', array('gid' => $this->gid, 'tid' => $tid)));
//            $weibo_tpl_data = model('Template')->parseTemplate('group_post_create_weibo', array('body' => $weibo_tpl_data));
//            $weibo_data['gid'] = $this->gid;
//            $weibo_data['content'] = $weibo_tpl_data['body'];
//            D('GroupWeibo', 'group')->doSaveWeibo($this->mid, $weibo_data, 0, '', '', '');

            $this->assign('jumpUrl', U('/GroupTopic/topic', array('gid' => $this->gid, 'tid' => $tid)));
            $this->success('发布帖子成功');
        } else {
            $this->error('发帖失败');
        }
    }

    private function __checkContent($content, $mix = 5, $max = 5000) {
        $content_length = get_str_length($content, true);
        if (0 == $content_length) {
            $this->error('内容不能为空');
        } else if ($content_length < $mix) {
            $this->error('内容不能少于' . $mix . '个字');
        } else if ($content_length > $max) {
            $this->error('内容不能超过' . $max . '个字');
        }
    }

    // 编辑话题
    public function edit() {
        // 权限判读 (管理员和创建者)
        $tid = isset($_GET['tid']) ? intval($_GET['tid']) : 0;
        $thread = $this->topic->getThread($tid);

        if (empty($thread))
            $this->error('话题不存在');

        // 管理员或者帖子主人
        if (!($this->isadmin || $thread['uid'] == $this->mid))
            $this->error('无权限');

        if (isset($_POST['editsubmit']) && trim($_POST['editsubmit']) == 'do') {
            $title = h(t(getShort($_POST['title'], 30)));
            if (empty($title))
                $this->error('标题不能为空');

            $this->__checkContent($_POST['content'], 10, 5000);
            $content = t(h($_POST['content']));
            // 附件信息
            $map['attach'] = $this->_setTopicAttach($thread['attach']);

            $map['title'] = $title;
            $map['cid'] = intval($_POST['cid']);
            $map['mtime'] = time();

            $this->topic->where('id=' . $tid)->save($map);

            $this->post->setField('content', $content, 'tid=' . $tid . " AND istopic=1");

            $this->assign('jumpUrl', U('event/GroupTopic/topic', array('gid' => $this->gid, 'tid' => $tid)));
            $this->success('编辑帖子成功');
        }

        $this->assign('thread', $thread);
//        $this->assign('category_list', $this->topic->categoryList($this->gid));
        $this->setTitle("编辑帖子 - " . $this->groupinfo['name']);
        $this->display();
    }

    //置顶
    public function top() {
        $tid = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
        if ($tid == '')
            exit(json_encode(array('flag' => '0', 'msg' => 'tid错误')));

        if (strpos($tid, ',')) {
            $map['id'] = array('IN', $tid);
            $topicInfo = $this->topic->field('id,uid,title,top')->where($map)->findAll();
        } else if (is_numeric($tid)) {
            $map = "id={$tid}";
            $topicInfo = $this->topic->field('id,uid,title,top')->where($map)->find();
        }

        $result = $this->topic->setField('top', 1, $map);

        if ($result !== false) {
            //设置日志
            $this->_setOperationLog('置顶', $topicInfo);
            // 发送通知
            if (!is_array($topicInfo[0])) {
                $topicInfo[] = $topicInfo;
            }
            foreach ($topicInfo as $v) {
                if ($v['uid'] != $this->mid && !$v['top']) {
                    $notify_data = array(
                        'title' => $v['title'],
                        'gid' => $this->gid,
                        'tid' => $v['id'],
                    );
                    service('Notify')->send($v['uid'], 'event_group_topic_top', $notify_data, $this->mid);
                    D('GroupUserCount', 'group')->addCount($v['uid'], 'bbs');
                }
            }

            exit(json_encode(array('flag' => '1', 'msg' => '话题置顶成功')));
        } else {
            exit(json_encode(array('flag' => '0', 'msg' => '话题置顶失败')));
        }
    }

    public function untop() {
        $tid = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
        if ($tid == '')
            exit(json_encode(array('flag' => '0', 'msg' => 'tid错误')));

        if (strpos($tid, ',')) {
            $map['id'] = array('IN', $tid);
            $topicInfo = $this->topic->field('id,uid,title')->where($map)->findAll();
        } else if (is_numeric($tid)) {
            $map = "id={$tid}";
            $topicInfo = $this->topic->field('id,uid,title')->where($map)->find();
        }

        $result = $this->topic->setField('top', 0, $map);

        if ($result !== false) {
            //设置日志
            $this->_setOperationLog('取消置顶', $topicInfo);

            //setScore($this->mid,'group_topic_cancel_top');
            exit(json_encode(array('flag' => '1', 'msg' => '取消置顶成功')));
        } else {
            exit(json_encode(array('flag' => '0', 'msg' => '取消置顶失败')));
        }
    }

    //删除
    public function del() {
        $id = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
        if ($id == '')
            exit(json_encode(array('flag' => '0', 'msg' => 'tid错误')));

        if ($_POST['type'] == 'thread') {
            if (strpos($id, ',') && $this->isadmin) {
                $map['id'] = array('IN', $id);
                $map['gid'] = $this->gid;
                $topicInfo = $this->topic->field('id,uid,title')->where($map)->findAll();
            } else if (is_numeric($id)) {
                $map['id'] = $id;
                $map['gid'] = $this->gid;
                $topicInfo = $this->topic->field('id,uid,title')->where($map)->find();
                if (!$this->isadmin && $topicInfo['uid'] != $this->mid) {
                    $this->error('你没有权限');
                }
            } else {
                $this->error('你没有权限');
            }
//            die;
            //设置日志
            $this->_setOperationLog('删除', $topicInfo);

            $res = $this->topic->del($this->gid,$id);

            if ($_POST['ajax'] == 1) {
                if ($res === false) {
                    exit(json_encode(array('flag' => '0', 'msg' => '删除失败')));
                } else {
                    exit(json_encode(array('flag' => '1', 'msg' => '删除成功')));
                }
            } else {
                $this->redirect('event/GroupTopic/index', array('gid' => $this->gid));
            }
        } else if ($_POST['type'] == 'post') {
            $post_info = $this->post->field('uid,tid')->where('id=' . $id)->find();           //获取要删除的帖子id
            if (!$this->isadmin && $post_info['uid'] != $this->mid) {
                $this->error('你没有权限');
            }
            $this->post->del($this->gid,$id);           //删除回复
            //帖子回复数目减少1个
            $this->topic->setDec('replycount', 'id=' . $post_info['tid']);
            $this->redirect('event/GroupTopic/topic', array('gid' => $this->gid, 'tid' => $post_info['tid']));
        }
    }

    // 处理表单附件信息
    protected function _setTopicAttach($old_attach = '') {
        // 文件功能是否开启
        if ($this->groupinfo['openUploadFile']) {
            // 文件上传权限
            if ($this->groupinfo['whoUploadFile'] == 3 || ($this->groupinfo['whoUploadFile'] == 2 && $this->isadmin)) {
                // 添加附件
                if ($_POST['attach']) {
                    if (count($_POST['attach']) > 3) {
                        $this->error('附件数量不能超过3个');
                    }
                    array_map('intval', $_POST['attach']);
                    $map['id'] = array('in', $_POST['attach']);
                    D('GroupDir')->setField('is_del', 0, $map);
                }
                // 处理删除的附件的
                $old_attach = unserialize($old_attach);
                if (is_array($_POST['attach'])) {
                    $del_attach = array_diff($old_attach, $_POST['attach']);
                } else {
                    $del_attach = $old_attach;
                }
                D('GroupDir')->remove($del_attach);

                return serialize($_POST['attach']);
            } else {
                return $old_attach;
            }
        } else {
            return $old_attach;
        }
    }

    //话题回复
    public function post() {
        //权限判读
        $tid =  intval($_GET['tid']) ;
        if ($tid > 0) {
            $topic = D('GroupTopic')->field('id,uid,title,`lock`')->where("gid={$this->gid} AND id={$tid} AND is_del=0")->find();  //获取话题内容
//            var_dump(D('GroupTopic')->getLastSql());die;
            if (!$topic) {
                $this->error('帖子不存在或已被删除');
            } else if ($topic['lock'] == 1) {
                $this->error('帖子已被锁定，不可回复');
            }

            $this->__checkContent($_POST['content'], 5, 10000);

            $post['gid'] = $this->gid;
            $post['uid'] = $this->mid;
            $post['tid'] = $tid;
            $post['content'] = t(h($_POST['content']));
            $post['istopic'] = 0;
            $post['ctime'] = time();
            $post['ip'] = get_client_ip();
//            if (isset($_POST['quote'])) {  //如果引用帖子
//                $post['quote'] = isset($_POST['qid']) ? intval($_POST['qid']) : 0; //引用帖子id
//                $post_info = D('GroupPost', 'group')->field('uid,istopic,content')->where("id={$post['quote']}")->find();
//                if ($post_info['uid'] != $this->mid) {
//                     //发送通知
//                    $notify_dao = service('Notify');
//                    $notify_data = array(
//                        'post' => $post_info['istopic'] ? "的帖子“{$topic['title']}”并回复您" : "在帖子“{$topic['title']}”中的回复",
//                        'quote' => strip_tags(getShort(html_entity_decode($post_info['content']), 30, '...')),
//                        'content' => strip_tags(getShort(html_entity_decode($post['content']), 60, '...')),
//                        'gid' => $this->gid,
//                        'tid' => $topic['id'],
//                    );
//                    $notify_dao->send($post_info['uid'], 'event_group_topic_quote', $notify_data, $this->mid);
//                    D('GroupUserCount')->addCount($post_info['uid'], 'bbs');
//                }
//            }

            $result = $this->post->add($post);  //添加回复
            if ($result) {
                if ($topic['uid'] != $this->mid) {
                    // 发送通知
                    $notify_dao = service('Notify');
                    $notify_data = array(
                        'title' => $topic['title'],
                        'content' => strip_tags(getShort(html_entity_decode($post['content']), 60, '...')),
                        'gid' => $this->gid,
                        'tid' => $topic['id'],
                    );
                    $notify_dao->send($topic['uid'], 'event_group_topic_reply', $notify_data, $this->mid);
                    D('GroupUserCount', 'group')->addCount($post_info['uid'], 'bbs');
                }

                $this->topic->setField('replytime', time(), 'id=' . $tid);
                $this->topic->setInc('replycount', 'id=' . $tid);
                // 积分
                X('Credit')->setUserCredit($this->mid, 'group_reply_topic');
            }
            $this->redirect('event/GroupTopic/topic', array('gid' => $this->gid, 'tid' => $tid));
        } else {
            $this->error('帖子参数错误');
        }
    }

    //编辑话题回复
    public function editPost() {
        //权限判读 (管理员和创建者)
        $pid = isset($_REQUEST['pid']) ? intval($_REQUEST['pid']) : 0;
        $post = $this->post->where('id=' . $pid . ' AND is_del=0')->find();

        //管理员或者帖子主人
        if (!$post) {
            $this->error('帖子回复不存在');
        }
        if (!($this->isadmin || $post['uid'] == $this->mid)) {
            $this->error('无权限');
        }

        if (isset($_POST['editsubmit']) && trim($_POST['editsubmit']) == 'do') {

            $this->__checkContent($_POST['content'], 5, 10000);
            $content = t(h($_POST['content']));

            $map['attach'] = !empty($_POST['attach']) ? serialize($_POST['attach']) : '';
            $map['content'] = $content;
            $res = $this->post->where('id=' . $pid . " AND istopic=0")->save($map);
            if ($res !== false) {
                $this->assign('jumpUrl', U('event/GroupTopic/topic', array('gid' => $this->gid, 'tid' => $post['tid'])));
                $this->success('修改成功');
            } else {
                $this->error('修改失败');
            }
        }

        $this->assign('post', $post);
        $this->setTitle($this->siteTitle['edit_topic']);
        $this->setTitle("编辑帖子回复 - " . $this->groupinfo['name']);
        $this->display();
    }

    // 话题显示
    public function topic() {
        if(!$this->smid){
            $this->error('请先登录');
        }
        $tid = intval($_GET['tid']) > 0 ? $_GET['tid'] : 0;

        if ($tid == 0)
            $this->error('参数错误');
        $limit = 20;

        $this->topic->setInc('viewcount', 'id=' . $tid);
        $thread = $this->topic->getThread($tid);     //获取主题
        // 判读帖子存不存在
        if (!$thread)
            $this->error('帖子不存在');
        // 帖子的分类
        $thread['ctitle'] = M('group_topic_category')->getField('title', "id={$thread['cid']} AND gid={$this->gid}");
        $thread['ctitle'] = $thread['ctitle'] ? "[{$thread['ctitle']}]" : '';

        // 附件信息
        if ($thread['attach']) {
            $_attach_map['id'] = array('IN', unserialize($thread['attach']));
            $thread['attach'] = D('GroupDir')->field('id,name,note,is_del')->where($_attach_map)->findAll();
        }

        $postlist = $this->post->order('istopic DESC')->where('is_del = 0 AND tid=' . $tid)->findPage($limit);

        // 起始楼层计算
        $p = $_GET[C('VAR_PAGE')] ? intval($_GET[C('VAR_PAGE')]) : 1;
        $this->assign('start_floor', intval((1 == $p) ? (($p - 1) * $limit + 1) : (($p - 1) * $limit) ));

        $this->assign('topic', $thread);
        $this->assign('tid', $tid);
        $this->assign('postlist', $postlist);

        $this->assign('isCollect', D('Collect', 'group')->isCollect($tid, $this->mid));  //判断是否收藏

        $this->setTitle("{$thread['title']} - 帖子 - {$this->groupinfo['name']}");
        $this->display();
    }

    protected function _setOperationLog($operation, &$post_info) {
        //设置日
        if (!is_array($post_info[0])) {
            $post_info[] = $post_info;
        }
        foreach ($post_info as $v) {
            if (!$v['uid'] || !$v['title']) {
                continue;
            }
            $content = '把 ' . getUserSpace($v['uid'], 'fn', '_blank', '@' . getUserName($v['uid']))
                    . ' 的帖子“<a href="' . U('event/GroupTopic/topic', array('gid' => $this->gid, 'tid' => $v['id'])) . '" target="_blank">'
                    . $v['title'] . '</a>” ' . $operation;
            D('GroupLog')->writeLog($this->gid, $this->mid, $content, 'topic');
        }
    }

    // 加入部落
    public function joinGroup() {
        $this->_isValidGroup($this->gid);
        if (isset($_POST['addsubmit'])) {
            $level = 0;
            $incMemberCount = false;
            if ($this->is_invited) {
                M('group_invite_verify')->where("gid={$this->gid} AND uid={$this->mid} AND is_used=0")->save(array('is_used' => 1));
                if (0 === intval($_POST['accept'])) {
                    // 拒绝邀请
                    exit;
                } else {
                    // 接受邀请加入
                    $level = 3;
                    $incMemberCount = ture;
                }
            } else if ($this->groupinfo['need_invite'] == 1) {
                // 需要审批，发送私信到管理员
                $level = 0;
                $incMemberCount = false;
                // 添加通知
                $toUserIds = $this->member->field('uid')->where('gid=' . $this->gid . ' AND (level=1 or level=2)')->findAll();
                foreach ($toUserIds as $k => $v) {
                    $toUserIds[$k] = $v['uid'];
                }

                $message_data['title'] = "申请加入校园部落 {$this->groupinfo['name']}";
                $url = '/index.php?app=event&mod=GroupManage&act=memberManage&gid='.$this->gid.'&type=apply';
                $message_data['content'] = "你好，请求你批准加入“{$this->groupinfo['name']}” 校园部落，"
                        . '<a href="' . $url . '"> 【点此】 </a>' . '进行操作。';
                $message_data['to'] = $toUserIds;
                $res = model('Message')->postMessage($message_data, $this->mid, false);
            }

            $result = D('EventGroup')->joinGroup($this->mid, $this->gid, $level, $incMemberCount, $_POST['reason']);   //加入
            S('Cache_MyGroup_' . $this->mid, null);
            exit;
        }

        parent::base();

        $this->assign('joinCount', $this->member->where("uid={$this->mid} AND level>1")->count());
        $member_info = $this->member->field('level')->where("gid={$this->gid} AND uid={$this->mid}")->find();
        $this->assign('isjoin', $member_info['level']);  // 是否加入过或加入情况

//        $user = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
//        if (!$user['mobile']) {
//            $this->assign('needmobile', 1);
//        } else {
//            $this->assign('needmobile', 0);
//        }
            $this->assign('needmobile', 0);

        $this->display();
    }

    private function _isValidGroup($gid) {
        if ($this->user['sid'] != $this->sid) {
            echo '加入失败！您不是本校学生';die;
        }
        if ($this->groupinfo['disband'] == 1) {
            echo '加入失败！该部落已解散';die;
        }
        if ($this->groupinfo['is_del'] == 1) {
            echo '加入失败！该部落已删除';die;
        }
    }

     //退出部落对话框
    function quitGroupDialog() {
        $this->assign('gid', $this->gid);
        $this->display();
    }

    //退出部落
    function quitGroup() {
        if ($this->_iscreater($this->mid, $this->gid) || !$this->ismember) {
            echo '0';
            exit;
        } //$this->error('你没有权限'); //部落不可以退出
        $res = $this->member->where("uid={$this->mid} AND gid={$this->gid}")->delete();  //用户退出
        if ($res) {
            D('EventGroup')->setDec('membercount', 'id=' . $this->gid);     //用户数量减少1
            M('event_group')->where('gid=' . $this->gid . ' AND uid=' . $this->mid)->delete();   //删除社团活动发起权限
            $content = '退出部落';
            D('GroupLog','event')->writeLog($this->gid, $this->mid, $content, 'log_quitGroup');
            S('Cache_MyGroup_' . $this->mid, null);
            echo '1';
            exit;
        }
    }

        //判读是不是创建者
    private function _iscreater($uid, $gid) {
        return $this->member->where("uid=$uid AND gid=$gid AND level=1")->count();
    }


}

?>
