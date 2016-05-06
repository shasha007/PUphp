<?php

/*
 * 基金后台管理
 *
 */
import('home.Action.PubackAction');
class AdminAction extends PubackAction {

    public function _initialize() {
        $this->assign('isAdmin', 1);
    }

    //基金申请  一
    public function index() {
        $cid = $_GET['cid'] ? $_GET['cid'] : 'all';
        $inner = '';
        $url = 'index_' . $cid;
        $slist = M('School')->where('pid=0')->field('id,title')->select();
        $this->assign('slist', $slist);
        if ($_POST['school']) {
            $map['af.sid'] = $_POST['school'];
        }
        switch ($cid) {
            case 'all':
                $menu2 = 1;
                if ($_POST['state']) {
                    if ($_POST['state'] == 1) {
                        $map['af.state'] = 0;
                    } elseif ($_POST['state'] == 2) {
                        $map['af.state'] = 1;
                        $map['af.loanState'] = 0;
                    } elseif ($_POST['state'] == 3) {
                        $map['af.state'] = 1;
                        $map['af.loanState'] = 1;
                    }
                } else {
                    $map['af.state'] = array('egt', 0);
                }

                if ($_POST['eventName'] && $_POST['eventName'] != '') {
                    $map['e.eventName'] = array('like', "%{$_POST['eventName']}%");
                }
                break;
            case 'check':
                $menu2 = 2;
                $map['af.state'] = 0;
                break;
            case 'send':
                $menu2 = 3;
                $map['af.state'] = 1;
                $map['af.loanState'] = 0;
                break;
            case 'have':
                $menu2 = 4;
                $map['af.state'] = 1;
                $map['af.loanState'] = 1;
                break;
            case 'reject':
                $menu2 = 5;
                $map['af.state'] = -1;
                $url = 'index_send';
                break;
        }
        $this->assign('menu2', $menu2);
        $data = array();
        $res = D('FundApplyfund')->fundList($map);
        if ($res) {
            foreach ($res['data'] as &$value) {
                if ($value['state'] == 1 && $value['loanState'] == 1) {
                    $value['state'] = 3;
                }
                $checkUid = M('fund_fundlog')->where('fundId = '.$value['id'])->getField('uid');
                $value['checker'] = getUserField($checkUid,'realname');
                //获取活动管理页地址
                $domain = M('School')->getField('domain', 'id='.$value['schoolId']);
                $hostNeedle = get_host_needle();
                $value['schoolUrl'] = 'http://'.$domain.'.'.$hostNeedle.'/index.php?app=event&mod=Author&act=index&id='.$value['eventId'];
            }
            $data = $res;
        }
        $tateArr = D('FundApplyfund')->state();
        $this->assign('state', $tateArr);
        $this->assign('data', $data);
        $inner = $this->fetch($url);
        $this->assign('sou', $inner);
        $this->display();
    }

    //活动管理  二
    public function activitylist() {
        $res = D('FundEvent')->where('isDel=0')->field('eventId,eventName,company,byTime')->order('eventId desc')->findPage(10);
        $this->assign($res);
        $this->assign('menu', 2);
        $this->assign('menu2', 1);
        $this->display();
    }

    //承办列表
    public function activityApply() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_fund_activityApply'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_fund_activityApply']);
        } else {
            unset($_SESSION['admin_fund_activityApply']);
        }
        $_POST['ename'] && $map['eventName'] = array('like', t($_POST['ename']) . '%');
        $_POST['company'] && $map['company'] = array('like', t($_POST['company']) . '%');
        $_POST['sid'] && $map['sid'] = intval($_POST['sid']);
        if (isset($_POST['state']) && in_array($_POST['state'], array(-1, 0, 1))) {
            $map['state'] = $_POST['state'];
        }
        $res = D('FundApplyevent')->activityApply($map);
