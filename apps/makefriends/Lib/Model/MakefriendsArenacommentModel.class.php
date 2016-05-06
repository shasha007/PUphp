<?php

/*
 * 擂台评论
 */
class MakefriendsArenacommentModel extends Model
{

    public function addComment($uid, $arenaId, $content,$rid=0) {
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
        $doArena = D('MakefriendsArena', 'makefriends');
        $arena = $doArena->getArenaInfo($arenaId);
        if (!$arena) {
            $this->error = '该擂台不存在！';
            return false;
        }
        $strlen = mb_strlen(trim($content), 'UTF8');
        if ($strlen < 1 || $strlen > 140) {
            $this->error = '评论或回复内容必须在1到140之间！';
            return false;
        }
        $data['content'] = t($_REQUEST['content']);
        $data['uid'] = $uid;
        $data['arenaId'] = $arenaId;
        $data['rid'] = $rid;
        $data['cTime'] = time();
        $result = $this->add($data);
        if ($result) {
             doCando($uid);
             //评论数+1
             D('MakefriendsArena','makefriends')->setCommentCountInc($arenaId);
        } else {
           $this->error = '评论失败';
            return false;
        }
        return true;
    }
    
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
    
    
}
?>
