<?php

class MakefriendsGiftcountModel extends Model {

    //添加送礼统计
    public function addGiftCount($uid, $toid, $total) {
        //判断是否存在
        $map['uid'] = $uid;
        $map['toid'] = $toid;
        $has = $this->where($map)->field('uid')->find();
        if ($has) {
            return $this->setInc('total', $map, $total);
        } else {
            $map['total'] = $total;
            return $this->add($map);
        }
    }

    //送礼页 粉丝排行
    public function giftCountList($map, $limit, $page, $order = 'total DESC') {
        $offset = ($page - 1) * $limit;
        $list = $this->where($map)->field('uid,total')->order($order)->limit("$offset,$limit")->select();
        if (!$list) {
            return array();
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        foreach ($list as &$value) {
            $value['nickname'] = D('MakefriendsUser','makefriends')->getNickname($value['uid']);
            $value['headPhoto_small'] = $daoUser->getHeadPhoto($value['uid'],'big');
        }
        return $list;
    }




}

?>