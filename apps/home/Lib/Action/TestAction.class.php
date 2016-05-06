<?php
error_reporting(E_ALL);
class TestAction extends Action{

        var $page;
	var $count;

        public function __construct(){
            echo '<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>';
            parent::__construct();
            $this->page       = $_REQUEST['page']       ? intval( $_REQUEST['page'] )     : 1;
            $this->count      = $_REQUEST['count']      ? intval( $_REQUEST['count'] )    : 4;
            $this->mid = 33654;
        }
     public function addEvent() {
        $res['status'] = 0;
        $res['msg'] = '';
        $user = D('User', 'home')->getUserByIdentifier($this->mid);
        $canEvent = $user['can_add_event'];
        $user_sid = $user['sid'];
        if (!$canEvent) {
            $res['msg'] = '您没有权限发布活动';
            var_dump($res);die;
        }
        //部落活动
        $group = M('event_group')->where('uid=' . $this->mid)->field('gid')->findAll();
        $res['group'] = array();
        if ($group) {
            $daogroup = M('group');
            foreach ($group as $k => $v) {
                $group[$k]['title'] = $daogroup->getField('name', 'id = ' . $v['gid']);
            }
             $res['group'] = $group;
        }
        $schoolOrga = M('school_orga')->field('id,title')->where('sid='.$user_sid)->findAll();
        $res['schoolOrga'] = $schoolOrga;
        $schools = model('Schools')->makeLevel0Tree($user_sid);
        $school = array();
        foreach($schools as $v){
            $row = array();
            $row['id'] = $v['id'];
            $row['title'] = $v['title'];
            $school[] = $row;
        }
        $res['school'] = $school;
        $res['status'] = 1;
        var_dump($res);die;
    }
    public function photo(){
        $this->display();
    }

    public function getTuanSchools()
    {
        $schools = array(
            '南京大学','东南大学','南京航空航天大学','南京理工大学','河海大学','南京农业大学','中国矿业大学','中国药科大学','江南大学','南京森林警察学院',
            '南京师范大学','南京医科大学','南京中医药大学','南京艺术学院','南京体育学院','江苏第二师范学院','江苏开放大学','南京工业大学','南京财经大学','南京信息工程大学',
            '南京邮电大学','南京林业大学','南京审计学院','南京工程学院','江苏警官学院','江苏经贸职业技术学院','南京工业职业技术学院','南京科技职业学院','南京交通职业技术学院','南京特殊教育师范学院',
            '江苏省南京工程高等职业学校','南京信息职业技术学院','江苏海事职业技术学院','南京铁道职业技术学院','三江学院','钟山职业技术学院','应天职业技术学院','正德职业技术学院','中国传媒大学南广学院','金肯职业技术学院',
            '南京航空航天大学金城学院','无锡职业技术学院','江苏师范大学','徐州医学院','江苏建筑职业技术学院','徐州工业职业技术学院','江苏理工学院','常州大学','常州信息职业技术学院','常州纺织服装职业技术学院',
            '常州工程职业技术学院','苏州大学','苏州科技学院','常熟理工学院','苏州工艺美术职业技术学院','苏州经贸职业技术学院','南通大学','南通航运职业技术学院','江苏工程职业技术学院','淮海工学院',
            '淮阴师范学院','淮阴工学院','江苏食品药品职业技术学院','盐城师范学院','盐城工学院','扬州大学','扬州工业职业技术学院','江苏大学','江苏科技大学','南京晓庄学院',
            '金陵科技学院','南京城市职业学院','南京机电职业技术学院','南京旅游职业学院','江苏建康职业学院','无锡太湖学院','无锡商业职业技术学院','无锡城市职业技术学院','江苏信息职业技术学院','无锡工艺职业技术学院',
            '无锡科技职业学院','江阴职业技术学院','无锡南洋职业技术学院','江南影视艺术职业学院','太湖创意职业技术学院','徐州工程学院','九州职业技术学院','徐州幼儿师范高等专科学校','徐州生物工程职业技术学院','常州工学院',
            '常州轻工职业技术学院','常州机电职业技术学院','建东职业技术学院','苏州市职业大学','苏州工业职业技术学院','苏州农业职业技术学院','苏州卫生职业技术学院','苏州健雄职业技术学院','沙洲职业工学院','苏州工业园区服务外包职业学院',
            '南通科技职业学院','南通职业大学','南通开放大学','江苏商贸职业学院','南通师范高等专科学校','南通理工学院','南通工贸技师学院','连云港师范高等专科学校','连云港职业技术学院','江苏财经职业技术学院',
            '淮安信息职业技术学院','江苏护理职业学院','盐城工业职业技术学院','盐城卫生职业技术学院','明达职业技术学院','扬州市职业大学','江海职业技术学院','镇江市高等专科学校','江苏农林职业技术学院','金山职业技术学院',
            '泰州学院','江苏农牧科技职业学院','泰州职业技术学院','宿迁学院','泽达学院','南京大学金陵学院','东南大学成贤学院','南京理工大学紫金学院','南京理工大学泰州科技学院','中国矿业大学徐海学院',
            '南京师范大学中北学院','南京师范大学泰州学院','南京医科大学康达学院','南京中医药大学翰林学院','南京财经大学红山学院','南京信息工程大学滨江学院','南京邮电大学通达学院','南京林业大学南方学院','南京审计学院金审学院','江苏师范大学科文学院',
            '常州大学怀德学院','苏州大学文正学院','苏州大学应用技术学院','苏州科技学院天平学院','南通大学杏林学院','扬州大学广陵学院','江苏大学京江学院','江苏科技大学苏州理工学院','江苏团省委学校部',
        );
        $return = array();
        foreach ($schools as $k=>$v ) {
            $map['title'] = array('LIKE','%'.$v.'%');
            $return[$k]['sid'] = M('school')->where($map)->getField('id');
            $return[$k]['school'] = $v;
        }
        $arr = array('学校ID','学校名称');
        array_unshift($return, $arr);
        service('Excel')->export2($return);
    }


}