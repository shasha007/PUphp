<?php

class PlayerAction extends BaseAction {

    public function index() {
        $keyword = msubstr(h($_POST['keyword']),0,10,'utf-8',false);
        //echo $keyword;
        if (strlen($keyword) > 0) {
            $map['realname'] = array('like', "%{$keyword}%");
            $this->assign('keyword', $keyword);
        }
        $map['isDel'] = 0;

        $list = M('show_user')->where($map)->order('ticket DESC')->findPage(8);
        $this->assign($list);
        $url = U('show/player/index');
        $this->assign('url', $url);
        $this->display();
    }

    public function details() {
        $id = intval($_REQUEST['id']);
        $app = M('show_user')->where("`id`={$id} AND isDel=0")->find();
        if (!$app) {
            $this->error('选手不存在或已被删除！');
        }
        $this->assign('app', $app);
        $flash = M('show_user_flash')->where(array('uid' => $id))->findAll();
        $this->assign('flash', $flash);
        $img = M('show_user_img')->where(array('uid' => $id))->order('id DESC')->findAll();
        $this->assign('img', $img);
        $photoOrder = array_flip(getSubByKey($img, 'id'));
        $this->assign('photoOrder',$photoOrder);
        $this->display();
    }

    //ajax 投票
    public function vote() {
        if(intval($this->mid) <= 0){
            echo -3; //未登录
            return;
        }
        //判断选手Id
        $pid = intval($_REQUEST['pid']);
        $app = M('show_user')->where("`id`={$pid} AND isDel=0")->find();
        if (!$app) {
            echo -2; //选手不存在或已删除
            return;
        }
        if($app['stoped']){
            echo -4; //已终止投票
            return;
        }
        $todayTime = strtotime('today');
        //用户每天投票一次
        $dao = M('show_vote');
        $voted = $dao->where(array('mid'=>$this->mid,'cTime'=>$todayTime,'pid'=>$pid))->find();
        if ($voted) {
            echo -1;
            return;
        }else{
            //票数+1
            M('show_user')->where("id={$pid}")->setField('ticket',$app['ticket']+1);
            //用户今天投票结束
            $dao->mid = $this->mid;
            $dao->pid = $pid;
            $dao->cTime = $todayTime;
            $dao->add();
            echo 1;
        }

    }

}

?>