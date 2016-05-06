<?php

/**
 * 活动显示于学校
 */
class EventSchool2Model extends Model {

    public function removeByEid($eventId){
        $this->where("eventId=$eventId")->delete();
    }
    //计算投放学校
    public function addSchool($eventId) {
        $data['eventId'] = $eventId;
        $data['china'] = 1;
        $this->removeByEid($eventId);
        if(!isset($_POST['prov'])){
            $this->add($data);
            return true;
        }
        $allProv = array();
        $allCity = array();
        $restCity = array();
        foreach ($_POST['prov'] as $k => $prov) { //循环省、市
            if($prov==0){ //无省表示全国
                $this->add($data);
                return true;
            }
            $city = $_POST['city'][$k];
            if($city==0){//无市表示全省
                $allProv[] = $prov;
                continue;
            }
            if($this->_isAllCitySchool($city)){//无校表示全市
                $allCity[] = $city;
                continue;
            }
            $restCity[$city] = array('provId'=>$prov);
        }
        $allProv = array_unique($allProv);
        $allCity = array_unique($allCity);
        $sids = $this->_calcSidNoProvNoCity($allProv,$allCity,$restCity);
        $sids = array_unique($sids);
        $this->_insertSchool($eventId,$allProv,$allCity,$sids);
        return true;
    }
    //前台编辑地区
    public function editbarSchool($eventId){
        $list = $this->where("eventId=$eventId")->findAll();
        $res = array();
        foreach ($list as $v) {
            if($v['china']==1){
                return array();
            }
            if($v['provId']>0){
                $this->_areaAllProv($v['provId'], $res);
            }elseif($v['cityId']>0){
                $this->_areaAllCity($v['cityId'], $res);
            }else{
                $this->_areaBySid($v['sid'], $res);

            }
        }
        return $res;
    }
    public function schoolString($eventId){
        $list = $this->where("eventId=$eventId")->findAll();
        $res = '';
        foreach ($list as $v) {
            if($v['china']==1){
                return '全国';
            }
            if($v['provId']>0){
                $res .= M('province')->getField('title', 'id='.$v['provId']);
                $res .=  '（全省）;';
            }elseif($v['cityId']>0){
                $res .= M('citys')->getField('city', 'id='.$v['cityId']);
                $res .=  '（全市）;';
            }else{
                $res .= M('school')->getField('title', 'id='.$v['sid']);
                $res .=  ';';
            }
        }
        return $res;
    }
    //是否该校可参加活动
    public function isSchoolEvent($sid,$eventId){
        $map['eventId'] = $eventId;
        $map['_string'] = 'china=1 OR sid='.$sid;
        $cityId = model('Schools')->getCityId($sid);
        $provId = model('Schools')->getProvId($sid);
        if($cityId){
            $map['_string'] .= ' OR cityId='.$cityId;
        }
        if($provId){
            $map['_string'] .= ' OR provId='.$provId;
        }
        $res = $this->where($map)->field('eventId')->find();
        if($res){
            return true;
        }
        return false;
    }
    //db搜索组装where语句
    public function selectWhere($sid,$prefix='',$otherWhere=''){
        $where = $prefix.'china=1 OR '.$prefix.'sid='.$sid;
        $cityId = model('Schools')->getCityId($sid);
        $provId = model('Schools')->getProvId($sid);
        $map = array();
        if($cityId){
            $map['_string'] .= ' OR cityId='.$cityId;
        }
        if($provId){
            $map['_string'] .= ' OR provId='.$provId;
        }
        $where = $where.$map['_string'] ;
        if($otherWhere){
            return '('.$otherWhere.') AND ('.$where.')';
        }
        return $where;
    }
    //检查是否全市学校
    private function _isAllCitySchool($cityId){
        $sidKey = 'schools'.$cityId;
        if(!isset($_POST[$sidKey])){//无校表示全市
            return true;
        }
        $allSchool = M('school')->where('cityId='.$cityId)->field('id')->findAll();
        foreach ($allSchool as $school) {
            if(!in_array($school['id'], $_POST[$sidKey])){
                return false;
            }
        }
        return true;
    }
    private function _areaAllProv($provId,&$res){
        $row['prov'] = $provId;
        $row['citys'] = M('citys')->where("pid=".$row['prov'])->field('id,city')->order('short asc')->findAll();
        $row['city'] = 0;
        $row['schools'] = array();
        $row['sids'] = array();
        $res[] = $row;
    }
    private function _areaAllCity($cityId,&$res){
        $row['city'] = $cityId;
        $row['prov'] = M('citys')->getField('pid', 'id='.$cityId);
        $row['citys'] = M('citys')->where("pid=".$row['prov'])->field('id,city')->order('short asc')->findAll();
        $row['schools'] = M('school')->where("cityId=".$row['city'])->field('id,title')->order('display_order asc')->findAll();
        $row['sids'] = getSubByKey($row['schools'], 'id');
        array_unshift($row['sids'], 0);
        $res[] = $row;
    }
    private function _areaBySid($sid,&$res){
        $cityId = model('Schools')->getCityId($sid);
        if(!$cityId){
            return;
        }
        $provId = model('Schools')->getProvId($sid);
        if(!$provId){
            return;
        }
        foreach($res as $k=>$v){
            if($v['prov']==$provId && $v['city']==0){
                return;
            }
            if($v['prov']==$provId && $v['city']==$cityId){
                if(!in_array($sid, $v['sids'])){
                    $res[$k]['sids'][] = $sid;
                }
                return;
            }
        }
        $row['prov'] = $provId;
        $row['citys'] = M('citys')->where("pid=".$row['prov'])->field('id,city')->order('short asc')->findAll();
        $row['city'] = $cityId;
        $row['schools'] = M('school')->where("cityId=".$row['city'])->field('id,title')->order('display_order asc')->findAll();
        $row['sids'][] = $sid;
        $res[] = $row;
        return;
    }
    //计算不在全省、全市内的sid
    private function _calcSidNoProvNoCity($noProv,$noCity,$calcSity) {
        $addtionSid = array();
        foreach ($calcSity as $city => $prov) {
            if(in_array($prov, $noProv)){
                continue;
            }
            if(in_array($city, $noCity)){
                continue;
            }
            $sidKey = 'schools'.$city;
            foreach ($_POST[$sidKey] as $sid) {
                $addtionSid[] = $sid;
            }
        }
        return $addtionSid;
    }
    private function _insertSchool($eventId,$allProv,$allCity,$sids){
        $data = array('eventId' => $eventId);
        foreach ($allProv as $prov) {
            $data['provId'] = $prov;
            $this->add($data);
        }
        $data = array('eventId' => $eventId);
        foreach ($allCity as $city) {
            $data['cityId'] = $city;
            $this->add($data);
        }
        $data = array('eventId' => $eventId);
        foreach ($sids as $sid) {
            $data['sid'] = $sid;
            $this->add($data);
        }
    }
}
