<?php

//BaseAction 公共基础Action
class BaseAction extends Action {

    var $appName;
    protected $sid;

    //执行应用初始化
    public function _initialize() {
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
    }

}

?>