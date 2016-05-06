<?php

class AnnounceAction extends SchoolbaseAction {


    public function _initialize() {
        parent::_initialize();
        $categorys = D('Category','announce')->__getCategory();
        $this->assign('categorys', $categorys);
        $this->assign('eventPage', 'announce');
        $this->setTitle('校内通知');
    }

    /**
     * index
     * @access public
     * @return void
     */
    public function index() {
        $keyword = msubstr(h($_GET['keyword']),0,10,'utf-8',false);
        //echo $keyword;
        if (strlen($keyword) > 0) {
            $map['title'] = array('like', "%{$keyword}%");
            $this->assign('keyword', $keyword);
        } else if (isset($_GET['keyword'])) {
            $this->error('请输入搜索条件！');
        }

        $cid = intval($_GET['cid']);
        if($cid>0){
            $map['cid'] = $cid;
            if($cid==3&&$this->user['sid1']){
                 $map['sid1'] = $this->user['sid1'];
            }
            $this->assign('cid', $cid);
        }
        $map['sid'] = $this->sid;
        $map['isDel'] = 0;
        $list = M('announce')->where($map)->order('id DESC')->findPage(10);
        $this->assign('list', $list);
        $this->display();
    }

    public function details() {
        $id = intval($_REQUEST['id']);
        $obj = M('announce')->where("`id`={$id} AND isDel=0")->find();
        if (!$obj) {
            $this->error('通知不存在或已被删除！');
        }
        $this->assign('obj', $obj);
        $cid = intval($_GET['cid']);
        if($cid>0){
            $this->assign('cid', $cid);
        }
        $this->display();
    }


}

?>
