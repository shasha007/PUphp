<?php

class MyshopAction extends Action {

    public function _initialize() {
        $this->assign('isAdmin', 1);
        $dao = M('order');
        $map['type'] = 1;
        $map['uid'] = $this->mid;
        $map['order_state'] = array('in', '0,2');
        $ygTodo = $dao->where($map)->field('order_id')->find();
        $this->assign('ygTodo', $ygTodo);
        $map['type'] = 2;
        $map['order_state'] = array('in', '0,2,4');
        $tgTodo = $dao->where($map)->field('order_id')->find();
        $this->assign('tgTodo', $tgTodo);
        $map['type'] = 3;
        $zjTodo = $dao->where($map)->field('order_id')->find();
        $this->assign('zjTodo', $zjTodo);
    }

    public function index() {
        $this->display();
    }

    public function yg() {
        $buy = M('buylist')->where('type=1 and uid=' . $this->mid)->field('product_id,buyNum')->order('buy_id desc')->findPage(5);
        foreach ($buy['data'] as $k => $v) {
            $yg = M('shop_yg')->where('id=' . $v['product_id'])->find();
            $buy['data'][$k]['yg'] = $yg;
            $product = M('shop_product')->where('id=' . $yg['product_id'])->find();
            $buy['data'][$k]['product'] = $product;
        }
        $this->assign($buy);
        $this->setTitle('梦想记录');
        $this->display();
    }

    public function ygBuy() {
        $id = intval($_GET['id']);
        $yg = M('shop_yg')->where('id=' . $id)->find();
        if (!$yg) {
            $this->error('商品不存在');
        }
        $this->assign('yg', $yg);
        $product = M('shop_product')->where('id=' . $yg['product_id'])->find();
        $this->assign('product', $product);
        //梦想码
        $rno = M('shop_rno')->where('uid=' . $this->mid . ' and ygid=' . $id)->findAll();
        $this->assign('rno', $rno);
        $this->setTitle('梦想记录');
        $this->display();
    }

    public function ygOrder() {
        $state = intval($_GET['state']);
        if ($state > 0) {
            $map['order_state'] = $state - 1;
        }
        $map['type'] = 1;
        $map['uid'] = $this->mid;
        $res = M('order')->where($map)->order('order_id desc')->findPage(5);
        foreach ($res['data'] as $k => $v) {
            $yg = M('shop_yg')->where('id=' . $v['product_id'])->find();
            $res['data'][$k]['yg'] = $yg;
            $product = M('shop_product')->where('id=' . $yg['product_id'])->find();
            $res['data'][$k]['product'] = $product;
        }
        $this->assign($res);
        $this->setTitle('中奖的商品');
        $this->display();
    }

    public function ygOrderDetail() {
        $id = intval($_GET['id']);
        $order = M('order')->where('type=1 and order_id=' . $id . ' and uid=' . $this->mid)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        $this->assign($order);
        $log = M('order_log')->where('order_id=' . $id)->findAll();
        $this->assign('log', $log);
        $yg = M('shop_yg')->where('id=' . $order['product_id'])->find();
        $this->assign('yg', $yg);
        $product = M('shop_product')->where('id=' . $yg['product_id'])->find();
        $this->assign('product', $product);
        //确认收货地址
        if ($order['order_state'] == 0) {
            $address = M('address')->where('uid=' . $this->mid)->findAll();
            $this->assign('address', $address);
        } else {
            $orderAddress = M('order_address')->where('order_id=' . $id)->find();
            $this->assign('orderAddress', $orderAddress);
        }
        if ($order['order_state'] > 1) {
            $trans = M('order_transport')->where('order_id=' . $id)->find();
            $this->assign('trans', $trans);
        }
        $this->setTitle('订单详情');
        $this->display();
    }

    public function address() {
        $list = M('address')->where('uid=' . $this->mid)->order('address_id desc')->findAll();
        $this->assign('list', $list);
        $this->assign('listJson', json_encode($list));
        $this->setTitle('收货地址');
        $this->display();
    }

    public function doEditAdd() {
        $id = intval($_REQUEST['id']);
        if (empty($id)) {
            $this->_insertAdd();
        } else {
            $this->_updateAdd($id);
        }
    }

