<?php
//前台活动管理
class LessonActiveAction extends CoursebaseAction {

    private $activeId;
    private $obj;
    private $active;

    public function _initialize() {
        parent::_initialize();
        $this->active = D('CourseActive');
        if (!$this->smid) {
            $this->error('您不是该校用户');
        }
        $id = intval($_REQUEST['id']);
        if ($id <= 0) {
            $this->error("错误的访问页面，请检查链接");
        }
        $this->active->setMid($this->mid);
        $map = array();
        $map['isDel'] = 0;
        $map['id'] = $id;
        $result = $this->active->where($map)->find();
        if ($result) {
            if($result['sid'] != $this->sid){
                $this->error('您没有权限管理该活动');
            }
            if ($result['uid'] != $this->mid && !$this->rights['canBackend']) {
                $this->error('您没有权限管理该活动');
            }
            $this->obj = $result;
            $this->assign($result);
        } else {
            $this->assign('jumpUrl', U('/Lesson/boardActive'));
            $this->error('课程活动不存在或，未通过审核或，已删除');
        }
        $this->assign('courseId', $id);
        $this->setTitle($result['title']);
        $this->activeId = $id;
    }

    public function index() {
        $this->obj['verifyCount'] = M('course_active_user')->where("status = 0 AND courseId ={$this->obj['id']}")->count();
        $this->display();
    }

    public function editCourse() {
        $type_list = D('CourseType')->getType();
        $this->assign('type', $type_list);
        $this->assign('istop', $this->active->getTop($this->sid));
        $this->display();
    }

    public function doEditCourse() {
        $id = intval($_REQUEST['id']);
        if (!$obj = $this->active->where(array('id' => $id))->find()) {
            $this->error('课程活动不存在或已删除');
        }
        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 20) {
            $this->error('课程活动名称最大20个字！');
        }
        $textarea = t($_POST['description']);
        if (mb_strlen($textarea, 'UTF8') <= 0 || mb_strlen($textarea, 'UTF8') > 200) {
            $this->error("课程活动简介1到200字!");
        }

        $map['deadline'] = _paramDate($_POST['deadline']);
        $map['sTime'] = _paramDate($_POST['sTime']);
        $map['eTime'] = _paramDate($_POST['eTime']);
        if ($map['sTime'] > $map['eTime']) {
            $this->error("结束时间不得早于开始时间");
        }
        if ($map['deadline'] > $map['eTime']) {
            $this->error('报名截止时间不能晚于结束时间');
        }

