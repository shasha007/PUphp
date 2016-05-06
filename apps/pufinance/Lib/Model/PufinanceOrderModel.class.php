<?php

/**
 * PU金主订单模型
 * Created by PhpStorm.
 * User: yangjun
 * Date: 2016/3/22
 * Time: 14:35
 */
class PufinanceOrderModel extends Model
{
    protected $tableName = 'pufinance_order';

    public function getUserOrderList($condition, $fields = '*', $order = 'id ASC')
    {
        $list = $this->where($condition)->field($fields)->order($order)->select();
        if (empty($list)) {
            return array();
        }
        $return = array();
        foreach ($list as $order) {
            $return[$order['id']] = $order;
        }
        return $return;
    }

    /**
     * 通过uid获取用户应还订单列表
     * last_amount 包含逾期滞纳金
     *
     * @param integer $uid
     *
     * @return array
     */
    /*public function getUserLastOrderListByUid($uid)
    {
        $condition = array(
            'uid' => $uid,
            'status' => 4,  // 放款成功（即待还款）
        );
        $list = $this->getUserOrderList($condition);
        if ($list) {
            foreach ($list as &$order) {
                $order['last_amount'] = 0;
                $order['all_last_overdue'] = 0;
                $stageList = D('PufinanceOrderStage')->getUserOrderStageList(array('order_id' => $order['id'], 'status' => 0));
                foreach ($stageList as &$stageOrder) {
                    if (isset($stageOrder['overdue'])) {
                        $stageOrder['last_amount'] = bcadd($stageOrder['last_amount'], $stageOrder['overdue']['all_last_overdue'], 2);
                        $order['all_last_overdue'] = bcadd($order['all_last_overdue'], $stageOrder['overdue']['all_last_overdue'], 2);
                    }
                    $order['last_amount'] = bcadd($order['last_amount'], $stageOrder['last_amount'], 2);

                }
                $order['stage_list'] = $stageList;
            }
        }
        return $list;
    }*/

    /**
     * 通过uid获取用户应还列表并对数据整理
     *
     * @param integer $uid
     *
     * @return array
     */
    public function getUserAllLastAmountByUid($uid)
    {
        $return = array(
            'all_last_amount' => 0,
            'list' => array(),
        );
        $condition = array(
            'uid' => $uid,
            'status' => 4,  // 放款成功（即待还款）
            'type' => array('in', array(1,2))
        );
        $list = $this->getUserOrderList($condition);
        if ($list) {
            foreach ($list as &$order) {
                $order['last_amount'] = 0;
                $order['all_last_overdue'] = 0;
                $stageList = D('PufinanceOrderStage')->getUserOrderStageList(array('order_id' => $order['id'], 'status' => 0));
                foreach ($stageList as &$stageOrder) {
                    if (isset($stageOrder['overdue'])) {
                        $stageOrder['last_amount'] = bcadd($stageOrder['last_amount'], $stageOrder['overdue']['all_last_overdue'], 2);
                        $order['all_last_overdue'] = bcadd($order['all_last_overdue'], $stageOrder['overdue']['all_last_overdue'], 2);
                    }
                    $order['last_amount'] = bcadd($order['last_amount'], $stageOrder['last_amount'], 2);

                }
                $order['stage_list'] = $stageList;
                $return['all_last_amount'] = bcadd($return['all_last_amount'], $order['last_amount'], 2);
            }
            $return['list'] = $list;
        }
        return $return;
    }

