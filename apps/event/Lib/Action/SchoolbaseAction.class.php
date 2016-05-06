<?php

/**
 * SchoolbaseAction
 * 校方活动前台抽象类
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class SchoolbaseAction extends Action {

    protected $appName;
    protected $event;
    protected $school;
    protected $sid;
    protected $smid = 0;
    protected $rights;
    //菁英人才外部版
    protected $jyrcOut = false;

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        // 提示消息不显示头部
        $this->assign('isAdmin', '1');
        $domain = parse_url($_SERVER['HTTP_HOST']);
        $map['domain'] = substr($domain['path'], 0, strpos($domain['path'], '.'));
        $map['eTime'] = array('gt', 0);
//        var_dump($map);die;
        $school = M('school')->where($map)->find();
        $hostNeedle = get_host_needle();
        if (!$school) {
            $this->assign('jumpUrl', 'http://'.$hostNeedle);
            $this->error('此学校尚未开通校方活动！');
        }
        $this->school = $school;
        $this->sid = $school['id'];
        $this->assign('school', $school);
        //站点信息
        $config = D('SchoolWeb','event')->getConfigCache($this->sid);
        $this->assign('webconfig', $config);
        //用户信息
        if($this->mid){
            $groups = M('user_group_link')->where('uid='.$this->mid)->field('user_group_id')->findAll();
            $gids = getSubByKey($groups, 'user_group_id');
            $this->rights['allAdmin'] = in_array(C('SADMIN'), $gids);
            //$this->rights['allLook'] = in_array(C('SLOOK'), $gids);
        }
        if ($this->user['sid'] == $this->sid || $this->rights['allAdmin']) {
            $this->smid = $this->mid;
            //是否可以进入后台
            if ($this->user['can_event'] ||$this->user['can_event2'] ||$this->user['can_gift']
                    ||$this->user['can_print']||$this->user['can_group']||$this->user['can_admin']||$this->user['can_announce']
                    ||$this->user['can_prov_event']||$this->user['can_prov_news']||$this->user['can_prov_work']||$this->user['can_credit']
                    ||$this->user['event_level'] != 20||$this->rights['allAdmin'] || $this->user['can_available']) {
                $this->assign('open_admin', 1);
            }
            if($this->rights['allAdmin'] || $this->user['can_admin']){
                $this->rights['can_admin'] = 1;
            }
        }
        $this->assign('smid', $this->smid);
        //南工大寒暑假定时开放及关闭
        $beginTime = strtotime(date('Y').'-04-01'.' 00:00:00');
        $endTime = strtotime(date('Y').'-04-15'.' 23:59:59');
        if(time()>=$beginTime && time()<=$endTime)
        {
            $summerEvent = 1;
            $this->assign('summerEvent',$summerEvent);
        }
        //南工大文体与创新定时开放及关闭
        if(time() >= strtotime('2018-01-01 00:00:00') && time()<= strtotime('2018-01-01 00:00:00'))
        {
            $artEvent = 1;
            $this->assign('artEvent',$artEvent);
        }

        //南工大社会工作与技能定时开放及关闭
        if(time() >= strtotime('2018-01-01 00:00:00') && time()<= strtotime('2018-01-01 00:00:00'))
        {
            $techEvent = 1;
            $this->assign('techEvent',$techEvent);
        }
        //应用名称
        $this->appName = '活动';
        //设置活动的数据处理层
        $this->event = D('Event');
        // 活动分类
        $cate = D('EventType')->getType($this->sid);
        $this->assign('category', $cate);
        $this->assign('searchType', D('EventType')->getSearchType($this->sid));
        //幻灯
        $this->assign('slide', $this->event->getSlide($this->sid));
        //菁英人才 并且 未登录前 或 登录用户不是菁英
        if(isTuanRole($this->sid) && !$this->smid){
            $this->jyrcOut = true;
        }
        $this->assign('jyrcOut', $this->jyrcOut);
        $this->assign('eventPage', 'event');
        //迎新url获取
//        $newcomerurl = M('newcomer_logo')->getField('website','sid='.$this->sid);
        $newcomerurl = 'no';
        $this->assign('newcomerurl', $newcomerurl);
        //积分商城是否显示
        $this->assign('showJf', X('Mmc')->hasJfProduct($this->sid));
    }

    protected function _checkSchoolUser() {
        if (!$this->smid) {
            $this->assign('jumpUrl', U('event/School/index'));
            $this->error('请先登录!');
        }
    }

    protected function _rightSide() {
        global $ts;
        $install_app = $ts['install_apps'];
        $this->assign('install_app', $install_app);
        $showFund = 0;
        if ($this->sid ==473) {
            $showFund = 1;
        }
        $this->assign('showFund', $showFund);
        if ( !isTuanRole($this->sid) || $this->jyrcOut) {
            //读取最新新闻
//            $map = array();
//            $map['a.isDel'] = 0;
//            $map['b.is_school_event'] = $this->sid;
//            $map['b.isDel'] = 0;
//            $map['b.status'] = 1;
//            $news = M('')->table('ts_event_news as a')->join('ts_event as b on a.eventId=b.id')
//               ->field('a.id,a.title,a.eventId')->where($map)->order('a.id Desc')->limit(5)->select();
//            $this->assign('rightNews', $news);
        } else {
            //菁英人才新闻
            $map['sid'] = $this->sid;
            $map['isDel'] = 0;
            $news = M('school_news')->where($map)->order('id Desc')->limit(5)->select();
            $this->assign('jyrcNews', $news);
            //菁英人才作业
            $work = M('school_work')->where($map)->order('id Desc')->limit(5)->select();
            $this->assign('jyrcWork', $work);
            //是否有待做的
            $done = M('school_workback')->where('uid='.$this->uid)->field('wid')->findAll();
            if($done){
                $doneIds = getSubByKey($done, 'wid');
                $map['id'] = array('not in', $doneIds);
            }
            $map['eTime'] = array('gt', time());
            $newWork = M('school_work')->where($map)->field('id')->find();
            $this->assign('newWork', $newWork);
        }
        //读取推荐积分
        $map = array();
        $map['sid'] = $this->sid;
        $map['isDel'] = 0;
        $hotList = M('jf')->where($map)->order("RAND()")->limit(3)->select();
        $this->assign('rightGift', $hotList);
        //排名
        if(ACTION_NAME == 'jf'){
            $pai = X('Mmc')->scoreTop10($this->sid);
            $this->assign('scoreTop', $pai);
        }else{
            $pai = X('Mmc')->creditTop10($this->sid);
            $this->assign('mon', $pai[0]);
            $this->assign('semester', $pai[1]);
            $this->assign('year', $pai[2]);
        }
    }
}
