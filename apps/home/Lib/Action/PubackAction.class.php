<?php

class PubackAction extends Action {

    private $menulist;
    private $menuUrl;

    public function _initialize() {
        // $this->success(); 和 $this->error();通过isAdmin变量决定是否加载头部
        $this->assign('isAdmin', 1);
        // 检查用户是否登录管理后台, 有效期为$_SESSION的有效期
        if (!service('Passport')->isLoggedPu())
            redirect(U('home/Public/pulogin'));
        //左边功能按钮
        $this->menulist = array('乐购','广告','广告(只读)','旅游','通知公告','APP市场'
            ,'课件','消费','微博','导航','建设银行'
            ,'培训','爱心校园','奖品奖券','吐泡泡/Ta秀','举报管理'
            ,'统计报表','招聘','用户注册','活动基金','部落基金','用户管理'
            ,'扑天下','成长服务超市','部落审核','PU客服','口贷乐','营销平台','康欣统计','今日推荐','话题广场','签到','H5活动问答','口袋金后台'
            ,'专题活动', 'PU金融', 'PU金放款','用户库','客户端管理','流量管理'
        );
        //功能按钮对应网址
        $this->menuUrl = array('shop/Admin/index','home/Ad/ad','home/Ad/adread','travel/Admin/index','announce/Admin/index','appstore/Admin/index'
            ,'document/Admin/index','coupon/Admin/index','weibo/Admin/index','home/Ditu/ditus','home/Bank/ccb'
            ,'train/Admin/index','home/Donate/index','home/Lucky/index','forum/Admin/index','home/Denounce/index'
            ,'home/Tj/index','job/Admin/index','home/Reg/index','fund/Admin/index','fund/Group/cyList','home/UserManage/index'
            ,'home/Ptx/index','grow/Admin/index','home/Tribe/index','home/PassManage/index','shop/Pocket/index','home/Activity/index','shop/Kangxin/kxCount','home/Eventimage/imglist','home/Themes/index'
            ,'home/CheckIn/index','home/Asking/index','shop/Pocket/bankList','home/Event/index', 'pufinance/Admin/index', 'pufinance/PufinanceOrder/orderList','home/RedPerson/index'
            ,'client/Index/index','pufinance/TrafficAdmin/index'
        );

        $this->assign('menuname', $this->menulist);
        $this->assign('menuurl', $this->menuUrl);
        $pubackMenu = $this->cachePubackMenu();
        $this->assign('pubackMenu', $pubackMenu);
    }

    public function index(){
        $this->display();
    }

    private function cachePubackMenu(){
        $userInfo = D('User', 'home')->getUserByIdentifier($this->mid, 'uid');
        //$userInfo = S('S_userInfo_'.$this->mid);
        if(!isset($userInfo['pubackMenu'])){
            $userInfo['pubackMenu'] = array();
            foreach ($this->menuUrl as $k=>$v) {
                if ( service('SystemPopedom')->canPuback($this->mid, $v) ) {
                    $userInfo['pubackMenu'][] = $k;
                }
            }
            S('S_userInfo_'.$this->mid, $userInfo);
        }
        return $userInfo['pubackMenu'];
    }

}