    public function getUserMonthLastAmount($uid)
    {
        $return = array(
            'all_last_amount' => 0, // 总应还款额
            'list' => array(), // 所有本月应还订单
        );

        $condition = array(
            'uid' => $uid,
            'status' => 4,  // 放款成功（即待还款）
            'type' => array('in', array(1,2))
        );
        $list = $this->getUserOrderList($condition);


        // 本月最后一天 23:59:59
        $lastday = strtotime('midnight', strtotime("first day of next month"))-1;

        $condition = array(
            'order_id' => array('in', array_column($list, 'id')),
            'status' => 0,
            'etime' => array('elt', $lastday),
        );
        $stageList = D('PufinanceOrderStage')->getUserOrderStageList($condition);
        if ($stageList) {
            foreach ($stageList as &$stageOrder) {
                if (isset($stageOrder['overdue'])) { // 逾期
                    $stageOrder['last_amount'] = bcadd($stageOrder['last_amount'], $stageOrder['overdue']['all_last_overdue'], 2);
                }

                $return['all_last_amount'] = bcadd($return['all_last_amount'], $stageOrder['last_amount'], 2);
                
                // 主订单数据
                $stageOrder['all_amount'] = $list[$stageOrder['order_id']]['amount']; // 总借款金额
                $stageOrder['bank_card_id'] = $list[$stageOrder['order_id']]['bank_card_id']; // 借款账户

            }

        }
        reset($stageList); // 重置指针
        $return['list'] = $stageList;
        return $return;
    }

    /**
     * 分页显示订单详情
     * */
    public function getOrderListByPage($map, $order="o.id desc")
    {
        $lists = $this->table(C('DB_PREFIX')."pufinance_order o")
            ->field('o.*,u.realname,u.ctfid,c.status nameList')
            ->join(C('DB_PREFIX').'pufinance_user u on o.uid=u.uid')
            ->join(C('DB_PREFIX').'pufinance_credit c on o.uid=c.uid')
            ->join(C('DB_PREFIX').'pufinance_bankcard bc on bc.uid=u.uid AND bc.id=o.bank_card_id')
            ->where($map)
            ->order($order)
            ->findPage(20);
        foreach($lists['data'] as &$v)
        {
            $v['area'] = getUserSchoolName($v['uid']);
            if ($v['bank_card_id']) {
                $banks = D('PufinanceBankcard')->getUserUsableBankcardListByUid($v['uid']);
                $v['bank_card_info'] = $banks[$v['bank_card_id']];
            }

        }
        unset($v);
        return $lists;
    }

    /**h5pu金订单
     *
     */
    public function getPuOrderList($uid,$page="1",$count="10",$order="id desc")
    {
        $map['uid'] = $uid;
        $offset = ($page - 1) * $count;
        $list = $this->field('uid,bank_card_id card,amount,last_amount,stage,last_stage,status,type')
            ->where($map)
            ->limit("$offset,$count")
            ->order($order)
            ->select();
        foreach($list as &$v)
        {
            $v['cardmsg'] = get_order_bank($v['card']);

        }
        return $list;
    }

    /**
     * 风控通过
     * @param $id
     * @return bool
     */
    public function doWindCon($id)
    {
        $data = $this->where("id=$id")->find();
        if(!$data)
        {
            return false;
        }
        $this->startTrans();
        // 进行相关的业务逻辑操作
        $Info = D('PufinanceOrderStage'); // 实例化分期订单对象
        $res = $Info->createUserOrderStage($data); // 生成分期订单
        $update['status'] = 2;
        $ups = $this->where("id=$id")->save($update);//更新主订单状态为风控通过
        $sa = D('PufinanceCredit')->addWhiteList($data['uid'],1);
        if ($res && $ups !== false && $sa !== false)
        {
            // 提交事务
            $this->commit();
            return true;
        }else
        {
            // 事务回滚
            $this->rollback();
            return false;
        }
    }

