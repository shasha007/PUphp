<?php

class TgAction extends Action {

    public function _initialize() {
        $this->setTitle('众志成城');
    }

    public function index() {
        $map['codeState'] = 1;
        $db_prefix = C('DB_PREFIX');
        $list = M('shop_tg')->table("{$db_prefix}shop_tg AS a ")
                ->join("{$db_prefix}shop_tgprod AS b ON b.id=a.tgprod_id")
                ->field('a.*,b.name,b.pic')
                ->where($map)->order('eday asc, a.id DESC')->findPage(8);

        $res = D('user')->where('uid='.$this->mid)->field('uid,sid')->find();
        $sid = $res['sid'];
        $this->assign('sid',$sid);
        $this->assign($list);
        $this->display();
    }

    public function detail() {
        $id = intval($_GET['id']);
        $dao = D('ShopTg');
        $obj = $dao->tgDetail($id);
        if(!$obj){
            $this->error('商品未上架！或已删除');
        }
        //var_dump($obj);die;

        $this->assign($obj);
        $this->setTitle($obj['name']);
        $this->display();
    }

    public function buy() {
//        if($this->user['sid']==473){
//            $this->error('口袋大学内部人员不可购买');
//        }
//        if(time()< strtotime('2013-12-18 18:18')){
//            $this->error('18日18点18分试运营三天，20日18点18分正式营业');
//        }
        $id = intval($_GET['id']);
        $dao = D('ShopTg');
        $obj = $dao->tgDetail($id);
        if(!$obj){
            $this->error('商品不存在！或已删除');
        }
        //var_dump($obj);die;
        $this->assign($obj);
        $this->setTitle($obj['name']);
        $this->display();
    }

    public function payment(){
        $id = intval($_POST['id']);
        $num = intval($_POST['num']);
        $res = D('ShopTg')->buy($this->mid,$id,$num);
        echo json_encode($res);
        exit;
    }

      public function comment(){
        $id = intval($_GET['id']);
        $map['isDel'] = 0;
        $map['tgId'] = $id;
        $list = M('shop_tg_comment')->where($map)->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
      }

}
