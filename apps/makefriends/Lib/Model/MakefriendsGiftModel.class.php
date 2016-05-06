<?php

class MakefriendsGiftModel extends Model {

    //记录送礼
    public function addGift($uid, $toid, $giftCode, $giftNum) {
        if ($uid <= 0 || $toid <= 0 || $giftNum <= 0) {
            $this->error = '缺少参数';
            return false;
        }
        if ($uid == $toid) {
            $this->error = '自己不可给自己送礼物';
            return false;
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        $checkUser = $daoUser->checkAndAddUser($uid);
        if(!$checkUser){
            $this->error = '本人账号错误';
            return false;
        }
        $touser = $daoUser->getUserInfo($toid);
        if (!$touser) {
            $this->error = '用户不存在';
            return false;
        }
        $data['price'] = $this->getPrice($giftCode);
        if ($data['price'] === false) {
            $this->error = '礼品不存在';
            return false;
        }
        $todayRest = $daoUser->todayRestGift($uid);
        //PU币购买
        $puBuy = $giftNum - $todayRest;
        if ($puBuy > 0) {
            $pay = Model('Money')->moneyOut($uid, $data['price'] * $puBuy * 100, '交友送礼物');
            if (!$pay) {
                $this->error = '您的账号余额不够，请前往充值！';
                if(isTestUser($uid)){
                    $this->error = 'PU币已用完，去摇一摇赚取吧！';
                }
                return false;
            }
        }else{
            $puBuy = 0;
        }
        $data['uid'] = $uid;
        $data['sid'] = getUserField($uid,'sid');
        $data['toid'] = $toid;
        $data['giftCode'] = $giftCode;
        $data['giftNum'] = $giftNum;
        $data['buyNum'] = $puBuy;
        $data['day'] = date('Y-m-d');
        $res = $this->add($data);
        if (!$res) {
            $this->error = '操作失败！';
            return false;
        }
        D('MakefriendsGiftcount', 'makefriends')->addGiftCount($uid, $toid, $giftNum * $data['price']);
        $daoUser->incGx($uid,'gift',$toid,$giftNum);
        $daoUser->incRq($toid,'gift',$giftNum);
        $this->upHasSend($uid, $giftNum);
        $daoUser->upCache($toid, 'newGift', 1);
        return true;
    }
    //更新用户今日已送花数量
    public function upHasSend($uid,$count){
        $cache = Mmc('Makefriends_user_' . $uid);
        if ($cache === false) {
            return true;
        }
        $user = json_decode($cache, true);
        $today = date('Y-m-d');
        if(isset($user['hasSend']) && $user['hasSendDay']==$today){
            $user['hasSend'] += $count;
            Mmc('Makefriends_user_' . $uid, json_encode($user),0,3600*18);
        }
        return true;
    }
    //获取礼品单价
    public function getPrice($giftCode) {
        if ($giftCode == 1) {
            return 0.50;
        }
        return false;
    }

    //礼物列表
    public function giftList($map, $limit = 10, $page = 1, $order = 'day DESC', $ch = 'get') {
        $offset = ($page - 1) * $limit;
        $field = 'toid as uid,giftNum,day';
        if ($ch == 'get') {
            $field = 'uid,giftNum,day';
        }
        $list = $this->where($map)->field($field)->order($order)->limit("$offset,$limit")->select();
        if (!$list) {
            return array();
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        foreach ($list as &$value) {
            $value['point'] = $daoUser->getGx('gift')*$value['giftNum']; //得到的贡献值
            $value['nickname'] = D('MakefriendsUser','makefriends')->getNickname($value['uid']);
            $value['headPhoto_small'] = $daoUser->getHeadPhoto($value['uid'],'big');
        }
        if ($ch == 'get') {
            $this->setField('newGift', 0, 'toid='.$map['toid']);
            $daoUser->upCache($map['toid'], 'newGift', 0);
        }
        return $list;
    }

    //统计用户一共收到多少个礼物
    public function userGetSum($toid){
        $map['toid'] = $toid;
        $num = 0;
        $res = $this->where($map)->sum('giftNum');
        if($res){
            $num = $res;
        }
        return $num;
    }

}

?>
