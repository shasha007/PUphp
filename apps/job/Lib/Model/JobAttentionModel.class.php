<?php
   class JobAttentionModel extends Model{
        
       //关注公司
       public function attentionCompany($map){
           $result = 0;//该公司不存在
           $this->error = '该公司不存在';
           $maps['eid'] = $map['eid'];
           $res = D('JobEnterpriseInfo')->getEnterpriseInfo($maps);
           unset($maps);
           if(!$res){
               return $result;
           }else{
               if($res['is_activation'] == 0){
                   $result = 1;//该公司未激活
                    $this->error = '该公司未激活';
                   return $result;
               }
               
               //判断是否已经关注
               $maps['uid'] = $map['uid'];       
               $maps['eid'] = $map['eid'];
               if($this->where($maps)->find()){
                   $result = 2;//已经关注
                   $this->error = '已经关注';
                   return $result;
               }else{
                   //关注操作
                   $result = $this->add($map);
                   if($result){
                       $result = 3;//关注成功
                   }else{
                       $result = 4;//关注失败
                       $this->error = '关注失败';
                   }
                   return $result;
               }

           }
           
       }
       
       //取消关注公司
       public function cancelAttentionCompany($map){
          $res = $this->where($map)->delete();
          if($res){
              return true;
          }else{
              return false;
          }
       }
    }
?>