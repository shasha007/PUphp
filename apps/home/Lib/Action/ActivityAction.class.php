<?php

/**
 * 营销后台
 */
import('home.Action.PubackAction');
header ( "Content-type: text/html; charset=utf-8" );
class ActivityAction extends PubackAction 
{
	/**
	 * 活动列表
	 * @see PubackAction::index()
	 */
    public function index() {
        $list = M('activity')->order('id DESC')->limit(100)->findAll();
        
        foreach($list as $k => $v) {
        	$list[$k]['status'] = $this->getStatus($v['stime'], $v['etime']);
        	$list[$k]['url'] = "http://".$_SERVER['SERVER_NAME']."/index.php?app=shop&mod=Activity&act=index&act_id=$v[id]";
        }
        
        $this->assign('data',$list);
        $this->display();
    }
    
    public function getStatus($stime,$etime)
    {
    	$now = date('Y-m-d H:i:s');
    	if($now > $stime && $now < $etime) return '进行中';
    	if($now > $etime) return '已结束';
    	if($now < $stime) return '未开始';
    }
    
    /**
     * 添加编辑活动
     */
    public function addActivity()
    {
    	if($_POST) {
    		$aid1 = $aid2 = $aid3 = 0;
    		$options = array();
    		$options['allow_exts'] = 'jpeg,gif,jpg,png,bmp';
    		$info = X('Xattach')->upload('activity', $options);
    		if($info['status']) {
	    		if (!$info['status']) {
	    			$this->error = '图片上传失败';
	    			return false;
	    		}
	    		for($i=1;$i<=3;$i++) {
	    			if($info['info'][($i-1)]['key'] == 'image1') $aid1 = $info['info'][($i-1)]['id'];
	    			if($info['info'][($i-1)]['key'] == 'image2') $aid2 = $info['info'][($i-1)]['id'];
	    			if($info['info'][($i-1)]['key'] == 'image3') $aid3 = $info['info'][($i-1)]['id'];
	    		}
    		}
    		

    		$data['title'] = $_POST['title'];
    		$data['desc'] = $_POST['desc'];
    		$data['rule'] = $_POST['content'];
    		$data['wintips'] = $_POST['wintips'];
    		$data['nowintips'] = $_POST['nowintips'];
    		$data['day_join_count'] = $_POST['day_join_count'];
    		$data['person_day_join_count'] = $_POST['person_day_join_count'];
    		$data['day_win_count'] = $_POST['day_win_count'];
    		$data['wincount'] = $_POST['wincount'] ? 1 : 0;
    		$data['stime'] = $_POST['sTime'];
    		$data['etime'] = $_POST['eTime'];
    		$data['cover'] = $aid1;
    		$data['over_cover'] = $aid2;
    		$data['share_img'] = $aid3;
    		$data['ctime'] = date('Y-m-d H:i:s');
    		$data['winraterange'] = $_POST['winraterange'];

    		if($_POST['id']) {
    			$act_id = $_POST['id'];
    			$res = M('activity')->where(array('id'=>$_POST['id']))->save($data);

    			$res = M('activity_area')->where(array('act_id'=>$act_id))->delete();
    			$res = M('activity_school')->where(array('act_id'=>$act_id))->delete();
    		} else {
    			//添加活动
    			$data['type'] = $_POST['type'];//活动类型,页面模板名
    			$res = M('activity')->add($data);
    			
    			$act_id = M('activity')->getLastInsID();
    		}
    		
    		//添加奖品
    		$i = 1;
    		foreach($_POST['prize'] as $k => $v) {
    			$prizeData = array('act_id'=>$act_id,'prize_id'=>$v['prize_id'],
    					'rate'=>$v['rate'],'total'=>$v['total'],'surp'=>$v['total'],
    					'sort'=>$i,'ctime'=>date('Y-m-d H:i:s'),
    					'cols'=>serialize($v['info']),
    			);
    			if($_POST['id']) {
    				$res = M('activity_prize')->where(array('id'=>$v['id'],'act_id'=>$act_id))->save($prizeData);
    			} else {
    				$prizeData['surp'] = $v['surp'];
    				$res = M('activity_prize')->add($prizeData);
    			}
    			$i++;
    		}
    		
    		//获取地区学校
    		$area = 0;
    		$asList = array();
    		$area = array_unique($_POST['area']);
    		foreach($area as $k => $v) {
    			if($v == '0') {
    				$area = 0;//所有地区
    				break;
    			} else {
    				$area = 1;//特定地区
    				$asList[$v] = $_POST['schools'.$v];
    			}
    		}
    		if($area == '0') {
    			$aaData = array('act_id'=>$act_id,'area_id'=>0);
    			$res = M('activity_area')->add($aaData);
    		} else {
    			foreach($asList as $k => $v) {
    				//插入area
    				$aaData = array('act_id'=>$act_id,'area_id'=>$k);
    				$res = M('activity_area')->add($aaData);
    				$aa_id = M('activity_area')->getLastInsID();
    				foreach($v as $k1 => $v1) {
    					$asData = array('act_id'=>$act_id,'aa_id'=>$aa_id,'school_id'=>$v1);
    					$res = M('activity_school')->add($asData);
    				}
    			}
    		}
    		//插入地区学校结束
    		header("Location:/index.php?app=home&mod=Activity&act=index");
    		exit;
    	} else {
    		$id = intval($_GET['id']);
    		$data = M('activity')->where(array('id'=>$id))->find();
    		$citys = M('citys')->findAll();
    		if ($id) {
    			$area = M('activity_area')->where('act_id='.$id)->field('area_id AS areaId')->findAll();
    			foreach ($area as $k => $v) {
    				foreach($v as $k1 => $v1) {
    					$area[$k]['school'] = M('school')->where('cityId=' . $v1)->field('id,title')->findAll();
    				}
    			}
    			$this->assign('area', $area);
    			$res = M('activity_school')->where(array('act_id'=>$id))->field('school_id AS sid')->findAll();
    			$sids = getSubByKey($res, 'sid');
    			$this->assign('sids', $sids);
    			$attach = getAttach($data['cover']);
    			$file = $attach['savepath'] . $attach['savename'];
    			$data['cover'] = tsMakeThumbUp($file, 60, 60, 'no');
    			$attach = getAttach($data['over_cover']);
    			$file = $attach['savepath'] . $attach['savename'];
    			$data['over_cover'] = tsMakeThumbUp($file, 60, 60, 'no');
    			$attach = getAttach($data['share_img']);
    			$file = $attach['savepath'] . $attach['savename'];
    			$data['share_img'] = tsMakeThumbUp($file, 60, 60, 'no');
    			$this->assign('data', $data);
    			$this->assign('id', $id);
    			
    			//获取奖品
    			$prize = M('activity_prize')->where(array('act_id'=>$id))->findAll();
    			foreach($prize as $k => $v) {
    				$prize[$k]['info'] = unserialize($v['cols']);
    			}
    		} else {
    			$prize = array(
    						array('id'=>'1'),array('id'=>'2'),array('id'=>'3'),
    						array('id'=>'4'),array('id'=>'5'),array('id'=>'6')
    						);
    		}
	    	$prizelist = M('prizelist')->order('id DESC')->findAll();
    		$this->assign('citys', $citys);
    		$this->assign('prize', $prize);
    		$this->assign('prizelist', $prizelist);
    		$this->display();
    	}
    }
    
