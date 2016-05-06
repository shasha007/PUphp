<?php

/**
 * @todo  PU管理后台 微博话题模块
 * Created by PhpStorm.
 * User: zhuhaibing06
 * Date: 2016/1/20
 * Time: 14:42
 */
import('home.Action.PubackAction');
class RedPersonAction extends PubackAction{
	protected $input;
    function _initialize()
    {
        parent::_initialize();
        $this->input = $_POST;
    }
	// 红人列表
	public function index(){
        if (!empty($this->input)){
            $_SESSION['redperson_search'] = serialize($this->input);
        }
        else if(isset($_GET[C('VAR_PAGE')])){
            $this->input = unserialize($_SESSION['redperson_search']);
        }
        else{
            unset($_SESSION['redperson_search']);
        }
        $this->input['rid'] && $map['rid'] = intval($this->input['rid']) ;
        $this->input['user_id'] && $map['uid'] = intval($this->input['user_id']) ;
        $map['isDel'] = 0 ;
		$list = M('red_user')->where($map)->findpage("20") ;
		foreach ($list['data'] as $key => $value) {
			$list['data'][$key]['uname'] = getUserField($value['uid'],'uname') ;
			$list['data'][$key]['rname'] = M('redlist')->where('id='.$value['rid'])->getField('name') ;
		}
		$redlist = D('Redlist')->search() ;
		$this->assign('list',$list) ;
		$this->assign($this->input);
		$this->assign('redlist',$redlist) ;
		$this->display() ;
	}

	// 红人头衔列表
	public function redList(){
		$list = D('Redlist')->search() ;
		$this->assign('list',$list) ;
		$this->display() ;
	}


	// 红人头衔添加页面修改
	public function addRedName(){
		$id = intval($_REQUEST['id']) ;
		if ($id>0) {
			$result = M('redlist')->where('id='.$id)->find() ;
			$this->assign($result) ;
		}
		$this->assign($_REQUEST) ;
		$this->display() ;
	}

	// 红人头衔添加操作修改
	public function doaddRedName(){
		$id = intval($_REQUEST['id']) ;
		$name = t($_REQUEST['name']) ;
		if (empty($name)) {
			$this->error('参数错误') ;
		}
		$data['name'] = $name ;
		if ($id>0) {
			if (D('Redlist')->editRed($id,$data) !== false) {
				$this->success('修改成功') ;
			}else{
				$this->error('修改失败') ;
			}
		}else{
			if (D('Redlist')->doAddRed($name)) {
			 	$this->success('添加成功') ;
			 }else{
			 	$this->error('添加失败') ;
			 }
		}
	}

	// 红人头衔删除
	public function deleteRedName(){
		$ids = explode(',',$_REQUEST['ids']) ;
		if(D('Redlist')->deleteRed($ids)){
			echo 1 ;
		}else{
			echo 0 ;
		}
	}

	// 给人添加红人属性
	public function addPersonToRed(){
		$list = D('Redlist')->search() ;
		$this->assign('list',$list) ;
		$this->display() ;
	}

	// 添加红人操作
	public function doaddPersonToRed(){
		$uid = intval($_REQUEST['uid']) ;
		$rid = intval($_REQUEST['rid']) ;
		$label = t($_REQUEST['label']) ;
		if ($uid < 1 || $rid < 1 || empty($label)) {
			$this->error('参数错误') ;
		}
		$result = D('Redlist')->addPersonToRed($uid,$rid,$label) ;
		switch ($result) {
			case '1':
				$this->success('添加成功') ;
				break;
			case '2':
				$this->error('此用户已经在该分类中') ;
				break;
		}
		$this->error('添加失败请稍后再试') ;
	}

	// 删除红人属性
	public function dodeletePersonRed(){
		$ids = explode(',',$_REQUEST['id']) ;
		if(D('Redlist')->deletePersonRed($ids)){
			echo 1 ;
		}else{
			echo 0 ;
		}
	}
}

?>