<?php 



class DiscoverAction extends BaseAction{
	
	public function index(){
        // 点击量+1
        D('WeiboDiscvoerHit','weibo')->hitAdd($this->mid) ;
        //$show = M('money')->order('money desc')->where($map)->limit(4)->select() ;
        $sql = "SELECT ts_user.uid,ts_user.sid,ts_user.ctime,ts_user.uname,ts_user.year FROM `ts_credit_user` force index (score) LEFT JOIN ts_user on ts_user.uid = ts_credit_user.uid where ts_user.year !='' and ts_user.sid NOT in (473) order by ts_credit_user.score desc LIMIT 4";
        $show = M()->query($sql) ;
        foreach ($show as $key => $value) {
            $sid = getUserField($value['uid'],'sid') ;
            $show[$key]['uname'] = getUserField($value['uid'],'uname') ;
            $show[$key]['title'] = tsGetSchoolName($sid) ;
            $show[$key]['is_friend'] = checkFriden($this->mid,$value['uid']) ;
            $show[$key]['face'] = getUserFace($value['uid'],'b') ;
        }
        //$map_hotlist['isTop'] = 1;
        $map_hotlist['sTime'] = array('LT',time());
        $map_hotlist['eTime'] = array('GT',time());
        $map_hotlist['isDel'] = 0;
        $map_hotlist['status'] = 1;
       /*  $map_hotlist['isHot'] = 1; */
        $hotEvent = $this->get_hot_event() ; 
        //新的明星部落
        $hotGroup=$this->getHotGroup();
        $recommend = $this->get_recommend() ;
        $tel = getUserField($this->mid,'mobile') ;
        if($this->mid == 222526)
        {
            $this->assign('yyFlag',1);
        }
        $this->assign('tel',$tel) ;
        $this->assign('recommend',$recommend) ;
        $this->assign('hotGroup',$hotGroup) ;
        $this->assign('hotEvent',$hotEvent) ;
        $this->assign('show',$show) ;
		$this->display() ;
	}
    
    private function get_recommend(){
        $data['grow']['url'] = '';
        $data['grow']['ico'] = PIC_URL.'/data/app_ico/new_grow.png';
        $data['grow']['name'] = '成长服务超市';
        $data['qnh']['name'] = '江苏青年荟' ;
        $data['qnh']['ico'] = PIC_URL.'/data/app_ico/app_icon_qnh1.png';
//        $data['volunteer1']['name'] = '志愿者打卡器' ;
//        $data['volunteer1']['ico'] = PIC_URL.'/data/app_ico/app_icon_volunteer1.png';
        if (isiOS()) {
            $data['qnh']['url'] = 'https://itunes.apple.com/cn/app/jiang-su-qing-nian-hui/id740364085?mt=8' ;
//            $data['volunteer1']['url'] = 'https://itunes.apple.com/us/app/zhi-yuan-zhe-da-ka-qi-da-xue-ban/id734199565?ls=1&mt=8' ;
        }elseif(isAndroid()){
            $data['qnh']['url'] = 'http://www.apk.anzhi.com/data1/apk/201401/14/com.imohoo.gongqing_22667000.apk' ;
//            $data['volunteer1']['url'] = 'http://www.dakaqi.cn/apk/pu_volunteer.apk' ;
        }
        return $data ;
    }

    private function getHotGroup(){
	
	//默认全国 即没有多余的条件
    	$condition=array();
    	$order='weekactiv_num DESC,activ_num desc,hit desc ,id ASC';
    	$group = D('EventGroup', 'event');
    	$list = $group->apiGroupListLu($condition,3,1,$order,
    			'id,name,school,logo,membercount,vStern,cid0,activ_num,weekactiv_num');
    	if(empty($list)) {
    		$condition = array('id'=>array('IN',array('34068','34082','34075','34070','123','287','5999','34100','34099','34098')));
    		$list = $group->apiGroupListLu($condition,3,1,$order,
    				'id,name,school,logo,membercount,vStern,cid0,activ_num,weekactiv_num');
    	}
    	foreach($list as &$v)
    	{	
    		$v['pic']=$v['logo'] ;
    		$v['schoolname']=tsGetSchoolNameById($v['school']) ;
    	} 

    	return $list;
	}

    private function get_hot_event() {
        $map = $this->_getMap();
        $list = $this->_getEvent($map);
        $this->getTag($list);
        return $list;
    }

    private function _getMap() {
        $map['isDel'] = 0;
        $map['status'] = 1;
        $map['school_audit'] = array('neq', 5);
        //去除pu活动
        $map['is_prov_event'] = array('neq', 2);
        return $map;
    }
    
    private function _getEvent($map){
        $list=M('event')->field('id,gid,title,coverId,sTime,eTime,isTop,sid,credit,address')
                        ->where($map)
                        ->limit("0,3")
                        ->order('hit desc ,sTime desc,eTime desc')
                        ->select();
        return $list;
    }
    //处理标签
    private function getTag(&$list){
        if(!empty($list))
        {
            foreach($list as &$row)
            {   $row['cover'] = tsGetCover($row['coverId'],200,200,'c');
                $row['isTop'] = $row['isTop'] == 0 ? '' : '推荐';
                $row['cName'] = $row['sid']<0 ? '校' : tsGetSchoolTitle($row['sid']);
                $row['isCredit'] = intval($row['credit']) == 0 ? '' : '学分';
            }
        }
    }

    //一元猎宝 数据统计
    public function tjYiYuanLieBao()
    {
        $date = date('Ymd');
        $map['date'] = $date;
        $info = M('yiyuanliebao_tj')->where($map)->find();
        if(empty($info))
        {
            $data['date'] = $date;
            $data['hit'] = 1;
            $flag = M('yiyuanliebao_tj')->add($data);
        }
        else
        {
            $flag = M('yiyuanliebao_tj')->setInc('hit', $map, 1);
        }

        if(!$flag)
        {
            echo json_encode(array('status'=>0,'msg'=>'操作失败')); exit;
        }
        echo json_encode(array('status'=>1,'msg'=>'操作失败')); exit;
    }

}   


 ?>