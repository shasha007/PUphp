<?php

/**
 * 后台抽象类
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class CourseCanAction extends Action {

    protected $school;
    protected $sid;
    protected $rights;

    //protected $user;
    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        $this->assign('isAdmin', 1);
        $domain = parse_url($_SERVER['HTTP_HOST']);
        $map['domain'] = substr($domain['path'], 0, strpos($domain['path'], '.'));
        $map['eTime'] = array('gt', 0);
        $school = M('school')->where($map)->find();
        $hostNeedle = get_host_needle();
        if (!$school) {
            $this->assign('jumpUrl', 'http://'.$hostNeedle);
            $this->error('此学校尚未开通校方活动！');
        }
        $this->school = $school;
        $this->sid = $school['id'];
        $this->assign('school', $school);
        $this->rights['allAdmin'] = $_SESSION['CourseAdmin'] == '123456';
        // 检查用户是否登录管理后台, 有效期为$_SESSION的有效期
        if ($_SESSION['CourseAdmin'] != '123456' && $_SESSION['CourseAdmin'] != $this->school['id'])
            redirect(U('event/CourseAdmin/adminlogin'));
    }

}
