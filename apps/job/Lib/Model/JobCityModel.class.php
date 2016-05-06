<?php
    /**
     * 省 城市 区数据表结构改变 此文件不用
     */
  class JobCityModel extends Model{
      
      //获取城市地区列表
      public function getCityList($map){
          //市列表
          $map['citypid'] = 0;
          $info = $this->where($map)->findAll();
          unset($pid);
          
          if($info){
              foreach ($info as &$val) {
                  $childList = $this->where('citypid='.$val['cityid']) ->select();
                  if($childList){
                      $val['district'] = $childList;
                  }else{
                      $val['district'] = array();
                  }
              }
              return $info;
          }else{
              return false;
          }
      }
      
      //某一地区 判断是地级市 还是区
      public function cityDetail($cityid){
          $cityname = '';
          //判断是市还是区
          $res = $this->where('cityid='.$cityid)->find();
          if($res['citypid'] == 0){
              $cityname = $res['cityname'];
          }else{
              $pname = $this->getField('cityname','cityid='.$res['citypid']);
              $cityname = $pname.'-'.$res['cityname'];
          }
          return $cityname;
      }
  }

?>
