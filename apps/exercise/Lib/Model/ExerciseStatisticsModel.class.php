<?php
class ExerciseStatisticsModel extends Model {

	public function statistics() {
		$app_alias	 = getAppAlias('exercise');
		$documentDao     = M('exercise');
		$documentCount     = $documentDao->count();
		return array(
			'题库总数'            	=>	$documentCount,
		);
	}
}
