<?php

/**
 * WeiboAction
 * 微博管理
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class WeiboAction extends TeacherAction {

    public function _initialize() {
        //管理权限判定
        parent::_initialize();
        if(!$this->rights['can_admin'] || !isTuanRole($this->sid)){
            echo('无权查看');
            die;
        }
    }

    public function index() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['es_weibo'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['es_weibo']);
        } else {
            unset($_SESSION['es_weibo']);
        }
        $this->assign('isSearch', isset($_POST['isSearch']) ? '1' : '0');

        $map = array();
        $map['b.sid'] = $this->sid;
        $_POST['num'] && $map['b.email'] = t($_POST['num']).$this->school['email'];
        $_POST['content'] && $map['a.content'] = array('like', '%' . t($_POST['content']) . '%');
        $order = 'weibo_id DESC';
        $map['a.isdel'] = 0;
        $db_prefix = C('DB_PREFIX');
        $data['list'] = M('weibo')->table("{$db_prefix}weibo AS a ")
                ->join("{$db_prefix}user AS b ON a.uid=b.uid")
                ->where($map)->field('a.*, b.sid')->order($order)->findPage(20);
        foreach($data['list']['data'] as $k=>$v){
            $data['list']['data'][$k]['expend'] = D('Weibo','weibo')->__parseTemplate($v);
        }
        $this->assign($_GET);
        $this->assign($data);
        $this->assign($_POST);
        $this->display('list');

//        $users = M('user')->where('sid=505')->field('uid')->findAll();
//        $uids = getSubByKey($users, 'uid');
//        $map = array();
//        $map['uid'] = array('in', $uids);
//        $map['isdel'] = 0;
//        $data['list'] = D('Operate','weibo')->getAdminSpaceList($map);

/**                                    <?php if( $vo['transpond_id'] ):?>
                            <div class="feed_quote">
                                <div class="q_tit">
                                <div class="q_con">
                                    <space uid="vo.expend.uid" class="null">@{uname}</space>：
                                    {$vo.expend.content|getShort=###,140,'...'|format=true}
                                </div>
                            </div>
                            <?php else:?>
                            {$vo.expend}
                            <?php endif;?>
                            <a href="{:U('home/space/detail',array('id'=>$vo['weibo_id']))}" target="_blank">查看详情»</a>
 */
    }

    public function delWeibo(){
        $weibo_ids = explode(',',$_POST['weibo_id']);
        $_LOG['uid'] = $this->mid;
        $_LOG['type'] = '2';
        $_LOG['ctime'] = time();
        foreach($weibo_ids as $weibo_id){
            $weibo = M( 'Weibo' )->where( 'weibo_id='.intval($weibo_id) )->find();
            if($weibo){
                $user = M('user')->field('sid')->where('uid='.$weibo['uid'])->find();
                if(isTuanRole($user['sid'])){
                    $data[] = '菁英人才 - 删除微博';
                    $data[] = $weibo;
                    $_LOG['data'] = serialize($data);
                    M('AdminLog')->add($_LOG);

                    $res = D("Operate",'weibo')->deleteMini(intval($weibo_id),intval($data[1]['uid']));
                    unset($data);
                }
            }
        }
        if($res){
            echo 1;
        }else{
            echo 0;
        }
    }

//    public function follow(){
//        $gdata['follow_group_id'] = 103;
//        $gdata['uid'] = 96510;
//        //所有菁英人才学生
//        $users = M('user')->where('sid=505 && uid!=96510')->field('uid')->findAll();
//        $daoFollow = D('weibo_follow');
//        $daoFollowGroup = M('weibo_follow_group_link');
//        $data['uid'] = 96510;
//        $backRow['fid'] = 96510;
//        foreach($users as $user){
//            //加关注
//            $data['fid'] = $user['uid'];
//            $followId = $daoFollow->add($data);
//            if($followId){
//                echo $user['uid'].' ';
//                //关注分组
//                $gdata['follow_id'] = $followId;
//                $daoFollowGroup->add($gdata);
//                //互相关注
//                $backRow['uid'] = $user['uid'];
//                $daoFollow->add($backRow);
//            }
//        }
//        //修改用户缓存信息
//        S('S_userInfo_96510', null);
//    }
}
