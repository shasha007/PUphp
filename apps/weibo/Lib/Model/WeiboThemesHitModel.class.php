<?php

/**
 * WeiboThemesHitModel
 * 话题点击量
 */
class WeiboThemesHitModel extends Model {

	//话题点击量增加
	public function hitAdd($uid,$themes_id){
		M('weibo_themes')->where('id='.$themes_id)->setInc('hit') ;
		$data['time'] = time() ;
		$time = strtotime(date('Y-m-d')) ;
		$map['uid'] = $uid ;
		$map['themes_id'] = $themes_id ;
		$map['time'] = array('GT',$time) ;
		if ($id = $this->where($map)->getField('id')) {
			$this->where('id='.$id)->save($data) ;
		}else{
			$data['uid'] = $uid ;
			$data['themes_id'] = $themes_id ;
			$this->add($data) ;			
		}
	}
}