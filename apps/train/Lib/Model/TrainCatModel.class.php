<?php

class TrainCatModel extends Model {

    public function getAllCat(){
        if ($cache = S('Cache_Train_Cat')) {
            return $cache;
        }
        $res = $this->order('id ASC')->findAll();
        S('Cache_Train_Cat', orderArray($res, 'id'));
        return S('Cache_Train_Cat');
    }

    public function getAllCatApi(){
        $res = $this->getAllCat();
        $api = array();
        foreach ($res as $v) {
            $api[] = $v;
        }
        return $api;
    }

    public function addCat($input){
        $data['name'] = preg_replace("/[ ]+/si", "", t($input['name']));
        $name = $this->where(array('name' => $data['name']))->getField('name');
        if (empty($data['name'])) {
            $this->error = '分类名称不能为空';
        }
        if ($name) {
            $this->error = '分类名称已存在，请重新填写';
        } else {
            if ($this->add($data)) {
                S('Cache_Train_Cat', null);
            } else {
                $this->error = '添加分类失败';
            }
        }
        if($this->error){
            return false;
        }
        return true;
    }

    public function editCat($input){
        $data['id'] = intval($input['id']);
        $data['name'] = t($input['name']);
        $data['name'] = preg_replace("/[ ]+/si", "", $input['name']);
        if (empty($data['name'])) {
            $this->error = '分类名称不能为空';
        }
        $name = $this->where(array('name' => $data['name']))->getField('name');
        if ($name) {
            $this->error = '分类名称已存在，请重新填写';
        } else {
            if ($this->save($data)) {
                S('Cache_Train_Cat', null);
            } else {
                $this->error = '修改分类失败';
            }
        }
        if($this->error){
            return false;
        }
        return true;
    }

    public function delCat($gid) {
        $map['id'] = array('IN', $gid);
        $has = M('train')->where(array('catId'=>$map['id']))->find();
        if($has){
            $this->error = '已有培训属于此分类，不可删除';
            return false;
        }
        $res = $this->where($map)->delete();
        if ($res) {
            S('Cache_Train_Cat', null);
            return true;
        } else {
            return false;
        }
    }
}

?>
