<?php

class TrainAreaModel extends Model {

    public function getAllArea($pid=0){
        if ($cache = S('Cache_Train_Area_'.$pid)) {
            return $cache;
        }
        $res = $this->where('pid='.$pid)->findAll();
        S('Cache_Train_Area_'.$pid, $res);
        return $res;
    }

    public function areaArr(){
        if ($cache = S('Cache_Train_Area')) {
            return $cache;
        }
        $res = $this->findAll();
        S('Cache_Train_Area', orderArray($res, 'id'));
        return S('Cache_Train_Area');
    }

    public function getAreaById($id){
        $areas = $this->areaArr();
        if(isset($areas[$id])){
            return $areas[$id];
        }
        return array();
    }

    public function addArea($input){
        if (empty($input ['title'])) {
            $this->error = '名称不能为空';
            return false;
        }
        $cate['title'] = t($input ['title']);
        $cate['pid'] = intval($input ['pid']); //1级分类
        if ($cate ['pid'] > 0) {
            $pid = $this->getField('pid', 'id =' . $cate ['pid']);
            if ($pid > 0){
                $this->error = '目前只能添加一级城市';
                return false;
            }
        }
        $res = $this->add($cate);
        if ($res) {
            S('Cache_Train_Area_'.$cate['pid'], null);
            S('Cache_Train_Area', null);
            return $res;
        }
        $this->error = '操作失败';
        return false;
    }

    public function editArea($input){
        $id = intval($input ['id']);
        $title = t($input ['title']);
        if ($title == '') {
            $this->error = '名称不能为空！';
            return false;
        }
        $res = $this->setField('title', $title, 'id=' . $id);
        if ($res) {
            S('Cache_Train_Area', null);
            $areas = $this->areaArr();
            $pid = $areas[$id]['pid'];
            S('Cache_Train_Area_'.$pid, null);
            return true;
        } else {
            $this->error = '操作失败！';
            return false;
        }
    }

    public function delArea($gid) {
        $map['id'] = array('IN', $gid);
        $has = M('train')->where(array('catId'=>$map['id']))->find();
        if($has){
            $this->error = '已有培训属于此分类，不可删除';
            return false;
        }
        $res = $this->where($map)->delete();
        if ($res) {
            S('Cache_Train_Area', null);
            return true;
        } else {
            return false;
        }
    }
}

?>
