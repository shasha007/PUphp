<?php

class TravelPartnerModel extends Model {

    public function addPartner($data) {
        $data['ctime'] = time();
        $addId = $this->add($data);
        return $addId;
    }

    public function updatePartner($data) {
        $oldPic = $this->getField('pic', 'id='.$data['id']);
        $res = $this->save($data);
        if ($res && $oldPic) {
            //删除旧的
            tsDelFile($oldPic);
        }
        return $res;
    }

    public function doDelete($map){
        $oldPics = $this->where($map)->field('pic')->findAll();
        $res = $this->where($map)->delete();
        if($res && $oldPics){
            foreach ($oldPics as $v) {
                if ($v['pic']) {
                    tsDelFile($v['pic']);
                }
            }
        }
        return $res;
    }
}

?>
