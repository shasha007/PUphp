<?php
/**
 * 岗位
 */
class PositionAction extends Action{
    private $eid;
   

    //put posts
    public function putPosts(){
            $eid = $this->eid;
            if(!$eid){
                $this->error(L('请先登录'));
            }
            //判断企业用户是否激活
            $is_activation = M('JobEnterpriseInfo')->getField('is_activation', $condition='eid='.$eid);
             if($is_activation != 2){
                 $this->error(L('你还未通过审核'));
             }

            
            if($_POST['number'] == '' ){
                  $this->error(L('请填写招聘人数'));
            }else{
                $mod = "/\d+/";
                if(!preg_match($mod,$_POST['number'])){
                    $this->error(L('请填写正确的人数'));
                }else{
                    if($_POST['number'] <= 0){
                        $this->error(L('人数不能小于等于0'));
                    }
                }
            }
            
            if($_POST['salary'] == '' ){
                  $this->error(L('请填写薪资'));
            }
            
            $endtime = $_POST['endtime'];
            if(strtotime($endtime) - time() < 0){
                $this->error(L('结束时间不能小于现在时间'));
            }
            
            $data['eid'] = $eid;        
            $data['positionname'] = $_POST['positionname'];
            $data['positionclass'] = $_POST['positionclass'];
            $data['number'] = $_POST['number'];
            $data['workarea'] = $_POST['workarea'];      
//            $data['putschool'] = $_POST['putschool'];
            $data['endtime'] = strtotime($endtime);
            $data['positiontype'] = $_POST['positiontype'];
            $data['salary'] = $_POST['salary'];
            $data['education'] = $_POST['education'];     
            $data['sex'] = $_POST['sex'];
            $data['jobdescripition'] = $_POST['jobdescripition'];      
            $data['ctime'] = time();     
            $res = M('JobPublishPosts')->add($data);
            if(!res){
                 $this->error(L('发布失败'));
            }
            //
    }
    public function putPosts_new(){
         $eid = $this->eid;
         if(!$eid){
                $this->error(L('请先登录'));
         }
    }

