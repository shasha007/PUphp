<?php

class PublicAction extends Action {
    
    public function index(){
        $this->display();
   
    }
    
    //企业注册
    public function companyRegist(){
        $ename = h($_POST['ename']);
        if(M('JobEnterprise')->where("ename= '{$ename}'")->select()){
             $this->error(L('该账户已存在'));
        }
        
        $name_strlen = mb_strlen($_REQUEST['ename'],UTF8);
        if($name_strlen <4 || $name_strlen>12){
            $this->error(L('账号长度应大于3小于13'));
        }
        
        $pass_strlen = mb_strlen($_REQUEST['pass'],UTF8);
        if($pass_strlen <4 || $pass_strlen>12){
            $this->error(L('密码长度应大于5小于13'));
        }
        $cellphone = $_REQUEST['cellphone'];   
        $email = $_REQUEST['email'];   
        $mode="/\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*/";
        if(!preg_match($mode,$email)){
            $this->error(L('请填写正确的邮箱'));
        }
        $fullname = $_REQUEST['fullname'];
        $full_strlen = mb_strlen($_REQUEST['fullname'],UTF8);
        if($full_strlen == ''){
            $this->error(L('请填写公司全名'));
        }
        if(!empty($_FILES['pic']['name'])){
            if(!isImg($_FILES['pic']['tmp_name'])){
              $this->error(L('营业执照复印件格式不正确'));
            }
            $options = array();
            $options['allow_exts'] == 'jpeg,gif,jpg,png,bmp';
            $info = X('Xattach')->upload('job',$options);
            if($info['status']){
//                 //图片id
            $blicense=$info['info'][0]['id'];
            }
        }else{
            $this->error(L('请上传营业执照复印件'));
        }
        
        $data['ename'] = h($_REQUEST['ename']);    
        $data['password'] = md5(h($_REQUEST['pass']));
        $res = M('JobEnterprise')->add($data);
        unset($data);
        if($res){
            $data['eid'] = $res;
            $data['cellphone'] = $cellphone;
            $data['getresume_email'] = $email;
            $data['fullname'] = $fullname;
            $data['blicense'] = $blicense;
            $data['ctime'] = time();
            $email_vcode = md5($res.rand(0,10000).time());
            $data['email_vcode'] = $email_vcode;
            $re = M('JobEnterpriseInfo')->add($data);
            if($re){
//                //发送邮件
                $this->send_email($res);
            }
        }
    }
    
    //发送邮件
    public function send_email($eid){
        $map['eid'] = $eid;
        $info = D('JobEnterpriseInfo')->getEnterpriseInfo($map);
        $email_vcode = $info['email_vcode'];
        $url = $_SERVER['HTTP_HOST'].__URL__.'&act=email_check&email_vcode='.$email_vcode;
        X('Mail')->send_email($info['getresume_email'],'邮箱验证',$url);
    }
    
    //邮箱验证
    public function email_check(){
        $email_vcode = $_GET['email_vcode'];
        $map['email_vcode'] = $email_vcode;
        $res = D('JobEnterpriseInfo')->getEnterpriseInfo($map);
        if($res){
            $data['is_activation'] = 1;
            M('JobEnterpriseInfo')->where('eid='.$res['eid'])->save($data);
            echo '验证成功 待审核';
        }else{
            echo '验证失败';
        }
        
    }
    //企业登陆
    public function dologin(){
        
        if(!$_POST['ename']){
            $this->error(L('请填写用户名'));
        }
        
         if(!$_POST['password']){
            $this->error(L('请填写密码'));
        }
        $map['ename'] = h($_REQUEST['ename']);
        $map['password'] = md5(h($_REQUEST['password']));   
        $res = M('JobEnterprise')->where($map)->find();
        unset($map);
        if(!$res){
            $this->error(L('账户不存在或密码错误，请重新输入'));
        }else{
            //获取用户所有信息
            $map['eid'] = $res['eid']; 
            $companyInfo = D('JobEnterpriseInfo')->getEnterpriseInfo($map);
            $companyInfo['lastlogintime'] = $res['lastlogintime'];    
            $companyInfo['ename'] = $res['ename'];

            $_SESSION['companyInfo'] = $companyInfo;   
            $_SESSION['eid'] = $res['eid'];

            //保存最后登陆时间
            $data['lastlogintime'] = time();
            M('JobEnterprise')->where($map)->save($data);
            //判断企业是否已经通过审核
            if(!$companyInfo['is_activation']){
                echo '未进行审核';//跳转到验证页面
            }else{
                redirect(U('job/Company/index'));//跳转到招聘首页
            }
        }
    }
    
    //企业退出
    public function doout(){
        unset($_SESSION['companyInfo']);
        unset($_SESSION['eid']);
        $this->redirect('job/Publics/login');
    }
}
?>
