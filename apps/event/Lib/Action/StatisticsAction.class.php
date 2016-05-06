<?php

/**
 * Created by PhpStorm.
 * User: zhuhaibing
 * Date: 2016/4/7
 * Time: 10:28
 */
class StatisticsAction extends TeacherAction
{

    public function _initialize()
    {
        //管理权限判定
        parent::_initialize();
        if(!$this->rights['can_admin'])
        {
            echo('无权查看');
            die;
        }
    }

    //用户统计首页
    public function index()
    {
        $sid = $this->sid;
        $map['pid'] = $sid;
        $department = M('school')->where($map)->select();
        $this->assign('sid',$sid);
        $this->assign('department',$department);
        for($i = 0; $i< 8 ; $i++)
        {
            $year[$i]['id'] = date('y',strtotime('-'.$i.' year'));
            $year[$i]['year'] = date('y',strtotime('-'.$i.' year')).'级';
        }
        $this->assign('year',$year);
        $this->display();
    }

    //用户统计-用户数统计
    public function userDetail()
    {
        if ($_POST['school'])
        {
            $map['sid'] = intval($_POST['school']);
        } else {
            $this->error('请选择学校');
        }
        if ($_POST['department'])
        {
            $map['sid1'] = intval($_POST['department']);
        }
        if ($_POST['year'])
        {
            $map['year'] = t($_POST['year']);
        }
        $res = M('user')->where($map)->field('sid1,year,count(*) as num')->group('sid1,year')->select();
        $school = tsGetSchoolName($map['sid']);
        $list = array();
        foreach ($res as $v)
        {
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

    //用户统计 - 年级用户人员明细
    public function userYearDetail()
    {
        set_time_limit(0);
        $input = t($_POST['years']);
        if(!$input){
            $this->error('请输入年级');
        }
        $years = explode(',', $input);
        $list = array();
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
        $dao = M('user');
        $daoSchool = M('school');
        $school = array(array($daoSchool->where('id = '.$this->sid)->getField('title')));
        foreach ($school as $w) {
            $title = $w[0];
            if(!$title)
                continue;
            $school = $daoSchool->where("title='$title'")->field('id')->find();
            if(!$school)
                continue;
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
        service('Excel')->export2($list,$title.'-'.date('m-d').'-学校年级用户数');
    }

    //用户统计- 本月登录人员明细
    public function userLoginMonthDetail()
    {
        set_time_limit(0);
        $sid = $this->sid;
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

    //用户统计- 各年级在校人员明细
    public function userYearOnlineDetail()
    {
        set_time_limit(0);
        $sid = $this->sid;
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

    //用户统计 - 未初始化人员明细
    public function userNoInitDetail()
    {
        set_time_limit(0);
        $sid = $this->sid;
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

    //用户统计 - 学分积分明细
    public function userScoreDetail()
    {
        set_time_limit(0);
        $sid = $this->sid;
        $years = t($_POST['years']);
        if(!$years){
            $this->error('请输入年级');
        }
        $file = SITE_PATH.'/data/tmp/1.8_'.$sid.'-'.$years.'_'.time().'.xls';
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
        if($applyV1)
        {
            $ecTypes = D('EcType','event')->getEcType($sid);
        }else
        {
            $ecTypes = D('EcFolder','event')->allEcFolder($sid, true);
        }
        foreach ($list as &$v)
        {
            $uid = $v['uid'];
            unset($v['uid']);
            $v['email'] = "'".getUserEmailNum($v['email']);
            $v['sid1'] = tsGetSchoolName($v['sid1']);
            $v['score'] = intval($v['score']);
            $v['jy'] = intval($this->getScoreAsJy($uid));
            foreach($types as $typeId=>$type)
            {
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
            if($applyV1)
            {
                foreach($ecTypes as $k)
                {
                    $ecId = $k['id'];
                    unset($map);
                    $map['uid'] = $uid;
                    $map['status'] = 1;
                    $map['type'] = $ecId;
                    $credit = M('ec_apply')->where($map)->sum('credit');
                    $v['ec'.$ecId] = $credit+0;
                }
            }
            else
            {
                foreach($ecTypes as $k)
                {
                    $ecId = $k['id'];
                    $ecIdArr = $k['files'];
                    if(!empty($ecIdArr))
                    {
                        $idArr = array();
                        foreach($ecIdArr as $e)
                        {
                            $idArr[] = $e['id'];
                        }
                    }
                    else
                    {
                        $idArr = array();
                        $idArr[] = $ecId;
                    }
                    unset($map);
                    $map['uid'] = $uid;
                    $map['status'] = 1;
                    $map['fileId'] = array('IN',join(',',$idArr));
                    $credit = M('ec_identify')->where($map)->sum('credit') + 0;
                    $v['ec'.$ecId] = $credit;
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

    //获取用户的经验值
    private function getScoreAsJy($uid)
    {
        $jy = M('credit_user')->where('uid = '.$uid)->getField('score');
        return $jy;
    }

    //下载EXCEL表格
    private function _download($file,$fileName){
        include_once(SITE_PATH . '/addons/libs/Http.class.php');
        Http::download($file,$fileName);
    }

    //部落统计首页
    public function group()
    {
        $this->display();
    }

    //部落统计 - 部落总数
    public function groupSum()
    {
        set_time_limit(0);
        $schools = M('school')->where('id = '.$this->sid)->select();
        $list = array(array('学校','总数','学生部门','部门人数','团支部','团支部人数','学生社团','社团人数'));
        foreach ($schools as $v) {
            $sid = $v['id'];
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
        service('Excel')->export2($list,tsGetSchoolName($this->sid).'-'.date('m-d').'-部落总数');
    }

    //部落统计 - 社团分类人数
    public function groupCate()
    {
        set_time_limit(0);
        $list = D('group_category')->field('id,title')->findAll();
        $map['is_del'] = 0;
        foreach($list as &$v){
            $map['cid0'] = $v['id'];
            $map['school'] = $this->sid;
            $glist = D('group')->where($map)->field('id')->findAll();
            foreach($glist as $val){
                $arr[] = $val['id'];
            }
            $m_map['gid'] = array('in', $arr);
            $m_map['level'] = array('gt',0);
            $v['num'] = D('group_member')->where($m_map)->count();
            unset($arr);
            unset($v['id']);
        }
        $arr = array('分类','人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,tsGetSchoolName($this->sid).'-'.date('m-d').'-社团分类人数');
    }

    //部落统计 - 部落活动数
    public function groupEvent()
    {
        set_time_limit(0);
        for($i=1;$i<4;$i++){
            $map['is_del'] = 0;
            $map['category'] = $i;
            $map['school'] = $this->sid;
            $num = D('group')->where($map)->count();
            $g_list = D('group')->where($map)->field('id')->findAll();
            foreach($g_list as $val){
                $g_arr[] = $val['id'];

            }
            $e_map['isDel'] = 0;
            $e_map['gid'] = array('in',$g_arr);
            $enum = D('event')->where($e_map)->count();
            $e_list = D('event')->where($e_map)->field('id,joinCount')->findAll();
            $all=0;
            foreach($e_list as $v ){
                $e_arr[] = $v['id'];
                $all += $v['joinCount'];
            }
            $e_u_map['status'] = 2;
            $e_u_map['eventId'] = array('in',$e_arr);
            $u_num = D('event_user')->where($e_u_map)->count();
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
            $res[] = $u_num;  //签到
            $list[] = $res;
            unset($g_arr);
            unset($e_arr);
            unset($res);
        }
        $result = array('分类','组织数','活动数', '报名数','签到数');
        array_unshift($list, $result);
        $name = tsGetSchoolName($this->sid).'-分类活动统计';
        service('Excel')->export2($list, $name);
    }

    //部落统计 - 部落详情统计
    public function groupDetail()
    {
        $sid = $this->sid;
        //找到社团
        $groups = M('Group')
            ->field('S.title,ts_group.name,ts_group.membercount,U.realname,U.sex,U.mobile')
            //学校信息
            ->join('ts_school S ON S.id=ts_group.school')
            //社长信息
            ->join('ts_user U ON U.uid=ts_group.uid')
            ->where(array('ts_group.school'=>$sid))
            ->select();

        $arr = array('学校名称','社团名称', '社团人数','社长姓名','性别(0代表女，1代表男)','手机');
        array_unshift($groups, $arr);
        service('Excel')->export2($groups,tsGetSchoolName($this->sid).'-部落详细统计');
    }

    //活动统计首页
    public function event()
    {
        $this->display();
    }

    //检测月份
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

    //活动统计 - 各年级活动明细
    public function eventYearDetail()
    {
        set_time_limit(0);
        $fileName = tsGetSchoolName($this->sid).'-活动总数';
        $calcMon = $this->_calcMon(t($_POST['mon']));
        $mon = $calcMon['mon'];
        $stime = $calcMon['stime'];
        $etime = $calcMon['etime'];
        $file = SITE_PATH.'/data/tj/2.1_'.$this->sid.'_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }
        $table = 'ts_temp_'.rand(1,9999).'_'.$this->sid.'_'.time();
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
        $sql = "insert into `$table` select id,is_school_event,school_audit,joinCount,uid from ts_event where isDel=0 and is_school_event = ".$this->sid." and school_audit>1 and cTime>=$stime and cTime<=$etime";
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
        $res = M('')->query("select *,count(1) as num,sum(joinCount) as gjoin from $table where is_school_event = ".$this->sid." group by is_school_event,year");
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

    //活动统计 - 各组织部门活动数
    public function eventOrgSum()
    {
        set_time_limit(0);
        $fileName = tsGetSchoolName($this->sid).'-三大部落活动详情';
        $calcMon = $this->_calcMon(t($_POST['mon']));
        $mon = $calcMon['mon'];
        $stime = $calcMon['stime'];
        $etime = $calcMon['etime'];
        $file = SITE_PATH.'/data/tj/2.2_'.$this->sid.'_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }
        $table = 'ts_temp_'.rand(1,9999).'_'.$this->sid.'_'.time();
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
        $res = M('')->query("select distinct(is_school_event) from $table WHERE is_school_event = ".$this->sid);
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

    //活动状态
    private function _eventStatus($school_audit){
        $arr = array('0'=>'待审核','1'=>'待审核','2'=>'进行中','3'=>'待完结','4'=>'完结被驳回','5'=>'已完结','6'=>'活动被驳回');
        if(isset($arr[$school_audit])){
            return $arr[$school_audit];
        }
        return '';
    }

    //活动统计 - 活动明细
    public function eventDetail()
    {
        set_time_limit(0);
        $fileName = tsGetSchoolName($this->sid).'-活动明细';
        $calcMon = $this->_calcMon(t($_POST['mon']));
        $mon = $calcMon['mon'];
        $stime = $calcMon['stime'];
        $etime = $calcMon['etime'];
        $file = SITE_PATH.'/data/tj/2.3_'.$this->sid.'_'.$mon.'.xls';
        if(file_exists($file)){
            include_once(SITE_PATH . '/addons/libs/Http.class.php');
            Http::download($file,$mon.$fileName.'.xls');
        }
        $daoEvent = M('event');
        $list = $daoEvent->where("isDel=0 and cTime>=$stime and cTime<=$etime and is_school_event = ".$this->sid)->order('is_school_event ASC,id ASC')
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

    //活动统计 - 活动总数
    public function eventSum()
    {
        set_time_limit(0);
        $list = array();
        $schools = M('school')->where('id = '.$this->sid)->select();
        foreach ($schools as $k => $school) {
            $sid = $school['id'];
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
        $arr = array('学校','活动总数','总完结活动数','报名总人数','签到总人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,tsGetSchoolName($this->sid).'-'.date('m-d').'-活动总数');
    }

}