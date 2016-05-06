<?php

class ForumPraiseModel extends Model {

    public function zan($uid,$tid){
        $data['uid'] = $uid;
        $data['tid'] = $tid;
        $data['ctime'] = time();
        $res = $this->add($data);
        if($res){
            M('forum')->setInc('praiseCount', 'id='.$tid);
            return true;
        }
        $this->error = '您已赞过，不可重复';
        return false;
    }

}

?>
