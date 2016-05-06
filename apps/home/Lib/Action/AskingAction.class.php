<?php

/**
 * Created by PhpStorm.
 * User: ggbound
 * Date: 16/1/26
 * Time: 09:01
 */
import('home.Action.PubackAction');
class AskingAction extends PubackAction
{
    protected $input;

    protected $sysget;

    protected $request;

    protected $eventAsk;

    function _initialize()
    {
        parent::_initialize();
        $this->input = $_POST;
        $this->sysget = $_GET;
        $this->request = $_REQUEST;
        $this->eventAsk = M('event_ask');
    }

    /**
     * 活动问答主题列表
     */
    public function index()
    {
        $map['isdel'] = 0;
        $list = $this->eventAsk->where($map)->order('`id` DESC')->findPage(10);
        $this->assign( $list);
        $this->display();
    }

    /**
     * 新增活动问答主题模板
     */
    public function addAsk()
    {
        $id =  intval($this->sysget['id']);
        $sTime = time();
        $eTime = strtotime('+ 3day');
        if(!empty($id))
        {
            $map['id'] = $id;
            $ask = $this->eventAsk->where($map)->find();
            $this->assign($ask);
        }
        else
        {
            $this->assign('sTime',$sTime);
            $this->assign('eTime',$eTime);
        }
        $this->display();
    }

    /**
     * 新增活动问答主题数据处理
     */
    public function doAddAsk()
    {
        if(empty($this->input['name']))
        {
            $this->error('问答主题不能为空！');
        }
        if(empty($this->input['intro']))
        {
            $this->error('问答简介不能为空');
        }
        if(strtotime($this->input['sTime'])<= time())
        {
            $this->error('问答开始时间不能小于当前时间');
        }
        if(strtotime($this->input['sTime']) >= strtotime($this->input['eTime']))
        {
            $this->error('问题结束时间不能小于开始时间');
        }
        unset($this->input['__hash__']);
        $this->input['sTime'] = strtotime($this->input['sTime']);
        $this->input['eTime'] = strtotime($this->input['eTime']);
        if(empty($this->input['id']))
        {
            $this->input['cTime'] = time();
            $flag = $this->eventAsk->add($this->input);
        }
        else
        {
            $u_map['id'] = intval($this->input['id']);
            $flag = $this->eventAsk->where($u_map)->save($this->input);
        }
        if($flag)
        {
            $this->success('添加成功');
        }
        else
        {
            $this->error('添加失败');
        }
    }

    /**
     * 活动问答主题删除
     */
    public function doDeleteAsk()
    {
        if (empty($this->input['ids']))
        {
            echo 0;
            exit;
        }
        $map['id'] = array('in', t($this->input['ids']));
        echo $this->eventAsk->where($map)->setField('isDel', 1) ? '1' : '0';
    }

    /**
     * 活动问答主题增加问题列表
     */
    public function addQuestion()
    {
        $id = intval($this->sysget['id']);
        $map['id'] = $id;
        $ask = $this->eventAsk->where($map)->find();
        $q_map['askId'] = $id;
        $q_map['isDel'] = 0;
        $cnt = M('event_ask_question')->where($q_map)->count();
        $list = M('event_ask_question')->where($q_map)->select();
        foreach ($list as $k=>$v )
        {
            $options = unserialize($v['options']);
            $list[$k]['A'] = $options[0];
            $list[$k]['B'] = $options[1];
            $list[$k]['C'] = $options[2];
            $list[$k]['D'] = $options[3];
        }
        $this->assign('vote',$list);
        $this->assign('cnt',$cnt);
        $this->assign($ask);
        $this->display();
    }

    /**
     * 活动问答主题 问题模板
     */
    public function question()
    {
        $id = intval($this->sysget['id']);
        $this->assign('id',$id);
        $this->display();
    }

    /**
     * 活动问答主题 问题数据处理
     */
    public function doAddQuestion()
    {

        $qOptinos = $this->input['opt'];
        $answer = $this->input['answer'];
        $title = $this->input['title'];
        if(!in_array($answer,$qOptinos)){
            echo json_encode(array('status'=>0,'info'=>'答案不在问题选项中'));
            die;
        }
        $data['askId'] = intval($this->input['id']);
        $data['title'] = $title;
        $data['options'] = serialize($qOptinos);
        $data['answer'] = $answer;
        $data['cTime'] = time();
        $flag = M('event_ask_question')->add($data);
        if($flag)
        {
            echo json_encode(array('status'=>1,'info'=>'问题添加成功'));
            die;
        }
        else
        {
            echo json_encode(array('status'=>0,'info'=>'问题添加失败'));
            die;
        }
    }

    /**
     * 活动问答主题 删除问题数据处理
     */
    public function deleteQuestion()
    {
        $id = intval($this->input['id']);
        $map['id'] = $id;
        $data['isDel'] = 1;
        $flag = M('event_ask_question')->where($map)->save($data);
        if($flag)
        {
            echo 1;
        }
        else
        {
            echo 0;
        }
    }

    public function config()
    {
        $this->display();
    }

}