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
            $this->redirect(U('w3g/Public/login'), 0);
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
        //多行URL地址支持
        $url = str_replace(array("\n", "\r"), '', $url);
        if (empty($msg))
            $msg = "系统将在{$time}秒之后自动跳转到{$url}！";
        if (!headers_sent()) {
            // redirect
            if (0 === $time) {
                header("Location: " . $url);
            } else {
                header("refresh:{$time};url={$url}");
                // 防止手机浏览器下的乱码
                $str = '<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />';
                $str .= $msg;
            }
        } else {
            $str = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
            if ($time != 0)
                $str .= $msg;
        }
        $this->assign('msg', $str);
        $this->display('redirect');
        die;
    }
}