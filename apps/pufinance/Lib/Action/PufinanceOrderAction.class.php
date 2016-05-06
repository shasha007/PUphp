<?php
/**
 * Created by PhpStorm.
 * User: yangjun
 * pu金后台订单管理
 * Date: 2016/3/22
 * Time: 13:28
 */
import('home.Action.PubackAction');
class PufinanceOrderAction extends PubackAction
{
    protected $PufinanceOrder;

    protected $KangXinUid = 3000639;    // 康欣后台管理UID
    //protected $KangXinUid = 2775311;    // 康欣后台管理UID

    public function _initialize()
    {
        parent::_initialize();
        $this->PufinanceOrder =  D('PufinanceOrder');
        $this->assign('kxuid', $this->KangXinUid);
    }
    /**
     * 后台订单入口
     */
    public function orderList()
    {
        if ($this->mid == $this->KangXinUid) {  // 借用 orderList 操作方法
            $this->kangxin();
        }

        //处理搜索条件或者默认进来时为未审核还是已通过状态
        $map = $this->search();
        //view判断状态栏已风投通过或者待审核
        if (isset($map['o.status'])) {
            $this->assign('status',$map['o.status']);
        }

        $list = $this->PufinanceOrder->getOrderListByPage($map);
        $this->assign($list);
        $this->display();
    }

    private function kangxin()
    {
        $map = array(
            'bc.invest_id' => 1,
            'o.status' => 2,
        );
        $list = $this->PufinanceOrder->getOrderListByPage($map);
        $this->assign($list);
        $this->display('orderList');
        exit;
    }

    /**
     * 订单搜索条件处理
     * @todo
     */
    protected function search()
    {
        //订单号
        $id = get_search_key('PufinaceOrder','id');
        if(!empty($id))
        {
            $map['o.id'] = $id;
            $search['id'] =  $map['o.id'];
        }
        //真实姓名
        $realname = get_search_key('PufinaceOrder','realname');
        if(!empty($realname))
        {
            $map['u.realname'] = $realname;
            $search['realname'] = $map['u.realname'] ;
        }
        //身份证号
        $ctfid = get_search_key('PufinaceOrder','ctfid');
        if(!empty($ctfid))
        {
            $map['u.ctfid'] = $ctfid;
            $search['ctfid'] = $map['u.ctfid'] ;
        }
        // 订单类型
        $type = intval(get_search_key('PufinaceOrder','type'));
        if ($type) {
            $map['o.type'] = $type;
            $search['type'] = $map['o.type'] ;
        }

        // 订单类型
        $sctime = get_search_key('PufinaceOrder','sctime');
        if (!empty($sctime)) {
            $map['o.ctime'] = array('egt', strtotime($sctime . ' 00:00:00'));
            $search['sctime'] = $sctime;
        }

        $ectime = get_search_key('PufinaceOrder','ectime');
        if (!empty($ectime)) {
            $map['o.ctime'] = array('elt', strtotime($ectime . ' 23:59:59'));
            $search['ectime'] = $ectime;
        }

        unset($id,$realname,$ctfid);
        //判断获取已风投通过或者待审核通过的订单(默认进来没有搜索条件时)or订单状态(有搜索条件时)
        if (isset($_REQUEST['status']) && $_REQUEST['status'] !== '') {
            $map['o.status'] = intval($_REQUEST['status']);
            $search['status'] =  $map['o.status'] ;
        }

        //view记录查询条件
        $this->assign('serach',$search);
        return $map;
    }


    /**
     * @param ajax处理订单
     * tag:1风控通过 2驳回 3订单结清 4放款
     * @return 1成功 0失败
     * @todo
     */
    public function dealOrder()
    {
        $id = intval($_POST['ids']);
        $tag = intval($_POST['tag']);
        switch($tag)
        {
            case 1:$res = $this->PufinanceOrder->doWindCon($id); break;
            case 2:$res = $this->PufinanceOrder->doOverRule($id); break;
            //case 3:$res = $this->PufinanceOrder->doSettle($id); break;
            case 4:$res = $this->PufinanceOrder->doLend($id); break;
        }
        !empty($res)? $data = 1:$data = 0;
        echo json_encode($data);
    }

