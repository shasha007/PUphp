<?php

import('home.Action.PubackAction');

class AdminAction extends PubackAction {

    public $course;

    public function _initialize() {
        parent::_initialize();
        $this->course = D('Train');
    }

    public function index() {
        if (!empty($_POST)) {
            $_SESSION['admin_train_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_train_search']);
        } else {
            unset($_SESSION['admin_train_search']);
        }

        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['title'] = array('like', "%" . $_POST['title'] . "%");
        $_POST['cat'] && $map['catId'] = intval($_POST['cat']);
        $_POST['area'] && $map['provinceId'] = intval($_POST['area']);
        $_POST['org'] && $map['orgId'] = intval($_POST['org']);
        $this->assign($_POST);
        $catList = D('TrainCat')->getAllCat();
        $orgList = D('TrainOrg')->getAllOrg();
        $areaList = D('TrainArea','train')->getAllArea();
        $this->assign('areaList', $areaList);
        $this->assign('catList', $catList);
        $this->assign('orgList', $orgList);
        $this->assign($this->course->courseList($map));
        $this->display();
    }

    //添加课程
    public function addCourse() {
        $org = D('TrainOrg')->getAllOrg();
        $cat = D('TrainCat')->getAllCat();
        $area = D('TrainArea','train')->getAllArea();
        $this->assign('area', $area);
        $this->assign('cat', $cat);
        $this->assign('org', $org);
        $this->display();
    }

    public function doAddCourse() {
        //参数合法性检查
        $required_field = array(
            'title' => '课程名称',
            'cost' => '报名费用',
            'address' => '课程地址',
            'province' => '所在省份',
            'city' => '所在城市',
            'cat' => '课程类别',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 30) {
            $this->error('课程名称最大30个字！');
        }
        $textarea = $_POST['description'];
        $map['uid'] = $this->mid;
        $map['title'] = $title;
        $map['description'] = $textarea;
        $map['provinceId'] = intval($_POST['province']);
        $map['cityId'] = intval($_POST['city']);
        $map['address'] = t($_POST['address']);
        $map['kDate'] = t($_POST['kDate']);
        $map['dauer'] = t($_POST['dauer']);
        $map['catId'] = intval($_POST['cat']);
        $map['orgId'] = intval($_POST['org']);
        $map['display_order'] = $map['orgId'];
        $map['cost'] = t($_POST['cost']);
        $map['contact'] = t($_POST['contact']);
        if ($addId = $this->course->doAddCourse($map)) {
            $this->success($this->appName . '创建成功');
        } else {
            $this->error($this->appName . '添加失败');
        }
    }

    public function editCourse() {
        $rid = intval($_REQUEST['id']);
        if ($rid <= 0) {
            $this->error("错误的访问页面，请检查链接");
        }
        $map['id'] = $rid;
        if ($result = $this->course->where($map)->find()) {
            $this->assign('course', $result);
        } else {
            $this->error('该课程不存在或已删除');
        }
        $org = D('TrainOrg')->getAllOrg();
        $cat = D('TrainCat')->getAllCat();
        $area = D('TrainArea','train')->getAllArea();
        $city = D('TrainArea','train')->getAllArea($result['provinceId']);
        $this->assign('area', $area);
        $this->assign('cat', $cat);
        $this->assign('org', $org);
        $this->assign('city', $city);
        $this->assign($result);
        $this->display();
    }

    public function doEditCourse() {
        $tid = intval($_REQUEST['tid']);
        if ($tid <= 0) {
            $this->error("错误的访问页面，请检查链接");
        }
        //参数合法性检查
        $required_field = array(
            'title' => '课程名称',
            'cost' => '报名费用',
            'address' => '课程地址',
            'province' => '所在省份',
            'city' => '所在城市',
            'cat' => '课程类别',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 30) {
            $this->error('课程名称最大30个字！');
        }
        $textarea = $_POST['description'];
        $map['uid'] = $this->mid;
        $map['title'] = $title;
        $map['description'] = $textarea;
        $map['provinceId'] = intval($_POST['province']);
        $map['cityId'] = intval($_POST['city']);
        $map['address'] = t($_POST['address']);
        $map['kDate'] = t($_POST['kDate']);
        $map['dauer'] = t($_POST['dauer']);
        $map['catId'] = intval($_POST['cat']);
        $map['orgId'] = intval($_POST['org']);
        $map['display_order'] = $map['orgId'];
        $map['cost'] = t($_POST['cost']);
        $map['contact'] = t($_POST['contact']);
        if ($addId = $this->course->doEditCourse($map, $tid)) {
            $this->success($this->appName . '修改成功');
        } else {
            $this->error($this->appName . '修改失败');
        }
    }

    private function _pDate($date) {
        $date_list = explode(' ', $date);
        list( $year, $month, $day ) = explode('-', $date_list[0]);
        list( $hour, $minute, $second ) = explode(':', $date_list[1]);
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    public function catList() {
        $catList = D('TrainCat')->getAllCat();
        $this->assign('catList', $catList);
        $this->display();
    }

    public function doEditType() {
        $dao = D('TrainCat');
        if($dao->editCat($_REQUEST)){
            $this->success('修改成功');
        }else{
            $this->error($dao->getError());
        }
    }

    public function doAddType() {
        $dao = D('TrainCat');
        if($dao->addCat($_POST)){
            $this->success('添加成功');
        }else{
            $this->error($dao->getError());
        }
    }

    public function editCourseTab() {
        $id = intval($_GET['id']);
        if ($id) {
            $name = M('train_cat')->getField('name', "id={$id}");
            $this->assign('id', $id);
            $this->assign('name', $name);
        }
        $this->display();
    }

    public function delCat() {
        $dao = D('TrainCat');
        $gid = is_array($_POST ['gid']) ? implode(',', $_POST ['gid']) : $_POST ['gid']; // 判读是不是数组
        $res = $dao->delCat($gid);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error($dao->getError());
        }
    }

    public function orgList() {
        $orgList = D('TrainOrg')->getAllOrg();
        $this->assign('orgList', $orgList);
        $this->display();
    }

    public function editOrgTab() {
        $id = intval($_GET['id']);
        if ($id) {
            $orgs = D('TrainOrg')->getAllOrg();
            $data = $orgs[$id];
            $this->assign($data);
        }
        $this->display();
    }

    public function doAddOrg() {
        $dao = D('TrainOrg');
        if($dao->addOrg($_POST)){
            $this->ajaxReturn(0, '添加成功', 1);
        }else{
            $this->ajaxReturn(0, $dao->getError(), 0);
        }
    }

    public function doEditOrg() {
        $dao = D('TrainOrg');
        if($dao->editOrg($_POST)){
            $this->ajaxReturn(0, '修改成功', 1);
        }else{
            $this->ajaxReturn(0, $dao->getError(), 0);
        }
    }

    public function delOrg() {
        $dao = D('TrainOrg');
        $gid = is_array($_POST ['gid']) ? implode(',', $_POST ['gid']) : $_POST ['gid']; // 判读是不是数组
        $res = $dao->delOrg($gid);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error($dao->getError());
        }
    }

    public function areaList() {
        $tree = M('train_area')->field("id,title as name,pid as pId")->findAll();
        $this->assign('tree', json_encode($tree));
        $this->display();
    }

    //添加省份
    public function addProvince() {
        $dao = D('TrainArea','train');
        $categoryId = $dao->addArea($_POST);
        if ($categoryId) {
            $this->success($categoryId);
        } else {
            $this->error($dao->getError());
        }
    }

    public function renameProvince() {
        $dao = D('TrainArea','train');
        $res = $dao->editArea($_POST);
        if ($res) {
            $this->success('操作成功！');
        } else {
            $this->error($dao->getError());
        }
    }

    public function changeCity() {
        $pid = intval($_POST['pid']);
        $citys = D('TrainArea','train')->getAllArea($pid);
        if ($citys) {
            echo json_encode($citys);
        }
    }

    public function put_course_to_recycle() {
        $dao = M('train');
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $map['id'] = array('IN', $gid);
        $res = $dao->where($map)->delete();
        if ($res) {
            $cMap['courseId'] = array('IN', $gid);
            M('train_collect')->where($cMap)->delete();
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
