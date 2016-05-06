<?php

class SchoolAction extends SchoolBaseAction {

    public function _initialize() {
        parent::_initialize();
        // 登录验证
        $passport = service('Passport');
        if (!$passport->isLogged()) {
            redirect(U('w3g/SchoolPublic/login'));
        }
    }

    public function index() {
        $this->kc();
    }

    public function kc() {
        if ($_GET['cid'] == 'all') {
            unset($_SESSION['lesson_searchCat']['cid']);
        } elseif ($_GET['cid']) {
            $_SESSION['lesson_searchCat']['cid'] = intval($_GET['cid']);
        }
        if ($_GET['cat'] == 'all') {
            unset($_SESSION['lesson_searchCat']['cat']);
        } elseif ($_GET['cat']) {
            $_SESSION['lesson_searchCat']['cat'] = t($_GET['cat']);
        }
        $map['isDel'] = 0;
        $map['status'] = 2;
        $map['sid'] = $this->sid;
        $searchTitle = t($_POST['sousuo']);
        if ($searchTitle) {
            if (mb_strlen($searchTitle, 'utf8') < 2) {
                $this->redirect(U('w3g/School/kc'), 3, '至少输入两个字');
            }
            $map['title'] = array('like', "%" . $searchTitle . "%");
            $this->assign('searchTitle', $searchTitle);
            $this->setTitle('搜索');
        }
        if ($_SESSION['lesson_searchCat']['cid']) {
            $map['typeId'] = $_SESSION['lesson_searchCat']['cid'];
        }

        switch ($_SESSION['lesson_searchCat']['cat']) {
            case 'join':
                $map_join['uid'] = $this->mid;
                $courseIds = M('course_user')->field('courseId')->where($map_join)->findAll();
                foreach ($courseIds as $v) {
                    $in_arr[] = $v['courseId'];
                }
                $map['id'] = array('in', $in_arr);
                break;
            default:
                //$map['deadline'] = array('gt',time());
                $map['limitCount'] = array('gt',0);

        }

        $daoCourse = D('Course', 'event');
        $result = $daoCourse->getCourseList($map);
        $this->assign($result);
        $type_list = D('CourseType', 'event')->getType();
        $this->assign('type', $type_list);
        $this->display('kc');
    }

    public function hd() {
        if ($_GET['cid'] == 'all') {
            unset($_SESSION['lessonActive_searchCat']['cid']);
        } elseif ($_GET['cid']) {
            $_SESSION['lessonActive_searchCat']['cid'] = intval($_GET['cid']);
        }
        if ($_GET['cat'] == 'all') {
            unset($_SESSION['lessonActive_searchCat']['cat']);
        } elseif ($_GET['cat']) {
            $_SESSION['lessonActive_searchCat']['cat'] = t($_GET['cat']);
        }
        $map['isDel'] = 0;
        $map['status'] = 2;
        $map['sid'] = $this->sid;
        $searchTitle = t($_POST['sousuo']);
        if ($searchTitle) {
            if (mb_strlen($searchTitle, 'utf8') < 2) {
                $this->redirect(U('w3g/School/kc'), 3, '至少输入两个字');
            }
            $map['title'] = array('like', "%" . $searchTitle . "%");
            $this->assign('searchTitle', $searchTitle);
            $this->setTitle('搜索');
        }
        if ($_SESSION['lessonActive_searchCat']['cid']) {
            $map['typeId'] = $_SESSION['lessonActive_searchCat']['cid'];
        }

        switch ($_SESSION['lessonActive_searchCat']['cat']) {
            case 'join':
                $map_join['uid'] = $this->mid;
                $courseIds = M('course_active_user')->field('courseId')->where($map_join)->findAll();
                foreach ($courseIds as $v) {
                    $in_arr[] = $v['courseId'];
                }
                $map['id'] = array('in', $in_arr);
                break;
            default:
                //$map['deadline'] = array('gt',time());
                $map['limitCount'] = array('gt',0);

        }

        $daoCourse = D('CourseActive', 'event');
        $result = $daoCourse->getCourseActiveList($map);
        $this->assign($result);
        $type_list = D('CourseType', 'event')->getType();
        $this->assign('type', $type_list);
        $this->display();
    }

