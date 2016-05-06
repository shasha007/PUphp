<?php

/**
 * SchoolAction
 * 校方活动
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class SjsqAction extends Action {

    private $sid;
    private $year;
    private $maxSize = 100; //MB

    /**
     * __initialize
     * 初始化
     * @access public
     * @return void
     */
    public function _initialize() {
        $this->assign('isAdmin', '1');
        $this->assign('maxSize', $this->maxSize);
        if (!$this->mid) {
            $this->assign('jumpUrl', U('home/Public/login'));
            $this->error('请先登录！');
        }
        $this->year = C('SJ_YEAR');
        $this->sid = $this->user['sid'];
        //申请开始结束时间
        $dao = D('Sj');
        
        //临时开报名参加按钮
        $domain = parse_url($_SERVER['HTTP_HOST']);
        $domain = substr($domain['path'], 0, strpos($domain['path'], '.'));
        if(!$dao->canSjJoin($this->sid) && $domain!='yctswadmin'){
            $this->error($dao->getError());
        }
    }

    public function index() {
        $this->display();
    }
    public function t1() {
        $this->assign(getPhotoConfig());
        $this->display();
    }
    public function t5() {
        $this->assign(getPhotoConfig());
        $this->display();
    }
    public function t6() {
        $this->assign(getPhotoConfig());
        $this->display();
    }
    public function t7() {
        $this->assign(getPhotoConfig());
        $this->display();
    }
    public function t2() {
        $this->assign(getPhotoConfig());
        $school = model('Schools')->makeLevel0Tree($this->sid);
        $this->assign('sid1s', $school);
        $this->display();
    }
    public function t3() {
        $this->assign(getPhotoConfig());
        $school = model('Schools')->makeLevel0Tree($this->sid);
        $this->assign('sid1s', $school);
        $this->display();
    }
    public function t4() {
        $this->assign(getPhotoConfig());
        $school = model('Schools')->makeLevel0Tree($this->sid);
        $this->assign('sid1s', $school);
        $this->display();
    }

    public function doAddT1(){
        $title = getShort(t($_POST['title']), 100);
        if (empty($title))
            $this->error('单位名称不能为空');

        $this->__checkContent($_POST['content'], 1, 600);
        if(!$_POST['imgs']){
            $this->error('请上传图片');
        }
        $upload['max_size'] = $this->maxSize * 1024 * 1024;;
        $upload['allow_exts'] = 'doc,docx';
        $info = X('Xattach')->upload('sj1', $upload);
        if ($info['status']) {  //上传成功
            $data['attach'] = $info['info'][0]['id'];
        } else {
            $this->error($info['info']);
            $this->error('请上传word格式附件');
        }
        $data['status'] = 1;
        $data['uid'] = $this->mid;
        $data['sid'] = $this->sid;
        $data['title'] = $title;
        $data['content'] = t(h($_POST['content']));
        $data['type'] = 1;
        $data['cTime'] = time();
        $data['year'] = $this->year;
        $id = M('sj')->add($data);
        if ($id) {
            //储存图片
            $daoImg = M('sj_img');
            $imgData['sjid'] = $id;
            foreach($_POST['imgs'] as $k=>$v){
                $imgData['status'] = 0;
                if($k==0){
                    $imgData['status'] = 1;
                }
                $imgData['attachId'] = $v;
                $daoImg->add($imgData);
            }
            if($_POST['flash']){
                //储存flash
                $daoFlash = M('sj_flash');
                $flashData['sjid'] = $id;
                foreach($_POST['flash'] as $k=>$v){
                    $flashData['flashId'] = $v;
                    $daoFlash->add($flashData);
                }
            }
            $this->next(U('event/Sjsq/t1'));
        } else {
            $this->error('提交失败');
        }
    }

    public function doAddT2(){
        $title = getShort(t($_POST['title']), 100);
        if (empty($title))
            $this->error('团队名称不能为空');
        $title2 = getShort(t($_POST['title2']), 100);
        if (empty($title2))
            $this->error('实践项目名称不能为空');
        $sid1 = t($_POST['sid1']);
        if(!$sid1){
            $this->error('院系不能为空');
        }
        $zusatz = t($_POST['zusatz']);
        if (empty($zusatz))
            $this->error('团队成员不能为空');
        $this->__checkContent($_POST['content'], 1, 1150,'总结材料');
        $isTop = intval($_POST['isTop']);
        if($isTop){
            $description = t($_POST['description']);
            if (empty($description))
                $this->error('团队简介不能为空');
            $data['description'] = $description;
        }
        if(!$_POST['imgs']){
            $this->error('请上传图片');
        }
        $data['status'] = 1;
        $data['uid'] = $this->mid;
        $data['sid'] = $this->sid;
        $data['sid1'] = $sid1;
        $data['title'] = $title;
        $data['title2'] = $title2;
        $data['zusatz'] = $zusatz;
        $data['content'] = t(h($_POST['content']));
        $data['type'] = 2;
        if($isTop){
            $data['type'] = 3;
        }
        $data['cTime'] = time();
        $data['year'] = $this->year;
        $id = M('sj')->add($data);
        if ($id) {
            //储存图片
            $daoImg = M('sj_img');
            $imgData['sjid'] = $id;
            foreach($_POST['imgs'] as $k=>$v){
                $imgData['status'] = 0;
                if($k==0){
                    $imgData['status'] = 1;
                }
                $imgData['attachId'] = $v;
                $daoImg->add($imgData);
            }
            if($_POST['flash']){
                //储存flash
                $daoFlash = M('sj_flash');
                $flashData['sjid'] = $id;
                foreach($_POST['flash'] as $k=>$v){
                    $flashData['flashId'] = $v;
                    $daoFlash->add($flashData);
                }
            }
            $this->next(U('event/Sjsq/t2'));
        } else {
            $this->error('提交失败');
        }
    }
    public function doAddT3(){
        $title = getShort(t($_POST['title']), 100);
        if (empty($title))
            $this->error('姓名不能为空');
        $title2 = getShort(t($_POST['title2']), 100);
        if (empty($title2))
            $this->error('学号不能为空');
        $sid1 = t($_POST['sid1']);
        if(!$sid1){
            $this->error('院系不能为空');
        }
        $zusatz = t($_POST['zusatz']);
        if (empty($zusatz))
            $this->error('所在团队不能为空');
        $this->__checkContent($_POST['content'], 1, 1150,'总结材料');
        $isTop = intval($_POST['isTop']);
        if($isTop){
            $description = t($_POST['description']);
            if (empty($description))
                $this->error('事迹简介不能为空');
            $data['description'] = $description;
        }
        if(!$_POST['imgs']){
            $this->error('请上传图片');
        }
        $data['status'] = 1;
        $data['uid'] = $this->mid;
        $data['sid'] = $this->sid;
        $data['sid1'] = $sid1;
        $data['title'] = $title;
        $data['title2'] = $title2;
        $data['zusatz'] = $zusatz;
        $data['content'] = t(h($_POST['content']));
        $data['type'] = 4;
        if($isTop){
            $data['type'] = 5;
        }
        $data['cTime'] = time();
        $data['year'] = $this->year;
        $id = M('sj')->add($data);
        if ($id) {
            //储存图片
            $daoImg = M('sj_img');
            $imgData['sjid'] = $id;
            foreach($_POST['imgs'] as $k=>$v){
                $imgData['status'] = 0;
                if($k==0){
                    $imgData['status'] = 1;
                }
                $imgData['attachId'] = $v;
                $daoImg->add($imgData);
            }
            if($_POST['flash']){
                //储存flash
                $daoFlash = M('sj_flash');
                $flashData['sjid'] = $id;
                foreach($_POST['flash'] as $k=>$v){
                    $flashData['flashId'] = $v;
                    $daoFlash->add($flashData);
                }
            }
            $this->next(U('event/Sjsq/t3'));
        } else {
            $this->error('提交失败');
        }
    }
    public function doAddT4(){
        $title = getShort(t($_POST['title']), 100);
        if (empty($title))
            $this->error('姓名不能为空');
        $title2 = getShort(t($_POST['title2']), 100);
        if (empty($title2))
            $this->error('工号不能为空');
        $sid1 = t($_POST['sid1']);
        if(!$sid1){
            $this->error('院系不能为空');
        }
        $zusatz = t($_POST['zusatz']);
        if (empty($zusatz))
            $this->error('指导团队不能为空');
        $this->__checkContent($_POST['content'], 1, 1150,'总结材料');
        if(!$_POST['imgs']){
            $this->error('请上传图片');
        }
        $data['status'] = 1;
        $data['uid'] = $this->mid;
        $data['sid'] = $this->sid;
        $data['sid1'] = $sid1;
        $data['title'] = $title;
        $data['title2'] = $title2;
        $data['zusatz'] = $zusatz;
        $data['content'] = t(h($_POST['content']));
        $data['type'] = 6;
        $data['cTime'] = time();
        $data['year'] = $this->year;
        $id = M('sj')->add($data);
        if ($id) {
            //储存图片
            $daoImg = M('sj_img');
            $imgData['sjid'] = $id;
            foreach($_POST['imgs'] as $k=>$v){
                $imgData['status'] = 0;
                if($k==0){
                    $imgData['status'] = 1;
                }
                $imgData['attachId'] = $v;
                $daoImg->add($imgData);
            }
            if($_POST['flash']){
                //储存flash
                $daoFlash = M('sj_flash');
                $flashData['sjid'] = $id;
                foreach($_POST['flash'] as $k=>$v){
                    $flashData['flashId'] = $v;
                    $daoFlash->add($flashData);
                }
            }
            $this->next(U('event/Sjsq/t4'));
        } else {
            $this->error('提交失败');
        }
    }
    public function doAddT5(){
        $title = getShort(t($_POST['title']), 100);
        if (empty($title))
            $this->error('基地名称不能为空');
        $this->__checkContent($_POST['content'], 1, 1150,'总结材料');
        $config = getPhotoConfig();
        $upload['max_size'] = $this->maxSize * 1024 * 1024;;
        $upload['allow_exts'] = $config['photo_file_ext'];
        $info = X('Xattach')->upload('sj1', $upload);
        if ($info['status']) {  //上传成功
            $data['attach'] = $info['info'][0]['id'];
        } else {
            $this->error($info['info']);
        }
        if(!$_POST['imgs']){
            $this->error('请上传图片');
        }
        $data['status'] = 1;
        $data['uid'] = $this->mid;
        $data['sid'] = $this->sid;
        $data['title'] = $title;
        $data['content'] = t(h($_POST['content']));
        $data['type'] = 7;
        $data['cTime'] = time();
        $data['year'] = $this->year;
        $id = M('sj')->add($data);
        if ($id) {
            //储存图片
            $daoImg = M('sj_img');
            $imgData['sjid'] = $id;
            foreach($_POST['imgs'] as $k=>$v){
                $imgData['status'] = 0;
                if($k==0){
                    $imgData['status'] = 1;
                }
                $imgData['attachId'] = $v;
                $daoImg->add($imgData);
            }
            if($_POST['flash']){
                //储存flash
                $daoFlash = M('sj_flash');
                $flashData['sjid'] = $id;
                foreach($_POST['flash'] as $k=>$v){
                    $flashData['flashId'] = $v;
                    $daoFlash->add($flashData);
                }
            }
            $this->next(U('event/Sjsq/t5'));
        } else {
            $this->error('提交失败');
        }
    }

    public function doAddT6(){
        $title = getShort(t($_POST['title']), 100);
        if (empty($title))
            $this->error('报告题目不能为空');
        $title2 = getShort(t($_POST['title2']), 100);
        if (empty($title2))
            $this->error('报告作者不能为空');
        $upload['max_size'] = $this->maxSize * 1024 * 1024;;
        $upload['allow_exts'] = 'doc,docx';
        $info = X('Xattach')->upload('sj1', $upload);
        if ($info['status']) {  //上传成功
            $data['attach'] = $info['info'][0]['id'];
        } else {
            $this->error($info['info']);
            $this->error('请上传word格式附件');
        }
        $data['status'] = 1;
        $data['uid'] = $this->mid;
        $data['sid'] = $this->sid;
        $data['title'] = $title;
        $data['title2'] = $title2;
        $data['type'] = 8;
        $data['cTime'] = time();
        $data['year'] = $this->year;
        $id = M('sj')->add($data);
        if ($id) {
            $this->next(U('event/Sjsq/t6'));
        } else {
            $this->error('提交失败');
        }
    }
    public function doAddT7(){
        $title = getShort(t($_POST['title']), 100);
        if (empty($title))
            $this->error('故事名称不能为空');
        $title2 = getShort(t($_POST['title2']), 100);
        if (empty($title2))
            $this->error('申报个人或团队名称不能为空');
        $this->__checkContent($_POST['content'], 1, 1150,'故事内容');
        if(!$_POST['imgs']){
            $this->error('请上传图片');
        }
        $data['status'] = 1;
        $data['uid'] = $this->mid;
        $data['sid'] = $this->sid;
        $data['title'] = $title;
        $data['title2'] = $title2;
        $data['content'] = t(h($_POST['content']));
        $data['type'] = 9;
        $data['cTime'] = time();
        $data['year'] = $this->year;
        $id = M('sj')->add($data);
        if ($id) {
            //储存图片
            $daoImg = M('sj_img');
            $imgData['sjid'] = $id;
            foreach($_POST['imgs'] as $k=>$v){
                $imgData['status'] = 0;
                if($k==0){
                    $imgData['status'] = 1;
                }
                $imgData['attachId'] = $v;
                $daoImg->add($imgData);
            }
            if($_POST['flash']){
                //储存flash
                $daoFlash = M('sj_flash');
                $flashData['sjid'] = $id;
                foreach($_POST['flash'] as $k=>$v){
                    $flashData['flashId'] = $v;
                    $daoFlash->add($flashData);
                }
            }
            $this->next(U('event/Sjsq/t7'));
        } else {
            $this->error('提交失败');
        }
    }
    public function uploadImg(){
        $this->assign(getPhotoConfig());
        $this->display();
    }
    //执行单张图片上传
    public function upload_single_pic(){
        $info = imgUploadDb($this->mid);
        if ($info['status']) {
            $res['status'] = true;
            $src = getGroupThumb($info['info'][0]['savepath'].$info['info'][0]['savename'],80,80,'f');
            $res['src'] = $src;
            $res['id'] = $info['info'][0]['id'];
            echo json_encode($res);
        }else{
            echo "0";
        }
    }

    public function doAddFlash() {
        $daoFlash = model('Flash');
        $daoFlash->addFlash(t($_POST['link']),$this->mid);
        $res = $daoFlash->getInfo();
        echo json_encode($res);
    }

    public function next($current){
        $this->assign('current', $current);
        $this->display('next');
    }


    private function __checkContent($content, $mix = 5, $max = 5000, $txt='内容') {
        $content_length = get_str_length($content, true);
        if (0 == $content_length) {
            $this->error($txt.'不能为空');
        } else if ($content_length < $mix) {
            $this->error($txt.'不能少于' . $mix . '个字');
        } else if ($content_length > $max) {
            $this->error($txt.'不能超过' . $max . '个字');
        }
    }
}
