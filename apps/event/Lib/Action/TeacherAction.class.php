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
class TeacherAction extends Action {

    protected $school;
    protected $rights;
    protected $sid;
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
        $this->assign('school',$school);
        $this->user = D('User', 'home')->getUserByIdentifier($this->mid);
        // 检查用户是否登录管理后台, 有效期为$_SESSION的有效期
        if ($_SESSION['TeacherAdmin'] != '123456' && $_SESSION['TeacherAdmin'] != $this->school['id'])
            redirect(U('event/School/adminlogin'));
        //各频道管理权限
        $this->rights['can_admin'] = 0;
        if($_SESSION['ThinkSNSAdmin'] == '1' || $this->user['can_admin']){
            $this->rights['can_admin'] = 1;
        }
        $this->rights['pu_admin'] = 0;
        if($_SESSION['ThinkSNSAdmin'] == '1'){
            $this->assign('thinkSnsAdmin', 1);
            $this->rights['pu_admin'] = 1;
            $this->rights['can_user'] = 1;
        }else{
            $this->rights['can_user'] = ($this->user['event_level'] == 20)?0:1;
        }
        $this->rights['can_add_event'] = $this->user['can_add_event'];
        $this->rights['can_event'] = $this->user['can_event'];
        $this->rights['can_event2'] = $this->user['can_event2'] || $this->rights['can_admin'];
        $this->rights['can_gift'] = $this->user['can_gift'] || $this->rights['can_admin'];
        $this->rights['can_print'] = $this->user['can_print'] || $this->rights['can_admin'];
        $this->rights['can_group'] = $this->user['can_group'] || $this->rights['can_admin'];
        $this->rights['can_announce'] = $this->user['can_announce'] || $this->rights['can_admin'];
        $this->rights['can_available'] = $this->user['can_available'] || $this->rights['can_admin'];
        $this->rights['can_prov_news'] = $this->user['can_prov_news'] || $this->rights['can_admin'];
        $this->rights['can_prov_work'] = $this->user['can_prov_work'] || $this->rights['can_admin'];
        $this->rights['can_credit'] = $this->user['can_credit'] || $this->rights['can_admin'];
        $this->assign('rights',$this->rights);
        //站点信息
        $config = D('SchoolWeb','event')->getConfigCache($this->sid);
        $this->assign('webconfig', $config);
    }

    public function findTeam() {
        if (!$_POST['team']) {
            exit();
        }
        $team = t($_POST['team']);
        if (preg_match("/[\x7f-\xff]+/", $_POST['team'])) {
            $name = M('user')
                            ->where("`realname` like '$team%' AND sid =" . $this->sid)
                            ->field('realname,uid,email')->findAll();
            if ($name) {
                foreach($name as $k=>$v){
                    $name{$k}['email'] = getUserEmailNum($v['email']);
                }
                exit(json_encode($name));
            }
        } else {
            $email = $team . $this->school['email'];
            $name = M('user')
                            ->where("`email` = '$email' AND sid =" . $this->sid)
                            ->field('realname,uid')->find();
            if ($name) {
                $name[0]['email'] ='000';
                exit(json_encode($name));
            }
        }
    }

}
