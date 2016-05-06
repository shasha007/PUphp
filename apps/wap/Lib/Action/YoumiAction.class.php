<?php

/**
 * Created by PhpStorm.
 * User: zhuhaibing06
 * Date: 2016/4/18
 * Time: 14:34
 */
class YoumiAction extends BaseAction
{

    public function youmi()
    {
        $scoreInfo = M('youmi_score')->where('uid = '.$this->mid)->find();
        $scoreConfig = M('youmi_score_config')->where('id = 1')->find();
        $heightPU = intval($scoreInfo['score']/$scoreConfig['score']);
        $this->assign($scoreInfo);
        $this->assign('pu',$heightPU);
        $this->assign('scoreConfig',$scoreConfig);
        $this->display();
    }

    public function exchange()
    {
        $pu = $_POST['pu'];
        if($pu[0] === '-' || $pu[0] === '0')
        {
            $return['status'] = 0;
            $return['msg'] = '兑换PU币必须大于等于1';
            echo json_encode($return);exit;
        }

        $scoreConfig = M('youmi_score_config')->where('id = 1')->find();
        $needScore = $pu*$scoreConfig['score'];

        $scoreInfo = M('youmi_score')->where('uid = '.$this->mid)->find();
        if(empty($scoreInfo))
        {
            $return['status'] = 0;
            $return['msg'] = '您当前还未获取到积分';
            echo json_encode($return);exit;
        }

        if($scoreInfo['score']<$needScore)
        {
            $return['status'] = 0;
            $return['msg'] = '兑换所需积分大于您所拥有的积分';
            echo json_encode($return);exit;
        }

        M('')->startTrans();

        //将用户信息写入积分明细表
        $l_data['uid'] = $this->mid;
        $l_data['score'] = $needScore;
        $l_data['type'] = 2;  //1为增加积分  2为消耗积分
        $l_data['cTime'] = time();
        $record = M('youmi_score_log')->add($l_data);

        //检测记录是否成功
        if(!$record)
        {
            $return['status'] = 0;
            $return['msg'] = '积分记录失败';
            echo json_encode($return);exit;
        }

        $map['uid'] = $this->mid;
        //有 更新积分记录
        $flag = M('youmi_score')->setDec('score', $map, $needScore);

        if($flag === FALSE)
        {
            $return['status'] = 0;
            $return['msg'] = '更新用户积分失败';
            echo json_encode($return);exit;
        }

        $m_data['uid'] = $this->mid;
        $m_data['typeName'] = '有米积分兑换';
        $m_data['logMoney'] = $pu*100;
        $m_data['ctime'] = time();

        $addMoneyLog = M('money_in')->add($m_data);
        $m_map['uid'] = $this->mid;
        $checkMoney = M('money')->where($m_map)->find();
        if(empty($checkMoney))
        {
            $moneyAdd['uid'] = $this->mid;
            $moneyAdd['money'] = $pu*100;
            $moneyAdd['ctime'] = time();
            $updateMoney = M('money')->add($moneyAdd);
        }
        else
        {
            $updateMoney = M('money')->setInc('money',$m_map,$pu*100);
        }
        if($addMoneyLog===FALSE || $updateMoney === FALSE)
        {
            M('')->rollback();
            $return['status'] = 0;
            $return['msg'] =  '操作失败';
            echo json_encode($return);exit;
        }

        M('')->commit();
        $return['status'] = 1;
        $return['msg'] =  '操作成功';
        echo json_encode($return);exit;
    }

}