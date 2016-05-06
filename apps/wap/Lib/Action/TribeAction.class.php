<?php
class TribeAction extends BaseAction {
	var $place=7;//部落广告

	//本周部落之星
	public function weekTribe(){
		//本周部落之星
		$weektrible=$this->getWeekTribe();
		//部落广告
		//$add=$this->adList();	
		//dump($add) ; die ;
		$this->assign('weektrible',$weektrible);
		$this->assign('add',$add);
		$this->display('weektribe');
	}		
	
	//部落广告列表
	public function adList() {
		if(isTestUser($this->mid)){
            return array();
        }
        $user = M('user')->field('year,sid')->where('uid='.$this->mid)->find();	
        if(!$user){
            return array();
        }
        $sid = $user['sid'];
        $year = $user['year'];
        $place = 17 ;
	    $from = appType();
	    $from = appType();
	    if ($place == 12 && $from=='ios' && $this->mid!=1478634) {
            //不再显
            return array();
        }
        if ($place == 11) {
            //不再显示全屏广告
            //return array();
            $adList = getAd($sid, $year, $place, 1);
        } else {
            $adList = getAd($sid, $year, $place, 10);
        }	    
        foreach ($adList as $k=>&$v) {
            $v['cover'] = tsGetCover($v['coverId'], 550, 100, 'no');
            $v['jumpid'] = 0;
            //PU最强班
            if($v['title']=='暑期最“强”班' || $v['title']=='PU最“强”班'){
                $v['jump']=1;
                $key = substr($this->secret, 5, 17);
                $num = time();
                $sign = md5($num.$key.$this->mid);
                $v['url'] = SITE_URL.'/kdl/'.$this->mid.$num.'/?uid='.$this->mid
                        .'&num='.$num.'&sign='.$sign
                        .'&japp=vote&jmod=Index&jact=mvote';
            }
            if ($v['jump']>1) {
                if(is_numeric($v['url'])){
                    $v['jumpid'] = intval($v['url']);
                }else{
                    $v['jumpid'] = intval(substr($v['url'], strrpos($v['url'], '&id=')+4));
                }
            }
            //元宵节活动
            if($v['jump'] == 1 && $v['url'] == 'http://pocketuni.net/index.php?app=wap&mod=Asking&act=cover'){
                $key = substr($this->secret, 5, 17);
                $num = time();
                $sign = md5($num.$key.$this->mid);
                $v['url'] = SITE_URL.'/kdl/'.$this->mid.$num.'/?uid='.$this->mid
                    .'&num='.$num.'&sign='.$sign
                    .'&japp=wap&jmod=Asking&jact=cover';
                continue;
            }

            if($v['jump'] == 1)
            {
                //todo 解析URL
                $urlArr = parse_url($v['url']);
                $urlQuery = $urlArr['query'];
                $urlParams = explode('&',$urlQuery);
                foreach($urlParams as $key=>$val)
                {
                    $vArr = explode('=',$val);
                    $urlReturn[$vArr[0]] = $vArr[1];
                }
            }

            //话题广场 themes_id weibo_id
            if($v['jump'] == 1 && $urlReturn['app'] == 'wap'){
                $params = '';
                if(!empty($urlReturn['themes_id']))
                {
                    $params = '&themes_id='.$urlReturn['themes_id'];
                }
                if(!empty($urlReturn['weibo_id']))
                {
                    $params = '&weibo_id='.$urlReturn['weibo_id'];
                }
                $v['url'] = $this->_autoLoginUrl($urlReturn['app'],$urlReturn['mod'],$urlReturn['act'],$params);
                continue;
            }

            //口贷乐
            if($v['jump']==1 && $v['url']=='http://pocketuni.net/index.php?app=shop&mod=PocketShop&act=index'){
                $key = substr($this->secret, 5, 17);
                $num = time();
                $sign = md5($num.$key.$this->mid);
                $v['url'] = SITE_URL.'/kdl/'.$this->mid.$num.'/?uid='.$this->mid
                        .'&num='.$num.'&sign='.$sign
                        .'&japp=shop&jmod=PocketShop&jact=index';
            }elseif($v['jump']==1){
                $v['url'] = $this->_autoLoginUrl('home','Vote','jump','&url='.urlencode($v['url']));
            }
        }
        return $adList ? $adList : array();   
	}
	
	
	
	
	
