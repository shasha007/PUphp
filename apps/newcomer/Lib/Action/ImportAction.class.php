<?php
/**
 * 1.excel里把列的顺序调整为 学号,姓名,学院
2.导出为逗号分隔的csv
3.把csv copy到apps/newcomer/Appinfo, 并命名为数字连续的文件名
4.修改apps/newcomer/Lib/Action/ImportAction.class.php 中的几个参数
5.打开网页用管理员登录, 并访问 import
 *
 * 配置一下导入的参数, 比如tag就是这批导入时候要加的2012级还是2011级
 *  $school = "苏州大学";
    $email_post = "@mysuda.com";
    $sid = 1; //学校id
debug=true是测试一下, 只导一个就停止的, 如果没问题, 再改成false就全导入了
start end是批量导入csv文件
 * update ts_user set password='53dfac50ed707f67bba6c4161c0c947f5e5378e3',email2='',mobile='',is_init=0 where
 * 访问这个import的时候, 还要加一下go=1
 */
import('admin.Action.AdministratorAction');
class ImportAction extends AdministratorAction {

	private $sid;
	private $email_post;
    private $oldEmailPost;
	private $debug;
	private $start;
	private $end;
	private $failArray;
	private $credit;
	private $daoUser;
	private $daoFollow;
	private $daoCredit;
	private $daoEventGroup;
    private $error;
    /** 旧学校ID **/
    private $oldSid;

    public function _initialize() {
        parent::_initialize();
            $this->sid = 2282;
            $this->start = 20160303;
            $this->credit = 200;
            $this->oldSid = 623;
            $school = M('school')->where('id='.$this->sid)->field('title,email')->find();
            $oldSchool = M('school')->where('id='.$this->oldSid)->field('title,email')->find();
            if(!$school){
                echo '学校不存在';
                die;
            }
            $this->email_post = $school['email'];
            if(!$this->email_post){
                echo '学校邮箱不存在';
                die;
            }
            if(!$oldSchool)
            {
                echo '旧学校不存在';die;
            }
            $this->oldEmailPost = $oldSchool['email'];
            echo $school['title'].' '.$school['email'].'<br/>';
            $this->debug = false;
            $this->daoUser = M('user');
            $this->end = $this->start;
    }

    public function sqlTest(){
        $s = time();
        $uidArr = M('weibo_follow')->where('uid=7 and type=0')->field('fid')->findAll();
        $uids = getSubByKey($uidArr, 'fid');
        $uids[] = 7;
        $map['isdel'] = 0;
        $map['uid'] = array('in', $uids);
        $weiboId = M('weibo')->where($map)->field('weibo_id')->order('weibo_id DESC')->limit(10)->findAll();
        $d = time()-$s;
        $dauer = date('i:s',time()-$s);
        echo ' '.$d.' '.$dauer.'<br/>';
        var_dump($weiboId);
    }

