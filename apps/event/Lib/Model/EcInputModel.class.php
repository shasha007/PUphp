<?php

/**
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EcInputModel extends Model {

    //添加 输入框、附件
    public function addEcInput($sid){
        $type = intval($_POST['type']);
        if($type==3){
            return $this->_addEcInput2($sid);
        }
        $fileId = intval($_POST['fileId']);
        if($fileId<=0){
            $this->error='申请表参数错误';
            return 0;
        }
        if(!in_array($type, array(1,2,4))){
            $this->error='类型错误';
            return 0;
        }
        //附件只可添加一次
        if($type==4){
            $hasAttach = $this->where("fileId=$fileId and type=4")->field('id')->find();
            if($hasAttach){
                $this->error='附件只可添加一次';
                return 0;
            }
        }
        $title = t($_POST['title']);
        if(!$title || get_str_length($_POST['title'])>10){
            $this->error='名称不能为空，最多10个字';
            return 0;
        }
        $must = intval($_POST['must'])?1:0;
        $desc = t($_POST['desc']);
        $data['title'] = $title;
        $data['must'] = $must;
        $data['desc'] = $desc;
        if($_POST['id']){
            $map['sid'] = $sid;
            $map['id'] = intval($_POST['id']);
            $this->where($map)->save($data);
            return true;
        }
        $data['opt'] = '';
        $data['sid'] = $sid;
        $data['type'] = $type;
        $data['fileId'] = $fileId;
        return $this->add($data);
    }
    //单选
    private function _addEcInput2($sid){
        $fileId = intval($_POST['fileId']);
        if($fileId<=0){
            $this->error='申请表参数错误';
            return 0;
        }
        $title = t($_POST['title']);
        if(!$title || get_str_length($_POST['title'])>100){
            $this->error='问题长度不能为空，最多100个字符';
            return 0;
        }
        $cntName = count($_POST['name']);
        $cntNote = count($_POST['note']);
        if($cntName<2 || $cntNote!=$cntName){
            $this->error='最少填写2个侯选项';
            return 0;
        }
        foreach($_POST['name'] as $k=>$v){
            $name = t($v);
            if(!$name || get_str_length($v)>100){
                $this->error='候选项长度不能为空，最多100个字符';
                return 0;
            }
            $note = $this->_regNote(t($_POST['note'][$k]));
//            if(!$note){
//                $this->error='得分请输入数字,或百分率。不可为0';
//                return 0;
//            }
            $opt[] = array($name,$note);
        }
        $desc = t($_POST['desc']);
        $data['title'] = $title;
        $data['desc'] = $desc;
        $data['opt'] = serialize($opt);
        if($_POST['id']){
            $map['sid'] = $sid;
            $map['id'] = intval($_POST['id']);
            $this->where($map)->save($data);
            return true;
        }
        $data['sid'] = $sid;
        $data['fileId'] = $fileId;
        $data['type'] = 3;
        $data['must'] = 1;
        return $this->add($data);
    }
    private function _regNote($note){
        if(!$note){
            return 0;
        }
        $last = substr($note, -1, 1);
        if($last!='%'){
            return intval($note*100)/100;
        }
        $int = intval($note);
        if($int<0){
            return (0-$int).'%';
        }elseif($int>0){
            return $int.'%';
        }
        return 0;
    }
    public function delInput($sid){
        $id = intval($_POST['id']);
        if($id<=0){
            $this->error ='ID错误';
            return false;
        }
        $res = $this->where("sid=$sid and id=$id")->delete();
        if(!$res){
            $this->error ='资料不存在，或已删除';
            return false;
        }
        return true;
    }
    public function setEcField($sid){
        $id = intval($_POST['id']);
        if($id<=0){
            $this->error  = 'ID错误';
            return false;
        }
        $field = t($_POST['field']);
        $value = t($_POST['value']);
        if($field=='inputOrder'){
            $value = intval($value);
        }
        $this->setField($field,$value,"sid=$sid and id=$id");
        return true;
    }
}

?>