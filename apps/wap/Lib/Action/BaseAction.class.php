<?php
class BaseAction extends Action {
	// 站点名称
	protected $_title;
	// 分页使用
	protected $_page;
	protected $_item_count;
	// 来源类型
	protected $_from_type;
	protected $_type_wap;
	// 关注状态
	protected $_follow_status;
	// 当前URL
	protected $_self_url;
	//获取当前用户的token
	protected $tocken;
	//获取当前用户的token_secret
	protected $secret;

	public function _initialize() {
		header("Content-type: text/html; charset=utf-8");
		global $ts;
		
		// 站点名称
		$this->_title  = $ts['site']['site_name'] . ' H5';
		$this->assign('site_name', $this->_title);

		// 分页
		$_GET['page']  = $_POST['page'] ? intval($_POST['page']) : intval($_GET['page']);
		$this->_page   = $_GET['page'] > 0 ? $_GET['page'] : 1;
		$this->assign('page', $this->_page);
		$this->_item_count = 20;
		$this->assign('item_count', $this->_item_count);
		
		// 来源类型
		$this->_type_wap  = 1;
		$this->_from_type = array('0'=>'网站','1'=>'手机网页版','2'=>'Android客户端','3'=>'iPhone客户端');
		$this->assign('from_type', $this->_from_type);

		// 关注状态
		$this->_follow_status = array('eachfollow'=>'相互关注','havefollow'=>'已关注','unfollow'=>'未关注');
		$this->assign('follow_status', $this->_follow_status);

		// 当前URL
		$this->_self_url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		if (isset($_POST['key'])) {
			$this->_self_url .= "&key={$_POST['key']}";
			$this->_self_url .= isset($_POST['user']) ? '&user=1' : '&weibo=1';
		}
		$this->assign('self_url', $this->_self_url);

		// 是否为owner
		$_GET['uid'] = intval($_GET['uid']);
		$this->assign('is_owner', ($_GET['uid']==0 || $_GET['uid']==$ts['user']['uid']) ? '1' : '0');

		// 获取新通知
		$counts = X ( 'Notify' )->getCount ( $this->mid );
		$uid = $this->mid;
		$this->assign('news',$counts);
		$this->assign('user_id',$uid);
		Session::pause();
		return ;
	}

	//JS请求 登录状态并重新登录
	public function appLogin()
	{
		$uid = $_REQUEST['uid'];
		if($this->mid > 0)
		{
			echo json_encode(array('status'=>1,'msg'=>'您已登录！')); exit;
		}
		service('Passport')->loginLocal($uid,null,true);
		echo json_encode(array('status'=>2,'msg'=>'重新自动登录成功！'));exit;
	}
	
	/**
	 * @todo 在app端打开首页的时候获取用户信息进行认证，成功则写入session，失败则提示授权失败
	 * @param string oauth_token
	 * @param string oauth_token_secret
	 * @return bool / string
	 */
	protected function verifyToken()
	{
		$verifycode['oauth_token'] = h($_REQUEST['oauth_token']);
		$verifycode['oauth_token_secret'] = h($_REQUEST['oauth_token_secret']);
		$verifycode['type'] = 'location';
		if($login = M('login')->where($verifycode)->field('uid')->find() ){
			$this->mid = $login['uid'];
			$this->tocken = $verifycode['oauth_token'];
			$this->secret = $verifycode['oauth_token_secret'];
			//将认证信息写入SESSION
			$_SESSION['mid'] = $this->mid;
			$_SESSION['tocken'] = $this->tocken;
			$_SESSION['secret'] = $this->secret;
			addLogin($this->mid);
		}else{
			die('授权失败，请重新登录！') ;
		}
	}
	
	/**
	 * @todo 生成服务器认可的URL
	 * @param String $app
	 * @param String $mod
	 * @param String $act
	 * @param string $param
	 * @return string URL地址
	 */
	protected function _autoLoginUrl($app,$mod,$act,$param=''){
		$key = substr($this->secret, 5, 17);
		$num = time();
		$sign = md5($num.$key.$this->mid);
		return SITE_URL.'/kdl/'.$this->mid.$num.'/?uid='.$this->mid
		.'&num='.$num.'&sign='.$sign
		."&japp=$app&jmod=$mod&jact=$act".$param;
	}
        
        /**
         * @todo 对两个二维数组进行合同并进行自定义字段排序处理
         * @author zhuhaibing
         * @param array $fir_arr
         * @param array $sec_arr
         * @param string $key 排序的key值键
         * @return array $data
         * @example 
         *  $in = M('money_in')->limit(5)->select();
            $out = M('money_out')->field('out_uid,out_title as typeName,out_money as logMoney,out_ctime as ctime')->limit(5)->select();
            $this->arraySortNew($in, $out));
         * 
         */
        protected function arraySortNew($fir_arr , $sec_arr ,$key='ctime')
        {
            $sortColumns = array();
            $data = array_merge($fir_arr, $sec_arr);
            foreach($data as $k =>$v) 
            { 
                $sortColumns[$k] = $v[$key]; 
            } 
            array_multisort($data, SORT_DESC, $sortColumns); 
            return $data;
        }
	
}