<?php

//相册应用 - UploadAction 上传文档 及 处理
class UploadAction extends BaseAction {

    public function _initialize() {
        parent::_initialize();
        $schools = model('Schools')->_makeTree(0);
        $sid = intval($_GET['sid']);
        if ($sid != 0) {
                $_SESSION['doc_school'] = $sid;
        }else{
               if (!isset($_SESSION['doc_school'])) {
                $user = $this->get('user');
                $_SESSION['doc_school'] = $user['sid'];
            }
        }
  
        $sid = intval($_SESSION['doc_school']);
        $this->school = $sid;
        $this->schools = $schools;
           if($sid>0){
                    $this->assign('school', tsGetSchoolTitle($sid));
                }else{
                    $this->assign('school', '全部学校');
                }
        $this->assign('schoolid', $sid);
        $this->assign('schools', $schools);
    }

    //普通上传
    public function index() {

        //$this->setTitle('上传文档');
        $category_tree = D('Category')->_makeTopTree();
        $this->assign('category_tree', $category_tree);
        $this->assign('cid', "-1");
        $this->display('newindex');
    }

    public function doAdd() {

        $config = getConfig();


        if ($config['max_document_num'] > 0 && $config['max_document_num'] <= D('Document')->where('isDel=0 AND userId=' . $this->mid)->count()) {
            //系统后台配置要求，如果超过，则不可以创建
            $this->error('你不可以再创建了，超过系统规定数目');
        }

        if (trim($_POST['dosubmit'])) {
            //检查验证码
            if (md5($_POST['verify']) != $_SESSION['verify']) {
                $this->error('验证码错误');
            }

            $document['uid'] = $this->mid;
            $document['name'] = h(t($_POST['name']));
            $document['intro'] = h(t($_POST['intro']));
            $document['rate'] = intval($_POST['rate']);
            $document['schoolid'] = intval($_POST['school0']);
            if(intval($_POST['school1']) > 0){
                $document['schoolid'] = intval($_POST['school1']);
            }
            $document['credit'] = intval($_POST['credit']);

            $document['cid0'] = intval($_POST['cid0']);
            $document['cid1'] = 0;
            intval($_POST['cid1']) > 0 && $document['cid1'] = intval($_POST['cid1']);

            if (!$document['name']) {
                $this->error('标题不能为空');
            } else if (get_str_length($_POST['name']) > 20) {
                $this->error('标题不能超过20个字');
            }

            if ($document['schoolid'] > 0) {
                if (null == model('Schools')->getField('id', array('id' => $document['schoolid']))) {
                    $this->error('请选择正确的学校分类');
                }
            } else {
                $document['schoolid'] = 0;
                //$this->error('请选择学校分类');
            }

            if ($document['cid0'] > 0) {
                if (null == D('Category')->getField('id', array('id' => $document['cid0']))) {
                    $this->error('请选择分类');
                }
            } else {
                $this->error('请选择分类');
            }
            if ($document['cid1'] > 0) {
                if (null == D('Category')->getField('id', array('id' => $document['cid1']))) {
                    $this->error('请选择分类');
                }
            }
            if (get_str_length($_POST['intro']) > 60) {
                $this->error('简介请不要超过60个字');
            }
            $document['privacy'] = $_POST['type'] == 'open' ? '1' : '0';
            //print_r($document);exit;
            $options['userId'] = $this->mid;
             $config['document_file_ext']='doc,docx,ppt,pptx,pdf,rar,zip';
            $options['allow_exts'] = $config['document_file_ext'];
            $options['max_size'] = $config['document_max_size'];

            $info = X('Xattach')->upload('document', $options);

            if ($info['status']) {
                //保存信息
                $this->save_document($document, $info['info']);
                //上传成功
                //计算积分
                //if($document['privacy']==1) {
                X('Credit')->setUserCredit($v['userId'], 'add_wenku_document');
                //}

                $this->assign('jumpUrl', U('/Index/mydocs'));
                $this->success('上传成功,请等待审核');
            } else {
                //上传出错
                $this->error($info['info']);
            }
        } else {
            $this->error('上传失败');
        }
    }

    //保存文档信息
    public function save_document($doc, $attachInfos) {
        //保存文档附件 并进行积分操作
        foreach ($attachInfos as $k => $v) {
            $document['attachId'] = $v['id'];
            $document['cid0'] = $doc['cid0'];
            $document['cid1'] = $doc['cid1'];
            $document['userId'] = $v['userId'];
            $document['cTime'] = time();
            $document['mTime'] = time();
            $document['name'] = $doc['name'];
            $document['intro'] = $doc['intro'];
            $document['size'] = $v['size'];
            $document['extension'] = $v['extension'];
            $document['savepath'] = $v['savepath'] . $v['savename'];
            $document['privacy'] = $doc['privacy'];
            $document['credit'] = $doc['credit'];
            $document['schoolid'] = $doc['schoolid'];
            $document['rate'] = $doc['rate'];
            $document['order'] = 10000;
            $documentid = D('Document')->add($document);
        }



        return true;
    }

}

?>