<?php

/**
 * Description of TrafficAction
 * 流量充值控制器
 * @author zhuhaibing06
 */

class TrafficAction extends BaseAction
{
    private $ignore_province = array(
        1 => array(),//移动
        2 => array(),//电信
        3 => array(//联通
            '广西','江西','云南','天津','北京','黑龙江','河北','辽宁','四川','吉林','安徽','内蒙古','海南',
        ),
);
    //流量充值首页
    public function index()
    {
        $this->display();
    }
    
    //流量充值详情页
    public function detail()
    {
        $id = (int)$_GET['id'];
        $traffic = M('traffic_lists')->field('flow_value,price')->where('is_show=1 and id='.$id)->find();
        if(empty($traffic) || ($_SESSION['recharge_step_1'] != 1)){
            header('location:'.U('pufinance/Traffic/index'));
        }
        $this->assign('opt',$_SESSION['recharge_operator']);
        $this->assign('traffic', $traffic);
        $this->display();
    }
    
    //流量订单列表
    public function order()
    {
        
    }
    
    //流量订单详情
    public function orderDetail()
    {
        
    }

    //判断手机号的运营商
    public function checkTel(){
        $tel = $_POST['tel'];
        if(!isValidMobile(t($tel))){
            $msg['code'] = -1;
            $msg['msg'] = '手机号格式不正确！';
            echo json_encode($msg);
            exit;
        }
        $mobileInfo = service('Traffic')->getMobileInfo($tel);
        if($mobileInfo['status'] == 1){
			if(!in_array($mobileInfo['data']['operator'],array('联通','移动','电信'))){
				$msg['msg'] = '目前运营商只支持移动、联通和电信';
				$msg['code'] = -5;
				echo json_encode($msg);
				exit;
			}

            $date = explode('-',date('Y-m',time()));
            $begin_time = strtotime($date[0].'-'.$date[1]);//当月的开始时间
            $end_time = strtotime($date[0].'-'.($date[1]+1));//当月的结束时间

            //判断充值次数是否用户
                //移动、电信用户当月可以充值10次
                //联通用户当月、同一规格的流量包充值5次
            $ignore_ids = array();//要忽略的流量包id集合
            if($mobileInfo['data']['operator'] == '联通'){
                if(in_array($mobileInfo['data']['area'],$this->ignore_province[3])){
                    $msg['code'] = -4;
                    $msg['msg'] = '联通用户暂不支持广西、江西、云南、天津、北京、黑龙江、河北、辽宁、四川、吉林、安徽、内蒙古、海南这些地方订购流量包！';
                    echo json_encode($msg);
                    exit;
                }else{
                    $count = M('traffic_lists')->where('traffic_operator_id=3 and is_show=1')->count();
                    $count_data = M('traffic_records')->field('traffic_lists_id,count(id) as count')->where("mobile='{$tel}'")->group('traffic_lists_id')->having('count>=5')->select();
                    $ignore_ids = !empty($count_data) ? array_column($count_data, 'traffic_lists_id') : array();
                    if(count($ignore_ids) >= $count){
                        $msg['code'] = -3;
                        $msg['msg'] = '该手机用户本月所有规格流量包订购已超出5次';
                        echo json_encode($msg);
                        exit;
                    }
                }

            }else{
               $count = M('traffic_records')->where("mobile='{$tel}' and ctime>={$begin_time} and ctime < {$end_time}")->count();
                if($count >= 10){
                    $msg['code'] = -3;
                    $msg['msg'] = '该手机用户本月重复订购已超出10次！';
                    echo json_encode($msg);
                    exit;
                }
            }

            $_SESSION['recharge_mobile'] = $tel;
            $_SESSION['recharge_step_1'] = 1;
            $_SESSION['recharge_operator'] = $mobileInfo['data']['operator'];
            $msg['code'] = 1;
            $msg['msg'] = $mobileInfo['data']['area_operator'];

            //获取各个运营商流量包套餐
            $lists = $this->_getTraffic($mobileInfo['data']['operator'],$ignore_ids);
            //$msg['html'] = $this->_formatHtml($lists);
            $msg['lists'] = $lists;

            echo json_encode($msg);
            exit;
        }else{
            $msg['msg'] = '数据获取有误！';
            $msg['code'] = -2;
            echo json_encode($msg);
            exit;
        }
    }

    //获取各个运营商流量包套餐
    private function _getTraffic($name,$ignore_ids=array()){
        $traffic_enable = array();
        $operator_data = M('traffic_operator')->field('id')->where("name='{$name}'")->find();
        if(empty($operator_data)){
            return false;
        }
        $traffic_enable = M('traffic_lists')->field('id,flow_value,face_value,price')->where('is_show=1 and traffic_operator_id='.$operator_data['id'])->order('face_value asc')->select();
        if(!empty($ignore_ids) && is_array($ignore_ids) && !empty($traffic_enable)){
            foreach($traffic_enable as $k=>$v){
                if(in_array($v['id'], $ignore_ids)){
                    unset($traffic_enable[$k]);
                }
            }
            $traffic_enable = array_values($traffic_enable);
        }
        return  $traffic_enable;
    }

    //拼接返回流量包列表
    private function _formatHtml($data){
        if(empty($data)){
            return false;
        }
        $str = '';
        foreach($data as $item){
            $str .= "<li><a href=".U('pufinance/Traffic/detail',array('id'=>$item['id'])).">{$item['flow_value']}&emsp;/ <span>{$item['price']}元<i>{$item['face_value']}元</i></span><div class=\"arrow-up\"></div></a> </li>";
        }
        return $str;
    }

}
