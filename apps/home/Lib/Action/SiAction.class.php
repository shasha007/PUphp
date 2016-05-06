<?php
class SiAction extends Action{

    private $_loged;

    public function _initialize() {
        $this->_loged = service('Passport')->isLogged();
        $this->assign('logged',$this->_loged);
    }

    public function test(){
        if(!$this->_loged){
            $this->display('siback');
            die;
        }
        //检查账号是否存在
        $hasSi = M('user_si')->where('uid='.$this->mid)->find();
        if(!$hasSi){
            include_once(SITE_PATH.'/addons/libs/Sip.class.php');
            $obj =  new Sip();
            $res = $obj->send($this->mid);
            if($res['status']){
                M('user_si')->add(array('uid'=>$this->mid));
            }
        }
        C('TOKEN_ON',false);
        $param['eppCode'] = 'TIANGONG';
        //会员流水号
        $param['seqNo'] = "$this->mid";
        $param['userID'] = "$this->mid";
        $param['userEmail'] = '';
        $param['userAgentDgst'] = '';
        $param['token'] = '';
        $param['orderID'] = "$this->mid";
        //若使用转导失败，将使用者导回哪一个页面
        $param['epphome'] = 'http://pocketuni.net/siback';
        $this->assign('info', json_encode($param));
        $this->display();
    }

}