<?php

/**
 *
 */
class ApiAction
{

    const SUCCESS                   = 0;
    const ERROR_INVALID_SERIAL_NO   = 10001;
    const ERROR_STAGE_ORDER_MISSING = 10002;
    const ERROR_ILLEGAL_SIGN        = 10003;
    const ERROR_POST_METHOD         = 10004;
    const ERROR_DATA_FORMAT_ERROR   = 10005;
    const ERROR_POST_DATA_EMPTY     = 10006;
    const ERROR_SERVICE_MISSING     = 10007;

    private $publicKeyPath;
    private $privateKeyPath;
    protected $signType = 'md5';
    public    $method   = '';
    protected $data     = null;
    protected $request  = array();
    protected $errMsg = array(
        self::SUCCESS                   => '响应成功',
        self::ERROR_INVALID_SERIAL_NO   => '无效的serialNo',
        self::ERROR_STAGE_ORDER_MISSING => '代扣数据不存在',
        self::ERROR_POST_METHOD         => '请求方式非法',
        self::ERROR_ILLEGAL_SIGN        => '请求验签失败',
        self::ERROR_DATA_FORMAT_ERROR   => '请求数据非法',
        self::ERROR_SERVICE_MISSING     => '请求接口不存在',
        self::ERROR_POST_DATA_EMPTY     => '未找到请求数据',
    );

    public function __construct()
    {
        Log::$log = array(); // 清空
        $this->privateKeyPath = SITE_PATH . '/apps/pufinance/Common/cert/rsa_private_key.pem';
        $this->publicKeyPath = SITE_PATH . '/apps/pufinance/Common/cert/kangxin_public_key.pem';

        //var_dump($this->errMsg);
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        if ($this->method != 'post') {
            return $this->response(self::ERROR_POST_METHOD);
        }

        $this->request = file_get_contents('php://input');

        if (empty($this->request)) {
            return $this->response(self::ERROR_POST_DATA_EMPTY);
        }
        Log::record($this->request);
        $xml = simplexml_load_string($this->request);
        if ($xml) {
            $this->request = json_decode(json_encode($xml), true);
        } else {
            return $this->response(self::ERROR_DATA_FORMAT_ERROR);
        }

        if (!$this->verifySign($this->request)) {
            return $this->response(self::ERROR_ILLEGAL_SIGN);
        }

        $this->data = $this->request['reqData'];
        $method = $this->request['service'];
        //$method = 'queryWithHolding';
        if (method_exists($this, $method)) {
            $this->$method();
        } else {
            return $this->response(self::ERROR_SERVICE_MISSING);
        }
    }

    /**
     * 查询代扣数据（即分期订单）
     */
    private function queryWithHolding()
    {
        //$this->data['serialNo'] = 60;
        if (empty($this->data['serialNo'])) {
            $this->response(self::ERROR_INVALID_SERIAL_NO);
        }

        $stageOrder = D('PufinanceOrderStage')->getById($this->data['serialNo']);
        if (!$stageOrder) {
            $this->response(self::ERROR_STAGE_ORDER_MISSING);
        }
        $order = D('PufinanceOrder')->where(array('id' => $stageOrder['order_id']))->field('id,ctime,repay_bank_card_id')->find();
        $bankcards = D('PufinanceBankcard')->getUserUsableBankcardListByUid($stageOrder['uid']);
        $bankcard = $bankcards[$order['repay_bank_card_id']];
        $realname = D('PufinanceUser')->getField('realname', array('uid' => $stageOrder['uid']));
        $data = array(
            'serialNo' => $stageOrder['id'],
            'loanNo' => $order['ctime'] . $stageOrder['order_id'],
            'cardSignNo' => $bankcard['card_sign_no'],
            'custNo' => $bankcard['cust_no'],
            'custName' => $realname,
            'bankName' => $bankcard['bank_name'],
            'bankCardNo' => $bankcard['card_no'],
            'amount' => $stageOrder['last_amount'],
        );
        $this->response(self::SUCCESS, $data);
    }

