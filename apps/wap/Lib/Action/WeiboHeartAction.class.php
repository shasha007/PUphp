<?php
class WeiboHeartAction extends BaseAction {
	public function heart(){

		if (intval($_REQUEST['status']) === 1) {
			if (D('Heart','weibo')->heartWeibo( intval( $_REQUEST['id'] ), $this->mid )) {
				echo 1 ;
				die ;
			}
		}else{
			if (D('Heart','weibo')->dodelete( intval( $_REQUEST['id'] ), $this->mid )) {
				echo 2 ;
				die ;
			}
		}
		echo 0 ;
	}
}
?>


