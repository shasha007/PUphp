<?php

/**
 * JfAction
 * 积分商城管理
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class ConfigAction extends TeacherAction {

    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        if ($this->rights['can_admin'] != 1) {
            $this->assign('jumpUrl', U('event/Readme/index'));
            $this->error('您没有权限管理全局配置！');
        }
    }

    public function webconfig() {
        $config = D('SchoolWeb','event')->getConfigCache($this->school['id']);
        $this->assign($config);
        $this->display();
    }

    public function orga() {
        $this->assign('list', D('SchoolOrga')->getAll($this->school['id']));
        $this->display();
    }

    public function editOrga() {
        $id = intval($_GET['id']);
        if ($id) {
            $orga = D('SchoolOrga')->where("id={$id} AND sid={$this->school['id']}")->find();
            $this->assign($orga);
        }
        $this->display();
    }
    
    //编辑或增加标签
    public function editTag(){
        $id = intval($_GET['id']);
        if ($id) {
            $tag = D('EventTag')->where("id={$id} AND sid={$this->school['id']}")->find();
            $this->assign($tag);
        }
        $this->display();
    }

    public function doAddOrga() {
        $isnull = preg_replace("/[ ]+/si", "", t($_POST['title']));
        $type = D('SchoolOrga');
        if (empty($isnull)) {
            echo -2;
        }
        $map['sid'] = $this->school['id'];
        $map['title'] = $isnull;
        $title = $type->where($map)->getField('title');
        $cat = intval($_POST['cat'])?intval($_POST['cat']):1;
        if ($title !== null) {
            echo 0;
        } else {
            if ($type->addOrga($this->school['id'], $isnull,$cat)) {
                echo 1;
            } else {
                echo -1;
            }
        }
    }
    
    //编辑标签处理
    public function doEditTag(){
        $dao = D('EventTag');
        $res = $dao->editEventTag($_POST['title'],$this->sid,$_POST['id']);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error($dao->getError());
        }
    }
    public function doEditOrga() {
        $_POST['id'] = intval($_POST['id']);
        $_POST['title'] = preg_replace("/[ ]+/si", "", t($_POST['title']));
        if (empty($_POST['title'])) {
            echo -2;
        }
        $type = D('SchoolOrga');
        $map['title'] = $_POST['title'];
        $map['sid'] = $this->sid;
        $map['id'] = array('neq', $_POST['id']);
        $title = $type->where($map)->getField('title');
        if ($title !== null) {
            echo 0; //分类名称重复
        } else {
            if ($type->editOrga($this->school['id'], $_POST['id'], $_POST['title'],intval($_POST['cat']))) {
                echo 1; //更新成功
            } else {
                echo -1;
            }
        }
    }
    
    //删除校级组织
    public function doDeleteOrga() {
        $type = D('SchoolOrga');
        if ($result = $type->deleteOrga($this->school['id'], t($_POST['id']))) {
            if (!strpos($_POST['id'], ",")) {
                echo 2;            //说明只是删除一个
            } else {
                echo 1;            //删除多个
            }
        } else {
            echo $result;
        }
    }

    public function doDeleteTag() {
        $tag = D('EventTag');
        $map['sid'] = $this->school['id'];
        $map['id'] = array('in', t($_POST['id']));
        $data['isdel'] = '1';
        if ($result = $tag->where($map)->save($data)) {
            if (!strpos($_POST['id'], ",")) {
                echo 2;            //说明只是删除一个
            } else {
                echo 1;            //删除多个
            }
        } else {
            echo $result;
        }
    }

    public function doOrgaOrder() {
        $_POST['id'] = intval($_POST['id']);
        $_POST['baseid'] = intval($_POST['baseid']);
        if ($result = D('SchoolOrga')->changeOrder($this->school['id'], $_POST['id'], $_POST['baseid'])) {
            echo 1; //更新成功
        } else {
            echo 0;
        }
    }

    public function doWebconfig() {
        if (empty($_POST)) {
            $this->error('参数错误');
        }
        //得到上传的图片
        if (!empty($_FILES['banner_logo']['name'])) {
            $logo_options['save_to_db'] = false;
            $logo_options['max_size'] = getPhotoConfig('photo_max_size');
            $logo_options['allow_exts'] = getPhotoConfig('photo_file_ext');
            $logo = X('Xattach')->upload('site_logo', $logo_options);
            if ($logo['status']) {
                $logofile = '/data/uploads/' . $logo['info'][0]['savepath'] . $logo['info'][0]['savename'];
            }elseif($logo['info'] != '没有选择上传文件'){
                $this->error($logo['info']);
            }
            $data['path'] = $logofile;
        }
        $data['title'] = t($_POST['title']);
        $data['cTime'] = time();
        $data['sid'] = $this->school['id'];
        $data['max_credit'] = t($_POST['max_credit']);
        $data['max_credit'] = $data['max_credit']*100/100;
        if($data['max_credit']>1000){
            $this->error('最大学分不可大于1000');
        }
        $data['max_score'] = intval($_POST['max_score']);
        if($data['max_score']>1000){
            $this->error('最大积分不可大于1000');
        }
        $data['cradit_name'] = t($_POST['cradit_name']);
        $dao = D('SchoolWeb','event');
        $res = $dao->editSchoolWeb($data);
        if ($res) {
            $this->assign('jumpUrl', U('event/Config/webconfig'));
            $this->success('保存成功');
        } else {
            $this->error($dao->getError());
        }
    }

    public function printconfig() {
        $config = D('SchoolWeb','event')->getConfigCache($this->school['id']);
        $this->assign($config);
        $this->display();
    }

    public function doPrintconfig() {
        if (empty($_POST)) {
            $this->error('参数错误');
        }
        $data['print_title'] = t($_POST['title']);
        $data['print_content'] = t(h($_POST['content']));
        $data['print_day'] = intval($_POST['print_day']);
        $data['print_address'] = t($_POST['print_address']);
        $data['sid'] = $this->school['id'];
        $res = D('SchoolWeb','event')->editSchoolWeb($data);
        if ($res) {
            $this->assign('jumpUrl', U('event/Config/printconfig'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function cx() {
        $config = D('SchoolWeb','event')->getConfigCache($this->school['id']);
        $this->assign($config);
        $this->display();
    }


    public function doCx() {
        $required_field = array(
            'cxjg' => '警告次数',
            'cxjy' => '禁活动次数',
            'cxday' => '禁活动天数'
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }
        $data['cxjg'] = intval($_POST['cxjg']);
        $data['cxjy'] = intval(h($_POST['cxjy']));
        $data['cxday'] = intval($_POST['cxday']);
        if($data['cxjg']<1 || $data['cxjg']>30){
            $this->error('警告次数 1-30');
        }
        if($data['cxjy']<2 || $data['cxjy']>50){
            $this->error('禁活动次数 2-50');
        }
        if($data['cxjy'] <= $data['cxjg']){
            $this->error('禁活动次数 必须大于 警告次数');
        }
        if($data['cxday']<1 || $data['cxday']>100){
            $this->error('禁活动天数 1-100');
        }
        $data['sid'] = $this->school['id'];
        $res = D('SchoolWeb','event')->editSchoolWeb($data);
        if ($res) {
            $this->assign('jumpUrl', U('event/Config/cx'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }
    //活动分类
    public function typeList(){
        $this->assign('menu1',2);
        $dao = D('EventType');
        $this->assign('type_list', $dao->getType($this->sid));
        if(!D('EtypeInit')->isInited($this->sid)){
            $this->assign('list',$dao->eventTypeDb($this->sid,true));
            $this->display('firstType');
        }else{
            $this->assign('list',$dao->eventTypeDb($this->sid));
            $this->display('typeList');
        }
    }
    //编辑分类
    public function editType(){
        $dao = D('EventType');
        $id = intval($_REQUEST['id']);
        if($id>0){
            $this->assign($dao->where("sid=$this->sid and id=$id")->find());
        }
        $this->assign('typeOrig',$dao->eventTypeOrig());
        $this->display();
    }
    //编辑分类
    public function doEditType(){
        $dao = D('EventType');
        $res = $dao->editEventType($this->sid);
        if($res){
            $this->success('操作成功');
        }
        $this->error($dao->getError());
    }
    //删除分类
    public function doDelType(){
        $dao = D('EventType');
        $id = intval($_REQUEST['id']);
        $res = $dao->delEventType($id,$this->sid);
        if($res){
            $this->success('操作成功');
        }
        $this->error($dao->getError());
    }
    //旧分类规整
    public function moveOldType(){
        if(D('EtypeInit')->isInited($this->sid)){
            $this->error('已经处理过，不可重复');
        }
        $dao = D('EventType');
        $newType = $dao->eventTypeDb($this->sid,true);
        if(empty($newType)){
            $this->error('请先添加自定义分类');
        }
        $this->assign('newType',$newType);
        $this->assign('oldType',$dao->eventTypeOrig());
        $this->display();
    }
    //旧分类规整
    public function doMoveOldType(){
        $dao = D('EventType');
        $res = $dao->doMoveOldType($this->sid,$this->mid);
        if($res){
            $this->assign('jumpUrl', U('event/Config/typeList'));
            $this->success('操作成功');
        }
        $this->error($dao->getError());
    }
    
    //配置每个活动分类学分上限
    public function configCredit()
    {
        $this->assign('menu1',2);
        $dao = D('EventType');
        $id = intval($_REQUEST['id']);
        if($id>0)
        {
            $info = $dao->where("sid=$this->sid and id=$id")->find();
            $this->assign($info);
        }
        $this->assign('typeOrig',$dao->eventTypeOrig());
        $this->display();
    }
    
    //处理每个活动分类学分上限数据
    public function doConfigCredit()
    {
        $map['id'] = $this->sid;
        $year = M('school')->where($map)->getField('tj_year');
        if(empty($year))
        {
            $this->error('学校暂未增加年制或者学校暂未开通');
        }
        unset($_POST['__hash__']);
        $data = $_POST;
        //3年制
        if($year == 3)
        {
            $upAllScore = $data['upScore'] * 3;
            $downAllScore = $data['downScore'] * 3;
            $allScore = $upAllScore+$downAllScore;
        }
        //4年制
        if($year == 4)
        {
            $upAllScore = $data['upScore'] * 4;
            $downAllScore = $data['downScore'] * 4;
            $allScore = $upAllScore+$downAllScore;
        }
        //检测是否设置的总和大于总学期数应获得学分
        if($data['allScore']>$allScore)
        {
            $this->error('单个分类总学期学分大于每个学期应获得学分的总和');
        }
        $data['sid'] = $this->sid;
        var_dump($data);
        
    }
    
    //配置所有活动分类的学分上限
    public function configAllCredit()
    {
        $this->assign('menu1',2);
        $this->display();
    }
    
    //处理所有活动分类学分上限数据处理
    public function doConfigAllCredit()
    {
        
    }

    //学分申请自定义列表
    public function ecCat() {
        $this->assign('menu1',3);
        $this->assign('menu2',1);
        $this->assign('folder', D('EcFolder')->allEcFolder($this->sid));
        $this->display();
    }
    //增加自定义文件夹
    public function addEcFolder(){
        $dao = D('EcFolder');
        $id = intval($_REQUEST['id']);
        if($id>0){
            $obj = $dao->where("is_folder=1 and sid=$this->sid and id=$id")->find();
            if(!$obj){
                $this->error('没找到数据');
            }
            $this->assign($obj);
        }
        $this->display();
    }
    //增加自定义文件
    public function addEcFile(){
        $dao = D('EcFolder');
        $id = intval($_GET['id']);
        if($id==0){
            $newId = $dao->addNewFile($this->sid);
            $this->assign('id', $newId);
            $this->assign('title', '新增申请表');
        //编辑申请表
        }else{
            $obj = $dao->where("is_folder=0 and sid=$this->sid and id=$id")->find();
            if(!$obj){
                $this->error('没找到数据');
            }
            $this->assign($obj);
            $inputs = M('EcInput')->where("fileId=$id")->order('inputOrder asc,id desc')->findAll();
            $this->assign('inputs',$inputs);
        }
        $this->display();
    }
    //增加自定义文件夹
    public function doAddEcFolder(){
        $dao = D('EcFolder');
        $res = $dao->editEcFolder($this->sid);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error($dao->getError());
        }
    }
    //删除文件夹
    public function doDelFolder() {
        $dao = D('EcFolder');
        $res = $dao->doDelFolder($this->sid);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error($dao->getError());
        }
    }
    //转移申请表 分类列表
    public function folderList(){
        $dao = D('EcFolder');
        $id = intval($_REQUEST['id']);
        $obj = $dao->where("is_folder=0 and sid=$this->sid and id=$id")->field('id,pid,title')->find();
        $this->assign($obj);
        $this->assign('list',$dao->onlyFolder($this->sid));
        $this->display();
    }
    //删除申请表
    public function doDelFile() {
        $dao = D('EcFolder');
        $res = $dao->doDelFile($this->sid);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error($dao->getError());
        }
    }
    //转移申请表pid
    public function setEcFiel(){
        $field = t($_POST['field']);
        if($field=='inputOrder'){
            $dao = D('EcInput');
        }else{
            $dao = D('EcFolder');
        }
        $res = $dao->setEcField($this->sid);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error($dao->getError());
        }
    }
    //增加自定义文件
    public function addEcInput(){
        $pid = intval($_GET['pid']);
        $type = intval($_GET['type']);
        $id = intval($_GET['id']);
        if($id>0){
            $obj = M('EcInput')->where("sid=$this->sid and id=$id")->find();
            if($obj){
                $pid = $obj['fileId'];
                $type = $obj['type'];
                $obj['opt'] = unserialize($obj['opt']);
                $this->assign('obj',$obj);
            }
        }
        if(!in_array($type, array(1,2,3,4))){
            $this->error('资料类型错误');
        }
        if($pid<=0){
            $this->error('没找到数据');
        }
        $this->assign('fileId',$pid);
        $this->assign('type',$type);
        if($type==3){
            $this->display('addEcInput2');
        }else{
            $this->display();
        }
    }
    public function doAddEcInput(){
        $dao = D('EcInput');
        $res = $dao->addEcInput($this->sid);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error($dao->getError());
        }
    }
    public function doDelInput(){
        $dao = D('EcInput');
        $res = $dao->delInput($this->sid);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error($dao->getError());
        }
    }
    //发布
    public function doRelease(){
        $dao = D('EcFolder');
        $res = $dao->doRelease($this->sid);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error($dao->getError());
        }
    }
    //审核人列表
    public function ecAuditor(){
        $this->assign('menu1',3);
        $this->assign('menu2',2);
        $list = D('EcAuditor')->ecAuditorList($this->sid);
        $this->assign($list);
        $this->display();
    }
    //添加审核人
    public function doAddEcAuditor(){
        $dao = D('EcAuditor');
        $res = $dao->addEcAuditor($this->sid);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error($dao->getError());
        }
    }
    //删除审核人
    public function delEcAuditor(){
        $dao = D('EcAuditor');
        $res = $dao->delEcAuditor($this->sid);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error($dao->getError());
        }
    }
    //预览
    public function review(){
        $id = intval($_GET['id']);
        $obj = D('EcFolder')->where("is_folder=0 and sid=$this->sid and id=$id")->find();
        if(!$obj){
            $this->error('该申请表不存在或已删除');
        }
        $this->assign($obj);
        $inputs = M('EcInput')->where("fileId=$id")->order('inputOrder asc,id desc')->findAll();
        foreach($inputs as &$v){
            $v['opt'] = unserialize($v['opt']);
        }
        $this->assign('inputs',$inputs);
        //审核人
        $audits = D('EcAuditor')->yuanxiList($this->sid);
        $this->assign('audit',$audits);
        $this->display();
    }
    //学分兑换
    public function ecChange(){
        $this->assign('menu1',3);
        $this->assign('menu2',3);
        $this->display();
    }
    public function doEcChange() {
        if (empty($_POST)) {
            $this->error('参数错误');
        }
        $data['ecName'] = t($_POST['ecName']);
        if(get_str_length($data['ecName'])>4){
            $this->error('最大4个汉字');
        }
        $data['ecRat'] = t($_POST['ecRat']);
        $data['sid'] = $this->school['id'];
        $res = D('SchoolWeb','event')->editSchoolWeb($data);
        if ($res) {
            $this->assign('jumpUrl', U('event/Config/ecChange'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }
    
    //活动标签自定义
    public function tag(){
        $this->assign('menu1',4);
        $tag = D('EventTag');
        $map['sid'] = $this->school['id'];
        $map['isdel'] = '0';
        $this->assign('list',$tag->where($map)->findAll());
        $this->display();
    }
    
    //默认学分
    public function autoCredit() {
        $this->assign('menu1',2);
        $typeList = D('EventType')->eventTypeDb($this->sid);
        if(!$typeList){
            redirect( U('event/Config/typeList'),1,'请先添加分类' );
        }
        $this->assign('typeList',$typeList);
        $levelList = D('EventLevel')->allLevelWithCredit($this->sid,$typeList);
        $this->assign('levelList',$levelList);
        $status = D('EventAutoLevel')->statusNow($this->sid);
        $statusMsg = '[尚未激活] << 点击激活';
        if($status){
            $statusMsg = '[已激活] << 点击取消';
        }
        $this->assign('statusMsg',$statusMsg);
        $this->display();
    }
    //添加默认学分
    public function addLevel(){
        $dao = D('EventLevel');
        $levelId = $dao->addEventLevel(t($_POST['title']),$this->sid);
        if(!$levelId){
            $this->error($dao->getError());
        }
        $dao = D('EventCredit');
        $res = $dao->addEventCredit($levelId,t($_POST['credit']),$this->sid);
        if(!$res){
            $this->error($dao->getError());
        }
        $this->success('操作成功');
    }
    //编辑默认学分
    public function editLevel(){
        $id = intval($_POST['id']);
        $daoLevel = D('EventLevel');
        $levelId = $daoLevel->editEventLevel(t($_POST['title']),$this->sid,$id);
        if(!$levelId){
            $this->error($daoLevel->getError());
        }
        $dao = D('EventCredit');
        $res = $dao->addEventCredit($levelId,t($_POST['credit']),$this->sid);
        if(!$res){
            $this->error($dao->getError());
        }
        $this->getLevelById();
    }
    // 获取某级别 名称+学分
    public function getLevelById() {
        $id = intval($_POST['id']);
        $dao = D('EventLevel');
        $res = $dao->getLevelCreditById($id,$this->sid);
        if($res){
            $this->ajaxReturn($res,$dao->getLevelNameById($id),1);
        }else{
            $this->error($dao->getError());
        }
    }
    // 删除级别
    public function delLevel() {
        $id = intval($_POST['id']);
        $dao = D('EventLevel');
        $res = $dao->delLevel($id,$this->sid);
        if($res){
            $this->success('删除成功');
        }else{
            $this->error($dao->getError());
        }
    }
    public function activeLevel(){
        $res = D('EventAutoLevel')->changeStatus($this->sid,$this->mid);
        if($res){
            $this->success('[已激活] << 点击取消');
        }else{
            $this->success('[尚未激活] << 点击激活');
        }
    }
}
