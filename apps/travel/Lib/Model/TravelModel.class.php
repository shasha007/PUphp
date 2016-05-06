<?php

class TravelModel extends Model {

    public function doAddRoute($routeMap, $cover) {
        $routeMap['cTime'] = isset($routeMap['cTime']) ? $routeMap['cTime'] : time();
        $routeMap['coverId'] = $cover['info'][0]['id'];
        $routeMap['limitCount'] = 0 == intval($routeMap['limitCount']) ? 999999999 : $routeMap['limitCount'];
        $addId = $this->add($routeMap);
        return $addId;
    }

    public function doEditRoute($routeMap, $cover, $obj) {
        $routeMap['rTime'] = isset($routeMap['rTime']) ? $routeMap['rTime'] : time();
        if ($cover['status']) {
            //删除旧的
            model('Attach')->deleteAttach($obj['coverId'], true, true);
            $routeMap['coverId'] = $cover['info'][0]['id'];
        }
        $routeMap['limitCount'] = 0 == intval($routeMap['limitCount']) ? 999999999 : $routeMap['limitCount'];
        $saveId = $this->where('id =' . $obj['id'])->save($routeMap);
        return $saveId;
    }

    public function routeList($map,$limit=15) {
        $map['isDel'] = 0;
        $list = $this->where($map)->order('id DESC')->findPage($limit);
        $daoCat = M('travel_cat');
        $daoArea = M('travel_area');
        $daoUser = M('travel_user');
        foreach ($list['data'] as $k => $v) {
            $list['data'][$k]['cat'] = $daoCat->where('id=' . $v['catId'])->getField('name');
            $list['data'][$k]['area'] = $daoArea->where('id=' . $v['areaId'])->getField('name');
            $list['data'][$k]['user'] = $daoUser->where('travelId=' . $v['id'])->getField('count(*)');
        }
        return $list;
    }

}

?>
