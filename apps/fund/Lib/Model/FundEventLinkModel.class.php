<?php

class FundEventLinkModel extends Model{
    
    /**
     * 获取申请成功的event表中的活动id
     */
    public  function getEventId($map){
        $db_prefix = C('DB_PREFIX');
        $res = $this->table("{$db_prefix}fund_event_link as link")
                    ->join("{$db_prefix}fund_applyevent as a on link.fundevent_id = a.id")
                    ->where($map)
                    ->field('link.event_id')
                    ->select();
        $arr = array();
        if($res){
            foreach ($res as $value) {
                $arr[] = $value['event_id'];
            }
            return $arr;
        }else{
            return false;
        }
    }
}
?>
