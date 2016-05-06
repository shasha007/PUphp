<?php

class MakefriendsUserModel extends Model {

    public function getUserInfo($uid) {
        $cache = Mmc('Makefriends_user_' . $uid);
        if ($cache !== false) {
            return json_decode($cache, true);
        }
        $res = $this->where('uid=' . $uid)->find();
        if (!$res) {
            return false;
        }
        $res['nickname'] = $this->getNickname($uid);
        Mmc('Makefriends_user_' . $uid, json_encode($res),0,3600*8);
        return $res;
    }

    //获取系统昵称
    public function getNickname($uid){
        $nickname = getUserField($uid, 'uname');
        if(!$nickname){
            return '';
        }
        return $nickname;
    }

    //得到用户学校所在省市
    public function getArea($uid){
        $cityId = getCityByUid($uid);
        $city = M('Citys')->getField('city', 'id='.$cityId);
        $pid = getProvByUid($uid);
        $area = M('Province')->getField('title','id='.$pid);
        return $area.'省'.$city.'市';
    }

    //更新用户缓存
    public function upUserInfo($uid,$key,$value,$type=''){
        $this->checkAndAddUser($uid);
        if ($type=='+') {
            $res = $this->setInc($key, 'uid =' . $uid, $value);
        }elseif ($type=='-') {
            $res = $this->setDec($key, 'uid =' . $uid, $value);
        }else{
            $res = $this->setField($key, $value, 'uid =' . $uid);
        }
        if(!$res){
            return false;
        }
        return $this->upCache($uid, $key, $value,$type);
    }

    public function upCache($uid,$key,$value,$type=''){
        $cache = Mmc('Makefriends_user_' . $uid);
        if ($cache === false) {
            return true;
        }
        $obj = json_decode($cache, true);
        if ($type=='+') {
            $obj[$key] += $value;
        }elseif ($type=='-') {
            $obj[$key] -= $value;
        }elseif ($type=='unset') {
            if(!isset($obj[$key])){
                return true;
            }
            unset($obj[$key]);
        }else{
            if(isset($obj[$key]) && $obj[$key]==$value){
                return true;
            }
            $obj[$key] = $value;
        }
        if($key=='contribution' && isset($obj['gxLevel'])){
            unset($obj['gxLevel']);
        }
        Mmc('Makefriends_user_' . $uid, json_encode($obj),0,3600*8);
        return true;
    }

    //用户贡献等级
    public function getGxLevel($uid) {
        $user = $this->getUserInfo($uid);
        if ($user['gxLevel']) {
            return $user['gxLevel'];
        }
        $gx = $user['contribution'];
        $cLevel = 1;
        $up = 100;
        $rest = $gx;
        while ($rest>=$up){
            $cLevel += 1;
            $rest -= $up;
            $up += 20;
        }
//        $grade1 = 19 * 120;
//        $grade2 = 10 * 180 + $grade1;
//        if ($gx == 0) {
//            $cLevel = 1;
//        } elseif ($gx < $grade1) {
//            $cLevel = 1 + floor($gx / 120);
//        } elseif ($gx < $grade2) {
//            $cLevel = 20 + floor(($gx - $grade1) / 180);
//        } else {
//            $cLevel = 30 + floor(($gx - $grade2) / 240);
//        }
        $user['gxLevel'] = $cLevel;
        Mmc('Makefriends_user_' . $uid, json_encode($user),0,3600*8);
        return $cLevel;
    }

    //下一等级升级需达到多少贡献值 输入当前等级
    public function gxLevelUpNeed($level){
        if($level%2==0){
            $count20 = $level/2*($level-1);
        }else{
            $count20 = ($level-1)*$level/2;
        }
        return $level*100+($count20*20);
    }

    //用户今日已送花数量
    public function hasSend($uid,$user=''){
        if(!$user){
            $user = $this->getUserInfo($uid);
        }
        $today = date('Y-m-d');
        if(!isset($user['hasSend']) || $user['hasSendDay']!=$today){
            $map['day'] = $today;
            $map['uid'] = $uid;
            $res = D('MakefriendsGift', 'makefriends')->where($map)->field('sum(giftNum) as sumGift')->find();
            $hasSend = 0;
            if($res){
                $hasSend = $res['sumGift'];
            }
            $this->upCache($uid, 'hasSend', $hasSend);
            $this->upCache($uid, 'hasSendDay', $today);
            return $hasSend;
        }
        return $user['hasSend'];
    }

