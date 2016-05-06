<?php

include_once SITE_PATH . '/apps/event/Lib/Model/BaseModel.class.php';

class EventOrgaModel extends BaseModel {
    public function getEventOrga($eid){
        $orga = $this->where("eventId=$eid")->find();
        if (!$orga) {
            $orga = $this->getEmptyOrga($eid);
        }
        return $orga;
    }

    public function getEmptyOrga($eid){
        $orga['eventId'] = $eid;
        $orga['title'] = '组织单位';
        $orga['content'] = '';
        $orga['cTime '] = time();
        return $orga;
    }
}