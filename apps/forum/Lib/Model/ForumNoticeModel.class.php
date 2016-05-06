<?php
//个人消息列表
class ForumNoticeModel extends Model {

    public function apiNoticeList($map, $limit = 15, $page) {
        $offset = ($page - 1) * $limit;
        $list = $this->where($map)->field('id,tid,rid,hid,cTime')->order('id DESC')->limit("$offset,$limit")->select();
        $daoForum = M('forum');
        foreach ($list as &$v) {
            $v['type'] = 1;
            $contentId = $v['tid'];
            $tocontentId = $v['tid'];
            //回复评论
            if ($v['hid'] > 0) {
                $v['type'] = 2;
                $contentId = $v['hid'];
                $tocontentId = $v['rid'];
            //评论帖子
            } elseif ($v['rid'] > 0) {
                //回复的对象
                $contentId = $v['rid'];
                $tocontentId = $v['tid'];
            }
            $content = $daoForum->field('isDel,content,uid')->where('id='.$contentId)->find();
            if(!$content || $content['isDel']){
                $v['content'] = '该内容已经被删除';
            }else{
                $v['content'] = $content['content'];
            }
            $user = D('User', 'home')->getUserByIdentifier($content['uid'], 'uid');
            $v['sex'] = $user['sex'];
            $v['school'] = $user['school'];
            if($contentId == $tocontentId){
                $v['tocontent'] = $v['content'];
            }else{
                $tocontent = $daoForum->field('isDel,content')->where('id='.$tocontentId)->find();
                if(!$tocontent || $tocontent['isDel']){
                    $v['tocontent'] = '该内容已经被删除';
                }else{
                    $v['tocontent'] = $tocontent['content'];
                }
            }
        }
        return $list;
    }

    public function delNotice($map) {
        $list = $this->where($map)->delete();
        return $list;
    }

}

?>