    //今日剩余送花数量
    public function todayRestGift($uid) {
        $gxLevel = $this->getGxLevel($uid);
        $max = 10 + ($gxLevel - 1) * 2;
        $res = $max - $this->hasSend($uid);
        if($res<=0){
            return 0;
        }
        return $res;
    }

    public function getHeadPhoto_old($uid,$type, $user = ''){
        if (!$user) {
            $user = $this->getUserInfo($uid);
        }
        $key = 'headPhoto_'.$type;
        if (!isset($user[$key]) || $user[$key]=='') {
            $aid = $user['headPhotoId'];
            $pic = '';
            if($aid){
                $attach = getAttach($aid);
                $file = $attach['savepath'] . $attach['savename'];
                if($type=='original'){
                    $pic = tsMakeThumbUp($file, 0, 0, 'no');
                }else{
                    $w = 150;
                    if($type=='middle'){
                        $w = 300;
                    }elseif($type=='big'){
                        $w = 500;
                    }
                    $pic = tsMakeThumbUp($file, $w, 0, 'f');
                }
            //默认头像
            }else{
                return PIC_URL.'/data/uface/makefriends_'.$type.'.jpeg';
            }

            $this->upCache($uid, $key, $pic);
            return $pic;
        }
        return $user[$key];
    }

    public function getHeadPhoto($uid,$type='middle', $user = ''){
//        if (!$user) {
//            $user = $this->getUserInfo($uid);
//        }
        $key = 'headPhoto_'.$type;
         if($type == 'middle'){
             $type = 'm';
         }else if($type == 'small'){
             $type = 's';
         }else if($type == 'original'){
             $type = 'original';
         }else if($type == 'xbig'){
             $type = 'xbig';
         }else{
             $type = 'big';
         }
//         if (!isset($user[$key]) || $user[$key]=='') {
                $pic = getUserFace($uid, $type);
                $this->upCache($uid, $key, $pic);
                return $pic;
//         }
//         return $user[$key];
    }
    //用户照片总数
    public function getPhotoCount($uid) {
        $user = $this->getUserInfo($uid);
        if (isset($user['photoCount'])) {
            return intval($user['photoCount']);
        }
        $count = D('MakefriendsPhoto', 'makefriends')->where('isDel=0 and uid=' . $uid)->count();
        $user['photoCount'] = intval($count);
        Mmc('Makefriends_user_' . $uid, json_encode($user),0,3600*8);
        return $count;
    }
    private function _getCacheUserPage($limit = 10, $page = 1, $order = 'weekRq DESC, cTime DESC'){
        //缓存每11分钟更新
        $cacheArr = array();
        $cache = Mmc('Makefriends_weekUser');
        if ($cache !== false) {
            $cache = json_decode($cache, true);
            $giltTime = strtotime('-11 minute');
            if($cache['time']<$giltTime){
                Mmc('Makefriends_weekUser',null);
            }else{
                $cacheArr = $cache;
            }
        }
        $key = 'page'.$page;
        if(isset($cacheArr[$key])){
            return $cacheArr[$key];
        }else{
            $offset = ($page - 1) * $limit;
            $list = $this->field('uid')->where('popularity>0')->order($order)->limit("$offset,$limit")->select();
            if (!$list) {
                $list = array();
            }
            $cacheArr[$key] = $list;
            if(!isset($cacheArr['time'])){
                $cacheArr['time'] = time();
            }
            Mmc('Makefriends_weekUser', json_encode($cacheArr),0,60*11);
            return $list;
        }
    }
    //week month
    private function _getCacheUserPage_1($limit = 10, $page = 1, $type='weekRq',$area=''){
        //缓存每11分钟更新
        $cacheArr = array();
        $mncType = 'Makefriends_pai_'.$type;
        $cache = Mmc($mncType);
        if ($cache !== false) {
            $cache = json_decode($cache, true);
            $giltTime = strtotime('-11 minute');
            if($cache['time']<$giltTime){
                Mmc($mncType,null);
            }else{
                $cacheArr = $cache;
            }
        }
        $key = $area.'_'.$page;
        if(isset($cacheArr[$key])){
            return $cacheArr[$key];
        }else{
            $offset = ($page - 1) * $limit;
            $field = 'uid,'.$type.' as rq';
            $map = array();
            if($type=='cRq'){
                $cityArr[] = array(2);    //1
                $cityArr[] = array(3, 1, 5, 7); //2
                $cityArr[] = array(6, 12, 4, 16, 9, 10, 15, 8); //3
                $map['cityId'] = array('in', $cityArr[$area]);
            }
            $order = $type.' DESC, cTime DESC';
            $list = $this->field($field)->where($map)->order($order)->limit("$offset,$limit")->select();
            if (!$list) {
                $list = array();
            }
            $cacheArr[$key] = $list;
            if(!isset($cacheArr['time'])){
                $cacheArr['time'] = time();
            }
            Mmc($mncType, json_encode($cacheArr),0,60*11);
            return $list;
        }
    }

