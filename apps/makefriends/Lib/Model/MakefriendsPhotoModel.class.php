<?php

class MakefriendsPhotoModel extends Model {

    public function getPhotoInfo($pid,$detail=false) {
        $cache = Mmc('Makefriends_photo_' . $pid);
        if ($cache !== false) {
           return json_decode($cache, true);
        }
        $res = $this->where('isDel=0 and photoId=' . $pid)->field('photoId,uid,content,cTime,praiseCount,backCount,weekCount,monthCount,w,h,audit_result,act_id')->find();
        if($detail) {
        	$attInfo = model('Attach')->getAllAttachById($res['photoId']);
        }
        if($attInfo) $res = array_merge($res,array('attUrl'=>$attInfo));
        if (!$res) {
            return false;
        }
        
        Mmc('Makefriends_photo_' . $pid, json_encode($res), 0, 3600 * 18);
        return $res;
    }

    //更新照片信息+缓存
    public function upPhotoInfo($photoId, $key, $value, $type = '') {
        if ($type == '+') {
            $res = $this->setInc($key, 'photoId =' . $photoId, $value);
        } elseif ($type == '-') {
            $res = $this->setDec($key, 'photoId =' . $photoId, $value);
        } else {
            $res = $this->setField($key, $value, 'photoId =' . $photoId);
        }
        if (!$res) {
            return false;
        }
        return $this->upCache($photoId, $key, $value, $type);
    }

    public function upCache($photoId, $key, $value, $type = '') {
        $cache = Mmc('Makefriends_photo_' . $photoId);
        if ($cache === false) {
            return true;
        }
        $obj = json_decode($cache, true);
        if ($type == '+') {
            $obj[$key] += $value;
        } elseif ($type == '-') {
            $obj[$key] -= $value;
        } else {
            if (isset($obj[$key]) && $obj[$key] == $value) {
                return true;
            }
            $obj[$key] = $value;
        }
        Mmc('Makefriends_photo_' . $photoId, json_encode($obj), 0, 3600 * 18);
        return true;
    }

    public function getPhotoPic($photoId,$type, $photo = '') {
        if (!$photo) {
            $photo = $this->getPhotoInfo($photoId);
        }
        $key = 'pic_'.$type;
        if (!isset($photo[$key])) {
            $attach = getAttach($photoId);
            $file = $attach['savepath'] . $attach['savename'];
            if($type=='original'){
                $pic = tsMakeThumbUp($file, 0, 0, 'no');
            }else{
                $w = 360;
                if($type=='big'){
                    $w = 540;
                }
                $pic = tsMakeThumbUp($file, $w, 0, 'f');
            }
            $this->upCache($photoId, $key, $pic);
            return $pic;
        }
        return $photo[$key];
    }

