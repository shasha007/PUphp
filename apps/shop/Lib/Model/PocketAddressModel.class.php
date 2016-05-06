<?php

class PocketAddressModel extends Model {

    //默认地址 |最近一次地址 或 第一次只有姓名手机
    public function defaultAddress($uid){
        $address = $this->where('uid='.$uid)->order('id desc')->limit(1)->findAll();
        if($address){
            $lastAdd = $address[0];
        }else{
            $lastAdd['id'] = 0;
            $lastAdd['name'] = getUserField($uid, 'realname');
            $lastAdd['tel'] = getUserField($uid, 'mobile');
        }
        return $lastAdd;
    }
    public function saveAddress($uid){
        $data['uid'] = $uid;
        $data['name'] = t($_POST['name']);
        if(empty($data['name'])){
            $this->error = '请填写姓名';
            return 0;
        }
        $data['tel'] = t($_POST['tel']);

        if(!isValidMobile($data['tel'])){
            $this->error = '请正确填写手机号码';
            return 0;
        }
        $data['identity'] = t($_POST['identity']);
        if(empty($data['identity'])){
            $this->error = '请填写身份证号码';
            return 0;
        }
        $data['zipCode'] = t($_POST['zipCode']);

        $data['address'] = t($_POST['address']);
        if(empty($data['address'])){
            $this->error = '请填写详细地址';
            return 0;
        }
        $oldId = intval($_POST['addId']);
        if($oldId){
            $oldAdd = $this->where("id=$oldId and uid=$uid")->find();
        }else{
            $oldAdd = false;
        }
        $newId = $oldId;
        if(!$oldAdd){
            $data['used'] = 0;
            $newId = $this->add($data);
        }elseif($oldAdd['name']!=$data['name'] || $oldAdd['tel']!=$data['tel'] || $oldAdd['identity']!=$data['identity']
                    || $oldAdd['zipCode']!=$data['zipCode']|| $oldAdd['address']!=$data['address']){
            if($oldAdd['used']){
                $data['used'] = 0;
                $newId = $this->add($data);
            }else{
                $this->where("id=$oldId")->save($data);
            }
        }
        return $newId;
    }

}

?>