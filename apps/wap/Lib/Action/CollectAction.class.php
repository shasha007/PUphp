<?php
/*
 * 我的收藏话题h5
 * 我的话题h5
 */
class CollectAction extends BaseAction {
	/*
	 * 我的收藏话题h5
	 */
	public function myCollectWeibo(){
		$list=D('Favorite','weibo')->getMyCollectWeibo($this->mid,'','',$this->_item_count,$this->_page);
		foreach ($list as $key => $value) {
			$list[$key]['isHeart'] = D('Heart','weibo')->isHearted($value['weibo_id'],$this->mid)?1:0;
			$list[$key]['content'] = buildAndroidCode(replaceSelfEmoji($list[$key]['content'])) ;
		}
		//dump($list) ; die ;
		$this->assign('list',$list);
		$this->display('collect_topic');
		
	}
	
	/*
	 * ajax删除我收藏的话题
	 */
	public function ajaxDelMyWeibo(){
		$weibo_id=intval($_REQUEST['weibo_id']);
		$map['weibo_id']=$weibo_id;
		$map['uid']=$this->mid;
		$res = M("weibo_favorite")->where($map)->delete();
		empty($res)?$data['status'] = 0:$data['status'] = 1;
		echo json_encode($data);
	}
	
	/*
	 * ajax获取我收藏的话题
	 */
	public function ajaxGetMyColWeibo(){
		$list=D('Favorite','weibo')->getMyCollectWeibo($this->mid,'','',$this->_item_count,$this->_page);
		foreach ($list as $key => $value) {
			$list[$key]['isHeart'] = D('Heart','weibo')->isHearted($value['weibo_id'],$this->mid)?1:0;
			$list[$key]['content'] = buildAndroidCode(replaceSelfEmoji($list[$key]['content'])) ;
		}		
		empty($list)? $data['status'] = 0 : $data['status'] = 1;
		$data['data'] = $list;
		echo json_encode($data);
		
	}
	/*
	 * 我的话题h5
	 */
	public function myWeibo(){
		$list=D('Weibo','weibo')->getMyWeibo($this->mid,$this->_item_count,$this->_page);
		foreach ($list as $key => $value) {
			$list[$key]['isHeart'] = D('Heart','weibo')->isHearted($value['weibo_id'],$this->mid)?1:0;
			$list[$key]['content'] = buildAndroidCode(replaceSelfEmoji($list[$key]['content'])) ;
		}
		$this->assign('list',$list);
		$this->display('mytopic');
	}
	
	/*
	 * ajax删除我的话题
	*/
	public function ajaxDelWeibo(){
		$weibo_id=intval($_REQUEST['weibo_id']);
		$map['weibo_id']=$weibo_id;
		$map['uid']=$this->mid;
		$res = M("weibo")->where($map)->delete();
		empty($res)?$data['status'] = 0:$data['status'] = 1;
		echo json_encode($data);
	}
	
	/*
	 * ajax获取我话题
	*/
	public function ajaxGetWeibo(){
		$list=D('Weibo','weibo')->getMyWeibo($this->mid,$this->_item_count,$this->_page);
		foreach ($list as $key => $value) {
			$list[$key]['isHeart'] = D('Heart','weibo')->isHearted($value['weibo_id'],$this->mid)?1:0;
			$list[$key]['content'] = buildAndroidCode(replaceSelfEmoji($list[$key]['content'])) ;
		}	
		empty($list)? $data['status'] = 0 : $data['status'] = 1;
		$data['data'] = $list;
		echo json_encode($data);
	
	}
	/**清空我收藏的话题
	 *
	* @return Ambigous <multitype:, unknown>
	*/
	public function delallweibo(){
		$data=D('Favorite','weibo')->delallweibo($this->mid);
		echo json_encode($data);
	}
}