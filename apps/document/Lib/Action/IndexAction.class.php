<?php

class IndexAction extends BaseAction{

	var $school;
	var $schools;

	public function _initialize() {
		parent::_initialize();

                $schools = model('Schools')->_makeTree(0);

                if (!isset($_GET['sid'])) {
                    if(!isset($_SESSION['doc_school'])){
                        $user = $this->get('user');
                        $_SESSION['doc_school'] = $user['sid'];
                    }
                } else {
                    $_SESSION['doc_school'] = intval($_GET['sid']);
                }

                $sid = intval($_SESSION['doc_school']);
                $this->school = $sid;
                $this->schools = $schools;
                if($sid>0){
                    $this->assign('school', tsGetSchoolTitle($sid));
                }else{
                    $this->assign('school', '全部学校');
                }
        $this->assign('adhome', getAd($this->user['sid'], substr($this->user['year'], 0, 2), 4, 5)); //广告调用
        $this->assign('schoolid', $sid);
        $this->assign('schools', $schools);
    }

	public function getSchool(){
            $sid1s = model('Schools')->makeLevel0Tree($this->school);
            $res = array();
            if($sid1s){
                $res[] = $this->school;
            }
            foreach($sid1s as $v){
                $res[] = $v['id'];
            }
            return $res;
	}

	public function index(){


		$map['privacy']	=	1;
		$map['status']	=	1;
		//$map['schoolid']	= 	$this->getSchool();
		//$map['schoolid'] = array(array('eq',0),array('eq',$this->getSchool()), 'or');
                if($_SESSION['doc_school']>0){
                    $map['schoolid'] = array('in',$this->getSchool());
                }
		//print_r($map);

		$order = 'document_id DESC';
		$config = getConfig();
		$documents	=	D('Document')->where($map)->limit(8)->order($order)->findAll();
		$this->assign('newdocuments',$documents);

		//print_r($documents);
		$order = 'readCount DESC';
		$documents	=	D('Document')->where($map)->order($order)->findPage($config['document_raws']);
		$this->assign('hotdocuments',$documents);
		//print_r($documents);

		$map['isrecom']	=	1;
		$documents	=	D('Document')->where($map)->order( 'rTime DESC' )->limit(8)->findAll();
		$this->assign('recomdocuments',$documents);

		unset($map);
		$map['privacy']	=	1;
		$map['status']	=	1;
		$map['schoolid'] = array('in',$this->getSchool());

        $users = D('Document')->field('userId,count(*) AS count')->where($map)->group('userId')->order( 'count DESC' )->limit(8)->findAll();
		//print_r($users);
		$this->assign('users',$users);

		$category_tree   = D('Category')->_makeTopTree();
		$this->assign('category_tree',$category_tree);
		$this->assign('cid', 0);
		$this->display('newindex');
    }
	public function docs(){

		$keyword	=	h($_GET['keyword']);
		$cid	=	intval($_GET['cid']);
		$cid1	=	intval($_GET['cid1']);

		if($cid<0){
			$this->assign('jumpUrl', U('document/Index/index'));
			$this->error('分类不存在或已被删除！');
		}
		//echo $keyword;
		if(strlen($keyword) > 0) {
			$map['name']  = array('like',"%{$keyword}%");
			$this->assign('keyword',$keyword);
		} else if($cid<=0) {
			$this->error('请输入搜索条件！');
		}

		$category_tree   = D('Category')->_makeTopTree();
		$this->assign('category_tree',$category_tree);


		if($cid>0) {
			$sub_category_tree   = D('Category')->_makeTree($cid);
			$this->assign('sub_category_tree',$sub_category_tree);
			$map['cid0']	=	$cid;
			$this->assign('cid', $cid);
		} else {
			$this->assign('cid', -1);
		}

		if($cid1>0) {
			$map['cid1']	=	$cid1;
			$this->assign('cid1', $cid1);
		}
		//$map['schoolid'] = array(array('eq',0),array('eq',$this->getSchool()), 'or');
                if($_SESSION['doc_school']>0){
                    $map['schoolid'] = array('in',$this->getSchool());
                }
		$map['status']	=	1;
		$map['privacy']	=	1;

		$config = getConfig();
		$order = 'readCount DESC';
		$documents	=	D('Document')->where($map)->order($order)->findPage($config['document_raws']);
		$this->assign('documents',$documents);
		//print_r($documents);

		$this->display('newlist');
    }
	public function mydocs(){

		$map['userId']	=	$this->mid;
		$config = getConfig();
		$order = 'mtime DESC';
		$documents	=	D('Document')->where($map)->order($order)->findPage($config['document_raws']);
		$this->assign('documents',$documents);
		//print_r($documents);

		$category_tree   = D('Category')->_makeTopTree();
		$this->assign('category_tree',$category_tree);

		$this->assign('admin', "1");

		$this->assign('cid', "-1");

		$this->display('newlist');
    }

