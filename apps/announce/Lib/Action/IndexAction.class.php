<?php

class IndexAction extends Action {

    var $sid;

    public function _initialize() {
        if (!isset($_GET['school'])) {
            if(!isset($_SESSION['announce_sid'])){
                $user = $this->get('user');
                $_SESSION['announce_sid'] = $user['sid'];
            }
        } else {
            $_SESSION['announce_sid'] = intval($_GET['school']);
        }
        $this->assign('school', tsGetSchoolNameById($_SESSION['announce_sid']));

        $categorys = D('Category')->__getCategory();
        $this->assign('categorys', $categorys);
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
            $this->assign('cid', $cid);
        }
        $map['sid'] = $_SESSION['announce_sid'];
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

    public function getSchools() {
        return model('Schools')->_makeTree(0);
    }

    public function notice() {
        $cid = !empty($_GET['k']) ? $_GET['k'] : '';
        $cid &&  $map['cid'] = $cid;
        $map['isDel'] = 0;
        $list = M('notice')->where($map)->field('id,title,front,cTime')->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->assign('k',$cid);
        $this->display();
    }

      public function noticeDetail() {
        $id = intval($_REQUEST['id']);
        $obj = M('notice')->where("`id`={$id} AND isDel=0")->find();
        if (!$obj) {
            $this->error('公告不存在或已被删除！');
        }
        $this->assign('obj', $obj);
        $this->display();
    }
    
    
}

?>
