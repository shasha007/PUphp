<?php

class AdminCredit2Action extends TeacherAction {

    public function _initialize() {
        parent::_initialize();
        if (!$this->rights['can_credit']) {
            $this->error('您没有权限');
        }
        $map = array();
        $map['sid'] = $this->sid;
        $map['status'] = 0;
        if (!$this->rights['can_admin']) {
            $map['audit'] = $this->mid;
        }
        $finishCount = D('EcIdentify')->where($map)->count();
        $this->assign('finishCount', $finishCount);
        header('Content-type:text/html ; charset=utf8');
    }
    //待审核
    public function index() {
        if (!$this->rights['can_admin']) {
            $map['audit'] = $this->mid;
        }
        $map['status'] = 0;
        $this->_getACList($map);
        $this->display();
    }
    //已通过
    public function audited() {
        if (!$this->rights['can_admin']) {
            $map['audit'] = $this->mid;
        }
        $map['status'] = 1;
        $this->_getACList($map);
        $this->assign('can_admin',$this->rights['can_admin']);
        $this->assign('schoolId',$this->sid);
        $this->display();
    }
    //审核 申请详情
    public function auditEcApply() {
        $this->_details();
        $map['status'] = 1;
        $map['uid'] = $this->get('applyUid');
        $list = D('EcIdentify')->where($map)->field('id,title,fileId,sid1,uid,audit,realname,credit,finish,cTime,rTime')
                        ->order('id DESC')->findAll();
        foreach ($list as &$v) {
            $v['fileName'] = D('EcFolder')->getFileName($v['fileId']);
        }
        $this->assign('audited',$list);
        $this->display();
    }
    //已通过 申请详情
    public function showEcApply() {
        $this->_details();
        $this->display();
    }

    private function _details() {
        $id = intval($_REQUEST['id']);
        $ecApply = D('EcIdentify')->getApply($this->sid, $id);
        $this->assign($ecApply);
    }

    private function _getACList($map = array(), $orig_order = 'id DESC') {
        $map['sid'] = $this->sid;
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['es_searchAC'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_searchAC']);
        } else {
            unset($_SESSION['es_searchAC']);
        }
        $uid = array();
        if (empty($_POST) && isset($_REQUEST['uid']) && $_REQUEST['uid']) {
            $uid[] = $_REQUEST['uid'];
        }
        if ($_POST['num']) {
            $num = t($_POST['num']);
            if ($num) {
                $mapUser['email'] = $num . $this->school['email'];
                $mapUser['sid'] = $this->sid;
                $userInfo = M('user')->where($mapUser)->field('uid')->find();
                if ($userInfo) {
                    $uid[] = $userInfo['uid'];
                } else {
                    $uid[] = 0;
                }
            }
        }
        if (!$_POST['num'] && $_POST['realname']) {
            $num = t($_POST['realname']);
            if ($num) {
                $mapUser = array();
                $mapUser['realname'] = $num;
                $mapUser['sid'] = $this->sid;
                $userInfo = M('user')->where($mapUser)->field('uid')->findAll();
                if ($userInfo) {
                    foreach ($userInfo as $v) {
                        $uid[] = $v['uid'];
                    }
                } else {
                    $uid[] = 0;
                }
            }
        }
        if (!empty($uid)) {
            $uid = array_unique($uid);
            $map['uid'] = array('in', $uid);
        }
        $list = D('EcIdentify')->where($map)->field('id,title,fileId,sid1,uid,audit,realname,credit,finish,cTime,rTime')
                        ->order($orig_order)->findPage(10);
        foreach ($list['data'] as &$v) {
            $v['fileName'] = D('EcFolder')->getFileName($v['fileId']);
        }
        $this->assign($_POST);
        $this->assign($list);
    }

    //审核通过，发放学分
    public function doAuditEcApply() {
        $id = intval($_REQUEST['id']);
        $note = t($_REQUEST['note']) * 100 / 100;
        $map['id'] = $id;
        $map['sid'] = $this->sid;
        $map['status'] = 0;
        if (!$this->rights['can_admin']) {
            $map['audit'] = $this->mid;
        }
        $webconfig = $this->get('webconfig');
        $creditName = $webconfig['cradit_name'];
        $dao = D('EcIdentify');
        $res = $dao->applyAudit($map, $this->mid, $note, $creditName);
        if (!$res) {
            $this->error($dao->getError());
        }
        $this->success('操作成功');
    }

    /**
     * 删除已经审核的
     *      需要同时退回学分
     */
    public function deleteAudited(){
        $id = intval($_REQUEST['id']);
        if ($id < 1){
            $this->error('ID错误');
        }

        if (!$this->rights['can_admin']) {
            $this->error('只有超级管理员能够进行此操作');
        }

        $dao = D('EcIdentify');
        if ($dao -> cancelCredit($id)){
            //如果取消成功
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }

    }

