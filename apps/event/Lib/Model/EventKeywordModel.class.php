<?php
/**
 * 搜索关键字
 */
class EventKeywordModel extends Model{
    
    //保存搜索关键字
    public function saveKeyword($word){
        return ;
        if($word == ''){
            return ;
        }
        $map['word'] = $word;
        $res = $this->where($map)->find();
        $data['ctime'] = time();
        if($res){
            //$this->setInc('num', "word='{$word}'");
            //$data['ctime'] = time();
            //$this->where($map)->save($data);
        }else{
            $data['word'] = $word;
            $this->add($data);
        }
    }
    
    //关键字列表
    public function keywordList($limit){
        $cache = Mmc('event_keywordList');
        if ($cache !== false) {
            return json_decode($cache, true);
        }
        $klist = array();
        $list = $this->field('word')->order('num DESC,id DESC')->limit("0,$limit")->select();
        if($list){
            $klist = $list;
        }
        Mmc('event_keywordList', json_encode($list), 0, 60*100);
        return $klist;
    }
    
    
}
?>


