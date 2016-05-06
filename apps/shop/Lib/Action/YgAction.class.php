<?php

class YgAction extends Action {

    public function _initialize() {
        $this->setTitle('一元梦想');
    }

    public function index() {
        $map['codeState'] = 1;
        $list = D('ShopProduct')->ygList($map,8);
        $this->assign($list);
        $this->display();
    }

    public function detail() {
        $id = intval($_GET['id']);
        $dao = D('ShopProduct');
        $obj = $dao->ygDetail($id);
        if(!$obj){
            $this->error('商品不存在！或已删除');
        }
        //var_dump($obj);die;
        $this->assign($obj);
        $progress = array(
            'per' => round($obj['has_attended'] / $obj['need_attended'] * 100, 2),
            'width' => round($obj['has_attended'] / $obj['need_attended'] * 150)
        );
        $this->assign('progress',$progress);
        $this->assign('historyYg',$dao->historyYg($obj['product_id']));
        $buylist = M('buylist')->where('type=1 and product_id='.$id)->field('uid,buyNum')->order('buy_id')->limit(8)->findAll();
        foreach ($buylist as $k => $v) {
            $buylist[$k]['realname'] = getUserRealName($v['uid']);
        }

           //评论列表
        $maps['isDel'] = 0;
        $maps['ygId'] = $id;
        $list = M('shop_yg_comment')->where($maps)->order('id DESC')->findPage(20);
        $this->assign($list);

        $this->assign('buylist',$buylist);
        $this->setTitle($obj['name']);
        $this->display();
    }

    public function buy() {
        if($this->user['sid']==473){
            $this->error('口袋大学内部人员不可购买');
        }
//        if(time()< strtotime('2013-12-18 18:18')){
//            $this->error('18日18点18分试运营三天，20日18点18分正式营业');
//        }
        $id = intval($_GET['id']);
        $dao = D('ShopProduct');
        $obj = $dao->ygDetail($id);
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
        $zjId = intval($_POST['voucher']);
        $res = D('ShopProduct')->buy($this->mid,$id,$num,$zjId);
        echo json_encode($res);
        exit;
    }

    public function autoOpen(){
        $product = M('shop_product')->where('id=1')->field('need_attended,over_times')->find();
        var_dump(M('shop_product')->getLastSql());die;

        $map['codeState'] = 1;
        $map['eday'] = date('Y-m-d');
        $list = M('shop_yg')->where($map)->field('id,has_attended,product_id,times')->findAll();
        foreach ($list as $v) {
            if($v['has_attended']==0){
                M('shop_yg')->setField('eday', '0000-00-00', 'id='.$v['id']);
            }else{
                //计算中奖人员
                $allRNO = M('shop_rno')->where("ygid=". $v['id'])->findAll();
                $winKey = rand(0, count($allRNO) - 1);
                $rnoWin = $allRNO[$winKey]['rno_id'];
                $winUid = $allRNO[$winKey]['uid'];
                $data = array(
                    'uid' => $winUid,
                    'product_id' => $v['id'],
                    'type' => 1,
                );
                $order_id = M('order')->add($data);
                $data = array(
                    'order_id' => $order_id,
                    'oplog' => "恭喜您一元梦想获奖，请尽快填写收货地址，以便我们为您配送！",
                    'opuser' => "PU系统",
                    'optime' => date("Y-m-d H:i:s")
                );
                M('order_log')->add($data);
                $uData = array(
                    'win' => $winUid,
                    'codeRNO' => $rnoWin,
                    'over_date' => date("Y-m-d H:i:s"),
                    'codeState' => '3',
                    'order_id' => $order_id,
                );
                M('shop_yg')->where('id=' . $v['id'])->save($uData);
                //自动开始下一期
                $pid = $v['product_id'];
                $times = $v['times'];
                $product = $this->where('id='.$pid)->field('need_attended,over_times')->find();
                if ($product['over_times'] == 0 || $times < $product['over_times']) {
                    //自动开始下一期
                    $copy = array(
                        'product_id' => $pid,
                        'need_attended' => $product['need_attended'],
                        'ctime' => time(),
                        'times' => $times + 1,
                    );
                    M('shop_yg')->add($copy);
                }
            }
        }
        die;
    }
    public function comment(){
        $id = intval($_GET['id']);
        $maps['isDel'] = 0;
        $maps['ygId'] = $id;
        $list = M('shop_yg_comment')->where($maps)->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function voucher(){
        $id = intval($_POST['id']);
        $res = model('LuckyZj')->userYgVoucher($this->mid,$id);
        if(!$res){
            $this->error('没有该商品的代金券，或已过期');
        }
        $this->success($res['id']);
    }

}
