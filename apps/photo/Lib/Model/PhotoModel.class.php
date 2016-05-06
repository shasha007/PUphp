<?php
class PhotoModel extends Model {
	var $tableName	=	'photo';

	public function getUidFeature($uid) {
		$map ['userId'] = $uid;
		$map ['feature'] = 1;
		$result = $this->where ( $map )->order ( "sorder ASC" )->findAll ();
		return $result;
	}
	public function setCount($appid, $count) {
		$map ['id'] = $appid;
		$map2 ['commentCount'] = $count;
		return $this->where ( $map )->save ( $map2 );
	}

	public function setFeature($pid,$uid){
		$condition['id'] = $pid;
		$condition['userId'] = $uid;
		$featureCount = $this->getFeatureCount($uid);
		if($featureCount == 3){
			$old_feature = $this->getUidFeature($uid);
			foreach($old_feature as $value){
				//取消最下面的特色展示
				if($value['sorder'] == 3){
					$rm['sorder'] = 0;
					$rm['feature'] = 0;
					$this->where('id='.$value['id'])->save($rm);
					continue;
				}
				$new_order = array();
				$new_order['sorder'] = $value['sorder'] + 1;
				$this->where('id='.$value['id'])->save($new_order);
			}
			//设置最新的这个图为第一个特色展示
			$map['sorder'] = 1;
		}else{
			$map['sorder'] = $featureCount+1;
		}
		$map['feature'] = 1;
		//设置为特色.并设置排序
		return $this->where($condition)->save($map)?1:0;
	}

	public function setDownOrder($pid,$uid){
	   $featureCount = $this->getFeatureCount($uid);
	   if($featureCount == 1) return -2; //只有一个特色的情况下，不需要进行调换
	   $old_order = $this->where('id='.$pid)->getField('sorder');
	   if($featureCount ==3 && $old_order == 3) return -1;//最后一个无法向下调换
	   $feature   = $this->getUidFeature($uid);
	   $now_feature = $this->getOrderFeature($feature);

       $map_current['sorder'] = $old_order+1;
       $map_current_condition['id']     = $pid;
       //当前的排序增加一个
       $this->where($map_current_condition)->save($map_current);

       //下一个图片的排序换成当前的
       $map_next['sorder'] = $old_order;
       $map_next_condition['id'] = $this->getNextFeature($now_feature,$old_order,true);
       $this->where($map_next_condition)->save($map_next);
		return 1;
	}

	private function getNextFeature($featureOrder,$order,$add = true){
		foreach($featureOrder as $key=>$value){
			if($add){
			    if($value == $order+1){
                    return $key;
                }
			}else{
			    if($value == $order-1){
                    return $key;
                }
			}

		}
	}

	private function getOrderFeature($feature){
		$result = array();
		foreach($feature as $value){
			$result[$value['id']] = $value['sorder'];
		}
		return $result;
	}
	public function setUpOrder($pid,$uid){
	   $featureCount = $this->getFeatureCount($uid);
       if($featureCount == 1) return -2; //只有一个特色的情况下，不需要进行调换
       $old_order = $this->where('id='.$pid)->getField('sorder');
       if($featureCount ==1 && $old_order == 3) return -1;//第一个无法向上调换
       $feature   = $this->getUidFeature($uid);
       $now_feature = $this->getOrderFeature($feature);

       //当前的排序减少一个
       $map_current['sorder']           = $old_order-1;
       $map_current_condition['id']     = $pid;
       $this->where($map_current_condition)->save($map_current);

       //上一个图片的排序换成当前的
       $map_next['sorder'] = $old_order;
       $map_next_condition['id'] = $this->getNextFeature($now_feature,$old_order,false);
       $this->where($map_next_condition)->save($map_next);
        return 1;
	}
	public function getFeatureCount($uid){
		$map['userId'] = $uid;
		$map['feature'] = 1;
	    $count = $this->field('count(1) as count')->where($map)->find();
	    return $count['count'];
	}

    public function apiPhotos($map,$limit, $page,$order = '`order` DESC,id DESC'){
        $sql = $this->field('id,albumId,name,savepath')
                    ->where($map)->order($order);
        $offset = ($page - 1) * $limit;
        $data = $sql->limit("$offset,$limit")->select();
        foreach ($data as $k => $v) {
            $data[$k]['thumb'] = tsMakeThumbUp($v['savepath'], 300, 300, 'f');
            $data[$k]['orig'] = tsMakeThumbUp($v['savepath'], 160, 160, 'no');
            unset($data[$k]['savepath']);
        }
        if(empty($data)){
            return array();
        }
        return $data;
    }