        //得到上传的图片
        $config = getPhotoConfig();
        $options['userId'] = $this->mid;
        $options['max_size'] = $config['photo_max_size'];
        $options['allow_exts'] = $config['photo_file_ext'];
        $cover = X('Xattach')->upload('courseActive', $options);
        if (!$cover['status'] && $cover['info'] != '没有选择上传文件') {
            $this->error($cover['info']);
        }
        $map['credit'] = intval($_POST['credit']);
        $map['title'] = $title;
        $map['address'] = t($_POST['address']);
        $map['limitCount'] = intval(t($_POST['count']));
        $map['typeId'] = intval($_POST['typeId']);
        $map['contact'] = t($_POST['contact']);
        $map['cost'] = intval($_POST['cost']);
        $map['costExplain'] = keyWordFilter(t($_POST['costExplain']));
        $map['allow'] = isset($_POST['allow']) ? 1 : 0;
        $map['need_tel'] = isset($_POST['need_tel']) ? 1 : 0;
        $map['description'] = $textarea;
        if ($this->active->doEditCourseActive($map, $cover, $obj)) {
            $this->assign('jumpUrl', U('/LessonActive/index', array('id' => $this->obj['id'], 'uid' => $this->mid)));
            $this->success($this->appName . '修改成功！');
        } else {
            $this->error($this->appName . '修改失败');
        }
    }

    public function courseDel() {
        if ($this->obj['status']==2) {
            $this->error('活动已发布，无法删除。请联系管理员');
        }
        $res = $this->active->where("id=" . $this->activeId)->setField('isDel', 1);
        if ($res) {
            $this->assign('jumpUrl', U('event/Lesson/boardActive', array('cat' => 'launch')));
            $this->success('删除成功');
        }
        $this->error('删除失败');
    }

    public function member() {
        if (!empty($_POST)) {
            $_SESSION['backend_course_active_user_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['backend_course_active_user_search']);
        } else {
            unset($_SESSION['backend_course_active_user_search']);
        }
        $_POST['realname'] = t(trim($_POST['realname']));
        $_POST['realname'] && $map['realname'] = array('like', "%" . $_POST['realname'] . "%");
        $this->assign($_POST);
        $map['status'] = 1;
        $map['courseId'] = $this->activeId;
        $result = M('course_active_user')->where($map)->findPage(10);
        $this->assign($result);
        $this->setTitle('成员列表');
        $this->display();
    }

    public function doDeleteMember() {
        $this->canEditCourse();
        $ids = explode(',', t($_POST ['mid']));
        $data['id'] = array('in', $ids);
        $data['courseId'] = $this->activeId;
        if ($this->active->doDelActiveUser($data)) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    private function canEditCourse() {
        if (!service('SystemPopedom')->hasPopedom($this->mid, 'admin/Index/index', false)
                && $this->obj['uid'] == $this->mid && $this->obj['eTime'] <= time() ) {
            $this->error('该活动已结束，无法更改');
        }
    }

    public function editCredit() {
        if ($this->obj['status'] != 2 || $this->obj['eTime'] > time()) {
            $this->error('非法操作,不存在或尚未结束');
        }
        $num = intval($_POST['val']);
        $userId = intval($_POST['sid']);
        if ($num > $this->obj['credit'] || $num < 0) {
            $this->error('发放课时不得大于课程活动课时');
        } else {
            $daoUser = M('course_active_user');
            $res = $daoUser->where("id=" . $userId)->field('credit,uid')->find();
            $credit = $num - $res['credit'];
            M('user')->setInc('course_credit', "uid=" . $res['uid'], $credit);
            $res = $daoUser->where("id=" . $userId)->setField('credit', $num);
            if ($res) {
                $this->success('编辑成功');
            } else {
                $this->error('编辑失败');
            }
        }
    }

    public function memberAudit() {
        $map['status'] = 0;
        $map['courseId'] = $this->activeId;
        //取得成员列表
        $result = M('course_active_user')->where($map)->order('id DESC')->findPage(10);
        $this->assign($result);
        $this->setTitle('成员审核');
        $this->display();
    }

    public function doAuditMember() {
        $this->canEditCourse();
        if ($this->obj['limitCount'] < 1) {
            $this->error('参加活动人数已满，不能再参加');
        }
        $ids = explode(',', t($_POST ['mid']));
        $num = count($ids);
        $data['id'] = array('in', $ids);
        $data['courseId'] = $this->activeId;
        $data['status'] = 0;
        $user = M('course_active_user')->where($data)->field('uid')->find();
        if (!$user) {
            $this->error('成员不存在');
        }
        $data['uid'] = $user['uid'];
        if ($this->active->doArgeeActiveUser($data,$num)) {
            $this->success('操作成功');
        }
        $this->error('操作失败');
    }

    public function photo() {
        if (!empty($_POST)) {
            $_SESSION['backend_course_photo_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['backend_course_photo_search']);
        } else {
            unset($_SESSION['backend_course_photo_search']);
        }
        $_POST['photoTitle'] = t(trim($_POST['photoTitle']));
        $_POST['photoTitle'] && $map['title'] = array('like', "%" . $_POST['photoTitle'] . "%");
        $this->assign($_POST);
        $map['courseId'] = $this->activeId;
        $map['uid'] = 0;
        $list = D('CourseImg')->where($map)->order('`id` DESC')->findPage(10);
        $this->assign($list);
        $this->display();
    }

    public function editPhoto() {
        $this->assign('type', 'add');
        if ($id = (int) $_REQUEST['gid']) {
            $this->assign('type', 'edit');
            $photo = D('CourseImg')->find($id);
            if (!$photo) {
                echo("无法找到对象!");
                return;
            }
            $this->assign("photo", $photo);
        }
        $config = getPhotoConfig();
        $this->assign($config);
        $this->display();
    }

    public function doEditPhoto() {
        $title = t($_POST['title']);
        if (mb_strlen($title, 'UTF8') > 60) {
            $this->error('标题最大60个字！');
        }
        $id = intval($_REQUEST['gid']);
        if (empty($id)) {
            $this->insertPhoto();
        } else {
            $this->updatePhoto($id);
        }
    }

    public function updatePhoto($id) {
        $map['id'] = $id;
        $map['courseId'] = $this->activeId;
        $dao = D('CourseImg');
        $img = $dao->where($map)->find();
        if (!$img) {
            $this->error('非法参数，图片不存在！');
        }
        $data = $this->_getPhotoData(false);
        if ($dao->where("id={$id}")->save($data)) {
            if (isset($data['uid'])) {
                $this->assign('jumpUrl', U('/LessonActive/playerImg', array('id' => $this->activeId, 'uid' => $data['uid'])));
            } else {
                $this->assign('jumpUrl', U('/LessonActive/photo', array('id' => $this->activeId)));
            }
            $this->success('修改成功！');
        } else {
            //失败提示
            $this->error('修改失败！');
        }
    }

    /**
     * 活动添加修改成员的信息
     */
    private function _getPhotoData($insert = true) {
        $info = $this->courseUpload();
        if ($info['status']) {
            $data['path'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
        } elseif ($insert) {
            $this->error("上传出错! " . $info['info']);
        } elseif ($info['info'] != '没有选择上传文件') {
            $this->error($info['info']);
        }
        $data['courseId'] = $this->activeId;
        if (isset($_POST['uid'])) {
            $data['uid'] = intval($_POST['uid']);
        }
        $data['title'] = t($_POST['title']);
        $data['cTime'] = time();
        return $data;
    }

    public function insertPhoto() {
        $data = $this->_getPhotoData();
        if ($res = D('CourseImg')->add($data)) {
            if (isset($data['uid'])) {
                $this->assign('jumpUrl', U('/LessonActive/playerImg', array('id' => $this->activeId, 'uid' => $data['uid'])));
            } else {
                $this->assign('jumpUrl', U('/LessonActive/photo', array('id' => $this->activeId)));
            }
            $this->success('添加成功');
        } else {
            $this->error('添加失败');
        }
    }

    private function courseUpload() {
        //上传参数
        $options['max_size'] = getPhotoConfig('photo_max_size');
        $options['allow_exts'] = getPhotoConfig('photo_file_ext');
        $options['save_to_db'] = false;
        //执行上传操作
        $info = X('Xattach')->upload('course', $options);
        return $info;
    }

    public function multiPhoto() {
        $config = getPhotoConfig();
        $this->assign($config);
        $this->display();
    }

    public function deletePhoto() {
        $ids = explode(',', t($_POST ['nid']));
        $map['id'] = array('in', $ids);
        if ($result = D('CourseImg')->doDelete($map)) {
            $this->success('删除成功！');
        } else {
            $this->error('删除失败！');
        }
    }

    //重新申请
    public function renew() {
        if ($this->obj['status'] != 0) {
            $this->error('非法操作');
        }
        $id = intval($_REQUEST['id']);
        $res = $this->active->where('id =' . $id)->setField('status', 1);
        if ($res) {
            //发短信给初审人
//            $event = M('event')->where("id=$this->eventId")->field('audit_uid,title')->find();
//            $mobile = M('user')->where("sid=" . $this->school['id'] . " AND uid=" . $event['audit_uid'])->field("mobile")->find();
//            if ($mobile['mobile']) {
//                $isSend = M('user_privacy')->where("`key`='active' AND uid=" . $event['audit_uid'])->field('value')->find();
//                if ($isSend['value'] != 1) {
//                    require_once(SITE_PATH . '/addons/libs/BayouSmsSender.php');
//                    $sender = new BayouSmsSender();
//                    $msg = '亲爱的PocketUni用户,活动"' . $event['title'] . '"重新提交申请,等待您的初级审核';
//                    $mobile = $mobile['mobile'];
//                    $pass = md5("113314446");
//                }
//            }
            $this->assign('jumpUrl', U('/Lesson/boardActive'));
            $this->success('重新申请成功');
        }
        $this->error('重新申请失败');
    }

////提前结束
//    public function beforeEnd() {
//        if ($this->obj['status'] != 2&&$this->obj['eTime']<time()) {
//            $this->error('非法操作');
//        }
//        $id = intval($_REQUEST['id']);
//        $res = $this->active->where('id =' . $id)->setField('eTime', time());
//        if ($res) {
//            $this->success('提前结束成功');
//        }
//        $this->error('提前结束失败');
//    }

    //上传后执行编辑操作
    public function mutiEditPhotos() {
        $upnum = intval($_REQUEST['upnum']);
        if ($upnum == 0)
            $this->error('请至少上传一张图片！');
        $jumpUrl = U('/LessonActive/photo', array('id' => $this->activeId));
        $this->assign('jumpUrl', $jumpUrl);
        $this->success('添加成功');
    }

    //执行单张图片上传
    public function upload_single_pic() {
        $info = eventUpload();
        if ($info['status']) {
            //保存图片信息
            $data['path'] = $info['info'][0]['savepath'].$info['info'][0]['savename'];
            $data['courseId'] = $this->activeId;
            if (isset($_REQUEST['uid'])) {
                $data['uid'] = intval($_REQUEST['uid']);
            }
            $data['title'] = t($_REQUEST['title']);
            $data['cTime'] = time();
            if ($res = D('CourseImg')->add($data)) {
                $this->success('添加成功');
            } else {
                $this->error('添加失败');
            }
            //启用session记录flash上传的图片数，也可以防止意外提交。
            //$upload_count	=	intval($_SESSION['upload_count']);
            //$_SESSION['upload_count']	=	$upload_count + 1;
        } else {
            //上传出错
            echo "0";
        }
    }

    public function xls() {
        require_once(SITE_PATH . '/addons/libs/PHPExcel.php');

        $objPHPExcel = new PHPExcel();
// Set document properties
        $objPHPExcel->getProperties()->setCreator("Maarten Balliauw")
                ->setLastModifiedBy("Maarten Balliauw")
                ->setTitle("Office 2007 XLSX Test Document")
                ->setSubject("Office 2007 XLSX Test Document")
                ->setDescription("Test document for Office 2007 XLSX, generated using PHP classes.")
                ->setKeywords("office 2007 openxml php")
                ->setCategory("Test result file");
        $title = $this->obj['title'];
        $map['status'] = 1;
        $map['courseId'] = $this->activeId;
        $list = M('course_active_user')
                ->where($map)
                ->findAll();
// Add some datas
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A3', '序号')
                ->setCellValue('B3', '姓   名')
                ->setCellValue('C3', '性别')
                ->setCellValue('D3', '院  系')
                ->setCellValue('E3', '学  号')
                ->setCellValue('F3', '学  分')
                ->setCellValue('G3', '电  话');


        foreach ($list as $k => $v) {
            $row = (string) ($k + 4);
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $row, $k + 1)
                    ->setCellValue('B' . $row, $v['realname'])
                    ->setCellValue('C' . $row, $v['sex'] ? 男 : 女)
                    ->setCellValue('D' . $row, tsGetSchoolByUid($v['uid']))
                    ->setCellValueExplicit('E' . $row, $v['studentId'],PHPExcel_Cell_DataType::TYPE_STRING)
                    ->setCellValue('F' . $row, $v['credit'])
                    ->setCellValue('G' . $row, $v['tel']);
        }
        $objPHPExcel->getActiveSheet()->mergeCells('A1:G2');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $title.'参加人员');
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true)->setSize(20); ;
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
//        $objPHPExcel->getActiveSheet()->getStyle('A1:D2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
//        $objPHPExcel->getActiveSheet()->getStyle('A1:D2')->getBorders()->getLeft()->getColor()->setARGB('FF993300');
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(25);
        $len = (string) count($list);
        $style = 'D4:D' . ($len+3);
        $objPHPExcel->getActiveSheet()->getStyle($style)->getAlignment()->setWrapText(true);

// Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle($title);


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="' . $title . '.xls"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');

// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }

}

