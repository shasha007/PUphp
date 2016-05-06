<?php

class DaoruAction extends AdministratorAction {

     private $_globalMsg = '';
    //添加学校 上传文件检查
    public function checkAddUser(){
        $res = $this->_readExcel();
        $schoolName = $res[1][0];
        $sid = M('school')->getField('id', "title='$schoolName'");
        if(!$sid){
            $this->_endCheck("学校【$schoolName 】不存在！");
        }
        $this->_globalMsg = '导入学校：【'.$schoolName.'】<br/>';
        $soll = array('学号','姓名','院系','年级','专业','班级','性别','手机号码','密码');
        $this->_checkLine($soll,$res[0],1);
        unset($res[0]);
        unset($res[1]);
        if(empty($res)){
            $this->_endCheck("学生数据0条！");
        }
        //检查院系
        $sdb = M('school')->where("pid=$sid")->field('id, title')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        $newSchoolArr = array();
        foreach ($res as $v){ 
            $yuanXi = t($v[2]);
            if(!$school[$yuanXi] && !in_array($yuanXi, $newSchoolArr)){
                $newSchoolArr[] = $yuanXi;
            }
        }
        if(empty($newSchoolArr)){
            $newSchool = '新建院系： 无';
        }else{
            $str = implode('， ', $newSchoolArr);
            $newSchool = '注意！将新建院系： '.$str;
        }
        $this->_endCheck('',$newSchool);
    }
    public function doAddUser(){
        $filePath = $_POST['filePath'];
        $dao = service('Excel');
        $res = $dao->read($filePath);
        if(empty($res)){
            $this->error('文档无内容');
        }
        $schoolName = $res[1][0];
        $sid = M('school')->getField('id', "title='$schoolName'");
        if(!$sid){
            $this->_endCheck("学校【$schoolName 】不存在！");
        }
        unset($res[0]);
        unset($res[1]);
        if(empty($res)){
            $this->_endCheck("学生数据0条！");
        }
        $sdb = M('school')->where("pid=$sid")->field('id, title')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['id'];
        }
        $suc = 0;
        $fail = 0;
        $msg = '';
        $email_post = M('school')->getField('email', "title='$schoolName'");
        $schoolDbChanged = false;
        foreach ($res as $k=>$v){
            $line = $k+1;
            $data = array();
            $email = t($v[0]).$email_post;
            $data['realname'] = t($v[1]);
            $data['realname'] = str_replace(' ', '', $data['realname']);
            $data['uname'] = $data['realname'].t($v[0]);
            $data['sid']     = $sid;
            $sid1 = t($v[2]);
            if($sid1){
                if($school[$sid1]){
                    $data['sid1'] = $school[$sid1];
                }else{
                    $mapSchool['title'] = $sid1;
                    $mapSchool['pid'] = $sid;
                    $banId = M('school')->add($mapSchool);
                    $data['sid1'] = $banId;
                    $school[$sid1] = $banId;
                    $schoolDbChanged = true;
                }
                $data['year'] = t($v[3]);
                $data['major'] = t($v[4]);
                $data['sex'] = (t($v[6]) == '女')?0:1;
                $data['is_init'] = 0;  //改为1，即导入的数据为初始化
                //$data['from_reg'] = 1; //非学校官方数据要修改的
                //$data['can_add_event'] = 1; //有发起活动权限，将前面的注释//去掉
                $mobile = t($v[7]);
                if($mobile){
                    $data['mobile'] = $mobile;
                }
                $pass = t($v[8]);
                if ($pass) {
                    $data['password'] = codePass($pass);
                }
                $uid = $this->_doAddUser($email,$data);
            }else{
                $uid = 0;
            }
            if ($uid) {
                $class = t($v[5]);
                if($class){
                    Model('UserA')->addUserA($uid,array('class'=>$class));
                }
                $suc++;
            } else {
                $fail++;
                $msg .= "第$line 行，写入失败：$v[0] $v[1] <br/>";
            }
        }
        if($schoolDbChanged){
            model('Schools')->initCache();
            model('Schools')->cacheSchoolDb($sid);
        }
        @unlink($filePath);
        $this->success('导入完毕<br/><span class="cGreen">成功：'.$suc.'条 </span>，<span class="cRed">失败：'.$fail.'条<br/>'.$msg.'</span>');
    }
    private function _doAddUser($email, $data){
        $data['email']     = $email;
        if (!isEmailAvailable($email)) {
            return false;
        }
        if(!isset($data['password'])){
            $data['password'] = '53dfac50ed707f67bba6c4161c0c947f5e5378e3';
        }
        $data['ctime']	   = time();
        $data['is_active'] = 1;
	if(!isset($data['is_init'])){
		$data['is_init']  = 0;
        }
        
        $data['is_valid']  = 1;
        if (!($uid = M('user')->add($data)))
                return false;
        //注册成功 初始积分
//        $sData['uid'] = $uid;
//        $sData['score'] = 200;
//        M('credit_user')->add($sData);
        return $uid;
    }
    //读取excel
    private function _readExcel(){
        if(empty($_FILES['file']) || strpos($_FILES['file']['name'], '.xls')===false){
            $this->error('请上传excel文档');
        }
        $dao = service('Excel');
        $res = $dao->read($_FILES['file']['tmp_name']);
        if(empty($res)){
            $this->error('文档无内容');
        }
        return $res;
    }
    private function _checkLine($soll,$row,$line){
        $cnt = count($soll);
        if(count($row)!=$cnt){
            $sollStr = implode('，',$soll);
            $this->_endCheck("表格第$line 行错误，必须是【$sollStr 】");
            return false;
        }
        for($i=0;$i<$cnt;$i++){
            if($row[$i] != $soll[$i]){
                $lie = $i+1;
                $this->_endCheck("第$line 行$lie 列【$row[$i]】错误，应该为 【$soll[$i]】");
                return false;
            }
        }
        return true;
    }
    private function _endCheck($errMsg,$sucMes){
        if($errMsg!=''){
            $this->error($this->_globalMsg.$errMsg);
//            $this->error("共$fail 个错误<br/>".$msg);
        }else{
            $file_name = time() . '_' . $this->mid . '.xls'; //使用时间来模拟图片的ID.
            $file_path = SITE_PATH . '/data/tmp/' . $file_name;
            $file = @$_FILES['file']['tmp_name'];
            file_exists($file_path) && @unlink($file_path);
            if (@copy($file, $file_path) || @move_uploaded_file($file, $file_path)) {
                @unlink($file);
                $this->ajaxReturn($file_path,$this->_globalMsg.$sucMes,1);
//                $this->ajaxReturn($file_path,"检查通过，共$suc 行有效数据，可以进行发放操作",1);
            }
            $this->error('保存文件失败');
        }
    }

}