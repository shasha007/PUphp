<?php
    
  class JobIndustryModel extends Model{
      
      //行业列表 
      public function industryList(){
          $map['industry_pid'] = 0;
          $res = $this->where($map)->findAll();
          if($res){
              foreach($res as &$val){
                  $child = $this->where('industry_pid ='.$val['industry_id'])->findAll();
                  $val['child'] = array();
                  if($child){
                  $val['child'] = $child;
                   }
                  }
              return $res;
          }else{
              return false;
          }
      }
      

      
  }

?>
