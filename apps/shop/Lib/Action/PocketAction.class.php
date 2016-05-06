<?php

import('home.Action.PubackAction');

class PocketAction extends PubackAction {

//获取首页的数据
    public function index() {
        if (!empty($_POST)) {
            $_SESSION['admin_grow_search'] = serialize($_POST);
        } elseif (isset($_GET['p'])) {
            $_SESSION['admin_grow_search_p'] = intval($_GET['p']);
            $_POST = unserialize($_SESSION['admin_grow_search']);
        } else {
            unset($_SESSION['admin_grow_search']);
        }
        $_POST['name'] && $map['name'] = array('like', '%' . t($_POST['name']) . '%');
        $_POST['cid'] && $map['cid'] = intval($_POST['cid']);
        $_POST['isDel'] && $map['isDel'] = intval($_POST['isDel']);
        $dao = D('pocket_goods');
        $daocate = D('pocket_category');
        $daopro = D('pocket_profit');
        $list = $dao->where($map)->order('ordernum DESC')->findPage(10);
        foreach($list['data'] as &$val){
            $res = $daocate->where('id='.$val['cid'])->find();
            $val['cid'] = $res['name'];
            $result = $daopro->where('id='.$val['profitId'])->field('id,name')->find();
            $val['profitName'] = $result['name'];
        }
        $clist = $this->getAllCate();
        $plist = $this->getAllProfit();
        $this->assign('plist',$plist);
        $this->assign('clist',$clist);
        $this->assign($list);
        $this->display();
    }

