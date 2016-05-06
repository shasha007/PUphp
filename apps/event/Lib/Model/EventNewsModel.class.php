<?php

/**
 * EventNewsModel
 * 活动的新闻模型
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class EventNewsModel extends BaseModel {

    public function doDelete($map) {
        return $this->where($map)->setField('isDel', 1);
    }

    public function getHandyList($map = array(), $limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;
        $map = array_merge(array('isDel'=>0),$map);
        $list = $this->field('id,title,cTime')->where($map)->order('id DESC')->limit("$offset,$limit")->select();
        foreach ($list as &$v) {
            $v['cTime'] = date('Y-m-d H:i', $v['cTime']);
            $v['title'] = htmlspecialchars_decode($v['title']);
            $v['url'] = U('home/Public/EventNews',array('id'=>$v['id']));
        }
        return $list;
    }

    public function getNews($map){
        $res = $this->field('id,title,content,cTime')->where($map)->find();
        if($res){
            $res['title'] = htmlspecialchars_decode($res['title']);
            $res['content'] = htmlspecialchars_decode($res['content']);
            return $res;
        }
        return FALSE;
    }

}
