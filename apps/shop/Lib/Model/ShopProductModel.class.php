<?php

class ShopProductModel extends Model {

    public function _initialize() {
        parent::_initialize();
    }

    public function addProduct($data) {
        $pData['name'] = $data['name'];
        $has = $this->where($pData)->field('id')->find();
        if($has){
            $this->error = '商品名称已存在，不可重复';
            return 0;
        }
        $pData['uid'] = $data['uid'];
        $pData['pic'] = $data['pic'];
        $pData['over_times'] = $data['over_times'];
        $pData['price'] = $data['price'];
        $pData['need_attended'] = $data['need_attended'];
        $pid = $this->add($pData);
        if ($pid) {
            $oData['content'] = $data['content'];
            $oData['imgs'] = $data['imgs'];
            $oData['product_id'] = $pid;
            M('shop_product_opt')->add($oData);
            return $pid;
        }
        $this->error = '添加失败';
        return 0;
    }

    public function restartYg($id){
        $map['id'] = $id;
        $map['isDel'] = 0;
        $map['audit'] = array('neq',0);
        $product = $this->where('id='.$id)->field('over_times,need_attended')->find();
        if(!$product){
            $this->error = '没找到商品，或结束期数为0';
            return false;
        }
        $lastYg = M('shop_yg')->where('product_id='.$id)->field('times,codeState')->order('times DESC')->find();
        if(!$lastYg){
            $times = 1;
        }elseif ($lastYg['codeState'] == 1) {
            $this->error = '正在云购，不用激活';
            return false;
        }elseif ($lastYg['times'] >= $product['over_times']) {
            $this->error = '结束期数应大于当前期数';
            return false;
        }else{
            $times = $lastYg['times']+1;
        }
        $data = array(
            'product_id' => $id,
            'times' => $times,
            'need_attended' => $product['need_attended'],
            'ctime' => time(),
        );
        return M('shop_yg')->add($data);
    }

        //二次审核，发起云购
    public function audit($id,$uid){
        $res = $this->setField('audit', $uid, 'id='.$id);
        if(!$res){
            return false;
        }
        $product = $this->where('id='.$id)->field('need_attended')->find();
        $copy = array(
            'product_id' => $id,
            'need_attended' => $product['need_attended'],
            'ctime' => time(),
        );
        return M('shop_yg')->add($copy);
    }


    public function updateProduct($data) {
        $map['name'] = $data['name'];
        $map['id'] = array('neq',$data['id']);
        $has = $this->where($map)->field('id')->find();
        if($has){
            $this->error = '商品名称已存在，不可重复';
            return 0;
        }
        $pData['name'] = $data['name'];
        $pData['id'] = $data['id'];
        if ($data['pic']) {
            $pData['pic'] = $data['pic'];
        }
        $pData['over_times'] = $data['over_times'];
        $pData['price'] = $data['price'];
        $pData['need_attended'] = $data['need_attended'];
        $pid = $this->save($pData);
        $oData['content'] = $data['content'];
        $oData['imgs'] = $data['imgs'];
        M('shop_product_opt')->where('product_id=' . $data['id'])->save($oData);
        return $data['id'];
    }

    public function deleteProduct($id) {
        $pic = $this->getField('pic', 'id=' . $id);
        $res = $this->where('id='.$id)->delete();
        if(!$res){
            $this->error = '删除失败';
            return false;
        }
        tsDelFile($pic);
        $imgs = M('shop_product_opt')->where('product_id=' . $id)->getField('imgs');
        $images = unserialize($imgs);
        foreach ($images as $v) {
            tsDelFile($v);
        }
        M('shop_product_opt')->where('product_id=' . $id)->delete();
        return true;
//        return $this->setField('isDel', 1, 'id=' . $id);
    }

    public function ygList($map, $page) {
        $db_prefix = C('DB_PREFIX');
        return M('shop_yg')->table("{$db_prefix}shop_yg AS a ")
                        ->join("{$db_prefix}shop_product AS b ON b.id=a.product_id")
                        ->field('a.id,a.times,a.need_attended,a.has_attended,a.eday,b.price,b.name,b.pic')
                        ->where($map)->order('a.id DESC')->findPage($page);
    }

    /**
     * 商品详情
     * @param $ygId
     */
    public function ygDetail($ygId) {
        if (!$ygId) {
            return false;
        }
        $db_prefix = C('DB_PREFIX');
        $result = $this->table("{$db_prefix}shop_yg AS a ")
                        ->join("{$db_prefix}shop_product AS b ON b.id=a.product_id")
                        ->field('a.*,b.name,b.price,b.pic')
                        ->where('a.id=' . $ygId)->find();
        $opt = M('shop_product_opt')->where('product_id=' . $result['product_id'])->find();
        $result['content'] = $opt['content'];
        $result['imgs'] = unserialize($opt['imgs']);
        return $result;
    }

    //往期云购
    public function historyYg($productId) {
        return M('shop_yg')->where('product_id=' . $productId)->field('id,times')->findAll();
    }

