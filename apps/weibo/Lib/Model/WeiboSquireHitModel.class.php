<?php

/**
 * WeiboThemesHitModel
 * 话题广场点击量
 */
class WeiboSquireHitModel extends Model {

	//话题广场点击量增加
	public function hitAdd($uid){
		$time = strtotime(date('Y-m-d')) ;
		if ($id = M('weibo_squire')->where('time='.$time)->getField('id')) {
			M('weibo_squire')->where('id='.$id)->setInc('hit') ;
		}else{
			$data['time'] = $time ;
			$data['hit'] = 1 ;
			M('weibo_squire')->add($data) ;
		}
		$map['time'] = array('GT',$time) ;
		$map['uid'] = $uid ;
		if ($hit_id = $this->where($map)->getField('id')) {
			$data['time'] = time() ;
			$this->where('id='.$hit_id)->save($data) ;
		}else{
			$data['time'] = time() ;
			$data['uid'] = $uid ;
			$this->add($data) ;
		}
	}
}