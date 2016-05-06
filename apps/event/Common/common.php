<?php
//获取应用配置
function getConfig($key=NULL){
	$config = model('Xdata')->lget('event');
	$config['limitpage']    || $config['limitpage'] =10;
	$config['canCreate']===0 || $config['canCreat']=1;
    ($config['credit'] > 0   || '0' === $config['credit']) || $config['credit']=100;
    $config['credit_type']  || $config['credit_type'] ='experience';
	($config['limittime']   || $config['limittime']==='0') || $config['limittime']=10;//换算为秒
        $config['createAudit'] === 0 || $config['createAudit'] = 1;

	if($key){
		return $config[$key];
	}else{
		return $config;
	}
}
function getPhotoConfig($key=NULL){
	$config = model('Xdata')->lget('photo');
	$config['album_raws'] || $config['album_raws']=6;
	$config['photo_raws'] || $config['photo_raws']=8;
	$config['photo_preview']==0 || $config['photo_preview']=1;
	($config['photo_max_size']=floatval($config['photo_max_size'])*1024*1024) || $config['photo_max_size']=-1;
	$config['photo_file_ext'] || $config['photo_file_ext']='jpeg,gif,jpg,png';
	$config['max_flash_upload_num'] || $config['max_flash_upload_num']=10;
	$config['open_watermark']==0 || $config['open_watermark']=1;
	$config['watermark_file'] || $config['watermark_file']='public/images/watermark.png';
	if($key==NULL){
		return $config;
	}else{
		return $config[$key];
	}
}

//获取活动封面存储地址
function getCover($coverId,$width=200,$height=200,$t='c'){
    return tsGetCover($coverId, $width, $height, $t);
}
//获取活动封面存储地址
function getLogo($logoId){
    $cover = D('Attach')->field('savepath,savename')->find($logoId);
    if($logoId){
            $cover	=	get_photo_url($cover['savepath'].$cover['savename']);
    }else{
            $cover	=	SITE_URL."/apps/event/Tpl/default/Public/images/hdpic1.gif";
    }
    return $cover;
}
//根据存储路径，获取图片真实URL
function get_photo_url($savepath) {
    if(strpos($savepath, '/')){
        return PIC_URL.'/data/uploads/'.$savepath;
    }else{
        return PIC_URL.'/data/uploads/event/'.$savepath;
    }
}
function realityImageURL($img) {
    $imgURL = sprintf('%s/apps/event/Tpl/default/Public/hold/%s', SITE_URL, $img); //默认的礼物图片地址
    if (file_exists(sprintf('./apps/event/Tpl/default/Public/hold/%s', $img))) {
        return $imgURL;
    } else {//若默认里没有则返回自定义的礼物图片地址
        return sprintf('%s/data/uploads/event/%s', SITE_URL, $img);
    }
}
/**
 * getBlogShort
 * 去除标签，截取blog的长度
 * @param mixed $content
 * @param mixed $length
 * @access public
 * @return void
 */
function getBlogShort($content,$length = 40) {
	$content	=	stripslashes($content);
	$content	=	strip_tags($content);
	$content	=	getShort($content,$length);
	return $content;
}

function getThumb($filename,$width=190,$height=240,$t='f') {
    if(empty($filename)){
        $thumb = SITE_URL . '/apps/event/Tpl/default/Public/images/user_pic_big.gif';
    }else{
        if(strpos($filename, '/')){
            $thumb = tsMakeThumbUp($filename,$width,$height,$t);
        }else{
            $thumb = tsMakeThumbUp('event/'.$filename,$width,$height,$t);
        }
    }
    return $thumb;
}

function getGroupThumb($filename,$width=80,$height=80,$t='c') {
    if(empty($filename) || $filename=='default.gif'){
        $thumb = '';
    }else{
        $thumb = tsMakeThumbUp($filename,$width,$height,$t);
    }
    if(!$thumb){
        $num = rand(1,6);
        if($width<=100){
            $thumb = PIC_URL.'/data/app_ico/group_s'.$num.'.png';
        }else{
            $thumb = PIC_URL.'/data/app_ico/group'.$num.'.jpg';
        }
    }
    return $thumb;
}
function getThumb1($filename,$width=190,$height=240,$t='f') {
    if(empty($filename)){
        $thumb = '__THEME__/images/no_photo.gif';
    }else{
        if(strpos($filename, '/')){
            $thumb = tsMakeThumbUp($filename,$width,$height,$t);
        }else{
            $thumb = tsMakeThumbUp('event/'.$filename,$width,$height,$t);
        }
    }
    return $thumb;
}

function deletePath($savepath){
    $path = SITE_PATH . '/data/uploads/event/' . $savepath;
    if ( is_file($path) )
        unlink($path);
}

