<?php

/**
 * Description of TrafficAdminAction
 * 流量管理后台
 * @author zhuhaibing06
 */
import('home.Action.PubackAction');
class TrafficAdminAction extends PubackAction
{
    //流量管理后台首页
    public function index()
    {
        $this->display();
    }
    
    //流量列表
    public function trafficLists()
    {
        $map = array();
        $operators = M('traffic_operator')->where('isDel=0')->field('id,name')->select();
        if (!empty($_POST)) {
            $_SESSION['traffic_lists_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['traffic_lists_search']);
        } else {
            unset($_SESSION['traffic_lists_search']);
        }
        if($_POST['opid'] && $_POST['opid'] != 'all'){
            $map['tl.traffic_operator_id'] = (int)$_POST['opid'];
        }
        $this->assign('opid', $_POST['opid']);
        $lists = D('TrafficLists')->getLists($map,'tl.id desc');
        $this->assign($lists);
        $this->assign('ops',$operators);
        $this->display();
    }
    
    //增加流量
    public function addTraffic()
    {
        if(!empty($_POST['__hash__']) && ($_POST['__hash__'] == $_SESSION['__hash__'])){
            $fields = array(
                'name' => '运营商名称',
                'flow_value' => '流量值',
                'quoted_price' => '报价',
                'zero_sale_price' => '建议最低零售价'
            );
            unset($_POST['__hash__']);
            foreach($fields as $k=>$val){
                if(empty($_POST[$k])){
                    $this->error($val.'不能为空！');
                }
            }
            if($_POST['face_value'] < 0){
                $this->error('面值不能小于0');
            }
            if($_POST['discount'] <0 || $_POST['discount'] >10){
                $this->error('折扣在0-10之间（包括0和10）');
            }
            $_POST['flow_value'] = (float)$_POST['flow_value'].$_POST['unit'];
            unset($_POST['unit']);
            $_POST['face_value'] = (float)$_POST['face_value'];
            $_POST['quoted_price'] = (float)$_POST['quoted_price'];
            $_POST['zero_sale_price'] = (float) $_POST['zero_sale_price'];
            $_POST['price'] = (float) $_POST['price'];
            $_POST['discount'] = $_POST['discount'] * 10;
            $_POST['traffic_operator_id'] = $_POST['name'];
            unset($_POST['name']);
            $_POST['is_show'] = $_POST['is_show'] - 1;

            $add = M('traffic_lists')->add($_POST);
            if($add){
                $this->success('新增成功！');
            }else{
                $this->error('新增失败!');
            }
        }else{
            $operators = M('traffic_operator')->where('isDel=0')->field('id,name')->select();
            $this->assign('ops',$operators);
            $this->display();
        }

    }
    
    //运营商列表
    public function operator()
    {
        $lists = M('traffic_operator')->findPage(10);
        $this->assign($lists);
        $this->display();
    }
    
    //增加运营商
    public function addOperator()
    {
        $this->display();
    }
    
    //增加运营商数据处理
    public function doAddOperator()
    {
        $data = $_POST;
        $hash = $_POST['__hash__'];
        unset($data['__hash__']);
        $data['cTime'] = time();
        if($hash === $_SESSION['__hash__'])
        {
            $map['name'] = $data['name'];
            $check = M('traffic_operator')->where($map)->find();
            if(!empty($check))
            {
                $this->error('运营商已经存在，请勿重复添加');
            }
            $flag = M('traffic_operator')->add($data);
            if(!$flag)
            {
                $this->error('操作失败');
            }
            $this->redirect('pufinance/TrafficAdmin/operator');
        }
        $this->error('表单已过期');
    }

    //更改面值
    public  function changePrice(){
        $id = (int)$_GET['id'];
        $item = M('traffic_lists')->where('id='.$id)->find();
        if(!empty($_POST['__hash__']) && ($_POST['__hash__'] == $_SESSION['__hash__'])){
            $amount = (float)$_POST['amount'];
            if($amount < 0){
                $this->error('面值不能为小于零的值！');
            }
            if($amount == $item['price']){
                $this->success('面值更新成功！');
            }
            $update = M('traffic_lists')->where('id='.$id)->data(array('price'=>$amount))->save();
            if(empty($update)){
                $this->error('面值更新失败！');
            }else{
                $this->success('面值更新成功！');
            }
        }else{
            $this->assign('price',$item['price']);
            $this->display();
        }
    }

    //更改使用状态
    public function changeStatus(){
        $id = (int)$_POST['id'];
        $act = $_POST['type'];
        $status = 0;
        if($act == 'open'){
            $status = 1;
        }
        $update = M('traffic_lists')->where('id='.$id)->data(array('is_show'=>$status))->save();
        if(!empty($update)){
            echo json_encode(array('code'=>1));
            exit;
        }else{
            echo json_encode(array('code'=>-1));
            exit;
        }
    }

    //流量充值订单
    public function order(){
        $status = array(
            1 => '充值中',
            2 => '充值成功',
        );

        if (!empty($_POST)) {
            $_SESSION['traffic_order_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['traffic_order_search']);
        } else {
            unset($_SESSION['traffic_order_search']);
        }

        $map = array();
        if($_POST['uid'] ){
            $map['tr.uid'] = (int)$_POST['uid'];
        }

        if($_POST['opid'] && $_POST['opid'] != 'all'){
            $map['tr.traffic_operator_id'] = (int)$_POST['opid'];
        }

        if($_POST['mobile']){
            $map['tr.mobile'] = $_POST['mobile'];
        }

        if($_POST['status'] && $_POST['status'] != 'all'){
            $map['tr.status'] = (int)$_POST['status'] - 1;
        }

        $orders = D('TrafficRecords')->getLists($map);
        $operators = M('traffic_operator')->where('isDel=0')->field('id,name')->select();
        $this->assign('ops',$operators);
        $this->assign($orders);
        $this->assign('status', $status);
        $this->display();
    }
    
}
