<?php

import('home.Action.PubackAction');

class TribeAction extends PubackAction {


    public function index() {
        $dao=D('temporary_tribe');
        $list=$dao->where('status=0')->field('id,uid,name,sid,cid,img,ctime,department')->findPage(10);
        //dump($list);die;
        $arr=$this->editList($list['data']);
        //$daouser=D('user');
        //$ress=$daouser->where('id=33654')->field('id,realname')->find();
        //dump($ress);die;
        $this->assign('list',$arr);
        $this->display();

    }

    public function pass() {
        $dao=D('temporary_tribe');
        $list=$dao->where('status=1')->field('id,uid,sid,name,cid,audit,atime,department')->findPage(10);
        //dump($list);die;
        $arr=$this->editList($list['data']);
        foreach($arr as &$val){
            $val['audit']=getUserName($val['audit']);
        }
        $this->assign('list',$arr);
        $this->display();

    }

    public function reject() {
        $dao=D('temporary_tribe');
        $list=$dao->where('status=2')->field('id,uid,sid,cid,audit,reason,name')->findPage(10);
        //dump($list);die;
        $arr=$this->editList($list['data']);
        foreach($arr as &$val){
            $val['audit']=getUserName($val['audit']);
        }
        $this->assign('list',$arr);
        $this->display();

    }
    //处理数据
    public function editList($arr){
        $daocate = D('group_category');
        foreach($arr as &$val){
            $res= model('Schools')->where('id='.$val['sid'])->field('id,title')->find();
            $val['sid'] = $res['title'];
            $result=$daocate->where('id='.$val['cid'])->field('id,title')->find();
            $val['cid']=$result['title'];
            $val['uid']=getUserName($val['uid']);
        }
        return $arr;
    }

    //审核部落
    public function audit(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('ID不存在，没找到数据！');
        }
        $res = D('temporary_tribe')->where('status=0 and id='.$id)->find();
        if(!$res){
            $this->error('没找到数据！');
        }
        $res['uname'] = getUserName($res['uid']);
        $daocate = D('group_category');
        $result=$daocate->where('id='.$res['cid'])->field('id,title')->find();
        $res['categroy'] = $result['title'];
        $res['school'] = tsGetSchoolName($res['sid']);
        $res['yxName'] = tsGetSchoolName($res['sid1']);
        $this->assign('list',$res);
        $this->assign('nowpage',intval($_GET['nowpage']));
        $this->display();
    }

    //处理审核通过
    public function doAudit(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('ID不存在！');
        }
        $data['audit']=$this->mid;
        $data['atime']=time();
        $data['status']=1;
        $dao = D('temporary_tribe');
        $result = $dao->where('id='.$id)->save($data);
        if($result){
            $res = $dao->where('id='.$id)->find();
            $group['uid']=$res['uid'];
            $group['name']=$res['name'];
            $group['cid0']=$res['cid'];
            $group['category']=$res['department'];
            $group['year']=$res['year'];
            $group['sid1']=$res['sid1'];
            $group['intro']=$res['explain'];
            $group['logo']=$res['img'];
            $group['school']=$res['sid'];
            $group['vStern'] = 1;
            $group['vStatus'] = 1;
            $group['whoDownloadFile'] = 3;
            $group['need_invite'] = 1;
            $group['prov_id'] = model('Schools')->getProvId($res['sid']);
            $group['ctime'] = time();
            $gid = M('group')->add($group);
            if ($gid) {
                //把自己添加到成员里面
                D('EventGroup', 'event')->joinGroup($group['uid'], $gid, 1, $incMemberCount = true);
                //发通知消息
                $notify_data['title'] = $group['name'];
                $notify_data['group_id'] = $gid;
                $notify_dao = service('Notify');
                $notify_dao->sendIn($group['uid'], 'event_group_init', $notify_data);
                $this->assign('jumpUrl',U('home/Tribe/pass'));
            } else {
                $this->error('创建失败');
            }

        }else{
            $this->error('审核失败');
        }
    }

    public function doReject(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('ID不存在！');
        }
        $data['status']=2;
        $data['reason']=t($_POST['reason']);
        if(empty($data['reason'])){
            $this->error('请填写驳回原因！');
        }
        $dao = D('temporary_tribe');
        $result = $dao->where('id='.$id)->save($data);
        if($result){
            $this->assign('jumpUrl',U('home/Tribe/reject'));
        }else{
            $this->error('操作失败！');
        }
    }

    //已审核过的详情
    public function detail(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('ID不存在，没找到数据！');
        }
        $res = D('temporary_tribe')->where('id='.$id)->find();
        if(!$res){
            $this->error('没找到数据！');
        }
        $res['uname'] = getUserName($res['uid']);
        $daocate = D('group_category');
        $result=$daocate->where('id='.$res['cid'])->field('id,title')->find();
        $res['categroy'] = $result['title'];
        $res['school'] = tsGetSchoolName($res['sid']);
        $res['yxName'] = tsGetSchoolName($res['sid1']);
        $this->assign('list',$res);
        $this->display();
    }
}

?>
