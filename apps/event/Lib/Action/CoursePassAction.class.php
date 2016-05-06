<?php

/**
 * 后台首页
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class CoursePassAction extends CourseCanAction {

    /**
     * index
     * 首页
     * @access public
     * @return void
     */
    public function index() {
        $this->display();
    }


}
