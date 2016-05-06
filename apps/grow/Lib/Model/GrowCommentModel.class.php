<?php

class GrowCommentModel extends Model {

    public function commentList($map, $limit = 10, $page = 1, $order = 'id DESC') {
        $offset = ($page - 1) * $limit;
        $result = $this->where($map)->field('id,content,uid,cTime')->order($order)->limit("$offset,$limit")->select();
        if (!$result) {
            return array();
        }
        foreach ($result as &$val) {
            $val['uname'] = getUserField($val['uid'],'uname');
            $val['uface'] = getUserFace($val['uid'],'b');
            $val['content'] = htmlspecialchars_decode($val['content']);
        }
        $list['count'] = $this->where($map)->count();
        $list['data'] = $result;
        return $list;
    }

    public function apiComment($uid, $grow_id, $content) {
        $strlen = mb_strlen($content, 'UTF8');
        if ($strlen < 1 || $strlen > 140) {
            $this->error = '评论内容必须在1到140之间！';
            return false;
        }
        $content = t($_REQUEST['content']);
        $strlen = mb_strlen($content, 'UTF8');
        if ($strlen < 1 || $strlen > 140) {
            $this->error = '评论内容必须在1到140之间！';
            return false;
        }
        $data['content'] = $content;
        $data['uid'] = $uid;
        $data['grow_id'] = $grow_id;
        $data['ctime'] = time();
        $result = $this->add($data);
        if ($result) {
            return true;
        } else {
            $this->error = '评论失败';
            return false;
        }

    }

    //统计评论数
    public function countComment($grow_id){
        $res = $this->where('grow_id='.$grow_id)->count();
        return $res;
    }
}

?>
