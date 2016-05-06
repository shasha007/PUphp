<?php

/**
 * Created by PhpStorm.
 * User: zhuhaibing06
 * Date: 2016/2/22
 * Time: 16:02
 */
class AiplayAction extends Action
{

    private $objJpush;

    private $sendTimes;

    private $jPushUrl;

    function _initialize()
    {
        $this->sendTimes = 0;
        include_once(SITE_PATH.'/addons/libs/Jpush3.class.php');
        $this->objJpush = new Jpush();
        $this->jPushUrl = 'https://api.jpush.cn/v3/push';
    }

    /**
     * 爱游戏激光推送接口-给爱游戏使用的
     * @return mixed
     */
    public function index()
    {
        if(empty($_REQUEST['title']) || empty($_REQUEST['content']) || empty($_REQUEST['fullname']) || empty($_REQUEST['extra']))
        {
            $return['status'] = 1;
            $return['msg'] = '爱游戏推送过来的信息不完整';
            echo json_encode($return);die;
        }
        $data['title'] = $_REQUEST['title'];
        $data['content'] = $_REQUEST['content'];
        $data['fullname'] = $_REQUEST['fullname'];
        $data['extra'] = serialize($_REQUEST['extra']);
        $data['cTime'] = time();
        $addPushInfo = M('aiplay_jpush')->add($data);
        if($addPushInfo)
        {
            $sTime = strtotime(date('Y-m-d').' 00:00:00');
            $eTime = strtotime(date('Y-m-d').' 23:59:59');
            $map['cTime'] = array('BETWEEN',array($sTime,$eTime));
            $countPushInfo = M('aiplay_jpush')->where($map)->count();
            if($countPushInfo <= $this->sendTimes)
            {
                $pushInfo = M('aiplay_jpush')->where($map)->order('id DESC')->limit(1)->select();
                //TODO 极光推送
                $sendStatus = $this->jPushSend($pushInfo[0]['title'],$pushInfo[0]['content'],$pushInfo[0]['fullname'],$pushInfo[0]['extra']);
                if($sendStatus['status'] === true)
                {
                    $return['status'] = 0;
                    $return['msg'] = '爱游戏信息推送成功';
                    echo json_encode($return);die;
                }
                else
                {
                    $return['status'] = 1;
                    $return['msg'] = $sendStatus['msg'];
                    echo json_encode($return);die;
                }
            }
            else
            {
                $return['status'] = 1;
                $return['msg'] = '当前推送信息数量已经超过当天指标';
                echo json_encode($return);die;
            }
        }
        else
        {
            $return['status'] = 1;
            $return['msg'] = 'PocketUni服务器发生异常，推送信息失败';
            echo json_encode($return);die;
        }
    }

    /**
     * 极光信息推送
     * @param $title
     * @param $content
     * @return mixed
     */
    private function jPushSend($title, $content, $fullname, $extra)
    {
        $extras = array(
                    'fullname' => $fullname,
                    'params'=> unserialize($extra),
                    );
        $params['platform'] = 'all';
        $params['audience'] = 'all';
    	//$params['audience']['alias'] = array('1448764');
        $params['notification']['android']['alert'] = $content;
        $params['notification']['android']['title'] = $title;
        $params['notification']['android']['builder_id'] = 3;
        $params['notification']['android']['extras'] = $extras;
        $params['notification']['ios']['alert'] = $content;
        $params['notification']['ios']['sound'] = 'default';
        $params['notification']['ios']['badge'] = 1;
        $params['notification']['ios']['extras'] = $extras;
        $params['options'] = array('sendno'=>2211,'apns_production'=>true);
        $params = json_encode($params);
        $res = $this->objJpush->request_post($this->jPushUrl,$params);
        $try = 1;
        while($res===false && $try<=5){
            sleep(1);
            $res = $this->objJpush->request_post($this->jPushUrl,$params);
            $try += 1;
        }
        $return['status'] = false;
        if ($res === false) {
            $return['msg'] = 'request_post返回false';
            return $return;
        }
        $res_arr = json_decode($res, true);
        if(isset($res_arr['error']) && isset($res_arr['code'])){
            $return['msg'] = $res_arr['error']['code'];
            return $return;
        }
        $return['status'] = true;
        $return['msg'] = '极光推送成功';
        return $return;
    }

}