    private function _insertAdd() {
        $data = $this->_getAddData();
        $res = M('address')->add($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Myshop/address'));
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    private function _getAddData() {
        //参数合法性检查
        $required_field = array(
            'city' => '所在地区',
            'shipAddress' => '街道地址',
            'shipZip' => '邮政编码',
            'shipName' => '收货人',
            'shipMobile' => '手机号码',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }
        $data['city'] = t($_REQUEST['city']);
        $data['shipAddress'] = t($_REQUEST['shipAddress']);
        $data['shipZip'] = t($_REQUEST['shipZip']);
        $data['shipName'] = t($_REQUEST['shipName']);
        $data['shipMobile'] = t($_REQUEST['shipMobile']);
        $data['shipTel'] = t($_REQUEST['shipTel']);
        $data['uid'] = $this->mid;
        return $data;
    }

    private function _updateAdd($id) {
        $data = $this->_getAddData();
        $data['address_id'] = $id;
        $res = M('address')->save($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Myshop/address'));
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    public function delAdd() {
        $id = intval($_REQUEST['id']);
        $res = M('address')->where('address_id=' . $id . ' and uid=' . $this->mid)->delete();
        if ($res) {
            echo 1;
            exit;
        }
        echo 0;
        exit;
    }

    public function ajaxAddress() {
        $oid = intval($_REQUEST['oid']);
        $order = M('order')->where('order_id=' . $oid . ' and uid=' . $this->mid . ' and order_state=0')->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        $data = $this->_getAddData();
        $res = M('address')->add($data);
        if (!$res) {
            $this->error('添加失败');
        } else {
            $data['order_id'] = $oid;
            $res = M('order_address')->add($data);
            if (!$res) {
                $this->error('保存失败');
            }
            M('order')->setField('order_state', 1, 'order_id=' . $oid);
            D('ShopProduct')->addOrderLog($oid, "会员已填写配送地址信息，等待发货", "会员本人"); //添加订单日志
            $this->success('收货地址确认成功');
        }
    }

    public function setOrderAddress() {
        $aid = intval($_REQUEST['aid']);
        $oid = intval($_REQUEST['oid']);
        $address = M('address')->field('city,shipAddress,shipZip,shipName,shipMobile,shipTel,uid')->where('address_id=' . $aid . ' and uid=' . $this->mid)->find();
        if (!$address) {
            $this->error('请选择地址');
        }
        $order = M('order')->where('order_id=' . $oid . ' and uid=' . $this->mid . ' and order_state=0')->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        $address['order_id'] = $oid;
        $res = M('order_address')->add($address);
        if (!$res) {
            $this->error('保存地址失败');
        }
        M('order')->setField('order_state', 1, 'order_id=' . $oid);
        D('ShopProduct')->addOrderLog($oid, "会员已填写配送地址信息，等待发货", "会员本人"); //添加订单日志
        $this->success('收货地址选择成功');
    }

    public function setOrderOver() {
        $oid = intval($_REQUEST['oid']);
        if (M('order')->setField('order_state', 10, 'order_id=' . $oid)) {
//            D('ShopProduct')->addOrderLog($oid,"买家提交确认收货成功","会员本人");//添加订单日志
//            D('ShopProduct')->addOrderLog($oid,"您的交易已成功，欢迎您再次光临","网站客服");//添加订单日志
            echo 1;
            exit;
        }
        echo 0;
        exit;
    }

    public function tgOrder() {
        $map['type'] = 2;
        $map['uid'] = $this->mid;
        if ($_GET['state'] == 99) {
            $map['order_state'] = 0;
        } elseif (isset($_GET['state'])) {
            $map['order_state'] = intval($_GET['state']);
        }
        $res = M('order')->where($map)->order('order_id desc')->findPage(5);
        $dao = M('shop_tgprod');
        $daoTg = M('shop_tg');
        foreach ($res['data'] as $k => $v) {
            $product = $daoTg->where('id=' . $v['product_id'])->find();
            $prod = $dao->where('id=' . $product['tgprod_id'])->find();
            $product['name'] = $prod['name'];
            $product['pic'] = $prod['pic'];
            $res['data'][$k]['product'] = $product;
        }
        $this->assign($res);
        $this->setTitle('团购记录');
        $this->display();
    }

    public function tgOrderDetail() {
        $id = intval($_GET['id']);
        $order = M('order')->where('type=2 and order_id=' . $id . ' and uid=' . $this->mid)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        $this->assign($order);
        $log = M('order_log')->where('order_id=' . $id)->findAll();
        $this->assign('log', $log);
        $product = M('shop_tg')->where('id=' . $order['product_id'])->find();
        $prod = M('shop_tgprod')->where('id=' . $product['tgprod_id'])->find();
        $product['name'] = $prod['name'];
        $product['pic'] = $prod['pic'];
        $this->assign('product', $product);
        //确认收货地址
        if ($order['order_state'] == 0) {
            $address = M('address')->where('uid=' . $this->mid)->findAll();
            $this->assign('address', $address);
        } else {
            $orderAddress = M('order_address')->where('order_id=' . $id)->find();
            $this->assign('orderAddress', $orderAddress);
        }
        if ($order['order_state'] == 1 || $order['order_state'] == 2 || $order['order_state'] == 10) {
            $trans = M('order_transport')->where('order_id=' . $id)->find();
            $this->assign('trans', $trans);
        }
        $this->setTitle('订单详情');
        $this->display();
    }

    public function tgPay() {
        $id = intval($_GET['id']);
        $map['type'] = 2;
        $map['order_id'] = $id;
        $map['uid'] = $this->mid;
        $map['order_state'] = 4;
        $order = M('order')->where($map)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        $obj = M('shop_tg')->where('id=' . $order['product_id'])->find();
        if ($order['totalMoney'] == 0) {
            $pay = currentPrice($obj['sprice'], $obj['eprice'], $obj['eprice_attended'], $obj['has_attended'], $obj['dec']) * $order['buyNum'];
            M('order')->setField('totalMoney', $pay, 'order_id=' . $order['order_id']);
            $order['totalMoney'] = $pay;
        }
        $order['topay'] = $order['totalMoney'] - $order['vorMoney'];
        $this->assign($order);
        $prod = M('shop_tgprod')->where('id=' . $obj['tgprod_id'])->find();
        $obj['name'] = $prod['name'];
        $obj['pic'] = $prod['pic'];
        $this->assign('product', $obj);
        $this->setTitle('支付尾款--众志成城');
        $this->display();
    }

    public function doTgPay() {
        $id = intval($_REQUEST['id']);
        $map['type'] = 2;
        $map['order_id'] = $id;
        $map['uid'] = $this->mid;
        $map['order_state'] = 4;
        $order = M('order')->where($map)->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        $obj = M('shop_tg')->where('id=' . $order['product_id'])->find();
        if ($order['totalMoney'] == 0) {
            $pay = currentPrice($obj['sprice'], $obj['eprice'], $obj['eprice_attended'], $obj['has_attended'], $obj['dec']) * $order['buyNum'];
            M('order')->setField('totalMoney', $pay, 'order_id=' . $order['order_id']);
            $order['totalMoney'] = $pay;
        }
        //扣钱
        $needMoney = $order['totalMoney'] - $order['vorMoney'];
        $url = U('shop/Myshop/tgOrderDetail', array('id' => $id));
        $name = M('shop_tgprod')->getField('name', 'id=' . $obj['tgprod_id']);
        $res = Model('Money')->moneyOut($this->mid, $needMoney, '众志成城尾款 订单号' . $id . ', ' . $name, $url);
        if (!$res) {
            $this->error('您的账号余额不够，请前往充值！');
        }
        M('order')->setField('order_state', '0', 'order_id=' . $id);
        $payMoney = $needMoney / 100;
        D('Order')->addOrderLog($id, "尾款 $payMoney PU币已付！尽快填写收货地址", "PU系统");
        $this->success('支付尾款成功');
    }

    //评论
    public function doTgComment() {
        $id = intval($_GET['id']);
        $this->assign('id', $id);
        $this->display();
    }

    public function addTgComment() {
        $orderid = intval($_POST['id']);
        if ($orderid <= 0) {
            $this->error("错误的访问页面，请检查链接");
        }
        $maps['order_state'] = 10;
        $maps['type'] = 2;
        $maps['uid'] = $this->mid;
        $maps['order_id'] = $orderid;
        $result = M('order')->where($maps)->find();
        if (!$result) {
            $this->error('您没有权限评论');
        }

        $map['content'] = t(h($_POST['content']));
        if (mb_strlen($map['content'], 'UTF8') < 5 || mb_strlen($map['content'], 'UTF8') > 250) {
            $this->error('评论内容不得小于5个字！');
        }
        $map['cTime'] = time();
        $map['tgId'] = $result['product_id'];
        $map['uid'] = $this->mid;
        $res = M('shop_tg_comment')->add($map);
        if ($res) {
            M('order')->setField('comment', 1, 'order_id=' . $orderid);
            $this->success('评论成功！');
        } else {
            $this->error('评论失败！');
        }
    }

    //云购评论
    public function doYgComment() {
        $id = intval($_GET['id']);
        $this->assign('id', $id);
        $this->display();
    }

    public function addYgComment() {
        $orderid = intval($_POST['id']);
        if ($orderid <= 0) {
            $this->error("错误的访问页面，请检查链接");
        }
        $maps['order_state'] = 10;
        $maps['type'] = 1;
        $maps['uid'] = $this->mid;
        $maps['order_id'] = $orderid;
        $result = M('order')->where($maps)->find();
        if (!$result) {
            $this->error('您没有权限评论');
        }

        $map['content'] = t(h($_POST['content']));
        if (mb_strlen($map['content'], 'UTF8') < 5 || mb_strlen($map['content'], 'UTF8') > 250) {
            $this->error('评论内容不得小于5个字！');
        }
        $map['cTime'] = time();
        $map['ygId'] = $result['product_id'];
        $map['uid'] = $this->mid;
        $res = M('shop_yg_comment')->add($map);
        if ($res) {
            M('order')->setField('comment', 1, 'order_id=' . $orderid);
            $this->success('评论成功！');
        } else {
            $this->error('评论失败！');
        }
    }

    //我捐出的
    public function myDonate() {
        $map['isDel'] = 0;
        $map['uid'] = $this->mid;
        $list = D('DonateProduct')->where($map)->order('id DESC')->findPage(10);
        $cat = M('donate_cat')->findAll();
        $cat = orderArray($cat, 'id');
        $map['status'] = 1;
        $notpass =  D('DonateProduct')->where($map)->getField('title');
        $this->assign($list);
        $this->assign('cat', $cat);
        $this->assign('notpass', $notpass);

        $this->display();
    }

    public function myDonateDel() {
        $id = t($_REQUEST['id']);
        if (!$id) {
            $this->error('错误的链接');
        }
        $uid = D('DonateProduct')->getField('uid', 'id =' . $id);
        if ($uid != $this->mid) {
            $this->error('您无权操作');
        }
        $buyer = D('DonateProduct')->getField('buyer', 'id =' . $id);
        if ($buyer > 0) {
            $this->error('该物品以被买下，无法删除');
        }
        $res = D('DonateProduct')->delDonate($id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error('操作失败');
        }
    }

    //我购买的捐物
    public function myDonateBuyer() {
        $map['isDel'] = 0;
        $map['buyer'] = $this->mid;
        $cat = array('日常生活', '美容护肤', '户外设备', '其他');
        $this->assign('cat', $cat);
        $list = D('DonateProduct')->where($map)->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function zj() {
        $type = intval($_GET['type']);
        if ($type > 0) {
            $map['type'] = $type;
        }
        $map['uid'] = $this->mid;
        $res = model('LuckyZj')->zjList($map);
        $this->assign($res);
        $this->assign('types',getLuckyTypes());
        $this->setTitle('中奖纪录');
        $this->display();
    }

    public function ygId(){
        $id = intval($_POST['id']);
        $yg = M('shop_yg')->where('codeState=1 and product_id='.$id)->field('id')->find();
        if(!$yg){
            $this->error('该一元梦想尚未上架，请联系客服或稍后再试');
        }
        $this->success($yg['id']);
        exit;
    }

    public function zjDetail() {
        $id = intval($_GET['id']);
        $obj = model('LuckyZj')->zjDetail($id,$this->mid);
        $this->assign($obj);
        //订单
        if($obj['type']==1){
            $log = M('order_log')->where('order_id=' . $obj['order_id'])->findAll();
            $this->assign('log', $log);
            //确认收货地址
            $order = M('order')->where('type=3 and order_id=' . $obj['order_id'] . ' and uid=' . $this->mid)->field('order_state,cday')->find();
            $this->assign('order_state', $order['order_state']);
            $this->assign('cday', $order['cday']);
            if ($order['order_state'] == 0) {
                $address = M('address')->where('uid=' . $this->mid)->findAll();
                $this->assign('address', $address);
            } else {
                $orderAddress = M('order_address')->where('order_id=' . $obj['order_id'])->find();
                $this->assign('orderAddress', $orderAddress);
            }
            if ($order['order_state'] > 1) {
                $trans = M('order_transport')->where('order_id=' . $obj['order_id'])->find();
                $this->assign('trans', $trans);
            }
        }
        $this->setTitle('奖品详情');
        $tpl = 'zjDetail'.$obj['type'];
        $this->display($tpl);
    }
    public function award_infor() {
        $id = intval($_GET['id']);
        $obj = model('LuckyZj')->webGift($id,$this->mid);
        $this->assign($obj);
        $this->display();
    }

}
