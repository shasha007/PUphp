<?php

class BankAction extends Action {
    
    public function _initialize() {
        $this->assign('isAdmin', '1');
        $this->assign('mobileJumpBox', 1);
    }

    //口袋金2.0
    public function bankPrice(){
        //判断登陆跳转
        $this->__isJump(1);
        //判断用户是否在苏州地区上学 如果不是则跳转到老的口袋金页面
        $this->__judge();
        $uid = $this->mid;
//        var_dump($uid);DIE;
        //如果是可待大学，首页出现办卡按钮
        $sid = D('user')->getField('sid','uid='.$uid);
        //搜索当前登录用户是否是新用户，如果办卡失败 则视为新用户
//        echo $sid;die;
        $res = D('BankCard')->isNewUser($uid);
        //如果不是口袋大学，老用户 mark=0  新用户mark=1 如果是口袋大学 新用户 显示贵宾办卡页面
        $mark = 0;
//        echo 22;
        if($res&&$sid!=473){
            $mark = 1;         //非口袋大学新用户
        }elseif($res&&$sid==473){
            $mark = 2;          //口袋大学新用户
        }
        //获取渠道
        $channel = $_GET['channel'];
        if(empty($channel)){
            $channel = 0;             //渠道
        }
        //为首页提供借款金额和借款期数
        $staging = array('0'=>1,'1'=>3,'2'=>6,'3'=>9,'4'=>12);
        $price = array('0'=>500, '1'=>1000,'2'=>1500,'3'=>2000,'4'=>2500, '5'=>3000,'6'=>3500,'7'=>4000,'8'=>4500,'9'=>5000);

        //获取首页借款原因
        $data = $this->getAllReason();
        $this->assign('data',$data);
        //获取口袋金首页的特卖商品
        $list = D('PocketGoods')->getHotGoods();
        $this->assign('list',$list);
        $this->assign('stagingList',$staging);
        $this->assign('priceList',$price);
        $this->assign('mark',$mark);  //如果mark为1  则页面会出现一千元免息 mark为2 页面出现办卡页面
        $this->assign('channel',$channel);//通过来时的网址获取办卡渠道  用于统计
        $this->display();
    }

    //获取口袋金借款原因
    private function getAllReason(){
        $map['isDel'] = 0;
        $list = D('pocket_reason')->where($map)->findAll();
        return $list;
    }

    //判断用户是否登陆，如果未登录跳转到登录页面
    private function __isJump($type){
        $uid = $this->mid;
        if(empty($uid)){
            redirect(U('home/Wxlog/login',array('type' => $type)));
        }
    }

    //判断用户是那个区域学校的 1苏州
    private function __judge(){
        $cityId = getCityByUid($this->mid);
        if($cityId != 1){
            redirect(U('shop/PocketShop/hotPocket'));
        }
    }

    //获取学生姓名以及所在城市邮编
    private function getUserPostcode(){
        $uid = $this->mid;
        $sid = D('user')->getField('sid','uid='.$uid);
        $city = D('school')->getField('cityId','id='.$sid);
        $cityName = D('citys')->getField('city','id='.$city);
        $arr2['name'] = D('user')->getField('realname','uid='.$uid);
        $arr2['post_code'] = areaPost($cityName);
        return $arr2;
    }

    //一千元免息
    public function noServise(){
        $uid = $this->mid;
        $arr2 = $this->getUserPostcode();
        $channel = $_GET['channel'];
        if(empty($channel)){
            $channel = 0;
        }
        //生成一个合同编号
        $this->assign('arr2',$arr2);

        $contract_id = D('BankCredit')->addNewCredit($uid);
        if($contract_id==0){
            $this->error('添加授信合同号失败，请重试!');
        }
        $this->assign('contract_id',$contract_id);
        $data['money'] = 1000;
        $data['staging'] = 1;
        $data['reason'] = '免费体验';
        $data['stagMoney'] = 1000;  //每月还款总额
        $data['benMoney'] = 1000;       //还款每月本金
        $data['servicePrice'] = 0;         //每月服务费
        $mark = 1;
        $this->assign('channel',$channel);
        $this->assign('mark',$mark);
        $this->assign('data',$data);
        $result = $this->isFromReg();
        if($result==1){
            $this->display('doMessage');
        }else{
            $this->display('doMessage2');
        }

    }