function getTypeTitle($typeId,$types=array()){
    if(empty($types)){
        $types = D('EventType')->getType();
    }
    return $types[$typeId];
}
/**
* _paramDate
* 解析日期
* @param mixed $date
* @access private
* @return void
*/
function _paramDate($date) {
   $date_list = explode(' ', $date);
    list( $year, $month, $day ) = explode('-', $date_list[0]);
    $hour = '00';
    $minute = '00';
    $second = '00';
    if(isset($date_list[1])){
        list( $hour, $minute, $second ) = explode(':', $date_list[1]);
    }
    return mktime($hour, $minute, $second, $month, $day, $year);
}
function eventUpload() {
    //上传参数
    $options['max_size'] = getPhotoConfig('photo_max_size');
    $options['allow_exts'] = getPhotoConfig('photo_file_ext');
    //$options['save_path'] = UPLOAD_PATH . $path;
    $options['save_to_db'] = false;
    //执行上传操作
    $info = X('Xattach')->upload('event', $options);
    return $info;
}
function imgUploadDb($mid) {
    //上传参数
    $options['userId']		=	$mid;
    $options['max_size'] = getPhotoConfig('photo_max_size');
    $options['allow_exts'] = getPhotoConfig('photo_file_ext');
    //执行上传操作
    $info = X('Xattach')->upload('event', $options);
    return $info;
}
function get_flash_url($host, $flashvar, $link='') {
    if(!$host){
        return $flashvar;
    }
    $flashAddr = array(
        'youku.com' => 'http://player.youku.com/player.php/sid/FLASHVAR/v.swf',
        'ku6.com' => 'http://player.ku6.com/refer/FLASHVAR/v.swf',
        //'sina.com.cn' => 'http://vhead.blog.sina.com.cn/player/outer_player.swf?vid=FLASHVAR',
        'sina.com.cn' => 'http://you.video.sina.com.cn/api/sinawebApi/outplayrefer.php/vid=FLASHVAR/s.swf',
        'tudou.com' => 'http://www.tudou.com/v/FLASHVAR',
        //'tudou.com' => 'http://www.tudou.com/v/FLASHVAR/&autoPlay=true/v.swf',
        //'youtube.com' => 'http://www.youtube.com/v/FLASHVAR',
        //'sohu.com' => 'http://v.blog.sohu.com/fo/v4/FLASHVAR',
        //'sohu.com' => 'http://share.vrs.sohu.com/FLASHVAR/v.swf',
        //'mofile.com' => 'http://tv.mofile.com/cn/xplayer.swf?v=FLASHVAR',
        'yixia.com' => 'http://paikeimg.video.sina.com.cn/splayer1.7.14.swf?scid=FLASHVAR&token=',
        't.cn' => 'http://paikeimg.video.sina.com.cn/splayer1.7.14.swf?scid=FLASHVAR&token=',
        'music' => 'FLASHVAR',
        'flash' => 'FLASHVAR'
    );
    $result = '';
    if (isset($flashAddr[$host])) {
        if('tudou.com' == $host) {
            if(strpos($link,'www.tudou.com/albumplay')!==false) {
                $flashAddr[$host] = 'http://www.tudou.com/a/FLASHVAR';
            }elseif(strpos($link,'www.tudou.com/listplay')!==false){
                $flashAddr[$host] = 'http://www.tudou.com/l/FLASHVAR';
            }
        }
        $result = str_replace('FLASHVAR', $flashvar, $flashAddr[$host]);
    }
    return $result;
}
function get_flash_img($img) {
    if (!$img) {
        return __THEME__ . '/images/nocontent.png';
    } else {
        return $img;
    }
}
function getCost($cost) {
    if ($cost == '0') {
        return '免费';
    } elseif ($cost == '1') {
        return 'AA制';
    } elseif ($cost == '2') {
        return '50元以下';
    } elseif ($cost == '3') {
        return '50-200元';
    } elseif ($cost == '4') {
        return '200-500元';
    } elseif ($cost == '5') {
        return '500-1000元';
    } elseif ($cost == '6') {
        return '1000元以上';
    }
}
function getSchoolDomain($sid){
    $school = M('school')->where('id='.$sid)->find();
    if($school){
        return $school['domain'];
    }
    return 'http://pocketuni.net';
}
function getJyPf(){
    //到课考勤5分，纪律考核5分，学习笔记10分，社会体验10分，团队活动10分，学习总结与规划5分。
    $res[] = array('到课考勤',5);
    $res[] = array('纪律考核',5);
    $res[] = array('学习笔记',10);
    $res[] = array('社会体验',10);
    $res[] = array('团队活动',10);
    $res[] = array('学习总结与规划',5);
    //政治生活锻炼10分，挂职实践10分，村官结对10分，课题调研10分，志愿服务10分，实践总结与党性分析报告5分
    $res[] = array('政治生活锻炼',10);
    $res[] = array('挂职实践',10);
    $res[] = array('村官结对',10);
    $res[] = array('课题调研',10);
    $res[] = array('志愿服务',10);
    $res[] = array('实践总结与党性分析报告',5);
    return $res;
}

