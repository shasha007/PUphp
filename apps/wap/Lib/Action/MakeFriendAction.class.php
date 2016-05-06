<?php
//添加好友
class MakeFriendAction extends BaseAction {
	public function index(){
		$data['uid'] = $this->mid ;
		$data['fid'] = intval($_REQUEST['fid']) ;
		if ($data['fid']<1) {
			$result['msg'] = '添加失败请稍候再试' ;
			$result['status'] = 0 ;
			echo json_encode($result);
			die ;
		}
		if (M('weibo_follow')->where($data)->find()) {
			$result['msg'] = '你们已经是好友！' ;
			$result['status'] = 1 ;	
			echo json_encode($result);
			die ;					
		}
		$data['ctime'] = time() ;
		$result['msg'] = '添加失败请稍候再试' ;
		$result['status'] = 0 ;
		if (M('weibo_follow')->add($data)) {
			$result['msg'] = '添加成功' ;
			$result['status'] = 1 ;
			echo json_encode($result);
			die;
		}
		echo json_encode($result);
		die;
	}
}
?>