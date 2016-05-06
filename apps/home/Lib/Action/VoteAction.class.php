<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/12/21
 * Time: 13:19
 */

class VoteAction extends Action{

    /**
     * 自动跳转
     */
    public function jump(){
        $redirect = $_REQUEST['url'];
        if (empty($redirect)){
            return;
        }
        $redirect = urldecode($redirect);
        redirect($redirect);
    }

    /**
     * 投票动作
     */
    public function voter(){

        $key = $_POST['key'];
        $id = $_POST['id'];
        //判断用户当前投票次数

        $currentDay = $this->_stintVoter($key,1);
        if (!$currentDay){
            $this->ajaxReturn('','投票失败,您今天已经投过票了哦!',0);
        }

        //记录投票
        $res = $this->addVoter($key,$id);
        if (!$res){
            $this->ajaxReturn('','投票失败,请稍后再试!',0);
        }
        //返回投票结果
        $this->voterPercent();
    }


    /**
     * 获取数字类型的统计结果
     */
    public function voterCount(){
        $key = $_POST['key'];
        $count = $this->_getVoter($key);
        if ($count){
            $this->ajaxReturn($count,'获取统计结果成功',1);
        }else{
            $this->ajaxReturn($count,'获取统计结果失败',0);
        }
    }

    /**
     * 获得百分比类型的统计结果
     */
    public function voterPercent(){
        $key = $_POST['key'];
        $count = $this->_getVoter($key);
        if ($count){
            //计算百分比
            $data = $this->_voterPercent($count);
            $this->ajaxReturn($data,'获取统计结果成功',1);
        }else{
            $this->ajaxReturn('','获取统计结果失败',0);
        }
    }

    /**
     * 限定每人每天最多可以投多少票
     */
    private function _stintVoter($key,$stint=1){
        //判断该用户是否投过票
        $userCount = intval(S($key.'_'.$this->mid));
        if ($userCount >= $stint){
            return false;
        }
        $userCount++;
        //缓存当前用户
        S($key.'_'.$this->mid,$userCount,strtotime(date('Y-m-d',strtotime('+1 day')))-time());
        return $userCount;
    }

    /**
     * 保存投票结果
     * @param $key              统计关键字,有前端指定
     * @param String $id        统计字段ID,有前端指定
     * @param Int $stint        限定没人投票次数
     * @return int
     */
    private function addVoter($key,$id='id',$stint=1){

        $map['meta_key'] = $key;
        $res = M('General')->where($map)->find();
        if ($res){
            $meta_value = json_decode($res['meta_value'],true);
            $count = intval($meta_value[$id]);
        }else{
            $count = 0;
        }

        $count++;
        $meta_value[$id] = $count;

        //保存投票次数
        $data['meta_value'] = json_encode($meta_value);
        if ($res){
            $rs = M('General')->where($map)->save($data);
        }else{
            $data['meta_key'] = $key;
            $rs = M('General')->add($data);
        }
        //echo M('General')->getLastSql();
        if ($rs){
            return $count;
        }else{
            return false;
        }
    }

    /**
     * 获取投票结果
     */
    private function _getVoter($key){
        $map['meta_key'] = $key;
        $res = M('General')->where($map)->find();
        if ($res){
            return json_decode($res['meta_value'],true);
        }
        return false;
    }

    /**
     * 计算所有结果的百分比
     * @param array $array      需要被统计的数组
     * @return array|bool
     */
    private function _voterPercent($array=array()){

        if (empty($array)){
            return false;
        }

        foreach($array as $key=> $item){
            if ($item > 0){
                $percent = round($item / (array_sum($array))*100);
            }else{
                $percent = 0;
            }
            $res[$key] = $percent.'%';
        }
        return $res;
    }

}