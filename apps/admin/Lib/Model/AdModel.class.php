<?php

class AdModel extends Model {

    public function doAddAd($map, $cover) {
        $map['cTime'] = isset($map['cTime']) ? $map['cTime'] : time();
        $map['coverId'] = $cover['info'][0]['id'];
        $addId = $this->add($map);
        return $addId;
    }

    public function doEditAd($map, $cover, $obj) {
        $map['rTime'] = isset($map['rTime']) ? $map['rTime'] : time();
        if ($cover['status']) {
            $map['coverId'] = $cover['info'][0]['id'];
        }
        $saveId = $this->where('id =' . $obj['id'])->save($map);
        return $saveId;
    }

    public function adClick($adid, $uid) {
        $res = $this->where('id=' . $adid)->find();
        if ($res && $res['type'] == 1 && ($res['price'] <= $res['fund'])) {
            $map['adId'] = $res['id'];
            $map['uid'] = $uid;
            $click = M('ad_click')->where($map)->find();
            if (!$click) {
                if (M('ad_click')->add($map)) {
                    $money = $res['price']*100 ;
                    if($money>0){
                        $this->setDec('fund', 'id=' . $res['id'], $res['price']);
                        Model('Money')->moneyIn($uid, $res['price']*100, '点击广告');
                    }
                }
            }
        }
    }

}