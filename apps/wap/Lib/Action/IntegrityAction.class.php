<?php 
class IntegrityAction extends BaseAction{

	public function index(){
		$uid = $this->mid ;
		if (intval($_REQUEST['userid']) > 1) {
			$uid = intval($_REQUEST['userid']) ;
		}
		$cs = '暂无' ;
		$cx = M('event_cx')->where('uid='.$uid)->find();
		if (!empty($cx)) {
			$cs = ceil($cx['attend']*100/$cx['total']).'%';	
		}
		$this->assign('cs',$cs) ;
		$ids = M('cron_cxlist')->where('uid='.$uid)->field('eventId,isAttend')->select() ;
			foreach ($ids as $key => $value) {
				$evids[] = $value['eventId'] ; 
				$att[$value['eventId']] = $value['isAttend']?2:0 ;
			}
		$where['id'] = array('IN',$evids) ;
		$event = M('event')->where($where)->order('id desc')->field('title,coverId,cTime,credit,status,id as eventId')->select() ;
		foreach ($event as $key => $value) {
			if (intval($event[$key]['credit']) ==0) {
				$event[$key]['credit'] = '' ;	
			}
			$event[$key]['status'] = $att[$value['eventId']] ;
			$event[$key]['pic'] = tsGetCover($value['coverId'],200,200,'c') ;
			$event[$key]['time'] = date('Y-m-d',$value['cTime']) ;
		}
		$this->assign('event',$event) ;
		$this->assign('cx',$cx);
		//dump($cx) ;
		//dump($event) ; die ;
		$this->display() ;

	}

}
 ?>