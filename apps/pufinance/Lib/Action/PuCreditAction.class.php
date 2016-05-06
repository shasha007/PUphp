<?php

/**
 * PU金控制器
 */
class PuCreditAction extends BaseAction
{

    /**
     * PU金入口
     */
    public function index()
    {
        //dump(service('Kangxin')->singlePaymentQuery('T10010116041500000002'));
        //dump(service('Kangxin')->tradeFlowQuery(time()));
        //$_SESSION = array();
        //dump($this->user);
        $CreditModel = D('PufinanceCredit');
        $pucredit = $CreditModel->getPufinanceCreditInfoByUid($this->mid);
        if (!$pucredit) {
            $pucredit = $CreditModel->initPufinanceCredit($this->mid);
        }
        //dump($pucredit);
        $this->assign('pucredit', $pucredit);

        $monthLastAmount = D('PufinanceOrder')->getUserMonthLastAmount($this->mid);
        //dump($monthLastAmount);
        $allLastAmount = D('PufinanceOrder')->getUserAllLastAmountByUid($this->mid);
        //dump($allLastAmount);
        $this->assign('month_last_amount', $monthLastAmount['all_last_amount']);

        $curr = current($monthLastAmount['list']);
        $this->assign('month_last_etime', $curr['etime']);

        $this->assign('all_last_amount', $allLastAmount['all_last_amount']);

        $isSuzhou = $this->user['schoolEvent']['cityId'] == 1 ? 1 : 0;
        $this->assign('is_suzhou', $isSuzhou);
        $this->display();
    }

    /**
     * 借款
     */
    public function borrow()
    {
        $bankcards = D('PufinanceBankcard')->getUserUsableBankcardListByUid($this->mid);
        if (empty($bankcards)) {
            $this->assign('nobank', 1);
        } else {
            $CreditModel = D('PufinanceCredit');
            $pucredit = $CreditModel->getPufinanceCreditInfoByUid($this->mid);
            $this->assign('pucredit', $pucredit);
            $this->assign('bankcards', $bankcards);

        }

        $this->display();
    }

