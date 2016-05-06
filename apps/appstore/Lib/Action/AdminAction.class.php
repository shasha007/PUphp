<?php

import('home.Action.PubackAction');
class AdminAction extends PubackAction {

	var $Category;

	/**
	 * _initialize
	 * @access public
	 * @return void
	 */
	public function _initialize() {
		//管理权限判定
        parent::_initialize();
		$this->Category = M ( 'appstore_category' );
	}

	/**
	 * index
	 * @access public
	 * @return void
	 */
    public function index(){
		$this->assign('t', 0);
		$this->display();
    }

    public function app(){

		$t	=	intval($_GET['t']);
		if(!in_array($t,array(1,2))) {
			$t = 1;
		}
		$title = $this->getTitle($t);
		$this->assign('typename', $title);

		$this->assign('isSearch', isset($_POST['isSearch'])?'1':'0');

		$limit = 20;
		$order='id DESC';

        $_POST['name']      && $map['title']      =   array( 'like','%'.t( $_POST['name'] ).'%' );
        ($_POST['order']    && $order     		 =   'id '.t( $_POST['order'] )) || $order='id DESC';
        $_POST['limit']     && $limit            =   intval($_POST['limit']);

		$map['category'] = $t;
		//print_r($map);
		$data = D('Appx')->where($map)->order($order)->findPage($limit);
		//print_r($data);
		$this->assign('t', $t);
		$this->assign($data);
		$this->display();
    }

    public function banner(){
		$data = M('appstore_banner')->order('`id` DESC')->findAll();
		//print_r($data);
		$this->assign('t', "8");
		$this->assign('data', $data);
		$this->display();
    }
	public function addBanner() {


		$t = 8;
		$this->assign('t', $t);
		$this->assign('type', 'add');
		$this->assign('typename', 'Banner');
		$this->assign('is_active', 1);
		$this->display('editBanner');
	}

	public function editBanner() {
		$map['id'] = intval($_GET['id']);
		$app = M('appstore_banner')->where($map)->find();
		if ( empty($app) ) {
			$this->error('该项目不存在');
		}

		$t = 8;
		$this->assign('t', $t);
		$this->assign($app);
		$this->assign('typename', 'Banner');
		$this->assign('type', 'edit');
		$this->display();
	}
	public function doEditBanner()
	{
		if (($_POST['id'] = intval($_POST['id'])) <= 0)
			unset($_POST['id']);
		$t = 8;
		// 格式化数据
		$_POST['title']			= t($_POST['title']);
		$_POST['is_active']		= intval($_POST['is_active']);
		$_POST['mtime']			= time();
		if (!isset($_POST['id'])) {
			// 新建文章
			$_POST['ctime']		= $_POST['mtime'];
		}
		// 数据检查
		if (empty($_POST['title']))
			$this->error('标题不能为空');

		$options['userId']		=	$this->mid;
		$options['allow_exts']	=	'jpeg,gif,jpg,png';
		$options['max_size']    =   10000000;

		$info	=	X('Xattach')->upload('appstore',$options);

		if($info['status']){
			$_POST['img']	=	$info['info'][0]['savepath'].$info['info'][0]['savename'];
		}else{
			//$this->error($info['info']);
		}

		// 提交
		$res = isset($_POST['id']) ? M('appstore_banner')->save($_POST) : M('appstore_banner')->add($_POST);

		if ($res) {
			if ( isset($_POST['id']) ) {
				$this->assign('jumpUrl', U('appstore/Admin/banner',array('t'=>$t)));
			} else {
				//M('appstore_document')->where("`document_id`=$res")->setField('display_order', $res);
				$this->assign('jumpUrl', U('appstore/Admin/addBanner',array('t'=>$t)));
			}
			$this->success('保存成功');
		} else {
			$this->error('保存失败');
		}
	}
	public function doDeleteBanner()
	{
		if (empty($_POST['ids'])) {
			echo 0;
			exit ;
		}

		$map['id'] = array('in', t($_POST['ids']));
		echo M('appstore_banner')->where($map)->delete() ? '1' : '0';
	}
	public function link(){
		$data = M('appstore_link')->order('`id` DESC')->findAll();
		//print_r($data);
		$this->assign('t', "9");
		$this->assign('data', $data);
		$this->display();
	}
	public function addLink() {


		$t = 9;
		$this->assign('t', $t);
		$this->assign('type', 'add');
		$this->assign('typename', '合作方');
		$this->assign('is_active', 1);
		$this->display('editLink');
	}

