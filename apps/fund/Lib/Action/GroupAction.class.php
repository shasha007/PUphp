<?php

/*
 * 部落基金后台管理
 *
 */
import('home.Action.PubackAction');
class GroupAction extends PubackAction {

    public function _initialize() {
        $this->assign('isAdmin', 1);
    }
    //投资基金管理  二
    public function index() {
//        $res = D('FundgroupSponsor')->field('id,company,type,money,is_activ')->findPage(10);
//        $this->assign($res);
//        $this->assign('menu', 1);
//        $this->assign('menu2', 1);
//        $this->display();
        forword('cyList', 'Group','fund',true);
    }
    //添加投资基金
    public function sponsorAdd() {
        $provs = M('province')->order('short asc')->findAll();
        $this->assign('provs', $provs);
        $this->assign('menu', 1);
        $this->assign('menu2', -1);
        $id = intval($_GET['id']);
        if ($id > 0) {
            $map['id'] = $id;
            $res = M('FundgroupSponsor')->where($map)->find();
            if ($res) {
                $this->assign($res);
                if($res['attachId']>0){
                    $this->assign('attach',getAttach($res['attachId']));
                }
                $this->assign('areas',D('FundgroupSponsorSchool')->editbarSchool($id));
            }
        }
        $this->display();
    }
    public function getCitys() {
        $provId = intval($_GET['provId']);
        $citys = M('citys')->where("pid=$provId")->field('id,city')->order('short asc')->findAll();
        echo json_encode($citys);die;
    }
    public function doAddSponsor() {
        $dao = D('FundgroupSponsor');
        $res = $dao->doAdd();
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }
    public function cyList(){
        $this->assign('menu', 1);
        $dao = D('FundgroupCyapply');
        $status = intval($_GET['status']);
        $this->assign('menu2', $status+2);
        $map['status'] = $status;
        $list = $dao->backList($map,'id,title,gid,uid,status,cTime,attachId');
        $this->assign($list);
        $this->display('cyList');
    }
    //创业基金申请详情
    public function cyDetail() {
        $map['id'] = intval($_GET['id']);
        $obj = D('FundgroupCyapply')->where($map)->find();
        if($obj){
            $group  = M('group')->where('id='.$obj['gid'])->field('name,school')->find();
            $obj['gname'] = $group['name'];
            $obj['sid'] = $group['school'];
        }
        $this->assign($obj);
        $this->display();
    }
    //创业基金审核通过
    public function cyPass() {
        $id = intval($_POST['id']);
        $dao = D('FundgroupCyapply');
        $res = $dao->doPass($id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }
    //创业基金审核驳回
    public function cyReject() {
        $reason = t($_POST['reason']);
        if (empty($reason)) {
            $this->error('请填写驳回原因');
        }
        $id = intval($_POST['id']);
        $dao = D('FundgroupCyapply');
        $res = $dao->doReject($id,$reason);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }
    //创业基金发放
    public function giveMoney() {
        $getMoney = $_POST['getMoney']*100/100;
        $id = intval($_POST['id']);
        $dao = D('FundgroupCyapply');
        $res = $dao->giveMoney($id,$getMoney);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }
    //任务基金管理
    public function rwList() {
        $map['isdel'] = 0;
        $res = D('FundgroupRw')->backList($map,'id,company,title,applyTime,attachId,needMoney');
        $this->assign($res);
        $this->assign('menu', 2);
        $this->assign('menu2', 1);
        $this->display();
    }
    //添加任务基金
    public function rwAdd() {
        $provs = M('province')->order('short asc')->findAll();
        $this->assign('provs', $provs);
        $this->assign('menu', 2);
        $this->assign('menu2', -1);
        $id = intval($_GET['id']);
        if ($id > 0) {
            $map['id'] = $id;
            $res = M('FundgroupRw')->where($map)->find();
            if ($res) {
                $this->assign($res);
                if($res['attachId']>0){
                    $this->assign('attach',getAttach($res['attachId']));
                }
                $this->assign('areas',D('FundgroupRwSchool')->editbarSchool($id));
            }
        }
        $this->display();
    }
    public function doAddRw() {
        $dao = D('FundgroupRw');
        $res = $dao->doAdd();
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }
    public function delRw() {
        $id = intval($_POST['id']);
        $dao = D('FundgroupRw');
        $res = $dao->doDel($id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }
    //任务基金申请列表
    public function rwApply(){
        $this->assign('menu', 2);
        $this->assign('menu2', 2);
        $dao = D('FundgroupRwapply');
        $status = intval($_GET['status']);
        $this->assign('menu3', $status);
        $map = array();
        if($status){
            $map['status'] = $status-1;
        }
        $list = $dao->backList($map,'id,rwId,gid,uid,status,cTime,attachId');
        $this->assign($list);
        $this->display('rwApply');
    }
    //任务基金申请详情
    public function rwDetail() {
        $map['id'] = intval($_GET['id']);
        $obj = D('FundgroupRwapply')->where($map)->find();
        if($obj){
            $group  = M('group')->where('id='.$obj['gid'])->field('name,school')->find();
            $obj['gname'] = $group['name'];
            $obj['sid'] = $group['school'];
        }
        $this->assign($obj);
        $this->display();
    }
    //任务基金审核通过
    public function rwPass() {
        $id = intval($_POST['id']);
        $dao = D('FundgroupRwapply');
        $res = $dao->doPass($id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }
    //任务基金审核驳回
    public function rwReject() {
        $reason = t($_POST['reason']);
        if (empty($reason)) {
            $this->error('请填写驳回原因');
        }
        $id = intval($_POST['id']);
        $dao = D('FundgroupRwapply');
        $res = $dao->doReject($id,$reason);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }
    //任务基金发放
    public function giveRwMoney() {
        $getMoney = $_POST['getMoney']*100/100;
        $id = intval($_POST['id']);
        $dao = D('FundgroupRwapply');
        $res = $dao->giveMoney($id,$getMoney);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }
    public function test() {
        $sid = 473;
        $map['_string'] = 'china=1 OR provId=1 OR cityId=2 OR sid=473';
        $list = M('')->table("ts_fundgroup_sponsor AS a ")
                ->join("ts_fundgroup_sponsor_school AS b ON a.id=b.sponsorId")
                ->where($map)->group('a.id')->findpage(2);
var_dump(M('')->getLastSql());
var_dump($list);
    }

}

?>
