<?php

class HomeAction extends AdministratorAction {

    // 统计信息
    public function statistics() {
        $statistics = array();

        /*
         * 重要: 为了防止与应用别名重名，“服务器信息”、“用户信息”、“开发团队”作为key前面有空格
         */

        // 服务器信息
//		$site_version = model('Xdata')->get('siteopt:site_system_version');
//		$serverInfo['核心版本']        	= 'ThinkSNS ' . $site_version;
//        $serverInfo['服务器系统及PHP版本']	= PHP_OS.' / PHP v'.PHP_VERSION;
//        $serverInfo['服务器软件'] 			= $_SERVER['SERVER_SOFTWARE'];
//        $serverInfo['最大上传许可']     	= ( @ini_get('file_uploads') )? ini_get('upload_max_filesize') : '<font color="red">no</font>';
//
//        $mysqlinfo = M('')->query("SELECT VERSION() as version");
//        $serverInfo['MySQL版本']			= $mysqlinfo[0]['version'] ;
//        $t = M('')->query("SHOW TABLE STATUS LIKE '".C('DB_PREFIX')."%'");
//        foreach ($t as $k){
//            $dbsize += $k['Data_length'] + $k['Index_length'];
//        }
//        $serverInfo['数据库大小']			= byte_format( $dbsize );
//        $statistics[' 服务器信息'] = $serverInfo;
//        unset($serverInfo);
        // 用户信息
        //$user['当前在线3分钟内'] = getOnlineUserCount();
        
        $key = 'Tj_tjUser';
        $cache = Mmc($key);
        if ($cache !== false) {
            $user = json_decode($cache, true);
        }else{
            $user['全部用户'] = M('user')->count();
            $user['登录过的用户'] = M('login_count')->count();
            $user['客户端登录过的用户'] = M('login')->count();
            $user['初始化过的用户'] = M('user')->where('`is_init` = 1')->count();
            $user['最后统计时间'] = date('Y-m-d H:i');
            Mmc($key, json_encode($user),0,3600*12);
        }
        $statistics[' 用户信息'] = $user;
        unset($user);

        // 应用统计
        $applist = array();
//        $res = model('App')->where('`statistics_entry`<>""')->field('app_name,app_alias,statistics_entry')->order('display_order ASC')->findAll();
//        foreach ($res as $v) {
//        	$d = explode('/', $v['statistics_entry']);
//        	$d[1] = empty($d[1]) ? 'index' : $d[1];
//        	$statistics[$v['app_alias']] = D($d[0], $v['app_name'])->$d[1]();
//        }
        // 开发团队
        $statistics[' 开发团队'] = array(
            '版权所有' => '<a href="http://www.zhishisoft.com" target="_blank">苏州天宫网络科技有限公司</a>',
        );

        $this->assign('statistics', $statistics);
        $this->display();
    }

    public function yy() {
        $list = M('y_tj')->order('day DESC')->findPage(7);
        $this->assign($list);
        $this->display();
    }

    public function pu() {
        $userId = intval($_POST['userId']);
        $db_prefix = C('DB_PREFIX');
        $dao = M('money');
        $dao->table("{$db_prefix}money AS a ")
            ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
            ->order('a.money DESC')
            ->field('a.uid,b.realname,b.sid,a.money');
        $count = false;
        if($_POST['userId']){
            $dao->where('a.uid='.$userId);
        }else{
            $tableRows = M('')->query("select table_rows from information_schema.tables where table_name='ts_money'");
            $count = $tableRows[0]['table_rows'];
        }
        $list = $dao->findPage(10,$count);
        $this->assign($list);
        $this->display();
    }

