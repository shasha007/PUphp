<?php
class appstoreStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	 = getAppAlias('appstore');
		$documentDao     = M('appstore_app');
		$appCount     = $documentDao->count();
		$documentCount     = M('appstore_document')->count();		
		return array(
			'APP数量'            	=>	$appCount,
			'资讯数量'            	=>	$documentCount
		);
	}
}
