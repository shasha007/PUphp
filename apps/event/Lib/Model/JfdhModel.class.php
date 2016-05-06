<?php

/**
 * JfdhModel
 * 兑换历史记录
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class JfdhModel extends Model {

    public function isCodeUesed($code) {
        $map['code'] = $code;
        $map['isGet'] = 0;
        return $this->where($map)->find();
    }

    //获取礼品数量
    public function lpCount($lpid){
        $cache = Mmc('Jf_count_'.$lpid);
        if($cache === false){
            $count = M('jf')->getField('number', 'id='.$lpid);
            $cache = intval($count);
            Mmc('Jf_count_'.$lpid,$cache,0,3600);
        }
        return $cache;
    }
    //暂时减少礼品数量
    public function decLpCount($lpid,$buyCount){
        $lpCount = $this->lpCount($lpid);
        if($lpCount<$buyCount){
            return false;
        }
        $cache = 0-$buyCount;
        Mmc('Jf_count_'.$lpid,$cache,0,3600,true);
        return true;
    }
    //增加礼品数量
    public function incLpCount($lpid,$buyCount){
        $cache = Mmc('Jf_count_'.$lpid);
        if($cache !== false){
            Mmc('Jf_count_'.$lpid,$buyCount,0,3600,true);
        }
    }

    public function doAdd($data) {
        $lpid = $data['jfid'];
        $buyCount = $data['number'];
        if(!$this->decLpCount($lpid,$buyCount)){
            $this->error = '库存不足，请刷新页面更新礼品数量！';
            return false;
        }
        $sumCost = $data['number'] * $data['cost'];
        if(!X('Score')->useScore($data['uid'],$sumCost,$data['sid'])){
            $this->incLpCount($lpid,$buyCount);
            $this->error = '积分不足！';
            return false;
        }
        //更新物品数量
        M('jf')->setDec('number', 'id='.$data['jfid'], $data['number']);
        $data['cTime'] = time();
        $this->add($data);
        return true;
    }

    public function getList($map = '', $field = '*', $order = 'id DESC', $limit = 10) {
        $res = $this->where($map)->field($field)->order($order)->findPage($limit);
        $jfids = getSubByKey($res['data'], 'jfid');
        $map1['id'] = array('in', $jfids);
        $tempJf = M('jf')->where($map1)->findAll();
        //转换成array($id => $jf)的格式
        $jf = array();
        foreach ($tempJf as $v) {
            $jf[$v['id']] = $v;
        }
        unset($tempJf);
        foreach ($res['data'] as $k => $v) {
            $res['data'][$k]['jf'] = $jf[$v['jfid']];
        }
        return $res;
    }

    public function linqu($map){
        $obj = $this->where($map)->find();
        if($obj){
            $data['isGet'] = 1;
            $data['uTime'] = time();
            if($this->where($map)->save($data)){
                return true;
            }
        }
        return false;
    }
}

?>