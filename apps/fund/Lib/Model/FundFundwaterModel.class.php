<?php
/**
 * 基金流水
 */
class FundFundwaterModel extends Model{
    
    /**
     * 添加流水
     * $money 钱
     * $eventId 活动id
     */
    public function addWater($eventId,$money){
        $data['fund'] = $money;      
        $data['eventId'] = $eventId;
        $data['cTime'] = time();
        $res = $this->add($data);
        if($res){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 流水统计
     */
    public function waterSum(){
        $sum = $this->sum('fund');
        return $sum;
    }
    
}
?>
