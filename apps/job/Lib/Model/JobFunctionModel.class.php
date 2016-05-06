<?php
    
  class JobFunctionModel extends Model{
      
      //所有行业列表
      public function functionList(){
          $map['function_pid'] = 0;
          $list = $this->where($map)->findAll();
          if($list){
              foreach($list as &$val){
                   $child = $this->where('function_pid ='.$val['function_id'])->findAll();
                   foreach ($child as &$value) {
                        $res = $this->where('function_pid ='.$val['function_id'])->findAll();
                        $value['list'] = array();
                        if($res){
                           $value['list'] = $res; 
                         }
                        }
                   $val['child'] = $child;
                  }
                  return $list;
          }else{
              return false;
          }
      }
      
  }

?>
