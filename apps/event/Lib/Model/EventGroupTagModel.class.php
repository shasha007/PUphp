<?php

class EventGroupTagModel extends Model {

    var $tableName = 'group_tag';

    // 设置部落tag
    public function setGroupTag($tagname, $gid) {
        $tagname = str_replace(' ', ',', $tagname);
        $tagname = str_replace('，', ',', $tagname);
        $tagInfo = $this->__addTags($tagname, 0);
        if ($tagInfo) {
            foreach ($tagInfo as $k => $v) {
                $groupTagInfo = $this->where("gid=$gid AND tag_id=" . $v['tag_id'])->find();
                if (!$groupTagInfo) {
                    $data['gid'] = $gid;
                    $data['tag_id'] = $v['tag_id'];
                    if ($v['group_tag_id'] = $this->add($data)) {
                        $tagdata[] = $v;
                        $tagids[] = $v['tag_id'];
                    }
                } else {
                    $tagids[] = $v['tag_id'];
                }
            }
            if ($tagids) {
                $delete_map['gid'] = $gid;
                $delete_map['tag_id'] = array('not in', $tagids);
                $this->where($delete_map)->delete();
                $return['code'] = '1';
                //$return['data'] =  $tagdata ;
            } else {
                $return['code'] = '0';
            }
        } else {
            $delete_map['gid'] = $gid;
            $this->where($delete_map)->delete();
            $return['code'] = '-1';
        }
        return $return['code'];
        //return json_encode($return);
    }
    
    //添加全局tag
	private function __addTags($tagname, $nowcount)
	{
		if(!$tagname) return false;
		$tagname = str_replace(' ', ',', $tagname);
		$tagname = str_replace('，', ',', $tagname);
		$tagname = explode(',', $tagname);
		foreach($tagname as $k=>$v){
			$v = preg_replace('/\s/i', '', $v);
			if( mb_strlen($v, 'UTF-8') > '10' || $v == '')continue;
			$result[] = $this->__addOneTag($v);
			$addcount = $addcount+1;
			if( $addcount+$nowcount >= 5 )break;
		}
		return $result;
	}

        
        	private function __addOneTag($tagname)
	{
		$map['tag_name'] = t($tagname);
		if( $info = M('tag')->where($map)->find() ){
			return $info;
		}else{
			$map['tag_id'] = M('tag')->add($map);
			return $map;
		}
	}

}

?>
