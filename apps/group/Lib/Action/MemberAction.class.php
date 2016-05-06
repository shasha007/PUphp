<?php

class MemberAction extends BaseAction
{
	var $member;

	public function _initialize(){
		parent::_initialize();
		$this->member = D('Member');
		$this->assign('current','member');
        $this->setTitle("成员 - " . $this->groupinfo['name']);
	}

	//所有成员
	public function index() {
		if($_GET['order'] == 'new') {
			$order = 'ctime DESC';
			$this->assign('order', $_GET['order']);
		}elseif($_GET['order'] == 'visit'){
			$order = 'mtime DESC';
			$this->assign('order', $_GET['order']);
		}else{
			$order = 'level ASC';
			$this->assign('order', 'all');
		}

		$search_key = $this->_getSearchKey();
		if ($search_key) {
			
		} else {
			$memberInfo = $this->member->order($order)->where('gid=' . $this->gid . " AND status=1 AND level>0")->findPage(20);
		}

		foreach ($memberInfo['data'] as &$member) {
			
			$user = D('User', 'home')->getUserByIdentifier($member['uid'], 'uid');
		    //$member['school1'] = "苏州大学";
		    //$member['school2'] = "信息学院";		
		    $member['school1'] = tsGetSchoolName($user['sid']);
		    $member['school2'] = tsGetSchoolName($user['sid1']);
		    //将来 年级，专业
		    //$res['year'] = $user['year'];
		    //$res['major'] = $user['major'];
		    //$member['mobile'] = $user['mobile'];

			$member['weibo'] = D('GroupWeibo')->field('weibo_id,gid,content')
			        					 ->where("uid={$member['uid']} AND gid={$member['gid']} AND isdel=0")
			        					 ->order('ctime DESC')
			        					 ->find();
			$member['followState'] = getFollowState( $this->mid, $member['uid']);
		}
                     //找到所有通过校方审核的社团
                $status = M('group_validate')->where('gid=' . $this->gid)->field('status')->order('id DESC')->find();
                $this->assign('status', $status['status']);

		$this->assign('memberInfo',$memberInfo);
		$this->display();
	}
}