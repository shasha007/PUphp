<?php

/**
 * SurveyAction
 * 问卷
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class SurveyAction extends SchoolbaseAction {

    public function _initialize() {
        parent::_initialize();
    }

    public function index(){
        $this->setTitle("问卷调查");
        $provId = intval(model('Citys')->getProv($this->school['cityId']));
        $res = M('survey')->where("status=1 and (sid=$this->sid or provId=$provId)")->field('id,title,deadline')->order('id DESC')->findPage(10);
        $this->assign($res);
        $this->display();
    }

    public function detail(){
        $this->setTitle("问卷调查");
        if(!$this->mid){
            $this->error('请先登录！');
        }
        if(!$this->smid){
            $this->error('您不是本校学生！');
        }
        $id = intval($_REQUEST['id']);
        $map['id'] = $id;
        $map['status'] = 1;
        $res = M('survey')->where($map)->find();
        if (!$res) {
            $this->error('问卷不存在或已被删除！');
        }
        $provId = intval(model('Citys')->getProv($this->school['cityId']));
        if($res['sid']!=$this->sid && $res['provId']!=$provId){
            $this->error('问卷不存在或已被删除！');
        }
        $this->assign('survey',$res);
        //是否已参与
        $voted = false;
        if($this->mid){
            $voteMap['uid'] = $this->mid;
            $voteMap['survey_id'] = $id;
            $voted = M('survey_user')->where($voteMap)->count();
        }
        $this->assign("voted", $voted);
        //进行中
        if(!$voted && $res['deadline'] > time()){
            //投票选项
            $vote = getVote($id);
            $this->assign("vote", $vote);
        }
        $this->display();
    }

    public function doSurvey(){
        if(!$this->mid){
            $this->error('请先登录！');
        }
        if(!$this->smid){
            $this->error('您不是本校学生！');
        }
        $this->checkHash();
        $id = intval($_REQUEST['id']);
        $map['id'] = $id;
        $map['status'] = 1;
        $survey = M('survey')->where($map)->field('deadline,sid,provId')->find();
        if (!$survey) {
            $this->error('问卷不存在或已被删除！');
        }
        $provId = intval(model('Citys')->getProv($this->school['cityId']));
        if($survey['sid']!=$this->sid && $survey['provId']!=$provId){
            $this->error('问卷不存在或已被删除！');
        }
        if($survey['deadline'] <= time()){
            $this->error('该调查已结束！');
        }
        //是否重复提交
        $voted = M('survey_user')->where("uid=$this->mid and survey_id=$id")->count();
        if($voted){
            $this->error('您已参与问卷，不可重复提交');
        }
        $mapVote['suid'] = $id;
        $mapVote['isDel'] = 0;
        $vote = D("SurveyVote",'event')->where($mapVote)->field('id,type')->findAll();
        $userOpt = '';
        foreach ($vote as $v) {
            if(!isset($_POST['opt_'.$v['id']][0])){
                $this->error('请确保每个问题至少选择一项');
            }
            $input = $_POST['opt_'.$v['id']];
            //单选
            if($v['type']==0){
                $userOpt .= intval($input[0]).',';
            }else{
                foreach ($input as $value) {
                    $userOpt .= intval($value).',';
                }
            }
        }
        //去掉最后一个,字符
        $userOpt = substr($userOpt, 0, count($userOpt) - 2);
        //写入数据库
        $data['survey_id'] = $id;
        $data['cTime'] = time();
        $data['uid'] = $this->mid;
        $data['sid'] = $this->user['sid'];
        $data['opts'] = $userOpt;
        $daoSurvey = D('Survey');
        $res = $daoSurvey->addUserVote($data);
        if($res){
            $this->assign('jumpUrl', U('event/Survey/index'));
            $this->success('感谢您的参与');
        }else{
            $this->error($daoSurvey->getError());
        }
    }
}
