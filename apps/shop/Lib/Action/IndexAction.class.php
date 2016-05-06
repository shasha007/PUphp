<?php

class IndexAction extends Action {

    public function index() {
        
        $map['codeState'] = 1;
        $list = D('ShopProduct')->ygList($map,3);
        $this->assign('yg',$list);
        $win = M('order')->where('type=1')->field('product_id,uid,type')->order('order_id DESC')->limit(5)->findAll();
        $db_prefix = C('DB_PREFIX');
        foreach ($win as $k => $v) {
            if($v['type']==1){
                $product = M('shop_yg')->table("{$db_prefix}shop_yg AS a ")
                        ->join("{$db_prefix}shop_product AS b ON b.id=a.product_id")
                        ->field('a.times,b.name,b.pic')
                        ->where('a.id='.$v['product_id'])->find();
                $win[$k]['name'] = '('.$product['times'].'期)'.$product['name'];
                $win[$k]['link'] = U('shop/Yg/detail',array('id'=>$v['product_id']));
                $win[$k]['ico'] = 'm_r_icon1';
                $win[$k]['pic'] = $product['pic'];
                $win[$k]['price'] = '1.00';
            }
            $user = M('user')->where('uid='.$v['uid'])->field('realname,sid')->find();
            $win[$k]['realname'] = $user['realname'];
            $win[$k]['school'] = tsGetSchoolName($user['sid']);
        }
        $this->assign('win',$win);
        //团购
        $map = array();
        $map['codeState'] = 1;
        $tg = M('shop_tg')->table("{$db_prefix}shop_tg AS a ")
                ->join("{$db_prefix}shop_tgprod AS b ON b.id=a.tgprod_id")
                ->field('a.*,b.name,b.pic')
                ->where($map)->order('a.id DESC')->limit(3)->findAll();
        //$tg = M('shop_tg')->where($map)->limit(3)->order('id desc')->findAll();
        $this->assign('tg',$tg);
        $shopYd = false;
        if(!isset($_SESSION['zusatz']['shopYd'])){
            $_SESSION['zusatz']['shopYd'] = true;
            if($this->mid){
                $dao = M('shop_yd');
                $has = $dao->where('uid='.$this->mid)->find();
                if(!$has){
                    $data = array('uid'=>$this->mid);
                    $dao->add($data);
                    $shopYd = true;
                }
            }else{
                $shopYd = true;
            }
        }
        $res = D('user')->where('uid='.$this->mid)->field('uid,sid')->find();
        $sid = $res['sid'];
        $this->assign('sid',$sid);
        $this->assign('showYd',$shopYd);
        $this->display();
    }

}
