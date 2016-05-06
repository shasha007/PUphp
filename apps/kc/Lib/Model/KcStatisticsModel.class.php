<?php
class KcStatisticsModel extends Model {

	public function statistics() {
		$app_alias	 = getAppAlias('kc');
		$documentDao     = M('kc_user');
		$documentCount     = $documentDao->count();
		return array(
			'创建课程表人数'            	=>	$documentCount,
		);
	}
}
