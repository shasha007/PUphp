<?php

/**
 * EcApplyModel
 * 学分申请
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EcApplyModel extends Model {

    public function doApply($sid,$user,$input) {
        if($sid!=$user['sid']){
            $this->error = '您无权申请该类别';
            return false;
        }
        //检查 type
        $type = intval($input['type']);
        if(!$type){
            $this->error = '请选择申请类别';
            return false;
        }
        if($sid==480){
            if(!in_array($type, array(10,11,12))){
                $this->error = '申请类别错误';
                return false;
            }
        }
        $data['type'] = $type;
        //检查 title
        if($type!=12){
            $title = t($input['title']);
            if(!$title){
                $this->error = '名称 不能为空';
                return false;
            }
            $data['title'] = $title;
        }
        //检查 audit
        $audit = intval($input['audit']);
        if(!$audit){
            $this->error = '请选择审核人';
            return false;
        }
        $audits = D('EcUtype')->auditById($sid,$type);
        $auditUids = getSubByKey($audits, 'uid');
        if(!in_array($audit, $auditUids)){
            $this->error = '审核人错误';
            return false;
        }
        $data['audit'] = $audit;
        //检查 type是否该校
        $ecType = D('EcType')->getTypeById($sid,$type);
        if(!$ecType){
            $this->error = '申请类别错误';
            return false;
        }
        if($sid==480){
            //实践或获奖时间
            $stime = intval($input['stime']);
            if(!$this->_validStime($stime)){
                $this->error = '实践或获奖时间错误';
                return false;
            }
            $data['stime'] = $stime;
            $calc = $this->_calc480($type,$input,$user['uid'],$sid);
            if($calc===false){
                return false;
            }
            if($calc['credit']<=0){
                $this->error = '总分为0，请勾选申请细则';
                return false;
            }
            $data['credit'] = $calc['credit'];
            if($type==12){
                $data['title'] = $calc['title'];
            }
        }
        if(isset($input['description'])){
            $description = t($input['description'],'nl');
            $data['description'] = $description;
        }
        if(isset($input['imgs'])&&!empty($input['imgs'])&&is_array($input['imgs'])){
            $data['imgs'] = serialize($input['imgs']);
        }
        $hasAttach = false;
        if(isset($input['attach'])&&!empty($input['attach'])&&is_array($input['attach'])){
            $hasAttach = true;
            $data['attachs'] = serialize($input['attach']);
        }
        $data['sid'] = $sid;
        $data['sid1'] = $user['sid1'];
        $data['uid'] = $user['uid'];
        $data['year'] = $user['year'];
        $data['realname'] = $user['realname'];
        $data['cTime'] = time();
        $res = $this->add($data);
        if($res){
            $reliveIds = array();
            if($hasAttach){
                $reliveIds = $input['attach'];
            }
            if(isset($input['attids'])&&!empty($input['attids'])&&is_array($input['attids'])){
                $reliveIds = array_merge($reliveIds, $input['attids']);
            }
            if(!empty($reliveIds)){
                model('Attach')->reliveAttach($reliveIds);
            }
            if($sid==480){
                $gdData = $calc['data'];
                $gdData['apply_id'] = $res;
                M('ec_applygd')->add($gdData);
                if($gdData['has_zs']==1){
                    Mmc('Ecapply_haszs_'.$user['uid'],1,0,3600);
                }
            }
            return true;
        }
        $this->error = '申请失败';
        return false;
    }

    private function _validStime($stime){
        if(strlen($stime)!=5){
            return false;
        }
        $sem = substr($stime,4,1);
        if($sem!=0 && $sem!=1){
            return false;
        }
        $nyear = date('Y');
        $year = substr($stime,0,4);
        if(($nyear-$year)>3 || $nyear<$year){
            return false;
        }
        return true;
    }

    private function _calc480($type,$input,$uid,$sid){
        $gd = intval($input['gd']);
        $credit = 0;
        $res = array();
        $res['data']['gd'] = $gd;
        $func = $type.'_'.$gd;
        $gdSelectFunc = 'gdSelect'.$func;
        if(!function_exists($gdSelectFunc)){
            $this->error('无效申请类型');
        }
        $select = $gdSelectFunc();
        $gdRadioFunc = 'gdRadio'.$func;
        $opt = '';
        $count = count($select);
        $half = 0;
        foreach ($select as $k => $v) {
            $k += 1;
            $ink = 'gd'.$k;
            $inv = intval($input[$ink]);
            if($inv<=0){
                $inv = 1;
            }
            $gdRadio = $gdRadioFunc($k,$inv);
            if($gdRadio === false){
                $this->error = $v['title'] . '参数错误';
                return false;
            }
            $opt .= $inv;
            if($k<$count){
                $opt .= ',';
            }
            $credit += $gdRadio['credit'];
            //是否要减半
            if($v['half'] && $inv==2){
                $half = 1;
            }
            //证书名称
            if($gdRadio['input']){
                if(hasZs($uid)){
                    $this->error = '各类证书不累加，只能申请一次。您已申请过了';
                    return false;
                }
                $zs_name = t($input[$gdRadio['input']]);
                if($zs_name==''){
                    $this->error = '请输入证书名称';
                    return false;
                }
                $res['data']['has_zs'] = 1;
                $res['data']['zs_name'] = $zs_name;
            }
            if($type==12){
                $res['title'] = $v['title'];
                if($gd==2){
                    $res['title'] = '校大学生艺术团服务';
                }elseif($gd==5){
                    $res['title'] = '证书';
                }
            }
        }
        $res['data']['opt'] = $opt;
        $res['data']['uid'] = $uid;
        $res['data']['sid'] = $sid;
        if($half==1){
            $credit = $credit/2;
        }
        $res['credit'] = $credit;
        return $res;
    }

    public function getApplyList($map,$order,$limit){
        return  $this->where($map)->field('id,title,type,sid1,uid,audit,realname,credit,finish,cTime,rTime')->order($order)->findPage($limit);
    }

    public function getApply($id){
        if($id<=0){
            return false;
        }
        $res = $this->where('id='.$id)->find();
        if(!$res){
            return false;
        }
        $res['imgs'] = unserialize($res['imgs']);
        $res['attachs'] = unserialize($res['attachs']);
        return $res;
    }

    public function apply($map,$mid,$credit,$creditName){
        if($credit<=0||$credit>200){
            $this->error = '请输入不大于200的数字，可带2位小数';
            return false;
        }
        $apply = $this->where($map)->field('id,type,uid,title')->find();
        if(!$apply){
            $this->error = '申请不存在或已通过审核';
            return false;
        }
        $data['id'] = $apply['id'];
        $data['credit'] = $credit;
        $data['finish'] = $mid;
        $data['status'] = 1;
        $data['rTime'] = time();
        $res = $this->save($data);
        if(!$res){
            $this->error = '操作失败，请稍后再试';
            return false;
        }
        $res = D('User','home')->incCredit($apply['uid'],$map['sid'],$credit);
        if(!$res){
            $data['id'] = $apply['id'];
            $data['credit'] = 0;
            $data['finish'] = 0;
            $data['status'] = 0;
            $this->save($data);
            $this->error = '操作失败，请稍后再试';
            return false;
        }
        $notify_dao = service('Notify');
        $notify_data['title'] = $apply['title'];
        $notify_data['credit'] = $credit;
        $notify_data['creditName'] = $creditName;
        $notify_dao->sendIn($apply['uid'], 'event_ec_pass', $notify_data);
        if($apply['type']==10||$apply['type']==11||$apply['type']==12){
            Mmc('AdminCredit_getGd_'.$apply['type'].'_'.$apply['uid'],null);
        }
        return true;
    }

    //驳回
    public function doRejectApply($id, $reason,$creditName){
        $map['id'] = $id;
        $apply = $this->where($map)->field('id,type,uid,title,imgs,attachs')->find();
        $res = $this->where($map)->delete();
        if (!$res) {
            $this->error = '操作失败，请稍后再试';
            return false;
        }
        //删除图片文件
        $images = unserialize($apply['imgs']);
        foreach ($images as $value) {
            tsDelFile($value);
        }
        //删除附件
        $attachs = unserialize($apply['attachs']);
        foreach ($attachs as $value) {
            model('Attach')->deleteAttach($value, true);
        }
        // 发送通知
        $notify_dao = service('Notify');
        $notify_data['creditName'] = $creditName;
        $notify_data['title'] = $apply['title'];
        $notify_data['reason'] = $reason;
        $notify_dao->sendIn($apply['uid'], 'event_ec_reject', $notify_data);
        //删除ec_applygd
        if($apply['type']==10||$apply['type']==11||$apply['type']==12){
            M('ec_applygd')->where('apply_id='.$apply['id'])->delete();
        }
        return true;
    }

}

?>