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
class EventImgModel extends Model {

    public function doDelete($map,$delFile='true') {
        $images = $this->field('path')->where($map)->findAll();
        if ($this->where($map)->delete()) {
            //删除图片文件
            if($delFile){
                foreach ($images as $value) {
                    if(strpos($value['path'], '/')){
                        tsDelFile($value['path']);
                    }else{
                        tsDelFile('event/'.$value['path']);
                    }
                }
            }
            return true;
        }
        return false;
    }

    public function getHandyHxList($map = array(), $limit = 10, $page = 1) {
        $offset = ($page - 1) * $limit;
        $list = $this->field('id,title,path,w,h')->where($map)->order('id DESC')->limit("$offset,$limit")->select();
        foreach ($list as $key => $value) {
            $row = $value;
            $row['src'] = tsGetEventUserThumb($row['path'], 0,0,'no');
            $row['path'] = tsGetEventUserThumb($row['path'], 255,182,'f');
            if($value['path'] && $value['w']==0 && $value['h']==0){
                $imgInfo = $this->_photoInfo($value['path']);
                $row['w'] = ''.$imgInfo[0];
                $row['h'] = ''.$imgInfo[1];
                if($row['w']==0 && $row['h']==0){
                    $row['w'] = 100;
                    $row['h'] = 100;
                }
                $data['w'] = $row['w'];
                $data['h'] = $row['h'];
                $this->where('id='.$value['id'])->save($data);
            }
            $list[$key] = $row;
        }
        return $list;
    }
    private function _photoInfo($filename){
        if(strpos($filename, '/')){
            $file = SITE_PATH.'/data/uploads/'.$filename;
        }else{
            $file = SITE_PATH.'/data/uploads/event/'.$filename;
        }
        $info = @getimagesize($file);
        return $info;
    }
}
