<?php
    /**
     * FilePutModel
     * 学校上报文件类
     *
     * @uses Model
     * @package Model::Mini
     * @version $id$
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class FilePutModel extends Model{
    /*
    *增加下发文件
    */
        public function doaddfile($mess){
            $data['title'] = t($mess['title']) ;
            $data['content'] = t($mess['content']) ;
            $data['provinceId'] = intval($mess['provinceId']) ;
            $data['cityId'] = intval($mess['cityId']) ;
            $data['sid'] = intval($mess['sid']) ;
            $data['cTime'] = time() ;
            $data['attachid'] = t($mess['file1'][0]);
            $data['isDel'] = 0 ;
            $put_id = $this->add($data) ;

            if ($put_id) {
                if(!empty($mess['file1']))
                {
                    $attachId = intval($mess['file1'][0]);
                    $map['id'] = $attachId;
                    $attachInfo = M('attach')->where($map)->find();
                    $a_data['id'] = $attachId;
                    $a_data['putId'] = $put_id;
                    $a_data['sid'] = $mess['sid'];
                    $a_data['filename'] = $attachInfo['name'];
                    $a_data['savename'] = $attachInfo['savename'];
                    $a_data['extra'] = $attachInfo['extension'];
                    $a_data['cTime'] = $attachInfo['uploadTime'];
                    M('file_put_attach')->add($a_data);
                }
                return 1 ;
            }
            return 0 ;
        }

        /**
        *修改下发文件
        */
        public function dosavefile($putId,$mess){
            $data['title'] = t($mess['title']) ;
            $data['content'] = t($mess['content']) ;
            $data['uTime'] = time() ;
            $data['attachid'] = t($mess['file1'][0]);   //附件id
            $data['provinceId'] = intval($mess['provinceId']) ;
            $data['cityId'] = intval($mess['cityId']) ;
            $data['sid'] = intval($mess['sid']) ;
            $data['isDel'] = 0 ;
            $map['id'] = $putId ;
            if ($this->where($map)->save($data) !== false) {
                if(!empty($mess['file1']))
                {
                    $attachId = intval($mess['file1'][0]);
                    $map['id'] = $attachId;
                    $file_push_attach_info = M('file_put_attach')->where($map)->find();
                    if(empty($file_push_attach_info))
                    {
                        $attachInfo = M('attach')->where($map)->find();
                        $a_data['id'] = $attachId;
                        $a_data['putId'] = $putId;
                        $a_data['sid'] = $mess['sid'];
                        $a_data['filename'] = $attachInfo['name'];
                        $a_data['savename'] = $attachInfo['savename'];
                        $a_data['extra'] = $attachInfo['extension'];
                        $a_data['cTime'] = $attachInfo['uploadTime'];
                        M('file_put_attach')->add($a_data);
                    }
                }
                return 1 ;
            }         
            return 0 ;   
        }

        /**
         * 删除上报文件
         * @param $putId
         * @return int
         */
        public function doDelFile($putId){
            $data['isDel'] = 1 ;
            $map['id'] = $putId;
            if ($this->where($map)->save($data) !== false) {
                return 1 ;
            }
            return 0 ;
        }


    }
    ?>