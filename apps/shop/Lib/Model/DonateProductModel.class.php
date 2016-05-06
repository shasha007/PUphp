<?php

class DonateProductModel extends Model {

    public function _initialize() {
        parent::_initialize();
    }

    public function addProduct($data) {
        $pData['uid'] = $data['uid'];
        $pData['title'] = $data['title'];
        $pData['pic'] = $data['pic'];
        $pData['contact'] = $data['contact'];
        $pData['mobile'] = $data['mobile'];
        $pData['catId'] = $data['catId'];
        $pData['price'] = $data['price'];
        $pData['provinceId'] = $data['provinceId'];
        $pData['cityId'] = $data['cityId'];
        $pData['sid'] = $data['sid'];
        $pData['sid1'] = $data['sid1'];
        $pData['groupId'] = $data['groupId'];
        $pData['groupName'] = $data['groupName'];
        $pData['cTime'] = time();
        $pid = $this->add($pData);
//        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
//        var_dump($this->getLastSql());die;
        if ($pid) {
            $oData['content'] = $data['content'];
            $oData['imgs'] = $data['imgs'];
            $oData['product_id'] = $pid;
            M('donate_product_opt')->add($oData);

            return $pid;
        }
        return 0;
    }

    public function updateProduct($data) {
        $pData['id'] = $data['id'];
        $pData['title'] = $data['title'];
        $pData['contact'] = $data['contact'];
        $pData['mobile'] = $data['mobile'];
        $pData['catId'] = $data['catId'];
        $pData['price'] = $data['price'];
        $pData['groupId'] = $data['groupId'];
        $pData['groupName'] = $data['groupName'];
        $pData['status'] = 0;
        if ($data['pic']) {
            $pData['pic'] = $data['pic'];
        }
        $pid = $this->save($pData);
        $oData['content'] = $data['content'];
        $oData['imgs'] = $data['imgs'];
        M('donate_product_opt')->where('product_id=' . $data['id'])->save($oData);
        return $data['id'];
    }

    public function delDonate($id){
        $pic = $this->getField('pic', 'id=' . $id);
        tsDelFile($pic);
        $imgs = M('donate_product_opt')->where('product_id=' . $id)->getField('imgs');
        $images = unserialize($imgs);
        foreach ($images as $v) {
            tsDelFile($v);
        }
        $this->where('id='.$id)->delete();
        return M('donate_product_opt')->where('product_id=' . $id)->delete();
    }

    public function donateDetail($donateId) {
        if (!$donateId) {
            return false;
        }
        $result = $this->where('id=' . $donateId)->find();
        $opt = M('donate_product_opt')->where('product_id=' . $result['id'])->find();
        $result['content'] = $opt['content'];
        if ($opt['imgs'] != 'N;') {
            $result['imgs'] = unserialize($opt['imgs']);
            array_unshift($result['imgs'], $result['pic']);
        } else {
            $result['imgs'] = array($result['pic']);
        }
        return $result;
    }

    //购买
    public function buy($uid, $id) {
        $result['status'] = 0;
        $donate = $this->where('id=' . $id)->find();
        if (!$donate) {
            $result['info'] = '商品不存在或已删除！';
            return $result;
        }
        if ($donate['buyer'] > 0) {
            $result['info'] = '该商品已售出！';
            return $result;
        }
        //扣钱
        $needMoney = $donate['price'] * 100;
        $url = U('shop/Donate/detail', array('id' => $id));
        $res = Model('Money')->moneyOut($uid, $needMoney, '爱心校园 ' . $donate['title'], $url);
        if (!$res) {
            $result['info'] = '您的账号余额不够，请前往充值！';
            return $result;
        }
        $res = $this->where('id =' . $donate['id'])->setField(array('buyer', 'buytime'), array($uid, time()));
        if ($res) {
            $data['uid'] = $donate['uid'];
            $data['buyer'] = $uid;
            $data['fund'] = $donate['price'];
            $data['buytime'] = time();
            M('donate_love_fund')->add($data);
            M('donate_love_all_fund')->setInc('allfund', 'type=1', $donate['price']);
        }
        $result['status'] = 1;
        return $result;
    }

    public function apiDonateList($map, $limit = 15, $page) {
        $offset = ($page - 1) * $limit;
        $list = $this->where($map)->field('id,title,pic,price')->order('id DESC')->limit("$offset,$limit")->select();
        foreach ($list as $k => $v) {
            $list[$k]['pic'] = tsMakeThumbUp($v['pic'], 60, 60, 'f');
        }
        return $list;
    }

    public function apiDonate($donateId) {
        if (!$donateId) {
            return false;
        }
        $result = $this->where('id=' . $donateId)->field('id,uid,title,price,catId,pic,cityId,contact,mobile,buyer,groupName')->find();
        $result['school'] = tsGetSchoolByUid($result['uid']);
        $result['city'] = M('citys')->getField('city', 'id='.$result['cityId']);
        $result['cat'] = M('donate_cat')->getField('name','id='.$result['catId']);
        unset($result['catId']);
        $opt = M('donate_product_opt')->where('product_id=' . $result['id'])->find();
        $result['content'] = $opt['content'];
        if ($opt['imgs'] != 'N;') {
            $result['imgs'] = unserialize($opt['imgs']);
            array_unshift($result['imgs'], $result['pic']);
        } else {
            $result['imgs'] = array($result['pic']);
        }
        foreach ($result['imgs'] as $k => $v) {
            $result['imgs'][$k] = tsMakeThumbUp($v, 800, 600, 'f');
        }
        return $result;
    }

}

?>
