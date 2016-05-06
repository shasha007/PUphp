<?php
/**
 * 全国热门活动\h5
 *
 *
 */
class EventAction extends BaseAction {
	private $daoEvent;
	private $cid;
	private $pro_id;
	private $order;
	private $offset;
	public function __construct() {
		parent::__construct();
		$this->daoEvent = D('Event', 'event');
		$sid = getUserField($this->mid, 'sid');
		//市
		$this->cid = model('Schools')->getCityId($sid);
		//省
		$this->pro_id =model('Schools')->getProvId($sid);
		$this->order='id desc';
		$this->offset=($this->_page-1)*$this->_item_count;
	}
	
	private function _getMap() {
		$map['isDel'] = 0;
		$map['status'] = 1;
		$map['school_audit'] = array('neq', 5);
		//去除pu活动
		$map['is_prov_event'] = array('neq', 2);
		return $map;
	}
	// hit desc,sTime desc,eTime desc  原来的排序
	private function _getEvent($map){
		$list=M('event')->field('id,gid,title,coverId,sTime,eTime,isTop,sid,credit,address')
						->where($map)
						->limit("$this->offset,$this->_item_count")
						->order('puRecomm DESC,isTop DESC,isHot DESC,joinCount DESC')
						->select();
		return $list;
	}
	//处理标签
	private function getTag(&$list){
		if(!empty($list))
		{
			foreach($list as &$row)
			{	$row['cover'] = tsGetCover($row['coverId'],125,125,'c');
				$row['isTop'] = $row['isTop'] == 0 ? '' : '推荐';
				//20160218修改：学校标签全部的学院名称改成“院”，
				$row['cName'] = $row['sid']<0 ? '校' : '院';
				$row['isCredit'] = intval($row['credit']) == 0 ? '' : '学分';
			}
		}
	}
	
	//处理时间
	private function doTime(&$list) {
		if(!empty($list))
		{
		foreach($list as &$v)
		{	//处理时间
			$v['duration']=trim(date("Y.m.d",$v['sTime']).'-'.date("Y.m.d",$v['eTime']));
		}
		}
	}
	
	//全国
	private function allChina() {
		$map = $this->_getMap();
		$list = $this->_getEvent($map);
		$this->getTag($list);
		return $list;
	}
	
	//全省
	private function allPro() {
		$map = $this->_getMap();
		$sid = getUserField($this->mid, 'sid');
		$s_map['provinceId'] = intval($this->pro_id);
		$sidArr = M('school')->where($s_map)->field('id')->select();
		foreach($sidArr as $k=>$v){
			$sidArray[] = $v['id'];
		}
		//20160215将本校的id也加进去
		$sidArray[] =$sid;
		$map['is_school_event'] = array('IN',join(',',$sidArray));
		$list = $this->_getEvent($map);
		$this->getTag($list);
		return $list;
	}
	
	//本校
	private function allSch() {
		$map = $this->_getMap();
		$sid = getUserField($this->mid, 'sid');
		$map['is_school_event'] = $sid;//本校活动
		$list = $this->_getEvent($map);
		$this->getTag($list);
		return $list;
	}
	
	//本市
	private function allCity() {
		$map = $this->_getMap();
		$sid = getUserField($this->mid, 'sid');
		$s_map['cityId'] = intval($this->cid);
		$sidArr = M('school')->where($s_map)->field('id')->select();
		foreach($sidArr as $k=>$v){
			$sidArray[] = $v['id'];
		}
		//20160215将本校的id也加进去
		$sidArray[] =$sid;
		$map['is_school_event'] = array('IN',join(',',$sidArray));
		$list = $this->_getEvent($map);
		$this->getTag($list);
		return $list;
	}
	
	//活动首页
	public function eventList() {
		$sid = getUserField($this->mid, 'sid');
		$title='';
		$default=intval($_REQUEST['ischool']);
		if($default>0)
		{
			//从首页本校热门活动跳转
			if (S("hot_event_school_".$sid."_1")) {
				$data = json_decode(S("hot_event_school_".$sid."_1"),true) ;
			}else{
				$data=$this->allSch();
				$jsn = json_encode($data) ;
				S("hot_event_school_".$sid."_1",$jsn) ;
			}			
			$title='本校';
		}
		else 
		{
			//默认全国
			if (S('hot_event_contry_1')) {
				$data = json_decode(S('hot_event_contry_1'),true) ;
			}else{
				$data=$this->allChina();
				$jsn = json_encode($data) ;
				S('hot_event_contry_1',$jsn) ;
			}
			
		}
		
		$list = empty($data) ? array() : $data;
		//处理时间
		$this->doTime($list);
		$this->assign('title',$title);
		$this->assign('list',$list);
		$this->display();
	}
	
