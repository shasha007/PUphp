<?php

class GrowInformationModel extends Model {

    //
    public function apiInformationList($map, $limit = 10, $page = 1, $order = 'id DESC'){
        $map['isDel']=0;
        $offset = ($page - 1) * $limit;
        $result = $this->where($map)->field('id,content as mess,logo,ctime,title')->order($order)->limit("$offset,$limit")->select();
        if (!$result) {
            return array();
        }

        foreach ($result as &$val) {
            $contents = htmlspecialchars_decode($val['mess'],ENT_QUOTES);
            $contents=strip_tags($contents);
            $contents=preg_replace('/&nbsp;/','',$contents);
            $val['mess'] = msubstr($contents, 0, 30);
        }
        return $result;
    }


    public function apiInformation($id,$uid,$version){
        $result = $this->where('id='.$id)->field('id,title,cid1,cid2,ctime,content')->find();
        //阅读数加一
        $this->setInc('rnum', 'id =' . $id);
        $list['cid1_name'] = D('GrowCategroy', 'grow')->getName($result['cid1']);
        $list['cid2_name'] = D('GrowCategroy', 'grow')->getName($result['cid2']);
        $res = D('GrowPraise','grow')->getPraise($id,$uid);
        if($res){
            $list['isPraise'] = 1;
        }else{
            $list['isPraise'] = 0;
        }
        $list['pcount'] = D('GrowPraise','grow')->countPraise($id);
        $list['ccount'] = D('GrowComment','grow')->countComment($id);
        $list['title']=$result['title'];
        $list['ctime']=$result['ctime'];
        $list['content']=  appHtml(htmlspecialchars_decode($result['content']),$version);
        $list['id']=$result['id'];
        return $list;
    }
}

?>
