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
    class EventVoteModel extends BaseModel{
        public function doDelete($map){
            return $this->where($map)->delete();
        }
    }
