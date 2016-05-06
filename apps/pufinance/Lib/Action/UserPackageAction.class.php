<?php
class UserPackageAction extends BaseAction
{
	public function index(){
		$map = array(
			'uid' => $this->mid,
			'_string' => 'is_receive=1 OR etime>'.time(),
		);
		$lists = D('PufinanceAmount')->getAmountLists($map);
		if(is_array($lists)){
			foreach($lists as $k=>$item){
				$lists[$k]['month'] = date('m',$item['ctime']);
			}
		}
		$this->assign('lists',$lists);
		$this->display();
	}
	
	//领取提额包
	public function receive(){
		$id = (int)$_POST['id'];
		$result = M('pufinance_amount')->where('id='.$id.' and is_receive=0')->find();//提额包
		$credit = M('pufinance_credit')->where('uid='.$this->mid)->find();//PU金
		$msg = array();
		if(empty($result)){
			$msg['code'] = -1;
			$msg['msg'] = '未找到或已领取';
			echo json_encode($msg);
			exit;
		}
		if($result['etime'] < time()){//判断是否过期失效
			$msg['code'] = -2;
			$msg['msg'] = '领取时间已经过期！';
			echo json_encode($msg);
			exit;
		}
		$data = array();
		if($result['type'] == 0){//总额度
			$data['all_amount'] = bcadd($credit['all_amount'], $result['amount'], 2);
			$data['usable_amount'] = bcadd($credit['usable_amount'], $result['amount'], 2);
		}elseif($result['type'] == 1){//免息额度
			$data['free_amount'] = bcadd($credit['free_amount'], $result['amount'], 2);
			$data['free_usable_amount'] = bcadd($credit['free_usable_amount'], $result['amount'], 2);
		}
		M('pufinance_credit')->startTrans();
		$update_credit = M('pufinance_credit')->where('uid='.$this->mid)->data($data)->save();
		$time = time();
		$update_amount = M('pufinance_amount')->where('id='.$id)->data(array('receive_time'=>$time,'is_receive'=>1))->save();
        // PU金记录
        $type = 'upcredit_' . ($result['type'] ? 'free_bag' : 'all_bag');
		$logRes = D('PufinanceCreditLog')->addCreditLog($this->mid, $type, $result['amount']);
		if(!empty($update_credit) && !empty($update_amount) && $logRes){
			M('pufinance_credit')->commit();
			$msg['code'] = 1;
			$msg['msg'] = '成功';
			$msg['ctime'] = $time;
			echo json_encode($msg);
			exit;
		}else{
			M('pufinance_credit')->rollback();
			$msg['code'] = -3;
			$msg['msg'] = '领取失败';
			echo json_encode($msg);
			exit;
		}
	}
	
	//删除操作
	private function del(){
		$id = (int)$_GET['id'];
		$delete = M('pufinance_amount')->where("id={$id} and uid={$this->mid}")->delete();
		if(empty($delete)){
			$this->error('删除失败！');
		}else{
			$this->success('删除失败！');
		}
	}
	
	//提额包分享
	public function share(){
		
		$this->display();
	}
	
}