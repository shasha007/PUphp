<?php

/***
 * 创业基金申请
 */
class FundgroupCyapplyModel extends Model{

    //后台列表
    public function backList($map,$field='*') {
        $list = $this->where($map)->field($field)->order('id desc')->findPage(10);
        $stats = array('-1'=>'被驳回','0'=>'待审核','1'=>'待发放','2'=>'已发放');
        foreach($list['data'] as &$v){
            $group  = M('group')->where('id='.$v['gid'])->field('name,school')->find();
            $v['gname'] = $group['name'];
            $v['sid'] = $group['school'];
            $v['statusName'] = $stats[$v['status']];
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
        $apply = $this->where("id=$id")->field('id,uid,title')->find();
        $notify_dao = service('Notify');
        $notify_data['title'] = $apply['title'];
        $notify_data['reason'] = $reason;
        $notify_dao->sendIn($apply['uid'], 'fund_cy_reject', $notify_data);
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
        //参数合法性检查
        $required_field = array(
            'gid' => '申请部落',
            'title' => '项目名称',
            'mobile' => '负责人联系方式',
            'partner' => '合伙人姓名',
            'partnerContact' => '合伙人联系方式',
            'needMoney' => '需求资金',
            'period' => '资金使用周期',
        );
        foreach ($required_field as $k => $v) {
            if (empty($input[$k])) {
                $this->error = $v . '不可为空';
                return false;
            }
        }
        $needMoney = t($input['needMoney']) * 100 / 100;
        if ($needMoney<1 || $needMoney>500000) {
            $this->error = '需求资金（1-50 0000元）';
            return false;
        }
        $period = intval($input['period']);
        if ($period<1 || $period>12) {
            $this->error = '资金使用周期（1-12月）';
            return false;
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
        $gid = intval($input['gid']);
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $map['uid'] = $mid;
        $map['id'] = $gid;
        $group = M('group')->where($map)->field('id')->find();
        if (!$group) {
            $this->error = '部落错误';
            return false;
        }
        $data['title'] = t($input['title']);
        $data['gid'] = $gid;
        $data['uid'] = $mid;
        $data['mobile'] = t($input['mobile']);
        $data['partner'] = t($input['partner']);
        $data['partnerContact'] = t($input['partnerContact']);
        $data['needMoney'] = $needMoney;
        $data['period'] = $period;
        $data['cTime'] = time();
        $res = $this->add($data);
        if ($res) {
            return true;
        } else {
            $this->error = '操作失败';
            return false;
        }
    }
    public function myCy($mid,$page,$count){
        $offset = ($page - 1) * $count;
        $map['uid'] = $mid;
        $res = $this->where($map)->field('title,status as state,rejectReason')->order('id desc')->limit("$offset,$count")->findAll();
        return $res;
    }
}
?>