	public function editLink() {
		$map['id'] = intval($_GET['id']);
		$app = M('appstore_link')->where($map)->find();
		if ( empty($app) ) {
			$this->error('该项目不存在');
		}

		$t = 9;
		$this->assign('t', $t);
		$this->assign($app);
		$this->assign('typename', '合作方');
		$this->assign('type', 'edit');
		$this->display();
	}
	public function doEditLink()
	{
		if (($_POST['id'] = intval($_POST['id'])) <= 0)
			unset($_POST['id']);
		$t = 9;
		// 格式化数据
		$_POST['title']			= t($_POST['title']);
		$_POST['is_active']		= intval($_POST['is_active']);
		$_POST['mtime']			= time();
		if (!isset($_POST['id'])) {
			// 新建文章
			$_POST['ctime']		= $_POST['mtime'];
		}
		// 数据检查
		if (empty($_POST['title']))
			$this->error('标题不能为空');

		$options['userId']		=	$this->mid;
		$options['allow_exts']	=	'jpeg,gif,jpg,png';
		$options['max_size']    =   10000000;

		$info	=	X('Xattach')->upload('appstore',$options);

		if($info['status']){
			$_POST['img']	=	$info['info'][0]['savepath'].$info['info'][0]['savename'];
		}else{
			//$this->error($info['info']);
		}

		// 提交
		$res = isset($_POST['id']) ? M('appstore_link')->save($_POST) : M('appstore_link')->add($_POST);

		if ($res) {
			if ( isset($_POST['id']) ) {
				$this->assign('jumpUrl', U('appstore/Admin/link',array('t'=>$t)));
			} else {
				//M('appstore_document')->where("`document_id`=$res")->setField('display_order', $res);
				$this->assign('jumpUrl', U('appstore/Admin/addLink',array('t'=>$t)));
			}
			$this->success('保存成功');
		} else {
			$this->error('保存失败');
		}
	}
	public function doDeleteLink()
	{
		if (empty($_POST['ids'])) {
			echo 0;
			exit ;
		}

		$map['id'] = array('in', t($_POST['ids']));
		echo M('appstore_link')->where($map)->delete() ? '1' : '0';
	}

	public function getTitle($type) {
		$title = "";
		switch( $type ) {
			case 1:
			$title = "应用";
			break;
			case 2:
			$title = "游戏";
			break;
			case 3:
			$title = "资讯";
			break;
			case 4:
			$title = "评测";
			break;
		}
		return $title;
	}

    public function doc(){

		$t	=	intval($_GET['t']);
		if(!in_array($t,array(3,4))) {
			$t = 3;
		}
		$title = $this->getTitle($t);
		$this->assign('typename', $title);
		$map['category'] = $t-3;
		$data = D('Document')->where($map)->order('`document_id` DESC')->findAll();
		$this->assign('t', $t);
		$this->assign('data', $data);
		$this->display();
    }
	public function addApp() {

		$t	=	intval($_GET['t']);
		if(!in_array($t,array(1,2))) {
			$t = 1;
		}
		$title = $this->getTitle($t);
		$this->assign('typename', $title);
		$this->assign('t', $t);
		$this->assign('type', 'add');
		$this->assign('is_active', 1);
		$this->display('editApp');
	}

