<?php

class UserAction extends AdministratorAction {

    public function _initialize() {
        parent::_initialize();
//        $this->checkRights();
    }

    public function checkRights(){
        $actionArr = array('doEditEvent','doChangeCity','closeSchoolEvent','doChangeRegister','addSchool','renameSchool','editSchool',
            'delSchool','doChangeUserSchool','addUser','doAddUser','editUser','doEditUser','doDeleteUser','sendActiveEmail','addfield',
            'deleteField','doSendMessage','doAddUserGroup','doEditUserGroup','doChangeUserGroup','doDeleteUserGroup','doAddNode','doEditNode',
            'doDeleteNode','doSetPopedom');
        if(in_array(ACTION_NAME, $actionArr)){
            if(!in_array($this->mid, array(33654,33655))){
                $this->error('从2015年起，您没有权限，请找技术部处理');
            }
        }
    }

    //学校管理
    public function school() {
        $this->assign('menu2', 1);
        $tree = M('school')->field("id,title as name,pid as pId")->order('display_order ASC')->findAll();
        $this->assign('tree', json_encode($tree));
        $this->display();
    }

    //学校官方活动
    public function schoolEvent() {
        $this->assign('menu2', 2);
        if (!empty($_POST)) {
            $_SESSION['admin_searchSchool'] = serialize($_POST);
        } elseif (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_searchSchool']);
        } else {
            unset($_SESSION['admin_searchSchool']);
        }
        //组装搜索条件
        if (isset($_POST['sid']) && $_POST['sid'] != '')
                $map['id'] = intval($_POST['sid']);
        if (isset($_POST['title']) && $_POST['title'] != '')
                $map['title'] = array('exp', 'LIKE "%' . t($_POST['title']) . '%"');
        if (isset($_POST['tj_year']) && $_POST['tj_year'] != '')
                $map['tj_year'] = t($_POST['tj_year']);
        if (isset($_POST['cityName']) && $_POST['cityName'] != ''){
            $cityId = M('citys')->getField('id', "city='".t($_POST['cityName'])."'");
            $map['cityId'] = intval($cityId);
        }
        $canRegister = intval($_POST['canRegister']);
        if ($canRegister == 1 || $canRegister == 2)
            $map['canRegister'] = $canRegister-1;
        $isCjdV2 = intval($_POST['isCjdV2']);
        if ($isCjdV2 == 1 || $isCjdV2 == 2)
            $map['isCjdV2'] = $isCjdV2-1;
        $eTime = intval($_POST['eTime']);
        if ($eTime == 1)
            $map['eTime'] = 0;
        elseif($eTime == 2)
            $map['eTime'] = array('neq',0);
        $map['pid'] = 0;
        $dao = model('Schools');
        $list = $dao->where($map)->order('display_order ASC')->findPage(10);
        $this->assign($list);
        $city = M('citys')->findAll();
        $city = orderArray($city,'id');
        $this->assign('city', $city);
        $this->display();
    }

    public function changeCity(){
        $city = M('citys')->order('short ASC')->findAll();
        $this->assign('city', $city);
        $this->display();
    }

