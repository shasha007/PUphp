<?php

/**
 * 用户管理
 *
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
import('home.Action.PubackAction');

class UserManageAction extends PubackAction {

    private function _canEdit($user){
        if($_SESSION['ThinkSNSAdmin'] == '1' || $this->user['can_admin'] || $this->mid == $user['uid'] ||
                $this->user['event_level'] < $user['event_level']){
            return true;
        }
        return false;
    }

    public function index() {
        $res = array();
        $this->assign($res);
        $this->assign('tree', model('Schools')->_makeTree(0));
        $this->display();
    }
    //添加用户
    public function addUser(){
    	$list = D('school')->where('pid=0')->field('id,title')->select();
        $this->assign('list',$list);
        $this->display();
    }

    public function getSid1() {
        $list = array();
        $pid = $_POST['pid'];
        if($pid>0){
            $daocate = M('school');
            $list = $daocate->where('pid=' . $pid)->field('id,title')->select();
        }
        echo json_encode($list);
    }
    //处理添加用户
    public function doAddUser(){
        //参数合法性检查
        $required_field = array(
            'event_level' => '身份',
            'number' => '学号',
            'password' => '密码',
            'uname' => '昵称',
            'realname' => '姓名',
            'sid' => '学校',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }
        $data['event_level'] = intval($_POST['event_level']);
        $data['sid1'] = intval($_POST['sid1']);
        $data['sid'] = intval($_POST['sid']);
        $data['year'] = t($_POST['year']);
        $data['major'] = t($_POST['major']);
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
        $res = D('school')->where('id='.$data['sid'])->field('id,email')->find();
        $data['email'] = t($_POST['number']).$res['email'];
        if (!isEmailAvailable($data['email'])) {
            $this->error('学号已经被使用，请重新输入');
        }
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
        $data['password'] = codePass($_POST['password']);
        $data['ctime'] = time();
        $data['is_active'] = 1;
        $data['sex'] = intval($_POST['sex']);
        $data['is_init'] = '0';
        $data['is_valid'] = '1';
        if(isset($_POST['event_role_info'])){
            $data['event_role_info'] = t($_POST['event_role_info']);
        }
        if($data['sid'] == 659){
            $data['is_init'] = '1';
            $data['mobile'] = '123456';
        }
        $data['can_admin'] = ($_POST['can_admin'] == 1) ? 1 : 0;
        $data['can_add_event'] = ($_POST['can_add_event'] == 1) ? 1 : 0;
        $data['can_event2'] = ($_POST['can_event2'] == 1) ? 1 : 0;
        $data['can_gift'] = ($_POST['can_gift'] == 1) ? 1 : 0;
        $data['can_print'] = ($_POST['can_print'] == 1) ? 1 : 0;
        $data['can_group'] = ($_POST['can_group'] == 1) ? 1 : 0;
        $data['can_announce'] = ($_POST['can_announce'] == 1) ? 1 : 0;

        $uid = M('user')->add($data);
        if (!$uid) {
            $this->error('抱歉：注册失败，请稍后重试');
            exit;
        }
        //菁英班微博关注超管
        if(isTuanRole($data['sid']) && !$data['can_admin']){
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
        $this->success('注册成功');
    }
    //删除用户
    public function doDeleteUser() {
        $_POST['uid'] = t($_POST['uid']);
        $_POST['uid'] = explode(',', $_POST['uid']);

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '用户 - 用户管理 ';
        $map['uid'] = array('in', $_POST['uid']);
        $data[] = M('user')->where($map)->findall();
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);
        $res = D('User', 'home')->deleteUser($_POST['uid']);
        if ($res) {
            echo 1;
        } else {
            echo 0;
            return;
        }
    }
    //编辑用户
    public function editUser() {
        $_GET['uid'] = intval($_GET['uid']);
        if ($_GET['uid'] <= 0)
            $this->error('参数错误');

        $map['uid'] = $_GET['uid'];
        $user = M('user')->where($map)->find();
        if (!$user)
            $this->error('无此用户');

        $credit = X('Credit');
        $credit_type = $credit->getCreditType();
        $user_credit = $credit->getUserCredit($map['uid']);

        $this->assign($user);
        $this->assign('credit_type', $credit_type);
        $this->assign('user_credit', $user_credit);
        $this->assign('type', 'edit');
        $this->assign('tree', model('Schools')->_makeTree(0));
        $this->assign('tree1', model('Schools')->_makeTree(intval($user['sid'])));
        $this->assign('chlidren',model('Schools')->field('title,id')->find($user['sid1']));
        $this->display();
    }
    public function childTree() {
    	$tree=model('Schools')->_makeTree(intval($_GET['sid']));
        echo json_encode($tree);
    }
    //搜索用户
    public function doSearchUser() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_searchUser'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_searchUser']);
        } else {
            unset($_SESSION['admin_searchUser']);
        }
        //组装搜索条件
        $fields = array('sid', 'sid1','email', 'uid', 'sex', 'is_init','year','major','mobile');
        $map = array();
        foreach ($fields as $v)
            if (isset($_POST[$v]) && $_POST[$v] != '')
                $map[$v] = t($_POST[$v]);

        //模糊查询
        $fields2 = array('realname','uname', 'email','year','major','mobile');
        foreach ($fields2 as $v)
            if (isset($_POST[$v]) && $_POST[$v] != '')
                $map[$v] = array('exp', 'LIKE "' . t($_POST[$v]) . '%"');

        if (isset($_POST['event_level']) && $_POST['event_level'] != ''){
            if($_POST['event_level']==20){
                $map['event_level'] = 20;
            }else{
                $map['event_level'] = array('neq','20');
            }
        }
        //按用户组搜索
        if (!empty($_POST['user_group_id'])) {
            $uids = model('UserGroup')->getUidByUserGroup($_POST['user_group_id']);
            $uids = array_unique($uids);
            //同时按部门和按用户组时，取交集
            $uids = empty($map['uid']) && !empty($uids) ? $uids : array_intersect($uids, $map['uid'][1]);
            $map['uid'] = array('in', $uids);
        }
        $res = D('User', 'home')->getUserList($map, true, true);
        $this->assign($res);
        $this->assign('type', 'searchUser');
        $this->assign(array_map('t', $_POST));
        $this->assign('tree', model('Schools')->_makeTree(0));
        $beforEdit = $_SERVER['REQUEST_URI'];
        if (!isset($_GET[C('VAR_PAGE')])) {
            $beforEdit .= '&p=1';
        }
        $_SESSION['admin_searchUser_url'] = $beforEdit;
        $this->display('index');
    }
    public function doEditUser() {
        //参数合法性检查
        $_POST['uid'] = intval($_POST['uid']);
        if (!M('user')->getField('email', "uid={$_POST['uid']}")) {
            unset($_POST['email']); // 无法编辑其Email
            unset($_POST['password']); // 无法编辑其密码
            $required_field = array(
                'uid' => '指定用户',
                'uname' => '姓名',
            );
            foreach ($required_field as $k => $v) {
                if (empty($_POST[$k]))
                    $this->error($v . '不可为空');
            }
        } else {
            $required_field = array(
                'uid' => '指定用户',
                'email' => 'Email',
                'uname' => '姓名',
            );
            foreach ($required_field as $k => $v) {
                if (empty($_POST[$k]))
                    $this->error($v . '不可为空');
            }
            if (!isValidEmail($_POST['email'])) {
                $this->error('Email格式错误，请重新输入');
            }
            if (!isEmailAvailable($_POST['email'], $_POST['uid'])) {
                $this->error('Email已经被使用，请重新输入');
            }
            if (!empty($_POST['password']) && strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16) {
                $this->error('密码必须为6-16位');
            }
        }
        //保存修改
        $key = array('email', 'uname', 'realname', 'sex','year','major');
        $value = array($_POST['email'], escape(h(t($_POST['uname']))),t($_POST['realname']), intval($_POST['sex']),$_POST['year'],$_POST['major']);
        $sid=intval($_POST['sid']);
        if(!empty($sid)){
            $key[] = 'sid';
            $value[] = $sid;
        }
        $mobile=t($_POST['mobile']);
        $key[] = 'mobile';
        $value[] = $mobile;
        $key[] = 'is_valid';
        $value[] = '1';
        $sid1=intval($_POST['sid1']);
        $key[] = 'sid1';
        $value[] = $sid1;
        if (!empty($_POST['password'])) {
            $key[] = 'password';
            $value[] = codePass($_POST['password']);
        }
        $map['uid'] = $_POST['uid'];

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '3';
        $data[] = '用户 - 用户管理 ';
        $data[] = M('user')->where($map)->field('uid,sid,sid1,year,major,email,password,uname,realname,sex')->find();
        if ($_POST['__hash__'])
            unset($_POST['__hash__']);
        if (!$_POST['password'])
            $_POST['password'] = $data['1']['password'];
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);
        M('user')->where($map)->setField($key, $value);
        if(isset($_SESSION['admin_searchUser_url'])){
            $jumpUrl = $_SESSION['admin_searchUser_url'];
        }else{
            $jumpUrl = U('home/UserManage/index');
        }
        $this->assign('jumpUrl', $jumpUrl);
        S('S_userInfo_' . $_POST['uid'], null);
        $this->success('保存成功');
    }
}

?>