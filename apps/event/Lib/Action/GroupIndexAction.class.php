<?php

/**
 * FrontAction
 * 部落页面
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class GroupIndexAction extends GroupBaseAction {

    protected $group;

    public function _initialize() {
        parent::base();
        $this->group = D('EventGroup');
          $domain = parse_url($_SERVER['HTTP_HOST']);
         $domain = substr($domain['path'], 0, strpos($domain['path'], '.'));
         $this->assign('domain',$domain);
         $this->setTitle('校园部落');
    }

    public function index() {
        if ($_GET['cat'] == 'all') {
            unset($_SESSION['group_searchCat']['cat']);
        } elseif ($_GET['cat']) {
            $_SESSION['group_searchCat']['cat'] = t($_GET['cat']);
        }
        if ($_GET['cid0'] == 'all') {
            unset($_SESSION['group_searchCat']['cid0']);
        } elseif ($_GET['cid0']) {
            $_SESSION['group_searchCat']['cid0'] = intval($_GET['cid0']);
        }
        if ($_GET['year'] == 'all') {
            unset($_SESSION['group_searchCat']['year']);
        } elseif ($_GET['year']) {
            $_SESSION['group_searchCat']['year'] = t($_GET['year']);
        }
        if ($_GET['school'] == 'all') {
            unset($_SESSION['group_searchCat']['school']);

        } elseif ($_GET['school']) {
            $_SESSION['group_searchCat']['school'] = intval($_GET['school']);
        }
        if ($_GET['category'] == 'all') {
            unset($_SESSION['group_searchCat']['category']);

        } elseif ($_GET['category']) {
            $_SESSION['group_searchCat']['category'] = intval($_GET['category']);
        }
        if(isset($_POST['title'])){
            $searchTitle = t($_POST['title']);
            if (mb_strlen($searchTitle, 'utf8') < 1) {
                unset($_SESSION['group_searchCat']['title']);
            }else{
                $_SESSION['group_searchCat']['title'] = $searchTitle;
            }
        }
        $map['is_del'] = 0;
        $map['disband'] = 0;
        $map['school'] = $this->sid;
        if($_SESSION['group_searchCat']['title']){
            $map['name'] = array('like', "%" . $_SESSION['group_searchCat']['title'] . "%");
            $this->assign('searchTitle', $_SESSION['group_searchCat']['title']);
            $this->setTitle('搜索' . $this->appName);
        }
        if ($_SESSION['group_searchCat']['cid0']) {
            $map['cid0'] = $_SESSION['group_searchCat']['cid0'];
        }
        if ($_SESSION['group_searchCat']['category']) {
            $map['category'] = $_SESSION['group_searchCat']['category'];
        }
        if ($_SESSION['group_searchCat']['year']) {
            $map['year'] = $_SESSION['group_searchCat']['year'];
        }
        if ($_SESSION['group_searchCat']['school']) {
            $map['sid1'] = $_SESSION['group_searchCat']['school'];
            if($map['sid1']==-1){
                  $schoolname = '校级';
            }else{
            $schoolname = tsGetSchoolName($_SESSION['group_searchCat']['school']);
            }
        }else{
               $schoolname = '全校';
        }
        switch ($_SESSION['group_searchCat']['cat']) {
            case 'manage':
                $member = D('GroupMember')->where("level IN (1,2) AND uid=" . $this->mid)->field('gid')->findAll();
                $ids = getSubByKey($member, 'gid');
                $map['id'] = array('in', $ids);
                break;
            case 'join':
                $member = D('GroupMember')->where("level=3 AND uid=" . $this->mid)->field('gid')->findAll();
                $ids = getSubByKey($member, 'gid');
                $map['id'] = array('in', $ids);
                break;
            default:
        }
        if ($init = $this->_uninit()) {
            $uninit = 'uninit';
            $this->assign('init',$init);
            $this->assign('uninit',$uninit);
        }

        $result = $this->group->getGroupList($map, $this->mid,'activ_num DESC, id DESC');
        $this->assign('year',$this->_getYear());
        $this->assign('schoolname',$schoolname);
        $this->assign('catList',$this->_getCategoryList());
        $this->assign($result);
        $this->display();
    }

    private function _uninit() {
        $map['is_del'] = 0;
        $map['is_init'] = 0;
        $map['disband'] = 0;
        $map['school'] = $this->sid;
        $map['uid'] = $this->mid;
        $result = $this->group->where($map)->field("id,name")->limit(4)->findAll();
        return $result;
    }

    private function  _getYear() {
        $thisYear = date('y', time());
        $years = array();
        for ($i = 9; $i <= $thisYear; $i++) {
            $years[] = sprintf("%02d", $i);
        }
        return $years;
    }

    private function _getCategoryList() {
        $res = M('group_category')->field('id,title')->findAll();
        return $res;
        ;
    }

    public function newSubSchool() {
        $school = model('Schools')->makeLevel0Tree($this->sid);
        $this->assign('school', $school);
        $this->display();
    }
   //添加部落
    public function add(){
        $cate=$this->_getCategoryList();
        $year=$this->_getYear();
        $school = model('Schools')->where('pid='.$this->sid)->field('id,title')->findAll();
        $this->assign('cate',$cate);
        $this->assign('year',$year);
        $this->assign('school',$school);
        $pai = $this->groupTopTen();
        $this->assign('pai',$pai);
        $this->display();
    }

    //检查部落名是否可用
    public function ajaxName(){
        $name = t($_POST['name']);
        $map['name'] = $name;
        $map['school'] = $this->sid;
        $result = D('group')->where($map)->find();
        $arr['sid'] = $this->sid;
        $arr['name'] = $name;
        $res = D('temporary_tribe')->where($arr)->find();
        if($result || $res){
            echo 1;
        }else{
            echo 0;
        }
    }

    public function doadd(){
        $info = tsUploadImg();
        if ($info['status']) {
            $data['img'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        } elseif ($info['info'] == '没有选择上传文件') {
            $this->error($info['info']);
        }
        if(empty($_POST['name'])){
            $this->error('部落名称为空！');
        }elseif((mb_strlen($_POST['name'], 'UTF8')<2)||(mb_strlen($_POST['name'], 'UTF8')>10)){
            $this->error('部落名称不符合规范！');
        }else{
            $data['name'] = t($_POST['name']);
        }
        if(empty($_POST['department'])){
            $this->error('部门为空！');
        }else{
            $data['department'] = $_POST['department'];
        }
        if(empty($_POST['school1'])){
            $this->error('学院为空！');
        }else{
            $data['sid1'] = $_POST['school1'];
        }
        if(empty($_POST['year'])){
            $this->error('年级为空！');
        }else{
            $data['year'] = $_POST['year'];
        }
        if(empty($_POST['categroy'])){
            $this->error('分类为空！');
        }else{
            $data['cid'] = $_POST['categroy'];
        }
        $data['explain'] = t($_POST['explain']);
        $data['ctime'] = time();
        $data['sid'] = $this->sid;

        $data['uid'] = $this->mid;
        $result=D('temporary_tribe')->add($data);
        if($result){
            $this->success('提交成功，等待审核！');
        }else{
            $this->error('提交失败！');
        }
    }

    public function mygroup(){
        $map['uid'] = $this->mid;
        $map['status'] != 2;
        $dao = D('temporary_tribe');
        $list=$dao->where($map)->findPage(10);
        //dump($list);die;

        $school = $arr['title'];
        $daocate = D('group_category');
        foreach($list['data'] as &$val){
            $arr= model('Schools')->where('id='.$val['sid'])->field('id,title')->find();
            $val['sid'] = $arr['title'];
            $res=$daocate->where('id='.$val['cid'])->field('id,title')->find();
            $val['cid']=$res['title'];
        }
        //dump($list);die;
        $this->assign('list',$list);
        $this->display();
    }

    //提供首页数据
    public function groupTopTen(){
        $res = D('school')->where('id='.$this->sid)->field('id,cityId')->find();
        $list = D('citys')->where('id='.$res['cityId'])->field('id,pid')->find();
        $prov_id = $list['pid'];
        $pai = X('Mmc')->groupProvTop10($prov_id,1);
        foreach($pai as $key=>&$val){
            if($key==0){
                $val['mark']=1;
            }elseif(($key==1)||($key==2)){
                $val['mark']=2;
            }else{
                $val['mark']=3;
            }
            $val['logo'] = tsMakeThumbUp($val["logo"],105,105);
            $result = D('school')->where('id='.$val['school'])->field('id,title')->find();
            $val['school'] = $result['title'];
        }
        return $pai;
    }

    //提供排行榜数据
    public function groupTop(){
        $type = intval($_POST['type']);
        $category = intval($_POST['category']);
        if(!$type){
            $type = 1;
        }
        if(!$category){
            $category = 1;
        }
//        $res = D('school')->where('id='.$this->sid)->field('id,cityId,title')->find();
        //总排行
        if($type == 1){
//            $list = D('citys')->where('id='.$res['cityId'])->field('id,pid')->find();
//            $prov_id = $list['pid'];
            $prov_id = 1;
            $pai = X('Mmc')->groupProvTop10($prov_id,$category);
        }else{
            $pai = X('Mmc')->groupSchoolTop10($this->sid,$category);
        }
        foreach($pai as &$val){
            $val['logo'] = getGroupThumb($val["logo"],100,100);
            $val['school'] = tsGetSchoolName($val['school']);
        }
        if(!$pai){
            $pai=0;
        }
        echo json_encode($pai);
        //dump($pai);
    }
}

?>