	public function editApp() {
		$map['id'] = intval($_GET['id']);
		$app = D('Appx')->where($map)->find();
		//print_r($app);exit;
		if ( empty($app) ) {
			$this->error('该文章不存在');
		}
		$t	= $app['category'];
		if(!in_array($t,array(1,2))) {
			$t = 1;
		}
		$title = $this->getTitle($t);
		$this->assign('typename', $title);
		$this->assign('t', $t);
		$this->assign($app);
		$this->assign('type', 'edit');
		$this->display();
	}
	public function addDocument() {

		$t	=	intval($_GET['t']);
		if(!in_array($t,array(3,4))) {
			$t = 3;
		}
		$title = $this->getTitle($t);
		$this->assign('typename', $title);
		$this->assign('t', $t);
		$this->assign('type', 'add');
		$this->assign('is_active', 1);
		$this->display('editDocument');
	}

	public function editDocument() {
		$map['document_id'] = intval($_GET['id']);
		$document = D('Document')->where($map)->find();
		if ( empty($document) ) {
			$this->error('该文章不存在');
		}
		$t	= $document['category']+3;
		if(!in_array($t,array(3,4))) {
			$t = 3;
		}
		$title = $this->getTitle($t);
		$this->assign('typename', $title);
		$this->assign('t', $t);
		$this->assign($document);
		$this->assign('type', 'edit');
		$this->display();
	}

	public function doEditApp()
	{
		if (($_POST['id'] = intval($_POST['id'])) <= 0)
			unset($_POST['id']);

		$t	=	intval($_GET['t']);
		if(!in_array($t,array(1,2))) {
			$t = 1;
		}

		// 格式化数据
		$_POST['title']			= t($_POST['title']);
		$_POST['is_active']		= intval($_POST['is_active']);
		$_POST['mtime']			= time();

		$_POST['content'] = t(h($_POST['content']));

		if (!isset($_POST['document_id'])) {
			// 新建文章
			$_POST['ctime']		= $_POST['mtime'];
			$_POST['category']	= $t;
		}

		// 数据检查
		if (empty($_POST['title']))
			$this->error('标题不能为空');

		$options['userId']		=	$this->mid;
		$options['allow_exts']	=	'jpeg,gif,jpg,png,apk,ipa';
		$options['max_size']    =   50000000;

		$info	=	X('Xattach')->upload('appstore',$options);

		if($info['status']){

			foreach($info['info'] as $file) {
				if($file['key'] == "icon") {
					$_POST['icon']	=	$file['savepath'].$file['savename'];
				} else if($file['key'] == "appfile") {
					$_POST['appfile']	=	$file['savepath'].$file['savename'];
				}
			}
		}else{
			//$this->error($info['info']);
		}

		//print_r($_POST);exit;
		// 提交
		$res = isset($_POST['id']) ? M('appstore_app')->save($_POST) : M('appstore_app')->add($_POST);

		if ($res) {
			if ( isset($_POST['id']) ) {
				$this->assign('jumpUrl', U('appstore/Admin/app',array('t'=>$t)));
			} else {
				//M('appstore_document')->where("`document_id`=$res")->setField('display_order', $res);
				$this->assign('jumpUrl', U('appstore/Admin/addApp',array('t'=>$t)));
			}
			$this->success('保存成功');
		} else {
			$this->error('保存失败');
		}
	}


