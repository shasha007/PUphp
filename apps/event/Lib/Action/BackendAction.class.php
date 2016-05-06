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
class BackendAction extends Action {

    private $eventId;
    private $event;
    private $obj;

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        global $ts;
        $this->event = D('Event');
        //活动
        $id = intval($_REQUEST['id']);
        //检测id是否为0
        if ($id <= 0) {
            $this->assign('jumpUrl', U('/Index/index'));
            $this->error("错误的访问页面，请检查链接");
        }
        $this->event->setMid($this->mid);
        $map['isDel'] = 0;
        $map['id'] = $id;
        if ($result = $this->event->where($map)->find()) {
            if ($result['uid'] != $this->mid && !service('SystemPopedom')->hasPopedom($this->mid, 'admin/Index/index', false)) {
                $this->error('您没有权限管理该活动');
            }
            if ($result['status'] == 0) {
                $this->error('该活动正在审核中');
            } elseif ($result['status'] == 2) {
                $this->error('该活动未通过审核');
            }
            $this->assign($result);
        } else {
            $this->assign('jumpUrl', U('/Index/index'));
            $this->error('活动不存在或，未通过审核或，已删除');
        }
        $this->assign('eventId', $id);
        $this->setTitle($result['title']);
        $this->eventId = $id;
        $this->obj = $result;