function getSiteTitle(){
	return array(
		'my_group'=>'所在部落的最新动态-我的部落-',
		'my_group_new_topic' => '最新话题-我的部落-',
		'my_friend_group' => '好友的部落-',
		'all_group' => '发现部落-',
		'newTopic_all' => '所有话题-最新话题-',
		'newTopic_my_post' => '最新话题-我发表的话题-',
		'newTopic_my_reply' => '我回复的话题-最新话题-',
		'newTopic_my_collect' => '我收藏的话题-最新话题-',
		'issue_topic' => '发布话题-',
		'create_group' => '创建部落-',
		'add_topic' => '发表话题',
		'search_topic' => '搜索话题',
		'dist_topic'  =>'精华话题',
		'edit_topic' =>'编辑话题',
		'topic_index'=>'话题-',
		'album_index'=>'相册',
		'upload_pic'=>'上传图片',
		'all_photo'=>'全部照片',
		'all_album'=>'群相册',
		'file_index'=>'文件',
		'file_upload'=>'上传文件',
		'member_index'=>'成员',

	);
}

function formatsize($fileSize) {
    $size = sprintf("%u", $fileSize);
    if ($size == 0) {
        return("0 Bytes");
    }
    $sizename = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
    return round($size / pow(1024, ($i = floor(log($size, 1024)))), 2) . $sizename[$i];
}
function sjType($id=0){
    $roles = array('1'=>'先进单位申报','2'=>'优秀团队申报','3'=>'优秀团队 + 十佳团队申报','4'=>'先进个人申报','5'=>'先进个人 + 十佳使者申报','6'=>'先进工作者申报',
                    '7'=>'优秀社会实践基地申报','8'=>'优秀调研报告申报','9'=>'十佳风尚奖申报');
    if($id){
        return $roles[$id];
    }
    return $roles;
}
function sjStatus($id=0){
    $roles = array('1'=>'待初审','2'=>'初审驳回','3'=>'待终审','4'=>'终审驳回','5'=>'终审通过');
    if($id){
        return $roles[$id];
    }
    return $roles;
}
//优秀社会实践基地申报（每校限1个）,优秀调研报告申报（每校限报2篇）,十佳风尚奖申报（每校限报2个）
function sjTypeLimit($type){
    $roles = array('7'=>1,'8'=>2,'9'=>2);
    if(isset($roles[$type])){
        return $roles[$type];
    }
    return 0;
}
function isSjSchool($sid){
/*1 '南京大学','东南大学','南京航空航天大学','南京理工大学','河海大学','南京农业大学','中国矿业大学','中国药科大学','江南大学','南京森林警察学院', 
 *2 '南京师范大学','南京医科大学','南京中医药大学','南京艺术学院','南京体育学院','江苏第二师范学院','江苏开放大学','南京工业大学','南京财经大学','南京信息工程大学',
 *3 '南京邮电大学','南京林业大学','南京审计学院','南京工程学院','江苏警官学院','江苏经贸职业技术学院','南京工业职业技术学院','南京科技职业技术学院','南京交通职业技术学院','南京特殊教育职业技术学院',
 *4 '江苏省南京工程高等职业学校','南京信息职业技术学院','江苏海事职业技术学院','南京铁道职业技术学院','三江学院','钟山职业技术学院','应天职业技术学院','正德职业技术学院','中国传媒大学南广学院','南京航空航天大学金城学院',
 *5 '无锡职业技术学院','江苏师范大学','徐州医学院','江苏建筑职业技术学院','徐州工业职业技术学院','江苏理工学院','常州大学','常州信息职业技术学院','常州纺织服装职业技术学院','常州工程职业技术学院',
 *6 '苏州大学','苏州科技学院','常熟理工学院','苏州工艺美术职业技术学院','苏州经贸职业技术学院','南通大学','南通航运职业技术学院','江苏工程职业技术学院','淮海工学院','淮阴师范学院',
 *7 '淮阴工学院','江苏食品药品职业技术学院','盐城师范学院','盐城工学院','扬州大学','扬州工业职业技术学院','江苏大学','江苏科技大学','南京团市委','无锡团市委',
 *8 '徐州团市委','常州团市委','苏州团市委','南通团市委','连云港团市委','淮安团市委','盐城团市委','扬州团市委','镇江团市委','泰州团市委',
 *9 '宿迁团市委'
*/
      $all = array(473,587,588,584,585,524,594,622,595,526,600, //1
          586,596,591,601,603,602,618,480,589,593, //2
          592,590,597,598,604,606,614,607,610,609, //3
          612,611,617,608,620,605,613,615,619,2136, //4
          638,623,624,616,625,627,626,629,630,628, //5
          1,2,639,621,507,631,632,633,640,528, //6
          527,531,635,634,636,637,551,550,664,665, //7
          666,667,668,669,670,671,672,673,674,675, //8
          676);
    return in_array($sid, $all);
}
function showSjBack($sid){
    if($sid == 505 || isSjSchool($sid)){
        return true;
    }
    return false;
}
function getEventName($id){
    $res = M('event')->getField('title', 'id='.$id);
    if(!$res){
        $res = '';
    }
    return $res;
}

