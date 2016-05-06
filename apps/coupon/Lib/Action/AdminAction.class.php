<?php

import('home.Action.PubackAction');

class AdminAction extends PubackAction {

    var $category;
    var $school;
    var $coupon;

    public function _initialize() {
        parent::_initialize();
        $this->category = D('Category');
        $this->school = D('School');
        $this->coupon = D('Coupon');
    }

    /**
     * index
     * 获取配置信息
     * @access public
     * @return void
     */
    public function index() {
        $this->assign('category_tree', D('Category')->_makeTree(0));
        $this->display();
    }

    public function edit_coupon_tab() {
        $this->assign('type', 'add');
        if ($id = (int) $_REQUEST['id']) {
            $this->assign('type', 'edit');
            //优惠劵信息
            $coupon = $this->coupon->find($id);
            if (!$coupon) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("coupon", $coupon);
        }
        //分类信息
        $categorys = $this->category->__getCategory();
        $this->assign('categorys', $categorys);
        //校区信息
//        $schools = $this->school->__getCategory();
//        $this->assign('schools', $schools);

        $this->display();
    }

    /**
     * coupon_list
     * 优惠劵管理
     * @access public
     * @return void
     */
    public function coupon_list() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_coupon_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_coupon_search']);
        } else {
            unset($_SESSION['admin_coupon_search']);
        }
        $_POST['description'] = t(trim($_POST['description']));
        $_POST['description'] && $map['description'] = array('like', "%" . $_POST['description'] . "%");
        isset($_POST['is_hot']) && $_POST['is_hot'] != '' && $map['is_hot'] = intval($_POST['is_hot']);
        isset($_POST['cid']) && $_POST['cid'] != '' && $map['cid'] = intval($_POST['cid']);
        isset($_POST['sid']) && $_POST['sid'] != '' && $map['sid'] = intval($_POST['sid']);
        $this->assign($_POST);
        $map['isDel'] = 0;
        $list = $this->coupon->where($map)->order('id DESC')->findPage(10);
        $this->assign($list);
