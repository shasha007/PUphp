<?php

import('home.Action.PubackAction');

class AdminAction extends PubackAction {

    public $route;

    public function _initialize() {
        parent::_initialize();
        $this->route = D('Travel');
    }

    public function index() {
        if (!empty($_POST)) {
            $_SESSION['admin_travel_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_travel_search']);
        } else {
            unset($_SESSION['admin_travel_search']);
        }

        $_POST['title'] = t(trim($_POST['title']));
        $_POST['title'] && $map['title'] = array('like', "%" . $_POST['title'] . "%");
        $_POST['cat'] && $map['catId'] = intval($_POST['cat']);
        $_POST['area'] && $map['areaId'] = intval($_POST['area']);
        $this->assign($_POST);
        $catList = M('travel_cat')->findAll();
        $areaList = M('travel_area')->findAll();
        $this->assign('areaList', $areaList);
        $this->assign('catList', $catList);
        $this->assign($this->route->routeList($map));
        $this->display();
    }

    //添加景点
    public function addRoute() {
        $cat = M('travel_cat')->findAll();
        $area = M('travel_area')->findAll();
        $this->assign('area', $area);
        $this->assign('cat', $cat);
        $this->display();
    }

    public function doAddRoute() {
        //参数合法性检查
        $required_field = array(
            'title' => '景点名称',
            'cost' => '报名费用',
            'costExplain' => '费用说明',
            'address' => '景点地址',
            'area' => '所在城市',
            'cat' => '景点类别',
            'sTime' => '景点开始时间',
            'eTime' => '景点结束时间',
            'deadline' => '截止报名时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 20) {
            $this->error('景点名称最大20个字！');
        }
        $textarea = t($_POST['description']);
        if (mb_strlen($textarea, 'UTF8') <= 0 || mb_strlen($textarea, 'UTF8') > 200) {
            $this->error("景点简介1到200字!");
        }
        if($_POST['cost2']&&!$_POST['costExplain2']){
              $this->error('第二个费用说明不能为空');
        }
        $map['deadline'] = $this->_pDate($_POST['deadline']);
        $map['sTime'] = $this->_pDate($_POST['sTime']);
        $map['eTime'] = $this->_pDate($_POST['eTime']);
        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于结束时间');
        }
        if (empty($_FILES['cover']['name'])) {
            $this->error('请上传景点logo');
        }
        //得到上传的图片
        $config = getPhotoConfig();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $images = X('Xattach')->upload('travel', $options);
        if (!$images['status'] && $images['info'] != '没有选择上传文件') {
            $this->error($images['info']);
        }
        $map['uid'] = $this->mid;
        $map['areaId'] = intval($_POST['area']);
        $map['title'] = $title;
        $map['address'] = t($_POST['address']);
        $map['limitCount'] = intval(t($_POST['limitCount']));
        $map['catId'] = intval($_POST['cat']);
        $map['contact'] = t($_POST['contact']);
        $map['cost'] = intval($_POST['cost']);
        $map['costExplain'] = t($_POST['costExplain']);
        $map['cost2'] = intval($_POST['cost2']);
        $map['costExplain2'] = t($_POST['costExplain2']);
        $map['description'] = $textarea;
        if ($addId = $this->route->doAddRoute($map, $images)) {
            $this->success($this->appName . '创建成功');
        } else {
            $this->error($this->appName . '添加失败');
        }
    }

    public function editRoute() {
        $rid = intval($_REQUEST['id']);
        if ($rid <= 0) {
            $this->error("错误的访问页面，请检查链接");
        }
        $map['isDel'] = 0;
        $map['id'] = $rid;
        if ($result = $this->route->where($map)->find()) {
            $this->assign('route', $result);
        } else {
            $this->error('该景点不存在或已删除');
        }
        $cat = M('travel_cat')->findAll();
        $area = M('travel_area')->findAll();
        $this->assign('area', $area);
        $this->assign('cat', $cat);
        $this->display();
    }

    public function doEditRoute() {
        $id = intval($_POST['id']);
        if (!$obj = $this->route->where(array('id' => $id))->find()) {
            $this->error('景点不存在或已删除');
        }
        //参数合法性检查
        $required_field = array(
            'title' => '景点名称',
            'cost' => '报名费用',
            'costExplain' => '费用说明',
            'address' => '景点地址',
            'area' => '所在城市',
            'cat' => '景点类别',
            'sTime' => '景点开始时间',
            'eTime' => '景点结束时间',
            'deadline' => '截止报名时间',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_POST[$k]))
                $this->error($v . '不可为空');
        }

        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 20) {
            $this->error('景点名称最大20个字！');
        }
        $textarea = t($_POST['description']);
        if (mb_strlen($textarea, 'UTF8') <= 0 || mb_strlen($textarea, 'UTF8') > 200) {
            $this->error("景点简介1到200字!");
        }
        $map['deadline'] = $this->_pDate($_POST['deadline']);
        $map['sTime'] = $this->_pDate($_POST['sTime']);
        $map['eTime'] = $this->_pDate($_POST['eTime']);
        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于结束时间');
        }
        //得到上传的图片
        $config = getPhotoConfig();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $images = X('Xattach')->upload('travel', $options);
        if (!$images['status'] && $images['info'] != '没有选择上传文件') {
            $this->error($images['info']);
        }
        $map['uid'] = $this->mid;
        $map['areaId'] = intval($_POST['area']);
        $map['title'] = $title;
        $map['address'] = t($_POST['address']);
        $map['limitCount'] = intval(t($_POST['limitCount']));
        $map['catId'] = intval($_POST['cat']);
        $map['contact'] = t($_POST['contact']);
        $map['cost'] = intval($_POST['cost']);
        $map['costExplain'] = t($_POST['costExplain']);
        $map['cost2'] = intval($_POST['cost2']);
        $map['costExplain2'] = t($_POST['costExplain2']);
        $map['description'] = $textarea;
        if ($addId = $this->route->doEditRoute($map, $images, $obj)) {
            $this->success($this->appName . '编辑成功');
        } else {
            $this->error($this->appName . '编辑失败');
        }
    }

    public function put_route_to_recycle() {
        $dao = M('travel');
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = $dao->setField('isDel', 1, 'id IN ' . t($gid)); // 通过审核
        if ($res) {
            if (strpos($_POST ['gid'], ',')) {
                echo 1;
            } else {
                echo 2;
            }
        } else {
            echo 0;
        }
    }

    private function _pDate($date) {
        $date_list = explode(' ', $date);
        list( $year, $month, $day ) = explode('-', $date_list[0]);
        list( $hour, $minute, $second ) = explode(':', $date_list[1]);
        return mktime($hour, $minute, $second, $month, $day, $year);
    }

    public function catList() {
        $catList = M('travel_cat')->findAll();
        $this->assign('catList', $catList);
        $this->display();
    }

    public function editRouteTab() {
        $id = intval($_GET['id']);
        if ($id) {
            $name = M('travel_cat')->getField('name', "id={$id}");
            $this->assign('id', $id);
            $this->assign('name', $name);
        }
        $this->display();
    }

    public function doEditType() {
        $_POST['id'] = intval($_POST['id']);
        $_POST['name'] = t($_POST['name']);
        $_POST['name'] = preg_replace("/[ ]+/si", "", $_POST['name']);
        if (empty($_POST['name'])) {
            echo -2;
        }
        $daotype = M('travel_cat');
        $name = $daotype->where(array('name' => t($_POST['name'])))->getField('name');
        if ($name !== null) {
            echo 0; //分类名称重复
        } else {
            if ($result = $daotype->save($_POST)) {
                echo 1; //更新成功
            } else {
                echo -1;
            }
        }
    }

    public function doAddType() {
        $isnull = preg_replace("/[ ]+/si", "", t($_POST['name']));
        $daotype = M('travel_cat');
        $name = $daotype->where(array('name' => $isnull))->getField('name');
        if (empty($isnull)) {
            echo -2;
        }
        if ($name !== null) {
            echo 0;
        } else {
            if ($result = $daotype->add($_POST)) {
                echo 1;
            } else {
                echo -1;
            }
        }
    }

    public function delCat() {
        $dao = M('travel_cat');
        $gid = is_array($_POST ['gid']) ? implode(',', $_POST ['gid']) : $_POST ['gid']; // 判读是不是数组
        $map['id'] = array('IN', $gid);
        $res = $dao->where($map)->delete();
        if ($res) {
            if (strpos($_POST ['gid'], ',')) {
                echo 1;
            } else {
                echo 2;
            }
        } else {
            echo 0;
        }
    }

    public function areaList() {
        $areaList = M('travel_area')->findAll();
        $this->assign('areaList', $areaList);
        $this->display();
    }

    public function editAreaTab() {
        $id = intval($_GET['id']);
        if ($id) {
            $name = M('travel_area')->getField('name', "id={$id}");
            $this->assign('id', $id);
            $this->assign('name', $name);
        }
        $this->display();
    }

    public function doEditArea() {
        $_POST['id'] = intval($_POST['id']);
        $_POST['name'] = t($_POST['name']);
        $_POST['name'] = preg_replace("/[ ]+/si", "", $_POST['name']);
        if (empty($_POST['name'])) {
            echo -2;
        }
        $daotype = M('travel_area');
        $name = $daotype->where(array('name' => t($_POST['name'])))->getField('name');
        if ($name !== null) {
            echo 0; //分类名称重复
        } else {
            if ($result = $daotype->save($_POST)) {
                echo 1; //更新成功
            } else {
                echo -1;
            }
        }
    }

    public function doAddArea() {
        $isnull = preg_replace("/[ ]+/si", "", t($_POST['name']));
        $daotype = M('travel_area');
        $name = $daotype->where(array('name' => $isnull))->getField('name');
        if (empty($isnull)) {
            echo -2;
        }
        if ($name !== null) {
            echo 0;
        } else {
            if ($result = $daotype->add($_POST)) {
                echo 1;
            } else {
                echo -1;
            }
        }
    }

    public function delArea() {
        $dao = M('travel_area');
        $gid = is_array($_POST ['gid']) ? implode(',', $_POST ['gid']) : $_POST ['gid']; // 判读是不是数组
        $map['id'] = array('IN', $gid);
        $res = $dao->where($map)->delete();
        if ($res) {
            if (strpos($_POST ['gid'], ',')) {
                echo 1;
            } else {
                echo 2;
            }
        } else {
            echo 0;
        }
    }

    //景点介绍
    public function travelDetail() {
        $id = intval($_REQUEST['id']);
        if (!$obj = $this->route->where(array('id' => $id))->field('title,id')->find()) {
            $this->error('景点不存在或已删除');
        }
        $news = M('travel_news')->where('travelId =' . $id)->find();
        $this->assign($news);
        $this->assign('obj', $obj);
        $this->display();
    }

    public function detailEdit() {
        $id = intval($_REQUEST['travelId']);
        $nid = intval($_REQUEST['nid']);
        $news = M('travel_news')->where('travelId =' . $id)->find();
        if (!$nid) {
            if ($news) {
                $this->error('景点介绍已存在，不能再添加');
            }
        } else {
            $this->assign($news);
            $this->assign('type', 'edit');
        }
        $this->display();
    }

    public function doEditDetail() {
        $title = t($_POST['title']);
        $travelId = intval($_REQUEST['travelId']);
        if (empty($title)) {
            $this->error('新闻名称不能为空！');
        }
        if (mb_strlen($title, 'UTF8') > 100) {
            $this->error('新闻名称最大100个字！');
        }
        $nid = intval($_REQUEST['nid']);
        if (!$nid) {
            $news = M('travel_news')->where('travelId =' . $travelId)->find();
            if ($news) {
                $this->error('景点介绍已存在，不能再添加');
            }
            $this->insertNews($travelId);
        } else {

            $this->updateNews($nid, $travelId);
        }
    }

    public function insertNews($travelId) {
        $title = t($_POST['title']);
        $map['title'] = $title;
        $map['travelId'] = $travelId;
        $map['content'] = t(h($_POST['content']));
        $map['cTime'] = time();
        $map['uTime'] = time();

        if (M('travel_news')->add($map)) {
            //保存成功则刷新页面
            $this->assign('jumpUrl', U('/Admin/travelDetail', array('id' => $travelId)));
            $this->success('添加成功！');
        } else {
            //失败提示
            $this->error('添加失败！');
        }
    }

    public function updateNews($nid, $travelId) {
//        $title = t($_POST['title']);
//        $map['title'] = $title;
        $map['content'] = t(h($_POST['content']));
        $map['uTime'] = time();
        if (M('travel_news')->where("id={$nid}")->save($map)) {
            $this->assign('jumpUrl', U('/Admin/travelDetail', array('id' => $travelId)));
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    public function delDetail() {
        $dao = M('travel_news');
        $gid = is_array($_POST ['gid']) ? implode(',', $_POST ['gid']) : $_POST ['gid']; // 判读是不是数组
        $map['id'] = array('IN', $gid);
        $res = $dao->where($map)->delete();
        if ($res) {
            if (strpos($_POST ['gid'], ',')) {
                echo 1;
            } else {
                echo 2;
            }
        } else {
            echo 0;
        }
    }

    public function member() {
        $travelId = intval($_REQUEST['id']);
        $list = M('travel_user')->where('travelId=' . $travelId)->findAll();
        $dao = M('travel');
        foreach ($list as $k => $v) {
            if ($v['type'] == 1) {
                $list{$k}['cost'] = $dao->getField('cost', "id=" . $travelId);
            } elseif ($v['type'] == 2) {
                $list{$k}['cost'] = $dao->getField('cost2', "id=" . $travelId);
            }
        }
        $this->assign('list', $list);
        $this->display();
        ;
    }

    public function partner() {
        $list = M('travel_partner')->findAll();
        $this->assign('list', $list);
        $this->display();
    }

    public function editPartner() {
        $this->assign('type', 'add');
        $id = (int) $_REQUEST['id'];
        if ($id) {
            $this->assign('type', 'edit');
            $map['id'] = $id;
            $obj = M('travel_partner')->where($map)->find();
            if (!$obj) {
                $this->error('无法找到对象');
            }
            $this->assign($obj);
        }
        $this->display();
    }

    public function doEditPartner() {
        if(intval($_REQUEST['display_order'])<=0){
            $this->error('排序必须大于0的整数');
        }
        $id = intval($_REQUEST['id']);
        if (empty($id)) {
            $this->_insertPartner();
        } else {
            $this->_updatePartner($id);
        }
    }

    private function _insertPartner() {
        $data = $this->_getPartnerData();
        $data['uid'] = $this->mid;
        $res = D('TravelPartner')->addPartner($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Admin/partner'));
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    private function _getPartnerData() {
        $info = tsUploadImg();
        if ($info['status']) {
            $data['pic'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
            $data['has_pic'] = 1;
        }
        $data['title'] = t($_REQUEST['title']);
        $data['url'] = t($_REQUEST['url']);
        $data['display_order'] = intval($_REQUEST['display_order']);
        if ($_REQUEST['type']==1) {
            $data['type'] = 1;
            $data['wap_url'] = t($_REQUEST['wap_url']);
            $data['apk_name'] = '';
            $data['apk_url'] = '';
        }else{
            $data['type'] = 2;
            $data['wap_url'] = '';
            $data['apk_name'] = t($_REQUEST['apk_name']);
            $data['apk_url'] = t($_REQUEST['apk_url']);
        }
        return $data;
    }

    private function _updatePartner($id) {
        $data = $this->_getPartnerData();
        $data['id'] = $id;
        $res = D('TravelPartner')->updatePartner($data);
        if ($res) {
            $this->assign('jumpUrl', U('/Admin/partner'));
            $this->success('修改成功');
        } else {
            $this->error('修改失败');
        }
    }

    public function delPartner(){
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if (D('TravelPartner')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    public function lyLucky() {
        $db_prefix = C('DB_PREFIX');
        $list = M('')->table("{$db_prefix}ly_lucky AS a ")
                ->join("{$db_prefix}user AS b ON b.uid=a.uid")
                ->field('a.uid,a.ctime,b.realname,b.mobile')
                ->findPage(10);
        $this->assign($list);
        $total = M('ly_day')->count();
        $this->assign('total',$total);
        $this->display();
    }
}

?>