<?php
//根据id获取学校或者院的名称
function get_info_by_sid($sid=0){
	$data = array();
	$data =  M('school')->field('title')->where('id='.$sid)->find();
	return !empty($data['title']) ? $data['title'] : '';

}

//获取诚信值
function get_eracity_value($attend, $total){
	if($total == 0 || $attend == 0){
		return '0%';
	}
	return ceil($attend * 100 / $total).'%';
}

//获取省份数据
function get_provinces($id=''){
	$provinces = array();
	if(empty($id)){
		$provinces = M('province')->field('id,title')->select();
	}else{
		$provinces = M('province')->field('id,title')->where('id='.$id)->find();
	}
	return $provinces;
}

//获取城市通过Pid数据
function get_citys($pid=0){
	if(empty($pid)){
		return M('citys')->select();
	}
	return M('citys')->field('id,city')->where('pid='.$pid)->select();
}

//获取学校或者院系的数据
function get_schools($pid=0){
	return M('school')->field('id,title')->where('pid='.$pid)->order('display_order asc')->select();
}

//数据格式为select标签中的option字符串
function option_format($data=array(), $value='id',$text='name',$select_id=0){
	if(!is_array($data)){
		return '';
	}
	$options = '';
	foreach($data as $item){
		if($select_id == $item[$value]){
			$options .= "<option value=\"{$item[$value]}\" selected >{$item[$text]}</option>";
		}else{
			$options .= "<option value=\"{$item[$value]}\">{$item[$text]}</option>";
		}

	}
	return $options;
}

/**
 * 获取PU金明细类型显示名称
 *
 * @param string $type 类型code 格式：act_class
 *                     目前act有：lend（借）、repay（还）、upcredit（提额）、downcredit（降额）
 *                     class有：pumoney（PU币）、各银行机构ID、wechat（微信）、alipay（支付宝）
 *                              all_sys（系统操作总额度）、all_bag（总额度包）、free_sys（系统操作免息额度）
 *                              free_bag（免息额度包）
 *
 * @return string
 */
function get_credit_log_type($type = null)
{
    list($act, $class) = explode('_', $type, 2);
    switch ($act) {
        case 'lend':    // 借
            if ($class == 'pumoney') {
                return '借款（PU币）';
            } else {    // 银行名称
                return '借款' . '（' . get_bank($class) . '）';
            }
            break;
        case 'repay':   // 还
            if (strpos($class, 'overrule') === 0) {
				list($a, $subclass) = explode('_', $class);
				if ($subclass == 'pumoney') {
					return '借款（PU币驳回）';
				} else {
					return '借款（' . get_bank($subclass) . '驳回）';
				}
			} elseif (strpos($class, 'deduct') === 0) {
					list($a, $subclass) = explode('_', $class);
					if ($subclass == 'pumoney') {
						return '还款（PU币系统扣款）';
					} else {
						return '还款（' . get_bank($subclass) .'系统扣款）';
					}
            } else {
                switch ($class) {
					case 'sys':
						return '还款（系统操作）';
						break;
                    case 'pumoney':
                        return '还款（PU币）';
                        break;
					case 'pucredit':
						return '还款（'.L('finance_name').'）';
						break;
                    case 'wechat':
                        return '还款（微信）';
                        break;
                    case 'alipay':
                        return '还款（支付宝）';
                        break;
                }
            }
            break;
        case 'upcredit':    // 提额
            switch ($class) {
                case 'all_sys':
                    return '提额（总额度系统操作）';
                    break;
                case 'all_bag':
                    return '提额（总额度提额包）';
                    break;
                case 'free_sys':
                    return '提额（免息额度系统操作）';
                    break;
                case 'free_bag':
                    return '提额（免息额度提额包）';
                    break;
            }
            break;
        case 'downcredit':  // 降额
            if ($class == 'all_sys') {
                return '降额（总额度系统操作）';
            } elseif ($class == 'free_sys') {
                return '降额（免息额度系统操作）';
            }
            break;
    }
    return '';
}