    public function apiAddPhoto($mid,$albumId){
        if ($albumId<=0) {
            $this->error = '专辑地址错误';
            return false;
        }
        if (empty($_FILES['pic']['name'])) {
            $this->error = '请上传图片';
            return false;
        }
        //得到上传的图片
        if(!isImg($_FILES['pic']['tmp_name'])){
            $this->error = '图片文件格式不对';
            return false;
        }
        $albumDao = D('Album', 'photo');
        $album = $albumDao->where('id='.$albumId.' and userId='.$mid)->field('id,privacy')->find();
        if (!$album) {
            $this->error = '专辑不存在或已被删除！';
            return false;
        }
        $images = tsUploadImg($mid,'photo',true);
        if (!$images['status']) {
            $this->error = $images['info'];
            return false;
        }
        //保存图片信息
        $v = $images['info'][0];
        $photo['attachId']	=	$v['id'];
        $photo['albumId']	=	$albumId;
        $photo['userId']	=	$mid;
        $photo['cTime']		=	time();
        $photo['mTime']		=	time();
        $photo['name']		=	substr($v['name'],'0',strpos($v['name'],'.'));	//去掉后缀名
        $photo['size']		=	$v['size'];
        $photo['savepath']	=	$v['savepath'].$v['savename'];
        $photo['privacy']	=	$album['privacy'];
        $photo['order']		=	10000;
        $photoId            =   $this->add($photo);
        if($photoId){
            //重置相册图片数
            $albumDao->updateAlbumPhotoCount($albumId);
            return true;
        }
        $this->error = '上传失败，请稍后再试！';
        return false;
    }

    public function apiUpdatePhoto($mid){
        $id = intval($_REQUEST['id']);
        if ($id<=0) {
            $this->error = '图片地址错误';
            return false;
        }
        $map['albumId'] = intval($_REQUEST['albumId']);
        $map['name'] = t($_REQUEST['name']);
        $albumDao = D('Album', 'photo');
        //图片原信息
        $oldInfo = $this->where("id={$id} AND userId={$mid}")->field('albumId')->find();
        if (!$oldInfo) {
            $this->error = '图片不存在或已删除';
            return false;
        }
        //新相册浏览权限
        $newAlbum = false;
        if ($map['albumId']>=0 && $map['albumId'] != $oldInfo['albumId']) {
            $newAlbum = $albumDao->where("id={$map['albumId']} AND userId={$mid}")->field('privacy')->find();
            if (!$newAlbum) {
                $this->error = '新相册不存在或已删除';
                return false;
            }
            $map['privacy'] = $newAlbum['privacy'];
            //旧相册封面
            $oldAlbum = $albumDao->where("id={$oldInfo['albumId']}")->field('coverImageId')->find();
            if($oldAlbum['coverImageId'] && $id == $oldAlbum['coverImageId']){
                $albumDao->setField(array('coverImageId','coverImagePath'), array(0,''), "id={$oldInfo['albumId']}");
            }
        }
        //更新信息
        $result = $this->where("id={$id} AND userId={$mid}")->save($map);
        if($result){
            //移动图片则重置相册图片数
            if ($newAlbum) {
                $albumDao->updateAlbumPhotoCount($map['albumId']);
                $albumDao->updateAlbumPhotoCount($oldInfo['albumId']);
            }
            return true;
        }
        $this->error = '操作失败，请稍后再试！';
        return false;
    }

    public function apiSetCover($mid){
        $id = intval($_REQUEST['id']);
        if ($id<=0) {
            $this->error = '图片地址错误';
            return false;
        }
        $photo = $this->where("id={$id} AND userId={$mid}")->field('albumId,savepath')->find();
        if (!$photo) {
            $this->error = '图片不存在或已删除';
            return false;
        }

        $map['coverImageId'] = $id;
        $map['coverImagePath'] = $photo['savepath'];
        $albumId = $photo['albumId'];
        $result = D('Album', 'photo')->where("id=$albumId")->save($map);
        if ($result) {
            return true;
        }
        $this->error = '操作失败，请稍后再试！';
        return false;
    }

    public function apiDelPhoto($mid){
        $id = intval($_REQUEST['id']);
        if ($id<=0) {
            $this->error = '图片地址错误';
            return false;
        }
        $photo = $this->where("id={$id} AND userId={$mid}")->field('albumId,attachId ')->find();
        if (!$photo) {
            $this->error = '图片不存在或已删除';
            return false;
        }
        $result = $this->where("id={$id}")->delete();
        if (!$result) {
            $this->error = '操作失败，请稍后再试！';
            return false;
        }
        $albumDao = D('Album', 'photo');
        $albumId = $photo['albumId'];
        $albumDao->updateAlbumPhotoCount($albumId);
        model('Attach')->deleteAttach($photo['attachId'], true);
        X('Credit')->setUserCredit($mid, 'delete_photo');
        //旧相册封面
        $oldAlbum = $albumDao->where("id={$albumId}")->field('coverImageId')->find();
        if ($oldAlbum['coverImageId'] && $id == $oldAlbum['coverImageId']) {
            $albumDao->setField(array('coverImageId', 'coverImagePath'), array(0, ''), "id={$albumId}");
        }
        return true;
    }
}
?>