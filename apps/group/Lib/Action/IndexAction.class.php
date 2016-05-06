<?php

class IndexAction extends BaseAction {

    protected $group;

    public function _initialize() {

        parent::base();
        $this->group = D('Group');
        // 社团热门排行
        $hot_group_list = $this->group->getHotListMin();
        $this->assign('hot_group_list', $hot_group_list);
    }

    // 发现社团
    public function newIndex() {
        $map = 'is_del=0 AND school=0';
        $group_list = $this->group->field('id,name,intro,logo,cid0,cid1,membercount,ctime,vStern')
                ->where($map)
                ->findPage(10);

        foreach ($group_list['data'] as &$group) {
            // 群标签
            $_tags = array();
            $tags = D('GroupTag')->getGroupTagList($group['id']);
            foreach ($tags as $tag) {
                $href = U('group/Index/search', array('k' => urlencode($tag['tag_name'])));
                $_tags[] = ($tag['tag_name'] == $search_key) ? "<a href=\"{$href}\" class=\"cRed\">{$tag['tag_name']}</a>" : "<a href=\"{$href}\">{$tag['tag_name']}</a>";
            }
            $group['tags'] = implode('<span class="cGray2"> | </span> ', $_tags);

            if ($search_key) {
                $group['name'] = preg_replace("/{$search_key}/i", "<span class=\"cRed\">\\0</span>", $group['name']);
                $group['intro'] = preg_replace("/{$search_key}/i", "<span class=\"cRed\">\\0</span>", $group['intro']);
            }
        }
        $this->assign('group_list', $group_list);
        $this->setTitle("社团首页");
        $this->display();
    }

    // 搜索社团
   public function search() {
        $search_key = $this->_getSearchKey();
        $db_prefix = C('DB_PREFIX');
        if ($search_key) {
            $tag_id = M('tag')->getField('tag_id', "tag_name='{$search_key}'");
            $map = "g.is_del=0 AND school=0 AND (g.name LIKE '%{$search_key}%' OR g.intro LIKE '%{$search_key}%'";
            if ($tag_id) {
                $map .= ' OR t.tag_id=' . $tag_id;
                $tag_id_score = "+IF(t.tag_id={$tag_id},2,0)";
            }
            $map .= ')';

            $group_count = $this->group->field('COUNT(DISTINCT(g.id)) AS count')
                    ->table("{$db_prefix}group AS g LEFT JOIN {$db_prefix}group_tag AS t ON g.id=t.gid")
                    ->where($map)
                    ->find();
            $group_list = $this->group->field('DISTINCT(g.id),g.name,g.intro,g.logo,g.cid0,g.cid1,g.membercount,g.ctime,g.vStern')
                    ->table("{$db_prefix}group AS g LEFT JOIN {$db_prefix}group_tag AS t ON g.id=t.gid")
                    ->where($map)
                    ->order("IF(LOCATE('{$search_key}',g.name),4,0)+IF(LOCATE('{$search_key}',g.intro),1,0){$tag_id_score} DESC")
                    ->findPage(10, $group_count['count']);
        } else {
            $map = 'is_del=0 AND school=0';
            $group_list = $this->group->field('id,name,intro,logo,cid0,cid1,membercount,ctime,vStern')
                    ->where($map)
                    ->findPage(10);
        }

        foreach ($group_list['data'] as &$group) {
            // 群标签
            $_tags = array();
            $tags = D('GroupTag')->getGroupTagList($group['id']);
            foreach ($tags as $tag) {
                $href = U('group/Index/search', array('k' => urlencode($tag['tag_name'])));
                $_tags[] = ($tag['tag_name'] == $search_key) ? "<a href=\"{$href}\" class=\"cRed\">{$tag['tag_name']}</a>" : "<a href=\"{$href}\">{$tag['tag_name']}</a>";
            }
            $group['tags'] = implode('<span class="cGray2"> | </span> ', $_tags);

            if ($search_key) {
                $group['name'] = preg_replace("/{$search_key}/i", "<span class=\"cRed\">\\0</span>", $group['name']);
                $group['intro'] = preg_replace("/{$search_key}/i", "<span class=\"cRed\">\\0</span>", $group['intro']);
            }
        }
        $this->assign('group_list', $group_list);
        $this->setTitle("社团搜索");
        $this->display();
    }

