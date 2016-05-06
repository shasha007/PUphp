<?php
/*
* 红人相关操作
*/
class RedlistModel extends Model {

	// 添加红人标签
	public function doAddRed($name){
		$data['name'] = $name ;
		return $this->add($data) ;
	}


	// 修改红人标签
	public function editRed($id,$data){
		return $this->where('id='.$id)->save($data) ;
	}

	//删除红人标签
	public function deleteRed($ids){
		$map['id'] = array('IN',$ids) ;
		$data['isDel'] = 1 ;
		return $this->where($map)->save($data) ;
	}
	// 搜索红人标签
	public function search($map=array()){
		$map['isDel'] = 0 ;
		return $this->where($map)->select() ;
	}

	// 添加红人
	public function addPersonToRed($uid,$rid,$label){
		$map['rid'] = $rid ;
		$map['uid'] = $uid ;
		$map['label'] = $label ;
		if ($this->searchRedPerson($map)) {
			return 2 ; //已经为此人添加过标签
		}else{
			if(M('red_user')->add($map)){
				return 1 ;//添加成功
			}
			return 0 ;//添加失败
		}
	}

	//删除红人标签
	public function deletePersonRed($ids){
		$map['id'] = array('IN',$ids) ;
		$data['isDel'] = 1 ;
		return M('red_user')->where($map)->save($data) ;
	}
	// 搜索红人
	public function searchRedPerson($map=array(),    $page=1,$num=20){
		$map['isDel'] = 0 ;
		$offset = ($page-1)*$num ;
		return M('red_user')->where($map)->limit("$offset,$num")->select() ;
	}
}