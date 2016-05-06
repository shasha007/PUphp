<?php

/**
 *
 * @uses Model
 * @package Model::Mini
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class KcOptModel extends Model {

    public function getDoubleKc($uid, $occurs, $weekdays, $froms, $tos, $withoutKc=array()) {
        $map['uid'] = $uid;
        $cnt = count($occurs);
        for ($i = 0; $i < $cnt; $i++) {
            if(!empty($withoutKc)){
                $map['kcid'] = array('not in', $withoutKc);
            }
            $map['weekday'] = $weekdays[$i];
            $map['from'] = array('elt', $tos[$i]);
            $map['to'] = array('egt', $froms[$i]);
            $occur = intval($occurs[$i]);
            if($occur == 0){
                $map['occur'] = array('in', array(0,1));
            }elseif($occur == 2){
                $map['occur'] = array('in', array(1,2));
            }
            $double = $this->where($map)->find();
            if($double){
                return $double['kcid'];
            }
        }
        return false;
    }

}
