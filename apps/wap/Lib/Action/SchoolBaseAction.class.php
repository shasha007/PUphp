<?php

class SchoolBaseAction extends Action {

    protected $school;
    protected $sid;
    protected $smid;

    public function _initialize() {
        $this->assign('isAdmin', 1);
        $domain = parse_url($_SERVER['HTTP_HOST']);
        $map['domain'] = substr($domain['path'], 0, strpos($domain['path'], '.'));
        $map['eTime'] = array('gt', 0);
        $school = M('school')->where($map)->find();
        if (!$school) {
            $this->redirect(U('wap/Public/login'), 0);
        }
        $this->school = $school;
        $this->sid = $school['id'];
        if($this->mid && $this->sid == $this->user['sid']){
            $this->smid = $this->mid;
            $this->assign('smid', $this->smid);
        }
        $this->assign('school', $school);
    }

    // URL重定向
    protected function redirect($url, $time = 0, $msg = '') {
        $this->assign('msg', $msg);
        $this->display('redirect');
        die;
    }
}