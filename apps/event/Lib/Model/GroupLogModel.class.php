<?php

class GroupLogModel extends Model {

    //写话题日志
    function writeLog($gid, $uid, $content, $type = 'topic') {
        if(empty($content)){
            return true;
        }
        if(strpos($type, 'log_')=== false){
            return true;
        }
        $map['gid'] = $gid;
        $map['uid'] = $uid;
        $map['type'] = $type;
        $map['content'] = $content;
        $map['ctime'] = time();
        $this->add($map);
    }

    //写成员日志
    function writeMemberLog() {

    }

    //写设置日志
    function writeSettingLog() {

    }

    public function delById($ids){
        if(!$ids){
            return true;
        }
        $map['id'] = array('IN', $ids);
        return $this->where($map)->delete();
    }

}

?>
