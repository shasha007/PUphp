<?php

/**
 * 
 * @author Administrator
 * ta秀活动
 */
class MakefriendsActivityModel extends Model {

    /**
     * ta秀首页
     */
    public function taShowList($limit = 10, $page = 1) {
        $key = 'MakefriendsActivity_taShowList_' .$limit.'_'. $page;
        $cache = Mmc($key);
        if ($cache !== false) {
           return json_decode($cache, true);
        }
        $offset = ($page - 1) * $limit;
        $now = date('Y-m-d H:i:s');
        $where = "etime>='$now' AND is_del=0";
        //获取活动信息
        $list = $this->where($where)
                ->order('stime Desc')
                ->limit("$offset,$limit")
                ->select();
        foreach ($list as $k => $v) {
            $list[$k]['title'] = htmlspecialchars_decode($list[$k]['title']);
            $list[$k]['rule'] = htmlspecialchars_decode($list[$k]['rule']);
            $map = array('act_id' => $v['id'], 'audit_result' => 1, 'isDel' => 0);
            $joinCount = M('makefriends_photo')->where($map)->count();

            $list[$k]['joinCount'] = $joinCount;

            //获取图片地址
            $attach = getAttach($v['att_id']);
            $file = $attach['savepath'] . $attach['savename'];
            $list[$k]['act_pic'] = tsMakeThumbUp($file, 360, 0);;
//            $list[$k]['act_pic'] = model('Attach')->getAllAttachById($v['att_id']);
            //获取点赞前10名照片
            $res = D('MakefriendsPhoto', 'makefriends')->getTopByActIdAndPraise($v['id'], 4, 1);
            $data = array();
            foreach ($res as $k1 => $v1) {
                $data = array(
                    'photoId' => $v1['photoId'],
                    'pic_big' => $v1['pic_big'],
                    'pic_middle' => $v1['pic_middle'],
                    'headPhoto' => $v1['headPhoto'],
                    'praiseCount' => $v1['praiseCount'],
                    'weekCount' => $v1['weekCount'],
                );
                $list[$k]['topten'][] = $data;
            }
            unset($list[$k]['att_id']);
        }
        Mmc($key, json_encode($list),0,60*10);
        return $list;
    }

    /**
     * 获取所有秀场活动列表
     */
    public function allActivityList($column = '*') {
        $now = date('Y-m-d H:i:s');
        $where = array('etime' => array('EGT', $now), 'is_del' => 0);

        $res = $this->field($column)->where($where)->order('stime Desc')->select();
        return $res;
    }

    public function getActivityById($id) {
        $list = D('MakefriendsActivity', 'makefriends')->where(array('id' => $id, 'is_del' => 0))->find();
        $list['act_pic'] = model('Attach')->getAllAttachById($list['att_id']);
        $list['title'] = htmlspecialchars_decode($list['title']);
        $list['rule'] = htmlspecialchars_decode($list['rule']);
        unset($list['att_id']);
        return $list;
    }

}

?>