    /**
     * 提交借款表单
     */
    public function doBorrow()
    {
        $minStageAmount = 200.00; // 分期最小金额 小于该值不分期
        $amount = round(floatval($_POST['amount']), 2); // 四舍五入
        $stage = intval($_POST['stage']);
        $bank_card_id = intval($_POST['bank_card_id']);
        $reson = t($_POST['reson']);
        $captcha = t($_POST['captcha']);
        $agree = intval($_POST['agree']);
        if (bccomp($amount, '0', 2) <= 0) {
            $this->error('请输入金额');
        }
        $maxStage = ceil($amount / $minStageAmount);

        if ($stage <= 0 || $stage > $maxStage || $stage > 12) {
            $this->error('请选择分期');
        }

        if (!$agree) {
            $this->error('必须同意协议');
        }

        if (empty($captcha)) {
            $this->error('请输入短信验证码');
        }

        if (!isset($_SESSION['borrow_' . $this->mid]) || time() - $_SESSION['borrow_' . $this->mid]['time'] > 600) {
            $this->error('短信验证码失效，请重新获取');
        }
        if ($captcha != $_SESSION['borrow_' . $this->mid]['captcha']) {
            $this->error('短信验证码不正确');
        }

        $pucredit = D('PufinanceCredit')->getPufinanceCreditInfoByUid($this->mid);
        if (!$pucredit || bccomp($amount, $pucredit['usable_amount'], 2) == 1) {
            $this->error("您最多可申请{$pucredit['usable_amount']}元借款");
        }

        $bankcards = D('PufinanceBankcard')->getUserUsableBankcardListByUid($this->mid);
        if (empty($bankcards)) {
            $this->error('银行卡不存在，请确认银行卡是否绑定');
        }

        if ($bank_card_id) {
            if (!isset($bankcards[$bank_card_id])) {
                $this->error("请选择收款方式");
            }
            $repay_bank_card_id = $bank_card_id;
            $type = 'lend_' . $bankcards[$bank_card_id]['bank_id'];
        } else {    // 借至pu币，还款银行卡取第一个
            $bank = current($bankcards);
            $repay_bank_card_id = $bank['id'];
            $type = 'lend_pumoney';
        }
        if (bccomp($amount, $pucredit['free_usable_amount'], 2) == 1) {
            $freeAmount = $pucredit['free_usable_amount'];
        } else {
            $freeAmount = $amount;
        }

        $order = array(
            'bank_card_id' => $bank_card_id,
            'repay_bank_card_id' => $repay_bank_card_id,
            'amount' => $amount,
            'free_amount' => $freeAmount,
            'stage' => $stage,
            'reson' => $reson
        );
        // 白名单 且在免风控额内 则直接通过
        $status = $pucredit['status'] == 1 && bccomp($amount, $pucredit['free_risk'], 2) <= 0 ? 2 : 0;
        if ($bankcards[$bank_card_id]['invest_id'] == 0) {
            $status == 0;
        }

        try {
            M()->startTrans();
            // 加入订单
            $res = D('PufinanceOrder')->addUserOrder($this->mid, $order, $status);
            if ($res === false) {
                throw_exception('申请借款失败，请稍后重试（1）');
            }
            if ($status == 2) { // 白名单 直接通过 生成分期
                $res = D('PufinanceOrderStage')->createUserOrderStage($res);
                if ($res === false) {
                    throw_exception('申请借款失败，请稍后重试（2）');
                }

            }
            // 更新用户PU金可用部分的数据
            $usableAmount = bcsub($pucredit['usable_amount'], $amount, 2); // 剩余可用额度
            $freeUsableAmount = bcsub($pucredit['free_usable_amount'], $freeAmount, 2); // 剩余可用免息额
            $res = D('PufinanceCredit')->updatePufinanceCredit($this->mid, array(
                'usable_amount' => $usableAmount,
                'free_usable_amount' => $freeUsableAmount,
            ));
            if ($res === false) {
                throw_exception('申请借款失败，请稍后重试（3）');
            }
            $res = D('PufinanceCreditLog')->addCreditLog($this->mid, $type, "-{$amount}");
            if ($res === false) {
                throw_exception('申请借款失败，请稍后重试（4）');
            }
            M()->commit();
            $_SESSION['borrow_' . $this->mid] = null;// 清除短信验证码
            $this->success('申请成功');
        } catch (ThinkException $e) {
            M()->rollback();
            $this->error($e->getMessage());
        }
    }

    public function sendBorrowSms()
    {
        if (empty($this->user['mobile'])) {
            $this->error('请先到【我的】【设置】绑定手机号码');
        }
        $captcha = rand(100, 999) . rand(100, 999);
        $_SESSION['borrow_' . $this->mid] = array(
            'captcha' => $captcha,
            'time' => time(),
        );
        $msg = "您正在进行申请借款操作，验证码为：{$captcha}，如非本人操作请忽略（打死都不能告诉别人）。";
        $res = service('Sms')->sendsms($this->user['mobile'], $msg);
        //$res['status'] = 1;
        if ($res['status']) {
            $mobile = substr_replace($this->user['mobile'], '****', 3, 4);
            $this->success("已成功发送至您的手机（{$mobile}），请查收！");
        } else {
            $this->error('发送失败，请重试！');
        }


    }

