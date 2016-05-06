<?php
class PufinanceAmountModel extends Model
{
    //分页显示用户信息
    public function getAmountListsByPage($map=array(), $order="id desc"){

       return  M('pufinance_amount')->field('id,uid,amount,ctime,etime,receive_time,type,is_receive')
            ->where($map)
            ->order($order)
            ->findPage(15);

    }
	
	//通过Uid获取数据
	public function getAmountLists($map=array(), $order="id desc", $limit="0,10"){
		return M('pufinance_amount')->where($map)->order($order)->limit($limit)->select();
	}
}