<?php

class IndexAction extends Action {

    public $route;

    public function _initialize() {
        $this->route = D('Travel');
    }

    public function index() {
        if ($_GET['cat'] == 'all') {
            unset($_SESSION['route_searchCat']['cat']);
        } elseif ($_GET['cat']) {
            $_SESSION['route_searchCat']['cat'] = intval($_GET['cat']);
        }
        if ($_GET['area'] == 'all') {
            unset($_SESSION['route_searchArea']['area']);
        } elseif ($_GET['area']) {
            $_SESSION['route_searchArea']['area'] = t($_GET['area']);
        }
        $searchTitle = t($_POST['title']);
        if ($searchTitle) {
            if (mb_strlen($searchTitle, 'utf8') < 2) {
                $this->error('至少输入两个字');
            }
            $map['title'] = array('like', "%" . $searchTitle . "%");
            $this->assign('searchTitle', $searchTitle);
            $this->setTitle('搜索' . $this->appName);
        }
        if ($_SESSION['route_searchCat']['cat']) {
            $map['catId'] = $_SESSION['route_searchCat']['cat'];
        }
        if ($_SESSION['route_searchArea']['area']) {
            $map['areaId'] = $_SESSION['route_searchArea']['area'];
        }
        $map['eTime'] = array('gt',time());
        $daoTravel = D('Travel');
        $result = $daoTravel->routeList($map, 6);
        $cat = M('travel_cat')->findAll();
        $area = M('travel_area')->findAll();
        $this->assign('area', $area);
        $this->assign('cat', $cat);
        $this->assign($result);
        $this->display();
    }

    private function _top() {
        $tid = intval($_REQUEST['id']);
        if ($tid <= 0) {
            $this->error("错误的访问页面，请检查链接");
        }
        $map['isDel'] = 0;
        $map['id'] = $tid;
        if ($result = $this->route->where($map)->find()) {
            $this->assign($result);
        } else {
            $this->error('该景点不存在或已删除');
        }
        $joined = M('travel_user')->where('travelId=' . $tid . '  AND uid=' . $this->mid)->getField('type');
        $this->assign('joined', $joined);
        $cat = M('travel_cat')->findAll();
        $area = M('travel_area')->findAll();
        $this->assign('area', $area);
        $this->assign('cat', $cat);
    }

    public function route() {
        $this->_top();
        $map['isDel'] = 0;
        $map['travelId'] = intval($_REQUEST['id']);
        $content = M('travel_news')->where($map)->getField('content');
        $this->assign('content', $content);
        $this->display();
    }

    public function member() {
        $this->_top();
        $travelId = intval($_REQUEST['id']);
        $daouser = M('travel_user');
        $list = $daouser->where('travelId=' . $travelId)->findAll();
        //判断用户是否参加
        $map['travelId'] = $travelId;
        $map['uid'] = $this->mid;
        $res = $daouser->where($map)->getField('uid');
        if ($res) {
            $this->assign('isuser', 1);
        }
        $this->assign('list', $list);
        $this->display();
    }

    public function comment() {
        $this->_top();
        $travelId = intval($_REQUEST['id']);
        $maps['isDel'] = 0;
        $maps['travelId'] = $travelId;
        $list = M('travel_comment')->where($maps)->order('id DESC')->findPage(20);
        //楼层计算
//        $limit = 20;
//        $p = $_GET[C('VAR_PAGE')] ? intval($_GET[C('VAR_PAGE')]) : 1;
//        $this->assign('start_floor', intval((1 == $p) ? (($p - 1) * $limit + 1) : (($p - 1) * $limit+1) ));
        $this->assign($list);
        $this->display();
    }

    public function addComment() {
        $travelId = intval($_POST['travelId']);
        if ($travelId <= 0) {
            $this->error("错误的访问页面，请检查链接");
        }
        $maps['isDel'] = 0;
        $maps['id'] = $travelId;
        $result = $this->route->where($maps)->find();
        if (!$result) {
            $this->error('该景点不存在或已删除');
        }
        $map['content'] = t(h($_POST['content']));
        if (mb_strlen($map['content'], 'UTF8') < 5) {
            $this->error('评论内容不得小于5个字！');
        }
        $map['cTime'] = time();
        $map['travelId'] = $travelId;
        $map['uid'] = $this->mid;
        $res = M('travel_comment')->add($map);
        if ($res) {
            $this->success('评论成功！');
        } else {
            $this->error('评论失败！');
        }
    }

    public function clause() {
        $this->display();
    }

    public function join() {
//        var_dump($suid);die;
        $this->_check(intval($_REQUEST['tid']));
        $cat = M('travel_cat')->findAll();
        $area = M('travel_area')->findAll();
        $this->assign('mobile', $this->user{'mobile'});
        $this->assign('name', $this->user{'realname'});
        $this->assign('area', $area);
        $this->assign('cat', $cat);
        $this->display();
    }