    public function repay()
    {
        $checkedStageIds = array();
        if (isset($_GET['t']) && $_GET['t'] == 'all') {
            $orderList = D('PufinanceOrder')->getUserAllLastAmountByUid($this->mid);
            if (isset($_SESSION['checked_order'])) {
                $amount = 0;
                foreach ($_SESSION['checked_order'] as $orderid => $stageIds) {
                    foreach ($orderList['list'][$orderid]['stage_list'] as $stageOrder) {
                        if (in_array($stageOrder['id'], $stageIds)) {
                            $amount = bcadd($amount, $stageOrder['last_amount'], 2);
                            $checkedStageIds[] = $stageOrder['id'];
                        }
                    }
                }
                $this->assign('amount', $amount);
            } else {
                foreach ($orderList['list'] as $order) {
                    $checkedStageIds = array_merge($checkedStageIds, array_column($order['stage_list'], 'id'));
                }
                $this->assign('amount', $orderList['all_last_amount']);
            }
        } else {
            $monthLastAmount = D('PufinanceOrder')->getUserMonthLastAmount($this->mid);
            if (isset($_GET['stageids'])) {
                $stageids = explode(',', $_GET['stageids']);
                $amount = 0;
                foreach ($monthLastAmount['list'] as $stage) {
                    if (in_array($stage['id'], $stageids)) {
                        $amount = bcadd($amount, $stage['last_amount'], 2);
                        $checkedStageIds[] = $stage['id'];
                    }
                }
                $this->assign('amount', $amount);
            } else {
                $curr = current($monthLastAmount['list']);

                $checkedStageIds[] = $curr['id'];
                $this->assign('amount', $curr['last_amount']);
                $this->assign('etime', $curr['etime']);
            }
        }
        $this->assign('checkedStageIds', $checkedStageIds);

        $this->display();
    }

    /**
     * 提交还款
     */
    public function doRepay()
    {
        $amount = round(floatval($_POST['amount']), 2);
        if ($amount <= 0) {
            $this->error('还款金额输入有误');
        }

        $this->assign('amount', $amount);
        $checkedStageIds = $_POST['checkedStageIds'];
        $this->assign('checkedStageIds', $checkedStageIds);

        $money = D('Money')->getField('money', array('uid' => $this->mid));
        $pumoney = D('PufinanceMoney')->getField('money', array('uid' => $this->mid));
        if ($pumoney) {
            $money += $pumoney;
        }
        $money = bcdiv($money, 100, 2);
        $this->assign('money', $money);
        $this->assign('money_dis', bccomp($money, $amount, 2) < 0 ? 'true' : 'false');
        $puCredit = D('PufinanceCredit')->getField('usable_amount', array('uid' => $this->mid));
        $this->assign('pucredit', $puCredit);
        $this->assign('pucredit_dis', bccomp($puCredit, $amount, 2) < 0 ? 'true' : 'false');
        $this->display();
    }

    /**
     * 提交支付
     */
    public function submitPay()
    {
        $method = $_POST['method'];
        if (!in_array($method, array('money', 'pucredit', 'wechat', 'alipay'))) {
            $this->error('支付方式异常！');
        }

        $amount = round(floatval($_POST['amount']), 2);
        if ($amount <= 0) {
            $this->error('还款金额输入有误');
        }

        $checkedStageIds = t($_POST['checkedStageIds']);

        $action = 'payBy' . ucfirst($method);
        if (method_exists($this, $action)) {
            $this->$action($amount, $checkedStageIds);
        }

    }

    public function submitPayOk()
    {
        $this->display();
    }

    private function checkPayPassword()
    {
        $puUser = D('PufinanceUser')->getUserByUid($this->mid);
        if (!$puUser || empty($puUser['salt']) || empty($puUser['paypassword'])) {
            $this->error('还未设置支付密码');
        }
        $paypwd = pay_password(t($_POST['paypwd']), $puUser['salt']);
        if ($paypwd != $puUser['paypassword']) {
            $this->error('支付密码有误');
        }
    }

