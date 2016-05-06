<?php

/**
 * PU订单支付模型
 */
class PufinancePayModel extends Model
{
    public function getPayInfo($uid, $amount, $stageIds)
    {
        $stageIds = $this->parseStageIds($stageIds);
        $condition = array(
            'uid' => $uid,
            'pay_amount' => $amount,
            'stage_ids' => $stageIds,
            'ctime' => array('egt', time() - 60),
        );
        $info = $this->where($condition)->order('id DESC')->find();
        if (!$info) {
            $info = $this->createPay($uid, $amount, $stageIds);
            if ($info === false) {
                return false;
            }
        }
        return $info;
    }

    public function createPay($uid, $amount, $stageIds)
    {
        $data = array(
            'uid' => $uid,
            'pay_amount' => $amount,
            'stage_ids' => $stageIds,
            'ctime' => time(),
        );
        $data['pay_sn'] = $this->makePaySn($uid);
        $res = $this->add($data);
        if ($res === false) {
            return false;
        }
        $data['id'] = $res;
        return $data;
    }

    private function parseStageIds($stageIds)
    {
        if (is_numeric($stageIds)) {
            $stageIds = array($stageIds);
        }

        if (is_string($stageIds)) {
            $stageIds = explode(',', $stageIds);
        }
        if (is_array($stageIds)) {
            sort($stageIds);
            return implode(',', $stageIds);
        }
        return $stageIds;

    }

    /**
     * 生成支付单编号(两位随机 + 现在的秒数+微秒+UID%1000)，该值会传给第三方支付接口
     * 长度 =2位 + 10位 + 3位 + 3位  = 18位
     * 1000个会员同一微秒提订单，重复机率为1/100
     * @param integer $uid
     *
     * @return string
     */
    private function makePaySn($uid)
    {
        return mt_rand(10, 99)
        . sprintf('%010d', time())
        . sprintf('%03d', (float)microtime() * 1000)
        . sprintf('%03d', (int)$uid % 1000);
    }
}