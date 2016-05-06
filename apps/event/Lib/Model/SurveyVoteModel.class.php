<?php
class SurveyVoteModel extends BaseModel
{

    public function _initialize(){
        parent::_initialize();
    }

    /**
     * addVote
     * 添加投票
     * @param mixed $data
     * @param mixed $opt
     * @access public
     * @return void
     */
    public function addVote($data,$opt){
        //检测选项是否重复
        $opt_test = array_filter($opt);
        foreach($opt as $value){
            if(get_str_length($value) >200){
                throw new ThinkException("投票选项不能超过200个字符");
            }
        }

        $opt_test_count = count(array_unique( $opt_test ));
        if( $opt_test_count < count($opt_test) ) throw new ThinkException( '投票不允许有重复项' );

        $map['suid'] = $data['suid'];
        $max = $this->where($map)->max('display_order');
        $data['display_order'] = $max + 1;
        $vote_id = $this->add($data);

        if($vote_id){
            //选项表
            $optDao = D("SurveyOpt");
            foreach($_POST["opt"] as $v) {
                if(!$v) continue;
                $data["vote_id"]    =    $vote_id;
                $data["name"]       =    t($v);
                $optDao->add($data);
            }
        }
        return $vote_id;

    }
}
?>