    /**
     * 使用PU币支付还款
     *
     * @param string $amount 还款金额
     * @param string $checkedStageIds 选择还款的分期订单号
     */
    private function payByMoney($amount, $checkedStageIds)
    {
        $this->checkPayPassword();
        $money = $money1 = D('Money')->getField('money', array('uid' => $this->mid));
        $pumoney = D('PufinanceMoney')->getField('money', array('uid' => $this->mid));
        if ($pumoney) {
            $money += $pumoney;
        }
        $money = bcdiv($money, 100, 2);
        if (bccomp($money, $amount, 2) < 0) {
            $this->error('您的PU币余额不足');
        }

        $condition = array(
            'uid' => $this->mid,
            'id' => array('in', $checkedStageIds)
        );
        $data = D('PufinanceOrderStage')->doRepayOrderStage($amount, $condition); // 选择还款的订单

        try {
            M()->startTrans();
            D('PufinanceOrder')->updateStageData($data, 'repay_pumoney');

            $amount = bcsub($amount, $data[3], 2); // 实际使用的还款金额 = 输入金额 - 多余的

            $repayAmount = bcmul($amount, 100); // 分
            if ($money1 >= $repayAmount) {
                $usedMoney1 = $repayAmount;
                $usedMoney2 = 0;
            } else {
                $usedMoney1 = $money1;
                $usedMoney2 = $repayAmount - $usedMoney1;
            }
            if ($usedMoney1 > 0) {
                $res = D('Money')->where(array('uid' => $this->mid))->save(array(
                    'money' => array('exp', 'money-' . $usedMoney1),
                ));
                if ($res === false) {
                    throw_exception('PU币支付失败（1）！');
                }
            }

            if ($usedMoney2 > 0) {
                $res = D('PufinanceMoney')->where(array('uid' => $this->mid))->save(array(
                    'money' => array('exp', 'money-' . $usedMoney2),
                ));
                if ($res === false) {
                    throw_exception('PU币支付失败（2）！');
                }
            }
            if ($usedMoney1 > 0 || $usedMoney2 > 0) {
                $log = array(
                    'out_uid' => $this->mid,
                    'out_title' => 'PU币还款',
                    'out_url' => '',
                    'out_ctime' => time(),
                    'out_money' => $usedMoney1 ?: 0,
                    'out_pumoney' => $usedMoney2 ?: 0,
                );
                $res = D('MoneyOut')->add($log);
                if ($res === false) {
                    throw_exception('PU币支付失败（3）！');
                }
            }
            M()->commit();
            $this->success('ok');
        } catch (ThinkException $e) {
            M()->rollback();
            //dump($e->getMessage());
            $this->error('PU币支付失败');
        }
    }

    /**
     * 使用PU金支付还款
     * 借新还旧
     *
     * @param string $amount 还款金额
     * @param string $checkedStageIds 选择还款的分期订单号
     */
    private function payByPucredit($amount, $checkedStageIds)
    {
        $this->checkPayPassword();
        $puCredit = D('PufinanceCredit')->getField('usable_amount', array('uid' => $this->mid));
        if (bccomp($puCredit, $amount, 2) < 0) {
            $this->error('您的'.L('finance_name').'可用额度不足');
        }

        $bankcards = D('PufinanceBankcard')->getUserUsableBankcardListByUid($this->mid);
        if (empty($bankcards)) {
            $this->error('您还未绑定银行卡！');
        }

        $bank = current($bankcards);
        $bank_card_id = $bank['id'];
        $type = 'lend_' . $bankcards[$bank_card_id]['bank_id'];

        $condition = array(
            'uid' => $this->mid,
            'id' => array('in', $checkedStageIds)
        );
        $data = D('PufinanceOrderStage')->doRepayOrderStage($amount, $condition); // 选择还款的订单

        try {
            M()->startTrans();
            D('PufinanceOrder')->updateStageData($data, 'repay_pucredit');

            $surplusAmount = $data[3]; // 多余的
            if (bccomp($surplusAmount, 0, 2) > 0) { // 有多余的 转入PU币
                $surplusAmount = bcmul($surplusAmount, 100);
                $res = D('Money')->setInc('money', array('uid' => $this->mid), $surplusAmount);
                if ($res === false) {
                    throw_exception('剩余的金额转入PU币失败！');
                }
                $res = M('MoneyIn')->add(array(
                    'uid' => $this->mid,
                    'typeName' => '还款：'.L('finance_name').'支付',
                    'logMoney' => $surplusAmount,
                    'ctime' => time(),
                ));
                if ($res === false) {
                    throw_exception('剩余的金额转入PU币记录失败！');
                }
            }

            $order = array(
                'bank_card_id' => $bank_card_id,
                'repay_bank_card_id' => $bank_card_id,
                'amount' => $amount,
                'free_amount' => 0,
                'stage' => 1,
                'reson' => L('finance_name').'还款'
            );

            // 加入订单
            $status = 4; // 放款成功
            $res = D('PufinanceOrder')->addUserOrder($this->mid, $order, $status);
            if ($res === false) {
                throw_exception(L('finance_name').'还款失败（1）');
            }
            // 生成分期
            $res = D('PufinanceOrderStage')->createUserOrderStage($res);
            if ($res === false) {
                throw_exception(L('finance_name').'还款失败（2）');
            }

            // 更新用户PU金可用部分的数据
            $usableAmount = bcsub($puCredit, $amount, 2); // 剩余可用额度
            $res = D('PufinanceCredit')->updatePufinanceCredit($this->mid, array(
                'usable_amount' => $usableAmount
            ));
            if ($res === false) {
                throw_exception(L('finance_name').'数据更新失败');
            }
            $res = D('PufinanceCreditLog')->addCreditLog($this->mid, $type, "-{$amount}");
            if ($res === false) {
                throw_exception(L('finance_name').'数据记录失败（4）');
            }

            M()->commit();
            $this->success('ok');
        } catch (ThinkException $e) {
            M()->rollback();
            //dump($e->getMessage());
            $this->error(L('finance_name').'支付失败');
        }
    }

