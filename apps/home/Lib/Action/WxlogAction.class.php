<?php 

    class WxlogAction extends Action{
        //微信登录界面
        public function login(){
            $type = $_GET['type'];
            $this->assign('type',$type);
            $this->display();
        }
        
        //微信登录处理
        public function dologin(){
            $school = $_POST['user_school'];
            $no = $_POST['user_no'];
            $password = $_POST['user_password'];
            //判断是否输入
            $error = '';
            if(!$school){
                $error = '请选择学校';
                $this->ajaxReturn($error);
                die;
            }
            if(!$no){
                $error = '请输入学号';
                $this->ajaxReturn($error);
                die;
            }
            if(!$password){
                $error = '请输入登录密码';
                $this->ajaxReturn($error);
                die;
            }
            $map['title'] = $school;
            $suffix = M('school')->where($map)->find();
            if(!$suffix){
                $error = '该学校尚未开通';
                $this->ajaxReturn($error);
                die;
            }
            $username = t($_POST['user_no']) . $suffix['email'];
            $password = $_POST['user_password'];

            $result = service('Passport')->loginLocal($username, $password, intval($_POST['remember']));
        
            //检查是否激活
            if (!$result && service('Passport')->getLastError() == '用户未激活') {
                $error = '该用户尚未激活，请更换帐号或激活帐号！';
                $this->ajaxReturn($error);
                die;
            }
        
            if ($result) {
                $error = 'success';
                $this->ajaxReturn($error);
                die;
                /* $refer_url = U('shop/PocketShop/bankPrice');
                redirect($refer_url); */
            } else {
                $error = '登录信息有误,请重新输入';
                $this->ajaxReturn($error);
                die;
            }
        }
        
        //微信注册界面
        public function reg(){
            $this->display();
        }
        
        
                
        //微信注册处理
        public function doreg(){
            
            /* $error = $_GET['id'];
            $this->ajaxReturn($error);
            die; */
            //除证件外，所有字段判空
            $required_field = array(
                'user_school' => '学校',
                'xueyuan' => '院系',
                'user_no' => '学号',
                'user_name' => '姓名',
                'user_major' => '专业',
                'grade' => '年级',
                'user_tel'=> '手机号码',
                'user_mail' => '密保邮箱',
                'user_password' => '密码'
            );
            foreach ($required_field as $k => $v) {
                if (empty($_GET[$k])){
                    $error = $v.'不可为空';
                    $this->ajaxreturn($error);
                    exit;
                }
            }
        
            //学号做一下判断
            $user_no = $_GET['user_no'];
            if (strpos($user_no, '@')) {
                $error = '输入学号而不是邮箱';
                $this->ajaxreturn($error);
                exit;
            }
            $sid = $_GET['sid'];  //学校id
            //判断学校是否存在
            if(empty($sid)){
                $error = '学校不存在，请重新选择';
                $this->ajaxreturn($error);
                exit;
            }
            $suffix = M('school')->where("canRegister=1 and id=$sid")->field('email')->find();
            if ($suffix && $suffix['email'] != '') {
                $email = $user_no . $suffix['email'];
                $hasUser = M('user')->where('`email`="' . $email . '"')->field('uid')->find();
                if($hasUser){
                    $error = '此学号已注册成功，请用学号登录';
                    $this->ajaxreturn($error);
                    exit;
                }
            } else {
                $error = '此学校不公开注册，学校统一导入，请用尝试学号登录';
                $this->ajaxreturn($error);
                exit;
            }
        
        
            //密码做一下判断
            if (strlen($_GET['user_password']) < 6 || strlen($_GET['user_password']) > 16) {
                $error = '密码格式有误, 合法的密码为6-16位字符!';
                $this->ajaxreturn($error);
                exit;
            }
        
            //邮箱做一下判断
            $preg = '/^([\w\.\_]{2,})@(\w{1,}).([a-z]{2,4})$/';
            if(!preg_match($preg,$_GET['user_mail'])){
                $error = '电子邮件不合法啊';
                $this->ajaxreturn($error);
                exit;
            }
        
        
            //手机做一下判断
            $mobile = $_GET['user_tel'];
            if (!isValidMobile($mobile)) {
                $error = '手机号码格式不正确';
                $this->ajaxreturn($error);
                exit;
            }
            $map = array('mobile'=>$_GET['user_tel']);
            $hasMobile = M('user')->where($map)->field('uid')->find();
            if ($hasMobile){
                $error = '该手机号码已被使用';
                $this->ajaxreturn($error);
                exit;
            }
            
            
        
            //上传证件做一下判断
            /* if(empty($_FILES['photo']['name'])){
                $this->error('上传证件不能为空');
                die;
            } */
            $type = $_FILES['photo']['type'];
            $arr = explode('/',$type);
            $type = $arr[1];
            $allow = array('jpg','jpeg','png','gif');
            if(!in_array($type,$allow)){
                $error = '上传的证件格式不正确';
                $this->ajaxreturn($error);
                exit;
            }
            
            
            //文件上传
            $options = array();
            $options['allow_exts'] = 'jpeg,gif,jpg,png,bmp';
            $options['isDel'] = 1;
            $info = X('Xattach')->upload('register',$options);
            if(!$info['status']){
                $error = $info['info'];
                $this->ajaxreturn($error);
                exit;
            }
            $data = array();
            $data['zj_file'] = $info['info'][0]['savepath'].$info['info'][0]['savename']; //上传证件
            $_POST['attid'] = $info['info'][0]['id'];
            
            
            
        
            //做一下保存
            $data['sid'] = $sid;  //学校
            $data['year'] = $_GET['grade'] - 2000; //年级
            $data['number'] = $_GET['user_no'];  //学号
            $data['realname'] = $_GET['user_name'];  //姓名
            $data['major'] = $_GET['user_major']; //专业
            $data['yuanxi'] = $_GET['xueyuan'];  //院系
            $data['password'] = codePass($_GET['user_password']);  //用户密码
            $data['email'] = $_GET['user_mail'];  //邮箱
            $data['mobile'] = $_GET['user_tel'];  //手机号
            $data['ctime'] = time();  //注册时间
            $reg = M('UserReg');
            $reginfo = $reg->add($data);
            
            
            //激活学生证附件
            /* if(isset($_POST['attid'])){
                $attid = intval($_POST['attid']);
                if(empty($attid)) return false;
                $map['id'] = array('in', $attid);
                $data['isDel'] = 0;
                M('attach_reg')->where($map)->save($data);
            } */
            
            
            //修改数据库user表中的字段is_reg,为了微信用户注册后可直接使用
            $u_data = array();
            $u_data['sid'] = $sid;  //学校id
            $u_data['is_reg'] = '1';
            $u_data['password'] = codePass($_GET['user_password']);
            $email = t($_GET['user_no']) . $suffix['email'];
            $u_data['email'] = $email;
            $u_data['realname'] = $_GET['user_name'];
            $u_data['mobile'] = $_GET['user_tel'];
            $u_data['ctime'] = time();
            $user = M('user');
            $result = $user->add($u_data);
        
            if(!$reginfo){
                $error = '注册失败，请稍后再试';
                $this->ajaxreturn($error);
                exit;
            }else{
                $error = 'success';
                $this->ajaxreturn($error);
                exit;
            }
        }
        
        //注册成功
        public function regsuccess(){
            $this->display(reg_succeed);
        }
        
        
        
        //模糊匹配学校名
        public function getSchool(){
            $name = $_GET['school'];            //学校名字
            $map = array();
            $map['title'] = array('like','%'.$name.'%');
            $map['pid'] = '0';
            $school = M('School');
            $count = $school->where($map)->count();
            if($count > 8){
                $schoolList = $school->where($map)->field('id,title')->limit(8)->select();
            }else{
                $schoolList = $school->where($map)->field('id,title')->select();
            }
            $this->ajaxReturn($schoolList);
            die;
        }
        
        //根据学校获取学院
        public function getXueyuan(){
            $sid = $_GET['sid'];
            $map = array();
            $map['pid'] = $sid;
            $school = M('School');
            $schoolList = $school->where($map)->field('id,title')->select();
            $this->ajaxReturn($schoolList);
            die;
        }
        
        //根据title判断学校是否存在
        public function judgeSchool(){
            $title = $_GET['title'];
            $map = array();
            $map['title'] = $title;
            $school = M('school');
            $data = '';
            $result = $school->where($map)->field('id,title')->find();
            if($result){
                $data = $result;
            }else{
                $data = 'error';
            }
            $this->ajaxReturn($data);
            die;
        }
    }

?>