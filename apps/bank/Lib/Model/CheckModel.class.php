<?php

/**
 * 
 * @author Administrator
 * 办卡相关
 */
header ( "Content-type: text/html; charset=utf-8" );
import('bank.Model.CMBModel');
class CheckModel extends CMBModel
{
	public $tableName = 'bank_card';
	/**
	 * 与银行通信，验证办卡时绑定的手机号是否一致
	 */
	public function checkMobile($card_id)
	{
		if(!intval($card_id)) return array('code'=>0,'msg'=>'card_id not null');

		$cardInfo = M('bank_card')->where(array('id'=>$card_id))->find();
		if(!$cardInfo) return array('code'=>0,'msg'=>'card info not null');
		
		if($cardInfo['check'] == '2') return array('code'=>1,'msg'=>'验证通过');
		$check = 0;
		//开始验证
		$data = array();
		
		$data['name'] = $this->encrypt($cardInfo['realname']);
// 		$data['cadnbr'] = $this->encrypt($cardInfo['card_no']);
		$data['cadnbr'] = $cardInfo['card_no'];
		$data['mobile'] = $this->encrypt($cardInfo['mobile']);
		
		//拼接发送数据
		$sendData = json_encode($data);

		//加密数据得到mac值
		$mac = md5($sendData.self::key);

		//发送地址
		$url = self::applyCardUrl.'?func='.__FUNCTION__.'&mac='.$mac;

		//发送数据并返回结果
		$res = $this->http_post_data($url, $sendData,false);

		if($res['rc'] != '0') {
			M('bank_card')->where(array('id'=>$card_id))->save(array('check'=>1));
			$returnData = array('code'=>0,'msg'=>'验证失败，'.$res['msg'],'result'=>$data);
		} else {
			if($res['msg'] == 'TRUE') {
				M('bank_card')->where(array('id'=>$card_id))->save(array('check'=>2));
				$returnData = array('code'=>1,'msg'=>'验证通过','result'=>$data);
			} else {
				M('bank_card')->where(array('id'=>$card_id))->save(array('check'=>1));
				$returnData = array('code'=>0,'msg'=>'验证失败，'.$res['msg'],'result'=>$data);
			}
		}
		
		return $returnData;
	}
}

?>