    public function moneyin() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_searchMoneyIn'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_searchMoneyIn']);
            if(is_array($_POST))
            $_REQUEST = array_merge($_REQUEST, $_POST);
        } else {
            unset($_SESSION['admin_searchMoneyIn']);
        }
        $map = array();
        //组装搜索条件
        $fields = array('a__uid');
        $map = array();
        foreach ($fields as $v)
            if (isset($_REQUEST[$v]) && $_REQUEST[$v] != ''){
                $key=  str_replace('__', '.', $v);
                $map[$key] = t($_REQUEST[$v]);
            }
        if (isset($_POST['typeName']) && $_POST['typeName'] != ''){
            $map['typeName'] = array('like',t($_POST['typeName']).'%');
        }
        //var_dump($map);die;
        $db_prefix = C('DB_PREFIX');
        $dao = M('money_in');
        $count = false;
        if(empty($map)){
            $tableRows = M('')->query("select table_rows from information_schema.tables where table_name='ts_money_in'");
            $count = $tableRows[0]['table_rows'];
        }else{
            // 只搜索typeName时缓存count
            $mapCnt = count($map);
            if($mapCnt==1 && isset($map['typeName'])){
                $count = $this->_moneyInTypeCache(t($_POST['typeName']));
            }
        }
        $list = $dao->table("{$db_prefix}money_in AS a ")
            ->join("{$db_prefix}user AS b ON  a.uid=b.uid")
            ->order('a.id DESC')
            ->where($map)
            ->field('a.uid,a.ctime,b.realname,b.sid,a.logMoney,typeName')
            ->findPage(10,$count);
        $this->assign($list);
        $this->display('moneyin');
    }
    private function _moneyInTypeCache($typeName){
        $key = 'Admin_MoneyIn_'.$typeName;
        $cache = Mmc($key);
        if($cache !== false){
            return $cache;
        }
        $map['typeName'] = array('like',$typeName.'%');
        $cnt = M('money_in')->where($map)->count();
        Mmc($key,$cnt,0,3600*12);
        return $cnt;
    }
    public function moneyout() {
        //为使搜索条件在分页时也有效，将搜索条件记录到SESSION中
        if (!empty($_POST)) {
            $_SESSION['admin_searchMoneyOut'] = serialize($_POST);
        } else if (isset($_GET[C('VAR_PAGE')])) {
            $_POST = unserialize($_SESSION['admin_searchMoneyOut']);
            if(is_array($_POST))
            $_REQUEST = array_merge($_REQUEST, $_POST);
        } else {
            unset($_SESSION['admin_searchMoneyOut']);
        }
        $map = array();
        //组装搜索条件
        $fields = array('out_uid');
        $map = array();
        foreach ($fields as $v)
            if (isset($_REQUEST[$v]) && $_REQUEST[$v] != ''){
                $key=  str_replace('__', '.', $v);
                $map[$key] = t($_REQUEST[$v]);
            }
        if (isset($_POST['typeName']) && $_POST['typeName'] != ''){
            $map['out_title'] = array('like',t($_POST['typeName']).'%');
        }
        //var_dump($map);die;
        $count = false;
        if(empty($map)){
            $tableRows = M('')->query("select table_rows from information_schema.tables where table_name='ts_money_in'");
            $count = $tableRows[0]['table_rows'];
        }else{
            // 只搜索typeName时缓存count
            $mapCnt = count($map);
            if($mapCnt==1 && isset($map['out_title'])){
                $count = $this->_moneyOutTypeCache(t($_POST['typeName']));
            }
        }
        $db_prefix = C('DB_PREFIX');
        $dao = M('money_out');
        $list = $dao->table("{$db_prefix}money_out AS a ")
            ->join("{$db_prefix}user AS b ON  a.out_uid=b.uid")
            ->order('a.id DESC')
            ->where($map)
            ->field('out_uid,out_ctime,b.realname,b.sid,out_money,out_title')
            ->findPage(10,$count);
        $this->assign($list);
        $this->display('moneyout');
    }
    private function _moneyOutTypeCache($typeName){
        $key = 'Admin_MoneyOut_'.$typeName;
        $cache = Mmc($key);
        if($cache !== false){
            return $cache;
        }
        $map['out_title'] = array('like',$typeName.'%');
        $cnt = M('money_out')->where($map)->count();
        Mmc($key,$cnt,0,3600*12);
        return $cnt;
    }

    public function tg() {
$uids = array('2472975','1478638','1478637','1478636','1478635','1478631','1478630','1473609','1473608','1472707','1472705','1472585','1471575','1471507','1454805','1448764','1267214','1267213','1267171','264864','222527','217241','217236','206762','206758','179238','96513','71052','71051','71047','71045','33655','33654','27609','27596','1');
$realname = array('娄德明','万松菊','安丽娜','朱晓峰','章润芳','沈静','胡欢欢','王素婷','朱恒','PU运营中心-Jean','PU-旅游','PU运营中心-潘芸','PU运营中心-夏萍','PU运营中心-杨虹','张晓军','王晓峰','PU运营中心-严庆','王莹','林忠','丛琪','ios测试2','PU运营中心-小爷','林一','林一','蒋慧','天宫2013','PU运营团队','PU运营中心-蒋慧','PU运营中心-雨婷','PU运营中心-本明','测试平台超管','冬天怕冷','陆冬云','徐锦盛','anglela','PU团队');
$p = intval($_GET['p']);
$p = $p?$p-1:0;
$dao = model('TgLogin');
$res = array();
for($i=0;$i<7;$i++){
    $vor = $p*7+$i;
    $day = date('Y-m-d',strtotime('-'.$vor.' day'));
    $days[] = $day;
    $logins[] = $dao->getTgLoginCache($day);
}
$i = 0;
foreach($uids as $uid){
    $res[$i][] = $uid;
    $res[$i][] = $realname[$i];
    //$res[$i][] = getrea;
    foreach($logins as $login){
        if(in_array($uid, $login)){
            $res[$i][] = '<span class="cGreen">登录<span>';
        }else{
            $res[$i][] = ' ';
        }
    }
    $i++;
}
$this->assign('days',$days);
$this->assign('list',$res);
$this->assign('p',$p+1);
$this->assign('totalRows',count($uids));
    $this->display();
    }
    //PU币发放 发放
    public function doPubi(){
        $reason = t($_POST['reason']);
        if(empty($reason)){
            $this->error('请填写发放理由');
        }
        $filePath = $_POST['filePath'];
        $dao = service('Excel');
        $res = $dao->read($filePath);
        if(empty($res)){
            $this->error('文档无内容');
        }
        $sdb = M('school')->where('pid=0')->field('title,email')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['email'];
        }
        unset($res[0]);
        $daoUser = M('user');
        $daoMoney = Model('Money');
        $daoNotify = service('Notify');
        $suc = 0;
        $fail = 0;
        $msg = '';
        foreach ($res as $k=>$v){
            if(!$v[0]&&!$v[1]&&!$v[2]&&!$v[3]){
                continue;
            }
            $line = $k+1;
            $s = t($v[1]);
            $email = t($v[0]).$school[$s];
            $uid = $daoUser->getField('uid',"email='".$email."'");
            if($uid){
                $money = t($v[2])*100;
                $mid = $daoMoney->moneyIn($uid, $money, $reason);
                if($mid){
                    $suc++;
                    // 发送通知
                    $notify_data = array('body' => $reason);
                    $daoNotify->sendIn($uid, 'admin_pubi', $notify_data);
                }else{
                    $fail++;
                    $msg .= $line.' 写入失败 '.$v[0].$s.'<br/>';
                }
            }else{
                $fail++;
                $msg .= $line.' 没找到用户 '.$v[0].$s.'<br/>';
            }
        }
        @unlink($filePath);
        $this->success('发放完毕<br/><span class="cGreen">成功：'.$suc.'条 </span><span class="cRed">失败：'.$fail.'条<br/>'.$msg.'</span>');
    }
    //PU币发放 上传文件检查
    public function checkPubiEmail(){
        $res = $this->_readExcel();
        $soll = array('学号','学校','PU币','姓名');
        $this->_checkFirstLine($soll,$res[0]);
        $sdb = M('school')->where('pid=0')->field('title,email')->findAll();
        foreach($sdb as $v){
            $school[$v['title']] = $v['email'];
        }
        unset($res[0]);
        $daoUser = M('user');
        $suc = 0;
        $fail = 0;
        $msg = '';
        foreach ($res as $k=>$v){
            if(!$v[0]&&!$v[1]&&!$v[2]&&!$v[3]){
                continue;
            }
            $line = $k+1;
            $realname = t($v[3]);
            $money = $v[2]*100/100;
            if($money<=0){
                $fail++;
                $msg .= "第$line 列 $realname $v[1] PU币为0 <br/>";
            }elseif($money>10){
                $fail++;
                $msg .= "第$line 列 $realname $v[1] PU币大于10 <br/>";
            }
            $s = t($v[1]);
            $map['email'] = t($v[0]).$school[$s];
            $likeRealname = mb_substr($realname, 0, 3);
            $map['realname'] = array('like',$likeRealname.'%');
            $has = $daoUser->where($map)->field('uid')->find();
            if(!$has){
                $fail++;
                $msg .= "第$line 列 $realname $v[1] 用户不存在 <br/>";
            }else{
                $suc++;
            }
        }
        $this->_endCheck($msg, $suc,$fail);
    }
    private function _checkFirstLine($soll,$row){
        $cnt = count($soll);
        if(count($row)!=$cnt){
            $this->error('表格第一行错误，请检查拼写及顺序');
            return false;
        }
        for($i=0;$i<$cnt;$i++){
            if($row[$i] != $soll[$i]){
                $hang = $i+1;
                $this->error('第'.$hang.'列【'.$row[$i].'】错误，应该为 【'.$soll[$i].'】');
                return false;
            }
        }
        return true;
    }
    //添加学校
    public function addSchool(){
        $this->assign('menu2', 4);
        $this->display();
    }
    //添加院系
    public function addYx(){
        $this->assign('menu2', 5);
        $this->display();
    }
    //读取excel
    private function _readExcel(){
        if(empty($_FILES['file']) || strpos($_FILES['file']['name'], '.xls')===false){
            $this->error('请上传excel文档');
        }
        $dao = service('Excel');
        $res = $dao->read($_FILES['file']['tmp_name']);
        if(empty($res)){
            $this->error('文档无内容');
        }
        return $res;
    }
    private function _hasSchool($title){
        $res = M('school')->getField('id',"pid=0 and title='$title'");
        if($res){
            return $res;
        }
        return 0;
    }
    private function _hasDomain($domain){
        $res = M('school')->where("pid=0 and domain='$domain'")->field('id')->find();
        if($res){
            return true;
        }
        return false;
    }
    private function _hasYx($title,$sid){
        $res = M('school')->getField('id',"pid=$sid and title='$title'");
        if($res){
            return $res;
        }
        return 0;
    }
    //添加学校 上传文件检查
    public function checkAddSchool(){
        $res = $this->_readExcel();
        $soll = array('学校','省份','城市','域名','几年制');
        $this->_checkFirstLine($soll,$res[0]);
        unset($res[0]);
        $suc = 0;
        $fail = 0;
        $msg = '';
        $excelSchool = array();
        $excelDomain = array();
        foreach ($res as $k=>$v){//检查学校是否存在，域名是否唯一，年制1-10
            if(!$v[0]&&!$v[1]&&!$v[2]&&!$v[3]&&!$v[4]){//空行不管继续
                continue;
            }
            $line = $k+1;
            $lineEmpty = '';
            foreach ($soll as $x => $y) {
                if(!$v[$x]){
                    $lineEmpty .= $y.'为空 ';
                }
            }
            if($lineEmpty!=''){
                $fail++;
                $msg .= "第$line 行 $lineEmpty <br/>";
                continue;
            }
            $sname = t($v[0]);
            if($this->_hasSchool($sname)){
                $fail++;
                $msg .= "第$line 行 $sname 学校已存在 <br/>";
                continue;
            }
            if(isset($excelSchool[$sname])){
                $fail++;
                $msg .= "第$line 行和第".$excelSchool[$sname]." 行 $sname 学校重复<br/>";
                continue;
            }
            $excelSchool[$sname] = $line;
            $domain = t($v[3]);
            if($this->_hasDomain($domain)){
                $fail++;
                $msg .= "第$line 行 $domain 域名已存在 <br/>";
                continue;
            }
            if(isset($excelDomain[$domain])){
                $fail++;
                $msg .= "第$line 行和第".$excelDomain[$domain]." 行 $sname 域名重复<br/>";
                continue;
            }
            $excelDomain[$domain] = $line;
            $year = intval($v[4]);
            if($year<3 || $year>4){
                $fail++;
                $msg .= "第$line 行$year 几年制不在范围3-4<br/>";
                continue;
            }
            $suc++;
        }
        $this->_endCheck($msg, $suc,$fail);
    }
    private function _endCheck($msg,$suc,$fail){
        if($msg!=''){
            $this->error("共$fail 个错误<br/>".$msg);
        }else{
            $file_name = time() . '_' . $this->mid . '.xls'; //使用时间来模拟图片的ID.
            $file_path = SITE_PATH . '/data/tmp/' . $file_name;
            $file = @$_FILES['file']['tmp_name'];
            file_exists($file_path) && @unlink($file_path);
            if (@copy($file, $file_path) || @move_uploaded_file($file, $file_path)) {
                @unlink($file);
                $this->ajaxReturn($file_path,"检查通过，共$suc 行有效数据，可以进行发放操作",1);
            }
            $this->error('保存文件失败');
        }
    }
    //添加学校 发放
    public function doAddSchool(){
        $filePath = $_POST['filePath'];
        $dao = service('Excel');
        $res = $dao->read($filePath);
        if(empty($res)){
            $this->error('文档无内容');
        }
        unset($res[0]);
        $daoSchool = model('Schools');
        $suc = 0;
        $fail = 0;
        $msg = '';
        foreach ($res as $k=>$v){
            if(!$v[0]&&!$v[1]&&!$v[2]&&!$v[3]&&!$v[4]){
                continue;
            }
            $line = $k+1;
            $provId = $this->_getProvId(t($v[1]));
            if(!$provId){
                $fail++;
                $msg .= $line.' 省份写入失败 '.$v[1].'<br/>';
                continue;
            }
            $cityId = $this->_getCityId(t($v[2]),$provId);
            if(!$cityId){
                $fail++;
                $msg .= $line.' 城市写入失败 '.$v[2].'<br/>';
                continue;
            }
            $title = t($v[0]);
            $domain = t($v[3]);
            $data['title'] = $title;
            $data['display_order'] = pinyin($title);
            $data['domain'] = $domain;
            $data['email'] = '@'.$domain.'.com';
            $data['cityId'] = $cityId;
            $data['provinceId'] = $provId;
            $data['tj_year'] = intval($v[4]);
            $data['module'] = '';
            $sid = $daoSchool->add($data);
            if($sid){
                $suc++;
                $daoSchool->initCache();
                $daoSchool->cacheSchoolDb($sid);
                $this->_addAdmin($sid,$domain);//添加超管
            }else{
                $fail++;
                $msg .= $line.' 写入失败 '.$v[0].'<br/>';
            }
        }
        @unlink($filePath);
        $this->success('导入完毕<br/><span class="cGreen">成功：'.$suc.'条 </span><span class="cRed">失败：'.$fail.'条<br/>'.$msg.'</span>');
    }
    //添加超管
    private function _addAdmin($sid,$domain) {
        $data['email'] = $domain.'@'.$domain.'.com';
        $data['password'] = codePass($domain.'admin');
        $data['uname'] = $domain.'超管';
        $data['realname'] = $domain.'超管';
        $data['sex'] = 1;
        $data['is_active'] = 1;
        $data['is_init'] = 0;
        $data['is_valid'] = 1;
        $data['ctime'] = time();
        $data['sid'] = $sid;
        $data['event_level'] = 10;
        $data['can_admin'] = 1;
        $data['can_gift'] = 1;
        $data['can_gift'] = 1;
        $data['can_event2'] = 1;
        $data['can_add_event'] = 1;
        $data['can_print'] = 1;
        $data['can_group'] = 1;
        $data['can_announce'] = 1;
        return M('user')->add($data);
    }
    private function _getProvId($title) {
        //去掉“省”
        $title = str_replace('省', '', $title);
        if(!$title){
            return 0;
        }
        $dao = M('province');
        $hasId = $dao->getField('id',"title='$title'");
        if($hasId){
            return $hasId;
        }
        $data['title'] = $title;
        $data['short'] = pinyin($title);
        $insert = $dao->add($data);
        if($insert){
            return $insert;
        }
        return 0;
    }
    private function _getCityId($title,$pid) {
        $title = str_replace('市', '', $title);
        if(!$title){
            return 0;
        }
        $dao = M('citys');
        $hasId = $dao->getField('id',"pid=$pid and city='$title'");
        if($hasId){
            return $hasId;
        }
        $data['city'] = $title;
        $data['pid'] = $pid;
        $data['short'] = pinyin($title);
        $insert = $dao->add($data);
        if($insert){
            return $insert;
        }
        return 0;
    }
    //添加院系 上传文件检查
    public function checkAddYx(){
        $res = $this->_readExcel();
        $soll = array('学校','院系');
        $this->_checkFirstLine($soll,$res[0]);
        unset($res[0]);
        $suc = 0;
        $fail = 0;
        $msg = '';
        $excelYx = array();
        foreach ($res as $k=>$v){//检查学校是否存在，院系
            if(!$v[0]&&!$v[1]){//空行不管继续
                continue;
            }
            $line = $k+1;
            $lineEmpty = '';
            foreach ($soll as $x => $y) {
                if(!$v[$x]){
                    $lineEmpty .= $y.'为空 ';
                }
            }
            if($lineEmpty!=''){
                $fail++;
                $msg .= "第$line 行 $lineEmpty <br/>";
                continue;
            }
            $sname = t($v[0]);
            $sid = $this->_hasSchool($sname);
            if(!$sid){
                $fail++;
                $msg .= "第$line 行 $sname 学校尚未添加 <br/>";
                continue;
            }
            $yx = t($v[1]);
            if($this->_hasYx($yx,$sid)){
                $fail++;
                $msg .= "第$line 行 $yx 院系已存在 <br/>";
                continue;
            }
            if(isset($excelYx[$sid][$yx])){
                $fail++;
                $msg .= "第$line 行和第".$excelYx[$sid][$yx]." 行 $yx 院系重复<br/>";
                continue;
            }
            $excelYx[$sid][$yx] = $line;
            $suc++;
        }
        $this->_endCheck($msg, $suc,$fail);
    }
    //导入院系 发放
    public function doAddYx(){
        $filePath = $_POST['filePath'];
        $dao = service('Excel');
        $res = $dao->read($filePath);
        if(empty($res)){
            $this->error('文档无内容');
        }
        unset($res[0]);
        $daoSchool = model('Schools');
        $suc = 0;
        $fail = 0;
        $msg = '';
        foreach ($res as $k=>$v){
            if(!$v[0]&&!$v[1]){
                continue;
            }
            $line = $k+1;
            $data['pid'] = $this->_hasSchool(t($v[0]));
            $title = t($v[1]);
            $data['title'] = $title;
            $data['display_order'] = pinyin($title);
            $data['domain'] = '';
            $data['email'] = '';
            $data['cityId'] = 0;
            $data['tj_year'] = 4;
            $data['module'] = '';
            $sid = $daoSchool->add($data);
            if($sid){
                $suc++;
                $daoSchool->initCache();
            }else{
                $fail++;
                $msg .= $line.' 写入失败 '.$v[0].'<br/>';
            }
        }
        @unlink($filePath);
        $this->success('导入完毕<br/><span class="cGreen">成功：'.$suc.'条 </span><span class="cRed">失败：'.$fail.'条<br/>'.$msg.'</span>');
    }

