<?php

class TrainCollectModel extends Model {

    public function doCollect($uid,$id) {
        $data['uid'] = $uid;
        $data['courseId'] = $id;
        $result = $this->where($data)->find();
        if ($result) {
            $this->error = '您已收藏该课程';
            return false;
        }
        $res = $this->add($data);
        if ($res) {
            M('train')->setInc('collect', 'id='.$id);
            unset($_SESSION['train_collect']);
            return true;
        } else {
            $this->error = '收藏失败';
            return false;
        }
    }

    public function cancelCollect($uid,$id) {
        $map['uid'] = $uid;
        $map['courseId'] = $id;
        $res = $this->where($map)->delete();
        if ($res) {
            M('train')->setDec('collect', 'id='.$id);
            unset($_SESSION['train_collect']);
            return true;
        } else {
            $this->error = '取消收藏失败';
            return false;
        }
    }

    public function getCollect($uid,$session=false){
        if($session){
            if(isset($_SESSION['train_collect'])){
                return unserialize($_SESSION['train_collect']);
            }else{
                $collect = $this->getCollect($uid,false);
                $_SESSION['train_collect'] = serialize($collect);
                return $collect;
            }
        }
        $map['uid'] = $uid;
        $res = $this->where($map)->field('courseId')->findAll();
        if($res){
            return getSubByKey($res, 'courseId');
        }
        return array();
    }

}

?>