    //User列表 人气排行
    public function popularityList($limit = 10, $page = 1, $type='weekRq', $area='') {
        $list = $this->_getCacheUserPage_1($limit, $page, $type, $area);
        foreach ($list as $v) {
            $uid = $v['uid'];
            $user = $this->getUserInfo($uid);
            $row['uid'] = $user['uid'];
            $row['nickname'] = $user['nickname'];
            $row['popularity'] = $v['rq'];
            $row['headPhoto_original'] = $this->getHeadPhoto($uid,'original', $user);
            $row['headPhoto_big'] = $this->getHeadPhoto($uid,'big', $user);
            $row['headPhoto_xbig'] = $this->getHeadPhoto($uid,'xbig', $user);
            $row['headPhoto_middle'] = $this->getHeadPhoto($uid,'middle', $user);
            $row['photoCount'] = $this->getPhotoCount($user['uid']);
            $row['w'] = $user['w'];
            $row['h'] = $user['h'];
            $res[] = $row;
        }
        return $res;
    }
    /**
     *1.南京地区, 2. 江南地区：无锡市, 苏州市, 镇江市, 常州市 ；3.江北地区：徐州市, 连云港市,淮安市,宿迁市,盐城市, 扬州市, 泰州市, 南通市
     */
    public function cityPopularityList(){
        $city = array('南京地区','江南地区','江北地区');
        foreach($city as $k=>$val){
            $row = array();
            $row['data'] = $this->popularityList(10,1,'cRq',$k);
            $row['city'] = $val;
            $list[] = $row;
        }
        return $list;
    }

    public function userHome($uid,$nowUid,$bylogin=false){
        if ($uid<=0) {
            return array();
        }
        $user = $this->getUserInfo($uid);
        if (!$user) {
            $info = array();
            $info['nickname'] = $this->getNickname($uid);
            $info['popularity'] = 0;
            $info['contribution'] = 0;
            $info['newComment'] = 0;
            $info['newPhoto'] = 0;
            $info['newGift'] = 0;
            $info['headPhoto_small'] = getUserFace($uid, 'big');;
            return $info;
        }
        $list['nickname'] = $this->getNickname($uid);
        $list['popularity'] = $user['popularity']; //人气值
        if(!$bylogin){
            $list['praiseCount'] = $user['praiseAllCount']; //被喜欢
            $list['uid'] = $uid;
            $list['backCount'] = $user['backAllCount']; //被评论
            //照片总数
            $list['photoCount'] = $this->getPhotoCount($uid);
            $list['canAttention'] = 0; //可否关注
            if($uid != $nowUid){
                D('MakefriendsAttention', 'makefriends')->readPhoto($nowUid,$uid);
                $hasAttention = D('MakefriendsAttention', 'makefriends')->hasAttention($nowUid,$uid);
                $list['hasAttention'] = $hasAttention?1:0;
            }
            $list['headPhoto_original'] = $this->getHeadPhoto($uid,'xbig', $user);
            $list['w'] = $user['w'];
            $list['h'] = $user['h'];
        }else{
            $list['contribution'] = $user['contribution']; //贡献
            $list['newComment'] = $this->hasNewComment($uid);
            $list['newPhoto'] = $this->hasNewPhoto($uid);
            $list['newGift'] = $this->hasNewGift($uid);
            $list['headPhoto_small'] = $this->getHeadPhoto($uid,'big', $user);
        }
        return $list;
    }

