<?php
/**
 * Created by PhpStorm.
 * User: wen ji ping
 * Date: 2016/1/15
 * Time: 16:33
 */

class LuckyNewYearAction extends Action{

    //显示抽奖页面
    public function index(){
        $this->display();
    }

    /**
     * 抽奖
     *
     */
    public function lottery(){
        $this->assign('isFirstOpen',$this->_isFirstOpen());
        $this->display();
    }

    public function ajaxLottery(){
        $res = Model('Yuser')->yy($this->mid,1,3,3);
        if ($res['data']){
            if ($res['data']['type'] == 3){
                //摇到人
                $data['type'] = 0;
                $data['id'] = rand(1,27);
            }elseif($res['data']['type'] == 4){
                //摇到后台添加的产品
                if (isset($res['data']['pubi'])){
                    $data['type']       = 1;
                    $data['id']         = $res['data']['pubi']/100;
                }else{
                    $data['type']       = 2;
                    $data['id']         = $res['data']['order_id'];
                }
            }else{
                $data['type'] = 0;
                $data['id'] = rand(1,27);
            }
        }else{
            $data['type'] = 3;
            $data['id'] = 0;
        }
        $data['response'] = $res;
        $this->ajaxReturn($data,'',1);
    }

    /**
     * 判断用户是否是第一次打开抽奖页面
     */
    private function _isFirstOpen(){
        //Mmc('new_year_is_first_'.$this->mid,null);
        $isFirstOpen = Mmc('new_year_is_first_'.$this->mid);
        Mmc('new_year_is_first_'.$this->mid,'100',0,3600*24*30);
        if (empty($isFirstOpen)){
            return 0;
        }else{
            return 1;
        }
    }

    /**
     * 领奖，填写个人信息
     */
    public function award(){
        $data['shipName']       = t($_POST['name']);
        $data['shipMobile']     = t($_POST['phone']);
        $data['shipAddress']    = t($_POST['address']);
        //处理订单信息
        $oid                    = intval($_REQUEST['award']);
        $order = M('order')->where('order_id=' . $oid . ' and uid=' . $this->mid . ' and order_state=0')->find();
        if (!$order) {
            $this->error('订单不存在');
        }
        $res = M('address')->add($data);
        if (!$res) {
            $this->error('添加失败');
        } else {
            $data['order_id'] = $oid;
            $res = M('order_address')->add($data);
            if (!$res) {
                $this->error('保存失败');
            }
            $order_data['order_state']  = 1;
            $order_data['type']         = 4;
            M('order')->where('order_id='.$oid)->save($order_data);
            D('ShopProduct','shop')->addOrderLog($oid, "会员已填写配送地址信息，等待发货", "会员本人"); //添加订单日志
            $this->success('操作成功');
        }
    }


}