<?php

class NewComerAction extends TeacherAction {

    var $Category;

    /**
     * _initialize
     * @access public
     * @return void
     */
    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        if ($this->rights['can_admin'] != 1) {
            $this->assign('jumpUrl', U('event/Readme/index'));
            $this->error('您没有迎新权限！');
        }
    }

    /**
     * index
     * @access public
     * @return void
     */
    public function index() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_newcomer_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_newcomer_search']);
        } else {
            unset($_SESSION['admin_newcomer_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['title'] = array('like', "%" . $_POST['title'] . "%");
        isset($_POST['category0']) && $_POST['category0'] != '' && $map['category0'] = intval($_POST['category0']);
        $map['sid'] = $this->sid;
        $data = M('newcomer_document')->where($map)->order('`display_order` ASC,`document_id` DESC')->findPage(10);
        $this->assign($_POST);
        $this->assign($data);
        $this->display();
    }

    public function addDocument() {
        $this->assign('type', 'add');
        $this->assign('is_active', 1);
        $this->assign('isrecom', 1);
        $this->display('editDocument');
    }

    public function editDocument() {
        $map['document_id'] = intval($_GET['id']);
        $document = M('newcomer_document')->where($map)->find();
        if ($document['sid'] != $this->sid) {
            $this->error('该资讯不属于本校');
        }
        if (empty($document))
            $this->error('该文章不存在');
        $this->assign($document);
        $this->assign('type', 'edit');
        $this->display();
    }

    public function doEditDocument() {
        if (($_POST['document_id'] = intval($_POST['document_id'])) <= 0)
            unset($_POST['document_id']);
        if (isset($_POST['document_id'])) {
            $sid = M('newcomer_document')->getField('sid', 'document_id=' . $_POST['document_id']);
            if ($sid != $this->sid)
                $this->error('该资讯不属于本校');
        }

        // 格式化数据
        $_POST['title'] = t($_POST['title']);
        $_POST['is_active'] = intval($_POST['is_active']);
        $_POST['last_editor_id'] = $this->mid;
        $_POST['sid'] = $this->sid;
        $_POST['mtime'] = time();
        if (preg_match('/^\s*((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z]+)+(?:\:[0-9]*)?(?:\/[^\x{4e00}-\x{9fa5}\s<\'\"“”‘’]*)?)\s*$/u', strip_tags(html_entity_decode($_POST['content'], ENT_QUOTES, 'UTF-8')), $url)
                || preg_match('/^\s*((?:mailto):\/\/[a-zA-Z0-9_]+@[a-zA-Z0-9][a-zA-Z0-9\.]*[a-zA-Z0-9])\s*$/u', strip_tags(html_entity_decode($_POST['content'], ENT_QUOTES, 'UTF-8')), $url)) {
            $_POST['content'] = h($url[1]);
        } else {
            $_POST['content'] = t(h($_POST['content']));
        }
        if (!isset($_POST['document_id'])) {
            // 新建文章
            $_POST['author_id'] = $this->mid;
            $_POST['ctime'] = $_POST['mtime'];
        }

        // 数据检查
        if (empty($_POST['title']))
            $this->error('标题不能为空');

        $options['userId'] = $this->mid;
        $options['allow_exts'] = 'jpeg,gif,jpg,png';
        $options['max_size'] = 2000000;
        $info = X('Xattach')->upload('document', $options);

        if ($info['status']) {
            $_POST['icon'] = $info['info'][0]['savepath'] . $info['info'][0]['savename'];
        } else {
            //$this->error($info['info']);
        }

        // 提交
        $res = isset($_POST['document_id']) ? M('newcomer_document')->save($_POST) : M('newcomer_document')->add($_POST);
        if ($res) {
            $this->assign('jumpUrl', U('/NewComer/index'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function put_info_to_recycle() {
        $dao = M('newcomer_document');
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = $dao->where('document_id IN ' . t($gid))->delete(); // 通过审核
        if ($res) {
            if (strpos($_POST ['gid'], ',')) {
                echo 1;
            } else {
                echo 2;
            }
        } else {
            echo 0;
        }
    }

    public function logoconfig() {
        $data = M('newcomer_logo')->where('sid=' . $this->sid)->find();
        $this->assign($data);
        $this->display();
    }

    public function dologoconfig() {
        $_POST['url'] = t($_POST['url']);
        $options['userId'] = $this->mid;
        $options['allow_exts'] = 'jpeg,gif,jpg,png';
        $options['max_size'] = 2000000;
        $info = X('Xattach')->upload('document', $options);
        if ($info['status']) {
            $_POST['logo'] = $info['info'][0]['savepath'] . $info['info'][0]['savename'];
        }
        $sid = M('newcomer_logo')->getField('sid', 'sid=' . $this->sid);
        if ($sid) {
            $res = M('newcomer_logo')->where('sid=' . $this->sid)->save($_POST);
        } else {
            $_POST['sid'] = $this->sid;
            $res = M('newcomer_logo')->add($_POST);
        }
//        var_dump( M('newcomer_logo')->getLastSql());die;
        if ($res) {
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

}

?>