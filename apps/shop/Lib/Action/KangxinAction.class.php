<?php
//header("Content-type:text/html;charset=utf-8");
import('home.Action.PubackAction');

class KangxinAction extends PubackAction {

    public function index(){
        $school = D('school')->where('pid=0 and cityId=1')->field('id,title')->findAll();
        $this->assign('school',$school);
        $uid=$this->mid;
        if($uid==2172494){
            $mark = 0;
        }else{
            $mark = 1;
        }
        $this->assign('mark',$mark);
        $this->display();
    }

    //业务后台看办卡数据
    public function cardCount(){
        $type = t($_POST['type'])?t($_POST['type']):'all';
        $val = intval($_POST['val']);
        $stime = t($_POST['stime']);
        $etime = t($_POST['etime']);
        if(($type!='all')&&empty($val)){
            $this->error('选择条件不完整');
        }
        if(empty($val)){
            $val = 0;
        }
        if(empty($stime)&&empty($etime)){
            $map['status'] = array('lt',2);
            $list['shenqin'] = D('bank_card')->field('distinct uid')->where($map)->count();  //申请数
            $list['success'] = D('bank_card')->field('distinct uid')->where('status=2')->count();    //开卡成功数
            $list['default'] = D('bank_card')->field('distinct uid')->where('status > 2')->count();    //办卡失败数
            $map1['status'] = array('in','1,2');     //状态为1和2的所有订单
            $arr = D('bank_finance')->where($map1)->field('count(distinct(bank_card_id)) as num')->find();
            $list['domoney'] = $arr['num'];          //已借款数
        }elseif($stime&&$etime){
            $list['shenqin'] = D('bank_card')->field('distinct uid')->where('status < 2 and ctime >="'.$stime.'" and ctime <="'.$etime.'"')->count();  //申请数
            $list['success'] = D('bank_card')->field('distinct uid')->where('status=2 and suctime >="'.$stime.'" and suctime<="'.$ctime.'"')->count();    //开卡成功数
            $list['default'] = D('bank_card')->field('distinct uid')->where('status > 2 and ctime >="'.$stime.'" and ctime<="'.$etime.'"')->count();    //办卡失败数
            $arr = D('bank_finance')->where('status in (1,2)')->field('count(distinct(bank_card_id)) as num')->find();
            $list['domoney'] = $arr['num'];          //已借款数
        }else{
            $this->error('请输入完整的时间段');
        }

        dump($list);die;
    }

    //查看某个时间段所有学生提交的办卡申请
    public function allApplyInfo(){
        $stime = t($_POST['stime']);
        $etime = t($_POST['etime']);
        $sid = intval($_POST['school_id']);
        if(empty($stime)){
            $this->error('请输入查询开始时间');
        }
        if(empty($etime)){
            $etime = date('Y-m-d');
        }

        if($sid>0){
            $list = D('bank_card')->where('ctime>="'.$stime.'" and ctime<="'.$etime.'" and school_id='.$sid)->field('uid,ctf_id,mobile,ctime')->findAll();
        }else{
            $list = D('bank_card')->where('ctime>="'.$stime.'" and ctime<="'.$etime.'"')->field('uid,ctf_id,mobile,ctime')->findAll();

        }
        if(empty($list)){
            $this->error('暂无数据');
        }
        foreach($list as &$v){
            $v['ctf_id'] = '`'.$v['ctf_id'];
		$user = M('user')->where('uid='.$v['uid'])->field('realname,email,sid1,year,major,sex')->find();
		$v['uid'] = $user['realname'];
		$v['num'] = ' '.getUserEmailNum($user['email']);
		$v['sid1'] = tsGetSchoolName($user['sid1']);
		$v['year'] = $user['year'];
		$v['major'] = $user['major'];
		$v['sex'] = $user['sex']?'男':'女';

        }
        $arr = array('姓名','身份证号','电话号码','申请时间','学号','院系','年级','专业','性别');
        $title = '办卡数据明细';
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);
    }

    //办卡明细查询
    public function allApply(){
        $status = intval($_POST['status']);
        $list = D('bank_card')->where('status='.$status)->field('ctime,uid,ctf_id,mobile,card_no,school_id,suctime')->findAll();
        if(empty($list)){
            $this->error('暂无数据');
        }
        $this->doDataCard($list,$status);
    }