    //新评论
    public function hasNewComment($uid){
        $user = $this->getUserInfo($uid);
        if (isset($user['newComment'])) {
            return $user['newComment'];
        }
        $newComment = 0;
        $dao = D('MakefriendsPhoto', 'makefriends');
        $res = $dao->where('newComment=1 and uid='.$uid)->field('photoId')->find();
        if($res){
            $newComment = 1;
        }
        $this->upCache($uid, 'newComment', $newComment);
        return $newComment;
    }
    //新照片 （我的关注）
    public function hasNewPhoto($uid){
        $user = $this->getUserInfo($uid);
        if (isset($user['newPhoto'])) {
            return $user['newPhoto'];
        }
        $newPhoto = 0;
        $dao = D('MakefriendsAttention', 'makefriends');
        $res = $dao->where('newPhoto=1 and uid='.$uid)->field('uid')->find();
        if($res){
            $newPhoto = 1;
        }
        $this->upCache($uid, 'newPhoto', $newPhoto);
        return $newPhoto;
    }
    //新礼物
    public function hasNewGift($uid){
        $user = $this->getUserInfo($uid);
        if (isset($user['newGift'])) {
            return $user['newGift'];
        }
        $newGift = 0;
        $dao = D('MakefriendsGift', 'makefriends');
        $res = $dao->where('newGift=1 and toid='.$uid)->field('uid')->find();
        if($res){
            $newGift = 1;
        }
        $this->upCache($uid, 'newGift', $newGift);
        return $newGift;
    }

    //backAllcount总评论数+1
    public function addBackAllCount($uid) {
        return $this->upUserInfo($uid,'backAllCount',1,'+');
    }
    //添加照片
    public function addPhoto($uid){
        $this->incGx($uid, 'photo');
        //照片总数+1
        $user = Mmc('Makefriends_user_' . $uid);
        if (($user !== false) && isset($user['photoCount'])) {
            $this->upCache($uid, 'photoCount',1,'+');
        }
    }
    //praiseAllcount总喜欢数+1
    public function addPraiseAllCount($uid) {
        return $this->upUserInfo($uid,'praiseAllCount',1,'+');
    }
    //检查交友账号是否存在，否则自动建立匿名访客
    public function checkAndAddUser($uid){
        $user = $this->getUserInfo($uid);
        if (!$user) {
            $res = $this->editNickname($uid, '匿名访客');
        }else{
            $res = true;
        }
        return $res;
    }

    //增加用户、修改昵称
    public function editNickname($uid, $name) {
        $nickname = t($name);
        if (!$nickname) {
            $this->error = '昵称不可为空';
            return false;
        }
        $len = get_str_length($nickname);
        if ($len>8) {
            $this->error = '昵称最多8个字符';
            return false;
        }
        $user = $this->getUserInfo($uid);
        if ($user) {
            $result = $this->upUserInfo($uid,'nickname',$nickname);
        } else {
            $data['uid'] = $uid;
            $data['nickname'] = $nickname;
            $data['cTime'] = time();
            $sid = getUserField($uid, 'sid');
            $data['cityId'] = model('Schools')->getCityId($sid);
            //贡献值增加操作
//            $data['contribution'] = 1;
            $result = $this->add($data);
            if ($result) {
//                Mmc('Make_friend_login_' . date('Ymd') . '_' . $uid, 1, 0, 3600 * 24);
//                $data['uid'] = $uid;
//                $data['type'] = 'login';
//                $data['total'] = 1;
//                $data['ctime'] = time();
//                M('makefriends_usergx')->add($data);
                //临时未满10人刷新人气排行
                $cache = Mmc('Makefriends_weekUser');
                if($cache!==false){
                    $cache = json_decode($cache, true);
                    $count = count($cache['page1']);
                    if($count<12){
                        Mmc('Makefriends_weekUser',null);
                    }
                }
            }
        }
        if ($result) {
            return true;
        }
        $this->error = '操作失败';
        return false;
    }

