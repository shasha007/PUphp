<?php
/**
 * 学生
 * */
    class IndexAction extends Action{

       public function _initialize(){

       }

    /**
    * 学生进入招聘首页
    */
    public function index(){
        $choice = $_GET['choice'] ? $_GET['choice']:'government';
        if($choice == 'government'){
            $map['is_gover'] = 1;
            $maps['p.is_gover'] = 1;

        }else{
            $map['is_gover'] = 0;
            $result = D('JobRecommendcompany')->getRecommendEid(true);
            if($result){
            $map['eid'] = array('in',$result);
              }
            $maps['p.is_gover'] = 0;
        }
        //企业列表
        $company = D('JobEnterpriseInfo')->EnterpriseList($map,6);
        $company = $company['data'];
        $this->assign('company',$company);

        //职位列表 {显示在本学校的}
        
       $posts = D('JobPublishPosts')->jobList($maps,4);
        $posts = $posts['data'];
        $this->assign('posts',$posts);
        $this->display('index');
    }

       //关注公司 改成AJAX
       public function attentionCompany(){
           $eid = t($_GET['eid']);
           $map['eid'] = $eid;
           $map['uid'] = $this->mid;
           $map['ctime'] = time();

           $info = D('JobAttention')->attentionCompany($map);
           if($info == 3){
               $this->success(L('关注成功'));
           }else{
               $message = D('JobAttention')->getError();
               $this->error(L($message));
           }
       }

       //取消关注公司 改成AJAX
       public function cancelAttentionCompany(){
           $map['uid'] = $this->mid;
           $map['eid'] = t($_REQUEST['eid']);
           $res = D('JobAttention')->cancelAttentionCompany($map);

           if($res){
               $this->success(L('取消关注成功'));
           }else{
               $this->error(L('取消关注失败'));
           }
       }

       //招聘举报
       public function recruitmentReport(){
           $postid = t($_GET['postid']);
           $type = t($_GET['type']);
           $reason = h($_GET['reason']);
           if($reason == ''){
                $this->error(L('请填写举报原因'));
           }
           $data['postid'] = $postid;
           $data['type'] = $type;
           $data['$reason'] = $reason;
           $res = D('JobReport')->report($data);
           if($res == 1){
               $this->success(L('举报成功'));
           }else{
               $message = D('JobReport')->getError();
               $this->error(L($message));
           }
       }
       
       /**
        * 查看更多企业
        */
       public function clist(){
           $this->display();
       }
       

    }
?>
