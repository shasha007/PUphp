<?php
class GroupFeedAction extends GroupBaseAction {

    public function _initialize() {
        parent::_initialize();
        // 权限判读
        if (in_array(ACTION_NAME, array('add', 'doAdd', 'edit','del'))) {
            if (!$this->smid) {
                $this->error('请先登录');
            }
            if (!$this->isadmin) {
                $this->error('你没有权限');
            }
        }
        $this->setTitle("部落动态 - " . $this->groupinfo['name']);
    }

    public function index() {
        $dao = D('GroupFeed');
        if(isset($_POST['doadd']) && $this->isValidHash()){
            if (!$this->isadmin) {
                $this->error('你没有权限');
            }
            if(!$this->doAdd()){
                $this->error($dao->getError());
            }
            $_REQUEST['sub'] = 1;
        }
        $list = D('GroupFeed')->getByGid($this->gid);
        $this->assign($list);
        if($_REQUEST['sub']){
            $this->assign('sub',1);
        }else{
            $this->assign('sub',0);
        }
        $this->display('index');
    }

    // 添加
    public function doAdd() {
        $dao = D('GroupFeed');
        $res = $dao->addFeed($this->gid,$this->mid,$_POST['content']);
        if ($res) {
            return true;
        }
        return false;
    }

    //删除
    public function del() {
        $id = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
        if ($id == '')
            $this->error('tid错误');
        $dao = D('GroupFeed');
        $res = $dao->remove($this->gid,$id);
        if (!$res) {
            $this->error($dao->getError());
        } else {
            $this->success('删除成功');
        }

    }

}
