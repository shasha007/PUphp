<?php
class AnnounceAdminAction extends TeacherAction {

    var $category;

    public function _initialize() {
        parent::_initialize();
        if(!$this->rights['can_announce'])
            $this->error('您没有权限');
        $this->category = D('Category','announce');
    }

    public function index() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_announce_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_announce_search']);
        } else {
            unset($_SESSION['admin_announce_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['title'] = array('like', "%" . $_POST['title'] . "%");
        isset($_POST['cid']) && $_POST['cid'] != '' && $map['cid'] = intval($_POST['cid']);
        $map['sid'] = $this->sid;
        $this->assign($_POST);

        $map['isDel'] = 0;
        $list = M('announce')->where($map)->order('id DESC')->findPage(10);
        $this->assign($list);
        $categorys = $this->category->__getCategory();
        $this->assign('categorys', $categorys);
        $this->display();
    }

    public function editInfo() {
        $this->assign('type', 'add');
        $pid = 1;
        if ($id = (int) $_REQUEST['id']) {
            $this->assign('type', 'edit');
            $info = M('announce')->find($id);
            if (!$info) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("obj", $info);
            $pid = $info['sid'];
        }
        $this->assign('subSchool', M('school')->where('pid = '.$this->sid)->findAll());

        //分类信息
        $categorys = $this->category->__getCategory();
        $this->assign('categorys', $categorys);
        $this->display();
    }

    public function doEditInfo() {
        $id = intval($_REQUEST['id']);
        $title = t($_POST['title']);
        if(empty($title)){
            $this->error('标题不能为空！');
        }
        if($_POST['cid'] == 3 && empty($_POST['sid1'])){
            $this->error('院系通知,请选择院系！');
        }
        if (empty($id)) {
            $this->insertInfo();
        } else {
            $this->updateInfo($id);
        }
    }

    public function insertInfo() {
        $dao = M('announce');
        $dao->title = t($_REQUEST['title']);
        $dao->content = t(h($_POST['content']));
        $dao->sid = $this->sid;
        $to_id = $this->sid;
        $cid = intval($_REQUEST['cid']);
        $dao->cid = $cid;
        if($cid == 3){
            $to_id = intval($_REQUEST['sid1']);
            $dao->sid1 = $to_id;
        }
        $dao->uid = $this->mid;
        $dao->isDel = 0;
        $dao->cTime = time();
        $dao->uTime = time();
        $dao->putstatus = intval($_POST['putstatus']);
        if ($dao->add()) {
            model('Jpush')->addJpush(6,$to_id);
            //成功提示
            $this->assign('jumpUrl', U('/AnnounceAdmin/index'));
            $this->success('添加成功！');
        } else {
            //失败提示
            $this->error('添加失败！');
        }
    }

    public function updateInfo($id) {
        $dao = M('announce');
        $info = $dao->find($id);
        if (!$info) {
            $this->error('非法参数，通知不存在！');
        }
        //保存当前数据对象
        $dao->title = t($_REQUEST['title']);
        $dao->content = t(h($_POST['content']));
        $dao->sid = $this->sid;

        $cid = intval($_REQUEST['cid']);
        $dao->cid = $cid;
        if($cid == 3){
            $dao->sid1 = intval($_REQUEST['sid1']);
        }else{
            $dao->sid1 = 0;
        }

        $dao->uid = $this->mid;
        $dao->uTime = time();
        $dao->putstatus = intval($_POST['putstatus']);
        if ($dao->where("id={$id}")->save()) {
            //保存成功则刷新页面
            $this->assign('jumpUrl', U('/AnnounceAdmin/index'));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    public function put_info_to_recycle() {
        $dao = M('announce');
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = $dao->setField('isDel', 1, 'id IN ' . t($gid)); // 通过审核
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

}

?>