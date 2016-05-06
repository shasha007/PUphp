<?php

/**
 * Created by PhpStorm.
 * User: zhuhaibing06
 * Date: 2016/4/18
 * Time: 9:48
 */
import('home.Action.PubackAction');
class YoumiAction extends PubackAction
{

    function _initialize()
    {
        parent::_initialize();
    }

    public function youMi()
    {
        $info = M('youmi_score_config')->where('id = 1')->find();
        $this->assign($info);
        $this->display();
    }

    public function doSaveConfig()
    {
        $data['pu'] = $_POST['pu'];
        $data['score'] = $_POST['score'];
        $check = M('youmi_score_config')->where('id = 1')->find();
        if(empty($check))
        {
            M('youmi_score_config')->add($data);
        }
        M('youmi_score_config')->where('id = 1')->save($data);
        $this->success('保存成功');
    }

}