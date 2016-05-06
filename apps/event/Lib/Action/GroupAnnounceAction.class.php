<?php
class GroupAnnounceAction extends GroupBaseAction {

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
        $this->setTitle("通知公告 - " . $this->groupinfo['name']);
    }

    public function index() {
        $dao = D('GroupAnnounce');
        if(isset($_POST['doadd']) && $this->isValidHash()){
            if (!$this->isadmin) {
                $this->error('你没有权限');
            }
            if(!$this->doAdd()){
                $this->error($dao->getError());
            }
            $_REQUEST['sub'] = 1;
            $this->groupinfo['announce'] = t(getShort($_POST['announce'], 60));
            $this->assign('groupinfo', $this->groupinfo);

        }
        $list = D('GroupAnnounce')->getByGid($this->gid);
        $this->assign($list);
        if($_REQUEST['sub']){
            $this->assign('sub',1);
        }else{
            $this->assign('sub',0);
        }
        $this->display('index');
    }

    // 添加通知公告
    public function doAdd() {
        $dao = D('GroupAnnounce');
        $res = $dao->addAnnounce($this->gid,$this->groupinfo['name'],$this->mid,$this->user['realname'],$_POST['announce']);
        if ($res) {
            return true;
        }
        return false;
    }

    //删除
    public function del() {
        $id = isset($_POST['tid']) && !empty($_POST['tid']) ? t($_POST['tid']) : '';
        if ($id == '')
            exit(json_encode(array('flag' => '0', 'msg' => 'tid错误')));
        $dao = D('GroupAnnounce');
        $res = $dao->remove($this->gid,$id);
        if ($res === false) {
            exit(json_encode(array('flag' => '0', 'msg' => '删除失败')));
        } else {
            exit(json_encode(array('flag' => '1', 'msg' => '删除成功')));
        }

    }

}
