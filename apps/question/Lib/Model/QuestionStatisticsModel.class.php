<?php
class QuestionStatisticsModel extends Model {
	
	public function statistics() {
		$app_alias	 = getAppAlias('question');
		$documentDao     = M('question');
		$documentCount     = $documentDao->count();
		return array(
			'问卷数量'            	=>	$documentCount,
		);
	}
}
