<?php

import('home.Action.PubackAction');

class PtxAction extends PubackAction {


    public function index() {
        $allblock=D('ptx_block');
        $list=$allblock->where('isdel=0')->order('id DESC')->findPage(10);
        $daoList = D('ptx_list');
        foreach ($list['data'] as &$v) {
            if($v['rtime']>0){
                $blockId = $v['id'];
                $v['rnum'] = $daoList->where("block_id=$blockId")->sum('rnum');
            }
        }
        $this->assign($list);
        $this->display();
    }

    public function add(){
        $blockId = intval($_GET['id']);
        $listId = intval($_GET['list_id']);
        $daoPlist = D('ptx_list');
        //编辑内容
        if($listId>0){
            $obj = $daoPlist->where('id='.$listId)->field('id,img,content,block_id,title,isbig')->find();
            if($obj){
                $blockId = $obj['block_id'];
                $_GET['id'] = $blockId;
                $this->assign('obj',$obj);
            }
        }
        if($blockId>0){
            $map['block_id']=$blockId;
            $ptx_list=$daoPlist->where($map)->order('ordernum ASC')->select();

            $daoBlock=D('ptx_block');
            $arr['id']=$blockId;
            $tlist=$daoBlock->where($arr)->select();
            $list=$tlist['0'];

            $this->assign('list',$list);
            $this->assign('ptx_list',$ptx_list);
        }
        $this->display('add');
    }

    public function doadd(){
        //参数合法性检查
        $required_field = array(
            'title' => '标题',
            'content' => '内容',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }
        $listId = intval($_POST['list_id']);
        $info = tsUploadImg();
        if ($info['status']) {
            $data['img'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        } elseif (($listId==0 && $info['info'] == '没有选择上传文件') || $info['info'] != '没有选择上传文件') {
            $this->error($info['info']);
        }
        $data['isbig'] = ($_POST['isbig']==1)?1:2;
        $data['title'] = t($_POST['title']);
        $data['content'] = t(h($_POST['content']));
        $dao = D('ptx_list');
        $blockId = intval($_POST['block_id']);
        //编辑
        if($listId>0){
            $map['id'] = $listId;
            $dao->where($map)->save($data);
            $_GET['id'] = $blockId;
            forword ('add','','',true);
            die;
        }
        if($blockId==0){
            $blockData['ctime']  = time();
            $blockId = M('ptx_block')->add($blockData);
        }
        if(!$blockId){
            $this->error('数据库错误，请联系技术部');
        }
        $data['block_id'] = $blockId;
        $newId = $dao->add($data);
        if($newId){
            $dao->setField('ordernum', $newId, 'id='.$newId);
        }
        $_GET['id'] = $blockId;
        forword ('add','','',true);
        die;
    }

    public function changeOrder() {
        $id = intval($_POST['id']);
        $baseId = intval($_POST['baseid']);
        $dao = D('ptx_list');
        $map['id'] = array('in', $id . ',' . $baseId);
        $orgas = $dao->where($map)->field('id,ordernum')->findAll();
        if (count($orgas) != 2) {
            echo 0;die;
        }
        //转为结果集为array('id'=>'order')的格式
        foreach ($orgas as $v) {
            $orgas[$v['id']] = intval($v['ordernum']);
        }
        //交换order值
        $res = $dao->where('id=' . $id)->setField('ordernum', $orgas[$baseId]);
        if ($res) {
            $res = $dao->where('id=' . $baseId)->setField('ordernum', $orgas[$id]);
        }
        if ($res) {
            echo 1; //更新成功
        } else {
            echo 0;
        }
    }

    //修改block表里面的数据是否发布
    public function edit(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('操作失败');
        }else{
            $daolist=D('ptx_block');
            $map['id']=$id;
            $list=$daolist->where($map)->select();
            $release=$list['0']['release'];
            if($release==1){
                $data['release'] =0;
            }else{
                $data['release'] =1;
            }
            $data['rtime']=time();

            $result=$daolist->where($map)->save($data);
            if($release==1){
                $this->success('成功取消发布');
            }else{

                $this->success('发布成功');
            }
        }
    }

    //删除block表中的数据  修改isdel的值
    public function del(){
        $id = intval($_POST['id']);
        if(empty($id)){
            $this->error('id为空');
        }
        D('ptx_block')->setField('isdel',1,'id='.$id);
        $this->success('删除成功');
    }

}

?>
