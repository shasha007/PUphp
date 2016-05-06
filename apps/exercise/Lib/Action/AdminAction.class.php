<?php

/**
 * AdminAction
 * @uses Action
 * @package Admin
 * @version $id$
 * @copyright 2009-2011 SamPeng
 * @author SamPeng <sampeng87@gmail.com>
 * @license PHP Version 5.2 {@link www.sampeng.cn}
 */
import('admin.Action.AdministratorAction');

class AdminAction extends AdministratorAction {

    public function index() {
        $this->display();
    }

    public function addTiku() {
        if(isset($_FILES['files']['tmp_name'][0])){
            $suc = 0;
            $fail = 0;
            $file = $_FILES['files']['tmp_name'][0];
            set_time_limit(0);
            $contents = file_get_contents($file);
            $aArray = explode("\n", $contents);
            $cnt = count($aArray);
            for($i=1;$i<$cnt;$i++) {
                $line = explode(",", $aArray[$i-1]);
                $title = $line[0];
                //导入每一行
                $ret = $this->doAddLine($line);
                if ($ret) {
                    $suc++;
                } else {
                    $fail++;
                    $failArray[] = '行'.$i.' '.  mb_substr($title, 0, 5,'UTF-8');
                }
            }

            $this->assign('suc',$suc);
            $this->assign('fail',$fail);
            $this->assign('failArray',$failArray);
        }
        $this->display();
    }

    private function doAddLine($line){
        if(!isset($line[0]) || !isset($line[1]) || !isset($line[2])){
            return false;
        }
        $title = douhao(t($line[0]));
        if($tile = ''){
            return false;
        }

        $typeArr = array('1'=>'选择','2'=>'判断','3'=>'填空');
        $type = getAlphnum(trim(t($line[1])),$typeArr);
        if(!array_key_exists($type,$typeArr)){
            return false;
        }

        $answer = douhao(t($line[2]));
        if($answer == ''){
            return false;
        }
        $select = '';
        switch ($type) {
            case 1:
                $i = 3;
                $select = array();
                $answerArr = array();
                while (isset($line[$i]) && trim(t($line[$i])) != ''){
                    $answerArr[$i-2] = chr(94+$i);
                    $select[] = douhao(t($line[$i]));
                    $i++;
                }
                $answer = getAlphnum($answer,$answerArr);
                if(!array_key_exists($answer,$answerArr)){
                    return false;
                }
                $select = serialize($select);
                break;
            case 2:
                $answerArr = array('错','对');
                $answer = getAlphnum($answer,$answerArr);
                if(!array_key_exists($answer,$answerArr)){
                    return false;
                }
            default:
                break;
        }
        $data['title'] = $title;
        $data['select'] = $select;
        $data['answer'] = $answer;
        $data['type'] = $type;
        return D('Exercise')->add($data);
    }

}
