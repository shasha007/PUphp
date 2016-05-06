<?php

/**
 * @uses Action
 * @package
 * @version $id$
 * @copyright 2013-2015 陆冬云
 * @author 陆冬云 <rechner00@hotmail.com>
 * @license PHP Version 5.3
 */
class EcFolderModel extends Model {

    //添加，编辑 分类
    public function editEcFolder($sid){
        $title = t($_POST['title']);
        if(!$title || get_str_length($_POST['title'])>20){
            $this->error ='名称不能为空，最多20个字';
            return 0;
        }
        $data['sid'] = $sid;
        $data['title'] = $title;
        $data['pid'] = intval($_POST['pid']);
        $data['is_folder'] = 1;
        $data['isRelease'] = 1;
        $id = intval($_POST['id']);
        if(!$id){
            return $this->add($data);
        }
        $this->where('id='.$id)->save($data);
        return $id;
    }
    //新建 申请表
    public function addNewFile($sid){
        $data['pid'] = intval($_GET['pid']);
        $data['sid'] = $sid;
        $data['title'] = '新增申请表';
        $data['pid'] = intval($_GET['pid']);
        $data['is_folder'] = 0;
        return $this->add($data);
    }
    public function doDelFolder($sid){
        $id = intval($_POST['id']);
        if($id<=0){
            $this->error ='分类ID错误';
            return false;
        }
        $res = $this->where("is_folder=1 and sid=$sid and id=$id")->delete();
        if(!$res){
            $this->error ='分类不存在，或已删除';
            return false;
        }
        $this->setField('pid', 0, "pid=$id");
        return true;
    }
    public function doDelFile($sid){
        $id = intval($_POST['id']);
        if($id<=0){
            $this->error ='申请表ID错误';
            return false;
        }
        $obj = $this->where("is_folder=0 and sid=$sid and id=$id")->find();
        if(!$obj){
            $this->error ='申请表不存在，或已删除';
            return false;
        }
        if(!$obj['isRelease']){
            $res = $this->where("id=$id")->delete();
            if(!$res){
                $this->error ='申请表不存在，或已删除';
                return false;
            }
            M('EcInput')->where("fileId=$id")->delete();
        }else{
            $this->setField('isDel', 1, "id=$id");
        }
        return true;
    }
    //转移pid,排序ordernum,发布isRelease,适用年级years
    public function setEcField($sid){
        $id = intval($_POST['id']);
        if($id<=0){
            $this->error  = 'ID错误';
            return false;
        }
        $field = t($_POST['field']);
        $value = t($_POST['value']);
        $yearsArr = array();
        $this->freeAllEcFolder($sid);
        if($field=='years'){
            $value = str_replace('，', ',', $value);
            $arr = explode(',', $value);
            foreach ($arr as $v) {
                $v = intval($v);
                if($v>=10 && $v<=99 && !in_array($v, $yearsArr)){
                    $yearsArr[] = $v;
                }
            }
            sort($yearsArr);
            $value = implode(',', $yearsArr);
            $this->error = $value;
            if($value){
                $value .= ',';
            }
            $this->setField($field,$value,"sid=$sid and id=$id");
            return false;
        }elseif($field=='ordernum'){
            $value = intval($value);
        }
        $this->setField($field,$value,"sid=$sid and id=$id");
        return true;
    }
    // 前台某个年级学生可申请列表
    public function allEcFolderYear($sid,$year){
        $list = $this->allEcFolder($sid, true, 0);
        if($year){
            $this->_checkYear($list, $year);
        }
        foreach ($list as $k=>$v) {
            if($v['is_folder'] && empty($v['files'])){
                unset($list[$k]);
            }
        }
        return $list;
    }
    //去除不是某个年级的申请表
    private function _checkYear(&$list,$year){
        foreach ($list as $k=>&$v) {
            if($v['is_folder']){
                $this->_checkYear($v['files'], $year);
            }else{
                if($v['years']!='' && strpos($v['years'],$year.',')===false){
                    unset($list[$k]);
                }
            }
        }
    }
    //后台列表 前台列表 $front=true
    public function allEcFolder($sid,$front=false,$pid=0){
        if ($front && $pid == 0 && $cache = S('EcFolder_allEcFolder_0_'.$sid)) { // 前台 pid=0 才缓存
            return $cache;
        }
        $where = "isDel=0 and pid=$pid and sid=$sid";
        if($front){
            $where .= ' and isRelease=1';
        }
        $list = $this->where($where)->order('ordernum asc,id desc')->findAll();
        if(!$list){
            return array();
        }
        foreach ($list as &$v) {
            //申请表
            if($v['is_folder']){
                $v['files'] = $this->allEcFolder($sid,$front, $v['id']);
            }
        }
        if ($front && $pid == 0) { // 前台 pid=0 才缓存
            S('EcFolder_allEcFolder_0_'.$sid, $list);
        }
        return $list;
    }
    public function freeAllEcFolder($sid){
        S('EcFolder_allEcFolder_0_'.$sid, null);
    }
    //分类列表
    public function onlyFolder($sid){
        $list = $this->where("is_folder=1 and pid=0 and sid=$sid")->order('ordernum asc,id desc')->findAll();
        if(!$list){
            return array();
        }
        return $list;
    }
    //发布
    public function doRelease($sid){
        $auditCnt = M('EcAuditor')->where("sid=$sid")->count();
        if(!$auditCnt){
            $this->error  = '尚未分配审核人，请先至【审核人管理】添加审核人';
            return false;
        }
        $_POST['field'] = 'isRelease';
        $_POST['value'] = '1';
        return $this->setEcField($sid);
    }
    public function getFile($fileId){
        $file = $this->where("id=$fileId")->field('title')->find();
        if(!$file){
            $file = array();
        }
        return $file;
    }
    public function getFileName($fileId){
        $file = $this->getFile($fileId);
        if(empty($file)){
            return '';
        }
        return $file['title'];
    }
}

?>