    //判断是不是自注册用户
    private function isFromReg(){
        $uid = $this->mid;
        $result = D('user')->getField('from_reg','uid='.$uid); //0的时候是系统导入  1  自己注册
        return $result;
    }

    //产生新的合同编号
//    private function addNewContract(){
//        $uid = $this->mid;
//        $nowtime = date('Ymd');
//        $nowtime = substr($nowtime,2);
//        $list = D('bank_credit')->where('uid='.$uid)->find();
//        if($list){
//            $res = $this->addOldContract();
//            return $res;
//        }
//            $map5['contract']  = array('like','08'.$nowtime."%");
//            $num = D('bank_contract')->where($map5)->count();
//            $num = $num+1;
//            $contract=sprintf("%04d", $num);
//
//        $arr4['contract_id'] = $nowtime.$contract.'_01';
//        $arr4['uid'] = $uid;
//        $arr4['ptm'] = '01'.$arr4['contract_id'];
//        $arr4['pfm'] = '02'.$arr4['contract_id'];
//        $arr4['ctime'] = date('Y-m-d H:i:s');
//        $res4 = D('bank_contract')->add($arr4);
//
//        $arr5['contract'] = '08'.$nowtime.$contract;
//        $arr5['ctime'] = date('Y-m-d H:i:s');
//        $arr5['uid'] = $uid;
//
//
//        $res5 = D('bank_credit')->add($arr5);
//        if(!$res4){
//            $this->error('添加合同失败');
//        }
//
//        if($res4&&$res5){
//
//            return $arr4['contract_id'];
//        }else{
//            $this->error('添加授信合同失败');
//        }
//    }
//
//    //产生新的合同编号
//    private function addOldContract(){
//        $uid = $this->mid;
//        $contract = D('bank_credit')->getField('contract','uid='.$uid);
//        if(empty($contract)){
//            $this->error('系统无法获取您的授信合同，请重试');
//        }
//        $newcon = substr($contract,2);  //日期+序号
//
//        $map5['contract_id']  = array('like',$newcon."%");
//        $num = D('bank_contract')->where($map5)->count();
//        $num = $num + 1;
//        if($num<10){
//            $arr4['contract_id'] = $newcon.'_0'.$num;
//        }else{
//            $arr4['contract_id'] = $newcon.'_'.$num;
//        }
//
//        $arr4['uid'] = $uid;
//        $arr4['ptm'] = '01'.$arr4['contract_id'];
//        $arr4['pfm'] = '02'.$arr4['contract_id'];
//        $arr4['ctime'] = date('Y-m-d H:i:s');
//        $res4 = D('bank_contract')->add($arr4);
//        if(!$res4){
//            $this->error('平台合同提交失败');
//        }
//        return $arr4['contract_id'];
//    }
    public function _before_lookXY(){
        $data['realname'] = $_GET['realname'];
        $data['mobile'] = $_GET['mobile'];
        $data['ctf_id'] = $_GET['ctf_id'];
        $str = '****************';
        $data['ctf_id'] = substr_replace($data['ctf_id'],$str,1,16);
        $data['money'] = $_GET['money'];

        $data['staging'] = $_GET['staging'];
        $data['stagMoney'] = getKxService($data['money']);
        $data['puMoney'] = getStagingPrice1($data['money'],$data['staging'])-getMouPrice($data['money'],$data['staging'])-$data['stagMoney'];
        $data['stagMoney'] = getMouPrice($data['money'],$data['staging']) + $data['stagMoney'];
        $data['ctime_y'] = date('Y');
        $data['ctime_m'] = date('m');
        $data['ctime_m1'] = date('m') + 1;
        $data['ctime_d'] = date('d');
        $staging = $data['staging'];
        $time = strtotime('+'.$staging.' month');
        $data['etime_y'] = date('Y',$time);
        $data['etime_m'] = date('m',$time);
        $data['etime_d'] = date('d',$time);
        return $data;
    }
     //查看协议
    public function lookXY(){
        $data=$this->_before_lookXY();
        $contract_id = $_GET['contract_id'];
        $uid = $this->mid;
        $map['uid'] = $uid;
        $map['status'] = array('neq',3);
        $list = D('bank_card')->where($map)->find();
        if($list['imgs']){
            $imgs = unserialize($list['imgs']);
            $this->assign('imgs',$imgs);
        }
        $m = intval($_GET['m']);
        $clumn=$m%2==1 ?'ptm':'pfm';
        $data['contract_id'] = D('BankContract')->getField($clumn,'contract_id="'.$contract_id.'"');
//        echo D('BankContract')->getLastSql();
        $this->assign('data',$data);
        $this->display('m_xy'.$m);
    }