    /**
     * 使用微信支付还款
     *
     * @param string $amount 还款金额
     * @param string $checkedStageIds 选择还款的分期订单号
     */
    private function payByWechat($amount, $checkedStageIds)
    {

        $payInfo = D('PufinancePay')->getPayInfo($this->mid, $amount, $checkedStageIds);
        if ($payInfo === false) {
            $this->error('支付订单失败！');
        }

        if ($payInfo['pay_status'] == 1) {
            $this->success('ok');
        }

        include_once SITE_PATH.'/addons/libs/weixinpay/lib/WxPay.Api.php' ;
        include_once SITE_PATH.'/addons/libs/weixinpay/lib/WxPay.Data.php' ;
        require_once SITE_PATH.'/addons/libs/weixinpay/lib/WxPay.Notify.php';
        //$notifyUrl = 'http://58.210.175.66:8013/pufinance/pay/notify/wechat/';
        $notifyUrl = 'http://www.pocketuni.net/pufinance/pay/notify/wechat/';

        $input = new WxPayUnifiedOrder();
        $input->SetBody(L('finance_name').'还款');
        $input->SetAttach('');
        $input->SetOut_trade_no($payInfo['pay_sn']);
        $input->SetTotal_fee(bcmul($amount, 100));
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetNotify_url($notifyUrl);
        $input->SetTrade_type("APP");

        try {
            $order = WxPayApi::unifiedOrder($input);
            //throw_exception(var_export($order, true));
            //dump($order);
            if ($order['result_code'] == 'SUCCESS' && $order['return_code'] == 'SUCCESS') {
                $time = time();
                $order['timestamp']= $time;
                $str  = 'appid='.$order['appid'].
                    '&noncestr='.$order['nonce_str'].
                    '&package=Sign=WXPay'.
                    '&partnerid='.$order['mch_id'].
                    '&prepayid='.$order['prepay_id'].
                    '&timestamp='.$time.
                    '&key=' . WxPayConfig::KEY;
                $order['order_sign'] = strtoupper(md5($str));
                $res = D('PufinancePay')->setField('prepay_id', $order['prepay_id'], array('id' => $payInfo['id']));
                if ($res === false) {
                    throw_exception('预支付订单处理失败');
                }

                $this->ajaxReturn($order);
            } else {
                throw_exception('微信下单失败！');
            }

        } catch (Exception $e) {
            //dump($e->getMessage());
            $this->error('微信支付失败！');
        }
    }

