<?php

/**
 * Created by PhpStorm.
 * User: ggbound
 * Date: 16/2/3
 * Time: 12:53
 */
class AskingAction extends BaseAction
{

    protected $input;
    protected $question;
    protected $ask;
    protected $users;

    public function _initialize()
    {
        parent::_initialize();
        $this->input = $_POST;
        $this->ask = M('event_ask');
        $this->question = M('event_ask_question');
        $this->users = M('event_ask_users');
    }

    /**
     *
     * @example
     * http://pocketuni.lo/index.php?app=wap&mod=Asking&act=index&id=2
     *
     */
    public function index()
    {
        $id = intval($_GET['id']);
        if(empty($id))
        {
            echo json_encode(array('status'=>0,'msg'=>'参数非法'));
            die;
        }
        $map['id'] = $id;
        $map['sTime'] = array('LT',time());
        $map['eTime'] = array('GT',time());
        $askInfo = $this->ask->where($map)->find();
        if(empty($askInfo))
        {
            echo json_encode(array('status'=>0,'msg'=>'输入的活动问答主题已经结束或者不存在'));
            die;
        }
        $qMap['askId'] = $id;
        $qMap['isDel'] = 0;
        $qustions = $this->question->where($qMap)->field('id,title,options')->select();
        shuffle($qustions);
        foreach ( $qustions as &$v ) {
            $optionArr = unserialize($v['options']);
            shuffle($optionArr);
            $v['options'] = $optionArr;
        }

        $return['data']['title'] = $askInfo['name'];
        $return['data']['qusetion'] = $qustions;
        $return['status'] = 1;
        $return['leftTimes'] = (3 - $this->answerTime($id));
        $return['msg'] = '问题已经开始';
        echo json_encode($return);
        die;
    }

    public function answer()
    {
        $uid = $this->mid;
        $id = $_REQUEST['qid'];
        $answer = $_REQUEST['answer'];
        $nowNum = $_REQUEST['number'];
        $map['id'] = $id;
        $map['answer'] = $answer;
        $qustionInfo = $this->question->where($map)->find();
        $aid = $this->question->where('id = '.$id)->getField('askId');
        $count = $this->answerTime($aid);
        if(empty($qustionInfo))
        {
            if($count<=2)
            {
                $data['askId'] = $aid;
                $data['uid'] = $uid;
                $data['num'] = $nowNum-1;
                $data['cTime'] = time();
                $this->users->add($data);
                echo json_encode(array('status'=>0,'msg'=>'当前已经答题到'.$nowNum.'题，答题错误，请重头开始！','times'=>$count+1));
                die;
            }
            if($count >= 3)
            {
                echo json_encode(array('status'=>0,'msg'=>'当日三次机会已经使用完成！'));
                die;
            }
        }
        if($nowNum == 10)
        {
            $data['askId'] = $aid;
            $data['uid'] = $uid;
            $data['num'] = $nowNum;
            $data['cTime'] = time();
            $this->users->add($data);
            echo json_encode(array('status'=>1,'msg'=>'答题成功，请继续答题！','times'=>$count+1));
            die;
        }
        echo json_encode(array('status'=>1,'msg'=>'答题成功，请继续答题！'));
        die;
    }

    private function answerTime($askId)
    {
        $begin = strtotime(date('Y-m-d').'00:00:00');
        $end = strtotime(date('Y-m-d').'23:59:59');
        $c_map['askId'] = $askId;
        $c_map['uid'] = $this->mid;
        $c_map['cTime'] = array('BETWEEN',array($begin,$end));
        $count = $this->users->where($c_map)->count();
        return $count;
    }

    public function cover()
    {
        $this->display();
    }

    public function question()
    {
        $this->display();
    }

    //我的排名 id
    public function myRanking()
    {
        $id = $_REQUEST['id'];
        $lists = M('')->query('select * from (select a.uid,a.num from ts_event_ask_users as a where a.askId = '.$id.' ORDER BY a.num DESC) as b where b.uid = '.$this->mid.' GROUP BY b.uid ORDER BY b.num DESC');
        if(empty($lists))
        {
            echo json_encode(array('status'=>0 , 'msg'=>'暂无排名')); die;
        }
        $count = M('')->query('select * from (select a.uid,a.num from ts_event_ask_users as a where a.askId = '.$id.' ORDER BY a.num DESC) as b GROUP BY b.uid ORDER BY b.num DESC');
        $ranking = 0;
        foreach ($count as $k=>$v)
        {
            if($v['uid'] == $this->mid)
            {
                $ranking = $k+1;
            }
        }
        echo json_encode(array('status'=>1 , 'msg'=>'您的排名为：'.$ranking,'data'=>$ranking)); die;
    }

    //所有排名 需要支持排序
    public function allRanking()
    {
        $id = $_REQUEST['id'];
        $page = isset($_REQUEST['page']) && $_REQUEST['page'] > 0 ? intval($_REQUEST['page']) : 1;
        $limit = 20;
        $offset = ($page-1) * $limit;
        $lists = M('')->query('select * from (select a.uid,a.num from ts_event_ask_users as a where a.askId = '.$id.' ORDER BY a.num DESC) as b GROUP BY b.uid ORDER BY b.num DESC LIMIT '.$offset.','.$limit);
        if(empty($lists))
        {
            echo json_encode(array('status'=>0 , 'msg'=>'暂无排名')); die;
        }
        foreach ($lists as $k=>$v)
        {
            $lists[$k]['uname'] = getUserField($v['uid'],'uname');
        }
        echo json_encode(array('status'=>1 , 'msg'=>'排名信息存在','data'=>$lists)); die;
    }

}