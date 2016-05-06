<?php
class DocumentModel extends Model {
	var $tableName	=	'newcomer_document';

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

		///删除文档
		//$save['isDel']	=	1;
		$result	   = $documentDao->where($map)->delete();

		if($result){
			return true;
		}else{
			return false;
		}	
	}
	
    public function getDocList($map = null,$field=null,$order = null,$limit = null) {
    	//处理where条件
	    //$map = $this->merge( $map );
        //连贯查询.获得数据集
        $limit = isset( $limit )?$limit:10;
        $result         = $this->where( $map )->field( $field )->order( $order )->findPage( $limit) ;
        
        //对数据集进行处理
        $data           = $result['data'];
        
     	return $data;
    }
}
?>