<?php
class OrderAction extends BaseAction
{
	public function index(){

		$types = array(
			1 => L('finance_name'),
			//2 => '消费',
		);
		$titles = array(
			1 => L('finance_name'),
		);
		$map = array();
		
		/*
		//条件组合
		if(!empty($_GET['t']) && (strtolower($_GET['t']) != 'all') && in_array((int)$_GET['t'],array(1,2)) ){//类型 对于用户开放 1-借款 2-消费
			$map['type'] = (int)$_GET['t'];
		}
		*/
		
		$map['type'] = 1;//类型，现在只开放借款的类型
		
		if(isset($_GET['s']) && in_array($_GET['s'],array(0,1,4,5))){
			$map['status'] = (int)$_GET['s'];
		}

		if(isset($_GET['s']) && in_array($_GET['s'],array(2,3))){
			$map['_string'] = 'status in (2,3)';
		}

		$map['uid'] = $this->mid;//用户uid
		$lists = D('PufinanceOrder')->getUserOrderList($map,'*', 'id desc');
		$this->assign('types',$types);
		$this->assign('titles',$titles);
		$this->assign('orders',$lists);
		$this->display('order_list');
	}
}