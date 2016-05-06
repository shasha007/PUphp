<?php
class GroupPostModel extends Model{

//     // 回收站
//     function remove($id){
//     	$id = is_array($id) ? '('.implode(',',$id).')' : '('.$id.')';  //判读是不是数组回收
//     	$uids = D('GroupPost')->field('uid')->where('id IN' . $id)->findAll();
//     	$res  = D('GroupPost')->setField('is_del', 1, 'id IN' . $id); //回复
//     	if ($res) {
//     		// 积分
//     		foreach ($uids as $vo) {
//     			X('Credit')->setUserCredit($vo['uid'], 'group_reply_topic', -1);
//     		}
//     	}
//     	return $res;
//     }

      // 删除
     function del($gid,$id) {
     	$id = in_array($id) ? '('.implode(',',$id).')' : '('.$id.')';  //判读是不是数组回收
//        $count = $this->where('gid='.$gid.' and id IN' . $id)->count();
     	$res = $this->where('gid='.$gid.' and id IN' . $id)->delete(); //删除回复
        if ($res) {
//            model('TjGday')->addGday($gid,-1*$count);
            return true;
        }
        $this->error = '操作失败';
        return false;
     }

}

?>
