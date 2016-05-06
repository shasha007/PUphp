<?php

/*
 * 企业账户 消费管理
 * 
 */
class AccountAction extends Action{
    
    public function index(){
        
    }
    
    /**
     * 消费情况
     */
    public function consumptionc(){
            $map['eid'] = $this->eid;
            $order = 'water_id,';
            if($_POST['watertype']){
                $map['watertype'] = t($_POST['watertype']);
            }
            if($_POST['time']){
                //从小到大
                if($_POST['time'] == 'asc'){
                   $order = 'ctime ASC,';
                //从大到小    
                }else if($_POST['time'] == 'desc'){
                    $order = 'ctime DESC,';
                }
                
            }
            if($_POST['account']){
                //从小到大
                if($_POST['account'] == 'asc'){
                   $order .= 'account ASC,';
                //从大到小    
                }else if($_POST['account'] == 'desc'){
                    $order .= 'account DESC,';
                }
            }
            $order = rtrim($order,',');
            $list = D('JobAccountWater')->accountWaterList($map,$limit=10,$order);

            $this->display('water');
    }
}
?>
