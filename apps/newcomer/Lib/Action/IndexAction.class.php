<?php

class IndexAction extends BaseAction {

    public function _initialize() {
        parent::_initialize();
    }

    public function news() {

        $id = intval($_REQUEST['id']);
        $map['document_id'] = $id;
        $order = 'mtime DESC, document_id DESC';
        $map['is_active'] = 1;
        $documentDao = D('Document');
        $document = $documentDao->where($map)->find();
        if (!$document) {
            $this->error('资讯不存在或已被删除！');
        }
        $id = $document['document_id'];

        $documentDao->execute('UPDATE ' . C('DB_PREFIX') . $documentDao->tableName . " SET readCount=readCount+1 WHERE document_id={$id} LIMIT 1");
        $document['readCount']++;
        $document['content'] = htmlspecialchars_decode($document['content']);
        $photos = D('Document')->where('is_active=1 AND isrecom=1 AND `icon` IS NOT NULL AND sid= ' . $this->sid)->field('icon,document_id')->order($order)->limit(4)->findAll();
        $this->assign('photos', $photos);
        $this->assign('document', $document);

        //获取校园官网logo
        $logo = M('newcomer_logo')->where('sid=' . $this->sid)->find();
        $this->assign($logo);
        $this->display();
    }

    public function index() {
        switch ($_REQUEST['cat']) {
            case 1:
                $map['category0'] = array("in", '1,2,3');
                break;
            case 2:
                $map['category0'] = array("in", '4,5,6');
                break;
            case 3:
                $map['category0'] = array("in", '7,8');
                break;
            default :
                $map['isrecom'] = 1;
        }
        $map['sid'] = $this->sid;
        $map['is_active'] = 1;
        $order = 'mtime DESC, document_id DESC';
        $documents = D('Document')->where($map)->order($order)->findPage(7);
        $this->assign($documents);

        $photos = D('Document')->where('is_active=1 AND isrecom=1 AND `icon` IS NOT NULL AND sid= ' . $this->sid)->field('icon,document_id')->order($order)->limit(4)->findAll();
        $this->assign('photos', $photos);

        //获取校园官网logo
        $logo = M('newcomer_logo')->where('sid=' . $this->sid)->find();
        $this->assign($logo);

        $this->display();
    }

    public function clist() {
        $map['sid'] = $this->sid;
        $map['is_active'] = 1;
        $_REQUEST['category'] && $map['category0'] = $_REQUEST['category'];
        $order = 'mtime DESC, document_id DESC';
        $documents = D('Document')->order($order)->where($map)->findPage(7);
        $this->assign($documents);
        $photos = D('Document')->where('is_active=1 AND isrecom=1 AND `icon` IS NOT NULL AND sid= ' . $this->sid)->field('icon,document_id')->order($order)->limit(4)->findAll();
        $this->assign('photos', $photos);

        //获取校园官网logo
        $logo = M('newcomer_logo')->where('sid=' . $this->sid)->find();
        $this->assign($logo);
        $this->display();
    }

