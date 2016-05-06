<?php

/**
 * IndexAction
 * 活动
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class IndexAction extends Action {

    private $appName;
    private $event;

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        //应用名称
        global $ts;
        $this->appName = $ts['app']['app_alias'];
        //设置活动的数据处理层
        $this->event = D('Event');
        // 活动分类
        $cate = D('EventType')->getType();
        $this->assign('category', $cate);
    }

    public function sj() {
        $this->assign('id1', C('SJ_GROUP'));
        $this->assign('id2', C('SJ_PERSON'));
        $this->assign('id3', C('SJ_FS'));
        
        $showBm = false; //报名参加按钮
        //临时开报名参加按钮
        $domain = parse_url($_SERVER['HTTP_HOST']);
        $domain = substr($domain['path'], 0, strpos($domain['path'], '.'));
        if($domain=='yctswadmin'){
            $showBm = true;
        }
        $showVote = true; //投票按钮
        $this->assign('showBm', $showBm);
        $this->assign('showVote', $showVote);
        $this->display();
    }

    private function _rightSide() {
//        $map = array();
//        $map['a.isDel'] = 0;
//        $map['b.is_prov_event'] = 2;
//        $map['b.isDel'] = 0;
//        $map['b.status'] = 1;
//        $news = M('')->table('ts_event_news as a')->join('ts_event as b on a.eventId=b.id')
//                ->field('a.id,a.title,a.eventId')->where($map)->order('a.id Desc')->limit(8)->select();
//        $this->assign('rightNews', $news);
        //读取推荐活动
//        $map = array();
//        $map['isDel'] = 0;
//        $map['status'] = 1;
//        $map['show_in_xyh'] = 1;
//        $hotList = $this->event->getHotList($map);
//        $this->assign('hotList', $hotList);
    }

    /**
     * index
     * 首页
     * @access public
     * @return void
     */
    public function index() {
        $this->setTitle('活动首页');
        $this->assign('cTitle', '全部');

        $map['isDel'] = 0;
        $map['status'] = 1;
        //查询
        if ($_POST['title']) {
            $searchTitle = t($_POST['title']);
            $map['title'] = array('like', "%" . $searchTitle . "%");
            $this->assign('searchTitle', $searchTitle);
            $this->setTitle('搜索' . $this->appName);
        }
        if ($_GET['cid']) {
            $cid = intval($_GET['cid']);
            $map['typeId'] = $cid;
            $category = $this->get('category');
            $this->assign('cTitle', $category[$cid]);
            $this->setTitle('分类浏览');
        }

        switch ($_GET['action']) {
            case 'join':    //参与的
                $map_join['status'] = array('neq', 0);
                $map_join['uid'] = $this->uid;
                $eventIds = D('EventUser')->field('eventId')->where($map_join)->findAll();
                foreach ($eventIds as $v) {
                    $in_arr[] = $v['eventId'];
                }
                $map['id'] = array('in', $in_arr);
                $this->setTitle("我参与的活动");
                break;
//            case 'launch':    //发起的
//                $map['status'] = array('in', '0,1');
//                $map['uid'] = $this->uid;
//                $this->setTitle("我发起的活动");
//                break;
            case 'collect'://收藏的
                $uid = $this->uid;

                $result = M('event_collection')->field('eid')->where("uid=$uid")->select();
                $eids = getSubByKey($result, eid);
                $map['id'] = array('in', $eids);
                $this->setTitle("我收藏的活动");
                break;
            default:      //活动首页
                if(isset($this->user['sid'])){
                    $map['b.sid'] = $this->user['sid'];
                }
        }
        $map['is_prov_event'] = 2;
        $this->assign('action', isset($_GET['action']) ? $_GET['action'] : 'index');
        $result = $this->event->getEventList($map, $this->mid);
        $this->assign($result);
//        //是否可以管理活动
//        $user = D('User', 'home')->getUserByIdentifier($this->mid);
//        if (service('SystemPopedom')->hasPopedom($this->mid, 'admin/Index/index', false)) {
//            $this->assign('can_admin_event', 1);
//        }
        $this->_rightSide();
        $this->display();
    }

    /**
     * addEvent
     * 发起活动
     * @access public
     * @return void
     */
    public function addEvent() {
//        $this->_createLimit($this->mid);
//        $user = $this->get('user');
//        $school = model('Schools')->getEventSelect($user['sid']);
//        $this->assign('addSchool', $school);
//        $typeDao = D('EventType');
//        $this->assign('type', $typeDao->getType2());
//        $this->setTitle('发起' . $this->appName);
//        $this->_rightSide();
//        $this->display();
        $this->error('发布活动暂时关闭！');
    }

    /**
     * _creatLimit
     * 条件限制判断
     * @access public
     * @return void
     */
    private function _createLimit($uid) {
        $config = getConfig();

        if (!$config['canCreate']) {
            $this->error('禁止发起' . $this->appName);
        }
        if ($config['credit']) {
            $userCredit = X('Credit')->getUserCredit($uid);
            if ($userCredit[$config['credit_type']]['credit'] < $config['credit']) {
                $this->error($userCredit[$config['credit_type']]['alias'] . '小于' . $config['credit'] . '，不允许发起' . $this->appName);
            }
        }
        if ($timeLimit = $config['limittime']) {
            $regTime = M('user')->getField('ctime', "uid={$uid}");
            $difference = (time() - $regTime) / 3600;

            if ($difference < $timeLimit) {
                $this->error('账户创建时间小于' . $timeLimit . '小时，不允许发起' . $this->appName);
            }
        }
    }

    /**
     * doAddEvent
     * 添加活动
     * @access public
     * @return void
     */
    public function doAddEvent() {
        $this->error('发布活动暂时关闭！');
    }

    /**
     * doAction
     * 本人取消申请
     * @access public
     * @return void
     */
    public function doDelAction() {
        $data['eventId'] = intval($_POST['id']);
        $data['uid'] = $this->mid;
        $res = $this->event->doDelUser($data);
        if ($res['status']) {
            $this->success($res['msg']);
        }
        $this->error($res['msg']);
    }

    public function playFlash() {
        $id = intval($_REQUEST['id']);
        $app = D('EventFlash')->where("`id`={$id}")->find();
        if (!$app) {
            $this->error('视频不存在或已被删除！');
        }
        $app['url'] = get_flash_url($app['host'], $app['flashvar'],$app['link']);
        $this->assign($app);
        $this->display();
    }

    public function playTsFlash() {
        $id = intval($_REQUEST['id']);
        $app = M('flash')->where("`id`={$id}")->find();
        if (!$app) {
            $this->error('视频不存在或已被删除！');
        }
        $app['url'] = get_flash_url($app['host'], $app['flashvar'],$app['link']);
        $this->assign($app);
        $this->display('playFlash');
    }

    public function ajaxNote() {
        $note = intval($_POST['note']);
        if ($note < 0 || $note > 6) {
            $this->error('操作失败');
        }
        if ($data = $this->event->doAddNote(intval($_POST['id']), $this->mid, $note)) {
            $this->ajaxReturn($data, $info = '操作成功', $status = 1, $type = 'JSON');
        }
        $this->error('操作失败');
    }

    public function school() {
        $sid = intval($_REQUEST['sid']);
        $tree = M('school')->where('pid='.$sid)->field("id,title as name,0 as pId")->order('display_order asc')->findAll();
        if ($tree) {
            array_unshift($tree, array('id' => 0, 'name' => '选择全部', 'pid' => -1, 'open' => true));
        }
        $str = substr($_REQUEST['selected'], 0, strlen($_REQUEST['selected']) - 1);
        if ($tree && $str) {
            $selected = explode(',', $str);
        }
        if ($selected) {
            foreach ($tree as $k => $vo) {
                if (in_array($vo['id'], $selected)) {
                    $tree[$k]['checked'] = true;
                }
            }
        }
        $this->assign('tree', json_encode($tree));
        $this->display();
    }

    //jun  收藏活动
    public function editCollect() {
        if (D('EventCollection')->fav($this->mid, t($_REQUEST['id']), t($_REQUEST['type']))){
            $this->success('操作成功');
        }else{
            $this->error('操作失败');
        }
    }

////////////////////////////////////////////////////////////////
}
