<?php

class WorkAttachModel extends Model {

    var $tableName = 'school_work_attach';

    //回收站 文件，包括附件
    function remove($id) {
        $id = is_array($id) ? '(' . implode(',', $id) . ')' : '(' . $id . ')';  //判读是不是数组回收

        $attachIds = array();
        $files = $this->field('attachId')->where('id IN' . $id)->findAll();

        $result = $this->where('id IN' . $id)->delete();
        if ($result) {
            foreach ($files as $v) {
                $attachIds[] = $v['attachId'];
            }
            //处理附件
            model('Attach')->deleteAttach($attachIds, true);
            return true;
        }
        return false;
    }

}

?>