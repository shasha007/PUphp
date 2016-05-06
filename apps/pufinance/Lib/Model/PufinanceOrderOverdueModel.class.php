<?php


class PufinanceOrderOverdueModel extends Model
{
    public function getOrderOverdueByStageOrder($stageOrder)
    {
        $condition = array(
            'uid' => $stageOrder['uid'],
            'order_stage_id' => $stageOrder['id']
        );
        $list = $this->where($condition)->order('id ASC')->select();
        if (empty($list)) {
            // 计算滞纳金
            $stime = $stageOrder['etime'] + 1;  // 开始时间
            $overdueTime = $stime + 7 * 86400;  // 滞纳金计算开始时间
            if (time() <= $overdueTime) { //7天的逾期不计费
                return false;
            }
        } else {
            $last = end($list);
            $stime = $last['etime'] + 1;
            $overdueTime = $stime;
        }

        if ($stageOrder['last_amount'] > 0) {
            $overdueOrder = array(
                'uid' => $stageOrder['uid'],
                'order_stage_id' => $stageOrder['id'],
                'stime' => $stime,
                'etime' => strtotime('midnight') - 1, // 至今
                'ctime' => time(),
                'last_amount' => $stageOrder['last_amount'],
            );

            $days = floor((time() - $overdueTime) / 86400); // 当天不算逾期费
            $overdue = ceil(bcmul(bcmul($stageOrder['last_amount'], 100), 5 / 1000 * $days, 2)) / 100;
            $overdueOrder['overdue'] = $overdueOrder['last_overdue'] = $overdue;
            $list[] = $overdueOrder;
        }

        $return = array(
            'all_last_overdue' => 0,
            'list' => $list,
        );
        foreach ($list as $item) {
            $return['all_last_overdue'] = bcadd($return['all_last_overdue'], $item['last_overdue'], 2);
        }
        return $return;
    }
}