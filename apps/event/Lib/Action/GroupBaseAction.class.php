<?php

/**
 * <b>注意:$this->未显示定义的变量名将会取值。对应变量名的model对象</b>
 * @uses Action
 * @package Action::group
 * @version $id$
 * @copyright 2009-2011 ThinkSNS
 * @author songhongguang
 */
class GroupBaseAction extends Action {

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    protected $isadmin;
    protected $gid;
    protected $ismember;
    protected $config;
    protected $groupinfo;
    protected $siteTitle;
    protected $is_invited;
    protected $sid;
    protected $school;
    protected $group;
    protected $smid = 0;
    protected $rights;

    protected function _initialize() {
        $this->group = D('EventGroup', 'event');
        $this->base();

        // 部落id
        if (isset($_GET['gid']) && intval($_GET['gid']) > 0) {
            $this->gid = intval($_GET['gid']);
        } else if (isset($_POST['gid']) && intval($_POST['gid']) > 0) {
            $this->gid = intval($_POST['gid']);
        } else {
            $this->error('gid 错误');
        }
        $groupinfo = $this->group->where('id=' . $this->gid . " AND is_del=0 AND disband=0 AND school=" . $this->sid)->find();
        /* + ------ 后台全局限制 START */
        $groupinfo['openWeibo'] = model('Xdata')->get('group:weibo') ? $groupinfo['openWeibo'] : 0;
        $groupinfo['openBlog'] = model('Xdata')->get('group:discussion') ? $groupinfo['openBlog'] : 0;
        $groupinfo['openUploadFile'] = model('Xdata')->get('group:uploadFile') ? $groupinfo['openUploadFile'] : 0;
        /* + ------ 后台全局限制 END */
        if (!$groupinfo['id']) {
            $this->error('该部落不存在，或者被删除');
        }
        // 判读当前用户的成员状态
        $member_info = D('GroupMember')->where("uid={$this->mid} AND gid={$this->gid}")->find();
        if ($member_info) {
            if ($member_info['level'] > 0) {
                $this->ismember = 1;
                $this->assign('ismember', $this->ismember);
                if ($member_info['level'] == 1 || $member_info['level'] == 2) {
                    $this->isadmin = 1;
                    $this->assign('isadmin', $this->isadmin);
                    if ($member_info['level'] == 1) {
                        $this->assign('superadmin', $this->isadmin);
                    }
                }
                // 记录访问时间
                D('GroupMember')->where('gid=' . $this->gid . " AND uid={$this->mid}")->setField('mtime', time());
            }
        }

        $groupinfo['cname1'] = $this->group->getCategoryField($groupinfo['cid0']);
        $groupinfo['schoolName'] = $this->group->getSchoolName($groupinfo['sid1']);
        $groupinfo['type_name'] = $groupinfo['brower_level'] == -1 ? '公开' : '私密';
        $groupinfo['tags'] = D('GroupTag', 'group')->getGroupTagList($this->gid);
        $groupinfo['openUploadFile'] = (model('Xdata')->get('group:uploadFile')) ? $groupinfo['openUploadFile'] : 0;
        $groupinfo['announce'] = D('GroupAnnounce')->getFirst($this->gid);
        $this->groupinfo = $groupinfo;
        $this->assign('groupinfo', $groupinfo);
        $this->assign('gid', $this->gid);
        $this->setTitle($this->groupinfo['name'] . '校园部落');

        // 浏览权限
        if (!$this->ismember) {
            // 邀请加入
            if (M('group_invite_verify')->where("gid={$this->gid} AND uid={$this->mid} AND is_used=0")->find()) {
                $this->is_invited = 1;
                $this->assign('is_invited', $this->is_invited);
            }
            if ($groupinfo['brower_level'] == 1) {
                if (MODULE_NAME != 'Group' || (ACTION_NAME != 'index' && ACTION_NAME != 'joinGroup')) {
                    $this->redirect('event/Group/index', array('gid' => $this->gid));
                } else if ('index' == ACTION_NAME) {
                    $this->display();
                    exit;
                }
            }
        }

        // 右侧部分信息，根据模块需求调用
//        if (!in_array(MODULE_NAME, array('Log'))) {
//            // 部落热门排行
//            $hot_group_list = $this->group->getHotList($this->sid);
//            $this->assign('hot_group_list', $hot_group_list);
//        }

        // 基本配置
        $this->config = model('Xdata')->lget('group');
        $this->assign('config', $this->config);
        //站点信息
        $config = D('SchoolWeb','event')->getConfigCache($this->sid);
        $this->assign('webconfig', $config);
        if (!$this->rights['allAdmin'] && !$this->ismember && ACTION_NAME != 'joinGroup' && !$this->user['can_admin']) {
            $this->display('../GroupPublic/nojoin');
            exit;
        }
    }

