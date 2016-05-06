<?php

/**
 * JfdhModel
 * 兑换历史记录
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EcTypeModel extends Model {

    public function getEcType($sid) {
        $cache = S('Cache_Ec_Type_'.$sid);
        if ($cache) {
            return $cache;
        }
        $param = $this->where('isDel=0 and sid=' . $sid)->findAll();
        if (!$param) {
            $param = array();
        }
        foreach ($param as &$v) {
            $audits = D('EcUtype','event')->auditById($sid,$v['id']);
            $v['audit'] = count($audits);
        }
        S('Cache_Ec_Type_'.$sid, $param);
        return $param;
    }
    public function getTypeById($sid,$id) {
        $list = $this->getEcType($sid);
        $res = array();
        foreach ($list as $v) {
            if($v['id']==$id){
                $res = $v;
            }
        }
        return $res;
    }

    public function editEcType($data){
        $sid = $data['sid'];
        if(isset($data['id'])){
            $res = $this->save($data);
        }else{
            $res = $this->add($data);
        }
        if($res){
            S('Cache_Ec_Type_'.$sid, null);
            return true;;
        }
        $this->error = '保存失败';
        return false;
    }

    public function delEcType($id,$sid){
        if(!$id){
            $this->error = '类别不存在或已删除';
            return false;
        }
        $map['id'] = $id;
        $map['sid'] = $sid;
        $map['isDel'] = 0;
        $param = $this->where($map)->field('id')->find();
        if(!$param){
            $this->error = '类别不存在或已删除';
            return false;
        }
        $res = $this->setField('isDel', 1, 'id='.$id);
        if($res){
            S('Cache_Ec_Type_'.$sid, null);
            return true;;
        }
        $this->error = '删除失败';
        return false;
    }

}

?>