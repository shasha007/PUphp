<?php

/**
 * 
 * @author Administrator
 * 放款
 */
import('bank.Model.CMBModel');
class GetAgentModel extends CMBModel
{
	/**
	 * 查询交易概要信息
	 */
	public function GetAgentInfo($data = array())
	{
		$this->setHead(__FUNCTION__);
		$this->sendData['SDKATSQYX'] = array_merge(array(
											'BGNDAT'=>date('Ymd'),
											'ENDDAT'=>date('Ymd'),
											),$data);
		$this->doBusiness();
		//不是0，则为操作失败或不存在
		if($this->revInfo['code'] != '0') return array('code'=>0,'msg'=>'查询失败','result'=>$this->revInfo);
		
		$data = $this->revData['NTQATSQYZ'];

		$result = array();
		$flag = true;
		foreach($data as $k => $v) {
			if(is_array($v)) {
				//返回数据中多条数据
				if($data[$k]) {
					if($v['REQSTA'] != 'FIN' || $v['RTNFLG'] != 'S')
					{
						/*
						 先查询GetAgentInfo获取业务的REQSTA，当REQSTA为FIN时表示业务已经完成
						 再看RTNFLG字段，如果RTNFLG为F表示整批业务都已经失败，
						 如果RTNFLG为S表示业务成功，但是可能是部分成功。
						 如果是部分成功再调用GetAgentDetail获取明细的成功状态
						 */
						$res = D('GetAgentDetail','bank')->GetAgentDetail($v['REQNBR']);
						if(!$res['code']) $failRows[] = $v['YURREF'];
					}
				}
			} else {
				//返回数据中只有一条
				if(($k == 'REQSTA' && $v != 'FIN') || ($k == 'RTNFLG' && $v != 'S')) {
					$flag = false;
					return array('code'=>0,'msg'=>'业务失败','failRows'=>array($data['YURREF']));
					break;
				} else {
					$res = D('GetAgentDetail','bank')->GetAgentDetail($v['REQNBR']);
					if($res['code']) $is_one = 1;
					
				}
			}
		}
		
		if($flag == true && $is_one == 1) return array('code'=>1,'msg'=>'业务成功');
		
		return array('code'=>1,'msg'=>'业务全部或部分执行成功','failRows'=>$failRows);
	}
}

?>
