<?php

/**
 * EcApplyAction
 * 学分申请
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EcApply2Action extends SchoolbaseAction {

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        parent::_initialize();
        $this->_checkSchoolUser();
    }

    //学分申请列表
    public function ecApplyList() {
        $list = D('EcFolder')->allEcFolderYear($this->sid,$this->user['year']);
        $this->assign('list', $list);
        $this->display();
    }
    //学分申请
    public function ecApply() {
        $id = intval($_GET['id']);
        $obj = D('EcFolder')->where("is_folder=0 and sid=$this->sid and id=$id")->find();
        if(!$obj){
            $this->error('该申请表不存在或已删除');
        }
        $this->assign($obj);
        $inputs = M('EcInput')->where("fileId=$id")->order('inputOrder asc,id desc')->findAll();
        foreach($inputs as &$v){
            $v['opt'] = unserialize($v['opt']);
        }
        $this->assign('inputs',$inputs);
        //审核人
        $audits = D('EcAuditor')->yuanxiList($this->sid);
        $this->assign('audit',$audits);
        $this->display();
    }
    public function ajaxAuditor(){
        $sid1 = intval($_GET['sid1']);
        $audits = D('EcAuditor')->getBySid1($this->sid,$sid1);
        echo json_encode($audits);
    }
    public function doEcApply(){
        if (!canDo($this->mid)) {
            $this->error('操作太频繁!请勿重复提交');
        }
        $dao = D('EcIdentify');
        $res = $dao->doApply($this->sid, $this->user);
        if ($res) {
            doCando($this->mid);
            $this->success('申请成功，请等待审核');
        } else {
            $this->error($dao->getError());
        }
    }
    public function myEc(){
        $map['uid'] = $this->mid;
        $list = D('EcIdentify')->where($map)->field('id,title,fileId,credit,status,cTime,rTime')->order('id DESC')->findPage(10);
        foreach ($list['data'] as &$v) {
            $v['fileName'] = D('EcFolder')->getFileName($v['fileId']);
        }
        $this->assign($list);
        $this->display();
    }
    public function cjd(){
        $list = D('EcFolder')->allEcFolderYear($this->sid,$this->user['year']);
        $this->assign('folder', $list);
        $applyCnt = count($list);
        $this->assign('cntApply', $this->_maxNum($applyCnt));
        $this->display();
    }
    //最大每类多少个
    private function _maxNum($applyCnt){
        $cntApply = array(0,47,20,12,10,7,6,5,4);
        if(isset($cntApply[$applyCnt])){
            return $cntApply[$applyCnt];
        }
        return 0;
    }
    //所有用户PU活动
    private function _applyTree($id){
        $list = $this->_allEcApply($id);
        foreach($list['tree'] as &$v){
            $v['name'] = $v['cTime'].'|'.$v['credit'].'分|'.$v['title'];
            $v['pId'] = 0;
            unset($v['cTime']);
            unset($v['credit']);
        }
        return $list;
    }
    //id分类下所有用户申请. 返回maxNum，tree,sumCredit
    private function _allEcApply($id){
        $list = D('EcFolder')->allEcFolderYear($this->sid,$this->user['year']);
        $res['maxNum'] = $this->_maxNum(count($list));
        $res['sumCredit'] = 0;
        $res['tree'] = array();
        $fileId = array();
        foreach($list as $v){
            if($v['id']!=$id){
                continue;
            }
            if($v['is_folder']){
                foreach($v['files'] as $w){
                    $fileId[] = $w['id'];
                }
            }else{
                $fileId[] = $v['id'];
            }
        }
        if(!$fileId){
            return $res;
        }
        $map['status'] = 1;
        $map['uid'] = $this->mid;
        $map['fileId'] = array('in',$fileId);
        $ecApply = M('EcIdentify')->where($map)->field('id,cTime,stime,credit,title')->order('credit DESC')->findAll();
        if(!$ecApply){
            return $res;
        }
        foreach($ecApply as &$v){
            $v['cTime'] = calcSemester($v['cTime'],$v['stime']);
            $res['sumCredit'] += $v['credit'];
        }
        $res['tree'] =  $ecApply;
        return $res;
    }
    //筛选 sem：0 PU活动，>0 申请分类id
    public function selectEvent() {
        $sem = intval($_GET['sem']);
        if($sem==0){
            $maxNum = 25;
            $tree = $this->_eventTree();
        }else{
            $applyTree = $this->_applyTree($sem);
            $maxNum = $applyTree['maxNum'];
            $tree = $applyTree['tree'];
        }
        if ($tree) {
            array_unshift($tree, array('id' => 0, 'name' => '选择全部', 'pid' => -1, 'open' => true));
            $str = rtrim($_REQUEST['selected'], ',');
            if ($str) {
                $selected = explode(',', $str);
            }
            if ($selected) {
                foreach ($tree as &$vo) {
                    if (in_array($vo['id'], $selected)) {
                        $vo['checked'] = true;
                    }
                }
            }else{
                $i = 0;
                foreach ($tree as &$v) {
                    if ($i<=$maxNum) {
                        $v['checked'] = true;
                    }else{
                        break;
                    }
                    $i++;
                }
            }
        }
        $this->assign('maxNum', $maxNum);
        $this->assign('sem', $sem);
        $this->assign('tree', json_encode($tree));
        $this->display();
    }
    //所有用户PU活动
    private function _eventTree(){
        $userEvent = $this->_allUserEvent();
        $list = $userEvent['tree'];
        foreach($list as &$v){
            $v['name'] = $v['cTime'].'|'.$v['credit'].'分|'.$v['typeId'].'|'.$v['title'];
            $v['pId'] = 0;
            unset($v['cTime']);
            unset($v['credit']);
            unset($v['typeId']);
        }
        return $list;
    }
    // tree,sumCredit
    private function _allUserEvent(){
        $year = $this->user['year'];
        $cache = Mmc('EcApply2_allUserEvent_'.$this->mid.'_'.$year);
        if($cache!==false){
            return json_decode($cache,true);
        }
        $res['tree'] = array();
        $res['sumCredit'] = 0;
        $map['uid'] = $this->mid;
        $map['status'] = array('neq',0);
        $list = M('event_user')->where($map)->field('eventId as id,credit,addCredit')->order('eventId DESC')->findAll();
        if($list){
            $webconfig = $this->get('webconfig');
            $rat = $webconfig['ecRat'];
            $list = $this->_sortEvent($list);
            $types = D('EventType')->getType($this->sid);
            $semesters = array('','一','二','三','四','五','六','七','八','九','十');
            foreach($list as $k=>&$v){
                $event = M('event')->where('id='.$v['id'])->field('cTime,title,typeId ')->find();
                if(!$event){
                    unset($list[$k]);
                    continue;
                }
                $semester = $this->_timeToSemester($event['cTime'], $year);
                $v['cTime'] = date('Y-m-d',$event['cTime']);
                if(isset($semesters[$semester])){
                    $v['cTime'] = '第'.$semesters[$semester].'学期';
                }
                $v['typeId'] = $types[$event['typeId']];
                $v['credit'] = $v['credit']*$rat;
                $v['credit'] = sprintf('%.2f',$v['credit']);
                $v['title'] = $event['title'];
                $res['sumCredit'] += $v['credit'];
            }
            $res['tree'] = $list;
        }
        Mmc('EcApply2_allUserEvent_'.$this->mid.'_'.$year,  json_encode($res),0,1200);
        return $res;
    }
    //pu活动列表 sumCredit，tree[]
    private function _excelEvent(){
        $userEvent = $this->_allUserEvent();
        $res['sumCredit'] = $userEvent['sumCredit'];
        $list = $userEvent['tree'];
        $eventIds = t($_POST['eventIds0']);
        if(!$eventIds){
            $res['tree'] = array_slice($list,0,25);
            return $res;
        }
        $res['tree'] = array();
        $selectIds = explode(',', $eventIds);
        foreach($list as $v){
            if(in_array($v['id'], $selectIds)){
                $res['tree'][] = $v;
            }
        }
        return $res;
    }
    private function _excelData(){
        $res['event'] = $this->_excelEvent();
        $res['apply'] = $this->_excelApply();
        return $res;
    }
    // 申请列表 title，sumCredit，data
    private function _excelApply(){
//        return $this->_testApply();
        
        $res = array();
        foreach($_POST as $k=>$v){
            if(substr($k, 0, 8)!='eventIds'){
                continue;
            }
            $fileId = substr($k, 8);
            $file = M('EcFolder')->getField('title', "id=$fileId and isDel=0 and isRelease=1 and sid=$this->sid");
            if(!$file){
                continue;
            }
            $typeRow['title'] = $file;
            $typeRow['data'] = array();
            $allApply = $this->_allEcApply($fileId);
            $typeRow['sumCredit'] = $allApply['sumCredit'];
            $identIds = t($v);
            if(!$identIds){
                $typeRow['data'] = array_slice($allApply['tree'],0,$allApply['maxNum']);
            }else{
                $selectIds = explode(',', $identIds);
                $selCnt = 0;
                foreach ($allApply['tree'] as $w) {
                    if($selCnt<$allApply['maxNum'] && in_array($w['id'], $selectIds)){
                        $typeRow['data'][] = $w;
                        $selCnt++;
                    }
                }
            }
            $res[] = $typeRow;
        }
        return $res;
    }
    //测试用
    private function _testApply(){
        $typeCnt = 1;
        for($i=1;$i<=$typeCnt;$i++){
            $row = array();
            $row['title'] = '申请类别'.$i;
            $row['sumCredit'] = $i+100;
            $row['data'][] = array("id"=>"1","cTime"=>"2015下","stime"=>"20151","credit"=>"1.00","title"=>"南京工程");
            $row['data'][] = array("id"=>"1","cTime"=>"2015下","stime"=>"20151","credit"=>"1.00","title"=>"南京工程");
            $row['data'][] = array("id"=>"1","cTime"=>"2015下","stime"=>"20151","credit"=>"1.00","title"=>"南京工程");
            $row['data'][] = array("id"=>"1","cTime"=>"2015下","stime"=>"20151","credit"=>"1.00","title"=>"南京工程");
            $row['data'][] = array("id"=>"1","cTime"=>"2015下","stime"=>"20151","credit"=>"1.00","title"=>"南京工程");
            $row['data'][] = array("id"=>"1","cTime"=>"2015下","stime"=>"20151","credit"=>"1.00","title"=>"南京工程");
            $res[] = $row;
        }
        return $res;
    }

    public function printExcel(){
        $webconfig = $this->get('webconfig');
        $b2 = '院系： '.tsGetSchoolName($this->user['sid1']).'           专业： '.$this->user['major']
                .'           年级： 20'.$this->user['year'].'           学号： '.getUserEmailNum($this->user['email'])
                .'           姓名： '.$this->user['realname'].'         成绩单位：'.$webconfig['ecName'];
        $file_path = service('Excel')->ecApply($this->mid,$b2,$this->_excelData(),'成绩');
        if(!$file_path){
            $this->error('错误');
        }
        if (file_exists($file_path)) {
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            $file_name = iconv("utf-8", 'gb2312', '第二课堂成绩单'.date('Y_m_d').'.xlsx');
            Http::download($file_path, $file_name);
        }
        $this->error('文件不存在');
    }
    private function _timeToSemester($time,$year){
        $sYear = '20'.sprintf("%02d", $year);
        $eYear = date('Y', $time);
        $eDay = date('m-d', $time);
        $semester = $eYear-$sYear;
        if($semester<=0){
            return 1;
        }
        if($eDay>'08-15'){
            return $semester+2;
        }elseif($eDay>'02-15'){
            return $semester+1;
        }else{
            return $semester;
        }
    }
    private function _sortEvent($list){
        $sumCredits = array();
        foreach($list as $k=>&$v){
            $sumCredits[$k] = $v['credit']+$v['addCredit'];
            $v['credit'] = $v['credit']+$v['addCredit'];
            unset($v['addCredit']);
        }
        arsort($sumCredits);
        foreach($sumCredits as $k=>&$v){
            $v = array();
            $v['id'] = $list[$k]['id'];
            $v['credit'] = $list[$k]['credit'];
        }
        return $sumCredits;
    }

}
