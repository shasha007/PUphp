<?php

/**
 * AdminAction
 * 管理
 * @uses Action
 * @package Admin
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
import('admin.Action.AdministratorAction');

class AdminAction extends AdministratorAction {

    /**
     * event
     * EventModel的实例化对象
     * @var mixed
     * @access private
     */
    private $event;

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
    }

    /**
     * basic
     * 基础设置管理
     * @access public
     * @return void
     */
    public function index() {
        $config = model('Xdata')->lget('event');
        $this->assign($config);

        $credit_types = X('Credit')->getCreditType();
        $this->assign('credit_types', $credit_types);

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
            $this->assign('jumpUrl', U('event/Admin/index'));
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
        $map['status'] = array('gt',0);
        $this->_getEventList($map);
        $this->assign('school', model('Schools')->_makeTree(0));
        $this->display();
    }

    /**
     * 活动详情
     */
    public function event(){
        $this->_getEvent();
        $this->display();
    }

    /**
     * audit
     * 待审核的活动
     * @access public
     * @return void
     */
    public function audit() {
        $map['status'] = 0;
        $this->_getEventList($map);
        $this->display();
    }

    public function doAudit() {
        $res = D('Event')->doAudit($_POST ['gid']); // 通过审核
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

    public function doDismissed() {
        $res = D('Event')->doDismissed($_POST ['gid']);
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

    private function _getEventList($map=array()){
        //get搜索参数转post
        if (!empty($_GET['typeId'])) {
            $_POST['typeId'] = $_GET['typeId'];
        }
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_search']);
        } else {
            unset($_SESSION['admin_search']);
        }
        $this->assign('isSearch', isset($_POST['isSearch']) ? '1' : '0');

        $map['isDel'] = 0;
        $_POST['uid'] && $map['uid'] = intval($_POST['uid']);
        $_POST['id'] && $map['id'] = intval($_POST['id']);
        $_POST['typeId'] && $map['typeId'] = intval($_POST['typeId']);
        $_POST['sid'] && $map['is_school_event'] = intval($_POST['sid']);
        $_POST['is_prov_event'] && $map['is_prov_event'] = intval($_POST['is_prov_event'])-1;
        $_POST['title'] && $map['title'] = array('like', '%' . t($_POST['title']) . '%');
        isset($_POST['isTop']) && $_POST['isTop'] != '' && $map['isTop'] = intval($_POST['isTop']);
        isset($_POST['isHot']) && $_POST['isHot'] != '' && $map['isHot'] = intval($_POST['isHot']);
        //处理时间
//            $_POST['sTime'] && $_POST['eTime'] && $map['cTime'] = $this->event->DateToTimeStemp(t( $_POST['sTime'] ),t( $_POST['eTime'] ) );
        $_POST['sTime'] && $_POST['eTime'] && $map['cTime'] = $this->event->DateToTimeStemp(t(date("Ymd", strtotime($_POST['sTime']))), t(date("Ymd", strtotime($_POST['eTime']))));
        //处理排序过程
        $order = isset($_POST['sorder']) ? t($_POST['sorder']) . " " . t($_POST['eorder']) : "id DESC";
        $_POST['limit'] && $limit = intval(t($_POST['limit']));

        $order && $list = $this->event->where($map)->field('id,is_prov_event,title,typeId,uid,joinCount,credit,score,cTime,
            school_audit,audit_uid,audit_uid2,status,isTop,isHot,puRecomm,is_school_event,gid,attachId,fTime,endattach')->order($order)->findPage($limit);
        $type_list = D('EventType')->getType();
        $this->assign($_POST);
        $this->assign($list);
        $this->assign('type_list', $type_list);
    }


    /**
     * transferEventTab
     * 转移活动
     * @access public
     * @return void
     */
    public function transferEventTab() {
        $type_list = D('EventType')->getType();
        $this->assign('type_list', $type_list);
        $this->assign('id', $_GET['id']);
        $this->display();
    }

    /**
     * doDeleteEvent
     * 执行转移活动
     * @access public
     * @return void
     */
    public function doTransferEvent() {
        $id['id'] = array('in', t($_POST['id']));
        $data['typeId'] = intval($_POST['type']);
        if (!$_POST['id'] || !$data['typeId']) {
            echo -2;
            exit;
        }
        if ($this->event->where($id)->save($data)) {
            if (!strpos($_REQUEST['id'], ",")) {
                echo 2;            //说明只操作一个
            } else {
                echo 1;            //操作多个
            }
        } else {
            echo -1;               //操作失败
        }
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
        $event['id'] = array('in', $_REQUEST['id']);        //要推荐的id.
        $act = $_REQUEST['type'];  //推荐动作
        $result = $this->event->doIsHot($event, $act);

        if (false != $result) {
            echo 1;            //推荐成功
        } else {
            echo -1;               //推荐失败
        }
    }
    //置顶操作
    public function doChangeIsTop() {
        $event['id'] = array('in', $_REQUEST['id']);        //要推荐的id.
        $act = $_REQUEST['type'];  //动作
        $result = $this->event->doIsTop($event, $act);

        if (false != $result) {
            echo 1;            //成功
        } else {
            echo -1;           //失败
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

    /**
     * eventtype
     * 活动类型列表
     * @access public
     * @return void
     */
    public function eventtype() {
        $type = D('EventType');
        $type = $type->order('id ASC')->findAll();
        $this->assign('type_list', $type);

        $count = D('Event')->field('typeId,count(typeId) as count')->group('typeId')->findAll();
        foreach ($count as $k => $v) {
            // unset($count[$k]);
            $count[$v['typeId']] = $v['count'];
        }
        $this->assign('count', $count);

        $this->display();
    }

    /**
     * editEventTab
     * 添加分类
     * @access public
     * @return void
     */
    public function editEventTab() {
        $id = intval($_GET['id']);
        if ($id) {
            $name = D('EventType')->getField('name', "id={$id}");
            $this->assign('id', $id);
            $this->assign('name', $name);
        }
        $this->display();
    }

    /**
     * doAddType
     * 添加分类
     * @access public
     * @return void
     */
    public function doAddType() {
        $isnull = preg_replace("/[ ]+/si", "", t($_POST['name']));
        $type = D('EventType');
        $name = M('EventType')->where(array('name' => $isnull))->getField('name');
        if (empty($isnull)) {
            echo -2;
        }
        if ($name !== null) {
            echo 0;
        } else {
            if ($result = $type->addType($_POST)) {
                echo 1;
            } else {
                echo -1;
            }
        }
    }

    /**
     * doEditType
     * 修改分类
     * @access public
     * @return void
     */
    public function doEditType() {
        $_POST['id'] = intval($_POST['id']);
        $_POST['name'] = t($_POST['name']);
        $_POST['name'] = preg_replace("/[ ]+/si", "", $_POST['name']);
        if (empty($_POST['name'])) {
            echo -2;
        }
        $type = D('EventType');
        $name = M('EventType')->where(array('name' => t($_POST['name'])))->getField('name');
        if ($name !== null) {
            echo 0; //分类名称重复
        } else {
            if ($result = $type->editType($_POST)) {
                echo 1; //更新成功
            } else {
                echo -1;
            }
        }
    }

    /**
     * doEditType
     * 删除分类
     * @access public
     * @return void
     */
    public function doDeleteType() {
        $id['id'] = array("in", $_POST['id']);
        $type = D('EventType');
        if ($result = $type->deleteType($id)) {
            if (!strpos($_POST['id'], ",")) {
                echo 2;            //说明只是删除一个
            } else {
                echo 1;            //删除多个
            }
        } else {
            echo $result;
        }
    }

    /**
     * 编辑活动
     */
    public function editEvent(){
        $this->_getEvent();
        $school = model('Schools')->getEventSelect(0);
        $this->assign('addSchool', $school);
        $this->assign('type', 'edit');
        $this->display();
    }

    private function _getEvent(){
        //活动
        $id = intval($_REQUEST['id']);
        //检测id是否为0
        if ($id <= 0) {
            $this->assign('jumpUrl', U('/Admin/eventlist'));
            $this->error("错误的访问页面，请检查链接");
        }
        $map['id'] = $id;
        if ($result = $this->event->where($map)->find()) {
            $this->assign($result);
        } else {
            $this->assign('jumpUrl', U('/Index/index'));
            $this->error('活动不存在');
        }
        // 活动分类
        $cate = D('EventType')->getType();
        $this->assign('category', $cate);
    }

    public function doEditEvent() {
        $id = intval($_POST['id']);
        if (!$obj = $this->event->where(array('id'=>$id))->find()) {
            $this->error('活动不存在或已删除');
        }
        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 20) {
            $this->error('活动名称最大20个字！');
        }
        $map['deadline'] = _paramDate($_POST['deadline']);
        $map['sTime'] = _paramDate($_POST['sTime']);
        $map['eTime'] = _paramDate($_POST['eTime']);
        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于结束时间');
        }

        //得到上传的图片
        $config = getPhotoConfig();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $cover = X('Xattach')->upload('event', $options);
        if(!$cover['status'] && $cover['info']!='没有选择上传文件'){
            $this->error($cover['info']);
        }

        $map['title'] = $title;
        $map['address'] = t($_POST['address']);
        $map['limitCount'] = intval(t($_POST['limitCount']));
        $map['typeId'] = intval($_POST['typeId']);
        $map['contact'] = t($_POST['contact']);
        $map['cost'] = intval($_POST['cost']);
        $map['costExplain'] = keyWordFilter(t($_POST['costExplain']));
        $map['allow'] = isset($_POST['allow']) ? 1 : 0;
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        $map['free_attend'] = isset($_POST['free_attend']) ? 1 : 0;
        $map['isTicket'] = isset($_POST['isTicket']) ? 1 : 0;
        $map['sid'] = intval($_POST['sid']);

        if ($this->event->doEditEvent($map, $cover, $obj)) {
            $this->assign('jumpUrl', U('/Admin/editEvent', array('id' => $id, 'uid' => $this->mid)));
            $this->success($this->appName . '修改成功！');
        } else {
            $this->error($this->appName . '修改失败');
        }
    }

    public function newsList() {
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
$map['is_school_event'] = 0;
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
$map['is_school_event'] = 0;
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
$map['is_school_event'] = 0;
        $db_prefix = C('DB_PREFIX');
        $list = D('EventImg')->table("{$db_prefix}event_img AS a ")
                ->join("{$db_prefix}event AS b ON a.eventId=b.id")
                ->field('a.id,a.eventId,a.path,a.title, a.cTime, a.uid as imgUid, b.uid')
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

    function home() {
        Session::pause();
        $this->display ();
    }
    function result() {
        $daoLucky = M('lucky');
        $list0 = $daoLucky->where('type = 0')->order('num ASC')->field('num')->findAll();
        $this->assign('list0',$list0);
        $list1 = $daoLucky->where('type = 1')->order('num ASC')->field('num')->findAll();
        $this->assign('list1',$list1);
        $list2 = $daoLucky->where('type = 2')->order('num ASC')->field('num')->findAll();
        $this->assign('list2',$list2);
        $list3 = $daoLucky->where('type = 3')->order('num ASC')->field('num')->findAll();
        $this->assign('list3',$list3);
        $this->setTitle ('获奖名单');
        $this->display ();
    }

    public function lucky(){
        $type = intval($_REQUEST['type']);
        if($type!=2&&$type!=3){
            $type = 3;
        }
        $this->assign('maxNum', M('lucky_pool')->max('num'));
        $daoLucky = M('lucky');
        $list = $daoLucky->where('type = '.$type)->field('num')->findAll();
        $this->assign('list',$list);
        if($type == 2){
            $this->assign('restTime',2-count($list)/10);
        }elseif($type == 3){
            $this->assign('restTime',5-count($list)/10);
        }
        $this->assign('type',$type);
        $class = '';
        $title = '三等奖';
        $desc = '数码商务套件  50名';
        if($type == 2){
            $class = 'ii1';
            $title = '二等奖';
            $desc = '蚕丝被 20名';
        }
        $this->assign('class',$class);
        $this->assign('desc',$desc);
        $this->setTitle ($title);
        $this->display ();
    }

    public function te(){
        $type = intval($_REQUEST['type']);
        if($type!=1&&$type!=0){
            $type = 0;
        }
        $daoLucky = M('lucky');
        $list = $daoLucky->where('type = '.$type)->field('num')->findAll();
        $this->assign('list',$list);
        if($type == 0){
            $this->assign('restTime',1-count($list));
        }elseif($type == 1){
            $this->assign('restTime',5-count($list));
        }
        $deco['class'] = 'ii3';
        $deco['title'] = '特等奖';
        $deco['desc'] = '微软Surface平板电脑 1名';
        if($type == 1){
            $deco['class'] = 'ii2';
            $deco['title'] = '一等奖';
            $deco['desc'] = 'ONDA平板电脑 5名';
        }
        $this->setTitle ($deco['title']);
        $this->assign($deco);
        $this->assign('type',$type);
        $this->display ();
    }

    public function cjte(){
        $type = intval($_REQUEST['type']);
        if($type!=0&&$type!=1){
            $type = 0;
        }
        $this->checkCjEnd($type);

        $daoPool = M('lucky_pool');
        $pool = $daoPool->where('isDel = 0')->field('num')->findAll();
        shuffle($pool);
        $result['status'] = 1;
        $result['data'] = $pool;
        echo json_encode($result);
    }

    public function saveCj(){
        $type = intval($_REQUEST['type']);
        if($type!=0&&$type!=1){
            $type = 0;
        }
        $this->checkCjEnd($type);
        $daoLucky = M('lucky');
        $num = intval($_REQUEST['num']);
        if($num <= 0){
            $this->error('操作太快，请重试！');
        }
        if(!$daoLucky->add(array('num'=>$num,'type'=>$type))){
            $this->error('操作太快，请重试！');
        }
        $daoPool = M('lucky_pool');
        if(!$daoPool->where(array('num'=>$num))->setField('isDel',1)){
            $this->error('操作太快，请重试！');
        }
        $this->success('保存成功');
    }

    private function checkCjEnd($type){
        $daoLucky = M('lucky');
        $cnt = $daoLucky->where('type = '.$type)->count();
        if($type == 0 && $cnt >= 1) $this->error('特等奖最多1名！');
        if($type == 1 && $cnt >= 5) $this->error('一等奖最多5名！');
    }

    public function cj(){
        $type = intval($_REQUEST['type']);
        if($type!=0&&$type!=1&&$type!=2&&$type!=3){
            $this->error('抽奖类型错误，特等奖到3等奖！');
        }
        $daoLucky = M('lucky');
        $cnt = $daoLucky->where('type = '.$type)->count();
        switch ($type) {
            case 3:
                if($cnt >= 50) $this->error('三等奖最多50名！');
                $max = 10;
                break;
            case 2:
                if($cnt >= 20) $this->error('二等奖最多20名！');
                $max = 10;
                break;
            case 1:
                if($cnt >= 5) $this->error('一等奖最多5名！');
                $max = 1;
                break;
            case 0:
                if($cnt >= 1) $this->error('特等奖最多1名！');
                $max = 1;
                break;
            default:
                $this->error('抽奖类型错误，特等奖到3等奖！');
                break;
        }

        $daoPool = M('lucky_pool');
        $pool = $daoPool->where('isDel = 0')->field('num')->findAll();
        shuffle($pool);
        $list = array_rand($pool,$max);
        $data = array();
        $insert = array();
        if(is_array($list)){
            foreach ($list as $value) {
                $data[] = $pool[$value]['num'];
                $daoLucky->add(array('num'=>$pool[$value]['num'],'type'=>$type));
            }
        }else{
            $data[] = $pool[$list]['num'];
            $daoLucky->add(array('num'=>$pool[$list]['num'],'type'=>$type));
        }
        $map['num'] = array('in',$data);
        $daoPool->where($map)->setField('isDel',1);
        $result['status'] = 1;
        $result['data'] = $data;
        echo json_encode($result);
    }

    public function installLucky(){
        header('Content-Type: text/html; charset=utf-8');
        $go = intval($_GET['go']);
        if($go>=76){
            M('lucky')->where(1)->delete();
            $dao = M('lucky_pool');
            $dao->where(1)->delete();
            for($i=1;$i<=$go;$i++){
                $dao->add(array('num'=>$i));
            }
            echo '初始化数据库成功！ 抽奖号码 1 - '. $go;
        }else{
            echo '初始化失败！抽奖号码不得小于76';
        }
    }

    public function workAttach(){
        $map['isDel'] = 1;
        $list = D('WorkAttach','event')->where()->findPage(20);
        $this->assign($list);
        $this->display();
    }

    public function delWorkAttach(){
        $res = D('WorkAttach','event')->remove($_POST['ids']);
        echo ($res)?1:0;
    }

    public function recommList() {
        if (!empty($_POST)) {
            $_SESSION['admin_event_recomm_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_event_recomm_search']);
        } else {
            unset($_SESSION['admin_event_recomm_search']);
        }
        $map = array();
        $_POST['provId'] = intval($_POST['provId']);
        $_POST['provId'] && $map['provId'] = $_POST['provId'];
        $_POST['eventId'] = intval($_POST['eventId']);
        $_POST['eventId'] && $map['eventId'] = $_POST['eventId'];
        $this->assign($_POST);
        $list = M('event_recomm')->where($map)->order('id desc')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function delRecomm() {
        $ids = explode(',', t($_POST ['nid']));
        foreach($ids as $id){
            if($id!=0){
                $map['id'] = $id;
                M('event_recomm')->where($map)->delete();
            }
        }
        $this->success('删除成功！');
    }

    public function doAddRecomm() {
        $eventId = intval($_POST['eventId']);
        $provId = intval($_POST['provId']);
        $res = false;
        if($eventId && $provId && $eventId!=$provId){
            $data = array('provId'=>$provId,'eventId'=>$eventId);
            $res = M('event_recomm')->add($data);
        }
        if($res){
            $this->success('添加成功！');
        }
        $this->error('添加失败！上传活动'.$eventId.'不可关联多个活动');
    }

        //pu推荐
    public function doChangePuRecomm() {
        $act = $_REQUEST['type'];  //动作
        $event['id'] = intval($_REQUEST['id']);        //要推荐的id.
        $result = $this->event->doPuRecomm($event, $act);
        if (false != $result) {
            $this->success('操作成功');        //成功
        } else {
            $this->error('设置失败');       //失败
        }
    }
}


