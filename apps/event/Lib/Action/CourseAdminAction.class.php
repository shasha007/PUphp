<?php

/**
 * SchoolAction
 * 校方活动
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class CourseAdminAction extends CoursebaseAction {

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        parent::_initialize();
    }

    public function adminlogin() {

        if ($_SESSION['CourseAdmin'] == '123456' || $_SESSION['CourseAdmin'] == $this->school['id']) {
            redirect(U('event/CoursePass/index'));
        }
        $this->display();
    }

    public function doAdminLogin() {
        // 提示消息不显示头部
        $this->assign('isAdmin', '1');
        // 检查验证码
        if (md5($_POST['verify']) != $_SESSION['verify']) {
            $this->error(L('error_security_code'));
        }
        $_POST['email'] = t($_POST['email']);
        if (isset($_POST['email'])) {
            if (!strpos($_POST['email'], '@')) {
                $_POST['email'] = $_POST['email'] . $this->school['email'];
            }
        }
        // 数据检查
        if (empty($_POST['password'])) {
            $this->error(L('password_notnull'));
        }
        if (isset($_POST['email']) && !isValidEmail($_POST['email'])) {
            $this->error(L('email_format_error'));
        }
        // 检查帐号/密码
        $is_logged = false;
        if (isset($_POST['email'])) {
            $is_logged = service('Passport')->loginCourseAdmin($_POST['email'], $_POST['password'], $this->school['id']);
        } else if ($this->mid > 0) {
            $is_logged = service('Passport')->loginCourseAdmin($this->mid, $_POST['password'], $this->school['id']);
        } else {
            $this->error(L('parameter_error'));
        }

        if ($is_logged) {
            redirect(U('event/CoursePass/index'));
        } else {
            $this->assign('jumpUrl', U('event/CourseAdmin/adminlogin'));
            $this->error(L('login_error'));
        }
    }

    public function logoutAdmin() {
        // 成功消息不显示头部
        $this->assign('isAdmin', '1');
        service('Passport')->logoutCourseAdmin();
        $this->assign('jumpUrl', U('event/CourseAdmin/adminlogin'));
        $this->success(L('exit_success'));
    }

    public function logout() {
        service('Passport')->logoutLocal();
        $domain = parse_url($_SERVER['HTTP_HOST']);
        $this->assign('jumpUrl', U('event/Lesson/board'));
        $this->success(L('exit_success'));
    }

    public function doLogin() {
        // 提示消息不显示头部
        $this->assign('isAdmin', '1');
        // 检查验证码
        $opt_verify = $this->_isVerifyOn('login');
        if ($opt_verify && md5($_POST['verify']) != $_SESSION['verify']) {
            $this->error(L('error_security_code'));
        }
        //选择学校登录
        $username = $_POST['number'] . $this->school['email'];
        //
        $password = $_POST['password'];

        if (!$password) {
            $this->error(L('please_input_password'));
        }
        service('Passport')->logoutSchoolAdmin();
        $result = service('Passport')->loginLocal($username, $password, intval($_POST['remember']));

        //检查是否激活
        if (!$result && service('Passport')->getLastError() == '用户未激活') {
              $this->assign('jumpUrl', U('event/Lesson/board'));
            $this->error('该用户尚未激活，请更换帐号或激活帐号！');
            exit;
        }

        //检查全局管理员或全局观察员
        if (!$result && service('Passport')->getLastError() != '用户未激活') {
            $username = $_POST['number'] . '@test.com';
            $result = service('Passport')->loginLocal($username, $password, intval($_POST['remember']));
        }

        if ($result) {
           $this->assign('jumpUrl', U('event/Lesson/board'));
            $this->success($username .'登录成功');
        } else {
            $this->error(L('login_error'));
        }
    }

    private function _isVerifyOn($type = 'login') {
        // 检查验证码
        if ($type != 'login' && $type != 'register')
            return false;
        $opt_verify = $GLOBALS['ts']['site']['site_verify'];
        return in_array($type, $opt_verify);
    }

}