    /**驳回
     * @param $id
     * @return bool
     */
    public function doOverRule($id)
    {
        $order = $this->getById($id);

        try {
            $update['status'] = 1;
            $this->startTrans();
            $ups = $this->where("id=$id")->save($update);//更新订单状态为驳回
            if ($ups === false) {
                throw_exception('更新订单状态失败');
            }
            $res = D('PufinanceCredit')->addWhiteList($order['uid'], 2);
            if ($res === false) {
                throw_exception('黑名单设置失败');
            }

            // 更新用户PU金可用部分的数据
            //$usableAmount = bcadd($order['amount'], $amount, 2); // 剩余可用额度
            //$freeUsableAmount = bcsub($order['free_amount'], $freeAmount, 2); // 剩余可用免息额
            $res = D('PufinanceCredit')->updatePufinanceCredit($order['uid'], array(
                'usable_amount' => array('exp', 'usable_amount+' . $order['amount']),
                'free_usable_amount' => array('exp', 'free_usable_amount+' . $order['free_amount']),
            ));
            if ($res === false) {
                throw_exception('更新用户PU金失败');
            }
            if ($order['bank_card_id']) {
                $banks = D('PufinanceBankcard')->getUserUsableBankcardListByUid($order['uid']);
                $type = 'repay_overrule_' . $banks[$order['bank_card_id']]['bank_id'];
            } else {
                $type = 'repay_overrule_pumoney';
            }

            //$type
            $res = D('PufinanceCreditLog')->addCreditLog($order['uid'], $type, $order['amount']);
            if ($res === false) {
                throw_exception('写入PU金记录失败');
            }
            $this->commit();
            return true;
        } catch (ThinkException $e) {
            $this->rollback();
            return false;
        }
        return false;
    }

    /**
     * 确认放款
     *
     * @param integer $id
     *
     * @return boolean
     */
    public function doLend($id)
    {
        $order = $this->getById($id);
        if ($order['status'] == 2) { // 待放款
            $update = array(
                'status' => 4,
                'lend_time' => time(),
            );
            return $this->where(array('id' => $id))->save($update);
        }

        return false;
    }

