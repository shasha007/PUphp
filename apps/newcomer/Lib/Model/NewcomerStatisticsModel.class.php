<?php
class newcomerStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	 = getAppAlias('newcomer');
		$documentDao     = M('newcomer_document');
		$documentCount     = $documentDao->count();
		return array(
			'资讯数量'            	=>	$documentCount
		);
	}
}
