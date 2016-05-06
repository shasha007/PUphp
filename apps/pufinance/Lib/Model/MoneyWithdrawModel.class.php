<?php


class MoneyWithdrawModel extends Model
{
    //列表
    public function getLists($map=array(), $order = 'mw.id desc'){
        return $this->table(C('DB_PREFIX').'money_withdraw mw')
                    ->field('mw.id,mw.uid,mw.ctime,mw.money,mw.chk_time,mw.chk_status,pu.realname,pu.ctfid,mw.bank_card_id,org.name,bo.bank_name,pb.invest_id')
                    ->join(C('DB_PREFIX').'pufinance_user pu on pu.uid=mw.uid')
                    ->join(C('DB_PREFIX').'pufinance_bankcard pb on pb.id=mw.bank_card_id')
                    ->join(C('DB_PREFIX').'pufinance_bank_org bo on bo.id=pb.bank_id')
                    ->join(C('DB_PREFIX').'pufinance_invest_org org on org.id=pb.invest_id')
                    ->order($order)
                    ->where($map)
                    ->findPage(15);
    }
}