    //购买
    public function buy($uid, $id, $num, $zjId=0) {
        $result['status'] = 0;
        //代金券
        $vocher = 0;
        if($zjId>0){
            $zj = M('lucky_zj')->where('used=0 and type=3 and uid='.$uid.' and id='.$zjId)->field('id')->find();
            if(!$zj){
                $result['info'] = '代金券错误，请重试！';
                return $result;
            }
            $vocher = 100;
        }
        //云购数量
        $daoYg = M('shop_yg');
        $yg = $daoYg->where('id=' . $id)->field('product_id,codeState,need_attended,has_attended,times')->find();
        if (!$yg) {
            $result['info'] = '商品不存在或已删除！';
            return $result;
        }
        if ($yg['codeState'] != 1) {
            $result['info'] = '本期一元梦想已满额，请选择下期购买！';
            return $result;
        }
        $restNum = $yg['need_attended'] - $yg['has_attended'];
        if ($restNum < $num) {
            $num = $restNum;
        }
        //扣钱
        $needMoney = ($num * 100)-$vocher;
        if($needMoney>0){
            $title = M('shop_product')->getField('name', 'id='.$yg['product_id']);
            $url = U('shop/Yg/detail',array('id'=>$id));
            $res = Model('Money')->moneyOut($uid, $needMoney, '一元梦想 '.$title, $url);
            if (!$res) {
                $result['info'] = '您的账号余额不够，请前往充值！';
                return $result;
            }
        }
        //代金券设为使用
        if($zjId>0){
            $data['id'] = $zjId;
            $data['used'] = 1;
            $data['utime'] = time();
            M('lucky_zj')->save($data);
        }
        //增加云购码
        for ($i = 1; $i <= $num; $i++) {
            $this->addRno($uid, $id); //增加云购码
        }
        $consumeData = array(
            'uid' => $uid,
            'logMoney' => $needMoney,
            'buyNum' => $num,
            'buyTime' => time(),
            'type' => 1,
            'product_id' => $id
        );
        //消费记录 每条明细
        M('consume')->add($consumeData);
        unset($consumeData['logMoney']);
        D('Buylist')->addBuyList($consumeData);
        if ($num + $yg['has_attended'] >= $yg['need_attended']) {
            //计算中奖人员
            $allRNO = $this->getrno($id);
            $winKey = rand(0, $yg['need_attended'] - 1);
            $rnoWin = $allRNO[$winKey]['rno_id'];
            $winUid = $allRNO[$winKey]['uid'];
            $order_id = $this->addOrder($winUid, $id);
            $this->addOrderLog($order_id, "恭喜您一元梦想获奖，请尽快填写收货地址，以便我们为您配送！", "PU系统");
            $uData = array(
                'has_attended' => $yg['need_attended'],
                'win' => $winUid,
                'codeRNO' => $rnoWin,
                'over_date' => date("Y-m-d H:i:s"),
                'codeState' => '3',
                'order_id' => $order_id,
            );
            $daoYg->where('id=' . $id)->save($uData);
            //自动开始下一期
            $product = $this->where('id='.$yg['product_id'])->field('name,need_attended,over_times')->find();
            if ($product['over_times'] == 0 || $yg['times'] < $product['over_times']) {
                //自动开始下一期
                $copy = array(
                    'product_id' => $yg['product_id'],
                    'need_attended' => $product['need_attended'],
                    'ctime' => time(),
                    'times' => $yg['times'] + 1,
                );
                $daoYg->add($copy);
            }
            //发短信
            $mobile = M('user')->getField('mobile', 'uid=' . $winUid);
            if ($mobile) {
                $msg = '亲爱的PocketUni用户,恭喜您一元梦想获奖。获奖商品:'
                .getShort($product['name'],18).' 请尽快填写收货地址，以便我们为您配送！。';
                service('Sms')->sendsms($mobile, $msg);
            }
            // 发送通知
            $notify_data = array('order_id' => $order_id,'product'=>$product['name']);
            service('Notify')->sendIn($winUid, 'shop_win', $notify_data);
        } else {
            $daoYg->setInc('has_attended', 'id=' . $id, $num);
        }
        $result['status'] = 1;
        return $result;
    }

    //增加云购码
    private function addRno($uid, $ygid) {
        $data['uid'] = $uid;
        $data['ygid'] = $ygid;
        return M('shop_rno')->add($data);
    }

    private function getrno($ygid) {
        return M('shop_rno')->where("ygid=". $ygid)->findAll();
    }

    // 中奖者订单生成
    public function addOrder($uid, $id) {
        $data = array(
            'uid' => $uid,
            'product_id' => $id,
            'type' => 1,
            'cday' => date('Y-m-d'),
        );
        return M('order')->add($data);
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
    //下架，退款
    public function cancelYg($id){
        $daoYg = M('shop_yg');
        $yg = $daoYg->where('id=' . $id)->field('product_id,codeState,has_attended')->find();
        if (!$yg) {
            $this->error = '一元梦想不存在或已删除！';
            return false;
        }
        if ($yg['codeState'] != 1) {
            $this->error = '本期已开奖，不可下架！';
            return false;
        }
        //删除云购
        $res = $daoYg->where('id='.$id)->delete();
        if(!$res){
            $this->error = '删除一元梦想失败！';
            return false;
        }
        if($yg['codeState'] == 0){
            return true;
        }
        //删除云购码
        M('shop_rno')->where('ygid='.$id)->delete();
        //删除消费记录
        M('consume')->where('type=1 and product_id='.$id)->delete();
        //退款
        $daoMoney = Model('Money');
        $daoNotify = service('Notify');
        $users = M('buylist')->where('type=1 and product_id='.$id)->field('uid,buyNum')->findAll();
        foreach ($users as $user) {
            $res = $daoMoney->moneyIn($user['uid'], $user['buyNum']*100, '一元梦想未开奖退款');
            if($res){
                // 发送通知
                $notify_data = array('body' => '一元梦想人数未满开奖取消，退还货款');
                $daoNotify->sendIn($user['uid'], 'admin_pubi', $notify_data);
            }
        }
        //删除buylist
        M('buylist')->where('type=1 and product_id='.$id)->delete();
        return true;
    }

}

?>