<?php
/**
 * Created by PhpStorm.
 * User: yangjun
 * Date: 2016/3/28
 * Time: 9:25
 * pu金逾期订单
 */
import('home.Action.PubackAction');
class PufinanceOrderStageAction extends PubackAction
{
    protected $PufinanceOrderStage;

    public function _initialize()
    {
        $this->PufinanceOrderStage =  D('PufinanceOrderStage');
    }

    /**
     * 逾期订单列表
     */
    public function stageOrderList()
    {
        //处理搜索条件
        $map = $this->search();
        //逾期条件
        $map['status'] = 0;
        //逾期时间=本期结束时间+七天宽限期，则本期结束时间<今天-7天时代表逾期
        //$stime = strtotime(date('Y-m-d',strtotime('-7 day')))-1;
        $etime = strtotime('midnight'); // 今天凌晨0点
        $map['etime'] = array('LT',$etime);
        $list = $this->PufinanceOrderStage->getStageOrderListByPage($map);
        foreach ($list['data'] as &$item) {
            $item['overdue'] = D('PufinanceOrderOverdue')->getOrderOverdueByStageOrder($item);
        }
        //dump($list);
        $this->assign($list);
        $this->display();
    }

    public function showOverdueDetail()
    {
        $stage_id = intval($_GET['stage_id']);
        $stageOrder = D('PufinanceOrderStage')->getById($stage_id);
        $overdueOrder = D('PufinanceOrderOverdue')->getOrderOverdueByStageOrder($stageOrder);
        $this->assign('overdue_order', $overdueOrder);
        $this->display();
    }

    /**
     * 订单搜索条件处理
     * @todo
     */
    protected function search()
    {
        //真实姓名
        $realname = get_search_key('PufinanceOrderStage','realname');
        if(!empty($realname))
        {
            $map['u.realname'] = $realname;
            $search['realname'] = $map['u.realname'] ;
        }
        //身份证号
        $ctfid = get_search_key('PufinanceOrderStage','ctfid');
        if(!empty($ctfid))
        {
            $map['u.ctfid'] = $ctfid;
            $search['ctfid'] = $map['u.ctfid'] ;
        }
        unset($realname,$ctfid);
        //view记录查询条件
        $this->assign('serach',$search);
        return $map;
    }

    /**
     *  ajax处理逾期订单结单
     *  @param $ids 分期订单id
     * @param $orderid 主订单id
     * @return 1成功 0失败
     * @todo
     */
    /*public function dealOrder()
    {
        $id = intval($_POST['ids']);
        $orderid = intval($_POST['orderid']);
        $res = $this->PufinanceOrderStage->doSettle($id, $orderid);
        !empty($res)? $data = 1:$data = 0;
        echo json_encode($data);
    }*/

}