<?php

include_once SITE_PATH.'/addons/libs/weixinpay/lib/WxPay.Api.php' ;
include_once SITE_PATH.'/addons/libs/weixinpay/lib/WxPay.Data.php' ;
require_once SITE_PATH.'/addons/libs/weixinpay/lib/WxPay.Notify.php';

/**
 * 微信支付通知回调类
 */
class PayNotifyCallBack extends WxPayNotify
{
    //查询订单
    public function orderQuery($transaction_id)
    {
        $input = new WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = WxPayApi::orderQuery($input);

        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }

    //重写回调处理函数
    public function NotifyProcess($data, &$msg)
    {

        if(!array_key_exists("transaction_id", $data)){
            $msg = "输入参数不正确";
            return false;
        }
        //查询订单，判断订单真实性
        if(!$this->orderQuery($data["transaction_id"])){
            $msg = "订单查询失败";
            return false;
        }
        // 处理支付订单
        $paySn = $data['out_trade_no'];
        $payInfo = D('PufinancePay')->getByPaySn($paySn);

        if (!$payInfo) {
            $msg = '支付订单查询失败';
            return false;
        }

        if ($payInfo['pay_status']) {
            return true;
        }

        $condition = array(
            'uid' => $payInfo['uid'],
            'id' => array('in', $payInfo['stage_ids'])
        );
        $data = D('PufinanceOrderStage')->doRepayOrderStage($payInfo['pay_amount'], $condition); // 选择还款的订单

        try {
            M()->startTrans();
            D('PufinanceOrder')->updateStageData($data, 'repay_wechat');

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
                $res = M('MoneyIn')->add(array(
                    'uid' => $payInfo['uid'],
                    'typeName' => '还款：微信支付',
                    'logMoney' => $surplusAmount,
                    'ctime' => time(),
                ));
                if ($res === false) {
                    throw_exception('剩余的金额转入PU币记录失败！');
                }
            }

            M()->commit();
            return true;
        } catch (ThinkException $e) {
            M()->rollback();
            $msg = $e->getMessage();

            $this->log($msg);
            return false;
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