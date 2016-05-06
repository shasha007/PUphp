<?php

/*
 * 招聘信息学校绑定模型
 * 
 */
class JobPostidSidModel extends Model{
    
    /**
     * $sids 学校id
     * $postid 招聘信息id
     */
    public function PostidBySid($postid,$sids){
        if(!is_array($sids)){
            $sids = explode(',', $sids);
        }
        //所有学校数量
        $all = M('School')->where('pid=0')->count();
        if($all +1 == count($sids)){
              $data['postid'] = $postid;
              $data['sid'] = -100;//-100代表所有学校
              $this->add($data);
        }else{
            foreach ($sids as $value) {
            if($value){
                $data['postid'] = $postid;
                $data['sid'] = $value;
                $this->add($data);
            }
        }
        }

    }
}
?>
