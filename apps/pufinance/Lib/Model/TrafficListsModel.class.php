<?php
class TrafficListsModel extends Model
{
    //列表
    public function getLists($map=array(), $order = 'tl.id desc'){
		$dbPrefix = C('DB_PREFIX');
        return $this->table($dbPrefix.'traffic_lists tl')
                    ->field('tl.id,tl.face_value,tl.flow_value,tl.is_show,tl.quoted_price,tl.discount,tl.zero_sale_price,tl.price,topt.name')
                    ->join($dbPrefix.'traffic_operator topt on topt.id=tl.traffic_operator_id')
                    ->order($order)
                    ->where($map)
                    ->findPage(15); 
    }
}