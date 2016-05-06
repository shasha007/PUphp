<?php

/**
 * 用户PU金模型
 */
class PufinanceCreditModel extends Model
{
    public function getPufinanceCreditInfo($condtion)
    {
        return $this->where($condtion)->find();
    }

    /**
     * 通过UID获取用户PU金额度信息
     *
     * @param integer $uid 用户UID
     *
     * @return mixed
     */
    public function getPufinanceCreditInfoByUid($uid)
    {
        return $this->getPufinanceCreditInfo(array('uid' => $uid));
    }

    /**
     * 初始化用户PU金
     *
     * @param $uid
     *
     * @return array
     */
    public function initPufinanceCredit($uid)
    {
        $amount = '1000.00';
        $freeAmount = '200.00';
        $pucredit = array(
            'uid' => $uid,
            'all_amount' => $amount,
            'usable_amount' => $amount,
            'free_amount' => $freeAmount,
            'free_usable_amount' => $freeAmount,
            'free_risk' => 200
        );
        $this->add($pucredit);
        return $pucredit;
    }

    /**
     * 更新用户PU金数据
     *
     * @param integer $uid
     * @param array $data
     *
     * @return mixed
     */
    public function updatePufinanceCredit($uid, $data)
    {
        return $this->where(array('uid' => $uid))->save($data);
    }
	
	/*
	 * 列表
	*/	
	public function getPufinanceCreditLists($map){
		$lists = array();
        $lists = $this->table(C('DB_PREFIX').'pufinance_credit pc')
                    ->field('pc.uid,pc.all_amount,pc.usable_amount,pc.free_amount,pc.free_usable_amount,pu.realname,pu.ctfid,pc.free_risk,pc.status,m.money umoney,pm.money pmoney,u.email')
                    ->join(C('DB_PREFIX').'pufinance_user pu on pc.uid = pu.uid')/*关联pu金用户表*/
                    ->join(C('DB_PREFIX').'pufinance_money pm on pm.uid=pc.uid')/*关联用户PU币表（来源PU金）*/
                    ->join(C('DB_PREFIX').'money m on m.uid=pc.uid')/*关联ts_money表*/
                    ->join(C('DB_PREFIX').'user u on u.uid=pc.uid')/*关联ts_user*/
                    ->where($map)
                    ->findPage(15);
        return $lists;
	}

    /**
     * 使用户加入白名单/黑名单
     *  @param $uid,$status: 0:初始状态 1:白名单 2:黑名单
     */
    public function addWhiteList($uid,$status)
    {
        $data['status'] = $status;
        $res = $this->where("uid=$uid")->save($data);
        return $res;
    }

    //导出数据
    public function getCreditDatas($map=array(), $order='c.uid desc', $limit='0,100'){
        return $this->table(C('DB_PREFIX').'pufinance_credit c')
            ->field('u.uid,u.realname,u.ctfid,c.all_amount,c.usable_amount,c.free_amount,c.free_usable_amount,c.free_risk,c.status')
            ->join(C('DB_PREFIX').'pufinance_user u on u.uid=c.uid')
            ->where($map)
            ->order($order)
            ->limit($limit)
            ->select();
    }
}