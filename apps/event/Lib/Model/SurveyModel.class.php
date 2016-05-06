<?php
class SurveyModel extends BaseModel
{
    public function _initialize(){
        parent::_initialize();
    }
    /**
     * $userData survey_id,cTime,uid,sid,opts
     */
    public function addUserVote($userData){
        //survey_user 投票用户
        $userData['cTime'] = time();
        $userData['resulted'] = 0;
        $suserId = M('survey_user')->add($userData);
        if(!$suserId){
            $this->error = '提交失败，请稍后再试！';
            return false;
        }
        //增加问卷被投次数
        $this->incSurveyCount($userData['survey_id']);
        return true;
    }

    //增加问卷被投次数
    public function incSurveyCount($id){
        return Mmc('Survey_num_'.$id,1,0,3600*24,true);
    }

}
?>