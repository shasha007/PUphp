<?php
    /**
     * EventTypeModel
     *
     * @uses BaseModel
     * @package
     * @version $id$
     * @copyright 2009-2011 SamPeng
     * @author SamPeng <sampeng87@gmail.com>
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class EventTypeModel extends Model{

        //该校所有分类，包括已删除的 格式 id=>name
        public function getType($sid=0){
            $stype = $this->eventTypeCache($sid);
            $newresult[1] = '文体艺术';
            $newresult[2] = '学术创新';
            $newresult[3] = '实习创业';
            $newresult[4] = '道德修养';
            $newresult[5] = '技能培训';
            $newresult[8] = '身心发展';
            $newresult[9] = '社会工作';
            $newresult[10] = '志愿服务';
            $newresult[11] = '其它';
            foreach ($stype as $v) {
                $newresult[$v['id']] = $v['name'];
            }
            return $newresult;
//
//            if($sid==480){
//                $newresult[1] = '文化艺术';
//                $newresult[2] = '人文社科';
//                $newresult[3] = '创新创业';
//                $newresult[10] = '公益服务';
//                $newresult[4] = '体育竞技';
//            }
//            return $newresult;
        }
        // 本校可用分类
        public function getSearchType($sid=0){
            $newresult = array();
            if(!D('EtypeInit','event')->isInited($sid)){
                $newresult[1] = '文体艺术';
                $newresult[2] = '学术创新';
                $newresult[3] = '实习创业';
                $newresult[4] = '道德修养';
                $newresult[5] = '技能培训';
                $newresult[8] = '身心发展';
                $newresult[9] = '社会工作';
                $newresult[10] = '志愿服务';
                $newresult[11] = '其它';
                return $newresult;
            }
            $stype = $this->eventTypeCache($sid);
            foreach ($stype as $v) {
                if(!$v['isdel']){
                    $newresult[$v['id']] = $v['name'];
                }
            }
            return $newresult;
//
//            if($sid==480){
//                $newresult[1] = '文化艺术';
//                $newresult[2] = '人文社科';
//                $newresult[3] = '创新创业';
//                $newresult[10] = '公益服务';
//                $newresult[4] = '体育竞技';
//            }else{
//                $newresult[1] = '文体艺术';
//                $newresult[2] = '学术创新';
//                $newresult[3] = '实习创业';
//                $newresult[4] = '道德修养';
//                $newresult[5] = '技能培训';
//                $newresult[8] = '身心发展';
//                $newresult[9] = '社会工作';
//                $newresult[10] = '志愿服务';
//                $newresult[11] = '其它';
//            }
//            return $newresult;
        }
        //发起活动时用
        public function getType2($sid=0){
            $newresult[1] = array('name'=>'文体艺术','banner'=>3,'pid'=>0);
            $newresult[2] = array('name'=>'学术创新','banner'=>2,'pid'=>0);
            $newresult[3] = array('name'=>'实习创业','banner'=>3,'pid'=>0);
            $newresult[4] = array('name'=>'道德修养','banner'=>3,'pid'=>0);
            $newresult[5] = array('name'=>'技能培训','banner'=>3,'pid'=>0);
            $newresult[8] = array('name'=>'身心发展','banner'=>2,'pid'=>0);
            $newresult[9] = array('name'=>'社会工作','banner'=>2,'pid'=>0);
            $newresult[10] = array('name'=>'志愿服务','banner'=>2,'pid'=>0);
            $newresult[11] = array('name'=>'其它','banner'=>2,'pid'=>0);
            if($sid<=0){
                return $newresult;
            }
            if(!D('EtypeInit','event')->isInited($sid)){
                return $newresult;
            }
            $stype = $this->eventTypeCache($sid);
            $res = array();
            foreach ($stype as $v) {
                if(!$v['isdel']){
                    $typeId = $v['id'];
                    $pid = $v['pid'];
                    $res[$typeId] = array('name'=>$v['name'],'banner'=>$newresult[$pid]['banner'],'pid'=>$pid);
                }
            }
            return $res;
        }
        //是否本校分类ID
        public function isSchoolTypeId($id,$sid) {
            $types = $this->apiEventType($sid);
            $ids = getSubByKey($types, 'id');
            return in_array($id, $ids);
        }
        //发起活动接口
        public function apiEventType($sid){
            $newresult[] = array('id'=>1,'title'=>'文体艺术');
            $newresult[] = array('id'=>2,'title'=>'学术创新');
            $newresult[] = array('id'=>3,'title'=>'实习创业');
            $newresult[] = array('id'=>4,'title'=>'道德修养');
            $newresult[] = array('id'=>5,'title'=>'技能培训');
            $newresult[] = array('id'=>8,'title'=>'身心发展');
            $newresult[] = array('id'=>9,'title'=>'社会工作');
            $newresult[] = array('id'=>10,'title'=>'志愿服务');
            $newresult[] = array('id'=>11,'title'=>'其它');
            if($sid<=0){
                return $newresult;
            }
            if(!D('EtypeInit','event')->isInited($sid)){
                return $newresult;
            }
            $stype = $this->eventTypeCache($sid);
            $res = array();
            foreach ($stype as $v) {
                if(!$v['isdel']){
                    $res[] = array('id'=>$v['id'],'title'=>$v['name']);
                }
            }
            return $res;
        }
        //
        public function editEventType($sid){
            $id = intval($_POST['id']);
            $pid = intval($_POST['pid']);
            $name = t($_POST['name']);
            if(empty($name)){
                $this->error = '名称不能为空';
                return 0;
            }
            if($pid==0){
                $this->error = '请选择一个归类';
                return 0;
            }
            $isInit = D('EtypeInit','event')->isInited($sid);
            $hasNameWhere = "sid=$sid and name='$name'";
            if($isInit){
                $hasNameWhere .= ' and isdel=0';
            }
            if($id){
                $hasNameWhere .= " and id!=$id";
            }
            $hasName = $this->where($hasNameWhere)->field('id')->find();
            if($hasName){
                $this->error = '该分类名称已存在，不可重复';
                return 0;
            }
            $parent = $this->where("id=$pid and pid=0")->field('id')->find();
            if(!$parent){
                $this->error = '选择归类错误';
                return 0;
            }
            $data['name'] = $name;
            $data['pid'] = $pid;
            $data['sid'] = $sid;
            if($id){
                $res = $this->where("id=$id")->save($data);
            }else{
                //等待【旧活动的分类】规整
                if(!$isInit){
                    $data['isdel'] = 1;
                }
                $res = $this->add($data);
            }
            if($res){
                return $res;
            }
            $this->error = '操作失败';
            return 0;
        }
        //原始分类
        public function eventTypeOrig(){
            $newresult[1] = array('id'=>'1','name'=>'文体艺术','banner'=>3);
            $newresult[2] = array('id'=>'2','name'=>'学术创新','banner'=>2);
            $newresult[3] = array('id'=>'3','name'=>'实习创业','banner'=>3);
            $newresult[4] = array('id'=>'4','name'=>'道德修养','banner'=>3);
            $newresult[5] = array('id'=>'5','name'=>'技能培训','banner'=>3);
            $newresult[8] = array('id'=>'8','name'=>'身心发展','banner'=>2);
            $newresult[9] = array('id'=>'9','name'=>'社会工作','banner'=>2);
            $newresult[10] = array('id'=>'10','name'=>'志愿服务','banner'=>2);
            $newresult[11] = array('id'=>'11','name'=>'其它','banner'=>2);
            return $newresult;
        }
        //各校分类
        public function eventTypeDb($sid,$withDel=false){
            $map['sid'] = $sid;
            if(!$withDel){
                $map['isdel'] = 0;
            }
            return $this->where($map)->findAll();
        }
        public function eventTypeCache($sid){
            return $this->eventTypeDb($sid,true);
        }
        //删除分类
        public function delEventType($id,$sid){
            if($id<=0 || $sid<=0){
                $this->error = '参数错误';
                return false;
            }
            $has = M('event')->where("typeId =$id")->field('id')->find();
            if($has){
                $this->setField('isdel', 1, "sid=$sid and id=$id");
            }else{
                $this->where("sid=$sid and id=$id")->delete();
            }
            return true;
        }
        //整合旧分类
        public function doMoveOldType($sid,$uid){
            //检查提交新分类是否完整
            $oldType = $this->eventTypeOrig();
            $moveArr = $_POST['newIds'];
            if(count($oldType)!=count($moveArr)){
                $this->error = '参数错误';
                return false;
            }
            //检查提交新分类是否合法
            $newType = $this->eventTypeDb($sid,true);
            $newTypeIds = getSubByKey($newType, 'id');
            foreach ($moveArr as $v) {
                if(!in_array($v, $newTypeIds)){
                    $this->error = '务必每类都要填选';
                    return false;
                }
            }
            //激活新分类
            $this->setField('isdel', 0, "sid=$sid");
            D('EtypeInit','event')->addEtypeInit($sid,$uid);
            //修改旧活动分类
            $sql = "insert into `ts_event_move` select id,typeId,is_school_event from ts_event where is_school_event=$sid";
            M('')->query($sql);
            $daoEvent = M('event');
            $i = 0;
            foreach ($oldType as $v) {
                $oldId = $v['id'];
                $i++;
                $daoEvent->setField('typeId',$moveArr[$i],"is_school_event=$sid and typeId=$oldId");
            }
            return true;
        }

                /**
         * getTypeName
         * 通过id获得名字
         * @param mixed $id
         * @access public
         * @return void
         */
        public function getTypeName($id,$sid=0){
            $type = $this->getType($sid);
            if(isset($type[$id])){
                return $type[$id];
            }
            $name = D('EventType')->getField('name', "id=$id");
            if(!$name){
                $name = '';
            }
            return $name;
        }


    }
