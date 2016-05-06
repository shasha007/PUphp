<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of GroupModel
 *
 * @author Administrator
 */
class EventGroupModel extends Model {

    var $tableName = 'group';

    public function getHotList($sid,$reset = false) {
        // 1分钟锁缓存
//        if (!($cache = S('Cache_Event_Group_Hot_list')) || $reset) {
//            S('Cache_Event_Group_Hot_list_t', time()); //缓存未设置 先设置缓存设定时间
//        } else {
//            if (!($cacheSetTime = S('Cache_Event_Group_Hot_list_t')) || $cacheSetTime + 60 <= time()) {
//                S('Cache_Event_Group_Hot_list_t', time()); //缓存未设置 先设置缓存设定时间
//            } else {
//                return $cache;
//            }
//        }
        // 缓存锁结束

        $data= $this->field('id,id AS gid,name,logo,membercount,intro,ctime,cid0,cid1,vStern')
                ->where('status=1 AND is_del=0 AND disband=0  AND school='.$sid)
                ->order('membercount DESC, id DESC')
                ->limit(5)
                ->findAll();
        //var_dump($this->getLastSql());
        S('Cache_Event_Group_Hot_list', $data);
        return $data;
    }

    //api获取我的社团
    public function apiGetAllMyGroup($mid, $limit = 10, $page) {
        $groupList = $this->table(C('DB_PREFIX') . "group_member as member left join " . C('DB_PREFIX') . "group as g on g.id = member.gid")
                ->field('g.id,g.name,g.sid1,g.openWeibo,g.type,g.membercount,g.logo,g.cid0,g.cid1,g.ctime,g.status,g.vStern,g.intro')
                ->where('member.uid = ' . $mid . ' and member.level>0 and g.is_del = 0 ')
                ->order('member.level ASC,member.id DESC');
        $offset = ($page - 1) * $limit;
        $data = $groupList->limit("$offset,$limit")->select();
        return $data;
    }

    public function apiGetMyGroupTop($mid, $limit = 10, $page,$sid) {
        $groupList = $this->table(C('DB_PREFIX') . "group_member as member left join " . C('DB_PREFIX') . "group as g on g.id = member.gid")
                ->field('g.id,g.name,g.logo,g.activ_num')
                ->where('member.uid = ' . $mid . ' and member.level>0 and g.is_del = 0 ')
                ->order('g.activ_num DESC,member.id DESC');
        $offset = ($page - 1) * $limit;
        $data = $groupList->limit("$offset,$limit")->select();


        return $this->_apiGroupTopList($data,$sid);
    }

    private function _apiGroupTopList($list,$sid){
        foreach($list as &$g) {
            $map['prov_id'] = 1;
            $map['is_del'] = 0;
            $map['activ_num'] = array('gt',$g['activ_num']);
            $allTop = $this->where($map)->count();
            $g['allTop'] = $allTop + 1;
            $map1['school'] = $sid;
            $map1['is_del'] = 0;
            $map1['activ_num'] = array('gt',$g['activ_num']);
            $schoolTop = $this->where($map1)->count();
            $g['schoolTop'] = $schoolTop + 1;
            if($g['logo']=='default.gif'){
                $g['logo'] = defaultGroupImg($g['id']);
            }else{
                $g['logo'] = tsMakeThumbUp($g['logo'], 300, 0, 'f');
            }
            unset($g['activ_num']);
        }
        return $list;
    }
    public function apiGetAllMyGroup2($mid, $limit = 10, $page) {
        $offset = ($page - 1) * $limit;
        $list = $this->table(C('DB_PREFIX') . "group_member as member left join " . C('DB_PREFIX') . "group as g on g.id = member.gid")
                ->field('g.id,g.name,g.sid1,g.logo,g.membercount,g.vStern,g.cid0,g.activ_num')
                ->where('member.uid = ' . $mid . ' and member.level>0 and g.is_del = 0 ')
                ->order('member.level ASC,member.id DESC')
                ->limit("$offset,$limit")->select();
        return $this->_apiGroupList($list);
    }

    public function getGroupList($map = '',$mid,$order = 'activ_num DESC, id DESC') {
        $result = $this->where($map)->field('id,uid,vStern,name,logo,cid0,sid1,membercount,intro,activ_num')->order($order)->findPage(10);
//追加必须的信息
        if (!empty($result['data'])) {
            foreach ($result['data'] as &$value) {
//                $value['count'] = D('Groupmember')->memberCount($value['id']);
                $value['category'] = $this->getCategoryField($value['cid0']);
                $value['schoolName'] = $this->getSchoolName($value['sid1']);
                $value['isMember'] = D('GroupMember')->isMember($mid,$value['id']);
            }
        }
        return $result;
    }

