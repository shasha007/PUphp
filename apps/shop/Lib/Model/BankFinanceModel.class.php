<?php

class BankFinanceModel extends Model {

    //添加订单
    public function addFinance($uid,$data1,$key,$card_id){
        $data['uid'] = $uid;
        $line = D('BankCard')->getField('surplus_line','id='.$card_id);
        if($data1['money']>$line){
            return 0;
        }
        $data['money'] = $data1['money'];
        $data['interest'] = $data1['servicePrice'] * $data1['staging'];
        $data['reason'] = $data1['reason'];
        $data['ctime'] = date('Y-m-d H:i:s');
        $data['stime'] = date('Y-m-d',strtotime('+1 month'));
        $data['staging'] = $data1['staging'];
        $data['mark'] = 2;
        $data['status'] = $key;
        $data['bank_card_id'] = $card_id;
        $id = $this->add($data);
        if($id){
            //添加成功
            $pdata['bank_finance_id'] = $id;
            $pdata['uid'] = $uid;
            $pdata['money'] = $data1['stagMoney'];
            $pdata['surp_money'] = $data1['stagMoney'];
            $pdata['ctime'] = date('Y-m-d H:i:s');
            $pdata['bank_card_id'] = $card_id;
            $time = time();
            for($i=1;$i<=$data['staging'];$i++){
                $time = strtotime('+30 day',$time);
                $y = date('Y-m-d',$time);
                $pdata['stime'] = $y;
                $res = D('bank_finance_detail')->add($pdata);
                if($res){
                    continue;
                }else{
                    $this->where('id='.$id)->delete();
                    return 0;
                }
            }
        }else{
            return 0;
        }
        if($res){
            $cdata['surplus_line'] = $line - $data['money'];
            D('bank_card')->where('id='.$card_id)->save($cdata);
            return $id;
        }
    }

    //一千免息添加
    public function addFinance1($uid,$key,$card_id){
        $data['uid'] = $uid;
        $data['money'] = 1000;
        $data['interest'] = 0;
        $data['ctime'] = date('Y-m-d H:i:s');
        $data['staging'] = 1;
        $data['mark'] = 1;
        $data['stime'] = date('Y-m-d',strtotime('+1 month'));
        $data['reason'] = '免费体验';
        $result = D('user')->getField('from_reg','uid='.$uid);
        if($result==0){
            $data['status'] = 40;
        }else{
            $data['status'] = $key;
        }

        $data['bank_card_id'] = $card_id;
        $id = $this->add($data);
        if($id){
            //添加成功
            $pdata['bank_finance_id'] = $id;
            $pdata['uid'] = $uid;
            $pdata['bank_card_id'] = $card_id;
            $pdata['money'] = 1000;
            $pdata['surp_money'] = 1000;
            $pdata['ctime'] = date('Y-m-d H:i:s');
            $y = date('Y-m-d',strtotime('+1 month'));
            $pdata['stime'] = $y;
            $res = D('bank_finance_detail')->add($pdata);
            if($res){
                //扣除相应额度
                $cdata['surplus_line'] = 4000.00;
                D('bank_card')->where('id='.$card_id)->save($cdata);
                return $id;
            }else{
                $this->where('id='.$id)->delete();
                return 0;
            }
        }else{
            return 0;
        }

    }

    //修改分歧表的stime
    public function editTime($id){
        if(empty($id)){
            return 0;
        }
        $time = time();
        $time1 = strtotime('+1 month',$time);
        $data['stime'] = date('Y-m-d',$time1);
        //$data['stime'] = date('Y-m-d');
        $res = $this->where('id='.$id)->save($data);
        //修改分期详情表时间
        //$time = time();
        $list = D('bank_finance_detail')->where('bank_finance_id='.$id)->field('id,stime')->order('id ASC')->findAll();
        foreach($list as $val){
            $time = strtotime('+1 month',$time);
            $pdata['stime'] = date('Y-m-d',$time);
            D('bank_finance_detail')->where('id='.$val['id'])->save($pdata);
        }
        return $res;

    }


    //修改订单状态
    public function editOrderStatus($id,$status){
        $result = $this->setField('status',$status,'id='.$id);
        if($result){
            $res = D('bank_finance_detail')->setField('status', $status, 'bank_finance_id='.$id);
            if($res){
                return 1;
            }else{
                $this->setField('status',1,'id='.$id);
                return 0;
            }
        }else{
            return 0;
        }
    }

    //获取康欣的贷款余额
    public function getMoney($type='all',$val=0){
        switch ($type){
            case 'all':
                //获取全部未还完的订单剩余本金总额
                $res = $this->where('status=1')->field('id,money,staging')->findAll();
                break;
            case 'school':
                $map['a.status'] = 1;
                $map['b.school_id'] = $val;
                $res = $this->table("ts_bank_finance AS a ")->join("ts_bank_card AS b ON a.bank_card_id=b.id")
                        ->field('a.id,a.money,a.staging')->where($map)->findAll();
                break;
            case 'city':
                $map['b.cityId'] = $val;
                $res1 = $this->table("ts_bank_card AS a ")->join("ts_school AS b ON a.school_id=b.id")
                        ->field('a.id,a.uid')->where($map)->findAll();
                $arr = array();
                foreach($res1 as $val){
                    $arr[] = $val['id'];
                }
                $map1['status'] = 1;
                $map1['bank_card_id'] = array('in',$arr);
                $res = $this->where($map1)->field('id,money,staging')->findAll();
                break;
            default :
                $res = array();
                break;
        }
        return $res;
    }

