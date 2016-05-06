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
class SjAction extends TeacherAction {

    private $map;
    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        $this->map['year'] = C('SJ_YEAR');
        if(!$this->rights['can_admin']){
            echo('无权查看');
            die;
        }
        if(!isSjSchool($this->sid) && $this->sid!=505){
            $this->error('您所在的学校不在十佳评选范围内，不可参加本次活动');
        }
        if($this->sid!=505){
            $this->map['sid'] = $this->sid;
        }
    }

    /**
     * index
     * 首页
     * @access public
     * @return void
     */
    public function index(){
        $this->_searchMap();
        if($this->sid != 505){
            $this->map['status'] = array(1);
        }else{
            $this->map['status'] = array(3);
        }
        $list = M('sj')->where($this->map)->order('id DESC')->findPage(20);
        $this->assign($list);
        $this->assign('searchUrl', U('event/Sj/index'));
        $this->display();
    }
    //已审核的申报
    public function finish(){
        $this->_searchMap();
        if($this->sid != 505){
            $this->map['status'] = array('in','3,4,5');
        }else{
            $this->map['status'] = array(5);
        }
        $list = M('sj')->where($this->map)->order('id DESC')->findPage(20);
        $this->assign($list);
        $this->assign('searchUrl', U('event/Sj/finish'));
        $this->display();
    }
   //驳回的申报
    public function reject(){
        $this->_searchMap();
        if($this->sid != 505){
            $this->map['status'] = array(2);
        }else{
            $this->map['status'] = array(4);
        }
        $list = M('sj')->where($this->map)->order('id DESC')->findPage(20);
        $this->assign($list);
        $this->assign('searchUrl', U('event/Sj/reject'));
        $this->display();
    }

    private function _searchMap() {
        if (!empty($_POST)) {
            $_SESSION['es_searchSj'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_searchSj']);
        } else {
            unset($_SESSION['es_searchSj']);
        }
        if ($this->sid == 505) {
            $sid = intval($_POST['sid']);
            if ($sid) {
                $this->map['sid'] = $sid;
            }
        }
        $type = intval($_POST['type']);
        if($type){
            $this->map['type'] = $type;
        }
    }
    //申报详情
    public function details(){
        $id = intval($_REQUEST['id']);
        $this->map['id'] = $id;
        $obj = M('sj')->where($this->map)->find();
        if(!$obj){
            $this->error('申报不存在');
        }
        $this->assign($obj);
        $img = M('sj_img')->where('sjid='.$id)->field('attachId')->findAll();
        $db_prefix = C('DB_PREFIX');
        $flash = M('flash')->table("{$db_prefix}flash AS a ")
                ->join("{$db_prefix}sj_flash AS b ON a.id=b.flashId")
                ->field('a.id, a.path')
                ->where('sjid='.$id)->order('a.id DESC')->findAll();
        $this->assign('img',$img);
        $this->assign('flash',$flash);
        $template = 'details'.$obj['type'];
        $this->display($template);
    }

    //通过
    public function doPass(){
        $id = intval($_POST['id']);
        $this->map['id'] = $id;
        $obj = M('sj')->where($this->map)->field('uid,title,type')->find();
        if(!$obj){
            $this->error('未找到申报对象');
        }
        $type = $obj['type'];
        //优秀社会实践基地申报（每校限1个）,优秀调研报告申报（每校限报2篇）,十佳风尚奖申报（每校限报2个）
        //$noLimit = array(473,505,664,665,666,667,668,669,670,671,672,673,674,675,676);
        $noLimit = array(473,505);
        if(!in_array($this->sid, $noLimit)){
            $limit = sjTypeLimit($type);
            if($limit){
                $map['sid'] = $this->sid;
                $map['type'] = $type;
                $map['status'] = array('in','3,5');
                $map['year'] = C('SJ_YEAR');
                $has = M('sj')->where($map)->count();
                if($has>=$limit){
                    $this->error(sjType($type).' 每校限报'.$limit.'个');
                }
            }
        }
        $status = 3;
        $msgEnd = '通过了校方审核，等待团省委审核';
        if($this->sid == 505){
            $status = 5;
            $msgEnd = '通过了最终团省委审核';
        }
        $res = M('sj')->where($this->map)->setField('status', $status);
        if($res && $status=5){
            // 发送通知
            $notify_dao = service('Notify');
            $msg = '您的 '.sjType($type).' ['.$obj['title'].'] '.$msgEnd;
            $notify_data = array('title' => $msg);
            $notify_dao->sendIn($obj['uid'], 'event_pass', $notify_data);
            $this->success(sjStatus($status));
        }
        $this->error('操作失败');
    }

    //驳回
    public function doDismissed(){
        $id = intval($_POST['id']);
        $reason = t($_POST['reject']);
        if (empty($reason)) {
            $this->error('请填写驳回原因');
        }
        $this->map['id'] = $id;
        $obj = M('sj')->field('uid,title,type')->find($id);
        if($this->sid == 505){
            $status = 4;
            $res = M('sj')->where($this->map)->setField(array('status','reason'), array($status,$reason));
        }else{
            $status = 2;
            $res = D('Sj')->delSj($this->map);
        }
        if($res){
            // 发送通知
            $notify_dao = service('Notify');
            $msgEnd = '校方初审';
            if($this->sid == 505){
                $msgEnd = '团省委终审';
            }
            $msg = '您的 '.sjType($obj['type']).' ['.$obj['title'].'] '.$msgEnd.' 被驳回';
            $notify_data = array('title' => $msg, 'reason' => $reason);
            $notify_dao->sendIn($obj['uid'], 'event_reject', $notify_data);
            $this->success(sjStatus($status));
        }
        $this->error('操作失败');
    }
    //撤回
    public function back(){
        $id = intval($_POST['id']);
        $status = 1;
        if(intval($_POST['status']) == 3){
            $status = 3;
        }
        $this->map['id'] = $id;
        $res = M('sj')->where($this->map)->setField('status', $status);
        if($res){
            // 发送通知
            $notify_dao = service('Notify');
            $obj = M('sj')->field('uid,title,type')->find($id);
            $msg = '您的 '.sjType($obj['type']).' ['.$obj['title'].'] 被撤回，等待重新审核';
            $notify_data = array('title' => $msg);
            $notify_dao->sendIn($obj['uid'], 'event_pass', $notify_data);
            $this->success(sjStatus($status));
        }
        $this->error('操作失败');
    }
}