    protected function base() {
        $domain = parse_url($_SERVER['HTTP_HOST']);
        $map['domain'] = substr($domain['path'], 0, strpos($domain['path'], '.'));
        $map['eTime'] = array('gt', 0);
        $school = M('school')->where($map)->find();
        $hostNeedle = get_host_needle();
        if (!$school) {
            $this->assign('jumpUrl', 'http://' . $hostNeedle);
            $this->error('此学校尚未开通校方活动！');
        }
        $this->school = $school;
        $this->sid = $school['id'];
        $this->assign('school', $school);
        if($this->mid){
            $groups = M('user_group_link')->where('uid='.$this->mid)->field('user_group_id')->findAll();
            $gids = getSubByKey($groups, 'user_group_id');
            $this->rights['allAdmin'] = in_array(C('SADMIN'), $gids);
        }
        if ($this->user['sid'] == $this->sid || $this->rights['allAdmin']) {
            $this->smid = $this->mid;
                 //是否可以进入后台
            if ($this->user['can_event'] ||$this->user['can_event2'] ||$this->user['can_gift']
                    ||$this->user['can_print']||$this->user['can_group']||$this->user['can_admin']
                    ||$this->user['can_prov_event']||$this->user['can_prov_news']||$this->user['can_prov_work']
                    ||$this->user['event_level'] != 20||$this->rights['allAdmin']) {
                $this->assign('open_admin', 1);
            }
        }


        //幻灯
        $this->assign('slide', D('Event')->getSlide($this->sid));
        global $ts;
        $install_app = $ts['install_apps'];
        $this->assign('install_app', $install_app);
        //最新部落部落
        $new_group_list = D('EventGroup')->getNewList($this->sid);
        $this->assign('new_group_list', $new_group_list);
        $this->assign('smid', $this->smid);
        $this->assign('groupPage', 'group');
        //站点信息
        $config = D('SchoolWeb','event')->getConfigCache($this->sid);
        $this->assign('webconfig', $config);
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
    }

    //执行单图上传操作
    protected function _upload($path, $save_name, $is_replace, $is_thumb, $thumb_name, $thumb_max_width) {
        if (!checkDir($path)) {
            return '目录创建失败: ' . $path;
        }

        import("ORG.Net.UploadFile");

        $upload = new UploadFile();

        //设置上传文件大小
        $upload->maxSize = '2000000';

        //设置上传文件类型
        $upload->allowExts = explode(',', strtolower('jpg,gif,png,jpeg,bmp'));

        //存储规则
        $upload->saveRule = 'uniqid';
        //设置上传路径
        $upload->savePath = $path;
        //保存的名字
        $upload->saveName = $save_name;
        //是否缩略图
        $upload->thumb = $is_thumb;
        $upload->thumbMaxWidth = $thumb_max_width;
        $upload->thumbFile = $thumb_name;

        //存在是否覆盖
        $upload->uploadReplace = $is_replace;
        //执行上传操作
        if (!$upload->upload()) {
            //捕获上传异常
            return $upload->getErrorMsg();
        } else {
            //上传成功
            return $upload->getUploadFileInfo();
        }
    }

    protected function _getSearchKey($key_name = 'k') {
        $key = '';
        // 为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (isset($_REQUEST[$key_name]) && !empty($_REQUEST[$key_name])) {
            if ($_GET[$key_name]) {
                $key = html_entity_decode(urldecode($_GET[$key_name]), ENT_QUOTES);
            } elseif ($_POST[$key_name]) {
                $key = $_POST[$key_name];
            }
            // 关键字不能超过30个字符
            if (mb_strlen($key, 'UTF8') > 30)
                $key = mb_substr($key, 0, 30, 'UTF8');
            $_SESSION['group_search_' . $key_name] = serialize($key);
        }else if (is_numeric($_GET[C('VAR_PAGE')])) {
            $key = unserialize($_SESSION['group_search_' . $key_name]);
        }else {
            unset($_SESSION['group_search_' . $key_name]);
        }
        $this->assign('search_key', h(t($key)));
        return trim($key);
    }

}