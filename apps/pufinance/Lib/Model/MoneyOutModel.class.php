<?php
class MoneyOutModel extends Model
{
    //列表
    public function getLists($map=array(), $order = 'mo.id desc'){
		$dbPrefix = C('DB_PREFIX');
        return $this->table($dbPrefix.'money_out mo')
                    ->field('mo.out_uid,mo.out_money,mo.out_title,mo.out_ctime,u.realname,pu.ctfid')
                    ->join($dbPrefix.'pufinance_user pu on pu.uid=mo.out_uid')
                    ->join($dbPrefix.'user u on u.uid=mo.out_uid')
                    ->order($order)
                    ->where($map)
                    ->findPage(15); 
    }
}