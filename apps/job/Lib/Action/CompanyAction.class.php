<?php
/**
 * 企业
 */
class CompanyAction extends Action{
    private $eid;

    /**
     * 企业资料
     */
    public function homePage(){
           $eid = t($_GET['eid']);
           $companyInfo = D('JobEnterpriseInfo')->getEnterpriseInfo($eid);
           $this->assign('cinfo',$companyInfo);
           unset($map);
           //公司近一个月的招聘
           $map['p.eid'] = $eid;
           $map['p.ctime'] = array('egt',time()-3600*24*30);
           $jobList = D('JobPublishPosts')->jobList($map,$limit=10);
           $num = count($jobList);//招聘数量
           $this->assign('jobList',$jobList['data']);
           $this->display('homePage');
    }
    
    /**
     * 推荐企业列表
     */
    public function recommendCompany(){
            $choice = $_GET['choice'] ? $_GET['choice']:'company';
//            $url = '';
            if($choice == 'government'){
                $map['r.type'] = 1;
            }else{
                $map['r.type'] = 0;
            }
            /*搜索条件*/
            
            //城市
            if($_GET['city'] && $_GET['city'] != 0){
                $map['e.city'] = $_GET['city'];
            }
            
            //行业
            if($_GET['industry'] && $_GET['industry'] != 0){
                $where['industrycategory_a']  = $_GET['industry'];
                $where['industrycategory_b']  = $_GET['industry'];
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
            }
            //推荐的企业
            $companyList = D('JobRecommendcompany')->companyList($map,10);
            //city列表
            $city['provinceID'] = 320000;//江苏省省ID
            $cityList = D('HatProvince')->provinceList($city,false);
            $this->assign('cityList',$cityList[0]['city']);
//            $this->display('recommendcompany');
    }
    
            //企业信息管理
        public function infomanage(){
            $map['eid'] = $this->eid;
            $res = D('JobEnterpriseInfo')->getEnterpriseInfo($map); 
//            $this->assign();
            $this->display();

        }
        
        /**
         * 企业信息管理操作
         */
        public function doinfomanage(){
            
            $data['type'] = t($_POST['type']); 
            $data['industrycategory_a'] = t($_POST['industrycategory_a']);
            $data['industrycategory_b'] = t($_POST['industrycategory_a']);   
            $data['scale'] = t($_POST['scale']);

            if($_POST['address'] == ''){
                $this->error(L('请填写正确的地址'));
            }
            $data['address'] = h($_POST['address']);
            $data['homepage'] = h($_POST['homepage']);
            $data['city'] = t($_POST['city']);   
            $data['contact'] = h($_POST['contact']);
            $data['telephone_areacode'] = h($_POST['telephone_areacode']);
            $data['telephone'] = h($_POST['telephone']);

            $str = $_POST['introduction'];
            $str_length = (strlen($str) + mb_strlen($str,'UTF8')) / 2;//获取字符长度
            if($str_length <= 0 || $str_length >10000){
                $this->error(L('字符长度不能小于0或者大于10000'));
            }
            $data['introduction'] = $str;
            $data['founder_name'] = h($_POST['founder_name']);

            //处理添加的照片前台传过来Id
            if(!empty($_POST['founder_pic'][0])){
                $data['founder_pic'] = $_POST['founder_pic'][0];
            }
            
            if(!empty($_POST['logo_pic'][0])){
                $data['logo_pic'] = $_POST['logo_pic'][0];
            }
            
            if(!empty($_POST['product_pic'])){
                //添加产品照片 上传的product_pic表
                foreach ($_POST['product_pic'] as $value) {
                    $data['eid'] = $this->eid;
                    $data['pic'] = $value;
                    M('JobProductPic')->add($data);
                }
            }
            $res = M('JobEnterpriseInfo')->where('eid='.$this->eid)->save($data);
            if($res){
                $this->success(L('信息设置成功'));
            }else{
                $this->success(L('信息设置失败'));
            }

            
        }
        
        
        //查看用户申请
        public function viewapplication(){
            $map['postid'] = t($_GET['postid']);
            $list = D('JobApplication')->applyList($map,$limit=10);
        
        }
        
        //微信登录界面
        public function login(){
            $this->display();
        }
        
