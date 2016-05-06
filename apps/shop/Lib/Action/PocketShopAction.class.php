<?php

class PocketShopAction extends Action {

    public function _initialize() {
        $this->assign('isAdmin', '1');
        $this->assign('mobileJumpBox', 1);
    }
//获取首页的数据
    public function index() {
//        $this->__judge();
        $this->assign('isPmUser',isPmUser($this->mid));
        if(!empty($_POST['name'])){
            $map['name'] = array('like', '%' . t($_POST['name']) . '%');
            $this->assign('name',$_POST['name']);
        }
        if($_POST['isDel']==1){
            $map['isDel'] = 1;
        }else{
            $map['isDel'] = 0;
        }

        $dao = D('pocket_goods');
        $daocate = D('pocket_goods_opt');
        $list = $dao->where($map)->field('id,pic,name,price,num,lowest,market,isHot,stock')->order('ordernum DESC')->limit(8)->findAll();
        //dump($list);die;
        foreach($list as &$val){
            $res = $daocate->where('gid='.$val['id'])->find();
            $val['desc'] = $res['desc'];
        }
        $map1['isDel'] = 0;
        $map1['isShow'] = 1;
        $logoList = D('pocket_logo')->where($map1)->order('ordernum DESC')->limit(4)->findAll();
        $this->assign('logoList',$logoList);
        $this->assign('list',$list);
        $this->display();
    }

    //热卖商品列表
    public function hotPocket(){
        $map['isDel'] = 0;
        $map['isHot'] = 1;
        $map['isPu'] = 0;
        $list = D('pocket_goods')->where($map)->field('id,pic,name,price,num,lowest,market,isHot,stock')->order('ordernum DESC')->limit(20)->findAll();
        $daocate = D('pocket_goods_opt');
        foreach($list as &$val){
            $res = $daocate->where('gid='.$val['id'])->find();

            $val['desc'] = $res['desc'];
        }
        $this->assign('list',$list);
        $this->display();
    }

    //分类商品
    public function getAllCate() {
        $daocate = M('pocket_category');
        $map['pid'] = 0;
        $map['isDel'] = 0;
        $list = $daocate->where($map)->select();
        $this->assign('list',$list);
        $this->display();
    }

    //分类商品列表
    public function sortList(){
        $map['cid'] = intval($_GET['cid']);
        $this->assign('cid',$map['cid']);
        if(!$map['cid']){
            $this->error('请选择分类');
        }
        if(!empty($_POST['name'])){
            $map['name'] = array('like', '%' . t($_POST['name']) . '%');
            $this->assign('name',$_POST['name']);
        }
        $daocate = M('pocket_category');
        $result = $daocate->where('id='.$map['cid'])->find();
        $this->assign('cname',$result['name']);
        $map['isDel'] = 0;
        $dao = D('pocket_goods');
        $daocate = D('pocket_goods_opt');
        $list = $dao->where($map)->field('id,pic,name,price,num,lowest,market')->order('ordernum DESC')->limit(8)->findAll();

        foreach($list as &$val){
            $res = $daocate->where('gid='.$val['id'])->find();
            $val['desc'] = $res['desc'];
        }
        $this->assign('list',$list);
        $this->display();
    }

    //商品详情页
    public function detail() {
        $id = intval($_GET['id']);
        if ($id) {
            $map['id'] = $id;
            $obj = M('pocket_goods')->where($map)->find();

            if (!$obj) {
                $this->error('该商品不存在');
            }else{
                $res = D('pocket_category')->where('id = '.$obj['cid'])->find();
                $obj['cid'] = $res['name'];
            }
            $list = D('pocket_profit')->where('id='.$obj['profitId'])->find();
            $arr = unserialize($list['interest']);
            foreach($arr as $key=>$val){
                $arr[$key] = $key;

            }
            $this->assign('arr',$arr);
            $opt = M('pocket_goods_opt')->where('gid='.$id)->find();
            //dump($opt);die;
            $opt['imgs'] = unserialize($opt['imgs']);
            $opt['color'] = unserialize($opt['color']);
            if(!$opt['color'][0]){
                $opt['color'] = 0;
            }
            //dump($opt['color']);die;
            $opt['content'] = htmlspecialchars_decode($opt['content']);
//            $opt['content']=strip_tags($opt['content']);
//            $opt['content']=preg_replace('/&nbsp;/','',$opt['content']);
            //dump($obj);die;
            $this->assign('opt',$opt);
            $this->assign('obj',$obj);
            $this->display();
        }else{
            $this->error('错误操作');
        }
    }
    // ajax获取商品图文详情
    public function ajaxContent(){
        $gid = intval($_GET['gid']);
        if(empty($gid)){
            echo '';
        }
        $opt = M('pocket_goods_opt')->where('gid='.$gid)->field('content')->find();
        $content = htmlspecialchars_decode($opt['content']);
        echo $content;
    }


    //详情信息页面
    public function moreDetail(){
        $gid = intval($_GET['gid']);
        if(empty($gid)){
            $this->error('没有选择商品');
        }
        $opt = M('pocket_goods_opt')->where('gid='.$gid)->field('id,gid,content')->find();
        $opt['content'] = htmlspecialchars_decode($opt['content']);
        $this->assign('opt',$opt);
        $this->display();
    }

    //添加收货信息
    public function addAddress(){
        $uid = $this->mid;
        $cityId = getCityByUid($this->mid);
        if($cityId != 1){ // 苏州
            redirect(U('shop/PocketShop/index'));
        }
        $gid = $_POST['id'];
        if(empty($gid)){
            $gid = $_GET['gid'];
            if(empty($gid)){
                $this->error('错误操作');
            }
        }
        $list = D('pocket_goods')->where('id='.$gid)->field('id,price,lowestShoufu,isPu')->find();
        if($list['isPu']==1){
            $result = D('bank_card')->where('status=2 and uid='.$uid)->find();
            if(!$result){
                //不能购买
                $this->display('error');
                die;
            }
        }
        $arr = D('pocket_goods_opt')->where('gid='.$gid)->field('id,color')->find();
        $mycolor = unserialize($arr['color']);
        //dump($mycolor);die;
        if($mycolor[0]){
            if(empty($_POST['color'])){
                $this->error('请选择商品颜色');
            }
        }
        $color = $_POST['color'];
        $shoufu = ($_POST['first']>0)?$_POST['first']:0;
        if($shoufu<$list['lowestShoufu']){
            $this->error('输入首付低于要求最低首付');
        }
        $preg = "/\d{1,10}(\.\d{1,2})?$/ ";
        if(!preg_match($preg,$shoufu)||($shoufu<0)||($shoufu>$list['price'])){
            $this->error('请正确输入首付金额');
        }
        $staging = $_POST['staging'];
        if(empty($staging)&&($shoufu<$list['price'])){
            $this->error('请选择分期数目');
        }
        $this->assign('gid',$gid);
        $this->assign('color',$color);
        $this->assign('shoufu',$shoufu);
        $this->assign('staging',$staging);
        $this->assign('add',D('PocketAddress')->defaultAddress($this->mid));
        $this->display();
    }

