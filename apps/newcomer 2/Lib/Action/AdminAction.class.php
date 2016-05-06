<?php

import('admin.Action.AdministratorAction');
class AdminAction extends AdministratorAction {

	var $Category;
		
	/**
	 * _initialize
	 * @access public
	 * @return void
	 */
	public function _initialize() {
		//管理权限判定
        parent::_initialize();
		$this->Category = M ( 'newcomer_category' );
	}

	/**
	 * index
	 * @access public
	 * @return void
	 */
    public function index(){
		$data = M('newcomer_document')->order('`display_order` ASC,`document_id` ASC')->findAll();
		$this->assign('data', $data);
		$this->display();
    }

	public function addDocument() {
		$this->assign('type', 'add');
		$this->assign('is_active', 1);	
		$this->assign('isrecom', 1);								
		$this->display('editDocument');
	}

	public function editDocument() {
		$map['document_id'] = intval($_GET['id']);
		$document = M('newcomer_document')->where($map)->find();
		if ( empty($document) )
			$this->error('该文章不存在');
		$this->assign($document);
		$this->assign('type', 'edit');
		$this->display();
	}

	public function doEditDocument()
	{
		if (($_POST['document_id'] = intval($_POST['document_id'])) <= 0)
			unset($_POST['document_id']);

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
		}

		// 数据检查
		if (empty($_POST['title']))
			$this->error('标题不能为空');

		$options['userId']		=	$this->mid;
		$options['allow_exts']	=	'jpeg,gif,jpg,png';
		$options['max_size']    =   2000000;
		$info	=	X('Xattach')->upload('document',$options);
		
		if($info['status']){	
			$_POST['icon']=$info['info'][0]['savepath'].$info['info'][0]['savename'];
		}else{			
			//$this->error($info['info']);
		}
		
		// 提交
		$res = isset($_POST['document_id']) ? M('newcomer_document')->save($_POST) : M('newcomer_document')->add($_POST);
		//print_r($res);
		//print_r($_POST);exit;	
		if ($res) {
			if ( isset($_POST['document_id']) ) {
				$this->assign('jumpUrl', U('newcomer/Admin/index'));
			} else {
				//M('newcomer_document')->where("`document_id`=$res")->setField('display_order', $res);
				$this->assign('jumpUrl', U('newcomer/Admin/addDocument'));
			}
			$this->success('保存成功');
		} else {
			$this->error('保存失败');
		}
	}

	public function doDeleteDocument()
	{
		if (empty($_POST['ids'])) {
			echo 0;
			exit ;
		}

		$map['document_id'] = array('in', t($_POST['ids']));
		echo M('newcomer_document')->where($map)->delete() ? '1' : '0';
	}


	/**
	 * basic 
	 * 分类管理
	 * @access public
	 * @return void
	 */
	public function category() {
		/*$categoryList	=	$this->Category->getCategoryList(0);
			$this->assign('categoryList',$categoryList);*/
		
		$this->assign ( 'category_tree', D ( 'Category' )->_makeTree ( 0 ) );
		$this->display ();
	}
	
	//添加分类
	public function addCategory() {
		if (empty ( $_POST ['title'] )) {
			$this->error ( '名称不能为空！' );
		}
		
		$cate ['title'] = t ( $_POST ['title'] );
		
		//$cate['pid']	=	$this->Category->_digCate($_POST); //多级分类需要打开
		$cate ['pid'] = intval ( $_POST ['cid0'] ); //1级分类
		S('Cache_Newcomer_Cate_0',null);
		S('Cache_Newcomer_Cate_'.$cate ['pid'],null);
		$categoryId = $this->Category->add ( $cate );
		if ($categoryId) {
			S('Cache_Newcomer_Cate_0',null); 
			$this->success ( '操作成功！' );
		} else {
			$this->error ( '操作失败！' );
		}
	}
	
	// 修改分类   		
	public function editCategory() {
		
		
		if (isset ( $_POST ['editSubmit'] )) {
			$id = intval ( $_POST ['id'] );
			if (! $this->Category->getField ( 'id', 'id=' . $id )) {
				$this->error ( '分类不存在！' );
			} else if (empty ( $_POST ['title'] )) {
				$this->error ( '名称不能为空！' );
			}
			
			$cate ['title'] = t ( $_POST ['title'] );
			
			$pid = $cate ['pid'] = intval ( $_POST ['cid0'] ); //1级分类
			
			S('Cache_Newcomer_Cate_0',null);
			S('Cache_Newcomer_Cate_'.$pid,null);
			
			if ($pid != 0 && ! $this->Category->getField ( 'id', 'id=' . $pid )) {
				$this->error ( '父级分类错误！' );
			} else if ($pid == $id) {
				$res = $this->Category->setField ( 'title', $cate ['title'], 'id=' . $id );
			} else {
				$res = $this->Category->where ( "id={$id}" )->save ( $cate );
			}
			
			if (false !== $res) {
				S('Cache_Newcomer_Cate_0',null); 
				$this->success ( '操作成功！' );
			} else {
				$this->error ( '操作失败！' );
			}
		}
		$id = intval ( $_GET ['id'] );
		
		$category = $this->Category->where ( "id=$id" )->find ();
		$this->assign ( 'category', $category );
		$this->display ();
	}
	
	// 删除分类
	public function delCategory() {
		$id = intval ( $_GET ['id'] );
		if ($this->Category->where ( 'id=' . $id )->delete ()) {
			$this->Category->where ( 'pid=' . $id )->delete ();
			S('Cache_Newcomer_Cate_0',null); 
			$this->success ();
		} else {
			$this->error ( '删除失败！' );
		}
	}
	
}
?>