    public function getPhotoNickname($photoId, $photo = '') {
        if (!$photo) {
            $photo = $this->getPhotoInfo($photoId);
        }
        if(!$photo){
            return '';
        }
        $nickname = D('MakefriendsUser','makefriends')->getNickname($photo['uid']);
        return $nickname;
    }
    //根据photo id获取photo字段
    private function _topField($list){
        $res = array();
        foreach ($list as $v) {
            $photoId = $v['photoId'];
            $row = array();
            $photo = $this->getPhotoInfo($photoId);
            $row['photoId'] = $photo['photoId'];
            $row['weekCount'] = $photo['weekCount'];
            $row['monthCount'] = $photo['monthCount'];
            $row['uid'] = $photo['uid'];
            $row['praiseCount'] = $photo['weekCount'];
            $row['backCount'] = $photo['backCount'];
            $row['photoCount'] = $photo['backCount'];
            $row['audit_result'] = $photo['audit_result'];
            $row['act_id'] = $photo['act_id'];
            $row['newComment'] = 0;
            $row['w'] = $photo['w'];
            $row['h'] = $photo['h'];
            $row['pic_big'] = $this->getPhotoPic($photoId,'big', $photo);
            $row['pic_middle'] = $this->getPhotoPic($photoId,'middle', $photo);
            $row['nickname'] = $this->getPhotoNickname($photoId, $photo);
            $res[] = $row;
        }
        return $res;
    }
    //排行榜 点赞照片列表 周
    public function getCacheWeekTop($limit,$page){
        //缓存每11分钟更新
        $cacheArr = array();
        $cache = Mmc('Makefriends_weekTop');
        if ($cache !== false) {
            $cache = json_decode($cache, true);
            $giltTime = strtotime('-11 minute');
            if($cache['time']<$giltTime){
                Mmc('Makefriends_weekTop',null);
            }else{
                $cacheArr = $cache;
            }
        }
        $key = 'page'.$page;
        if(isset($cacheArr[$key])){
            return $cacheArr[$key];
        }else{
            $offset = ($page - 1) * $limit;
            $map['isDel'] = 0;
            $list = $this->where($map)->field('photoId')->order('weekCount DESC, photoId DESC')->limit("$offset,$limit")->select();
            $res = $this->_topField($list);
            $cacheArr[$key] = $res;
            if(!isset($cacheArr['time'])){
                $cacheArr['time'] = time();
            }
            Mmc('Makefriends_weekTop', json_encode($cacheArr),0,60*11);
            return $res;
        }
    }
    //排行榜 点赞照片列表 周
    public function getCacheMonthTop($limit,$page){
        //缓存每11分钟更新
        $cacheArr = array();
        $cache = Mmc('Makefriends_MonthTop');
        if ($cache !== false) {
            $cache = json_decode($cache, true);
            $giltTime = strtotime('-11 minute');
            if($cache['time']<$giltTime){
                Mmc('Makefriends_MonthTop',null);
            }else{
                $cacheArr = $cache;
            }
        }
        $key = 'page'.$page;
        if(isset($cacheArr[$key])){
            return $cacheArr[$key];
        }else{
            $offset = ($page - 1) * $limit;
            $map['isDel'] = 0;
            $list = $this->where($map)->field('photoId')->order('monthCount DESC, photoId DESC')->limit("$offset,$limit")->select();
            $res = $this->_topField($list);
            $cacheArr[$key] = $res;
            if(!isset($cacheArr['time'])){
                $cacheArr['time'] = time();
            }
            Mmc('Makefriends_MonthTop', json_encode($cacheArr),0,60*11);
            return $res;
        }
    }

    private function _getCachePhotoForShow($map,$limit,$page,$order,$realname=''){
        $offset = ($page - 1) * $limit;
        
        if($realname) $map['tu.realname'] = $realname;
        
        $list = $this->where($map)
        			 	->field('ts_makefriends_photo.photoId')
        				->join('ts_user as tu ON ts_makefriends_photo.uid = tu.uid')
        				->order('ts_makefriends_photo.'.$order)
        				->limit("$offset,$limit")
        				->select();
        return $list;
    }
    //照片列表
     public function photoList($uid,$map, $limit = 10, $page = 1, $order = 'photoId DESC', $withNickname = false, $newComment = false,$realname='') {
        if(in_array($order,array('weekCount DESC','monthCount DESC','praiseCount DESC'))) {
        	$map['audit_result'] = 1;
        	$list = $this->_getCachePhotoForShow($map,$limit,$page,$order,$realname);
        } else{
            $offset = ($page - 1) * $limit;
            $list = $this->where($map)->field('photoId')->order($order)->limit("$offset,$limit")->select();
        }
        if (!$list) {
            return array();
        }
        $res = array();
        foreach ($list as $v) {
            $photoId = $v['photoId'];
            $row = array();
            $photo = $this->getPhotoInfo($photoId);
            $row['photoId'] = $photo['photoId'];
            $row['weekCount'] = $photo['weekCount'];
            $row['monthCount'] = $photo['monthCount'];
            $row['photoCount'] = $this->photoCount($photo['uid']);
            $row['uid'] = $photo['uid'];
            if($order=='weekCount DESC, photoId DESC'){
                $row['praiseCount'] = $photo['weekCount'];
            }else{
                $row['praiseCount'] = $photo['praiseCount'];
            }
            $row['backCount'] = $photo['backCount'];
            $row['audit_result'] = $photo['audit_result'];
            $row['act_id'] = $photo['act_id'];
            $row['newComment'] = 0;
            if($newComment){
                $row['newComment'] = $photo['newComment'];
            }
            $row['cTime'] = $photo['cTime'];
            $row['ctime'] = date('Y-m-d',$photo['cTime']);
            $row['w'] = $photo['w'];
            $row['h'] = $photo['h'];
            $row['pic_big'] = $this->getPhotoPic($photoId,'big', $photo);
            $row['pic_middle'] = $this->getPhotoPic($photoId,'middle', $photo);
            if ($withNickname) {
                $row['nickname'] = $this->getPhotoNickname($photoId, $photo);
            }
            $row['giftNum'] = D('MakefriendsGift', 'makefriends')->userGetSum($photo['uid']);
            $row['headPhoto'] = D('MakefriendsUser', 'makefriends')->getHeadPhoto($photo['uid'],'big');
            $row['area'] =  D('MakefriendsUser', 'makefriends')->getArea($photo['uid']);
            //获取赞记录
            $row['jilu'] = D('MakefriendsPraise','makefriends')->record($photoId);
            $row['photoId'] = $photo['photoId'];
            if($row['uid'] != $uid){
                $hasAttention = D('MakefriendsAttention', 'makefriends')->hasAttention($uid,$row['uid']);
                $row['hasAttention'] = $hasAttention?1:0;
            }
            $hasZan = D('MakefriendsPraise','makefriends')->hasZan($uid,$row['photoId']);
            $row['hasZan'] = $hasZan?1:0;
            $res[] = $row;
        }
        return $res;
    }
    
