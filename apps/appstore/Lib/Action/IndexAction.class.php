<?php

class IndexAction extends BaseAction{
	
	var $platform = 0;
	var $platforms = array();

	
	public function _initialize() {
		parent::_initialize();
		
		$platforms = D('Category')->_makeTree(0, 2);		
		
		$platform =	intval($_GET['platform']);
		
		if($platform==0) {
			$platform = intval($_SESSION['platform']);
		} else {
			$_SESSION['platform'] = $platform;
		}
		if($platform<=0 || $platform>count($platforms)) {
			$platform = 0;
		} else {
			$platform--;
		}
		
		$this->platform = $platform;
		$this->platforms = $platforms;
		$this->assign('platform',$platforms[$platform]['t']);
		$this->assign('platforms',$platforms);	
	}
	
	public function getplatform(){
		if(isset($this->platforms[$this->platform]['a'])) {
			return $this->platforms[$this->platform]['a'];	
		} 
		return 0;		
	}
	
	public function index(){
		
		$type = 0;					
		$recomList1   = D('Category')->_makeTree(1, 1);
		for($i=0,$len=count($recomList1);$i<$len;$i++) {
			if($i>=7) {
				unset($recomList1[$i]);
			} else {
				$cid1 = $recomList1[$i]['a'];
				$recomList1[$i]['r'] = $this->getRecom2List($cid1,12);
			}						
		}
		$this->assign('recomList1',$recomList1);

		$recomList2   = D('Category')->_makeTree(2, 1);
		for($i=0,$len=count($recomList2);$i<$len;$i++) {
			if($i>=7) {
				unset($recomList2[$i]);
			} else {
				$cid1 = $recomList2[$i]['a'];
				$recomList2[$i]['r'] = $this->getRecom2List($cid1,12);
			}						
		}
		
		$this->assign('recomList2',$recomList2);
				
		$bannerlist = $this->getBannerList(5);
		$this->assign('bannerlist',$bannerlist);
				
		$linklist = $this->getLinkList(16);
		$this->assign('linklist',$linklist);		
		
		//print_r($linklist);
		
		$hotList1 = $this->getHotList(1,10);
		$this->assign('hotList1',$hotList1);

		$hotList2 = $this->getHotList(2,10);
		$this->assign('hotList2',$hotList2);
				
		$recomList3 = $this->getDocList(8);
		$this->assign('recomList3',$recomList3);		
				
		$this->assign('type',$type);
		$this->display('newindex');
	}
	

	//显示一张文档
	public function document() {
		$id   = intval($_REQUEST['id']);

		//获取文档信息
		$documentDao = D('Document');
		$document	  =	$documentDao->where("`document_id`={$id} AND is_active=1")->find();
		

		//验证文档信息是否正确
		if(!$document){
			$this->assign('jumpUrl', U('appstore/Index'));
			$this->error('文档不存在或已被删除！');
		}

		//点击率加1
		$documentDao->execute('UPDATE '.C('DB_PREFIX').$documentDao->tableName." SET readCount=readCount+1 WHERE document_id={$id} LIMIT 1");
		
		$document['content'] = htmlspecialchars_decode($document['content']);
		
		$linklist = $this->getLinkList(16);
		$this->assign('linklist',$linklist);
		
		$this->assign('type',"3");	
		$this->assign('app',$document);
		$this->assign('documentCount',$documentCount);
		$this->setTitle($document['title']);
		$this->display('newdocview');

	}
	
	public function app() {
		$id   = intval($_REQUEST['id']);

		//获取文档信息
		$dao = D('Appx');
		$app	  =	$dao->where("`id`={$id} AND is_active=1")->find();
		

		//验证文档信息是否正确
		if(!$app){
			$this->assign('jumpUrl', U('appstore/Index'));
			$this->error('应用不存在或已被删除！');
		}


		//点击率加1
		$dao->execute('UPDATE '.C('DB_PREFIX').$dao->tableName." SET readCount=readCount+1 WHERE id={$id} LIMIT 1");
		
		$app['content'] = htmlspecialchars_decode($app['content']);


		$type = $app['category'];
		$this->assign('type',$type);
		if($type<3) {
			$category_tree   = D('Category')->_makeTree($type, 1);
			$this->assign('category_tree',$category_tree);
			
			$recomList1 = $this->getRecomList(10);
			$this->assign('recomList1',$recomList1);			
			
			$recomList2 = $this->getRecom2List($app['cid1'],10);
			$this->assign('recomList2',$recomList2);
			$hotList = $this->getHotList($type,10);
			$this->assign('hotList',$hotList);
		}
		
		$linklist = $this->getLinkList(16);
		$this->assign('linklist',$linklist);
		
		$this->assign('app',$app);
		$this->setTitle($app['title']);
		$this->display('newappview');

	}
	
