<?php

/**
 * PU金订单分期模型
 */
class PufinanceOrderStageModel extends Model
{

    public function getUserOrderStageList($condition, $order = 'etime ASC, id ASC')
    {
        $stageOrders = $this->where($condition)->order($order)->select();
        foreach ($stageOrders as &$stageOrder) {
            if ($stageOrder['status'] == 0 && $stageOrder['etime'] < time()) { // 逾期
                $overdue = D('PufinanceOrderOverdue')->getOrderOverdueByStageOrder($stageOrder);
                if ($overdue) {
                    $stageOrder['overdue'] = $overdue;
                }

            }

        }
        return $stageOrders;
    }

    public function createUserOrderStage($order)
    {
        if ($order['stage'] > 1) {
            $amount = bcmul($order['amount'], 100); // 分
            $freeAmount = bcmul($order['free_amount'], 100); // 分
            $interest = bcmul($order['interest'], 100); // 分

            $mod = fmod($amount, $order['stage']);  // 余数
            $freeMod = fmod($freeAmount, $order['stage']);
            $imod = fmod($interest, $order['stage']);

            $avgAmount = floor($amount/$order['stage']); // 平均数 舍去
            $avgFreeAmount = floor($freeAmount/$order['stage']);
            $avgInterest = floor($interest/$order['stage']);

            $stime = strtotime('midnight', $order['ctime']);
            for ($i = 0; $i < $order['stage']; $i++) {
                $data = array();
                if ($i == $order['stage'] - 1) { // 最后一期 + 余数
                    $data['amount'] = bcdiv($avgAmount + $mod, 100, 2);
                    $data['interest'] = bcdiv($avgInterest + $imod, 100, 2);
                    $data['last_amount'] = bcadd($data['amount'], $data['interest'], 2);
                    $data['free_amount'] = bcdiv($avgFreeAmount + $freeMod, 100, 2);
                } else {
                    $data['amount'] = bcdiv($avgAmount, 100, 2);
                    $data['interest'] = bcdiv($avgInterest, 100, 2);
                    $data['last_amount'] = bcadd($data['amount'], $data['interest'], 2);
                    $data['free_amount'] = bcdiv($avgFreeAmount, 100, 2);
                }
                $data['order_id'] = $order['id'];
                $data['uid'] = $order['uid'];
                $data['stime'] = $stime + $i * 30 * 86400; // 开始时间 零点
                $data['etime'] = $stime + ($i + 1) * 30 * 86400 - 1; // 30天
                $res = $this->add($data);
                if ($res === false) {
                    break;
                }
            }
            return $res;
        } else {    // 单期
            $data = array(
                'uid' => $order['uid'],
                'amount' => $order['amount'],
                'interest' => $order['interest'],
                'last_amount' => $order['last_amount'],
                'free_amount' => $order['free_amount'],
                'order_id' => $order['id'],
                'stime' => strtotime('midnight', $order['ctime']), // 开始时间 当天零点
                'etime' => strtotime('midnight', $order['ctime'] + 30 * 86400) - 1, // 30天
            );
            return $this->add($data);
        }
    }

    /**
     * 分页显示逾期订单详情
     * @todo滞纳金需要动态算，目前没写
     * */
    public function getStageOrderListByPage($map, $order="id desc")
    {
        //$beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'))-1;//昨天截止时间(精确到23:59:59)
        $lists = $this->table(C('DB_PREFIX')."pufinance_order_stage o")
            ->field('o.*, pu.realname,pu.ctfid,u.mobile')
            ->join(C('DB_PREFIX')."pufinance_user pu on o.uid=pu.uid")
            ->join(C('DB_PREFIX')."user u on pu.uid=u.uid")
            ->where($map)
            ->order($order)
            ->findPage(20);
        return $lists;
    }

