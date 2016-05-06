<?php

/**
 * 用户银行卡模型
 */
class PufinanceBankcardModel extends Model
{
    const DES_KEY = 'ab914a02';
    /**
     * 通过条件获取银行卡信息
     *
     * @param array $condition 条件
     *
     * @return array|boolean
     */
    public function getBankcardList($condition)
    {
        return $this->where($condition)->select();

    }

    /**
     * 通过UID获取用户所有银行卡并缓存
     *
     * @param integer $uid 用户UID
     *
     * @return array
     */
    public function getUserAllBankcardListByUid($uid)
    {
        $list = S('pufinance_bank_card_list' . $uid);
        if (!$list) {
            $list = $this->getBankcardList(array('uid' => $uid));
            if ($list) {
                S('pufinance_bank_card_list' . $uid, $list);
            }
        }
        $des = self::DES_KEY;
        $list = array_map(function ($o) use ($des) {
            $bank = D('PufinanceBankOrg', 'pufinance')->getBankInfoById($o['bank_id']);
            $o['bank_name'] = $bank['bank_name'];
            $o['card_no'] = desdecrypt($o['card_no'], $des);
            $o['hide_card_no'] = substr_replace($o['card_no'], '**********', 4, strlen($o['card_no']) - 8);
            return $o;
        }, $list);

        return $list;
    }
	
	//获取用户未解绑的所有银行卡
	public function getUserAllAvailableBanks($uid, $condition=array()){
		$list = $this->getBankcardList(array_merge(array('uid' => $uid, 'status'=>1),$condition));
		
        $des = self::DES_KEY;
        $list = array_map(function ($o) use ($des) {
            $o['bank_name'] = get_bank($o['bank_id']);
            $o['card_no'] = desdecrypt($o['card_no'], $des);
            $o['hide_card_no'] = substr_replace($o['card_no'], '**********', 4, strlen($o['card_no']) - 8);
            return $o;
        }, $list);

        return $list;
	}

    /**
     * 通过UID获取用户所有可用银行卡
     *
     * @param integer $uid 用户UID
     *
     * @return array 用户银行卡信息列表
     */
    public function getUserUsableBankcardListByUid($uid)
    {
        $all = $this->getUserAllBankcardListByUid($uid);
        $return = array();
        foreach ($all as $card) {
            if ($card['status']) {
                $return[$card['id']] = $card;
            }
        }
        return $return;
    }

    public function addUserBankcard($uid, $bankInfo)
    {
        $data = array(
            'uid' => $uid,
            'bank_id' => $bankInfo['bank_id'],
            'card_no' => desencrypt($bankInfo['card_no'], self::DES_KEY),
            'mobile' => $bankInfo['mobile'],
            'status' => 1,
            'ctime' => time(),
            'card_sign_no' => isset($bankInfo['card_sign_no']) ? $bankInfo['card_sign_no'] : '',
            'cust_no' => isset($bankInfo['cust_no']) ? $bankInfo['cust_no'] : '',
            'bank_name' => isset($bankInfo['bank_name']) ? $bankInfo['bank_name'] : '',
            'province_id' => intval($bankInfo['province_id']),
            'city_id' => intval($bankInfo['city_id']),
        );
        $res =  $this->add($data);
        if ($res) {
            S('pufinance_bank_card_list' . $uid, null);// 清理缓存
        }
        return $res;
    }

    public function saveUserBankcard($condition, $data)
    {
        $bankcards = $this->where($condition)->select();
        foreach ($bankcards as $bankcard) {
            S('pufinance_bank_card_list' . $bankcard['uid'], null);// 清理缓存
        }
        return $this->where($condition)->save($data);
    }

    /**
     * 删除用户银行卡（伪删除仅做标识）
     *
     * @param integer $uid
     * @param integer $id
     *
     * @return boolean
     */
    public function delUserBankcard($uid, $id)
    {
        $res = $this->setField('status', 0, array('id' => $id, 'uid' => $uid));
        if ($res !== false) {
            S('pufinance_bank_card_list' . $uid, null);// 清理缓存
        }
        return $res;
    }
    /**根据bankid获取银行卡信息
     * @param $bankid
     * @return array()
     * @todo
     */
    public function getOrderBank($bankid)
    {
        if($bankid == 0)//代表pu币借款
        {
            return $res = array(
                'uid'=>'',
                'bank'=>'PU钱包',
                'card_no'=>'',
                'mobile'=>''
            );
        }
        else //pu金借款
        {
            $res = M("pufinance_bankcard")->field('uid,bank_id,card_no,mobile')->where("id=$bankid")->find();
            if ($res) {
                $bank = D('PufinanceBankOrg','pufinance')->getBankInfoById($res['bank_id']);
                $des = self::DES_KEY;
                $res['card_no'] = desdecrypt($res['card_no'], $des);
                $res['hide_card_no'] = substr_replace($res['card_no'], '**********', 4, strlen($res['card_no']) - 8);
                $res['bank'] = $bank['bank_name'];
            }
            return	 !empty($res) ? $res: array();
        }
    }

}