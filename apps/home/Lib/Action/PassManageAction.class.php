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

class PassManageAction extends PubackAction {

    public function index() {
        $res = array();
        $this->assign($res);
        $this->assign('tree', model('Schools')->_makeTree(0));
        $this->display();
    }
    //编辑用户
    public function editUser() {
        $_GET['uid'] = intval($_GET['uid']);
        if ($_GET['uid'] <= 0)
            $this->error('参数错误');

        $map['uid'] = $_GET['uid'];
        $user = M('user')->where($map)->field('uid,sid,sid1,year,major,uname,realname,sex')->find();
        if (!$user)
            $this->error('无此用户');
        $this->assign($user);
        $this->assign('type', 'edit');
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
        $fields = array('sid', 'sid1', 'sex', 'is_init','year');
        $map = array();
        foreach ($fields as $v)
            if (isset($_POST[$v]) && $_POST[$v] != '')
                $map[$v] = t($_POST[$v]);

        //模糊查询
        $fields2 = array('realname','uname');
        foreach ($fields2 as $v)
            if (isset($_POST[$v]) && $_POST[$v] != '')
                $map[$v] = array('exp', 'LIKE "' . t($_POST[$v]) . '%"');
        if (isset($_POST['num']) && $_POST['num'] != '')
                $map['email'] = array('exp', 'LIKE "' . t($_POST['num']) . '%"');
        if (isset($_POST['event_level']) && $_POST['event_level'] != ''){
            if($_POST['event_level']==20){
                $map['event_level'] = 20;
            }else{
                $map['event_level'] = array('neq','20');
            }
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
        if(!$_POST['uid']){
            $this->error('UID不可为空');
        }
        //保存修改
        if (!empty($_POST['password'])) {
            $key[] = 'password';
            $value[] = codePass($_POST['password']);
        }else{
            $this->error('密码不可为空');
        }
        $map['uid'] = $_POST['uid'];

        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '3';
        $data[] = '用户 - 用户管理 ';
        $data[] = M('user')->where($map)->field('uid,password')->find();
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
            $jumpUrl = U('home/PassManage/index');
        }
        $this->assign('jumpUrl', $jumpUrl);
        S('S_userInfo_' . $_POST['uid'], null);
        $this->success('保存成功');
    }
}

?>