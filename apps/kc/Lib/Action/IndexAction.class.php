<?php

class IndexAction extends Action {

    public function _initialize() {

    }

    public function index() {
        $daoKc = D('Kc');
        $kc = $daoKc->where(array('uid'=>$this->mid))->findAll();
        foreach ($kc as $value) {
            $kcids[] = $value['id'];
            $kcs[$value['id']] = $value;
        }
        $optDao = D('KcOpt');
        $map['kcid'] = array('in', $kcids);
        $ops = $optDao->where($map)->findAll();
        foreach ($ops as $value) {
            $weekday = $value['weekday'];
            for($class=$value['from'];$class<=$value['to'];$class++){
                $block = array();
                $block['id'] = $value['kcid'];
                $block['title'] = $kcs[$value['kcid']]['courseName'];
                if($value['occur'] == '0'){
                    $block['title'] = '(单)'.$block['title'];
                }elseif($value['occur'] == '2'){
                    $block['title'] = '(双)'.$block['title'];
                }
                $list[$class+1][$weekday][] = $block;
            }
        }
        $this->assign('list', $list);
        $this->display();
    }

    public function add() {
        $this->display();
    }

    public function doEdit() {
        $oldId = intval($_POST['id']);
        $daoKc = D('Kc');
        $withoutKc=array();
        if($oldId>0){
            if(!$daoKc->where('id = '.$oldId)->find()){
                $this->error('没找到课程，请重试！');
            }
            $kcMap['id'] = $oldId;
            $withoutKc=array($oldId);
        }
        $kcMap['uid'] = $this->mid;
        $kcMap['courseName'] = t($_POST['courseName']);
        if ($kcMap['courseName'] == '') {
            $this->error('请输入课程名');
        }
        if (mb_strlen($kcMap['courseName'], 'UTF8') > 20) {
            $this->error('课程名最大20个字！');
        }
        $kcMap['teacher'] = t($_POST['teacher']);
        if (mb_strlen($kcMap['teacher'], 'UTF8') > 20) {
            $this->error('老师姓名最大20个字！');
        }
        $kcMap['begin'] = intval($_POST['begin']);
        $kcMap['end'] = intval($_POST['end']);
        if ($kcMap['begin'] < 1 || $kcMap['end'] < 1 || $kcMap['begin'] > 25 || $kcMap['end'] > 25 || $kcMap['begin'] > $kcMap['end']) {
            $this->error('起始，结束周输入不正确，请检查');
        }
        $optDao = D('KcOpt');
        $doubleId = $optDao->getDoubleKc($this->mid, $_POST['occurs'], $_POST['weekdays'], $_POST['froms'], $_POST['tos'],$withoutKc);
        if ($doubleId) {
            $this->error('与其它课程有时间冲突!');
        }
        $kcMap['cTime'] = time();
        $successMsg = '添加成功';
        if($oldId){
            $successMsg = '修改成功';
            $id = $oldId;
            $daoKc->save($kcMap);
            $optDao->where('kcid = '.$id)->delete();
        }else{
            $id = $daoKc->add($kcMap);
            if(!$id){
                $this->error('系统出错，请稍后再试!');
            }
        }
        $optMap['kcid'] = $id;
        $optMap['uid'] = $this->mid;
        foreach ($_POST['occurs'] as $key => $value) {
            $optMap['occur'] = intval($value);
            $optMap['weekday'] = intval($_POST['weekdays'][$key]);
            $optMap['from'] = intval($_POST['froms'][$key]);
            $optMap['to'] = intval($_POST['tos'][$key]);
            $optMap['addr'] = t($_POST['addrs'][$key]);
            $optDao->add($optMap);
        }
        $this->success($successMsg);
    }

    public function showDialog() {
        $ids = explode(',', t($_REQUEST ['ids']));
        $daoKc = D('Kc');
        $map['id'] = array('in',$ids);
        $list = $daoKc->where($map)->findAll();
        $optDao = D('KcOpt');
        foreach ($list as $key => $value) {
            $ops = $optDao->where(array('kcid' =>$value['id']))->findAll();
            $list[$key]['ops'] = $ops;
        }
        $this->assign('list', $list);
        $conf['occurs'] = array('单周','每周','双周');
        $conf['weekdays'] = array('周日','周一','周二','周三','周四','周五','周六');
        $this->assign($conf);
        $this->display();
    }

    public function doDelete(){
        $id = intval($_POST['id']);
        if(!D('Kc')->where('id = '.$id)->delete()){
            $this->error('删除失败，请重试！');
        }
        if(!D('KcOpt')->where('kcid = '.$id)->delete()){
            $this->error('删除失败，请重试！');
        }
        $this->success('删除成功！');
    }

    public function edit(){
        $id = intval($_REQUEST['id']);
        $daoKc = D('Kc');
        $kc = $daoKc->where('id = '.$id)->find();
        if(!$kc){
            $this->error('找不到该课程！');
        }
        $daoOpt = D('KcOpt');
        $opt = $daoOpt->where('kcid = '.$id)->findAll();
        $this->assign('kc',$kc);
        $this->assign('opt',$opt);
        $this->display();
    }

    public function timeAjax() {
        $html = '<div class="courseTimes">
            <div class="add_week">
            <select name="occurs[]" class="sr2 fl">
                <option value="0">单周</option>
                <option selected value="1">每周</option>
                <option value="2">双周</option>
            </select>
            <select name="weekdays[]" class="sr2 fl">
                <option value="1">周一</option>
                <option value="2">周二</option>
                <option value="3">周三</option>
                <option value="4">周四</option>
                <option value="5">周五</option>
                <option value="6">周六</option>
                <option value="0">周日</option>
            </select>
            <select name="froms[]" onchange="if(parseInt(jQuery(this).next().next().val())<this.value)jQuery(this).next().next().val(this.value);" class="sr2 fl">
                <option value="0">第1节</option>
                <option value="1">第2节</option>
                <option value="2">第3节</option>
                <option value="3">第4节</option>
                <option value="4">第5节</option>
                <option value="5">第6节</option>
                <option value="6">第7节</option>
                <option value="7">第8节</option>
                <option value="8">第9节</option>
                <option value="9">第10节</option>
                <option value="10">第11节</option>
                <option value="11">第12节</option>
                <option value="12">第13节</option>
                <option value="13">第14节</option>
                <option value="14">第15节</option>
                <option value="15">第16节</option>
            </select>
            <span class="fl">到</span>
            <select name="tos[]" onchange="if(parseInt(jQuery(this).prev().prev().val())>this.value)jQuery(this).val(jQuery(this).prev().prev().val());" class="sr2 fl">
                <option value="0">第1节</option>
                <option value="1">第2节</option>
                <option value="2">第3节</option>
                <option value="3">第4节</option>
                <option value="4">第5节</option>
                <option value="5">第6节</option>
                <option value="6">第7节</option>
                <option value="7">第8节</option>
                <option value="8">第9节</option>
                <option value="9">第10节</option>
                <option value="10">第11节</option>
                <option value="11">第12节</option>
                <option value="12">第13节</option>
                <option value="13">第14节</option>
                <option value="14">第15节</option>
                <option value="15">第16节</option>
            </select>
            <span class="red"><a href="javascript:;" onclick="removeTime(this);">删除</a></span>
        </div>
        <div class="add_place"><input type="text" name="addrs[]" class="add_input" placeholder="请输入上课地点" maxlength="50"> 可选
        </div>';
        echo $html;
    }

}
