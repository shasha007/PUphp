<?php

/***
 * 资金赞助
 */
class FundSponsorModel extends Model{

    public function sponsorList($map,$limit=10,$order='id DESC'){
        $res = $this->where($map)->field('id,company,putFund,actualFund,putTime,actualTime,cTime,endTime,attachId')->order($order)->findPage($limit);
        return $res;
    }

    /**
     * 资金统计
     */
    public function sponsorCount(){
        $sum = $this->sum('actualFund');
        return $sum;
    }

     /**
     * 基金已经使用 剩余统计
     */
    public function fundNow(){
        $all = 0;
        $alls = $this->sponsorCount();
        if($alls){
            $all = $alls;
        }
        $hasUse = 0;
        $hasUses = D('FundFundwater','fund')->waterSum();
        if($hasUses){
            $hasUse = $hasUses;
        }
        $last = $all - $hasUse;
        $arr = array();
        $arr['all'] = $alls;
        $arr['hasUse'] = $hasUse;
        $arr['last'] = $last;
        return $arr;
     }
}
?>