    //群的创建
    function add() {
        if (!$this->rights['addGroup']) {
            $this->error('您无权创建');
        }
        $this->setTitle("创建社团");
        $this->display();
    }

    //做创建操作
    public function doAdd() {
        if (!$this->rights['addGroup']) {
            $this->error('您无权创建');
        }
        if (trim($_POST['dosubmit'])) {
            //检查验证码
            if (md5($_POST['verify']) != $_SESSION['verify']) {
                $this->error('验证码错误');
            }
            $group['uid'] = $this->mid;
            $group['name'] = h(t($_POST['name']));
            $group['intro'] = h(t($_POST['intro']));
            $group['cid0'] = intval($_POST['cid0']);
            $group['need_invite'] = 0;
            $group['school'] = 0;
            if (!$group['name']) {
                $this->error('标题不能为空');
            } else if (get_str_length($_POST['name']) > 20) {
                $this->error('标题不能超过20个字');
            }
            if (D('Category')->getField('id', 'name=' . $group['name'])) {
                $this->error('请选择分类');
            }
            $introLen = get_str_length($_POST['intro']);
            if ($introLen > 60) {
                $this->error('社团简介请不要超过60个字');
            } elseif ($introLen == 0) {
                $this->error('社团简介不能为空');
            }
            $group['type'] = $_POST['type'] == 'open' ? 'open' : 'close';

            /*
              $group['need_invite'] = intval($this->config[$group['type'] . '_invite']);  //是否需要邀请
              $group['need_verify'] = intval($this->config[$group['type'] . '_review']);   //申请是否需要同意
              $group['actor_level'] = intval($this->config[$group['type'] . '_sayMember']);  //发表话题权限
              $group['brower_level'] = intval($this->config[$group['type'] . '_viewMember']); //浏览权限
             */
            //fix shetuan permission
            $group['type'] = "open";
            $group['brower_level'] = "-1";

            $group['openUploadFile'] = intval($this->config['openUploadFile']);
            $group['whoUploadFile'] = intval($this->config['whoUploadFile']);
            $group['whoDownloadFile'] = 3;
            $group['openAlbum'] = intval($this->config['openAlbum']);
            $group['whoCreateAlbum'] = intval($this->config['whoCreateAlbum']);
            $group['whoUploadPic'] = intval($this->config['whoUploadPic']);
            $group['anno'] = intval($_POST['anno']);
            $group['ctime'] = time();

            if (1 == $this->config['createAudit']) {
                $group['status'] = 0;
            }

            // 社团LOGO
            $options['userId'] = $this->mid;
            $options['max_size'] = 2 * 1024 * 1024;  //2MB
            $options['allow_exts'] = 'jpg,gif,png,jpeg,bmp';
            $info = X('Xattach')->upload('group_logo', $options);
            if ($info['status']) {
                $group['logo'] = $info['info'][0]['savepath'] . $info['info'][0]['savename'];
            } else {
                $group['logo'] = 'default.gif';
            }

            $gid = $this->group->add($group);

            if ($gid) {
                // 把自己添加到成员里面
                $this->group->joingroup($this->mid, $gid, 1, $incMemberCount = true);
                // 添加社团标签
                D('GroupTag')->setGroupTag($_POST['tags'], $gid);
                // 积分操作
                X('Credit')->setUserCredit($this->mid, 'add_group');
                S('Cache_MyGroup_' . $this->mid, null);
                if (1 == $this->config['createAudit']) {
                    $this->assign('jumpUrl', U('group/SomeOne/index', array('uid' => $this->mid, 'type' => 'manage')));
                    $this->success('创建成功，请等待审核');
                } else {
                    $this->assign('jumpUrl', U('group/Invite/create', array('gid' => $gid, 'from' => 'create')));
                    $this->success('创建成功');
                }
            } else {
                $this->error('创建失败');
            }
        } else {
            $this->error('创建失败');
        }
    }
}