    /**订单结清
     * @param $id
     * @return bool
     */
    /*public function doSettle($id)
    {
        $this->startTrans();
        // 进行相关的业务逻辑操作
        $update['status'] = 5;
        $update['last_amount'] = 0;
        $update['last_stage'] = 0;
        $ups = $this->where("id=$id")->save($update);//更新主订单状态为结清，剩余还款金额和剩余分期数为0
        //更新分期订单状态为结清，剩余还款金额为0
        $data['last_amount'] = 0;
        $data['status'] = 1;
        $res = D('PufinanceOrderStage')->where("order_id=$id")->save($data);
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

    public function addUserOrder($uid, $order, $status = 1, $type = 1)
    {
        $data = array(
            'uid' => $uid,
            'bank_card_id' => $order['bank_card_id'],
            'repay_bank_card_id' => $order['repay_bank_card_id'],
            'amount' => $order['amount'],
            'free_amount' => $order['free_amount'],
            'stage' => $order['stage'],
            'last_stage' => $order['stage'],
            'reson' => $order['reson'],
            //'ctime' => 1456195905,
            'ctime' => isset($order['ctime']) && $order['ctime'] ? $order['ctime'] : time(),
            'status' => $status,
            'type' => $type,
        );

        $interest = calc_interest($order['amount'], $order['free_amount'], $order['stage']);
        $data['last_amount'] = bcadd($order['amount'], $interest, 2);    // 本金+利息
        $data['interest'] = $interest;
        //return $data;
        $res = $this->add($data);
        if ($res) {
            $data['id'] = $res;
            return $data;
        }
        return false;
    }

    public function doRepayOrder($repayAmount, $orderId = 0, $uid = 0)
    {
        $condition = array();
        if ($orderId) {
            $condition['order_id'] = $orderId;
        }

        if ($uid) {
            $condition['uid'] = $uid;
        }
        if (empty($condition) || $repayAmount <= 0) {
            return false;
        }
        $data = D('PufinanceOrderStage')->doRepayOrderStage($repayAmount, $condition);
        //dump($data);

        try {
            $this->startTrans();
            $this->updateStageData($data, 'repay_sys');

            $this->commit();
            return array('status' => true);
        } catch (ThinkException $e) {
            $this->rollback();
            return array('status' => false, 'info' => $e->getMessage());
        }
    }

    public function updateStageData($data, $type)
    {
        //throw_exception('throw');
        list($stageData, $overdueData, $puCredit) = $data;
        if ($stageData) {
            foreach ($stageData as $oid => $stage) {
                $order = $this->field('id,last_stage')->getById($oid);

                $repaidAmount = 0;
                $repaidStage = 0;
                // 处理分期
                foreach ($stage as $stageId => $item) {
                    $repaidAmount = bcadd($repaidAmount, $item['repay_amount'], 2);
                    unset($item['repay_amount']);
                    if (isset($item['status']) && $item['status'] == 1) {
                        $repaidStage++;
                    }
                    $res = D('PufinanceOrderStage')->where(array('id' => $stageId))->save($item);
                    if ($res === false) {
                        throw_exception('更新分期数据失败');
                    }

                }
                // 处理主订单
                $data = array(
                    'last_amount' => array('exp', 'last_amount-' . $repaidAmount),
                );
                $data['last_stage'] = $order['last_stage'] - $repaidStage;
                if ($data['last_stage'] <= 0) {
                    $data['status'] = 5; // 结单
                }
                $res = D('PufinanceOrder')->where(array('id' => $oid))->save($data);
                if ($res === false) {
                    throw_exception('更新主订单数据失败');
                }
            }
        }

        if ($overdueData['add']) {
            foreach ($overdueData['add'] as $item) {
                $res = D('PufinanceOrderOverdue')->add($item);
                if ($res === false) {
                    throw_exception('添加逾期订单数据失败');
                }
            }
        }

        if ($overdueData['update']) {
            foreach ($overdueData['update'] as $overdueid => $item) {
                $res = D('PufinanceOrderOverdue')->where(array('id' => $overdueid))->save($item);
                if ($res === false) {
                    throw_exception('更新逾期订单数据失败');
                }
            }
        }

        if ($puCredit) {
            foreach ($puCredit as $userId => $item) {
                $data = array();
                if (bcsub($item['amount'], $item['interest'], 2) > 0) {
                    $data['usable_amount'] = array('exp', 'usable_amount+' . bcsub($item['amount'], $item['interest'], 2));
                }

                if (isset($item['free_amount'])) {
                    $data['free_usable_amount'] = array('exp', 'free_usable_amount+' . $item['free_amount']);
                }
                if (!empty($data)) {
                    $res = D('PufinanceCredit')->where(array('uid' => $userId))->save($data);
                    if ($res === false) {
                        throw_exception('更新用户PU金数据失败');
                    }
                }

                $res = D('PufinanceCreditLog')->addCreditLog($userId, $type, $item['amount']);
                if ($res === false) {
                    throw_exception('新增用户PU金还款记录失败');
                }
            }
        }
    }

    //订单导入所需的数据
    public function getOrdersData($map=array(),$order="o.id desc",$limit='0,500'){
        $data = $this->table(C('DB_PREFIX')."pufinance_order o")
                ->field('o.amount,o.bank_card_id,o.repay_bank_card_id,o.free_amount,o.interest,o.stage,o.ctime,o.status,o.type,o.lend_time,u.realname,u.year,u.email,u.sex,u.mobile,s.title sname,s.tj_year,p.title pname,c.city cname,b.invest_id,pu.ctfid,pu.recommend_uid,pu.ctime pctime')
                ->join(C('DB_PREFIX').'user u on u.uid=o.uid')/*关联用户*/
                ->join(C('DB_PREFIX').'pufinance_user pu on pu.uid=o.uid')/*关联pu金用户表*/
                ->join(C('DB_PREFIX').'pufinance_bankcard b on b.id=o.bank_card_id')/*银行卡*/
                ->join(C('DB_PREFIX').'school s on s.id=u.sid')/*学校*/
                ->join(C('DB_PREFIX').'province p on p.id=s.provinceId')/*省份*/
                ->join(C('DB_PREFIX').'citys c on c.id=s.cityId')/*城市*/
                ->where($map)
                ->order($order)
                ->limit($limit)
                ->select();
        return $data;
    }

}
