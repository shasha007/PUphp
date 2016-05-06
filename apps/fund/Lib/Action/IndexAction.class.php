<?php

/*
 * 活动基金前台
 *
 */
class IndexAction extends Action{

    public function _initialize() {
        $this->assign('isAdmin', 1);
    }
    /**
     * 右侧动态栏 余额
     */
    public function rightInfo(){
       //余额
       $count = D('FundSponsor')->fundNow();
       $this->assign('sponsorRest',$count);
       //赞助动态
       $sponsor = D('FundSponsor')->field('id,company,actualFund,actualTime,attachId')->order('id desc')->findPage(10);
       $this->assign('sponsor',$sponsor['data']);
       //申请/审核动态
       $apply_res = D('FundFundlog')->logList(array(0,1));
       $this->assign('applys',$apply_res['data']);
       //发放动态
       $sends = D('FundFundlog')->logList(array(2));
       $this->assign('sends',$sends['data']);
    }

    public function index(){
       //在本学校显示的活动列表
       $sid = getUserField($this->mid, 'sid');
       $events = D('FundEvent')->listBySid($sid);
       $this->assign($events);
       //调用右侧栏信息
       $this->rightInfo();
       $this->display();
    }

    //活动详情
    public function eventDetail(){
        $id = intval($_REQUEST['id']);
        $info = M('FundEvent')->where("eventId=$id")->find();
        $this->assign($info);
        $this->display();
    }
    //我要申请
    public function eventApply(){
        $dao = D('FundEvent');
        $eid = intval($_REQUEST['id']);
        $group = $dao->_canApplyGroup($eid,$this->mid);
        if(!$group){
            $this->assign('msg', $dao->getError());
            $this->display();exit;
        }
        foreach($group as &$val){
             $val['name'] = M('Group')->getField('name', 'id = ' . $val['gid']);
        }
        $this->assign('groups', $group);
        $this->display();
    }
    /**
     * 更多动态
     *
     */
    public function moreDynamic(){
        //调用右侧栏信息
        $this->rightInfo();
        $cid = $_GET['cid'];
        switch($cid){
            case 's':
                $info = $sponsor = D('FundSponsor')->field('id,company,actualFund,actualTime,attachId')->order('id desc')->findPage(10);
                $this->assign($info);
                $content = $this->fetch('_moresponsor');
                break;
            case 'c':
                $info = D('FundFundlog')->logList(array(0,1));
                $this->assign($info);
                $content = $this->fetch('_morecheck');
                break;
            case 'f':
                $info = D('FundFundlog')->logList(array(2));
                $this->assign($info);
                $content = $this->fetch('_moresend');
                break;
        }
        $this->assign('content',$content);
        $this->display();
    }

    // 我申请的活动列表
    public function myapply(){
        $res = M('FundApplyevent')->where("uid=$this->mid")->field('id,eventId,state,eid')->order('id desc')->findPage(10);
        $daoEvent = M('FundEvent');
        foreach ($res['data'] as &$v) {
            $v['event'] = $daoEvent->where('eventId=' . $v['eventId'])->field('eventName,company,logo')->find();
            $v['count'] = D('FundApplyevent')->groupCount($v['eventId']);
        }
        $this->assign($res);
        $this->display();
    }

    /**
     * 返回部落 AJAX
     */
    public function returnGroup(){
        //活动部落 ？？
        $group = M('event_group')->where('uid='.$this->mid)->findAll();
        if(count($group) == 1){
           $group['name'] = M('Group')->getField('name', 'id = ' . $v['gid']);
        }elseif(count($group)>1){
            foreach($group as &$val){
                 $val['name'] = M('Group')->getField('name', 'id = ' . $val['gid']);
            }
        }

        if($group){
            echo json_encode($group);
        }else{
            echo 0;
        }
    }

    public function doapplyEvent(){
        $dao = D('FundApplyevent');
        $res = $dao->doApply($this->mid, $_POST);
        if ($res) {
            $this->display('success_fund');
        } else {
            $this->error($dao->getError());
        }
    }


    /**
     * 申请基金选择的活动列表 在审核中或者通过的活动不可选择 AJAX
     */
    public function fund_apply(){
        $events = D('FundApplyfund')->eventList($this->mid);
        $this->assign('events', $events);
        $this->display();
    }

    /**
     * 申请基金
     */
    public function doapplyFund(){
        $dao = D('FundApplyfund');
        $res = $dao->doApply($this->mid);
        if ($res) {
            $this->display('success_fund');
        } else {
            $this->error($dao->getError());
        }
    }



}
?>
