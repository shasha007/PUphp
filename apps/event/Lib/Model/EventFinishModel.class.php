<?php

/**
 * 活动完结
 */
class EventFinishModel extends Model {

    public function applyFinish($eventId,$print_img,$print_img_id,$print_text,$endattach,$pay) {
        $data['print_img'] = $print_img;
        $data['print_text'] = $print_text;
        $data['endattach'] = $endattach;
        $data['pay'] = $pay;
        $old = $this->where('eventId='.$eventId)->field('print_img,endattach')->find();
        if(!$old){
            $data['eventId'] = $eventId;
            $res = $this->add();
        }else{
            $res = $this->where('eventId='.$eventId)->save($data);
            //删除旧的，激活新的
            $reliveid = array();
            if($print_img_id){
                $reliveid[] = $print_img_id;
                if($old['print_img']){
                    tsDelFile($old['print_img']);
                }
            }
            if($endattach){
                $reliveid[] = $endattach;
                if($old['endattach']){
                    model('Attach')->deleteAttach($old['endattach'], true);
                }
            }
            if(!empty($reliveid)){
                model('Attach')->reliveAttach($reliveid);
            }
        }
        if($res){
            $edata['school_audit'] = 3;
            $edata['fTime'] = time();
            M('event')->where('id='.$eventId)->save($edata);
            return true;
        }
        return false;
    }
}
