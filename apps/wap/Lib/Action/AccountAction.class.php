<?php
/**
 * pu币\h5
 * 
 *
 */
class AccountAction extends BaseAction {
	public $length=10;
	
	
	
	//pu币消费充值明细h5
	public function puList(){
		//默认显示前十条
		//拉取消费明细
		$data=$this->getMylist();
		$this->assign('list',$data);
		$this->display();
	}
	
	//pu首页h5
	public function puIndex(){
		$money = Model('Money')->getMoneyCache($this->mid);
		$pumoney = M('PufinanceMoney')->getField('money', array('uid' => $this->mid));
        $this->user['money'] = $money + $pumoney;
		$this->assign('user',$this->user);
		//用户城市id
		$cid=getCityByUid($this->mid);
		$this->assign('cid',$cid);
		$this->display();
	}

	//pu币消费充值明细h5(ajax)
	public function ajaxRechargeList(){
		$page=intval($_REQUEST['page']);
		if($page>0){
			$offset=($page-1)*$this->length;
			$list=$this->getMylist($offset);
		}
		if(!empty($list))
		{
				foreach($list as &$v)
				{
						$v['ctime']=date("Y-m-d",$v['ctime']);
				}
				$t['status']=1;
				$t['data']=$list;
		}else
		{
			$t['status']=0;
			$t['data']='';
		}
		echo json_encode($t);
	}

	public function getMylist($offset=0)
	{
		$sql = "select * from (select uid,'1' as `type`,typeName,logMoney,ctime, '0' AS pumoney from ts_money_in where uid=%d union select out_uid as uid,'0' as `type`,out_title as typeName,out_money as logMoney,out_ctime as ctime,out_pumoney AS pumoney from ts_money_out where out_uid=%d) a order by ctime desc limit %d,%d " ;
		$sql = sprintf($sql,$this->mid,$this->mid,$offset,$this->length) ;
		$con = M()->query($sql) ;
		foreach($con as &$v)
		{
            $v['logMoney'] += $v['pumoney'];
			$v['logMoney'] = money2xs($v['logMoney']);
		}
		return $con;
	}

	
	
	
	
	
	
	
	
	
	
}