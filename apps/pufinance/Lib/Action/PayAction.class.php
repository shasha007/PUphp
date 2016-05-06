<?php


/**
 * 第三方支付通知处理控制器
 */
class PayAction
{
    public function notify()
    {
        $type = t($_GET['type']);
        $method = $type . 'Notify';
        if (method_exists($this, $method)) {
            $this->$method();
        }
    }

    private function wechatNotify()
    {
        require_once APP_PATH . '/Common/WechatPayNotify.php';

        $notify = new PayNotifyCallBack();
        $notify->Handle(false);
    }

    private function alipayNotify()
    {
        include_once SITE_PATH.'/addons/libs/alipay/alipay.config_tgxx.php';
        include_once SITE_PATH.'/addons/libs/alipay/lib/alipay_notify.class.php';
        $this->log(var_export($_POST, true));
        //计算得出通知验证结果
        $alipayNotify = new AlipayNotify($alipay_config);
        $verify_result = $alipayNotify->verifyNotify();
        if ($verify_result) {
            $paySn = $_POST['out_trade_no'];
            //交易状态
            $trade_status = $_POST['trade_status'];
            if ($trade_status == 'TRADE_SUCCESS' || $trade_status == 'TRADE_FINISHED') {
                $payInfo = D('PufinancePay')->getByPaySn($paySn);
                if (!$payInfo) {
                    $this->log('支付订单查询失败');
                    exit('fail');
                }

                if ($payInfo['pay_status']) {
                    exit('success');
                }

                $condition = array(
                    'uid' => $payInfo['uid'],
                    'id' => array('in', $payInfo['stage_ids'])
                );
                $data = D('PufinanceOrderStage')->doRepayOrderStage($payInfo['pay_amount'], $condition); // 选择还款的订单

                try {
                    M()->startTrans();
                    D('PufinanceOrder')->updateStageData($data, 'repay_alipay');

                    $res = D('PufinancePay')->setField('pay_status', 1, array('pay_sn' => $paySn));
                    if ($res === false) {
                        throw_exception('更新支付订单状态失败');
                    }

                    $surplusAmount = $data[3]; // 多余的
                    if (bccomp($surplusAmount, 0, 2) > 0) { // 有多余的 转入PU币
                        $surplusAmount = bcmul($surplusAmount, 100);
                        $res = D('Money')->setInc('money', array('uid' => $payInfo['uid']), $surplusAmount);
                        if ($res === false) {
                            throw_exception('剩余的金额转入PU币失败！');
                        }
                        $data = array(
                            'uid' => $payInfo['uid'],
                            'typeName' => '还款：支付宝支付',
                            'logMoney' => $surplusAmount,
                            'ctime' => time(),
                        );
                        $res = M('MoneyIn')->add($data);
                        if ($res === false) {
                            throw_exception('剩余的金额转入PU币记录失败！' . var_export($data, true));
                        }
                    }

                    M()->commit();
                    exit('success');
                } catch (ThinkException $e) {
                    M()->rollback();

                    $this->log($e->getMessage());
                    exit('fail');
                }
            }
        } else {
            $this->log('认证签名失败' . $alipayNotify->getDebugInfo());
            exit('fail');
        }
    }

    public function log($msg)
    {
        $logPath = SITE_PATH . '/data/pufinance/pay';

        if (!is_dir($logPath)) {
            mkdir($logPath, 0777, true);
        }

        $logFile = $logPath . '/' . date('Ymd') . '.log';
        Log::write($msg, Log::ERR, Log::FILE, $logFile);
    }
}