    //添加收货信息 PU币支付
    public function addAddress1(){
        $uid = $this->mid;
        $gid = $_GET['gid'];
        $list = D('pocket_goods')->where('id='.$gid)->field('id,isPu')->find();
        if($list['isPu']==1){
            $result = D('bank_card')->where('status=2 and uid='.$uid)->find();
            if(!$result){
                //不能购买
                $this->display('error');
                die;
            }
        }
        $arr = D('pocket_goods_opt')->where('gid='.$gid)->field('id,color')->find();
        $mycolor = unserialize($arr['color']);
        //dump($mycolor);die;
        if($mycolor[0]){
            if(empty($_GET['color'])){
                $this->error('请选择商品颜色');
            }
        }
        $color = $_GET['color'];
        $this->assign('gid',$gid);
        $this->assign('color',$color);
        $this->assign('add',D('PocketAddress')->defaultAddress($this->mid));
        $this->display();
    }

    //处理添加信息 PU币
    public function doAddress1(){
        $newId = D('PocketAddress')->saveAddress($this->mid);
        //echo $res;die;
        if($newId){
            $this->assign('addressId',$newId);
            $this->assign('data',$_POST);
            $gid = $_POST['gid'];
            $color = $_POST['color'];
//            $shoufu = $_POST['shoufu'];
//            $staging = $_POST['staging'];
            $list = D('pocket_goods')->where('id='.$gid)->find();
//            $this->assign('shoufu',$shoufu);
//            $this->assign('staging',$staging);
//            $res = $this->_checkGoodsPrice($gid,$shoufu,$staging);
//            $this->assign('res',$res);
            $this->assign('list',$list);
            $this->assign('color',$color);
            $this->display('addOrder1');
        }else{
            $this->error(D('PocketAddress')->getError());
        }
    }

    //处理添加信息
    public function doAddress(){
        $newId = D('PocketAddress')->saveAddress($this->mid);
        //echo $res;die;
        if($newId){
            $this->assign('addressId',$newId);
            $this->assign('data',$_POST);
            $gid = $_POST['gid'];
            $color = $_POST['color'];
            $shoufu = $_POST['shoufu'];
            $staging = $_POST['staging'];
            $list = D('pocket_goods')->where('id='.$gid)->find();
            $this->assign('gid',$gid);
            $this->assign('shoufu',$shoufu);
            $this->assign('staging',$staging);
            $res = $this->_checkGoodsPrice($gid,$shoufu,$staging);
            $this->assign('res',$res);
            $this->assign('list',$list);
            $this->assign('color',$color);
            $this->display('addOrder');
        }else{
            $this->error(D('PocketAddress')->getError());
        }
    }

    //提交订单
    public function addOrder(){
        //echo $_GET['addressId'];die;
        $data['uid'] = $this->mid;
        $data['gid'] = intval($_GET['gid']);
        $data['color'] = t($_GET['color']);
        $data['shoufu'] = $_GET['shoufu'];
        $data['staging'] = intval($_GET['staging']);
        $preg = "/\d{1,10}(\.\d{1,2})?$/ ";
        if(!preg_match($preg,$data['shoufu'])||($data['shoufu']<0)){
            $this->error('请正确输入首付金额');
        }
        $list = $this->_checkGoodsPrice($data['gid'],$data['shoufu'],$data['staging']);
        $data['stagPrice'] = $list['all'];
        if(empty($data['gid'])){
            $this->error('没有选择商品');
        }
        $data['addressId'] = intval($_GET['addressId']);
        if(empty($data['addressId'])){
            $this->error('没有选择收货地址');
        }
        $data['ctime'] = time();
        $data['desc'] = $_POST['desc'];
        $res = D('pocket_order')->add($data);
        if($res){
            $kucun = D('pocket_goods')->getField('stock','id='.$data['gid']);
            if($kucun<=0){
                D('pocket_order')->where('id='.$res)->delete();
                $this->error('库存不足,无法购买');
            }else{
                D('pocket_goods')->setDec('stock', "id=".$data['gid']);
                D('pocket_address')->setField('used', 1, "id=".$data['addressId']);
                $this->display('wait');
            }
//            D('pocket_address')->setField('used', 1, "id=".$data['addressId']);
//            $this->display('wait');
        }else{
            $this->error('申请失败');
        }
    }

    //提交订单 PU币
    public function addOrder1(){
        $data['uid'] = $this->mid;
        $data['gid'] = intval($_GET['gid']);
        $data['color'] = t($_GET['color']);
        if(empty($data['gid'])){
            $this->error('没有选择商品');
        }
        $data['addressId'] = intval($_GET['addressId']);
        if(empty($data['addressId'])){
            $this->error('没有选择收货地址');
        }
        $obj = D('pocket_goods')->where('id='.$data['gid'])->field('name,price,stock')->find();
        if(!$obj){
            $this->error('商品不存在');
        }
        if($obj['stock']<=0){
            $this->assign('jumpUrl', U('shop/PocketShop/detail').'&id='.$data['gid']);
            $this->error('库存不足,无法购买');
        }
        //扣除PU币
        $res = Model('Money')->moneyOut($data['uid'], $obj['price']*100, '分期商城'.$obj['name']);
        if (!$res) {

            $this->assign('jumpUrl', U('shop/PocketShop/detail').'&id='.$data['gid']);
            $this->error('您的账号余额不够，请前往充值！');
        }
        //剪掉库存
        D('pocket_goods')->setDec('stock', "id=".$data['gid']);

        $data['price'] = $obj['price'];
        $data['ctime'] = time();
        //添加订单
        D('pocket_pu_order')->add($data);
        D('pocket_address')->setField('used', 1, "id=".$data['addressId']);
        $this->display('wait1');
        }


