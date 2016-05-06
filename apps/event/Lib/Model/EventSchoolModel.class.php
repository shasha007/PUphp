<?php

/**
 * EventNewsModel
 * 活动的新闻模型
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class EventSchoolModel extends BaseModel {

    public function getEventIds($sid) {
        $map['sid'] = $sid;
        $eventIds = $this->where($map)->findAll();
        return getSubByKey($eventIds, 'eventId');
    }

    public function getSchoolIds($eid) {
        $map['eventId'] = $eid;
        $ids = $this->where($map)->findAll();
        return getSubByKey($ids, 'sid');
    }

    public function addBySids($eid, $sids) {
        if (!is_array($sids)) {
            $sids = explode(',', $sids);
        }
        foreach ($sids as $vo) {
            if ($vo > 0) {
                $this->add(array('eventId' => $eid, 'sid' => $vo));
            }
        }
    }

    public function removeByEid($eid) {
        $eid = intval($eid);
        if ($eid > 0) {
            $map['eventId'] = $eid;
            $this->where($map)->delete();
        }
    }

}
