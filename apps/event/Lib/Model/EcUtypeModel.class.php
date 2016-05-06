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
class EcUtypeModel extends Model {

    //分配权限
    public function changeUType($uid,$types,$sid,$realname='',$checkCanCredit=false) {
//        $old = $this->where('uid='.$uid)->field('type')->findAll();
//        foreach($old as $v){
//            M('ec_type')->setDec('audit','id='.$v['type']);
//        }
        $data = array();
        $data['uid'] = $uid;
        $data['sid'] = $sid;
        if($realname==''){
            $realname = M('user')->getField('realname', 'uid='.$uid);
        }
        $data['realname'] = $realname;
        $canCredit = false;
        foreach ($types as $v) {
            $data['type'] = intval($v);
            if($data['type']!=0){
                $res = $this->add($data);
                if($res){
//                    M('ec_type')->setInc('audit','id='.$data['type']);
                    $canCredit = true;
                }
            }
        }
        if($checkCanCredit){
            if($canCredit){
                $res = D('User', 'home')->upField($uid,'can_credit', '1');
            }else{
                $res = D('User', 'home')->upField($uid,'can_credit', '0');
            }
        }
        S('Cache_Ec_Utype_'.$sid, null);
        S('Cache_Ec_Type_'.$sid, null);
    }

    //添加审核人
    public function addUType($uid,$types,$sid,$realname='') {
        if(!is_array($types)){
            $types = array($types);
        }
        $data['uid'] = $uid;
        $data['sid'] = $sid;
        if($realname==''){
            $realname = M('user')->getField('realname', 'uid='.$uid);
        }
        $data['realname'] = $realname;
        $canCredit = false;
        foreach ($types as $v) {
            $data['type'] = intval($v);
            if($data['type']!=0){
                $res = $this->add($data);
                if($res){
//                    M('ec_type')->setInc('audit','id='.$data['type']);
                    $canCredit = true;
                }
            }
        }
        if($canCredit){
            $res = D('User', 'home')->upField($uid,'can_credit', '1');
        }
        S('Cache_Ec_Utype_'.$sid, null);
        S('Cache_Ec_Type_'.$sid, null);
    }

    //删除审核人
    public function delUType($uid,$types,$sid) {
        if(!is_array($types)){
            $types = array($types);
        }
        $data['uid'] = $uid;
        $done = false;
        foreach ($types as $v) {
            $data['type'] = intval($v);
            if($data['type']!=0){
                $res = $this->where($data)->delete();
                if($res){
//                    M('ec_type')->setDec('audit','id='.$data['type']);
                    $done = true;
                }
            }
        }
        if($done){
            $has = $this->where('uid='.$uid)->field('type')->find();
            if(!$has){
                $res = D('User', 'home')->upField($uid,'can_credit', '0');
            }
        }
        S('Cache_Ec_Utype_'.$sid, null);
        S('Cache_Ec_Type_'.$sid, null);
    }

    public function getBySid($sid){
        return $this->where('sid='.$sid)->field('uid,type,realname')->findAll();
    }

    //审核人列表by sid
    public function applyAudit($sid){
        $cache = S('Cache_Ec_Utype_'.$sid);
        if ($cache) {
            return $cache;
        }
        $ecUsers = $this->getBySid($sid);
        $utype = array();
        foreach($ecUsers as $ecUser){
            $user = M('user')->where('uid='.$ecUser['uid'])->field('sid1')->find();
            if($user){
                $row['uid'] = $ecUser['uid'];
                $row['realname'] = $ecUser['realname'];
                if($user['sid1']){
                    $row['school'] = M('school')->getField('title', 'id='.$user['sid1']);
                }else{
                    $row['school'] = '校团委';
                }
                $utype[$ecUser['type']][] = $row;
            }
        }
        S('Cache_Ec_Utype_'.$sid, $utype);
        return $utype;
    }
    //审核人列表by sid
    public function auditById($sid,$id){
        $audits = $this->applyAudit($sid);
        $res = array();
        if(isset($audits[$id])){
            $res = $audits[$id];
        }
        return $res;
    }
}

?>