<?php

header ( "Content-type: text/html; charset=utf-8" );
/**
 * 
 * @author Administrator
 * 放款
 */
import('bank.Model.CMBModel');
class TestModel extends CMBModel
{
	public $logTableName = 'bank_lend_order_log';
	public $tableName = 'bank_lend_order';
	public $isSleep = true;

	/**
	 * 即时放款 http://xyhui.lo/?app=api&mod=Test&act=lend
	 * @param unknown $order_id PU订单ID
	 * @param unknown $sum 放款金额
	 * @param unknown $studentCardNo 学生银行卡号
	 * @param unknown $studentCardAccount 学生银行卡户名
	 */
	public function DCPAYMNT($finance_id,$uid='',$sum='',$studentCardNo='',$studentCardAccount='',$tel='')
	{
// 		$this->setHead(__FUNCTION__);
	
// 		if(!$finance_id) return array('code'=>0,'msg'=>'finance_id not null');
// 		//创建业务订单
// 		if($uid) $res = $this->saveOrder($finance_id,$uid);
// 		if(!$this->order_id) return array('code'=>0,'msg'=>'create order failed');
// 		$studentCardNo = $this->decrypt($studentCardNo);
// 		if(!$studentCardNo) return array('code'=>0,'msg'=>'studentCardNo failed');
		
		//验证手机号
		$info = M('bank_finance')->where(array('id'=>$finance_id))->find();
		$card_id = $info['bank_card_id'];
		if(!$card_id) return array('code'=>0,'msg'=>'card id failed');
		$checkTel = D('Check','bank')->checkMobile($card_id);

		if(!$checkTel) return array('code'=>'-1','msg'=>'check tel failed');
		return 'suc';
		/*
		$this->sendData['SDKPAYRQX'] = array(
				'BUSCOD'=>'N02031',
		);
		$this->sendData['DCOPDPAYX'] = array(
				'YURREF'=>$this->order_id,
				'DBTACC'=>self::payCompanyNo,//企业付款账号
				'DBTBBK'=>self::payBankNo,//分行代码
				'TRSAMT'=>$sum,//总金额
				'CCYNBR'=>10,//币种
				'STLCHN'=>'F',//总笔数
				'NUSAGE'=>'代发',
				// 											'EPTDAT'=>date('Ymd'),
		// 											'EPTTIM'=>date('His'),
				'CRTACC'=>$studentCardNo,//学生卡号
				'CRTNAM'=>$studentCardAccount,
				'BNKFLG'=>'Y',
		);
	
		//发送xml至银行
		$post = $this->doBusiness();
	
		$revData = $this->revData;
		$returnData = array();

		//开始验证返回结果
		if(in_array($this->revData['INFO']['RETCOD'], array('0','-1','-9')) && isset($this->revData['NTQPAYRQZ'])) {
			//REQNBR 为银行订单号或流水号,需要更新到数据库
			$this->bank_order_id = $this->revData['NTQPAYRQZ']['REQNBR'];
	
			if($this->revData['NTQPAYRQZ']['REQSTS'] == 'FIN' && $this->revData['NTQPAYRQZ']['RTNFLG'] == 'F') {
				$returnData = array('code'=>0,'msg'=>'代发失败','result'=>$revData);
			} else {
				if($this->bank_order_id) {
					//单笔订单查询
					 					$sdate = '20150111';
					 					$edate = '20150128';
	
//					$res = D('GetPaymentInfo','bank')->GetPaymentInfo($this->order_id,$sdate,$edate);
					$res = D('GetPaymentInfo','bank')->GetPaymentInfo($this->order_id);
// 					$res = D('GetPaymentInfo','bank')->GetPaymentInfo(10006,$sdate,$edate);

					if($res['code'] == '1') {
						//更新放款数据,开始还款状态
						M('bank_finance')->where(array('id'=>$finance_id))->save(array('status'=>1));
						M('bank_finance_detail')->where(array('bank_finance_id'=>$finance_id))
						->save(array('status'=>1));
						$returnData = array('code'=>1,'msg'=>'代发成功','result'=>$revData);
					} else {
						M('bank_finance')->where(array('id'=>$finance_id))->save(array('status'=>3));
						$returnData = array('code'=>3,'msg'=>'正在代发','result'=>$revData);
					}
				}
			}
		}
			
		if(empty($returnData)) $returnData = array('code'=>0,'msg'=>'代发失败','result'=>$revData);
	
		//更新数据
		$this->saveOrder('',$uid,$this->bank_order_id,$returnData['code'],$returnData['msg']);
		return $returnData;
		*/
	}
	/**
	 * 非即时放款 http://xyhui.lo/?app=api&mod=Test&act=lend
	 * @param unknown $order_id PU订单ID 对应ts_bank_finance的id
	 * @param unknown $sum 放款金额
	 * @param unknown $studentCardNo 学生银行卡号
	 * @param unknown $studentCardAccount 学生银行卡户名
	 */
	public function AgentRequest($finance_id,$uid,$sum,$studentCardNo,$studentCardAccount)
	{
		$this->setHead(__FUNCTION__);
		
		if(!$finance_id) return array('code'=>0,'msg'=>'finance_id not null');

		//创建业务订单
		if($uid) $res = $this->saveOrder($finance_id,$uid);
		if(!$this->order_id) return array('code'=>0,'msg'=>'create order failed');
		
		$this->sendData['SDKATSRQX'] = array(
											'BUSCOD'=>'N03010',//N03020
											'BUSMOD'=>'00001',//00004
											'C_TRSTYP'=>'代发其他',
											'TRSTYP'=>'BYBK',//上线用BYBF代发个人贷款
											'DBTACC'=>self::payCompanyNo,//企业付款账号
											'BBKNBR'=>self::payBankNo,//分行代码
											'BANKAREA'=>self::payBankName,
											'SUM'=>$sum,//总金额
											'TOTAL'=>1,//总笔数
											'YURREF'=>$this->order_id,
											'MEMO'=>'代发',
// 											'GRTFLG'=>'Y',
										);
		$this->sendData['SDKATDRQX'] = array(
											'ACCNBR'=>$studentCardNo,//学生卡号
											'CLTNAM'=>$studentCardAccount,
											'TRSAMT'=>$sum,
											'TRSDSP'=>'代发学生贷款',
										);
		
		//发送xml至银行
		$post = $this->doBusiness();
		
		//开始验证返回结果
// 		if($this->revData['INFO']['RETCOD'] != '0') return array('code'=>0,'msg'=>'代发失败','result'=>$this->revInfo);
		
		if(isset($this->revData['NTREQNBRY'])) {
			//REQNBR 为银行订单号或流水号,需要更新到数据库
			$this->bank_order_id = $this->revData['NTREQNBRY']['REQNBR'];

			$code = 0;
			if($this->sendData['SDKATSRQX']['GRTFLG'] == 'Y' && $this->revData['NTREQNBRY']['RSV50Z'] == 'OPR') {
				//成功
				$code = 1;
			} else if($this->sendData['SDKATSRQX']['GRTFLG'] == '' && $this->revData['NTREQNBRY']['RSV50Z'] == 'BNK') {
				//成功
				$code = 1;
			}
			if($code) {
				//单笔订单查询
				$returnData = array('code'=>1,'msg'=>'代发成功，银行处理中','result'=>$this->revInfo);
			}
		}
		
		if(empty($returnData)) $returnData = array('code'=>0,'msg'=>'代发失败','result'=>$this->revInfo);
		
		//更新数据
		$this->saveOrder('',$uid,$this->bank_order_id,$returnData['code'],$returnData['msg']);
		return $returnData;
	}
	
	/**
	 * 与银行通信，验证办卡时绑定的手机号是否一致
	 * @param unknown $tel
	 * @return boolean
	 */
	public function checkTel($finance_id,$uid,$studentCardNo,$studentCardAccount,$tel)
	{
		
		
		$check = 0;
		//开始验证
		
		$data = array(
				'check'=>$check
		);
		return M('ts_bank_card')->where(array('id'=>$card_id))->save($data);
		return true;
	}
	
	
	/**
	 * 生成放款订单
	 */
	public function saveOrder($finance_id='',$uid,$bank_order_id='',$bank_code=0,$bank_msg='')
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
						'finance_id'=>$finance_id,
						'uid'=>$uid,
						'stime'=>$datetime,
						'status'=>1,
						);
			$this->order_id = M($this->tableName)->add($data);
		}
	}
}

?>
