<?php

/**
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EcIdentifyModel extends Model {

    //添加申请
    public function doApply($sid, $user) {
        if ($sid != $user['sid']) {
            $this->error = '您无权申请该类别';
            return false;
        }
        $id = intval($_POST['fileId']);
        if($id<=0){
            $this->error = '申请表格ID错误';
            return false;
        }
        $file = M('EcFolder')->where("id=$id")->field('sid,is_folder,years,isDel,isRelease')->find();
        if(!$file || $file['sid']!=$sid || $file['is_folder']==1 || $file['isDel']==1 || $file['isRelease']==0
                || ($file['years']!='' && strpos($file['years'],$user['year'])===false)){
            $this->error = '申请表格不存在，或已删除';
            return false;
        }
        $data['fileId'] = $id;
        if(!$this->_checkTitle($data,$user['uid'])){
            return false;
        }
        //实践或获奖时间
        if(!$this->_checkStime($data)){
            return false;
        }
        //检查 审核人
        if(!$this->_checkAuditor($data,$sid)){
            return false;
        }
        //检查 申请提交资料
        if(!$this->_checkFieldSubmit($data,$id)){
            return false;
        }

        $data['uid'] = $user['uid'];
        $data['realname'] = $user['realname'];
        $data['sid'] = $user['sid'];
        $data['sid1'] = $user['sid1'];
        $data['year'] = $user['year'];
        $data['cTime'] = time();
        $res = $this->add($data);
        if(!$res){
            $this->error = '操作失败';
            return false;
        }
        return true;
    }
    //申请详情
    public function getApply($sid,$id){
        if($id<=0){
            return false;
        }
        $res = $this->where("sid=$sid and id=$id")->field('id,uid as applyUid,fileId,realname,sid1,stime,credit,title,opt,cTime')->find();
        if(!$res){
            return false;
        }
        $res['fileName'] = D('EcFolder')->getFileName($res['fileId']);
        $res['opt'] = unserialize($res['opt']);
        $inputs = M('EcInput')->where("fileId=".$res['fileId'])->order('inputOrder asc,id desc')->field('type,title,opt')->findAll();
        foreach ($res['opt'] as $k => $v) {
            $type = $inputs[$k]['type'];
            $row['title'] = $inputs[$k]['title'];
            $row['credit'] = 0;
            $row['res'] = $v;
            if($type==3){
                $sysOpt = unserialize($inputs[$k]['opt']);
                $row['res'] = $sysOpt[$v][0];
                $row['credit'] = $sysOpt[$v][1];
            }
            $res['inputs'][] = $row;
        }
        return $res;
    }
    //审核通过
    public function applyAudit($map,$mid,$credit,$creditName){
        if($credit<=0||$credit>200){
            $this->error = '请输入不大于200的数字，可带2位小数';
            return false;
        }
        $apply = $this->where($map)->field('id,credit,uid,title')->find();
        if(!$apply){
            $this->error = '申请不存在或已通过审核';
            return false;
        }
        if($credit > $apply['credit']){
            $this->error = '学分只能往低调';
            return false;
        }
        $data['id'] = $apply['id'];
        $data['credit'] = $credit;
        $data['finish'] = $mid;
        $data['status'] = 1;
        $data['rTime'] = time();
        $thisRes = $this->save($data);
        if(!$thisRes){
            $this->error = '操作失败，请稍后再试';
            return false;
        }
        $UserRes = D('User','home')->incCredit($apply['uid'],$map['sid'],$credit);
        if(!$UserRes){
            $data['id'] = $apply['id'];
            $data['credit'] = $apply['credit'];
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
        return true;
    }

    /**
     * 取消学分
     * @param $id      学分表ID
     * @return bool
     */
    public function cancelCredit($id){

        //取得用户信息
        $rs = $this->field('uid,sid,credit')->find($id);

        if (!$rs){
            $this->error = '该学分记录不存在';
            return false;
        }

        M()->startTrans();
        //删除学分申请记录
        $rs1 = $this->where(array('id'=>$id))->delete();
        //M('Test')->add(array('msg'=>$this->getLastSql()));
        //扣除对应的学分
        $rs2 = D('User','home')->decCredit($rs['uid'],$rs['sid'],$rs['credit']);
        //@todo:添加到通知

        if ($rs1 && $rs2){
            M()->commit();
            return true;
        }
        M()->rollback();
        return true;
    }

    //驳回
    public function doRejectApply($id, $reason,$creditName){
        $map['id'] = $id;
        $apply = $this->where($map)->field('id,uid,title,opt')->find();
        $res = $this->where($map)->delete();
        if (!$res) {
            $this->error = '操作失败，请稍后再试';
            return false;
        }
        //删除附件
        $opt = unserialize($apply['opt']);
        foreach ($opt as $v){
            if(is_array($v) && !empty($v)){
                foreach ($v as $w) {
                    model('Attach')->deleteAttach($w, true);
                }
            }
        }
        // 发送通知
        $notify_dao = service('Notify');
        $notify_data['creditName'] = $creditName;
        $notify_data['title'] = $apply['title'];
        $notify_data['reason'] = $reason;
        $notify_dao->sendIn($apply['uid'], 'event_ec_reject', $notify_data);
        return true;
    }
    private function _checkFieldSubmit(&$data,$fieldId) {
        $submit = array();
        $credit = 0;
        $prozent = array();
        $inputs = M('EcInput')->where("fileId=$fieldId")->order('inputOrder asc,id desc')->findAll();
        foreach($inputs as &$v){
            if($v['type']==1 || $v['type']==2){
                $key = 'in'.$v['id'];
                $input = t($_POST[$key]);
                if($v['must'] && !$input){
                    $this->error = '未填：'.$v['title'];
                    return false;
                }
            }elseif ($v['type']==3) {
                $key = 'in'.$v['id'];
                $input = intval($_POST[$key]);
                $opt = unserialize($v['opt']);
                if(!isset($opt[$input])){
                    $this->error = '选择错误：'.$v['title'];
                    return false;
                }
                $note = $opt[$input][1];
                if(substr($note, -1, 1)!='%'){
                    $credit += $note;
                }else{
                    $prozent[] = $note;
                }
            }elseif ($v['type']==4) {
                $key = 'attach';
                $input = $_POST[$key];
                if($v['must'] && empty($input)){
                    $this->error = '未上传：'.$v['title'];
                    return false;
                }
            }
            $submit[] = $input;
        }
        foreach ($prozent as $v) {
            $credit = ($credit*$v)/100;
        }
        $data['opt'] = serialize($submit);
        $data['credit'] = $credit;
        return true;
    }

    private function _checkTitle(&$data,$uid) {
        $title = t($_POST['title']);
        if (!$title) {
            $this->error = '名称 不能为空';
            return false;
        }
        $data['title'] = $title;
        $fileId = $data['fileId'];
        $hasTitle = $this->where("fileId=$fileId and uid=$uid and title='$title'")->count();
        if($hasTitle){
            $this->error = '您已申请，不可重复申请';
            return false;
        }
        return true;
    }
    private function _checkStime(&$data) {
        $stime = intval($_POST['stime']);
        if (!$this->_validStime($stime)) {
            $this->error = '实践或获奖时间错误';
            return false;
        }
        $data['stime'] = $stime;
        return true;
    }
    private function _checkAuditor(&$data,$sid) {
        $audit = intval($_POST['audit']);
        if(!$audit){
            $this->error = '请选择审核人';
            return false;
        }
        if(!D('EcAuditor')->hasAuditor($sid,$audit)){
            $this->error = '审核人错误';
            return false;
        }
        $data['audit'] = $audit;
        return true;
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

}

?>