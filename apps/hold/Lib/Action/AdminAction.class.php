<?php

import ( 'admin.Action.AdministratorAction' );
class AdminAction extends AdministratorAction {

    var $info;
    var $user;
    var $img;
    var $flash;

    public function _initialize() {
        parent::_initialize();
        $this->info = M('hold_info');
        $this->user = M('hold_user');
        $this->img = M('hold_user_img');
        $this->flash = M('hold_user_flash');
    }

    /* 去掉视频标题 --在线播放字样
    public function update(){
        $flash = $this->flash->findAll();
        foreach ($flash as $value) {
            $map['id'] = $value['id'];
            $map['title'] = preg_replace(array('/—在线播放(.*)/','/ 在线观看(.*)/'), '', $value['title']);
            //var_dump($value);
            $this->flash->save($map);
        }
    }
     */

        /**
     * index
     * 获取配置信息
     * @access public
     * @return void
     */
    public function index() {
        $order = 'id DESC';
        $map['isDel'] = 0;
        $list = $this->info->where($map)->order($order)->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function editInfo() {
        $this->assign('type', 'add');
        if ($id = (int) $_REQUEST['id']) {
            $this->assign('type', 'edit');

            $info = $this->info->find($id);
            if (!$info) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("info", $info);
        }
        $this->display();
    }

    public function doEditInfo() {
        $id = intval($_REQUEST['id']);
        if (empty($id) && !empty($_REQUEST['title'])) {
            $this->insertInfo();
        } elseif (!empty($id) && !empty($_REQUEST['title'])) {
            $this->updateInfo($id);
        } else {
            $this->error('标题不能为空！');
        }
    }

    public function insertInfo() {
        $this->info->title = t($_REQUEST['title']);
        $this->info->content = t(h($_POST['content']));
        $this->info->isDel = 0;
        $this->info->cTime = time();
        $this->info->uTime = time();
        if ($this->info->add()) {
            //成功提示
            $this->assign('jumpUrl', U('/Admin/index'));
            $this->success('添加成功！');
        } else {
            //失败提示
            $this->error('添加失败！');
        }
    }

    public function updateInfo($id) {
        $info = $this->info->find($id);
        if (!$info) {
            $this->error('非法参数，新闻不存在！');
        }
        //保存当前数据对象
        $this->info->title = t($_REQUEST['title']);
        $this->info->content = t(h($_POST['content']));
        $this->info->uTime = time();
        if ($this->info->where("id={$id}")->save()) {
            //保存成功则刷新页面
            $this->assign('jumpUrl', U('/Admin/index'));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    public function put_info_to_recycle() {
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = $this->info->setField('isDel', 1, 'id IN ' . t($gid)); // 通过审核
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

    public function userList() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_hold_user_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')]) || isset($_GET['orderKey'])) {
            $_POST = unserialize($_SESSION['admin_hold_user_search']);
        } else {
            unset($_SESSION['admin_hold_user_search']);
        }
        $_POST['num'] = t(trim($_POST['num']));
        $_POST['num'] && $map['num'] = array('like', "%" . $_POST['num'] . "%");
        $_POST['realname'] = t(trim($_POST['realname']));
        $_POST['realname'] && $map['realname'] = array('like', "%" . $_POST['realname'] . "%");
        $this->assign($_POST);

        $db_prefix = C('DB_PREFIX');
        $order = 'id DESC';
        if($_GET['orderKey'] && $_GET['orderType']){
            $order = $_GET['orderKey'].' '.$_GET['orderType'];
            $this->assign('orderKey', $_GET['orderKey']);
            $this->assign('orderType', $_GET['orderType']);
        }
        $map['isDel'] = 0;
        $list = $this->user->table("{$db_prefix}hold_user AS a ")
                ->join("{$db_prefix}hold_user_img AS b ON a.id=b.uid")
                ->join("{$db_prefix}hold_user_flash AS c ON a.id=c.uid")
                ->field('a.* , GROUP_CONCAT(DISTINCT b.id) AS img, GROUP_CONCAT(DISTINCT c.id) AS flash')->group('a.id')
                ->where($map)->order($order)->findPage(10);
        //var_dump($list);die;
        $this->assign($list);
        $this->display();
    }

    public function editUser() {
        $this->assign('type', 'add');
        if ($id = (int) $_REQUEST['id']) {
            $this->assign('type', 'edit');

            $user = $this->user->find($id);
            if (!$user) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("holdUser", $user);
        }
        $this->display();
    }

    public function doEditUser() {
        $id = intval($_REQUEST['id']);
        if (empty($_REQUEST['num'])) {
            $this->error('编号不能为空！');
        } elseif (empty($_REQUEST['realname'])) {
            $this->error('姓名不能为空！');
        }
        $obj = $this->user->field('id,num')->where(array('num'=>t($_REQUEST['num'])))->find();
        if (empty($id)) {
            if($obj){
                $this->error('已有选手使用编号['.$_REQUEST['num'].']，不可重复！');
            }
            $this->insertUser();
        } else {
            if($obj && $obj['id'] != $id){
                $this->error('已有选手使用编号['.$_REQUEST['num'].']，不可重复！');
            }
            $this->updateUser($id);
        }
    }

    public function insertUser() {
        $info = $this->_upload();
        $path = 'user_pic_big.gif';
        if ($info['status'])
            $path = $info['info'][0]['savename'];
            //$this->error("上传出错! " . $info['info']);
        $this->user->path = $path;
        $this->user->num = t($_REQUEST['num']);
        $this->user->realname = t($_REQUEST['realname']);
        $this->user->school = t($_REQUEST['school']);
        $this->user->college = t($_REQUEST['college']);
        $this->user->comment = t($_REQUEST['comment']);
        $this->user->note = intval($_REQUEST['note']);
        $this->user->sex = intval($_REQUEST['sex']);
        $this->user->ticket = intval($_REQUEST['ticket']);
        $this->user->isDel = 0;
        if ($this->user->add()) {
            //成功提示
            $this->assign('jumpUrl', U('/Admin/userList'));
            $this->success('添加成功！');
        } else {
            //失败提示
            $this->error('添加失败！');
        }
    }

    public function updateUser($id) {
        $user = $this->user->find($id);
        if (!$user) {
            $this->error('非法参数，选手不存在！');
        }
        $info = $this->_upload();
        if ($info['status']) {
            $this->user->path = $info['info'][0]['savename'];
        }
        //保存当前数据对象
        $this->user->num = t($_REQUEST['num']);
        $this->user->realname = t($_REQUEST['realname']);
        $this->user->school = t($_REQUEST['school']);
        $this->user->college = t($_REQUEST['college']);
        $this->user->comment = t($_REQUEST['comment']);
        $this->user->note = intval($_REQUEST['note']);
        $this->user->sex = intval($_REQUEST['sex']);
        $this->user->ticket = intval($_REQUEST['ticket']);
        if ($this->user->where("id={$id}")->save()) {
            //保存成功则刷新页面
            $this->assign('jumpUrl', U('/Admin/userList'));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败，或未做改动！！');
        }
    }

    public function put_user_to_recycle() {
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = $this->user->setField('isDel', 1, 'id IN ' . t($gid)); // 通过审核
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

    public function imgList() {
        $id = (int) $_REQUEST['id'];
        $user = $this->user->find($id);
        if (!$user) {
            echo("无法找到选手!");
            return;
        }
        $this->assign("holdUser", $user);
        $map['uid'] = $id;
        $list = $this->img->where($map)->order('`id` DESC')->findAll();
        $this->assign('data', $list);
        $this->assign('uid', $id);
        $this->display();
    }

    public function addImg() {
        $id = (int) $_REQUEST['id'];
        $user = $this->user->find($id);
        if (!$user) {
            echo("无法找到选手!");
            return;
        }
        $this->assign('uid', $id);
        $this->display();
    }

    public function insertImg() {
        $id = (int) $_REQUEST['id'];
        $user = $this->user->find($id);
        if (!$user) {
            echo("无法找到选手!");
            return;
        }
        $info = $this->_upload();
        if (!$info['status'])
            $this->error("上传出错! " . $info['info']);
        $this->img->path = $info['info'][0]['savename'];
        $this->img->uid = $id;
        if ($this->img->add()) {
            //成功提示
            $this->assign('jumpUrl', U('/Admin/imgList', array('id' => $id)));
            $this->success('添加成功！');
        } else {
            //失败提示
            $this->error('添加失败！');
        }
    }

    public function deleteImg() {
        $gid = explode(',', t($_POST ['gid']));
        $map['id'] = array('in', $gid);
        $result = $this->img->where($map)->delete();
        if ($result) {
            //删除成功
            if (!strpos($_REQUEST['gid'], ",")) {
                echo 2;
                exit;         //说明只是删除一个
            } else {
                echo 1;
                exit;            //删除多个
            }
        } else {
            //删除失败
            echo "0";
            exit;
        }
    }

    public function flashList() {
        $id = (int) $_REQUEST['id'];
        $user = $this->user->find($id);
        if (!$user) {
            $this->error('无法找到选手!');
            return;
        }
        $this->assign("holdUser", $user);
        $map['uid'] = $id;
        $list = $this->flash->where($map)->order('`id` DESC')->findAll();
        $this->assign('data', $list);
        $this->assign('uid', $id);
        $this->display();
    }

    public function addFlash() {
        $id = (int) $_REQUEST['id'];
        $user = $this->user->find($id);
        if (!$user) {
            $this->error('无法找到选手!');
            return;
        }
        $this->assign('uid', $id);
        $this->display();
    }

    public function insertFlash() {
        $id = (int) $_REQUEST['id'];
        $user = $this->user->find($id);
        if (!$user) {
            $this->error('无法找到选手!');
            return;
        }
        $link = t($_POST['link']);
        $parseLink = parse_url($link);
        if (preg_match("/(youku.com|ku6.com|sina.com.cn)$/i", $parseLink['host'], $hosts)) {
            $link = getShortUrl($link);
            $addonsData = array();
            Addons::hook("weibo_type", array("typeId" => 3, "typeData" => $link, "result" => &$addonsData));
            $addonsData = unserialize($addonsData['type_data']);
            //var_dump($addonsData);die;
            $addonsData['title'] = preg_replace(array('/—在线播放(.*)/','/ 在线观看(.*)/'), '', $addonsData['title']);
            $this->flash->title = $addonsData['title'];
            $this->flash->path = $addonsData['flashimg'] ? $addonsData['flashimg'] : '';
            $this->flash->flashvar = $addonsData['flashvar'];
            $this->flash->host = $addonsData['host'];
            $this->flash->link = $link;
            $this->flash->uid = $id;
            if ($this->flash->add()) {
                //成功提示
                $this->assign('jumpUrl', U('/Admin/flashList', array('id' => $id)));
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
        $gid = explode(',', t($_POST ['gid']));
        $map['id'] = array('in', $gid);
        $result = $this->flash->where($map)->delete();
        if ($result) {
            //删除成功
            if (!strpos($_REQUEST['gid'], ",")) {
                echo 2;
                exit;         //说明只是删除一个
            } else {
                echo 1;
                exit;            //删除多个
            }
        } else {
            //删除失败
            echo "0";
            exit;
        }
    }

    public function videoList() {
        $list = $this->flash->order('`id` DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function doEditVideo() {
        $link = t($_POST['link']);
        $parseLink = parse_url($link);
        if (preg_match("/(youku.com|ku6.com|sina.com.cn)$/i", $parseLink['host'], $hosts)) {
            $link = getShortUrl($link);
            $addonsData = array();
            Addons::hook("weibo_type", array("typeId" => 3, "typeData" => $link, "result" => &$addonsData));
            $addonsData = unserialize($addonsData['type_data']);
            //var_dump($addonsData);die;
            $addonsData['title'] = preg_replace(array('/—在线播放(.*)/','/ 在线观看(.*)/'), '', $addonsData['title']);
            $this->flash->title = $addonsData['title'];
            $this->flash->path = $addonsData['flashimg'] ? $addonsData['flashimg'] : '';
            $this->flash->flashvar = $addonsData['flashvar'];
            $this->flash->host = $addonsData['host'];
            $this->flash->link = $link;
            $this->flash->uid = 0;
            if ($this->flash->add()) {
                //成功提示
                $this->assign('jumpUrl', U('/Admin/videoList'));
                $this->success('添加成功！');
            } else {
                //失败提示
                $this->error('添加失败！');
            }
        } else {
            $this->error(L('only_support_video'));
        }
    }

    public function photoList() {
        $list = $this->img->where(array('uid' => 0))->order('`id` DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function addPhoto() {
        $this->assign('type', 'add');
        if ($id = (int) $_REQUEST['id']) {
            $this->assign('type', 'edit');
            $photo = $this->img->find($id);
            if (!$photo) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("photo", $photo);
        }
        $this->display();
    }

    public function doEditPhoto() {
        $id = intval($_REQUEST['id']);
        if (empty($_REQUEST['title'])) {
            $this->error("标题不能为空! ");
        } elseif (empty($id)) {
            $this->insertPhoto();
        } else {
            $this->updatePhoto($id);
        }
    }

    public function insertPhoto() {
        $info = $this->_upload();
        if (!$info['status'])
            $this->error("上传出错! " . $info['info']);
        $this->img->title = t($_REQUEST['title']);
        $this->img->path = $info['info'][0]['savename'];
        $this->img->uid = 0;
        if ($this->img->add()) {
            //成功提示
            $this->assign('jumpUrl', U('/Admin/photoList'));
            $this->success('添加成功！');
        } else {
            //失败提示
            $this->error('添加失败！');
        }
    }

    public function updatePhoto($id) {
        $img = $this->img->find($id);
        if (!$img) {
            $this->error('非法参数，照片不存在！');
        }
        $info = $this->_upload();
        if ($info['status']) {
            $this->img->path = $info['info'][0]['savename'];
        }
        //保存当前数据对象
        $this->img->title = t($_REQUEST['title']);
        $this->img->uid = 0;
        if ($this->img->where("id={$id}")->save()) {
            //保存成功则刷新页面
            $this->assign('jumpUrl', U('/Admin/photoList'));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败，或没有改动！');
        }
    }

    public function doChangeStoped() {
        $map['id'] = array('in', t($_REQUEST['id']));        //要用户id
        $act = $_REQUEST['type'];  //更改动作
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "start":   //打开
                $result = $this->user->setField('stoped', 0, $map);
                break;
            case "stop":   //停止
                $result = $this->user->setField('stoped', 1, $map);
                break;
        }
        if (false !== $result) {
            echo 1;
            exit;       //成功
        } else {
            echo -1;
            exit;      //失败
        }
    }

    public function videoAddons() {
        Addons::addonsHook('WeiboType', 'paramUrl');
    }

    public function editVideo() {
        $this->display();
    }

    protected function _upload() {
        //上传参数
        $options['max_size'] = '2000000';
        $options['allow_exts'] = 'jpg,gif,png,jpeg';
        $options['save_path'] = UPLOAD_PATH . '/hold/';
        $options['save_to_db'] = false;
        //$saveName && $options['save_name'] = $saveName;
        //执行上传操作
        $info = X('Xattach')->upload('hold', $options);
        return $info;
    }

}

?>