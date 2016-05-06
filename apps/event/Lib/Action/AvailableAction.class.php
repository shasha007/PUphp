<?php

/**
 * Created by PhpStorm.
 * User: zhuhaibing06
 * Date: 2016/2/18
 * Time: 16:25
 */

class AvailableAction extends TeacherAction
{

    private $provinceId;

    private $cityId;

    function _initialize()
    {
        parent::_initialize();
        $editSid = $this->school['id'];
        if($_SESSION['ThinkSNSAdmin'] != '1' && !$this->user['can_admin'] && $this->user['event_level'] > 10 && $this->user['sid1']){
            $editSid = $this->user['sid1'];
        }
        $this->assign('editSid',$editSid);
        //todo 从session中获取当前用户所在学校的省份、城市ID
        $this->provinceId = intval($_SESSION['userInfo']['schoolEvent']['provinceId']);
        $this->cityId = intval($_SESSION['userInfo']['schoolEvent']['cityId']);
    }

    /**
     * 下发文件首页
     */
    public function index()
    {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_REQUEST)) {
            $_SESSION['es_searchUser'] = serialize($_REQUEST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_REQUEST = unserialize($_SESSION['es_searchUser']);
        } else {
            unset($_SESSION['es_searchUser']);
        }
        if (intval($_REQUEST['id'])) {
            $map['p.id'] = intval($_REQUEST['id']) ;
        }
        if (t($_REQUEST['title'])) {
            $map['p.title'] = array('LIKE','%'.t($_REQUEST['title']).'%') ;
        }
        $map['p.isDel'] = 0 ;
        $sid = intval($_SESSION['userInfo']['sid']);
        if (isTuanRole($sid)) {
            $map['_string'] = 'p.uid = '.$this->mid.' OR p.sid = '.$sid;
            $list = M('')->table('ts_file_push as p ')->where($map)->order('id DESC')->findpage(20) ;
        }else{
            $map['s.sid'] = intval($_SESSION['userInfo']['sid']) ;
            $list = M('')->table('ts_file_push_school as s')->join('ts_file_push as p on p.id = s.pushId')->where($map)->order('p.id DESC')->findpage(20) ;
        }
        foreach ($list['data'] as $key => $value) {
            $list['data'][$key]['schooleCount'] = M('file_push_school')->where('pushId='.$value['id'])->count() ;
            $list['data'][$key]['readCount'] = M('file_push_school')->where('pushId='.$value['id'].' and status =1 ')->count() ;
            $list['data'][$key]['replyCount'] = M('file_push_reply')->where('pushId='.$value['id'].' and isDel=0')->count() ;
        }
        $this->assign('list',$list) ;
        $this->assign($_REQUEST) ;
        $this->display();
    }

    /**
    *增加下发文件
    */
    public function addFile()
    {
        $dao = M('school');
        $sid = intval($_SESSION['userInfo']['sid']);
        $province = $dao->where('id='.$sid)->getField('provinceId');
        $area = M('file_push_school')->where('pushId='.intval($_REQUEST['id']))->group('pushId')->field('areaId')->findAll();
        if (!empty($area))
        {
            $dao = M('school');
            foreach ($area as $k => $v)
            {
                $area[$k]['school'] = $dao->where('cityId=' . $v['areaId'])->field('id,title')->findAll();
            }
            $this->assign('area', $area);
            $res = M('file_push_school')->where('pushId='.intval($_REQUEST['id']))->field('sid')->findAll();
            $sids = getSubByKey($res, 'sid');
            $this->assign('sids', $sids);
        }
        $citys = M('citys')->where('pid = '.$province)->findAll();
        $this->assign('citys', $citys);
        $this->display();
    }

    /**
    *添加/修改 下发文件
    */
    public function doAddFile()
    {
        //校验参数
        $school = 0 ;
        foreach ($_REQUEST['area'] as $key => $value) {
           if (!empty($_REQUEST['schools'.$value])) {
               $school = 1 ;
               berak ;
           }
        }
        if ($school === 0) {
            $this->error('下发学校不可为空')  ;
        }
        $required_field = array(
            'title' => '通知标题',
            'content' => '通知内容主体',
            'template' => '要回执的格式主体内容',
        );
        foreach ($required_field as $k => $v) {
            if (empty($_REQUEST[$k]))
                $this->error($v . '不可为空');
        }
        $data = $_REQUEST ;
        $sid = intval($_SESSION['userInfo']['sid']);
        $data['uid'] = $this->mid ;
        $data['sid'] = $sid ;
        if ($_REQUEST['pushId']) {
            $result = D('FilePush')->dosavefile($_REQUEST['pushId'],$data) ;
            if ($result) {
                $this->success('修改成功') ;
            }
            $this->error('修改失败请稍候再试') ;            
        }else{
            $result = D('FilePush')->doaddfile($data) ;
        }
        if ($result) {
            $this->success('添加成功') ;
        }
        $this->error('添加失败请稍候再试') ;
    }

    /**
    *删除下发文件
    */
    public function delFile(){
        $pushId = intval($_REQUEST['id']) ;
        if (D('FilePush')->dodelfile($pushId)) {
            $this->success('删除成功')  ;
        }else{
            $this->error('删除失败') ;
        }
    }

    /**
    *编辑页面
    */
    public function editFile()
    {
        $dao = M('school');
        $sid = intval($_SESSION['userInfo']['sid']);
        $province = $dao->where('id='.$sid)->getField('provinceId');
        $area = M('file_push_school')->where('pushId='.intval($_REQUEST['id']))->group('areaId')->field('areaId')->findAll();
        if (!empty($area))
        {
            $dao = M('school');
            foreach ($area as $k => $v)
            {
                $area[$k]['school'] = $dao->where('cityId=' . $v['areaId'])->field('id,title')->findAll();
            }
            $this->assign('area', $area);
            $res = M('file_push_school')->where('pushId='.intval($_REQUEST['id']))->field('sid')->findAll();
            $sids = getSubByKey($res, 'sid');
            $this->assign('sids', $sids);
        }
        $citys = M('citys')->where('pid = '.$province)->findAll();
        $info = M('file_push')->where('id='.intval($_REQUEST['id']))->find() ;
        $this->assign('citys', $citys);        
        $this->assign('info',$info) ;
        $this->display('addFile') ;
    }

    /**
    * 查看
    */
    public function chekckSchool()
    {
        $sid = intval($_SESSION['userInfo']['sid']);
        $pushId = intval($_REQUEST['id']) ;
        $map['sid'] = $sid ;
        $map['pushId'] = $pushId ;
        $push_school_info = M('file_push_school')->where($map)->find() ;
        if ($sid === intval($push_school_info['sid']) && 0 === intval($push_school_info['status'])) {
            D('FilePush')->check_file($pushId,$sid) ;
        }
        $push_info = M('file_push')->where('id='.$pushId)->find() ;
        if ($template = M('file_push_reply')->where($map)->getField('content')) {
            $push_info['template'] = $template ;
        }
        $this->assign('info',$push_info) ;
        $this->display() ;
    }

    /**
    *学校提交回执
    */

    public function doreply(){
        $sid = intval($_SESSION['userInfo']['sid']);
        $data['sid'] = $sid ;
        $data['pushId'] = intval($_REQUEST['pushId']) ;
        $data['content'] = t($_REQUEST['template']) ;
        if (isTuanRole($sid)) {
            $this->error('您没有权限提交回执') ;
        }
        if(D('FilePush')->do_reply($data)){
            $this->success('提交成功') ;
        } ;
        $this->error('提交失败请稍后再试');
    }

    /**
     * 学校查看、回执等信息汇总
     */
    public function schools(){
        if (!empty($_REQUEST)) {
            $_SESSION['es_searchUser'] = serialize($_REQUEST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_REQUEST = unserialize($_SESSION['es_searchUser']);
        } else {
            unset($_SESSION['es_searchUser']);
        }        
        $status = t($_REQUEST['status']) ;
        $pushId = intval($_REQUEST['id']) ;
        $map['id'] = $pushId ;
        $push_school = M('file_push')->where($map)->find() ;
        switch ($status) {
            case 'read':
                $s_map['status'] = 1 ;
                break;
            case 'reply':
                $s_map['isReply'] = 1 ;
                break;
            default:
                break;
        }
        $this->assign('data',$push_school);
        $s_map['pushId'] = $pushId;
        $list =  M('file_push_school')->where($s_map)->field('sid,status,isReply,pushId')->findpage(20) ;
        $this->assign('list',$list) ;
        $this->display() ;
    }

    /**
    *团省委查看回执
    */
    public function reply(){
        $map['pushId'] = intval($_REQUEST['pushId']) ;
        $map['sid'] = intval($_REQUEST['sid']) ;
        $push= M('file_push')->where('id='.$map['pushId'])->find() ;
        $info = M('file_push_reply')->where($map)->find() ;
        $this->assign('push',$push) ;
        $this->assign('info',$info) ;
        $this->display() ;
    }

    /**
     * 学校上报 文件列表
     */
    public function lists()
    {
        if (!empty($_POST)) {
            $_SESSION['file_put_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_REQUEST = unserialize($_SESSION['file_put_search']);
        } else {
            unset($_SESSION['file_put_search']);
        }
        if (t($_POST['title'])) {
            $map['title'] = array('LIKE','%'.t($_POST['title']).'%') ;
        }
        $sid = intval($this->sid);
        $map['isDel'] = 0;
        $map['sid'] = $sid;
        if(isTuanRole($sid))
        {
            unset($map['sid']);
            $map['provinceId'] = $this->provinceId;
        }
        $lists = M('file_put')->where($map)->order('id DESC')->findpage(20);
        $this->assign('list',$lists);
        $this->display();
    }

    /**
     * 学校上报内容查看
     */
    public function viewPutFile()
    {
        if(!empty($_GET['id']))
        {
            $id = intval($_GET['id']);
            $map['id'] = $id;
            $info = M('file_put')->where($map)->find();
            $this->assign('info',$info);
        }
        $this->display();
    }

    /**
     * 学校上报信息增加模板渲染
     */
    public function addPutFile()
    {
        if(!empty($_GET['id']))
        {
            $id = intval($_GET['id']);
            $map['id'] = $id;
            $info = M('file_put')->where($map)->find();
            $this->assign('info',$info);
        }
        $this->display();
    }

    /**
     * 学校上报信息 数据处理
     */
    public function doAddPutFile()
    {
        $data = $_POST;
        $data['provinceId'] = $this->provinceId;
        $data['cityId'] = $this->cityId;
        $data['sid'] = $this->sid;
        $data['uid'] = $this->mid;
        $dao = D('FilePut');
        if(empty($data['putId']))
        {
            $flag = $dao->doaddfile($data);
        }
        else
        {
            $id = intval($data['putId']);
            $flag = $dao->dosavefile($id,$data);
        }
        if($flag)
        {
            $this->success('操作成功');
        }
        else
        {
            $this->error('操作失败');
        }
    }

    /**
     * 删除上报文件
     */
    public function delPutFile()
    {
        $putId = intval($_REQUEST['id']) ;
        if (D('FilePut')->doDelFile($putId)) {
            $this->success('删除成功')  ;
        }else{
            $this->error('删除失败') ;
        }
    }

    /**
     * 附件管理列表
     */
    public function attach()
    {
        $c_type = empty($_GET['c_type']) ? 0:intval($_GET['c_type']);

        $sid = intval($this->sid);
        if($c_type == 0)
        {
            if(isTuanRole($sid))
            {
                $map['sid'] = $sid;
                $lists = M('file_push_attach')->where($map)->order('id DESC')->findpage(20);
            }
            else
            {
                $map['S.sid'] = $sid;
                $map['A.id'] = array('NEQ','');
                $lists = M('')->table('ts_file_push_school S')->join('ts_file_push_attach A on S.pushId=A.pushId')->where($map)->order('A.id DESC')->findpage(20);
            }
            foreach($lists['data'] as &$data)
            {
                $data['title'] = M('file_push')->where('id = '.$data['pushId'])->getField('title');
            }
        }
        else
        {
            if(isTuanRole($sid))
            {
                $s_map['provinceId'] = $this->provinceId;
                $schoolArr = M('school')->where($s_map)->field('id')->select();
                foreach($schoolArr as $key=>$val)
                {
                    $schools[] = $val['id'];
                }
                $map['sid'] = array('IN',join(',',$schools));
                $lists = M('file_put_attach')->where($map)->order('id DESC')->findpage(20);
            }
            else
            {
                $map['sid'] = $sid;
                $lists = M('file_put_attach')->where($map)->order('id DESC')->findpage(20);
            }
            foreach($lists['data'] as &$data)
            {
                $data['title'] = M('file_put')->where('id = '.$data['putId'])->getField('title');
            }
        }
        $this->assign('list',$lists);
        $this->assign('c_type',$c_type);
        $this->display();
    }

    /**
     * 配置绑定后台可以查看下发文件的人员信息 模板
     */
    public function config()
    {
        $map['sid'] = intval($this->sid);
        $config = M('file_config')->where($map)->find();
        $this->assign('config',$config);
        $this->display();
    }

    /**
     * 配置绑定数据处理
     */
    public function doConfig()
    {
        $data = $_POST;
        $data['provinceId'] = $this->provinceId;
        $data['cityId'] = $this->cityId;
        $data['sid'] = intval($this->sid);
        $data['cTime'] = time();
        unset($data['__hash__']);
        $map['sid'] = intval($this->sid);
        if(M('file_config')->where($map)->find())
        {
            $this->error('已经绑定接收人，请勿重复绑定');
        }
        $flag = M('file_config')->add($data);
        if($flag)
        {
            $this->redirect('event/Available/index',array(),0,'绑定成功');
        }
        else
        {
            $this->error('绑定失败');
        }
    }

    /**
     * 发送短信通知
     */
    public function OASendMessage()
    {
        $pushId = intval($_GET['id']);
        $map['sid'] = intval($_GET['sid']);
        $map['can_available'] = 1;
        $p_map['id'] = $pushId;
        $title = M('file_push')->where($p_map)->getField('title');
        $msg = '【团省委】下发了标题为：【'.$title.'】文件请及时查收！';
        $mobiles = M('user')->where($map)->field('mobile')->select();
        foreach($mobiles as $k=>$v)
        {
            $mobileArr[] = $v['mobile'];
        }
        $status = service('Sms')->sendsms(join(',',$mobileArr), $msg);
        if($status['status'] == 1)
        {
            $this->success('通知已发送');
        }
        else
        {
            $this->error('通知发送失败');
        }
    }

    /**
     * ajax获取学校信息
     */
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