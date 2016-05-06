<?php

class SchoolWebModel extends Model {

    public function getConfigDb($sid){
        $obj = $this->where('sid='.$sid)->find();
        if(!$obj){
            $schoolName = tsGetSchoolName($sid);
            $initData['sid'] = $sid;
            $initData['title'] = $schoolName."大学生实践成才服务平台";
            $initData['path'] = '';
            $initData['cTime'] = time();
            $initData['print_title'] = $schoolName."大学生实践证书";
            $initData['print_content'] = '';
            $initData['print_day'] = '7';
            $initData['print_address'] = '';
            $initData['cxjg'] = '3';
            $initData['cxjy'] = '5';
            $initData['cxday'] = '7';
            $initData['cradit_name'] = "实践学分";
            $initData['max_credit'] = 10.00;
            $initData['max_score'] = 10;
            $initData['gift_address'] = '';
            $this->add($initData);
            return $initData;
        }
        return $obj;
    }

    public function getConfigCache($sid){
        $config = S('S_config_'.$sid);
        if(empty($config)) {
            $config = $this->getConfigDb($sid);
            S('S_config_'.$sid, $config);
        }
        return $config;
    }

    public function editSchoolWeb($data){
        $res = $this->save($data);
        if($res){
            S('S_config_'.$data['sid'], null);
            return true;
        }
        return false;;
    }

}

?>