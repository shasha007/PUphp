<?php

class IndexAction extends Action {

    public function index() {
        $map['eday'] = array('egt',date('Y-m-d',time()));
        if ($_GET['city']) {
            $_SESSION['train_search']['city'] = t($_GET['city']);
        }
        if ($_GET['prov']) {
            $_SESSION['train_search']['prov'] = t($_GET['prov']);
        }
        if ($_GET['cat'] == 'all') {
            unset($_SESSION['train_search']['cat']);
        } elseif ($_GET['cat']) {
            $_SESSION['train_search']['cat'] = intval($_GET['cat']);
        }
        if ($_GET['org'] == 'all') {
            unset($_SESSION['train_search']['org']);
        } elseif ($_GET['org']) {
            $_SESSION['train_search']['org'] = intval($_GET['org']);
        }
        $areaTitle = '全部';
        $areas = D('TrainArea')->areaArr();
        if ($_SESSION['train_search']['city'] && $_SESSION['train_search']['city'] != 'all') {
            $map['cityId'] = $_SESSION['train_search']['city'];
            $areaTitle = $areas[$map['cityId']]['title'];
        }elseif ($_SESSION['train_search']['prov'] && $_SESSION['train_search']['prov'] != 'all') {
            $map['provinceId'] = $_SESSION['train_search']['prov'];
            $areaTitle = $areas[$map['provinceId']]['title'];
        }
        if ($_SESSION['train_search']['cat']) {
            $map['catId'] = $_SESSION['train_search']['cat'];
        }
        if ($_SESSION['train_search']['org']) {
            $map['orgId'] = $_SESSION['train_search']['org'];
        }
        $this->assign('areaTitle',$areaTitle);
        $list = M('train')->field('id,title,cost,kDate,dauer,contact,catId,cityId,orgId')->order('display_order ASC')->where($map)->findPage(10);
        $this->assign($list);
        $catList = D('TrainCat')->getAllCat();
        $orgList = D('TrainOrg')->getAllOrg();
        $this->assign('catList', $catList);
        $this->assign('orgList', $orgList);
        $collect = D('TrainCollect')->getCollect($this->mid,true);
        $this->assign('collect', $collect);
        $this->display();
    }

    public function area() {
        $provs = D('TrainArea')->getAllArea();
        $first = $provs[0]['id'];
        $citys = D('TrainArea')->getAllArea($first);
        $res = array(array('id'=>$first,'title'=>'全部','pid'=>0,'link'=>U('train/Index/index',array('prov'=>$first,'city'=>'all'))));
        foreach($citys as $v){
            $v['link'] = U('train/Index/index',array('prov'=>$first,'city'=>$v['id']));
            $res[] = $v;
        }
        $this->assign('provs', $provs);
        $this->assign('first',$first);
        $this->assign('citys', $res);
        $this->display();
    }

    public function ajaxArea(){
        $pid = intval($_REQUEST['pid']);
        $citys = D('TrainArea')->getAllArea($pid);
        $res = array(array('id'=>$pid,'title'=>'全部','pid'=>0,'link'=>U('train/Index/index',array('prov'=>$pid,'city'=>'all'))));
        foreach($citys as $v){
            $v['link'] = U('train/Index/index',array('prov'=>$pid,'city'=>$v['id']));
            $res[] = $v;
        }
        exit(json_encode($res));
    }

    public function detail(){
        $rid = intval($_REQUEST['id']);
        if ($rid <= 0) {
            $this->error("错误的访问页面，请检查链接");
        }
        if ($result = D('Train')->course($rid,$this->mid)) {
            $this->assign($result);
            $collectIds = D('TrainCollect')->getCollect($this->mid,true);
            $this->assign('collectIds', $collectIds);
        } else {
            $this->error('该培训课程不存在或已删除');
        }
        $this->display();
    }

    public function collect(){
        $collect = D('TrainCollect')->getCollect($this->mid,true);
        $map['id'] = array('in',$collect);
        $map['eday'] = array('egt',date('Y-m-d',time()));
        $list = M('train')->where($map)->field('id,title,cost,kDate,dauer,contact,catId,cityId,orgId')->order('display_order ASC')->findPage(10);
        $this->assign($list);
        $orgs = D('TrainOrg','train')->getAllOrg();
        $this->assign('orgs', $orgs);
        $this->display();
    }

    public function ajaxIndexCollect(){
        $id = intval($_POST['id']);
        $act = intval($_POST['del']);
        $dao = D('TrainCollect');
        if($id && $act==1){
            if($dao->doCollect($this->mid,$id)){
                $this->success('收藏成功');
            }else{
                $this->error($dao->getError());
            }
        }elseif($id && $act==2){
            if($dao->cancelCollect($this->mid,$id)){
                $this->success('取消收藏成功');
            }else{
                $this->error($dao->getError());
            }
        }else{
            $this->error('参数错误，请检查链接');
        }
    }

}
