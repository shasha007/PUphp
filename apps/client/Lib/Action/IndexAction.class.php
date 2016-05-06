<?php

/**
 * Created by PhpStorm.
 * User: ggbound
 * Date: 16/3/30
 * Time: 21:07
 * 管理APP客户端开关的功能
 */
import('home.Action.PubackAction');
class IndexAction extends PubackAction
{

    function _initialize()
    {
        parent::_initialize();
    }

    public function index()
    {
        $this->display();
    }

    /**
     * APP首页广告下面的几个BUTTON按钮列表
     */
    public function appList()
    {
        $map['status'] = 0;
        $map['isDel'] = 0;
        $list = M('entry_app')->where($map)->order('sort ASC,id DESC')->findPage(10);
        foreach($list['data'] as $v){
            $v['isShowAndroid'] = intval($v['isShowAndroid']);
            $v['isShowIos'] = intval($v['isShowIos']);
        }
        $this->assign( $list);
        $this->display();
    }

    /**
     * APP 新增页面
     */
    public function addApp()
    {
        if(!empty($_GET['id']))
        {
            $map['id'] = intval($_GET['id']);
            $data = M('entry_app')->where($map)->find();
            $this->assign($data);
        }
        $this->display();
    }

    /**
     * APP 新增数据处理
     */
    public function doAddApp()
    {
        $data = $_POST;
        unset($data['__hash__']);
        if($data['type'] == 1)
        {
            if(empty($data['code']))
            {
                $this->error('原生必须输入APP CODE');
            }
        }
        if(empty($data['name']))
        {
            $this->error('APP名称不能为空');
        }
        if(empty($data['desc']))
        {
            $this->error('APP描述不能为空');
        }
        if(!empty($data['ico']))
        {
            if(!empty($_FILES['pic']['name']))
            {
                $images = tsUploadImg($this->mid,'App',true);
                if (!$images['status'] && $images['info'] != '没有选择上传文件')
                {
                    $this->error($images['info']);
                }
                $data['ico'] = $this->getPicUrl($images);
            }
            else
            {
                $data['ico'] = $data['ico'];
            }
        }
        else
        {
            if(empty($_FILES['pic']['name']))
            {
                $this->error('APP图标不能为空');
            }
            $images = tsUploadImg($this->mid,'App',true);
            if (!$images['status'] && $images['info'] != '没有选择上传文件')
            {
                $this->error($images['info']);
            }
           $data['ico'] = $this->getPicUrl($images);
        }
        if($data['type'] == 2)
        {
            if(empty($data['url']))
            {
                $this->error('H5必须输入外链地址');
            }
        }
        if(empty($data['id']))
        {
            $flag = M('entry_app')->add($data);
        }
        else
        {
            $map['id'] = $data['id'];
            unset($data['id']);
            $flag = M('entry_app')->where($map)->save($data);
        }
        if($flag)
        {
            $this->success('操作成功');
        }
        else
        {
            $this->error('操作失败');
        }
    }

    /**
     * 修改当前APP所在平台的展示
     */
    public function editOptions()
    {
        $map['id'] = intval($_POST['id']);
        if(!empty($_POST['isShowAndroid']))
        {
            $data['isShowAndroid'] = $_POST['isShowAndroid']== 2 ? 0 : 1;
        }
        if(!empty($_POST['isShowIos']))
        {
            $data['isShowIos'] = $_POST['isShowIos'] == 2 ? 0 :1 ;
        }
        if(!empty($_POST['sort']))
        {
            $data['sort'] = $_POST['sort'];
        }
        M('entry_app')->where($map)->save($data);
        echo 1;
    }

    /**
     * 删除 APP
     */
    public function doDeleteApp()
    {
        if (empty($_POST['ids'])) {
            echo 0;
            exit;
        }
        $map['id'] = array('in', t($_POST['ids']));
        echo M('entry_app')->where($map)->setField('isDel', 1) ? '1' : '0';
    }

    /**
     * 根据返回的图片信息获取对应的图片地址详细信息
     * @param array $img
     * @return string
     */
    protected function getPicUrl($img)
    {
        $picUrl = $img['info'][0]['savepath'].$img['info'][0]['savename'];
        return $picUrl;
    }

}