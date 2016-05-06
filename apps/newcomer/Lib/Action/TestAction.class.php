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
 *
 * 访问这个import的时候, 还要加一下go=1
 */
import('admin.Action.AdministratorAction');
class TestAction extends AdministratorAction {

    public function index(){
        set_time_limit(0);
//        $sql = "SELECT id,bank_card_id,money,staging,stime,mark FROM `ts_bank_finance` WHERE stime>='2015-12-02'and stime<='2015-12-04'and status in(1,2)";
//        $list = M('')->query($sql);
//       dump($list);
//        $sql = "delete from ts_message_list where `min_max` like '-%'";
//       $sql = "delete from ts_bank_finance_detail where bank_finance_id in (5271,5284,5283)";
//       $sql = "delete from ts_bank_card where id=5065";
//        $sql = "update ts_bank_finance set status=40 where id in (5285,5288)";
//        $sql = "update ts_bank_contract set finance_id=5270 where contract_id='1511060027_08'";
//        $sql = "select * from ts_bank_finance_detail where bank_finance_id in (5270,5271)";
//        $sql = "SELECT * FROM ts_bank_contract WHERE uid = 2279637";
//        $sql = "select * from ts_bank_finance where id in (5285,5288)";
//        $sql = "select * from ts_bank_card where id = 5062";
//        echo $sql;
//        $list = M('')->query($sql);
//        $sql = M('')->getLastSql();
//        echo $sql;
//        $list = D('EcIdentify')->findAll();
//        $list1 = M('')->query($sql1);
//        $list2 = M('')->query($sql2);
//        dump($list);
//
//
//        dump($list1);
//        dump($list2);
//        while($list){
//            foreach($list as $v){
//                $year = M('user')->getField('year','uid='.$v['uid']);
//                if(!$year){
//                    $year = 'xxx';
//                }
//                M('xch')->setField('year', $year,'uid='.$v['uid']);
//            }
//            $list = M('')->query($sql);
//        }


        die('ok');
//合并小游戏排行
//        $daoOld = M('game_score');
//        $daoNew = M('game_score_new');
//        $new = $daoNew->order('id asc')->field('uid,gid,score')->findAll();
//        foreach($new as $v){
//            $gid = $v['gid'];
//            $uid = $v['uid'];
//            $newScore = $v['score'];
//            $oldScore = $daoOld->getField('score',"uid=$uid and gid=$gid");
//            if(!$oldScore){
//                $daoOld->add($v);
//            }elseif($newScore>$oldScore){
//                $daoOld->where("uid=$uid and gid=$gid")->delete();
//                $daoOld->add($v);
//            }
//        }
//        //摇一摇
//        $winPu = 0.05;
//        $win = array(10,20,30,40,50);
//        $cs = array(96,1,1,1,1);
//        $times = 50528 ;
//        $total = 0;
//        $sumCs = 0;
//        foreach ($cs as $k => $v) {
//            $sumCs += $v;
//            $total += $v*$win[$k];
//        }
//        var_dump(count($win));
//        var_dump(count($cs));
//        var_dump($sumCs);
//        var_dump($times*$winPu*$total/10000);
//        die('ok');
//活动学分/4
//        $map['sid'] = 525;
//        $map['year'] = '13';
//        $list = M('user')->where($map)->field('uid')->findAll();
//        $uids = getSubByKey($list, 'uid');
//        foreach($uids as $uid){
//            $sql = "update ts_event_user set credit=credit/4 where uid=$uid and credit>0";
//            M('')->query($sql);
//            $sql = "update ts_event_user set addCredit=addCredit/4 where uid=$uid and addCredit>0";
//            M('')->query($sql);
//            $sql = "update ts_tj_eday set credit=credit/4 where uid=$uid";
//            M('')->query($sql);
//        }
        die('ok');
        //初始化用户，除了已绑定手机的用户及老师，全部要初始化
//        $sids = array(598,619,630,614,611,613,586,600,599,622,616,623,633,588,584,594,595,590,593,636,637,621,507,2133,607,608,601,602,615,620,605,609);
//        $map['sid'] = array('in',$sids);
//        $map['event_level'] = '20';
//        $map['mobile'] = array('neq','');
//        //$map['mobile'] = '';
//
//        $data['password'] = codePass('111111');
////        $data['is_init'] = 0;
////        $data['email2'] = '';
//        $list = M('user')->where($map)->save($data);
//        var_dump($list);
//        die;

//        $map['type'] = 'event_finishback';
//        $map['ctime'] = array('gt',  strtotime('2013-12-30'));
//        $notify = M('notify')->where($map)->field('data')->findAll();
//        foreach ($notify as $value) {
//            $row = unserialize($value['data']);
//            $eventId = $row['eventId'];
//            if($eventId!=8937 && $eventId!=8938){
//                //更新已签到的
//                $user = M('event_user')->where('status=2 and eventId='.$eventId)->field('uid')->findAll();
//                $attend = getSubByKey($user, uid);
//                $map = array();
//                $map['uid'] = array('in',$attend);
//                $data = array();
//                $data['total'] = array('exp','total-1');
//                $data['attend'] = array('exp','attend-1');
//                M('event_cx')->where($map)->save($data);
//                //更新未签到的
//                $user = M('event_user')->where('status=1 and eventId='.$eventId)->field('uid')->findAll();
//                $attend = getSubByKey($user, uid);
//                $map = array();
//                $map['uid'] = array('in',$attend);
//                $data = array();
//                $data['total'] = array('exp','total-1');
//                $data['absent'] = array('exp','absent-1');
//                $res = M('event_cx')->where($map)->save($data);
//            }
//        }
	die;
//        $sql = "select distinct(major) from ts_user where sid1=965";
//        $sql = "select count(1) from ts_user where sid1=965 and major='轨道交通信号'";
//        $sql = "update ts_user set sid1=2071 where sid1=965 and major='轨道交通信号'";
        $sql = 'select * from ts_citys';
        $res = M('')->query($sql);
        var_dump($res);
        die;
        $schools = M('school')->field('id,title')->findAll();
        foreach ($schools as $school) {
            $data['id'] = $school['id'];
            $data['display_order'] = pinyin($school['title']);
            M('school')->save($data);
        }
        die;
        $map['type'] = 'location';
        for($i=1;$i<=10;$i++){
        $map['email'] = 'zyz_test'.$i.'@test.com';
        $db_prefix = C('DB_PREFIX');
        $list = M('')->table("{$db_prefix}user AS a ")
                ->join("{$db_prefix}login AS b ON b.uid=a.uid")
                ->where($map)->field('a.email,a.uid,b.oauth_token,b.oauth_token_secret')->find();
var_dump($list);
        }
        die;
        $arr = array(38717,40322, 88170, 64522,140618,174050,175540,208119,244430,290170,268559,299532,376989,389995,388092,388799,395748,397560,403085,415883,433816,449650,455526,484443,488161,488721,498150,534706,584410,733032);
        $map['id'] = array('in',$arr);
        M('event_user')->where($map)->delete();
        die;
        $list = M('group')->where('school=629')->field('id')->findAll();
        var_dump(count($list));die;
        $daoGroup = D('EventGroup');
        $daoGroup->disBandGroup($gid);
//        $sql = "SELECT uid FROM `ts_user` where email like '1@%'";
//        var_dump(M('')->query($sql));die;
        //互换年级专业
//        $dao = M('user');
//        $years = array();
//        foreach ($years as $year) {
//            $list = $dao->where("year='$year'")->field('uid,major,year')->findAll();
//            foreach ($list as $value) {
//                $major = $value['year'];
//                $value['year'] = $value['major'];
//                $value['major'] = $major;
//                $dao->save($value);
//            }
//        }
        //$arr = array('工商管理','市场营销','人力资源管理','信息管理与信息系统','电子商务','财务管理');
        //$list = $dao->where("year like '%级'")->field('uid,year')->findAll();
//        $sql = "update ts_user set year='08' where year='08级' OR  year='2008'";
//        $list = M('')->query($sql);

        $sql = "SELECT DISTINCT (`year`) as year FROM `ts_user` where sid!=659 and sid!=505 and sid!=473";
        //$sql = "SELECT DISTINCT (`year`) as year FROM `ts_user` where sid=590";
        //$sql = "SELECT DISTINCT (`sid`) FROM `ts_user` where year = '劳动与社会保'";
        $list = M('')->query($sql);
        foreach ($list as $value) {
            echo "'".$value['year']."',";
        }
        die;
        //活动成员参加时间0
        $db_prefix = C('DB_PREFIX');
        $list = M('')->table("{$db_prefix}event_user AS a ")
                ->join("{$db_prefix}event AS b ON b.id=a.eventId")
                ->field('a.id,b.cTime')
                ->where('a.cTime=0')->findAll();
        $dao = M('event_user');
        foreach ($list as $v) {
            $dao->save($v);
        }
        die;
        $sql = "INSERT INTO `2012xyhui`.`ts_partner` (
`mAppName` ,
`mIconUrl` ,
`mDownloadUrl` ,
`mPkgName`
)
VALUES
('139邮箱', 'http://pocketuni.net/data/partner/mobile_app_mail.png', 'http://file1.popspace.com/apk/10/064/442/ab3c1b6d-17d1-45a4-8102-dc5d8df89b53.apk', 'cn.cj.pe'
),(
'139出行', 'http://pocketuni.net/data/partner/mobile_app_cx.png', 'http://wap.139sz.cn/cx/download.php?id=1', 'com.android.suzhoumap'
),(
'无线智慧城', 'http://pocketuni.net/data/partner/mobile_app_city.png', 'http://wap.139sz.cn/g3club/3gfly/?a=app&m=down&id=12912', 'com.jscity'
),(
'苏州生活', 'http://pocketuni.net/data/partner/mobile_app_life.png', 'http://wap.139sz.cn/MobileSZSH_V1.apk', 'com.xwtech.szlife'
),(
'139分享', 'http://pocketuni.net/data/partner/mobile_app_share.png', 'http://wap.139sz.cn/g3club/3gfly/?a=app&m=down&id=75654', 'com.diypda.g3downmarket'
),(
'农家乐', 'http://pocketuni.net/data/partner/mobile_app_njl.png', 'http://wap.139sz.cn/g3club/3gfly/?a=app&m=down&id=65105', 'com.szmobile.sznjl'
),(
'139答应', 'http://pocketuni.net/data/partner/mobile_app_dy.png', 'http://www.apk.anzhi.com/data1/apk/201309/16/com.cplatform.xqw_14035600.apk', 'com.cplatform.xqw'
);";
        M('')->execute($sql);
        var_dump(M('partner')->findAll());die;
//        die;
//        //pu币
//        $daoMoney = Model('Money');
//        $daoMoney->moneyIn(1267214, 10000, '测试用');
//        die;
//        $list = M('user')->where('cs_orga!=0 and can_event=1')->field('uid,cs_orga')->findAll();
//        $dao = M('event_csorga');
//        foreach ($list as $value) {
//            $data = array('uid'=>$value['uid'],'orga'=>$value['cs_orga']);
//            $dao->add($data);
//        }
//        die;
//        //活动码授权人数
//        $list = M('event_add')->where('codelimit!=5')->findAll();
//        $dao = M('event');
//        foreach ($list as $value) {
//            $data = array('codelimit'=>$value['codelimit']);
//            $dao->where('uid='.$value['uid'])->save($data);
//        }
//        die;
//        include_once SITE_PATH.'/addons/libs/Image.class.php';
//        Image::thumb(UPLOAD_PATH.'/2013/1213/15/52aab18bf2602.png',
//                UPLOAD_PATH.'/2013/1213/15/middle_52aab18bf2602.png',
//                '',465,'auto');
//        die;
//        //RENAME TABLE `2012xyhui`.`ts_money` TO `2012xyhui`.`ts_money_old` ;
//        set_time_limit(0);
//        $list = M('money_old')->findAll();
//        $dao = M('money');
//        foreach ($list as $v) {
//            $data['uid']= $v['uid'];
//            $data['money'] = $v['money'];
//            $dao->add($data);
//        }
//        die;
////        set_time_limit(0);
////        $map['credit'] = array('neq',0);
////        $list = M('event_user')->where($map)->field('uid,credit,fTime,usid')->findAll();
////        $map = array();
////        foreach ($list as $v) {
////            $data['uid'] = $v['uid'];
////            $data['sid'] = $v['usid'];
////            $data['credit'] = $v['credit'];
////            $data['day'] = date('Y-m-d', $v['fTime']);
////            $map['uid'] = $data['uid'];
////            $map['day'] = $data['day'];
////            $res = M('tj_eday')->field('id,credit')->where($map)->find();
////            if($res){
////                $data['credit'] += $res['credit'];
////                M('tj_eday')->where('id='.$res['id'])->save($data);
////            }else{
////                M('tj_eday')->add($data);
////            }
////        }
////        die;
        $map['isDel']= 0;
        $map['sid']= 0;
        $map['is_school_event']= array('neq',0);
        $map['audit_uid']= array('neq',0);
        $list = M('event')->where($map)->field('id,audit_uid')->findAll();
        var_dump(count($list));
        $succ = 0;
        foreach ($list as $value) {
            $sid = M('user')->where('uid='.$value['audit_uid'])->getField('cs_orga');
            if($sid){
                $succ+=1;
                M('event')->setField('sid', $sid, 'id='.$value['id']);
            }
        }
        var_dump($succ);
        die;
        //改变图片大小
        require_once(SITE_PATH . '/addons/libs/Image.class.php');
        $src=SITE_PATH.'/data/aa1.jpg';
        $des=SITE_PATH.'/data/aa.jpg';
        Image::thumb( $src, $des, '' , 1000, 1000 );
        die;
        $dao = M('user');
        $user = $dao->where("sid=530 and email like '%@hatsw_admin.com'")->field('uid,email')->findAll();
        foreach ($user as $v) {
            $data['email'] = str_replace('hatsw_admin', 'hcit', $v['email']);
            $dao->where("uid=".$v['uid'])->save($data);
        }
        die;
        $school = Model('Schools')->makeLevel0Tree();
        $dao = M('event');
        foreach ($school as $v) {
            $top = $dao->where('isDel=0 and isTop=1 and is_school_event='.$v['id'])->count();
            if($top>5){
                echo $top.'个置顶 <a href="http://'.$v['domain'].'.pocketuni.net/index.php?app=event&mod=Event&act=eventlist" target="_blank">'.$v['title'].'</a></br>';
            }
        }
        die;
        $map['sid1']=1046;
        $map['year']='13级';
        $data['is_valid'] = 0;
        $data['is_init'] = 0;
        $data['password'] = md5('111111');
        $data['mobile'] = '';
        $data['email2'] = '';
        M('user')->where($map)->save($data);
        die;
        //十佳图片导出
        $path = SITE_PATH.'/tt/1/';
        $dao = M('sj');
        $list = $dao->where('type=9 and status=5')->field('id,sid,title,ticket')->order('ticket DESC, id DESC')->limit(20)->findAll();
        $daoImg = M('sj_img');
        $daoAttach = M('attach');
        foreach ($list as $key=>$value) {
            $line = $key+1;
            $title = str_replace(array(' ','•'), array('',''), $value['title']);
            $school = tsGetSchoolName($value['sid']);
            $tempDir = $path.$line.'.'.$school.'-'.$title.'/';
            $dir = iconv("UTF-8", "GBK", $tempDir);
            checkDir($dir);
            $attIds = $daoImg->where(array('sjid' => $value['id']))->field('attachId')->order('status DESC, id DESC')->findAll();
            foreach($attIds as $k=>$attId){
                $no = $k+1;
                $no = sprintf ("%02d",$no);
                $att = $daoAttach->where('id='.$attId['attachId'])->field('savepath,savename,extension')->find();
                if($att){
                    $exec = 'cp '.SITE_PATH.'/data/uploads/'.$att['savepath'].$att['savename'].' '.$dir.$no.'.'.$att['extension'];
                    exec($exec);
                }
            }
        }
        die;
        set_time_limit(0);
        //微博数
        $daoCount = Model('UserCount');
        $dao = M('weibo');
        for($i=1;$i<=22300;$i++){
            $follow = $dao->where('weibo_id='.$i)->field('uid')->find();
            if($follow){
                $daoCount->addCount($follow['uid'],'weibo');
            }
        }
        die;
        //粉丝关注数
        $daoCount = Model('UserCount');
        $daoFollow = M('weibo_follow');
        $follow = $daoFollow->where('type=0')->findAll();
        foreach($follow as $f){
            $daoCount->addCount($f['uid'],'following');
            $daoCount->addCount($f['fid'],'follower');
        }
        die;
        for($i=1;$i<=3005000;$i++){
            $follow = $daoFollow->where('follow_id='.$i)->find();
            if($follow && $follow['type']==0 && $follow['uid']!=1 && $follow['fid']!=1){
                $daoCount->addCount($follow['uid'],'following');
                $daoCount->addCount($follow['fid'],'follower');
            }
        }
        die;
        $dao = M('event_user');
        $uids = $dao->where('id>5000 and id<9000')->field('id,uid,realname')->findAll();
        $daoUser = M('user');
        foreach ($uids as $value) {
            $user = $daoUser->where('uid='.$value['uid'])->field('realname')->find();
            if($user['realname'] != $value['realname']){
                $data = array('realname'=>$user['realname']);
                $dao->where('id='.$value['id'])->save($data);
            }
        }
        die;
        //$tag_id = M('tag')->getField('tag_id', "tag_name='苏州大学'");
        $daoTag = M('tag');
        $daoGtag = M('group_tag');
        $tag_id = $daoTag->query('SELECT count(1) as count,tag_name FROM `ts_tag` GROUP BY tag_name having count>1;');
        foreach ($tag_id as $value) {
            $search_key = $value['tag_name'];
            $tags = $daoTag->where("tag_name='{$search_key}'")->findAll();
            $ids = getSubByKey($tags, 'tag_id');
            $rest = $ids[0];
            unset($ids[0]);
            $map['tag_id'] = array('in',$ids);
            $daoTag->where($map)->delete();
            $data['tag_id'] = $rest;
            $daoGtag->where($map)->save($data);
        }
        var_dump($tag_id);
        die;
        $path = SITE_PATH.'/tt/1/';
        $dao = M('sj');
        $list = $dao->where('type=9 and status=5')->field('id,title,sid,content,attach')->findAll();
        $word = new word();
        foreach ($list as $value) {
//            $tempDir = $path.$value['title'].'/';
//            $dir = iconv("UTF-8", "GBK", $tempDir);
//            checkDir($dir);
            $title = str_replace(' ', '', $value['title']);
            $school = tsGetSchoolName($value['sid']);
            $wordname = $school.'-'.$title.".doc";
            //$wordname = $title.".doc";
            $word->start();
            $html = htmlspecialchars_decode($value['content']);
            echo $html;
            $word->save($path.$wordname);
            ob_flush();//每次执行前刷新缓存
            flush();
//            $attname = $school.'-'.$title;
//            $attname = iconv("UTF-8", "GBK", $attname);
//            $att = M('attach')->where('id='.$value['attach'])->field('savepath,savename,extension')->find();
//            if($att){
//                $exec = 'cp '.SITE_PATH.'/data/uploads/'.$att['savepath'].$att['savename'].' '.$path.$attname.'.'.$att['extension'];
//                exec($exec);
//            }
        }
//        for($i=1;$i<=3;$i++){
//            $word->start();
//            $html = '';
//            $wordname = '每次执'.$i.".doc";
//            echo $html;
//            $word->save($path.$wordname);
//            ob_flush();//每次执行前刷新缓存
//            flush();
//        }
        die;