/**
 * 通过ID获取银行信息
 * @param null|string $id 银行ID
 *
 * @return array|string
 */
function get_bank($id = null, $key = 'bank_name')
{
    $bank = D('PufinanceBankOrg', 'pufinance')->getBankInfoById($id);

    return $key === null ? $bank : (isset($bank[$key]) ? $bank[$key] : '');
}


/**根据bankid获取银行卡信息
 * @param $bankid
 * @return array()
 * @todo
 */
function get_order_bank($bankid)
{
	return D('PufinanceBankcard','pufinance')->getOrderBank($bankid);
}

//提额包类型
function get_amount_type($type=0){
	switch($type){
		case 0:
			$title = '总额度';
			break;
		case 1:
			$title = '免息额度';
			break;
		default :
			$title = 'unknown';
	}
	return $title;
}

//提额包领取状态
function get_amount_receive_status($is_receive=null, $etime=null, $receive_time=null){
	if(isset($is_receive) && $is_receive == 0){
		if(time() <= $etime){
			return '待领取';
		}
		return '已过期';
	}
	if(isset($is_receive) && $is_receive == 1){
		return date('Y-m-d H:i:s', $receive_time).'已领取';
	}
}

//pu金状态
function get_credit_status($status=0){
	switch($status){
		case 0:
			$title = '初始';
			break;
		case 1:
			$title = '白名单';
			break;
		case 2:
			$title = '黑名单';
			break;
		default :
			$title = 'unknown';
	}
	return $title;
}

/**
 * @param 获取单个搜索条件
 * @return 单个条件
 */
function get_search_key($table_name,$key_name = 'k')
{
	$key = '';
	// 为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
	if (isset($_REQUEST[$key_name]) && !empty($_REQUEST[$key_name]))
	{
		if ($_GET[$key_name])
		{
			$key = html_entity_decode(urldecode($_GET[$key_name]), ENT_QUOTES);
		}
		elseif ($_POST[$key_name])
		{
			$key = $_POST[$key_name];
		}
		// 关键字不能超过30个字符
		if (mb_strlen($key, 'UTF8') > 30)
			$key = mb_substr($key, 0, 30, 'UTF8');
		$_SESSION[$table_name.'_search_' . $key_name] = serialize($key);
	}
	else if (is_numeric($_GET[C('VAR_PAGE')]))
	{
		$key = unserialize($_SESSION[$table_name.'_search_' . $key_name]);
	}
	else
	{
		unset($_SESSION[$table_name.'_search_' . $key_name]);
	}
	return trim($key);
}

/**
 * 计算借款利息
 *
 * （总额本金 - 免息额）* 期数 * 30天 * 日利息 进一
 *
 * @param float $amount		 借款金额
 * @param float $free_amount 免息金额
 * @param integer $stage	 分期数
 *
 * @return float 利息
 */
function calc_interest($amount, $free_amount, $stage)
{
	$rate = 4.5 / 10000 * 30;  // 每期利率 = 日利率 * 天数
    $amount = bcsub($amount, $free_amount, 2);  // 利息计算基数
    $interest = bcmul($amount, $rate, 6);       // 基数 * 每期利率
	return ceil(bcmul($interest, $stage * 100, 2)) / 100;
}

//提现审核状态
function get_withdraw_status($status=0){
	switch($status){
		case 0:
			$title = '待审核';
			break;
		case 1:
			$title = '已审核';
			break;
		case 2:
			$title = '未通过';
			break;
		default :
			$title = 'unknown';
	}
	return $title;
}

//支付密码
function pay_password($pwd,$salt=null){
	if(isset($salt)){
		return md5(md5($pwd).$salt);
	}else{
		$salt = mt_rand(100000,999999);
		return array('pwd'=>md5(md5($pwd).$salt),'salt'=>$salt);
	}

}

