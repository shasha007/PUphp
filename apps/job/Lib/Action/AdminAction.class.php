<?php

import('home.Action.PubackAction');

class AdminAction extends PubackAction {

    public function _initialize() {
        parent::_initialize();

    }

    //企业注册待审核 is_activation 0 待审核 1通过 (2未通过)
    public function companycheck(){
        $map['is_activation'] = 1;//待审核
        $list = D('JobEnterpriseInfo')->EnterpriseList($map,10);
        $state = array('未通过','已通过');
        $this->assign('state',$state);
        $this->assign('data',$list);
        $this->display();
    }

    //企业审核--AJAX
    public function companyexamineAjax(){
        $type = t($_POST['type']);
        $eid = t($_POST['eid']);

        if($type == 1){
            $data['is_activation'] = 2;//通过审核
            if(M('JobEnterpriseInfo')->where('eid='.$eid)->save($data)){
               echo 1;
            }else{
              echo 0;
             }
        }else{
             $data['is_activation'] = 2;//未通过审核
              if(M('JobEnterpriseInfo')->where('eid='.$eid)->save($data)){
               echo 1;
            }else{
              echo 0;
             }
        }
    }


    //查看公司注册申请详情
    public function  registrationDetail(){
        $eid = t($_GET['eid']);
        $info = D('JobEnterpriseInfo')->getEnterpriseInfo($eid);
        $ename = M('JobEnterprise')->getField('ename','eid='.$eid);
        $info['ename'] = $ename;
        $this->assign('data',$info);
        $this->display('regcheck');
    }

    //企业管理 所有企业列表
    public function index(){
        $list = D('JobEnterpriseInfo')->EnterpriseList($map,10);
        $state = array('未通过','已通过');
        $this->assign('state',$state);
        $this->assign('data',$list);
        $this->display();
    }

     //招聘审核 列表
    public function postsexamine(){
        //state为 0的招聘信息
        $map['state'] = 0;
        $map['isDel'] = 0;
        $list = D('JobPublishPosts')->jobList($map,$limit=10);
        $this->assign('data',$list);
        $this->display();
    }

//    public function postexamineAjax(){
//         $postid = t($_REQUEST['postid']);
//         if($postid == 1){
//              $data['state'] = 1;
//            if(M('JobPublishPosts')->where('postid='.$postid)->save($data)){
//                echo 1;
//            }else{
//              echo 0;
//        }
//         }else{
//              $data['state'] = 3;
//            if(M('JobPublishPosts')->where('postid='.$postid)->save($data)){
//                echo 1;
//            }else{
//                echo 0;
//            }
//         }
//    }

    //招聘管理 所有招聘信息列表
    public function postslist(){
        if($_POST['postname']){
            $postname = h($_POST['postname']);
            $map['p.positionname'] = array('like',"%{$postname}%");
        }

        $list = D('JobPublishPosts')->jobList($map,10);
        $state =D('JobPublishPosts')->getState();//状态
        $this->assign('data',$list);
        $this->assign('state',$state);
        $this->display();
    }

    //招聘 通过 停止
    public function through_stop_ajax(){
        $postid = t($_POST['postid']);
        $type = t($_POST['type']);
        $map['postid'] = $postid;
        //获取招聘信息
        $info = D('JobPublishPosts')->jobDetail(array('postid'=>$postid));
        if($type == 1){
         //通过
          $data['state'] = 1;
          $res = M('JobPublishPosts')->where($map)->save($data);
          if($res){
              if($info['endtime'] <= time()){
                  echo 1;//已经结束
              }else{
                  echo 2;
              }
          }else{
              echo 0;//操作失败
          }
        }else{
            //停止
           $data['state'] = 3;
           $res = M('JobPublishPosts')->where($map)->save($data);
           if($res){
               echo 3;//停止成功
           }else{
               echo 0;//操作失败
           }
        }

    }

    //招聘举报列表
    public function reportList(){
        $list = D('JobReport')->reportList();
        echo '<pre>';
        print_r($list);
        echo '</pre>';
        $this->display('report');
    }

