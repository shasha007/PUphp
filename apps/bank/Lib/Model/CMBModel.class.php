<?php

/**
 * 
 * @author Administrator
 * 
 */
class CMBModel extends Model
{
	const testDomain = 'http://218.4.189.170/CmbsuWeb/PU';
	public $testIp = 'http://218.4.158.6:4358';
//  	public $testIp = 'http://192.168.1.31:8080';
	const applyCardUrl = 'http://suzhou.cmbchina.com/web/PU';
	const key = '56CC09E7CFDC4CEF';
	public $lgnnam = '康欣钱惠';
	const collectCompanyNo = '512905599610101';
	const collectBankNo = '52';
	const collectBankName = '中新支行';
	const payCompanyNo = '512903083210777';
	const payBankNo = '52';
	const payBankName = '中新支行';
	const dattyp = 2;
	/*
	 * 发送数据数组
	 */
	public $sendData = array();
	/*
	 * 接收数据数组
	 */
	public $revData = array();
	/*
	 * 接收数据数组INFO部分
	 */
	public $revInfo = array();
	/*
	 * 发送数据XML
	 */
	public $sendXml = '';
	/*
	 * 接收数据XML
	 */
	public $revXml = '';
	/*
	 * 业务订单日志表名
	 */
	public $logTableName = '';
	/*
	 * 业务订单表
	 */
	public $tableName = '';
	/*
	 * 业务订单id
	 */
	public $order_id = '';
	/*
	 * 业务订单日志id
	 */
	public $log_order_id = '';
	/*
	 * 银行返回业务订单id
	 */
	public $bank_order_id = '';
	/*
	 * 查询订单是否等待
	 */
	public $isSleep = false;
	/*
	 * 查询订单前等待时间
	 */
	public $sleepTime = '15';
	
	const WRITELOG = true;
	
	public $banMsg = array(
						'0'=>'操作成功',
						'-1'=>'提交主机失败',
						'-2'=>'执行失败',
						'-3'=>'数据格式错误',
						'-4'=>'尚未登录系统',
						'-5'=>'请求太频繁',
						'-6'=>'不是证书卡用户',
						'-7'=>'用户取消操作',
						'-9'=>'其它错误',);
	
	public $cardMsg = array(
						'0'=>'操作成功',
						'10'=>' 通讯类错误',
						'11'=>'认证失败',
						'12'=>'消息不完整/MAC校验通不过',
						'13'=>'格式有误/解密失败',
						'20'=>'业务数据类错误',
						'21'=>'接口用法有误',
						'22'=>'接口数据有误',
						'30'=>'接口数据',);
	
	public function returnXmlToArray($postStr)
	{
		$array = @(array)simplexml_load_string($postStr,'SimpleXMLElement',LIBXML_NOCDATA);
		return $this->struct_to_array($array);
	}
	
	protected function setHead($data)
	{
		$this->sendData['INFO'] = array('FUNNAM'=>$data,'DATTYP'=>2,'LGNNAM'=>$this->lgnnam);
	}
	
	/**
	 * 开始执行发送业务xml报文
	 * @param unknown $business
	 * @return Ambigous <multitype:, unknown, array>
	 */
	protected function doBusiness($business = 'CMBSDKPGK')
	{
		$res = $this->arrayToXml($this->sendData,$business);
		$this->sendXml = $res;
		$this->saveLog();
		$this->revData = $this->http_post_data($this->testIp, $res);
		$this->saveLog($this->order_id);
		$this->getRevData();
	}
	
	/**
	 * 获取返回报文INFO部分
	 */
	public function getRevData()
	{
		$this->revInfo = array(
					'code'=>$this->revData['INFO']['RETCOD'],
					'msg'=>$this->bankMsg[$this->revData['INFO']['RETCOD']],
					'bankerror'=>$this->revData['INFO']['ERRMSG'],
					);
	}
	
	/**
	 * 拼接生成xml
	 * @param unknown $arr
	 * @param string $head xml的根节点
	 * @param number $dom
	 * @param number $item
	 * @return string
	 */
	protected function arrayToXml($arr,$head='CMBSDKPGK',$dom=0,$item=0)
	{
		if (!$dom){
			$dom = new DOMDocument("1.0",'GBK');
		}
		if(!$item){
			$item = $dom->createElement($head);
			$dom->appendChild($item);
		}
		foreach ($arr as $key=>$val){
			$itemx = $dom->createElement(is_string($key)?$key:"item");
			$item->appendChild($itemx);
			if (!is_array($val)){
				$text = $dom->createTextNode($val);
				$itemx->appendChild($text);
	
			}else {
				$this->arrayToXml($val,$head,$dom,$itemx);
			}
		}
		return $dom->saveXML();
	}
	
