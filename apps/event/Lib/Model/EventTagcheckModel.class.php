<?php

/**
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EventTagcheckModel extends Model {

    public function removeByEid($eid){
        $this->where("eid=$eid")->delete();
    }
    //增加、编辑
    public function editEventTagcheck($eid,$tags){
        $this->removeByEid($eid);
        $data['eid'] = $eid;
        foreach($tags as $v){
            $data['tid'] = $v;
            $this->add($data);
        }
    }
    //获取某活动所有标签
    public function getTagsByEid($eid){
        $list = $this->where("eid=$eid")->field('tid')->findAll();
        return getSubByKey($list, 'tid');
    }
    
}

?>