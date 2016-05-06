<?php

/**
 * AdminNewsAction
 * 新闻管理
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class AdminNewsAction extends TeacherAction {

    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        if (!$this->rights['can_prov_news'] || !isTuanRole($this->sid)) {
            $this->assign('jumpUrl', U('event/Readme/index'));
            $this->error('您没有权限');
        }
    }

    public function index() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['es_news_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_news_search']);
        } else {
            unset($_SESSION['es_news_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['title'] = array('like', "%" . $_POST['title'] . "%");
        $this->assign($_POST);
        $map['isDel'] = 0;
        $map['sid'] = $this->school['id'];
        $res = M('school_news')->where($map)->order('id DESC')->findPage(10);
        $this->assign($res);
        $this->display('list');
    }

    public function addNews() {
        $this->assign('type', 'add');
        $this->display('editNews');
    }

    public function doAddNews() {
        //参数合法性检查
        $required_field = array(
            'title' => '新闻标题',
            'content' => '新闻正文',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $data['title'] = t($_POST['title']);
        $data['content'] = t(h($_POST['content']));
        $data['cTime'] = time();
        $data['uid'] = $this->mid;
        $data['sid'] = $this->school['id'];

        $uid = M('school_news')->add($data);
        if (!$uid) {
            $this->error('抱歉：添加失败，请稍后重试');
            exit;
        }
        $this->assign('jumpUrl', U('event/AdminNews/index'));
        $this->success('添加成功');
    }

    public function editNews() {
        $_GET['id'] = intval($_GET['id']);
        if ($_GET['id'] <= 0)
            $this->error('参数错误');
        $map['sid'] = $this->school['id'];
        $map['id'] = $_GET['id'];
        $news = M('school_news')->where($map)->find();
        if (!$news)
            $this->error('无此新闻');
        $this->assign($news);
        $this->assign('type', 'edit');
        $this->display();
    }

    public function doEditNews() {
        $id = intval($_POST['id']);
        if (!$obj = M('school_news')->where(array('id' => $id))->find()) {
            $this->error('新闻不存在或已删除');
        }
        //参数合法性检查
        $required_field = array(
            'title' => '新闻标题',
            'content' => '新闻正文',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $data['title'] = t($_POST['title']);
        $data['content'] = t(h($_POST['content']));
        $data['uTime'] = time();

        $uid = M('school_news')->where('id = ' . $id)->save($data);
        if (!$uid) {
            $this->error('抱歉：修改失败，请稍后重试');
            exit;
        }
        $this->success('修改成功');
    }

    public function doDeleteNews() {
        $map['id'] = array('in', explode(',', $_REQUEST['id']));    //要删除的id.
        $map['sid'] = $this->school['id'];
        if (empty($map)) {
            echo -1;
        }
        $result = M('school_news')->where($map)->setField('isDel', 1);
        if (false != $result) {
            if (!strpos($_REQUEST['id'], ",")) {
                echo 2;            //说明只是删除一个
            } else {
                echo 1;            //删除多个
            }
        } else {
            echo -1;               //删除失败
        }
    }
}
