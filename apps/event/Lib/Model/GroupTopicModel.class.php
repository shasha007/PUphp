<?php

class GroupTopicModel extends Model {

    //获取帖子
    function getThread($tid, $field = '*') {

        $thread = $this->where('id=' . $tid . ' AND is_del=0')->field($field)->find();

        if ($thread) {
            $thread['content'] = D('GroupPost')->getField('content', 'istopic=1 AND tid=' . $tid);
            $thread['pid'] = D('GroupPost')->getField('id', 'istopic=1 AND tid=' . $tid);
        }
        return $thread;
    }

    //获取大事件
    function getEvent($tid, $field = '*') {

        $thread = $this->where('id=' . $tid . ' AND is_del=0 AND isEvent=1')->field($field)->find();
        if ($thread) {
            $thread['content'] = D('GroupPost')->getField('content', 'istopic=1 AND tid=' . $tid);
            $thread['pid'] = D('GroupPost')->getField('id', 'istopic=1 AND tid=' . $tid);
        }
        return $thread;
    }

        //获取制度
    function getRule($tid, $field = '*') {

        $thread = $this->where('id=' . $tid . ' AND is_del=0 AND isRule=1')->field($field)->find();
        if ($thread) {
            $thread['content'] = D('GroupPost')->getField('content', 'istopic=1 AND tid=' . $tid);
            $thread['pid'] = D('GroupPost')->getField('id', 'istopic=1 AND tid=' . $tid);
        }
        return $thread;
    }

    //回收站
//    function remove($id) {
//        $id = is_array($id) ? '(' . implode(',', $id) . ')' : '(' . $id . ')';  //判读是不是数组回收
//        $uids = D('GroupTopic')->field('uid')->where('id IN' . $id)->findAll();
//        $res = D('GroupTopic')->setField('is_del', 1, 'id IN' . $id); //回收话题
//        if ($res) {
//            D('GroupPost')->setField('is_del', 1, 'tid IN' . $id); //回复
//            // 积分
////            foreach ($uids as $vo) {
////                X('Credit')->setUserCredit($vo['uid'], 'group_delete_topic');
////            }
//        }
//        return $res;
//    }

    // 删除
    function del($gid,$id) {
        $id = in_array($id) ? '(' . implode(',', $id) . ')' : '(' . $id . ')';  //判读是不是数组回收
        $this->where('gid='.$gid.' and id IN' . $id)->delete(); //删除话题
        $daoPost = D('GroupPost');
        $daoPost->where('tid IN' . $id)->delete(); //删除回复
//        $count1 = $this->where('gid='.$gid.' and id IN' . $id)->count();
//        $count2 = $daoPost->where('gid='.$gid.' and tid IN' . $id)->count();
//        $count = 2*$count1+($count2-1);
//        model('TjGday')->addGday($gid,-1*$count);
    }

    function recover($id) {
        $id = in_array($id) ? '(' . implode(',', $id) . ')' : '(' . $id . ')';  //判读是不是数组回收
        D('GroupTopic')->setField('is_del', 0, 'id IN' . $id); //回收话题
        D('GroupPost')->setField('is_del', 0, 'tid IN' . $id); //回复
    }

}

?>