    /**
     * 使用支付宝支付还款
     *
     * @param string $amount 还款金额
     * @param string $checkedStageIds 选择还款的分期订单号
     */
    private function payByAlipay($amount, $checkedStageIds)
    {
        $payInfo = D('PufinancePay')->getPayInfo($this->mid, $amount, $checkedStageIds);
        if ($payInfo === false) {
            $this->error('支付订单失败！');
        }

        if ($payInfo['pay_status'] == 1) {
            $this->success('ok');
        }
        //$notifyUrl = 'http://58.210.175.66:8013/pufinance/pay/notify/alipay/';
        $notifyUrl = 'http://www.pocketuni.net/pufinance/pay/notify/alipay/';

        $this->ajaxReturn(array(
            'pay_sn' => $payInfo['pay_sn'],
            'notify_url' => $notifyUrl,
        ));
    }


    /**
     * 计算每期金额
     *
     * @return array
     */
    public function ajaxCalcPerAmount()
    {
        $amount = round(floatval($_POST['amount']), 2); // 四舍五入
        $stage = intval($_POST['stage']);
        if ($amount > 0 && $stage >= 1 && $stage <= 12) {
            $pucredit = D('PufinanceCredit')->getPufinanceCreditInfoByUid($this->mid);
            if (bccomp($amount, $pucredit['usable_amount'], 2) <=  0) {
                // 免息额
                if (bccomp($amount, $pucredit['free_usable_amount'], 2) == 1) {
                    $freeAmount = $pucredit['free_usable_amount'];
                } else {
                    $freeAmount = $amount;
                }

                $interest = calc_interest($amount, $freeAmount, $stage);
                if ($stage > 1) {
                    $avgAmount = bcdiv($amount, $stage, 2);
                    $avgInterest = bcdiv($interest, $stage, 2);
                    $data = array(
                        'amount' => bcadd($avgAmount, $avgInterest, 2),
                        'interest' => $avgInterest
                    );
                } else {
                    $data = array(
                        'amount' => bcadd($amount, $interest, 2),
                        'interest' => $interest
                    );
                }
                $this->ajaxReturn($data);
            }

        }

    }

    /**
     * 本月应还
     */
    public function monthLastAmount()
    {
        $monthLastAmount = D('PufinanceOrder')->getUserMonthLastAmount($this->mid);
        //dump($monthLastAmount);
        $this->assign('month_last_amount', $monthLastAmount);


        $bankList = D('PufinanceBankcard')->getUserUsableBankcardListByUid($this->mid);
        //dump($bankList);
        $this->assign('bank_list', $bankList);
        $this->display();
    }

    /**
     * 显示分期订单
     */
    public function showOrder()
    {
        $id = intval($_GET['id']);
        $condition = array(
            'order_id' => $id,
            'uid' => $this->mid,
        );
        $stageList = D('PufinanceOrderStage')->getUserOrderStageList($condition);
        foreach ($stageList as &$stageOrder) {
            if (isset($stageOrder['overdue'])) { // 逾期
                $stageOrder['last_amount'] = bcadd($stageOrder['last_amount'], $stageOrder['overdue']['all_last_overdue'], 2);
            }
        }
        $last = $repaid = array();
        $i = 0;
        foreach ($stageList as $order) {
            $i++;
            if ($order['status']) {
                $repaid[$i] = $order;
            } else {
                $last[$i] = $order;
            }

        }
        $this->assign('last_list', $last);
        $this->assign('repaid_list', $repaid);
        $this->assign('stages', count($stageList));
        $this->display();
    }

