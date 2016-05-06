<?php
class documentStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	 = getAppAlias('document');
		$documentDao     = M('wenku');
		$documentCount     = $documentDao->count();
		$storageCount   = $documentDao->sum('size');
		$storageCount   = byte_format($storageCount);
		return array(
			'文档数量'            	=>	$documentCount,
			'占用空间'				=>  "{$storageCount}"
		);
	}
}