	public function appdown() {
		$id   = intval($_REQUEST['id']);
		$dao = D('Appx');
		$app	  =	$dao->where("`id`={$id} AND is_active=1")->find();		
		if(!$app){
			//$this->assign('jumpUrl', U('appstore/Index'));
			//$this->error('应用不存在或已被删除！');
		} else {
		$dao->execute('UPDATE '.C('DB_PREFIX').$dao->tableName." SET downloadCount=downloadCount+1 WHERE id={$id} LIMIT 1");
		}
		
		echo "1";
	}
	
    public function items() {
		$keyword	=	h($_POST['keyword']);		
		$type	=	intval($_GET['type']);
		if(!in_array($type,array(1,2,3,4))) {
			$type	=	intval($_POST['type']);
			if(!in_array($type,array(1,2,3,4))) {
				$type = 1;
			}
		}
		
		$cid	=	intval($_GET['cid']);
		
		switch( $type ) {
			case 1:
			$dao	=	D('Appx');
			$map['category'] = $type;
			$title = "应用";
			$searchApp = true;
			break;
			case 2:
			$dao	=	D('Appx');
			$map['category'] = $type;
			$title = "游戏";
			$searchApp = true;
			break;
			case 3:
			$dao	=	D('Document');
			$map['category'] = $type-3;
			$title = "资讯";
			$searchApp = false;
			break;
			case 4:
			$dao	=	D('Document');
			$map['category'] = $type-3;
			$title = "评测";
			$searchApp = false;
			break;									
		}
		
		
		
		$this->assign('type',$type);								
		$condition = false;	
		if($cid>0 && $searchApp) {		
			$cname = D('Category')->getField('title', array('id'=>$cid));			
			if(strlen($cname)>0) {
				$map['cid1']  = $cid;
				$this->assign('cid', $cid);
				$this->assign('cname', $cname);		
				$condition = true;
			}
		} else {
			$this->assign('cid', 0);
		}
		
		if($type<3) {
			$category_tree   = D('Category')->_makeTree($type, 1);
			$this->assign('category_tree',$category_tree);
		}
		
		
		if(strlen($keyword) > 0) {			
			$map['title']  = array('like',"%{$keyword}%");
			$this->assign('keyword',$keyword);
			$condition = true;
		}		
		
		$config = getConfig();
		
		$map['platform'] = $this->getplatform();
		
		$order = "`mtime` DESC";		
		if($condition) {							
			$items	=	$dao->where($map)->order($order)->findPage($config['document_raws']);
			if(strlen($keyword) > 0) {		
				foreach ($items['data'] as &$item) {
					$item['title']	 = preg_replace("/{$keyword}/i", "<span class=\"cRed\">\\0</span>", $item['title'], 1);
				}							
			}							
		} else {	
			$items	=	$dao->where($map)->order($order)->findPage($config['document_raws']);
		}
		//print_r($map);
		//print_r($items);	
		
		$linklist = $this->getLinkList(16);
		$this->assign('linklist',$linklist);
		$this->assign('typename',$title);
		$this->assign('items',$items);
		$this->display('newlist');
    }	

	function getDocList($limit=10){				
			$type = 0;
			$lists = M('appstore_document')->where(' category='.$type.' And is_active=1 And platform='.$this->getplatform())->order( 'readCount DESC' )->limit($limit)->findAll();		
		
		return $lists;		
	}
	
	function getHotList($type, $limit=10){	

			$lists = M('appstore_app')->where(' category='.$type.' And is_active=1 And platform='.$this->getplatform())->order( 'readCount DESC' )->limit($limit)->findAll();			
	
		return $lists;		
	}

	function getRecomList($limit=10){	
		//小类
			$lists = M('appstore_app')->field('title,id,icon,readCount,downloadCount')->where('isrecom=1 And is_active=1  And platform='.$this->getplatform())->order( 'rTime DESC' )->limit($limit)->findAll();
		return $lists;		
	}
		
	function getRecom2List($type, $limit=10){	
		//小类
			$lists = M('appstore_app')->field('title,id,icon,readCount,downloadCount')->where(' cid1='.$type.' And isrecom=1 And is_active=1  And platform='.$this->getplatform())->order( 'rTime DESC' )->limit($limit)->findAll();
		return $lists;		
	}	
	function getBannerList($limit=10){	

			$lists = M('appstore_banner')->field('title,id,img,url')->where('is_active=1 And platform='.$this->getplatform())->order( 'mTime DESC' )->limit($limit)->findAll();
		return $lists;		
	}
	function getLinkList($limit=10){	

			$lists = M('appstore_link')->field('title,id,img,url')->where('is_active=1')->order( 'mTime DESC' )->limit($limit)->findAll();
		return $lists;		
	}	
}




?>