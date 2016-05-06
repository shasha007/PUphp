<?php
/**
 * Created by PhpStorm.
 * User: wen ji ping
 * Date: 2016/1/13
 * Time: 15:32
 */

class CheckInTypeModel extends Model{


    protected $_auto = array(
        array('start_time','strtotime',3,'function'),
        array('end_time','strtotime',3,'function'),
    );

    /**
     * 获取指定签到类型的统计时间段
     * @param int $type_id          类型ID
     * @return array|bool
     */
    public function getCurrentTime($type_id){
        $map['id'] = $type_id;
        $data = $this->field('start_time,end_time')->find($type_id);
        return $data;
    }

}