<?php

/**
 * AdminWorkAction
 * 作业管理
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class AdminWorkAction extends TeacherAction {

    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        if (!$this->rights['can_prov_work'] || !isTuanRole($this->sid)) {
            $this->assign('jumpUrl', U('event/Readme/index'));
            $this->error('您没有权限');
        }
        $map['a.autor'] = $this->mid;
        $map['a.status'] = 1;
        $map['b.isDel'] = 0;
        $db_prefix = C('DB_PREFIX');
        $res = M('school_work')->table("{$db_prefix}school_workback AS a ")
                ->join("{$db_prefix}school_work AS b ON a.wid=b.id")
                ->field('b.id')
                ->where($map)->find();
        $this->assign('hasWorkBack', $res);
    }

    public function index() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['es_work_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_work_search']);
        } else {
            unset($_SESSION['es_work_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['title'] = array('like', "%" . $_POST['title'] . "%");
        $this->assign($_POST);
        $map['a.isDel'] = 0;
        $map['a.sid'] = $this->school['id'];
        //非超管，只能看自己发布的
        if(!$this->rights['can_admin']){
            $map['a.uid'] = $this->mid;
        }
        //$res = M('school_work')->where($map)->order('id DESC')->findPage(10);
        $db_prefix = C('DB_PREFIX');
        $res = M('school_work')->table("{$db_prefix}school_work AS a ")
                ->join("{$db_prefix}school_workback AS b ON a.id=b.wid")
                ->field('a.id, a.title, a.cTime, a.eTime, a.uid, group_concat(b.status) as workback')
                ->group('a.id')
                ->where($map)->order('a.id DESC')->findPage(10);
        $this->assign($res);
        $this->display('list');
    }

    public function addWork() {
        $this->assign('type', 'add');
        $this->display('editWork');
    }

    public function doAddWork() {
        //参数合法性检查
        $required_field = array(
            'title' => '作业标题',
            'eTime' => '截止时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $data['title'] = t($_POST['title']);
        $data['description'] = t($_POST['description']);
        $data['cTime'] = time();
        $data['eTime'] = _paramDate($_POST['eTime']);
        $data['uid'] = $this->mid;
        $data['sid'] = $this->school['id'];

        $uid = M('school_work')->add($data);
        if (!$uid) {
            $this->error('抱歉：添加失败，请稍后重试');
            exit;
        }
        $this->assign('jumpUrl', U('event/AdminWork/index'));
        $this->success('添加成功');
    }

    public function editWork() {
        $_GET['id'] = intval($_GET['id']);
        if ($_GET['id'] <= 0)
            $this->error('参数错误');
        $map['sid'] = $this->school['id'];
        $map['id'] = $_GET['id'];
        $work = M('school_work')->where($map)->find();
        if (!$work)
            $this->error('无此作业');
        $this->_acl($work['uid']);
        $this->assign($work);
        $this->assign('type', 'edit');
        $this->display();
    }

    public function doEditWork() {
        $id = intval($_POST['id']);
        if (!$obj = M('school_work')->where(array('id' => $id))->find()) {
            $this->error('作业不存在或已删除');
        }
        $this->_acl($obj['uid']);
        //参数合法性检查
        $required_field = array(
            'title' => '作业标题',
            'eTime' => '截止时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $data['title'] = t($_POST['title']);
        $data['description'] = t($_POST['description']);
        $data['uTime'] = time();
        $data['eTime'] = _paramDate($_POST['eTime']);

        $uid = M('school_work')->where('id = ' . $id)->save($data);
        if (!$uid) {
            $this->error('抱歉：修改失败，请稍后重试');
            exit;
        }
        $this->success('修改成功');
    }

    public function doDeleteWork() {
        $map['id'] = array('in', explode(',', $_REQUEST['id']));    //要删除的id.
        $map['sid'] = $this->school['id'];
        if (empty($map)) {
            echo -1;
        }
        //非超管，只能删自己发布的
        if(!$this->rights['can_admin']){
            $map['uid'] = $this->mid;
        }
        $result = M('school_work')->where($map)->setField('isDel', 1);
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

    public function worklist() {
        $id = intval($_GET['id']);
        if ($id <= 0)
            $this->error('参数错误');
        $map['sid'] = $this->school['id'];
        $map['id'] = $id;
        $work = M('school_work')->where($map)->find();
        if (!$work)
            $this->error('无此作业');
        $this->_acl($work['uid']);
        $this->assign('work', $work);
        $workback = M('school_workback')->where('wid='.$id)->field('id,uid,status,note,cTime')->order('id DESC')->findPage(10);
        $this->assign($workback);
        $this->display();
    }

    public function backlist() {
        $map['a.isDel'] = 0;
        $map['a.sid'] = $this->school['id'];
        $map['b.status'] = 1;
        $map['a.uid'] = $this->mid;
        $db_prefix = C('DB_PREFIX');
        $res = M('school_work')->table("{$db_prefix}school_work AS a ")
                ->join("{$db_prefix}school_workback AS b ON a.id=b.wid")
                ->field('a.id, a.title, a.cTime, a.eTime, a.uid, count(*) as noNote')
                ->group('a.id')
                ->where($map)->order('a.id DESC')->findPage(10);
        $this->assign($res);
        $this->display();
    }

    //作业查看评分
    public function editBack() {
        $id = intval($_GET['id']);
        if ($id <= 0)
            $this->error('参数错误');
        $map['id'] = $id;
        $dao = M('school_workback');
        $workback = $dao->where($map)->find();
        if (!$workback)
            $this->error('无此作业');
        $work = M('school_work')->where('id='.$workback['wid'])->find();
        $this->_acl($work['uid']);
        //修改评分
        if(isset($_POST['note'])){
            $note = intval($_POST['note']);
            $data['note'] = $note;
            $data['status'] = 2;
            $dao->where($map)->save($data);
            $workback['status'] = 2;
            $workback['note'] = $note;
        }

        $this->assign('work', $work);
        $this->assign('workback', $workback);

        //附件
        $attach = array();
        if($workback['attach']){
            $attIds = unserialize($workback['attach']);
            $_attach_map['id'] = array('IN', $attIds);
            $attach = D('WorkAttach', 'event')->field('id,name,fileurl')->where($_attach_map)->findAll();
        }
        $this->assign('attach', $attach);
        $qhMap['wid'] = $workback['wid'];
        $qhMap['id'] = array('gt',$id);
        $prev = $dao->where($qhMap)->field('id')->order('id ASC')->limit(1)->find();
        if($prev){
            $this->assign('prev', $prev['id']);
        }
        $qhMap['id'] = array('lt',$id);
        $next = $dao->where($qhMap)->field('id')->order('id DESC')->limit(1)->find();
        if($next){
            $this->assign('next', $next['id']);
        }
        $this->display();
    }

    private function _acl($workUid, $sid=0){
        if ($workUid != $this->mid && !$this->rights['can_admin'])
            $this->error('无权查看');
//        if($sid && $sid != $this->user['sid']){
//            $this->error('无权查看');
//        }
    }
}
