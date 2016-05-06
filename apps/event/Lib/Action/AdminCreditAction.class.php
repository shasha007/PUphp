<?php
class AdminCreditAction extends TeacherAction {


    public function _initialize() {
        parent::_initialize();
        if(!$this->rights['can_credit']){
            $this->error('您没有权限');
        }
        $map = array();
        $map['sid'] = $this->sid;
        $map['status'] = 0;
        if(!$this->rights['can_admin']){
            $map['audit'] = $this->mid;
        }
        $finishCount = D('EcApply')->where($map)->count();
        $this->assign('finishCount', $finishCount);
    }

    public function index() {
        if(!$this->rights['can_admin']){
            $map['audit'] = $this->mid;
        }
        $map['status'] = 0;
        $this->_getACList($map);
        $this->display();
    }

    public function audited() {
        if(!$this->rights['can_admin']){
            $map['audit'] = $this->mid;
        }
        $map['status'] = 1;
        $this->_getACList($map);
        $this->display();
    }

    private function _getACList($map=array(),$orig_order='id DESC'){
        $map['sid'] = $this->sid;
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['es_searchAC'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_searchAC']);
        } else {
            unset($_SESSION['es_searchAC']);
        }
        $uid = array();
        if(empty($_POST) && isset($_REQUEST['uid']) && $_REQUEST['uid']){
            $uid[] = $_REQUEST['uid'];
        }

        if(!empty($_POST['num']) && $_POST['num']){
            $num = t($_POST['num']);
            if($num){
                $mapUser['email'] = $num.$this->school['email'];
                $mapUser['sid'] = $this->sid;
                $userInfo = M('user')->where($mapUser)->field('uid')->find();
                if($userInfo){
                    $uid[] = $userInfo['uid'];
                }else{
                    $uid[] = 0;
                }
            }
        }
        else
        {
            $_POST['num'] = '';
        }
        if(!empty($_POST['realname']) && $_POST['realname']){
            $num = t($_POST['realname']);
            if($num){
                $mapUser = array();
                $mapUser['realname'] = $num;
                $mapUser['sid'] = $this->sid;
                $userInfo = M('user')->where($mapUser)->field('uid')->findAll();
                if($userInfo){
                    foreach($userInfo as $v){
                        $uid[] = $v['uid'];
                    }
                }else{
                    $uid[] = 0;
                }
            }
        }
        else
        {
            $_POST['realname'] = '';
        }

        if(!empty($uid)){
            $uid = array_unique($uid);
            $map['uid'] = array('in',$uid);
        }
        $list = D('EcApply')->getApplyList($map, $orig_order, 10);
        $this->assign($_POST);
        $this->assign($list);
    }

    /**
     * 删除已经审核的
     * 需要同时退回学分
     */
    public function deleteAudited()
    {
        $id = intval($_REQUEST['id']);
        if ($id < 1){
            $this->error('ID错误');
        }

        if (!$this->rights['can_admin']) {
            $this->error('只有超级管理员能够进行此操作');
        }

        $map['id'] = $id;
        $info = M('ec_apply')->where($map)->find();
        if(empty($info))
        {
            $this->error('该学分记录不存在');
        }
        $uid = $info['uid'];
        $credit = $info['credit'];
        $sid = $info['sid'];
        M()->startTrans();
        //删除学分申请记录
        $rs1 = M('ec_apply')->where($map)->delete();
        //扣除对应的学分
        $rs2 = D('User','home')->decCredit($uid,$sid,$credit);
        //@todo:添加到通知
        if ($rs1 && $rs2){
            M()->commit();
            //如果取消成功
            $this->success('删除成功');
        }
        M()->rollback();
        $this->error('删除失败');
    }

    public function ecType() {
        if(!$this->rights['can_admin'])
            $this->error('您没有权限');
        $list = D('EcType')->getEcType($this->sid);
        $this->assign('totalRows', count($list));
        $this->assign('data', $list);
        $this->display();
    }

    public function editEcType() {
        if(!$this->rights['can_admin'])
            $this->error('您没有权限');
        $this->assign('type', 'add');
        $id = (int) $_REQUEST['id'];
        if ($id) {
            $this->assign('type', 'edit');
            $map['id'] = $id;
            $obj = M('ec_type')->where($map)->find();
            if (!$obj) {
                $this->error('类别不存在或已删除');
            }
            $this->assign($obj);
        }
        $this->display();
    }

