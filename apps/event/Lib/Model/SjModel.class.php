<?php

/**
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class SjModel extends Model {

    private $joinStart = '2015-09-18 00:00';
    private $joinEnd = '2015-10-08 23:59';
    private $voteStart = '2015-10-09 09:00';
    private $voteEnd = '2015-10-15 10:00';
    //彻底删除
    public function delSj($map){
        $list = $this->where($map)->field('id,attach')->findAll();
        if(!$list){
            return true;
        }
        $daoImg = M('sj_img');
        $daoSjFlash = M('sj_flash');
        $daoFlash = M('flash');
        $attIds = array();
        foreach ($list as $sj) {
            $sjids[] = $sj['id'];
            //附件
            if($sj['attach']){
                $attIds[] = $sj['attach'];
            }
            //图片
            $imgs = $daoImg->where('sjid='.$sj['id'])->field('attachId')->findAll();
            if($imgs){
                $imgAttIds = getSubByKey($imgs, 'attachId');
                $attIds = array_merge($attIds,$imgAttIds);
            }
        }
        if(!empty($attIds)){
            model('Attach')->deleteAttach($attIds, true);
        }
        //sj_img
        $sjidMap['sjid'] = array('in', $sjids);
        $daoImg->where($sjidMap)->delete();
        //sj_flash
        $flash = $daoSjFlash->where($sjidMap)->field('flashId')->findAll();
        if($flash){
            $flashIds = getSubByKey($flash, 'flashId');
            $flashMap['id'] = array('in', $flashIds);
            $daoFlash->where($flashMap)->delete();
            $daoSjFlash->where($sjidMap)->delete();
        }
        $this->where($map)->delete();
        return true;
    }
    public function canSjJoin($sid){
        $state = $this->joinTimeState();
        if($state==2){
            $this->error = '报名未开始，申请时间：'.$this->joinStart.' - '.$this->joinEnd;
            return false;
        }elseif($state==3){
            $this->error = '报名已结束，申请时间：'.$this->joinStart.' - '.$this->joinEnd;
            return false;
        }
        if(!isSjSchool($sid)){
            $this->error = '您所在的学校不在十佳评选范围内，不可申请报名，但可进行投票';
            return false;
        }
        return true;
    }
    //报名时间
    public function getJoinStart(){
        return $this->joinStart;
    }
    public function getJoinEnd(){
        return $this->joinEnd;
    }
    //投票时间
    public function frontVoteTime(){
        return date('m月d日 H:i', strtotime($this->voteStart)).' - '.date('m月d日 H:i', strtotime($this->voteEnd));
    }
    //报名是否开始 1进行中 2未开始 3已结束
    public function joinTimeState(){
        $now = time();
        $state = 1;
        if($now < strtotime($this->joinStart)){
            $state = 2;
        }elseif($now > strtotime($this->joinEnd)){
            $state = 3;
        }
        return $state;
    }
    //投票是否开始 1进行中 2未开始 3已结束
    public function voteTimeState(){
        $now = time();
        $state = 1;
        if($now < strtotime($this->voteStart)){
            $state = 2;
        }elseif($now > strtotime($this->voteEnd)){
            $state = 3;
        }
        return $state;
    }
    //剩余可投票数
    public function restTicket($uid, $eid, $pid) {
        if(!$pid || !$eid){
            $this->error = '参数错误';
            return 0;
        }
        $voteTimeState = $this->voteTimeState();
        if ($voteTimeState==2) {
            $this->error = '投票尚未开始';
            return 0;
        }elseif($voteTimeState==3){
            $this->error = '投票尚已结束';
            return 0;
        }
        $map['mid'] = $uid;
        $map['eventId'] = $eid;
        $dao = M('sj_vote_'.C('SJ_YEAR'));
        $count = $dao->where($map)->count();
        if ($count >= 10) {
            $this->error = '您已投满了10票! 每人每评选最多投10票';
            return 0;
        }
        $map['pid'] = $pid;
        $ticketed = $dao->where($map)->count();
        if ($ticketed) {
            $this->error = '您已给TA投过票了';
            return 0;
        }
        return 10 - $count;
    }
    //投票 -1失败 >=0成功 返回剩余票数
    public function sjVote($uid, $eid, $pid) {
        $restCount = $this->restTicket($uid, $eid, $pid);
        if (!$restCount) {
            return -1;
        }
        $map['mid'] = $uid;
        $map['eventId'] = $eid;
        $map['pid'] = $pid;
        $map['cTime'] = time();
        $dao = M('sj_vote_'.C('SJ_YEAR'));
        $vid = $dao->add($map);
        if ($vid) {
            $restCount -= 1;
            if ($restCount == 0) {
                $this->_incSjTicket($uid,$eid);
                $this->error = '您已投满了10票！票数每5分钟刷新';
            }else{
                $this->error = '投票成功！投满10票才生效，还差【'.$restCount.'】票!';
            }
            return $restCount;
        }
        $this->error = '投票失败，请稍后再试';
        return -1;
    }
    private function _incSjTicket($uid,$eid){
        $data['status'] = 1;
        M('sj_vote_'.C('SJ_YEAR'))->where('eventId='.$eid.' and mid='.$uid)->save($data);
//        $sjIdArr = M('sj_vote_'.C('SJ_YEAR'))->where('eventId='.$this->eventId.' and mid='.$this->mid)->field('pid')->findAll();
//        $sjIds = getSubByKey($sjIdArr, 'pid');
//        $dao = M('sj');
//        foreach ($sjIds as $sjid) {
//            $map['id'] = $sjid;
//            $dao->where($map)->setInc('ticket');
//        }
//        return true;
//        $map['id'] = array('in', $sjIds);
//        return M('sj')->where($map)->setInc('ticket');
    }
}

?>