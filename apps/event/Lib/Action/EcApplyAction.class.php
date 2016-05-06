<?php

/**
 * EcApplyAction
 * 学分申请
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EcApplyAction extends SchoolbaseAction {

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        parent::_initialize();
    }

    //学分申请
    public function ecapply() {
        $this->_checkSchoolUser();
        if($this->sid==480){
            $beginTime = strtotime(date('Y').'-04-01'.' 00:00:00');
            $endTime = strtotime(date('Y').'-04-15'.' 23:59:59');
            if(time()>=$beginTime && time()<=$endTime)
            {
                $summerEvent = 1;
                $this->assign('summerEvent',$summerEvent);
            }
            //南工大文体与创新定时开放及关闭
            if(time() >= strtotime('2018-01-01 00:00:00') && time()<= strtotime('2018-01-01 00:00:00'))
            {
                $artEvent = 1;
                $this->assign('artEvent',$artEvent);
            }

            //南工大社会工作与技能定时开放及关闭
            if(time() >= strtotime('2018-01-01 00:00:00') && time()<= strtotime('2018-01-01 00:00:00'))
            {
                $techEvent = 1;
                $this->assign('techEvent',$techEvent);
            }
            $this->display('list480');
        }else{
            $list = D('EcType')->getEcType($this->sid);
            $this->assign('EcType', $list);
            $audit = D('EcUtype')->applyAudit($this->sid);
            $this->assign('utype', json_encode($audit));
            $this->setTitle('学分申请');
            $this->_rightSide();
            $this->display();
        }
    }
    //学分申请
    public function apply480() {
        if($this->sid != 480){
            $this->error('您无权访问');
        }
        $this->_checkSchoolUser();
        $id = intval($_GET['id']);
        $gd = intval($_GET['gd']);
        if($id==11 && ($gd<1 || $gd>6)){
            $this->display('list480_11');
            die;
        }
        if($id==12 && ($gd<1 || $gd>5)){
            $this->display('list480_12');
            die;
        }
        if($id==12 && $gd==5 && hasZs($this->user['uid'])){
            $this->error('证书只能申请一次，您已申请过');
        }
        $func = $id.'_'.$gd;
        $gdSelectFunc = 'gdSelect'.$func;
        if(!function_exists($gdSelectFunc)){
            $this->error('无效申请类型');
        }
        $this->assign('type', $id);
        $this->assign('gd', $gd);
        $select = $gdSelectFunc();
        $this->assign('topTitle', gdTitle($id,$gd));
        $this->assign('gdRadioFunc', 'gdRadio'.$func);
        $this->assign('select', $select);
        $this->assign('selectNum', count($select));
        $type = D('EcType')->getTypeById($this->sid,$id);
        if(isset($type['description'])){
            $this->assign('description', $type['description']);
        }
        $this->assign('audit', D('EcUtype')->auditById($this->sid,$id));
        $this->display();
    }

    public function doEcApply() {
        $this->_checkSchoolUser();
        if (!canDo($this->mid)) {
            $this->error('操作太频繁!请勿重复提交');
        }
        $dao = D('EcApply');
        $res = $dao->doApply($this->sid, $this->user, $_POST);
        if ($res) {
            doCando($this->mid);
            $this->assign('jumpUrl', U('event/EcApply/myEc'));
            $this->success('申请成功，请等待审核');
        } else {
            $this->error($dao->getError());
        }
    }

    public function myEc(){
        $this->_checkSchoolUser();
        $map['uid'] = $this->mid;
        $list = M('ec_apply')->where($map)->field('id,title,type,credit,status,cTime,rTime')->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }
    public function myDetail(){
        $id = intval($_GET['id']);
        $ecApply = D('EcApply')->getApply($id);
        $desc = '';
        if($ecApply && $ecApply['uid']==$this->mid){
            $desc = $ecApply['description'];
        }
        $this->assign('desc',$desc);
        $this->display();
    }

}
