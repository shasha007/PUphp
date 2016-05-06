<?php

class DonateAction extends Action {

    public function _initialize() {
        $this->error('爱心校园已关闭');
        $this->setTitle('爱心校园');
    }

    public function index() {
        $sid = $this->user['sid'];
        $locid = model('Schools')->getCityId($sid);
        $loid = model('Schools')->getProvId($sid);
        if ($_GET['provinceId']) {
            $_SESSION['donate_searchSchool']['provinceId'] = intval($_GET['provinceId']);
            unset($_SESSION['donate_searchSchool']['cityId']);
            unset($_SESSION['donate_searchSchool']['sid']);
            unset($_SESSION['donate_searchSchool']['sid1']);
        } else {
            $_SESSION['donate_searchSchool']['provinceId'] = $loid;
        }
        if ($_GET['cityId']) {
            $_SESSION['donate_searchSchool']['cityId'] = intval($_GET['cityId']);
            unset($_SESSION['donate_searchSchool']['sid']);
            unset($_SESSION['donate_searchSchool']['sid1']);
        }
        if ($_GET['sid']) {
            $_SESSION['donate_searchSchool']['sid'] = intval($_GET['sid']);
            unset($_SESSION['donate_searchSchool']['sid1']);
        }
        if ($_GET['sid1']) {
            $_SESSION['donate_searchSchool']['sid1'] = intval($_GET['sid1']);
        }

        $province = M('province')->getField('title', 'id=' . $loid);
        $provinceType = "'" . "provinceId" . "'";
        $string = '<a href ="javascript:void(0)" onClick="area(' . $provinceType . ')">' . $province . '</a>';
        $city = '城 市';
        $cityType = "'" . "cityId" . "'";
        $string.='  |   <a href ="javascript:void(0)" onClick="area(' . $cityType . ')">' . $city . '</a>';
        if ($_SESSION['donate_searchSchool']['provinceId']) {
            $map['provinceId'] = $_SESSION['donate_searchSchool']['provinceId'];
            $name = M('province')->getField('title', 'id=' . $map['provinceId']);
            $string = '<a href ="javascript:void(0)" onClick="area(' . $provinceType . ')">' . $name . '</a>';
            $string.='  |   <a href ="javascript:void(0)" onClick="area(' . $cityType . ')">' . $city . '</a>';
        }
        if ($_SESSION['donate_searchSchool']['cityId']) {
            $map['cityId'] = $_SESSION['donate_searchSchool']['cityId'];
            $name = M('citys')->getField('city', 'id=' . $map['cityId']);
            $cityType = "'" . "sid" . "'";
            $school = '学 校';
            $schoolType = "'" . "sid" . "'";
            $string = str_replace($city, $name, $string);
            $string.='  |   <a href ="javascript:void(0)" onClick="area(' . $schoolType . ')">' . $school . '</a>  ';
        }
        if ($_SESSION['donate_searchSchool']['sid']) {
            $map['sid'] = $_SESSION['donate_searchSchool']['sid'];
            $name = M('school')->getField('title', 'id=' . $map['sid']);
            $partType = "'" . "sid1" . "'";
            $part = '院 系';
            $string.='  |   <a href ="javascript:void(0)" onClick="area(' . $partType . ')">' . $part . '</a>  ';
            $string = str_replace($school, $name, $string);
        }
        if ($_SESSION['donate_searchSchool']['sid1']) {
            $map['sid1'] = $_SESSION['donate_searchSchool']['sid1'];
            $name = M('school')->getField('title', 'id=' . $map['sid1']);
            $string = str_replace($part, $name, $string);
        }

        if ($_GET['cat'] == 'all') {
            unset($_SESSION['donate_searchCat']['cat']);
        } elseif ($_GET['cat']) {
            $_SESSION['donate_searchCat']['cat'] = intval($_GET['cat']);
        }
        if ($_GET['price'] == 'all') {
            unset($_SESSION['donate_searchPrice']['price']);
        } elseif ($_GET['price']) {
            $_SESSION['donate_searchPrice']['price'] = intval($_GET['price']);
        }
        if ($_SESSION['donate_searchCat']['cat']) {
            $map['catId'] = $_SESSION['donate_searchCat']['cat'];
        }
        if ($_SESSION['donate_searchPrice']['price']) {
            $map['price'] = $_SESSION['donate_searchPrice']['price'];
        }
        $map['isDel'] = 0;
        $map['status'] = 2;
        $map['buyer'] = 0;
        $list = D('DonateProduct')->where($map)->findPage(12);
        $this->assign($list);
        $this->assign('right', $this->_right());
        $this->assign('inFund', M('donate_love_all_fund')->getField('allfund', 'type=1'));
        $this->assign('outFund', M('donate_love_all_fund')->getField('allfund', 'type=2'));
        $this->assign('catList', M('donate_cat')->findAll());
        $this->assign('string', $string);
        $this->display();
    }

    public function editDonate() {
        $this->assign('type', 'add');
        $id = (int) $_REQUEST['id'];
        if ($id) {
            $this->assign('type', 'edit');
            $map['id'] = $id;
            $obj = M('donate_product')->where($map)->find();
            if (!$obj) {
                $this->error('无法找到对象');
            }
            if ($obj['status'] == 2) {
                $this->error('审核通过,无法修改');
            }
            $opt = M('donate_product_opt')->field('content,imgs')->where('product_id=' . $id)->find();
            $opt['imgs'] = unserialize($opt['imgs']);
            $this->assign($opt);
            $this->assign($obj);
        }
        $group = M('group_member')->where('uid=' . $this->mid)->field('gid')->findAll();
        if (count($group) > 0) {
            $daogroup = M('group');
            foreach ($group as $k => $v) {
                $group[$k]['name'] = $daogroup->getField('name', 'id = ' . $v['gid']);
            }
            $this->assign('group', $group);
        }
        $this->assign('catList', M('donate_cat')->findAll());
        $this->display();
    }

