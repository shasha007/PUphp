<?php

/*
 * 精英贷数据
 */
class EliteloanDataModel extends Model{
    //添加数据
    public function addData($data){
        $rules = array('name'=>'姓名','phone'=>'手机号码','email'=>'邮箱');
        foreach($rules as $k=>$v){
            if($data[$k] == ''){
                $this->error = $v.'不能为空';
                return false;
            }
        }
       $data['cTime'] = time();
       $res = $this->add($data);
       if($res){
           return true;
       }else{
           $this->error = '系统错误';
           return false;
       }
    }
    
    //数据列表
    public function dataList(){
        $res = $this->field('name,phone,email,ctime')->order('id DESC')->findPage(10);
    }
}
?>
