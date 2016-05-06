<?php

/**
 * AdminSurveyAction
 * 问卷调查管理
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class AdminSurveyAction extends TeacherAction {

    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        if (!$this->rights['can_admin']) {
            $this->assign('jumpUrl', U('event/Readme/index'));
            $this->error('您没有权限');
        }
    }

    public function index() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['es_survey_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_survey_search']);
        } else {
            unset($_SESSION['es_survey_search']);
        }
        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['title'] = array('like', $_POST['title'] . "%");
        $this->assign($_POST);
        $map['status'] = array('neq',2);
        $map['sid'] = $this->school['id'];
        $res = M('survey')->where($map)->order('id DESC')->findPage(10);
        $this->assign($res);
        $this->display('list');
    }

    public function addSurvey() {
        $this->assign('type', 'add');
        $this->display();
    }

    public function doAddSurvey() {
        $this->checkHash();
        //参数合法性检查
        $required_field = array(
            'title' => '标题',
            'deadline' => '截止时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $data['title'] = t($_POST['title']);
        $data['explain'] = t($_POST['explain']);
        $data['deadline'] = _paramDate($_POST['deadline']);
        $data['cTime'] = time();
        $data['uid'] = $this->mid;
        $data['sid'] = $this->school['id'];
        $data['provId'] = 0;
        if($_POST['isProv']){
            $data['provId'] = intval(model('Citys')->getProv($this->school['cityId']));
        }
        $dao = M('survey');
        $id = $dao->add($data);
        if (!$id) {
            $this->error('抱歉：添加失败，请稍后重试');
            exit;
        }
        $this->assign('jumpUrl', U('event/AdminSurvey/edit',array('id'=>$id)));
        $this->success('操作成功，请添加问卷问题');
    }

    public function edit() {
        $id = intval($_GET['id']);
        if ($id <= 0)
            $this->error('参数错误');
        $map['sid'] = $this->school['id'];
        $map['id'] = $id;
        $survey = M('survey')->where($map)->find();
        if (!$survey)
            $this->error('无此问卷');
        $this->assign('survey',$survey);
        $mapVote['suid'] = $id;
        $mapVote['isDel'] = 0;
        $vote = M('survey_vote')->where($mapVote)->findAll();
        $dao = M('survey_opt');
        foreach ($vote as $k=>$v) {
            $vote[$k]['opt'] = $dao->where('vote_id='.$v['id'])->field('name')->findAll();
        }
        $this->assign('vote',$vote);
        $this->assign('cnt',count($vote));
        $this->display();
    }

    public function editSurvey() {
        $_GET['id'] = intval($_GET['id']);
        if ($_GET['id'] <= 0){
            echo('参数错误');return;
        }
        $map['sid'] = $this->school['id'];
        $map['id'] = $_GET['id'];
        $survey = M('survey')->where($map)->field('id,title,`explain`,deadline,provId')->find();
        if (!$survey){
            echo('无此问卷');return;
        }
        $this->assign($survey);
        $this->display();
    }

    public function doEditSurvey() {
        $id = intval($_POST['id']);
        $map['sid'] = $this->school['id'];
        $map['id'] = $id;
        $survey = M('survey')->where($map)->field('id,provId')->find();
        if (!$survey) {
            $this->error('问卷不存在或已删除');
        }
        //参数合法性检查
        $required_field = array(
            'title' => '问卷标题',
            'deadline' => '截止时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $data['title'] = t($_POST['title']);
        $data['explain'] = t($_POST['explain']);
        $data['deadline'] = strtotime($_POST['deadline']);
        $data['rTime'] = time();
        $data['provId'] = 0;
        if($_POST['isProv']){
            $data['provId'] = intval(model('Citys')->getProv($this->school['cityId']));
        }
        $res = M('survey')->where('id = ' . $id)->save($data);
        if (!$res) {
            $this->error('抱歉：修改失败，请稍后重试');
            exit;
        }
        $this->success('修改成功');
    }

    //添加问题弹窗
    public function addVote() {
        $id = intval($_GET['id']);
        $this->assign('id',$id);
        $this->display();
    }

    public function doAddVote(){
        $data['suid'] = intval($_POST['id']);
        $map['id'] = $data['suid'];
        $map['sid'] = $this->school['id'];
        if (!$obj = M('survey')->where($map)->field('id')->find()) {
            $this->error('问卷不存在或已删除');
        }
        $data['title'] = t($_POST['title']);
        $data['type'] = intval($_POST['type']);
        $opt = $_POST['opt'];
        $voteDao = D("SurveyVote");
        try{
            $result = $voteDao->addVote($data,$opt);
        }catch(ThinkException $e){
            $this->error($e->getMessage());
        }
        if($result){
            Mmc('Survey_vote_'.$data['suid'],null);
            $this->success($result);
        }
    }

    public function changeStatus(){
        $id = intval($_POST['id']);
        $map['sid'] = $this->school['id'];
        $map['id'] = $id;
        $dao = M('survey');
        $survey = $dao->where($map)->field('id')->find();
        if (!$survey) {
            $this->error('问卷不存在或已删除');
        }
        $status = intval($_POST['status']);
        $res = false;
        //激活，检查是否有选项
        if($status == 1){
            $mapVote['suid'] = $id;
            $mapVote['isDel'] = 0;
            $vote = M('survey_vote')->where($mapVote)->field('id')->find();
            if(!$vote){
                echo 2;exit;
            }
        }
        if($status == 1 || $status == 2){
            $res = $dao->where($map)->setField('status', $status);
        }
        if($res){
            echo 1;exit;
        }
        echo 0;exit;
    }

    public function survey(){
        $id = intval($_REQUEST['id']);
        $map['sid'] = $this->school['id'];
        $map['id'] = $id;
        $dao = M('survey');
        $survey = $dao->where($map)->find();
        if (!$survey) {
            $this->error('问卷不存在或已删除');
        }
        $this->assign('survey',$survey);
        $this->assign("filterId", 0);
        $this->assign("vote", getVote($id));
        $this->assign("vote_pers", getVotePers($id));
        $this->display();
    }

    public function doDeleteSurvey() {
        $map['id'] = array('in', explode(',', $_REQUEST['id']));    //要删除的id.
        $map['sid'] = $this->school['id'];
        if (empty($map)) {
            echo -1;
        }
        $result = M('survey')->where($map)->setField('isDel', 1);
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

    public function deleteVote() {
        $id = intval($_REQUEST['id']);
        $map['id'] = $id;
        $dao = M('survey_vote');
        $vote = $dao->where($map)->field('suid')->find();
        if (!$vote) {
            $this->error('题目不存在或已删除');
        }
        $mapSurvey['id'] = $vote['suid'];
        $mapSurvey['sid'] = $this->school['id'];
        $survey = M('survey')->where($mapSurvey)->field('id')->find();
        if (!$survey) {
            $this->error('无权查看');
        }
        $result = $dao->where($map)->setField('isDel',1);
        if (false != $result) {
            Mmc('Survey_vote_'.$survey['id'],null);
            echo 1;            //删除多个
        } else {
            echo 0;               //删除失败
        }
    }
    //问卷调查参与人导出
     public function excel() {
        set_time_limit(0);
        if (!intval($_GET['p'])) {
            $page = 1;
        } else {
            $page = intval($_GET['p']);
        }
        $limit = 5000;
        $offset = ($page - 1) * $limit;
        $id = intval($_GET['id']);
        $daoUser = M('survey_user');
        $list = $daoUser
                ->where('survey_id =' . $id)
                ->field('uid,cTime')
                ->limit("$offset,$limit")
                ->select();
        $dao = M('user');
        foreach ($list as $k => $v) {
            $user = $dao->where('uid='.$v['uid'])->field('email,mobile')->find();
            $list[$k]['school'] = tsGetSchoolByUid($v['uid']);
            $list[$k]['email'] = "'".getUserEmailNum($user['email']);
            $list[$k]['mobile'] = $user['mobile'];
            $list[$k]['uid'] = getUserRealName($v['uid']);
            $list[$k]['time'] = date('Y-m-d h:i', $v['cTime']);
            unset( $list[$k]['cTime']);
        }
        $title = M('survey')->getField('title', 'id=' . $id);
        closeDb();
        $arr = array('姓名','学校','学号','联系方式','时间');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);
    }

}
