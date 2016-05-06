<?php

class UserMobileModel extends Model {

    private $waitTime = 60;
    private $maxCnt = 5;
    public function clean() {
        $map['status'] = array('neq', 0);
        $map['cTime'] = array('lt', strtotime('-10 minutes'));
        $this->where($map)->setField('status', 0);
    }
    //今天是否可以发送
    private function _canSend($uid,$mobile=''){
        $cnt = $this->where("(uid=$uid OR mobile='$mobile') AND cTime>=".strtotime(date('Y-m-d'))."")->count();
        if($cnt>=$this->maxCnt){
            $this->error = '每天最多发送'.$this->maxCnt.'次';
            return false;
        }
        if ($this->getRestSec($uid)) {
            $this->error = $this->waitTime.'秒内只能发送一次';
            return false;
        }
        return true;
    }

    //根据手机判断
    private function _cansend1($mobile){
        //根据session判断
        $today = date('Y-m-d');
        $todayHas = intval($_SESSION['regHas'.$today]);
        if($todayHas >= $this->maxCnt){
            $this->error = '每天最多发送'.$this->maxCnt.'次';
            return false;
        }
        $restSec = $this->waitTime + intval($_SESSION['regSendMobileTime'.$today]) - time();
        if($restSec>0){
            $this->error = $this->waitTime.'秒内只能发送一次';
            return false;
        }
        //根据DB判断
        $map['mobile'] = $mobile;
        $lastTime = $this->where($map)->max('cTime');
        $rank = $lastTime + $this->waitTime - time();
        if($rank > 0){
            $this->error = $this->waitTime.'秒内只能发送一次';
            return false;
        }
        $map['cTime'] = array('gt',strtotime(date('Y-m-d')));
        $cnt = $this->where($map)->count();
        if($cnt>=$this->maxCnt){
            $this->error = '每天最多发送'.$this->maxCnt.'次';
            return false;
        }
        return true;
    }

    public function addRow($uid, $mobile, $from = '') {
        $mobile .= '';
        /*
        $restSec = $this->getRestSec($uid);
        if($restSec>0){
            $this->error = '等待'.$restSec.'秒后重新发送！';
            return false;
        }
        */
        if (!$this->_canSend($uid,$mobile)) {
            return false;
        }
        require_once(SITE_PATH . '/addons/libs/String.class.php');
        $code = String::rand_string(4, 1);

        //该用户以前的code统统作废
        $map = array();
        $map['status'] = array('neq', 0);
        $map['uid'] = $uid;
        $this->where($map)->setField('status', 0);

        $data['uid'] = $uid;
        $data['mobile'] = $mobile;
        $data['code'] = $code;
        $data['cTime'] = time();
        if ($this->add($data)) {
            $this->_updateRestSec($uid);
            closeDb();
            $msg = '您的验证码为：' . $code . '，请尽快完成验证';
            $result = service('Sms')->sendsms($mobile, $msg);
            return true;
        }
        $this->error = '发送验证码短信失败，请稍后再试';
        return false;
    }

    /**
     * 激活用户发送短信码
     * @param unknown $uid
     * @param unknown $mobile
     * @param string $from
     * @return number
     */
    public function addRowForActive($uid, $mobile,$from='') {
        $mobile .= '';
        if (!$this->_canSend($uid,$mobile)) {
            return false;
        }
        require_once(SITE_PATH . '/addons/libs/String.class.php');
        $code = String::rand_string(4, 1);
        $map = array();
        $map['status'] = array('neq', 0);
        $map['uid'] = $uid;
        $this->where($map)->setField('status', 0);
        $data['uid'] = $uid;
        $data['mobile'] = $mobile;
        $data['code'] = $code;
        $data['cTime'] = time();
        if($this->add($data)){
            closeDb();
            $msg = '您的验证码为：'.$code.'，请尽快完成验证';
            $result = service('Sms')->sendsms($mobile, $msg);
            return true;
        }
        $this->error = '发送验证码短信失败，请稍后再试';
        return false;
    }
    public function addRowMail($uid, $email,$from='') {
        if (!$this->_canSend($uid)) {
            return false;
        }
        require_once(SITE_PATH . '/addons/libs/String.class.php');
        $code = String::rand_string(4, 1);
        //该用户以前的code统统作废
        $map = array();
        $map['status'] = array('neq', 0);
        $map['uid'] = $uid;
        $this->where($map)->setField('status', 0);

        $data['uid'] = $uid;
        $data['mobile'] = $email;
        $data['code'] = $code;
        $data['cTime'] = time();
        if($this->add($data)){
            closeDb();
            $msg = '亲爱的PocketUni用户，您'.$from.'绑定邮箱验证码为：'.$code.'。请您尽快完成验证。';
            global $ts;
            $result = service('Mail')->send_email($email, $ts['site']['site_name'] . '身份验证', $msg);
            return true;
        }
        $this->error = '发送验证码短信失败，请稍后再试';
        return false;
    }
    //还有几秒才可进行下次发送
    public function getRestSec($uid=0) {

        $oldTime = intval(Mmc('GET_LAST_SEND_CODE_TIME_'.$uid));
        // 刷新缓存
        Mmc('GET_LAST_SEND_CODE_TIME_'.$uid,time(),0,60);
        // 如果记录不存在，则无需等待
        if (!$oldTime){
            return 0;
        }else{
            // 计算任然需要等待的时间
            return time() - $oldTime;
        }

        /*
        $today = date('Y-m-d');
        if(!$uid){
            $todayHas = intval($_SESSION['regHas'.$today]);
            if($todayHas >= $this->maxCnt){
                $nextDayTime = strtotime(date("Y-m-d",strtotime("+1 day")));
                return $nextDayTime-time();
            }
            $restSec = $this->waitTime + intval($_SESSION['regSendMobileTime'.$today]) - time();
            if($restSec<=0){
                $restSec = 0;
            }
            return $restSec;
        }
        $map['uid'] = $uid;
        $map['cTime'] = array('gt',strtotime($today));
        $cnt = $this->where($map)->count();
        if($cnt>=$this->maxCnt){
            $nextDayTime = strtotime(date("Y-m-d",strtotime("+1 day")));
            return $nextDayTime-time();
        }
        $lastTime = $this->where(array('uid' => $uid))->max('cTime');
        $rank = $lastTime + $this->waitTime - time();
        if ($rank <= 0) {
            return 0;
        }
        return $rank;
        */
    }
    private function _updateRestSec()
    {
        $today = date('Y-m-d');
        $_SESSION['regHas'.$today] += 1;
        $_SESSION['regSendMobileTime'.$today] = time();
    }
    public function onlyBind($uid,$mobile,$code){
        $map['status'] = array('neq', 0);
        $map['uid'] = $uid;
        $obj = $this->where($map)->find();
        if($obj){
            if($obj['mobile'] == $mobile && $obj['code'] == $code){
                $this->where('id = '.$obj['id'])->setField('status', 0);
                return true;
            }else{
                $this->where('id='.$obj['id'])->setField('status', $obj['status']-1);

            }
        }
        return false;
    }