	public function docview(){

		$uid  = intval($_REQUEST['uid']);
		$id   = intval($_REQUEST['id']);
		$documentDao = D('Document');
		$document	  =	$documentDao->where("`id`={$id} AND userId={$uid} ")->find();

		//验证文档信息是否正确
		if(!$document){
			$this->assign('jumpUrl', U('document/Index/index'));
			$this->error('文档不存在或已被删除！');
		}

		//隐私控制
		if($this->mid!=$uid){
			if($document['privacy']!=1){
				$this->error('这个文档，只有主人自己可见。');
			}
		}

		require_once("php/lib/common.php");
		require_once("php/lib/doc2pdf_php5.php");
		require_once("php/lib/pdf2swf_php5.php");

		$configManager = new Config();
		$path = $configManager->getConfig('path.doc');
		$doc = $document['savepath'];
		$docid = $document['id'];
		$convertType = getConvertType($doc);

		$srcdoc = $path.$doc;

		$configManager = new Config();

		//$swfFilePath = $configManager->getConfig('path.swf') . $doc;
		$unread = SITE_URL . '/apps/document/Tpl/default/Public/js/unread.swf';
		$wordpre = (getConfig('allow_word_pre') ==1);

		switch($convertType) {
			case '2':
			if($wordpre) {
				$docconv=new doc2pdf();

				$targetfile = $configManager->getConfig('path.pdf'). $docid. ".pdf";

				$output=$docconv->convert($srcdoc, $targetfile);
				if(!$output){
					$this->unread();
				}
				$srcdoc = $targetfile;
			} else {
				$this->unread();
			}

			case '1':
				//echo $doc;
				$pdfconv=new pdf2swf();

				$targetfile = $configManager->getConfig('path.swf'). $docid. ".pdf" . ".swf";
				//echo $targetfile;
				$output=$pdfconv->convert($srcdoc, $targetfile);
				if(!$output){
					$this->unread();
				}
				$srcdoc = $targetfile;

			case '0':
			$swfFilePath = $srcdoc;

			if($configManager->getConfig('allowcache')){
				setCacheHeaders();
			}
			if(!$configManager->getConfig('allowcache') || ($configManager->getConfig('allowcache') && endOrRespond())){
				header('Content-type: application/x-shockwave-flash');
				header('Accept-Ranges: bytes');
				header('Content-Length: ' . filesize($swfFilePath));
				echo file_get_contents($swfFilePath);
			}

			break;
			default:
				echo "type error";
			break;
		}

	}

