<?php
class AnnounceStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	 = getAppAlias('announce');
		$documentDao     = M('announce');
		$documentCount     = $documentDao->count();
		return array(
			'通知数量'            	=>	$documentCount,
		);
	}
}
