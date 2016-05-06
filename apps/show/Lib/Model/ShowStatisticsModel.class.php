<?php
class ShowStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	 = getAppAlias('show');
		$documentDao     = M('show_user');
		$documentCount     = $documentDao->count();
		return array(
			'选手数量'            	=>	$documentCount,
		);
	}
}
