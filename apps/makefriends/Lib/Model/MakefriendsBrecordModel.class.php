<?php

class MakefriendsBrecordModel extends Model
{
    //添加浏览记录
    public function addRecord($uid,$photoId){
        if(!$uid || !$photoId){
            return false;
        }
        $map['uid'] = $uid;
        $map['photoId'] = $photoId;
        if($this->where($map)->select()){
            $this->where($map)->setField('cTime',time());
            return true;
        }
        $data = $map;
        $data['cTime'] = time();
        $res = $this->add($data);
        return $res;
    }

    //照片浏览UID
    public function recordUid($photoId){
       $res = $this->where('photoId = '.$photoId)->field('uid')->order('id DESC')->limit("0,10")->select();
       if(!$res){
           return array();
       }
       return $res;
    }

    //浏览头像
    public function record($photoId){
        $uid = $this->recordUid($photoId);
        foreach($uid as &$val){
            $val['head'] = D('MakefriendsUser', 'makefriends')->getHeadPhoto($val['uid'],'big');
        }
        return $uid;
    }
}
?>
