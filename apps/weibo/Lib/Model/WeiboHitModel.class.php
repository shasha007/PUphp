<?php

/**
 * WeiboThemesHitModel
 * 微博点击量
 */
class WeiboHitModel extends Model {

	//话题点击量增加
	public function hitAdd($uid,$weibo_id){
		$data['time'] = time() ;
		M('weibo')->where('weibo_id='.$weibo_id)->setInc('hit') ;
		$time = strtotime(date('Y-m-d')) ;
		$map['uid'] = $uid ;
		$map['weibo_id'] = $weibo_id ;
		$map['time'] = array('GT',$time) ;
		if ($id = $this->where($map)->getField('id')) {
			$this->where('id='.$id)->save($data) ;
		}else{
			$data['uid'] = $uid ;
			$data['weibo_id'] = $weibo_id ;
			$this->add($data) ;			
		}
	}
}