    public function bind($uid,$mobile,$code){
        $mobile .= '';
        //$this->clean();
        $map['status'] = array('neq', 0);
        $map['uid'] = $uid;
        $obj = $this->where($map)->find();
        if($obj){
            if($obj['mobile'] == $mobile && $obj['code'] == $code){
                $this->where('id = '.$obj['id'])->setField('status', 0);
                $data['mobile'] = $mobile;
                $data['is_valid'] = 1;
                M('user')->where('uid='.$uid)->save($data);
                $userLoginInfo = S('S_userInfo_'.$uid);
                if(!empty($userLoginInfo)) {
                    $userLoginInfo['mobile'] = $mobile;
                    $userLoginInfo['is_valid'] = 1;
                    S('S_userInfo_'.$uid, $userLoginInfo);
                    if($_SESSION['userInfo']['uid'] == $uid){
                        $_SESSION['userInfo']['mobile'] = $mobile;
                        $_SESSION['userInfo']['is_valid'] = 1;
                    }
                }
                return true;
            }else{
                $this->where('id='.$obj['id'])->setField('status', $obj['status']-1);

            }
        }
        return false;
    }

    public function bindEmail($uid,$email,$code){
        //$this->clean();
        $map['status'] = array('neq', 0);
        $map['uid'] = $uid;
        $obj = $this->where($map)->find();
        if($obj){
            if($obj['mobile'] == $email && $obj['code'] == $code){
                $this->where('id = '.$obj['id'])->setField('status', 0);
                $data['email2'] = $email;
                $data['is_valid'] = 1;
                M('user')->where('uid='.$uid)->save($data);
                $userLoginInfo = S('S_userInfo_'.$uid);
                if(!empty($userLoginInfo)) {
                    $userLoginInfo['email2'] = $email;
                    $userLoginInfo['is_valid'] = 1;
                    S('S_userInfo_'.$uid, $userLoginInfo);
                    if($_SESSION['userInfo']['uid'] == $uid){
                        $_SESSION['userInfo']['email2'] = $email;
                        $_SESSION['userInfo']['is_valid'] = 1;
                    }
                }
                return true;
            }else{
                $this->where('id='.$obj['id'])->setField('status', $obj['status']-1);
            }
        }
        return false;
    }

    //注册时发送手机验证码
    public function addRowForActive1($mobile,$from='') {
        $mobile .= '';
        if (!$this->_canSend1($mobile)) {
            return false;
        }
        $map['mobile'] = $mobile;
        $this->setField('status',0,$map);
        require_once(SITE_PATH . '/addons/libs/String.class.php');
        $code = String::rand_string(4, 1);
        $data['mobile'] = $mobile;
        $data['code'] = $code;
        $data['cTime'] = time();
        if($this->add($data)){
            $this->_updateRestSec($uid);
            closeDb();
            $msg = '您的验证码为：'.$code.'，请尽快完成验证';
            $result = service('Sms')->sendsms($mobile, $msg);
            return true;
        }
        $this->error = '发送验证码短信失败，请稍后再试';
        return false;
    }
    public function getWaitTime()
    {
        return $this->waitTime;
    }
}