    /**
     * 奖品列表
     */
    public function prizelist()
    {
    	$list = M('prizelist')->order('id DESC')->limit(100)->findAll();
    	foreach($list as $k => $v) {
    		$attach = getAttach($v['img']);
    		$file = $attach['savepath'] . $attach['savename'];
    		$list[$k]['pic_o'] = tsMakeThumbUp($file, 60, 60, 'no');
    		$list[$k]['pic_m'] = tsMakeThumbUp($file, 100, 100, 'f');
    	}
    	$this->assign('data',$list);
        $this->display();
    }
    
    /**
     * 添加编辑奖品
     */
    public function addPrize()
    {
    	if($_POST) {
    		if (!$_POST['id'] && empty($_FILES['img']["tmp_name"])) {
    			$this->error('图片不可为空');
    		}
    		if (!empty($_FILES['img']["tmp_name"]) && substr($_FILES['img']['type'], 0, 5) != 'image') {
    			$this->error('图片格式错误');
    		}
    		if (!isImg($_FILES['img']['tmp_name'])) {
    			$this->error = '图片格式不对';
    			return false;
    		}
    		if($_FILES['img']["tmp_name"]) {
    			list($sr_w, $sr_h) = @getimagesize($_FILES['img']['tmp_name']);
    			$options = array();
    			$options['allow_exts'] = 'jpeg,gif,jpg,png,bmp';
    			$info = X('Xattach')->upload('activity', $options);
    			if (!$info['status']) {
    				$this->error = '图片上传失败';
    				return false;
    			}
    			$aid = $info['info'][0]['id'];
    		}
    	
    		if($_POST['id']) {
    			$data['name'] = $_POST['name'];
    			$data['ctime'] = date('Y-m-d H:i:s');
    			$data['desc'] = $_POST['desc'];
    			$data['price'] = number_format($_POST['price'],2,'.','');
    			if($aid) $data['img'] = $aid;
    			$map = array('id'=>$_POST['id']);
    			$res = M('prizelist')->where($map)->save($data);
    		} else {
    			$data['name'] = $_POST['name'];
    			$data['ctime'] = date('Y-m-d H:i:s');
    			$data['desc'] = $_POST['desc'];
    			$data['price'] = number_format($_POST['price'],2,'.','');
    			$data['img'] = $aid;
    			$data['ctime'] = date('Y-m-d H:i:s');
    			$res = M('prizelist')->add($data);
    		}
    		header("Location:/index.php?app=home&mod=Activity&act=prizelist");
    		exit;
    	} else {
    		$id = intval($_GET['id']);
    		if ($id) {
    			$data = M('prizelist')->WHERE("id='$id'")->find();
    			$attach = getAttach($data['img']);
    			$file = $attach['savepath'] . $attach['savename'];
    			$data['pic_o'] = tsMakeThumbUp($file, 60, 60, 'no');
    			$data['pic_m'] = tsMakeThumbUp($file, 100, 100, 'f');
    			$this->assign('id', $id);
    			$this->assign('data', $data);
    		}
    		$this->display();
    	}
    }
    
