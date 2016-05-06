<?php
    class JobAccountWaterModel extends Model{
        //消费流水
        public function accountWaterList($map,$limit=10,$order='water_id'){
            $res = $this->where($map)->field('water_id,watertype,account,ctime')->order($order)->findPage($limit);
            if($res){
                foreach ($res as &$val) {
                    $val['watertype'] = $this->accountType($val['watertype']);
                }
                return $res;
            }else{
                return false;
            }
        }
        
        //消费类型
        public function accountType($id){
            $accountType = array('广告消费','发布职位消费','人才库消费','充值');
            if(isset($id)){
                return $accountType[$id];
            }else{
                return $accountType;
            }
        }
    }
?>