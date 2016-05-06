<?php
/**
 * 用户管理中心
 * @author Nonant
 *
 */

class AccountAction extends Action
{
    var $pUser;

    function _initialize(){
        $this->pUser = D('UserProfile');
        $this->pUser->uid = $this->mid;

        // 是否启用个性化域名
        $is_domain_on = model('Xdata')->lget('siteopt');
        $is_domain_on = $is_domain_on['site_user_domain_on'];

        $menu[] = array( 'act' => 'index',		'name' => L('personal_profile') );
		//$menu[] = array( 'act' => 'avatar',		'name' => L('face_setting') );
        $menu[] = array( 'act' => 'privacy',	'name' => L('private_setting') );
        if ($is_domain_on == 1)
        	$menu[] = array( 'act' => 'domain',	'name' => L('self_domain') );
        $menu[] = array( 'act' => 'security',	'name' => L('account_safe') );
        //$menu[] = array( 'act' => 'bind',		'name' => L('outer_bind') );
        //$menu[] = array( 'act' => 'credit',		'name' => L('integral_rule') );
        Addons::hook('home_account_tab', array('menu'=>& $menu));
        $menu[] = array( 'act' => 'myprint',		'name' => '打印证书' );
        $menu[] = array( 'act' => 'recharge',		'name' => '充值中心' );
        $menu[] = array( 'act' => 'cx',		'name' => '诚信系统' );
        $this->assign('accountmenu', $menu);
    }

    private function _getJoinList() {
        $map_join['status'] = 2;
        $map_join['uid'] = $this->mid;
        $eventIds = M('event_user')->field('eventId')->where($map_join)->findAll();
        $eids = getSubByKey($eventIds, 'eventId');
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['is_school_event'] = $this->user['sid'];
        $map['id'] = array('in', $eids);
        return M('event')->field('id,title')->where($map)->findAll();
    }

    public function myprint(){
        $action = t($_GET['action']);
        $template = 'editPrint';
        switch ($action) {
            case 'addPrint1':
                $this->assign('type', 'add');
                $this->assign('orga', 1);
                $this->assign('event', $this->_getJoinList());
                //站点信息
                $config = D('SchoolWeb','event')->getConfigCache($this->user['sid']);
                $this->assign('webconfig', $config);
                break;
            case 'addPrint2':
                $this->assign('type', 'add');
                $this->assign('orga', 0);
                $this->assign('event', $this->_getJoinList());
                break;

            default:
                $map['sid'] = $this->user['sid'];
                $map['uid'] = $this->mid;
                $res = M('event_print')->where($map)->order('id desc')->findPage(10);
                $this->assign($res);
                $action = 'list';
                $template = 'myprint';
                break;
        }
        $this->assign('action', $action);
        $this->display($template);
    }

    public function doAddPrint() {
        $data['is_orga'] = ($_POST['is_orga']) ? 1 : 0;
        if ($data['is_orga'] == 0) {
            //参数合法性检查
            $data['title'] = t($_POST['title']);
            $required_field = array(
                'title' => '标题',
            );
            foreach ($required_field as $k => $v) {
                if (empty($data[$k]))
                    $this->error($v . '不可为空');
            }
            $data['content'] = t(h($_POST['content']));
        }

        if (is_array($_POST['checkbox'])) {
            $data['eids'] = implode(',', $_POST['checkbox']);
        } else {
            $this->error('请选择活动！');
        }
        $data['cTime'] = time();
        $data['uid'] = $this->mid;
        $data['sid'] = $this->user['sid'];

        $id = M('event_print')->add($data);
        if (!$id) {
            $this->error('抱歉：添加失败，请稍后重试');
            exit;
        }
        $this->assign('jumpUrl', U('home/Account/myprint'));
        $this->success('添加成功');
    }

    public function editPrint() {
        $this->assign('type', 'edit');
        $_GET['id'] = intval($_GET['id']);
        if ($_GET['id'] <= 0)
            $this->error('参数错误');
        $map['id'] = $_GET['id'];
        $map['uid'] = $this->mid;
        $obj = M('event_print')->where($map)->find();
        if (!$obj)
            $this->error('无此打印记录');
        if ($obj['eids']) {
            $obj['eids'] = explode(',', $obj['eids']);
        } else {
            $obj['eids'] = array();
        }
        $this->assign($obj);
        $this->assign('event', $this->_getJoinList());
        $orga = 0;
        if ($obj['is_orga']) {
            //站点信息
            $config = D('SchoolWeb','event')->getConfigCache($this->user['sid']);
            $this->assign('webconfig', $config);
            $orga = 1;
        }
        $this->assign('orga', $orga);
        $this->display();
    }

