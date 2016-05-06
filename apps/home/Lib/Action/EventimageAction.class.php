<?php
/**
 * IndexAction
 * 活动缩略图后台
 *20160106
 *yangjun
 */
import('home.Action.PubackAction');
class EventimageAction extends PubackAction {
	
	 protected $radio1='';
	 protected $radio2='';
	 protected $radio3=''; 
	 /*允许操作*/
	 public $allowOpt = true;
	 
	 public function adread()
	 {
	 	$this->allowOpt = false;
	 	$this->ad();
	 }
	 private function _getMap() {
	    	$map['isDel'] = 0;
	    	$map['status'] = 1;
	    	return $map;
	    }
	    
	 //判断图片尺寸
	 private function getimg($data,$radio=1){
	 	$size=$data[0]/$data[1];
	 	if($size==$radio){
	 		return true;
	 	}else{
	 		return false;
	 	}
	 	
	 	
	 }
	 
	 
	 //今日推荐首页
	public function imglist()
	{	$where['status']=1;
		//搜索条件
		$title='';
		if(!empty($_POST['title']))
		{	
			$title=t($_POST['title']);
			$where['title']=array('like',"%$title%");
		}
		$list=M('event_image')->where($where)->order('queue asc,id DESC')->findpage(10);
		$this->assign($list);
		$this->assign('title',$title);
		$this->assign('allowOpt',$this->allowOpt);
		$this->display();
		
		
		
	}
	//今日推荐添加
	public function addimage(){
		$event=D('EventDisplay','event')->getAllDisplay() ;
		foreach ($event as $key => $value) {
			$ids[] = $value['event_id'] ;
		}
		$map['a.id'] = array('IN',$ids) ;
		$event = $this->getevent($map) ;
		$citys = M('citys')->findAll();
		$this->assign('citys', $citys);
		$this->assign('event',$event);
		$this->display();	
	}
	
	//今日推荐编辑
	public function editimage(){
		$where['id'] = intval($_GET['id']);
		$list=M('event_image')->where($where)->find();
		$this->assign( $list);
		$this->display();
		
		
	}
	// 地址变更
	public function palceDisplay(){
		$where['id'] = intval($_GET['id']);
		$list=M('event_image')->where($where)->find();
		$citys = M('citys')->findAll();
		if($list['areaId']) {
            $area = M('event_image_line')->where('event_image_id=' . $list['id'])->group('areaId')->field('areaId')->findAll();
            // dump(M('event_image_line')->getlastSql()) ; die ;
            $dao = M('school');
            foreach ($area as $k => $v) {
                $area[$k]['school'] = $dao->where('cityId=' . $v['areaId'])->field('id,title')->findAll();
            }
            $this->assign('area', $area);
            $res = M('event_image_line')->where('event_image_id=' . $list['id'])->field('sid')->findAll();
            $sids = getSubByKey($res, 'sid');
            $this->assign('sids', $sids);
        }
        // dump($area) ; dump($sids) ; die ;
        $this->assign( $list);
		$this->assign('citys', $citys);
		$this->display() ;
	}
	// 地址变更操作
	public function changePlace(){
		// dump($_REQUEST) ; die ;
		if ($_REQUEST['area'][0] == 0) {
			$data['areaId'] = 0 ;
			$data['sid'] = 0 ;
		}else{
			$areaIds = implode(",",$_REQUEST['area']) ;
			foreach ($_REQUEST['area'] as $key => $value) {
				$name = 'schools'.$value ;
				$sid.= implode(",", $_REQUEST[$name]) ;
				$sid.=',' ;
			}
			$sid = trim($sid,',') ;
		}
		$map['id'] = intval($_REQUEST['id']) ;
		$data['areaId'] = $areaIds ;
		$data['sid'] = $sid ;
		M('event_image')->startTrans() ;
		if (M('event_image')->where($map)->save($data) !== false) {
			if (M('event_image_line')->where('event_image_id='.$map['id'])->delete()) {
				foreach ($_REQUEST['area'] as $key => $value) {
					$da['event_image_id'] = $map['id'] ;
					$da['areaId'] = $value ;
					$name = 'schools'.$value ;
					foreach ($_REQUEST[$name] as $key => $value) {
						$da['sid'] = $value ;
						if (!M('event_image_line')->add($da)) {
							M('event_image')->rollback();
							$this->error('修改失败') ;	
						} 
					}
				}
				M('event_image')->commit() ;
				$this->success('修改成功') ;
			}
		}
		$this->error('修改失败') ;
	}
	//今日推荐删除
	public function doDeleteImg() 
	{	
		if (empty($_POST['ids'])) {
			echo 0;
			exit;
		}
		$map['id'] = array('in', t($_POST['ids']));
		$flag = M('event_image')->where($map)->delete();
		echo empty($flag) ? '0' : '1';
	}
	
	
	public function doeditimage()
	{
		if (($_POST['id'] = intval($_POST['id'])) <= 0)
		{
			$this->error("错误链接");
		}
		$id = $_POST['id'];
		if (!$obj = M('event_image')->where(array('id' => $id))->find()) 
		{
			$this->error('广告不存在或已删除');
		}
		//处理上传的图片
		$data=$this->withimg();
		//更新数据
		$where['id']=$id;
		$where['status']=1;
		$res=M('event_image')->where($where)->save($data);
		if($res)
		{
			$this->assign('jumpUrl', U('home/Eventimage/imglist'));
			$this->success('修改成功');
		}else
		{
			$this->error('修改失败');
		}
	}
	