    /**
     * 数据统计
     */
    public function statistics()
    {
    	$list = M('activity')->order('id DESC')->limit(100)->findAll();
    	
    	foreach($list as $k => $v) {
    		//中奖人数
    		$list[$k]['winprizeCount'] = $this->winPrizeCount($v['id']);
    		//中奖次数
    		$list[$k]['winprizeCountTimes'] = $this->winPrizeCountTimes($v['id']);
    		//参加活动的总人数
    		$list[$k]['joinCount'] = $this->joinCount($v['id']);
    		//参加活动的总人数的次数
    		$list[$k]['joinCountTimes'] = $this->joinCountTimes($v['id']);
    		//领奖人数
    		$list[$k]['getPrizeCount'] = $this->getPrizeCount($v['id']);
    		//领奖次数
    		$list[$k]['getPrizeCountTimes'] = $this->getPrizeCountTimes($v['id']);
    	}
    	
    	$this->assign('data',$list);
    	$this->display();
    }
    
    /**
     * 中奖人数
     * @param unknown $act_id
     */
    public function winPrizeCount($act_id)
    {
    	$data = M('prize_sn')->where(array('act_id'=>$act_id))->field("COUNT(distinct(uid)) AS total")->find();
    	
    	return $data['total'];
    }
    
    /**
     * 中奖次数
     * @param unknown $act_id
     */
    public function winPrizeCountTimes($act_id)
    {
    	$data = M('activity_prize')->field("(SUM(total)-SUM(surp)) AS total")->where(array('act_id'=>$act_id))->find();

    	return $data['total'];
    }
    
    /**
     * 领奖人数
     * @param unknown $act_id
     */
    public function getPrizeCount($act_id)
    {
    	$data = M('prize_sn')->field("COUNT(distinct(get_uid)) AS total")->where("get_uid>0 AND act_id='$act_id'")->find();
    	
    	return $data['total'];
    }
    
    /**
     * 领奖次数
     * @param unknown $act_id
     */
    public function getPrizeCountTimes($act_id)
    {
    	$data = M('prize_sn')->field("COUNT(get_uid) AS total")->where("get_uid>0 AND act_id='$act_id'")->find();
    	return $data['total'];
    }
    
    /**
     * 参与人数
     * @param unknown $act_id
     */
    public function joinCount($act_id)
    {
    	$data = M('activity_log')->field("COUNT(distinct(uid)) AS total")->where(array('act_id'=>$act_id))->find();
    	
    	return $data['total'];
    }
    
    /**
     * 参与人数
     * @param unknown $act_id
     */
    public function joinCountTimes($act_id)
    {
    	$data = M('activity_log')->field("COUNT(*) AS total")->where(array('act_id'=>$act_id))->find();
    	
    	return $data['total'];
    }

    /**
     * 中奖纪录
     */
    public function winlist()
    {
    	$act_id = $_GET['id'];
    	$data = M('prize_sn')->where(array('act_id'=>$act_id))->order('id DESC')->findAll();
    	
    	foreach($data as $k => $v) {
    		$prize = M('prizelist')->where(array('id'=>$v['prize_id']))->find();
    		$data[$k]['prize'] = $prize['name'].'--'.$prize['price'];

    		$uinfo = M('user')->where(array('uid'=>$v['uid']))->find();
    		$data[$k]['username'] = 'uname:'.$uinfo['uname'].'; realname:'.$uinfo['realname'];
    	}

    	$this->assign('data',$data);
    	$this->display();
    }
    
    /**
     * 发放奖品
     */
    public function sendPrize()
    {
    	$id = $_POST['id'];
    	$uid = $_POST['uid'];
    	
    	if(!$id || !$uid) die('0');
    	
    	$data = array('get_uid'=>$uid,'get_time'=>date('Y-m-d H:i:s'));
    	$prize = M('prize_sn')->where(array('id'=>$id,'uid'=>$uid))->save($data);
    	
    	if($prize) {
    		echo 1;
    	} else {
    		echo 0;
    	}
    	exit;
    }
    public function school() {
    	$cityId = intval($_REQUEST['cityId']);
    	if (!$cityId) {
    		exit(json_encode(array()));
    	}
    	$schools = M('school')->where('cityId=' . $cityId)->field('id,title')->order('display_order asc')->findAll();
    	if ($schools) {
    		exit(json_encode($schools));
    	}
    	exit(json_encode(array()));
    }
}

?>
