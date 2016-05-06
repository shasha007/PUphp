<?php

class GrowCategroyModel extends Model {

    public function __getCategory($pid = -1) {
        if ($pid != -1) {
            $categorys = $this->where("pid='$pid'")->order('display_order ASC, id ASC')->findAll();
        } else {
            if ($cache = S('Cache_Grow_Category')) {
                return $cache;
            }
            $categorys = $this->order('display_order ASC, id ASC')->findAll();
        }
        foreach ($categorys as $v) {
            $categorys_[$v['id']]['title'] = $v['name'];
            $categorys_[$v['id']]['pid'] = $v['pid'];
        }
        if($pid == -1){
            S('Cache_Grow_Category', $categorys_);
        }
        return $categorys_;
    }
    //所有分类二维数组
    public function apiCatList(){
        $list=$this->where('pid=0')->field('id,name')->select();
        foreach($list as &$val){
            $result=$this->where('pid='.$val['id'])->field('id,name')->select();
            if($result){
                $val['child']=$result;
            }else{
                $val['child']=array();
            }
        }
        return $list;
    }
    //获取分类名
    public function getName($id){
        $name = $this->where('id='.$id)->field('name')->find();
        return $name['name'];
    }
}

?>
