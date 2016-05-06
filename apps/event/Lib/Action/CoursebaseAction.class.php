<?php

/**
 * SchoolbaseAction
 * 校方活动前台抽象类
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class CoursebaseAction extends Action {

    protected $appName;
    protected $school;
    protected $sid;
    protected $smid = 0;
    protected $rights;

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        // 提示消息不显示头部
        $this->assign('isAdmin', 1);
        $domain = parse_url($_SERVER['HTTP_HOST']);
        $map['domain'] = substr($domain['path'], 0, strpos($domain['path'], '.'));
        $map['eTime'] = array('gt', 0);
        $school = M('school')->where($map)->find();
        $hostNeedle = get_host_needle();
        if (!$school) {
            $this->assign('jumpUrl', 'http://' . $hostNeedle);
            $this->error('此学校尚未开通校方活动！');
        }
        $this->school = $school;
        $this->sid = $school['id'];
        $this->assign('school', $school);
        //用户信息
        $this->rights['allAdmin'] = false;
        if($this->mid){
            $groups = M('user_group_link')->where('uid='.$this->mid)->field('user_group_id')->findAll();
            $gids = getSubByKey($groups, 'user_group_id');
            $this->rights['allAdmin'] = in_array(C('SADMIN'), $gids);
            //$this->rights['allLook'] = in_array(C('SLOOK'), $gids);
        }
        if ($this->user['sid'] == $this->sid || $this->rights['allAdmin']) {
            $this->smid = $this->mid;
            $this->assign('smid', $this->smid);
        }
        $this->rights['canAdd'] = false ||$this->rights['allAdmin'];
        $this->rights['canBackend'] = false||$this->rights['allAdmin'];
        if($this->smid && !$this->rights['allAdmin']){
            $this->rights['canAdd'] = in_array(C('KCFQGID'), $gids);
            $this->rights['canBackend'] = in_array(C('KCADMINGID'), $gids) || in_array(C('KCGLGID'), $gids);
        }
        $this->assign('rights', $this->rights);
        //应用名称
        $this->appName = '课程';
        // 活动分类
        $cate = D('CourseType')->getType();
        $this->assign('category', $cate);
    }

}
