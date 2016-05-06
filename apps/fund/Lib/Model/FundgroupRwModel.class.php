<?php

/*
 * 任务基金
 *
 */

class FundgroupRwModel extends Model {

    //添加任务基金
    public function doAdd() {
        $data = array();
        $required_field = array(
            'title' => '任务名称',
            'needMoney' => '任务奖金',
            'stime' => '开始时间',
            'applyTime' => '截止申领日期',
            'company' => '公司名称',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k])) {
                $this->error = $v . '不可为空';
                return FALSE;
            }
        }
        $id = intval($_POST['id']);
        if ($id == 0 && (!isset($_POST['imgid1'][0]) || $_POST['imgid1'][0] <= 0)) {
            $this->error = '请上传公司logo';
            return FALSE;
        }
        $data['company'] = t($_POST['company']);
        $data['title'] = t($_POST['title']);
        $data['needMoney'] = $_POST['needMoney'] * 100 / 100;
        $data['stime'] = strtotime($_POST['stime']);
        $data['applyTime'] = strtotime($_POST['applyTime']);
        $data['content'] = t(h($_POST['content']));
        $data['cTime'] = time();
        if (isset($_POST['imgid1'][0]) && $_POST['imgid1'][0] > 0) {
            $data['attachId'] = intval($_POST['imgid1'][0]);
        }
        if ($id > 0) {
            $this->where("id=$id")->save($data);
        } else {
            $id = $this->add($data);
        }
        if (!$id) {
            $this->error = '写入失败';
            return FALSE;
        }
        D('FundgroupRwSchool')->addSchool($id);
        return true;
    }

    //删除
    public function doDel($id) {
        $hasApply = M('FundgroupRwapply')->where('rwId=' . $id)->field('id')->find();
        if (!$hasApply) {
            return $this->where('id=' . $id)->delete();
        }
        return $this->setField('isdel', 1, 'id=' . $id);
    }

    //后台列表
    public function backList($map, $field = '*') {
        $list = $this->where($map)->field($field)->order('id desc')->findPage(10);
        $stats = array('-1' => '被驳回', '0' => '待审核', '1' => '待发放', '2' => '已发放');
        $daoApply = M('FundgroupRwapply');
        foreach ($list['data'] as &$v) {
            $group = M('group')->where('id=' . $v['gid'])->field('name,school')->find();
            $v['gname'] = $group['name'];
            $v['sid'] = $group['school'];
            $v['statusName'] = $stats[$v['status']];
            $v['waitCnt'] = $daoApply->where('status=0 and rwId=' . $v['id'])->count();
            $v['passCnt'] = $daoApply->where('status>0 and rwId=' . $v['id'])->count();
        }
        return $list;
    }

    //api各学校任务基金列表
    public function apiListBySid($sid, $page, $limit = 6) {
        $map['a.applyTime'] = array('gt', time());
        $map['_string'] = 'china=1 OR sid='.$sid;
        $cityId = M('school')->getField('cityId', 'id='.$sid);
        if($cityId){
            $map['_string'] .= ' OR cityId='.$cityId;
            $provId = M('citys')->getField('pid', 'id='.$cityId);
            if($provId){
                $map['_string'] .= ' OR provId='.$provId;
            }
        }
        $offset = ($page - 1) * $limit;
        $order = 'a.applyTime asc, a.id DESC';
        $res = M('')->table("ts_fundgroup_rw AS a ")
                        ->join("ts_fundgroup_rw_school AS b ON a.id=b.rwId")
                        ->field('a.id,a.title,a.company as companyName,a.attachId as pic,applyTime as byTime,needMoney as money')
                        ->where($map)->order($order)->group('a.id')->limit("$offset,$limit")->findAll();
        if (!$res) {
            return array();
        }
        return $res;
    }
    //是否可以申请
    public function canApply($mid,$rwId,$gid){
        if(!$this->_canMidApplyRwid($mid, $rwId)){
            return false;
        }
        if($gid<=0){
            $this->error = '请选择部落';
            return false;
        }
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $map['uid'] = $mid;
        $map['id'] = $gid;
        $group = M('group')->where($map)->field('id')->find();
        if(!$group){
            $this->error = '部落错误';
            return false;
        }
        $hasApply = M('FundgroupRwapply')->where('status!=-1 and rwId='.$rwId.' and gid='.$gid)->field('id')->find();
        if($hasApply){
            $this->error = '该部落已申请此活动';
            return false;
        }
        return true;
    }
    private function _canMidApplyRwid($mid,$rwId) {
        if($rwId<=0){
            $this->error = '请选择任务';
            return false;
        }
        $rw = $this->where('isdel=0 and id='.$rwId)->field('id,applyTime')->find();
        if(!$rw){
            $this->error = '任务不存在，或已删除';
            return false;
        }
        if($rw['applyTime']<time()){
            $this->error = '任务申领已截止';
            return false;
        }
        $sid = getUserField($mid, 'sid');
        $isSchoolRw = D('FundgroupRwSchool')->isSchoolRw($sid,$rwId);
        if(!$isSchoolRw){
            $this->error = '任务不对您所在学校开放';
            return false;
        }
        return true;
    }
    public function canApplyGroup($mid,$rwId){
        if(!$this->_canMidApplyRwid($mid, $rwId)){
            return array();
        }
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $map['uid'] = $mid;
        $group = M('group')->where($map)->field('id,name')->order('id desc')->findAll();
        if(!$group){
            $this->error = '部落社长才可申请';
            return array();
        }
        $hasApply = M('FundgroupRwapply')->where('status!=-1 and rwId='.$rwId.' and uid='.$mid)->field('gid')->findAll();
        $hasGids = getSubByKey($hasApply, 'gid');
        foreach ($group as $k=>$v) {
            if(in_array($v['id'], $hasGids)){
                unset($group[$k]);
            }
        }
        if(empty($group)){
            $this->error = '您已申请';
            return array();
        }
        return $group;
    }

}
