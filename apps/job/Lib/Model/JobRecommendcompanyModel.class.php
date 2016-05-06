<?php
    /**
     * 推荐企业(自己推荐)
     * type 0 自己 1政府
     * **/
    class JobRecommendcompanyModel extends Model{
        
        //推荐企业列表
        public function companyList($map,$limit,$order='r.recommend_id DESC'){
            $db_prefix = C('DB_PREFIX');
            $res = $this->table("{$db_prefix}job_recommendcompany as r")
                    ->join("{$db_prefix}job_enterprise_info as e on e.eid=r.eid")
                    ->where($map)
                    ->field('r.recommend_id as recommend_id,r.eid as eid,r.endtime as endtime,r.type as type,e.logo_pic as logo_pic')
                    ->order($order)
                    ->findPage($limit);
            if($res){
                   foreach ($res['data'] as &$val) {
                   $val['logo_pics'] = '';
                   $attach = getAttach($val['logo_pic']);
                   $file = $attach['savepath'] . $attach['savename'];
                   $val['logo_pics'] = tsMakeThumbUp($file, 60, 60, 'no');

              }
                return $res;
            }else{
                return false;
            }
        }
        
        /**
         * 返回推荐企业的eid
         * $type false 未过期 true 全部
         */
        public function getRecommendEid($type=false){
            if(!$type){
                //判断时间是否过期
                $map['endtime'] = array('elt',time());
            }
            $res = $this->where($map)->field('eid')->select();
            if($res){
                $arr = array();
                foreach ($res as $value) {
                    $arr[] = $value['eid'];
                }
                return $arr;
            }else{
                return false;
            }
        }
        
    }
?>
