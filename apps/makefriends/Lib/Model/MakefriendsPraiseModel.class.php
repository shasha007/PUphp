<?php

class MakefriendsPraiseModel extends Model {

    public function zan($uid,$photoId){
        $daoUser = D('MakefriendsUser','makefriends');
        $checkUser = $daoUser->checkAndAddUser($uid);
        if(!$checkUser){
            $this->error = '本人账号错误';
            return false;
        }
        //查看照片所属人
        $daoPhoto = D('MakefriendsPhoto', 'makefriends');
        $photo = $daoPhoto->getPhotoInfo($photoId);
        if(!$photo){
            $this->error = '照片不存在';
            return false;
        }
        $data['uid'] = $uid;
        $data['photoId'] = $photoId;
        $data['day'] = date('Y-m-d');
        $res = $this->add($data);
        if($res){
            $daoPhoto->upPhotoInfo($photoId,'praiseCount',1,'+');
            $daoPhoto->upPhotoInfo($photoId,'weekCount',1,'+');
            $daoPhoto->upPhotoInfo($photoId,'monthCount',1,'+');
            if($photo['uid'] != $uid){
                //贡献值操作
                $daoUser->incGx($uid,'praise',$photoId);
                 //人气值操作
                $daoUser->incRq($photo['uid'],'praise');
             }
            $daoUser->addPraiseAllCount($photo['uid']);//总赞数+1
            return true;
        }
        $this->error = '您已赞过，不可重复';
        return false;
    }

    /**
     * 是否赞过
     */
    public function hasZan($uid,$photoId){
        $map['uid'] = $uid;
        $map['photoId'] = $photoId;
        if(M('MakefriendsPraise')->where($map)->find()){
            return true;
        }else{
            return false;
        }
    }

    /**
     * 赞过某照片的uid列表
     * $photoId 照片id
     */
    public function zanJilu($photoId){
        $map['photoId'] = $photoId;
        $res = $this->where($map)->field('uid')->order('day DESC')->limit('0,10')->select();

        if(!$res){
            return array();
        }
        return $res;
    }


    //浏览头像
    public function record($photoId){
        $res = $this->zanJilu($photoId);
        foreach($res as &$val){
            $val['head'] = D('MakefriendsUser', 'makefriends')->getHeadPhoto($val['uid'],'big');
        }
        return $res;
    }



}

?>