	//添加方法	
	public function doaddimage(){
		$sid = '' ;
		if ($_REQUEST['area'][0] == 0) {
			$data['areaId'] = 0 ;
			$data['sid'] = 0 ;
		}else{
			$areaIds = implode(",",$_REQUEST['area']) ;
			foreach ($_REQUEST['area'] as $key => $value) {
				$name = 'schools'.$value ;
				$sid.= implode(",", $_REQUEST[$name]) ;
				$sid.=',' ;
			}
			$sid = trim($sid,',') ;
		}
		if(empty($_POST['eventid'])){
			$this->error('请选择活动'); 
		}
		//活动id
		$eventid=intval($_POST['eventid']);
		//处理上传的图片
		$data=$this->withimg();
		//保存记录
		$this->addeventimg($eventid,$data['img1Id'],$data['img2Id'],$data['img3Id'],$areaIds,$sid);
	}
	
	//获取所有可参加的活动(去掉已经有活动图的活动)
	protected function getevent($map){
		/* $map['a.eTime']=array('egt',time());//活动结束时间大于等于今天 */
		$map['isDel']=0;
		//今日推荐的条件1:是pu活动 2:是进行中的活动
		$map['is_prov_event']=2;//是pu活动
		//是进行中的活动
		$map['_string']='school_audit=2 and status >0';
		$order='sTime DESC';
		$list= M()->table("ts_event AS a ")->join("ts_event_school2 AS b ON a.id=b.eventId")
                    ->field('a.id,title,coverId,sTime,eTime,joinCount,note,
                is_prov_event,address')
                            ->where($map)->order($order)->select();
		//已添加图片的活动
		$data=M('event_image')->field('eventId')->where('status=1')->order('id DESC')->select();
		$eventIds=array();
		foreach($data as $v)
		{
			array_push($eventIds, $v['eventId']);
		}
		//剔除已有图片的活动
		foreach($list as $k=>$t)
		{
			if(in_array($t['id'], $eventIds))
			{
				unset($list[$k]);
			}
		}
		return $list;
		
	}
	//保存event——image	
	protected function addeventimg($eventid,$imgid1,$imgid2,$imgid3,$area,$sid){
		$title=	M('event')->where("id=$eventid")->getfield('title');
		$data['eventId']=$eventid;
		$data['title']=$title;
		$data['img1Id']=$imgid1;
		$data['img2Id']=$imgid2;
		$data['img3Id']=$imgid3;
		$data['areaId']=$area;
		$data['sid']=$sid;
		$model=M('event_image')->add($data);
		if($model){
			foreach ($_REQUEST['area'] as $key => $value) {
				$da['event_image_id'] = $model ;
				$da['areaId'] = $value ;
				$name = 'schools'.$value ;
				foreach ($_REQUEST[$name] as $key => $value) {
					$da['sid'] = $value ;
					M('event_image_line')->add($da) ;
				}
			}
			$this->assign('jumpUrl', U('home/Eventimage/imglist'));
			$this->success('添加成功');
		}else{
			$this->error('该活动已有缩略图');
		}
	}
	
	public function geteventimage($data){
		$model=M('event_image');
		foreach($data as $k=>&$v){
		$where['eventId']=$v['id'];
		$where['status']=1;
		$v['imageid']=intval($model->where($where)->getfield('id'));	
			
		}
		return $data;
				
		
	}
	//处理上传图片
	protected function withimg()
	{
		if (empty($_FILES['cover1']['name']) && empty($_FILES['cover2']['name']) && empty($_FILES['cover3']['name'])) {
			$this->error('您还有没有传满三张图片');
		}
		//定义三张图片尺寸
		$this->radio1=580/210;
		$this->radio2=282/282;
		$this->radio3=183/263;
		$image1=$this->getimg(getimagesize($_FILES['cover1']['tmp_name']),$this->radio1);
		$image2=$this->getimg(getimagesize($_FILES['cover2']['tmp_name']),$this->radio2);
		$image3=$this->getimg(getimagesize($_FILES['cover3']['tmp_name']),$this->radio3);
		if(!$image1){
			$this->error('第一张尺寸不符合');
		}
		if(!$image2){
			$this->error('第二张尺寸不符合');
		}
		if(!$image3){
			$this->error('第三张尺寸不符合');
		}
		//得到上传的图片
		$images = tsUploadImg($this->mid,'eventimage',true);
		
		if (!$images['status'] && $images['info'] != '没有选择上传文件') {
			$this->error($images['info']);
		}
		$imgid1=$images['info'][0]['id'];
		$imgid2=$images['info'][1]['id'];
		$imgid3=$images['info'][2]['id'];
		
		$data['img1Id']=$imgid1;
		$data['img2Id']=$imgid2;
		$data['img3Id']=$imgid3;
		return $data;
		
	}

	// 活动库列表
	public function eventDisplay(){
		$eventId = intval($_REQUEST['event_id']) ;
		if ($eventId>0) {
			$map['ts_event_display.event_id'] = $eventId ;
		}
		$list = D('EventDisplay','event')->searchEvent($map) ;
		$this->assign('allowOpt',$this->allowOpt);
		$this->assign($list) ;
		$this->assign($_REQUEST) ;
		$this->display() ;
	}

	// 增加活动到活动库
	public function doaddEventToDisplay(){
		$event_ids = t($_REQUEST['event_id']) ;
		if (empty($event_ids)) {
			$this->error('参数错误') ;
		}
		// 中文逗号 换成英文逗号
		$event_ids= preg_replace("/(，)/" ,',' ,$event_ids); 
		$event_ids = explode(',',$event_ids) ;
		if (D('EventDisplay','event')->addEventToDisplay($event_ids)) {
			$this->success('添加成功') ;
		}
		$this->error('添加失败') ;
	}

	// 活动库删除活动
	public function dodeleteEventFromDisplay(){
		$ids = t($_REQUEST['ids']) ;
		if (empty($ids)) {
			echo 0 ; die ;
		}
		if (D('EventDisplay','event')->deleteEventFromDisplay($ids)) {
			echo 1 ; die ;
		}
		echo 0 ;
	}


    /*
    * 开启关闭今日推荐
    */
    public function open(){
        $map['id'] = intval($_REQUEST['id']) ;
        if (intval($_REQUEST['status']) === 1 ) {
            $data['display'] = 1 ;
        }else{
            $data['display'] = 0 ;
        }
        if (M('event_image')->where($map)->save($data) !==false) {
            echo 1 ;die ;
        }
        echo 0 ;
    }
    /*
    * 排序
    */
    public function addRemark(){
    	$id = intval($_REQUEST['id']) ;
    	if ($id < 1) {
    		$this->error('参数错误') ;
    	}
    	$queue = intval($_REQUEST['val']) ;
    	$data['queue'] = $queue ;
    	if (M('event_image')->where('id='.$id)->save($data) !== false) {
    		$this->success('修改成功') ;
    	}
    	$this->error('修改失败') ;
    }


    // 所有活动列表

    public function allEventList(){
        $this->_getEventList();
        $taglist = D('EventTag')->where("isdel = '0' and ")->findAll();
        $this->assign('taglist',$taglist);
    	$this->display() ;
    }



    private function _getEventList($map=array(),$orig_order='id DESC'){
        //get搜索参数转post
        if (!empty($_GET['typeId'])) {
            $_POST['typeId'] = $_GET['typeId'];
        }
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['home_searchEvent'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['home_searchEvent']);
        } else {
            unset($_SESSION['home_searchEvent']);
        }
        $this->assign('isSearch', isset($_POST['isSearch']) ? '1' : '0');

        $map['isDel'] = 0;
        if(intval($_POST[sid1])){
            $res=M('user')->where('sid1 ='.$_POST['sid1'])->field('uid')->findAll();
            $uids= getSubByKey($res,'uid');
            $map['uid'] =array('in',$uids);
        }
        $_POST['typeId'] && $map['typeId'] = intval($_POST['typeId']);
        $_POST['title'] && $map['title'] = array('like', '%' . t($_POST['title']) . '%');
        if (isset($_POST['status'])&&$_POST['status']!=='') {
            $map['school_audit'] = intval($_POST['status']);
        }
        if (isset($_POST['pu']) && $_POST['pu'] != '')
            $map['is_prov_event'] = intval($_POST['pu']);
        if (isset($_POST['puRecomm']) && $_POST['puRecomm'] != '')
            $map['puRecomm'] = array('gt',0);
        if (isset($_POST['isTop']) && $_POST['isTop'] != '')
            $map['isTop'] = intval($_POST['isTop']);
        if (isset($_POST['isHot']) && $_POST['isHot'] != '')
            $map['isHot'] = intval($_POST['isHot']);

        //取出标签
        $tid = intval($_POST['tagId']);
        $this->assign('tid',$tid);
        if($tid){
            $eids = D('EventTagcheck')->field('eid')->where("tid=$tid")->findAll();
            $map['id'] = array('in',  getSubByKey($eids, 'eid'));
        }
        //处理时间
//            $_POST['sTime'] && $_POST['eTime'] && $map['cTime'] = $this->event->DateToTimeStemp(t( $_POST['sTime'] ),t( $_POST['eTime'] ) );
        $_POST['sTime'] && $_POST['eTime'] && $map['cTime'] = D('Event','event')->DateToTimeStemp(t(date("Ymd", strtotime($_POST['sTime']))), t(date("Ymd", strtotime($_POST['eTime']))));
        //处理排序过程
        $order = isset($_POST['sorder']) ? t($_POST['sorder']) . " " . t($_POST['eorder']) : $orig_order;
        $_POST['limit'] && $limit = intval(t($_POST['limit']));
        $order && $list = D('Event','event')->where($map)->field('id,is_prov_event,title,typeId,uid,joinCount,credit,score,cTime,
            school_audit,audit_uid,audit_uid2,status,isTop,remark,isHot,puRecomm,is_school_event,gid,attachId,fTime,endattach,es_type')->order($order)->findPage($limit);
        /* echo $this->event->getLastSql();
        die; */
        
        //处理活动列表
        foreach($list as $key => &$val){
            if($key == 'data'){
                foreach($val as &$v){
                    $tagCheck = D('EventTagcheck');
                    $map_c['eid'] = $v['id'];
                    $tid = $tagCheck->where($map_c)->field('tid')->findAll();
                    $tids = '';
                    foreach($tid as $t){
                        $tids .= $t['tid'].',';
                    }
                    $tids = rtrim($tids,','); 
                    $map_t['id'] = array('in',$tids);
                    $etag = D('EventTag');
                    $tlist = $etag->where($map_t)->field('title')->findAll();
                    $tag = '';
                    if(!empty($tlist)){
                        foreach($tlist as $v1){
                            $tag .= $v1['title'].',';
                        } 
                    }
                    $v['tag'] = rtrim($tag,',');
                    $v['onlineTime'] = D('EventOnline','event')->getOnlineTime($v['id']);
                }
            }
        }
        $this->assign($_POST);
        $this->assign($list);
        $this->assign('editSid',$this->sid);
        $this->assign('type_list', D('EventType','event')->getType($this->sid));
        $this->assign('searchType', D('EventType','event')->getSearchType($this->sid));
    }
}