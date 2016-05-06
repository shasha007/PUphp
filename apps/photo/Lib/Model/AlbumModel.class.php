<?php
class AlbumModel extends Model{
	var $tableName	=	'photo_album';

	//为新用户创建默认数据
	public function createNewData($uid=0) {
		//创建默认相册
		if( intval($uid) <= 0 ){
			$uid	=	$this->mid;
		}
		$count	=	$this->where("userId='$uid' AND isDel=0")->count();
		if($count==0){
			$name	=	getShort(getUserName($uid),5).'的相册';	//默认的相册名
			$album['cTime']		=	time();
			$album['mTime']		=	time();
			$album['userId']	=	$uid;
			$album['name']		=	$name;
			$album['privacy']	=	1;
			$this->add($album);
		}
	}

	//更新相册图片数量
	function updateAlbumPhotoCount($aid) {
		$count	=	D('Photo')->where("albumId='$aid' AND isDel=0")->count();
		$map['photoCount']	=	$count;
		return $this->where("id='$aid'")->save($map);
	}

	//设置相册封面
	function setAlbumCover($albumId,$cover=0) {
		//插入图片封面
		$cover_info	=	D('Photo')->where("id='$cover'")->find();
		if($cover>0 && $cover_info){
			$map['coverImageId']	=	$cover_info['id'];
			$map['coverImagePath']	=	$cover_info['savepath'];
		}
		$map['mTime']	=	time();
		//更新相册信息
		$result	=	$this->where("id='$albumId'")->save($map);
		if($result){
			return true;
		}else{
			return false;
		}
	}

	//通过相册ID 获取图片ID集
/*	function getPhotoIds($uid,$albumId,$type) {
		$photos	=	$this->getPhotos($uid,$albumId,$type);
		if($photos){
			foreach($photos as $v){
				$photoIds[]	=	$v['photoId'];
			}
			return $photoIds;
		}else{
			return false;
		}
	}*/

	//通过相册ID 获取图片集
	function getPhotos($uid,$albumId,$type,$order='id ASC',$shownum=5) {
		//某个人的全部图片
		if($type=='mAll'){
			$map['userId']	=	$uid;
		}else{
		//某个专辑的全部图片(无type下默认)
			$map['albumId']	=	$albumId;
			$map['userId']	=	$uid;
		}
		$map['isDel']	=	0;
		$result	=	 D('Photo')->order($order)->where($map)->findAll();
		return $result;
	}

	//删除相册
	function deleteAlbum($aids,$uid,$isAdmin=0) {
		//解析ID成数组
		if(!is_array($aids)){
			$aids	=	explode(',',$aids);
		}

		//非管理员只能删除自己的图片
		if(!$isAdmin){
			$map['userId']	=	$uid;
		}

		//同步删除图片及附件
		$album['albumId']	=	array('in',$aids);
		$photos		=	D('Photo')->field('id')->where($album)->findAll();
		foreach($photos as $v){
			$photoIds[]	=	$v['id'];
		}
		//处理图片及附件
		$this->deletePhoto($photoIds,$uid,$isAdmin);

		//删除相册
		$map['id']		=	array('in',$aids);
		//$save['isDel']	=	1;
		$result	=	$this->where($map)->delete();
		if($result){
			return true;
		}else{
			return false;
		}
	}

	//删除图片
	function deletePhoto($pids,$uid,$isAdmin=0) {
		//解析ID成数组
		if(!is_array($pids)){
			$pids	=	explode(',',$pids);
		}

		//非管理员只能删除自己的图片
		if(!$isAdmin){
			$map['userId']	=	$uid;
		}

		//获取图片信息
		$photoDao  = D('Photo');
		$map['id'] = array('in',$pids);
		$photos	   = $photoDao->where($map)->findAll();

		///删除图片
		//$save['isDel']	=	1;
		$result	   = $photoDao->where($map)->delete();

		if($result){
			foreach($photos as $v){
				$attachIds[]	=	$v['attachId'];
				//重置相册图片数
				$this->updateAlbumPhotoCount($v['albumId']);
			}
			//处理附件
			model('Attach')->deleteAttach($attachIds, true);
			return true;
		}else{
			return false;
		}
	}

    public function apiAlbumList($map=array(), $limit=10, $page=1, $order = 'id DESC'){
        $sql = $this->field('id,userId,name,mTime,coverImagePath,photoCount,privacy')
                    ->where($map)->order($order);
        $offset = ($page - 1) * $limit;
        $data = $sql->limit("$offset,$limit")->select();
        foreach ($data as $k => $v) {
            $data[$k]['mTime'] = friendlyDate($v['mTime']);
            $data[$k]['cover'] = $this->getAlbumCover($v['id'], $v);
            unset($data[$k]['coverImagePath']);
        }
        if(empty($data)){
            return array();
        }
        return $data;
    }

