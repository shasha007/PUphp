<?php

class IndexAction extends BaseAction {

    public function index() {
        $list = M('show_info')->where(array('isDel'=>0))->order('id DESC')->limit(6)->findAll();
        $this->assign("news", $list);
        $list = M('show_user')->where(array('isDel'=>0))->order('ticket DESC')->limit(12)->findAll();
        $this->assign("player", $list);
        $list = M('show_user_img')->where(array('uid'=>0))->order('id DESC')->limit(8)->findAll();
        $this->assign("photo", $list);
        //$list = M('show_user_flash')->where(array('uid'=>0))->order('id DESC')->limit(6)->findAll();
        $list = M('show_user_flash')->order('id DESC')->limit(6)->findAll();
        $this->assign("video", $list);
        $photoOrder = M('show_user_img')->where(array('uid' => 0))->order('id DESC')->field('id')->findAll();
        $photoOrder = array_flip(getSubByKey($photoOrder, 'id'));
        $this->assign('photoOrder',$photoOrder);
        $this->display();
    }

    public function video() {
        $list = M('show_user_flash')->order('id DESC')->findPage(12);
        $this->assign('url', U('show/index/video'));
        $this->assign($list);
        $this->display();
    }

    public function photo() {
        $list = M('show_user_img')->where(array('uid' => 0))->order('id DESC')->findPage(9);
        $this->assign('url', U('show/index/photo'));
        $this->assign($list);
        $photoOrder = M('show_user_img')->where(array('uid' => 0))->order('id DESC')->field('id')->findAll();
        $photoOrder = array_flip(getSubByKey($photoOrder, 'id'));
        $this->assign('photoOrder',$photoOrder);
        $this->display();
    }

    public function playFlash(){
        $id = intval($_REQUEST['id']);
        $app = M('show_user_flash')->where("`id`={$id}")->find();
        if (!$app) {
            $this->error('视频不存在或已被删除！');
        }
        $app['url'] = get_flash_url($app['host'], $app['flashvar']);
        $this->assign($app);
        $this->display();
    }

    public function details(){
        $this->assign('echoFocus',U('/Index/jsonPhoto'));
        $this->display();
    }

    public function jsonPhoto(){
        $dao = M('show_user_img');
        $result = array();
        $result['slide']['title'] = '独show星动力-照片';
        $result['slide']['createtime'] = '2012-11-21 16:16:02';
        $result['slide']['url'] = U('//details');
        $list = $dao->where(array('uid' => 0))->order('id DESC')->findAll();
        foreach ($list as $value) {
            $vo['title'] = $value['title'];
            $vo['intro'] = $value['title'];
            $vo['thumb_50'] = getThumb($value['path'],50,50);
            $vo['thumb_160'] = getThumb($value['path'],160,160);
            $vo['image_url'] = realityImageURL($value['path']);
            $vo['createtime'] = '2012年11月21日 16:16';
            $vo['source'] = '校邮汇';
            $vo['id'] = $value['id'];
            $result['images'][] = $vo;
        }
        $result['next_album']['interface'] = U('/index/jsonPhoto');
        $result['next_album']['title'] = '独show星动力-照片';
        $result['next_album']['url'] = U('/index/details');
        $result['next_album']['thumb_50'] = '';
        echo 'var slide_data = '.json_encode($result);
    }

    public function details2(){
        $id = intval($_GET['id']);
        $this->assign('echoFocus',U('/Index/jsonPhoto2',array('id'=>$id)));
        $this->display('details');
    }

    public function jsonPhoto2(){
        $id = intval($_GET['id']);
        $dao = M('show_user_img');
        $obj = M('show_user')->where("`id`={$id}")->find();
        if (!$obj) {
            $this->error('选手不存在或已被删除！');
        }
        $result = array();
        $result['slide']['title'] = '独show星动力-照片';
        $result['slide']['createtime'] = '2012-11-21 16:16:02';
        $result['slide']['url'] = U('//details2');
        $list = $dao->where(array('uid' => $id))->order('id DESC')->findAll();
        foreach ($list as $value) {
            $vo['title'] = $obj['realname'].' 的靓照';
            $vo['intro'] = $obj['realname'].' 的靓照';
            $vo['thumb_50'] = getThumb($value['path'],50,50);
            $vo['thumb_160'] = getThumb($value['path'],160,160);
            $vo['image_url'] = realityImageURL($value['path']);
            $vo['createtime'] = '2012年11月21日 16:16';
            $vo['source'] = '校邮汇';
            $vo['id'] = $value['id'];
            $result['images'][] = $vo;
        }
        $result['next_album']['interface'] = U('/index/jsonPhoto2',array('id'=>$id));
        $result['next_album']['title'] = '独show星动力-照片';
        $result['next_album']['url'] = U('/index/details2',array('id'=>$id));
        $result['next_album']['thumb_50'] = '';
        echo 'var slide_data = '.json_encode($result);
    }
}

?>