<?php
    
  class JobPositionCollectionModel extends Model{
      
      //收藏列表
      public function collectionList($map,$limit=10,$order='eid'){
         $res =  $this->where($map)->field('uid,postid,ctime')->order($order)->findPage($limit);
         if($res){
             return $res;
         }else{
             return false;
         }
      }
      
      //收藏职位
      public function collectionPosition($map){
           //判断职位是否删除 或者到期
           $maps['postid'] = $map['postid'];      
           $maps['isDel'] = 0;
        
           $info = D('JobPublishPosts')->jobDetail($maps);
           unset($maps);
           if($info){
               
               if($info['state'] == 0){
                    $result = 1;
                    $this->error = '职位在审核';
                    return $result;
               }
               if($info['state'] == 2){
                    $result = 2;
                    $this->error = '职位已过期';
                    return $result;
               }
               
                    //判断是否收藏
                $maps['uid'] = $map['uid'];
                $maps['postid'] = $map['postid'];
                $result = $this->collectionList($maps);
                if($result){
                    $result = 3;
                    $this->error = '该职位已收藏';
                    return $result;
                }else{
                    $res = $this->add($map);
                    if($res){
                       $result = 4;
                    }else{
                          $result = 5;
                         $this->error = '收藏失败';
                    }
                    return $result;
                }
               
           }else{
                $result = 0;
                $this->error = '职位不存在';
                return $result;
           }

      }
      
      //取消收藏职位
      public function cancelCollectionPosition($map){
          $res = $this->where($map)->delete();
          if($res){
              return true;
          }else{
              return false;
          }
      }
      


  }

?>
