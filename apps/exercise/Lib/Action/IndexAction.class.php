<?php

class IndexAction extends Action {

    public function index() {
        $this->display();
    }

    public function go() {
        $dao = D('Exercise');
        $cntTk = 20;
        $cntXz = 10;
        $cntPd = 10;
        $this->assign('tk', $dao->where(array('type'=>3))->limit($cntTk)->order('rand()')->findAll());
        $this->assign('xz', $dao->where(array('type'=>1))->limit($cntXz)->order('rand()')->findAll());
        $this->assign('pd', $dao->where(array('type'=>2))->limit($cntPd)->order('rand()')->findAll());
        $this->assign('goSum', count($this->get('tk'))+count($this->get('xz'))+count($this->get('pd')));
        $this->display();
    }

}
