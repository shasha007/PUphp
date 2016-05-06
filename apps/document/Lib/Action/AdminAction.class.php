<?php

import('home.Action.PubackAction');

class AdminAction extends PubackAction {

    var $Category;
    var $School;

    public function _initialize() {
        parent::_initialize();
        $this->Category = D('Category');
        //$this->School = D ( 'School' );
    }

    /**
     * index
     * 获取配置信息
     * @access public
     * @return void
     */
    public function index() {
        //获取配置
        //$config   = getConfig();
        //$this->assign($config);
        $this->display();
    }

    /**
     * do_change_config
     * 更改配置
     * @access public
     * @return void
     */
    public function do_change_config() {
        foreach ($_POST as $k => $v) {
            $config[$k] = t($v);
        }
        if (model('Xdata')->lput('document', $config)) {
            $this->assign('jumpUrl', U('document/Admin/index'));
            $this->success('设置成功！');
        } else {
            $this->error('设置失败！');
        }
    }

    /**
     * document_list
     * 文档管理
     * @access public
     * @return void
     */
    public function document_list() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['vote_admin_search'] = serialize($_POST);
        } else if (!empty($_GET['albumId'])) {
            $_SESSION['vote_admin_search'] = serialize($_GET);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['vote_admin_search']);
        } else {
            unset($_SESSION['vote_admin_search']);
        }
        $this->assign('isSearch', isset($_POST['isSearch']) ? '1' : '0');

        $_POST['userId'] && $map['userId'] = intval($_POST['userId']);
        $_POST['id'] && $map['id'] = intval($_POST['id']);
        $_POST['name'] && $map['name'] = array('like', '%' . t($_POST['name']) . '%');
        ($_POST['order'] && $order = 'id ' . t($_POST['order'])) || $order = 'id DESC';
        $_POST['limit'] && $limit = intval($_POST['limit']);

        $map['isDel'] = 0;

        $list = D('Document')->where($map)->order($order)->findPage($limit);

        $this->assign($_POST);
        $this->assign($list);
        $this->display();
    }

    public function put_document_to_recycle() {
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = D('Document')->setField('isDel', 1, 'id IN ' . t($gid)); // 通过审核
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

    public function delete_document() {
        $map['id'] = t($_REQUEST['id']);
        $result = D('Document')->deleteDocument($map['id'], $this->mid, 1);
        if ($result) {
            //删除成功
            if (!strpos($_REQUEST['id'], ",")) {
                echo 2;
                exit;         //说明只是删除一个
            } else {
                echo 1;
                exit;            //删除多个
            }
        } else {
            //删除失败
            echo "0";
            exit;
        }
    }

    public function doChangeIsHot() {
        $map['id'] = array('in', t($_REQUEST['id']));        //要推荐的id
        $act = $_REQUEST['type'];  //推荐动作

        if (empty($map)) {
            throw new ThinkException("不允许空条件操作数据库");
        }
        switch ($act) {
            case "recommend":   //推荐
                $field = array('isrecom', 'rTime');
                $val = array(1, time());
                $result = D('Document')->setField($field, $val, $map);
                break;
            case "cancel":   //取消推荐
                $field = array('isrecom', 'rTime');
                $val = array(0, 0);
                $result = D('Document')->setField($field, $val, $map);
                break;
        }
        if (false !== $result) {
            echo 1;
            exit;       //推荐成功
        } else {
            echo -1;
            exit;      //推荐失败
        }
    }

    /**
     * basic
     * 分类管理
     * @access public
     * @return void
     */
    public function category() {
        /* $categoryList	=	$this->Category->getCategoryList(0);
          $this->assign('categoryList',$categoryList); */

        $this->assign('category_tree', D('Category')->_makeTree(0));
        $this->display();
    }

    //添加分类
    public function addCategory() {
        if (empty($_POST ['title'])) {
            $this->error('名称不能为空！');
        }

        $cate ['title'] = t($_POST ['title']);

        //$cate['pid']	=	$this->Category->_digCate($_POST); //多级分类需要打开
        $cate ['pid'] = intval($_POST ['cid0']); //1级分类
        S('Cache_Document_Cate_0', null);
        S('Cache_Document_Cate_' . $cate ['pid'], null);
        $categoryId = $this->Category->add($cate);
        if ($categoryId) {
            S('Cache_Document_Cate_0', null);
            $this->success('操作成功！');
        } else {
            $this->error('操作失败！');
        }
    }

    // 修改分类
    public function editCategory() {
        if (isset($_POST ['editSubmit'])) {
            $id = intval($_POST ['id']);
            if (!$this->Category->getField('id', 'id=' . $id)) {
                $this->error('分类不存在！');
            } else if (empty($_POST ['title'])) {
                $this->error('名称不能为空！');
            }

            $cate ['title'] = t($_POST ['title']);

            $pid = $cate ['pid'] = intval($_POST ['cid0']); //1级分类

            S('Cache_Document_Cate_0', null);
            S('Cache_Document_Cate_' . $pid, null);

            if ($pid != 0 && !$this->Category->getField('id', 'id=' . $pid)) {
                $this->error('父级分类错误！');
            } else if ($pid == $id) {
                $res = $this->Category->setField('title', $cate ['title'], 'id=' . $id);
            } else {
                $res = $this->Category->where("id={$id}")->save($cate);
            }

            if (false !== $res) {
                S('Cache_Document_Cate_0', null);
                $this->success('操作成功！');
            } else {
                $this->error('操作失败！');
            }
        }
        $id = intval($_GET ['id']);
        $category = $this->Category->where("id=$id")->find();
        $this->assign('category', $category);
        $this->display();
    }

    // 删除分类
    public function delCategory() {
        $id = intval($_GET ['id']);
        if ($this->Category->where('id=' . $id)->delete()) {
            $this->Category->where('pid=' . $id)->delete();
            S('Cache_Document_Cate_0', null);
            $this->success();
        } else {
            $this->error('删除失败！');
        }
    }

    public function recycle() {
        $list = D('Document')->where('isDel=1')->order('document_id DESC')->findPage();
        $this->assign($list);
        $this->display();
    }

    public function doRecycle() {
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = D('Document')->setField('isDel', 0, 'id IN ' . t($gid));
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

    public function audit() {
        $audit_list = D('Document')->where('status=0 AND isDel=0 And privacy=1')->order('document_id DESC')->findPage();
        $this->assign('audit_list', $audit_list);
        $this->display();
    }

    public function doAudit() {
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = D('Document')->setField('status', 1, 'id IN ' . t($gid)); // 通过审核
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

    public function doDismissed() {
        $gid = is_array($_POST ['gid']) ? '(' . implode(',', $_POST ['gid']) . ')' : '(' . $_POST ['gid'] . ')'; // 判读是不是数组
        $res = D('Document')->setField(array('status', 'privacy'), array(2, 0), 'id IN ' . t($gid));

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

    public function editDocument() {
        $map['id'] = intval($_GET['id']);
        $document = D('Document')->where($map)->find();
        if (empty($document)) {
            $this->error('该课件不存在');
        }
        $this->assign($document);
        $sid0 = $document['schoolid'];
        $sid1 = $document['schoolid'];
        if ($document['schoolid'] != 0) {
            $pid = M('School')->getField('pid','id='.$sid0);
            if($pid>0){
                $sid0 = $pid;
            }else{
                $sid1 = 0;
            }
        }
        $this->assign('sid0', $sid0);
        $this->assign('sid1', $sid1);
        $this->display();
    }

    public function doEditDocument() {
        $document['id'] = intval($_POST['id']);
        $document['name'] = h(t($_POST['name']));
        $document['intro'] = h(t($_POST['intro']));
        $document['schoolid'] = intval($_POST['school0']);
        if (intval($_POST['school1']) > 0) {
            $document['schoolid'] = intval($_POST['school1']);
        }
        $document['credit'] = intval($_POST['credit']);
        $document['downloadCount'] = intval($_POST['downloadCount']);
        $document['readCount'] = intval($_POST['readCount']);
        $document['privacy'] = intval($_POST['privacy']);
        $document['rate'] = intval($_POST['rate']);
        $document['cid0'] = intval($_POST['cid0']);
        $document['cid1'] = 0;
        intval($_POST['cid1']) > 0 && $document['cid1'] = intval($_POST['cid1']);

        if (!$document['name']) {
            $this->error('标题不能为空');
        } else if (get_str_length($_POST['name']) > 20) {
            $this->error('标题不能超过20个字');
        }

        if ($document['schoolid'] > 0) {
            if (null == model('Schools')->getField('id', array('id' => $document['schoolid']))) {
                $this->error('请选择正确的学校分类');
            }
        } else {
            $document['schoolid'] = 0;
        }

        if ($document['cid0'] > 0) {
            if (null == D('Category')->getField('id', array('id' => $document['cid0']))) {
                $this->error('请选择分类');
            }
        } else {
            $this->error('请选择分类');
        }
        if ($document['cid1'] > 0) {
            if (null == D('Category')->getField('id', array('id' => $document['cid1']))) {
                $this->error('请选择分类');
            }
        }
        if (get_str_length($_POST['intro']) > 60) {
            $this->error('简介请不要超过60个字');
        }


        //print_r($document);exit;
        // 提交
        $res = D('Document')->save($document);

        if ($res) {
            $this->assign('jumpUrl', U('/Admin/document_list'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

    public function editOrder() {

        $new_order = @array_flip($_POST['category_top']);

        //print_r($new_order);exit;
        $category = D('Category');
        $now_order = $category->field('id,display_order')->where("pid=0")->findAll();

        //print_r($new_order);
        //print_r($now_order);exit;
        $res = true;

        foreach ($now_order as $v) {
            if ($new_order[$v['id']] == $v['display_order'])
                continue;
            //print_r($v['id']);
            //print_r(array('display_order'=>intval($new_order[$v['id']])));exit;
            $item['id'] = $v['id'];
            $item['display_order'] = intval($new_order[$v['id']]);
            //print_r($item);

            $_res = $category->save($item);

            $res = ($res && $_res) ? true : false;
        }

        if ($res) {
            S('Cache_Document_Cate_0', null);
            S('Cache_Document_Cate_top_0', null);
            $this->assign('jumpUrl', U('/Admin/category'));
            $this->success('保存成功');
        } else {
            $this->error('保存失败');
        }
    }

//jun 课程排名搜索
    public function count() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['count_admin_search'] = serialize($_POST);
        } else if (!empty($_GET['albumId'])) {
            $_SESSION['count_admin_search'] = serialize($_GET);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['count_admin_search']);
        } else {
            unset($_SESSION['count_admin_search']);
        }
        if (intval($_POST['sid1'])) {
            $map['schoolid'] = intval($_POST['sid1']);
        } else {
            if ($_POST['sid']) {
                $sids = M('school')->where('pid=' . $_POST['sid'])->field('id')->findAll();
                $sidto = array();
                foreach ($sids as $k => $v) {
                    $sidto[$k] = $v['id'];
                }
                $string = implode(',', $sidto);
                $map['schoolid'] = array('in', $string);
            }
        }
        $start = $this->_paramDate($_POST['sTime']);
        $end = $this->_paramDate($_POST['eTime']);
        if ($start > $end) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($start && $end) {
            if (t($_POST['lei']) == 2) {
                $map['dTime'] = array('between', array($start, $end));
            } else {
                $map['mTime'] = array('between', array($start, $end));
            }
        }
        $_POST['wen'] && $map['cid0'] = $_POST['wen'];
        $map['isDel'] = 0;
        $db_prefix = C('DB_PREFIX');
        if (intval($_POST['lei']) == 1) {
            $list = M('wenku')->table("{$db_prefix}wenku AS w ")
                    ->join("{$db_prefix}user AS u ON  w.userId=u.uid")
                    ->field('count(*) as num,u.uname')
                    ->where($map)
                    ->group('w.userId')
                    ->order('num DESC')
                    ->findPage(20);
        } else if (intval($_POST['lei']) == 2) {
            $map['docid'] = array('gt', 0);
            $list = M('wenku')->table("{$db_prefix}wenku AS a ")
                    ->join("{$db_prefix}wenku_download AS b ON  a.id=b.docid")
                    ->field('count(*) as count,a.name')
                    ->where($map)
                    ->group('docid')
                    ->order('count DESC')
                    ->findPage(20);
        }
        if ($_POST['sid']) {
            $tree2 = model('Schools')->_makeTree(intval($_POST['sid']));
            $this->assign('tree2', $tree2);
        }
        $cateory = M('wenku_category')->where('pid=0')->field('id,title')->findAll();
        $this->assign('tree', model('Schools')->_makeTree(0));
        $this->assign('cateory', $cateory);
        $this->assign($_POST);
        $this->assign($list);
        $this->display();
    }

    public function childTree() {

        $tree = model('Schools')->_makeTree(intval($_GET['sid']));
        if ($tree) {
            echo json_encode($tree);
        } else {
            exit(json_encode(array()));
        }
    }

}

?>