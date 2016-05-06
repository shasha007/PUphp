<?php

class GroupWeiboOperateAction extends GroupBaseAction {
    
  public function _initialize() {
        parent::_initialize();
  }
    // 分享到微博
    public function weibo() {
        // 解析参数
        $_GET['param'] = unserialize(urldecode($_GET['param']));
        $active_field = $_GET['param']['active_field'] == 'title' ? 'title' : 'body';
        $this->assign('has_status', $_GET['param']['has_status']);
        $this->assign('is_success_status', $_GET['param']['is_success_status']);
        $this->assign('status_title', t($_GET['param']['status_title']));

        // 解析模板(统一使用模板的body字段)
        $_GET['data'] = unserialize(urldecode($_GET['data']));
        $content = model('Template')->parseTemplate(t($_GET['tpl_name']), array($active_field => $_GET['data']));
        //$content		= preg_replace_callback('/((?:https?|ftp):\/\/(?:www\.)?(?:[a-zA-Z0-9][a-zA-Z0-9\-]*\.)?[a-zA-Z0-9][a-zA-Z0-9\-]*(?:\.[a-zA-Z0-9]+)+(?:\:[0-9]*)?(?:\/[^\x{4e00}-\x{9fa5}\s<\'\"“”‘’]*)?)/u',group_get_content_url, $content);
        $this->assign('content', $content[$active_field]);

        $this->assign('type', $_GET['data']['type']);
        $this->assign('type_data', $_GET['data']['type_data']);
        $this->assign('button_title', t(urldecode($_GET['button_title'])));
        $this->display();
    }

}

?>
