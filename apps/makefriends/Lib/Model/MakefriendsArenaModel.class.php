<?php

/*
 * 创建擂台/加入擂台
 */
class MakefriendsArenaModel extends Model
{
    //创建擂台/加入擂台
    public function addArena($uid,$declaration='',$pid=0,$photoId=0){
        //加入擂台
        if($pid){
            $info = $this->getArenaInfo($pid);
            if(!$info){
                $this->error = '擂台错误';
                return false;
            }else{
                if($info['uid'] == $uid){
                  $this->error = '不能自己pk自己';
                  return false;
                }
            }
        }
        //陆，photoId 原为附件id，现修改为 makefriendsPhoto 的id（1检查是否保存成功 2.取照片的地方需修改）
        $daoPhoto = D('MakefriendsPhoto', 'makefriends');
        if($photoId){
            $photo = $daoPhoto->getPhotoInfo($photoId);
            if(!$photo || $photo['uid']!=$uid){
                $this->error = '照片选择错误';
                return false;
            }
        }else{
            $photoId = $daoPhoto->apiAddPhoto($uid);
            if(!$photoId){
                $this->error = $daoPhoto->getError();
                return false;
            }
        }
        $data['photoId'] = $photoId;

        $data['cTime'] = time();
        $data['uid'] = $uid;
        $data['declaration'] = $declaration;
        $data['pid'] = $pid;
        $data['arenaStatus'] = 1;
        if($pid){
          $data['arenaStatus'] = 0;
          $data['startTime'] = time();
        }
        $res = $this->add($data);
        if($res){
          if($pid){
              //陆，要合并成一个save
            $this->where('arenaId='.$pid)->setField('arenaStatus',0);
            $this->where('arenaId='.$pid)->setField('startTime',time());
            Mmc('Makefriends_arena_'.$pid,null);
          }
            return true;
        }else{
            $this->error = '系统错误';
            return false;
        }
    }


    public function getArenaInfo($arenaId){
        $cache = Mmc('Makefriends_arena_'.$arenaId);
            if ($cache !== false) {
            return json_decode($cache, true);
        }
        $res = $this->where('arenaId=' . $arenaId)->find();
        if (!$res) {
            return false;
        }
        Mmc('Makefriends_arena_' . $arenaId, json_encode($res),0,3600);
        return $res;
    }

    public function getCacheArenaPage($uid=0,$limit=10,$page=1,$order='arena DESC'){
        $cacheArr = array();
        if($uid){
            $type = 'Makefriends_arenaUserPage_'.$uid;
        }else{
            $type = 'Makefriends_arenaPage';
        }
        $cache = Mmc($type);
        if($cache !== false){
             $cache = json_decode($cache, true);
             $giltTime = strtotime('-11 minute');
             if($giltTime > $cache['time']){
                   Mmc($cache,null);
             }else{
                   $cacheArr = $cache;
             }
        }
        $key = 'page_'.$page;
        if(isset($cache[$key])){
            return $cache[$key];
        }else{
            $offset = ($page - 1) * $limit;
            $map['isDel'] = 0;
            $map['pid'] = 0;
            if($uid){
              $map['uid'] = $uid;
            }
             $list = $this->where($map)->field('arenaId')->order($order)->limit("$offset,$limit")->select();
             if(!$list){
                 $list = array();
             }
            $cacheArr[$key] = $list;
            if(!isset($cacheArr['time'])){
                $cacheArr['time'] = time();
            }
            Mmc($type, json_encode($cacheArr),0,60*11);
            return $list;
        }

    }

    //擂台列表
    public function arenaList($uid=0,$limit = 10, $page = 1, $order='arenaId DESC'){
        //不需要缓存，因为不用复杂排序
//        $list = $this->getCacheArenaPage($uid,$limit = 10, $page = 1, $order='arenaId DESC');
        $map['isDel'] = 0;
        $map['pid'] = 0;
        if ($uid) {
            $map['uid'] = $uid;
        }
        $offset = ($page - 1) * $limit;
        $list = $this->where($map)->field('arenaId')->order($order)->limit("$offset,$limit")->select();
        if (!$list) {
            $list = array();
        }

        foreach ($list as &$value) {
            $this->pkExpireOperation($value['arenaId']);
            $info = $this->getArenaInfo($value['arenaId']);
            $value['arenaStatus'] = $info['arenaStatus'];
            $value['arenaCommentCount'] = $info['commentCount'];
            $value['user'] = $this->getArenaUser($value['arenaId']);
            $value['challengeCountdown'] = $this->challengeCountdown($info['arenaStatus']);
            $value['pkCountdown'] = $this->pkCountdown($info['arenaStatus']);
        }
        return $list;
    }