    //ajax
    public function ajaxPocket(){
        $m=$_POST['n'];
        $cid=$_POST['id'];
        $mark = $_POST['mark'];
        if($mark==1){
            $map['isHot'] = 1;
        }
        //$name=t($_POST['name']);
        if($cid){
            $map['cid'] = $cid;
        }
        if(!empty($_POST['name'])){
            $map['name'] = array('like', '%' . t($_POST['name']) . '%');
        }
        //$n = $m+2;
        $dao=D('pocket_goods');
        $map['isdel']=0;
        $count=$dao->where($map)->count();
        if($m>=$count){
            echo 0;
        }else{
            $list=$dao->where($map)->field('id,pic,name,price,num,lowest,market,isHot')->order('ordernum DESC')->limit($m.',8')->select();
            $daolist=D('pocket_goods_opt');
            foreach($list as &$v){
                $res = $daolist->where('gid='.$v['id'])->find();
                $v['desc'] = $res['desc'];
                $v['pic'] = tsMakeThumbUp($v["pic"],200,200);
            }
            echo json_encode($list);
        }
    }

    //口袋乐商品列表
    public function stagingList(){

    }

    //选择口袋金金额
    public function addPrice(){
        $map['isDel'] = 0;
        $list = D('pocket_reason')->where($map)->select();
        $staging = getPriceStag();
        $price = getPrice();
        //dump($staging);die;
        $this->assign('stagingList',$staging);
        $this->assign('priceList',$price);
        $this->assign('list',$list);
        $this->display();
    }
    //
    public function doAddPrice(){
        $cityId = getCityByUid($this->mid);
        if($cityId != 1){ // 苏州
            redirect(U('shop/PocketShop/index'));
        }
        $data = $this->_checkPocketPrice();
        $this->assign($data);
        $this->assign('add',D('PocketAddress')->defaultAddress($this->mid));
        $this->display();
    }
    private function _checkPocketPrice(){
        $data['price'] = intval($_POST['price']);
        $data['staging'] = intval($_POST['staging']);
        $data['reasonId'] = t($_POST['reasonId']);
        if($data['price']<=0){
            $this->error('请选择申请金额');
        }
        if($data['staging']<=0){
            $this->error('请选择还款期数');
        }
        if(!$data['reasonId'] || $data['reasonId']=='--请选择--'){
            $this->error('请选择借款原因');
        }
        $data['stagPrice'] = getStagingPrice($data['price'],$data['staging']);
        $data['benPrice'] = getMouPrice($data['price'],$data['staging']);
        $data['servicePrice'] = $data['stagPrice'] - $data['benPrice'];
        return $data;
    }
    public function doPocketPrice(){
        $newId = D('PocketAddress')->saveAddress($this->mid);
        //echo $res;die;
        if($newId){
            $data = $this->_checkPocketPrice();
            $data['addressId'] = $newId;
            $data['uid'] = $this->mid;
            $data['ctime'] = time();
            $res = D('pocket_price')->add($data);
            if($res){
                $this->display('wait');
            }else{
                $this->error('提交申请失败');
            }
        }else{
            $this->error(D('PocketAddress')->getError());
        }
    }

    //个人订单
    public function myList(){
        $map['uid'] = $this->mid;
        $status = intval($_GET['status']);
        if(empty($status)){
            $map['status'] = array('neq',5);
        }else{
            $this->assign('status',$status);
            $map['status'] = $status-1;
        }
        $list = D('pocket_order')->where($map)->order('id DESC')->select(); //商品订单
        $result = D('pocket_price')->where($map)->order('id DESC')->select();  //口袋金订单
        $dao = D('pocket_goods');
        foreach($list as &$val){
            $res = $dao->where('id='.$val['gid'])->field('id,name,price,pic')->find();
            $val['goodsName'] = $res['name'];
            $val['price'] = $res['price'];
            $val['pic'] = $res['pic'];
            $arr = array('0'=>'待审核','1'=>'审核通过','2'=>'审核失败','3'=>'还款中','4'=>'订单结束');
            $val['sta'] = $arr[$val['status']];
        }
        foreach($result as &$val){
            $arr = array('0'=>'待审核','1'=>'等待放款','2'=>'审核失败','3'=>'还款中','4'=>'订单结束');
            $val['sta'] = $arr[$val['status']];
            $val['benPrice'] = getMouPrice($val['price'],$val['staging']);
            $val['service'] = $val['stagPrice']-$val['benPrice'];
        }
        $this->assign('list',$list);
        $this->assign('result',$result);
        $this->display();
    }

    //商品订单详情
    public function orderDetail(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('请选择订单');
        }
        $list = D('pocket_order')->where('id='.$id)->find();
        $goodsList = D('pocket_goods')->where('id='.$list['gid'])->find();
        $addressList = D('pocket_address')->where('id='.$list['addressId'])->find();
        $this->assign('list',$list);
        $this->assign('goodsList',$goodsList);
        $this->assign('addressList',$addressList);
        $this->display();
    }

    //用户删除符合条件的订单
    public function delGoodsOrder(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('请选择订单');
        }
        $uid = $this->mid;
        $list = D('pocket_order')->where('id='.$id)->field('uid,status')->find();
        if(($list['uid']!=$uid)||($list['status']==1)||($list['status']==3)){
            $this->error('无法删除该订单');
        }
        $data['status']=5;
        $result = D('pocket_order')->where('id='.$id)->save($data);
        if($result){
            $this->assign('jumpUrl', U('shop/PocketShop/myList'));
            $this->success('订单删除成功');
        }else{
            $this->error('操作失败');
        }
    }


    //口袋金订单详情
    public function priceOrderDetail(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('请选择订单');
        }
        $list = D('pocket_price')->where('id='.$id)->find();
        $list['benPrice'] = getMouPrice($list['price'],$list['staging']);
        $list['service'] = $list['stagPrice']-$list['benPrice'];
        $addressList = D('pocket_address')->where('id='.$list['addressId'])->find();
        $this->assign('list',$list);
        $this->assign('addressList',$addressList);
        $this->display();
    }

    //删除口袋金订单
    public function delPriceOrder(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('请选择订单');
        }
        $uid = $this->mid;
        $list = D('pocket_price')->where('id='.$id)->field('uid,status')->find();
        if(($list['uid']!=$uid)||($list['status']==1)||($list['status']==3)){
            $this->error('无法删除该订单');
        }
        $data['status']=5;
        $result = D('pocket_price')->where('id='.$id)->save($data);
        if($result){
            $this->assign('jumpUrl', U('shop/PocketShop/myList'));
            $this->success('订单删除成功');
        }else{
            $this->error('操作失败');
        }
    }

    //获取商品每月还款金额
    public function getGoodPrice(){
        $id = intval($_POST['id']);
        $first = intval($_POST['m']);
        $staging = intval($_POST['n']);
        $data = $this->_checkGoodsPrice($id,$first,$staging);
        echo json_encode($data);
    }

    //计算首付 分期服务费
    public function _checkGoodsPrice($gid,$shoufu,$staging){
        $res = D('pocket_goods')->where('id='.$gid)->field('id,price,profitId')->find();
        $data['eveprice'] = round(($res['price']-$shoufu)/$staging,2); //每月还款本金
        $result = D('pocket_profit')->where('id='.$res['profitId'])->find();
        $interest = unserialize($result['interest']);
        $data['service'] =round($data['eveprice'] * $interest[$staging],2); //每月的服务费
        $data['all'] = $data['eveprice'] +$data['service'];
        return $data;
    }

    //新的口袋金
    public function bankPrice(){
        $this->__isJump(1);

        //echo $channel;die;
        $this->__judge();
        $uid = $this->mid;
        //如果是可待大学，首页出现办卡按钮
        $sid = getUserField($uid, 'sid');
//        $city = D('school')->getField('cityId','id='.$sid);
//        $cityName = D('citys')->getField('city','id='.$city);
        $map['uid'] = $uid;
        $map['status'] = array('neq',3);
        $res = D('bank_card')->where($map)->find();

        if($sid!=473){

            if($res){
                $mark = 0;
            }else{
                $mark = 1;
            }
        }else{
            if(!$res){
                $mark = 2;
            }
        }
        $channel = $_GET['channel'];
//        $channel = 'e/0McU3HLMNP+E5zftOkaw==';
        if(empty($channel)){
            $channel = 0;             //渠道
        }
        $staging = array('0'=>1,'1'=>3,'2'=>6,'3'=>9,'4'=>12);
        $price = array('0'=>500, '1'=>1000,'2'=>1500,'3'=>2000,'4'=>2500, '5'=>3000,'6'=>3500,'7'=>4000,'8'=>4500,'9'=>5000);

        $map1['isDel'] = 0;
        $data = D('pocket_reason')->where($map1)->select();
        $this->assign('data',$data);
        $list = D('pocket_goods')->where('isDel=0 and isHot=1')->field('id,name,price,pic,stock')->order('ordernum DESC')->limit(6)->findAll();
        $this->assign('list',$list);
        $this->assign('stagingList',$staging);
        $this->assign('priceList',$price);
        $this->assign('mark',$mark);  //如果mark为1  则页面会出现一千元免息 mark为2 页面出现办卡页面
        $this->assign('channel',$channel);
        $this->display();
    }

    //判断用户是否登陆，如果未登录跳转到登录页面
    private function __isJump($type){

        $uid = $this->mid;
        if(empty($uid)){
            redirect(U('home/Wxlog/login',array('type' => $type)));
        }
    }