    public function doEditEcType() {
        if(!$this->rights['can_admin'])
            $this->error('您没有权限');
        if($_POST['title']==''){
            $this->error('类别名称不能为空');
        }
        $data = $this->_getEcTypeData();
        $id = intval($_POST['id']);
        if ($id) {
            $data['id'] = $id;
        }
        $dao = D('EcType');
        $res = $dao->editEcType($data);
        if ($res) {
            if($id){
                $this->assign('jumpUrl', U('event/AdminCredit/ecType'));
            }
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }

    private function _getEcTypeData() {
        $data['sid'] = $this->sid;
        $data['title'] = t($_POST['title']);
        $data['description'] = t($_POST['description'],'nl');
        $data['need_text'] = $_POST['need_text']?1:0;
        $data['img'] = $_POST['img']?1:0;
        $data['attach'] = $_POST['attach']?1:0;
        return $data;
    }

    public function delEcType(){
        if(!$this->rights['can_admin'])
            $this->error('您没有权限');
        $id = intval($_POST['id']);
        $dao = D('EcType');
        $res = $dao->delEcType($id,$this->sid);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }

    public function auditEcApply(){
        $this->_details();
        $this->display();
    }
    public function showEcApply(){
        $this->_details();
        $this->display();
    }
    private function _details(){
        $id = intval($_REQUEST['id']);
        $ecApply = D('EcApply')->getApply($id);
        $this->assign($ecApply);
        $canEdit = true;
        if($ecApply['type']==10||$ecApply['type']==11||$ecApply['type']==12){
            $zusatz = M('ec_applygd')->where('apply_id='.$ecApply['id'])->find();
            $opts = explode(',', $zusatz['opt']);
            $func = $ecApply['type'].'_'.$zusatz['gd'];
            $titleFunc = 'gdSelect'.$func;
            $selectFunc = 'gdRadio'.$func;
            $gdopt = array();
            foreach ($opts as $k => $v) {
                $row = array();
                $title = $titleFunc($k+1);
                $row['title'] = $title['title'];
                $row['select'] = $selectFunc($k+1,$v);
                $gdopt[] = $row;
            }
            $this->assign('zusatz',$gdopt);
            $this->assign('zs_name',$zusatz['zs_name']);
            $canEdit = false;
        }
        $this->assign('canEdit',$canEdit);
    }

    //审核通过，发放学分
    public function doAuditEcApply(){
        $id = intval($_REQUEST['id']);
        $note = t($_REQUEST['note'])*100/100;
        $map['id'] = $id;
        $map['sid'] = $this->sid;
        $map['status'] = 0;
        if(!$this->rights['can_admin']){
            $map['audit'] = $this->mid;
        }
        $webconfig = $this->get('webconfig');
        $creditName = $webconfig['cradit_name'];
        $res = D('EcApply')->apply($map, $this->mid,$note,$creditName);
        if(!$res){
            $this->error(D('EcApply')->getError());
        }
        $this->success('操作成功');
    }

    public function rejectApply() {
        $id=intval($_GET['id']);
        $this->assign('id',$id);
        $this->display();
    }


    //jun  完结驳回活动
   public function doRejectApply() {
       $reason = t($_POST['reject']);
        $id = intval($_POST ['id']);
        if (empty($reason)) {
            $this->error('请填写驳回原因');
        }
        $webconfig = $this->get('webconfig');
        $creditName = $webconfig['cradit_name'];
        $res = D('EcApply')->doRejectApply($id, $reason,$creditName);
        if ($res) {
            $this->success('驳回成功');
        } else {
            $this->error(D('EcApply')->getError());
        }
    }

    //审核人列表
    public function ecUser(){
        $map['sid'] = $this->sid;
        $type = intval($_REQUEST['type']);
        if($type>0){
            $map['type'] = $type;
        }
        $this->assign('type', $type);
        $list = M('ec_utype')->where($map)->findAll();
        $this->assign('list', $list);
        $this->assign('totalRows', count($list));
        $this->display();
    }

    //添加审核人
    public function doAddAudit(){
        $type = intval($_POST['type']);
        if($type<=0){
            $this->error('申请类别错误');
        }
        $uid = intval($_POST['uid']);
        if($uid<=0){
            $this->error('不存在此人');
        }
        $typeName = getEcTypeName($type,$this->sid);
        if($typeName==''){
            $this->error('申请类别错误');
        }
        $user = M('user')->where('uid='.$uid.' and sid='.$this->sid)->field('realname')->find();
        if(!$user){
            $this->error('不存在此人');
        }
        $has = M('ec_utype')->where('uid='.$uid.' and type='.$type)->field('type')->find();
        if($has){
            $this->error('该用户已拥有该类别审核权限，不可重复分配');
        }
        D('EcUtype')->addUType($uid,$type,$this->sid,$user['realname']);
        $this->success('操作成功');
    }

    //删除审核人
    public function delAudit(){
        $type = intval($_POST['type']);
        if($type<=0){
            $this->error('申请类别错误');
        }
        $uid = intval($_POST['uid']);
        if($uid<=0){
            $this->error('不存在此人');
        }
        $typeName = getEcTypeName($type,$this->sid);
        if($typeName==''){
            $this->error('申请类别错误');
        }
        $user = M('user')->where('uid='.$uid.' and sid='.$this->sid)->field('realname')->find();
        if(!$user){
            $this->error('不存在此人');
        }
        $has = M('ec_utype')->where('uid='.$uid.' and type='.$type)->field('type')->find();
        if(!$has){
            $this->error('该用户无该类别审核权限，不删除');
        }
        D('EcUtype')->delUType($uid,$type,$this->sid);
        $this->success('操作成功');
    }

    public function cjd(){
        $user = false;
        if($_POST['num']){
            $num = t($_POST['num']);
            if($num){
                $email = $num.$this->school['email'];
                $user = M('user')->where("email='$email'")->field('uid,realname,year')->find();
            }
        }elseif(intval($_GET['uid'])>0){
            $user = M('user')->where("sid=$this->sid and uid=".intval($_GET['uid']))->field('uid,realname,year')->find();
        }
        if(!$user){
            $this->display();
            die;
        }
        $this->assign('findUser', $user['uid']);
        $this->assign('realname', $user['realname']);
        $this->assign('semester1', $this->getSemeser(1,$user['uid'], $user['year']));
        $this->assign('semester2', $this->getSemeser(2,$user['uid'], $user['year']));
        $this->assign('semester3', $this->getSemeser(3,$user['uid'], $user['year']));
        $this->assign('semester4', $this->getSemeser(4,$user['uid'], $user['year']));
        $this->assign('semester5', $this->getSemeser(5,$user['uid'], $user['year']));
        $this->assign('semester6', $this->getSemeser(6,$user['uid'], $user['year']));
        $this->assign('semester7', $this->getGd($user['uid'],10));
        $this->assign('semester8', $this->getGd($user['uid'],11));
        $this->assign('semester9', $this->getGd($user['uid'],12));
        $this->display();
    }

    private function _getSemesterMap($year,$semester){
        $yearPlus = floor($semester/2);
        $sYear = sprintf("%02d", $year+$yearPlus);
        if($semester%2==1){
            $sday = '20'.$sYear.'-08-16 00:00:00';
            $eYear = sprintf("%02d", $sYear+1);
            $eday = '20'.$eYear.'-02-14 23:59:59';
        }else{
            $sday = '20'.$sYear.'-02-15 00:00:00';
            $eday = '20'.$sYear.'-08-15 23:59:59';
        }
        return array('between ',array(strtotime($sday),strtotime($eday)));;
    }


    //2.15-8.15和8.16-2.14
    public function getSemeser($semester,$uid,$year=0){
        if(!$uid||$semester<0||$semester>6){
            return array();
        }
        $cache = Mmc('AdminCredit_semester_'.$semester.'_'.$uid);
        if($cache!==false){
            return json_decode($cache,true);
        }
        if($year==0){
            $user = D('User', 'home')->getUserByIdentifier($uid);
            if(!$user || !$user['year']){
                return array();
            }
            $year = $user['year'];
        }
        $map['uid'] = $uid;
        $map['status'] = array('neq',0);
        $map['cTime'] = $this->_getSemesterMap($year,$semester);
        $list = M('event_user')->where($map)->field('eventId as id,credit,addCredit')->order('id DESC')->findAll();
        if(!$list){
            $list = array();
        }else{
            $types = D('EventType')->getType($this->sid);
            foreach($list as &$v){
                $event = M('event')->where('id='.$v['id'])->field('title,typeId ')->find();
                $v['name'] = '活动已删除';
                $v['type'] = '已删除';
                if($event){
                    $v['name'] = htmlspecialchars_decode($event['title'],ENT_QUOTES);
                    $v['type'] = $types[$event['typeId']];
                }
                $v['credit'] = sprintf('%.2f',$v['credit']+$v['addCredit']);
                $v['pId'] = 0;
                unset($v['addCredit']);
            }
        }
        Mmc('AdminCredit_semester_'.$semester.'_'.$uid,  json_encode($list),0,3600);
        return $list;
    }

    public function getGd($uid,$type){
        if(!$uid||$type<10||$type>12){
            return array();
        }
        $cache = Mmc('AdminCredit_getGd_'.$type.'_'.$uid);
        if($cache!==false){
            return json_decode($cache,true);
        }
        $map['uid'] = $uid;
        $map['status'] = 1;
        $map['type'] = $type;
        $list = M('ec_apply')->where($map)->field('id,cTime as type,stime,title as name,description,credit')->order('id DESC')->findAll();
        if(!$list){
            $list = array();
        }else{
            if($type==11){
                $daoGd = M('ec_applygd');
            }
            foreach($list as &$v){
                $v['pId'] = 0;
                $v['type'] = calcSemester($v['type'],$v['stime']);
                $v['credit'] = sprintf('%.2f',$v['credit']);
                if($type==12 && $v['description']){
                    $v['name'] = $v['description'];
                }
                unset($v['description']);
                if($type==11){
                    $zusatz = $daoGd->where('apply_id='.$v['id'])->find();
                    $opts = explode(',', $zusatz['opt']);
                    $selectFunc = 'gdRadio11_'.$zusatz['gd'];
                    $select = $selectFunc(1,$opts[0]);
                    $v['name'] = htmlspecialchars_decode($v['name'],ENT_QUOTES);
                    $v['name'] .= $select['title'];
                }
            }
        }
        Mmc('AdminCredit_getGd_'.$type.'_'.$uid,  json_encode($list),0,3600);
        return $list;
    }

    public function selectPrint() {
        $uid = intval($_GET['uid']);
        $sem = intval($_GET['sem']);
        if($sem>6){
            $tree = $this->getGd($uid,$sem+3);
        }else{
            $tree = $this->getSemeser($sem,$uid);
        }
        if ($tree) {
            array_unshift($tree, array('id' => 0, 'name' => '选择全部', 'pid' => -1, 'open' => true));
        }
        $str = substr($_REQUEST['selected'], 0, strlen($_REQUEST['selected']) - 1);
        if ($tree && $str) {
            $selected = explode(',', $str);
        }
        if ($selected) {
            foreach ($tree as $k => $vo) {
                if (in_array($vo['id'], $selected)) {
                    $tree[$k]['checked'] = true;
                }
            }
        }
        $this->assign('sem', $sem);
        $this->assign('tree', json_encode($tree));
        $this->display();
    }
    public function outexcel(){
        $uid = intval($_GET['uid']);
        if(!$uid){
            $this->error('用户不存在');
        }
        $user = D('User', 'home')->getUserByIdentifier($uid);
        if(!$user || $user['sid']!=$this->sid){
            $this->error('用户不存在');
        }
        $max = 10;
        for($i=1;$i<=9;$i++){
            if($i<=6){
                $row = array(array('类别','活动名称','学时'));
            }else{
                $row = array(array('学年','内容','学时'));
            }
            $showIds = t($_POST['showIds'.$i]);
            $input = rtrim($showIds, ',');
            $sem2Arr = explode(',', $input);
            $count = count($sem2Arr);
            $restCount = $count;
            if($count>0){
                if($i<=6){
                    $tree = $this->getSemeser($i,$uid);
                }else{
                    $tree = $this->getGd($uid,$i+3);
                }
                foreach ($tree as $v) {
                    $id = $v['id'];
                    if(in_array($id, $sem2Arr)){
                        $name = substr_utf(htmlspecialchars_decode($v['name'],ENT_QUOTES),18);
                        $row[] = array($v['type'],$name,$v['credit']);
                        $restCount -= 1;
                        if($restCount<=0 || count($row)>$max){
                            break;
                        }
                    }
                }
            }
            $list[] = $row;
        }
        $num = getUserEmailNum($user['email']);
        $userInfo = M('user')->field('sid1,major,year,email,realname')->where('uid = '.$uid)->find();
        $userInfo['sid1'] = tsGetSchoolName($userInfo['sid1']);
        $userInfo['year'] = '20'.$userInfo['year'];
        $userInfo['email'] = str_replace('@njut.com','',$userInfo['email']);
        service('Excel')->njtech($list, $userInfo, $num.'_第二课堂成绩单');
    }
    public function wcl(){
        $this->assign('editSid', $this->sid);
        $types = D('EcType')->getEcType($this->sid);
        $this->assign('types', $types);
        if($_POST['sid1']){
            $sid1 = intval($_POST['sid1']);
            $year = t($_POST['year']);
            $year = str_replace('级', '', $year);
            $type = intval($_POST['type']);
        }
        $title = array();
        $list = array();
        if($sid1 && $year && $type){
            $title[] = $_POST['sname'];
            $title[] = $year.'级 （人数）';
            $typeName = getEcTypeName($type, $this->sid);
            $title[] = $typeName.' （完成人数）';
            $title[] = '完成率';
            $list[] = '';
            $all = M('user')->getField('count(1) as count', 'sid1='.$sid1.' and year='.$year);
            $list[] = $all.'人';
            $has = M('ec_apply')->getField('count(distinct(uid)) as count', 'sid1='.$sid1.' and year='.$year.' and type='.$type);
            $list[] = $has.'人';
            $rat = money2xs($has/$all*10000);
            $list[] = $rat.'%';
        }
        $this->assign('title', $title);
        $this->assign('list', $list);
        $this->display();
    }
}

?>