//    //
//    public function test98(){
//        $list = D('bank_finance')->where('status=98')->field('bank_card_id')->findAll();
//        foreach($list as &$v){
//            $res = D('bank_card')->where('id='.$v['bank_card_id'])->field('ctime,uid,ctf_id,mobile,card_no,school_id,suctime')->find();
//            $v['ctime'] = $res['ctime'];
//            $v['uid'] = $res['uid'];
//            $v['ctf_id'] = $res['ctf_id'];
//            $v['mobile'] = $res['mobile'];
//            $v['card_no'] = $res['card_no'];
//            $v['school_id'] = $res['school_id'];
//            $v['suctime'] = $res['suctime'];
//            unset($v['bank_card_id']);
//        }
//        $this->doDataCard($list);
//
//    }

    //处理数据
    private function doDataCard($list,$status=2){
        foreach($list as &$v){
            $v['ctf_id'] = '`'.$v['ctf_id'];
            $user = M('user')->where('uid='.$v['uid'])->field('realname,email')->find();
            $v['uid'] = $user['realname'];
            $v['num'] = ' '.getUserEmailNum($user['email']);
            $v['school_id'] = tsGetSchoolName($v['school_id']);
            if($status==2){
                $v['card_no'] = D('CMB','bank')->decrypt($v['card_no']);
                $v['card_no'] = '`'.$v['card_no'];
            }
        }
        $arr = array('申请时间','姓名','身份证号','电话号码','卡号','学校','成功(失败)时间','学号');
        $title = '所有用户办卡数据明细';
        array_unshift($list, $arr);
//        dump($list);die;
        service('Excel')->export2($list, $title);
//        service('Excel')->export2($list, $title);
    }

    //订单状态为40的各类型卡
    public function cationCardInfo(){
        //接受一个mark 判断找什么类型的订单
        $mark = intval($_POST['mark']);
        switch($mark){
            case 1:
                //手机号码不对
                $list = D('bank_card')->where('`check`=1')->field('ctime,uid,ctf_id,mobile,card_no,school_id,suctime')->findAll();
                break;
            case 2:
                //风控驳回
                $list = D('bank_card')->where('allow_risk=2 and status=2')->field('ctime,uid,ctf_id,mobile,card_no,school_id,suctime')->findAll();
                break;
            case 3:
                //本四转三
                $list = D('bank_card')->where('f_mark=2 and status=2')->field('ctime,uid,ctf_id,mobile,card_no,school_id,suctime')->findAll();
                break;
            case 4:
                //本四转三
                $list = D('bank_card')->where('free_allow_risk=0 and status=2')->field('ctime,uid,ctf_id,mobile,card_no,school_id,suctime')->findAll();
                break;
            default:
                $this->error('错误操作');
                break;
        }
        if(empty($list)){
            $this->error('暂无数据');
        }
        $this->doDataCard($list);
    }

    //业务查看一段时间某个学校的借款数据明细
    public function oneSchoolDetail(){
        $list = $this->getTgDate();
        foreach($list as $key=>$val){
            $result = D('bank_card')->where('id='.$val['bank_card_id'])->field('realname,uid,ctf_id,school_id,mobile,address')->find();
            $list[$key]['realname'] = $result['realname'];
            $list[$key]['ctf_id'] = '`'.$result['ctf_id'];
            $list[$key]['mobile'] = $result['mobile'];
            $list[$key]['address'] = $result['address'];
            $list[$key]['school'] = D('school')->getField('title','id='.$result['school_id']);
            $list[$key]['contract'] = D('bank_credit')->getField('contract','uid='.$result['uid']);
            $list[$key]['ptm'] = D('bank_contract')->getField('ptm','finance_id='.$val['id']);
            $list[$key]['rate'] = '8%';
            unset($list[$key]['id']);
            unset($list[$key]['bank_card_id']);
            $stime = strtotime($val['stime']);
            $time2 = strtotime('-1 month',$stime);
            $list[$key]['stime'] = date('Y-m-d',$time2);
        }

        $arr = array('借款金额', '期限','日期','客户姓名','身份证号码','电话号码','宿舍地址','学校','授信合同号','提款合同号','利率');
        $title = '放款数据明细';
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);
    }

    //业务获取数据的条件语句
    private function getSql($type,$val){
        if($type=='school'){
            //某一个学校的数据
            $arr = array();
            $list = D('bank_card')->where('school_id ='.$val)->field('id,uid')->findAll();
            foreach($list as $v){
                $arr[] = $v['id'];                   //符合条件的bank_card_id
            }
            $sql = 'and bank_card_id in'.$arr;
        }else{
            //某一个城市的数据
            $map['b.cityId'] = $val;
            $res1 = $this->table("ts_bank_card AS a ")->join("ts_school AS b ON a.school_id=b.id")
                    ->field('a.id,a.uid')->where($map)->findAll();
            $arr = array();
            foreach($res1 as $v){
                $arr[] = $v['id'];
            }

            $sql = 'and bank_card_id in'.$arr;
        }
        return $sql;
    }

    //处理借款数据
    private function getTgDate(){
        $type = t($_POST['type'])?t($_POST['type']):'all';
        $val = intval($_POST['val']);

        if(($type!='all')&&empty($val)){
            $this->error('选择条件不完整');
        }
        $sql = '';
        if($type!='all'){
            $sql = $this->getSql($type,$val);
        }
        $stime = t($_POST['stime']);
        $etime = t($_POST['etime']);

        if(empty($stime)&&empty($etime)){
            $this->error('请输入查询日期');
        }
        $stime1 = strtotime($stime);
        $etime1 = strtotime($etime);
        $time1 = strtotime('+1 month',$stime1);      //查询开始的扣款开始时间
        $time2 = strtotime('+1 month',$etime1);       //查询结束的扣款开始时间
        $data['stime'] = date('Y-m-d',$time1);
        $data['etime'] = date('Y-m-d',$time2);

        if($stime&&$etime){
            //有开始时间和结束时间
//            $map['stime'] = array('egt',$data['stime']);
//            $map['stime'] = array('elt',$data['etime']);
//            $map['status'] = array('in','1,2');
            $list = D('bank_finance')->where('stime>="'.$data['stime'].'"and stime<="'.$data['etime'].'"and status in(1,2)'.$sql)->field('id,bank_card_id,money,staging,stime')->findAll();
            $sql = D('bank_finance')->getLastSql();
//
        }else{
            //只有一个时间
            $map['stime'] = $_POST['stime']?$data['stime']:$data['etime'];
            $list = D('bank_finance')->where('stime = "'.$map['stime'].'" and status in(1,2)'.$sql)->field('id,bank_card_id,money,staging,stime')->findAll();
        }
        return $list;
    }

    //根据学校导出该校学生的办卡信息
    public function schoolCardInfo(){
        $sid = intval($_POST['school']);
        $cityId = D('citys')->getField('id',"city='苏州'");
        $map['pid'] = 0;
        $map['cityId'] = $cityId;
        $school_list = D('school')->where($map)->field('id,title')->findAll();
        if($sid){
            //确认是否需要以及办卡成功
            $list = D('bank_card')->where('school_id='.$sid)->findPage(15);
        }
    }

    //根据学生的身份证账号导出学生的信息
    public function ctfIdInfo(){
        $ctf_id = t($_POST['ctf_id']);
        if(!isCtfId($ctf_id)){
            $this->display();die;
        }
        $ulist = D('bank_card')->where('ctf_id='.$ctf_id)->find();
        $uid = $ulist['uid'];
        $userinfo = D('bank_user_info')->where('bank_id='.$ulist['id'])->find();
        if($userinfo['education']==1){
            $userinfo['education']='专科';
        }else{
            $userinfo['education']='本科';
        }
        $num = substr($ctf_id,-2,1);
        if(($num%2)==1){
            $ulist['sex'] = '男';
        }else{
            $ulist['sex'] = '女';
        }
        $ulist['school'] = D('school')->getField('title','id='.$ulist['school_id']);
        $ulist['department'] = D('school')->getField('title','id='.$userinfo['sid1']);   //院系
        $ulist['major'] = D('user')->getField('major','uid='.$ulist['uid']);     //专业
        $ulist['grade'] = D('user')->getField('year','uid='.$ulist['uid']);     //年级
        $email = D('user')->getField('email','uid='.$ulist['uid']);
        $arr = explode('@',$email);
        $ulist['no'] = $arr[0];    //学号
        //订单信息
        $map['status'] = array('in','1,2');
        $map['bank_card_id'] = $ulist['id'];
        $list = D('bank_finance')->where($map)->field('id,money,staging,stime')->findAll();

        $arr['contract'] = D('bank_credit')->getField('contract','uid='.$uid);
        $count = 0;
        $sum = 0;
        foreach($list as $key=>$val){
            $count += $val['money'];
            $sum += 1;
            $list[$key]['ptm'] = D('bank_contract')->getField('ptm','finance_id='.$val['id']);
            $map1['status'] = array('in','0,1');
            $map1['bank_finance_id'] = $val['id'];
            $num2 = D('bank_finance_detail')->where($map1)->count();
            $money = intval($val['money']);
            $benPrice = getMouPrice($money,$val['staging']);
            $list[$key]['remainMoney'] = $benPrice*$num2;       //剩余的钱
            $map1['status'] = 4;
            $num3 = D('bank_finance_detail')->where($map1)->count();
            $list[$key]['overdue'] = $num3;       //逾期情况
        }
        $arr['count'] = $count;
        $arr['sum'] = $sum;
        $this->assign('arr',$arr);
        $this->assign('info',$userinfo);
        $this->assign('ulist',$ulist);
        $this->assign('list',$list);
        $this->display();
    }

    //ajax获取查询条件
    public function getCondition(){
        $type = t($_POST['type']);
        if($type=='school'){
            //学校
            $list = D('school')->where('pid=0')->field('id,title')->findAll();
        }else{
            //城市
            $list = D('citys')->where('pid=1')->field('id,city')->findAll();
        }
        echo json_encode($list);
    }

    //康欣统计表查询
    public function kxCount(){

        $type = t($_POST['type'])?t($_POST['type']):'all';
        $val = intval($_POST['val']);
        if(($type!='all')&&empty($val)){
            $this->error('选择条件不完整');
        }
        if(empty($val)){
            $val = 0;
        }

        $key = 'KangXin_kxCount_'.$type.'_'.$val;
        $cache = Mmc($key);
        if ($cache !== false) {
            $assign = json_decode($cache, true);
            $this->assign('list',$assign['list']);
            $this->assign('result',$assign['result']);
            $this->display('kxCount');
            exit;
        }

        //die('ok');
        $res = D('BankFinance','shop')->getMoney($type,$val);
        //处理返回数据
        $num = 0;
        $count = 0;
        foreach($res as $val){
            $map['status'] = 2;
            $map['bank_finance_id'] = $val['id'];
            $num1 = D('bank_finance_detail')->where($map)->count();
            $money = intval($val['money']);
            if($num1==0){
                $num += $money;
            }else{
                $benPrice = getMouPrice($money,$val['staging']);
                $num += $benPrice*($val['staging']-$num1);    //贷款余额
            }
            $count += 1;                   //贷款笔数
            unset($map);
        }
        $list['num'] = $num;
        $list['count'] = $count;
        $result = D('BankFinance')->getLeave($type,$val);   //获取逾期余额 逾期笔数
        $this->assign('list',$list);
        $this->assign('result',$result);

        $assign['list'] = $list;
        $assign['result'] = $result;
        Mmc($key, json_encode($assign),0,1800);

        $this->display('kxCount');

    }

    //处理借款数据
    private function getKxDate(){
        $stime = t($_POST['stime']);
        $etime = t($_POST['etime']);
        if(empty($stime)&&empty($etime)){
            $this->error('请输入查询日期');
        }
        $stime1 = strtotime($stime);
        $etime1 = strtotime($etime);
        $time1 = strtotime('+1 month',$stime1);      //查询开始的扣款开始时间
        $time2 = strtotime('+1 month',$etime1);       //查询结束的扣款开始时间
        $data['stime'] = date('Y-m-d',$time1);
        $data['etime'] = date('Y-m-d',$time2);
        if($stime&&$etime){
            //有开始时间和结束时间
//            $map['stime'] = array('egt',$data['stime']);
//            $map['stime'] = array('elt',$data['etime']);
//            $map['status'] = array('in','1,2');
            $list = D('bank_finance')->where('stime>="'.$data['stime'].'"and stime<="'.$data['etime'].'"and status in(1,2)')->field('id,bank_card_id,money,staging,stime,mark')->findAll();
            $sql = D('bank_finance')->getLastSql();
//
        }else{
            //只有一个时间
            $map['stime'] = $_POST['stime']?$data['stime']:$data['etime'];
            $map['status'] = array('in','1,2');
            $list = D('bank_finance')->where($map)->field('id,bank_card_id,money,staging,stime,mark')->findAll();
        }
        return $list;
    }

    //获取一段时间的数据总信息
    public function getAllCount(){
        $stime = t($_POST['stime']);
        $etime = t($_POST['etime']);
        if(empty($stime)&&empty($etime)){
            $this->display();die;
        }
        $stime1 = strtotime($stime);
        $etime1 = strtotime($etime);
        $time1 = strtotime('+1 month',$stime1);      //查询开始的扣款开始时间
        $time2 = strtotime('+1 month',$etime1);       //查询结束的扣款开始时间
        $data['stime'] = date('Y-m-d',$time1);
        $data['etime'] = date('Y-m-d',$time2);
        if($stime&&$etime){
            $map['etime'] = $data['stime'];
            $map['stime'] = $data['etime'];
            $list = D('bank_finance_detail')->where('stime>="'.$map['etime'].'"and stime<="'.$map['stime'].'"and status = 2')->field('bank_finance_id,money,etime')->findAll(); //还款的数据
        }else{
            $stime = $_POST['stime']?$data['stime']:$data['etime'];
            $map['etime'] = $stime;
            $list = D('bank_finance_detail')->where('stime="'.$map['etime'].'"and status = 2')->field('bank_finance_id,money,etime')->findAll(); //还款的数据
        }


        $sql = D('bank_finance_detail')->getLastSql();

        $hmoney = 0;
        $hnum = 0;
        $arr1 = array();
        foreach($list as $val){
            $list3 = D('bank_finance')->where('id='.$val['bank_finance_id'])->field('id,money,staging')->find();
            //未完待续
            if(!in_array($list3['id'],$arr1)){
                $arr1[]=$list3['id'];
                $hnum += 1;
            }                           //如果不是之前有的订单 笔数加一
            $money = intval($list3['money']);
            $benPrice = getMouPrice($money,$list3['staging']);
            $hmoney +=  $benPrice;
            unset($list3);
        }
        $data['hprice'] = $hmoney;    //还款总金额
        $data['hnum'] = $hnum;           //还款笔数

        //逾期的数据
        $list5 = D('bank_finance_detail')->where('stime>="'.$map['etime'].'"and stime<="'.$map['stime'].'"and status = 4')->field('bank_finance_id,etime')->findAll(); //逾期的数据
        $yprice = 0;
        $ynum = 0;
        $arr3 = array();
        foreach($list5 as $val){
            $list4 = $this->where('id='.$val['bank_finance_id'])->field('id,money,staging')->find();
            if(!in_array($list4['id'],$arr3)){
                $arr3[]=$list4['id'];
                $ynum += 1;
            }                           //如果不是之前有的订单 笔数加一
            $money = intval($list4['money']);
            $benPrice = getMouPrice($money,$list4['staging']);
            $yprice +=  $benPrice;
            unset($list4);
        }
        $data['yprice'] = $yprice;    //逾期总金额
        $data['ynum'] = $ynum;         //逾期笔数

        $list1 = $this->getKxDate();         //一段时间的借款数据
        $money = 0;
        $num = 0;
        foreach($list1 as $val){
            $money += $val['money'];
            $num +=1;
        }
        $data['jprice'] = $money;    //借款总金额
        $data['jnum'] = $num;                //借款笔数

        $this->assign('data',$data);
        $this->display();
    }

     //康欣每日放款明细
    public function kxLoanDetail(){
        $list = $this->getKxDate();
        foreach($list as $key=>$val){
            $result = D('bank_card')->where('id='.$val['bank_card_id'])->field('realname,uid,ctf_id,school_id,mobile,address')->find();
            //dump($result);die;
            $list[$key]['realname'] = $result['realname'];
            $list[$key]['ctf_id'] = '`'.$result['ctf_id'];
            $list[$key]['mobile'] = $result['mobile'];
            $list[$key]['address'] = $result['address'];
            $list[$key]['school'] = D('school')->getField('title','id='.$result['school_id']);
            $list[$key]['contract'] = D('bank_credit')->getField('contract','uid='.$result['uid']);
            $list[$key]['ptm'] = D('bank_contract')->getField('ptm','finance_id='.$val['id']);
            if($val['mark']==1){
                $list[$key]['rate'] = '0%';
            }else{
                $list[$key]['rate'] = '8%';
            }
            unset($list[$key]['mark']);
            unset($list[$key]['id']);
            unset($list[$key]['bank_card_id']);
            $stime = strtotime($val['stime']);
            $time2 = strtotime('-1 month',$stime);
            $list[$key]['stime'] = date('Y-m-d',$time2);
        }
        //dump($list);die;
        $arr = array('借款金额', '期限','日期','客户姓名','身份证号码','电话号码','宿舍地址','学校','授信合同号','提款合同号','利率');
        $title = '放款数据明细';
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);
    }


    //康欣每日还款统计
    public function kxRepaymentDetail(){
        $stime = t($_POST['stime']);
        $etime = t($_POST['etime']);
        $uid = $this->mid;
        if(empty($stime)&&empty($etime)){
            $this->error('请输入查询日期');
        }
        if($stime&&$etime){
            $map['etime'] = array('egt',$stime.' 00:00:00');
            $map['etime'] = array('elt',$etime.' 59:59:59');
            $map['status'] = 2;
        }else{
            $stime = $_POST['stime']?$stime:$etime;
            $map['etime'] = array('egt',$stime.' 00:00:00');
            $map['etime'] = array('elt',$stime.' 59:59:59');
            $map['status'] = 2;
        }
        $list = D('bank_finance_detail')->where($map)->field('bank_card_id,bank_finance_id,money,etime')->findAll();
        foreach($list as $key=>$val){
            $result = D('bank_card')->where('id='.$val['bank_card_id'])->field('realname,uid,ctf_id,school_id,mobile,address')->find();
            $list[$key]['realname'] = $result['realname'];
            $list[$key]['ctf_id'] = $result['ctf_id'];
            $list[$key]['mobile'] = $result['mobile'];
            $list[$key]['address'] = $result['address'];
            $list[$key]['school'] = D('school')->getField('title','id='.$result['school_id']);
            $list[$key]['contract'] = D('bank_credit')->getField('contract','uid='.$result['uid']);
            $list[$key]['ptm'] = D('bank_contract')->getField('ptm','finance_id='.$val['bank_finance_id']);
            $money = D('bank_finance')->getField('money','id='.$val['bank_finance_id']);
            $money = intval($money);
            $list[$key]['interest'] = getKxService($money);
            if($uid!=2172494){
                $benPrice = D('bank_finance')->getField('staging','id='.$val['bank_finance_id']);  //分期数
            }
        }
        dump($list);die;
        $arr = array('用户总数', '初始化用户总数');
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);
    }

