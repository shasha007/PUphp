<?php

class TrainOrgModel extends Model {

    public function getAllOrg(){
        if ($cache = S('Cache_Train_Org')) {
            return $cache;
        }
        $res = $this->order('id ASC')->findAll();
        S('Cache_Train_Org', orderArray($res, 'id'));
        return S('Cache_Train_Org');
    }

    public function getAllOrgApi(){
        $res = $this->getAllOrg();
        $api = array();
        foreach ($res as $v) {
            $api[] = $v;
        }
        return $api;
    }

    public function addOrg($input){
        $isnull = preg_replace("/[ ]+/si", "", t($input['name']));
        if (!$isnull) {
            $this->error('机构名称不能为空！');
        }
        if (empty($_FILES['logo']['name'])) {
            $this->error = '请上传机构logo';
            return false;
        }
        $name = $this->where(array('name' => $isnull))->getField('name');
        if ($name) {
            $this->error = '机构名称已存在，请重新填写！';
            return false;
        } else {
            //得到上传的图片
            $images = tsUploadImg();
            if (!$images['status']) {
                $this->error = $images['info'];
                return false;
            }
            $data['name'] = $isnull;
            $data['link'] = t($input['link']);
            $data['description'] = $input['description'];
            $data['logo'] = $images['info'][0]['savepath'].$images['info'][0]['savename'];
            $result = $this->add($data);
            if ($result) {
                S('Cache_Train_Org', null);
                return true;
            } else {
                $this->error = '添加机构失败';
                return false;
            }
        }
    }

    public function editOrg($input){
        $data['id'] = intval($input['id']);
        $data['name'] = preg_replace("/[ ]+/si", "", t($input['name']));
        $data['link'] = t($input['link']);
        $data['eday'] = t($input['eday']);
        $data['description'] = $input['description'];
        if (empty($data['name'])) {
            $this->error = '机构名称不能为空';
            return false;
        }
        //得到上传的图片
        $images = tsUploadImg();
        if (!$images['status'] && $images['info'] != '没有选择上传文件') {
            $this->error = $images['info'];
            return false;
        }
        if ($images['status']) {
            $data['logo'] = $images['info'][0]['savepath'].$images['info'][0]['savename'];
        }
        $result = $this->save($data);
        if ($result) {
            if ($images['status']) {
                //删除旧的
                $orgs = $this->getAllOrg();
                tsDelFile($orgs[$data['id']]['logo']);
            }
            //更新课程签约时间
            M('train')->setField('eday', $data['eday'], 'orgId='.$data['id']);
            S('Cache_Train_Org', null);
            return true;
        } else {
            $this->error = '修改机构失败';
            return false;
        }
    }

    public function delOrg($gid) {
        $map['id'] = array('IN', $gid);
        $has = M('train')->where(array('orgId'=>$map['id']))->find();
        if($has){
            $this->error = '已有培训属于此机构，不可删除';
            return false;
        }
        $orgs = $this->getAllOrg();
        $res = $this->where($map)->delete();
        if ($res) {
            $ids = explode(',', $gid);
            foreach ($ids as $id) {
                tsDelFile($orgs[$id]['logo']);
            }
            S('Cache_Train_Org', null);
            return true;
        } else {
            return false;
        }
    }
}

?>
