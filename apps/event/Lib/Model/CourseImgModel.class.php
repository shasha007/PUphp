<?php

/**
 * EventImgModel
 * 活动的图片模型
 * @uses BaseModel
 * @package
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
class CourseImgModel extends BaseModel {

    public function doDelete($map) {
        $images = $this->field('path')->where($map)->findAll();
        if ($this->where($map)->delete()) {
            //删除图片文件
            foreach ($images as $value) {
                if(strpos($value['path'], '/')){
                    tsDelFile($value['path']);
                }else{
                    tsDelFile('event/'.$value['path']);
                }

            }
            return true;
        }
        return false;
    }

    public function getHandyHxList($map = array(), $limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;
        $list = $this->field('id,title,path')->where($map)->order('id DESC')->limit("$offset,$limit")->select();
        foreach ($list as $key => $value) {
            $row = $value;
            $row['path'] = tsGetEventUserThumb($row['path'], 255,182,'f');
            $list[$key] = $row;
        }
        return $list;
    }
}
