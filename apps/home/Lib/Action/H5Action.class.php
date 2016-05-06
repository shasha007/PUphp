<?php

class H5Action extends Action {

    //官方公告详情H5页面
    public function notice(){
        $this->assign('isAdmin', '1');
        $this->assign('mobileJumpBox', 1);
        $id = intval($_REQUEST['id']);
        $obj = M('notice')->where("`id`={$id}")->field('title,content,front,cid,cTime as time')->find();
        if (!$obj) {
            $this->error('公告不存在或已被删除！');
        }
        $this->assign($obj);
        $this->display();
    }
    //校园通知详情H5页面
    public function announce(){
        $this->assign('isAdmin', '1');
        $this->assign('mobileJumpBox', 1);
        $id = intval($_REQUEST['id']);
        $obj = M('announce')->field('title,content,sid1,cid,cTime')
                        ->where("`id`={$id}")->find();
        if (!$obj) {
            $this->error('通知不存在或已被删除！');
        }
        $categorys = D('Category','announce')->__getCategory();
        $obj['category'] = $categorys[$obj['cid']];
        $this->assign($obj);
        $this->display();
    }
    

}
