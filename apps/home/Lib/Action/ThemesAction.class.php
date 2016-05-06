<?php

/**
 * @todo  PU管理后台 微博话题模块
 * Created by PhpStorm.
 * User: zhuhaibing06
 * Date: 2016/1/20
 * Time: 14:42
 */
import('home.Action.PubackAction');
class ThemesAction extends PubackAction
{

    protected $input;

    protected $sysget;

    protected $file;

    protected $model;

    protected $weibo;

    protected $weiboConfig;

    function _initialize()
    {
        parent::_initialize();
        $this->input = $_POST;
        $this->request = $_REQUEST ;
        $this->file = $_FILES;
        $this->sysget = $_GET;
        $this->model = M('weibo_themes');
        $this->weibo = D('Weibo');
        $this->weiboConfig = M('weibo_themes_config');
    }

    /**
     * @todo 话题广场话题列表
     */
    public function index()
    {
        $this->assign('isSearch', isset($this->input['isSearch']) ? '1' : '0');
        $this->input['themes_id'] && $map['id'] = t($this->input['themes_id']);
        $this->input['content'] && $map['name'] = array('LIKE','%'.t($this->input['content']).'%');

        if ($this->input['isShow'] === '0') {
            $map['isShow']  = 0 ;
        }elseif ($this->input['isShow'] === '1') {
            $map['isShow']  = 1 ;
        }
        $map['isDel'] = 0;
        $list = $this->model->where($map)->order('orderoption asc, id DESC')->findPage(10);
        $this->assign( $list);
        $this->assign($this->input);
        $this->display();
    }

    /**
     * @todo 话题广场新增话题-模板
     */
    public function addThemes()
    {
        $id = $this->sysget['id'];
        if(!empty($id))
        {
            $map['id'] = intval($id);
            $themes = $this->model->where($map)->find();
            $this->assign($themes);
        }
        $this->display();
    }

    /**
     * @todo 话题广场新增话题-数据处理
     */
    public function doAddThemes()
    {
        if(empty($this->input['name']))
        {
            $this->error('话题不能为空！');
        }
        if(empty($this->input['intro']))
        {
            $this->error('话题简介不能为空');
        }

        if(!empty($this->input['exsitPic']))
        {
            if(!empty($this->file['pic']['name']))
            {
                $images = tsUploadImg($this->mid,'Themes',true);
                if (!$images['status'] && $images['info'] != '没有选择上传文件')
                {
                    $this->error($images['info']);
                }
                $this->input['pic'] = $this->getPicUrl($images);
            }
            else
            {
                $this->input['pic'] = $this->input['exsitPic'];
            }
        }
        else
        {
            if(empty($this->file['pic']['name']))
            {
                $this->error('请上传话题LOGO！');
            }
            $images = tsUploadImg($this->mid,'Themes',true);
            if (!$images['status'] && $images['info'] != '没有选择上传文件')
            {
                $this->error($images['info']);
            }
            $this->input['pic'] = $this->getPicUrl($images);
        }
        unset($this->input['__hash__']);
        if(empty($this->input['id']))
        {
            $this->input['ctime'] = time();
            $flag = $this->model->add($this->input);
        }
        else
        {
            $map['id'] = intval($this->input['id']);
            unset($this->input['id']);
            $flag = $this->model->where($map)->save($this->input);
        }
        if($flag)
        {
            $this->success('添加成功！');
        }
        else
        {
            $this->error('添加失败！');
        }
    }

    /**
     * @todo 删除话题广场中的话题 -- 伪删除
     */
    public function doDeleteThemes()
    {
        if (empty($this->input['ids'])) {
            echo 0;
            exit;
        }
        $map['id'] = array('in', t($this->input['ids']));
        echo $this->model->where($map)->setField('isDel', 1) ? '1' : '0';
    }

    /**
     * @todo 根据返回的图片信息获取对应的图片地址详细信息
     * @param array $img
     * @return string
     */
    protected function getPicUrl($img)
    {
        $picUrl = $img['info'][0]['savepath'].$img['info'][0]['savename'];
        return $picUrl;
    }

