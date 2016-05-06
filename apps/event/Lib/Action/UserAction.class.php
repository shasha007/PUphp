<?php

/**
 * IndexAction
 * 校方活动教师后台
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class UserAction extends TeacherAction {

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        parent::_initialize();
        if(!$this->rights['can_user']){
            $this->error('您没有权限');
        }
        $editSid = $this->school['id'];
        if($_SESSION['ThinkSNSAdmin'] != '1' && !$this->user['can_admin'] && $this->user['event_level'] > 10 && $this->user['sid1']){
            $editSid = $this->user['sid1'];
        }
        $this->assign('editSid',$editSid);
        $this->assign('roles',  $this->_showRoles());
        $this->assign('canAddUser',$this->_canAddUser());
        $this->assign('canEditUser',$this->_canEditUser());
    }

    public function credit(){
        $num = t($_GET['num']);
        if($num){
            $mapUser['email'] = $num.$this->school['email'];
            $mapUser['sid'] = $this->sid;
            $userInfo = M('user')->where($mapUser)->field('uid,realname,school_event_credit')->find();
            if($userInfo){
                $userInfo['school_event_score'] = X('Score')->getUserScore($userInfo['uid']);
                $this->assign('userInfo',$userInfo);
                $db_prefix = C('DB_PREFIX');
                $map['a.uid'] = $userInfo['uid'];
                $map['a.status'] = array('gt', 0);
                $list = D('EventUser')->table("{$db_prefix}event_user AS a ")
                        ->join("{$db_prefix}event AS b ON a.eventId=b.id")
                        ->where($map)->field('a.cTime,b.fTime,a.credit,a.status,a.score,a.addCredit,b.title,b.school_audit')
                        ->order('b.fTime DESC,a.id DESC')->findPage(20);
                $this->assign($list);
            }
        }
        $this->display();
    }
    public function userGroup(){
        $num = t($_GET['num']);
        if($num){
            $mapUser['email'] = $num.$this->school['email'];
            $userInfo = M('user')->where($mapUser)->field('uid,realname')->find();
            if($userInfo){
                $this->assign('userInfo',$userInfo);
                $db_prefix = C('DB_PREFIX');
                $map['a.uid'] = $userInfo['uid'];
                $map['a.level'] =array('gt',0); ;
                $map['b.is_del'] = 0; ;
                $list = D('GroupMember')->table("{$db_prefix}group_member AS a ")
                        ->join("{$db_prefix}group AS b ON a.gid=b.id")
                        ->where($map)->field('a.cTime,a.level,a.remark,b.name')->findPage(20);
                $this->assign($list);
            }
        }
        $this->display();
    }

        /**
     * index
     * 首页
     * @access public
     * @return void
     */
    public function index() {
        $order = 'uid DESC';
        if($_GET['orderKey'] && $_GET['orderType']){
            $_GET['orderKey'] = t($_GET['orderKey']);
            $_GET['orderType'] = t($_GET['orderType']);
            $order = $_GET['orderKey'].' '.$_GET['orderType'];
            $this->assign('orderKey', $_GET['orderKey']);
            $this->assign('orderType', $_GET['orderType']);
        }
        $res = D('User', 'home')->getUserList($this->_userFilter(), false, false,
                'uid,realname,email,jy_year_note,sid,sid1,year,major,event_level,can_admin,can_prov_news,can_prov_work,can_add_event,can_prov_event'.
                ',can_event,can_event2,can_gift,can_print,can_group,can_credit,school_event_credit,can_available,ctime,admin_level,mobile'
                ,$order,10,true);
        $this->assign($res);
        $this->display();
    }
    //是否可以增加编辑用户
    private function _canAddUser(){
//        if(!in_array($this->mid, array(33654,33655))){
//            return false;
//        }
        return true;
    }
    private function _canEditUser(){
//        if($this->sid==473 && !in_array($this->mid, array(33654,33655))){
//            return false;
//        }
        return true;
    }

    //不同身份要过滤掉，不显示的
    private function _userFilter(){
        $map['sid'] = $this->school['id'];
        if($_SESSION['ThinkSNSAdmin'] != '1'){
            //过滤掉管理员
//            $uids = model('UserGroup')->getUidByUserGroup(1);
//            $uids = array_unique($uids);
//            $map['uid'] = array('not in', $uids);
            switch ($this->user['event_level']) {
                case 13:
                    $map['major'] = $this->user['major'];
                case 12:
                    $map['year'] = $this->user['year'];
                case 11:
                    $map['sid1'] = $this->user['sid1'];
                default:
                    break;
            }
        }
        return $map;
    }

    //添加用户
    public function addUser() {
        if(!$this->get('canAddUser')){
            $this->error('您没有权限！');
        }
    	$this->assign('type', 'add');
        $this->assign('canEditRole', true);
        $this->assign('schoolOrga', D('SchoolOrga')->getAll($this->school['id']));
        $school = model('Schools')->makeLevel0Tree($this->school['id']);
        $this->assign('addSchool', $school);
        //学分认定类别
        $userEcTypes = array();
        if($user['can_credit']){
            $userEcType = M('ec_utype')->where('uid ='.$uid)->field('type')->findAll();
            $userEcTypes = getSubByKey($userEcType,'type');
        }
        $this->assign('userEcTypes',$userEcTypes);
        $this->assign('ecTypes', D('EcType')->getEcType($this->school['id']));
        $this->display('editUser');
    }

    //编辑用户
    public function editUser() {
    	$uid  = intval($_GET['uid']);
    	if ($uid <= 0) $this->error('参数错误');
        $map['sid'] = $this->school['id'];
    	$map['uid']	= $uid;
    	$user = M('user')->where($map)->find();
        if($user['event_level'] != 20)
        {
            $showPassword = 1;
            $this->assign('showPassword',$showPassword);
        }
    	if(!$user) $this->error('无此用户');
        if(!$this->_canEdit($user)) $this->error('您无权修改该用户资料');
        $user['number'] = substr($user['email'], 0, strpos($user['email'], '@'));
        $user['sidName'] = tsGetSchoolName($user['sid1']);
        $gids = $this->_getUserGroupName($uid);
        $this->assign('gids',$gids);
        //社团活动项
        $checkgids = M('event_group')->where('uid ='.$uid)->field('gid')->findAll();
        $checkgids = getSubByKey($checkgids,'gid');
        $this->assign('checkgids',$checkgids);
        //初审人归属组织
        if($user['can_event']){
            $userOrga = M('event_csorga')->where('uid ='.$uid)->field('orga')->findAll();
            $userOrags = getSubByKey($userOrga,'orga');
            $this->assign('userOrags',$userOrags);
        }
        //学分认定类别
        $userEcTypes = array();
        if($user['can_credit']){
            $userEcType = M('ec_utype')->where('uid ='.$uid)->field('type')->findAll();
            $userEcTypes = getSubByKey($userEcType,'type');
        }
        $this->assign('userEcTypes',$userEcTypes);
        $this->assign('ecTypes', D('EcType')->getEcType($this->school['id']));

        $codelimit = M('event_add')->where('uid='.$uid)->getField('codelimit');
        $this->assign('codelimit',$codelimit);;
        $this->assign($user);
        $this->assign('canEditRole', $this->_canEditRole($map['uid']));
        $this->assign('type', 'edit');
        $this->assign('schoolOrga', D('SchoolOrga')->getAll($this->school['id']));
        $school = model('Schools')->makeLevel0Tree($this->school['id']);
        $this->assign('addSchool', $school);
        $this->display();
    }

    //可显示的所有身份
    private function _showRoles(){
        $all = getSchoolEventRoles();
        if ($_SESSION['ThinkSNSAdmin'] != '1' && !$this->user['can_admin']) {
            foreach ($all as $key => $value) {
                if($key <= $this->user['event_level'])
                    unset($all[$key]);
            }
        }
        return $all;
    }

    private function _canEditRole($uid){
        if ($_SESSION['ThinkSNSAdmin'] != '1' && !$this->user['can_admin'] && $uid==$this->mid) {
            return false;
        }
        return true;
    }

    private function _canEdit($user){
        if($_SESSION['ThinkSNSAdmin'] == '1' || $this->user['can_admin'] || $this->mid == $user['uid'] ||
                $this->user['event_level'] < $user['event_level']){
            return true;
        }
        return false;
    }

    public function doAddUser() {
        if(!$this->get('canAddUser')){
            $this->error('您没有权限！');
        }
        //参数合法性检查
        $required_field = array(
            'event_level' => '身份',
            'number' => '学号',
            'password' => '密码',
            'uname' => '昵称',
            'realname' => '姓名',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }
        $data['event_level'] = intval($_POST['event_level']);
        if(!$this->_canEdit(array('uid'=>0, 'event_level'=>$data['event_level'])))
            $this->error('您无权添加该身份！');
        $data['sid1'] = intval($_POST['sid1']);
        $data['year'] = t($_POST['year']);
        $data['major'] = t($_POST['major']);
        //院系，年级，专业
        switch ($data['event_level']) {
            //case 20:
            case 13:
                if(empty($data['major']))
                    $this->error('专业领导，专业不能为空');
            case 12:
                if(empty($data['year']))
                    $this->error('年级领导，年级不能为空');
            case 11:
                if(empty($data['sid1']))
                    $this->error('院系领导，请选择院系');
            default:
                break;
        }
        if (strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16) {
            $this->error('密码必须为6-16位');
        }
        $data['email'] = t($_POST['number']).$this->school['email'];
        if (!isEmailAvailable($data['email'])) {
            $this->error('学号已经被使用，请重新输入');
        }
        //全局管理观察账号不可使用
//        if($this->school['email'] != '@test.com'){
//            $adminEmail = t($_POST['number']).'@test.com';
//            if (!isEmailAvailable($adminEmail)) {
//                $this->error('学号已经被使用，请重新输入');
//            }
//        }
        $data['uname'] = escape(h(t($_POST['uname'])));
        $data['realname'] = escape(h(t($_POST['realname'])));
        $unameError = getUnameError($data['uname'], $_POST['uid']);
        if($unameError){
            $this->error($unameError);
        }
        $realnameError = getRealnameError($data['realname'],20);
        if($realnameError){
            $this->error($realnameError);
        }
        if($_POST['can_event'] && !$_POST['orga']){
            $this->error('请选择初级审核人归属组织');
        }

        //注册用户
        $data['password'] = codePass($_POST['password']);
        $data['ctime'] = time();
        $data['is_active'] = 1;
        $data['sex'] = intval($_POST['sex']);
        $data['sid'] = $this->school['id'];
        $data['is_init'] = '0';
        $data['is_valid'] = '1';
        if(isset($_POST['event_role_info'])){
            $data['event_role_info'] = t($_POST['event_role_info']);
        }
        if($data['sid'] == 659){
            $data['is_init'] = '1';
            $data['mobile'] = '123456';
        }
        //权限
        if($_SESSION['ThinkSNSAdmin'] == '1' || $this->user['can_admin']){
            $data['can_add_event'] = ($_POST['can_add_event'] == 1) ? 1 : 0;
            $data['can_prov_event'] = ($_POST['can_prov_event'] == 1) ? 1 : 0;
            $data['can_prov_news'] = ($_POST['can_prov_news'] == 1) ? 1 : 0;
            $data['can_prov_work'] = ($_POST['can_prov_work'] == 1) ? 1 : 0;
            $data['can_event'] = ($_POST['can_event'] == 1) ? 1 : 0;
            $data['can_event2'] = ($_POST['can_event2'] == 1) ? 1 : 0;
            $data['can_gift'] = ($_POST['can_gift'] == 1) ? 1 : 0;
            $data['can_print'] = ($_POST['can_print'] == 1) ? 1 : 0;
            $data['can_group'] = ($_POST['can_group'] == 1) ? 1 : 0;
            $data['can_announce'] = ($_POST['can_announce'] == 1) ? 1 : 0;
            $data['can_credit'] = ($_POST['can_credit'] == 1) ? 1 : 0;
            if ($_SESSION['ThinkSNSAdmin'] == '1') {
                $data['can_admin'] = ($_POST['can_admin'] == 1) ? 1 : 0;
            }
        }elseif($this->user['can_event']){
            $data['can_add_event'] = ($_POST['can_add_event'] == 1) ? 1 : 0;
        }
        $uid = M('user')->add($data);
        if (!$uid) {
            $this->error('抱歉：注册失败，请稍后重试');
            exit;
        }

        //初审人多院系
        if($data['can_event']){
            $adata = array();
            $adata['uid'] = $uid;
            foreach ($_POST['orga'] as $v) {
                $adata['orga'] = t($v);
                M('event_csorga')->add($adata);
            }
        }
        //初审人多院系
        if($data['can_credit'] && !empty($_POST['ecTypes'])){
            D('EcUtype')->changeUType($uid,$_POST['ecTypes'],$this->sid,$data['realname']);
        }

        //菁英班微博关注超管
        if(isTuanRole($this->school['id']) && !$data['can_admin']){
            $adminUid = 96510;
            $jyGid = 103;
            //加关注
            $daoFollow = D('weibo_follow');
            $fdata['uid'] = $adminUid;
            $fdata['fid'] = $uid;
            $fdata['group_id'] = $jyGid;
            $fdata['ctime'] = time();
            $followId = $daoFollow->add($fdata);
            if($followId){
                //互相关注
                $backRow['uid'] = $uid;
                $backRow['fid'] = $adminUid;
                $daoFollow->add($backRow);
                $daoUserCount = Model('UserCount');
                $daoUserCount->addCount($uid,'following');
                $daoUserCount->addCount($uid,'follower');
                $daoUserCount->addCount($adminUid,'following');
                $daoUserCount->addCount($adminUid,'follower');
            }
        }

           //发起人能给出活动签到码授权的人数
         if ($_POST['can_add_event'] && intval($_POST['codelimit']) > 0) {
            $daoAdd = M('event_add');
            $where['uid'] = $uid;
            $where['codelimit'] = intval($_POST['codelimit']);
            $daoAdd->add($where);
        }

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '1';
        $logData[] = '用户 - 用户管理 ';
        $logData[] = $data;
        $_LOG['data'] = serialize($logData);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);
        $this->success('注册成功');
    }

    public function doEditUser() {
        //dump($_POST);die;
        if(!$this->get('canEditUser')){
            $this->error('您没有权限！');
        }
        //参数合法性检查
        //var_dump($_POST);die;
        $uid = intval($_POST['uid']);
        $required_field = array(
            'uid' => '指定用户',
            'number' => '学号',
            'uname' => '昵称',
            'realname' => '姓名',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }
        $data['event_level'] = $this->user['event_level'];
        if($this->_canEditRole($uid)){
            if (empty($_POST['event_level'])){
                $this->error('身份不可为空');
            }
            $data['event_level'] = intval($_POST['event_level']);
            if(!$this->_canEdit(array('uid'=>$uid, 'event_level'=>$data['event_level'])))
                $this->error('您无权添加该身份！');
        }
        $data['sid1'] = intval($_POST['sid1']);
        $data['year'] = t($_POST['year']);
        $data['major'] = t($_POST['major']);
        //院系，年级，专业
        switch ($data['event_level']) {
            //case 20:
            case 13:
                if(empty($data['major']))
                    $this->error('专业领导，专业不能为空');
            case 12:
                if(empty($data['year']))
                    $this->error('年级领导，年级不能为空');
            case 11:
                if(empty($data['sid1']))
                    $this->error('院系领导，请选择院系');
            default:
                break;
        }
        $_POST['email'] = t($_POST['number']).$this->school['email'];
        if (!isEmailAvailable($_POST['email'], $uid)) {
            $this->error('学号已经被使用，请重新输入');
        }
        //全局管理观察账号不可使用
//        if($this->school['email'] != '@test.com'){
//            $adminEmail = t($_POST['number']).'@test.com';
//            if (!isEmailAvailable($adminEmail)) {
//                $this->error('学号已经被使用，请重新输入');
//            }
//        }
//        if (!empty($_POST['password']) && strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16) {
//            $this->error('密码必须为6-16位');
//        }
        $unameError = getUnameError($_POST['uname'], $uid,20);
        if($unameError){
            $this->error($unameError);
        }
        if($_POST['can_event'] && !$_POST['orga']){
            $this->error('请选择初审核人归属组织不可为空');
        }

        //保存修改
        $realname = escape(h(t($_POST['realname'])));
        $key = array('event_level', 'sid', 'email', 'uname', 'realname', 'sex', 'sid1','year','major','mobile','event_role_info');
        $value = array($data['event_level'], $this->school['id'], t($_POST['number']).$this->school['email'], escape(h(t($_POST['uname']))),$realname,
            intval($_POST['sex']), $data['sid1'], $data['year'], $data['major'],$_POST['mobile'], t($_POST['event_role_info']));

        //权限
        if ($_SESSION['ThinkSNSAdmin'] == '1' || $this->user['can_admin']) {
            $key[] = 'can_add_event';
            $value[] = ($_POST['can_add_event'] == 1) ? 1 : 0;
            //菁英人才专有
            if( isTuanRole($this->sid) || $this->sid==473){
                $key[] = 'can_prov_event';
                $value[] = ($_POST['can_prov_event'] == 1) ? 1 : 0;
                $key[] = 'can_prov_news';
                $value[] = ($_POST['can_prov_news'] == 1) ? 1 : 0;
                $key[] = 'can_prov_work';
                $value[] = ($_POST['can_prov_work'] == 1) ? 1 : 0;
            }
            //初审
            $key[] = 'can_event';
            $can_event = ($_POST['can_event'] == 1) ? 1 : 0;
            $value[] = $can_event;
            M('event_csorga')->where('uid='.$uid)->delete();
            if($can_event){
                $data['uid'] = $uid;
                foreach ($_POST['orga'] as $v) {
                    $data['orga'] = t($v);
                    M('event_csorga')->add($data);
                }
            }
            //终审
            $key[] = 'can_event2';
            $value[] = ($_POST['can_event2'] == 1) ? 1 : 0;
            $key[] = 'can_gift';
            $value[] = ($_POST['can_gift'] == 1) ? 1 : 0;
            $key[] = 'can_print';
            $value[] = ($_POST['can_print'] == 1) ? 1 : 0;
            $key[] = 'can_group';
            $value[] = ($_POST['can_group'] == 1) ? 1 : 0;
            $key[] = 'can_announce';
            $value[] = ($_POST['can_announce'] == 1) ? 1 : 0;
            $key[] = 'can_available';
            $value[] = ($_POST['can_available'] == 1) ? 1 : 0;
            $key[] = 'can_credit';
            $can_credit = ($_POST['can_credit'] == 1) ? 1 : 0;
            $value[] = $can_credit;
            M('ec_utype')->where('uid='.$uid)->delete();
            if($can_credit){
                D('EcUtype')->changeUType($uid,$_POST['ecTypes'],$this->sid,$realname);
            }
            if ($_SESSION['ThinkSNSAdmin'] == '1') {
                $key[] = 'can_admin';
                $value[] = ($_POST['can_admin'] == 1) ? 1 : 0;
            }

        // 初级审核者 可以设定 活动发起人
        }elseif($this->user['can_event']){
            $key[] = 'can_add_event';
            $value[] = ($_POST['can_add_event'] == 1) ? 1 : 0;
        }
        $map['uid'] = $uid;
if (!empty($_POST['password'])) {
    $key[] = 'password';
    $value[] = codePass($_POST['password']);
}
$_LOG['uid'] = $this->mid;
$_LOG['type'] = '3';
$data[] = $this->school['title'].' - 用户管理 ';
$data[] = M('user')->where($map)->field('uid,event_level,sid1,year,major,email,password,uname,realname,sex')->find();
if ($_POST['__hash__'])
    unset($_POST['__hash__']);
$data[] = $_POST;
$_LOG['data'] = serialize($data);
$_LOG['ctime'] = time();
M('AdminLog')->add($_LOG);


        $res = M('user')->where($map)->setField($key, $value);

S('S_userInfo_'.$uid, null);
if($uid == $this->mid){
    $_SESSION['userInfo'] = D('User', 'home')->getUserByIdentifier($this->mid);
}
//添加社团发起活动权限
    if(in_array('can_add_event', $key)){
        $daogroup = M('event_group');
        $result = $daogroup->where('uid=' . $uid)->delete();
        if ($_POST['can_add_event'] && $_POST['addGroupEvent']) {
            foreach ($_POST['addGroupEvent'] as $v) {
                $data['gid'] = intval($v);
                $data['uid'] = intval($uid);
                $res = $daogroup->add($data);
            }
        }
    }
   //发起人能给出活动签到码授权的人数
        if ($_POST['can_add_event'] && intval($_POST['codelimit']) > 0) {
            $daoAdd = M('event_add');
            $where['uid'] = $uid;
            $result = $daoAdd->where($where)->delete();
            $where['codelimit'] = intval($_POST['codelimit']);
            $daoAdd->add($where);
        }
        $this->assign('jumpUrl', U('event/User/index'));
        $this->success('保存成功');
    }

    //搜索用户
    public function doSearchUser() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['es_searchUser'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_searchUser']);
        } else {
            unset($_SESSION['es_searchUser']);
        }
        //组装搜索条件
        $map = $this->_userFilter();
        $fields = array('uid');
        foreach ($fields as $v)
            if (isset($_POST[$v]) && $_POST[$v] != '')
                $map[$v] = array('in', explode(',', $_POST[$v]));

        $number = t($_POST['number']);
        if($number!=''){
            $map['email'] = $number.$this->school['email'];
        }
        //姓名时，模糊查询
        $_POST['realname'] = t($_POST['realname']);
        if (isset($_POST['realname']) && $_POST['realname'] != '') {
            $map['realname'] = array('exp', 'LIKE "' . $_POST['realname'] . '%"');
        }
        $sid1 = intval($_POST['sid1']);
        if($sid1){
            $map['sid1'] = $sid1;
        }
        if ($_POST['event_level']) {
            $map['event_level'] = $_POST['event_level'];
        }
        if ($_POST['year']) {
            $map['year'] = $_POST['year'];
        }
        if ($_POST['is_init']) {
            $map['is_init'] = $_POST['is_init']-1;
        }

        //权限
        if ($_POST['can_add_event'] == 1) {
            $map['can_add_event'] = 1;
        }
        if ($_POST['can_prov_event'] == 1) {
            $map['can_prov_event'] = 1;
        }
        if ($_POST['can_prov_news'] == 1) {
            $map['can_prov_news'] = 1;
        }
        if ($_POST['can_prov_work'] == 1) {
            $map['can_prov_work'] = 1;
        }
        if ($_POST['can_event'] == 1) {
            $map['can_event'] = 1;
        }
        if ($_POST['can_event2'] == 1) {
            $map['can_event2'] = 1;
        }
        if ($_POST['can_gift'] == 1) {
            $map['can_gift'] = 1;
        }
        if ($_POST['can_print'] == 1) {
            $map['can_print'] = 1;
        }
        if ($_POST['can_group'] == 1) {
            $map['can_group'] = 1;
        }
        if ($_POST['can_available'] == 1) {
            $map['can_available'] = 1;
        }
        if ($_POST['can_credit'] == 1) {
            $map['can_credit'] = 1;
        }
        $res = D('User', 'home')->getUserList($map, false, false,'*','uid DESC',10,true);
        $this->assign($res);

        $this->assign('type', 'searchUser');
        $this->assign(array_map('t', $_POST));
        $this->assign('tree', model('Schools')->_makeTree(0));
        $this->display('index');
    }

    //删除用户
    public function doDeleteUser() {
        echo 0; return ;
    	$_POST['uid'] = t($_POST['uid']);
    	$_POST['uid'] = explode(',', $_POST['uid']);

    	$_LOG['uid'] = $this->mid;
		$_LOG['type'] = '2';
		$data[] = '用户 - 用户管理 ';
		$map['uid'] = array('in',$_POST['uid']);
		$data[] = M('user')->where($map)->findall();
		$_LOG['data'] = serialize($data);
		$_LOG['ctime'] = time();
		M('AdminLog')->add($_LOG);

    	//ts_user
    	$res = D('User', 'home')->deleteUser($_POST['uid']);
    	if($res) {echo 1;		  }
    	else 	 {echo 0; return ;}
    }

    public function editYearNote(){
        //到课考勤5分，纪律考核5分，学习笔记10分，社会体验10分，团队活动10分，学习总结与规划5分。
        //政治生活锻炼10分，挂职实践10分，村官结对10分，课题调研10分，志愿服务10分，实践总结与党性分析报告5分
        if(!$this->rights['can_admin']){
            $this->error('无权查看');
        }
        $uid = t($_REQUEST['uid']);
        $pf = M('school_year_note')->where('uid='.$uid)->find();
        if($pf){
            $note = unserialize($pf['note']);
            $pf['note'] = explode(',', $note);
        }
        $this->assign('notes', $pf['note']);
        $this->assign('list', getJyPf());
        $this->display();
    }

    public function doEditYearNote() {
        if(!$this->rights['can_admin']){
            $this->error('无权查看');
        }
        $uid = t($_REQUEST['uid']);
        $input = explode(',', $_REQUEST['row']);
        $list = getJyPf();
        $sum = 0;
        for ($i = 0; $i < 12; $i++) {
            if ($input[$i] > $list[$i][1]) {
                $this->error($list[$i][0] . ' 输入错误');
            }
            $sum += $input[$i];
        }
        $data['note'] = serialize($_REQUEST['row']);
        $dao = M('school_year_note');
        $record = $dao->where('uid='.$uid)->find();
        $_LOG['uid'] = $this->mid;
        $logData[] = '用户 - 学期总评分';
        if($record){
            $res = $dao->where('uid='.$uid)->save($data);
            $_LOG['type'] = '3';
            $logData[] = $record;
            $logData[] = $data;
        }else{
            $data['uid'] = $uid;
            $res = $dao->add($data);
            $_LOG['type'] = '1';
            $logData[] = $data;
        }
        if($res && M('user')->where('uid='.$uid)->setField('jy_year_note', $sum)){
            $_LOG['data'] = serialize($logData);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
            $this->success($sum);
        }
        $this->error('操作失败');
    }
    //得到用户参加的为管理员的社团名称
    private function _getUserGroupName($uid){
        $map['uid'] = $uid;
        $map['level'] = array('in','1,2');
        $gids = M('group_member')->where($map)->field('gid')->findAll();
        if ($gids) {
            $daogroup = M('group');
            foreach ($gids as $k => $v) {
                $name = $daogroup
                        ->where('is_del=0 AND disband=0 AND  id=' . $v['gid'] . ' AND school =' . $this->school['id'])
                        ->getField('name');
                if (!$name)
                    continue;
                $arr[$k]['name'] = $name;
                $arr[$k]['id'] = $v['gid'];
            }
        }
        return $arr;
    }
    public function excel() {
        set_time_limit(0);
        if (!intval($_GET['p'])) {
            $page = 1;
        } else {
            $page = intval($_GET['p']);
        }
        $limit = 5000;
        $offset = ($page - 1) * $limit;
        $map['sid'] = $this->sid;
        $year = array('11','12','13','14');
        $map['year'] = array('in', $year);
        $map['is_init'] = 0;
//        $map['mobile'] =array('exp',"!=''");
        $list = M('user')
                ->where($map)
                ->field('realname,email,uid,year,major,sex,mobile')
                ->limit("$offset,$limit")
                ->select();
        foreach ($list as $k => $v) {
            $list[$k]['uid'] = tsGetSchoolByUid($v['uid']);
            $list[$k]['email'] = getUserEmailNum($v['email']);
            $list[$k]['sex'] = $v['sex'] == 1 ? '男' : '女';
        }
        $arr = array('姓名', '学号', '学校', '年级', '专业', '性别','电话');
        array_unshift($list, $arr);
        $title = M('school')->getField('title','id='.$this->sid);
        service('Excel')->export2($list,$title);
    }
    //积分 学分导出
    public function scoreExcel() {
        set_time_limit(0);
        if (!intval($_GET['p'])) {
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo('请地址栏输入第几页，格式 &p=1 注意p小写');die;
        } else {
            $page = intval($_GET['p']);
        }
        if($page<=0){
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo('请地址栏输入第几页，格式 &p=1 注意p小写');die;
        }
        $year = t($_GET['y']);
        if(!$year){
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo('请地址栏输入年级，格式 &y=14 或多个年级 &y=12,13,14');die;
        }
        $limit = 5000;
        $offset = ($page - 1) * $limit;
        $map['sid'] = $this->sid;
//        $year = array('11','12','13','14');
        $map['year'] = array('in', $year);
//        $map['is_init'] = 0;
//        $map['mobile'] =array('exp',"!=''");
        $list = M('user')
                ->where($map)
                ->field('uid,realname,email,sid1,year,major,sex,mobile,school_event_credit,sex as school_event_score')
                ->limit("$offset,$limit")
                ->findAll();
        if(empty($list)){
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo('未找到记录');die;
        }
        foreach ($list as $k => $v) {
            $uid = $v['uid'];
            unset($list[$k]['uid']);
            $list[$k]['sid1'] = tsGetSchoolName($v['sid1']);
            $list[$k]['email'] = "'".getUserEmailNum($v['email']);
            $list[$k]['sex'] = $v['sex'] == 1 ? '男' : '女';
            $list[$k]['school_event_score'] = X('Score')->getUserScore($uid);
        }
        $arr = array('姓名', '学号', '院系', '年级', '专业', '性别', '电话', '学分', '积分');
        array_unshift($list, $arr);
        $title = tsGetSchoolName($this->sid);
        service('Excel')->export2($list, $title);
    }


    //团市委用户数据
  public function tuanwei() {
        set_time_limit(0);
        $daoSchool = M('school');
        $daoUser = M('user');
        $list = array();
        $tuan = $daoSchool->where('cityId =14')->field('id,title')->findAll();
        $i =0;
        foreach ($tuan as $v) {
            $coll = $daoSchool->where('pid =' . $v['id'])->field('id,title')->findAll();
            foreach ($coll as  $val) {
                $list[$i]['school'] = $v['title'];
                $list[$i]['coll'] = $val['title'];
                $list[$i]['all'] = $daoUser->where('sid1='.$val['id'])->count();
                $list[$i]['init'] = $daoUser->where('is_init=1 AND  sid1='.$val['id'])->count();
                $i++;
            }
        }
          closeDb();
        $arr = array('学校', '院系', '用户总数', '激活数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'团市委');
    }


    public function schoolE() {
        set_time_limit(0);
        $dao = M('user');
        $school = $dao
                ->field('sid')
                ->group('sid')
                ->findAll();
        $daoschool = M('school');
        $argo = M('school_orga');
        foreach ($school as $k => $v) {
            if ($v['sid'] < 0) {
                $school[$k]['title'] = $argo->getField('title', 'id =' . $v['sid']);
            } else {
                $school[$k]['title'] = $daoschool->getField('title', 'id =' . $v['sid']);
            }
        }

        $school = orderArray($school, 'sid');
        $row = array();
        $year = array('10', '11', '12', '13','14');
        foreach ($school as $k => $v) {
            $row[$k]['school'] = $school[$v['sid']]['title'];
            $map['sid'] = $v['sid'];
            $maps['sid'] = $v['sid'];
            foreach ($year as $key => $val) {
                $map['year'] = $val;
                $row[$k][$val] = $dao->where($map)->count();
            }
            foreach ($year as $key => $val) {
                $maps['year'] = $val;
                $maps['is_init'] = 1;
                $row[$k]['init' . $val] = $dao->where($maps)->count();
            }
        }
//        var_dump($row);
//        die;
        closeDb();
        $arr = array('学校', '10级', '11级', '12级', '13级', '14级', '10级', '11级', '12级', '13级' ,'14级');
        array_unshift($row, $arr);
        service('Excel')->export2($row, '用户');
    }

    public function getData() {
        set_time_limit(0);
        $part = M('school')->where('pid=' . $this->sid)->field('id,title')->findAll();
        $title =  M('school')->getField('title','id='.$this->sid);
        $dao = M('user');
        $daoEvent = M('event');
        foreach ($part as $k => $v) {
            $list[$k]['school'] = $v['title'];
            $list[$k]['people'] = $dao->where('year IN (11,12,13)  AND sid1=' . $v['id'])->count();
            $list[$k]['valid'] = $dao->where(' year IN (11,12,13)  AND is_init=1 AND sid1=' . $v['id'])->count();
            $list[$k]['event'] = $daoEvent->where('isDel=0 AND sid =' . $v['id'])->count();
            $list[$k]['wancount'] =  $daoEvent->where('isDel=0 AND school_audit=5 AND sid='.$v['id'])->count();
        }
        closeDb();
        $arr = array('学院', '人数', '初始化人数', '活动数','完结活动数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);
    }


    //统计各个学校用户数
        public function getSchoolUser() {
        set_time_limit(0);
        $Daoschool = M('school');
        $school = $Daoschool->where('pid=0')->field('title,id')->findAll();
        $dao = M('user');
        foreach ($school as $k => $v) {
            $list[$k]['school'] = $v['title'];
            $list[$k]['people'] = $dao->where(' event_level=20 AND sid=' . $v['id'])->count();
            $list[$k]['valid'] = $dao->where('event_level=20 AND is_init=1 AND sid=' . $v['id'])->count();
        }
        closeDb();
        $arr = array('学院', '人数', '初始化人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '各个学校用户数');
    }

    public function getEvent() {
        set_time_limit(0);
        if(!intval($_GET['p'])){
            $page = 1;
        }else{
           $page = intval($_GET['p']);
        }
        $limit = 1000;
         $offset = ($page - 1) * $limit;
        $daoEvent = M('event');
        $list = $daoEvent
                        ->where('isDel=0 AND is_school_event=' . $this->sid)
                        ->field('sid,title,joinCount,id')
                        ->order('sid')->limit("$offset,$limit")->select();
          $daoUser = M('event_user');
          $daoSchool= M('school');
          $ogra= M('school_orga')->where('sid='.$this->sid)->field('id,title')->findAll();
          $orga = orderArray($ogra,'id');

            $title =  $daoSchool->getField('title','id='.$this->sid);
          foreach ($list as $k => $v) {
            $list[$k]['id'] = $daoUser->where('status=2 AND eventId='.$v['id'])->count();
            if($v['sid']<0){
                $key = 0-$v['sid'];
            $list[$k]['sid'] =  $orga[$key]['title'];

            }else{

            $list[$k]['sid'] =  $daoSchool->getField('title','id='.$v['sid']);
            }
        }
        closeDb();
        $arr = array('学院', '活动标题', '参加人数','签到人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title.'活动统计');

    }


    public function getEventDetail() {
        set_time_limit(0);
        if (!intval($_GET['p'])) {
            $page = 1;
        } else {
            $page = intval($_GET['p']);
        }
        $limit = 1000;
        $offset = ($page - 1) * $limit;
        $daoEvent = M('event');
        $list = $daoEvent
                        ->where('isDel=0 AND is_school_event=' . $this->sid)
                        ->field('sid,title,joinCount,id,address,typeId,uid,school_audit,sTime,eTime')
                        ->order('sid')->limit("$offset,$limit")->select();
        $daoUser = M('event_user');
        $daoSchool = M('school');
        $User = M('user');
        $ogra = M('school_orga')->where('sid=' . $this->sid)->field('id,title')->findAll();
        $orga = orderArray($ogra, 'id');
        $type = M('event_type')->findAll();
        $type = orderArray($type, 'id');

        $title = $daoSchool->getField('title', 'id=' . $this->sid);
        foreach ($list as $k => $v) {
            $list[$k]['id'] = $daoUser->where('status=2 AND eventId=' . $v['id'])->count();
            $list[$k]['typeId'] = $type[$v['typeId']]['name'];
            $list[$k]['year'] = $User->getField('year', 'uid =' . $v['uid']);
            $list[$k]['uid'] = getUserName($v['uid']);
            $list[$k]['sTime'] = date('Y-m-d h;i', $v['sTime']);
            $list[$k]['eTime'] = date('Y-m-d h;i', $v['eTime']);
            switch ($v['school_audit']) {
                case 0;
                    $list[$k]['school_audit'] = '等待初审';
                    break;
                case 1;
                    $list[$k]['school_audit'] = '等待终审';
                    break;
                case 2;
                    if (time() < $v['sTime']) {

                        $list[$k]['school_audit'] = '未开始';
                    } else if (time() <= $v['eTime']) {
                        $list[$k]['school_audit'] = '进行中';
                    } else {
                        $list[$k]['school_audit'] = '已结束';
                    }
                    break;
                case 3;
                    $list[$k]['school_audit'] = '完结审核';
                    break;
                case 4;
                    $list[$k]['school_audit'] = '完结驳回';
                    break;
                case 5;
                    $list[$k]['school_audit'] = '等待通过';
                    break;
                case 6;
                    $list[$k]['school_audit'] = '活动驳回';
                    break;
            }

            if ($v['sid'] < 0) {
                $key = 0 - $v['sid'];
                $list[$k]['sid'] = $orga[$key]['title'];
            } else {

                $list[$k]['sid'] = $daoSchool->getField('title', 'id=' . $v['sid']);
            }
        }
        closeDb();
        $arr = array('学院', '活动标题', '参加人数', '签到人数', '活动地址', '类型', '发起人', '状态', '开始时间', '结束时间', '级别');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title . '活动统计明细');
    }


    public function erweima(){
//        $year = intval($_GET['year']);
//        if($year<10){
//            echo '输入大于09的年级';
//        }
        $map['sid'] = $this->sid;
        $map['year'] = array('in','14');
        $map['event_level'] = 20;
        $list = M('user')->field('uid,email,sid1,realname,major')->where($map)->order('uid asc')->findPage(140);
        $this->assign($list);
        $this->display();
    }


    //所有活动统计
    public function getAllEvent() {
        set_time_limit(0);
        if (!intval($_GET['p'])) {
            $page = 1;
        } else {
            $page = intval($_GET['p']);
        }
        $limit = 5000;
        $offset = ($page - 1) * $limit;
        $daoEvent = M('event');
        $list = $daoEvent
                        ->where('isDel=0 ')
                        ->field('is_school_event,title,joinCount,id,typeId,gid,sTime,eTime,is_prov_event')
                        ->order('sid')->limit("$offset,$limit")->select();
        $daoUser = M('event_user');
        $daoSchool = M('school');
        $User = M('user');
        $ogra = M('school_orga')->field('id,title')->findAll();
        $orga = orderArray($ogra, 'id');
        $type = M('event_type')->findAll();
        $type = orderArray($type, 'id');
        $group = M('group');
        foreach ($list as $k => $v) {


            $list[$k]['typeId'] = $type[$v['typeId']]['name'];
            $list[$k]['gid'] = $v['gid'] ? $group->getField('name', 'id=' . $v['gid']) : '';
            $list[$k]['sTime'] = date('Y-m-d h;i', $v['sTime']);
            $list[$k]['eTime'] = date('Y-m-d h;i', $v['eTime']);
            if($v['is_prov_event']==1){
                $list[$k]['is_prov_event'] = '全省';
            }elseif ($v['is_prov_event']==2) {
                $list[$k]['is_prov_event'] = 'PU';
            }else{
                $list[$k]['is_prov_event'] = '学校';
            }
            if ($v['is_school_event'] < 0) {
                $key = 0 - $v['is_school_event'];
                $list[$k]['is_school_event'] = $orga[$key]['title'];
            } else {
                $list[$k]['is_school_event'] = $daoSchool->getField('title', 'id=' . $v['is_school_event']);
            }


            $list[$k]['id'] = $daoUser->where('status=2 AND eventId=' . $v['id'])->count();
            $uids = $daoUser->where(' status=2 AND eventId=' . $v['id'])->field('uid')->findAll();
            $list[$k]['10y'] = 0;
            $list[$k]['11y'] = 0;
            $list[$k]['12y'] = 0;
            $list[$k]['13y'] = 0;
            foreach ($uids as $v) {
                $year = $User->getField('year', 'uid=' . $v['uid']);
                switch ($year) {
                    case'10':
                        $list[$k]['10y'] = $list[$k]['10y'] + 1;
                        break;
                    case'11':
                        $list[$k]['11y'] = $list[$k]['11y'] + 1;
                        break;
                    case'12':
                        $list[$k]['12y'] = $list[$k]['12y'] + 1;
                        break;
                    case'13':
                        $list[$k]['13y'] = $list[$k]['13y'] + 1;
                        break;
                }
            }
        }
//        var_dump($list);
//        die;
        closeDb();
        $arr = array('学校', '活动标题', '参加人数', '签到人数', '类型', '发起组织', '开始时间', '结束时间', '规模', '10级签到数', '11级签到数', '12级签到数', '13级签到数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title . '活动统计明细');
    }

    //按月份统计活动
    public function getEventByMonth() {
        $date1 = '2013-09-01';
        $date2 = '2014-09-16';

        $date1 = strtotime($date1);
        $date2 = strtotime($date2);
//       echo  date('y-m-d',$date1);die;
        $datex = $date1;
        $daoEvent = M('event');
         $map['isDel'] = 0;
        while ($datex < $date2) {
            $start = $datex;
            $datex = strtotime('+1 month', $datex);
            $map['eTime'] = array('between',array($start,$datex));
                    echo date('y-m',$start)."月份".'------';
            echo $daoEvent->where($map)->count().'</br>';
        }
    }

    //各学校活动活动明细
    public function schoolEvent() {
        $school = M('school')->where('pid=0')->field('title,id')->findAll();
        $group = M('group');
        $daoEvent = M('event');
        $daoUser = M('event_user');
        $list = array();
         $Daoschool =  M('school');
        foreach ($school as $k => $v) {
              $list[$k]['school'] =$Daoschool->getField('title','id='.$v['id']);

            $list[$k]['bumen'] = $group->where('school=' . $v['id'] . ' AND category=1 ')->count();
            $list[$k]['zhibu'] = $group->where('school=' . $v['id'] . ' AND category=2 ')->count();
            $list[$k]['shetu'] = $group->where('school= ' . $v['id'] . '  AND category=3 ')->count();
            $bumengids = $group->where('school=' . $v['id'] . ' AND category=1 ')->field('id')->findAll();
            $bumengids = getSubByKey($bumengids, 'id');
            $map['gid'] = array('IN', $bumengids);
            $list[$k]['bumenEvent'] = $daoEvent->where($map)->count() ? $daoEvent->where($map)->count() : 0 ;
            //部门活动报名人数
            $eventId = $daoEvent->where($map)->field('id')->findAll();
            if ($eventId) {
                $allNum = 0;
                foreach ($eventId as $val) {
                    $num = $daoUser->where('eventId =' . $val['id'])->count();
                    $allNum = $allNum + $num;
                }
                $list[$k]['bumenUser'] = $allNum;
                //部门活动签到人数
                $allNum = 0;
                foreach ($eventId as $val) {
                    $num = $daoUser->where(' status =2 AND eventId =' . $val['id'])->count();
                    $allNum = $allNum + $num;
                }
                $list[$k]['bumenDao'] = $allNum;
            } else {
                $list[$k]['bumenUser'] = 0;
                $list[$k]['bumenDao'] = 0;
            }
            //`----------------------------------------

            $zhibugids = $group->where('school=' . $v['id'] . ' AND category=2 ')->field('id')->findAll();
            $zhibugids = getSubByKey($zhibugids, 'id');
            $map['gid'] = array('IN', $zhibugids);
//            var_dump($group->getLastSql());die;
            $list[$k]['zhibuEvent'] = $daoEvent->where($map)->count() ? $daoEvent->where($map)->count() : 0 ;

            //团支部活动报名人数
            $eventId = $daoEvent->where($map)->field('id')->findAll();
            if ($eventId) {
                $allNum = 0;
                foreach ($eventId as $val) {
                    $num = $daoUser->where('eventId =' . $val['id'])->count();
                    $allNum = $allNum + $num;
                }
                $list[$k]['zhibuUser'] = $allNum;
                //团支部活动签到人数
                $allNum = 0;
                foreach ($eventId as $val) {
                    $num = $daoUser->where(' status =2 AND eventId =' . $val['id'])->count();
                    $allNum = $allNum + $num;
                }
                $list[$k]['zhibuDao'] = $allNum;
            } else {
                $list[$k]['zhibuUser'] = 0;
                $list[$k]['zhibuDao'] = 0;
            }
//`----------------------------------------
            $shetugids = $group->where('school=' . $v['id'] . ' AND category=3 ')->field('id')->findAll();
            $shetugids = getSubByKey($shetugids, 'id');
            $map['gid'] = array('IN', $shetugids);
            $list[$k]['shetuEvent'] = $daoEvent->where($map)->count() ? $daoEvent->where($map)->count() : 0 ;
            //社团活动报名人数
            $eventId = $daoEvent->where($map)->field('id')->findAll();
            if ($eventId) {
                $allNum = 0;
                foreach ($eventId as $val) {
                    $num = $daoUser->where('eventId =' . $val['id'])->count();
                    $allNum = $allNum + $num;
                }
                $list[$k]['shetuUser'] = $allNum;
                //社团活动签到人数
                $allNum = 0;
                foreach ($eventId as $val) {
                    $num = $daoUser->where(' status =2 AND eventId =' . $val['id'])->count();
                    $allNum = $allNum + $num;
                }
                $list[$k]['shetuDao'] = $allNum;
            } else {
                $list[$k]['shetuUser'] = 0;
                $list[$k]['shetuDao'] = 0;
            }
        }
        closeDb();
        $arr = array('学校', '学生部门数', '团支部数','学生社团数',  '部门活动总数', '部门活动报名总数', '部门活动签到总数', '团支部活动总数', '团支部活动报名总数', '团支部活动签到总数', '社团活动总数', '社团活动报名总人数', '社团活动签到总人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title . '学校活动明细');

    }

    public function phpword() {
        header("Cache-Control: no-cache, must-revalidate");
        header("Pragma: no-cache");
        $content = M('donate_product_opt')->getField('content', 'product_id = 22');
        $content = htmlspecialchars_decode($content);
        $fileContent = $this->getWordDocument($content);
        $fileName = iconv("utf-8", "GBK", 'tripal' . rand(1, 999));
        header("Content-Type: application/doc");
        header("Content-Disposition: attachment; filename=" . $fileName . ".doc");
        echo $fileContent;
    }

    public function getWordDocument($content, $absolutePath = "http://www.pocketuni.net", $isEraseLink = true) {
        include_once(SITE_PATH . '/addons/libs/MhtFileMaker.php');
        $mht = new MhtFileMaker();
        if ($isEraseLink)
            $content = preg_replace('/<a\s*.*?\s*>(\s*.*?\s*)<\/a>/i', '$1', $content);   //去掉链接
        $images = array();
        $files = array();
        $matches = array();
//这个算法要求src后的属性值必须使用引号括起来
        if (preg_match_all('/<\s*img\s+[^>]*?src\s*=\s*(\'|\")(.*?)\\1[^>]*?\/?\s*>/i', $content, $matches)) {
            $arrPath = $matches[2];
            for ($i = 0; $i < count($arrPath); $i++) {
                $path = $arrPath[$i];
                $imgPath = trim($path);
                if ($imgPath != "") {
                    if (substr($imgPath, 0, 7) == 'http://') {
//绝对链接，不加前缀
                    } else {
                        $imgPath = $absolutePath . $imgPath;
                    }
                    $files[] = $imgPath;
                    $images[] = $imgPath;
                }
            }
        }
        $mht->AddContents("tmp.html", $mht->GetMimeType("tmp.html"), $content);
        for ($i = 1; $i < count($images); $i++) {
            $image = $images[$i];
            if (@fopen($image, 'r')) {
                $imgcontent = file_get_contents($image);
                if ($content)
                    $mht->AddContents($files[$i], $mht->GetMimeType($image), $imgcontent);
            }
            else {
                echo "file:" . $image . " not exist!<br />";
            }
        }
        return $mht->GetFile();
    }

    //部落人数统计--按部门分
    public function teamB(){
        $daoGroup = M('group');
        $list = $daoGroup->group('school')->field('school')->findAll();
        $daoSchool = M('school');
        $daoMember = M('group_member');
        foreach($list as $k=>$val){
            $groups =$daoGroup->where('category=1 AND school='.$val['school'])->field('id')->findAll();
            $num =0;
            $list[$k]['bumen'] =0 ;
            foreach($groups as $v){
                $num = $daoMember->where('gid='.$v['id'])->count();
                 $list[$k]['bumen'] = $list[$k]['bumen'] +$num;
            }
            $groups =$daoGroup->where('category=2 AND school='.$val['school'])->field('id')->findAll();
            $num =0;
            $list[$k]['tuan'] =0 ;
            foreach($groups as $v){
                $num = $daoMember->where('gid='.$v['id'])->count();
                 $list[$k]['tuan'] = $list[$k]['tuan'] +$num;
            }
            $groups =$daoGroup->where('category=3 AND school='.$val['school'])->field('id')->findAll();
            $num =0;
            $list[$k]['shetuan'] =0 ;
            foreach($groups as $v){
                $num = $daoMember->where('gid='.$v['id'])->count();
                 $list[$k]['shetuan'] = $list[$k]['shetuan'] +$num;
            }

            $list[$k]['school'] = $daoSchool->getField('title','id='.$val['school']);

        }
       closeDb();
        $arr = array('学校', '学生部门', '团支部', '学生社团');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title . '部落统计');

    }


        //部落数统计--按部门分
    public function teamNum(){
        $daoGroup = M('group');
        $list = $daoGroup->group('school')->field('school')->findAll();
        $daoSchool = M('school');
        foreach ($list as $k => $val) {
            $list[$k]['bumen'] = $daoGroup->where('category=1 AND school=' . $val['school'])->field('id')->count();
            $list[$k]['tuan'] = $daoGroup->where('category=2 AND school=' . $val['school'])->field('id')->count();
            $list[$k]['shetuan'] = $daoGroup->where('category=3 AND school=' . $val['school'])->field('id')->count();
            $list[$k]['school'] = $daoSchool->getField('title', 'id=' . $val['school']);
        }
       closeDb();
        $arr = array('学校', '学生部门', '团支部', '学生社团');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '部落数统计');

    }

     //部落人数统计--按分类分
    public function teamCat() {
        $list = M('group_category')->field('title,id')->findAll();
        $daoGroup = M('group');
        $daoMember = M('group_member');
        foreach ($list as $k=>$v) {
            $gids = $daoGroup->where('cid0=' . $v['id'])->field('id')->findAll();
            $list[$k]['id'] =0 ;
            foreach($gids as $val){
                $num = $daoMember->where('gid='.$val['id'])->count();
                 $list[$k]['id'] = $list[$k]['id'] +$num;
            }
        }
         closeDb();
         service('Excel')->export2($list, $title . '部落统计-分类');
    }

    public function apply(){
        $num = t($_GET['num']);
        if($num){
            $mapUser['email'] = $num.$this->school['email'];
            $mapUser['sid'] = $this->sid;
            $userInfo = M('user')->where($mapUser)->field('uid,realname,school_event_credit')->find();
            if($userInfo){
                $userInfo['school_event_score'] = X('Score')->getUserScore($userInfo['uid']);
                $this->assign('userInfo',$userInfo);
                $map['uid'] = $userInfo['uid'];
                $map['status'] = 1;
                $list = D('EcApply')->getApplyList($map,'rTime DESC',20);
                $this->assign($list);
            }
        }
        $this->display();
    }

//学校参加并签到过的活动的所有人的名单
    public function eventUserExcel() {
          set_time_limit(0);
        if (!intval($_GET['p'])) {
            $page = 1;
        } else {
            $page = intval($_GET['p']);
        }
        $limit = 2000;
        $offset = ($page - 1) * $limit;
        $db_prefix = C('DB_PREFIX');
        $list = M('event_user')->table("{$db_prefix}event_user AS a ")
                ->join("{$db_prefix}user AS b ON  b.uid=a.uid")
                ->field('a.realname,a.uid,a.eventId,a.tel,a.cTime')
                 ->where('a.status=2 AND b.sid=' . $this->sid)
                ->limit("$offset,$limit")
                ->findAll();
        $Event = M('event');
        $User = M('user');
        foreach ($list as $k => $v) {
            $list[$k]['eventId'] = $Event->getField('title', 'id=' . $v['eventId']);
            $list[$k]['email'] = getUserEmailNum($User->getField('email', 'uid=' . $v['uid']));
            $list[$k]['uid'] = tsGetSchoolByUid($v['uid']);
            $list[$k]['cTime'] = date('Y-m-d h:i', $v['cTime']);
        }
        $title = M('school')->getField('title', 'id=' . $this->sid);
        closeDb();
        $arr = array('姓名', '学校', '活动名称','联系方式', '参加时间', '学号');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);
    }

}
