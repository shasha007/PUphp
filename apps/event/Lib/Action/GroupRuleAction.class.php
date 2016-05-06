<?php
class GroupRuleAction extends GroupBaseAction {

    var $topic;
    var $post;

    public function _initialize() {
        parent::_initialize();
        $this->topic = D('GroupTopic');
        $this->post = D('GroupPost');
           // 权限判读
        if (in_array(ACTION_NAME, array('add', 'doAdd', 'edit','del'))) {
               if (!$this->smid) {
                $this->error('请先登录');
            }
            // 删除
            if (!$this->isadmin) {
                $this->error('你没有权限');
            }
        }
        $this->assign('current', 'rule');
        $this->setTitle("规章制度 - " . $this->groupinfo['name']);
    }

    public function Index() {
        $search_key = $this->_getSearchKey();
        $search_key = $search_key ? " AND title LIKE '%{$search_key}%' " : '';
        $topiclist = $this->topic
                ->order('addtime DESC')
                ->where('is_del=0  AND isEvent=0 AND isRule=1 AND gid=' . $this->gid . $search_key)
                ->findPage(10);
//        var_dump($this->topic->getLastSql());die;
        $this->assign($topiclist);
        $this->setTitle("规章制度 - " . $this->groupinfo['name']);
        $this->display();
    }

    // 发表话题 编辑话题
    public function add() {
        $this->setTitle("发布新规章制度 - " . $this->groupinfo['name']);
        $this->display();
    }

    // 添加规章制度
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
        $topic['addtime'] = time();
        $topic['isRule'] = 1;
        $tid = D('GroupTopic')->add($topic);
        $topic['replytime'] = time();
        if ($tid) {
            $post['gid'] = $this->gid;
            $post['uid'] = $this->mid;
            $post['tid'] = $tid;
            $post['content'] = t(h($_POST['content']));
            $post['istopic'] = 1;
            $post['ctime'] = time();
            $post['ip'] = get_client_ip();
            $post_id = $this->post->add($post);
            $this->assign('jumpUrl', U('/GroupRule/index', array('gid' => $this->gid, 'tid' => $tid)));
            $this->success('发布规章制度成功');
        } else {
            $this->error('发布规章制度失败');
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

    public function rule() {
        $tid = intval($_GET['tid']) > 0 ? $_GET['tid'] : 0;
        if ($tid == 0)
            $this->error('参数错误');
        $this->topic->setInc('viewcount', 'id=' . $tid);
        $thread = $this->topic->getRule($tid);
     if (!$thread)
            $this->error('规章制度不存在');
        // 附件信息
        if ($thread['attach']) {
            $_attach_map['id'] = array('IN', unserialize($thread['attach']));
            $thread['attach'] = D('GroupDir')->field('id,name,note,is_del')->where($_attach_map)->findAll();
        }
        $this->setTitle("{$thread['title']} - 规章制度 - {$this->groupinfo['name']}");
        $this->assign('topic', $thread);
        $this->assign('tid', $tid);
        $this->display();
    }
    
 // 编辑
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

            $this->assign('jumpUrl', U('event/GroupRule/rule', array('gid' => $this->gid, 'tid' => $tid)));
            $this->success('编辑规章制度成功');
        }

        $this->assign('thread', $thread);
        $this->setTitle("编辑规章制度 - " . $this->groupinfo['name']);
        $this->display();
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

            $res = $this->topic->remove($id);

            if ($_POST['ajax'] == 1) {
                if ($res === false) {
                    exit(json_encode(array('flag' => '0', 'msg' => '删除失败')));
                } else {
                    exit(json_encode(array('flag' => '1', 'msg' => '删除成功')));
                }
            } else {
                $this->redirect('event/GroupRule/index', array('gid' => $this->gid));
            }
        } 
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
                    . ' 的规章制度“<a href="' . U('event/GroupRule/rule', array('gid' => $this->gid, 'tid' => $v['id'])) . '" target="_blank">'
                    . $v['title'] . '</a>” ' . $operation;
            D('GroupLog')->writeLog($this->gid, $this->mid, $content, 'topic');
        }
    }

}

?>