    //照片列表
    public function lookPhotoList($uid,$map,$photo_id=0,$order = 'photoId DESC') {
    	//@todo cache
    	$list = $this->where($map)->field('photoId')->order($order)->findAll();
    	if (!$list) {
    		return array();
    	}
    	$res = array();
    	foreach ($list as $v) {
    		if($photo_id==$v['photoId']){
    			$row['mark'] = 1;
    		}else{
    			$row['mark'] = 0;
    		}
    		$photoId = $v['photoId'];
    		$photo = $this->getPhotoInfo($photoId);
    		$row['photoId'] = $photo['photoId'];
    		$row['uid'] = $photo['uid'];
    		$row['praiseCount'] = $photo['praiseCount'];
    		$row['backCount'] = $photo['backCount'];
    		$row['newComment'] = $photo['newComment'];
    		$row['cTime'] = $photo['cTime'];
    		$row['ctime'] = date('Y-m-d',$photo['cTime']);
    		$row['w'] = $photo['w'];
    		$row['h'] = $photo['h'];
    		$row['pic_big'] = $this->getPhotoPic($photoId,'big', $photo);
    		$row['nickname'] = $this->getPhotoNickname($photoId, $photo);
    		$row['giftNum'] = D('MakefriendsGift', 'makefriends')->userGetSum($photo['uid']);
    		$row['headPhoto'] = D('MakefriendsUser', 'makefriends')->getHeadPhoto($photo['uid'],'big');
    		$row['area'] =  D('MakefriendsUser', 'makefriends')->getArea($photo['uid']);
    		//获取赞记录
    		$row['jilu'] = D('MakefriendsPraise','makefriends')->record($photoId);
    		$row['photoId'] = $photo['photoId'];
    		if($row['uid'] != $uid){
    			$hasAttention = D('MakefriendsAttention', 'makefriends')->hasAttention($uid,$row['uid']);
    			$row['hasAttention'] = $hasAttention?1:0;
    		}
    		$hasZan = D('MakefriendsPraise','makefriends')->hasZan($uid,$row['photoId']);
    		$row['hasZan'] = $hasZan?1:0;
    		$res[] = $row;
    	}
    	return $res;
    }