    /**
     * 指定资金方（第三方借贷机构）
     */
    public function setInvest()
    {
        $bank_card_id = intval($_GET['bank_card_id']);
        $invests = D('PufinanceInvestOrg')->getInvestOrgList();
        $this->assign('bank_card_id', $bank_card_id);
        $this->assign('invests', $invests);
        $this->display();
    }

    /**
     * 提交资金方（第三方借贷机构）
     */
    public function doSetInvest()
    {
        $bank_card_id = intval($_POST['bank_card_id']);
        $invest_id = intval($_POST['invest_id']);
        $res = D('PufinanceBankcard')->saveUserBankcard(array('id' => $bank_card_id), array('invest_id' => $invest_id));
        if ($res === false) {
            $this->error('指定资金方失败');
        } else {
            $this->success('ok');
        }

    }

    public function repayOrder()
    {
        $order_id = intval($_GET['order_id']);
        //$a = D('PufinanceOrder')->doRepayOrder(500, 0, 2775311);
        //dump($a);
        $stageOrders = D('PufinanceOrderStage')->getUserOrderStageList(array('order_id' => $order_id));
        //dump($stageOrders);
        $this->assign('order_id', $order_id);
        $this->assign('stage_order', $stageOrders);
        $this->display();
    }

    public function doRepayOrder()
    {
        $order_id = intval($_POST['order_id']);
        $amount = round(floatval($_POST['amount']), 2);
        if (!$order_id) {
            $this->error('订单参数有误');
        }
        if ($amount <= 0) {
            $this->error('还款金额输入有误');
        }
        $res = D('PufinanceOrder')->doRepayOrder($amount, $order_id);
        if ($res['status']) {
            $this->success('ok');
        } else {
            $this->error($res['info']);
        }

    }

    /**
     * 人工订单
     */
    public function customOrder()
    {
        $uid = intval($_GET['uid']);
        if (empty($uid)) {
            $this->error('未指定用户');
        }
        $bankcards = D('PufinanceBankcard')->getUserUsableBankcardListByUid($uid);
        $this->assign('bankcards', $bankcards);
        $banks = D('PufinanceBankOrg')->getBankList();
        $this->assign('banks', $banks);
        $puUser = D('PufinanceUser')->getByUid($uid);
        $this->assign('pu_user', $puUser);
        $mobile = M('User')->getField('mobile', array('uid' => $uid));
        $this->assign('mobile', $mobile);

        $this->display();
    }

    public function doCustomOrder()
    {
        $uid = intval($_POST['uid']);
        if (empty($uid)) {
            $this->error('未指定用户');
        }
        $amount = round(floatval($_POST['amount']), 2); // 四舍五入
        if (bccomp($amount, '0', 2) <= 0) {
            $this->error('请输入金额');
        }
        $stage = intval($_POST['stage']);
        if ($stage <= 0 || $stage > 12) {
            $this->error('请输入分期');
        }
        $bank_card_id = intval($_POST['bank_card_id']);
        $type = intval($_POST['type']);
        $ctime = strtotime($_POST['ctime']);
        $repay_bank_card_id = intval($_POST['repay_bank_card_id']);

        $reson = t($_POST['reson']);
        try {
            M()->startTrans();
            $order = D('PufinanceOrder')->addUserOrder($uid, array(
                'bank_card_id' => $bank_card_id,
                'repay_bank_card_id' => $repay_bank_card_id,
                'amount' => $amount,
                'free_amount' => 0,
                'stage' => $stage,
                'last_stage' => $stage,
                'reson' => $reson,
                'ctime' => $ctime,
            ), 2, $type);
            if ($order === false) {
                throw_exception('主订单写入失败');
            }
            $res = D('PufinanceOrderStage')->createUserOrderStage($order);
            if ($res === false) {
                throw_exception('分期数据写入失败');
            }
            M()->commit();
            $this->success('保存成功');
        } catch (ThinkException $e) {
            M()->rollback();
            $this->error($e->getMessage());
        }


    }
}