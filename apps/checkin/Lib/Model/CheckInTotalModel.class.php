<?php
/**
 * Created by PhpStorm.
 * User: wen ji ping
 * Date: 2016/1/13
 * Time: 16:30
 */

class CheckInTotalModel extends Model{

    /**
     * 取得排行榜
     * @param int $type_id      统计签到类别，默认为
     * @param string $type      default 'continue' mines continue check in count;else mines total check in count
     * @param int $limit        显示前多少名
     * @return array | bool
     */
    public function getRankList($type_id=0,$type='continue',$limit=20){
        if ($type_id == 0){
            return false;
        }
        $map['check_in_type'] = $type_id;
        if ($type=='continue'){
            $order = 'continue_count desc';
        }else{
            $order = 'total_count desc';
        }
        $list = $this->where($map)->order($order)->limit($limit)->select();
        return $list;
    }

    /**
     * 加入到签到统计
     * @param $user_id          签到用户ID
     * @param $type_id          统计类型ID
     * @return int | bool
     */
    public function addToCheckInTotal($user_id,$type_id){
        $map['user_id']         = $user_id;
        $map['check_in_type']   = $type_id;
        $result = $this->where($map)->find();
        if (!$result){
            //如果记录不存在，则添加
            $data['user_id']        = $user_id;
            $data['user_name']      = getUserName($user_id);
            $data['school_id']      = getUserField($user_id,'sid');
            $data['school_name']    = getUserSchoolName($user_id);
            $data['check_in_type']  = $type_id;
            $data['continue_count'] = 1;
            $data['total_count']    = 1;
            $this->add($data);
        }else{
            //如果存在，则累加签到次数
            if (D('CheckIn')->isContinueCheckIn($user_id)){
                $data['continue_count'] = $result['continue_count'] + 1;
            }else{
                //如果该用户不是连续签到
                $data['continue_count'] = 1;
            }
            $data['total_count']    = $result['total_count'] + 1;
            $this->where($map)->save($data);
        }
        return $data['continue_count'];
    }


}