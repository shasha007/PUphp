<?php

include_once SITE_PATH . '/apps/event/Lib/Model/BaseModel.class.php';

class EventCollectionModel extends BaseModel {

    //add添加或cancel取消收藏
    public function fav($uid,$eid,$type){
        $data['uid'] = $uid;
        $data['eid'] = $eid;
        if($type=='add'){
            $data['time'] = time();
            return $this->add($data);
        }
        if($type=='cancel'){
            return $this->where($data)->delete();
        }
        return false;
    }
    //清空我的收藏活动api
    public function cleanMyCollectEvent($uid){
    	$map['uid']=$uid;
    	$res = M("event_collection")->where($map)->delete();
    	return $this->getError($res);
    }
    
    public function getError($res=''){
    	if($res)
    	{
    		$list['status']=1;
    		$list['msg']=$res;
    	}
    	else
    	{
    		$list['status']=0;
    		$list['msg']='失败';
    	}
    	return $list;
    }
}