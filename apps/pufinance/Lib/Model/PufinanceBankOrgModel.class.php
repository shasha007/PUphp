<?php

/**
 * 银行机构模型
 */
class PufinanceBankOrgModel extends Model
{
    public function getBankList()
    {
        $banks = S('pufinance_banks_list');
        if (!$banks) {
            $list = $this->where(array('invest_id' => 1))->select();
            $banks = array();
            foreach ($list as $bank) {
                $banks[$bank['id']] = $bank;
            }
            S('pufinance_banks_list', $banks, 0);
        }
        return $banks;
    }

    public function getBankInfoById($id)
    {
        $banks = $this->getBankList();
        return isset($banks[$id]) ? $banks[$id] : false;
    }
}