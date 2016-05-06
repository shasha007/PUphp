<?php

import('home.Action.PubackAction');

class AdminAction extends PubackAction {

//获取首页的数据
    public function index() {
        $map['isDel'] = 0;
        if (!empty($_POST)) {
            $_SESSION['admin_grow_search'] = serialize($_POST);
        } elseif (isset($_GET['p'])) {
            $_SESSION['admin_grow_search_p'] = intval($_GET['p']);
            $_POST = unserialize($_SESSION['admin_grow_search']);
        } else {
            unset($_SESSION['admin_grow_search']);
        }
        $_POST['title'] && $map['title'] = array('like', '%' . t($_POST['title']) . '%');
        $_POST['cid1'] && $map['cid1'] = intval($_POST['cid1']);
        $_POST['cid2'] && $map['cid2'] = intval($_POST['cid2']);
        $list = D('grow_information')->where($map)->field('id,title,cid1,cid2,ctime,rnum')->order('id DESC')->findPage(10);
        $this->assign($list);
        $channellist = $this->getAllChannel();
        $this->assign('clist', $channellist);

        if(($_POST['cid1']&&empty($_POST['cid2']))||$map['cid2']){
            $cat2 = M('grow_categroy')->where('pid=' . $_POST['cid1'])->field('id,name')->select();
            $this->assign('catelist', $cat2);
        }
        $this->display('index');
    }

    //获取所有的频道
    public function getAllChannel() {
        $daocate = M('grow_categroy');
        $list = $daocate->where('pid=0')->select();
        return $list;
    }

    //获取给定频道的所有类别
    public function getAllCategroy() {
        $list = array();
        $pid = $_POST['pid'];
        if($pid>0){
            $daocate = M('grow_categroy');
            $list = $daocate->where('pid=' . $pid)->select();
        }
        echo json_encode($list);
    }

    //修改或添加资讯
    public function addInformation() {
        $list = $this->getAllChannel();
        $this->assign('list', $list);
        $id = intval($_GET['id']);
        if ($id > 0) {
            $daoinfor = D('grow_information');
            $inforlist = $daoinfor->where('id=' . $id)->find();
            $this->assign('inforlist', $inforlist);
            $cat1 = $inforlist['cid1'];
        }else{
            $cat1 = $list[0]['id'];
        }
        $cat2 = M('grow_categroy')->where('pid=' . $cat1)->field('id,name')->select();
        $this->assign('catelist', $cat2);
        $this->display('add');
    }
    //修显示资讯
    public function show() {
        $id = intval($_GET['id']);
        if ($id > 0) {
            $daoinfor = D('grow_information');
            $inforlist = $daoinfor->where('id=' . $id)->find();
            $this->assign($inforlist);
        }
        $this->display();
    }

    public function doadd() {
        //参数合法性检查
        $required_field = array(
            'title' => '标题',
            'content' => '内容',
            'cid1' => '频道',
            'cid2' => '类别',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }
        $id = intval($_POST['id']);
        if (!$id && empty($_FILES['img']["tmp_name"])) {
            $this->error('LOGO文件不可为空');
        }
        if (!empty($_FILES['img']["tmp_name"]) && substr($_FILES['img']['type'], 0, 5) != 'image') {
            $this->error('LOGO文件格式错误');
        }

        $options['userId'] = $this->mid;
        $options['allow_exts'] = '';
        $options['max_size'] = 100 * 1024 * 1024;
        $info = X('Xattach')->upload('grow', $options);

        if ($info['status']) {
            foreach ($info['info'] as $file) {
                if ($file['key'] == "img") {
                    $data['logo'] = $file['savepath'] . $file['savename'];
                } else if ($file['key'] == "attach") {
                    $data['attId'] = $file['id'];
                }
            }
        } else {
            //$this->error($info['info']);
        }
        $data['cid1'] = $_POST['cid1'];
        $data['cid2'] = $_POST['cid2'];
        $data['title'] = t($_POST['title']);
        $data['content'] = t(h($_POST['content']));
        $dao = D('grow_information');
        //编辑
        if ($id > 0) {
            $map['id'] = $id;
            $dao->where($map)->save($data);
            $jumpUrl = U('/Admin/index');
            if($_SESSION['admin_grow_search_p']){
                $jumpUrl .= '&p='.$_SESSION['admin_grow_search_p'];
            }
            $this->assign('jumpUrl', $jumpUrl);
            $this->success('修改成功');
            //添加
        } else {
            $data['ctime'] = time();
            $newid = $dao->add($data);
        }
        if ($newid) {
            $this->assign('jumpUrl', U('/Admin/index'));
            $this->success('添加成功');
        } else {
            $this->error('内部错误');
        }
    }

