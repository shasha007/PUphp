<?php

include_once SITE_PATH . '/apps/event/Lib/Model/BaseModel.class.php';

/**
 * EventModel
 * 活动主数据库模型
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class CourseActiveModel extends BaseModel {

    var $mid;

    //首页置顶推荐活动
    public function getSchoolIndex($map, $limit = 4) {
        $map['isDel'] = 0;
        $map['status'] = 1;
        return $this->where($map)->field('id,title,sid,typeId,coverId,sTime,eTime,uid')
                        ->order('id DESC')->limit($limit)->findAll();
    }

    public function getCourseActiveList($map = '', $mid, $order = 'isTop DESC, id DESC',$page=10) {
        $this->mid = $mid;
        $result = $this->where($map)->order($order)->findPage($page);
        //追加必须的信息
        if (!empty($result['data'])) {
            $user = M('course_active_user');
            foreach ($result['data'] as &$value) {
                $value = $this->_appendContent($value);
                //计算待审核人数
                $value['verifyCount'] = $user->where("status = 0  AND courseId =" . $value['id'])->count();
                $res=$user->where("uid =".$this->mid." AND courseId =" . $value['id'])->find();
                $value['joined'] = $res ? 1 : 0;
            }
        }
        return $result;
    }

















    public function getAdminCode($eid) {
        $event = $this->where('id=' . $eid)->field('id,adminCode')->find();
        if (!$event) {
            return '';
        } elseif ($event['adminCode']) {
            return $event['adminCode'];
        } else {
            require_once(SITE_PATH . '/addons/libs/String.class.php');
            $randval = String::rand_string(2, 5);
            $code = $randval . $eid;
            $this->setField('adminCode', $code, 'id=' . $eid);
            return $code;
        }
    }


    /**
     * 追加和反解析数据
     * @param mixed $data
     * @access public
     * @return void
     */
    private function _appendContent($data) {
        $type = D('CourseType','event');
        $data['type'] = $type->getTypeName($data['typeId']);

        //反解析时间
        $data['time'] = date('Y-m-d H:i', $data['sTime']) . " 至 " . date('Y-m-d H:i', $data['eTime']);
        $data['dl'] = date('Y-m-d H:i', $data['deadline']);

        //追加权限
        $data += $this->checkMember($data['uid'], $this->mid);

        //追加是否已参加的判定
        $userDao = self::factoryModel('user');
        if ($result = $userDao->hasUser($this->mid, $data['id'])) {
            $data['canJoin'] = false;
            $data['hasMember'] = $result['status'];
            return $data;
        }

        return $data;
    }

    /**
     * checkRoll
     * 检查权限
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function checkMember($eventAdmin, $mid) {
        $result = array(
            'admin' => false,
            'canJoin' => true,
            'hasMember' => false,
        );
        if ($mid == $eventAdmin) {
            $result['admin'] = true;
            return $result;
        }

        return $result;
    }

    /**
     * doAddEvent
     * 添加活动
     * @param mixed $map
     * @param mixed $feed
     * @access public
     * @return void
     */