//    public function test11(){
//        $map['status'] = 1;
//        $map['stime'] = '2015-11-17';
//        $list = D('bank_finance')->where($map)->field('id,status')->findAll();
//
//        $id=array();
//        foreach($list as $v){
//            $id[] = $v['id'];
//        }
//        $map1['bank_finance_id'] = array('in',$id);
//        $data['status'] = 1;
//        $arr = D('bank_finance_detail')->where($map1)->field('id,bank_finance_id,status,stime')->findAll();
//        dump($arr);
//    }

    //康欣借贷情况统计表
    public function kxLoan(){
        $uid = $this->mid;
        if($uid != 2172494){
            redirect(U('shop/Kangxin/tgLoan'));
        }
        $map['status'] = array('in','1,2');
        $list = D('bank_finance')->where($map)->field('id,bank_card_id,money,staging,stime,mark')->findAll();
        //dump($list);die;
        foreach($list as $key=>$val){
            $result = D('bank_card')->where('id='.$val['bank_card_id'])->field('realname,uid,ctf_id,school_id,mobile,address,card_no')->find();
            $list[$key]['realname'] = $result['realname'];
            $card = D('CMB','bank')->decrypt($result['card_no']);
            $list[$key]['card_no'] = '`'.$card;
            $list[$key]['ctf_id'] = '`'.$result['ctf_id'];
            $list[$key]['mobile'] = $result['mobile'];
            $list[$key]['address'] = $result['address'];
            $list[$key]['school'] = D('school')->getField('title','id='.$result['school_id']);
            $list[$key]['id'] = D('bank_credit')->getField('contract','uid='.$result['uid']);  //授信合同编号
            $list[$key]['id'] = '`'.$list[$key]['id'];
            $list[$key]['bank_card_id'] = D('bank_contract')->getField('ptm','finance_id='.$val['id']);//取款合同编号
            $money = intval($val['money']);
            if($val['mark']==1){
                $list[$key]['interest'] = '0%';                       //利率
                $list[$key]['rate'] = 0;            //利息总额
            }else{
                $list[$key]['interest'] = '8%';                 //利率
                $rate = getKxService($money);
                $list[$key]['rate'] = $rate*$val['staging'];        //利息总额
            }
            $list[$key]['all'] = $list[$key]['rate']+$money;        //还款总额
            $time = strtotime($val['stime']);
            $stime1 = strtotime('-1 month',$time);                     //借款日时间戳
            $etime1 = strtotime('+'.$val['staging'].' month',$stime1);     //到期日时间戳
            $list[$key]['stime1'] = date('Y-m-d',$stime1);                  //借款日
            $list[$key]['etime1'] = date('Y-m-d',$etime1);                  //到期日

            unset($list[$key]['mark']);
            unset($list[$key]['stime']);
        }
        //dump($list);die;
        $arr = array('授信合同号','提款合同号','借款金额','分期数','客户姓名','卡号','身份证号码','电话号码','宿舍地址','学校','利率','利息总计','还款总额','借款日','到期日');
        $title = '借贷情况统计表';
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);

    }

    //客服后台导出接待情况统计表
    public function tgLoan(){
        $fields = 'bf.id,
bf.bank_card_id,
bf.money,
bf.interest,
bf.stime,
bf.staging,
bf.mark,
bcd.uid,
bcd.realname,
bcd.school_id,
bcd.ctf_id,
bcd.mobile,
bcd.address,
bcd.card_no,
s.title,
bcr.contract,
bcon.ptm';
        $list = array();
        foreach (array('1', '2') as $status) {
            $l = D()->table('ts_bank_finance AS bf')
                ->where(array('bf.status' => $status))
                ->field($fields)
                ->join('ts_bank_card AS bcd ON bf.bank_card_id = bcd.id')
                ->join('ts_school AS s ON bcd.school_id = s.id')
                ->join('ts_bank_credit AS bcr ON bcd.uid = bcr.uid')
                ->join('ts_bank_contract AS bcon ON bf.id = bcon.finance_id')
                ->select();
            $list = array_merge($list, $l);
        }


        //$list = D('bank_finance')->where($map)->field('id,bank_card_id,money,staging,stime,mark,interest')->findAll();
        //dump($list);exit;
        $tmp = array();
        foreach ($list as $val) {
            if($val['mark']==1){
                $interest = 0;            //利息总额
                $service = 0;         //总de服务费
                $rate = '0%';                       //利率
            }else{
                $kxinterest = getKxService(intval($val['money']));
                $interest = $kxinterest*$val['staging'];        //利息总额
                $service = $val['interest'] - $interest;   //总de服务费
                $rate = '8%';                 //利率
            }
            $card = D('CMB','bank')->decrypt($val['card_no']);

            $time = strtotime($val['stime']);
            $stime1 = strtotime('-1 month',$time);                     //借款日时间戳
            $etime1 = strtotime('+'.$val['staging'].' month',$stime1);     //到期日时间戳

            $tmp[] = array(
                $val['contract'], //授信合同编号
                $val['ptm'], //取款合同编号
                $val['money'], // 借款金额
                $val['staging'], // 分期数
                $rate, //利率
                $interest,// 利息总计
                $service,// 服务费
                $val['interest']+$val['money'],// 还款总额
                date('Y-m-d',$stime1), // 借款日
                date('Y-m-d',$etime1), // 到期日
                $val['realname'], //客户姓名
                '`' . $card, // 卡号
                '`'. $val['ctf_id'],// 身份证号码
                $val['mobile'],// 电话号码
                $val['address'],// 宿舍地址
                $val['title'],// 学校

            );
        }
        //dump($tmp);exit;
        /*foreach($list as $key=>$val){
            $result = D('bank_card')->where('id='.$val['bank_card_id'])->field('realname,uid,ctf_id,school_id,mobile,address,card_no')->find();
            $list[$key]['realname'] = $result['realname'];
            $card = D('CMB','bank')->decrypt($result['card_no']);
            $list[$key]['card_no'] = '`'.$card;
            $list[$key]['ctf_id'] = '`'.$result['ctf_id'];
            $list[$key]['mobile'] = $result['mobile'];
            $list[$key]['address'] = $result['address'];
            $list[$key]['school'] = D('school')->getField('title','id='.$result['school_id']);
            $list[$key]['id'] = D('bank_credit')->getField('contract','uid='.$result['uid']);  //授信合同编号
            $list[$key]['id'] = '`'.$list[$key]['id'];
            $list[$key]['bank_card_id'] = D('bank_contract')->getField('ptm','finance_id='.$val['id']);//取款合同编号
            $money = intval($val['money']);
            if($val['mark']==1){
                $list[$key]['rate'] = 0;            //利息总额
                $list[$key]['service'] = 0;         //总de服务费
                $list[$key]['interest'] = '0%';                       //利率
            }else{
                $rate = getKxService($money);
                $list[$key]['rate'] = $rate*$val['staging'];        //利息总额
                $list[$key]['service'] = $val['interest'] - $list[$key]['rate'];   //总de服务费
                $list[$key]['interest'] = '8%';                 //利率
            }
            $list[$key]['all'] = $val['interest']+$money;        //还款总额

            $time = strtotime($val['stime']);
            $stime1 = strtotime('-1 month',$time);                     //借款日时间戳
            $etime1 = strtotime('+'.$val['staging'].' month',$stime1);     //到期日时间戳
            $list[$key]['stime1'] = date('Y-m-d',$stime1);                  //借款日
            $list[$key]['etime1'] = date('Y-m-d',$etime1);                  //到期日
            unset($list[$key]['mark']);
            unset($list[$key]['stime']);
            //unset($list[$key]['interest']);
        }*/
        //dump($list);die;
        $arr = array('授信合同号','提款合同号','借款金额','分期数','利率','利息总计','服务费','还款总额','借款日','到期日','客户姓名','卡号','身份证号码','电话号码','宿舍地址','学校');
        $title = '借贷情况统计表';
        array_unshift($tmp, $arr);
        //$this->display();
        service('Excel')->export2($tmp, $title);
    }


    //所有订单信息
    public function allFinanceList(){
        $list = D('bank_finance')->field('bank_card_id,money,staging,stime,mark,status')->findAll();
        foreach($list as &$v){
            $res = D('bank_card')->where('id='.$v['bank_card_id'])->field('realname,ctf_id,mobile,school_id,status')->find();
            $v['realname'] = $res['realname'];
            $v['ctf_id'] = '`'.$res['ctf_id'];
            $v['mobile'] = $res['mobile'];
            $v['school'] = D('school')->getField('title','id='.$res['school_id']);
            if($res['status']==2){
                $v['card_status'] = '办卡成功';
            }elseif($res['status']==3){
                $v['card_status'] = '办卡失败';
            }else{
                $v['card_status'] = '正在处理';
            }
            if($v['mark']==1){
                $v['mark']='一千元免息';
            }else{
                $v['mark']='非免息';
            }
            switch($v['status']){
                case 0:
                    $v['status'] = '未放款';
                    break;
                case 1:
                    $v['status'] = '已放款';
                    break;
                case 2:
                    $v['status'] = '还款结束';
                    break;
                case 40:
                    $v['status'] = '等待放款';
                    break;
                case 44:
                    $v['status'] = '订单作废';
                    break;
                default:
                    $v['status'] = '正在处理';
                    break;
            }
            unset($res);
            unset($v['bank_card_id']);
        }
        $arr = array('金额','分期数','申请时间','订单类型','订单状态','姓名','身份证号码','电话号码','学校','办卡状态');
        $title = '订单情况统计表';
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);
    }

    public function statusList(){
        $status = intval($_POST['status']);
        if(empty($status)){
            $this->error('请输入参数');
        }
        $list = D('bank_finance')->where('status='.$status)->field('bank_card_id,money,staging')->findAll();
        if($list){
            foreach($list as &$v){
                $res = D('bank_card')->where('id='.$v['bank_card_id'])->field('realname,ctf_id,mobile,school_id,status')->find();
                $v['realname'] = $res['realname'];
                $v['ctf_id'] = '`'.$res['ctf_id'];
                $v['mobile'] = $res['mobile'];
                $v['school'] = D('school')->getField('title','id='.$res['school_id']);
                if($res['status']==2){
                    $v['card_status'] = '办卡成功';
                }elseif($res['status']==3){
                    $v['card_status'] = '办卡失败';
                }else{
                    $v['card_status'] = '正在处理';
                }

                unset($res);
                unset($v['bank_card_id']);
            }
        }
        $arr = array('金额','分期数','姓名','身份证号码','电话号码','学校','办卡状态');
        $title = '订单情况统计表';
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);

    }

    //8 已放款的订单情况
    public function financeRepayList(){
        $mark = intval($_POST['mark']);
        if(empty($mark)){
            $this->error('请输入订单类型');
        }
        $map['mark'] = $mark;
        $map['status'] = array('in','1,2');
        $list = D('bank_finance')->where($map)->field('id,bank_card_id,money,staging,stime,status')->findAll();
        $answer = array('11','还款中','还款结束');
        if($list){
            foreach($list as &$v){
                $clist = D('bank_card')->where('id='.$v['bank_card_id'])->field('realname,card_no')->find();
                $v['realname'] = $clist['realname'];
                $card = D('CMB','bank')->decrypt($clist['card_no']);
                $v['card_no'] = '`'.$card;
                $v['status'] = $answer[$v['status']];
                if($mark==2){
                    $v['doCount'] = D('bank_finance_detail')->where('status=2 and bank_finance_id='.$v['id'])->count();  //已还款期数
                    $res = D('bank_finance_detail')->where('bank_finance_id='.$v['id'])->field('id,money')->limit(1)->find();
                    $v['repayMoney'] = $res['money'];     //每期应还金额
                }else{
                    $money = D('bank_finance_detail')->getField('surp_money','status!=2 and bank_finance_id='.$v['id']);
                    $v['needRepay'] = $money?$money:0;
                    $v['repayMoney'] = 1000.00-$v['needRepay'];
                }

                unset($v['id']);
                unset($v['bank_card_id']);
            }
        }
        if($mark==1){
            $arr = array('金额','分期数','首次还款时间','订单类型','姓名','卡号','剩余还款金额','已还金额');
        }else{
            $arr = array('金额','分期数','首次还款时间','订单类型','姓名','卡号','已还款期数','每期应还金额');
        }
        $title = '还款订单情况统计表';
        array_unshift($list, $arr);
        service('Excel')->export2($list, $title);
    }

    //康欣导出金农放款数据
    public function jinNongData(){
        $stime = t($_POST['stime']);
        $etime = t($_POST['etime']);
        //$uid = $this->mid;
        if(empty($stime)&&empty($etime)){
            $this->error('请输入查询日期');
        }
        $stime = strtotime($stime);
        $etime = strtotime($etime);
//        if($stime&&$etime){
//            $time1 = strtotime('+30 day',$stime);      //查询开始的扣款开始时间
//            $time2 = strtotime('+30 day',$etime);
//            $map['stime'] = array('egt',$time1.' 00:00:00');
//            $map['stime'] = array('elt',$time2.' 59:59:59');
//        }else{
//            $stime = $_POST['stime']?$stime:$etime;
//            $time = strtotime('+30 day',$stime);
//            $map['stime'] = array('egt',$time.' 00:00:00');
//            $map['stime'] = array('elt',$time.' 59:59:59');
//        }
//        $map['status'] = 1;
        $data = array();
        $list = D('BankFinance','shop')->getJnData($stime,$etime);
//        dump($list);die;
        foreach($list as $v){
            $result = D('bank_card')->where('id='.$v['bank_card_id'])->field('realname,school_id,mobile,ctf_id,address,card_no')->find();
            $arr['custName'] = $result['realname'];
            $arr['paperNo'] = $result['ctf_id'];
            $arr['phoneNo'] = $result['mobile'];
            $arr['school'] = tsGetSchoolName($result['school_id']);
            $arr['custAddr'] = t($result['address']);
            $arr['applAmt'] = $v['money'];
            //number_format   保留两位小数
            $applDt = str_replace('-','',$v['ctime']);   //申请日期
            $arr['applDt'] = substr($applDt,0,8);
            $res = D('bank_finance_detail')->where('bank_finance_id='.$v['id'])->field('id,stime')->order('id DESC')->limit(1)->find();
            $arr['lastDueDt'] = str_replace('-','',$res['stime']);
            $arr['contNoExt'] = D('bank_contract')->getField('contract_id','finance_id='.$v['id']);  //合同号
            $arr['gracePerd'] = 28;
            $arr['repayDays'] = 30;
            $arr['intRate'] = sprintf("%.4f", 0.08);
            $arr['bankCd'] = '0308';
            $arr['account'] = D('CMB','bank')->decrypt($result['card_no']);
            $arr['bankAddr'] = '';
            $data['root'][] = $arr;
        }
        $data['busTyp'] = '0';
//        dump($data);
        $msg = json_encode($data);
        $fileName = date('Ymd').'1';

        $this->fileExit($fileName, $msg);

    }


    //文件导出
    public function fileExit($fileName,$msg){
//        file_put_contents('./data/kanxin/'.$fileName.'.json',$msg);
//        header("Pragma: public");
//        header("Expires: 0");
//        header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
//        header("Content-Type:application/force-download");
//        header("Content-Type:application/octet-stream");
//        header("Content-Type:application/download");
//
//        //多浏览器下兼容中文标题
//        $encoded_filename = urlencode($fileName);
//        $ua = $_SERVER["HTTP_USER_AGENT"];
//        if (preg_match("/MSIE/", $ua)) {
//            header('Content-Disposition: attachment; filename="' . $encoded_filename . '.json"');
//        } else if (preg_match("/Firefox/", $ua)) {
//            header('Content-Disposition: attachment; filename*="utf8\'\'' . $fileName . '.json"');
//        } else {
//            header('Content-Disposition: attachment; filename="' . $fileName . '.json"');
//        }
//
//        header("Content-Transfer-Encoding:binary");

        file_put_contents('./data/'.$fileName.'.json',$msg);
        $key = md5_file('./data/'.$fileName.'.json');
        $data['name'] = $fileName;
        $data['md5_key'] = $key;
        D('bank_mkey')->add($data);
        header("Content-Type:application/force-download");
        header("Content-Disposition:attachment;filename=".$fileName.".json");  //下载
        readfile('./data/'.$fileName.'.json');
    }


    //金农扣款数据导出
    public function jinNongReData(){
        $stime = t($_POST['stime']);
        $etime = t($_POST['etime']);
        //$uid = $this->mid;
        if(empty($stime)&&empty($etime)){
            $this->error('请输入查询日期');
        }
//        $stime = strtotime($stime);
//        $etime = strtotime($etime);

        $data = array();
        $list = D('BankFinance','shop')->getJnReData($stime,$etime);
        if(empty($list)){
            $this->error('暂无数据');
        }
        $date = date('Ymd');
        foreach($list as $v){
            $arr['custName'] = D('bank_card')->getField('realname','id='.$v['bank_card_id']);
            $arr['contNoExt'] = D('bank_contract')->getField('contract_id','finance_id='.$v['bank_finance_id']);
            $arr['setlTyp'] = '3';
            $arr['perdNo'] = D('bank_finance_detail')->where('bank_finance_id='.$v['bank_finance_id'].' and id<='.$v['id'])->count();
            $arr['fee'] = sprintf("%.2f", 0);
            $result = D('bank_finance')->where('id='.$v['bank_finance_id'])->field('money,staging')->find();
            $money = intval($result['money']);
            $setlPrcp = getMouPrice($money,$result['staging']);
            $setlInt = getKxService($money);
            $arr['setlPrcp'] = sprintf("%.2f", $setlPrcp);
            $arr['setlInt'] = sprintf("%.2f", $setlInt);
            $num=sprintf("%07d", $v['id']);
            $arr['setlTxNo'] = $data.$num;
            $data['root'][] = $arr;
        }
        $data['busTyp'] = '1';
        $msg = json_encode($data);
        $fileName = date('Ymd').'2';
//        dump($list);die;
        $this->fileExit($fileName, $msg);

    }



    //修改订单还款状态 金额 时间
