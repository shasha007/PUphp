<?php

class BankCardModel extends Model {

    public function editCardMessage($id,$data,$arr){

        if(($data['allow_risk']!=2)&&$data['back_reason']){
            $this->error = '请确定风控状态是否填写正确';
            return 0;
        }
        if(($data['allow_risk']==2)&&(empty($data['back_reason']))){
            $this->error = '请填写驳回原因';
            return 0;
        }
        if(!isValidMobile($data['mobile'])){
            $this->error = '请正确填写手机号码';
            return 0;
        }

        if(!isValidMobile($data['d_mobile'])){
            $this->error = '请正确填写父亲手机号码';
            return 0;
        }
        if(!isValidMobile($arr['class_mobile'])){
            $this->error = '请正确填写同学手机号码';
            return 0;
        }

        if(!isCtfId($data['ctf_id'])){
            $this->error = '请正确填写身份证号码';
            return 0;
        }
        if($data['realname']&&$data['address']&&$data['ctf_id']&&$data['email_bill']&&$data['post_code']&&$arr['qq']&&$arr['d_name']&&$arr['d_company']&&$arr['home']&&$arr['classmate']&&$arr['class_address']){
            $data['check'] = 0;
            $res = $this->where('id='.$id)->save($data);
            $res1 = D('bank_user_info')->where('bank_id='.$id)->save($arr);
            if($res||$res1){
                $allow_risk = $this->getField('allow_risk','id='.$id);
                if($allow_risk==2){
                    //把关联的订单全部改为status=0、
                    D('bank_finance')->setField('status', 0, 'mark=2 and bank_card_id='.$id);
                }
                return 1;
            }else{
                $this->error = '操作失败';
                return 0;
            }
        }else{
            $this->error = '资料填写不完整';
            return 0;
        }

    }

    //添加办卡资料信息
    public function addBankCard($data,$uid,$arr){
        if(empty($uid)){
            $this->error = '错误操作';
            return 0;
        }
        if(!isValidMobile($data['mobile'])){
            $this->error = '请正确填写手机号码';
            return 0;
        }

        if(!isValidMobile($data['d_mobile'])){
            $this->error = '请正确填写父亲手机号码';
            return 0;
        }

        if(!isCtfId($data['ctf_id'])){
            $this->error = '请正确填写身份证号码';
            return 0;
        }
        $arr1 = array('民族'=>'nation','同学宿舍地址'=>'class_address','父亲姓名'=>'d_name','QQ/微信'=>'qq','家长工作单位'=>'d_company','同学姓名'=>'classmate','同学手机号'=>'class_mobile','家庭地址'=>'home');
        foreach($arr1 as $key=>$val){
            if(empty($arr[$val])){
                $this->error = $key.'为空，请正确填写';
                return 0;
            }
        }
        if($data['realname']&&$data['address']&&$data['ctf_id']&&$data['email_bill']&&$data['post_code']){
            $oldid = $this->getField('id','status=3 and uid='.$uid);
            D('bank_finance')->where('bank_card_id ='.$oldid)->delete();
            D('bank_finance_detail')->where('bank_card_id ='.$oldid)->delete();
            $this->where('status=3 and uid='.$uid)->delete();    //删除之前办卡失败的纪录
            $data['uid'] = $uid;
            $data['school_id'] = D('user')->getField('sid','uid='.$uid);
            $data['ctime'] = date('Y-m-d H:i:s');

            $city = getCityByUid($uid);
            $cityName = D('citys')->getField('city','id='.$city);
            $data['area'] = areaCode($cityName);
            if(!empty($arr['referrer'])){
                $data['referrer'] = $arr['referrer'];
            }
            $result4 = D('user')->getField('from_reg','uid='.$uid);
            if($result4==0){
                $data['free_allow_risk']=1;
            }
            $res = $this->add($data);
            if($res){
                $arr['uid'] = $uid;
                $arr['sid1'] = D('user')->getField('sid1','uid='.$uid);
                $year = D('school')->getField('tj_year','id='.$arr['sid1']);
                if($year==3){
                    $arr['education'] = 1;
                }else{
                    $arr['education'] = 2;
                }
                $arr['bank_id'] = $res;
                //return $arr;
                $result = D('bank_user_info')->add($arr);
                if($result){
                    return $res;
                }else{
                    $this->where('id='.$res)->delete();
                    $this->error = '操作失败';
                    return 0;
                }
            }else{
                $this->error = '操作失败';
                return 0;
            }
        }else{
            $this->error = '资料填写不完整';
            return 0;
        }

    }

    //修改相应额度
    public function editsurplus_line($id,$money,$type){
        $list = $this->where('id='.$id)->field('id,surplus_line')->find();
        if(($type==1)&&($list['surplus_line']<$money)){
            $this->error = '额度不足';
            return 0;
        }
        if($type==1){
            $data['surplus_line'] = $list['surplus_line']-$money;
        }else{
            $data['surplus_line'] = $list['surplus_line']+$money;
        }
        $res = $this->where('id='.$id)->save($data);
        if($res){
            return 1;
        }else{
            $this->error = '额度修改失败';
            return 0;
        }
    }

