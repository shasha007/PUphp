<?php
    
  class JobPublishPostsModel extends Model{
      
      
      //招聘信息列表
      public function jobList($map,$limit=10,$order='p.postid DESC'){
          $db_prefix = C('DB_PREFIX');
          $res = $this->table("{$db_prefix}job_publish_posts as p")
                  ->join("{$db_prefix}job_function_c as f on f.id=p.positionclass")         
                  ->join("{$db_prefix}job_enterprise_info as e on e.eid=p.eid")
                  ->where($map)
                  ->field('p.postid,p.positionname as positionname,p.workarea as workarea,f.functionname as functionname,p.ctime as ctime,p.salary,p.positiontype,p.endtime as endtime,p.number as number,p.education as education,p.sex as sex,p.jobdescripition as jobdescrpition,p.state as state,e.fullname as fullname,e.eid as eid')     
                  ->order($order)
                  ->findPage($limit);
          if($res){
              foreach ($res['data'] as &$val) {
                  $val['ename'] = M('JobEnterprise')->getField('ename', 'eid='.$val['eid']);
                  $num = D('JobApplication')->applynumber($val['postid']);
                  $val['applynum'] = $num ? $num : 0;
              }
              return $res;
          }else{
              return false;
          }
      }


      //某一招聘信息详情
      public function jobDetail($map){
          $res = $this->jobList($map);
          if($res){
              return $res['data'][0];
          }else{
              return false;
          }
      }

  }

?>