    /**逾期订单结单处理
     * @param $id 分期订单id
     * @param $orderid 主订单id
     * @param
     */
    /*public function doSettle($id,$orderid)
    {
        $this->startTrans();
        // 进行相关的业务逻辑操作
        //更新分期订单状态为结清，剩余还款金额为0,滞纳金为0
        $stage = $this->where("id=$id")->find();
        $data['last_amount'] = 0;
        $data['overdue'] = 0;
        $data['status'] = 1;
        $res = $this->where("id=$id")->save($data);
        //更新主订单剩余还款金额=原金额-分期订单剩余还款金额 ;剩余分期数-1
        $map['id'] = $orderid;
        $update['last_amount'] = "last_amount-$stage[last_amount]";
        $update['last_stage'] = 'last_stage-1';
        $ups = D('PufinanceOrder')->where($map)->save($update);
        if($ups && $res)
        {
            //提交事务
            $this->commit();
            return true;
        }else
        {
            //事务回滚
            $this->rollback();
            return false;
        }
    }*/

    /**
     * @param integer $repayAmount 还款金额
     * @param array $condition
     *
     * @return array|bool
     */
    public function doRepayOrderStage($repayAmount, $condition)
    {
        $condition['status'] = 0;
        if ($repayAmount <= 0) {
            return false;
        }

        $stageOrders = $this->getUserOrderStageList($condition);
        $stageData = array();   // 分期订单待处理数据
        $puCredit = array();    // PU金 释放值
        $overdueData = array(); // 逾期订单待处理数据
        foreach ($stageOrders as $stageOrder) {
            // 优先还利息
            $repayInterest = 0;
            if (bccomp($stageOrder['last_amount'], $stageOrder['amount'], 2) > 0) {
                $lastInterest = bcsub($stageOrder['last_amount'], $stageOrder['amount'], 2);    // 剩余待还利息
                if (bccomp($repayAmount, $lastInterest, 2) > 0) {
                    $repayInterest = $lastInterest;
                } else {
                    $repayInterest = $repayAmount;
                }

            }

            if (bccomp($repayAmount, $stageOrder['last_amount'], 2) < 0) { // 未还完
                if (bccomp($repayAmount, 0, 2) <= 0) {
                    break;
                }

                $stageData[$stageOrder['order_id']][$stageOrder['id']] = array(
                    'last_amount' => bcsub($stageOrder['last_amount'], $repayAmount, 2),    // 剩余应还
                    'repay_amount' => $repayAmount    // 记录已还
                );
                $puCredit[$stageOrder['uid']]['amount'] = bcadd($puCredit[$stageOrder['uid']]['amount'], $repayAmount, 2);
                // 扣除还的利息
                $puCredit[$stageOrder['uid']]['interest'] = bcadd($puCredit[$stageOrder['uid']]['interest'], $repayInterest, 2);
                $repayAmount = 0;
                break;
            } elseif (bccomp($repayAmount, $stageOrder['last_amount'], 2) == 0) {
                // 结清 记录已还
                $stageData[$stageOrder['order_id']][$stageOrder['id']] = array(
                    'last_amount' => 0,
                    'repay_amount' => $repayAmount,
                );

                if (isset($stageOrder['overdue'])) { // 未还完
                    $overdueData['add'][] = end($stageOrder['overdue']['list']); // 记录应还滞纳金
                } else { // 没有逾期 订单结清
                    $stageData[$stageOrder['order_id']][$stageOrder['id']]['status'] = 1;
                    $puCredit[$stageOrder['uid']]['free_amount'] = bcadd($puCredit[$stageOrder['uid']]['free_amount'], $stageOrder['free_amount'], 2);
                }
                $puCredit[$stageOrder['uid']]['amount'] = bcadd($puCredit[$stageOrder['uid']]['amount'], $repayAmount, 2);
                // 扣除还的利息
                $puCredit[$stageOrder['uid']]['interest'] = bcadd($puCredit[$stageOrder['uid']]['interest'], $repayInterest, 2);
                $repayAmount = 0;
                break;
            } elseif (bccomp($repayAmount, $stageOrder['last_amount'], 2) > 0) {
                // 结清 记录已还
                $stageData[$stageOrder['order_id']][$stageOrder['id']] = array(
                    'last_amount' => 0,
                    'repay_amount' => $stageOrder['last_amount'],
                );

                $repayAmount = bcsub($repayAmount, $stageOrder['last_amount'], 2);     // 扣除该期应还

                if (isset($stageOrder['overdue'])) {    // 逾期
                    if (bccomp($repayAmount, $stageOrder['overdue']['all_last_overdue'], 2) < 0) { // 仍未还完
                        foreach ($stageOrder['overdue']['list'] as $overdueOrder) { // 逾期结清
                            if (bccomp($repayAmount, $overdueOrder['last_overdue'], 2) > 0) {
                                $lastOverdue = 0;
                                $repayAmount = bcsub($repayAmount, $overdueOrder['last_overdue'], 2);
                            } else {
                                $lastOverdue = bcsub($overdueOrder['last_overdue'], $repayAmount, 2);
                                $repayAmount = 0;
                            }

                            if ($overdueOrder['id']) {
                                $overdueData['update'][$overdueOrder['id']] = array(
                                    'last_overdue' => $lastOverdue,
                                );
                            } else {
                                $overdueOrder['last_overdue'] = $lastOverdue;
                                $overdueData['add'][] = $overdueOrder;
                            }
                        }
                    } elseif (bccomp($repayAmount, $stageOrder['overdue']['all_last_overdue'], 2) == 0) {
                        // 扣除逾期
                        $repayAmount = 0;
                        // 订单结清
                        $stageData[$stageOrder['order_id']][$stageOrder['id']]['status'] = 1;
                        $puCredit[$stageOrder['uid']]['free_amount'] = bcadd($puCredit[$stageOrder['uid']]['free_amount'], $stageOrder['free_amount'], 2);
                        foreach ($stageOrder['overdue']['list'] as $overdueOrder) { // 逾期结清

                            if ($overdueOrder['id']) {
                                $overdueData['update'][$overdueOrder['id']] = array(
                                    'last_overdue' => 0,
                                );
                            } else {
                                $overdueOrder['last_overdue'] = 0;
                                $overdueData['add'][] = $overdueOrder;
                            }
                        }
                    } elseif (bccomp($repayAmount, $stageOrder['overdue']['all_last_overdue'], 2) > 0) {
                        // 扣除逾期
                        $repayAmount = bcsub($repayAmount, $stageOrder['overdue']['all_last_overdue'], 2);

                        // 订单结清
                        $stageData[$stageOrder['order_id']][$stageOrder['id']]['status'] = 1;
                        $puCredit[$stageOrder['uid']]['free_amount'] = bcadd($puCredit[$stageOrder['uid']]['free_amount'], $stageOrder['free_amount'], 2);
                        foreach ($stageOrder['overdue']['list'] as $overdueOrder) { // 逾期结清

                            if ($overdueOrder['id']) {
                                $overdueData['update'][$overdueOrder['id']] = array(
                                    'last_overdue' => 0,
                                );
                            } else {
                                $overdueOrder['last_overdue'] = 0;
                                $overdueData['add'][] = $overdueOrder;
                            }
                        }
                    }
                } else {
                    // 订单结清
                    $stageData[$stageOrder['order_id']][$stageOrder['id']]['status'] = 1;
                    $puCredit[$stageOrder['uid']]['free_amount'] = bcadd($puCredit[$stageOrder['uid']]['free_amount'], $stageOrder['free_amount'], 2);
                }
                $puCredit[$stageOrder['uid']]['amount'] = bcadd($puCredit[$stageOrder['uid']]['amount'], $stageOrder['last_amount'], 2);
                // 扣除还的利息
                $puCredit[$stageOrder['uid']]['interest'] = bcadd($puCredit[$stageOrder['uid']]['interest'], $repayInterest, 2);
            }
        }
//dump($repayAmount);
        return array(
            $stageData,
            $overdueData,
            $puCredit,
            $repayAmount,   // 多余的金额（有人脑残多还钱了）
        );
    }
}