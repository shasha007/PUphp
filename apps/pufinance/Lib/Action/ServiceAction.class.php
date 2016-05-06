<?php
class ServiceAction extends BaseAction
{
	//自助服务
	public function self(){
		
		$this->display();
	}
	
	//人工服务
	public function customer(){
		
		$this->display();
	}

	//重置密码第一步信息验证
	public function changePwd(){
		session_start();
		if($_POST['__hash__'] && ($_POST['__hash__'] == $_SESSION['__hash__'])){

			if(($_SESSION['vcode']['num'] != trim($_POST['vcode']) ) || ( (time()-$_SESSION['vcode']['time'])>3600*2 ) ){//验证手机验证码是否正确
				unset($_SESSION['vcode']);
				$this->error('手机验证码不正确或者已失效');
			}
			$data['realname'] = t($_POST['realname']);
			$data['ctfid'] = t($_POST['idCard']);
			$result = M('pufinance_user')->where($data)->find();
			if(empty($result)){
				$this->error('姓名或者身份证号不正确！');
			}else{
				$_SESSION['step1'] = 1;
				header('location:'.U('pufinance/service/resetPwd'));
			}
		}else{
			$user = D('PufinanceUser')->getUserByUid($this->mid);
			if(empty($user)){
				header('location:'.U('pufinance/Bankcard/bindBankcard'));
			}
			$this->display('changePassword');
		}

	}

	//重置密码第二步密码重置
	public function resetPwd(){
		if($_SESSION['step1'] != 1){//判断验证第一步是否完成
			header('location:'.U('pufinance/service/changePwd') );//未完成跳转到第一步
		}
        $successUrl = U('pufinance/help/index');
        // 来自银行卡绑定
		if (isset($_GET['from']) && $_GET['from'] == 'bindBankcard') {
            $puUser = D('PufinanceUser')->getByUid($this->mid);
            if (empty($puUser['mobile']) || empty($puUser['email'])) { // 设置用户信息
                $successUrl = U('pufinance/Service/personalInfo', array('from' => $_GET['from']));
            } else {
                $successUrl = U('pufinance/Bankcard/index');
            }

        }
        $this->assign('successurl', $successUrl);
		$this->display('resetPassword');
	}

	//手机验证码
	public function verify(){
		session_start();

		$mobile = $_POST['tel'];
		if( empty($this->user['mobile']) ){
			echo json_encode(array('code'=>-2,'msg'=>'请先去【我的】->【设置】->【绑定手机号】'));
			exit;
		}elseif($this->user['mobile'] != $mobile){
			echo json_encode(array('code'=>-3,'msg'=>'请使用绑定的手机号！'));
			exit;
		}
		$random = mt_rand(100000,999999);
		$_SESSION['vcode']['num'] = $random;
		$_SESSION['vcode']['time'] = time();
		$msg = $random.'(口袋校园重置支付密码验证，请勿将验证码透露给他人)';
		$status = service('Sms')->sendsms($mobile,$msg);
		if($status['status'] == 1){
			echo json_encode(array('code'=>1));
			exit;
		}else{
			echo json_encode(array('code'=>-1));
			exit;
		}
	}

	//修改个人信息
	public function personalInfo(){
		$user = D('PufinanceUser')->getUserByUid($this->mid);
		if(empty($user)){
			header('location:'.U('pufinance/Bankcard/bindBankcard'));
		}
		if(!empty($_POST['__hash__']) && ($_POST['__hash__'] == $_SESSION['__hash__'])){
			
			if(empty($this->user['mobile'])){//用户还没有绑定手机号
				$this->error('请先去【我的】->【设置】->【绑定手机号】');
				exit;
			}
			
			$data = array();
			$items = array(
				//'username' => '姓名',
				'nation' => '民族',
				'qq' => 'QQ(微信)',
				//'tel' => '备用手机号',
				'email' => '邮箱',
				'address' => '地址',
			);
			unset($_POST['__hash__']);
			foreach($items as $k=>$v){
				$value = trim($_POST[$k]);
				if(empty($value)){
					$this->error($v.'不能为空!');
				}
			}
			M('pufinance_user')->startTrans();
			$puuser_update = $user_update = true;
			
			$mobile = t($_POST['tel']);
			if(empty($user['mobile']) && empty($mobile)){
				$this->error('备用手机号不能为空!');
			}
			
			if(!empty($mobile) && !isValidMobile($mobile)){
				$this->error('备用手机格式不正确!');
			}
			
			if(!empty($mobile) && ($this->user['mobile'] == $mobile)){
				$this->error('备用手机号不能与绑定手机号一样！');
			}
			
			if(empty($user['mobile']) && !empty($mobile)){
				$data['mobile'] = $mobile;
			}elseif(!empty($mobile)){
				if($user['mobile'] != $mobile){
					$data['mobile'] = $mobile;
				}
			}
			
			//验证邮箱格式
			if(filter_var( t($_POST['email']),FILTER_VALIDATE_EMAIL) === false ){
				$this->error('邮箱格式不正确!');
			}elseif($user['email'] != t($_POST['email'])){//判断邮箱是否更改
				$data['email'] = t($_POST['email']);
				$user_update = M('user')->where('uid='.$this->mid)->data(array('email2'=>t($_POST['email'])))->save();
				//$user_update = !empty($user_update) ? true : false;
			}
			/*
			if($user['realname'] != t($_POST['username'])){//判断姓名是否更改
				$data['realname'] = t($_POST['username']);//姓名
			}*/
			if($user['ethnic'] != t($_POST['nation'])){//判断民族是否更改
				$data['ethnic'] = t($_POST['nation']);//民族
			}
			if($user['imid'] != t($_POST['qq'])){//判断微信qq是否更改
				$data['imid'] = t($_POST['qq']);//微信 qq
			}
			if($user['address'] != t($_POST['address'])){//判断地址是否更改
				$data['address'] = t($_POST['address']);//地址
			}
			if(isset($_POST['ruid']) && empty($user['recommend_uid'])){//推荐人uid
				$data['recommend_uid'] = (int)$_POST['ruid'];
			}

			if(empty($data)){//个人信息没有改动
                // 来自银行卡绑定
                if (isset($_GET['from']) && $_GET['from'] == 'bindBankcard') {
                    $this->assign('jumpUrl', U('pufinance/Bankcard/index'));
                }

                $this->success('操作成功！');
			}else{
				$puuser_update = M('pufinance_user')->where('uid='.$this->mid)->data($data)->save();
				$puuser_update = !empty($puuser_update) ? true : false;
			}
			if($puuser_update !== false && $user_update !== false){
				M('pufinance_user')->commit();
                // 来自银行卡绑定
                if (isset($_GET['from']) && $_GET['from'] == 'bindBankcard') {
                    $this->assign('jumpUrl', U('pufinance/Bankcard/index'));
                }
				$this->success('操作成功！');
			}else{
				M('pufinance_user')->rollback();
				$this->error('操作失败!');
			}
		}else{
			$this->assign($user);
			$this->display();
		}

	}

	//执行重置密码
	public function doReset(){
		if($_SESSION['step1'] != 1){//判断验证第一步是否完成
			header('location:'.U('pufinance/service/changePwd') );//未完成跳转到第一步
		}
		$pwd = pay_password(trim($_POST['pw']));
		$data['paypassword'] = $pwd['pwd'];
		$data['salt'] = $pwd['salt'];
		$update = M('pufinance_user')->where('uid='.$this->mid)->data($data)->save();
		if(empty($update)){
			echo 0;
		}else{
			unset($_SESSION['step1']);
			echo 1;
		}
	}
}