    public function photoDetail($photoId, $uid) {
        if ($photoId <= 0) {
            return array();
        }
        $photo = $this->getPhotoInfo($photoId);
        if (!$photo) {
            return array();
        }
        //获取赞记录
        $row['jilu'] = D('MakefriendsPraise','makefriends')->record($photoId);
        $row['photoId'] = $photo['photoId'];
        $row['praiseCount'] = $photo['praiseCount'];
        $row['backCount'] = $photo['backCount'];
        $row['cTime'] = $photo['cTime'];
        $row['content'] = $photo['content'];
        $row['uid'] = $photo['uid'];
        $row['w'] = $photo['w'];
        $row['h'] = $photo['h'];
        $row['pic_original'] = $this->getPhotoPic($photoId,'original', $photo);
        $row['pic_big'] = $this->getPhotoPic($photoId,'big', $photo);
        $row['nickname'] = $this->getPhotoNickname($photoId, $photo);
        $hasAttention = D('MakefriendsAttention', 'makefriends')->hasAttention($uid,$photo['uid']);
        $row['hasAttention'] = $hasAttention?1:0;
        if ($photo['uid'] == $uid && $photo['newComment'] = 1) {
            $this->upPhotoInfo($photoId, 'newComment', 0);
            D('MakefriendsUser', 'makefriends')->upCache($uid, 'newComment', 0, 'unset');
        }
         $row['giftNum'] = D('MakefriendsGift', 'makefriends')->userGetSum($photo['uid']);
         $row['headPhoto'] = D('MakefriendsUser', 'makefriends')->getHeadPhoto($photo['uid'],'big');
         $row['area'] =  D('MakefriendsUser', 'makefriends')->getArea($photo['uid']);
         $hasZan = D('MakefriendsPraise','makefriends')->hasZan($uid,$photoId);
        $row['hasZan'] = $hasZan?1:0;
        return $row;
    }

    //照片总数
    public function photoCount($uid) {
        return D('MakefriendsUser', 'makefriends')->getPhotoCount($uid);
    }

    public function delPhoto($photoId,$uid){
        if ($photoId <= 0) {
            $this->error = '照片ID错误';
            return FALSE;
        }
        $photo = $this->getPhotoInfo($photoId);
        if (!$photo) {
            $this->error = '照片不存在';
            return FALSE;
        }
        if ($photo['uid']!=$uid) {
            $this->error = '照片不存在';
            return FALSE;
        }
        $res = $this->setField('isDel', 1, 'photoId='.$photoId);
        if(!$res){
            $this->error = '删除失败';
            return FALSE;
        }
        Mmc('Makefriends_photo_' . $photoId, null);
        //更新用户 贡献、人气、喜欢数、评论数
        $daoUser = D('MakefriendsUser', 'makefriends');
        $gx = $daoUser->getGx('photo');
        $data['contribution'] = array('exp','contribution-'.$gx);
//        $rq = ($photo['praiseCount']*$daoUser->getRq('praise'))+($photo['backCount']*$daoUser->getRq('comment'));
        $rq = ($photo['praiseCount']*$daoUser->getRq('praise'));
        $data['popularity'] = array('exp','popularity-'.$rq);
        $data['weekRq'] = array('exp','weekRq-'.$rq);
        $data['monthRq'] = array('exp','monthRq-'.$rq);
        $data['cRq'] = array('exp','cRq-'.$rq);
        $data['praiseAllCount'] = array('exp','praiseAllCount-'.$photo['praiseCount']);
        $data['backAllCount'] = array('exp','backAllCount-'.$photo['backCount']);
        $daoUser->where('uid='.$uid)->save($data);
        $cache = Mmc('Makefriends_user_' . $uid);
        if ($cache !== false) {
            $obj = json_decode($cache, true);
            $obj['contribution'] -= $gx;
            $obj['popularity'] -= $rq;
            $obj['weekRq'] -= $rq;
            $obj['monthRq'] -= $rq;
            $obj['cRq'] -= $rq;
            $obj['praiseAllCount'] -= $photo['praiseCount'];
            $obj['backAllCount'] -= $photo['backCount'];
            if(isset($obj['photoCount']) && $obj['photoCount']>0){
                $obj['photoCount'] -= 1; //照片总数-1
            }
            Mmc('Makefriends_user_' . $uid, json_encode($obj),0,3600*8);
        }
        //记录贡献流水
        $data = array();
        $data['uid'] = $uid;
        $data['type'] = 'delphoto';
        $data['toid'] = $photoId;
        $data['total'] = 0-$gx;
        $data['ctime'] = time();
        M('makefriends_usergx')->add($data);
        return true;
    }