	public function unread() {
		$unread = SITE_URL . '/apps/document/Tpl/default/Public/js/unread.swf';

		header('Content-type: application/x-shockwave-flash');
		header('Accept-Ranges: bytes');
		header('Content-Length: ' . filesize($unread));
		echo file_get_contents($unread);
		exit;
	}
	public function doc(){


		$uid  = intval($_REQUEST['uid']);
		$id   = intval($_REQUEST['id']);
		//获取文档信息
		$documentDao = D('Document');
		$document	  =	$documentDao->where("`id`={$id} AND userId={$uid} ")->find();
		$this->assign('document',$document);

		//验证文档信息是否正确
		if(!$document){
			$this->assign('jumpUrl', U('document/Index/index'));
			$this->error('文档不存在或已被删除！');
		}

		//隐私控制
		if($this->mid!=$uid){
			if($document['privacy']!=1){
				$this->error('这个文档，只有主人自己可见。');
			}
		}



		//点击率加1
		$documentDao->execute('UPDATE '.C('DB_PREFIX').$documentDao->tableName." SET readCount=readCount+1 WHERE id={$id} AND userId={$this->uid} LIMIT 1");
		$this->assign('documentCount',$documentCount);
		$cid = $document['cid0'];
		//print_r($cid);
		unset($documents);
		//$this->setTitle(getUserName($this->uid).'的文档：'.$document['name']);
		//$this->display();

		$map['privacy']	=	1;
		$map['cid0']	=	$document['cid0'];
		$map['cid1']	=	$document['cid1'];
		$map['id']  = array('neq',$id);

		$order = 'readCount DESC';
		$config = getConfig();
		$documents	=	D('Document')->where($map)->limit(8)->order($order)->findAll();
		$this->assign('newdocuments',$documents);

			$category_tree   = D('Category')->_makeTopTree();
			$this->assign('category_tree',$category_tree);
			$sub_category_tree   = D('Category')->_makeTree($cid);
			$this->assign('sub_category_tree',$sub_category_tree);

			$this->assign('cid', $cid);
                        closeDb();
			$this->display('newview');
	    }

    public function download() {


		$uid  = intval($_REQUEST['uid']);
		$id   = intval($_REQUEST['id']);
		//获取文档信息
		$documentDao = D('Document');
		$document	  =	$documentDao->where("`id`={$id} AND userId={$uid} ")->find();
		$this->assign('document',$document);
		//验证文档信息是否正确
		if(!$document){
			$this->assign('jumpUrl', U('document/Index/index'));
			$this->error('文档不存在或已被删除！');
		}
		//隐私控制
		if($this->mid!=$uid){
			if($document['privacy']!=1){
				$this->error('这个文档，只有主人自己可见。');
			}
		}
		$name = $document['name'];
		$savepath = $document['savepath'];
		$file = get_document_url($savepath);
		//echo $file;
		if (!file_exists($file)) {
			$this->error('这个文档不存在');
		}

		$price = intval($document['credit']);
		if($this->mid!=$uid && $price>0) {
			$dao = M('wenku_download');
			$downloaded  =	$dao->where("`userid`={$this->mid} AND docid={$id} ")->find();

			if(!$downloaded){

				$setCredit = X('Credit');
				$userCredit = $setCredit->getUserCredit($this->mid);
				if($userCredit['score']['credit']<$price) {
					$this->error( $userCredit['score']['alias'].'不足,下载失败');
				}

				$d['ownerid'] = $uid;
				$d['price'] = $price;
				$d['userid'] = $this->mid;
				$d['docid'] = $id;
				$d['dTime'] = time();
				$ret = $dao->add($d);
				if($ret) {
					$setCredit->setUserCredit($this->mid,array('score'=>$price),-1);
					$setCredit->setUserCredit($uid,array('score'=>$price),1);

				}
			}
		}
		$documentDao->execute('UPDATE '.C('DB_PREFIX').$documentDao->tableName." SET downloadCount=downloadCount+1 WHERE id={$id} AND userId={$this->uid} LIMIT 1");

		$ua = $_SERVER["HTTP_USER_AGENT"];
		$filename = $document['name'].".".$document['extension'];
		$encoded_filename = urlencode($filename);
		$encoded_filename = str_replace("+", "%20", $encoded_filename);
		header('Content-Type: application/octet-stream');
		if (preg_match("/MSIE/", $ua)) {
		header('Content-Disposition: attachment; filename="' . $encoded_filename . '"');
		} else if (preg_match("/Firefox/", $ua)) {
		header('Content-Disposition: attachment; filename*="utf8\'\'' . $filename . '"');
		} else {
		header('Content-Disposition: attachment; filename="' . $filename . '"');
		}
		header('Content-Length: ' . filesize($file));
		echo file_get_contents($file);
	}
	public function del() {
		$map['id']		=	t($_REQUEST['id']);
		$result	=	D('Document')->deleteDocument($map['id'],$this->mid);
		if($result){
				X('Credit')->setUserCredit($this->mid, 'del_wenku_document');
                echo 1;exit;
		}else{
			//删除失败
			echo "0";exit;
		}
	}
}
?>