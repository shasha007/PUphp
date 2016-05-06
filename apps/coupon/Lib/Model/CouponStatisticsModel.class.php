<?php
class CouponStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	 = getAppAlias('coupon');
		$documentDao     = M('coupon');
		$documentCount     = $documentDao->count();
		return array(
			'优惠劵数量'            	=>	$documentCount,
		);
	}
}