    //search posts
    public function searchPosts(){
         $map['state'] = 1;//发布中的招聘     
           $map['endtime'] = array('gt',time());//未过期
          /*关键词搜索*/
           if($_REQUEST['key']){
               $keyword = h($_REQUEST['keyword']);
               if($_REQUEST['key'] == 'company' && $keyword != ''){
                  $map['e.fullname'] =  array('like',"%{$keyword}%");
              }else if($_REQUEST['key'] == 'positionname' && $keyword != ''){
                   $map['p.positionname'] =  array('like',"%{$keyword}%");
              }
           }
          // 行业
           if($_REQUEST['industry']){
               $industry = t($_REQUEST['industry']);
               //判断是否是数组   
               if(!strpos($industry,'+')){
               $industry_id = $industry;
                $where['e.industrycategory_a']  = $industry_id;
                $where['e.industrycategory_b']  = $industry_id;
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
               }else{
                   $industryArr = explode('+', $industry);
                    $in_data = array();
                   foreach ($industryArr as $val) {
                       $in_data[] = $val;
                }
                $where['e.industrycategory_a']  = array('in',$in_data);
                $where['e.industrycategory_b']  = array('in',$in_data);
                $where['_logic'] = 'or';
                $map['_complex'] = $where;
               }
               
           }
           
           //职能
           if($_POST['function']){
               $function = t($_REQUEST['function']);
               //判断是否是数组   
               if(!strpos($function,'+')){
               $function_id = $function;
               $map['f.function_id'] = $function_id;
               }else{
                   $functionArr = explode('+', $function);
                    $fu_data = array();
                   foreach ($functionArr as $val) {
                       $fu_data[] =$val;
                   }
                   $map['f.function_id'] = array('in',$fu_data);
               }
           }
           //地区
           if($_REQUEST['city']){
               $city = h($_REQUEST['city']);
               //判断是否是数组
               if(!strpos($city,'+')){

                   //查看是市还是区
                   $result = M('HatCity') ->where('cityID='.$city)->select();//返回true则表示属于市
                   if($result){
                   //获取此市有哪些区
                     $area = M('HatArea')->field('areaID')->where('father='.$city)->select();
                     $areaArr = array();
                     foreach ($area as $value) {
                         $areaArr[] = $value['areaID'];
                     }
                   $areaArr[] = $city;
                   $map['p.workarea']  = array('in',$areaArr);
                   }else{
                       $map['p.workarea'] = $city;
                   }
               }else{
                   $cityArr = explode('+', $city);
                   $areaArr = array();
                   foreach ($cityArr as $val) {
                       $result = M('HatCity') ->where('cityID='.$val)->select();//返回true则表示属于市
                       if($result){
                         //获取此市有哪些区
                         $area = M('HatArea')->field('areaID')->where('father='.$val)->select();
                          foreach ($area as $value) {
                          $areaArr[] = $value['areaID'];
                          }
                       }
                   }
                   $arr =  array_merge($cityArr,$areaArr);
                   $map['p.workarea']  = array('in',$arr);
               }
             
           }
           
           //月薪 salary
           if($_REQUEST['salary'] && $_REQUEST['salary'] != 0){
               $map['p.salary'] = $_REQUEST['salary'];
           }
           //工作性质 positiontype
           if($_REQUEST['worktype'] && $_REQUEST['worktype'] != 0){
               $map['p.worktype'] = $_REQUEST['worktype'];
           }
           //发布时间 ctime
           if($_REQUEST['time'] && $_REQUEST['time'] != 0){
                $time = $_REQUEST['time'];
                switch ($time){
                    case 1:
                        $map['p.ctime'] = array('gt',strtotime(date('Y-m-d')));//今天
                        breeak;
                    case 2:
                        $map['p.ctime'] = array('gt',strtotime('-2 day',time()));//两天内
                        breeak;
                    case 3:
                         $map['p.ctime'] = array('gt',strtotime('-7 day',time()));//一周内
                        breeak;
                    case 4:
                         $map['p.ctime'] = array('gt',strtotime('-30 day',time()));//一月内
                        breeak;
                }

           }

           //月薪范围数组
           $salary = D('JobPublishPosts')->monthlySalary();
           //最低学历数组
           $education = D('JobPublishPosts')->getEducation();
           //工作性质数组
           $worktype = D('JobPublishPosts')->positiontype();
           //发布时间数组
           $time = D('JobPublishPosts')->releaseTime();
           
           $list = D('jobPublishPosts')->jobList($map,10);
           $url = '';
           //保存post条件
           foreach ($_POST as $key=>$value) {
               if(!empty($value) && $key != '__hash__'){
                   $url .= "&$key=$value";
               }
           }
           
           if(!empty($_GET)){
               foreach ($_GET as $key=>$value) {
                   $url .= "&$key=$value";
               }
           }
           
           $this->assign('url',$url);
           $this->assign('salary',$salary);       
           $this->assign('education',$education);
           $this->assign('worktype',$worktype);  
           $this->assign('time',$time);        

           //输出招聘信息
           $this->assign('list',$list);
           
           echo '<pre>';
           print_r($list);
           echo '</pre>';
           
           $this->display('post');
    }
    
    //before end
    public function beforeEnd(){
            $map['postid'] = $_GET['postid'];
            $info = D('JobPublishPosts')->jobDetail($map);
            if($info['state'] == 2){
                $this->error(L('招聘已结束'));
            }
            $data['state'] = 2;
            $res = M('JobPublishPosts')->where($map)->save($data);
    }
    
