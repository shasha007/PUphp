<?php

/**
 * EventTypeModel
 *
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class EventFavModel extends BaseModel {

    public function addFav($uid,$fav){
        if($fav['cid'] || $fav['sid']){
            $data['fav'] = serialize($fav);
            $res = $this->where('uid='.$uid)->save($data);
            if(!$res){
                $data['uid'] = $uid;
                return $this->add($data);
            }
        }else{
            $res = $this->where('uid='.$uid)->delete();
        }
        return $res;
    }

    public function getFav($uid){
        $fav = $this->where('uid='.$uid)->getField('fav');
        if($fav){
            $fav = unserialize($fav);
        }
        return $fav;
    }
}