    public function pay() {
        $this->_check(intval($_REQUEST['tid']));
        $tid = intval($_REQUEST['tid']);
        $type = intval($_REQUEST['type']);
        $maps['isDel'] = 0;
        $maps['id'] = $tid;
        $res = $this->route->where($maps)->field('id,title,cost,cost2')->find();
        //判断那种价格
        if ($type == 1) {
            $price = $res['cost'];
        } elseif ($type == 2) {
            $price = $res['cost2'];
        } else {
            $this->error('非法操作');
        }
        $this->assign($res);
        $this->assign('price', $price);
        //查看账户余额
        $myMoney = Model('Money')->getMoneyCache($this->mid);
        if (!$myMoney) {
            $myMoney = 0;
        }
        $this->assign('myMoney', $myMoney);
        if ($myMoney < $price*100) {
            $this->assign('less', 1);
        }
        $cat = M('travel_cat')->findAll();
        $area = M('travel_area')->findAll();
        $this->assign('area', $area);
        $this->assign('cat', $cat);
        $this->assign('type', $type);
        $this->display();
    }

    public function doPay() {
        $this->_check(intval($_POST['tid']));
        $tid = intval($_POST['tid']);
        $type = intval($_POST['type']);
        $maps['isDel'] = 0;
        $maps['id'] = $tid;
        $route = $this->route->where($maps)->field('id,title,cost,cost2,sTime')->find();
        //判断那种价格
        if ($type == 1) {
            $price = $route['cost'];
        } elseif ($type == 2) {
            $price = $route['cost2'];
        } else {
            $this->error('非法操作');
        }
        $url = U('travel/Index/route',array('id'=>$tid));
        $res = Model('Money')->moneyOut($this->mid, $price*100, '旅游 '.$route['title'], $url);
        if ($res) {
            $data['travelId'] = $route['id'];
            $data['uid'] = $this->mid;
            $data['realname'] = $this->user['realname'];
            $data['tel'] = $this->user['mobile'];
            $data['sex'] = $this->user['sex'];
            $data['sid'] = $this->user['sid'];
            $data['type'] = $type;
            $data['cTime'] = time();
            $result = M('travel_user')->add($data);
            if ($result) {
                $this->route->where('id=' . $tid)->setInc('joinCount');
                $this->route->where('id=' . $tid)->setDec('limitCount');
                //短信推送
                $msg = "亲爱的".$this->user['realname']."同学,您已经成功预订".date('y-m-d',$route['sTime']).$route['title']."门票，至景区或者酒店请您出示该短信，报姓名入园；服务热线：0512-69330061（PocketUni-旅游平台）";
                service('Sms')->sendsms($this->user['mobile'], $msg);
                $this->assign('jumpUrl', U('travel/Index/route', array('id' => $tid)));
                $this->success('报名成功！');
            } else {
                $this->error('报名失败！');
            }
        } else {
            $this->error('账户PU币不足，支付失败！');
        }
    }

    public function myRoute() {
        if (!$this->mid) {
            $this->error('请先登录！');
        }
        $map['b.uid'] = $this->mid;
        $db_prefix = C('DB_PREFIX');
        $list = $this->route->table("{$db_prefix}travel AS a ")
                        ->join("{$db_prefix}travel_user AS b ON a.id=b.travelId")
                        ->field('a.id,a.coverId,a.title,a.cost,a.cost2,a.costExplain,a.costExplain2, b.type,b.cTime')
                        ->where($map)->order('b.id DESC')->findPage(10);
        $cat = M('travel_cat')->findAll();
        $area = M('travel_area')->findAll();
        $this->assign($list);
        $this->assign('area', $area);
        $this->assign('cat', $cat);
        $this->display();
    }

    private function _check($tid) {
        if (!$this->mid) {
            $this->error("请先登录");
        }
        $travelId = $tid;
        if ($travelId <= 0) {
            $this->error("错误的访问页面，请检查链接");
        }
        $maps['isDel'] = 0;
        $maps['id'] = $travelId;
        $res = $this->route->where($maps)->find();
        if (!$res) {
            $this->error('该景点不存在或已删除');
        }
        if ($res['deadline'] < time()) {
            $this->error('报名时间已截止');
        }
        if ($res['limitCount'] < 1) {
            $this->error('人员已满');
        }
        $result = M('travel_user')->where('travelId=' . $travelId . '  AND uid=' . $this->mid)->find();
        if ($result) {
            $this->error('您已报名');
        }
    }

    public function partner(){
        $dao = M('travel_partner');
        $withPic = $dao->where('has_pic=1')->order('display_order ASC')->findAll();
        $this->assign('withPic', $withPic);
        $noPic = $dao->where('has_pic=0')->order('display_order ASC')->findAll();
        $this->assign('noPic', $noPic);
        $this->display();
    }

}

?>