<?php

/**
 * EventTypeModel
 *
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class SchoolOrgaModel extends Model {

    /**
     * getType
     * 获取所有分类
     * @access public
     * @return void
     */
    public function getAll($sid, $cat=0, $field='*') {
        $map['sid'] = $sid;
        if($cat){
            $map['cat'] = $cat;
        }
        return $this->where($map)->field($field)->order('display_order ASC')->findAll();
    }

    /**
     * addType
     * 增加分类
     * @param mixed $map
     * @access public
     * @return void
     */
    public function addOrga($sid, $title,$cat) {
        if (empty($title)) {
            return -1;
        }
        $map['sid'] = $sid;
        $max = $this->where($map)->max('display_order');
        $map['title'] = $title;
        $map['cat'] = $cat;
        $map['display_order'] = $max + 1;
        return $this->add($map);
    }

    /**
     * deleteType
     * 删除分类
     * @param mixed $map
     * @access public
     * @return void
     */
    public function deleteOrga($sid, $ids) {
        $map['sid'] = $sid;
        $map['id'] = array('in', $ids);
        return $this->where($map)->delete();
    }

    public function changeOrder($sid, $id, $baseId) {
        if ($id <= 0 || $baseId <= 0) {
            return false;
        }
        $map['sid'] = $sid;
        $map['id'] = array('in', $id . ',' . $baseId);
        $orgas = $this->where($map)->findAll();
        if (count($orgas) != 2) {
            return false;
        }
        //转为结果集为array('id'=>'order')的格式
        foreach ($orgas as $v) {
            $orgas[$v['id']] = intval($v['display_order']);
        }
        //交换order值
        $res = $this->where('id=' . $id)->setField('display_order', $orgas[$baseId]);
        if ($res) {
            $res = $this->where('id=' . $baseId)->setField('display_order', $orgas[$id]);
        }
        return $res;
    }

    /**
     * editType
     * 编辑分类
     * @param mixed $map
     * @access public
     * @return void
     */
    public function editOrga($sid, $id, $title, $cat) {
        $map['sid'] = $sid;
        $map['id'] = $id;
        $data['title'] = $title;
        $data['cat'] = $cat;
        $query = $this->where($map)->save($data);
        return $query;
    }

    /**
     * getTypeName
     * 通过id获得名字
     * @param mixed $id
     * @access public
     * @return void
     */
    public function getTypeName($id) {
        $map['id'] = $id;
        $result = $this->where($map)->field('title')->find();
        return $result['title'];
    }

}
