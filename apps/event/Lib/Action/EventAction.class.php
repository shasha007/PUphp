<?php

/**
 * EventAction
 * 活动管理
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */

class EventAction extends TeacherAction {

    /**
     * event
     * EventModel的实例化对象
     * @var mixed
     * @access private
     */
    private $event;
    private $eventAdmin;
    private $remarkNeedSids;

    /**
     * config
     * EventConfig的实例化对象
     * @var mixed
     * @access private
     */
    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        $this->event = D('Event');
        if($this->rights['can_event'] != 1 && $this->rights['can_event2'] != 1){
            $this->assign('jumpUrl', U('event/Readme/index'));
            $this->error('您没有权限管理活动！');
        }
        $aufeAudit = FALSE;
        //待审核数
        if($this->rights['can_admin'] || $this->rights['can_event']){
            $map['is_school_event'] = $this->sid;
            $map['isDel'] = 0;
            $map['status'] = 0;
            $map['school_audit'] = 0;
            if(!$this->rights['can_admin']){
                $map['audit_uid'] = $this->mid;
            }
            $auditCount1 = $this->event->where($map)->count();
            $this->assign('auditCount1', $auditCount1);
            $this->assign('showAudit1', 1);
            $aufeAudit = true;
            // 输出用户省id
            $sid = getUserField($this->mid,'sid') ;
            $proveId = M('school')->where('id='.$sid)->getField('provinceId') ;
            $this->assign('province',$proveId) ;
        }
        $map = array();
        $this->eventAdmin = false;
        //超管和终审 终审个数
        if($this->rights['can_admin'] || $this->rights['can_event2']){
            $this->eventAdmin = true;
            $map['is_school_event'] = $this->sid;
            $map['isDel'] = 0;
            $map['status'] = 0;
            $map['school_audit'] = 1;
            if(!$this->rights['can_admin'] && $this->user['event_level']!=10){
                $this->eventAdmin = false;
                $map['sid'] = $this->user['sid1'];
            }
            $auditCount2 = $this->event->where($map)->count();
            $this->assign('auditCount2', $auditCount2);
            $this->assign('showAudit2', 1);
            $aufeAudit = true;
        }
        $this->assign('eventAdmin', $this->eventAdmin);
        //end待审核数
        $map = array();
        $map['is_school_event'] = $this->sid;
        if($this->sid == 473){
            $map['is_school_event'] = array('in',array(0,$this->sid));
        }
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['school_audit'] = 3;
        if(!$this->rights['can_admin']){
            $map['audit_uid2'] = $this->mid;
        }
        $finishCount = $this->event->where($map)->count();
        $this->assign('finishCount', $finishCount);
        $aufeAuditUrl = '';
        $editStuPracticeTeam = '';
        if($this->sid==2348){
            $num = getUserEmailNum($this->user['email']);
            $key = md5($num.'8Pu&!lj$Qx@G');
            $aufeAuditUrl = "http://youth.ancai.cc/Login_PU.asp?PuAction=EditAct&Action=Check&num=$num&key=$key";
            $editStuPracticeTeam = "http://youth.ancai.cc/Login_PU.asp?PuAction=EditStuPracticeTeam&Action=Check&num=$num&key=$key";
        }
        $this->assign('editStuPracticeTeam', $editStuPracticeTeam);
        $this->assign('aufeAuditUrl', $aufeAuditUrl);
        //初审需要备注的学校ID
        $this->remarkNeedSids = array(12823,473);
    }

    /**
     * basic
     * 基础设置管理
     * @access public
     * @return void
     */
    public function index() {
        $this->display();
    }

    /**
     * doChangeBase
     * 修改全局设置
     * @access public
     * @return void
     */
    public function doChangeBase() {
        //变量过滤 todo:更细致的过滤
        foreach ($_POST as $k => $v) {
            $config[$k] = t($v);
        }
        //$config['limitsuffix'] = preg_replace("/bmp\|||\|bmp/",'',$config['photo_file_ext']);//过滤bmp
        if (model('Xdata')->lput('event', $config)) {
            $this->assign('jumpUrl', U('event/Event/index'));
            $this->success('设置成功！');
        } else {
            $this->error('设置失败！');
        }
    }

    /**
     * eventlist
     * 获得所有人的eventlist
     * @access public
     * @return void
     */
    public function eventlist() {
        if($this->rights['can_admin']){

        }elseif(!$this->rights['can_event2']){
            $map['audit_uid'] = $this->mid;
        }elseif($this->user['event_level']!=10){
            $map['sid'] = $this->user['sid1'];
        }
        $map['es_type'] = 0 ;
        $this->_getEventList($map);
        $this->assign('canPuRecomm', $this->_canPuRecomm());
        $taglist = D('EventTag')->where("isdel = '0' and  sid=".$this->sid)->findAll();
        $this->assign('taglist',$taglist);
        $this->display();
    }
    private function _canPuRecomm(){
        if($this->rights['pu_admin'] || $this->mid == 96513){
            return true;
        }
        return false;
    }

    /**
     * 活动详情
     */
    public function event(){
        $this->_getEvent();
        $this->display();
    }

    //初审
    public function audit1() {
        $map['status'] = 0;
        $map['isDel'] = 0;
        $map['school_audit'] = 0;
        if(in_array($this->sid,$this->remarkNeedSids))
        {
            $this->assign('remarkFlag',1);
        }
        //超管显示所有
        if(!$this->rights['can_admin']){
            $map['audit_uid'] = $this->mid;
        }
        $this->_getEventList($map);
        $this->display();
    }
    //终审
    public function audit2() {
        $map['isDel'] = 0;
        $map['school_audit'] = 1;
        //超管显示所有
        if($this->rights['can_admin']){

        }elseif(!$this->rights['can_event2']==1){
            $this->error('您没有终审权力');
        }elseif($this->user['event_level']!=10){
            $map['sid'] = $this->user['sid1'];
        }
        $this->_getEventList($map);
        $this->display();
    }
    public function finish() {
        $map['status'] = 1;
        $map['school_audit'] = 3;
        if(!$this->rights['can_admin']){
            $map['audit_uid2'] = $this->mid;
        }
        $this->_getEventList($map,'fTime DESC');
        $this->display();
    }

    //活动校方审核通过
    public function doAudit() {
        $webconfig = $this->get('webconfig');
        $res = $this->event->doSchoolAudit(intval($_REQUEST ['gid']),$this->mid,$this->sid,
                t($_REQUEST ['credit']),t($_REQUEST ['score']),intval($_REQUEST ['codelimit']),$this->rights['can_admin'],
                $webconfig['max_credit'],$webconfig['max_score']); // 通过审核
        if ($res) {
            $this->success('审核成功');
        } else {
            $this->error($this->event->getError());
        }
    }
    //活动校方审核弹窗
    public function doAuditScore() {
        $id = intval($_REQUEST['id']);
        $event = M('event')->field('id,title,credit,score,sTime,eTime,codelimit')->where('id='.$id)->find();
        $this->assign($event);
        $this->assign('creditEditbar',$this->_creditEditbar());
        $this->display();
    }
    public function doFinish() {
        $res = D('Event')->doFinish($_POST ['gid'],$_POST ['code'],$_POST ['give']); // 通过审核
        if ($res) {
            if (strpos($_POST ['gid'], ',')) {
                echo 1;
            } else {
                echo 2;
            }
        } else {
            echo 0;
        }
    }

   public function doAuditReason() {
        $id=intval($_GET['id']);
        $this->assign('id',$id);
        $del= $_GET['del']?1:0;
        $this->assign('del',$del);
        $this->display();
    }
    //jun  驳回活动
    public function doDismissed() {
        $reason = t($_POST['reject']);
        $id = intval($_POST ['id']);
        if (empty($reason)) {
            $this->error('请填写驳回原因！');
        }
        $res = D('Event')->doDismissed($id, $reason,intval($_POST ['del']));

        if ($res) {
            $this->success('驳回成功！');
        } else {
            $this->error('驳回失败！');
        }
    }

   public function doFinishAudit() {

        $id=intval($_GET['id']);
        $this->assign('id',$id);
        $this->display();
    }


    //jun  完结驳回活动
   public function doFinishBack() {
       $reason=t($_POST['reject']);
       $id=intval($_POST ['id']);
      if (empty($reason)){
           $this->ajaxReturn(0,"请填写驳回原因",1);
            }
        $res = D('Event')->doFinishBack($id,$reason);
        if($res){
             $this->ajaxReturn(0,"驳回成功！",2);
        }else{
             $this->ajaxReturn(0,"驳回失败！",0);
        }
    }

    private function _getEventList($map=array(),$orig_order='id DESC'){
        $map['is_school_event'] = $this->sid;
        if($this->sid == 473){
            $map['is_school_event'] = array('in',array(0,$this->sid));
        }
        //get搜索参数转post
        if (!empty($_GET['typeId'])) {
            $_POST['typeId'] = $_GET['typeId'];
        }
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['es_searchEvent'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_searchEvent']);
        } else {
            unset($_SESSION['es_searchEvent']);
        }
        $this->assign('isSearch', isset($_POST['isSearch']) ? '1' : '0');

        $map['isDel'] = 0;
        if(intval($_POST[sid1])){
            $res=M('user')->where('sid1 ='.$_POST['sid1'])->field('uid')->findAll();
            $uids= getSubByKey($res,'uid');
            $map['uid'] =array('in',$uids);
        }
        $_POST['typeId'] && $map['typeId'] = intval($_POST['typeId']);
        $_POST['title'] && $map['title'] = array('like', '%' . t($_POST['title']) . '%');
        if (isset($_POST['status'])&&$_POST['status']!=='') {
            $map['school_audit'] = intval($_POST['status']);
        }
        if (isset($_POST['pu']) && $_POST['pu'] != '')
            $map['is_prov_event'] = intval($_POST['pu']);
        if (isset($_POST['puRecomm']) && $_POST['puRecomm'] != '')
            $map['puRecomm'] = array('gt',0);
        if (isset($_POST['isTop']) && $_POST['isTop'] != '')
            $map['isTop'] = intval($_POST['isTop']);
        if (isset($_POST['isHot']) && $_POST['isHot'] != '')
            $map['isHot'] = intval($_POST['isHot']);

        //取出标签
        $tid = intval($_POST['tagId']);
        $this->assign('tid',$tid);
        if($tid){
            $eids = D('EventTagcheck')->field('eid')->where("tid=$tid")->findAll();
            $map['id'] = array('in',  getSubByKey($eids, 'eid'));
        }
        //处理时间
//            $_POST['sTime'] && $_POST['eTime'] && $map['cTime'] = $this->event->DateToTimeStemp(t( $_POST['sTime'] ),t( $_POST['eTime'] ) );
        $_POST['sTime'] && $_POST['eTime'] && $map['cTime'] = $this->event->DateToTimeStemp(t(date("Ymd", strtotime($_POST['sTime']))), t(date("Ymd", strtotime($_POST['eTime']))));
        //处理排序过程
        $order = isset($_POST['sorder']) ? t($_POST['sorder']) . " " . t($_POST['eorder']) : $orig_order;
        $_POST['limit'] && $limit = intval(t($_POST['limit']));
        $order && $list = $this->event->where($map)->field('id,is_prov_event,title,typeId,uid,joinCount,credit,score,cTime,
            school_audit,audit_uid,audit_uid2,status,isTop,remark,isHot,puRecomm,is_school_event,gid,attachId,fTime,endattach,es_type')->order($order)->findPage($limit);
        /* echo $this->event->getLastSql();
        die; */
        
        //处理活动列表
        foreach($list as $key => &$val){
            if($key == 'data'){
                foreach($val as &$v){
                    $tagCheck = D('EventTagcheck');
                    $map_c['eid'] = $v['id'];
                    $tid = $tagCheck->where($map_c)->field('tid')->findAll();
                    $tids = '';
                    foreach($tid as $t){
                        $tids .= $t['tid'].',';
                    }
                    $tids = rtrim($tids,','); 
                    $map_t['id'] = array('in',$tids);
                    $etag = D('EventTag');
                    $tlist = $etag->where($map_t)->field('title')->findAll();
                    $tag = '';
                    if(!empty($tlist)){
                        foreach($tlist as $v1){
                            $tag .= $v1['title'].',';
                        } 
                    }
                    $v['tag'] = rtrim($tag,',');
                    $v['onlineTime'] = D('EventOnline')->getOnlineTime($v['id']);
                }
            }
        }
        $this->assign($_POST);
        $this->assign($list);
        $this->assign('editSid',$this->sid);
        $this->assign('type_list', D('EventType')->getType($this->sid));
        $this->assign('searchType', D('EventType')->getSearchType($this->sid));
    }

    /**
     * doDeleteEvent
     * 删除活动
     * @access public
     * @return int
     */
    public function doDeleteEvent() {
        $eventid['id'] = array('in', explode(',', $_REQUEST['id']));    //要删除的id.
        $result = $this->event->doDeleteEvent($eventid);
        if (false != $result) {
            if (!strpos($_REQUEST['id'], ",")) {
                echo 2;            //说明只是删除一个
            } else {
                echo 1;            //删除多个
            }
        } else {
            echo -1;               //删除失败
        }
    }

    //推荐操作
    public function doChangeIsHot() {
        if ($this->rights['can_event2'] != 1 && $this->rights['can_admin'] != 1) {
            $this->error('对不起,你无权操作');
        }
        $act = $_REQUEST['type'];  //推荐动作
        //只可幻灯5个
        if($act=='recommend'){
            $top = $this->event->where('isHot=1 and isDel=0 and is_school_event='.$this->sid)->count();
            if($top>=5){
                $this->error('最多幻灯5个活动，请【搜索】后【取消】其它幻灯再试');
            }
        }
        $event['id'] = array('in', $_REQUEST['id']);        //要推荐的id.
        $result = $this->event->doIsHot($event, $act);
         if (false != $result) {
            $this->success('操作成功');        //成功
        } else {
            $this->error('设置失败');       //失败
        }
    }
    //置顶操作
    public function doChangeIsTop() {
        if ($this->rights['can_event2'] != 1 && $this->rights['can_admin'] != 1) {
            $this->error('对不起,你无权操作');
        }
        $act = $_REQUEST['type'];  //动作
        //只可置顶5个
        if($act=='top'){
            $top = $this->event->where('isTop=1 and isDel=0 and is_school_event='.$this->sid)->count();
            if($top>=5){
                $this->error('最多推荐5个活动，请【搜索】后【取消】其它推荐再试');
            }
        }
        $event['id'] = intval($_REQUEST['id']);        //要推荐的id.
        $result = $this->event->doIsTop($event, $act);
        if (false != $result) {
            $this->success('操作成功');        //成功
        } else {
            $this->error('设置失败');       //失败
        }
    }
    //pu推荐
    public function doChangePuRecomm() {
        if (!$this->_canPuRecomm()) {
            $this->error('对不起,你无权操作');
        }
        $act = $_REQUEST['type'];  //动作
        $event['id'] = intval($_REQUEST['id']);        //要推荐的id.
        $result = $this->event->doPuRecomm($event, $act);
        if (false != $result) {
            $this->success('操作成功');        //成功
        } else {
            $this->error('设置失败');       //失败
        }
    }

    //重新激活操作
    public function doChangeActiv() {
        $id = (int) $_REQUEST['id'];        //要激活的id.
        $result = $this->event->doIsActiv($id);

        if (false != $result) {
            echo 1;            //激活成功
            // 发送通知
            $obj = $this->event->find($id);
            $notify_dao = service('Notify');
            $notify_data = array('title' => $obj['title'], 'event_id' => $obj['id'], 'event_uid' => $obj['uid']);
            $notify_dao->sendIn($obj['uid'], 'event_reactiv', $notify_data);
        } else {
            echo -1;               //激活失败
        }
    }
    //学分是否可编辑
    private function _creditEditbar(){
        if($this->rights['can_admin']){
            return 1;
        }
        $levelStatus = D('EventAutoLevel')->statusNow($this->sid);
        if($levelStatus){
            return 0;;
        }
        return 1;
    }
    /**
     * 编辑活动
     */
    public function editEvent(){
        $this->_getEvent();
        $this->_eventEditbar($this->get('school_audit'),$this->get('audit_uid'),$this->get('sid'));
        $this->assign('schoolOrga', D('SchoolOrga')->getAll($this->sid));
        $school = model('Schools')->makeLevel0Tree($this->sid);
        $this->assign('addSchool', $school);
        $typeDao = D('EventType');
        $type = $typeDao->getType2($this->sid);
        $this->assign('type', $type);
        //取出标签
        $taglist = D('EventTag')->where("isdel = '0' and  sid=".$this->sid)->findAll();
        $this->assign('taglist',$taglist);
        $id = intval($_REQUEST['id']);
        $tags = D('EventTagcheck')->getTagsByEid($id);
        $this->assign('tags',$tags);
        $this->assign('creditEditbar',$this->_creditEditbar());
        $this->assign('level',D('EventLevel')->allLevel($this->sid));
        $this->assign('autoCredit',D('EventAutoLevel')->statusNow($this->sid));
        $this->display();
    }
    public function autoCredit() {
        $typeId  = intval($_POST['typeId']);
        $levelId  = intval($_POST['levelId']);
        $levelStatus = D('EventAutoLevel')->statusNow($this->sid);
        if(!$levelStatus){
            $this->error('自动学分未激活');
        }
        $credit = D('EventCredit')->creditByLevelType($levelId,$typeId);
        $this->success($credit);
    }
    private function _getEvent(){
        //活动
        $id = intval($_REQUEST['id']);
        //检测id是否为0
        if ($id <= 0) {
            $this->assign('jumpUrl', U('/Event/eventlist'));
            $this->error("错误的访问页面，请检查链接");
        }
        $map['id'] = $id;
        $map['is_prov_event'] = 0;
        if ($result = $this->event->where($map)->find()) {
            $this->assign($result);
        } else {
            $this->assign('jumpUrl', U('/Index/index'));
            $this->error('活动不存在');
        }
        // 活动分类
        $cate = D('EventType')->getType($this->sid);
        $this->assign('category', $cate);
    }
    private function _eventEditbar($schoolAudit,$auditUid,$eventSid){
        if($schoolAudit==5){
            $this->error('活动已完结，无法编辑');
        }
        if($this->rights['can_admin']){

        }elseif(!$this->rights['can_event2']){
            if($auditUid!=$this->mid){
                $this->error('您没有权限');
            }
        }elseif($this->user['event_level']!=10){
            if($eventSid != $this->user['sid1']){
                $this->error('您没有权限');
            }
        }
    }
    public function doEditEvent() {
        $id = intval($_POST['id']);
        if (!$obj = $this->event->where(array('id'=>$id,'is_prov_event'=>0))->find()) {
            $this->error('活动不存在或已删除');
        }
        $this->_eventEditbar($obj['school_audit'],$obj['audit_uid'],$obj['sid']);
        
        if (mb_strlen($_POST['title'], 'UTF8') > 30) {
            $this->error('活动名称最大30个字！');
        }
        $title = t($_POST['title']);
        if (mb_strlen($_POST['description'], 'UTF8') <= 0 || mb_strlen($_POST['description'], 'UTF8') > 250) {
            $this->error("活动简介1到250字!");
        }
        $webconfig = $this->get('webconfig');
        $credit = t($_POST['credit'])*100/100;
        if($credit>$webconfig['max_credit']){
            $this->error('学分最大'.$webconfig['max_credit']);
        }
        $score = intval($_POST['score']);
        if($score>$webconfig['max_score']){
            $this->error('积分最大'.$webconfig['max_score']);
        }
        $textarea = t($_POST['description']);

        $map['startline'] = intval(_paramDate($_POST['startline']));
        $map['deadline'] = _paramDate($_POST['deadline']);
        $map['sTime'] = _paramDate($_POST['sTime']);
        $map['eTime'] = _paramDate($_POST['eTime']);
        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($map['deadline'] < $map['startline']) {
            $this->error("报名截止时间不得早于报名开始时间");
        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于结束时间');
        }

        //得到上传的图片
        if(isset($_POST['imgid1'][0]) && $_POST['imgid1'][0]>0){
            $map['coverId'] = intval($_POST['imgid1'][0]);
        }
        $defaultBanner = intval($_POST['banner']);
        if (isset($_POST['imgid2'][0]) && $_POST['imgid2'][0]>0){
            $map['logoId'] = intval($_POST['imgid2'][0]);
        }elseif($defaultBanner){
            $map['logoId'] = 0;
            $map['default_banner'] = intval($_POST['banner']);
        }
        if(isset($_POST['file1'][0]) && $_POST['file1'][0]>0){
            $map['attachId'] = intval($_POST['file1'][0]);
        }
        $map['score'] = $score;
        $map['credit'] = $credit;
        $map['codelimit'] = intval($_POST['codelimit']);
        $map['title'] = $title;
        $map['address'] = t($_POST['address']);
        if(t($_POST['limitCount']) == '无限制'){
            $map['limitCount'] = 6000000;
        }else{
            $map['limitCount'] = intval($_POST['limitCount']);
        }
        $map['typeId'] = intval($_POST['typeId']);
        $map['contact'] = t($_POST['contact']);
        $map['cost'] = intval($_POST['cost']);
        $map['costExplain'] = keyWordFilter(t($_POST['costExplain']));
        $map['allow'] = isset($_POST['allow']) ? 1 : 0;
        $map['isTicket'] = isset($_POST['isTicket']) ? 1 : 0;
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        $map['free_attend'] = isset($_POST['free_attend']) ? 1 : 0;
        //$map['show_in_xyh'] = isset($_POST['show_in_xyh']) ? 1 : 0;
        if(intval($_POST['sid'])!=0){
            $map['sid'] = intval($_POST['sid']);
        }
        $map['description'] = $textarea;
        $map['levelId'] = $this->_addEventGetLevelId(intval($_POST['levelId']), $this->sid);
        if ($this->event->doEditEvent($map, array(), $obj)) {
            //活动标签
            D('EventTagcheck')->editEventTagcheck($id,$_POST['tags']);
            $this->assign('jumpUrl', U('/Event/editEvent', array('id' => $id, 'uid' => $this->mid)));
            $this->success($this->appName . '修改成功！');
        } else {
            $this->error($this->appName . '修改失败');
        }
    }
    private function _addEventGetLevelId($levelId,$sid){
        $level = D('EventLevel','event')->allLevel($sid);
        if(!$level){
            return 0;
        }
        if(!$levelId){
            return $level[0]['id'];
        }
        $levelIds = getSubByKey($level, 'id');
        if(!in_array($levelId, $levelIds)){
            return $level[0]['id'];
        }
        return $levelId;
    }
    public function newsList() {
        if(!$this->eventAdmin){
            $this->error('您没有权限');
        }
        if (!empty($_POST)) {
            $_SESSION['admin_event_news_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_event_news_search']);
        } else {
            unset($_SESSION['admin_event_news_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['a.title'] = array('like', "%" . $_POST['title'] . "%");
        $_POST['eventUid'] = t($_POST['eventUid']);
        $_POST['eventUid'] && $map['uid'] = $_POST['eventUid'];
        $_POST['eventId'] = t($_POST['eventId']);
        $_POST['eventId'] && $map['eventId'] = $_POST['eventId'];
        $this->assign($_POST);
        $map['is_school_event'] = $this->sid;
        if ($this->rights['can_event2'] != 1 && $this->rights['can_admin'] != 1) {
            $map['b.audit_uid'] = $this->mid;
        }
        $db_prefix = C('DB_PREFIX');
        $list = D('EventNews')->table("{$db_prefix}event_news AS a ")
                ->join("{$db_prefix}event AS b ON a.eventId=b.id")
                ->field('a.* , b.uid')
                ->where($map)->order('a.id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    /**
     * 删除新闻
     */
    public function deleteNews() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('EventNews')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function flashList() {
        if(!$this->eventAdmin){
            $this->error('您没有权限');
        }
        if (!empty($_POST)) {
            $_SESSION['admin_event_flash_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_event_flash_search']);
        } else {
            unset($_SESSION['admin_event_flash_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['a.title'] = array('like', "%" . $_POST['title'] . "%");
        $_POST['eventUid'] = t($_POST['eventUid']);
        $_POST['eventUid'] && $map['b.uid'] = $_POST['eventUid'];
        $_POST['eventId'] = t($_POST['eventId']);
        $_POST['eventId'] && $map['eventId'] = $_POST['eventId'];
        $this->assign($_POST);
        $map['is_school_event'] = $this->sid;
        if ($this->rights['can_event2'] != 1 && $this->rights['can_admin'] != 1) {
            $map['b.audit_uid'] = $this->mid;
        }
        $db_prefix = C('DB_PREFIX');
        $list = D('EventFlash')->table("{$db_prefix}event_flash AS a ")
                ->join("{$db_prefix}event AS b ON a.eventId=b.id")
                ->field('a.id,a.eventId,a.path,a.title, a.cTime, b.uid')
                ->where($map)->order('a.id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }
    /**
     * 删除视频
     */
    public function deleteFlash() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('EventFlash')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function imgList() {
        if(!$this->eventAdmin){
            $this->error('您没有权限');
        }
        if (!empty($_POST)) {
            $_SESSION['admin_event_img_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_event_img_search']);
        } else {
            unset($_SESSION['admin_event_img_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['a.title'] = array('like', "%" . $_POST['title'] . "%");
        $_POST['eventUid'] = t($_POST['eventUid']);
        $_POST['eventUid'] && $map['b.uid'] = $_POST['eventUid'];
        $_POST['eventId'] = t($_POST['eventId']);
        $_POST['eventId'] && $map['eventId'] = $_POST['eventId'];
        $this->assign($_POST);
        $map['is_school_event'] = $this->sid;
        if ($this->rights['can_event2'] != 1 && $this->rights['can_admin'] != 1) {
            $map['b.audit_uid'] = $this->mid;
        }
        $db_prefix = C('DB_PREFIX');
        $list = D('EventImg')->table("{$db_prefix}event_img AS a ")
                ->join("{$db_prefix}event AS b ON a.eventId=b.id")
                ->field('a.id,a.eventId,a.path,b.title, a.cTime,a.upUid, b.uid')
                ->where($map)->order('a.id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }
    /**
     * 删除视频
     */
    public function deleteImg() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('EventImg')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function finishAudit(){
        $id = intval($_GET['id']);
        $event = M('event')->where('id='.$id)->field('id,title,print_img,print_text,joinCount,pay,endattach')->find();
        $this->assign($event);
        if($event){
            $map['eventId'] = $id;
            $map['status'] = 2;
            $attCount = M('event_user')->where($map)->count();
            $this->assign('attCount', $attCount);
        }
        $this->display();
    }

    //给全体报名
    public function allJoin(){
        if($_SESSION['ThinkSNSAdmin'] != '1'){
            $this->error('您没有权限');
        }
        $eventId = intval($_REQUEST['id']);
        $map['id'] = $eventId;
        $map['isDel'] = 0;
        $map['is_school_event'] = $this->sid;
        $event = $this->event->where($map)->field('status')->find();
        if(!$event){
            $this->error('活动不存在或已删除');
        }
        if($event['status']==0){
            $this->error('活动尚未通过审核');
        }
        if($event['status']==2){
            $this->error('活动已完结');
        }
        set_time_limit(0);
        $data['eventId'] = $eventId;
        $data['cTime'] = time();
        $data['status'] = 1;
        $map = array();
        $map['sid'] = $this->sid;
        $users = M('user')->where($map)->field('uid,realname,sex,mobile')->findAll();
        $userDao = D('EventUser');
        $succ = 0;
        foreach($users as $user){
            $data['uid'] = $user['uid'];
            $data['realname'] = $user['realname'];
            $data['sex'] = $user['sex'];
            $data['usid'] = $this->sid;
            if ($userDao->add($data)) {
                $succ += 1;
            }
        }
        if($succ>0){
            $this->event->setInc('joinCount','id='.$eventId,$succ);
        }
        $this->success('成功报名'.$succ.'个成员');

    }

    //完结的活动补发积分学分
    public function bufa(){
        $map['id'] = intval($_POST['id']);
        $map['school_audit'] = 5;
        if($this->rights['can_admin']){

        }elseif(!$this->rights['can_event2']){
            $map['audit_uid'] = $this->mid;
        }elseif($this->user['event_level']!=10){
            $map['sid'] = $this->user['sid1'];
        }
        $res = $this->event->bufa($map);
        echo json_encode($res);
        exit;
    }
    

    //活动初审增加备注
    public function addRemark()
    {
        $id = $_POST['id'];
        $remark = $_POST['remark'];
        $map['id'] = intval($id);
        $data['remark'] = t($remark);
        $flag = M('event')->where($map)->save($data);
        $return['status'] = 0;
        $return['info'] = '操作成功';
        if(!$flag)
        {
            $return['status'] = 1;
            $return['info'] = '操作失败';
        }
        echo json_encode($return);die;
    }

    // 特殊活动管理
    public function SpecialEvenLlist(){
        if($this->rights['can_admin']){

        }elseif(!$this->rights['can_event2']){
            $map['audit_uid'] = $this->mid;
        }elseif($this->user['event_level']!=10){
            $map['sid'] = $this->user['sid1'];
        }
        $map['es_type'] = array('GT',0) ;
        $this->_getEventList($map);
        $this->assign('canPuRecomm', $this->_canPuRecomm());
        $series = M('event_series')->where('isDel=0')->select() ;
        $taglist = D('EventTag')->where("isdel = '0' and  sid=".$this->sid)->findAll();
        foreach ($series as $key => $value) {
            $es_ty[$value['id']] = $value['title'] ;
        }
        $this->assign('series',$es_ty) ;
        $this->assign('taglist',$taglist);
        $this->display() ;
    }
}
