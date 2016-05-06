<?php

import('home.Action.PubackAction');

class AdminAction extends PubackAction {

    var $category;

    public function _initialize() {
        parent::_initialize();
        $this->category = D('Category');
    }

    public function index() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_announce_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_announce_search']);
        } else {
            unset($_SESSION['admin_announce_search']);
        }
        $_POST['title'] = !empty($_POST['title']) ? t(trim($_POST['title'])) : '';
        $_POST['title'] && $map['title'] = array('like', "%" . $_POST['title'] . "%");
        isset($_POST['cid']) && $_POST['cid'] != '' && $map['cid'] = intval($_POST['cid']);
        isset($_POST['sid']) && $_POST['sid'] != '' && $map['sid'] = intval($_POST['sid']);
        $this->assign($_POST);

        $map['isDel'] = 0;
        $list = M('announce')->where($map)->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->assign('schools', $this->getSchoolCategory());
        $categorys = $this->category->__getCategory();
        $this->assign('categorys', $categorys);
        $this->display();
    }

    public function editInfo() {
        $this->assign('type', 'add');
        $pid = 1;
        if ($id = (int) $_REQUEST['id']) {
            $this->assign('type', 'edit');
            $info = M('announce')->find($id);
            if (!$info) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("obj", $info);
            $pid = $info['sid'];
        }
        $this->assign('schools', $this->getSchoolCategory());
        $this->assign('subSchool', M('school')->where('pid = '.$pid)->findAll());

        //分类信息
        $categorys = $this->category->__getCategory();
        $this->assign('categorys', $categorys);
        $this->display();
    }

    public function doEditInfo() {
        $id = intval($_REQUEST['id']);
        $title = t($_POST['title']);
        if(empty($title)){
            $this->error('标题不能为空！');
        }
        if(empty($_POST['sid'])){
            $this->error('请选择学校！');
        }
        if($_POST['cid'] == 3 && empty($_POST['sid1'])){
            $this->error('院系通知,请选择院系！');
        }
        if (empty($id)) {
            $this->insertInfo();
        } else {
            $this->updateInfo($id);
        }
    }

    public function insertInfo() {
        $dao = M('announce');
        $dao->title = t($_REQUEST['title']);
        $dao->content = t(h($_POST['content']));
        $to_id = intval($_REQUEST['sid']);
        $dao->sid = $to_id;
        $cid = intval($_REQUEST['cid']);
        $dao->cid = $cid;
        if($cid == 3){
            $to_id = intval($_REQUEST['sid1']);
            $dao->sid1 = $to_id;
        }
        $dao->uid = $this->mid;
        $dao->isDel = 0;
        $dao->cTime = time();
        $dao->uTime = time();
        if ($dao->add()) {
            model('Jpush')->addJpush(6,$to_id);
            //成功提示
            $this->assign('jumpUrl', U('/Admin/index'));
            $this->success('添加成功！');
        } else {
            //失败提示
            $this->error('添加失败！');
        }
    }

    public function updateInfo($id) {
        $dao = M('announce');
        $info = $dao->find($id);
        if (!$info) {
            $this->error('非法参数，通知不存在！');
        }
        //保存当前数据对象
        $dao->title = t($_REQUEST['title']);
        $dao->content = t(h($_POST['content']));
        $dao->sid = intval($_REQUEST['sid']);

        $cid = intval($_REQUEST['cid']);
        $dao->cid = $cid;
        if($cid == 3){
            $dao->sid1 = intval($_REQUEST['sid1']);
        }else{
            $dao->sid1 = 0;
        }

        $dao->uid = $this->mid;
        $dao->uTime = time();
        if ($dao->where("id={$id}")->save()) {
            //保存成功则刷新页面
            $this->assign('jumpUrl', U('/Admin/index'));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    public function put_info_to_recycle() {
        $dao = M('announce');
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = $dao->setField('isDel', 1, 'id IN ' . t($gid)); // 通过审核
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

    public function getSchoolCategory() {
        return model('Schools')->_makeTree(0);
    }

    public function categoryList() {
        $this->assign('category_tree', D('Category')->_makeTree(0));
        $this->display();
    }

//添加分类
    public function addCategory() {
        if (empty($_POST ['title'])) {
            $this->error('名称不能为空！');
        }
        if (mb_strlen($_POST ['title'], 'UTF8') > 6) {
            $this->error('名称最大6个字！');
        }

        $cate ['title'] = t($_POST ['title']);

        //$cate['pid']	=	$this->category->_digCate($_POST); //多级分类需要打开
        $cate ['pid'] = intval($_POST ['cid0']); //1级分类
        S('Cache_Announce_Cate_0', null);
        S('Cache_Announce_Cate_' . $cate ['pid'], null);
        $categoryId = $this->category->add($cate);
        if ($categoryId) {
            S('Cache_Announce_Cate_0', null);
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }

    // 修改分类
    public function editCategory() {
        if (isset($_POST['editSubmit'])) {
            $id = intval($_POST ['id']);
            if (!$this->category->getField('id', 'id=' . $id)) {
                $this->error('分类不存在！');
            } else if (empty($_POST ['title'])) {
                $this->error('名称不能为空！');
            } elseif (mb_strlen($_POST ['title'], 'UTF8') > 6) {
                $this->error('名称最大6个字！');
            }
            $cate ['title'] = t($_POST ['title']);

            $pid = $cate ['pid'] = intval($_POST ['cid0']); //1级分类

            S('Cache_Announce_Cate_0', null);
            S('Cache_Announce_Cate_' . $pid, null);
            if ($pid != 0 && !$this->category->getField('id', 'id=' . $pid)) {
                $this->error('父级分类错误！');
            } else if ($pid == $id) {
                $res = $this->category->setField('title', $cate ['title'], 'id=' . $id);
            } else {
                $res = $this->category->where("id={$id}")->save($cate);
            }

            if (false !== $res) {
                S('Cache_Announce_Cate_0', null);
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！');
            }
        }
        $id = intval($_GET ['id']);
        $category = $this->category->where("id=$id")->find();
        $this->assign('category', $category);
        $this->display();
    }

    // 删除分类
    public function delCategory() {
        $id = intval($_GET ['id']);
        if ($this->category->where('id=' . $id)->delete()) {
            $this->category->where('pid=' . $id)->delete();
            S('Cache_Announce_Cate_0', null);
            $this->success();
        } else {
            $this->error('删除失败！');
        }
    }

    // 分类排序
    public function editOrder() {
        $new_order = @array_flip($_POST['category_top']);
        $now_order = $this->category->field('id,display_order')->findAll();
        $res = true;
        foreach ($now_order as $v) {
            if ($new_order[$v['id']] == $v['display_order'])
                continue;
            $item['id'] = $v['id'];
            $item['display_order'] = intval($new_order[$v['id']]);
            $_res = $this->category->save($item);
            $res = ($res && $_res) ? true : false;
        }

        if ($res) {
            S('Cache_Announce_Cate_0', null);
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function notice(){
           //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_notice_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_notice_search']);
        } else {
            unset($_SESSION['admin_notice_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['title'] = array('like', "%" . $_POST['title'] . "%");
        isset($_POST['cid']) && $_POST['cid'] != '' && $map['cid'] = intval($_POST['cid']);
        $this->assign($_POST);

        $map['isDel'] = 0;
        $list = M('notice')->where($map)->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

     public function editNotice() {
        $this->assign('type', 'add');
        if ($id = (int) $_REQUEST['id']) {
            $this->assign('type', 'edit');
            $info = M('notice')->find($id);
            if (!$info) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("obj", $info);
        }
        $this->display();
    }

      public function doEditNotice() {
        $id = intval($_REQUEST['id']);
        $title = t($_POST['title']);
        $front = t($_POST['front']);
        if(empty($title)){
            $this->error('标题不能为空！');
        }
        if(empty($front)){
            $this->error('副标题不能为空！');
        }
        if (empty($id)) {
            $this->insertNotice();
        } else {
            $this->updateNotice($id);
        }

        }

    public function insertNotice() {
        $dao = M('notice');
        $dao->title = t($_REQUEST['title']);
        $dao->front = t($_REQUEST['front']);
        $dao->content = t(h($_POST['content']));
        $cid = intval($_REQUEST['cid']);
        $dao->cid = $cid;
        $dao->uid = $this->mid;
        $dao->isDel = 0;
        $dao->cTime = date('Y-m-d');
        if ($dao->add()) {
            model('Jpush')->addJpush(5,$cid);
            //成功提示
            $this->assign('jumpUrl', U('/Admin/notice'));
            $this->success('添加成功！');
        } else {
            //失败提示
            $this->error('添加失败！');
        }
    }
      public function updateNotice($id) {
        $dao = M('notice');
        $info = $dao->find($id);
        if (!$info) {
            $this->error('非法参数，公告不存在！');
        }
        //保存当前数据对象
        $dao->title = t($_REQUEST['title']);
        $dao->front = t($_REQUEST['front']);
        $dao->content = t(h($_POST['content']));
        $cid = intval($_REQUEST['cid']);
        $dao->cid = $cid;
        $dao->uid = $this->mid;
        if ($dao->where("id={$id}")->save()) {
            //保存成功则刷新页面
            $this->assign('jumpUrl', U('/Admin/notice'));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

        public function put_notice_to_recycle() {
        $dao = M('notice');
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = $dao->setField('isDel', 1, 'id IN ' . t($gid)); // 通过审核
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
}

?>