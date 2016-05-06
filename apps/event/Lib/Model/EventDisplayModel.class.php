<?php

/**
 * EcApplyModel
 * 推荐的活动库
 * @uses Action
 * @package
 * @version $id$
 * @license PHP Version 5.3
 */
class EventDisplayModel extends Model {
	// 添加活动到活动库
	public function addEventToDisplay($event_ids){
		$this->startTrans();
		$data['time'] = time() ;
		foreach ($event_ids as $key => $value) {
			$data['event_id'] = $value ;
			if ($this->where('event_id='.$value)->find()) {
				if (!$this->where('event_id='.$value)->save($data)) {
					$this->rollback();
					return 0 ;
				}
			}else{
				if (!$this->add($data)) {
					$this->rollback() ;	
					return 0 ;
				}
			}
		}
		$this->commit() ;
		return 1 ;
	}

	// 从活动库删除活动
	public function deleteEventFromDisplay($ids){
		$map['id'] = array('IN',$ids) ;
		if ($this->where($map)->delete()) {
			return 1 ;
		}
		return 0 ;
	}

	public function searchEvent($map=array()){
		$map['ts_event_display.isDel'] = 0 ;
		$result = $this->join("ts_event e on e.id = ts_event_display.event_id")->where($map)->order('ts_event_display.id desc ')->field("e.title,ts_event_display.*")->findpage("20") ;
		return $result ;
	}

	public function getAllDisplay(){
		$map['ts_event_display.isDel'] = 0 ;
		$result = $this->join("ts_event e on e.id = ts_event_display.event_id")->where($map)->order('ts_event_display.id desc ')->field("e.title,ts_event_display.*")->select() ;
		return $result ;
	}
}