//        var_dump(D('FundApplyevent')->getLastSql());
        $this->assign($res);
        $beforEdit = $_SERVER['REQUEST_URI'];
        if (!isset($_GET[C('VAR_PAGE')])) {
            $beforEdit .= '&p=1';
        }
        $_SESSION['admin_activityApply_url'] = $beforEdit;
        $this->assign('menu', 2);
        $this->display();
    }

    //某活动、学校承办
    public function activityCheck() {
        $eid = intval($_GET['eid']);
        $sid = intval($_GET['sid']);
        $res = M('FundApplyevent')->where("eventId=$eid and sid=$sid")
                        ->field('id,eventId,sid,uid,gid,cTime,amount,amount2,attachId,state')
                        ->order('id desc')->findPage(10);
        $daoEvent = M('FundEvent');
        $daoGroup = M('Group');
        foreach ($res['data'] as &$v) {
            $v['eventName'] = $daoEvent->getField('eventName', 'eventId=' . $v['eventId']);
            $v['gname'] = $daoGroup->getField('name', 'id=' . $v['gid']);
        }
        $this->assign($res);
        $this->assign('menu', 2);
        $this->display();
    }

    //承办审核详情
    public function activityCheckDetail() {
        $id = intval($_REQUEST['id']);
        $res = M('FundApplyevent')->where("id=$id")->find();
        if (!$res) {
            $this->error('该申请不存在');
        }
        $daoEvent = M('FundEvent');
        $daoGroup = M('Group');
        $res['eventName'] = $daoEvent->getField('eventName', 'eventId=' . $res['eventId']);
        $res['gname'] = $daoGroup->getField('name', 'id=' . $res['gid']);
        $this->assign($res);
        $this->display();
    }

    /* activity_check_throughAjax 承办活动审核 通过 */
    public function activity_check_throughAjax() {
        $id = intval($_POST['id']);
        $amount2 = t($_POST['amount2']);
        $dao = D('FundApplyevent');
        $res = $dao->through($id, $amount2, $this->mid);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }

    /* activity_check_rejectAjax 承办活动审核 驳回 */

    public function activity_check_rejectAjax() {
        $id = intval($_POST['id']);
        $reason = t($_POST['reason']);
        $dao = D('FundApplyevent');
        $res = $dao->reject($id, $reason);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }

    //添加活动
    public function activityAdd() {
        $provs = M('province')->findAll();
        $this->assign('provs', $provs);
        $this->assign('menu', 2);
        $this->assign('menu2', -1);
        $id = intval($_GET['id']);
        if ($id > 0) {
            $map['eventId'] = $id;
            $res = M('FundEvent')->where($map)->find();
            if ($res) {
                $this->assign($res);
                $this->assign('areas',D('FundEventSchool')->editbarSchool($id));
            }
        }
//        
//        $id = intval($_REQUEST['id']);
//        if ($id > 0) {
//            $map['eventId'] = $id;
//            $res = M('FundEvent')->where($map)->find();
//            if (!$res) {
//                $this->error('活动不存在');
//            }
//            $res['sidStr'] = D('FundEventSchool')->getSchoolStr($id);
//            $this->assign($res);
//        }
//        $this->assign('menu', 2);
//        $this->assign('menu2', -1);
        $this->display();
    }
    //待完结
    public function activityEnd() {
        $this->assign('menu2', 3);
        $map['status'] = 1;
        $map['school_audit'] = 3;
        $map['show_in_xyh'] = 2;
        if($_GET['ended']){
            $map['school_audit'] = 5;
            $this->assign('menu2', 4);
        }
        $list = M('Event')->where($map)->field('id,uid,gid,joinCount,is_school_event as sid,endattach')->order('fTime DESC')->findPage(10);
        unset($map);
        if ($list['data']) {
            $daoEvent = M('FundEvent');
            $daoApply = M('FundApplyevent');
            $daoGroup = M('Group');
            foreach ($list['data'] as &$v) {
                $apply = $daoApply->where('eid='.$v['id'])->field('id as applyId,eventId')->find();
                $v['applyId'] = $apply['applyId'];
                $event = $daoEvent->where('eventId='.$apply['eventId'])->field('eventName')->find();
                $v['eventName'] = $event['eventName'];
                $v['gname'] = $daoGroup->getField('name', 'id=' . $v['gid']);
            }
        }
        $this->assign($list);
        $this->assign('menu', 2);
        $this->display();
    }
    //待完结
    public function activityFinish() {
        $id = intval($_GET['id']);
        $event = M('event')->where('id='.$id)->field('id as eid,title,print_img,print_text,joinCount,pay')->find();
        $this->assign($event);
        if($event){
            $map['eventId'] = $id;
            $map['status'] = 2;
            $attCount = M('event_user')->where($map)->count();
            $this->assign('attCount', $attCount);
        }
        $applyId = intval($_GET['applyId']);
        $apply = M('FundApplyevent')->where('id='.$applyId)->field('id as applyId,contact,telephone,qq,alipayAccount,amount,amount2,amount3,finished')->find();
        $this->assign($apply);
        $this->display();
    }
    //完结活动
    public function activity_do_finish(){
        $applyId = intval($_POST['applyId']);
        $amount3 = t($_POST['amount3']);
        $dao = D('FundApplyevent');
        $res = $dao->doFinish($applyId, $amount3);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }

    //基金管理 三
    public function fundlist() {
        $cid = $_GET['cid'] ? $_GET['cid'] : 'all';
        $url = 'fundlist_' . $cid;
        switch ($cid) {
            case 'all':
                $menu2 = 2;
                $res = D('FundSponsor')->sponsorList();
                break;
            case 'wait':
                $menu2 = 3;
                $map['af.state'] = 1;
                $map['af.loanState'] = 0;
                $res = D('FundApplyfund')->fundList($map);
                break;
            case 'have':
                $menu2 = 4;
                $map['af.state'] = 1;
                $map['af.loanState'] = 1;
                $res = D('FundApplyfund')->fundList($map);
                break;
        }
        $this->assign('menu2', $menu2);
        $this->assign($res);
        $inner = $this->fetch($url);
        $this->assign('inner', $inner);
        //基金情况
        $fund = D('FundSponsor')->fundNow();
        $this->assign('fund', $fund);
        $this->display();
    }

    //添加赞助基金
    public function doaddFund() {
        if (empty($_POST['fullname'])) {
            $this->error(L('公司名不能为空'));
        }
        if (empty($_POST['putfund'])) {
            $this->error(L('投放金额不能为空'));
        }
        if (empty($_POST['actualfund'])) {
            $this->error(L('实际到帐不能为空'));
        }
        if (empty($_POST['puttime'])) {
            $this->error(L('投放时间不能为空'));
        }
        if (empty($_POST['actualtime'])) {
            $this->error(L('实际到帐时间不能为空'));
        }
        if (empty($_POST['ctime'])) {
            $this->error(L('合作开始时间不能为空'));
        }
        if (empty($_POST['endtime'])) {
            $this->error(L('合作结束时间不能为空'));
        }

        if (strtotime($_POST['ctime']) > strtotime($_POST['endtime'])) {
            $this->error(L('合作结束时间不能大于开始时间'));
        }
        $data['company'] = h($_POST['fullname']);
        $data['putFund'] = t($_POST['putfund']);
        $data['actualFund'] = t($_POST['actualfund']);
        $data['putTime'] = strtotime($_POST['puttime']);
        $data['actualTime'] = strtotime($_POST['actualtime']);
        $data['cTime'] = strtotime($_POST['ctime']);
        $data['endTime'] = strtotime($_POST['endtime']);

        if (!empty($_FILES['pic']['name'])) {
            $info = X('Xattach')->upload('fund');
            if ($info['status']) {
                //图片id
                $attachId = $info['info'][0]['id'];
                $data['attachId'] = $attachId;
            }
        }

        $res = M('FundSponsor')->add($data);
        if ($res) {
            $this->success(L('操作成功'));
        } else {
            $this->error(L('操作失败'));
        }
    }

    //添加编辑活动
    public function doaddEvent() {
        $dao = D('FundEvent');
        $res = $dao->doEdit($_POST);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }

    /**
     * fund_detailAjax  返回某条申请信息
     * * */
    public function fund_detailAjax() {
        $id = $_POST['id'];
        $detail = D('FundApplyfund')->oneDetail($id);
        if ($detail) {
            $detail['sendTime'] = friendlyDate($detail['sendTime']);
            echo json_encode($detail);
        } else {
            echo 0;
        }
    }

    /**
     * fund_send_throughAjax 基金发放
     * * */
    public function fund_send_throughAjax() {
        $uid = intval($this->mid);
        $id = $_POST['id'];
        $res = D('FundApplyfund')->send($id,$uid);
        if ($res) {
            //返回现在基金详情
            $fund = D('FundSponsor')->fundNow();
            echo json_encode($fund);
        } else {
            echo 0;
        }
    }

    /**
     * index_throughAjax 基金申请通过
     * * */
    public function index_throughAjax() {
        $id = $_POST['id'];
        $money = $_POST['money'];
        $uid = intval($this->mid);
        $dao = D('FundApplyfund');
        $res = $dao->through($id, $money,$uid);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }

    /**
     * index_rejectAjax 基金申请驳回
     * * */
    public function index_rejectAjax() {
        $id = $_POST['id'];
        $reason = $_POST['reason'];
        $res = D('FundApplyfund')->reject($id, $reason);
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }

    /**
     * activity_deleteAjax 删除活动
     * * */
    public function activity_deleteAjax() {
        $id = $_POST['id'];
        $res = D('FundEvent')->delete($id);
        if ($res) {
            echo 1;
        } else {
            echo 0;
        }
    }

    private function _calcMon($input){
        if(!$input){
            $this->error('请输入月份');
        }
        $res['stime'] = strtotime($input.'-01');
        if(!$res['stime']){
            $this->error('请输入月份');
        }
        $res['mon'] = date('Y-m',$res['stime']);
        $res['etime'] = strtotime('+1 month', $res['stime'])-1;
        return $res;
    }
    // 待审核主办活动导出
    public function excelCheck(){
        set_time_limit(0);
        $fileName = '待审核主办活动';
        $time1 = $_POST['mon1'];
        if(empty($time1)){
            $this->error('请输入时间');
        }
        $time2 = $_POST['mon2'];
        $stime = strtotime($time1);
        $etime = strtotime($time2);
        $sqlCnt = M('')->query("select count(1) as count from ts_fund_applyfund where state=0 and ctime>=$stime and ctime<=$etime");
        $count = $sqlCnt[0]['count'];
        if($count<=0){
              $this->error('搜索结果为空');
        }
        if($count>5000){
              $this->error('结果过多，大于5千条。请缩小时间区间');
        }
        $list = M('')->query("select sid,eventId,uid,position,cTime,eventId as etime,eventId as estate,`range`,eRegistration,eSign,fund,eventId as attend,eventId as news,eventId as pic,eventId as flash,eventId as comment"
                . ",telephone,qq,eventId as payType,alipayAccount,responsibleInfo from ts_fund_applyfund where state=0 and ctime>=$stime and ctime<=$etime order by `id` asc");

        foreach ($list as &$v) {
            $v['sid'] = tsGetSchoolName($v['sid']);
            $v['uid'] = getUserField($v['uid'],'realname');
            $v['cTime'] = date('Y-m-d',$v['cTime']);
            $v['range'] = getRange($v['range']);
            $eid = $v['eventId'];
            $event = M('')->query("select title,sTime,eTime,school_audit from ts_event where id=$eid");
            $v['eventId'] = $event[0]['title'];
            $v['etime'] = date('Y-m-d H:i',$event[0]['sTime']).'至'.date('Y-m-d H:i',$event[0]['eTime']);
            $v['estate'] = '进行中';
            if($event[0]['school_audit']==3){
                $v['estate'] = '完结待审核';
            }elseif($event[0]['school_audit']==4){
                $v['estate'] = '完结被驳回';
            }elseif($event[0]['school_audit']==5){
                $v['estate'] = '已完结';
            }
            $v['attend'] = M('EventUser')->where("eventId=$eid and status=2")->count();
            $v['news'] = M('EventNews')->where("eventId=$eid and isDel=0")->count();
            $v['pic'] = M('EventImg')->where("eventId=$eid")->count();
            $v['flash'] = M('EventFlash')->where("eventId=$eid and uid=0")->count();
            $v['comment'] = M('Comment')->where("type='event' and appid=$eid")->count();
            $v['payType'] = '支付宝';
       }
       $arr = array('学校名称','活动名称','申请人','申请人职位','申请日期','活动时间','活动状态','活动范围','预计报名','预计签到','申请金额','实际签到','活动新闻','活动图片','活动视频','评论数'
           ,'电话','qq','账户类型','帐号','部落外联负责人姓名及电话');
       array_unshift($list, $arr);
       service('Excel')->export2($list,$fileName);
    }


    /**
     * isShowFund 0 不显示扑基金申请页面，1 显示扑基金申请页面
     */
    public function config()
    {
        $area = M('fund_black')->group('areaId')->field('areaId')->findAll();
        if (!empty($area)) {
            $dao = M('school');
            foreach ($area as $k => $v) {
                $area[$k]['school'] = $dao->where('cityId=' . $v['areaId'])->field('id,title')->findAll();
            }
            $this->assign('area', $area);
            $res = M('fund_black')->field('sid')->findAll();
            $sids = getSubByKey($res, 'sid');
            $this->assign('sids', $sids);
        }
        $citys = M('citys')->findAll();
        $this->assign('citys', $citys);
        $this->display();
    }

    public function doConfig(){
        M()->query($sql = 'TRUNCATE table `ts_fund_black`');
        $cityIds = M('citys')->field('id')->findAll();
        $daoadLine = M('fund_black');
        $schoolId = array();
        $sids = '';
        foreach ($cityIds as $v)
        {
            $str = 'schools' . $v['id'];
            if (isset($_POST[$str]))
            {
                if ($sids)
                {
                    $sids = $sids . ",";
                }
                $schoolId[$v['id']] = $_POST[$str];
                $sids .= implode(',', $schoolId[$v['id']]);
            }
        }
        foreach ($schoolId as $key => $v)
        {
            foreach ($v as $value)
            {
                $data['areaId'] = $key;
                $data['sid'] = $value;
                $daoadLine->add($data);
                Mmc('extend_getFundBlack_'.$data['sid'],null);
            }
        }
        $this->success('修改成功');
    }

    public function school() {
        $cityId = intval($_REQUEST['cityId']);
        if (!$cityId) {
            exit(json_encode(array()));
        }
        $schools = M('school')->where('cityId=' . $cityId)->field('id,title')->order('display_order asc')->findAll();
        if ($schools) {
            exit(json_encode($schools));
        }
        exit(json_encode(array()));
    }

}

?>
