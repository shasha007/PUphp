<?php

/*
 * 活动模型
 *
 */
class FundEventModel extends Model{

    //前台各学校申报活动列表
    public function listBySid($sid,$order='a.byTime asc, a.eventId DESC',$limit=10){
        $map['a.byTime'] = array('gt', time());
        $map['a.isDel'] = 0;
        $map['_string'] = 'china=1 OR sid='.$sid;
        $cityId = model('Schools')->getCityId($sid);
        $provId = model('Schools')->getProvId($sid);
        if($cityId){
            $map['_string'] .= ' OR cityId='.$cityId;
        }
        if($provId){
            $map['_string'] .= ' OR provId='.$provId;
        }
        $res = M('')->table("ts_fund_event AS a ")
                        ->join("ts_fund_event_school AS b ON a.eventId=b.fundEvnetId")
                        ->field('a.eventId,a.company,a.eventName,a.logo,byTime')
                        ->where($map)->order($order)->group('a.eventId')->findPage($limit);
        foreach($res['data'] as &$val){
            $val['count'] = D('FundApplyevent')->groupCount($val['eventId']);
        }
        return $res;
    }
    //api各学校申报活动列表
    public function apiListBySid($sid,$page,$limit=6){
        $map['a.byTime'] = array('gt', time());
        $map['a.isDel'] = 0;
        $map['_string'] = 'china=1 OR sid='.$sid;
        $cityId = model('Schools')->getCityId($sid);
        $provId = model('Schools')->getProvId($sid);
        if($cityId){
            $map['_string'] .= ' OR cityId='.$cityId;
        }
        if($provId){
            $map['_string'] .= ' OR provId='.$provId;
        }
        $offset = ($page - 1) * $limit;
        $order = 'a.byTime asc, a.eventId DESC';
        $res = M('')->table("ts_fund_event AS a ")
                        ->join("ts_fund_event_school AS b ON a.eventId=b.fundEvnetId")
                        ->field('a.eventId as id,a.eventName as title,a.company as companyName,a.logo as pic,byTime')
                        ->where($map)->order($order)->group('a.eventId')->limit("$offset,$limit")->findAll();
        if (!$res)  return array();
        return $res;
    }
     /**
      * 删除活动
      * $id 活动d
      */
     public function delete($id){
         $data['isDel'] = 1;
         $map['eventId'] = $id;
         $res = $this->where($map)->save($data);
         if($res){
             return false;
         }else{
             return true;
         }
     }

    
     public function doEdit($input = array()) {
        if(empty($input)) {
            $input = $_POST;
        }
        $eventId = intval($input['eventId']);
        //参数合法性检查
        $required_field = array(
            'company' => '公司名',
            'eventName' => '活动名称',
            'ctime' => '活动开始时间',
            'endtime' => '活动结束时间',
            'bytime' => '承办截止时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($input[$k])){
                $this->error = $v . '不可为空';
                return false;
            }
        }

        if (!$eventId && !$input['imgid1']) {
            $this->error = '请上传logo';
            return false;
        }

        $data['company'] = t($input['company']);
        $data['eventName'] = t($input['eventName']);
        $data['cTime'] = strtotime($input['ctime']);
        $data['endTime'] = strtotime($input['endtime']);
        $data['byTime'] = strtotime($input['bytime']);
        $data['descript'] = t(h($input['content']));
        if ($data['cTime'] >= $data['endTime']) {
            $this->error = '活动结束时间不得小于开始时间';
            return false;
        }
        if ($data['byTime'] >= $data['cTime']) {
            $this->error = '承办截止日期不能大于活动开始日期';
            return false;
        }
        //活动logo
        if ($input['imgid1']) {
            $data['logo'] = $input['imgid1'][0];
        }

        if ($eventId) {
            M('FundEvent')->where('eventId='.$eventId)->save($data);
        }else{
            $eventId = M('FundEvent')->add($data);
        }
        unset($data);
        if(!$eventId){
            $this->error = '操作失败';
            return false;
        }
        //绑定学校
        D('FundEventSchool')->addSchool($eventId);
        //删除此次活动缓存
        Mmc('fund_noeDetail_'.$eventId,null);
        return true;
    }
    //可申请的部落
    public function _canApplyGroup($eid,$mid){
        $fundEvent = $this->where('eventId='.$eid)->field('byTime')->find();
        if(!$fundEvent){
            $this->error = '活动不存在';
            return array();
        }
        if($fundEvent['byTime']<time()){
            $this->error = '承办已截止';
            return array();
        }
        //本校只能申请一次
        $sid = getUserField($mid, 'sid');
        $hasSelfSid = M('FundApplyevent')->where("eventId=$eid and sid=$sid and state=1")->count();
        if($hasSelfSid){
            $this->error = '已有部落承接承办';
            return array();
        }
        //活动部落 ？？
        $group = M('event_group')->where('uid='.$mid)->field('gid')->findAll();
        if(!$group){
            $this->error = '部落活动管理员才可申请';
            return array();
        }
        $hasApply = M('FundApplyevent')->where("eventId=$eid and sid=$sid and state!=-1")->field('gid')->findAll();
        $hasGids = getSubByKey($hasApply, 'gid');
        foreach ($group as $k=>$v) {
            if(in_array($v['gid'], $hasGids)){
                unset($group[$k]);
            }
        }
        if(empty($group)){
            $this->error = '您已申请';
            return array();
        }
        return $group;
    }

}

