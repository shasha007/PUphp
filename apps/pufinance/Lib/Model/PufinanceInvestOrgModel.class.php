<?php


class PufinanceInvestOrgModel extends Model
{
    public function getInvestOrgList()
    {
        $invests = S('pufinance_invest_org_list');
        if (!$invests) {
            $list = $this->select();
            $invests = array();
            foreach ($list as $item) {
                $invests[$item['id']] = $item;
            }
            unset($invests[2]); // 暂停小苏
            S('pufinance_invest_org_list', $invests, 0);
        }
        return $invests;
    }

    public function getInvestOrgInfoById($id)
    {
        $invests = $this->getInvestOrgList();
        return isset($invests[$id]) ? $invests[$id] : false;
    }
}