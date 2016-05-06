<?php
class DocumentModel extends Model {
	var $tableName	=	'wenku';

		//回收站文档
	public function deleteDocument_to_recycle($pids,$uid,$isAdmin=0) {
	}
		//删除文档
	public function deleteDocument($pids,$uid,$isAdmin=0) {
		
		//解析ID成数组
		if(!is_array($pids)){
			$pids	=	explode(',',$pids);
		}

		//非管理员只能删除自己的文档
		if(!$isAdmin){
			$map['userId']	=	$uid;
		}
		//获取文档信息
		$documentDao  = D('Document');
		$map['id'] = array('in',$pids);
		$documents	   = $documentDao->where($map)->findAll();

		///删除文档
		//$save['isDel']	=	1;
		$result	   = $documentDao->where($map)->delete();

		if($result){
			foreach($documents as $v){
				$attachIds[]	=	$v['attachId'];
			}
			//处理附件			
			model('Attach')->deleteAttach($attachIds, true);
			return true;
		}else{
			return false;
		}	
	}
     

}
?>