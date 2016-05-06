<?php
/**
 * Created by wenjiping.
 * User: Administrator
 * Date: 2016/1/13
 * Time: 13:53
 */

import('home.Action.PubackAction');

class AdminAction extends PubackAction{


    /**
     * 显示列表
     */
    public function index(){
        $list = M('CheckInTotal')->order('continue_count desc')->findPage();
        $this->assign($list);
        $this->display();
    }

    /**
     * 分类列表
     */
    public function categoryList(){
        $list = M('CheckInType')->findPage();
        $this->assign($list);
        $this->display();
    }

    /**
     * 添加分类
     */
    public function addCategory(){
        $id = intval($_POST['id']);

        $Dao = D('CheckInType');
        $vo = $Dao->create();

        if (!$vo){
            $this->error($Dao->getError());
        }

        if ($id >0){
            $res = $Dao->save();
        }else{
            $res = $Dao->add();
        }

        if ($res){
            $this->success('操作成功');
        }else{
            $this->error('保存失败');
        }
    }

    /**
     * 删除分类
     */
    public function deleteCatetory(){

        $id = intval($_POST['id']);
        if ($id < 1){
            $this->error('ID错误');
        }

        $res = M('CheckInType')->delete($id);
        if ($res){
            $this->success('删除成功');
        }else{
            $this->error('删除失败');
        }
    }

}