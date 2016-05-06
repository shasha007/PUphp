<?php
    /**
     * EventFlashModel
     * 活动的视频模型
     * @uses BaseModel
     * @package
     * @version $id$
     * @copyright 2009-2011 SamPeng
     * @author SamPeng <sampeng87@gmail.com>
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class EventFlashModel extends BaseModel{
        public function doDelete($map){
            return $this->where($map)->delete();
        }
    }
