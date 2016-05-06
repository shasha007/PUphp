<?php
 header("Content-type:text/html;charset=utf-8");
class ActivityAction extends Action 
{
	/**
	 * 活动首页
	 */
    public function index() {
        $act_id = intval($_GET['act_id']);
        $oauth_token = h($_GET['oauth_token']);
        $oauth_token_secret = h($_GET['oauth_token_secret']);
        if(!$act_id) die();
        if(!$this->mid) redirect(U('home/Wxlog/login',array('act_id' => $act_id)));
        if(empty($oauth_token)){
            $oauth_token = D('login')->getField('oauth_token', 'type="location" and uid='.$this->mid);
            $oauth_token_secret = D('login')->getField('oauth_token_secret', 'type="location" and uid='.$this->mid);
        }
        $map = array('id'=>$act_id);
        //活动数据
        $activity = M('activity')->where($map)->find();
    	$uinfo = M('user')->where(array('uid'=>$this->mid))->find();

        $res = $this->checkData($act_id, $activity,$uinfo);

        $this->assign('act_id',$act_id);
        $this->assign('oauth_token',$oauth_token);
        $this->assign('oauth_token_secret',$oauth_token_secret);
        $this->assign('activity',$activity);
        
        //活动奖品
        $activity_prize = M('activity_prize a')->where(array('act_id'=>$act_id))
        										->field('a.*,b.name,b.price,b.img,b.desc')
        										->join('ts_prizelist b ON a.prize_id=b.id')
        										->order('a.sort asc')
        										->findAll();
        //"50M免费流量包", "10闪币",  "5闪币","谢谢参与", "10M免费流量包", "20M免费流量包", "20闪币 ","谢谢参与",
        
        $prizeJs = array();
        $prizeColorJs = array();
        foreach($activity_prize as $k => $v){
        	$activity_prize[$k]['cols'] = unserialize($v['cols']);
        	$attach = getAttach($v['img']);
        	$file = $attach['savepath'] . $attach['savename'];
        	$activity_prize[$k]['pic_o'] = tsMakeThumbUp($file, 60, 60, 'no');
        	$activity_prize[$k]['pic_m'] = tsMakeThumbUp($file, 100, 100, 'f');
        	
        	//拼接js奖品
        	$prizeJs[] = '"'.$v['name'].'"';
        	$prizeColorJs[] = '"#FFF4D6"';
        }
        $prizeJs[] = '"谢谢参与"';
        $prizeColorJs[] = '"#FFF4D6"';
        $this->assign('prizeJs',implode(',',$prizeJs));
        $this->assign('prizeColorJs',implode(',',$prizeColorJs));
        $this->assign('activity_prize',$activity_prize);
        
        //剩余抽奖次数
        $person_day_join_count = $activity['person_day_join_count'];
        $surpCount = $this->chkPersonDayJoinCount($person_day_join_count,$act_id);
        $this->assign('surpCount',$surpCount);

        $this->display();
    }
    
    /**
     * 每人每日可参与次数
     * @param unknown $person_day_join_count
     */
    private function chkPersonDayJoinCount($person_day_join_count,$act_id)
    {
    	$stime = date('Y-m-d 00:00:00');
    	$etime = date('Y-m-d 23:59:59');
    	$where = array(
    			'act_id'=>$act_id,
    			'uid'=>$this->mid,
    			'ctime'=>array('BETWEEN',array('"'.$stime.'"','"'.$etime.'"')),
    	);
    	$count = M('activity_log')->where($where)->count();
    	if($person_day_join_count <= $count) return 0;
    	return $person_day_join_count-$count;
    }
    
    /**
     * 获取活动状态
     */
    private function getStatus($stime,$etime)
    {
    	$now = date('Y-m-d H:i:s');
    	if($now < $stime) return array('code'=>1,'msg'=>'活动未开始','is_redirect'=>true);
    	if($now > $etime) return array('code'=>2,'msg'=>'很遗憾，活动已经结束！','is_redirect'=>true);
    	if($now >= $stime && $now <= $etime) return array('code'=>3,'msg'=>'活动正在进行','is_redirect'=>false);
    }
    
    private function checkData($act_id,$activity,$uinfo)
    {
    	//活动地区
    	$activity_area = M('activity_area')->where(array('act_id'=>$act_id))->findAll();
    	$isAllArea = false;//是否为全部地区允许参与 false 否
    	foreach($activity_area as $k => $v) {
    		if($v['area_id'] == '0') {
    			$isAllArea = true;
    			break;
    		}
    	}
    	
    	if(!$isAllArea) {
    		//根据学生地区学校，判断地区学校
    		$city_id = $uinfo['city'];
    		$school_id = $uinfo['sid'];
    	
//     		$getAreaByUcity = M('activity_area')->where(array('act_id'=>$act_id,'area_id'=>$city_id))->count();
    		$getSchoolByUschool = M('activity_school')->where(array('act_id'=>$act_id,'school_id'=>$school_id))->count();

//     		if(!$getAreaByUcity || !$getSchoolByUschool) {
//     			redirect(U('Shop/Activity/showStatus',array('act_id'=>$act_id,'status'=>'您不在此次活动的地区及学校范围')));
//     		}
    		if(!$getSchoolByUschool) {
    			redirect(U('Shop/Activity/showStatus',array('act_id'=>$act_id,'status'=>'您不在此次活动的地区及学校范围')));
    		}
    	}
    	
    	//判断状态
    	$status = $this->getStatus($activity['stime'],$activity['etime']);
    	if($status['is_redirect']) redirect(U('Shop/Activity/showStatus',array('act_id'=>$act_id,'status'=>$status['msg'])));
    	
    	return true;
    }
    
    /**
     * 展示结果
     */
    public function showStatus()
    {
    	$act_id = intval($_GET['act_id']);
    	$msg = $_GET['msg'];

    	$this->assign('act_id',$act_id);
    	$this->assign('msg',$msg);
    	
    	$this->display();
    }
    
    /**
     * 中奖填写信息页面
     */
    public function addInfo()
    {
    	//活动奖品纪录id
    	$id = intval($_GET['id']);
    	//活动id
    	$act_id = intval($_GET['act_id']);
    	//奖品id
    	$prize_id = intval($_GET['prize_id']);
    	//中奖纪录id
    	$win_prize_id = intval($_GET['win_prize_id']);

    	//需要填写数据
    	$map = array('act_id'=>$act_id,'prize_id'=>$prize_id,'id'=>$id);
    	$res = M('activity_prize')->where($map)->field('cols')->find();

    	$cols = unserialize($res['cols']);

    	$this->assign('win_prize_id',$win_prize_id);
    	$this->assign('act_id',$act_id);
    	$this->assign('cols',$cols);
    	 
    	$this->display();
    }
    
    /**
     * 中奖纪录
     */
    public function winlist()
    {
    	$res = M('prize_sn')->where(array('uid'=>$this->mid,'act_id'=>intval($_GET['act_id'])))->order('id DESC')->findAll();
    	
    	foreach($res as $k => $v)
    	{
    		$prize = M('prizelist')->where(array('id'=>$v['prize_id']))->find();
    		$res[$k]['prizename'] = $prize['name'];
    	}
    	
    	$this->assign('data',$res);
    	
    	$this->display();
    }
}

?>
