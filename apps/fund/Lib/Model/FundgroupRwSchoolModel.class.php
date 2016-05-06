<?php

/*
 * 投资基金投放地区
 *
 */
class FundgroupRwSchoolModel extends Model{

    //计算投放学校
    public function addSchool($rwId) {
        $data['rwId'] = $rwId;
        $data['china'] = 1;
        $this->where("rwId=$rwId")->delete();
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
        $this->_insertSchool($rwId,$allProv,$allCity,$sids);
        return true;
    }
    //前台编辑地区
    public function editbarSchool($rwId){
        $list = $this->where("rwId=$rwId")->findAll();
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
//        var_dump($res);die;
        return $res;
    }
    //是否该校可申请任务
    public function isSchoolRw($sid,$rwId){
        $map['rwId'] = $rwId;
        $map['_string'] = 'china=1 OR sid='.$sid;
        $cityId = M('school')->getField('cityId', 'id='.$sid);
        if($cityId){
            $map['_string'] .= ' OR cityId='.$cityId;
            $provId = M('citys')->getField('pid', 'id='.$cityId);
            if($provId){
                $map['_string'] .= ' OR provId='.$provId;
            }
        }
        $res = $this->where($map)->field('id')->find();
        if($res){
            return true;
        }
        return false;
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
        $cityId = M('school')->getField('cityId', "id=$sid");
        if(!$cityId){
            return;
        }
        $provId = M('citys')->getField('pid', "id=$cityId");
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
    private function _insertSchool($rwId,$allProv,$allCity,$sids){
        $data = array('rwId' => $rwId);
        foreach ($allProv as $prov) {
            $data['provId'] = $prov;
            $this->add($data);
        }
        $data = array('rwId' => $rwId);
        foreach ($allCity as $city) {
            $data['cityId'] = $city;
            $this->add($data);
        }
        $data = array('rwId' => $rwId);
        foreach ($sids as $sid) {
            $data['sid'] = $sid;
            $this->add($data);
        }
    }
    
}
