<?php

/**
 * 用户注册
 *
 * @version $id
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
import('home.Action.PubackAction');

class RegAction extends PubackAction {

    public function index() {
        $list = M('user_reg')->where('status=0')->field('id,realname,sid,number,ctime')->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }
    //已通过的审核列表
    public function pass() {
        $list = M('user_reg')->where('status=1')->field('id,realname,number,sid,uid,audit,email,mobile,mail_send,sms_send,rtime,zj_file')
                ->order('rtime DESC')->findPage(10);
        $daoUser = M('user');
        foreach ($list['data'] as &$v) {
            $v['is_active'] = $daoUser->getField('is_active', 'uid='.$v['uid']);
            $v['thumb'] = tsMakeThumbUp($v['zj_file'],450,550,'f','',false,'register');
        }
        $this->assign($list);
        $this->display();
    }
    //被驳回的审核列表
    public function reject() {
        $list = M('user_reg')->where('status=2')->field('id,realname,uid,sid,audit,mail_send,rtime,zj_file,reason')->order('rtime DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    //注册详细页
    public function audit(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('ID不存在，没找到数据！');
        }
        $user_reg = D('User_reg');
        $res = $user_reg->where('status=0 and id='.$id)->field('id,uid,realname,sid,number,yuanxi,major,year,zj_file,email,mobile,password')->find();
        if(!$res){
            $this->error('ID不存在，没找到数据！');
        }
        $res['school'] = tsGetSchoolName($res['sid']);
        $res['yxName'] = tsGetSchoolName($res['yuanxi']);
        $res['grade'] = model('UserA')->getUserGrade($res['uid']);
        $this->assign('obj',$res);
        $this->assign('nowpage',intval($_GET['nowpage']));
        $this->display();
    }
    //审核通过 新建用户 发邮件给用户
    public function doAudit(){
        $daoReg = model('UserReg');
        $res = $daoReg->doAudit($this->mid);
        if(!$res){
            $this->error($daoReg->getError());
        }
        //跳转返回
        $goBack = U('home/Reg/index');
        $nowpage = intval($_POST['nowpage']);
        if($nowpage){
            $goBack .= '&p='.$nowpage;
        }
        $this->assign('jumpUrl', $goBack);
        $this->success('用户建立成功!');
    }
    
    //审核驳回
    public function doReject(){
        $daoReg = model('UserReg');
        $res = $daoReg->doReject($this->mid);
        if(!$res){
            $this->error($daoReg->getError());
        }
        //跳转返回
        $goBack = U('home/Reg/index');
        $nowpage = intval($_POST['nowpage']);
        if($nowpage){
            $goBack .= '&p='.$nowpage;
        }
        $this->assign('jumpUrl', $goBack);
        $this->success('已驳回!');
    }

}

?>