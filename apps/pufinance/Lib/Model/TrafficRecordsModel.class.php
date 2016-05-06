<?php
class TrafficRecordsModel extends Model
{
    //列表
    public function getLists($map=array(), $order = 'tr.id desc'){
		$dbPrefix = C('DB_PREFIX');
        return $this->table($dbPrefix.'traffic_records tr')
                    ->field('tr.uid,tr.mobile,tr.ctime,tr.etime,tr.money,tr.status,topt.name,tl.flow_value')
                    ->join($dbPrefix.'traffic_operator topt on topt.id=tr.traffic_operator_id')/*运营商*/
                    ->join($dbPrefix.'traffic_lists tl on tl.id=tr.traffic_lists_id')/*流量包*/
                    ->order($order)
                    ->where($map)
                    ->findPage(15); 
    }

    //添加记录
    public function add(){

    }
}