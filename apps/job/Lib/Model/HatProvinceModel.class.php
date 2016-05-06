<?php

    //全国省
    class HatProvinceModel extends Model{
        
       //省列表(包含所属市所属区)
        public function provinceList($map,$type=true){
            $res = $this->where($map)->order(' binary CONVERT(`order` USING GBK)')->findAll();
            unset($map);
            if($res){
                 foreach ($res as &$value) {
                     $map['father'] = $value['provinceID'];
                     $cityArr = M('HatCity')->where($map)->order(' binary CONVERT(`order` USING GBK)')->select();
                     unset($map);
                     //区
                     $value['city'] = array();
                     if($cityArr){
                        if($type){
                              foreach ($cityArr as &$val) {
                              $map['father'] = $val['cityID'];
                              $areaArr = M('HatArea')->where($map)->order(' binary CONVERT(`order` USING GBK)')->select();
                              $val['area'] = array();
                              if($areaArr){
                                  $val['area'] = $areaArr;
                              }
                         }
                        }
                         $value['city'] = $cityArr;
                     }
                    

                  }
            }
            return $res;
        }
    }
?>