    public function register() {

        $photos = $this->getDocList(3, 1, 4);
        $this->assign('photos', $photos);

        //验证码
        $opt_verify = $this->_isVerifyOn('register');
        if ($opt_verify)
            $this->assign('register_verify_on', 1);

        $this->display();
    }

//	public function doRegister()
//	{
//		// 验证码
//		$verify_option = $this->_isVerifyOn('register');
//		if ($verify_option && md5($_POST['verify']) != $_SESSION['verify'])
//			$this->error(L('error_security_code'));
//
//		// 参数合法性检查
//		$required_field = array(
//			'email'		=> 'Email',
//			'nickname'  => L('username'),
//			'password'	=> L('password'),
//			'repassword'=> L('retype_password'),
//		);
//		foreach ($required_field as $k => $v)
//			if (empty($_POST[$k]))
//				$this->error($v . L('not_null'));
//
//		if (!$this->isValidEmail($_POST['email']))
//			$this->error(L('email_format_error_retype'));
//		if (!$this->isValidNickName($_POST['nickname']))
//			$this->error(L('username_format_error'));
//		if (strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16 || $_POST['password'] != $_POST['repassword'])
//			$this->error(L('password_rule'));
//		if (!$this->isEmailAvailable($_POST['email']))
//			$this->error(L('email_used_retype'));
//
//		$value = t( msubstr( $_POST['intro']['newcomer_school'],0,70,'utf-8',false ) );
//		if(strlen($value)==0) {
//			$this->error("学校不能为空");
//		}
//
//		$value = t( msubstr( $_POST['intro']['newcomer_studentno'],0,70,'utf-8',false ) );
//		if(strlen($value)==0) {
//			$this->error("学号不能为空");
//		}
//
//		// 是否需要Email激活
//		$need_email_activate = intval(model('Xdata')->get('register:register_email_activate'));
//
//		// 注册
//		$data['email']     = $_POST['email'];
//		$data['password']  = md5($_POST['password']);
//		$data['uname']	   = t($_POST['nickname']);
//		$data['ctime']	   = time();
//		$data['is_active'] = $need_email_activate ? 0 : 1;
//
//		$data['sex']   	  = intval($_POST['sex']);
//		$data['province'] = 1;
//		$data['city']     = 3283;
//		$data['location'] = getLocation($data['province'], $data['city']);
//		$data['is_init']  = 1;
//
//		if (!($uid = D('User', 'home')->add($data)))
//			$this->error(L('reg_filed_retry'));
//
//		// 将用户添加到myop_userlog，以使漫游应用能获取到用户信息
//		$user_log = array(
//			'uid'		=> $uid,
//			'action'	=> 'add',
//			'type'		=> '0',
//			'dateline'	=> time(),
//		);
//		M('myop_userlog')->add($user_log);
//
//		// 同步至UCenter
//		if (UC_SYNC) {
//			$uc_uid = uc_user_register($_POST['nickname'],$_POST['password'],$_POST['email']);
//			//echo uc_user_synlogin($uc_uid);
//			if ($uc_uid > 0)
//				ts_add_ucenter_user_ref($uid,$uc_uid,$data['uname']);
//		}
//
//		if ($need_email_activate == 1) { // 邮件激活
//			$this->activate($uid, $_POST['email'], $invite_code);
//		} else {
//			// 置为已登陆, 供完善个人资料时使用
//			service('Passport')->loginLocal($uid);
//
//	    	$dao = D('Follow','weibo');
//
//	        // 默认关注的好友
//			$auto_freind = model('Xdata')->lget('register');
//			$auto_freind['register_auto_friend'] = explode(',', $auto_freind['register_auto_friend']);
//			foreach($auto_freind['register_auto_friend'] as $v) {
//				if (($v = intval($v)) <= 0)
//					continue ;
//				$dao->dofollow($v, $uid);
//				$dao->dofollow($uid, $v);
//			}
//
//			// 开通个人空间
//			$data['uid'] = $uid;
//			model('Space')->add($data);
//
//			//注册成功 初始积分
//			X('Credit')->setUserCredit($uid,'init_default');
//
//			$puser = D('UserProfile', 'home');
//			$puser->uid = $uid;
//			$puser->upintro();
//
//
//			$value = t( msubstr( $_POST['intro']['newcomer_school'],0,70,'utf-8',false ) );
//			if(strlen($value)>0) {
//				D('UserTag','home')->addUserTagByName( $value ,$uid ,0);
//			}
//			$value = t( msubstr( $_POST['intro']['newcomer_department'],0,70,'utf-8',false ) );
//			if(strlen($value)>0) {
//				D('UserTag','home')->addUserTagByName( $value ,$uid ,0);
//			}
//			$value="2012级";
//			D('UserTag','home')->addUserTagByName( $value ,$uid ,0);
//			redirect(U('home/Index/index'));
//
//		}
//	}
    //发送激活邮件
    public function activate($uid, $email, $invite = '', $is_resend = 0) {
        //设置激活路径
        $activate_url = service('Validation')->addValidation($uid, '', U('home/Public/doActivate'), 'register_activate', serialize($invite));

        $this->assign('url', $activate_url);

        //设置邮件模板
        $body = <<<EOD
	感谢您的注册!<br>

	请马上点击以下注册确认链接，激活您的帐号！<br>

	<a href="$activate_url" target='_blank'>$activate_url</a><br/>

	如果通过点击以上链接无法访问，请将该网址复制并粘贴至新的浏览器窗口中。<br/>

	如果你错误地收到了此电子邮件，你无需执行任何操作来取消帐号！此帐号将不会启动。
EOD;
        // 发送邮件
        global $ts;
        $email_sent = service('Mail')->send_email($email, "激活{$ts['site']['site_name']}帐号", $body);

        // 渲染输出
        if ($email_sent) {
            $email_info = explode("@", $email);
            switch ($email_info[1]) {
                case "qq.com" : $email_url = "mail.qq.com";
                    break;
                case "163.com" : $email_url = "mail.163.com";
                    break;
                case "126.com" : $email_url = "mail.126.com";
                    break;
                case "gmail.com" : $email_url = "mail.google.com";
                    break;
                default : $email_url = "mail." . $email_info[1];
            }

            $this->assign("uid", $uid);
            $this->assign('email', $email);
            $this->assign('is_resend', $is_resend);
            $this->assign("email_url", $email_url);
            $this->display('activate');
        } else {
            $this->assign('jumpUrl', U('home/Index/index'));
            $this->error(L('email_send_error_retry'));
        }
    }

