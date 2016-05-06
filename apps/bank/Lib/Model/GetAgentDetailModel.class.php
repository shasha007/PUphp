<?php

/**
 * 
 * @author Administrator
 * 放款
 */
import('bank.Model.CMBModel');
class GetAgentDetailModel extends CMBModel
{
	/**
	 * 查询交易概要信息
	 */
	public function GetAgentDetail($bank_order_id)
	{
		$this->setHead(__FUNCTION__);
		$this->sendData['SDKATDQYX'] = array(
											'REQNBR'=>$bank_order_id, // 银行单号需要保存在数据库中
											);
		$this->doBusiness();
		//不是0，则为操作失败或不存在
		if($this->revInfo['code'] != '0') return array('code'=>0,'msg'=>'查询失败','result'=>$this->revInfo);
		
		$data = $this->revData['NTQATDQYZ'];

		if($data['STSCOD'] == 'S') return array('code'=>1,'msg'=>'业务成功','result'=>$this->revData);

		if($data['STSCOD'] == 'I') return array('code'=>999,'msg'=>'业务正在处理','result'=>$this->revData);
		
		return array('code'=>0,'msg'=>'业务失败','result'=>$this->revData);
	}
	
	/**
	 * 查询交易概要信息，包含 余额不足时的实际扣款金额
	 * @param unknown $bank_order_id
	 */
	public function NTAGDINF($bank_order_id)
	{
		$this->setHead(__FUNCTION__);
		$this->sendData['NTAGDINFY1'] = array(
											'REQNBR'=>$bank_order_id, // 银行单号需要保存在数据库中
											);
		$this->doBusiness();
		//不是0，则为操作失败或不存在
		if($this->revInfo['code'] != '0') return array('code'=>0,'msg'=>'查询失败','result'=>$this->revInfo);
		
		$data = $this->revData['NTAGCDTLY1'];

		if($data['STSCOD'] == 'S') {
			if($data['LGRAMT'] < $data['TRSAMT']) return array('code'=>4,'msg'=>'余额不足','result'=>$this->revData);
			return array('code'=>2,'msg'=>'业务成功','result'=>$this->revData);
		}
		
		if(in_array($data['STSCOD'],array('I','A'))) return array('code'=>999,'msg'=>'业务正在处理','result'=>$this->revData);
		
		return array('code'=>3,'msg'=>'业务失败','result'=>$this->revData);
	}
}

?>
