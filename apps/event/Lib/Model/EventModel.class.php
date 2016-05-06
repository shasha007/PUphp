<?php

include_once SITE_PATH . '/apps/event/Lib/Model/BaseModel.class.php';

/**
 * EventModel
 * 活动主数据库模型
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class EventModel extends BaseModel {

    var $mid;

    //首页PU推荐活动
    public function getPuRecomm($sid){
        $key = 'EventModel_getPuRecomm_'.$sid;
        $cache = Mmc($key);
        if ($cache !== false) {
            return json_decode($cache, true);
        }
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['puRecomm'] = array('gt', 0);
        $map['school_audit'] = array('neq', 5);
        $map['_string'] = D('EventSchool2','event')->selectWhere($sid,'b.');
        $list =  $this->table("ts_event AS a ")->join("ts_event_school2 AS b ON a.id=b.eventId")
                    ->field('id,title,coverId,sTime,eTime,uid,typeId')
                    ->where($map)->order('puRecomm desc, a.id DESC')->limit(4)->findAll();
        Mmc($key, json_encode($list),0,3600);
        return $list;
    }
    //首页置顶推荐活动
    public function getSchoolIndex($map,$limit=4,$withZj=false){
        $map['isDel'] = 0;
        $map['status'] = 1;
        $this->where($map)->field('id,title,sid,typeId,coverId,sTime,eTime,uid')
                    ->order('id DESC')->limit($limit);
        $sql = $this->setExe(false)->select();
        $key = md5($sql);
        $cache = Mmc($key);
        if ($cache !== false) {
            return json_decode($cache, true);
        }
        $res = $this->select();
        Mmc($key, json_encode($res), 0, 60*30);
        return $res;
    }

    public function getSlide($sid){
        $key = 'EventModel_getSlide_'.$sid;
        $cache = Mmc($key);
        if ($cache !== false) {
            return json_decode($cache, true);
        }
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['is_school_event'] = $sid;
        $map['isHot'] = 1;
        //$map['logoId'] = array('neq', 0);
        $slide = $this->where($map)->order('id DESC')->field('id,title,logoId,typeId,default_banner')->limit(5)->findAll();
        Mmc($key, json_encode($slide),0,3600);
        return $slide;
    }

    public function getHomeList($map = '', $mid = 0, $limit = 3, $order = 'id DESC') {
        $this->mid = $mid;
        $result = $this->where($map)->order($order)->limit($limit)->findAll();
        if (!empty($result)) {
            $user = self::factoryModel('user');
            foreach ($result as &$value) {
                $value['cover'] = tsGetCover($value['coverId']);
                $value['canJoin'] = true;
                if ($mid > 0) {
                    $userDao = self::factoryModel('user');
                    if ($hasUser = $userDao->hasUser($this->mid, $value['id'])) {
                        $value['canJoin'] = false;
                    }
                }
            }
        }
        return $result;
    }

    public function handyGetEventList($mid, $map = array(), $limit=10, $page=1, $order = 'isTop DESC, id DESC'){
        $this->mid = $mid;
        if(isset($map['b.sid'])){
            $sid = $map['b.sid'];
            unset($map['b.sid']);
            $map['_string'] = D('EventSchool2','event')->selectWhere($sid,'b.',$map['_string']);
            $map['_string'] .= ' OR is_prov_event=2 ';
            //var_dump($map);
            $this->table("ts_event AS a ")->join("ts_event_school2 AS b ON a.id=b.eventId")
                    ->field('a.id,a.sid as cid,a.credit,a.isTop,a.gid,title,coverId,sTime,eTime,joinCount,note,is_prov_event,address')
                    ->where($map)
                    ->order($order);
        }else{
            $this->field('id,title,coverId,sTime,eTime,joinCount,note,is_prov_event,address')
            		->where($map)
            		->order($order);
        }
        $offset = ($page - 1) * $limit;
        $this->limit("$offset,$limit");
        $sql = $this->setExe(false)->select();
        //var_dump($sql);
        //exit();
        $key = md5($sql);
        $cache = Mmc($key);
        if ($cache !== false) {
            return json_decode($cache, true);
        }

        $data = $this->select();
//        $cate = D('EventType','event')->getType();
        foreach ($data as &$row) {
            $row['title'] = htmlspecialchars_decode($row['title'], ENT_QUOTES);
            $row['address'] = htmlspecialchars_decode($row['address'], ENT_QUOTES);
            $row['cover'] = tsGetCover($row['coverId']);
            $row['logo'] = $row['cover'];
            $row['sTime'] = date('Y-m-d H:i', $row['sTime']);
            $row['eTime'] = date('Y-m-d H:i', $row['eTime']);
            $row['friendCount'] = $this->getEventFriendNum($row['id']);
//            if($row['is_prov_event']==2){
//                $row['sName'] = 'PU平台';
//            }else{
//                $row['sName'] = tsGetSchoolTitle($row['is_school_event']);
//            }
			$row['isTop'] = $row['isTop'] == 0 ? '' : '推荐';
//			20160218修改：学校标签全部的学院名称改成“院”，
//          $row['cName'] = $row['cid']<0 ? '校' : tsGetSchoolTitle($row['cid']);
			$row['cName'] = $row['cid']<0 ? '校' : '院';
            $row['isCredit'] = intval($row['credit']) == 0 ? '' : '学分';
            unset($row['cid']);
            unset($row['credit']);
            //活动发起
            if($row['is_prov_event']==1){
                $row['area'] = 3;
            }elseif($row['is_prov_event']==0){
                $row['area'] = 2;
            }else{
                $row['area'] = 1;
            }
            //三下乡
            $sj = $this->_sjJoinCount($row['id']);
            if($sj['isSj']){
                $row['joinCount'] = $sj['joinCount'];
            }
        }
        $expire = 60*10;
        if(isset($map['gid'])){
            $expire = 3600*5; //部落活动列表可缓存时间长点
        }
        Mmc($key, json_encode($data), 0, $expire);
        return $data;
    }
    
    /**
     * @todo 获取当前活动参与的好友数量
     * @param int $eventId
     * @return int $count
     */
    protected function getEventFriendNum($eventId)
    {
		$uid = $this->mid;
		$map['uid'] = intval($uid);
		$fidArr = M('weibo_follow')->where($map)->field('fid')->select();
		if(empty($fidArr))
		{
			return 0;
			die;
		}	
		foreach ($fidArr as $value) 
		{
			$arr[] = $value['fid'];
		}
		$map_event['uid'] = array('IN',join(',', $arr));
		$map_event['eventId'] = intval($eventId);
		$count = M('event_user')->where($map_event)->count();
		return $count;
    }
    
    //20151230yangjun
    public function activeEventList($mid, $map = array(), $limit=10, $page=1, $order = 'isTop DESC, id DESC'){
    	$this->mid = $mid;
    	if(isset($map['b.sid'])){
    		$sid = $map['b.sid'];
    		unset($map['b.sid']);
    		$this->table("ts_event AS a ")->join("ts_event_school2 AS b ON a.id=b.eventId")
    		->field('a.id,a.sid as cid,a.credit,a.isTop,a.gid,title,coverId,sTime,eTime,joinCount,note,is_prov_event,address')
                    ->where($map)->order($order);
    	}else{
    		$this->field('id,gid,title,coverId,sTime,eTime')
                    ->where($map)->order($order);
    	}
    	$offset = ($page - 1) * $limit;
    	$this->limit("$offset,$limit");
    	$sql = $this->setExe(false)->select();
    	$key = md5($sql);
    	$cache = Mmc($key);
    	if ($cache !== false) {
    		return json_decode($cache, true);
    	}
    
    	$data = $this->select();
    
    	foreach ($data as &$row) {
    		$row['title'] = htmlspecialchars_decode($row['title'], ENT_QUOTES);
    		$row['cover'] = tsGetCover($row['coverId']);
    		$row['sTime'] = date('Y-m-d H:i', $row['sTime']);
    		$row['eTime'] = date('Y-m-d H:i', $row['eTime']);
    		$row['uname'] = getUserField($this->mid, 'uname');
    		$row['isTop'] = $row['isTop'] == 0 ? '' : '推荐';
    		$row['cName'] = $row['cid']<0 ? '校' : tsGetSchoolTitle($row['cid']);
    		$row['isCredit'] = intval($row['credit']) == 0 ? '' : '学分';
    		unset($row['coverId']);
    		unset($row['uid']);
    	}
    	$expire = 60*10;
    	if(isset($map['gid'])){
    		$expire = 3600*5; //部落活动列表可缓存时间长点
    	}
    	Mmc($key, json_encode($data), 0, $expire);
    	return $data;
    }
    
    private function _sjJoinCount($id){
        $res['isSj'] = false;
        $type = 0;
        if ($id == C('SJ_GROUP')) {
            $type = 3;
        }elseif($id == C('SJ_PERSON')) {
            $type = 5;
        }elseif($id == C('SJ_FS')) {
            $type = 9;
        }
        if($type==0){
            return $res;
        }
        $map['type'] = $type;
        $map['status'] = 5;
        $map['year'] = C('SJ_YEAR');
        $res['joinCount'] = M('sj')->where($map)->count();
        $res['isSj'] = true;
        return $res;
    }

    public function getEvent($mid, $id){
        $this->mid = $mid;
        if(!$id){
            return array();
        }
        $row = $this->_apiEvent($id);
        if(empty($row))
        {
            return array();
        }
        if($row['isDel'] == 1)
        {
            return array();
        }
        if($row['joinCount']<=0){
            $row['eventUser'] = array();
        }else{
            $row['eventUser'] = $this->_apiJoinUser($id);
        }
        if(empty($row['address'])){
            $row['address'] = '线上';
        }
        //是否己参与
        $row['hasJoin'] = 0;
        $joinUser = D('EventUser','event')->hasUser($mid, $id);
        if($joinUser) {
            $row['hasJoin'] = 1;
        }
        $row['joinAudit'] = 1;
        if(!$joinUser || $joinUser['status']==0) {
            $row['joinAudit'] = 0;
        }
        //是否可报名
        $row['canJoin'] = 0;
        if(!$row['isFinish'] && !$row['hasJoin'] && $row['limitCount'] > 0){
            $row['canJoin'] = 1;
        }
        unset($row['limitCount']);

        //是否己参与评分
        $row['hasNoted'] = intval(D('EventNote','event')->hasNoted($id, $mid));
        //是否已收藏
        $colleted = M('event_collection')->where('uid='.$mid)->findAll();
        $colIds = getSubByKey($colleted, 'eid');
        $row['hasFav'] = false;
        if($colIds && in_array($id, $colIds)){
            $row['hasFav'] = true;
        }
        if($mid == $row['uid']&&empty($row['adminCode'])){
            $row['adminCode'] = $this->getAdminCode($id);
        }
        //评论
        $row['comment'] = $this->_apiComment($id);
        return $row;
    }
    //活动评论
    private function _apiComment($id){
        $cache = Mmc('event_apiComment_'.$id);
        if ($cache !== false) {
            return json_decode($cache, true);
        }
        $map1['type'] = 'event';
        $map1['appid'] = $id;
        $clist = M('comment')->where($map1)->field('id,uid,comment,cTime as ctime')->order('id DESC')->limit(5)->findAll();
        if(empty($clist)){
            $clist = array();
        }else{
            foreach ($clist as &$v) {
                $v['uname'] = getUserName($v['uid']);
                $v['face'] = getUserFace($v['uid'],'b');
                if(empty($v['comment'])){
                    $v['comment'] = ' ';
                }
                $v['comment'] = htmlspecialchars_decode($v['comment'], ENT_QUOTES);
                $v['ctime'] = date('Y-m-d H:i', $v['ctime']);
            }
        }
        Mmc('event_apiComment_'.$id, json_encode($clist), 0, 60*60);
        return $clist;
    }
    private function _apiJoinUser($id){
        $cache = Mmc('event_apiJoinUser_'.$id);
        if ($cache !== false) {
            return json_decode($cache, true);
        } 
        //报名人员
        $umap['eventId'] = $id;
        $umap['status'] = array('neq','0');
        $eventUser = M('event_user')->where($umap)->field('uid,realname')->limit(7)->order('id DESC')->findAll();
        if($eventUser){
            foreach ($eventUser as &$v) {
                $v['uface'] = getUserFace($v['uid'],'b');
            }
        }else{
            $eventUser = array();
        }
        Mmc('event_apiJoinUser_'.$id, json_encode($eventUser), 0, 60*60);
        return $eventUser;
    }
    private function _apiEvent($id){
        $cache = Mmc('event_getEvent_'.$id);
        //去掉取缓存
       /*  if ($cache !== false) {
            return json_decode($cache, true);
        } */
        return $this->_updateEventCache($id);
    }
    private function _updateEventCache($id)
    {
        $map['isDel'] = 0;
        $map['id'] = $id;
        $row = $this->where($map)->field('id,adminCode,uid,gid,title,logoId,coverId,sTime,eTime,isDel,uid,sid,typeId as cid,score as activityScore,credit as practiceCredits,default_banner,
            address,cost,costExplain,limitCount,contact,joinCount,note,noteUser,startline,deadline,school_audit,is_school_event,is_prov_event,need_tel,free_attend,description')
                ->find();

        if(!$row){
            return array();
        }
        //增加字段活动四个时间戳
        $row['applyStartTime'] = $row['startline'];
        $row['applyEndTime'] = $row['deadline'];
        $row['eventEndTime'] = $row['eTime'];
        $row['eventStartTime'] = $row['sTime'];

        $config = D('SchoolWeb','event')->getConfigCache($row['is_school_event']);
        $row['practiceCreditsTitle'] = $config['cradit_name']; //个人中心“实践学分”字段由服务器动态提供

        $row['title'] = htmlspecialchars_decode($row['title'], ENT_QUOTES);
        $row['title'] = mb_substr($row['title'], 0, 20, 'UTF8');
        $row['description'] = htmlspecialchars_decode($row['description'], ENT_QUOTES);
        $row['address'] = htmlspecialchars_decode($row['address'], ENT_QUOTES);
        $row['area'] = 1;
        if($row['is_prov_event']==1){
            $row['area'] = 3;
        }elseif($row['is_prov_event']==0){
            $row['area'] = 2;
        }
        unset($row['is_prov_event']);
        //是否己结束
        $row['isFinish'] = 0;
        if($row['school_audit'] == 5 || $row['deadline'] <= time()){
            $row['isFinish'] = 1;
        }
        unset($row['school_audit']);
        //是否显示签到
        $row['showAttend'] = 1;
        //unset($row['is_school_event']);
        //经费
        $row['cost'] = tsGetCost($row['cost']);
        $row['banner'] = tsGetLogo($row['logoId'],$row['cid'],$row['default_banner'],480,270,'f','m');
        $row['cover'] = tsGetCover($row['coverId']);
        unset($row['logoId']);
        unset($row['coverId']);
        $row['isStart'] = $row['sTime'] < time() ? 0 : 1;
        $start = $row['startline']==0?'':date('m-d H:i', $row['startline']);
        $row['startline'] = $row['sTime'];
        $row['sTime'] = date('m-d H:i', $row['sTime']);
        $row['eTime'] = date('m-d H:i', $row['eTime']);
        $row['deadline'] = $start.' 至 '.date('m-d H:i', $row['deadline']);
        $row['uname'] = getUserRealName($row['uid']);
        $row['uface'] = getUserFace($row['uid'],'b');
        $row['sName'] = '';
        $row['cName'] = '';
        $row['orga'] = '';
        //投票人数
        $row['ePlayer'] = $this->_apiPlayer($id);
        //新闻数
        $row['eNews'] = D('event_news')->where('eventId = '.$map['id'])->count();
        //是否有新闻
        $row['news'] = $row['eNews'];
        //是否有花絮
        $row['photo'] = D('EventImg')->where(array('eventId' => $map['id'], 'uid'=>0))->count();
        Mmc('event_getEvent_'.$id, json_encode($row), 0, 3600*2);
        return $row;
    }
    //活动详情 排行榜选手
    private function _apiPlayer($id){
        if ($id == C('SJ_GROUP')) {
            $playerList = $this->_sjPlayer(3);
        }elseif ($id == C('SJ_PERSON')) {
            $playerList = $this->_sjPlayer(5);
        }elseif ($id == C('SJ_FS')) {
            $playerList = $this->_sjPlayer(9);
        }else{
            $playerList = D('event_player')->where('eventId = '.$id)->field('uid,realname,path')->limit(2)->select();
        }
        
        if($playerList){
            foreach ($playerList as $k => $v) {
                if($v['uid'] == 0)
                {
                    $playerList[$k]['uface'] = tsGetEventUserThumb($v['path'], 163,204,'c');
                }  
                else 
                {
                    $playerList[$k]['uface'] = getUserFace($v['uid'],'b');
                }
            }
            return $playerList;
        }
        return array();
    }
    private function _sjPlayer($type){
        $map['type'] = $type;
        $map['status'] = 5;
        $map['year'] = C('SJ_YEAR');
        return M('sj')->where($map)->field('uid,title as realname')->limit(7)->select();
    }

    public function canAttend($mid, $event, $checkTime=true){
        if($event['school_audit']==5){
            $this->error = '活动已完结，不可签到';
            return false;
        }
        if($checkTime){
            $now = time();
            //开始前1小时签到
            $startTime = $event['sTime']-3600;
            if($now > $event['eTime'] || $now < $startTime){
                $this->error = '签到失败，签到时间活动前一小时至活动结束';
                return false;
            }
        }
        $user = D('EventUser','event')->hasUser($mid,$event['id']);
        if($user && $user['status'] == 2){
            $this->error = '签到成功';
            return false;
        }
        if(!$event['free_attend']){
            if(!$user){
                $this->error = '签到失败，您尚未报名';
                return false;
            }
            if($user['status'] == 0){
                $this->error = '签到失败，您的报名尚未通过审核';
                return false;
            }
        }
        return true;
    }

    public function isAttend($mid,$code) {
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['adminCode'] = strtoupper($code);
        $event = $this->where($map)->field('id,sTime,eTime,school_audit,free_attend')->find();
        if (!$event) {
            $this->error = '活动码错误，或活动已结束';
            return false;
        }
        $canAttend = $this->canAttend($mid,$event);
        if (!$canAttend) {
            return false;
        }
        return true;
    }

    //管理员后台帮忙签到
    public function adminUserAttend($mid, $eventId) {
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['id'] = $eventId;
        $event = $this->where($map)->field('id,sTime,eTime,school_audit,free_attend,gid')->find();
        if (!$event) {
            $this->error = '签到失败';
            return false;
        }
        $canAttend = $this->canAttend($mid,$event,false);
        if (!$canAttend) {
          return false;
        }
        $daoUser = D('EventUser', 'event');
        if (!$daoUser->attend($mid, $event['id'])) {
            $this->error = '签到失败';
            return false;
        }
        return true;
    }
    //手机扫描个人二维码签到
    public function apiAttend($mid,$code,$uid)
    {
        $upCode = strtoupper($code);
        //活动详情
        $event = $this->_getAttendEvent($upCode);
        if(!$event){
            return false;
        }
        //活动签到码授权的人数判断
        $codeUser = $this->_eventCodeUser($upCode, $mid,$event['codelimit']);
        if(!$codeUser){
            return false;
        }
        //活动成员签到
        $daoUser = D('EventUser','event');
        $result = $daoUser->apiAttend($uid,$event['id'],$event['free_attend']);
        if(!$result){
            $this->error = $daoUser->getError();
            return false;
        }
        return true;
    }
    private function _getAttendEvent($code)
    {
        $key = '_getAttendEvent_'.$code;
        $cache = Mmc($key);
        if($cache !== false){
            $event = json_decode($cache,true);
        }else{
            $map['isDel'] = 0;
            $map['status'] = 1;
            $map['adminCode'] = $code;
            $event = $this->where($map)->field('id,sTime,eTime,school_audit,free_attend,codelimit')->find();
            if(!$event){
                $this->error = '无效活动码或您无权签到';
                return false;
            }
            if($event['school_audit']==5){
                $this->error = '活动已完结，不可签到';
                return false;
            }
        }
        $now = time();
        //开始前1小时签到
        $startTime = $event['sTime']-3600;
        if($now > $event['eTime'] || $now < $startTime){
            $this->error = '签到失败，签到时间活动前一小时至活动结束';
            return false;
        }
        Mmc($key, json_encode($event),0,3600*2);
        return $event;
    }
    private function _upCacheAttendEvent($code,$change)
    {
        if(!$code){
            return false;
        }
        $key = '_getAttendEvent_'.$code;
        $cache = Mmc($key);
        if($cache === false){
            return false;
        }
        $event = json_decode($cache,true);//sTime,eTime,school_audit,free_attend,codelimit
        if(isset($change['sTime'])) $event['sTime'] = $change['sTime'];
        if(isset($change['eTime'])) $event['eTime'] = $change['eTime'];
        if(isset($change['school_audit'])) $event['school_audit'] = $change['school_audit'];
        if(isset($change['free_attend'])) $event['free_attend'] = $change['free_attend'];
        if(isset($change['codelimit'])) $event['codelimit'] = $change['codelimit'];
        Mmc($key, json_encode($event),0,3600*2);
    }
    //活动签到码授权的人数判断
    private function _eventCodeUser($code,$mid,$codeLimit)
    {
        $key = 'eventCodeUser_'.$code.'_'.$mid;
        $cache = Mmc($key);
        if($cache !== false){
            return true;
        }
        $daoCode = M('event_code');
        $where['adminCode'] = $code;
        $where['uid'] = $mid;
        $result = $daoCode->where($where)->find();
        if(!$result){
           $codelimit = $codeLimit ? $codeLimit : 5;
           $code = $daoCode->where("`adminCode`='$code'")->count();
           if($code>=$codelimit){
                 $this->error = '授权人数已满,您无权签到';
                 return false;
           }else{
               $daoCode->add($where);
           }
        }
        Mmc($key,1,0,3600*4);
        return true;
    }

    public function getQrCode($eid){
        $event = $this->where('id='.$eid)->field('id,attendCode')->find();
        if(!$event){
            return '';
        }elseif($event['attendCode']){
            return $event['attendCode'];
        }else{
            require_once(SITE_PATH . '/addons/libs/String.class.php');
            $randval = String::rand_string(5);
            $code = $randval.$eid;
            $this->setField('attendCode', $code, 'id='.$eid);
            return $code;
        }
    }

    public function getAdminCode($eid){
        $event = $this->where('id='.$eid)->field('id,adminCode')->find();
        if(!$event){
            return '';
        }elseif($event['adminCode']){
            return $event['adminCode'];
        }else{
            require_once(SITE_PATH . '/addons/libs/String.class.php');
            $randval = String::rand_string(2,5);
            $code = $randval.$eid;
            $this->setField('adminCode', $code, 'id='.$eid);
            return $code;
        }
    }

    public function getEventList($map = '', $mid, $order = 'isTop DESC, id DESC') {
        $this->mid = $mid;
        if(isset($map['b.sid'])){
            $sid = $map['b.sid'];
            unset($map['b.sid']);
            $map['_string'] = D('EventSchool2','event')->selectWhere($sid,'b.',$map['_string']);
            $result = $this->table("ts_event AS a ")->join("ts_event_school2 AS b ON a.id=b.eventId")->where($map)->order($order)
                    ->field('id,uid,title,typeId,sTime,eTime,startline,deadline,joinCount,coverId,
            isTop,a.sid,gid,credit,score,noteUser,note,school_audit,status,limitCount,allow,need_tel,free_attend,is_school_event')->findPage(10);
        }else{
            $result = $this->where($map)->order($order)->order($order)->field('id,uid,title,typeId,sTime,eTime,startline,deadline,joinCount,coverId,
                isTop,sid,gid,credit,score,noteUser,note,school_audit,status,limitCount,allow,need_tel,free_attend,is_school_event')->findPage(10);
        }
        //追加必须的信息
        if (!empty($result['data'])) {
            $colleted = M('event_collection')->where('uid='.$mid)->findAll();
            $colIds = getSubByKey($colleted, 'eid');
            $user = self::factoryModel('user');
            foreach ($result['data'] as &$value) {
                $value = $this->_appendContent($value);
                $value['cover'] = getCover($value['coverId']);
                //计算待审核人数
                if ($this->mid == $value['uid']) {
                    $value['verifyCount'] = $user->where("status = 0  AND eventId =" . $value['id'])->count();
                }
                //是否已评分
                $value['hasNoted'] = D('EventNote')->hasNoted($value['id'], $mid);
                //检查是否已收藏
                $value['hasColleted'] = false;
                if($colIds && in_array($value['id'], $colIds)){
                    $value['hasColleted'] = true;
                }
            }
        }
        return $result;
    }

    /**
     * 追加和反解析数据
     * @param mixed $data
     * @access public
     * @return void
     */
    private function _appendContent($data) {
        $type = self::factoryModel('type');
        $data['type'] = $type->getTypeName($data['typeId'],$data['is_school_event']);

        //反解析时间
        $data['time'] = date('Y-m-d H:i', $data['sTime']) . " 至 " . date('Y-m-d H:i', $data['eTime']);
        $data['dl'] = date('Y-m-d H:i', $data['deadline']);

        //追加权限
        $data += $this->checkMember($data['uid'], $this->mid);

        //追加是否已参加的判定
        $userDao = self::factoryModel('user');
        if ($result = $userDao->hasUser($this->mid, $data['id'])) {
            $data['canJoin'] = false;
            $data['hasMember'] = $result['status'];
            return $data;
        }

        return $data;
    }

    /**
     * checkRoll
     * 检查权限
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function checkMember($eventAdmin, $mid) {
        $result = array(
            'admin' => false,
            'canJoin' => true,
            'hasMember' => false,
        );
        if ($mid == $eventAdmin) {
            $result['admin'] = true;
            return $result;
        }

        return $result;
    }

    /**
     * doAddEvent
     * 添加活动
     * @param mixed $map
     * @param mixed $feed
     * @access public
     * @return void
     */
    public function doAddEvent($eventMap, $cover=array()) {
        $eventMap['cTime'] = isset($eventMap['cTime']) ? $eventMap['cTime'] : time();
        if (isset($cover['status']) && $cover['status']) {
            foreach ($cover['info'] as $value) {
                if ($value['key'] == 'cover') {
                    $eventMap['coverId'] = $value['id'];
                } elseif ($value['key'] == 'logo') {
                    $eventMap['logoId'] = $value['id'];
                }elseif ($value['key'] == 'attach') {
                    $eventMap['attachId'] = $value['id'];
                }
            }
        }
        //活动签到权限人数
        $codelimit = M('event_add')->getField('codelimit', 'uid='.$eventMap['uid']);
        if(!$codelimit){
            $codelimit = 5;
        }
        $eventMap['codelimit'] = $codelimit;

        $addId = $this->add($eventMap);
        //激活新的
        if($addId){
            //特殊活动新建投票表
            if($eventMap['es_type']>0){
                $table_name = 'ts_event_spcil_vote_'.$addId ;
                $sql = "CREATE TABLE `$table_name` (
                    `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                    `eventId` INT(11) UNSIGNED NOT NULL,
                    `mid` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '投票用户',
                    `pid` INT(11) UNSIGNED NOT NULL DEFAULT '0' COMMENT '选手',
                    `cTime` INT(11) UNSIGNED NULL DEFAULT NULL,
                    `status` TINYINT(1) UNSIGNED NOT NULL DEFAULT '1',
                    PRIMARY KEY (`id`),
                    INDEX `mid_eventId` (`mid`, `eventId`),
                    INDEX `mid_pid` (`mid`, `pid`)
                )
                COLLATE='utf8_general_ci' ENGINE=MyISAM  AUTO_INCREMENT=3488400;";
                M('')->query($sql) ;
            }
            $reliveid = array();
            if($eventMap['coverId']){
                $reliveid[] = $eventMap['coverId'];
            }
            if($eventMap['logoId']){
                $reliveid[] = $eventMap['logoId'];
            }
            if($eventMap['attachId']){
                $reliveid[] = $eventMap['attachId'];
            }
            if(!empty($reliveid)){
                model('Attach')->reliveAttach($reliveid);
            }
        }

        return $addId;
    }
    private function isSchoolOrga($orgaId,$sid){
        if($orgaId<0){
            $test = 0-$orgaId;
            $schoolOrga = D('SchoolOrga','event')->getAll($sid);
            $ids = getSubByKey($schoolOrga, 'id');
            return in_array($test, $ids);
        }
        if($orgaId>0){
            $addSchool = model('Schools')->makeLevel0Tree($sid);
            $ids = getSubByKey($addSchool, 'id');
            return in_array($orgaId, $ids);
        }
        return false;
    }
    private function isSchoolGid($gid,$sid){
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $map['school'] = $sid;
        $res = M('group')->where($map)->field('id')->findAll();
        $ids = getSubByKey($res, 'id');
        return in_array($gid, $ids);
    }
    //安徽财大添加活动
    public function addAufeEvent(){
        $sid = 2348;
        foreach($_POST as $k=>&$v){
            $v = strToUtf8($v);
        }
        $res['state'] = 1;
        $res['msg'] = '接口开发中';
        $res['id'] = 0;
        $required_field = array(
            'num' => '发起人学号',
            'auditNum' => '审核人学号',
            'key' => '加密',
            'title' => '活动名称',
            'description' => '活动简介',
            'address' => '活动地点',
            'typeId' => '活动分类',
            'sid' => '归属组织',
            'gid' => '发起部落',
            'sTime' => '活动开始时间',
            'eTime' => '活动结束时间',
            'startline' => '报名开始时间',
            'deadline' => '截止报名时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k])){
                $res['msg'] = $v . '不可为空';
                return $res;
            }
        }
        $num = t($_POST['num']);
        $sec = '8Pu&!lj$Qx@G';
        $key = t($_POST['key']);
        if(md5($num.$sec)!=$key){
            $res['msg'] = '加密错误';
            return $res;
        }
        $domain = M('school')->getField('email', 'id='.$sid);
        $mapUser['email'] = $num.$domain;
        $uid = M('user')->getField('uid',$mapUser);
        if(!$uid){
            $res['msg'] = '发起人学号不存在';
            return $res;
        }
        $auditNum = t($_POST['auditNum']);
        $mapUser['email'] = $auditNum.$domain;
        $audit_uid = M('user')->getField('uid',$mapUser);
        if(!$audit_uid){
            $res['msg'] = '审核人学号不存在';
            return $res;
        }
        if (mb_strlen($_POST['title'], 'UTF8') > 30) {
            $res['msg'] = '活动名称最大30个字';
            return $res;
        }
        if (mb_strlen($_POST['description'], 'UTF8') <= 0 || mb_strlen($_POST['description'], 'UTF8') > 250) {
            $res['msg'] = '活动简介1到250字';
            return $res;
        }
        $data['uid'] = $uid;
        $data['audit_uid'] = $audit_uid;
        $data['audit_uid2'] = $audit_uid;
        $data['title'] = t($_POST['title']);
        $data['contact'] = isset($_POST['contact'])?t($_POST['contact']):'';
        $typeId = intval($_POST['typeId']);
        $isSchoolTypeId = D('EventType','event')->isSchoolTypeId($typeId,$sid);
        if(!$isSchoolTypeId){
            $res['msg'] = '活动分类不存在';
            return $res;
        }
        $data['typeId'] = $typeId;
        $data['description'] = t($_POST['description']);
        $data['startline'] = intval($this->_paramDate($_POST['startline']));
        $data['deadline'] = $this->_paramDate($_POST['deadline']);
        $data['sTime'] = $this->_paramDate($_POST['sTime']);
        $data['eTime'] = $this->_paramDate($_POST['eTime']);
        if ($data['sTime'] > $data['eTime']) {
            $res['msg'] = '活动结束时间不得早于开始时间';
            return $res;
        }
        if ($data['deadline'] < $data['startline']) {
            $res['msg'] = '报名截止时间不得早于报名开始时间';
            return $res;
        }
        if ($data['deadline'] > $data['eTime']) {
            $res['msg'] = '报名截止时间不能晚于活动结束时间';
            return $res;
        }
        $data['address'] = t($_POST['address']);
        $limitCount = intval($_POST['limitCount']);
        $data['limitCount'] = 6000000;
        if($limitCount){
            $data['limitCount'] = intval($_POST['limitCount']);
        }
        //默认图片
        if (empty($_FILES['cover']['name'])) {
            $data['coverId'] = 444624;
        }else{
            $images = tsUploadImg($uid,'aufe_event',true);
            if (!$images['status']) {
                $res['msg'] = $images['info'];
                return $res;
            }
            $data['coverId'] = $images['info'][0]['id'];
        }
        $data['logoId'] = 0;
        $data['isTicket'] = (isset($_POST['isTicket']) && t($_POST['isTicket'])) ? 1:0;
        $data['allow'] = (isset($_POST['allow']) && t($_POST['allow'])) ? 1:0;
        $data['need_tel'] = (isset($_POST['need_tel']) && t($_POST['need_tel'])) ? 1:0;
        $data['free_attend'] = (isset($_POST['free_attend']) && t($_POST['free_attend'])) ? 1:0;
        $data['status'] = 1;
        $orgaId = t($_POST['sid']);
        $isSchoolOrga = $this->isSchoolOrga($orgaId,$sid);
        if(!$isSchoolOrga){
            $res['msg'] = '归属组织不存在';
            return $res;
        }
        $data['sid'] = $orgaId;
        $data['default_banner'] = 1;
        $data['show_in_xyh'] = 1;
        $data['is_school_event'] = $sid;
        $data['school_audit'] = 2; //终审通过
        $gid = t($_POST['gid']);
        $isSchoolGid = $this->isSchoolGid($gid,$sid);
        if(!$isSchoolGid){
            $res['msg'] = '发起部落不存在';
            return $res;
        }
        $data['gid'] = $gid;
        $addId = $this->doAddEvent($data);
        if ($addId) {
            X('Credit')->setUserCredit($uid, 'add_event');
            //显示于学校
            M('EventSchool2')->add(array('eventId'=>$addId,'sid'=>$sid));
            $res['state'] = 0;
            $res['msg'] = '添加成功';
            $res['id'] = $addId;
        } else {
            $res['msg'] = '添加失败';
        }
        return $res;
    }
    private function _paramDate($date) {
       $date_list = explode(' ', $date);
        list( $year, $month, $day ) = explode('-', $date_list[0]);
        $hour = '00';
        $minute = '00';
        $second = '00';
        if(isset($date_list[1])){
            list( $hour, $minute, $second ) = explode(':', $date_list[1]);
        }
        return mktime($hour, $minute, $second, $month, $day, $year);
    }
        /**
     * doAddEvent
     * 客户端添加活动
     * @param mixed $map
     * @param mixed $feed
     * @access public
     * @return void
     */
    public function apiDoAddEvent($eventMap,$cover) {
        $eventMap['cTime'] = isset($eventMap['cTime']) ? $eventMap['cTime'] : time();
        if ($cover['status']) {
            foreach ($cover['info'] as $value) {
                if ($value['key'] == 'cover') {
                    $eventMap['coverId'] = $value['id'];
                }
            }
        }
        //$eventMap['limitCount'] = 0 == intval($eventMap['limitCount']) ? 999999999 : $eventMap['limitCount'];
        $addId = $this->add($eventMap);
        return $addId;
    }

//根据存储路径，获取图片真实URL
    function get_photo_url($savepath) {
        return './data/uploads/' . $savepath;
    }

    public function doEditEvent($eventMap, $cover, $obj) {
        $eventMap['rTime'] = isset($eventMap['rTime']) ? $eventMap['rTime'] : time();
        if (isset($cover['status']) && $cover['status']) {
            foreach ($cover['info'] as $value) {
                if ($value['key'] == 'cover') {
                    $eventMap['coverId'] = $value['id'];
                } elseif ($value['key'] == 'logo') {
                    $eventMap['logoId'] = $value['id'];
                } elseif ($value['key'] == 'attach') {
                    $eventMap['attachId'] = $value['id'];
                }
            }
        }
        $addId = $this->where('id =' . $obj['id'])->save($eventMap);
        //删除旧的,激活新的
        if($addId){
            $reliveid = array();
            if($eventMap['coverId']){
                $reliveid[] = $eventMap['coverId'];
                model('Attach')->deleteAttach($obj['coverId'], true);
            }
            if(isset($eventMap['logoId'])){
                model('Attach')->deleteAttach($obj['logoId'], true);
                if($eventMap['logoId']){
                    $reliveid[] = $eventMap['logoId'];
                }
            }
            if($eventMap['attachId']){
                $reliveid[] = $eventMap['attachId'];
                model('Attach')->deleteAttach($obj['attachId'], true);
            }
            if(!empty($reliveid)){
                model('Attach')->reliveAttach($reliveid);
            }
        }
        $this->_updateEventCache($obj['id']);
        $this->_upCacheAttendEvent($obj['adminCode'],$eventMap);
        return $addId;
    }

    /**
     * factoryModel
     * 工厂方法
     * @param mixed $name
     * @static
     * @access private
     * @return void
     */
    public static function factoryModel($name) {
        return D("Event" . ucfirst($name), 'event');
    }

    /**
     * doAddUser
     * 添加用户行为
     * @param mixed $data
     * @access public
     * @return void
     */
    public function doAddUser($data,$noJoinAttend=false) {
        $result = array('status' => 0);

        //诚信系统禁止中
        $stoped = M('event_cx')->field('eday')->where('uid='.$data['uid'].' and status=2')->find();
        if($stoped){
            $result['info'] = '您被诚信系统惩罚中，'.$stoped['eday'].' 前不可参加活动';
            return $result;
        }

        //检查用户是否是该活动允许的学校
        $canJoin = D('EventSchool2','event')->isSchoolEvent($data['usid'],$data['id']);
        if(!$canJoin){
            $result['info'] = '该活动不向您所在学校开放报名';
            return $result;
        }
        //检查这个id是否存在
        if (false == $event = $this->where('id =' . $data['id'])->field('need_tel,allow,limitCount,startline,deadline,title,status')->find()) {
            $result['info'] = '活动不存在';
            return $result;
        }
        if(!$noJoinAttend){
            if($event['startline']>time()){
                $start = date('Y-m-d H:i', $event['startline']);
                $result['info'] = '活动尚未开始报名，报名开始时间：'.$start;
                return $result;
            }
            if($event['deadline']<time()){
                $result['info'] = '活动报名时间已过';
                return $result;
            }
            if($event['need_tel'] && empty($data['tel'])){
                $result['info'] = '联系电话不能为空';
                return $result;
            }
            if($event['status'] == 0 )
            {
                $result['info'] = '该活动未通过审核，暂不能报名参加';
                return $result;
            }
        }

        //检查是否已经加入
        $userDao = self::factoryModel('user');
        $hasUser = $userDao->hasUser($data['uid'], $data['id']);
        if ($hasUser) {
            $result['info'] = '您已报名该活动，不可重复报名';
            $result['status'] = 1;
            return $result;
        }
        $map = $data;
        unset($map['id']);
        $map['eventId'] = $data['id'];
        $map['cTime'] = time();
        $map['status'] = $event['allow'] ? 0 : 1;
        if($noJoinAttend){
            $map['status'] = 2;
        }
        //不需要审核时，限定报名人数
        if ($event['allow']){
            return $this->_joinUser($map,$event['title']);
        }
        if ($event['limitCount'] < 1) {
            $result['info'] = '人数已满！报名失败';
            return $result;
        }

        $limit = M('event')->getField('limitCount', 'id =' . $data['id']);
        if ($limit < 1){
            $result['info'] = '人数已满！报名失败';
            return $result;
        }else{
            return $this->_joinUser($map,$event['title']);
        }
        /*
        $try = 10;
        while ($try>0){
            $besetz = useQueue('Evnet_joinQueue_'.$data['id']);
            if($besetz){
                $limit = M('event')->getField('limitCount', 'id =' . $data['id']);
                if ($limit < 1) {
                    $result['info'] = '人数已满！报名失败';
                    return $result;
                }
                $res = $this->_joinUser($map);
                freeQueue('Evnet_joinQueue_'.$data['id']);
                return $res;
            }
            $try -= 1;
            sleep(1);
        }
        */
        $result['info'] = '报名人太多，请稍后再试';
        return $result;
    }

    private function _joinUser($map,$title=''){
        $res = D('EventUser','event')->add($map);
        if ($res) {
            $result['info'] = '报名成功，请等待审核';
            if ($map['status']) {
                $result['info'] = '报名成功';
                M()->startTrans();
                $setInt = $this->setInc('joinCount', 'id=' . $map['eventId']);
                $setDec = $this->setDec('limitCount', 'id=' . $map['eventId'].' AND limitCount>0');
                if ($setInt && $setDec){
                    M()->commit();
                    X('Credit')->setUserCredit($map['uid'], 'join_event');
                    $result['status'] = 1;
                }else{
                    M()->rollback();
                    $result['status'] = 0;
                }
            }
            //加入到队列:加入到活动群组
            $rongyun_data['do_action']  = json_encode(array('Rongyun','joinEventGroup'));
            $rongyun_data['param']      = json_encode(array('userId'=>$map['uid'],'groupId'=>$map['eventId'],'groupName'=>$title));
            $rongyun_data['create_time']= time();
            $rongyun_data['next_time']  = time();
            model('Scheduler')->addToRongyun($rongyun_data);
            $result['status'] = 1;
        } else {
            $result['status'] = 0;
            $result['info'] = '报名失败';
        }
        return $result;
    }
    /**
     * doArgeeUser
     * 同意申请
     * @param mixed $data
     * @access public
     * @return void
     */
    public function doArgeeUser($data) {
        $userDao = self::factoryModel('user');
        $data['status'] = 0;
        if ($userDao->where($data)->setField('status', 1)) {
            $this->setInc('joinCount', 'id=' . $data['eventId']);
            $this->setDec('limitCount', 'id=' . $data['eventId']);
            $user = $userDao->where('id='.$data['id'])->field('uid')->find();
            X('Credit')->setUserCredit($user['uid'], 'join_event');
            //加入到队列:加入到活动群组
            $rongyun_data['do_action']  = json_encode(array('Rongyun','joinEventGroup'));
            $rongyun_data['param']      = json_encode(array('userId'=>$user['uid'],'groupId'=>$data['eventId']));
            $rongyun_data['create_time']= time();
            $rongyun_data['next_time']  = time();
            model('Scheduler')->addToRongyun($rongyun_data);

            return true;
        }
        return false;
    }

    /**
     * doDelUser
     * 删除用户
     * @param mixed $data
     * @access public
     * @return void
     */
    public function doDelUser($data,$adminDo=false) {
        $res['status'] = 0;
        $userDao = self::factoryModel('user');
        $user = $userDao->where($data)->field('id, uid, status,eventId')->find();
        if(!$user){
            $res['msg'] = '用户不存在';
            return $res;
        }
        $event = $this->where('id='.$user['eventId'])->field('deadline,school_audit')->find();
        if($event['school_audit']==5){
            $res['msg'] = '活动已完结，不可操作';
            return $res;
        }
        if(!$adminDo){
            if($user['status']==1){
                if(time()>$event['deadline']){
                    $res['msg'] = '报名已截止，取消参加请联系发起人操作';
                    return $res;
                }
            }
            if($user['status']==2){
                $res['msg'] = '您已签到，不可取消参加';
                return $res;
            }
        }elseif($user['status']==2){
            $userDao->setField('status',1,'id='.$user['id']);
            $res['status'] = 1;
            $res['msg'] = '状态已改为未签到';
            return $res;
        }
        if ($userDao->where('id = '.$user['id'])->delete()) {
            //加入到队列:退出活动群组
            $rongyun_data['do_action']  = json_encode(array('Rongyun','quitEventGroup'));
            $rongyun_data['param']      = json_encode(array('userId'=>$user['uid'],'groupId'=>$data['eventId']));
            $rongyun_data['create_time']= time();
            $rongyun_data['next_time']  = time();
            model('Scheduler')->addToRongyun($rongyun_data);
            //记录数相应减1
            $deleteMap['id'] = $user['eventId'];
            if ($user['status']) {
                $this->setInc('limitCount', $deleteMap);
                $this->setDec("joinCount", $deleteMap);
                X('Credit')->setUserCredit($user['uid'], 'cancel_join_event');
            }
            $res['status'] = 1;
            $res['msg'] = '操作成功';
            return $res;
        }
        $res['msg'] = '操作失败';
        return $res;
    }

    public function doEditData($time, $id) {
        //检查安全性，防止非管理员访问
        $uid = $this->where('id=' . $id)->getField('uid');
        if ($uid != $this->mid) {
            return -1;
        }
        if ($this->where('id=' . $id)->setField(array('startline','deadline'), array(0,$time))) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * doDeleteEvent
     * 删除活动
     * @param mixed $eventId
     * @access public
     * @return void
     */
    public function doDeleteEvent($map) {
        if (empty($map)) {
            return false;
        }
        $this->where($map)->setField('isDel', 1);
        $events = $this->field('id,uid,status,coverId,logoId,attachId')->where($map)->findAll();
        foreach ($events as &$v) {
            //通过审核的
            if($v['status']){
                //积分
                X('Credit')->setUserCredit($v['uid'], 'delete_event');
                //删除新闻
                $news['eventId'] = $map['id'];
                D('EventNews')->doDelete($news);
            //未通过审核的
            }else{
                /*
                if($v['coverId']){
                    model('Attach')->deleteAttach($v['coverId'], true);
                }
                if($v['logoId']){
                    model('Attach')->deleteAttach($v['logoId'], true);
                }
                if($v['attachId']){
                    model('Attach')->deleteAttach($v['attachId'], true);
                }
                */
                //$this->where('id='.$v['id'])->delete();
                //显示于学校
                D('EventSchool2','event')->removeByEid($v['id']);
            }
        }
        return true;
        /*
          if ($this->where($eventId)->delete()) {
          //删除选项
          self::factoryModel('opts')->where($opts_map)->delete();
          //删除成员
          $user_map['eventId'] = $eventId['id'];
          self::factoryModel('user')->where($user_map)->delete();
          return true;
          }
          return false;
         */

    }

    /**
     * doIsHot
     * 设置推荐
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function doIsHot($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "recommend":   //推荐
                $result = $this->where($map)->setField('isHot', 1);
                break;
            case "cancel":   //取消推荐
                $result = $this->where($map)->setField('isHot', 0);
                break;
        }
        return $result;
    }

    /**
     * 设置投票
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function doIsTicket($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "open":
                $result = $this->where($map)->setField('isTicket', 1);
                break;
            case "close":
                $result = $this->where($map)->setField('isTicket', 0);
                break;
        }
        return $result;
    }
    public function doRepeatedVote($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "open":
                $result = $this->where($map)->setField('repeated_vote', 1);
                break;
            case "close":
                $result = $this->where($map)->setField('repeated_vote', 0);
                break;
        }
        return $result;
    }
    public function doPlayerUpload($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "open":
                $result = $this->where($map)->setField('player_upload', 1);
                break;
            case "close":
                $result = $this->where($map)->setField('player_upload', 0);
                break;
        }
        return $result;
    }

    /**
     * 设置置顶
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function doIsTop($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "top":   //置顶
                $result = $this->where($map)->setField('isTop', 1);
                break;
            case "cancel":   //取消置顶
                $result = $this->where($map)->setField('isTop', 0);
                break;
        }
        return $result;
    }

    public function doPuRecomm($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "top":   //置顶
                $result = $this->where($map)->setField('puRecomm', 1);
                break;
            case "cancel":   //取消置顶
                $result = $this->where($map)->setField('puRecomm', 0);
                break;
        }
        return $result;
    }

    /**
     * doIsActiv
     * 重新激活
     * @param Intger $id
     * @access public
     * @return void
     */
    public function doIsActiv($id) {
        $result = false;
        if ($id > 0) {
            $map['id'] = $id;
            $data['deadline'] = strtotime('+1 day');
            $result = $this->where($map)->save($data);
        }
        return $result;
    }

    /**
     * getHotList
     * 推荐列表
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function getHotList($map=array()) {
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['isHot'] = 1;
        $result = $this->where($map)->order('isTop DESC, id DESC')->limit(5)->findAll();
        return $result;
    }

    /**
     * hasMember
     * 判断是否是有这个成员
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function hasMember($uid, $eventId) {
        $user = self::factoryModel('user');
        if ($result = $user->where('uid=' . $uid . ' AND eventId=' . $eventId)->field('action,status')->find()) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 大后台审核通过
     * @param array $ids
     * @return boolean
     */
    public function doAudit($ids) {
        $map['id'] = array('IN', $ids);
        $res = $this->where($map)->setField('status', 1); // 通过审核
        if ($res) {
            // 发送通知
            $events = $this->where($map)->findAll();
            $notify_dao = service('Notify');
            foreach ($events as $v) {
                $notify_data = array('title' => $v ['title'], 'eventId' => $v ['id']);
                $notify_dao->sendIn($v ['uid'], 'event_audit', $notify_data);
            }
        }
        return $res;
    }
    //jun  完结驳回
     public function doFinishBack($ids,$reason) {
        $map['id'] = array('IN', $ids);
        $res = $this->where($map)->setField('school_audit', 4);
        if ($res) {
            // 发送通知
            $events = $this->where($map)->field('id,title,uid')->findAll();
            $notify_dao = service('Notify');
            foreach ($events as $v) {
                $notify_data['eventId'] = $v['id'];
                $notify_data['title'] = $v['title'];
                $notify_data['reason'] = $reason;
                $notify_dao->sendIn($v ['uid'], 'event_finishback', $notify_data);
            }
        }
        return $res;
    }
    /**
     * 校方审核通过
     * @param
     * @return boolean
     */
    public function doSchoolAudit($id,$uid,$sid,$credit,$score,$codelimit,$isAdmin,$max_credit,$max_score) {
        $map['id'] = $id;
        $user = D('User', 'home')->getUserByIdentifier($uid);
        if($user['sid'] != $sid && !$isAdmin){
            $this->error = '您没有权限';
            return false;
        }
        $data['credit'] = $credit*100/100;
        if($data['credit']>$max_credit){
            $this->error = '学分最大'.$max_credit;
            return false;
        }
        $data['score'] = intval($score);
        if($data['score']>$max_score){
            $this->error = '积分最大'.$max_score;
            return false;
        }
        $data['codelimit'] = intval($codelimit);
        $data['school_audit'] = 1;
        $isAudit2 = $user['can_event2'] || $isAdmin;
        if($isAudit2){
            $data['school_audit'] = 2;
            $data['audit_uid2'] = $uid;
            $onlineTime = D('EventOnline','event')->getOnlineTime($id);
            $data['status'] = 1;
            if($onlineTime){
                $data['status'] = 0;
            }
        }
        $res = $this->where($map)->save($data);
        if($res){
            //终审通过
            if($isAudit2){
                $event = $this->where("id=".$id)->field('uid,title,gid')->find();
                // 给发起人发送通知
                $notify_data = array('title' => $event['title'], 'eventId' => $id);
                service('Notify')->sendIn($event['uid'], 'event_audit', $notify_data);
                //部落活跃度
                if($event['gid']>0){
                    model('TjGday')->addGday($event['gid'],'Gday_event');
                }
                //将发起人默认参加 和签到
                $autor = M('user')->where('uid='.$event['uid'])->field('realname,mobile,sex,sid')->find();
                $euData['eventId'] = $map['id'];
                $euData['uid'] = $event['uid'];
                $euData['realname'] = $autor['realname'];
                $euData['sex'] =  $autor['sex'];
                $euData['tel'] =  $autor['mobile'];
                $euData['usid'] =  $autor['sid'];
                $euData['cTime'] =  time();
                $euData['status']=2;
                M('event_user')->add($euData);
                $joinUserData['joinCount'] = array('exp','joinCount+1');
                $joinUserData['limitCount'] = array('exp','limitCount-1');
                $this->where('id=' . $id)->save($joinUserData);
                X('Credit')->setUserCredit($event['uid'], 'join_event');
                $this->_updateEventCache($id);
                //加入到队列
                $rongyun_group['groupName'] = $event['title'];
                $rongyun_group['userId']    = $event['uid'];
                $rongyun_group['groupId']   = $id;
                $rongyun_data['do_action']  = json_encode(array('Rongyun','createEventGroup'));
                $rongyun_data['param']      = json_encode($rongyun_group);
                $rongyun_data['create_time']= time();
                $rongyun_data['next_time']  = time();   //立即执行
                model('Scheduler')->addToRongyun($rongyun_data);
            }else{
                //发短信给终审人
//                $endUser= M('user')->where("can_event2=1 AND sid=".$sid)->field('uid,mobile')->findAll();
//                $arr=array();
//                $daoUserPrivacy = M('user_privacy');
//                foreach($endUser as $v){
//                    if($v['mobile']){
//                        $isSend = $daoUserPrivacy->where("`key`='active' AND uid=".$v['uid'])->field('value')->find();
//                        if($isSend['value']!=1){
//                            $arr[] = $v['mobile'];
//                        }
//                    }
//                }
//                $mobile=implode(',', $arr);
//                if($mobile){
//                    $msg = '您有新的活动"' . $event['title'] . '"等待您的审核';
//                    service('Sms')->sendsms($mobile, $msg);
//                }
            }
        }
        return $res;
    }

    /**
     * 完结活动
     * @param array $id
     * @return boolean
     */
    public function doFinish($id,$code,$giveScore=true) {
        $map['id'] = array('IN', $id);
        $code = intval($code);
        $data['school_audit'] = $code;
        $data['fTime'] = time();
        $data['isTicket'] = 0;
        $res = $this->where($map)->save($data);
        if(!$res){
            return false;
        }
        if($code!=5){
            return $res;
        }
        //begin 安财大部分活动改为未完结
$eids = array(128418,128172,127484,124187,124181,123174,122626,122557,120334,120310,119619,119228,118704,118342,117388,117027,114394,114391,114389,114334,114333,114332,114329,114326,114323,114321,114318,114315,114314,114311,114306,114191,114115,113849,113687,112606,112384,112182,110857,110792,108867,108861,108855);
        if(in_array($id, $eids)){
            M('cron_credit')->add(array('eid'=>$id));
            return $res;
        }
        // end 安财大部分活动改为未完结
        
        //诚信度
        M('event_cron')->add(array('event_id'=>$id));
        //部落评分
        $event = $this->where($map)->field('title,gid,uid')->find();
        if($event['gid']>0){
            model('TjGday')->addGday($event['gid'],'Gday_finish',$id);
        }
        //完结不发放积分 私信通知
        if($code==5 && !$giveScore){
            $map = array();
            $map['eventId'] = $id;
            $map['status'] = 2;
            $eventUser = D('EventUser')->where($map)->field('uid')->findAll();
            $uids = getSubByKey( $eventUser, 'uid' );
            $notify_dao = service('Notify');
            $notify_data['title'] = $event['title'];
            $notify_dao->sendIn($uids, 'event_finish_error', $notify_data);
            return true;
        }
        //发放学分
        M('cron_credit')->add(array('eid'=>$id));

        //加入到队列:解散组
        /*
        $rongyun_group['userId']    = $event['uid'];
        $rongyun_group['groupId']   = $id;
        $rongyun_data['do_action']  = json_encode(array('Rongyun','dismissEventGroup'));
        $rongyun_data['param']      = json_encode($rongyun_group);
        $rongyun_data['create_time']= time();
        $rongyun_data['next_time']  = strtotime('+1 week');
        M('Scheduler')->add($rongyun_data);
        */
        return $res;
    }
    //补发积分
    public function bufa($where) {
        $result['status'] = 0;
        $event = $this->where($where)->field('id,is_school_event,score,credit')->find();
        if(!$event){
            $result['info'] = '活动不存在或您没有权限！';
            return $result;
        }
        M('cron_credit')->add(array('eid'=>$where['id']));
        $result['status'] = 1;
        $result['info'] = '操作成功，学分积分将在第二天生效';

        return $result;
    }

    //活动统计日结
    public function upEday($uid,$sid,$credit){
        $today = date('Y-m-d');
        $map['uid'] = $uid;
        $map['day'] = $today;
        $res = M('tj_eday')->field('id,credit')->where($map)->find();
        $data['uid'] = $uid;
        $data['sid'] = $sid;
        $data['credit'] = $credit;
        $data['day'] = $today;
        if($res){
            $data['credit'] += $res['credit'];
            return M('tj_eday')->where('id='.$res['id'])->save($data);
        }else{
            return M('tj_eday')->add($data);
        }
    }
    /**
     *  驳回活动
     * @param array $ids
     * @return boolean
     */
    public function doDismissed($ids,$reason,$del) {
        $map['id'] = array('IN', $ids);
        if($del){
            $this->doDeleteEvent($map);
        }else{
            $this->where($map)->setField('school_audit', 6);
        }
        // 发送通知
        $events = $this->where($map)->field('id,title,uid')->findAll();
        $notify_dao = service('Notify');
        foreach ($events as $v) {
            $url = $v['title'];
            if(!$del){
                $link = U('event/Author/index', array('id' => $v['id']));
                $url = '<a href="'.$link.'">'.$v['title'].'</a>';
            }
            $notify_data['title'] = $url;
            $notify_data['reason'] = $reason;
            $notify_dao->sendIn($v ['uid'], 'event_delaudit', $notify_data);
        }
        return true;
    }

    /**
     * 评分
     */
    public function doAddNote($eventId, $uid, $note) {
        $noteArr = array(1, 2, 3, 4, 5);
        if (!in_array($note, $noteArr)) {
            $this->error = $note.'分数错误';
            return false;
        }
        $daoNote = D('EventNote','event');
        //是否已评分
        if ($daoNote->hasNoted($eventId, $uid)) {
            $this->error = '您已评分，不可重复评分';
            return false;
        }
        $map['id'] = $eventId;
        $event = $this->where($map)->field('id,gid')->find();
        //活动是否存在
        if (!$event) {
            $this->error = '活动不存在';
            return false;
        }
        if (!$daoNote->addNote($eventId, $uid, $note)) {
            $this->error = '评分失败，请稍后再试';
            return false;
        }
        $avg = $daoNote->getAvg($eventId);
        $data['note'] = $avg;
        $data['noteUser'] = $daoNote->getNoteCount($eventId);;
        if ($this->where($map)->save($data)) {
            $data['note'] = sprintf('%0.1f', $avg);
            return $data;
        }
        $this->error = '评分失败，请稍后再试';
        return false;
    }


    //用户首页活动列表
    public function getHoneEvent($sid) {
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['puRecomm'] = array('gt', 0);
        $map['is_school_event'] = $sid;
        $num = $this->where($map)->count();
        if ($num > 5) {
            return $this->where($map)->field('id,title,sTime,eTime,coverId,address,limitCount,joinCount')->limit(6)->order('id DESC')->findAll();
        }
        //pu推荐的活动id
        $puRecomm = $this->where($map)->field('id,title,sTime,eTime,coverId,address,limitCount,joinCount')->order('id DESC')->findAll();
        if(!$puRecomm){
            $puRecomm = array();
        }
        //找到置顶的且不是pu推荐的活动id
        $map['puRecomm'] = 0;
        $map['isTop'] = 1;
        $num2 = $this->where($map)->count();
//      var_dump($puRecommIds)
        if (($num + $num2) > 5) {
            $hotId = $this->where($map)->field('id,title,sTime,eTime,coverId,address,limitCount,joinCount')->limit(6-$num)->order('id DESC')->findAll();
            return array_merge($puRecomm,$hotId);
        }
        //找到不是pu推荐的所有活动
        unset($map['isTop']);
        $other =  $this->where($map)->field('id,title,sTime,eTime,coverId,address,limitCount,joinCount')->limit(6-$num)->order('id DESC')->findAll();
        return array_merge($puRecomm, $other);
    }

    public function addHit($uid,$event_id){
        $this->where('id='.$event_id)->setInc('hit') ;
        $time = strtotime(date('Y-m-d')) ;
        $map['uid'] = $uid ;
        $map['event_id'] = $event_id ;
        $map['time'] = array('GT',$time) ;
        if ($id = M('event_hit')->where($map)->getField('id')) {
            $data['time'] = time() ;
            M('event_hit')->where('id='.$id)->save() ;
        }else{
            $data['time'] = time() ;
            $data['uid'] = $uid ;
            $data['event_id'] = $event_id ;
            M('event_hit')->add($data) ;
        }
    }
}

?>