    /**
     * 修改选定订单
     */
    public function changeCheckedOrder()
    {
        // 全部待还订单
        $orderList = D('PufinanceOrder')->getUserAllLastAmountByUid($this->mid);

        if (isset($_GET['id']) && isset($_GET['check'])) {
            $id = intval($_GET['id']);
            $check = intval($_GET['check']);
            if ($check) {
                $_SESSION['checked_order'][$id] = array_column($orderList['list'][$id]['stage_list'], 'id');
            } else {
                unset($_SESSION['checked_order'][$id]);
            }
            $amount = 0;
            foreach ($_SESSION['checked_order'] as $orderid => $stageIds) {
                foreach ($orderList['list'][$orderid]['stage_list'] as $stageOrder) {
                    if (in_array($stageOrder['id'], $stageIds)) {
                        $amount = bcadd($amount, $stageOrder['last_amount'], 2);
                    }

                }
            }
            $this->ajaxReturn($amount);
        }
        if (isset($_GET['id']) && isset($_GET['stageids'])) {
            $id = intval($_GET['id']);
            $stageids = explode(',', $_GET['stageids']);
            if (isset($orderList['list'][$id])) {
                $_SESSION['checked_order'][$id] = array();
                foreach ($stageids as $stageid) {
                    if (in_array($stageid, array_column($orderList['list'][$id]['stage_list'], 'id'))) {
                        $_SESSION['checked_order'][$id][] = $stageid;
                    }
                }
            }
        }
    }

    /**
     * 全部应还
     */
    public function allLastAmount()
    {
        // 全部待还订单
        $orderList = D('PufinanceOrder')->getUserAllLastAmountByUid($this->mid);
        //dump($orderList);
        $this->assign('order_list', $orderList);
        //unset($_SESSION['checked_order']);
        if (!isset($_SESSION['checked_order'])) {
            $_SESSION['checked_order'] = array();
            foreach ($orderList['list'] as $order) {
                foreach ($order['stage_list'] as $stageOrder) {
                    if ($stageOrder['status'] == 0) {
                        $_SESSION['checked_order'][$order['id']][] = $stageOrder['id'];
                    }
                }
            }
        }

        $checkedAmount = 0;
        foreach ($_SESSION['checked_order'] as $id => $stageIds) {
            foreach ($orderList['list'][$id]['stage_list'] as $stageOrder) {
                if (in_array($stageOrder['id'], $stageIds)) {
                    $checkedAmount = bcadd($checkedAmount, $stageOrder['last_amount'], 2);
                }
            }
        }
        $this->assign('checked_amount', $checkedAmount);
//dump($_SESSION['checked_order']);

        $bankList = D('PufinanceBankcard')->getUserUsableBankcardListByUid($this->mid);
        $this->assign('bank_list', $bankList);

        $this->display();
    }

    /**
     * PU金明细
     */
    public function showCreditLog()
    {
        $credit = D('PufinanceCredit')->getPufinanceCreditInfoByUid($this->mid);
        $this->assign('credit', $credit);

        $creditLogs = D('PufinanceCreditLog')->getCreditLogListOrderByMonthByUid($this->mid);
        $this->assign('logs', $creditLogs);

        $this->display();
    }

    public function checkTradeStatus()
    {
        $type = intval($_POST['type']);
        $tradeNo = h($_POST['trade_no']);
        if (empty($tradeNo)) {
            $this->error('no trade');
        }

        if ($type == 0) { // 支付宝
            $payInfo = D('PufinancePay')->getByPaySn($tradeNo);
            if ($payInfo['pay_status'] == 1) {
                $this->success('ok');
            }
        } elseif ($type == 1) { // 微信
            $condition = array(
                'prepay_id' => $tradeNo
            );
            $payInfo = D('PufinancePay')->where($condition)->find();
            if ($payInfo['pay_status'] == 1) {
                $this->success('ok');
            }
        }
        $this->error('no pay');
    }

    /**
     * 服务合同
     */
    public function agree()
    {
        $user = D('PufinanceUser')->where(array('uid' => $this->mid))->field('uid, realname,ctfid')->find();
        $this->assign('user', $user);
        $this->display();
    }
}