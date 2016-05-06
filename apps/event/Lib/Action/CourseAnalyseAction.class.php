<?php

/**
 * IndexAction
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class CourseAnalyseAction extends CourseCanAction {

    public function _initialize() {
        parent::_initialize();
    }

    public function index() {
        if (!empty($_POST)) {
            $_SESSION['admin_searchCourse'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_searchCourse']);
        } else {
            unset($_SESSION['admin_searchCourse']);
        }
        $this->assign('isSearch', isset($_POST['isSearch']) ? '1' : '0');
        $_POST['stuId'] && $map['a.studentId'] = t($_POST['stuId']);
        if(is_numeric($_POST['start'])&&is_numeric($_POST['end'])){
            $have = 'num BETWEEN '.$_POST['start'].' AND '.$_POST['end'];
        }
        if($_GET['orderKey'] && $_GET['orderType']){
            $_GET['orderKey'] = t($_GET['orderKey']);
            $_GET['orderType'] = t($_GET['orderType']);
            $order = $_GET['orderKey'].' '.$_GET['orderType'];
            $this->assign('orderKey', $_GET['orderKey']);
            $this->assign('orderType', $_GET['orderType']);
        }
        $map['b.sid'] = $this->sid;
        $db_prefix = C('DB_PREFIX');
       $result = M('course_user')->table("{$db_prefix}course_user AS a ")
                ->join("{$db_prefix}course AS b ON a.courseId=b.id")
                ->where($map)
                ->field('count(a.uid) as num')
                ->group('a.uid')
                ->having($have)
                ->findAll();
                $count	= count($result);
        $list = M('course_user')->table("{$db_prefix}course_user AS a ")
                ->join("{$db_prefix}course AS b ON a.courseId=b.id")
                ->where($map)
                ->field('a.realname,a.studentId,a.uid,group_concat(b.title) as title,count(a.uid) as num,a.uid A')
                ->order($order)
                ->group('a.uid')
                ->having($have)
                ->findPage(10, $count);
        $this->assign($list);
        $this->assign($_POST);
        $this->display();
    }

    public function course() {
        if (!empty($_POST)) {
            $_SESSION['admin_event_course_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_event_course_search']);
        } else {
            unset($_SESSION['admin_event_course_search']);
        }

        $daocourse = M('course');
        $_POST['title'] && $map['title'] = array('like', '%' . t($_POST['title']) . '%');
        $map['isDel'] = 0;
        $map['status'] = 2;
        $map['sid'] = $this->sid;
        $list = $daocourse->where($map)->field('id,title,joinCount,cTime')->findPage(10);
        $this->assign($list);
        $this->assign('isSearch', isset($_POST['isSearch']) ? '1' : '0');
        $this->assign($_POST);
        $this->display();
    }

    public function lone() {
        if (!empty($_POST)) {
            $_SESSION['admin_event_lone_search'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_event_lone_search']);
        } else {
            unset($_SESSION['admin_event_lone_search']);
        }
        $id = intval($_REQUEST['id']);
        $title = t($_REQUEST['title']);
        $map['courseId'] = $id;
        $map['sid'] = $this->sid;
        $list = M('course_user')->where($map)->field('realname,studentId,sid,uid')->findPage(10);
        $this->assign($list);
        $this->assign('title', $title);
        $this->assign('id', $id);
        $this->display();
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

        $db_prefix = C('DB_PREFIX');
        $list = M('course_user')->table("{$db_prefix}course_user AS a ")
                ->join("{$db_prefix}course AS b ON a.courseId=b.id")
                ->field('a.realname,a.studentId,a.uid,group_concat(b.title) as title,count(a.uid) as num')
                ->group('a.uid')
                ->limit(4999)
                ->findAll();
// Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '序号')
                ->setCellValue('B1', '姓 名')
                ->setCellValue('C1', '学 号')
                ->setCellValue('D1', '所在院')
                ->setCellValue('E1', '参加课程数')
                ->setCellValue('F1', '详细课程列表');

        foreach ($list as $k => $v) {
            $row = (string) ($k + 2);
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $row, $k + 1)
                    ->setCellValue('B' . $row, $v['realname'])
                    ->setCellValueExplicit('C' . $row, $v['studentId'],PHPExcel_Cell_DataType::TYPE_STRING)
                    ->setCellValue('D' . $row, tsGetSchoolByUid($v['uid']))
                    ->setCellValue('E' . $row, $v['num'])
                    ->setCellValue('F' . $row, $v['title']);
        }
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(45);
        $len = (string) count($list);
        $style = 'D2:D' . ($len+1);
        $objPHPExcel->getActiveSheet()->getStyle($style)->getAlignment()->setWrapText(true);
        $style = 'F2:F' . ($len+1);
        $objPHPExcel->getActiveSheet()->getStyle($style)->getAlignment()->setWrapText(true);
// Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('学生报名课程统计分析');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="学生报名课程统计分析.xls"');
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

    public function coursexls() {
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

        $daocourse = M('course');
        $map['isDel'] = 0;
        $map['status'] = 2;
        $list = $daocourse
                ->where($map)
                ->field('id,title,joinCount,cTime')
                ->findAll();
// Add some data
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A1', '序号')
                ->setCellValue('B1', '课程名称')
                ->setCellValue('C1', '发布时间')
                ->setCellValue('D1', '报名人数');


        foreach ($list as $k => $v) {
            $row = (string) ($k + 2);
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $row, $k + 1)
                    ->setCellValue('B' . $row, $v['title'])
                    ->setCellValue('C' . $row, date("Y-m-d", $v['cTime']))
                    ->setCellValue('D' . $row, $v['joinCount']);
        }

        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(45);
        $len = (string) count($list);
        $style = 'B2:B' . ($len+1);
        $objPHPExcel->getActiveSheet()->getStyle($style)->getAlignment()->setWrapText(true);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);

// Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle('所有课程学生报名统计分析');


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="所有课程学生报名统计分析.xls"');
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

    public function lonexls() {
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

        $id = intval($_REQUEST['id']);
        $title = t($_REQUEST['title']);
        $list = M('course_user')
                ->where('courseId=' . $id)
                ->field('realname,studentId,uid')
                ->findAll();
// Add some datas
        $objPHPExcel->setActiveSheetIndex(0)
                ->setCellValue('A3', '序号')
                ->setCellValue('B3', '姓名')
                ->setCellValue('C3', '学号')
                ->setCellValue('D3', '学院');


        foreach ($list as $k => $v) {
            $row = (string) ($k + 4);
            $objPHPExcel->setActiveSheetIndex(0)
                    ->setCellValue('A' . $row, $k + 1)
                    ->setCellValue('B' . $row, $v['realname'])
                    ->setCellValueExplicit('C' . $row, $v['studentId'],PHPExcel_Cell_DataType::TYPE_STRING)
                    ->setCellValue('D' . $row, tsGetSchoolByUid($v['uid']));
        }
        $objPHPExcel->getActiveSheet()->mergeCells('A1:D2');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $title);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->setBold(true)->setSize(20);
        $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(25);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(45);
        $len = (string) count($list);
        $style = 'D4:D' . ($len+3);
        $objPHPExcel->getActiveSheet()->getStyle($style)->getAlignment()->setWrapText(true);

// Rename worksheet
        $objPHPExcel->getActiveSheet()->setTitle($title);


// Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $objPHPExcel->setActiveSheetIndex(0);


// Redirect output to a client’s web browser (Excel5)
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$title.'.xls"');
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