        //修改时间
        public function updatetime(){
            $data['time'] = $_POST['time'];    
            $postid = $_POST['postid'];
            if($time <= time()){
                $this->error(L('结束时间不能小于现在时间'));
            }
            $res = M('JobPublishPosts')->where('postid='.$postid)->save($data);
            if($res){
                $this->success(L('修改成功'));
            }else{
                $this->error(L('修改失败'));
            }
        }
        
        //重新发布
        public function reRelease(){
            $postid = t($_GET['postid']);
            $info = D('JobPublishPosts')->jobDetail($postid);
            unset($info['postid']);
            if($info['state'] == 3){
                 $this->error(L('已被管理员停止'));
            }
            //修改创建时间
            $info['ctime'] = time();
            //修改结束时间
            $endtime = $_POST['endtime'];
            if($endtime <= time()){
                $this->error(L('结束时间不能小于现在时间'));
            }
            //如果发布中 或者完结 重新发布不用审核 其余情况就要审核
            if($info['state'] == 1 && $info['state'] == 2){
                $info['state'] = 1;
            }else{
                $info['state'] == 0;
            }
 
        }
        
       //收藏职位
       public function collectPosition(){
           $map['uid'] = $this->mid;
           $map['postid'] = t($_GET['postid']);
           $map['ctime'] = time();
           $res = D('JobPositionCollection')->collectionPosition($map);
           if($res == 4){
                $this->success(L('收藏成功'));
           }else{
                $message = D('JobPositionCollection')->getError();
                $this->error(L($message));
           }
           
       }
       
       //取消收藏职位
       public function cancelCollectionPosition(){
           $map['uid'] = $this->mid;
           $map['postid'] = t($_GET['postid']);
           $res = D('JobPositionCollection')->cancelCollectionPosition($map);
           if($res){
               $this->success(L('取消收藏成功'));
           }else{
               $this->error(L('取消收藏失败'));
           }
       }
       
       //申请职位
       public function applyPosition(){
           $postid = t($_GET['postid']);
           $uid = $this->mid;
           $map['postid'] = $postid;          
           $map['uid'] = $uid;       
           $map['ctime'] = time();
           $res = D('JobApplication')->applyPosition($map);
           if($res == 4){
               $this->success(L('申请成功'));
           }else{
               $message = D('JobApplication')->getError();
               $this->error(L($message));
           }
           
       }
       
        //读取招聘信息
       public function job_post(){
           $postid = t($_GET['postid']);
           //招聘信息
           $map['postid'] = $postid;
           $pInfo = D('JobPublishPosts')->jobDetail($map);
           unset($map);
           if(!$pInfo){
               $this->error(L('招聘信息不存在'));
           }
           $this->assign('pInfo',$pInfo);
           //公司信息
           $eid = $pInfo['eid'];
           $cInfo = D('JobEnterpriseInfo')->getEnterpriseInfo($eid );
           $this->assign('cInfo',$cInfo);
           
           //相似职位
           $likename = $info['positionname'];
           $map['positionname'] = array('like',"%{$likename}%");
           $map['postid'] = array('not in',$postid);
           $likepost = D('JobPublishPosts')->jobList($map);
           $this->display();
       }

       
        //职位管理——职位列表
        public function jobList(){
            $eid = $this->eid;
            $map['e.eid'] = $eid;

            $list = D('JobPublishPosts')->jobList($map,$limit=10);
            if($list){
                foreach ($list['data'] as &$value) {
                    $value['number'] = D('JobApplication')->applynumber($value['postid']);
                }
            }
            echo '<pre>';
            print_r($list);
            echo '</pre>';
//            $this->assign()
        }
        
       /**
        * 所有推荐企业最新招聘列表
        */
       public function recommendCompany_post(){
            $choice = $_GET['choice'] ? $_GET['choice']:'company';
//          $url = '';
            if($choice == 'government'){
                $type['type'] = 1;
            }else{
                $type['type'] = 0;
            }
            
            $postList = D('JobPositionCollection')->newJob($type,10);

       }
       

}
?>