//根据存储路径，获取图片真实URL
    function get_photo_url($savepath) {
        return './data/uploads/' . $savepath;
    }

    public function doEditCourse($eventMap, $cover, $obj) {
        $eventMap['rTime'] = isset($eventMap['rTime']) ? $eventMap['rTime'] : time();
        if ($cover['status']) {
            foreach ($cover['info'] as $value) {
                if ($value['key'] == 'cover') {
                    //删除旧的
                    model('Attach')->deleteAttach($obj['coverId'], true, true);
                    $eventMap['coverId'] = $value['id'];
                } elseif ($value['key'] == 'logo') {
                    model('Attach')->deleteAttach($obj['logoId'], true, true);
                    $eventMap['logoId'] = $value['id'];
                }
            }
        }
        $eventMap['limitCount'] = 0 == intval($eventMap['limitCount']) ? 999999999 : $eventMap['limitCount'];
        $addId = $this->where('id =' . $obj['id'])->save($eventMap);

        return $addId;
    }

    /**
     * factoryModel
     * 工厂方法
     * @param mixed $name
     * @static
     * @access private
     * @return void
     */
    public static function factoryModel($name) {
        return D("Event" . ucfirst($name), 'event');
    }

    /**
     * doArgeeUser
     * 同意申请
     * @param mixed $data
     * @access public
     * @return void
     */
    public function doArgeeUser($data) {
        $userDao = self::factoryModel('user');
        if ($userDao->where('id=' . $data['id'])->setField('status', 1)) {
            $this->setInc('joinCount', 'id=' . $data['eventId']);
            $this->setDec('limitCount', 'id=' . $data['eventId']);
            X('Credit')->setUserCredit($data['uid'], 'join_event');
            $event = $this->where('id=' . $data['eventId'])->find();
            if ($event['is_school_event']) {
                //$this->getScore($data['id'], $data['uid'], $event['is_school_event'], $event['score']);
            }
            return true;
        }
        return false;
    }



    public function doEditData($time, $id) {
        //检查安全性，防止非管理员访问
        $uid = $this->where('id=' . $id)->getField('uid');
        if ($uid != $this->mid) {
            return -1;
        }

        if ($this->where('id=' . $id)->setField('deadline', $time)) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * getList
     * 供后台管理获取列表的方法
     * @param mixed $order
     * @param mixed $limit
     * @access public
     * @return void
     */
    public function getList($map, $order, $limit) {
        $result = $this->where($map)->order($order)->findPage($limit);
        //将属性追加
        foreach ($result['data'] as &$value) {
            $value = $this->_appendContent($value);
        }
        return $result;
    }

    /**
     * doDeleteEvent
     * 删除活动
     * @param mixed $eventId
     * @access public
     * @return void
     */
    public function doDeleteCourse($eventId) {
        //TODO 检查是否是管理员

        if (empty($eventId)) {
            return false;
        }
        $course = M('course_active')->where('id=' . $id)->field('sid')->find();
        if ($course['sid'] != $this->school['id']) {
            return false;
        }
        //取出选项ID
        $optsIds = $this->field('uid,title')->where($eventId)->findAll();
        foreach ($optsIds as &$v) {
            // 发送通知
            $notify_dao = service('Notify');
            $notify_data = array('title' => $v ['title']);
            $notify_dao->sendIn($v ['uid'], 'event_course_active_del', $notify_data);
        }
        //删除活动
        $this->where($eventId)->setField('isDel', 1);
        $news['eventId'] = $eventId['id'];
        D('EventNews')->doDelete($news);
        return true;
        /*
          if ($this->where($eventId)->delete()) {
          //删除选项
          self::factoryModel('opts')->where($opts_map)->delete();
          //删除成员
          $user_map['eventId'] = $eventId['id'];
          self::factoryModel('user')->where($user_map)->delete();
          return true;
          }
         */

        return false;
    }

    /**
     * doIsHot
     * 设置推荐
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function doIsHot($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "recommend":   //推荐
                $result = $this->where($map)->setField('isHot', 1);
                break;
            case "cancel":   //取消推荐
                $result = $this->where($map)->setField('isHot', 0);
                break;
        }
        return $result;
    }


    /**
     * 设置置顶
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function doIsTop($map, $act) {
        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "top":   //置顶
                $result = $this->where($map)->setField('isTop', 1);
                break;
            case "cancel":   //取消置顶
                $result = $this->where($map)->setField('isTop', 0);
                break;
        }
        return $result;
    }



    /**
     * getHotList
     * 推荐列表
     * @param mixed $map
     * @param mixed $act
     * @access public
     * @return void
     */
    public function getHotList($map = array()) {
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['isHot'] = 1;
        $result = $this->where($map)->order('isTop DESC, id DESC')->limit(5)->findAll();
        return $result;
    }

    /**
     * hasMember
     * 判断是否是有这个成员
     * @param mixed $uid
     * @access public
     * @return void
     */
    public function hasMember($uid, $eventId) {
        $user = self::factoryModel('user');
        if ($result = $user->where('uid=' . $uid . ' AND eventId=' . $eventId)->field('action,status')->find()) {
            return $result;
        } else {
            return false;
        }
    }

    /**
     * 审核通过
     * @param array $ids
     * @return boolean
     */
    public function doAudit($ids, $uid) {
        $map['id'] = array('IN', $ids);
        $res = $this->where($map)->setField(array('status', 'audit_uid'), array(2, $uid)); // 通过审核
        if ($res) {
            // 发送通知
            $courses = $this->where($map)->findAll();
            $notify_dao = service('Notify');
            foreach ($courses as $v) {
                $notify_data = array('title' => $v ['title'], 'courseId' => $v ['id']);
                $notify_dao->sendIn($v ['uid'], 'event_course_active_audit', $notify_data);
            }
        }
        return $res;
    }


    /**
     * 校方审核通过
     * @param array $ids
     * @return boolean
     */
    public function doSchoolAudit($id, $uid, $sid) {
        $res = $this->doAudit($id, $uid);
        return $res;
    }





    /**
     * 驳回
     * @param array $ids
     * @return boolean
     */
    public function doDismissed($ids, $reason, $del, $uid) {
        $map['id'] = array('IN', $ids);
        $res = $this->where($map)->setField(array('status', 'audit_uid'), array(0, $uid));
        if ($del) {
            $this->where($map)->setField('isDel', 1);
        }
        if ($res) {
            // 发送通知
            $courses = $this->where($map)->field('id,title,uid')->findAll();
            $notify_dao = service('Notify');
            foreach ($courses as $v) {
                $url = $v['title'];
                if (!$del) {
                    $link = U('event/LessonActive/index', array('id' => $v['id']));
                    $url = '<a href="' . $link . '">' . $v['title'] . '</a>';
                }
                $notify_data['title'] = $url;
                $notify_data['reason'] = $reason;
                $notify_dao->sendIn($v ['uid'], 'event_course_active_delaudit', $notify_data);
            }
        }
        return $res;
    }



    public function getTop($sid) {
        $map['isDel'] = 0;
        $map['sid'] = $sid;
        $map['status'] = array('in', '2,3');
        $map['isTop'] = 1;
        $re = $this->where($map)->field('title,credit,joinCount,logoId,id')
                        ->order('id DESC')->limit(10)->findAll();
        return $re;
    }

    /**
     * doAddUser
     * 添加用户行为
     * @param mixed $data
     * @access public
     * @return void
     */
    public function doAddUser($data) {
        $result = array('status' => 0);
        $userDao = M('course_active_user');
        //检查这个id是否存在
        if (false == $course = $this->where('id =' . $data['id'])->find()) {
            $result['info'] = '课程不存在';
            return $result;
        }
        if ($course['need_tel'] && empty($data['tel'])) {
            $result['info'] = '联系电话不能为空';
            return $result;
        }
        $mapuser['uid'] = $data['uid'];
        $mapuser['courseId'] = $data['id'];
        $re = $userDao->where($mapuser)->find();
        //检查是否已经加入
        if ($re) {
            $result['info'] = '已经报名该课程';
            return $result;
        }

        if (!$course['allow'] && $course['limitCount'] < 1) {
            $result['info'] = '人数已满！添加失败';
            return $result;
        }

        $map = $data;
        $map['courseId'] = $data['id'];
        unset($map['id']);
        $map['cTime'] = time();
        $map['status'] = $course['allow'] ? 0 : 1;
        $res = $userDao->add($map);
        if ($res) {
            $result['info'] = '报名成功，请等待审核';
            if ($map['status']) {
                $result['info'] = '报名成功';
                $this->setInc('joinCount', 'id=' . $map['courseId']);
                $this->setDec('limitCount', 'id=' . $map['courseId']);
                X('Credit')->setUserCredit($map['uid'], 'join_course');
            }
            $result['status'] = 1;
        } else {
            $result['info'] = '报名失败';
        }
        return $result;
    }

    public function doArgeeActiveUser($data,$num) {
        $userDao = M('course_active_user');
         $map['id']=$data['id'];
        if ($userDao->where($map)->setField('status', 1)) {
            $this->setInc('joinCount', 'id=' . $data['courseId'],$num);
            $this->setDec('limitCount', 'id=' . $data['courseId'],$num);
            return true;
        }
        return false;
    }

    public function doEditCourseActive($eventMap, $cover, $obj) {
        $daoActive = M('course_active');
        $eventMap['rTime'] = isset($eventMap['rTime']) ? $eventMap['rTime'] : time();
        if ($cover['status']) {
            foreach ($cover['info'] as $value) {
                if ($value['key'] == 'cover') {
                    //删除旧的
                    model('Attach')->deleteAttach($obj['coverId'], true, true);
                    $eventMap['coverId'] = $value['id'];
                } elseif ($value['key'] == 'logo') {
                    model('Attach')->deleteAttach($obj['logoId'], true, true);
                    $eventMap['logoId'] = $value['id'];
                }
            }
        }
        $eventMap['limitCount'] = 0 == intval($eventMap['limitCount']) ? 999999999 : $eventMap['limitCount'];
        $addId = $daoActive->where('id =' . $obj['id'])->save($eventMap);

        return $addId;
    }

    public function doDelActiveUser($data) {
        $userDao = M('course_active_user');
        $user = $userDao->where($data)->field('id, status,courseId')->find();
        if (!$user) {
            return false;
        }
        if ($userDao->where($data)->delete()) {
            //记录数相应减1
            $deleteMap['id'] = $user['courseId'];
            if ($user['status']) {
                $this->setInc('limitCount', $deleteMap);
                $this->setDec("joinCount", $deleteMap);
            }
            return true;
        }
        return false;
    }

}

?>