	//ajax数据
	public function ajaxGetEvent(){
		//区域:1全国2全省3全市4本校
		$sid = getUserField($this->mid, 'sid');
		$a=intval($_REQUEST['aid']);
		switch($a)
		{	
			case $a == 1:
				if (S("hot_event_contry_$this->_page")) {
					$data = json_decode(S("hot_event_contry_$this->_page"),true) ;
				}else{
					$data=$this->allChina();
					S("hot_event_contry_$this->_page",json_encode($data)) ;	
				}
				break;
			case $a == 2:
				if (S("hot_event_pro_$this->pro_id_$this->_page")) {
					$data = json_decode(S("hot_event_pro_$this->_page"),true) ;
				}else{
					$data=$this->allPro();
					S("hot_event_pro_$this->pro_id_$this->_page",json_encode($data)) ;
				}
				break;
			case $a == 3:
				if(S("hot_event_city_$this->cid_$this->_page")){
					$data = json_decode(S("hot_event_city_$this->_page"),true) ;
				}else{
					$data=$this->allCity();
					S("hot_event_city_$this->cid_$this->_page",json_encode($data)) ;
				}
				break;
			case $a == 4:
				if(S("hot_event_school_".$sid."_".$this->_page)){
					$data = json_decode(S("hot_event_school_".$sid."_".$this->_page),true) ;
				}else{
					$data=$this->allSch();
					S("hot_event_school_".$sid."_".$this->_page,json_encode($data)) ;
				}
				break;
				
		}
		$list = empty($data) ? array() : $data;
		//处理时间
		$this->doTime($list);
		if(!empty($list))
		{
			$t['status']=1;
			$t['data']=$list;
		}
		else
		{
			$t['status']=0;
			$t['data']='';
		}
		echo json_encode($t);
	}

	// 本校热门活动
	public function hotSchoolEvent(){
		// $this->_item_count = 2  ;
		$sid = getUserField($this->mid,'sid') ;
		$offset = $this->offset ;
		$count = $this->_item_count ;
		$map = $this->getSchoolmap() ;
		$order = $this->getSchoolorder() ;
		$list = $this->_getSchoolEvent($map,$offset,$count,$order) ;
		// 学校组织
		$organize = D('SchoolOrga','event')->getAll($sid);
		// 学院
		$faculty = model('Schools')->makeLevel0Tree($sid);
		$faculty = array_merge($organize,$faculty) ;
		foreach ($faculty as $key => $value) {
			if ($value['pid']) {
				$id = $value['id'] ;
			}else{
				$id = -$value['id'] ;
			}
			$facultyall[$id] = $value ;
		}
		// 获取活动分类
		$type = D('EventType','event')->getType2($sid);
		// dump($facultyall) ; die ;
		$this->assign($_REQUEST) ;
		$this->assign('list',$list) ;
		$this->assign('faculty',$facultyall) ;
		$this->assign('types',$type) ;
		$this->display() ;
	}

	// jax 下拉刷新
	public function hotSchoolEventAjax(){
		// $this->_item_count = 2  ;
		$sid = getUserField($this->mid,'sid') ;
		$offset = $this->offset ;
		$count = $this->_item_count ;
		$map = $this->getSchoolmap() ;
		$order = $this->getSchoolorder() ;
		$list = $this->_getSchoolEvent($map,$offset,$count,$order) ;
		if (empty($list)) {
			echo 0 ; die ;
		}
		$this->assign('list',$list) ;
		$this->display() ;
	}
	// 获取本校热门活动条件
	private function getSchoolmap($map=array()){
		$map = $this->_getMap() ;
		$sid = getUserField($this->mid,'sid') ;
		// 结束不显示 必须学校活动
		$map['eTime'] = array('GT',time()) ;
		$map['is_school_event'] = $sid;
		// 分类
		$type = intval($_REQUEST['type']) ;
		// 归属组织
		$sid = intval($_REQUEST['sid']) ;
		if ($type > 1) {
			$map['typeId'] = $type ;
		}
		if ($sid) {
			$map['sid'] = $sid ;
		}
		return $map ;
	}
	// 获取排序
	private function getSchoolorder(){
		$order = t($_REQUEST['order']) ;
		switch ($order) {
			case 'start':
				$order = 'sTime desc ,id desc ' ;
				break;
			case 'hot':
				$order = 'puRecomm DESC,isTop DESC,isHot DESC,joinCount DESC' ;
				break;
			case 'new':
				$order = 'cTime desc , id desc ' ;
				break;
			default:
				$order = 'puRecomm DESC,isTop DESC,isHot DESC,joinCount DESC' ;
				break ;
		}
		return $order ;
	}
		// 获取本校热门活动用的
	private function _getSchoolEvent($map,$offset=0,$count=20,$order='puRecomm DESC,isTop DESC,isHot DESC,joinCount DESC'){
		$list=M('event')->field('id,gid,title,coverId,sTime,eTime,isTop,sid,credit,address')
						->where($map)
						->limit("$offset,$count")
						->order($order)
						->select();
		$this->getTag($list);
		$this->doTime($list);
		return $list;
	}
}