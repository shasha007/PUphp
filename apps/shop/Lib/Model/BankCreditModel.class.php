<?php

class BankCreditModel extends Model {
    //产生新的授信合同号
    public function addNewCredit($uid)
    {
        $nowtime = date('Ymd');
        $nowtime = substr($nowtime,2);
        $list = $this->where('uid='.$uid)->find();
        if($list){
            $res = $this->addOldContract($uid);
            return $res;
        }
        $map5['contract']  = array('like','08'.$nowtime."%");
        $num = $this->where($map5)->count();
        $num = $num+1;
        $contract=sprintf("%04d", $num);
        $arr4['contract_id'] = $nowtime.$contract.'_01';
        $arr4['uid'] = $uid;
        $arr4['ptm'] = '01'.$arr4['contract_id'];
        $arr4['pfm'] = '02'.$arr4['contract_id'];
        $arr4['ctime'] = date('Y-m-d H:i:s');
        $res4 = D('bank_contract')->add($arr4);

        $arr5['contract'] = '08'.$nowtime.$contract;
        $arr5['ctime'] = date('Y-m-d H:i:s');
        $arr5['uid'] = $uid;

        $res5 = $this->add($arr5);
        if(!$res4){
            return 0;
        }

        if($res4&&$res5){
            return $arr4['contract_id'];
        }else{
            return 0;
        }
    }

    //口袋金产生老用户的合同号
    public function addOldContract($uid)
    {
        $contract = $this->getField('contract','uid='.$uid);
        if(empty($contract)){
            return 0;
        }
        $newcon = substr($contract,2);  //日期+序号
        $map5['contract_id']  = array('like',$newcon."%");
        $num = D('bank_contract')->where($map5)->count();
        $num = $num + 1;
        if($num<10){
            $arr4['contract_id'] = $newcon.'_0'.$num;
        }else{
            $arr4['contract_id'] = $newcon.'_'.$num;
        }

        $arr4['uid'] = $uid;
        $arr4['ptm'] = '01'.$arr4['contract_id'];
        $arr4['pfm'] = '02'.$arr4['contract_id'];
        $arr4['ctime'] = date('Y-m-d H:i:s');
        $res4 = D('bank_contract')->add($arr4);
        if(!$res4){
            return 0;
        }
        return $arr4['contract_id'];
    }

}

?>