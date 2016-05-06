<?php

/*
 * 活动基金客户端
 *
 */
class MobileAction extends Action{

    public function _initialize() {
        $this->assign('isAdmin', 1);
        $this->assign('mobileJumpBox', 1);
        $this->assign('headTitle', '基金规则');
    }
    //主办活动基金
    public function apply_fund(){
        $this->assign('headTitle', '活动经费申请');
        $events = D('FundApplyfund')->eventList($this->mid);
        if(empty($events)){
            $this->assign('msg', '本人已发布、未完结的活动，才可申请资助资金');
        }
        $this->assign('events', $events);
        $this->display();
    }
    //主办活动基金 表单提交
    public function doapplyFund(){
        $dao = D('FundApplyfund');
        $res = $dao->doApply($this->mid);
        if ($res) {
            $this->success('申请成功，请等待审核');
        } else {
            $this->error($dao->getError());
        }
    }
    //承办活动基金
    public function apply_event(){
        $this->assign('headTitle', '接活动创收');
        $id = intval($_REQUEST['id']);
        $info = M('FundEvent')->where("eventId=$id")->find();
        $this->assign($info);
        $this->display();
    }
    //承办活动基金 表单
    public function applyEvent(){
        $this->assign('headTitle', '接活动创收');
        $dao = D('FundEvent');
        $eid = intval($_REQUEST['id']);
        $group = $dao->_canApplyGroup($eid,$this->mid);
        if(!$group){
            $this->assign('msg', $dao->getError());
        }
        foreach($group as &$val){
             $val['name'] = M('Group')->getField('name', 'id = ' . $val['gid']);
        }
        $this->assign('id', $eid);
        $this->assign('groups', $group);
        $this->display();
    }
    //承办活动基金 表单提交
    public function doapplyEvent() {
        $dao = D('FundApplyevent');
        $res = $dao->doApply($this->mid, $_POST);
        if ($res) {
            $this->success('申请成功，请等待审核');
        } else {
            $this->error($dao->getError());
        }
    }
    //创业基金 社长可申请
    public function apply_cy(){
        $this->assign('headTitle', '创业基金');
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $map['uid'] = $this->mid;
        $group = M('group')->where($map)->field('id,name')->order('id desc')->findAll();
        if(!$group){
            $this->assign('msg', '部落社长才可申请');
        }
        $this->assign('group', $group);
        $this->display();
    }
    //创业基金 表单提交
    public function doapplyCy(){
        $dao = D('FundgroupCyapply');
        $res = $dao->doApply($this->mid);
        if ($res) {
            $this->success('申请成功，请等待审核');
        } else {
            $this->error($dao->getError());
        }
    }
    
    /**
     * @todo 任务详情展示
     * @author zhuhaibing
     */
    public function applyRw(){
        $this->assign('headTitle', '任务基金');
        $id = intval($_REQUEST['id']);
        $info = M('FundgroupRw')->where("id=$id")->find();
        $this->assign($info);
        $this->display();
    }
    
    //任务基金
    public function apply_rw(){
        $this->assign('headTitle', '任务基金');
        $rwId = intval($_GET['id']);
        $this->assign('id', $rwId);
        $dao = D('FundgroupRw');
        $group = $dao->canApplyGroup($this->mid,$rwId);
        $this->assign('group', $group);
        if(!$group){
            $this->assign('msg', $dao->getError());
        }
        $this->display();
    }
    //任务基金 表单提交
    public function doapplyRw(){
        $dao = D('FundgroupRwapply');
        $res = $dao->doApply($this->mid);
        if ($res) {
            $this->success('申请成功，请等待审核');
        } else {
            $this->error($dao->getError());
        }
    }



}