    //直通办卡
    public function addPcard(){
        $uid = $this->mid;
        $channel = $_GET['channel'];
        if(empty($channel)){
            $channel = 0;
        }
        $sid = D('user')->getField('sid','uid='.$uid);
        $city = D('school')->getField('cityId','id='.$sid);
        $cityName = D('citys')->getField('city','id='.$city);
        $arr2['name'] = D('user')->getField('realname','uid='.$uid);
        $arr2['post_code'] = areaPost($cityName);
        //生成一个合同编号
        $this->assign('arr2',$arr2);

        $map['uid'] = $uid;
        $map['status'] = array('neq',3);
        $res = D('bank_card')->where($map)->field('id,realname,mobile,ctf_id')->limit(1)->find();
        $this->assign('res',$res);
        $this->assign('channel',$channel);    //办卡渠道
        $this->display('doMessage1');
    }

    //处理直通办卡
    public function doAddPcard(){
        $data['realname'] = t($_POST['realname']);
        $data['ctf_id'] = t($_POST['ctf_id']);
        $data['email_bill'] = '496868409@qq.com';
        $data['mobile'] = t($_POST['mobile']);
        $data['address'] = '江苏省苏州市创意产业园4幢A203';
        $data['area'] = '0512';
        $data['school_id'] = 473;
        $data['post_code'] = intval($_POST['post_code']);
        $channel = $_POST['channel'];
        if($channel){
            $data['channel'] = D('CMB','bank')->decrypt($channel);
        }else{
            $data['channel'] = 0;
        }
        $data['uid'] = $this->mid;
        //$data['channel'] = $_POST['channel'];
        $data['surplus_line'] = 5000;
        $data['total_line'] = 5000;
        $res = D('BankCard')->addPcard($data);
        if($res){
            $res1 = D('Card','bank')->sendApplyInfo($res,$data['uid']);
            if($res1['code']==1){
                $this->assign('jumpUrl', U('/Bank/bankPrice'));
                $this->success('办卡申请提交成功，等待银行审核');
            }else{
                $this->error('办卡申请提交失败，请重试');
            }
        }else{
            $this->error('提交信息失败');
        }
    }

    //判断用户是否是毕业生
    private function isOneYear(){
        //$year = date('Y');  //当前年份
        $uid = $this->mid;
        $sid = D('user')->getField('sid1','uid='.$uid);  //学院ID
        if($sid<=0){
            $sid = D('user')->getField('sid','uid='.$uid);  //学校ID
        }
        $syear = D('user')->getField('year','uid='.$uid);   //入学年份
        $school_year = D('school')->getField('tj_year','id='.$sid);  //学校是几年制的
        $eYear = $syear + $school_year -1;
        $eTime = mktime(0,0,0,6,30,$eYear);     //离毕业一年时的时间戳
        $sTime = time();           //当前时间戳
        if($sTime<$eTime){
            return 1;      //不是待毕业生
        }else{
            return 0;
        }
    }

