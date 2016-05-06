<?php

/**
 * 统计
 *
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
import('home.Action.PubackAction');

class TjAction extends PubackAction {

    function _initialize()
    {
        $this->assign('isAdmin', '1');
        $uid = $this->mid;
        $allowUserId = array(2915652,2933966);
        if(in_array($uid,$allowUserId))
        {
            $this->assign('tzFlag',1);
        }
    }

    // 各类统计选项列表
    public function index() {
        $this->display();
    }


    public function group(){
        $citys = model('Citys')->getAllCitys();
        $this->assign('citys',$citys);
        $this->display();
    }

    //用户及活跃数
    public function userActive() {
        $school = $this->allSchool();
        $this->assign('school', $school);
        $this->display();
    }

    //所有学校
    public function allSchool() {
        return model('Schools')->makeLevel0Tree();
    }

    //1.1所有高校用户总数、活跃用户总数
    public function allUser() {
        $map = array();
        $title = '用户总数_';
        if (!$_POST['withTeacher']) {
            $map['event_level'] = 20;
            $title .= '不';
        }
        $title .= '含老师';
        $year = t($_POST['year']);
        if ($year) {
            $map['year'] = $year;
            $title .= '_' . $year;
        }
        //总用户
        $userAll = M('user')->where($map)->count();
        if ($userAll == 0) {
            $this->error('用户数为零');
        }
        //初始化用户
        $map['is_init'] = 1;
        $user_init = M('user')->where($map)->count();
        $list = array(array($userAll, $user_init));
        $arr = array('用户总数', '初始化用户总数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);
    }

    //1.2根据学校、院系、年级查询 用户总数（学生 、所有）、初始化用户总数（学生、所有）
    public function userCountDetail() {
        if ($_POST['school']) {
            $map['sid'] = intval($_POST['school']);
        } else {
            $this->error('请选择学校');
        }
        if ($_POST['yuanxi']) {
            $map['sid1'] = intval($_POST['yuanxi']);
        }
        if ($_POST['year']) {
            $map['year'] = t($_POST['year']);
        }
        $res = M('user')->where($map)->field('sid1,year,count(*) as num')->group('sid1,year')->select();
        $school = tsGetSchoolName($map['sid']);
        $list = array();
        foreach ($res as $v) {
            $row = array();
            $row[] = $school; //学校
            $row[] = tsGetSchoolName($v['sid1']); //院系
            $row[] = $v['year']; //年级
            $map2 = array();
            $map2['sid'] = $map['sid'];
            $map2['sid1'] = $v['sid1'];
            $map2['year'] = $v['year'];
            $map2['event_level'] = 20;
            //本条件求所有用户学生数
            $st = M('user')->where($map2)->count(); //学生用户数
            //初始化本条件求所有用户学生数 总人数
            $map2['is_init'] = 1;
            $st_init = M('user')->where($map2)->force('sid_year')->count();
            unset($map2['event_level']);
            $all_init = M('user')->where($map2)->force('sid_year')->count();
            $row[] = $st;
            $row[] = $v['num'];
            $row[] = $st_init;
            $row[] = $all_init;
            $list[] = $row;
        }
        $arr = array('学校', '院系', '年级', '学生用户数', '所有用户数', '学生用户数(初始化)', '所有用户数(初始化)');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $school.'_用户数');
    }

    //Ajax根据学校查询相关院系
    public function school_ajax() {
        $sid = $_POST['sid']; //学校id
        //获得相应院系
        $res = M('school')->where('pid=' . $sid)->field('id,title')->select();
        if ($res)
            echo json_encode($res);
    }

    //活动数据
    public function eventData() {
        $stime = mktime(0,0,0,7,1,2015);
//        $etime = time();
        $n = D('event')->where('isDel=0 and sTime>='.$stime)->count();
        $num = ceil($n/10000);
        $this->assign('num',$num);
        $this->display();
    }

    private function _calcMon($input){
        if(!$input){
            $this->error('请输入月份');
        }
        $res['stime'] = strtotime($input.'-01');
        if(!$res['stime']){
            $this->error('请输入月份');
        }
        $res['mon'] = date('Y-m',$res['stime']);
        $res['etime'] = strtotime('+1 month', $res['stime'])-1;
        return $res;
    }

    //2.1每个月各个高校的活动总数
    public function every_month_shcool_count() {
        set_time_limit(0);
        $fileName = '各高校的活动总数';
        $calcMon = $this->_calcMon(t($_POST['mon']));
        $mon = $calcMon['mon'];
        $stime = $calcMon['stime'];
        $etime = $calcMon['etime'];
        $file = SITE_PATH.'/data/tj/2.1_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }
        $table = 'ts_temp_'.rand(1,9999).'_'.time();
        $sql = "CREATE temporary TABLE `$table` (
`id` int unsigned NOT NULL,
  `is_school_event` int unsigned NOT NULL,
  `school_audit`  tinyint(1) unsigned NOT NULL ,
  `joinCount` int unsigned NOT NULL,
  `uid` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `uid` (`uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        M('')->query($sql);
        $sql = "insert into `$table` select id,is_school_event,school_audit,joinCount,uid from ts_event where isDel=0 and school_audit>1 and cTime>=$stime and cTime<=$etime";
        M('')->query($sql);
        M('')->query("ALTER TABLE `$table` ADD `year` varchar(10) DEFAULT ''");
        $users = M('')->query("select distinct(uid) from $table");
        if(!$users){
            $this->error('没有搜索到结果');
        }
        foreach ($users as $user) {
            $uid = $user['uid'];
            $year = M('user')->getField('year', 'uid='.$uid);
            if($year){
                M('')->query("update $table set year='$year' where uid=$uid");
            }
        }
        $res = M('')->query("select *,count(1) as num,sum(joinCount) as gjoin from $table group by is_school_event,year");
        $list = array();
        foreach($res as $v){
            $sid = $v['is_school_event'];
            $year = $v['year'];
            $row = array();
            $row[] = $mon;
            if($sid==0){
                $row[] = 'PU平台活动'; //学校
            }else{
                $row[] = tsGetSchoolName($sid); //学校
            }
            $row[] = $year; //年级
            $row[] = $v['num']; //发起活动总数
            $gjoin = M('')->query("select count(1) as num from $table where school_audit=5 and is_school_event=$sid and year='$year'");
            $row[] = $gjoin[0]['num']; //完结活动总数
            $row[] = $v['gjoin']; //报名人数
            $mapEventUser['a.is_school_event'] = $sid;
            $mapEventUser['a.year'] = $year;
            $mapEventUser['b.status'] = 2;
            $attCnt = M('')->table("$table AS a ")
                ->join("ts_event_user AS b ON a.id=b.eventId")
                ->where($mapEventUser)->field('count(1) as count')->find();
            $row[] = $attCnt['count'];
            $list[] = $row;
        }
        $arr = array('月份', '学校', '年级', '发起活动总数', '完结活动总数', '报名人数', '签到人数');
        array_unshift($list, $arr);
        //当前月份不保存
        if($mon==date('Y-m')){
            service('Excel')->export2($list, $mon.$fileName);
            die;
        }
        service('Excel')->exportFile($list, $mon.$fileName,$file);
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }else{
            $this->error('生成文件错误');
        }
    }


    //2.2三大部落发起的活动总数、三大部落完结的活动总数、报名人数、签到人数；）
    public function eventDetail_every() {
        set_time_limit(0);
        $fileName = '三大部落活动详情';
        $calcMon = $this->_calcMon(t($_POST['mon']));
        $mon = $calcMon['mon'];
        $stime = $calcMon['stime'];
        $etime = $calcMon['etime'];
        $file = SITE_PATH.'/data/tj/2.2_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }
        $table = 'ts_temp_'.rand(1,9999).'_'.time();
        $sql = "CREATE temporary TABLE `$table` (
`id` int unsigned NOT NULL,
  `is_school_event` int unsigned NOT NULL,
  `school_audit`  tinyint(1) unsigned NOT NULL ,
  `joinCount` int unsigned NOT NULL,
  `gid` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `gid` (`gid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
        M('')->query($sql);
        $sql = "insert into `$table` select id,is_school_event,school_audit,joinCount,gid from ts_event where gid>0 and isDel=0 and school_audit>1 and cTime>=$stime and cTime<=$etime";
        M('')->query($sql);
        M('')->query("ALTER TABLE `$table` ADD `category` tinyint(1) unsigned NOT NULL DEFAULT 0");
        $users = M('')->query("select distinct(gid) from $table");
        if(!$users){
            $this->error('搜索结果为空');
        }
        foreach ($users as $user) {
            $gid = $user['gid'];
            $category = M('group')->getField('category', 'id='.$gid);
            if($category){
                M('')->query("update $table set category='$category' where gid=$gid");
            }
        }
        $res = M('')->query("select distinct(is_school_event) from $table");
        $list = array();
        foreach($res as $v){
            $sid = $v['is_school_event'];
            $row = array();
            $row[] = $mon;
            if($sid==0){
                $row[] = 'PU平台活动'; //学校
            }else{
                $row[] = tsGetSchoolName($sid); //学校
            }
            for($category=1;$category<=3;$category++){
                $count1 = M('')->query("select count(1) as num from $table where is_school_event=$sid and category=$category");
                $all = $count1[0]['num'];
                $row[] = $all; //发起活动总数
                if($all==0){
                    $row[] = 0;
                    $row[] = 0;
                    $row[] = 0;
                }else{
                    $count1 = M('')->query("select count(1) as num from $table where school_audit=5 and is_school_event=$sid and category=$category");
                    $row[] = $count1[0]['num']; //完结活动总数
                    $count1 = M('')->query("select sum(joinCount) as num from $table where is_school_event=$sid and category=$category");
                    $row[] = $count1[0]['num']; //报名人数
                    $mapEventUser['a.is_school_event'] = $sid;
                    $mapEventUser['a.category'] = $category;
                    $mapEventUser['b.status'] = 2;
                    $attCnt = M('')->table("$table AS a ")
                        ->join("ts_event_user AS b ON a.id=b.eventId")
                        ->where($mapEventUser)->field('count(1) as count')->find();
                    $row[] = $attCnt['count']; //签到人数
                }
            }
            $list[] = $row;
        }
        $arr = array('', '','发起活动总数','完结活动总数','报名人数','签到人数','发起活动总数','完结活动总数','报名人数','签到人数','发起活动总数','完结活动总数','报名人数','签到人数');
        array_unshift($list, $arr);
        $arr = array('月份', '学校','学生部门','','','','团支部','','','','学生社团','','','');
        array_unshift($list, $arr);
        //当前月份不保存
        if($mon==date('Y-m')){
            service('Excel')->export2($list, $mon.$fileName);
            die;
        }
        service('Excel')->exportFile($list, $mon.$fileName,$file);
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }else{
            $this->error('生成文件错误');
        }
    }

    //2.3高校的活动明细表
    public function eventDetail() {
        set_time_limit(0);
        $fileName = '高校的活动明细';
        $calcMon = $this->_calcMon(t($_POST['mon']));
        $mon = $calcMon['mon'];
        $stime = $calcMon['stime'];
        $etime = $calcMon['etime'];
        $file = SITE_PATH.'/data/tj/2.3_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }
        $daoEvent = M('event');
        $list = $daoEvent->where("isDel=0 and cTime>=$stime and cTime<=$etime")->order('is_school_event ASC,id ASC')
                ->field('is_school_event,title,gid,sid,cTime,school_audit,joinCount,id,typeId')->findAll();
        foreach ($list as &$v) {

            $v['is_school_event'] = tsGetSchoolName($v['is_school_event']); //学校
            if($v['gid']){
                $v['gid'] = getGroupName($v['gid']);
            }else{
                $v['gid'] = '';
            }
            $v['sid'] = getEventOrga($v['sid']);
            $v['cTime'] = date('Y-m-d',$v['cTime']);
            $v['school_audit'] = $this->_eventStatus($v['school_audit']);
            $eventId = $v['id'];
            $v['id'] = M('event_user')->where("status=2 and eventId=$eventId")->count(); //签到人数
            $res = M('event_type')->where('id='.$v['typeId'])->find();
            if($res['pid']==0){
                $v['typeId'] = $res['name'];
            }else{
                $result = M('event_type')->where('id='.$res['pid'])->find();
                $v['typeId'] = $result['name'];
            }

        }
        $arr = array('学校', '活动标题', '发起组织', '归属组织', '发起时间','活动状态', '报名人数', '签到人数', '活动类型');
        array_unshift($list, $arr);
        //当前月份不保存 .
        if($mon==date('Y-m')){
            service('Excel')->export2($list, $mon.$fileName);
            die;
        }
        service('Excel')->exportFile($list, $mon.$fileName,$file);
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }else{
            $this->error('生成文件错误');
        }
    }

    //所有活动信息
    public function eventAll(){
        set_time_limit(0);
        $page = intval($_POST['page']);
        $p = $page-1;
        $m = $p*10000;
        $end = 10000;
        $stime = mktime(0,0,0,7,1,2015);
        $list = D('event')->where('isDel=0 and sTime>='.$stime)->limit("$m,$end")->field('uid,title,gid,ctime,school_audit,joinCount,id,typeId')->findAll();
        if(!$list){
            $this->error('暂无数据!');
        }
        $arr = array('待初审','待终审','进行中','完结待审核','完结被驳回','已完结','活动被驳回');
        foreach($list as &$v){
            $sid = D('user')->getField('sid','uid='.$v['uid']);
            $v['uid'] = D('school')->getField('title','id='.$sid);
            if(empty($v['uid'])){
                $v['uid'] = '空';
            }
            $glist = D('group')->where('id='.$v['gid'])->field('name')->find();
            $v['gid'] = $glist['name'];
            if(empty($v['gid'])){
                $v['gid'] = '空';
            }
            $v['ctime'] = date('Y-m-d',$v['ctime']);
            if(empty($v['ctime'])){
                $v['ctime'] = '空';
            }
            $v['school_audit'] = $arr[$v['school_audit']];
            if(empty($v['school_audit'])){
                $v['school_audit'] = '空';
            }
            $map['status'] = 2;
            $map['eventId'] = $v['id'];
            $v['id'] = M('event_user')->where($map)->count();
            if(empty($v['id'])){
                $v['id'] = '空';
            }
            $map1['id'] = $v['typeId'];
            $tlist = M('event_type')->where($map1)->find();
            if($tlist['pid']!=0){
                $tlist = M('event_type')->where('id='.$tlist['pid'])->find();
            }
            $v['typeId'] = $tlist['name'];
            if(empty($v['typeId'])){
                $v['typeId'] = '空';
            }
        }
        //dump($list);die;
        $result = array('学校','活动标题','发起组织', '发起时间','活动状态','报名人数','签到人数','活动类型');
        array_unshift($list, $result);
        $name = '活动信息统计';
        service('Excel')->export2($list, $name);
    }
    //6.3各分类组织活动
    public function everyGroupEvent(){
        set_time_limit(0);
        for($i=1;$i<4;$i++){
            $map2['is_del'] = 0;
            $map2['category'] = $i;
            $num = D('group')->where($map2)->count();
            $glist = D('group')->where($map2)->field('id')->findAll();
            foreach($glist as $val){
                $arr[] = $val['id'];

            }
            $map['isDel'] = 0;
            $map['gid'] = array('in',$arr);
            $enum = D('event')->where($map)->count();
            $elist1 = D('event')->where($map)->field('id,joinCount')->findAll();
            $all=0;
            foreach($elist1 as $v ){
                $arr1[] = $v['id'];
                $all += $v['joinCount'];
            }
            $map1['status'] = 2;
            $map1['eventId'] = array('in',$arr1);
            $unum = D('event_user')->where($map1)->count();
            if($i==1){
                $res[] = '学生部门';
            }elseif($i==2){
                $res[] = '团支部';
            }else{
                $res[] = '学生社团';
            }
            $res[] = $num; //组织
            $res[] = $enum;  // 活动
            $res[] = $all;   //报名
            $res[] = $unum;  //签到
            $list[] = $res;
            unset($arr);
            unset($arr1);
            unset($res);
        }
        $result = array('分类','组织数','活动数', '报名数','签到数');
        array_unshift($list, $result);
        $name = '分类活动统计';
        service('Excel')->export2($list, $name);
    }
    //活动签到人数各级
    public function eventMember(){
        $this->error('影响生产环境，需重新设计。陆冬云');
        set_time_limit(0);
        $list = D('event_type')->where('isDel=0 and pid=0')->field('id,name')->findAll();
        if(!$list){
            $this->error('暂无数据!');
        }
        foreach ($list as &$v) {
            $res = M('event_type')->where('isDel=0 and pid='.$v['id'])->field('id,name')->findAll();
            $arr[] = $v['id'];
            foreach($res as $val){
                $arr[] = $val['id'];
            }
            $map['isDel'] = 0;
            $map['typeId'] = array('in',$arr);
            $v['count'] = M('event')->where($map)->count();
            $all = M('event')->where($map)->field('joinCount')->findAll();
            $v['joinCount'] = 0;
            foreach($all as $val1){
                $v['joinCount'] += $val1['joinCount'];
            }
            $map1['type'] = array('in',$arr);
            for($year=10;$year<15;$year++){
                $map1['year'] = $year;
                $v[$year] = M('xch')->where($map1)->count();

            }
            unset($v['id']);
            unset($map);
            unset($map1);
            unset($arr);
        }
        $result = array('类型','活动数','报名数','10级签到', '11级签到','12级签到','13级签到','14级签到');
        array_unshift($list, $result);
        $name = '活动类型统计';
        service('Excel')->export2($list, $name);
    }

    private function _eventStatus($school_audit){
        $arr = array('0'=>'待审核','1'=>'待审核','2'=>'进行中','3'=>'待完结','4'=>'完结被驳回','5'=>'已完结','6'=>'活动被驳回');
        if(isset($arr[$school_audit])){
            return $arr[$school_audit];
        }
        return '';
    }
    //3.1吐泡泡
    public function paopao1(){
        set_time_limit(0);
        $fileName = '吐泡泡';
        $calcMon = $this->_calcMon(t($_POST['mon']));
        $mon = $calcMon['mon'];
        $stime = $calcMon['stime'];
        $etime = $calcMon['etime'];
        $file = SITE_PATH.'/data/tj/3.1_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }
        $list = M('')->query("select cTime,sid,backCount,readCount,praiseCount from ts_forum where tid=0 and cTime>=$stime and cTime<=$etime order by `id` asc");
        if(!$list){
            $this->error('搜索结果为空');
        }
        foreach ($list as &$v) {
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['cTime'] = date('Y-m-d H:i',$v['cTime']);
        }
        $arr = array('发布时间', '所属学校', '回复数', '阅读数','赞');
        array_unshift($list, $arr);
        //当前月份不保存
        if($mon==date('Y-m')){
            service('Excel')->export2($list, $mon.$fileName);
            die;
        }
        service('Excel')->exportFile($list, $mon.$fileName,$file);
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }else{
            $this->error('生成文件错误');
        }
    }


  //摇一摇中奖纪录
  public function zjCount(){
      //$res['stime'] = strtotime('2014-06-05 13:12:57');
      //$res = date('Y-m-d H:i:s',1401945177);
      set_time_limit(0);
      $fileName = '摇一摇中奖纪录';
      $time1 = $_POST['mon1'];
      if(empty($time1)){
          $this->error('请输入时间');
      }
      $time2 = $_POST['mon2'];
      $stime = strtotime($time1);
      $etime = strtotime($time2);
      $map = "ctime>=$stime and ctime<=$etime";
      $pid = intval($_POST['pid']);
      if($pid){
          $map .= " and pid=$pid";
      }
      $count = M('LuckyZj')->where($map)->count();
      if($count>20000){
          $this->error("结果有$count 条，请缩减搜索范围！");
      }
      $list = M('')->query("select name,ctime,uid from ts_lucky_zj where $map order by `id` desc");
      if(!$list){
            $this->error('搜索结果为空');
      }

      foreach ($list as &$value) {
            $value['ctime'] = date('Y-m-d H:i:s',$value['ctime']);
            $uid = $value['uid'];
            $value['realname'] = getUserField($uid, 'realname');
            $value['xh'] = getUserEmailNum(getUserField($uid, 'email'));
            if(substr($value['xh'],0,1)==0){
                $value['xh'] = "'".$value['xh'];
            }
            $value['sid'] = tsGetSchoolByUid($uid);
            unset($value['uid']);
     }
     //dump($list);die;
     $arr = array('商品名', '中奖时间', '中奖人', '学号','学校');
     array_unshift($list, $arr);
     service('Excel')->export2($list,$fileName);
  }
    //3.2TA秀发布照片信息
  public function taShowPhoto(){
        set_time_limit(0);
        $fileName = 'TA秀发布照片';
        $calcMon = $this->_calcMon(t($_POST['mon']));
        $mon = $calcMon['mon'];
        $stime = $calcMon['stime'];
        $etime = $calcMon['etime'];
        $file = SITE_PATH.'/data/tj/3.2_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }
        $list = M('')->query("select photoId,cTime,uid,sid,praiseCount,backCount from ts_makefriends_photo where isDel=0 and cTime>=$stime and cTime<=$etime order by `photoId` asc");
        if(!$list){
            $this->error('搜索结果为空');
        }
        foreach ($list as &$v) {
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['uid'] = getUserField($v['uid'],'realname');
            $v['cTime'] = date('Y-m-d H:i',$v['cTime']);
        }
        $arr = array('ID','发布时间', '发布者', '所属学校', '被赞数','评论数','学号');
        array_unshift($list, $arr);
        //当前月份不保存
        if($mon==date('Y-m')){
            service('Excel')->export2($list, $mon.$fileName);
            die;
        }
        service('Excel')->exportFile($list, $mon.$fileName,$file);
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }else{
            $this->error('生成文件错误');
        }
    }
    //3.2TA秀礼物
    public function taShowGift(){
        set_time_limit(0);
        $fileName = 'TA秀礼物';
        $calcMon = $this->_calcMon(t($_POST['mon']));
        $mon = $calcMon['mon'];
        $stime = date('Y-m-d',$calcMon['stime']);
        $etime = date('Y-m-d',$calcMon['etime']);
        $file = SITE_PATH.'/data/tj/3.3_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }
        $list = M('')->query("select day,uid,sid,toid,giftNum,buyNum from ts_makefriends_gift where day>='$stime' and day<='$etime' order by `id` asc");
        if(!$list){
            $this->error('搜索结果为空');
        }
        foreach ($list as &$v) {
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['uid'] = getUserField($v['uid'],'realname');
            $v['toid'] = getUserField($v['toid'],'realname');
        }
        $arr = array('送礼日期','送礼人', '所属学校', '收礼人', '免费礼物数','收费礼物数');
        array_unshift($list, $arr);
        //当前月份不保存
        if($mon==date('Y-m')){
            service('Excel')->export2($list, $mon.$fileName);
            die;
        }
        service('Excel')->exportFile($list, $mon.$fileName,$file);
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }else{
            $this->error('生成文件错误');
        }
    }
    //4.1微博
    public function weibo1(){
        set_time_limit(0);
        $fileName = 'PU博客';
        $calcMon = $this->_calcMon(t($_POST['mon']));
        $mon = $calcMon['mon'];
        $stime = $calcMon['stime'];
        $etime = $calcMon['etime'];
        $file = SITE_PATH.'/data/tj/4.1_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }
        $list = M('')->query("select ctime,content,transpond,comment,heart from ts_weibo where uid=1709563 and ctime>=$stime and ctime<=$etime order by `weibo_id` asc");
        if(!$list){
            $this->error('搜索结果为空');
        }
        foreach ($list as &$v) {
            $v['ctime'] = date('Y-m-d',$v['ctime']);
            $v['content'] = htmlspecialchars_decode($v['content']);
        }
        $arr = array('日期', '内容', '转发数', '评论数','赞');
        array_unshift($list, $arr);
        //当前月份不保存
        if($mon==date('Y-m')){
            service('Excel')->export2($list, $mon.$fileName);
            die;
        }
        service('Excel')->exportFile($list, $mon.$fileName,$file);
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }else{
            $this->error('生成文件错误');
        }
    }
    //4.2话题统计 输入：#晒年味#
    public function weibo2() {
        set_time_limit(0);
        $content = t($_POST['content']);//#晒年味#
        $searchKey = $content.'%';
        $map['content'] = array('like',$searchKey);
        $map['isDel'] = 0;
        $count = D('weibo')->where($map)->count();
        if($count>=10000){
            $this->error('数据量太大，无法导出！');
            die;
        }
        $list = M('')->query("select sid,uid,comment,content,ctime,heart from ts_weibo where isdel=0 and content like '$searchKey' order by `weibo_id` DESC");
        foreach ($list as &$v) {
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['ctime'] = date('Y-m-d && H:i:s',$v['ctime']);
            $user = M('user')->where('uid='.$v['uid'])->field('realname,email')->find();
            $v['email'] = "'".getUserEmailNum($user['email']);
            $v['uid'] = $user['realname'];
        }
        //学校 学号 姓名 点赞数 评论数 内容
        $arr = array('学校','姓名','评论数', '内容','发布时间','点赞数','学号');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $content);
    }

    //游戏
    public function game(){
        $list = M('GameName')->select();
        $this->assign('list',$list);
        $this->display();
    }
    //游戏积分导出
    public function gameScore(){
        set_time_limit(0);
        $gid = intval($_POST['game']);
        $table = t($_POST['table']);
        $dao = M($table);
        $result = M('GameName')->where('gid='.$gid)->find();
        $list = $dao->where('gid='.$gid)->field('uid as rank,score,uid')->order('score DESC,id ASC')->limit(20)->findAll();
        if(!$list){
            $this->error('暂无数据!');
        }
        foreach ($list as $key=>&$v) {
            $v['rank'] = $key + 1;
            $user = M('user')->where('uid='.$v['uid'])->field('realname,email,sid')->find();
            $v['email'] = "'".getUserEmailNum($user['email']);
            $v['uid'] = $user['realname'];
            $v['sid'] = tsGetSchoolName($user['sid']);
        }
        $arr = array('排名','分数','姓名', '学号','学校');
        array_unshift($list, $arr);
        $name = $table=='GameScore'?'本周':'上周';
        $name .= $result['name'];
        service('Excel')->export2($list, $name);
    }
    //游戏排行截图
    public function gameTop(){
        $gid = intval($_POST['gid']);
        $table = t($_POST['table']);
        $dao = M($table);
        $list = $dao->where('gid='.$gid)->field('uid,score')->order('score DESC,id ASC')->limit(10)->findAll();
        if($list){
            foreach($list as $key=>&$v){
                $v['rank'] = $key+1;
                $v['realname'] = getUserField($v['uid'],'realname');
                $v['face'] = getUserFace($v['uid'],'b');
                unset($v['uid']);
            }
        }else{
            $this->error('暂无数据!');
        }
        $this->assign('list',$list);
        $this->display();
    }
    // 1. 用户数统计
    public function tjUser(){
        $key = 'Tj_tjUser';
        $cache = Mmc($key);
        if ($cache !== false) {
            $user = json_decode($cache, true);
        }else{
            $user['全部用户'] = M('user')->count();
            $user['登录过的用户'] = M('login_count')->count();
            $user['客户端登录过的用户'] = M('login')->count();
            $user['初始化过的用户'] = M('user')->where('`is_init` = 1')->count();
            $user['最后统计时间'] = date('Y-m-d H:i');
            Mmc($key, json_encode($user),0,3600*12);
        }
        $this->assign('statistics', $user);
        $this->display();
    }
    // 5.1 摇一摇统计
    public function tjYyy(){
        $list = M('y_tj')->order('day DESC')->findPage(7);
        $this->assign($list);
        $this->display();
    }

    // 4. 微博统计
    public function tjWeiboCount(){
        $list = M('weibo')->where('isdel=0')->field('sid,count(1) as count')->group('sid')->findAll();
        $this->assign('list',$list);
        $this->display();
    }
    //5.2 摇一摇各校人次
    public function yyy2(){
        set_time_limit(0);
        $fileName = '摇一摇各校人次';
        $input = t($_POST['mon']);
        if(!$input){
            $this->error('请输入月份');
        }
        $stime = strtotime($input.'-01');
        if(!$stime){
            $this->error('请输入正确月份');
        }
        $mon = date('Ym',$stime);
        $file = SITE_PATH.'/data/tj/5.2_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }
        $table = 'ts_y_record_'.$mon;
        $res = M('')->query("select TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='2012xyhui' and TABLE_NAME='$table';");
        if(!$res){
            $this->error('无'.$mon.'数据');
        }
        $list = M('')->query("select sid,count(distinct(uid)) as count from $table group by sid");
        foreach ($list as $k=>&$v) {
            if($v['sid']==0){
                unset($list[$k]);
            }
            $v['sid'] = tsGetSchoolName($v['sid']);
        }
        $arr = array('学校', '摇一摇人次');
        array_unshift($list, $arr);
        //当前月份不保存
        if($mon==date('Ym')){
            service('Excel')->export2($list, $mon.$fileName);
            die;
        }
        service('Excel')->exportFile($list, $mon.$fileName,$file);
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }else{
            $this->error('生成文件错误');
        }
    }

    //2.4 各校活动总数
    public function eventCount() {
        set_time_limit(0);
        $list = array();
        $schools = model('Schools')->makeLevel0Tree();
        $notj = array(659,473,1950);
        foreach ($schools as $k => $school) {
            $sid = $school['id'];
            if(!in_array($sid, $notj)){
                $list[$k]['school'] = $school['title'];
                $map = array();
                $map['status'] = 1;
                $map['isDel'] = 0;
                $map['is_school_event'] = $sid;
                $list[$k]['event'] = M('event')->where($map)->count();//活动总数
                $map['school_audit'] = 5;
                $list[$k]['finish'] = M('event')->where($map)->count();//总完结活动数
                $map = array();
                $map['usid'] = $sid;
                $list[$k]['join'] = M('event_user')->where($map)->count();//报名总人数
                $map['status'] = 2;
                $list[$k]['attend'] = M('event_user')->where($map)->count();//签到总人数
            }
        }
        $arr = array('学校','活动总数','总完结活动数','报名总人数','签到总人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,date('m-d').'各校活动总数');
    }
    //6.1各校部落总数
    public function group1() {
        set_time_limit(0);
        $schools = model('Schools')->makeLevel0Tree();
        $notj = array(659,473,1950);
        $list = array(array('学校','总数','学生部门','部门人数','团支部','团支部人数','学生社团','社团人数'));
        foreach ($schools as $v) {
            $sid = $v['id'];
            if(!in_array($sid, $notj)){
                $map['is_del'] = 0;
                $map['school'] = $sid;
                $all = M('group')->where($map)->count();
                if($all>0){
                    $row = array();
                    $row[] = $v['title'];
                    $row[] = $all;
                    $map2 = $map;
                    $map2['category']=1;
                    $row[] = M('group')->where($map2)->count();
                    $ids = M('group')->where($map2)->field('id')->findAll();
                    foreach($ids as $val){
                        $arr[] = $val['id'];
                    }
                    $map1['gid'] = array('in', $arr);
                    $map1['level'] = array('gt',0);
                    $row[] = D('group_member')->where($map1)->count();
                    unset($arr);
                    $map2['category']=2;
                    $row[] = M('group')->where($map2)->count();
                    $ids = M('group')->where($map2)->field('id')->findAll();
                    foreach($ids as $val){
                        $arr[] = $val['id'];
                    }
                    $map1['gid'] = array('in', $arr);
                    $map1['level'] = array('gt',0);
                    $row[] = D('group_member')->where($map1)->count();
                    unset($arr);
                    $map2['category']=3;
                    $row[] = M('group')->where($map2)->count();
                    $ids = M('group')->where($map2)->field('id')->findAll();
                    foreach($ids as $val){
                        $arr[] = $val['id'];
                    }
                    $map1['gid'] = array('in', $arr);
                    $map1['level'] = array('gt',0);
                    $row[] = D('group_member')->where($map1)->count();
                    unset($arr);
                    $list[] = $row;
                }
            }
        }
        service('Excel')->export2($list,date('m-d').'各校部落总数');
    }

    //6.2 统计部落加入人数
    public function groupCount(){
        set_time_limit(0);
        $map['is_del'] = 0;
        for($i=1;$i<4;$i++){
            $map['category']=$i;
            $glist[$i] = D('group')->where($map)->field('id')->findAll();
            foreach($glist[$i] as $v){
                $arr[$i][] = $v['id'];

            }
            //dump($map);
            $map1[$i]['gid'] = array('in', $arr[$i]);
            $map1[$i]['level'] = array('gt',0);
            $num[$i] = D('group_member')->where($map1[$i])->count();
        }
        $list = array(array($num[1],$num[2],$num[3]));
        $arr = array('部门人数','团支部人数','学生社团人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,date('m-d').'部落加入人数');
    }
    //6.3 社团分类人数
    public function groupCate(){
        set_time_limit(0);
        $list = D('group_category')->field('id,title')->findAll();
        $map['is_del'] = 0;
        foreach($list as &$v){
            $map['cid0']=$v['id'];
            $glist = D('group')->where($map)->field('id')->findAll();
            foreach($glist as $val){
                $arr[] = $val['id'];
            }
            $map1['gid'] = array('in', $arr);
            $map1['level'] = array('gt',0);
            $v['num'] = D('group_member')->where($map1)->count();
            unset($arr);
            unset($v['id']);
        }

        $arr = array('分类','人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,date('m-d').'社团分类人数');
    }
     //读取excel
    private function _readExcel(){
        if(empty($_FILES['file']) || strpos($_FILES['file']['name'], '.xls')===false){
            $this->error('请上传excel文档');
        }
        $dao = service('Excel');
        $res = $dao->read($_FILES['file']['tmp_name']);
        if(empty($res)){
            $this->error('文档无内容');
        }
        return $res;
    }
    //1.4. 所有学校年级用户数
    public function user4() {
        set_time_limit(0);
        $input = t($_POST['years']);
        if(!$input){
            $this->error('请输入年级');
        }
        $excels = $this->_readExcel();
        $years = explode(',', $input);
        $list = array();
        $countYear = count($years);
        $row1[] = '';
        $row2[] = '学校';
        foreach ($years as $k=>$v) {
            if($k==0){
                $row1[] = '注册用户';
            }else{
                $row1[] = '';
            }
            $row2[] = $v.'级';
        }
        foreach ($years as $k=>$v) {
            if($k==0){
                $row1[] = '活跃用户';
            }else{
                $row1[] = '';
            }
            $row2[] = $v.'级';
        }
        array_unshift($list, $row2);
        array_unshift($list, $row1);
//        $schools = model('Schools')->makeLevel0Tree();
//        var_dump(count($schools));die;
//        $notj = array(659,473,1950);
        $dao = M('user');
        $daoSchool = M('school');
        foreach ($excels as $w) {
            $title = $w[0];
            if(!$title)                continue;
            $school = $daoSchool->where("title='$title'")->field('id')->find();
            if(!$school)                continue;
            $sid = $school['id'];
                $map = array();
                $map['sid'] = $sid;
                $row = array();
                $row[] = $title;
                foreach ($years as $v) {
                    $map['year'] = $v;
                    $row[] = $dao->where($map)->count();
                }
                foreach ($years as $v) {
                    $map['year'] = $v;
                    $map['is_init'] = 1;
                    $row[] = $dao->where($map)->force('sid_year')->count();
                }
                $list[] = $row;
        }
//        foreach ($schools as $v) {
//            $sid = $v['id'];
//            if(!in_array($sid, $notj)){
//                $map = array();
//                $map['sid'] = $sid;
//                $row = array();
//                $row[] = $v['title'];
//                foreach ($years as $v) {
//                    $map['year'] = $v;
//                    $row[] = $dao->where($map)->count();
//                }
//                foreach ($years as $v) {
//                    $map['year'] = $v;
//                    $map['is_init'] = 1;
//                    $row[] = $dao->where($map)->count();
//                }
//                $list[] = $row;
//            }
//        }
        service('Excel')->export2($list,date('m-d').'所有学校年级用户数');
    }
    //1.5. 各校本月登录人员明细
    public function user5() {
        set_time_limit(0);
        $sid = intval($_POST['school']);
        if (!$sid) {
            $this->error('请选择学校');
        }
        $years = t($_POST['years']);
        if($years){
            $map['a.year'] = array('in',$years);
        }
        $map['a.sid'] = $sid;
        $map['a.istj'] = 1;
        $tableMon = date('Ym', strtotime('-1 day'));
        $table = 'ts_tj_login_'.$tableMon;
        $list = M('')->table("$table AS a ")
                ->join("ts_user AS b ON a.uid=b.uid")
                ->field('b.email,b.realname,b.sid1,b.year,b.major')
                ->where($map)->order('a.uid ASC')->group('a.uid')->findAll();
        if(!$list){
            $this->error('没有搜索到结果');
        }
        foreach ($list as &$v) {
            $v['email'] = "'".getUserEmailNum($v['email']);
            $v['sid1'] = tsGetSchoolName($v['sid1']);
        }
        $arr = array('学号', '姓名', '院系', '年级', '专业');
        array_unshift($list, $arr);
        $school = tsGetSchoolName($sid);
        service('Excel')->export2($list, $school.'_本月登录人员');
    }
    //1.6. 各校各年级人员明细
    public function user6() {
        set_time_limit(0);
        $sid = intval($_POST['school']);
        if (!$sid) {
            $this->error('请选择学校');
        }
        $years = t($_POST['years']);
        if(!$years){
            $this->error('请输入年级');
        }
        $map['sid'] = $sid;
        $map['year'] = array('in',$years);
        $list = M('user')->where($map)->force('sid_year')->field('email,realname,sid1,year,major')->findAll();
        if(!$list){
            $this->error('没有搜索到结果');
        }
        foreach ($list as &$v) {
            $v['email'] = "'".getUserEmailNum($v['email']);
            $v['sid1'] = tsGetSchoolName($v['sid1']);
        }
        $arr = array('学号', '姓名', '院系', '年级', '专业');
        array_unshift($list, $arr);
        $school = tsGetSchoolName($sid);
        service('Excel')->export2($list, $school.'_在校人员明细');
    }
    //1.7. 各校未初始化人员明细
    public function user7() {
        set_time_limit(0);
        $sid = intval($_POST['school']);
        if (!$sid) {
            $this->error('请选择学校');
        }
        $years = t($_POST['years']);
        if(!$years){
            $this->error('请输入年级');
        }
        $map['is_init'] = 0;
        $map['sid'] = $sid;
        $map['year'] = array('in',$years);
        $list = M('user')->where($map)->force('sid_year')->field('email,realname,sid1,year,major')->findAll();
        if(!$list){
            $this->error('没有搜索到结果');
        }
        foreach ($list as &$v) {
            $v['email'] = "'".getUserEmailNum($v['email']);
            $v['sid1'] = tsGetSchoolName($v['sid1']);
        }
        $arr = array('学号','姓名', '院系', '年级', '专业');
        array_unshift($list, $arr);
        $school = tsGetSchoolName($sid);
        service('Excel')->export2($list, $school.'_未初始化人员明细');
    }
    //1.8. 各校学分积分明细
    public function user8() {
        set_time_limit(0);
        $sid = intval($_POST['school']);
        if (!$sid) {
            $this->error('请选择学校');
        }
        $years = t($_POST['years']);
        if(!$years){
            $this->error('请输入年级');
        }
        $file = SITE_PATH.'/data/tmp/1.8_'.$sid.'-'.$years.'.xls';
        $school = tsGetSchoolName($sid);
        $fileName = $school.'_学分积分明细';
        if(file_exists($file)){
            $this->_download($file, $fileName.'.xls');
        }
        
        $map['a.sid'] = $sid;
        $map['year'] = array('in',$years);
        $list = M('')->table("ts_user AS a ")
                ->join("ts_user_score AS b ON a.uid=b.uid")
                ->field('a.uid,a.email,a.realname,a.sid1,a.year,a.major,a.school_event_credit,b.score')
                ->where($map)->order('a.uid ASC')->findAll();
        if(!$list){
            $this->error('没有搜索到结果');
        }
        $types = D('EventType','event')->getSearchType($sid);
        
        $applyV1 = isEcApplySchool($sid);
        $ecTypes = array();
        if($applyV1){
            $ecTypes = D('EcType','event')->getEcType($sid);
        }
//        $list = M('user')->where($map)->field('uid,email,realname,sid1,year,major,school_event_credit,school_event_credit as school_event_score')->findAll();
        $daoEventUser = M('EventUser');
        foreach ($list as &$v) {
            $uid = $v['uid'];
            unset($v['uid']);
            $v['email'] = "'".getUserEmailNum($v['email']);
            $v['sid1'] = tsGetSchoolName($v['sid1']);
            $v['score'] = intval($v['score']);
            $v['jy'] = intval($this->getScoreAsJy($uid));
            foreach($types as $typeId=>$type){
                unset($map);
                $map['b.typeId'] = $typeId;
                $map['a.uid'] = $uid;
                $map['a.fTime'] = array('gt',0);
                $credit = M('')->table("ts_event_user AS a")
                        ->join("ts_event AS b ON a.eventId=b.id")
                        ->field('sum(a.credit) as credit,sum(a.addCredit) as addCredit')
                        ->where($map)->find();
                $v['type'.$typeId] = $credit['credit']+$credit['addCredit'];
            }
            if($applyV1){
            foreach($ecTypes as $k){
                $ecId = $k['id'];
                unset($map);
                $map['uid'] = $uid;
                $map['status'] = 1;
                $map['type'] = $ecId;
                $credit = M('ec_apply')->where($map)->sum('credit');
                $v['ec'.$ecId] = $credit+0;
            }
            }
        }

        $arr = array('学号','姓名', '院系', '年级', '专业', '学分', '积分','经验');
        foreach($types as $typeId=>$type){
            $arr[] = $type;
        }
        foreach($ecTypes as $ecType){
            $arr[] = $ecType['title'];
        }
        array_unshift($list, $arr);
        service('Excel')->exportFile($list,$fileName,$file);
        $this->_download($file, $fileName.'.xls');
    }

    private function getScoreAsJy($uid)
    {
        $jy = M('credit_user')->where('uid = '.$uid)->getField('score');
        return $jy;
    }

    private function _ecType($sid,$uid,&$list){
        $ecTypes = D('EcType','event')->getEcType($sid);
    }
    private function _download($file,$fileName){
        include_once(SITE_PATH . '/addons/libs/Http.class.php');
        Http::download($file,$fileName);
    }

    //7.1. 正在云购商品
    public function legou1() {
        set_time_limit(0);
        $map['codeState'] = 1;
        $list = M('')->table("ts_shop_yg AS a ")
                ->join("ts_shop_product AS b ON b.id=a.product_id")
                ->field('b.name,a.ctime,a.need_attended,a.has_attended,a.product_id,a.times,b.over_times')
                ->where($map)->order('a.id DESC')->findAll();
        $dao = M('shop_yg');
        foreach ($list as &$v) {
            if($v['times']!=1){
                $v['ctime'] = $dao->getField('ctime','times=1 and product_id='.$v['product_id']);
            }
            $v['ctime'] = date('Y-m-d',$v['ctime']);
            $v['product_id'] = $v['need_attended']-$v['has_attended'];
        }
        $arr = array('商品名称','上架时间','总需人数','已购人数','所差人数','目前期数','总期数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,date('m-d').'正在云购商品');
    }
    // 1.1.2 日活月活
    public function rh(){
        $list = M('tj_rh')->order('day DESC')->findPage(10);
        if(falseRh($this->mid)){
            $list = M('tj_rh1')->order('day DESC')->findPage(10);
        }
        $this->assign($list);
        $this->display();
    }
    public function yanqing1(){
        $dbday = t($_POST['eday']);
        $file = SITE_PATH.'/data/yq/yh_'.$dbday.'.xls';
        if(!file_exists($file)){
            $this->error($dbday.'日统计未存档');
        }
        include_once(SITE_PATH . '/addons/libs/Http.class.php');
        Http::download($file,'各校每日月活'.$dbday.'.xls');
    }
    // 1.1.2 月活
    public function yh(){
        $school = $this->allSchool();
        $this->assign('school', $school);
        $sid = intval($_POST['sid']);
        $res = array();
        if($sid>0 && isset($school[$sid])){
            $res = $this->_getYh($sid);
            $res['sid'] = $sid;
            $res['schoolName'] = $school[$sid]['title'];
        }
        $this->assign('res',$res);
        $this->display();
    }
    private function _getYh($sid){
        $cache = Mmc('Tjyh_'.$sid);
        if ($cache !== false) {
            $cache = json_decode($cache, true);
            $giltTime = strtotime('-1 hour');
            if($cache['time']>$giltTime){
                return $cache;
            }
        }
        $res['loginCount'] = 0;
        $res['allCount'] = 0;
        $res['time'] = time();
        $table = 'tj_login_'.date('Ym');
        $tableName = 'ts_'.$table;
        $hasTable = M('')->query("select TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='2012xyhui' and TABLE_NAME='$tableName';");
        if($hasTable){
            $count = M('')->query("select count(distinct(uid)) as count from $tableName where istj=1 and sid=$sid;");
            $res['loginCount'] = $count[0]['count'];
            $years = $this->_activYears($sid);
            $count = M('')->query("select count(1) as count from ts_user where year in($years) and sid=$sid;");
            $res['allCount'] = $count[0]['count'];
        }
        Mmc('Tjyh_'.$sid, json_encode($res),0,3700);
        return $res;
    }
    private function _activYears($sid){
        $fromYear = date('Y')-2000;
        $nowDay = date('m-d');
        if($nowDay<'09-01'){
            $fromYear -= 1;
        }
        $years = $fromYear;
        $school = $this->allSchool();
        $schoolYear = $school[$sid]['tj_year'];
        for($i=1;$i<$schoolYear;$i++){
            $addYear = $fromYear-$i;
            $years .= ','.$addYear;
        }
        return $years;
    }

    //个类活动数量
    public function eventType(){
        $types = D('EventType','event')->getType();
        $map['isDel'] = 0;
        $map['status'] = 1;
        $dao = M('event');
        $list = array();
        foreach($types as $k=>$v){
            $row = array();
            $row['type'] = $v;
            $row['num'] = $dao->getField('count(1) as count', 'isDel=0 and status=1 and typeId='.$k);
            $list[] = $row;
        }
        $this->assign('list',$list);
        $this->display();
    }

    // ============================================
    //活动类型数据分析
    public function activityTypeAnalysis() {
        $m = M('event');
        $list = $m->join('ts_user on ts_user.uid = ts_event.uid')
                ->field('ts_user.sid,ts_event.typeId,count(*)')
                ->group('ts_user.sid,ts_event.typeId')
                ->select();

        foreach ($list as &$val) {

            //加入09-14报名人数
            for ($i = 9; $i <= 14; $i++) {
                $map['ts_user.sid'] = $val['sid'];
                $map['typeId'] = $val['typeId'];
                $map['ts_user.year'] = "{$i}";
                if ($i < 10) {
                    $map['ts_user.year'] = "0{$i}";
                }
                $num = $m->join('ts_user on ts_user.uid=ts_event.uid')
                                ->join('ts_event_user on ts_event_user.eventId=ts_event.id')
                                ->where($map)->count();
                $val[] = $num;
            }

            //加入09-14签到人数
            for ($i = 9; $i <= 14; $i++) {
                $map['ts_user.sid'] = $val['sid'];
                $map['typeId'] = $val['typeId'];
                $map['ts_event_user.status'] = 2;
                $map['ts_user.year'] = "{$i}";
                if ($i < 10) {
                    $map['ts_user.year'] = "0{$i}";
                }
                $num = $m->join('ts_user on ts_user.uid=ts_event.uid')
                                ->join('ts_event_user on ts_event_user.eventId=ts_event.id')
                                ->where($map)->count();
                $val[] = $num;
                unset($map['ts_event_user.status']);
            }
            $val['sid'] = tsGetSchoolName($val['sid']); //学校名
            $val['typeId'] = M('eventType')->getField('name', 'id=' . $val['typeId']);
        }

        $arr = array('学校', '活动类型', '活动数量', '(报名人数)09级', '10级', '11级', '12级', '13级', '14级', '(签到人数)09级', '10级', '11级', '12级', '13级', '14级');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '活动类型数据分析');
    }

    //所有高校不同类型社团的成员数
    public function every_shcool_community_munber() {
        $m = M(group);
        $list = $m->field('school,name,membercount')
                ->order('school')
                ->select();
        foreach ($list as &$val) {
            $val['school'] = tsGetSchoolName($val['school']); //学校名
        }
        $arr = array('学校', '类型', '人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '所有高校不同类型社团的成员数');
    }

    //5.三大部落成员数统计（社团人数、团支部人数、学生部门人数）
    public function threeGroup() {
        //1学生部门 2团支部 3学生社团
        $gArr = array('学生部门' => 1, '团支部' => 3, '学生社团' => 2);
        $list = array();
        foreach ($gArr as $key => &$val) {
            $map['category'] = $val;
            $list[] = array($key, M(group)->where($map)->sum(membercount));
        }
        $arr = array('类型', '人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '三大部落成员数统计');
    }

    //单个学校数据：
    public function everySchoolData() {
        $school = $this->allSchool();
        $this->assign('school', $school);
        $this->display();
    }

    //1.校内通知4个类型的数据量（院系通知、学校发文、学校通知、其他）
    public function schoolNotice() {
        if ($_POST['school'] == 'please') {
            $this->error('请选择学校');
        }
        $sid = $_POST['school'];
        $m = M('announce');
        $map['sid'] = $sid;
        if (intval($_POST['yuefen'])) {
            $map["from_unixtime(cTime,'%Y-%m')"] = array('like', "%{$_POST['yuefen']}%");
        }

        $list = $m->where($map)
                ->field("from_unixtime(cTime,'%Y-%m') as cTime")
                ->group("from_unixtime(cTime,'%Y-%m')")
                ->select();
        $res = array();
        foreach ($list as &$val) {
            $map["from_unixtime(cTime,'%Y-%m')"] = $val['cTime'];
            //cid 1学校发文 2学校通知 3院系通知 4其他
            $cArr = array('院系通知' => 3, '学校发文' => 1, '学校通知' => 2, '其他' => 4);
            foreach ($cArr as $key => $v) {
                $map['cid'] = $v;
                $res[] = array($val['cTime'], $key, $m->where($map)->count());
            }
        }
        $arr = array('日期', '类型', '数量');
        array_unshift($res, $arr);
        service('Excel')->export2($res, '校内通知4个类型的数据量');
    }

    //2.微博发布数（本校、各院系、年级）；
    public function weiboxx() {
        if ($_POST['school'] == 'please') {
            $this->error('请选择学校');
        }
        $map['u.sid'] = $_POST['school'];
        $sid = $_POST['school'];
        if (intval($_POST['yuefen'])) {
            $map["from_unixtime(w.cTime,'%Y-%m')"] = array('like', "%{$_POST['yuefen']}%");
        }
        if ($_POST['year'] != 'please') {
            $map['u.year'] = $_POST['year'];
        }
        $m = M('weibo');
        $list = $m->table("ts_weibo w")
                ->join("ts_user u on u.uid = w.uid")
                ->where($map)
                ->field("from_unixtime(w.cTime,'%Y-%m'),u.sid,u.sid1,u.year,count(*)")
                ->group("from_unixtime(w.cTime,'%Y-%m'),u.sid1,u.year")
                ->select();
        $arr = array('日期', '学校', '院系', '年级', '数量');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '微博发布数');
    }

    //9.未初始化学生信息
    public function uninitialized() {
        if ($_POST['school'] == 'please') {
            $this->error('请选择学校');
        }
        $sid = $_POST['school'];
        $map['is_init'] = 0;
        $list = M('user')->where($map)->field('sid,email,uname,sid1,year,major,mobile')->select();
        foreach ($list as &$val) {
            $val['sid'] = tsGetSchoolName($val['sid']); //学校名
            $val['sid1'] = tsGetSchoolName($val['sid1']); //学校名
        }
        $arr = array('学校', '学号', '姓名', '院系', '年级', '专业', '联系电话');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '未初始化学生信息');
    }

    //话题（专题）参与度
    public function topicConversation() {
//         if($_POST['school'] == 'please'){
//              $this->error('请选择学校');
//          }
//         $map['u.sid'] = $_POST['school'];
//         if(intval($_POST['yuefen'])){
//            $map["from_unixtime(t.ctime,'%Y-%m')"] = array('like',"%{$_POST['yuefen']}%");
//          }
//          $m = M('weiboTopic');
//          $list = $m->table("ts_weibo_topic as t")
//                    ->join("ts_weibo_topic_link as l on l.topic_id=t.topic_id")
//                    ->join("ts_weibo as w on w.weibo_id = l.weibo_id")
//                    ->join("ts_user as u on u.uid=w.uid")
//                    ->field("from_unixtime(t.ctime,'%Y-%m'),count(*),t.name,u.year")
//                    ->where($map)
//                    ->group("t.name,u.year")
//                    ->select();
//
//          echo '<pre>';
//          print_r($list);
//          echo '</pre>';
//          echo $m->getLastSql();
    }

    //活动
    public function activityGroupBySid1() {
        if ($_POST['school'] == 'please') {
            $this->error('请选择学校');
        }
        $map['u.sid'] = $_POST['school'];
        if (intval($_POST['yuefen'])) {
            $map["from_unixtime(e.cTime,'%Y-%m')"] = array('like', "%{$_POST['yuefen']}%");
        }
        $m = M('event');
        //各月份各学校各年级所有情况
        $list = $m->table("ts_event as e")
                ->join("ts_user as u on e.uid=u.uid")
                ->where($map)
                ->field("from_unixtime(e.cTime,'%Y-%m') as cTime,e.is_school_event,u.sid1, count(*) as num,sum(joinCount) as joinCount")
                ->group("from_unixtime(e.cTime,'%Y-%m'), u.sid1 ")
                ->select();
        unset($map);
        foreach ($list as &$val) {
            $map["from_unixtime(e.cTime,'%Y-%m')"] = $val['cTime'];
            $map['u.sid'] = $val['sid'];
            $map['e.status'] = 2;
            $end = $m->table("ts_event as e")->join("ts_user as u on u.uid=e.uid")->where($map)->count();
            $val['end'] = $end;
            unset($map['e.status']);
            $val['jcount'] = $val['joinCount'];
            unset($val['joinCount']);
            //签到人数
            $map['tu.status'] = 2;
            $qd = $m->table("ts_event as e")->join("ts_user as u on u.uid=e.uid")->join("ts_event_user as tu on tu.eventId=e.id")->where($map)->count();
            $val['qd'] = $qd;
            unset($map['tu.status']);
            $val['is_school_event'] = tsGetSchoolName($val['sid']); //学校
            $val['sid1'] = tsGetSchoolName($val['sid1']); //院系
        }
        $arr = array('日期', '学校', '院系', '发起活动总数', '完结活动总数', '报名人数', '签到人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '各个院系与年级活动情况');
    }

    public function activityGroupByYear() {
        if ($_POST['school'] == 'please') {
            $this->error('请选择学校');
        }
        $map['u.sid'] = $_POST['school'];
        if (intval($_POST['yuefen'])) {
            $map["from_unixtime(e.cTime,'%Y-%m')"] = array('like', "%{$_POST['yuefen']}%");
        }
        $m = M('event');
        //各月份各学校各年级所有情况
        $list = $m->table("ts_event as e")
                ->join("ts_user as u on e.uid=u.uid")
                ->where($map)
                ->field("from_unixtime(e.cTime,'%Y-%m') as cTime,e.is_school_event,u.year, count(*) as num,sum(joinCount) as joinCount")
                ->group("from_unixtime(e.cTime,'%Y-%m'), u.year ")
                ->select();
        unset($map);
        foreach ($list as &$val) {
            $map["from_unixtime(e.cTime,'%Y-%m')"] = $val['cTime'];
            $map['u.sid'] = $val['sid'];
            $map['e.status'] = 2;
            $end = $m->table("ts_event as e")->join("ts_user as u on u.uid=e.uid")->where($map)->count();
            $val['end'] = $end;
            unset($map['e.status']);
            $val['jcount'] = $val['joinCount'];
            unset($val['joinCount']);
            //签到人数
            $map['tu.status'] = 2;
            $qd = $m->table("ts_event as e")->join("ts_user as u on u.uid=e.uid")->join("ts_event_user as tu on tu.eventId=e.id")->where($map)->count();
            $val['qd'] = $qd;
            unset($map['tu.status']);
            $val['is_school_event'] = tsGetSchoolName($val['sid']); //学校
            $val['sid1'] = tsGetSchoolName($val['sid1']); //院系
        }
        $arr = array('日期', '学校', '年级', '发起活动总数', '完结活动总数', '报名人数', '签到人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '各个院系与年级活动情况');
    }

    //扑天下阅读数统计
    public function ptxCount(){
        $list = D('ptx_list')->field('title,rnum')->findAll();
        $arr = array('名称', '阅读数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'扑天下阅读数');
    }

    //成长服务超市
    public function growCount(){
        $map['pid'] = array('neq',0);

        $list = D('grow_categroy')->where($map)->field('pid,name,id')->findAll();
        //dump($list);die;
        foreach ($list as &$v){
            $map1['cid1'] = $v['pid'];
            $map1['cid2'] = $v['id'];
            $map1['isDel'] = 0;
            $v['pid'] = D('grow_categroy')->getField('name','id='.$v['pid']);
            $v['id'] = D('grow_information')->where($map1)->sum('rnum');
        }
        $arr = array('频道','类别', '阅读数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'成长服务超市阅读数');
    }

    //1.9导出所有学校
    public function allProSchool(){
        $sql = 'select b.pid,b.city,a.title from ts_school as a,ts_citys as b where a.cityId=b.id and a.pid=0 order by b.pid ASC';
        $list = M()->query($sql);
        foreach($list as &$v){
            $v['pid'] = D('province')->getField('title','id='.$v['pid']);
        }
        //dump($list);die;
        $arr = array('省份','城市', '学校');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'各省市学校表');
    }

    //导出今天借款的
    public function todayFinance(){
        $time1 = time();
        $time = strtotime('+1 month',$time1);
        $map['stime'] = date('Y-m-d',$time);
        $map['status'] = 1;
        $list = D('bank_finance')->where($map)->field('bank_card_id,money,ctime,staging,mark')->findAll();

        foreach($list as $key=>$val){

            $list[$key]['card_account'] = D('bank_card')->getField('card_account','id='.$val['bank_card_id']);
            $list[$key]['mobile'] = D('bank_card')->getField('mobile','id='.$val['bank_card_id']);
            $list[$key]['bank_card_id'] = D('bank_card')->getField('realname','id='.$val['bank_card_id']);
            if($val['mark']==1){
                $val['mark'] = '一千元免息';
            }else{
                $val['mark'] = '非一千元免息';
            }
        }

        $arr = array('姓名','借款金额', '申请日期','分期数','借款类型','开户名','联系电话');
        array_unshift($list, $arr);
        //dump($list);die;
        service('Excel')->export2($list,'PU订单表');
    }


    public function exportGroup(){
        $city = intval($_POST['city']);
        if ($city < 1){
            $this->error('ID错误');
        }

        //找到社团
        $grous = M('Group')
            ->field('S.title,ts_group.name,ts_group.membercount,U.realname,U.sex,U.mobile')
            //学校信息
            ->join('ts_school S ON S.id=ts_group.school')
            //社长信息
            ->join('ts_user U ON U.uid=ts_group.uid')
            ->where(array('S.cityId'=>$city))
            ->select();

        $arr = array('学校名称','社团名称', '社团人数','社长姓名','性别(0代表女，1代表男)','手机');
        array_unshift($grous, $arr);
        //dump($list);die;
        service('Excel')->export2($grous,'PU部落统计');
    }
    
    //2.9学校年制导出
    public function allSchoolYear(){
        $list = D('school')->where('pid=0')->field('title,tj_year')->findAll();
        $arr = array('学校名称','学年数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'所有学校学年统计');
    }

    //9 一元猎宝
    public function yiYuanLieBao()
    {
        $lists = M('yiyuanliebao_tj')->select();
        $this->assign('lists',$lists);
        $this->display();
    }
    
}

?>