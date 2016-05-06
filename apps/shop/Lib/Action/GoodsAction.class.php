<?php

import('home.Action.PubackAction');

class GoodsAction extends PubackAction {

    //添加商品库
    public function addDepot(){
        $this->display();
    }

    //处理添加商品库
    public function doAddDepot(){
        $data['name'] = t($_POST['name']);
        if(empty($data['name'])){
            $this->error('请填写商品库名称');
        }
        $res = D('shop_depot')->add($data);
        if($res){
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }

    //商品库列表
    public function depotList(){
        $map['isDel'] = 0;
        $list = D('shop_depot')->where($map)->findPage(10);
        $this->assign($list);
        $this->display();

    }

    //商品列表
    public function index(){
        $map['isDel'] = 0;
        $list = D('depot_goods')->where($map)->findPage(10);
        $dao = D('pocket_category');
        foreach($list as $val){
            $res = $dao->where('id='.$val['cid'])->find();
            $val['cid'] = $res['name'];
        }
        $this->assign($list);
        $this->display();
    }

    //删除商品
    public function delGoods(){
        $map['id'] = intval($_GET['id']);
        if(empty($map['id'])){
            $this->error('错误操作');
        }
        $data['isDel'] = 1;
        $res = D('depot_goods')->where($map)->save($data);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    //修改商品信息
    public function addGoods(){
        $id = intval($_GET['id']);
        if($id){
            //修改
            $list = D('depot_goods')->where('id='.$id)->find();
            if(empty($list)){
                $this->error('获取商品信息失败');
            }
            $this->assign('list',$list);
        }
        //添加
        $clist = $this->getAllCate();
        $this->assign('clist',$clist);
        $this->display();
    }

    //处理添加修改商品信息
    public function doAddGoods(){
        $info = tsUploadImg();
        if ($info['status']) {
            $pic = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        } elseif ($info['info'] != '没有选择上传文件') {
            $this->error($info['info']);
        }
        $data['name'] = t($_POST['name']);
        $data['cid'] = intval($_POST['cid']);
        $data['imgs'] = serialize($_REQUEST['imgs']);
        $id = $_POST['id'];
        if($id){
            //修改
            $map['name'] = $data['name'];
            $map['id'] = array('neq',$id);
            $res = D('depot_goods')->where($map)->find();
            if($res){
                $this->error('商品名称已存在，不可重复');
            }
            if($pic){
                $data['pic'] = $pic;
            }
            $result = D('depot_goods')->where('id='.$id)->save($data);
        }else{
            //添加
            $map['name'] = $data['name'];
            $res = D('depot_goods')->where($map)->find();
            if($res){
                $this->error('商品名称已存在，不可重复');
            }
            $data['pic'] = $pic;
            $result = D('depot_goods')->add($data);
        }
        if($result){
            $this->assign('jumpUrl', U('/Goods/index'));
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    //获取所有分类
    public function getAllCate() {
        $daocate = M('pocket_category');
        $map['pid'] = 0;
        $map['isDel'] = 0;
        $list = $daocate->where($map)->order('id ASC')->select();
        return $list;
    }
}

?>

