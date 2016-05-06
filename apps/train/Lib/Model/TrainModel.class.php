<?php

class TrainModel extends Model {

    public function doAddCourse($courseMap) {
        $courseMap['cTime'] = isset($courseMap['cTime']) ? $courseMap['cTime'] : time();
        $orgList = D('TrainOrg','train')->getAllOrg();
        $courseMap['eday'] = $orgList[$courseMap['orgId']]['eday'];
        $addId = $this->add($courseMap);
        return $addId;
    }

    public function doEditCourse($courseMap, $tid) {
        $courseMap['rTime'] = isset($courseMap['rTime']) ? $courseMap['rTime'] : time();
        $orgList = D('TrainOrg','train')->getAllOrg();
        $courseMap['eday'] = $orgList[$courseMap['orgId']]['eday'];
        $saveId = $this->where('id =' . $tid)->save($courseMap);
        return $saveId;
    }

    //后台课程列表
    public function courseList($map, $limit = 15) {
        $list = $this->where($map)->order('id DESC')->findPage($limit);
        $cats = D('TrainCat')->getAllCat();
        $daoArea = M('train_area');
        $orgs = D('TrainOrg')->getAllOrg();
        foreach ($list['data'] as $k => $v) {
            $list['data'][$k]['cat'] = $cats[$v['catId']]['name'];
            $list['data'][$k]['province'] = $daoArea->where('id=' . $v['provinceId'])->getField('title');
            $list['data'][$k]['city'] = $daoArea->where('id=' . $v['cityId'])->getField('title');
            $list['data'][$k]['org'] = $orgs[$v['orgId']]['name'];
        }
        return $list;
    }

    public function apiCourseList($map, $limit = 15, $page,$mid,$checkCollect=true) {
        $offset = ($page - 1) * $limit;
        $map['eday'] = array('egt',date('Y-m-d',time()));
        $list = $this->where($map)->field('id,title,cost,orgId,dauer')->order('display_order ASC')->limit("$offset,$limit")->select();
        $orgs = D('TrainOrg','train')->getAllOrg();
        if($checkCollect){
            $collect = M('train_collect')->where('uid ='.$mid)->findAll();
            $isCollect = getSubByKey($collect, 'courseId');
        }
        foreach ($list as $k => $v) {
            $list[$k]['title'] = htmlspecialchars_decode($v['title']);
            $list[$k]['org'] = $orgs[$v['orgId']]['name'];
            $list[$k]['orgLogo'] = tsMakeThumbUp($orgs[$v['orgId']]['logo'],60,60,'f');
            if($checkCollect){
                $list[$k]['collect'] = in_array($v['id'], $isCollect) ? 1 : 0;
            }else{
                $list[$k]['collect'] = 1;
            }
        }
        return $list;
    }

    public function apiCourse($id,$uid) {
        $map['id'] = $id;
        $map['eday'] = array('egt',date('Y-m-d',time()));
        $course = $this->where($map)->field('id,title,description,address,cost,contact,kDate,dauer,click,orgId')->find();
        if(!$course){
            return array();
        }
        $course['title'] = htmlspecialchars_decode($course['title']);
        $orgs = D('TrainOrg','train')->getAllOrg();
        $course['org'] = $orgs[$course['orgId']]['name'];
        $course['orgDesc'] = $orgs[$course['orgId']]['description'];
        $collect = M('train_collect')->getField('uid','courseId='.$course['id'].' AND uid ='.$uid);
        $course['collect'] = $collect ? 1 : 0;
        //增加点击量
        $this->where('id ='.$id)->setInc('click');
        return $course;
    }

    public function course($id,$uid) {
        $map['id'] = $id;
        $map['eday'] = array('egt',date('Y-m-d',time()));
        $course = $this->where($map)->find();
        if(!$course){
            return array();
        }
        $orgs = D('TrainOrg','train')->getAllOrg();
        $course['org'] = $orgs[$course['orgId']];
        $collect = M('train_collect')->getField('uid','courseId='.$course['id'].' AND uid ='.$uid);
        $course['collect'] = $collect ? 1 : 0;
        //增加点击量
        $this->where('id ='.$id)->setInc('click');
        return $course;
    }

}

?>