	public function doEditDocument()
	{
		if (($_POST['document_id'] = intval($_POST['document_id'])) <= 0)
			unset($_POST['document_id']);

		$t	=	intval($_GET['t']);
		if(!in_array($t,array(3,4))) {
			$t = 3;
		}

		// 格式化数据
		$_POST['title']			= t($_POST['title']);
		$_POST['is_active']		= intval($_POST['is_active']);
		$_POST['last_editor_id']= $this->mid;
		$_POST['mtime']			= time();
		if (preg_match('/^\s*((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z]+)+(?:\:[0-9]*)?(?:\/[^\x{4e00}-\x{9fa5}\s<\'\"“”‘’]*)?)\s*$/u', strip_tags(html_entity_decode($_POST['content'], ENT_QUOTES, 'UTF-8')), $url)
			|| preg_match('/^\s*((?:mailto):\/\/[a-zA-Z0-9_]+@[a-zA-Z0-9][a-zA-Z0-9\.]*[a-zA-Z0-9])\s*$/u', strip_tags(html_entity_decode($_POST['content'], ENT_QUOTES, 'UTF-8')), $url)) {
			$_POST['content'] = h($url[1]);
		} else {
			$_POST['content'] = t(h($_POST['content']));
		}
		if (!isset($_POST['document_id'])) {
			// 新建文章
			$_POST['author_id']	= $this->mid;
			$_POST['ctime']		= $_POST['mtime'];
			$_POST['category']	= $t-3;
		}

		// 数据检查
		if (empty($_POST['title']))
			$this->error('标题不能为空');

		// 提交
		$res = isset($_POST['document_id']) ? M('appstore_document')->save($_POST) : M('appstore_document')->add($_POST);

		if ($res) {
			if ( isset($_POST['document_id']) ) {
				$this->assign('jumpUrl', U('appstore/Admin/doc',array('t'=>$t)));
			} else {
				//M('appstore_document')->where("`document_id`=$res")->setField('display_order', $res);
				$this->assign('jumpUrl', U('appstore/Admin/addDocument',array('t'=>$t)));
			}
			$this->success('保存成功');
		} else {
			$this->error('保存失败');
		}
	}
	public function doDeleteApp()
	{
		if (empty($_POST['ids'])) {
			echo 0;
			exit ;
		}

		$map['id'] = array('in', t($_POST['ids']));
		echo M('appstore_app')->where($map)->delete() ? '1' : '0';
	}

	public function doDeleteDocument()
	{
		if (empty($_POST['ids'])) {
			echo 0;
			exit ;
		}

		$map['document_id'] = array('in', t($_POST['ids']));
		echo M('appstore_document')->where($map)->delete() ? '1' : '0';
	}


	/**
	 * basic
	 * 分类管理
	 * @access public
	 * @return void
	 */
	public function category() {
		$t	=	intval($_GET['t']);
		if(!in_array($t,array(5,6,7))) {
			$t = 5;
		}
		$title = "";
		$tpl = "";
		$c = "";
		switch( $t ) {
			case 5:
			$title = "平台";
			$tpl = "platform";
			$c = D ( 'Category' )->_makeTree ( 0, 2 );
			break;
			case 6:
			$title = "应用";
			$tpl = "category";
			$c = D ( 'Category' )->_makeTree ( 1, 1 );
			break;
			case 7:
			$title = "游戏";
			$tpl = "category";
			$c = D ( 'Category' )->_makeTree ( 2, 1 );
			break;
		}
		$this->assign('typename', $title);
		$this->assign('t', $t);
		$this->assign ( 'category_tree', $c );

		$this->display ($tpl);
	}

	//添加分类
	public function addCategory() {
		if (empty ( $_POST ['title'] )) {
			$this->error ( '名称不能为空！' );
		}

		$t	=	intval($_GET['t']);
		if(!in_array($t,array(5,6,7))) {
			$t = 5;
		}
		switch( $t ) {
			case 5:
			$cate ['pid'] = 0;
			$type = 2;
			break;
			case 6:
			$cate ['pid'] = 1;
			$type = 1;
			break;
			case 7:
			$cate ['pid'] = 2;
			$type = 1;
			break;
		}
		$cate ['type'] = $type;
		$cate ['title'] = t ( $_POST ['title'] );

		$categoryId = D('Category')->add ( $cate );
		if ($categoryId) {
			S('Cache_Appstore_Cate_0_1',null);
			S('Cache_Appstore_Cate_0_2',null);
			$this->success ( '操作成功！' );
		} else {
			$this->error ( '操作失败！' );
		}
	}

