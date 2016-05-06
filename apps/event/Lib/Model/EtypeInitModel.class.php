<?php

/**
 * EtypeInitModel
 * 活动分类自定义生效
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EtypeInitModel extends Model {

    public function isInited($sid){
        $inited = $this->where("sid=$sid")->field('sid')->find();
        if($inited){
            return true;
        }else{
            return false;
        }
    }
    public function addEtypeInit($sid,$uid){
        $data['sid'] = $sid;
        $data['uid'] = $uid;
        $data['ctime'] = time();
        return $this->add($data);
    }

}

?>