    private function makeSign($data)
    {
        $str = $this->parseParams($data);
        return $this->rsaSign($str, $this->privateKeyPath);
    }

    private function response($errCode, $data = array())
    {
        $return = array(
            'resCode' => $errCode,
            'resMsg'  => $this->getErrMsg($errCode)
        );

        if (!empty($data)) {
            $return['resData'] = $data;
            $return['sign'] = $this->makeSign($return);
        }

        $this->xml(array('message' => $return));

    }

    protected function getErrMsg($code)
    {
        return $this->errMsg[$code];
    }

    protected function setErrMsg($data = array())
    {
        foreach ($data as $key => $val) {
            $this->errMsg[$key] = $val;
        }
    }

    public function json($data)
    {
        header('Content-Type:application/json; charset=utf-8');
        exit(json_encode($data));
    }

    public function xml($data)
    {
        header('Content-Type:application/xml; charset=utf-8');
        $content = $this->arrayToXml($data);
        Log::record($content);
        $logPath = SITE_PATH . '/data/pufinance/api';
        if (!is_dir($logPath)) {
            mkdir($logPath, 0777, true);
        }

        Log::save(Log::FILE, $logPath . '/' . date('Ymd') . '.log');
        exit($content);
    }

    /**
     * 验证签名
     * @param array $data 响应的数据
     *
     * @return boolean
     */
    private function verifySign($data)
    {
        $sign = $data['sign'];
        unset($data['sign']);
        $str = $this->parseParams($data);
        return $this->rsaVerify($str, $this->publicKeyPath, $sign);

    }

    private function parseParams($data)
    {
        if (isset($data['reqData'])) {  // 请求数据
            $reqData = '<reqData>';
            foreach ($data['reqData'] as $key => $item) {
                $reqData .= "<{$key}>{$item}</{$key}>";
            }
            $reqData .= '</reqData>';
            $data['reqData'] = $reqData;
        }

        if (isset($data['resData'])) {  // 响应数据
            $resData = '<resData>';
            foreach ($data['resData'] as $key => $item) {
                $resData .= "<{$key}>{$item}</{$key}>";
            }
            $resData .= '</resData>';
            $data['resData'] = $resData;
        }

        ksort($data);
        $str = '';
        foreach ($data as $key => $item) {
            $str .= $item;
        }
        Log::record($str);
        return $str;
    }

    /**
     * RSA签名
     *
     * @param string $data             待签名数据
     * @param string $private_key_path 私钥文件路径
     *
     * @return string 签名结果
     */
    private function rsaSign($data, $private_key_path) {
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
     *
     * @param string $data                待签名数据
     * @param string $public_key_path     公钥文件路径
     * @param string $sign                要校对的的签名结果
     *
     * @return boolean 验证结果
     */
    private function rsaVerify($data, $public_key_path, $sign)  {
        $pubKey = file_get_contents($public_key_path);
        $res = openssl_get_publickey($pubKey);
        $result = (bool)openssl_verify($data, base64_decode($sign), $res);
        openssl_free_key($res);
        return $result;
    }

    protected function arrayToXml($array, $dom = false, $item = false)
    {
        if (!$dom) {
            $dom = new DOMDocument("1.0", 'UTF-8');
        }

        foreach ($array as $key => $value) {
            if (!$item) {
                $item = $dom->createElement(is_string($key) ? $key : "item");
                $dom->appendChild($item);
                $this->arrayToXml($value, $dom, $item);
            } else {
                $itemx = $dom->createElement(is_string($key) ? $key : "item");
                $item->appendChild($itemx);
                if (is_array($value)) {
                    $this->arrayToXml($value, $dom, $itemx);
                } else {
                    $text = $dom->createTextNode($value);
                    $itemx->appendChild($text);
                }
            }
        }
        return $dom->saveXML();
    }

}