/**
 * RSA签名
 * @param string $data 待签名数据
 * @param string $private_key_path 商户私钥文件路径
 *
 * @return string 签名结果
 */
function rsa_sign($data, $private_key_path)
{
    $priKey = file_get_contents($private_key_path);
    $res = openssl_get_privatekey($priKey);
    openssl_sign($data, $sign, $res);
    openssl_free_key($res);
    //base64编码
    $sign = base64_encode($sign);
    return $sign;
}

/**
 * RSA验签
 * @param string $data 待签名数据
 * @param string $public_key_path 支付宝的公钥文件路径
 * @param string $sign 要校对的的签名结果
 *
 * @return boolean  验证结果
 */
function rsa_verify($data, $public_key_path, $sign)  {
    $pubKey = file_get_contents($public_key_path);
    $res = openssl_get_publickey($pubKey);
    $result = (bool)openssl_verify($data, base64_decode($sign), $res);
    openssl_free_key($res);
    return $result;
}

/**
 * 获取第三方借贷机构信息
 *
 * @param integer $invest_id ID
 * @param string $key 字段
 *
 * @return string
 */
function get_invest($invest_id, $key = 'name')
{
    $invest = D('PufinanceInvestOrg')->getInvestOrgInfoById($invest_id);
    return isset($invest[$key]) ? $invest[$key] : '';
}

function order_status($status=null){
	$status_titles = array(
		0 => '待审核',
		1 => '已驳回',
		2 => '待放款',
		3 => '放款中',
		4 => '待还款',
		5 => '已还清',
	);
	if(is_null($status)){
		return $status_titles;
	}else{
		return isset($status_titles[$status]) ? $status_titles[$status] : '';
	}
}

//订单类型
function order_type($type=null){
	$types = array(
		1 => '借款',
		2 => '消费',
		3 => '提现',
	);
	if(is_null($type)){
		return $types;
	}else{
		return isset($types[$type]) ? $types[$type] : '';
	}

}

/**
 * Provides functionality for array_column() to projects using PHP earlier than
 * version 5.5.
 *
 * @copyright (c) 2015 WinterSilence (http://github.com/WinterSilence)
 * @license       MIT
 */
if (!function_exists('array_column')) {

	/**
	 * Returns an array of values representing a single column from the input
	 * array.
	 *
	 * @param array $array     A multi-dimensional array from which to pull a
	 *                         column of values.
	 * @param mixed $columnKey The column of values to return. This value may
	 *                         be the integer key of the column you wish to retrieve, or it may be
	 *                         the string key name for an associative array. It may also be NULL to
	 *                         return complete arrays (useful together with index_key to reindex
	 *                         the array).
	 * @param mixed $indexKey  The column to use as the index/keys for the
	 *                         returned array. This value may be the integer key of the column, or
	 *                         it may be the string key name.
	 *
	 * @return array
	 */
	function array_column(array $array, $columnKey, $indexKey = null)
	{
		$result = array();

		foreach ($array as $subArray) {
			if (!is_array($subArray)) {
				continue;
			} elseif (is_null($indexKey) && array_key_exists($columnKey, $subArray)) {
				$result[] = $subArray[$columnKey];
			} elseif (array_key_exists($indexKey, $subArray)) {
				if (is_null($columnKey)) {
					$result[$subArray[$indexKey]] = $subArray;
				} elseif (array_key_exists($columnKey, $subArray)) {
					$result[$subArray[$indexKey]] = $subArray[$columnKey];
				}
			}
		}
		return $result;
	}

}

function order_sn(){
	return date('YmdHis').substr(microtime(), 2, 3).sprintf('%02d',mt_rand(0,99));
}

//获取省份数据
function get_province_name($id)
{
	return $id ? M('province')->getField('title', array('id' => intval($id))) : '';
}

//获取城市通过Pid数据
function get_city_name($id)
{
	return $id ? M('citys')->getField('city', array('id' => intval($id))) : '';
}