        $dao = M('user_mobile');
        for($i=29;$i>=22;$i--){
            $j = $i-1;
            $str = '2013-09-'.$j;
            $from = strtotime($str);
            $str = '2013-09-'.$i;
            $to = strtotime($str);
            $map = array();
            $map['cTime'] = array('between',array($from,$to));
            $total = $dao->where($map)->count();
            $map['mobile'] = array('like','%@%');
            $email = $dao->where($map)->count();
            $mobile = $total-$email;
            echo $j.'号0点到 '.$i.'号0点: '.$email.' 邮件 '.$mobile.' 短信<br/>';
        }
        die;

        $res = model('Schools')->__getCategory(-1);
        var_dump($res);
//        $citys = model('Citys')->getAllCitys();
//        var_dump($citys);
        $schools = model('Schools')->makeLevel0Tree(0);
        var_dump($schools);die;
        $s = time();
        M('user')->where('sid=584')->force('sid')->order('uid DESC')->limit(10)->findAll();
        $e = time();
        echo time()-$s;
        die;
    }

    //修改学院 合并院系
    public function changeSid1(){
        die;
        $from = 1664;
        $to = 1659;
        $dao = M('');
        $sql = "update ts_event set sid=$to where sid=$from;";
        $dao->query($sql);
        $sql = "update ts_group set sid1=$to where sid1=$from;";
        $dao->query($sql);
        $sql = "update ts_announce set sid1=$to where sid1=$from;";
        $dao->query($sql);
        $sql = "update ts_ec_apply set sid1=$to where sid1=$from;";
        $dao->query($sql);
        $sql = "update ts_user set sid1=$to where sid1=$from;";
        $dao->query($sql);
        $sql = "delete from ts_school where id=$from;";
        $dao->query($sql);
        echo 'from:'.$from.' to:'.$to;
    }

    //实践学分清零 by sid
    public function delCredit(){
        die;
        $sid = 589;
        //user school_event_credit (sid)
        //event_user credit addCredit (usid)
        //tj_eday sid
        //tj_event 第二天生效
        M('user')->setField('school_event_credit', 0, 'sid='.$sid);
        $data['credit'] = 0;
        $data['addCredit'] = 0;
        M('event_user')->where('usid='.$sid)->save($data);
        M('tj_eday')->where('sid='.$sid)->delete();
    }
    //实践学分清零 by eventId
    public function delCreditId(){
        die;
        $id = 23150;
        //user school_event_credit (sid)
        //event_user credit addCredit (usid)
        //tj_eday sid
        //tj_event 第二天生效
        $daoEventUser = M('event_user');
        $daoUser = M('user');
        $daoTj = M('tj_eday');
        $eventUser = $daoEventUser->where('fTime!=0 and credit!=0 and eventId='.$id)->field('uid,credit,fTime')->findAll();
        foreach ($eventUser as $v) {
            $map['uid'] = $v['uid'];
            $data['school_event_credit'] = array('exp','school_event_credit-'.$v['credit']);
            $daoUser->where($map)->save($data);
            $map['day'] = date('Y-m-d', $v['fTime']);
            $data = array('credit'=>array('exp','credit-'.$v['credit']));
            $daoTj->where('uid='.$v['uid'])->save($data);
        }
        $daoEventUser->setField('credit',0,'eventId='.$id);
        $daoTj->where('credit=0')->delete();
    }

    public function credit_event(){
        die;
        $eids = array(35884,35897,35905,28746,23150,22657,20984,20056,19851,18314,17782,16264);
        $map['fTime'] = array('neq','0');
        $map['eventId'] = array('in',$eids);
        $uids = M('event_user')->where($map)->field('uid')->findAll();
        $uids = getSubByKey($uids, 'uid');
        $nuids = array_unique($uids);
        foreach($nuids as $k=>$uid){
            $this->calcTjEday($uid);
        }
    }

    private function calcCredit($uid){
        $credit = M('event_user')->where('fTime!=0 and uid='.$uid)->field('sum(credit) as credit')->find();
        M('user')->setField('school_event_credit', $credit['credit'], 'uid='.$uid);
    }

    private function calcTjEday($uid){
        M('tj_eday')->where('uid='.$uid)->delete();
        $credit = M('event_user')->where('fTime!=0 and credit!=0 and uid='.$uid)->field('credit,fTime,usid')->findAll();
        $data['uid'] = $uid;
        foreach ($credit as $v) {
            $data['sid'] = $v['usid'];
            $data['credit'] = $v['credit'];
            $data['day'] = date('Y-m-d', $v['fTime']);
            $res = M('tj_eday')->add($data);
            if(!$res){
                $sdata = array('credit'=>array('exp','credit+'.$v['credit']));
                M('tj_eday')->where("uid=$uid and day='".$data['day']."'")->save($sdata);
            }
        }
    }


    public function money(){
        die;
        $from = strtotime('2014-05-27 10:44');
        $to = strtotime('2014-05-29 16:50');
        $daoIn = M('money_in');
        $ins = $daoIn->where('ctime>'.$from.' and ctime<'.$to)->field('uid,logMoney')->findAll();
        $dao = Model('Money');
        foreach ($ins as $in) {
            $dao->incMoney($in['uid'], $in['logMoney']);
        }
        $daoOut = M('money_out');
        $outs = $daoOut->where('out_ctime>'.$from.' and out_ctime<'.$to)->field('out_uid,out_money')->findAll();
        foreach ($outs as $in) {
            $dao->setDec('money', 'uid='.$in['out_uid'], $in['out_money']);
            $dao->getMoneyCache($in['out_uid']);
        }
    }

    public function delGroup(){
        die;
        $map['school'] = 2080;
        $map['name'] = array('like','康达11%');
        $groups = M('group')->where($map)->field('id')->findAll();
        $dao = D('EventGroup','event');
        foreach ($groups as $group) {
            $dao->endDel($group['id']);
        }
    }

    public function sj(){
        $pid = intval($_GET['pid']);
        if(!$pid){
            echo '亲，请输入pid';
        }
        $dao = M('sj_vote');
        $uids = $dao->where('pid='.$pid)->field('mid')->findAll();
        $uids = getSubByKey($uids, 'mid');
        $map['uid'] = array('in',$uids);
        $sids = M('user')->where($map)->field('sid')->findAll();
        $res = array();
        foreach ($sids as $value) {
            $sid = tsGetSchoolName($value['sid']);
            if(isset($res[$sid])){
                $res[$sid] += 1;
            }else{
                $res[$sid] = 1;
            }
        }
        arsort($res);
        var_dump($res);
        die;
    }
    public function excel() {
        set_time_limit(0);
        $map['type'] =9;
        $map['status'] = 5;
        $list = M('sj')
                ->where($map)
                ->field('sid,title,title2')
                ->findAll();
        foreach ($list as $k => $v) {
            $list[$k]['sid'] = tsGetSchoolName($v['sid']);
        }
        $arr = array('学校','故事名称','个人或团队名称');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'十佳风尚奖候选名单');
    }
    //三下乡
    public function sjtj1() {
        set_time_limit(0);
        $map['status'] = 5;
        $map['year'] = '2014';
        $list = M('sj')->where($map)->field('sid,title,type,ticket')->findAll();
        $roles = array('1'=>'先进单位申报','2'=>'优秀团队申报','3'=>'优秀团队申报','4'=>'先进个人申报','5'=>'先进个人申报','6'=>'先进工作者申报',
                    '7'=>'优秀社会实践基地申报','8'=>'优秀调研报告申报','9'=>'十佳风尚奖申报');
        foreach ($list as $k=>$v) {
            $list[$k]['sid'] = tsGetSchoolName($v['sid']);
            $list[$k]['type'] = $roles[$v['type']];
        }
        $arr = array('学校','申请项目','申请类别','票数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'2014三下乡');
    }
    //十佳团队
    public function sjtj3() {
        set_time_limit(0);
        $map['status'] = 5;
        $map['type'] = 3;
        $map['year'] = '2015';
        $list = M('sj')->where($map)->field('sid as num,sid,title,title2,ticket')->order('ticket DESC')->findAll();
        foreach ($list as $k=>&$v) {
            $v['num'] = $k+1;
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['zusatz'] = htmlspecialchars_decode($v['zusatz']);
        }
        $arr = array('序号','学校','团队名称','实践项目名称','票数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'2015十佳团队');
    }
    //十佳使者
    public function sjtj5() {
        set_time_limit(0);
        $map['status'] = 5;
        $map['type'] = 5;
        $map['year'] = '2015';
        $list = M('sj')->where($map)->field('sid as num,sid,title,zusatz,ticket')->order('ticket DESC')->findAll();
        foreach ($list as $k=>&$v) {
            $v['num'] = $k+1;
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['zusatz'] = htmlspecialchars_decode($v['zusatz']);
        }
        $arr = array('序号','学校','姓名','所在团队','票数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'2015十佳使者');
    }
    //十佳风尚
    public function sjtj9() {
        set_time_limit(0);
        $map['status'] = 5;
        $map['type'] = 9;
        $map['year'] = '2015';
        $list = M('sj')->where($map)->field('sid as num,sid,title,title2,ticket')->order('ticket DESC')->findAll();
        foreach ($list as $k=>&$v) {
            $v['num'] = $k+1;
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['title2'] = htmlspecialchars_decode($v['title2']);
        }
        $arr = array('序号','学校','故事名称','所属个人或团队名称','票数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'2015十佳风尚');
    }
    //优秀调研报告申报
    public function sjtj8() {
        set_time_limit(0);
        $map['status'] = 5;
        $map['type'] = 8;
        $map['year'] = '2014';
        $list = M('sj')->where($map)->field('sid as num,title,title2,sid')->order('id DESC')->findAll();
        foreach ($list as $k=>&$v) {
            $v['num'] = $k+1;
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['title2'] = htmlspecialchars_decode($v['title2']);
        }
        $arr = array('序号','报告题目','报告作者','学校');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'2014优秀调研报告申报');
    }
    //多少人参加一元梦想，众志成城
    public function shop(){
        $sql = "select count(distinct(out_uid)) as yg from ts_money_out where out_title like '一元梦想%'";
        $list = M('')->query($sql);
        var_dump($list);
        $sql = "select count(distinct(out_uid)) as tg from ts_money_out where out_title like '众志成城%'";
        $list = M('')->query($sql);
        var_dump($list);
    }
    public function credit() {
        set_time_limit(0);
        $map['b.sid'] = array('in','1,2,3,4,402,480,524,525,526,528,529,530,531,550,551,631,634');
        $db_prefix = C('DB_PREFIX');
        $list = M('')->table("{$db_prefix}credit_user AS a ")
                ->join("{$db_prefix}user AS b ON b.uid=a.uid")
                ->field('b.uid,a.score,b.realname,b.sid,b.sid1,b.email')
                ->where($map)->limit(100)->order('a.score DESC')->findAll();
        foreach ($list as $k => $v) {
            $list[$k]['sid'] = tsGetSchoolName($v['sid']);
            $list[$k]['sid1'] = tsGetSchoolName($v['sid1']);
        }
        $arr = array('uid','经验值','姓名','学校','院系','邮箱');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'经验值前100');
    }
    //全家福
    public function qjf() {
        set_time_limit(0);
        $map['a.eventId'] = 12239;
        $db_prefix = C('DB_PREFIX');
        $list = M('')->table("{$db_prefix}event_vote AS a ")
                ->join("{$db_prefix}user AS b ON b.uid=a.mid")
                ->join("{$db_prefix}event_player AS c ON c.id=a.pid")
                ->field('b.email,b.realname,b.sid,b.sid1,count(1) as vote,group_concat(c.realname)')
                ->where($map)->group('a.mid')->findAll();
        foreach ($list as $k => $v) {
            $list[$k]['email'] = ' '.getUserEmailNum($v['email']);
            $list[$k]['sid'] = tsGetSchoolName($v['sid']);
            $list[$k]['sid1'] = tsGetSchoolName($v['sid1']);
        }
        $arr = array('学号','姓名','学校','院系','投票数','选手');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'全家福投票数-'. date('Y-m-d'));
    }
    //南京金陵科技学院
    public function jlSchool() {
        set_time_limit(0);
        $sid = 1917;
        $sid1s = array(1705,1706,1707,1708,1709,1710,1711,1712,1713,1714,1715);
        $daoSchool = M('school');
        //$daoUser = M('User');
        foreach ($sid1s as $sid1) {
            //学院
            $oldSchool = $daoSchool->where('id='.$sid1)->field('title')->find();
            $sData = array();
            $sData['title'] = str_replace('金陵科技学院', '', $oldSchool['title']);
            $sData['pid'] = $sid;
            $daoSchool->where('id='.$sid1)->save($sData);
            //学生
        }
    }
    private function _delSchoolGroup($sid){
        $daoGroup = M('group');
        $gids = $daoGroup->where('school =' . $sid)->field('id')->findAll();
        foreach ($gids as $gid) {
            $this->_delGroup($gid['id']);
        }
    }
    private function _delGroup($gid){
        $result = M('group')->where('id=' . $gid)->delete();
        if (!$result)
            return false;
        M('group_member')->where('gid = ' . $gid)->delete();       //删除成员
//        M('group_tag')->where('gid = ' . $gid)->delete();       //删除标签
//        M('group_topic')->where('gid = ' . $gid)->delete(); //回收话题
//        M('group_attachment')->where('gid = ' . $gid)->delete(); //回收共享
        $daoEventGroup = M('event_group');
        $uids = $daoEventGroup->where('gid =' . $gid)->field('uid')->findAll();
        if (!$uids)
            return true;
        $daoEventGroup->where('gid =' . $gid)->delete();
        $daoUser = M('user');
        foreach ($uids as $v) {
            $addevent = $daoEventGroup->where('uid=' . $v['uid'])->getField('id');
            if (!$addevent) {
                $daoUser->where('uid =' . $v['uid'])->setField('can_add_event', 0);
            }
        }
        return true;
    }

    public function del9User(){
        $daoGroup = M('group');
        $daoGroupMember = M('group_member');
//        $uids = M('user')->where('sid=99999')->field('uid')->findAll();
//        foreach ($uids as $value) {
//            $uid = $value['uid'];
//            $daoGroupMember->where('uid='.$uid)->delete();
//        }
        $groups = $daoGroup->where('school!=0')->field('id')->findAll();
        foreach ($groups as $value) {
            $gid = $value['id'];
            $count = $daoGroupMember->where('level!=0 and gid='.$gid)->count();
            $daoGroup->setField('membercount', $count,'id='.$gid);
        }
    }


    public function jlUser() {
        set_time_limit(0);
        $sid = 1917;
        $sid1s = array(1705,1706,1707,1708,1709,1710,1711,1712,1713,1714,1715);
        $daoUser = M('User');
        foreach ($sid1s as $sid1) {
            //学生
            $users = $daoUser->where('sid1='.$sid1)->field('uid,email')->findAll();
            foreach ($users as $value) {
                $sData = array();
                $sData['email'] = str_replace('njtsw_admin', 'jit', $value['email']);
                $sData['sid'] = $sid;
                $daoUser->where('uid='.$value['uid'])->save($sData);
            }
        }
    }

    public function yytest() {
        set_time_limit(0);
        model('Yuser')->test();
    }
    public function sudaMobile() {
        set_time_limit(0);
        $map['sid'] =1;
        $map['mobile'] = array('neq','');
        $list = M('user')
                ->where($map)
                ->field('email,year,mobile')
                ->findAll();
        $arr = array('学号','年级','手机');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'苏大手机号码');
    }
    public function vote() {
        set_time_limit(0);

        $id = 145144;
        $page = intval($_REQUEST['page'])>1?intval($_REQUEST['page']):1;
        $s = ($page - 1) * 1;
        $events = M('EventPlayer')->field('id')->where(array('eventId'=>$id))->limit($s.',1')->select();
        $pid = $events[0]['id'];

        $map['a.pid'] = $pid;
        $db_prefix = C('DB_PREFIX');
        $list = M('')->table("{$db_prefix}event_vote AS a ")
            ->join("{$db_prefix}user AS b ON b.uid=a.mid")
            ->where($map)->group('a.mid')->field('email,realname,sid,count(1)')->findAll();
        foreach ($list as $k=>$v) {
            $list[$k]['email'] = "'".getUserEmailNum($v['email']);
            $list[$k]['sid'] = tsGetSchoolName($v['sid']);
        }
        $arr = array('学号','姓名','学校','票数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,$pid.'被投票数');

    }
    //部落统计
    public  function group(){
        set_time_limit(0);
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $schools = M('school')->where('pid=0')->field('id,title')->findAll();
        $list = array(array('学校','总数','学生部门','团支部','学生社团'));
        foreach ($schools as $v) {
            $row = array();
            $row[] = $v['title'];
            $map['school'] = $v['id'];
            $row[] = M('group')->where($map)->count();
            $map2 = $map;
            $map2['category']=1;
            $row[] = M('group')->where($map2)->count();
            $map2['category']=2;
            $row[] = M('group')->where($map2)->count();
            $map2['category']=3;
            $row[] = M('group')->where($map2)->count();
            $list[] = $row;
        }
        service('Excel')->export2($list,'各校部落数量'.  date('m-d'));
    }
    //用户统计
    public  function user(){
        set_time_limit(0);
        $schools = M('school')->where('pid=0')->field('id,title')->findAll();
        $list = array(array('学校','总用户数','登录人数','登录大一','登录大二','登录大三','登录大四'));
        $daoUser = M('user');
        $db_prefix = C('DB_PREFIX');
        $dao = M('');
        foreach ($schools as $v) {
            $row = array();
            $row[] = $v['title'];
            $row[] = $daoUser->where('sid='.$v['id'])->count();
            $dao->table("{$db_prefix}login_count AS a ")
                ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
                ->field('count(*) as count');
            $dao->where('b.sid='.$v['id']);
            $row[] = $dao->count();
            $dao->table("{$db_prefix}login_count AS a ")
                ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
                ->field('count(*) as count');
            $dao->where("b.year='13' and b.sid=".$v['id']);
            $row[] = $dao->count();
            $dao->table("{$db_prefix}login_count AS a ")
                ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
                ->field('count(*) as count');
            $dao->where("b.year='12' and b.sid=".$v['id']);
            $row[] = $dao->count();
            $dao->table("{$db_prefix}login_count AS a ")
                ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
                ->field('count(*) as count');
            $dao->where("b.year='11' and b.sid=".$v['id']);
            $row[] = $dao->count();
            $dao->table("{$db_prefix}login_count AS a ")
                ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
                ->field('count(*) as count');
            $dao->where("b.year='10' and b.sid=".$v['id']);
            $row[] = $dao->count();
            $list[] = $row;
        }
        service('Excel')->export2($list,'各校用户数'.  date('m-d'));
    }
    //pu币扣除
    public function moneyOut(){
        die;
        Model('Money')->moneyOut(882825, 19700, '充值退款');
        die;
        $db_prefix = C('DB_PREFIX');
        $list = M('')->table("{$db_prefix}money AS a ")
                ->join("{$db_prefix}money_out AS b ON b.out_uid=a.uid")
                ->where('money=2000 and isnull(b.out_uid)')->field('uid')->findAll();
        $daoMoney = Model('Money');
        foreach ($list as $user) {
            $daoMoney->moneyOut($user['uid'], 2000, '赠送20PU币有效期截止');
        }
    }
    //发放PU币
    public function moneyin(){
        $uids = array('1712012','1711930');
        $daoMoney = Model('Money');
        $daoNotify = service('Notify');
        foreach ($uids as $uid) {
            $mid = $daoMoney->moneyIn($uid, 1000, '活动奖励-释放你的爱之三日情侣');
            if($mid){
                // 发送通知
                $notify_data = array('body' => '活动奖励-释放你的爱之三日情侣');
                $daoNotify->sendIn($uid, 'admin_pubi', $notify_data);
            }
        }
    }
    public function addmoney(){
        $uids = array();
        $daoMoney = Model('Money');
        $total_fee = 5000;
        foreach ($uids as $uid) {
            $daoMoney->incMoney($uid, $total_fee);
        }
    }
    public function yy() {
        set_time_limit(0);
        $mon = intval($_GET['m']);
        if($mon<=0){
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo '请带年月参数如： &m=201412';
            die;
        }
        $table = 'ts_y_record_'.$mon;
        $res = M('')->query("select TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='2012xyhui' and TABLE_NAME='$table';");
        if(!$res){
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo '无'.$mon.'数据';
            die;
        }
        $list = M('')->query("select sid,count(distinct(uid)) as count from $table group by sid");
        foreach ($list as $k=>&$v) {
            if($v['sid']==0){
                unset($list[$k]);
            }
            $v['sid'] = tsGetSchoolName($v['sid']);
        }
        $arr = array('学校','摇一摇人次');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'摇一摇人次'. $mon);
    }
    //活动 显示学校、发起人组织、活动名称、报名人数、签到人数、状态
    public function event(){
        set_time_limit(0);
        $map['status'] = 1;
        $map['isDel'] = 0;
        $map['is_school_event'] = array('neq','0');
        $list = M('event')->where($map)->limit(10)->field('id,is_school_event,gid,gid as gorga,title,typeId,joinCount,id as attend,school_audit')->findAll();
        $daoGroup = M('group');
        $daoEU = M('event_user');
        $types = M('event_type')->field('id,name')->findAll();
        $types = orderArray($types,'id');
        $status = array('5'=>'已完结','4'=>'待完结','3'=>'待完结','2'=>'进行中');
        $gOrga = array('0'=>'','1'=>'学生部门','2'=>'团支部','3'=>'学生社团');
        foreach ($list as $k => $v) {
            if($v['school_audit']>1){
                $list[$k]['is_school_event'] = tsGetSchoolName($v['is_school_event']);
                if($v['gid']==0){
                    $list[$k]['gid'] = '';
                    $list[$k]['gorga'] = '';
                }else{
                    $group = $daoGroup->where('id='.$v['gid'])->field('name,category')->find();
                    $list[$k]['gid'] = $group['name'];
                    $gOrgaId = $group['category'];
                    $list[$k]['gorga'] = $gOrga[$gOrgaId];
                }
                $list[$k]['attend'] = $daoEU->where('status=2 and eventId='.$v['id'])->count();
                $list[$k]['typeId'] = $types[$v['typeId']]['name'];
                $list[$k]['school_audit'] = $status[$v['school_audit']];
            }else{
                unset($list[$k]);
            }
        }
        $arr = array('id','学校','发起人组织','组织归类','活动名称','类别','报名人数','签到人数','状态');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'各校活动列表'. date('m-d'));
    }
    public function tjEvent(){
        $list = array();
        $schools = model('Schools')->makeLevel0Tree();
//        $sids = array(480,524,551,550,528,530,531,529,1,3,2,4,402,525,634,591,639,526,635,527,631,1917,629,617,596,597,589,592,618,585,626,627,625,624,632,640,612,603,1908,606,610,628);
        $notj = array(659,473,1950);
        foreach ($schools as $k => $school) {
            $sid = $school['id'];
            if(!in_array($sid, $notj)){
                    $list[$k]['school'] = $school['title'];
                $map = array();
                $map['status'] = 1;
                $map['isDel'] = 0;
                $map['is_school_event'] = $sid;
                    $list[$k]['event'] = M('event')->where($map)->count();
                $map['school_audit'] = 5;
                    $list[$k]['finish'] = M('event')->where($map)->count();
                unset($map['school_audit']);
                    $list[$k]['join'] = (int)M('event')->where($map)->sum('joinCount');
                $map = array();
                $map['a.status'] = 2;
                $map['usid'] = $sid;
                $map['b.is_school_event'] = $sid;
                    $list[$k]['attend'] = M('')->table("ts_event_user AS a ")
                        ->join("ts_event AS b ON b.id=a.eventId")
                        ->where($map)->count();
            }
        }
        $arr = array('学校','活动总数','总完结活动数','报名总人数','签到总人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'各校活动统计'. date('m-d'));
    }
    //各年级参加活动情况
    public function eventnj(){
        set_time_limit(0);
        $nj = t($_GET['nj']);
        if(!$nj){
            echo '请在地址栏输入正确的年级 如：&nj=11';
            die;
        }
        $map['b.year'] = $nj.'级';
        $map['a.status'] = 0;
        $db_prefix = C('DB_PREFIX');
        $count0 = M('')->table("{$db_prefix}event_user AS a ")
                ->join("{$db_prefix}user AS b ON b.uid=a.uid")
                ->where($map)->count();
        $map['a.status'] = 1;
        $count1 = M('')->table("{$db_prefix}event_user AS a ")
                ->join("{$db_prefix}user AS b ON b.uid=a.uid")
                ->where($map)->count();
        $map['a.status'] = 2;
        $count2 = M('')->table("{$db_prefix}event_user AS a ")
                ->join("{$db_prefix}user AS b ON b.uid=a.uid")
                ->where($map)->count();
        echo $map['b.year'].' 报名未通过：'.$count0.' 报名未签到：'.$count1.' 签到：'.$count2;
    }
    public function ydb(){
            $a = 'S1234567';
        $send_data = $a;
        $address = '127.0.0.1';
        $service_port = 35433;
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket < 0) {
            die('1');
        }
        $result = socket_connect($socket, $address, $service_port);
        if ($result < 0) {
            die('2');
        }
        //发送命令
        $in = $send_data;
        socket_write($socket, $in, strlen($in));
        $out = socket_read($socket, 2048);
        socket_close($socket);
        var_dump($out);
    }
    public function ccb(){
        $a = 'POSID=081276692&BRANCHID=322000000&ORDERID=2014010709325133654&PAYMENT=0.01&CURCODE=01&REMARK1=33654&REMARK2=&ACC_TYPE=12&SUCCESS=Y&TYPE=1&REFERER=http://suda.pocketuni.lo/index.php&CLIENTIP=58.210.175.67&SIGN=b5a0170631f0cfcfad4e474c62e14096bbf656669d766cae987bcbd13a685bfa737bd05ca25bc9572c2ef0362f02f389b6acaaed4d5cf9d3b912cb0d662d6087881987a641db78d6f05ebe3536f62893297a16443ab7ed7365d7f6aac99783764e84484e16b656ee3b25644d91fc1caa7cdcb18061fc338a2468faa237d598a3';
        $send_data = $a."\n";
        $address = '127.0.0.1';
        $service_port = 35432;
        $socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
        if ($socket < 0) {
            die('1');
        }
        $result = socket_connect($socket, $address, $service_port);
        if ($result < 0) {
            die('2');
        }
        //发送命令
        $in = $send_data;
        socket_write($socket, $in, strlen($in));
        $out = socket_read($socket, 2048);
        socket_close($socket);
        var_dump(substr($out, 0, 2));
    }

    //清理数据库
    public function cleanDb(){
        set_time_limit(0);
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';

        // 移走ts_event_vote过期投票数据
//        $table = 'ts_event_vote_201412';
//        $res = M('')->query("select TABLE_NAME from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA='2012xyhui' and TABLE_NAME='$table';");
//        if(!$res){
//                $sql = "CREATE TABLE `$table` (
//`id` mediumint(5) NOT NULL AUTO_INCREMENT,
//  `eventId` int(11) NOT NULL,
//  `mid` mediumint(5) NOT NULL DEFAULT '0',
//  `pid` mediumint(5) NOT NULL DEFAULT '0',
//  `cTime` int(11) unsigned DEFAULT NULL,
//  `status` tinyint(1) unsigned NOT NULL DEFAULT '1',
//  PRIMARY KEY (`id`)
//    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
//        M('')->query($sql);
//        }
//
//        $eids = M('event')->field('id')->where('isTicket=1')->findAll();
//        $eids = getSubByKey($eids, 'id');
//        $useEids = array();
//        foreach($eids as $eid){
//            if(M('event_vote')->where("eventId=$eid")->field('eventId')->find()){
//                $useEids[] = $eid;
//            }
//        }
//        $notin = implode(',', $useEids);
//        M('')->query("INSERT INTO $table select * from ts_event_vote where eventId not in($notin)");
//        M('')->query("delete from ts_event_vote where eventId not in($notin)");
//        die;

//        $month1ago = strtotime('-1 month');
//        $sql = "select title from ts_message_list where list_id=83231 ";
//        $res = M('')->query($sql);
//        var_dump($res);die;

        //ts_login_record表清理
//        $month3ago = strtotime('-3 month');
//        $sql = "delete FROM `ts_login_record` WHERE `ctime`<$month3ago";
//        M('')->query($sql);
//        $sql = "ALTER TABLE `ts_login_record` DROP `login_record_id`";
//        M('')->query($sql);
//        $sql = "ALTER TABLE `ts_login_record` ADD `login_record_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;";
//        M('')->query($sql);
//        $sql = "TRUNCATE TABLE `ts_admin_log`";
//        M('')->query($sql);
//        $sql = "TRUNCATE TABLE `ts_myop_friendlog`;";
//        M('')->query($sql);
//        //通知管理
//        $month1ago = strtotime('-1 month');
//        $sql = "delete from ts_notify where ctime<$month1ago;";// 删除一个月前的
//        M('')->query($sql);
//        $sql = "delete from ts_notify where is_read=1;";//删除已读的
//        M('')->query($sql);
//        $sql = "ALTER TABLE `ts_notify` DROP `notify_id`;";
//        M('')->query($sql);
//        $sql = "ALTER TABLE `ts_notify` ADD `notify_id` INT( 11 ) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY FIRST;";
//        M('')->query($sql);
//        //申请加入校园部落
//        $month1ago = strtotime('-1 month');
//        $sql = "select count(1) as count from ts_message_list where title like '申请加入校园部落%' and mtime<$month1ago";
//        $counts = M('')->query($sql);
//        $count = ($counts[0]['count']);
//        var_dump($count);
//        $limit = 10000;
//        $start = 0;
//        while($start < $count){
//            $sql = "select list_id from ts_message_list where title like '申请加入校园部落%' and mtime<$month1ago LIMIT $start,$limit";
//            $listIds = M('')->query($sql);
//            $listId = getSubByKey($listIds, 'list_id');
//            $map = array();
//            $map['list_id'] = array('in',$listId);
//            $a = M('message_member')->where($map)->delete();
//            if($a){
//                if(M('message_content')->where($map)->delete()){
//                    M('message_list')->where($map)->delete();
//                    $start += $limit;
//                    echo $start.' done ';
//                }
//            }  else {
//                $start = $count;
//            }
//        }
//        //申请加入社团
//        $sql = "select list_id from ts_message_list where title like '申请加入社团%' and mtime<$month1ago";
//        $listIds = M('')->query($sql);
//        $listIds = getSubByKey($listIds, 'list_id');
//        $map = array();
//        $map['list_id'] = array('in',$listIds);
//        M('message_list')->where($map)->delete();
//        M('message_member')->where($map)->delete();
//        M('message_content')->where($map)->delete();
        //邀您加入社团
//        $sql = "select list_id from ts_message_list where title like '邀您加入%' and mtime<$month1ago";
//        $listIds = M('')->query($sql);
//        $listIds = getSubByKey($listIds, 'list_id');
//        $map = array();
//        $map['list_id'] = array('in',$listIds);
//        M('message_list')->where($map)->delete();
//        M('message_member')->where($map)->delete();
//        M('message_content')->where($map)->delete();
    }

    public function userExcel() {
        set_time_limit(0);
        $map['sid'] = 402;
        $year = array('14');
        $map['year'] = array('in', $year);
        $map['is_init'] = 0;
        $list = M('user')
                ->where($map)
                ->field('realname,email,uid,year,major,sex')
                ->select();
        foreach ($list as $k => $v) {
            $list[$k]['uid'] = tsGetSchoolByUid($v['uid']);
            $list[$k]['email'] = "'".getUserEmailNum($v['email']);
            $list[$k]['sex'] = $v['sex'] == 1 ? '男' : '女';
        }
        $arr = array('姓名', '学号', '院系', '年级', '专业', '性别');
        array_unshift($list, $arr);
        $title = '文正14未初始化';
        service('Excel')->export2($list,$title);
    }

    //到处所有学校院系
    public function schoolyx() {
        set_time_limit(0);
        $list = M('School')->where('pid!=0 and pid!=659 and pid!=1950')->field('id,title,pid')->order('pid')->select();
        foreach($list as $k=>&$val){
            $ps = M('School')->getField('title','id='.$val['pid']);
            if(!$ps){
                unset($list[$k]);
            }
            $val['ps'] = $ps;
            $val  =array_reverse($val);
            $map['sid1'] = $val['id'];
            $num = M('User')->where($map)->count();
            if($num==0){
                unset($list[$k]);
            }
            $val['num'] = $num;
            unset($val['pid']);
            unset($val['id']);
        }
        $arr = array('学校', '院系', '人数');
        array_unshift($list, $arr);
        $title = '全部学校院系';
        service('Excel')->export2($list,$title);

    }

    //充值统计
    public function min() {
        set_time_limit(0);
        $types = M('')->query('select distinct(typeName) as type from ts_money_in');
        foreach ($types as $v) {
            $type = $v['type'];
            $m = M('')->query("select sum(logMoney) as money from ts_money_in where typeName='$type'");
            $v['money'] = $m[0]['money']/100;
            $count = M('')->query("select count(1) as count from ts_money_in where typeName='$type'");
            $v['count'] = $count[0]['count'];
            $list[]  = $v;
        }
        $arr = array('充值类别','充值金额','人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'PU币充值');
    }

    //支付统计
    public function mout() {
        set_time_limit(0);
        $types = array('旅游','摇一摇','一元梦想','众志成城','爱心校园');
        foreach ($types as $v) {
            $row['type'] = $v;
            $m = M('')->query("select sum(out_money) as money from ts_money_out where out_title like '$v%'");
            $row['money'] = $m[0]['money']/100;
            $list[]  = $row;
        }
        $arr = array('使用类别','使用金额');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'PU币使用');
    }

    //摇一摇统计
    public function yytj() {
        set_time_limit(0);
        $list = M('y_tj')->field('day, free_times, one_times, two_times, moneyIn, five_times, moneyOut')->findAll();
        foreach ($list as &$v) {
            $v['one_times'] = $v['one_times']+$v['two_times']+$v['five_times'];
            $v['two_times'] = '无法统计';
            $v['moneyIn'] = ($v['moneyIn']-$v['moneyOut'])/100;
            unset($v['five_times']);
            unset($v['moneyOut']);
        }
        $arr = array('日期','免费摇一摇次数','充值摇一摇次数','参与人数','盈亏');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'摇一摇统计');
    }
    //ALTER TABLE `ts_y_record_201412` ADD INDEX ( `sid`,`day` );
    public function yy12() {
        set_time_limit(0);
        $schools = M('school')->field('id,title')->where("pid=0")->findAll();
        $dao12 = M('y_record_201412');
        $dao01 = M('y_record_201501');
        $list = array();
        foreach ($schools as $school) {
            $row = array();
            $row[] = $school['title'];
            $sid = $school['id'];
            $row[] = $this->_yy12Count1($sid, '2014-12-23', $dao12);
            $row[] = $this->_yy12Count2($sid, '2014-12-23', $dao12);
            $row[] = $this->_yy12Count1($sid, '2014-12-24', $dao12);
            $row[] = $this->_yy12Count2($sid, '2014-12-24', $dao12);
            $row[] = $this->_yy12Count1($sid, '2014-12-25', $dao12);
            $row[] = $this->_yy12Count2($sid, '2014-12-25', $dao12);
            $row[] = $this->_yy12Count1($sid, '2014-12-26', $dao12);
            $row[] = $this->_yy12Count2($sid, '2014-12-26', $dao12);
            $row[] = $this->_yy12Count1($sid, '2014-12-27', $dao12);
            $row[] = $this->_yy12Count2($sid, '2014-12-27', $dao12);
            $row[] = $this->_yy12Count1($sid, '2014-12-28', $dao12);
            $row[] = $this->_yy12Count2($sid, '2014-12-28', $dao12);
            $row[] = $this->_yy12Count1($sid, '2014-12-29', $dao12);
            $row[] = $this->_yy12Count2($sid, '2014-12-29', $dao12);
            $row[] = $this->_yy12Count1($sid, '2014-12-30', $dao12);
            $row[] = $this->_yy12Count2($sid, '2014-12-30', $dao12);
            $row[] = $this->_yy12Count1($sid, '2014-12-31', $dao12);
            $row[] = $this->_yy12Count2($sid, '2014-12-31', $dao12);
            $row[] = $this->_yy12Count1($sid, '2015-01-01', $dao01);
            $row[] = $this->_yy12Count2($sid, '2015-01-01', $dao01);
            $row[] = $this->_yy12Count1($sid, '2015-01-02', $dao01);
            $row[] = $this->_yy12Count2($sid, '2015-01-02', $dao01);
            $row[] = $this->_yy12Count1($sid, '2015-01-03', $dao01);
            $row[] = $this->_yy12Count2($sid, '2015-01-03', $dao01);
            $list[] = $row;
        }
        $arr = array('学校','12.23',' ','12.24',' ','12.25',' ','12.26',' ','12.27',' ','12.28',' ','12.29',' ','12.30'
            ,' ','12.31',' ','01.01',' ','01.02',' ','01.03',' ');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'摇一摇统计1223-0103',6);
    }
    private function _yy12Count1($sid,$day,$dao){
        $map['sid'] = $sid;
        $map['day'] = $day;
        $res = $dao->where($map)->field('count(distinct(uid)) as count')->find();
        return $res['count'];
    }
    private function _yy12Count2($sid,$day,$dao){
        $map['sid'] = $sid;
        $map['day'] = $day;
        return $dao->where($map)->count();
    }

    //三下乡投票人数
    // ALTER TABLE `ts_sj_vote` ADD INDEX ( `sid` );
    // ALTER TABLE ts_sj_vote DROP INDEX sid;
    public function sjvote() {
        set_time_limit(0);
        $list = M('school')->field('id,title')->where('pid=0')->findAll();
        foreach ($list as $k=>&$v) {
            $sid = $v['id'];
            $d = M('')->query("select count(distinct(mid)) as count from ts_sj_vote_2014 where sid=$sid");
            $v['y14'] = $d[0]['count'];
            $d = M('')->query("select count(distinct(mid)) as count from ts_sj_vote where sid=$sid");
            $v['y13'] = $d[0]['count'];
            unset($v['id']);
            if($v['y14']==0 && $v['y13']==0){
                unset($list[$k]);
            }
        }
        $arr = array('学校名称','2014年参与投票人数','2013年参与投票人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'三下乡统计');
    }

    // 淮阴工学院
    public function hyg() {
        set_time_limit(0);
        $schools = M('')->query('select id,title from ts_school where pid=527');
        $list = array();
        foreach ($schools as $school) {
            $sid1 = $school['id'];
            $row = array();
            $row['title'] = $school['title'];
            //14级
            $groups = M('')->query("select id,name,membercount from ts_group where sid1=$sid1 and year=14;");
            if($groups){
                foreach ($groups as $group) {
                    $gid = $group['id'];
                    $row['year'] = '14';
                    $row['name'] = $group['name'];
                    $row['count'] = $group['membercount'];
                    $uids = $this->_guids($gid);
                    $row['init'] = $this->_jhrs($uids);
                    $row['events'] = $this->_groupEvents($gid);
                    $row['finish'] = $this->_finishEvents($gid);
                    $row['sj'] = $this->_sxx($uids);
                    $list[] = $row;
                }
            }
            //14级
            $groups = M('')->query("select id,name from ts_group where sid1=$sid1 and year!='14';");
            if($groups){
                foreach ($groups as $group) {
                    $gid = $group['id'];
                    $row['year'] = '其它年级';
                    $row['name'] = $group['name'];
                    $uids = $this->_guids($gid);
                    $row['count'] = count($uids);
                    $row['init'] = $this->_jhrs($uids);
                    $row['events'] = $this->_groupEvents($gid);
                    $row['finish'] = $this->_finishEvents($gid);
                    $row['sj'] = $this->_sxx($uids);
                    $list[] = $row;
                }
            }
        }
        $arr = array('学院','年级','部落','部落人数','激活人数','总发起活动数','总完结活动数','参与三下乡投票人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'问卷调查统计');
    }

    private function _sxx($uids){
        if(empty($uids)){
            return 0;
        }
        $map['mid'] = array('in',$uids);
        $res = M('sj_vote_2014')->where($map)->field('count(distinct(mid)) as count')->find();
        return $res['count'];
    }

    private function _finishEvents($gid){
        $map['gid'] = $gid;
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['school_audit'] = 5;
        return M('event')->where($map)->count();
    }

    private function _groupEvents($gid){
        $map['gid'] = $gid;
        $map['isDel'] = 0;
        $map['status'] = 1;
        return M('event')->where($map)->count();
    }

    private function _jhrs($uids){
        if(empty($uids)){
            return 0;
        }
        $map['uid'] = array('in',$uids);
        $map['is_init'] = 1;
        return M('user')->where($map)->count();
    }

    private function _guids($gid){
        $guids = M('')->query("select uid from ts_group_member where status=1 and gid=$gid;");
        return getSubByKey($guids, 'uid');
    }

    //团省委活动列表
    public function provEvent(){
        set_time_limit(0);
        $list = M('')->query('select id,title,sTime,joinCount from ts_event where status=1 and is_prov_event=1');
        $sj = M('')->query('select id,title,sTime,joinCount from ts_event where id in (42047,42053,42055)');
        foreach ($sj as $v) {
            $list[] = $v;
        }
        foreach ($list as &$v) {
            $eventId = $v['id'];
            $v['sTime'] = date('Y-m-d',$v['sTime']);
            if($eventId==1096 || $eventId==1097 || $eventId==1098){
                $count = M('')->query("select count(distinct(mid)) as count from ts_sj_vote where eventId=$eventId;");
                $v['vote'] = $count[0]['count'];
            }elseif($eventId==42047 || $eventId==42053 || $eventId==42055){
                $count = M('')->query("select count(distinct(mid)) as count from ts_sj_vote_2014 where eventId=$eventId;");
                $v['vote'] = $count[0]['count'];
            }else{
                $count = M('')->query("select count(distinct(mid)) as count from ts_event_vote where eventId=$eventId;");
                $v['vote'] = $count[0]['count'];
            }
        }
        $arr = array('ID','名称','活动时间','参加人数','投票人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'团省委活动列表');
    }
    //全省前20大活动列表
    public function topEvent(){
        set_time_limit(0);
        $list = M('')->query('select is_school_event,title,joinCount,id,typeId from ts_event where status=1 and is_prov_event=0 and is_school_event!=659 order by joinCount desc limit 25');
        $daoType = D('EventType','event');
        foreach ($list as &$v) {
            $eventId = $v['id'];
            $v['is_school_event'] = tsGetSchoolName($v['is_school_event']);
            $v['id'] = M('event_user')->where("status=2 and eventId=$eventId")->count();
            $v['typeId'] = $daoType->getTypeName($v['typeId']);
        }
        $arr = array('活动发学校','活动名称','报名人数','签到人数','活动类型');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'全省前20大活动列表');
    }
    //苏州大学、南京工业大学、江苏大学、无锡商院前10大活动列表（活动发起部门、活动名称、报名人数、签到人数、活动类型）
    public function topEvent2(){
        set_time_limit(0);
        $sids = array(1,480,551,525);
        $sList = array();
        $daoType = D('EventType','event');
        foreach($sids as $sid){
            $list = M('')->query("select is_school_event,sid,title,joinCount,id,typeId from ts_event where is_school_event=$sid and status=1 and is_prov_event=0 and is_school_event!=659 order by joinCount desc limit 10");
            foreach ($list as $v) {
                $eventId = $v['id'];
                $v['is_school_event'] = tsGetSchoolName($v['is_school_event']);
                if($v['sid']>0){
                    $v['sid'] = tsGetSchoolName($v['sid']);
                }elseif($v['sid']<0){
                    $s = 0-$v['sid'];
                    $v['sid'] = M('school_orga')->getField('title', "id=$s");
                }
                $v['id'] = M('event_user')->where("status=2 and eventId=$eventId")->count();
                $v['typeId'] = $daoType->getTypeName($v['typeId']);
                $sList[] = $v;
            }
        }
        $arr = array('活动发起学校','活动发起部门','活动名称','报名人数','签到人数','活动类型');
        array_unshift($sList, $arr);
        service('Excel')->export2($sList,'代表学校前10大活动列表');
    }
    //每日微博数
    public function weibo(){
        set_time_limit(0);
        $time1 = M('')->query('select ctime from ts_weibo order by ctime asc limit 1');
        $stime = $time1[0]['ctime'];
        $sday = date('Y-m-d',$stime);
        $today = date('Y-m-d');
        $now = time();
        $checkS = $stime;
        $checkE = strtotime('+1 day', strtotime($sday));
        while($checkS<$now){
            $row['day'] = date('Y-m-d',$checkS);
            $row['count'] = M('weibo')->where("ctime>$checkS and ctime<$checkE")->count();
            $checkS = $checkE;
            $checkE = strtotime('+1 day', $checkE);
            $list[] = $row;
        }
        $arr = array('日期','微博数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'每日微博数');
    }
    //吐泡泡每日发数据
    public function paopao(){
        set_time_limit(0);
        $time1 = M('')->query('select cTime as ctime from ts_forum order by cTime asc limit 1');
        $stime = $time1[0]['ctime'];
        $sday = date('Y-m-d',$stime);
        $today = date('Y-m-d');
        $now = time();
        $checkS = $stime;
        $checkE = strtotime('+1 day', strtotime($sday));
        while($checkS<$now){
            $row['day'] = date('Y-m-d',$checkS);
            $row['count'] = M('forum')->where("cTime>$checkS and cTime<$checkE")->count();
            $checkS = $checkE;
            $checkE = strtotime('+1 day', $checkE);
            $list[] = $row;
        }
        $arr = array('日期','泡泡数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'每日吐泡泡数');
    }
    //我的青春印迹
    public function qc(){
        set_time_limit(0);
        $id = 25150;
        $res = M('')->query("select mid,count(1) as count from ts_event_vote where eventId=$id group by mid order by cTime asc ");
        foreach ($res as $v) {
            $row = M('user')->where('uid='.$v['mid'])->field('email,realname,sid,sid1')->find();
            if($row){
                $row['email'] = "'".getUserEmailNum($row['email']);
                $row['sid'] = tsGetSchoolName($row['sid']);
                $row['sid1'] = tsGetSchoolName($row['sid1']);
                $row['count'] = $v['count'];
                $list[] = $row;
            }
        }
        $arr = array('学号','姓名','学校','院系','投票数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'qc-爱心秀');
    }
    //注册用户、初始化用户
    public function initCount(){
        set_time_limit(0);
        $list = M('')->query("select title,id from ts_school where pid=0 order by display_order asc ");
        foreach ($list as &$v) {
            $sid = $v['id'];
            $v['id'] = M('user')->where("event_level=20 and sid=$sid")->count();
            $v['init'] = M('user')->where("event_level=20 and is_init=1 and sid=$sid")->count();
        }
        $arr = array('学校','注册用户数','已验证用户数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'注册-已验证用户数');
    }
    //学分排行
    public function eventCredit(){
        set_time_limit(0);
        $list = M('')->query("select sid1,major,year,email,realname,school_event_credit from ts_user where school_event_credit>0 and sid=597 order by school_event_credit desc");
        foreach ($list as &$v) {
            $v['sid1'] = tsGetSchoolName($v['sid1']);
            $v['email'] = "'".getUserEmailNum($v['email']);
        }
        $arr = array('院系','专业','年级','学号','姓名','学分');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'南京审计学院学生学分排行榜');
    }
    //团市委
    public function tsw(){
        set_time_limit(0);
        $res = M('')->query("select id,title from ts_school where pid=0 and title like '%团市委' order by display_order asc");
        $list = array();
        foreach ($res as $v) {
            $row['sid'] = $v['title'];
            $sid = $v['id'];
            $sid1 = M('')->query("select id,title from ts_school where pid=$sid order by display_order asc");
            foreach ($sid1 as $k) {
                $ssid = $k['id'];
                $row['sid1'] = $k['title'];
                $row['count'] = M('user')->where("event_level=20 and sid1=$ssid")->count();
                $list[] = $row;
            }
        }
        $arr = array('团市委','学院','学生人数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'团市委学院');
    }
    //校对部落人数
    public function jdgroup(){
        set_time_limit(0);
        $list = M('')->query("select id,membercount,school from ts_group where is_del=0 order by id asc");
        foreach ($list as $v) {
            $gid = $v['id'];
            $count = M('group_member')->where("level>0 and gid=$gid")->count();
            if($count != $v['membercount']){
                $res = tsGetSchoolName($v['school']);
                $res .= "gid=$gid <br/>";
                echo $res;
                M('')->query("update ts_group set membercount=$count where id=$gid;");
            }
        }
        die('finish');
    }
    public function yydj(){
        set_time_limit(0);
        $pids = array('14893'=>'文学系','14894'=>'外语系','14895'=>'艺术系','14896'=>'经济系（一）','14897'=>'经济系（二）','14898'=>'管理系','14899'=>'电子信息系','14900'=>'光电技术系','14901'=>'机电工程系','14902'=>'计算科学系','14903'=>'法学系','14904'=>'城市轨道交通系');
        $list = M('')->query("select b.email,b.realname,b.sid1,pid,a.cTime from ts_event_vote as a left join ts_user as b on a.mid=b.uid where eventId=43301;");
        foreach ($list as &$v) {
            $v['email'] = "'".getUserEmailNum($v['email']);
            $v['sid1'] = tsGetSchoolName($v['sid1']);
            $v['pid'] = $pids[$v['pid']];
            $v['cTime'] = date('Y-m-d H:i',$v['cTime']);
        }
        $arr = array('学号','姓名','院系','所投系科','时间');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'第十二届新生英语短剧大赛');
    }
    public function checkCx(){
        die;
        $sql = 'select uid from ts_user where sid=525 and uid>=1786201 and uid<=1786200';
        $res = M('')->query($sql);
        $start = strtotime('2013-12-27');
        foreach($res as $v){
            $uid = $v['uid'];
            $sql = "select a.status,a.eventId from ts_event_user as a left join ts_event as b on a.eventId=b.id where a.status!=0 and a.uid=$uid and b.school_audit=5 and b.fTime>$start";
            $eu = M('')->query($sql);
            $total = 0;
            $attend = 0;
            if($eu){
                foreach ($eu as $w) {
                    if($w['eventId']!=52150 && $w['eventId']!=52182 && $w['eventId']!=52187){
                        $total+=1;
                        if($w['status']==2){
                            $attend+=1;
                        }
                    }
                }
                $cx = M('')->query("select total,attend from ts_event_cx where uid=$uid");
                $info = true;
                if(!$cx){
                    $info = false;
                    $insert = "INSERT INTO ts_event_cx (`uid`,`total`,`attend`) values ('$uid','$total','$total');";
                    M('')->query("INSERT INTO ts_event_cx (`uid`,`total`,`attend`) values ('$uid','$total','$total');");
                    var_dump($insert);
                }else{
                    if($cx[0]['total']!=$total || $cx[0]['attend']!=$attend){
                        $info = false;
                        M('')->query("UPDATE `ts_event_cx` SET `total`=$total,`attend`=$attend WHERE uid=$uid;");
                    }
                }
                if(!$info){
                    echo "<br/> uid=$uid,total=$total,attend=$attend <br/>";
                }
            }
        }
    }
    //充值
    public function pucz(){
        die;
        $uid = 1687698;
        $money = 20*100;
        Model('Money')->moneyIn($uid, $money, '活跃用户奖励');
        S('S_userInfo_' . $uid, null);
    }
    public function order(){
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $url = 'https://pocketuni.net/order_test';
        $order['order_id'] = '2014112400001';
        $order['price'] = 10.1;
        $order['name'] = '充值名称';
        $order['account'] = '';
        $order['number'] = '充值数量';
        $order['ctime'] = time();
        $order['order_url'] = 'http://localhost/phpmyadmin/index.php?db=2012xyhui&table=ts_order_7881_test&target=sql.php&token=f8fd183b63e4f6b5380bf1e46e516952';
        $param['order'] = json_encode($order);
        $param['timestamp'] = time();
        $param['tocken'] = '485efc4f3cc2dcbaf8bee97328909bd3';
        $param['uid'] = 2029423;
        $key = 'JH4NqXHyEjVfcjNtNejbfUV9F6capFNmjnYyZVPLq6zV3S2pu9ZQhzS8MXfX4E4pqTqqGSqMsuyw89M9FuLCXPDNG4VhMF2nUFaUsUq5rGmYZaPc6aYjBRpznz9LbTe7';
        require_once (SITE_PATH . '/addons/libs/pupay/payVerify.function.php');
        $param['sign'] = substr(sha1(substr($key, 0, 32).createLinkstring($param).substr($key, 32)),1,32);
        $res = request_post($url, $param);
        var_dump($res);
    }
    public function orderlo(){
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $url = 'http://pocketuni.lo/order_test';
        $order['order_id'] = '2014112400001';
        $order['price'] = 9.70;
        $order['name'] = '充值名称';
        $order['account'] = '';
        $order['number'] = '充值数量';
        $order['ctime'] = time();
        $order['order_url'] = 'http://localhost/phpmyadmin/index.php?db=2012xyhui&table=ts_order_7881_test&target=sql.php&token=f8fd183b63e4f6b5380bf1e46e516952';
        $param['order'] = json_encode($order);
        $param['timestamp'] = time();
        $param['tocken'] = '43f196a3c349e42fd0b2135f9ad56b18';
        $param['uid'] = 33654;
        $key = 'JH4NqXHyEjVfcjNtNejbfUV9F6capFNmjnYyZVPLq6zV3S2pu9ZQhzS8MXfX4E4pqTqqGSqMsuyw89M9FuLCXPDNG4VhMF2nUFaUsUq5rGmYZaPc6aYjBRpznz9LbTe7';
        require_once (SITE_PATH . '/addons/libs/pupay/payVerify.function.php');
        $param['sign'] = substr(sha1(substr($key, 0, 32).createLinkstring($param).substr($key, 32)),1,32);
        $res = request_post($url, $param);
        var_dump($res);
    }
    public function pupay(){
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $url = 'https://pocketuni.net/pupay_test';
        $param['order_id'] = '2014112400003';
        $param['timestamp'] = time();
        $param['tocken'] = '485efc4f3cc2dcbaf8bee97328909bd3';
        $param['uid'] = 2029423;
        $key = 'JH4NqXHyEjVfcjNtNejbfUV9F6capFNmjnYyZVPLq6zV3S2pu9ZQhzS8MXfX4E4pqTqqGSqMsuyw89M9FuLCXPDNG4VhMF2nUFaUsUq5rGmYZaPc6aYjBRpznz9LbTe7';
        require_once (SITE_PATH . '/addons/libs/pupay/payVerify.function.php');
        $param['sign'] = substr(sha1(substr($key, 0, 96).createLinkstring($param).substr($key, 96)),6,32);
        $res = request_post($url, $param);
        var_dump($res);
    }
    public function restpu(){
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $url = 'https://pocketuni.net/restpu_test';
        $param['timestamp'] = time();
        $param['tocken'] = 'e30913f9e8b26046fb1ec1862950f072';
        $param['uid'] = 2044278;
        $key = 'JH4NqXHyEjVfcjNtNejbfUV9F6capFNmjnYyZVPLq6zV3S2pu9ZQhzS8MXfX4E4pqTqqGSqMsuyw89M9FuLCXPDNG4VhMF2nUFaUsUq5rGmYZaPc6aYjBRpznz9LbTe7';
        require_once (SITE_PATH . '/addons/libs/pupay/payVerify.function.php');
        $param['sign'] = substr(sha1(substr($key, 0, 64).createLinkstring($param).substr($key, 64)),2,32);
        $res = request_post($url, $param);
        var_dump($res);
    }
    public function status(){
        date_default_timezone_set('PRC');
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $url = 'https://pocketuni.net/change_status_test';
        $param['new_status'] = 2002;
        $param['order_id'] = '125022814251093378784571';
        $param['timestamp'] = time();
        $param['tocken'] = '6f5c24e9f166a5e3439638abb22ad28b';
        $param['uid'] = 33655;
        $key = 'JH4NqXHyEjVfcjNtNejbfUV9F6capFNmjnYyZVPLq6zV3S2pu9ZQhzS8MXfX4E4pqTqqGSqMsuyw89M9FuLCXPDNG4VhMF2nUFaUsUq5rGmYZaPc6aYjBRpznz9LbTe7';
        require_once (SITE_PATH . '/addons/libs/pupay/payVerify.function.php');
        $param['sign'] = substr(sha1(substr($key, 0, 16).createLinkstring($param).substr($key, 16)),4,32);
        $res = request_post($url, $param);
        var_dump($res);
    }
    public function groupEvents(){
        $sid = 640;
        $sql = 'select a.name,count(1) as count from ts_group as a left join ts_event as b on a.id=b.gid where a.school=640 and a.is_del=0 and b.status=1 and b.isDel=0 group by a.id';
        $list = M('')->query($sql);
        $arr = array('部落名称','发起活动数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'淮海工学院部落发起活动数');
    }
    public function surveySchool(){
        $schools = model('Schools')->makeLevel0Tree();
        foreach ($schools as $v) {
            $table = 'ts_survey_school_'.$v['id'];
            M('')->query("DROP TABLE $table");
        }
    }
    public function follow(){
        $list = M('')->table('ts_weibo_follow as a')->join('ts_weibo_follow_group_link as b on a.follow_id=b.follow_id')
                ->field('a.follow_id,a.uid,a.fid,b.follow_group_id')->where('a.type=0 and b.follow_group_id is not null')->findAll();
        foreach ($list as $v) {
            $groupId = intval($v['follow_group_id']);
            if($groupId>0){
                M('weibo_follow')->where('follow_id='.$v['follow_id'])->save(array('group_id'=>$groupId));
            }
        }
    }
    /**
     * event_vote
     * event_player ticket
     */
    public function initVote(){
        $eventIds = array();
        $daoVote = M('event_vote');
        $daoPlayer = M('event_player');
        foreach ($eventIds as $eid) {
            $daoVote->where('eventId='.$eid)->delete();
            $daoPlayer->setField('ticket', 0, "ticket>0 and eventId=$eid");
        }
    }
    //回复到某个点
    public function initVoteTo(){
//        $lastTime = strtotime('2015-05-12 08:30');
        $eid = 87788;
        $daoVote = M('event_vote');
        $daoPlayer = M('event_player');

//        $res = M('')->query("select mid from ts_event_vote where status=0 and eventId = $eid group by mid having count(1)<10");
//        $data['status'] = 3;
//        foreach ($res as $v) {
//            $mid = $v['mid'];
//            $daoVote->where("eventId=$eid and mid=$mid")->save($data);
//        }
//        die;
        $player = $daoPlayer->where("status=1 and eventId=$eid")->field('id')->findAll();
        foreach ($player as $v) {
            $pid = $v['id'];
            $cnt = $daoVote->where("status=0 and eventId=$eid and pid=$pid")->count();
            $daoPlayer->setField('ticket', $cnt, "id=$pid");
        }
    }
    //吐泡泡一个月统计
    public function forum(){
        set_time_limit(0);
        $id = intval($_GET['id']);
        if($id<=0){
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo '地址栏请填起始ID： &id=22385';
            die;
        }
        $list = M('')->query("select id,backCount,readCount,praiseCount,uid,sid,cTime from ts_forum where tid=0 and id>=$id order by `id` asc");
        foreach ($list as &$v) {
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['uid'] = getUserRealName($v['uid']);
            $v['cTime'] = date('Y-m-d H:i',$v['cTime']);
        }
        $arr = array('ID','回复数','阅读数','赞','发布者','所属学校','发布时间');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'吐泡泡统计'.$id.'_'.date('Y-m-d'));
    }
    //积分 学分导出
    public function scoreExcel() {
        set_time_limit(0);
        $sid = intval($_GET['sid']);
        $year = t($_GET['year']);
        if($sid<=0 || !$year){
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo 'sid或year未提交';die;
        }
        $map['sid'] = $sid;
        $map['year'] = $year;
        $list = M('user')
                ->where($map)
                ->field('realname,email,uid,year,major,sex,school_event_credit,sex as school_event_score')
                ->findAll();
        if(!$list){
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo '没有数据';die;
        }
        foreach ($list as &$v) {
            $uid = $v['uid'];
            $v['email'] = "'".getUserEmailNum($v['email']);
            $v['sex'] = $v['sex'] == 1 ? '男' : '女';
            $v['school_event_score'] = X('Score')->getUserScore($uid);
            unset($v['uid']);
        }
        $arr = array('姓名', '学号', '年级', '专业', '性别', '学分', '积分');
        array_unshift($list, $arr);
        $title = tsGetSchoolName($sid).'_'.$year;
        service('Excel')->export2($list, $title);
    }
    //suda
    public function suda() {
        set_time_limit(0);
        $sid = 618;
        $schools = M('school')->field('id,title')->where("pid=$sid")->findAll();
        foreach ($schools as $school) {
            $sid1 = $school['id'];
            $row = array();
            $row[] = $school['title'];
            $row[] = M('user')->where('sid1='.$sid1)->count();
            $row[] = M('user')->where('is_init=1 and sid1='.$sid1)->count();
            $map['is_school_event'] = $sid;
            $map['isDel'] = 0;
            $groups = M('group')->where("category=1 and sid1=$sid1")->field('id')->findAll();
            $gids = getSubByKey($groups, 'id');
            if(empty($gids)){
                $row[] = 0;
                $row[] = 0;
            }else{
                $map['gid'] = array('in',$gids);
                $row[] = M('event')->where($map)->count();
                $map['school_audit'] = 5;
                $row[] = M('event')->where($map)->count();
                unset($map['school_audit']);
            }
            $groups = M('group')->where("category=2 and sid1=$sid1")->field('id')->findAll();
            $gids = getSubByKey($groups, 'id');
            if(empty($gids)){
                $row[] = 0;
                $row[] = 0;
            }else{
                $map['gid'] = array('in',$gids);
                $row[] = M('event')->where($map)->count();
                $map['school_audit'] = 5;
                $row[] = M('event')->where($map)->count();
                unset($map['school_audit']);
            }
            $groups = M('group')->where("category=3 and sid1=$sid1")->field('id')->findAll();
            $gids = getSubByKey($groups, 'id');
            if(empty($gids)){
                $row[] = 0;
                $row[] = 0;
            }else{
                $map['gid'] = array('in',$gids);
                $row[] = M('event')->where($map)->count();
                $map['school_audit'] = 5;
                $row[] = M('event')->where($map)->count();
                unset($map['school_audit']);
            }
            $list[] = $row;
        }
        $arr = array('学院', '实名注册人数', '已初始化人数', '社团发起活动数', '完结活动数', '学生部门发起活动数', '完结活动数', '团支部发起活动数', '完结活动数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '江苏开放大学报表201412');
    }
    public function jsdx() {
        set_time_limit(0);
        $list = M('event')->where('is_school_event=551 and isDel=0 and sid<0')->order('joinCount DESC')->limit(10)->field('title,joinCount,sid')->findAll();
        $list2 = M('event')->where('is_school_event=551 and isDel=0 and sid>0')->order('joinCount DESC')->limit(5)->field('title,joinCount,sid')->findAll();
        foreach($list as &$v){
            $v['sid'] = 0-$v['sid'];
            $v['sid'] = M('school_orga')->getField('title', 'id='.$v['sid']);
        }
        foreach($list2 as $k){
            $k['sid'] = M('school')->getField('title', 'id='.$k['sid']);
            $list[] = $k;
        }
        $arr = array('活动', '参加人数','归属组织');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '江苏大学报表2014');
    }
    public function jsdx1() {
        set_time_limit(0);
        $res = M('')->query('select count(1) as count,uid from ts_event_user where usid=551 and status=2 group by uid order by count DESC limit 15');
        foreach($res as $v){
            $row = array();
            $row[] = $v['count'];
            $uid = $v['uid'];
            $user = M('user')->where("uid=$uid")->field('realname,sex,email,sid1,year,major')->find();
            $row[] = $user['realname'];
            $row[] = $user['sex']?'男':'女';
            $row[] = "'".getUserEmailNum($user['email']);
            $row[] = tsGetSchoolName($user['sid1']);
            $row[] = $user['year'];
            $row[] = $user['major'];
            $list[] = $row;
        }
        $arr = array('参加活动次数', '姓名', '性别','学号','院系','年级','专业');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '江苏大学pu之星');
    }
    public function jsdx2() {
        set_time_limit(0);
        $schools = M('school')->field('id,title')->where('pid=551')->findAll();
        foreach($schools as $school){
            $row = array();
            $row[] = $school['title'];
            $sid1 = $school['id'];
            $res = M('')->query("select count(distinct(a.uid)) as count from ts_event_user as a left join ts_user as b on a.uid=b.uid where usid=551 and b.sid1=$sid1");
            $row[] = $res[0]['count'];
            $row[] = M('user')->where("sid1=$sid1")->count();
            $list[] = $row;
        }
        $arr = array('学院', '参与活动学生数', '学院学生总数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '江苏大学学院使用率');
    }
    public function mf(){
        set_time_limit(0);
        $photos = M('makefriends_photo')->field('photoId')->findAll();
        foreach($photos as $photo){
            $pid = $photo['photoId'];
            $attach = getAttach($pid);
            $file = $attach['savepath'] . $attach['savename'];
            $src = SITE_PATH.'/data/uploads/'.$file;
            list($sr_w, $sr_h) = @getimagesize($src);
            $data['w'] = $sr_w;
            $data['h'] = $sr_h;
            M('makefriends_photo')->where('photoId='.$pid)->save($data);
            Mmc('Makefriends_photo_' . $pid, null);
        }
    }
    public function weibosid(){
        set_time_limit(0);
        $daoWeibo = M('weibo');
        $user = $daoWeibo->where('sid=0')->field('uid')->find();
        $daoUser = M('user');
        while($user){
            $uid = $user['uid'];
            $sid = $daoUser->getField('sid', 'uid='.$uid);
            if(!$sid){
                $sid = 99999;
            }
            $daoWeibo->setField('sid', $sid, 'uid='.$uid);
            $user = $daoWeibo->where('sid=0')->field('uid')->find();
        }
    }
    public function cslg() {
        set_time_limit(0);
        $list = M('event')->field('title,gid,sid,joinCount,id,sTime,address,school_audit')->where('isDel=0 and is_school_event=639 and school_audit>1')->findAll();
        $daoUser = M('event_user');
        foreach($list as &$v){
            $v['gid'] = getGroupName($v['gid']);
            $v['sid'] = getEventOrga($v['sid']);
            $v['id'] = $daoUser->where('status=2 and eventId='.$v['id'])->count();
            $v['sTime'] = date('Y-m-d',$v['sTime']);
            $v['school_audit'] = $v['school_audit']==5?'是':'否';
        }
        $arr = array('活动名称','发起组织','归属组织','参与人数','签到人数','活动时间','活动地点','是否完结');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '常熟理工学院活动数据');
    }
    public function love() {
        set_time_limit(0);
        $list = M('')->query("select sid,uid,comment,heart from ts_weibo where isdel=0 and content like '#晒年味#%' order by `weibo_id` DESC");
        foreach ($list as &$v) {
            $v['sid'] = tsGetSchoolName($v['sid']);
            $user = M('user')->where('uid='.$v['uid'])->field('realname')->find();
            $v['uid'] = $user['realname'];
        }
        $arr = array('学校', '姓名', '评论数','赞');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '晒年味');
    }
    public function eventCj() {
        set_time_limit(0);
        $daoUser = M('event_user');
        $list = $daoUser->field('usid,uid,realname')->where('lot=1 and eventId=69689')->findAll();
        foreach($list as &$v){
            $v['usid'] = tsGetSchoolName($v['usid']);
            $email = getUserEmail($v['uid']);
            $v['uid'] = ' '.getUserEmailNum($email);
        }
        $arr = array('学校','学号','姓名');
        array_unshift($list, $arr);
        $arr = array('一等奖','','');
        array_unshift($list, $arr);
        $list[] = array('二等奖','','');
        $list[] = array('学校','学号','姓名');
        $lucky2 = $daoUser->field('usid,uid,realname')->where('lot=2 and eventId=69689')->findAll();
        foreach($lucky2 as $k){
            $row = array();
            $row['usid'] = tsGetSchoolName($k['usid']);
            $email = getUserEmail($k['uid']);
            $row['uid'] = ' '.getUserEmailNum($email);
            $row['realname'] = $k['realname'];
            $list[] = $row;
        }
        $list[] = array('三等奖','','');
        $list[] = array('学校','学号','姓名');
        $lucky3 = $daoUser->field('usid,uid,realname')->where('lot=3 and eventId=69689')->findAll();
        foreach($lucky3 as $k){
            $row = array();
            $row['usid'] = tsGetSchoolName($k['usid']);
            $email = getUserEmail($k['uid']);
            $row['uid'] = ' '.getUserEmailNum($email);
            $row['realname'] = $k['realname'];
            $list[] = $row;
        }
        service('Excel')->export2($list, '抽奖纪录');
    }
    // 批量私信
    public function jgz(){
        die;
        $user = M('eventUser')->where('eventId=69689 and lot=0')->field('uid')->findAll();
        $data['title'] = '坚果座参与奖';
        $data['content'] = t('http://taoquan.taobao.com/coupon/unify_apply.htm?sellerId=1968206982&activityId=212478985 点击链接并登陆自己的淘宝账号即可领取3元代金券。（领取成功后可到淘宝客户端“我的卡券包”内查看，到坚果座旗舰店内消费，代金券将直接显示） 代金券使用时间：2015-1-8到2015-3-31');
        $dao = model('Message');
        foreach ($user as $v) {
            $data['to'] = $v['uid'];
            $dao->postMessage($data, 1709563,false);
        }
    }
    public function wy(){
        $list = M('user_tg')->field('school,email,realname')->findAll();
        $arr = array('学校', '学号', '姓名');
        array_unshift($list, $arr);
        service('Excel')->export2($list, 'PU内部账号');
    }
    //清空相册交友
    public function initmf(){
        die;
        Mmc('Makefriends_weekPhoto',null);
        Mmc('Makefriends_weekUser',null);
        $photos = M('makefriends_photo')->field('photoId')->findAll();
        $daoA = model('Attach');
        foreach ($photos as $v) {
            $pid = $v['photoId'];
            Mmc('Makefriends_photo_' . $pid,null);
            $daoA->deleteAttach($pid,true);
        }

        $today = date('Ymd');
        $users = M('makefriends_user')->field('uid')->findAll();
        foreach ($users as $v) {
            $uid = $v['uid'];
            Mmc('Makefriends_user_' . $uid,null);
            Mmc('Make_friend_login_' . $today . '_' . $uid,null);
        }
        M('')->execute("TRUNCATE TABLE `ts_makefriends_user`");
        M('')->execute("TRUNCATE TABLE `ts_makefriends_usergx`");
        M('')->execute("TRUNCATE TABLE `ts_makefriends_photo`");
        M('')->execute("TRUNCATE TABLE `ts_makefriends_comment`");
        M('')->execute("TRUNCATE TABLE `ts_makefriends_attention`");
        M('')->execute("TRUNCATE TABLE `ts_makefriends_gift`");
        M('')->execute("TRUNCATE TABLE `ts_makefriends_giftcount`");
        M('')->execute("TRUNCATE TABLE `ts_makefriends_praise`");
    }
    public function addsid(){
        die;
        $dao = M('makefriends_gift');
        $list = $dao->where('sid=0')->field('distinct(uid) as uid')->findAll();
        foreach ($list as $v) {
            $uid = $v['uid'];
            $sid = getUserField($uid,'sid');
            $dao->setField('sid', $sid, 'uid='.$uid);
        }
    }
    public function nzy() {
        set_time_limit(0);
        $list = M('')->query("select distinct(uid) as uid from ts_ec_apply where sid=591 and status=1 order by uid asc");
        foreach ($list as $k=>&$v) {
            $uid = $v['uid'];
            $v['uid'] = $k+1;
            $user = M('user')->where('uid='.$uid)->field('email,realname,year,major,sid1')->find();
            $v['email'] = "'".getUserEmailNum($user['email']);
            $v['realname'] = $user['realname'];
            $v['year'] = '20'.$user['year'];
            $v['major'] = $user['major'];
            $v['sid1'] = tsGetSchoolName($user['sid1']);
            $xs = M('')->query("select count(1) as count,type from ts_ec_apply where uid=$uid and status=1 group by type;");
            for($i=1;$i<=8;$i++){
                $key = 'x'.$i;
                $v[$key] = 0;
            }
            foreach($xs as $w){
                $type = $w['type']-12;
                $key = 'x'.$type;
                $v[$key] = $w['count'];
            }
        }
        $arr = array('序号', '学生学号', '学生姓名','所在年级','所在专业','所在学院','第一项','第二项','第三项','第四项','第五项','第六项','第七项','第八项');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '南中医资料导出');
    }
    public function donzy2(){
        set_time_limit(0);
        $page = intval($_POST['page']);
        if($page<=0){
            die;
        }
        $limit = intval($_POST['limit']);
        if($limit<=0){
            $limit = 5000;
        }
        $offset = ($page - 1) * $limit;
        $list = M('ec_apply')->where('sid=591 and status=1')
                ->field('uid,description,type')->limit("$offset,$limit")->order('uid asc,id asc')->select();
        $types = array('13'=>'一','14'=>'二','15'=>'三','16'=>'四','17'=>'五','18'=>'六','19'=>'七','20'=>'八');
        $lastUid = 0;
        foreach ($list as &$v) {
            $uid = $v['uid'];
            if($lastUid!=$uid){
                $user = M('user')->where('uid='.$uid)->field('email,realname,sid1,year,major')->find();
            }
            $v['email'] = "'".getUserEmailNum($user['email']);
            $v['realname'] = $user['realname'];
            $v['sid1'] = tsGetSchoolName($user['sid1']);
            $v['year'] = '20'.$user['year'];
            $v['major'] = $user['major'];
            $v['typename'] = '第'.$types[$v['type']].'项';
            $v['desc'] = $v['description'];
            unset($v['uid']);
            unset($v['description']);
            unset($v['type']);
        }
        $arr = array('学生学号', '学生姓名','所在学院','所在年级','所在专业','第几项','相关说明');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '南中医资料导出格式二');
    }

    public function nzy2(){
        echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
        $list = M('')->query("select count(1) as count from ts_ec_apply where sid=591 and status=1");
        $page = $list[0]['count'];
        $html = '<form method="post" action="/index.php?app=newcomer&mod=Test&act=donzy2">';
        $html .= '<div>一共有'.$page.'条数据，每页：';
        $html .= '<input type="text" value="5000" name="limit" />';
        $html .= '条，你想导出第几页：';
        $html .= '<input type="text" value="1" name="page" />';
        $html .= '<input type="submit" value="导 出"/>';
        $html .= '</div></form>';
        echo($html);
    }
    public function taxiu(){
        die;
        $day = '2015-03-13';
        $time = strtotime('2015-03-13');
        $daoUser = M('makefriends_user');
        $users = $daoUser->where('weekRq>0')->field('uid')->order('weekRq desc')->findAll();
        $now = time();
        foreach ($users as $k=>$v) {
            $rq = 0;
            $uid = $v['uid'];
            //赞
            $sql = "select count(1) as count from ts_makefriends_praise as a left join ts_makefriends_photo as b ".
                    "on a.photoId=b.photoId where b.uid=$uid and a.day>='$day'";
            $res = M('')->query($sql);
            $rq += $res[0]['count'];
            //关注
            $res = M('')->query("select count(1) as count from ts_makefriends_attention where tid=$uid and cTime>=$time");
            $rq += $res[0]['count'];
            //礼物
            $res = M('')->query("select count(1) as count from ts_makefriends_gift where toid=$uid and day>='$day'");
            $rq += $res[0]['count'];
            //评论
            $stime = $time;
            $etime = $time+86399;
            while($stime<$now){
                $sql = "select count(1) as count from ts_makefriends_comment as a left join ts_makefriends_photo as b ".
                        "on a.photoId=b.photoId where b.uid=$uid and a.cTime>=$stime and a.cTime<=$etime";
                $res = M('')->query($sql);
                $dayrq = $res[0]['count'];
                if($dayrq>20){
                    $dayrq  = 20;
                }
                $rq += $dayrq;
                $stime += 86400;
                $etime += 86400;
            }
            $daoUser->setField('weekRq', $rq, 'uid='.$uid);
            Mmc('Makefriends_user_' . $uid, null);
        }
        Mmc('Makefriends_weekUser',null);
    }
    public function tagx(){
        die;
        $daoUser = M('makefriends_user');
        $daoGx = M('makefriends_usergx');
        $users = $daoUser->where('contribution>100')->field('uid,contribution')->findAll();
        foreach ($users as $k=>$v) {
            $uid = $v['uid'];
            $con = $v['contribution'];
            $nocom = $daoGx->getField('sum(total)', "type!='comment' and uid=$uid");
            $limit = $nocom+140;
            if($con>$limit){
                $daoUser->setField('contribution', $limit, 'uid='.$uid);
            }
        }
    }
    //删除学分申请
    public function cleanEcApply(){
        $uid = 1615227;
//        delete from ts_ec_apply where id=91194;
//        update ts_user set school_event_credit=20.00 where uid=$uid;
//
//        select * from ts_tj_eday where uid=1615227;
//        update ts_tj_eday set credit=4.00 where id=901952;
//
//        select * from ts_tj_event1 where tj_uid=1615227;
//        update ts_tj_event1 set credit=4.00 where tj_uid=1615227;
//
//        select * from ts_tj_event2 where tj_uid=1615227;
//        update ts_tj_event2 set credit=4.00 where tj_uid=1615227;
//
//        select * from ts_tj_event3 where tj_uid=1615227;
//        update ts_tj_event3 set credit=4.00 where tj_uid=1615227;

        Mmc('AdminCredit_getGd_12_'.$uid,null);
        S('S_userInfo_'.$uid,null);
    }
    //日登陆不在校
    public function rhno(){
        set_time_limit(0);
        $list = M('')->table("ts_tj_login_201503 AS a ")
                ->join("ts_user AS b ON b.uid=a.uid")
                ->field('b.uid,b.event_level,b.sid,b.year')
                ->where("a.day='2015-03-31' and a.istj=0")->findAll();
        foreach ($list as &$v) {
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['event_level'] = $v['event_level']==20?'学生':'老师';
        }
        $arr = array('UID','学生or老师', '学校','年级');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '日登陆不在校');
    }
    public function sp(){
        $list = M('')->table("ts_event_vote AS a ")
                ->join("ts_user AS b ON b.uid=a.mid")
                ->field('b.sid,b.email,b.realname,b.sid1,b.year')
                ->where("a.pid=21489")->findAll();
        foreach ($list as &$v) {
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['sid1'] = tsGetSchoolName($v['sid1']);
        }
        $arr = array('学校', '学号', '姓名', '院系', '年级');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '刷票2');
    }
    //修改发放过的积分
    public function changeScore(){
        $eid = 79721;
        $minus = 8;
        $daoUser = M('event_user');
        $users = $daoUser->where("eventId=$eid and score>0")->field('uid')->findAll();
        $daoScore = M('user_score');
        foreach ($users as $v) {
            $uid = $v['uid'];
            $daoScore->setDec('score', 'uid='.$uid, $minus);
            var_dump($daoScore->getLastSql());
            die;
            S('ts_S_userInfo_'.$uid, null);
        }
        //$daoUser->setField('score', 2, "eventId=$eid and score>0");
    }
    //参加投票的学生
    public function vote2() {
        set_time_limit(0);
        $eid = 85062;
        $db_prefix = C('DB_PREFIX');
        $list = M('')->table("{$db_prefix}event_vote AS a ")
                ->join("{$db_prefix}user AS b ON b.uid=a.mid")
                ->where('a.eventId='.$eid)->group('a.mid')->field('sid1,email,realname,year,major,mobile')->findAll();
        foreach ($list as $k=>$v) {
            $list[$k]['sid1'] = tsGetSchoolName($v['sid1']);
            $list[$k]['email'] = "'".getUserEmailNum($v['email']);
        }
        $arr = array('院系','学号','姓名','年级','专业','手机');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'参加投票的学生');
    }
    //参加投票的年级人数
    public function vote3() {
        set_time_limit(0);
        $eid = 87788;
        $player = M('eventPlayer')->where("eventId=$eid")->field('id,realname')->order('ticket DESC')->limit(2)->findAll();
        foreach ($player as $k=>$v) {
            $row = array();
            $row[] = $k+1;
            $row[] = $v['realname'];
            $pid = $v['id'];
            for($i=10;$i<=14;$i++){
                $res = M('')->query("select count(1) as count from ts_event_vote as a left join ts_user as b on a.mid=b.uid where pid=$pid and b.year='$i'");
                $row[] = $res[0]['count'];
            }
            $list[] = $row;
        }
        $arr = array('第几名','选手','10级','11级','12级','13级','14级');
        array_unshift($list, $arr);
        $title = M('event')->getField('title',"id=$eid");
        $tarr = array("$title",'','','','','','');
        array_unshift($list, $tarr);
        service('Excel')->export2($list,'参加投票的年级人数');
    }
    //给某选手的投票导出
    public function vote4() {
        set_time_limit(0);
        $pid = 29088; //选手id
        $list = M('')->query("select email,realname,sid1,year,major,a.cTime from ts_event_vote as a left join ts_user as b on a.mid=b.uid where pid=$pid;");
        foreach ($list as &$v) {
            $v['sid1'] = tsGetSchoolName($v['sid1']);
            $v['email'] = "'".getUserEmailNum($v['email']);
            $v['cTime'] = date('Y-m-d H:i', $v['cTime']);
        }
        $arr = array('学号','姓名','院系','年级','专业','投票时间');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'李克迪的被投票统计');
    }
    //参加投票的年级人数
    public function vote5() {
        set_time_limit(0);
        $pid = 25905;
        $list = M('')->query("select sid,count(1) as count from ts_event_vote as a left join ts_user as b on a.mid=b.uid where pid=$pid group by sid;");
        foreach ($list as &$v) {
            $v['sid'] = tsGetSchoolName($v['sid']);
        }
        $arr = array('学校','票数');
        array_unshift($list, $arr);
        $title = M('EventPlayer')->getField('realname',"id=$pid");
        $tarr = array("$title",'');
        array_unshift($list, $tarr);
        service('Excel')->export2($list,'参加投票的学校');
    }
    public function voteDetail(){
        set_time_limit(0);
        $pid = intval($_GET['pid']);
        $from = t($_GET['from']);
        $to = t($_GET['to']);
        $map['a.pid'] = $pid;
        if($from){
            $map['a.cTime'] = array('gt',  strtotime($from));
        }
        if($to){
            $map['a.cTime'] = array('lt',  strtotime($to));
        }
        $list = M('')->table("ts_event_vote AS a ")
                ->join("ts_user AS b ON b.uid=a.mid")
                ->field('b.uid,b.email,b.realname,b.sid,year,a.cTime')
                ->where($map)->findAll();
        foreach ($list as &$v){
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['cTime'] = date('Y-m-d H:i:s',$v['cTime']);
        }
        $arr = array('UID','学号','姓名','学校','年级','投票时间');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'参加投票的学生');
    }
    //http://test.pocketuni.net/index.php?app=newcomer&mod=Test&act=voteDetail2&pid=25919&from=2015-05-12 07:18:09&to=2015-05-12 08:00:01
    public function voteDetail2(){
        set_time_limit(0);
        $pid = intval($_GET['pid']);
        $from = t($_GET['from']);
        $to = t($_GET['to']);
        $map['a.pid'] = $pid;
        if($from){
            $map['a.cTime'] = array('gt',  strtotime($from));
        }
        $list = M('')->table("ts_event_vote AS a ")
                ->join("ts_user AS b ON b.uid=a.mid")
                ->field('b.uid,b.email,b.password,b.is_init,b.sid,a.cTime')
                ->where($map)->findAll();
        foreach ($list as &$v){
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['cTime'] = date('Y-m-d H:i:s',$v['cTime']);
        }
        $arr = array('UID','学号','密码','是否初始化','学校','投票时间');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'参加投票的学生');
    }
    public function groupAdmin(){
        set_time_limit(0);
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $map['school'] = array('not in',array('0,473,659,1950'));
        $list = M('')->table("ts_group AS a ")
                ->join("ts_user AS b ON b.uid=a.uid")
                ->field('a.name,a.cid0,a.activ_num,a.school,b.realname,b.email,b.mobile,b.sex')
                ->order('a.school asc,id asc')
                ->where($map)->findAll();
        $cats = M('group_category')->field('id,title')->findAll();
        foreach($cats as $v){
            $types[$v[id]] = $v['title'];
        }
        foreach ($list as &$v){
            $v['cid0'] = $types[$v[cid0]];
            $v['school'] = tsGetSchoolName($v['school']);
            $v['email'] = "'".getUserEmailNum($v['email']);
            $v['sex'] = $v['sex'] == 1 ? '男' : '女';
        }
        $arr = array('部落名称','分类','活跃度','学校','姓名','学号','手机','性别');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'部落统计');
    }
    public function jsspx(){
        set_time_limit(0);
        $page = intval($_GET['p']);
        $map['a.sid'] = 531;
        $map['a.year'] = 14;
        $map['b.status'] = array('neq',0);
        $limit = 10000;
        $offset = ($page - 1) * $limit;
        $list = M('')->table("ts_user AS a ")
                ->join("ts_event_user AS b ON b.uid=a.uid")
                ->field('a.sid1,a.year,a.major,a.email,a.realname,b.eventId as etime,b.eventId as ename,b.status,b.credit,b.score')
                ->order('a.sid1 asc,a.uid asc')
                ->where($map)->limit("$offset,$limit")->select();
        foreach ($list as $k=>&$v){
            $event = $this->getEvent($v['etime']);
            if(empty($event)){
                unset($list[$k]);
            }else{
                $v['etime'] = date('Y-m-d', $event['sTime']);
                $v['ename'] = $event['title'];
                $v['sid1'] = tsGetSchoolName($v['sid1']);
                $v['email'] = "'".getUserEmailNum($v['email']);
                $v['status'] = $v['status'] == 1 ? '否' : '是';
            }
        }
        $arr = array('院系','年级','专业','学号','姓名','活动时间','活动名称','是否签到','学分','积分');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'江苏食品'.$map['a.year'].'_'.$page);
    }
    public function jssp(){
        set_time_limit(0);
        $page = intval($_GET['p']);
        $year = intval($_GET['y']);
        if(!$page || !$year){
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo '地址栏添加年级和第几页 &y=12&p=1 每页5000条';
            die;
        }
        $map['a.sid'] = 531;
        $map['a.year'] = "$year";
        $map['b.status'] = array('neq',0);
        $limit = 5000;
        $offset = ($page - 1) * $limit;
        $list = M('')->table("ts_user AS a ")
                ->join("ts_event_user AS b ON b.uid=a.uid")
                ->field('a.email,a.uid,a.realname,b.eventId as etype,b.eventId as etime,b.eventId as ename,b.eventId'
                        .',b.eventId as ebm,b.eventId as egroup,b.eventId as egid,b.eventId as eautor,b.status,b.credit,b.score')
                ->order('a.uid asc')
                ->where($map)->limit("$offset,$limit")->select();
        $types = D('EventType','event')->getType($map['a.sid']);
        $categorys = array('1'=>'学生部门','2'=>'团支部', '3'=>'学生社团');
        foreach ($list as $k=>&$v){
            $event = $this->getEvent($v['etime']);
            if(empty($event)){
                unset($list[$k]);
            }else{
                $v['email'] = "'".getUserEmailNum($v['email']);
                $v['etype'] = $types[$event['typeId']];
                $v['etime'] = date('Y-m-d', $event['sTime']);
                $v['ename'] = $event['title'];
                $v['egid'] = $event['gid']>0?$event['gid']:'';
                $v['eautor'] = getUserField($event['uid'], 'realname');
                $v['status'] = $v['status'] == 1 ? '否' : '是';
                $group = $this->getGroup($event['gid']);
                $v['ebm'] = '';
                $v['egroup'] = '';
                if($group){
                    $v['ebm'] = $categorys[$group['category']];
                    $v['egroup'] = $group['name'];
                }
            }
        }
        $arr = array('学号','UID','姓名','活动类别','活动时间','活动名称','活动ID','归属部门','归属部落','归属部落GID','发起人','是否签到','学分','积分');
        array_unshift($list, $arr);
        if($page<10){
            $page = '0'.$page;
        }
        service('Excel')->export2($list,'江苏食品'.$year.'_'.$page);
    }
    public function getEvent($eid){
        $cache = Mmc('jssp_test');
        $obj = array();
        if ($cache !== false) {
            $obj = json_decode($cache, true);
            if(isset($obj[$eid])){
                return $obj[$eid];
            }
        }
        $res = M('event')->where('id=' . $eid)->field('typeId,sTime,title,gid,uid')->find();
        if (!$res) {
            return array();
        }
        $obj[$eid] = $res;
        Mmc('jssp_test', json_encode($obj), 0, 3600 * 2);
        return $res;
    }
    public function getGroup($eid){
        if($eid<=0){
            return array();
        }
        $cache = Mmc('jssp_group');
        $obj = array();
        if ($cache !== false) {
            $obj = json_decode($cache, true);
            if(isset($obj[$eid])){
                return $obj[$eid];
            }
        }
        $res = M('group')->where('id=' . $eid)->field('category,name')->find();
        if (!$res) {
            return array();
        }
        $obj[$eid] = $res;
        Mmc('jssp_group', json_encode($obj), 0, 3600 * 2);
        return $res;
    }
    public function jssp2(){
        set_time_limit(0);
        $page = intval($_GET['p']);
        if(!$page){
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            echo '地址栏添加第几页 &p=1 每页10000条';
            die;
        }
        $map['is_school_event'] = 531;
        $map['isDel'] = 0;
        $limit = 10000;
        $offset = ($page - 1) * $limit;
        $list = M('event')
                ->field('title,id,typeId as type,typeId,gid as ebm,gid,uid,credit,score')
                ->order('id desc')
                ->where($map)->limit("$offset,$limit")->select();
        $types = D('EventType','event')->getType($map['a.sid']);
        $categorys = array('1'=>'学生部门','2'=>'团支部', '3'=>'学生社团');
        foreach ($list as &$v){
            $v['type'] = $types[$v['typeId']];
            $v['gid'] = $v['gid']>0?$v['gid']:'';
            $v['uid'] = getUserField($v['uid'], 'realname');
            $group = $this->getGroup($v['gid']);
            $v['ebm'] = '';
            if($group){
                $v['ebm'] = $categorys[$group['category']];
            }
        }
        $arr = array('活动名称','活动ID','活动类别','类别ID','归属部门（学生组织、团支部、社团）','归属部落GID','发起人','学分','积分');
        array_unshift($list, $arr);
        if($page<10){
            $page = '0'.$page;
        }
        service('Excel')->export2($list,'江苏食品_所有活动'.$page);
    }
    public function jsspx2(){
        set_time_limit(0);
        $page = intval($_GET['p']);
        $map['sid'] = 531;
        $map['year'] = 13;
        $limit = 2;
        $offset = ($page - 1) * $limit;
        $users = M('user')->where($map)->field('sid1,year,major,email,realname,uid')->order('sid1 asc,uid asc')->limit("$offset,$limit")->select();
        $daoEuser = M('event_user');
        $list = array();
        foreach($users as $v){
            $euser = $daoEuser->where("status!=0 and uid=".$v['uid'])->field('eventId,status,credit,score')->findAll();
            if(!$euser){
                continue;
            }
            $row = $v;
            foreach ($euser as $w) {
                $event = $this->getEvent($v['etime']);
            }
        }
        $list = M('')->table("ts_user AS a ")
                ->join("ts_event_user AS b ON b.uid=a.uid")
                ->field('a.sid1,a.year,a.major,a.email,a.realname,b.eventId as etime,b.eventId as ename,b.status,b.credit,b.score')
                ->order('a.sid1 asc,a.uid asc')
                ->where($map)->limit("$offset,$limit")->select();
        foreach ($list as $k=>&$v){
            $event = $this->getEvent($v['etime']);
            if(empty($event)){
                unset($list[$k]);
            }else{
                $v['etime'] = date('Y-m-d', $event['sTime']);
                $v['ename'] = $event['title'];
                $v['sid1'] = tsGetSchoolName($v['sid1']);
                $v['email'] = "'".getUserEmailNum($v['email']);
                $v['status'] = $v['status'] == 1 ? '否' : '是';
            }
        }
        $arr = array('院系','年级','专业','学号','姓名','活动时间','活动名称','是否签到','学分','积分');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'江苏食品');
    }
    public function njhk(){
        set_time_limit(0);
        $map['is_school_event'] = 2136;
        $map['isDel'] = 0;
        $list = M('event')
                ->field('title,uid,gid,sid,cTime,sTime,audit_uid,audit_uid2,credit,score,joinCount,id,school_audit')
                ->order('id desc')
                ->where($map)->select();
        $daoUser = M('event_user');
        foreach($list as &$v){
            $v['uid'] = getUserField($v['uid'], 'realname');
            $v['audit_uid'] = getUserField($v['audit_uid'], 'realname');
            $v['audit_uid2'] = getUserField($v['audit_uid2'], 'realname');
            $v['gid'] = getGroupName($v['gid']);
            $v['sid'] = getEventOrga($v['sid']);
            $v['id'] = $daoUser->where('status=2 and eventId='.$v['id'])->count();
            $v['cTime'] = date('Y-m-d',$v['cTime']);
            $v['sTime'] = date('Y-m-d',$v['sTime']);
            $v['school_audit'] = $v['school_audit']==5?'已完结':'进行中';
        }
        $arr = array('活动名称','发起人','发起组织','归属组织','发起时间','活动时间','初审人','终审人','实践学分','活动积分','参与人数','签到人数','状态');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'南京航空航天大学金城学院');
    }
    public function pubim(){
        set_time_limit(0);
        $dao = M('y_tj');
        $mon = array('2014-11','2014-12','2015-01','2015-02','2015-03','2015-04','2015-05','2015-06');
        foreach ($mon as $k=>$v){
            $map = array();
            $row = array();
            $map['day'][] = array('EGT',$v.'-01');
            if(isset($mon[$k+1])){
                $map['day'][] = array('lt',$mon[$k+1].'-01');
            }
            $row[] = $v;
            $freeTimes = $dao->where($map)->sum('free_times');
            $times = $dao->where($map)->sum('times');
            $moneyIn = $dao->where($map)->sum('moneyIn');
            $moneyOut = $dao->where($map)->sum('moneyOut');
            $row[] = $freeTimes;
            $row[] = $times-$freeTimes;
            $row[] = money2xs($moneyIn-$moneyOut);
            $list[] = $row;
        }
        $arr = array('月份','免费摇一摇次数','花钱次数','盈亏');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'摇一摇月统计');
    }
    public function weibom(){
        set_time_limit(0);
        $dao = M('weibo');
        $mon = array('2014-11','2014-12','2015-01','2015-02','2015-03','2015-04','2015-05','2015-06');
        foreach ($mon as $k=>$v){
            $map = array();
            $row = array();
            $from = strtotime($v.'-01');
            $map['ctime'][] = array('EGT',$from);
            if(isset($mon[$k+1])){
                $to = strtotime($mon[$k+1].'-01');
                $map['ctime'][] = array('lt',$to);
            }
            $row[] = $v;
            $row[] = $dao->where($map)->count();
            $list[] = $row;
        }
        $arr = array('月份','发布微博数');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'微博数月统计');
    }
    public function addCitys(){
        die;
        $citys = array('武汉');
        $dao = M('citys');
        foreach($citys as $v){
            $data['city'] = $v;
            $data['short'] = pinyin($v);
            $data['pid'] = 6;
            $dao->add($data);
        }
    }
    public function ld() {
        set_time_limit(0);
        $list = M('user')->where("event_level=10 OR event_level=11")->field('event_level,realname,sid')->findAll();
        foreach ($list as &$v) {
            $v['event_level'] = ($v['event_level']==10) ? '校级领导':'院系领导';
            $v['sid'] = tsGetSchoolName($v['sid']);
        }
        $arr = array('身份','姓名','学校');
        array_unshift($list, $arr);
        service('Excel')->export2($list,'领导');
    }
    //清空第二课堂成绩单
    public function initEc2(){
        $sid = 0;
        M('')->query("delete from ts_ec_folder where sid=$sid");
        M('')->query("delete from ts_ec_input where sid=$sid");
        M('')->query("delete from ts_ec_auditor where sid=$sid");
        M('')->query("delete from ts_ec_identify where sid=$sid");
        M('')->query("update ts_user set can_credit=0 where sid=$sid and can_admin=0");
    }
    private function _addEventGetLevelId($levelId,$sid){
        $level = D('EventLevel','event')->allLevel($sid);
        if(!$level){
            return 0;
        }
        if(!$levelId){
            return $level[0]['id'];
        }
        $levelIds = getSubByKey($level, 'id');
        if(!in_array($levelId, $levelIds)){
            return $level[0]['id'];
        }
        return $levelId;
    }
    private function _uinfo($uid)
    {
        $key = 'lu_uinfo_'.$uid;
        $cache = Mmc($key);
        if($cache !== false){
            return json_decode($cache,true);
        }
        $user = M('user')->where("uid=$uid")->field('sid,realname')->find();
        Mmc($key,  json_encode($user),0,60*2);
        return $user;
    }
    public function lu(){
        // 修改日活月活
        M('')->execute("CREATE TABLE ts_tj_rh1 LIKE ts_tj_rh;");
        M('')->execute("INSERT INTO ts_tj_rh1 SELECT * FROM ts_tj_rh;;");
        $list = M('')->query("select *  from ts_tj_rh1 order by day asc;");
        $firstDay = 1;
        foreach($list as $vo){
            $rad1 = rand(400,700);
            $dlogin = intval($vo['tj_user']*$rad1/10000);
            $data = array();
            $data['all_dlogin'] = $vo['all_dlogin']-$vo['tj_dlogin']+$dlogin;
            $data['tj_dlogin'] = $dlogin;
            $day = substr($vo['day'], -2).'';
            $firstDay = $day=='01' ? 1 : 0;
            if($firstDay){
                $lastRad = $rad1;
                $data['tj_mlogin'] = $data['tj_dlogin'];
                $data['all_mlogin'] = $data['all_dlogin'];
            }else{
                $rad2 = rand(200,500)/$day*3;
                $newRad = $lastRad + $rad2;
                $mlogin = intval($vo['tj_user']*$newRad/10000);
                $data['all_mlogin'] = $vo['all_mlogin']-$vo['tj_mlogin']+$mlogin;
                $data['tj_mlogin'] = $mlogin;
                $lastRad = $newRad;
            }
            M('tj_rh1')->where("day='$vo[day]'")->save($data);
        }
        //活动改为未完结
        die;
        $eids = array(128418,128172,127484,124187,124181,123174,122626,122557,120334,120310,119619,119228,118704,118342,117388,117027,114394,114391,114389,114334,114333,114332,114329,114326,114323,114321,114318,114315,114314,114311,114306,114191,114115,113849,113687,112606,112384,112182,110857,110792,108867,108861,108855);
        doQuery("update ts_event_user set fTime=0 where eventId in (128418,128172);");
        //活动改为未完结
        doQuery("update ts_event set school_audit=3 where id in (128418,128172);");
        //从cronjob里删掉 ts_cron_credit
        doQuery("delete from ts_cron_credit where eid in (128418,128172);");

        
    }

    private function perList()
    {
        return array(xujinsheng,
                        congqi,
                        zhengchenghan,
                        huhuanhuan,
                        anglela_zheng,
                        wangying,
                        zhaoyilan,
                        xiexiaofei,
                        lujia,
                        minliang,
                        liupanpan,
                        shichenglan,
                        shashuo,
                        zhouxiang,
                        zhuxiaofeng,
                        dongyiqing,
                        chenwei,
                        dainanci,
                        xuchunhua,
                        rechner00,
                        jl,
                        yanglong,
                        zhaoshengkuan,
                        wansongju,
                        mengleifang,
                        lixiaofei,
                        xchunhe,
                        wjp,
                        zhuhaibing,
                        yangjun,
                        huqiwen,
                        zhujie,
                        huajiangang,
                        zhangdeshun,
                        wxf,
                        feikf,
                        ios_test,
                        cuiqq,
                        tianboyu,
                        chenyujuan,
                        luhaimei,
                        sujinming,
                        shenjing,
                        hulei,
                        nijunyan,
                        liuyang,
                        zhouqin,
                        zhangmin01,
                        dingchunlan,
                        jinbao,
                        zhangmin,
                        xxf,
                        liunamin,
                        guyan,
                        gubaolong,
                        liyi,
                        loudeming,
                        yanqing,
                        duyanping,
                        jiangyinkang,
                        fanxinran,
                        zhangyu01,
                        zhuguodan,
                        penglei,
                        qimengjun,
                        zhangjunfu,
                        liying,
                        pu007,
                        pu010,
                        pu013,
                        guoyu,
                        lujiamin,
                        songshengyao,
                        sunjie,
                        lujianwen,
                        sundawei,
                        qinghai03,
                        wangdazhi,
                        xiaye,
                        liumin01,
                        tianyu,
                        huangwenjuan,
                        anlina,
                        gusumin,
                        changzhizhao,
                        zhuxiaohong,
                        wangzhe1,
                        xulei,
                        lujing,
                        jiangyi,
                        yuanfang,
                        nijunqiang,
                        yaoyao,
                        jiangjingdong,
                        zhurongmei,
                        panjie,
                        liliuxiang,
                        shenzhengxue,
                        simaguoqiang,
                        zhaijiangang,
                        gaoxingda,
                        zhangjunwei,
                        jinxiaofei,
                        zhangjianzhong,
                        baoyiwei,
                        zhuxiang,
                        qianguoxiang,
                        mayajun,
                        xushian,
                        wangna,
                        yangdongping,
                        chuwei,
                        xiaofuchun,
                        wangerdong,
                        sunlei,
                        sunlei1,
                        mazhiming,
                        dujianbo,
                        yujianying,
                        wangxianjing,
                        shaochunman,
                        jean,
                        niwei,
                        yangjie,
                        xuwei,
                        shenjunye,
                        daijun,
                        wuchunxing,
                        zhangxiaodong,
                        chenliang,
                        diaoyong,
                        qianweidong
        );
    }

    public function doInsertSign()
    {
        $eventId = 145063;
        $userEmail = $this->perList();
        $userUid = $this->getUidByEmail($userEmail);
        $i = 0;
        foreach($userUid as $val){
            $data['eventId'] = $eventId;
            $data['uid'] = $val;
            $data['status'] = 2;
            $data['cTime'] = 1452877200;
            $data['realname'] = getUserField($val,'realname');
            $data['sex'] = getUserField($val,'sex');
            $data['usid'] = getUserField($val,'sid');
            if(M('event_user')->where($data)->find()){
                echo $val.'用户已加入'.$eventId.'活动并签到成功！<br/>';
            }else{
                M('event_user')->add($data);
                $i++;
            }
        }
        echo $i.'个用户新增到活动<br/>';
    }

    private function getUidByEmail($userArr)
    {
        $map['sid'] = 473;
        $newUid = array();
        foreach($userArr as $val)
        {
            $map['email'] = $val.'@test.com';
            $newUid[] = M('user')->where($map)->getField('uid');
        }
        return $newUid;
    }


    public function tongjiA(){
        set_time_limit(0);
        $schools = M('School')->field('id,title,eTime,tj_year')->where('pid=0 and id != 473')->select();

        foreach ($schools as $key => $school){
            $sids = M('School')->field('id')->where('pid='.$school['id'])->select();
            $sidString = '';
            foreach ($sids as $sid){
                $sidString .= $sid['id'].',';
            }
            $sidString = $sidString.$school['id'];

            //学校名称
            $list[$key]['title'] = $school['title'];

            //开通时间
            $list[$key]['time'] = $school['eTime']>0?date('Y-m-d',$school['eTime']):$school['eTime'];

            //取得该学校总在校学生人数
            $list[$key]['total_user'] = $this->totalUser($sidString,$school['tj_year']);

            // 取得该校月活2015-9-1至2016-1-31
            $total_yuehuo = 0;
            $total_yuehuo += $this->_getYh($school['id'],201509);
            $total_yuehuo += $this->_getYh($school['id'],201510);
            $total_yuehuo += $this->_getYh($school['id'],201511);
            $total_yuehuo += $this->_getYh($school['id'],201512);
            $total_yuehuo += $this->_getYh($school['id'],201601);
            $list[$key]['total_yuehuo'] = intval($total_yuehuo / 5);

            // 发起活动数汇总2015-9-1至2016-1-31
            $list[$key]['event'] = $this->getEventTotal('2015-09-01','2016-02-01',$sidString);

            //活动报名人数汇总
            $sql = 'SELECT COUNT(*) as cnt FROM ts_event_user WHERE eventId in(SELECT id FROM ts_event WHERE sid in ('.$sidString.') AND cTime>='.strtotime('2015-09-01').' AND cTime<='.strtotime('2016-02-01').')';
            $result = M()->query($sql);
            $list[$key]['event_baoming'] = $result[0]['cnt'];

            //活动签到人数汇总
            $sql = 'SELECT COUNT(*) as cnt FROM ts_event_user WHERE status=2 AND eventId in(SELECT id FROM ts_event WHERE sid in ('.$sidString.') AND cTime>='.strtotime('2015-09-01').' AND cTime<='.strtotime('2016-02-01').')';
            $result = M()->query($sql);
            $list[$key]['event_qiandao'] = $result[0]['cnt'];

            //学生社团总数
            $list[$key]['group_shetuan'] = $this->getGroupTotal('2015-09-01','2016-02-01',$sidString,3);

            //学生社团总人数
            $sql = 'SELECT COUNT(*) as cnt FROM ts_group_member WHERE gid in(SELECT id FROM ts_group WHERE category=3 AND school in ('.$sidString.') AND cTime>='.strtotime('2015-09-01').' AND cTime<='.strtotime('2016-02-01').')';
            $result = M()->query($sql);
            $list[$key]['group_shetuan_user'] = $result[0]['cnt'];

            //学生部门总数
            $list[$key]['group_bumen'] = $this->getGroupTotal('2015-09-01','2016-02-01',$sidString,1);

            //学生部门总人数
            $sql = 'SELECT COUNT(*) as cnt FROM ts_group_member WHERE gid in(SELECT id FROM ts_group WHERE category=1 AND school in ('.$sidString.') AND cTime>='.strtotime('2015-09-01').' AND cTime<='.strtotime('2016-02-01').')';
            $result = M()->query($sql);
            $list[$key]['group_bumen_user'] = $result[0]['cnt'];

            //团支部总数
            $list[$key]['group_tuanzhibu'] = $this->getGroupTotal('2015-09-01','2016-02-01',$sidString,2);

            //团支部总人数
            $sql = 'SELECT COUNT(*) as cnt FROM ts_group_member WHERE gid in(SELECT id FROM ts_group WHERE category=2 AND school in ('.$sidString.') AND cTime>='.strtotime('2015-09-01').' AND cTime<='.strtotime('2016-02-01').')';
            $result = M()->query($sql);
            $list[$key]['group_tuanzhibu_user'] = $result[0]['cnt'];

            //部落总数
            $list[$key]['group'] = $this->getGroupTotal('2015-09-01','2016-02-01',$sidString);

            //部落总人数
            $sql = 'SELECT COUNT(*) as cnt FROM ts_group_member WHERE gid in(SELECT id FROM ts_group WHERE school in ('.$sidString.') AND cTime>='.strtotime('2015-09-01').' AND cTime<='.strtotime('2016-02-01').')';
            $result = M()->query($sql);
            $list[$key]['group_user'] = $result[0]['cnt'];
            sleep(1);
        }
        $arr = array('学校名称','开通时间','用户数','月活','发起活动数汇总','活动报名人数汇总','活动签到人数汇总','学生社团总数','学生社团总人数','学生部门总数','学生部门总人数','团支部总数','团支部总人数','部落总数','部落总人数');
        array_unshift($list, $arr);
        service('Excel')->exportFile($list,'统计','/001.xls');
    }

    /**
     * 统计激活和未激活人数
     */
    public function tongjiZ()
    {
        set_time_limit(0);
        $cModel = M('citys');
        $pModel = M('province');
        $userModel = M('user');
        $schools = M('School')->field('id,title,eTime,tj_year,provinceId,cityId')->where('pid=0 and id != 473')->select();
        foreach($schools as $k=>$v)
        {
            $lists[$k]['province'] = $pModel->where('id = '.$v['provinceId'])->getField('title');
            $lists[$k]['city'] = $cModel->where('id = '.$v['cityId'])->getField('city');
            $lists[$k]['schoolName'] = $v['title'];
            $lists[$k]['tj_year'] = $v['tj_year'];
            $lists[$k]['15_init_count'] = $userModel->where('sid = '.$v['id'].' and year = 15 and is_init = 1')->count();
            $lists[$k]['15_no_init_count'] = $userModel->where('sid = '.$v['id'].' and year = 15 and is_init = 0')->count();
            $lists[$k]['14_init_count'] = $userModel->where('sid = '.$v['id'].' and year = 14 and is_init = 1')->count();
            $lists[$k]['14_no_init_count'] = $userModel->where('sid = '.$v['id'].' and year = 14 and is_init = 0')->count();
            $lists[$k]['13_init_count'] = $userModel->where('sid = '.$v['id'].' and year = 13 and is_init = 1')->count();
            $lists[$k]['13_no_init_count'] = $userModel->where('sid = '.$v['id'].' and year = 13 and is_init = 0')->count();
            $lists[$k]['12_init_count'] = $userModel->where('sid = '.$v['id'].' and year = 12 and is_init = 1')->count();
            $lists[$k]['12_no_init_count'] = $userModel->where('sid = '.$v['id'].' and year = 12 and is_init = 0')->count();
        }
        $arr = array('省份','城市','学校名称','年制','15级已激活人数','15级未激活人数','14级已激活人数','14级未激活人数','13级已激活人数','13级未激活人数','12级已激活人数','12级未激活人数');
        array_unshift($lists, $arr);
        service('Excel')->exportFile($lists,'统计','./data/00002.xls');
    }

    /**
     * 统计每个年级的学生数量
     */
    public function tongjiAll()
    {
        set_time_limit(0);
        $cModel = M('citys');
        $pModel = M('province');
        $userModel = M('user');
        $schools = M('School')->field('id,title,eTime,tj_year,provinceId,cityId')->where('pid=0 and id != 473')->order('provinceId ASC')->select();
        foreach($schools as $k=>$v)
        {
            $lists[$k]['province'] = $pModel->where('id = '.$v['provinceId'])->getField('title');
            $lists[$k]['city'] = $cModel->where('id = '.$v['cityId'])->getField('city');
            $lists[$k]['schoolName'] = $v['title'];
            $lists[$k]['tj_year'] = $v['tj_year'];
            $lists[$k]['15'] = $userModel->where('sid = '.$v['id'].' and year = 15')->count();
            $lists[$k]['14'] = $userModel->where('sid = '.$v['id'].' and year = 14')->count();
            $lists[$k]['13'] = $userModel->where('sid = '.$v['id'].' and year = 13')->count();
            $lists[$k]['12'] = $userModel->where('sid = '.$v['id'].' and year = 12')->count();
            $lists[$k]['11'] = $userModel->where('sid = '.$v['id'].' and year = 11')->count();
            $lists[$k]['10'] = $userModel->where('sid = '.$v['id'].' and year = 10')->count();
            $lists[$k]['09'] = $userModel->where('sid = '.$v['id'].' and year = 09')->count();
            $lists[$k]['08'] = $userModel->where('sid = '.$v['id'].' and year = 08')->count();
            $lists[$k]['07'] = $userModel->where('sid = '.$v['id'].' and year = 07')->count();
        }
        $arr = array('省份','城市','学校名称','年制','15级','14级','13级','12级','11级','10级','09级','08级','07级');
        array_unshift($lists, $arr);
        service('Excel')->exportFile($lists,'统计','./data/'.time().'.xls');
    }

    public function tongjiOneToEvery()
    {
        set_time_limit(0);
        $cModel = M('citys');
        $pModel = M('province');
        $userModel = M('user');
        $schools = M('School')->field('id,title,eTime,tj_year,provinceId,cityId')->where('pid=0 and id != 473')->order('provinceId ASC')->select();
        $one =  ' and ctime between '.strtotime('2016-01-01 00:00:00').' and '.strtotime('2016-01-31 23:59:59');
        $two = ' and ctime between '.strtotime('2016-02-01 00:00:00').' and '.strtotime('2016-02-29 23:59:59');
        $three = ' and ctime between '.strtotime('2016-03-01 00:00:00').' and '.strtotime('2016-03-31 23:59:59');
        foreach($schools as $k=>$v)
        {
            $lists[$k]['province'] = $pModel->where('id = '.$v['provinceId'])->getField('title');
            $lists[$k]['city'] = $cModel->where('id = '.$v['cityId'])->getField('city');
            $lists[$k]['schoolName'] = $v['title'];
            $lists[$k]['1'] = $userModel->where('sid = '.$v['id'].$one)->count();
            $lists[$k]['2'] = $userModel->where('sid = '.$v['id'].' and is_init = 1 '.$one)->count();
            $lists[$k]['3'] = $userModel->where('sid = '.$v['id'].$two)->count();
            $lists[$k]['4'] = $userModel->where('sid = '.$v['id'].' and is_init = 1 '.$two)->count();
            $lists[$k]['5'] = $userModel->where('sid = '.$v['id'].$three)->count();
            $lists[$k]['6'] = $userModel->where('sid = '.$v['id'].' and is_init = 1 '.$three)->count();
        }
        $arr = array('省份','城市','学校名称','1月注册数','1月激活数','2月注册数','2月激活数','3月注册数','3月激活数');
        array_unshift($lists, $arr);
        service('Excel')->exportFile($lists,'统计','./data/'.time().'.xls');
    }

    /**
     * 南工大文化艺术节投票选手数据导出
     */
    public function exportPlayerDetail()
    {
        $eventId = 152047;
        $map['eventId'] = $eventId;
        $list = M('event_player')->where($map)->select();
        $result = array();
        $c_type = array(1=>'个人申报',2=>'组织推荐');
        $e_type = array(1=>'思想引领类',2=>'勤奋励志类',3=>'创新创业类',4=>'实践公益类',5=>'文体宣传类',6=>'其他类');
        foreach($list as $k=>$v)
        {
            $params = unserialize($v['paramValue']);
            $result[$k]['realname'] = $v['realname'];
            $result[$k]['school'] = $v['school'];
            $result[$k]['c_type'] = $c_type[$v['c_type']];
            $result[$k]['e_type'] = $e_type[$v['e_type']];
            $result[$k]['content'] = $v['content'];
            $result[$k]['commentCount'] = $v['commentCount'];
            $result[$k]['sj'] = $params[0];
        }
        $arr = array('姓名','院系','申报类型','活动类型','简介','评论数','事迹');
        array_unshift($result, $arr);
        service('Excel')->exportFile($result,'统计','./data/'.time().'.xls');
    }

    /**
     * 南工大文化艺术节投票选手 投票数 数据导出
     */
    public function exportPlayerVoteDetail()
    {
        $eventId = 152047;
        $map['eventId'] = $eventId;
        $list = M('event_player')->where($map)->select();
        $result = array();
        foreach($list as $k=>$v)
        {
            $result[$k]['realname'] = $v['realname'];
            $result[$k]['ticket'] = $v['ticket'];
            $result[$k]['commentCount'] = $v['commentCount'];
        }
        $arr = array('姓名','点赞投票数','评论数');
        array_unshift($result, $arr);
        service('Excel')->exportFile($result,'统计','./data/'.time().'.xls');
    }

    /**
     * 江苏信息技术学院2015年下半年学分导出
     */
    public function exportJsxxCredit()
    {
        set_time_limit(0);
        $map['sid'] = 2133;
        $map['event_level'] = 20;
        $students = M('user')->where($map)->field('uid,realname,email,sid1,year,major')->select();
        foreach($students as $k=>$v)
        {
            $result[$k]['realname'] = $v['realname'];
            $result[$k]['no'] = str_replace('@jsit.com','',$v['email']);
            $result[$k]['department'] = $v['sid1'] == 0 ? '' : tsGetSchoolName($v['sid1']);
            $result[$k]['year'] = $v['year'];
            $result[$k]['major'] = $v['major'];
            $result[$k]['sum_credit'] = M('event_user')->where('uid='.$v['uid'])->sum('credit')+0;
            $begin_time = strtotime('2016-02-17 00:00:00');
            $end_time = strtotime('2016-04-20 23:59:59');
            $result[$k]['2016_credit'] = M('event_user')->where('uid='.$v['uid'].' and cTime BETWEEN '.$begin_time.' and '.$end_time)->sum('credit')+0;
        }
        $arr = array('姓名','学号','院系','年级','专业','总学分','15年下半年学分');
        array_unshift($result, $arr);
        service('Excel')->exportFile($result,'统计','./data/江苏信息技术学院2015年下半年学分导出.xls');
    }
    
    /**
     * 智慧之星答题信息导出
     */
    public function exportZhzx()
    {
        set_time_limit(0);
        $map['askId'] = 2;
        $map['cTime'] = array('elt',  strtotime('2016-04-29 16:00:00'));
        $id = 2;
        $cTime = strtotime('2016-04-29 16:00:00');
        $users = M('')->query('select * from (select a.uid,a.num from ts_event_ask_users as a where a.askId = '.$id.' and a.cTime < \''.$cTime.'\' ORDER BY a.num DESC) as b GROUP BY b.uid ORDER BY b.num DESC');
        foreach($users as $k=>$v)
        {
            $result[$k]['realname'] = getUserField($v['uid'], 'realname');
            $email = getUserField($v['uid'], 'email');
            $emailArr = explode('@', $email);
            $result[$k]['no'] = $emailArr[0];
            $sid = getUserField($v['uid'], 'sid');
            $result[$k]['school'] = tsGetSchoolTitle($sid);
            $count = M('')->query('select count(1) as num from ts_event_ask_users where uid = '.$v['uid'].' and askId = '.$id.';');
            $result[$k]['count'] = $count[0]['num'];
        }
        $arr = array('姓名','学号','学校','参与次数');
        array_unshift($result, $arr);
        service('Excel')->exportFile($result,'统计','./data/智慧之星'.time().'.xls');
    }

    /**
     * 江苏师范大学 选手投票 投票人员数据导出
     */
    public function exportJssfPlayer()
    {
        $eventId = 163323;
        $map['eventId'] = $eventId;
        $list = M('event_vote')->where($map)->select();
        $result = array();
        foreach($list as $k=>$v)
        {
            $uid = $v['mid'];
            $sid1 = getUserField($uid,'sid1');
            $email = getUserField($uid, 'email');
            $emailArr = explode('@',$email);
            $realname = getUserField($uid, 'realname');
            $year = getUserField($uid, 'year');
            $class = M('user_a')->where('uid = '.$uid)->getField('class');
            $result[$k]['depart'] = tsGetSchoolName($sid1);
            $result[$k]['class'] = $class;
            $result[$k]['no'] = $emailArr[0];
            $result[$k]['realname'] = $realname;
            $result[$k]['year'] = $year;
        }
        $arr = array('院系','班级','学号','姓名','年级'); //院系  班级  学号  姓名  年级
        array_unshift($result, $arr);
        service('Excel')->exportFile($result,'统计','./data/'.time().'.xls');
    }

    /**
     * 取得某学校在校学生人数
     */
    public function totalUser($sid,$year){
        $total_user = 0;
        if ($year == 4){
            $total_user = M('User')->where('year>12 AND sid in ('.$sid.')')->count();
        }elseif($year == 3){
            $total_user = M('User')->where('year>13 AND sid in ('.$sid.')')->count();
        }

        return $total_user;
    }

    /**
     * 获取月活
     * @param $sid
     * @return array|mixed|stdClass|string
     */
    private function _getYh($sid,$month){
        $tableName = 'ts_tj_login_'.$month;
        $count = M('')->query("select count(distinct(uid)) as count from $tableName where istj=1 and sid=$sid;");
        var_dump($count);
        return $count[0]['count'];
    }


    private function getEventTotal($fromtime,$endtime,$sid){
        $condition['sid'] = $sid;
        $condition['cTime'] = array(array(''),array());
        $count = M('Event')->where('sid in ('.$sid.') AND cTime>='.strtotime($fromtime).' AND cTime<='.strtotime($endtime))->count();
        return $count;
    }

    private function getGroupTotal($fromtime,$endtime,$sid,$type=''){
        if (!empty($type)){
            $condition = 'category='.$type.' AND ';
        }
        $count = M('Group')->where($condition.'school in ('.$sid.') AND cTime>='.strtotime($fromtime).' AND cTime<='.strtotime($endtime))->count();
        return $count;
    }


 
}

class word {
    function start() {
        ob_start();
        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office"
xmlns:w="urn:schemas-microsoft-com:office:word"
xmlns="http://www.w3.org/TR/REC-html40">';
    }

    function save($path) {
        echo "</html>";
        $data = ob_get_contents();
        ob_end_clean();
        $this->wirtefile($path, $data);
    }

    function wirtefile($fn, $data) {
        $pagename = iconv("UTF-8", "GBK", $fn);
        //$tempDir = substr($pagename, 0, strpos($pagename, '/'));

        $fp = fopen($pagename, "w+");
        fwrite($fp, $data);
        fclose($fp);
    }






}
?>