        //微信登录处理
        public function dologin(){
            $school = $_POST['user_school'];
            $no = $_POST['user_no'];
            $password = $_POST['user_password'];
            //判断是否输入
            if(!$school){
                $this->assign('jumpUrl',U('job/Company/login'));
                $this->error('请选择学校');
            }
            if(!$no){
                $this->assign('jumpUrl',U('job/Company/login'));
                $this->error('请输入学号');
            }
            if(!$password){
                $this->assign('jumpUrl',U('job/Company/login'));
                $this->error('请输入登录密码');
            }
            $map['title'] = $school;
            $suffix = M('school')->where($map)->find();
            if(!$suffix){
                $this->assign('jumpUrl',U('job/Company/login'));
                $this->error('该学校尚未开通');
            }
            $username = t($_POST['user_no']) . $suffix['email'];
            $password = $_POST['user_password'];
            
            if (!$password) {
                $this->assign('jumpUrl',U('job/Company/login'));
                $this->error(L('please_input_password'));
            }
            $result = service('Passport')->loginLocal($username, $password, intval($_POST['remember']));
            
            //检查是否激活
            if (!$result && service('Passport')->getLastError() == '用户未激活') {
                $this->assign('jumpUrl',U('job/Company/login'));
                $this->error('该用户尚未激活，请更换帐号或激活帐号！');
                exit;
            }
            
            if ($result) {
                
                $refer_url = U('shop/PocketShop/bankPrice');
                redirect($refer_url);
            } else {
                $this->error(L('login_error'));
            }
        }
        
        //微信注册界面
        public function reg(){
            $this->display();
        }
        
        //微信注册处理
        public function doreg(){
            
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
                if (empty($_POST[$k])){
                    $this->error($v . '不可为空');
                    exit;
                }
            }
            
            //学号做一下判断
            $user_no = $_POST['user_no'];
            if (strpos($user_no, '@')) {
                $this->error('输入学号而不是邮箱');
                exit;
            }
            $sid = $_POST['sid'];  //学校id
            $suffix = M('school')->where("canRegister=1 and id=$sid")->field('email')->find();
            if ($suffix && $suffix['email'] != '') {
                $email = $user_no . $suffix['email'];
                $hasUser = M('user')->where('`email`="' . $email . '"')->field('uid')->find();
                if($hasUser){
                    $this->error('此学号已注册成功，请用学号登录');
                    exit;
                }
            } else {
                $this->error('此学校不公开注册，学校统一导入，请用尝试学号登录');
                exit;
            }
            
            
            //密码做一下判断
            if (strlen($_POST['user_password']) < 6 || strlen($_POST['user_password']) > 16) {
                $this->error('密码格式有误, 合法的密码为6-16位字符!');
                exit;
            }
            
            //邮箱做一下判断
            $preg = '/^([\w\.\_]{2,})@(\w{1,}).([a-z]{2,4})$/';
            if(!preg_match($preg,$_POST['user_mail'])){
                $this->error("电子邮件不合法啊");
                exit;
            }
            
            
            //手机做一下判断
            $mobile = $_POST['user_tel'];
            if (!isValidMobile($mobile)) {
                $this->error('手机号码格式不正确');
                exit;
            }
            $map = array('mobile'=>$_POST['user_tel']);
            $hasMobile = M('user')->where($map)->field('uid')->find();
            if ($hasMobile){
                $this->error('该手机号码已被使用');
                exit;
            }
            
            //上传证件做一下判断
            if(empty($_FILES['photo']['name'])){
                $this->error('上传证件不能为空');
                die;
            }
            $type = $_FILES['photo']['type'];
            $arr = explode('/',$type);
            $type = $arr[1];
            $allow = array('jpg','jpeg','png','gif');
            if(!in_array($type,$allow)){
                $this->error('上传的证件格式不正确');
                exit;
            }
            //文件上传
            $options = array();
            $options['allow_exts'] = 'jpeg,gif,jpg,png,bmp';
            $options['isDel'] = 1;
            $info = X('Xattach')->upload('register',$options);
            if(!$info['status']){
                $this->error($info['info']);
                exit;
            }
            $data = array();
            $data['zj_file'] = $info['info'][0]['savepath'].$info['info'][0]['savename']; //上传证件
            $_POST['attid'] = $info['info'][0]['id'];
            
            //做一下保存
            $data['sid'] = $sid;  //学校 
            $data['year'] = $_POST['grade'] - 2000; //年级
            $data['number'] = $_POST['user_no'];  //学号
            $data['realname'] = $_POST['user_name'];  //姓名
            $data['major'] = $_POST['user_major']; //专业
            $data['yuanxi'] = $_POST['xueyuan'];  //院系
            $data['password'] = codePass($_POST['user_password']);  //用户密码
            $data['email'] = $_POST['user_mail'];  //邮箱
            $data['mobile'] = $_POST['user_tel'];  //手机号
            $data['ctime'] = time();  //注册时间
            $reg = M('UserReg');
            $res = $reg->add($data);
            
            //激活学生证附件
            if(isset($_POST['attid'])){
                $attid = intval($_POST['attid']);
                if(empty($attid)) return false;
                $map['id'] = array('in', $attid);
                $data['isDel'] = 0;
                M('attach_reg')->where($map)->save($data);
            }
            
            if(!$res){
                $this->error('注册失败，请稍后再试');
                exit;
            }else{
                $this->display('reg_succeed');
            }
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