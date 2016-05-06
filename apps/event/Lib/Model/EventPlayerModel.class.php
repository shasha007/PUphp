<?php

/**
 * EventPlayerModel
 * 活动用户项
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 ludongyun
 * @author ludongyun <rechner00@hotmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class EventPlayerModel extends BaseModel {

    private $_error;
    private $_restCount;

    public function getError(){
        return $this->_error;
    }
    public function getRestCount(){
        return $this->_restCount;
    }

    //接口获取用户详情
    public function playerDetail($mid,$map){
        $app = $this->where($map)->field('id,path,realname,ticket,school,content,stoped,eventId,commentCount,paramValue')->find();
        $eventId = $app['eventId'] ;
        $isjingying = M('event')->where("id=$eventId and es_type>0")->count() ;
        if ($isjingying) {
            $mode_name = 'event_spcil_vote_'.$eventId;
            $vote_p = M("$mode_name") ;
        }else{
            $vote_p = M("event_vote") ;
        }
        if (!$app) {
            $this->error('选手不存在或已被删除！');
        }
        if($app['paramValue']){
            $app['paramValue'] = unserialize($app['paramValue']);
        }else{
            $app['paramValue'] = array();
        }
        $result = M('event')->where('id='.$map['eventId'])->field('repeatTicket')->find();
        $map_p = array();
        $map_p['eventId'] = $map['eventId'];
        $map_p['mid'] = $mid;
        if($result['repeatTicket'] == '1'){
            $start = strtotime(date('Y-m-d'));
            $end = strtotime(date('Y-m-d',strtotime('+1day')));
            $map_p['cTime'] = array(array('gt',$start),array('lt',$end));
            $pids = $vote_p->where($map_p)->field('pid')->findAll();
        }else{
            $pids = $vote_p->where($map_p)->field('pid')->findAll();
        }
        $pids = getSubByKey($pids,'pid');
        $app['canVote'] = $this->_canVote($mid,$app,$pids);
        $app['path'] = tsGetEventUserThumb($app['path'], 163,204,'c');
        $flash = D('EventFlash')->where(array('uid' => $map['id']))->order('id ASC')->findAll();
        if(empty($flash)){
            $app['flash'] = array();
        }else{
            foreach($flash as &$v){
                $v['url'] = $this->get_flash_url($v['host'], $v['flashvar'],$v['link']);
            }
            $app['flash'] = $flash;
        }
        $img = D('EventImg')->where(array('uid' => $map['id']))->order('id ASC')->findAll();
        foreach($img as &$v){
            $v['original_path'] = tsMakeThumbUp($v['path'], 150, 150, 'no');
            $v['path'] = tsMakeThumbUp($v['path'], 150, 150, 'f');
        }
        if(empty($img)){
            $app['img'] = array();
        }else{
            $app['img'] = $img;
        }
        return $app;
    }

    public function getHandyList($mid, $map, $limit = 10, $page = 1, $order = 'ticket DESC, id DESC') {
        $map['status'] = 1;
        $offset = ($page - 1) * $limit;
        if($map['eventId']==12239){
            $order = "RAND()";
        }
//        if($map['eventId']==87788){
//            $order = 'id DESC';
//        }
        // 判断排序规则
        $sorted = M('Event')->where('id='.$map['eventId'])->getField('player_sorted');
        // 如果为随机随机排序
        if ($sorted){
            $key = 'PlayerList_'.md5(json_encode($map));
            $cacheData = Mmc($key);
            if (!$cacheData){
                $cacheData = $this->field('id,path,realname,ticket,school,stoped,eventId,commentCount')->where($map)->order(' rand() ')->select();
                // 缓存
                Mmc($key,$cacheData,0,3600);
            }
            // 数组分页
            $list = array_slice($cacheData,$offset,$limit);
        }else{
            $list = $this->field('id,path,realname,ticket,school,stoped,eventId,commentCount')->where($map)->order($order)->limit("$offset,$limit")->select();
        }


        //判断活动是否支持每日投票功能
        $result = M('event')->where('id='.$map['eventId'])->field('repeatTicket')->find();
        $map_p = array();
        $map_p['eventId'] = $map['eventId'];
        $eventId = $map_p['eventId'] ;
        $map_p['mid'] = $mid;
        $isjingying = M('event')->where("id=$eventId and es_type>0")->count() ;
        if ($isjingying) {
            $mode_name = 'event_spcil_vote_'.$eventId;
            $vote_p = M("$mode_name") ;
        }else{
            $vote_p = M("event_vote") ;
        }
        if($result['repeatTicket'] == '1'){
            $start = strtotime(date('Y-m-d'));
            $end = strtotime(date('Y-m-d',strtotime('+1day')));
            $map_p['cTime'] = array(array('gt',$start),array('lt',$end));
            $pids = $vote_p->where($map_p)->field('pid')->findAll();
        }else{
            $pids = $vote_p->where($map_p)->field('pid')->findAll();
        }
//         $pids = M('event_vote')->where('eventId='.$m          ap['eventId'].' and mid='.$mid)->field('pid')->findAll();
        $pids = getSubByKey($pids,'pid');
        foreach ($list as $key => $value) {
            $row = $value;
            $row['path'] = tsGetEventUserThumb($row['path'], 163,204,'c');
            //用户学校
            if(!$row['school']){
                $row['school'] = ' ';
            }
//            if($map['eventId']==87788){
//              $row['ticket'] = '隐藏';
//            }
            $row['sex'] = '1';
            //是否可己投票
            $row['canVote'] = $this->_canVote($mid,$value,$pids);
            unset($row['stoped']);
            $list[$key] = $row;
            $list[$key]['imgCount'] = M('EventImg')->where('uid='.$value['id'])->count();
            $list[$key]['flashCount'] = M('EventFlash')->where('uid='.$value['id'])->count();
            // 解决缓存选手后，不实时更新票数的问题
            $result = M('EventPlayer')->field('ticket,commentCount')->find($value['id']);
            $list[$key]['ticket'] = $result['ticket'];
            $list[$key]['commentCount'] = $result['commentCount'];
            $map_c['type'] = 'eventPlayer';
            $map_c['appid'] = $value['id'];
            $list[$key]['commCount'] = M('comment')->where($map_c)->count();
        }
        return $list;
    }

    private function _canVote($mid,$player,$pids){
        if($player['stoped']){
            return false;
        }
        $event = M('event')->where('id='.$player['eventId'])->field('isTicket,maxVote,repeated_vote,sTime,eTime,is_school_event,school_audit')->find();
        if(!$event){
            return false;
        }
        if(!$event['isTicket']){
            return false;
        }
        if($event['sTime'] > time()){
            return false;
        }
        if($event['eTime'] <= time() ||
                ($event['is_school_event'] && $event['school_audit'] != 2)){
            return false;
        }
        $votedCount = count($pids);
        if($event['maxVote']<=$votedCount){
            return false;
        }
        if(!$event['repeated_vote']){
            $res = array_unique($pids);
            if(in_array($player['id'], $res)){
                return false;
            }
        }
        return true;
    }

    public function allowYear($eventId,$mid){
        $voteAllow = M('EventVoteyear')->where("eid=$eventId")->field('allowYear,allowSid1')->find();
        if(!$voteAllow){
            return true;
        }
        if($voteAllow['allowYear']){
            $arr = explode(',', $voteAllow['allowYear']);
            $year = getUserField($mid, 'year');
            if($year && !in_array('20'.$year, $arr)){
                $this->_error = '该活动不允许您所在年级投票';
                return false;
            }
        }
        if($voteAllow['allowSid1']){
            $arr = explode(',', $voteAllow['allowSid1']);
            $sid1 = getUserField($mid, 'sid1');
            if($sid1 && !in_array($sid1, $arr)){
                $this->_error = '该活动不允许您所在院系投票';
                return false;
            }
        }
        return true;
    }

    public function vote($mid,$pid,$event=false,$sid=0,$eventId=0){
        $this->_restCount = 0;

        $isjingying = M('event')->where("id=$eventId and es_type>0")->count() ;
        if ($isjingying) {
            $mode_name = 'event_spcil_vote_'.$eventId ;
            $vote_p = M("$mode_name") ;
            $player = $this->where('status=1 and id = '.$pid)->field('recommPid as eventId ,stoped')->find();
        }else{
            $vote_p = M('event_vote') ;
            $player = $this->where('status=1 and id = '.$pid)->field('eventId,stoped')->find();
        }
        if(!$player || $player['stoped']){
            $this->_error = '投票已关闭';
            return false;
        }
        $eventId = $player['eventId'];
        if(!$event){
            $event = M('event')->where('id='.$eventId)->field('isTicket,maxVote,repeatTicket,repeated_vote,allTicket,sTime,eTime')->find();
        }
        if(!$event || !$event['isTicket']){
            $this->_error = '投票已关闭';
            return false;
        }

        if($event['eTime'] <= time()){
            $this->_error = '活动已结束';
            return false;
        }
        if($event['sTime'] > time()){
            $this->_error = '活动尚未开始，开始时间：'.date('Y-m-d H:i',$event['sTime']);
            return false;
        }
        //检查用户是否是该活动允许的学校
        if(!$sid){
            $sid = getUserField($mid, 'sid');
        }

        // 如果需要先签到才能投票，则判断用户是否已经签到 status=2 为已经签到
        $condition['ts_event_user.eventId'] = $eventId;
        $condition['ts_event_user.uid']     = $mid;
        $eventUser = M('EventUser')->field('ts_event_user.status,ts_event.is_check_in')
            ->join('ts_event on ts_event.id=ts_event_user.eventId')
            ->where($condition)->find();

        if ($eventUser['status'] != 2 && $eventUser['is_check_in']){
            $this->_error = '该活动必须签到后才可以投票';
            return false;
        }
        unset($condition);
        unset($eventUser);

        $canJoin = D('EventSchool2','event')->isSchoolEvent($sid,$eventId);
        if(!$canJoin){
              $this->_error = '该活动不向您所在学校开放投票';
              return false;
        }
        //允许投票年级

        if(!$this->allowYear($eventId,$mid)){
            return false;
        }
        $maxVote = $event['maxVote'];
        //如果支持每日可投，计算当日已投数量
        if($event['repeatTicket']){
            $map = array();
            $map['eventId'] = $eventId;
            $map['mid'] = $mid;
            $start = strtotime(date('Y-m-d'));
            $end = strtotime(date('Y-m-d',strtotime('+1day')));
            $map['cTime'] = array(array('gt',$start),array('lt',$end));
            $votedCount = $vote_p->where($map)->count();
        }else{
            $votedCount = $vote_p->where('eventId='.$eventId.' and mid='.$mid)->count();
        }
//         $votedCount = M('event_vote')->where('eventId='.$eventId.' and mid='.$mid)->count();
        if($maxVote<=$votedCount){
            $this->_error = '本活动最多可投'.$maxVote.'票，您已投满！';
            return false;
        }

        //检查重复投票
        $bandVote = $this->_getBandVote($mid,$eventId,$event['repeated_vote']);
        if(in_array($pid, $bandVote)){
            $this->_error = '不可重复投票！';
            return false;
        }
        $data['mid'] = $mid;
        $data['cTime'] = time();
        $data['pid'] = $pid;
        $data['eventId'] = $eventId;
        //是否全部投完

        $data['status'] = $event['allTicket']?0:1;
        //检查是否特殊活动
        $res = $vote_p->add($data);

        if($res){
//             $has = $this->_hasTicket($mid, $eventId, $maxVote,$event['allTicket']);
            $has = $this->_hasTicket($mid, $eventId, $maxVote,$event);
            $resCount = $maxVote-$has;
            if($event['allTicket']){
                if($has>=$maxVote){
                    $this->_error = '投票成功！您已投满了'.$maxVote.'票!！';
                }else{
                    $this->_error = '投票成功！投满'.$maxVote.'票才生效，还差【'.$resCount.'】票!';
                    $this->_restCount = $resCount;
                }
            }else{
                if ($isjingying) {
                    M('event_special_tickets')->setInc('ticket','player_id=' . $pid);              //给投票数加一
                }else{
                    $this->setInc('ticket','id=' . $pid);              //给投票数加一
                }
                if($has>=$maxVote){
                    $this->_error = '投票成功！您已投满了'.$maxVote.'票!！';
                }else{
                    $this->_error = '投票成功！还有【'.$resCount.'】票可投!';
                    $this->_restCount = $resCount;
                }
            }
            return true;
        }
        $this->_error = '投票失败！';
        return false;
    }

    private function _hasTicket($mid,$eventId,$maxTicket,$event) {
        $isjingying = M('event')->where("id=$eventId and es_type>0")->count() ;
        if ($isjingying) {
            $mode_name = 'event_spcil_vote_'.$eventId ;
            $vote_p = M("$mode_name") ;
        }else{
            $vote_p = M('event_vote') ;
        }
        if($event['repeatTicket']){
            $map = array();
            $map['eventId'] = $eventId;
            $map['mid'] = $mid;
            $start = strtotime(date('Y-m-d'));
            $end = strtotime(date('Y-m-d',strtotime('+1day')));
            $map['cTime'] = array(array('gt',$start),array('lt',$end));
            $pidArr = $vote_p->where($map)->field('pid,status')->findAll();
        }else{
            $pidArr = $vote_p->where('eventId=' . $eventId . ' and mid=' . $mid)->field('pid,status')->findAll();
        }
//         $pidArr = M('event_vote')->where('eventId=' . $eventId . ' and mid=' . $mid)->field('pid,status')->findAll();
        $has = count($pidArr);
        if($has>=$maxTicket && $event['allTicket']){
            foreach ($pidArr as $vote) {
                if($vote['status']==0){
                    $this->setInc('ticket', 'id='.$vote['pid']);
                }
            }
        }
        return $has;
    }

    //不可重复投票pid
    private function _getBandVote($mid,$eventId,$repeated_vote){
        $res = array();
        if($mid && !$repeated_vote){
            $isjingying = M('event')->where("id=$eventId and es_type>0")->count() ;
            if ($isjingying) {
                $mode_name = 'event_spcil_vote_'.$eventId;
                $pids = M("$mode_name")->where('eventId='.$eventId.' and mid='.$mid)->field('pid')->findAll();
            }else{
                $pids = M('event_vote')->where('eventId='.$eventId.' and cTime>'.strtotime(date('Y-m-d')).' and mid='.$mid)->field('pid')->findAll();
            } 
            $res = getSubByKey($pids,'pid');
            $res = array_unique($res);
        }
        return $res;
    }

    public function doChangeVote($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "open":
                $result = $this->where($map)->setField('stoped', 0);
                break;
            case "close":
                $result = $this->where($map)->setField('stoped', 1);
                break;
        }
        return $result;
    }

    public function doDelPlayer($data,$delFile='true'){
        $res = false;
        $player = $this->where($data)->findAll();
        if($this->where($data)->delete()){
            foreach($player as $v){
                if(strpos($v['path'], '/')){
                    tsDelFile($v['path']);
                }else{
                    tsDelFile('event/'.$v['path']);
                }
                $map['uid'] = $v['id'];
                D('EventImg')->doDelete($map,$delFile);
                D('EventFlash')->doDelete($map,$delFile);
            }
            $res = true;
        }
        return $res;
    }

    public function doAllowPlayer($map){
        $data['status'] = 1;
        return $this->where($map)->save($data);
    }

    //选手资料返回修改
    public function doPlayerBack($pid){
        return $this->setField('status', 2, 'id='.$pid);
    }

    public function get_flash_url($host, $flashvar, $link='') {
    if(!$host){
        return $flashvar;
    }
    $flashAddr = array(
        //'youku.com' => 'http://player.youku.com/embed/FLASHVAR',
        'youku.com' => '<iframe height="100%" width="100%" src="http://player.youku.com/embed/FLASHVAR" frameborder=0 allowfullscreen></iframe>',
        'ku6.com' => 'http://v.ku6.com/show/FLASHVAR',
        //http://v.ku6.com/show/eEWfRt9Lj6BdoWpMTjz9FA...html
        //'sina.com.cn' => 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid=FLASHVAR',
        //'sina.com.cn' => 'http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=FLASHVAR/s.swf',
        'sina.com.cn'=>'<div><object id="ssss" width="100%" height="100%" ><param name="allowScriptAccess" value="always" /><embed pluginspage="http://www.macromedia.com/go/getflashplayer" src="http://video.sina.com.cn/share/video/FLASHVAR.swf" type="application/x-shockwave-flash" name="ssss" allowFullScreen="true" allowScriptAccess="always" width="480" height="370" /></object></div>',
        'tudou.com' => 'http://www.tudou.com/v/FLASHVAR',
        //'tudou.com' => 'http://www.tudou.com/v/FLASHVAR/&autoPlay=true/v.swf',
        //'youtube.com' => 'http://www.youtube.com/v/FLASHVAR',
        //'sohu.com' => 'http://v.blog.sohu.com/fo/v4/FLASHVAR',
        //'sohu.com' => 'http://share.vrs.sohu.com/FLASHVAR/v.swf',
        //'mofile.com' => 'http://tv.mofile.com/cn/xplayer.swf?v=FLASHVAR',
        'yixia.com' => 'http://paikeimg.video.sina.com.cn/splayer1.7.14.swf?scid=FLASHVAR&token=',
        't.cn' => 'http://paikeimg.video.sina.com.cn/splayer1.7.14.swf?scid=FLASHVAR&token=',
        'music' => 'FLASHVAR',
        'flash' => 'FLASHVAR'
    );
    $result = '';
    if (isset($flashAddr[$host])) {
        if('tudou.com' == $host) {
            if(strpos($link,'www.tudou.com/albumplay')!==false) {
                $flashAddr[$host] = 'http://www.tudou.com/a/FLASHVAR';
            }elseif(strpos($link,'www.tudou.com/listplay')!==false){
                $flashAddr[$host] = 'http://www.tudou.com/l/FLASHVAR';
            }
        }
        $result = str_replace('FLASHVAR', $flashvar, $flashAddr[$host]);
    }
    return $result;
}

}
