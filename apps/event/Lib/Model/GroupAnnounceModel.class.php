<?php

class GroupAnnounceModel extends Model {

   public function getByGid($gid){
       return $this->where('gid='.$gid)->order('id DESC')->findPage(10);
   }

   public function addAnnounce($gid,$groupName,$uid,$realname,$announce){
       $data['content'] = t(getShort($announce, 60));
       if(empty($data['content'])){
           $this->error = '公告内容不能为空';
           return false;
       }
       $data['gid'] = $gid;
       $data['uid'] = $uid;
       if($realname==''){
            $realname = M('user')->getField('realname', 'uid='.$uid);
        }
       $data['realname'] = $realname;
       $data['ctime'] = time();
       $res = $this->add($data);
       if ($res) {
           model('TjGday')->addGday($gid,'Gday_announce');
           $members = M('group_member')->where('gid=' . $gid . " AND status=1 AND level>0")->field('uid')->findAll();
           if ($members) {
               $uids = getSubByKey($members, 'uid');
               if($groupName==''){
                   $groupName = M('group')->getField('name', 'id='.$gid);
               }
                $notify_dao = service('Notify');
                $notify_data = array('gName' => $groupName,'content'=>$announce,'group_id'=>$gid);
                $notify_dao->sendIn($uids, 'event_g_ann', $notify_data);
           }
           //todo 推送

//            $log = "发布公告: {$data['content']}";
//            D('GroupLog')->writeLog($gid, $uid, $log, 'setting');
            return true;
        }
        $this->error = '操作失败';
        return false;
   }

   public function remove($gid, $id) {
        $id = is_array($id) ? '(' . implode(',', $id) . ')' : '(' . $id . ')';  //判读是不是数组回收
        $count = $this->where('gid='.$gid.' and id IN' . $id)->count();
        $res = $this->where('gid='.$gid.' and id IN' . $id)->delete(); //回收话题
        if ($res) {
            model('TjGday')->addGday($gid,'Gday_article_del',0,$count);
            return true;
        }
        $this->error = '操作失败';
        return false;
    }

   public function getFirst($gid) {
        $res = $this->where('gid='.$gid)->order('id DESC')->field('content')->find();
        if($res){
            return $res['content'];
        }
        return '';
    }

    public function apiAnnounceList($map=array(), $limit=10, $page=1, $order = 'id DESC'){
        $sql = $this->where($map)->field('id,uid,realname,content,ctime')->order($order);
        $offset = ($page - 1) * $limit;
        $data = $sql->limit("$offset,$limit")->select();
        if(empty($data)){
            return array();
        }
        foreach ($data as $k => $v) {
            $data[$k]['content'] = htmlspecialchars_decode($v['content']);
            $data[$k]['ctime'] = friendlyDate($v['ctime']);
        }
        return $data;
    }
}

?>