    //设置商品为特卖商品
    public function addHotPocket(){
        $id = intval($_GET['id']);
        if (empty($id)) {
            $this->error('操作失败');
        } else {
            $res = M('pocket_goods')->where('id = '.$id)->field('id,isHot')->find();
            if($res['isHot']==1){
                $result = M('pocket_goods')->setField('isHot',0,'id=' . $id);
            }else{
                $result = M('pocket_goods')->setField('isHot',1,'id=' . $id);
            }
        }
        if ($result) {
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    //设置Pu专属商品
    public function addPuPocket(){
        $id = intval($_GET['id']);
        if (empty($id)) {
            $this->error('操作失败');
        } else {
            $res = M('pocket_goods')->where('id = '.$id)->field('id,isPu')->find();
            if($res['isPu']==1){
                $result = M('pocket_goods')->setField('isPu',0,'id=' . $id);
            }else{
                $result = M('pocket_goods')->setField('isPu',1,'id=' . $id);
            }
        }
        if ($result) {
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    //获取所有的利率
    public function getAllProfit() {
        $dao = M('pocket_profit');
        $map['isDel'] = 0;
        $list = $dao->where($map)->order('id ASC')->select();
        return $list;
    }

    //获取所有的频道
    public function getAllCate() {
        $daocate = M('pocket_category');
        $map['pid'] = 0;
        $map['isDel'] = 0;
        $list = $daocate->where($map)->order('id ASC')->select();
        return $list;
    }

    public function addPocket() {
        $this->assign('type', 'add');
        $id = $_REQUEST['id'];
        if ($id) {
            $this->assign('type', 'edit');
            $map['id'] = $id;
            $obj = M('pocket_goods')->where($map)->find();
            //dump($obj);die;
            if (!$obj) {
                $this->error('无法找到对象');
            }
            $opt = M('pocket_goods_opt')->where('gid='.$id)->find();
            //dump($opt);die;
            $opt['imgs'] = unserialize($opt['imgs']);
            $opt['color'] = unserialize($opt['color']);
            $this->assign($opt);
            $this->assign($obj);
        }
        $proList = D('pocket_profit')->where('isDel = 0')->findAll();
        $this->assign('proList',$proList);
        $list = $this->getAllCate();
        $this->assign('list',$list);
        $this->display();
    }

    //处理添加商品和修改商品
    public function doAddPocket() {
        $id = intval($_REQUEST['id']);
        if (empty($id)) {
            $this->_insertYg();
        } else {
            $this->_updateYg($id);
        }
    }

    //添加商品是自动带出最低月供
    public function getGoodLowest(){
        $price = $_POST['price'];
        $profit = $_POST['profit'];
        $result = D('pocket_profit')->where('id='.$profit)->find();
        $interest = unserialize($result['interest']);
        $max = 0;
        foreach($interest as $key=>$val){
            if($key>$max){
                $max = $key;
            }
        }
        $eveprice = round($price/$max,2);  //每月本金
        $service =round($eveprice * $interest[$max],2); //每月的服务费
        $data['all'] = $eveprice +$service;
        $data['staging'] = $max;
        echo json_encode($data);
    }

    private function _insertYg() {
        $data = $this->_getYgData();
        $dao = D('PocketGoods');
        $res = $dao->addProduct($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Pocket/index'));
            $this->success('添加成功');
        } else {
            $this->error($dao->getError());
        }
    }

    private function _getYgData() {
        $info = tsUploadImg();
        if ($info['status']) {
            $data['pic'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        } elseif ($info['info'] != '没有选择上传文件') {
            $this->error($info['info']);
        }
        $data['name'] = t($_REQUEST['name']);
        $data['content'] = t(h($_REQUEST['content']));
        $data['cid'] = intval($_REQUEST['cid']);
        $data['market'] = t($_REQUEST['market'])*100/100;
        $data['num'] = intval($_REQUEST['num']);
        $data['lowest'] = t($_REQUEST['lowest'])*100/100;
        $data['profitId'] = intval($_REQUEST['profitId']);
        $data['price'] = t($_REQUEST['price'])*100/100;
        $data['stock'] = intval($_REQUEST['stock']);
        $data['lowestShoufu'] = t($_REQUEST['lowestShoufu'])*100/100;
        $data['desc'] = t($_REQUEST['desc'],'br');
        if($data['price']>$data['market']){
            $this->error('商品价格大于市场价格');
        }
        if($_REQUEST['color']){
            $data['color'] = serialize($_REQUEST['color']);
        }else{
            $data['color'] = null;
        }
        $data['imgs'] = serialize($_REQUEST['imgs']);
        return $data;
    }

    private function _updateYg($id) {
        $data = $this->_getYgData();
        $data['id'] = $id;
        $dao = D('PocketGoods');
        $res = $dao->updateProduct($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Pocket/index'));
            $this->success('修改成功');
        } else {
            $this->error($dao->getError());
        }
    }
    //修改商品显示顺序
    public function changeOrder() {
        $id = intval($_POST['id']);
        $ordernum = intval($_POST['ordernum']);
        $dao = D('PocketGoods');
        $res = $dao->setField('ordernum', $ordernum, 'id='.$id);
        if ($res) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败，或无变化');
        }
    }

    //修改商品价格
    public function changePrice() {
        $id = intval($_POST['id']);
        $price = intval($_POST['newPrice']);
        $dao = D('PocketGoods');
        $res = $dao->setField('price', $price, 'id='.$id);
        if ($res) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败，或无变化');
        }
    }

    //修改商品价格
    public function changeShoufu() {
        $id = intval($_POST['id']);
        $shoufu = intval($_POST['newShoufu']);
        $dao = D('PocketGoods');
        $res = $dao->setField('lowestShoufu', $shoufu, 'id='.$id);
        if ($res) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败，或无变化');
        }
    }
    //修改商品是否上架
    public function editPocket() {
        $id = intval($_GET['id']);
        if (empty($id)) {
            $this->error('操作失败');
        } else {
            $res = M('pocket_goods')->where('id = '.$id)->field('id,isDel')->find();
            if($res['isDel']==1){
                $result = M('pocket_goods')->setField('isDel',0,'id=' . $id);
            }else{
                $result = M('pocket_goods')->setField('isDel',1,'id=' . $id);
            }
        }
        if ($result) {
            $this->success('操作成功');
        }
    }

    //显示频道类别
    public function categroyList() {
        $tree = M('pocket_category')->where('isDel=0')->field("id,name,pid as pId")->order('display_order ASC')->findAll();
        //dump($tree);die;
        $this->assign('tree', json_encode($tree));
        $this->display();
    }

    //添加频道类别
    public function addCategroy() {
        if (empty($_POST ['title'])) {
            $this->error('名称不能为空！');
        }
        $cate ['name'] = t($_POST ['title']);
        $cate ['display_order'] = pinyin($cate ['name']);
        $cate ['pid'] = intval($_POST ['pid']); //1级分类
        if(!$cate['pid']==0){
            $this->error('错误操作！');
        }
        $categoryId = M('pocket_category')->add($cate);
        if ($categoryId) {
            S('Cache_Pocket_Category', null);
            $this->success($categoryId);
        } else {
            $this->error('操作失败！');
        }
    }

    //重命名分类
    public function renameCategroy() {
        $id = intval($_POST ['id']);
        $name = t($_POST ['title']);
        if ($name == '') {
            $this->error('名称不能为空！');
        }
        $data['name'] = $name;
        $data ['display_order'] = pinyin($name);
        $res = M('pocket_category')->where('id=' . $id)->save($data);
        if ($res) {
            S('Cache_Pocket_Category', null);
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }

    //删除分类
    public function delCategroy(){
        $id = intval($_POST ['id']);
        if(empty($id)){
            $this->error('没有操作对象！');
        }
        $data['isDel'] = 1;
        $result = M('pocket_category')->where('id=' . $id)->save($data);
        if($result){
            S('Cache_Pocket_Category', null);
            $this->success('操作成功！');
        }else{
            $this->error('操作失败！');
        }
    }

    public function addStaging(){
        $gid = intval($_GET['gid']);
        $this->assign('gid',$gid);
        $this->display();
    }

    public function doAddStaging(){
        $data['gid'] = intval($_POST['gid']);
        $data['price'] = intval($_POST['price']);
        $data['shoufu'] = intval($_POST['shoufu']);
        if(!$data['gid']){
            $this->error('错误操作');
        }
        if($data['price']<=0){
            $this->error('填写的价格不规范');
        }
        if($data['shoufu']<0){
            $data['shoufu'] = 0;
        }
        $result = D('staging_goods')->where('gid='.$data['gid'])->find();
        if($result){
            $this->error('该商品已存在，不可重复添加');
        }
        $res = D('staging_goods')->add($data);
        if($res){
            $this->assign('jumpUrl', U('/Pocket/stagingList'));
            $this->success('添加成功');
        }else{
            $this->error('添加失败');
        }
    }

    //订单列表
    public function orderList(){
        $map['status'] = array('neq',5);
        $list = D('pocket_order')->where($map)->order('id DESC')->findPage(10);
        $dao = D('pocket_goods');
        foreach($list['data'] as &$val){
            $res = $dao->where('id='.$val['gid'])->find();
            $val['goods'] = $res['name'];
            $val['realname'] = getUserField($val['uid'],'realname');
            $sid = getUserField($val['uid'],'sid');
            $val['school'] = tsGetSchoolName($sid);
        }
        $this->assign($list);
        $this->display();
    }

    //特卖商品订单
    public function puOrderList(){
        $map['status'] = array('neq',3);
        $list = D('pocket_pu_order')->where($map)->order('id DESC')->findPage(10);
        $dao = D('pocket_goods');
        foreach($list['data'] as &$val){
            $res = $dao->where('id='.$val['gid'])->find();
            $val['goods'] = $res['name'];
            $val['realname'] = getUserField($val['uid'],'realname');
            $sid = getUserField($val['uid'],'sid');
            $val['school'] = tsGetSchoolName($sid);
        }
        $this->assign($list);
        $this->display();
    }

    //处理订单审核通过
    public function doOrder(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $data['status']=1;
        $result = D('pocket_order')->where('id='.$id)->save($data);
        if($result){
            $this->success('订单审核成功');
        }else{
            $this->error('操作失败');
        }
    }

    //处理审核不通过
    public function editOrder(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $list = D('pocket_order')->where('id='.$id)->find();
        if(!$list){
            $this->error('获取订单信息失败');
        }
        $dao = D('pocket_goods');
        $daoress = D('pocket_address');
        $result = $daoress->where('id='.$list['addressId'])->find();
        $list['addressId'] = $result['address'];
        $res = $dao->where('id='.$list['gid'])->find();
        $list['gid'] = $res['name'];
        $list['uid'] = getUserName($list['uid']);
        $this->assign('list',$list);
        $this->assign('result',$result);
        $this->display();
    }

    //驳回
    public function doEditOrder(){
        $id = intval($_POST['id']);
        if(!$id){
            $this->error('错误操作');
        }
        if(empty($_POST['reason'])){
            $this->error('请填写驳回原因');
        }
        $data['reason'] = t($_POST['reason']);
        $data['status'] = 2;
        $result = D('pocket_order')->where('id='.$id)->save($data);
        if($result){
            $gid = D('pocket_order')->getField('gid','id='.$id);
            $list = D('pocket_goods')->where('id='.$gid)->find();
            $stock = $list['stock']+1;
            //echo $stock;die;
            D('pocket_goods')->setField('stock',$stock,'id='.$gid);
            $this->assign('jumpUrl', U('shop/Pocket/orderList'));
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    //获取用户信息
    public function userInfo(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $result = D('pocket_order')->where('id='.$id)->field('gid,addressId,reason,color')->find();
        $addressId = $result['addressId'];
        $daoress = D('pocket_address');
        $list = $daoress->where('id='.$addressId)->find();
        $res = D('pocket_goods')->where('id='.$result['gid'])->find();
        $res['color'] = $result['color'];
        if($result['reason']){
            $this->assign('reason',$result['reason']);
        }
        $this->assign('res',$res);
        $this->assign('list',$list);
        $this->display();
    }

    //获取用户信息 pu币订单
    public function userInfo1(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $result = D('pocket_pu_order')->where('id='.$id)->field('gid,addressId,color')->find();
        $addressId = $result['addressId'];
        $daoress = D('pocket_address');
        $list = $daoress->where('id='.$addressId)->find();
        $res = D('pocket_goods')->where('id='.$result['gid'])->find();
        $res['color'] = $result['color'];
        $this->assign('res',$res);
        $this->assign('list',$list);
        $this->display('userInfo');
    }
    //口袋乐商品列表
    public function stagingList(){

    }

    //口袋金订单列表
    public function priceList(){
        $map['status'] = array('neq',5);
        $list = D('pocket_price')->where($map)->order('id DESC')->findPage(10);
        $dao = D('pocket_reason');
        foreach($list['data'] as &$val){
            $res = $dao->where('id='.$val['reasonId'])->find();
            $val['reasonId'] = $res['name'];
            $val['realname'] = getUserField($val['uid'],'realname');
            $sid = getUserField($val['uid'],'sid');
            $val['school'] = tsGetSchoolName($sid);
        }
        $this->assign($list);
        $this->display();
    }

    //处理口袋金订单
    public function doPriceOrder(){
        $m = intval($_GET['m']);
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        switch($m){
            case 1:
                $data['status']=1;
            break;
            case 2:
                $data['status']=3;
            break;
            case 3:
                $data['status']=4;
            break;
            default:
                $this->error('错误操作');
            break;
        }
        $result = D('pocket_price')->where('id='.$id)->save($data);
        if($result){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    //处理审核不通过
    public function editPriceOrder(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $list = D('pocket_price')->where('id='.$id)->find();
        if(!$list){
            $this->error('获取订单信息失败');
        }
        $dao = D('pocket_address');
        $result = $dao->where('id='.$list['addressId'])->find();
        $daoreason = D('pocket_reason');
        $res = $daoreason->where('id='.$list['reasonId'])->find();
        $list['reasonId'] = $res['name'];
        $list['uid'] = getUserName($list['uid']);
        $this->assign('list',$list);
        $this->assign('result',$result);
        $this->display();
    }

    public function doEditPrice(){
        $id = intval($_POST['id']);
        if(!$id){
            $this->error('错误操作');
        }
        if(empty($_POST['reason'])){
            $this->error('请填写驳回原因');
        }
        $data['reason'] = t($_POST['reason']);
        $data['status'] = 2;
        $result = D('pocket_price')->where('id='.$id)->save($data);
        if($result){
            $this->assign('jumpUrl', U('shop/Pocket/priceList'));
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }



    //借款原因列表
    public function reasonList(){
        $map['isDel'] = 0;
        $list = D('pocket_reason')->where($map)->findPage(10);
        $this->assign($list);
        $this->display();
    }

    //添加借款原因
    public function addReason(){
        $this->display();
    }

    public function doAddReason(){
        $data['name'] = trim($_POST['name']);
        if(empty($data['name'])|| get_str_length($data['name'])>10 ){
            $this->error('原因不能为空而且长度在10个字以内');
        }
        $data['name'] = t($_POST['name']);
        $res = D('pocket_reason')->add($data);
        if($res){
            $this->assign('jumpUrl', U('shop/Pocket/reasonList'));
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }
    //删除原因
    public function delReason(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $data['isDel'] = 1;
        $res = D('pocket_reason')->where('id='.$id)->save($data);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    //获取用户信息
    public function priceUserInfo(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $result = D('pocket_price')->where('id='.$id)->field('addressId,reason,price,staging,stagPrice,reasonId')->find();
        $addressId = $result['addressId'];
        $daoress = D('pocket_address');
        $list = $daoress->where('id='.$addressId)->find();
        $res = D('pocket_reason')->where('id='.$result['reasonId'])->find();
        if($result['reason']){
            $this->assign('reason',$result['reason']);
        }
        $this->assign('result',$result);
        $this->assign('res',$res);
        $this->assign('list',$list);
        $this->display();
    }

    //口袋乐商品的利率管理列表
    public function profitList(){
        $map['isDel'] = 0;
        $list = D('pocket_profit')->where($map)->order('id DESC')->findPage(10);
        //dump($list);die;
        foreach($list['data'] as &$val){
            $val['interest'] = unserialize($val['interest']);
        }
        //dump($list);die;
        $this->assign($list);
        $this->display();
    }

    //完整的信息
    public function profitAll(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $list = D('pocket_profit')->where('id='.$id)->find();
        $list['interest'] = unserialize($list['interest']);
        $this->assign('list',$list);
        $this->display();
    }

    //添加新的利率
    public function addProfit(){
        $id = intval($_GET['id']);
        if($id){
            //修改
            $list = D('pocket_profit')->where('id = '.$id)->find();
            $list['interest'] = unserialize($list['interest']);
            $this->assign('list',$list);
        }
        $this->display();
    }

//    //修改商品的最低首付
//    public function test11(){
//        $list = D('pocket_goods')->field('id,profitId,price')->limit(3000,3000)->findAll();
//        foreach($list as $val){
//            $result = D('pocket_profit')->where('id='.$val['profitId'])->find();
//            $interest = unserialize($result['interest']);
//            $max = 0;
//            foreach($interest as $key=>$v){
//                if($key>$max){
//                    $max = $key;
//                }
//            }
//            $eveprice = round($val['price']/$max,2);  //每月本金
//            $service =round($eveprice * $interest[$max],2); //每月的服务费
//            $data['lowest'] = $eveprice +$service;
//            $data['num'] = $max;
//            D('PocketGoods')->where('id='.$val['id'])->save($data);
//            unset($data);
//            unset($result);
//        }
//        die('ok');
//    }

    //修改商品利率
    public function changeProfit() {
        $id = intval($_POST['id']);
        $profitId= intval($_POST['profitId']);
        $dao = D('PocketGoods');
        $res = $dao->setField('profitId', $profitId, 'id='.$id);
        if ($res) {
            //
            $price = $dao->getField('price','id='.$id);
            $result = D('pocket_profit')->where('id='.$profitId)->find();
            $interest = unserialize($result['interest']);
            $max = 0;
            foreach($interest as $key=>$val){
                if($key>$max){
                    $max = $key;
                }
            }
            $eveprice = round($price/$max,2);  //每月本金
            $service =round($eveprice * $interest[$max],2); //每月的服务费
            $data['lowest'] = $eveprice +$service;
            $data['num'] = $max;
            $dao->where('id='.$id)->save($data);
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    //处理添加 修改 利率
    public function doAddProfit(){
        $data['name'] = t($_POST['name']);
        if(empty($data['name'])){
            $this->error('利率名称为空');
        }
        for($i=1;$i<=24;$i++){
            $arr[$i] = $_POST['interest'][$i];
            if(empty($arr[$i])){
                unset($_POST['interest'][$i]);
            }
        }
        if(empty($_POST['interest'])){
            $this->error('利率填写不符合规范');
        }
        $data['interest'] = serialize($_POST['interest']);
        $id = intval($_POST['id']);
        if($id){
            $res = D('pocket_profit')->where('id='.$id)->save($data);
            if($res){
                $this->assign('jumpUrl', U('shop/Pocket/profitList'));
                $this->success('修改成功');
            }else{
                $this->error('修改失败');
            }
        }else{
            $res = D('pocket_profit')->add($data);
            if($res){
                $this->assign('jumpUrl', U('shop/Pocket/profitList'));
                $this->success('添加成功');
            }else{
                $this->error('添加失败');
            }
        }
    }

    //删除利率
    public function delProfit(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $data['isDel'] = 1;
        $res = D('pocket_profit')->where('id='.$id)->save($data);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    //首页广告图列表
    public function logoList(){
    	$map['isDel']=0;
        $list = D('pocket_logo')->where($map)->order('ordernum DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function addLogo(){
        $id = intval($_GET['id']);
        if($id){
            $list = D('pocket_logo')->where('id='.$id)->find();
            $this->assign('list',$list);
        }
        $this->display();
    }

    //添加广告图
    public function doAddLogo(){
        $data['path'] = $_POST['path'];
        $info = tsUploadImg();
        if ($info['status']) {
            $data['pic'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        } elseif (($info['info'] != '没有选择上传文件')||(($info['info'] = '没有选择上传文件')&& !$_POST['id']) ) {
            $this->error($info['info']);
        }
        if(empty($data['path'])){
            $this->error('请填写链接地址');
        }
        if(!$_POST['id']){
            $res = D('pocket_logo')->add($data);
        }else{
            $id = intval($_POST['id']);
            $res = D('pocket_logo')->where('id='.$id)->save($data);
        }
        if($res){
            $this->assign('jumpUrl', U('shop/Pocket/logoList'));
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    //修改广告是否前台显示
    public function editLogo(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $list = D('pocket_logo')->where('id='.$id)->find();
        if($list['isShow']==1){
            $data['isShow']=0;
        }else{
            $data['isShow']=1;
        }
        $res = D('pocket_logo')->where('id='.$id)->save($data);
        if($res){
            $this->success('修改成功');
        }else{
            $this->error('操作失败');
        }
    }

    public function changeLogoOrder() {
        $id = intval($_POST['id']);
        $ordernum = intval($_POST['ordernum']);
        $dao = D('PocketLogo');
        $res = $dao->setField('ordernum', $ordernum, 'id='.$id);
        if ($res) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败，或无变化');
        }
    }

    //删除广告信息
    public function delLogo(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $data['isDel'] = 1;
        $res = D('pocket_logo')->where('id = '.$id)->save($data);
        if($res){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    //口袋金申请办卡列表
    public function bankList(){
        if($_POST['ctf_id']){
            $_POST['ctf_id'] && $map['ctf_id'] = array('like', '%' . t($_POST['ctf_id']) . '%');
        }
        if($_POST['realname']){
            $_POST['realname'] && $map['realname'] = array('like', '%' . t($_POST['realname']) . '%');
        }
        $map['allow_risk'] = 0;
        $list = D('bank_card')->where($map)->order('`f_mark` ASC,`id` ASC')->findPage(20);
        $arr = array('0'=>'未处理','1'=>'处理中','2'=>'办卡成功','3'=>'办卡失败');          //办卡所有状态
        $arr1 = array('0'=>'非免息','1'=>'一千元免息','2'=>'待毕业生');
        $str = '**************';

        foreach($list['data'] as &$v){
            $v['status_detail'] = $arr[$v['status']];     //办卡状态
            $v['school'] = D('school')->getField('title','id='.$v['school_id']);
            $v['f_detail'] = $arr1[$v['f_mark']];   //用户第一笔订单状态
            $v['ctf_id'] = substr_replace($v['ctf_id'],$str,2,14);
        }
        $this->assign($list);
        $this->display();
    }

    //风控审核过的列表
    public function agreeBankList(){
        if($_POST['ctf_id']){
            $_POST['ctf_id'] && $map['ctf_id'] = array('like', '%' . t($_POST['ctf_id']) . '%');
        }
        if($_POST['realname']){
            $_POST['realname'] && $map['realname'] = array('like', '%' . t($_POST['realname']) . '%');
        }
        $map['allow_risk'] = array('neq',0);
        $list = D('bank_card')->where($map)->order('id ASC')->findPage(10);
        $arr = array('1'=>'通过','2'=>'驳回');
        $str1 = '************';
        foreach($list['data'] as &$v){
            $v['status_detail'] = $arr[$v['allow_risk']];     //状态
            $v['card_no'] = D('CMB','bank')->decrypt($v['card_no']);
            $v['card_no'] = substr_replace($v['card_no'],$str1,2,12);
        }
        $this->assign($list);
        $this->display();
    }

    //处理1000风控审核不通过
    public function noFreeCard(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $res = D('bank_card')->setField('free_allow_risk',0,'id='.$id);
        if($res){
            D('bank_finance')->setField('status',0,'mark=1 and bank_card_id='.$id);
            D('BankOp')->do_log_op_info($this->mid,$id,'noFreeCard');
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    //处理1000风控审核通过
    public function agreeFreeCard(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $data['free_allow_risk'] = 1;
        $res = D('bank_card')->where('id='.$id)->save($data);
        if($res){
            $map['bank_card_id'] = $id;
            $map['mark'] = 1;
            $map['status'] = 0;
            $data1['status'] = 40;
            $res1 = D('bank_finance')->where($map)->save($data1);
            D('BankOp')->do_log_op_info($this->mid,$id,'agreeFreeCard');
        }else{
            $this->error('操作失败');
        }
        if($res1){
            $map1['bank_card_id'] = $id;
            $map1['mark'] = 1;
            $map1['status'] = 40;
            $list = D('bank_finance')->where($map1)->field('id')->find();
            D('BankFinance')->editTime($list['id']);
            $this->success('操作成功');
        }else{
            $data2['free_allow_risk'] = 0;
            $res = D('bank_card')->where('id='.$id)->save($data2);
            $this->error('操作失败');
        }
    }

    //处理风控审核通过
    public function agreeCard(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $data['allow_risk'] = 1;
        $res = D('bank_card')->where('id='.$id)->save($data);
        if($res){
            $map['bank_card_id'] = $id;
            $map['mark'] = 2;
            $map['status'] = 0;
            $data1['status'] = 40;
            $list = D('bank_finance')->where($map)->findAll();
            if($list){
                //有订单
                $res1 = D('bank_finance')->where($map)->save($data1);
                if($res1){
                    //更新还款时间
                    $map1['bank_card_id'] = $id;
                    $map1['mark'] = 2;
                    $map1['status'] = 40;
                    $list = D('bank_finance')->where($map1)->field('id')->findAll();
                    foreach($list as $val){
                        D('BankFinance')->editTime($val['id']);
                    }
                    D('BankOp')->do_log_op_info($this->mid,$id,'agreeCard');
                   $this->success('操作成功');
                }else{
                    $data2['allow_risk'] = 0;
                    $res = D('bank_card')->where('id='.$id)->save($data2);
                    D('BankOp')->do_log_op_info($this->mid,$id,'agreeCard');
                    $this->error('操作失败');
                }
            }else{
                //无订单
                $this->success('操作成功');
            }

        }else{
            $this->error('操作失败');
        }

    }
    //修改列表信息
    public function editBankCard(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $list = D('bank_card')->where('id='.$id)->find();
        if(empty($list)){
            $this->error('暂无数据');
        }
        if($list['card_no']){
            $list['card_no'] = D('CMB','bank')->decrypt($list['card_no']);
        }
        $res = D('bank_user_info')->where('bank_id='.$id)->find();
        $this->assign('res',$res);

        D('BankOp')->do_log_op_info($this->mid,$id,'editBankCard');

        $imgs = unserialize($list['imgs']);
        $this->assign('imgs',$imgs);
        $this->assign('list',$list);
        $this->display();
    }

    //删除用户记录
    public function delBankCard(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $list = D('bank_card')->where('id='.$id)->find();
        if(empty($list)){
            $this->error('暂无数据');
        }else{
            $res = D('bank_card')->where('id='.$id)->delete();
            D('bank_finance')->where('bank_card_id='.$id)->delete();
            D('bank_finance_detail')->where('bank_card_id='.$id)->delete();
            D('bank_user_info')->where('bank_id='.$id)->delete();
            D('BankOp')->do_log_op_info($this->mid,$id,'delBankCard');
        }
        if($res){
            $this->success('删除成功');
        }else{
            $this->error('操作失败');
        }
    }

    //处理数据
    private function _getBankData(){
        $data['realname'] = t($_POST['realname']);
        $data['address'] = t($_POST['address']);
        $data['ctf_id'] = t($_POST['ctf_id']);
        $data['email_bill'] = t($_POST['email_bill']);
        $data['mobile'] = t($_POST['mobile']);
        $data['allow_risk'] = $_POST['allow_risk'];
        $data['back_reason'] = t($_POST['back_reason']);
        $data['d_mobile'] = t($_POST['d_mobile']);
        $data['m_mobile'] = t($_POST['m_mobile']);
        $data['post_code'] = intval($_POST['post_code']);
        $data['status'] = intval($_POST['status']);
        $data['card_no'] = t($_POST['card_no']);
        $data['card_account'] = t($_POST['card_account']);
        $data['imgs'] = serialize($_POST['imgs']);
        //dump($data);die;
        return $data;
    }

    //处理数据
    private function _getBankInfoData(){
        $data['qq'] = t($_POST['qq']);
        $data['d_name'] = t($_POST['d_name']);
        $data['d_company'] = t($_POST['d_company']);
        $data['dc_mobile'] = t($_POST['dc_mobile']);
        $data['home'] = t($_POST['home']);
        $data['classmate'] = $_POST['classmate'];
        $data['class_mobile'] = t($_POST['class_mobile']);
        $data['class_address'] = t($_POST['class_address']);
        //dump($data);die;
        return $data;
    }

    //处理修改列表信息
    public function doEditBankCard(){

        $id = intval($_POST ['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $data = $this->_getBankData();
        $arr = $this->_getBankInfoData();
        if($data['card_no']){
            $data['card_no'] = D('CMB','bank')->encrypt($data['card_no']);
        }
        $res = D('BankCard')->editCardMessage($id,$data,$arr);
        D('BankOp')->do_log_op_info($this->mid,$id,'doEditBankCard');
        if($res){
            $this->assign('jumpUrl', U('shop/Pocket/bankList'));
            $this->success('修改成功');
        }else{
            $this->error(D('BankCard')->getError());
        }
    }

    //查看附件
    public function lookBankPic(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $list = D('bank_card')->where('id='.$id)->field('id,imgs')->find();
        $imgs = unserialize($list['imgs']);
        //dump($imgs);die;
        $this->assign('imgs',$imgs);
        $this->display();
    }

//    //利率列表
//    public function rateList(){
//        $map['isDel'] = 0;
//        $list = D('bank_rate')->where($map)->findPage(10);
//        $this->assign($list);
//        $this->display();
//    }
//
//    //修改利率
//    public function changeRate(){
//        $id = intval($_POST['id']);
//        $rate = intval($_POST['newRate']);
//        $dao = D('bank_rate');
//        $res = $dao->setField('rate', $rate, 'id='.$id);
//        if ($res) {
//            $this->success('修改成功');
//        } else {
//            $this->error('修改失败，或无变化');
//        }
//    }
//
//    //添加新的利率
//    public function addRate(){
//        $id = intval($_GET['id']);
//        if($id){
//            //修改 查看详情
//            $map['id'] = $id;
//            $list = D('bank_rate')->where($map)->find();
//            $this->assign('list',$list);
//        }
//        $this->display();
//    }
//
//    //处理添加
//    public function doAddRate(){
//        $id = intval($_POST ['id']);
//        $data['staging'] = intval($_POST['staging']);
//        if(empty($data['staging'])){
//            $this->error('请填写还款期数');
//        }
//        $data['money'] = $_POST['money'];
//        $preg = "/\d{1,10}(\.\d{1,2})?$/ ";
//        if(!preg_match($preg,$data['money'])||($data['money']<=0)){
//            $this->error('请正确输入金额');
//        }
//        $data['rate'] = $_POST['rate'];
//        if(!preg_match($preg,$data['rate'])||($data['rate']<=0)){
//            $this->error('请正确输入利率');
//        }
//        if($id){
//            //修改
//            $res = D('bank_rate')->where('id='.$id)->save($data);
//        }else{
//            //添加
//            $map['money'] = $data['money'];
//            $map['staging'] = $data['staging'];
//            $map['isDel'] = 0;
//            $list = D('bank_rate')->where($map)->find();
//            if($list){
//                $this->error('已存在相同金额期数的利率，请删除之后再添加');
//            }
//            $res = D('bank_rate')->add($data);
//        }
//        if($res){
//            $this->assign('jumpUrl', U('shop/Pocket/rateList'));
//            $this->success('操作成功');
//        }else{
//            $this->error('操作失败');
//        }
//    }
//
//    //删除利率
//    public function delRate(){
//        $id = intval($_GET ['id']);
//        if(empty($id)){
//            $this->error('错误操作');
//        }
//        $data['isDel'] = 1;
//        $res = D('bank_rate')->where('id='.$id)->save($data);
//        if($res){
//            $this->success('删除成功,请及时添加新的利率');
//        }else{
//            $this->error('删除失败');
//        }
//    }

    //用户借款记录列表
    public function financeList(){
        if($_POST['ctf_id']){
            $_POST['ctf_id'] && $map2['ctf_id'] = array('like', '%' . t($_POST['ctf_id']) . '%');
        }
        if($_POST['realname']){
            $_POST['realname'] && $map2['realname'] = array('like', '%' . t($_POST['realname']) . '%');
        }
        if($map2){
            $ids = D('bank_card')->where($map2)->field('id,realname')->findAll();
            $arr = array();
            foreach($ids as $v){
                $arr[] = $v['id'];
            }
        }
        if($arr){
            $map1['bank_card_id'] = array('in',$arr);
        }
        $list = D('bank_finance')->where($map1)->field('id,bank_card_id,uid,money,staging,ctime,reason,mark,status')->order('id DESC')->findPage(10);
        foreach($list['data'] as &$v){
            $result1 = D('pocket_reason')->where('id='.$v['reason'])->find();
            $v['reason'] = $result1['name'];
            $res = D('bank_card')->where('id='.$v['bank_card_id'])->field('realname,school_id,allow_risk,free_allow_risk,card_no')->find();
            $v['realname'] = $res['realname'];
            //按类型分
            if($v['mark']==1){
                //1000元免息
                $result = D('user')->getField('from_reg','uid='.$v['uid']);
                if($res['free_allow_risk']){
                    $v['allow_risk'] = '风控通过';
                    $v['can_push'] = 1;
                }elseif($res['card_no']&&($result==0)){
                    //不需要风控,有卡号就可以
                    $v['allow_risk'] = '风控未通过';
                    $v['can_push'] = 1;
                }else{
                    $v['allow_risk'] = '风控未通过';
                    $v['can_push'] = 0;
                }
            }else{
                //非一千元免息
                if($res['allow_risk']==1){
                    $v['allow_risk'] = '风控通过';
                    $v['can_push'] = 1;
                }else{
                    $v['allow_risk'] = '风控未通过';
                    $v['can_push'] = 0;
                }

            }



//            if($res['allow_risk']==1){
//                $v['allow_risk'] = '风控通过';
//                $v['can_push'] = 1;  //是否可借款
////                echo 1;
////                dump($list);die;
//            }else{
//                $v['allow_risk'] = '风控未通过';
//                if($res['card_no']&&($v['mark']==1)){
//                    $v['can_push'] = 1;
//                }else{
//                    $v['can_push'] = 0;
//                }
//            }

            $v['school'] = D('school')->getField('title','id='.$res['school_id']);
            //$map['status'] = array('lt',1);
            $map['bank_finance_id'] = $v['id'];
            $dlist = D('bank_finance_detail')->where($map)->limit(1)->find();
             //dump($dlist);die;
            $v['stagmoney'] = $dlist['money']; //每期还款金额

        }

        $this->assign($list);
        $this->display();
    }

    //待还款的纪录
    public function needList(){
        $res = D('bank_finance')->where('status = 1')->field('id')->findAll();
        foreach($res as $val){
            $arr[] = $val['id'];
        }

        $map['bank_finance_id'] = array('in',$arr);
        $map['status'] = array('neq',2);
        $list = D('bank_finance_detail')->where($map)->field('id,money,surp_money,stime,bank_card_id')->findPage(15);
        $str = '**************';
        foreach ($list['data'] as $key => $value){
            $list['data'][$key]['realname'] = D('bank_card')->getField('realname','id='.$value['bank_card_id']);
            $card_no = D('bank_card')->getField('card_no','id='.$value['bank_card_id']);
            $card_no = D('CMB','bank')->decrypt($card_no);
            $list['data'][$key]['card_no'] = substr_replace($card_no,$str,1,14);
            $sid = D('bank_card')->getField('school_id','id='.$value['bank_card_id']);
            $list['data'][$key]['sid'] = D('school')->getField('title','id='.$sid);
        }
        $this->assign($list);
        $this->display();
    }

    //确认用户订单还请
    public function doNeedList(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $data['status'] = 2;
        $data['etime'] = date('Y-m-d H:i:s');
        $res = D('bank_finance_detail')->where('id='.$id)->save($data);
        $bank_finance_id = D('bank_finance_detail')->getField('bank_finance_id','id='.$id);
        if($res){
            $map['bank_finance_id'] = $bank_finance_id;
            $map['status'] = array('neq',2);
            $res1 = D('bank_finance_detail')->where($map)->findAll();
            if($res1){
                //有金额没还完
                $this->success('操作成功');
            }else{
                //该笔订单全部还清
                D('bank_finance')->where('id='.$bank_finance_id)->save($data);
                $money = D('bank_finance')->getField('money','id='.$bank_finance_id);
                $card_id = D('bank_finance')->getField('bank_card_id','id='.$bank_finance_id);
                $surplus_line = D('bank_card')->getField('surplus_line','id='.$card_id);
                $data1['surplus_line'] = $surplus_line + $money;
                D('bank_card')->where('id='.$card_id)->save($data1);
                $this->success('操作成功');
            }

        }else{
            $this->error('操作失败，请重试');
        }
    }

    //还款失败的订单
    public function defaultFinance(){
        $map['status'] = array('gt',2);
        $list = D('bank_finance_detail')->where($map)->field('id,bank_finance_id,money,uid,surp_money,status')->findPage(10);
        //dump($list);die;
        foreach($list['data'] as &$val){
            $val['realname'] = D('bank_card')->getField('realname','uid='.$val['uid']);
            $flist = D('bank_finance')->where('id='.$val['bank_finance_id'])->field('money,staging')->find();
            $val['allMoney'] = $flist['money'];
            $val['staging'] = $flist['staging'];
        }
        $this->assign($list);
        $this->display();
    }

//    //添加不需要风控1000元的学校
//    public function addBankSchool(){
//        $city = '苏州';
//        $cityId = D('citys')->getField('id','city='.$city);
//        $map['cityId'] = $cityId;
//        $map['pid'] = 0;
//        $list = D('school')->where($map)->field('id,title')->findAll();
//        $this->assign('list',$list);
//        $this->display();
//    }
//
//    //处理添加学校
//    public function doAddSchool(){
//        $data['sid'] = intval($_POST['sid']);
//        $data['cate'] = 1;
//        $res = D('bank_school')->where($data)->find();
//        if($res){
//            $this->error('您添加的数据已在列表中，不可重复操作');
//        }else{
//            $res1 = D('bank_school')->add($data);
//            if($res1){
//                $this->success('操作成功');
//            }else{
//                $this->error('操作失败');
//            }
//        }
//    }
//
//    //删除bank_school中的数据
//    public function delSchool(){
//        $id = intval($_GET['id']);
//        if(empty($id)){
//            $this->error('错误操作');
//        }
//        $res = D('bank_school')->where('id='.$id)->delete();
//        if($res){
//            $this->success('操作成功');
//        }else{
//            $this->error('操作失败');
//        }
//    }
//
//    //bank_school列表
//    public function bankSchoolList(){
//        $list = D('bank_school')->findPage(10);
//        foreach($list['data'] as $key=>$val){
//            $list['data'][$key]['school']=D('school')->getField('title','id='.$val['sid']);
//        }
//        $this->assign($list);
//        $this->display();
//    }

    //修改还款失败订单的金额
    public function changeDetailMoney(){
        $id = intval($_POST['id']);
        $money = intval($_POST['surp_money']);
        if($money<=0){
            $this->error('错误操作');
        }
        $dao = D('bank_finance_detail');
        $res = $dao->setField('surp_money', $money, 'id='.$id);
        if ($res) {
            $this->success('修改成功');
        } else {
            $this->error('修改失败，或无变化');
        }
    }

    //代扣
    public function doSurp_money(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $list = D('bank_finance_detail')->where('id='.$id)->find();
        $clist = D('bank_card')->where('uid='.$list['uid'])->field('card_no,realname')->find();
        $res = D('Deduct','bank')->AgentRequest($id,$list['uid'],$list['surp_money'],$clist['card_no'],$clist['realname']);
        if($res['code']==1){
            $data['status']=2;
            D('bank_finance_detail')->where('id='.$id)->save($data);
            $this->success('还款成功');
        }else{
            $this->error('还款失败');
        }
    }

    //订单列表查看用户详情
    public function lookCard(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $list = D('bank_card')->where('id='.$id)->find();
        $arr = array('0'=>'未处理','1'=>'处理中','2'=>'办卡成功','3'=>'办卡失败');          //办卡所有状态
        $res = D('school')->where('id='.$list['school_id'])->field('title')->find();
        $list['school_id'] = $res['title'];              //学校名
        $list['status_detail'] = $arr[$list['status']];     //办卡状态
        $str = '****************';
        $str1 = '**************';
        $list['card_no'] = D('CMB','bank')->decrypt($list['card_no']);
        if($list['card_no']){
            $list['card_no'] = substr_replace($list['card_no'],$str1,1,14);
        }
        $list['ctf_id'] = substr_replace($list['ctf_id'],$str,1,16);
        $this->assign('list',$list);
        $this->display();
    }

    //办卡列表查看用户详情
    public function lookFinance(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $list = D('bank_finance')->where('bank_card_id='.$id)->order('id ASC')->limit(1)->find();
        $list['stagmoney'] = D('bank_finance_detail')->getField('money','bank_finance_id='.$list['id']); //每期还款金额    //办卡状态
        $this->assign('list',$list);
        $this->display();
    }

    //查看驳回原因
    public function lookBackReason(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $list = D('bank_card')->where('id='.$id)->field('back_reason')->find();
        $this->assign('list',$list);
        $this->display();
    }

    //处理借款通过
//    public function agreeBankFinance(){
//        set_time_limit(0);
//        $id = intval($_GET['id']);
//        if(!$id){
//            $this->error('错误操作');
//        }
//        $data['status'] = 1;
//        $res = D('bank_finance')->where('id='.$id)->save($data);
//        $uid = D('bank_finance')->getField('uid','id='.$id);
//        $card_id = D('bank_finance')->getField('bank_card_id','id='.$id);
//        $map['uid'] = $uid;
//        $map['id'] = $card_id;
//        $map['status'] = array('neq',3);
//        $list = D('bank_card')->where($map)->find();
//        if(empty($list)){
//            $this->error('错误操作');
//        }
//        $money = D('bank_finance')->getField('money','id='.$id);
//        $line = D('bank_card')->getField('surplus_line','id='.$card_id);
//        if($money>$line){
//            $this->error('额度不足');
//        }
//        if($res){
//            //调用代发接口
//            $res1 = D('Lend','bank')->DCPAYMNT($id,$uid,$money,$list['card_no'],$list['card_account']);
//            if(($res1['code']==1)||($res1['code']==3)){
//                $card_id = D('bank_finance')->getField('bank_card_id','id='.$id);
//                $money_line = D('bank_card')->getField('surplus_line','id='.$card_id);
//                $surplus_line = $money_line - $money;
//                D('bank_card')->setField('surplus_line',$surplus_line,'id='.$card_id);
//                //修改分期表，以及分期详情表的stime
//                D('BankFinance')->editTime($id);
//                $this->success('操作成功');
//            }else{
//                $data['status'] = 0;
//                D('bank_finance')->where('id='.$id)->save($data);
//                $this->error('发款失败');
//            }
//        }else{
//            $this->error('操作失败');
//        }
//    }

    //查看详情 处理借款失败
    public function backBankFinance(){
        $id = intval($_GET['id']);
        if(!$id){
            $this->error('错误操作');
        }
        $list = D('bank_finance')->where('id='.$id)->find();
        if(empty($list)){
            $this->error('暂无数据');
        }
        $list['stagMoney'] = D('bank_finance_detail')->getField('money','bank_finance_id='.$id);
        $this->assign('list',$list);
        $this->display();
    }

    //处理驳回订单
    public function doBackBank(){
        $id = intval($_POST ['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $data['status'] = intval($_POST['status']);
        $data['back_reason'] = t($_POST['back_reason']);
        if(($data['status']==4) && (empty($data['back_reason']))){
            $this->error('请填写驳回原因');
        }elseif(($data['status']!==4)&&$data['back_reason']){
            $this->error('请正确填写订单状态和驳回原因');
        }
    }

    //渠道列表
    public function channelList(){
        $list = D('bank_channel')->order('id DESC')->findPage(10);
        //dump($_SERVER);die;
        foreach($list['data'] as $key=>$val){

            $real = D('CMB','bank')->encrypt($val['real']);
            $real = urlencode(str_replace('+', '%2B', $real));
            $list['data'][$key]['url'] = $_SERVER['SERVER_NAME'].'/index.php?app=shop&mod=PocketShop&act=bankPrice&channel='.$real;
        }
        $this->assign($list);
        $this->display();
    }

    //修改或添加渠道
    public function editChannel(){
        $id = intval($_GET['id']);
        if($id){
            //查看
            $list = D('bank_channel')->where('id='.$id)->find();
            $this->assign('list',$list);
        }
        $this->display();
    }

    //处理修改或添加
    public function doEditChannel(){
        $id = intval($_POST['id']);
        $real = intval($_POST['real']);

        if($_POST['mark']==1){
            //个人且没传数据
            if(empty($real)){
            $this->error('错误操作');
            }
            $data['real'] = $real;

        }else{
           //平台

            $res = D('bank_channel')->count();
            $num = $res+1;
            $var=sprintf("%07d", $num);
            $data['real'] = 'C'.$var;
        }

        if($id){
            //修改
            $result = D('bank_channel')->where('id='.$id)->save($data);
        }else{
            //添加
            $result = D('bank_channel')->add($data);
        }
        if($result){
            $this->assign('jumpUrl', U('shop/Pocket/channelList'));
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

    //后台查看协议
    public function lookXY(){
        $xmark = intval($_GET['xmark']);     //标记是提款协议还是服务协议
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('操作失败');
        }
        $list = D('bank_finance')->where('id='.$id)->field('money,staging,bank_card_id,mark')->find();
        $clist = D('bank_contract')->where('finance_id='.$id)->find();
        $blist = D('bank_card')->where('id='.$list['bank_card_id'])->field('realname,mobile,ctf_id')->find();
        if(!($list&&$clist&&$blist)){
            $this->error('获取数据失败');
        }
        $str = '****************';
        $blist['ctf_id'] = substr_replace($blist['ctf_id'],$str,1,16);
        $time = strtotime($clist['ctime']);
        $data['ctime_y'] = date('Y',$time);
        $data['ctime_m'] = date('m',$time);
        $money = intval($list['money']);
        $data['stagMoney'] = getKxService($money);

        $data['puMoney'] = getStagingPrice1($money,$list['staging'])-getMouPrice($money,$list['staging'])-$data['stagMoney'];
        $data['stagMoney'] = getMouPrice($money,$list['staging']) + $data['stagMoney'];
        //$data['ctime_m1'] = date('m') + 1;
        $data['ctime_d'] = date('d',$time);
        $etime = strtotime('+'.$list['staging'].' month',$time);
        $data['etime_y'] = date('Y',$etime);
        $data['etime_m'] = date('m',$etime);
        $data['etime_d'] = date('d',$etime);
        $this->assign('list',$list);
        $this->assign('clist',$clist);
        $this->assign('blist',$blist);
        if($xmark==1){
            //提款协议
            $data['contract_id'] = $clist['ptm'];
            if($list['mark']==1){
                //一千元免息
                $this->assign('data',$data);
                $this->display('m_xy4');

            }else{
                //非一千元免息
                $this->assign('data',$data);
                $this->display('m_xy2');
            }
        }else{
            //服务协议
            $data['contract_id'] = $clist['pfm'];
            if($list['mark']==1){
                //一千元免息
                $this->assign('data',$data);
                $this->display('m_xy3');
            }else{
                //非一千元免息
                $this->assign('data',$data);
                $this->display('m_xy1');
            }
        }
    }

    //风控看学生详细信息
    public function studentInfo(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $ulist = D('bank_card')->where('id='.$id)->find();
        $uid = $ulist['uid'];
        $userinfo = D('bank_user_info')->where('bank_id='.$ulist['id'])->find();
        if($userinfo['education']==1){
            $userinfo['education']='专科';
        }else{
            $userinfo['education']='本科';
        }
        $ctf_id = $ulist['ctf_id'];
        $num = substr($ctf_id,-2,1);
        if(($num%2)==1){
            $ulist['sex'] = '男';
        }else{
            $ulist['sex'] = '女';
        }
        $ulist['school'] = D('school')->getField('title','id='.$ulist['school_id']);
        $ulist['department'] = D('school')->getField('title','id='.$userinfo['sid1']);   //院系
        $ulist['major'] = D('user')->getField('major','uid='.$ulist['uid']);     //专业
        $ulist['grade'] = D('user')->getField('year','uid='.$ulist['uid']);     //年级
        $email = D('user')->getField('email','uid='.$uid);
        $arr = explode('@',$email);
        $ulist['no'] = $arr[0];    //学号
        if($ulist['card_no']){
            $ulist['card_no'] = D('CMB','bank')->decrypt($ulist['card_no']);
        }
        //订单
        $map['status'] = array('in','0,1');
        $map['bank_card_id'] = $id;
        $list = D('bank_finance')->where($map)->field('id,money,staging,stime')->findAll();

        $arr['contract'] = D('bank_credit')->getField('contract','uid='.$uid);
        $count = 0;
        $sum = 0;
        foreach($list as $key=>$val){
            $count += $val['money'];
            $sum += 1;
            $list[$key]['ptm'] = D('bank_contract')->getField('ptm','finance_id='.$val['id']);
            $map1['status'] = array('in','0,1');
            $map1['bank_finance_id'] = $val['id'];
            $num2 = D('bank_finance_detail')->where($map1)->count();
            $money = intval($val['money']);
            $benPrice = getMouPrice($money,$val['staging']);
            $list[$key]['remainMoney'] = $benPrice*$num2;       //剩余的钱
            $map1['status'] = 4;
            $num3 = D('bank_finance_detail')->where($map1)->count();
            $list[$key]['overdue'] = $num3;       //逾期情况
        }
//        dump($list);die;
        $arr['count'] = $count;
        $arr['sum'] = $sum;
        $this->assign('arr',$arr);
        $this->assign('info',$userinfo);
        $this->assign('ulist',$ulist);
        $this->assign('list',$list);
        $this->display();
    }

    //订单确认还款
    public function confirmRepayment(){
        $id = intval($_GET['id']);
        if(empty($id)){
            $this->error('错误操作');
        }
        $res = D('BankFinance')->editOrderStatus($id,2);
        if($res){
            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }
    }
    
    //后台订单取消 删除
    public function deleBankList(){
        $id = intval($_GET['id']);
        if($id){
            $res = D('BankFinance')->deleteList($id);
            if($res == 1){
                $this->success('删除成功');
            }elseif($res == 2){
                $this->error('订单删除出错，请联系技术部');
            }else{
                $this->error('操作失败，请重试');
            }
        }else{
            $this->error('错误操作');
        }
    }


}

?>

