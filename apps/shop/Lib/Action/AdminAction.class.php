<?php

/**
 * 乐购的后台操作
 *
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
import('home.Action.PubackAction');

class AdminAction extends PubackAction {

    public function _initialize() {
        parent::_initialize();
    }

    public function index() {
        $this->display();
    }

    public function yg() {
        if($_GET['cat']==1){
            $map['audit'] = 0;
        }elseif($_GET['cat']==2){
            $map['audit'] = array('neq',0);
        }
        $map['isDel'] = 0;
        $list = M('shop_product')->where($map)->order('id DESC')->findPage(10);
        foreach ($list['data'] as $key=>$v) {
            $list['data'][$key]['restart'] = 0;
            $list['data'][$key]['lastYgId'] = 0;
            if($v['audit']==0){
                $list['data'][$key]['state'] = '待审核';
            }else{
                $yg = M('shop_yg')->where('product_id='.$v['id'])->field('id,times,codeState')->order('times DESC')->find();
                if(!$yg){
                    $list['data'][$key]['restart'] = 1;
                    $list['data'][$key]['state'] = '已结束,无人获奖';
                }elseif($yg['codeState']==1){
                    $list['data'][$key]['lastYgId'] = $yg['id'];
                    $list['data'][$key]['state'] = '<span class="cGreen">进行中，当前期数'.$yg['times'].'</span>';
                }else{
                    $list['data'][$key]['lastYgId'] = $yg['id'];
                    $list['data'][$key]['restart'] = 1;
                    $list['data'][$key]['state'] = '已结束，最后期数'.$yg['times'];
                }
            }
        }
        $this->assign($list);
        $this->display('yg');
    }

    public function yy() {
        $map['codeState'] = 1;
        $db_prefix = C('DB_PREFIX');
        $list = M('shop_yg')->table("{$db_prefix}shop_yg AS a ")
                ->join("{$db_prefix}shop_product AS b ON b.id=a.product_id")
                ->field('a.id,a.need_attended,a.has_attended,a.times,a.eday,b.name,b.pic')
                ->where($map)->order('a.id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function editYg() {
        $this->assign('type', 'add');
        $id = (int) $_REQUEST['id'];
        if ($id) {
            $this->assign('type', 'edit');
            $map['id'] = $id;
            $obj = M('shop_product')->where($map)->find();
            if (!$obj) {
                $this->error('无法找到对象');
            }
            $opt = M('shop_product_opt')->field('content,imgs')->where('product_id='.$id)->find();
            $opt['imgs'] = unserialize($opt['imgs']);
            $this->assign($opt);
            $this->assign($obj);
        }
        $this->display();
    }

    public function audit() {
        $id = (int) $_REQUEST['id'];
        if ($id) {
            $map['id'] = $id;
            $obj = M('shop_product')->where($map)->find();
            if (!$obj) {
                $this->error('无法找到对象');
            }
            if ($obj['audit']) {
                $this->error('已经二次审核通过');
            }
            $opt = M('shop_product_opt')->field('content,imgs')->where('product_id='.$id)->find();
            $opt['imgs'] = unserialize($opt['imgs']);
            $this->assign($opt);
            $this->assign($obj);
            $this->display();
        }else{
            $this->error('无法找到对象');
        }
    }

    public function doAudit(){
        $id = (int) $_REQUEST['id'];
        if (!$id) {
            $this->error('无法找到对象');
        }
        $map['id'] = $id;
        $obj = M('shop_product')->where($map)->find();
        if (!$obj) {
            $this->error('无法找到对象');
        }
        if ($obj['audit']) {
            $this->error('已经二次审核通过');
        }
        if ($obj['uid'] == $this->mid) {
            $this->error('自己发起的商品需其他人审核');
        }
        $res = D('ShopProduct')->audit($id,$this->mid);
        if(!$res){
            $this->error('审核失败');
        }
        $this->assign('jumpUrl', U('/Admin/yg',array('cat'=>1)));
        $this->success('审核成功');
    }

    public function doEditYg() {
        if(intval($_REQUEST['need_attended'])<=0){
            $this->error('所需参与人数必须大于零的整数');
        }
        $id = intval($_REQUEST['id']);
        if (empty($id)) {
            $this->_insertYg();
        } else {
            $this->_updateYg($id);
        }
    }

    private function _insertYg() {
        $data = $this->_getYgData();
        $data['uid'] = $this->mid;
        $dao = D('ShopProduct');
        $res = $dao->addProduct($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Admin/yg'));
            $this->success('添加成功');
        } else {
            $this->error($dao->getError());
        }
    }

    private function _getYgData() {
        $info = tsUploadImg();
        if ($info['status']) {
            $data['pic'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        } elseif ($info['info'] != '没有选择上传文件') {
            $this->error($info['info']);
        }
        $data['name'] = t($_REQUEST['name']);
        $data['content'] = t(h($_REQUEST['content']));
        $data['over_times'] = t($_REQUEST['over_times']);
        $data['need_attended'] = t($_REQUEST['need_attended']);
        $data['price'] = $data['need_attended'];
        $data['imgs'] = serialize($_REQUEST['imgs']);
        return $data;
    }

    private function _updateYg($id) {
        $data = $this->_getYgData();
        $data['id'] = $id;
        $dao = D('ShopProduct');
        $res = $dao->updateProduct($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Admin/yg'));
            $this->success('修改成功');
        } else {
            $this->error($dao->getError());
        }
    }

    public function deleteProduct() {
        $id = intval($_REQUEST['id']);
        if(D('ShopProduct')->deleteProduct($id)){
            echo 1;
            exit;
        }
        echo 0;
        exit;
    }

    //订单列表
    public function order() {
        $map['order_state'] = array('neq',3);
        $state = intval($_GET['state']);
        if($state>0){
            $map['order_state'] = $state-1;
        }
        if($_REQUEST['search_uid']){
             $uname = t($_REQUEST['search_uid']);
             if($uname){
                 $user = M('user')->where("realname='$uname'")->field('uid')->findAll();
                 $uids = getSubByKey($user, 'uid');
                 if($uids){
                    $map['uid'] = array('in',$uids);
                 }
             }
        }
        $list = M('order')->where($map)->order('order_id DESC')->findPage(10);
        $db_prefix = C('DB_PREFIX');
        foreach ($list['data'] as $key => $v) {
            //商品详情
            if($v['type']==1){
                $product = M('')->table("{$db_prefix}shop_yg AS a ")
                                ->join("{$db_prefix}shop_product AS b ON b.id=a.product_id")
                                ->field('a.times,b.name,b.price,b.pic')
                                ->where('a.id=' . $v['product_id'])->find();
                $list['data'][$key]['product'] = $product;
            }elseif($v['type']==2){
                $product = M('')->table("{$db_prefix}shop_tg AS a ")
                                ->join("{$db_prefix}shop_tgprod AS b ON b.id=a.tgprod_id")
                                ->field('a.cprice,b.name,b.pic')
                                ->where('a.id=' . $v['product_id'])->find();
                $list['data'][$key]['product'] = $product;
            }elseif($v['type']==3){
                $product = M('lucky_product')->field('name')
                                ->where('id=' . $v['product_id'])->find();
                $list['data'][$key]['product'] = $product;
            }
            //收货信息
            if($v['order_state']>0){
                $list['data'][$key]['address'] = M('order_address')->where('order_id='.$v['order_id'])->find();
            }
            //物流信息
            if($v['order_state']>1){
                $list['data'][$key]['trans'] = M('order_transport')->where('order_id='.$v['order_id'])->find();
            }
            //会员信息
            $user = M('user')->where('uid='.$v['uid'])->field('realname,sid,sid1,mobile')->find();
            if($user['sid1']){
                $user['school'] = tsGetSchoolTitle($user['sid1']);
            }else{
                $user['school'] = tsGetSchoolName($user['sid']);
            }
            $list['data'][$key]['user'] = $user;
        }
        $this->assign($list);
        $this->display('order');
    }

    public function sendProduct(){
        $data['order_id'] = intval($_POST['order_id']);
        if(!$data['order_id']){
            $this->error('订单号错误');
        }
        $data['transport_num'] = t($_POST['transport_num']);
        if(!$data['transport_num']){
            $this->error('快递单号不能为空');
        }
        $data['transport_name'] = t($_POST['transport_name']);
        if(!$data['transport_name']){
            $this->error('快递公司不能为空');
        }
        $data['transport_mark'] = t($_POST['transport_mark']);
        $data['transport_time'] = date('Y-m-d H:i:s');
        if(M('order_transport')->add($data)){
            M('order')->setField('order_state', 2, 'order_id='.$data['order_id']);
            D('ShopProduct')->addOrderLog($data['order_id'], "您获得的商品已发货，请注意查收", "网站客服");
            $this->success('发货成功');
        }
        $this->error('保存失败');
    }

    public function changeEday(){
        $data['id'] = intval($_POST['id']);
        if(!$data['id']){
            $this->error('云购不存在');
        }
        $data['eday'] = t($_POST['eday']);
        if(!$data['eday']){
            $data['eday'] = '0000-00-00';
        }elseif($data['eday']<=date('Y-m-d')){
            $this->error('开奖时间不可早于今天');
        }
        if(M('shop_yg')->save($data)){
            $this->success('保存成功');
        }
        $this->error('保存失败');
    }

    public function deleteYg(){
        $id = intval($_POST['id']);
        if(!$id){
            $this->error('云购不存在');
        }
        $yg = M('shop_yg')->where('id='.$id)->field('has_attended')->find();
        if(!$yg){
            $this->error('云购不存在');
        }
        if($yg['has_attended']!=0){
            $this->error('已有人抢购，不可删除');
        }
        if(M('shop_yg')->where('id='.$id)->delete()){
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }

    //下架商品，退还货款
    public function cancelYg(){
        $id = intval($_POST['id']);
        $dao = D('ShopProduct');
        if($dao->cancelYg($id)){
            $this->success('下架成功');
        }
        $this->error($dao->getError());
    }

    public function tg() {
        $map['codeState'] = array('neq',3);
        $state = intval($_GET['state']);
        if($state>0){
            $map['codeState'] = $state-1;
        }
        $list = M('shop_tg')->where($map)->order('id DESC')->findPage(10);
        $dao = M('shop_tgprod');
        foreach ($list['data'] as $key=>$v) {
            //商品属性
            $prod = $dao->where('id='.$v['tgprod_id'])->find();
            $list['data'][$key]['name'] = $prod['name'];
            $list['data'][$key]['pic'] = $prod['pic'];
            $list['data'][$key]['times'] = $prod['times'];
            $list['data'][$key]['canActiv'] = $prod['canActiv'];
            if($v['codeState']==2){
                $list['data'][$key]['state'] = '<span class="cRed">待审核</span>';
            }elseif($v['codeState']==1){
                $list['data'][$key]['state'] = '<span class="cGreen">进行中</span>';
            }else{
                $list['data'][$key]['state'] = '已结束';
            }
        }
        $this->assign($list);
        $this->display('tg');
    }

    public function editTg() {
        $this->assign('type', 'add');
        $id = (int) $_REQUEST['id'];
        if ($id) {
            $this->assign('type', 'edit');
            $map['id'] = $id;
            $obj = M('shop_tg')->where($map)->find();
            if (!$obj) {
                $this->error('无法找到对象');
            }
            $prod = M('shop_tgprod')->where('id='.$obj['tgprod_id'])->field('name,pic,price')->find();
            $this->assign($prod);
            $opt = M('shop_tg_opt')->field('content,imgs')->where('tg_id='.$obj['tgprod_id'])->find();
            $opt['imgs'] = unserialize($opt['imgs']);
            $this->assign($opt);
            $this->assign($obj);
        }
        $this->display();
    }

    public function doEditTg() {
        if(intval($_REQUEST['price'])<=0){
            $this->error('市场参考价必须大于零的整数');
        }
        if(intval($_REQUEST['sprice'])<=0){
            $this->error('起拍价必须大于零的整数');
        }
        if(intval($_REQUEST['eprice'])<=0){
            $this->error('最低价必须大于零的整数');
        }
        if(intval($_REQUEST['eprice_attended'])<=0){
            $this->error('最低价需要人数必须大于零的整数');
        }
        if(intval($_REQUEST['sprice'])<intval($_REQUEST['eprice'])){
            $this->error('最低价必须小于起拍价');
        }
        if(strtotime($_REQUEST['eday'])<=strtotime(date('Y-m-d'))){
            $this->error('结束时间必须大于今天');
        }
        $id = intval($_REQUEST['id']);
        $name = t($_REQUEST['name']);
        $sameNameId = M('shop_tgprod')->getField('id', "name='$name'");
        if($sameNameId){
            if(!$id){
                $this->error('该商品已经存在，可尝试开始下一期');
            }else{
                $pid = M('shop_tg')->getField('tgprod_id', "id=$id");
                if($pid != $sameNameId){
                    $this->error('该商品已经存在，可尝试开始下一期');
                }
            }
        }
        if (empty($id)) {
            $this->_insertTg();
        } else {
            $this->_updateTg($id);
        }
    }

    private function _insertTg() {
        $data = $this->_getTgData();
        $res = D('ShopTg')->addProduct($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Admin/tg'));
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    private function _getTgData() {
        $info = tsUploadImg();
        if ($info['status']) {
            $data['pic'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        } elseif ($info['info'] != '没有选择上传文件') {
            $this->error($info['info']);
        }
        $data['uid'] = $this->mid;
        $data['name'] = t($_REQUEST['name']);
        $data['price'] = t($_REQUEST['price']);
        $data['sprice'] = t($_REQUEST['sprice']);
        $data['eprice'] = t($_REQUEST['eprice']);
        $data['eprice_attended'] = t($_REQUEST['eprice_attended']);
        $data['content'] = t(h($_REQUEST['content']));
        $data['eday'] = t($_REQUEST['eday']);
        $data['imgs'] = serialize($_REQUEST['imgs']);
        return $data;
    }

    private function _updateTg($id) {
        $data = $this->_getTgData();
        $data['id'] = $id;
        $data['opt'] = t($_REQUEST['opt']);
        $res = D('ShopTg')->updateProduct($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Admin/tg'));
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    public function auditTg() {
        $id = (int) $_REQUEST['id'];
        if ($id) {
            $map['id'] = $id;
            $obj = M('shop_tg')->where($map)->find();
            if (!$obj) {
                $this->error('无法找到对象');
            }
            if ($obj['audit']) {
                $this->error('已经二次审核通过');
            }
            $prod = M('shop_tgprod')->where('id='.$obj['tgprod_id'])->field('name,pic,price')->find();
            $this->assign($prod);
            $opt = M('shop_tg_opt')->field('content,imgs')->where('tg_id='.$obj['tgprod_id'])->find();
            $opt['imgs'] = unserialize($opt['imgs']);
            $this->assign($opt);
            $this->assign($obj);
            $this->display();
        }else{
            $this->error('无法找到对象');
        }
    }

    public function doAuditTg(){
        $id = (int) $_REQUEST['id'];
        if (!$id) {
            $this->error('无法找到对象');
        }
        $map['id'] = $id;
        $obj = M('shop_tg')->where($map)->find();
        if (!$obj) {
            $this->error('无法找到对象');
        }
        if ($obj['audit']) {
            $this->error('已经二次审核通过');
        }
        if ($obj['uid'] == $this->mid) {
            $this->error('自己发起的商品需其他人审核');
        }
        if($obj['eday']<=date('Y-m-d')){
            $this->error('结束时间必须大于今天');
        }
        $res = D('ShopTg')->audit($id,$this->mid);
        if(!$res){
            $this->error('审核失败');
        }
        $this->assign('jumpUrl', U('/Admin/tg',array('cat'=>1)));
        $this->success('审核成功');
    }
    public function deleteTg() {
        $id = intval($_REQUEST['id']);
        if(D('ShopTg')->deleteProduct($id)){
            echo 1;
            exit;
        }
        echo 0;
        exit;
    }

    public function tgUser(){
        $id = intval($_REQUEST['id']);
        if ($id) {
            $map['id'] = $id;
            $obj = M('shop_tg')->field('tgprod_id')->where($map)->find();
            if (!$obj) {
                $this->error('无法找到对象');
            }
            $prod = M('shop_tgprod')->where('id='.$obj['tgprod_id'])->field('name')->find();
            $this->assign($prod);
            $order = M('order')->where("type=2 and product_id=$id")->findPage(20);
            $this->assign($order);
        }
        $this->display();
    }

    public function restartYg(){
        $id = intval($_POST['id']);
        $dao = D('ShopProduct');
        $res = $dao->restartYg($id);
        if ($res) {
            $this->success('激活成功');
        } else {
            $this->error($dao->getError());
        }
    }

    public function update(){
//        $daotg = M('shop_tg');
//        $tg = $daotg->order('id asc')->findAll();
//        $dao = M('shop_tgprod');
//        foreach ($tg as $value) {
//            $canActiv = 0;
//            if($value['codeState']==3){
//                $canActiv = 1;
//            }
//            $data = array(
//                'id' => $value['id'],
//                'name' => $value['name'],
//                'price' => $value['price'],
//                'pic' => $value['pic'],
//                'uid' => $value['uid'],
//                'audit' => $value['audit'],
//                'canActiv' => $canActiv,
//                'times' => 1,
//            );
//            $dao->add($data);
//            $daotg->setField('tgprod_id', $value['id'], 'id='.$value['id']);
//        }

//        $ids = array(36,34);
//        $daoTg = M('shop_tg');
//        $daoorder_log = M('order_log');
//        $daoOrder = M('order');
//        foreach ($ids as $id) {
//            $obj = $daoTg->where('id=' . $id)->field('tgprod_id')->find();
//            $pid = $obj['tgprod_id'];
//            $daoTg->where('id=' . $id)->delete();
//            $dao = M('shop_tgprod');
//            $daoOpt = M('shop_tg_opt');
//            $pic = $dao->getField('pic', 'id=' . $pid);
//            tsDelFile($pic);
//            $dao->where('id=' . $pid)->delete();
//            $imgs = $daoOpt->getField('imgs', 'tg_id=' . $pid);
//            if ($imgs) {
//                $imgsArr = unserialize($imgs);
//                foreach ($imgsArr as $va) {
//                    tsDelFile($va);
//                }
//            }
//            $daoOpt->where('tg_id=' . $pid)->delete();
//            $consumeData = array(
//                'type' => 2,
//                'product_id' => $id
//            );
//            M('consume')->where($consumeData)->delete();
//            $orderData = array(
//                'type' => 2,
//                'product_id' => $id
//            );
//            $orders = $daoOrder->where($orderData)->findAll();
//            foreach ($orders as $order) {
//                $daoorder_log->where('order_id='.$order['order_id'])->delete();
//            }
//            $daoOrder->where($orderData)->delete();
//        }
    }
}

?>