    public function doEditDonate() {
        $id = intval($_REQUEST['id']);
        if (empty($id)) {
            $this->_insertDonate();
        } else {
            $uid = D('DonateProduct')->getField('uid', 'id =' . $id);
            if ($uid != $this->mid) {
                $this->error('您无权操作');
            }
            $status = D('DonateProduct')->getField('status', 'id =' . $id);
            if ($status == 2) {
                $this->error('审核通过,无法修改');
            }
            $this->_updateDonate($id);
        }
    }

    private function _insertDonate() {
        $data = $this->_getDonateData();
        $data['uid'] = $this->mid;
//        var_dump($data);die;
        $res = D('DonateProduct')->addProduct($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Donate/index'));
            $this->success('添加成功,请等待审核');
        } else {
            $this->error('添加失败');
        }
    }

    private function _updateDonate($id) {
        $data = $this->_getDonateData();
        $data['id'] = $id;
        $res = D('DonateProduct')->updateProduct($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Donate/index'));
            $this->success('修改成功,请等待审核');
        } else {
            $this->error('修改失败');
        }
    }

    private function _getDonateData() {
        //参数合法性检查
        $required_field = array(
            'title' => '物品名称',
            'price' => '物品价格',
            'content' => '物品详情',
            'catId' => '物品分类',
            'contact' => '联系人',
            'mobile' => '联系电话',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }
        if (mb_strlen($_POST['title'], 'utf8') > 30) {
            $this->error('标题必须在30个字内');
        }
        $info = tsUploadImg();
        if ($info['status']) {
            $data['pic'] = $info['info'][0]['savepath'] . $info['info'][0]['savename'];
        } elseif ($info['info'] != '没有选择上传文件') {
            $this->error($info['info']);
        }
        if (intval($_REQUEST['price']) == 1) {
            $data['price'] = 1;
        } else if (intval($_REQUEST['price']) == 3) {
            $data['price'] = 3;
        } else if (intval($_REQUEST['price']) == 5) {
            $data['price'] = 5;
        }
        $data['title'] = t($_REQUEST['title']);
        $data['content'] = t(h($_REQUEST['content']));
        $data['catId'] = intval($_REQUEST['catId']);
        $data['contact'] = t($_REQUEST['contact']);
        $data['mobile'] = t($_REQUEST['mobile']);
        $data['cityId'] = model('Schools')->getCityId($this->user['sid']);
        $data['provinceId'] = model('Schools')->getProvId($this->user['sid']);
        $data['sid'] = $this->user['sid'];
        $data['sid1'] = $this->user['sid1'];
        $data['groupId'] = intval($_REQUEST['groupId']);
        $data['groupName'] = '';
        if($data['groupId']>0){
            $data['groupName'] = M('group')->getField('name', 'id='.$data['groupId']);
        }
        $data['imgs'] = serialize($_REQUEST['imgs']);
        return $data;
    }

    public function detail() {
        $id = intval($_GET['id']);
        $dao = D('DonateProduct');
        $cat = M('donate_cat')->findAll();
        $cat = orderArray($cat, 'id');
        $obj = $dao->donateDetail($id);
        if (!$obj) {
            $this->error('商品不存在！或已删除');
        }
        $this->assign('city', M('citys')->getField('city','id='.$obj['cityId']));
        $this->assign('cat', $cat);
        $this->assign($obj);
        $this->display();
    }

    public function buy() {
        $id = intval($_GET['id']);
        $dao = D('DonateProduct');
        if (!$id) {
            $this->error('错误的链接');
        }
        $map['id'] = $id;
        $obj = $dao->where($map)->find();
        if (!$obj) {
            $this->error('商品不存在！或已删除');
        }
        if ($obj['status'] != 2) {
            $this->error('该商品尚未通过审核');
        }
        if ($obj['buyer'] > 0) {
            $this->error('该商品已售出');
        }
        $this->assign($obj);
        $this->setTitle($obj['name']);
        $this->display();
    }

    public function payment() {
        $id = intval($_POST['id']);
        $res = D('DonateProduct')->buy($this->mid, $id);
        echo json_encode($res);
        exit;
    }

    public function citySchool() {
        $type = $_GET['type'];
        if ($type == 'provinceId') {
            $list = M('province')->findALl();
        } else if ($type == 'cityId') {
            $list = M('citys')->where('pid = ' . $_SESSION['donate_searchSchool']['provinceId'])->field('id,city as title')->findALl();
        } else if ($type == 'sid') {
            $list = M('school')->where('cityId =' . $_SESSION['donate_searchSchool']['cityId'])->field('id,title')->findALl();
        } else if ($type == 'sid1') {
            $map['pid'] = $_SESSION['donate_searchSchool']['sid'];
            $list = M('school')->where($map)->field('id,title')->findALl();
        }
        $this->assign('list', $list);
        $this->assign('type', $type);
        $this->display();
    }

    private function _right() {
        $map['isDel'] = 0;
        $map['buyer'] = array('gt', 0);
        $right = D('DonateProduct')->where($map)->field('id,title,uid,pic,buytime,groupName')->order('id DESC')->limit(8)->findAll();
        return $right;
    }

}

?>