    //修改头像
    public function upHeadPhoto($uid) {
        if (empty($_FILES['pic']['name'])) {
            $this->error = '未选择上传的图片';
            return false;
        }
        if (!isImg($_FILES['pic']['tmp_name'])) {
            $this->error = '图片格式不对';
            return false;
        }
        $user = $this->getUserInfo($uid);
        if (!$user) {
            $this->error = '用户不存在';
            return false;
        }
        list($sr_w, $sr_h) = @getimagesize($_FILES['pic']['tmp_name']);
        $options = array();
        $options['allow_exts'] = 'jpeg,gif,jpg,png,bmp';
        $info = X('Xattach')->upload('makefriends_headphoto', $options);
        if (!$info['status']) {
            $this->error = '图片上传失败';
            return false;
        }
        $data['headPhotoId'] = $info['info'][0]['id'];
        $data['w'] = $sr_w;
        $data['h'] = $sr_h;
        $result = $this->where('uid='.$uid)->save($data);
//        $result = $this->upUserInfo($uid,'headPhotoId',$aid);
        if ($result) {
            //删除旧的
            if ($user['headPhotoId'] > 0) {
                model('Attach')->deleteAttach($user['headPhotoId'], true);
            }
            $user = $this->getUserInfo($uid);
            $user['headPhotoId'] = $data['headPhotoId'];
            unset($user['headPhoto_original']);
            unset($user['headPhoto_middle']);
            unset($user['headPhoto_small']);
            unset($user['headPhoto_big']);
            unset($user['headPhoto_xbig']);
            $user['w'] = $sr_w;
            $user['h'] = $sr_h;
            Mmc('Makefriends_user_' . $uid, json_encode($user),0,3600*8);
            return true;
        }
        $this->error = '操作失败';
        return false;
    }

    /**
     * 增加人气值
     * @param type $type 3被赞 4被评论
     */
    public function incRq($uid, $type, $count=0) {
        $num = $this->getRq($type);
        if($type=='gift'){
            $num = $num*$count;
        }
        if($num==0){
            return true;
        }
        //每日人气上限
        if($type=='comment'){
            $today = date('Ymd');
            $rqlimit = intval(Mmc('Makefriends_rqlimit_' . $today . '_' . $uid));
            if ($rqlimit>=20) {
                return true;
            }
        }
        $data['popularity'] = array('exp','popularity+'.$num);
        $data['weekRq'] = array('exp','weekRq+'.$num);
        $data['monthRq'] = array('exp','monthRq+'.$num);
        $data['cRq'] = array('exp','cRq+'.$num);
        $this->checkAndAddUser($uid);
        $res = $this->where('uid='.$uid)->save($data);
        if(!$res){
            return false;
        }
        $obj = $this->getUserInfo($uid);
        if ($obj !== false) {
            $obj['popularity'] += $num;
            $obj['weekRq'] += $num;
            $obj['monthRq'] += $num;
            $obj['cRq'] += $num;
            Mmc('Makefriends_user_' . $uid, json_encode($obj),0,3600*8);
        }
        //每日贡献上限
        if($type=='comment'){
            $newGx = $rqlimit+$num;
            Mmc('Makefriends_rqlimit_' . $today . '_' . $uid, $newGx, 0, 3600 * 24);
        }
        return true;
    }