    /**
     * 政府操作
     */
    public function gover(){
        $cid = $_GET['cid'] ? $_GET['cid'] : 'addcompany';
        if($cid == 'addcompany'){
            $map['provinceId'] = 320000;
            $res = D('HatProvince')->provinceList($map,false);
            $city = array();
            if($res){
                $city = $res[0]['city'];
            }
            $this->assign('city', $city);
            $scale = scale();
            $this->assign('scale',$scale);
            $indus =D('JobIndustry')->industryList();
            $this->assign('indus',$indus);
            $type = type();
            $this->assign('type',$type);
        }else if($cid == 'addpost'){
            //政府添加的公司
            $eids = M('JobEnterpriseInfo')->where('is_gover=1')->field('eid,fullname')->select();
            $this->assign('eids', $eids);
            $map['provinceId'] = 320000;
            $workarea = D('HatProvince')->provinceList($map);
            $this->assign('workarea',$workarea[0]['city']);
            $ed = getEducation();
            $this->assign('ed', $ed);
            $salary = monthlySalary();
            unset($salary[0]);
            $this->assign('salary', $salary);
            //职位分类 function_a
            $f_a = M('JobFunctionA')->field('id,functionname')->select();
            $this->assign('f_a', $f_a);
        }else if($cid == 'addpolicy'){
            $map['provinceId'] = 320000;
            $citys = D('HatProvince')->provinceList($map,false);
            $this->assign('citys',$citys[0]['city']);
        }
        $inner = $this->fetch($cid);
        $this->assign('inner',$inner);
        $this->display();
    }
    /**
     * 政府推荐企业添加
     */
    public function goverAddCompany(){
//
//      if(!$_POST['fullname']){
//          $this->error(L('公司名名不能为空'));
//      }
//      if(!$_POST['imgid1']){
//          $this->error(L('请上传企业logo'));
//      }
//      if(!$_POST['type']){
//          $this->error(L('请选择公司类型'));
//      }
//      if(!$_POST['industrycategory_a']){
//          $this->error(L('请选择行业1'));
//      }
//      if(!$_POST['scale']){
//          $this->error(L('请选择公司规模'));
//      }
//      if(!$_POST['address']){
//          $this->error(L('请填写公司地址'));
//      }
//      if(!$_POST['homepage']){
//          $this->error(L('请填写公司主页'));
//      }
//      if(!$_POST['city']){
//          $this->error(L('请选择城市'));
//      }
//       if(!$_POST['contact']){
//          $this->error(L('请填写联系人'));
//      }
//       if(!$_POST['telephone_areacode']){
//          $this->error(L('请填写电话区号'));
//      }
//       if(!$_POST['telephone']){
//          $this->error(L('请填写电话号码'));
//      }
//       if(!$_POST['contact_email']){
//          $this->error(L('请填写联系邮箱'));
//      }
//       if(!$_POST['introduction']){
//          $this->error(L('请填写公司简介'));
//      }
//      if(!$_POST['founder_name']){
//          $this->error(L('请填写创始人名'));
//      }
//      if(!$_POST['founder_info']){
//          $this->error(L('请填写创始人信息'));
//      }
      $datas['ename'] = $_POST['fullname'];

      $data['fullname'] = $_POST['fullname'];
      $data['logo_pic'] = $_POST['imgid1'][0];
      $data['type'] = $_POST['type'];
      $data['industrycategory_a'] = $_POST['industrycategory_a'];
      $data['industrycategory_b'] = $_POST['industrycategory_b']?$_POST['industrycategory_b']:0;
      $data['scale'] = $_POST['scale'];
      $data['address'] = $_POST['address'];
      $data['homepage'] = $_POST['homepage'];
      $data['city'] = $_POST['city'];
      $data['contact'] = $_POST['contact'];
      $data['telephone_areacode'] = $_POST['telephone_areacode'];
      $data['telephone'] = $_POST['telephone'];
      $data['contact_email'] = $_POST['contact_email'] ? $_POST['contact_email']:'';
      $data['introduction'] = $_POST['introduction'];
      $data['founder_name'] = $_POST['founder_name']?$_POST['founder_name']:'';
      $data['founder_pic'] = $_POST['imgid2'][0]?$_POST['imgid2'][0]:0;
      $data['founder_info'] = $_POST['founder_info']?$_POST['founder_info']:'';
      $data['is_gover'] = 1;
      $data['is_activation'] = 2;//直接默认已经激活

      $res_a = M('JobEnterprise')->add($datas);

         if($res_a){
              $data['eid'] = $res_a;
              $res_b = M('JobEnterpriseInfo')->add($data);
              if($res_b){
                   $this->success(L('添加成功'));
              }else{
                  //删除 ename
                  M('JobEnterprise')->where('eid='.$res_a)->delete();
                  $this->error(L('添加失败'));
              }
         }else{
              $this->error(L('添加失败!'));
         }

    }

