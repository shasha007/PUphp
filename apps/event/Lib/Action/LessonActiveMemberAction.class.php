<?php
//前台活动详细页
class LessonActiveMemberAction extends CoursebaseAction {

    private $courseId;
    private $course;
    private $obj;

    public function _initialize() {
        parent::_initialize();
        $this->course = D('CourseActive');
        //课程活动
        $id = intval($_REQUEST['id']);
        //检测id是否为0
        if ($id <= 0) {
            $this->assign('jumpUrl', U('/Index/index'));
            $this->error("错误的访问页面，请检查链接");
        }
        $this->course->setMid($this->mid);
        $map['id'] = $id;
        $map['isDel'] = 0;
        if ($result = $this->course->where($map)->find()) {
            if($result['sid'] != $this->sid){
                $this->error('课程活动不存在');
            }
            if ($result['status'] == 1) {
                $this->error('该课程活动正在审核中');
            } elseif ($result['status'] == 0) {
                $this->error('该课程活动未通过审核');
            }
            $result['isEnd'] = false;

            $this->obj = $result;
            $this->assign($result);
        } else {
            $this->assign('jumpUrl', U('/Index/index'));
            $this->error('课程活动不存在或，未通过审核或，已删除');
        }
        $this->assign('istop', $this->course->getTop($this->sid));
        $this->assign('courseId', $id);
        $this->courseId = $id;
    }

    public function index() {
        if ($this->obj{'limitCount'} < 1) {
            $this->error('参加课程活动人数已满，不能再参加');
        }
        $this->assign('tel', $this->user['mobile']);
        $this->assign('name', $this->user['realname']);
        $this->display();
    }

    public function doAddMember() {
        if ($this->user['sid'] != $this->sid) {
            $this->error('您不是该校用户');
        }
        $data['id'] = $this->courseId;
        $data['uid'] = $this->mid;
        $data['realname'] = $this->user['realname'];
        $data['sex'] = $this->user['sex'];
        $data['sid'] = $this->user['sid'];
        $data['studentId'] = getUserEmailNum($this->user['email']);
        if ($this->obj{'need_tel'}) {
            if (!$this->user['mobile']) {
                redirect(U('home/Account/security#mobile'));
            }
            $data['tel'] = $this->user['mobile'];
        }

        $this->course->setMid($this->mid);
        $result = $this->course->doAddUser($data);
        if ($result['status'] == 0) {
            $this->error($result['info']);
        } else {
            $this->assign('jumpUrl', U('/LessonActiveMember/detail', array('id' => $this->courseId)));
            $this->success($result['info']);
        }
    }

    private function canEditCourse() {
        if (!service('SystemPopedom')->hasPopedom($this->mid, 'admin/Index/index', false)
                && $this->obj['uid'] == $this->mid && $this->obj['status'] == 3) {
            $this->error('该课程活动已结束，无法更改');
        }
    }

    public function detail() {
        $map['courseId'] = $this->courseId;
        $map['uid'] = 0;
        $list = D('CourseImg')->where($map)->order('`id` DESC')->limit(6)->findAll();
        $mapuser['uid'] = $this->mid;
        $mapuser['courseId'] = $this->courseId;
        //检查是否已经加入
        $res = M('course_active_user')->where($mapuser)->find();
        $joined = $res ? 1 : 0;
        // 活动分类
        $cate = D('CourseType')->getType();
        $this->assign('category', $cate);
        $this->assign('joined', $joined);
        $this->assign('list', $list);
        $this->display();
    }

    public function morePhoto() {
        $map['courseId'] = $this->courseId;
        $map['uid'] = 0;
        $list = D('CourseImg')->where($map)->order('`id` DESC')->findPage(12);
        $this->assign($list);
        $this->display();
    }

    public function photoDetail() {
        $map['id'] = $this->courseId;
        if (isset($_REQUEST['uid'])) {
            $map['uid'] = intval($_REQUEST['uid']);
        }
        $this->assign('echoFocus', U('/LessonActiveMember/jsonPhoto', $map));
        $this->display();
    }

    public function jsonPhoto() {
        $dao = M('CourseImg');
        $uid = intval($_REQUEST['uid']);
        $result = array();
        $result['slide']['title'] = $this->obj['title'] . '-照片';
        $result['slide']['createtime'] = '2012-11-21 16:16:02';
        $result['slide']['url'] = U('/LessonActiveMember/photoDetail', array('id' => $this->courseId, 'uid' => $uid));
        $list = $dao->where(array('courseId' => $this->courseId, 'uid' => $uid))->order('id DESC')->findAll();
        foreach ($list as $value) {
            $vo['title'] = getShort($value['title'], 35);
            $vo['intro'] = $value['title'];
            $vo['thumb_50'] = getThumb($value['path'], 50, 50);
            $vo['thumb_160'] = getThumb($value['path'], 160, 160);
            $vo['image_url'] = get_photo_url($value['path']);
            $vo['createtime'] = friendlyDate($value['cTime']);
            $vo['source'] = 'PocketUni';
            $vo['id'] = $value['id'];
            $result['images'][] = $vo;
        }
        $result['next_album']['interface'] = U('/LessonActiveMember/jsonPhoto', array('id' => $this->courseId, 'uid' => $uid));
        $result['next_album']['title'] = $this->obj['title'] . '-照片';
        $result['next_album']['url'] = U('/LessonActiveMember/photoDetail', array('id' => $this->courseId, 'uid' => $uid));
        $result['next_album']['thumb_50'] = '';
        echo 'var slide_data = ' . json_encode($result);
    }

}

