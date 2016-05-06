<?php

/**
 * 银行后台操作
 *
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
import('home.Action.PubackAction');

class BankAction extends PubackAction {

    public function ccb(){
        $userId = intval($_POST['userId']);
        $db_prefix = C('DB_PREFIX');
        $dao = M('money_ccb');
        $dao->table("{$db_prefix}money_ccb AS a ")
            ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
            ->order('a.id DESC')
            ->field('a.*,b.realname,b.sid');
        $map = array();
        if($_POST['userId']){
            $map['a.uid'] = $userId;
        }
        $sday = t($_POST['sday']);
        $eday = t($_POST['eday']);
        if($sday){
            $stime = strtotime($sday);
            if(!$stime){
                $this->error('开始时间格式错误');
            }
        }
        if($eday){
            $etime = strtotime($eday);
            if(!$etime){
                $this->error('结束时间格式错误');
            }
        }
        if($stime && $etime){
            $map['a.ctime'] = array('between',"$stime,$etime");
        }elseif($stime){
            $map['a.ctime'] = array(array('egt',$stime));
        }elseif($etime){
            $map['a.ctime'] = array(array('elt',$etime));
        }
        if($map){
            $dao->where($map);
        }
        $list = $dao->findPage(20);
        //var_dump($dao->getLastSql());
        $this->assign($list);
        $this->display();
    }

}

?>