    public function doChangeCity(){
        $id = intval($_REQUEST['id']);
        $cityId = intval($_REQUEST['cityId']);
        $res = model('Schools')->doChangeCity($id,$cityId);
        if ($res) {
            $this->success('操作成功！');
        } else {
            $this->error(model('Schools')->getError());
        }
    }
    public function doChangeYear(){
        $id = intval($_REQUEST['id']);
        $data['tj_year'] = intval($_REQUEST['tj_year']);
        $res = M('school')->where("id={$id}")->save($data);
        if ($res) {
            model('Schools')->initCache();
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }

    //开通学校官方活动
    public function editSchoolEvent() {
        if (isset($_REQUEST['id'])) {
            $this->assign('schoolId', intval($_REQUEST['id']));
        }
        $this->display();
    }

    //开通学校官方活动
    public function doEditEvent() {
        $id = intval($_POST ['id']);
        $dao = M('school');
        $school = $dao->where('id=' . $id)->field('email')->find();
        if (!$school) {
            $this->error('学校不存在！');
        }
        $cate ['domain'] = t($_POST ['domain']);
        if (empty($cate ['domain'])) {
            $this->error('域名不能为空！');
        }
        if(!$school['email']){
            $cate ['email'] = '@'.$cate ['domain'].'.com';
        }
//        if (strcasecmp(substr($cate ['domain'], 0, 7), 'http://') != 0) {
//            $cate ['domain'] = 'http://' . $cate ['domain'];
//        }
        $cate ['eTime'] = time();
        $res = $dao->where("id={$id}")->save($cate);
        if (false !== $res) {
            model('Schools')->initCache();
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }

    //关闭学校官方活动
    public function closeSchoolEvent() {
        $dao = M('school');
        $map['id'] = intval($_REQUEST['id']);
        $key[] = 'eTime';
        $value[] = 0;
        $key[] = 'domain';
        $value[] = '';
        $result = $dao->where($map)->setField($key, $value);
        if ($result) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    //开通学校注册
    public function doChangeRegister() {
        $value = ($_REQUEST['type'] == 'open') ? 1 : 0;
        echo model('Schools')->doChangeRegister(intval($_REQUEST['id']),$value);
    }
    //开通学校注册
    public function doChangeCjd() {
        $value = ($_REQUEST['type'] == 'open') ? 1 : 0;
        $result = $this->_setSchoolField(intval($_REQUEST['id']),'isCjdV2',$value);
        $res = 0;
        if ($result) {
            $res = 1;
        }
        echo $res;
    }
    
    //设置是否为团省委特殊团体
    public function doChangeTuan()
    {
        $value = ($_REQUEST['type'] == 'open') ? 1 : 0;
        $result = $this->_setSchoolField(intval($_REQUEST['id']),'is_tuan',$value);
        $res = 0;
        if ($result) 
        {
            $res = 1;
        }
        echo $res;
    }


    private function _setSchoolField($sid,$field,$value){
        return M('school')->where('id='.$sid)->setField($field, $value);
    }

    //添加学校
    public function addSchool() {
        if (empty($_POST ['title'])) {
            $this->error('名称不能为空！');
        }

        $cate ['title'] = t($_POST ['title']);
        $cate ['display_order'] = pinyin($cate ['title']);
        $cate ['pid'] = intval($_POST ['pid']); //1级分类
        S('Cache_Event_School_0', null);
        S('Cache_School_' . $cate ['pid'], null);
        $categoryId = model('Schools')->add($cate);
        if ($categoryId) {
            model('Schools')->initCache();
            $this->success($categoryId);
        } else {
            $this->error('操作失败！');
        }
    }

    public function renameSchool() {
        $id = intval($_POST ['id']);
        $title = t($_POST ['title']);
        if ($title == '') {
            $this->error('名称不能为空！');
        }
        $data['id'] = $id;
        $data['title'] = $title;
        $data ['display_order'] = pinyin($title);
        $daoSchool = model('Schools');
        $res = $daoSchool->save($data);
        if (false !== $res) {
            $school = $daoSchool->field('id,pid')->where('id=' . $id)->find();
            if($school['pid']==0){
                S('Cache_School_Level0_' . $school['id'], null);
                $daoSchool->cacheSchoolDb($id);
            }else{
                S('Cache_School_Level0_' . $school['pid'], null);
            }
            $daoSchool->initCache();
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }

    // 修改分类
    public function editSchool() {
        //if (isset($_POST ['editSubmit'])) {
        $id = intval($_POST ['id']);
        if (!model('Schools')->getField('id', 'id=' . $id)) {
            $this->error('分类不存在！');
        } else if (empty($_POST ['title'])) {
            $this->error('名称不能为空！');
        }

        $cate ['title'] = t($_POST ['title']);

        $pid = $cate ['pid'] = intval($_POST ['sid0']); //1级分类

        S('Cache_School_0', null);
        S('Cache_School_' . $pid, null);

        if ($pid != 0 && !model('Schools')->getField('id', 'id=' . $pid)) {
            $this->error('父级分类错误！');
        } else if ($pid == $id) {
            $res = model('Schools')->setField('title', $cate ['title'], 'id=' . $id);
        } else {
            $res = model('Schools')->where("id={$id}")->save($cate);
        }

        if (false !== $res) {
            S('Cache_School_0', null);
            S('Cache_School_Level_0', null);
            S('Cache_School_Level0_0', null);
            S('Cache_School_Category', null);
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
//        }
//        $id = intval($_GET ['id']);
//        $category = model('Schools')->where("id=$id")->find();
//        $this->assign('school', $category);
//        $this->assign('tree', model('Schools')->_makeTree(0));
//        $this->display();
    }

    // 删除分类
    public function delSchool() {
        $id = intval($_POST ['id']);
        if (model('Schools')->where('id=' . $id)->delete()) {
            model('Schools')->where('pid=' . $id)->delete();
            S('Cache_School_0', null);
            S('Cache_School_Level_0', null);
            S('Cache_School_Level0_0', null);
            S('Cache_School_Category', null);
            $this->success();
        } else {
            $this->error('删除失败！');
        }
    }

    //转移用户学校
    public function changeUserSchool() {
        $_GET['uids'] = explode(',', t($_GET['uids']));
        foreach ($_GET['uids'] as $k => $v)
            if (!is_numeric($v) || intval($v) <= 0)
                unset($_GET['uids'][$k]);
        $count = count($_GET['uids']);

        $_GET['uids'] = implode(',', $_GET['uids']);
        $this->assign('uids', $_GET['uids']);

        $map['uid'] = array('in', $_GET['uids']);
        $users = D('User', 'home')->getUserList($map, false, false, 'uname', '', $count > 3 ? 3 : $count);
        $users = implode(', ', getSubByKey($users['data'], 'uname'));
        $users = $count > 3 ? "$users 等共{$count}人" : "$users 共{$count}人";

        $this->assign('unames', $users);
        $this->assign('tree', model('Schools')->_makeTree(0));
        $this->display();
    }

    public function doChangeUserSchool() {
        $_POST['gid'] = intval($_POST['gid']);
        $_POST['uid'] = explode(',', t($_POST['uid']));
        if (empty($_POST['uid'])) {
            echo 0;
            return;
        }
        $uids = implode(',', $_POST['uid']);
        $map['uid'] = array('in', $uids);

        $logUser = M('user')->where($map)->field('uid, uname, sid')->findAll();
        if (M('user')->where($map)->setField('sid', $_POST['gid'])) {
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = '用户 - 用户管理  - 转移用户学校';
            if ($_POST['__hash__'])
                unset($_POST['__hash__']);
            //$data[] = M( 'UserGroupLink' )->where( 'uid='.$_POST['uid']['0'])->find();
            $data[] = $logUser;
            $data[] = array('新的sid' => $_POST['gid']);
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
            echo 1;
        }else {
            echo 0;
        }
    }

    //用户管理
    public function user() {
        //$dao = D('User', 'home');
        //$res = $dao->getUserList('', true, true,'*', 'uid DESC',10);
        $res = array();
        $this->assign($res);
        $this->assign('tree', model('Schools')->_makeTree(0));
        $this->display();
    }

    //添加用户
    public function addUser() {
        $this->assign('tree', model('Schools')->_makeTree(0));
        $credit_type = X('Credit')->getCreditType();
        $this->assign('credit_type', $credit_type);
        $this->assign('type', 'add');
        $this->display('editUser');
    }

    public function doAddUser() {
        //参数合法性检查
        $required_field = array(
            'email' => 'Email',
            'password' => '密码',
            'uname' => '姓名',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }
        if (!isValidEmail($_POST['email'])) {
            $this->error('Email格式错误，请重新输入');
        }
        if (strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16) {
            $this->error('密码必须为6-16位');
        }
        if (!isEmailAvailable($_POST['email'])) {
            $this->error('Email已经被使用，请重新输入');
        }

        if (!isLegalUsername(t($_POST['uname']))) {
            $this->error('昵称格式不正确');
        }

        $haveName = M('User')->where("`uname`='" . t($_POST['uname']) . "'")->find();
        if (is_array($haveName) && sizeof($haveName) > 0) {
            $this->error('昵称已被使用');
        }

        //注册用户
        $_POST['uname'] = escape(h(t($_POST['uname'])));
        $_POST['realname'] = t($_POST['realname']);
        $_POST['password'] = codePass($_POST['password']);
        $_POST['domain'] = h($_POST['domain']);
        $_POST['ctime'] = time();
//        $_POST['is_active'] = intval($_POST['is_active']);
        $_POST['is_active'] = 1;
        $_POST['sex'] = intval($_POST['sex']);
        $_POST['sid'] = intval($_POST['sid']);
        $_POST['is_init'] = '1';
        $_POST['is_valid'] = '1';

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '1';
        $data[] = '用户 - 用户管理 ';
        if ($_POST['__hash__'])
            unset($_POST['__hash__']);
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $uid = M('user')->add($_POST);
        if (!$uid) {
            $this->error('抱歉：注册失败，请稍后重试');
            exit;
        }

        //添加用户组信息
        model('UserGroup')->addUserToUserGroup($uid, t($_POST['user_group_id']));

        $this->success('注册成功');
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

    //jun  传输院校到ajax
   public function childTree() {

    	$tree=model('Schools')->_makeTree(intval($_GET['sid']));
       echo json_encode($tree);
     }

    public function doEditUser() {
        //参数合法性检查

        $_POST['uid'] = intval($_POST['uid']);
        S('S_userInfo_' . $_POST['uid'], null);
        if (!M('user')->getField('email', "uid={$_POST['uid']}")) { // 非本地Email帐号（即第三方）的用户
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
        if (mb_strlen($_POST['uname'], 'UTF8') > 10) {
            $this->error('昵称不能超过10个字符');
        }
        if (mb_strlen($_POST['year'], 'UTF8') > 3) {
            $this->error('年级格式不正确');
        }
        //保存修改
        $key = array('email', 'uname', 'realname', 'sex', 'is_active', 'domain','year','major');
        $value = array($_POST['email'], escape(h(t($_POST['uname']))),t($_POST['realname']), intval($_POST['sex']), intval($_POST['is_active']), h($_POST['domain']),$_POST['year'],$_POST['major']);
        $sid=intval($_POST['sid']);
        if(!empty($sid)){
            $key[] = 'sid';
            $value[] = $sid;
        }
        $mobile=t($_POST['mobile']);
        $key[] = 'mobile';
        $value[] = $mobile.'';
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
        $data[] = M('user')->where($map)->field('uid,email,password,uname,domain,sex,is_active')->find();
        $CreditInfo = M('CreditUser')->where($map)->find();
        $data['1']["scorea"] = $CreditInfo['scorea'] ? $CreditInfo['scorea'] : '0';
        $data['1']["experience"] = $CreditInfo['experience'] ? $CreditInfo['experience'] : '0';
        $GroupInfo = M('UserGroupLink')->where($map)->find();
        $data['1']['user_group_id'] = $GroupInfo['user_group_id'] ? $GroupInfo['user_group_id'] : '0';
        if ($_POST['__hash__'])
            unset($_POST['__hash__']);
        if (!$_POST['password'])
            $_POST['password'] = $data['1']['password'];
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = M('user')->where($map)->setField($key, $value);
        //保存积分设置
        $credit = X('Credit');
        $credit_type = $credit->getCreditType();
        foreach ($credit_type as $v) {
            $credit_action[$v['name']] = intval($_POST[$v['name']]);
        }
        $credit->setUserCredit($map['uid'], $credit_action, 'reset');

        //添加用户组信息
        model('UserGroup')->addUserToUserGroup($_POST['uid'], t($_POST['user_group_id']));

        S('UserGroupIds_' . $_POST['uid'], null);
        S('S_userInfo_' . $_POST['uid'], null);
        if(isset($_SESSION['admin_searchUser_url'])){
            $jumpUrl = $_SESSION['admin_searchUser_url'];
        }else{
            $jumpUrl = U('admin/User/user');
        }
        $this->assign('jumpUrl', $jumpUrl);
        $this->success('保存成功');
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

        //ts_user
        $res = D('User', 'home')->deleteUser($_POST['uid']);
        if ($res) {
            echo 1;
        } else {
            echo 0;
            return;
        }
    }
    public function sendActiveEmail() {
        $_POST['uid'] = t($_POST['uid']);
        $_POST['uid'] = explode(',', $_POST['uid']);
        if (isset($_POST['email'])) {
            $uid = $_POST['uid'][0];
            $email = $_POST['email'];
            $this->a($uid, $email);
            if (!$res) {
                echo 0;
                return;
            }
        } else {
            foreach ($_POST['uid'] as $uid) {
                $dao = D('User', 'home');
                $info = $dao->getUserByIdentifier($uid);
                $res = $this->a($uid, $info['email']);
                if (!$res) {
                    echo 0;
                    return;
                }
            }
        }
        echo 1;
        return;
    }

    public function a($uid, $email) {
        //设置激活路径
        $activate_url = service('Validation')->addValidation($uid, '', U('home/Public/doActivate'), 'register_activate', serialize($invite));

        //设置邮件模板
        $body = <<<EOD
感谢您的注册!<br>

请马上点击以下注册确认链接，激活您的帐号！<br>

<a href="$activate_url" target='_blank'>$activate_url</a><br/>

如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

默认密码为：123456,激活后请第一时间修改。<br/>

如果你错误地收到了此电子邮件，你无需执行任何操作来取消帐号！此帐号将不会启动。
EOD;
        // 发送邮件
        global $ts;
        $res = service('Mail')->send_email($email, "激活{$ts['site']['site_name']}帐号", $body);

        if ($res) {
            return 1;
        } else {
            return 0;
        }
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
        //学校登录人数
//        if(isset($_POST['sid'])){
//            $db_prefix = C('DB_PREFIX');
//            $dao = M('login_count');
//            $dao->table("{$db_prefix}login_count AS a ")
//                    ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
//                    ->field('count(*) as count');
//            if(isset($map['sid1'])){
//                $dao->where('b.sid='.$_POST['sid'].' and b.sid1='.$_POST['sid1']);
//            }else{
//                $dao->where('b.sid='.$_POST['sid']);
//            }
//            $count = $dao->count();
//            $this->assign('loginCount', $count);
//        }

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
        $this->display('user');
    }

    //字段配置
    public function setField() {
        $data['list'] = D('UserSet')->getFieldList();
        $this->assign($data);
        $this->display();
    }

    //添加字段
    public function addfield() {
        if ($_POST) {
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '1';
            $data[] = '用户 - 资料配置 ';
            if ($_POST['__hash__'])
                unset($_POST['__hash__']);
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
            if (D('UserSet')->addfield()) {
                $this->success('添加成功');
            } else {
                $this->error(D('UserSet')->getError());
            }
        } else {
            $this->display();
        }
    }

    public function deleteField() {
        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '用户 - 资料配置 ';
        $map['id'] = array('in', $_POST['ids']);
        $data[] = D('UserSet')->where($map)->findall();
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        echo D('UserSet')->deleteField(intval($_POST['ids'])) ? '1' : '0';
    }

    public function relateUser() {
        if ($_POST) {
            $data['tag_weight'] = intval($_POST['tag_weight']);
            $data['city_weight'] = intval($_POST['city_weight']);
            $data['friend_weight'] = intval($_POST['friend_weight']);
            $data['follower_weight'] = intval($_POST['follower_weight']);
            $data['hide_no_avatar'] = intval($_POST['hide_no_avatar']);
            model('Xdata')->lput('related_user', $data);
        }

        $data = model('Xdata')->lget('related_user');
        $data['tag_weight'] = isset($data['tag_weight']) ? intval($data['tag_weight']) : 4;
        $data['city_weight'] = isset($data['city_weight']) ? intval($data['city_weight']) : 3;
        $data['friend_weight'] = isset($data['friend_weight']) ? intval($data['friend_weight']) : 2;
        $data['follower_weight'] = isset($data['follower_weight']) ? intval($data['follower_weight']) : 1;
        $data['total_weight'] = $data['tag_weight'] + $data['city_weight'] + $data['friend_weight'] + $data['follower_weight'];
        $data['hide_no_avatar'] = intval($data['hide_no_avatar']);

        $this->assign($data);
        $this->display();
    }

    public function follower() {
        if ($_POST) {
            $data['hide_no_avatar'] = intval($_POST['hide_no_avatar']);
            $data['hide_auto_friend'] = intval($_POST['hide_auto_friend']);
            model('Xdata')->lput('top_follower', $data);
            //修改后清缓存
            $cache_id = '_weibo_top_followed_10_00' . intval($data['hide_auto_friend']) . intval($data['hide_no_avatar']);
            $cache_tid = '_weibo_top_followed_t_10_' . intval($data['hide_auto_friend']) . intval($data['hide_no_avatar']);
            S($cache_id, Null);
            S($cache_tid, Null);
        }

        $data = model('Xdata')->lget('top_follower');
        $data['hide_no_avatar'] = intval($data['hide_no_avatar']);
        $data['hide_auto_friend'] = intval($data['hide_auto_friend']);
        $this->assign($data);
        $this->display();
    }

    //消息群发
    public function message() {
        // 用户组列表
        $user_group_list = model('UserGroup')->field('`user_group_id`,`title`')->findAll();
        $this->assign('user_group_list', $user_group_list);
        $this->display();
    }

    public function doSendMessage() {
        $_POST['user_group_id'] = intval($_POST['user_group_id']);
        $_POST['type'] = intval($_POST['type']);
        $_logpost = $_POST ? $_POST : '0';
        // 收件人
        if ($_POST['user_group_id'] == 0) {
            // 全部用户
            $_POST['to'] = M('user')->where('`is_active`=1 AND `is_init`=1')->field('`uid`,`email`')->findAll();
            $_POST['to'] = $_POST['type'] == 1 ? getSubByKey($_POST['to'], 'email') : getSubByKey($_POST['to'], 'uid');
        } else {
            // 指定用户组
            $_POST['to'] = model('UserGroup')->getUidByUserGroup($_POST['user_group_id']);
            if ($_POST['type'] == 1) {
                $map['uid'] = array('in', $_POST['to']);
                $_POST['to'] = M('user')->where($map)->field('email')->findAll();
                $_POST['to'] = getSubByKey($_POST['to'], 'email');
            }
        }
        unset($_POST['user_group_id']);

        $res = false;
        if ($_POST['type'] == 0) {
            // 站内信
            if ($_POST['title'] && $_POST['content']) {
                $res = model('Message')->postMessage($_POST, $this->mid);
                $res = !empty($res);
            }
        } else {
            // Email
            $service = service('Mail');
            $_POST['title'] = t($_POST['title']);
            $_POST['content'] = t($_POST['content']);
            foreach ($_POST['to'] as $v)
                $res = $res || $service->send_email($v, $_POST['title'], $_POST['content']);
        }
        if ($res) {
            if ($_logpost['title'] || $_logpost['content']) {
                $_LOG['uid'] = $this->mid;
                $_LOG['type'] = '1';
                $data[] = '用户 - 消息群发 ';
                if ($_logpost['__hash__'])
                    unset($_logpost['__hash__']);
                $data[] = $_logpost;
                $_LOG['data'] = serialize($data);
                $_LOG['ctime'] = time();
                M('AdminLog')->add($_LOG);
            }
            $this->success('发送成功');
        }else {
            $this->error('发送失败');
        }
    }

    private function __sendMessage() {

    }

    //用户等级
    public function level() {
        echo '<h2>这里是用户等级</h2>';
        //$this->display();
    }

    //用户组列表
    public function userGroup() {
        $user_groups = model('UserGroup')->getUserGroupByMap();
        $this->assign('user_groups', $user_groups);
        $this->display();
    }

    //添加or编辑用户组
    public function editUserGroup() {
        $_GET['gid'] = intval($_GET['gid']);
        if ($_GET['gid'] > 0) {
            //编辑时，显示原用户组名称
            $user_group = model('UserGroup')->getUserGroupById($_GET['gid']);
            $this->assign('user_group', $user_group[0]);
        }
        $this->display();
    }

    public function doAddUserGroup() {
        $_POST['title'] = escape(t($_POST['title']));
        if (empty($_POST['title'])) {
            echo 0;
            return;
        }

        $dao = model('UserGroup');
        if ($dao->isUserGroupExist($_POST['title'])) {
            echo -1; // 用户组已存在
        } else {
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '1';
            $data[] = '用户 - 用户组管理 ';
            if ($_POST['__hash__'])
                unset($_POST['__hash__']);
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);

            $res = $dao->addUserGroup($_POST['title'], $_POST['icon']);
            if ($res)
                echo intval($res);
            else
                echo 0;
        }
    }

    public function doEditUserGroup() {
        $gid = intval($_POST['gid']);
        $dao = model('UserGroup');
        $data['title'] = escape(t($_POST['title']));
        $data['icon'] = escape(t($_POST['icon']));

        if ($dao->isUserGroupExist($data['title'], $gid)) {
            echo -1; // 用户组已存在
        } else {
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = '用户 - 用户组管理 ';
            $data[] = M('user_group')->where('user_group_id=' . $gid)->data($data)->findAll();
            if ($_POST['__hash__'])
                unset($_POST['__hash__']);
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);

            $res = M('user_group')->where('user_group_id=' . $gid)->data($data)->save();
            $res1 = M('user_group_link')->where('user_group_id=' . $gid)->setField('user_group_title', $data['title']) && $res;
            if (false !== $res)
                echo 1;
            else
                echo 0;
        }
    }

    //转移用户组
    public function changeUserGroup() {
        $_GET['uids'] = explode(',', t($_GET['uids']));
        foreach ($_GET['uids'] as $k => $v)
            if (!is_numeric($v) || intval($v) <= 0)
                unset($_GET['uids'][$k]);
        $count = count($_GET['uids']);

        $_GET['uids'] = implode(',', $_GET['uids']);
        $this->assign('uids', $_GET['uids']);

        $map['uid'] = array('in', $_GET['uids']);
        $users = D('User', 'home')->getUserList($map, false, false, 'uname', '', $count > 3 ? 3 : $count);
        $users = implode(', ', getSubByKey($users['data'], 'uname'));
        $users = $count > 3 ? "$users 等共{$count}人" : "$users 共{$count}人";

        $this->assign('unames', $users);
        $this->display();
    }

    public function doChangeUserGroup() {
        $_POST['gid'] = explode(',', t($_POST['gid']));
        $_POST['uid'] = explode(',', t($_POST['uid']));
        if (empty($_POST['gid']) || empty($_POST['uid'])) {
            echo 0;
            return;
        }


        $logpost = M('UserGroupLink')->where('uid=' . $_POST['uid']['0'])->find();


        if (model('UserGroup')->addUserToUserGroup($_POST['uid'], $_POST['gid'])) {
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = '用户 - 用户管理  - 转移用户组';
            $data[] = $logpost;
            if ($_POST['__hash__'])
                unset($_POST['__hash__']);
            $data[] = M('UserGroupLink')->where('uid=' . $_POST['uid']['0'])->find();
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
            echo 1;
        }else {
            echo 0;
        }
    }

    public function doDeleteUserGroup() {
        $_POST['gid'] = t($_POST['gid']);
        //不为空时，不允许删除
        if (!model('UserGroup')->isUserGroupEmpty($_POST['gid'])) {
            echo 0;
            return;
        }
        //提交删除

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '用户 - 用户组管理';
        $data[] = M('UserGroup')->where('user_group_id=' . $_POST['gid'])->find();
        if ($_POST['__hash__'])
            unset($_POST['__hash__']);
        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        $res = model('UserGroup')->deleteUserGroup($_POST['gid']);
        if ($res)
            echo 1;
        else
            echo 0;
    }

    public function isUserGroupExist() {
        $res = model('UserGroup')->isUserGroupExist($_POST['title'], intval($_POST['gid']));
        if ($res)
            echo 1;
        else
            echo 0;
    }

    public function isUserGroupEmpty() {
        $res = model('UserGroup')->isUserGroupEmpty($_POST['gid']);
        if ($res)
            echo 1;
        else
            echo 0;
    }

    /** 权限 * */
    public function node() {
        $node = D('Node')->getAllNode();
        $this->assign($node);
        $this->display();
    }

    public function addNode() {
        $this->assign('type', 'add');
        $this->display('editNode');
    }

    public function doAddNode($old_nid = 0) {
        //module为*时，action被忽略
        $_POST['act_name'] = $_POST['mod_name'] == '*' ? $_POST['mod_name'] : $_POST['act_name'];

        if (!$this->__isValidRequest('app_name,mod_name,act_name'))
            $this->error('参数不完整');

        //action为*时，subAction被忽略
        $_POST['subAction'] = ($_POST['act_name'] == '*') ? array() : $_POST['subAction'];

        foreach ($_POST['subAction'] as $k => $v) {
            if (empty($v))
                unset($_POST['subAction'][$k]);
            if ($v == '*')
                $this->error('参数错误：模块和方法名不为“*”时，关联方法名不可为“*”');
        }
        $_POST['parent_node_id'] = 0;
        unset($_POST['node_id']);

        if (!$old_nid) {
            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '1';
            $data[] = '用户 - 权限 - 节点管理';
            if ($_POST['__hash__'])
                unset($_POST['__hash__']);
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);
        }

        $res = D('Node')->add($_POST);
        $nid = $res;

        //添加关联节点
        if (!empty($_POST['subAction'])) {
            $prefix = C('DB_PREFIX');
            $sql = "INSERT INTO `{$prefix}node` (`app_name`,`app_alias`,`mod_name`,`mod_alias`,`act_name`,`act_alias`,`description`,`parent_node_id`) VALUES";

            foreach ($_POST['subAction'] as $v) {
                $sql .= " ('{$_POST['app_name']}','{$_POST['app_alias']}','{$_POST['mod_name']}','{$_POST['mod_alias']}','{$v}','{$_POST['act_alias']}_关联方法','{$_POST['description']}','{$nid}'),";
            }
            $sql = rtrim($sql, ',');

            $res = $nid && M('')->execute($sql);
        }

        //编辑时，删除旧记录
        if ($res && $old_nid) {

            $_LOG['uid'] = $this->mid;
            $_LOG['type'] = '3';
            $data[] = '用户 - 权限 - 节点管理';
            $data[] = D('Node')->where("`node_id`=$old_nid OR `parent_node_id`=$old_nid")->find();
            $data['1']['subAction'] = getSubByKey(D('Node')->where('parent_node_id=' . $old_nid)->field('act_name')->findall(), 'act_name');
            if ($_POST['__hash__'])
                unset($_POST['__hash__']);
            $data[] = $_POST;
            $_LOG['data'] = serialize($data);
            $_LOG['ctime'] = time();
            M('AdminLog')->add($_LOG);

            D('Node')->where("`node_id`=$old_nid OR `parent_node_id`=$old_nid")->delete();
            //更新权限表
            M('user_group_popedom')->where("`node_id`=$old_nid")->setField('node_id', $nid);
        }

        if ($res) {
            //编辑时，跳转至节点列表页
            $old_nid && $this->assign('jumpUrl', U('admin/User/node'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function editNode() {
        $nid = intval($_GET['nid']);
        $node = D('Node')->getNodeDetailById($nid);
        if (!$node)
            $this->error('不存在此节点');

        $this->assign($node);
        $this->assign('type', 'edit');
        $this->display();
    }

    public function doEditNode() {
        //删除旧记录，添加新记录
        $this->doAddNode(intval($_POST['node_id']));
        exit;
    }

    public function doDeleteNode() {
        $_POST['ids'] = t($_POST['ids']);
        //不为空时，不允许删除
        if (!D('Node')->isNodeEmpty($_POST['ids'])) {
            echo 0;
            return;
        }

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $data[] = '用户 - 权限 - 节点管理';
        $map['node_id'] = array('in', $_POST['ids']);
        $nodeList = $data[] = D('Node')->where($map)->findall();
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        //提交删除
        $res = D('Node')->deleteNode($_POST['ids']);
        if ($res) {
            # 删除节点权限缓存
            Service('SystemPopedom')->delNodeCache();
            echo 1;
        } else {
            echo 0;
        }
    }

    public function popedom() {
        //获取主节点
        $node = D('Node')->getNodeByMap('`parent_node_id`=0', 'app_name ASC, mod_name ASC, act_name ASC, node_id ASC');

        //获取节点与用户组的对应关系
        $nids = getSubByKey($node['data'], 'node_id');
        $prefix = C('DB_PREFIX');
        $where = 'p.node_id IN ( ' . implode(',', $nids) . ' )';
        $sql = "SELECT p.node_id,g.title FROM {$prefix}user_group_popedom AS p INNER JOIN {$prefix}user_group AS g ON p.user_group_id = g.user_group_id WHERE $where";
        $res = M('')->query($sql);
        $node_usergroup = array();
        foreach ($res as $v) {
            $node_usergroup[$v['node_id']][] = $v['title'];
        }
        $this->assign($node);
        $this->assign('node_usergroup', $node_usergroup);
        $this->display();
    }

    public function setPopedom() {
        $_GET['nids'] = t($_GET['nids']);
        $_GET['nids'] = explode(',', $_GET['nids']);
        foreach ($_GET['nids'] as $k => $v) {
            if (!is_numeric($v))
                unset($_GET['nids'][$k]);
        }
        $count = count($_GET['nids']);
        $this->assign('nids', implode(',', $_GET['nids']));
        $this->assign('count', $count);

        if ($count == 1) {
            $map['node_id'] = array('in', $_GET['nids']);
            $user_group = M('user_group_popedom')->where($map)->findAll();
            $user_group = getSubByKey($user_group, 'user_group_id');
            $this->assign('user_group', $user_group);
        }
        $this->display();
    }

    public function doSetPopedom() {
        $_POST['gid'] = explode(',', $_POST['gid']);
        $_POST['nid'] = explode(',', $_POST['nid']);

        foreach ($_POST['gid'] as $k => $v)
            if (!is_numeric($v) || intval($v) <= 0)
                unset($_POST['gid'][$k]);
        if (empty($_POST['gid'])) {
            echo 0;
            return;
        }

        foreach ($_POST['nid'] as $k => $v)
            if (!is_numeric($v) || intval($v) <= 0)
                unset($_POST['nid'][$k]);
        if (empty($_POST['nid'])) {
            echo 0;
            return;
        }

        //获取节点的关联节点ID
        $map['parent_node_id'] = array('in', $_POST['nid']);
        $nids = D('Node')->where($map)->field('node_id')->findAll();
        $nids = getSubByKey($nids, 'node_id');
        $nids = array_merge($nids, $_POST['nid']);
        if (empty($nids)) {
            echo 0;
            return;
        }

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '3';
        $data[] = '用户 - 权限 - 节点管理';
        $where['node_id'] = array('in', $_POST['nid']);
        $data['1']['nid'] = $_POST['nid'];
        $data['1']['gid'] = getSubByKey(M('user_group_popedom')->where($where)->findall(), 'user_group_id');

        $data[] = $_POST;
        $_LOG['data'] = serialize($data);
        $_LOG['ctime'] = time();
        M('AdminLog')->add($_LOG);

        //删除旧记录
        M('user_group_popedom')->where('`node_id` IN ( ' . implode(',', $nids) . ' )')->delete();

        //组装插入SQL
        $sql = "INSERT INTO `" . C('DB_PREFIX') . "user_group_popedom` (`user_group_id`,`node_id`) VALUES ";
        foreach ($nids as $nid) {
            foreach ($_POST['gid'] as $gid) {
                $sql .= "('$gid', '$nid'),";
            }
        }
        $sql = rtrim($sql, ',');

        $res = M('')->execute($sql);
        if ($res) {
            #每次编辑完权限 就设置相关缓存
            Service('SystemPopedom')->delNodeCache();
            echo 1;
        } else {
            echo 0;
        }
    }

    private function __isValidRequest($field, $array = 'post') {
        $field = is_array($field) ? $field : explode(',', $field);
        $array = $array == 'post' ? $_POST : $_GET;
        foreach ($field as $v) {
            $v = trim($v);
            if (!isset($array[$v]) || $array[$v] == '')
                return false;
        }
        return true;
    }

       //用户管理
    public function schoolMajor() {
        $this->assign('menu2', 3);
        $this->assign('school', model('Schools')->_makeTree(0));
        $this->display();
    }


    public function doSearchMajor() {
        $this->assign('menu2', 3);
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_searchMarjor'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_searchMarjor']);
        } else {
            unset($_SESSION['admin_searchMarjor']);
        }
        $_POST['sid'] && $map['sid'] = $_POST['sid'];
        $_POST['sid1'] && $map['sid1'] = $_POST['sid1'];
        $_POST['year'] && $map['year'] = $_POST['year'];
        $map['major'] = array('neq','');
        $cnt = M('user')->where($map)->field('count(distinct(major)) as cnt')->find();
        $list = M('user')->where($map)->field('distinct(major) as major')->findPage(10,$cnt['cnt']);
        $this->assign($list);
        $this->assign($_POST);
        $this->assign('school', model('Schools')->_makeTree(0));
        $this->display('schoolMajor');
    }

}