    //判断用户是否有一千元吗免息，如果有 是否还款完成
    private function isServiceOk($id,$money){
        $map['uid'] = $this->mid;
        $map['bank_card_id'] = $id;
        $map['mark'] = 1;
        $result = D('bank_finance')->where($map)->field('id,status,stime')->find();
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

    //用户信息确认
    public function doMessage(){
        //判断是不是大四毕业生
        $uid = $this->mid;
        $arr2 = $this->getUserPostcode();
        //接收数据
        $channel = $_POST['channel'];
        $data = $this->_checkBankPrice();

        $res = D('BankCard')->isNewUser($uid);
        if($res){
            //新用户
            $contract_id = D('BankCredit')->addNewCredit($uid);
        }else{
            //老用户
            $list = D('BankCard')->getUserCard($uid);
            $this->assign('res',$list['data']);
            $this->assign('list',$list['info']);
            $cango = D('BankFinance')->isServiceOk($uid,$list['data']['id'],$data['money']);
            if($cango==0){
                $this->error('您的一千元免息订单未完成，无法操作');
            }
            $contract_id = D('BankCredit')->addOldContract($uid);
        }
        $line = $list['data']['surplus_line']?intval($list['data']['surplus_line']):5000;
        if($line<$data['money']){
            $this->error('您的额度不足');
        }
        //生成一个合同编号
        $this->assign('arr2',$arr2);
        $this->assign('contract_id',$contract_id);
        //提交
        $mark = 2;
        $this->assign('channel',$channel);
        $this->assign('mark',$mark);
        $this->assign('data',$data);
        $this->display();
    }

    //计算利息服务费
    private function _checkBankPrice(){
        $data['money'] = intval($_POST['money']);
        $data['staging'] = intval($_POST['staging']);
        $data['reason'] = t($_POST['reason']);
        if($data['money']<=0){
            $this->error('请选择申请金额');
        }
        if($data['staging']<=0){
            $this->error('请选择还款期数');
        }
        if(!$data['reason'] || $data['reason']==''){
            $this->error('请填写借款原因');
        }
        $data['stagMoney'] = getStagingPrice1($data['money'],$data['staging']);  //每月还款总额
        $data['benMoney'] = getMouPrice($data['money'],$data['staging']);       //还款每月本金
        if(!($data['stagMoney']&&$data['benMoney'])){
            $this->error('填写数据出错');
        }
        $data['servicePrice'] = $data['stagMoney'] - $data['benMoney'];         //每月服务费
//        $data['servicePrice'] = $data['stagMoney'] - $data['benMoney'];
        return $data;
    }

    //判断用户信息是否完善
    private function isAllInfo($id){
        $map['uid'] = $this->mid;
        $map['bank_id'] = $id;
        $res = D('bank_user_info')->where($map)->find();
        if($res){
            return 1;   //完善
        }else{
            return 0;   //不完善
        }
    }

    //处理数据
    private function _getUserInfoData(){
        $arr['nation'] = t($_POST['nation']);
        $arr['qq'] = t($_POST['qq']);
        $arr['class_address'] = t($_POST['class_address']);
        $arr['d_company'] = t($_POST['d_company']);
        $arr['d_name'] = t($_POST['d_name']);
        $arr['dc_mobile'] = t($_POST['dc_mobile']);
        $arr['classmate'] = t($_POST['classmate']);
        $arr['class_mobile'] = t($_POST['class_mobile']);
        $arr['class_address'] = t($_POST['class_address']);
        $arr['home'] = t($_POST['home']);
        return $arr;
    }

    //处理一千元免息提交
    public function doEditFreeList(){
        $uid = $this->mid;
        $contract_id = $_POST['contract_id'];
        $goon = D('BankFinance')->isHasFree($uid);
        if($goon==0){
            echo '错误操作，您已申请过一千元免息';
        }
        $data = $this->_getCardData();
        $data['f_mark'] = 1;
        $isReg = $this->isFromReg();
        $isYear = 1;
        if($isReg){
            $arr = $this->_getUserInfoData();
            $data['isReg'] = 1;
            //自行注册
            $res = D('BankCard','shop')->addBankCard($data,$uid,$arr);
        }else{
            $data['isReg'] = 0;
            //系统导入    .......需要写一个新的方法 添加4个字段到办卡表中
            $isYear = $this->isOneYear();    //是不是待毕业生
            $res = D('BankCard','shop')->addFourCard($data,$uid,$isYear);
//            echo $res;die;
        }
        if($res){
                //办卡信息提交成功
                D('Card','bank')->sendApplyInfo($res,$uid);
                $key = 0;
                $result = D('BankFinance','shop')->addFinance1($uid,$key,$res);
                if($result){
                    D('bank_contract')->setField('finance_id',$result,'contract_id="'.$contract_id.'"' );
                    D('BankFinance')->editStatusByYear($isYear,$res,$result);
                    $this->assign('jumpUrl', U('/PocketShop/bankPrice'));
                    echo 1;
                }else{
                    D('bank_card')->where('id='.$res)->delete();
                    echo '数据写入失败，请重新申请';
                }
            }else{
                $error = D('BankCard')->getError();
                echo $error;
            }
    }

    //用户个人详细信息完善
    private function doAllInfo($card_id){
        //判断用户信息是否完善
        $isAll = $this->isAllInfo($card_id);
        if($isAll==0){
            //需要完善信息
            $info = $this->_getUserInfoData();
            $mess['d_mobile'] = t($_POST['d_mobile']);
            $mess['referrer'] = t($_POST['referrer']);
            $res6 = D('BankCard','shop')->editBankUserInfo($info,$card_id,$mess);
            if($res6==0){
                return 0;

            }
        }
        return 1;
    }

    //办卡信息填写提交
    public function doEditCard(){
        //前面要判断 是否是毕业生 是否有卡
        $uid = $this->mid;
        if($_POST['mark']==2){
            $data1 = $this->_checkBankPrice();
        }else{
            $this->doEditFreeList();
        }
        $info = D('BankCard')->isOver($data1['money'],$uid);
        if($info==0){
            echo '您的申请已超过可用额度';exit;
        }
        //合同编号
        $contract_id = $_POST['contract_id'];    //通过uid从表里获取
        if($_POST['id']){
            $isall = $this->doAllInfo($_POST['id']);
            if($isall==0){
                $error = D('BankCard')->getError();
                echo $error;exit;
            }
            //检测是否通过风控
            $key = D('BankCard')->getListStatus($_POST['id']);
            //再次检测额度问题
            $surplus_line = M('bank_card')->where('uid = '.intval($uid).' and id = '.intval($_POST['id']))->getField('surplus_line');
            if(intval($surplus_line) == 0 )
            {
                $key = 0;
            }
            $res = D('BankFinance')->addFinance($uid,$data1,$key,$_POST['id']);
            if($res){
                D('bank_contract')->setField('finance_id',$res,'contract_id="'.$contract_id.'"' );
                //$this->assign('jumpUrl', U('/Bank/bankPrice'));
                echo '提交成功，等待审核';exit;
            }else{
                echo '数据写入失败，请重新申请';exit;
            }
        }else{
            $data = $this->_getCardData();
            $data['f_mark'] = 0;
            $arr = $this->_getUserInfoData();
            $data['isReg'] = 0;
            $data['isReg'] = $this->isFromReg();
            $res = D('BankCard','shop')->addBankCard($data,$uid,$arr);
            $isYear = $this->isOneYear();
            if($res){

                //等待调用办卡接口
                D('Card','bank')->sendApplyInfo($res,$uid);
                $key = 0;
                $result = D('BankFinance')->addFinance($uid,$data1,$key,$res);
                //echo $result;die;
                if($result){
                    D('bank_contract')->setField('finance_id',$result,'contract_id="'.$contract_id.'"' );
                    D('BankFinance')->editStatusByYear($isYear,$res,$result);
                    echo 1;exit;
                }else{
                    D('bank_card')->where('id='.$res)->delete();
                    echo '数据写入失败，请重新申请';exit;
                }
            }else{
                $error = D('BankCard')->getError();
                echo $error;
            }
        }
    }

    //处理数据
    private function _getCardData(){
        $data['realname'] = t($_POST['realname']);
        $data['address'] = t($_POST['address']);
        $data['ctf_id'] = t($_POST['ctf_id']);
        $data['email_bill'] = t($_POST['email_bill']);
        $data['mobile'] = t($_POST['mobile']);
        $data['d_mobile'] = t($_POST['d_mobile']);

        $data['post_code'] = intval($_POST['post_code']);
        $channel = $_POST['channel'];
        if($channel){
            $data['channel'] = D('CMB','bank')->decrypt($channel);
        }
        $data['surplus_line'] = 5000;
        $data['total_line'] = 5000;

        return $data;
    }

    //个人订单
    public function myBankList(){
        $this->__isJump(2);
        //echo getStagingPrice1(500,1);die;
        $uid = $this->mid;
        if(empty($uid)){
            $this->error('请登陆后操作');
        }
        $arr = array('审核中','还款中','还款结束');
        $map['uid'] = $uid;
        $status = intval($_GET['status']);
        if($status){
            $this->assign('status',$status);
            $map['status'] = $status;
        }
        $list = D('bank_finance')->where($map)->field('id,money,ctime,stime,etime,reason,staging,status,mark')->order('id DESC')->findAll();
        foreach($list as &$val){

            if($val['status']>2){
                $val['sta'] = '审核中';
            }else{
                $val['sta'] = $arr[$val['status']];
            }
            if($val['mark']==2){
                $money = intval($val['money']);
                $val['stagMoney'] = getStagingPrice1($money,$val['staging']);
                $val['benMoney'] = getMouPrice($money,$val['staging']);
            }else{
                $val['stagMoney'] = 1000;
                $val['benMoney'] = 1000;
            }
            $val['service'] = $val['stagMoney']-$val['benMoney'];
        }
        $this->assign('list',$list);
        $this->display();
    }

    //订单详情
    public function bankListDetail(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $list = D('bank_finance')->where('id='.$id)->find();
        $map['uid'] = $list['uid'];
        $map['status'] = array('neq',3);
        $blist = D('bank_card')->where($map)->find();
        if($list['mark']==2){
            $list['money'] = intval($list['money']);
            $list['stagMoney'] = getStagingPrice1($list['money'],$list['staging']);
            $list['benMoney'] = getMouPrice($list['money'],$list['staging']);
        }else{
            $list['stagMoney'] = 1000;
            $list['benMoney'] = 1000;
        }
        $str = '****************';
        $blist['ctf_id'] = substr_replace($blist['ctf_id'],$str,1,16);
        $list['service'] = $list['stagMoney']-$list['benMoney'];
        $this->assign('blist',$blist);
        $this->assign('list',$list);
        $this->display();
    }

    //个人中心
    public function personalCenter(){
        $uid = $this->mid;
        if(empty($uid)){
            $this->error('请登陆后操作');
        }
        $list = D('bank_card')->where('uid='.$uid)->find();
        $str = '****************';
        $str1 = '**************';
        if($list['card_no']){
            $list['card_no'] = D('CMB','bank')->decrypt($list['card_no']);
            $list['card_no'] = substr_replace($list['card_no'],$str1,1,14);
        }
        $list['ctf_id'] = substr_replace($list['ctf_id'],$str,1,16);
        $this->assign('list',$list);
        $this->display();
    }

    //用户修改手机号码
    public function editMobile(){
        $id = intval($_POST['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $check = D('bank_card')->getField('check','id='.$id);
        if($check!=1){
            $this->error('您没有权限修改手机号');
        }
        $mobile = t($_POST['mobile']);
        $res = D('BankCard')->changeMobile($id,$mobile);
        if($res){
            $this->success('修改手机成功');
        }else{
            $error = D('BankCard')->getError();
            $this->error($error);
        }
    }

    //查看还款计划
    public function lookRepayment(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $mlist = D('bank_finance')->where('id='.$id)->field('money,staging')->find();
        $money = intval($mlist['money']);
        $money = getMouPrice($money,$mlist['staging']);
        $list = D('bank_finance_detail')->where('bank_finance_id='.$id)->field('money,surp_money,stime,status')->order('id ASC')->findAll();
        $i = 0;
        foreach($list as &$v){
            $i += 1;
            $v['num'] = $i;
            $v['hasDo'] = $money;
        }
        $this->assign('list',$list);
        $this->display();
    }

//    //测试口袋大学
//    public function koudai(){
//        $map['school_id'] = 473;
////        $map['status'] = 2;
//        $clist = D('bank_card')->where($map)->field('id,realname,status')->findAll();
//        $ids = array();
//        $arr = array(15,43,216,352,5210);
//        foreach($clist as $v){
//            if(!in_array($v['id'], $arr)){
//                $ids[] = $v['id'];
//            }
//        }
//        $map1['bank_card_id'] = array('in',$ids);
//        D('bank_finance')->where($map1)->delete();
//        D('bank_finance_detail')->where($map1)->delete();
//        dump($ids);
//    }
//
    public function test22(){
        //$str = '123456';
        file_put_contents("./123123/test.json","Hello World. Testing!");
        header("Content-Type:application/force-download");
        header("Content-Disposition:attachment;filename=test.json");  //下载
        readfile("./123123/test.json");    //保存
    }
}

?>