    /**
     * @todo  话题微博列表
     */
    public function weibo()
    {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_REQUEST))
        {
            $_SESSION['weibo_search'] = serialize($this->input);
        }
        else if(isset($_GET[C('VAR_PAGE')]))
        {
            $this->input = unserialize($_SESSION['weibo_search']);
        }
        else
        {
            unset($_SESSION['weibo_search']);
        }

        $this->assign('isSearch', isset($this->input['isSearch']) ? '1' : '0');
        $this->input['wid'] && $map['weibo_id'] = t($this->input['wid']);
        $this->input['uid'] && $map['uid'] = t($this->input['uid']);
        $this->input['isTop'] && $map['isTop'] = intval($this->input['isTop']);
        $_REQUEST['themes_id'] && $map['themes_id'] = intval($_REQUEST['themes_id']);
        $this->input['content'] && $map['content'] = array('LIKE','%'.t($this->input['content']).'%');
        $order = ( $this->input['orderkey'] && $this->input['ordertype'] ) ? $this->input['orderkey'].' '.$this->input['ordertype'] : 'isTop DESC , weibo_id DESC';
        $map['isdel'] = 0;
        $data['list'] = $this->weibo->order($order)->where($map)->findpage(20);
        foreach ($data['list']['data'] as $k=>$v ) {
            $data['list']['data'][$k]['themesname'] = $this->getThemesTitle($v['themes_id']);
        }
        if(is_array($map) && sizeof($map)=='1')
        {
            unset($map);
        }
        $this->assign($this->sysget);
        $this->assign($data);
        $this->assign($this->input);
        $this->display();
    }

    protected function getThemesTitle($id)
    {
        $map['id'] = $id;
        $name = $this->model->where($map)->getField('name');
        if(empty($name))
        {
            $name = '不属于任何话题的微博';
        }
        return $name;
    }

    /**
     * @todo  是否设置为头条
     */
    public function weiboSave()
    {
        $weibo_id = $this->input['weibo_id'];
        $map['weibo_id'] = intval($weibo_id);
        $getWeiboThemesId = $this->weibo->where($map)->getField('themes_id');
        $res = array('status'=>0,'msg'=>'设置成功');
        if($this->input['flag'] == 1)
        {
            $c_map['themes_id'] = $getWeiboThemesId;
            $c_map['isTop'] = 1;
            $checkThemesIsTopCount = $this->weibo->where($c_map)->count();
            if($checkThemesIsTopCount >= 3)
            {
                $res['status'] = 2;
                $res['msg'] = '当前话题设置头条已经有3条了！';
                echo json_encode($res); exit;
            }
        }
        $isTop = intval($this->input['flag']);
        $result = M()->query('UPDATE `ts_weibo` SET `isTop` = \''.$isTop.'\' WHERE `weibo_id` = '.$weibo_id);
        // 设置头条发消息给用户
        if ($isTop === 1) {
            $receive = weibo_get_uid($map['weibo_id']) ;
            $weibo['weibo_id'] = $weibo_id ;
            add_notify($weibo,$receive,'home_weibo_top') ;
        }
        if(!empty($result))
        {
            $res['status'] = 1;
            $res['msg'] = '设置失败';
        }
        echo json_encode($res);
        exit;
    }

    /**
     * @todo pu账号与话题绑定管理页面
     */
    public function tConfig()
    {
        $puArr = $this->weiboConfig->field('uid')->findAll();
        $themesArr = $this->weiboConfig->field('themesId')->findAll();
        foreach($puArr as $pk=>$pv)
        {
            $pu[] = $pv['uid'];
        }
        foreach($themesArr as $tk=>$tv)
        {
            $themes[] = $tv['themesId'];
        }
        $puString = implode('|',$pu);
        $themesString = implode('|',$themes);
        $this->assign('pu',$puString);
        $this->assign('themes',$themesString);
        $this->display();
    }

    /**
     * @todo PU账号与话题绑定管理数据处理
     */
    public function doConfig()
    {
        $puIdArr = explode('|',$this->input['pu']);
        $themesIdArr = explode('|',$this->input['themes']);
        if(count($puIdArr) != count($themesIdArr))
        {
            $this->error('PU账号与话题数量不匹配，请检测后重新提交！');
        }
        M()->query('TRUNCATE `ts_weibo_themes_config`;');
        foreach($puIdArr as $k=>$v)
        {
            $data['uid'] = $v;
            $data['themesId'] = $themesIdArr[$k];
            $this->weiboConfig->add($data);
        }
        $this->success('配置成功！');
    }

    /**
    * @todo 排序修改
    */
    public function sequence(){
        $map['id'] = intval($_POST['id']) ;
        $data['orderoption'] = intval($_POST['orderoption']) ;
        $result = $this->model->where($map)->save($data) ;
        if ($result !== false) {
            echo 1 ;
            die ;
        }
        echo 0 ;
    }

    /*
    * 开启关闭微博
    */
    public function open(){
        $map['id'] = intval($this->input['id']) ;
        if (intval($this->input['status']) === 1 ) {
            $data['isShow'] = 1 ;
        }else{
            $data['isShow'] = 0 ;
        }
        if (M('weibo_themes')->where($map)->save($data) !==false) {
            echo 1 ;die ;
        }
        echo 0 ;
    }

    /*
    * 话题点击详情
    */
    public function themes_hit(){
        if (!empty($_REQUEST))
        {
            $_SESSION['themes_hit'] = serialize($this->input);
        }
        else if(isset($_GET[C('VAR_PAGE')]))
        {
            $this->input = unserialize($_SESSION['themes_hit']);
        }
        else
        {
            unset($_SESSION['themes_hit']);
        }       
        $type = t($this->input['type']) ;
        $stime = intval(strtotime(t($this->input['stime']))) ;
        $etime = intval(strtotime(t($this->input['etime']))) ;
        if ($stime) {
            $map['time'] = array('GT',$stime) ;
        }
        if ($etime) {
            if ($map['time']) {
                $map['time'] = array('BETWEEN',"$stime,$etime") ;
            }else{
                $map['time'] = array('LT',$etime) ;
            }
        }
        $map['themes_id'] = intval($_REQUEST['themes_id']) ;
        $list = M('weibo_themes_hit')->where($map)->order('id desc')->findpage('20') ;
        $this->assign($this->input) ;
        $this->assign('themes_id',$map['themes_id']) ;
        $this->assign($list) ;
        $this->display() ;
    }

    //excle 导出数据
    public function excle_hit(){
        $stime = intval(strtotime(t($this->input['stime']))) ;
        $etime = intval(strtotime(t($this->input['etime']))) ;
        if ($stime) {
            $map['time'] = array('GT',$stime) ;
        }
        if ($etime) {
            if ($map['time']) {
                $map['time'] = array('BETWEEN',"$stime,$etime") ;
            }else{
                $map['time'] = array('LT',$etime) ;
            }
        }
        $map['themes_id'] = intval($_REQUEST['themes_id']) ;
        $info = M('weibo_themes_hit')->where($map)->order('id desc')->select() ;
        foreach ($info as $key => $value) {
            $row = array() ;
            $row[] = $value['id'] ;
            $row[] = $value['themes_id'] ;
            $row[] = $value['uid'] ;
            $row[] = date('Y-m-d H:i:m',$value['time']) ;
            $list[] = $row ;
        }
        $arr = array('id', '话题id', '用户id', '打开时间');
        array_unshift($list, $arr);
        service('Excel')->export2($list, '话题点击详情','18');
    }

    //话题位置绑定
    public function doaddplace(){
        $data['place'] = t($_REQUEST['place']) ;
        $data['themes_id'] = intval($_REQUEST['themes']) ;
        $result = D('WeiboThemes','weibo')->doaddplace($data) ;
        if ($result['code'] == 1) {
            $this->success($result['msg']) ;
        }else{
            $this->error($result['msg']) ;
        }
    }

    //话题位置列表
    public function placeList(){
        $list = M('weibo_themes_dispaly')->select() ;
        $this->assign('list',$list) ;
        $this->display() ;
    }

    //话题位置配置删除
    public function deletePlace(){
        $id = explode(',', t($_REQUEST['id'])) ;
        $map['id'] = array('IN',$id) ;
        if (M('weibo_themes_dispaly')->where($map)->delete()) {
            echo  1 ; die ;
        }
        return 0 ;
    }
    // 微博话题配置
    public function changetheme(){
        $map['isShow'] = 1 ;
        $map['isDel'] = 0 ;
        $result = M('weibo_themes')->where($map)->field('id,name')->order('id desc')->select() ;
        $this->assign('result',$result) ;
        $this->assign($_REQUEST) ;
        $this->display() ;
    }

    // 微博话题配置
    public function dochangethemes(){
        if (intval($_REQUEST['weibo_id']) < 1) {
            $this->error('参数错误') ;
        }
        if (intval($_REQUEST['themes_id']) < 1) {
            $this->error('参数错误') ;
        }
        $map['weibo_id'] = intval($_REQUEST['weibo_id']) ;
        $data['themes_id'] = intval($_REQUEST['themes_id']) ;
        if (M('weibo')->where($map)->save($data) !== false) {
            $this->success('修改成功') ;
        }
        $this->error('修改失败') ;
    }
    
}