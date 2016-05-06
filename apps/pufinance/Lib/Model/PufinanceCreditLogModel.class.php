<?php

/**
 * PU金明细模型
 */
class PufinanceCreditLogModel extends Model
{
    public function addCreditLog($uid, $type, $amount)
    {
        $data = array(
            'uid' => $uid,
            'type' => $type,
            'amount' => $amount,
            //'ctime' => 1456665321,
            'ctime' => time(),
        );
        return $this->add($data);
    }

    public function getCreditLogList($condition, $order = 'id DESC', $limit = '30')
    {
        return $this->where($condition)->order($order)->limit($limit)->select();
    }

    public function getCreditLogListOrderByMonthByUid($uid)
    {
        /**
        $start = $page * $months - 1;
        $end = ($page - 1) * $months - 1;
        // 开始时间 前start月第一天0点
        $stime = strtotime('midnight', strtotime("first day of -{$start} months"));
        // 截至时间 月底23:59:59（即下月初0点-1）
        $etime = strtotime('midnight', strtotime("first day of -{$end} months")) - 1;
         */
        $etime = strtotime('midnight', strtotime("first day of -5 months"));    // 目前只查当前时间半年前的（自然月）
        $condition = array(
            'uid' => $uid,
            'ctime' => array('egt', $etime)
        );
        $list = $this->getCreditLogList($condition, 'id DESC', null);
        $return = array();
        foreach ($list as $log) {
            $return[date('Ym', $log['ctime'])][] = $log;
        }
        return $return;
    }
	
	public function getCreditLogListWithUser($map,$order="cl.id desc"){
		return $this->table(C('DB_PREFIX').'pufinance_credit_log cl')
            ->field('cl.uid,cl.amount,cl.type,cl.ctime,u.realname,u.ctfid')
            ->join(C('DB_PREFIX').'pufinance_user u on u.uid=cl.uid')
            ->where($map)
            ->order($order)
            ->findPage(15);
	}


}