    //口袋大学直接办卡
    public function addPcard($data){
        $data['d_mobile'] = '18939868845';
        $data['ctime'] = date('Y-m-d H:i:s');
        $res = $this->add($data);
        if($res){
            //在信息表填写必要信息
            $arr['bank_id'] = $res;
            $arr['nation'] = '汉';
            $arr['education'] = 2;
            $arr['uid'] = $data['uid'];
            $arr['sid1'] = 0;
            $arr['d_name'] = '保密';
            $arr['classmate'] = '暂无';
            $arr['class_mobile'] = '18939984455';
            $arr['home'] = '江苏省苏州市创意产业园4幢A203';
            $result = D('bank_user_info')->add($arr);
                if($result){
                    return $res;
                }else{
                    $this->where('id='.$res)->delete();
                    $this->error = '操作失败';
                    return 0;
                }
        }else{
            //返回失败
            $this->error = '操作失败';
             return 0;
        }
    }

    //用户修改手机
    public function changeMobile($id,$mobile){
        $data['mobile'] = $mobile;
        if(!isValidMobile($data['mobile'])){
            $this->error = '请正确填写手机号码';
            return 0;
        }
        $data['check'] = 0;
        $res = $this->where('id='.$id)->save($data);
        if($res){
            return 1;
        }else{
            $this->error = '修改失败';
            return 0;
        }
    }

    //系统导入用户选择一千元免息添加四个字段到bank_card表中
    public function addFourCard($data,$uid,$mark){
        if(empty($uid)){
            $this->error = '错误操作';
            return 0;
        }
        if(!isValidMobile($data['mobile'])){
            $this->error = '请正确填写手机号码';
            return 0;
        }
        if(!isCtfId($data['ctf_id'])){
            $this->error = '请正确填写身份证号码';
            return 0;
        }
        if($data['realname']&&$data['address']&&$data['email_bill']&&$data['post_code']){
            $data['uid'] = $uid;
            $data['school_id'] = D('user')->getField('sid','uid='.$uid);
            $data['ctime'] = date('Y-m-d H:i:s');

            $city = getCityByUid($uid);
            $cityName = D('citys')->getField('city','id='.$city);
            $data['area'] = areaCode($cityName);
            $data['free_allow_risk']=$mark;
            $data['d_mobile'] = $data['mobile'];
            $res = $this->add($data);
//            $sql = $this->getLastSql();
//            return $sql;die;
            if($res){
                return $res;
            }else{
                $this->error = '操作失败';
                return 0;
            }
        }
    }

    //完善个人信息
    public function editBankUserInfo($arr,$id,$data){
        if(empty($id)){
            $this->error = '错误操作';
            return 0;
        }
        if(!isValidMobile($data['d_mobile'])){
            $this->error = '请正确填写家长手机号码';
            return 0;
        }
        if(!isValidMobile($arr['class_mobile'])){
            $this->error = '请正确填写同学手机号码';
            return 0;
        }
        //$data['d_mobile'] = $d_mobile;
        //添加父母手机号
        $res = $this->where('id='.$id)->save($data);
        if($res){
            $arr1 = array('民族'=>'nation','同学宿舍地址'=>'class_address','父亲姓名'=>'d_name','QQ/微信'=>'qq','家长工作单位'=>'d_company','同学姓名'=>'classmate','同学手机号'=>'class_mobile','家庭地址'=>'home');
            foreach($arr1 as $key=>$val){
                if(empty($arr[$val])){
                    $this->error = $key.'为空，请正确填写';
                    return 0;
                }
            }
            //在个人信息表中添加数据
            $arr['uid'] = $this->getField('uid','id='.$id);
            $arr['sid1'] = D('user')->getField('sid1','uid='.$arr['uid']);
            $year = D('school')->getField('tj_year','id='.$arr['sid1']);
            if($year==3){
                $arr['education'] = 1;
            }else{
                $arr['education'] = 2;
            }
            $arr['bank_id'] = $id;
            $result = D('bank_user_info')->add($arr);
            if($result){
                return 1;
            }else{
                $this->error = '添加资料失败';
                return 0;
            }
        }else{
            //失败
            $this->error = '操作失败';
            return 0;
        }
    }

    //检测用户额度是否够
    public function isOver($money,$uid){
        if(!($money&&$uid)){
            return 0;
        }
        $list = $this->where('status!=3 and uid='.$uid)->field('surplus_line')->find();
        if($list){
            $surp_line = intval($list['surplus_line']);
            $money = intval($money);
            if($money>$surp_line){
                return 0;
            }else{
                return 1;
            }
        }else{
            return 1;
        }
//        $surp_line = $this->getField('surplus_line','status!=3 and uid='.$uid);

    }

    //判断是否是新用户
    public function isNewUser($uid)
    {
        $map['uid'] = $uid;
        $map['status'] = array('neq',3);
        $res = $this->where($map)->field('id')->find();
        //如果不是口袋大学，老用户 mark=0  新用户mark=1 如果是口袋大学 新用户 显示贵宾办卡页面
        if($res){
            return 0;   //老用户
        }
        return 1;
    }

    //口袋金获取老用户信息
    public function getUserCard($uid)
    {
        $map['uid'] = $uid;
        $map['status'] = array('neq',3);
        $res['data'] = $this->where($map)->field('id,realname,mobile,address,ctf_id,card_no,allow_risk,d_mobile,m_mobile,post_code,email_bill,surplus_line,referrer')->find();
        $str = '****************';
        $res['data']['ctf_id'] = substr_replace($res['data']['ctf_id'],$str,1,16);
        $list = D('bank_user_info')->where('bank_id='.$res['data']['id'])->find();
        $res['info'] = $list;
        return $res;
    }

    //前台下订单时判断订单状态
    public function getListStatus($id)
    {
        $list = $this->where('id='.$id)->field('id,allow_risk,card_no,status')->find();
        if(($list['allow_risk']==1)&&$list['card_no']&&($list['status']==2)){
            $key = 40;
        }else{
            $key = 0;
        }
        return $key;
    }
}

?>