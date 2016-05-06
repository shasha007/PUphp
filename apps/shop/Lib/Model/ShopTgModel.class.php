<?php

class ShopTgModel extends Model {

    public function _initialize() {
        parent::_initialize();
    }
//pay可能会是0
    public function addProduct($data) {
        //商品信息
        $pData2['name'] = $data['name'];
        $pData2['pic'] = $data['pic'];
        $pData2['price'] = $data['price'];
        $pid = M('shop_tgprod')->add($pData2);
        if ($pid) {
            $oData['content'] = $data['content'];
            $oData['imgs'] = $data['imgs'];
            $oData['tg_id'] = $pid;
            M('shop_tg_opt')->add($oData);
            $pData['tgprod_id'] = $pid;
            $pData['uid'] = $data['uid'];
            $pData['eday'] = $data['eday'];
            $pData['sprice'] = $data['sprice'];
            $pData['cprice'] = $data['sprice']*100;
            $pData['eprice'] = $data['eprice'];
            $pData['eprice_attended'] = $data['eprice_attended'];
            $pay = ceil($pData['sprice']/20);
            $pData['pay'] = $pay;
            $dec = intval(($pData['sprice']-$pData['eprice'])*100/$pData['eprice_attended']);
            $pData['dec'] = $dec;
            return $this->add($pData);
        }
        return 0;
    }

    //二次审核
    public function audit($id,$uid){
        return $this->setField(array('audit','codeState'), array($uid,1), 'id='.$id);
    }

    public function deleteProduct($id) {
        $obj = $this->where('id='.$id)->field('has_attended,tgprod_id,tg_times')->find();
        if(!$obj || $obj['has_attended']){
            return false;
        }
        $pid = $obj['tgprod_id'];
        $this->where('id='.$id)->delete();
        $dao = M('shop_tgprod');
        $daoOpt = M('shop_tg_opt');
        if($obj['tg_times'] == 1){
            $pic = $dao->getField('pic', 'id='. $pid);
            tsDelFile($pic);
            $dao->where('id='.$pid)->delete();
            $imgs = $daoOpt->getField('imgs', 'tg_id='. $pid);
            if($imgs){
                $imgsArr = unserialize($imgs);
                foreach ($imgsArr as $va) {
                    tsDelFile($va);
                }
            }
            return $daoOpt->where('tg_id='.$pid)->delete();
        }else{
            $data = array(
                'canActiv' => 1,
                'times' => array('exp','times-1')
            );
            return $dao->where('id='. $pid)->save($data);
        }
    }

    public function updateProduct($data) {
        $obj = $this->where('id='.$data['id'])->field('tgprod_id,codeState,has_attended')->find();
        if(!$obj){
            return false;
        }
        $daoTgProd = M('shop_tgprod');
        if($data['opt']=='renew'){
            $prod = $daoTgProd->where('id='. $obj['tgprod_id'])->field('canActiv,times')->find();
            if(!$prod['canActiv']){
                return false;
            }
        }elseif($obj['codeState']==3){
            return false;
        }
        $pData['eday'] = $data['eday'];
        $pData['sprice'] = $data['sprice'];
        $pData['eprice'] = $data['eprice'];
        $pData['eprice_attended'] = $data['eprice_attended'];
        $pay = ceil($pData['sprice']/20);
        $pData['pay'] = $pay;
        $dec = intval(($pData['sprice']-$pData['eprice'])*100/$pData['eprice_attended']);
        $pData['dec'] = $dec;
        //下一期
        if($data['opt']=='renew'){
            $pData['tg_times'] = $prod['times']+1;
            $pData['cprice'] = $data['sprice']*100;
            $pData['tgprod_id'] = $obj['tgprod_id'];
            $pData['uid'] = $data['uid'];
            $pid = $this->add($pData);
        }else{
            $pData['id'] = $data['id'];
            $pData['cprice'] = currentPrice($pData['sprice'],$pData['eprice'],$pData['eprice_attended'],$obj['has_attended'],$pData['dec']);
            $this->save($pData);
        }
        //商品信息
        $pData2['name'] = $data['name'];
        if ($data['pic']) {
            $pData2['pic'] = $data['pic'];
        }
        $pData2['price'] = $data['price'];
        if($data['opt']=='renew'){
            $pData2['canActiv'] = 0;
            $pData2['times'] = $prod['times']+1;
        }
        $daoTgProd->where('id=' . $obj['tgprod_id'])->save($pData2);
        $oData['content'] = $data['content'];
        $oData['imgs'] = $data['imgs'];
        M('shop_tg_opt')->where('tg_id=' . $obj['tgprod_id'])->save($oData);
        return $data['id'];
    }

