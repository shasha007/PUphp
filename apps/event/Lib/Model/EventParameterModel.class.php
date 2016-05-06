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
class EventParameterModel extends Model {

    public function getParam($eventId) {
        if ($cache = S('Cache_Event_Param_'.$eventId)) {
            return $cache;
        }
        $param = $this->where('eventId=' . $eventId)->find();
        if ($param) {
            $param['parameter'] = unserialize($param['parameter']);
            $param['defaultName'] = unserialize($param['defaultName']);
        } else {
            $param = array();
            $param['eventId'] = $eventId;
            $param['parameter'] = array();
            $param['defaultName'] = $this->_defaultName();
        }
        S('Cache_Event_Param_'.$eventId, $param);
        return S('Cache_Event_Param_'.$eventId);
    }

    public function editParam($input){
        $data['eventId'] = $input['eventId'];
        $realname = t($input['realname']);
        $defaultName['realname'] = $realname?$realname:'选手名称';
        $school = t($input['school']);
        $defaultName['school'] = $school?$school:'选手院校';
        $content = t($input['content']);
        $defaultName['content'] = $content?$content:'简介';
        $path = t($input['path']);
        $defaultName['path'] = $path?$path:'头像+展示图片';
        $data['defaultName'] = serialize($defaultName);
        $paramCount = intval($input['paramCount']);
        for($i=0;$i<=$paramCount;$i++){
            $key = 'param_'.$i;
            $name = '';
            $row = array();
            if(isset($input[$key])){
                $name = t($input[$key]);
            }
            if($name != ''){
                $row[] = $name;
                $key = 'type_'.$i;
                $row[] = t($input[$key]);
                $key = 'wr_ok_'.$i;
                $row[] = isset($input[$key])?1:0;
                $key = 'show_ok_'.$i;
                $row[] = isset($input[$key])?1:0;
            }
            if(!empty($row)){
                $parameter[] = $row;
            }
        }
        $data['parameter'] = serialize($parameter);
        $res = M('event_parameter')->save($data);
        if(!$res){
            $res = M('event_parameter')->add($data);
        }
        if($res){
            S('Cache_Event_Param_'.$input['eventId'], null);
            return true;;
        }
        $this->error = '保存失败';
        return false;
    }

    private function _defaultName() {
        return array('realname' => '选手名称', 'school' => '选手院校', 'content' => '简介', 'path' => '头像+展示图片');
    }

}
