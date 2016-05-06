<?php

class ForumModel extends Model {

    //泡泡列表
    public function apiForumList($map, $limit = 10, $page = 1,$order='id DESC') {
        $this->_cleanCacheCount();
        $offset = ($page - 1) * $limit;
        $list = $this->where($map)->field('id,sid,content,backCount,cTime,uid,readCount,praiseCount,photoId,isDel,tid,rid')->order($order)->limit("$offset,$limit")->select();
        foreach ($list as &$value) {
            $user = D('User', 'home')->getUserByIdentifier($value['uid'], 'uid');
            $value['sex'] = $user['sex'];
            $value['school'] = tsGetSchoolName($value['sid']);
            $value['pic_o'] = '';
            $value['pic_m'] = '';
            if ($value['photoId']) {
                $attach = getAttach($value['photoId']);
                $file = $attach['savepath'] . $attach['savename'];
                $value['pic_o'] = tsMakeThumbUp($file, 60, 60, 'no');
                $value['pic_m'] = tsMakeThumbUp($file, 465, 0, 'f');
            }
            //判断是否被删除
            if($value['isDel']==1){
                $value['content']='该内容已经被删除';
            }
            unset($value['sid']);
            unset($value['photoId']);
            unset($value['isDel']);
        }
        return $list;
    }

    public function getCount($map){
        return $this->where($map)->count();
    }

    public function apiBackList($map, $limit = 10, $page) {
        $offset = ($page - 1) * $limit;
        $list = $this->where($map)->field('content,cTime')->order('id ASC')->limit("$offset,$limit")->select();
        return $list;
    }

    //增加阅读数 缓存超过1000个或半小时 更新数据库
    public function addReadCount($tid){
        Mmc('forum_read_'.$tid,1,0,0,true);
        $cache = Mmc('forum_read_tids');
        $tids = array();
        if($cache !== false){
            $tids = json_decode($cache,true);
        }
        if(!in_array($tid, $tids)){
            $tids[] = $tid;
            Mmc('forum_read_tids',  json_encode($tids));
        }
    }

    private function _saveReadCount($tid,$count){
        Mmc('forum_read_'.$tid,null);
        Mmc('forum_readtime_'.$tid,null);
        $this->setInc('readCount', 'id='.$tid,$count);
    }

    private function _cleanCacheCount(){
        $time = Mmc('forum_read_time');
        if($time === false){
            Mmc('forum_read_time',time());
            return;
        }
        if(time()-$time<60){
            return;
        }
        $tids = Mmc('forum_read_tids');
        if($tids === false){
            return;
        }
        Mmc('forum_read_tids',null);
        Mmc('forum_read_time',time());
        $tids = json_decode($tids,true);
        foreach ($tids as $tid) {
            $count = Mmc('forum_read_'.$tid);
            if($count !== false){
                $this->_saveReadCount($tid, $count);
            }
        }
    }
    
    public function del($uid,$id){
      $map['uid'] = $uid;
      $map['id'] = $id;
      if(!$this->where('id='.$id)->find()){
          $this->error = '帖子不存在';
          return false;//帖子不存在
      }
      if(!$this->where($map)->find()){
          $this->error = '只能删除自己帖子';
          return false;//只能删除自己帖子
      }
      $data['isDel'] = 1;
      $res = $this->where($map)->save($data);
      if($res){
          return true;
      }else{
          $this->error = '系统错误';
          return false;//系统错误
      }
    }
}

?>
