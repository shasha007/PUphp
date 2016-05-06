<?php

/**
 * IndexAction
 * 校方活动教师后台
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class CourseUserAction extends CourseCanAction {

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        parent::_initialize();
    }
    /**
     * index
     * 首页
     * @access public
     * @return void
     */
    public function index() {
        $order = 'uid DESC';
        if($_GET['orderKey'] && $_GET['orderType']){
            $_GET['orderKey'] = t($_GET['orderKey']);
            $_GET['orderType'] = t($_GET['orderType']);
            $order = $_GET['orderKey'].' '.$_GET['orderType'].','.$order;
            $this->assign('orderKey', $_GET['orderKey']);
            $this->assign('orderType', $_GET['orderType']);
        }
        $res = D('User', 'home')->getUserList($this->_userFilter(), false, false,'*',$order);
        $this->assign($res);
        $this->assign('editSid',$this->school['id']);
        $this->display();
    }

    //不同身份要过滤掉，不显示的
    private function _userFilter() {
        $map['sid'] = $this->school['id'];
        if (!$this->rights['allAdmin']) {
            //过滤掉管理员
            $uids = model('UserGroup')->getUidByUserGroup(1);
            $uids = array_unique($uids);
            $map['uid'] = array('not in', $uids);
        }
        return $map;
    }

    //编辑用户
//    public function editUser() {
//        $_GET['uid'] = intval($_GET['uid']);
//        if ($_GET['uid'] <= 0)
//            $this->error('参数错误');
//        $map['sid'] = $this->school['id'];
//        $map['uid'] = $_GET['uid'];
//        $user = M('user')->where($map)->find();
//        if (!$user)
//            $this->error('无此用户');
//        if (!$this->_canEdit($user))
//            $this->error('您无权修改该用户资料');
//        $user['number'] = substr($user['email'], 0, strpos($user['email'], '@'));
//        $user['sidName'] = tsGetSchoolName($user['sid1']);
//        $this->assign($user);
//        $this->assign('canEditRole', $this->_canEditRole($map['uid']));
//        $this->assign('type', 'edit');
//        $this->assign('schoolOrga', D('SchoolOrga')->getAll($this->school['id']));
//        $school = model('Schools')->makeLevel0Tree($this->school['id']);
//        $this->assign('addSchool', $school);
//        $this->display();
//    }

    private function _canEditRole($uid) {
        if (!$this->rights['allAdmin'] && !$this->user['can_admin'] && $uid == $this->mid) {
            return false;
        }
        return true;
    }

    private function _canEdit($user) {
        if (!$this->rights['allAdmin'] || $this->user['can_admin'] || $this->mid == $user['uid'] ||
                $this->user['event_level'] < $user['event_level']) {
            return true;
        }
        return false;
    }

//    public function doEditUser() {

//        //修改权限
//        model('UserGroup', 'addons')->addUserToUserGroup($_POST['uid'], t($_POST['user_group_id']));
//        S('UserGroupIds_' . $_POST['uid'], null);
////        if ($_POST['uid'] == $this->mid) {
////            $_SESSION['userInfo'] = D('User', 'home')->getUserByIdentifier($this->mid);
////        }
//        $this->assign('jumpUrl', U('event/CourseUser/index'));
//        $this->success('保存成功');
//    }

    //搜索用户
    public function doSearchUser() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['es_searchUser'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_searchUser']);
        } else {
            unset($_SESSION['es_searchUser']);
        }
        //组装搜索条件
        $map = $this->_userFilter();
        $fields = array('uid');
        foreach ($fields as $v)
            if (isset($_POST[$v]) && $_POST[$v] != '')
                $map[$v] = array('in', explode(',', $_POST[$v]));

        $number = t($_POST['number']);
        if ($number != '') {
            $map['email'] = $number . $this->school['email'];
        }
        //姓名时，模糊查询
        if (isset($_POST['realname']) && $_POST['realname'] != '') {
            $map['realname'] = array('exp', 'LIKE "%' . $_POST['realname'] . '%"');
        }
        $sid1 = intval($_POST['sid1']);
        if ($sid1) {
            $map['sid1'] = $sid1;
        }

        $res = D('User', 'home')->getUserList($map, true, true);
        $this->assign($res);
        $this->assign('editSid',$this->school['id']);
        $this->assign('type', 'searchUser');
        $this->assign(array_map('t', $_POST));
        $this->assign('tree', model('Schools')->_makeTree(0));
        $this->display('index');
    }

    //学分详情
    public function credit(){
        $num = t($_GET['num']);
        if($num){
            $mapUser['email'] = $num.$this->school['email'];
            $userInfo = M('user')->where($mapUser)->field('uid,realname,course_credit')->find();
            if($userInfo){
                $this->assign('userInfo',$userInfo);
                $db_prefix = C('DB_PREFIX');
                $map['a.uid'] = $userInfo['uid'];
                $map['a.status'] = 1;
                $course = M('course_user')->table("{$db_prefix}course_user AS a ")
                                ->join("{$db_prefix}course AS b ON a.courseId=b.id")
                                ->where($map)->field('a.cTime,a.credit,b.title')->findPage(20);
                $this->assign($course);
            }
        }
        $this->display();
    }

    public function creditActive() {
        $num = t($_GET['num']);
        if ($num) {
            $mapUser['email'] = $num . $this->school['email'];
            $userInfo = M('user')->where($mapUser)->field('uid,realname,course_credit')->find();
            if ($userInfo) {
                $this->assign('userInfo', $userInfo);
                $db_prefix = C('DB_PREFIX');
                $map['a.uid'] = $userInfo['uid'];
                $map['a.status'] = 1;
                $active = D('course_active_user')->table("{$db_prefix}course_active_user AS a ")
                                ->join("{$db_prefix}course_active AS b ON a.courseId=b.id")
                                ->where($map)->field('a.cTime,a.credit,b.title')->findPage(20);
                $this->assign($active);
            }
        }
        $this->display();
    }

}
