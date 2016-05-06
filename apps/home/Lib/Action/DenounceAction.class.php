<?php

/**
 *
 *
 * @version $id$
 * @copyright 2013-2015张晓军
 * @author 张晓军
 * @license PHP Version 5.3
 */
import('home.Action.PubackAction');

class DenounceAction extends PubackAction {

    public function _initialize() {
        parent::_initialize();
    }

    /**
     * 举报管理
     */
    public function index() {
        $_GET['id'] && $map['id'] = array('in', explode(',', $_GET['id']));
        $_GET['uid'] && $map['uid'] = array('in', explode(',', $_GET['uid']));
        $_GET['fuid'] && $map['fuid'] = array('in', explode(',', $_GET['fuid']));
        $_GET['from'] && $map['from'] = $_GET['from'];
        $map['state'] = $_GET['state'] ? $_GET['state'] : '0';
        $data = model('Denounce')->getFromList($map);
        $data['state'] = $map['state'];
        $this->assign($data);
        if (is_array($map) && sizeof($map) == '1')
            unset($map);
        $this->assign($_GET);
        $this->assign('isSearch', empty($map) ? '0' : '1');
        $this->display();
    }



    public function doDeleteDenounce() {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        echo model('Denounce')->deleteDenounce(t($_POST['ids'])) ? '1' : '0';
    }

    public function doReviewDenounce() {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        echo model('Denounce')->reviewDenounce(t($_POST['ids'])) ? '1' : '0';
    }

    public function detail() {
        $id = intval($_GET['id']);
        if ($id <= 0) {
            $this->error('链接不存在');
        }
        $data = model('Denounce')->find($id);
        $user = M('user')->where('uid='.$data['uid'])->field('email,year')->find();
        $fuser = M('user')->where('uid='.$data['fuid'])->field('email,year')->find();
        $this->assign($data);
        $this->assign('user',$user);
        $this->assign('fuser',$fuser);
        $this->display();
    }
    public function feedback() {
        $state = intval($_GET['state']);
        //未处理
        if($state==1){
            $data = M('feedback')->where('fid=0')->order('id DESC')->findPage();
        //已处理
        }elseif($state==2){
            $data = M('feedback')->where('fid!=0')->order('id DESC')->findPage();
        }else{
            $data = M('feedback')->order('id DESC')->findPage();
        }
        $this->assign($data);
        $this->display();
    }

    public function talkMessage(){
        $uid = intval($_GET['uid']);
        $toid = intval($_GET['toid']);
        $message = array();
        if($uid>0 && $toid>0){
            $minMax = $uid.'_'.$toid;
            if($toid<$uid){
                $minMax = $toid.'_'.$uid;
            }
            $listId = M('message_list')->getField('list_id',"from_uid=$uid and min_max='$minMax'");
            if($listId){
                $message = M('message_content')->where("list_id=$listId")->field('from_uid,content')->order('message_id asc')->findAll();
            }
        }
        $this->assign('message',$message);
        $this->display();
    }

    public function doDeleteFeedBack() {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        echo M('feedback')->delete(intval($_POST['ids'])) ? '1' : '0';
    }

    public function doFinishFeedBack(){
        if(!$this->mid){
            $this->error('请先登录！');
        }
        $ids = t($_POST['ids']);
        $map['id'] = array('in', $ids);
        $map['fid'] = 0;
        $data['fid'] = $this->mid;
        $data['rtime'] = time();
        $res = M('feedback')->where($map)->save($data);
        if($res){
            $return['status'] = 1;
            $return['info'] = '操作成功！';
            $return['realname'] = $this->user['realname'];
            echo json_encode($return);die;
        }
        $this->error('操作失败！');
    }
    //用户反馈导出
    public function feedExcel(){
        set_time_limit(0);
        $calcMon = $this->_calcMon(t($_POST['mon']));
        $mon = $calcMon['mon'];
        $stime = $calcMon['stime'];
        $etime = $calcMon['etime'];
        $file = SITE_PATH.'/data/tj/feedback_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.'用户反馈.xls');
        }
        $list = M('')->table('ts_feedback as a')->join('ts_user as b on a.uid=b.uid')->where("a.ctime>=$stime and a.ctime<=$etime")
                ->field('a.id,b.realname,b.sid,a.content,a.contact,a.ctime')->findAll();
        if(empty($list)){
            $this->error('搜寻结果为空');
        }
        foreach($list as &$v){
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['content'] = htmlspecialchars_decode($v['content']);
            $v['ctime'] = date('Y-m-d H:i', $v['ctime']);
        }
        $arr = array('ID', '反馈人', '学校', '反馈内容', '联系方式', '反馈时间');
        array_unshift($list, $arr);
        //当前月份不保存
        if($mon==date('Y-m')){
            service('Excel')->export2($list, $mon.'用户反馈');
            die;
        }
        service('Excel')->exportFile($list, $mon.'用户反馈',$file);
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.'用户反馈.xls');
        }else{
            $this->error('生成文件错误');
        }
    }
    private function _calcMon($input){
        if(!$input){
            $this->error('请输入月份');
        }
        $res['stime'] = strtotime($input.'-01');
        if(!$res['stime']){
            $this->error('请输入月份');
        }
        $res['mon'] = date('Y-m',$res['stime']);
        $res['etime'] = strtotime('+1 month', $res['stime'])-1;
        return $res;
    }
    //禁言列表
    public function gag() {
        $data = M('user_gag')->order('ctime DESC')->findPage(10);
        $this->assign($data);
        $this->display();
    }
    //解除禁言
    public function freeGag() {
        $uid = intval($_POST['uid']);
        $type = intval($_POST['type']);
        $res = M('user_gag')->where("uid=$uid and type=$type")->delete();
        if(!$res){
            $this->error('解除禁言失败');
        }
        $this->success('解除禁言成功');
    }
    //增加禁言
    public function doAddGag(){
        $uid = intval($_POST['uid']);
        if(!$uid){
            $this->error('请输入用户uid');
        }
        $type = intval($_POST['type']);
        if($type!=1){
            $this->error('禁言种类错误');
        }
        $data['uid'] = $uid;
        $data['type'] = $type;
        $data['ctime'] = time();
        $res = M('user_gag')->add($data);
        if(!$res){
            $this->error('操作失败，或该用户已禁言，不可重复');
        }
        $this->assign('jumpUrl', U('home/Denounce/gag'));
        $this->success('禁言成功');
    }
}

?>