    public function rejectApply() {
        $id = intval($_GET['id']);
        $this->assign('id', $id);
        $this->display();
    }

    //驳回
    public function doRejectApply() {
        $reason = t($_POST['reject']);
        $id = intval($_POST ['id']);
        if (empty($reason)) {
            $this->error('请填写驳回原因');
        }
        $webconfig = $this->get('webconfig');
        $creditName = $webconfig['cradit_name'];
        $res = D('EcIdentify')->doRejectApply($id, $reason, $creditName);
        if ($res) {
            $this->success('驳回成功');
        } else {
            $this->error(D('EcIdentify')->getError());
        }
    }

    //学时导出
    public function exportAudited()
    {
        set_time_limit(0);
        $sTime = strtotime($_POST['sTime']);
        $eTime = strtotime($_POST['eTime']);
        if(empty($_POST['sTime']) || empty($_POST['eTime']))
        {
            $this->error('时间段参数不完整');
        }
        $sid = $this->sid;
        $map['rTime'] = array('BETWEEN',array($sTime,$eTime));
        $map['sid'] = $sid;
        $map['status'] = 1;
        $lists = M('ec_identify')->field('id,fileId,opt,title,stime,uid,credit,finish,rTime')->where($map)->select();
        foreach($lists as $k=>$v)
        {
            $result[$k]['S_KEY'] = $v['id'];
            $category = $this->getCategoryById($v['fileId']);
            $result[$k]['DL'] = $category['DL'];
            $result[$k]['XL'] = $category['XL'];
            $opt = unserialize($v['opt']);
            $result[$k]['MX'] = join('\n',$this->getOptions($v['fileId'],$opt));
            $result[$k]['MC'] = $v['title'];
            $xn = $this->getXnByStime($v['stime']);
            if($xn['xq'] == 0)
            {
                $result[$k]['XN'] = ($xn['year']-1).'-'.$xn['year'];
                $result[$k]['XQ'] = $xn['xq']+2;
            }
            else
            {
                $result[$k]['XN'] = $xn['year'].'-'.($xn['year']+1);
                $result[$k]['XQ'] = $xn['xq'];
            }
            $email = getUserField($v['uid'],'email');
            $emailArr = explode('@',$email);
            $result[$k]['XH'] = "'".$emailArr[0];
            $result[$k]['XF'] = $v['credit'];
            $result[$k]['SHR'] = getUserField($v['finish'],'realname');
            $result[$k]['SHRQ'] = date('Y年m月d日 H时m分',$v['rTime']);
        }
        $arr = array('S_KEY','DL', 'XL','MX','MC','XN','XQ','XH','XF','SHR','SHRQ');
        array_unshift($result, $arr);
        $name = tsGetSchoolName($sid).'-申请类学分';
        service('Excel')->export2($result, $name);
    }

    private function getCategoryById($id)
    {
        $map['id'] = $id;
        $info =M('ec_folder')->where($map)->find();
        if($info['pid'] !== 0 )
        {
            $p_map['id'] = $info['pid'];
            $p_info = M('ec_folder')->where($p_map)->find();
            $return['DL'] = $p_info['title'];
            $return['XL'] = $info['title'];
        }
        else
        {
            $return['DL'] = $info['title'];
            $return['XL'] = '';
        }
        return $return;
    }

    private function getXnByStime($sTime)
    {
        $year = substr($sTime,0,4);
        $xq = substr($sTime,4,4);
        $return['year'] = $year;
        $return['xq'] = $xq;
        return $return;
    }

    private function getOptions($fileId, $opt)
    {
        $map['fileId'] = $fileId;
        $orderByArr = array(657,654,660);
        $order = 'id DESC';
        if(in_array($fileId,$orderByArr))
        {
            $order = 'id ASC';
        }
        $optionLists = M('ec_input')->where($map)->order($order)->select();
        foreach($opt as $k=>$v){
            $optionsDetail[$k] = $optionLists[$k]['title'].'：有';
            $options[$k] = unserialize($optionLists[$k]['opt']);
            if($options === false)
            {
                $optionsDetail[$k] = $optionLists[$k]['title'].'：'.$v[$k];
            }
            else
            {

                $optionsDetail[$k] = $optionLists[$k]['title'] . '：' . $options[$k][$v][0];
            }

            if($optionLists[$k]['type'] == 4)
            {
                unset($optionsDetail[$k]);
            }
        }
        return $optionsDetail;
    }

}

?>