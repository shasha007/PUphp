<?php

class MakefriendsCommentModel extends Model {

    //评论列表 id照片id
    public function commentList($map, $limit = 10, $page = 1, $order = 'id DESC') {
        $offset = ($page - 1) * $limit;
        $result = $this->where($map)->field('id,content,uid,cTime')->order($order)->limit("$offset,$limit")->select();
        if (!$result) {
            return array();
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        foreach ($result as &$value) {
            $value['nickname'] = D('MakefriendsUser','makefriends')->getNickname($value['uid']);
            $value['headPhoto_small'] = $daoUser->getHeadPhoto($value['uid'],'big');
        }
        return $result;
    }

    public function addComment_old($uid, $photoId, $content) {
        if(!canDo($uid,5)){
            $this->error = '操作太频繁，请不要重复提交！';
            return false;
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        $checkUser = $daoUser->checkAndAddUser($uid);
        if(!$checkUser){
            $this->error = '本人账号错误';
            return false;
        }
        $daoPhoto = D('MakefriendsPhoto', 'makefriends');
        $photo = $daoPhoto->getPhotoInfo($photoId);
        if (!$photo) {
            $this->error = '该照片不存在或者已经被删除！';
            return false;
        }
        $strlen = mb_strlen(trim($content), 'UTF8');
        if ($strlen < 1 || $strlen > 140) {
            $this->error = '评论或回复内容必须在1到140之间！';
            return false;
        }
        $data['content'] = t($_REQUEST['content']);
        $data['uid'] = $uid;
        $data['photoId'] = $photoId;
        $data['cTime'] = time();
        $result = $this->add($data);
        if ($result) {
            doCando($uid);
            $daoPhoto->upPhotoInfo($photoId,'backCount',1,'+');//评论数+1
            if ($photo['uid'] != $uid) {
                //贡献值操作
                $daoUser->incGx($uid, 'comment', $photoId);
                //人气值操作
                $daoUser->incRq($photo['uid'], 'comment');
                $daoPhoto->upPhotoInfo($photoId,'newComment',1);
                $daoUser->upCache($photo['uid'], 'newComment', 1);
            }
            $daoUser->addBackAllCount($photo['uid']); //总评论数+1
            return true;
        } else {
            $this->error = '评论失败';
            return false;
        }
        return true;
    }

    public function addComment($uid, $photoId, $content,$rid=0) {
        if(!canDo($uid,5)){
            $this->error = '操作太频繁，请不要重复提交！';
            return false;
        }
        $daoUser = D('MakefriendsUser', 'makefriends');
        $checkUser = $daoUser->checkAndAddUser($uid);
        if(!$checkUser){
            $this->error = '本人账号错误';
            return false;
        }
        $daoPhoto = D('MakefriendsPhoto', 'makefriends');
        $photo = $daoPhoto->getPhotoInfo($photoId);
        if (!$photo) {
            $this->error = '该照片不存在或者已经被删除！';
            return false;
        }
        $strlen = mb_strlen(trim($content), 'UTF8');
        if ($strlen < 1 || $strlen > 140) {
            $this->error = '评论或回复内容必须在1到140之间！';
            return false;
        }
        $data['content'] = t($_REQUEST['content']);
        $data['uid'] = $uid;
        $data['photoId'] = $photoId;
        $data['rid'] = $rid;
        $data['cTime'] = time();
        $result = $this->add($data);
        if ($result) {
            doCando($uid);
            $daoPhoto->upPhotoInfo($photoId,'backCount',1,'+');//评论数+1
            if ($photo['uid'] != $uid) {
                //贡献值操作
                $daoUser->incGx($uid, 'comment', $photoId);
                //人气值操作
                $daoUser->incRq($photo['uid'], 'comment');
                $daoPhoto->upPhotoInfo($photoId,'newComment',1);
                $daoUser->upCache($photo['uid'], 'newComment', 1);
            }
            $daoUser->addBackAllCount($photo['uid']); //总评论数+1
            return true;
        } else {
            $this->error = '操作失败';
            return false;
        }
        return true;
    }

    /**
     * 评论消息列表
     */
    public function CommentMessage($uid,$limit=10,$page=1,$order='id DESC'){
           $offset = ($page-1) * $limit;
           $list = $this->where('uid='.$uid)->field('uid,photoId,content')->order($order)->limit("$offset,$limit")->select();
           
    }
}

?>
