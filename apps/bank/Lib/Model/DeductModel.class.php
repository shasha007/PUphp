<?php

/**
 * 
 * @author Administrator
 * 放款
 */
import('bank.Model.CMBModel');
class DeductModel extends CMBModel
{
	public $logTableName = 'bank_deduct_order_log';
	public $tableName = 'bank_deduct_order';
	public $isSleep = true;
	public $testIp = 'http://218.4.158.6:4358';
	public $lgnnam = '天宫信息朱晓峰';
	
	/**
	 * 放款 http://xyhui.lo/?app=api&mod=Test&act=lend
	 * @param unknown $order_id PU订单ID，对应ts_bank_finance_detail的id
	 * @param unknown $sum 扣款金额
	 * @param unknown $studentCardNo 学生银行卡号
	 * @param unknown $studentCardAccount 学生银行卡户名
	 */
	public function AgentRequest($finance_detail_id,$uid,$sum,$studentCardNo,$studentCardAccount)
	{
		$this->setHead(__FUNCTION__);
		
		if(!$finance_detail_id) return array('code'=>0,'msg'=>'finance_detail_id not null');

		//创建业务订单
		if($uid) $res = $this->saveOrder($finance_detail_id,$uid);
		if(!$this->order_id) return array('code'=>0,'msg'=>'order_id not null');
		
		$studentCardNo = $this->decrypt($studentCardNo);
		if(!$studentCardNo) return array('code'=>0,'msg'=>'studentCardNo failed');
		
		$this->sendData['SDKATSRQX'] = array(
											'BUSCOD'=>'N03030',
											'BUSMOD'=>'00002',
											'C_TRSTYP'=>'代扣其他',
											'TRSTYP'=>'AYBK',
											'DBTACC'=>self::collectCompanyNo,//企业收款账号
											'BBKNBR'=>self::collectBankNo,//分行代码
											'BANKAREA'=>self::collectBankName,
											'SUM'=>$sum,//总金额
											'TOTAL'=>1,//总笔数
											'YURREF'=>$this->order_id,
											'MEMO'=>'代扣',
											'GRTFLG'=>'Y',
										);
		$this->sendData['SDKATDRQX'] = array(
											'ACCNBR'=>$studentCardNo,//学生卡号
											'CLTNAM'=>$studentCardAccount,
											'TRSAMT'=>$sum,
											'TRSDSP'=>'',
										);
		
		//发送xml至银行
		$post = $this->doBusiness();
		
		//开始验证返回结果
// 		if($this->revData['INFO']['RETCOD'] != '0') return array('code'=>0,'msg'=>'代扣失败','result'=>$this->revInfo);

		$revData = $this->revData;
		if(isset($this->revData['NTREQNBRY'])) {
			//REQNBR 为银行订单号或流水号,需要更新到数据库
			$this->bank_order_id = $this->revData['NTREQNBRY']['REQNBR'];

			if($this->sendData['SDKATSRQX']['GRTFLG'] == 'Y' && $this->revData['NTREQNBRY']['RSV50Z'] == 'OPR') {
				//成功
				$code = 1;
			} else if($this->sendData['SDKATSRQX']['GRTFLG'] == '' && $this->revData['NTREQNBRY']['RSV50Z'] == 'BNK') {
				//成功
				$code = 1;
			}
			if($code) {
				//单笔订单查询
				$returnData = array('code'=>999,'msg'=>'代扣成功，银行处理中','result'=>$revData);
			}
		}
		
		if(empty($returnData)) $returnData = array('code'=>0,'msg'=>'代扣失败','result'=>$revData);

		//更新数据
		$this->saveOrder('',$uid,$this->bank_order_id,$returnData['code'],$returnData['msg']);
		return $returnData;
	}
	
	/**
	 * 生成或更新扣款订单
	 */
	public function saveOrder($finance_detail_id='',$uid,$bank_order_id='',$bank_code=0,$bank_msg='')
	{
		$datetime = date('Y-m-d H:i:s');
		if($this->order_id) {
			$data = array(
						'etime'=>$datetime,
						'bank_order_id'=>$bank_order_id,
						'status'=>2,
						'bank_code'=>$bank_code,
						'bank_msg'=>$bank_msg,
						);
			return M($this->tableName)->where(array('order_id'=>$this->order_id))->save($data);
		} else {
			$data = array(
						'finance_detail_id'=>$finance_detail_id,
						'uid'=>$uid,
						'stime'=>$datetime,
						'status'=>1,
						);
			
			$this->order_id = M($this->tableName)->add($data);
		}
	}
}

?>