	// 修改分类
	public function editCategory() {

		$id = intval ( $_GET ['id'] );
		$t	=	intval($_GET['t']);
		if(!in_array($t,array(5,6,7))) {
			$t = 5;
		}
		$title = "";
		switch( $t ) {
			case 5:
			$title = "平台";
			$type = 2;
			break;
			case 6:
			$title = "应用";
			$type = 1;
			break;
			case 7:
			$title = "游戏";
			$type = 1;
			break;
		}

		if (isset ( $_POST ['editSubmit'] )) {
			$id = intval ( $_POST ['id'] );
			if (! D ( 'Category' )->getField ( 'id', 'id=' . $id )) {
				$this->error ( '分类不存在！' );
			} else if (empty ( $_POST ['title'] )) {
				$this->error ( '名称不能为空！' );
			}

			$cate ['title'] = t ( $_POST ['title'] );

			$res = D ( 'Category' )->setField ( 'title', $cate ['title'], 'id=' . $id );

			if (false !== $res) {
				S('Cache_Appstore_Cate_0_1',null);
				S('Cache_Appstore_Cate_0_2',null);
				$this->success ( '操作成功！' );
			} else {
				$this->error ( '操作失败！' );
			}
		}

		$this->assign('typename', $title);
		$this->assign('t', $t);
		$category = D('Category')->where ( "id=$id" )->find ();
		$this->assign ( 'category', $category );
		$this->display ();
	}

	// 删除分类
	public function delCategory() {
		$id = intval ( $_GET ['id'] );

		if ($this->Category->where ( 'id=' . $id )->delete ()) {
			$this->Category->where ( 'pid=' . $id )->delete ();
			S('Cache_Appstore_Cate_0_1',null);
			S('Cache_Appstore_Cate_0_2',null);
			$this->success ();
		} else {
			$this->error ( '删除失败！' );
		}
	}

	 public function doRecom(){
       		$map['id'] = t($_REQUEST['id']);        //要推荐的id
           $act  = $_REQUEST['type'];  //推荐动作

			if( empty($map) ) {
				echo -1;exit;
			}
			switch( $act ) {
				case "recommend":   //推荐
					$field = array( 'isrecom','rtime' );
					$val = array( 1,time() );
					$result = M('appstore_app')->setField( $field,$val,$map );
					break;
				case "cancel":   //取消推荐
					$field = array( 'isrecom','rtime' );
					$val = array( 0,0 );
					$result = D('Appx')->setField( $field,$val,$map );
					break;

			}
		//	print_r($result);
           if( false !== $result){
               echo 1;exit;       //推荐成功
           }else{
               echo -1;exit;      //推荐失败
           }
       }

	public function editOrder(){

		$t	=	intval($_GET['t']);
		if(!in_array($t,array(6,7))) {
			$t = 6;
		}
		$title = "";
		$pid = 1;
		switch( $t ) {
			case 6:
			$title = "应用";
			$pid = 1;
			break;
			case 7:
			$title = "游戏";
			$pid = 2;
			break;
		}

		$new_order = @array_flip($_POST['category_top']);

		//print_r($new_order);exit;
		$category = D( 'Category' );
		$now_order = $category->field('id,display_order')->where("type=1 and pid=".$pid)->findAll();


		//print_r($new_order); print_r($now_order);exit;
		$res = true;

		foreach($now_order as $v){
			if($new_order[$v['id']] == $v['display_order']) continue;
			//print_r($v['id']);
			//print_r(array('display_order'=>intval($new_order[$v['id']])));exit;
			$item['id'] = $v['id'];
			$item['display_order'] = intval($new_order[$v['id']]);
			//print_r($item);

			$_res = $category->save($item);

			$res = ($res&&$_res)?true:false;
		}

		if ($res) {
			S('Cache_Appstore_Cate_0_'.$type,null);
			S('Cache_Appstore_Cate_top_0_'.$type,null);
			$this->assign('jumpUrl', U('/Admin/category', array('t'=>$t)));
			$this->success('保存成功');
		} else {
			$this->error('保存失败');
		}

	}
}
?>