<?php

/**
 * 
 * @author Administrator
 * 查询支付结果
 */
import('bank.Model.CMBModel');
class GetPaymentInfoModel extends CMBModel
{
	/**
	 * 查询支付结果
	 */
	public function GetPaymentInfo($order_id='',$sdate = '',$edate = '')
	{
		$this->setHead(__FUNCTION__);
		$date = date('Ymd');
		$this->sendData['SDKPAYQYX'] = array(
											'YURREF'=>$order_id,
											'BGNDAT'=>$sdate?$sdate:$date,
											'ENDDAT'=>$edate?$edate:$date,
											);
		$this->doBusiness();

		//不是0，则为操作失败或不存在
		if($this->revInfo['code'] != '0') return array('code'=>0,'msg'=>'查询失败','result'=>$this->revInfo);

		$data = $this->revData['NTQPAYQYZ'];

		if($data['REQSTS'] == 'FIN') {
			if($data['RTNFLG'] == 'F') {
				return array('code'=>0,'msg'=>'业务失败');
			} else if($data['RTNFLG'] == 'S') {
				return array('code'=>1,'msg'=>'业务成功');
			}
		}

		return array('code'=>0,'msg'=>'业务失败');
	}
}

?>
