<?php

/**
 * 爱心校园
 *
 * @version $id$
 * @copyright 2013-2015 张晓军
 * @author 张晓军
 * @license PHP Version 5.3
 */
import('home.Action.PubackAction');

class DonateAction extends PubackAction {

    private $donate;

    public function _initialize() {
        parent::_initialize();
        $this->donate = D('DonateProduct', 'shop');
        $map['isDel'] = 0;
        $map['buyer'] = 0;
        $map['status'] = 0;
        $auditCount = $this->donate->where($map)->count();
        $this->assign('auditCount', $auditCount);
    }

    public function donateList() {
        $map['isDel'] = 0;
        $map['buyer'] = 0;
        $map['status'] = 2;
        $list = $this->donate->where($map)->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function donateOver() {
        $map['isDel'] = 0;
        $map['buyer'] = array('gt', 0);
        $map['status'] = 2;
        $list = $this->donate->where($map)->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function audit() {
        $map['isDel'] = 0;
        $map['buyer'] = 0;
        $map['status'] = 0;
        $list = $this->donate->where($map)->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function doAuditReason() {
        $id = intval($_GET['id']);
        $this->assign('id', $id);
        $del = $_GET['del'] ? 1 : 0;
        $this->assign('del', $del);
        $this->display();
    }

    //jun  驳回
    public function doDismissed() {
        $id = intval($_POST ['id']);
        $reason = t($_POST['reject']);
        $del = intval($_POST['del']);
        if (empty($reason)) {
            $this->ajaxReturn(0, "请填写驳回原因", 0);
        }
        $map['id'] = array('IN', $id);
        $res = $this->donate->where($map)->setField('status', 1);
        if ($del) {
            $this->donate->delDonate($id);
        }
        if ($res) {
            // 发送通知
            $donate = $this->donate->where($map)->field('id,title,uid')->findAll();
            $notify_dao = service('Notify');
            foreach ($donate as $v) {
                $url = $v['title'];
                if (!$del) {
                    $link = U('shop/Donate/editDonate', array('id' => $v['id']));
                    $url = '<a href="' . $link . '">' . $v['title'] . '</a>';
                }
                $notify_data['title'] = $url;
                $notify_data['reason'] = $reason;
                $notify_dao->sendIn($v ['uid'], 'home_delaudit', $notify_data);
            }
            $this->ajaxReturn(0, "驳回成功！", 1);
        } else {
            $this->ajaxReturn(0, "驳回失败！", 0);
        }
    }

    public function pass() {
        $id = intval($_POST ['id']);
        $map['id'] = array('IN', $id);
        $res = $this->donate->where($map)->setField('status', 2);
        if ($res) {
            // 发送通知
            $donate = $this->donate->where($map)->field('id,title,uid')->findAll();
            $notify_dao = service('Notify');
            foreach ($donate as $v) {
                $notify_data['title'] = $v['title'];
                $notify_data['donateId'] = $id;
                $notify_dao->sendIn($v ['uid'], 'home_donate_audit', $notify_data);
            }
            $this->ajaxReturn(0, "通过成功！", 1);
        } else {
            $this->ajaxReturn(0, "通过失败！", 0);
        }
    }

    public function catList() {
        $catList = M('donate_cat')->findAll();
        $this->assign('catList', $catList);
        $this->display();
    }

    public function editCatTab() {
        $id = intval($_GET['id']);
        if ($id) {
            $name = M('donate_cat')->getField('name', "id={$id}");
            $this->assign('id', $id);
            $this->assign('name', $name);
        }
        $this->display();
    }

    public function delCat() {
        $dao = M('donate_cat');
        $gid = is_array($_POST ['gid']) ? implode(',', $_POST ['gid']) : $_POST ['gid']; // 判读是不是数组
        $map['id'] = array('IN', $gid);
        $res = $dao->where($map)->delete();
        if ($res) {
            if (strpos($_POST ['gid'], ',')) {
                echo 1;
            } else {
                echo 2;
            }
        } else {
            echo 0;
        }
    }

    public function doAddType() {
        $isnull = preg_replace("/[ ]+/si", "", t($_POST['name']));
        $daotype = M('donate_cat');
        $name = $daotype->where(array('name' => $isnull))->getField('name');
        if (empty($isnull)) {
            echo -2;
        }
        if ($name !== null) {
            echo 0;
        } else {
            if ($result = $daotype->add($_POST)) {
                echo 1;
            } else {
                echo -1;
            }
        }
    }

    public function doEditType() {
        $_POST['id'] = intval($_POST['id']);
        $_POST['name'] = t($_POST['name']);
        $_POST['name'] = preg_replace("/[ ]+/si", "", $_POST['name']);
        if (empty($_POST['name'])) {
            echo -2;
        }
        $daotype = M('donate_cat');
        $name = $daotype->where(array('name' => t($_POST['name'])))->getField('name');
        if ($name !== null) {
            echo 0; //分类名称重复
        } else {
            if ($result = $daotype->save($_POST)) {
                echo 1; //更新成功
            } else {
                echo -1;
            }
        }
    }

}

?>