    //删除资讯
    public function delinformation() {
        $id = intval($_GET['id']);
        if (empty($id)) {
            $this->error('操作失败');
        } else {
            $result = M('grow_information')->setField('isDel',1,'id=' . $id);
        }
        if ($result) {
            $this->success('删除成功');
        }
    }

    //显示频道类别
    public function categroyList() {
        $tree = M('grow_categroy')->field("id,name,pid as pId")->order('display_order ASC')->findAll();
        $this->assign('tree', json_encode($tree));
        $this->display();
    }

    //添加频道类别
    public function addCategroy() {
        if (empty($_POST ['title'])) {
            $this->error('名称不能为空！');
        }
        $cate ['name'] = t($_POST ['title']);
        $cate ['display_order'] = pinyin($cate ['name']);
        $cate ['pid'] = intval($_POST ['pid']); //1级分类
        if(!$cate['pid']==0){
            $res = M('grow_categroy')->where('id='.$cate['pid'])->find();
            if(!$res['pid']==0){
                $this->error('错误操作！');
            }
        }
        $categoryId = M('grow_categroy')->add($cate);
        if ($categoryId) {
            S('Cache_Grow_Category', null);
            $this->success($categoryId);
        } else {
            $this->error('操作失败！');
        }
    }

    //重命名分类
    public function renameCategroy() {
        $id = intval($_POST ['id']);
        $name = t($_POST ['title']);
        if ($name == '') {
            $this->error('名称不能为空！');
        }
        $data['name'] = $name;
        $data ['display_order'] = pinyin($name);
        $res = M('grow_categroy')->where('id=' . $id)->save($data);
        if ($res) {
            S('Cache_Grow_Category', null);
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }

    //删除分类
    public function delCategroy(){
        $id = intval($_POST ['id']);
        if(empty($id)){
            $this->error('没有操作对象！');
        }
        $list = M('grow_categroy')->where('pid=' . $id)->select();
        if($list){
            foreach($list as $val){
                $map['cid2'] = $val['id'];
                $map['isDel'] = 0;
                $res = M('grow_information')->where($map)->select();
                if(!$res){
                    M('grow_categroy')->where('id=' . $val['id'])->delete();
                }
            }
            $clist = M('grow_categroy')->where('pid=' . $id)->select();
            if(!$clist){
                $res = M('grow_categroy')->where('id=' . $id)->delete();
                if($res){
                    S('Cache_Grow_Category', null);
                    $this->success('操作成功！');
                }else{
                    $this->error('操作失败！');
                }
            }else{
                $this->error('所选分类的子分类有资讯信息，无法完全删除！');
//                $this->categroyList();

            }
        }else{
            $map['cid2'] = $id;
            $map['isDel'] = 0;
            $res = M('grow_information')->where($map)->select();
            if($res){
                $this->error('该分类有资讯信息，无法删除！');
            }else{
                $result = M('grow_categroy')->where('id=' . $id)->delete();
                if($result){
                    S('Cache_Grow_Category', null);
                    $this->success('操作成功！');
                }else{
                    $this->error('操作失败！');
                }
            }
        }
    }


}

?>