    //获取康欣逾期余额
    public function getLeave($type,$val){
        switch ($type){
            case 'all':
                //获取全部未还完的订单剩余本金总额
                $res = D('bank_finance_detail')->where('status=4')->field('id,bank_finance_id,surp_money')->findAll();
                break;
            case 'school':
                $map['a.status'] = 4;
                $map['b.school_id'] = $val;
                $res = $this->table("ts_bank_finance_detail AS a ")->join("ts_bank_card AS b ON a.bank_card_id=b.id")
                        ->field('a.id,a.bank_finance_id,a.surp_money')->where($map)->findAll();
                break;
            case 'city':
                $map['b.cityId'] = $val;
                $res1 = $this->table("ts_bank_card AS a ")->join("ts_school AS b ON a.school_id=b.id")
                        ->field('a.id,a.uid')->where($map)->findAll();
                $arr = array();
                foreach($res1 as $val){
                    $arr[] = $val['id'];
                }
                $map1['status'] = 4;
                $map1['bank_card_id'] = array('in',$arr);
                $res = $this->where($map1)->field('id,bank_finance_id,surp_money')->findAll();
                break;
            default :
                $res = array();
                break;
        }
        //处理数据
        $price = 0;
        $num = 0;
        $arr1 = array();
        foreach($res as $val){
            $list = $this->where('id='.$val['bank_finance_id'])->field('id,money,staging')->find();
            if(!in_array($list['id'],$arr1)){
                $arr1[]=$list['id'];
                $num += 1;
            }                           //如果不是之前有的订单 笔数加一
            //$money = intval($list['money']);
            //$benPrice = getMouPrice($money,$list['staging']);
            $price +=  $val['surp_money'];
            unset($list);
        }
        $data['price'] = $price;
        $data['num'] = $num;
        return $data;
    }

    //检测用户是否申请过一千元免息
    public function isHasFree($uid){
        if(empty($uid)){
            return 0;
        }
        $list = $this->where('mark=1 and uid='.$uid)->find();
        if($list){
            $status = D('bank_card')->getField('status','id='.$list['bank_card_id']);
            if($status==3){
                return 1;
            }else{
                return 0;
            }
        }else{
            return 1;
        }
    }

    //口袋金检测用户一千元免息是否完成
    public function isServiceOk($uid,$id,$money)
    {
        $map['uid'] = $uid;
        $map['bank_card_id'] = $id;
        $map['mark'] = 1;
        $result = $this->where($map)->field('id,status,stime')->find();
        $otime = strtotime($result['stime']);
        $ntime = time();
        if($result){
            //有1000元免息
            if($result['status']==2){
                //还款结束
                return 1;
            }elseif($money==1000&&($otime<=$ntime)){
                return 1;
            }else{
                return 0;
            }
        }else{
            //没有1000免息
            return 1;
        }
    }

    //口袋金新版判断学生是不是待毕业生 修改顶戴状态
    public function editStatusByYear($isYear,$card_id,$finance_id)
    {
        if($isYear==1){
            return '';
        }
        D('bank_finance')->setField('status',0,'id='.$finance_id);
        D('bank_card')->setField('isYear',1,'id='.$card_id);
        return 1;
    }

    //获取今农的数据
    public function getJnData($stime,$etime=0){
        if($stime&&$etime){
            $time1 = strtotime('+30 day',$stime);      //查询开始的扣款开始时间
            $time2 = strtotime('+30 day',$etime);
            $time1 = date('Y-m-d',$time1).' 00:00:00';
            $time2 = date('Y-m-d',$time2).' 59:59:59';
        }else{
            $stime = $stime?$stime:$etime;
            $time = strtotime('+30 day',$stime);
            $time = date('Y-m-d',$time);
            $time1 = date('Y-m-d',$time).' 00:00:00';
            $time2 = date('Y-m-d',$time).' 59:59:59';
        }

        $list = $this->where('status=1 and is_kx=0 and stime>="'.$time1.'" and stime<="'.$time2.'"')->field('id,bank_card_id,money,ctime')->findAll();
//        $sql = $this->getLastSql();
//        return $sql;die;
        return $list;
    }

    //获取金农还款数据
    public function getJnReData($stime,$etime = 0){
        if($stime&&$etime){
//            $time1 = strtotime('+30 day',$stime);      //查询开始的扣款开始时间
//            $time2 = strtotime('+30 day',$etime);
            $time1 = $stime.' 00:00:00';
            $time2 = $etime.' 59:59:59';
        }else{
            $stime = $stime?$stime:$etime;
//            $time = strtotime('+30 day',$stime);
//            $time = date('Y-m-d',$stime);
            $time1 = $stime.' 00:00:00';
            $time2 = $stime.' 59:59:59';
        }

        $list = D('bank_finance_detail')->where('status=2 and is_kx=0 and etime>="'.$time1.'" and etime<="'.$time2.'"')->field('id,bank_card_id,bank_finance_id,money')->findAll();
        return $list;
    }
}

?>