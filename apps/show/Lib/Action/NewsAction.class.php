<?php

class NewsAction extends BaseAction {

    public function index() {
        $order = 'id DESC';
        $map['isDel'] = 0;
        $list = M('show_info')->where($map)->order($order)->findPage(6);
        $this->assign($list);
        //var_dump($list['data'][0]);
        $url = U('show/news/index');
        $this->assign('url', $url);
        $this->display();
    }

    public function details() {
        $id = intval($_REQUEST['id']);
        $dao = M('show_info');
        $app = $dao->where("`id`={$id} AND isDel=0")->find();
        if (!$app) {
            $this->error('新闻不存在或已被删除！');
        }
        $app['content'] = htmlspecialchars_decode($app['content']);
        $this->assign('app',$app);
        $this->display();
    }

}

?>