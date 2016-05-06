<?php

/**
 * 前台基础控制器
 */
class BaseAction extends Action
{
    public function __construct()
    {
        //dump($GLOBALS);

        if (!isset($_SESSION['mid'])) { // 未登陆
            if (isset($_GET['mid']) && isset($_GET['token'])) {
                $uid = intval($_GET['mid']);
                $token = h($_GET['token']);
                if (!empty($uid) && !empty($token)) {
                    $condition = array(
                        'uid' => $uid,
                        'oauth_token' => $token
                    );
                    //dump($condition);
                    $login = M('Login')->where($condition)->find();
                    //dump($login);
                    if (!empty($login)) { // 登陆成功
                        $this->mid = $login['uid'];
                        $_SESSION['mid'] = $this->mid;
						
                    }

                }
            } else {
                $referUrl = $_SESSION['refer_url'];
                $this->redirect('pufinance/Public/login', array('refer' => urlencode($referUrl)));
            }
        }
        //dump($_SESSION);
        parent::__construct();
    }

    public function _initialize()
    {
        $this->assign('mobileJumpBox', 1);
    }
}