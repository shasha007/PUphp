<?php

/**
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EventTagModel extends Model {

    //增加、编辑
    public function editEventTag($title,$sid,$id=0){
        $isnull = preg_replace("/[ ]+/si", "", t($title));
        if (empty($isnull)) {
            $this->error = '标签名不能为空';
            return false;
        }
        $map['sid'] = $sid;
        $map['title'] = $isnull;
        $hasId = $this->where($map)->getField('id');
        if(!$hasId){ // 无重名 增加或编辑
            if($id){
                $map['isdel'] = 0;
                $this->where("id=$id")->save($map);
            }else {
                $this->add($map);
            }
            return true;
        }else{ //重名
            if($id){ //重名编辑
                if($hasId==$id){
                    return true;
                }else{
                    $this->error = '标签重名，不可修改。可以尝试添加';
                    return false;
                }
            }else { //重名增加
                $this->where("id=$hasId")->setField('isdel',0);
                return true;
            }
        }
    }
    public function getTagsByEid($eid){
        $list = M('EventTagcheck')->where("eid=$eid")->field('tid')->findAll();
        return getSubByKey($list, 'tid');
    }
    
}

?>