<?php

/***
 * 任务基金申请
 */
class FundgroupRwapplyModel extends Model{

    //后台列表
    public function backList($map,$field='*') {
        $list = $this->where($map)->field($field)->order('id desc')->findPage(10);
        $stats = array('-1'=>'被驳回','0'=>'待审核','1'=>'待发放','2'=>'已发放');
        $dao = M('FundgroupRw');
        foreach($list['data'] as &$v){
            $group  = M('group')->where('id='.$v['gid'])->field('name,school')->find();
            $v['gname'] = $group['name'];
            $v['sid'] = $group['school'];
            $v['statusName'] = $stats[$v['status']];
            $rw = $dao->where('id='.$v['rwId'])->field('title,company')->find();
            $v['title'] = $rw['title'];
            $v['company'] = $rw['company'];
        }
        return $list;
    }
    //审核通过
    public function doPass($id) {
        $data['status'] = 1;
        $res = $this->where("id=$id")->save($data);
        if(!$res){
            $this->error = '操作失败';
            return FALSE;
        }
        return TRUE;
    }
    //审核驳回
    public function doReject($id,$reason) {
        $data['status'] = -1;
        $data['rejectReason'] = $reason;
        $res = $this->where("id=$id")->save($data);
        if(!$res){
            $this->error = '操作失败';
            return FALSE;
        }
        $apply = $this->where("id=$id")->field('uid,rwId')->find();
        $notify_dao = service('Notify');
        $notify_data['title'] = M('FundgroupRw')->getField('title', 'id='.$apply['rwId']);
        $notify_data['reason'] = $reason;
        $notify_dao->sendIn($apply['uid'], 'fund_rw_reject', $notify_data);
        return TRUE;
    }
    //发放
    public function giveMoney($id,$money) {
        $data['status'] = 2;
        $data['getMoney'] = $money;
        $res = $this->where("id=$id")->save($data);
        if(!$res){
            $this->error = '操作失败'.$this->getLastSql();
            return FALSE;
        }
        return TRUE;
    }
    //申请
    public function doApply($mid) {
        $input = $_POST;
        $rwId = intval($input['id']);
        $gid = intval($input['gid']);
        $canApply = D('FundgroupRw')->canApply($mid,$rwId,$gid);
        if(!$canApply){
            $this->error = D('FundgroupRw')->getError();
            return false;
        }
        //参数合法性检查
        $required_field = array(
            'gid' => '申请部落',
            'mobile' => '负责人联系方式',
        );
        foreach ($required_field as $k => $v) {
            if (empty($input[$k])) {
                $this->error = $v . '不可为空';
                return false;
            }
        }
        if (!empty($_FILES['attach']['name'])) {
            $info = X('Xattach')->upload('fund_cy');
            if ($info['status']) {
                //附件Id
                $attachId = $info['info'][0]['id'];
                $data['attachId'] = $attachId;
            }else{
                $this->error = $info['info'];
                return false;
            }
        }
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $map['uid'] = $mid;
        $map['id'] = $gid;
        $group = M('group')->where($map)->field('id')->find();
        if (!$group) {
            $this->error = '部落错误';
            return false;
        }
        $data['rwId'] = $rwId;
        $data['gid'] = $gid;
        $data['uid'] = $mid;
        $data['mobile'] = t($input['mobile']);
        $data['partner'] = t($input['partner']);
        $data['partnerContact'] = t($input['partnerContact']);
        $data['cTime'] = time();
        $res = $this->add($data);
        if ($res) {
            return true;
        } else {
            $this->error = '操作失败';
            return false;
        }
    }
    public function myRw($mid,$page,$count){
        $offset = ($page - 1) * $count;
        $map['uid'] = $mid;
        $res = $this->where($map)->field('rwId,gid,status as state,rejectReason')->order('id desc')->limit("$offset,$count")->findAll();
        foreach($res as &$v){
            $v['title'] = '活动已删除';
            $title = M('FundgroupRw')->getField('title', 'id='.$v['rwId']);
            if($title){
                $v['title'] = $title;
            }
            $v['groupName'] = '';
            $groupName = M('group')->getField('name', 'id='.$v['gid']);
            if($groupName){
                $v['groupName'] = $groupName;
            }
        }
        return $res;
    }
}
?>
