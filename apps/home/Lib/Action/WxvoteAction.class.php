<?php 

    class WxvoteAction extends Action{
        
        public function __construct(){
            parent::__construct();
            $mid = $_SESSION['mid'];
            if(empty($mid)){
                redirect(U('home/Wxlog/login&type=wxvote'));
            }
        }
        
        //活动首页
        public function index(){
            $this->display();
        }
        
        public function judge(){
            //用户发起本校活动的权限
            $user = M('user');
            $mid = $_SESSION['mid'];
            
            //判断用户是否登录
            if(empty($mid)){
                $error = 'unlogin';
                $this->ajaxReturn($error);
                die;
            }
            
            //判断用户是否参加活动
            $event = M('Wx_event');
            $map_e = array();
            $map_e['uid'] = $mid;
            $rel = $event->where($map_e)->find();
            if(!empty($rel)){
                $error = 'repeat';
                $this->ajaxReturn($error);
                die;
            }
            
            
            $map = array();
            $map['uid'] = $mid;
            $rel = $user->where($map)->find();
            
            
            //用户pu卡权限
            $card = M('bank_card');
            $map_c = array();
            $map_c['uid'] = $mid;
            $list = $card->where($map_c)->find();
            
            /* if(empty($rel['can_add_event'])){
                $error = '你没有权限';
                $this->ajaxReturn($error);
                die;
            }else{ */
                if($list['status'] == '2'){
                    $error = 'success';
                    $this->ajaxReturn($error);
                    die;
                }else{
                    $error = '没有权限';
                    $this->ajaxReturn($error);
                    die;
                }
            //}
        }
        
        //活动上传照片页面
        public function uppic(){
            $this->display();
        }
        
        
        //活动介绍
        public function introduce(){
            $this->display();
        }
        
        
        //test
        
        public function test(){

            /* $mid = $_SESSION['mid'];
            $card = M('bank_card');
            $map_c = array();
            $map_c['uid'] = $mid;
            $list = $card->where($map_c)->find();
            dump($list); */
            /* $user = M('Event');
            $map = array();
            $map['id'] = '103003';
            $map['uid'] = '2094049';
            $userList = $user->where($map)->find();
            dump($userList); */
            
            $reg = M('User_reg');
            $list = $reg->limit(3)->select();
            dump($list);
            
            
            
            $user = M('User');
            $map = array();
            $map['uid'] = array('gt','0');
            $list3 = $user->where($map)->limit(3)->select();
            dump($list3);
            
            /*$login = M('Login');
            $map_o = array();
            $map_o['uid'] = '19';
            $list2 = $login->where($map_o)->find();
            dump($list2);
            
            $user = M('User');
            $map = array();
            $map['uid'] = array('gt','0');
            $map['email'] = "yaoyf@test.com";
            $list = $user->where($map)->select();
            dump($list);
            
            $login = M('Login');
            $map_l = array();
            $map_l['uid'] = $list[0]['uid'];
            $list1 = $login->where($map_l)->find();
            dump($list1);
            die; */
            
        }
        
        
        
        //人气排行榜
        public function votesort(){
            
            $event = M('Wx_event');
            $eventList = $event->order('vote_num desc')->limit('20')->select();
            $uid = $_SESSION['mid'];
            //获取用户头像
            $img = getUserFace($uid);
            $rel = M('Wx_event')->where("uid=$uid")->find();
            if(empty($rel)){            //用户没有创建活动
                $rank = '0';            //我的排名
                $myvote = '0';          //我的票数
            }else{
                $myvote = $rel['vote_num'];
                foreach($eventList as $key=>$val){
                    if($rel['uid'] == $val['uid']){
                        $rank = $key+1;
                    }
                }
            }
            
            //一天内我已投票的活动
            $vote = M('Wx_vote');
            $map = array();
            $start = strtotime(date('Y-m-d'));
            $end = strtotime(date('Y-m-d',strtotime('+1day')));
            $map['ctime'] = array(array('gt',$start),array('lt',$end));
            $map['uid'] = $uid;
            $voteList = $vote->where($map)->field('eid')->findAll();
            $eids = array();
            foreach($voteList as $val){
                $eids[] = $val['eid'];
            }
            
            foreach($eventList as &$v){
                if(in_array($v['id'],$eids)){          //已投
                    $v['status'] = '0';
                }else{
                    $v['status'] = '1';                //未投
                }
            }
            
            unset($v);
            
            $this->assign('img',$img);
            $this->assign('rank',$rank);
            $this->assign('myvote',$myvote);
            $this->assign('list',$eventList);
            $this->display();
        }
        
        //全员排行榜
        public function rank(){
            //我的排名和投票数
            $event = M('Wx_event');
            
            import("ORG.Util.Page");
            
            //获取用户头像
            $img = getUserFace($uid);
            
            $map_e = array();
            $map_e['uid'] = array('gt','0');
            $realname = $_GET['realname'];
            if(!empty($realname)){
                $map_e['realname'] = array('like','%'.$realname.'%');
                $this->assign('realname',$realname);
            } 
            
            $count = $event->where($map_e)->count();  //显示分页总数
            
            $page = new Page($count,3);
            
            $eventList = $event->where($map_e)->limit($page->firstRow . ',' . $page->listRows)->order('vote_num desc')->select();
            //所有活动
            $result = $event->order('vote_num desc')->select();
            $uid = $_SESSION['mid'];
            $rel = M('Wx_event')->where("uid=$uid")->find();
            if(empty($rel)){            //用户没有创建活动
                $rank = '0';            //我的排名
                $myvote = '0';          //我的票数
            }else{
                $myvote = $rel['vote_num'];
                foreach($result as $key=>$val){
                    if($rel['uid'] == $val['uid']){
                        $rank = $key+1;
                    }
                }
            }
            
            
            //一天内我已投票的活动
            $vote = M('Wx_vote');
            $map = array();
            $start = strtotime(date('Y-m-d'));
            $end = strtotime(date('Y-m-d',strtotime('+1day')));
            $map['ctime'] = array(array('gt',$start),array('lt',$end));
            $map['uid'] = $uid;
            $voteList = $vote->where($map)->field('eid')->findAll();
            $eids = array();
            foreach($voteList as $val){
                $eids[] = $val['eid'];
            }
            
            foreach($eventList as &$v){
                if(in_array($v['id'],$eids)){          //已投
                    $v['status'] = '0';
                }else{
                    $v['status'] = '1';                //未投
                }
            }
            
            unset($v);
            
            $this->assign('img',$img);
            $this->assign('page',$page->show());
            $this->assign('rank',$rank);
            $this->assign('myvote',$myvote);
            $this->assign('list',$eventList);
            $this->display();
        }
        
        
        
        //投票操作
        public function vote(){
            $uid = $_SESSION['mid'];
            $eid = $_POST['eid'];
            $event = M('Wx_event');
            $map = array();
            $map['id'] = $eid;
            $rel = $event->where($map)->find();
            
            
            
            if($rel['uid'] == $uid){
                $error = '不能为自己投票';
                $this->ajaxReturn($error);
                die;
            }else{
                $vote = M('Wx_vote');
                $map = array();
                $start = strtotime(date('Y-m-d'));
                $end = strtotime(date('Y-m-d',strtotime('+1day')));
                $map['ctime'] = array(array('gt',$start),array('lt',$end));
                $map['uid'] = $uid;
                $voteList = $vote->where($map)->findAll();
                if(empty($voteList)){
                    //投票表添加数据
                    $data = array();
                    $data['eid'] = $eid;
                    $data['uid'] = $uid;
                    $data['ctime'] = time();
                    $result1 = M('Wx_vote')->add($data);
                    
                    //活动表添加数据
                    $map_e = array();
                    $map_e['id'] = $eid;
                    $data_e['vote_num'] = $rel['vote_num'] + 1;
                    $result2 = $event->where($map_e)->save($data_e);
                    if(empty($result1) || empty($result2)){
                        $error = '投票失败';
                        $this->ajaxReturn($error);
                        die;
                    }else{
                        $error = 'success';
                        $this->ajaxReturn($error);
                        die;
                    }
                }else{
                    $error = '你今天投票权用完了，明天再来';
                    $this->ajaxReturn($error);
                    die;
                }
                
            }
        }
        
        
        //图片上传
        public  function fileupload(){
            header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
            header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
            
            
            
            if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
                exit; 
            }
            
            
            if ( !empty($_REQUEST[ 'debug' ]) ) {
                $random = rand(0, intval($_REQUEST[ 'debug' ]) );
                if ( $random === 0 ) {
                    header("HTTP/1.0 500 Internal Server Error");
                    exit;
                }
            }
            
            @set_time_limit(5 * 60);
            
            $targetDir = 'public/wxvote/upload_tmp';
            $uploadDir = 'public/wxvote/pic';
            
            $cleanupTargetDir = true; // Remove old files
            $maxFileAge = 5 * 3600; // Temp file age in seconds
            
            
            // Create target dir
            if (!file_exists($targetDir)) {
                @mkdir($targetDir);
            }
            
            // Create target dir
            if (!file_exists($uploadDir)) {
                @mkdir($uploadDir);
            }
            
            // Get a file name
            if (isset($_REQUEST["name"])) {
                $fileName = date('YmdHis').$_REQUEST["name"];
            } elseif (!empty($_FILES)) {
                $fileName = date('YmdHis').$_FILES["file"]["name"];
            } else {
                $fileName = uniqid("file_");
            }
            
            $filePath = $targetDir . DIRECTORY_SEPARATOR . $fileName;
            $uploadPath = $uploadDir . DIRECTORY_SEPARATOR . $fileName;
            
            
            
            //上传图片时，添加一条活动记录
            
            $data = array();
            $map = array();
            $uid = $_SESSION['mid'];
            $map['uid'] = $uid;
            $rel = M('User')->where($map)->field('realname,sid')->find();      //根据uid获取用户信息
            
            
            $map = array();
            $map['id'] = $rel['sid'];
            $school = M('School')->where($map)->field('title')->find();       //根据sid获取学校信息
            
            $data['realname'] = $rel['realname'];
            $data['school'] = $school['title'];
            $data['uid'] = $uid;
            $data['pic'] = "wxvote/pic/$fileName";
            
            //判断该用户是否已经创建活动
            $map = array();
            $map['uid'] = $uid;
            $result = M('Wx_event')->where($map)->select();
            if(empty($result)){
                M('Wx_event')->add($data);
            }else{
                $data_u['pic'] = "wxvote/pic/$fileName";
                $map_u['uid'] = $uid;
                M('Wx_event')->where($map_u)->save($data_u);
            }
            
            
            
            // Chunking might be enabled
            $chunk = isset($_REQUEST["chunk"]) ? intval($_REQUEST["chunk"]) : 0;
            $chunks = isset($_REQUEST["chunks"]) ? intval($_REQUEST["chunks"]) : 1;
            
            
            // Remove old temp files
            if ($cleanupTargetDir) {
                if (!is_dir($targetDir) || !$dir = opendir($targetDir)) {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}');
                }
            
                while (($file = readdir($dir)) !== false) {
                    $tmpfilePath = $targetDir . DIRECTORY_SEPARATOR . $file;
            
                    // If temp file is current file proceed to the next
                    if ($tmpfilePath == "{$filePath}_{$chunk}.part" || $tmpfilePath == "{$filePath}_{$chunk}.parttmp") {
                        continue;
                    }
            
                    // Remove temp file if it is older than the max age and is not the current file
                    if (preg_match('/\.(part|parttmp)$/', $file) && (@filemtime($tmpfilePath) < time() - $maxFileAge)) {
                        @unlink($tmpfilePath);
                    }
                }
                closedir($dir);
            }
            
            
            // Open temp file
            if (!$out = @fopen("{$filePath}_{$chunk}.parttmp", "wb")) {
                die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
            }
            
            if (!empty($_FILES)) {
                if ($_FILES["file"]["error"] || !is_uploaded_file($_FILES["file"]["tmp_name"])) {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}');
                }
            
                // Read binary input stream and append it to temp file
                if (!$in = @fopen($_FILES["file"]["tmp_name"], "rb")) {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                }
            } else {
                if (!$in = @fopen("php://input", "rb")) {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}');
                }
            }
            
            while ($buff = fread($in, 4096)) {
                fwrite($out, $buff);
            }
            
            @fclose($out);
            @fclose($in);
            
            rename("{$filePath}_{$chunk}.parttmp", "{$filePath}_{$chunk}.part");
            
            $index = 0;
            $done = true;
            for( $index = 0; $index < $chunks; $index++ ) {
                if ( !file_exists("{$filePath}_{$index}.part") ) {
                    $done = false;
                    break;
                }
            }
            if ( $done ) {
                if (!$out = @fopen($uploadPath, "wb")) {
                    die('{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}');
                }
            
                if ( flock($out, LOCK_EX) ) {
                    for( $index = 0; $index < $chunks; $index++ ) {
                        if (!$in = @fopen("{$filePath}_{$index}.part", "rb")) {
                            break;
                        }
            
                        while ($buff = fread($in, 4096)) {
                            fwrite($out, $buff);
                        }
            
                        @fclose($in);
                        @unlink("{$filePath}_{$index}.part");
                    }
            
                    flock($out, LOCK_UN);
                }
                @fclose($out);
            }
            
            // Return Success JSON-RPC response
            die('{"jsonrpc" : "2.0", "result" : null, "id" : "id"}');
        }
    }

?>