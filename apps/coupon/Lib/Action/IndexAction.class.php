<?php

class IndexAction extends BaseAction {

    var $category;
    var $school;

    public function _initialize() {
        parent::_initialize();
        global $ts;
    	$ts['site']['page_title'] = 'PocketUni-消费频道';
        $this->category = D('Category');
        $this->school = D('School');
        $this->_getTop();
    }

    public function index() {
        $category = $this->get('category');

        $list = array();
        foreach ($category as $value) {
            $order = 'id DESC';
            $map['isDel'] = 0;
            $map['is_hot'] = 1;
            $map['cid'] = $value['id'];
            $coupon = array('cid' => $value['id'], 'cName' => $value['title']);
            $coupon['hot'] = D('Coupon')->where($map)->order($order)->limit(5)->findAll();
            $list[] = $coupon;
        }
        $this->assign("list", $list);
        $this->assign(array('cid' => 0, 'sid' => 0));
        $this->display();
    }

    //获取页面头部 分类，校区 信息
    private function _getTop() {
        //分类
        $category = $this->_getCategory();
        $this->assign("category", $category);
        $total = 0;
        foreach ($category as $value) {
            $total += $value['total'];
        }
        $this->assign("total", $total);
        //校区
        $this->assign("school", $this->_getSchool());
    }

    private function _getCategory() {
        $db_prefix = C('DB_PREFIX');
        return M()->table("{$db_prefix}coupon_category AS a LEFT JOIN {$db_prefix}coupon AS b ON a.id=b.cid")
                        ->field('a.*, IF(ISNULL(b.isDel),0,count(*)) as total')->group('a.id')->where('b.isDel = 0 OR ISNULL(b.isDel)')->order('a.id')->findAll();
    }

    private function _getSchool() {
        $db_prefix = C('DB_PREFIX');
        return M()->table("{$db_prefix}coupon_school AS a LEFT JOIN {$db_prefix}coupon AS b ON a.id=b.sid")
                        ->field('a.*, IF(ISNULL(b.isDel),0,count(*)) as total')->group('a.id')->where('b.isDel = 0 OR ISNULL(b.isDel)')->order('a.id')->findAll();
    }

    public function couponList() {
        $cid = intval($_GET['cid']);
        $sid = intval($_GET['sid']);
        $this->assign('cid',$cid);
        $this->assign('cTitle',  $this->_getNameById($cid, $this->get('category')));
        $this->assign('sid',$sid);
        $this->assign('sTitle',  $this->_getNameById($sid, $this->get('school')));

        $map['isDel'] = 0;
        if($cid > 0){
            $map['cid'] = $cid;
        }
        if($sid > 0){
            $map['sid'] = $sid;
        }
        $list = M('coupon')->where($map)->order('id DESC')->findPage(20);
        $this->assign($list);
        $this->display();
    }

    private function _getNameById($id,$arr){
        if($id == 0){
            return '全部';
        }
        foreach ($arr as $value) {
            if($value['id'] == $id){
                return $value['title'];
            }
        }
        return '错误选择';
    }

    public function details() {
        $id = intval($_REQUEST['id']);
        $dao = M('coupon');
        $obj = $dao->where("`id`={$id} AND isDel=0")->find();
        if (!$obj) {
            $this->error('优惠券不存在或已被删除！');
        }
        $category = $this->category->find($obj['cid']);
        $this->assign('cTitle', $category['title']);
        $school = $this->school->find($obj['sid']);
        $this->assign('sTitle', $school['title']);
        $this->assign('cid', $obj['cid']);
        $this->assign('sid', $obj['sid']);
        $this->assign('obj', $obj);
        $dao->setInc('readCount', 'id=' . $id);
        $this->display();
    }

}

?>