    /**
     * 添加职位
     */
    public function addPost(){
//        if(!$_POST['company']){
//            $this->error(L('请选择公司'));
//        }
//        if(!$_POST['positionname']){
//            $this->error(L('请填写职位名'));
//        }
//        if(!$_POST['positionclass']){
//            $this->error(L('请选择职位分类'));
//        }
//        if(!$_POST['number']){
//            $this->error(L('请填写人数'));
//        }
//        if(!$_POST['workarea']){
//            $this->error(L('请选择地区'));
//        }
//        if(!$_POST['showSids']){
//            $this->error(L('请选择学校'));
//        }
//        if(!$_POST['endtime']){
//            $this->error(L('请选择结束时间'));
//        }
//        if(!$_POST['salary']){
//            $this->error(L('请选择薪资范围'));
//        }
//        if(!$_POST['type']){
//            $this->error(L('请选择职位类型'));
//        }

        $data['eid'] = $_POST['company'];
        $data['positionname'] = $_POST['positionname'];
        $data['positionclass'] = $_POST['positionclass'];
        $data['number'] = t($_POST['number']);
        $data['workarea'] = t($_POST['workarea']);
        $data['endtime'] = $_POST['endtime'];
        $data['salary'] = $_POST['salary'];
        $data['positiontype'] = $_POST['type'];
        $data['education'] = $_POST['education'];
        $data['sex'] = $_POST['sex'];
        $data['jobdescripition'] = $_POST['jobdescripition'];
        $data['is_gover'] = 1;
        $data['state'] = 1;
        $data['ctime'] = time();
        $postid = M('JobPublishPosts')->add($data);
        if($postid){
            //绑定学校
            D('JobPostidSid')->PostidBySid($postid,rtrim($_POST['showSids'],','));
            $this->success(L('添加成功'));
        }else{
            $this->error(L('添加失败'));
        }
    }

    /**
     * function_b AJAX
     */
    public function function_b(){
        $id = t($_POST['id']);
        $res = M('JobFunctionB')->where('pid='.$id)->field('id,functionname')->select();
        if($res){
            echo json_encode($res);
        }else{
            echo 0;
        }
    }

     /**
     * function_c AJAX
     */
    public function function_c(){
        $id = t($_POST['id']);
        $res = M('JobFunctionC')->where('pid='.$id)->field('id,functionname')->select();
        if($res){
            echo json_encode($res);
        }else{
            echo 0;
        }
    }
    
    /***地方政策**/
    public function doaddpolicy(){
        if(!$_POST['city']){
            $this->error(L('请选择城市'));
        }
        if(!$_POST['title']){
            $this->error(L('请填写标题'));
        }
        if(!$_POST['city']){
            $this->error(L('请填写内容'));
        }
       if(!$_POST['author']){
            $this->error(L('请填写发布人'));
        }
        $data['area'] = $_POST['city'];      
        $data['title'] = $_POST['title'];
        $data['content'] = $_POST['content'];    
        $data['author'] = $_POST['author'];    
        $data['ctime'] = time();
        $res = M('JobLocalpolicy')->add($data);
        if($res){
             $this->success(L('添加成功'));
        }else{
              $this->error(L('添加失败'));
        }

    }
    public function test(){
//          D('JobPostidSid')->PostidBySid(654,'0,1,2,3');
//        echo rtrim('0,1,2,3,',',');

    }

}

?>
