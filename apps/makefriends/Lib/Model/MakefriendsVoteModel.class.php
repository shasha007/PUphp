<?php
class MakefriendsVoteModel extends Model
{
    //添加投票记录
    public function arenaVote($uid,$arenaId,$toId){
        $info = D('MakefriendsArena','makefriends')->getArenaInfo($arenaId);
        if($info['arenaStatus']){
            $this->error = '擂台未开始或已结束';
            return false;
        }
        $map['uid'] = $uid;
        $map['arenaId'] = $arenaId;
        if($this->where($data)->count()){
            $this->error = '已经支持过';
            return false;
        }
        $data['uid'] = $toId;     
        $data['arenaId'] = $arenaId;
        $data['cTime'] = time();
        $res = $this->add($data);
        if($res){
            //判断是擂主还是挑战者
            $doArena = M('MakefriendsArena');
            if($doArena->where("arenaId = $arenaId AND uid = $toId")->count()){
                $doArena->setInc( 'voteCount','arenaId='.$arenaId);
            }else{
                 $doArena->setInc( 'voteCount','pid='.$arenaId);
            }
             return true;
        }else{
            $this->error = '系统错误';
            return false;
        }
    }
}
?>
