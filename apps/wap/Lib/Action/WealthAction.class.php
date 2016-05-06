<?php
/*
 * 我的财富\h5
 *
 *
 */
class WealthAction extends BaseAction {
	// 如果传入uid则显示别人的 信息
	public function _initialize(){
		parent::_initialize() ;
		if (intval($_REQUEST['userid'])) {
			$this->mid = intval($_REQUEST['userid']) ;
		}
	}
	private function _getMap() {
		$map['a.uid'] = $this->mid;
		$map['a.status'] = array('gt',0);
		//活动完结的才算学分
		$map['b.isDel'] = 0 ;
		$map['b.school_audit'] = 5;
		return $map;
	}
	//活动积分h5
	public function scoreList(){
		$list=$this->getDetail('s');
		$score=$this->getEventScore();
		$this->assign('list',$list);
		//活动积分
		$this->assign('score',$score['score']);
		$this->display();
	}
	
	//活动学分h5
	public function creditList(){
		//学分
		$pocket_credit = M('user')->where('uid='.$this->mid)->getField('school_event_credit') ;
		$ec_credit = M('ec_apply')->where('uid='.$this->mid.' and status =1 ')->getField('sum(credit)') ;
		$ec_credit += M('ec_identify')->where('uid='.$this->mid.' and status =1 ')->getField('sum(credit)') ;
		$score=$this->getEventScore();
		//申请学分
		$list1 = $this->geteccredit() ;
		//活动学分列表
		$list=$this->getDetail('c');
		// dump($list) ; die ;
		$this->assign('list1',$list1);
		$this->assign('list',$list);
		//活动学分
		$this->assign('pocket_credit',$pocket_credit) ;
		$this->assign('ec_credit',$ec_credit) ;
		$this->assign('name',$name) ;
		$this->assign('credit',$score['credit']);
		$this->display();
	}
	/*ajax活动积分和活动学分
	 * 其中data['score']=活动积分，data['credit']=活动学分
	 */
	public function ajaxList(){
		$ctype = intval($_REQUEST['ctype']);
		if ($ctype === 1) {
			$list = $this->geteccredit();
			if (empty($list)) {
				$t['status'] = 0 ;
				$t['data'] = '' ;
			}else{
				$t['status'] = 1 ;
				$t['data'] = $list ;
			}
		}else{
			$type = $_REQUEST['type'];
			$list = $this->getDetail($type);
			if(!empty($list))
			{
				$t['status']=1;
				foreach($list as &$v)
				{
					$v['cTime']=date("Y-m-d",$v['cTime']);
				}
				$t['data']=$list;
			}else
			{
				$t['status']=0;
				$t['data']='';
			}
		}
		echo json_encode($t);
	}
	
	//用户活动学分积分明细
	protected function getDetail($type = 's'){
		$list=array();
		$this->_item_count=10;
		$offset=($this->_page-1)*$this->_item_count;
		if($this->mid && $this->sid!=480)
		{
			$map=$this->_getMap();
			if($type == 's')
			{
				 $map['_string'] = '(a.score+a.addScore)>0';
			}
			if($type == 'c')
			{
				 $map['_string'] = '(a.credit+a.addCredit)>0';
			}
			$list =Model()->table('ts_event_user as a')
										->join(' ts_event as b on b.id = a.eventId')
										->where($map)
										->field('a.id as a,a.eventId as eventId,a.credit as credit ,a.score as score,a.addCredit as addCredit,a.addScore as addScore,a.cTime as cTime')
										->order('a.id DESC')
										->limit("$offset,$this->_item_count")
										->select();
			if(!empty($list))
			{
				foreach($list as &$v)
				{
					$t= D('Event')->getField('title','id='.$v['eventId']);
					$v['title']=htmlspecialchars_decode($t, ENT_QUOTES);
					$v['imgurl']=tsGetCover(D('Event')->getField('coverId','id='.$v['eventId']),$width=125,$height=125,'c');
					$v['credit'] =$v['credit']+$v['addCredit'];//活动学分
					$v['score'] = $v['score']+$v['addScore'];//活动积分
				}
			}
			
		}
		
	return $list;
	}	
	
	
	
	
	//获取活动积分和活动学分
	private function getEventScore(){
		$data=array(
					'score'=>0,//活动积分
					'credit'=>0//活动学分
					);
		$map=$this->_getMap();
		$list = Model()->table('ts_event_user as a')
						->join(' ts_event as b on b.id = a.eventId')
						->where($map)
						->field('SUM(a.score+a.addscore) as score,SUM(a.credit+a.addCredit) as credit')
						->select();
		if(!empty($list))
		{
		foreach($list as $v)
		{
			if(!empty($v['score']))
			{
				 $data['score']=$v['score'];
			}
			if(!empty($v['credit']))
			{
				$data['credit']=$v['credit'];
			}
			
		}
		}
		return  $data;
	}
	
	
	
	private function geteccredit(){
		$length = 10 ;
		$page = intval($_REQUEST['page'])?intval($_REQUEST['page']):1 ;
		$offset=($page-1)*$length;
		$sql = "select * from ( select `title`,`cTime`,`credit` from ts_ec_apply where uid=%d and status=1 union select `title`,`cTime`,`credit` from ts_ec_identify where uid=%d and status=1 )  a order by cTime desc limit %d,%d " ;
		$sql = sprintf($sql,$this->mid,$this->mid,$offset,$length) ;
		$con = M()->query($sql) ;
		return $con ;
	}
	
	
	
	
	
	
	
	
	
}