    /**
     * 减少人气值
     * @param type $type
     */
    public function decRq($uid, $type, $count=1) {
        if($type == 'comment'){
            return true;
        }
        $num = $this->getRq($type);
        if($type=='gift'){
            $num = $num*$count;
        }
        if($num==0){
            return true;
        }
        $rq = $num*$count;
        $data['popularity'] = array('exp','popularity-'.$rq);
        $data['weekRq'] = array('exp','weekRq-'.$rq);
        $data['monthRq'] = array('exp','monthRq-'.$rq);
        $data['cRq'] = array('exp','cRq-'.$rq);
        $this->checkAndAddUser($uid);
        $res = $this->where('uid='.$uid)->save($data);
        if(!$res){
            return false;
        }
        $obj = $this->getUserInfo($uid);
        if ($obj !== false) {
            $obj['popularity'] -= $rq;
            $obj['weekRq'] -= $rq;
            $obj['monthRq'] -= $rq;
            $obj['cRq'] -= $rq;
            Mmc('Makefriends_user_' . $uid, json_encode($obj),0,3600*8);
        }
        return true;
    }
    /**
     * 增加贡献值
     * @param type
     */
    public function incGx($uid, $type,$toid=0,$count=0) {
        $num = $this->getGx($type);
        if($type=='gift'){
            $num = $num*$count;
        }
        if($num==0){
            return true;
        }
        //每日贡献上限
        if($type=='comment'){
            $today = date('Ymd');
            $gxlimit = intval(Mmc('Makefriends_gxlimit_' . $today . '_' . $uid));
            if ($gxlimit>=20) {
                return true;
            }
        }
        $res = $this->upUserInfo($uid,'contribution',$num,'+');
        if($res){
            $data['uid'] = $uid;
            $data['type'] = $type;
            $data['toid'] = $toid;
            $data['total'] = $num;
            $data['ctime'] = time();
            M('makefriends_usergx')->add($data);
            //每日贡献上限
            if($type=='comment'){
                $newGx = $gxlimit+$num;
                Mmc('Makefriends_gxlimit_' . $today . '_' . $uid, $newGx, 0, 3600 * 24);
            }
        }
        return $res;
    }

    /**
     * 减少贡献值
     * @param type $type
     */
    public function decGx($uid, $type, $toid=0) {
        if($type == 'comment'){
            return true;
        }
        $num = $this->getGx($type);
        if($num==0){
            return true;
        }
        $res = $this->upUserInfo($uid,'contribution',$num,'-');
        if($res){
            $data['uid'] = $uid;
            $data['type'] = $type;
            $data['toid'] = $toid;
            $data['total'] = 0-$num;
            $data['ctime'] = time();
            M('makefriends_usergx')->add($data);
        }
        return $res;
    }

    //登录
    public function denglu($uid) {
        $user = $this->getUserInfo($uid);
        if(!$user){
            return false;
        }
        $today = date('Ymd');
        $cache = Mmc('Make_friend_login_' . $today . '_' . $uid);
        if ($cache !== false) {
            return false;
        }
        //贡献值增加操作
        $gx = $this->getGx('login');
        $res = $this->upUserInfo($uid,'contribution',$gx,'+');
        if ($res) {
            Mmc('Make_friend_login_' . $today . '_' . $uid, 1, 0, 3600 * 24);
            $data['uid'] = $uid;
            $data['type'] = 'login';
            $data['toid'] = 0;
            $data['total'] = $gx;
            $data['ctime'] = time();
            M('makefriends_usergx')->add($data);
            return true;
        }
        return false;
    }
    // 可得贡献值
    public function getGx($type){
        switch ($type) {
            case 'login':
            case 'praise':
                return 1;
                break;
            case 'photo':
                return 5;
                break;
            case 'comment':
            case 'attention':
                return 2;
                break;
            case 'gift':
                return 1;
                break;
            default:
                return 0;
                break;
        }
    }
    // 可得人气
    public function getRq($type){
        switch ($type) {
            case 'praise':
                return 1;
                break;
            case 'comment':
            case 'attention':
                return 1;
                break;
            case 'gift':
                return 1;
                break;
            default:
                return 0;
                break;
        }
    }
    public function initWeekUser(){
        $list = $this->where('weekRq!=0')->field('uid')->findAll();
        $this->setField('weekRq', 0);
        foreach($list as $v){
            $uid = $v['uid'];
            Mmc('Makefriends_user_' . $uid,null);
        }
        Mmc('Makefriends_weekUser',null);
    }

    //周 月 排行 列表缓存 $type = week month c
   public function initEveryUser($type){
        $list = $this->where($type.'Rq!=0')->field('uid')->findAll();
        $this->setField($type.'Rq', 0);
        foreach($list as $v){
            $uid = $v['uid'];
            Mmc('Makefriends_user_' . $uid,null);
        }
        Mmc('Makefriends_'.$type.'User',null);
   }



}

?>
