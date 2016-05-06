<?php

/**
 * Created by PhpStorm.
 * User: ggbound
 * Date: 15/12/16
 * Time: 17:46
 */
class BankOpModel extends Model
{

    public function do_log_op_info($uid,$bank_id,$action){

        $data['uid'] = $uid;
        $data['bank_id'] = $bank_id;
        $data['action'] = $action;
        $data['optime'] = time();
        $this->add($data);

    }

}