function getDocCategoryName($id) {
       $arr = array('报道须知','交通指南','户籍知识','医疗卫生','就餐指南','住宿指南','帮困助学','财政管理');
        return $arr[$id-1];
}
//南京工大 社会实践与兼职类
function gdSelect10_0($k=0) {
    $arr[] = array('title'=>'活动级别');
    $arr[] = array('title'=>'是否经校团委认定的重点志愿服务项目');
    $arr[] = array('title'=>'是否获得表彰');
    $arr[] = array('title'=>'学生在校期间注册工商企业开展创业实践并维持运行一年以上');
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdRadio10_0($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'自主参加','credit'=>25);
    $arr[1][] = array('title'=>'校级','credit'=>30);
    $arr[1][] = array('title'=>'省级','credit'=>35);
    $arr[1][] = array('title'=>'国家级','credit'=>40);
    $arr[2][] = array('title'=>'否','credit'=>0);
    $arr[2][] = array('title'=>'是','credit'=>10);
    $arr[3][] = array('title'=>'否','credit'=>0,'br'=>1);
    $arr[3][] = array('title'=>'个人校级表彰','credit'=>10);
    $arr[3][] = array('title'=>'个人省级表彰','credit'=>20);
    $arr[3][] = array('title'=>'个人全国表彰','credit'=>30,'br'=>1);
    $arr[3][] = array('title'=>'集体校级表彰','credit'=>5);
    $arr[3][] = array('title'=>'集体省级表彰','credit'=>10);
    $arr[3][] = array('title'=>'集体全国表彰','credit'=>15);
    $arr[4][] = array('title'=>'否','credit'=>0);
    $arr[4][] = array('title'=>'是','credit'=>75);
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdSelect11_1($k=0) {
    $arr[] = array('title'=>'在竞赛中获得');
    $arr[] = array('title'=>'举办 (__) 个人艺术作品展览或演出');
    $arr[] = array('title'=>'是否艺术类特长生','half'=>3);
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdRadio11_1($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'省级特等奖','credit'=>60);
    $arr[1][] = array('title'=>'省级一等奖','credit'=>50);
    $arr[1][] = array('title'=>'省级二等奖','credit'=>40);
    $arr[1][] = array('title'=>'省级三等奖','credit'=>30);
    $arr[1][] = array('title'=>'省级优秀奖','credit'=>20);
    $arr[1][] = array('title'=>'省级参与','credit'=>10,'br'=>1);
    $arr[1][] = array('title'=>'全国特等奖','credit'=>80);
    $arr[1][] = array('title'=>'全国一等奖','credit'=>70);
    $arr[1][] = array('title'=>'全国二等奖','credit'=>60);
    $arr[1][] = array('title'=>'全国三等奖','credit'=>50);
    $arr[1][] = array('title'=>'全国优秀奖','credit'=>40);
    $arr[1][] = array('title'=>'全国参与','credit'=>15,'br'=>1);
    $arr[1][] = array('title'=>'国际特等奖','credit'=>90);
    $arr[1][] = array('title'=>'国际一等奖','credit'=>80);
    $arr[1][] = array('title'=>'国际二等奖','credit'=>70);
    $arr[1][] = array('title'=>'国际三等奖','credit'=>60);
    $arr[1][] = array('title'=>'国际优秀奖','credit'=>50);
    $arr[1][] = array('title'=>'国际参与','credit'=>20);
    $arr[2][] = array('title'=>'否','credit'=>0);
    $arr[2][] = array('title'=>'全国','credit'=>60);
    $arr[2][] = array('title'=>'省级','credit'=>50);
    $arr[2][] = array('title'=>'市级','credit'=>40);
    $arr[2][] = array('title'=>'校内','credit'=>20);
    $arr[3][] = array('title'=>'否','credit'=>0,'tc'=>' ');
    $arr[3][] = array('title'=>'是','credit'=>0,'tc'=>'特长生总实践学时减半');
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdSelect11_2($k=0) {
    $arr[] = array('title'=>'在竞赛中获得');
    $arr[] = array('title'=>'是否体育特长生','half'=>2);
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdRadio11_2($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'省级冠军','credit'=>60);
    $arr[1][] = array('title'=>'省级亚军','credit'=>50);
    $arr[1][] = array('title'=>'省级季军','credit'=>40);
    $arr[1][] = array('title'=>'省级前四至八名','credit'=>30);
    $arr[1][] = array('title'=>'省级参与','credit'=>10,'br'=>1);
    $arr[1][] = array('title'=>'全国冠军','credit'=>80);
    $arr[1][] = array('title'=>'全国亚军','credit'=>70);
    $arr[1][] = array('title'=>'全国季军','credit'=>60);
    $arr[1][] = array('title'=>'全国前四至八名','credit'=>50);
    $arr[1][] = array('title'=>'全国参与','credit'=>15,'br'=>1);
    $arr[1][] = array('title'=>'国际冠军','credit'=>90);
    $arr[1][] = array('title'=>'国际亚军','credit'=>80);
    $arr[1][] = array('title'=>'国际季军','credit'=>70);
    $arr[1][] = array('title'=>'国际前四至八名','credit'=>60);
    $arr[1][] = array('title'=>'国际参与','credit'=>20);
    $arr[2][] = array('title'=>'否','credit'=>0,'tc'=>' ');
    $arr[2][] = array('title'=>'是','credit'=>0,'tc'=>'特长生总实践学时减半');
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdSelect11_3($k=0) {
    $arr[] = array('title'=>'在竞赛中获得','zusatz'=>'A类：省级专业指导委员会、学会、协会或地方政府主办的创新创业竞赛、“创新杯”校级竞赛、校本科生科技论坛、校数模竞赛以及校电子设计大赛<br/>
        B类：全国专业指导委员会、学会、协会主办的竞赛、“挑战杯”省级竞赛、省级数模竞赛以及省级电子设计大赛<br/>
        C类：国家部委和国际行业协会主办的竞赛、“挑战杯”全国竞赛、全国数模竞赛以及全国电子设计大赛<br/>');
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdRadio11_3($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'A类特等奖','credit'=>50);
    $arr[1][] = array('title'=>'A类一等奖','credit'=>40);
    $arr[1][] = array('title'=>'A类二等奖','credit'=>30);
    $arr[1][] = array('title'=>'A类三等奖','credit'=>20);
    $arr[1][] = array('title'=>'A类优秀奖','credit'=>10);
    $arr[1][] = array('title'=>'A类参与','credit'=>5,'br'=>1);
    $arr[1][] = array('title'=>'B类特等奖','credit'=>80);
    $arr[1][] = array('title'=>'B类一等奖','credit'=>70);
    $arr[1][] = array('title'=>'B类二等奖','credit'=>60);
    $arr[1][] = array('title'=>'B类三等奖','credit'=>50);
    $arr[1][] = array('title'=>'B类优秀奖','credit'=>40);
    $arr[1][] = array('title'=>'B类参与','credit'=>15,'br'=>1);
    $arr[1][] = array('title'=>'C类特等奖','credit'=>90);
    $arr[1][] = array('title'=>'C类一等奖','credit'=>80);
    $arr[1][] = array('title'=>'C类二等奖','credit'=>70);
    $arr[1][] = array('title'=>'C类三等奖','credit'=>60);
    $arr[1][] = array('title'=>'C类优秀奖','credit'=>50);
    $arr[1][] = array('title'=>'C类参与','credit'=>20);
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdSelect11_4($k=0) {
    $arr[] = array('title'=>'专著刊物及作者名次');
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdRadio11_4($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'出版专著第1作者','credit'=>90);
    $arr[1][] = array('title'=>'出版专著第2作者','credit'=>80);
    $arr[1][] = array('title'=>'出版专著第3作者','credit'=>70);
    $arr[1][] = array('title'=>'出版专著第4作者','credit'=>60,'br'=>1);
    $arr[1][] = array('title'=>'出版专著第5作者','credit'=>50);
    $arr[1][] = array('title'=>'出版专著第6作者','credit'=>40);
    $arr[1][] = array('title'=>'出版专著第7作者','credit'=>30);
    $arr[1][] = array('title'=>'出版专著第8作者','credit'=>20);
    $arr[1][] = array('title'=>'出版专著其它作者','credit'=>10,'br'=>2);
    $arr[1][] = array('title'=>'国际核心刊物第1作者','credit'=>90);
    $arr[1][] = array('title'=>'国际核心刊物第2作者','credit'=>80);
    $arr[1][] = array('title'=>'国际核心刊物第3作者','credit'=>70);
    $arr[1][] = array('title'=>'国际核心刊物第4作者','credit'=>60,'br'=>1);
    $arr[1][] = array('title'=>'国际核心刊物第5作者','credit'=>50);
    $arr[1][] = array('title'=>'国际核心刊物第6作者','credit'=>40);
    $arr[1][] = array('title'=>'国际核心刊物第7作者','credit'=>30);
    $arr[1][] = array('title'=>'国际核心刊物第8作者','credit'=>20);
    $arr[1][] = array('title'=>'国际核心刊物其它作者','credit'=>10,'br'=>2);
    $arr[1][] = array('title'=>'国际一般刊物第1作者','credit'=>60);
    $arr[1][] = array('title'=>'国际一般刊物第2作者','credit'=>50);
    $arr[1][] = array('title'=>'国际一般刊物第3作者','credit'=>40,'br'=>1);
    $arr[1][] = array('title'=>'国际一般刊物第4作者','credit'=>30);
    $arr[1][] = array('title'=>'国际一般刊物第5作者','credit'=>20);
    $arr[1][] = array('title'=>'国际一般刊物其它作者','credit'=>10,'br'=>2);
    $arr[1][] = array('title'=>'国内核心刊物第1作者','credit'=>60);
    $arr[1][] = array('title'=>'国内核心刊物第2作者','credit'=>50);
    $arr[1][] = array('title'=>'国内核心刊物第3作者','credit'=>40,'br'=>1);
    $arr[1][] = array('title'=>'国内核心刊物第4作者','credit'=>30);
    $arr[1][] = array('title'=>'国内核心刊物第5作者','credit'=>20);
    $arr[1][] = array('title'=>'国内核心刊物其它作者','credit'=>10,'br'=>2);
    $arr[1][] = array('title'=>'国内一般刊物第1作者','credit'=>30);
    $arr[1][] = array('title'=>'国内一般刊物第2作者','credit'=>20);
    $arr[1][] = array('title'=>'国内一般刊物其它作者','credit'=>10);
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdSelect11_5($k=0) {
    $arr[] = array('title'=>'专利类型及授权人名次');
    $arr[] = array('title'=>'专利受理情况','half'=>2);
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdRadio11_5($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'发明专利第1作者','credit'=>90);
    $arr[1][] = array('title'=>'发明专利第2作者','credit'=>80);
    $arr[1][] = array('title'=>'发明专利第3作者','credit'=>70);
    $arr[1][] = array('title'=>'发明专利第4作者','credit'=>60,'br'=>1);
    $arr[1][] = array('title'=>'发明专利第5作者','credit'=>50);
    $arr[1][] = array('title'=>'发明专利第6作者','credit'=>40);
    $arr[1][] = array('title'=>'发明专利第7作者','credit'=>30);
    $arr[1][] = array('title'=>'发明专利第8作者','credit'=>20);
    $arr[1][] = array('title'=>'发明专利其它作者','credit'=>10,'br'=>2);
    $arr[1][] = array('title'=>'实用新型第1作者','credit'=>60);
    $arr[1][] = array('title'=>'实用新型第2作者','credit'=>50);
    $arr[1][] = array('title'=>'实用新型第3作者','credit'=>40,'br'=>1);
    $arr[1][] = array('title'=>'实用新型第4作者','credit'=>30);
    $arr[1][] = array('title'=>'实用新型第5作者','credit'=>20);
    $arr[1][] = array('title'=>'实用新型其它作者','credit'=>10,'br'=>2);
    $arr[1][] = array('title'=>'外观设计第1作者','credit'=>40);
    $arr[1][] = array('title'=>'外观设计第2作者','credit'=>30);
    $arr[1][] = array('title'=>'外观设计第2作者','credit'=>20);
    $arr[1][] = array('title'=>'外观设计其它作者','credit'=>10);
    $arr[2][] = array('title'=>'已取得专利授权证书','credit'=>0,'tc'=>' ');
    $arr[2][] = array('title'=>'专利申请予以受理但未取得专利授权证书','credit'=>0,'tc'=>'未取得证书总实践学时减半');
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdSelect11_6($k=0) {
    $arr[] = array('title'=>'获奖级别及作者名次');
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdRadio11_6($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'国家级一等奖第1作者','credit'=>100);
    $arr[1][] = array('title'=>'国家级一等奖第2作者','credit'=>90);
    $arr[1][] = array('title'=>'国家级一等奖第3作者','credit'=>80);
    $arr[1][] = array('title'=>'国家级一等奖第4作者','credit'=>70,'br'=>1);
    $arr[1][] = array('title'=>'国家级一等奖第5作者','credit'=>60);
    $arr[1][] = array('title'=>'国家级一等奖第6作者','credit'=>50);
    $arr[1][] = array('title'=>'国家级一等奖第7作者','credit'=>40);
    $arr[1][] = array('title'=>'国家级一等奖其它作者','credit'=>30,'br'=>2);
    $arr[1][] = array('title'=>'国家级二等奖第1作者','credit'=>90);
    $arr[1][] = array('title'=>'国家级二等奖第2作者','credit'=>80);
    $arr[1][] = array('title'=>'国家级二等奖第3作者','credit'=>70);
    $arr[1][] = array('title'=>'国家级二等奖第4作者','credit'=>60,'br'=>1);
    $arr[1][] = array('title'=>'国家级二等奖第5作者','credit'=>50);
    $arr[1][] = array('title'=>'国家级二等奖第6作者','credit'=>40);
    $arr[1][] = array('title'=>'国家级二等奖其它作者','credit'=>30,'br'=>2);
    $arr[1][] = array('title'=>'国家级三等奖第1作者','credit'=>80);
    $arr[1][] = array('title'=>'国家级三等奖第2作者','credit'=>70);
    $arr[1][] = array('title'=>'国家级三等奖第3作者','credit'=>60,'br'=>1);
    $arr[1][] = array('title'=>'国家级三等奖第4作者','credit'=>50);
    $arr[1][] = array('title'=>'国家级三等奖第5作者','credit'=>40);
    $arr[1][] = array('title'=>'国家级三等奖其它作者','credit'=>30,'br'=>2);
    $arr[1][] = array('title'=>'省级一等奖第1作者','credit'=>90);
    $arr[1][] = array('title'=>'省级一等奖第2作者','credit'=>80);
    $arr[1][] = array('title'=>'省级一等奖第3作者','credit'=>70,'br'=>1);
    $arr[1][] = array('title'=>'省级一等奖第4作者','credit'=>60);
    $arr[1][] = array('title'=>'省级一等奖第5作者','credit'=>50);
    $arr[1][] = array('title'=>'省级一等奖第6作者','credit'=>40);
    $arr[1][] = array('title'=>'省级一等奖其它作者','credit'=>30,'br'=>2);
    $arr[1][] = array('title'=>'省级二等奖第1作者','credit'=>80);
    $arr[1][] = array('title'=>'省级二等奖第2作者','credit'=>70);
    $arr[1][] = array('title'=>'省级二等奖第3作者','credit'=>60,'br'=>1);
    $arr[1][] = array('title'=>'省级二等奖第4作者','credit'=>50);
    $arr[1][] = array('title'=>'省级二等奖第5作者','credit'=>40);
    $arr[1][] = array('title'=>'省级二等奖其它作者','credit'=>30,'br'=>2);
    $arr[1][] = array('title'=>'省级三等奖第1作者','credit'=>70);
    $arr[1][] = array('title'=>'省级三等奖第2作者','credit'=>60);
    $arr[1][] = array('title'=>'省级三等奖第3作者','credit'=>50);
    $arr[1][] = array('title'=>'省级三等奖第4作者','credit'=>40);
    $arr[1][] = array('title'=>'省级三等奖其它作者','credit'=>30);
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdSelect12_1($k=0) {
    $arr[] = array('title'=>'担任职务','zusatz'=>'如同一学期兼多职的，只按最高职计算一项<br/>');
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdSelect12_2($k=0) {
    $arr[] = array('title'=>'在校大学生艺术团服务期满(__),且考核合格');
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdSelect12_3($k=0) {
    $arr[] = array('title'=>'个人表彰');
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdSelect12_4($k=0) {
    $arr[] = array('title'=>'集体表彰');
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdSelect12_5($k=0) {
    $arr[] = array('title'=>'专业技能与职业资格证书','zusatz'=>'各类证书不累加，只能申请一次<br/>');
    if($k<=0){
        return $arr;
    }else{
        return $arr[$k-1];
    }
}
function gdRadio12_1($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'团支委','credit'=>4);
    $arr[1][] = array('title'=>'班委','credit'=>4);
    $arr[1][] = array('title'=>'校院两级学生组织各部委员','credit'=>4);
    $arr[1][] = array('title'=>'学生社团骨干','credit'=>4,'br'=>1);
    $arr[1][] = array('title'=>'团支书','credit'=>6);
    $arr[1][] = array('title'=>'班长','credit'=>6);
    $arr[1][] = array('title'=>'校院两级学生组织各部负责人','credit'=>6);
    $arr[1][] = array('title'=>'学生社团负责人','credit'=>6,'br'=>1);
    $arr[1][] = array('title'=>'院级组织主席团','credit'=>8,'br'=>1);
    $arr[1][] = array('title'=>'校级组织主席团','credit'=>10,'br'=>1);
    $arr[1][] = array('title'=>'省学联负责人','credit'=>12);
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdRadio12_2($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'2年','credit'=>30);
    $arr[1][] = array('title'=>'3年','credit'=>50);
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdRadio12_3($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'校级文化活动积极分子','credit'=>5);
    $arr[1][] = array('title'=>'校级优秀团员','credit'=>5,'br'=>1);
    $arr[1][] = array('title'=>'校级优秀学生干部','credit'=>10);
    $arr[1][] = array('title'=>'校级优秀学生会干部','credit'=>10);
    $arr[1][] = array('title'=>'校级优秀社团干部','credit'=>10);
    $arr[1][] = array('title'=>'校级三好学生','credit'=>10,'br'=>1);
    $arr[1][] = array('title'=>'校级三好学生标兵','credit'=>15,'br'=>1);
    $arr[1][] = array('title'=>'校级十佳团员','credit'=>20);
    $arr[1][] = array('title'=>'校级十佳团支书','credit'=>20);
    $arr[1][] = array('title'=>'校级爱心大使','credit'=>20);
    $arr[1][] = array('title'=>'校级自强之星','credit'=>20,'br'=>2);
    $arr[1][] = array('title'=>'省级文化活动积极分子','credit'=>25);
    $arr[1][] = array('title'=>'省级优秀团员','credit'=>25,'br'=>1);
    $arr[1][] = array('title'=>'省级优秀学生干部','credit'=>30);
    $arr[1][] = array('title'=>'省级优秀学生会干部','credit'=>30);
    $arr[1][] = array('title'=>'省级优秀社团干部','credit'=>30);
    $arr[1][] = array('title'=>'省级三好学生','credit'=>30,'br'=>1);
    $arr[1][] = array('title'=>'省级三好学生标兵','credit'=>35,'br'=>1);
    $arr[1][] = array('title'=>'省级十佳团员','credit'=>40);
    $arr[1][] = array('title'=>'省级十佳团支书','credit'=>40);
    $arr[1][] = array('title'=>'省级爱心大使','credit'=>40);
    $arr[1][] = array('title'=>'省级自强之星','credit'=>40,'br'=>2);
    $arr[1][] = array('title'=>'国家级文化活动积极分子','credit'=>65);
    $arr[1][] = array('title'=>'国家级优秀团员','credit'=>65,'br'=>1);
    $arr[1][] = array('title'=>'国家级优秀学生干部','credit'=>70);
    $arr[1][] = array('title'=>'国家级优秀学生会干部','credit'=>70);
    $arr[1][] = array('title'=>'国家级优秀社团干部','credit'=>70);
    $arr[1][] = array('title'=>'国家级三好学生','credit'=>70,'br'=>1);
    $arr[1][] = array('title'=>'国家级三好学生标兵','credit'=>75,'br'=>1);
    $arr[1][] = array('title'=>'国家级十佳团员','credit'=>80);
    $arr[1][] = array('title'=>'国家级十佳团支书','credit'=>80);
    $arr[1][] = array('title'=>'国家级爱心大使','credit'=>80);
    $arr[1][] = array('title'=>'国家级自强之星','credit'=>80);
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdRadio12_4($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'省级表彰负责人','credit'=>20);
    $arr[1][] = array('title'=>'省级表彰骨干成员','credit'=>10,'br'=>1);
    $arr[1][] = array('title'=>'国家级表彰负责人','credit'=>40);
    $arr[1][] = array('title'=>'国家级表彰骨干成员','credit'=>20);
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdRadio12_5($k,$v=0) {
    $arr = array();
    $arr[1][] = array('title'=>'是','credit'=>10,'input'=>'zs_name');
    if($v<=0){
        return $arr[$k];
    }else{
        return $arr[$k][$v-1];
    }
}
function gdTitle($type,$type1){
    if($type==10){
        return array('寒暑期社会实践类','活动名称');
    }elseif($type==11){
        $res = '文体与创新创业竞赛类 / ';
        if($type1==1){
            return array($res.'文化艺术竞赛','竞赛名称');
        }elseif($type1==2){
            return array($res.'体育竞技比赛','竞赛名称');
        }elseif($type1==3){
            return array($res.'创新创业竞赛','竞赛名称');
        }elseif($type1==4){
            return array($res.'出版专著、发表学术论文','论文名称');
        }elseif($type1==5){
            return array($res.'发明专利','专利名称');
        }elseif($type1==6){
            return array($res.'科技成果奖','成果名称');
        }
    }elseif($type==12){
        return array('社会工作与技能培训类','名称');
    }
}
function hasZs($uid){
    $cache = Mmc('Ecapply_haszs_'.$uid);
    if($cache===false){
        $cache = M('ec_applygd')->where('has_zs=1 and uid='.$uid)->count();
        Mmc('Ecapply_haszs_'.$uid,$cache,0,3600);
    }
    return $cache;
}
//2.15-8.15和8.16-2.14
function calcSemester($time,$stime=0){
    if($stime>0 && strlen($stime)==5){
        $year = substr($stime,0,4);
        $sem = substr($stime,4,1);
        if($sem=='1'){
            return $year.'下';
        }
        return $year.'上';
    }

    $year = date('Y', $time);
    $day = date('m-d', $time);
    if($day>'08-15'){
        $res = $year.'下';
    }elseif($day>'02-14'){
        $res = $year.'上';
    }else{
        $year -= 1;
        $res = $year.'下';
    }
    return $res;
}
function getSurveyCount($id){
    if($id==105){
        return 13516;
    }
    $cache = Mmc('Survey_num_'.$id);
    if($cache===false){
        $cache = M('survey_user')->where('survey_id='.$id)->count();
        Mmc('Survey_num_'.$id,$cache,0,3600*24);
    }
    return $cache;
}
function getVote($surveyId){
    $cache = Mmc('Survey_vote_'.$surveyId);
    if($cache===false){
        $mapVote['suid'] = $surveyId;
        $mapVote['isDel'] = 0;
        $vote = D("SurveyVote",'event')->where($mapVote)->field('id,title,type')->order("display_order asc")->findAll();
        if(!$vote){
            return array();
        }
        $dao = M('survey_opt');
        foreach ($vote as &$v) {
            $v['opt'] = $dao->where('vote_id='.$v['id'])->field('id,name')->findAll();
        }
        $cache = serialize($vote);
        Mmc('Survey_vote_'.$surveyId,$cache,0,3600*24);
        return $vote;
    }
    return unserialize($cache);
}
function getVotePers($surveyId){
    $cache = Mmc('Survey_vote_pers_'.$surveyId);
    if($cache===false){
        $vote = getVote($surveyId);
        $result = M('survey_result')->where('survey_id='.$surveyId)->field('opt_id,num')->findAll();
        $result = orderArray($result, 'opt_id');
        $data = array();
        foreach ($vote as $v) {
            $total = 0;
            foreach ($v['opt'] as &$opt) {
                $optId = $opt['id'];
                $num = $result[$optId]['num'];
                $total += $num;
                $data[$optId]['num'] = $num;
                $opt['num'] = $num;
            }
            foreach ($v['opt'] as $op) {
                $data[$op['id']]['prozent'] = round(((float)$op['num']/(float)$total)*100,0);
            }
        }
        $cache = serialize($data);
        Mmc('Survey_vote_pers_'.$surveyId,$cache,0,3600*24);
        return $data;
    }
    return unserialize($cache);
}