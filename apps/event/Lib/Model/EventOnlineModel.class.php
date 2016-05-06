<?php

/**
 * 活动自动定时上线
 */
class EventOnlineModel extends Model {

    public function editOnline($eventId, $time)
    {
        if($time <= time()){
            $this->where("eventId=$eventId")->delete();
            M('Event')->setField('status',1,"id=$eventId and school_audit>=2");
        }else{
            M()->execute("replace into ts_event_online (`eventId`,`online_time`) VALUES (".$eventId.",".$time.")");
            M('Event')->setField('status',0,"id=$eventId");
        }
    }

    public function getOnlineTime($eventId)
    {
        $onlineTime = $this->getField('online_time', "eventId=$eventId");
        if($onlineTime){
            return $onlineTime;
        }
        return '';
    }
}
