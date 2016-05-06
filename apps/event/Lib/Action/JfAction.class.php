<?php

/**
 * JfAction
 * 积分商城管理
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class JfAction extends TeacherAction {

    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        if ($this->rights['can_gift'] != 1) {
            $this->assign('jumpUrl', U('event/Readme/index'));
            $this->error('您没有权限管理积分商城！');
        }
    }

    public function index() {
        $code = $_REQUEST['code'];
        if ($code) {
            $this->assign('code', $code);
            $map['sid'] = $this->sid;
            $map['isGet'] = 0;
            $map['code'] = $code;
            $res = D('Jfdh')->getList($map);
            $this->assign($res);
        }
        $this->display();
    }

    public function giftlist() {
        $map['isDel'] = 0;
        $map['sid'] = $this->sid;
        $res = M('jf')->where($map)->order('id DESC')->findPage(10);
        $this->assign($res);
        $this->display();
    }

    public function doDeleteGift() {
        $eventid['id'] = array('in', explode(',', $_REQUEST['id']));    //要删除的id.
        if (empty($eventid)) {
            echo -1;
        }
        $result = M('jf')->where($eventid)->setField('isDel', 1);
        ;
        if (false != $result) {
            Mmc('has_Jf_Product_'.$this->sid,null);
            if (!strpos($_REQUEST['id'], ",")) {
                echo 2;            //说明只是删除一个
            } else {
                echo 1;            //删除多个
            }
        } else {
            echo -1;               //删除失败
        }
    }

    //添加用户物品
    public function addGift() {
        $this->assign('type', 'add');
        $this->display('editGift');
    }

    public function doAddGift() {
        //参数合法性检查
        $required_field = array(
            'title' => '物品名称',
            'cost' => '所需积分',
            'number' => '物品数量',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        if (!is_numeric($_POST['cost'])) {
            $this->error('所需积分只能是整数');
        }
        if (!is_numeric($_POST['number'])) {
            $this->error('物品数量只能是整数');
        }

        $_POST['title'] = t($_POST['title']);
        $_POST['content'] = t(h($_POST['content']));
        $_POST['number'] = intval($_POST['number']);
        $_POST['cost'] = intval($_POST['cost']);
        $_POST['cTime'] = time();
        $_POST['sid'] = $this->sid;
        //得到上传的图片
        $cover = eventUpload();
        if ($cover['status']) {
            $_POST['path'] = $cover['info'][0]['savepath'].$cover['info'][0]['savename'];
        }elseif($cover['info']!='没有选择上传文件'){
            $this->error($cover['info']);
        }
        if ($_POST['__hash__'])
            unset($_POST['__hash__']);

        $uid = M('jf')->add($_POST);
        if (!$uid) {
            $this->error('抱歉：添加失败，请稍后重试');
            exit;
        }
        Mmc('has_Jf_Product_'.$this->sid,true,0,3600*24);
        $this->success('添加成功');
    }

    public function editGift() {
        $_GET['id'] = intval($_GET['id']);
        if ($_GET['id'] <= 0)
            $this->error('参数错误');
        $map['sid'] = $this->sid;
        $map['id'] = $_GET['id'];
        $gift = M('jf')->where($map)->find();
        if (!$gift)
            $this->error('无此物品');
        $this->assign($gift);
        $this->assign('type', 'edit');
        $this->display();
    }

    public function doEditGift() {
        $id = intval($_POST['id']);
        if (!$obj = M('jf')->where(array('id' => $id))->find()) {
            $this->error('物品不存在或已删除');
        }
        //参数合法性检查
        $required_field = array(
            'title' => '物品名称',
            'cost' => '所需积分',
            'number' => '物品数量',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        if (!is_numeric($_POST['cost'])) {
            $this->error('所需积分只能是整数');
        }
        if (!is_numeric($_POST['number'])) {
            $this->error('物品数量只能是整数');
        }

        $_POST['title'] = t($_POST['title']);
        $_POST['content'] = t(h($_POST['content']));
        $_POST['number'] = intval($_POST['number']);
        $_POST['cost'] = intval($_POST['cost']);
        $_POST['uTime'] = time();
        //得到上传的图片
        $cover = eventUpload();
        if ($cover['status']) {
            $_POST['path'] = $cover['info'][0]['savepath'].$cover['info'][0]['savename'];
        }elseif($cover['info']!='没有选择上传文件'){
            $this->error($cover['info']);
        }
        if ($_POST['__hash__'])
            unset($_POST['__hash__']);

        $uid = M('jf')->where('id = ' . $id)->save($_POST);
        if (!$uid) {
            $this->error('抱歉：修改失败，请稍后重试');
            exit;
        }
        $this->success('修改成功');
    }

    //推荐操作
    public function doChangeIsHot() {
        $map['id'] = array('in', $_REQUEST['id']);        //要推荐的id.
        $act = $_REQUEST['type'];  //推荐动作
        if (empty($map)) {
            $result = false;
        } else {
            switch ($act) {
                case "open":   //推荐
                    $result = M('jf')->where($map)->setField('isHot', 1);
                    break;
                case "cancel":   //取消推荐
                    $result = M('jf')->where($map)->setField('isHot', 0);
                    break;
            }
        }
        if (false != $result) {
            echo 1;            //推荐成功
        } else {
            echo -1;               //推荐失败
        }
    }

    //置顶操作
    public function doChangeIsTop() {
        $map['id'] = array('in', $_REQUEST['id']);        //要推荐的id.
        $act = $_REQUEST['type'];  //推荐动作
        if (empty($map)) {
            $result = false;
        } else {
            switch ($act) {
                case "open":   //推荐
                    $result = M('jf')->where($map)->setField('isTop', 1);
                    break;
                case "cancel":   //取消推荐
                    $result = M('jf')->where($map)->setField('isTop', 0);
                    break;
            }
        }
        if (false != $result) {
            echo 1;            //推荐成功
        } else {
            echo -1;               //推荐失败
        }
    }

    public function dh() {
        $map['sid'] = $this->sid;
        $res = D('Jfdh')->getList($map);
        $this->assign($res);
        $this->display();
    }

    public function linqu() {
        $id = $_REQUEST['id'];
        if(!$id){
            $this->error('操作失败');
        }
        $map['id'] = $id;
        $map['isGet'] = 0;
        $map['sid'] = $this->sid;
        if (D('Jfdh')->linqu($map)) {
            $this->success('操作成功,物品领取完毕');
        }
        $this->error('操作失败');
    }

    public function doEditGiftAddress(){
        $data['gift_address'] = t($_POST['address']);
        if (empty($data['gift_address'])) {
            echo 0;
        }
        $data['sid'] = $this->sid;
        $res = D('SchoolWeb','event')->editSchoolWeb($data);
        if ($res) {
            echo 1; //更新成功
        } else {
            echo -1;
        }
    }

}