    //获取擂台擂主及挑战者信息
    public function getArenaUser($arenaId){
        $cid = M('MakefriendsArena')->getField('arenaId','pid='.$arenaId); //挑战者arenaId
        $user[0] = array('uid'=>$this->getField('uid','arenaId='.$arenaId),'isChampion'=>1,'arenaId'=>$arenaId);    //擂主
        $user[1] = array('uid'=>$this->getField('uid','pid='.$arenaId),'isChampion'=>0,'arenaId'=>$cid);    //挑战者
        foreach($user as &$val){
            $dao = D('MakefriendsUser','makefriends');
            $val['nickname'] = $dao->getNickname($val['uid']);
            $val['headPhoto_middle'] = $dao->getHeadPhoto($val['uid'],'middle');
            $val['headPhoto_small'] = $dao->getHeadPhoto($val['uid'],'small');
            $val['headPhoto_big'] = $dao->getHeadPhoto($val['uid'],'big');
            $val['headPhoto_original'] = $dao->getHeadPhoto($val['uid'],'original');
            $info = $this->getArenaInfo($val['arenaId']);
            $val['voteCount'] = $info['voteCount'];
            $val['photoId'] = $info['photoId'];
            $p = D('MakefriendsPhoto','makefriends');
            $val['pic_big'] = $p->getPhotoPic($val['photoId'],'big', $photo);
            $val['pic_middle'] = $p->getPhotoPic($val['photoId'],'middle', $photo);
            $val['pic_middle'] = $p->getPhotoPic($val['photoId'],'original', $photo);
            $val['declaration'] = $info['declaration'];
            $val['publishTime'] = $info['cTime'];
            $val['declarationTime'] = $info['cTime'];
        }
        return $user;
    }

    //擂台详情
    public function arenaDetail($arenaId){
        $this->pkExpireOperation($arenaId);
        $info = array();
        $infos = $this->getArenaInfo($arenaId);
        $info['arenaId'] = $infos['arenaId'];
        $info['arenaStatus'] = $infos['arenaStatus'];
        $info['arenaCommentCount'] = $infos['commentCount'];
        $info['challengeCountdown'] = $this->challengeCountdown($arenaId);
        $info['pkCountdown'] = $this->pkCountdown($arenaId);
        $info['arenaUserInfoList'] = $this->getArenaUser($arenaId);
        return $info;
    }

    //评论数+1
    public function setCommentCountInc($arenaId){
        $res = $this->setInc('commentCount', 'arenaId =' . $arenaId);
        if($res){
            return true;
        }else{
            return false;
        }
    }

    //擂台挑战倒计时
    public function challengeCountdown($arenaId){
        $info = $this->getArenaInfo($arenaId);
        $cTime = $info['cTime'];
        $lastTime = $cTime + (48*3600) - time();
        $lastTime = $lastTime >= 0 ? $lastTime :0;
        return $lastTime;
    }

    //擂台pk倒计时
    public function pkCountdown($arenaId){
        $info = $this->getArenaInfo($arenaId);
        if($info['arenaStatus'] == 1){
            return -1;//未开始
        }
        $startTime = $info['startTime'];
        $lastTime = $startTime + (24*3600) - time();
        $lastTime = $lastTime >= 0 ? $lastTime :0;
        return $lastTime;
    }

    //自动匹配
    public function autoMatch(){
        //获取所有24H未匹配的所有擂台
        //自动分组 如果单数自动排除一个
        //添加到数据库
        Mmc('Makefriends_arena_'.$pid,null);
    }

    //擂台PK到时间操作
    public function pkExpireOperation($arenaId){
        $info = $this->getArenaInfo($arenaId);
        //todo 判断pk是否已结束

        //判断是否到时间
        if($info['startTime'] + (3600*12) > time() || $info['arenaId'] != 0){
            return;
        }
        $this->where('arenaId='.$arenaId)->setField('arenaStatus',0);
        $this->where('pid='.$arenaId)->setField('arenaStatus',0);
        $pArenaId = $this->where('pid='.$arenaId)->getField('arenaId');//挑战者arenaId
        $pInfo = $this->getArenaInfo($pArenaId);
        if($info['voteCount'] > $pInfo['voteCount']){
              $this->where('arenaId='.$arenaId)->setField('arenaResult',1);
              $this->where('pid='.$pArenaId)->setField('arenaResult',2);
        }else if($info['voteCount'] < $pInfo['voteCount']){
              $this->where('arenaId='.$arenaId)->setField('arenaResult',2);
              $this->where('pid='.$pArenaId)->setField('arenaResult',1);
        }else{
              //平手
              $this->where('arenaId='.$arenaId)->setField('arenaResult',3);
              $this->where('pid='.$pArenaId)->setField('arenaResult',3);
        }
         Mmc('Makefriends_arena_'.$arenaId,null);
         Mmc('Makefriends_arena_'.$pArenaId,null);
    }

}
?>