        // 活动分类
        $cate = D('EventType')->getType();
        $this->assign('category', $cate);
    }

    /**
     * 活动管理首页
     */
    public function index() {
        $user = $this->get('user');
        $school = model('Schools')->getEventSelect($user['sid']);
        $this->assign('addSchool', $school);
        //计算待审核人数
        $result['verifyCount'] = D('EventUser')->where("status = 0 AND eventId ={$result['id']}")->count();
        $this->assign($result);
        $map['eventId'] = $this->eventId;
        $showInSchool = D('EventSchool')->where($map)->findAll();
        $this->assign('showInSchool', $showInSchool);
        $this->display();
    }

    /**
     * edit
     * 编辑活动
     * @access public
     * @return void
     */
    public function edit() {
        $this->canEditEvent();
        $user = $this->get('user');
        $school = model('Schools')->getEventSelect($user['sid']);
        $this->assign('addSchool', $school);
        $sids = D('EventSchool')->getSchoolIds($this->eventId);
        $sidStr = '';
        if ($sids) {
            $sidStr = implode(',', $sids);
            $sidStr = '0,' . $sidStr . ',';
        }
        $this->assign('sidStr', $sidStr);
        $typeDao = D('EventType');
        $type = $typeDao->getType2();
        $this->assign('type', $type);
        $this->display();
    }

    /**
     * doEditEvent
     * 修改活动
     * @access public
     * @return void
     */
    public function doEditEvent() {
        $this->canEditEvent();
        $title = t($_POST['title']);
        $textarea = t($_POST['description']);
        if (mb_strlen($textarea, 'UTF8') <= 0 || mb_strlen($textarea, 'UTF8') > 200) {
            $this->error("活动简介1到200字!");
        }
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
        $sids = substr($_POST['showSids'], 2, strlen($_POST['showSids']) - 3);
        if (!$sids) {
            $this->error('请选择活动显示于哪些学校');
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
        $defaultBanner = intval($_POST['banner']);
        if($defaultBanner){
            $map['logoId'] = 0;
            $map['default_banner'] = intval($_POST['banner']);
        }
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
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        $map['free_attend'] = isset($_POST['free_attend']) ? 1 : 0;
        $map['isTicket'] = isset($_POST['isTicket']) ? 1 : 0;
        $map['sid'] = intval($_POST['sid']);
        $map['description'] = $textarea;
        if ($this->event->doEditEvent($map, $cover, $this->obj)) {
            D('EventSchool')->removeByEid($this->eventId);
            D('EventSchool')->addBySids($this->eventId, $sids);
            $this->assign('jumpUrl', U('/Backend/index', array('id' => $this->obj['id'], 'uid' => $this->mid)));
            $this->success($this->appName . '修改成功！');
        } else {
            $this->error($this->appName . '修改失败');
        }
    }

    private function canEditEvent() {
        if (!service('SystemPopedom')->hasPopedom($this->mid, 'admin/Index/index', false)
                && $this->obj['uid'] == $this->mid && $this->obj['school_audit'] == 5) {
            $this->error('该活动已完结，无法更改');
        }
    }

    /**
     * doEndAction
     * 结束活动
     * @access public
     * @return void
     */
    public function doEndAction() {
        $id = $_POST['id'];
        $this->event->setMid($this->mid);
        echo $this->event->doEditData(time(), $id);
    }

    /**
     * doDeleteEvent
     * 删除活动
     * @access public
     * @return void
     */
    public function doDeleteEvent() {
        $eventid['id'] = intval($_REQUEST['id']);    //要删除的id.
        $eventid['uid'] = $this->mid;
        $result = $this->event->doDeleteEvent($eventid);
        if (false != $result) {
            echo 1;
        } else {
            echo 0;               //删除失败
        }
    }

    /**
     * member
     * 活动成员
     * @access public
     * @return void
     */
    public function member() {
        if (!empty($_POST)) {
            $_SESSION['backend_event_user_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['backend_event_user_search']);
        } else {
            unset($_SESSION['backend_event_user_search']);
        }
        $_POST['realname'] = t(trim($_POST['realname']));
        $_POST['realname'] && $map['realname'] = array('like', "%" . $_POST['realname'] . "%");
        $this->assign($_POST);

        $map['a.status'] = 1;
        $map['a.eventId'] = $this->eventId;
        $order = 'a.isHot DESC,a.id DESC';
        if ($_GET['orderKey'] && $_GET['orderType']) {
            $order = $_GET['orderKey'] . ' ' . $_GET['orderType'];
            $this->assign('orderKey', $_GET['orderKey']);
            $this->assign('orderType', $_GET['orderType']);
        }
        //取得成员列表
        //$result = D('EventUser')->getUserList($map);
        $db_prefix = C('DB_PREFIX');
        $result = D('EventUser')->table("{$db_prefix}event_user AS a ")
                        ->join("{$db_prefix}event_img AS b ON a.id=b.uid")
                        ->join("{$db_prefix}event_flash AS c ON a.id=c.uid")
                        ->field('a.* , count(DISTINCT b.id) AS img, count(DISTINCT c.id) AS flash')->group('a.id')
                        ->where($map)->order($order)->findPage(10);
        //var_dump($result);die;
        $this->assign($result);
        $this->setTitle('成员列表');
        // 院校
        $schools = model('Schools')->__getCategory();
        $this->assign('schools', $schools);
        $this->display();
    }

    /**
     * 成员审核
     * @access public
     * @return void
     */
    public function memberAudit() {
        $map['status'] = 0;
        $map['eventId'] = $this->eventId;
        //取得成员列表
        $result = D('EventUser')->getUserList($map);
        $this->assign($result);
        $this->setTitle('成员审核');
        // 院校
        $schools = model('Schools')->__getCategory();
        $this->assign('schools', $schools);
        $this->display();
    }

    /**
     * 成员照片列表
     */
    public function memberImg() {
        $map['id'] = intval($_REQUEST['uid']);
        $map['eventId'] = $this->eventId;
        $user = D('EventUser')->where($map)->find();
        if (!$user) {
            $this->error('成员不存在');
        }
        $this->assign('member', $user);
        $map['uid'] = intval($_REQUEST['uid']);
        unset($map['id']);
        $list = D('EventImg')->where($map)->order('`id` DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    /**
     * 成员视频列表
     */
    public function memberFlash() {
        $map['id'] = intval($_REQUEST['uid']);
        $map['eventId'] = $this->eventId;
        $user = D('EventUser')->where($map)->find();
        if (!$user) {
            $this->error('成员不存在');
        }
        $this->assign('member', $user);
        $map['uid'] = intval($_REQUEST['uid']);
        unset($map['id']);
        $list = D('EventFlash')->where($map)->order('`id` DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    /**
     * 审核成员
     */
    public function doAuditMember() {
        $this->canEditEvent();
        if ($this->obj['limitCount'] < 1) {
            $this->error('参加活动人数已满，不能再参加');
        }
        $data['id'] = intval($_POST['mid']);
        $data['eventId'] = $this->eventId;
        if ($this->event->doArgeeUser($data)) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    /**
     * 审核成员
     */
    public function doDeleteMember() {
        $this->canEditEvent();
        $data['id'] = intval($_POST['mid']);
        $data['eventId'] = $this->eventId;
        $res = $this->event->doDelUser($data,true);
        if ($res['status']) {
            $this->success($res['msg']);
        }
        $this->error($res['msg']);
    }

    /**
     * 活动开启投票
     */
    public function doChangeIsTicket() {
        $map['id'] = $this->eventId;
        $act = $_REQUEST['type'];  //动作
        if ($this->event->doIsTicket($map, $act)) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    /**
     * 用户开启投票
     */
    public function doChangeVote() {
        $map['id'] = intval($_REQUEST['uid']);
        $map['eventId'] = $this->eventId;
        $act = $_REQUEST['type'];  //动作
        if (D('EventUser')->doChangeVote($map, $act)) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    public function editUser() {
        $this->canEditEvent();
        $this->assign('type', 'add');
        if ($uid = (int) $_REQUEST['uid']) {
            $this->assign('type', 'edit');
            $map['eventId'] = $this->eventId;
            $map['id'] = $uid;
            $user = D('EventUser')->where($map)->find();
            if (!$user) {
                $this->error('无法找到对象');
            }
            $this->assign("holdUser", $user);
        }
        $this->display();
    }

    public function doAddUser() {
        $this->canEditEvent();
        if (empty($_REQUEST['realname'])) {
            $this->error('姓名不能为空！');
        }
        if ($this->obj['need_tel'] && empty($_REQUEST['tel'])) {
            $this->error('联系电话不能为空！');
        }
        $uid = intval($_REQUEST['uid']);
        if (empty($uid)) {
            $this->insertUser();
        } else {
            $this->updateUser($uid);
        }
    }

    public function insertUser() {
        $this->canEditEvent();
        if ($this->obj['limitCount'] < 1) {
            $this->error('人数已满！添加失败');
        }
        $data = $this->_getUserData();
        if ($res = D('EventUser')->add($data)) {
            $this->event->setInc('joinCount', 'id=' . $this->eventId);
            $this->event->setDec('limitCount', 'id=' . $this->eventId);
            $this->assign('jumpUrl', U('/Backend/member', array('id' => $this->eventId)));
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    /**
     * 活动添加修改成员的信息
     */
    private function _getUserData() {
        $info = eventUpload();
        if ($info['status'])
            $data['path'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        $data['cTime'] = time();
        $data['tel'] = t($_REQUEST['tel']);
        $data['eventId'] = $this->eventId;
        $data['uid'] = 0;
        $data['status'] = 1;
        $data['ticket'] = intval($_REQUEST['ticket']);
        $data['realname'] = t($_REQUEST['realname']);
        $data['sex'] = intval($_REQUEST['sex']);
        //处理院校
        $sids = explode(',', $_POST['current']);
        $cnt = count($sids);
        if ($sids[0] != '') {
            $data['sid'] = intval($sids[$cnt - 1]);
        }
        return $data;
    }

    public function updateUser($id) {
        $this->canEditEvent();
        $map['id'] = $id;
        $map['eventId'] = $this->eventId;
        $daoUser = D('EventUser');
        $user = $daoUser->where($map)->find();
        if (!$user) {
            $this->error('非法参数，成员不存在！');
        }
        $data = $this->_getUserData();
        if ($daoUser->where("id={$id}")->save($data)) {
            //删除旧头像
            if ($user['path'] && isset($data['path'])) {
                deletePath($user['path']);
            }
            $this->assign('jumpUrl', U('/Backend/member', array('id' => $this->eventId)));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    /**
     * newslist
     * 活动新闻列表
     * @access public
     * @return void
     */
    public function newsList() {
        if (!empty($_POST)) {
            $_SESSION['backend_event_news_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['backend_event_news_search']);
        } else {
            unset($_SESSION['backend_event_news_search']);
        }
        $_POST['newsTitle'] = t(trim($_POST['newsTitle']));
        $_POST['newsTitle'] && $map['title'] = array('like', "%" . $_POST['newsTitle'] . "%");
        $this->assign($_POST);
        $map['eventId'] = $this->eventId;
        $list = D('EventNews')->where($map)->order('`id` DESC')->findPage(10);
        $this->assign('list', $list);
        $this->setTitle('新闻列表');
        $this->display();
    }

    public function newsEdit() {
        $this->assign('type', 'add');
        if ($nid = (int) $_REQUEST['nid']) {
            $this->assign('type', 'edit');
            $map['eventId'] = $this->eventId;
            $map['id'] = $nid;
            $news = D('EventNews')->where($map)->find();
            if (!$news) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("news", $news);
        }
        $this->display();
    }

    public function doNewsEdit() {
        $title = t($_POST['title']);
        if (empty($title)) {
            $this->error('新闻名称不能为空！');
        }
        if (mb_strlen($title, 'UTF8') > 60) {
            $this->error('新闻名称最大60个字！');
        }
        $nid = intval($_REQUEST['nid']);
        if (empty($nid)) {
            $this->insertNews();
        } else {
            $this->updateNews($nid);
        }
    }

    public function insertNews() {
        $title = t($_POST['title']);
        $map['title'] = $title;
        $map['eventId'] = $this->eventId;
        $map['content'] = t(h($_POST['content']));
        $map['cTime'] = time();
        $map['uTime'] = time();

        if (D('EventNews')->add($map)) {
            //保存成功则刷新页面
            $this->assign('jumpUrl', U('/Backend/newsList', array('id' => $this->eventId)));
            $this->success('添加成功！');
        } else {
            //失败提示
            $this->error('添加失败！');
        }
    }

    public function updateNews($id) {
        $title = t($_POST['title']);
        $map['title'] = $title;
        $map['content'] = t(h($_POST['content']));
        $map['uTime'] = time();
        if (D('EventNews')->where("id={$id}")->save($map)) {
            $this->assign('jumpUrl', U('/Backend/newsList', array('id' => $this->eventId)));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    /**
     * newsDelete
     * 删除新闻
     * @access public
     * @return int
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

    public function flash() {
        if (!empty($_POST)) {
            $_SESSION['backend_event_flash_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['backend_event_flash_search']);
        } else {
            unset($_SESSION['backend_event_flash_search']);
        }
        $_POST['flashTitle'] = t(trim($_POST['flashTitle']));
        $_POST['flashTitle'] && $map['title'] = array('like', "%" . $_POST['flashTitle'] . "%");
        $this->assign($_POST);
        $map['eventId'] = $this->eventId;
        $list = D('EventFlash')->where($map)->order('`id` DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function addFlash() {
        if (isset($_REQUEST['uid'])) {
            $this->assign('memberId', intval($_REQUEST['uid']));
        }
        $this->display();
    }

    public function doAddFlash() {
        $link = t($_POST['link']);
        $parseLink = parse_url($link);
        if (preg_match("/(youku.com|ku6.com|sina.com.cn)$/i", $parseLink['host'], $hosts)) {
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
            $jumpUrl = U('/Backend/flash', array('id' => $this->eventId));
            if (isset($_POST['uid'])) {
                $data['uid'] = intval($_POST['uid']);
                $jumpUrl = U('/Backend/memberFlash', array('id' => $this->eventId, 'uid' => $data['uid']));
            }
            $data['cTime'] = time();
            if (D('EventFlash')->add($data)) {
                //成功提示
                $this->assign('jumpUrl', $jumpUrl);
                $this->success('添加成功！');
            } else {
                //失败提示
                $this->error('添加失败！');
            }
        } else {
            $this->error(L('only_support_video'));
        }
    }

    public function deleteFlash() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('EventFlash')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function photo() {
        if (!empty($_POST)) {
            $_SESSION['backend_event_photo_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['backend_event_photo_search']);
        } else {
            unset($_SESSION['backend_event_photo_search']);
        }
        $_POST['photoTitle'] = t(trim($_POST['photoTitle']));
        $_POST['photoTitle'] && $map['title'] = array('like', "%" . $_POST['photoTitle'] . "%");
        $this->assign($_POST);
        $map['eventId'] = $this->eventId;
        $map['uid'] = 0;

        $list = D('EventImg')->where($map)->order('`id` DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function editPhoto() {
        $this->assign('type', 'add');
        if ($id = (int) $_REQUEST['gid']) {
            $this->assign('type', 'edit');
            $photo = D('EventImg')->find($id);
            if (!$photo) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("photo", $photo);
        }
        $this->display();
    }

    public function editMemberImg() {
        $map['id'] = intval($_REQUEST['uid']);
        $map['eventId'] = $this->eventId;
        $user = D('EventUser')->where($map)->find();
        if (!$user) {
            $this->error('成员不存在');
        }
        $this->assign('member', $user);
        $this->assign('type', 'add');
        if ($id = (int) $_REQUEST['gid']) {
            $this->assign('type', 'edit');
            $photo = D('EventImg')->find($id);
            if (!$photo) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("photo", $photo);
        }
        $this->display();
    }

    public function doEditPhoto() {
        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 60) {
            $this->error('标题最大60个字！');
        }
        $id = intval($_REQUEST['gid']);
        if (empty($id)) {
            $this->insertPhoto();
        } else {
            $this->updatePhoto($id);
        }
    }

    /**
     * 活动添加修改成员的信息
     */
    private function _getPhotoData($insert = true) {
        $info = eventUpload();
        if ($info['status']) {
            $data['path'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        } elseif ($insert) {
            $this->error("上传出错! " . $info['info']);
        }
        $data['eventId'] = $this->eventId;
        if (isset($_POST['uid'])) {
            $data['uid'] = intval($_POST['uid']);
        }
        $data['title'] = t($_POST['title']);
        $data['cTime'] = time();
        return $data;
    }

    public function insertPhoto() {
        $data = $this->_getPhotoData();
        if ($res = D('EventImg')->add($data)) {
            if (isset($data['uid'])) {
                $this->assign('jumpUrl', U('/Backend/memberImg', array('id' => $this->eventId, 'uid' => $data['uid'])));
            } else {
                $this->assign('jumpUrl', U('/Backend/photo', array('id' => $this->eventId)));
            }
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    public function updatePhoto($id) {
        $map['id'] = $id;
        $map['eventId'] = $this->eventId;
        $dao = D('EventImg');
        $img = $dao->where($map)->find();
        if (!$img) {
            $this->error('非法参数，图片不存在！');
        }
        $data = $this->_getPhotoData(false);
        if ($dao->where("id={$id}")->save($data)) {
            if (isset($data['uid'])) {
                $this->assign('jumpUrl', U('/Backend/memberImg', array('id' => $this->eventId, 'uid' => $data['uid'])));
            } else {
                $this->assign('jumpUrl', U('/Backend/photo', array('id' => $this->eventId)));
            }
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    public function deletePhoto() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('EventImg')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function multiPhoto() {
        $config = getPhotoConfig();
        $this->assign($config);
        $this->display();
    }

    public function memberMultiPhoto() {
        $map['id'] = intval($_REQUEST['uid']);
        $map['eventId'] = $this->eventId;
        $user = D('EventUser')->where($map)->find();
        if (!$user) {
            $this->error('成员不存在');
        }
        $this->assign('member', $user);
        $config = getPhotoConfig();
        $this->assign($config);
        $this->display();
    }

    //执行单张图片上传
    public function upload_single_pic() {
        $info = eventUpload();
        if ($info['status']) {
            //保存图片信息
            $data['path'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
            $data['eventId'] = $this->eventId;
            if (isset($_REQUEST['uid'])) {
                $data['uid'] = intval($_REQUEST['uid']);
            }
            $data['title'] = t($_REQUEST['title']);
            $data['cTime'] = time();
            if ($res = D('EventImg')->add($data)) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
            //启用session记录flash上传的图片数，也可以防止意外提交。
            //$upload_count	=	intval($_SESSION['upload_count']);
            //$_SESSION['upload_count']	=	$upload_count + 1;
        } else {
            //上传出错
            echo "0";
        }
    }

    //上传后执行编辑操作
    public function mutiEditPhotos() {
        $upnum = intval($_REQUEST['upnum']);
        if ($upnum == 0)
            $this->error('请至少上传一张图片！');
        $jumpUrl = U('/Backend/photo', array('id' => $this->eventId));
        if (isset($_POST['uid'])) {
            $jumpUrl = U('/Backend/memberImg', array('id' => $this->eventId, 'uid' => intval($_POST['uid'])));
        }
        $this->assign('jumpUrl', $jumpUrl);
        $this->success('添加成功');
    }

    public function orga() {
        $orga = D('EventOrga')->where(array('eventId' => $this->eventId))->find();
        if (!$orga) {
            D('eventOrga')->createOrga(array('eventId' => $this->eventId));
            $orga = D('EventOrga')->where(array('eventId' => $this->eventId))->find();
        }
        $this->assign('orga', $orga);
        $this->display();
    }

    public function editOrga() {
        $orga = D('EventOrga')->where(array('eventId' => $this->eventId))->find();
        if (!$orga) {
            D('eventOrga')->createOrga(array('eventId' => $this->eventId));
            $orga = D('EventOrga')->where(array('eventId' => $this->eventId))->find();
        }
        $this->assign('orga', $orga);
        $this->display();
    }

    public function doEditOrga() {
        $title = t($_POST['title']);
        if (empty($title)) {
            $this->error('标题不能为空！');
        }
        if (mb_strlen($title, 'UTF8') > 10) {
            $this->error('标题最大10个字！');
        }
        $map['title'] = $title;
        $map['content'] = t(h($_POST['content']));
        $map['uTime'] = time();
        if (D('EventOrga')->where("eventId={$this->eventId}")->save($map)) {
            $this->assign('jumpUrl', U('/Backend/orga', array('id' => $this->eventId)));
            $this->success('修改成功！');
        } else {
            $this->error('修改失败！');
        }
    }

//    public function qrcode(){
//        $qrcode = $this->event->getQrCode($this->eventId);
//        $this->assign('qrcode', $qrcode);
//        $this->display();
//    }
}
