<?php

class LessonAction extends CoursebaseAction {

    public function _initialize() {
        parent::_initialize();
    }

    public function board() {
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
        $searchTitle = t($_POST['title']);
        if ($searchTitle) {
            if (mb_strlen($searchTitle, 'utf8') < 2) {
                $this->error('至少输入两个字');
            }
            $map['title'] = array('like', "%" . $searchTitle . "%");
            $this->assign('searchTitle', $searchTitle);
            $this->setTitle('搜索' . $this->appName);
        }
        if ($_SESSION['lesson_searchCat']['cid']) {
            $map['typeId'] = $_SESSION['lesson_searchCat']['cid'];
        }

        switch ($_SESSION['lesson_searchCat']['cat']) {
            case 'launch':
                $map['status'] = array('in', '0,1,2');
                $map['uid'] = $this->mid;
                break;
            case 'join':
                $map_join['uid'] = $this->mid;
                $courseIds = M('course_user')->field('courseId')->where($map_join)->findAll();
                foreach ($courseIds as $v) {
                    $in_arr[] = $v['courseId'];
                }
                $map['id'] = array('in', $in_arr);
                break;
            default:
        }
        $daoCourse = D('Course');
        $result = $daoCourse->getCourseList($map, $this->mid);
        $type_list = D('CourseType')->getType();
        $this->assign($result);
        $this->assign('istop', $daoCourse->getTop($this->sid));
        $this->assign('type', $type_list);
        $this->display();
    }

    public function boardActive() {
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
        $title = t($_POST['title']);
        if ($_POST['title']) {
            $searchTitle = t($_POST['title']);
            if (mb_strlen($searchTitle, 'utf8') < 2) {
                $this->error('至少输入两个字');
            }
            $map['title'] = array('like', "%" . $searchTitle . "%");
            $this->assign('searchTitle', $searchTitle);
            $this->setTitle('搜索' . $this->appName);
        }
        if ($_SESSION['lessonActive_searchCat']['cid']) {
            $map['typeId'] = $_SESSION['lessonActive_searchCat']['cid'];
        }

        switch ($_SESSION['lessonActive_searchCat']['cat']) {
            case 'launch':
                $map['status'] = array('in', '0,1,2');
                $map['uid'] = $this->mid;
                break;
            case 'join':
                $map_join['uid'] = $this->mid;
                $courseIds = M('course_active_user')->field('courseId')->where($map_join)->findAll();
                foreach ($courseIds as $v) {
                    $in_arr[] = $v['courseId'];
                }
                $map['id'] = array('in', $in_arr);
                break;
            default:
        }
        $result = D('CourseActive')->getCourseActiveList($map, $this->mid);
        $type_list = D('CourseType')->getType();
        $this->assign($result);
        $this->assign('istop', D('CourseActive')->getTop($this->sid));
        $this->assign('type', $type_list);
        $this->display();
    }

    public function add() {
        $this->assign('istop', D('Course')->getTop($this->sid));
        $this->display();
    }

    public function addCourse() {
        $type_list = D('CourseType')->getType();
        $this->assign('type', $type_list);
        $this->assign('istop', D('Course')->getTop($this->sid));
        $this->display();
    }

    public function addCourseActive() {
        $type_list = D('CourseType')->getType();
        $this->assign('type', $type_list);
        $this->assign('istop', D('CourseActive')->getTop($this->sid));
        $this->display();
    }

    public function doAddCourse() {
        $this->checkHash();
        $required_field = array(
            'title' => '课程名称',
            'teacher' => '授课老师',
            'address' => '课程地点',
            'typeId' => '课程分类',
            'sTime' => '课程开始时间',
            'eTime' => '课程结束时间',
            'deadline' => '截止报名时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 20) {
            $this->error('课程名称最大20个字！');
        }
        $textarea = t($_POST['description']);
        if (mb_strlen($textarea, 'UTF8') <= 0 || mb_strlen($textarea, 'UTF8') > 200) {
            $this->error("课程简介1到200字!");
        }
        $map['deadline'] = _paramDate($_POST['deadline']);
        $map['sTime'] = _paramDate($_POST['sTime']);
        $map['eTime'] = _paramDate($_POST['eTime']);
        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于结束时间');
        }
        if (empty($_FILES['logo']['name'])) {
            $this->error('请上传课程图片');
        }

        $config = getPhotoConfig();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $cover = X('Xattach')->upload('course', $options);
        if (!$cover['status'] && $cover['info'] != '没有选择上传文件') {
            $this->error($cover['info']);
        }
        $map['uid'] = $this->mid;
        $map['sid'] = $this->sid;
        $map['title'] = $title;
        $map['credit'] = intval($_POST['credit']);
        $map['teacher'] = t($_POST['teacher']);
        $map['address'] = t($_POST['address']);
        $map['limitCount'] = intval(t($_POST['count']));
        $map['typeId'] = intval($_POST['typeId']);
        $map['contact'] = t($_POST['contact']);
        $map['allow'] = isset($_POST['allow']) ? 1 : 0;
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        $map['description'] = $textarea;
        //
        $map['status'] = 1;
        if (D('Course')->doAddCourse($map, $cover)) {
            $this->success($this->appName . '创建成功，请等待审核');
        } else {
            $this->error($this->appName . '添加失败');
        }
    }

    public function doAddCourseActive() {
        $this->checkHash();
        $required_field = array(
            'title' => '课程活动名称',
            'address' => '课程活动地点',
            'typeId' => '课程活动分类',
            'sTime' => '课程活动开始时间',
            'eTime' => '课程活动结束时间',
            'deadline' => '截止报名时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 20) {
            $this->error('课程活动名称最大20个字！');
        }
        $textarea = t($_POST['description']);
        if (mb_strlen($textarea, 'UTF8') <= 0 || mb_strlen($textarea, 'UTF8') > 200) {
            $this->error("课程活动简介1到200字!");
        }
        $map['deadline'] = _paramDate($_POST['deadline']);
        $map['sTime'] = _paramDate($_POST['sTime']);
        $map['eTime'] = _paramDate($_POST['eTime']);
        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于结束时间');
        }

        if (empty($_FILES['logo']['name'])) {
            $this->error('请上传课程活动logo');
        }

        $config = getPhotoConfig();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $cover = X('Xattach')->upload('courseActive', $options);
        if (!$cover['status'] && $cover['info'] != '没有选择上传文件') {
            $this->error($cover['info']);
        }
        $map['uid'] = $this->mid;
        $map['sid'] = $this->sid;
        $map['title'] = $title;
        $map['credit'] = intval($_POST['credit']);
        $map['address'] = t($_POST['address']);
        $map['limitCount'] = intval(t($_POST['count']));
        $map['typeId'] = intval($_POST['typeId']);
        $map['contact'] = t($_POST['contact']);
        $map['cost'] = intval($_POST['cost']);
        $map['costExplain'] = keyWordFilter(t($_POST['costExplain']));
        $map['allow'] = isset($_POST['allow']) ? 1 : 0;
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        $map['description'] = $textarea;
        $map['status'] = 1;
        if (D('Course')->doAddCourseActive($map, $cover)) {
            $this->success($this->appName . '创建成功，请等待审核');
        } else {
            $this->error($this->appName . '添加失败');
        }
    }

}

?>