//    //判断是否是口袋大学的用户
//    private function __judge(){
//        $uid = $this->mid;
//        $sid = getUserField($uid, 'sid');
////        $city = D('school')->getField('cityId','id='.$sid);
////        $cityName = D('citys')->getField('city','id='.$city);
//        if($sid!=473){
//            redirect(U('shop/PocketShop/addPrice'));
//        }
//    }

    //判断用户是那个区域学校的
    private function __judge(){
        $cityId = getCityByUid($this->mid);
        if($cityId != 1){ // 苏州
            redirect(U('shop/PocketShop/hotPocket'));
        }else{
            redirect(U('shop/Bank/bankPrice'));
        }


    }

    //计算利息服务费
    private function _checkBankPrice(){
        $data['money'] = intval($_POST['money']);
        $data['staging'] = intval($_POST['staging']);
        $data['reason'] = t($_POST['reason']);
        if($data['money']<=0){
            $this->error('请选择申请金额');
        }
        if($data['staging']<=0){
            $this->error('请选择还款期数');
        }
        if(!$data['reason'] || $data['reason']==''){
            $this->error('请填写借款原因');
        }
        $data['stagMoney'] = getStagingPrice1($data['money'],$data['staging']);  //每月还款总额
        $data['benMoney'] = getMouPrice($data['money'],$data['staging']);       //还款每月本金
        if(!($data['stagMoney']&&$data['benMoney'])){
            $this->error('填写数据出错');
        }
        $data['servicePrice'] = $data['stagMoney'] - $data['benMoney'];         //每月服务费
//        $data['servicePrice'] = $data['stagMoney'] - $data['benMoney'];
        return $data;
    }

    //用户信息确认
    public function doMessage(){
        //判断是不是大四毕业生
        $uid = $this->mid;
        $cityId = getCityByUid($this->mid);
        if($cityId != 1){ // 苏州
            redirect(U('shop/PocketShop/index'));
        }
        //接收数据
        $channel = $_POST['channel'];
        $data = $this->_checkBankPrice();
        $map['uid'] = $uid;
        $map['status'] = array('neq',3);
        $res = D('bank_card')->where($map)->field('id,realname,mobile,address,ctf_id,card_no,allow_risk,d_mobile,m_mobile,post_code,email_bill,surplus_line,referrer')->limit(1)->find();
        //现在的
        if($res){
            //判断之前的一千免息是否完成
            $map1['uid'] = $uid;
            $map1['mark'] = 1;
            $result = D('bank_finance')->where($map1)->field('id,status,stime')->find();
            $otime = strtotime($result['stime']);
            $ntime = time();
            if($result&&($result['status']!=2)&&($data['money']!=1000)){
                    $this->error('1000元免息未还款，无法进行该操作');
            }else{
                if($result&&($result['status']!=2)&&($otime>$ntime)){
                    $this->error('未到还款时间，您无法进行该操作');
                }
                //是否在额度范围内
                if($res['surplus_line']>=$data['money']){
                    $list = D('bank_user_info')->where('bank_id='.$res['id'])->find();
                    $str = '****************';
                    $res['ctf_id'] = substr_replace($res['ctf_id'],$str,1,16);
                    $this->assign('list',$list);
                    $this->assign('res',$res);
                }else{
                    $this->error('您的额度不足');
                }
            }
            //已经有卡
            $map2['status'] = 0;
            $map2['bank_card_id'] = $res['id'];
            $money2 = D('bank_finance')->where($map2)->sum('money');  //已经申请的金额
            $money2 = intval($money2);
            $line2 = D('bank_card')->getField('surplus_line','uid='.$uid);
            if(($line2-$money2)<$data['money']){
                $this->error('您的额度不足');
            }
            //老用户产生合同编号
            $contract_id = $this->addOldContract();

        }else{

            //新用户
            $contract_id = $this->addNewContract();

        }
        $arr2['name'] = D('user')->getField('realname','uid='.$uid);
        $arr2['post_code'] = areaPost($cityName);
        //生成一个合同编号
        $this->assign('arr2',$arr2);
        $this->assign('contract_id',$contract_id);

        //提交
        $mark = 2;
        $this->assign('channel',$channel);
        $this->assign('mark',$mark);
        $this->assign('data',$data);
        $this->display();
    }

    //产生新的合同编号
    private function addOldContract(){
        $uid = $this->mid;
        $contract = D('bank_credit')->getField('contract','uid='.$uid);
        if(empty($contract)){
            $this->error('系统无法获取您的授信合同，请重试');
        }
        $newcon = substr($contract,2);  //日期+序号

        $map5['contract_id']  = array('like',$newcon."%");
        $num = D('bank_contract')->where($map5)->count();
        $num = $num + 1;
        if($num<10){
            $arr4['contract_id'] = $newcon.'_0'.$num;
        }else{
            $arr4['contract_id'] = $newcon.'_'.$num;
        }

        $arr4['uid'] = $uid;
        $arr4['ptm'] = '01'.$arr4['contract_id'];
        $arr4['pfm'] = '02'.$arr4['contract_id'];
        $arr4['ctime'] = date('Y-m-d H:i:s');
        $res4 = D('bank_contract')->add($arr4);
        if(!$res4){
            $this->error('平台合同提交失败');
        }
        return $arr4['contract_id'];
    }

    //产生新的合同编号
    private function addNewContract(){
        $uid = $this->mid;
        $nowtime = date('Ymd');
        $nowtime = substr($nowtime,2);
        $list = D('bank_credit')->where('uid='.$uid)->find();
        if($list){
            $res = $this->addOldContract();
            return $res;
        }
            $map5['contract']  = array('like','08'.$nowtime."%");
            $num = D('bank_credit')->where($map5)->count();
            $num = $num+1;
            $contract=sprintf("%04d", $num);

        $arr4['contract_id'] = $nowtime.$contract.'_01';
        $arr4['uid'] = $uid;
        $arr4['ptm'] = '01'.$arr4['contract_id'];
        $arr4['pfm'] = '02'.$arr4['contract_id'];
        $arr4['ctime'] = date('Y-m-d H:i:s');
        //dump($arr4);die;
        $res4 = D('bank_contract')->add($arr4);

        $arr5['contract'] = '08'.$nowtime.$contract;
        $arr5['ctime'] = date('Y-m-d H:i:s');
        $arr5['uid'] = $uid;


        $res5 = D('bank_credit')->add($arr5);
        if(!$res4){
            $this->error('添加合同失败');
        }

        if($res4&&$res5){

            return $arr4['contract_id'];
        }else{
            $this->error('添加授信合同失败');
        }
    }

    //判断用户是否是毕业生
    public function isOneYear(){
        $year = date('Y');  //当前年份
        $uid = $this->mid;
        $sid = getUserField($uid, 'sid1');  //学院ID
        if($sid<=0){
            $sid = getUserField($uid, 'sid');  //学校ID
        }
        $syear = D('user')->getField('year','uid='.$uid);   //入学年份
        $school_year = D('school')->getField('tj_year','id='.$sid);  //学校是几年制的
        $eYear = $syear + $school_year -1;
        $eTime = mktime(0,0,0,6,30,$eYear);     //离毕业一年时的时间戳
        $sTime = time();           //当前时间戳
        if($sTime<$eTime){
            return 1;
        }else{
            return 0;
        }
    }

    //处理数据
    private function _getCardData(){
        $data['realname'] = t($_POST['realname']);
        $data['address'] = t($_POST['address']);
        $data['ctf_id'] = t($_POST['ctf_id']);
        $data['email_bill'] = t($_POST['email_bill']);
        $data['mobile'] = t($_POST['mobile']);
        $data['d_mobile'] = t($_POST['d_mobile']);

        $data['post_code'] = intval($_POST['post_code']);
        $channel = $_POST['channel'];
        if($channel){
            $data['channel'] = D('CMB','bank')->decrypt($channel);
        }
        $data['surplus_line'] = 5000;
        $data['total_line'] = 5000;

        return $data;
    }

    //处理数据
    private function _getUserInfoData(){
        $arr['nation'] = t($_POST['nation']);
        //$arr['education'] = t($_POST['education']);
        $arr['qq'] = t($_POST['qq']);
        $arr['class_address'] = t($_POST['class_address']);
        $arr['d_company'] = t($_POST['d_company']);
        $arr['d_name'] = t($_POST['d_name']);
        $arr['dc_mobile'] = t($_POST['dc_mobile']);
        $arr['classmate'] = t($_POST['classmate']);
        $arr['class_mobile'] = t($_POST['class_mobile']);
        $arr['class_address'] = t($_POST['class_address']);
        $arr['home'] = t($_POST['home']);
        $arr['referrer'] = $_POST['referrer'];

        return $arr;
    }

    //办卡信息填写提交
    public function doEditCard(){

        //前面要判断 是否是毕业生 是否有卡
        $uid = $this->mid;
        if($_POST['mark']==2){
            $data1 = $this->_checkBankPrice();
            if($_POST['id']){
                $line2 = D('bank_card')->getField('surplus_line','id='.$_POST['id']);
                $map2['status'] = 0;
                $map2['bank_card_id'] = $_POST['id'];
                $money2 = D('bank_finance')->where($map2)->sum('money');  //已经申请的金额
                $money2 = intval($money2);
//                $line2 = D('bank_card')->getField('surplus_line','uid='.$uid);
//                if(($line2-$money2)<$data1['money']){
//                    $this->error('您的额度不足');
//                }
            }else{
                $line2 = 5000;
                $map2['status'] = 0;
                $map2['uid'] = $uid;
                $money2 = D('bank_finance')->where($map2)->sum('money');  //已经申请的金额
                $money2 = intval($money2);
            }
            if(($line2-$money2)<$data1['money']){
                    $this->error('您的额度不足');
                }

        }
        //合同编号
        $contract_id = $_POST['contract_id'];
        if($_POST['id']){
            //有卡用户
            $list = D('bank_card')->where('id='.$_POST['id'])->find();
            //dump($list);die;
            if(($list['allow_risk']==1)&&$list['card_no']&&($list['status']==2)){

                $key = 1;
                $res = D('BankFinance','shop')->addFinance($uid,$data1,$key,$_POST['id']);
                if($res){
                        //额度扣成功
                        //调用代发接口
                        $res1 = D('Lend','bank')->DCPAYMNT($res,$uid,$data1['money'],$list['card_no'],$list['card_account']);

                        if(($res1['code']==1)||($res1['code']==3)){
                            //代发成功 在合同表里添加分期ID
                            D('bank_contract')->setField('finance_id',$res,'contract_id='.$contract_id );
                            $this->assign('jumpUrl', U('/PocketShop/bankPrice'));
                            //$this->success('提交成功，等待银行放款');
                            echo '提交成功，等待银行放款';
                        }else{
                            //代发失败
                            D('bank_finance')->where('id='.$res)->delete();
                            D('bank_finance_detail')->where('bank_finance_id='.$res)->delete();
                            //$this->error('操作失败，请重新申请');
                            echo '放款失败，请重新申请';
                        }


                }else{
                    //$this->error('数据写入失败，请重新申请');
                    echo '数据写入失败，请重新申请';
                }
            }else{
                //等待审核

                //echo 111;die;
                $key = 0;
                $res = D('BankFinance')->addFinance($uid,$data1,$key,$_POST['id']);
                if($res){
                    D('bank_contract')->setField('finance_id',$res,'contract_id="'.$contract_id.'"' );
                    $this->assign('jumpUrl', U('/PocketShop/bankPrice'));
                    //$this->success('提交成功，等待审核');
                    echo '提交成功，等待审核';
                }else{
                    //$this->error('数据写入失败，请重新申请');
                    echo '数据写入失败，请重新申请';
                }
            }
        }elseif($_POST['mark']==1){
            //没有卡 一千元免息
            $info = D('bank_card')->where('uid='.$uid.'and status !=3')->find();
            if($info){
                echo '错误操作，您无法进行此操作';
            }
            $data = $this->_getCardData();
            $data['f_mark'] = 1;
            $arr = $this->_getUserInfoData();
            $res = D('BankCard','shop')->addBankCard($data,$uid,$arr);
            if($res){
                //echo $res;die;
                $res1 = D('Card','bank')->sendApplyInfo($res,$uid);
                if($res1['code']==1){
                    $key = 0;
                    $result = D('BankFinance','shop')->addFinance1($uid,$key,$res);
                    if($result){
                        D('bank_contract')->setField('finance_id',$result,'contract_id="'.$contract_id.'"' );
                        $result4 = $this->isOneYear();
                        if(!$result4){
                            D('bank_finance')->setField('status',44,'id='.$result);
                            D('bank_card')->setField('f_mark',2,'id='.$res);
                        }
                        $this->assign('jumpUrl', U('/PocketShop/bankPrice'));
                        //$this->success('提交成功，等待办卡成功');
                        echo 1;
                    }else{
                        D('bank_card')->where('id='.$res)->delete();
                        //$this->error('数据写入失败，请重新申请');
                        echo '数据写入失败，请重新申请';
                    }
                }else{
                    D('bank_card')->where('id='.$res)->delete();
                    //$this->error('办卡页面发生错误，请重新操作');
                    echo '办卡页面发生错误，请重新操作';
                }
                //等待调用办卡接口

            }else{
                //$this->error(D('BankCard')->getError());
                $error = D('BankCard')->getError();
                echo $error;
            }
        }else{

//            $arr = array('姓名'=>'realname','身份证号码'=>'ctf_id','联系地址'=>'address','邮编'=>'post_code','邮箱'=>'email_bill','申请理由'=>'reason');
//            $arr1 = array('手机号码'=>'mobile','父亲手机号码'=>'Dmobile','母亲手机号码'=>'Mmobile');
//            $data = array();
//            foreach($arr as $k=>$v){
//                if(empty($_POST[$v])){
//                    $this->error($k.'不能为空');
//                }
//                $data[$v] = $_POST[$v];
//            }
//            foreach($arr1 as $k=>$v){
//                if(!isValidMobile($_POST[$v])){
//                    $this->error('请正确填写'.$k);
//                }
//                $data[$v] = $_POST[$v];
//            }
            $data = $this->_getCardData();
            $data['f_mark'] = 0;
            $arr = $this->_getUserInfoData();
            $res = D('BankCard','shop')->addBankCard($data,$uid,$arr);
            if($res){
                //等待调用办卡接口
                $res1 = D('Card','bank')->sendApplyInfo($res,$uid);
               // $res1['code']=1;
//                dump($res1);die;
                if($res1['code']==1){
                    $key = 0;
                    $result = D('BankFinance')->addFinance($uid,$data1,$key,$res);
                    if($result){
                        D('bank_contract')->setField('finance_id',$result,'contract_id="'.$contract_id.'"' );
                        $result4 = $this->isOneYear();
                        if(!$result4){
                            D('bank_finance')->setField('status',44,'id='.$result);
                            D('bank_card')->setField('f_mark',2,'id='.$res);
                        }
                        //$this->assign('jumpUrl', U('/PocketShop/bankPrice'));

                        //$this->success('提交成功，等待办卡成功');
                        echo 1;
                    }else{
                        D('bank_card')->where('id='.$res)->delete();
                        //$this->error('数据写入失败，请重新申请');
                        echo '数据写入失败，请重新申请';
                    }
                }else{
                    D('bank_card')->where('id='.$res)->delete();
                    //$this->error('办卡页面发生错误，请重新操作');
                    echo '办卡页面发生错误，请重新操作';
                }

            }else{
                $error = D('BankCard')->getError();
                echo $error;
            }
        }

    }

    //一千元免息
    public function noServise(){
        $uid = $this->mid;
        $sid = getUserField($uid, 'sid');

        $channel = $_GET['channel'];
        //dump($channel);die;
        if(empty($channel)){
            $channel = 0;
        }
        $city = model('Schools')->getCityId($sid);
        $cityName = D('citys')->getField('city','id='.$city);
        $arr2['name'] = D('user')->getField('realname','uid='.$uid);
        $arr2['post_code'] = areaPost($cityName);
        //dump($arr2);die;
        //生成一个合同编号
        $this->assign('arr2',$arr2);

        $contract_id = $this->addNewContract();

       $this->assign('contract_id',$contract_id);

        $data['money'] = 1000;
        $data['staging'] = 1;
        $data['reason'] = '免费体验';
        $data['stagMoney'] = 1000;  //每月还款总额
        $data['benMoney'] = 1000;       //还款每月本金
        $data['servicePrice'] = 0;         //每月服务费
        $mark = 1;
        $this->assign('channel',$channel);
        $this->assign('mark',$mark);
        $this->assign('data',$data);
        $this->display('doMessage');
    }

    //个人订单
    public function myBankList(){
        $this->__isJump(2);
        //echo getStagingPrice1(500,1);die;
        $uid = $this->mid;
        if(empty($uid)){
            $this->error('请登陆后操作');
        }
        $arr = array('审核中','还款中','还款结束');
        $map['uid'] = $uid;
        $status = intval($_GET['status']);
        if($status){
            $this->assign('status',$status);
            $map['status'] = $status;
        }else{
            $map['status'] = array('in','0,1,2');
        }
        $list = D('bank_finance')->where($map)->field('id,money,ctime,stime,etime,reason,staging,status,mark')->order('ctime DESC')->findAll();
        //dump($list);die;
        foreach($list as &$val){
            $val['sta'] = $arr[$val['status']];
            if($val['mark']==2){
                $money = intval($val['money']);
                $val['stagMoney'] = getStagingPrice1($money,$val['staging']);
                //echo $val['stagMoney'];die;
                //$data['stagMoney'] = getStagingPrice($data['money'],$data['staging']);  //每月还款总额
                $val['benMoney'] = getMouPrice($money,$val['staging']);
            }else{
                $val['stagMoney'] = 1000;
                $val['benMoney'] = 1000;
            }
            $val['service'] = $val['stagMoney']-$val['benMoney'];
        }
        $this->assign('list',$list);
        $this->display();
    }

    //订单详情
    public function bankListDetail(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $list = D('bank_finance')->where('id='.$id)->find();
        $map['uid'] = $list['uid'];
        $map['status'] = array('neq',3);
        $blist = D('bank_card')->where($map)->find();
        if($list['mark']==2){
            $list['money'] = intval($list['money']);
                $list['stagMoney'] = getStagingPrice1($list['money'],$list['staging']);
                //$data['stagMoney'] = getStagingPrice($data['money'],$data['staging']);  //每月还款总额
                $list['benMoney'] = getMouPrice($list['money'],$list['staging']);
            }else{
                $list['stagMoney'] = 1000;
                $list['benMoney'] = 1000;
            }
        $str = '****************';
        $blist['ctf_id'] = substr_replace($blist['ctf_id'],$str,1,16);
        $list['service'] = $list['stagMoney']-$list['benMoney'];
        $this->assign('blist',$blist);
        $this->assign('list',$list);
        $this->display();
    }

    //个人中心
    public function personalCenter(){
        $uid = $this->mid;
        if(empty($uid)){
            $this->error('请登陆后操作');
        }
        $list = D('bank_card')->where('uid='.$uid)->find();
        $str = '****************';
        $str1 = '**************';
        if($list['card_no']){
            $list['card_no'] = D('CMB','bank')->decrypt($list['card_no']);
            $list['card_no'] = substr_replace($list['card_no'],$str1,1,14);
        }
        $list['ctf_id'] = substr_replace($list['ctf_id'],$str,1,16);
        $this->assign('list',$list);
        $this->display();
    }

    //用户修改手机号码
    public function editMobile(){
        $id = intval($_POST['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $check = D('bank_card')->getField('check','id='.$id);
        if($check!=1){
            $this->error('您没有权限修改手机号');
        }
        $mobile = t($_POST['mobile']);
        $res = D('BankCard')->changeMobile($id,$mobile);
        if($res){
            $this->success('修改手机成功');
        }else{
            $error = D('BankCard')->getError();
            $this->error($error);
        }
    }

    //查看协议
    public function lookXY(){
        $data['realname'] = $_GET['realname'];
        $data['mobile'] = $_GET['mobile'];
        $data['ctf_id'] = $_GET['ctf_id'];
        $str = '****************';
        $data['ctf_id'] = substr_replace($data['ctf_id'],$str,1,16);
        $data['money'] = $_GET['money'];
        $contract_id = $_GET['contract_id'];
        $data['staging'] = $_GET['staging'];
        $data['stagMoney'] = getKxService($data['money']);
        $data['puMoney'] = getStagingPrice1($data['money'],$data['staging'])-getMouPrice($data['money'],$data['staging'])-$data['stagMoney'];
        $data['stagMoney'] = getMouPrice($data['money'],$data['staging']) + $data['stagMoney'];
        $data['ctime_y'] = date('Y');
        $data['ctime_m'] = date('m');
        $data['ctime_m1'] = date('m') + 1;
        $data['ctime_d'] = date('d');
        $staging = $data['staging'];
        $time = strtotime('+'.$staging.' month');
        $data['etime_y'] = date('Y',$time);
        $data['etime_m'] = date('m',$time);
        $data['etime_d'] = date('d',$time);
        $uid = $this->mid;
        $map['uid'] = $uid;
        $map['status'] = array('neq',3);
        $list = D('bank_card')->where($map)->find();
        if($list['imgs']){
            $imgs = unserialize($list['imgs']);
            $this->assign('imgs',$imgs);
        }
        $m = $_GET['m'];
        switch ($m){
            case 1:  //pu卡提款
                $data['contract_id'] = D('BankContract')->getField('ptm','contract_id="'.$contract_id.'"');
                $this->assign('data',$data);
                $this->display('m_xy2');
                break;
            case 2:  //pu卡服务
                $data['contract_id'] = D('BankContract')->getField('pfm','contract_id="'.$contract_id.'"');
                $this->assign('data',$data);
                $this->display('m_xy1');
                break;
            case 3:   //1000提款
                $data['contract_id'] = D('BankContract')->getField('ptm','contract_id="'.$contract_id.'"');
                $this->assign('data',$data);
                $this->display('m_xy4');
                break;
            case 4:  //1000服务
                $data['contract_id'] = D('BankContract')->getField('pfm','contract_id="'.$contract_id.'"');
                $this->assign('data',$data);
                $this->display('m_xy3');
                break;
            default :
                $this->error('错误操作');
        }
    }

    //查看协议
    public function lookMyPic(){
        $uid = $this->mid;
        $map['uid'] = $uid;
        $map['status'] = array('neq',3);
        $list = D('bank_card')->where($map)->find();
        //dump($list);die;
        $imgs = unserialize($list['imgs']);
        //dump($imgs);die;
        $this->assign('imgs',$imgs);
        $this->display();
    }

    //直通办卡
    public function addPcard(){
        $uid = $this->mid;
        $channel = $_GET['channel'];
        //dump($channel);die;
        if(empty($channel)){
            $channel = 0;
        }
        $sid = getUserField($uid, 'sid');
        $city = model('Schools')->getCityId($sid);
        $cityName = D('citys')->getField('city','id='.$city);
        $arr2['name'] = D('user')->getField('realname','uid='.$uid);
        $arr2['post_code'] = areaPost($cityName);
        //dump($arr2);die;
        //生成一个合同编号
        $this->assign('arr2',$arr2);

        $map['uid'] = $uid;
        $map['status'] = array('neq',3);
        $res = D('bank_card')->where($map)->field('id,realname,mobile,ctf_id')->limit(1)->find();
        $this->assign('res',$res);
        $this->assign('channel',$channel);
        $this->display('doMessage1');
    }

    //处理直通办卡
    public function doAddPcard(){
        $data['realname'] = t($_POST['realname']);
        $data['ctf_id'] = t($_POST['ctf_id']);
        $data['email_bill'] = '496868409@qq.com';
        $data['mobile'] = t($_POST['mobile']);
        $data['address'] = '江苏省苏州市创意产业园4幢A203';
        $data['area'] = '0512';
        $data['school_id'] = 473;
        $data['post_code'] = intval($_POST['post_code']);
        $channel = $_POST['channel'];
        if($channel){
            $data['channel'] = D('CMB','bank')->decrypt($channel);
        }else{
            $data['channel'] = 0;
        }
        $data['uid'] = $this->mid;
        //$data['channel'] = $_POST['channel'];
        $data['surplus_line'] = 5000;
        $data['total_line'] = 5000;
        $res = D('BankCard')->addPcard($data);
        if($res){
            $res1 = D('Card','bank')->sendApplyInfo($res,$data['uid']);
            if($res1['code']==1){
                $this->assign('jumpUrl', U('/PocketShop/bankPrice'));
                $this->success('办卡申请提交成功，等待银行审核');
            }else{
                $this->error('办卡申请提交失败，请重试');
            }
        }else{
            $this->error('提交信息失败');
        }
    }

    //查看还款计划
    public function lookRepayment(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $list = D('bank_finance_detail')->where('bank_finance_id='.$id)->field('money,surp_money,stime,status')->findAll();
        $this->assign('list',$list);
        $this->display();
    }


    //前台用户符合情况修改自己的手机号
    public function userEditMobile(){
        $id = $this->mid;   //传递的是uid
        if(empty($id)){
            $this->error('错误操作，请先登录后再操作');
        }
        $userList = D('bank_card')->where('uid = '.$id)->find();
        $userInfo = D('bank_user_info')->where('uid='.$id)->find();
        $this->assign('userList',$userList);
        $this->assign('userInfo',$userInfo);
        $this->display();
    }

    //验证手机号成功就ok
    public function doEditMobile(){
        $mobile = t($_POST['mobile']);
        if(!isValidMobile($data['d_mobile'])){
            $this->error('请正确输入手机号码');
        }
    }




    //获取口袋金首页的特卖商品
    private function getHotGoods(){
        $list = D('pocket_goods')->where('isDel=0 and isHot=1')->field('id,name,price,pic,stock')->limit(6)->findAll();
        return $list;
    }

    //获取口袋金借款原因
    private function getAllReason(){
        $map['isDel'] = 0;
        $list = D('pocket_reason')->where($map)->findAll();
        return $list;
    }

     //判断是不是大四毕业生
    private function isFromReg(){
        $uid = $this->mid;
        $result = D('user')->getField('from_reg','uid='.$uid); //0的时候是系统导入  1  自己注册
        return $result;
    }

    //用户信息确认
    public function doMessage2(){

        //判断是不是大四毕业生
        $result = $this->isFromReg();
        $uid = $this->mid;
        //接收数据
        $channel = $_POST['channel'];
        $data = $this->_checkBankPrice();
        //判断用户是否是新用户
        $map['uid'] = $uid;
        $map['status'] = array('neq',3);
        $res = D('bank_card')->where($map)->field('id,realname,mobile,address,ctf_id,card_no,allow_risk,d_mobile,m_mobile,post_code,email_bill,surplus_line,referrer')->limit(1)->find();
        //现在的
        if($res){
            //老用户
                //是否在额度范围
                $list = D('bank_user_info')->where('bank_id='.$res['id'])->find();
                $str = '****************';
                $res['ctf_id'] = substr_replace($res['ctf_id'],$str,1,16);
                $this->assign('list',$list);
                $this->assign('res',$res);
            //已经有卡
            $map2['status'] = 0;
            $map2['bank_card_id'] = $res['id'];
            $money2 = D('bank_finance')->where($map2)->sum('money');  //已经申请的金额
            $money2 = intval($money2);
            $line2 = D('bank_card')->getField('surplus_line','id='.$res['id']);
            if(($line2-$money2)<$data['money']){
                $this->error('您的额度不足');
            }
            //老用户产生合同编号
            $contract_id = $this->addOldContract();
        }else{
            //新用户
            $contract_id = $this->addNewContract();
        }
        $arr2['name'] = D('user')->getField('realname','uid='.$uid);
        $arr2['post_code'] = areaPost($cityName);
        $this->assign('arr2',$arr2);
        $this->assign('contract_id',$contract_id);
        //提交
        $mark = 2;   //订单类型
        $this->assign('channel',$channel);
        $this->assign('mark',$mark);
        $this->assign('data',$data);
        //根据不同的result值（代表是否是系统导入） 显示不同的页面
        $this->display();
    }

    //判断用户信息是否完善
    public function isAllInfo(){
        $uid = $this->mid;
        $res = D('bank_user_info')->where('uid='.$uid)->find();
        if($res){
            return 1;
        }else{
            return 0;
        }
    }



    public function ordercenter(){
        $uid = $this->mid ;
        $status = t($_GET['status']) ;
        if ($status!== null) {
            $map['status'] = array('eq',$status) ;
            $this->status = $status ;
        }
        $map['ts_pocket_order.uid'] = array('eq',$uid) ;
        $this->list = $list = M('pocket_order')
                            ->join('join ts_pocket_goods g on ts_pocket_order.gid = g.id')
                            ->field('g.name,g.pic,g.price,ts_pocket_order.reason,ts_pocket_order.staging')->where($map)->select() ;
        //dump($list) ; die ;
        $this->display() ;
    }

    public function testCardEd(){
        $map['status']=2;
        $list = D('bank_card')->where($map)->field('id,surplus_line')->order('id ASC')->limit('3500,500')->select();

        if(empty($list)){
            die('ok');
        }
        foreach($list as $v){
            $map1['status'] = 1;
            $map1['bank_card_id'] = $v['id'];
            $money = D('bank_finance')->where($map1)->sum('money');
            $money = intval($money);
            $data['surplus_line'] = 5000 - $money;
            if($data['surplus_line'] != $v['surplus_line']){
                D('bank_card')->where('id='.$v['id'])->save($data);
            }
            unset($data['surplus_line']);
        }
        //dump($list);die;
    }

    /**
     * 处理申请PU卡的页面
     * 并增加权限判断
     */
    public function apply()
    {
        $uid = $this->mid;
        $map['uid'] = $uid;
        $map['status'] = 2;
        $cardInfo = M('bank_card')->where($map)->find();
        $flag = empty($cardInfo) ? 0 : 1;
        $this->assign('flag',$flag);
        $this->display();
    }


}

?>