    Private function _getCourse($id) {
        $map['id'] = $id;
        $map['isDel'] = 0;
        $result = M('course')->where($map)->find();
        if ($result) {
            if ($result['sid'] != $this->sid) {
                $this->redirect(U('w3g/School/kc'), 3, '课程不存在');
            }
            if ($result['status'] == 1) {
                $this->redirect(U('w3g/School/kc'), 3, '课程正在审核中');
            } elseif ($result['status'] == 0) {
                $this->redirect(U('w3g/School/kc'), 3, '课程未通过审核');
            }
            $this->assign($result);
        } else {
            $this->redirect(U('w3g/School/kc'), 3, '课程不存在或，未通过审核或，已删除');
        }
        return $result;
    }

    Private function _getHd($id) {
        $map['id'] = $id;
        $map['isDel'] = 0;
        $result = M('course_active')->where($map)->find();
        if ($result) {
            if ($result['sid'] != $this->sid) {
                $this->redirect(U('w3g/School/hd'), 3, '课程活动不存在');
            }
            if ($result['status'] == 1) {
                $this->redirect(U('w3g/School/hd'), 3, '课程活动正在审核中');
            } elseif ($result['status'] == 0) {
                $this->redirect(U('w3g/School/hd'), 3, '课程活动未通过审核');
            }
            $this->assign($result);
        } else {
            $this->redirect(U('w3g/School/hd'), 3, '课程活动不存在或，未通过审核或，已删除');
        }
        return $result;
    }

    public function kcDetail() {
        $id = intval($_REQUEST['id']);
        $this->_getCourse($id);
        $joined = 0;
        if($this->smid){
            $mapuser['uid'] = $this->smid;
            $mapuser['courseId'] = $id;
            //检查是否已经加入
            $res = M('course_user')->where($mapuser)->find();
            $joined = $res ? 1 : 0;
        }
        $this->assign('joined', $joined);
        // 活动分类
        $cate = D('CourseType', 'event')->getType();
        $this->assign('category', $cate);
        $this->display();
    }

    public function hdDetail() {
        $id = intval($_REQUEST['id']);
        $this->_getHd($id);
        $joined = 0;
        if($this->smid){
            $mapuser['uid'] = $this->smid;
            $mapuser['courseId'] = $id;
            //检查是否已经加入
            $res = M('course_active_user')->where($mapuser)->find();
            $joined = $res ? 1 : 0;
        }
        $this->assign('joined', $joined);
        // 活动分类
        $cate = D('CourseType', 'event')->getType();
        $this->assign('category', $cate);
        $this->display();
    }

    public function join() {
        $id = intval($_REQUEST['id']);
        if(!$id){
            $this->redirect(U('w3g/School/kc'), 3, '参数错误');
        }
        $kcUrl = U('w3g/School/kcDetail',array('id'=>$id));
        if (!$this->smid) {
            $this->redirect($kcUrl, 3, '您不是该校用户');
        }
        $course = $this->_getCourse($id);
        if ($course['limitCount'] < 1) {
            $this->redirect($kcUrl, 3, '人数已满，不能再参加');
        }
        $data['id'] = $id;
        $data['uid'] = $this->smid;
        $data['realname'] = $this->user['realname'];
        $data['sex'] = $this->user['sex'];
        $data['studentId'] = getUserEmailNum($this->user['email']);
        $data['sid'] = $this->user['sid'];
        if ($course['need_tel']) {
            $data['tel'] = $this->user['mobile'];
        }

        $result = D('Course','event')->doAddUser($data);
        $this->redirect($kcUrl, 3, $result['info']);
    }

    public function joinHd() {
        $id = intval($_REQUEST['id']);
        if(!$id){
            $this->redirect(U('w3g/School/hd'), 3, '参数错误');
        }
        $kcUrl = U('w3g/School/hdDetail',array('id'=>$id));
        if (!$this->smid) {
            $this->redirect($kcUrl, 3, '您不是该校用户');
        }
        $course = $this->_getHd($id);
        if ($course['limitCount'] < 1) {
            $this->redirect($kcUrl, 3, '人数已满，不能再参加');
        }
        $data['id'] = $id;
        $data['uid'] = $this->smid;
        $data['realname'] = $this->user['realname'];
        $data['sex'] = $this->user['sex'];
        $data['studentId'] = getUserEmailNum($this->user['email']);
        $data['sid'] = $this->user['sid'];
        if ($course['need_tel']) {
            $data['tel'] = $this->user['mobile'];
        }

        $result = D('CourseActive','event')->doAddUser($data);
        $this->redirect($kcUrl, 3, $result['info']);
    }

}