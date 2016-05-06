<?php

class BuylistModel extends Model {

    //购买记录
    public function addBuyList($data) {
        $dao = M('buylist');
        $condition = array('uid' => $data['uid'], 'product_id' => $data['product_id'], 'type'=>$data['type']);
        $result = $dao->where($condition)->field('buyNum,buy_id')->find();
        $data['buyIp'] = get_client_ip();
        if ($result) {
            $data['buyNum'] = $result['buyNum'] + $data['buyNum'];
            $dao->where($condition)->save($data);
        } else {
            $dao->add($data);
        }
        return TRUE;
    }

}

?>