    public function getAlbumCover($albumId, $album = '', $width = 300, $height = 300) {
        //获取相册详细信息
        if (empty($album) || $albumId != $album['id']) {
            $album = $this->find($albumId);
        }

        //根据隐私情况，判断相册封面
        if ($album['privacy'] == 4) {
            //密码可见
            $cover = SITE_URL . "/apps/photo/Tpl/default/Public/images/photo_mima.gif";
        } elseif ($album['privacy'] == 3) {
            //主人可见
            $cover = SITE_URL . "/apps/photo/Tpl/default/Public/images/photo_zrkj.gif";
        } elseif ($album['privacy'] == 2) {
            //显示相册只有他关注的人可见
            $cover = SITE_URL . "/apps/photo/Tpl/default/Public/images/photo_hykj.gif";
        } else {
            //图片封面
            if (intval($album['photoCount']) > 0 && !empty($album['coverImagePath'])) {
                $cover = tsMakeThumbUp($album['coverImagePath'], $width, $height, 'c');
            } elseif (intval($album['photoCount']) == 0) {
                $cover = SITE_URL . "/apps/photo/Tpl/default/Public/images/photo_zwzp.gif";
            } else {//无设置封面 且有照片 则默认最新一张为封面
                $firstImg = M('photo')->field('savepath')->where("albumId={$album['id']}")->order('`order` DESC,id DESC')->find();
                $cover = tsMakeThumbUp($firstImg['savepath'], $width, $height, 'c');
            }
        }
        return $cover;
    }

    public function apiAlbum($id,$mid,$pass, $limit=10, $page=1,$order = '`order` DESC,id DESC'){
        if ($id<=0) {
            $this->error = '专辑地址错误';
            return false;
        }
        $album = $this->where("id={$id}")->field('id,name,mTime,photoCount,userId,privacy,privacy_data,coverImageId')->find();
        if (!$album) {
            $this->error = '专辑不存在或已被删除！';
            return false;
        }
        //隐私控制
        if ($mid != $album['userId']) {
            if ($album['privacy'] == 3) {
                $this->error = '这个专辑只有主人自己可见！';
                return false;
            } elseif ($album['privacy'] == 2){
                $relationship = getFollowState($mid, $album['userId']);
                if($relationship == 'unfollow'){
                    $this->error = '这个专辑只有关注的人可见！';
                    return false;
                }
            } elseif ($album['privacy'] == 4) {
                if($album['privacy_data'] != $pass){
                    $this->error = '这个专辑需密码访问，密码错误！';
                    return false;
                }
            }
        }
        //获取图片数据
        $map['albumId'] = $id;
        $map['userId'] = $album['userId'];
        $map['isDel'] = 0;
        $album['photos'] = D('Photo','photo')->apiPhotos($map,$limit, $page,$order);
        $album['mTime'] = friendlyDate($album['mTime']);
        unset($album['privacy_data']);
        return $album;
    }

    public function apiAddAlbum($mid, $input) {
        $name = h(t(mStr(trim($input['name']), 12, 'utf-8', false)));
        if (strlen($name) == 0) {
            $this->error = '相册名称不能为空！';
            return false;
        }
        //检测相册是否已存在
        $albumId = $this->getField('id', "userId={$mid} AND name='{$name}'");
        if ($albumId) {
            $this->error = '相册名称重复！';
            return false;
        }

        $data['cTime'] = time();
        $data['mTime'] = time();
        $data['userId'] = $mid;
        $data['name'] = $name;
        $data['privacy'] = intval($input['privacy']);
        if (!in_array($data['privacy'], array(1,2,3,4))){
            $this->error = '访问权限参数错误！';
            return false;
        }
        //设置相册密码
        if ($data['privacy'] == 4){
            $data['privacy_data'] = t($input['privacy_data']);
        }

        $result = $this->add($data);

        if ($result) {
            X('Credit')->setUserCredit($mid, 'add_album');
            $data['id'] = $result;
            $data['photoCount'] = 0;
            $data['cover'] = $this->getAlbumCover($result, $data);
            unset($data['cTime']);
            unset($data['privacy_data']);
            return $data;
        }
        $this->error = '创建失败，请稍后再试！';
        return false;
    }

    public function apiDelAlbum($id,$mid){
        if ($id<=0) {
            $this->error = '专辑地址错误';
            return false;
        }
        $result = $this->deleteAlbum($id, $mid);
        if ($result) {
            //删除成功
            X('Credit')->setUserCredit($mid, 'delete_album');
            return true;
        }
        $this->error = '删除失败，请稍后再试！';
        return false;
    }

    public function apiEditAlbum($id,$mid){
        if ($id<=0) {
            $this->error = '专辑地址错误';
            return false;
        }
        $data['privacy'] = intval($_REQUEST['privacy']);
        if (!in_array($data['privacy'], array(1,2,3,4))){
            $this->error = '访问权限参数错误！';
            return false;
        }
        $data['privacy_data'] = '';
        if ($data['privacy'] == 4){
            $data['privacy_data'] = t($_REQUEST['privacy_data']);
        }
        $name = h(t(mStr(trim($_REQUEST['name']), 12, 'utf-8', false)));
        if (strlen($name) == 0) {
            $this->error = '相册名称不能为空！';
            return false;
        }
        //检测相册是否已存在
        $albumId = $this->getField('id', "userId={$mid} AND name='{$name}'");
        if ($albumId && $albumId!=$id) {
            $this->error = '相册名称重复！';
            return false;
        }
        $data['mTime'] = time();
        $data['name'] = $name;
        $album = $this->where("userId={$mid} AND id={$id}")->field('id,privacy')->find();
        if (!$album) {
            $this->error = '你没有权限编辑该相册！';
            return false;
        }
        $result	= $this->where("id='$id'")->save($data);
        if ($result) {
            //如果相册隐私发生变化
            if ($album['privacy'] != $data['privacy']) {
                D('Photo', 'photo')->setField('privacy',$data['privacy'],"albumId=$id");
            }
            return true;
        }
        $this->error = '操作失败，请稍后再试！';
        return false;
    }

}
?>