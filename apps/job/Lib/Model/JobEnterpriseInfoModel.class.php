<?php

  class JobEnterpriseInfoModel extends Model{

     //企业列表
       public function EnterpriseList($map,$limit=10,$order='eid DESC'){
          $info = $this->where($map)->field('eid,type,industrycategory_a,industrycategory_b,scale,address,homepage,contact,city,telephone_areacode,telephone,logo_pic,contact_email,getresume_email,fullname,email_vcode,ctime,blicense,introduction,founder_name,founder_pic,founder_info')->order($order)->findPage($limit);
          if($info){
              foreach ($info['data'] as &$val) {
                  $val['ename'] = M('JobEnterprise')->getField('ename', 'eid='.$val['eid']);
                  $val['logo_pics'] ='';
                  $val['founder_pics'] ='';

                  if($val['logo_pic']){
                    $attach = getAttach($val['logo_pic']);
                    $file = $attach['savepath'] . $attach['savename'];
                    $val['logo_pics'] = tsMakeThumbUp($file, 60, 60, 'no');
                   }
                  if($val['founder_pic']){
                    $attach = getAttach($val['founder_pic']);
                    $file = $attach['savepath'] . $attach['savename'];
                    $val['founder_pics'] = tsMakeThumbUp($file, 60, 60, 'no');
                   }
                   if($val['blicense']){
                    $attach = getAttach($val['blicense']);
                    $file = $attach['savepath'] . $attach['savename'];
                    $val['blicense'] = tsMakeThumbUp($file, 60, 60, 'no');
                   }

                  if(!$val['is_gover']){
                       $count = M('JobAccount')->getField('account', 'eid='.$val['eid']);
                       $val['account'] = $count ? $count : 0;
                  }else{
                      $val['account'] = 0;
                  }
              }
              return $info;
          }else{
              return false;
          }
      }

      //获取企业信息
      public function getEnterpriseInfo($eid){
          $map['eid'] = $eid;
          $res = $this->EnterpriseList($map);
          if($res){
              return $res['data'][0];
          }else{
              return false;
          }
      }

  }

?>
