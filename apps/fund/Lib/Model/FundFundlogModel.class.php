<?php

/*
 * 申请基金日志
 *
 */

class FundFundlogModel extends Model {

    //记录日志
    public function addFundlog($uid , $fundId, $state) {
        $data['uid'] = $uid;
        $data['fundId'] = $fundId;
        $data['state'] = $state;
        $data['cTime'] = time();
        return $this->add($data);
    }

    //前台申请/审核动态
    public function logList($state) {
        $map['state'] = array('in', $state);
        $res = $this->where($map)->field('fundId,state,cTime')->order('id desc')->findPage(10);
        foreach ($res['data'] as $k => &$v) {
            $fund = M('FundApplyfund')->where('id=' . $v['fundId'])->field('eventId,fund,audltFund')->find();
            if (!$fund) {
                unset($res['data'][$k]);
                continue;
            }
            $v['stateName'] = '申请活动基金';
            $v['fund'] = $fund['fund'];
            if ($v['state'] == 1) {
                $v['stateName'] = '申请活动基金，审核通过';
                $v['fund'] = $fund['audltFund'];
            }
            $v['audltFund'] = $fund['audltFund'];
            $v['eventName'] = M('Event')->getField('title', 'id=' . $fund['eventId']);
        }
        return $res;
    }

}

?>
