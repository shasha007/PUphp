<?php
    class JobApplicationModel extends Model{
        
        //申请职位
        public function applyPosition($map){
            $result = 0;//职位不存在或已删除
            $this->error = '职位不存在或已删除';
            $maps['postid'] = $map['postid'];         
            $maps['isDel'] = 0;

            $res = D('JobPublishPosts')->jobDetail($maps);
            unset($maps);
            if($res){
                if($res['state'] == 0){
                    $result = 1;//职位还在审核
                    $this->error = '职位还在审核';
                    return $result;
                    
                }
                 if($res['state'] == 2){
                    $result = 2;//职位发布结束
                    $this->error = '职位发布结束';
                    return $result;
                    
                }
                
               //申请职位操作
                $maps['uid'] = $map['uid'];                
                $maps['postid'] = $map['postid'];
               if(M('JobApplication')->where($maps)->select()){
                   $result = 3;//已经申请
                   $this->error = '已经申请';
                   return $result;
               }
               $result = $this->add($map);
               if($result){
                   $result = 4;//申请成功 
               }else{
                   $result =5;//申请失败
                   $this->error = '申请失败';
               }
               return $result;
            }
            return $result;
        }
        
        //用户申请列表
        public function applyList($map,$limit=10,$order='application_id'){
            $offset = ($page-1) * $limit;
           $res = $this->where($map)->field('application_id,uid,postid,state,ctime')->order($order)->findPage($limit);
           if($res){
               return $res;
           }else{
               return false;
           }
        }
        
            //单个招聘信息申请人数
          public function applynumber($postid){
            $map['postid'] = $postid;
            $res = $this->where($map)->count();
            if($res){
                return $res;
              }else{
                return 0;
             }
      }
    }
?>