    public function tgDetail($id) {
        if (!$id) {
            return false;
        }
        $map['codeState'] = array('neq',2);
        $map['id'] = $id;
        $obj = $this->where($map)->find();
        if (!$obj) {
            return false;
        }
        $prod = M('shop_tgprod')->where('id='.$obj['tgprod_id'])->field('name,pic,price')->find();
        $obj['name'] = $prod['name'];
        $obj['pic'] = $prod['pic'];
        $obj['price'] = $prod['price'];
        $opt = M('shop_tg_opt')->where('tg_id=' . $obj['tgprod_id'])->find();
        $obj['content'] = $opt['content'];
        $obj['imgs'] = unserialize($opt['imgs']);
        return $obj;
    }

    //购买
    public function buy($uid, $id, $num) {
        $result['status'] = 0;
        $map['codeState'] = 1;
        $map['id'] = $id;
        $obj = $this->where($map)->find();
        $prod = M('shop_tgprod')->where('id='.$obj['tgprod_id'])->field('name')->find();
        if (!$obj) {
            $result['info'] = '商品不存在或已结束！';
            return $result;
        }
        //扣钱
        $needMoney = $num * $obj['pay']*100;
        $url = U('shop/Tg/detail',array('id'=>$id));
        $res = Model('Money')->moneyOut($uid, $needMoney, '众志成城 '.$prod['name'], $url);
        if (!$res) {
            $result['info'] = '您的账号余额不够，请前往充值！';
            return $result;
        }
        $cprice = currentPrice($obj['sprice'],$obj['eprice'],$obj['eprice_attended'],$obj['has_attended']+$num,$obj['dec']);
        $data = array(
            'has_attended' => array('exp','has_attended+'.$num),
            'cprice' => $cprice
        );
        $this->where('id=' . $id)->save($data);
        $consumeData = array(
            'uid' => $uid,
            'logMoney' => $needMoney,
            'buyNum' => $num,
            'buyTime' => time(),
            'type' => 2,
            'product_id' => $id
        );
        M('consume')->add($consumeData);
//        unset($consumeData['logMoney']);
//        D('Buylist')->addBuyList($consumeData);
        $orderData = array(
            'uid' => $uid,
            'vorMoney' => $needMoney,
            'buyNum' => $num,
            'type' => 2,
            'order_state' => 3,
            'cday' => date('Y-m-d'),
            'product_id' => $id
        );
        $this->addOrder($orderData);
        $result['status'] = 1;
        return $result;
    }

    //订单生成
    public function addOrder($data) {
        $dao = M('order');
        $condition = array('uid' => $data['uid'], 'product_id' => $data['product_id'], 'type'=>$data['type']);
        $result = $dao->where($condition)->field('order_id,buyNum,vorMoney')->find();
        $num = $data['buyNum'];
        $pay = $data['vorMoney']/100;
        if ($result) {
            $order_id = $result['order_id'];
            $data['buyNum'] = $result['buyNum'] + $data['buyNum'];
            $data['vorMoney'] = $result['vorMoney'] + $data['vorMoney'];
            $dao->where('order_id='.$result['order_id'])->save($data);
        } else {
            $order_id = $dao->add($data);
        }
        $this->addOrderLog($order_id, "购买$num"."件,定金$pay"."PU币已付！", "PU系统");
        return TRUE;
    }

    public function addOrderLog($order_id, $oplog, $user) {
        $data = array(
            'order_id' => $order_id,
            'oplog' => $oplog,
            'opuser' => $user,
            'optime' => date("Y-m-d H:i:s")
        );
        return M('order_log')->add($data);
    }

}

?>