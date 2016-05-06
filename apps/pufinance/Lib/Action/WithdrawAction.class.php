<?php
class WithdrawAction extends BaseAction
{
	public function index(){
		session_start();
		$money = $this->_getUserAllMoney($this->mid);
		$bank_card_id = (int)$_GET['id'];
		if(!empty($bank_card_id)){
			$bank_info = D('PufinanceBankcard')->getUserAllAvailableBanks($this->mid, array('id'=>$bank_card_id));
			if(empty($bank_info)){
				header('location:'.U('Pufinance/withdraw/bankLists'));
			}else{
				$bank_info = array_shift($bank_info);
				$_SESSION['withdraw'.$this->mid]['bank_card_id'] = $bank_info['id'];
			}
		}else{
			$bank_info = D('PufinanceBankcard')->getUserAllAvailableBanks($this->mid);
			if(empty($bank_info)){
				header('location:'.U('pufinance/Bankcard/bindBankcard'));
			}else{
				$bank_info = array_shift(array_slice($bank_info,0,1));
				$_SESSION['withdraw'.$this->mid]['bank_card_id'] = $bank_info['id'];
			}
		}
		$this->assign('bank_info',$bank_info);
		$this->assign('money',$money['total']/100);
		$this->display();
	}
	
	public function finishShow(){
		$bank_card_id = $_SESSION['withdraw'.$this->mid]['bank_card_id'];//银行卡id
		$amount = $_SESSION['withdraw'.$this->mid]['amount'] * 100;//用户提现的钱(单位分)
		
		//银行信息
		$bank_info = D('PufinanceBankcard')->getUserAllAvailableBanks($this->mid, array('id'=>$bank_card_id));
		$bank_info = array_shift($bank_info);
		
		//用户钱包
		$user_amount = $this->_getUserAllMoney($this->mid);
		
		if(empty($bank_card_id) || empty($amount) || empty($bank_info)){//不满足条件，跳转至提现首页
			header('location:'.U('Pufinance/withdraw/index'));
		}
		
		//判断钱包的钱
		if($amount > $user_amount['total']){
			$this->error('钱包余额不足！');
		}
		M('money_withdraw')->startTrans();
		$data['uid'] = $this->mid;
		$data['ctime'] = time();
		$data['money'] = $amount / 100;
		$data['bank_card_id'] = $bank_card_id;
		$user_money_update = $lend_money_update = $money_withdraw_add = true;
		//分配金额
		if($amount <= $user_amount['user_money']){
			$data['user_money'] = $amount / 100;
			$data['lend_money'] = 0;
			//更新ts_money
			$user_money_update = M('money')->setDec('money',"uid={$this->mid}",$amount);
			$user_money_update = !empty($user_money_update) ? true : false;
		}else{
			$data['user_money'] = $user_amount['user_money'] / 100;
			$data['lend_money'] = ($amount - $user_amount['user_money']) / 100;
			
			//更新ts_money
			if($user_amount['user_money'] != 0){
				$user_money_update = M('money')->where('uid='.$this->mid)->setField('money',0);
				$user_money_update = !empty($user_money_update) ? true : false;
			}
			
			//更新ts_pufinance_money
			$lend_money_update = M('pufinance_money')->setDec('money', 'uid='.$this->mid, $amount - $user_amount['user_money']);
			$lend_money_update = !empty($lend_money_update) ? true : false;
		}
		$money_withdraw_add = M('money_withdraw')->data($data)->add();
		$money_withdraw_add = !empty($money_withdraw_add) ? true : false;

		if(!empty($user_money_update) && !empty($lend_money_update) && !empty($money_withdraw_add)){
			M('money_withdraw')->commit();
			unset($_SESSION['withdraw'.$this->mid]);
			$this->assign('money', $data['money']);
			$this->assign('bank_info',$bank_info);
			$this->display();
		}else{
			M('money_withdraw')->rollback();
			header('location:'.U('Pufinance/withdraw/index'));
		}
	}
	
	//用户可使用的银行卡
	public function bankLists(){
		$banks = D('PufinanceBankcard')->getUserAllAvailableBanks($this->mid);
		$this->assign('banks',$banks);
		$this->display('choose_card');
	}
	
	//新卡身份验证
	public function newBankCheck(){
		session_start();
		$user_info = D('PufinanceUser')->getUserByUid($this->mid);
		if($_POST['str']){
			$msg = array();
			$password = pay_password(trim($_POST['str']), $user_info['salt']);
			if($password == $user_info['paypassword']){
				if(!empty($_POST['amount'])){
					$_SESSION['withdraw'.$this->mid]['amount'] = (float)$_POST['amount'];
				}
				$msg['code'] = 1;
				$msg['msg'] = 'success';
				echo json_encode($msg);
				exit;
			}else{
				$msg['code'] = -1;
				$msg['msg'] = 'fail';
				echo json_encode($msg);
				exit;
			}
		}else{
			$this->display();
		}
	}
	
	//获取用户所有的PU币(单位为分)
	private function _getUserAllMoney($uid){
		$user_money = M('money')->field('money')->where('uid='.$uid)->find();
		$lend_money = M('pufinance_money')->field('money')->where('uid='.$uid)->find();
		$umoney = $user_money['money'] ? $user_money['money'] : 0;
		$lmoney = $lend_money['money'] ? $lend_money['money'] : 0;
		return array(
			'total' => bcadd($umoney, $lmoney, 2),
			'user_money' => $umoney,
			'lend_money' => $lmoney,
		);
	}
	
	
}