    public function apiGroupList($map = array(), $fields = null, $order = 'activ_num DESC, id DESC', $limit = null) {
        //处理where条件
        $map['is_del'] = 0;
        $map['disband'] = 0;
        //连贯查询.获得数据集
        $result = $this->where($map)->field($fields)->order($order)->findPage($limit);
        return $result;
    }
    public function apiGroupListLu($map, $limit = 10, $page = 1, $order = 'id DESC',$fields='*') {
        //处理where条件
        $map['is_del'] = 0;
        $map['disband'] = 0;
        //连贯查询.获得数据集
        $offset = ($page - 1) * $limit;
        $list = $this->where($map)->field($fields)->order($order)->limit("$offset,$limit")->select();
        if (!$list) {
            return array();
        }
        return $this->_apiGroupList($list);
    }
    private function _apiGroupList($list){
        $cats = D('Category','group')->catList();
        foreach($list as &$g) {
            $g['cname0'] = $cats[$g['cid0']]['title'];
            if($g['logo']=='default.gif'){
                $g['logo'] = defaultGroupImg($g['id']);
            }else{
            	//$g['logo'] = tsMakeThumbUp($g['logo'], 300, 0, 'f');
            	//20160217调整
                 $g['logo'] = tsMakeThumbUp($g['logo'], 300, 300, 'c'); 
            }
            $g['depart'] = $g['sid1'] <= 0 ? '校级部落' : tsGetSchoolTitle($g['sid1']);
        }
        return $list;
    }

    public function getCategoryField($cid) {
        $title = M('group_category')->where('id =' . $cid)->getField('title');
        return $title;
    }

    public function getSchoolName($sid1) {
        if($sid1>0){
           $title = M('school')->where("id =$sid1")->getField('title');
        }else if($sid1=='-1'){
           $title = '校级';
        }
        return $title;
    }

    public function getNewList($sid) {
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $map['school'] = $sid;
        $result = $this->where($map)->field('id,name,logo,intro')->order('id DESC')->limit(6)->findAll();
        return $result;
    }

    //某人加入某部落
    public function joinGroup($mid, $gid, $level, $incMemberCount = false, $reason = '') {
        if (M('group_member')->where("uid=$mid AND gid=$gid")->find())
            exit('你已经加入过');

        $member['uid'] = $mid;
        $member['gid'] = $gid;
        $member['name'] = getUserName($mid);
        $member['level'] = $level;
        $member['ctime'] = time();
        $member['mtime'] = time();
        $member['reason'] = $reason;
        $ret = M('group_member')->add($member);

        // 不需要审批直接添加，审批就不用添加了。
        if ($incMemberCount) {
            // 成员统计
            $this->setInc('membercount', 'id=' . $gid);
        }
        if ($level == 1 || $level == 2) {
            //修改部落活动发起权限
            $daoEventGroup = M('event_group');
            $data['gid'] = $gid;
            $data['uid'] = $mid;
            $daoEventGroup->add($data);
            M('user')->where('uid =' . $mid)->setField('can_add_event', 1);
        }

        return $ret;
    }


      public function disBandGroup($gid) {
        $result = $this->where('id=' . $gid)->setField(array('is_del', 'disband'), array(1, 1));
        if (!$result)
            return false;
        $daoEventGroup = M('event_group');
        $uids = $daoEventGroup->where('gid =' . $gid)->field('uid')->findAll();
        if (!$uids)
            return true;
        $daoUser = M('user');
        $daoEventGroup->where('gid =' . $gid)->delete();
        foreach ($uids as $v) {
            $addevent = $daoEventGroup->where('uid=' . $v['uid'])->getField('id');
            if (!$addevent) {
                $daoUser->where('uid =' . $v['uid'])->setField('can_add_event', 0);
            }
        }
        return true;
    }

    //彻底删除部落
    public function endDel($gid){
        if($this->where('id='.$gid)->delete()){
            //发起活动权限
            $daoEventGroup = M('event_group');
            $uids = $daoEventGroup->where('gid =' . $gid)->field('uid')->findAll();
            if ($uids){
                $daoUser = M('user');
                $daoEventGroup->where('gid =' . $gid)->delete();
                foreach ($uids as $v) {
                    $addevent = $daoEventGroup->where('uid=' . $v['uid'])->getField('id');
                    if (!$addevent) {
                        $daoUser->where('uid =' . $v['uid'])->setField('can_add_event', 0);
                    }
                }
            }
            //删除部落成员
            M('group_member')->where('gid =' . $gid)->delete();
            M('group_topic')->where('gid =' . $gid)->delete();
            M('group_post')->where('gid =' . $gid)->delete();
            //删除共享
            $daoAttach = M('group_attachment');
            $files = $daoAttach->where('gid='.$gid)->field('attachId')->findAll();
            if($files){
                $attIds = getSubByKey($files, attachId);
                model('Attach')->deleteAttach($attIds, true);
            }
            $daoAttach->where('gid='.$gid)->delete();
            return true;
        }
        return false;
    }

    //处理排行榜数据
    public function editList($list,$uid){
        $mark = 0;
        foreach ($list as &$v) {
            $v['school'] = tsGetSchoolName($v['school']);
            if(!$v['logo'] || $v['logo']=='default.gif'){
                $v['logo'] = defaultGroupImg($v['id']);
            }else{
                $v['logo'] = tsMakeThumbUp($v['logo'], 300, 0, 'f');
            }
            $map['gid'] = $v['id'];
            $map['uid'] = $uid;
            $map['level'] =array('gt',0);
            $res = D('group_member')->where($map)->find();
            if($res){
                $mark += 1;
            }
       }
       $list['num'] = $mark;
       return $list;
    }

}

?>