    public function doEditPrint() {
        $id = intval($_POST['id']);
        $map['id'] = $id;
        $map['uid'] = $this->mid;
        if (!$obj = M('event_print')->where($map)->find()) {
            $this->error('打印记录不存在或已删除');
        }
        if (!$obj['is_orga']) {
            $data['title'] = t($_POST['title']);
            //参数合法性检查
            $required_field = array(
                'title' => '标题',
            );
            foreach ($required_field as $k => $v) {
                if (empty($data[$k]))
                    $this->error($v . '不可为空');
            }
            $data['content'] = t(h($_POST['content']));
        }

        if (is_array($_POST['checkbox'])) {
            $data['eids'] = implode(',', $_POST['checkbox']);
        } else {
            $this->error('请选择活动！');
        }
        $data['cTime'] = time();

        $uid = M('event_print')->where('id = ' . $id)->save($data);
        if (!$uid) {
            $this->error('抱歉：修改失败，请稍后重试');
            exit;
        }
        $this->assign('jumpUrl', U('home/Account/myprint'));
        $this->success('修改成功');
    }

    public function doDelPrint() {
        $id = intval($_REQUEST['id']);
        if ($id <= 0) {
            $this->error('操作失败');
        }
        $map['id'] = $id;
        $map['uid'] = $this->mid;
        $dao = M('event_print');
        if ($dao->where($map)->delete()) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    public function printView() {
        $id = intval($_REQUEST['id']);
        $map['id'] = $id;
        $map['uid'] = $this->mid;
        $obj = M('event_print')->where($map)->find();
        if (!$obj) {
            $this->error('打印记录不存在或已被删除！');
        }
        $this->assign($obj);
        $map = array();
        $map['isDel'] = 0;
        $map['is_school_event'] = $this->user['sid'];
        $map['id'] = array('in', $obj['eids']);
        $list = M('event')->where($map)->findAll();
        $this->assign('list', $list);
        $this->display();
    }

    // 有不存在的ACTION操作的时候执行
    protected function _empty() {
    	if (empty($_POST)) {
    		$this->display('addons');
		}
	}

    //个人资料
    function index(){
        $data['userInfo']         = $this->pUser->getUserInfo();
        $data['userTag']          = D('UserTag')->getUserTagList($this->mid);
        //$data['userFavTag']       = D('UserTag')->getFavTageList($this->mid);

        $sid = $data['userInfo']['detail']['sid'];
        if($sid && $data['userInfo']['detail']['sid1']){
            $this->assign('sids', $data['userInfo']['detail']['sid1']);
            $this->assign('sName', tsGetSchoolName($data['userInfo']['detail']['sid1']));
        }
        $this->assign('editSid', $sid);
        $this->assign( $data );
        $this->setTitle(L('setting').' - '.L('personal_profile'));
        $this->display();
    }

    //更新资料
    function update(){
    	S('S_userInfo_'.$_SESSION['userInfo']['uid'],null);
		$nickname = $_REQUEST['nickname'];

		//检查禁止注册的用户昵称
		$audit = model('Xdata')->lget('audit');
		if($audit['banuid']==1){
			$bannedunames = $audit['bannedunames'];
			if(!empty($bannedunames)){
				$bannedunames = explode('|',$bannedunames);
				if(in_array($nickname,$bannedunames)){
					exit(json_encode(array('message'=>'这个昵称禁止注册','boolen'=>0)));
				}
			}
		}

        exit( json_encode($this->pUser->upDate( t($_REQUEST['dotype']) )) );
    }

    //绑定帐号
    function bind(){
   	    $user = M('user')->where('uid='.$this->mid)->field('email')->find();
   	    $replace = substr($user['email'],2,-3);
   	    for ($i=1;$i<=strlen($replace);$i++){
   	    	$replacestring.='*';
   	    }
        $data['email'] = str_replace(  $replace, $replacestring ,$user['email'] );
        $bindData = array();
        Addons::hook('account_bind_after',array('bindInfo'=>&$bindData));
        $data['bind']  = $bindData;
   	    $this->assign($data);
   	    $this->setTitle(L('setting').' - '.L('outer_bind'));
    	$this->display();
    }

    //教育、工作情况
    function addproject(){
        $pUserProfile = D('UserProfile');
        $pUserProfile->uid = $this->mid;
        $strType = t( $_POST['addtype'] );
        if( $strType =='education' ){
            $data['school'] = msubstr( t($_POST['school']),0,70,'utf-8',false );
            $data['classes']= msubstr( t($_POST['classes']),0,70,'utf-8',false );
            $data['year']   = $_POST['year'];
            if( empty( $data['school'] ) ){
                $return['message']  = L('schoolname_nonull');
                $return['boolen']   = "0";
                exit( json_encode($return) );
            }
        }elseif ($strType == 'career' ){
            $data['company']   = msubstr( t($_POST['company']),0,70,'utf-8',false );
            $data['position']  = msubstr( t($_POST['position']),0,70,'utf-8',false );
            $data['begintime'] = intval( $_POST['beginyear'] ).'-'.intval($_POST['beginmonth']);
            $data['endtime']   = ( $_POST['nowworkflag'] ) ? L('now') : intval( $_POST['endyear'] ).'-'.intval($_POST['endmonth']);
            //2011-03-11 添加
            $date_begin = explode("-", $data['begintime']);
            $date_end = explode("-", $data['endtime']);

            $begin = mktime(0, 0, 0, $date_begin[1], 0, $date_begin[0]);
            $end = mktime(0, 0, 0, $date_end[1], 0, $date_end[0]);

            if( empty( $data['company'] ) ){
                $return['message']  = L('companyname_nonull');
                $return['boolen']   = "0";
                exit( json_encode($return) );
            }

            if($data['endtime'] != L('now') && $begin > $end) {
            	$return['message'] = L('start_time_later');
            	$return['boolen'] = "0";
            	exit(json_encode($return));
            }
        }
        $data['id'] = $pUserProfile->dosave($strType,$data,'list',true);
        if($data['id']){
            $data['addtype'] = $strType;
            $return['message']  = L('companyname_nonull');
            $return['boolen']   = "1";
            $return['data']   = $data;
            exit( json_encode($return) );
        }
    }

    //个人标签
    function doUserTag(){
    	$strType = h($_REQUEST['type']);
        if( $strType!='deltag' && !$_POST['tagname'] && !$_POST['tagid'] ){
    		echo  json_encode( array('code'=>'3') );
    		exit();
    	}
    	if ($strType=='deltag'){
    		echo D('UserTag')->doDel(intval($_POST['tagid']),$this->mid);
    		exit();
    	}
    	$count = M( 'UserTag' )->where( 'uid='.$this->mid )->count();
    	if( $count >=10 ){
    		echo  json_encode( array('code'=>'2') );
    		exit();
    	}
    	if($strType=='addByname'){
    		$_POST['tagname'] = str_replace('，', ',', $_POST['tagname']);
    		$_POST['tagname'] = str_replace(' ', ',', $_POST['tagname']);
    		echo D('UserTag')->addUserTagByName( $_POST['tagname'] ,$this->mid ,$count);
    	}
    	if ($strType=='addByid'){
    		echo D('UserTag')->addUserTagById( $_POST['tagid'] ,$this->mid);
    	}
    }

    //头像处理
    function avatar(){
        $type = $_REQUEST['t'];
        $pAvatar = D('Avatar');
        $pAvatar ->uid = $this->mid;
        if( $type == 'upload' ){
            echo $pAvatar->upload();
        }elseif ( $type == 'save'){
            $pAvatar->dosave($this->mid);
        }elseif ( $type == 'camera'){
            $pAvatar->getcamera();
        }else{
        	$this->display();
        }
    }

    //邀请
    public function invite() {
    	if($_POST){
    		if( model('Invite')->getReceiveCode( $this->mid ) ){
    			$this->assign('jumpUrl',U('home/Account/invite'));
    			$this->success(L('invite_code_success'));
    			redirect( U('home/Account/invite') );
    		}else{
    			$this->error(L('invite_code_error'));
    		}
    	}else{
	    	$invitecode = model('Invite')->getInviteCode( $this->mid );
	    	$receivecount = model('Invite')->getReceiveCount( $this->mid );
			$this->assign('receivecount',$receivecount);
			$this->assign('list',$invitecode);
			$this->setTitle(L('invite'));
	    	$this->display();
    	}
    }

    public function doInvite() {
    	$_POST['email'] = t($_POST['email']);
    	if ( !isValidEmail($_POST['email']) ) {
    		echo -1; //错误的Email格式
    		return ;
    	}

    	$map['email']  = $_POST['email'];
    	$map['is_active'] = 1;
    	if ( $user = M('user')->where($map)->find() ) {
    		echo $user['id']; //被邀请人已存在
    		return ;
    	}
    	unset($map);

    	//添加验证数据 之1
    	$validation = service('Validation')->addValidation($this->mid, $_POST['email'], U('home/Public/inviteRegister'), 'test_invite');
    	if (!$validation) {
    		echo 0;
    		return ;
    	}

    	//发送邀请邮件
    	global $ts;
    	$data['title'] = array(
    		'actor_name'	=> $ts['user']['uname'],
    		'site_name'		=> $ts['site']['site_name'],
    	);
    	$data['body']  = array(
    		'email'			=> $_POST['email'],
    		'actor'			=> '<a href="' . U('home/Space/index',array('uid'=>$ts['user']['uid'])) . '" target="_blank">' . $ts['user']['uname'] . '</a>',
    		'site'			=> '<a href="' . U('home') . '" target="_blank">' . $ts['site']['site_name'] . '</a>',
    	);
    	$tpl_record = model('Template')->parseTemplate('invite_register', $data);
    	unset($data);

    	if ($tpl_record) {
    		//echo -2; //邀请成功

    		//添加验证数据 之2
    		$map['target_url'] = $validation;
    		M('validation')->where($map)->setField('data', serialize(array('tpl_record_id'=>$tpl_record)));
    		echo $validation;
    	}else {
    		echo 0;
    	}
    }

	//邀请已存在的用户
    public function inviteExisted() {
    	$this->assign('uid', intval($_GET['uid']));
    	$this->display();
    }

    //删除资料
    function delprofile(){
        $intId = intval( $_REQUEST['id'] );
        $pUserProfile = D('UserProfile');
        echo $pUserProfile->delprofile( $intId ,$this->mid );
    }

    //帐号安全
    public function security() {
        $this->assign('restSec',D('UserMobile')->getRestSec($this->mid));
    	// UCenter账号同步失败则重新设置
    	if(UC_SYNC){
    		global $ts;
	    	$uc_user_ref = ts_get_ucenter_user_ref($this->mid);
	    	if(!$uc_user_ref){
	    		if(uc_user_checkname($ts['user']['uname']))$this->assign('uc_username',$ts['user']['uname']);
	    		if(uc_user_checkemail($ts['user']['email']))$this->assign('uc_email',$ts['user']['email']);
	    		$this->assign('set_ucenter_username',1);
	    	}
    	}

    	$this->setTitle(L('setting').' - '.L('account_safe'));
    	$this->display();
    }

    //隐私设置
    function privacy(){
    	if($_POST){
//                var_dump($_POST);die;
    		$r = D('UserPrivacy')->dosave($_POST['userset'],$this->mid);
    	}
    	$userSet = D('UserPrivacy')->getUserSet($this->mid);
    	$blacklist = D('UserPrivacy')->getBlackList($this->mid);
    	$this->assign('userset',$userSet );
    	$this->assign('blacklist',$blacklist );
    	$this->setTitle(L('setting').' - '.L('private_setting'));
    	$this->display();

    }


    //设置黑名单
    function setBlackList(){
    	if( D("UserPrivacy")->setBlackList( $this->mid , t($_POST['type']) , intval($_POST['uid']) ) ){
    		echo '1';
    	}else{
    		echo '0';
    	}
    }

    //个性化域名
    function domain(){
    	// 是否启用个性化域名
        $is_domain_on = model('Xdata')->lget('siteopt');
        if ($is_domain_on['site_user_domain_on'] != 1)
        	$this->error(L('self_domain_off'));

    	if($_POST){
    		$domain = h($_POST['domain']);

    		if( !ereg('^[a-zA-Z][a-zA-Z0-9]+$', $domain)){
    			$this->error(L('domain_english_only'));
    		}

    		if( strlen($domain)<2 ){
    			$this->error(L('domain_short'));
    		}

    		if( strlen($domain)>20 ){
    			$this->error(L('domain_long'));
    		}

			//检查已被禁用的个性化域名
			$audit = model('Xdata')->lget('audit');
			if($audit['banuid']==1){
				$banned_domains = $audit['banneddomains'];
				if(!empty($banned_domains)){
					$banned_domains = explode('|',$banned_domains);
					if( in_array($domain,$banned_domains)){
						$this->error('该个性域名已被禁用');
					}
				}
			}

    		if( M('user')->where("uid!={$this->mid} AND domain='{$domain}'")->count()){
    			$this->error(L('people_used'));
    		}else{
    			M('user')->setField('domain',$domain,'uid='.$this->mid);
    			$this->success(L('setting_success'));
    		}
    	}else{
	    	$user = M('user')->where('uid='.$this->mid)->find();
	    	$data['userDomain'] = $user['domain'];
	    	$this->assign($data);
	    	$this->setTitle(L('setting').' - '.L('self_domain'));
	    	$this->display();
    	}
    }

    //修改密码
    public function doModifyPassword() {
        if(!canChangePass($this->mid)){
            $this->error("该账号不可修改密码");
        }
        if (strlen($_POST['password']) < 6 || strlen($_POST['password']) > 16 || $_POST['password'] != $_POST['repassword']) {
            $this->error(L('password_rule'));
        }
        if ($_POST['password'] == $_POST['oldpassword']) {
            $this->error(L('password_old_sameas_new'));
        }
        if ($_SESSION['userInfo']['password']==md5($_POST['oldpassword']) || $_SESSION['userInfo']['password']==  codePass($_POST['oldpassword'])) {
            $dao = M('user');
            $newPass = codePass($_POST['password']);
            $map['uid'] = $this->mid;
            if ($dao->where($map)->setField('password',$newPass)) {
                S('S_userInfo_' . $this->mid, null);
                $_SESSION['userInfo']['password'] = $newPass;
                $_SESSION['md5pass'] = false;
                $this->success(L('save_success'));
            } else {
                $this->error(L('save_error'));
            }
        } else {
            $this->error(L('oldpassword_wrong'));
        }
    }

    //修改帐号
    public function modifyEmail() {
        $_POST['email'] = t($_POST['email']);
        if (!isValidEmail($_POST['email'])) {
            $this->error('Email地址不能为空，且格式必须正确');
        }
        $email = $_POST['email'];
        $res = M('user')->where("`email2`= '$email'")->field('uid')->find();
        if ($res) {
            $this->error('新Email已存在');
        }
        $password = t($_POST['password']);
        if (md5($password) != $this->user['password'] && codePass($password) != $this->user['password']) {
            $this->error('登录密码不正确');
        }
        $map['uid'] = $this->mid;
        if (M('user')->where($map)->setField('email2', $_POST['email'])) {
            S('S_userInfo_'.$this->uid, null);
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    // 设置UCenter账号
    public function doModifyUCenter() {
    	include_once(SITE_PATH.'/api/uc_client/uc_sync.php');
    	if(UC_SYNC){
	    	$uc_user_ref = ts_get_ucenter_user_ref($this->mid);
	    	if(!$uc_user_ref){
	    		$username = $_POST['username'];
	    		$email = $_POST['email'];
	    		$password = $_POST['password'];
	    		if(uc_user_checkname($username) != 1 || !isLegalUsername($username) || M('user')->where("uname='{$username}' AND uid<>{$this->mid}")->count())$this->error('用户名不合法或已经存在，请重新设置用户名');
	    		if(uc_user_checkemail($email) != 1 || M('user')->where("uname='{$email}' AND uid<>{$this->mid}")->count())$this->error('Email不合法或已经存在，请重新设置Email');
	    		global $ts;
	    		if(md5($password) != $ts['user']['password'])$this->error(L('password_error_retype'));
	    		$uc_uid = uc_user_register($username,$password,$email);
	    		if($uc_uid>0){
	    			ts_add_ucenter_user_ref($this->mid,$uc_uid,$username);
					$this->assign('jumpUrl', U('home/Account/security'));
					$this->success(L('ucenter_setting_success'));
	    		}else{
	    			$this->error(L('ucenter_setting_error'));
	    		}
	    	}else{
	    		redirect(U('home/Account/security'));
	    	}
    	}else{
    		redirect(U('home/Account/security'));
    	}
    }

    //积分规则
    public function credit(){
    	$credit = X('Credit');
    	$credit_type  = $credit->getCreditType();
    	$credit_rules = $credit->getCreditRules();

    	$this->assign('credit_type',$credit_type);
    	$this->assign('credit_rules',$credit_rules);
    	$this->setTitle(L('setting').' - '.L('integral_rule'));
    	$this->display();
    }

	public function weiboshow(){
		$this->display();
	}

	public function weiboshare(){
		$this->display();
	}

    public function mobileCode() {
        $pass = t($_POST['pass']);
        if (md5($pass) != $this->user['password'] && codePass($pass) != $this->user['password']) {
            $this->error('登录密码不对');
        }
        $mobile = t($_POST['mobile']);
        if (!isValidMobile($mobile)) {
            $this->error('输入合法的11位手机号码');
        }
        /*
        $map['mobile'] = $mobile.'';
        $hasMobile = M('user')->where($map)->field('uid')->find();
        if($hasMobile){
            $this->error('该手机号码已绑定');
        }
        */
        $daoUserMobile = D('UserMobile', 'home');
        $res = $daoUserMobile->addRow($this->mid,$mobile,'A');
        if(!$res){
            $this->error($daoUserMobile->getError());
        }
        $this->success('操作成功');
    }
//
//    public function mobileBind() {
//        $code = intval($_POST['code']);
//        $mobile = t($_POST['mobile']);
//        $res = D('UserMobile')->bind($this->mid,$mobile,$code);
//        if($res){
//            echo 1; exit;
//        }
//        echo 0; exit;
//    }

    public function mobileBind() {
        $mobile = t($_POST['mobile']);
        if (!isValidMobile($mobile)) {
            $this->error('手机号码不能为空，且格式必须正确');
        }
        /*
        //$res = M('user')->where("`mobile`= '$mobile'")->field('uid')->find();
        if ($res) {
            $this->error('该手机号码已被使用');
        }
        */
        // 解绑存在的手机号
        $res = M('user')->field('uid')->where('mobile="'.$mobile.'" AND uid<>'.$this->mid)->save(array('mobile'=>''));
        $dateTime = date('Y年m月d日 H:i');
        $msg = '您绑定的'.$mobile.'号码于'.$dateTime.'解绑，若非本人操作，请联系PU客服：4008788593';
        // 发送解绑通知
        foreach ($res as $ck=>$cv) {
            add_notify(array('title'=>$msg,$cv['uid'],'mobile_unbind'));
        }
        // 重新绑定手机号
        $code = intval($_POST['code']);
        $result = D('UserMobile', 'home')->bind($this->mid, $mobile, $code);
        if ($result) {
            $this->success('操作成功');
        } else {
            $this->error('验证码错误');
        }
    }

    public function recharge(){
        $this->user['money'] = Model('Money')->getMoneyCache($this->mid);
        $userInfoCache = $this->get('userInfoCache');
        $userInfoCache['money'] = $this->user['money'];
        $this->assign('user',$this->user);
        $this->assign('userInfoCache',$userInfoCache);
        $this->setTitle('充值中心');
        $this->display();
    }
    //手机充值页面
    public function mrecharge(){
        $this->user['money'] = Model('Money')->getMoneyCache($this->mid);
        $userInfoCache = $this->get('userInfoCache');
        $userInfoCache['money'] = $this->user['money'];
        $this->assign('user',$this->user);
        $this->assign('userInfoCache',$userInfoCache);
        $this->setTitle('充值中心');
        $this->display();
    }

    /**
    * 充值、支付
    */
    public function goPay() {
        $_POST['money'] = t($_POST['money']);
        $money = $_POST['money']*100;
        if ($money <= 0)
            $this->error ('请输入充值金额');
        $order = array(
            'product_name' => "PU充值" . $_POST['money'] . "元",
            'order_price' => $money,
            'attach' => $this->mid,
            'return_url' => U('home/Account/rechargeList'),
            'payName' => t($_POST['payName']),
            //'return_url' => 'http://'.$_SERVER[HTTP_HOST].'/pay',
            //'notify_url' => 'http://'.$_SERVER[HTTP_HOST].'/pay',
        );
        service('Pay')->payUrl($order);
        exit;
    }

    public function rechargeList(){
        if(isset($_GET['r'])){
            $userInfoCache = $this->get('userInfoCache');
            $userInfoCache['money'] = Model('Money')->getMoneyCache($this->mid);
            $this->assign('userInfoCache',$userInfoCache);
        }
        $list = M('money_in')->where('uid='.$this->mid)->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->display('rechargeList');
    }

    public function consumeList(){
        $list = M('money_out')->where('out_uid='.$this->mid)->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function cx(){
        $config = D('SchoolWeb','event')->getConfigCache($this->user['sid']);
        $this->assign('schoolConfig',$config);
        $this->setTitle('诚信系统');
        $this->display();
    }

}
