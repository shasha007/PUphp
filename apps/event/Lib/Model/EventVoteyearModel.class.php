<?php
    /**
     * EventImgModel
     * 活动的图片模型
     * @uses BaseModel
     * @package
     * @version $id$
     * @copyright 2009-2011 SamPeng
     * @author SamPeng <sampeng87@gmail.com>
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class EventVoteyearModel extends Model{
        public function doAdd($eid,$input){
            $year = $this->_calcYear($input);
            $sid1 = $this->_calcSid1($input);
            $this->doDel($eid);
            if($year||$sid1){
                $data['eid'] = $eid;
                $data['allowYear'] = $year;
                $data['allowSid1'] = $sid1;
                $this->add($data);
            }
        }
        private function _calcYear($input){
            $allowYear = t($input['allowYear']);
            $arr = explode(',', $allowYear);
            $res = array();
            $nowY = date('Y');
            foreach ($arr as $v) {
                $v = intval($v);
                if($v>2010 && $v<=$nowY && !in_array($v, $res)){
                    $res[] = $v;
                }
            }
            $year = '';
            if(!empty($res)){
                $year = implode(',', $res);
            }
            return $year;
        }
        private function _calcSid1($input){
            $allowYear = t($input['allowSid1']);
            $arr = explode(',', $allowYear);
            $res = array();
            $sid1s = M('school')->where('pid='.$input['sid'])->field("id")->findAll();
            $allSid1 = getSubByKey($sid1s, 'id');
            foreach ($arr as $v) {
                $v = intval($v);
                if(in_array($v, $allSid1) && !in_array($v, $res)){
                    $res[] = $v;
                }
            }
            $year = '';
            if(!empty($res) && count($sid1s)>count($res)){
                $year = implode(',', $res);
            }
            return $year;
        }
        public function doDel($eid){
            $this->where("eid=$eid")->delete();
        }
    }
