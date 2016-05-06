<?php

/**
 * 爱心校园
 *
 * @version $id$
 * @copyright 2013-2015 张晓军
 * @author 张晓军
 * @license PHP Version 5.3
 */
import('home.Action.PubackAction');

class LuckyAction extends PubackAction {

    public function index() {
        $list = M('lucky_product')->where('status=1')->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->assign('types', getLuckyTypes());
        $this->display();
    }
    
    //待审核商品列表
    public function audit(){
        $list = M('lucky_product')->where('status=0')->order('id DESC')->findPage(10);
        $this->assign($list);
        $this->assign('types', getLuckyTypes());
        $this->display();
    }
    
    //通过审核
    public function pass(){
        $id = intval($_POST['id']);
        $data['status']=1;
       /*  $rel = M('lucky_product')->setField('status',1,"id=$id"); 20151222杨军注释*/
        $condition['id']=$id;  
     	$res=M('lucky_product')->where($condition)->save($data);
     	if($res){
        $this->success('审核通过成功'); 
     	}
    }
    
    //驳回
    public function reject(){
        $id = intval($_POST['id']);
        $dao = model('LuckyProduct');
        $res = $dao->delProduct($id);
        if ($res) {
            $this->success('驳回成功');
        } else {
            $this->error($dao->getError());
        }
    }
    

    public function editLucky(){
        $id = intval($_GET['id']);
        $provs = M('province')->findAll();
        $this->assign('provs', $provs);
        if ($id) {
            $obj = M('lucky_product')->where("id={$id}")->find();
            if(!$obj){
                $this->error('商品不存在');
            }
            if($obj['type']==3){
                $obj['ygName'] = M('shop_product')->getField('name', 'id='.$obj['ygid']);
            }
            if($obj['etime']==3123456789){
                $obj['etime']=0;
            }
            $this->assign($obj);
            $this->assign('areas',D('FundEventSchool','fund')->editbarSchool($id));
        }
        $this->display();
    }

    public function doEditLucky(){
        $dao = model('LuckyProduct');
        $res = $dao->editProduct();

        if ($res) {
            $this->assign('jumpUrl', U('home/Lucky/index'));
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }

    public function delProduct(){
        $id = intval($_POST['id']);
        $dao = model('LuckyProduct');
        $res = $dao->delProduct($id);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error($dao->getError());
        }
    }

    public function zeroProduct(){
        $id = intval($_POST['id']);
        $dao = model('LuckyProduct');
        $res = $dao->zeroProduct($id);
        if ($res) {
            $this->success('操作成功');
        } else {
            $this->error($dao->getError());
        }
    }

    public function yy() {
        $list = model('LuckyYy')->getYyList();
        $this->assign($list);
        $this->assign('types', getLuckyTypes());
        $this->display();
    }

    public function editYy(){
        $this->assign('yyact', 'add');
        $pid = intval($_GET['pid']);
        if ($pid) {
            $obj = M('lucky_yy')->where("pid={$pid}")->find();
            $this->assign($obj);
            $this->assign('yyact', 'edit');
        }
        $this->assign('costs', array(0,1,2,5));
        $this->display();
    }

    public function doEditYy(){
        $dao = model('LuckyYy');
        $res = $dao->editYy();
        if ($res) {
            $this->assign('jumpUrl', U('home/Lucky/yy'));
            $this->success('添加成功');
        } else {
            $this->error($dao->getError());
        }
    }

    public function delYy(){
        $id = intval($_POST['id']);
        $dao = model('LuckyYy');
        $res = $dao->delYy($id);
        if ($res) {
            $this->success('删除成功');
        } else {
            $this->error($dao->getError());
        }
    }

    public function zj(){
        if (!empty($_POST)) {
            $_SESSION['admin_lucky_zj'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_lucky_zj']);
        } else {
            unset($_SESSION['admin_lucky_zj']);
        }
        $pid = intval($_POST['pid']);
        $map = array();

        if(!empty($_POST['mon1']))
        {
            $sTime = strtotime($_POST['mon1']);
        }
        if(!empty($_POST['mon2']))
        {
            $eTime = strtotime($_POST['mon2']);
        }
        if($sTime && $eTime)
        {
            $map['ctime'] =array('BETWEEN',array($sTime,$eTime));
        }
        if($pid>0){
            $map['pid'] = $pid;
        }
        $list = M('lucky_zj')->where($map)->order('id DESC')->findPage(10);
        foreach ($list['data'] as &$value) {
            $uid = $value['uid'];
            $user = D('User', 'home')->getUserByIdentifier($uid);
            $value['realname'] = $user['realname'];
            $value['xh'] = getUserEmailNum($user['email']);
        }
        $this->assign($list);
        $this->display();
    }

    public function coupon(){
        $lucky_id = intval($_REQUEST['lucky_id']);
        if ($lucky_id<1){
            $this->error('ID错误');
        }
        $map['lucky_id'] = $lucky_id;
        $list = M('LuckyTicket')->where($map)->findPage();
        $this->assign($list);
        $this->display();
    }


}

?>
