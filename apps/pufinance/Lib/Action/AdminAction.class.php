<?php
/**
 * PU金融后台管理操作
 */
import('home.Action.PubackAction');

class AdminAction extends PubackAction
{

    //用户列表
    public function index()
    {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_searchUser'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_searchUser']);
        } else {
            unset($_SESSION['admin_searchUser']);
        }

       /* //用户id
        if(!empty($_POST['uid'])){
            $map['puser.uid'] = (int)$_POST['uid'];
        }*/

        //姓名
        if(!empty($_POST['realname'])){
            $map['puser.realname'] = array('like','%'.t($_POST['realname']).'%');
        }

        //身份证号
        if(!empty($_POST['ctfid'])){
            $map['puser.ctfid'] = t($_POST['ctfid']);
        }

        /*
        //学号
        if(!empty($_POST['stuNo'])){
            $map['user.email'] = array('like',t($_POST['stuNo']).'@%');
        }

        //诚信值
        $cx_condition = '';
        if((isset($_POST['minCx']) && $_POST['minCx'] === '0') &&  (isset($_POST['maxCx']) && $_POST['maxCx'] === '0')){
            $cx_condition =  'cx.total=0 or cx.attend=0';
        }
        if( (isset($_POST['minCx']) && $_POST['minCx'] === '0') || !empty($_POST['minCx'])){
            $cx_condition = (isset($_POST['minCx']) && $_POST['minCx'] === '0') ? 'cx.total=0 or cx.attend=0' : 'ceil(cx.attend*100/total)>='.(int)$_POST['minCx'];
        }
        if(!empty($_POST['maxCx']) ){
            $cx_condition .= ' or ceil(cx.attend*100/total)<='.(int)$_POST['maxCx'];
        }
        if(!empty($cx_condition)){
            $map['_string'] = $cx_condition;
        }

        //区域
        if(!empty($_POST['province']) && (strtolower($_POST['province']) != 'all')){
          $map['school.provinceId'] = (int)$_POST['province'];
        }
        if(!empty($_POST['city']) && (strtolower($_POST['city']) != 'all')){
          $map['school.cityId'] = (int)$_POST['city'];
        }
*/
        //学校-院系
        if(!empty($_POST['yx']) && (strtolower($_POST['yx']) != 'all')){
          $map['school.Id'] = (int)$_POST['school'];
        }else{
          if(!empty($_POST['school']) && (strtolower($_POST['school']) != 'all')){
              $map['school.Id'] = (int)$_POST['school'];
          }
        }

        /*
        //年级
        if(!empty($_POST['year'])){
          $map['user.year'] = (int)$_POST['year'];
        }*/

        $lists = D('PufinanceUser')->getUsersListByPage($map);
        $userInfos = array();
        foreach ($lists['data'] as &$list) {
            if ($list['recommend_uid']) {
                if (!isset($userInfos[$list['recommend_uid']])) {
                    $userInfos[$list['recommend_uid']] = M('User')->getField('realname', array('uid' => $list['recommend_uid']));
                }
                $list['recommend_realname'] = $userInfos[$list['recommend_uid']];
            }
        }
        $schools = get_schools(0);//所有学校
        //$provinces = get_provinces();//所有省份
        $this->assign('schools_option',option_format($schools,'id','title',$_POST['school']));
        //$this->assign('xys_option',option_format(get_schools($_POST['school']),'id','title',$_POST['yx']));
        //$this->assign('provinces_option',option_format($provinces,'id','title',$_POST['province']));
        //$this->assign('citys_option',option_format(get_citys($_POST['province']),'id','city',$_POST['city']));
        $this->assign($lists);
        $this->display();
    }

    //用户详情
    public function userDetail(){
        $uid = (int)$_GET['uid'];
        $list = D('PufinanceUser')->getUserInfoByUid($uid);
        if ($list['recommend_uid']) {
            $list['recommend_realname'] = M('User')->getField('realname', array('uid' => $list['recommend_uid']));;
        }

        $attach = M('attach')->field('savepath,savename')->where(array('userId' => $uid, 'attach_type' => 'puctfid'))->select();
        $attachs = array();
        foreach ($attach as $item) {
            $attachs[] = PIC_URL.'/data/uploads/'.$item['savepath'].$item['savename'];
        }
        $this->assign('attachs', $attachs);

        $groups = $this->_getGroupByUid($uid);//用户部落信息
        $this->assign($groups);
        $this->assign('list', $list);
        $this->display();
    }

    public function userEdit()
    {
        $uid = intval($_GET['uid']);
        $info = D('PufinanceUser')->getUserByUid($uid);

        $this->assign('info', $info);
        $this->display();
    }

    public function doUserEdit()
    {
        $uid = intval($_POST['uid']);
        $info = D('PufinanceUser')->getUserByUid($uid);
        if (!$info) {
            $this->error('用户不存在');
        }
        $update = array();
        $update['realname'] = t($_POST['realname']);
        $update['ctfid'] = t($_POST['ctfid']);
        $update['position'] = t($_POST['position']);
        $update['ethnic'] = t($_POST['ethnic']);
        $update['imid'] = t($_POST['imid']);
        $update['mobile'] = t($_POST['mobile']);
        $update['email'] = t($_POST['email']);
        $update['address'] = t($_POST['address']);
        $update['remark'] = t($_POST['remark']);
        foreach ($update as $key => $value) {
            if ($update[$key] == $info[$key]) {
                unset($update[$key]);
            }
        }
        $res = D('PufinanceUser')->where(array('uid' => $uid))->save($update);
        if ($res === false) {
            $this->error('用户资料更新失败！');
        }
        $this->success('操作成功');
    }

    //ajax删除user记录
    public function deleteUserByAjax(){
        if (empty($_POST['id'])) {
            echo 0;
            exit;
        }
        $map['id'] = array('in', t($_POST['id']));
        $detele = M('pufinance_user')->where($map)->delete();
        echo !empty($detele) ? 1 : 0;
    }

    //ajax 获取城市
    public function getCitys(){
        $id = (int)$_POST['id'];
        $selectId = (int)$_POST['selectId'];
        $citys = get_citys($id);
        $msg = array();
        if(!empty($citys)){
            $msg['code'] = 1;
            $msg['options'] = option_format($citys, 'id', 'city',$selectId);
            echo json_encode($msg);
            exit;
        }
        $msg['code'] = -1;
        echo json_encode($msg);
    }

    //ajax 获取学院列表
    public function getDepartments(){
        $id = (int)$_POST['id'];
        $schools = get_schools($id);
        $msg = array();
        if(!empty($schools)){
            $msg['code'] = 1;
            $msg['options'] = option_format($schools, 'id', 'title');
            echo json_encode($msg);
            exit;
        }
        $msg['code'] = -1;
        echo json_encode($msg);
    }

    //用户钱包列表
    public function wallet(){
        $status = array(
            1 => '白名单',
            2 => '黑名单',
        );
        $map = array();
        if ((int)$_REQUEST['uid']) {
            $map['pc.uid'] = (int)$_REQUEST['uid'];
        }

        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['wallet_searchUser'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['wallet_searchUser']);
        } else {
            unset($_SESSION['wallet_searchUser']);
        }

        //姓名
        if(!empty($_POST['realname'])){
            $map['pu.realname'] = array('like','%'.t($_POST['realname']).'%');
        }

        //身份证号
        if(!empty($_POST['ctfid'])){
            $map['pu.ctfid'] = t($_POST['ctfid']);
        }

        //状态
        if(!empty($_POST['status']) && (strtolower($_POST['status']) != 'all')){
            $map['pc.status'] = (int)$_POST['status'];
        }

        $lists = D('PufinanceCredit')->getPufinanceCreditLists($map);
        if($lists['data']){
            foreach($lists['data'] as $k=>$val){
                $lists['data'][$k]['banks'] = $this->_getBanksInfoByUid($val['uid']);
            }
        }
        $this->assign($lists);
        $this->assign('status',$status);
        $this->display();
    }

    //用户交易记录
    public function record(){
        $types = array(
            'lend' => '借款',
            'repay' => '还款',
            'upcredit' => '提额',
            'downcredit' => '降额',
        );
        $map = array();
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['record_searchUser'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['record_searchUser']);
        } else {
            unset($_SESSION['record_searchUser']);
        }

        //姓名
        if(!empty($_POST['realname'])){
            $map['u.realname'] = array('like','%'.t($_POST['realname']).'%');
        }

        //身份证号
        if(!empty($_POST['ctfid'])){
            $map['u.ctfid'] = t($_POST['ctfid']);
        }

        //类型
        if(!empty($_POST['type']) && ($_POST['type'] != 'all')){
            $map['cl.type'] = array('like', t($_POST['type']).'%');
        }

        $lists = D('PufinanceCreditLog')->getCreditLogListWithUser($map);
        $this->assign($lists);
        $this->assign('types',$types);
        $this->display();
    }

    //修改总金额
    public function changeAllAmount(){
        $uid = (int)$_REQUEST['uid'];
		$credit = D('PufinanceCredit')->getPufinanceCreditInfoByUid($uid);
        if(!empty($_POST)){
            $amount = is_numeric(trim($_POST['amount'])) ? trim($_POST['amount']) : 0;
            if($amount<0){
                $this->error('金额不能小于0！');
                exit;
            }
			M('pufinance_credit')->startTrans();
			$change_amount = bcsub($amount,$credit['all_amount'],2);

            file_put_contents('./1.txt',$change_amount);

			$update = $update_log = false;
			if($change_amount == 0){
				$this->success('操作成功！');
				exit;
			}elseif($change_amount > 0){
				$update_log = D('PufinanceCreditLog')->addCreditLog($uid, 'upcredit_all_sys', $change_amount);
			}elseif($change_amount < 0){
				$update_log = D('PufinanceCreditLog')->addCreditLog($uid, 'downcredit_all_sys', $change_amount);
			}
            $update = M('PufinanceCredit')->where('uid='.$uid)->save(array('all_amount'=>$amount,'usable_amount'=>($credit['usable_amount']+$change_amount)));
			if(!empty($update_log) && !empty($update)){
				M('pufinance_credit')->commit();
				$this->success('操作成功！');
			}else{
				M('pufinance_credit')->rollback();
				$this->error('操作失败！');
			}
        }else{
            $this->assign($credit);
            $this->display();
        }
    }

    //修改免息金额
    public function changeFreeAmount(){
        $uid = (int)$_REQUEST['uid'];
		$credit = D('PufinanceCredit')->getPufinanceCreditInfoByUid($uid);
        if(!empty($_POST)){
            $amount = is_numeric(trim($_POST['amount'])) ? trim($_POST['amount']) : 0;
            if($amount<0){
                $this->error('金额不能小于0！');
                exit;
            }
			
			M('pufinance_credit')->startTrans();
			$change_amount = bcsub($amount,$credit['free_amount'],2);
			$update = $update_log = false;
			if($change_amount == 0){
				$this->success('操作成功！');
				exit;
			}elseif($change_amount > 0){
				$update_log = D('PufinanceCreditLog')->addCreditLog($uid, 'upcredit_free_sys', $change_amount);
			}elseif($change_amount < 0){
				$update_log = D('PufinanceCreditLog')->addCreditLog($uid, 'downcredit_free_sys', $change_amount);
			}
           $update = M('PufinanceCredit')->where('uid='.$uid)->save(array('free_amount'=>$amount,'free_usable_amount'=>($credit['free_usable_amount'] + $change_amount)));
			if(!empty($update_log) && !empty($update)){
				M('pufinance_credit')->commit();
				$this->success('操作成功！');
			}else{
				M('pufinance_credit')->rollback();
				$this->error('操作失败！');
			}
        }else{
            $this->assign($credit);
            $this->display();
        }
    }

    /**
     * 设置用户黑白名单
     */
    public function setUserStatus()
    {
        $uid = intval($_GET['uid']);
        $this->assign('uid', $uid);

        $this->display();
    }

    /**
     * 设置用户黑白名单
     */
    public function doSetUserStatus()
    {
        $uid = intval($_POST['uid']);
        $status = intval($_POST['status']);
        $res = D('PufinanceCredit')->addWhiteList($uid, $status);
        if ($res === false) {
            $this->error('黑白名单设置失败');
        } else {
            $this->success('ok');
        }
    }

    //修改风控金额
    public function changeFreeRiskAmount(){
        $uid = (int)$_REQUEST['uid'];
        if(!empty($_POST)){
            $amount = is_numeric(trim($_POST['amount'])) ? trim($_POST['amount']) : 0;
            if(empty($amount)){
                $this->error('操作失败！');
                exit;
            }
            $update = M('PufinanceCredit')->where('uid='.$uid)->save(array('free_risk'=>$amount));
            if(!empty($update)){
                $this->success('操作成功！');
            }else{
                $this->error('操作失败！');
            }
        }else{
            $credit = D('PufinanceCredit')->getPufinanceCreditInfoByUid($uid);
            $this->assign($credit);
            $this->display();
        }
    }

    //PU币交易列表
    public function transaction(){
        $types = array(
            1 => '提现',
            2 => '消费',
        );
        $type = (int)$_POST['t'];
        $type = !empty($type) ? $type : 1;
        if (!empty($_POST['t'])) {
            $_SESSION['type'] = serialize($_POST['t']);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $type = unserialize($_SESSION['type']);
        } else {
            unset($_SESSION['type']);
        }

        $this->assign('type',$type);
        $this->assign('types',$types);
        if($type == 1){
            $this->_withdrawLists();
        }elseif($type == 2){
            $this->_consumeLists();
        }
    }

    //pu币提现列表
    private function _withdrawLists(){

        $map = array();
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['withdraw_searchUser'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['withdraw_searchUser']);
        } else {
            unset($_SESSION['withdraw_searchUser']);
        }

        //姓名
        if(!empty($_POST['realname'])){
            $map['pu.realname'] = array('like','%'.t($_POST['realname']).'%');
        }

        //身份证号
        if(!empty($_POST['ctfid'])){
            $map['pu.ctfid'] = t($_POST['ctfid']);
        }

        $lists = D('MoneyWithdraw')->getLists($map,'mw.chk_status asc');
        $this->assign($lists);
        $this->display('withdraw');
    }

    //PU币消费列表
    private function _consumeLists(){
        $map = array();
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['consume_searchUser'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['consume_searchUser']);
        } else {
            unset($_SESSION['consume_searchUser']);
        }

        //姓名
        if(!empty($_POST['realname'])){
            $map['u.realname'] = array('like','%'.t($_POST['realname']).'%');
        }

        //身份证号
        if(!empty($_POST['ctfid'])){
            $map['pu.ctfid'] = t($_POST['ctfid']);
        }

        $lists = D('MoneyOut')->getLists($map);
        $this->assign($lists);
        $this->display('consume');
    }

    //提现通过
    public function doAgree(){
        $id = (int)$_POST['id'];
        $msg = array();
        $record = M('moneyWithdraw')->where("id={$id} and chk_status=0")->find();
        if(empty($record)){
            $msg['code'] = -1;
            $msg['msg'] = '未找到或者已经被删除！';
            echo json_encode($msg);
            exit;
        }else{
			M('money_withdraw')->startTrans();
			$update = $add = false;
			
            $update = M('money_withdraw')->data(array('chk_status'=>1,'chk_time'=>time()))->where("id={$id}")->save();
			$money_out = array(
				'out_uid' => $this->mid,
				'out_money' => $record['money'] * 100,
				'out_title' => '提现',
				'out_ctime' => time(),
			);
			$add = M('money_out')->data($money_out)->add();
            if($update && $add){
				M('money_withdraw')->commit();
                $msg['code'] = 1;
                $msg['msg'] = 'success';
                echo json_encode($msg);
                exit;
            }else{
				M('money_withdraw')->rollback();
                $msg['code'] = -2;
                $msg['msg'] = 'fail';
                echo json_encode($msg);
                exit;
            }
        }
    }

    //提现驳回
    public function doReject(){
        $id = (int)$_POST['id'];
        $msg = array();
        $record = M('moneyWithdraw')->where("id={$id} and chk_status=0")->find();
        if(empty($record)){
            $msg['code'] = -1;
            $msg['msg'] = '未找到或者已经被删除！';
            echo json_encode($msg);
            exit;
        }else{
            M('money_withdraw')->startTrans();
            $user_money = (float)$record['user_money'];//用户的pu币
            $lend_money = (float)$record['lend_money'];//借的pu币
            $use_money_update = $lend_money_update = true;
            if(!empty($user_money)){
                $use_money_update = M('money')->setInc( 'money','uid='.$record['uid'],$record['user_money']*100);
                $use_money_update = !empty($use_money_update) ? true : false;
            }
            if(!empty($lend_money)){
                $lend_money_update = M('pufinance_money')->setInc( 'money','uid='.$record['uid'],$record['lend_money']*100);
                $lend_money_update = !empty($lend_money_update) ? true : false;
            }

            $update = M('money_withdraw')->data(array('chk_status'=>2,'chk_time'=>time()))->where("id={$id}")->save();
            if(!empty($use_money_update) && !empty($lend_money_update) && !empty($update)){
                M('money_withdraw')->commit();
                $msg['code'] = 1;
                $msg['msg'] = 'success';
                echo json_encode($msg);
                exit;
            }else{
                M('money_withdraw')->rollback();
                $msg['code'] = -2;
                $msg['msg'] = 'fail';
                echo json_encode($msg);
                exit;
            }
        }
    }

    //根据用户uid获取所有银行卡信息
    private function _getBanksInfoByUid($uid=0){
        $banks = D('PufinanceBankcard')->getUserAllBankcardListByUid($uid);
        return list_sort_by($banks,'status','desc');
    }

    //用户部落信息
    private function _getGroupByUid($uid=0){
        //用户管理的部落
        $manage_member = D('GroupMember','event')->where("level IN (1,2) AND uid=" . $uid)->field('gid')->findAll();
        $manage_ids = getSubByKey($manage_member, 'gid');
        $map_manage['id'] = array('in', $manage_ids);
        $manage_data = D('EventGroup','event')->field('id,uid,name')->where($map_manage)->order('id desc')->findAll();

        //用户加入的部落
        $join_member = D('GroupMember','event')->where("level=3 AND uid=" . $uid)->field('gid')->findAll();
        $join_ids = getSubByKey($join_member, 'gid');
        $map_join['id'] = array('in', $join_ids);
        $join_data = D('EventGroup','event')->field('id,uid,name')->where($map_join)->order('id desc')->findAll();
        return array(
            'manage' => $manage_data,
            'join' => $join_data
        );
    }

}