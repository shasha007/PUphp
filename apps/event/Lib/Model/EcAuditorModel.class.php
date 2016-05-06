<?php

/**
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EcAuditorModel extends Model {

    //后台列表
    public function ecAuditorList($sid) {
        $res = $this->where("sid=$sid")->findPage(10);
        foreach ($res['data'] as &$v) {
            $v['sid1Name'] = tsGetSchoolName($v['sid1']);
            if ($v['sid1Name'] == '') {
                $v['sid1Name'] = '校团委';
            }
        }
        return $res;
    }

    //添加审核人
    public function addEcAuditor($sid) {
        $uid = intval($_POST['uid']);
        $daoUser = D('User','home');
        $user = $daoUser->where("sid=$sid and uid=$uid")->field('uid,sid,sid1,realname')->find();
        if (!$user || $user['sid'] != $sid) {
            $this->error = '用户不存在';
            return false;
        }
        $this->add($user);
        $daoUser->upField($uid,'can_credit',1);
        return true;
    }

    //删除审核人
    public function delEcAuditor($sid) {
        $uid = intval($_POST['uid']);
        $this->where("sid=$sid and uid=$uid")->delete();
        $daoUser = D('User','home');
        $is_admin = getUserField($uid, 'can_admin');
        if(!$is_admin){
            $daoUser->upField($uid,'can_credit',0);
        }
        return true;
    }

    //前台选择院系、审核人
    public function yuanxiList($sid) {
        $list = $this->where("sid=$sid")->field('sid1')->findAll();
        if (!$list) {
            return array();
        }
        foreach ($list as &$v) {
            $v['sName'] = $v['sid1']==0?'校团委':tsGetSchoolName($v['sid1']);
        }
        return $list;
    }
    public function getBySid1($sid,$sid1){
        $list = $this->where("sid=$sid and sid1=$sid1")->field('uid,realname')->findAll();
        if (!$list) {
            return array();
        }
        return $list;
    }
    public function hasAuditor($sid,$uid){
        $has = $this->where("sid=$sid and uid=$uid")->field('uid')->find();
        if($has){
            return true;
        }
        return false;
    }
}

?>