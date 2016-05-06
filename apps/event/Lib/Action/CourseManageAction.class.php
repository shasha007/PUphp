<?php

/**
 * CourseAction
 * 课程管理
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 张晓军
 * @author 张晓军 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class CourseManageAction extends CourseCanAction {


    private $course;

    public function _initialize() {
        parent::_initialize();
        //管理权限判定
        $this->course = D('Course');
        $map['isDel'] = 0;
        $map['sid'] = $this->sid;
        $map['status'] = 1;
        $auditCount = $this->course->where($map)->count();
        $this->assign('auditCount', $auditCount);
    }


    public function index() {
        $this->display();
    }


    public function courselist() {
        //$map['status'] = array('in', '0,1,2,3');
        $map['sid'] = $this->sid;
        $this->_getCourseList($map);
        $this->display();
    }

    /**
     * 课程详情
     */
    public function course() {
        $this->_getCourse();
        $this->display();
    }

    /**
     * audit
     * 待审核的课程
     * @access public
     * @return void
     */
    public function audit() {
        $map['isDel'] = 0;
        $map['sid'] = $this->sid;
        $map['status'] = 1;
        $this->_getCourseList($map);
        $this->display();
    }

    /**
     * audit
     * 待完结的课程
     * @access public
     * @return void
     */
    public function doAudit() {
        $res = D('Course')->doSchoolAudit(intval($_REQUEST ['gid']), $this->mid, $this->sid); // 通过审核
        if ($res) {
            echo 2;
        } else {
            echo 0;
        }
    }

    public function doAuditScore() {
        $id = intval($_REQUEST['id']);
        $this->_checkSchool($id);
        $course = M('course')->field('id,title,sTime,eTime')->where('id=' . $id)->find();
        $this->assign($course);
        $this->display();
    }


    public function doAuditReason() {
        $id = intval($_GET['id']);
        $this->assign('id', $id);
        $del = $_GET['del'] ? 1 : 0;
        $this->assign('del', $del);
        $this->display();
    }

    //jun  驳回课程
    public function doDismissed() {
        $reason = t($_POST['reject']);
        $id = intval($_POST ['id']);
        if (empty($reason)) {
            $this->ajaxReturn(0, "请填写驳回原因", 1);
        }
        $this->_checkSchool($id);
        $res = D('Course')->doDismissed($id, $reason, intval($_POST ['del']), $this->mid);

        if ($res) {
            $this->ajaxReturn(0, "驳回成功！", 2);
        } else {
            $this->ajaxReturn(0, "驳回失败！", 0);
        }
    }

    private function _getCourseList($map = array(), $orig_order = 'cTime DESC') {
        //get搜索参数转post
        if (!empty($_GET['typeId'])) {
            $_POST['typeId'] = $_GET['typeId'];
        }
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['es_searchEvent'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_searchEvent']);
        } else {
            unset($_SESSION['es_searchEvent']);
        }
        $this->assign('isSearch', isset($_POST['isSearch']) ? '1' : '0');

        $map['isDel'] = 0;
        $_POST['uid'] && $map['uid'] = intval($_POST['uid']);
        $_POST['id'] && $map['id'] = intval($_POST['id']);
        $_POST['typeId'] && $map['typeId'] = intval($_POST['typeId']);
        $_POST['title'] && $map['title'] = array('like', '%' . t($_POST['title']) . '%');
        isset($_POST['isTop']) && $_POST['isTop'] != '' && $map['isTop'] = intval($_POST['isTop']);
        isset($_POST['isHot']) && $_POST['isHot'] != '' && $map['isHot'] = intval($_POST['isHot']);
        //处理时间
        $_POST['sTime'] && $_POST['eTime'] && $map['cTime'] = $this->course->DateToTimeStemp(t(date("Ymd", strtotime($_POST['sTime']))), t(date("Ymd", strtotime($_POST['eTime']))));
        //处理排序过程
        $order = isset($_POST['sorder']) ? t($_POST['sorder']) . " " . t($_POST['eorder']) : $orig_order;
        $_POST['limit'] && $limit = intval(t($_POST['limit']));
        $order && $list = $this->course->getList($map, $order, $limit);
        $type_list = D('CourseType')->getType();
        $this->assign($_POST);
        $this->assign($list);
        $this->assign('type_list', $type_list);
    }

    /**
     * doDeleteEvent
     * 删除课程
     * @access public
     * @return int
     */
    public function doDeleteCourse() {
        $courseid['id'] = array('in', explode(',', $_REQUEST['id']));    //要删除的id.
        $result = $this->course->doDeleteCourse($courseid);
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

    //推荐操作
    public function doChangeIsHot() {
        $event['id'] = array('in', $_REQUEST['id']);        //要推荐的id.
        $act = $_REQUEST['type'];  //推荐动作
        $result = $this->course->doIsHot($event, $act);

        if (false != $result) {
            echo 1;            //推荐成功
        } else {
            echo -1;               //推荐失败
        }
    }

    //置顶操作
    public function doChangeIsTop() {
        $course['id'] = array('in', $_REQUEST['id']);        //要推荐的id.
        $act = $_REQUEST['type'];  //动作
        $result = $this->course->doIsTop($course, $act);

        if (false != $result) {
            echo 1;            //成功
        } else {
            echo -1;           //失败
        }
    }

    /**
     * eventtype
     * 课程类型列表
     * @access public
     * @return void
     */
    public function courseType() {
        $type = D('CourseType');
        $type = $type->order('id ASC')->findAll();
        $this->assign('type_list', $type);

        $count = D('Event')->field('typeId,count(typeId) as count')->group('typeId')->findAll();
        foreach ($count as $k => $v) {
            // unset($count[$k]);
            $count[$v['typeId']] = $v['count'];
        }
        $this->assign('count', $count);

        $this->display();
    }

 public function editType() {

     $id= intval($_REQUEST['id']);
     $val=t($_REQUEST['val']);
     M('course_type')->where("id=".$id)->setField('name', $val);

 }
  public function addType() {
      $this->display();
  }

    /**
     * doAddType
     * 添加分类
     * @access public
     * @return void
     */
    public function doAddType() {
        $isnull = preg_replace("/[ ]+/si", "", t($_POST['name']));
        $type = D('CourseType');
        $name = M('CourseType')->where(array('name' => $isnull))->getField('name');
        if (empty($isnull)) {
            echo -2;
        }
        if ($name !== null) {
            echo 0;
        } else {
            if ($result = $type->addType($_POST)) {
                echo 1;
            } else {
                echo -1;
            }
        }
    }


    /**
     * doEditType
     * 删除分类
     * @access public
     * @return void
     */
    public function doDeleteType() {
        $id['id'] = array("in", $_POST['id']);
        $type = D('CourseType');
        if ($result = $type->deleteType($id)) {
            if (!strpos($_POST['id'], ",")) {
                echo 2;            //说明只是删除一个
            } else {
                echo 1;            //删除多个
            }
        } else {
            echo $result;
        }
    }

    /**
     * 编辑课程
     */
    public function editCourse() {
        $this->_getCourse();
        $typeDao = D('CourseType');
        $type = $typeDao->getType();
        $this->assign('type', $type);
        $this->display();
    }

    private function _getCourse() {
        //课程
        $id = intval($_REQUEST['id']);
        //检测id是否为0
        if ($id <= 0) {
            $this->assign('jumpUrl', U('/CourseManage/courselist'));
            $this->error("错误的访问页面，请检查链接");
        }
        $this->_checkSchool($id);
        $map['id'] = $id;
        if ($result = $this->course->where($map)->find()) {
            $this->assign($result);
        } else {
            $this->assign('jumpUrl', U('/Index/index'));
            $this->error('课程不存在');
        }
        // 课程分类
        $cate = D('CourseType')->getType();
        $this->assign('category', $cate);
    }

    public function doEditCourse() {
        $id = intval($_POST['id']);
        if (!$obj = $this->course->where(array('id' => $id))->find()) {
            $this->error('课程不存在或已删除');
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

        //得到上传的图片
        $config = getPhotoConfig();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $cover = X('Xattach')->upload('course', $options);
        if (!$cover['status'] && $cover['info'] != '没有选择上传文件') {
            $this->error($cover['info']);
        }
//        var_dump($_FILES);die;
        $map['credit'] = intval($_POST['credit']);
        $map['title'] = $title;
        $map['address'] = t($_POST['address']);
        $map['limitCount'] = intval(t($_POST['limitCount']));
        $map['typeId'] = intval($_POST['typeId']);
        $map['contact'] = t($_POST['contact']);
        $map['cost'] = intval($_POST['cost']);
        $map['costExplain'] = keyWordFilter(t($_POST['costExplain']));
        $map['allow'] = isset($_POST['allow']) ? 1 : 0;
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        $map['description'] = $textarea;
        if ($this->course->doEditCourse($map, $cover, $obj)) {
            $this->assign('jumpUrl', U('/CourseManage/editCourse', array('id' => $id, 'uid' => $this->mid)));
            $this->success($this->appName . '修改成功！');
        } else {
            $this->error($this->appName . '修改失败');
        }
    }

    public function newsList() {
        if (!empty($_POST)) {
            $_SESSION['admin_event_news_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_event_news_search']);
        } else {
            unset($_SESSION['admin_event_news_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['a.title'] = array('like', "%" . $_POST['title'] . "%");
        $_POST['eventUid'] = t($_POST['eventUid']);
        $_POST['eventUid'] && $map['uid'] = $_POST['eventUid'];
        $_POST['eventId'] = t($_POST['eventId']);
        $_POST['eventId'] && $map['eventId'] = $_POST['eventId'];
        $this->assign($_POST);
        $map['is_school_event'] = $this->sid;
        $db_prefix = C('DB_PREFIX');
        $list = D('EventNews')->table("{$db_prefix}event_news AS a ")
                        ->join("{$db_prefix}event AS b ON a.eventId=b.id")
                        ->field('a.* , b.uid')
                        ->where($map)->order('a.id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }



    public function imgList() {
        if (!empty($_POST)) {
            $_SESSION['admin_event_img_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_event_img_search']);
        } else {
            unset($_SESSION['admin_event_img_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['a.title'] = array('like', "%" . $_POST['title'] . "%");
        $_POST['eventUid'] = t($_POST['eventUid']);
        $_POST['eventUid'] && $map['b.uid'] = $_POST['eventUid'];
        $_POST['eventId'] = t($_POST['eventId']);
        $_POST['eventId'] && $map['eventId'] = $_POST['eventId'];
        $this->assign($_POST);
        $map['is_school_event'] = $this->sid;
        $db_prefix = C('DB_PREFIX');
        $list = D('EventImg')->table("{$db_prefix}event_img AS a ")
                        ->join("{$db_prefix}event AS b ON a.eventId=b.id")
                        ->field('a.id,a.eventId,a.path,a.title, a.cTime, b.uid')
                        ->where($map)->order('a.id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    /**
     * 删除视频
     */
    public function deleteImg() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('EventImg')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    private function _checkSchool($id) {
        $course = M('course')->where('id=' . $id)->field('sid')->find();
        if ($course['sid'] != $this->sid) {
            $this->assign('jumpUrl', U('/CourseManage/courselist'));
            $this->error("您不属于该学校，无法操作");
        }
    }

}
