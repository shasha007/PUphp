<?php
//水区
class GroupFeedModel extends Model {

   public function getByGid($gid){
       return $this->where('gid='.$gid)->order('id DESC')->findPage(10);
   }

   public function addFeed($gid,$uid,$content){
       $data['content'] = t($content);
       if(empty($data['content'])){
           $this->error = '动态内容不能为空';
           return false;
       }
       if(!empty($_FILES['pic']['name'])){
            if(!isImg($_FILES['pic']['tmp_name'])){
                $this->error = '图片文件格式不对';
                return false;
            }
            $options = array();
            $options['allow_exts'] = 'jpeg,gif,jpg,png,bmp';
            $options['save_to_db'] = false;
            $info = X('Xattach')->upload('groupFeed',$options);
            if($info['status']){
                 //图片id
                $data['img'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
            }
        }
       $data['gid'] = $gid;
       $data['uid'] = $uid;
       $data['ctime'] = time();
       $res = $this->add($data);
       if ($res) {
           model('TjGday')->addGday($gid,'Gday_topic');
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

    public function apiFeedList($map=array(), $limit=10, $page=1, $order = 'id DESC'){
        $sql = $this->where($map)->field('id,uid,content,ctime,img')->order($order);
        $offset = ($page - 1) * $limit;
        $data = $sql->limit("$offset,$limit")->select();
        if(empty($data)){
            return array();
        }
        foreach ($data as $k => $v) {
            $data[$k]['content'] = htmlspecialchars_decode($v['content']);
            $data[$k]['ctime'] = friendlyDate($v['ctime']);
            $imgSmall = '';
            if($v['img']){
                $data[$k]['img'] = PIC_URL.'/data/uploads/'.$v['img'];
                $imgSmall = tsMakeThumbUp($v['img'], 120, 120);
            }
            $data[$k]['imgSmall'] = $imgSmall;
            $data[$k]['uface'] = getUserFace($v['uid'], 'b');
        }
        return $data;
    }
}

?>
