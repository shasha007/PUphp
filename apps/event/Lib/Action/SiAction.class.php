<?php

/**
 * SI测评平台
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author 陆冬云 <ludongyun@tiangongwang.com>
 */
class SiAction extends SchoolbaseAction {

    public function _initialize() {
        parent::_initialize();
        $this->setTitle('测评平台');
    }

    public function index() {
        $this->display();
    }

}

?>
