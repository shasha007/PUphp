<?php

class JobLocalpolicyModel extends Model{
    
    public function polictList($map,$limit=10,$order='id DESC'){
        $info = $this->where($map)->field('id,title,content,ctime,area,author')->order($order)->findPage($limit);
        if($info){
            return $info;
        }else{
            return false;
        }
    }
}
?>
