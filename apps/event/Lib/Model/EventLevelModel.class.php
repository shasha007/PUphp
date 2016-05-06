<?php

/**
 * 活动级别
 */
class EventLevelModel extends Model {

    //全部级别
    public function allLevel($sid) {
        $key = 'EventLevel_allLevel_'.$sid;
        $cache = Mmc($key);
        if ($cache !== false) {
            return json_decode($cache, true);
        }
        $map['sid'] = $sid;
        $map['isdel'] = 0;
        $list = $this->where($map)->field('id,title')->findAll();
        if(!$list){
            $list = array();
        }
        Mmc($key, json_encode($list),0,3600);
        return $list;
    }
    // 添加活动时选择级别 只有1个级别不用用户选择
    public function addEventAllLevel($sid) {
        $level = $this->allLevel($sid);
        $levelCnt = count($level);
        if($levelCnt>1){
            return $level;
        }
        return array();
    }
    public function addEventLevel($title,$sid){
        $map['title'] = $this->_checkTitle($title);
        if(!$map['title']){
            return 0;
        }
        $map['sid'] = $sid;
        $hasId = $this->where($map)->getField('id');
        if(!$hasId){ // 无重名
            $hasId = $this->add($map);
        }else{ //重名增加
            $this->where("id=$hasId")->setField('isdel',0);
        }
        $this->_cleanMmcAllLevel($sid);
        return $hasId;
    }
    public function editEventLevel($title,$sid,$id){
        $map['title'] = $this->_checkTitle($title);
        if(!$map['title']){
            return 0;
        }
        $map['sid'] = $sid;
        $hasId = $this->where($map)->getField('id');
        if(!$hasId){ // 无重名
            $map['isdel'] = 0;
            $this->where("id=$id")->save($map);
            $this->_cleanMmcAllLevel($sid);
        }elseif($hasId!=$id){ //重名编辑
            $this->error = '级别重名，不可修改。可以尝试添加';
            return 0;
        }
        return $id;
    }
    //学校后台 级别/分类/学分列表
    public function allLevelWithCredit($sid,$typeList) {
        $level = $this->allLevel($sid);
        if(!$level){
            return array();
        }
        $dao = D('EventCredit','event');
        foreach ($level as &$v) {
            $levelId = $v['id'];
            $credits = $dao->creditsByLevelId($levelId);
            $v['credits'] = $this->_calcCredits($credits,$typeList);
            
        }
        return $level;
    }
    public function getLevelNameById($id){
        $level = $this->getField('title','id='.$id);
        if($level){
            return $level;
        }
        return '';
    }
    public function getLevelCreditById($id,$sid){
        $credits = D('EventCredit','event')->creditsByLevelId($id);
        $typeList = D('EventType')->eventTypeDb($sid);
        return $this->_calcCredits($credits,$typeList);
    }
    public function delLevel($id,$sid){
        if(!$id||!$sid){
            return false;
        }
        $res = $this->setField('isdel',1,'id='.$id.' and sid='.$sid);
        if(!$res){
            return false;
        }
        D('EventCredit','event')->delCredit($id);
        $this->_cleanMmcAllLevel($sid);
        return true;
    }

    private function _calcCredits($credits,$typeList) {
        $creditArr = orderArray($credits, 'typeId');
        $res = array();
        foreach ($typeList as $v) {
            $typeId = $v['id'];
            $row = 0;
            if(isset($creditArr[$typeId])){
                $row = $creditArr[$typeId]['credit'];
            }
            $res[] = $row;
        }
        return $res;
    }
    private function _checkTitle($title){
        $isnull = preg_replace("/[ ]+/si", "", t($title));
        if (empty($isnull)) {
            $this->error = '级别名不能为空';
            return false;
        }
        return $isnull;
    }
    private function _cleanMmcAllLevel($sid){
        $key = 'EventLevel_allLevel_'.$sid;
        Mmc($key, null);
        $this->allLevel($sid);
    }

}
