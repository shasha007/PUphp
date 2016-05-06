<?php
    /**
     * FilePushModel 
     * 团委下发文件类
     *
     * @uses Model
     * @package Model::Mini
     * @version $id$
     * @copyright 2009-2011 SamPeng 
     * @author SamPeng <sampeng87@gmail.com> 
     * @license PHP Version 5.2 {@link www.sampeng.cn}
     */
    class FilePushModel extends Model{
    /*
    *增加下发文件
    */
        public function doaddfile($mess){
            $data['title'] = t($mess['title']) ;
            $data['content'] = t($mess['content']) ;
            $data['template'] = t($mess['template']) ;
            $data['uid'] = intval($mess['uid']) ;
            $data['cTime'] = time() ;
            $data['uTime'] = time() ;
            $data['attachid'] = t($mess['file1'][0]) ? t($mess['file1'][0]) : 0;
            $data['isDel'] = 0 ;
            $data['sid'] = $mess['sid'];
            $push_id = $this->add($data) ;

            if ($push_id) {
                $con['pushId'] = $push_id ;
                $schoolForMobiles = array();
                foreach ($mess['area'] as $key => $value) {
                    $con['areaId'] = $value ;
                    $schools = $mess['schools'.$value] ;
                    foreach ($schools as $k => $v) {
                        $schoolForMobiles[] = $v;
                        $con['sid'] = $v ;
                        M('file_push_school')->add($con) ;
                    }
                }

                if($mess['is_mobile'] == '1')
                {
                    $msg = '【团省委】下发了标题为：【'.$data['title'].'】文件请及时查收！';
                    $map['sid'] = array('IN',join(',',$schoolForMobiles));
                    $map['can_available'] = 1;
                    $mobiles = M('user')->where($map)->field('mobile')->select();
                    foreach($mobiles as $k=>$v)
                    {
                        $mobileArr[] = $v['mobile'];
                    }
                    service('Sms')->sendsms(join(',',$mobileArr), $msg);
                }

                if(!empty($mess['file1']))
                {
                    $attachId = intval($mess['file1'][0]);
                    $map['id'] = $attachId;
                    $attachInfo = M('attach')->where($map)->find();
                    $a_data['id'] = $attachId;
                    $a_data['pushId'] = $push_id;
                    $a_data['sid'] = $mess['sid'];
                    $a_data['filename'] = $attachInfo['name'];
                    $a_data['savename'] = $attachInfo['savename'];
                    $a_data['extra'] = $attachInfo['extension'];
                    $a_data['cTime'] = $attachInfo['uploadTime'];
                    M('file_push_attach')->add($a_data);
                }
                return 1 ;
            }
            return 0 ;
        }

        /*
        *修改下发文件
        */

        public function dosavefile($pushId,$mess){
            $data['title'] = t($mess['title']) ;
            $data['content'] = t($mess['content']) ;
            $data['template'] = t($mess['template']) ;
            $data['uTime'] = time() ;
            $data['attachid'] = t($mess['file1'][0]);   //附件id
            $data['isDel'] = 0 ;
            $data['sid'] = $mess['sid'];
            $map['id'] = $pushId ;
            if ($this->where($map)->save($data) !== false) {
                $edi['uid'] = $_SESSION['userInfo']['uid'] ;
                $edi['pushId'] = $pushId ;
                $edi['uTime'] = $data['uTime'] ;
                M('file_edit')->add($edi) ; 
                M('file_push_school')->where('pushId='.$pushId)->delete() ;
                $con['pushId'] = $pushId ;
                foreach ($mess['area'] as $key => $value) {
                    $con['areaId'] = $value ;
                    $schools = $mess['schools'.$value] ;
                    foreach ($schools as $k => $v) {
                        $con['sid'] = $v ;
                        M('file_push_school')->add($con) ;
                    }
                }
                if(!empty($mess['file1']))
                {
                    $attachId = intval($mess['file1'][0]);
                    $map['id'] = $attachId;
                    $file_push_attach_info = M('file_push_attach')->where($map)->find();
                    if(empty($file_push_attach_info))
                    {
                        $attachInfo = M('attach')->where($map)->find();
                        $a_data['id'] = $attachId;
                        $a_data['pushId'] = $pushId;
                        $a_data['sid'] = $mess['sid'];
                        $a_data['filename'] = $attachInfo['name'];
                        $a_data['savename'] = $attachInfo['savename'];
                        $a_data['extra'] = $attachInfo['extension'];
                        $a_data['cTime'] = $attachInfo['uploadTime'];
                        M('file_push_attach')->add($a_data);
                    }
                }
                return 1 ;
            }         
            return 0 ;   
        }

        /*
        * 删除下发文件
        */

        public function dodelfile($pushId){
            $data['isDel'] = 1 ;
            if ($this->where('id='.$pushId)->save($data) !== false) {
                M('file_push_school')->where('pushId='.$pushId)->save($data) ;
                return 1 ;
            }
            return 0 ;
        }


        /*
        *学校查看 下发文件
        */

        public function check_file ($pushId,$sid){
            $data['status'] = 1 ;
            $map['sid'] = $sid ;
            $map['pushId'] = $pushId ;
            if (M('file_push_school')->where($map)->save($data)) {
                $view['pushId'] = $pushId ; 
                $view['sid'] = $sid ;
                $view['cTime'] = time() ;
                M('file_push_view')->add($view) ;
                return 1 ;
            }
            return 0 ;
        }


        /*
        * 学校填写回执
        */
        public function do_reply($data){
            $map['pushId'] = $data['pushId'] ;
            $map['sid'] = $data['sid'] ;
            $rep = $map ;
            $rep['content'] = $data['content'] ;
            //$rep['attachid'] =  ;  回执附件id
            $rep['cTime'] = time() ;
            $reply = M('file_push_reply')->where($map)->find() ;
            if($reply && $reply['isDel'] == 0){
                if(M('file_push_reply')->where($map)->save($rep) !== false){
                    return 1 ;
                } 
            }else{
                if (M('file_push_reply')->add($rep)) {
                    $da['isReply'] = 1 ;
                    M('file_push_school')->where($map)->save($da) ;
                    return 1 ;
                }
            }
            return 0 ;
        }
    }
    ?>