    //上图
    public function addPhoto($uid, $aid, $sr_w, $sr_h, $content = '', $act_id='') {
        $data['photoId'] = $aid;
        if ($content) {
            $data['content'] = $content;
        }
        $data['cTime'] = time();
        $data['uid'] = $uid;
        $data['sid'] = getUserField($uid,'sid');
        $data['w'] = $sr_w;
        $data['h'] = $sr_h;
        if($act_id) {
        	$data['act_id'] = $act_id;
        	$data['is_audit'] = 1;
        }
        $result = $this->add($data);
        if ($result) {
            doCando($uid);
            D('MakefriendsUser', 'makefriends')->addPhoto($uid);
            D('MakefriendsAttention', 'makefriends')->newPhoto($uid);
            //临时未满10人刷新人气排行
            $cache = Mmc('Makefriends_weekPhoto');
            if($cache!==false){
                $cache = json_decode($cache, true);
                $count = count($cache['page1']);
                if($count<10){
                    Mmc('Makefriends_weekPhoto',null);
                }
            }
            return $result;
        }
        $this->error = '操作失败';
        return false;
    }

    /**
     * ta秀上传照片
     * @param unknown $uid
     * @param string $act_id 活动id 不传则为普通上传，否则为秀场照片
     * @return boolean|Ambigous <number, mixed>
     */
    public function apiAddPhoto($uid,$act_id='') {
        if(!canDo($uid,5)){
            $this->error = '操作太频繁，请不要重复提交！';
            return false;
        }
        $checkUser = D('MakefriendsUser', 'makefriends')->checkAndAddUser($uid);
        if(!$checkUser){
            $this->error = '本人账号错误';
            return false;
        }
        if (empty($_FILES['pic']['name'])) {
            $this->error = '未选择上传的图片';
            return false;
        }
        if (!isImg($_FILES['pic']['tmp_name'])) {
            $this->error = '图片格式不对';
            return false;
        }
        list($sr_w, $sr_h) = @getimagesize($_FILES['pic']['tmp_name']);
        $options = array();
        $options['allow_exts'] = 'jpeg,gif,jpg,png,bmp';
        $info = X('Xattach')->upload('makefriends', $options);
        if (!$info['status']) {
            $this->error = '图片上传失败';
            return false;
        }
        $aid = $info['info'][0]['id'];
        $content = '';
        if (isset($_REQUEST['content'])) {
            $content = t($_REQUEST['content']);
        }
        return $this->addPhoto($uid, $aid, $sr_w, $sr_h, $content, $act_id);
    }
    public function initWeekPhoto(){
        $list = $this->where('weekCount!=0')->field('photoId')->findAll();
        $this->setField('weekCount', 0);
        foreach($list as $v){
            $pid = $v['photoId'];
            Mmc('Makefriends_photo_' . $pid,null);
        }
        Mmc('Makefriends_weekPhoto',null);
    }

    /**
     *1.南京地区, 
     *2. 江南地区：无锡市, 苏州市, 镇江市, 常州市 ；
     *3.江北地区：徐州市, 连云港市,淮安市,宿迁市,盐城市, 扬州市, 泰州市, 南通市
     *4.青海地区：西宁市
     */
    public function cityPraiseList(){
    	$city = array('南京地区','江南地区','江北地区','青海地区');
    	foreach($city as $k=>$val){
    		$row = array();
    		$row['data'] = $this->_praiseList(10,1,'weekCount',$k);
    		$row['city'] = $val;
    		$list[] = $row;
    	}
    	return $list;
    }
    
    public function _praiseList($limit = 10, $page = 1, $type='praiseCount', $area='') {
    	$list = $this->_getCachePhotoByArea($limit, $page, $type, $area);
    	foreach ($list as $v) {
    		$row = array();
    		$uid = $v['uid'];
    		$user = D('MakefriendsUser', 'makefriends')->getUserInfo($uid);
    		$row['uid'] = $user['uid'];
    		$row['nickname'] = $user['nickname'];
    		$row['photoCount'] = $this->photoCount($user['uid']);
//     		$row['w'] = $user['w'];
//     		$row['h'] = $user['h'];
    		$row = array_merge($row,$this->getPhotoInfo($v['photoId'],false));
    		if(!$row['pic_big']) $row['pic_big'] = $this->getPhotoPic($v['photoId'],'big', $v);
    		if(!$row['pic_middle']) $row['pic_middle'] = $this->getPhotoPic($v['photoId'],'middle', $v);
    		$res[] = $row;
    	}
    	return $res;
    }
    
