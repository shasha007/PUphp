<?php

/**
 * 导航后台操作
 *
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
import('home.Action.PubackAction');

class DituAction extends PubackAction {

    public function ditus() {
        $limit = 20;
        $order = 'sort ASC,id DESC';
        $items = M('group_ditu_list')->order($order)->findPage($limit);
        $this->assign($items);
        $this->display();
    }

    public function addDitus() {
        $this->assign('type', 'add');
        $this->assign('schools',model('Schools')->_makeTopTree());
        $this->display('editDitus');
    }

    public function editDitus() {

        $map['id'] = intval($_GET['id']);
        $document = D('group_ditu_list')->where($map)->find();
        if (empty($document)) {
            $this->assign('type', 'add');
        } else {
            $this->assign($document);
            $this->assign('type', 'edit');
        }
        $this->display('editDitus');
    }

    public function doEditDitus() {
        $document['id'] = intval($_POST['id']);
        $document['title'] = h(t($_POST['title']));
        $document['desc'] = h(t($_POST['desc']));
        $document['school'] = intval($_POST['school0']);
        $document['sort'] = intval($_POST['sort']);
        if (!$document['title']) {
            $this->error('标题不能为空');
        } else if (get_str_length($_POST['title']) > 50) {
            $this->error('标题不能超过50个字');
        }

        if ($document['school'] > 0) {
            if (null == model('Schools')->getField('id', array('id' => $document['school']))) {
                $this->error('请选择正确的学校分类');
            }
        } else {
            $document['school'] = 0;
        }

        $res = false;
        if ($document['id'] > 0) {
            $res = M('group_ditu_list')->save($document);
        } else {
            unset($document['id']);
            $document['uid'] = $this->mid;
            $res = M('group_ditu_list')->add($document);
        }

        if ($res) {
            $this->assign('jumpUrl', U('/Ditu/ditus'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function ditu() {
        $map['listid'] = intval($_GET['id']);
        $limit = 20;
        $order = 'sort ASC,id DESC';
        $items = M('group_ditu')->where($map)->order($order)->findPage($limit);
        //print_r($items);
        $this->assign($items);

        $listid = intval($_GET['id']);

        $this->assign('listid', $listid);
        $this->display();
    }

    public function addDitu() {
        $this->assign('type', 'add');
        $listid = intval($_GET['listid']);
        $this->assign('listid', $listid);
        $this->display('editDitu');
    }

    public function editDitu() {

        $map['id'] = intval($_GET['id']);
        $document = D('group_ditu')->where($map)->find();
        if (empty($document)) {
            $this->assign('type', 'add');
        } else {
            $this->assign($document);
            $this->assign('type', 'edit');
        }
        $this->display('editDitu');
    }

    public function doEditDitu() {
        $document['listid'] = intval($_POST['listid']);
        $document['id'] = intval($_POST['id']);
        $document['title'] = h(t($_POST['title']));
        $document['lat'] = h(t($_POST['lat']));
        $document['lng'] = h(t($_POST['lng']));
        $document['sort'] = intval($_POST['sort']);
        if (!$document['title']) {
            $this->error('标题不能为空');
        } else if (get_str_length($_POST['title']) > 50) {
            $this->error('标题不能超过50个字');
        }

        $res = false;
        if ($document['id'] > 0) {
            $res = M('group_ditu')->save($document);
        } else {
            unset($document['id']);
            $document['uid'] = $this->mid;
            $res = M('group_ditu')->add($document);
        }

        if ($res) {
            $this->assign('jumpUrl', U('/Ditu/ditu', array('id' => $document['listid'])));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function dodeleteDitu() {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        $map['id'] = array('in', t($_POST['ids']));
        echo M('group_ditu')->where($map)->delete() ? '1' : '0';
    }

    public function dodeleteDitus() {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        $map['id'] = array('in', t($_POST['ids']));
        echo M('group_ditu_list')->where($map)->delete() ? '1' : '0';
    }

}

?>