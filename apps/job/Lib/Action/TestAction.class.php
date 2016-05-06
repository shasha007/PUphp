<?php
class TestAction extends Action{

    public function vard($obj){
    echo '<pre>';
     print_r($obj);
     echo '</pre>';
    }

 //合并TA好友关注数据表
 public function changeTaShow(){
     set_time_limit(0);
     $ta_follow = M('MakefriendsAttention')->findAll();
     $this->vard($ta_follow);
     foreach($ta_follow as $val){
         $map['uid'] = $val['uid'];     
         $map['fid'] = $val['tid'];
         $data['uid'] = $val['uid'];  
         $data['fid'] = $val['tid'];
         $data['newPhoto'] = $val['newPhoto'];
         $weibo = M('WeiboFollow');
         $has = $weibo ->where($map)->find();
         if($has){
             $weibo->where($map)->setField('newPhoto',$val['newPhoto']);
         }else{
             $data['from'] = 1;
             $weibo->add($data);
             //uid 关注数+1 fid被关注数+1
//             $c = M('UserCount');
//             $c->setInc('following','uid='.$val['uid']);     
//             $c->setInc('follower','uid='.$val['fid']);
         }
     }
 }
 
    public function upUserCount(){
        set_time_limit(0);
        $u = M('UserCount');
        $userCount = $u->findAll();
        $w = M('WeiboFollow');
        foreach($userCount as $val){
            $following = $w->where('uid='.$val['uid'])->count();    //关注     
            $follower = $w->where('fid='.$val['uid'])->count();     //被关注
            $u->where('uid='.$val['uid'])->setField('following',$following);    
            $u->where('uid='.$val['uid'])->setField('follower',$follower);
        }
    }
    
    public function alter(){
        $sql = "";
        M()->query($sql);
    }
    
    public function cha(){
        $sql = "show tables";
        echo '<pre>';
        print_r(M()->query($sql));
        echo '</pre>';
    }

    
}
?>