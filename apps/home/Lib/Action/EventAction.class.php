<?php

/**
 * Created by PhpStorm.
 * User: zhuhaibing06
 * Date: 2016/3/10
 * Time: 11:16
 */
import('home.Action.PubackAction');
class EventAction extends PubackAction
{

    /**
     * 专题活动管理后台首页
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 专题分类管理
     */
    public function category()
    {
        $map['isDel'] = 0;
        $map['status'] = 0;
        $lists = M('event_series')->where($map)->findpage(20);
        $this->assign('list',$lists);
        $this->display();
    }

    /**
     * 新增专题分类 模板
     */
    public function addCategory()
    {
        $id = intval($_GET['id']);
        if(!empty($_GET['id']))
        {
            $map['id'] = $id;
            $info = M('event_series')->where($map)->find();
            $this->assign('info',$info);
        }
        $this->display();
    }

    /**
     * 新增专题分类 数据处理
     */
    public function doAddCategory()
    {
        $data = $_POST;
        unset($data['__hash__']);
        if(empty($data['id']))
        {
            unset($data['id']);
            $data['cTime'] = time();
            $flag = M('event_series')->add($data);
        }
        else
        {
            $map['id'] = intval($data['id']);
            unset($data['id']);
            $flag = M('event_series')->where($map)->save($data);
        }
        if($flag)
        {
            $this->redirect('home/Event/category');
        }
        else
        {
            $this->error('操作失败');
        }
    }

    /**
     * 专题活动配置页
     */
    public function config()
    {
        $map['isDel'] = 0;
        $map['status'] = 0;
        $lists = M('event_series')->where($map)->findpage(20);
        foreach($lists['data'] as &$v)
        {
            $v['sTime'] = date('Y-m-d H:i:s',$v['sTime']);
            $v['eTime'] = date('Y-m-d H:i:s',$v['eTime']);
        }
        $this->assign('list',$lists);
        $this->display();
    }

    /**
     * 专题活动配置数据处理
     */
    public function doConfig()
    {
        $return  = array(
            'status'=>1,
            'msg'=>'操作成功',
        );
        $data = $_POST;
        if(empty($data['id']))
        {
            $return['status'] = 0;
            $return['msg'] = '参数错误';
            echo json_encode($return); die;
        }
        $map['id'] = intval($data['id']);
        unset($data['id']);
        $data['sTime'] = strtotime($data['sTime']);
        $data['eTime'] = strtotime($data['eTime']);
        $flag = M('event_series')->where($map)->save($data);
        if(!$flag)
        {
            $return['status'] = 0;
            $return['msg'] = '操作失败';
            echo json_encode($return); die;
        }
        echo json_encode($return); die;
    }

}