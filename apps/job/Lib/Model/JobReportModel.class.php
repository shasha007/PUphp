<?php
    /**
     * 举报
     * **/
    class JobReportModel extends Model{
        
        //举报列表
        public function reportList($map,$limit=10,$order='id DESC'){
            $res = $this->where($map)->field('id,uid,postid,type,reason,ctime')->order($order)->findPage($limit);
            if($res){
                return $res;
            }else{
                return false;
            }
        }
        
        //举报
        public function report($data){
            if(!$data['uid']){
                $data['uid'] = $_SESSION['mid'];
            }
            $map['postid'] = $data['postid'];
            if(!M('JobPublishPosts')->where($map)->find()){
                $this->error = '职位不存在';
                return  0;
               
            }
            $map['uid'] = $data['uid'];
            if($this->where($map)->find()){
                $this->error = '已经举报';
                return 2;
            }
            $res = $this->add($data);
            if($res){
                return 1;//举报成功
            }
        }
        
        //类型
        public function type($type){
            $type = array('薪资不真实','工作经验或学历要求不真实','公司信息不真实','其他');
            if($type){
                return $type[$type];
            }else{
                return $type;
            }
        }
        
    }
?>
