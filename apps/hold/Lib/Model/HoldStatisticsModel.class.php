<?php
class HoldStatisticsModel extends Model {

	public function statistics() {
		$app_alias	 = getAppAlias('hold');
		$documentDao     = M('hold_user');
		$documentCount     = $documentDao->count();
		return array(
			'选手数量'            	=>	$documentCount,
		);
	}
}