    private function _isVerifyOn($type = 'login') {
        // 检查验证码
        if ($type != 'login' && $type != 'register')
            return false;
        $opt_verify = $GLOBALS['ts']['site']['site_verify'];
        return in_array($type, $opt_verify);
    }

    //检查Email地址是否合法
    public function isValidEmail($email) {
        if (UC_SYNC) {
            $res = uc_user_checkemail($email);
            if ($res == -4) {
                return false;
            } else {
                return true;
            }
        } else {
            return preg_match("/[_a-zA-Z\d\-\.]+@[_a-zA-Z\d\-]+(\.[_a-zA-Z\d\-]+)+$/i", $email) !== 0;
        }
    }

    //检查Email是否可用
    public function isEmailAvailable($email = null) {
        $return_type = empty($email) ? 'ajax' : 'return';
        $email = empty($email) ? $_POST['email'] : $email;

        $res = M('user')->where('`email`="' . $email . '"')->find();
        if (UC_SYNC) {
            $uc_res = uc_user_checkemail($email);
            if ($uc_res == -5 || $uc_res == -6) {
                $res = true;
            }
        }

        if (!$res) {
            if ($return_type === 'ajax')
                echo 'success';
            else
                return true;
        }else {
            if ($return_type === 'ajax')
                echo L('email_used');
            else
                return false;
        }
    }

    //检查昵称是否符合规则，且是否为唯一

    public function isValidNickName($name) {
        $return_type = empty($name) ? 'ajax' : 'return';
        $name = empty($name) ? t($_POST['nickname']) : $name;

        if (UC_SYNC) {
            $uc_res = uc_user_checkname($name);
            if ($uc_res == -1 || !isLegalUsername($name)) {
                if ($return_type === 'ajax') {
                    echo L('username_rule');
                    return;
                }
                else
                    return false;
            }
        } else if (!isLegalUsername($name)) {
            if ($return_type === 'ajax') {
                echo L('username_rule');
                return;
            }
            else
                return false;
        }

        if ($this->mid) {
            $res = M('user')->where("uname='{$name}' AND uid<>{$this->mid}")->count();
            $nickname = M('user')->getField('uname', "uid={$this->mid}");
            if (UC_SYNC && ($uc_res == -2 || $uc_res == -3) && $nickname != $name) {
                $res = 1;
            }
        } else {
            $res = M('user')->where("uname='{$name}'")->count();
            if (UC_SYNC && ($uc_res == -2 || $uc_res == -3)) {
                $res = 1;
            }
        }

        if (!$res) {
            if ($return_type === 'ajax')
                echo 'success';
            else
                return true;
        }else {
            if ($return_type === 'ajax')
                echo L('username_used');
            else
                return false;
        }
    }

}

?>