	/**
	 * 对象转数组
	 * @param unknown $item
	 * @return array
	 */
	protected function struct_to_array($item){
		if(!is_string($item)){
			$item =(array)$item;
			foreach($item as $key=>$val){
				$item[$key]  = $this->struct_to_array($val);
			}
		}
		return$item;
	}
	
	/**
	 * 中文编码
	 *
	 * @param string 要编码的字符串
	 * @return string 编码过的字符串
	 */
	protected function hex_encode($s) {
		$s = iconv('UTF-8', 'GBK', $s);
		$s;
	}
	
	// 数据加密
	public function encrypt($input,$key='') {
		if(!$key) $key = self::key;
		$size = mcrypt_get_block_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB);
		$input = $this->pkcs5_pad($input, $size);
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
		$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
		mcrypt_generic_init($td, $key, $iv);
		$data = mcrypt_generic($td, $input);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		$data = base64_encode($data);
		return $data;
	}
	
	public function pkcs5_pad ($text, $blocksize) {
		$pad = $blocksize - (strlen($text) % $blocksize);
		return $text . str_repeat(chr($pad), $pad);
	}
	
	// 数据解密
	public function decrypt($sStr,$sKey='') {
		if(!$sKey) $sKey = self::key;
		$decrypted= mcrypt_decrypt(
				MCRYPT_RIJNDAEL_128,
				$sKey,
				base64_decode($sStr),
				MCRYPT_MODE_ECB
		);
	
		$dec_s = strlen($decrypted);
		$padding = ord($decrypted[$dec_s-1]);
		$decrypted = substr($decrypted, 0, -$padding);
		return $decrypted;
	}
	
	protected function http_post_data($url, $data_string, $contentType = 'xml') {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		'Content-Type: application/xml; charset=GBK',
		'Content-Length: ' . strlen($data_string))
		);
		ob_start();
		curl_exec($ch);
		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$return_content = ob_get_contents();
// 		var_dump($return_code);
// 		var_dump($return_content);
// 		exit;
		$return_content =  strtr($return_content,array(chr(14)=>'',chr(15)=>''));
// 		$return_content = iconv('EBCDIC','gbk', $return_content);
		if($contentType == 'xml') {
			$this->revXml = $return_content;
			$return_content = $this->returnXmlToArray($return_content);
		} else {
			$return_content = json_decode($return_content,true);
		}
		ob_end_clean();
		return $return_content;
// 		$return_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
//      return array($return_code, $return_content);
	}
	
	/**
	 * 添加日志
	 * @param unknown $order_id 订单号
	 * @param unknown $tableName 日志表名
	 * @return mixed
	 */
	protected function saveLog($isUpdate = false)
	{
		if(!self::WRITELOG) return false;
// 		if(!$this->order_id) return 'order_id null';
		if($isUpdate) {
			//如果是更新数据
			$data = array(
						'rev_data'=>serialize($this->revXml),
						'rev_time'=>date('Y-m-d H:i:s'),
						);
			return M($this->logTableName)->where(array('order_id'=>$this->order_id))->save($data);
		} else {
			//如果是新增数据
			$data = array(
						'order_id'=>$this->order_id,
						'post_data'=>serialize($this->sendXml),
						'ctime'=>date('Y-m-d H:i:s'),
			);
			return M($this->logTableName)->add($data);
		}
	}
	
	/**
	 * 获取xml返回日志，转array
	 * @param unknown $order_id
	 * @param unknown $tableName
	 */
	public function getXmlLogByOrderId($order_id,$tableName)
	{
		$res = M($this->logTableName)->where(array('order_id'=>$order_id))->find();
		foreach($res as $k => $v) {
			$res[$k]['post_data_array'] = $this->returnXmlToArray($v['post_data']);
			$res[$k]['rev_data_array'] = $this->returnXmlToArray($v['rev_data']);
		}
		
		return $res;
	}
}

?>
