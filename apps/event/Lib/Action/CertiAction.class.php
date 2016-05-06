<?php

/**
 * CertiAction
 * 打印证书管理
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class CertiAction extends TeacherAction {

    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        if ($this->rights['can_print'] != 1) {
            $this->assign('jumpUrl', U('event/Readme/index'));
            $this->error('您没有权限管理打印证书！');
        }
    }

    public function index() {
        $res = $this->_getPrintList();
        $this->assign($res);
        $this->display();
    }

    public function doDeletePrint() {
        $map['sid'] = $this->school['id'];
        $map['id'] = array('in', explode(',', $_REQUEST['id']));    //要删除的id.
        $result = M('event_print')->where($map)->delete();
        if (false != $result) {
            if (!strpos($_REQUEST['id'], ",")) {
                echo 2;            //说明只是删除一个
            } else {
                echo 1;            //删除多个
            }
        } else {
            echo -1;               //删除失败
        }
    }

    //搜索用户
    public function doSearchUser() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['s_searchPrint'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['s_searchPrint']);
        } else {
            unset($_SESSION['s_searchPrint']);
        }
        //查找用户
        $map = array();
        $number = t($_POST['number']);
        if($number!=''){
            $map['b.email'] = $number.$this->school['email'];
        }
        //姓名时，模糊查询
        if (isset($_POST['realname']) && $_POST['realname'] != '') {
            $map['b.realname'] = array('exp', 'LIKE "%' . $_POST['realname'] . '%"');
        }
        $res = $this->_getPrintList($map);
        $this->assign($res);
        $this->assign('isSearch', 1);
        $this->assign(array_map('t', $_POST));
        $this->display('index');
    }

    private function _getPrintList($map=array()){
        $map['a.sid'] = $this->school['id'];
        $map['a.is_orga'] = 1;
        $config = D('SchoolWeb','event')->getConfigCache($this->school['id']);
        if($config['print_day']){
            $day = '-'.$config['print_day'].' day';
            $map['a.cTime'] >= strtotime($day);
        }
        $db_prefix = C('DB_PREFIX');
        return M('event_print')->table("{$db_prefix}event_print AS a")
                    ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
                    ->field('a.id,a.cTime,b.realname,b.email')
                    ->where($map)
                    ->order('a.id DESC')
                    ->findPage(20);
    }
}