//        $categorysInfo = $this->category->__getCategory();
//        $this->assign("categorysInfo", $categorysInfo);
        $schoolsInfo = $this->school->__getCategory();
        $this->assign("schoolsInfo", $schoolsInfo);
        $this->display();
    }

    public function put_coupon_to_recycle() {
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = D('Coupon')->setField('isDel', 1, 'id IN ' . t($gid)); // 通过审核
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

    public function delete_document() {
        $map['id'] = t($_REQUEST['id']);
        $result = D('Coupon')->deleteCoupon($map['id'], $this->mid, 1);
        if ($result) {
            //删除成功
            if (!strpos($_REQUEST['id'], ",")) {
                echo 2;
                exit;         //说明只是删除一个
            } else {
                echo 1;
                exit;            //删除多个
            }
        } else {
            //删除失败
            echo "0";
            exit;
        }
    }

    public function doChangeIsHot() {
        $map['id'] = array('in', t($_REQUEST['id']));        //要优惠券id
        $act = $_REQUEST['type'];  //更改动作
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "recommend":   //推荐
                $result = $this->coupon->setField('is_hot', 1, $map);
                break;
            case "cancel":   //不推荐
                $result = $this->coupon->setField('is_hot', 0, $map);
                break;
        }
        if (false !== $result) {
            echo 1;
            exit;       //成功
        } else {
            echo -1;
            exit;      //失败
        }
    }

    //添加分类
    public function addCategory() {
        if (empty($_POST ['title'])) {
            $this->error('名称不能为空！');
        }

        $cate ['title'] = t($_POST ['title']);

        //$cate['pid']	=	$this->category->_digCate($_POST); //多级分类需要打开
        $cate ['pid'] = intval($_POST ['cid0']); //1级分类
        S('Cache_Coupon_Cate_0', null);
        S('Cache_Coupon_Cate_' . $cate ['pid'], null);
        $categoryId = $this->category->add($cate);
        if ($categoryId) {
            S('Cache_Coupon_Cate_0', null);
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
            }

            $cate ['title'] = t($_POST ['title']);

            $pid = $cate ['pid'] = intval($_POST ['cid0']); //1级分类

            S('Cache_Coupon_Cate_0', null);
            S('Cache_Coupon_Cate_' . $pid, null);
            if ($pid != 0 && !$this->category->getField('id', 'id=' . $pid)) {
                $this->error('父级分类错误！');
            } else if ($pid == $id) {
                $res = $this->category->setField('title', $cate ['title'], 'id=' . $id);
            } else {
                $res = $this->category->where("id={$id}")->save($cate);
            }

            if (false !== $res) {
                S('Cache_Coupon_Cate_0', null);
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
            S('Cache_Coupon_Cate_0', null);
            $this->success();
        } else {
            $this->error('删除失败！');
        }
    }

    public function school() {
        $this->assign('category_tree', D('School')->_makeTree(0));
        $this->display();
    }

    //添加学校
    public function addSchool() {
        if (empty($_POST ['title'])) {
            $this->error('名称不能为空！');
        }

        $cate ['title'] = t($_POST ['title']);

        //$cate['pid']	=	$this->school->_digCate($_POST); //多级学校需要打开
        $cate ['pid'] = intval($_POST ['cid0']); //1级学校
        S('Cache_Coupon_School_Cate_0', null);
        S('Cache_Coupon_School_Cate_' . $cate ['pid'], null);
        $SchoolId = $this->school->add($cate);
        if ($SchoolId) {
            S('Cache_Coupon_School_Cate_0', null);
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }

    // 修改学校
    public function editSchool() {
        if (isset($_POST ['editSubmit'])) {
            $id = intval($_POST ['id']);
            if (!$this->school->getField('id', 'id=' . $id)) {
                $this->error('学校不存在！');
            } else if (empty($_POST ['title'])) {
                $this->error('名称不能为空！');
            }

            $cate ['title'] = t($_POST ['title']);

            $pid = $cate ['pid'] = intval($_POST ['cid0']); //1级学校

            S('Cache_Coupon_School_Cate_0', null);
            S('Cache_Coupon_School_Cate_' . $pid, null);

            if ($pid != 0 && !$this->school->getField('id', 'id=' . $pid)) {
                $this->error('父级学校错误！');
            } else if ($pid == $id) {
                $res = $this->school->setField('title', $cate ['title'], 'id=' . $id);
            } else {
                $res = $this->school->where("id={$id}")->save($cate);
            }

            if (false !== $res) {
                S('Cache_Coupon_School_Cate_0', null);
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！');
            }
        }
        $id = intval($_GET ['id']);
        $School = $this->school->where("id=$id")->find();
        $this->assign('school', $School);
        $this->display();
    }

    // 删除学校
    public function delSchool() {
        $id = intval($_GET ['id']);
        if ($this->school->where('id=' . $id)->delete()) {
            $this->school->where('pid=' . $id)->delete();
            S('Cache_Coupon_School_Cate_0', null);
            $this->success();
        } else {
            $this->error('删除失败！');
        }
    }

    public function recycle() {
        $list = D('Coupon')->where('isDel=1')->order('ctime DESC')->findPage();
        $this->assign($list);
        $this->display();
    }

    public function doRecycle() {
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = D('Coupon')->setField('isDel', 0, 'id IN ' . t($gid));
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

    public function edit_coupon() {
        $id = intval($_REQUEST['id']);
        if (empty($_REQUEST['description'])) {
            $this->error('标题不能为空');
        } else if (get_str_length($_REQUEST['description']) > 20) {
            $this->error('标题不能超过20个字');
        } elseif (empty($id)) {
            $this->insert_coupon();
        } else {
            $this->update_coupon($id);
        }
    }

    public function insert_coupon() {
        $info = $this->_upload();
        if (!$info['status'])
            $this->error("上传出错! " . $info['info']);
        //保存当前数据对象
        $this->coupon->description = t($_REQUEST['description']);
        $this->coupon->content = t(h($_POST['content']));
        $this->coupon->cid = intval($_REQUEST['cid']);
        $this->coupon->sid = intval($_REQUEST['sid']);
        $this->coupon->path = $info['info'][0]['savename'];
        $this->coupon->is_hot = intval($_POST['is_hot']);
        $this->coupon->isDel = 0;
        $this->coupon->cTime = time();
        $this->coupon->uTime = time();
        if ($this->coupon->add()) {
            //成功提示
            $this->assign('jumpUrl', U('/Admin/coupon_list'));
            $this->success('添加成功！');
        } else {
            //失败提示
            $this->error('添加失败！');
        }
    }

    public function update_coupon($id) {
        $coupon = $this->coupon->find($id);
        if (!$coupon) {
            $this->error('非法参数，优惠劵不存在！');
        }
        $info = $this->_upload();
        if ($info['status']) {
            $this->coupon->path = $info['info'][0]['savename'];
        }
        //保存当前数据对象
        $this->coupon->description = t($_REQUEST['description']);
        $this->coupon->content = t(h($_POST['content']));
        $this->coupon->cid = intval($_REQUEST['cid']);
        $this->coupon->sid = intval($_REQUEST['sid']);
        $this->coupon->is_hot = intval($_POST['is_hot']);
        $this->coupon->isDel = 0;
        $this->coupon->uTime = time();
        if ($this->coupon->where("id={$id}")->save()) {
            //保存成功则刷新页面
            $this->assign('jumpUrl', U('/Admin/coupon_list'));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    protected function _upload($path) {
        //上传参数
        $options['max_size'] = '2000000';
        $options['allow_exts'] = 'jpg,gif,png,jpeg';
        $options['save_to_db'] = false;
        $options['save_path'] = UPLOAD_PATH . '/coupon/';
        //$saveName && $options['save_name'] = $saveName;
        //执行上传操作
        $info = X('Xattach')->upload('coupon', $options);
        return $info;
    }

}

?>