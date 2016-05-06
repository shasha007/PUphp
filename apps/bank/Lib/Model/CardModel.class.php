<?php

/**
 * 
 * @author Administrator
 * 办卡相关
 */
header ( "Content-type: text/html; charset=utf-8" );
import('bank.Model.CMBModel');
class CardModel extends CMBModel
{
	public $tableName = 'bank_applycard_order';
	public $logTableName = 'bank_applycard_order_log';
	/**
	 * 办卡
	 */
	public function sendApplyInfo($card_id,$uid)
	{
		if(!intval($card_id)) return array('code'=>0,'msg'=>'card_id not null');
		
		//创建业务订单
		if($uid) $res = $this->saveOrder($card_id,$uid);
		if(!$this->order_id) return array('code'=>0,'msg'=>'order_id not null');
		
		$itemCount = 1;
		
		$cardInfo = M('bank_card')->where(array('uid'=>$uid,'id'=>$card_id,'status'=>'0'))->find();
		if(!$cardInfo) return array('code'=>0,'msg'=>'card info not null');

		//--------------------------
// 		M('bank_card')->where(array('id'=>$cardInfo['id']))->save(array('status'=>1));
// 		return array('code'=>1,'msg'=>'办卡成功');
		
		
		$items = array();
		
		$items['apply_id'] = $card_id;
		$items['name'] = $cardInfo['realname'];
		$items['ctf_id'] = $cardInfo['ctf_id'];
		$items['mobile'] = $cardInfo['mobile'];
		$items['email'] = $cardInfo['email_bill'];
		$items['address'] = $cardInfo['address'];
		$items['post_code'] = $cardInfo['post_code'];
		$items['channel'] = $cardInfo['channel'];
		$items['Uid'] = $cardInfo['uid'];
		$schoolInfo = M('school')->field('title')->where(array('id'=>$cardInfo['school_id']))->find();
		$items['School'] = $schoolInfo['title'];
		$items['region'] = $cardInfo['area'];
		$carddetailInfo = M('bank_user_info')->field('education,home')->where(array('uid'=>$uid))->find();
		$items['education'] = (($carddetailInfo['education'] == '2')?'B':'C');
		$items['HomeAdress'] = $carddetailInfo['home'];
		$items['HomePostCode'] = '';
		
		$data = array(
					'seq'=>$this->order_id,
					'count'=>1,
					'data'=>array('items'=>array($items)),
					);

		//拼接发送数据
		foreach($data['data']['items'][0] as $k => $v) {
			$data['data']['items'][0][$k] = $this->encrypt($v,self::key);
		}
		$sendData = json_encode($data);

		//加密数据得到mac值
		$mac = md5($sendData.self::key);

		//发送地址
		$url = self::applyCardUrl.'?func='.__FUNCTION__.'&mac='.$mac;
		
		//发送数据并返回结果
		$res = $this->http_post_data($url, $sendData,false);
		error_log(date('Y-m-d H:i:s').' '.json_encode($res).'@@@'.$res['rc'].' @@@@@'."\n",3,__DIR__.'/card.log');
		
		if($res['rc'] == '0') {
			M('bank_card')->where(array('id'=>$cardInfo['id']))->save(array('status'=>1));
			$returnData = array('code'=>1,'msg'=>'办卡成功，正在审核','result'=>$data,'bankresult'=>$res);
		} else {
			M('bank_card')->where(array('id'=>$cardInfo['id']))->save(array('status'=>3));
			$returnData = array('code'=>3,'msg'=>'办卡失败'.$res['msg'],'result'=>$data,'bankresult'=>$res);
		}
		
		//更新数据
		$this->saveOrder('',$uid,$this->bank_order_id,$res['rc'],$res['msg']);
		
		return $returnData;
	}
	
	/**
	 * 生成办卡订单
	 */
	public function saveOrder($card_id,$uid,$bank_order_id='',$bank_code=0,$bank_msg='')
	{
		$datetime = date('Y-m-d H:i:s');
		if($this->order_id) {
			$data = array(
						'bank_order_id'=>$bank_order_id,
						'bank_code'=>$bank_code,
						'bank_msg'=>$bank_msg,
						);
			return M($this->tableName)->where(array('order_id'=>$this->order_id))->save($data);
		} else {
			$data = array(
						'bank_card_id'=>$card_id,
						'uid'=>$uid,
						'stime'=>$datetime,
						'status'=>1,
						);
			
			$this->order_id = M($this->tableName)->add($data);
		}
	}
}

?>
