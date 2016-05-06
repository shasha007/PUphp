<?php 
class ShowAction extends BaseAction{
	public function index(){
        $pp = intval($_REQUEST['pge']) ;
        if ($pp<1) {
            $pp = 0 ;
        }elseif($pp<6){
            $pp = 10*($pp-1) ;
        }else{
            echo 0 ; die ;
        }
        $result = $this->get_red($pp) ;
        $this->assign('result',$result) ;
        if ($pp > 1) {
            $this->display('get_more_show') ; die ;
        }else{
            $this->display('index') ;    
        }
    }
    //获取红人数据
    private function get_red($pp=0){
        S('show_red_peoples',null) ;
        $uids = S('show_red_peoples') ;
        if (empty($uids)) {
            $map['ts_user.year'] = array('NEQ','') ;
            //$map['ts_user.sid'] = array('NEQ',473) ;
            $sql = "SELECT ts_user.uid,ts_user.sid,ts_user.ctime,ts_user.uname,ts_user.year FROM `ts_credit_user` force index (score) LEFT JOIN ts_user on ts_user.uid = ts_credit_user.uid where ts_user.year !='' and ts_user.sid NOT in (473) order by ts_credit_user.score desc LIMIT 50";
            $uids = M()->query($sql) ;
            //$uids = M('user')->field('ts_user.uid,ts_user.sid,ts_user.ctime,ts_user.uname,ts_user.year')->where($map)->join('LEFT JOIN ts_credit_user c on ts_user.uid = c.uid')->order('c.score desc')->limit(50)->select() ;
            $uids = json_encode($uids) ;
            S('show_red_peoples',$uids) ;
        }        
        $uids = json_decode($uids,true) ;
        $result = array_slice($uids,$pp,10) ;
        foreach ($result as $key => $value) {
            $result[$key]['title'] = tsGetSchoolName($value['sid']) ;
            $result[$key]['is_friend'] = checkFriden($this->mid,$value['uid']) ;
            $result[$key]['face'] = getUserFace($value['uid'],'b') ;
        }
        return $result ;
    }
}




 ?>