//    public function editFreeList(){
////        $realname = t($_POST['name']);
//        $etime = t($_POST['etime']);
//        $res = D('bank_card')->where('id=3443')->field('status,surplus_line')->find();
//        if(empty($res)){
//            $this->error('没有办卡纪录');
//        }
//        if(intval($res['surplus_line'])<=4000){
//            $money = intval($res['surplus_line'])+1000;
//            D('bank_card')->setField('surplus_line', $money, 'id='.$res['id']);
//        }
//        $list = D('bank_finance')->where('id=3474')->field('id,bank_card_id')->find();
//        if(empty($list)){
//            $this->error('没有订单纪录');
//        }
//        $map['bank_finance_id'] = $list['id'];
//        $data['surp_money'] = 0;
//        $data['etime'] = $etime;
//        $data['status'] = 2;
//        $result = D('bank_finance_detail')->where($map)->save($data);
//        $data1['etime'] = $etime;
//        $data1['status'] = 2;
//        $res1 = D('bank_finance')->where('id='.$list['id'])->save($data1);
//        dump($result+$res1);
//        $list1 = D('bank_finance_detail')->where($map)->find();
//        dump($list1);
//        $list2 = D('bank_finance')->where('id='.$list['id'])->find();
//        dump($list2);
//    }

}