	//获取本周部落之星
	public function getWeekTribe() {
		$sid = getUserField($this->mid, 'sid');
		$dpart = intval($_REQUEST['dpart']);
		$sid1 = intval($_REQUEST['sid1']);
		$year = intval($_REQUEST['year']);
		$sort = intval($_REQUEST['sort']);
		//默认全国 即没有多余的条件
		$condition=array();
		/* if ($sid > 0) {
			$condition['school'] = $sid;//默认本校
		} */
		if ($sid1 > 0) {
			$condition['sid1'] = $sid1;//院系
		}
		if ($year > 0) {
			$condition['year'] = $year;//年级
		}
		if ($sort > 0) {
			$condition['cid0'] = $sort;//市
		}
		$order='weekactiv_num DESC,activ_num desc,hit desc ,id ASC';
		$group = D('EventGroup', 'event');
		$list = $group->apiGroupListLu($condition,$this->_item_count,$this->_page,$order,
				'id,name,school,logo,membercount,vStern,cid0,activ_num,weekactiv_num');
		if(empty($list)) {
			$condition = array('id'=>array('IN',array('34068','34082','34075','34070','123','287','5999','34100','34099','34098')));
			$list = $group->apiGroupListLu($condition,$this->_item_count,$this->_page,$order,
					'id,name,school,logo,membercount,vStern,cid0,activ_num,weekactiv_num');
		}
		$this->isAddTribe($list);
		return $list;
	}	
	
	
	
	//判读是不是成员
	protected function isMember($uid,$gid) {
		return M('group_member')->where("uid=$uid AND gid=$gid AND level=3")->count();
	}
	
	//添加是否已加入该部落和部落所属学校
	protected function isAddTribe(&$list=array()){
		foreach($list as &$v){
			$member=$this->isMember($this->mid, $v['id']);
			if($member>0){
				$v['Is_join']=1;
			}else{
				$v['Is_join']=0;
			}
			$v['schoolname']=tsGetSchoolName($v['school']);//部落所属学校
			if(empty($v['schoolname']))
			{
				$v['schoolname']=" ";
			}
		}
	}	
	//ajax部落排名
	public function ajaxGetTribe()
	{	//学校
		$sid = getUserField($this->mid, 'sid');
		//市
		$cid = model('Schools')->getCityId($sid);
		//省
		$pro_id =model('Schools')->getProvId($sid);
		//区域:1全国2全省3全市4本校
		$a=intval($_REQUEST['aid']);
		//1全部 2学生社团 3团支部4学生部门
		$i=intval($_REQUEST['sid']);
		/* if($i==0 && $a==0 )
		{
			$where["school"]=$sid;//什么都没选默认进入本校
		} */
		switch($i)
		{	case 0:break;//什么条件都没选
			case 1:break;//全部
			case 2:$where["category"]=3;break;
			case 3:$where["category"]=2;break;
			case 4:$where["category"]=1;break;
			
		}
		switch($a)
		{	case 0:break;//什么条件都没选
			case 1:break;//全部
			case 2:$where["prov_id"]=$pro_id;break;
			case 3:$where["cid0"]=$cid;break;
			case 4:$where["school"]=$sid;break;
		}
		$order='weekactiv_num DESC,id ASC';
		$group = D('EventGroup', 'event');
		$list = $group->apiGroupListLu($where,$this->_item_count,$this->_page,$order,
				'id,name,school,logo,membercount,vStern,cid0,activ_num,weekactiv_num');
		$this->isAddTribe($list);
		echo $this->getHtml($this->_page,$list);
	}
	
	 protected function getHtml($page,$list)
	 {	
	 	$page=$page-1;
	 	$offset=1+$page*$this->_item_count;
	 	if(!empty($list)){
	 		$t['status']=1;
	 		$t['data']=$list;
	 		$t['rank']=$offset;
	 	}else{
	 		$t['status']=0;
	 		$t['data']='';
	 		$t['rank']=$offset;
	 	}
	 	return json_encode($t);
	 }	
}