//	public function update()
//	{
//		$service = service('System');
//		$current_version = $service->getSystemVersion();
//		$lastest_version = $service->checkUpdate();
//
//		// 兼容ThinkSNS 2.1 Build 10992的版本号
//		foreach ($current_version as $k => $v)
//			if ($v <= 0)
//				$current_version[$k] = '10992';
//
//		// 自动升级程序仅支持ThinkSNS 2.1 Final(10920或10992)及以上版本
//		$system_version = model('Xdata')->get('siteopt:site_system_version');
//		$this->assign('system_version', ($system_version == '10920' || $system_version == '10992')
//										? 'ThinkSNS 2.1 Final Build '.$system_version
//										: $system_version);
//
//		$this->assign('is_support',     ($system_version == '10920' || $system_version == '10992' || $current_version['core'] >= 10992));
//		$this->assign('current_version', $current_version);
//		$this->assign('lastest_version', $lastest_version);
//		$this->display();
//	}
//
//	public function doUpdate()
//	{
//		$_GET['app_name'] = strtolower($_GET['app_name']);
//		$apps = model('App')->getAllApp('app_name');
//		$apps = getSubByKey($apps, 'app_name');
//		$apps[] = 'core';
//		if (!in_array($_GET['app_name'], $apps))
//			$this->error('参数错误');
//
//		$lastest_version = service('System')->checkUpdate();
//		if ($lastest_version['error'])
//			$this->error($lastest_version['error_message']);
//
//		$lastest_version = $lastest_version[$_GET['app_name']];
//		if (empty($lastest_version))
//			$this->error('应用不存在');
//		if ($lastest_version['error'])
//			$this->error($lastest_version['error_message']);
//		if (!$lastest_version['has_update'])
//			$this->error($_GET['app_name'] . '已经为最新版本');
//
//		// 升级的SQL文件 (必须)
//		// 每个版本必须附带数据升级文件, 并命名为: appname_versionNO.sql, 如: blog_14000.sql/core_14000.sql
//		// core的升级文件位于/update/目录
//		// app的升级文件位于/apps/app_name/Appinfo/目录
//		$sql_files = array();
//		foreach ($lastest_version['version_number_list'] as $version_no) {
//			if ($lastest_version['current_version_number'] >= $version_no)
//				continue ;
//
//			if ($_GET['app_name'] == 'core')
//				$path = '/update/core_' . $version_no . '.sql';
//			else
//				$path = "/apps/{$_GET['app_name']}/Appinfo/{$_GET['app_name']}_{$version_no}.sql";
//
//			if (!is_file(SITE_PATH . $path))
//				$this->error("{$path} 不存在");
//			else
//				$sql_files[] = SITE_PATH . $path;
//		}
//
//		// 升级的脚本文件 (可选)
//		$before_update_script = '';
//		$after_update_script  = '';
//		if ($_GET['app_name'] == 'core') {
//			$before_update_script = SITE_PATH . '/update/before_update_db.php';
//			$after_update_script  = SITE_PATH . '/update/after_update_db.php';
//		} else {
//			$before_update_script = SITE_PATH . "/apps/{$_GET['app_name']}/Appinfo/before_update_db.php";
//			$after_update_script  = SITE_PATH . "/apps/{$_GET['app_name']}/Appinfo/after_update_db.php";
//		}
//
//		// 执行SQL文件和脚本文件 (TODO: 数据库执行错误时的回滚)
//		if (is_file($before_update_script))
//			include_once $before_update_script;
//		foreach ($sql_files as $file) {
//			$res = M('')->executeSqlFile($file);
//			if (!empty($res))
//				$this->error("SQL错误: {$res['error_code']}");
//		}
//		if (is_file($after_update_script))
//			include_once $after_update_script;
//
//		// 升级完成, 更新版本名称和版本号
//		$dao = model('Xdata');
//		if ($_GET['app_name'] == 'core') {
//			$data['site_system_version'] 		= $lastest_version['lastest_version'];
//			$data['site_system_version_number'] = $lastest_version['lastest_version_number'];
//			$dao->lput('siteopt', $data);
//		} else {
//			$dao->put("{$_GET['app_name']}:version_number", $lastest_version['lastest_version_number'], true);
//		}
//
//		service('System')->unsetUpdateCache();
//
//		$this->success('升级成功');
//	}
}