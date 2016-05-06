<?php

/**
 * WeiboThemesHitModel
 * 发现点击量
 */
class WeiboDiscvoerHitModel extends Model {

	//发现点击量增加
	public function hitAdd($uid){
		$time = strtotime(date('Y-m-d')) ;
		if ($id = M('weibo_discover')->where('time='.$time)->getField('id')) {
			M('weibo_discover')->where('id='.$id)->setInc('hit') ;
		}else{
			$data['time'] = $time ;
			$data['hit'] = 1 ;
			M('weibo_discover')->add($data) ;
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