    public function deleteUser(){
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $failArray = array();
        $uids = array();
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $email = t($user[0]).$this->email_post;
                $uids[] = $this->daoUser->where("email='$email'")->getField('uid');
            }
        }
        D('User', 'home')->deleteUser($uids);
    }

    public function qrcode(){
        $this->assign('qrcode', '90003');
        $this->display();
    }

    public function findNoImport(){
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $failArray = array();
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $email = t($user[0]).$this->email_post;
                if(!$this->daoUser->where("email='$email'")->getField('uid')){
                    $fail++;
                    $line = $i+1;
                    $failArray[] = $line.' '.$email;
                }
            }
        }


        echo "done";
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($failArray);echo '<br/>';
    }

    public function getSid(){
        $map['pid'] = 0;
        $sdb = M('school')->where($map)->field('id, title')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $failArray = array();
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $sid1 = t($user[0]);
                if($school[$sid1]){
                    //$oldSchool[$sid1] = $school[$sid1];
                    echo $school[$sid1].',';
                }else{
                    $bad[$sid1] = 0;
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($bad);echo '<br/>';
    }

    public function checkSchool(){
        $map['pid'] = $this->sid;
        $sdb = M('school')->where($map)->field('id, title')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $failArray = array();
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $sid1 = t($user[2]);
                if($school[$sid1]){
                    $oldSchool[$sid1] = $sid1;
                }else{
                    $newSchool[$sid1] = $i+1;
                }
            }
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        echo '新院系: ';print_r($newSchool);echo '<br/>';
        echo '已存在: ';print_r($oldSchool);
    }

    public function checkGroupSchool(){
        $map['pid'] = $this->sid;
        $sdb = M('school')->where($map)->field('id, title')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/group/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $failArray = array();
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $sid1 = t($user[2]);
                if($school[$sid1]){
                    $oldSchool[$sid1] = $sid1;
                }else{
                    $newSchool[$sid1] = $i+1;
                }
            }
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($newSchool);echo '<br/>';
        print_r($oldSchool);
    }

    public function tongji(){
        $sql = 'SELECT SUM(a.credit) as credit,SUM(a.score) as score,SUM(a.joinCount) as joinCount,b.title FROM ts_event    AS a LEFT JOIN ts_school AS b ON  b.id=a.sid WHERE ( a.is_school_event = 1 ) AND    ( a.isDel = 0 ) GROUP BY a.sid';
        $res = M('')->query($sql);
            echo '<div style="width:600px;">';
            echo '<span style="width:250px;float:left;">';
            echo '学院';
            echo '</span>';
            echo '<span style="width:50px;float:left;">';
            echo '学分';
            echo '</span>';
            echo '<span style="width:50px;float:left;">';
            echo '积分';
            echo '</span>';
            echo '<span style="width:100px;float:left;">';
            echo '加入人数';
            echo '</span>';
            echo '</div>';
            echo '<br/>';
        foreach($res as $v){
            echo '<div style="width:600px;">';
            echo '<span style="width:250px;float:left;">';
            echo $v['title'].'&nbsp;';
            echo '</span>';
            echo '<span style="width:50px;float:left;">';
            echo $v['credit'];
            echo '</span>';
            echo '<span style="width:50px;float:left;">';
            echo $v['score'];
            echo '</span>';
            echo '<span style="width:100px;float:left;">';
            echo $v['joinCount'];
            echo '</span>';
            echo '</div>';
            echo '<br/>';
        }
        die;
    }

    public function sendsms(){
//        $dao = M('citys');
//        $data['city'] = '各市团市委';
//        $data['short'] = '2';
//        $dao->add($data);
//        die;
//        $res = service('Sms')->sendsms('13862125365','亲爱的PocketUni用户,有新的活动"校园歌手大赛"等待您的审核');
//        var_dump($res);die;
        //var_dump(file_get_contents('http://sms.c8686.com/Api/BayouSmsApiEx.aspx'));die;

//        $name = 'tiangongwangluo';
//        $pass = base64_encode('888888');
//        require_once(SITE_PATH . '/addons/libs/LiangpingSmsSender.php');
//        $sender=new LiangpingSmsSender();
//        $mobile = '13771358171';
//        for($i=1;$i<2;$i++){
//            $msg= '亲爱的PocketUni用户，您的验证码为：测试下。请您尽快完成验证。';
//            $result = $sender->sendsms($name,$pass,$mobile, $msg);
//            var_dump($result);
//        }
//        die;
        require_once(SITE_PATH . '/addons/libs/BayouSmsSender.php');
        $mobile = '13771358171';
        $sender=new BayouSmsSender();
        $msg = '亲爱的PocketUni用户，您的验证码为：1234。请您尽快完成验证。';
        $pass = md5("113314446");
        $result = $sender->sendsms("636978", $pass, $mobile, $msg);
        var_dump($result);die;
    }
    //学号0|姓名1|院系2|年级3|专业4|班级5|性别6|手机号码7|密码8
    private function _checkFirstLine($arr){
        $firstLine = explode(",", $arr);
        $soll = array('学号','姓名','院系','年级','专业','班级','性别','手机号码','密码');
        $cnt = count($soll);
        if(count($firstLine)<$cnt){
            $this->error = '第一列必须是 学号 姓名 院系 年级 专业 班级 性别 手机号码 密码';
            return false;
        }
        //csv第一列和最后一列，特殊判断
        $firstLine[0] = substr($firstLine[0], 3); //第一列从第三字节开始
        $lastKey = $cnt-1;
        $lastLen = strlen($soll[$lastKey]);
        $firstLine[$lastKey] = substr($firstLine[$lastKey],0,$lastLen);//最后列去掉尾部换行之类多余
        for($i=0;$i<$cnt;$i++){
            $is = t($firstLine[$i]);
            if($is != $soll[$i]){
                $hang = $i+1;
                $this->error = '第'.$hang.'列错误，'.$firstLine[$i].' 应该是 '.$soll[$i];
                return false;
            }
        }
        return true;
    }

    private function _checkGroupLine($arr){
        $firstLine = explode(",", $arr);
        $soll = array('部落名称','种类','院系','年级','分类','姓名','学号','手机','职位','最高权限');
        if(count($firstLine)<10){
            $this->error = '至少10列 部落名称,种类,院系,年级,分类,姓名,学号,手机,职位,最高权限';
            return false;
        }
        if($firstLine[0] != $soll[0]){
            $noBom = substr($firstLine[0], 3);
            if($noBom != $soll[0]){
                $this->error = '第1列错误，'.$firstLine[0].' 不是 '.$soll[0];
                return false;
            }
        }
        $firstLine[9] = substr($firstLine[9],0,12);
        for($i=1;$i<=9;$i++){
            if($firstLine[$i] != $soll[$i]){
                $hang = $i+1;
                $this->error = '第'.$hang.'列错误，'.$firstLine[$i].' 不是 '.$soll[$i];
                return false;
            }
        }
        return true;
    }
    //学号0|姓名1|院系2|年级3|专业4|班级5|性别6|手机号码7|密码8
    public function addUser() {
        $go = intval($_REQUEST['go']);
        if ($go <= 0) {
            return;
        }
        $map['pid'] = $this->sid;
        $sdb = M('school')->where($map)->field('id, title')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        $cArray = array();

        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $failArray = array();
        if(!$this->_checkFirstLine($cArray[0][0])){
            echo $this->error;
            die;
        }

        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $email = t($user[0]).$this->email_post;
                $data['realname'] = t($user[1]);
                $data['realname'] = str_replace(' ', '', $data['realname']);
                $data['uname'] = $data['realname'].t($user[0]);
                $data['sid']     = $this->sid;
                $sid1 = t($user[2]);
                if($sid1){
                    if($school[$sid1]){
                        $data['sid1'] = $school[$sid1];
                    }else{
                        $mapSchool['title'] = $sid1;
                        $mapSchool['pid'] = $this->sid;
                        $banId = M('school')->add($mapSchool);
                        $data['sid1'] = $banId;
                        $school[$sid1] = $banId;
                    }
                    $data['year'] = t($user[3]);
                    $data['major'] = t($user[4]);
                    $data['sex'] = (t($user[6]) == '女')?0:1;
		    //改为1，即导入的数据为初始化
		    $data['is_init'] = 0; 
		    //非学校官方数据要修改的
		    //$data['from_reg'] = 1;
                    //有发起活动权限，将下面的注释//去掉
                    //$data['can_add_event'] = 1;
                    $mobile = t($user[7]);
                    if($mobile){
                        $data['mobile'] = $mobile;
                    }
                    $pass = t($user[8]);
                    if ($pass) {
                        $data['password'] = codePass($pass);
                    }
                    $uid = $this->_doAddUser($email,$data);
                }else{
                    $uid = 0;
                }
                if ($uid) {
                    $class = t($user[5]);
                    if($class){
                        Model('UserA')->addUserA($uid,array('class'=>$class));
                    }
                    $suc++;
                } else {
                    $fail++;
                    //$failArray[] = $email.' '.$data['realname'].' '.$data['sid1'].' '.$data['year'].' '.$data['major'].' '.$data['sex'].' '.$data['mobile'].'<br/>';
                    $failArray[] = $email.'<br/>';
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($failArray);
    }
    // 根据学号修改学生信息
    //学号0|姓名1|院系2|年级3|专业4|班级5|性别6|手机号码7|密码8
    public function updateUser() {
        $map['pid'] = $this->sid;
        $sdb = M('school')->where($map)->field('id, title')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        $cArray = array();

        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        if(!$this->_checkFirstLine($cArray[0][0])){
            echo $this->error;
            die;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $number = t($user[0]);
                $email = $number.$this->email_post;
                $uid = $this->daoUser->getField('uid', "email='".$email."'");
                if(!$uid){
                    $ret = false;
                    $fail++;
                    $this->failArray[] = $number.'<br/>';
                }else{
                    /*真实姓名*/
                    $data['realname'] = t($user[1]);
                    $data['realname'] = str_replace(' ', '', $data['realname']);
                    /*昵称*/
        //                $data['uname'] = $data['realname'].$number;
                    /*学校*/
                    $data['sid']     = $this->sid;
                     /*院系*/
                    $sid1 = t($user[2]);
                    if($sid1){
                        if($school[$sid1]){
                            $data['sid1'] = $school[$sid1];
                        }else{
                            $mapSchool['title'] = $sid1;
                            $mapSchool['pid'] = $this->sid;
                            $banId = M('school')->add($mapSchool);
                            $data['sid1'] = $banId;
                            $school[$sid1] = $banId;
                        }
                    }
                    /*年级*/
                    $data['year'] = t($user[3]);
                    if(strlen($data['year'])==4){
                        $data['year'] = substr($data['year'], 2);
                    }
                    /*专业*/
                    $data['major'] = t($user[4]);
                    /*班级*/
                    $class = t($user[5]);
                    if($class){
                        Model('UserA')->addUserA($uid,array('class'=>$class));
                    }
                    /*性别*/
                    $xb = t($user[6]);
                    if($xb){
                        $data['sex'] = ($xb == '女')?0:1;
                    }
                    /*手机号码7*/
                    $mobile = t($user[7]);
                    if($mobile){
                        $data['mobile'] = $mobile;
                    }
                    /*密码8*/
                    $pass = t($user[8]);
                    if ($pass) {
                        $data['password'] = codePass($pass);
                    }

                    $ret = $this->_upUser($email, $data);
                    S('S_userInfo_'.$uid, null);
                    $suc++;
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }

    /**
     * 按照原先的学生信息进行学校和院系的变更
     */
    // 根据学号修改学生信息
    //学号0|姓名1|院系2|年级3|专业4|班级5|性别6|手机号码7|密码8
    public function updateUserChangeSchool() {
        $map['pid'] = $this->sid;
        $sdb = M('school')->where($map)->field('id, title')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        $cArray = array();

        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }

        if(!$this->_checkFirstLine($cArray[0][0])){
            echo $this->error;
            die;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $number = t($user[0]);
                $oldEmail = $number.$this->oldEmailPost;
                $data['email'] = $number.$this->email_post;
                $uid = $this->daoUser->getField('uid', "email='".$oldEmail."'");
                if(!$uid){
                    $ret = false;
                    $fail++;
                    $this->failArray[] = $number.'<br/>';
                }else{
                    /*真实姓名*/
                    $data['realname'] = t($user[1]);
                    $data['realname'] = str_replace(' ', '', $data['realname']);
                    /*昵称*/
                    //                $data['uname'] = $data['realname'].$number;
                    /*学校*/
                    $data['sid']     = $this->sid;
                    /*院系*/
                    $sid1 = t($user[2]);
                    if($sid1){
                        if($school[$sid1]){
                            $data['sid1'] = $school[$sid1];
                        }else{
                            $mapSchool['title'] = $sid1;
                            $mapSchool['pid'] = $this->sid;
                            $banId = M('school')->add($mapSchool);
                            $data['sid1'] = $banId;
                            $school[$sid1] = $banId;
                        }
                    }
                    /*年级*/
                    $data['year'] = t($user[3]);
                    if(strlen($data['year'])==4){
                        $data['year'] = substr($data['year'], 2);
                    }
                    /*专业*/
                    $data['major'] = t($user[4]);
                    /*班级*/
                    $class = t($user[5]);
                    if($class){
                        Model('UserA')->addUserA($uid,array('class'=>$class));
                    }
                    /*性别*/
                    $xb = t($user[6]);
                    if($xb){
                        $data['sex'] = ($xb == '女')?0:1;
                    }
                    /*手机号码7*/
                    $mobile = t($user[7]);
                    if($mobile){
                        $data['mobile'] = $mobile;
                    }
                    /*密码8*/
                    $pass = t($user[8]);
                    if ($pass) {
                        $data['password'] = codePass($pass);
                    }
                    $ret = $this->_upUserById($uid, $data);
                    S('S_userInfo_'.$uid, null);
                    $suc++;
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }

    /**
     * 按照UID去更新学生信息
     * @param $uid
     * @param $data
     * @return mixed
     */
    private function _upUserById($uid, $data)
    {
        $map['uid'] = $uid;
        return $this->daoUser->where($map)->save($data);
    }

    //更新某id段的学生信息
    public function upUid() {
        $uidsI = 0;
        $uids = $this->daoUser->field('uid')->where('sid=99999')->order('uid asc')->findAll();
        $map['pid'] = $this->sid;
        $sdb = M('school')->where($map)->field('id, title')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $tt = 1;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $email = t($user[0]).$this->email_post;
                $data['email'] = $email;
                $data['realname'] = t($user[1]);
                $data['realname'] = str_replace(' ', '', $data['realname']);
                $data['uname'] = $data['realname'].t($user[0]);
                $data['sid']     = $this->sid;
                $sid1 = t($user[2]);
                if($sid1){
                    if($school[$sid1]){
                        $data['sid1'] = $school[$sid1];
                    }else{
                        $mapSchool['title'] = $sid1;
                        $mapSchool['pid'] = $this->sid;
                        $banId = M('school')->add($mapSchool);
                        $data['sid1'] = $banId;
                        $school[$sid1] = $banId;
                    }
                    $data['year'] = t($user[3]);
                    $data['major'] = t($user[4]);
                    $data['sex'] = (t($user[5]) == '女')?0:1;
                    $data['password']  = '53dfac50ed707f67bba6c4161c0c947f5e5378e3';
                    $data['is_valid'] = 1;
                    $data['is_init'] = 0;
                    $data['mobile'] = '';
                    if(isset($user[6])){
                        $data['mobile'] = t($user[6]);
                    }
                    $data['email2'] = '';
                    $data['ctime'] = time();
                    if(!isset($uids[$uidsI])){
                        die('uid用完');
                    }
                    $uid = $uids[$uidsI]['uid'];
                    $ret = $this->daoUser->where("uid=$uid")->save($data);
                }else{
                    $ret = false;
                }
                if ($ret) {
                    $uidsI++;
                    $suc++;
                } else {
                    //var_dump($this->daoUser->getLastSql());die;
                    $fail++;
                    $this->failArray[] = $email.'<br/>';
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>uid - " . $uid;
        echo "<br>";
        print_r($this->failArray);
    }

    public function upd() {
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $email = t($user[0]).$this->email_post;
                $data['realname'] = t($user[1]);
                $data['realname'] = str_replace(' ', '', $data['realname']);
                $data['sex'] = (t($user[2]) == '女')?0:1;
                $map['email'] = $email;
                //$ret = $this->daoUser->where($map)->save($data);
                $ret = $this->daoUser->where($map)->field('uid')->find();
                if ($ret) {
                    $suc++;
                } else {
                    $fail++;
                    $this->failArray[] = t($user[0]).'<br/>';
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        foreach ($this->failArray as $v) {
            echo $v;
        }
    }

    private function _upUser($email, $data){
//        if(!$data['uname']||!$data['sid1']){
//            return false;
//        }
        $map['email'] = $email;
        //unset($data['uname']);
        //unset($data['realname']);
        //unset($data['sex']);
        //$data['ctime']	   = time();
        return $this->daoUser->where($map)->save($data);
    }

    private function _doAddUser($email, $data){
        $data['email']     = $email;
        if (!isEmailAvailable($email)) {
            return false;
        }
        if(!isset($data['password'])){
//            $password = "111111";
//            $data['password']  = codePass($password);
            $data['password'] = '53dfac50ed707f67bba6c4161c0c947f5e5378e3';
        }
        $data['ctime']	   = time();
        $data['is_active'] = 1;
	if(!isset($data['is_init'])){
		$data['is_init']  = 0;
        }
        
        $data['is_valid']  = 1;
        if (!($uid = $this->daoUser->add($data)))
                return false;
        // 默认关注的好友
//        if(!$this->daoFollow){
//            $this->daoFollow = M('weibo_follow');
//        }
//        $fData['uid']  = $uid;
//        $fData2['uid']  = 1;
//        $fData['fid']  = 1;
//        $fData2['fid']  = $uid;
//        $fData['type'] = 0;
//        $fData2['type'] = 0;
//        $this->daoFollow->add($fData);
//        $this->daoFollow->add($fData2);

        //注册成功 初始积分
        if(!$this->daoCredit){
            $this->daoCredit = M('credit_user');
        }
        $sData['uid'] = $uid;
        $sData['score'] = $this->credit;
        $this->daoCredit->add($sData);

        return $uid;


    }
    private function _doAddTzbUser($email, $data){
        $data['email']     = $email;
        if (!isEmailAvailable($email)) {
            return false;
        }
        if(!isset($data['password'])){
//            $password = "111111";
//            $data['password']  = codePass($password);
            $data['password']  = '53dfac50ed707f67bba6c4161c0c947f5e5378e3';
        }
        $data['ctime']	   = time();
        $data['is_active'] = 1;
        $data['is_init']  = 1;
        if (!($uid = $this->daoUser->add($data)))
                return false;

        //注册成功 初始积分
        if(!$this->daoCredit){
            $this->daoCredit = M('credit_user');
        }
        $sData['uid'] = $uid;
        $sData['score'] = $this->credit;
        $this->daoCredit->add($sData);
        return true;
    }

    public function index(){

		$go   = intval($_REQUEST['go']);
		if($go<=0) {
			return;
		}

		$debug = $this->debug;
		$start = $this->start;
		$end = $this->end;

		set_time_limit(0);
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

		$cArray = array();

		for($i=$start;$i<=$end;$i++) {
			$file = APPS_PATH.'/newcomer/Appinfo/'.$i.".csv";
			$users = file_get_contents($file);
			$aArray = explode("\n", $users);
			$cArray[] = $aArray;
		}

		$suc = 0;
		$fail = 0;
		$failArray = array();
$ban = '';
$banId = 0;
		for($j=0;$j<count($cArray);$j++) {
			for($i=1;$i<count($cArray[$j]);$i++) {

				//echo $i;
				$user = explode(",", $cArray[$j][$i]);
				$studentno  = $user[0];
				$name  = $user[1];
				$sid1  = $user[2];
				$sid2  = $user[3];
$user[4] = t($user[4]);
if($user[4] && $ban!=$user[4]){
    $map['title'] = $user[4];
    $map['pid'] = $sid2;
    $newId = M('school')->where($map)->find();
    if($newId){
        $banId = $newId['id'];
    }else{
        $banId = M('school')->add($map);
    }
    $ban = $user[4];
}
				$sid3  = $banId;
				//$department  = $user[2];

				$ret = $this->doRegister($studentno, $name, $sid1,$sid2,$sid3);
				if($ret) {
					$suc++;
				} else {
					$fail++;
					$failArray[] = $studentno.' '.$name;
				}
				if($debug) break;
			}
			if($debug) break;
		}


		echo "done";
		echo "<br>suc - ".$suc;
		echo "<br>fail - ".$fail;
		echo "<br>";
		print_r($failArray);

    }

	public function doRegister($studentno, $name, $sid1,$sid2,$sid3)
	{

		if(empty($studentno) || empty($name) ) {
			//echo "a";
			return false;
		}

		$email = $studentno.$this->email_post;
		$password = "111111";
		$sex = 1;
		$need_email_activate = 0;


		// 注册
		$data['sid']     = $this->sid;
		$data['sid1']     = $sid1;
		$data['sid2']     = $sid2;
		$data['sid3']     = $sid3;
		$data['email']     = $email;
		$data['password']  = codePass($password);
		$data['uname']	   = t($name);
		$data['ctime']	   = time();
		$data['is_active'] = $need_email_activate ? 0 : 1;

		$data['sex']   	  = $sex;
		$data['is_init']  = 1;

		if (!($uid = D('User', 'home')->add($data)))
			return false;

		// 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
		$user_log = array(
			'uid'		=> $uid,
			'action'	=> 'add',
			'type'		=> '0',
			'dateline'	=> time(),
		);
		M('myop_userlog')->add($user_log);

		// 同步至UCenter
		if (UC_SYNC) {
			$uc_uid = uc_user_register($data['nickname'],$data['password'],$data['email']);
			//echo uc_user_synlogin($uc_uid);
			if ($uc_uid > 0)
				ts_add_ucenter_user_ref($uid,$uc_uid,$data['uname']);
		}

		if ($need_email_activate == 1) { // 邮件激活
			$this->activate($uid, $data['email'], $invite_code);
		} else {
			// 置为已登陆, 供完善个人资料时使用
			//service('Passport')->loginLocal($uid);

	    	$dao = D('Follow','weibo');

	        // 默认关注的好友
			$auto_freind = model('Xdata')->lget('register');
			$auto_freind['register_auto_friend'] = explode(',', $auto_freind['register_auto_friend']);
			foreach($auto_freind['register_auto_friend'] as $v) {
				if (($v = intval($v)) <= 0)
					continue ;
				$dao->dofollow($v, $uid);
				$dao->dofollow($uid, $v);
			}

			// 开通个人空间
			$data['uid'] = $uid;
			model('Space')->add($data);

			//注册成功 初始积分
			X('Credit')->setUserCredit($uid,'init_default');

			$puser = D('UserProfile', 'home');
			$puser->uid = $uid;
			$puser->upintro();

			return true;

		}

		return false;
	}
public function sid(){

		$go   = intval($_REQUEST['go']);
		if($go<=0) {
			return;
		}

		$debug = $this->debug;
		$start = $this->start;
		$end = $this->end;

		set_time_limit(0);
		echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

		$cArray = array();

		for($i=$start;$i<=$end;$i++) {
			$file = APPS_PATH.'/newcomer/Appinfo/'.$i.".csv";
			$users = file_get_contents($file);
			$aArray = explode("\n", $users);
			$cArray[] = $aArray;
		}

		$suc = 0;
		$fail = 0;
		$failArray = array();
$ban = '';
$banId = 0;
		for($j=0;$j<count($cArray);$j++) {
			for($i=1;$i<count($cArray[$j]);$i++) {

				//echo $i;
				$user = explode(",", $cArray[$j][$i]);
				$studentno  = $user[0];
				$name  = $user[1];
				$sid1  = $user[2];
				$sid2  = $user[3];
if($user[4] && $ban!=$user[4]){
    $map['title'] = $user[4];
    $map['pid'] = $sid2;
    $newId = M('school')->where($map)->find();
    if($newId){
        $banId = $newId['id'];
    }else{
        $banId = 0;
    }
    $ban = $user[4];
}
				$sid3  = $banId;
				//$department  = $user[2];

				$ret = $this->doSid($studentno, $sid1,$sid2,$sid3);
				if($ret) {
					$suc++;
				} else {
					$fail++;
					$failArray[] = $name;
				}
				if($debug) break;
			}
			if($debug) break;
		}


		echo "done";
		echo "<br>suc - ".$suc;
		echo "<br>fail - ".$fail;
		echo "<br>";
		print_r($failArray);

    }

    public function doSid($studentno, $sid1,$sid2,$sid3)
    {
            if(empty($studentno)) {
                    //echo "a";
                    return false;
            }
            $email = $studentno.$this->email_post;
            // 注册
            $data['sid1']     = $sid1;
            $data['sid2']     = $sid2;
            $data['sid3']     = $sid3;
            $map['email']     = $email;

            if (!($uid = D('User', 'home')->where($map)->save($data)))
                    return false;

            return true;
    }

    //搜索用户
    public function getUser(){
        $go = intval($_REQUEST['go']);
        if ($go <= 0) {
            return;
        }
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        $cArray = array();

        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $failArray = array();
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $email = t($user[0]).$this->email_post;
                $map['email'] = $email;
                $map['sid1'] = 282;
                $ret = $this->daoUser->where($map)->field('uid,sid1')->find();
                if ($ret) {
                    $suc++;
                } else {
                    $fail++;
                    $failArray[] = $email.'<br/>';
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($failArray);
    }


    //添加学校院系
    public function school(){
        $go = intval($_REQUEST['go']);
        if ($go <= 0) {
            return;
        }

        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        $cArray = array();

        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }

        $suc = 0;
        $fail = 0;
        $failArray = array();
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                //echo $i;
                $input = explode(",", $cArray[$j][$i]);

                $sid1 = $this->getSchoolId($input[0],$this->sid);
                $sid2 = $this->getSchoolId($input[1],$sid1);
                $this->getSchoolId($input[2],$sid2);

                $ret = true;
                if ($ret) {
                    $suc++;
                } else {
                    $fail++;
                    $failArray[] = $name;
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($failArray);
    }

    private function getSchoolId($title,$pid){
        $title = t($title);
        if($title == ''){
            return false;
        }
        $dao = M('school');
        $map['title'] = $title;
        $map['pid'] = $pid;
        $school1 = $dao->where($map)->field('id')->find();
        if (!$school1) {
            return $dao->add($map);
        } else {
            return $school1['id'];
        }
    }

    //批量导入社团
    //1.没有就加权限 2.写入eventgroup表
    public function addGroup() {
        $go = intval($_REQUEST['go']);
        if ($go <= 0) {
            return;
        }
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        $cArray = array();

        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/group/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        if(!$this->_checkGroupLine($cArray[0][0])){
            echo $this->error;
            die;
        }
        $map['pid'] = $this->sid;
        $sdb = M('school')->where($map)->field('id, title')->findAll();
        $school['校级'] = -1;
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        $category = array('学生部门'=>1,'团支部'=>2,'学生社团'=>3);
        $cdb = M('group_category')->field('id,title')->findAll();
        foreach($cdb as $v){
            $cat0[$v['title']] = $v['id'];
        }
        $suc = 0;
        $fail = 0;
        $failArray = array();
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $line = $i+1;
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $data['name'] = t($user[0]);
                $va = t($user[1]);
                $data['category'] = $category[$va];
                $sid1 = t($user[2]);
                if($sid1 && $school[$sid1]){
                    $data['sid1'] = $school[$sid1];
                    $data['year'] = t($user[3]);
                    $va = t($user[4]);
                    if($va){
                        if($va=='其它'){
                            $va = '其他';
                        }elseif(!strpos($va, '类')){
                            $va .= '类';
                        }
                    }
                    if($va && $cat0[$va]){
                        $data['cid0'] = $cat0[$va];
                    }
                    $num = t($user[6]);
                    $mobile = t($user[7]);
                    $uid = $this->_getUid($num.$this->email_post,$mobile);
                    if($uid){
                        $data['uid'] = $uid;
                        $ret = $this->_doAddGroup($data,$line);
                    }else{
                        $this->failArray[] = $line.'行 没找到学生 '.t($user[5]).' '.$num.' '.$data['name'].' <br/>';
                        $ret = false;
                    }
                    //}
                }else{
                    $this->failArray[] = $line.' 无院系 '.$data['name'].'<br/>';
                    $ret = false;
                }
                if ($ret) {
                    $suc++;
                } else {
                    $fail++;
                    //$failArray[] = $email.' '.$data['realname'].' '.$data['sid1'].' '.$data['year'].' '.$data['major'].' '.$data['sex'].' '.$data['mobile'].'<br/>';
                    //$failArray[] = $i.' '.$data['name'].'<br/>';
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }

    private function _doAddGroup($data,$i){
        if(!$data['name'] || !$data['category'] || !$data['sid1'] || !$data['uid']){
            $this->failArray[] = $i.' 资料不全 '.$data['name'].' 种类：'.$data['category'].' 院系：'.$data['sid1'].' 用户：'.$data['uid'].'<br/>';
            return false;
        }
        $data['school'] = $this->sid;
        $data['vStatus'] = 1;
        $data['whoDownloadFile'] = 3;
        $data['need_invite'] = 1;
        $data['vStern'] = 0;
        $data['ctime'] = time();
        $uid = $data['uid'];
        $dao = D('EventGroup','event');
        $gmap['name'] = $data['name'];
        $gmap['school'] = $this->sid;
        $group = $dao->where($gmap)->field('id,membercount')->find();
        $gid=0;
        if($group){
            $gid = $group['id'];
        }
//        $gid = $dao->where($gmap)->getField('id');
        //部落已存在
        if($gid){
            $hasUser = M('group_member')->where("uid= $uid AND gid=$gid")->getField('id');
            if(!$hasUser){
                $level = 2;
                if($group['membercount']<1){
                    $level = 1;
                }
                if($dao->joinGroup($uid, $gid, $level, true)){
                    //加入到队列：用户加入部落
                    $rongyun_group['groupId']   = $gid;
                    $rongyun_group['groupName'] = $data['name'];
                    $rongyun_group['userId']    = $uid;
                    $rongyun_data['do_action']  = json_encode(array('Rongyun','joinTribeGroup'));//用户加入部落
                    $rongyun_data['param']      = json_encode($rongyun_group);
                    $rongyun_data['create_time']= time();
                    $rongyun_data['next_time']  = time();
                    model('Scheduler')->addToRongyun($rongyun_data);
                    if(!$this->daoEventGroup){
                        $this->daoEventGroup = M('event_group');
                    }
                    $data['gid'] = $gid;
                    $data['uid'] = $uid;
                    $this->daoEventGroup->add($data);
                    $this->daoUser->where('uid =' . $uid)->setField('can_add_event', 1);
                    if($level==1){
                        $dao->setField('uid', $uid, 'id='.$gid);
                    }
                    return true;
                }else{
                    $this->failArray[] = $i.' 部落已存在，成员加入失败 '.$data['name'].'<br/>';
                    return false;
                }
            }
            //部落存在，用户已加入
            return true;
        }
        //添加部落
        $gid = M('group')->add($data);

        //新加部落,第一个人为社长
        if($gid){
            //加入到队列
            $rongyun_group['groupName'] = $data['name'];
            $rongyun_group['userId']    = $data['uid'];
            $rongyun_group['groupId']        = $gid;
            $rongyun_data['do_action']  = json_encode(array('Rongyun','createTribeGroup'));
            $rongyun_data['param']      = json_encode($rongyun_group);
            $rongyun_data['create_time']= time();
            $rongyun_data['next_time']  = time();   //立即执行
            model('Scheduler')->addToRongyun($rongyun_data);
            //M('RongyunGroup')->add();
            if($dao->joinGroup($uid, $gid, 1, true)){
                return $gid;
            }
        }
        $this->failArray[] = $i.' 添加部落失败 '.$data['name'].'<br/>';
        return false;
    }

    /**
     * 导入社团的时候顺便导入成员数据
     *
     * 其中需要在原先的csv文件的基础上再增加一个字段为level
     *
     * level的字段含义：
     *  1 社长 2 管理员 3 普通成员
     */
    //批量导入社团
    //1.没有就加权限 2.写入eventgroup表
    public function addGroupNew() {
        $go = intval($_REQUEST['go']);
        if ($go <= 0) {
            return;
        }
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        $cArray = array();

        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/group/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        if(!$this->_checkGroupLine($cArray[0][0])){
            echo $this->error;
            die;
        }
        $map['pid'] = $this->sid;
        $sdb = M('school')->where($map)->field('id, title')->findAll();
        $school['校级'] = -1;
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        $category = array('学生部门'=>1,'团支部'=>2,'学生社团'=>3);
        $cdb = M('group_category')->field('id,title')->findAll();
        foreach($cdb as $v){
            $cat0[$v['title']] = $v['id'];
        }
        $suc = 0;
        $fail = 0;
        $this->failArray = array();
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $line = $i+1;
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $data['name'] = t($user[0]);
                $va = t($user[1]);
                $data['category'] = $category[$va];
                $sid1 = t($user[2]);
                if($sid1 && $school[$sid1]){
                    $data['sid1'] = $school[$sid1];
                    $data['year'] = t($user[3]);
                    $va = t($user[4]);
                    if($va){
                        if($va=='其它'){
                            $va = '其他';
                        }elseif(!strpos($va, '类')){
                            $va .= '类';
                        }
                    }
                    if($va && $cat0[$va]){
                        $data['cid0'] = $cat0[$va];
                    }
                    $num = t($user[6]);
                    $mobile = t($user[7]);
                    $data['remark'] = t($user[8]);
                    $level = t($user[9]); // 部落中成员级别 1 社长 2 管理员 3 普通成员
                    $uid = $this->_getUid($num.$this->email_post,$mobile);
                    if($uid){
                        $data['uid'] = $uid;
                        $ret = $this->_doAddGroupNew($data,$line,$level);
                    }else{
                        $this->failArray[] = $line.'行 没找到学生 '.t($user[5]).' '.$num.' '.$data['name'].' <br/>';
                        $ret = false;
                    }
                }else{
                    $this->failArray[] = $line.' 无院系 '.$data['name'].'<br/>';
                    $ret = false;
                }
                if ($ret) {
                    $suc++;
                } else {
                    $fail++;
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }

    /**
     * 配合addGroupNew的成员数据导入处理
     *
     * @param $data
     * @param $i
     * @param $level
     * @return bool|int|mixed
     */
    private function _doAddGroupNew($data,$i,$level){
        if(!$data['name'] || !$data['category'] || !$data['sid1'] || !$data['uid']){
            $this->failArray[] = $i.' 资料不全 '.$data['name'].' 种类：'.$data['category'].' 院系：'.$data['sid1'].' 用户：'.$data['uid'].'<br/>';
            return false;
        }
        $data['school'] = $this->sid;
        $data['vStatus'] = 1;
        $data['whoDownloadFile'] = 3;
        $data['need_invite'] = 1;
        $data['vStern'] = 0;
        $data['ctime'] = time();
        $uid = $data['uid'];
        $dao = D('EventGroup','event');
        $gmap['name'] = $data['name'];
        $gmap['school'] = $this->sid;
        $group = $dao->where($gmap)->field('id,membercount')->find();
        $gid=0;
        if($group)
        {
            $gid = $group['id'];
        }
        else
        {
            //添加部落
            $gid = M('group')->add($data);
        }

        //新加部落,第一个人为社长
        if($gid && $level == 1){
            //加入到队列
            $rongyun_group['groupName'] = $data['name'];
            $rongyun_group['userId']    = $data['uid'];
            $rongyun_group['groupId']        = $gid;
            $rongyun_data['do_action']  = json_encode(array('Rongyun','createTribeGroup'));
            $rongyun_data['param']      = json_encode($rongyun_group);
            $rongyun_data['create_time']= time();
            $rongyun_data['next_time']  = time();   //立即执行
            M('Scheduler')->add($rongyun_data);
            if($dao->joinGroup($uid, $gid, $level, true)){
                return $gid;
            }
        }

        //部落已存在
        if($gid){
            $hasUser = M('group_member')->where("uid= $uid AND gid=$gid")->getField('id');
            if(!$hasUser){
                if($dao->joinGroup($uid, $gid, $level, true)){
                    //加入到队列：用户加入部落
                    $rongyun_group['groupId']   = $gid;
                    $rongyun_group['groupName'] = $data['name'];
                    $rongyun_group['userId']    = $uid;
                    $rongyun_data['do_action']  = json_encode(array('Rongyun','joinTribeGroup'));//用户加入部落
                    $rongyun_data['param']      = json_encode($rongyun_group);
                    $rongyun_data['create_time']= time();
                    $rongyun_data['next_time']  = time();
                    M('Scheduler')->add($rongyun_data);
                    if(!$this->daoEventGroup){
                        $this->daoEventGroup = M('event_group');
                    }
                    $data['gid'] = $gid;
                    $data['uid'] = $uid;
                    $this->daoEventGroup->add($data);
                    $this->daoUser->where('uid =' . $uid)->setField('can_add_event', 1);
                    if($level==1 || $level == 2){
                        $dao->setField('uid', $uid, 'id='.$gid);
                    }
                    return true;
                }else{
                    $this->failArray[] = $i.' 部落已存在，成员加入失败 '.$data['name'].'<br/>';
                    return false;
                }
            }
            //部落存在，用户已加入
            return true;
        }
        $this->failArray[] = $i.' 添加部落失败 '.$data['name'].'<br/>';
        return false;
    }


    private function _getUid($email,$mobile=''){
        $map['email'] = $email;
        $user = $this->daoUser->where($map)->field('uid, mobile')->find();
        if($user){
            if($mobile && !$user['mobile']){
                $data['mobile'] = $mobile;
                $this->daoUser->where($map)->save($data);
            }
            return $user['uid'];
        }
        return false;
    }

    public function analyseWeibo() {
          $db_prefix = C('DB_PREFIX');
          $map['a.isdel'] = 0;
        $list = M('weibo')->table("{$db_prefix}weibo AS a ")
                        ->join("{$db_prefix}user AS b ON  b.uid=a.uid")
                        ->group('b.sid')
                        ->field('COUNT(*) as num,b.sid')
                        ->where($map)
                        ->findAll();
                            echo '<table>';
                            echo '<tr>';
                            echo '<td>学校</td><td>微博数</td>';
                               echo '</tr>';
                        foreach($list as $v){
                              echo '<tr>';
                            echo '<td>'.tsGetSchoolName($v['sid']).'</td><td>'.$v['num'].'</td>';
                               echo '</tr>';
                        }

                            echo '</table>';
     }


     public function changeEmail() {
        $go = intval($_REQUEST['go']);
        if ($go <= 0) {
            return;
        }

        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        $cArray = array();

        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $failArray = array();
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $map['email'] = t($user[0]).$this->email_post;
                $data['email'] = t($user[1]).$this->email_post;
                $ret = $this->daoUser->where($map)->save($data);

                if ($ret) {
                    $suc++;
                } else {
                    $fail++;
                    //$failArray[] = $email.' '.$data['realname'].' '.$data['sid1'].' '.$data['year'].' '.$data['major'].' '.$data['sex'].' '.$data['mobile'].'<br/>';
                    $failArray[] = $map['email'].'<br/>';
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($failArray);
    }

    //部落社长发起部落活动权限
  public function addGroupEvent() {
      //1.没有就加权限 2.写入eventgroup表
//      $gid = M('group')->where('school = 12823')->field('id')->select();
//      foreach($gid as $v)
//      {
//          $gidArr[] = $v['id'];
//      }
//      $uid = M('group_member')->where('level IN (1,2) and gid IN ('.join(',',$gidArr).')')->field('uid')->select();
//      foreach($uid as $v)
//      {
//          $uidArr[] = $v['uid'];
//      }
//      M('')->query('update ts_user set can_add_event = 1 WHERE uid IN ('.join(',',$uidArr).')');
//      die;
      $result=M('group_member')->where('level=1')->field('gid,uid')->findAll();
      $daoEventGroup = M('event_group');
      foreach($result as $v){
          $data['gid'] = $v['gid'];
          $data['uid'] = $v['uid'];
          $daoEventGroup->add($data);
          $this->daoUser->where('uid =' . $v['uid'])->setField('can_add_event', 1);
      }
  }

  public function xz(){
      echo 'xx';
      die;
      var_dump($res);die;



      $dao = M('medal');
      $medal = $dao->field('medal_id,data')->findAll();
      foreach ($medal as $value) {
          $arr = unserialize($value['data']);
          if(isset($arr['icon_url'])){
            $arr['icon_url'] = str_replace('2012.xyhui.com', 'pocketuni.net', $arr['icon_url']);
          }
          $arr['alert_message'] = str_replace('2012.xyhui.com', 'pocketuni.net', $arr['alert_message']);
          $c = serialize($arr);
          $data = array('data'=>$c);
          $dao->where('medal_id='.$value['medal_id'])->save($data);
      }
      $dao = M('user_medal');
      $medal = $dao->field('user_medal_id,data')->findAll();
      foreach ($medal as $value) {
          $arr = unserialize($value['data']);
          $arr['alert_message'] = str_replace('2012.xyhui.com', 'pocketuni.net', $arr['alert_message']);
          $c = serialize($arr);
          $data = array('data'=>$c);
          $dao->where('user_medal_id='.$value['user_medal_id'])->save($data);
      }
  }

  public function addTzbGroup() {
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        $cArray = array();

        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/group/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $map['pid'] = $this->sid;
        $sdb = M('school')->where($map)->field('id, title')->findAll();
        $school['校级'] = -1;
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $line = $i+1;
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $data['name'] = t($user[0]);
                $data['intro'] = t($user[3]);
                $data['category'] = 1;
                $sid1 = t($user[1]);
                if($sid1 && $school[$sid1]){
                    $data['sid1'] = $school[$sid1];
                    $data['year'] = '';
                    $data['cid0'] = 8;
                    $data['uid'] = 222529;
                    $ret = $this->_doAddGroup($data,$line);
                }else{
                    $this->failArray[] = $line.' 无院系 '.$data['name'].'<br/>';
                    $ret = false;
                }
                if ($ret) {
                    $map=array();
                    $map['tag_name'] = t($user[2]);
                    if ($info = M('tag')->where($map)->find()) {
                        $tagId = $info['tag_id'];
                    } else {
                        $tagId = M('tag')->add($map);
                    }
                    $tagData = array();
                    $tagData['gid'] = $ret;
                    $tagData['tag_id'] = $tagId;
                    M('group_tag')->add($tagData);
                    $suc++;
                } else {
                    $fail++;
                    //$failArray[] = $email.' '.$data['realname'].' '.$data['sid1'].' '.$data['year'].' '.$data['major'].' '.$data['sex'].' '.$data['mobile'].'<br/>';
                    //$failArray[] = $i.' '.$data['name'].'<br/>';
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }

    //学号，新密码
    public function newpass() {
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $failArray = array();
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $num = t($user[0]);
                $email = $num.$this->email_post;
                $password = t($user[1]);
                $data['password']  = codePass($password);
                $ret = $this->_upUser($email, $data);
                if ($ret) {
                    $suc++;
                } else {
                    $fail++;
                    //$failArray[] = $email.' '.$data['realname'].' '.$data['sid1'].' '.$data['year'].' '.$data['major'].' '.$data['sex'].' '.$data['mobile'].'<br/>';
                    $failArray[] = $email.'<br/>';
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($failArray);
    }
    public function attent() {
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $line = $i+1;
                $user = explode(",", $cArray[$j][$i]);
                $email = t($user[0]).$this->email_post;
                $data = array();
                $mid = $this->daoUser->getField('uid','email='."'".$email."'");
                if($mid){
                    $suc++;
                    $data['password']  = codePass('111111');
                    $data['year'] = t($user[3]);
                    $data['major'] = t($user[4]);
                    $data['sex'] = (t($user[5]) == '女')?0:1;
                    $data['mobile'] = '';
                    $data['email2'] = '';
                    $data['is_valid']  = 0;
                    $data['is_init']  = 0;
                    $this->daoUser->where('uid='.$mid)->save($data);
                    S('S_userInfo_' . $mid, null);
                }else{
                    $fail++;
                    $this->failArray[] = $line.' 没找到用户 '.$email.'<br/>';
                }

            }

        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }
    //uid|pu币
    public function pubiUid() {
        die;
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $daoMoney = Model('Money');
        $daoNotify = service('Notify');
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $line = $i+1;
                $user = explode(",", $cArray[$j][$i]);
                $uid = t($user[0]);
                $money = t($user[1])*100;
                $mid = $daoMoney->moneyIn($uid, $money, '你摇我就送');
                if($mid){
                    $suc++;
                    // 发送通知
                    $notify_data = array('body' => '活动奖励-你摇我就送');
                    $daoNotify->sendIn($uid, 'admin_pubi', $notify_data);
                }else{
                    $fail++;
                    $this->failArray[] = $line.' 没找到用户 '.$uid.'<br/>';
                }

            }

        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }
    //学号|学校|pu币
    public function pubiEmail() {
        die;
        $start = $this->start;
        $end = $this->end;
        $sdb = M('school')->where('pid=0')->field('title,email')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['email'];
        }
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $daoMoney = Model('Money');
        $daoNotify = service('Notify');
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $line = $i+1;
                $user = explode(",", $cArray[$j][$i]);
                $s = t($user[1]);
                $email = t($user[0]).$school[$s];
                $uid = $this->daoUser->getField('uid',"email='".$email."'");
                if($uid){
                    $money = t($user[2])*100;
                    $mid = $daoMoney->moneyIn($uid, $money, '活动奖励-娱乐小游戏');
                    if($mid){
                        $suc++;
                        // 发送通知
                        $notify_data = array('body' => '活动奖励-娱乐小游戏');
                        $daoNotify->sendIn($uid, 'admin_pubi', $notify_data);
                    }else{
                        $fail++;
                        $this->failArray[] = $line.' 写入失败 '.$user[0].$s.'<br/>';
                    }
                }else{
                    $fail++;
                    $this->failArray[] = $line.' 没找到用户 '.$user[0].$s.'<br/>';
                }
            }
        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }
    //学号|学校|pu币|姓名
    public function checkPubiEmail() {
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;
        $sdb = M('school')->where('pid=0')->field('title,email')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['email'];
        }
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $line = $i+1;
                $user = explode(",", $cArray[$j][$i]);
                $s = t($user[1]);
                $realname = t($user[3]);
                $map['email'] = t($user[0]).$school[$s];
                $likeRealname = mb_substr($realname, 0, 3);
                $map['realname'] = array('like',$likeRealname.'%');
                $has = $this->daoUser->where($map)->field('uid')->find();
                if(!$has){
                    $fail++;
                    $msg = $line.','.$realname.' '.$s.'<br/>';
                    echo $msg;
                }else{
                    $suc++;
                }
            }

        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
    }

    //报名活动
    public function addEvent() {
        die;
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $line = $i+1;
                $in = explode(",", $cArray[$j][$i]);
                $email = t($in[0]);
                $map['email'] = $email.$this->email_post;
                $user = $this->daoUser->where($map)->field('uid,realname,sex')->find();
                if(!$user){
                    $fail++;
                    $this->failArray[] = $line.' 没找到用户 '.$email.'<br/>';
                }else{
                    $suc++;
                    $data['eventId'] = 12836;
                    $data['uid'] = $user['uid'];
                    $data['realname'] = $user['realname'];
                    $data['sex'] = $user['sex'];
                    $data['usid'] = $this->sid;
                    $data['cTime'] = time();
                    $res = M('event_user')->add($data);
                }
            }

        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }
    //重置密码
    public function resetPass() {
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $line = $i+1;
                $in = explode(",", $cArray[$j][$i]);
                $email = t($in[0]).$this->email_post;
                $map['email'] = $email;
                $data['password'] = codePass('111111');
                //密码重置，不做未初始化处理，下面三行进行注释
		$data['is_init'] = 0;
                $data['email2'] = '';
                $data['mobile'] = '';
                $user = $this->daoUser->where($map)->save($data);
                if(!$user){
                    $fail++;
                    $this->failArray[] = $line.' 没找到用户 '.$email.'<br/>';
                }else{
                    $suc++;
                }
            }

        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }

    //初始化用户，输入uid
    public function iniUser() {
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $line = $i+1;
                $in = explode(",", $cArray[$j][$i]);
                $uid = t($in[0]);
                $map['uid'] = $uid;
                $data['password'] = codePass('111111');
                $data['is_init'] = 0;
                $data['email2'] = '';
                $data['mobile'] = '';
                $user = $this->daoUser->where($map)->save($data);
                if(!$user){
                    $fail++;
                    $this->failArray[] = $line.' 没找到用户 '.$uid.'<br/>';
                }else{
                    $suc++;
                }
            }

        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }

    //修改部分用户数据
    public function upUser() {
//        $map['pid'] = $this->sid;
//        $sdb = M('school')->where($map)->field('id, title')->findAll();
//        foreach($sdb as $v){
//            $school[$v['title']] = $v['id'];
//        }
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;

        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        $cArray = array();

        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $data = array();
                $new = t($user[0]);
                $old = t($user[1]);
                $email = $old.$this->email_post;
                $data['email'] = 'yyy'.$new.$this->email_post;
                $ret = $this->_upUser($email, $data);
                if ($ret) {
                    $suc++;
                } else {
                    $fail++;
                    //$failArray[] = $email.' '.$data['realname'].' '.$data['sid1'].' '.$data['year'].' '.$data['major'].' '.$data['sex'].' '.$data['mobile'].'<br/>';
                    $this->failArray[] = $email.'<br/>';
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }


        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }

    public function recommEvent(){
        $provId = 32273;
        set_time_limit(0);
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode(",", $cArray[$j][$i]);
                $pid = intval($user[0]);
                $ret = $this->_recomm($pid,$provId);
                if ($ret) {
                    $suc++;
                } else {
                    $fail++;
                    $this->failArray[] = $pid.'<br/>';
                }
                if ($debug)
                    break;
            }
            if ($debug)
                break;
        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
        print_r($this->failArray);
    }

    private function _recomm($pid,$provId){
        $player = M('event_player')->where('id='.$pid)
                ->field('uid,sid,path,realname,school,content,paramValue,isRecomm')->find();
        if(!$player){
            return false;
        }
        if($player['isRecomm']){
            return true;
        }
        //上传选手
        $player['eventId'] = $provId;
        $player['cTime'] = time();
        $player['status'] = 0;
        $player['recommPid'] = $pid;
        $newId = M('event_player')->add($player);
        if(!$newId){
            return false;
        }
        //上传图片
        $imgs = M('event_img')->where('uid='.$pid)->field('path,title')->findAll();
        foreach ($imgs as $v) {
            $v['eventId'] = $provId;
            $v['uid'] = $newId;
            $v['cTime'] = time();
            M('event_img')->add($v);
        }
        //上传flash
        $flash = M('event_flash')->where('uid='.$pid)->field('path,link,title,flashvar,host')->findAll();
        foreach ($flash as $v) {
            $v['eventId'] = $provId;
            $v['uid'] = $newId;
            $v['cTime'] = time();
            $v['show'] = 0;
            M('event_flash')->add($v);
        }
        $data = array('isRecomm'=>1,'recommPid'=>$newId);
        M('event_player')->where('id='.$pid)->save($data);
        return true;
    }

    public function dnan() {
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("xxxyyy", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $user = explode("$", $cArray[$j][$i]);
                if(count($user)!=5){
                    var_dump($i);
                    $fail++;
                }else{
                    $data = array();
                    $email = t($user[0]).$this->email_post;
                    $hasUser = M('user')->where("email='$email'")->field('uid')->find();
                    if(!$hasUser){
                        $hang = $i+1;
                        echo "[$hang]". $user[0].'<br/>';
                    }
                    $suc++;
                }
            }
        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
    }
    //检查用户是否存在
    public function checkUser() {
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $in = explode(",", $cArray[$j][$i]);
                $num = t($in[0]);
                $email = $num.$this->email_post;
                $map['email'] = $email;
                $user = $this->daoUser->where($map)->field('uid')->find();
                if(!$user){
                    $fail++;
                    $msg = $num.','.t($in[1]).'<br/>';
                    echo $msg;
                }else{
                    $suc++;
                }
            }

        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
    }
    //南中医八个1导入
    public function nzy() {
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $daoEcApply = M('ec_apply');
        $types['第一项'] = 13;
        $types['第二项'] = 14;
        $types['第三项'] = 15;
        $types['第四项'] = 16;
        $types['第五项'] = 17;
        $types['第六项'] = 18;
        $types['第七项'] = 19;
        $types['第八项'] = 20;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $in = explode(",", $cArray[$j][$i]);
                $num = t($in[0]);
                $email = $num.'@njutcm.com';
                $map['email'] = $email;
                $user = $this->daoUser->where($map)->field('uid,sid1,realname')->find();
                if(!$user){
                    $fail++;
                    $msg = $num.','.t($in[1]).'<br/>';
                    echo $msg;
                }else{
                    $type = t($in[1]);
                    if(!isset($types[$type])){
                        $fail++;
                        $msg = $num.',类型不对 '.t($in[1]).'<br/>';
                        echo $msg;
                    }else{
                        $data['type'] = $types[$type];
                        $data['title'] = t($in[2]);
                        $data['description'] = t($in[3]);
                        $data['audit'] = 33654;
                        $data['finish'] = 33654;
                        $data['status'] = 1;
                        $data['credit'] = 0.00;
                        $data['sid'] = 591;
                        $data['sid1'] = $user['sid1'];
                        $data['uid'] = $user['uid'];
                        $data['realname'] = $user['realname'];
                        $data['cTime'] = time();
                        $data['rTime'] = time();
                        $daoEcApply->add($data);
                        $suc++;
                    }
                }
            }

        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
    }
    //检查用户是否存在
    public function checkUser2() {
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $in = explode(",", $cArray[$j][$i]);
                $num = t($in[0]);
                $email = $num.$this->email_post;
                $map['email'] = $email;
                $user = $this->daoUser->where($map)->field('uid')->find();
                if($user){
                    $fail++;
                    $msg = $num.','.$num.'<br/>';
                    echo $msg;
                }else{
                    $suc++;
                }
            }

        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
    }
    //uid|pu币|姓名
    public function checkUidRealname() {
        $debug = $this->debug;
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $line = $i+1;
                $user = explode(",", $cArray[$j][$i]);
                $uid = t($user[0]);
                $realname = t($user[2]);
                $map['uid'] = $uid;
                $map['realname'] = $realname;
                $user = $this->daoUser->where($map)->field('uid')->find();
                if(!$user){
                    $fail++;
                    $msg = $uid.','.$realname.'<br/>';
                    echo $msg;
                }else{
                    $suc++;
                }
            }

        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
    }
    public function delVote() {
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        $eid = 87788;
        $daoVote = M('event_vote');
        $data['status'] = 2;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $in = explode(",", $cArray[$j][$i]);
                $uid = t($in[0]);
                $map['mid'] = $uid;
                $map['eventId'] = $eid;
                $user = $daoVote->where($map)->save($data);
                if(!$user){
                    $fail++;
                    $msg = $uid.'<br/>';
                    echo $msg;
                }else{
                    $suc++;
                }
            }

        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
    }
    public function hebei() {
        die('xxx');
        $start = $this->start;
        $end = $this->end;
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $cArray = array();
        for ($i = $start; $i <= $end; $i++) {
            $file = APPS_PATH . '/newcomer/Appinfo/' . $i . ".csv";
            $users = file_get_contents($file);
            $aArray = explode("\n", $users);
            $cArray[] = $aArray;
        }
        $suc = 0;
        $fail = 0;
        for ($j = 0; $j < count($cArray); $j++) {
            for ($i = 1; $i < count($cArray[$j]); $i++) {
                $in = explode(",", $cArray[$j][$i]);
                $realname = t($in[1]);
                $credit = t($in[2]);
                $email = t($in[0]).$this->email_post;
                $user = M('user')->where("email='$email'")->field('uid,realname,sex')->find();
                    $line = $i+1;
                if(!$user){
                    $fail+=1;
                    echo t($in[0]).' '.$realname.' '.$user['realname']."<br/>";
                }else{
                    $sql = "insert into ts_event_user (`eventId`,`uid`,`status`,`cTime`,`realname`,`sex`,`usid`,`remark`,`addCredit`) values ";
                    $sql .= "('149504','$user[uid]','2','1457679668','$user[realname]','$user[sex]',$this->sid,'系统导入',$credit)";
                    M('')->query($sql);
                }
            }
        }
        echo "done";
        echo "<br>suc - " . $suc;
        echo "<br>fail - " . $fail;
        echo "<br>";
    }

    /**
     * 江苏经贸职业技术学院  学分导入
     */
    public function doExcel()
    {
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $uploadFile = './data/20160330.xls';
        $result = service('Excel')->doImportExcel($uploadFile,$this->email_post);
        foreach($result as $k => $v)
        {
            $data['realname'] = $v[0];
            $data['email'] = $v[1].$this->email_post;
            $data['password'] = codePass($v[2]);
            $data['uname'] = $v[0].$v[1];
            $data['sex'] = $v['4'] == '男' ? 1 : 0;
            $data['event_level'] = 11;
            $data['sid'] = $this->sid;
            $data['sid1'] = $this->getSchoolId($v[3],$this->sid);
            $data['ctime'] = time();
            $flag = M('user')->add($data);
            if(!$flag)
            {
                $error_email[] = $v[0];
            }
        }
        var_dump($error_email);
    }

    public function doUpdatePass()
    {
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $uploadFile = './data/20160331.xls';
        $result = service('Excel')->doImportExcel($uploadFile,$this->email_post);
        foreach($result as $k => $v)
        {
            $map['email'] = $v[0].$this->email_post;
            $data['password'] = codePass('111111');
            $flag = M('user')->where($map)->save($data);
            if(!$flag)
            {
                $error_email[] = $v[0].$this->email_post;
            }
        }
        var_dump($error_email);
    }

    public function checkUserExist()
    {
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $uploadFile = './data/20160331.xls';
        $result = service('Excel')->doImportExcel($uploadFile,$this->email_post);
        foreach($result as $k => $v)
        {
            $map['email'] = $v[0].$this->email_post;
            $flag = M('user')->where($map)->find();
            if(empty($flag))
            {
                $error_email[] = $v[0].$this->email_post;
            }
        }
        var_dump($error_email);
    }

}
?>