    private function _getCachePhotoByArea($limit = 10, $page = 1, $type='praiseCount',$area=''){
    	//缓存每29分钟更新
    	$cacheArr = array();
    	$mncType = 'Makefriends_photoArea_'.$type;
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
    		$map = array('isDel'=>0);
    		$cityArr[] = array(2);    //1南京地区
    		$cityArr[] = array(3, 1, 5, 7); //2江南地区
    		$cityArr[] = array(6, 12, 4, 16, 9, 10, 15, 8); //3江北地区
    		$cityArr[] = array(17); //4青海地区
    		$map['cityId'] = array('in', $cityArr[$area]);
    		$order = 'ts_makefriends_photo.'.$type.' DESC, ts_makefriends_photo.photoId DESC';
    		
//     		$sql = "SELECT mp.*,sc.cityId FROM ts_makefriends_photo mp,ts_school sc 
//     		        WHERE ".import(',',$cityArr)." mp.sid=sc.id
//     		        $offset,$limit";
//     		$list = doQuery($sql);
    		
    		$list = $this->field('ts_makefriends_photo.*,sc.cityId')
    		             ->where($map)
    		             ->join('ts_school as sc ON ts_makefriends_photo.sid = sc.id')
    		             ->order($order)
    		             ->limit("$offset,$limit")
    		             ->select();
    		if (!$list) {
    			$list = array();
    		}
    		$cacheArr[$key] = $list;
    		if(!isset($cacheArr['time'])){
    			$cacheArr['time'] = time();
    		}
     		Mmc($mncType, json_encode($cacheArr),0,60*29);
    		return $list;
    	}
    }
    
    /**
     * 获取前十名点赞照片
     * @param number $count
     * @param number $page
     */
    public function getTopByActIdAndPraise($actid,$limit=4,$page=1){
        $key = 'Makefriends_getTopByActIdAndPraise_' . $actid;
        $cache = Mmc($key);
        if ($cache !== false) {
           return json_decode($cache, true);
        }
    	$map = array('isDel'=>0,'audit_result'=>1,'act_id'=>$actid);
        $offset = ($page - 1) * $limit;
        $list = $this->where($map)->field('photoId')->order('praiseCount DESC')->limit("$offset,$limit")->select();
        if (!$list) {
            return array();
        }
        $res = array();
        foreach ($list as $v) {
            $photoId = $v['photoId'];
            $row = array();
            $photo = $this->getPhotoInfo($photoId);
            $row['photoId'] = $photo['photoId'];
            $row['act_id'] = $photo['act_id'];
            $row['w'] = $photo['w'];
            $row['h'] = $photo['h'];
            $row['pic_big'] = $this->getPhotoPic($photoId,'big', $photo);
            $row['pic_middle'] = $this->getPhotoPic($photoId,'middle', $photo);
            $res[] = $row;
        }
        Mmc($key, json_encode($res),0,3600*12);
    	return $res;
    }
    
    /**
     * 获取用户秀场照片点赞最高排名
     * @param unknown $uid
     */
    public function getRankShowByUid($uid,$act_id)
    {
    	$where = array(
    				'uid'=>$uid,
    				'act_id'=>$act_id,
    				'isDel'=>0,
    				'audit_result'=>1,
    				);
    	$userMaxPraise = $this->field('MAX(praiseCount) AS praiseCount,photoId')
    						  ->where($where)
    				 		  ->find();
    	$where = array(
    					'praiseCount'=>array('GT',$userMaxPraise['praiseCount']),
    					'act_id'=>$act_id,
    					);
    	$res = $this->where($where)->count();
    	return array('rank'=>($res?($res+1):0),'photoId'=>$userMaxPraise['photoId']?$userMaxPraise['photoId']:0);
    }
}

?>
