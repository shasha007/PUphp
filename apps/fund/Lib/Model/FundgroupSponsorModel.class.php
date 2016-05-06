<?php

/*
 * 投资基金企业
 *
 */
class FundgroupSponsorModel extends Model{
    //添加投资基金
    public function doAdd() {
        $data = array();
        $data['company'] = t($_POST['company']);
        $data['type'] = t($_POST['type']);
        $data['money'] = t($_POST['money']);
        $data['stuff'] = t($_POST['stuff']);
        $data['win'] = t($_POST['win']);
        $data['month'] = t($_POST['month']);
        $data['content'] = t(h($_POST['content']));
        $data['cTime'] = time();
        if(isset($_POST['file1'][0]) && $_POST['file1'][0]>0){
            $data['attachId'] = intval($_POST['file1'][0]);
        }
        $sponsorId = intval($_POST['id']);
        if($sponsorId>0){
            $this->where("id=$sponsorId")->save($data);
        }else{
            $sponsorId = $this->add($data);
        }
        if(!$sponsorId){
            $this->error = '写入失败';
            return FALSE;
        }
        D('FundgroupSponsorSchool')->addSchool($sponsorId);
        return true;
    }
    
}
