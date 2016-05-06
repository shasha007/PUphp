<?php

/**
 * FrontAction
 * 活动页面
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class FrontAction extends Action {

    private $eventId;
    private $event;
    private $obj;
    private $esFlag;

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        $this->assign('isAdmin', '1');

        //投票开始结束时间
        $daoSj = D('Sj');
        $this->assign('topMsg', $daoSj->frontVoteTime());
        $this->assign('sjVote', $daoSj->voteTimeState());

        $this->event = D('Event');
        //活动
        $id = intval($_REQUEST['id']);
        //检测id是否为0
        if ($id<=0) {
            $this->assign('jumpUrl', getUrlDomain());
            $this->error("错误的访问页面，请检查链接");
        }
        //api登录
        if(isset($_POST['oauth_token']) && isset($_POST['oauth_token_secret'])){
            $verifycode['oauth_token'] = h($_POST['oauth_token']);
            $verifycode['oauth_token_secret'] = h($_POST['oauth_token_secret']);
            $verifycode['type'] = 'location';
            if($login = M('login')->where($verifycode)->field('uid')->find() ){
                $this->mid = $login['uid'];
                $_SESSION['mid'] = $this->mid;
            }
        }
        $this->event->setMid($this->mid);
        $map['id'] = $id;
        $map['isDel'] = 0;
        if ($result = $this->event->where($map)->find()) {
            if($result['status'] == 0){
                $this->error('该活动正在审核中');
            }
            $result['isEnd'] = false;
            $result['isStart'] = false;
            if($result['sTime'] <= time()){
                $result['isStart'] = true;
            }
            if($result['eTime'] <= time() ||
                    ($result['is_school_event'] && $result['school_audit'] != 2)){
                $result['isEnd'] = true;
            }
            $this->obj = $result;
            $this->assign('event', $result);
        } else {
            $this->error('活动不存在或，未通过审核或，已删除');
        }

        $this->assign('eventId', $id);
        $this->eventId = $id;
        //背景图片
        $height = 357;
        //$file = getLogo($result['logoId']);
        $file = tsGetLogo($result['logoId'],$result['typeId'],$result['default_banner']);
//        $arr = getimagesize($file);
//        if($arr[1]<357){
//            $height = $arr[1];
//        }
//        $bgcss = '';
        //if($result['logoId'] != 0){
            $bgcss = '.bg_pic{background: url("'.$file.'") no-repeat center top; float:left; width:100%; height:'.$height.'px;background-size: 100% 100%;}';
        //}
        $this->assign('bgcss', $bgcss);
        $this->assign("hasVideo", D('EventFlash')->where(array('eventId' => $this->eventId,'show'=>1))->count());
        $this->assign("hasPhoto", D('EventImg')->where(array('uid' => 0,'eventId' => $this->eventId))->count());
        $this->esFlag =  152047;
        if($this->esFlag == $this->eventId)
        {
            $this->assign('flag',1);
        }
    }

    private function _routerSj(){
        if($this->eventId == C('SJ_GROUP')){
            $this->player1(3);exit;
        }
        if($this->eventId == C('SJ_PERSON')){
            $this->player1(5);exit;
        }
        if($this->eventId == C('SJ_FS')){
            $this->player1(9);exit;
        }
    }

    private function _routerSjDetails(){
        if($this->eventId == C('SJ_GROUP')){
            $this->playerDetails1();exit;
        }
        if($this->eventId == C('SJ_PERSON')){
            $this->playerDetails1();exit;
        }
        if($this->eventId == C('SJ_FS')){
            $this->playerDetails1();exit;
        }
    }

        /**
     * index
     * 首页
     * @access public
     * @return void
     */
    public function index() {
        $this->_routerSj();
        // 活动分类
        $cate = D('EventType')->getType($this->obj['is_school_event']);
        $this->assign('category', $cate);
        $list = D('EventNews')->where(array('eventId' => $this->eventId))->order('id DESC')->limit(6)->findAll();
        $this->assign("news", $list);
        $order = 'ticket DESC';
//        if($this->eventId==87788){
//            $order = 'id DESC';
//        }
        $list = D('EventPlayer')->field('id,realname,school,path,ticket,stoped,commentCount')
                ->where(array('eventId' => $this->eventId,'status'=>1))->order($order)->limit(12)->findAll();
        foreach ($list as &$v){
            $v['imgCount'] = M('EventImg')->where('uid='.$v['id'])->count();
            $v['flashCount'] = M('EventFlash')->where('uid='.$v['id'])->count();
            $v['commentCount'] = M('comment')->where('type = \'eventPlayer\' and appid = '.$v['id'])->count();
            M('event_player')->where('id = '.$v['id'])->save(array('commentCount'=>$v['commentCount']));
//            if($this->eventId==87788){
//                $v['ticket'] = '隐藏';
//            }
        }
        $this->assign("player", $list);
        $list = D('EventImg')->where(array('uid' => 0,'eventId' => $this->eventId))->order('id ASC')->limit(8)->findAll();
        $this->assign("photo", $list);
        $list = D('EventFlash')->where(array('eventId' => $this->eventId,'show'=>1))->order('id ASC')->limit(6)->findAll();
        $this->assign("video", $list);
        $this->assign('orga', D('EventOrga')->getEventOrga($this->eventId));
        //已投几票
        $this->assign('restVote', $this->_getRestVote());
        $this->assign('bandVote', $this->_getBandVote());
        $this->display();
    }

    private function _getRestVote(){
        $restVote = 0;
        $map = array();
        if($this->mid){
            //如果活动支持每日投票
            $map['eventId'] = $this->eventId;
            $map['mid'] = $this->mid;
            if($this->obj['repeatTicket']){
                $start = strtotime(date('Y-m-d'));
                $end = strtotime(date('Y-m-d',strtotime('+1day')));
                $map['cTime'] = array(array('gt',$start),array('lt',$end));
            }
//             $votedCount = M('event_vote')->where('eventId='.$this->eventId.' and mid='.$this->mid)->count();
            $isjingying = M('event')->where("id=$this->eventId and es_type>0")->count() ;
            if ($isjingying) {
                $mode_name = 'event_spcil_vote_'.$this->eventId;
                $votedCount = M("$mode_name")->where($map)->count();
            }else{
                $votedCount = M('event_vote')->where($map)->count();
            }
            $restVote = $this->obj['maxVote'] - $votedCount;
        }
        return $restVote;
    }

    //不可重复投票pid
    private function _getBandVote(){
        $res = array();
        if($this->mid && !$this->obj['repeated_vote']){
            $isjingying = M('event')->where("id=$this->eventId and es_type>0")->count() ;
            if ($isjingying) {
                $mode_name = 'event_spcil_vote_'.$this->eventId;
                $pids = M("$mode_name")->where('eventId='.$this->eventId.' and mid='.$this->mid)->field('pid')->findAll();
            }else{
                $pids = M('event_vote')->where('eventId='.$this->eventId.' and cTime>'.strtotime(date('Y-m-d')).' and mid='.$this->mid)->field('pid')->findAll();
            }            
            $res = getSubByKey($pids,'pid');
            $res = array_unique($res);
        }
        return $res;
    }


    /**
     * 新闻列表
     */
    public function news(){
        $this->_routerSj();
        $order = 'id DESC';
        $map['eventId'] = $this->eventId;
        $list = D('EventNews')->where($map)->order($order)->findPage(6);
        $this->assign($list);
        $this->display();
    }

    /**
     * 新闻详细
     */
    public function newsDetails() {
        $this->_routerSj();
        $id = intval($_REQUEST['nid']);
        $map['id'] = $id;
        $map['eventId'] = $this->eventId;
        $app = D('EventNews')->where($map)->find();
        if (!$app) {
            $this->error('新闻不存在或已被删除！');
        }
        $app['content'] = htmlspecialchars_decode($app['content']);
        $this->assign('app',$app);
        $this->display();
    }

    /**
     *人气排行列表
     */
    public function player() {
        $this->_routerSj();
        $keyword = msubstr(h($_POST['keyword']),0,10,'utf-8',false);
        //echo $keyword;
        if (strlen($keyword) > 0) {
            $map['realname'] = array('like', "%{$keyword}%");
            $this->assign('keyword', $keyword);
        }

        if($this->esFlag == $this->eventId)
        {
            if(isset($_POST['c_type']))
            {
                $url = U('/Front/player',array('id'=>$this->eventId,'c_type'=>$_POST['c_type'],'e_type'=>$_POST['e_type']));
                header("Location: ".$url);
            }
            if(isset($_GET['c_type']))
            {
                $map['c_type'] = $_GET['c_type'];
                $map['e_type'] = $_GET['e_type'];
            }
        }
        $order = 'ticket DESC, id DESC';
        //判断是否团省委的特殊活动
        $isjingying = M('event')->where("id=$this->eventId and es_type>0")->count() ;
        $istuan = isTuanEvent($this->eventId) ;
        if ($isjingying && $istuan) {
            $map['status'] = 1 ;
            $map['recommPid'] = $this->eventId;
            $map['status'] = 1;
            $this->assign('tuanjy',1) ; //团省委并且是 特殊活动
            // 
            //判断活动是否结束
            $ma['id'] = $this->eventId ;
            $ma['eTime'] = array('LT',time()) ;
            $isfinish = M('event')->where($ma)->find() ;
            if ($isfinish) {
                $map['sort'] = array('GT',0) ;
                $order = 'sort';
                $list['data'] = D('EventPlayer')->join('ts_event_special_tickets s on ts_event_player.id = s.player_id')->field('id,realname,school,path,s.ticket,stoped,commentCount')->where($map)->order($order)->limit(10)->select();
            }else{
                $list = D('EventPlayer')->join('ts_event_special_tickets s on ts_event_player.id = s.player_id')->field('id,realname,school,path,s.ticket,stoped,commentCount')->where($map)->order($order)->findPage(8);
            }

            // $list['data'] = D('EventPlayer')->field('id,realname,school,path,ticket,stoped,commentCount')->where($map)->order($order)->findPage(8);
        }else{
            $map['eventId'] = $this->eventId;
            $map['_string'] = 'status=1 OR recommPid>0';
            $order = 'ticket DESC, id DESC';
            $list = D('EventPlayer')->field('id,realname,school,path,ticket,stoped,commentCount')->where($map)->order($order)->findPage(8);
        }
//        if($this->eventId==87788){
//            $order = 'id DESC';
//        }

        // 如果是需要随机排序
        if ($this->obj['player_sorted']){
            $key = md5(json_encode($map));
            $cacheData = Mmc($key);
            if (!$cacheData){
                $cacheData = D('EventPlayer')->field('id,realname,school,path,ticket,stoped,commentCount')->where($map)->order(' rand() ')->select();
                // 缓存列表
                Mmc($key,$cacheData,0,3600);
            }
            $page = isset($_GET['p'])?$_GET['p']:1;
            $page = intval($page) < 1?1:intval($page);
            $list['data'] = array_slice($cacheData,($page - 1) * 8,8);
            //$list['html'] = new Page(count($cacheData,8));
            foreach ($list['data'] as &$item){
                $item['ticket'] = M('EventPlayer')->where('id='.$item['id'])->getField('ticket');
            }
        }

        foreach ($list['data'] as &$v){
            $v['imgCount'] = M('EventImg')->where('uid='.$v['id'])->count();
            $v['flashCount'] = M('EventFlash')->where('uid='.$v['id'])->count();
            $commentCount = M('comment')->where('type = \'eventPlayer\' and appid = '.$v['id'])->count();
            if ($v['commentCount'] != $commentCount){
                $v['commentCount'] = $commentCount;
                M('event_player')->where('id = '.$v['id'])->save(array('commentCount'=>$v['commentCount']));
            }
//            if($this->eventId==87788){
//                $v['ticket'] = '隐藏';
//            }
        }
        $this->assign($list);
        $this->assign('restVote', $this->_getRestVote());
        $this->assign('bandVote', $this->_getBandVote());
        $this->display();
    }

    public function player1($type) {
        if (!empty($_POST)) {
            $_SESSION['es_searchSjf'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_searchSjf']);
        } else {
            unset($_SESSION['es_searchSjf']);
        }
        $sid = intval($_POST['sid']);
        if ($sid) {
            $map['sid'] = $sid;
        }
        $keyword = msubstr(h($_POST['keyword']),0,10,'utf-8',false);
        if (strlen($keyword) > 0) {
            $map['title'] = array('like', "{$keyword}%");
            $_POST['keyword'] = $keyword;
        }
        $map['type'] = $type;
        $map['status'] = 5;
        $map['year'] = C('SJ_YEAR');
        $list = M('sj')->where($map)->field('id,sid,title,ticket')->order('ticket DESC, id DESC')->findPage(8);
        //是否已投票
        $mid = 0;
        $restCount = 10;
        if($this->mid){
            $voteArr = M('sj_vote_'.C('SJ_YEAR'))->where('mid='.$this->mid.' and eventId='.$this->eventId)->field('pid')->findAll();
            if($voteArr){
                $voted = getSubByKey($voteArr,'pid');
                foreach ($list['data'] as $key=>$value) {
                    $list['data'][$key]['voted'] = in_array($value['id'], $voted);
                }
                $restCount = 10-count($voteArr);
            }
            $mid = $this->mid;
        }
        $this->assign('mid',$mid);
        $this->assign('restCount',$restCount);
        $sjIds = getSubByKey($list['data'], 'id');
        $map = array();
        $map['sjid'] = array('in', $sjIds);
        $map['status'] = 1;
        $imgArr = M('sj_img')->where($map)->field('sjid,attachId')->findAll();
        $imgs = orderArray($imgArr,'sjid');
        $this->assign('imgs',$imgs);
        $this->assign($list);
        $this->display('player1');
    }

    /**
     *  人气排行详细页
     */
    public function playerDetails() {
        $this->_routerSjDetails();
        
        $id = intval($_REQUEST['pid']);
        $map['id'] = $id;
        $uid = M('event')->where('id = '.$this->eventId)->getField('uid');
        if($uid == $this->mid)
        {
            $this->assign('topRole',1);
        }
        $isjingying = M('event')->where("id=$this->eventId and es_type>0")->count() ;
        if ($isjingying) {
            $map['status'] = 1;
            $map['recommPid'] = $this->eventId;
            $this->assign('tuanjy',1) ; //团省委并且是 特殊活动
            $tickets = M('event_special_tickets')->where('player_id='.$id)->getField('ticket') ;
            $app = D('EventPlayer')->where($map)->find();
            $app['ticket'] = $tickets ;
        }else{
            $map['_string'] = 'status=1 OR recommPid>0';
            $map['eventId'] = $this->eventId;
            $app = D('EventPlayer')->where($map)->find();
        }
        
        if (!$app) {
            $this->error('选手不存在或已被删除！');
        }
        if($app['paramValue']){
            $app['paramValue'] = unserialize($app['paramValue']);
        }else{
            $app['paramValue'] = array();
        }
//        if($this->eventId==87788){
//                $app['ticket'] = '隐藏';
//            }
        $this->assign('app', $app);
        $flash = D('EventFlash')->where(array('uid' => $id))->order('id ASC')->findAll();
        $this->assign('flash', $flash);
        $this->assign('flashCount', count($flash));
        $img = D('EventImg')->where(array('uid' => $id))->order('id ASC')->findAll();
        $this->assign('img', $img);
        $this->assign('imgCount', count($img));
        $this->assign('restVote', $this->_getRestVote());
        $this->assign('bandVote', $this->_getBandVote());
        $param = D('EventParameter')->getParam($this->eventId);
        $this->assign($param);
        $this->display();
    }
    public function playerDetails1() {
        $id = intval($_REQUEST['pid']);
        $map['id'] = $id;
        $map['status'] = 5;
        $app = M('sj')->where($map)->field('id,sid,sid1,title,title2,description,zusatz,ticket,type,content,year')->find();
        if (!$app) {
            $this->error('选手不存在或已被删除！');
        }
        $this->assign('app', $app);

        $mid = 0;
        $restCount = 10;
        $voted = false;
        if($this->mid){
            $table = 'sj_vote';
            if($app['year']>2013){
                $table = 'sj_vote_'.$app['year'];
            }
            $dao = M($table);
            $countVoted = $dao->where('mid='.$this->mid.' and eventId='.$this->eventId)->count();
            $restCount = 10-$countVoted;
            if($countVoted){
                $votedCount = $dao->where('mid='.$this->mid.' and pid='.$id)->count();
                if($votedCount){
                    $voted = true;
                }
            }
            $mid = $this->mid;
        }
        $this->assign('mid',$mid);
        $this->assign('voted',$voted);
        $this->assign('restCount',$restCount);

        //视频
        $flashArr = M('sj_flash')->where(array('sjid' => $id))->field('flashId')->findAll();
        $ids = getSubByKey($flashArr, 'flashId');
        $map = array();
        $map['id'] = array('in', $ids);
        $flash = M('flash')->where($map)->field('id,path')->findAll();
        $this->assign('flash', $flash);
        //图片
        $daoSjImg = M('sj_img');
        $img = $daoSjImg->where(array('sjid' => $id))->field('attachId,status')->findAll();
        $this->assign('img', $img);
        foreach($img as $v){
            if($v['status'] == 1){
                $this->assign('topImg', $v['attachId']);
            }
        }
        $template = 'playerDetails'.$app['type'];
        $this->display($template);
    }

    /**
     * 视频列表
     */
    public function video() {
        $list = D('EventFlash')->where(array('eventId' => $this->eventId,'show'=>1))->order('id DESC')->findPage(12);
        $this->assign($list);
        $this->display();
    }
    /**
     * 照片列表
     */
    public function photo() {
        $list = D('EventImg')->where(array('eventId' => $this->eventId, 'uid'=>0))->order('id ASC')->findPage(9);
        $this->assign($list);
        $this->display();
    }
    public function join(){
        //todo 检测是否属于专题活动 esFlag = 0 则非专题活动 1则为专题活动
        $esFlag = 0;
        $c_map['id'] = $this->eventId;
        $checkEventSeries = M('event')->where($c_map)->getField('es_type');
        if($checkEventSeries > 0)
        {
            $esFlag = 1;
        }
        $this->assign('esFlag',$esFlag);
        //检测结束

        $event = $this->obj;
        $event['isLogin'] = false;
        $event['hasJoin'] = false;
        $event['hasMember'] = false;
        $event['notInEventSchool'] = false;
        $upload = false;
        if($this->mid > 0){
            $event['isLogin'] = true;
            //检查用户是否是该活动允许的学校
            $canJoin = D('EventSchool2')->isSchoolEvent($this->user['sid'],$this->eventId);
            if(!$canJoin){
                $event['notInEventSchool'] = true;
            }else if ($result = D('EventUser')->hasUser($this->mid, $this->eventId)) {
                $event['hasJoin'] = true;
                $event['hasMember'] = $result['status'];
                //是否已上传选手
                if($event['hasMember'] && $event['player_upload']){
                    $upload = M('event_player')->where('uid='.$this->mid.' and eventId='.$this->eventId)->find();
                }
            }
        }
        //是否显示上传资料
        $player = array();
        $player['showUpload'] = false;
        if($event['player_upload'] && $event['hasMember'] && $event['deadline']>time() && $event['school_audit'] != 5){
            $player['showUpload'] = true;
        }
        $player['status'] = '1';
        if($upload){
            $player['status'] = '2';
            if($upload['status']){
                $player['status'] = '3';
            }
        }
        $this->assign('player', $player);
        $param = D('EventParameter')->getParam($this->eventId);
        $this->assign($param);
        if($player['status'] != '3'){
            $fdjs = "<script language='javascript'>";
            $fdjs = $fdjs . "function checkUpPlayer(){ ";
            foreach ($param['defaultName'] as $key => $val) {
                if($key!='path'){
                    if($this->esFlag == $this->eventId && $key == 'content')
                    {
                        $fdjs = $fdjs . "if (document.myform.$key.value.length > 100) {\n";
                        $fdjs = $fdjs . "alert('$val 不能多于100个字');\n";
                        $fdjs = $fdjs . "document.myform.$key.focus();\n";
                        $fdjs = $fdjs . "return false;}\n";
                    }
                    $fdjs = $fdjs . "if (document.myform.$key.value.length == 0) {\n";
                    $fdjs = $fdjs . "alert('$val 不能为空');\n";
                    $fdjs = $fdjs . "document.myform.$key.focus();\n";
                    $fdjs = $fdjs . "return false;}\n";
                }
            }
            $fdjs = $fdjs . "if(imgCount==0) {\n";
            $fdjs = $fdjs . "alert('至少上传一张图片作为头像');\n";
            $fdjs = $fdjs . "return false;}\n";
            foreach ($param['parameter'] as $key => $val) {
                $k=$key+1;
                if ($val[2] == 1) {
                    $fdjs = $fdjs . "if (document.myform.para$k.value.length == 0) {\n";
                    $fdjs = $fdjs . "alert('$val[0] 不能为空');\n";
                    $fdjs = $fdjs . "document.myform.para$k.focus();\n";
                    $fdjs = $fdjs . "return false;}\n";
                    if($val[1] == 4) {
                        $fdjs = $fdjs . "var link = document.myform.para$k.value;\n";
                        $fdjs = $fdjs . "if (!checkFlash(link)) {\n";
                        $fdjs = $fdjs . "alert('视频链接不合法');\n";
                        $fdjs = $fdjs . "return false;}\n";
                    }
                    if($this->esFlag == $this->eventId && $val[1] == 2)
                    {
                        $fdjs = $fdjs . "if (document.myform.para$k.value.length < 300) {\n";
                        $fdjs = $fdjs . "alert('$val[0] 不能少于300个字');\n";
                        $fdjs = $fdjs . "document.myform.para$k.focus();\n";
                        $fdjs = $fdjs . "return false;}\n";
                        $fdjs = $fdjs . "if (document.myform.para$k.value.length > 1000) {\n";
                        $fdjs = $fdjs . "alert('$val[0] 不能多于1000个字');\n";
                        $fdjs = $fdjs . "document.myform.para$k.focus();\n";
                        $fdjs = $fdjs . "return false;}\n";
                    }
                }
            }
            $fdjs = $fdjs . "}</script>";
            $this->assign('fdjs',$fdjs);
        }
        $upload['paramValue'] = unserialize($upload['paramValue']);
        foreach($upload['paramValue'] as $pk=>$pv)
        {
            $upload['para'.($pk+1)] = $pv;
        }
        $this->assign('event', $event);
        $this->assign('upload', $upload);
        $config = getPhotoConfig();
        $this->assign($config);
        if(!empty($upload) && $player['status'] > 1 && $esFlag == 1)
        {
            $this->display(joinEsPlayer);
        }
        else
        {
            $this->display();
        }
    }

    public function doAddUser() {
//        if (empty($_REQUEST['realname'])) {
//            $this->error('姓名不能为空！');
//        }
        $this->insertUser();
    }

    public function doSavePlayer()
    {
        $res = intval($_REQUEST['playerId']);
        $param = D('EventParameter')->getParam($this->eventId);
        $paramValue = array();
        $flash = array();
        $attachs = '';
        foreach ($param['parameter'] as $k => $v) {
            $key = $k+1;
            $key = 'para'.$key;
            $input = isset($_POST[$key])?$_POST[$key]:'';
            //视频
            if($v[1]==4){
                if($input!=''){
                    $parseLink = parse_url($input);
                    if (!preg_match("/(youku.com|ku6.com|sina.com.cn|yixia.com|t.cn)$/i", $parseLink['host'], $hosts)) {
                        $this->error('视频链接不合法');
                    }
                    $flash[] = $input;
                }
                $paramValue[] = '';
            }else{
                $paramValue[] = t($input,'nl');
            }
            if($v[1] == 3 && $input){
                if($attachs!=''){
                    $attachs .= ',';
                }
                $attachs .= $input;
            }
        }
        $imgCount = count($_POST['imgs']);
        if($imgCount>0){
            for($i=0;$i<$imgCount;$i++){
                $this->_addPhoto($_POST['imgs'][$i], $res);
            }
        }
        if(!empty($flash)){
            foreach ($flash as $link) {
                $this->_addFlash($link, $res);
            }
        }
        model('Attach')->reliveAttach($attachs);
        if($_POST['attids']){
            model('Attach')->reliveAttach($_POST['attids']);
        }
        $this->assign('jumpUrl', U('/Front/join',array('id'=>$this->eventId)));
        $this->success('上传成功');
    }

    public function insertUser() {
        $data['id'] = $this->eventId;
        $data['uid'] = $this->mid;
        //$data['realname'] = t($_REQUEST['realname']);
        $data['realname'] = $this->user['realname'];
        $data['sex'] = $this->user['sex'];
        if($this->obj['need_tel']){
            $data['tel'] = $this->user['mobile'];
        }
        $data['usid'] = $this->user['sid'];
        //$data['sid'] = intval($_REQUEST['sid']);

        $this->event->setMid($this->mid);
        $result = $this->event->doAddUser($data);
        if($result['status'] == 0){
            $this->error($result['info']);
        }else{
            $this->assign('jumpUrl', U('/Front/join',array('id'=>$this->eventId)));
            $this->success($result['info']);
        }
    }

    public function details(){
        $map['id'] = $this->eventId;
        if(isset($_REQUEST['uid'])){
            $map['uid'] = intval($_REQUEST['uid']);
        }
        $this->assign('echoFocus',U('/Front/jsonPhoto',$map));
        $this->display();
    }

    public function jsonPhoto(){
        if($this->eventId == C('SJ_GROUP') || $this->eventId == C('SJ_PERSON') || $this->eventId == C('SJ_FS')){
            $this->jsonPhoto1();
            exit;
        }
        $dao = M('EventImg');
        $uid = intval($_REQUEST['uid']);
        $result = array();
        $result['slide']['title'] = $this->obj['title'].'-照片';
        $result['slide']['createtime'] = '2012-11-21 16:16:02';
        $result['slide']['url'] = U('/Front/details',array('id'=> $this->eventId,'uid'=>$uid));
        $list = $dao->where(array('eventId'=>$this->eventId,'uid' => $uid))->order('id ASC')->findAll();
        foreach ($list as $value) {
            $vo['title'] = getShort($value['title'],35);
            $vo['intro'] = $value['title'];
            $vo['thumb_50'] = getThumb($value['path'],50,50);
            $vo['thumb_160'] = getThumb($value['path'],160,160);
            $vo['image_url'] = get_photo_url($value['path']);
            $vo['createtime'] = friendlyDate($value['cTime']);
            $vo['source'] = 'PocketUni';
            $vo['id'] = $value['id'];
            $result['images'][] = $vo;
        }
        $result['next_album']['interface'] = U('/Front/jsonPhoto',array('id'=>  $this->eventId,'uid'=>$uid));
        $result['next_album']['title'] = $this->obj['title'].'-照片';
        $result['next_album']['url'] = U('/Front/details',array('id'=>  $this->eventId,'uid'=>$uid));
        $result['next_album']['thumb_50'] = '';
        echo 'var slide_data = '.json_encode($result);
    }

    public function jsonPhoto1(){
        $dao = M('sj_img');
        $uid = intval($_REQUEST['uid']);
        $obj = M('sj')->where('id='.$uid)->field('title')->find();
        $result = array();
        $result['slide']['title'] = $obj['title'].'-照片';
        $result['slide']['createtime'] = date('Y-m-d H:i',$this->obj['cTime']);
        $result['slide']['url'] = U('/Front/details',array('id'=> $this->eventId,'uid'=>$uid));
        $attIds = $dao->where('sjid='.$uid)->field('attachId')->findAll();
        $attIds = getSubByKey($attIds, 'attachId');
        $map['id'] = array('in',$attIds);
        $list = M('attach')->where($map)->order('id ASC')->field('id,name,savepath,savename,uploadTime')->findAll();
        foreach ($list as $value) {
            $path = $value['savepath'].$value['savename'];
            $vo['title'] = getShort($obj['title'],35);
            $vo['intro'] = $obj['title'];
            $vo['thumb_50'] = tsMakeThumbUp($path,50,50);
            $vo['thumb_160'] = tsMakeThumbUp($path,160,160);
            $vo['image_url'] = PIC_URL.'/data/uploads/'.$path;
            $vo['createtime'] = friendlyDate($value['uploadTime']);
            $vo['source'] = 'PocketUni';
            $vo['id'] = $value['id'];
            $result['images'][] = $vo;
        }
        $result['next_album']['interface'] = U('/Front/jsonPhoto',array('id'=>  $this->eventId,'uid'=>$uid));
        $result['next_album']['title'] = $this->obj['title'].'-照片';
        $result['next_album']['url'] = U('/Front/details',array('id'=>  $this->eventId,'uid'=>$uid));
        $result['next_album']['thumb_50'] = '';
        echo 'var slide_data = '.json_encode($result);
    }

    //ajax 投票
    public function vote() {
        if(intval($this->mid) <= 0){
            $this->error('请先登录！');
        }
        $pid = intval($_REQUEST['pid']);
        $eventId = intval($_REQUEST['id']); //活动id
        $dao = D('EventPlayer');
        if($dao->vote($this->mid,$pid,$this->obj,$this->user['sid'],$eventId)){
            $this->success($dao->getError());
        }else{
            $this->error($dao->getError());
        }
    }
    //ajax 投票
    public function vote1() {
        $pid = intval($_REQUEST['pid']);
        $dao = D('Sj');
        $resCount = $dao->sjVote($this->mid, $this->eventId, $pid);
        if($resCount<0){
            $this->error($dao->getError());
        }elseif($resCount>0){
            $res['status'] = 2;
            $res['info'] = $dao->getError();
            echo json_encode($res);
        }else{
            $this->success($dao->getError());
        }
    }

    public function comment(){
        $this->display();
    }

    public function doAddPlayer() {
        if(!$this->mid){
            $this->error('请先登录！');
        }
        if($this->esFlag == $this->eventId)
        {
            $data['c_type'] = t($_POST['c_type']);
            $data['e_type'] = t($_POST['e_type']);
        }
        if(!$this->obj['player_upload']){
            $this->error('活动发起人已关闭上传资料');
        }
        if($this->obj['deadline']<time()){
            $this->error('上传资料时间已过');
        }
        $data['realname'] = t($_POST['realname']);
        if(empty($data['realname'])){
            $data['realname'] = $this->user['realname'];
        }
        $data['school'] = t($_POST['school']);
        if(!$data['school']){
            $this->error('选手院校未填');
        }
        $hasJoin = D('EventUser')->hasUser($this->mid, $this->eventId);
        if (!$hasJoin || $hasJoin['status']==0) {
            $this->error('尚未报名或报名未通过发起者审核');
        }
        if(empty($_POST['imgs'])){
            $this->error('至少上传一张图片作为头像');
        }
        $param = D('EventParameter')->getParam($this->eventId);
        $paramValue = array();
        $flash = array();
        $attachs = '';
        foreach ($param['parameter'] as $k => $v) {
            $key = $k+1;
            $key = 'para'.$key;
            $input = isset($_POST[$key])?$_POST[$key]:'';
            if($v[2]==1 && $input==''){
                $this->error($v[0].' 不能为空');
            }
            //视频
            if($v[1]==4){
                if($input!=''){
                    $parseLink = parse_url($input);
                    if (!preg_match("/(youku.com|ku6.com|sina.com.cn|yixia.com|t.cn)$/i", $parseLink['host'], $hosts)) {
                        $this->error('视频链接不合法');
                    }
                    $flash[] = $input;
                }
                $paramValue[] = '';
            }else{
                $paramValue[] = t($input,'nl');
            }
            if($v[1] == 3 && $input){
                if($attachs!=''){
                    $attachs .= ',';
                }
                $attachs .= $input;
            }
        }
        $data['paramValue'] = serialize($paramValue);
        $data['path'] = $_POST['imgs'][0];
        $data['cTime'] = time();
        $data['eventId'] = $this->eventId;
        $data['content'] = t($_REQUEST['content'],'nl');
        $data['uid'] = $this->mid;
        $data['sid'] = $this->user['sid'];
        $data['status'] = 0;
        $res = D('EventPlayer')->add($data);
        if(!$res){
            $this->error('上传失败，请稍后再试！');
        }
        $imgCount = count($_POST['imgs']);
        if($imgCount>1){
            for($i=1;$i<$imgCount;$i++){
                $this->_addPhoto($_POST['imgs'][$i], $res);
            }
        }
        if(!empty($flash)){
            foreach ($flash as $link) {
                $this->_addFlash($link, $res);
            }
        }
        model('Attach')->reliveAttach($attachs);
        if($_POST['attids']){
            model('Attach')->reliveAttach($_POST['attids']);
        }
        $this->assign('jumpUrl', U('/Front/join',array('id'=>$this->eventId)));
        $this->success('上传成功，等待审核');
    }

    private function _addPhoto($path,$uid,$title=''){
        $data['path'] = $path;
        $data['eventId'] = $this->eventId;
        $data['uid'] = $uid;
        $data['upUid'] = $this->mid;
        $data['title'] = $title;
        $data['cTime'] = time();
        D('EventImg')->add($data);
    }

    private function _addFlash($link,$uid){
        $link = getShortUrl($link);
        $addonsData = array();
        Addons::hook("weibo_type", array("typeId" => 3, "typeData" => $link, "result" => &$addonsData));
        $addonsData = unserialize($addonsData['type_data']);
        //var_dump($addonsData);die;
        $addonsData['title'] = preg_replace(array('/—在线播放(.*)/', '/ 在线观看(.*)/'), '', $addonsData['title']);
        $data['title'] = $addonsData['title'];
        $data['path'] = $addonsData['flashimg'] ? $addonsData['flashimg'] : '';
        $data['flashvar'] = $addonsData['flashvar'];
        $data['host'] = $addonsData['host'];
        $data['link'] = $link;
        $data['eventId'] = $this->eventId;
        $data['uid'] = $uid;
        $data['cTime'] = time();
        $data['show'] = 0;
        D('EventFlash')->add($data);
    }
}
