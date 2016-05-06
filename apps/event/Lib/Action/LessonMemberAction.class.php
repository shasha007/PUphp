<?php
//前台课程详细页
class LessonMemberAction extends CoursebaseAction {

    private $courseId;
    private $course;
    private $obj;

    public function _initialize() {
        parent::_initialize();
        $this->course = D('Course');
        //课程
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
                $this->error('课程不存在');
            }
            if ($result['status'] == 1) {
                $this->error('该课程正在审核中');
            } elseif ($result['status'] == 0) {
                $this->error('该课程未通过审核');
            }
            if($id==15){
                $result['joinCount'] = 270;
            }
            $result['isEnd'] = false;
            $this->obj = $result;
            $this->assign($result);
        } else {
            $this->assign('jumpUrl', U('/Index/index'));
            $this->error('课程不存在或，未通过审核或，已删除');
        }
        $this->assign('courseId', $id);
        $this->assign('istop', $this->course->getTop($this->sid));
        $this->courseId = $id;
    }

    public function index() {
        if ($this->obj{'limitCount'} < 1) {
            $this->error('参加课程人数已满，不能再参加');
        }
        $this->assign('tel', $this->user['mobile']);
        $this->assign('name', $this->user['realname']);
        $this->assign('istop', $this->course->getTop($this->sid));
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
        $data['studentId'] = getUserEmailNum($this->user['email']);
        $data['sid'] = $this->user['sid'];
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
            $this->assign('jumpUrl', U('/LessonMember/detail', array('id' => $this->courseId)));
            $this->success($result['info']);
        }
    }

    private function canEditCourse() {
        if (!service('SystemPopedom')->hasPopedom($this->mid, 'admin/Index/index', false)
                && $this->obj['uid'] == $this->mid && $this->obj['status'] == 3) {
            $this->error('该课程已结束，无法更改');
        }
    }

    public function detail() {
        $mapuser['uid'] = $this->mid;
        $mapuser['courseId'] = $this->courseId;
        //检查是否已经加入
       $res =  M('course_user')->where($mapuser)->find();
       $joined = $res ? 1:0;
        // 活动分类
        $cate = D('CourseType')->getType();
        $this->assign('joined', $joined);
        $this->assign('category', $cate);
        $this->display();
    }

}

