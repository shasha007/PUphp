<?php

/**
 * WeiboThemesHitModel
 * 话题点击量
 */
class WeiboThemesModel extends Model {

	//话题显示位置 数量搜索
	public function select_themes($place,$num){
		$map['isShow'] = 1 ;
		$map['isDel'] = 0 ;
		$ma['place'] = $place ;
		$place = M('weibo_themes_dispaly')->join('ts_weibo_themes t on t.id = ts_weibo_themes_dispaly.themes_id')->field('t.*')->where($ma)->select() ;
		//第四条微博位置 随机显示一个配置的 如果没配置则不显示
		foreach ($place as $key => $value) {
			$place[$key]['pic'] = PIC_URL.'/data/uploads/'.$value['pic'] ;
		}
		if ($ma['place'] == 'the4') {
			if (empty($place)) {
				return array() ;
			}
			shuffle($place) ;
			return array_pop($place) ;
		}
		foreach ($place as $key => $value) {
			$ids[] = $value['id'] ;
		}
		if (count($place) < $num) {
			if ($ids) {
				$map['id'] = array('NOT IN ',$ids) ;
			}
			$list = M('weibo_themes')->where($map)->limit($num-count($place))->order('id')->select() ;
			$place = $place?$place:array() ;
			foreach ($list as $key => $value) {
				array_push($place, $value) ;
			}
		}
		return $place ;
	}


	public function doaddplace($data){
		$result['msg'] = '配置失败' ;
		$result['code'] = 0 ;
		switch ($data['place']) {
			case 'discover':
				$num = 3 ;
				break;
		}
		$map['place'] = $data['place'] ;
		$count = M('weibo_themes_dispaly')->where($map)->count() ;
		if ($count < $num || !$num) {
			if (M('weibo_themes_dispaly')->where($data)->find()) {
				$result['msg'] = '该话题已经配置在该位置' ;
			}else{
				if (M('weibo_themes_dispaly')->add($data)) {
					$result['code'] = 1 ;
					$result['msg'] = '配置成功' ;
				}
			}
		}else{
			$result['msg'] = '该位置已经配置满' ;
		}
		return $result ;        
	}
	// 根据weibo_id  weibo_num-1
	public function decrease ($weibo_id){
		$themes_id = M('weibo')->where('weibo_id='.$weibo_id)->getField('themes_id') ;
		if (intval($themes_id) > 0) {
			$this->where('id='.$themes_id)->setDec('weibo_num') ;
		}
	}
}