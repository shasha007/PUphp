<?php

/**
 * EventNoteModel
 * 活动的评分模型
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class EventNoteModel extends BaseModel {

    public function hasNoted($eventId, $uid) {
        $map['eventId'] = $eventId;
        $map['mid'] = $uid;
        return $this->where($map)->field('id')->find();
    }

    public function addNote($eventId,$uid, $note){
        $map['eventId'] = $eventId;
        $map['mid'] = $uid;
        $map['note'] = $note;
        $map['cTime'] = time();
        return $this->add($map);
    }

    public function getAvg($eventId){
        $map['eventId'] = $eventId;
        $avg = $this->where($map)->avg('note');
        return round($avg*10)/10;
    }

    public function getNoteCount($eventId){
        return $this->where('eventId = '.$eventId)->count();
    }
}
