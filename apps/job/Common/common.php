<?php
    
    //url正则替换
    function url_replace($url,$param){
      return     preg_replace("/&{$param}=\d/is", "", $url);
    }
    //行业名
    function getsIndustryName($id){
        $name = M('JobIndustry')->getField('industryname','industry_id='.$id);
        if($name){
            return $name;
        }else{
            return '';
        }
    }
    
    //地点名
    function getAreaName($address){
        if(M('HatCity')->where('cityId='.$address)->select()){
            $name = M('HatCity')->getField('city','cityId='.$address);
        }elseif(M('HatArea')->where('areaId='.$address)->select()){
            $pid = M('HatArea')->getField('father','areaId='.$address);
            $pname = M('HatCity')->getField('city','cityId='.$pid);
            $name = M('HatArea')->getField('area','areaId='.$address);
            $name = $pname.'-'.$name;
        }else{
            $name = '未知';
        }
        return $name;
    }
    
    //薪资
      function monthlySalary($id){
          $salary = array('不限','2K以下','2K-5K','5K-10K','10K-15K','15K-25K','25K-50K','50K以上');
          if(isset($id)){
              return $salary[$id];
          }else{
             return $salary;
          }

      }
      
       //学历
       function getEducation($id){
          $education = array('不限','大专','本科','硕士','博士');
          if(isset($id)){
              return $education[$id];
          }else{
             return $education;
          }

      }
      
       //兼职 全职 实习
       function positiontype($id){
          $positiontype = array('不限','兼职','全职','实习');
          if(isset($id)){
              return $positiontype[$id];
          }else{
             return $positiontype;
          }

      }
      
     //发布时间
      function releaseTime($id){
          $time = array('不限','今天','3天内','一周内','一月内');
          if(isset($id)){
              return $time[$id];
          }else{
             return $time;
          }

      }
      
      //发布状态
      function getState($id){
          $state = array('审核中','发布中','已结束','停止');
          if(isset($id)){
              return $state[$id];
          }else{
             return $state;
          }

      }
      
      //企业规模
      function scale($id){
          $scale = array(1=>'20人以下','20-99人','100-499人','500-999人','1000-9999人','10000人以上');
          if(isset($id)){
              return $scale[$id];
          }else{
             return $scale;
          }

      }

      //公司类型
     function type($id){
          $type = array(1=>'国有企业','集体企业','股份合作企业','联营企业','有限责任公司', '股份有限公司','私营企业','其他企业');
          if(isset($id)){
              return $type[$id];
          }else{
              return $type;
          }

      }
      
      function getjobSex($id){
          $sex = array('不限','男','女');
          if(isset($id)){
              return $sex[$id];
          }else{
              return $sex;
          }

      }

      
      
    
?>